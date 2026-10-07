<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks whether assigning this country_admins row also granted the
 * user the 'manager' Spatie role (because they didn't already hold
 * 'admin'/'manager' on their own). Lets CountryAdminService::delete()
 * revoke exactly that grant on removal — never touching a role the user
 * already held independently of this assignment — so removing a country
 * admin can't accidentally leave them an unrestricted global superadmin.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('country_admins', function (Blueprint $table) {
            $table->boolean('manager_role_granted')->default(false)->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('country_admins', function (Blueprint $table) {
            $table->dropColumn('manager_role_granted');
        });
    }
};
