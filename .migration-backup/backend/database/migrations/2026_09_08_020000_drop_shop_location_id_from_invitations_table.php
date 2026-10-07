<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops invitations.shop_location_id now that invitation_shop_locations
 * (see the migration immediately before this one) has every value it held,
 * and every read/write site has moved to the pivot instead.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['shop_location_id']);
            $table->dropColumn('shop_location_id');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreignId('shop_location_id')
                ->nullable()
                ->after('shop_role_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }
};
