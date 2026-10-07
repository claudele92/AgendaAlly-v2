<?php
declare(strict_types=1);

// One already-issued, owner-approved verification. Never issues or resends codes.
$root=dirname(__DIR__,2);
$backend=$root.'/.migration-backup/backend';
require $backend.'/vendor/autoload.php';
$app=require $backend.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\SelectedAccountEmailPolicy as Policy;
use App\Services\EmailSettingService\SelectedEmailRecovery as Recovery;
use App\Support\AccountEmailEvidence as Evidence;
use Illuminate\Support\Facades\{DB,Crypt};

$directory=$root.'/.local/staging-mvp';
$approvalPath=$directory.'/account-email-approval.json';
$approvalCreated=false;
function retrySave(string $path,array $facts):void {
    if(file_exists($path)||is_link($path)) throw new RuntimeException('One-shot evidence already exists.');
    $handle=fopen($path,'xb');
    if(!$handle||!chmod($path,0600)) throw new RuntimeException('Cannot secure evidence.');
    try {
        $line=json_encode($facts,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
        if(fwrite($handle,$line)!==strlen($line)||!fflush($handle)||!fsync($handle))
            throw new RuntimeException('Cannot persist evidence.');
    } finally {fclose($handle);}
}
function retrySnapshot(string $root):array {
    ob_start();require $root.'/scripts/development/email-presentation-preservation.php';
    return json_decode(ob_get_clean(),true,512,JSON_THROW_ON_ERROR);
}
try {
    if(!Policy::runtimeAllowed()||file_exists($approvalPath)||is_link($approvalPath))
        throw new RuntimeException('Selected runtime authority is invalid.');
    $context=Evidence::context();
    if(!$context||(int)$context['user_id']!==144) throw new RuntimeException('Prepared context unavailable.');
    $user=App\Models\User::findOrFail(144);
    if($user->email_verified_at||$user->password||$user->roles->pluck('name')->all()!==['user']
        ||!hash_equals($context['recipient_sha256'],hash('sha256',strtolower(trim($user->email)))))
        throw new RuntimeException('Approved disposable recipient/state mismatch.');
    $rows=DB::table('selected_email_deliveries')->whereIn('state',['PENDING','BLOCKED','SENDING','UNKNOWN'])->get();
    $jobs=DB::table('jobs')->get();
    if($rows->count()!==1||$jobs->count()!==1||DB::table('failed_jobs')->exists())
        throw new RuntimeException('Unexpected pending or failed work.');
    $row=$rows[0];$job=$jobs[0];
    if((int)$row->user_id!==144||$row->kind!=='verify'||$row->state!=='PENDING'
        ||$row->claimed_at!==null||$row->sent_at!==null
        ||now()->greaterThanOrEqualTo($row->expires_at)||now()->diffInSeconds($row->expires_at)<90
        ||$job->queue!=='mvp-notifications'||$job->reserved_at!==null||(int)$job->attempts!==0
        ||Recovery::jobIdentity($job)!==$row->id)
        throw new RuntimeException('Record/job is not fresh, untouched verification.');
    $payload=json_decode(Crypt::decryptString($row->encrypted_payload),true,512,JSON_THROW_ON_ERROR);
    if($payload['email']!==$user->email
        ||!app(App\Services\AuthService\EmailVerificationService::class)->isCurrent($user,$payload['challenge']))
        throw new RuntimeException('Challenge is no longer current.');
    unset($payload); // Never persisted, output or included in evidence.
    $events=array_map(fn($line)=>json_decode($line,true,512,JSON_THROW_ON_ERROR),
        file($directory.'/account-email-recovery-events.jsonl',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
    $events=array_values(array_filter($events,fn($event)=>$event['attempt_id']===$context['attempt_id']));
    $authorized=count(array_filter($events,fn($event)=>$event['event']==='owner_authorized_one_verification'
        &&($event['facts']['send_limit']??null)===1&&($event['facts']['reset_authorized']??null)===false))===1;
    $requests=array_values(array_filter($events,fn($event)=>$event['event']==='verification_request'));
    $responses=array_values(array_filter($events,fn($event)=>$event['event']==='backend_response'
        &&($event['facts']['operation']??null)==='verification_request'));
    if(!$authorized||count($requests)!==1||count($responses)!==1
        ||$responses[0]['facts']['http_status']!==200
        ||$responses[0]['facts']['request_id']!==$requests[0]['facts']['request_id']
        ||count($responses[0]['facts']['new_outbox'])!==1
        ||$responses[0]['facts']['new_outbox'][0]['id']!==$row->id)
        throw new RuntimeException('Fresh request/authorization evidence is incomplete or ambiguous.');
    $manifest=json_decode(file_get_contents($root.'/.migration-backup/web/.next/dev/routes-manifest.json'),true,512,JSON_THROW_ON_ERROR);
    $rewrite=$manifest['rewrites']['afterFiles']??[];
    if(count($rewrite)!==1||$rewrite[0]['destination']!=='http://127.0.0.1:8000/api/v1/:path*')
        throw new RuntimeException('Normal Customer rewrite changed.');
    $before=retrySnapshot($root);
    retrySave($directory.'/account-email-retry-pre-send.json',[
        'at'=>gmdate('c'),'attempt_id'=>$context['attempt_id'],'request_id'=>$requests[0]['facts']['request_id'],
        'delivery_id'=>$row->id,'user_id'=>144,'kind'=>'verify','expires_at'=>$row->expires_at,
        'row_sha256'=>Recovery::fingerprint($row),'job_id'=>(int)$job->id,'job_sha256'=>Recovery::fingerprint($job),
        'recipient_binding_current'=>true,'unrelated_pending'=>0,'failed_jobs'=>0,'snapshot'=>$before,
    ]);
    // Durable one-shot marker precedes any transport. Its presence prevents replay.
    retrySave($directory.'/account-email-retry-send-intent.json',[
        'at'=>gmdate('c'),'delivery_id'=>$row->id,'user_id'=>144,'kind'=>'verify','attempt_limit'=>1,
        'owner_authorization'=>'Explicit one-send authorization and owner-confirmed fresh code-entry screen',
    ]);
    Evidence::append('owner_recipient_screen_ready',[
        'user_id'=>144,'delivery_id'=>$row->id,'fresh_signup_count'=>1,
        'code_entered'=>false,'resend_clicked'=>false,'reset_requested'=>false,
        'source'=>'explicit_owner_readiness_confirmation',
    ]);
    retrySave($approvalPath,[
        'version'=>1,'owner_controlled_recipient'=>true,'disposable_or_explicitly_safe_account'=>true,
        'records'=>[[
            'delivery_id'=>$row->id,'user_id'=>144,'kind'=>'verify',
            'recipient_sha256'=>$context['recipient_sha256'],
            'expires_at'=>min(time()+180,strtotime($row->expires_at.' UTC')),
        ]],
    ]);
    $approvalCreated=true;
    if(!Policy::permitsRow($row)) throw new RuntimeException('Exact-record approval rejected.');
    try {
        $exit=$app->make(Illuminate\Contracts\Console\Kernel::class)->call('selected:account-email',['delivery'=>$row->id]);
    } finally {
        if(!unlink($approvalPath)) throw new RuntimeException('Approval removal requires immediate intervention.');
        $approvalCreated=false;
        Evidence::append('temporary_send_authority_removed',['delivery_id'=>$row->id,'approval_file_absent'=>true]);
    }
    $after=retrySnapshot($root);$changed=[];
    foreach($before['tables'] as $table=>$value) if($after['tables'][$table]!==$value) $changed[]=$table;
    $preserved=!array_diff($changed,['selected_email_deliveries','jobs','failed_jobs'])
        &&$before['schemaSha256']===$after['schemaSha256'];
    $state=DB::table('selected_email_deliveries')->where('id',$row->id)->value('state');
    $result=[
        'at'=>gmdate('c'),'delivery_id'=>$row->id,'state'=>$state,'command_exit'=>$exit,
        'pending_or_uncertain'=>DB::table('selected_email_deliveries')->whereIn('state',['PENDING','BLOCKED','SENDING','UNKNOWN'])->count(),
        'jobs'=>DB::table('jobs')->count(),'failed_jobs'=>DB::table('failed_jobs')->count(),
        'approval_file_absent'=>!file_exists($approvalPath),'changed_tables'=>$changed,
        'all_other_tables_and_schema_preserved'=>$preserved,'snapshot'=>$after,
    ];
    retrySave($directory.'/account-email-retry-result.json',$result);
    echo json_encode(array_diff_key($result,['snapshot'=>true])),"\n";
    exit($exit===0&&$state==='SENT'&&$preserved?0:1);
} catch(Throwable) {
    if($approvalCreated) {unlink($approvalPath);$approvalCreated=false;}
    fwrite(STDERR,"Single verification attempt stopped; do not retry. Inspect retained safe evidence/state. No new challenge or reset was issued.\n");
    exit(1);
} finally {
    if($approvalCreated) unlink($approvalPath);
}
