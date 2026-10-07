<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentCollection;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Currency;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BookingService\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the snapshot-race window flagged on PR #164:
 * checkout (BaseService::resolveGatewayConfig()) and settlement
 * (TransactionObserver) both used to read the shop's LIVE
 * collect_via_platform setting independently, so a shop flipping the
 * toggle between those two moments could make them disagree about whether
 * the platform actually holds the seller's share of a given payment.
 *
 * BookingService::beforeSave() now freezes collect_via_platform onto the
 * booking itself at creation time - the same spot service_fee/
 * commission_fee are already frozen - and TransactionObserver reads that
 * frozen column instead of the shop's current setting. These tests prove a
 * toggle change AFTER a booking exists never changes what that specific
 * booking settles as, in either direction.
 */
class CollectViaPlatformSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(bool $collectViaPlatform): Shop
    {
        $seller = User::factory()->create();

        return Shop::factory()->create([
            'user_id' => $seller->id,
            'collect_via_platform' => $collectViaPlatform,
        ]);
    }

    private function makeServiceMaster(Shop $shop, User $master): ServiceMaster
    {
        $service = Service::query()->create([
            'category_id' => Category::factory()->create()->id,
            'shop_id' => $shop->id,
        ]);

        return ServiceMaster::query()->create([
            'service_id' => $service->id,
            'master_id' => $master->id,
            'shop_id' => $shop->id,
            'active' => true,
            'price' => 100,
            'commission_fee' => 5,
        ]);
    }

    /**
     * Runs the real BookingService::beforeSave() snapshot logic (not a
     * hand-built array) so this test exercises the exact code path that
     * freezes collect_via_platform, then persists the result as a real
     * Booking row.
     */
    private function makeBookingViaBeforeSave(Shop $shop, ServiceMaster $serviceMaster, User $customer): Booking
    {
        $currency = Currency::factory()->create();

        $shopId = null;
        $frozen = (new BookingService)->beforeSave(
            ['service_master_id' => $serviceMaster->id],
            1,
            $shopId
        );

        return Booking::query()->create(array_merge($frozen, [
            'user_id' => $customer->id,
            'currency_id' => $currency->id,
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => Booking::STATUS_NEW,
            'total_price' => 100,
            'coupon_price' => 0,
        ]));
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

    public function test_toggle_was_on_at_creation_stays_on_even_after_shop_later_turns_it_off(): void
    {
        $shop = $this->makeShop(true);
        $master = User::factory()->create();
        $customer = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $booking = $this->makeBookingViaBeforeSave($shop, $serviceMaster, $customer);

        $this->assertTrue((bool) $booking->collect_via_platform);

        // The shop opts back out AFTER the booking already exists.
        $shop->update(['collect_via_platform' => false]);

        $this->markPaid($booking);

        $payable = PlatformFeeLedgerEntry::query()
            ->where('payable_id', $booking->id)
            ->where('payable_type', Booking::class)
            ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)
            ->first();

        $this->assertNotNull(
            $payable,
            'A booking frozen with collect_via_platform=true must still get a payable ledger entry, even if the shop opts out afterward.'
        );
    }

    public function test_toggle_was_off_at_creation_stays_off_even_after_shop_later_turns_it_on(): void
    {
        $shop = $this->makeShop(false);
        $master = User::factory()->create();
        $customer = User::factory()->create();
        $serviceMaster = $this->makeServiceMaster($shop, $master);

        $booking = $this->makeBookingViaBeforeSave($shop, $serviceMaster, $customer);

        $this->assertFalse((bool) $booking->collect_via_platform);

        // The shop opts in AFTER the booking already exists.
        $shop->update(['collect_via_platform' => true]);

        $this->markPaid($booking);

        $payable = PlatformFeeLedgerEntry::query()
            ->where('payable_id', $booking->id)
            ->where('payable_type', Booking::class)
            ->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)
            ->first();

        $this->assertNull(
            $payable,
            'A booking frozen with collect_via_platform=false must never get a payable ledger entry, even if the shop opts in afterward.'
        );
    }
}
