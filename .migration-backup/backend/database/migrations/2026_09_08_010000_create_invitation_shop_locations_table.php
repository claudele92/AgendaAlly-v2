<?php

declare(strict_types=1);

use Database\Seeders\Support\InvitationShopLocationBackfiller;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces invitations.shop_location_id (a single nullable FK — see the
 * 2026_09_07_020000 migration) with a many-to-many pivot, so one
 * invitation can be assigned to more than one branch — e.g. both the
 * PRODUCT and SERVICE ShopLocation rows for the same city, which are
 * structurally separate rows even when they represent "the same place"
 * (see DemoAfricaSeeder). Backfills existing shop_location_id values into
 * the pivot before the follow-up migration drops that column.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('invitation_shop_locations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invitation_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('shop_location_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['invitation_id', 'shop_location_id']);
        });

        InvitationShopLocationBackfiller::run();
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_shop_locations');
    }
};
