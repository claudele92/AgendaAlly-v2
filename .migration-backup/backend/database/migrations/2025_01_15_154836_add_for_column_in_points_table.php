<?php

use App\Models\Point;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForColumnInPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            $table->string('for')->default(Point::FOR_ORDER)->index();
        });

        Schema::table('point_histories', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
            $table->nullableMorphs('model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            $table->dropIndex('points_for_index');
            $table->dropColumn('for');
        });
    }
}
