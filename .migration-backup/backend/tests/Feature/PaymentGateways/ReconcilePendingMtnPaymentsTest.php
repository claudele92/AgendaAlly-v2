<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Booking;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\PlatformPaymentConfig;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * A successful MTN payment whose webhook never arrives must still get
 * recorded — that's the whole point of this scheduled command. Covers:
 * a resolvable-but-unresolved transaction gets checked and marked paid;
 * one too fresh to check yet is left alone; one abandoned past the
 * safety cutoff is marked failed without calling MTN again; and a
 * platform-fee (subscription) transaction, resolved against
 * PlatformPaymentConfig rather than ShopPayment, is reconciled the same
 * way a shop-level one is.
 */
class ReconcilePendingMtnPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function makeShopAndPayment(): array
    {
        $currency = Currency::query()->create(['title' => 'GHS', 'symbol' => 'GHS', 'rate' => 1, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);
        $country  = Country::query()->create(['region_id' => $region->id, 'active' => true, 'currency_id' => $currency->id]);

        $shop = Shop::factory()->create([
            'user_id'       => User::factory()->create()->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id' => $shop->id, 'region_id' => $region->id, 'country_id' => $country->id, 'type' => ShopLocation::PRODUCT,
        ]);

        $payment = Payment::query()->create(['tag' => Payment::TAG_MTN, 'active' => true, 'input' => 15]);
        $country->payments()->attach($payment->id, ['active' => true]);

        ShopPayment::query()->create([
            'shop_id' => $shop->id, 'payment_id' => $payment->id, 'status' => true,
            'subscription_key' => 'sub-key', 'api_user' => 'user', 'api_key' => 'key',
            'target_environment' => 'mtnghana', 'currency' => 'GHS',
        ]);

        $booking = Booking::query()->create([
            'shop_id'     => $shop->id,
            'master_id'   => User::factory()->create()->id,
            'user_id'     => User::factory()->create()->id,
            'currency_id' => $currency->id,
            'start_date'  => now(),
            'end_date'    => now()->addHour(),
            'total_price' => 300,
            'rate'        => 1,
        ]);

        return [$payment, $booking];
    }

    private function makePendingProcess(Payment $payment, Booking $booking, string $requestedAt): PaymentProcess
    {
        $referenceId = 'ref-' . uniqid();

        return PaymentProcess::updateOrCreate([
            'user_id'    => $booking->user_id,
            'model_type' => Booking::class,
            'model_id'   => $booking->id,
        ], [
            'id'   => $referenceId,
            'data' => [
                'payment_id'       => $payment->id,
                'mtn_reference_id' => $referenceId,
                'mtn_resolved'     => false,
                'requested_at'     => $requestedAt,
                'model_type'       => Booking::class,
                'model_id'         => $booking->id,
                'status'           => Booking::STATUS_PROGRESS,
            ],
        ]);
    }

    public function test_a_resolvable_pending_payment_is_checked_and_marked_paid(): void
    {
        [$payment, $booking] = $this->makeShopAndPayment();
        $process = $this->makePendingProcess($payment, $booking, now()->subMinutes(5)->toIso8601String());

        Http::fake([
            '*/collection/token/'                     => Http::response(['access_token' => 'tok'], 200),
            '*/collection/v1_0/requesttopay/*'        => Http::response(['status' => 'SUCCESSFUL'], 200),
        ]);

        $this->artisan('mtn:reconcile-pending-payments')->assertExitCode(0);

        $process->refresh();
        $this->assertTrue($process->data['mtn_resolved']);
        $this->assertSame(Transaction::STATUS_PAID, $process->data['status']);
    }

    public function test_a_too_fresh_pending_payment_is_left_untouched(): void
    {
        [$payment, $booking] = $this->makeShopAndPayment();
        $process = $this->makePendingProcess($payment, $booking, now()->subSeconds(30)->toIso8601String());

        Http::fake();

        $this->artisan('mtn:reconcile-pending-payments')->assertExitCode(0);

        Http::assertNothingSent();

        $process->refresh();
        $this->assertFalse($process->data['mtn_resolved']);
    }

    public function test_an_abandoned_payment_past_the_cutoff_is_marked_failed_without_calling_mtn(): void
    {
        [$payment, $booking] = $this->makeShopAndPayment();
        $process = $this->makePendingProcess($payment, $booking, now()->subHours(25)->toIso8601String());

        Http::fake();

        $this->artisan('mtn:reconcile-pending-payments')->assertExitCode(0);

        Http::assertNothingSent();

        $process->refresh();
        $this->assertTrue($process->data['mtn_resolved']);
        $this->assertSame(Transaction::STATUS_CANCELED, $process->data['status']);
    }

    public function test_a_platform_level_pending_payment_is_also_reconciled(): void
    {
        $currency = Currency::query()->create(['title' => 'GHS', 'symbol' => 'GHS', 'rate' => 1, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);
        $country  = Country::query()->create(['region_id' => $region->id, 'active' => true, 'currency_id' => $currency->id]);

        $shop = Shop::factory()->create([
            'user_id'       => User::factory()->create()->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id' => $shop->id, 'region_id' => $region->id, 'country_id' => $country->id, 'type' => ShopLocation::PRODUCT,
        ]);

        $payment = Payment::query()->create(['tag' => Payment::TAG_MTN, 'active' => true, 'input' => 15]);
        $country->payments()->attach($payment->id, ['active' => true]);

        PlatformPaymentConfig::query()->create([
            'country_id' => $country->id, 'payment_id' => $payment->id, 'status' => true,
            'subscription_key' => 'plat-sub', 'api_user' => 'plat-user', 'api_key' => 'plat-key',
            'target_environment' => 'mtnghana', 'currency' => 'GHS',
        ]);

        $subscription = Subscription::factory()->create(['price' => 500, 'active' => 1]);
        $referenceId  = 'ref-' . uniqid();

        $process = PaymentProcess::updateOrCreate([
            'user_id'    => $shop->user_id,
            'model_type' => Subscription::class,
            'model_id'   => $subscription->id,
        ], [
            'id'   => $referenceId,
            'data' => [
                'payment_id'       => $payment->id,
                'mtn_reference_id' => $referenceId,
                'mtn_resolved'     => false,
                'requested_at'     => now()->subMinutes(5)->toIso8601String(),
                'model_type'       => Subscription::class,
                'model_id'         => $subscription->id,
                'shop_id'          => $shop->id,
                'status'           => 'new',
            ],
        ]);

        Http::fake([
            '*/collection/token/'              => Http::response(['access_token' => 'tok'], 200),
            '*/collection/v1_0/requesttopay/*' => Http::response(['status' => 'SUCCESSFUL'], 200),
        ]);

        $this->artisan('mtn:reconcile-pending-payments')->assertExitCode(0);

        $process->refresh();
        $this->assertTrue($process->data['mtn_resolved']);
        $this->assertSame(Transaction::STATUS_PAID, $process->data['status']);
    }
}
