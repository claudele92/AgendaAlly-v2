<?php
declare(strict_types=1);
namespace Tests\Hardening;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MtnAttemptSchemaTest extends IsolatedTestCase
{
    private function oldSchema(bool $collision=false): void
    {
        DB::unprepared('CREATE TABLE users(id INTEGER PRIMARY KEY)');
        DB::unprepared('CREATE TABLE payment_collection_contexts(id INTEGER PRIMARY KEY,funding_event_key TEXT,provider_tag TEXT)');
        // Exact original migration shape, including nonunique process ID.
        DB::unprepared('CREATE TABLE payment_process(id VARCHAR NOT NULL,user_id INTEGER REFERENCES users(id),
            model_type VARCHAR NOT NULL,model_id INTEGER NOT NULL,data TEXT NOT NULL)');
        $row=['id'=>'legacy-synthetic-id','user_id'=>null,'model_type'=>'synthetic-legacy',
            'model_id'=>900001,'data'=>'{"unverified_legacy":true}'];
        DB::table('payment_process')->insert($row);
        if ($collision) DB::table('payment_process')->insert($row);
        DB::statement('PRAGMA foreign_keys=ON');
    }
    private function migration(): \Illuminate\Database\Migrations\Migration
    { return require dirname(__DIR__,2).'/database/migrations/2026_10_03_100300_add_mtn_attempt_identity.php'; }

    public function test_collision_stops_before_any_schema_change_or_cleanup(): void
    {
        $this->oldSchema(true);
        try { $this->migration()->up(); self::fail(); }
        catch (\RuntimeException $e) { self::assertSame('LEGACY PAYMENT_PROCESS ID COLLISION REQUIRES DECISION',$e->getMessage()); }
        self::assertSame(2,DB::table('payment_process')->count());
        self::assertFalse(Schema::hasColumn('payment_process','mtn_funding_event_key'));
        self::assertCount(0,DB::select('PRAGMA index_list(payment_process)'));
    }

    public function test_additive_migration_preserves_legacy_projection_null_metadata_and_foreign_keys(): void
    {
        $this->oldSchema(); $old=(array)DB::table('payment_process')->first();
        $m=$this->migration(); $m->up();
        $new=(array)DB::table('payment_process')->first();
        self::assertSame($old,array_intersect_key($new,$old));
        foreach (['mtn_funding_event_key','mtn_anchor_context_id','mtn_dispatch_state',
            'mtn_attempt_version','mtn_config_fingerprint','mtn_dispatch_claimed_at'] as $field) {
            self::assertArrayHasKey($field,$new); self::assertNull($new[$field]);
        }
        self::assertCount(2,array_filter(DB::select('PRAGMA index_list(payment_process)'),fn($i)=>(int)$i->unique===1));
        $fk=array_filter(DB::select('PRAGMA foreign_key_list(payment_process)'),fn($r)=>$r->from==='mtn_anchor_context_id');
        self::assertCount(1,$fk);
        self::assertCount(0,DB::select('PRAGMA foreign_key_check'));
        try { DB::table('payment_process')->insert($old); self::fail(); }
        catch (\Illuminate\Database\QueryException $e) { self::assertStringContainsString('UNIQUE',$e->getMessage()); }
        try { DB::table('payment_process')->where('id',$old['id'])->update(['mtn_funding_event_key'=>'customer-backfill']); self::fail(); }
        catch (\Illuminate\Database\QueryException $e) { self::assertNotSame('',$e->getMessage()); }
        self::assertSame($new,(array)DB::table('payment_process')->first());
        $m->down();
        self::assertSame($old,(array)DB::table('payment_process')->first());
        self::assertFalse(Schema::hasColumn('payment_process','mtn_dispatch_state'));
    }
}