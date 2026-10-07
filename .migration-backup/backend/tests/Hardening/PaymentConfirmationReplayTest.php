<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\{AllocationBalances,AllocationWriter};
use Illuminate\Support\Facades\DB;

/** SQLite models the hydration boundary; native workers separately prove the wait. */
class PaymentConfirmationReplayTest extends PaymentCompletionFixture
{
    public static function receipts(): array
    {
        return [['same', true], ['different', false]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('receipts')]
    public function test_state_materialized_before_authority_is_not_used_after_owner_finality(string $receipt, bool $success): void
    {
        [$id,$context]=$this->funded('platform','paystack',false,false);
        $interleaved=false;$pending=null;
        DB::listen(function($q)use(&$interleaved,&$pending,$context):void {
            if($interleaved || !str_starts_with(strtolower($q->sql),'select')
                || !str_contains($q->sql,'payment_collection_contexts') || !str_contains($q->sql,' in (')) return;
            $interleaved=true;
            $pending=DB::table('payment_collection_contexts')->where('id',$context)->value('state');
            (new AllocationWriter)->confirm([$context],'same',10000);
        });
        try {
            $this->writer->confirm([$context],$receipt,10000);
            self::assertTrue($success,'Different receipt must be denied.');
        } catch(\DomainException $e) {
            if($success) throw $e;
            self::assertStringContainsString('different evidence',$e->getMessage());
        }
        self::assertTrue($interleaved);self::assertSame('pending',$pending);
        self::assertSame(1,DB::table('payment_collection_contexts')->count());
        self::assertSame(1,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        self::assertSame(10000,(int)DB::table('payment_collection_contexts')->sum('amount'));
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        $balance=(new AllocationBalances)->current($id);
        self::assertTrue($balance['finalized']);self::assertSame(9000,$balance['vendor_payable']);
        self::assertSame(1000,(int)DB::table('commerce_payment_allocations')->where('id',$id)->value('commission_amount'));
        self::assertSame(0,DB::connection()->transactionLevel());
        self::assertFalse(DB::connection()->getPdo()->inTransaction());
    }

    public function test_finalized_replay_requires_retained_canonical_effects(): void
    {
        [$id,$context]=$this->funded('platform','paystack',false);
        DB::table('platform_fee_ledger_entries')->where('allocation_id',$id)->where('effect_key','base:payable')->delete();
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('canonical effects');
        $this->writer->confirm([$context],'synthetic-receipt-1',10000);
    }

    public function test_changed_amount_and_currency_are_not_replayed(): void
    {
        [$id,$context]=$this->funded('platform','paystack',false);
        try {$this->writer->confirm([$context],'synthetic-receipt',9999);self::fail('Changed total accepted.');}
        catch(\DomainException $e){self::assertStringContainsString('checkout/cap mismatch',$e->getMessage());}
        DB::table('payment_collection_contexts')->where('id',$context)->update(['currency_code'=>'EUR']);
        try {$this->writer->confirm([$context],'synthetic-receipt',10000);self::fail('Changed currency accepted.');}
        catch(\DomainException $e){self::assertStringContainsString('currency/scale mismatch',$e->getMessage());}
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(0,DB::connection()->transactionLevel());
    }

    public static function originalBindings(): array
    {
        return array_map(fn($field)=>[$field],['provider_tag','configuration_revision',
            'configuration_reference','funding_event_key','funding_key','amount','currency_code']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('originalBindings')]
    public function test_presenting_changed_original_evidence_cannot_reopen_terminal_funding(string $field): void
    {
        [$id,$context]=$this->funded('platform','paystack',false);
        $row=(array)DB::table('payment_collection_contexts')->where('id',$context)->first();
        $row[$field]=$field==='amount'?9999:($field==='funding_event_key'?(string)\Illuminate\Support\Str::uuid():'different');
        try {$this->writer->stage($id,$row);self::fail('Changed original identity accepted.');}
        catch(\DomainException $e){self::assertNotSame('',$e->getMessage());}
        self::assertSame(1,DB::table('payment_collection_contexts')->count());
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame('confirmed',DB::table('payment_collection_contexts')->where('id',$context)->value('state'));
        self::assertSame(0,DB::connection()->transactionLevel());
        self::assertFalse(DB::connection()->getPdo()->inTransaction());
    }
}