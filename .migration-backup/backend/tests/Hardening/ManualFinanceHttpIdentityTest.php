<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
final class ManualFinanceHttpIdentityTest extends ManualFinanceHttpFixture
{
    private string $directory;
    private string $url;
    private array $tokens=[];
    private int $sequence=0;

    private function retain(string $name,array $data): void
    {
        $f=fopen($this->directory.'/'.$name.'.json','xb');
        if (!$f) throw new \RuntimeException('Evidence must never overwrite.');
        fwrite($f,json_encode($data,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)); fclose($f);
        chmod($this->directory.'/'.$name.'.json',0600);
    }

    private function http(string $label,?int $actor,string $method,string $suffix,array $body=[],int $expected=200,
        bool $unchanged=true,?string $cookie=null): array
    {
        $before=$this->exactEffects();
        $headers=['Accept: application/json','Content-Type: application/json'];
        if ($actor!==null) $headers[]='Authorization: Bearer '.$this->tokens[$actor];
        if ($cookie!==null) $headers[]='Cookie: finance_fixture_session='.$cookie;
        $context=stream_context_create(['http'=>['method'=>$method,'header'=>implode("\r\n",$headers),
            'content'=>json_encode($body),'ignore_errors'=>true,'timeout'=>20]]);
        $raw=file_get_contents($this->url.'/api/v1/dashboard/manual-finance'.$suffix,false,$context);
        $status=(int)explode(' ',$http_response_header[0]??'0 0')[1];
        $after=$this->exactEffects();
        // Incremental evidence precedes assertions, preserving failures too.
        $this->retain(sprintf('%03d-%s',++$this->sequence,$label),[
            'transport'=>'loopback HTTP / original native routes / real Sanctum Guard',
            'actor_id'=>$actor,'cookie_session'=>$cookie!==null,'method'=>$method,'path'=>$suffix,
            'body'=>$body,'status'=>$status,'response'=>json_decode((string)$raw,true),
            'before'=>$before,'after'=>$after,'unchanged'=>$before===$after]);
        self::assertSame($expected,$status,$label.': '.$raw);
        if ($unchanged) self::assertSame($before,$after,$label.' must preserve every non-auth row.');
        return json_decode((string)$raw,true,512,JSON_THROW_ON_ERROR);
    }

    public function test_real_http_identity_ownership_scopes_and_revocation(): void
    {
        $root=dirname(__DIR__,4);
        $this->directory=$root.'/.local/manual-finance/http-identity-'.bin2hex(random_bytes(8));
        mkdir($this->directory,0700,true); mkdir($this->directory.'/sessions',0700);
        $this->provisionIdentities();
        [$a,$context]=$this->funded();
        $refund=$this->manual->request($this->customer,$this->requestInput($a,$context));
        $this->checkout=(string)Str::uuid();
        DB::table('bookings')->insert(['id'=>2,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,'total_price'=>100,
            'status'=>'ended','start_date'=>now()->subHour()]);
        [$b,$secondContext]=$this->funded(sourceId:2);
        $payout=$this->manual->request($this->vendor,$this->requestInput($b,$secondContext,'payout'));
        $fresh=[];
        foreach ([3,4] as $source) {
            $this->checkout=(string)Str::uuid();
            DB::table('bookings')->insert(['id'=>$source,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,
                'total_price'=>100,'status'=>'ended','start_date'=>now()->subHour()]);
            $fresh[$source]=$this->funded(sourceId:$source);
        }
        $this->httpAuthority($this->directory);
        foreach ([1,2,3,4,5,6,7,8] as $id) {
            $this->tokens[$id]=User::findOrFail($id)->createToken('disposable-finance-identity')->plainTextToken;
        }
        // Native file session custody: no public test login or normal account setup.
        $session=$this->app['session']->driver(); $session->start();
        $session->put($this->app['auth']->guard('web')->getName(),3); $session->save();
        $sessionId=$session->getId();
        $prefix=\Illuminate\Cookie\CookieValuePrefix::create('finance_fixture_session',$this->app['encrypter']->getKey());
        $cookie=rawurlencode($this->app['encrypter']->encrypt($prefix.$sessionId,false));
        DB::statement("VACUUM INTO '".str_replace("'","''",$this->directory.'/native.sqlite')."'");
        chmod($this->directory.'/native.sqlite',0600);
        config(['database.connections.hardening.database'=>$this->directory.'/native.sqlite']);
        DB::purge('hardening');
        DB::connection()->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
        $this->app['auth']->forgetGuards();
        $this->app->instance('request',\Illuminate\Http\Request::create('/'));
        // Kernel fixture auth must not leave the provisioning session in CLI queries.
        $this->app['session']->forgetDrivers();
        $socket=stream_socket_server('tcp://127.0.0.1:0',$errno,$error);
        if (!$socket) throw new \RuntimeException($error);
        $address=stream_socket_get_name($socket,false); fclose($socket);
        $this->url='http://'.$address;
        file_put_contents($this->directory.'/router.php',"<?php\n define('MANUAL_FINANCE_HTTP_DIRECTORY',".
            var_export($this->directory,true).");\n require ".
            var_export($root.'/scripts/development/manual-finance-identity-http.php',true).";\n");
        $pipes=[];
        $process=proc_open([PHP_BINARY,'-S',$address,$this->directory.'/router.php'],
            [0=>['pipe','r'],1=>['file',$this->directory.'/server.log','a'],
                2=>['file',$this->directory.'/server.log','a']],$pipes,$root);
        if (!is_resource($process)) throw new \RuntimeException('Cannot start isolated HTTP server.');
        fclose($pipes[0]);
        try {
            for ($i=0;$i<100;$i++) {
                $ready=@stream_socket_client('tcp://'.$address,$errno,$error,0.1);
                if ($ready) { fclose($ready); break; }
                usleep(20000);
            }
            $this->retain('baseline',['effects'=>$this->exactEffects(),'refund'=>$refund,'payout'=>$payout,
                'isolation'=>'new disposable SQLite native schema; no original .env/bootstrap']);
            $this->http('anonymous',null,'GET','',expected:401);
            foreach ([4=>'missing-grants',5=>'foreign-country',7=>'unrelated-customer'] as $id=>$label) {
                self::assertSame([],$this->http($label.'-queue',$id,'GET','')['data']);
                foreach ([$refund,$payout] as $w) {
                    $this->http($label.'-'.$w['kind'].'-detail',$id,'GET','/'.$w['id'],expected:404);
                    $this->http($label.'-'.$w['kind'].'-approve',$id,'POST','/'.$w['id'].'/approve',
                        $this->command($w),404);
                }
            }
            foreach ([2=>$refund,1=>$payout] as $id=>$w) {
                $own=$this->http('owner-'.$w['kind'],$id,'GET','/'.$w['id'])['data'];
                self::assertSame($w['id'],$own['id']);
                self::assertArrayNotHasKey('attachments',$own);
                foreach ($own['events'] as $event) { self::assertArrayNotHasKey('reason',$event); self::assertArrayNotHasKey('actor_id',$event); }
                $other=$id===2?$payout:$refund;
                $this->http('other-kind-'.$w['kind'],$id,'GET','/'.$other['id'],expected:404);
                self::assertSame([$w['id']],array_column($this->http('owner-queue-'.$w['kind'],$id,'GET','')['data'],'id'));
                $this->http('owner-no-finance-'.$w['kind'],$id,'POST','/'.$w['id'].'/approve',$this->command($w),403);
            }
            foreach ([1=>'payout',2=>'refund'] as $id=>$kind) {
                $input=$this->requestInput($a,$context,$kind);
                $this->http('foreign-create-'.$kind,7,'POST','',$input,404);
                self::assertSame([],$this->http('foreign-sources-'.$kind,7,'GET','/sources?kind='.$kind)['data']);
                [$source,$sourceContext]=$fresh[$id===2?3:4];
                $eligible=$this->http('owner-sources-'.$kind,$id,'GET','/sources?kind='.$kind)['data'];
                self::assertContains($source,array_column($eligible,'allocation_id'));
                $input=$this->requestInput($source,$sourceContext,$kind);
                $created=$this->http('owner-create-'.$kind,$id,'POST','',$input,unchanged:false)['data'];
                self::assertSame($id,(int)$created['requester_id']);
                self::assertSame('REQUESTED',$created['state']);
                $replayed=$this->http('owner-create-replay-'.$kind,$id,'POST','',$input)['data'];
                self::assertSame($created,$replayed);
            }
            foreach ([$refund,$payout] as $w) $this->http('invited-readable-'.$w['kind'],8,'GET','/'.$w['id']);
            DB::table('shop_locations')->insert(['shop_id'=>1,'country_id'=>2]);
            foreach ([$refund,$payout] as $w) {
                $this->http('mixed-country-detail-'.$w['kind'],8,'GET','/'.$w['id'],expected:404);
                $this->http('mixed-country-act-'.$w['kind'],8,'POST','/'.$w['id'].'/approve',$this->command($w),404);
            }
            self::assertSame([],$this->http('mixed-country-queue',8,'GET','')['data']);
            DB::table('country_admins')->insert(['user_id'=>6,'country_id'=>1]);
            foreach ([$refund,$payout] as $w) {
                $this->http('mixed-direct-grant-detail-'.$w['kind'],6,'GET','/'.$w['id'],expected:404);
                $this->http('mixed-direct-grant-act-'.$w['kind'],6,'POST','/'.$w['id'].'/approve',$this->command($w),404);
            }
            DB::table('shop_locations')->where('country_id',2)->delete();
            DB::table('country_invitations')->where('user_id',8)->update(['status'=>'revoked']);
            self::assertSame([],$this->http('revoked-country-invitation-queue',8,'GET','')['data']);
            foreach ([$refund,$payout] as $w) {
                $this->http('revoked-country-invitation-'.$w['kind'],8,'GET','/'.$w['id'],expected:404);
                $this->http('revoked-country-invitation-act-'.$w['kind'],8,'POST','/'.$w['id'].'/approve',
                    $this->command($w),404);
            }
            foreach ([$refund,$payout] as $w) {
                $approved=$this->http('finance-approve-'.$w['kind'],3,'POST','/'.$w['id'].'/approve',
                    $this->command($w),unchanged:false)['data'];
                self::assertSame('APPROVED',$approved['state']);
                $claim=$this->http('finance-claim-'.$w['kind'],3,'POST','/'.$w['id'].'/claim',
                    $this->command($approved),unchanged:false)['data'];
                $completion=$this->command($claim)+['evidence'=>$this->terminalEvidence($claim)];
                $this->http('changed-actor-complete-'.$w['kind'],6,'POST','/'.$w['id'].'/complete',$completion,403);
                $this->http('stale-version-'.$w['kind'],3,'POST','/'.$w['id'].'/review',$this->command($approved),409);
                $permission=DB::table('permissions')->where('name','payments.'.
                    ($w['kind']==='refund'?'refunds':'payouts').'.complete')->value('id');
                DB::table('model_has_permissions')->where('model_id',3)->where('permission_id',$permission)->delete();
                $visible=$this->http('view-without-complete-'.$w['kind'],3,'GET','/'.$w['id'])['data'];
                self::assertNotContains('complete',$visible['actions']);
                $this->http('missing-complete-grant-'.$w['kind'],3,'POST','/'.$w['id'].'/complete',$completion,403);
                DB::table('model_has_permissions')->where('model_id',3)->delete();
                $this->http('revoked-grant-complete-'.$w['kind'],3,'POST','/'.$w['id'].'/complete',$completion,404);
                $this->http('revoked-grant-read-'.$w['kind'],3,'GET','/'.$w['id'],expected:404);
                foreach (DB::table('permissions')->pluck('id') as $permission) {
                    DB::table('model_has_permissions')->insert(['model_id'=>3,'model_type'=>User::class,'permission_id'=>$permission]);
                }
            }
            $this->http('cookie-finance',null,'GET','/'.$refund['id'],cookie:$cookie);
            // Sanctum prioritizes a valid web identity over a bearer identity.
            $this->http('cookie-actor-precedence',7,'GET','/'.$refund['id'],cookie:$cookie);
            DB::table('model_has_permissions')->where('model_id',3)->delete();
            $this->http('cookie-revoked-grants',null,'GET','/'.$refund['id'],expected:404,cookie:$cookie);
            $this->http('cookie-revoked-grants-act',null,'POST','/'.$refund['id'].'/review',
                $this->command($refund),404,cookie:$cookie);
            unlink($this->directory.'/sessions/'.$sessionId);
            $this->http('invalidated-cookie',null,'GET','',expected:401,cookie:$cookie);
            $this->http('invalidated-cookie-act',null,'POST','/'.$refund['id'].'/review',
                $this->command($refund),401,cookie:$cookie);
            $this->http('invalid-cookie-bearer-fallback',7,'GET','/'.$refund['id'],expected:404,cookie:$cookie);
            $validFinanceToken=$this->tokens[6];
            $token=User::findOrFail(6)->createToken('expired',['*'],now()->subMinute());
            $this->tokens[6]=$token->plainTextToken;
            $this->http('expired-token-read',6,'GET','',expected:401);
            $this->http('expired-token-act',6,'POST','/'.$refund['id'].'/review',$this->command($refund),401);
            $this->tokens[6]=$validFinanceToken;
            \Laravel\Sanctum\PersonalAccessToken::findToken($this->tokens[6])->delete();
            $this->http('revoked-finance-token-read',6,'GET','',expected:401);
            $this->http('revoked-finance-token-act',6,'POST','/'.$refund['id'].'/review',$this->command($refund),401);
            \Laravel\Sanctum\PersonalAccessToken::findToken($this->tokens[2])->delete();
            $this->http('revoked-token-read',2,'GET','/'.$refund['id'],expected:401);
            $this->http('revoked-token-act',2,'POST','/'.$refund['id'].'/cancel',$this->command($refund),401);
            DB::table('users')->where('id',7)->delete();
            $this->http('deleted-actor',7,'GET','',expected:401);
            self::assertSame(0,DB::table('manual_financial_evidence')->count());
            self::assertSame(0,DB::table('manual_financial_workflows')->whereNotNull('completed_at')->count());
            self::assertSame(['UNKNOWN','UNKNOWN','RESERVED','RESERVED'],
                DB::table('payment_financial_operations')->orderBy('allocation_id')->pluck('state')->all());
            $this->retain('passed',['requests'=>$this->sequence,'effects'=>$this->exactEffects(),
                'limits'=>'No native MySQL, full login/challenge, private download or production certification claimed.']);
        } finally {
            proc_terminate($process); proc_close($process);
            // Retain proof database/receipts, not reusable auth credentials/sessions.
            DB::table('personal_access_tokens')->delete();
            foreach (glob($this->directory.'/sessions/*') as $file) unlink($file);
            $this->tokens=[];
        }
    }
}
