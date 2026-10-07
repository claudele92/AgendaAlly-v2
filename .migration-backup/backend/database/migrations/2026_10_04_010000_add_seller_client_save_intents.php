<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_client_save_intents', function (Blueprint $table) {
            $table->id();
            // No cascading deletion of replay evidence when a directory result disappears.
            $table->unsignedBigInteger('actor_id');
            $table->unsignedBigInteger('shop_id');
            $table->string('intent_key', 36);
            $table->unsignedBigInteger('shop_location_id')->nullable();
            $table->char('payload_hash', 64);
            $table->string('result_kind', 16)->nullable();
            $table->unsignedBigInteger('result_id')->nullable();
            $table->boolean('reused_existing')->default(false);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->timestamps();
            $table->unique(['actor_id', 'shop_id', 'intent_key'], 'seller_client_save_intent_authority');
        });
    }

    public function down(): void
    {
        throw new LogicException('Client-save replay evidence is forward-only; do not discard committed identities.');
    }
};