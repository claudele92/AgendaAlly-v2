<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\CommercePaymentAllocation;
use App\Models\PaymentCollectionContext;
use App\Models\PlatformFeeLedgerEntry;
use App\Services\PaymentAccounting\AccountingEffects;
use App\Services\PaymentAccounting\AllocationBalances;
use App\Services\PaymentAccounting\ExactMoney;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;

final class PaymentAccountingTest extends PaymentAccountingFixture
{
    public static function patterns(): array
    {
        return [
            'platform' => [[['platform',10000]],1000,0,9000],
            'direct' => [[['vendor_direct',10000]],0,1000,0],
            'cash' => [[['offline',10000]],0,1000,0],
            'wallet' => [[['internal',10000]],1000,0,9000],
            'wallet_platform' => [[['internal',4000],['platform',6000]],1000,0,9000],
            'wallet_direct' => [[['internal',4000],['vendor_direct',6000]],1000,0,3000],
            'wallet_cash' => [[['internal',4000],['offline',6000]],1000,0,3000],
            'platform_direct_mathematical' => [[['platform',4000],['vendor_direct',6000]],1000,0,3000],
            'held_equal_commission' => [[['internal',1000],['vendor_direct',9000]],1000,0,0],
            'held_less_than_commission' => [[['internal',500],['vendor_direct',9500]],500,500,0],
        ];
    }

    #[DataProvider('patterns')]
    public function test_exact_funding_and_partial_full_refund_matrix(array $legs, int $s, int $r, int $m): void
    {
        $id = $this->writer->commit($this->quote());
        $ids = $this->fund($id, $legs);
        $balances = new AllocationBalances;
        $original = $balances->current($id);
        self::assertSame($s, $original['commission_satisfied']);
        self::assertSame($r, $original['commission_receivable']);
        self::assertSame($m, $original['vendor_payable']);
        self::assertSame(10000, $original['platform_principal'] + $original['vendor_principal']);
        self::assertSame(10000, $original['commission'] + $original['vendor_entitlement']);
        $before = DB::table('payment_collection_contexts')->get()->toJson();
        foreach ([1, 2] as $iteration) {
            $group = (string) Str::uuid();
            $proof = ['authority' => 'synthetic_approved_half_reversal', 'operation' => $group];
            $effects = [['kind' => 'commission_reversal', 'amount' => 500, 'proof' => $proof]];
            foreach ($ids as $contextId) {
                $context = DB::table('payment_collection_contexts')->find($contextId);
                $effects[] = ['kind' => 'refund_principal', 'context_id' => $contextId, 'amount' => intdiv((int) $context->amount, 2), 'proof' => $proof];
            }
            (new AccountingEffects)->append($id, $group, $effects);
            (new AccountingEffects)->append($id, $group, $effects); // identical replay
            $current = $balances->current($id);
            self::assertSame(10000 - 5000 * $iteration, $current['gross']);
            self::assertSame(1000 - 500 * $iteration, $current['commission']);
            self::assertSame($current['gross'], $current['platform_principal'] + $current['vendor_principal']);
            self::assertSame($current['gross'] + $current['economic_shortfall'], $current['commission'] + $current['vendor_entitlement']);
        }
        self::assertSame(0, $balances->current($id)['vendor_payable']);
        self::assertSame($before, DB::table('payment_collection_contexts')->get()->toJson());
        self::assertSame('fully_reversed', DB::table('commerce_payment_allocations')->find($id)->state);
        self::assertCount(2, DB::table('platform_fee_ledger_entries')->whereIn('effect_kind', ['base_commission','base_payable'])->get());
    }

    public function test_zero_and_full_commission(): void
    {
        foreach ([0,10000] as $key => $commission) {
            $id = $this->writer->commit($this->quote($key + 1, 10000, $commission));
            $this->fund($id, [['internal',10000]]);
            $result = (new AllocationBalances)->current($id);
            self::assertSame($commission, $result['commission_satisfied']);
            self::assertSame(10000 - $commission, $result['vendor_payable']);
        }
    }

    public function test_retained_commission_and_prior_settlement_are_separate_obligations(): void
    {
        $id = $this->writer->commit($this->quote());
        $ids = $this->fund($id, [['internal',4000],['vendor_direct',6000]]);
        $group = (string) Str::uuid();
        (new AccountingEffects)->append($id, $group, [
            ['kind' => 'vendor_settlement','amount' => 2000,'proof' => ['authority' => 'synthetic_settlement','operation' => $group]],
        ]);
        $group = (string) Str::uuid();
        $proof = ['authority' => 'synthetic_retained_commission','operation' => $group];
        (new AccountingEffects)->append($id, $group, [
            ['kind' => 'refund_principal','context_id' => $ids[0],'amount' => 4000,'proof' => $proof],
            ['kind' => 'refund_principal','context_id' => $ids[1],'amount' => 6000,'proof' => $proof],
        ]);
        $value = (new AllocationBalances)->current($id);
        self::assertSame(0, $value['vendor_payable']);
        self::assertSame(2000, $value['settlement_recovery_due']);
        self::assertSame(1000, $value['commission_receivable']);
        self::assertSame(1000, $value['economic_shortfall']);
        self::assertSame(0, $value['vendor_entitlement']);
    }

    public function test_replacement_duplicate_paid_and_receipt_replay_keep_one_economic_claim(): void
    {
        $id = $this->writer->commit($this->quote());
        $e = $this->evidence('internal',10000);
        $context = $this->writer->stage($id, $e);
        $this->writer->confirm([$context], 'original-withdrawal',10000);
        foreach ([11,12] as $t) {
            DB::table('transactions')->insert(['id' => $t,'payable_type' => \App\Models\Booking::class,'payable_id' => 1,'status' => 'paid']);
            $this->writer->linkTransaction($t,$id,$context);
            $this->writer->finalize($id);
            $this->writer->confirm([$context], 'original-withdrawal',10000);
        }
        DB::table('transactions')->where('id',11)->delete();
        self::assertSame($id, $this->writer->commit($this->quote()));
        self::assertSame($context, $this->writer->stage($id,$e));
        self::assertSame(2, DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(1, DB::table('commerce_payment_allocations')->count());
        self::assertSame(1, DB::table('payment_collection_contexts')->count());
    }

    public function test_global_receipt_reuse_rolls_back_other_checkout(): void
    {
        $first = $this->writer->commit($this->quote());
        $c1 = $this->writer->stage($first,$this->evidence('platform',10000));
        $this->writer->confirm([$c1],'provider-receipt',10000);
        $this->checkout = (string) Str::uuid();
        $second = $this->writer->commit($this->quote(2));
        $c2 = $this->writer->stage($second,$this->evidence('platform',10000));
        try {
            $this->writer->confirm([$c2],'provider-receipt',10000);
            self::fail('Receipt funded twice.');
        } catch (\DomainException $e) {
            self::assertStringContainsString('already claimed', $e->getMessage());
        }
        self::assertNull(DB::table('commerce_payment_allocations')->find($second)->finalized_at);
        self::assertSame('committed',DB::table('payment_collection_contexts')->find($c2)->state);
        self::assertSame(2, DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_incomplete_wallet_is_exposure_not_full_payable(): void
    {
        $id = $this->writer->commit($this->quote());
        $this->fund($id,[['internal',4000]]);
        $value = (new AllocationBalances)->current($id);
        self::assertFalse($value['finalized']);
        self::assertSame(4000,$value['customer_held_exposure']);
        self::assertSame(0,$value['vendor_payable']);
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
        $this->expectException(\DomainException::class);
        $this->writer->cancel($id);
    }

    public function test_cart_requote_preserves_canceled_identity_and_binding_uniqueness(): void
    {
        $quote = array_replace($this->quote(), ['origin_type' => 'cart','origin_id' => 7,'payable_type' => null,'payable_id' => null]);
        $id = $this->writer->commit($quote);
        $context = $this->writer->stage($id,$this->evidence('platform',10000));
        $this->writer->pending([$context],'original-provider-id');
        try {
            $this->writer->cancel($id);
            self::fail('Unresolved intent canceled.');
        } catch (\DomainException) {}
        $this->writer->reject($context,true); $this->writer->cancel($id);
        $quote['checkout_key'] = (string) Str::uuid();
        $quote['gross_amount'] = 20000; $quote['commission_amount'] = 2000; $quote['vendor_entitlement_amount'] = 18000;
        $second = $this->writer->commit($quote);
        DB::table('orders')->insert(['id' => 9,'cart_id' => 7,'shop_id' => 1,'user_id' => 2,'currency_id' => 1]);
        $this->writer->bind($second,'order',9);
        self::assertNotSame($id,$second);
        self::assertSame('canceled',DB::table('commerce_payment_allocations')->find($id)->state);
        self::assertSame(10000,(int)DB::table('commerce_payment_allocations')->find($id)->gross_amount);
        $this->expectException(\DomainException::class);
        $this->writer->bind($id,'order',9);
    }

    public function test_multishop_shared_wallet_receipt_is_partitioned_once(): void
    {
        DB::table('shops')->insert(['id' => 2,'user_id' => 1,'country_id' => 1]);
        $a = $this->writer->commit($this->quote(1));
        $b = $this->writer->commit($this->quote(2,20000,2000,2));
        $event = (string) Str::uuid();
        $ca = $this->writer->stage($a,$this->evidence('internal',10000,'selected_method',1,$event,30000));
        $cb = $this->writer->stage($b,$this->evidence('internal',20000,'selected_method',2,$event,30000));
        $this->writer->confirm([$ca,$cb],'shared-wallet-withdrawal',30000);
        self::assertSame(9000,(new AllocationBalances)->current($a)['vendor_payable']);
        self::assertSame(18000,(new AllocationBalances)->current($b)['vendor_payable']);
        self::assertSame(1,DB::table('payment_collection_contexts')->whereNotNull('receipt_claim_key')->count());
        self::assertSame($ca,(int)DB::table('payment_collection_contexts')->find($cb)->receipt_anchor_context_id);
    }

    public function test_direct_other_shop_is_rejected_without_ledger_rows(): void
    {
        $id = $this->writer->commit($this->quote());
        $this->expectException(\DomainException::class);
        $this->writer->stage($id,$this->evidence('vendor_direct',10000,'selected_method',2));
    }

    public function test_models_and_linked_legacy_status_helpers_cannot_mutate_evidence(): void
    {
        $id = $this->writer->commit($this->quote());
        $ids = $this->fund($id,[['internal',10000]]);
        foreach ([CommercePaymentAllocation::find($id),PaymentCollectionContext::find($ids[0]),PlatformFeeLedgerEntry::first()] as $model) {
            try { $model->delete(); self::fail('Evidence deleted.'); } catch (\DomainException) {}
        }
        $this->expectException(\DomainException::class);
        PlatformFeeLedgerEntry::first()->markCollected();
    }

    public function test_money_range_precision_and_overflow_free_proportions(): void
    {
        self::assertSame(PHP_INT_MAX,ExactMoney::units('92233720368547758.07',2));
        self::assertSame('2.50',ExactMoney::decimal(250,2));
        self::assertSame(PHP_INT_MAX-1,ExactMoney::portion(PHP_INT_MAX,PHP_INT_MAX-1,PHP_INT_MAX));
        self::assertSame([1=>34,2=>33,3=>33],ExactMoney::partition(100,[1=>100,2=>100,3=>100]));
        foreach (['1.001','-1','1e2','92233720368547758.08'] as $value) {
            try { ExactMoney::units($value); self::fail('Invalid amount accepted.'); } catch (\DomainException) {}
        }
    }

    public function test_unfinalized_partial_funding_return_allows_safe_cancellation_without_full_fee(): void
    {
        $id=$this->writer->commit($this->quote());
        $ids=$this->fund($id,[['internal',4000]]);
        $group=(string)Str::uuid();
        (new AccountingEffects)->append($id,$group,[
            ['kind'=>'refund_principal','context_id'=>$ids[0],'amount'=>4000,'proof'=>['authority'=>'synthetic_partial_return','operation'=>$group]],
        ]);
        $this->writer->cancel($id);
        self::assertSame('canceled',DB::table('commerce_payment_allocations')->find($id)->state);
        self::assertNull(DB::table('commerce_payment_allocations')->find($id)->finalized_at);
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->whereIn('effect_kind',['base_commission','base_payable'])->count());
        self::assertSame('confirmed',DB::table('payment_collection_contexts')->find($ids[0])->state);
    }
}