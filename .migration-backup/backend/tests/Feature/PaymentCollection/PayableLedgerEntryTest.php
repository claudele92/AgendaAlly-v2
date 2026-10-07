<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentCollection;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regression coverage for TransactionObserver's new second write: a
 * 'payable' ledger row recording what the platform now owes a shop that
 * opted into collect_via_platform, alongside the pre-existing 'fee' row -
 * and for BookingService::reversePayableForCanceledBooking(), which must
 * correct that payable via a new signed row rather than editing it.
 */
class PayableLedgerEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
    }

    private function makeBooking(bool $collectViaPlatform): Booking
    {
        $seller = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id' => $seller->id,
            'collect_via_platform' => $collectViaPlatform,
        ]);
        $master = User::factory()->create();
        $customer = User::factory()->create();
        $currency = Currency::factory()->create();

        return Booking::query()->create([
            'shop_id' => $shop->id,
            'master_id' => $master->id,
            'user_id' => $customer->id,
            'currency_id' => $currency->id,
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => Booking::STATUS_NEW,
            'total_price' => 100,
            'service_fee' => 10,
            'commission_fee' => 5,
            'coupon_price' => 0,
        ]);
    }

    private function markPaid(Booking $booking): Transaction
    {
        $transaction = $booking->createTransaction([
            'price' => $booking->total_price,
            'user_id' => $booking->user_id,
            'status_description' => 'test',
        ]);

        $transaction->update(['status' => Transaction::STATUS_PAID]);

        return $transaction->fresh();
    }

    public function test_default_off_only_writes_the_fee_entry(): void
    {
        $booking = $this->makeBooking(false);
        $transaction = $this->markPaid($booking);

        $entries = PlatformFeeLedgerEntry::query()
            ->where('payable_id', $booking->id)
            ->where('payable_type', Booking::class)
            ->get();

        $this->assertCount(1, $entries);
        $this->assertSame(PlatformFeeLedgerEntry::ENTRY_TYPE_FEE, $entries->first()->entry_type);
        $this->assertEquals(10.0, $entries->first()->amount);
    }

    public function test_opted_in_also_writes_a_payable_entry_for_the_sellers_share(): void
    {
        $booking = $this->makeBooking(true);
        $this->markPaid($booking);

        $payable = PlatformFeeLedgerEntry::query()
            ->where('payable_id', $booking->id)
            ->where('payable_type', Booking::class)
            ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)
            ->first();

        $this->assertNotNull($payable);
        // total_price(100) - service_fee(10) - commission_fee(5) - coupon_price(0)
        $this->assertEquals(85.0, $payable->amount);
    }

    public function test_repeated_paid_transitions_do_not_duplicate_either_entry(): void
    {
        $booking = $this->makeBooking(true);
        $transaction = $this->markPaid($booking);

        // An unrelated update to an already-paid transaction must not
        // create a second pair of ledger rows for the same money.
        $transaction->update(['note' => 'unrelated change']);

        $this->assertSame(
            1,
            PlatformFeeLedgerEntry::query()
                ->where('payable_id', $booking->id)
                ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_FEE)
                ->count()
        );
        $this->assertSame(
            1,
            PlatformFeeLedgerEntry::query()
                ->where('payable_id', $booking->id)
                ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)
                ->count()
        );
    }

    public function test_canceling_a_booking_reverses_its_payable_via_a_new_row_not_a_mutation(): void
    {
        $booking = $this->makeBooking(true);
        $this->markPaid($booking);

        $originalPayable = PlatformFeeLedgerEntry::query()
            ->where('payable_id', $booking->id)
            ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)
            ->first();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'sanctum');

        (new \App\Services\BookingService\BookingService)->statusUpdate($booking->id, [
            'status' => Booking::STATUS_CANCELED,
        ]);

        // The original row must be untouched.
        $this->assertEquals(85.0, $originalPayable->fresh()->amount);
        $this->assertSame(PlatformFeeLedgerEntry::STATUS_PENDING, $originalPayable->fresh()->status);

        $adjustment = PlatformFeeLedgerEntry::query()
            ->where('payable_id', $booking->id)
            ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE_ADJUSTMENT)
            ->first();

        $this->assertNotNull($adjustment);
        $this->assertEquals(-85.0, $adjustment->amount);

        $this->assertEquals(0.0, PlatformFeeLedgerEntry::payableBalanceForShop($booking->shop_id));
    }

    public function test_canceling_a_booking_with_no_payable_entry_is_a_no_op(): void
    {
        $booking = $this->makeBooking(false);
        $this->markPaid($booking);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'sanctum');

        (new \App\Services\BookingService\BookingService)->statusUpdate($booking->id, [
            'status' => Booking::STATUS_CANCELED,
        ]);

        $this->assertSame(
            0,
            PlatformFeeLedgerEntry::query()
                ->where('payable_id', $booking->id)
                ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE_ADJUSTMENT)
                ->count()
        );
    }
}
