<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use App\Support\DefaultCountryRoles;
use Illuminate\Support\Facades\DB;

/**
 * Backfills the 3 default country staff roles (see DefaultCountryRoles)
 * onto every country that doesn't already have them.
 *
 * Uses raw DB::table() calls rather than the CountryRole/CountryPermission
 * Eloquent models, same reasoning as CountryDefaultsBackfiller: this runs
 * from a migration (see the 2026_09_07_050000 migration below), and
 * migrations should stay independent of application model classes that
 * can change shape over time. DefaultCountryRoles itself has no such
 * dependency, so both this class and CountryRoleService::seedDefaultRoles()
 * (the Eloquent version CountryObserver::created() uses for new countries)
 * read the exact same role/permission definitions without either one
 * needing the other.
 *
 * Every write is an existence-checked upsert, so this is safe to call more
 * than once — once from that migration (for an existing install, where
 * countries/country_permissions already have data at migration time) and
 * again from DatabaseSeeder after seeding (for a fresh `migrate:fresh
 * --seed`, where those tables are still empty during the migration phase).
 * On a fresh seed this ends up a no-op in practice: DemoAfricaSeeder creates
 * every demo country via Country::create(), so CountryObserver::created()
 * already seeds them before this ever runs — this call exists for
 * completeness/symmetry with CountryDefaultsBackfiller, and as a safety net
 * should a future seeder ever create a country through a path that bypasses
 * that observer.
 */
class CountryRoleDefaultsBackfiller
{
    public static function run(): void
    {
        DB::transaction(function () {
            $countryIds = DB::table('countries')->pluck('id');

            if ($countryIds->isEmpty()) {
                return;
            }

            $allPermissionIds = DB::table('country_permissions')
                ->whereNotIn('group', DefaultCountryRoles::PLATFORM_ONLY_GROUPS)
                ->pluck('id');

            if ($allPermissionIds->isEmpty()) {
                return;
            }

            foreach ($countryIds as $countryId) {
                foreach (DefaultCountryRoles::DEFINITIONS as $definition) {
                    $roleId = DB::table('country_roles')
                        ->where('country_id', $countryId)
                        ->where('name', $definition['name'])
                        ->value('id');

                    if (!$roleId) {
                        $roleId = DB::table('country_roles')->insertGetId([
                            'country_id' => $countryId,
                            'name'       => $definition['name'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $permissionIds = $definition['permissions'] === 'all'
                        ? $allPermissionIds
                        : DB::table('country_permissions')
                            ->whereIn('key', $definition['permissions'])
                            ->pluck('id');

                    foreach ($permissionIds as $permissionId) {
                        $exists = DB::table('country_role_permissions')
                            ->where('country_role_id', $roleId)
                            ->where('country_permission_id', $permissionId)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        DB::table('country_role_permissions')->insert([
                            'country_role_id'       => $roleId,
                            'country_permission_id' => $permissionId,
                        ]);
                    }
                }
            }
        });
    }
}
