<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\AccountingEffects;
use App\Services\PaymentAccounting\AllocationBalances;
use App\Services\PaymentAccounting\AllocationWriter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Opt-in, loopback-only, initially EMPTY disposable MySQL database.
 * Never boots application production configuration or reads production secrets.
 * Creates and drops only its isolated fixture tables. No provider transport.
 */
final class PaymentAccountingMySqlContentionTest extends PaymentAccountingFixture
{
    private bool $ownsFixture = false;
    private array $mysql = [];

    protected function isolatedDatabaseConfiguration(): array
    {
        if (getenv('PAYMENT_TEST_MYSQL_ENABLE') !== '1') {
            self::markTestSkipped('Disposable MySQL certification not enabled; SQLite is not production certification.');
        }
        $name = (string) getenv('PAYMENT_TEST_MYSQL_DATABASE');
        if (!preg_match('/^agendaally_payment_disposable_[a-z0-9]+$/D', $name)) {
            self::fail('Use an explicitly named, empty agendaally_payment_disposable_* database.');
        }
        $port = (string) (getenv('PAYMENT_TEST_MYSQL_PORT') ?: '3306');
        if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) self::fail('Invalid local test port.');
        $this->mysql = [
            'driver'=>'mysql','host'=>'127.0.0.1','port'=>(int)$port,'database'=>$name,
            'username'=>(string)getenv('PAYMENT_TEST_MYSQL_USER'),
            'password'=>(string)getenv('PAYMENT_TEST_MYSQL_PASSWORD'),
            'charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true,
        ];
        try {
            $pdo = new \PDO("mysql:host=127.0.0.1;port={$port};dbname={$name}",
                $this->mysql['username'], $this->mysql['password']);
            $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
            if (str_contains(strtolower($version), 'mariadb') || version_compare($version, '8.0.16', '<')) {
                self::fail('This prepared harness requires MySQL 8.0.16+ enforced CHECK constraints, not MariaDB.');
            }
            if ($pdo->query('SHOW TABLES')->fetchColumn() !== false) self::fail('Disposable test database must be initially empty.');
        } catch (\PDOException $e) {
            self::fail('Cannot connect to the local disposable MySQL database. Connection details are redacted.');
        }
        $this->ownsFixture = true;
        return $this->mysql;
    }

    protected function tearDown(): void
    {
        if ($this->ownsFixture && isset($this->database)) {
            $manager=$this->database->getDatabaseManager();
            $manager->setDefaultConnection('hardening');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            try {
                foreach (DB::select('SHOW TABLES') as $table) {
                    $name=array_values((array)$table)[0];
                    DB::statement('DROP TABLE `'.str_replace('`','``',$name).'`');
                }
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                $manager->disconnect('contender');
            }
        }
        parent::tearDown();
    }

    public static function paths(): array
    {
        return [['economic_creation'],['contribution'],['confirmation'],['refund'],['vendor_settlement']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('paths')]
    public function test_real_mysql_locks_and_once_only_accounting(string $path): void
    {
        self::assertSame('mysql', DB::connection()->getDriverName());
        $quote=$this->quote();
        if ($path === 'economic_creation') {
            $operation=fn()=>(new AllocationWriter)->commit($quote);
        } else {
            $id=$this->writer->commit($quote);
            $evidence=$this->evidence('platform',10000);
            if ($path === 'contribution') {
                $operation=fn()=>(new AllocationWriter)->stage($id,$evidence);
            } else {
                $context=$this->writer->stage($id,$evidence);
                if ($path === 'confirmation') {
                    $operation=fn()=>(new AllocationWriter)->confirm([$context],'synthetic-receipt',10000);
                } else {
                    $this->writer->confirm([$context],'synthetic-receipt',10000);
                    $group=(string)Str::uuid();
                    $proof=['authority'=>'synthetic_engine_test','operation'=>$group];
                    $effects=$path === 'refund' ? [
                        ['kind'=>'refund_principal','context_id'=>$context,'amount'=>5000,'proof'=>$proof],
                        ['kind'=>'commission_reversal','amount'=>500,'proof'=>$proof],
                    ] : [['kind'=>'vendor_settlement','amount'=>2000,'proof'=>$proof]];
                    $operation=fn()=>(new AccountingEffects)->append($id,$group,$effects);
                }
            }
        }
        $this->contend($operation);
        self::assertSame(1,DB::table('commerce_payment_allocations')->count());
        if ($path !== 'economic_creation') self::assertSame(1,DB::table('payment_collection_contexts')->count());
        if ($path === 'confirmation') {
            self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
            self::assertSame(9000,(new AllocationBalances)->current($id)['vendor_payable']);
        } elseif (isset($group)) {
            self::assertSame($path === 'refund' ? 2 : 1,DB::table('platform_fee_ledger_entries')->where('event_group_key',$group)->count());
            self::assertSame($path === 'refund' ? 4500 : 7000,(new AllocationBalances)->current($id)['vendor_payable']);
        }
    }

    private function contend(callable $operation): void
    {
        $manager=$this->database->getDatabaseManager();
        $this->database->addConnection($this->mysql,'contender');
        $manager->connection('contender')->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager());
        $manager->connection('contender')->statement('SET SESSION innodb_lock_wait_timeout=1');
        $attempted=false; $errorCode=null;
        DB::listen(function(QueryExecuted $query) use($operation,$manager,&$attempted,&$errorCode): void {
            if ($attempted || !preg_match('/^(insert|update)\\b/i',$query->sql)) return;
            $attempted=true;
            $manager->setDefaultConnection('contender');
            try {
                $operation();
            } catch (\Illuminate\Database\QueryException $e) {
                // Do not print SQL bindings, connection settings or credentials.
                $errorCode=(int)($e->errorInfo[1] ?? 0);
            } finally {
                $manager->setDefaultConnection('hardening');
            }
        });
        DB::transaction(fn()=>$operation());
        self::assertTrue($attempted);
        self::assertContains($errorCode,[1205,1213],'Contender must lose to a real InnoDB lock, not a synthetic error.');
        try {
            $manager->setDefaultConnection('contender');
            $operation(); // Replay after the first commit; must not double effects.
        } finally {
            $manager->setDefaultConnection('hardening');
        }
    }
}