<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\CompletionSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class PaymentCompletionIndexFixture extends PaymentCompletionFixture
{
    private function indexContract(): void
    {
        $mysql = DB::connection()->getDriverName() === 'mysql';
        $name = $mysql ? 'eca_provider_reference_unique'
            : 'electronic_collection_attempts_provider_provider_reference_unique';
        if ($mysql) {
            $rows = DB::select("SELECT COLUMN_NAME AS col, NON_UNIQUE AS non_unique
                FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE()
                AND TABLE_NAME='electronic_collection_attempts' AND INDEX_NAME=?
                ORDER BY SEQ_IN_INDEX", [$name]);
            self::assertSame(['provider','provider_reference'], array_column($rows, 'col'));
            self::assertSame([0,0], array_map(fn($r)=>(int)$r->non_unique, $rows));
            self::assertSame('YES', DB::selectOne("SELECT IS_NULLABLE AS nullable
                FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
                AND TABLE_NAME='electronic_collection_attempts' AND COLUMN_NAME='provider_reference'")->nullable);
        } else {
            $rows = DB::select("PRAGMA index_info('$name')");
            self::assertSame(['provider','provider_reference'], array_column($rows, 'name'));
            $index = array_values(array_filter(DB::select("PRAGMA index_list('electronic_collection_attempts')"),
                fn($r)=>$r->name === $name));
            self::assertCount(1, $index);
            self::assertSame(1, (int)$index[0]->unique);
        }
    }

    public function test_fresh_bootstrap_and_empty_original_migration_down_up(): void
    {
        $this->indexContract();
        foreach (CompletionSchema::TABLES as $table) self::assertSame(0, DB::table($table)->count());
        $migration = require __DIR__.'/../../database/migrations/2026_10_03_100400_add_payment_completion_identity.php';
        $migration->down();
        foreach (CompletionSchema::TABLES as $table) {
            self::assertFalse(\Illuminate\Support\Facades\Schema::hasTable($table));
        }
        $migration->up();
        $this->indexContract();
        foreach (CompletionSchema::TABLES as $table) self::assertSame(0, DB::table($table)->count());
    }

    public function test_same_unique_rejects_duplicates_and_accepts_distinct_and_null_references(): void
    {
        [$allocation, $context, $revision, $attempt] = $this->funded('platform', 'paystack', true, false);
        $row = (array)DB::table('electronic_collection_attempts')->find($attempt->id);
        $candidate = $this->candidate($row, $revision, 2);
        try {
            DB::table('electronic_collection_attempts')->insert($candidate);
            self::fail('Duplicate provider/reference must be rejected.');
        } catch (\Illuminate\Database\QueryException $e) {
            self::assertSame(DB::connection()->getDriverName() === 'mysql' ? 1062 : 19, (int)$e->errorInfo[1]);
            self::assertStringContainsString(DB::connection()->getDriverName() === 'mysql'
                ? 'eca_provider_reference_unique' : 'electronic_collection_attempts.provider', $e->getMessage());
        }
        self::assertSame(1, DB::table('electronic_collection_attempts')->count());
        $candidate['provider_reference'] = 'synthetic-distinct-reference';
        DB::table('electronic_collection_attempts')->insert($candidate);
        foreach ([3,4] as $origin) {
            $candidate = $this->candidate($row, $revision, $origin);
            $candidate['provider_reference'] = null;
            DB::table('electronic_collection_attempts')->insert($candidate);
        }
        self::assertSame(4, DB::table('electronic_collection_attempts')->count());
        self::assertSame(2, DB::table('electronic_collection_attempts')->whereNull('provider_reference')->count());
        self::assertSame(4, DB::table('electronic_collection_attempts')->distinct()->count('funding_event_key'));
        self::assertSame($row, (array)DB::table('electronic_collection_attempts')->find($attempt->id));
        $this->indexContract();
    }

    private function candidate(array $row, string $revision, int $origin): array
    {
        $id = $this->writer->commit(array_replace($this->quote($origin), ['checkout_key'=>(string)Str::uuid()]));
        $evidence = $this->evidence('platform', 10000);
        $evidence['provider_tag'] = 'paystack';
        $evidence['configuration_source'] = 'global_payload';
        $evidence['configuration_reference'] = '1';
        $evidence['configuration_revision'] = $revision;
        $context = $this->writer->stage($id, $evidence);
        return array_replace($row, ['id'=>(string)Str::uuid(), 'funding_event_key'=>$evidence['funding_event_key'],
            'anchor_context_id'=>$context, 'process_reference'=>(string)Str::uuid()]);
    }
}