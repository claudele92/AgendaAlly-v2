<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restricts a user (already holding the global 'admin' or 'manager' Spatie
 * role) to exactly one country. Unique on user_id guarantees a user can
 * never be assigned to more than one country. No row here at all = an
 * unrestricted global superadmin — this table is purely additive: existing
 * admin/manager holders are untouched by its introduction and remain
 * superadmins by default (see the accompanying data-migration note in the
 * PR description — there is no data migration, that's the point).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_admins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_admins');
    }
};
