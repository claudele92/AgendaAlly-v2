<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Country;
use App\Models\CountryAdmin;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PlatformPaymentConfig;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentService\OrangeService;
use App\Services\PlatformPaymentConfigService\PlatformPaymentConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A subscription/ads-package purchase is a platform-fee payment: it must
 * resolve the platform's own PlatformPaymentConfig for the shop's
 * country, never the shop's own ShopPayment config (that's for
 * customer-facing checkout only) — see BaseService::resolveGatewayConfig().
 */
class PlatformPaymentConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['manager', 'seller'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        Cache::put('rjkcvd.ewoidfh', (object) ['local' => true, 'active' => true]);
    }

    /** @return array{0: Shop, 1: Country, 2: Payment} */
    private function makeShopCountryAndPayment(string $countryCurrency = 'XOF', bool $gatewayAvailable = true): array
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        $currency = Currency::query()->create(['title' => $countryCurrency, 'symbol' => $countryCurrency, 'rate' => 1, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);
        $country  = Country::query()->create(['region_id' => $region->id, 'active' => true, 'currency_id' => $currency->id]);

        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id' => $shop->id, 'region_id' => $region->id, 'country_id' => $country->id, 'type' => ShopLocation::PRODUCT,
        ]);

        $payment = Payment::query()->create(['tag' => Payment::TAG_ORANGE, 'active' => true, 'input' => 15]);

        if ($gatewayAvailable) {
            $country->payments()->attach($payment->id, ['active' => true]);
        }

        return [$shop, $country, $payment];
    }

    public function test_a_subscription_purchase_uses_the_platform_config_not_the_shops_own_config(): void
    {
        [$shop, $country, $payment] = $this->makeShopCountryAndPayment('XOF');

        // The shop's own (customer-facing) Orange config — must be ignored here.
        ShopPayment::query()->create([
            'shop_id' => $shop->id, 'payment_id' => $payment->id, 'status' => true,
            'client_id' => 'shop-client', 'merchant_key' => 'shop-secret', 'currency' => 'XOF',
        ]);

        // The platform's own config for this country — must be used instead.
        PlatformPaymentConfig::query()->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true,
            'client_id' => 'platform-client', 'merchant_key' => 'platform-secret', 'currency' => 'XOF',
        ]);

        $subscription = Subscription::factory()->create(['price' => 500, 'active' => 1]);

        $this->actingAs($shop->seller, 'sanctum');

        Http::fake([
            '*/oauth/token'            => Http::response(['access_token' => 'fake-token'], 200),
            '*/api/eWallet/v4/qrcode'  => Http::response(['deepLink' => 'https://example.test/pay'], 200),
        ]);

        (new OrangeService())->processTransaction(['subscription_id' => $subscription->id]);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), 'oauth/token')) {
                return false;
            }

            return $request->data()['client_id'] === 'platform-client'
                && $request->data()['client_secret'] === 'platform-secret';
        });
    }

    public function test_an_ads_package_purchase_uses_the_platform_config_for_the_shops_country(): void
    {
        [$shop, $country, $payment] = $this->makeShopCountryAndPayment('XAF');

        PlatformPaymentConfig::query()->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true,
            'client_id' => 'platform-client-2', 'merchant_key' => 'platform-secret-2', 'currency' => 'XAF',
        ]);

        $adsPackage = \App\Models\AdsPackage::query()->create(['price' => 200, 'time' => 7, 'time_type' => 'day']);

        $this->actingAs($shop->seller, 'sanctum');

        Http::fake([
            '*/oauth/token'           => Http::response(['access_token' => 'fake-token'], 200),
            '*/api/eWallet/v4/qrcode' => Http::response(['deepLink' => 'https://example.test/pay'], 200),
        ]);

        (new OrangeService())->processTransaction(['ads_package_id' => $adsPackage->id]);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), 'qrcode')) {
                return false;
            }

            return $request->data()['amount']['unit'] === 'XAF';
        });
    }

    public function test_platform_config_currency_defaults_to_the_countrys_currency_when_not_given(): void
    {
        [, $country, $payment] = $this->makeShopCountryAndPayment('XOF');

        $result = (new PlatformPaymentConfigService())->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true, 'merchant_key' => 'secret',
        ]);

        $this->assertTrue($result['status']);
        $this->assertSame('XOF', PlatformPaymentConfig::where('country_id', $country->id)->value('currency'));
    }

    public function test_platform_config_currency_override_is_respected(): void
    {
        [, $country, $payment] = $this->makeShopCountryAndPayment('XOF');

        $result = (new PlatformPaymentConfigService())->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true,
            'merchant_key' => 'secret', 'currency' => 'XAF',
        ]);

        $this->assertTrue($result['status']);
        $this->assertSame('XAF', PlatformPaymentConfig::where('country_id', $country->id)->value('currency'));
    }

    public function test_cannot_configure_platform_gateway_for_a_country_where_it_is_unavailable(): void
    {
        [, $country, $payment] = $this->makeShopCountryAndPayment('XOF', gatewayAvailable: false);

        $result = (new PlatformPaymentConfigService())->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true, 'merchant_key' => 'secret',
        ]);

        $this->assertFalse($result['status']);
        $this->assertNull(PlatformPaymentConfig::where('country_id', $country->id)->first());
    }

    public function test_credentials_are_encrypted_at_rest(): void
    {
        [, $country, $payment] = $this->makeShopCountryAndPayment('XOF');

        (new PlatformPaymentConfigService())->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true, 'merchant_key' => 'super-secret',
        ]);

        $raw = DB::table('platform_payment_configs')->where('country_id', $country->id)->value('merchant_key');
        $this->assertNotSame('super-secret', $raw);

        $decrypted = PlatformPaymentConfig::where('country_id', $country->id)->first();
        $this->assertSame('super-secret', $decrypted->merchant_key);
    }

    public function test_a_country_admin_cannot_manage_platform_payment_configs(): void
    {
        [, $country] = $this->makeShopCountryAndPayment('XOF');

        $admin = User::factory()->create();
        $admin->assignRole('manager');
        CountryAdmin::query()->create(['user_id' => $admin->id, 'country_id' => $country->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/dashboard/admin/platform-payment-configs/paginate');

        $response->assertStatus(403);
    }

    public function test_a_superadmin_can_manage_platform_payment_configs(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('manager');

        $response = $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/dashboard/admin/platform-payment-configs/paginate');

        $response->assertStatus(200);
    }
}
