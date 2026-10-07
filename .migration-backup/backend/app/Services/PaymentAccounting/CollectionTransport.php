<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\Http;
use App\Services\PaymentService\Verification\PayPalVerification;

/** Existing authenticated provider contracts, now bound to one durable attempt. */
final class CollectionTransport
{
    public function dispatch(object $a, \App\Models\PaymentProcess $process, array $p, string $key): array
    {
        $b=$process->data; $amount=(int)$b['total_price']; $currency=strtoupper($b['currency']);
        $return=request()->getSchemeAndHttpHost()."/payment-success?$key=".$b['model_id'];
        $email=auth('sanctum')->user()?->email;
        try {
            if ($a->provider==='paystack') {
                $r=Http::withToken($p['paystack_sk'])->timeout(20)->post('https://api.paystack.co/transaction/initialize',[
                    'email'=>$email,'amount'=>$amount,'currency'=>$currency,'reference'=>$a->id,'callback_url'=>$return]);
                if ($r->successful() && $r->json('status')===true && $r->json('data.reference')===$a->id) {
                    return ['reference'=>$a->id,'url'=>$r->json('data.authorization_url')];
                }
            } elseif ($a->provider==='flutter-wave') {
                $r=Http::withToken($p['flw_sk'])->timeout(20)->post('https://api.flutterwave.com/v3/payments',[
                    'tx_ref'=>$a->id,'amount'=>ExactMoney::decimal($amount,2),'currency'=>$currency,
                    'redirect_url'=>$return,'customer'=>['email'=>$email],
                    'customizations'=>array_intersect_key($p,array_flip(['title','description','logo']))]);
                if ($r->successful() && $r->json('status')==='success' && $r->json('data.link')) {
                    return ['reference'=>$a->id,'url'=>$r->json('data.link'),'merchant_id'=>$p['flw_account_id']];
                }
            } elseif ($a->provider==='stripe') {
                if (!\App\Services\PaymentService\StripeService::supportsCurrency($currency)
                    || (\App\Helpers\EnvironmentPolicy::paymentMode()==='sandbox' && !str_starts_with($p['stripe_sk'],'sk_test_'))) {
                    throw new \DomainException('Stripe currency/environment is not supported.');
                }
                $account=Http::withBasicAuth($p['stripe_sk'],'')->timeout(20)->get('https://api.stripe.com/v1/account');
                if (!$account->successful() || !$account->json('id')) return [];
                $process->update(['data'=>array_merge($process->data,['merchant_id'=>$account->json('id')])]);
                $r=Http::withBasicAuth($p['stripe_sk'],'')->withHeaders(['Idempotency-Key'=>$a->id])
                    ->asForm()->timeout(20)->post('https://api.stripe.com/v1/checkout/sessions',[
                        'mode'=>'payment','client_reference_id'=>$a->id,'metadata[attempt_id]'=>$a->id,
                        'payment_intent_data[metadata][attempt_id]'=>$a->id,
                        'line_items[0][price_data][currency]'=>strtolower($currency),
                        'line_items[0][price_data][product_data][name]'=>'Payment',
                        'line_items[0][price_data][unit_amount]'=>$amount,'line_items[0][quantity]'=>1,
                        'success_url'=>$return.'&token={CHECKOUT_SESSION_ID}','cancel_url'=>$return.'&status=error']);
                if ($r->successful() && $r->json('id')) return ['reference'=>$r->json('id'),
                    'payment_id'=>$r->json('payment_intent'),'url'=>$r->json('url'),'merchant_id'=>$account->json('id')];
            } elseif ($a->provider==='paypal') {
                $c=PayPalVerification::credentials($p); $v=new PayPalVerification; $token=$v->token($c);
                $r=Http::withToken($token)->timeout(20)->withHeaders(['PayPal-Request-Id'=>$a->id])
                    ->post($c['url'].'/v2/checkout/orders',['intent'=>'CAPTURE','purchase_units'=>[[
                        'reference_id'=>$a->id,'custom_id'=>$a->id,'payee'=>['merchant_id'=>$c['merchant']],
                        'amount'=>['currency_code'=>$currency,'value'=>PayPalVerification::amount($amount,$currency)]]],
                        'payment_source'=>['paypal'=>['experience_context'=>['return_url'=>$return,'cancel_url'=>$return.'&status=error']]]]);
                if ($r->successful() && $r->json('id')) {
                    $url=collect($r->json('links',[]))->firstWhere('rel','payer-action')['href']??null;
                    return ['reference'=>$r->json('id'),'url'=>$url,'merchant_id'=>$c['merchant']];
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException|\RuntimeException $e) {
            // Never log credentials, request/response bodies or exception traces.
        }
        return []; // Every ambiguous initiation remains claimed UNKNOWN, no blind retry.
    }
}