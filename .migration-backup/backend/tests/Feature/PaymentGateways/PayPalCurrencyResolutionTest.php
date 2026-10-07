<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

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
use App\Services\PaymentService\PayPalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

/**
 * PayPal does not support every currency the platform might be running
 * under (XAF/XOF among others), and previously blindly forwarded whichever
 * currency the platform base-currency flag or the booking's own
 * country-derived currency_id happened to produce (see BaseService::
 * beforeSubscription()/beforeBooking()). This exercises the fix: PayPal
 * resolves its own settlement currency via the same per-country
 * PlatformPaymentConfig override MTN/Orange already use, falling back to
 * USD, and converts the charged amount into that currency — without ever
 * touching ShopPayment (PayPal has no per-shop merchant account), and
 * without changing Orange/MTN's own resolution at all.
 */
class PayPalCurrencyResolutionTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Shop, 1: Country, 2: Booking} */
    private function makeShopCountryAndBooking(string $countryCurrency = 'XAF'): array
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        $currency = Currency::query()->create(['title' => $countryCurrency, 'symbol' => $countryCurrency, 'rate' => 600, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);
        $country  = Country::query()->create(['region_id' => $region->id, 'active' => true, 'currency_id' => $currency->id]);

        $seller = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id' => $shop->id, 'region_id' => $region->id, 'country_id' => $country->id, 'type' => ShopLocation::SERVICE,
        ]);

        $master   = User::factory()->create();
        $customer = User::factory()->create();

        $booking = Booking::create([
            'service_master_id' => null,
            'master_id'         => $master->id,
            'user_id'           => $customer->id,
            'shop_id'           => $shop->id,
            'currency_id'       => $currency->id,
            'start_date'        => now()->addDay(),
            'end_date'          => now()->addDay()->addHour(),
            'price'             => 10000,
            'total_price'       => 11200,
            'service_fee'       => 1200,
            'status'            => Booking::STATUS_NEW,
        ]);

        return [$shop, $country, $booking];
    }

    public function test_paypal_falls_back_to_the_platform_country_config_when_no_shop_payment_exists(): void
    {
        [, $country, $booking] = $this->makeShopCountryAndBooking();
        $payment = Payment::query()->create(['tag' => Payment::TAG_PAY_PAL, 'active' => true, 'input' => 10]);

        PlatformPaymentConfig::query()->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true, 'currency' => 'EUR',
        ]);

        $before = ['model_type' => Booking::class, 'model_id' => $booking->id];

        $config = (new BaseService())->resolveGatewayConfig($before, $payment->id);

        $this->assertInstanceOf(PlatformPaymentConfig::class, $config);
        $this->assertSame('EUR', $config->getCurrency());
    }

    public function test_paypal_resolves_to_null_when_nothing_is_configured_for_the_country(): void
    {
        [, , $booking] = $this->makeShopCountryAndBooking();
        $payment = Payment::query()->create(['tag' => Payment::TAG_PAY_PAL, 'active' => true, 'input' => 10]);

        $before = ['model_type' => Booking::class, 'model_id' => $booking->id];

        $this->assertNull((new BaseService())->resolveGatewayConfig($before, $payment->id));
    }

    public function test_mtn_and_orange_never_fall_back_to_the_platform_config_for_a_booking(): void
    {
        [$shop, $country, $booking] = $this->makeShopCountryAndBooking();
        $mtn = Payment::query()->create(['tag' => Payment::TAG_MTN, 'active' => true, 'input' => 16]);

        // A platform-level config exists for this country+MTN (e.g. used
        // for platform-fee purchases) - a booking must still ignore it
        // entirely when the shop has no ShopPayment of its own.
        PlatformPaymentConfig::query()->create([
            'country_id' => $country->id, 'payment_id' => $mtn->id, 'status' => true, 'currency' => 'XAF',
        ]);

        $before = ['model_type' => Booking::class, 'model_id' => $booking->id];

        $this->assertNull((new BaseService())->resolveGatewayConfig($before, $mtn->id));

        // Once the shop configures its own ShopPayment, that (and only
        // that) is what resolves - unchanged from before this fix.
        ShopPayment::query()->create([
            'shop_id' => $shop->id, 'payment_id' => $mtn->id, 'status' => true, 'currency' => 'XAF',
        ]);

        $config = (new BaseService())->resolveGatewayConfig($before, $mtn->id);
        $this->assertInstanceOf(ShopPayment::class, $config);
    }

    public function test_settlement_amount_defaults_to_usd_and_converts_correctly_when_unconfigured(): void
    {
        [, , $booking] = $this->makeShopCountryAndBooking('XAF');
        $payment = Payment::query()->create(['tag' => Payment::TAG_PAY_PAL, 'active' => true, 'input' => 10]);

        $before = [
            'model_type'  => Booking::class,
            'model_id'    => $booking->id,
            'currency'    => 'XAF',
            'total_price' => 1120000, // 11200.00 XAF, in cents
        ];

        [$currencyCode, $amount] = $this->invokeResolveSettlementAmount($before, $payment->id);

        $this->assertSame('USD', $currencyCode);
        // 600 XAF per 1 USD (fixture rate) -> 11200 XAF == 18.666... USD
        $this->assertEqualsWithDelta(1866.666, $amount, 0.01);
    }

    public function test_settlement_amount_uses_the_configured_override_currency(): void
    {
        [, $country, $booking] = $this->makeShopCountryAndBooking('XAF');
        $payment = Payment::query()->create(['tag' => Payment::TAG_PAY_PAL, 'active' => true, 'input' => 10]);
        Currency::query()->create(['title' => 'EUR', 'symbol' => '€', 'rate' => 655, 'default' => 0, 'active' => 1]);

        PlatformPaymentConfig::query()->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true, 'currency' => 'EUR',
        ]);

        $before = [
            'model_type'  => Booking::class,
            'model_id'    => $booking->id,
            'currency'    => 'XAF',
            'total_price' => 1120000,
        ];

        [$currencyCode, $amount] = $this->invokeResolveSettlementAmount($before, $payment->id);

        $this->assertSame('EUR', $currencyCode);
        // 655 XAF per 1 EUR (real-world CFA franc peg) -> 11200 XAF == ~17.10 EUR
        $this->assertEqualsWithDelta(1709.9, $amount, 1);
    }

    public function test_settlement_amount_is_unchanged_when_already_in_the_resolved_currency(): void
    {
        [, , $booking] = $this->makeShopCountryAndBooking('XAF');
        $payment = Payment::query()->create(['tag' => Payment::TAG_PAY_PAL, 'active' => true, 'input' => 10]);

        $before = [
            'model_type'  => Booking::class,
            'model_id'    => $booking->id,
            'currency'    => 'USD',
            'total_price' => 500000,
        ];

        [$currencyCode, $amount] = $this->invokeResolveSettlementAmount($before, $payment->id);

        $this->assertSame('USD', $currencyCode);
        $this->assertSame(500000.0, $amount);
    }

    private function invokeResolveSettlementAmount(array $before, int $paymentId): array
    {
        $service = new PayPalService();
        $method  = new ReflectionMethod($service, 'resolveSettlementAmount');
        $method->setAccessible(true);

        return $method->invoke($service, $before, $paymentId);
    }
}
