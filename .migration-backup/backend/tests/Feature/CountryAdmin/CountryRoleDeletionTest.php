<?php

declare(strict_types=1);

namespace Tests\Feature\CountryAdmin;

use App\Models\CountryInvitation;
use App\Models\CountryPermission;
use App\Models\CountryRole;
use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use App\Services\CountryRoleService\CountryRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting a country_role still referenced by a staff invitation is
 * blocked explicitly (not reassigned/nulled silently) — matches the
 * country_invitations.country_role_id restrictOnDelete FK, mirrors
 * ShopRoleDeletionTest one level up.
 */
class CountryRoleDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function makeCountry(): Country
    {
        $region = Region::query()->create(['active' => true]);

        return Country::query()->create(['region_id' => $region->id, 'active' => true]);
    }

    public function test_deleting_a_role_still_assigned_to_staff_is_blocked(): void
    {
        (new \Database\Seeders\CountryPermissionSeeder())->run();

        $country = $this->makeCountry();
        $admin   = User::factory()->create();
        $role    = CountryRole::query()->create(['country_id' => $country->id, 'name' => 'Front Desk']);
        $role->permissions()->sync(CountryPermission::query()->pluck('id')->take(2));

        $staff = User::factory()->create();
        CountryInvitation::query()->create([
            'country_id'      => $country->id,
            'user_id'         => $staff->id,
            'created_by'      => $admin->id,
            'country_role_id' => $role->id,
            'status'          => CountryInvitation::ACCEPTED,
        ]);

        $result = (new CountryRoleService())->deleteRole($role);

        $this->assertFalse($result['status']);
        $this->assertNotNull(CountryRole::find($role->id), 'role must still exist');
    }

    public function test_deleting_an_unused_role_succeeds(): void
    {
        $country = $this->makeCountry();
        $role    = CountryRole::query()->create(['country_id' => $country->id, 'name' => 'Unused Role']);

        $result = (new CountryRoleService())->deleteRole($role);

        $this->assertTrue($result['status']);
        $this->assertNull(CountryRole::find($role->id));
    }
}
