<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\User\OrderController;
use App\Models\Order;
use App\Services\OrderService\OrderFulfillmentAuthority;
use App\Services\OrderService\OrderStatusUpdateService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/** No native kernel, owned database, provider transport or financial data used. */
final class ProductFulfillmentAuthorityTest extends ProductFulfillmentFixture
{
    private function customerRequest(int $id, array $input): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create("/fixture/customer/orders/$id/status/change", 'POST', $input);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        try {
            return $this->app['router']->dispatch($request);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    private function state(): array
    {
        $state = $this->persistedState();
        foreach (['orders', 'order_status_notes', 'point_histories'] as $table) {
            $state[$table] = json_encode($this->database->table($table)->orderBy('id')->get(), JSON_THROW_ON_ERROR);
        }
        $state['transaction_events'] = $this->transactionEvents;
        $state['referral_jobs'] = $this->referralJobs;
        return $state;
    }

    public static function forbiddenTransitions(): array
    {
        $cases = [];
        foreach (Order::STATUSES as $status) {
            if ($status === Order::STATUS_CANCELED) continue;
            foreach ([1, 3, 4] as $id) $cases["$status order $id"] = [$id, $status];
        }
        return $cases;
    }

    /** @dataProvider forbiddenTransitions */
    public function test_customer_cannot_initiate_fulfillment_or_other_non_cancellation_transition(int $id, string $status): void
    {
        $before = $this->state();
        $response = $this->customerRequest($id, ['status' => $status]);
        self::assertSame(422, $response->getStatusCode(), $response->getContent());
        self::assertSame($before, $this->state());
        self::assertSame('progress', $this->database->table('transactions')->value('status'));
        self::assertSame(0, $this->database->table('platform_fee_ledger_entries')->count());
    }

    public function test_customer_cannot_cancel_another_customers_order_even_in_same_shop(): void
    {
        $before = $this->state();
        foreach ([3, 4, 999] as $id) {
            self::assertSame(404, $this->customerRequest($id, ['status' => 'canceled'])->getStatusCode());
            self::assertSame($before, $this->state());
        }
    }

    public function test_customer_can_still_cancel_own_new_cash_order_without_settlement(): void
    {
        // Presentation dispatch is queued, never executed by this isolated fixture.
        $bus = $this->app->make(\Illuminate\Contracts\Bus\Dispatcher::class);
        $bus->shouldReceive('dispatch')->andReturn(null);
        $financial = $this->persistedState();
        $response = $this->customerRequest(1, ['status' => 'canceled']);
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertSame('canceled', Order::findOrFail(1)->status);
        self::assertSame('progress', $this->database->table('transactions')->value('status'));
        self::assertSame(0, \App\Models\WalletHistory::count());
        self::assertSame(0, $this->referralJobs);
        self::assertSame(0, $this->database->table('platform_fee_ledger_entries')->count());
        self::assertSame($financial['wallets'], $this->persistedState()['wallets']);
    }

    public function test_customer_cannot_cancel_after_native_new_state_window(): void
    {
        $this->database->table('orders')->where('id', 1)->update(['status' => 'accepted']);
        $before = $this->state();
        self::assertSame(400, $this->customerRequest(1, ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->state());
    }

    public static function actors(): array
    {
        return [
            'owner' => [1, 'seller', 1, true], 'foreign seller' => [3, 'seller', 1, false],
            'granted staff' => [4, 'shop_manager', 1, true], 'view staff' => [5, 'moderator', 1, false],
            'uninvited staff' => [2, 'shop_manager', 1, false], 'cross shop staff' => [4, 'moderator', 2, false],
            'admin' => [3, 'admin', 1, true], 'manager' => [3, 'manager', 1, true],
            'assigned driver' => [3, 'deliveryman', 1, true], 'unassigned driver' => [2, 'deliveryman', 1, false],
            'customer' => [2, 'user', 1, false], 'anonymous' => [null, 'user', 1, false],
            'missing order' => [1, 'seller', 999, false],
        ];
    }

    /** @dataProvider actors */
    public function test_native_actor_target_authority_is_independent_of_finality(?int $id, string $role, int $orderId, bool $allowed): void
    {
        $this->authenticate($id, $role);
        $order = Order::find($orderId) ?? (new Order())->forceFill(['id' => $orderId]);
        $before = $this->state();
        self::assertSame($allowed, OrderFulfillmentAuthority::allows($order));
        if (!$allowed) {
            $result = (new OrderStatusUpdateService())->statusUpdate($order, ['status' => 'delivered']);
            self::assertFalse($result['status']);
            self::assertSame('ERROR_101', $result['code']);
        }
        self::assertSame($before, $this->state());
    }

    public function test_inactive_driver_relationship_cannot_initiate_settlement(): void
    {
        $this->authenticate(3, 'deliveryman');
        $this->database->table('invitations')->where('user_id', 3)->update(['driver_active' => false]);
        self::assertFalse(OrderFulfillmentAuthority::allows(Order::findOrFail(1)));
    }

    public static function legitimateFirstActors(): array
    {
        return [[1, 'seller'], [4, 'shop_manager'], [3, 'admin'], [3, 'manager'], [3, 'deliveryman']];
    }

    /** @dataProvider legitimateFirstActors */
    public function test_legitimate_native_first_delivery_is_not_disabled_by_authority_control(int $id, string $role): void
    {
        $this->authenticate($id, $role);
        $result = (new OrderStatusUpdateService())->statusUpdate(Order::findOrFail(1), ['status' => 'delivered']);
        self::assertTrue($result['status'], json_encode($result));
        self::assertSame('delivered', Order::findOrFail(1)->status);
        self::assertEquals(130, $this->database->table('wallets')->where('id', 3)->value('price'));
        self::assertEquals(30, $this->database->table('wallets')->where('id', 2)->value('price'));
        self::assertSame(1, \App\Models\WalletHistory::count());
        self::assertSame(2, \App\Models\Transaction::count());
        self::assertSame('paid', $this->database->table('transactions')->where('id', 1)->value('status'));
        self::assertSame(1, $this->database->table('platform_fee_ledger_entries')->count());
        self::assertSame(1, $this->referralJobs);
        // This proves first authorized delivery only, NOT replay finality.
    }

    public function test_anonymous_customer_route_is_denied_without_financial_effects(): void
    {
        $this->authenticate(null);
        $before = $this->state();
        self::assertSame(401, $this->customerRequest(1, ['status' => 'delivered'])->getStatusCode());
        self::assertSame($before, $this->state());
    }
}