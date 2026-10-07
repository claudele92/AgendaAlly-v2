<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A country admin's own named roles for their country's staff (e.g.
 * "Local Support", "Payments Reviewer"). Isolation between countries is
 * structural (the country_id column), same as shop_roles.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name');
            $table->timestamps();

            $table->unique(['country_id', 'name']);
        });

        Schema::create('country_role_permissions', function (Blueprint $table) {
            $table->foreignId('country_role_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('country_permission_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->primary(['country_role_id', 'country_permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_role_permissions');
        Schema::dropIfExists('country_roles');
    }
};
