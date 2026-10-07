<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Http\Resources\PaymentPayloadResource;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Services\PaymentEligibility\PaymentEligibilityService;
use App\Services\PaymentPayloadService\PaymentPayloadService;
use Illuminate\Support\Facades\DB;

/** Disposable native schema only; no provider calls or real credentials. */
final class PaymentConfigurationCompletionTest extends PaymentNativeCheckoutFixture
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('payment_eligibility', require dirname(__DIR__, 2).'/config/payment_eligibility.php');
        $this->app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('s',32)));
        $this->app['config']->set('app.cipher', 'AES-256-CBC');
        $this->app->register(\Illuminate\Encryption\EncryptionServiceProvider::class);
        $this->app['config']->set('development.urls.api', 'https://synthetic.example');
        foreach ([5=>'flutter-wave',6=>'paystack',7=>'stripe',8=>'paypal'] as $id=>$tag) {
            $this->row('payments',['id'=>$id,'tag'=>$tag,'active'=>1]);
            $this->row('country_payments',['country_id'=>1,'payment_id'=>$id,'active'=>1]);
        }
        $this->row('currencies',['id'=>2,'title'=>'USD','active'=>1]);
    }

    private function context(string $mode = 'platform'): array
    {
        return ['valid'=>true,'reason'=>null,'shop_ids'=>[1],'country_ids'=>[1],
            'transaction_type'=>'product','transaction_currency'=>'USD',
            'collection_mode'=>$mode,'quote_supported'=>true];
    }

    public function test_new_secret_storage_is_encrypted_and_internal_sdk_values_still_work(): void
    {
        foreach ([5=>['flw_sk'=>'synthetic-secret','flw_webhook_secret_hash'=>'synthetic-hash','flw_account_id'=>'synthetic-account'],
            6=>['paystack_sk'=>'synthetic-secret'],
            7=>['stripe_sk'=>'synthetic-secret','stripe_webhook_secret'=>'synthetic-hash'],
            8=>['paypal_sandbox_client_id'=>'synthetic-client','paypal_sandbox_client_secret'=>'synthetic-secret']] as $id=>$fields) {
            $model=PaymentPayload::create(['payment_id'=>$id,'payload'=>$fields+['currency'=>'USD']]);
            $raw=(string)DB::table('payment_payloads')->where('payment_id',$id)->value('payload');
            foreach ($fields as $key=>$value) {
                self::assertStringNotContainsString($value,$raw);
                self::assertSame($value,$model->fresh()->payload[$key]);
            }
            self::assertSame('USD',json_decode($raw,true)['currency']);
            self::assertArrayNotHasKey('payload',$model->toArray());
            $api=(new PaymentPayloadResource($model))->resolve();
            foreach ($fields as $key=>$value) {
                self::assertArrayNotHasKey($key,$api['payload']);
                self::assertTrue($api['credential_presence'][$key]);
            }
        }
    }

    public function test_blank_secret_edit_preserves_secret_and_explicit_rotation_replaces_it(): void
    {
        PaymentPayload::create(['payment_id'=>7,'payload'=>[
            'stripe_pk'=>'synthetic-public','stripe_sk'=>'synthetic-original','currency'=>'USD',
        ]]);
        $service=new PaymentPayloadService;
        $result=$service->update(7,['payload'=>['stripe_sk'=>'','stripe_pk'=>'synthetic-public','currency'=>'USD']]);
        self::assertTrue($result['status']);
        self::assertSame('synthetic-original',PaymentPayload::find(7)->payload['stripe_sk']);
        $result=$service->update(7,['payload'=>['stripe_sk'=>'synthetic-rotated']]);
        self::assertTrue($result['status']);
        self::assertSame('synthetic-rotated',PaymentPayload::find(7)->payload['stripe_sk']);
    }

    public function test_paypal_only_requires_credentials_for_selected_environment(): void
    {
        $service=new PaymentPayloadService;
        $result=$service->create(['payment_id'=>8,'payload'=>[
            'paypal_mode'=>'sandbox','paypal_currency'=>'USD',
            'paypal_sandbox_client_id'=>'synthetic-client','paypal_sandbox_client_secret'=>'synthetic-secret',
        ]]);
        self::assertTrue($result['status']);
        $result=$service->update(8,['payload'=>['paypal_sandbox_client_secret'=>'','paypal_mode'=>'sandbox']]);
        self::assertTrue($result['status']);
        self::assertSame('synthetic-secret',PaymentPayload::find(8)->payload['paypal_sandbox_client_secret']);
        $result=$service->update(8,['payload'=>['paypal_mode'=>'live']]);
        self::assertFalse($result['status']);
        self::assertSame('sandbox',PaymentPayload::find(8)->payload['paypal_mode']);
    }

    public function test_configuration_never_activates_checkout_or_satisfies_vendor_direct(): void
    {
        PaymentPayload::create(['payment_id'=>7,'payload'=>[
            'stripe_pk'=>'synthetic-public','stripe_sk'=>'synthetic-secret',
            'stripe_webhook_secret'=>'synthetic-hash','currency'=>'USD',
        ]]);
        $service=new PaymentEligibilityService;
        $platform=$service->decision(Payment::find(7),$this->context());
        self::assertSame('CONFIGURED',$platform['configuration_state']);
        self::assertSame('SUPPORTED',$platform['capability_state']);
        self::assertFalse($platform['checkout_available']);
        self::assertContains('canonical_configuration_revision_unavailable',$platform['runtime_reasons']);
        self::assertNotContains('legacy_secret_storage_not_certified',$platform['runtime_reasons']);
        $direct=$service->decision(Payment::find(7),$this->context('vendor_direct'),true);
        self::assertFalse($direct['configured']);
        self::assertFalse($direct['available_for_configuration']);
        self::assertContains('collection_mode_unsupported',$direct['reasons']);
    }

    public function test_missing_credentials_are_not_currency_unsupported_and_real_use_is_disabled(): void
    {
        $this->app['config']->set('app.env','local');
        foreach ([7,8] as $id) {
            $state=(new PaymentEligibilityService)->decision(Payment::find($id),$this->context());
            self::assertSame('SUPPORTED',$state['capability_state']);
            self::assertSame('NOT_CONFIGURED',$state['configuration_state']);
            self::assertFalse($state['activation_allowed']);
            self::assertFalse((new PaymentEligibilityService)->catalogReady(Payment::find($id)));
        }
    }

    public function test_legacy_secret_storage_remains_explicitly_uncertified_without_backfill(): void
    {
        $legacy=['paystack_sk'=>'synthetic-legacy','currency'=>'USD'];
        $this->row('payment_payloads',['payment_id'=>6,'payload'=>json_encode($legacy)]);
        $state=(new PaymentEligibilityService)->decision(Payment::find(6),$this->context());
        self::assertContains('legacy_secret_storage_not_certified',$state['runtime_reasons']);
        self::assertSame($legacy,json_decode(DB::table('payment_payloads')->where('payment_id',6)->value('payload'),true));
        self::assertArrayNotHasKey('paystack_sk',(new PaymentPayloadResource(PaymentPayload::find(6)))->resolve()['payload']);
    }

    public function test_wrong_provider_fields_are_rejected_without_reflecting_or_storing_values(): void
    {
        $result=(new PaymentPayloadService)->create(['payment_id'=>6,'payload'=>[
            'paystack_sk'=>'synthetic-valid','paystack_pk'=>'synthetic-public','currency'=>'USD',
            'stripe_sk'=>'synthetic-wrong-provider',
        ]]);
        self::assertFalse($result['status']);
        self::assertSame(0,DB::table('payment_payloads')->count());
        self::assertStringNotContainsString('synthetic-wrong-provider',json_encode($result));
        self::assertStringNotContainsString('stripe_sk',json_encode($result));
    }
}