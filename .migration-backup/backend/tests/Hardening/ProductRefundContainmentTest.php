<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\WalletHistory;

final class ProductRefundContainmentTest extends ProductRefundFixture
{
    public function test_legitimate_vendor_acceptance_credits_once_and_keeps_original_payment_and_ledger(): void
    {
        $original = $this->database->table('transactions')->where('payable_type', Order::class)->first();
        $ledger = $this->persistedState()['platform_fee_ledger_entries'];
        self::assertSame(200, $this->updateRefund(['status' => 'accepted'])->getStatusCode());
        self::assertSame([100.0, 80.0, 80.0], $this->balances());
        self::assertSame('accepted', OrderRefund::findOrFail(1)->status);
        self::assertSame('new', Order::findOrFail(1)->status);
        self::assertEquals($original, $this->database->table('transactions')->where('payable_type', Order::class)->first());
        self::assertSame($ledger, $this->persistedState()['platform_fee_ledger_entries']);
        self::assertSame(1, WalletHistory::count());
        $history = WalletHistory::first();
        self::assertSame('paid', $history->status);
        self::assertSame('topup', $history->type);
        self::assertSame(50.0, (float) $history->price);
        self::assertSame('paid', $history->transaction->status);
        self::assertSame(WalletHistory::class, $history->transaction->payable_type);
        self::assertSame(2, Transaction::count());
        self::assertSame(9, (int) Stock::find(1)->quantity);
        $after = $this->persistedState();
        $events = $this->transactionEvents;
        for ($i = 0; $i < 3; ++$i) {
            self::assertFalse($this->settle()['status']);
            self::assertSame($after, $this->persistedState());
        }
        self::assertSame($events, $this->transactionEvents);
    }

    public static function terminalTransitions(): array
    {
        return array_map(fn ($state) => [$state], ['pending', 'accepted', 'canceled', 'rejected', '', null, ['accepted']]);
    }

    /** @dataProvider terminalTransitions */
    public function test_accepted_is_terminal_and_cycling_cannot_repeat_any_persisted_effect(mixed $state): void
    {
        self::assertTrue($this->settle()['status']);
        $after = $this->persistedState();
        self::assertFalse($this->refundService->update(OrderRefund::find(1), ['status' => $state, 'answer' => 'Fixture'])['status']);
        self::assertFalse($this->settle()['status']);
        self::assertSame($after, $this->persistedState());
    }

    public function test_pending_cancel_is_nonfinancial_and_canceled_does_not_reopen_but_customer_may_request_again(): void
    {
        $before = $this->persistedState();
        self::assertSame(200, $this->updateRefund(['status' => 'canceled', 'answer' => 'Declined'])->getStatusCode());
        self::assertSame('canceled', OrderRefund::find(1)->status);
        self::assertSame('Declined', OrderRefund::find(1)->answer);
        $after = $this->persistedState();
        foreach (['pending', 'accepted', 'canceled'] as $status) {
            self::assertFalse($this->refundService->update(OrderRefund::find(1), ['status' => $status])['status']);
            self::assertSame($after, $this->persistedState());
        }
        foreach (['wallets', 'wallet_histories', 'transactions', 'stocks', 'platform_fee_ledger_entries'] as $table) {
            self::assertSame($before[$table], $after[$table]);
        }
        self::assertTrue($this->refundService->create(['order_id' => 1, 'cause' => 'New request'])['status']);
        self::assertSame('pending', OrderRefund::find(2)->status);
        self::assertTrue($this->settle([], OrderRefund::find(2))['status']);
        self::assertSame(1, WalletHistory::count());
    }

    public function test_creation_never_accepts_forged_initial_status_and_rejects_duplicate_or_settled_requests(): void
    {
        self::assertFalse($this->refundService->create(['order_id' => 1, 'cause' => 'Fixture'])['status']);
        $this->database->table('order_refunds')->where('id', 1)->update(['status' => 'canceled']);
        self::assertTrue($this->refundService->create(['order_id' => 1, 'cause' => 'Fixture',
            'status' => 'accepted', 'price' => 99999, 'user_id' => 3])['status']);
        self::assertSame('pending', OrderRefund::find(2)->status);
        self::assertSame(0, WalletHistory::count());
        self::assertTrue($this->settle([], OrderRefund::find(2))['status']);
        $before = $this->persistedState();
        self::assertFalse($this->refundService->create(['order_id' => 1, 'cause' => 'Again'])['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_stale_route_models_and_duplicate_pending_rows_cannot_settle_the_same_order_twice(): void
    {
        $stale = OrderRefund::findOrFail(1);
        $this->database->table('order_refunds')->insert(['id' => 2, 'order_id' => 1, 'status' => 'pending']);
        $sibling = OrderRefund::findOrFail(2);
        self::assertTrue($this->settle()['status']);
        $before = $this->persistedState();
        self::assertFalse($this->settle([], $stale)['status']);
        self::assertFalse($this->settle([], $sibling)['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_delivered_partner_recovery_stock_and_referral_dispatch_occur_once(): void
    {
        $this->deliveredPartners();
        self::assertTrue($this->settle()['status']);
        self::assertSame([60.0, 80.0, 75.0], $this->balances());
        self::assertSame(3, WalletHistory::count());
        self::assertSame(6, Transaction::count());
        self::assertSame(1, $this->referralJobs);
        self::assertSame(1, $this->database->table('platform_fee_ledger_entries')->count());
        $before = $this->persistedState();
        foreach (['pending', 'canceled', 'accepted'] as $status) {
            self::assertFalse($this->refundService->update(OrderRefund::find(1), ['status' => $status])['status']);
        }
        self::assertSame($before, $this->persistedState());
    }

    public static function invalidTotals(): array { return [[0], [-5]]; }

    /** @dataProvider invalidTotals */
    public function test_invalid_persisted_total_fails_and_rolls_back_claim_and_stock(int $total): void
    {
        $this->database->table('orders')->where('id', 1)->update(['total_price' => $total]);
        $before = $this->persistedState();
        self::assertFalse($this->settle(['price' => 999])['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_unpaid_already_refunded_and_missing_wallet_orders_do_not_become_accepted(): void
    {
        foreach (['progress', 'refund'] as $status) {
            $this->database->table('transactions')->update(['status' => $status]);
            $before = $this->persistedState();
            self::assertFalse($this->settle()['status']);
            self::assertSame($before, $this->persistedState());
        }
        $this->database->table('transactions')->update(['status' => 'paid']);
        $this->database->table('wallets')->where('user_id', 2)->delete();
        $before = $this->persistedState();
        self::assertFalse($this->settle()['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_stock_failure_before_credit_rolls_back_every_table(): void
    {
        Stock::updating(fn () => throw new \RuntimeException('isolated stock failure'));
        $before = $this->persistedState();
        self::assertFalse($this->settle()['status']);
        self::assertSame($before, $this->persistedState());
    }

    public static function walletFailures(): array { return [['recipient_before'], ['recipient_after']]; }

    /** @dataProvider walletFailures */
    public function test_false_wallet_result_before_or_after_credit_rolls_back_claim_history_transaction_and_stock(string $stage): void
    {
        $this->service->failAt = $stage;
        $before = $this->persistedState();
        self::assertFalse($this->settle()['status']);
        self::assertSame($before, $this->persistedState());
        $this->service->failAt = null;
        self::assertTrue($this->settle()['status']);
        self::assertSame(1, WalletHistory::count());
    }

    public function test_exception_after_customer_balance_increment_before_completion_rolls_back(): void
    {
        // Relation increments use a SQL builder, not Wallet model events.
        // QueryExecuted is emitted after the real balance UPDATE completes.
        \Illuminate\Support\Facades\DB::listen(function (\Illuminate\Database\Events\QueryExecuted $query): void {
            if (str_starts_with($query->sql, 'update "wallets"') && str_contains($query->sql, '+')) {
                throw new \RuntimeException('isolated after-credit failure');
            }
        });
        $before = $this->persistedState();
        self::assertFalse($this->settle()['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_failure_during_delivered_partner_recovery_rolls_back_and_registers_no_referral(): void
    {
        $this->deliveredPartners();
        \Illuminate\Support\Facades\DB::listen(function (\Illuminate\Database\Events\QueryExecuted $query): void {
            if (str_starts_with($query->sql, 'update "wallets"') && str_contains($query->sql, '-')) {
                throw new \RuntimeException('isolated partner failure');
            }
        });
        $before = $this->persistedState();
        self::assertFalse($this->settle()['status']);
        self::assertSame($before, $this->persistedState());
        self::assertSame(0, $this->referralJobs);
    }

    public function test_financially_settled_rows_cannot_be_deleted_by_customer_vendor_admin_or_drop_all(): void
    {
        self::assertTrue($this->settle()['status']);
        $before = $this->persistedState();
        foreach ([[null, true], [1, false], [null, false]] as [$shop, $admin]) {
            $this->authenticate($shop === null && !$admin ? 2 : 1, $admin ? 'admin' : 'seller');
            self::assertTrue($this->refundService->delete([1], $shop, $admin)['status']);
            self::assertSame($before, $this->persistedState());
        }
        self::assertTrue($this->refundService->dropAll()['status']);
        self::assertSame($before, $this->persistedState());
        self::assertFalse($this->refundService->create(['order_id' => 1, 'cause' => 'Again'])['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_refund_paid_wallet_history_is_terminal_and_foreign_history_requests_do_not_mutate(): void
    {
        self::assertTrue($this->settle()['status']);
        $history = WalletHistory::first();
        $before = $this->persistedState();
        foreach ([2, 1] as $actor) {
            $this->authenticate($actor, 'user');
            foreach (['canceled', 'rejected', 'paid'] as $status) {
                self::assertNotSame(200, $this->request("history/{$history->uuid}/status/change",
                    ['status' => $status, 'user_id' => 2, 'wallet_uuid' => 'wallet-2'])->getStatusCode());
                self::assertSame($before, $this->persistedState());
            }
        }
    }

    public function test_customer_can_delete_canceled_request_and_admin_bulk_delete_retains_settlement_witness(): void
    {
        self::assertTrue($this->settle()['status']);
        $db = $this->database;
        $db->table('order_refunds')->insert([
            ['id' => 2, 'order_id' => 1, 'status' => 'canceled'],
            ['id' => 3, 'order_id' => 2, 'status' => 'pending'],
        ]);
        $this->authenticate(2);
        self::assertTrue($this->refundService->delete([2])['status']);
        self::assertNull(OrderRefund::find(2));
        self::assertNotNull(OrderRefund::find(1));
        $before = $this->persistedState();
        self::assertTrue($this->refundService->dropAll()['status']);
        self::assertSame([1], OrderRefund::pluck('id')->all());
        foreach (['wallets', 'wallet_histories', 'transactions', 'stocks', 'platform_fee_ledger_entries'] as $table) {
            self::assertSame($before[$table], $this->persistedState()[$table]);
        }
        self::assertFalse($this->refundService->create(['order_id' => 1, 'cause' => 'Again'])['status']);
    }

    public function test_downloaded_digital_line_is_excluded_using_persisted_values_and_remaining_credit_is_positive(): void
    {
        $db = $this->database;
        $db->table('digital_files')->insert(['id' => 1, 'product_id' => 1]);
        $db->table('user_digital_files')->insert(['digital_file_id' => 1, 'user_id' => 2, 'downloaded' => true]);
        $db->table('order_details')->where('id', 1)->update(['total_price' => 10]);
        $db->table('products')->insert(['id' => 2, 'o_count' => 1, 'od_count' => 1]);
        $db->table('stocks')->insert(['id' => 2, 'product_id' => 2, 'quantity' => 7, 'o_count' => 1, 'od_count' => 1]);
        $db->table('order_details')->insert(['order_id' => 1, 'stock_id' => 2, 'quantity' => 1, 'total_price' => 40]);
        self::assertTrue($this->settle(['price' => 999])['status']);
        self::assertSame(40.0, (float) WalletHistory::first()->price);
        self::assertSame([100.0, 70.0, 80.0], $this->balances());
        $before = $this->persistedState();
        self::assertFalse($this->settle()['status']);
        self::assertSame($before, $this->persistedState());
    }
}