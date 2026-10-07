<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mirrors the shop-side `invitations` table for country-scoped staff.
 * Can't reuse `invitations` directly — its shop_id FK is required, not
 * nullable, so overloading it for a country-scoped invite would mean
 * either a fake shop_id or a schema change to that existing table; a
 * separate table keeps the two invite systems (and their route/permission
 * checks) independent, same as country_roles is independent of shop_roles.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('country_role_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique(['country_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_invitations');
    }
};
