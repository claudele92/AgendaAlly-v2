<?php

declare(strict_types=1);

namespace Tests\Feature\CountryAdmin;

use App\Models\CountryAdmin;
use App\Models\Order;
use App\Models\Region;
use App\Models\Country;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The "hard requirement": country restriction must filter the actual
 * underlying queries a country admin's dashboard runs, not just what the
 * UI happens to display — proven here directly against Eloquent (the
 * global scope) rather than only through a controller response, and also
 * covering the *staff* case (an invited user with no country_admins row
 * of their own), which is where CountryContext::restrictedCountryId()
 * originally had a gap that would have leaked every country's data.
 */
class CountryScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['manager', 'admin'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        Cache::put('rjkcvd.ewoidfh', (object) ['local' => true, 'active' => true]);
    }

    /** @return array{0: Country, 1: Shop} */
    private function makeCountryWithShop(): array
    {
        $region  = Region::query()->create(['active' => true]);
        $country = Country::query()->create(['region_id' => $region->id, 'active' => true]);

        $shop = Shop::factory()->create([
            'user_id'       => User::factory()->create()->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id'    => $shop->id,
            'region_id'  => $region->id,
            'country_id' => $country->id,
        ]);

        return [$country, $shop];
    }

    public function test_country_admin_only_sees_orders_from_their_own_country(): void
    {
        [$countryA, $shopA] = $this->makeCountryWithShop();
        [$countryB, $shopB] = $this->makeCountryWithShop();

        $orderA = Order::factory()->create(['shop_id' => $shopA->id]);
        $orderB = Order::factory()->create(['shop_id' => $shopB->id]);

        $admin = User::factory()->create();
        $admin->assignRole('manager');
        CountryAdmin::query()->create(['user_id' => $admin->id, 'country_id' => $countryA->id]);

        $this->actingAs($admin, 'sanctum');

        $visibleIds = Order::query()->pluck('id')->all();

        $this->assertContains($orderA->id, $visibleIds);
        $this->assertNotContains($orderB->id, $visibleIds);
    }

    public function test_superadmin_sees_orders_from_every_country(): void
    {
        [, $shopA] = $this->makeCountryWithShop();
        [, $shopB] = $this->makeCountryWithShop();

        $orderA = Order::factory()->create(['shop_id' => $shopA->id]);
        $orderB = Order::factory()->create(['shop_id' => $shopB->id]);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('manager');

        $this->actingAs($superAdmin, 'sanctum');

        $visibleIds = Order::query()->pluck('id')->all();

        $this->assertContains($orderA->id, $visibleIds);
        $this->assertContains($orderB->id, $visibleIds);
    }

    /**
     * Regression test: staff invited into a country (an accepted
     * country_invitations row) have no country_admins row of their own.
     * CountryContext::restrictedCountryId() must still resolve their
     * restriction from the invitation, or the global scope silently
     * stops applying for every invited staff member.
     */
    public function test_invited_staff_with_no_country_admin_row_is_still_scoped(): void
    {
        [$countryA, $shopA] = $this->makeCountryWithShop();
        [, $shopB] = $this->makeCountryWithShop();

        $orderA = Order::factory()->create(['shop_id' => $shopA->id]);
        $orderB = Order::factory()->create(['shop_id' => $shopB->id]);

        $countryAdminUser = User::factory()->create();
        $countryAdminUser->assignRole('manager');
        CountryAdmin::query()->create(['user_id' => $countryAdminUser->id, 'country_id' => $countryA->id]);

        $role = \App\Models\CountryRole::query()->create(['country_id' => $countryA->id, 'name' => 'Orders Staff']);

        $staff = User::factory()->create();
        $staff->assignRole('manager');

        \App\Models\CountryInvitation::query()->create([
            'country_id'      => $countryA->id,
            'user_id'         => $staff->id,
            'created_by'      => $countryAdminUser->id,
            'country_role_id' => $role->id,
            'status'          => \App\Models\CountryInvitation::ACCEPTED,
        ]);

        $this->actingAs($staff, 'sanctum');

        $visibleIds = Order::query()->pluck('id')->all();

        $this->assertContains($orderA->id, $visibleIds);
        $this->assertNotContains($orderB->id, $visibleIds);
    }
}
