<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use App\Models\{PaymentProcess,Transaction};
use App\Services\PaymentService\{BaseService,Verification\PayPalVerification,Verification\DecimalAmount};
use Illuminate\Support\Facades\{DB,Http};

final class CollectionReconciliation
{
    public function reconcile(string $id): array
    {
        $a=DB::table('electronic_collection_attempts')->find($id);
        if (!$a) throw new \DomainException('Original collection attempt unavailable.');
        if (in_array($a->state,['SUCCESS','FAILURE'],true)) return ['status'=>true,'state'=>$a->state,'message'=>'Already terminal'];
        if ($a->state==='PRE_DISPATCH') throw new \DomainException('Collection has never been dispatched.');
        if (config('app.env')!=='testing' || !\App\Helpers\EnvironmentPolicy::paymentProviderEnabled($a->provider)) {
            throw new \DomainException('Electronic reconciliation activation is disabled.');
        }
        $process=PaymentProcess::findOrFail($a->process_reference);
        $p=(new MerchantRevisions)->payload($a->revision_id,$a->provider);
        $b=$process->data;
        $proof=['authenticated'=>true,'merchant_verified'=>true,'reference'=>$process->id,
            'payment_id'=>$b['payment_id'],'model_type'=>$process->model_type,'model_id'=>$process->model_id];
        $ref=rawurlencode((string)$a->provider_reference);
        if (!$ref) throw new \DomainException('Original external identity unavailable; retain UNKNOWN for authenticated callback/provider recovery.');
        $state=null;
        if ($a->provider==='paystack') {
            $r=Http::withToken($p['paystack_sk'])->timeout(20)->get("https://api.paystack.co/transaction/verify/$ref");
            $d=$r->json('data',[]);
            if (!$r->successful() || $r->json('status')!==true || ($d['reference']??null)!==$a->provider_reference) {
                return ['status'=>false,'state'=>$a->state,'message'=>'Original provider evidence unavailable'];
            }
            $proof+=['amount_minor'=>$d['amount']??null,'currency'=>$d['currency']??null,
                'provider_payment_id'=>isset($d['id'])?(string)$d['id']:null];
            $state=match($d['status']??''){'success'=>Transaction::STATUS_PAID,'failed','abandoned'=>Transaction::STATUS_REJECTED,default=>null};
        } elseif ($a->provider==='stripe') {
            $client=Http::withBasicAuth($p['stripe_sk'],'')->timeout(20);
            $account=$client->get('https://api.stripe.com/v1/account');
            $r=$client->get("https://api.stripe.com/v1/checkout/sessions/$ref");
            $d=$r->json();
            if (!$account->successful() || !$r->successful() || ($d['id']??null)!==$a->provider_reference
                || ($d['client_reference_id']??null)!==$process->id || $account->json('id')!==($b['merchant_id']??null)) {
                return ['status'=>false,'state'=>$a->state,'message'=>'Original merchant/checkout evidence unavailable'];
            }
            $proof+=['amount_minor'=>$d['amount_total']??null,'currency'=>$d['currency']??null,
                'provider_collection_reference'=>$d['id'],'provider_payment_id'=>$d['payment_intent']??null];
            if (($d['payment_status']??null)==='paid') $state=Transaction::STATUS_PAID;
        } elseif ($a->provider==='paypal') {
            $c=PayPalVerification::credentials($p);$v=new PayPalVerification;$token=$v->token($c);
            $d=$v->order($a->provider_reference,$c,$token);
            $verified=PayPalVerification::proof($d,$a->provider_reference,$b,$c['merchant']);
            if ($verified) {$proof=$verified;$proof['reference']=$process->id;$state=Transaction::STATUS_PAID;}
        } elseif ($a->provider==='flutter-wave') {
            if (!$a->provider_payment_id) throw new \DomainException('Original Flutterwave transaction ID required; recover through authenticated callback, not a new charge.');
            $r=Http::withToken($p['flw_sk'])->timeout(20)->get('https://api.flutterwave.com/v3/transactions/'.
                rawurlencode($a->provider_payment_id).'/verify');
            $d=$r->json('data',[]);
            if (!$r->successful() || $r->json('status')!=='success' || ($d['tx_ref']??null)!==$a->provider_reference
                || (string)($d['account_id']??'')!==(string)$p['flw_account_id']) return ['status'=>false,'state'=>$a->state];
            $proof+=['amount_minor'=>DecimalAmount::minor($d['amount']??null),'currency'=>$d['currency']??null,
                'provider_payment_id'=>(string)($d['id']??'')];
            $state=match($d['status']??''){'successful'=>Transaction::STATUS_PAID,'failed'=>Transaction::STATUS_REJECTED,default=>null};
        }
        if (!$state) return ['status'=>false,'state'=>$a->state,'message'=>'Provider has not authoritatively settled; original claim retained'];
        return (new BaseService)->afterHook($process->id,$state,null,$proof);
    }
}