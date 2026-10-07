<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;

final class ProductFulfillmentFinalityTest extends ProductFulfillmentFinalityFixture
{
    public static function authorizedActors(): array
    {
        return [[1, 'seller'], [4, 'shop_manager'], [3, 'admin'], [3, 'manager'], [3, 'deliveryman']];
    }

    #[DataProvider('authorizedActors')]
    public function test_first_batch_and_immediate_duplicate_for_approved_native_actors(int $id, string $role): void
    {
        $this->authenticate($id, $role);
        self::assertTrue($this->fulfill()['status']);
        $this->assertFirstBatch();
        $before = $this->persistedState();
        self::assertFalse($this->fulfill()['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_multiple_lifecycle_cycles_and_stale_models_never_repeat_finance(): void
    {
        $staleA = Order::findOrFail(1);
        $staleB = Order::findOrFail(1);
        self::assertTrue($this->fulfill($staleA)['status']);
        $this->assertFirstBatch();
        $before = $this->financialState();
        self::assertFalse($this->fulfill($staleB)['status']);
        foreach (['ready', 'accepted', 'pause', 'on_a_way', 'ready'] as $status) {
            self::assertTrue($this->fulfill($staleB, $status)['status']);
            self::assertTrue($this->fulfill($staleB)['status']);
            self::assertSame($before, $this->financialState());
            self::assertSame('settled', Order::findOrFail(1)->fulfillment_financial_state);
        }
    }

    public function test_deleted_receipts_and_admin_wallet_do_not_erase_finality(): void
    {
        self::assertTrue($this->fulfill()['status']);
        $this->database->table('wallet_histories')->delete();
        $this->database->table('wallets')->where('id', 3)->delete();
        self::assertTrue($this->fulfill(null, 'ready')['status']);
        $before = $this->financialState();
        self::assertTrue($this->fulfill()['status']);
        self::assertSame($before, $this->financialState());
        self::assertSame('settled', Order::findOrFail(1)->fulfillment_financial_state);
    }

    public function test_replacement_payment_and_transaction_cannot_reopen_settlement(): void
    {
        self::assertTrue($this->fulfill()['status']);
        $this->database->table('platform_fee_ledger_entries')->delete();
        $this->database->table('transactions')->where('id', 1)->delete();
        $this->database->table('payments')->insert(['id' => 3, 'tag' => 'cash', 'active' => true]);
        Transaction::create(['payable_type' => Order::class, 'payable_id' => 1, 'user_id' => 2,
            'price' => 50, 'payment_sys_id' => 3, 'status' => 'progress']);
        self::assertTrue($this->fulfill(null, 'ready')['status']);
        $before = $this->financialState();
        self::assertTrue($this->fulfill()['status']);
        self::assertSame($before, $this->financialState());
        self::assertSame('progress', Order::findOrFail(1)->transaction->status);
    }

    public function test_zero_fee_order_has_full_gross_and_cashback_batch_without_fee_identity(): void
    {
        $this->database->table('orders')->where('id', 1)->update(['service_fee' => 0]);
        self::assertTrue($this->fulfill()['status']);
        self::assertSame(0, $this->database->table('platform_fee_ledger_entries')->count());
        self::assertSame(2, WalletHistory::count());
        $before = $this->financialState();
        self::assertTrue($this->fulfill(null, 'ready')['status']);
        self::assertTrue($this->fulfill()['status']);
        self::assertSame($before, $this->financialState());
    }

    public function test_prepaid_electronic_record_is_not_itself_a_fulfillment_claim(): void
    {
        $this->database->table('payments')->insert(['id' => 3, 'tag' => 'stripe', 'active' => true]);
        Transaction::findOrFail(1)->update(['payment_sys_id' => 3, 'status' => 'paid']);
        // Real observer created the fee before fulfillment; no provider is called.
        self::assertSame(1, $this->database->table('platform_fee_ledger_entries')->count());
        self::assertSame('pending', Order::findOrFail(1)->fulfillment_financial_state);
        self::assertTrue($this->fulfill()['status']);
        $this->assertFirstBatch();
        $before = $this->financialState();
        self::assertTrue($this->fulfill(null, 'ready')['status']);
        self::assertTrue($this->fulfill()['status']);
        self::assertSame($before, $this->financialState());
    }

    public static function failureStages(): array
    {
        return array_map(fn ($s) => [$s], [
            'claim', 'gross-history', 'gross-transaction', 'gross-credit',
            'cashback-history', 'cashback-transaction', 'cashback-credit',
            'point-history', 'cash-paid', 'fee-ledger', 'order-status',
        ]);
    }

    #[DataProvider('failureStages')]
    public function test_exception_before_each_required_write_rolls_back_and_retry_succeeds(string $stage): void
    {
        $armed = true;
        $histories = $transactions = $credits = 0;
        $this->database->getConnection()->beforeExecuting(function ($sql) use (
            $stage, &$armed, &$histories, &$transactions, &$credits
        ): void {
            if (!$armed) return;
            $write = null;
            if (str_starts_with($sql, 'insert into "wallet_histories"')) $write = ++$histories === 1 ? 'gross-history' : 'cashback-history';
            if (str_starts_with($sql, 'insert into "transactions"')) $write = ++$transactions === 1 ? 'gross-transaction' : 'cashback-transaction';
            if (str_starts_with($sql, 'update "wallets"')) $write = ++$credits === 1 ? 'gross-credit' : 'cashback-credit';
            if (str_starts_with($sql, 'insert into "point_histories"')) $write = 'point-history';
            if (str_starts_with($sql, 'insert into "platform_fee_ledger_entries"')) $write = 'fee-ledger';
            if (str_starts_with($sql, 'update "transactions"')) $write = 'cash-paid';
            if (str_starts_with($sql, 'update "orders"')) $write = str_contains($sql, '"fulfillment_financial_state"') ? 'claim' : 'order-status';
            if ($write === $stage) {
                $armed = false;
                throw new \RuntimeException('Isolated required-write failure: ' . $stage);
            }
        });
        $before = $this->persistedState();
        self::assertFalse($this->fulfill()['status']);
        self::assertFalse($armed, 'The intended native operation must have been reached');
        self::assertSame($before, $this->persistedState());
        self::assertSame('pending', Order::findOrFail(1)->fulfillment_financial_state);
        self::assertSame([], $this->referralTransactionLevels);
        self::assertTrue($this->fulfill()['status']);
        $this->assertFirstBatch();
    }

    public static function unsuccessfulOperations(): array
    {
        return array_map(fn ($s) => [$s], [
            'gross-amount', 'cashback-amount', 'history-save', 'transaction-save',
            'history-link', 'point-save', 'cash-update', 'ledger-save', 'order-update', 'credit-zero-rows',
            'cash-zero-rows', 'history-link-zero-rows', 'order-zero-rows',
        ]);
    }

    #[DataProvider('unsuccessfulOperations')]
    public function test_native_false_unsuccessful_result_is_not_successful_settlement(string $operation): void
    {
        $armed = true;
        $rejected = false;
        if ($operation === 'gross-amount') $this->database->table('orders')->where('id', 1)->update(['total_price' => -50]);
        if ($operation === 'cashback-amount') $this->database->table('points')->update(['price' => -5]);
        foreach ([WalletHistory::class, Transaction::class, \App\Models\PointHistory::class,
            \App\Models\PlatformFeeLedgerEntry::class, Order::class] as $class) {
            $class::saving(function ($model) use ($operation, &$armed, &$rejected) {
                if (!$armed) return null;
                $reject = match ($operation) {
                    'history-save' => $model instanceof WalletHistory && !$model->exists,
                    'transaction-save' => $model instanceof Transaction && !$model->exists,
                    'history-link' => $model instanceof WalletHistory && $model->exists && $model->isDirty('transaction_id'),
                    'point-save' => $model instanceof \App\Models\PointHistory,
                    'cash-update' => $model instanceof Transaction && $model->exists && $model->isDirty('status'),
                    'ledger-save' => $model instanceof \App\Models\PlatformFeeLedgerEntry,
                    'order-update' => $model instanceof Order,
                    default => false,
                };
                if ($reject) {
                    $rejected = true;
                    // Morph updateOrCreate retries save on an unsaved result.
                    if ($operation !== 'transaction-save') $armed = false;
                    return false;
                }
                return null;
            });
        }
        if ($operation === 'credit-zero-rows') {
            $this->database->getConnection()->beforeExecuting(function ($sql) use (&$armed): void {
                if ($armed && str_starts_with($sql, 'update "wallets"')) {
                    $armed = false;
                    $this->database->table('wallets')->where('id', 3)->delete();
                }
            });
        }
        if (in_array($operation, ['cash-zero-rows', 'history-link-zero-rows', 'order-zero-rows'])) {
            $this->database->getConnection()->beforeExecuting(function ($sql) use ($operation, &$armed): void {
                $matches = match ($operation) {
                    'cash-zero-rows' => str_starts_with($sql, 'update "transactions"'),
                    'history-link-zero-rows' => str_starts_with($sql, 'update "wallet_histories"'),
                    'order-zero-rows' => str_starts_with($sql, 'update "orders"') && str_contains($sql, '"status"'),
                };
                if ($armed && $matches) {
                    $armed = false;
                    $table = match ($operation) {
                        'cash-zero-rows' => 'transactions',
                        'history-link-zero-rows' => 'wallet_histories',
                        'order-zero-rows' => 'orders',
                    };
                    $this->database->table($table)->where('id', 1)->delete();
                }
            });
        }
        $before = $this->persistedState();
        self::assertFalse($this->fulfill()['status']);
        self::assertSame($before, $this->persistedState());
        self::assertSame('pending', Order::findOrFail(1)->fulfillment_financial_state);
        self::assertSame([], $this->referralTransactionLevels);
        if ($operation === 'transaction-save') {
            self::assertTrue($rejected);
            $armed = false;
        }
        if (in_array($operation, ['gross-amount', 'cashback-amount'])) {
            $this->database->table('orders')->where('id', 1)->update(['total_price' => 50]);
            $this->database->table('points')->update(['price' => 5]);
        } else {
            self::assertFalse($armed, 'The native unsuccessful operation must have been reached');
        }
        self::assertTrue($this->fulfill()['status']);
        $this->assertFirstBatch();
    }

    public function test_missing_admin_wallet_rolls_back_and_is_retryable(): void
    {
        $wallet = (array) $this->database->table('wallets')->where('id', 3)->first();
        $this->database->table('wallets')->where('id', 3)->delete();
        $before = $this->persistedState();
        self::assertFalse($this->fulfill()['status']);
        self::assertSame($before, $this->persistedState());
        $this->database->table('wallets')->insert($wallet);
        self::assertTrue($this->fulfill()['status']);
        $this->assertFirstBatch();
    }

    public function test_outer_rollback_never_dispatches_referral_and_restores_pending(): void
    {
        $before = $this->persistedState();
        DB::beginTransaction();
        self::assertTrue($this->fulfill()['status']);
        self::assertSame(0, $this->referralJobs);
        DB::rollBack();
        self::assertSame($before, $this->persistedState());
        self::assertTrue($this->fulfill()['status']);
        $this->assertFirstBatch();
    }

    public function test_legacy_unverified_fails_closed_even_after_lifecycle_update(): void
    {
        $this->database->table('orders')->where('id', 1)->update(['fulfillment_financial_state' => 'unverified']);
        $before = $this->persistedState();
        $result = $this->fulfill();
        self::assertFalse($result['status']);
        self::assertStringContainsString('unverified', $result['message']);
        self::assertSame($before, $this->persistedState());
        self::assertTrue($this->fulfill(null, 'ready')['status']);
        self::assertFalse($this->fulfill()['status']);
        self::assertSame('unverified', Order::findOrFail(1)->fulfillment_financial_state);
    }

    public function test_staff_authority_is_rechecked_in_the_locked_transaction_before_claim(): void
    {
        $this->authenticate(4, 'shop_manager');
        $revoked = false;
        $this->database->getConnection()->beforeExecuting(function ($sql, $bindings, $connection) use (&$revoked): void {
            if (!$revoked && $connection->transactionLevel() > 0 && str_starts_with($sql, 'select')
                && str_contains($sql, '"orders"')) {
                $revoked = true;
                $this->database->table('shop_role_permissions')->where('shop_permission_id', 3)->delete();
            }
        });
        self::assertFalse($this->fulfill()['status']);
        self::assertTrue($revoked);
        self::assertSame('pending', Order::findOrFail(1)->fulfillment_financial_state);
        self::assertSame(0, WalletHistory::count());
        self::assertSame(1, Transaction::count());
        self::assertSame(0, $this->referralJobs);
        self::assertEquals(80, $this->database->table('wallets')->where('id', 3)->value('price'));
    }

    public function test_private_state_cannot_be_mass_assigned_or_reset_by_generic_updates(): void
    {
        self::assertTrue($this->fulfill()['status']);
        $order = Order::findOrFail(1);
        $order->update(['status' => 'ready', 'fulfillment_financial_state' => 'pending']);
        self::assertSame('settled', $order->fresh()->fulfillment_financial_state);
        self::assertArrayNotHasKey('fulfillment_financial_state', $order->attributesToArray());
        try {
            $order->forceFill(['fulfillment_financial_state' => 'pending']);
            self::fail('Forced model assignment must not reset finality');
        } catch (\LogicException $e) {
            self::assertStringContainsString('server-owned', $e->getMessage());
        }
        $before = $this->financialState();
        self::assertTrue($this->fulfill()['status']);
        self::assertSame($before, $this->financialState());
    }

    public function test_trusted_creators_opt_in_only_new_records_and_import_style_create_does_not(): void
    {
        $attrs = ['shop_id' => 1, 'user_id' => 2, 'deliveryman_id' => 3, 'fulfillment_financial_state' => 'settled'];
        $generic = Order::create($attrs)->fresh();
        self::assertSame('unverified', $generic->fulfillment_financial_state);
        $pos = Order::createForFulfillment($attrs);
        self::assertSame('pending', $pos->fulfillment_financial_state);
        $cart = Order::updateOrCreateForFulfillment(['id' => $pos->id], ['status' => 'ready',
            'fulfillment_financial_state' => 'unverified']);
        self::assertSame('pending', $cart->fulfillment_financial_state);
        $legacy = Order::updateOrCreateForFulfillment(['id' => $generic->id], ['status' => 'ready']);
        self::assertSame('unverified', $legacy->fulfillment_financial_state);
        $new = Order::updateOrCreateForFulfillment(['shop_id' => 2, 'user_id' => 5], ['deliveryman_id' => 3]);
        self::assertSame('pending', $new->fulfillment_financial_state);
        // Production import uses the generic method and cannot initialize eligibility.
        Order::updateOrCreate(['id' => $legacy->id], ['status' => 'new', 'fulfillment_financial_state' => 'settled']);
        self::assertSame('unverified', $legacy->fresh()->fulfillment_financial_state);
    }

    public function test_cancellation_refund_and_notes_do_not_reset_settled_marker(): void
    {
        self::assertTrue($this->fulfill()['status']);
        self::assertTrue($this->fulfill(null, 'canceled')['status']);
        self::assertSame('settled', Order::findOrFail(1)->fulfillment_financial_state);
        $before = $this->financialState();
        self::assertTrue($this->fulfill()['status']);
        self::assertSame($before, $this->financialState());
    }

    public function test_migration_rollback_refuses_to_destroy_committed_finality(): void
    {
        self::assertTrue($this->fulfill()['status']);
        $migration = require dirname(__DIR__, 2) . '/database/migrations/2026_10_06_010000_add_product_fulfillment_financial_state.php';
        try { $migration->down(); self::fail('Settled finality must survive migration rollback'); }
        catch (\RuntimeException $e) { self::assertStringContainsString('Cannot remove', $e->getMessage()); }
        self::assertSame('settled', Order::findOrFail(1)->fulfillment_financial_state);
    }
}