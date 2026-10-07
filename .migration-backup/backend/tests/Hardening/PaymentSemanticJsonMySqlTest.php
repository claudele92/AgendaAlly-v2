<?php
declare(strict_types=1);
namespace Tests\Hardening;

use Illuminate\Support\Facades\DB;

final class PaymentSemanticJsonMySqlTest extends PaymentSemanticJsonFixture
{
    private bool $ownsFixture = false;

    protected function isolatedDatabaseConfiguration(): array
    {
        if (getenv('PAYMENT_TEST_MYSQL_ENABLE') !== '1') self::markTestSkipped('Disposable MySQL opt-in required.');
        $name = (string)getenv('PAYMENT_TEST_MYSQL_DATABASE');
        $port = (string)getenv('PAYMENT_TEST_MYSQL_PORT');
        if (!preg_match('/^agendaally_payment_disposable_[a-z0-9]+$/D', $name)
            || !ctype_digit($port) || (int)$port < 1 || (int)$port > 65535) self::fail('Dedicated disposable database required.');
        $config = ['driver'=>'mysql','host'=>'127.0.0.1','port'=>(int)$port,'database'=>$name,
            'username'=>(string)getenv('PAYMENT_TEST_MYSQL_USER'), 'password'=>(string)getenv('PAYMENT_TEST_MYSQL_PASSWORD'),
            'charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true];
        try {
            $pdo = new \PDO("mysql:host=127.0.0.1;port=$port;dbname=$name", $config['username'], $config['password']);
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
}