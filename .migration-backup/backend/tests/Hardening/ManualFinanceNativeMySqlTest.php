<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\ManualFinance\{WorkflowService,WorkflowQueries};
use App\Services\PaymentAccounting\{AllocationBalances,FinancialOperations};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Only our local --no-defaults MySQL listener and a new disposable database. */
final class ManualFinanceNativeMySqlTest extends ManualFinancialWorkflowFixture
{
    private ?string $ownedDatabase = null;

    protected function isolatedDatabaseConfiguration(): array
    {
        // Independent validation/review runs may overlap. Never clear another
        // runner's schema; race workers inherit this one test's configuration.
        $db='agendaally_manual_finance_disposable_'.getmypid().'_'.bin2hex(random_bytes(6));
        $pdo=new \PDO('mysql:host=127.0.0.1;port=33308','root','',[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->ownedDatabase=$db;
        $pdo->exec("USE `$db`");
        if ($pdo->query('SELECT DATABASE()')->fetchColumn()!==$db) throw new \RuntimeException('Wrong owned database.');
        if ($pdo->query('SHOW TABLES')->fetchColumn()!==false) throw new \RuntimeException('New owned database must be empty.');
        return ['driver'=>'mysql','host'=>'127.0.0.1','port'=>33308,'database'=>$db,'username'=>'root','password'=>'',
            'charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true];
    }
    protected function setUp(): void
    {
        parent::setUp();
        DB::connection()->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
        DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        DB::statement('SET SESSION innodb_lock_wait_timeout=3');
    }
    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            if ($this->ownedDatabase!==null) {
                $db=$this->ownedDatabase;
                if (!preg_match('/^agendaally_manual_finance_disposable_[0-9]+_[a-f0-9]{12}$/D',$db)) {
                    throw new \RuntimeException('Refusing cleanup outside this test-owned schema.');
                }
                $pdo=new \PDO('mysql:host=127.0.0.1;port=33308','root','',[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("DROP DATABASE `$db`");
                $this->ownedDatabase=null;
            }
        }
    }
    private function race(callable $one,callable $two): array
    {
        if (!function_exists('pcntl_fork')) self::markTestSkipped('Native process concurrency unavailable.');
        $dir=sys_get_temp_dir().'/manual-race-'.Str::uuid(); mkdir($dir);
        DB::purge('hardening');
        $pids=[];
        foreach ([$one,$two] as $i=>$work) {
            $pid=pcntl_fork();
            if ($pid===0) {
                while (!file_exists($dir.'/start')) usleep(1000);
                DB::purge('hardening');
                DB::connection()->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
                try { $row=$work(); $result=['ok'=>true,'id'=>$row['id'],'state'=>$row['state']]; }
                catch (\Throwable $e) { $result=['ok'=>false,'class'=>get_class($e)]; }
                file_put_contents($dir.'/'.$i.'.json',json_encode($result)); exit(0);
            }
            if ($pid<0) throw new \RuntimeException('Native process could not start.');
            $pids[]=$pid;
        }
        touch($dir.'/start');
        foreach ($pids as $pid) pcntl_waitpid($pid,$status);
        DB::purge('hardening');
        DB::connection()->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
        $results=[json_decode(file_get_contents($dir.'/0.json'),true),json_decode(file_get_contents($dir.'/1.json'),true)];
        foreach (glob($dir.'/*') as $path) unlink($path); rmdir($dir);
        return $results;
    }
    public function test_native_manual_simultaneous_refund_and_payout_reservations(): void
    {
        [$id,$c]=$this->funded();
        $r=$this->requestInput($id,$c); $p=$this->requestInput($id,$c,'payout');
        $results=$this->race(fn()=>(new WorkflowService)->request($this->customer,$r),fn()=>(new WorkflowService)->request($this->vendor,$p));
        self::assertSame(1,count(array_filter($results,fn($r)=>$r['ok'])));
        self::assertSame(1,DB::table('manual_financial_workflows')->count());
        self::assertSame(1,DB::table('payment_financial_operations')->count());
        self::assertSame(1,DB::table('manual_financial_events')->count());
    }
    public function test_native_manual_simultaneous_claims_then_same_completion_intent(): void
    {
        $w=$this->approved();
        $one=$this->command($w); $two=$this->command($w);
        $results=$this->race(fn()=>(new WorkflowService)->action($this->finance,$w['id'],'claim',$one),
            fn()=>(new WorkflowService)->action($this->finance,$w['id'],'claim',$two));
        self::assertSame(1,count(array_filter($results,fn($r)=>$r['ok'])));
        $w=(new \App\Services\ManualFinance\WorkflowQueries)->safe($this->finance,DB::table('manual_financial_workflows')->first(),false);
        $complete=$this->command($w)+['evidence'=>$this->terminalEvidence($w)];
        $results=$this->race(fn()=>(new WorkflowService)->action($this->finance,$w['id'],'complete',$complete),
            fn()=>(new WorkflowService)->action($this->finance,$w['id'],'complete',$complete));
        self::assertSame(2,count(array_filter($results,fn($r)=>$r['ok'])));
        self::assertSame(1,DB::table('manual_financial_evidence')->count());
        self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('effect_kind','refund_principal')->count());
        self::assertSame(4,DB::table('manual_financial_events')->count());
        self::assertSame(0,(new FinancialOperations)->reserved($w['allocation_id'],'refund'));
    }
    public function test_native_manual_required_write_failure_and_terminal_uniqueness(): void
    {
        $w=$this->claimed();
        DB::unprepared("CREATE TRIGGER injected_manual BEFORE INSERT ON manual_financial_events FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic audit write failure'");
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)+['evidence'=>$this->terminalEvidence($w)]));
        DB::unprepared('DROP TRIGGER injected_manual');
        $done=$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)+['evidence'=>$this->terminalEvidence($w)]);
        self::assertSame('COMPLETED',$done['state']);
        self::assertSame(10000,(new AllocationBalances)->current($w['allocation_id'])['refunded']);
        $this->denies(fn()=>DB::table('manual_financial_evidence')->delete());
        $this->denies(fn()=>DB::table('manual_financial_workflows')->where('id',$w['id'])->update(['state'=>'APPROVED']));
    }
    public function test_native_manual_same_reference_competition_across_sources(): void
    {
        $one=$this->claimed();
        $this->checkout=(string)Str::uuid();
        DB::table('bookings')->insert(['id'=>2,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,
            'total_price'=>100,'status'=>'ended','start_date'=>now()->subHour()]);
        [$id,$c]=$this->funded(sourceId:2);
        $two=$this->manual->request($this->customer,$this->requestInput($id,$c));
        $two=$this->manual->action($this->finance,$two['id'],'approve',$this->command($two));
        $two=$this->manual->action($this->finance,$two['id'],'claim',$this->command($two));
        $proofOne=$this->terminalEvidence($one);
        $proofTwo=array_replace($this->terminalEvidence($two),['external_reference'=>$proofOne['external_reference']]);
        $commandOne=$this->command($one)+['evidence'=>$proofOne];
        $commandTwo=$this->command($two)+['evidence'=>$proofTwo];
        $results=$this->race(fn()=>(new WorkflowService)->action($this->finance,$one['id'],'complete',$commandOne),
            fn()=>(new WorkflowService)->action($this->finance,$two['id'],'complete',$commandTwo));
        self::assertSame(1,count(array_filter($results,fn($r)=>$r['ok'])));
        self::assertSame(1,DB::table('manual_financial_evidence')->count());
        self::assertSame(1,DB::table('manual_financial_workflows')->where('state','COMPLETED')->count());
        self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('effect_kind','refund_principal')->count());
        self::assertSame('10000',(string)DB::table('payment_financial_operations')->where('state','UNKNOWN')->sum('amount_units'));
    }
    public function test_native_manual_retry_rolls_back_failed_mutex_attempt(): void
    {
        [$id,$c]=$this->funded();
        $failures=0;
        DB::listen(function($query) use(&$failures) {
            if ($failures===0 && str_starts_with(strtolower($query->sql),'update `commerce_payment_allocations`')) {
                $failures++;
                throw new \Illuminate\Database\DeadlockException('Deadlock found when trying to get lock; synthetic retry fault');
            }
        });
        $result=$this->manual->request($this->customer,$this->requestInput($id,$c));
        self::assertSame(1,$failures);
        self::assertSame('REQUESTED',$result['state']);
        self::assertSame(1,DB::table('manual_financial_workflows')->count());
        self::assertSame(1,DB::table('manual_financial_commands')->count());
        self::assertSame(1,DB::table('manual_financial_events')->count());
    }

    public function test_native_policy_distinct_intents_share_fee_limited_authority(): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$id,$c]=$this->funded();
        $one=$this->policyRequest($id,$c); $two=$this->policyRequest($id,$c);
        $results=$this->race(fn()=>(new WorkflowService)->request($this->customer,$one),
            fn()=>(new WorkflowService)->request($this->customer,$two));
        self::assertSame(1,count(array_filter($results,fn($r)=>$r['ok'])));
        self::assertSame(1,DB::table('manual_financial_workflows')->count());
        self::assertSame(5000,(new FinancialOperations)->reserved($id,'refund',$c));
        $w=(new WorkflowQueries)->safe($this->finance,DB::table('manual_financial_workflows')->first(),false);
        $this->finishPolicyRefund($w);
        self::assertSame(5000,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(0,(new FinancialOperations)->reserved($id,'refund',$c));
        self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('effect_kind','refund_principal')->count());
        $this->denies(fn()=>$this->manual->request($this->customer,$this->policyRequest($id,$c)));
    }

    public function test_native_policy_forward_boundaries_reject_and_roll_back_overheld_authority(): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        foreach (self::policyForwardBoundaries() as $index=>[$boundary]) {
            $sourceId=$index+2;
            $this->checkout=(string)Str::uuid();
            DB::table('bookings')->insert(['id'=>$sourceId,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,
                'total_price'=>100,'status'=>'ended','start_date'=>now()->subHour()]);
            [$id,$c]=$this->funded(sourceId:$sourceId);
            $this->assertPolicyForwardBoundary($boundary,$id,$c);
        }
    }
}
