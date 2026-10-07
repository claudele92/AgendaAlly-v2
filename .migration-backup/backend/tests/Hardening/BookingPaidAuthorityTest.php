<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\PaymentEligibility\PaymentContextFactory;
use App\Services\PaymentEligibility\PaymentEligibilityService;

final class BookingPaidAuthorityTest extends BookingPaidAuthorityFixture
{
    public static function forgedInputs(): array
    {
        return [
            'missing verification' => [[]],
            'forged status' => [['status' => 'paid', 'verified' => true]],
            'forged reference' => [['payment_trx_id' => 'forged-reference']],
            'wrong amount' => [['price' => 0.01]],
            'wrong currency' => [['currency_id' => 2, 'currency' => 'USD']],
            'wrong booking fields' => [['booking_id' => 2, 'shop_id' => 2, 'user_id' => 3]],
            'fake complete evidence' => [['payment_trx_id' => 'synthetic-booking-reference', 'price' => 50,
                'currency_id' => 1, 'verified' => true, 'merchant_verified' => true, 'status' => 'paid']],
        ];
    }

    /** @dataProvider forgedInputs */
    public function test_active_configured_eligible_electronic_selection_is_not_provider_proof(array $extra): void
    {
        $decision = (new PaymentEligibilityService)->decision(\App\Models\Payment::findOrFail(8),
            (new PaymentContextFactory)->target('booking_id', 1));
        self::assertTrue($decision['eligible']);
        self::assertTrue($decision['configured']);
        $before = $this->bookingState();
        $response = $this->paymentHttp(array_merge(['payment_sys_id' => 8], $extra));
        self::assertFalse($response->getData(true)['status']);
        self::assertSame($before, $this->bookingState());
        self::assertSame(0, Transaction::where('payable_type', Booking::class)->where('status', 'paid')->count());
        self::assertSame(0, $this->database->table('platform_fee_ledger_entries')->where('payable_type', Booking::class)->count());
    }

    public function test_inactive_unconfigured_and_unsupported_methods_fail_closed(): void
    {
        $this->database->table('platform_payment_configs')->delete();
        $this->database->table('payments')->where('id', 8)->update(['active' => false]);
        $before = $this->bookingState();
        self::assertSame(422, $this->paymentHttp(['payment_sys_id' => 8])->getStatusCode());
        self::assertFalse($this->transactionService->bookingTransaction(1, ['payment_sys_id' => 8])['status']);
        self::assertSame($before, $this->bookingState());
        $this->database->table('payments')->where('id', 8)->update(['active' => true]);
        self::assertFalse($this->transactionService->bookingTransaction(1, ['payment_sys_id' => 8])['status']);
    }

    public function test_foreign_booking_and_direct_service_class_alias_do_not_bypass_boundary(): void
    {
        $this->database->table('bookings')->where('id', 2)->update(['user_id' => 3]);
        $before = $this->bookingState();
        self::assertFalse($this->transactionService->bookingTransaction(2, ['payment_sys_id' => 2])['status']);
        self::assertFalse($this->transactionService->orderTransaction(1, ['payment_sys_id' => 8], Booking::class, 'paid')['status']);
        self::assertSame($before, $this->bookingState());
    }

    public function test_cash_uses_native_paid_amount_and_customer_not_supplied_financial_fields(): void
    {
        self::assertTrue($this->paymentHttp(['payment_sys_id' => 2, 'price' => 0.01, 'user_id' => 3])->getData(true)['status']);
        $transaction = Transaction::where('payable_type', Booking::class)->firstOrFail();
        self::assertSame('paid', $transaction->status);
        self::assertSame(50.0, (float) $transaction->price);
        self::assertSame(2, (int) $transaction->user_id);
        self::assertSame(30.0, (float) Wallet::where('user_id', 2)->value('price'));
        self::assertSame(0, $this->database->table('wallet_histories')->count());
    }

    public function test_wallet_keeps_one_native_debit_history_and_replay_is_non_financial(): void
    {
        Wallet::where('user_id', 2)->update(['price' => 100]);
        self::assertTrue($this->paymentHttp(['payment_sys_id' => 1, 'price' => 1])->getData(true)['status']);
        self::assertSame(50.0, (float) Wallet::where('user_id', 2)->value('price'));
        self::assertSame(1, $this->database->table('wallet_histories')->where('type', 'withdraw')->count());
        $before = $this->bookingState();
        self::assertTrue($this->paymentHttp(['payment_sys_id' => 1])->getData(true)['status']);
        self::assertSame($before, $this->bookingState());
    }

    public function test_legacy_mtn_booking_without_durable_binding_is_not_settled_or_reclassified(): void
    {
        [$controller, $service] = $this->verifiedMtn();
        $before = $this->bookingState();
        self::assertFalse($controller->checkStatus('synthetic-booking-reference')->getData(true)['status']);
        self::assertSame(0, $service->checks);
        self::assertSame($before, $this->bookingState());
        self::assertFalse($controller->checkStatus('synthetic-booking-reference')->getData(true)['status']);
        self::assertSame($before, $this->bookingState());
        self::assertFalse($this->paymentHttp(['payment_sys_id' => 8, 'payment_trx_id' => 'synthetic-booking-reference'])->getData(true)['status']);
        self::assertSame($before, $this->bookingState());
    }

    public static function providerMismatches(): array
    {
        return [[['amount' => '49.99']], [['currency' => 'USD']], [['externalId' => 'wrong-reference']]];
    }

    /** @dataProvider providerMismatches */
    public function test_legacy_attempt_stays_unfunded_when_provider_evidence_varies(array $override): void
    {
        [$controller, $service] = $this->verifiedMtn();
        $service->result = array_merge($service->result, $override);
        $before = $this->bookingState();
        self::assertFalse($controller->checkStatus('synthetic-booking-reference')->getData(true)['status']);
        self::assertSame($before, $this->bookingState());
    }

    public function test_manual_admin_siblings_do_not_accept_electronic_paid_status_or_a_reason_as_proof(): void
    {
        $booking = Booking::findOrFail(1);
        $transaction = $booking->createTransaction(['price' => 50, 'payment_sys_id' => 8, 'user_id' => 2, 'status' => 'progress']);
        $this->authenticate(3, 'admin');
        $before = $this->bookingState();
        self::assertFalse($this->paymentHttp(['status' => 'paid', 'reason' => 'manual override', 'token' => 'fake'], 'PUT')->getData(true)['status']);
        self::assertFalse($this->transactionService->updateStatus($transaction->id, ['status' => 'paid'])['status']);
        self::assertSame($before, $this->bookingState());
    }

    public function test_foreign_http_booking_and_inconsistent_verified_intent_binding_fail_closed(): void
    {
        $this->database->table('bookings')->where('id', 1)->update(['user_id' => 3]);
        $before = $this->bookingState();
        self::assertSame(403, $this->paymentHttp(['payment_sys_id' => 8])->getStatusCode());
        self::assertSame($before, $this->bookingState());
        $this->database->table('bookings')->where('id', 1)->update(['user_id' => 2]);
        [$controller] = $this->verifiedMtn();
        \App\Models\PaymentProcess::whereKey('synthetic-booking-reference')->update(['model_id' => 2]);
        $before = $this->bookingState();
        self::assertFalse($controller->checkStatus('synthetic-booking-reference')->getData(true)['status']);
        self::assertSame($before, $this->bookingState());
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_booking_can_end_without_turning_an_electronic_pending_transaction_paid(): void
    {
        // Only the post-commit response relationship graph is isolated.
        \Mockery::mock('overload:App\Repositories\BookingRepository\BookingRepository')
            ->shouldReceive('getWith')->andReturn([]);
        \Illuminate\Support\Facades\Schema::table('bookings', fn (\Illuminate\Database\Schema\Blueprint $t) => $t->text('notes')->nullable());
        \Illuminate\Support\Facades\Schema::create('user_member_ships', function (\Illuminate\Database\Schema\Blueprint $t): void {
            $t->increments('id'); $t->integer('user_id'); $t->dateTime('expired_at');
        });
        $booking = Booking::findOrFail(1);
        $booking->createTransaction(['price' => 50, 'user_id' => 2, 'payment_sys_id' => 8, 'status' => 'progress']);
        $before = $this->bookingState();
        $result = (new BookingLifecyclePaymentFixture())->update($booking, ['status' => Booking::STATUS_ENDED]);
        self::assertTrue($result['status'], $result['message'] ?? '');
        self::assertSame(Booking::STATUS_ENDED, Booking::findOrFail(1)->status);
        self::assertSame('progress', Transaction::where('payable_type', Booking::class)->firstOrFail()->status);
        $after = $this->bookingState();
        unset($before['bookings'], $after['bookings']);
        self::assertSame($before, $after);
    }

    public function test_owned_same_currency_children_keep_native_wallet_arithmetic_and_corrupt_children_fail_closed(): void
    {
        $this->database->table('bookings')->where('id', 2)->update(['shop_id' => 1, 'parent_id' => 1, 'total_price' => 20]);
        Wallet::where('user_id', 2)->update(['price' => 100]);
        self::assertTrue($this->transactionService->bookingTransaction(1, ['payment_sys_id' => 1])['status']);
        self::assertSame(30.0, (float) Wallet::where('user_id', 2)->value('price'));
        self::assertSame(2, Transaction::where('payable_type', Booking::class)->where('status', 'paid')->count());
        self::assertSame(70.0, (float) $this->database->table('wallet_histories')->where('type', 'withdraw')->sum('price'));
        $this->database->table('bookings')->where('id', 2)->update(['currency_id' => 2]);
        $before = $this->bookingState();
        self::assertFalse($this->transactionService->bookingTransaction(1, ['payment_sys_id' => 2])['status']);
        self::assertSame($before, $this->bookingState());
    }
}