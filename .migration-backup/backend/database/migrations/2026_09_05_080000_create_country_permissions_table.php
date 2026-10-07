<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-defined catalog of permissions a country admin can grant to
 * their own country's staff via a country_role. Same bespoke pattern as
 * shop_permissions — a separate table, not Spatie's own, and not Spatie
 * Teams (see the shop-roles PR for why Teams was ruled out).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->string('label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_permissions');
    }
};
