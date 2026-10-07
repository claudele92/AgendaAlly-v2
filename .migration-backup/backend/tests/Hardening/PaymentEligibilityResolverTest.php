<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Payment;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\ShopLocation;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PaymentEligibility\PaymentContextFactory;
use App\Services\PaymentEligibility\PaymentEligibilityService;
use App\Services\PaymentService\BaseService;
use App\Services\PaymentService\MtnService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class PaymentEligibilityResolverTest extends IsolatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('payment_eligibility', require dirname(__DIR__, 2) . '/config/payment_eligibility.php');
        $this->app['config']->set('development.urls.api', 'https://synthetic.example');
        $this->checkoutSchema();
    }

    public function test_shop_country_and_currency_are_authoritative_not_client_market_or_display_currency(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->seedCurrencyAndCountry(2, 'USD', 20);
        $this->seedShop(7, 10, ShopLocation::PRODUCT);
        $this->authenticate(42);

        $context = (new PaymentContextFactory)->request([
            'shop_id' => 7,
            'country_id' => 20,
            'currency_id' => 2,
        ]);

        self::assertTrue($context['valid']);
        self::assertSame([10], $context['country_ids']);
        self::assertSame(1, $context['transaction_currency_id']);
        self::assertSame('EUR', $context['transaction_currency']);
        self::assertSame(2, $context['requested_currency_id']);
        self::assertFalse($context['display_currency_is_charge']);
    }

    public function test_shop_wide_collection_requires_both_domains_and_currency_capability_without_credentials(): void
    {
        $this->seedCurrencyAndCountry(1, 'XAF', 10);
        $this->seedCurrencyAndCountry(2, 'ZZZ', 20);
        $this->seedShop(7, 10, ShopLocation::PRODUCT, true, 20);
        $this->authenticate(42);
        $this->app['config']->set('development.payments.mode', 'test');
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'mtn', 'active' => true]);
        $this->database->table('country_payments')->insert([
            ['country_id' => 10, 'payment_id' => 5, 'active' => true],
            ['country_id' => 20, 'payment_id' => 5, 'active' => true],
        ]);
        $resolver = new PaymentEligibilityService;
        $shop = \App\Models\Shop::find(7);
        self::assertTrue($resolver->collectionPolicy($shop, 1)['vendor_direct_available']);
        self::assertSame('setup_required', $resolver->collectionPolicy($shop, 1)['vendor_direct_state']);
        self::assertFalse($resolver->collectionPolicy($shop)['vendor_direct_available']);
        self::assertSame(['product', 'booking'], $resolver->collectionPolicy($shop)['applies_to']);
        $this->database->table('currencies')->where('id', 2)->update(['title' => 'GHS']);
        self::assertFalse($resolver->collectionPolicy($shop)['vendor_direct_available']);
        self::assertSame('shop_wide_merchant_currency_conflict', $resolver->collectionPolicy($shop)['vendor_direct_unavailable_reason']);
        $this->database->table('currencies')->where('id', 2)->update(['title' => 'XAF']);
        self::assertTrue($resolver->collectionPolicy($shop)['vendor_direct_available']);
        self::assertSame('setup_required', $resolver->collectionPolicy($shop)['vendor_direct_state']);
        $this->database->table('country_payments')->where('country_id', 20)->update(['active' => false]);
        self::assertTrue($resolver->collectionPolicy($shop)['vendor_direct_available']);
    }

    public function test_foreign_booking_cart_and_order_targets_are_denied(): void
    {
        $this->database->table('bookings')->insert(['id' => 1, 'user_id' => 81, 'shop_id' => 7, 'currency_id' => 1]);
        $this->database->table('carts')->insert(['id' => 2, 'owner_id' => 81, 'currency_id' => 1]);
        $this->database->table('orders')->insert(['id' => 3, 'user_id' => 81, 'shop_id' => 7, 'currency_id' => 1]);
        $this->authenticate(42);

        foreach (['booking_id' => 1, 'cart_id' => 2, 'order_id' => 3] as $key => $id) {
            try {
                (new PaymentContextFactory)->target($key, $id);
                self::fail("A foreign $key target was accepted");
            } catch (AccessDeniedHttpException $exception) {
                self::assertSame('Payment target is unavailable to this account', $exception->getMessage());
            }
        }
    }

    public function test_product_and_service_countries_are_separate_and_ambiguous_shop_fails_closed(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->seedCurrencyAndCountry(2, 'GBP', 20);
        $this->seedShop(7, 10, ShopLocation::PRODUCT, false, 20);
        $this->authenticate(42);

        $factory = new PaymentContextFactory;
        $ambiguous = $factory->request(['shop_id' => 7]);
        $product = $factory->request(['shop_id' => 7, 'location_type' => ShopLocation::PRODUCT]);
        $service = $factory->request(['shop_id' => 7, 'location_type' => ShopLocation::SERVICE]);

        self::assertFalse($ambiguous['valid']);
        self::assertSame('business_location_type_required', $ambiguous['reason']);
        self::assertSame([10], $product['country_ids']);
        self::assertSame('EUR', $product['transaction_currency']);
        self::assertSame([20], $service['country_ids']);
        self::assertSame('GBP', $service['transaction_currency']);
        self::assertSame('product', $product['transaction_type']);
        self::assertSame('booking', $service['transaction_type']);
    }

    public function test_booking_collection_mode_uses_its_frozen_snapshot_not_the_live_shop_toggle(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->seedShop(7, 10, ShopLocation::SERVICE, true);
        $this->database->table('bookings')->insert([
            'id' => 11, 'user_id' => 42, 'shop_id' => 7, 'currency_id' => 1, 'collect_via_platform' => false,
        ]);
        $this->authenticate(42);

        $context = (new PaymentContextFactory)->target('booking_id', 11);

        self::assertTrue($context['valid']);
        self::assertSame('vendor_direct', $context['collection_mode']);
    }

    public function test_global_provider_credentials_do_not_bypass_environment_or_global_active_gates(): void
    {
        $this->seedCurrencyAndCountry(1, 'GBP', 10);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'stripe', 'active' => true]);
        $this->database->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 5, 'active' => true]);
        $this->database->table('payment_payloads')->insert([
            'payment_id' => 5,
            'payload' => json_encode(['stripe_sk' => 'synthetic-key', 'stripe_webhook_secret' => 'synthetic-hook']),
        ]);
        $context = $this->shopContext(7, 10, ShopLocation::PRODUCT);
        // A global payload test needs a platform collection context; the
        // absent Shop row otherwise makes the legacy helper vendor_direct.
        $context['collection_mode']='platform';
        $this->app['config']->set('development.payments.mode', 'disabled');

        $resolver = new PaymentEligibilityService;
        $disabled = $resolver->decision(Payment::find(5), $context);
        self::assertFalse($disabled['configured']);
        self::assertSame('CONFIGURATION_INCOMPLETE',$disabled['configuration_state']);
        self::assertContains('stripe_pk',$disabled['configuration']['missing']);
        self::assertFalse($disabled['eligible']);
        self::assertContains('environment_disabled', $disabled['reasons']);
        self::assertFalse($resolver->catalogReady(Payment::find(5)));

        $this->app['config']->set('development.payments.mode', 'test');
        $this->database->table('payments')->where('id', 5)->update(['active' => false]);
        $inactive = $resolver->decision(Payment::find(5), $context);
        self::assertFalse($inactive['eligible']);
        self::assertContains('globally_disabled', $inactive['reasons']);
        self::assertFalse($resolver->catalogReady(Payment::find(5)));
    }

    public function test_synthetic_mtn_configuration_is_scoped_to_collector_and_exact_currency_without_decryption(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->seedShop(7, 10, ShopLocation::PRODUCT);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'mtn', 'active' => true]);
        $this->database->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 5, 'active' => true]);
        $this->database->table('shop_payments')->insert([
            'shop_id' => 7, 'payment_id' => 5, 'status' => true,
            'api_key' => 'synthetic-direct-key', 'subscription_key' => 'synthetic-direct-sub',
            'api_user' => 'synthetic-direct-user', 'target_environment' => 'sandbox', 'currency' => 'EUR',
        ]);
        $this->database->table('platform_payment_configs')->insert([
            'country_id' => 10, 'payment_id' => 5, 'status' => true,
            'api_key' => 'synthetic-platform-key', 'subscription_key' => 'synthetic-platform-sub',
            'api_user' => 'synthetic-platform-user', 'target_environment' => 'sandbox', 'currency' => 'EUR',
        ]);
        $this->app['config']->set('development.payments.mode', 'sandbox');
        $this->app['config']->set('development.payments.sandbox_providers', ['mtn']);
        $resolver = new PaymentEligibilityService;
        $payment = Payment::find(5);

        $direct = $resolver->decision($payment, $this->shopContext(7, 10, ShopLocation::PRODUCT));
        $platform = $resolver->decision($payment, array_merge(
            $this->shopContext(7, 10, ShopLocation::PRODUCT),
            ['collection_mode' => 'platform']
        ));

        self::assertTrue($direct['eligible']);
        self::assertTrue($platform['eligible']);
        self::assertTrue($direct['configured']);
        self::assertTrue($direct['currency_supported']);
        self::assertStringNotContainsString('synthetic-direct-key', json_encode($direct));
        self::assertStringNotContainsString('synthetic-platform-key', json_encode($platform));

        $this->database->table('shop_payments')->where('shop_id', 7)->update(['currency' => 'USD']);
        $wrongCurrency = $resolver->decision($payment, $this->shopContext(7, 10, ShopLocation::PRODUCT));
        self::assertFalse($wrongCurrency['eligible']);
        self::assertTrue($wrongCurrency['currency_supported']);
        self::assertContains('collector_currency_mismatch', $wrongCurrency['reasons']);
    }

    public function test_unknown_currency_support_and_country_pivot_never_enable_external_checkout(): void
    {
        $this->seedCurrencyAndCountry(1, 'GBP', 10);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'paystack', 'active' => true]);
        $this->database->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 5, 'active' => true]);
        $this->database->table('payment_payloads')->insert([
            'payment_id' => 5, 'payload' => json_encode(['paystack_sk' => 'synthetic-paystack-key']),
        ]);
        $this->app['config']->set('development.payments.mode', 'test');
        $resolver = new PaymentEligibilityService;
        $payment = Payment::find(5);
        $decision = $resolver->decision($payment, ['collection_mode' => 'platform'] + $this->shopContext(7, 10, ShopLocation::PRODUCT));

        self::assertTrue($decision['country_permitted']);
        self::assertTrue($decision['configured']);
        self::assertNull($decision['currency_supported']);
        self::assertFalse($decision['eligible']);
        self::assertContains('currency_support_unknown', $decision['reasons']);

        $this->app['config']->set('development.payments.mode', 'disabled');
        self::assertFalse($resolver->catalogReady($payment));
        self::assertFalse($resolver->decision($payment, $this->shopContext(7, 10, ShopLocation::PRODUCT))['eligible']);
    }

    public function test_cash_wallet_legacy_compatibility_is_explicit_and_strict_country_policy_denies(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->database->table('payments')->insert([
            ['id' => 5, 'tag' => 'cash', 'active' => true],
            ['id' => 6, 'tag' => 'wallet', 'active' => true],
        ]);
        $this->database->table('wallets')->insert(['id' => 9, 'user_id' => 42, 'currency_id' => 1, 'price' => 12.5]);
        $this->authenticate(42);
        $context = $this->shopContext(7, 10, ShopLocation::PRODUCT);
        $resolver = new PaymentEligibilityService;

        self::assertTrue($resolver->decision(Payment::find(5), $context)['eligible']);
        self::assertTrue($resolver->decision(Payment::find(6), $context)['eligible']);

        $this->app['config']->set('payment_eligibility.internal_country_policy', 'strict');
        foreach ([5, 6] as $paymentId) {
            $strict = $resolver->decision(Payment::find($paymentId), $context);
            self::assertFalse($strict['eligible']);
            self::assertFalse($strict['country_permitted']);
            self::assertContains('country_policy_denied', $strict['reasons']);
        }
    }

    public function test_initiation_eligibility_guard_runs_before_wallet_contribution_mutates_balance(): void
    {
        $this->seedCurrencyAndCountry(1, 'GBP', 10);
        $this->seedShop(7, 10, ShopLocation::SERVICE);
        $this->database->table('bookings')->insert([
            'id' => 11, 'user_id' => 42, 'shop_id' => 7, 'currency_id' => 1,
            'collect_via_platform' => true, 'total_price' => 25, 'rate' => 1, 'status' => 'booked',
        ]);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'stripe', 'active' => true]);
        $this->database->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 5, 'active' => true]);
        $this->database->table('wallets')->insert(['id' => 9, 'user_id' => 42, 'currency_id' => 1, 'price' => 80]);
        $this->authenticate(42);
        $this->app['config']->set('development.payments.mode', 'disabled');

        $service = new class extends BaseService {
            public function __construct()
            {
                $this->language = 'en';
                $this->currency = 1;
            }

            protected function getModelClass(): string
            {
                return \App\Models\Payout::class;
            }
        };

        try {
            $service->getPayload(['booking_id' => 11, 'from_wallet_price' => 15], [], 5);
            self::fail('An ineligible payment method passed initiation');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('Payment method unavailable', $exception->getMessage());
        }

        self::assertSame(80.0, (float) $this->database->table('wallets')->where('id', 9)->value('price'));
    }

    public function test_mixed_shop_and_multi_currency_cart_contexts_fail_safely(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->seedCurrencyAndCountry(2, 'USD', 20);
        $this->seedShop(7, 10, ShopLocation::PRODUCT, false);
        $this->seedShop(8, 10, ShopLocation::PRODUCT, true);
        $this->seedShop(9, 20, ShopLocation::PRODUCT, true);
        $this->database->table('carts')->insert(['id' => 4, 'owner_id' => 42, 'currency_id' => 1]);
        $this->database->table('user_carts')->insert([
            ['id' => 41, 'cart_id' => 4],
            ['id' => 42, 'cart_id' => 4],
        ]);
        $this->database->table('cart_details')->insert([
            ['id' => 51, 'user_cart_id' => 41, 'shop_id' => 7],
            ['id' => 52, 'user_cart_id' => 42, 'shop_id' => 8],
        ]);
        $this->authenticate(42);
        $factory = new PaymentContextFactory;

        $sameCurrency = $factory->target('cart_id', 4);
        self::assertTrue($sameCurrency['valid']);
        self::assertCount(2, $sameCurrency['shop_ids']);
        self::assertSame('mixed', $sameCurrency['collection_mode']);

        $this->database->table('cart_details')->where('id', 52)->update(['shop_id' => 9]);
        $multiCurrency = $factory->target('cart_id', 4);
        self::assertFalse($multiCurrency['valid']);
        self::assertSame('multi_currency_cart_unsupported', $multiCurrency['reason']);
    }

    public function test_safe_decision_metadata_contains_no_credential_values_or_secrets(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'stripe', 'active' => true]);
        $this->database->table('payment_payloads')->insert([
            'payment_id' => 5,
            'payload' => json_encode(['stripe_sk' => 'secret-fixture-value', 'stripe_webhook_secret' => 'hook-fixture-value']),
        ]);
        $this->app['config']->set('development.payments.mode', 'test');
        $decision = (new PaymentEligibilityService)->decision(
            Payment::find(5),
            $this->shopContext(7, 10, ShopLocation::PRODUCT)
        );
        $encoded = json_encode($decision);

        self::assertSame(
            ['id', 'tag', 'globally_active', 'capability','capability_state','configuration','configuration_state',
                'runtime_state','runtime_ready','runtime_reasons','integration_ready', 'vendor_configurable', 'transaction_types', 'collection_modes', 'country_permitted',
                'currency_supported', 'supported_currencies', 'collection_mode', 'configured', 'eligible',
                'checkout_available','checkout_state','quote_state','activation_allowed',
                'available_for_configuration', 'reasons'],
            array_keys($decision)
        );
        self::assertStringNotContainsString('secret-fixture-value', $encoded);
        self::assertStringNotContainsString('hook-fixture-value', $encoded);
    }

    public function test_cart_checkout_guard_denies_foreign_cart_before_any_checkout_work(): void
    {
        $this->database->table('carts')->insert(['id' => 4, 'owner_id' => 81, 'currency_id' => 1]);
        $this->authenticate(42);

        try {
            (new PaymentEligibilityService)->assertCartCheckout(['cart_id' => 4]);
            self::fail('A foreign cart passed the order checkout guard');
        } catch (AccessDeniedHttpException $exception) {
            self::assertSame('Payment target is unavailable to this account', $exception->getMessage());
        }
    }

    public function test_cart_checkout_guard_denies_globally_disabled_cash_and_wallet_before_mutation(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->seedShop(7, 10, ShopLocation::PRODUCT);
        $this->seedOwnedCart(4, 7, 1);
        $this->database->table('payments')->insert([
            ['id' => 5, 'tag' => 'cash', 'active' => false],
            ['id' => 6, 'tag' => 'wallet', 'active' => false],
        ]);
        $this->database->table('wallets')->insert(['id' => 9, 'user_id' => 42, 'currency_id' => 1, 'price' => 80]);
        $this->authenticate(42);
        $resolver = new PaymentEligibilityService;

        try {
            $resolver->assertCartCheckout(['cart_id' => 4, 'payment_id' => 5, 'currency_id' => 1]);
            self::fail('Globally disabled cash passed the cart guard');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('globally_disabled', $exception->getMessage());
        }

        // Cash itself is enabled now, but it must not confer permission to
        // attach a wallet contribution when the wallet method is disabled.
        $this->database->table('payments')->where('id', 5)->update(['active' => true]);
        try {
            $resolver->assertCartCheckout([
                'cart_id' => 4, 'payment_id' => 5, 'currency_id' => 1, 'from_wallet_price' => 12,
            ]);
            self::fail('Cash checkout bypassed the globally disabled wallet contribution guard');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('globally_disabled', $exception->getMessage());
        }

        self::assertSame(80.0, (float) $this->database->table('wallets')->where('id', 9)->value('price'));
    }

    public function test_wallet_withdrawal_guards_booking_and_cart_before_debit(): void
    {
        $this->seedCurrencyAndCountry(1, 'EUR', 10);
        $this->seedShop(7, 10, ShopLocation::PRODUCT, true, 10);
        $this->database->table('bookings')->insert([
            'id' => 11, 'user_id' => 42, 'shop_id' => 7, 'currency_id' => 1, 'collect_via_platform' => true,
        ]);
        $this->seedOwnedCart(4, 7, 1);
        $this->database->table('payments')->insert(['id' => 6, 'tag' => 'wallet', 'active' => false]);
        $this->database->table('wallets')->insert(['id' => 9, 'user_id' => 42, 'currency_id' => 1, 'price' => 80]);
        $this->authenticate(42);

        $user = new User;
        $user->forceFill(['id' => 42]);
        $user->setRelation('wallet', (new Wallet)->forceFill(['id' => 9, 'uuid' => 'fixture-wallet']));
        $service = new class extends BaseService {
            public function __construct()
            {
                $this->language = 'en';
                $this->currency = 1;
            }

            protected function getModelClass(): string
            {
                return \App\Models\Payout::class;
            }
        };

        foreach ([
            Booking::query()->find(11),
            Cart::query()->find(4),
        ] as $payable) {
            try {
                $service->walletPriceWithdraw($payable, ['from_wallet_price' => 12], $user);
                self::fail('A disabled wallet method debited a payable');
            } catch (RuntimeException $exception) {
                self::assertStringContainsString('globally_disabled', $exception->getMessage());
            }
        }

        self::assertSame(80.0, (float) $this->database->table('wallets')->where('id', 9)->value('price'));
    }

    public function test_missing_mtn_catalog_entry_fails_before_network_or_catalog_insertion(): void
    {
        $service = (new \ReflectionClass(MtnService::class))->newInstanceWithoutConstructor();

        try {
            $service->processTransaction(['booking_id' => 99]);
            self::fail('MTN checkout created an absent catalog entry');
        } catch (\Exception $exception) {
            self::assertSame('MTN Mobile Money payment method is unavailable', $exception->getMessage());
        }

        self::assertSame(0, $this->database->table('payments')->count());
    }

    public function test_explicit_collection_save_is_idempotent_and_cannot_bypass_service_context(): void
    {
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->seedCurrencyAndCountry(1, 'XAF', 10);
        $this->seedCurrencyAndCountry(2, 'ZZZ', 20);
        $this->seedShop(7, 10, ShopLocation::PRODUCT, true, 20);
        $this->authenticate(42);
        $this->app['config']->set('development.payments.mode', 'test');
        $this->database->table('shops')->where('id', 7)->update(['uuid' => 'collection-fixture']);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'mtn', 'active' => true]);
        $this->database->table('country_payments')->insert([
            ['country_id' => 10, 'payment_id' => 5, 'active' => true],
            ['country_id' => 20, 'payment_id' => 5, 'active' => true],
        ]);
        Schema::create('languages', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->boolean('default')->default(true);
        });
        $this->database->table('languages')->insert(['locale' => 'en']);
        Schema::create('translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->string('key');
            $table->string('value');
        });
        $controller = Mockery::mock(\App\Http\Controllers\API\v1\Dashboard\Seller\ShopController::class)->makePartial();
        $controller->shouldReceive('successResponse')->andReturn(new \Illuminate\Http\JsonResponse(['status' => true]));
        $reflection = new \ReflectionClass(\App\Http\Controllers\API\v1\Dashboard\Seller\ShopController::class);
        $reflection->getProperty('shop')->setValue($controller, \App\Models\Shop::find(7));
        $reflection->getProperty('language')->setValue($controller, 'en');
        $request = CollectionStateRequestProbe::create('/', 'POST', ['collection_mode' => 'vendor_direct', 'location_type' => 1]);
        $this->app->instance('request', $request);
        self::assertFalse($controller->setCollectViaPlatform($request)->getData(true)['status']);
        self::assertTrue((bool) \App\Models\Shop::find(7)->collect_via_platform);
        $this->database->table('currencies')->where('id', 2)->update(['title' => 'XAF']);
        foreach ([false, false, true, true] as $desired) {
            $request = CollectionStateRequestProbe::create('/', 'POST', [
                'collection_mode' => $desired ? 'platform' : 'vendor_direct',
            ]);
            $this->app->instance('request', $request);
            self::assertTrue($controller->setCollectViaPlatform($request)->getData(true)['status']);
            self::assertSame($desired, (bool) \App\Models\Shop::find(7)->collect_via_platform);
        }
    }

    private function checkoutSchema(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
        });
        Schema::create('countries', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('currency_id')->nullable();
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
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('type');
        });
        Schema::create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('shop_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->boolean('collect_via_platform')->default(false);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('rate', 10, 4)->default(1);
            $table->string('status')->nullable();
        });
        Schema::create('carts', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('owner_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
        });
        Schema::create('user_carts', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('cart_id');
        });
        Schema::create('cart_details', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_cart_id');
            $table->unsignedInteger('shop_id');
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('shop_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
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
        Schema::create('payment_payloads', function (Blueprint $table): void {
            $table->unsignedInteger('payment_id')->primary();
            $table->json('payload')->nullable();
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
            $table->string('merchant_key')->nullable();
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
            $table->string('merchant_key')->nullable();
            $table->string('currency')->nullable();
        });
        Schema::create('wallets', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('currency_id');
            $table->decimal('price', 12, 2)->default(0);
        });
    }

    private function seedCurrencyAndCountry(int $currencyId, string $currency, int $countryId): void
    {
        if (!$this->database->table('currencies')->where('id', $currencyId)->exists()) {
            $this->database->table('currencies')->insert(['id' => $currencyId, 'title' => $currency]);
        }
        if (!$this->database->table('countries')->where('id', $countryId)->exists()) {
            $this->database->table('countries')->insert(['id' => $countryId, 'currency_id' => $currencyId, 'active' => true]);
        }
    }

    private function seedShop(int $shopId, int $productCountryId, int $productOrService, bool $platform = false, ?int $serviceCountryId = null): void
    {
        $this->database->table('shops')->insert([
            'id' => $shopId, 'user_id' => 42, 'collect_via_platform' => $platform,
        ]);
        $this->database->table('shop_locations')->insert([
            'shop_id' => $shopId, 'country_id' => $productCountryId, 'type' => $productOrService,
        ]);
        if ($serviceCountryId !== null) {
            $this->database->table('shop_locations')->insert([
                'shop_id' => $shopId, 'country_id' => $serviceCountryId, 'type' => ShopLocation::SERVICE,
            ]);
        }
    }

    private function shopContext(int $shopId, int $countryId, int $locationType): array
    {
        $currencyId = (int) $this->database->table('countries')->where('id', $countryId)->value('currency_id');
        return [
            'valid' => true, 'reason' => null, 'shop_ids' => [$shopId], 'country_ids' => [$countryId],
            'transaction_type' => $locationType === ShopLocation::SERVICE ? 'booking' : 'product',
            'transaction_currency_id' => $currencyId,
            'transaction_currency' => (string) $this->database->table('currencies')->where('id', $currencyId)->value('title'),
            'requested_currency_id' => null, 'display_currency_is_charge' => true,
            'collection_mode' => (bool) $this->database->table('shops')->where('id', $shopId)->value('collect_via_platform')
                ? 'platform' : 'vendor_direct',
            'location_type' => $locationType, 'country_policy_mode' => 'legacy_compatibility',
        ];
    }

    private function authenticate(int $id): void
    {
        $user = new User;
        $user->forceFill(['id' => $id]);
        $user->setRelation('countryAdmin', (object) ['country_id' => null]);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
    }

    private function seedOwnedCart(int $cartId, int $shopId, int $currencyId): void
    {
        $this->database->table('carts')->insert([
            'id' => $cartId, 'owner_id' => 42, 'currency_id' => $currencyId,
        ]);
        $this->database->table('user_carts')->insert(['id' => $cartId * 10, 'cart_id' => $cartId]);
        $this->database->table('cart_details')->insert([
            'id' => $cartId * 10 + 1, 'user_cart_id' => $cartId * 10, 'shop_id' => $shopId,
        ]);
    }
}

final class CollectionStateRequestProbe extends \Illuminate\Http\Request
{
    public function validate(array $rules): array
    {
        return \Illuminate\Support\Facades\Validator::make($this->all(), $rules)->validate();
    }
}