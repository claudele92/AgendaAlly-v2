<?php
declare(strict_types=1);
namespace Tests\Hardening;

use Illuminate\Support\Facades\DB;

final class BookingRefundConcurrencyTest extends BookingRefundFixture
{
    public function test_independent_sqlite_connection_cannot_compete_for_uncommitted_refund_claim(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'isolated-booking-refund-');
        $manager = $this->database->getDatabaseManager();
        $pdo = $manager->connection()->getPdo();
        $pdo->exec('VACUUM INTO ' . $pdo->quote($file));
        $manager->purge('hardening');
        foreach (['hardening', 'competing'] as $name) {
            $this->database->addConnection(['driver' => 'sqlite', 'database' => $file, 'prefix' => ''], $name);
        }
        $second = $manager->connection('competing')->getPdo();
        $second->exec('PRAGMA busy_timeout=1');
        $attempted = $blocked = false;
        try {
            DB::listen(function (\Illuminate\Database\Events\QueryExecuted $query) use ($second, &$attempted, &$blocked): void {
                if ($attempted || !str_starts_with($query->sql, 'update "transactions"')) return;
                $attempted = true;
                $second->beginTransaction();
                try {
                    self::assertNull($second->query("SELECT refund_time FROM transactions WHERE payable_type='App\\Models\\Booking' AND payable_id=1")->fetchColumn());
                    $second->exec("UPDATE transactions SET refund_time=CURRENT_TIMESTAMP WHERE payable_type='App\\Models\\Booking' AND payable_id=1 AND refund_time IS NULL");
                } catch (\PDOException $e) {
                    self::assertStringContainsString('locked', $e->getMessage()); $blocked = true;
                } finally { $second->rollBack(); }
            });
            $this->cancel();
            self::assertTrue($attempted); self::assertTrue($blocked);
            self::assertSame(0, $second->exec("UPDATE transactions SET refund_time=CURRENT_TIMESTAMP WHERE payable_type='App\\Models\\Booking' AND payable_id=1 AND refund_time IS NULL"));
            $this->rejects(fn () => $this->cancel());
            self::assertSame(1, \App\Models\WalletHistory::count());
            self::assertSame(75.0, $this->balances()[1]);
        } finally {
            $manager->disconnect('competing'); $manager->disconnect('hardening');
            unset($pdo, $second); unlink($file);
        }
    }
}