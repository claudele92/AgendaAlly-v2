<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Order;
use App\Services\OrderService\OrderStatusUpdateService;

abstract class ProductFulfillmentFinalityFixture extends ProductFulfillmentFixture
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->database->table('points')->insert([
            'active' => true, 'value' => 0, 'for' => 'order', 'type' => 'fixed', 'price' => 5,
        ]);
        $this->authenticate(1, 'seller');
    }

    protected function fulfill(?Order $order = null, string $status = 'delivered'): array
    {
        return (new OrderStatusUpdateService())->statusUpdate($order ?? Order::findOrFail(1), ['status' => $status]);
    }

    protected function financialState(): array
    {
        $state = $this->persistedState();
        unset($state['orders'], $state['order_status_notes']);
        $state['_transaction_events'] = $this->transactionEvents;
        return $state;
    }

    protected function assertFirstBatch(): void
    {
        self::assertSame('settled', Order::findOrFail(1)->fulfillment_financial_state);
        self::assertEquals(130, $this->database->table('wallets')->where('id', 3)->value('price'));
        self::assertEquals(35, $this->database->table('wallets')->where('id', 2)->value('price'));
        self::assertSame(2, $this->database->table('wallet_histories')->count());
        self::assertSame(3, $this->database->table('transactions')->count());
        self::assertSame('paid', $this->database->table('transactions')->where('id', 1)->value('status'));
        self::assertSame(1, $this->database->table('point_histories')->count());
        self::assertSame(1, $this->database->table('platform_fee_ledger_entries')->count());
        self::assertSame(1, $this->referralJobs);
        self::assertSame([0], $this->referralTransactionLevels);
    }
}