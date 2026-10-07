<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService\StripeService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stripe's Checkout Session unit_amount must be the amount in the
 * currency's OWN unit for a zero-decimal currency (XAF, XOF, JPY, ...) -
 * every before*() helper in BaseService unconditionally multiplies by
 * 100, assuming a 2-decimal currency, which would silently overcharge
 * 100x if ever reached for one of these. See StripeService::
 * ZERO_DECIMAL_CURRENCIES / supportsCurrency() - this proves the guard
 * actually stops that, rather than just tracing that it should.
 */
class StripeZeroDecimalCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_decimal_currencies_are_rejected(): void
    {
        foreach (['XAF', 'XOF', 'JPY', 'xaf', 'Xof'] as $currency) {
            $this->assertFalse(
                StripeService::supportsCurrency($currency),
                "$currency should not be supported by Stripe today"
            );
        }
    }

    public function test_ordinary_two_decimal_currencies_are_supported(): void
    {
        foreach (['USD', 'EUR', 'GBP', null] as $currency) {
            $this->assertTrue(
                StripeService::supportsCurrency($currency),
                var_export($currency, true) . ' should be supported'
            );
        }
    }

    private function makeXafBooking(): Booking
    {
        $shop     = Shop::factory()->create(['user_id' => User::factory()->create()->id]);
        $master   = User::factory()->create();
        $customer = User::factory()->create();
        $currency = Currency::factory()->create(['title' => 'XAF']);

        return Booking::create([
            'service_master_id' => null,
            'master_id'         => $master->id,
            'user_id'           => $customer->id,
            'shop_id'           => $shop->id,
            'currency_id'       => $currency->id,
            'start_date'        => now()->addDay(),
            'end_date'          => now()->addDay()->addHour(),
            'price'             => 5000,
            'total_price'       => 5000,
            'service_fee'       => 0,
            'status'            => Booking::STATUS_NEW,
        ]);
    }

    private function configureStripe(): void
    {
        $payment = Payment::query()->create(['tag' => Payment::TAG_STRIPE, 'active' => true, 'input' => 1]);

        PaymentPayload::create([
            'payment_id' => $payment->id,
            'payload'    => ['stripe_sk' => 'sk_test_fake_key_for_tests'],
        ]);
    }

    public function test_stripe_checkout_rejects_a_xaf_booking_before_any_charge_attempt(): void
    {
        $this->configureStripe();
        $booking = $this->makeXafBooking();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Stripe payment is not supported for XAF currency. Please select a supported local payment method.'
        );

        try {
            (new StripeService)->processTransaction(['booking_id' => $booking->id]);
        } finally {
            // The whole point of the guard: no Transaction/PaymentProcess
            // row exists, because we never reached Session::create() -
            // there is nothing here that could have overcharged anyone.
            $this->assertSame(0, Transaction::where('payable_id', $booking->id)->count());
            $this->assertSame(0, PaymentProcess::query()->count());
        }
    }
}
