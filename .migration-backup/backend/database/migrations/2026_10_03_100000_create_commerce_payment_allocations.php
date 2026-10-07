<?php
declare(strict_types=1);

use App\Services\PaymentAccounting\AccountingSchema as Accounting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columns = [
            'id @pk', 'checkout_key CHAR(36) NOT NULL',
            'origin_type VARCHAR(16) NOT NULL', 'origin_id @ref NOT NULL',
            'shop_id @ref NOT NULL', 'vendor_user_id @ref NOT NULL',
            'payer_user_id @ref NULL', 'local_client_id @ref NULL',
            'country_id @ref NOT NULL', 'currency_id @ref NOT NULL', 'currency_code VARCHAR(8) NOT NULL',
            'money_scale SMALLINT NOT NULL', 'purpose VARCHAR(16) NOT NULL', 'obligation_key VARCHAR(64) NOT NULL',
            'payable_type VARCHAR(16) NULL', 'payable_id @ref NULL',
            'gross_amount BIGINT NOT NULL', 'commission_amount BIGINT NOT NULL',
            'vendor_entitlement_amount BIGINT NOT NULL', 'adjustment_amount BIGINT NOT NULL',
            'native_components JSON NOT NULL', 'policy_key VARCHAR(48) NOT NULL',
            "state VARCHAR(24) NOT NULL DEFAULT 'committed'", 'version BIGINT NOT NULL DEFAULT 0',
            'committed_at TIMESTAMP NOT NULL', 'finalized_at TIMESTAMP NULL',
            'created_at TIMESTAMP NOT NULL', 'updated_at TIMESTAMP NOT NULL',
        ];
        $original = [
            'original_platform_amount', 'original_vendor_direct_amount',
            'original_commission_satisfied', 'original_commission_receivable',
            'original_platform_adjustment', 'original_vendor_adjustment', 'original_vendor_payable',
        ];
        foreach ($original as $column) {
            $columns[] = "{$column} BIGINT NULL";
        }
        $nulls = implode(' AND ', array_map(fn ($c) => "{$c} IS NULL", $original));
        $filled = implode(' AND ', array_map(fn ($c) => "{$c} IS NOT NULL AND {$c} >= 0", $original));
        Accounting::create('commerce_payment_allocations', $columns, [
            Accounting::restrict('shop_id', 'shops'), Accounting::restrict('vendor_user_id', 'users'),
            Accounting::restrict('payer_user_id', 'users'), Accounting::restrict('country_id', 'countries'),
            Accounting::restrict('currency_id', 'currencies'),
            'CONSTRAINT cpa_origin_unique UNIQUE(checkout_key,origin_type,origin_id,shop_id,purpose,obligation_key)',
            'CONSTRAINT cpa_payable_unique UNIQUE(payable_type,payable_id,purpose,obligation_key)',
            "CHECK(origin_type IN ('cart','order','booking'))",
            "CHECK(purpose IN ('base','tip','extra_time') AND (purpose <> 'base' OR obligation_key = 'base'))",
            "CHECK(policy_key = 'platform_held_first_commission')",
            "CHECK(state IN ('committed','funding','funded','partially_reversed','fully_reversed','canceled','review_required'))",
            "CHECK((state IN ('committed','funding','canceled') AND finalized_at IS NULL)
                OR (state IN ('funded','partially_reversed','fully_reversed') AND finalized_at IS NOT NULL)
                OR state = 'review_required')",
            'CHECK(money_scale BETWEEN 0 AND 8 AND version >= 0)',
            'CHECK(gross_amount >= 0 AND commission_amount >= 0 AND vendor_entitlement_amount >= 0 AND adjustment_amount >= 0)',
            // Subtractive form avoids overflow from summing signed BIGINT values.
            'CHECK(commission_amount <= gross_amount AND adjustment_amount <= gross_amount - commission_amount AND vendor_entitlement_amount = gross_amount - commission_amount - adjustment_amount)',
            "CHECK((payable_type IS NULL AND payable_id IS NULL) OR (payable_type IS NOT NULL AND payable_type IN ('order','booking') AND payable_id IS NOT NULL))",
            "CHECK((finalized_at IS NULL AND {$nulls}) OR (finalized_at IS NOT NULL AND {$filled}
                AND original_platform_amount <= gross_amount
                AND original_vendor_direct_amount = gross_amount - original_platform_amount
                AND original_commission_satisfied <= commission_amount
                AND original_commission_satisfied <= original_platform_amount
                AND (original_commission_satisfied = commission_amount OR original_commission_satisfied = original_platform_amount)
                AND original_commission_receivable = commission_amount - original_commission_satisfied
                AND original_platform_adjustment <= original_platform_amount - original_commission_satisfied
                AND original_vendor_adjustment = adjustment_amount - original_platform_adjustment
                AND original_vendor_adjustment <= original_vendor_direct_amount - original_commission_receivable
                AND original_vendor_payable = original_platform_amount - original_commission_satisfied - original_platform_adjustment))",
        ], [
            'cpa_checkout_idx' => 'checkout_key,id', 'cpa_shop_idx' => 'shop_id,currency_id,state,id',
            'cpa_vendor_idx' => 'vendor_user_id,currency_id,state,id', 'cpa_country_idx' => 'country_id,currency_id,state,id',
        ]);
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            $integer = "typeof(gross_amount) = 'integer' AND typeof(commission_amount) = 'integer'
                AND typeof(vendor_entitlement_amount) = 'integer' AND typeof(adjustment_amount) = 'integer'
                AND typeof(money_scale) = 'integer' AND typeof(version) = 'integer'";
            foreach ($original as $column) $integer .= " AND ({$column} IS NULL OR typeof({$column}) = 'integer')";
            foreach (['INSERT','UPDATE'] as $event) {
                $condition = preg_replace('/typeof\\(([a-z_]+)\\)/', 'typeof(NEW.$1)', $integer);
                foreach ($original as $column) $condition = str_replace("({$column} IS NULL", "(NEW.{$column} IS NULL", $condition);
                \Illuminate\Support\Facades\DB::unprepared("CREATE TRIGGER cpa_integer_".strtolower($event)."
                    BEFORE {$event} ON commerce_payment_allocations WHEN NOT ({$condition})
                    BEGIN SELECT RAISE(ABORT, 'Accounting amounts require exact integer units'); END");
            }
        }
    }

    public function down(): void
    {
        Accounting::refuseDestructiveRollback();
        Schema::dropIfExists('commerce_payment_allocations');
    }
};