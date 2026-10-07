<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The country staff roles every country gets automatically — see
 * CountryObserver::created() (new countries, going forward) and
 * CountryRoleDefaultsBackfiller (existing countries, one-off catch-up).
 *
 * Deliberately excludes "Main Accountant" — that role is platform-wide
 * (country_id = null, see the 2026_09_07_030000 migration) and must never
 * be duplicated per country.
 *
 * Pure data, no Eloquent/framework dependency, so it's safe to reference
 * from a migration as well as from application services — CoreRepository/
 * CoreService-based classes and migrations don't mix (see
 * CountryDefaultsBackfiller for why), but a plain data holder like this one
 * carries no such risk.
 *
 * `permissions` is either an array of CountryPermission keys, or the
 * literal string 'all' — meaning every key currently in the catalog, kept
 * as a sentinel rather than an enumerated list so it can't drift from the
 * catalog as permissions are added.
 */
final class DefaultCountryRoles
{
    /**
     * Permission groups that exist in the country_permissions catalog but
     * aren't actually country-scoped data (e.g. currency.* — Currency has
     * no country_id at all; it reuses this catalog/middleware rather than
     * a parallel platform-permission system). Excluded from the 'all'
     * sentinel below so a country's own "everything" role (Country
     * Manager) never inherits a platform-wide capability just because a
     * new key was added to the catalog for something else entirely.
     */
    public const PLATFORM_ONLY_GROUPS = ['currency'];

    public const DEFINITIONS = [
        [
            'name'        => 'Country Manager',
            'permissions' => 'all',
        ],
        [
            'name'        => 'Country Accountant',
            'permissions' => ['transactions.view', 'reports.view'],
        ],
        [
            'name'        => 'Support/Customer Service',
            'permissions' => ['bookings.view', 'orders.view', 'tickets.view', 'tickets.manage', 'vendors.view'],
        ],
    ];
}
