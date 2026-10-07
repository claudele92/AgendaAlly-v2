<?php
declare(strict_types=1);

// Normal owned source only. Never issues a challenge or enables transport.
$root = dirname(__DIR__,2);
$backend = $root.'/.migration-backup/backend';
require $backend.'/vendor/autoload.php';
$app = require $backend.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\EmailSettingService\SelectedEmailRecovery as Recovery;
use Illuminate\Support\Facades\DB;

$directory = $root.'/.local/staging-mvp';
function saveEvidence(string $path, array $value): void {
    if (file_exists($path) || is_link($path)) throw new RuntimeException('Evidence already exists; refusing replacement.');
    $stream = fopen($path,'xb');
    if (!$stream || !chmod($path,0600)) throw new RuntimeException('Evidence cannot be secured.');
    try {
        $json = json_encode($value,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
        if (fwrite($stream,$json)!==strlen($json) || !fflush($stream) || !fsync($stream)) throw new RuntimeException('Evidence persistence failed.');
    } finally { fclose($stream); }
}
function snapshot(string $root): array {
    ob_start(); require $root.'/scripts/development/email-presentation-preservation.php';
    return json_decode(ob_get_clean(),true,512,JSON_THROW_ON_ERROR);
}
function activeState(): array {
    return [
        'pending_or_uncertain'=>DB::table('selected_email_deliveries')->whereIn('state',['PENDING','BLOCKED','SENDING','UNKNOWN'])->count(),
        'jobs'=>DB::table('jobs')->count(),'failed_jobs'=>DB::table('failed_jobs')->count(),
    ];
}
try {
    if (PHP_SAPI!=='cli' || getenv('REPLIT_DEPLOYMENT')!==false || config('app.env')!=='local'
        || !config('development.enabled') || !config('development.database.owned_sqlite_enabled')
        || config('app.debug') || config('database.default')!=='sqlite'
        || realpath(DB::connection()->getDatabaseName())!==realpath($backend.'/database/development/agendaally.sqlite')
        || !in_array(config('development.email.mode'),['log','disabled'],true)
        || !in_array(config('mail.default'),['log','array'],true)
        || config('development.payments.mode')!=='disabled'
        || !in_array(config('development.sms.mode'),['log','disabled'],true)
        || config('development.firebase.enabled') || config('development.maps.enabled')
        || is_file($directory.'/account-email-approval.json')) {
        throw new RuntimeException('Recovery runtime safety gate failed.');
    }
    $mode=$argv[1]??'inspect';
    $identities=[
        '75ef2b85-cbf9-48dd-b30a-e40d8b23f564'=>['reset','2026-10-05 23:42:19',2],
        '5f414156-cb15-4317-aabb-8ed125198b77'=>['verify','2026-10-05 23:48:28',3],
        '0ebcd0fe-da09-4c1f-95f8-abc741e3b00e'=>['verify','2026-10-05 23:52:56',4],
    ];
    $user=App\Models\User::findOrFail(144);
    if ($user->email_verified_at || $user->password || !$user->hasRole('user')
        || $user->roles->count()!==1 || (string)$user->created_at!=='2026-10-05 22:59:40') {
        throw new RuntimeException('Approved disposable account identity/state changed.');
    }
    if ($mode==='inspect') {
        DB::statement('PRAGMA query_only=ON');
        $entries=[];
        foreach($identities as $id=>[$kind,$created,$jobId]) {
            $row=DB::table('selected_email_deliveries')->find($id);
            $job=DB::table('jobs')->find($jobId);
            if (!$row || !$job || (int)$row->user_id!==144 || $row->kind!==$kind
                || $row->created_at!==$created || $row->state!=='PENDING'
                || $row->claimed_at!==null || $row->sent_at!==null
                || Recovery::jobIdentity($job)!==$id || (int)$job->attempts!==0
                || $job->reserved_at!==null || $job->queue!=='mvp-notifications'
                || abs((int)$job->created_at-strtotime($created.' UTC'))>2) {
                throw new RuntimeException('Documented artifact/job identity mismatch.');
            }
            $payload=json_decode(Illuminate\Support\Facades\Crypt::decryptString($row->encrypted_payload),true,512,JSON_THROW_ON_ERROR);
            if (($payload['email']??null)!==$user->email) throw new RuntimeException('Encrypted recipient does not match.');
            $entries[]=['id'=>$id,'kind'=>$kind,'created_at'=>$created,'expires_at'=>$row->expires_at,
                'row_sha256'=>Recovery::fingerprint($row),'job_id'=>$jobId,'job_sha256'=>Recovery::fingerprint($job),
                'recipient_binding_confirmed'=>true,'expired_at_inspection'=>now()->greaterThanOrEqualTo($row->expires_at)];
        }
        if (activeState()!==['pending_or_uncertain'=>3,'jobs'=>3,'failed_jobs'=>0]) throw new RuntimeException('Unexpected active records; stopped.');
        $evidence=['at'=>gmdate('c'),'user_id'=>144,'recipient_sha256'=>hash('sha256',strtolower(trim($user->email))),
            'request_correlation'=>'Exact documented endpoint/outbox/job timestamps; initiating actor unavailable',
            'records'=>$entries,'active'=>activeState(),'snapshot'=>snapshot($root)];
        saveEvidence($directory.'/account-email-recovery-before.json',$evidence);
        echo json_encode(['result'=>'INSPECTED_ONLY','user_id'=>144,'records'=>$entries,'active'=>$evidence['active']]),"\n";
    } elseif ($mode==='cleanup') {
        $before=json_decode(file_get_contents($directory.'/account-email-recovery-before.json'),true,512,JSON_THROW_ON_ERROR);
        if (($before['user_id']??null)!==144 || !hash_equals($before['recipient_sha256'],hash('sha256',strtolower(trim($user->email)))))
            throw new RuntimeException('Frozen recipient authority mismatch.');
        foreach ($before['records'] as $entry) if (!isset($identities[$entry['id']])) throw new RuntimeException('Frozen selection mismatch.');
        if(count($before['records'])!==3) throw new RuntimeException('Frozen selection cardinality mismatch.');
        saveEvidence($directory.'/account-email-recovery-intent.json',[
            'at'=>gmdate('c'),'action'=>'EXACT_UNATTEMPTED_ARTIFACT_CANCELLATION','user_id'=>144,
            'expected'=>$before['records'],'before_sha256'=>hash_file('sha256',$directory.'/account-email-recovery-before.json'),
            'owner_authority'=>'Explicit bounded cleanup request; no SMTP or new challenge authority',
        ]);
        $receipt=(new Recovery)->cancel(144,$before['records']);
        $after=snapshot($root);
        $changed=[];
        foreach($before['snapshot']['tables'] as $table=>$value) if($after['tables'][$table]!==$value) $changed[]=$table;
        $allowed=['jobs','password_resets','selected_email_deliveries'];
        $safe=!array_diff($changed,$allowed) && $after['schemaSha256']===$before['snapshot']['schemaSha256'];
        $evidence=['at'=>gmdate('c'),'result'=>'CLEANUP_COMMITTED','receipt'=>$receipt,'active'=>activeState(),
            'changed_tables'=>$changed,'all_other_tables_and_schema_preserved'=>$safe,'snapshot'=>$after];
        saveEvidence($directory.'/account-email-recovery-after.json',$evidence);
        if (!$safe || activeState()!==['pending_or_uncertain'=>0,'jobs'=>0,'failed_jobs'=>0]) throw new RuntimeException('Post-cleanup review required; no further action.');
        echo json_encode(array_diff_key($evidence,['snapshot'=>true])),"\n";
    } elseif($mode==='prepare') {
        $after=json_decode(file_get_contents($directory.'/account-email-recovery-after.json'),true,512,JSON_THROW_ON_ERROR);
        if (($after['all_other_tables_and_schema_preserved']??false)!==true
            || activeState()!==['pending_or_uncertain'=>0,'jobs'=>0,'failed_jobs'=>0]) throw new RuntimeException('Not clean; cannot prepare.');
        $context=['version'=>1,'attempt_id'=>(string)Illuminate\Support\Str::uuid(),'user_id'=>144,'kind'=>'verify',
            'recipient_sha256'=>hash('sha256',strtolower(trim($user->email))),
            'expires_at'=>time()+7200,'phase'=>'PREPARED_NO_CHALLENGE_NO_SEND_AUTHORITY',
            'fresh_request_limit'=>1,'send_limit'=>1,'reset_permitted'=>false];
        saveEvidence($directory.'/account-email-recovery-context.json',$context);
        App\Support\AccountEmailEvidence::append('retry_prepared',[
            'user_id'=>144,'challenge_issued'=>false,'send_authorized'=>false,'clean_state'=>activeState(),
        ]);
        echo json_encode(['result'=>$context['phase'],'attempt_id'=>$context['attempt_id'],'active'=>activeState(),
            'fresh_challenge_deferred_until_recipient_screen_ready'=>true]),"\n";
    } else throw new RuntimeException('Unknown operation.');
} catch(Throwable $error) {
    // Deliberately suppress exception arguments/transport/recipient details.
    fwrite(STDERR,"Bounded account-email recovery stopped at a safety/state/evidence check. No send or challenge was attempted.\n");exit(1);
}
