<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table): void {
            // Null retains legacy delivery eligibility; no existing
            // shop, historical order or payment preference is rewritten.
            $table->json('product_fulfillment_methods')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shops', fn (Blueprint $table) => $table->dropColumn('product_fulfillment_methods'));
    }
};