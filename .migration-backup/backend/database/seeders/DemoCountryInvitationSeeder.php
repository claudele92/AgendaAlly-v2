<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryInvitation;
use App\Models\CountryRole;
use App\Models\User;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * A real demo assignment for the platform-wide "Main Accountant" role (see
 * DemoStaffRolesSeeder) — CheckCountryPermission resolves the acting user's
 * country from their own CountryInvitation row, and country_invitations.
 * country_id is non-nullable, so even a platform-wide role (country_id =
 * NULL on the role itself) still needs a real country on the grant to
 * actually be checkable. Cameroon is used here only as that anchor
 * country, not because the role is Cameroon-specific.
 *
 * Runs after UserSeeder (needs user 115), DemoAfricaSeeder (needs Cameroon)
 * and DemoStaffRolesSeeder (needs the Main Accountant country_role).
 * Keyed by updateOrCreate(user_id, country_id), so safe to run more than
 * once.
 */
class DemoCountryInvitationSeeder extends Seeder
{
    use Loggable;

    // See UserSeeder — the demo platform finance user being invited, and
    // UserSeeder/DatabaseSeeder — the seeded superadmin doing the inviting.
    private const MAIN_ACCOUNTANT_USER_ID = 115;
    private const INVITED_BY_USER_ID = 103;

    public function run(): void
    {
        try {
            $user = User::find(self::MAIN_ACCOUNTANT_USER_ID);
            $cameroon = Country::where('code', 'cm')->first();
            $mainAccountantRole = CountryRole::whereNull('country_id')
                ->where('name', 'Main Accountant')
                ->first();

            if (!$user || !$cameroon || !$mainAccountantRole) {
                return;
            }

            CountryInvitation::updateOrCreate([
                'user_id'    => $user->id,
                'country_id' => $cameroon->id,
            ], [
                'created_by'      => self::INVITED_BY_USER_ID,
                'country_role_id' => $mainAccountantRole->id,
                'status'          => CountryInvitation::ACCEPTED,
            ]);

            // Needed to pass the outer `role:admin|manager` route-group
            // gate before CheckCountryPermission's finer check ever runs —
            // mirrors the note on User::isSuperAdmin() that country staff
            // "typically carry 'manager' too just to pass" that gate.
            $roles   = $user->roles?->pluck('name')?->toArray() ?? [];
            $roles[] = 'manager';
            $user->syncRoles(array_unique($roles));
        } catch (Throwable $e) {
            $this->error($e);
        }
    }
}
