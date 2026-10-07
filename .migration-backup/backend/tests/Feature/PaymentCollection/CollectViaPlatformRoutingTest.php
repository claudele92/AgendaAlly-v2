<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentCollection;

use App\Models\Booking;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PlatformPaymentConfig;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Models\User;
use App\Services\PaymentService\BaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the collect_via_platform toggle's one job: which
 * gateway config a shop's Orange/MTN checkout resolves to. Default (false)
 * must keep today's behavior byte-for-byte - the shop's own ShopPayment.
 * Only when a shop explicitly opts in does it route to the platform's own
 * PlatformPaymentConfig instead, exactly like PayPal already does.
 */
class CollectViaPlatformRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function makeShopWithCountry(bool $collectViaPlatform): Shop
    {
        $seller = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id' => $seller->id,
            'collect_via_platform' => $collectViaPlatform,
        ]);

        $region = Region::query()->create(['active' => true]);
        $currency = Currency::factory()->create();
        $country = Country::query()->create(['active' => true, 'code' => 'SV', 'currency_id' => $currency->id]);

        ShopLocation::query()->create([
            'shop_id' => $shop->id,
            'region_id' => $region->id,
            'country_id' => $country->id,
            'type' => ShopLocation::SERVICE,
        ]);

        return $shop;
    }

    private function makeBooking(Shop $shop): Booking
    {
        $master = User::factory()->create();
        $customer = User::factory()->create();
        $currency = Currency::factory()->create();

        return Booking::query()->create([
            'shop_id' => $shop->id,
            'master_id' => $master->id,
            'user_id' => $customer->id,
            'currency_id' => $currency->id,
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => Booking::STATUS_NEW,
        ]);
    }

    public function test_default_off_routes_orange_mtn_through_the_shops_own_gateway(): void
    {
        $shop = $this->makeShopWithCountry(false);
        $booking = $this->makeBooking($shop);

        $payment = Payment::query()->create(['tag' => Payment::TAG_MTN, 'active' => true]);

        ShopPayment::query()->create([
            'shop_id' => $shop->id,
            'payment_id' => $payment->id,
            'api_user' => 'shop-own-user',
        ]);

        PlatformPaymentConfig::query()->create([
            'country_id' => $shop->checkoutCountry(ShopLocation::SERVICE)->id,
            'payment_id' => $payment->id,
            'api_user' => 'platform-user',
        ]);

        $config = (new BaseService)->resolveGatewayConfig(
            ['model_type' => Booking::class, 'model_id' => $booking->id],
            $payment->id
        );

        $this->assertInstanceOf(ShopPayment::class, $config);
        $this->assertSame('shop-own-user', $config->getApiUser());
    }

    public function test_opted_in_routes_orange_mtn_through_the_platforms_own_gateway(): void
    {
        $shop = $this->makeShopWithCountry(true);
        $booking = $this->makeBooking($shop);

        $payment = Payment::query()->create(['tag' => Payment::TAG_MTN, 'active' => true]);

        ShopPayment::query()->create([
            'shop_id' => $shop->id,
            'payment_id' => $payment->id,
            'api_user' => 'shop-own-user',
        ]);

        PlatformPaymentConfig::query()->create([
            'country_id' => $shop->checkoutCountry(ShopLocation::SERVICE)->id,
            'payment_id' => $payment->id,
            'api_user' => 'platform-user',
        ]);

        $config = (new BaseService)->resolveGatewayConfig(
            ['model_type' => Booking::class, 'model_id' => $booking->id],
            $payment->id
        );

        $this->assertInstanceOf(PlatformPaymentConfig::class, $config);
        $this->assertSame('platform-user', $config->getApiUser());
    }
}
