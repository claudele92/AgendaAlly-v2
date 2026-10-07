<?php
declare(strict_types=1);

namespace App\Services\BookingService;

use App\Models\Booking;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\WalletHistory;
use App\Rules\PositiveWalletAmount;
use App\Services\WalletHistoryService\WalletHistoryService;
use RuntimeException;

/** Called only inside the Booking lifecycle's transaction and Booking lock. */
final class BookingCancellationSettlement
{
    public function assertCanTransition(Booking $booking): void
    {
        // The payment marker survives a stale/reopened lifecycle value.
        if ($booking->status === Booking::STATUS_CANCELED
            || $booking->transactions()->whereNotNull('refund_time')->exists()) {
            throw new RuntimeException('Canceled or financially settled Booking is terminal');
        }
    }

    public function settle(Booking $booking): void
    {
        if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
            $allocation = \Illuminate\Support\Facades\DB::table('commerce_payment_allocations')
                ->where('payable_type','booking')->where('payable_id',$booking->id)
                ->lockForUpdate()->first();
            if ($allocation) {
                // Use the existing canonical unfunded-cancellation authority.
                // It refuses held principal, finalization or unresolved receipts;
                // this is not a refund path and cannot bypass funded custody.
                (new \App\Services\PaymentAccounting\AllocationWriter)
                    ->cancel((int) $allocation->id);
            }
        }
        // SQLite ignores FOR UPDATE: a conditional write claims cancellation
        // before any money movement; all claims and effects roll back together.
        if (Booking::query()->whereKey($booking->id)->where('status', $booking->status)
            ->where('status', '!=', Booking::STATUS_CANCELED)
            ->update(['status' => Booking::STATUS_CANCELED]) !== 1) {
            throw new RuntimeException('Booking cancellation was not claimed');
        }
        $payment = $booking->transaction()->lockForUpdate()->first();
        $tag = $payment?->paymentSystem?->tag;
        if ($payment && Transaction::query()->whereKey($payment->id)->whereNull('refund_time')
            ->update(['refund_time' => now()]) !== 1) {
            throw new RuntimeException('Booking payment refund was already claimed');
        }
        if (!$booking->user || !$payment || $tag === 'cash') {
            return; // No prepaid principal to credit; native Cash is offline.
        }
        if ($payment->status !== Transaction::STATUS_PAID) {
            throw new RuntimeException('A successful Booking payment is required');
        }
        $hour = Settings::where('key', 'booking_refund_canceled_hour')->value('value');
        $hour = $hour === null ? 24 : $this->number($hour, 'cancellation window');
        if ($hour < 0) throw new RuntimeException('Invalid cancellation window');
        $inWindow = strtotime((string) $booking->start_date) > time() - $hour * 3600;
        $percentage = $inWindow
            ? $this->number(Settings::where('key', 'booking_canceled_commission')->value('value'), 'cancellation percentage')
            : 100.0;
        if ($percentage < 0 || $percentage > 100) {
            throw new RuntimeException('Cancellation percentage must be between 0 and 100');
        }
        $total = $this->number($booking->total_price, 'Booking total');
        if ($total <= 0) throw new RuntimeException('Booking total must be positive');
        if ($tag === 'wallet') {
            $refund = $total * (1 - $percentage / 100);
            if ($refund > 0) {
                // Never refund more than the original independently paid amount.
                if (!PositiveWalletAmount::accepts($payment->price)) {
                    throw new RuntimeException('Positive finite funded payment is required');
                }
                $refund = min($total, (float) $payment->price) * (1 - $percentage / 100);
                if (!$booking->user->wallet
                    || (int) $booking->user->wallet->currency_id !== (int) $booking->currency_id) {
                    throw new RuntimeException('Customer Wallet charge currency mismatch');
                }
                $this->wallet($booking, 'topup', $refund);
            }
        } else {
            // Preserve the native electronic cancellation-fee path, once only.
            // No provider refund is invented by this internal containment.
            $fee = $total * $percentage / 100;
            if ($fee > 0) {
                if (!PositiveWalletAmount::accepts($fee) || !$booking->user->wallet
                    || (int) $booking->user->wallet->currency_id !== (int) $booking->currency_id) {
                    throw new RuntimeException('Invalid electronic cancellation fee Wallet');
                }
                \App\Services\WalletHistoryService\WalletDebit::debit(
                    $booking->user->wallet, $fee, (int) $booking->user_id
                );
            }
        }
    }

    private function wallet(Booking $booking, string $type, float $amount): void
    {
        if (!PositiveWalletAmount::accepts($amount) || !$booking->user?->wallet
            || (int) $booking->user->wallet->currency_id !== (int) $booking->currency_id) {
            throw new RuntimeException('Invalid cancellation Wallet operation');
        }
        $result = app(WalletHistoryService::class)->create([
            'user' => $booking->user, 'type' => $type, 'price' => $amount,
            'status' => WalletHistory::PAID,
            'note' => "Cancellation for Booking #{$booking->id}; payment #{$booking->transaction->id}",
        ]);
        if (!data_get($result, 'status')) throw new RuntimeException('Booking cancellation Wallet operation failed');
    }

    private function number(mixed $value, string $label): float
    {
        if (!is_numeric($value) || !is_finite((float) $value)) {
            throw new RuntimeException("Missing or malformed $label");
        }
        return (float) $value;
    }
}