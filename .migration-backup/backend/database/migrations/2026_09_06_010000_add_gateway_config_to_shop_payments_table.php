<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-shop credentials/config for gateways whose merchant registration is
 * done by the receiving business itself — currently Orange Money and MTN
 * Mobile Money — so there is no platform-level credential to fall back
 * to. Credentials are encrypted at rest via Laravel's encrypted cast (see
 * ShopPayment); target_environment and currency are plain config values,
 * not secrets. Nullable throughout: every other gateway leaves these
 * columns unused, same as the existing client_id/secret_id columns.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_payments', function (Blueprint $table) {
            $table->text('merchant_key')->nullable()->after('secret_id');
            $table->text('subscription_key')->nullable()->after('merchant_key');
            $table->text('api_user')->nullable()->after('subscription_key');
            $table->text('api_key')->nullable()->after('api_user');
            $table->string('target_environment')->nullable()->after('api_key');
            $table->string('currency', 8)->nullable()->after('target_environment');
            $table->string('base_url')->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('shop_payments', function (Blueprint $table) {
            $table->dropColumn([
                'merchant_key',
                'subscription_key',
                'api_user',
                'api_key',
                'target_environment',
                'currency',
                'base_url',
            ]);
        });
    }
};
