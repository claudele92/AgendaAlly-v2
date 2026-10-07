<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Helpers\MapsConfiguration;
use App\Helpers\OrderHelper;
use App\Helpers\ServiceMedia;
use App\Helpers\SpecialistDiscovery;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mockery;

final class NativeSixWorkstreamTest extends IsolatedTestCase
{
    private string $storageRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->storageRoot = sys_get_temp_dir() . '/native-six-' . uniqid('', true);
        $this->app['config']->set('app.url', 'https://media.example.test');
        $this->app['config']->set('filesystems', ['default' => 'public', 'disks' => [
            'public' => ['driver' => 'local', 'root' => $this->storageRoot, 'url' => 'https://media.example.test/storage'],
        ]]);
        foreach ([
            'settings' => ['key', 'value'],
            'shops' => ['status', 'product_fulfillment_methods', 'delivery_type', 'user_id', 'service_min_price', 'service_max_price'],
            'areas' => ['active', 'city_id', 'country_id'],
            'delivery_prices' => ['shop_id', 'area_id', 'city_id', 'country_id', 'region_id', 'price'],
            'users' => ['active'],
            'services' => ['shop_id', 'status', 'img', 'price', 'interval', 'pause', 'type'],
            'service_masters' => ['shop_id', 'service_id', 'master_id', 'active'],
            'invitations' => ['shop_id', 'user_id', 'role', 'status'],
            'galleries' => ['loadable_type', 'loadable_id', 'path', 'type', 'title', 'size', 'mime', 'preview'],
            'service_translations' => ['service_id', 'locale', 'title', 'description'],
            'currencies' => ['title', 'symbol', 'active', 'default', 'rate', 'position'],
            'languages' => ['locale', 'active', 'default'],
            'country_admins' => ['user_id', 'country_id'],
            'country_invitations' => ['user_id', 'invited_user_id', 'country_id', 'country_role_id', 'status'],
        ] as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns): void {
                $table->increments('id');
                foreach ($columns as $column) $table->string($column)->nullable();
                $table->timestamps();
            });
        }
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->forceFill(['id' => 101]);
        $actor->setRelation('countryAdmin', null);
        $actor->setRelation('shop', (new Shop)->forceFill(['id' => 11]));
        $actor->shouldReceive('getForeignKey')->andReturn('user_id');
        $actor->shouldReceive('hasRole')->andReturn(false);
        $actor->shouldReceive('hasShopPermission')->with(11, 'services.manage')->andReturn(true);
        $actor->shouldReceive('hasShopPermission')->with(22, 'services.manage')->andReturn(false);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($actor);
        $guard->shouldReceive('id')->andReturn(101);
        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('guard')->andReturn($guard);
        $this->app->instance('auth', $factory);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->storageRoot);
        parent::tearDown();
    }

    public function test_service_portable_media_reference_resolves_without_stored_development_host(): void
    {
        self::assertSame('https://media.example.test/storage/images/services/shops/11/photo.jpg',
            ServiceMedia::publicUrl('/storage/images/services/shops/11/photo.jpg'));
        self::assertSame('https://native-bucket.example.test/public/images/services/shops/11/photo.jpg',
            ServiceMedia::publicUrl('https://native-bucket.example.test/public/images/services/shops/11/photo.jpg'));
        self::assertNull(ServiceMedia::publicUrl(null));
    }

    public function test_service_gallery_removal_clears_only_own_primary_and_preserves_shared_file(): void
    {
        $path = 'images/services/shops/11/shared.jpg';
        $url = 'https://media.example.test/storage/' . $path;
        Storage::disk('public')->put($path, 'synthetic-image');
        $this->database->table('services')->insert([
            ['id' => 1, 'shop_id' => 11, 'img' => $url],
            ['id' => 2, 'shop_id' => 22, 'img' => $url],
        ]);
        $this->database->table('galleries')->insert([
            ['id' => 1, 'loadable_type' => Service::class, 'loadable_id' => 1, 'path' => $url],
            ['id' => 2, 'loadable_type' => Service::class, 'loadable_id' => 2, 'path' => $url],
        ]);
        $storage = new \App\Services\GalleryService\FileStorageService;
        self::assertFalse($storage->deleteFileFromStorage(['ids' => [2]])['status']);
        self::assertTrue($storage->deleteFileFromStorage(['ids' => [1]])['status']);
        self::assertNull(Service::find(1)->img);
        self::assertSame($url, Service::find(2)->img);
        self::assertTrue(Storage::disk('public')->exists($path));
        self::assertSame(1, Service::whereKey(1)->count());
        self::assertSame([2], \App\Models\Gallery::pluck('id')->all());
    }

    public function test_maps_blank_preserves_keys_and_explicit_clear_is_required(): void
    {
        self::assertSame([], MapsConfiguration::normalizeInput([
            'google_map_key' => ' ', 'google_map_server_key' => '',
        ]));
        self::assertSame(['google_map_server_key' => ''], MapsConfiguration::normalizeInput([
            'clear_google_map_server_key' => true,
        ]));
        self::assertSame(['google_map_key' => 'new-public-key'],
            MapsConfiguration::normalizeInput(['google_map_key' => ' new-public-key ']));
    }

    public function test_maps_replacement_and_removal_cannot_be_combined(): void
    {
        $this->expectException(ValidationException::class);
        MapsConfiguration::normalizeInput(['google_map_key' => 'synthetic', 'clear_google_map_key' => true]);
    }

    public function test_maps_default_disabled_and_server_value_is_never_serialized(): void
    {
        Settings::create(['key' => 'google_map_server_key', 'value' => 'synthetic-server-only']);
        Settings::create(['key' => 'google_map_key', 'value' => 'synthetic-public']);
        $public = MapsConfiguration::settings(Settings::all())->pluck('value', 'key')->all();
        self::assertSame('0', $public['maps_enabled']);
        self::assertArrayNotHasKey('google_map_server_key', $public);
        self::assertStringNotContainsString('synthetic-server-only', json_encode($public));
        $admin = MapsConfiguration::settings(Settings::all(), true)->pluck('value', 'key')->all();
        self::assertSame('1', $admin['google_map_server_key_configured']);
        self::assertArrayNotHasKey('google_map_server_key', $admin);
        Settings::create(['key' => 'maps_enabled', 'value' => '1']);
        self::assertTrue(MapsConfiguration::enabled());
    }

    public function test_maps_management_requires_platform_admin_not_a_shop_owner(): void
    {
        $actor = Mockery::mock(User::class)->makePartial();
        $actor->shouldReceive('isSuperAdmin')->andReturn(false, true);
        self::assertFalse(MapsConfiguration::canManage($actor));
        self::assertTrue(MapsConfiguration::canManage($actor));
        self::assertFalse(MapsConfiguration::canManage(null));
    }

    public function test_production_maps_environment_kill_switch_survives_local_opt_in(): void
    {
        $this->app['config']->set('development.enabled', true);
        $this->app['config']->set('development.maps.enabled', false);
        $this->app['config']->set('app.env', 'production');
        self::assertFalse(MapsConfiguration::browserEnvironmentPermitted());
        $this->app['config']->set('app.env', 'local');
        self::assertTrue(MapsConfiguration::browserEnvironmentPermitted());
    }

    public function test_legacy_product_fulfillment_defaults_and_vendor_choice(): void
    {
        $shop = new Shop;
        self::assertSame([Order::DELIVERY], $shop->productFulfillmentMethods());
        $shop->product_fulfillment_methods = [Order::PICKUP];
        self::assertSame([Order::PICKUP], $shop->productFulfillmentMethods());
    }

    public function test_pickup_never_looks_up_stale_foreign_tariff_and_costs_zero(): void
    {
        $fees = [];
        OrderHelper::checkShopDelivery((new Shop)->forceFill(['id' => 11, 'product_fulfillment_methods' => ['pickup']]), [
            'delivery_type' => Order::PICKUP, 'delivery_price_id' => 999,
        ], 'en', $fees);
        self::assertSame([['shop_id' => 11, 'price' => 0]], $fees);
    }

    public function test_unoffered_fulfillment_is_rejected_server_side(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('does not offer');
        $fees = [];
        OrderHelper::checkShopDelivery((new Shop)->forceFill([
            'id' => 11, 'product_fulfillment_methods' => [Order::PICKUP],
        ]), ['delivery_type' => Order::DELIVERY], 'en', $fees);
    }

    public function test_foreign_vendor_delivery_price_cannot_be_used_as_platform_tariff(): void
    {
        $this->database->table('delivery_prices')->insert(['id' => 1, 'shop_id' => 22, 'price' => 5]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('another business');
        $fees = [];
        OrderHelper::checkShopDelivery((new Shop)->forceFill(['id' => 11]),
            ['delivery_type' => Order::DELIVERY, 'delivery_price_id' => 1], 'en', $fees);
    }

    public function test_delivery_area_country_and_city_are_server_authoritative(): void
    {
        $this->database->table('areas')->insert(['id' => 1, 'active' => 1, 'city_id' => 2, 'country_id' => 3]);
        $this->database->table('delivery_prices')->insert([
            'id' => 1, 'shop_id' => 11, 'area_id' => 1, 'city_id' => 9, 'country_id' => 3,
        ]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('does not match');
        $fees = [];
        OrderHelper::checkShopDelivery((new Shop)->forceFill(['id' => 11]),
            ['delivery_type' => Order::DELIVERY, 'delivery_price_id' => 1], 'en', $fees);
    }

    public function test_pickup_request_keeps_shop_but_discards_delivery_address_tariff_and_driver(): void
    {
        $request = PickupRequestProbe::create('/', 'POST', [
            'delivery_type' => Order::PICKUP, 'shop_id' => 11, 'cart_id' => 5,
            'address' => ['area_id' => 9], 'location' => ['latitude' => 1],
            'delivery_price_id' => 9, 'deliveryman_id' => 42,
        ]);
        $request->prepareFixture();
        self::assertSame(11, $request->input('shop_id'));
        foreach (['address', 'location', 'delivery_price_id', 'deliveryman_id'] as $key) self::assertFalse($request->has($key));
        $query = PickupRequestProbe::create('/?deliveryman_id=42&delivery_price_id=9', 'POST', ['delivery_type' => 'pickup']);
        $query->prepareFixture();
        self::assertFalse($query->has('deliveryman_id'));
        self::assertFalse($query->has('delivery_price_id'));
    }

    public function test_delivery_needs_a_destination_but_pickup_does_not(): void
    {
        foreach ([
            ['delivery_type' => 'delivery'],
            ['delivery_type' => 'delivery', 'location' => ['address' => 'Bastos street']],
            ['delivery_type' => 'pickup'],
        ] as $index => $data) {
            $request = PickupRequestProbe::create('/', 'POST', $data);
            $validator = Validator::make($data, []);
            $request->withValidator($validator);
            self::assertSame($index === 0, $validator->fails());
        }
    }

    public function test_pickup_only_business_cannot_accept_collection_point_transport(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('does not offer');
        $fees = [];
        OrderHelper::checkShopDelivery((new Shop)->forceFill(['id' => 11, 'product_fulfillment_methods' => ['pickup']]),
            ['delivery_type' => Order::POINT, 'delivery_point_id' => 1], 'en', $fees);
    }

    public function test_delivery_price_cannot_be_submitted_for_another_requested_city(): void
    {
        $this->database->table('delivery_prices')->insert(['id' => 1, 'shop_id' => 11, 'city_id' => 2]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('requested country, city or Area');
        $fees = [];
        OrderHelper::checkShopDelivery((new Shop)->forceFill(['id' => 11]),
            ['delivery_type' => Order::DELIVERY, 'delivery_price_id' => 1, 'city_id' => 9], 'en', $fees);
    }

    public function test_empty_and_unknown_product_fulfillment_choices_are_invalid(): void
    {
        $rules = (new \App\Http\Requests\Shop\StoreRequest)->rules();
        $rules = array_intersect_key($rules, array_flip(['product_fulfillment_methods', 'product_fulfillment_methods.*']));
        self::assertTrue(Validator::make(['product_fulfillment_methods' => []], $rules)->fails());
        self::assertTrue(Validator::make(['product_fulfillment_methods' => ['made-up']], $rules)->fails());
        self::assertFalse(Validator::make(['product_fulfillment_methods' => ['pickup']], $rules)->fails());
    }

    public function test_service_media_requires_real_owned_upload_and_trusted_origin(): void
    {
        $path = 'images/services/shops/11/101-12345678-1234-1234-1234-123456789abc.webp';
        $url = 'https://media.example.test/storage/' . $path;
        self::assertFalse(ServiceMedia::allows([$url], 11));
        Storage::disk('public')->put($path, 'synthetic-image');
        self::assertTrue(ServiceMedia::allows([$url], 11));
        self::assertFalse(ServiceMedia::allows([$url], 22));
        self::assertFalse(ServiceMedia::allows([str_replace('media.example.test', 'foreign.example.test', $url)], 11));
        self::assertFalse(ServiceMedia::allows([$url . '?token=synthetic'], 11));
        self::assertTrue(ServiceMedia::allows([], 11));
    }

    public function test_same_service_can_retain_legacy_image_but_not_copy_another_services_url(): void
    {
        $service = (new Service)->forceFill(['id' => 1, 'shop_id' => 11, 'img' => 'https://legacy.example.test/own.webp']);
        self::assertTrue(ServiceMedia::allows([$service->img], 11, $service));
        self::assertFalse(ServiceMedia::allows(['https://legacy.example.test/foreign.webp'], 11, $service));
    }

    public function test_service_media_retains_native_s3_url_and_tenant_namespace_contract(): void
    {
        Settings::create(['key' => 'aws', 'value' => '1']);
        $this->app['config']->set('filesystems.disks.s3', [
            'driver' => 'local', 'root' => $this->storageRoot . '/s3', 'url' => 'https://native-bucket.example.test',
        ]);
        $path = 'public/images/services/shops/11/101-12345678-1234-1234-1234-123456789abc.webp';
        Storage::disk('s3')->put($path, 'synthetic-image');
        $url = Storage::disk('s3')->url($path);
        self::assertTrue(ServiceMedia::allows([$url], 11));
        self::assertFalse(ServiceMedia::allows([$url], 22));
        self::assertFalse(ServiceMedia::allows([str_replace('native-bucket', 'foreign-bucket', $url)], 11));
    }

    public function test_service_image_create_replace_remove_preserves_content_price_duration_and_assignments(): void
    {
        $this->database->table('shops')->insert(['id' => 11, 'status' => Shop::APPROVED, 'service_min_price' => 10, 'service_max_price' => 10]);
        $this->database->table('currencies')->insert(['id' => 1, 'title' => 'XAF', 'active' => 1, 'default' => 1, 'rate' => 1]);
        $this->database->table('languages')->insert(['id' => 1, 'locale' => 'en', 'active' => 1, 'default' => 1]);
        request()->merge(['lang' => 'en', 'currency_id' => 1]);
        $models = new \App\Services\ModelService\ModelService;
        $result = $models->create(['shop_id' => 11, 'title' => ['en' => 'Photo fixture'],
            'description' => ['en' => 'Keep this description'], 'price' => 10, 'interval' => 30,
            'type' => Service::OFFLINE_IN, 'status' => Service::STATUS_ACCEPTED]);
        self::assertTrue($result['status'], $result['message'] ?? '');
        $service = $result['data'];
        self::assertNull($service->img);
        self::assertSame('Photo fixture', $service->translations()->first()->title);
        $this->database->table('service_masters')->insert(['shop_id' => 11, 'service_id' => $service->id, 'master_id' => 1, 'active' => 1]);
        $paths = [];
        foreach (['a', 'b'] as $letter) {
            $path = "images/services/shops/11/101-{$letter}2345678-1234-1234-1234-123456789abc.webp";
            Storage::disk('public')->put($path, 'synthetic-image');
            $paths[] = 'https://media.example.test/storage/' . $path;
        }
        $json = \Illuminate\Http\Request::create('/api/v1', 'PUT', [], [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode(['lang' => 'en', 'currency_id' => 1, 'previews' => ['https://foreign.example.test/preview.webp']]));
        $this->app->instance('request', $json);
        foreach ($paths as $path) {
            $changed = $models->update($service, ['images' => [$path]]);
            self::assertTrue($changed['status'], $changed['message'] ?? '');
            self::assertSame($path, $service->fresh()->img);
            self::assertNull($service->galleries()->first()->preview);
            self::assertSame(1, $service->galleries()->count());
        }
        self::assertFalse($models->update($service, ['img' => 'https://foreign.example.test/other.webp'])['status']);
        self::assertSame($paths[1], $service->fresh()->img);
        self::assertTrue($models->update($service, ['images' => []])['status']);
        self::assertNull($service->fresh()->img);
        self::assertSame(0, $service->galleries()->count());
        self::assertSame(10.0, (float) $service->fresh()->price);
        self::assertSame(30, (int) $service->fresh()->interval);
        self::assertSame('Keep this description', $service->translations()->first()->description);
        self::assertSame(1, $this->database->table('service_masters')->count());
    }

    public function test_gallery_management_excludes_foreign_service_images_but_preserves_other_native_media(): void
    {
        $this->database->table('services')->insert([
            ['id' => 1, 'shop_id' => 11], ['id' => 2, 'shop_id' => 22],
        ]);
        $this->database->table('galleries')->insert([
            ['id' => 1, 'loadable_type' => Service::class, 'loadable_id' => 1],
            ['id' => 2, 'loadable_type' => Service::class, 'loadable_id' => 2],
            ['id' => 3, 'loadable_type' => Shop::class, 'loadable_id' => 22],
        ]);
        self::assertSame([1], ServiceMedia::scopeGallery(\App\Models\Gallery::query())->orderBy('id')->pluck('id')->all());
    }

    public function test_profession_modes_and_zero_starting_price_are_projected_without_role_labels(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('master')->andReturn(false);
        $user->setRelation('roles', collect());
        $user->setRelation('serviceMasters', collect([(object) [
            'type' => Service::OFFLINE_IN, 'rate_total_price' => 0,
            'service' => (object) ['type' => Service::OFFLINE_IN, 'category' => (object) [
                'parent' => null, 'translation' => (object) ['title' => 'Hair Styling'],
            ]],
        ]]));
        $fields = SpecialistDiscovery::fields($user);
        self::assertSame('private', $fields['profile_visibility']);
        self::assertSame(['Hair Styling'], $fields['professional_domains']);
        self::assertSame([Service::OFFLINE_IN], $fields['service_modes']);
        self::assertSame(0, $fields['starting_price']);
        self::assertArrayNotHasKey('role', $fields);
    }

    public function test_discovery_assignment_requires_same_shop_accepted_invitation_active_service_and_assignment(): void
    {
        $this->database->table('users')->insert(['id' => 1, 'active' => 1]);
        $this->database->table('shops')->insert(['id' => 11, 'status' => Shop::APPROVED]);
        $this->database->table('services')->insert(['id' => 1, 'shop_id' => 11, 'status' => Service::STATUS_ACCEPTED]);
        $this->database->table('service_masters')->insert(['id' => 1, 'shop_id' => 11,
            'service_id' => 1, 'master_id' => 1, 'active' => 1]);
        $this->database->table('invitations')->insert(['user_id' => 1, 'shop_id' => 22,
            'role' => 'master', 'status' => \App\Models\Invitation::ACCEPTED]);
        self::assertFalse(SpecialistDiscovery::eligibleAssignments(ServiceMaster::query())->exists());
        $this->database->table('invitations')->update(['shop_id' => 11]);
        self::assertTrue(SpecialistDiscovery::eligibleAssignments(ServiceMaster::query())->exists());
        $this->database->table('service_masters')->update(['active' => 0]);
        self::assertFalse(SpecialistDiscovery::eligibleAssignments(ServiceMaster::query())->exists());
        $this->database->table('service_masters')->update(['active' => 1]);
        $this->database->table('services')->update(['status' => Service::STATUS_CANCELED]);
        self::assertFalse(SpecialistDiscovery::eligibleAssignments(ServiceMaster::query())->exists());
    }
}

final class PickupRequestProbe extends \App\Http\Requests\Order\StoreRequest
{
    public function prepareFixture(): void { $this->prepareForValidation(); }
}