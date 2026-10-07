<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-defined catalog of permissions a seller can grant to their own
 * shop's staff via a shop_role. Deliberately separate from Spatie's own
 * `permissions` table (used only by the fixed global roles: user, seller,
 * master, deliveryman, admin, manager) — this is a bespoke, shop-scoped
 * system, not Spatie's Teams feature.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->string('label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_permissions');
    }
};
