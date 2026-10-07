<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\PaymentPayload;
use App\Services\PaymentAccounting\{CompletionSchema,MerchantRevisions,DurableCollections};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

abstract class PaymentCompletionFixture extends PaymentAccountingFixture
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('payment_payloads',function(Blueprint $t){$t->unsignedBigInteger('payment_id');$t->text('payload');});
        Schema::create('payment_process',function(Blueprint $t){
            $t->string('id')->primary();$t->unsignedBigInteger('user_id');$t->string('model_type');
            $t->unsignedBigInteger('model_id');$t->text('data');$t->timestamps();
        });
        (new CompletionSchema)->up();
        config(['payment_eligibility.mode'=>'sandbox','development.payment_mode'=>'sandbox']);
    }

    protected function funded(string $mode='platform', string $provider='paystack', bool $recordAttempt=true,
        bool $confirmed=true, int $sourceId=1): array
    {
        DB::table('payments')->where('id',1)->update(['tag'=>$provider]);
        $p=PaymentPayload::where('payment_id',1)->first() ?? PaymentPayload::create(['payment_id'=>1,'payload'=>[
            'paystack_sk'=>'synthetic-old','paystack_pk'=>'synthetic-public','currency'=>'USD',
            'stripe_sk'=>'sk_test_synthetic','paypal_mode'=>'sandbox','paypal_sandbox_client_id'=>'synthetic-client',
            'paypal_sandbox_client_secret'=>'synthetic-old','paypal_merchant_id'=>'synthetic-merchant','paypal_webhook_id'=>'synthetic-hook',
        ]]);
        (new MerchantRevisions)->append($p);
        $id=$this->writer->commit($this->quote($sourceId));
        $e=$this->evidence($mode,10000);
        if ($mode==='platform') {
            $e['provider_tag']=$provider;$e['configuration_source']='global_payload';
            $e['configuration_reference']='1';$e['configuration_revision']=$p->revision_id;
        }
        $c=$this->writer->stage($id,$e);
        $paymentReference=$sourceId===1?'original-payment':'original-payment-'.$sourceId;
        $attempt=null;
        if ($mode==='platform' && $recordAttempt) {
            $attempt=(new DurableCollections)->reserve($this->before($c),$provider);
            if ($confirmed) {
                (new DurableCollections)->claim($attempt->id);
                DB::table('electronic_collection_attempts')->where('id',$attempt->id)->update([
                    'state'=>'SUCCESS','provider_reference'=>$attempt->provider_reference ?? 'original-payment',
                    'provider_payment_id'=>$provider==='paypal'?'original-capture':($provider==='stripe'?'pi_original':$paymentReference),'version'=>2]);
            }
        }
        $this->writer->pending([$c],$paymentReference);
        if ($confirmed) $this->writer->confirm([$c],'synthetic-receipt-'.$sourceId,10000);
        return [$id,$c,$p->revision_id,$attempt];
    }

    protected function before(int $context): array
    {
        return ['accounting_context_ids'=>[$context],'model_type'=>\App\Models\Booking::class,'model_id'=>1,
            'total_price'=>10000,'currency'=>'USD','payment_id'=>1];
    }
}