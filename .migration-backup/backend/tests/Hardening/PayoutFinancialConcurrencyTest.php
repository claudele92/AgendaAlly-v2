<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Payout;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

final class PayoutFinancialConcurrencyTest extends PayoutFinancialFixture
{
    public function test_two_independent_sqlite_connections_cannot_both_settle_the_same_payout(): void
    {
        $manager = $this->app['db'];
        $file = tempnam(sys_get_temp_dir(), 'native-payout-contention-');
        $manager->connection()->getPdo()->exec('VACUUM INTO ' . $manager->connection()->getPdo()->quote($file));
        $manager->purge('hardening');
        foreach (['hardening', 'contender'] as $name) {
            $this->database->addConnection(['driver' => 'sqlite', 'database' => $file, 'prefix' => ''], $name);
            $manager->connection($name)->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager());
        }
        $second = $manager->connection('contender')->getPdo();
        $second->exec('PRAGMA busy_timeout=1');
        $attempted = false;
        $contender = null;
        try {
            DB::listen(function (QueryExecuted $query) use ($manager, &$attempted, &$contender): void {
                if ($attempted || !str_starts_with($query->sql, 'update "payouts"')) return;
                $attempted = true;
                $manager->setDefaultConnection('contender');
                try {
                    self::assertSame('pending', Payout::findOrFail(1)->status);
                    $contender = $this->approve();
                } finally { $manager->setDefaultConnection('hardening'); }
            });
            self::assertTrue($this->approve()['status']);
            self::assertTrue($attempted);
            self::assertFalse($contender['status']);
            self::assertStringContainsString('locked', $contender['message']);
            $this->assertPayoutBatch();
            $before = $this->payoutState();
            $manager->setDefaultConnection('contender');
            self::assertSame('accepted', Payout::findOrFail(1)->status);
            self::assertFalse($this->approve()['status']);
            self::assertSame($before, $this->payoutState());
            self::assertSame(0, $second->exec("UPDATE payouts SET status='accepted' WHERE id=1 AND status='pending'"));
        } finally {
            $manager->setDefaultConnection('hardening');
            $manager->disconnect('contender'); $manager->disconnect('hardening');
            unset($second); unlink($file);
        }
    }
}