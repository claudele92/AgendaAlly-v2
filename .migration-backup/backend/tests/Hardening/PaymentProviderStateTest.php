<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Payment;
use App\Models\Shop;
use App\Models\ShopPayment;
use App\Services\PaymentEligibility\PaymentEligibilityService;
use App\Services\ShopServices\ShopPaymentService;
use Illuminate\Support\Facades\DB;

/** Native empty schema, synthetic configuration, stray HTTP requests forbidden. */
final class PaymentProviderStateTest extends PaymentNativeCheckoutFixture
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('payment_eligibility', require dirname(__DIR__, 2).'/config/payment_eligibility.php');
        $this->app['config']->set('development.urls.api', 'https://synthetic.example');
        $this->app['config']->set('development.payments.mode', 'test');
        $this->app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('s',32)));
        $this->app['config']->set('app.cipher', 'AES-256-CBC');
        $this->app->register(\Illuminate\Encryption\EncryptionServiceProvider::class);
        foreach ([4=>'orange',5=>'flutter-wave',6=>'paystack'] as $id=>$tag) {
            $this->row('payments',['id'=>$id,'tag'=>$tag,'active'=>1]);
        }
        foreach ([3,4,5,6] as $id) $this->row('country_payments',['country_id'=>1,'payment_id'=>$id,'active'=>1]);
    }

    private function context(string $mode = 'platform', int $shop = 1): array
    {
        return ['valid'=>true,'reason'=>null,'shop_ids'=>[$shop],'country_ids'=>[1],
            'transaction_type'=>'product','transaction_currency'=>'XAF','transaction_currency_id'=>1,
            'collection_mode'=>$mode,'quote_supported'=>true];
    }

    private function decision(int $id = 3, string $mode = 'platform', int $shop = 1): array
    {
        return (new PaymentEligibilityService)->decision(Payment::findOrFail($id), $this->context($mode,$shop));
    }

    private function configure(string $mode = 'platform', int $shop = 1, array $changes = []): void
    {
        $this->row($mode === 'platform' ? 'platform_payment_configs' : 'shop_payments',
            array_replace(['payment_id'=>3,'country_id'=>1,'shop_id'=>$shop,'status'=>1,
                'api_key'=>'synthetic-key','api_user'=>'synthetic-user','subscription_key'=>'synthetic-sub',
                'target_environment'=>'mtncameroon','currency'=>'XAF','base_url'=>'https://synthetic-mtn.example'],$changes));
    }

    public function test_capable_without_configuration_is_not_unsupported(): void
    {
        $s = $this->decision();
        self::assertSame('SUPPORTED',$s['capability_state']);
        self::assertTrue($s['currency_supported']);
        self::assertSame('NOT_CONFIGURED',$s['configuration_state']);
        self::assertSame('NOT_READY',$s['runtime_state']);
        self::assertFalse($s['checkout_available']);
        self::assertNotContains('currency_unsupported',$s['reasons']);
        self::assertSame(0,DB::table('commerce_payment_allocations')->count());
    }

    public function test_incomplete_configuration_and_missing_callback_are_distinct(): void
    {
        $this->configure(changes:['api_key'=>null]);
        $s=$this->decision();
        self::assertSame('CONFIGURATION_INCOMPLETE',$s['configuration_state']);
        self::assertFalse($s['runtime_ready']);
        self::assertContains('api_key',$s['runtime_reasons']);
        DB::table('platform_payment_configs')->update(['api_key'=>'synthetic-key']);
        $this->app['config']->set('development.urls.api',null);
        $s=$this->decision();
        self::assertSame('CONFIGURED',$s['configuration_state']);
        self::assertSame('SUPPORTED',$s['capability_state']);
        self::assertContains('callback_url_not_configured',$s['runtime_reasons']);
    }

    public function test_runtime_ready_does_not_activate_checkout(): void
    {
        $this->configure();
        $this->app['config']->set('app.env','local');
        $this->app['config']->set('development.payments.mode','disabled');
        $s=$this->decision();
        self::assertTrue($s['runtime_ready']);
        self::assertFalse($s['activation_allowed']);
        self::assertFalse($s['eligible']);
        self::assertSame('CHECKOUT_UNAVAILABLE',$s['checkout_state']);
        $this->app['config']->set('development.payments.mode','sandbox');
        $this->app['config']->set('development.payments.sandbox_providers',['mtn']);
        self::assertFalse($this->decision()['checkout_available']);
    }

    public function test_configuration_owners_never_fall_back_to_each_other(): void
    {
        $this->configure();
        self::assertTrue($this->decision()['configured']);
        self::assertFalse($this->decision(3,'vendor_direct')['configured']);
        DB::table('platform_payment_configs')->delete();
        $this->configure('vendor_direct');
        self::assertTrue($this->decision(3,'vendor_direct')['configured']);
        self::assertFalse($this->decision()['configured']);
        self::assertFalse($this->decision(3,'vendor_direct',2)['configured']);
    }

    public function test_credentials_cannot_change_capability_and_wrong_merchant_currency_is_not_unsupported(): void
    {
        $this->configure(changes:['currency'=>'USD']);
        $s=$this->decision();
        self::assertSame('SUPPORTED',$s['capability_state']);
        self::assertTrue($s['configured']);
        self::assertContains('collector_currency_mismatch',$s['runtime_reasons']);
        $context=$this->context();
        $context['transaction_currency']='ZZZ';
        $s=(new PaymentEligibilityService)->decision(Payment::find(3),$context);
        self::assertSame('UNSUPPORTED',$s['capability_state']);
        self::assertFalse($s['checkout_available']);
    }

    public function test_orange_missing_verifier_and_unknown_currency_are_not_missing_configuration(): void
    {
        $this->row('platform_payment_configs',['payment_id'=>4,'country_id'=>1,'status'=>1,
            'client_id'=>'synthetic-id','merchant_key'=>'synthetic-secret','currency'=>'XAF']);
        $s=$this->decision(4);
        self::assertSame('UNKNOWN',$s['capability_state']);
        self::assertSame('CONFIGURED',$s['configuration_state']);
        self::assertContains('authoritative_verifier_missing',$s['runtime_reasons']);
        self::assertNotContains('currency_unsupported',$s['reasons']);
        self::assertFalse($s['checkout_available']);
        $this->app['config']->set('payment_eligibility.currencies.orange',['XAF']);
        $declared=$this->decision(4);
        self::assertSame('SUPPORTED',$declared['capability_state']);
        self::assertSame('CONFIGURED',$declared['configuration_state']);
        self::assertFalse($declared['runtime_ready']);
        self::assertContains('authoritative_verifier_missing',$declared['runtime_reasons']);
    }

    public function test_platform_only_providers_remain_unknown_for_xaf_and_not_runtime_ready(): void
    {
        foreach ([5=>['flw_sk'=>'synthetic-key','flw_account_id'=>'synthetic-account','flw_webhook_secret_hash'=>'synthetic-hook'],
            6=>['paystack_sk'=>'synthetic-key']] as $id=>$payload) {
            $this->row('payment_payloads',['payment_id'=>$id,'payload'=>json_encode($payload)]);
            $s=$this->decision($id);
            self::assertSame('UNKNOWN',$s['capability_state']);
            self::assertTrue($s['configured']);
            self::assertFalse($s['runtime_ready']);
            self::assertContains('canonical_configuration_revision_unavailable',$s['runtime_reasons']);
            self::assertFalse($this->decision($id,'vendor_direct')['configured']);
            self::assertSame('UNSUPPORTED',$this->decision($id,'vendor_direct')['capability_state']);
        }
    }

    public function test_vendor_can_store_encrypted_configuration_with_platform_preference_and_activation_off(): void
    {
        DB::table('shops')->where('id',1)->update(['collect_via_platform'=>true]);
        $this->app['config']->set('development.payments.mode','disabled');
        $result=(new ShopPaymentService)->create(['shop_id'=>1,'payment_id'=>3,'status'=>true,
            'api_key'=>'synthetic-secret','subscription_key'=>'synthetic-sub','api_user'=>'synthetic-user',
            'target_environment'=>'mtncameroon','base_url'=>'https://synthetic-mtn.example','location_type'=>1]);
        self::assertTrue($result['status']);
        $row=ShopPayment::where('shop_id',1)->firstOrFail();
        self::assertNotSame('synthetic-secret',$row->getRawOriginal('api_key'));
        self::assertSame('synthetic-secret',$row->api_key);
        self::assertTrue((bool)Shop::find(1)->collect_via_platform);
        $s=$this->decision(3,'vendor_direct');
        self::assertTrue($s['configured']);
        self::assertTrue($s['runtime_ready']);
        self::assertFalse($s['checkout_available']);
        self::assertSame(0,DB::table('commerce_payment_allocations')->count());
        self::assertSame(0,DB::table('payment_collection_contexts')->count());
        self::assertStringNotContainsString('synthetic-secret',json_encode($s));
    }

    public function test_native_seller_controller_rejects_cross_shop_show_and_update(): void
    {
        $this->configure('vendor_direct',2);
        $controller=(new \ReflectionClass(\App\Http\Controllers\API\v1\Dashboard\Seller\ShopPaymentController::class))
            ->newInstanceWithoutConstructor();
        $property=new \ReflectionProperty(\App\Http\Controllers\API\v1\Dashboard\Seller\SellerBaseController::class,'shop');
        $property->setValue($controller,Shop::find(1));
        $other=ShopPayment::where('shop_id',2)->firstOrFail();
        foreach (['show','update'] as $method) {
            try {
                if ($method==='show') $controller->show($other);
                else $controller->update($other,new \App\Http\Requests\ShopPayment\UpdateRequest);
                self::fail('Cross-Shop configuration accepted');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                self::assertSame(404,$e->getStatusCode());
            }
        }
        self::assertSame(1,DB::table('shop_payments')->count());
        self::assertSame(2,(int)$other->fresh()->shop_id);
    }

    public function test_secret_resources_only_return_presence_and_updates_preserve_redacted_secrets(): void
    {
        $this->row('payment_payloads',['payment_id'=>6,'payload'=>json_encode([
            'paystack_sk'=>'synthetic-secret','paystack_pk'=>'synthetic-public','currency'=>'XAF'])]);
        $resource=(new \App\Http\Resources\PaymentPayloadResource(\App\Models\PaymentPayload::find(6)))
            ->toArray($this->app['request']);
        self::assertArrayNotHasKey('paystack_sk',$resource['payload']);
        self::assertTrue($resource['credential_presence']['paystack_sk']);
        $result=(new \App\Services\PaymentPayloadService\PaymentPayloadService)->update(6,[
            'payload'=>['paystack_sk'=>'','paystack_pk'=>'new-public','currency'=>'XAF']]);
        self::assertTrue($result['status']);
        self::assertSame('synthetic-secret',\App\Models\PaymentPayload::find(6)->payload['paystack_sk']);
        self::assertSame('new-public',\App\Models\PaymentPayload::find(6)->payload['paystack_pk']);
    }

    public function test_quote_validation_and_mtn_environment_are_independent_readiness_gates(): void
    {
        $this->configure();
        $context=$this->context();
        $context['quote_supported']=false;
        $s=(new PaymentEligibilityService)->decision(Payment::find(3),$context);
        self::assertTrue($s['runtime_ready']);
        self::assertFalse($s['checkout_available']);
        self::assertContains('canonical_quote_unsupported',$s['reasons']);
        DB::table('platform_payment_configs')->update(['target_environment'=>'sandbox']);
        self::assertContains('mtn_sandbox_currency_mismatch',$this->decision()['runtime_reasons']);
        DB::table('platform_payment_configs')->update(['target_environment'=>'mtncameroon','base_url'=>null]);
        self::assertContains('provider_endpoint_not_configured',$this->decision()['runtime_reasons']);
    }

    public function test_native_seller_store_assigns_server_shop_not_submitted_shop(): void
    {
        DB::table('shops')->where('id',1)->update(['collect_via_platform'=>true]);
        $controller=(new \ReflectionClass(\App\Http\Controllers\API\v1\Dashboard\Seller\ShopPaymentController::class))
            ->newInstanceWithoutConstructor();
        foreach (['shop'=>Shop::find(1),'language'=>'en','service'=>new ShopPaymentService] as $key=>$value) {
            $property=new \ReflectionProperty($controller,$key);
            $property->setValue($controller,$value);
        }
        $request=\Mockery::mock(\App\Http\Requests\ShopPayment\StoreRequest::class);
        $request->shouldReceive('validated')->once()->andReturn([
            'shop_id'=>999,'payment_id'=>3,'status'=>false,'location_type'=>1,
            'api_key'=>'synthetic-secret','api_user'=>'synthetic-user','subscription_key'=>'synthetic-sub',
            'target_environment'=>'mtncameroon',
        ]);
        $response=$controller->store($request);
        self::assertSame(200,$response->getStatusCode());
        self::assertSame(1,(int)ShopPayment::firstOrFail()->shop_id);
        self::assertFalse($this->decision(3,'vendor_direct',999)['configured']);
        self::assertSame(0,DB::table('commerce_payment_allocations')->count());
    }

    public function test_configuration_changes_leave_existing_native_financial_snapshots_untouched(): void
    {
        $order=$this->order();
        (new \App\Services\PaymentAccounting\NativeQuoteFactory)->commitNew($order);
        $order->createTransaction(['price'=>100,'user_id'=>2,'payment_sys_id'=>1,'status'=>'paid']);
        $tables=['commerce_payment_allocations','payment_collection_contexts','platform_fee_ledger_entries','transactions','wallets'];
        $before=[];
        foreach ($tables as $table) $before[$table]=DB::table($table)->get()->toJson();
        $service=new ShopPaymentService;
        $result=$service->create(['shop_id'=>1,'payment_id'=>3,'status'=>false,'location_type'=>1,
            'api_key'=>'synthetic-secret','api_user'=>'synthetic-user','subscription_key'=>'synthetic-sub',
            'target_environment'=>'mtncameroon','base_url'=>'https://synthetic-mtn.example']);
        self::assertTrue($result['status']);
        self::assertTrue($service->update(['api_key'=>'replacement-synthetic-secret'],ShopPayment::firstOrFail())['status']);
        foreach ($tables as $table) self::assertSame($before[$table],DB::table($table)->get()->toJson(),$table);
        self::assertStringNotContainsString('synthetic-secret',json_encode($this->diagnosticLog->messages));
    }

    public function test_country_activation_does_not_determine_capability_or_configuration(): void
    {
        $this->configure();
        DB::table('country_payments')->where('payment_id',3)->update(['active'=>false]);
        $s=$this->decision();
        self::assertSame('SUPPORTED',$s['capability_state']);
        self::assertSame('CONFIGURED',$s['configuration_state']);
        self::assertTrue($s['runtime_ready']);
        self::assertFalse($s['checkout_available']);
        self::assertContains('country_policy_denied',$s['reasons']);
    }
}