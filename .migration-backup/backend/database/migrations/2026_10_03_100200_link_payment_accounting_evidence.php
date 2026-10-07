<?php
declare(strict_types=1);

use App\Services\PaymentAccounting\AccountingSchema as Accounting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $t): void {
            $t->foreignId('allocation_id')->nullable()->constrained('commerce_payment_allocations')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('collection_context_id')->nullable()->constrained('payment_collection_contexts')->restrictOnDelete()->restrictOnUpdate();
            $t->index('allocation_id'); $t->index('collection_context_id');
        });
        Schema::table('platform_fee_ledger_entries', function (Blueprint $t): void {
            $t->foreignId('allocation_id')->nullable()->constrained('commerce_payment_allocations')->restrictOnDelete()->restrictOnUpdate();
            $t->foreignId('collection_context_id')->nullable()->constrained('payment_collection_contexts')->restrictOnDelete()->restrictOnUpdate();
            $t->string('effect_key', 191)->nullable(); $t->char('event_group_key', 36)->nullable();
            $t->string('effect_kind', 32)->nullable(); $t->bigInteger('exact_amount')->nullable(); $t->json('effect_data')->nullable();
            $t->unique(['allocation_id', 'effect_key'], 'pfle_effect_unique');
            $t->index(['allocation_id', 'effect_kind', 'id'], 'pfle_allocation_idx');
            $t->index(['collection_context_id', 'effect_kind', 'id'], 'pfle_context_idx');
            $t->index(['event_group_key', 'id'], 'pfle_group_idx');
        });
        // Keep the existing transaction/type unique index throughout FK replacement.
        Schema::table('platform_fee_ledger_entries', function (Blueprint $t): void {
            $t->dropForeign(['transaction_id']); $t->dropForeign(['shop_id']);
            $t->unsignedBigInteger('transaction_id')->nullable()->change();
        });
        Schema::table('platform_fee_ledger_entries', function (Blueprint $t): void {
            $t->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete()->restrictOnUpdate();
            $t->foreign('shop_id')->references('id')->on('shops')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        Accounting::refuseDestructiveRollback();
        if (DB::table('platform_fee_ledger_entries')->whereNull('transaction_id')->exists()) {
            throw new RuntimeException('Cannot restore NOT NULL without fabricating transaction provenance.');
        }
        Schema::table('platform_fee_ledger_entries', function (Blueprint $t): void {
            $t->dropForeign(['transaction_id']); $t->dropForeign(['shop_id']);
            $t->dropForeign(['allocation_id']); $t->dropForeign(['collection_context_id']);
            $t->dropUnique('pfle_effect_unique');
            $t->dropIndex('pfle_allocation_idx'); $t->dropIndex('pfle_context_idx'); $t->dropIndex('pfle_group_idx');
            $t->unsignedBigInteger('transaction_id')->nullable(false)->change();
            $t->dropColumn(['allocation_id','collection_context_id','effect_key','event_group_key','effect_kind','exact_amount','effect_data']);
        });
        Schema::table('platform_fee_ledger_entries', function (Blueprint $t): void {
            $t->foreign('transaction_id')->references('id')->on('transactions')->cascadeOnDelete()->cascadeOnUpdate();
            $t->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete()->cascadeOnUpdate();
        });
        Schema::table('transactions', function (Blueprint $t): void {
            $t->dropForeign(['allocation_id']); $t->dropForeign(['collection_context_id']);
            $t->dropIndex(['allocation_id']); $t->dropIndex(['collection_context_id']);
            $t->dropColumn(['allocation_id','collection_context_id']);
        });
    }
};