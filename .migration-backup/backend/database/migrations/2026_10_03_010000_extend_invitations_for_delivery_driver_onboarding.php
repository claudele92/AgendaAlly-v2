<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table): void {
            // A pending contact invitation has no AgendaAlly identity yet.
            // Existing staff and accepted invitation rows remain unchanged.
            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->string('driver_invite_email')->nullable();
            $table->string('driver_invite_email_normalized')->nullable()->index();
            $table->string('driver_invite_token_hash', 64)->nullable()->index();
            $table->timestamp('driver_invite_expires_at')->nullable();
            $table->timestamp('driver_contact_verified_at')->nullable();
            $table->string('driver_notification_status', 32)->nullable();
            $table->string('driver_invite_firstname', 100)->nullable();
            $table->string('driver_invite_lastname', 100)->nullable();
        });
    }

    public function down(): void
    {
        // Never delete pending contacts to make rollback possible. Fail
        // closed if any such rows still exist; callers can explicitly
        // reconcile them before rolling back this schema extension.
        if (DB::table('invitations')->whereNull('user_id')->exists()) {
            throw new RuntimeException(
                'Cannot roll back delivery-driver invitations while contact invitations have null user_id.'
            );
        }

        Schema::table('invitations', function (Blueprint $table): void {
            $table->dropIndex(['driver_invite_email_normalized']);
            $table->dropIndex(['driver_invite_token_hash']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn([
                'driver_invite_email',
                'driver_invite_email_normalized',
                'driver_invite_token_hash',
                'driver_invite_expires_at',
                'driver_contact_verified_at',
                'driver_notification_status',
                'driver_invite_firstname',
                'driver_invite_lastname',
            ]);
        });
    }
};