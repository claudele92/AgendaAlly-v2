<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Additive approved identities. No backfill, financial rewrite or MTN change. */
final class CompletionSchema
{
    public const TABLES = ['payment_merchant_revisions', 'electronic_collection_attempts',
        'payment_financial_operations', 'payment_receipt_evidence'];

    public function up(): void
    {
        if (DB::table('payment_payloads')->select('payment_id')->groupBy('payment_id')
            ->havingRaw('COUNT(*) > 1')->exists()) {
            throw new \RuntimeException('Duplicate global profiles require a separate legacy decision.');
        }
        Schema::create('payment_merchant_revisions', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $t->string('provider',32); $t->string('owner_type',16)->default('platform');
            $t->longText('encrypted_payload'); $t->timestamp('created_at');
            $t->index(['payment_id','created_at']);
        });
        Schema::table('payment_payloads', function (Blueprint $t): void {
            $t->unique('payment_id','global_profile_payment_unique');
            $t->foreignUuid('revision_id')->nullable()->constrained('payment_merchant_revisions')->restrictOnDelete();
        });
        Schema::create('electronic_collection_attempts', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->uuid('funding_event_key')->unique();
            $t->foreignId('anchor_context_id')->constrained('payment_collection_contexts')->restrictOnDelete();
            $t->foreignUuid('revision_id')->constrained('payment_merchant_revisions')->restrictOnDelete();
            $t->string('provider',32); $t->string('state',32)->default('PRE_DISPATCH');
            $t->string('provider_reference')->nullable(); $t->string('provider_payment_id')->nullable();
            $t->string('process_reference')->unique(); $t->unsignedInteger('version')->default(0);
            $t->timestamp('claimed_at')->nullable(); $t->timestamps();
            $t->unique(['provider','provider_reference'],
                DB::connection()->getDriverName() === 'mysql' ? 'eca_provider_reference_unique' : null);
            $t->index(['state','updated_at']);
        });
        Schema::create('payment_financial_operations', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->string('kind',20);
            $t->foreignId('allocation_id')->constrained('commerce_payment_allocations')->restrictOnDelete();
            $t->foreignId('context_id')->nullable()->constrained('payment_collection_contexts')->restrictOnDelete();
            $t->foreignUuid('revision_id')->nullable()->constrained('payment_merchant_revisions')->restrictOnDelete();
            $t->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $t->uuid('request_key'); $t->bigInteger('amount_units');
            $t->string('provider',32)->nullable(); $t->string('original_payment_id')->nullable();
            $t->string('state',32)->default('RESERVED'); $t->unsignedInteger('version')->default(0);
            $t->timestamp('claimed_at')->nullable(); $t->string('external_reference')->nullable();
            $t->timestamp('completed_at')->nullable(); $t->timestamps();
            $t->unique(['allocation_id','kind','request_key'],'financial_operation_request_unique');
            $t->unique(['provider','external_reference'],'financial_operation_external_unique');
            $t->index(['allocation_id','kind','state']);
        });
        Schema::create('payment_receipt_evidence', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('operation_id')->unique()->constrained('payment_financial_operations')->restrictOnDelete();
            $t->string('source',32); $t->string('receipt_reference')->unique();
            $t->char('document_sha256',64); $t->text('retained_evidence');
            $t->timestamp('received_at'); $t->timestamp('created_at');
        });
        if (DB::connection()->getDriverName() === 'sqlite') $this->sqliteGuards();
        elseif (DB::connection()->getDriverName() === 'mysql') $this->mysqlGuards();
    }

    private function sqliteGuards(): void
    {
        foreach (['payment_merchant_revisions','payment_receipt_evidence'] as $table) {
            foreach (['UPDATE','DELETE'] as $event) DB::unprepared(
                "CREATE TRIGGER {$table}_retain_".strtolower($event)." BEFORE {$event} ON {$table}
                BEGIN SELECT RAISE(ABORT,'Immutable retained payment evidence'); END");
        }
        foreach ([
            'payment_financial_operations' => ['id','kind','allocation_id','context_id','revision_id','actor_id',
                'request_key','amount_units','provider','original_payment_id'],
        ] as $table => $fields) {
            $change = implode(' OR ',array_map(fn ($f) => "NEW.$f IS NOT OLD.$f",$fields));
            DB::unprepared("CREATE TRIGGER {$table}_immutable BEFORE UPDATE ON {$table}
                WHEN {$change} OR (OLD.claimed_at IS NOT NULL AND NEW.claimed_at IS NOT OLD.claimed_at)
                OR (OLD.external_reference IS NOT NULL AND NEW.external_reference IS NOT OLD.external_reference)
                BEGIN SELECT RAISE(ABORT,'Immutable operation binding'); END");
            // Attempts use provider_reference rather than external_reference.
        }
        DB::unprepared("CREATE TRIGGER electronic_collection_attempts_immutable BEFORE UPDATE ON electronic_collection_attempts
            WHEN NEW.id IS NOT OLD.id OR NEW.funding_event_key IS NOT OLD.funding_event_key
            OR NEW.anchor_context_id IS NOT OLD.anchor_context_id OR NEW.revision_id IS NOT OLD.revision_id
            OR NEW.provider IS NOT OLD.provider
            OR NEW.process_reference IS NOT OLD.process_reference
            OR (OLD.claimed_at IS NOT NULL AND NEW.claimed_at IS NOT OLD.claimed_at)
            OR (OLD.provider_reference IS NOT NULL AND NEW.provider_reference IS NOT OLD.provider_reference)
            OR (OLD.provider_payment_id IS NOT NULL AND NEW.provider_payment_id IS NOT OLD.provider_payment_id)
            BEGIN SELECT RAISE(ABORT,'Immutable original electronic attempt'); END");
        foreach (['electronic_collection_attempts','payment_financial_operations'] as $table) {
            DB::unprepared("CREATE TRIGGER {$table}_retain BEFORE DELETE ON {$table}
                BEGIN SELECT RAISE(ABORT,'Retain payment recovery evidence'); END");
            DB::unprepared("CREATE TRIGGER {$table}_terminal BEFORE UPDATE ON {$table}
                WHEN OLD.state IN ('SUCCESS','FAILURE','CANCELED') AND (
                    NEW.state IS NOT OLD.state OR NEW.version IS NOT OLD.version)
                BEGIN SELECT RAISE(ABORT,'Terminal operation cannot be reopened'); END");
        }
        DB::unprepared("CREATE TRIGGER financial_operation_amount BEFORE INSERT ON payment_financial_operations
            WHEN typeof(NEW.amount_units)!='integer' OR NEW.amount_units<=0
                OR NEW.kind NOT IN ('refund','receivable','payout')
            BEGIN SELECT RAISE(ABORT,'Invalid financial operation'); END");
        DB::unprepared("CREATE TRIGGER generic_attempt_binding BEFORE INSERT ON electronic_collection_attempts
            WHEN NEW.provider='mtn' OR NOT EXISTS (
                SELECT 1 FROM payment_collection_contexts c JOIN payment_merchant_revisions r ON r.id=NEW.revision_id
                WHERE c.id=NEW.anchor_context_id AND c.funding_event_key=NEW.funding_event_key
                AND c.provider_tag=NEW.provider AND c.configuration_revision=NEW.revision_id
                AND c.collection_mode='platform' AND r.provider=NEW.provider AND r.payment_id=c.payment_id)
            BEGIN SELECT RAISE(ABORT,'Invalid original generic funding binding'); END");
        foreach (['electronic_collection_attempts'=>"'PRE_DISPATCH','UNKNOWN','PENDING','SUCCESS','FAILURE','CANCELED'",
            'payment_financial_operations'=>"'RESERVED','UNKNOWN','PENDING','SUCCESS','FAILURE','CANCELED'"] as $table=>$states) {
            foreach (['INSERT','UPDATE'] as $event) DB::unprepared("CREATE TRIGGER {$table}_state_".strtolower($event).
                " BEFORE {$event} ON {$table} WHEN NEW.state NOT IN ({$states})
                BEGIN SELECT RAISE(ABORT,'Invalid operation lifecycle state'); END");
        }
        DB::unprepared("CREATE TRIGGER profile_revision_binding BEFORE UPDATE OF revision_id ON payment_payloads
            WHEN NEW.revision_id IS NOT NULL AND NOT EXISTS (
                SELECT 1 FROM payment_merchant_revisions r WHERE r.id=NEW.revision_id AND r.payment_id=NEW.payment_id)
            BEGIN SELECT RAISE(ABORT,'Revision belongs to another merchant profile'); END");
    }

    /** Prepared MySQL 8.0.16+ retention. Requires disposable-engine execution before certification. */
    private function mysqlGuards(): void
    {
        foreach (['payment_merchant_revisions','payment_receipt_evidence'] as $table) {
            foreach (['UPDATE','DELETE'] as $event) DB::unprepared("CREATE TRIGGER {$table}_retain_".strtolower($event).
                " BEFORE {$event} ON {$table} FOR EACH ROW SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT='Immutable retained payment evidence'");
        }
        foreach ([
            'electronic_collection_attempts'=>['id','funding_event_key','anchor_context_id','revision_id','provider','process_reference'],
            'payment_financial_operations'=>['id','kind','allocation_id','context_id','revision_id','actor_id','request_key',
                'amount_units','provider','original_payment_id'],
        ] as $table=>$fields) {
            $changes=implode(' OR ',array_map(fn($f)=>"NOT (NEW.$f <=> OLD.$f)",$fields));
            $reference=$table==='electronic_collection_attempts'?'provider_reference':'external_reference';
            DB::unprepared("CREATE TRIGGER {$table}_immutable BEFORE UPDATE ON {$table} FOR EACH ROW BEGIN
                IF {$changes} OR (OLD.claimed_at IS NOT NULL AND NOT (NEW.claimed_at <=> OLD.claimed_at))
                OR (OLD.{$reference} IS NOT NULL AND NOT (NEW.{$reference} <=> OLD.{$reference}))
                OR (OLD.state IN ('SUCCESS','FAILURE','CANCELED') AND (NEW.state<>OLD.state OR NEW.version<>OLD.version))
                THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Immutable original payment operation'; END IF; END");
            DB::unprepared("CREATE TRIGGER {$table}_retain BEFORE DELETE ON {$table} FOR EACH ROW
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Retain payment recovery evidence'");
        }
        DB::unprepared("CREATE TRIGGER generic_payment_identity_immutable BEFORE UPDATE ON electronic_collection_attempts
            FOR EACH ROW BEGIN IF OLD.provider_payment_id IS NOT NULL AND NOT (NEW.provider_payment_id <=> OLD.provider_payment_id)
            THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Immutable original provider payment'; END IF; END");
        DB::statement("ALTER TABLE payment_financial_operations ADD CONSTRAINT payment_operation_valid CHECK (
            amount_units>0 AND kind IN ('refund','receivable','payout')
            AND state IN ('RESERVED','UNKNOWN','PENDING','SUCCESS','FAILURE','CANCELED'))");
        DB::statement("ALTER TABLE electronic_collection_attempts ADD CONSTRAINT generic_attempt_valid CHECK (
            provider<>'mtn' AND state IN ('PRE_DISPATCH','UNKNOWN','PENDING','SUCCESS','FAILURE','CANCELED'))");
        DB::unprepared("CREATE TRIGGER generic_attempt_binding BEFORE INSERT ON electronic_collection_attempts FOR EACH ROW BEGIN
            IF NOT EXISTS (SELECT 1 FROM payment_collection_contexts c JOIN payment_merchant_revisions r ON r.id=NEW.revision_id
                WHERE c.id=NEW.anchor_context_id AND c.funding_event_key=NEW.funding_event_key
                AND c.provider_tag=NEW.provider AND c.configuration_revision=NEW.revision_id
                AND c.collection_mode='platform' AND r.provider=NEW.provider AND r.payment_id=c.payment_id)
            THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid original generic funding binding'; END IF; END");
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) if (DB::table($table)->exists()) {
            throw new \RuntimeException('Retained payment evidence exists; destructive rollback refused.');
        }
        Schema::drop('payment_receipt_evidence'); Schema::drop('payment_financial_operations');
        Schema::drop('electronic_collection_attempts');
        if (DB::connection()->getDriverName() === 'mysql') {
            // InnoDB may replace the legacy FK's implicit payment_id index with
            // our unique profile index. Restore FK support before removing it.
            $indexes = Schema::getIndexes('payment_payloads');
            $support = array_filter($indexes, fn($i) =>
                $i['name'] !== 'global_profile_payment_unique' && ($i['columns'][0] ?? null) === 'payment_id');
            foreach (Schema::getForeignKeys('payment_payloads') as $foreign) {
                if ($foreign['columns'] === ['payment_id'] && !$support) {
                    Schema::table('payment_payloads', fn(Blueprint $t) =>
                        $t->index('payment_id', $foreign['name']));
                    break;
                }
            }
        }
        Schema::table('payment_payloads', function (Blueprint $t): void {
            $t->dropForeign(['revision_id']); $t->dropColumn('revision_id');
            $t->dropUnique('global_profile_payment_unique');
        });
        Schema::drop('payment_merchant_revisions');
    }
}