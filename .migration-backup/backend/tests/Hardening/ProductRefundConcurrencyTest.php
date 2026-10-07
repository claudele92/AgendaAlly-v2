<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\OrderRefund;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

final class ProductRefundConcurrencyTest extends ProductRefundFixture
{
    public function test_competing_sqlite_connection_cannot_claim_while_first_settlement_is_uncommitted(): void
    {
        // Only a copy of this process's synthetic fixture, never the owned DB.
        $file = tempnam(sys_get_temp_dir(), 'isolated-refund-');
        $manager = $this->database->getDatabaseManager();
        $manager->connection()->getPdo()->exec('VACUUM INTO ' . $manager->connection()->getPdo()->quote($file));
        $manager->purge('hardening');
        $this->database->addConnection(['driver' => 'sqlite', 'database' => $file, 'prefix' => ''], 'hardening');
        $this->database->addConnection(['driver' => 'sqlite', 'database' => $file, 'prefix' => ''], 'competing');
        $second = $manager->connection('competing')->getPdo();
        $second->exec('PRAGMA busy_timeout=1');
        $attempted = false;
        $blocked = false;
        try {
            DB::listen(function (QueryExecuted $query) use ($second, &$attempted, &$blocked): void {
                if ($attempted || !str_starts_with($query->sql, 'update "order_refunds"')) return;
                $attempted = true;
                $second->beginTransaction();
                try {
                    // A separate connection still sees the committed pending
                    // state, but cannot upgrade that stale read into a write.
                    self::assertSame('pending', $second->query('SELECT status FROM order_refunds WHERE id=1')->fetchColumn());
                    $second->exec("UPDATE order_refunds SET status='accepted' WHERE id=1 AND status='pending'");
                } catch (\PDOException $e) {
                    self::assertStringContainsString('locked', $e->getMessage());
                    $blocked = true;
                } finally {
                    $second->rollBack();
                }
            });
            $result = $this->settle();
            self::assertTrue($result['status'], json_encode($result));
            self::assertTrue($attempted);
            self::assertTrue($blocked);
            self::assertSame(0, $second->exec("UPDATE order_refunds SET status='accepted' WHERE id=1 AND status='pending'"));
            $before = $this->persistedState();
            self::assertFalse($this->settle([], (new OrderRefund())->forceFill(['id' => 1, 'status' => 'pending']))['status']);
            self::assertSame($before, $this->persistedState());
            self::assertSame(1, \App\Models\WalletHistory::count());
            self::assertSame([100.0, 80.0, 80.0], $this->balances());
        } finally {
            $manager->disconnect('competing');
            $manager->disconnect('hardening');
            unset($second);
            unlink($file);
        }
    }

    public function test_exception_after_atomic_claim_before_credit_rolls_back_the_terminal_marker(): void
    {
        DB::listen(function (QueryExecuted $query): void {
            if (str_starts_with($query->sql, 'update "order_refunds"')) {
                throw new \RuntimeException('isolated after-claim failure');
            }
        });
        $before = $this->persistedState();
        self::assertFalse($this->settle()['status']);
        self::assertSame($before, $this->persistedState());
    }
}