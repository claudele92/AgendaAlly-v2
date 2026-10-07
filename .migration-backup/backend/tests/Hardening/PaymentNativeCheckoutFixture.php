<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\User;
use App\Observers\TransactionObserver;
use App\Services\PaymentAccounting\AllocationWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Empty native schema, synthetic rows, no application kernel or development
 * financial writes. The reviewed development schema is inspected read-only.
 */
abstract class PaymentNativeCheckoutFixture extends IsolatedTestCase
{
    protected AllocationWriter $writer;
    protected object $diagnosticLog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $bus=\Mockery::mock(\Illuminate\Contracts\Bus\Dispatcher::class);
        $bus->shouldReceive('dispatch')->andReturn(null); // Notification-only jobs, never financial service calls.
        $bus->shouldReceive('dispatchAfterResponse')->andReturn(null);
        $this->app->instance(\Illuminate\Contracts\Bus\Dispatcher::class,$bus);
        $this->app['config']->set('auth',[
            'defaults'=>['guard'=>'sanctum'],
            'guards'=>['sanctum'=>['driver'=>'sanctum','provider'=>'users']],
            'providers'=>['users'=>['driver'=>'eloquent','model'=>User::class]],
        ]);
        $this->app['config']->set('permission',[
            'models'=>['permission'=>\Spatie\Permission\Models\Permission::class,'role'=>\Spatie\Permission\Models\Role::class],
            'table_names'=>['roles'=>'roles','permissions'=>'permissions','model_has_roles'=>'model_has_roles',
                'model_has_permissions'=>'model_has_permissions','role_has_permissions'=>'role_has_permissions'],
            'column_names'=>['model_morph_key'=>'model_id'],
            'cache'=>['key'=>'isolated-native-permissions','store'=>'array'],
        ]);
        $this->diagnosticLog=new class extends \Psr\Log\AbstractLogger {
            public array $messages=[];
            public function log($level, \Stringable|string $message,array $context=[]): void
            { $this->messages[]=[(string)$message,$context]; }
        };
        $this->app->instance('log',$this->diagnosticLog);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('log');
        \Illuminate\Database\Eloquent\Model::clearBootedModels();
        $metadata = new \ReflectionProperty(\Illuminate\Database\Eloquent\Model::class, 'guardableColumns');
        $metadata->setValue(null, []);
        $source = new \PDO('sqlite:'.dirname(__DIR__,2).'/database/development/agendaally.sqlite');
        $source->exec('PRAGMA query_only=ON');
        foreach ($source->query("SELECT sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL") as $entry) {
            DB::unprepared($entry['sql']);
        }
        foreach ($source->query("SELECT sql FROM sqlite_master WHERE type IN ('index','trigger') AND sql IS NOT NULL") as $entry) {
            DB::unprepared($entry['sql']);
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_process','mtn_funding_event_key')) {
            (require dirname(__DIR__,2).'/database/migrations/2026_10_03_100300_add_mtn_attempt_identity.php')->up();
        }
        $this->row('currencies', ['id'=>1,'title'=>'XAF','rate'=>1,'default'=>1,'active'=>1]);
        $this->row('countries', ['id'=>1,'currency_id'=>1,'active'=>1]);
        foreach ([1,2,3] as $id) $this->row('users',['id'=>$id,'uuid'=>(string)Str::uuid(),'lang'=>'en','currency_id'=>1]);
        $this->row('languages',['locale'=>'en','default'=>1,'active'=>1]);
        $this->row('wallets',['id'=>1,'uuid'=>(string)Str::uuid(),'user_id'=>2,'currency_id'=>1,'price'=>1000]);
        foreach ([1=>'cash',2=>'wallet',3=>'mtn'] as $id=>$tag) $this->row('payments',['id'=>$id,'tag'=>$tag,'active'=>1]);
        $this->row('settings',['key'=>'service_fee','value'=>10]);
        $this->row('settings',['key'=>'service_fee_type','value'=>'fixed']);
        $this->row('settings',['key'=>'booking_service_fee','value'=>10]);
        $this->row('settings',['key'=>'booking_service_fee_type','value'=>'fixed']);
        $this->shop(1);
        $user = (new User)->forceFill(['id'=>2,'uuid'=>'fixture-customer','lang'=>'en','currency_id'=>1]);
        $user->setRelation('roles',collect([]));
        $user->setRelation('countryAdmin',(object)['country_id'=>null]);
        $guard = \Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(2);
        $guard->shouldReceive('check')->andReturn(true);
        $auth = \Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $auth->shouldReceive('guard')->andReturn($guard);
        $this->app->instance('auth',$auth);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('auth');
        \App\Models\Transaction::observe(TransactionObserver::class);
        $this->writer = new AllocationWriter;
    }

    protected function row(string $table, array $values): int
    {
        $columns = DB::select('PRAGMA table_info("'.$table.'")');
        $names = [];
        foreach ($columns as $column) {
            $names[] = $column->name;
            if ($column->notnull && $column->dflt_value === null && !$column->pk && !array_key_exists($column->name,$values)) {
                $values[$column->name] = preg_match('/INT|REAL|NUM|DEC|DOUBLE|FLOAT/i',$column->type) ? 0 : 'fixture';
            }
        }
        $values = array_intersect_key($values,array_flip($names));
        DB::table($table)->insert($values);
        return (int) ($values['id'] ?? DB::getPdo()->lastInsertId());
    }

    protected function shop(int $id): void
    {
        $this->row('shops',['id'=>$id,'uuid'=>(string)Str::uuid(),'user_id'=>$id===1?1:3,
            'status'=>'approved','tax'=>0,'percentage'=>0,'delivery_type'=>2,'collect_via_platform'=>1]);
        foreach ([1,2] as $type) $this->row('shop_locations',['shop_id'=>$id,'region_id'=>1,'country_id'=>1,'type'=>$type]);
    }

    protected function cart(int $shops = 1): \App\Models\Cart
    {
        $this->row('carts',['id'=>1,'owner_id'=>2,'currency_id'=>1,'rate'=>1,'region_id'=>1,'total_price'=>90*$shops]);
        $this->row('user_carts',['id'=>1,'cart_id'=>1,'user_id'=>2,'uuid'=>(string)Str::uuid()]);
        for ($shop=1;$shop<=$shops;$shop++) {
            if ($shop>1) $this->shop($shop);
            $this->row('products',['id'=>$shop,'uuid'=>(string)Str::uuid(),'shop_id'=>$shop,'tax'=>0,'active'=>1,'status'=>'published','min_qty'=>1,'max_qty'=>100]);
            $this->row('stocks',['id'=>$shop,'product_id'=>$shop,'price'=>90,'quantity'=>50,'tax'=>0]);
            $this->row('cart_details',['id'=>$shop,'user_cart_id'=>1,'shop_id'=>$shop]);
            $this->row('cart_detail_products',['cart_detail_id'=>$shop,'stock_id'=>$shop,'quantity'=>1,'bonus'=>0,
                'price'=>90,'discount'=>0,'tax'=>0,'total_price'=>90]);
        }
        return \App\Models\Cart::findOrFail(1);
    }

    protected function booking(array $overrides = []): \App\Models\Booking
    {
        $this->row('services',['id'=>1,'category_id'=>1,'shop_id'=>1,'active'=>1]);
        $this->row('service_masters',['id'=>1,'service_id'=>1,'master_id'=>1,'shop_id'=>1,'price'=>90,'discount'=>0,'commission_fee'=>0,'active'=>1]);
        $id=$this->row('bookings',array_replace(['id'=>1,'service_master_id'=>1,'service_id'=>1,'master_id'=>1,
            'shop_id'=>1,'shop_location_id'=>2,'user_id'=>2,'currency_id'=>1,'price'=>90,'discount'=>0,
            'commission_fee'=>0,'service_fee'=>10,'coupon_price'=>0,'gift_cart_price'=>0,'extra_price'=>0,
            'rate'=>1,'total_price'=>100,'status'=>'new','start_date'=>'2026-10-15 10:00:00','end_date'=>'2026-10-15 11:00:00'],$overrides));
        $model=\App\Models\Booking::findOrFail($id);
        $model->wasRecentlyCreated=true;
        return $model;
    }

    protected function order(): \App\Models\Order
    {
        $this->row('orders',['id'=>1,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,'rate'=>1,'total_price'=>100,
            'commission_fee'=>0,'service_fee'=>10,'total_tax'=>0,'coupon_price'=>0,'tips'=>0,'delivery_fee'=>0]);
        $model=\App\Models\Order::findOrFail(1);
        $model->wasRecentlyCreated=true;
        return $model;
    }
}