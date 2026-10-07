<?php
declare(strict_types=1);
// CLI-only, disposable native controller fixture. Never boots the normal app/database.
if (PHP_SAPI!=='cli') throw new RuntimeException('CLI-only acceptance fixture.');
$root=dirname(__DIR__,2);
require $root.'/.migration-backup/backend/vendor/autoload.php';
final class ManualFinanceBrowserFixture extends \Tests\Hardening\ManualFinancialWorkflowTest
{
    public function bootFixture(string $path): void
    {
        parent::setUp();
        $this->app->instance('files',new \Illuminate\Filesystem\Filesystem);
        \Illuminate\Support\Facades\Schema::create('languages',function(\Illuminate\Database\Schema\Blueprint $t) {
            $t->id(); $t->string('locale'); $t->boolean('default')->default(false);
        });
        \Illuminate\Support\Facades\Schema::table('currencies',fn(\Illuminate\Database\Schema\Blueprint $t)=>$t->boolean('default')->default(false));
        \Illuminate\Support\Facades\Schema::create('shop_locations',function(\Illuminate\Database\Schema\Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('shop_id'); $t->unsignedBigInteger('country_id');
        });
        if (!file_exists($path)) {
            \Illuminate\Support\Facades\DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
            [$id,$context]=$this->funded();
            $this->manual->request($this->customer,array_replace($this->requestInput($id,$context),['amount_units'=>'5000']));
            foreach ([2,3,4,5] as $sourceId) {
                $this->checkout=(string)\Illuminate\Support\Str::uuid();
                \Illuminate\Support\Facades\DB::table('bookings')->insert(['id'=>$sourceId,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,
                    'total_price'=>100,'status'=>'ended','start_date'=>now()->subHour()]);
                [$id,$context]=$this->funded(sourceId:$sourceId);
                if ($sourceId===2) $this->manual->request($this->vendor,$this->requestInput($id,$context,'payout'));
            }
            \Illuminate\Support\Facades\DB::statement("VACUUM INTO '".str_replace("'","''",$path)."'");
            chmod($path,0600);
        }
        config(['database.connections.hardening.database'=>$path]);
        \Illuminate\Support\Facades\DB::purge('hardening');
        \Illuminate\Support\Facades\DB::connection()->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
    }
    public function dispatchFixture(array $input): array
    {
        $actor=match($input['actor']??'finance') { 'customer'=>$this->customer,'vendor'=>$this->vendor,default=>$this->finance };
        $guard=\Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($actor); $guard->shouldReceive('id')->andReturn($actor->id);
        $guard->shouldReceive('check')->andReturn(true);
        $auth=\Mockery::mock(\Illuminate\Contracts\Auth\Factory::class); $auth->shouldReceive('guard')->andReturn($guard);
        $this->app->instance('auth',$auth); \Illuminate\Support\Facades\Facade::clearResolvedInstance('auth');
        \Illuminate\Http\Request::macro('validate',function(array $rules) {
            return \Illuminate\Support\Facades\Validator::make($this->all(),$rules)->validate();
        });
        $request=\Illuminate\Http\Request::create($input['path']??'/','POST',$input['body']??[]);
        $controller=new \App\Http\Controllers\API\v1\ManualFinanceController;
        return match($input['operation']??'list') {
            'capabilities'=>$controller->capabilities(),
            'sources'=>$controller->sources($request),
            'detail'=>$controller->show($input['id']),
            'action'=>$controller->action($request,$input['id'],$input['action']),
            'create'=>$controller->store($request),
            default=>$controller->index($request),
        };
    }
}
// Separate from the immutable interrupted campaign; never reopen normal storage.
$path=$root.'/.local/manual-finance/browser-ui-acceptance.sqlite';
$fixture=new ManualFinanceBrowserFixture('test_customer_refund_request_reserves_once_and_changed_intent_conflicts');
$fixture->bootFixture($path);
try {
    $input=json_decode($argv[1]??'{}',true,512,JSON_THROW_ON_ERROR);
    echo json_encode(['status'=>200,'body'=>$fixture->dispatchFixture($input)],JSON_THROW_ON_ERROR),"\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo json_encode(['status'=>$e->getStatusCode(),'body'=>['message'=>$e->getMessage()]]),"\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo json_encode(['status'=>422,'body'=>['message'=>'Validation failed','errors'=>$e->errors()]]),"\n";
}
