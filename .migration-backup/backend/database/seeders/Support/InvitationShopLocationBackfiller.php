<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copies every existing invitations.shop_location_id value into the new
 * invitation_shop_locations pivot before that column is dropped (see the
 * two migrations either side of this one — create-pivot-and-backfill,
 * then drop-column). Uses raw DB::table() rather than the Invitation/
 * ShopLocation Eloquent models, same reasoning as
 * CountryRoleDefaultsBackfiller: this runs from a migration, and
 * migrations should stay independent of application model classes that
 * can change shape over time.
 *
 * Unlike CountryDefaultsBackfiller/CountryRoleDefaultsBackfiller, this
 * does NOT get a second call from DatabaseSeeder: those backfill template/
 * default data that only exists once other seeders have run, so a fresh
 * `migrate:fresh --seed` needs the post-seed call to actually do anything.
 * This backfill's source is a schema column, present (if not yet empty)
 * at migration time on both a fresh install and an existing one — a
 * single call from the create-pivot migration covers both. The
 * hasColumn() guard exists only so this is a harmless no-op if ever
 * called after the column-drop migration that follows it.
 */
class InvitationShopLocationBackfiller
{
    public static function run(): void
    {
        if (!Schema::hasColumn('invitations', 'shop_location_id')) {
            return;
        }

        $rows = DB::table('invitations')
            ->whereNotNull('shop_location_id')
            ->get(['id', 'shop_location_id']);

        foreach ($rows as $row) {
            $exists = DB::table('invitation_shop_locations')
                ->where('invitation_id', $row->id)
                ->where('shop_location_id', $row->shop_location_id)
                ->exists();

            if (!$exists) {
                DB::table('invitation_shop_locations')->insert([
                    'invitation_id'    => $row->id,
                    'shop_location_id' => $row->shop_location_id,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }
}
