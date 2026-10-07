<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\{Payment,PaymentPayload,PaymentProcess,Order};
use App\Services\PaymentAccounting\{CompletionSchema,MerchantRevisions,AllocationBalances};
use Illuminate\Support\Facades\{DB,Http,Schema};

final class PaymentGenericNativeJourneyTest extends PaymentNativeCheckoutFixture
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!Schema::hasTable('payment_merchant_revisions')) (new CompletionSchema)->up();
        config(['development.payment_mode'=>'sandbox','app.url'=>'https://synthetic.invalid',
            'payment_eligibility.currencies.paystack'=>['USD'],'payment_eligibility.currencies.flutter-wave'=>['USD']]);
        config(['payment_eligibility.currencies.stripe'=>['USD'],'payment_eligibility.currencies.paypal'=>['USD']]);
        DB::table('currencies')->where('id',1)->update(['title'=>'USD']);
    }

    public static function providers(): array
    {
        $cases=[];foreach (['paystack','flutter-wave','stripe','paypal'] as $p) foreach (['cart','booking'] as $t) $cases[]=[$p,$t];
        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providers')]
    public function test_native_initiation_original_revision_verification_rotation_and_replay(string $tag,string $type): void
    {
        $this->row('payments',['id'=>4,'tag'=>$tag,'active'=>1]);
        $this->row('country_payments',['country_id'=>1,'payment_id'=>4]);
        $payload=['currency'=>'USD','configured_environment'=>'sandbox','paystack_sk'=>'synthetic-old',
            'paystack_pk'=>'synthetic-public','flw_sk'=>'synthetic-old','flw_pk'=>'synthetic-public',
            'flw_webhook_secret_hash'=>'synthetic-webhook','flw_account_id'=>'synthetic-merchant',
            'stripe_sk'=>'sk_test_synthetic','stripe_pk'=>'pk_test_synthetic','stripe_webhook_secret'=>'synthetic-webhook',
            'paypal_mode'=>'sandbox','paypal_currency'=>'USD','paypal_sandbox_client_id'=>'synthetic-client',
            'paypal_sandbox_client_secret'=>'synthetic-old','paypal_webhook_id'=>'synthetic-webhook','paypal_merchant_id'=>'synthetic-merchant'];
        $p=PaymentPayload::create(['payment_id'=>4,'payload'=>$payload]);(new MerchantRevisions)->append($p);
        $revision=$p->revision_id;
        if ($type==='cart') {$this->cart();DB::table('products')->update(['digital'=>1]);}
        else {$model=$this->booking(['collect_via_platform'=>1]);(new \App\Services\PaymentAccounting\NativeQuoteFactory)->commitNew($model);
            $model->createTransaction(['price'=>100,'payment_sys_id'=>4,'user_id'=>2,'status'=>'progress']);}
        $service=match($tag){
            'paystack'=>new \App\Services\PaymentService\PayStackService,
            'flutter-wave'=>new \App\Services\PaymentService\FlutterWaveService,
            'stripe'=>new \App\Services\PaymentService\StripeService,
            'paypal'=>new \App\Services\PaymentService\PayPalService,
        };
        Http::fake([
            '*/transaction/initialize'=>fn($r)=>Http::response(['status'=>true,'data'=>['reference'=>$r['reference'],'authorization_url'=>'https://synthetic.invalid/checkout']]),
            '*/v3/payments'=>Http::response(['status'=>'success','data'=>['link'=>'https://synthetic.invalid/checkout']]),
            '*/v1/account'=>Http::response(['id'=>'synthetic-merchant']),
            '*/v1/checkout/sessions'=>Http::response(['id'=>'cs_synthetic','payment_intent'=>'pi_synthetic','url'=>'https://synthetic.invalid/checkout']),
            '*/v1/oauth2/token'=>Http::response(['access_token'=>'synthetic-token']),
            '*/v2/checkout/orders'=>Http::response(['id'=>'PP-SYNTHETIC','links'=>[['rel'=>'payer-action','href'=>'https://synthetic.invalid/checkout']]]),
        ]);
        $process=$service->processTransaction([$type==='cart'?'cart_id':'booking_id'=>1,'user_id'=>2,
            'currency_id'=>1,'payment_id'=>4,'delivery_type'=>Order::DIGITAL]);
        self::assertSame('PENDING',DB::table('electronic_collection_attempts')->value('state'));
        self::assertSame($revision,DB::table('electronic_collection_attempts')->value('revision_id'));
        self::assertSame(0,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        $payload['paystack_sk']='synthetic-new';$payload['flw_sk']='synthetic-new';
        $payload['stripe_webhook_secret']='synthetic-new';$payload['paypal_sandbox_client_secret']='synthetic-new';
        $p->payload=$payload;$p->save();(new MerchantRevisions)->append($p);
        $controllerClass=match($tag){
            'paystack'=>\App\Http\Controllers\API\v1\Dashboard\Payment\PayStackController::class,
            'flutter-wave'=>\App\Http\Controllers\API\v1\Dashboard\Payment\FlutterWaveController::class,
            'stripe'=>\App\Http\Controllers\API\v1\Dashboard\Payment\StripeController::class,
            default=>null,
        };
        if ($controllerClass) {
            $controller=(new \ReflectionClass($controllerClass))->newInstanceWithoutConstructor();
            (new \ReflectionProperty($controller,'service'))->setValue($controller,$service);
        }
        $id=$process->id;
        if ($tag==='paystack') {
            $body=['event'=>'charge.success','data'=>['id'=>91,'reference'=>$id,'amount'=>10000,'currency'=>'USD']];
            $r=$this->request($body,['x-paystack-signature'=>hash_hmac('sha512',json_encode($body),'synthetic-old')]);
            $verify=fn()=>$controller->paymentWebHook($r);
        } elseif ($tag==='flutter-wave') {
            Http::fake(['*/v3/transactions/91/verify'=>Http::response(['status'=>'success','data'=>[
                'id'=>91,'status'=>'successful','account_id'=>'synthetic-merchant','tx_ref'=>$id,'currency'=>'USD','amount'=>'100.00']])]);
            $r=$this->request(['data'=>['id'=>91,'tx_ref'=>$id]],['verif-hash'=>'synthetic-webhook']);
            $verify=fn()=>$controller->paymentWebHook($r);
        } elseif ($tag==='stripe') {
            Http::fake(['*/v1/checkout/sessions/cs_synthetic'=>Http::response(['id'=>'cs_synthetic',
                'payment_intent'=>'pi_synthetic','client_reference_id'=>$id,'payment_status'=>'paid','amount_total'=>10000,'currency'=>'usd'])]);
            $body=['type'=>'checkout.session.completed','data'=>['object'=>['id'=>'cs_synthetic',
                'payment_intent'=>'pi_synthetic','client_reference_id'=>$id,'metadata'=>['attempt_id'=>$id]]]];
            $stamp=time();$sig=hash_hmac('sha256',$stamp.'.'.json_encode($body),'synthetic-webhook');
            $r=$this->request($body,['stripe-signature'=>"t=$stamp,v1=$sig"]);
            $verify=fn()=>$controller->paymentWebHook($r);
        } else {
            $order=['id'=>'PP-SYNTHETIC','status'=>'COMPLETED','purchase_units'=>[[
                'reference_id'=>$id,'payee'=>['merchant_id'=>'synthetic-merchant'],'payments'=>['captures'=>[[
                    'id'=>'PP-CAPTURE','status'=>'COMPLETED','amount'=>['currency_code'=>'USD','value'=>'100.00']]]]]]];
            Http::fake(['*/v1/notifications/verify-webhook-signature'=>Http::response(['verification_status'=>'SUCCESS']),
                '*/v2/checkout/orders/PP-SYNTHETIC'=>Http::response($order)]);
            $r=$this->request(['event_type'=>'PAYMENT.CAPTURE.COMPLETED','resource'=>[
                'supplementary_data'=>['related_ids'=>['order_id'=>'PP-SYNTHETIC']]]],
                ['paypal-auth-algo'=>'SHA256withRSA','paypal-cert-url'=>'https://api.paypal.com/synthetic-cert',
                    'paypal-transmission-id'=>'synthetic','paypal-transmission-sig'=>'synthetic','paypal-transmission-time'=>'synthetic']);
            $verify=fn()=>$service->verifiedWebhook($r);
        }
        $result=$verify();self::assertTrue($result['status'],json_encode([$result,$this->diagnosticLog->messages]));
        self::assertSame('SUCCESS',DB::table('electronic_collection_attempts')->value('state'));
        $allocation=DB::table('commerce_payment_allocations')->first();
        self::assertSame(9000,(new AllocationBalances)->current((int)$allocation->id)['vendor_payable']);
        self::assertTrue($verify()['status']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        if ($type==='cart') {self::assertSame(1,Order::count());self::assertSame(49,(int)DB::table('stocks')->value('quantity'));}
    }

    private function request(array $body,array $headers): \Illuminate\Http\Request
    {
        $server=['CONTENT_TYPE'=>'application/json'];foreach($headers as $k=>$v)$server['HTTP_'.strtoupper(str_replace('-','_',$k))]=$v;
        return \Illuminate\Http\Request::create('/synthetic-callback','POST',[],[],[],$server,json_encode($body));
    }
}