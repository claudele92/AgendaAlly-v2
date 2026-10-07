<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A human-readable branch name (e.g. "Bonanjo - Main Studio") shown on the
 * storefront's branch switcher instead of the full street address, which
 * gets crowded once a shop has several branches. Nullable and additive -
 * every existing ShopLocation keeps working unaliased, falling back to its
 * address/city the same way the storefront already falls back elsewhere.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_locations', function (Blueprint $table) {
            $table->string('alias')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('shop_locations', function (Blueprint $table) {
            $table->dropColumn('alias');
        });
    }
};
