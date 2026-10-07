<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\DB;

/** Equivalent database guards on both supported native database engines. */
final class AccountingSchema
{
    private const RECEIPT_SELF_ANCHOR_CHECK =
        'CHECK(receipt_anchor_context_id IS NULL OR receipt_anchor_context_id <> id)';

    public static function create(string $table, array $columns, array $constraints, array $indexes): void
    {
        $driver = DB::connection()->getDriverName();
        if (!in_array($driver, ['sqlite', 'mysql'], true)) {
            throw new \RuntimeException('Accounting migration requires reviewed SQLite/MySQL DDL.');
        }
        $id = $driver === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        // Existing native IDs are unsigned on MySQL; money/version remain signed.
        $columns = array_map(fn ($definition) => str_replace(
            ['@pk', '@ref'], [$id, $driver === 'mysql' ? 'BIGINT UNSIGNED' : 'BIGINT'], $definition
        ), $columns);
        $mysqlReceiptGuard = $driver === 'mysql' && $table === 'payment_collection_contexts';
        if ($mysqlReceiptGuard) {
            // MySQL forbids AUTO_INCREMENT columns in CHECK expressions. Replace
            // only this exact approved guard; every other constraint is retained.
            $guard = array_search(self::RECEIPT_SELF_ANCHOR_CHECK, $constraints, true);
            if ($guard === false) {
                throw new \RuntimeException('Expected receipt self-anchor invariant is missing.');
            }
            unset($constraints[$guard]);
        }
        DB::statement('CREATE TABLE '.$table.' ('.implode(",\n", array_merge($columns, $constraints)).')');
        if ($mysqlReceiptGuard) self::installMySqlReceiptAnchorGuard();
        foreach ($indexes as $name => $fields) {
            DB::statement("CREATE INDEX {$name} ON {$table} ({$fields})");
        }
    }

    /**
     * Fresh bootstrap, or an explicitly approved existing-table upgrade.
     * Never rewrites/classifies existing rows; invalid preexisting data fails closed.
     */
    public static function installMySqlReceiptAnchorGuard(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new \RuntimeException('MySQL receipt guard requires a MySQL connection.');
        }
        if (DB::table('payment_collection_contexts')->whereNotNull('receipt_anchor_context_id')
            ->whereColumn('receipt_anchor_context_id', 'id')->exists()) {
            throw new \RuntimeException('Existing receipt self-anchor prevents guard installation.');
        }
        // AFTER INSERT observes the assigned AUTO_INCREMENT id, including explicit
        // ids. SIGNAL rolls back the offending statement; FK rules remain intact.
        foreach (['insert' => 'AFTER INSERT', 'update' => 'BEFORE UPDATE'] as $suffix => $event) {
            DB::unprepared("CREATE TRIGGER pcc_receipt_anchor_{$suffix} {$event}
                ON payment_collection_contexts FOR EACH ROW BEGIN
                IF NEW.receipt_anchor_context_id IS NOT NULL AND NEW.receipt_anchor_context_id = NEW.id
                THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Receipt context cannot anchor itself';
                END IF; END");
        }
    }

    public static function restrict(string $column, string $table): string
    {
        return "FOREIGN KEY ({$column}) REFERENCES {$table}(id) ON DELETE RESTRICT ON UPDATE RESTRICT";
    }

    public static function refuseDestructiveRollback(): void
    {
        foreach (['commerce_payment_allocations', 'payment_collection_contexts'] as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table) && DB::table($table)->exists()) {
                throw new \RuntimeException('Accounting evidence exists: destructive rollback refused.');
            }
        }
    }
}