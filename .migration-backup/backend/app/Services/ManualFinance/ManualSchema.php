<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

/** Additive identities only. No history mapping, grants, balance writes or transport. */
final class ManualSchema
{
    public const TABLES = ['manual_financial_workflows','manual_financial_commands',
        'manual_financial_events','manual_financial_attachments','manual_financial_evidence',
        'manual_financial_notifications','manual_financial_evidence_access'];

    public function up(): void
    {
        Schema::create('manual_financial_workflows', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->foreignUuid('operation_id')->unique()->constrained('payment_financial_operations')->restrictOnDelete();
            $t->foreignId('allocation_id')->constrained('commerce_payment_allocations')->restrictOnDelete();
            $t->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('beneficiary_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('shop_id')->constrained('shops')->restrictOnDelete();
            $t->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $t->string('kind',16); $t->bigInteger('amount_units');
            $t->string('currency_code',8); $t->unsignedTinyInteger('money_scale');
            $t->string('state',24)->default('REQUESTED'); $t->unsignedInteger('version')->default(0);
            $t->string('method',24); $t->string('institution',48); $t->string('destination_mask',80);
            $t->char('destination_fingerprint',64); $t->string('execution_scope',80);
            $t->longText('policy_snapshot'); $t->char('snapshot_digest',64);
            $t->timestamp('requested_at'); $t->timestamp('approved_at')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('completed_at')->nullable();
            $t->foreignId('completed_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->uuid('claim_id')->nullable(); $t->foreignId('claim_actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $t->timestamp('claimed_at')->nullable(); $t->unsignedInteger('attempt')->default(0);
            $t->index(['country_id','kind','state','requested_at'],'manual_queue_country');
            $t->index(['shop_id','kind','state'],'manual_queue_shop');
            $t->index(['beneficiary_id','requested_at'],'manual_queue_beneficiary');
        });
        Schema::create('manual_financial_commands', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $t->uuid('command_key'); $t->string('action',24); $t->string('scope',80);
            $t->char('payload_digest',64); $t->longText('result'); $t->timestamp('created_at');
            $t->unique(['actor_id','command_key'],'manual_command_identity');
        });
        Schema::create('manual_financial_events', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->foreignUuid('workflow_id')->constrained('manual_financial_workflows')->restrictOnDelete();
            $t->foreignUuid('command_id')->unique()->constrained('manual_financial_commands')->restrictOnDelete();
            $t->unsignedInteger('version'); $t->string('action',24); $t->string('previous_state',24)->nullable();
            $t->string('new_state',24); $t->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $t->string('authority',128); $t->longText('evidence'); $t->timestamp('created_at');
            $t->unique(['workflow_id','version'],'manual_event_version');
        });
        Schema::create('manual_financial_attachments', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->foreignUuid('workflow_id')->constrained('manual_financial_workflows')->restrictOnDelete();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->string('path')->unique(); $t->string('mime',48); $t->unsignedInteger('bytes');
            $t->char('sha256',64); $t->timestamp('created_at');
        });
        Schema::create('manual_financial_evidence', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->foreignUuid('workflow_id')->constrained('manual_financial_workflows')->restrictOnDelete();
            $t->foreignUuid('operation_id')->constrained('payment_financial_operations')->restrictOnDelete();
            $t->uuid('claim_id'); $t->unsignedInteger('attempt'); $t->string('state',24);
            $t->bigInteger('amount_units'); $t->string('currency_code',8); $t->foreignId('beneficiary_id')->constrained('users')->restrictOnDelete();
            $t->string('method',24); $t->string('institution',48); $t->char('destination_fingerprint',64);
            $t->string('external_reference',128); $t->char('execution_identity',64)->unique();
            $t->foreignUuid('attachment_id')->nullable()->constrained('manual_financial_attachments')->restrictOnDelete();
            $t->char('document_sha256',64)->nullable(); $t->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $t->timestamp('executed_at'); $t->timestamp('recorded_at'); $t->boolean('attested');
            $t->unique(['workflow_id','attempt'],'manual_attempt_evidence');
        });
        Schema::create('manual_financial_notifications', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->foreignUuid('event_id')->constrained('manual_financial_events')->restrictOnDelete();
            $t->foreignId('recipient_id')->constrained('users')->restrictOnDelete();
            $t->string('template_type',48); $t->string('state',24)->default('LIBRARY_ONLY');
            $t->timestamp('created_at'); $t->unique(['event_id','recipient_id','template_type'],'manual_notification_intent');
        });
        Schema::create('manual_financial_evidence_access', function (Blueprint $t): void {
            $t->uuid('id')->primary(); $t->foreignUuid('attachment_id')->constrained('manual_financial_attachments')->restrictOnDelete();
            $t->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $t->string('action',16); $t->timestamp('created_at');
        });
        $driver = DB::connection()->getDriverName();
        foreach (array_diff(self::TABLES,['manual_financial_workflows','manual_financial_notifications']) as $table) {
            foreach (['UPDATE','DELETE'] as $verb) {
                $name = $table.'_'.strtolower($verb).'_guard';
                DB::unprepared($driver === 'sqlite'
                    ? "CREATE TRIGGER $name BEFORE $verb ON $table BEGIN SELECT RAISE(ABORT,'Immutable manual financial evidence'); END"
                    : "CREATE TRIGGER $name BEFORE $verb ON $table FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Immutable manual financial evidence'");
            }
        }
        $fields = ['id','operation_id','allocation_id','requester_id','beneficiary_id','shop_id','country_id','kind',
            'amount_units','currency_code','money_scale','method','institution','destination_mask','destination_fingerprint',
            'execution_scope','policy_snapshot','snapshot_digest','requested_at'];
        $change = implode(' OR ', array_map(fn($f)=>$driver === 'sqlite' ? "NEW.$f IS NOT OLD.$f" : "NOT (NEW.$f <=> OLD.$f)",$fields));
        $terminal = "OLD.state IN ('COMPLETED','REJECTED','CANCELLED')";
        DB::unprepared($driver === 'sqlite'
            ? "CREATE TRIGGER manual_workflow_binding BEFORE UPDATE ON manual_financial_workflows WHEN $change OR $terminal BEGIN SELECT RAISE(ABORT,'Immutable manual workflow'); END"
            : "CREATE TRIGGER manual_workflow_binding BEFORE UPDATE ON manual_financial_workflows FOR EACH ROW BEGIN IF $change OR $terminal THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Immutable manual workflow'; END IF; END");
        DB::unprepared($driver === 'sqlite'
            ? "CREATE TRIGGER manual_workflow_delete BEFORE DELETE ON manual_financial_workflows BEGIN SELECT RAISE(ABORT,'Retained manual workflow'); END"
            : "CREATE TRIGGER manual_workflow_delete BEFORE DELETE ON manual_financial_workflows FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Retained manual workflow'");
    }

    public function down(): void
    {
        if (DB::table('manual_financial_workflows')->exists()) {
            throw new \RuntimeException('Retained financial identities cannot be dropped; disable initiation and reconcile.');
        }
        foreach (array_reverse(self::TABLES) as $table) Schema::dropIfExists($table);
    }
}
