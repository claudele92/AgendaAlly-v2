<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Transaction;
use App\Services\BookingService\BookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionProperty;

/** Real native finance/auth; only resource presentation and push transport stubbed. */
trait BookingRefundPresentation
{
    public function bookingStatusUpdateNotify(Booking $booking): void {}
    public function successResponse(string $message = '', $data = null): \Illuminate\Http\JsonResponse
    { return new \Illuminate\Http\JsonResponse(['status' => true]); }
}
final class RefundSellerController extends \App\Http\Controllers\API\v1\Dashboard\Seller\BookingController
{ use BookingRefundPresentation; }
final class RefundAdminController extends \App\Http\Controllers\API\v1\Dashboard\Admin\BookingController
{ use BookingRefundPresentation; }
final class RefundUserController extends \App\Http\Controllers\API\v1\Dashboard\User\BookingController
{ use BookingRefundPresentation; }
final class RefundMasterController extends \App\Http\Controllers\API\v1\Dashboard\Master\BookingController
{ use BookingRefundPresentation; }

abstract class BookingRefundFixture extends ProductRefundFixture
{
    protected BookingService $bookingService;
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('service_masters', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('master_id'); $t->integer('service_id'); $t->integer('shop_id');
            $t->boolean('active')->default(true); $t->integer('interval')->default(60); $t->integer('pause')->default(0);
        });
        Schema::create('services',function(Blueprint $t):void{
            $t->increments('id');$t->integer('shop_id');$t->string('status');
        });
        foreach(['users'=>'active','shops'=>'status'] as $table=>$column) if(!Schema::hasColumn($table,$column)) {
            Schema::table($table,fn(Blueprint $t)=>$column==='active'
                ? $t->boolean($column)->default(false) : $t->string($column)->nullable());
        }
        if(!Schema::hasColumn('invitations','role'))Schema::table('invitations',fn(Blueprint $t)=>$t->string('role')->nullable());
        Schema::create('user_working_days',function(Blueprint $t):void{
            $t->increments('id');$t->integer('user_id');$t->string('day');$t->string('from');$t->string('to');
            $t->boolean('disabled')->default(false);
        });
        Schema::create('master_closed_dates',function(Blueprint $t):void{
            $t->increments('id');$t->integer('master_id');$t->date('date')->nullable();
        });
        Schema::create('master_disabled_times',function(Blueprint $t):void{
            $t->increments('id');$t->integer('master_id');$t->date('date')->nullable();$t->string('from')->nullable();
            $t->string('to')->nullable();$t->string('repeats')->nullable();$t->boolean('can_booking')->default(false);
        });
        Schema::create('bookings', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('shop_id'); $t->integer('user_id')->nullable();
            $t->integer('service_master_id');
            $t->integer('service_id');
            $t->integer('master_id'); $t->integer('parent_id')->nullable(); $t->integer('local_client_id')->nullable();
            $t->integer('currency_id')->default(1); $t->decimal('total_price')->default(50);
            $t->decimal('service_fee')->default(5); $t->decimal('commission_fee')->default(0);
            $t->decimal('coupon_price')->default(0); $t->boolean('collect_via_platform')->default(true);
            $t->string('status')->default('booked'); $t->text('canceled_note')->nullable();
            $t->dateTime('start_date'); $t->dateTime('end_date'); $t->timestamps();
        });
        foreach (['point_histories', 'booking_activities'] as $name) {
            Schema::create($name, function (Blueprint $t) use ($name): void {
                $t->increments('id'); $t->integer('user_id'); $t->timestamps();
                if ($name === 'point_histories') {
                    $t->string('model_type'); $t->integer('model_id'); $t->decimal('price');
                } else { $t->integer('booking_id'); $t->string('type'); $t->text('note')->nullable(); }
            });
        }
        Schema::create('settings', function (Blueprint $t): void {
            $t->increments('id'); $t->string('key'); $t->string('value')->nullable();
        });
        Schema::create('booking_clients', fn (Blueprint $t) => $t->increments('id'));
        $db = $this->database;
        $db->table('settings')->insert(['key' => 'booking_canceled_commission', 'value' => '10']);
        $db->table('payments')->insert([['id' => 2, 'tag' => 'cash', 'active' => 1], ['id' => 3, 'tag' => 'stripe', 'active' => 0]]);
        $db->table('users')->where('id',3)->update(['active'=>true]);
        $db->table('shops')->whereIn('id',[1,2])->update(['status'=>\App\Models\Shop::APPROVED]);
        foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
            $db->table('user_working_days')->insert(['user_id'=>3,'day'=>$day,'from'=>'09:00','to'=>'18:00','disabled'=>false]);
        }
        foreach ([1, 2] as $id) {
            $db->table('services')->insert(['id'=>$id,'shop_id'=>$id,'status'=>\App\Models\Service::STATUS_ACCEPTED]);
            // Current specialist membership, with no financial/staff grants.
            $db->table('shop_roles')->insert(['id'=>100+$id,'shop_id'=>$id,'name'=>'Fixture specialist']);
            $db->table('invitations')->insert(['shop_id'=>$id,'user_id'=>3,'role'=>'master',
                'shop_role_id'=>100+$id,'status'=>\App\Models\Invitation::ACCEPTED]);
            $db->table('service_masters')->insert(['id'=>$id,'master_id'=>3,'service_id'=>$id,'shop_id'=>$id]);
            $start=$id===1?'2099-01-01 09:00:00':'2099-01-01 11:00:00';
            $end=$id===1?'2099-01-01 10:00:00':'2099-01-01 12:00:00';
            $db->table('bookings')->insert(['id' => $id, 'shop_id' => $id, 'user_id' => 2, 'master_id' => 3,
                'service_master_id'=>$id,'service_id'=>$id,
                'start_date'=>$start,'end_date'=>$end]);
            Transaction::create(['payable_type' => Booking::class, 'payable_id' => $id,
                'price' => 50, 'user_id' => 2, 'payment_sys_id' => 1, 'status' => 'paid']);
        }
        foreach ([1, 2] as $id) $db->table('shop_permissions')->insert([
            'id' => 10 + $id, 'key' => $id === 1 ? 'bookings.manage' : 'bookings.status']);
        foreach ([11, 12] as $id) $db->table('shop_role_permissions')->insert(['shop_role_id' => 1, 'shop_permission_id' => $id]);
        $this->bookingService = new BookingService;
        foreach (['seller' => RefundSellerController::class, 'admin' => RefundAdminController::class,
            'user' => RefundUserController::class, 'master' => RefundMasterController::class] as $role => $class) {
            $controller = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            (new ReflectionProperty($class, 'language'))->setValue($controller, 'en');
            (new ReflectionProperty(get_parent_class($class), 'service'))->setValue($controller, $this->bookingService);
            if ($role === 'seller') (new ReflectionProperty($class, 'shop'))->setValue($controller, \App\Models\Shop::find(1));
            $this->app->instance($class, $controller);
            $middleware = [\App\Http\Middleware\SanctumCheck::class,
                'role:' . ($role === 'seller' ? 'seller|moderator|shop_manager|admin' : $role)];
            if ($role === 'seller') $middleware[] = \App\Http\Middleware\CheckShopPermission::class . ':bookings.manage';
            $this->app['router']->post("/fixture/booking/$role/{id}", [$class, 'statusUpdate'])->middleware($middleware);
        }
    }
    protected function cancel(int $id = 1, array $extra = []): Booking
    { return $this->bookingService->statusUpdate($id, ['status' => 'canceled'] + $extra); }
    protected function bookingRequest(array $data, string $role = 'seller', int $id = 1): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create("/fixture/booking/$role/$id", 'POST', $data);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request); Facade::clearResolvedInstance('request');
        try { return $this->app['router']->dispatch($request); }
        catch (\Spatie\Permission\Exceptions\UnauthorizedException $e) { return new \Illuminate\Http\JsonResponse([], 403); }
        catch (\Illuminate\Http\Exceptions\HttpResponseException $e) { return $e->getResponse(); }
    }
    protected function rejects(callable $action): void
    {
        $before = $this->persistedState();
        $refused = false;
        try { $action(); }
        catch (\Exception $e) { $refused = true; }
        self::assertTrue($refused, 'Expected refusal');
        self::assertSame($before, $this->persistedState());
    }
}