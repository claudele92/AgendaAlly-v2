<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks money the platform is owed but did not itself receive at
 * payment time — currently just Booking::service_fee. Orange Money and
 * MTN Mobile Money settle the whole charge (service price + fee)
 * straight into the shop's own merchant account (see ShopPayment), and
 * PayPal is not yet wired for per-shop split payouts either, so the fee
 * portion has to be tracked here and collected separately (e.g. netted
 * against a seller's next Payout) rather than silently kept by the shop
 * or left unaccounted for. One row is created per paid booking
 * transaction — see TransactionObserver.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_fee_ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->morphs('payable');

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('transaction_id')
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->double('amount', 22, 2);
            $table->string('status', 16)->default('pending')->index();
            $table->timestamp('collected_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_fee_ledger_entries');
    }
};
