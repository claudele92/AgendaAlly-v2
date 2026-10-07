<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Approved existing-table extension; equivalent SQLite/MySQL identity guards.
 * no historical classification/backfill, no provider operations.
 */
return new class extends Migration {
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            (new \App\Services\PaymentAccounting\MtnMySqlSchema)->up();
            return;
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            throw new \RuntimeException('MTN attempt migration requires separately verified engine DDL.');
        }
        DB::transaction(function (): void {
            if (DB::table('payment_process')->select('id')->groupBy('id')
                ->havingRaw('COUNT(*) > 1')->exists()) {
                throw new \RuntimeException('LEGACY PAYMENT_PROCESS ID COLLISION REQUIRES DECISION');
            }
            if (Schema::hasColumn('payment_process', 'mtn_funding_event_key')) {
                throw new \RuntimeException('MTN attempt schema already present; inspect migration metadata.');
            }
            DB::statement('CREATE UNIQUE INDEX payment_process_identity_unique ON payment_process(id)');
            foreach ([
                'mtn_funding_event_key CHAR(36)',
                'mtn_anchor_context_id INTEGER REFERENCES payment_collection_contexts(id) ON UPDATE RESTRICT ON DELETE RESTRICT',
                'mtn_dispatch_state VARCHAR(32)',
                'mtn_attempt_version INTEGER',
                'mtn_config_fingerprint CHAR(64)',
                'mtn_dispatch_claimed_at TEXT',
            ] as $column) {
                DB::statement("ALTER TABLE payment_process ADD COLUMN {$column} NULL");
            }
            DB::statement('CREATE UNIQUE INDEX mtn_attempt_event_unique ON payment_process(mtn_funding_event_key)');
            DB::statement('CREATE INDEX mtn_attempt_anchor_index ON payment_process(mtn_anchor_context_id)');
            // NULL metadata leaves legacy/non-MTN rows unchanged. A new binding
            // is coherent, typed, server-owned and anchored to canonical MTN.
            $valid = "(NEW.mtn_funding_event_key IS NULL AND NEW.mtn_anchor_context_id IS NULL
                AND NEW.mtn_dispatch_state IS NULL AND NEW.mtn_attempt_version IS NULL
                AND NEW.mtn_config_fingerprint IS NULL AND NEW.mtn_dispatch_claimed_at IS NULL)
                OR (length(NEW.id)=36 AND length(NEW.mtn_funding_event_key)=36
                AND length(NEW.mtn_config_fingerprint)=64
                AND typeof(NEW.mtn_attempt_version)='integer' AND NEW.mtn_attempt_version>=0
                AND NEW.mtn_dispatch_state IN ('RESERVED_NOT_DISPATCHED','DISPATCH_OUTCOME_UNKNOWN',
                    'ACCEPTED_PENDING','PROVIDER_PENDING','VERIFIED_SUCCESS','VERIFIED_FAILURE','REVIEW_REQUIRED')
                AND ((NEW.mtn_dispatch_state='RESERVED_NOT_DISPATCHED' AND NEW.mtn_dispatch_claimed_at IS NULL)
                    OR (NEW.mtn_dispatch_state!='RESERVED_NOT_DISPATCHED' AND NEW.mtn_dispatch_claimed_at IS NOT NULL))
                AND EXISTS(SELECT 1 FROM payment_collection_contexts c WHERE c.id=NEW.mtn_anchor_context_id
                    AND c.funding_event_key=NEW.mtn_funding_event_key AND c.provider_tag='mtn'))";
            foreach (['INSERT','UPDATE'] as $event) {
                $name = 'mtn_attempt_coherent_'.strtolower($event);
                DB::unprepared("CREATE TRIGGER {$name} BEFORE {$event} ON payment_process
                    WHEN NOT COALESCE(({$valid}),0)
                    BEGIN SELECT RAISE(ABORT,'Invalid MTN attempt binding'); END");
            }
            DB::unprepared("CREATE TRIGGER mtn_attempt_identity_immutable BEFORE UPDATE ON payment_process
                WHEN (OLD.mtn_funding_event_key IS NOT NULL AND (
                    NEW.id IS NOT OLD.id OR NEW.mtn_funding_event_key IS NOT OLD.mtn_funding_event_key
                    OR NEW.mtn_anchor_context_id IS NOT OLD.mtn_anchor_context_id
                    OR NEW.mtn_config_fingerprint IS NOT OLD.mtn_config_fingerprint
                    OR NEW.model_type IS NOT OLD.model_type OR NEW.model_id IS NOT OLD.model_id
                    OR NEW.user_id IS NOT OLD.user_id))
                OR (OLD.mtn_funding_event_key IS NULL AND NEW.mtn_funding_event_key IS NOT NULL)
                BEGIN SELECT RAISE(ABORT,'Immutable MTN attempt identity; no legacy backfill'); END");
            DB::unprepared("CREATE TRIGGER mtn_attempt_lifecycle BEFORE UPDATE ON payment_process
                WHEN OLD.mtn_funding_event_key IS NOT NULL AND (
                    NEW.mtn_dispatch_claimed_at IS NOT OLD.mtn_dispatch_claimed_at
                        AND OLD.mtn_dispatch_state!='RESERVED_NOT_DISPATCHED'
                    OR NEW.mtn_attempt_version<OLD.mtn_attempt_version
                    OR (NEW.mtn_dispatch_state IS NOT OLD.mtn_dispatch_state AND (
                        NEW.mtn_attempt_version!=OLD.mtn_attempt_version+1
                        OR OLD.mtn_dispatch_state IN ('VERIFIED_SUCCESS','VERIFIED_FAILURE')
                        OR (OLD.mtn_dispatch_state='RESERVED_NOT_DISPATCHED'
                            AND NEW.mtn_dispatch_state!='DISPATCH_OUTCOME_UNKNOWN')
                        OR NEW.mtn_dispatch_state='RESERVED_NOT_DISPATCHED')))
                BEGIN SELECT RAISE(ABORT,'Invalid MTN attempt lifecycle'); END");
            DB::unprepared("CREATE TRIGGER mtn_attempt_retain BEFORE DELETE ON payment_process
                WHEN OLD.mtn_funding_event_key IS NOT NULL
                BEGIN SELECT RAISE(ABORT,'Retain MTN recovery evidence'); END");
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            (new \App\Services\PaymentAccounting\MtnMySqlSchema)->down();
            return;
        }
        if (DB::table('payment_process')->whereNotNull('mtn_funding_event_key')->exists()) {
            throw new \RuntimeException('MTN evidence exists; destructive rollback refused.');
        }
        DB::transaction(function (): void {
            foreach (['mtn_attempt_coherent_insert','mtn_attempt_coherent_update',
                'mtn_attempt_identity_immutable','mtn_attempt_lifecycle','mtn_attempt_retain'] as $trigger) {
                DB::statement("DROP TRIGGER {$trigger}");
            }
            foreach (['mtn_attempt_anchor_index','mtn_attempt_event_unique','payment_process_identity_unique'] as $index) {
                DB::statement("DROP INDEX {$index}");
            }
            foreach (['mtn_dispatch_claimed_at','mtn_config_fingerprint','mtn_attempt_version',
                'mtn_dispatch_state','mtn_anchor_context_id','mtn_funding_event_key'] as $column) {
                DB::statement("ALTER TABLE payment_process DROP COLUMN {$column}");
            }
        });
    }
};