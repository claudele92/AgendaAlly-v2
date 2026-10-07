<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A seller's own named roles for their shop's administrative staff (e.g.
 * "Front Desk", "Accountant") — replaces the fixed platform-wide
 * `shop_manager` role for this staff category. Scoped by shop_id on the
 * table itself, so isolation between shops (and from the admin-side
 * `admin`/`manager` roles) is structural, not enforced via Spatie Teams.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name');
            $table->timestamps();

            $table->unique(['shop_id', 'name']);
        });

        Schema::create('shop_role_permissions', function (Blueprint $table) {
            $table->foreignId('shop_role_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('shop_permission_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->primary(['shop_role_id', 'shop_permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_role_permissions');
        Schema::dropIfExists('shop_roles');
    }
};
