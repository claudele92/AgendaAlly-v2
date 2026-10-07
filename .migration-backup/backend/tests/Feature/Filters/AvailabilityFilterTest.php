<?php

declare(strict_types=1);

namespace Tests\Feature\Filters;

use App\Models\Shop;
use App\Models\ShopClosedDate;
use App\Models\ShopWorkingDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the availability filter: the storefront's search
 * bar wrote `date`/`timeFrom`/`timeTo` into the URL, but neither shops.tsx
 * nor shops/page.tsx ever read them back into the shop-listing fetch, so the
 * filter never reached the backend at all - `Shop::scopeFilter`'s existing
 * `date` branch (which expects snake_case `date`/`time_from`/`time_to`) was
 * always a dead code path from the storefront. This locks in the backend
 * contract (param names + basic matching behavior) the frontend fix now
 * relies on.
 */
class AvailabilityFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(): Shop
    {
        $seller = User::factory()->create();

        return Shop::factory()->create(['user_id' => $seller->id]);
    }

    public function test_shop_open_on_requested_day_and_time_is_included(): void
    {
        $shop = $this->makeShop();
        $date = now()->next('monday');

        ShopWorkingDay::query()->create([
            'shop_id' => $shop->id,
            'day' => 'monday',
            'from' => '09:00',
            'to' => '18:00',
            'disabled' => 0,
        ]);

        $result = Shop::query()->filter([
            'date' => $date->format('Y-m-d'),
            'time_from' => '10:00',
            'time_to' => '20:00',
        ])->pluck('id');

        $this->assertTrue($result->contains($shop->id));
    }

    public function test_shop_closed_on_requested_date_is_excluded(): void
    {
        $shop = $this->makeShop();
        $date = now()->next('monday');

        ShopWorkingDay::query()->create([
            'shop_id' => $shop->id,
            'day' => 'monday',
            'from' => '09:00',
            'to' => '18:00',
            'disabled' => 0,
        ]);

        ShopClosedDate::query()->create([
            'shop_id' => $shop->id,
            'date' => $date->format('Y-m-d'),
        ]);

        $result = Shop::query()->filter([
            'date' => $date->format('Y-m-d'),
            'time_from' => '10:00',
            'time_to' => '20:00',
        ])->pluck('id');

        $this->assertFalse($result->contains($shop->id));
    }

    public function test_shop_with_no_working_day_for_requested_weekday_is_excluded(): void
    {
        $shop = $this->makeShop();
        $date = now()->next('monday');

        ShopWorkingDay::query()->create([
            'shop_id' => $shop->id,
            'day' => 'tuesday',
            'from' => '09:00',
            'to' => '18:00',
            'disabled' => 0,
        ]);

        $result = Shop::query()->filter([
            'date' => $date->format('Y-m-d'),
            'time_from' => '10:00',
            'time_to' => '20:00',
        ])->pluck('id');

        $this->assertFalse($result->contains($shop->id));
    }
}
