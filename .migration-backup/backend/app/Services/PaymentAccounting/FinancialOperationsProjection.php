<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only retained evidence, never a payment/refund/payout authorizer. */
final class FinancialOperationsProjection
{
    public function forTransaction(Transaction $transaction, ?User $actor): ?array
    {
        if (!$actor || !Schema::hasTable('commerce_payment_allocations')) return null;
        $type = match ($transaction->payable_type) {
            Order::class => 'order', Booking::class => 'booking', default => null,
        };
        if (!$type) return null;
        $allocation = DB::table('commerce_payment_allocations')
            ->where('payable_type', $type)->where('payable_id', $transaction->payable_id)
            ->where('purpose', 'base')->where('obligation_key', 'base')->first();
        if (!$allocation) return null; // Legacy does not acquire invented evidence.
        $country = $actor->countryAdmin?->country_id;
        $admin = $actor->hasRole('admin')
            && ($country === null || (int) $country === (int) $allocation->country_id);
        if (!$admin && !$actor->hasShopPermission((int) $allocation->shop_id, 'payments.gateways.manage')) return null;

        // One database snapshot prevents mixed pre/post-confirmation projections.
        return DB::transaction(function () use ($allocation): array {
            $allocation = DB::table('commerce_payment_allocations')->where('id', $allocation->id)->first();
            $balances = (new AllocationBalances)->current((int) $allocation->id);
            $contexts = DB::table('payment_collection_contexts')->where('allocation_id', $allocation->id)
                ->orderBy('id')->get();
            $effects = DB::table('platform_fee_ledger_entries')->where('allocation_id', $allocation->id)->get();
            $confirmed = $contexts->isNotEmpty() && $contexts->every(fn ($c) => $c->state === 'confirmed');
            $refund = (int) ($balances['refunded'] ?? $balances['funding_returned'] ?? 0);
            $receivable = (int) $balances['commission_receivable'];
            $payable = (int) $balances['vendor_payable'];
            $settled = $effects->contains('effect_kind', 'vendor_settlement');
            $collected = $effects->contains('effect_kind', 'receivable_collection');
            foreach ($balances as $key => $value) {
                if (is_int($value) && !in_array($key, ['money_scale', 'currency_id'], true)) {
                    $balances[$key] = (string) $value; // Preserve BIGINT in browsers.
                }
            }
            return [
                'allocation_id' => (int) $allocation->id, 'shop_id' => (int) $allocation->shop_id,
                'state' => $allocation->state, 'currency_code' => $allocation->currency_code,
                'money_scale' => (int) $allocation->money_scale, 'balances' => $balances,
                'payment_verified' => (bool) $balances['finalized'] && $confirmed,
                'external_settlement_status' => 'NOT_VERIFIED',
                'refund_status' => $refund > 0 ? 'REFUND_ACCOUNTING_RECORDED' : 'NO_RECORDED_REFUND',
                'payout_status' => !$balances['finalized'] ? 'NOT_ELIGIBLE'
                    : ($settled ? ($payable > 0 ? 'PARTIALLY_ACCOUNTED' : 'SETTLEMENT_ACCOUNTING_RECORDED')
                        : ($payable > 0 ? 'PAYABLE_OPEN_EXTERNAL_PAYOUT_NOT_READY' : 'NOT_APPLICABLE')),
                'commission_receivable_state' => $receivable > 0 ? 'OPEN'
                    : ($collected ? 'SETTLEMENT_ACCOUNTING_RECORDED' : 'NOT_APPLICABLE'),
                'contributions' => $contexts->map(fn ($c) => [
                    'provider' => $c->provider_tag ?: ($c->collection_mode === 'offline' ? 'cash' : 'wallet'),
                    'collection_mode' => $c->collection_mode, 'custody' => $c->custody_type,
                    'state' => $c->state, 'provider_reference' => $c->provider_payment_reference,
                ])->all(),
            ];
        });
    }
}