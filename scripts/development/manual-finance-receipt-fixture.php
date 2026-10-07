<?php
declare(strict_types=1);
// Isolated native receipt acceptance only. Never loads the normal .env/bootstrap.
require __DIR__.'/manual-finance-regression-bootstrap.php';

use Illuminate\Support\Facades\{DB,Facade,Storage,URL};
use Illuminate\Support\Str;

final class ManualFinanceReceiptFixture extends \Tests\Hardening\ManualFinanceHttpFixture
{
    private function receiptRuntime(string $directory): void
    {
        config(['filesystems.disks.local'=>['driver'=>'local','root'=>$directory.'/private','throw'=>true]]);
        $this->app->singleton('filesystem',fn($app)=>new \Illuminate\Filesystem\FilesystemManager($app));
        Facade::clearResolvedInstance('filesystem');
        // The isolated router bypasses RouteServiceProvider's post-boot lookup
        // refresh. Use the real route collection, including its assigned names.
        $routes=$this->app['router']->getRoutes();
        $routes->refreshNameLookups(); $routes->refreshActionLookups();
        $url=new \Illuminate\Routing\UrlGenerator($routes,
            \Illuminate\Http\Request::create('http://127.0.0.1:3138'));
        $url->setKeyResolver(fn()=>'synthetic-disposable-receipt-signing-key');
        $this->app->instance('url',$url);
        Facade::clearResolvedInstance('url');
        // Register the framework's real request signature macros, without
        // booting the normal app or its notification/provider transports.
        (new \Illuminate\Foundation\Providers\FoundationServiceProvider($this->app))
            ->registerRequestSignatureValidation();
        $this->app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class,new \Illuminate\Routing\ResponseFactory(
            \Mockery::mock(\Illuminate\Contracts\View\Factory::class),new \Illuminate\Routing\Redirector($url)));
    }

    public function provision(string $directory): array
    {
        parent::setUp();
        $this->provisionIdentities();
        $workflows=[];
        foreach ([1=>'refund',2=>'payout'] as $source=>$kind) {
            if ($source===2) {
                $this->checkout=(string)Str::uuid();
                DB::table('bookings')->insert(['id'=>2,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,
                    'total_price'=>100,'status'=>'ended','start_date'=>now()->subHour()]);
            }
            [$allocation,$context]=$this->funded(sourceId:$source);
            $w=$this->manual->request($kind==='refund'?$this->customer:$this->vendor,
                $this->requestInput($allocation,$context,$kind));
            $w=$this->manual->action($this->finance,$w['id'],'approve',$this->command($w));
            $workflows[$kind]=$this->manual->action($this->finance,$w['id'],'claim',$this->command($w));
        }
        $this->httpAuthority($directory);
        // These are disposable native tokens only, kept outside sanitized evidence.
        $tokens=[];
        foreach ([1,2,3,4,5,6,7] as $id) {
            $tokens[$id]=\App\Models\User::findOrFail($id)->createToken('synthetic-receipt-fixture')->plainTextToken;
        }
        file_put_contents($directory.'/auth.json',json_encode($tokens,JSON_THROW_ON_ERROR));
        chmod($directory.'/auth.json',0600);
        DB::statement("VACUUM INTO '".str_replace("'","''",$directory.'/native.sqlite')."'");
        chmod($directory.'/native.sqlite',0600);
        return ['directory'=>$directory,'workflows'=>$workflows,
            'isolation'=>'Fresh disposable native SQLite HTTP fixture; no normal bootstrap, providers or email'];
    }

    public function open(string $directory): \Illuminate\Foundation\Application
    {
        $app=$this->openHttpDatabase($directory);
        $this->receiptRuntime($directory);
        return $app;
    }

    public function control(string $directory,string $operation,array $input): array
    {
        $this->open($directory);
        if ($operation==='revoke') {
            $ids=DB::table('permissions')->whereIn('name',[
                'payments.refunds.evidence.view','payments.payouts.evidence.view'])->pluck('id');
            $removed=DB::table('model_has_permissions')->where('model_id',3)->whereIn('permission_id',$ids)->delete();
            return ['revoked_actor'=>3,'removed_grants'=>$removed];
        }
        if ($operation==='expired-link') {
            return ['url'=>URL::temporarySignedRoute('manual-finance.evidence',now()->subMinute(),[
                'workflow'=>$input['workflow'],'attachment'=>$input['attachment'],'actor'=>3],false)];
        }
        if ($operation==='binding-link') {
            return ['url'=>URL::temporarySignedRoute('manual-finance.evidence',now()->addMinutes(5),[
                'workflow'=>$input['workflow'],'attachment'=>$input['attachment'],'actor'=>3],false)];
        }
        if ($operation!=='snapshot') throw new RuntimeException('Unknown fixture operation.');
        $attachments=DB::table('manual_financial_attachments')->orderBy('id')->get()->map(function($a) {
            $row=(array)$a; $bytes=Storage::disk('local')->get($a->path);
            $row['retained_sha256']=hash('sha256',$bytes);
            $row['retained_bytes']=strlen($bytes);
            return $row;
        })->all();
        return ['attachments'=>$attachments,
            'evidence'=>DB::table('manual_financial_evidence')->orderBy('id')->get()->all(),
            'access'=>DB::table('manual_financial_evidence_access')->orderBy('created_at')->orderBy('id')->get()->all(),
            'effects'=>$this->exactEffects()];
    }
}

$root=dirname(__DIR__,2);
$fixture=new ManualFinanceReceiptFixture('receipt-acceptance');
if (PHP_SAPI==='cli-server') {
    if (!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)) { http_response_code(403); exit; }
    try {
        $app=$fixture->open(defined('MANUAL_FINANCE_RECEIPT_DIRECTORY')?MANUAL_FINANCE_RECEIPT_DIRECTORY:'');
        $request=\Illuminate\Http\Request::capture(); $app->instance('request',$request);
        $response=$app['router']->dispatch($request);
    } catch (\Illuminate\Auth\AuthenticationException $e) {
        $response=new \Illuminate\Http\JsonResponse(['message'=>'Unauthenticated.'],401);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        $response=new \Illuminate\Http\JsonResponse(['message'=>$e->getMessage()],$e->getStatusCode());
    } catch (\Illuminate\Validation\ValidationException $e) {
        $response=new \Illuminate\Http\JsonResponse(['message'=>'Validation failed','errors'=>$e->errors()],422);
    } catch (\Throwable $e) {
        error_log(get_class($e).': '.$e->getMessage());
        $response=new \Illuminate\Http\JsonResponse(['message'=>'Isolated receipt fixture failed.'],500);
    }
    $response->send(); exit;
}
if (PHP_SAPI!=='cli') throw new RuntimeException('Private CLI fixture only.');
$operation=$argv[1]??'';
if ($operation==='setup') {
    $directory=$root.'/.local/manual-finance/http-identity-'.bin2hex(random_bytes(8));
    mkdir($directory,0700,true); mkdir($directory.'/sessions',0700); mkdir($directory.'/private',0700);
    $manifest=$fixture->provision($directory);
    file_put_contents($directory.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n");
    file_put_contents($directory.'/router.php',"<?php\n define('MANUAL_FINANCE_RECEIPT_DIRECTORY',".
        var_export($directory,true).");\n require ".var_export(__FILE__,true).";\n");
    echo json_encode($manifest,JSON_THROW_ON_ERROR),"\n";
} else {
    $directory=$argv[2]??'';
    if (!preg_match('~^'.preg_quote($root,'~').'/\.local/manual-finance/http-identity-[a-f0-9]{16}$~D',$directory)) {
        throw new RuntimeException('Explicit owned fixture directory required.');
    }
    echo json_encode($fixture->control($directory,$operation,json_decode($argv[3]??'{}',true,512,JSON_THROW_ON_ERROR)),
        JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}
