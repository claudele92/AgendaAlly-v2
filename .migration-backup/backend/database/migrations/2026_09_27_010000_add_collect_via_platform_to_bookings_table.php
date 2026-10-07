<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Freezes the shop's collect_via_platform setting onto the booking itself
 * at creation time, the same way service_fee/commission_fee are already
 * frozen from Settings/ServiceMaster in BookingService::beforeSave() -
 * closing the race window where checkout (which reads the shop's setting
 * live, to decide the gateway) and settlement (previously also reading it
 * live, to decide whether to write a payable ledger row) could disagree if
 * the shop flips the toggle in between. TransactionObserver now reads this
 * frozen column instead of the shop's live setting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('collect_via_platform')->default(false)->after('shop_location_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('collect_via_platform');
        });
    }
};
