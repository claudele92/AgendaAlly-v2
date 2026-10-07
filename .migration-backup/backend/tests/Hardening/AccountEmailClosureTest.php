<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Helpers\SelectedAccountEmailPolicy;
use App\Models\{EmailSetting,EmailTemplate,User};
use App\Services\AuthService\{EmailVerificationService,PasswordResetService};
use App\Services\EmailSettingService\{EmailSendService,SelectedEmailDelivery};
use App\Support\EmailPlainText;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\{DB,Schema};
use PHPMailer\PHPMailer\PHPMailer;

final class AccountEmailClosureTest extends IsolatedTestCase
{
    private string $compiled;
    protected function setUp(): void
    {
        parent::setUp();
        $this->compiled = sys_get_temp_dir().'/agendaally-email-closure-'.bin2hex(random_bytes(8));
        mkdir($this->compiled,0700);
        $this->app->instance('files',new Filesystem);
        $this->app['config']->set('view',['paths'=>[resource_path('views')],'compiled'=>$this->compiled]);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        $transactions = new DatabaseTransactionsManager;
        $this->app->instance('db.transactions',$transactions);
        DB::connection()->setTransactionManager($transactions);
        $this->app->register(\Illuminate\Bus\BusServiceProvider::class);
        $this->app->register(\Illuminate\Queue\QueueServiceProvider::class);
        $this->app['config']->set('queue',['default'=>'database','connections'=>['database'=>[
            'driver'=>'database','connection'=>'hardening','table'=>'jobs','queue'=>'default','retry_after'=>90,'after_commit'=>false,
        ]]]);
        $this->app['config']->set('development.email.mode','smtp'); // Captured local boundary only; network disabled.
        Schema::create('users',function(Blueprint $t):void{
            $t->increments('id');$t->string('email');$t->string('firstname')->nullable();$t->string('lastname')->nullable();
            $t->string('verify_token')->nullable();$t->timestamp('email_verified_at')->nullable();$t->timestamps();
        });
        Schema::create('settings',function(Blueprint $t):void{$t->id();$t->string('key');$t->text('value')->nullable();$t->timestamps();});
        Schema::create('email_templates',function(Blueprint $t):void{
            $t->id();$t->string('type');$t->string('subject')->nullable();$t->text('body')->nullable();$t->text('alt_body')->nullable();
            $t->unsignedBigInteger('email_setting_id')->nullable();$t->timestamps();
        });
        Schema::create('email_subscriptions',function(Blueprint $t):void{$t->id();$t->unsignedInteger('user_id');$t->boolean('active');$t->timestamps();});
        Schema::create('password_resets',function(Blueprint $t):void{$t->string('email')->index();$t->string('token')->index();$t->timestamp('created_at')->nullable();});
        Schema::create('jobs',function(Blueprint $t):void{
            $t->id();$t->string('queue')->index();$t->longText('payload');$t->unsignedTinyInteger('attempts');$t->unsignedInteger('reserved_at')->nullable();
            $t->unsignedInteger('available_at');$t->unsignedInteger('created_at');
        });
        Schema::create('languages',function(Blueprint $t):void{$t->id();$t->string('locale');$t->boolean('default');});
        Schema::create('currencies',function(Blueprint $t):void{
            $t->id();$t->string('title')->nullable();$t->string('symbol')->nullable();$t->string('position')->nullable();
            $t->decimal('rate',10,4)->default(1);$t->boolean('default');$t->boolean('active');
        });
        DB::table('languages')->insert(['locale'=>'en','default'=>true]);
        DB::table('currencies')->insert(['symbol'=>'XAF','rate'=>1,'default'=>true,'active'=>true]);
        (require database_path('migrations/2026_10_07_010000_add_selected_email_deliveries.php'))->up();
        DB::table('settings')->insert([
            ['key'=>'title','value'=>'AgendaAlly'],['key'=>'logo','value'=>'http://localhost:8000/storage/images/settings/agendaally-platform-logo.png'],
            ['key'=>'instagram','value'=>'https://www.instagram.com/agendaally'],['key'=>'facebook','value'=>'https://www.facebook.com/AgendaAlly/'],
            ['key'=>'linkedin','value'=>'https://www.linkedin.com/company/agendaally'],['key'=>'twitter','value'=>''],
        ]);
        ClosureCaptureMailer::$messages=[];ClosureCaptureMailer::$failAddress=null;ClosureCaptureMailer::$negativeAddress=null;
        $this->app->instance(EmailSendService::class,new ClosureCaptureSender);
    }
    protected function tearDown():void
    {
        (new Filesystem)->deleteDirectory($this->compiled);
        parent::tearDown();
    }
    private function user(int $id=1):User
    {
        DB::table('users')->insert(['id'=>$id,'email'=>"recipient$id@example.invalid",'firstname'=>'Synthetic','created_at'=>now(),'updated_at'=>now()]);
        return User::findOrFail($id);
    }
    private function issue(string $kind='verify',int $id=1):array
    {
        $user=$this->user($id);
        $challenge=$kind==='verify'?(new EmailVerificationService)->issue($user):(new PasswordResetService)->issueEmailToken($user);
        $delivery=(new SelectedEmailDelivery)->enqueue($user->fresh(),$kind,$challenge);
        return [$user->fresh(),$challenge,$delivery];
    }
    private function recoveryEntry(string $id):array
    {
        $row=DB::table('selected_email_deliveries')->find($id);
        $job=DB::table('jobs')->get()->first(fn($job)=>\App\Services\EmailSettingService\SelectedEmailRecovery::jobIdentity($job)===$id);
        return ['id'=>$id,'row_sha256'=>\App\Services\EmailSettingService\SelectedEmailRecovery::fingerprint($row),
            'job_id'=>$job->id,'job_sha256'=>\App\Services\EmailSettingService\SelectedEmailRecovery::fingerprint($job)];
    }
    public function test_recovery_cancels_only_exact_jobs_retains_evidence_and_revokes_only_matching_reset():void
    {
        [$user,$challenge,$verify]=$this->issue();
        $resetChallenge=(new PasswordResetService)->issueEmailToken($user);
        $reset=(new SelectedEmailDelivery)->enqueue($user,'reset',$resetChallenge);
        [$other,$otherChallenge,$unrelated]=$this->issue('reset',2);
        $otherRow=(array)DB::table('selected_email_deliveries')->find($unrelated);
        $otherJob=(array)DB::table('jobs')->where('id',$this->recoveryEntry($unrelated)['job_id'])->first();
        $before=(array)DB::table('users')->find($user->id);
        $receipt=(new \App\Services\EmailSettingService\SelectedEmailRecovery)->cancel($user->id,[
            $this->recoveryEntry($verify),$this->recoveryEntry($reset)]);
        self::assertCount(2,$receipt);self::assertSame(1,DB::table('jobs')->count());
        self::assertSame(3,DB::table('selected_email_deliveries')->count());
        self::assertSame('EXPIRED',DB::table('selected_email_deliveries')->where('id',$verify)->value('state'));
        self::assertFalse((new PasswordResetService)->isCurrentEmailToken($user,$resetChallenge));
        self::assertTrue((new PasswordResetService)->isCurrentEmailToken($other,$otherChallenge));
        self::assertSame($otherRow,(array)DB::table('selected_email_deliveries')->find($unrelated));
        self::assertSame($otherJob,(array)DB::table('jobs')->find($otherJob['id']));
        self::assertSame($before,(array)DB::table('users')->find($user->id));
        self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_recovery_refuses_attempted_job_and_rolls_back_entire_selection():void
    {
        [$user,$challenge,$first]=$this->issue();
        $second=(new SelectedEmailDelivery)->enqueue($user,'verify','123456');
        $entries=[$this->recoveryEntry($first),$this->recoveryEntry($second)];
        DB::table('jobs')->where('id',$entries[1]['job_id'])->update(['attempts'=>1]);
        try {(new \App\Services\EmailSettingService\SelectedEmailRecovery)->cancel($user->id,$entries);self::fail('Unsafe job accepted.');}
        catch(\RuntimeException) {}
        self::assertSame(2,DB::table('selected_email_deliveries')->where('state','PENDING')->count());
        self::assertSame(2,DB::table('jobs')->count());self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_recovery_refuses_wrong_recipient_and_preserves_newer_reset_challenge():void
    {
        [$user,$challenge,$reset]=$this->issue('reset');
        $new=(new PasswordResetService)->issueEmailToken($user);
        (new \App\Services\EmailSettingService\SelectedEmailRecovery)->cancel($user->id,[$this->recoveryEntry($reset)]);
        self::assertTrue((new PasswordResetService)->isCurrentEmailToken($user,$new));
        [$other,$otherChallenge,$verify]=$this->issue('verify',2);
        try {(new \App\Services\EmailSettingService\SelectedEmailRecovery)->cancel($user->id,[$this->recoveryEntry($verify)]);self::fail('Wrong recipient accepted.');}
        catch(\RuntimeException) {}
        self::assertSame('PENDING',DB::table('selected_email_deliveries')->where('id',$verify)->value('state'));
    }
    public function test_verification_response_evidence_captures_transition_without_code_email_or_token():void
    {
        $user=$this->user();
        Schema::create('personal_access_tokens',function(Blueprint $t):void{
            $t->id();$t->string('tokenable_type');$t->unsignedBigInteger('tokenable_id');
        });
        $middleware=new class extends \App\Http\Middleware\AccountEmailEvidence {
            public array $events=[];
            public array $fixtureContext=[];
            protected function context():?array { return $this->fixtureContext; }
            protected function record(string $event,array $facts):void { $this->events[]=['event'=>$event,'facts'=>$facts]; }
        };
        $middleware->fixtureContext=['user_id'=>$user->id,'recipient_sha256'=>hash('sha256',strtolower(trim($user->email)))];
        $request=\Illuminate\Http\Request::create('/api/v1/auth/verify/email','POST',['email'=>$user->email,'otp'=>'654321']);
        $response=$middleware->handle($request,function()use($user){
            $user->forceFill(['email_verified_at'=>now()])->save(); // Isolated synthetic database only.
            return new \Illuminate\Http\JsonResponse(['statusCode'=>'OK','data'=>['access_token'=>'synthetic-secret-never-log']]);
        });
        self::assertSame(200,$response->getStatusCode());
        self::assertSame('verification_submission',$middleware->events[0]['event']);
        $facts=$middleware->events[1]['facts'];
        self::assertFalse($middleware->events[0]['facts']['verified_before']);
        self::assertTrue($facts['verified_after']);self::assertNotNull($facts['verification_timestamp']);
        self::assertSame(200,$facts['http_status']);
        $encoded=json_encode($middleware->events,JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('654321',$encoded);
        self::assertStringNotContainsString($user->email,$encoded);
        self::assertStringNotContainsString('synthetic-secret-never-log',$encoded);
        self::assertNotNull($response->headers->get('X-AgendaAlly-Acceptance-Request'));
    }
    public function test_actual_migration_unique_event_and_empty_rollback_contract():void
    {
        self::assertTrue(Schema::hasTable('selected_email_deliveries'));
        foreach(['id','event_key','user_id','kind','encrypted_payload','state','error_code','expires_at','claimed_at','sent_at','created_at','updated_at'] as $column)
            self::assertTrue(Schema::hasColumn('selected_email_deliveries',$column));
        $migration=require database_path('migrations/2026_10_07_010000_add_selected_email_deliveries.php');
        $migration->down();self::assertFalse(Schema::hasTable('selected_email_deliveries'));$migration->up();
        [$user,$challenge,$id]=$this->issue();
        self::assertSame($id,(new SelectedEmailDelivery)->enqueue($user,'verify',$challenge));
        self::assertSame(1,DB::table('selected_email_deliveries')->count());
        $payload=DB::table('selected_email_deliveries')->value('encrypted_payload');
        self::assertStringNotContainsString($challenge,$payload);
        self::assertStringNotContainsString($user->email,$payload);
        try{$migration->down();self::fail('Populated rollback must not erase evidence.');}catch(\RuntimeException){self::assertTrue(Schema::hasTable('selected_email_deliveries'));}
    }
    public function test_after_commit_queue_and_rollback_never_produce_an_email():void
    {
        $user=$this->user();$challenge=(new EmailVerificationService)->issue($user);
        DB::beginTransaction();(new SelectedEmailDelivery)->enqueue($user->fresh(),'verify',$challenge);
        self::assertSame(0,DB::table('jobs')->count());DB::rollBack();
        self::assertSame(0,DB::table('selected_email_deliveries')->count());self::assertSame(0,DB::table('jobs')->count());
        DB::beginTransaction();(new SelectedEmailDelivery)->enqueue($user->fresh(),'verify',$challenge);
        self::assertSame(0,DB::table('jobs')->count());DB::commit();self::assertSame(1,DB::table('jobs')->count());
        self::assertSame('mvp-notifications',DB::table('jobs')->value('queue'));self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_one_isolated_native_worker_job_does_not_consume_the_shared_queue():void
    {
        [,,$selected]=$this->issue('verify',1);
        [,,$unrelated]=$this->issue('verify',2);
        $payload=json_decode(DB::table('jobs')->where('payload','like','%'.$selected.'%')->value('payload'),true);
        $expected=(new \App\Jobs\SelectedAccountEmail($selected))->onConnection('database')->onQueue('mvp-notifications')->afterCommit();
        self::assertSame(serialize($expected),$payload['data']['command']);
        $queue='account-email-acceptance-'.$selected;
        self::assertSame(1,DB::table('jobs')->where('payload','like','%'.$selected.'%')->update(['queue'=>$queue]));
        $this->app->instance(\Illuminate\Contracts\Debug\ExceptionHandler::class,new \Illuminate\Foundation\Exceptions\Handler($this->app));
        $options=new \Illuminate\Queue\WorkerOptions;
        $options->maxTries=1;$options->sleep=0;$options->timeout=30;
        $this->app['queue.worker']->runNextJob('database',$queue,$options);
        self::assertSame('SENT',DB::table('selected_email_deliveries')->where('id',$selected)->value('state'));
        self::assertSame('PENDING',DB::table('selected_email_deliveries')->where('id',$unrelated)->value('state'));
        self::assertSame(1,DB::table('jobs')->count());self::assertSame('mvp-notifications',DB::table('jobs')->value('queue'));
        self::assertCount(1,ClosureCaptureMailer::$messages);
    }
    public function test_verify_actual_sender_mime_delivery_duplicate_and_consumption():void
    {
        [$user,$challenge,$id]=$this->issue();
        $delivery=new SelectedEmailDelivery;
        self::assertSame('SENT',$delivery->deliver($id));self::assertSame('SENT',$delivery->deliver($id));
        self::assertCount(1,ClosureCaptureMailer::$messages);$message=ClosureCaptureMailer::$messages[0];
        self::assertCount(1,$message['to']);self::assertSame($user->email,$message['to'][0][0]);
        self::assertStringContainsString($challenge,$message['text']);self::assertStringContainsString('Verify your email address',$message['text']);
        self::assertStringContainsString('multipart/alternative',$message['mime']);self::assertStringContainsString('multipart/related',$message['mime']);
        self::assertCount(4,$message['attachments']);self::assertNull((new EmailVerificationService)->consume('other@example.invalid',$challenge));
        self::assertNotNull((new EmailVerificationService)->consume($user->email,$challenge));
        self::assertNull((new EmailVerificationService)->consume($user->email,$challenge));self::assertNotNull($user->fresh()->email_verified_at);
        self::assertFalse($this->app->bound('agendaally.selected_account_delivery'));
    }
    public function test_reset_has_independent_purpose_and_single_use_bound_recipient():void
    {
        // A configured verification template MUST NOT override password-reset purpose.
        DB::table('email_templates')->insert(['type'=>'verify','subject'=>'VERIFICATION ONLY','body'=>'Verify $verify_code','alt_body'=>'Verify $verify_code']);
        [$user,$challenge,$id]=$this->issue('reset');
        self::assertSame('SENT',(new SelectedEmailDelivery)->deliver($id));
        $message=ClosureCaptureMailer::$messages[0];
        self::assertSame('Reset password',$message['subject']);self::assertStringNotContainsString('VERIFICATION ONLY',$message['html']);
        self::assertStringContainsString($challenge,$message['text']);
        self::assertNull((new PasswordResetService)->consumeEmailToken($challenge,'other@example.invalid'));
        self::assertNotNull((new PasswordResetService)->consumeEmailToken($challenge,$user->email));
        self::assertNull((new PasswordResetService)->consumeEmailToken($challenge,$user->email));
    }
    public function test_superseded_expired_and_consumed_requests_never_send():void
    {
        [$user,$old,$id]=$this->issue();
        $new=(new EmailVerificationService)->issue($user);
        self::assertSame('EXPIRED',(new SelectedEmailDelivery)->deliver($id));
        $next=(new SelectedEmailDelivery)->enqueue($user->fresh(),'verify',$new);
        \Illuminate\Support\Carbon::setTestNow(now()->addMinutes(11));
        self::assertSame('EXPIRED',(new SelectedEmailDelivery)->deliver($next));
        self::assertNull((new EmailVerificationService)->consume($user->email,$new));self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_reset_repeat_and_expiry_are_bound_without_duplicate_state():void
    {
        [$user,$old,$id]=$this->issue('reset');
        $new=(new PasswordResetService)->issueEmailToken($user);
        self::assertSame('EXPIRED',(new SelectedEmailDelivery)->deliver($id));
        self::assertNull((new PasswordResetService)->consumeEmailToken($old,$user->email));
        $next=(new SelectedEmailDelivery)->enqueue($user,'reset',$new);
        \Illuminate\Support\Carbon::setTestNow(now()->addMinutes(61));
        self::assertSame('EXPIRED',(new SelectedEmailDelivery)->deliver($next));
        self::assertNull((new PasswordResetService)->consumeEmailToken($new,$user->email));self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_live_claim_and_lost_ack_are_terminal_without_resend():void
    {
        [,,$id]=$this->issue();
        DB::table('selected_email_deliveries')->where('id',$id)->update(['state'=>'SENDING','claimed_at'=>now()]);
        self::assertSame('BUSY',(new SelectedEmailDelivery)->deliver($id));
        \Illuminate\Support\Carbon::setTestNow(now()->addSeconds(91));
        self::assertSame('UNKNOWN',(new SelectedEmailDelivery)->deliver($id));
        self::assertSame('UNKNOWN',(new SelectedEmailDelivery)->deliver($id));self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_failed_ack_and_disabled_transport_do_not_retry():void
    {
        [$user,,$id]=$this->issue();$this->app['config']->set('development.email.mode','log');
        self::assertSame('BLOCKED',(new SelectedEmailDelivery)->deliver($id));self::assertSame([],ClosureCaptureMailer::$messages);
        $this->app['config']->set('development.email.mode','smtp');ClosureCaptureMailer::$failAddress=$user->email;
        self::assertSame('UNKNOWN',(new SelectedEmailDelivery)->deliver($id));self::assertSame('UNKNOWN',(new SelectedEmailDelivery)->deliver($id));
        self::assertSame('SMTP_ACK_UNCERTAIN',DB::table('selected_email_deliveries')->value('error_code'));
        self::assertFalse($this->app->bound('agendaally.selected_account_delivery'));
    }
    public function test_negative_transport_ack_never_marks_sent_or_retries():void
    {
        [$user,,$id]=$this->issue();
        ClosureCaptureMailer::$negativeAddress=$user->email;
        self::assertSame('UNKNOWN',(new SelectedEmailDelivery)->deliver($id));
        self::assertSame('UNKNOWN',(new SelectedEmailDelivery)->deliver($id));
        self::assertNull(DB::table('selected_email_deliveries')->value('sent_at'));
        self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_consumed_code_and_changed_recipient_cannot_be_delivered():void
    {
        [$user,$code,$id]=$this->issue();
        self::assertNotNull((new EmailVerificationService)->consume($user->email,$code));
        self::assertSame('EXPIRED',(new SelectedEmailDelivery)->deliver($id));
        [$other,,$next]=$this->issue('reset',2);
        DB::table('users')->where('id',$other->id)->update(['email'=>'changed@example.invalid']);
        self::assertSame('EXPIRED',(new SelectedEmailDelivery)->deliver($next));
        self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_each_subscription_has_one_private_envelope_and_isolated_failure():void
    {
        $first=$this->user();$second=$this->user(2);$inactive=$this->user(3);
        DB::table('email_subscriptions')->insert([['user_id'=>1,'active'=>true],['user_id'=>2,'active'=>true],['user_id'=>3,'active'=>false]]);
        $template=new EmailTemplate(['subject'=>'Synthetic subscription','body'=>'<p>Common approved content</p>','alt_body'=>'Common approved content']);
        $template->setRelation('emailSetting',new EmailSetting);$template->setRelation('galleries',collect());
        $result=(new ClosureCaptureSender)->sendSubscriptions($template);
        self::assertTrue($result['status']);self::assertSame(2,$result['sent_count']);self::assertCount(2,ClosureCaptureMailer::$messages);
        foreach(ClosureCaptureMailer::$messages as $i=>$message){
            $own=$i===0?$first->email:$second->email;$other=$i===0?$second->email:$first->email;
            self::assertSame([[$own,'Synthetic']],$message['to']);self::assertSame([],$message['cc']);self::assertSame([],$message['bcc']);
            self::assertStringNotContainsString($other,$message['mime']);self::assertStringNotContainsString($inactive->email,$message['mime']);
        }
        ClosureCaptureMailer::$messages=[];ClosureCaptureMailer::$failAddress=$first->email;
        $result=(new ClosureCaptureSender)->sendSubscriptions($template);
        self::assertFalse($result['status']);self::assertSame(1,$result['sent_count']);self::assertSame(1,$result['failed_count']);
        self::assertStringNotContainsString($first->email,json_encode($result));self::assertStringNotContainsString('synthetic transport detail',json_encode($result));
        $this->app['config']->set('development.email.mode','log');ClosureCaptureMailer::$messages=[];
        self::assertFalse((new ClosureCaptureSender)->sendSubscriptions($template)['status']);self::assertSame([],ClosureCaptureMailer::$messages);
    }
    public function test_plaintext_preserves_content_without_new_money_authority():void
    {
        $text=EmailPlainText::fromHtml('<html><head><style>bad-css</style></head><body><p>Cash selected &mdash; UNPAID / UNCOLLECTED.</p><p>XAF 1,000.00</p><a href="https://example.invalid/help">Help</a></body></html>','Order summary');
        self::assertStringContainsString('UNPAID / UNCOLLECTED',$text);self::assertStringContainsString('XAF 1,000.00',$text);
        self::assertStringContainsString('Help (https://example.invalid/help)',$text);self::assertStringNotContainsString('bad-css',$text);
        self::assertStringNotContainsString('<p>',$text);self::assertStringNotContainsString('https://user:secret@',EmailPlainText::fromHtml('<a href="https://user:secret@example.invalid">Help</a>'));
    }
    public function test_approval_is_cli_only_default_off_and_never_a_global_email_mode():void
    {
        $facts=['published'=>false,'cli'=>true,'authority'=>true,'root'=>true,'database'=>true,'development'=>true,
            'owned'=>true,'tls'=>true,'queue'=>true,'debug'=>false,'firebase'=>false,'maps'=>false,'environment'=>'local','driver'=>'sqlite',
            'email'=>'log','mailer'=>'log','payments'=>'disabled','sms'=>'log'];
        self::assertTrue(SelectedAccountEmailPolicy::factsAllowed($facts));
        foreach(['cli','authority','root','database','development','owned','tls','queue'] as $key)
            self::assertFalse(SelectedAccountEmailPolicy::factsAllowed(array_replace($facts,[$key=>false])));
        foreach(['published','debug','firebase','maps'] as $key)
            self::assertFalse(SelectedAccountEmailPolicy::factsAllowed(array_replace($facts,[$key=>true])));
        self::assertFalse(SelectedAccountEmailPolicy::factsAllowed(array_replace($facts,['email'=>'smtp'])));
        foreach(['sendOrder','sendSubscriptions','sendDeliveryDriverInvitation','sendTest'] as $operation)
            self::assertFalse(SelectedAccountEmailPolicy::permitsSender($operation));
    }
}

/** Captures actual sender-generated MIME, never opens SMTP or loads a credential. */
final class ClosureCaptureSender extends EmailSendService
{
    public function emailBaseAuth(?EmailSetting $setting,User $user):PHPMailer
    {
        $mail=new ClosureCaptureMailer(true);$mail->isHTML();$mail->CharSet='UTF-8';
        $mail->setFrom('fixture@example.invalid','AgendaAlly');$mail->addAddress($user->email,'Synthetic');return $mail;
    }
}
final class ClosureCaptureMailer extends PHPMailer
{
    public static array $messages=[];
    public static ?string $failAddress=null;
    public static ?string $negativeAddress=null;
    public function send():bool
    {
        if (($this->getToAddresses()[0][0]??null)===self::$negativeAddress) return false;
        if (($this->getToAddresses()[0][0]??null)===self::$failAddress)
            throw new \RuntimeException('synthetic transport detail '.$this->getToAddresses()[0][0]);
        if (!$this->preSend()) return false;
        self::$messages[]=['to'=>$this->getToAddresses(),'cc'=>$this->getCcAddresses(),'bcc'=>$this->getBccAddresses(),
            'subject'=>$this->Subject,'html'=>$this->Body,'text'=>$this->AltBody,'mime'=>$this->getSentMIMEMessage(),'attachments'=>$this->getAttachments()];
        return true;
    }
}
