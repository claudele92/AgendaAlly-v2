<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Internal trusted boundary, not an HTTP input contract. Every financial claim
 * is inside one SQL transaction. SQLite obtains a write reservation before reads.
 */
final class AllocationWriter
{
    private const ALLOCATIONS = 'commerce_payment_allocations';
    private const CONTEXTS = 'payment_collection_contexts';
    private const LEDGER = 'platform_fee_ledger_entries';

    public function commit(array $snapshot): int
    {
        return DB::transaction(function () use ($snapshot): int {
            $terms = [
                'checkout_key','origin_type','origin_id','shop_id','vendor_user_id','payer_user_id','local_client_id',
                'country_id','currency_id','currency_code','money_scale','purpose','obligation_key','payable_type','payable_id',
                'gross_amount','commission_amount','vendor_entitlement_amount','adjustment_amount','native_components','policy_key',
            ];
            foreach ($terms as $field) {
                if (!array_key_exists($field, $snapshot)) {
                    throw new \DomainException("Incomplete trusted quote: {$field}");
                }
            }
            if (!Str::isUuid($snapshot['checkout_key']) || $snapshot['policy_key'] !== 'platform_held_first_commission') {
                throw new \DomainException('Invalid server checkout policy.');
            }
            $g = $snapshot['gross_amount']; $c = $snapshot['commission_amount'];
            $e = $snapshot['vendor_entitlement_amount']; $a = $snapshot['adjustment_amount'];
            if (ExactMoney::sum([$c, $e, $a]) !== $g) {
                throw new \DomainException('Quote does not conserve integer money.');
            }
            // Resolve native beneficiary/ownership from persisted shop/country,
            // never from the caller's financial metadata.
            $shop = DB::table('shops')->where('id', $snapshot['shop_id'])->first();
            if (!$shop || (int) $shop->user_id !== $snapshot['vendor_user_id']) {
                throw new \DomainException('Unproven original Vendor beneficiary.');
            }
            $identity = array_intersect_key($snapshot, array_flip([
                'checkout_key','origin_type','origin_id','shop_id','purpose','obligation_key',
            ]));
            // insertOrIgnore handles only creation contention. Always verify the
            // retained snapshot afterward; no constraint failure is success.
            $row = array_intersect_key($snapshot, array_flip($terms));
            $row['native_components'] = self::json($row['native_components']);
            $now = now();
            $row += ['state' => 'committed', 'version' => 0, 'committed_at' => $now, 'created_at' => $now, 'updated_at' => $now];
            DB::table(self::ALLOCATIONS)->insertOrIgnore($row);
            $existing = DB::table(self::ALLOCATIONS)->where($identity)->first();
            if (!$existing) {
                throw new \DomainException('Economic identity conflict or invalid quote.');
            }
            foreach ($terms as $field) {
                $equal = $field === 'native_components'
                    ? SemanticJson::equal((string)$existing->$field, (string)$row[$field])
                    : (string)$existing->$field === (string)$row[$field];
                if (!$equal) {
                    throw new \DomainException("Committed quote is immutable: {$field}");
                }
            }
            return (int) $existing->id;
        }, 3);
    }

    public function bind(int $allocationId, string $type, int $id): void
    {
        DB::transaction(function () use ($allocationId, $type, $id): void {
            $a = $this->lock($allocationId);
            if ($a->payable_type !== null) {
                if ($a->payable_type !== $type || (int) $a->payable_id !== $id) {
                    throw new \DomainException('Economic payable cannot be rebound.');
                }
                return;
            }
            if (!in_array($a->state, ['committed','funding'], true) || $a->finalized_at !== null) {
                throw new \DomainException('Terminal Cart allocation cannot acquire a new payable.');
            }
            if ($a->origin_type !== 'cart' || $type !== 'order') {
                throw new \DomainException('Only Cart allocations bind to a new Order.');
            }
            $order = DB::table('orders')->where('id', $id)->first();
            if (!$order || (int) $order->cart_id !== (int) $a->origin_id || (int) $order->shop_id !== (int) $a->shop_id
                || (int) $order->currency_id !== (int) $a->currency_id || (int) $order->user_id !== (int) $a->payer_user_id) {
                throw new \DomainException('Unproven Cart/Shop/Order binding.');
            }
            $this->cas($a, ['payable_type' => $type, 'payable_id' => $id]);
        }, 3);
    }

    public function stage(int $allocationId, array $evidence): int
    {
        return DB::transaction(function () use ($allocationId, $evidence): int {
            $a = $this->lock($allocationId);
            if (!in_array($a->state, ['committed', 'funding'], true)) {
                // Replay may resolve an already-confirmed retained contribution.
                $old = DB::table(self::CONTEXTS)->where('allocation_id', $allocationId)
                    ->where('funding_key', $evidence['funding_key'])->first();
                if (!$old || $old->state !== 'confirmed') {
                    throw new \DomainException('Allocation does not accept new funding.');
                }
            }
            foreach (['currency_id','currency_code','money_scale'] as $field) {
                if ((string) $evidence[$field] !== (string) $a->$field) {
                    throw new \DomainException('Contribution currency/scale mismatch.');
                }
            }
            if (!is_int($evidence['amount']) || $evidence['amount'] <= 0 || $evidence['amount'] > (int) $a->gross_amount
                || !is_int($evidence['receipt_total_amount']) || $evidence['receipt_total_amount'] < $evidence['amount']
                || !Str::isUuid($evidence['funding_event_key'])) {
                throw new \DomainException('Invalid trusted funding amount/event.');
            }
            if (($evidence['expected_collector_type'] === 'shop' && (int) $evidence['expected_collector_id'] !== (int) $a->shop_id)
                || ($evidence['credential_owner_type'] === 'shop' && (int) $evidence['credential_owner_id'] !== (int) $a->shop_id)) {
                throw new \DomainException('Cross-Shop collection ownership forbidden.');
            }
            $allowed = [
                'funding_key','funding_slot','funding_event_key','collection_mode','custody_type',
                'expected_collector_type','expected_collector_id','credential_owner_type','credential_owner_id',
                'payment_id','provider_tag','configuration_source','configuration_reference','configuration_revision',
                'merchant_binding_reference','payment_process_reference','provider_payment_reference',
                'source_transaction_id','wallet_id','wallet_history_reference','currency_id','currency_code','money_scale',
                'amount','receipt_total_amount',
            ];
            $row = array_intersect_key($evidence, array_flip($allowed));
            $row += ['allocation_id' => $allocationId, 'state' => 'committed', 'version' => 0,
                'committed_at' => now(), 'created_at' => now(), 'updated_at' => now()];
            DB::table(self::CONTEXTS)->insertOrIgnore($row);
            $old = DB::table(self::CONTEXTS)->where('allocation_id', $allocationId)->where('funding_key', $evidence['funding_key'])->first();
            if (!$old) {
                throw new \DomainException('Contribution identity conflict.');
            }
            foreach ($allowed as $field) {
                if ((string) ($old->$field ?? null) !== (string) ($row[$field] ?? null)) {
                    throw new \DomainException("Contribution evidence is immutable: {$field}");
                }
            }
            if ($a->state === 'committed') {
                $this->cas($a, ['state' => 'funding']);
            }
            return (int) $old->id;
        }, 3);
    }

    /** Persist authenticated provider reference once, before outbound retry. */
    public function pending(array $contextIds, ?string $providerReference = null): void
    {
        DB::transaction(function () use ($contextIds, $providerReference): void {
            foreach ($this->orderedContexts($contextIds) as $context) {
                $this->lock((int) $context->allocation_id);
                if ($context->state === 'confirmed') {
                    continue;
                }
                if (!in_array($context->state, ['committed','pending'], true)
                    || ($context->provider_payment_reference !== null && $context->provider_payment_reference !== $providerReference)) {
                    throw new \DomainException('Pending receipt identity conflict.');
                }
                DB::table(self::CONTEXTS)->where('id', $context->id)->where('version', $context->version)->update([
                    'state' => 'pending', 'provider_payment_reference' => $providerReference,
                    'version' => (int) $context->version + 1, 'updated_at' => now(),
                ]);
            }
        }, 3);
    }

    /**
     * All members were staged before charge/debit. receiptIdentity comes ONLY
     * from a trusted adapter (verified provider reference, withdrawal UUID, or
     * retained Cash acceptance event), never from a browser callback.
     */
    public function confirm(array $contextIds, string $receiptIdentity, int $receiptTotal): void
    {
        // Discovery supplies routing, never funding state. In an owned root this
        // must not establish an RR view before the authoritative parent mutex.
        $discovered = $this->orderedContexts($contextIds);
        if ($discovered->isEmpty() || $receiptIdentity === '') {
            throw new \DomainException('Missing authenticated funding evidence.');
        }
        DB::transaction(function () use ($discovered, $receiptIdentity, $receiptTotal): void {
            $allocations = [];
            foreach ($discovered->pluck('allocation_id')->unique()->sort() as $id) {
                $allocations[$id] = $this->lock((int) $id);
            }
            $binding = [
                'funding_event_key','payment_id','collection_mode','custody_type','credential_owner_type',
                'credential_owner_id','provider_tag','merchant_binding_reference','currency_id','currency_code','money_scale',
                'configuration_source','configuration_reference','configuration_revision','wallet_id','wallet_history_reference',
            ];
            // Known-existing primary-key current reads, after parents: never reuse
            // pre-wait objects or use a child authority range scan to repair RR.
            $contexts = $discovered->map(function ($old) use ($binding): object {
                $current = DB::table(self::CONTEXTS)->where('id', $old->id)->lockForUpdate()->first();
                if (!$current) throw new \DomainException('Missing original funding context.');
                foreach (array_merge($binding, ['allocation_id','funding_key','funding_slot',
                    'amount','receipt_total_amount','expected_collector_type','expected_collector_id']) as $field) {
                    if ((string) $current->$field !== (string) $old->$field) {
                        throw new \DomainException("Original contribution binding changed: {$field}");
                    }
                }
                return $current;
            });
            $first = $contexts->first();
            $checkout = $allocations[$first->allocation_id]->checkout_key;
            $members = DB::table(self::CONTEXTS)->where('funding_event_key', $first->funding_event_key)->orderBy('id')->get();
            if ($members->pluck('id')->map(fn ($id) => (int) $id)->all() !== $contexts->pluck('id')->map(fn ($id) => (int) $id)->all()) {
                throw new \DomainException('Receipt membership must be frozen and confirmed as one group.');
            }
            foreach ($contexts as $context) {
                foreach (['currency_id','currency_code','money_scale'] as $field) {
                    if ((string) $context->$field !== (string) $allocations[$context->allocation_id]->$field) {
                        throw new \DomainException('Contribution currency/scale mismatch.');
                    }
                }
                if ($allocations[$context->allocation_id]->checkout_key !== $checkout || (int) $context->receipt_total_amount !== $receiptTotal) {
                    throw new \DomainException('Shared receipt checkout/cap mismatch.');
                }
                foreach (['payer_user_id','local_client_id'] as $payer) {
                    if ((string)$allocations[$context->allocation_id]->$payer !== (string)$allocations[$first->allocation_id]->$payer) {
                        throw new \DomainException('Shared receipt cannot combine different original payers.');
                    }
                }
                foreach ($binding as $field) {
                    if ((string) $context->$field !== (string) $first->$field) {
                        throw new \DomainException("Shared receipt binding mismatch: {$field}");
                    }
                }
            }
            if (ExactMoney::sum($contexts->pluck('amount')->map(fn ($n) => (int) $n)->all()) !== $receiptTotal) {
                throw new \DomainException('Receipt does not match its frozen contribution shares.');
            }
            // Namespace independent of config revision/rotation or checkout key.
            $key = hash('sha256', self::json([
                $first->collection_mode, $first->provider_tag, $first->credential_owner_type,
                $first->credential_owner_id, $first->merchant_binding_reference, $receiptIdentity,
            ]));
            $anchor = DB::table(self::CONTEXTS)->where('receipt_claim_key', $key)->lockForUpdate()->first();
            if ($anchor && !in_array((int) $anchor->id, $contexts->pluck('id')->map(fn ($id) => (int) $id)->all(), true)) {
                throw new \DomainException('Receipt already claimed by another checkout.');
            }
            $anchorId = $anchor ? (int) $anchor->id : (int) $first->id;
            foreach ($contexts as $context) {
                $a = $allocations[$context->allocation_id];
                if ($context->state === 'confirmed') {
                    if ((int) $context->id === $anchorId ? $context->receipt_claim_key !== $key
                        : (int) $context->receipt_anchor_context_id !== $anchorId) {
                        throw new \DomainException('Confirmed contribution received different evidence.');
                    }
                    if ($context->confirmed_slot !== $context->funding_slot
                        || $context->confirmed_collector_type !== $context->expected_collector_type
                        || (string) $context->confirmed_collector_id !== (string) $context->expected_collector_id) {
                        throw new \DomainException('Confirmed contribution received different evidence.');
                    }
                    if ($a->finalized_at !== null) $this->assertRetainedConfirmation($a, $context);
                    continue;
                }
                if (!in_array($context->state, ['committed','pending'], true) || !in_array($a->state, ['committed','funding'], true)
                    || $a->finalized_at !== null) {
                    throw new \DomainException('Terminal allocation/contribution cannot accept funding.');
                }
                $funded = (int) DB::table(self::CONTEXTS)->where('allocation_id', $a->id)->where('state', 'confirmed')->sum('amount');
                if ($context->amount > $a->gross_amount - $funded) {
                    throw new \DomainException('Contribution exceeds unfunded economic value.');
                }
                $changed = DB::table(self::CONTEXTS)->where('id', $context->id)->where('version', $context->version)
                    ->whereIn('state', ['committed','pending'])->update([
                        'state' => 'confirmed', 'confirmed_at' => now(), 'confirmed_slot' => $context->funding_slot,
                        'confirmed_collector_type' => $context->expected_collector_type,
                        'confirmed_collector_id' => $context->expected_collector_id,
                        'receipt_claim_key' => (int) $context->id === $anchorId ? $key : null,
                        'receipt_anchor_context_id' => (int) $context->id === $anchorId ? null : $anchorId,
                        'version' => (int) $context->version + 1, 'updated_at' => now(),
                    ]);
                if ($changed !== 1) {
                    throw new \DomainException('Contribution claim lost.');
                }
            }
            foreach (array_keys($allocations) as $id) {
                if ($allocations[$id]->state !== 'canceled') $this->finalize((int) $id);
            }
        }, 3);
    }

    public function finalize(int $id): void
    {
        // Called within confirm's existing transaction, also safe independently.
        DB::transaction(function () use ($id): void {
            $a = $this->lock($id);
            if ($a->finalized_at !== null) {
                return;
            }
            if (!in_array($a->state, ['committed','funding'], true)) {
                throw new \DomainException('Terminal allocation cannot finalize.');
            }
            $contexts = DB::table(self::CONTEXTS)->where('allocation_id', $id)->where('state', 'confirmed')->orderBy('id')->get();
            $total = ExactMoney::sum($contexts->pluck('amount')->map(fn ($n) => (int) $n)->all());
            if ($total !== (int) $a->gross_amount) {
                return; // Quoted terms are not recognized while incompletely funded.
            }
            if ($a->payable_id === null) {
                return; // Retained Cart funding exposure; finalize only after native binding.
            }
            $held = $contexts->where('custody_type', 'platform')->mapWithKeys(fn ($c) => [$c->id => (int) $c->amount])->all();
            $direct = $contexts->where('custody_type', 'vendor')->mapWithKeys(fn ($c) => [$c->id => (int) $c->amount])->all();
            $p = ExactMoney::sum(array_values($held)); $d = $total - $p;
            $c = (int) $a->commission_amount; $s = min($p, $c); $r = $c - $s;
            // Noncommission adjustments require a frozen, typed per-context native
            // responsibility. Never infer custody/beneficiary from a field name.
            $native = json_decode($a->native_components, true, 512, JSON_THROW_ON_ERROR);
            $adjustments = $native['adjustment_context_units'] ?? [];
            $ap = 0; $ad = 0;
            foreach ($contexts as $context) {
                $value = $adjustments[$context->id] ?? 0;
                if (!is_int($value) || $value < 0) {
                    throw new \DomainException('Unproven native adjustment assignment.');
                }
                if ($context->custody_type === 'platform') {
                    $ap = ExactMoney::sum([$ap, $value]);
                } else {
                    $ad = ExactMoney::sum([$ad, $value]);
                }
            }
            if (ExactMoney::sum([$ap, $ad]) !== (int) $a->adjustment_amount || $ap > $p - $s || $ad > $d - $r) {
                throw new \DomainException('Native adjustment custody contract does not reconcile.');
            }
            $m = $p - $s - $ap;
            $sShares = ExactMoney::partition($s, $held);
            $rShares = ExactMoney::partition($r, $direct);
            foreach ($contexts as $context) {
                $cs = $sShares[$context->id] ?? 0; $rs = $rShares[$context->id] ?? 0;
                $as = $adjustments[$context->id] ?? 0;
                $entitlement = (int) $context->amount - $cs - $rs - $as;
                if ($entitlement < 0) {
                    throw new \DomainException('Contribution adjustment exceeds its retained custody.');
                }
                DB::table(self::CONTEXTS)->where('id', $context->id)->whereNull('original_commission_share')->update([
                    'original_commission_share' => $cs, 'original_receivable_share' => $rs,
                    'original_adjustment_share' => $as, 'original_vendor_entitlement_share' => $entitlement,
                ]);
            }
            $this->cas($a, [
                'original_platform_amount' => $p, 'original_vendor_direct_amount' => $d,
                'original_commission_satisfied' => $s, 'original_commission_receivable' => $r,
                'original_platform_adjustment' => $ap, 'original_vendor_adjustment' => $ad,
                'original_vendor_payable' => $m, 'finalized_at' => now(), 'state' => 'funded',
            ], true);
            $this->baseEffect($a, 'base:commission', 'base_commission', $c);
            $this->baseEffect($a, 'base:payable', 'base_payable', $m);
        }, 3);
    }

    public function reject(int $contextId, bool $authoritativeNoCollection): void
    {
        if (!$authoritativeNoCollection) {
            throw new \DomainException('Timeout/disabled provider is not proof of no collection.');
        }
        DB::transaction(function () use ($contextId): void {
            $context = DB::table(self::CONTEXTS)->where('id', $contextId)->first();
            if (!$context) {
                throw new \DomainException('Missing contribution.');
            }
            $this->lock((int) $context->allocation_id);
            if (!in_array($context->state, ['committed','pending','rejected'], true)) {
                throw new \DomainException('Confirmed contribution evidence is terminal.');
            }
            DB::table(self::CONTEXTS)->where('id', $contextId)->where('version', $context->version)->update([
                'state' => 'rejected', 'version' => (int) $context->version + 1, 'updated_at' => now(),
            ]);
        }, 3);
    }

    public function cancel(int $allocationId): void
    {
        DB::transaction(function () use ($allocationId): void {
            $a = $this->lock($allocationId);
            if ($a->state === 'canceled') {
                return;
            }
            $remaining = (new AllocationBalances)->current($allocationId);
            if (!in_array($a->state, ['committed','funding'], true) || $a->finalized_at !== null
                || $remaining['platform_principal'] !== 0 || $remaining['vendor_principal'] !== 0
                || DB::table(self::CONTEXTS)->where('allocation_id', $allocationId)
                    ->whereNotIn('state', ['confirmed','rejected','canceled'])->exists()) {
                throw new \DomainException('Held value or unresolved receipt prevents cancellation.');
            }
            $this->cas($a, ['state' => 'canceled']);
        }, 3);
    }

    public function linkTransaction(int $transactionId, int $allocationId, ?int $contextId): void
    {
        DB::transaction(function () use ($transactionId, $allocationId, $contextId): void {
            $a = $this->lock($allocationId);
            $t = DB::table('transactions')->where('id', $transactionId)->lockForUpdate()->first();
            $context = $contextId === null ? null : DB::table(self::CONTEXTS)->where('id', $contextId)->first();
            if (!$t || ($contextId !== null && (!$context || (int) $context->allocation_id !== $allocationId))
                || ($t->allocation_id !== null && (int) $t->allocation_id !== $allocationId)
                || ($t->collection_context_id !== null && (int) $t->collection_context_id !== $contextId)) {
                throw new \DomainException('Transaction allocation/provenance mismatch.');
            }
            $nativeType = $a->payable_type === 'booking' ? \App\Models\Booking::class : \App\Models\Order::class;
            if ($a->payable_id === null || $t->payable_type !== $nativeType || (int) $t->payable_id !== (int) $a->payable_id) {
                throw new \DomainException('Transaction cannot link another payable.');
            }
            DB::table('transactions')->where('id', $transactionId)->update([
                'allocation_id' => $allocationId, 'collection_context_id' => $contextId,
            ]);
        }, 3);
    }

    private function baseEffect(object $a, string $key, string $kind, int $amount): void
    {
        if ($a->payable_id === null) {
            throw new \DomainException('Cart must bind before economic finalization.');
        }
        DB::table(self::LEDGER)->insert([
            'allocation_id' => $a->id, 'collection_context_id' => null,
            'effect_key' => $key, 'effect_kind' => $kind, 'event_group_key' => null,
            'exact_amount' => $amount, 'effect_data' => self::json(['policy' => $a->policy_key]),
            'payable_type' => $a->payable_type === 'booking' ? \App\Models\Booking::class : \App\Models\Order::class,
            'payable_id' => $a->payable_id, 'shop_id' => $a->shop_id,
            'transaction_id' => null, 'payment_id' => null, 'currency_id' => $a->currency_id,
            'entry_type' => $kind === 'base_commission' ? 'fee' : 'payable',
            'amount' => ExactMoney::decimal($amount, (int) $a->money_scale), 'status' => 'pending',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Replay proves retained success; it must not repair or recreate its effects. */
    private function assertRetainedConfirmation(object $a, object $context): void
    {
        foreach (['original_commission_share','original_receivable_share',
            'original_adjustment_share','original_vendor_entitlement_share'] as $field) {
            if ($context->$field === null) {
                throw new \DomainException('Confirmed contribution is missing canonical effects.');
            }
        }
        if (ExactMoney::sum(array_map(fn ($field) => (int) $context->$field,
            ['original_commission_share','original_receivable_share','original_adjustment_share',
                'original_vendor_entitlement_share'])) !== (int) $context->amount) {
            throw new \DomainException('Confirmed contribution has incompatible canonical effects.');
        }
        foreach ([
            ['base:commission', 'base_commission', (int) $a->commission_amount],
            ['base:payable', 'base_payable', (int) $a->original_vendor_payable],
        ] as [$key, $kind, $amount]) {
            // The existing UNIQUE(allocation_id,effect_key) proves exactly once.
            $effect = DB::table(self::LEDGER)->where('allocation_id', $a->id)
                ->where('effect_key', $key)->lockForUpdate()->first();
            if (!$effect || $effect->effect_kind !== $kind || (int) $effect->exact_amount !== $amount
                || $effect->collection_context_id !== null || (int) $effect->currency_id !== (int) $a->currency_id
                || (int) $effect->shop_id !== (int) $a->shop_id || (int) $effect->payable_id !== (int) $a->payable_id
                || $effect->payable_type !== ($a->payable_type === 'booking' ? \App\Models\Booking::class : \App\Models\Order::class)
                || !SemanticJson::equal($effect->effect_data, self::json(['policy' => $a->policy_key]))) {
                throw new \DomainException('Confirmed contribution has incompatible canonical effects.');
            }
        }
    }

    private function orderedContexts(array $ids): \Illuminate\Support\Collection
    {
        $ids = array_values(array_unique($ids)); sort($ids, SORT_NUMERIC);
        $contexts = DB::table(self::CONTEXTS)->whereIn('id', $ids)->orderBy('id')->get();
        if ($contexts->count() !== count($ids)) {
            throw new \DomainException('Missing original funding context.');
        }
        return $contexts;
    }

    public function lock(int $id): object
    {
        // An actual write is required on SQLite: FOR UPDATE is ignored there.
        if (DB::connection()->getDriverName() === 'sqlite') {
            if (DB::table(self::ALLOCATIONS)->where('id', $id)->increment('version') !== 1) {
                throw new \DomainException('Missing allocation.');
            }
        }
        $a = DB::table(self::ALLOCATIONS)->where('id', $id)->lockForUpdate()->first();
        if (!$a) {
            throw new \DomainException('Missing allocation.');
        }
        return $a;
    }

    private function cas(object $a, array $values, bool $finalClaim = false): void
    {
        $q = DB::table(self::ALLOCATIONS)->where('id', $a->id)->where('version', $a->version);
        if ($finalClaim) {
            $q->whereNull('finalized_at');
        }
        if ($q->update($values + ['version' => (int) $a->version + 1, 'updated_at' => now()]) !== 1) {
            throw new \DomainException('Economic claim lost.');
        }
    }

    public static function json(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}