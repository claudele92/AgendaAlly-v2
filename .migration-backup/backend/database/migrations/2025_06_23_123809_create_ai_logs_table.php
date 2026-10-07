<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAiLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('model');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('locale', 10);
            $table->json('content')->nullable();
            $table->integer('total_token')->default(0);
            $table->smallInteger('status')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'total_token']);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('ai_access')->default(true);
            $table->integer('ai_token_limit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('ai_access');
            $table->dropColumn('ai_token_limit');
        });
    }
}
