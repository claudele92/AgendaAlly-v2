<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\PaymentPayload;
use App\Services\PaymentAccounting\{CompletionSchema,MerchantRevisions,DurableCollections,FinancialOperations,AllocationBalances};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema,Http};
use Illuminate\Support\Str;

class PaymentCompletionIdentityTest extends PaymentCompletionFixture
{

    public function test_revisions_encrypt_retain_and_do_not_reinterpret_rotation(): void
    {
        [$id,$c,$r]=$this->funded();
        $p=PaymentPayload::find(1);$p->payload=['paystack_sk'=>'synthetic-new','currency'=>'USD'];$p->save();
        (new MerchantRevisions)->append($p);
        self::assertNotSame($r,$p->revision_id);
        self::assertSame('synthetic-old',(new MerchantRevisions)->payload($r,'paystack')['paystack_sk']);
        self::assertStringNotContainsString('synthetic-old',DB::table('payment_merchant_revisions')->value('encrypted_payload'));
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('payment_merchant_revisions')->where('id',$r)->update(['provider'=>'stripe']);
    }

    public function test_original_attempt_is_durable_and_dispatch_claim_only_once(): void
    {
        [$id,$c,$r,$a]=$this->funded();
        self::assertNotNull(DB::table('payment_process')->find($a->process_reference));
        self::assertFalse((new DurableCollections)->claim($a->id));
        $same=(new DurableCollections)->reserve(['accounting_context_ids'=>[$c]],'paystack');
        self::assertSame($a->id,$same->id);
        self::assertSame(1,DB::table('electronic_collection_attempts')->count());
        self::assertSame($r,$same->revision_id);
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('electronic_collection_attempts')->where('id',$a->id)->update(['provider_reference'=>'replacement']);
    }

    public function test_refund_limits_reservations_and_retry_identity(): void
    {
        [$id,$c]=$this->funded();$service=new FinancialOperations;$key=(string)Str::uuid();
        $one=$service->reserve($id,'refund',$key,6000,2,$c);
        self::assertSame($one->id,$service->reserve($id,'refund',$key,6000,2,$c)->id);
        self::assertSame(6000,$service->reserved($id,'refund',$c));
        $this->expectException(\DomainException::class);
        $service->reserve($id,'refund',(string)Str::uuid(),4001,2,$c);
    }

    public function test_paystack_processed_not_queued_and_original_secret_after_rotation(): void
    {
        [$id,$c]=$this->funded();
        $p=PaymentPayload::find(1);$p->payload=['paystack_sk'=>'synthetic-new','currency'=>'USD'];$p->save();
        (new MerchantRevisions)->append($p);
        $service=new FinancialOperations;$op=$service->reserve($id,'refund',(string)Str::uuid(),2500,2,$c);
        Http::fake(['api.paystack.co/refund*'=>Http::sequence()
            ->push(['status'=>true,'data'=>['id'=>72,'transaction'=>'original-payment','amount'=>2500,'currency'=>'USD','status'=>'pending']])
            ->push(['status'=>true,'data'=>['id'=>72,'transaction'=>'original-payment','amount'=>2500,'currency'=>'USD','status'=>'processed']])]);
        self::assertSame('PENDING',$service->refund($op->id)->state);
        self::assertSame(0,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame('SUCCESS',$service->refund($op->id,true)->state);
        self::assertSame('SUCCESS',$service->refund($op->id,true)->state);
        self::assertSame(2500,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(750,(new AllocationBalances)->current($id)['commission']);
        Http::assertSent(fn($r)=>$r->header('Authorization')[0]==='Bearer synthetic-old');
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->where('event_group_key',$op->id)->count());
    }

    public function test_paypal_refund_response_completed_is_parsed_from_original_capture(): void
    {
        [$id,$c]=$this->funded('platform','paypal');$service=new FinancialOperations;
        $op=$service->reserve($id,'refund',(string)Str::uuid(),10000,2,$c);
        Http::fake(['*/v1/oauth2/token'=>Http::response(['access_token'=>'synthetic-token']),
            '*/v2/payments/captures/original-capture/refund'=>Http::response([
                'id'=>'synthetic-refund','status'=>'COMPLETED','amount'=>['currency_code'=>'USD','value'=>'100.00']])]);
        self::assertSame('SUCCESS',$service->refund($op->id)->state);
        self::assertSame(10000,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(0,(new AllocationBalances)->current($id)['vendor_payable']);
        Http::assertSent(fn($r)=>str_ends_with($r->url(),'/captures/original-capture/refund')
            && $r->header('PayPal-Request-Id')[0]===$op->id);
    }

    public function test_unknown_refund_never_releases_or_redispatches_authority(): void
    {
        [$id,$c]=$this->funded();$s=new FinancialOperations;
        $op=$s->reserve($id,'refund',(string)Str::uuid(),10000,2,$c);
        Http::fake(['api.paystack.co/refund'=>Http::response([],503)]);
        self::assertSame('UNKNOWN',$s->refund($op->id)->state);
        self::assertSame(10000,$s->reserved($id,'refund',$c));
        $this->expectException(\DomainException::class);
        $s->cancel($op->id);
    }

    public function test_receivable_settlement_requires_retained_unique_receipt_and_replays_once(): void
    {
        [$id]=$this->funded('offline');$s=new FinancialOperations;
        $op=$s->reserve($id,'receivable',(string)Str::uuid(),1000,2);
        $e=['source'=>'cash_receipt','receipt_reference'=>'synthetic-cash-receipt',
            'document_sha256'=>str_repeat('a',64),'retained_evidence'=>'Synthetic verified receipt provenance, no actual cash received.',
            'received_at'=>'2026-10-03T10:00:00.000Z'];
        self::assertSame('SUCCESS',$s->receipt($op->id,$e)->state);
        self::assertSame('SUCCESS',$s->receipt($op->id,$e)->state);
        self::assertSame(0,(new AllocationBalances)->current($id)['commission_receivable']);
        self::assertSame(1,DB::table('payment_receipt_evidence')->count());
        self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('event_group_key',$op->id)->count());
    }

    public function test_payout_reservation_cannot_exceed_liability_and_has_no_external_success_path(): void
    {
        [$id]=$this->funded();$s=new FinancialOperations;
        $op=$s->reserve($id,'payout',(string)Str::uuid(),9000,2);
        self::assertSame(9000,$s->reserved($id,'payout'));
        self::assertSame(9000,(new AllocationBalances)->current($id)['vendor_payable']);
        $s->cancel($op->id);
        self::assertSame(0,$s->reserved($id,'payout'));
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->where('effect_kind','vendor_settlement')->count());
        $op=$s->reserve($id,'payout',(string)Str::uuid(),9000,2);
        $this->expectException(\DomainException::class);
        $s->reserve($id,'payout',(string)Str::uuid(),1,2);
    }

    public function test_populated_migration_rollback_is_refused_without_deleting_evidence(): void
    {
        $this->funded();
        $this->expectException(\RuntimeException::class);
        (new CompletionSchema)->down();
    }

    public function test_empty_migration_rolls_back_and_reapplies_without_touching_legacy_tables(): void
    {
        (new CompletionSchema)->down();
        self::assertFalse(Schema::hasColumn('payment_payloads','revision_id'));
        self::assertSame(2,DB::table('users')->count());
        (new CompletionSchema)->up();
        self::assertTrue(Schema::hasTable('payment_financial_operations'));
        self::assertSame(0,DB::table('payment_financial_operations')->count());
    }

    public function test_wrong_refund_amount_or_original_payment_remains_unknown_without_effects(): void
    {
        [$id,$c]=$this->funded();$s=new FinancialOperations;
        $op=$s->reserve($id,'refund',(string)Str::uuid(),1000,2,$c);
        Http::fake(['api.paystack.co/refund'=>Http::response(['status'=>true,'data'=>[
            'id'=>8,'transaction'=>'other-payment','amount'=>1000,'currency'=>'USD','status'=>'processed']])]);
        self::assertSame('UNKNOWN',$s->refund($op->id)->state);
        self::assertSame(0,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(1000,$s->reserved($id,'refund',$c));
    }

    public function test_receipt_reference_cannot_settle_two_reservations(): void
    {
        [$id]=$this->funded('offline');$s=new FinancialOperations;
        $first=$s->reserve($id,'receivable',(string)Str::uuid(),600,2);
        $second=$s->reserve($id,'receivable',(string)Str::uuid(),400,2);
        $e=['source'=>'cash_receipt','receipt_reference'=>'synthetic-single-receipt',
            'document_sha256'=>str_repeat('a',64),'retained_evidence'=>'Synthetic verified cash receipt provenance.',
            'received_at'=>'2026-10-03 10:00:00'];
        $s->receipt($first->id,$e);
        try{$s->receipt($second->id,$e);self::fail('One receipt must not create a second collection.');}
        catch(\Illuminate\Database\QueryException $expected){}
        self::assertSame(400,(new AllocationBalances)->current($id)['commission_receivable']);
        self::assertSame('RESERVED',DB::table('payment_financial_operations')->where('id',$second->id)->value('state'));
    }

    public function test_financial_operations_deny_other_country_and_ungranted_shop_read(): void
    {
        [$id]=$this->funded();
        $this->actor(true,false,2);
        $controller=(new \ReflectionClass(\App\Http\Controllers\API\v1\PaymentOperationsController::class))->newInstanceWithoutConstructor();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->index($id);
    }

    public function test_granted_shop_can_inspect_but_cannot_process_receivable_receipt(): void
    {
        [$id]=$this->funded('offline');$s=new FinancialOperations;
        $op=$s->reserve($id,'receivable',(string)Str::uuid(),1000,2);
        $this->actor(false,true,null);
        $controller=(new \ReflectionClass(\App\Http\Controllers\API\v1\PaymentOperationsController::class))->newInstanceWithoutConstructor();
        self::assertFalse($controller->index($id)['data']['can_process']);
        self::assertTrue($controller->index($id)['data']['can_request_payout']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->action(new \Illuminate\Http\Request,$op->id,'receipt');
    }

    private function actor(bool $admin,bool $shop,?int $country): void
    {
        $u=\Mockery::mock(\App\Models\User::class)->makePartial();$u->forceFill(['id'=>2]);
        $u->setRelation('countryAdmin',(object)['country_id'=>$country]);
        $u->shouldReceive('hasRole')->andReturn($admin);$u->shouldReceive('hasShopPermission')->andReturn($shop);
        $g=\Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $g->shouldReceive('user')->andReturn($u);$g->shouldReceive('id')->andReturn(2);$g->shouldReceive('check')->andReturn(true);
        $a=\Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);$a->shouldReceive('guard')->andReturn($g);
        $this->app->instance('auth',$a);\Illuminate\Support\Facades\Facade::clearResolvedInstance('auth');
    }

    public function test_stripe_refund_pending_success_replay_uses_original_intent_and_revision(): void
    {
        [$id,$context]=$this->funded('platform','stripe');$s=new FinancialOperations;
        $p=PaymentPayload::find(1);$p->payload=['stripe_sk'=>'sk_test_changed','currency'=>'USD'];$p->save();
        (new MerchantRevisions)->append($p);
        $op=$s->reserve($id,'refund',(string)Str::uuid(),2500,2,$context);
        Http::fake(['api.stripe.com/v1/refunds*'=>Http::sequence()
            ->push(['id'=>'re_synthetic','status'=>'pending','payment_intent'=>'pi_original','amount'=>2500,'currency'=>'usd'])
            ->push(['id'=>'re_synthetic','status'=>'succeeded','payment_intent'=>'pi_original','amount'=>2500,'currency'=>'usd'])]);
        self::assertSame('PENDING',$s->refund($op->id)->state);
        self::assertSame(0,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame('SUCCESS',$s->refund($op->id,true)->state);
        self::assertSame('SUCCESS',$s->refund($op->id,true)->state);
        self::assertSame(2500,(new AllocationBalances)->current($id)['refunded']);
        Http::assertSent(fn($r)=>$r->method()==='POST' && $r['payment_intent']==='pi_original'
            && $r->header('Authorization')[0]==='Basic '.base64_encode('sk_test_synthetic:')
            && $r->header('Idempotency-Key')[0]===$op->id);
    }

    public function test_pending_collection_dashboard_uses_frozen_country_and_denies_seller_global_list(): void
    {
        $this->funded('platform','paystack',true,false);
        $controller=(new \ReflectionClass(\App\Http\Controllers\API\v1\PaymentOperationsController::class))->newInstanceWithoutConstructor();
        $this->actor(true,false,2);self::assertCount(0,$controller->attempts()['data']['attempts']);
        $this->actor(true,false,1);self::assertCount(1,$controller->attempts()['data']['attempts']);
        $this->actor(false,true,null);$this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->attempts();
    }
}