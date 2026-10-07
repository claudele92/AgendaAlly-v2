<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopWorkingDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * ReportRepository::performanceDashboard() previously crashed for ANY
 * request (500, "date(): Argument #2 ($timestamp) must be of type ?int,
 * false given") because it reused a MySQL DATE_FORMAT() pattern (e.g.
 * '%Y-%m-%d') directly as a PHP date() format string in its occupancy
 * calculation - PHP doesn't treat '%Y' as a unit, so it produced
 * unparseable garbage like '%2026-%08-%01'. shop_id was also required,
 * blocking a genuine platform-wide (all shops) view even once that was
 * fixed. This covers both: the endpoint no longer crashes for a single
 * shop or platform-wide, and bookings_chart aggregates correctly across
 * shops when shop_id is omitted.
 */
class PerformanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        Role::findOrCreate('manager', 'web');

        $user = User::factory()->create();
        $user->assignRole('manager');

        $this->actingAs($user, 'sanctum');

        return $user;
    }

    private function makeShopWithEndedBooking(float $totalPrice = 11200): Shop
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        $currency = Currency::query()->create(['title' => 'XAF', 'symbol' => 'FCFA', 'rate' => 600, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);

        $seller = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopWorkingDay::query()->create([
            'shop_id' => $shop->id, 'day' => 'monday', 'from' => '09:00', 'to' => '18:00', 'disabled' => false,
        ]);

        $master   = User::factory()->create();
        $customer = User::factory()->create();

        Booking::create([
            'service_master_id' => null,
            'master_id'         => $master->id,
            'user_id'           => $customer->id,
            'shop_id'           => $shop->id,
            'currency_id'       => $currency->id,
            'start_date'        => now()->subDay(),
            'end_date'          => now()->subDay()->addHour(),
            'price'             => 10000,
            'total_price'       => $totalPrice,
            'service_fee'       => 1200,
            'status'            => Booking::STATUS_ENDED,
        ]);

        return $shop;
    }

    private function dateRangeCoveringToday(): array
    {
        return [
            'date_from' => now()->subDays(10)->format('Y-m-d'),
            'date_to'   => now()->addDay()->format('Y-m-d'),
        ];
    }

    public function test_it_no_longer_crashes_when_scoped_to_a_single_shop(): void
    {
        $this->actingAsSuperAdmin();
        $shop = $this->makeShopWithEndedBooking();

        $response = $this->getJson('/api/v1/dashboard/admin/report/performance-dashboard?' . http_build_query(
            $this->dateRangeCoveringToday() + ['type' => 'day', 'shop_id' => $shop->id]
        ));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.bookings_chart'));
        $this->assertSame(11200, $response->json('data.bookings_chart.0.ended_price'));
    }

    public function test_it_works_platform_wide_when_shop_id_is_omitted(): void
    {
        $this->actingAsSuperAdmin();
        $shopA = $this->makeShopWithEndedBooking(11200);
        $this->makeShopWithEndedBooking(5000);

        $response = $this->getJson('/api/v1/dashboard/admin/report/performance-dashboard?' . http_build_query(
            $this->dateRangeCoveringToday() + ['type' => 'day']
        ));

        $response->assertStatus(200);

        // Both shops' bookings roll into the same platform-wide total.
        $this->assertSame(16200, $response->json('data.bookings_chart.0.ended_price'));
        $this->assertSame(2, $response->json('data.bookings_chart.0.ended_count'));

        // Occupancy is a single-shop concept - correctly empty/zeroed
        // platform-wide, not crashing or defaulting to the first shop found.
        $this->assertSame(0, $response->json('data.occupancy_rate'));
        $this->assertEmpty($response->json('data.occupancy_chart'));

        // Silence the unused-variable warning while keeping the fixture
        // call's intent (two distinct shops) obvious at the call site.
        $this->assertNotNull($shopA->id);
    }

    public function test_it_works_for_every_granularity_platform_wide(): void
    {
        $this->actingAsSuperAdmin();
        $this->makeShopWithEndedBooking();

        foreach (['day', 'week', 'month', 'year'] as $type) {
            $response = $this->getJson('/api/v1/dashboard/admin/report/performance-dashboard?' . http_build_query(
                $this->dateRangeCoveringToday() + ['type' => $type]
            ));

            $response->assertStatus(200);
            $this->assertNotEmpty($response->json('data.bookings_chart'), "type=$type should not be empty");
        }
    }
}
