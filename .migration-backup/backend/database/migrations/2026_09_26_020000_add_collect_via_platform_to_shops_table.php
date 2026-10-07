<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Opt-in toggle: when true, Orange/MTN checkout for this shop routes
 * through the platform's own gateway (PlatformPaymentConfig) instead of the
 * shop's own ShopPayment credentials - for when a buyer's country/currency
 * isn't one the shop's own mobile-money account can settle. Defaults false
 * so existing shops keep today's behavior unchanged. See
 * BaseService::resolveGatewayConfig() and TransactionObserver.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('collect_via_platform')->default(false)->after('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('collect_via_platform');
        });
    }
};
