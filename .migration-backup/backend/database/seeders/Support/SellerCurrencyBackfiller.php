<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;

/**
 * Backfills users.currency_id for every seller (a user that owns a shop)
 * whose currency_id is still NULL, resolved from that shop's own country -
 * using raw DB::table() queries rather than the Shop/ShopLocation Eloquent
 * models, same reasoning as InvitationShopLocationBackfiller: this runs
 * from a migration, and migrations should stay independent of application
 * model classes that can change shape over time.
 *
 * Mirrors Shop::checkoutCountry()/displayCurrency() (PRODUCT location
 * preferred, falling back to SERVICE; first row by id if a shop somehow has
 * more than one of a type). Only ever touches a NULL currency_id - never a
 * seller's already-set value, whether that came from UserService::
 * updateCurrency() or a previous run of this same backfill - same
 * conservative rule CountryDefaultsBackfiller uses for countries.currency_id.
 *
 * Records exactly which user ids it changes in the backup table this
 * migration owns, so the migration's down() reverses precisely what this
 * backfilled - not any currency a seller has since chosen themselves.
 *
 * Going forward this is a no-op for any shop created after
 * ShopLocation::booted()'s saved() sync (added alongside this migration):
 * that keeps a seller's currency_id current the moment their location's
 * country is known, on every write path (API, admin, seeders). This
 * backfill only catches shops whose location rows already existed before
 * that event did - most notably the two demo sellers.
 */
class SellerCurrencyBackfiller
{
    public const BACKUP_TABLE = 'seller_currency_backfill_backup';

    public static function run(): void
    {
        DB::transaction(function () {
            $shops = DB::table('shops')
                ->whereNotNull('user_id')
                ->get(['id', 'user_id']);

            foreach ($shops as $shop) {
                $user = DB::table('users')->where('id', $shop->user_id)->first(['id', 'currency_id']);

                if (!$user || $user->currency_id) {
                    continue;
                }

                $countryId = self::resolveCountryId((int) $shop->id, 1) // PRODUCT
                    ?? self::resolveCountryId((int) $shop->id, 2); // SERVICE

                if (!$countryId) {
                    continue;
                }

                $currencyId = DB::table('countries')->where('id', $countryId)->value('currency_id');

                if (!$currencyId) {
                    continue;
                }

                DB::table('users')->where('id', $user->id)->update(['currency_id' => $currencyId]);

                DB::table(self::BACKUP_TABLE)->insert([
                    'user_id'    => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    private static function resolveCountryId(int $shopId, int $locationType): ?int
    {
        return DB::table('shop_locations')
            ->where('shop_id', $shopId)
            ->where('type', $locationType)
            ->whereNotNull('country_id')
            ->orderBy('id')
            ->value('country_id');
    }
}
