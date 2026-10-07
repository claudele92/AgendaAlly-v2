<?php
declare(strict_types=1);

namespace Tests\Baseline;

use App\Helpers\ResponseError;
use App\Http\Resources\BookingResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderRefundResource;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\CartDetailProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderRefund;
use App\Models\Product;
use App\Models\ShopLocation;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BookingService\BookingService;
use App\Services\CartService\CartService;
use App\Services\OrderService\OrderRefundService;
use App\Services\OrderService\OrderService;
use App\Repositories\OrderRepository\OrderRefundRepository;
use App\Repositories\OrderRepository\OrderRepository;
use App\Repositories\BookingRepository\BookingRepository;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Hardening\IsolatedTestCase;

/**
 * Baselines call the original models, repositories, services and resources.
 * The schema below is purpose-built test data, not an application migration.
 */
final class OriginalDomainBaselineTest extends IsolatedTestCase
{
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        // The isolated translation service resolves its normal Filesystem
        // contract when formatting the service's original error message.
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        Cache::flush();
        \Illuminate\Support\Carbon::setTestNow('2030-01-01 08:00:00');
        $this->createSyntheticSchema();

        DB::table('currencies')->insert([
            'id' => 1, 'title' => 'Baseline currency', 'symbol' => 'B',
            'rate' => 1, 'default' => 1, 'active' => 1,
        ]);
        DB::table('languages')->insert(['id' => 1, 'locale' => 'en', 'default' => 1]);
        DB::table('countries')->insert(['id' => 1, 'currency_id' => 1, 'title' => 'Baseline']);
        DB::table('users')->insert([
            'id' => 1, 'firstname' => 'Baseline', 'lastname' => 'Customer',
            'email' => 'baseline@example.invalid', 'phone' => '+10000000000',
        ]);
        $this->customer = new User();
        $this->customer->forceFill([
            'id' => 1, 'firstname' => 'Baseline', 'lastname' => 'Customer',
            'email' => 'baseline@example.invalid', 'phone' => '+10000000000',
        ]);
        $this->customer->exists = true;
        $this->useAuthenticatedCustomer($this->customer);
        $this->app['config']->set('auth.defaults.guard', 'sanctum');
        $this->app['config']->set('permission', [
            'models' => [
                'permission' => \Spatie\Permission\Models\Permission::class,
                'role' => \Spatie\Permission\Models\Role::class,
            ],
            'table_names' => [
                'roles' => 'roles',
                'model_has_roles' => 'model_has_roles',
                'model_has_permissions' => 'model_has_permissions',
                'role_has_permissions' => 'role_has_permissions',
                'permissions' => 'permissions',
            ],
            'column_names' => [
                'role_pivot_key' => 'role_id',
                'permission_pivot_key' => 'permission_id',
                'model_morph_key' => 'model_id',
            ],
            'teams' => false,
            'cache' => ['expiration_time' => 3600, 'key' => 'baseline.permission.cache', 'store' => 'array'],
            'events_enabled' => false,
        ]);
        $this->app->singleton(\Spatie\Permission\PermissionRegistrar::class);
        $this->app->register(\Illuminate\Bus\BusServiceProvider::class);
        $this->app['config']->set('view', [
            'paths' => [],
            'compiled' => sys_get_temp_dir(),
        ]);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        $this->app->register(\Illuminate\Routing\RoutingServiceProvider::class);
    }

    public function test_cart_checkout_refund_lifecycle_and_resource_envelopes(): void
    {
        $this->app->instance(
            'request',
            \Illuminate\Http\Request::create('/api/v1/dashboard/user/cart', 'POST')
        );
        DB::table('shops')->insert([
            'id' => 1, 'user_id' => $this->customer->id, 'status' => 'approved',
            'delivery_type' => 1, 'tax' => 0, 'percentage' => 0,
            'uuid' => 'synthetic-shop', 'slug' => 'synthetic-shop',
        ]);
        DB::table('shop_locations')->insert([
            'shop_id' => 1, 'country_id' => 1, 'type' => ShopLocation::PRODUCT,
            'region_id' => 1,
        ]);
        DB::table('products')->insert([
            'id' => 1, 'shop_id' => 1, 'status' => Product::PUBLISHED,
            'active' => 1, 'min_qty' => 1, 'max_qty' => 10, 'tax' => 10,
            'digital' => 0, 'title' => 'Synthetic product',
        ]);
        DB::table('stocks')->insert([
            'id' => 1, 'product_id' => 1, 'price' => 25, 'quantity' => 8,
            'discount_expired_at' => '2030-12-31', 'o_count' => 0, 'od_count' => 0,
        ]);

        $cartService = new CartService();
        $cartResult = $cartService->create(['stock_id' => 1, 'country_id' => 1, 'quantity' => 4]);
        self::assertTrue($cartResult['status']);
        $cartEnvelope = $cartResult['data']->response(request())->getData(true);
        self::assertSame(1, (int)$cartEnvelope['data']['id']);
        self::assertSame(
            4,
            (int)$cartEnvelope['data']['user_carts'][0]['cartDetails'][0]['cartDetailProducts'][0]['quantity']
        );
        $cart = Cart::query()->firstOrFail();
        $cartProduct = CartDetailProduct::query()->firstOrFail();
        self::assertSame(4, (int)$cartProduct->quantity);
        self::assertSame(110.0, (float)$cartProduct->price);

        // Existing-cart behavior is source behavior: updateOrCreate replaces
        // this stock line's quantity and amount rather than accumulating it.
        $cartService->create(['stock_id' => 1, 'country_id' => 1, 'quantity' => 2]);
        $cartProduct->refresh();
        self::assertSame(2, (int)$cartProduct->quantity);
        self::assertSame(55.0, (float)$cartProduct->price);
        self::assertNotNull(
            $cartProduct->bonus,
            'Synthetic cart rows must receive the original boolean bonus default.'
        );

        $checkout = (new OrderService())->create([
            'cart_id' => $cart->id,
            'user_id' => $this->customer->id,
            'delivery_type' => Order::DELIVERY,
            'status' => Order::STATUS_NEW,
        ]);
        self::assertTrue(
            $checkout['status'],
            'Original order checkout failed: ' . ($checkout['code'] ?? '') . ' ' . ($checkout['message'] ?? '')
        );
        /** @var Order $order */
        $order = array_values($checkout['data'])[0];
        // Checkout assigns a random one-time code; fix only this synthetic
        // fixture field so the genuine resource envelope remains repeatable.
        $order->update(['otp' => 1111]);
        $detail = OrderDetail::where('order_id', $order->id)->firstOrFail();
        self::assertSame(2, (int)$detail->quantity);
        self::assertSame(55.0, (float)$detail->total_price);
        self::assertSame(55.0, (float)$order->total_price);
        self::assertFalse(Cart::whereKey($cart->id)->exists());

        $orderHistory = (new OrderRepository())->ordersPaginate([
            'user_id' => $this->customer->id,
            'perPage' => 10,
        ]);
        self::assertSame([$order->id], $orderHistory->getCollection()->pluck('id')->all());

        $refundCreate = (new OrderRefundService())->create([
            'order_id' => $order->id,
            'status' => OrderRefund::STATUS_PENDING,
            'cause' => 'synthetic return reason',
            'answer' => '',
        ]);
        self::assertTrue($refundCreate['status'], 'Original refund create failed: ' . ($refundCreate['message'] ?? ''));
        $refund = OrderRefund::where('order_id', $order->id)->firstOrFail();
        self::assertSame(OrderRefund::STATUS_PENDING, OrderRefund::where('order_id', $order->id)->firstOrFail()->status);
        $refundHistory = (new OrderRefundRepository())->paginate([
            'user_id' => $this->customer->id,
            'perPage' => 10,
        ]);
        $refundHistoryIds = $refundHistory->getCollection()->pluck('id')->all();
        self::assertSame([$refund->id], $refundHistoryIds);
        $refundEnvelope = OrderRefundResource::make($refund)->response(request())->getData(true);
        self::assertSame($refund->id, $refundEnvelope['data']['id']);
        $refundHistoryEnvelope = OrderRefundResource::collection($refundHistory)->response(request())->getData(true);
        self::assertSame($refund->id, $refundHistoryEnvelope['data'][0]['id']);
        $duplicateRefund = (new OrderRefundService())->create([
            'order_id' => $order->id,
            'status' => OrderRefund::STATUS_PENDING,
            'cause' => 'second synthetic request',
        ]);
        self::assertFalse($duplicateRefund['status']);
        self::assertSame(
            ResponseError::ERROR_506,
            $duplicateRefund['code'],
            'Refund failure: ' . ($duplicateRefund['message'] ?? '')
        );
        $orderEnvelope = OrderResource::make($order->load(['orderDetails', 'orderRefunds']))
            ->response(request())
            ->getData(true);
        self::assertSame($order->id, $orderEnvelope['data']['id']);
        self::assertSame(2, (int)$orderEnvelope['data']['details'][0]['quantity']);
        self::assertSame('pending', $orderEnvelope['data']['order_refunds'][0]['status']);

        $this->assertFixtureSection('cart', [
            'first_quantity' => 4,
            'first_line_price' => 110,
            'same_stock_second_quantity' => (int)$cartProduct->quantity,
            'same_stock_second_line_price' => (float)$cartProduct->price,
            'currency_id' => $cart->currency_id,
            'rate' => (float)$cart->rate,
            'resource' => $cartEnvelope,
        ]);
        $this->assertFixtureSection('checkout', [
            'status' => $checkout['status'],
            'order_id' => (int)$order->id,
            'order_status' => $order->status,
            'detail_quantity' => (int)$detail->quantity,
            'detail_total_price' => (float)$detail->total_price,
            'order_total_price' => (float)$order->total_price,
            'cart_removed' => !Cart::whereKey($cart->id)->exists(),
            'history_order_ids' => $orderHistory->getCollection()->pluck('id')->map(fn($id) => (int)$id)->all(),
            'resource' => $orderEnvelope,
        ]);
        $this->assertFixtureSection('refund', [
            'create_status' => $refundCreate['status'],
            'history_refund_ids' => array_map('intval', $refundHistoryIds),
            'resource' => $refundEnvelope,
            'history_resource' => $refundHistoryEnvelope,
            'duplicate_pending_request' => [
                'status' => $duplicateRefund['status'],
                'code' => $duplicateRefund['code'],
            ],
        ]);
    }

    public function test_missing_booking_service_master_fails_without_write(): void
    {
        $bookingCreate = (new BookingService())->create([
            'user_id' => $this->customer->id,
            'data' => [[
                'service_master_id' => 987654,
                'start_date' => '2030-01-02 10:00',
            ]],
        ]);
        $bookingRowsAfterCreate = Booking::count();
        self::assertFalse($bookingCreate['status']);
        self::assertSame(0, $bookingRowsAfterCreate, 'A missing service/location must not write a booking.');

        $this->assertFixtureSection('booking', [
            'service_create_missing_slot' => [
                'status' => $bookingCreate['status'],
                'code' => $bookingCreate['code'] ?? null,
                'rows_written' => $bookingRowsAfterCreate,
            ],
        ]);
    }

    public function test_successful_booking_schedule_history_and_resource_contract(): void
    {
        $this->seedBookingSchedule();
        $this->app->instance(
            'request',
            \Illuminate\Http\Request::create('/api/v1/dashboard/user/bookings', 'POST')
        );

        $result = (new BookingService())->create([
            'user_id' => $this->customer->id,
            'data' => [[
                'service_master_id' => 1,
                'shop_location_id' => 1,
                'start_date' => '2030-01-02 10:00',
            ]],
        ]);
        self::assertTrue(
            $result['status'],
            'Original scheduled booking create failed: ' . ($result['code'] ?? '') . ' ' . ($result['message'] ?? '')
        );
        /** @var Booking $booking */
        $booking = $result['data'][0];
        self::assertSame(1, (int)$booking->shop_id);
        self::assertSame(2, (int)$booking->master_id);
        self::assertSame(1, (int)$booking->shop_location_id);
        self::assertSame('2030-01-02 10:00', $booking->start_date);
        self::assertSame('2030-01-02 11:00', $booking->end_date);
        self::assertSame(40.0, (float)$booking->total_price);

        $booking->createTransaction([
            'price' => 40,
            'user_id' => $this->customer->id,
            'payment_sys_id' => 1,
            'status' => Transaction::STATUS_PROGRESS,
        ]);
        $abandoned = Booking::create([
            'user_id' => $this->customer->id,
            'shop_id' => 1,
            'master_id' => 2,
            'service_master_id' => 1,
            'shop_location_id' => 1,
            'currency_id' => 1,
            'status' => Booking::STATUS_NEW,
            'start_date' => '2030-01-03 10:00:00',
            'end_date' => '2030-01-03 11:00:00',
            'price' => 40,
            'total_price' => 40,
        ]);
        $canceled = Booking::create([
            'user_id' => $this->customer->id,
            'shop_id' => 1,
            'master_id' => 2,
            'service_master_id' => 1,
            'shop_location_id' => 1,
            'currency_id' => 1,
            'status' => Booking::STATUS_CANCELED,
            'start_date' => '2030-01-04 10:00:00',
            'end_date' => '2030-01-04 11:00:00',
            'price' => 40,
            'total_price' => 40,
        ]);
        $historyIds = Booking::filter([
            'user_id' => $this->customer->id,
            'hide_abandoned_new' => true,
        ])->orderBy('id')->pluck('id')->all();
        self::assertSame([$booking->id, $canceled->id], $historyIds);
        self::assertNotContains($abandoned->id, $historyIds);

        $booking = (new BookingRepository())->show($booking);
        $bookingEnvelope = BookingResource::make($booking)->response(request())->getData(true);
        self::assertSame($booking->id, $bookingEnvelope['data']['id']);
        self::assertSame('new', $bookingEnvelope['data']['status']);
        self::assertSame(1, (int)$bookingEnvelope['data']['shop_location']['id']);
        self::assertSame('progress', $bookingEnvelope['data']['transaction']['status']);

        $this->assertFixtureSection('booking', [
            'successful_create' => [
                'status' => $result['status'],
                'booking_id' => (int)$booking->id,
                'booking_status' => $booking->status,
                'shop_id' => (int)$booking->shop_id,
                'master_id' => (int)$booking->master_id,
                'service_master_id' => (int)$booking->service_master_id,
                'shop_location_id' => (int)$booking->shop_location_id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'total_price' => (float)$booking->total_price,
                'history_visible_ids' => array_map('intval', $historyIds),
                'history_hidden_abandoned_ids' => [(int)$abandoned->id],
                'resource' => $bookingEnvelope,
            ],
        ]);
    }

    public function test_native_booking_service_creates_walk_in_booking_without_account_or_wallet_side_effects(): void
    {
        $this->seedBookingSchedule();
        $seller = User::query()->findOrFail(3);
        $this->useAuthenticatedCustomer($seller);
        $this->app->instance(
            'request',
            \Illuminate\Http\Request::create('/api/v1/dashboard/seller/bookings', 'POST')
        );
        DB::table('seller_booking_clients')->insert([
            'id' => 1,
            'shop_id' => 1,
            'shop_location_id' => 1,
            'name' => 'Walk-in Client',
            'dedupe_scope' => 'location:1',
        ]);
        DB::table('users')->where('id', 3)->update(['b_count' => 0, 'b_sum' => 0]);
        DB::table('wallets')->insert(['user_id' => 3, 'price' => 1000]);

        $this->app->instance(
            'request',
            \Illuminate\Http\Request::create('/api/v1/dashboard/user/bookings', 'POST')
        );
        $sharedRouteResult = (new BookingService())->create([
            'shop_id' => 1,
            'local_client_id' => 1,
            'data' => [[
                'service_master_id' => 1,
                'shop_location_id' => 1,
                'start_date' => '2030-01-02 10:00',
            ]],
        ]);
        self::assertFalse($sharedRouteResult['status']);
        self::assertSame(0, Booking::query()->count());

        $this->app->instance(
            'request',
            \Illuminate\Http\Request::create('/api/v1/dashboard/seller/bookings', 'POST')
        );
        $result = (new BookingService())->create([
            'shop_id' => 1,
            'local_client_id' => 1,
            'data' => [[
                'service_master_id' => 1,
                'shop_location_id' => 1,
                'start_date' => '2030-01-02 10:00',
            ]],
        ]);

        self::assertTrue(
            $result['status'],
            'Native BookingService walk-in create failed: ' . ($result['code'] ?? '') . ' ' . ($result['message'] ?? '')
        );
        /** @var Booking $booking */
        $booking = $result['data'][0];
        $booking->load(['localClient', 'transaction.paymentSystem']);
        self::assertNull($booking->user_id);
        self::assertSame(1, (int) $booking->local_client_id);
        self::assertSame('Walk-in Client', $booking->localClient->name);
        self::assertNull($booking->transaction);
        self::assertSame(0, DB::table('transactions')->count());
        self::assertSame(0, (int) DB::table('users')->where('id', 3)->value('b_count'));
        self::assertSame(1000.0, (float) DB::table('wallets')->where('user_id', 3)->value('price'));

        $envelope = BookingResource::make($booking)->response(request())->getData(true);
        self::assertSame('Walk-in Client', $envelope['data']['local_client']['name']);
        self::assertArrayNotHasKey('user_id', $envelope['data']);

        $statusUpdated = (new BookingService())->statusUpdate($booking->id, [
            'status' => Booking::STATUS_CANCELED,
        ]);
        self::assertSame(Booking::STATUS_CANCELED, $statusUpdated->status);
        self::assertSame(1000.0, (float) DB::table('wallets')->where('user_id', 3)->value('price'));
        self::assertSame(0, DB::table('transactions')->count());
    }

    private function useAuthenticatedCustomer(User $user): void
    {
        $guard = \Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($user->id);
        $guard->shouldReceive('check')->andReturn(true);

        $auth = \Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
    }

    private function seedBookingSchedule(): void
    {
        DB::table('users')->insert([
            [
                'id' => 2, 'uuid' => 'synthetic-master', 'firstname' => 'Baseline',
                'lastname' => 'Master', 'email' => 'master@example.invalid', 'active' => 1,
            ],
            [
                'id' => 3, 'uuid' => 'synthetic-seller', 'firstname' => 'Baseline',
                'lastname' => 'Seller', 'email' => 'seller@example.invalid', 'active' => 1,
            ],
        ]);
        DB::table('roles')->insert(['id' => 1, 'name' => 'master', 'guard_name' => 'sanctum']);
        DB::table('roles')->insert(['id' => 2, 'name' => 'seller', 'guard_name' => 'sanctum']);
        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => User::class,
            'model_id' => 2,
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => 2,
            'model_type' => User::class,
            'model_id' => 3,
        ]);
        DB::table('regions')->insert(['id' => 1, 'active' => 1]);
        DB::table('cities')->insert(['id' => 1, 'region_id' => 1, 'country_id' => 1, 'active' => 1]);
        DB::table('countries')->where('id', 1)->update(['created_at' => now(), 'updated_at' => now()]);
        DB::table('shops')->insert([
            'id' => 1, 'user_id' => 3, 'status' => 'approved',
            'delivery_type' => 1, 'tax' => 0, 'percentage' => 0,
            'uuid' => 'synthetic-booking-shop', 'slug' => 'synthetic-booking-shop',
            'open' => 1, 'verify' => 1, 'visibility' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('shop_locations')->insert([
            'id' => 1, 'shop_id' => 1, 'country_id' => 1,
            'region_id' => 1, 'city_id' => 1, 'type' => ShopLocation::SERVICE,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('shop_translations')->insert([
            'shop_id' => 1, 'locale' => 'en', 'title' => 'Baseline booking shop',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('services')->insert([
            'id' => 1, 'shop_id' => 1, 'category_id' => 1, 'slug' => 'synthetic-service',
            'status' => 'accepted', 'price' => 40, 'commission_fee' => 0,
            'discount' => 0, 'interval' => 60, 'pause' => 0, 'type' => 'service',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('service_translations')->insert([
            'service_id' => 1, 'locale' => 'en', 'title' => 'Baseline service',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('service_masters')->insert([
            'id' => 1, 'shop_id' => 1, 'master_id' => 2, 'service_id' => 1,
            'active' => 1, 'price' => 40, 'interval' => 60, 'pause' => 0,
            'discount' => 0, 'commission_fee' => 0, 'type' => 'service',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('user_working_days')->insert([
            'user_id' => 2, 'day' => 'wednesday', 'from' => '09:00',
            'to' => '17:00', 'disabled' => 0,
        ]);
    }

    private function assertFixtureSection(string $section, array $expected): void
    {
        $fixturePath = __DIR__ . '/fixtures/original-domain.json';

        if (getenv('BASELINE_WRITE_FIXTURES') === '1') {
            $fixture = is_file($fixturePath)
                ? json_decode(file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR)
                : [];
            $fixture['source'] = 'original Laravel backend; synthetic isolated SQLite :memory:';
            if ($section === 'booking' && array_key_exists('service_create_missing_slot', $expected)) {
                unset($fixture['booking']['history_visible_ids'], $fixture['booking']['history_hidden_abandoned_ids']);
            }
            $fixture[$section] = array_replace($fixture[$section] ?? [], $expected);
            if (!is_dir(dirname($fixturePath))) {
                mkdir(dirname($fixturePath), 0775, true);
            }
            file_put_contents(
                $fixturePath,
                json_encode(
                    $fixture,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
                ) . PHP_EOL
            );
        }

        self::assertFileExists($fixturePath, 'Generate tests/Baseline/fixtures/original-domain.json using the baseline runner.');
        $fixture = json_decode(file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(
            $this->normalizeFixtureValue($expected),
            $this->normalizeFixtureValue(array_intersect_key($fixture[$section] ?? [], $expected)),
            "Fixture section mismatch: $section"
        );
    }

    private function normalizeFixtureValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $entry) {
            $value[$key] = $this->normalizeFixtureValue($entry);
        }

        if (!array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }

    /**
     * This is intentionally a small explicit synthetic schema. It covers only
     * columns read or written by the selected original methods and supplies
     * empty related tables queried by the actual Eloquent eager-load paths.
     */
    private function createSyntheticSchema(): void
    {
        $schema = $this->database->schema('hardening');
        $integerColumns = [
            'active', 'default', 'min_qty', 'max_qty', 'quantity', 'bonus', 'group',
            'digital', 'visibility', 'verify', 'open', 'current', 'o_count', 'od_count',
            'country_role_id', 'user_carts_count', 'cart_details_count', 'disabled', 'gender',
        ];
        $decimalColumns = [
            'price', 'total_price', 'rate', 'discount', 'tax', 'commission_fee',
            'service_fee', 'delivery_fee', 'total_discount', 'total_tax',
            'seller_fee', 'origin_price', 'tips', 'percentage', 'latitude',
            'longitude',
        ];
        $tables = [
            'users' => 'uuid firstname lastname email phone lang currency_id img active password gender referral my_referral o_count o_sum r_count r_sum r_avg b_count b_sum',
            'country_admins' => 'user_id country_id',
            'country_invitations' => 'user_id country_id country_role_id status',
            'currencies' => 'title symbol rate default active',
            'languages' => 'locale default',
            'translations' => 'locale key value',
            'countries' => 'currency_id title region_id',
            'regions' => 'active',
            'cities' => 'region_id country_id active',
            'areas' => 'region_id country_id city_id active',
            'region_translations' => 'region_id locale title',
            'country_translations' => 'country_id locale title',
            'city_translations' => 'city_id locale title',
            'area_translations' => 'area_id locale title',
            'shops' => 'user_id status delivery_type tax percentage latitude longitude uuid slug logo_img background_img open delivery_time verify email_statuses visibility o_count b_count b_sum r_count r_sum r_avg collect_via_platform',
            'shop_locations' => 'shop_id country_id region_id city_id area_id type',
            'shop_translations' => 'shop_id locale title address',
            'shop_subscriptions' => 'shop_id subscription_id expired_at active',
            'subscriptions' => 'order_limit booking_limit',
            'service_masters' => 'shop_id master_id service_id active price interval pause discount commission_fee type data gender',
            'services' => 'shop_id category_id slug status status_note price commission_fee discount interval pause type img data gender',
            'service_translations' => 'service_id locale title description',
            'categories' => 'parent_id type active',
            'category_translations' => 'category_id locale title',
            'bookings' => 'user_id local_client_id shop_id shop_location_id master_id service_master_id service_id category_id currency_id parent_id status start_date end_date price total_price service_fee commission_fee rate discount note canceled_note collect_via_platform',
            'transactions' => 'payable_id payable_type parent_id user_id payment_sys_id payment_trx_id price note perform_time status status_description refund_time',
            'roles' => 'name guard_name',
            'model_has_roles' => 'role_id model_type model_id',
            'invitations' => 'shop_id user_id master_id shop_role_id status',
            'invitation_shop_locations' => 'invitation_id shop_location_id',
            'user_working_days' => 'user_id day from to disabled',
            'master_closed_dates' => 'master_id date',
            'master_disabled_times' => 'master_id date from to can_booking repeats custom_repeat_type custom_repeat_value end_type end_value',
            'user_member_ships' => 'user_id member_ship_id sessions remainder expired_at',
            'member_ship_services' => 'member_ship_id service_id',
            'booking_activities' => 'booking_id user_id type data',
            'booking_extras' => 'booking_id service_extra_id price',
            'booking_extra_translations' => 'booking_extra_id locale title',
            'booking_extra_times' => 'booking_id price start_date end_date',
            'service_extras' => 'service_id price active',
            'service_extra_translations' => 'service_extra_id locale title',
            'products' => 'shop_id status active min_qty max_qty tax img interval digital o_count od_count title deleted_at',
            'product_translations' => 'product_id locale title',
            'stocks' => 'product_id price quantity discount_id discount_expired_at o_count od_count sku img deleted_at',
            'discounts' => 'start end active type price',
            'whole_sale_prices' => 'stock_id min_quantity max_quantity price',
            'carts' => 'owner_id user_id name status total_price currency_id region_id country_id city_id area_id rate group stock_id quantity price discount shop_id',
            'user_carts' => 'cart_id user_id name status uuid stock_id quantity price discount shop_id country_id currency_id rate',
            'cart_details' => 'user_cart_id shop_id stock_id quantity price discount country_id currency_id rate',
            'cart_detail_products' => 'cart_detail_id stock_id quantity price discount bonus parent_id country_id currency_id rate',
            'bonuses' => 'shop_id stock_id type expired_at status value bonus_stock_id bonus_quantity',
            'stock_extras' => 'stock_id extra_value_id extra_group_id',
            'extra_values' => 'extra_group_id value active',
            'extra_groups' => 'shop_id type active',
            'extra_group_translations' => 'extra_group_id locale title',
            'galleries' => 'loadable_id loadable_type path type',
            'payment_process' => 'model_id model_type data payment_id status',
            'settings' => 'key value',
            'orders' => 'user_id deliveryman_id address_id delivery_price_id delivery_point_id currency_id shop_id type delivery_type commission_fee canceled_note track_name track_id track_url cart_id parent_id otp seller_fee origin_price status total_price delivery_fee total_discount total_tax service_fee rate note location address phone username delivery_date coupon_price tips current img',
            'order_details' => 'order_id stock_id replace_stock_id replace_quantity replace_note origin_price total_price tax discount quantity bonus note shop_id',
            'order_refunds' => 'order_id status cause answer',
            'order_coupons' => 'order_id user_id name price',
            'point_histories' => 'user_id model_id model_type price',
            'reviews' => 'user_id reviewable_id reviewable_type assignable_id assignable_type rating comment',
            'user_addresses' => 'user_id phone address',
            'delivery_man_settings' => 'user_id',
            'delivery_prices' => 'shop_id price region_id country_id city_id area_id',
            'delivery_points' => 'shop_id price',
            'delivery_point_working_days' => 'delivery_point_id',
            'delivery_point_closed_dates' => 'delivery_point_id',
            'order_status_notes' => 'order_id user_id status note',
            'payments' => 'tag active',
            'digital_files' => 'product_id',
            'user_digital_files' => 'digital_file_id user_id downloaded',
            'push_notifications' => 'user_id model_id model_type type title body data read_at',
            'seller_booking_clients' => 'shop_id shop_location_id name phone email normalized_phone normalized_email dedupe_scope',
            'wallets' => 'user_id price currency_id',
            'platform_fee_ledger_entries' => 'transaction_id entry_type payable_type payable_id shop_id payment_id currency_id amount status note',
        ];

        foreach ($tables as $name => $columns) {
            if ($schema->hasTable($name)) {
                continue;
            }
            $schema->create($name, function (Blueprint $table) use ($name, $columns, $integerColumns, $decimalColumns): void {
                $table->increments('id');
                foreach (preg_split('/\s+/', trim($columns)) as $column) {
                    if (str_ends_with($column, '_id') || in_array($column, $integerColumns, true)) {
                        $definition = $table->integer($column)->nullable();
                    } elseif (in_array($column, $decimalColumns, true)) {
                        $definition = $table->float($column)->nullable();
                    } else {
                        $definition = $table->text($column)->nullable();
                    }
                    if ($name === 'cart_detail_products' && $column === 'bonus') {
                        $definition->default(false);
                    }
                    if ($name === 'bookings' && $column === 'status') {
                        $definition->default(Booking::STATUS_NEW);
                    }
                }
                $table->timestamps();
            });
        }
    }
}