<?php
declare(strict_types=1);

namespace App\Services\WalletHistoryService;

use App\Models\Wallet;
use App\Rules\PositiveWalletAmount;
use Illuminate\Support\Facades\DB;

/** Serialize existing native spendable value, without changing its money format. */
final class WalletDebit
{
    public static function lock(Wallet $identity, int $ownerId): Wallet
    {
        if (DB::connection()->transactionLevel() < 1) {
            throw new \LogicException('Wallet authority requires a financial transaction.');
        }
        $current = Wallet::query()->whereKey($identity->id)->lockForUpdate()->first();
        if (!$current || (int) $current->user_id !== $ownerId
            || (string) $current->uuid !== (string) $identity->uuid
            || (int) $current->currency_id !== (int) $identity->currency_id) {
            throw new \DomainException('Original owned Wallet is unavailable.', 109);
        }
        return $current;
    }

    /** @param array<array{0:Wallet,1:int}> $identities */
    public static function lockOrdered(array $identities): void
    {
        usort($identities, fn (array $a, array $b): int => $a[0]->id <=> $b[0]->id);
        foreach ($identities as [$wallet, $ownerId]) self::lock($wallet, $ownerId);
    }

    public static function debit(Wallet $identity, mixed $amount, int $ownerId): Wallet
    {
        if (!PositiveWalletAmount::accepts($amount)) {
            throw new \InvalidArgumentException('Positive native Wallet debit required.');
        }
        $current = self::lock($identity, $ownerId);
        // Native DECIMAL comparison/arithmetic remains in the database. A current
        // lock and conditional write protect even pre-hydrated callers after waits.
        if (Wallet::query()->whereKey($current->id)->where('user_id', $ownerId)
            ->where('price', '>=', $amount)->decrement('price', $amount) !== 1) {
            throw new \DomainException('Insufficient spendable Wallet funds.', 109);
        }
        return $current;
    }
}