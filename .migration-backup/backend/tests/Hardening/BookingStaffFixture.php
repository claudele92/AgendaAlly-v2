<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Seller\BookingController;
use App\Http\Middleware\CheckSellerShop;
use App\Http\Middleware\CheckShopPermission;
use App\Http\Middleware\SanctumCheck;
use App\Models\Booking;
use App\Models\Transaction;
use App\Services\BookingService\BookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionProperty;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/** Stub presentation/transport only; never authorization, lifecycle or accounting. */
final class BookingStaffControllerFixture extends BookingController
{
    public function bookingStatusUpdateNotify(Booking $booking): void {}

    public function successResponse(string $message = '', $data = null): JsonResponse
    {
        return new JsonResponse(['status' => true, 'message' => $message]);
    }
}

final class RecordingBookingService extends BookingService
{
    public int $entries = 0;

    public function statusUpdate(int $id, array $filter): Booking
    {
        ++$this->entries;
        return parent::statusUpdate($id, $filter);
    }

    public function extraTime(int $id, array $data): Booking
    {
        ++$this->entries;
        return parent::extraTime($id, $data);
    }

    public function delete(?array $ids = [], array $filter = []): void
    {
        ++$this->entries;
        parent::delete($ids, $filter);
    }
}

abstract class BookingStaffFixture extends PaymentStatusFixture
{
    protected RecordingBookingService $lifecycle;
    protected int $bookingMutationEvents = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extendLifecycleSchema();
        $this->seedLifecycleFixtures();
        $this->lifecycle = new RecordingBookingService();
        Booking::updating(function (): void { ++$this->bookingMutationEvents; });
        Booking::deleting(function (): void { ++$this->bookingMutationEvents; });
        $this->staffActor(300, ['moderator']);

        $router = $this->app['router'];
        $router->aliasMiddleware('fixture.role', RoleMiddleware::class);
        foreach ([
            ['POST', 'status/update', 'statusUpdate', 'bookings.status'],
            ['POST', 'extra-time', 'extraTime', 'bookings.manage'],
            ['DELETE', null, 'destroy', 'bookings.manage'],
        ] as [$verb, $suffix, $method, $permission]) {
            $path = $suffix ? "{id}/{$suffix}" : 'delete';
            $router->match([$verb], "/api/v1/dashboard/seller/bookings/{$path}", [BookingController::class, $method])
                ->middleware([
                    SanctumCheck::class,
                    'fixture.role:seller|moderator|admin|shop_manager',
                    CheckSellerShop::class,
                    CheckShopPermission::class.':'.$permission,
                ]);
        }
    }

    protected function staffActor(?int $id, array $roles, ?int $country = null): void
    {
        $this->authenticate($id, $roles[0] ?? 'moderator', $country);
        $guard = auth('sanctum');
        $guard->shouldReceive('guest')->andReturn($id === null);
        $this->app['auth']->shouldReceive('guard')->with(null)->andReturn($guard);
        $actor = $guard->user();
        $actor?->setRelation('roles', collect(array_map(
            fn ($role) => (new Role())->forceFill(['name' => $role]), $roles
        )));
        $controller = (new ReflectionClass(BookingStaffControllerFixture::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($controller, 'language'))->setValue($controller, 'en');
        (new ReflectionProperty($controller, 'shop'))->setValue($controller, $actor?->shop ?? $actor?->moderatorShop);
        (new ReflectionProperty(BookingController::class, 'service'))->setValue($controller, $this->lifecycle);
        $this->app->instance(BookingController::class, $controller);
    }

    protected function requestBooking(int $id, array $input, string $operation = 'status/update'): \Symfony\Component\HttpFoundation\Response
    {
        $deletion = $operation === 'delete';
        $path = $deletion ? 'delete' : "{$id}/{$operation}";
        $request = Request::create("/api/v1/dashboard/seller/bookings/{$path}", $deletion ? 'DELETE' : 'POST', $input);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        try {
            return $this->app['router']->dispatch($request);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        } catch (HttpExceptionInterface $e) {
            return new JsonResponse(['status' => false], $e->getStatusCode());
        }
    }

    protected function fingerprint(): array
    {
        return parent::fingerprint() + [
            '_lifecycle_entries' => $this->lifecycle->entries,
            '_booking_mutation_events' => $this->bookingMutationEvents,
        ];
    }

    private function extendLifecycleSchema(): void
    {
        Schema::table('transactions', fn (Blueprint $t) => $t->timestamp('refund_time')->nullable());
        Schema::table('wallets', fn (Blueprint $t) => $t->integer('currency_id')->default(1));
        foreach (['users', 'shops'] as $name) {
            Schema::table($name, function (Blueprint $t) use ($name): void {
                $t->integer('b_count')->default(0); $t->decimal('b_sum')->default(0);
                foreach (['created_at', 'updated_at'] as $stamp) {
                    if (!Schema::hasColumn($name, $stamp)) {
                        $t->timestamp($stamp)->nullable();
                    }
                }
            });
        }
        Schema::table('users', function (Blueprint $t): void {
            foreach (['firstname', 'lastname', 'lang', 'firebase_token'] as $field) {
                $t->string($field)->nullable();
            }
        });
        Schema::table('bookings', function (Blueprint $t): void {
            $t->integer('service_master_id')->nullable();
            $t->integer('master_id')->nullable(); $t->integer('local_client_id')->nullable();
            $t->string('status')->default('booked'); $t->string('canceled_note')->nullable();
            $t->dateTime('start_date')->nullable(); $t->dateTime('end_date')->nullable();
        });
        Schema::table('platform_fee_ledger_entries', fn (Blueprint $t) => $t->text('note')->nullable());
        Schema::create('languages', function (Blueprint $t): void {
            $t->increments('id'); $t->string('locale'); $t->boolean('default')->default(true);
        });
        Schema::create('service_masters', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('master_id'); $t->integer('service_id'); $t->integer('shop_id');
            $t->boolean('active')->default(true); $t->integer('interval')->default(60); $t->integer('pause')->default(0);
        });
        foreach(['users'=>['active'],'services'=>['status'],'shops'=>['status']] as $table=>$columns) {
            foreach($columns as $column) if(!Schema::hasColumn($table,$column)) {
                Schema::table($table,fn(Blueprint $t)=>$column==='active'
                    ? $t->boolean($column)->default(false) : $t->string($column)->nullable());
            }
        }
        Schema::create('user_working_days',function(Blueprint $t):void{
            $t->increments('id');$t->integer('user_id');$t->string('day');$t->string('from');$t->string('to');
            $t->boolean('disabled')->default(false);
        });
        Schema::create('master_closed_dates',function(Blueprint $t):void{
            $t->increments('id');$t->integer('master_id');$t->date('date')->nullable();
        });
        Schema::create('master_disabled_times',function(Blueprint $t):void{
            $t->increments('id');$t->integer('master_id');$t->date('date')->nullable();
            $t->string('from')->nullable();$t->string('to')->nullable();$t->string('repeats')->nullable();
            $t->boolean('can_booking')->default(false);
        });
        Schema::table('invitations',fn(Blueprint $t)=>$t->string('role')->nullable());
        Schema::create('currencies', function (Blueprint $t): void {
            $t->increments('id'); $t->string('title'); $t->decimal('rate')->default(1);
            $t->boolean('default')->default(true); $t->boolean('active')->default(true);
        });
        Schema::create('settings', function (Blueprint $t): void {
            $t->increments('id'); $t->string('key'); $t->string('value');
        });
        Schema::create('booking_activities', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('booking_id'); $t->integer('user_id');
            $t->string('type'); $t->text('note')->nullable(); $t->timestamps();
        });
        Schema::create('booking_extra_times', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('booking_id'); $t->decimal('price');
            $t->integer('duration'); $t->string('duration_type'); $t->timestamps();
        });
        Schema::create('point_histories', function (Blueprint $t): void {
            $t->increments('id'); $t->string('model_type'); $t->integer('model_id');
            $t->integer('user_id'); $t->decimal('price'); $t->timestamps();
        });
        Schema::create('points', function (Blueprint $t): void {
            $t->increments('id'); $t->boolean('active'); $t->decimal('value');
            $t->string('for'); $t->string('type'); $t->decimal('price');
        });
        Schema::create('notifications', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('user_id'); $t->string('type');
        });
        $pivot = (new \App\Models\NotificationUser())->getTable();
        Schema::create($pivot, function (Blueprint $t): void {
            $t->increments('id'); $t->integer('user_id'); $t->integer('notification_id');
            $t->boolean('active')->default(false);
        });
    }

    private function seedLifecycleFixtures(): void
    {
        $db = $this->database;
        $db->table('languages')->insert(['locale' => 'en']);
        $db->table('currencies')->insert(['title' => 'fixture currency']);
        $db->table('shop_permissions')->insert([
            ['id' => 4, 'key' => 'bookings.status'], ['id' => 5, 'key' => 'bookings.manage'],
        ]);
        foreach ([1, 3] as $role) {
            foreach ([4, 5] as $permission) {
                $db->table('shop_role_permissions')->insert(['shop_role_id' => $role, 'shop_permission_id' => $permission]);
            }
        }
        // The parent already supplies specialist 999.
        $db->table('users')->insert([['id' => 500], ['id' => 501]]);
        $db->table('users')->insert(['id'=>998]);
        $db->table('users')->whereIn('id',[998,999])->update(['active'=>true]);
        $db->table('shops')->whereIn('id',[1,2])->update(['status'=>\App\Models\Shop::APPROVED]);
        $db->table('services')->whereIn('id',[101,102])->update(['status'=>\App\Models\Service::STATUS_ACCEPTED]);
        foreach([1=>999,2=>998] as $shop=>$master) {
            $db->table('invitations')->insert(['shop_id'=>$shop,'user_id'=>$master,'role'=>'master',
                'status'=>\App\Models\Invitation::ACCEPTED]);
            foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
                $db->table('user_working_days')->insert(['user_id'=>$master,'day'=>$day,'from'=>'09:00','to'=>'18:00','disabled'=>false]);
            }
        }
        $db->table('wallets')->insert([
            ['id' => 500, 'user_id' => 500, 'price' => 100],
            ['id' => 501, 'user_id' => 501, 'price' => 100],
        ]);
        $db->table('shop_locations')->where('shop_id', 2)->update(['country_id' => 10]);
        foreach ([11 => [1, 500, 3], 12 => [2, 501, 4]] as $booking => [$shop, $customer, $transaction]) {
            $master=$shop===1?999:998; // Separate specialists: no invalid global overlap in the fixture.
            $service=(int)$db->table('bookings')->where('id',$booking)->value('service_id');
            $db->table('service_masters')->insert(['id'=>$booking,'master_id'=>$master,'service_id'=>$service,'shop_id'=>$shop]);
            $db->table('bookings')->where('id', $booking)->update([
                'service_master_id'=>$booking,
                'master_id' => $master, 'user_id' => $customer,
                'start_date' => '2020-01-01 09:00:00', 'end_date' => '2020-01-01 10:00:00',
            ]);
            $db->table('transactions')->where('id', $transaction)->update([
                'payment_sys_id' => 2, 'status' => Transaction::STATUS_PAID,
            ]);
            $db->table('point_histories')->insert([
                'model_type' => Booking::class, 'model_id' => $booking, 'user_id' => $customer, 'price' => 2,
            ]);
            $db->table('platform_fee_ledger_entries')->insert([
                'transaction_id' => $transaction, 'entry_type' => 'payable', 'payable_type' => Booking::class,
                'payable_id' => $booking, 'shop_id' => $shop, 'payment_id' => 2,
                'currency_id' => 1, 'amount' => 33, 'status' => 'pending',
            ]);
        }
    }
}