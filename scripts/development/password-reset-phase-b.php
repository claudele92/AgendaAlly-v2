<?php
declare(strict_types=1);

// One owner-authorized reset, using an already approved Customer. No code,
// password, recipient address or SMTP dialogue is emitted or persisted here.
$root = dirname(__DIR__, 2);
$backend = $root.'/.migration-backup/backend';
require $backend.'/vendor/autoload.php';
$app = require $backend.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\SelectedAccountEmailPolicy as Policy;
use App\Services\EmailSettingService\SelectedEmailRecovery as Recovery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

$directory = $root.'/.local/staging-mvp';
$prefix = $directory.'/password-reset-phase-b';
$approvalPath = $directory.'/account-email-approval.json';
$approvalCreated = false;

function resetEvidence(string $path, array $facts): void
{
    if (is_link($path) || file_exists($path)) throw new RuntimeException('One-shot evidence already exists.');
    $handle = fopen($path, 'xb');
    if (!$handle || !chmod($path, 0600)) throw new RuntimeException('Secure evidence unavailable.');
    try {
        $line = json_encode($facts, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n";
        if (fwrite($handle, $line) !== strlen($line) || !fflush($handle) || !fsync($handle)) {
            throw new RuntimeException('Evidence could not be persisted.');
        }
    } finally { fclose($handle); }
}

function resetSnapshot(): array
{
    $pdo = DB::connection()->getPdo();
    $snapshot = ['schema' => hash('sha256', serialize($pdo->query(
        "SELECT type,name,tbl_name,sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' ORDER BY type,name"
    )->fetchAll(PDO::FETCH_ASSOC))), 'tables' => []];
    foreach ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
        ->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $quoted = '"'.str_replace('"', '""', $table).'"';
        $rows = $pdo->query('SELECT * FROM '.$quoted.' ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
        $snapshot['tables'][$table] = ['rows' => count($rows), 'sha256' => hash('sha256', serialize($rows))];
    }
    return $snapshot;
}

function resetOtherDeliveries(string $selectedId): array
{
    $statement = DB::connection()->getPdo()->prepare(
        'SELECT * FROM selected_email_deliveries WHERE id <> ? ORDER BY rowid'
    );
    $statement->execute([$selectedId]);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    return ['rows' => count($rows), 'sha256' => hash('sha256', serialize($rows))];
}

try {
    if (!Policy::runtimeAllowed() || file_exists($approvalPath) || is_link($approvalPath)) {
        throw new RuntimeException('Runtime isolation or permission boundary unavailable.');
    }
    $authority = json_decode(file_get_contents($directory.'/account-email-recovery-context-closed.json'), true, 512, JSON_THROW_ON_ERROR);
    $checkpoint = json_decode(file_get_contents($directory.'/account-login-logout-checkpoint.json'), true, 512, JSON_THROW_ON_ERROR);
    if (($authority['user_id'] ?? null) !== 144 || ($checkpoint['user_id'] ?? null) !== 144
        || ($checkpoint['verified_at_unchanged'] ?? null) !== true) {
        throw new RuntimeException('Previously approved Customer authority is absent.');
    }
    $user = App\Models\User::findOrFail(144);
    if (!$user->email_verified_at || !$user->password || !$user->active
        || $user->roles->pluck('name')->all() !== ['user']
        || !hash_equals($authority['recipient_sha256'], hash('sha256', strtolower(trim($user->email))))) {
        throw new RuntimeException('Approved Customer or recipient binding changed.');
    }
    if (DB::table('failed_jobs')->exists() || App\Models\EmailTemplate::where('type', 'reset')->count() !== 1
        || !DB::table('email_settings')->where('active', true)->whereNotNull('password')->exists()) {
        throw new RuntimeException('Provider, template or failed-job prerequisite unavailable.');
    }
    $manifest = json_decode(file_get_contents($root.'/.migration-backup/web/.next/dev/routes-manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    $rewrite = $manifest['rewrites']['afterFiles'] ?? [];
    if (count($rewrite) !== 1 || $rewrite[0]['destination'] !== 'http://127.0.0.1:8000/api/v1/:path*') {
        throw new RuntimeException('Normal Customer is not bound to normal Laravel.');
    }
    $pending = DB::table('selected_email_deliveries')->whereIn('state', ['PENDING', 'BLOCKED', 'SENDING', 'UNKNOWN'])->get();
    $mode = $argv[1] ?? '';
    if ($mode === 'completion-check') {
        require __DIR__.'/password-reset-completion-check.php';
        echo json_encode(checkResetCompletion($prefix, $user)), "\n";
        exit(0);
    }
    if ($mode === 'prepare') {
        if ($pending->isNotEmpty() || DB::table('jobs')->exists() || DB::table('password_resets')->exists()) {
            throw new RuntimeException('Pending work or existing challenge prevents isolation.');
        }
        resetEvidence($prefix.'-before.json', [
            'at' => gmdate('c'), 'user_id' => 144, 'recipient_sha256' => $authority['recipient_sha256'],
            'verified' => true, 'role' => 'user', 'password_present' => true,
            'snapshot' => resetSnapshot(), 'request_limit' => 1, 'send_limit' => 1,
            'normal_customer_normal_backend' => true,
        ]);
        resetEvidence($prefix.'-request-intent.json', [
            'at' => gmdate('c'), 'user_id' => 144, 'kind' => 'reset',
            'owner_authorization' => 'Explicit Phase B authorization after owner-accepted focused Phase A evidence; authenticated Admin CRUD remains UNVERIFIED.',
            'normal_request_limit' => 1, 'send_limit' => 1, 'verification_or_subscription_or_admin_test_authorized' => false,
        ]);
        echo json_encode(['prepared' => true, 'verified_customer' => true, 'recipient_binding' => true,
            'pending' => 0, 'jobs' => 0, 'failed_jobs' => 0, 'existing_reset_challenges' => 0,
            'send_permission_absent' => true, 'normal_runtime' => true]), "\n";
        exit(0);
    }
    if ($mode === 'keep-latest-check') {
        require __DIR__.'/password-reset-current-authorization.php';
        [$facts] = authorizeCurrentReset($prefix, $user, $pending, false);
        echo json_encode($facts), "\n";
        exit(0);
    }
    if ($mode === 'keep-latest-send-once') {
        require __DIR__.'/password-reset-current-authorization.php';
        [$before, $requestProof] = authorizeCurrentReset($prefix, $user, $pending);
        $pending = DB::table('selected_email_deliveries')->whereIn('state', ['PENDING','BLOCKED','SENDING','UNKNOWN'])->get();
    } elseif ($mode === 'send-once') {
        $before = json_decode(file_get_contents($prefix.'-before.json'), true, 512, JSON_THROW_ON_ERROR);
        $requestProof = json_decode(file_get_contents($prefix.'-request-proof.json'), true, 512, JSON_THROW_ON_ERROR);
    } else throw new RuntimeException('Unsupported operation.');
    $requestBound = $mode === 'keep-latest-send-once'
        ? ($requestProof['request_count'] ?? null) === 2
            && ($requestProof['effective_selected_request_count'] ?? null) === 1
            && ($requestProof['new_requests_authorized'] ?? null) === 0
            && ($requestProof['new_resends_authorized'] ?? null) === 0
        : ($requestProof['request_count'] ?? null) === 1
            && ($requestProof['http_status'] ?? null) === 200 && ($requestProof['no_resend'] ?? null) === true;
    if (!$requestBound || ($requestProof['normal_ui'] ?? null) !== true
        || ($requestProof['code_entry_visible'] ?? null) !== true || ($requestProof['no_code_in_url'] ?? null) !== true
        || ($requestProof['approved_recipient_binding'] ?? null) !== true
        || (($requestProof['resume_without_request_verified'] ?? null) !== true
            && ($requestProof['owner_code_entry_session_confirmed'] ?? null) !== true)) {
        throw new RuntimeException('Normal one-request UI proof is incomplete.');
    }
    $jobs = DB::table('jobs')->get();
    if ($pending->count() !== 1 || $jobs->count() !== 1 || DB::table('password_resets')->count() !== 1) {
        throw new RuntimeException('Exactly one isolated request/delivery/job is required.');
    }
    $row = $pending[0]; $job = $jobs[0];
    if ((int) $row->user_id !== 144 || $row->kind !== 'reset' || $row->state !== 'PENDING'
        || $row->claimed_at !== null || $row->sent_at !== null
        || now()->greaterThanOrEqualTo($row->expires_at) || now()->diffInSeconds($row->expires_at) < 90
        || $job->queue !== 'mvp-notifications' || $job->reserved_at !== null || (int) $job->attempts !== 0
        || Recovery::jobIdentity($job) !== $row->id) {
        throw new RuntimeException('Delivery/job is not a fresh untouched reset.');
    }
    $payload = json_decode(Crypt::decryptString($row->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
    if (($payload['email'] ?? null) !== $user->email
        || !app(App\Services\AuthService\PasswordResetService::class)->isCurrentEmailToken($user, $payload['challenge'])) {
        throw new RuntimeException('Reset challenge/recipient is not current.');
    }
    unset($payload); // Never returned, logged or saved.
    $preSend = resetSnapshot(); $changed = [];
    if ($preSend['tables']['selected_email_deliveries']['rows']
        !== $before['snapshot']['tables']['selected_email_deliveries']['rows'] + 1
        || resetOtherDeliveries($row->id) !== $before['snapshot']['tables']['selected_email_deliveries']) {
        throw new RuntimeException('Request changed unrelated delivery history or issued multiple deliveries.');
    }
    foreach ($before['snapshot']['tables'] as $table => $fingerprint) {
        if ($preSend['tables'][$table] !== $fingerprint) $changed[] = $table;
    }
    if ($before['snapshot']['schema'] !== $preSend['schema']
        || array_diff($changed, ['password_resets', 'selected_email_deliveries', 'jobs'])) {
        throw new RuntimeException('Unrelated data changed before the send boundary.');
    }
    resetEvidence($prefix.'-pre-send.json', [
        'at' => gmdate('c'), 'user_id' => 144, 'delivery_id' => $row->id, 'kind' => 'reset',
        'expires_at' => $row->expires_at, 'recipient_binding_current' => true,
        'row_sha256' => Recovery::fingerprint($row), 'job_sha256' => Recovery::fingerprint($job),
        'changed_tables_from_request' => $changed, 'snapshot' => $preSend,
    ]);
    // Durable consumed authority marker is written before any SMTP connection.
    resetEvidence($prefix.'-send-intent.json', ['at' => gmdate('c'), 'user_id' => 144,
        'delivery_id' => $row->id, 'kind' => 'reset', 'attempt_limit' => 1]);
    resetEvidence($approvalPath, [
        'version' => 1, 'owner_controlled_recipient' => true, 'disposable_or_explicitly_safe_account' => true,
        'records' => [['delivery_id' => $row->id, 'user_id' => 144, 'kind' => 'reset',
            'recipient_sha256' => $authority['recipient_sha256'],
            'expires_at' => min(time() + 180, strtotime($row->expires_at.' UTC'))]],
    ]);
    $approvalCreated = true;
    if (!Policy::permitsRow($row)) throw new RuntimeException('Exact-record temporary approval rejected.');
    try {
        $exit = $app->make(Illuminate\Contracts\Console\Kernel::class)->call('selected:account-email', ['delivery' => $row->id]);
    } finally {
        if (!unlink($approvalPath)) throw new RuntimeException('Temporary approval removal needs intervention.');
        $approvalCreated = false;
    }
    $after = resetSnapshot(); $sendChanges = [];
    foreach ($preSend['tables'] as $table => $fingerprint) if ($after['tables'][$table] !== $fingerprint) $sendChanges[] = $table;
    $state = DB::table('selected_email_deliveries')->where('id', $row->id)->value('state');
    $result = [
        'at' => gmdate('c'), 'delivery_id' => $row->id, 'state' => $state, 'command_exit' => $exit,
        'temporary_approval_absent' => !file_exists($approvalPath),
        'jobs' => DB::table('jobs')->count(), 'failed_jobs' => DB::table('failed_jobs')->count(),
        'pending_or_uncertain' => DB::table('selected_email_deliveries')->whereIn('state', ['PENDING','BLOCKED','SENDING','UNKNOWN'])->count(),
        'changed_tables_during_send' => $sendChanges,
        'schema_and_unrelated_data_preserved' => $after['schema'] === $preSend['schema']
            && !array_diff($sendChanges, ['selected_email_deliveries','jobs','failed_jobs'])
            && resetOtherDeliveries($row->id) === $before['snapshot']['tables']['selected_email_deliveries'],
        'gmail_receipt' => 'AWAITING_OWNER_CONFIRMATION', 'snapshot' => $after,
    ];
    resetEvidence($prefix.'-result.json', $result);
    echo json_encode(array_diff_key($result, ['snapshot' => true])), "\n";
    exit($exit === 0 && $state === 'SENT' && $result['schema_and_unrelated_data_preserved'] ? 0 : 1);
} catch (Throwable) {
    fwrite(STDERR, "Phase B stopped at a safety boundary. No automatic retry or second reset is permitted. Retained metadata/state must be reviewed.\n");
    exit(1);
} finally {
    if ($approvalCreated) unlink($approvalPath);
}
