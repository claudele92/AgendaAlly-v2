<?php

declare(strict_types=1);

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;

/**
 * Backfills sensible defaults for the country currency + country-scoped
 * payment gateway feature:
 *
 *  1. Every country with no `currency_id` set gets the platform's current
 *     default currency (there is no other per-country signal to use).
 *  2. Every active country gets a `country_payments` row for every currently
 *     active external payment gateway (cash/wallet excluded — see below), so
 *     no active country is left with zero available gateways.
 *
 * Every write is behind an existence/null check, so this is safe to call
 * more than once — once from the schema migration below (for an existing
 * install, where `currencies`/`countries`/`payments` already have data at
 * migration time) and again from DatabaseSeeder after seeding (for a fresh
 * `migrate:fresh --seed`, where those tables are still empty during the
 * migration phase and only get populated afterwards). It's a harmless no-op
 * against whichever of those tables aren't populated yet at call time.
 *
 * Records exactly which rows it creates in the two backup tables the
 * migration owns, so the migration's down() reverses precisely what this
 * backfilled — not any currency assignment or country_payments row an admin
 * sets afterwards through the country settings screen.
 */
class CountryDefaultsBackfiller
{
    public const CURRENCY_BACKUP_TABLE = 'country_currency_backfill_backup';
    public const PAYMENTS_BACKUP_TABLE = 'country_payments_backfill_backup';

    public static function run(): void
    {
        DB::transaction(function () {
            $defaultCurrencyId = DB::table('currencies')->where('default', 1)->value('id');

            if ($defaultCurrencyId) {
                $countryIds = DB::table('countries')->whereNull('currency_id')->pluck('id');

                foreach ($countryIds as $countryId) {
                    DB::table(self::CURRENCY_BACKUP_TABLE)->insert([
                        'country_id' => $countryId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($countryIds->isNotEmpty()) {
                    DB::table('countries')->whereIn('id', $countryIds)->update([
                        'currency_id' => $defaultCurrencyId,
                    ]);
                }
            }

            $activeCountryIds = DB::table('countries')->where('active', true)->pluck('id');

            // 'cash' and 'wallet' (Payment::TAG_CASH / TAG_WALLET) are payment
            // *methods*, not rails tied to a country's banking/regulatory setup
            // the way Stripe/PayStack/Orange Money etc. are. They stay always
            // available everywhere and are excluded from country scoping.
            $activePaymentIds = DB::table('payments')
                ->where('active', true)
                ->whereNotIn('tag', ['cash', 'wallet'])
                ->pluck('id');

            foreach ($activeCountryIds as $countryId) {
                foreach ($activePaymentIds as $paymentId) {
                    $exists = DB::table('country_payments')
                        ->where('country_id', $countryId)
                        ->where('payment_id', $paymentId)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $id = DB::table('country_payments')->insertGetId([
                        'country_id' => $countryId,
                        'payment_id' => $paymentId,
                        'active'     => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table(self::PAYMENTS_BACKUP_TABLE)->insert([
                        'country_payment_id' => $id,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }
            }
        });
    }
}
