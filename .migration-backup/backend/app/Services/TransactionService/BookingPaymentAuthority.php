<?php
declare(strict_types=1);

namespace App\Services\TransactionService;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Transaction;

/** Selection/manual commerce actions are not authoritative provider verification. */
final class BookingPaymentAuthority
{
    public static function offline(?Payment $payment): bool
    {
        // These are the only distinct offline/native methods declared by Payment.
        return in_array($payment?->tag, [Payment::TAG_CASH, Payment::TAG_WALLET], true);
    }

    public static function allowsManualStatus(Transaction $transaction, ?string $status): bool
    {
        return $transaction->payable_type !== Booking::class
            || $status !== Transaction::STATUS_PAID
            || self::offline($transaction->paymentSystem);
    }
}