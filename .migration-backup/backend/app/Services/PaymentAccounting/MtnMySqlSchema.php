<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Equivalent MySQL representation of the approved MTN identity, not a new identity. */
final class MtnMySqlSchema
{
    private const TRIGGERS = ['mtn_attempt_coherent_insert','mtn_attempt_coherent_update',
        'mtn_attempt_identity_immutable','mtn_attempt_lifecycle','mtn_attempt_retain'];

    public function up(): void
    {
        $this->outsideTransaction();
        if (DB::table('payment_process')->select('id')->groupBy('id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new \RuntimeException('LEGACY PAYMENT_PROCESS ID COLLISION REQUIRES DECISION');
        }
        if (Schema::hasColumn('payment_process','mtn_funding_event_key')) {
            throw new \RuntimeException('MTN attempt schema already present; inspect migration metadata.');
        }
        // One atomic ALTER for fields/indexes/FK. MySQL DDL implicitly commits;
        // do not pretend trigger installation is a transactional migration.
        DB::statement("ALTER TABLE payment_process
            ADD COLUMN mtn_funding_event_key CHAR(36) NULL,
            ADD COLUMN mtn_anchor_context_id BIGINT UNSIGNED NULL,
            ADD COLUMN mtn_dispatch_state VARCHAR(32) NULL,
            ADD COLUMN mtn_attempt_version BIGINT NULL,
            ADD COLUMN mtn_config_fingerprint CHAR(64) NULL,
            ADD COLUMN mtn_dispatch_claimed_at TEXT NULL,
            ADD UNIQUE INDEX payment_process_identity_unique (id),
            ADD UNIQUE INDEX mtn_attempt_event_unique (mtn_funding_event_key),
            ADD INDEX mtn_attempt_anchor_index (mtn_anchor_context_id),
            ADD CONSTRAINT mtn_attempt_anchor_fk FOREIGN KEY (mtn_anchor_context_id)
                REFERENCES payment_collection_contexts(id) ON UPDATE RESTRICT ON DELETE RESTRICT");
        $valid = "(NEW.mtn_funding_event_key IS NULL AND NEW.mtn_anchor_context_id IS NULL
            AND NEW.mtn_dispatch_state IS NULL AND NEW.mtn_attempt_version IS NULL
            AND NEW.mtn_config_fingerprint IS NULL AND NEW.mtn_dispatch_claimed_at IS NULL)
            OR (CHAR_LENGTH(NEW.id)=36 AND CHAR_LENGTH(NEW.mtn_funding_event_key)=36
            AND CHAR_LENGTH(NEW.mtn_config_fingerprint)=64
            AND NEW.mtn_attempt_version IS NOT NULL AND NEW.mtn_attempt_version>=0
            AND BINARY NEW.mtn_dispatch_state IN ('RESERVED_NOT_DISPATCHED','DISPATCH_OUTCOME_UNKNOWN',
                'ACCEPTED_PENDING','PROVIDER_PENDING','VERIFIED_SUCCESS','VERIFIED_FAILURE','REVIEW_REQUIRED')
            AND ((BINARY NEW.mtn_dispatch_state='RESERVED_NOT_DISPATCHED' AND NEW.mtn_dispatch_claimed_at IS NULL)
                OR (BINARY NEW.mtn_dispatch_state!='RESERVED_NOT_DISPATCHED' AND NEW.mtn_dispatch_claimed_at IS NOT NULL))
            AND EXISTS(SELECT 1 FROM payment_collection_contexts c WHERE c.id=NEW.mtn_anchor_context_id
                AND BINARY c.funding_event_key=BINARY NEW.mtn_funding_event_key AND BINARY c.provider_tag='mtn'))";
        foreach (['INSERT','UPDATE'] as $event) {
            $this->trigger('mtn_attempt_coherent_'.strtolower($event),$event,
                "NOT COALESCE(({$valid}),0)",'Invalid MTN attempt binding');
        }
        $different = fn(string $column): string => "NOT (BINARY NEW.{$column} <=> BINARY OLD.{$column})";
        $identity = implode(' OR ',array_map($different,
            ['id','mtn_funding_event_key','mtn_anchor_context_id','mtn_config_fingerprint','model_type','model_id','user_id']));
        $this->trigger('mtn_attempt_identity_immutable','UPDATE',
            "(OLD.mtn_funding_event_key IS NOT NULL AND ({$identity}))
            OR (OLD.mtn_funding_event_key IS NULL AND NEW.mtn_funding_event_key IS NOT NULL)",
            'Immutable MTN attempt identity; no legacy backfill');
        $this->trigger('mtn_attempt_lifecycle','UPDATE',
            "OLD.mtn_funding_event_key IS NOT NULL AND (
            ({$different('mtn_dispatch_claimed_at')} AND BINARY OLD.mtn_dispatch_state!='RESERVED_NOT_DISPATCHED')
            OR NEW.mtn_attempt_version<OLD.mtn_attempt_version
            OR ({$different('mtn_dispatch_state')} AND (
                NEW.mtn_attempt_version!=OLD.mtn_attempt_version+1
                OR BINARY OLD.mtn_dispatch_state IN ('VERIFIED_SUCCESS','VERIFIED_FAILURE')
                OR (BINARY OLD.mtn_dispatch_state='RESERVED_NOT_DISPATCHED'
                    AND BINARY NEW.mtn_dispatch_state!='DISPATCH_OUTCOME_UNKNOWN')
                OR BINARY NEW.mtn_dispatch_state='RESERVED_NOT_DISPATCHED')))",
            'Invalid MTN attempt lifecycle');
        $this->trigger('mtn_attempt_retain','DELETE','OLD.mtn_funding_event_key IS NOT NULL',
            'Retain MTN recovery evidence');
    }

    private function trigger(string $name,string $event,string $condition,string $message): void
    {
        DB::unprepared("CREATE TRIGGER {$name} BEFORE {$event} ON payment_process FOR EACH ROW BEGIN
            IF {$condition} THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='{$message}'; END IF; END");
    }

    public function down(): void
    {
        $this->outsideTransaction();
        if (DB::table('payment_process')->whereNotNull('mtn_funding_event_key')->exists()) {
            throw new \RuntimeException('MTN evidence exists; destructive rollback refused.');
        }
        foreach (self::TRIGGERS as $name) DB::statement("DROP TRIGGER {$name}");
        DB::statement("ALTER TABLE payment_process DROP FOREIGN KEY mtn_attempt_anchor_fk,
            DROP INDEX mtn_attempt_anchor_index, DROP INDEX mtn_attempt_event_unique,
            DROP INDEX payment_process_identity_unique,
            DROP COLUMN mtn_dispatch_claimed_at, DROP COLUMN mtn_config_fingerprint,
            DROP COLUMN mtn_attempt_version, DROP COLUMN mtn_dispatch_state,
            DROP COLUMN mtn_anchor_context_id, DROP COLUMN mtn_funding_event_key");
    }

    private function outsideTransaction(): void
    {
        if (DB::connection()->transactionLevel()!==0 || DB::connection()->getPdo()->inTransaction()) {
            throw new \RuntimeException('MySQL MTN DDL requires an owned migration boundary outside transactions.');
        }
    }
}