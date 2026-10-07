<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\PaymentService\Verification\PayPalVerification;

/** Frozen original credentials + authenticated full/partial refund evidence. */
final class RefundTransport
{
    public static function assertEnabled(object $op): void
    {
        if (!in_array($op->provider,['paystack','stripe','paypal'],true) || !$op->revision_id) {
            throw new \DomainException('Original provider refund contract/revision is not ready.');
        }
        if (config('app.env')!=='testing' || !\App\Helpers\EnvironmentPolicy::paymentProviderEnabled($op->provider)) {
            throw new \DomainException('Electronic refund activation is disabled.');
        }
        $a=DB::table('commerce_payment_allocations')->find($op->allocation_id);
        if ((int)$a->money_scale!==2) throw new \DomainException('Provider amount-scale contract is not ready.');
        if ($op->provider==='paypal') {
            try {PayPalVerification::amount((int)$op->amount_units,$a->currency_code);}
            catch(\RuntimeException $e) {throw new \DomainException('Original PayPal currency/amount precision is unsupported.');}
        }
    }

    public function execute(object $op, bool $read): array
    {
        self::assertEnabled($op);
        $p=(new MerchantRevisions)->payload($op->revision_id,$op->provider);
        $a=DB::table('commerce_payment_allocations')->find($op->allocation_id);
        if ((int)$a->money_scale!==2) throw new \DomainException('Provider amount-scale contract is not ready.');
        try {
            $ref=rawurlencode((string)$op->external_reference);
            if ($op->provider==='paystack') {
                $client=Http::withToken($p['paystack_sk'])->timeout(20);
                $r=$read ? $client->get("https://api.paystack.co/refund/$ref")
                    : $client->post('https://api.paystack.co/refund',['transaction'=>$op->original_payment_id,
                        'amount'=>(int)$op->amount_units,'currency'=>$a->currency_code,'merchant_note'=>$op->id]);
                $d=$r->json('data',[]);
                $transaction=is_array($d['transaction']??null)?($d['transaction']['id']??''):($d['transaction']??'');
                $reference=is_array($d['transaction']??null)?($d['transaction']['reference']??''):'';
                if (!$r->successful() || $r->json('status')!==true
                    || !in_array($op->original_payment_id,[(string)$transaction,(string)$reference],true)
                    || (string)($d['amount']??'')!==(string)$op->amount_units
                    || strtoupper($d['currency']??'')!==$a->currency_code || empty($d['id'])) return [];
                // Root status=true means accepted/queued, NEVER economic refund success.
                $state=match($d['status']??''){'processed'=>'SUCCESS','failed'=>'FAILURE',
                    'pending','processing','needs-attention'=>'PENDING',default=>'UNKNOWN'};
                return ['verified'=>true,'state'=>$state,'reference'=>(string)$d['id']];
            }
            if ($op->provider==='stripe') {
                if (!str_starts_with($op->original_payment_id,'pi_') && !str_starts_with($op->original_payment_id,'ch_')) return [];
                $client=Http::withBasicAuth($p['stripe_sk'],'')->timeout(20)->asForm()
                    ->withHeaders(['Idempotency-Key'=>$op->id]);
                $field=str_starts_with($op->original_payment_id,'pi_')?'payment_intent':'charge';
                $r=$read ? $client->get("https://api.stripe.com/v1/refunds/$ref")
                    : $client->post('https://api.stripe.com/v1/refunds',[$field=>$op->original_payment_id,
                        'amount'=>(int)$op->amount_units,'metadata[operation_id]'=>$op->id]);
                $d=$r->json();
                if (!$r->successful() || ($d[$field]??null)!==$op->original_payment_id
                    || (string)($d['amount']??'')!==(string)$op->amount_units
                    || strtoupper($d['currency']??'')!==$a->currency_code || empty($d['id'])) return [];
                return ['verified'=>true,'reference'=>$d['id'],'state'=>match($d['status']??''){
                    'succeeded'=>'SUCCESS','failed','canceled'=>'FAILURE','pending','requires_action'=>'PENDING',default=>'UNKNOWN'}];
            }
            $c=PayPalVerification::credentials($p); $token=(new PayPalVerification)->token($c);
            $client=Http::withToken($token)->timeout(20)->withHeaders(['PayPal-Request-Id'=>$op->id]);
            $r=$read ? $client->get($c['url']."/v2/payments/refunds/$ref")
                : $client->post($c['url'].'/v2/payments/captures/'.rawurlencode($op->original_payment_id).'/refund',
                    ['amount'=>['currency_code'=>$a->currency_code,'value'=>PayPalVerification::amount((int)$op->amount_units,$a->currency_code)]]);
            $d=$r->json();
            $parent=collect($d['links']??[])->firstWhere('rel','up')['href']??'';
            if (!$r->successful() || empty($d['id'])
                || PayPalVerification::minor((string)($d['amount']['value']??''))!==(int)$op->amount_units
                || ($d['amount']['currency_code']??'')!==$a->currency_code
                || ($read && !str_ends_with($parent,'/captures/'.$op->original_payment_id))) return [];
            return ['verified'=>true,'reference'=>$d['id'],'state'=>match($d['status']??''){
                'COMPLETED'=>'SUCCESS','FAILED','CANCELLED'=>'FAILURE','PENDING'=>'PENDING',default=>'UNKNOWN'}];
        } catch (\Illuminate\Http\Client\ConnectionException|\RuntimeException $e) {
            return []; // Preserve UNKNOWN/reservation, never leak credential-bearing traces.
        }
    }
}