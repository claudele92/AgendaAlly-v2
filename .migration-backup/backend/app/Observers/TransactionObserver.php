<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Booking;
use App\Models\Order;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Transaction;

/**
 * Records both directions of the fee ledger the moment a payment actually
 * completes, regardless of which gateway carried it — see
 * PlatformFeeLedgerEntry. Covers both Bookings and product Orders, which
 * share the same Payable trait and (for the fee side) the same relevant
 * columns (shop_id, currency_id, service_fee) - only the payable-on-
 * behalf-of-shop side is Booking-specific, since collect_via_platform
 * doesn't exist on Order.
 *
 * Most "paid" code paths (gateway callback in BaseService::afterHook,
 * the synchronous wallet debit in TransactionService::walletHistoryAdd)
 * work by calling Transaction::update(['status' => ...]) on a row that
 * already existed in some other status - a genuine updated() transition.
 * One path doesn't: CartOrderService::createTransactionByOrder(), used
 * for a multi-shop cart checkout, resolves the whole cart's payment
 * status BEFORE creating each order's own Transaction row, so that row
 * is inserted already 'paid' and never fires updated() at all - hence
 * the separate created() hook below, rather than just widening the
 * updated() class check.
 */
class TransactionObserver
{
    private const FEE_PAYABLE_TYPES = [Booking::class, Order::class];

    public function created(Transaction $transaction): void
    {
        if (!$this->isPaidFeeEligibleTransaction($transaction)) {
            return;
        }

        $payable = $transaction->payable;
        $this->recordFee($transaction, $payable);

        if ($payable instanceof Booking) {
            $this->recordPayableIfCollectingOnBehalfOfShop($transaction, $payable);
        }
    }

    public function updated(Transaction $transaction): void
    {
        if (!$this->isNewlyPaidFeeEligibleTransaction($transaction)) {
            return;
        }

        $payable = $transaction->payable;

        $this->recordFee($transaction, $payable);

        if ($payable instanceof Booking) {
            $this->recordPayableIfCollectingOnBehalfOfShop($transaction, $payable);
        }
    }

    private function isPaidFeeEligibleTransaction(Transaction $transaction): bool
    {
        // New linked/native-bound payments use the economic writer exclusively.
        // Never reconstruct legacy allocations from mutable commerce here.
        if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
            $type = match ($transaction->payable_type) {
                Booking::class => 'booking', Order::class => 'order', default => null,
            };
            if ($type && \Illuminate\Support\Facades\DB::table('commerce_payment_allocations')
                ->where('payable_type', $type)->where('payable_id', $transaction->payable_id)->exists()) {
                if ($transaction->status === Transaction::STATUS_PAID
                    && $transaction->paymentSystem?->tag === 'cash') {
                    (new \App\Services\PaymentAccounting\NativePaymentAccounting)->cash($transaction->payable, $transaction);
                }
                return false;
            }
            if ($transaction->allocation_id !== null) return false;
        }
        if ($transaction->status !== Transaction::STATUS_PAID) {
            return false;
        }

        if (!in_array($transaction->payable_type, self::FEE_PAYABLE_TYPES, true)) {
            return false;
        }

        return (bool) $transaction->payable;
    }

    private function isNewlyPaidFeeEligibleTransaction(Transaction $transaction): bool
    {
        // Only the progress -> paid transition is a real payment event;
        // any other update to an already-paid transaction (e.g. a later
        // refund flips it away and back, or an unrelated field changes)
        // must not create a second ledger entry for the same money. A
        // transaction that was already 'paid' at creation (see created()
        // above) has getOriginal('status') === 'paid' from the moment it
        // exists, so this correctly never re-fires for it here.
        if ($transaction->getOriginal('status') === Transaction::STATUS_PAID) {
            return false;
        }

        return $this->isPaidFeeEligibleTransaction($transaction);
    }

    private function recordFee(Transaction $transaction, Booking|Order $payable): void
    {
        if ((float) $payable->service_fee <= 0) {
            return;
        }

        PlatformFeeLedgerEntry::query()->firstOrCreate(
            ['transaction_id' => $transaction->id, 'entry_type' => PlatformFeeLedgerEntry::ENTRY_TYPE_FEE],
            [
                'payable_type' => get_class($payable),
                'payable_id'   => $payable->id,
                'shop_id'      => $payable->shop_id,
                'payment_id'   => $transaction->payment_sys_id,
                'currency_id'  => $payable->currency_id,
                'amount'       => $payable->service_fee,
                'status'       => PlatformFeeLedgerEntry::STATUS_PENDING,
            ]
        );
    }

    /**
     * When a shop had opted into collect_via_platform, checkout already
     * routed this payment through the platform's own gateway (see
     * BaseService::resolveGatewayConfig()) rather than the shop's own
     * credentials - so the platform, not the shop, actually holds the
     * seller's share of this payment right now. Record that as a payable
     * ledger row, read once here at settlement and never recomputed if the
     * shop's toggle or fee settings change afterward (firstOrCreate never
     * updates an existing row's amount).
     *
     * Reads booking->collect_via_platform - frozen onto the booking at
     * creation time in BookingService::beforeSave() - rather than the
     * shop's current, possibly-since-changed setting. Checkout resolved
     * the gateway from that same frozen intent; if this read instead
     * followed the shop's live setting, a toggle flipped between checkout
     * and settlement could make the two disagree about whether the
     * platform actually holds the money.
     */
    private function recordPayableIfCollectingOnBehalfOfShop(Transaction $transaction, Booking $booking): void
    {
        if (!$booking->collect_via_platform) {
            return;
        }

        $sellerFee = (float) $booking->seller_fee;

        if ($sellerFee <= 0) {
            return;
        }

        PlatformFeeLedgerEntry::query()->firstOrCreate(
            ['transaction_id' => $transaction->id, 'entry_type' => PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE],
            [
                'payable_type' => Booking::class,
                'payable_id'   => $booking->id,
                'shop_id'      => $booking->shop_id,
                'payment_id'   => $transaction->payment_sys_id,
                'currency_id'  => $booking->currency_id,
                'amount'       => $sellerFee,
                'status'       => PlatformFeeLedgerEntry::STATUS_PENDING,
            ]
        );
    }
}
