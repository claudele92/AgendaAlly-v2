<?php

declare(strict_types=1);

use Database\Seeders\Support\SellerCurrencyBackfiller;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the backup table for, then runs, SellerCurrencyBackfiller — see
 * that class for what it backfills and why it's safe to call more than
 * once. DatabaseSeeder also calls it after seeding, since on a fresh
 * `migrate:fresh --seed` the shop_locations/countries rows this reads are
 * still empty at migration time and only get their data during the seed
 * phase that follows (though ShopLocation::booted()'s saved() sync should
 * already have set every fresh-seeded seller's currency_id by then — this
 * is just the same double-safety-net pattern CountryDefaultsBackfiller's
 * migration counterpart uses).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable(SellerCurrencyBackfiller::BACKUP_TABLE)) {
            Schema::create(SellerCurrencyBackfiller::BACKUP_TABLE, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
            });
        }

        SellerCurrencyBackfiller::run();
    }

    public function down(): void
    {
        if (Schema::hasTable(SellerCurrencyBackfiller::BACKUP_TABLE)) {
            $ids = DB::table(SellerCurrencyBackfiller::BACKUP_TABLE)->pluck('user_id');
            DB::table('users')->whereIn('id', $ids)->update(['currency_id' => null]);
            Schema::dropIfExists(SellerCurrencyBackfiller::BACKUP_TABLE);
        }
    }
};
