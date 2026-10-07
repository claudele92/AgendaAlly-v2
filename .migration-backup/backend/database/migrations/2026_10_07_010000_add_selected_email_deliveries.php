<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('selected_email_deliveries', function(Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('event_key',64)->unique();
            $t->unsignedBigInteger('user_id');
            $t->string('kind',20);
            $t->text('encrypted_payload');
            $t->string('state',20)->default('PENDING')->index();
            $t->string('error_code',80)->nullable();
            $t->timestamp('expires_at');
            $t->timestamp('claimed_at')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        if (Schema::hasTable('selected_email_deliveries')
            && \Illuminate\Support\Facades\DB::table('selected_email_deliveries')->exists()) {
            throw new RuntimeException('Retained selected email evidence requires an approved recovery plan, not routine schema rollback.');
        }
        Schema::dropIfExists('selected_email_deliveries');
    }
};