<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_pickup_policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_location_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('timing_mode')->default('as_soon_as_ready');
            $table->string('timezone')->nullable();
            $table->json('weekly_hours')->nullable();
            $table->unsignedInteger('preparation_minutes')->default(0);
            $table->unsignedInteger('window_minutes')->default(120);
            $table->boolean('same_day')->default(true);
            $table->string('cutoff', 5)->nullable();
            $table->json('blackout_dates')->nullable();
            $table->timestamps();
        });
        Schema::table('orders', fn (Blueprint $table) => $table->json('pickup')->nullable());
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('pickup'));
        Schema::dropIfExists('shop_pickup_policies');
    }
};