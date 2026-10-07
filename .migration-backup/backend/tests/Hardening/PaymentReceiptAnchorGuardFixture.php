<?php
declare(strict_types=1);
namespace Tests\Hardening;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Only disposable rows; exercises the approved invariant by direct SQL. */
abstract class PaymentReceiptAnchorGuardFixture extends PaymentAccountingFixture
{
    protected function root(): array
    {
        // This suite certifies DDL/trigger behavior, independently of application
        // quote serialization. Financial application paths run in the original 13.
        $quote = $this->quote();
        $quote['native_components'] = \App\Services\PaymentAccounting\AllocationWriter::json($quote['native_components']);
        $allocation = DB::table('commerce_payment_allocations')->insertGetId($quote+[
            'state'=>'committed','version'=>0,'committed_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
        ]);
        $id = $this->writer->stage($allocation, $this->evidence('platform',10000));
        return (array)DB::table('payment_collection_contexts')->find($id);
    }

    protected function child(array $root, int $anchor, ?int $id = null): array
    {
        unset($root['id']);
        $root['funding_key'] = 'selected_method:'.Str::uuid();
        $root['funding_event_key'] = (string)Str::uuid();
        $root['state'] = 'confirmed';
        $root['confirmed_at'] = now();
        $root['confirmed_slot'] = $root['funding_slot'];
        $root['confirmed_collector_type'] = $root['expected_collector_type'];
        $root['confirmed_collector_id'] = $root['expected_collector_id'];
        $root['receipt_claim_key'] = null;
        $root['receipt_anchor_context_id'] = $anchor;
        if ($id !== null) $root['id'] = $id;
        return $root;
    }

    protected function rejectsSelf(callable $write): void
    {
        try {
            $write();
            self::fail('Database must reject the exact receipt self-anchor invariant.');
        } catch (\Illuminate\Database\QueryException $e) {
            if (DB::connection()->getDriverName() === 'mysql') {
                self::assertSame(1644,(int)$e->errorInfo[1]);
                self::assertStringContainsString('Receipt context cannot anchor itself',$e->getMessage());
            } else {
                self::assertStringContainsString('CHECK constraint failed',$e->getMessage());
            }
        }
    }

    public static function identities(): array { return [['generated'],['explicit']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('identities')]
    public function test_direct_insert_rejects_generated_and_explicit_self_anchor(string $identity): void
    {
        $root = $this->root();
        $id = $identity === 'explicit' ? 77 : (int)$root['id']+1;
        $this->rejectsSelf(fn()=>DB::table('payment_collection_contexts')->insert(
            $this->child($root,$id,$identity === 'explicit' ? $id : null)));
        self::assertSame(1,DB::table('payment_collection_contexts')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_direct_update_rejects_self_anchor_without_changing_row(): void
    {
        $root = $this->root();
        $patch = $this->child($root,(int)$root['id']);
        unset($patch['funding_key'],$patch['funding_event_key']);
        $this->rejectsSelf(fn()=>DB::table('payment_collection_contexts')->where('id',$root['id'])->update($patch));
        self::assertSame($root,(array)DB::table('payment_collection_contexts')->find($root['id']));
    }

    public function test_null_and_distinct_anchor_pass_and_fk_remains_restrictive(): void
    {
        $root = $this->root();
        self::assertNull($root['receipt_anchor_context_id']);
        DB::table('payment_collection_contexts')->insert($this->child($root,(int)$root['id']));
        self::assertSame(2,DB::table('payment_collection_contexts')->count());
        try {
            DB::table('payment_collection_contexts')->where('id',$root['id'])->delete();
            self::fail('Referenced root must remain retained by its foreign key.');
        } catch (\Illuminate\Database\QueryException $e) {
            self::assertTrue(in_array((int)$e->errorInfo[1],[1451,19],true));
        }
        self::assertSame(2,DB::table('payment_collection_contexts')->count());
    }

    public function test_failed_insert_rolls_back_statement_inside_live_transaction(): void
    {
        $root = $this->root();
        DB::beginTransaction();
        try {
            $this->rejectsSelf(fn()=>DB::table('payment_collection_contexts')->insert($this->child($root,77,77)));
            self::assertSame(1,DB::connection()->transactionLevel());
            self::assertSame(1,DB::table('payment_collection_contexts')->count());
            self::assertSame($root,(array)DB::table('payment_collection_contexts')->find($root['id']));
            DB::commit();
        } finally {
            if (DB::connection()->transactionLevel()) DB::rollBack();
        }
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_empty_migration_down_up_reinstalls_same_guard(): void
    {
        $link = require __DIR__.'/../../database/migrations/2026_10_03_100200_link_payment_accounting_evidence.php';
        $contexts = require __DIR__.'/../../database/migrations/2026_10_03_100100_create_payment_collection_contexts.php';
        $link->down();
        $contexts->down();
        $contexts->up();
        $link->up();
        $this->test_direct_update_rejects_self_anchor_without_changing_row();
    }

    public function test_nonempty_migration_rollback_refuses_without_deleting_evidence(): void
    {
        $root = $this->root();
        $migration = require __DIR__.'/../../database/migrations/2026_10_03_100100_create_payment_collection_contexts.php';
        try { $migration->down(); self::fail('Retained accounting forbids destructive rollback.'); }
        catch (\RuntimeException $e) { self::assertStringContainsString('destructive rollback refused',$e->getMessage()); }
        self::assertSame($root,(array)DB::table('payment_collection_contexts')->find($root['id']));
    }
}