<?php

declare(strict_types=1);

namespace Tests\Feature\CountryPayments;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers the country currency_id + country_payments backfill migration, and
 * confirms it never touches historical bookings/orders — they snapshot
 * `currency_id` + `rate` at creation time and must stay that way regardless
 * of a country's currency changing later.
 */
class CountryCurrencyBackfillTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        return require database_path('migrations/2026_09_05_030000_backfill_country_currency_and_payments.php');
    }

    public function test_it_backfills_currency_for_countries_missing_one(): void
    {
        $defaultCurrency = Currency::factory()->create(['title' => 'Default', 'default' => true]);
        $otherCurrency   = Currency::factory()->create(['title' => 'Other', 'default' => false]);

        $countryNoCurrency  = Country::query()->create(['active' => true, 'code' => 'ZZ', 'currency_id' => null]);
        $countryHasCurrency = Country::query()->create(['active' => true, 'code' => 'YY', 'currency_id' => $otherCurrency->id]);

        $this->migration()->up();

        $this->assertSame($defaultCurrency->id, $countryNoCurrency->fresh()->currency_id);
        $this->assertSame($otherCurrency->id, $countryHasCurrency->fresh()->currency_id);
    }

    public function test_it_enables_all_active_gateways_for_active_countries_only(): void
    {
        Currency::factory()->create(['title' => 'Default', 'default' => true]);

        $activeCountry   = Country::query()->create(['active' => true, 'code' => 'ZZ']);
        $inactiveCountry = Country::query()->create(['active' => false, 'code' => 'YY']);

        $activePayment = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_STRIPE]);
        Payment::factory()->create(['active' => false, 'tag' => Payment::TAG_PAY_PAL]); // must never get enabled anywhere
        $walletPayment = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_WALLET]);
        $cashPayment   = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_CASH]);

        $this->migration()->up();

        $this->assertTrue(
            $activeCountry->fresh()->payments()->where('payment_id', $activePayment->id)->exists()
        );
        $this->assertSame(0, $inactiveCountry->fresh()->payments()->count());
        // Only the one external gateway — wallet/cash are excluded entirely,
        // they stay always-available everywhere rather than being scoped.
        $this->assertSame(1, $activeCountry->fresh()->payments()->count());
        $this->assertFalse($activeCountry->fresh()->payments()->where('payment_id', $walletPayment->id)->exists());
        $this->assertFalse($activeCountry->fresh()->payments()->where('payment_id', $cashPayment->id)->exists());
    }

    public function test_historical_booking_currency_snapshot_is_unaffected(): void
    {
        $originalCurrency = Currency::factory()->create(['title' => 'Original', 'default' => true, 'rate' => 1.0]);
        $newDefaultLater   = Currency::factory()->create(['title' => 'Other', 'default' => false]);

        $master   = User::factory()->create();
        $customer = User::factory()->create();

        $bookingId = DB::table('bookings')->insertGetId([
            'master_id'   => $master->id,
            'user_id'     => $customer->id,
            'currency_id' => $originalCurrency->id,
            'rate'        => $originalCurrency->rate,
            'start_date'  => now(),
            'end_date'    => now()->addHour(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $country = Country::query()->create(['active' => true, 'code' => 'ZZ']);

        // Run the backfill, then change the country's currency again afterwards
        // (simulating an admin editing it later) — neither should touch the
        // booking's own snapshotted currency_id/rate.
        $this->migration()->up();
        $country->update(['currency_id' => $newDefaultLater->id]);

        $booking = DB::table('bookings')->find($bookingId);

        $this->assertSame($originalCurrency->id, $booking->currency_id);
        $this->assertEquals($originalCurrency->rate, $booking->rate);
    }
}
