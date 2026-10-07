<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\PaymentProcess;
use App\Services\PaymentAccounting\{DurableCollections,FinancialOperations};
use Illuminate\Support\Facades\{DB,Http};
use Illuminate\Support\Str;

class PaymentCompletionContentionTest extends PaymentCompletionFixture
{
    protected array $fixtureConnection=[];
    private ?string $temporaryDatabase=null;

    protected function isolatedDatabaseConfiguration(): array
    {
        $this->temporaryDatabase=sys_get_temp_dir().'/agendaally_completion_'.bin2hex(random_bytes(8)).'.sqlite';
        if (!touch($this->temporaryDatabase)) throw new \RuntimeException('Disposable SQLite fixture could not be created.');
        return $this->fixtureConnection=['driver'=>'sqlite','database'=>$this->temporaryDatabase,'prefix'=>''];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->database->addConnection($this->fixtureConnection,'contender');
        DB::connection('contender')->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
        foreach(['hardening','contender'] as $c) {
            if (DB::connection($c)->getDriverName()==='sqlite') DB::connection($c)->statement('PRAGMA busy_timeout=10');
            else DB::connection($c)->statement('SET SESSION innodb_lock_wait_timeout=1');
        }
    }
    protected function tearDown(): void
    {
        if(isset($this->database)) {
            $this->database->getDatabaseManager()->setDefaultConnection('hardening');
            DB::disconnect('contender');
        }
        parent::tearDown();
        if($this->temporaryDatabase) @unlink($this->temporaryDatabase);
    }
    public static function paths(): array
    {
        return array_map(fn($x)=>[$x],['attempt','claim','provider_finalization','refund_reservation',
            'refund_finalization','receivable','payable_reservation','payout_finalization_blocked']);
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('paths')]
    public function test_two_connections_serialize_durable_authority_and_replay(string $path): void
    {
        $collections=new DurableCollections;$operations=new FinancialOperations;
        [$allocation,$context,$revision,$attempt]=$this->funded($path==='receivable'?'offline':'platform','paystack',
            $path!=='attempt',!in_array($path,['attempt','claim','provider_finalization'],true));
        $key=(string)Str::uuid();
        $op=null;
        if($path==='refund_finalization') {
            $op=$operations->reserve($allocation,'refund',$key,2500,2,$context);
            Http::fake(['api.paystack.co/refund*'=>fn($r)=>Http::response(['status'=>true,'data'=>[
                'id'=>72,'transaction'=>'original-payment','amount'=>2500,'currency'=>'USD',
                'status'=>$r->method()==='POST'?'pending':'processed']])]);
            $operations->refund($op->id);
        }
        if($path==='receivable') $op=$operations->reserve($allocation,'receivable',$key,1000,2);
        if($path==='payout_finalization_blocked') $op=$operations->reserve($allocation,'payout',$key,9000,2);
        if($path==='provider_finalization') $collections->claim($attempt->id);
        $receipt=['source'=>'cash_receipt','receipt_reference'=>'synthetic-receipt',
            'document_sha256'=>str_repeat('c',64),'retained_evidence'=>'Synthetic cash receipt with retained provenance.',
            'received_at'=>'2026-10-03 10:00:00'];
        $action=function(bool $retry=false) use($path,$collections,$operations,$allocation,$context,$attempt,$key,$op,$receipt) {
            return match($path) {
                'attempt'=>$collections->reserve($this->before($context),'paystack'),
                'claim'=>$collections->claim($attempt->id),
                'provider_finalization'=>$this->finalize($context,$attempt->id),
                'refund_reservation'=>$operations->reserve($allocation,'refund',$retry?(string)Str::uuid():$key,$retry?5000:6000,2,$context),
                'refund_finalization'=>$operations->refund($op->id,true),
                'receivable'=>$operations->receipt($op->id,$receipt),
                'payable_reservation'=>$operations->reserve($allocation,'payout',$retry?(string)Str::uuid():$key,9000,2),
                'payout_finalization_blocked'=>$operations->refund($op->id,true),
            };
        };
        if($path==='payout_finalization_blocked') {
            foreach(['hardening','contender'] as $connection) {
                $this->database->getDatabaseManager()->setDefaultConnection($connection);
                try {$action();self::fail('No external payout authority exists.');}catch(\DomainException $e){}
            }
            self::assertSame(0,DB::table('platform_fee_ledger_entries')->where('effect_kind','vendor_settlement')->count());
            self::assertSame('RESERVED',DB::table('payment_financial_operations')->where('id',$op->id)->value('state'));
            return;
        }
        $owner=DB::connection('hardening');$owner->beginTransaction();
        try {
            $first=$action();
            $this->database->getDatabaseManager()->setDefaultConnection('contender');
            try {$action(true);self::fail('Contender must not pass the uncommitted authority write.');}
            catch(\Illuminate\Database\QueryException|\Illuminate\Database\DeadlockException $e) {
                self::assertTrue(str_contains(strtolower($e->getMessage()),'locked')
                    || str_contains(strtolower($e->getMessage()),'lock wait')
                    || str_contains(strtolower($e->getMessage()),'deadlock'));
            }
            $owner->commit();
            if(in_array($path,['refund_reservation','payable_reservation'],true)) {
                try{$action(true);self::fail('Remaining capacity must reject replay/new reservation.');}catch(\DomainException $e){}
                self::assertSame(1,DB::table('payment_financial_operations')->count());
            } else {
                $again=$action(true);
                if($path==='attempt') {self::assertSame($first->id,$again->id);self::assertSame(1,DB::table('electronic_collection_attempts')->count());}
                if($path==='claim') {self::assertTrue($first);self::assertFalse($again);}
                if($path==='provider_finalization') {self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());}
                if($path==='refund_finalization') {self::assertSame('SUCCESS',$again->state);
                    self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('effect_kind','refund_principal')->count());}
                if($path==='receivable') {self::assertSame('SUCCESS',$again->state);
                    self::assertSame(1,DB::table('payment_receipt_evidence')->count());
                    self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('effect_kind','receivable_collection')->count());}
            }
        } finally {
            if($owner->transactionLevel()>0) $owner->rollBack();
            $this->database->getDatabaseManager()->setDefaultConnection('hardening');
        }
    }
    private function finalize(int $context,string $attempt): void
    {
        DB::transaction(function()use($context,$attempt):void{
            $this->writer->confirm([$context],'synthetic-receipt',10000);
            (new DurableCollections)->finalized(PaymentProcess::findOrFail($attempt),'paid',[
                'authenticated'=>true,'merchant_verified'=>true,'provider_payment_id'=>'original-payment']);
        },3);
    }
}