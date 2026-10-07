<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Bounded native Cash/Wallet adapter. No guessing for historical payments and
 * no customer-controlled accounting keys. Provider evidence has its own adapter.
 */
final class NativePaymentAccounting
{
    public static function installed(): bool
    {
        return Schema::hasTable('commerce_payment_allocations')
            && Schema::hasTable('payment_collection_contexts')
            && Schema::hasColumn('transactions', 'collection_context_id')
            && Schema::hasColumn('platform_fee_ledger_entries', 'effect_data');
    }

    public function allocation(Booking|Order $model): object
    {
        $type = $model instanceof Booking ? 'booking' : 'order';
        $a = DB::table('commerce_payment_allocations')->where('payable_type', $type)
            ->where('payable_id', $model->id)->where('purpose', 'base')->where('obligation_key', 'base')->first();
        if (!$a) {
            throw new \DomainException('Original accounting context is unverified; legacy classification is not authorized.');
        }
        return $a;
    }

    public function prepare(Booking|Order $model, Payment $payment, string $decimal, string $slot = 'selected_method'): int
    {
        $a = $this->allocation($model);
        $writer = new AllocationWriter;
        $a = $writer->lock((int) $a->id);
        $mode = match ($payment->tag) {
            'cash' => 'offline', 'wallet' => 'internal',
            default => throw new \DomainException('Verified provider contribution required.'),
        };
        $amount = ExactMoney::units($decimal, (int) $a->money_scale);
        if ($mode === 'internal') {
            self::assertWalletCurrency((int) $a->currency_id, $a->payer_user_id === null ? null : (int)$a->payer_user_id);
        }
        $existing = DB::table('payment_collection_contexts')->where('allocation_id', $a->id)
            ->where('funding_key', $slot.':native:'.$payment->id)->first();
        $event = $existing?->funding_event_key ?? (string) Str::uuid();
        $history = $mode === 'internal' ? ($existing?->wallet_history_reference ?? (string) Str::uuid()) : null;
        $wallet = $mode === 'internal' ? DB::table('wallets')->where('user_id', $a->payer_user_id)->first() : null;
        if ($mode === 'internal' && !$wallet) {
            throw new \DomainException('Original payer Wallet is required.');
        }
        return $writer->stage((int) $a->id, [
            'funding_key' => $slot.':native:'.$payment->id, 'funding_slot' => $slot, 'funding_event_key' => $event,
            'collection_mode' => $mode, 'custody_type' => $mode === 'internal' ? 'platform' : 'vendor',
            'expected_collector_type' => $mode === 'internal' ? 'platform' : 'shop',
            'expected_collector_id' => $mode === 'internal' ? null : (int) $a->shop_id,
            'credential_owner_type' => 'none', 'credential_owner_id' => null, 'payment_id' => (int) $payment->id,
            'provider_tag' => null, 'currency_id' => (int) $a->currency_id, 'currency_code' => $a->currency_code,
            'money_scale' => (int) $a->money_scale, 'amount' => $amount, 'receipt_total_amount' => $amount,
            'wallet_id' => $wallet?->id, 'wallet_history_reference' => $history,
        ]);
    }

    public function cash(Booking|Order $model, Transaction $transaction): void
    {
        $payment = Payment::findOrFail($transaction->payment_sys_id);
        if ($payment->tag !== 'cash' || $transaction->status !== Transaction::STATUS_PAID) {
            throw new \DomainException('Native authorized Cash acceptance required.');
        }
        $persistedPrice = DB::table('transactions')->where('id',$transaction->id)->value('price');
        $contextId = $this->prepare($model, $payment, (string) $persistedPrice);
        $context = DB::table('payment_collection_contexts')->find($contextId);
        (new AllocationWriter)->linkTransaction((int) $transaction->id, (int) $context->allocation_id, $contextId);
        (new AllocationWriter)->confirm([$contextId], 'native-cash:'.$context->allocation_id.':'.$context->funding_event_key, (int) $context->amount);
        $transaction->refresh();
    }

    public function walletContext(Booking|Order $model, Transaction $transaction): object
    {
        $a = $this->allocation($model);
        $contexts = DB::table('payment_collection_contexts')->where('allocation_id', $a->id)
            ->where('payment_id', $transaction->payment_sys_id)->where('collection_mode', 'internal')
            ->where('amount', ExactMoney::units((string) $transaction->getRawOriginal('price'), (int) $a->money_scale))
            ->whereIn('state', ['committed','pending'])->orderBy('id')->get();
        if ($contexts->count() !== 1) {
            throw new \DomainException('Wallet debit must have one precommitted original contribution.');
        }
        return $contexts->first();
    }

    public static function assertWalletCurrency(int $currency, ?int $payer): void
    {
        $wallet = $payer === null ? null : DB::table('wallets')->where('user_id',$payer)->first();
        if (!$wallet || (int)$wallet->currency_id !== $currency) {
            throw new \DomainException('Owned Wallet currency must match the frozen charge currency; unproven FX is blocked.');
        }
    }

    public function walletConfirmed(Booking|Order $model, Transaction $transaction, object $history): void
    {
        $context = $this->walletContext($model, $transaction);
        $a = $this->allocation($model);
        $wallet = DB::table('wallets')->where('id', $context->wallet_id)->first();
        if (!$wallet || $history->uuid !== $context->wallet_history_reference || $history->wallet_uuid !== $wallet->uuid
            || (int) $history->transaction_id !== (int) $transaction->id || $history->type !== 'withdraw'
            || $history->status !== Transaction::STATUS_PAID
            || ExactMoney::units((string) $history->getRawOriginal('price'), (int) $a->money_scale) !== (int) $context->amount) {
            throw new \DomainException('Original Wallet withdrawal evidence mismatch.');
        }
        (new AllocationWriter)->linkTransaction((int) $transaction->id, (int) $a->id, (int) $context->id);
        (new AllocationWriter)->confirm([(int) $context->id], 'wallet-withdrawal:'.$history->uuid, (int) $context->amount);
        $transaction->refresh();
    }
}