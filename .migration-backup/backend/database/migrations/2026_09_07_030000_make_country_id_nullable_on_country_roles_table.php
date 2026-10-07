<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allows a country_roles row to represent a platform-wide role (e.g. "Main
 * Accountant") by leaving country_id null, rather than forcing every role
 * in this system to be scoped to a single country. The unique(['country_id',
 * 'name']) constraint from the original migration is unaffected — MySQL/
 * MariaDB treat NULL as distinct across rows in a unique index, so this
 * doesn't relax per-country name uniqueness for the country-scoped roles.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('country_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('country_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->nullable(false)->change();
        });
    }
};
