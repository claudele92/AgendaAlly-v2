<?php

declare(strict_types=1);

use Database\Seeders\Support\CountryRoleDefaultsBackfiller;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Runs CountryRoleDefaultsBackfiller — see that class for what it
 * backfills and why it's safe to call more than once. DatabaseSeeder also
 * calls it after seeding, for the same reason CountryDefaultsBackfiller's
 * migration counterpart does: on a fresh `migrate:fresh --seed`, the
 * countries/country_permissions tables this reads are still empty at
 * migration time.
 *
 * This is what makes the auto-seeding in CountryObserver::created() (new
 * countries, going forward) actually take effect for countries that
 * already existed on a real running database before that observer did.
 */
return new class extends Migration {
    public function up(): void
    {
        CountryRoleDefaultsBackfiller::run();
    }

    public function down(): void
    {
        // Additive-only backfill (new country_roles rows; no existing
        // column overwritten), so reversal is just deleting rows that
        // exactly match the 3 template role names — no backup table
        // needed, unlike CountryDefaultsBackfiller's currency_id/
        // country_payments backfill, which overwrites an ambiguous
        // existing column. If an admin has since renamed one of these
        // roles, this intentionally leaves it alone — the name no longer
        // matches what this migration created.
        DB::table('country_roles')
            ->whereIn('name', array_column(\App\Support\DefaultCountryRoles::DEFINITIONS, 'name'))
            ->delete();
    }
};
