<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Orange Money/MTN Mobile Money settle the whole charge (service price +
 * service_fee) into the shop's own merchant account, and PayPal is not
 * yet wired for per-shop splits either — so the platform's service_fee
 * has to be tracked separately the moment a booking is paid, for every
 * gateway, or it is silently never collected. See TransactionObserver.
 */
class PlatformFeeLedgerTest extends TestCase
{
    use RefreshDatabase;

    private function makePayment(string $tag): Payment
    {
        return Payment::query()->create(['tag' => $tag, 'active' => true, 'input' => 1]);
    }

    private function makeBooking(float $serviceFee, ?Shop $shop = null): Booking
    {
        $shop     = $shop ?? Shop::factory()->create(['user_id' => User::factory()->create()->id]);
        $master   = User::factory()->create();
        $customer = User::factory()->create();
        $currency = Currency::factory()->create();

        return Booking::create([
            'service_master_id' => null,
            'master_id'         => $master->id,
            'user_id'           => $customer->id,
            'shop_id'           => $shop->id,
            'currency_id'       => $currency->id,
            'start_date'        => now()->addDay(),
            'end_date'          => now()->addDay()->addHour(),
            'price'             => 10000,
            'total_price'       => 10000 + $serviceFee,
            'service_fee'       => $serviceFee,
            'status'            => Booking::STATUS_NEW,
        ]);
    }

    private function payTransaction(Booking $booking, Payment $payment): Transaction
    {
        $transaction = $booking->createTransaction([
            'price'              => $booking->total_price,
            'user_id'            => $booking->user_id,
            'payment_sys_id'     => $payment->id,
            'payment_trx_id'     => null,
            'note'               => (string) $booking->id,
            'perform_time'       => now(),
            'status_description' => "Transaction for booking #{$booking->id}",
            'request'            => null,
        ]);

        $this->assertSame(Transaction::STATUS_PROGRESS, $transaction->status);

        $transaction->update(['status' => Transaction::STATUS_PAID]);

        return $transaction->fresh();
    }

    public function test_paying_a_booking_records_a_pending_fee_ledger_entry(): void
    {
        $shop        = Shop::factory()->create(['user_id' => User::factory()->create()->id]);
        $booking     = $this->makeBooking(1200, $shop);
        $transaction = $this->payTransaction($booking, $this->makePayment(Payment::TAG_WALLET));

        $entry = PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->first();

        $this->assertNotNull($entry);
        $this->assertSame(Booking::class, $entry->payable_type);
        $this->assertSame($booking->id, $entry->payable_id);
        $this->assertSame($shop->id, $entry->shop_id);
        $this->assertSame($booking->currency_id, $entry->currency_id);
        $this->assertSame(1200.0, $entry->amount);
        $this->assertSame(PlatformFeeLedgerEntry::STATUS_PENDING, $entry->status);
    }

    public function test_a_booking_with_no_service_fee_gets_no_ledger_entry(): void
    {
        $booking     = $this->makeBooking(0);
        $transaction = $this->payTransaction($booking, $this->makePayment(Payment::TAG_WALLET));

        $this->assertSame(
            0,
            PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->count()
        );
    }

    public function test_re_saving_an_already_paid_transaction_does_not_duplicate_the_entry(): void
    {
        $booking     = $this->makeBooking(1200);
        $transaction = $this->payTransaction($booking, $this->makePayment(Payment::TAG_WALLET));

        $transaction->touch();
        $transaction->update(['status' => Transaction::STATUS_PAID]);

        $this->assertSame(
            1,
            PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->count()
        );
    }

    public function test_ledger_entry_records_the_gateway_that_collected_the_fee(): void
    {
        $orange      = $this->makePayment(Payment::TAG_ORANGE);
        $booking     = $this->makeBooking(1200);
        $transaction = $this->payTransaction($booking, $orange);

        $entry = PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->first();

        $this->assertSame($orange->id, $entry->payment_id);
    }
}
