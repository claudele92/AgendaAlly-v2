<?php
declare(strict_types=1);
namespace App\Services\PaymentService;

use App\Models\PaymentProcess;
use App\Models\Payment;
use App\Services\PaymentAccounting\AllocationWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** MTN protocol identity only. Existing canonical writers own money/finality. */
final class MtnAttempt
{
    public const RESERVED = 'RESERVED_NOT_DISPATCHED';
    public const UNKNOWN = 'DISPATCH_OUTCOME_UNKNOWN';
    public const ACCEPTED = 'ACCEPTED_PENDING';
    public const PENDING = 'PROVIDER_PENDING';
    public const SUCCESS = 'VERIFIED_SUCCESS';
    public const FAILURE = 'VERIFIED_FAILURE';

    public function reserve(array $before, int $paymentId, string $fingerprint): PaymentProcess
    {
        if (DB::transactionLevel()===0) throw new \DomainException('MTN reservation requires a local transaction.');
        $allocationIds=DB::table('payment_collection_contexts')->whereIn('id',$before['accounting_context_ids'] ?? [])
            ->orderBy('allocation_id')->pluck('allocation_id')->unique();
        foreach ($allocationIds as $id) (new AllocationWriter)->lock((int)$id);
        $old=PaymentProcess::where('mtn_funding_event_key',$before['accounting_funding_event'] ?? '')->first();
        if ($old) {
            $this->context($old);
            if (!hash_equals((string)$old->mtn_config_fingerprint,$fingerprint)
                || $old->model_type!==$before['model_type'] || (int)$old->model_id!==(int)$before['model_id']) {
                throw new \DomainException('Retained MTN attempt cannot be rebound.');
            }
            return $old;
        }
        $contexts = DB::table('payment_collection_contexts')->whereIn('id', $before['accounting_context_ids'] ?? [])
            ->orderBy('id')->get();
        if ($contexts->isEmpty() || $contexts->count() !== count($before['accounting_context_ids'])
            || !Str::isUuid($before['accounting_funding_event'] ?? '')) {
            throw new \DomainException('MTN needs original canonical funding.');
        }
        $reference = (string) Str::uuid();
        $process = PaymentProcess::create([
            'id'=>$reference,'user_id'=>auth('sanctum')->id(),
            'model_type'=>$before['model_type'],'model_id'=>$before['model_id'],
            'mtn_funding_event_key'=>$before['accounting_funding_event'],
            'mtn_anchor_context_id'=>$contexts->first()->id,'mtn_dispatch_state'=>self::RESERVED,
            'mtn_attempt_version'=>0,'mtn_config_fingerprint'=>$fingerprint,
            'data'=>array_merge($before, [
                'payment_id'=>$paymentId,'mtn_reference_id'=>$reference,'mtn_resolved'=>false,
                'requested_at'=>now()->toIso8601String(),'mtn_config_fingerprint'=>$fingerprint,
            ]),
        ]);
        (new AllocationWriter)->pending($contexts->pluck('id')->all(), $reference);
        DB::table('payment_collection_contexts')->whereIn('id',$contexts->pluck('id'))
            ->update(['payment_process_reference'=>$reference]);
        $this->context($process);
        return $process;
    }

    /** Read canonical authority, validating the entire original receipt group. */
    public function context(PaymentProcess $process): object
    {
        if (!$process->mtn_funding_event_key) {
            throw new \DomainException('Legacy MTN attempt lacks durable identity.');
        }
        $members = DB::table('payment_collection_contexts')->where('funding_event_key',$process->mtn_funding_event_key)
            ->orderBy('id')->get();
        $first = $members->first();
        $ids = array_map('intval', $process->data['accounting_context_ids'] ?? []); sort($ids);
        if (!$first || (int)$first->id !== (int)$process->mtn_anchor_context_id
            || $members->pluck('id')->map(fn($id)=>(int)$id)->all() !== $ids
            || (string)($process->data['accounting_funding_event'] ?? '') !== $process->mtn_funding_event_key
            || (string)($process->data['mtn_reference_id'] ?? '') !== $process->id
            || !hash_equals((string)$process->mtn_config_fingerprint,(string)($process->data['mtn_config_fingerprint'] ?? ''))) {
            throw new \DomainException('MTN canonical binding disagreement.');
        }
        if (\App\Services\PaymentAccounting\ExactMoney::sum($members->map(fn($c)=>(int)$c->amount)->all())
            !==(int)$first->receipt_total_amount) throw new \DomainException('MTN canonical receipt shares disagree.');
        foreach ($members as $c) {
            $a=DB::table('commerce_payment_allocations')->find($c->allocation_id);
            foreach (['payment_id','provider_tag','collection_mode','custody_type','credential_owner_type',
                'credential_owner_id','configuration_source','configuration_reference','configuration_revision',
                'currency_id','currency_code','money_scale','receipt_total_amount'] as $field) {
                if ((string)$c->$field !== (string)$first->$field) {
                    throw new \DomainException('MTN receipt group/configuration disagreement.');
                }
            }
            $type=$a?->origin_type==='cart' ? \App\Models\Cart::class : \App\Models\Booking::class;
            if (!$a || $c->provider_tag!=='mtn'
                || (int)$c->payment_id !== (int)($process->data['payment_id'] ?? 0)
                || !Payment::whereKey($c->payment_id)->where('tag','mtn')->exists()
                || (string)$c->provider_payment_reference !== $process->id
                || (string)$c->payment_process_reference !== $process->id
                || $type!==$process->model_type
                || ($a->origin_type==='cart' ? (int)$a->origin_id : (int)($process->data['booking_id'] ?? $process->model_id)) !== (int)$process->model_id
                || (int)$a->payer_user_id !== (int)$process->user_id
                || (int)$c->receipt_total_amount !== (int)($process->data['total_price'] ?? 0)
                || $c->currency_code !== ($process->data['currency'] ?? '')
                || (int)$c->money_scale!==2) {
                throw new \DomainException('MTN original amount/payable/owner disagreement.');
            }
        }
        return $first;
    }

    /** No caller may transmit while an outer transaction can still roll back. */
    public function committedBoundary(): void
    {
        if (DB::transactionLevel() !== 0) {
            throw new \DomainException('MTN external I/O requires committed identity; outer transaction forbidden.');
        }
    }

    public function claim(string $id): bool
    {
        $this->committedBoundary();
        return DB::transaction(function () use ($id): bool {
            $p=PaymentProcess::findOrFail($id);
            $this->context($p);
            return DB::table('payment_process')->where('id',$id)->where('mtn_dispatch_state',self::RESERVED)
                ->where('mtn_attempt_version',$p->mtn_attempt_version)->update([
                    'mtn_dispatch_state'=>self::UNKNOWN,'mtn_dispatch_claimed_at'=>now()->toIso8601String(),
                    'mtn_attempt_version'=>(int)$p->mtn_attempt_version+1,
                ])===1;
        }, 3);
    }

    public function transition(PaymentProcess $p, string $state): void
    {
        if (in_array($p->mtn_dispatch_state,[self::SUCCESS,self::FAILURE],true)) return;
        $changed=DB::table('payment_process')->where('id',$p->id)
            ->where('mtn_attempt_version',$p->mtn_attempt_version)->update([
                'mtn_dispatch_state'=>$state,'mtn_attempt_version'=>(int)$p->mtn_attempt_version+1,
            ]);
        if ($changed!==1) throw new \DomainException('MTN lifecycle claim lost; reconcile retained identity.');
    }
}