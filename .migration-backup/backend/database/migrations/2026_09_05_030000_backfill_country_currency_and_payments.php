<?php

declare(strict_types=1);

use Database\Seeders\Support\CountryDefaultsBackfiller;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the backup tables for, then runs, CountryDefaultsBackfiller — see
 * that class for what it backfills and why it's safe to call more than
 * once. DatabaseSeeder also calls it after seeding, since on a fresh
 * `migrate:fresh --seed` the currencies/countries/payments tables this
 * migration reads are still empty at migration time and only get their
 * data during the seed phase that follows.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable(CountryDefaultsBackfiller::CURRENCY_BACKUP_TABLE)) {
            Schema::create(CountryDefaultsBackfiller::CURRENCY_BACKUP_TABLE, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('country_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable(CountryDefaultsBackfiller::PAYMENTS_BACKUP_TABLE)) {
            Schema::create(CountryDefaultsBackfiller::PAYMENTS_BACKUP_TABLE, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('country_payment_id');
                $table->timestamps();
            });
        }

        CountryDefaultsBackfiller::run();
    }

    public function down(): void
    {
        if (Schema::hasTable(CountryDefaultsBackfiller::PAYMENTS_BACKUP_TABLE)) {
            $ids = DB::table(CountryDefaultsBackfiller::PAYMENTS_BACKUP_TABLE)->pluck('country_payment_id');
            DB::table('country_payments')->whereIn('id', $ids)->delete();
            Schema::dropIfExists(CountryDefaultsBackfiller::PAYMENTS_BACKUP_TABLE);
        }

        if (Schema::hasTable(CountryDefaultsBackfiller::CURRENCY_BACKUP_TABLE)) {
            $ids = DB::table(CountryDefaultsBackfiller::CURRENCY_BACKUP_TABLE)->pluck('country_id');
            DB::table('countries')->whereIn('id', $ids)->update(['currency_id' => null]);
            Schema::dropIfExists(CountryDefaultsBackfiller::CURRENCY_BACKUP_TABLE);
        }
    }
};
