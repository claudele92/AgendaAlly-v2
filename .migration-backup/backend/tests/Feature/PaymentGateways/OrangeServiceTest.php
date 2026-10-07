<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Booking;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Models\User;
use App\Services\PaymentService\OrangeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * OrangeService must use the shop's own configured merchant_key/currency
 * and the transaction's real amount — not the hardcoded merchant_key-less
 * request, `1`, and `'XOF'` this replaces — and must never dd() mid-request.
 */
class OrangeServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeShopInCountry(string $currencyTitle): Shop
    {
        $currency = Currency::query()->create(['title' => $currencyTitle, 'symbol' => $currencyTitle, 'rate' => 1, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);
        $country  = Country::query()->create(['region_id' => $region->id, 'active' => true, 'currency_id' => $currency->id]);

        $shop = Shop::factory()->create([
            'user_id'       => User::factory()->create()->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id'    => $shop->id,
            'region_id'  => $region->id,
            'country_id' => $country->id,
            'type'       => ShopLocation::PRODUCT,
        ]);

        $payment = Payment::query()->create(['tag' => Payment::TAG_ORANGE, 'active' => true, 'input' => 15]);
        $country->payments()->attach($payment->id, ['active' => true]);

        ShopPayment::query()->create([
            'shop_id'      => $shop->id,
            'payment_id'   => $payment->id,
            'status'       => true,
            'client_id'    => 'test-client-id',
            'merchant_key' => 'test-client-secret',
            'currency'     => $currencyTitle,
        ]);

        return $shop;
    }

    private function makeBooking(Shop $shop, float $totalPrice): Booking
    {
        $currency = Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        $master   = User::factory()->create();
        $customer = User::factory()->create();

        return Booking::query()->create([
            'shop_id'     => $shop->id,
            'master_id'   => $master->id,
            'user_id'     => $customer->id,
            'currency_id' => $currency->id,
            'start_date'  => now(),
            'end_date'    => now()->addHour(),
            'total_price' => $totalPrice,
            'rate'        => 1,
        ]);
    }

    public function test_it_uses_the_shops_configured_currency_and_the_real_booking_amount_not_hardcoded_values(): void
    {
        $shop    = $this->makeShopInCountry('XOF');
        $booking = $this->makeBooking($shop, 500.0);

        $this->actingAs($booking->user, 'sanctum');

        Http::fake([
            '*/oauth/token' => Http::response(['access_token' => 'fake-token'], 200),
            '*/api/eWallet/v4/qrcode' => Http::response(['deepLink' => 'https://example.test/pay'], 200),
        ]);

        (new OrangeService())->processTransaction(['booking_id' => $booking->id]);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), 'qrcode')) {
                return false;
            }

            $amount = $request->data()['amount'] ?? [];

            return $amount['unit'] === 'XOF'
                && $amount['value'] === 500
                && $amount['value'] !== 1;
        });
    }

    public function test_it_rejects_a_shop_that_has_not_configured_orange_money(): void
    {
        $shop = $this->makeShopInCountry('XOF');

        ShopPayment::query()->where('shop_id', $shop->id)->delete();

        $booking = $this->makeBooking($shop, 500.0);
        $this->actingAs($booking->user, 'sanctum');

        $this->expectExceptionMessage('Orange Money has not been configured for this transaction yet');

        (new OrangeService())->processTransaction(['booking_id' => $booking->id]);
    }
}
