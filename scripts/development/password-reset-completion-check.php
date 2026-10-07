<?php
declare(strict_types=1);

use App\Services\AuthService\PasswordResetService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/** Read-only state/replay proof. No code exchange, login, resend or transport. */
function checkResetCompletion(string $prefix, App\Models\User $user): array
{
    $authority = json_decode(file_get_contents($prefix.'-revised-authorization.json'), true, 512, JSON_THROW_ON_ERROR);
    $sent = json_decode(file_get_contents($prefix.'-result.json'), true, 512, JSON_THROW_ON_ERROR);
    $row = DB::table('selected_email_deliveries')->where('id', $authority['latest_delivery'])->first();
    $older = DB::table('selected_email_deliveries')->where('id', $authority['older_delivery'])->first();
    if (!$row || !$older || $row->state !== 'SENT' || $older->state !== 'EXPIRED'
        || ($sent['state'] ?? null) !== 'SENT' || (int)$row->user_id !== 144 || $row->kind !== 'reset') {
        throw new RuntimeException('Approved delivery outcome changed.');
    }
    $customer = (array)DB::table('users')->find(144);
    unset($customer['password'], $customer['updated_at']);
    $profilePreserved = hash('sha256', serialize($customer)) === $authority['approved_customer_nonpassword_sha256'];
    // Native updated audit data contains the OLD raw-original changed fields,
    // not the new persisted values. Compare internally; never return either hash.
    $transition = DB::selectOne(
        "SELECT u.password<>json_extract(m.data,'$.password') AS changed
         FROM users u JOIN model_logs m ON m.model_id=u.id
         WHERE u.id=144 AND m.id=550 AND m.type='user_updated' AND m.created_by=144"
    );
    $auditKeys = DB::select('SELECT key FROM model_logs,json_each(model_logs.data) WHERE model_logs.id=550');
    $passwordChanged = (bool)($transition->changed ?? false)
        && array_map(fn($item) => $item->key, $auditKeys) === ['password'];
    $payload = json_decode(Crypt::decryptString($row->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
    if (($payload['email'] ?? null) !== $user->email) throw new RuntimeException('Consumed recipient binding changed.');
    $consumed = DB::table('password_resets')->count() === 0;
    $replayDeniedByState = !app(PasswordResetService::class)->isCurrentEmailToken($user, $payload['challenge']);
    unset($payload);
    $snapshot = resetSnapshot(); $changed = [];
    foreach ($sent['snapshot']['tables'] as $table => $fingerprint) {
        if ($snapshot['tables'][$table] !== $fingerprint) $changed[] = $table;
    }
    $unrelatedPreserved = $snapshot['schema'] === $sent['snapshot']['schema']
        && !array_diff($changed, ['users','model_logs','password_resets','personal_access_tokens']);
    $facts = [
        'at' => gmdate('c'), 'user_id' => 144,
        'owner_gmail_receipt_and_presentation_confirmed' => true,
        'owner_reports_normal_ui_reset_complete' => true,
        'verified' => (bool)$user->email_verified_at,
        'profile_role_location_and_recipient_unchanged' => $profilePreserved,
        'persisted_password_hash_transition' => $passwordChanged,
        'native_password_audit_at' => DB::table('model_logs')->where('id',550)->value('created_at'),
        'challenge_consumed' => $consumed,
        'consumed_email_code_cannot_issue_another_authorization_by_state' => $replayDeniedByState,
        'replay_check_method' => 'Exact native isCurrentEmailToken is false; no challenge row remains. No consume/exchange request or limiter mutation.',
        'current_customer_token_count' => DB::table('personal_access_tokens')->where('tokenable_id',144)->count(),
        'delivery_history_unchanged_after_send' => $snapshot['tables']['selected_email_deliveries'] === $sent['snapshot']['tables']['selected_email_deliveries'],
        'jobs' => DB::table('jobs')->count(), 'failed_jobs' => DB::table('failed_jobs')->count(),
        'pending_or_uncertain' => DB::table('selected_email_deliveries')->whereIn('state',['PENDING','BLOCKED','SENDING','UNKNOWN'])->count(),
        'temporary_permission_absent' => !file_exists(dirname($prefix).'/account-email-approval.json'),
        'changed_tables_since_send' => $changed, 'schema_and_unrelated_data_preserved' => $unrelatedPreserved,
        'normal_new_password_login' => 'AWAITING_OWNER_UI_CONFIRMATION_AND_IDENTITY_CHECK',
        'score' => 'C1=0.5; O4=0.5; 16/20=80%', 'snapshot' => $snapshot,
    ];
    if (!$profilePreserved || !$passwordChanged || !$consumed || !$replayDeniedByState
        || !$unrelatedPreserved || !$facts['delivery_history_unchanged_after_send']
        || $facts['jobs'] !== 0 || $facts['failed_jobs'] !== 0 || $facts['pending_or_uncertain'] !== 0) {
        throw new RuntimeException('Authoritative completion checks require review.');
    }
    resetEvidence($prefix.'-completion-state.json', $facts);
    return array_diff_key($facts, ['snapshot' => true]);
}
