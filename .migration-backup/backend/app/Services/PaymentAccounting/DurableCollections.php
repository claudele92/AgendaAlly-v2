<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Services\PaymentService\BaseService;
use App\Services\PaymentService\Verification\PayPalVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/** Non-MTN, server-owned, original-revision attempts. No automatic redispatch. */
final class DurableCollections
{
    public function reserve(array $before, string $provider): object
    {
        return DB::transaction(function () use ($before,$provider) {
            $ids=$before['accounting_context_ids'] ?? [];
            $contexts=DB::table('payment_collection_contexts')->whereIn('id',$ids)->orderBy('id')->get();
            if ($contexts->isEmpty() || $contexts->count()!==count($ids) || $provider==='mtn') {
                throw new \DomainException('Original non-MTN canonical contexts are required.');
            }
            $c=$contexts->first();
            foreach ($contexts as $context) if ($context->funding_event_key!==$c->funding_event_key
                || $context->configuration_revision!==$c->configuration_revision
                || $context->provider_tag!==$provider || $context->collection_mode!=='platform') {
                throw new \DomainException('A generic attempt cannot combine independent merchants.');
            }
            (new AllocationWriter)->lock((int)$c->allocation_id);
            $existing=DB::table('electronic_collection_attempts')->where('funding_event_key',$c->funding_event_key)->first();
            if ($existing) return $existing;
            $id=(string)Str::uuid();
            DB::table('electronic_collection_attempts')->insert([
                'id'=>$id,'funding_event_key'=>$c->funding_event_key,'anchor_context_id'=>$c->id,
                'revision_id'=>$c->configuration_revision,'provider'=>$provider,'state'=>'PRE_DISPATCH',
                'provider_reference'=>in_array($provider,['paystack','flutter-wave'],true)?$id:null,
                'process_reference'=>$id,'version'=>0,'created_at'=>now(),'updated_at'=>now(),
            ]);
            PaymentProcess::create(['id'=>$id,'user_id'=>auth('sanctum')->id(),
                'model_type'=>$before['model_type'],'model_id'=>$before['model_id'],
                'data'=>array_merge($before,['generic_attempt_id'=>$id,'merchant_revision_id'=>$c->configuration_revision,
                    'payment_reference'=>$id,'payment_id'=>$c->payment_id,
                    'merchant_id'=>$provider==='paypal'
                        ? PayPalVerification::credentials((new MerchantRevisions)->payload($c->configuration_revision,$provider))['merchant']
                        : null])]);
            return DB::table('electronic_collection_attempts')->find($id);
        },3);
    }

    public function claim(string $id): bool
    {
        return DB::table('electronic_collection_attempts')->where('id',$id)->where('state','PRE_DISPATCH')
            ->whereNull('claimed_at')->update(['state'=>'UNKNOWN','claimed_at'=>now(),
                'version'=>DB::raw('version+1'),'updated_at'=>now()])===1;
    }

    public static function process(string $reference): ?PaymentProcess
    {
        $process=PaymentProcess::find($reference);
        if ($process || !MerchantRevisions::installed()) return $process;
        $a=DB::table('electronic_collection_attempts')->where('provider_reference',$reference)
            ->orWhere('provider_payment_id',$reference)->first();
        return $a ? PaymentProcess::find($a->process_reference) : null;
    }

    public function initiate(BaseService $base, array $data, string $provider): PaymentProcess
    {
        if (DB::connection()->transactionLevel()!==0) throw new \DomainException('External dispatch requires a committed independent attempt.');
        // This increment grants no transport activation, even for configured keys.
        if (config('app.env')!=='testing' || !\App\Helpers\EnvironmentPolicy::paymentProviderEnabled($provider)) {
            throw new \DomainException('Electronic collection activation is disabled.');
        }
        $payment=Payment::where('tag',$provider)->firstOrFail();
        $profile=\App\Models\PaymentPayload::findOrFail($payment->id);
        if (!$profile->revision_id) throw new \DomainException('Save an original merchant revision first.');
        $payload=(new MerchantRevisions)->payload($profile->revision_id,$provider);
        [$key,$before]=$base->getPayload($data,$payload,(int)$payment->id);
        $attempt=$this->reserve($before,$provider);
        $payload=(new MerchantRevisions)->payload($attempt->revision_id,$provider);
        $process=PaymentProcess::findOrFail($attempt->process_reference);
        if (!$this->claim($attempt->id)) return $process;
        // Claim committed before account/authentication/payment transport.
        $result=(new CollectionTransport)->dispatch($attempt,$process,$payload,$key);
        $reference=$result['reference'] ?? null;
        if (!$reference) return $process; // UNKNOWN retains reservation and original identity.
        DB::transaction(function () use ($attempt,$process,$result,$reference): void {
            $changed=DB::table('electronic_collection_attempts')->where('id',$attempt->id)->where('state','UNKNOWN')->update([
                'provider_reference'=>$reference,'provider_payment_id'=>$result['payment_id'] ?? null,
                'state'=>'PENDING','version'=>DB::raw('version+1'),'updated_at'=>now(),
            ]);
            if ($changed!==1) return; // An authenticated callback may have won before the HTTP response.
            $process->refresh();
            $process->update(['data'=>array_merge($process->data,['url'=>$result['url'] ?? null,
                'provider_reference'=>$reference,'merchant_id'=>$result['merchant_id'] ?? null])]);
        });
        return $process->fresh();
    }

    /** Called only after native authenticated verification/canonical confirmation. */
    public function finalized(PaymentProcess $process, string $status, array $verified): void
    {
        if (empty($process->data['generic_attempt_id'])) return;
        $a=DB::table('electronic_collection_attempts')->find($process->data['generic_attempt_id']);
        if (!$a || ($verified['authenticated']??false)!==true || ($verified['merchant_verified']??false)!==true) {
            throw new \DomainException('Missing original attempt verification.');
        }
        if (!in_array($status,[\App\Models\Transaction::STATUS_PAID,\App\Models\Transaction::STATUS_REJECTED,
            \App\Models\Transaction::STATUS_CANCELED],true)) return;
        DB::table('electronic_collection_attempts')->where('id',$a->id)->whereIn('state',['UNKNOWN','PENDING'])
            ->update(['state'=>$status===\App\Models\Transaction::STATUS_PAID?'SUCCESS':'FAILURE',
                'provider_payment_id'=>$verified['provider_payment_id'] ?? $a->provider_payment_id,
                'provider_reference'=>$a->provider_reference ?? ($verified['provider_collection_reference']??null),
                'version'=>DB::raw('version+1'),'updated_at'=>now()]);
    }
}