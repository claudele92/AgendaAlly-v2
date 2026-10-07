<?php
declare(strict_types=1);

use App\Services\AuthService\PasswordResetService;
use App\Services\EmailSettingService\SelectedEmailRecovery;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Exact owner-approved superseded-row cancellation for a VERIFIED Customer.
 * Does not relax the older unverified-account recovery service or issue codes.
 * Called only after the normal one-shot script's runtime/identity safety gates.
 */
function authorizeCurrentReset(string $prefix, App\Models\User $user, $pending, bool $commit = true): array
{
    $olderId = 'f540eb35-d8cb-4ce2-9505-15cf975ff4ab';
    $latestId = '7d13b183-8f22-4404-8599-eefcc22b60ed';
    $jobs = DB::table('jobs')->get();
    if ($pending->count() !== 2 || $jobs->count() !== 2
        || DB::table('password_resets')->count() !== 1 || DB::table('failed_jobs')->exists()) {
        throw new RuntimeException('Revised exact-two recovery scope changed.');
    }
    $rows = []; $matchedJobs = [];
    foreach ($pending as $row) {
        if (!in_array($row->id, [$olderId, $latestId], true) || (int)$row->user_id !== 144
            || $row->kind !== 'reset' || $row->state !== 'PENDING'
            || $row->claimed_at !== null || $row->sent_at !== null) {
            throw new RuntimeException('Unexpected or attempted delivery in revised scope.');
        }
        $rows[$row->id] = $row;
    }
    foreach ($jobs as $job) {
        $identity = SelectedEmailRecovery::jobIdentity($job);
        if (!isset($rows[$identity]) || isset($matchedJobs[$identity])
            || $job->queue !== 'mvp-notifications' || $job->reserved_at !== null || (int)$job->attempts !== 0) {
            throw new RuntimeException('Revised scope contains an unexpected or attempted job.');
        }
        $matchedJobs[$identity] = $job;
    }
    if (count($matchedJobs) !== 2 || $rows[$olderId]->created_at !== '2026-10-06 06:10:17'
        || $rows[$latestId]->created_at !== '2026-10-06 06:11:58'
        || now()->greaterThanOrEqualTo($rows[$latestId]->expires_at)
        || now()->diffInSeconds($rows[$latestId]->expires_at) < 180) {
        throw new RuntimeException('Current challenge lifetime or exact request identity changed.');
    }
    $service = app(PasswordResetService::class);
    foreach ($rows as $id => $row) {
        $payload = json_decode(Crypt::decryptString($row->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
        if (($payload['email'] ?? null) !== $user->email || !is_string($payload['challenge'] ?? null)
            || $service->isCurrentEmailToken($user, $payload['challenge']) !== ($id === $latestId)) {
            throw new RuntimeException('Latest/current versus superseded binding is not proven.');
        }
        unset($payload);
    }
    $original = json_decode(file_get_contents($prefix.'-before.json'), true, 512, JSON_THROW_ON_ERROR);
    $snapshot = resetSnapshot(); $changed = [];
    foreach ($original['snapshot']['tables'] as $table => $fingerprint) {
        if ($snapshot['tables'][$table] !== $fingerprint) $changed[] = $table;
    }
    if ($snapshot['schema'] !== $original['snapshot']['schema']
        || array_diff($changed, ['users','model_logs','password_resets','selected_email_deliveries','jobs'])) {
        throw new RuntimeException('Unrelated protected data changed before revised authorization.');
    }
    $audit = DB::table('model_logs')->select(['id','model_id','created_by','type','created_at'])->orderByDesc('id')->first();
    $auditKeys = DB::select('SELECT key FROM model_logs,json_each(model_logs.data) WHERE model_logs.id=?', [$audit->id]);
    if ($snapshot['tables']['users']['rows'] !== $original['snapshot']['tables']['users']['rows']
        || $snapshot['tables']['model_logs']['rows'] !== $original['snapshot']['tables']['model_logs']['rows'] + 1
        || (int)$audit->model_id !== 144 || (int)$audit->created_by !== 144
        || $audit->type !== 'user_updated' || $audit->created_at !== '2026-10-06 05:42:56'
        || array_map(fn($item) => $item->key, $auditKeys) !== ['password']
        || (string)$user->updated_at !== '2026-10-06 05:42:56') {
        throw new RuntimeException('Account/audit drift exceeds the owner-confirmed password update.');
    }
    $history = DB::connection()->getPdo()->prepare(
        'SELECT * FROM selected_email_deliveries WHERE id NOT IN (?,?) ORDER BY rowid'
    );
    $history->execute([$olderId, $latestId]);
    $historyRows = $history->fetchAll(PDO::FETCH_ASSOC);
    if (['rows' => count($historyRows), 'sha256' => hash('sha256', serialize($historyRows))]
        !== $original['snapshot']['tables']['selected_email_deliveries']) {
        throw new RuntimeException('Earlier delivery history changed.');
    }
    if (!$commit) return [[
        'ready' => true, 'approved_verified_customer' => true,
        'older_superseded' => true, 'latest_current' => true,
        'unattempted_exact_deliveries' => 2, 'unattempted_exact_jobs' => 2,
        'failed_jobs' => 0, 'current_challenges' => 1,
        'older_history_preserved' => true, 'owner_password_update_scope_matches' => true,
        'approval_absent' => true, 'no_mutation_or_smtp' => true,
    ], []];
    $customer = (array)DB::table('users')->find(144);
    unset($customer['password'], $customer['updated_at']);
    resetEvidence($prefix.'-revised-authorization.json', [
        'at' => gmdate('c'), 'user_id' => 144, 'older_delivery' => $olderId, 'latest_delivery' => $latestId,
        'requests_already_recorded' => 2, 'owner_confirms_separate_password_update' => true,
        'owner_authorization' => 'Cancel only older superseded reset; preserve latest/current challenge; exactly ONE real email for latest; no new request or unrelated processing; stop for Gmail receipt and UI interaction.',
        'owner_normal_code_entry_ready' => true, 'source' => 'explicit_owner_form_confirmation',
        'send_limit' => 1, 'new_request_limit' => 0, 'gmail_receipt_confirmed' => false,
        'older_row_sha256' => SelectedEmailRecovery::fingerprint($rows[$olderId]),
        'older_job_sha256' => SelectedEmailRecovery::fingerprint($matchedJobs[$olderId]),
        'latest_row_sha256' => SelectedEmailRecovery::fingerprint($rows[$latestId]),
        'latest_job_sha256' => SelectedEmailRecovery::fingerprint($matchedJobs[$latestId]),
        'approved_customer_nonpassword_sha256' => hash('sha256', serialize($customer)),
        'changed_tables_since_original_preflight' => $changed, 'snapshot' => $snapshot,
    ]);
    DB::transaction(function () use ($rows, $matchedJobs, $olderId, $latestId): void {
        foreach ($rows as $id => $expected) {
            $current = DB::table('selected_email_deliveries')->where('id', $id)->lockForUpdate()->first();
            $job = DB::table('jobs')->where('id', $matchedJobs[$id]->id)->lockForUpdate()->first();
            if (!$current || !$job
                || SelectedEmailRecovery::fingerprint($current) !== SelectedEmailRecovery::fingerprint($expected)
                || SelectedEmailRecovery::fingerprint($job) !== SelectedEmailRecovery::fingerprint($matchedJobs[$id])) {
                throw new RuntimeException('Selected rows/jobs changed concurrently.');
            }
        }
        // The older token was proven noncurrent. Never delete/replace any token.
        if (DB::table('selected_email_deliveries')->where('id', $olderId)->where('state', 'PENDING')
                ->whereNull('claimed_at')->whereNull('sent_at')->update([
                    'state' => 'EXPIRED', 'error_code' => 'OWNER_CANCELLED_SUPERSEDED_RESET', 'updated_at' => now(),
                ]) !== 1
            || DB::table('jobs')->where('id', $matchedJobs[$olderId]->id)
                ->whereNull('reserved_at')->where('attempts', 0)->delete() !== 1) {
            throw new RuntimeException('Exact older-row cancellation failed.');
        }
    });
    $after = resetSnapshot(); $cancelChanges = [];
    foreach ($snapshot['tables'] as $table => $fingerprint) {
        if ($after['tables'][$table] !== $fingerprint) $cancelChanges[] = $table;
    }
    $latestUnchanged = SelectedEmailRecovery::fingerprint(DB::table('selected_email_deliveries')->find($latestId))
        === SelectedEmailRecovery::fingerprint($rows[$latestId]);
    $preserved = $after['schema'] === $snapshot['schema']
        && !array_diff($cancelChanges, ['selected_email_deliveries','jobs'])
        && $after['tables']['password_resets'] === $snapshot['tables']['password_resets']
        && $latestUnchanged
        && SelectedEmailRecovery::fingerprint(DB::table('jobs')->find($matchedJobs[$latestId]->id))
            === SelectedEmailRecovery::fingerprint($matchedJobs[$latestId]);
    resetEvidence($prefix.'-cancel-older-result.json', [
        'at' => gmdate('c'), 'older_state' => 'EXPIRED', 'older_job_removed' => true,
        'latest_delivery_unchanged' => $latestUnchanged, 'latest_challenge_and_job_unchanged' => $preserved,
        'changed_tables' => $cancelChanges, 'snapshot' => $after,
    ]);
    if (!$preserved) throw new RuntimeException('Cancellation preservation check failed; no send permitted.');
    // Compare excluded latest row to the full retained history, including the
    // deliberately cancelled older row, rather than rewriting original evidence.
    $after['tables']['selected_email_deliveries'] = resetOtherDeliveries($latestId);
    return [['snapshot' => $after], [
        'normal_ui' => true, 'request_count' => 2, 'effective_selected_request_count' => 1,
        'requests_already_recorded' => 2, 'http_status' => 'NOT_RETAINED',
        'code_entry_visible' => true, 'owner_code_entry_session_confirmed' => true,
        'new_requests_authorized' => 0, 'new_resends_authorized' => 0,
        'body_only_exchange_source_contract' => true,
        'no_code_in_url' => true, 'approved_recipient_binding' => true,
        'source' => 'owner_ready_confirmation_and_revised_exact_latest_authorization',
    ]];
}
