<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Append-only, locally authorized effect groups. No transport, provider refund,
 * payout route, or public/manual correction endpoint is supplied by this phase.
 */
final class AccountingEffects
{
    public function append(int $allocationId, string $group, array $effects): void
    {
        if (!Str::isUuid($group) || $effects === []) {
            throw new \DomainException('A retained native operation ID and complete effect group are required.');
        }
        DB::transaction(function () use ($allocationId, $group, $effects): void {
            $a = (new AllocationWriter)->lock($allocationId);
            if ($a->state === 'review_required' || $a->state === 'canceled') {
                throw new \DomainException('Review/canceled allocation cannot accept economic effects.');
            }
            $rows = [];
            foreach ($effects as $effect) {
                $kind = $effect['kind']; $contextId = $effect['context_id'] ?? null; $amount = $effect['amount'];
                if ($a->finalized_at === null && $kind !== 'refund_principal') {
                    throw new \DomainException('Incomplete funding can return original principal, not recognize/reverse full economics.');
                }
                if (!is_int($amount) || ($kind !== 'payable_delta' && $amount < 0)) {
                    throw new \DomainException('Effect requires exact integer units.');
                }
                $context = $contextId === null ? null : DB::table('payment_collection_contexts')->where('id', $contextId)->first();
                if ($contextId !== null && (!$context || (int) $context->allocation_id !== $allocationId || $context->state !== 'confirmed')) {
                    throw new \DomainException('Effect must reference original confirmed custody.');
                }
                if (in_array($kind, ['refund_principal','adjustment_reversal'], true) && !$context) {
                    throw new \DomainException('Principal/adjustment reversal needs an original context.');
                }
                $key = match ($kind) {
                    'refund_principal' => "refund:{$group}:principal:{$contextId}",
                    'commission_reversal' => "refund:{$group}:commission",
                    'adjustment_reversal' => "refund:{$group}:adjustment:{$contextId}",
                    'payable_delta' => "refund:{$group}:payable",
                    'vendor_settlement' => "settlement:{$group}:vendor",
                    'settlement_reversal' => "settlement:{$group}:reversal",
                    'receivable_collection' => "receivable:{$group}:collection",
                    'receivable_collection_reversal' => "receivable:{$group}:reversal",
                    default => throw new \DomainException('Unapproved effect kind.'),
                };
                // Authorization/proof is typed, not a mutable request payload.
                if (!isset($effect['proof']['authority'], $effect['proof']['operation'])
                    || $effect['proof']['operation'] !== $group) {
                    throw new \DomainException('Missing trusted native effect authorization.');
                }
                $rows[$key] = [
                    'allocation_id' => $allocationId, 'collection_context_id' => $contextId,
                    'effect_key' => $key, 'event_group_key' => $group, 'effect_kind' => $kind,
                    'exact_amount' => $amount, 'effect_data' => AllocationWriter::json($effect['proof']),
                    'transaction_id' => null, 'payment_id' => null, 'shop_id' => $a->shop_id, 'currency_id' => $a->currency_id,
                    'payable_type' => $a->payable_type === 'booking' ? \App\Models\Booking::class : \App\Models\Order::class,
                    'payable_id' => $a->payable_id, 'entry_type' => 'payable_adjustment',
                    'amount' => ExactMoney::decimal($amount, (int) $a->money_scale), 'status' => 'pending',
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            if (count($rows) !== count($effects)) {
                throw new \DomainException('Duplicate effect in one group.');
            }
            $existing = DB::table('platform_fee_ledger_entries')->where('allocation_id', $allocationId)
                ->where('event_group_key', $group)->orderBy('effect_key')->get();
            if ($existing->isNotEmpty()) {
                $oldKeys = $existing->pluck('effect_key')->all(); $keys = array_keys($rows); sort($keys);
                if ($oldKeys !== $keys) {
                    throw new \DomainException('Effect group cannot be extended or partially replayed.');
                }
                foreach ($existing as $old) {
                    foreach (['exact_amount','collection_context_id','effect_kind','effect_data'] as $field) {
                        $equal = $field === 'effect_data'
                            ? SemanticJson::equal((string)$old->$field, (string)$rows[$old->effect_key][$field])
                            : (string)$old->$field === (string)$rows[$old->effect_key][$field];
                        if (!$equal) {
                            throw new \DomainException('Economic effect evidence cannot change.');
                        }
                    }
                }
                return;
            }
            $before = (new AllocationBalances)->current($allocationId);
            foreach ($rows as $row) {
                if ($row['effect_kind'] === 'vendor_settlement' && $row['exact_amount'] > $before['vendor_payable']) {
                    throw new \DomainException('Settlement exceeds actual platform Vendor liability.');
                }
                if ($row['effect_kind'] === 'receivable_collection' && $row['exact_amount'] > $before['commission_receivable']) {
                    throw new \DomainException('Recovery exceeds outstanding Vendor commission.');
                }
            }
            DB::table('platform_fee_ledger_entries')->insert(array_values($rows));
            $after = (new AllocationBalances)->current($allocationId); // caps/conservation or rollback entire group
            if ($a->finalized_at !== null && $after['refunded'] > 0) {
                DB::table('commerce_payment_allocations')->where('id', $allocationId)->where('version', $a->version)->update([
                    'state' => $after['gross'] === 0 ? 'fully_reversed' : 'partially_reversed',
                    'version' => (int) $a->version + 1, 'updated_at' => now(),
                ]);
            }
            // payable_delta is an auditable projection, never another adjustment
            // added to M'. Verify it against the actual frozen economics/effects.
            foreach ($rows as $row) {
                if ($row['effect_kind'] === 'payable_delta'
                    && $row['exact_amount'] !== $after['vendor_payable'] - $before['vendor_payable']) {
                    throw new \DomainException('Payable delta must match the effect-group liability change.');
                }
            }
        }, 3);
    }
}