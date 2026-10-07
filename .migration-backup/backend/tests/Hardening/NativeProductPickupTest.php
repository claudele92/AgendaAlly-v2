<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopPickupPolicy;
use App\Models\User;
use App\Services\ProductPickupService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class NativeProductPickupTest extends IsolatedTestCase
{
    private ProductPickupService $pickup;
    private Shop $shop;
    private bool $canManage = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $responses = Mockery::mock(\Illuminate\Contracts\Routing\ResponseFactory::class);
        $responses->shouldReceive('json')->andReturnUsing(fn ($data) => new \Illuminate\Http\JsonResponse($data));
        $this->app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, $responses);
        foreach ([
            'shops' => ['user_id', 'status', 'visibility', 'product_fulfillment_methods', 'delivery_type'],
            'shop_locations' => ['shop_id', 'type', 'address', 'alias'],
            'orders' => ['shop_id', 'delivery_type', 'delivery_fee', 'delivery_date', 'address_id', 'address',
                'location', 'delivery_price_id', 'delivery_point_id', 'deliveryman_id'],
            'settings' => ['key', 'value'],
            'languages' => ['locale', 'active', 'default'],
            'currencies' => ['title', 'symbol', 'active', 'default', 'rate'],
            'country_admins' => ['user_id', 'country_id'],
            'country_invitations' => ['user_id', 'invited_user_id', 'country_id', 'country_role_id', 'status'],
        ] as $table => $columns) {
            Schema::create($table, function (Blueprint $t) use ($columns): void {
                $t->increments('id');
                foreach ($columns as $column) $t->string($column)->nullable();
                $t->timestamps();
            });
        }
        $migration = require dirname(__DIR__, 2) . '/database/migrations/2026_10_05_010000_add_native_product_pickup_scheduling.php';
        // Fixture orders already exist; exercise the actual additive migration.
        DB::table('orders')->insert(['id' => 900, 'shop_id' => 11, 'delivery_type' => 'pickup', 'delivery_fee' => 0]);
        $migration->up();
        DB::table('shops')->insert([
            ['id' => 11, 'user_id' => 101, 'status' => 'approved', 'visibility' => 1,
                'product_fulfillment_methods' => '["delivery","pickup"]'],
            ['id' => 22, 'user_id' => 202, 'status' => 'approved', 'visibility' => 1,
                'product_fulfillment_methods' => '["pickup"]'],
        ]);
        DB::table('shop_locations')->insert([
            ['id' => 1, 'shop_id' => 11, 'type' => 1, 'address' => 'Owned Product branch', 'alias' => 'Product branch'],
            ['id' => 2, 'shop_id' => 22, 'type' => 1, 'address' => 'Other vendor branch', 'alias' => 'Other'],
            ['id' => 3, 'shop_id' => 11, 'type' => 2, 'address' => 'Service-only location', 'alias' => 'Service'],
        ]);
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->forceFill(['id' => 101]);
        $actor->setRelation('shop', (new Shop)->forceFill(['id' => 11]));
        $actor->setRelation('countryAdmin', null);
        $actor->shouldReceive('hasRole')->andReturn(false);
        $actor->shouldReceive('isSuperAdmin')->andReturn(false);
        $actor->shouldReceive('getForeignKey')->andReturn('user_id');
        $actor->shouldReceive('hasShopPermission')->andReturnUsing(fn ($id, $key) => $id === 11 &&
            ($key === 'shop_settings.view' || ($key === 'shop_settings.manage' && $this->canManage)));
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($actor);
        $guard->shouldReceive('id')->andReturn(101);
        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('guard')->andReturn($guard);
        $this->app->instance('auth', $factory);
        $this->pickup = new ProductPickupService;
        $this->shop = Shop::findOrFail(11);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-05 10:20', 'Africa/Douala'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function scheduled(array $overrides = []): array
    {
        return array_replace($this->pickup->defaultPolicy(), [
            'timing_mode' => 'scheduled', 'timezone' => 'Africa/Douala',
            'weekly_hours' => array_map(fn ($day) => ['day' => $day, 'enabled' => $day !== 0,
                'start' => '09:00', 'end' => '18:00'], range(0, 6)),
            'preparation_minutes' => 60, 'window_minutes' => 60,
            'cutoff' => '15:00',
        ], $overrides);
    }

    private function save(array $policy): void
    {
        ShopPickupPolicy::updateOrCreate(['shop_id' => 11, 'shop_location_id' => 1],
            $this->pickup->validatePolicy($policy));
    }

    private function selection(): array
    {
        $this->save($this->scheduled());
        $window = $this->pickup->availability($this->shop, 1, '2026-10-06')['windows'][0];
        return ['delivery_type' => 'pickup', 'pickup_selections' => [11 => [
            'shop_location_id' => 1, 'date' => '2026-10-06',
            'window_start' => $window['window_start'], 'window_end' => $window['window_end'],
        ]]];
    }

    public function test_existing_pickup_defaults_to_ready_based_without_fake_date(): void
    {
        $fields = $this->pickup->orderFields($this->shop, ['delivery_type' => 'pickup']);
        self::assertSame('as_soon_as_ready', $fields['pickup']['timing_mode']);
        self::assertNull($fields['pickup']['window_start']);
        self::assertNull($fields['pickup']['window_end']);
        self::assertNull($fields['delivery_date']);
        self::assertSame('Owned Product branch', $fields['pickup']['address']);
    }

    public function test_vendor_scheduled_policy_persists_rules_and_normalizes_numeric_days(): void
    {
        $policy = $this->scheduled();
        $policy['weekly_hours'][1]['day'] = '1';
        $this->save($policy);
        $stored = $this->pickup->policy(11, 1);
        self::assertSame('scheduled', $stored['timing_mode']);
        self::assertSame(1, $stored['weekly_hours'][1]['day']);
        self::assertSame(60, $stored['preparation_minutes']);
        self::assertSame('15:00', $stored['cutoff']);
        self::assertSame('Africa/Douala', $stored['timezone']);
    }

    public function test_scheduled_mode_requires_explicit_timezone(): void
    {
        $this->expectException(ValidationException::class);
        $this->pickup->validatePolicy($this->scheduled(['timezone' => null]));
    }

    public function test_closed_or_overnight_hours_cannot_be_saved_as_scheduled(): void
    {
        $this->expectException(ValidationException::class);
        $this->pickup->validatePolicy($this->scheduled(['weekly_hours' => [
            ['day' => 1, 'enabled' => true, 'start' => '18:00', 'end' => '09:00'],
        ]]));
    }

    public function test_lead_time_removes_windows_that_start_too_soon(): void
    {
        $windows = $this->pickup->windows($this->scheduled(), '2026-10-05');
        self::assertSame('12:00', $windows[0]['local_start']);
        self::assertSame('13:00', $windows[0]['local_end']);
    }

    public function test_cutoff_is_enforced_on_the_server(): void
    {
        self::assertSame([], $this->pickup->windows($this->scheduled(), '2026-10-05',
            CarbonImmutable::parse('2026-10-05 15:00', 'Africa/Douala')));
    }

    public function test_same_day_can_be_disabled_without_blocking_tomorrow(): void
    {
        $policy = $this->scheduled(['same_day' => false]);
        self::assertSame([], $this->pickup->windows($policy, '2026-10-05'));
        self::assertNotEmpty($this->pickup->windows($policy, '2026-10-06'));
    }

    public function test_blackout_dates_and_closed_days_return_no_windows(): void
    {
        self::assertSame([], $this->pickup->windows($this->scheduled(['blackout_dates' => ['2026-10-06']]), '2026-10-06'));
        self::assertSame([], $this->pickup->windows($this->scheduled(), '2026-10-11'));
    }

    public function test_public_availability_returns_only_product_branches_and_utc_windows(): void
    {
        $this->save($this->scheduled());
        $data = $this->pickup->availability($this->shop, 1, '2026-10-06');
        self::assertSame([1], array_column($data['locations'], 'id'));
        self::assertSame('09:00', $data['windows'][0]['local_start']);
        self::assertSame('2026-10-06T08:00:00+00:00', $data['windows'][0]['window_start']);
        self::assertArrayNotHasKey('blackout_dates', $data);
        self::assertArrayNotHasKey('weekly_hours', $data);
    }

    public function test_foreign_branch_cannot_be_used_for_own_shop(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->pickup->orderFields($this->shop, ['delivery_type' => 'pickup',
            'pickup_selections' => [11 => ['shop_location_id' => 2]]]);
    }

    public function test_service_location_is_not_a_product_pickup_branch(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->pickup->location(11, 3);
    }

    public function test_forged_window_cannot_create_order_fields(): void
    {
        $data = $this->selection();
        $data['pickup_selections'][11]['window_start'] = '2026-10-06T08:01:00+00:00';
        $this->expectException(ValidationException::class);
        $this->pickup->orderFields($this->shop, $data);
    }

    public function test_window_is_revalidated_after_vendor_adds_blackout(): void
    {
        $data = $this->selection();
        $this->save($this->scheduled(['blackout_dates' => ['2026-10-06']]));
        $this->expectException(ValidationException::class);
        $this->pickup->orderFields($this->shop, $data);
    }

    public function test_disabled_pickup_is_rejected_even_with_a_previously_valid_window(): void
    {
        $data = $this->selection();
        $this->shop->product_fulfillment_methods = ['delivery'];
        $this->expectException(ValidationException::class);
        $this->pickup->orderFields($this->shop, $data);
    }

    public function test_pickup_zeroes_fee_and_strips_every_delivery_and_point_field(): void
    {
        $fields = $this->pickup->orderFields($this->shop, $this->selection() + [
            'delivery_fee' => 999, 'delivery_price_id' => 55, 'delivery_point_id' => 99,
            'address_id' => 4, 'deliveryman_id' => 7, 'address' => ['address' => 'Stale'],
        ]);
        self::assertSame(0, $fields['delivery_fee']);
        foreach (['address_id', 'address', 'location', 'delivery_price_id', 'delivery_point_id', 'deliveryman_id'] as $key) {
            self::assertNull($fields[$key]);
        }
    }

    public function test_delivery_and_collection_point_do_not_use_pickup_rules(): void
    {
        self::assertSame(['pickup' => null], $this->pickup->orderFields($this->shop, ['delivery_type' => 'delivery']));
        self::assertSame(['pickup' => null], $this->pickup->orderFields($this->shop, ['delivery_type' => 'point']));
        self::assertSame(['pickup' => null], $this->pickup->orderFields($this->shop, ['delivery_type' => 'digital']));
    }

    public function test_ready_based_legacy_shop_does_not_require_fabricated_branch_or_date(): void
    {
        DB::table('shop_locations')->where('id', 1)->delete();
        $this->shop->setRelation('translation', (new \App\Models\ShopTranslation)->forceFill(['address' => 'Native Shop postal address']));
        $fields = $this->pickup->orderFields($this->shop, ['delivery_type' => 'pickup']);
        self::assertNull($fields['pickup']['shop_location_id']);
        self::assertNull($fields['pickup']['window_start']);
        self::assertSame('Native Shop postal address', $fields['pickup']['address']);
    }

    public function test_view_only_staff_cannot_modify_policy(): void
    {
        $this->canManage = false;
        $controller = new \App\Http\Controllers\API\v1\ProductPickupController;
        self::assertSame('as_soon_as_ready', $controller->show(11, 1)->getData(true)['data']['timing_mode']);
        try {
            $controller->save(\Illuminate\Http\Request::create('/', 'PUT', $this->scheduled()), 11, 1);
            self::fail('View-only permission must not allow policy writes.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
            self::assertSame(0, ShopPickupPolicy::count());
        }
    }

    public function test_single_product_location_may_use_shop_address_but_multiple_branches_may_not(): void
    {
        $this->shop->setRelation('translation', (new \App\Models\ShopTranslation)->forceFill(['address' => 'Native Shop postal address']));
        $location = (new \App\Models\ShopLocation)->forceFill(['address' => null]);
        self::assertSame('Native Shop postal address', $this->pickup->pickupAddress($this->shop, $location, 1));
        self::assertNull($this->pickup->pickupAddress($this->shop, $location, 2));
    }

    public function test_ready_based_mode_rejects_fake_window(): void
    {
        $this->expectException(ValidationException::class);
        $this->pickup->orderFields($this->shop, ['delivery_type' => 'pickup',
            'pickup_selections' => [11 => ['window_start' => '2026-10-06T08:00:00+00:00']]]);
    }

    public function test_structured_snapshot_persists_and_historical_order_is_unchanged(): void
    {
        $before = DB::table('orders')->where('id', 900)->first();
        $fields = $this->pickup->orderFields($this->shop, $this->selection());
        $order = Order::withoutEvents(fn () => Order::create($fields + ['shop_id' => 11, 'delivery_type' => 'pickup']));
        self::assertSame($fields['pickup'], $order->fresh()->pickup);
        self::assertEquals($before, DB::table('orders')->where('id', 900)->first());
        self::assertNull($before->pickup);
    }

    public function test_multi_vendor_selections_remain_shop_scoped(): void
    {
        $data = $this->selection();
        $data['pickup_selections'][22] = ['shop_location_id' => 2];
        $other = $this->pickup->orderFields(Shop::findOrFail(22), $data);
        self::assertSame(22, $other['pickup']['shop_id']);
        self::assertSame(2, $other['pickup']['shop_location_id']);
        self::assertSame('as_soon_as_ready', $other['pickup']['timing_mode']);
        self::assertNull($other['pickup']['window_start']);
    }

    public function test_policy_controller_denies_other_vendor_private_settings(): void
    {
        $controller = new \App\Http\Controllers\API\v1\ProductPickupController;
        try {
            $controller->show(22, 2);
            self::fail('Foreign private policy must be forbidden.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
    }

    public function test_vendor_cannot_save_foreign_policy_or_forged_branch(): void
    {
        $controller = new \App\Http\Controllers\API\v1\ProductPickupController;
        $request = \Illuminate\Http\Request::create('/', 'PUT', $this->scheduled());
        try {
            $controller->save($request, 22, 2);
            self::fail('Foreign policy writes must be forbidden.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        try {
            $controller->save($request, 11, 2);
            self::fail('A foreign branch must not be attached to an owned Shop.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            self::assertSame(0, ShopPickupPolicy::count());
        }
    }

    public function test_owner_can_save_reload_and_reset_policy_via_controller(): void
    {
        $controller = new \App\Http\Controllers\API\v1\ProductPickupController;
        $request = \Illuminate\Http\Request::create('/', 'PUT', $this->scheduled());
        $response = $controller->save($request, 11, 1)->getData(true);
        self::assertSame('scheduled', $response['data']['timing_mode']);
        self::assertSame('scheduled', $controller->show(11, 1)->getData(true)['data']['timing_mode']);
        self::assertSame('as_soon_as_ready', $controller->destroy(11, 1)->getData(true)['data']['timing_mode']);
    }

    public function test_native_order_paths_call_authoritative_pickup_validation(): void
    {
        foreach (['CartOrderService', 'POSOrderService'] as $name) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/app/Services/OrderService/' . $name . '.php');
            self::assertStringContainsString('ProductPickupService)->orderFields(', $source);
            self::assertStringContainsString("'pickup_selections' => true", $source);
        }
    }
}