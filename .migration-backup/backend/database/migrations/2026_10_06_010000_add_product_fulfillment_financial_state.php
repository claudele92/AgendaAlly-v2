<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('fulfillment_financial_state', ['unverified', 'pending', 'settled'])
                ->default('unverified');
        });
    }

    public function down(): void
    {
        if (DB::table('orders')->where('fulfillment_financial_state', 'settled')->exists()) {
            throw new RuntimeException('Cannot remove committed fulfillment financial finality.');
        }
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('fulfillment_financial_state'));
    }
};