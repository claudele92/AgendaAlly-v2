<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a seller's Service/Product Location act as a real branch (country +
 * city + a specific address/pin), not just a country/city picker. Mirrors
 * the columns already used for this on shops (see
 * 2023_12_18_064533_add_lat_long_column_in_shops_table).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_locations', function (Blueprint $table) {
            $table->string('address')->nullable()->after('area_id');
            $table->double('latitude')->nullable()->after('address');
            $table->double('longitude')->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('shop_locations', function (Blueprint $table) {
            $table->dropColumn(['address', 'latitude', 'longitude']);
        });
    }
};
