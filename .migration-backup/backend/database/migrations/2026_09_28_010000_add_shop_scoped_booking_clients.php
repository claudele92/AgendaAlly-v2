<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add first-class, shop-owned identities for walk-in/manual bookings.
 *
 * Existing registered-account bookings keep their user_id unchanged. A
 * booking may instead reference a local client, without creating an account.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_booking_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shop_location_id')->nullable()
                ->constrained('shop_locations')->cascadeOnUpdate()->nullOnDelete();
            $table->string('name', 191);
            $table->string('phone', 64)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('normalized_phone', 64)->nullable();
            $table->string('normalized_email', 191)->nullable();
            $table->string('dedupe_scope', 32)->default('shop');
            $table->timestamps();

            $table->index(['shop_id', 'shop_location_id']);
            $table->index(['shop_id', 'normalized_phone']);
            $table->index(['shop_id', 'normalized_email']);
            $table->unique(['shop_id', 'dedupe_scope', 'normalized_phone'], 'seller_booking_clients_phone_dedupe');
            $table->unique(['shop_id', 'dedupe_scope', 'normalized_email'], 'seller_booking_clients_email_dedupe');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('local_client_id')->nullable()
                ->after('user_id')
                ->constrained('seller_booking_clients')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('local_client_id');
        });
    }

    /**
     * This schema addition is deliberately forward-only. The development
     * migration guard must not infer that rollback is safe for accepted data.
     */
    public function down(): void
    {
        throw new LogicException('Shop-scoped walk-in booking clients are a forward-only development migration.');
    }
};