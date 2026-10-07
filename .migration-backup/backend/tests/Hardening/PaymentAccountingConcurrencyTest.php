<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\AccountingEffects;
use App\Services\PaymentAccounting\AllocationBalances;
use App\Services\PaymentAccounting\AllocationWriter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentAccountingConcurrencyTest extends PaymentAccountingFixture
{
    private array $temporaryDatabases = [];

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->temporaryDatabases as $file) @unlink($file);
    }

    public function test_two_sqlite_connections_contend_for_economic_creation(): void
    {
        $quote=$this->quote();
        $operation=fn () => (new AllocationWriter)->commit($quote);
        $this->contend($operation,$operation,'insert or ignore into "commerce_payment_allocations"');
        self::assertSame(1,DB::table('commerce_payment_allocations')->count());
    }

    public function test_two_sqlite_connections_contend_for_contribution_recording(): void
    {
        $id=$this->writer->commit($this->quote());
        $evidence=$this->evidence('platform',10000);
        $operation=fn () => (new AllocationWriter)->stage($id,$evidence);
        $this->contend($operation,$operation);
        self::assertSame(1,DB::table('payment_collection_contexts')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }
    public function test_two_sqlite_connections_contend_for_confirmation_and_base_effects_then_replay(): void
    {
        $id = $this->writer->commit($this->quote());
        $context = $this->writer->stage($id,$this->evidence('platform',10000));
        $this->contend(
            fn () => (new AllocationWriter)->confirm([$context],'same-verified-receipt',10000),
            fn () => (new AllocationWriter)->confirm([$context],'same-verified-receipt',10000)
        );
        self::assertSame(1,DB::table('commerce_payment_allocations')->count());
        self::assertSame(1,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(9000,(new AllocationBalances)->current($id)['vendor_payable']);
    }

    public function test_two_sqlite_connections_contend_for_complete_refund_group(): void
    {
        $id = $this->writer->commit($this->quote());
        $ids = $this->fund($id,[['internal',10000]]);
        $group=(string)Str::uuid(); $proof=['authority'=>'synthetic_refund','operation'=>$group];
        $effects=[
            ['kind'=>'refund_principal','context_id'=>$ids[0],'amount'=>5000,'proof'=>$proof],
            ['kind'=>'commission_reversal','amount'=>500,'proof'=>$proof],
        ];
        $operation=fn () => (new AccountingEffects)->append($id,$group,$effects);
        $this->contend($operation,$operation);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->where('event_group_key',$group)->count());
        self::assertSame(4500,(new AllocationBalances)->current($id)['vendor_payable']);
    }

    public function test_two_sqlite_connections_contend_for_vendor_settlement_group(): void
    {
        $id = $this->writer->commit($this->quote());
        $this->fund($id,[['internal',10000]]);
        $group=(string)Str::uuid();
        $effects=[['kind'=>'vendor_settlement','amount'=>2000,'proof'=>['authority'=>'synthetic_settlement','operation'=>$group]]];
        $operation=fn () => (new AccountingEffects)->append($id,$group,$effects);
        $this->contend($operation,$operation);
        self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('event_group_key',$group)->count());
        self::assertSame(7000,(new AllocationBalances)->current($id)['vendor_payable']);
    }

    private function contend(callable $primary, callable $contender, string $prefix='update "commerce_payment_allocations"'): void
    {
        $file=tempnam(sys_get_temp_dir(),'isolated-accounting-');
        $this->temporaryDatabases[]=$file;
        $manager=$this->database->getDatabaseManager();
        $manager->connection()->getPdo()->exec('VACUUM INTO '.$manager->connection()->getPdo()->quote($file));
        $manager->purge('hardening');
        foreach (['hardening','contender'] as $name) {
            $this->database->addConnection(['driver'=>'sqlite','database'=>$file,'prefix'=>''],$name);
            $manager->connection($name)->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager());
            $manager->connection($name)->getPdo()->exec('PRAGMA foreign_keys=ON');
            $manager->connection($name)->getPdo()->exec('PRAGMA busy_timeout=1');
        }
        $attempted=false; $failure=null;
        try {
            DB::listen(function (QueryExecuted $query) use ($manager,$contender,$prefix,&$attempted,&$failure): void {
                if ($attempted || !str_starts_with($query->sql,$prefix)) return;
                $attempted=true;
                $manager->setDefaultConnection('contender');
                try { $contender(); } catch (\Throwable $e) { $failure=$e; }
                finally { $manager->setDefaultConnection('hardening'); }
            });
            $primary();
            self::assertTrue($attempted);
            self::assertInstanceOf(\Illuminate\Database\QueryException::class,$failure);
            self::assertStringContainsString('locked',$failure->getMessage());
            $manager->setDefaultConnection('contender');
            $contender();
            $manager->setDefaultConnection('hardening');
        } finally {
            $manager->setDefaultConnection('hardening');
            $manager->disconnect('contender'); $manager->disconnect('hardening');
        }
    }
}