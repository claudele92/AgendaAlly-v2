<?php

declare(strict_types=1);

namespace Tests\Feature\CountryAdmin;

use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use App\Services\CountryAdminService\CountryAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Assigning a country_admins row must grant 'manager' when the user
 * doesn't already have panel access, or they couldn't reach the admin
 * panel at all (still gated by role:admin|manager). Removing that row
 * must revoke exactly that grant — never a role the user already held
 * independently — so a plain assignment-then-removal can't accidentally
 * leave the user an unrestricted global superadmin, but a user who was
 * already a superadmin before being restricted correctly reverts to
 * being one after the restriction is lifted.
 */
class CountryAdminRoleGrantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['manager', 'admin'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function makeCountry(): Country
    {
        $region = Region::query()->create(['active' => true]);

        return Country::query()->create(['region_id' => $region->id, 'active' => true]);
    }

    public function test_assigning_a_plain_user_grants_manager_and_removal_revokes_it(): void
    {
        $country = $this->makeCountry();
        $user    = User::factory()->create();

        $this->assertFalse($user->hasRole('manager'));

        $service = new CountryAdminService();
        $result  = $service->create(['user_id' => $user->id, 'country_id' => $country->id]);

        $this->assertTrue($result['status']);
        $admin = $result['data'];

        $user->refresh();
        $this->assertTrue($user->hasRole('manager'), 'assignment must grant manager so the panel is reachable');
        $this->assertTrue($admin->manager_role_granted);

        $service->deleteAdmin($admin);

        $user->refresh();
        $this->assertFalse($user->hasRole('manager'), 'removal must revoke the role it granted');
        $this->assertFalse($user->isSuperAdmin(), 'must never become an unrestricted superadmin by accident');
    }

    public function test_removing_an_assignment_from_a_pre_existing_manager_keeps_their_role(): void
    {
        $country = $this->makeCountry();
        $user    = User::factory()->create();
        $user->assignRole('manager');

        $service = new CountryAdminService();
        $result  = $service->create(['user_id' => $user->id, 'country_id' => $country->id]);

        $this->assertTrue($result['status']);
        $admin = $result['data'];

        $this->assertFalse($admin->manager_role_granted, 'must not claim credit for a role the user already had');

        $service->deleteAdmin($admin);

        $user->refresh();
        $this->assertTrue($user->hasRole('manager'), 'a role held before the assignment is never ours to revoke');
        $this->assertTrue($user->isSuperAdmin(), 'lifting the restriction correctly reverts them to their prior unrestricted state');
    }
}
