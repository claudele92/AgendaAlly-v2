<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\AccountingSchema;
use Illuminate\Support\Facades\DB;

final class PaymentReceiptAnchorMySqlGuardTest extends PaymentReceiptAnchorGuardFixture
{
    private bool $ownsFixture = false;

    protected function isolatedDatabaseConfiguration(): array
    {
        if (getenv('PAYMENT_TEST_MYSQL_ENABLE') !== '1') self::markTestSkipped('Disposable MySQL opt-in required.');
        $name = (string)getenv('PAYMENT_TEST_MYSQL_DATABASE');
        $port = (string)getenv('PAYMENT_TEST_MYSQL_PORT');
        if (!preg_match('/^agendaally_payment_disposable_[a-z0-9]+$/D',$name)
            || !ctype_digit($port) || (int)$port<1 || (int)$port>65535) self::fail('Dedicated disposable database required.');
        $config = ['driver'=>'mysql','host'=>'127.0.0.1','port'=>(int)$port,'database'=>$name,
            'username'=>(string)getenv('PAYMENT_TEST_MYSQL_USER'),'password'=>(string)getenv('PAYMENT_TEST_MYSQL_PASSWORD'),
            'charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true];
        try {
            $pdo = new \PDO("mysql:host=127.0.0.1;port=$port;dbname=$name",$config['username'],$config['password']);
            $version = (string)$pdo->query('SELECT VERSION()')->fetchColumn();
            if (str_contains(strtolower($version),'mariadb') || version_compare($version,'8.0.16','<')) self::fail('MySQL 8.0.16+ required.');
            if ($pdo->query('SHOW TABLES')->fetchColumn() !== false) self::fail('Initially empty disposable database required.');
        } catch (\PDOException $e) { self::fail('Disposable MySQL unavailable; details redacted.'); }
        $this->ownsFixture = true;
        return $config;
    }

    protected function tearDown(): void
    {
        if ($this->ownsFixture && isset($this->database)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            try {
                foreach (DB::select('SHOW TABLES') as $row) {
                    $name = array_values((array)$row)[0];
                    DB::statement('DROP TABLE `'.str_replace('`','``',$name).'`');
                }
            } finally { DB::statement('SET FOREIGN_KEY_CHECKS=1'); }
        }
        parent::tearDown();
    }

    public function test_existing_valid_table_guard_installation_preserves_rows(): void
    {
        $root = $this->root();
        DB::statement('DROP TRIGGER pcc_receipt_anchor_insert');
        DB::statement('DROP TRIGGER pcc_receipt_anchor_update');
        AccountingSchema::installMySqlReceiptAnchorGuard();
        self::assertSame(2,(int)DB::selectOne("SELECT COUNT(*) AS n FROM information_schema.TRIGGERS
            WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME IN ('pcc_receipt_anchor_insert','pcc_receipt_anchor_update')")->n);
        self::assertSame($root,(array)DB::table('payment_collection_contexts')->find($root['id']));
        $patch = $this->child($root,(int)$root['id']);
        unset($patch['funding_key'],$patch['funding_event_key']);
        $this->rejectsSelf(fn()=>DB::table('payment_collection_contexts')->where('id',$root['id'])->update($patch));
        self::assertSame($root,(array)DB::table('payment_collection_contexts')->find($root['id']));
    }

    public function test_existing_invalid_synthetic_row_blocks_guard_installation_without_rewriting(): void
    {
        $root = $this->root();
        DB::statement('DROP TRIGGER pcc_receipt_anchor_insert');
        DB::statement('DROP TRIGGER pcc_receipt_anchor_update');
        DB::table('payment_collection_contexts')->insert($this->child($root,77,77));
        $before = DB::table('payment_collection_contexts')->orderBy('id')->get()->toArray();
        try { AccountingSchema::installMySqlReceiptAnchorGuard(); self::fail('Invalid preexisting rows must fail closed.'); }
        catch (\RuntimeException $e) { self::assertStringContainsString('self-anchor prevents guard installation',$e->getMessage()); }
        self::assertEquals($before,DB::table('payment_collection_contexts')->orderBy('id')->get()->toArray());
        self::assertSame(0,(int)DB::selectOne("SELECT COUNT(*) AS n FROM information_schema.TRIGGERS
            WHERE TRIGGER_SCHEMA=DATABASE() AND TRIGGER_NAME LIKE 'pcc_receipt_anchor_%'")->n);
    }
}