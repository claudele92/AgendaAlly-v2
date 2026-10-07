<?php
declare(strict_types=1);
namespace Tests\Hardening;

require_once __DIR__.'/MtnDurableAttemptTest.php';

use App\Models\PaymentProcess;
use App\Services\PaymentService\MtnAttempt;
use App\Services\PaymentAccounting\AllocationWriter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

final class MtnDurableConcurrencyTest extends MtnDurableFixture
{
    private array $files=[];
    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->files as $file) @unlink($file);
    }

    public function test_two_connections_reserve_one_canonical_event(): void
    {
        $this->prepareMtn();
        [, $before]=$this->mtn->getPayload($this->input,[],3);
        $operation=fn()=>DB::transaction(fn()=>(new MtnAttempt)->reserve($before,3,$this->mtn->configFingerprint($this->mtn->gateway)));
        $this->contend($operation,'update "commerce_payment_allocations"');
        self::assertSame(1,PaymentProcess::count());
        self::assertSame(1,PaymentProcess::distinct()->count('mtn_funding_event_key'));
        $this->noMtnMoney(); self::assertSame(0,$this->posts);
    }

    public function test_two_connections_initiate_one_committed_reference(): void
    {
        $this->prepareMtn();
        $this->contend(fn()=>$this->mtn->processTransaction($this->input),'UPDATE bookings');
        self::assertSame(1,PaymentProcess::count()); self::assertSame(1,$this->posts);
        self::assertSame(MtnAttempt::ACCEPTED,PaymentProcess::firstOrFail()->mtn_dispatch_state);
        $this->noMtnMoney();
    }

    public function test_two_connections_reconcile_success_once(): void
    {
        $this->prepareMtn(); $p=$this->mtn->processTransaction($this->input);
        $this->contend(fn()=>$this->mtn->reconcileAttempt($p->id),'update "payment_process"',true);
        self::assertSame(1,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(9000,(int)DB::table('commerce_payment_allocations')->value('original_vendor_payable'));
    }

    public function test_two_connections_canonical_finalization_once(): void
    {
        $this->prepareMtn('cart'); $p=$this->mtn->processTransaction($this->input);
        $writer=new AllocationWriter;
        // Trusted synthetic receipt confirms retained Cart funding; no payable
        // yet, so accepted canonical finalization intentionally waits.
        $writer->confirm($p->data['accounting_context_ids'],'provider-receipt:'.$p->id,10000);
        $order=$this->order(); DB::table('orders')->where('id',$order->id)->update(['cart_id'=>1]);
        $a=DB::table('commerce_payment_allocations')->first();
        $writer->bind((int)$a->id,'order',(int)$order->id);
        $this->contend(fn()=>$writer->finalize((int)$a->id),'update "commerce_payment_allocations" set "original_platform_amount"');
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(9000,(int)DB::table('commerce_payment_allocations')->value('original_vendor_payable'));
    }

    private function contend(callable $operation,string $prefix,bool $containedFailure=false): void
    {
        $file=tempnam(sys_get_temp_dir(),'mtn-identity-contention-'); $this->files[]=$file;
        $manager=$this->database->getDatabaseManager();
        $manager->connection()->getPdo()->exec('VACUUM INTO '.$manager->connection()->getPdo()->quote($file));
        $manager->purge('hardening');
        foreach (['hardening','contender'] as $name) {
            $this->database->addConnection(['driver'=>'sqlite','database'=>$file,'prefix'=>''],$name);
            $manager->connection($name)->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
            $manager->connection($name)->getPdo()->exec('PRAGMA foreign_keys=ON; PRAGMA busy_timeout=1');
        }
        $this->independentFile=$file;
        $attempted=false; $failure=null; $result=null;
        DB::listen(function (QueryExecuted $query) use ($operation,$prefix,$manager,&$attempted,&$failure,&$result): void {
            if ($attempted || !str_starts_with($query->sql,$prefix)) return;
            $attempted=true; $manager->setDefaultConnection('contender');
            try { $result=$operation(); } catch (\Throwable $e) { $failure=$e; }
            finally { $manager->setDefaultConnection('hardening'); }
        });
        try {
            $operation(); self::assertTrue($attempted);
            if ($containedFailure) self::assertFalse($result['status']);
            else {
                self::assertInstanceOf(\Illuminate\Database\QueryException::class,$failure);
                self::assertStringContainsString('locked',$failure->getMessage());
            }
            $manager->setDefaultConnection('contender'); $operation();
        } finally {
            $manager->setDefaultConnection('hardening');
            $manager->disconnect('contender');
        }
    }
}