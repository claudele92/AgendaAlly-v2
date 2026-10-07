<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Payout;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletHistory;

final class PayoutFinancialContainmentTest extends PayoutFinancialFixture
{
    public function test_native_payout_conserves_value_once_and_admin_replay_cannot_repeat(): void
    {
        $untouched = $this->payoutState();
        self::assertTrue($this->approve()['status']);
        $this->assertPayoutBatch();
        $after = $this->payoutState();
        foreach (['shops', 'orders', 'order_refunds', 'platform_fee_ledger_entries'] as $table) {
            self::assertSame($untouched[$table], $after[$table]);
        }
        foreach (['accepted', 'pending', 'canceled', 'accepted'] as $status) {
            self::assertFalse($this->payoutService->statusChange(1, $status)['status']);
            self::assertSame($after, $this->payoutState());
        }
    }

    public function test_vendor_and_stale_service_update_cannot_reset_or_change_completed_financial_terms(): void
    {
        $stale = Payout::findOrFail(1);
        self::assertTrue($this->approve()['status']);
        $before = $this->payoutState();
        $this->authenticate(1, 'seller');
        self::assertFalse($this->approve()['status']);
        self::assertFalse($this->payoutService->update($stale, ['status' => 'pending', 'price' => 25])['status']);
        self::assertSame($before, $this->payoutState());
        self::assertTrue($this->payoutService->update($stale, ['status' => 'pending', 'created_by' => 2, 'approved_by' => 2])['status']);
        self::assertSame($before, $this->payoutState());
        $this->authenticate(3, 'admin');
        self::assertFalse($this->approve()['status']);
        $this->assertPayoutBatch();
    }

    public function test_pending_and_canceled_transitions_do_not_have_financial_legs(): void
    {
        $before = $this->payoutState();
        self::assertFalse($this->payoutService->statusChange(1, 'pending')['status']);
        self::assertTrue($this->payoutService->statusChange(1, 'canceled')['status']);
        self::assertFalse($this->payoutService->statusChange(1, 'canceled')['status']);
        self::assertTrue($this->payoutService->statusChange(1, 'pending')['status']);
        $after = $this->payoutState();
        unset($before['payouts'], $after['payouts']);
        self::assertSame($before, $after);
        self::assertTrue($this->approve()['status']);
        $this->assertPayoutBatch();
    }

    public static function serviceFaults(): array
    {
        return array_map(fn ($fault) => [$fault], [
            'topup_before', 'topup_after_false', 'withdraw_before',
            'withdraw_after_false', 'withdraw_after_throw', 'topup_after_throw',
        ]);
    }

    /** @dataProvider serviceFaults */
    public function test_native_false_or_exception_at_either_leg_rolls_back_and_retry_succeeds_once(string $fault): void
    {
        $before = $this->payoutState();
        $this->historyService->fault = $fault;
        self::assertFalse($this->approve()['status']);
        self::assertSame($before, $this->payoutState());
        self::assertSame('pending', Payout::findOrFail(1)->status);
        $this->historyService->fault = null;
        self::assertTrue($this->approve()['status']);
        $this->assertPayoutBatch();
        $after = $this->payoutState();
        self::assertFalse($this->approve()['status']);
        self::assertSame($after, $this->payoutState());
    }

    public static function saveFaults(): array
    {
        return [['history'], ['history_link'], ['history_transaction'], ['recipient_summary'],
            ['approver_summary'], ['existing_recipient_summary'], ['existing_approver_summary'],
            ['finalization'], ['finalization_exception'], ['withdraw_no_row'],
            ['recipient_wallet_after_credit'], ['funding_wallet_after_summary'],
            ['funding_wallet_after_finalization'], ['funding_wallet_identity_after_finalization']];
    }

    /** @dataProvider saveFaults */
    public function test_required_native_saves_arithmetic_and_finalization_are_checked(string $fault): void
    {
        if (str_starts_with($fault, 'existing_')) {
            $userId = $fault === 'existing_recipient_summary' ? 1 : 3;
            Wallet::where('user_id', $userId)->firstOrFail()->createTransaction([
                'price' => 3, 'payment_sys_id' => 1, 'user_id' => $userId, 'status' => 'progress',
            ]);
        }
        $before = $this->payoutState();
        $enabled = true;
        WalletHistory::saving(function ($history) use ($fault, &$enabled) {
            if (!$enabled) return null;
            if ($fault === 'history' && $history->type === 'withdraw') return false;
            if ($fault === 'history_link' && $history->isDirty('transaction_id')) return false;
            return null;
        });
        Transaction::saving(function ($transaction) use ($fault, &$enabled) {
            if (!$enabled) return null;
            if ($fault === 'history_transaction' && $transaction->payable_type === WalletHistory::class) return false;
            if ($transaction->payable_type !== Wallet::class) return null;
            if (in_array($fault, ['recipient_summary', 'existing_recipient_summary']) && (int) $transaction->payable_id === 1) return false;
            if (in_array($fault, ['approver_summary', 'existing_approver_summary']) && (int) $transaction->payable_id === 3) return false;
            return null;
        });
        Transaction::created(function ($transaction) use ($fault, &$enabled) {
            if ($enabled && $fault === 'funding_wallet_after_summary'
                && $transaction->payable_type === Wallet::class && (int) $transaction->payable_id === 3) {
                Wallet::where('user_id', 3)->delete();
            }
        });
        Payout::updating(function () use ($fault, &$enabled) {
            if ($enabled && $fault === 'finalization') return false;
            if ($enabled && $fault === 'finalization_exception') throw new \RuntimeException('Injected finalization failure');
            return null;
        });
        Payout::updated(function () use ($fault, &$enabled) {
            if (!$enabled) return;
            if ($fault === 'funding_wallet_after_finalization') Wallet::where('user_id', 3)->delete();
            if ($fault === 'funding_wallet_identity_after_finalization') {
                Wallet::where('user_id', 3)->update(['uuid' => 'fixture-replacement-wallet']);
            }
        });
        WalletHistory::created(function ($history) use ($fault, &$enabled) {
            if ($enabled && $fault === 'withdraw_no_row' && $history->type === 'withdraw') {
                Wallet::where('user_id', 3)->delete();
            }
            if ($enabled && $fault === 'recipient_wallet_after_credit' && $history->type === 'withdraw') {
                Wallet::where('user_id', 1)->delete();
            }
        });
        self::assertFalse($this->approve()['status']);
        self::assertSame($before, $this->payoutState());
        $enabled = false;
        self::assertTrue($this->approve()['status']);
        $this->assertPayoutBatch();
    }

    public function test_zero_affected_row_debit_rolls_back_and_retry_conserves_value_once(): void
    {
        self::assertSame('sqlite', $this->database->getConnection()->getDriverName());
        $before = $this->payoutState();
        // Reject the actual funding debit without deleting a Wallet after debit.
        // This complements, rather than replaces, the existing disappearance fault.
        $this->database->getConnection()->statement(
            'CREATE TEMP TRIGGER payout_debit_no_row BEFORE UPDATE OF price ON wallets '
            . 'WHEN OLD.user_id = 3 AND NEW.price < OLD.price BEGIN SELECT RAISE(IGNORE); END'
        );
        try {
            $result = $this->approve();
            self::assertFalse($result['status']);
            self::assertSame('Insufficient spendable Wallet funds.', $result['message']);
            self::assertSame($before, $this->payoutState());
            self::assertSame('pending', Payout::findOrFail(1)->status);
        } finally {
            $this->database->getConnection()->statement('DROP TRIGGER payout_debit_no_row');
        }
        self::assertTrue($this->approve()['status']);
        $this->assertPayoutBatch();
        $after = $this->payoutState();
        self::assertFalse($this->approve()['status']);
        self::assertSame($after, $this->payoutState());
    }

    public function test_insufficient_balance_missing_wallet_and_invalid_amount_fail_before_commit(): void
    {
        foreach (['balance', 'recipient', 'amount'] as $fault) {
            if ($fault === 'balance') Wallet::where('user_id', 3)->update(['price' => 5]);
            if ($fault === 'recipient') Wallet::where('user_id', 1)->delete();
            if ($fault === 'amount') Payout::whereKey(1)->update(['price' => -1]);
            $before = $this->payoutState();
            self::assertFalse($this->approve()['status']);
            self::assertSame($before, $this->payoutState());
            Wallet::where('user_id', 3)->update(['price' => 80]);
        }
    }

    public function test_non_wallet_native_acceptance_is_only_bookkeeping_not_external_settlement(): void
    {
        $this->database->table('payments')->insert(['id' => 8, 'tag' => 'mtn', 'active' => 0]);
        Payout::whereKey(1)->update(['payment_id' => 8]);
        self::assertTrue($this->approve()['status']);
        self::assertSame(80.0, (float) Wallet::where('user_id', 3)->value('price'));
        self::assertSame(100.0, (float) Wallet::where('user_id', 1)->value('price'));
        self::assertSame(0, WalletHistory::count());
        self::assertSame(2, Transaction::where('payable_type', Wallet::class)->where('status', 'progress')->count());
        $before = $this->payoutState();
        self::assertFalse($this->approve()['status']);
        self::assertSame($before, $this->payoutState());
    }

    public function test_native_self_wallet_pair_is_conservative_and_is_not_a_second_debit(): void
    {
        Payout::whereKey(1)->update(['created_by' => 3]);
        self::assertTrue($this->approve()['status']);
        self::assertSame(80.0, (float) Wallet::where('user_id', 3)->value('price'));
        self::assertSame(2, WalletHistory::count());
        self::assertSame(1, Transaction::where('payable_type', Wallet::class)->count());
        self::assertSame('accepted', Payout::findOrFail(1)->status);
    }

    public function test_native_admin_http_approval_and_replay_and_vendor_update_use_the_same_boundary(): void
    {
        self::assertTrue($this->payoutHttp('/fixture/payout/admin/1/status', ['status' => 'accepted'], 'POST')->getData(true)['status']);
        $this->assertPayoutBatch();
        $before = $this->payoutState();
        self::assertFalse($this->payoutHttp('/fixture/payout/admin/1/status', ['status' => 'accepted'], 'POST')->getData(true)['status']);
        self::assertFalse($this->payoutHttp('/fixture/payout/admin/1', ['price' => 21])->getData(true)['status']);
        $this->authenticate(1, 'seller');
        self::assertFalse($this->payoutHttp('/fixture/payout/seller/1', ['price' => 21])->getData(true)['status']);
        self::assertTrue($this->payoutHttp('/fixture/payout/seller/1', ['status' => 'pending', 'approved_by' => 1])->getData(true)['status']);
        self::assertSame($before, $this->payoutState());
    }

    public function test_vendor_cannot_invoke_admin_approval_and_cannot_change_the_recipient(): void
    {
        $this->authenticate(1, 'seller');
        $before = $this->payoutState();
        self::assertSame(403, $this->payoutHttp('/fixture/payout/admin/1/status', ['status' => 'accepted'], 'POST')->getStatusCode());
        self::assertSame(422, $this->payoutHttp('/fixture/payout/seller/1', ['created_by' => 2])->getStatusCode());
        self::assertSame($before, $this->payoutState());
    }
}