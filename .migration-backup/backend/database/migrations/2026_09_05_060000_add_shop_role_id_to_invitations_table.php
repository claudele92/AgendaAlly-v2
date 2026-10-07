<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreignId('shop_role_id')
                ->nullable()
                ->after('role')
                ->constrained()
                ->cascadeOnUpdate()
                // A shop_role that's still assigned to a staff invitation
                // cannot be deleted — enforced in RoleController too, this
                // is the DB-level backstop for that same rule.
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['shop_role_id']);
            $table->dropColumn('shop_role_id');
        });
    }
};
