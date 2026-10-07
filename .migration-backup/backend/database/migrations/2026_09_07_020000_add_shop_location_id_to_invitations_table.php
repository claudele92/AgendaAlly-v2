<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreignId('shop_location_id')
                ->nullable()
                ->after('shop_role_id')
                ->constrained()
                ->cascadeOnUpdate()
                // Unlike shop_role_id (restrictOnDelete - a role in use can't
                // be deleted), deleting a branch/location should just
                // unassign staff from it, not block the deletion.
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['shop_location_id']);
            $table->dropColumn('shop_location_id');
        });
    }
};
