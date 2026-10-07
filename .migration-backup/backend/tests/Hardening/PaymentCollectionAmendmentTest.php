<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Payment;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use App\Services\PaymentEligibility\PaymentContextFactory;
use App\Services\PaymentEligibility\PaymentEligibilityService;
use App\Services\ShopServices\ShopActivityService;
use App\Http\Controllers\API\v1\Dashboard\Seller\SellerBaseController;
use App\Http\Controllers\API\v1\Dashboard\Seller\ShopController;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionProperty;

final class PaymentCollectionAmendmentTest extends IsolatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('payment_eligibility', require dirname(__DIR__, 2) . '/config/payment_eligibility.php');
        $this->app['config']->set('auth.defaults.guard','sanctum');
        $this->app['config']->set('auth.guards.sanctum',['driver'=>'sanctum','provider'=>'users']);
        $this->app['config']->set('auth.providers.users',['driver'=>'eloquent','model'=>User::class]);
        $this->schema();
        Schema::create('languages', function (Blueprint $t): void {
            $t->increments('id'); $t->string('locale'); $t->boolean('default')->default(true);
        });
        $this->database->table('languages')->insert(['locale'=>'en','default'=>true]);
        $this->authenticate(42, false);
        $translator = Mockery::mock(\Illuminate\Contracts\Translation\Translator::class);
        $translator->shouldReceive('get')->andReturnUsing(static fn (string $key): string => $key);
        $this->app->instance('translator', $translator);
    }

    public function test_new_shops_default_to_platform_but_explicit_and_hydrated_vendor_direct_are_preserved(): void
    {
        self::assertTrue((new Shop)->collect_via_platform);
        self::assertFalse((new Shop(['collect_via_platform' => false]))->collect_via_platform);

        $this->database->table('shops')->insert([
            'id' => 7, 'uuid' => 'persisted-vendor-direct', 'collect_via_platform' => false,
        ]);
        self::assertFalse(Shop::query()->find(7)->collect_via_platform);
    }

    public function test_shop_collection_service_supports_idempotent_desired_state_and_legacy_toggle(): void
    {
        $this->database->table('shops')->insert([
            'id' => 7, 'uuid' => 'collection-shop', 'collect_via_platform' => true,
        ]);
        $service = new class extends ShopActivityService {
            public function __construct()
            {
                $this->model = new Shop;
            }
        };

        $service->changeCollectViaPlatformStatus('collection-shop', false);
        self::assertFalse(Shop::query()->find(7)->collect_via_platform);
        $service->changeCollectViaPlatformStatus('collection-shop', false);
        self::assertFalse(Shop::query()->find(7)->collect_via_platform);
        $service->changeCollectViaPlatformStatus('collection-shop', true);
        self::assertTrue(Shop::query()->find(7)->collect_via_platform);
        $service->changeCollectViaPlatformStatus('collection-shop');
        self::assertFalse(Shop::query()->find(7)->collect_via_platform);
    }

    public function test_platform_routing_does_not_use_vendor_shop_credentials_or_offer_vendor_configuration(): void
    {
        $this->seedBusinessContext();
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'mtn', 'active' => true]);
        $this->database->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 5, 'active' => true]);
        $this->database->table('shop_payments')->insert($this->mtnConfig([
            'shop_id' => 7, 'payment_id' => 5,
        ]));
        $this->app['config']->set('development.payments.mode', 'sandbox');
        $this->app['config']->set('development.payments.sandbox_providers', ['mtn']);
        $this->authenticate(42, true);

        $factory = new PaymentContextFactory;
        $resolver = new PaymentEligibilityService;
        $platform = $factory->shop(Shop::query()->find(7), ShopLocation::PRODUCT);
        $direct = array_merge($platform, ['collection_mode' => 'vendor_direct']);
        $payment = Payment::query()->find(5);

        $directCharge=$resolver->decision($payment, $direct);
        self::assertTrue($directCharge['configured']);
        self::assertFalse($directCharge['eligible']);
        self::assertFalse($directCharge['runtime_ready']);
        self::assertContains('callback_url_not_configured',$directCharge['runtime_reasons']);
        $platformCharge = $resolver->decision($payment, $platform);
        self::assertFalse($platformCharge['configured']);
        self::assertFalse($platformCharge['eligible']);
        self::assertContains('collector_not_configured', $platformCharge['reasons']);

        $platformConfiguration = $resolver->decision($payment, $platform, true);
        self::assertTrue($platformConfiguration['configured']);
        self::assertTrue($platformConfiguration['available_for_configuration']);
        self::assertSame('vendor_direct',$platformConfiguration['collection_mode']);
        self::assertFalse($platformConfiguration['checkout_available']);

        $this->database->table('platform_payment_configs')->insert($this->mtnConfig([
            'country_id' => 10, 'payment_id' => 5,
        ]));
        $this->database->table('platform_payment_configs')->where('country_id', 10)
            ->update(['api_key' => 'platform-api-key']);
        $configuredPlatform=$resolver->decision($payment, $platform);
        self::assertTrue($configuredPlatform['configured']);
        self::assertFalse($configuredPlatform['eligible']);
        self::assertContains('callback_url_not_configured',$configuredPlatform['runtime_reasons']);
    }

    public function test_optional_vendor_direct_collection_policy_fails_closed_and_lists_only_supported_mtn_setup(): void
    {
        $this->seedBusinessContext();
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'mtn', 'active' => true]);
        $this->database->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 5, 'active' => true]);
        $this->authenticate(42, true);
        $shop = Shop::query()->find(7);
        $resolver = new PaymentEligibilityService;

        // An active country pivot alone is not enough when provider activation
        // is disabled by environment policy.
        $this->app['config']->set('development.payments.mode', 'disabled');
        $disabled = $resolver->collectionPolicy($shop, ShopLocation::PRODUCT);
        self::assertTrue($disabled['vendor_direct_available']); // Setup capability, not checkout activation.
        self::assertSame([5], $disabled['vendor_direct_method_ids']);
        self::assertSame('setup_required',$disabled['vendor_direct_state']);
        $disabledContext=(new PaymentContextFactory)->shop($shop,ShopLocation::PRODUCT);
        $disabledContext['collection_mode']='vendor_direct';
        $disabledDecision=$resolver->decision(Payment::findOrFail(5),$disabledContext);
        self::assertFalse($disabledDecision['activation_allowed']);
        self::assertFalse($disabledDecision['eligible']);
        self::assertFalse($disabledDecision['checkout_available']);
        self::assertContains('environment_disabled',$disabledDecision['reasons']);

        $this->app['config']->set('development.payments.mode', 'sandbox');
        $this->app['config']->set('development.payments.sandbox_providers', ['mtn']);
        $available = $resolver->collectionPolicy($shop, ShopLocation::PRODUCT);
        self::assertSame('platform', $available['mode']);
        self::assertSame('platform', $available['default_mode']);
        self::assertTrue($available['platform_available']);
        self::assertTrue($available['vendor_direct_available']);
        self::assertSame([5], $available['vendor_direct_method_ids']);
        self::assertTrue($available['can_manage']);
        self::assertFalse($available['payout_methods_implemented']);

        $this->database->table('payments')->where('id', 5)->update(['active' => false]);
        self::assertTrue($resolver->collectionPolicy($shop, ShopLocation::PRODUCT)['vendor_direct_available']);
        $inactive=$resolver->decision(Payment::findOrFail(5),$disabledContext);
        self::assertFalse($inactive['globally_active']);
        self::assertFalse($inactive['eligible']);
        // Environment activation and global active are separate gates.
        self::assertTrue($inactive['activation_allowed']);
        self::assertContains('globally_disabled',$inactive['reasons']);
    }

    public function test_seller_collection_endpoint_enforces_permission_conflicts_and_direct_availability(): void
    {
        $this->seedBusinessContext();
        $shop = Shop::query()->find(7);
        $this->authenticate(42, false);
        $unauthorized = $this->callCollectionEndpoint($shop, ['collection_mode' => 'vendor_direct']);
        self::assertSame('ERROR_403', $unauthorized->getData(true)['statusCode']);
        self::assertTrue(Shop::query()->find(7)->collect_via_platform);

        $this->authenticate(42, true);
        $conflicting = $this->callCollectionEndpoint($shop, [
            'collection_mode' => 'vendor_direct', 'collect_via_platform' => true,
        ]);
        self::assertSame(400, $conflicting->getStatusCode());
        self::assertSame('Conflicting collection choices', $conflicting->getData(true)['message']);
        self::assertTrue(Shop::query()->find(7)->collect_via_platform);

        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'mtn', 'active' => true]);
        $this->database->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 5, 'active' => true]);
        $this->app['config']->set('development.payments.mode', 'disabled');
        $unavailable = $this->callCollectionEndpoint($shop, ['collection_mode' => 'vendor_direct']);
        self::assertSame(200, $unavailable->getStatusCode()); // Configuration selection, not provider activation.
        self::assertFalse(Shop::query()->find(7)->collect_via_platform);
        $context=(new PaymentContextFactory)->shop(Shop::findOrFail(7),ShopLocation::PRODUCT);
        $decision=(new PaymentEligibilityService)->decision(Payment::findOrFail(5),$context);
        self::assertFalse($decision['activation_allowed']);
        self::assertFalse($decision['eligible']);
        self::assertFalse($decision['checkout_available']);
        self::assertContains('environment_disabled',$decision['reasons']);
    }

    private function schema(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
        });
        Schema::create('countries', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('currency_id');
            $table->boolean('active')->default(true);
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uuid')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('collect_via_platform')->default(false);
            $table->timestamps();
        });
        Schema::create('shop_locations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('type');
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag');
            $table->boolean('active')->default(true);
        });
        Schema::create('country_payments', function (Blueprint $table): void {
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('payment_id');
            $table->boolean('active')->default(false);
        });
        Schema::create('shop_payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('payment_id');
            $table->boolean('status')->default(false);
            $table->string('api_key')->nullable();
            $table->string('subscription_key')->nullable();
            $table->string('api_user')->nullable();
            $table->string('target_environment')->nullable();
            $table->string('currency')->nullable();
        });
        Schema::create('platform_payment_configs', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('payment_id');
            $table->boolean('status')->default(false);
            $table->string('api_key')->nullable();
            $table->string('subscription_key')->nullable();
            $table->string('api_user')->nullable();
            $table->string('target_environment')->nullable();
            $table->string('currency')->nullable();
        });
    }

    private function seedBusinessContext(): void
    {
        $this->database->table('currencies')->insert(['id' => 1, 'title' => 'EUR']);
        $this->database->table('countries')->insert(['id' => 10, 'currency_id' => 1, 'active' => true]);
        $this->database->table('shops')->insert([
            'id' => 7, 'uuid' => 'collection-shop', 'user_id' => 42, 'collect_via_platform' => true,
        ]);
        $this->database->table('shop_locations')->insert([
            'shop_id' => 7, 'country_id' => 10, 'type' => ShopLocation::PRODUCT,
        ]);
    }

    private function mtnConfig(array $identity): array
    {
        return $identity + [
            'status' => true,
            'api_key' => 'synthetic-api-key',
            'subscription_key' => 'synthetic-subscription',
            'api_user' => 'synthetic-api-user',
            'target_environment' => 'sandbox',
            'currency' => 'EUR',
        ];
    }

    private function authenticate(int $id, bool $canManage): void
    {
        $user = new class($canManage) extends User {
            public function __construct(private bool $canManage) {}

            public function hasShopPermission(int $shopId, string $permissionKey): bool
            {
                return $this->canManage && $permissionKey === 'payments.gateways.manage';
            }
        };
        $user->forceFill(['id' => $id]);
        $user->setRelation('roles',collect([(new \Spatie\Permission\Models\Role)->forceFill([
            'name'=>'seller','guard_name'=>'sanctum',
        ])]));
        $user->setRelation('countryAdmin', (object) ['country_id' => null]);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('check')->andReturn(true);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
    }

    private function callCollectionEndpoint(Shop $shop, array $input): \Illuminate\Http\JsonResponse
    {
        if (!Request::hasMacro('validate')) {
            Request::macro('validate', function (array $rules): array {
                return $this->all();
            });
        }
        $controller = (new \ReflectionClass(ShopController::class))->newInstanceWithoutConstructor();
        $shopProperty = new ReflectionProperty(SellerBaseController::class, 'shop');
        $shopProperty->setAccessible(true);
        $shopProperty->setValue($controller, $shop);
        $languageProperty = new ReflectionProperty(\App\Http\Controllers\Controller::class, 'language');
        $languageProperty->setAccessible(true);
        $languageProperty->setValue($controller, 'en');
        $request = Request::create('/api/v1/dashboard/seller/shops/collection', 'POST', $input);

        return $controller->setCollectViaPlatform($request);
    }
}