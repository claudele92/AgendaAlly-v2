<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Durable reservations around, not replacements for, accepted append-only effects. */
final class FinancialOperations
{
    /**
     * Application POST boundary: a fresh owned RR read view, allocation mutex
     * first, then the unchanged reservation body. Never inherit an arbitrary
     * caller snapshot or silently switch that caller's isolation/connection.
     */
    public function reserveOwned(int $allocationId, string $kind, string $requestKey, int $amount,
        int $actorId, ?int $contextId = null): object
    {
        $connection = DB::connection();
        if ($connection->transactionLevel() !== 0 || $connection->getPdo()->inTransaction()) {
            throw new \DomainException('Financial reservation requires a fresh owned transaction.');
        }
        if ($connection->getDriverName() === 'mysql') {
            // Outside a transaction: this reads session metadata, not authority.
            $isolation = $connection->getPdo()->query('SELECT @@transaction_isolation')->fetchColumn();
            if ($isolation !== 'REPEATABLE-READ') {
                throw new \DomainException('Owned MySQL reservation requires REPEATABLE READ.');
            }
        }
        return $this->reserve($allocationId,$kind,$requestKey,$amount,$actorId,$contextId);
    }

    /** Lower-level unit for already-managed fresh parent-authority transactions. */
    public function reserve(int $allocationId, string $kind, string $requestKey, int $amount,
        int $actorId, ?int $contextId = null): object
    {
        if (!Str::isUuid($requestKey) || $amount<=0 || !in_array($kind,['refund','receivable','payout'],true)) {
            throw new \DomainException('Valid operation and exact positive native units are required.');
        }
        if ($kind!=='refund' && $contextId!==null) throw new \DomainException('Non-refund operation cannot select another contribution.');
        return DB::transaction(function () use ($allocationId,$kind,$requestKey,$amount,$actorId,$contextId) {
            $a=$this->lock($allocationId);
            $old=DB::table('payment_financial_operations')->where('allocation_id',$allocationId)
                ->where('kind',$kind)->where('request_key',$requestKey)->first();
            if ($old) {
                if ((int)$old->amount_units!==$amount || (int)$old->actor_id!==$actorId
                    || $old->context_id!=$contextId) throw new \DomainException('Retry cannot change original operation.');
                return $old;
            }
            $balances=(new AllocationBalances)->current($allocationId);
            if (!$balances['finalized']) throw new \DomainException('Only finalized canonical economics are operationally eligible.');
            $context=null; $revision=null; $paymentIdentity=null;
            if ($kind==='refund') {
                $context=DB::table('payment_collection_contexts')->where('id',$contextId)
                    ->where('allocation_id',$allocationId)->where('state','confirmed')->first();
                if (!$context || !in_array($context->collection_mode,['platform','vendor_direct'],true)
                    || !$context->provider_payment_reference) {
                    throw new \DomainException('Original verified electronic custody and payment identity are required.');
                }
                $attempt=DB::table('electronic_collection_attempts')->where('funding_event_key',$context->funding_event_key)->first();
                if ($attempt) {
                    if ($attempt->state!=='SUCCESS') throw new \DomainException('Original collection is not terminally verified.');
                    if (in_array($context->provider_tag,['stripe','paypal','flutter-wave'],true) && !$attempt->provider_payment_id) {
                        throw new \DomainException('Original captured provider payment identity is unavailable.');
                    }
                    $revision=$attempt->revision_id;
                    $paymentIdentity=$attempt->provider_payment_id ?? $attempt->provider_reference;
                } elseif (!in_array($context->provider_tag,['mtn','orange'],true)) {
                    throw new \DomainException('Legacy merchant evidence cannot authorize a refund.');
                } else $paymentIdentity=$context->provider_payment_reference;
                $returned=(int)DB::table('platform_fee_ledger_entries')->where('allocation_id',$allocationId)
                    ->where('collection_context_id',$contextId)->where('effect_kind','refund_principal')->sum('exact_amount');
                $capacity=(int)$context->amount-$returned-$this->reserved($allocationId,'refund',$contextId);
                if ($this->reserved($allocationId,'payout')>0) throw new \DomainException('Resolve reserved payout before refunding its funding.');
                if ($this->reserved($allocationId,'receivable')>0) throw new \DomainException('Resolve reserved receipts before reversing their receivable authority.');
            } else {
                $capacity=(int)$balances[$kind==='payout'?'vendor_payable':'commission_receivable']
                    -$this->reserved($allocationId,$kind);
                if ($kind==='payout' && $this->reserved($allocationId,'refund')>0) {
                    throw new \DomainException('Resolve reserved refunds before reserving Vendor payout.');
                }
            }
            if ($amount>$capacity) throw new \DomainException('Requested amount exceeds unreserved original authority.');
            $id=(string)Str::uuid();
            DB::table('payment_financial_operations')->insert([
                'id'=>$id,'kind'=>$kind,'allocation_id'=>$allocationId,'context_id'=>$contextId,
                'revision_id'=>$revision,'actor_id'=>$actorId,'request_key'=>$requestKey,'amount_units'=>$amount,
                'provider'=>$context?->provider_tag,'original_payment_id'=>$paymentIdentity,
                'state'=>'RESERVED','version'=>0,'created_at'=>now(),'updated_at'=>now(),
            ]);
            return DB::table('payment_financial_operations')->find($id);
        },3);
    }

    public function reserved(int $allocationId, string $kind, ?int $context = null): int
    {
        $q=DB::table('payment_financial_operations')->where('allocation_id',$allocationId)->where('kind',$kind)
            ->whereIn('state',['RESERVED','UNKNOWN','PENDING']);
        if ($context!==null) $q->where('context_id',$context);
        return (int)$q->sum('amount_units');
    }

    private function lock(int $id): object
    {
        $a=(new AllocationWriter)->lock($id);
        // Actual SQLite write serialization, plus InnoDB row lock. No money change.
        if (DB::table('commerce_payment_allocations')->where('id',$id)->where('version',$a->version)
            ->update(['version'=>DB::raw('version+1'),'updated_at'=>now()])!==1) {
            throw new \DomainException('Stale allocation; retry the same operation.');
        }
        return $a;
    }

    public function cancel(string $id): void
    {
        $this->assertNotManual($id);
        DB::transaction(function () use ($id): void {
            $op=DB::table('payment_financial_operations')->find($id);
            if (!$op) throw new \DomainException('Operation unavailable.');
            $this->lock((int)$op->allocation_id);
            if ($op->state==='CANCELED') return;
            if (DB::table('payment_financial_operations')->where('id',$id)->where('state','RESERVED')
                ->whereNull('claimed_at')->update(['state'=>'CANCELED','version'=>DB::raw('version+1'),'updated_at'=>now()])!==1) {
                throw new \DomainException('Dispatched/ambiguous authority cannot be released by cancellation.');
            }
        });
    }

    public function refund(string $id, bool $reconcile = false): object
    {
        $this->assertNotManual($id);
        $op=DB::table('payment_financial_operations')->find($id);
        if (!$op || $op->kind!=='refund') throw new \DomainException('Electronic refund operation required.');
        if (in_array($op->state,['SUCCESS','FAILURE','CANCELED'],true)) return $op;
        RefundTransport::assertEnabled($op);
        if (!$reconcile) {
            if (DB::connection()->transactionLevel()!==0) throw new \DomainException('External refund requires a committed independent claim.');
            if (DB::table('payment_financial_operations')->where('id',$id)->where('state','RESERVED')
                ->whereNull('claimed_at')->update(['state'=>'UNKNOWN','claimed_at'=>now(),
                    'version'=>DB::raw('version+1'),'updated_at'=>now()])!==1) {
                throw new \DomainException('Original refund is already claimed; reconcile, never redispatch.');
            }
            $op=DB::table('payment_financial_operations')->find($id);
        } elseif (!$op->external_reference) {
            throw new \DomainException('UNKNOWN without provider refund identity requires provider-assisted recovery; no blind retry.');
        }
        $result=(new RefundTransport)->execute($op,$reconcile);
        return $this->applyRefundResult($id,$result);
    }

    private function assertNotManual(string $id): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('manual_financial_workflows')
            && DB::table('manual_financial_workflows')->where('operation_id',$id)->exists()) {
            throw new \DomainException('Manual workflow authority must use its scoped claim/evidence/transition boundary.');
        }
    }

    /** Internal adapter result only. No HTTP endpoint accepts financial success. */
    private function applyRefundResult(string $id, array $r): object
    {
        return DB::transaction(function () use ($id,$r) {
            $op=DB::table('payment_financial_operations')->find($id);
            $a=$this->lock((int)$op->allocation_id);
            $op=DB::table('payment_financial_operations')->find($id);
            if (in_array($op->state,['SUCCESS','FAILURE','CANCELED'],true)) return $op;
            if (($r['verified']??false)!==true) return $op;
            if (!empty($r['reference']) && $op->external_reference && $op->external_reference!==$r['reference']) {
                throw new \DomainException('Original refund reference cannot change.');
            }
            $state=$r['state'];
            if ($state==='SUCCESS') {
                $context=DB::table('payment_collection_contexts')->find($op->context_id);
                $before=(new AllocationBalances)->current((int)$a->id);
                $effects=DB::table('platform_fee_ledger_entries')->where('allocation_id',$a->id)->get();
                $oldContext=(int)$effects->where('effect_kind','refund_principal')
                    ->where('collection_context_id',$context->id)->sum('exact_amount');
                $oldCommission=(int)$effects->where('effect_kind','commission_reversal')->sum('exact_amount');
                $oldAdjustment=(int)$effects->where('effect_kind','adjustment_reversal')
                    ->where('collection_context_id',$context->id)->sum('exact_amount');
                $commission=ExactMoney::portion((int)$a->commission_amount,
                    $before['refunded']+(int)$op->amount_units,(int)$a->gross_amount)-$oldCommission;
                $adjustment=ExactMoney::portion((int)$context->original_adjustment_share,
                    $oldContext+(int)$op->amount_units,(int)$context->amount)-$oldAdjustment;
                $proof=['authority'=>'original_verified_provider_refund','operation'=>$op->id,
                    'provider'=>$op->provider,'provider_reference'=>$r['reference'],'revision'=>$op->revision_id];
                // Existing accepted equations decide liability; no new payable calculation/credit.
                $group=[['kind'=>'refund_principal','context_id'=>(int)$context->id,'amount'=>(int)$op->amount_units,'proof'=>$proof]];
                if ($commission>0) $group[]=['kind'=>'commission_reversal','amount'=>$commission,'proof'=>$proof];
                if ($adjustment>0) $group[]=['kind'=>'adjustment_reversal','context_id'=>(int)$context->id,'amount'=>$adjustment,'proof'=>$proof];
                (new AccountingEffects)->append((int)$a->id,$op->id,$group);
            }
            DB::table('payment_financial_operations')->where('id',$id)->update([
                'state'=>$state,'external_reference'=>$r['reference']??$op->external_reference,
                'version'=>DB::raw('version+1'),'updated_at'=>now(),
                'completed_at'=>in_array($state,['SUCCESS','FAILURE'],true)?now():null,
            ]);
            return DB::table('payment_financial_operations')->find($id);
        },3);
    }

    public function receipt(string $id, array $evidence): object
    {
        if (($evidence['source']??null)!=='cash_receipt' || empty($evidence['receipt_reference'])
            || !preg_match('/^[a-f0-9]{64}$/D',$evidence['document_sha256']??'')
            || strlen(trim($evidence['retained_evidence']??''))<20 || empty($evidence['received_at'])) {
            throw new \DomainException('Legitimate retained cash receipt, provenance and document digest are required.');
        }
        try {
            $evidence['received_at']=\Carbon\CarbonImmutable::parse($evidence['received_at'])->utc()->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw new \DomainException('A valid retained receipt timestamp is required.');
        }
        return DB::transaction(function () use ($id,$evidence) {
            $op=DB::table('payment_financial_operations')->find($id);
            if (!$op || $op->kind!=='receivable') throw new \DomainException('Commission receivable operation required.');
            $this->lock((int)$op->allocation_id);
            if ($op->state==='SUCCESS') {
                $old=DB::table('payment_receipt_evidence')->where('operation_id',$id)->first();
                foreach (['source','receipt_reference','document_sha256','retained_evidence','received_at'] as $field) {
                    if ((string)$old->$field!==(string)$evidence[$field]) throw new \DomainException('Receipt evidence cannot change on replay.');
                }
                return $op;
            }
            if ($op->state!=='RESERVED') throw new \DomainException('Receivable authority is not open.');
            DB::table('payment_receipt_evidence')->insert(array_intersect_key($evidence,array_flip(
                ['source','receipt_reference','document_sha256','retained_evidence','received_at']))+
                ['id'=>(string)Str::uuid(),'operation_id'=>$id,'created_at'=>now()]);
            (new AccountingEffects)->append((int)$op->allocation_id,$id,[
                ['kind'=>'receivable_collection','amount'=>(int)$op->amount_units,
                    'proof'=>['authority'=>'authorized_retained_cash_receipt','operation'=>$id,
                        'recorded_by_id'=>(int)auth('sanctum')->id(),
                        'receipt_reference'=>$evidence['receipt_reference'],'document_sha256'=>$evidence['document_sha256']]],
            ]);
            DB::table('payment_financial_operations')->where('id',$id)->update([
                'state'=>'SUCCESS','version'=>DB::raw('version+1'),'completed_at'=>now(),'updated_at'=>now()]);
            return DB::table('payment_financial_operations')->find($id);
        },3);
    }
}