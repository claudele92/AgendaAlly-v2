<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Order;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

final class ProductFulfillmentConcurrencyTest extends ProductFulfillmentFinalityFixture
{
    public function test_two_legitimate_sqlite_connections_compete_for_one_claim_and_one_financial_batch(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'isolated-fulfillment-');
        $manager = $this->database->getDatabaseManager();
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
                if ($attempted || !str_starts_with($query->sql, 'update "orders"')
                    || !str_contains($query->sql, '"fulfillment_financial_state"')) return;
                $attempted = true;
                $manager->setDefaultConnection('contender');
                try {
                    $order = Order::findOrFail(1);
                    self::assertSame('pending', $order->fulfillment_financial_state);
                    self::assertTrue(\App\Services\OrderService\OrderFulfillmentAuthority::allows($order));
                    // Full native second service attempt, not merely a competing raw UPDATE.
                    $contender = $this->fulfill($order);
                } finally {
                    $manager->setDefaultConnection('hardening');
                }
            });
            self::assertTrue($this->fulfill()['status']);
            self::assertTrue($attempted);
            self::assertFalse($contender['status']);
            self::assertStringContainsString('locked', $contender['message']);
            $this->assertFirstBatch();
            $before = $this->financialState();
            $manager->setDefaultConnection('contender');
            self::assertSame('settled', Order::findOrFail(1)->fulfillment_financial_state);
            self::assertTrue($this->fulfill(null, 'ready')['status']);
            self::assertTrue($this->fulfill()['status']);
            self::assertSame($before, $this->financialState());
            self::assertSame(0, $second->exec("UPDATE orders SET fulfillment_financial_state='settled' WHERE id=1 AND fulfillment_financial_state='pending'"));
            $this->assertFirstBatch();
        } finally {
            $manager->setDefaultConnection('hardening');
            $manager->disconnect('contender');
            $manager->disconnect('hardening');
            unset($second);
            unlink($file);
        }
    }
}