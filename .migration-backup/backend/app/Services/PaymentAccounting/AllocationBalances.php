<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\DB;

/** Exact read-only liability projection; no current settings or legacy inference. */
final class AllocationBalances
{
    public function current(int $id): array
    {
        $a = DB::table('commerce_payment_allocations')->where('id', $id)->first();
        if (!$a) {
            throw new \DomainException('Missing retained allocation.');
        }
        $contexts = DB::table('payment_collection_contexts')->where('allocation_id', $id)->where('state', 'confirmed')->get();
        $effects = DB::table('platform_fee_ledger_entries')->where('allocation_id', $id)->get();
        $sum = fn (string $kind) => ExactMoney::sum($effects->where('effect_kind', $kind)
            ->pluck('exact_amount')->map(fn ($n) => (int) $n)->all());
        $p = 0; $d = 0; $refund = 0; $ap = 0; $ad = 0;
        foreach ($contexts as $context) {
            $returned = ExactMoney::sum($effects->where('effect_kind', 'refund_principal')->where('collection_context_id', $context->id)
                ->pluck('exact_amount')->map(fn ($n) => (int) $n)->all());
            if ($returned > (int) $context->amount) {
                throw new \DomainException('Refund exceeds original contribution.');
            }
            $adjustmentReversal = ExactMoney::sum($effects->where('effect_kind', 'adjustment_reversal')
                ->where('collection_context_id', $context->id)->pluck('exact_amount')->map(fn ($n) => (int) $n)->all());
            $remainingAdjustment = (int) ($context->original_adjustment_share ?? 0) - $adjustmentReversal;
            if ($remainingAdjustment < 0) {
                throw new \DomainException('Adjustment reversal exceeds original assignment.');
            }
            $remaining = (int) $context->amount - $returned;
            if ($context->custody_type === 'platform') {
                $p = ExactMoney::sum([$p, $remaining]); $ap = ExactMoney::sum([$ap, $remainingAdjustment]);
            } else {
                $d = ExactMoney::sum([$d, $remaining]); $ad = ExactMoney::sum([$ad, $remainingAdjustment]);
            }
            $refund = ExactMoney::sum([$refund, $returned]);
        }
        if ($a->finalized_at === null) {
            return [
                'finalized' => false, 'currency_id' => (int) $a->currency_id, 'money_scale' => (int) $a->money_scale,
                'quoted_gross' => (int) $a->gross_amount, 'quoted_commission' => (int) $a->commission_amount,
                'platform_principal' => $p, 'vendor_principal' => $d, 'customer_held_exposure' => $p,
                'funding_returned' => $refund,
                'commission_satisfied' => 0, 'commission_receivable' => 0, 'vendor_payable' => 0,
            ];
        }
        $g = (int) $a->gross_amount - $refund;
        $c = (int) $a->commission_amount - $sum('commission_reversal');
        $adjustment = (int) $a->adjustment_amount - $sum('adjustment_reversal');
        if ($c < 0 || $adjustment < 0 || $g < 0) {
            throw new \DomainException('Effect exceeds original quote.');
        }
        $s = min($p, $c); $r = $c - $s;
        if ($ap > $p - $s || $ad > max($d - $r, 0) || $ap + $ad !== $adjustment) {
            throw new \DomainException('Residual adjustment requires authorized reattribution/review.');
        }
        $m = $p - $s - $ap;
        $u = $sum('vendor_settlement') - $sum('settlement_reversal');
        $q = $sum('receivable_collection') - $sum('receivable_collection_reversal');
        if ($u < 0 || $q < 0) {
            throw new \DomainException('Settlement/recovery reversal exceeds original effect.');
        }
        return [
            'finalized' => true, 'currency_id' => (int) $a->currency_id, 'money_scale' => (int) $a->money_scale,
            'original_gross' => (int) $a->gross_amount, 'original_commission' => (int) $a->commission_amount,
            'original_vendor_payable' => (int) $a->original_vendor_payable,
            'refunded' => $refund, 'gross' => $g, 'commission' => $c, 'adjustment' => $adjustment,
            'vendor_entitlement' => max($g - $c - $adjustment, 0), 'economic_shortfall' => max($c + $adjustment - $g, 0),
            'platform_principal' => $p, 'vendor_principal' => $d, 'commission_satisfied' => $s,
            'commission_receivable' => max($r - $q, 0), 'vendor_payable' => max($m - $u, 0),
            'settlement_recovery_due' => max($u - $m, 0), 'excess_commission_recovery' => max($q - $r, 0),
            'net_platform_position' => $p - $u + $q,
        ];
    }

    /** Never mix currencies or infer liability from compatibility status labels. */
    public function forVendor(int $vendorId, int $currencyId): array
    {
        $ids = DB::table('commerce_payment_allocations')->where('vendor_user_id', $vendorId)
            ->where('currency_id', $currencyId)->whereNotNull('finalized_at')->pluck('id');
        $result = [];
        foreach ($ids as $id) {
            $value = $this->current((int) $id);
            // Return scale-separated totals; callers cannot sum different unit scales.
            $scale = $value['money_scale'];
            foreach (['vendor_payable','commission_receivable','settlement_recovery_due','excess_commission_recovery'] as $field) {
                $result[$scale][$field] = ExactMoney::sum([$result[$scale][$field] ?? 0, $value[$field]]);
            }
        }
        return $result;
    }
}