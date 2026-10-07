<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a second kind of row to the existing fee ledger rather than a new
 * table: 'payable' rows track what the platform owes a shop that opted
 * into collect_via_platform (it received the full charge on the shop's
 * behalf), and 'payable_adjustment' rows model a booking's one-time
 * cancellation/refund as a new signed row instead of mutating the
 * already-settled 'payable'/'fee' entries. The original
 * unique(transaction_id) - one 'fee' row per transaction - becomes
 * unique(transaction_id, entry_type) so a 'payable' row and, later, one
 * 'payable_adjustment' row can each coexist alongside the 'fee' row
 * already written for the same transaction.
 *
 * Fixed after a production failure (SQLSTATE 1553: "Cannot drop index
 * ...transaction_id_unique: needed in a foreign key constraint"): the
 * original version of this migration dropped that single-column unique
 * index BEFORE creating its composite replacement. That index is also
 * the sole index backing transaction_id's foreign key to transactions
 * (declared together via ->unique()->constrained() in the table's
 * create migration), and MySQL/InnoDB refuses to drop the only index
 * currently supporting a FK. Every step here is now guarded with an
 * existence check and reordered so the replacement index always exists
 * before the old one is dropped, making this safe to re-run against a
 * database left mid-way through the original, broken version (MySQL DDL
 * isn't transactional, so the ADD COLUMN below may already have applied
 * even though this migration was never marked as run).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('platform_fee_ledger_entries', 'entry_type')) {
            Schema::table('platform_fee_ledger_entries', function (Blueprint $table) {
                $table->string('entry_type', 24)->default('fee')->after('shop_id');
            });
        }

        // Create the composite unique index while the old single-column
        // one still exists, so the transaction_id -> transactions foreign
        // key always has a supporting index to fall back on - only once
        // this exists is it safe to drop the old one.
        if (!Schema::hasIndex('platform_fee_ledger_entries', ['transaction_id', 'entry_type'], 'unique')) {
            Schema::table('platform_fee_ledger_entries', function (Blueprint $table) {
                $table->unique(['transaction_id', 'entry_type']);
            });
        }

        if (Schema::hasIndex('platform_fee_ledger_entries', ['transaction_id'], 'unique')) {
            Schema::table('platform_fee_ledger_entries', function (Blueprint $table) {
                $table->dropUnique(['transaction_id']);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasIndex('platform_fee_ledger_entries', ['transaction_id'], 'unique')) {
            Schema::table('platform_fee_ledger_entries', function (Blueprint $table) {
                $table->unique('transaction_id');
            });
        }

        if (Schema::hasIndex('platform_fee_ledger_entries', ['transaction_id', 'entry_type'], 'unique')) {
            Schema::table('platform_fee_ledger_entries', function (Blueprint $table) {
                $table->dropUnique(['transaction_id', 'entry_type']);
            });
        }

        if (Schema::hasColumn('platform_fee_ledger_entries', 'entry_type')) {
            Schema::table('platform_fee_ledger_entries', function (Blueprint $table) {
                $table->dropColumn('entry_type');
            });
        }
    }
};
