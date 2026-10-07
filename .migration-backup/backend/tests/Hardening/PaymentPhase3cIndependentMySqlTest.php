<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\FinancialOperations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentPhase3cIndependentMySqlTest extends PaymentPhase3cMySqlFixture
{
    public function test_distinct_allocations_progress_while_first_reservation_transaction_remains_open(): void
    {
        [$first, $context] = $this->funded();
        DB::table('bookings')->insert(['id'=>2]);
        $second = $this->writer->commit($this->quote(2));
        $secondContext = $this->writer->stage($second, $this->evidence('offline',10000));
        $this->writer->confirm([$secondContext], 'independent-offline-receipt', 10000);
        self::assertNotSame($first,$second);
        self::assertSame(0,DB::table('payment_financial_operations')->count());
        $reader=$this->reader();
        $reader->statement('SET SESSION innodb_lock_wait_timeout=1');
        $manager=$this->database->getDatabaseManager();
        $owner=DB::connection('hardening');
        $evidence=['first_allocation'=>$first,'second_allocation'=>$second,
            'financial_operation_table_initially_empty'=>true,'provider_requests_made'=>false];
        foreach ([
            'replay'=>"SELECT * FROM payment_financial_operations FORCE INDEX(financial_operation_request_unique)
                WHERE allocation_id=$first AND kind='refund' AND request_key='00000000-0000-4000-8000-000000000001'
                LIMIT 1 LOCK IN SHARE MODE",
            'reserved'=>"SELECT amount_units FROM payment_financial_operations
                FORCE INDEX(payment_financial_operations_allocation_id_kind_state_index)
                WHERE allocation_id=$first AND kind='refund' AND state IN ('RESERVED','UNKNOWN','PENDING')
                ORDER BY state,id LOCK IN SHARE MODE",
        ] as $key=>$sql) $evidence['access_plans'][$key]=DB::select('EXPLAIN '.$sql);
        $owner->beginTransaction();
        try {
            (new FinancialOperations)->reserve($first,'refund',(string)Str::uuid(),1000,2,$context);
            $evidence['first_parent_mutex_held']=true;
            $manager->setDefaultConnection('snapshot_reader');
            // Prove the independent parent row itself is not the blocking resource.
            $reader->beginTransaction();
            $evidence['second_parent_lock_acquired']=(bool)DB::table('commerce_payment_allocations')
                ->where('id',$second)->lockForUpdate()->first();
            $evidence['second_accepted']=false;
            try {
                (new FinancialOperations)->reserve($second,'receivable',(string)Str::uuid(),100,2);
                $evidence['second_accepted']=true;
                $reader->commit();
            } catch (\Illuminate\Database\QueryException|\Illuminate\Database\DeadlockException $e) {
                $evidence['second_error_class']=$e::class;
                $evidence['second_driver_code']=$e->errorInfo[1]??null;
                while ($reader->transactionLevel()>0) $reader->rollBack();
            }
            // Dedicated loopback fixture account has read-only lock-monitor access.
            $evidence['locks']=$reader->select("SELECT OBJECT_NAME,INDEX_NAME,LOCK_TYPE,LOCK_MODE,LOCK_STATUS,LOCK_DATA
                FROM performance_schema.data_locks WHERE OBJECT_SCHEMA=? ORDER BY OBJECT_NAME,INDEX_NAME,LOCK_DATA",
                [$this->mysql['database']]);
            $manager->setDefaultConnection('hardening');
            $owner->commit();
            $evidence['committed_operations']=DB::table('payment_financial_operations')
                ->orderBy('allocation_id')->get(['allocation_id','kind','amount_units','state'])->all();
            file_put_contents(__DIR__.'/../../../../.local/payment-phase3c-reservation-independent-evidence.json',
                json_encode($evidence,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n");
            self::assertTrue($evidence['second_parent_lock_acquired']);
            self::assertTrue($evidence['second_accepted'],
                'An unrelated allocation must progress before the first reservation transaction commits.');
        } finally {
            while ($reader->transactionLevel()>0) $reader->rollBack();
            while ($owner->transactionLevel()>0) $owner->rollBack();
            $manager->setDefaultConnection('hardening');
        }
    }
}