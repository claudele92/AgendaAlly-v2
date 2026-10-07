<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nullable because a shop that hasn't set up any ShopLocation rows yet
     * (branches are opt-in) must keep booking exactly as before - see
     * BookingService::resolveBookingLocation(). Once a shop has at least one
     * SERVICE location, booking creation requires and validates this column.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('shop_location_id')
                ->nullable()
                ->after('shop_id')
                ->constrained('shop_locations')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_location_id');
        });
    }
};
