<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultIndexInLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->index('default');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->index(['id', 'status'], 'shops_id_status_index');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index(['product_id', 'deleted_at', 'quantity'], 'stocks_pid_del_qty_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['deleted_at', 'status', 'active', 'shop_id'], 'products_del_stat_act_shop_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropIndex('languages_default_index');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('shops_id_status_index');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('stocks_pid_del_qty_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_del_stat_act_shop_index');
        });
    }
}
