<?php
declare(strict_types=1);

use App\Services\PaymentAccounting\AccountingSchema as Accounting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Accounting::create('payment_collection_contexts', [
            'id @pk', 'allocation_id @ref NOT NULL', 'funding_key VARCHAR(96) NOT NULL',
            'funding_slot VARCHAR(32) NOT NULL', 'confirmed_slot VARCHAR(32) NULL',
            'funding_event_key CHAR(36) NOT NULL', 'receipt_claim_key CHAR(64) NULL',
            'receipt_anchor_context_id @ref NULL', 'collection_mode VARCHAR(16) NOT NULL', 'custody_type VARCHAR(16) NOT NULL',
            'expected_collector_type VARCHAR(16) NOT NULL', 'expected_collector_id @ref NULL',
            'confirmed_collector_type VARCHAR(16) NULL', 'confirmed_collector_id @ref NULL',
            'credential_owner_type VARCHAR(16) NOT NULL', 'credential_owner_id @ref NULL',
            'payment_id @ref NOT NULL', 'provider_tag VARCHAR(32) NULL',
            'configuration_source VARCHAR(32) NULL', 'configuration_reference VARCHAR(96) NULL',
            'configuration_revision VARCHAR(96) NULL', 'merchant_binding_reference VARCHAR(128) NULL',
            'payment_process_reference VARCHAR(191) NULL', 'provider_payment_reference VARCHAR(191) NULL',
            'source_transaction_id @ref NULL', 'wallet_id @ref NULL', 'wallet_history_reference VARCHAR(96) NULL',
            'currency_id @ref NOT NULL', 'currency_code VARCHAR(8) NOT NULL', 'money_scale SMALLINT NOT NULL',
            'amount BIGINT NOT NULL', 'receipt_total_amount BIGINT NOT NULL',
            'original_commission_share BIGINT NULL', 'original_receivable_share BIGINT NULL',
            'original_adjustment_share BIGINT NULL', 'original_vendor_entitlement_share BIGINT NULL',
            "state VARCHAR(24) NOT NULL DEFAULT 'committed'", 'version BIGINT NOT NULL DEFAULT 0',
            'committed_at TIMESTAMP NOT NULL', 'confirmed_at TIMESTAMP NULL',
            'created_at TIMESTAMP NOT NULL', 'updated_at TIMESTAMP NOT NULL',
        ], [
            Accounting::restrict('allocation_id', 'commerce_payment_allocations'),
            Accounting::restrict('receipt_anchor_context_id', 'payment_collection_contexts'),
            Accounting::restrict('payment_id', 'payments'), Accounting::restrict('currency_id', 'currencies'),
            'CONSTRAINT pcc_funding_unique UNIQUE(allocation_id,funding_key)',
            'CONSTRAINT pcc_event_unique UNIQUE(funding_event_key,allocation_id)',
            'CONSTRAINT pcc_slot_unique UNIQUE(allocation_id,confirmed_slot)',
            'CONSTRAINT pcc_receipt_unique UNIQUE(receipt_claim_key)',
            'CHECK(amount > 0 AND receipt_total_amount >= amount AND money_scale BETWEEN 0 AND 8 AND version >= 0)',
            "CHECK(funding_slot IN ('wallet_contribution','selected_method'))",
            "CHECK(confirmed_slot IS NULL OR confirmed_slot = funding_slot)",
            "CHECK(state IN ('committed','pending','confirmed','rejected','canceled','review_required'))",
            "CHECK((state = 'confirmed' AND confirmed_at IS NOT NULL AND confirmed_slot IS NOT NULL AND confirmed_collector_type IS NOT NULL
                    AND ((receipt_claim_key IS NOT NULL AND receipt_anchor_context_id IS NULL) OR (receipt_claim_key IS NULL AND receipt_anchor_context_id IS NOT NULL)))
                OR (state <> 'confirmed' AND confirmed_at IS NULL AND confirmed_slot IS NULL AND confirmed_collector_type IS NULL
                    AND confirmed_collector_id IS NULL AND receipt_claim_key IS NULL AND receipt_anchor_context_id IS NULL))",
            'CHECK(receipt_anchor_context_id IS NULL OR receipt_anchor_context_id <> id)',
            "CHECK((collection_mode IN ('platform','internal') AND custody_type = 'platform' AND expected_collector_type = 'platform' AND expected_collector_id IS NULL)
                OR (collection_mode IN ('vendor_direct','offline') AND custody_type = 'vendor' AND expected_collector_type = 'shop' AND expected_collector_id IS NOT NULL))",
            "CHECK((collection_mode IN ('offline','internal') AND credential_owner_type = 'none' AND credential_owner_id IS NULL AND provider_tag IS NULL)
                OR (collection_mode = 'platform' AND credential_owner_type = 'platform' AND credential_owner_id IS NULL AND provider_tag IS NOT NULL
                    AND configuration_source IS NOT NULL AND configuration_reference IS NOT NULL AND configuration_revision IS NOT NULL)
                OR (collection_mode = 'vendor_direct' AND credential_owner_type = 'shop' AND credential_owner_id = expected_collector_id AND provider_tag IS NOT NULL
                    AND configuration_source IS NOT NULL AND configuration_reference IS NOT NULL AND configuration_revision IS NOT NULL))",
            'CHECK(confirmed_collector_type IS NULL OR (confirmed_collector_type = expected_collector_type AND
                ((confirmed_collector_id IS NULL AND expected_collector_id IS NULL) OR confirmed_collector_id = expected_collector_id)))',
            'CHECK((original_commission_share IS NULL AND original_receivable_share IS NULL AND original_adjustment_share IS NULL AND original_vendor_entitlement_share IS NULL)
                OR (original_commission_share IS NOT NULL AND original_receivable_share IS NOT NULL AND original_adjustment_share IS NOT NULL AND original_vendor_entitlement_share IS NOT NULL
                    AND original_commission_share >= 0 AND original_receivable_share >= 0 AND original_adjustment_share >= 0 AND original_vendor_entitlement_share >= 0
                    AND original_commission_share <= amount AND original_receivable_share <= amount - original_commission_share
                    AND original_adjustment_share <= amount - original_commission_share - original_receivable_share
                    AND original_vendor_entitlement_share = amount - original_commission_share - original_receivable_share - original_adjustment_share))',
        ], [
            'pcc_allocation_idx' => 'allocation_id,state,id', 'pcc_event_idx' => 'funding_event_key,id',
            'pcc_provider_idx' => 'provider_tag,credential_owner_type,credential_owner_id,provider_payment_reference',
            'pcc_process_idx' => 'payment_process_reference,id',
        ]);
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            $integers = ['amount','receipt_total_amount','money_scale','version'];
            $nullable = ['original_commission_share','original_receivable_share','original_adjustment_share','original_vendor_entitlement_share'];
            $checks = array_map(fn ($c) => "typeof(NEW.{$c}) = 'integer'", $integers);
            foreach ($nullable as $column) $checks[] = "(NEW.{$column} IS NULL OR typeof(NEW.{$column}) = 'integer')";
            foreach (['INSERT','UPDATE'] as $event) {
                \Illuminate\Support\Facades\DB::unprepared("CREATE TRIGGER pcc_integer_".strtolower($event)."
                    BEFORE {$event} ON payment_collection_contexts WHEN NOT (".implode(' AND ', $checks).")
                    BEGIN SELECT RAISE(ABORT, 'Funding amounts require exact integer units'); END");
            }
        }
    }

    public function down(): void
    {
        Accounting::refuseDestructiveRollback();
        Schema::dropIfExists('payment_collection_contexts');
    }
};