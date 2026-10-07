<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Booking;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Models\User;
use App\Services\PaymentService\MtnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * MtnService now calls MTN's own Collections API directly (not the old
 * gutouch.net/touchpayapi aggregator): the real booking amount, the
 * shop's configured currency, and X-Target-Environment must all be
 * present and correct on the outbound requesttopay call.
 */
class MtnServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeShopInCountry(string $currencyTitle, string $targetEnvironment): Shop
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

        $payment = Payment::query()->create(['tag' => Payment::TAG_MTN, 'active' => true, 'input' => 15]);
        $country->payments()->attach($payment->id, ['active' => true]);

        ShopPayment::query()->create([
            'shop_id'             => $shop->id,
            'payment_id'          => $payment->id,
            'status'              => true,
            'subscription_key'    => 'test-subscription-key',
            'api_user'            => 'test-api-user',
            'api_key'             => 'test-api-key',
            'target_environment'  => $targetEnvironment,
            'currency'            => $currencyTitle,
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

    public function test_requesttopay_carries_the_real_amount_configured_currency_and_target_environment(): void
    {
        $shop    = $this->makeShopInCountry('GHS', 'mtnghana');
        $booking = $this->makeBooking($shop, 500.0);

        $this->actingAs($booking->user, 'sanctum');

        Http::fake([
            '*/collection/token/'         => Http::response(['access_token' => 'fake-token'], 200),
            '*/collection/v1_0/requesttopay' => Http::response('', 202),
        ]);

        (new MtnService())->processTransaction(['booking_id' => $booking->id, 'phone' => '233241234567']);

        Http::assertSent(function ($request) {
            if (!str_contains($request->url(), 'requesttopay')) {
                return false;
            }

            return $request->data()['amount'] === '500.00'
                && $request->data()['amount'] !== '101'
                && $request->data()['currency'] === 'GHS'
                && $request->header('X-Target-Environment')[0] === 'mtnghana'
                && $request->header('Ocp-Apim-Subscription-Key')[0] === 'test-subscription-key';
        });
    }

    public function test_it_stores_a_payment_process_row_keyed_by_the_mtn_reference_id(): void
    {
        $shop    = $this->makeShopInCountry('XAF', 'mtncameroon');
        $booking = $this->makeBooking($shop, 250.0);

        $this->actingAs($booking->user, 'sanctum');

        Http::fake([
            '*/collection/token/'            => Http::response(['access_token' => 'fake-token'], 200),
            '*/collection/v1_0/requesttopay' => Http::response('', 202),
        ]);

        $process = (new MtnService())->processTransaction(['booking_id' => $booking->id, 'phone' => '237600000000']);

        // mtn_reference_id is MTN's own externalId/X-Reference-Id — a UUID
        // generated per request, unrelated to the PaymentProcess row's own
        // id. What "keyed by" means here: it's the value MTN's webhook will
        // report back, so it must be the exact id sent on the outbound call.
        $this->assertInstanceOf(PaymentProcess::class, $process);
        $this->assertFalse($process->data['mtn_resolved']);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($process->data['mtn_reference_id']));

        Http::assertSent(function ($request) use ($process) {
            if (!str_contains($request->url(), 'requesttopay')) {
                return false;
            }

            return $request->header('X-Reference-Id')[0] === $process->data['mtn_reference_id'];
        });
    }

    public function test_it_rejects_a_shop_missing_any_required_mtn_field(): void
    {
        $shop = $this->makeShopInCountry('XAF', 'mtncameroon');

        ShopPayment::query()->where('shop_id', $shop->id)->update(['target_environment' => null]);

        $booking = $this->makeBooking($shop, 250.0);
        $this->actingAs($booking->user, 'sanctum');

        $this->expectExceptionMessage('MTN Mobile Money has not been configured for this transaction yet');

        (new MtnService())->processTransaction(['booking_id' => $booking->id, 'phone' => '237600000000']);
    }
}
