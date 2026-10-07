<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Currency;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mirrors PlatformFeeLedgerTest's Booking coverage for product Orders — see
 * TransactionObserver. Two paths matter here: a transaction that transitions
 * progress -> paid via update() (single/direct order checkout, the wallet
 * debit), and one created already 'paid' in a single insert (a multi-shop
 * cart checkout's CartOrderService::createTransactionByOrder()), which only
 * the observer's created() hook catches.
 */
class OrderPlatformFeeLedgerTest extends TestCase
{
    use RefreshDatabase;

    private function makePayment(string $tag): Payment
    {
        return Payment::query()->create(['tag' => $tag, 'active' => true, 'input' => 1]);
    }

    private function makeOrder(float $serviceFee, ?Shop $shop = null): Order
    {
        $shop     = $shop ?? Shop::factory()->create(['user_id' => User::factory()->create()->id]);
        $customer = User::factory()->create();
        $currency = Currency::factory()->create();

        return Order::factory()->create([
            'user_id'     => $customer->id,
            'shop_id'     => $shop->id,
            'currency_id' => $currency->id,
            'total_price' => 10000 + $serviceFee,
            'service_fee' => $serviceFee,
            'status'      => Order::STATUS_NEW,
        ]);
    }

    public function test_paying_an_order_via_a_status_transition_records_a_pending_fee_ledger_entry(): void
    {
        $shop    = Shop::factory()->create(['user_id' => User::factory()->create()->id]);
        $order   = $this->makeOrder(800, $shop);
        $payment = $this->makePayment(Payment::TAG_WALLET);

        $transaction = $order->createTransaction([
            'price'              => $order->total_price,
            'user_id'            => $order->user_id,
            'payment_sys_id'     => $payment->id,
            'payment_trx_id'     => null,
            'note'               => (string) $order->id,
            'perform_time'       => now(),
            'status_description' => "Transaction for order #{$order->id}",
            'request'            => null,
        ]);

        $this->assertSame(Transaction::STATUS_PROGRESS, $transaction->status);

        $transaction->update(['status' => Transaction::STATUS_PAID]);
        $transaction = $transaction->fresh();

        $entry = PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->first();

        $this->assertNotNull($entry);
        $this->assertSame(Order::class, $entry->payable_type);
        $this->assertSame($order->id, $entry->payable_id);
        $this->assertSame($shop->id, $entry->shop_id);
        $this->assertSame($order->currency_id, $entry->currency_id);
        $this->assertSame(800.0, $entry->amount);
        $this->assertSame(PlatformFeeLedgerEntry::STATUS_PENDING, $entry->status);
    }

    public function test_an_order_transaction_created_already_paid_still_records_a_fee_entry(): void
    {
        // Reproduces CartOrderService::createTransactionByOrder(): the cart's
        // payment already resolved to 'paid' before this order's own
        // Transaction row is ever inserted, so it's born paid rather than
        // transitioning through updated().
        $shop    = Shop::factory()->create(['user_id' => User::factory()->create()->id]);
        $order   = $this->makeOrder(500, $shop);
        $payment = $this->makePayment(Payment::TAG_WALLET);

        $transaction = $order->createTransaction([
            'price'              => $order->total_price,
            'user_id'            => $order->user_id,
            'payment_sys_id'     => $payment->id,
            'payment_trx_id'     => null,
            'note'               => (string) $order->id,
            'perform_time'       => now(),
            'status'             => Transaction::STATUS_PAID,
            'status_description' => "Transaction for order #{$order->id}",
            'request'            => null,
        ]);

        $this->assertSame(Transaction::STATUS_PAID, $transaction->status);

        $entry = PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->first();

        $this->assertNotNull($entry);
        $this->assertSame(Order::class, $entry->payable_type);
        $this->assertSame($order->id, $entry->payable_id);
        $this->assertSame(500.0, $entry->amount);
        $this->assertSame(PlatformFeeLedgerEntry::STATUS_PENDING, $entry->status);
    }

    public function test_an_order_with_no_service_fee_gets_no_ledger_entry(): void
    {
        $order   = $this->makeOrder(0);
        $payment = $this->makePayment(Payment::TAG_WALLET);

        $transaction = $order->createTransaction([
            'price'              => $order->total_price,
            'user_id'            => $order->user_id,
            'payment_sys_id'     => $payment->id,
            'payment_trx_id'     => null,
            'note'               => (string) $order->id,
            'perform_time'       => now(),
            'status'             => Transaction::STATUS_PAID,
            'status_description' => "Transaction for order #{$order->id}",
            'request'            => null,
        ]);

        $this->assertSame(
            0,
            PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->count()
        );
    }

    public function test_re_saving_an_order_transaction_created_already_paid_does_not_duplicate_the_entry(): void
    {
        $order   = $this->makeOrder(500);
        $payment = $this->makePayment(Payment::TAG_WALLET);

        $transaction = $order->createTransaction([
            'price'              => $order->total_price,
            'user_id'            => $order->user_id,
            'payment_sys_id'     => $payment->id,
            'payment_trx_id'     => null,
            'note'               => (string) $order->id,
            'perform_time'       => now(),
            'status'             => Transaction::STATUS_PAID,
            'status_description' => "Transaction for order #{$order->id}",
            'request'            => null,
        ]);

        $transaction->touch();
        $transaction->update(['status' => Transaction::STATUS_PAID]);

        $this->assertSame(
            1,
            PlatformFeeLedgerEntry::where('transaction_id', $transaction->id)->count()
        );
    }
}
