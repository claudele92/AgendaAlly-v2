<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Transaction;
use App\Models\WalletHistory;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

final class WalletTransferContainmentTest extends WalletTransferFixture
{
    public function test_successful_transfer_has_exactly_one_debit_and_credit_and_terminal_consistent_statuses(): void
    {
        self::assertSame(200, $this->send()->getStatusCode());
        self::assertSame([80.0, 50.0, 80.0], $this->balances());
        self::assertSame(130.0, array_sum(array_slice($this->balances(), 0, 2)));
        self::assertSame(2, WalletHistory::count());
        self::assertSame(2, Transaction::count());
        self::assertSame(1, WalletHistory::where('type', 'withdraw')->count());
        self::assertSame(1, WalletHistory::where('type', 'topup')->count());
        self::assertSame(['paid', 'paid'], WalletHistory::orderBy('id')->pluck('status')->all());
        self::assertSame(['paid', 'paid'], Transaction::orderBy('id')->pluck('status')->all());
        foreach (WalletHistory::all() as $history) {
            self::assertSame(20.0, (float) $history->price);
            self::assertSame($history->status, $history->transaction->status);
        }
    }

    public static function terminalAttempts(): array
    {
        return [
            'customer cancel' => ['canceled', false],
            'customer reject' => ['rejected', false],
            'admin reject' => ['rejected', true],
            'admin paid replay' => ['paid', true],
        ];
    }

    #[DataProvider('terminalAttempts')]
    public function test_completed_transfer_cannot_be_reversed_or_recredited_through_either_status_action(string $status, bool $admin): void
    {
        self::assertSame(200, $this->send()->getStatusCode());
        $debit = WalletHistory::where('type', 'withdraw')->firstOrFail();
        $before = $this->fingerprint();
        $events = $this->transactionEvents;
        if ($admin) $this->authenticate(3, 'admin');
        for ($i = 0; $i < 3; ++$i) {
            self::assertSame(404, $this->request("history/{$debit->uuid}/status/change", ['status' => $status], $admin)->getStatusCode());
            self::assertSame($before, $this->fingerprint());
            self::assertSame([80.0, 50.0, 80.0], $this->balances());
            self::assertSame(130.0, array_sum(array_slice($this->balances(), 0, 2)));
        }
        self::assertSame($events, $this->transactionEvents);
    }

    public static function pendingActions(): array
    {
        return [
            'own cancel' => ['canceled', false],
            'own reject' => ['rejected', false],
            'admin pending reject' => ['rejected', true],
        ];
    }

    #[DataProvider('pendingActions')]
    public function test_legitimate_pending_withdrawal_restores_once_and_has_consistent_terminal_statuses(string $status, bool $admin): void
    {
        $response = $this->request('withdraw', ['price' => 20]);
        self::assertSame(200, $response->getStatusCode());
        $debit = WalletHistory::firstOrFail();
        self::assertSame('processed', $debit->status);
        self::assertSame('progress', $debit->transaction->status);
        self::assertSame([80.0, 30.0, 80.0], $this->balances());
        if ($admin) $this->authenticate(3, 'admin');
        self::assertSame(200, $this->request("history/{$debit->uuid}/status/change", ['status' => $status], $admin)->getStatusCode());
        self::assertSame([100.0, 30.0, 80.0], $this->balances());
        self::assertSame('canceled', $debit->fresh()->status);
        self::assertSame('canceled', $debit->fresh()->transaction->status);
        $before = $this->fingerprint();
        self::assertSame(404, $this->request("history/{$debit->uuid}/status/change", ['status' => $status], $admin)->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public static function customerRoles(): array
    {
        return [['user'], ['seller'], ['moderator'], ['shop_manager']];
    }

    #[DataProvider('customerRoles')]
    public function test_customer_channel_cannot_change_foreign_pending_history_even_with_forged_ownership(string $role): void
    {
        $foreign = $this->withdrawal(2);
        $before = $this->fingerprint();
        $events = $this->transactionEvents;
        $this->authenticate(1, $role);
        self::assertSame(404, $this->request("history/{$foreign->uuid}/status/change", [
            'status' => 'canceled', 'user_id' => 2, 'wallet_uuid' => 'wallet-2', 'owner_id' => 2,
        ])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame($events, $this->transactionEvents);
        self::assertSame('processed', $foreign->fresh()->status);
    }

    public static function failureStages(): array
    {
        return [['recipient_before'], ['recipient_after'], ['finalization']];
    }

    #[DataProvider('failureStages')]
    public function test_false_service_result_at_any_transfer_stage_rolls_back_both_legs(string $stage): void
    {
        $this->service->failAt = $stage;
        $before = $this->fingerprint();
        self::assertSame(400, $this->send()->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame([100.0, 30.0, 80.0], $this->balances());
        self::assertSame(0, WalletHistory::count());
        self::assertSame(0, Transaction::count());
    }

    public function test_exception_during_recipient_credit_rolls_back_sender_and_recipient_records(): void
    {
        WalletHistory::created(function (WalletHistory $history): void {
            if ($history->type === 'topup') throw new RuntimeException('isolated recipient failure');
        });
        $before = $this->fingerprint();
        try {
            $this->send();
            self::fail('Expected injected failure');
        } catch (RuntimeException $e) {
            self::assertSame('isolated recipient failure', $e->getMessage());
        }
        self::assertSame($before, $this->fingerprint());
        self::assertSame([100.0, 30.0, 80.0], $this->balances());
    }

    public function test_insufficient_funds_and_missing_recipient_have_zero_financial_effects(): void
    {
        $before = $this->fingerprint();
        self::assertSame(400, $this->send(101)->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame(422, $this->send(20, 1, 'missing-recipient')->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public function test_exception_during_status_update_rolls_back_the_legitimate_cancellation(): void
    {
        $history = $this->withdrawal();
        $before = $this->fingerprint();
        Transaction::updating(function (): void {
            throw new RuntimeException('isolated status failure');
        });
        try {
            $this->request("history/{$history->uuid}/status/change", ['status' => 'canceled']);
            self::fail('Expected injected failure');
        } catch (RuntimeException $e) {
            self::assertSame('isolated status failure', $e->getMessage());
        }
        self::assertSame($before, $this->fingerprint());
        self::assertSame([80.0, 30.0, 80.0], $this->balances());
        self::assertSame('processed', $history->fresh()->status);
        self::assertSame('progress', $history->fresh()->transaction->status);
    }

    public function test_repeated_send_requests_are_separate_funded_transfers_not_unbacked_credits(): void
    {
        for ($i = 1; $i <= 3; ++$i) {
            $this->authenticate(1);
            self::assertSame(200, $this->send()->getStatusCode());
            self::assertSame([100.0 - 20 * $i, 30.0 + 20 * $i, 80.0], $this->balances());
            self::assertSame(130.0, array_sum(array_slice($this->balances(), 0, 2)));
            self::assertSame(2 * $i, WalletHistory::count());
            self::assertSame(2 * $i, Transaction::count());
        }
    }

    public function test_native_rate_conversion_conserves_normalized_transfer_value(): void
    {
        self::assertSame(200, $this->send(40, 2)->getStatusCode());
        self::assertSame([80.0, 50.0, 80.0], $this->balances());
        self::assertSame([20.0, 20.0], WalletHistory::orderBy('id')->pluck('price')->map(fn ($x) => (float) $x)->all());
    }

    public function test_self_transfer_is_value_neutral_and_its_debit_is_not_cancellable(): void
    {
        self::assertSame(200, $this->send(20, 1, 'user-1')->getStatusCode());
        self::assertSame([100.0, 30.0, 80.0], $this->balances());
        self::assertSame(2, WalletHistory::count());
        self::assertSame(2, Transaction::count());
        $history = WalletHistory::where('type', 'withdraw')->firstOrFail();
        $before = $this->fingerprint();
        self::assertSame(404, $this->request("history/{$history->uuid}/status/change", ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public function test_admin_can_finalize_genuine_pending_topup_once_without_recredit_on_replay(): void
    {
        $history = $this->service->create([
            'type' => 'topup', 'price' => 20, 'user' => \App\Models\User::findOrFail(2),
            'status' => WalletHistory::PROCESSED,
        ])['data'];
        self::assertSame([100.0, 30.0, 80.0], $this->balances());
        $this->authenticate(3, 'admin');
        self::assertSame(200, $this->request("history/{$history->uuid}/status/change", ['status' => 'paid'], true)->getStatusCode());
        self::assertSame([100.0, 50.0, 80.0], $this->balances());
        self::assertSame('paid', $history->fresh()->status);
        self::assertSame('paid', $history->fresh()->transaction->status);
        $before = $this->fingerprint();
        self::assertSame(404, $this->request("history/{$history->uuid}/status/change", ['status' => 'paid'], true)->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public function test_customer_cannot_mark_history_paid_and_anonymous_requests_do_not_mutate(): void
    {
        $history = $this->withdrawal();
        $before = $this->fingerprint();
        self::assertSame(400, $this->request("history/{$history->uuid}/status/change", ['status' => 'paid'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        $this->authenticate(null);
        self::assertSame(401, $this->send()->getStatusCode());
        self::assertSame(401, $this->request("history/{$history->uuid}/status/change", ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public function test_history_listing_is_owner_scoped_and_shows_completed_debit_as_paid(): void
    {
        $this->withdrawal(2);
        self::assertSame(200, $this->send()->getStatusCode());
        // Real native action/repository/paginator; inspect its resource items
        // without serializing unrelated nested User/role/privacy presentation.
        $request = \App\Http\Requests\FilterParamsRequest::create(
            '/api/v1/dashboard/user/wallet/histories', 'GET', ['wallet_uuid' => 'wallet-2']
        );
        $response = $this->app->make(\App\Http\Controllers\API\v1\Dashboard\User\WalletController::class)
            ->walletHistories($request);
        $rows = $response->collection;
        self::assertCount(1, $rows);
        self::assertSame('wallet-1', $rows[0]->wallet_uuid);
        self::assertSame('withdraw', $rows[0]->type);
        self::assertSame('paid', $rows[0]->status);
    }
}