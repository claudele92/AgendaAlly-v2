<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The platform's own Orange Money/MTN Mobile Money merchant config for
 * platform-fee purchases (ShopSubscription, ShopAdsPackage) — as opposed
 * to shop_payments, which holds each shop's own credentials for
 * customer-facing checkout. Same rule applies at this level: the
 * platform needs its own merchant registration per country (different
 * currencies/target_environments), so this is keyed by country, not a
 * single global credential set. Superadmin-managed only — see
 * PlatformPaymentConfigController; deliberately not exposed to sellers
 * or country admins regardless of the country-admin hierarchy, since
 * this is platform revenue, not shop revenue.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_payment_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('payment_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->boolean('status')->default(true);
            $table->string('client_id', 191)->nullable();
            $table->text('merchant_key')->nullable();
            $table->text('subscription_key')->nullable();
            $table->text('api_user')->nullable();
            $table->text('api_key')->nullable();
            $table->string('target_environment')->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('base_url')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['country_id', 'payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_payment_configs');
    }
};
