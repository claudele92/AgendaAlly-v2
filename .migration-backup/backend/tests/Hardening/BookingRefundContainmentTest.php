<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB;

final class BookingRefundContainmentTest extends BookingRefundFixture
{
    public function test_first_cancellation_settles_records_and_original_paid_marker_once(): void
    {
        $original = Booking::find(1)->transaction;
        $this->cancel();
        self::assertSame([100.0, 75.0, 80.0], $this->balances());
        self::assertSame('canceled', Booking::find(1)->status);
        self::assertSame('paid', $original->fresh()->status);
        self::assertNotNull($original->fresh()->refund_time);
        self::assertSame(1, WalletHistory::count());
        self::assertSame(45.0, (float) WalletHistory::first()->price);
        self::assertSame('paid', WalletHistory::first()->transaction->status);
        self::assertSame(4, Transaction::count());
        self::assertSame(1, DB::table('platform_fee_ledger_entries')->where('entry_type', 'payable_adjustment')->count());
        foreach (['canceled', 'booked', 'new', 'progress', 'ended'] as $status) {
            $this->rejects(fn () => $this->bookingService->statusUpdate(1, ['status' => $status]));
        }
    }
    public function test_durable_payment_marker_blocks_replay_even_if_lifecycle_is_overwritten(): void
    {
        $this->cancel();
        DB::table('bookings')->where('id', 1)->update(['status' => 'booked']);
        $this->rejects(fn () => $this->cancel());
        self::assertSame(1, WalletHistory::count());
        $result = $this->bookingService->update(Booking::find(1), ['status' => 'progress']);
        self::assertFalse($result['status']);
        self::assertSame('booked', Booking::find(1)->status);
    }
    public static function policies(): array
    { return [[null, false, 30], ['oops', false, 30], ['-1', false, 30], ['101', false, 30],
        ['NaN', false, 30], ['INF', false, 30], ['', false, 30], ['0', true, 80], ['10', true, 75], ['100', true, 30]]; }
    /** @dataProvider policies */
    public function test_policy_configuration_is_explicit_bounded_and_deterministic(?string $value, bool $allowed, int $balance): void
    {
        if ($value === null) DB::table('settings')->delete();
        else DB::table('settings')->update(['value' => $value]);
        if ($allowed) $this->cancel(); else $this->rejects(fn () => $this->cancel());
        self::assertSame((float) $balance, $this->balances()[1]);
        self::assertSame($allowed && $balance > 30 ? 1 : 0, WalletHistory::count());
    }
    public static function invalidFunding(): array { return [['progress'], ['refund'], ['canceled']]; }
    /** @dataProvider invalidFunding */
    public function test_wallet_refund_requires_authoritatively_paid_funding(string $status): void
    {
        DB::table('transactions')->where('payable_type', Booking::class)->update(['status' => $status]);
        $this->rejects(fn () => $this->cancel(1, ['payment_status' => 'paid']));
    }
    public function test_persisted_funding_caps_refund_and_forged_payload_cannot_redirect(): void
    {
        DB::table('transactions')->where('payable_type', Booking::class)->where('payable_id', 1)->update(['price' => 20]);
        $this->cancel(1, ['refund_amount' => 999, 'total_price' => 999, 'user_id' => 3, 'shop_id' => 2,
            'wallet_uuid' => 'wallet-3', 'payment_id' => 3, 'booking_id' => 2, 'booking_canceled_commission' => 0]);
        self::assertSame([100.0, 48.0, 80.0], $this->balances());
        self::assertSame('wallet-2', WalletHistory::first()->wallet_uuid);
        self::assertSame(18.0, (float) WalletHistory::first()->price);
    }
    public function test_zero_negative_nonfinite_funding_and_currency_mismatch_roll_back(): void
    {
        foreach ([0, -1, 'INF'] as $price) {
            DB::table('transactions')->where('payable_type', Booking::class)->update(['price' => $price]);
            $this->rejects(fn () => $this->cancel());
        }
        DB::table('transactions')->where('payable_type', Booking::class)->update(['price' => 50]);
        DB::table('wallets')->where('user_id', 2)->update(['currency_id' => 2]);
        $this->rejects(fn () => $this->cancel());
    }
    public static function failures(): array { return [['recipient_before'], ['recipient_after']]; }
    /** @dataProvider failures */
    public function test_wallet_failure_before_or_after_real_credit_rolls_back(string $stage): void
    {
        $this->service->failAt = $stage;
        $this->rejects(fn () => $this->cancel());
        $this->service->failAt = null;
        $this->cancel();
        self::assertSame(1, WalletHistory::count());
    }
    public static function sqlFailure(): array { return [['booking_claim'], ['claim'], ['credit'], ['ledger']]; }
    /** @dataProvider sqlFailure */
    public function test_exceptions_after_claim_credit_or_ledger_roll_back(string $stage): void
    {
        DB::listen(function (\Illuminate\Database\Events\QueryExecuted $query) use ($stage): void {
            $hit = match ($stage) {
                'booking_claim' => str_starts_with($query->sql, 'update "bookings"'),
                'claim' => str_starts_with($query->sql, 'update "transactions"') && str_contains($query->sql, 'refund_time'),
                'credit' => str_starts_with($query->sql, 'update "wallets"') && str_contains($query->sql, '+'),
                'ledger' => str_starts_with($query->sql, 'insert into "platform_fee_ledger_entries"'),
            };
            if ($hit) throw new \RuntimeException("isolated $stage failure");
        });
        $this->rejects(fn () => $this->cancel());
    }
    public function test_final_booking_persistence_failure_rolls_back_credit_and_accounting(): void
    {
        DB::table('point_histories')->insert(['model_type' => Booking::class, 'model_id' => 1,
            'user_id' => 2, 'price' => 2]);
        Booking::updating(fn () => throw new \RuntimeException('isolated finalization failure'));
        $this->rejects(fn () => $this->cancel());
    }
    public function test_cash_has_no_principal_credit_and_electronic_fee_is_once_only_not_provider_refund(): void
    {
        DB::table('transactions')->where('payable_type', Booking::class)->where('payable_id', 1)->update(['payment_sys_id' => 2]);
        $this->cancel();
        self::assertSame(0, WalletHistory::count());
        self::assertSame(30.0, $this->balances()[1]);
        DB::table('transactions')->where('payable_type', Booking::class)->where('payable_id', 2)->update(['payment_sys_id' => 3]);
        $this->authenticate(3, 'admin');
        $this->cancel(2);
        self::assertSame(25.0, $this->balances()[1]);
        self::assertSame(0, WalletHistory::count()); // Native electronic fee is a direct debit.
        $this->rejects(fn () => $this->cancel(2));
    }
    public function test_late_wallet_cancellation_consumes_entitlement_without_zero_history(): void
    {
        DB::table('bookings')->where('id', 1)->update(['start_date' => '2020-01-01 09:00:00']);
        DB::table('settings')->delete();
        $this->cancel();
        self::assertSame(0, WalletHistory::count());
        self::assertSame(30.0, $this->balances()[1]);
        $this->rejects(fn () => $this->cancel());
    }
    public function test_customer_parent_cancellation_claims_each_payment_once_and_group_failure_rolls_back(): void
    {
        DB::table('bookings')->where('id', 2)->update(['parent_id' => 1, 'shop_id' => 1]);
        $this->authenticate(2, 'user');
        DB::table('transactions')->where('payable_type', Booking::class)->where('payable_id', 2)->update(['status' => 'progress']);
        $this->rejects(fn () => $this->bookingService->canceledByParent(1, ['status' => 'canceled']));
        DB::table('transactions')->where('payable_type', Booking::class)->where('payable_id', 2)->update(['status' => 'paid']);
        $this->bookingService->canceledByParent(1, ['status' => 'canceled', 'user_id' => 3]);
        self::assertSame(120.0, $this->balances()[1]);
        self::assertSame(2, WalletHistory::count());
        $this->rejects(fn () => $this->bookingService->canceledByParent(1, ['status' => 'canceled']));
    }
    public static function invalidWindow(): array { return [['oops'], ['-1'], ['INF']]; }
    /** @dataProvider invalidWindow */
    public function test_malformed_window_fails_without_choosing_business_policy(string $value): void
    {
        DB::table('settings')->insert(['key' => 'booking_refund_canceled_hour', 'value' => $value]);
        $this->rejects(fn () => $this->cancel());
    }
    public function test_cashback_recovery_is_atomic_and_once_only(): void
    {
        DB::table('point_histories')->insert(['model_type' => Booking::class, 'model_id' => 1,
            'user_id' => 2, 'price' => 2]);
        $this->cancel();
        self::assertSame(73.0, $this->balances()[1]);
        self::assertSame(0, DB::table('point_histories')->count());
        $this->rejects(fn () => $this->cancel());
    }
}