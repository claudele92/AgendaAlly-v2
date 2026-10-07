<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Updates the 'find.services' (customer hero, homepage) and
 * 'business.section.title' (business hero, /for-business) translation
 * values to the finalized copy. TranslationSeeder's firstOrCreate() only
 * ever inserts a row that doesn't exist yet - it never overwrites the value
 * of a key that's already been seeded, so an environment that seeded the
 * earlier draft copy (see the translations.php docblocks these two keys
 * carry) needs this migration to actually pick up the final strings. A
 * fresh `migrate:fresh --seed` gets the final copy directly from
 * translations.php and finds nothing to update here - the where() clauses
 * below only match rows still holding the old draft value.
 */
return new class extends Migration {
    private const UPDATES = [
        'find.services' => [
            'old' => 'Find Your Services. Book Your Appointments. Shop Your Favorites.',
            'new' => 'Book Services. Shop Favorites. Simple.',
        ],
        'business.section.title' => [
            'old' => 'Run Your Schedule. Sell Your Products. Grow Your Business.',
            'new' => 'Manage Bookings. Sell More. Grow Faster.',
        ],
    ];

    public function up(): void
    {
        foreach (self::UPDATES as $key => $copy) {
            DB::table('translations')
                ->where('key', $key)
                ->where('locale', 'en')
                ->where('value', $copy['old'])
                ->update(['value' => $copy['new']]);
        }
    }

    public function down(): void
    {
        foreach (self::UPDATES as $key => $copy) {
            DB::table('translations')
                ->where('key', $key)
                ->where('locale', 'en')
                ->where('value', $copy['new'])
                ->update(['value' => $copy['old']]);
        }
    }
};
