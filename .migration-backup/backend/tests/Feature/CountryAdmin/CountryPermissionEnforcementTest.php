<?php

declare(strict_types=1);

namespace Tests\Feature\CountryAdmin;

use App\Models\CountryAdmin;
use App\Models\CountryInvitation;
use App\Models\CountryPermission;
use App\Models\CountryRole;
use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the bespoke country_roles permission system: staff whose role
 * only grants orders.* permissions can reach the orders endpoint but not
 * the transactions one, while the assigned country admin always has
 * every permission within their own country regardless of any
 * country_role — see User::hasCountryPermission().
 */
class CountryPermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('manager', 'web');

        (new \Database\Seeders\CountryPermissionSeeder())->run();

        Cache::put('rjkcvd.ewoidfh', (object) ['local' => true, 'active' => true]);
    }

    private function makeCountry(): Country
    {
        $region = Region::query()->create(['active' => true]);

        return Country::query()->create(['region_id' => $region->id, 'active' => true]);
    }

    private function makeStaffWithRole(Country $country, User $admin, array $permissionKeys): User
    {
        $role = CountryRole::query()->create(['country_id' => $country->id, 'name' => 'Limited Staff']);
        $role->permissions()->sync(CountryPermission::query()->whereIn('key', $permissionKeys)->pluck('id'));

        $staff = User::factory()->create();
        $staff->assignRole('manager');

        CountryInvitation::query()->create([
            'country_id'      => $country->id,
            'user_id'         => $staff->id,
            'created_by'      => $admin->id,
            'country_role_id' => $role->id,
            'status'          => CountryInvitation::ACCEPTED,
        ]);

        return $staff;
    }

    public function test_staff_with_orders_only_role_cannot_hit_transactions_endpoint(): void
    {
        $country = $this->makeCountry();
        $admin   = User::factory()->create();
        $admin->assignRole('manager');
        CountryAdmin::query()->create(['user_id' => $admin->id, 'country_id' => $country->id]);

        $staff = $this->makeStaffWithRole($country, $admin, ['orders.view']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/dashboard/admin/transactions/paginate');

        $response->assertStatus(403);
    }

    public function test_staff_with_orders_only_role_can_hit_orders_endpoint(): void
    {
        $country = $this->makeCountry();
        $admin   = User::factory()->create();
        $admin->assignRole('manager');
        CountryAdmin::query()->create(['user_id' => $admin->id, 'country_id' => $country->id]);

        $staff = $this->makeStaffWithRole($country, $admin, ['orders.view']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/dashboard/admin/orders/paginate');

        $response->assertStatus(200);
    }

    public function test_country_admin_has_every_permission_with_no_country_role_at_all(): void
    {
        $country = $this->makeCountry();
        $admin   = User::factory()->create();
        $admin->assignRole('manager');
        CountryAdmin::query()->create(['user_id' => $admin->id, 'country_id' => $country->id]);

        $this->assertTrue($admin->hasCountryPermission($country->id, 'transactions.manage'));
        $this->assertTrue($admin->hasCountryPermission($country->id, 'staff.roles.manage'));
        $this->assertTrue($admin->hasCountryPermission($country->id, 'anything.not.in.the.catalog'));
    }

    public function test_country_admin_has_no_permission_for_a_different_country(): void
    {
        $countryA = $this->makeCountry();
        $countryB = $this->makeCountry();

        $admin = User::factory()->create();
        $admin->assignRole('manager');
        CountryAdmin::query()->create(['user_id' => $admin->id, 'country_id' => $countryA->id]);

        $this->assertFalse($admin->hasCountryPermission($countryB->id, 'orders.view'));
    }

    public function test_superadmin_has_every_permission_for_every_country(): void
    {
        $countryA = $this->makeCountry();
        $countryB = $this->makeCountry();

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('manager');

        $this->assertTrue($superAdmin->hasCountryPermission($countryA->id, 'orders.view'));
        $this->assertTrue($superAdmin->hasCountryPermission($countryB->id, 'transactions.manage'));
    }
}
