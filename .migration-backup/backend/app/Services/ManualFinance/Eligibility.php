<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

use App\Models\User;
use App\Services\PaymentAccounting\{AllocationBalances,ExactMoney,FinancialOperations};
use Illuminate\Support\Facades\DB;

final class Eligibility
{
    public function source(object $a): object
    {
        if (!in_array($a->payable_type,['booking','order'],true) || $a->purpose!=='base' || $a->obligation_key!=='base') {
            throw new \DomainException('Only an original base Booking/Order allocation is eligible.');
        }
        $source = DB::table($a->payable_type==='booking'?'bookings':'orders')->where('id',$a->payable_id)->first();
        if (!$source || (int)$source->shop_id!==(int)$a->shop_id || (int)$source->user_id!==(int)$a->payer_user_id
            || (int)$source->currency_id!==(int)$a->currency_id
            || !DB::table('shops')->where('id',$a->shop_id)->where('user_id',$a->vendor_user_id)->exists()) {
            throw new \DomainException('Original source/beneficiary/Shop/currency cannot be established.');
        }
        if (!$a->finalized_at || in_array($a->state,['review_required','canceled'],true)) {
            throw new \DomainException('Finalized verified original economics required.');
        }
        return $source;
    }

    /** Fixed policy amount; never a user-controlled partial-refund editor. */
    public function refund(object $a, int $contextId): array
    {
        $source = $this->source($a);
        $context = DB::table('payment_collection_contexts')->where('id',$contextId)
            ->where('allocation_id',$a->id)->where('state','confirmed')->first();
        if (!$context || !in_array($context->collection_mode,['platform','vendor_direct'],true)
            || !$context->provider_payment_reference || in_array($context->provider_tag,['wallet','cash'],true)
            || $context->funding_slot==='wallet_contribution'
            || $context->currency_code!==$a->currency_code || (int)$context->money_scale!==(int)$a->money_scale) {
            throw new \DomainException('Verified collected electronic principal required; Cash proof and Wallet redemption are unavailable.');
        }
        $policy = ['authority'=>'native_refund_policy','source_type'=>$a->payable_type,'source_id'=>$a->payable_id];
        if ($a->payable_type==='booking') {
            $hour = DB::table('settings')->where('key','booking_refund_canceled_hour')->value('value') ?? '24';
            $percentage = DB::table('settings')->where('key','booking_canceled_commission')->value('value');
            if (!is_numeric($hour) || (float)$hour<0 || $percentage===null || !isset($source->start_date)) {
                throw new \DomainException('Authoritative Booking refund policy unavailable.');
            }
            $inWindow = strtotime($source->start_date)>time()-(float)$hour*3600;
            $fee = $inWindow ? ExactMoney::units((string)$percentage,2) : 10000;
            if ($fee<0 || $fee>10000) throw new \DomainException('Invalid native cancellation policy.');
            $policy += ['window_hours'=>(string)$hour,'fee_basis_points'=>$fee,'evaluated_at'=>now()->utc()->toIso8601String()];
        } else {
            // Digital line entitlement needs per-line authoritative allocation:
            // do not call the legacy mutating float/statistics refund calculator.
            if (DB::table('order_details as d')->join('stocks as s','s.id','=','d.stock_id')
                ->join('digital_files as f','f.product_id','=','s.product_id')->where('d.order_id',$source->id)->exists()) {
                throw new \DomainException('Digital refund policy requires authoritative per-line review.');
            }
        }
        $ceiling = $this->sharedRefundCeiling($a,$context,$policy);
        $returned = $this->returned((int)$a->id,$contextId);
        $held = (new FinancialOperations)->reserved((int)$a->id,'refund',$contextId);
        $remaining = max(0,$ceiling-$returned);
        $eligible = $held >= $remaining ? 0 : $remaining-$held;
        if ($eligible<=0) throw new \DomainException('No eligible original principal remains.');
        return [$eligible,$context,$policy];
    }

    /** Called beneath the owned allocation mutex; the current operation is held too. */
    public function assertRefundPolicyHeld(object $a,object $context,array $policy,int $amount): void
    {
        $ceiling=$this->sharedRefundCeiling($a,$context,$policy);
        $returned=$this->returned((int)$a->id,(int)$context->id);
        $held=(new FinancialOperations)->reserved((int)$a->id,'refund',(int)$context->id);
        if ($returned>$ceiling || $held>$ceiling-$returned || $held<$amount) {
            throw new \DomainException('Held refunds exceed shared original refund-policy authority.');
        }
    }

    private function returned(int $allocation,int $context): int
    {
        return (int)DB::table('platform_fee_ledger_entries')->where('allocation_id',$allocation)
            ->where('collection_context_id',$context)->where('effect_kind','refund_principal')->sum('exact_amount');
    }

    private function sharedRefundCeiling(object $a,object $context,array $policy): int
    {
        $ceiling=$this->refundPolicyCeiling($a,$context,$policy);
        // A newer, more permissive quote must not strand an older held mandate
        // by expanding its aggregate authority. Released/completed mandates do
        // not freeze future requests; completed principal remains deducted.
        $snapshots=DB::table('manual_financial_workflows as w')
            ->join('payment_financial_operations as op','op.id','=','w.operation_id')
            ->where('w.allocation_id',$a->id)->where('w.kind','refund')
            ->where('op.allocation_id',$a->id)->where('op.kind','refund')
            ->where('op.context_id',$context->id)->whereIn('op.state',['RESERVED','UNKNOWN','PENDING'])
            ->pluck('w.policy_snapshot');
        foreach ($snapshots as $snapshot) {
            $frozen=json_decode($snapshot,true,512,JSON_THROW_ON_ERROR);
            if (!is_array($frozen)) throw new \DomainException('Original refund policy snapshot unavailable.');
            $ceiling=min($ceiling,$this->refundPolicyCeiling($a,$context,$frozen));
        }
        return $ceiling;
    }

    private function refundPolicyCeiling(object $a,object $context,array $policy): int
    {
        if ((int)$context->allocation_id!==(int)$a->id
            || ($policy['authority']??null)!=='native_refund_policy'
            || ($policy['source_type']??null)!==$a->payable_type
            || (string)($policy['source_id']??'')!==(string)$a->payable_id) {
            throw new \DomainException('Original refund policy binding unavailable.');
        }
        $fee=$a->payable_type==='booking' ? ($policy['fee_basis_points']??null) : 0;
        if (!is_int($fee) || $fee<0 || $fee>10000) {
            throw new \DomainException('Original refund policy amount unavailable.');
        }
        return ExactMoney::portion((int)$context->amount,10000-$fee,10000);
    }

    public function payout(object $a): int
    {
        $source = $this->source($a);
        if (($a->payable_type==='booking' && $source->status!=='ended')
            || ($a->payable_type==='order' && ($source->status!=='delivered' || ($source->fulfillment_financial_state??null)!=='settled'))) {
            throw new \DomainException('Verified fulfilled/matured Vendor entitlement required.');
        }
        $balance = (new AllocationBalances)->current((int)$a->id);
        $available = (int)$balance['vendor_payable']-(new FinancialOperations)->reserved((int)$a->id,'payout');
        if ($available<=0) throw new \DomainException('No unreserved platform Vendor payable; Wallet value and Vendor-direct custody are not payout authority.');
        return $available;
    }

    public function request(User $u, object $a, array $input): array
    {
        $kind = $input['kind'];
        $scope = new FinanceScope;
        if ($kind==='refund') {
            if ((int)$a->payer_user_id!==(int)$u->id) $scope->require($u,$a,$kind,'request');
            [$amount,$context,$policy] = $this->refund($a,(int)($input['context_id']??0));
            $beneficiary = (int)$a->payer_user_id;
            $executor = $context->custody_type==='platform' ? 'platform' : 'shop:'.$a->shop_id;
        } else {
            if (!$scope->vendor($u,$a)) abort(403,'Only the original Vendor beneficiary may request platform payout. Specialist compensation is Shop-funded and outside this workflow.');
            if (!empty($input['context_id'])) throw new \DomainException('Payout cannot select refund contribution.');
            $amount = $this->payout($a); $context = null; $beneficiary = (int)$a->vendor_user_id;
            $policy = ['authority'=>'verified_matured_platform_vendor_payable']; $executor = 'platform';
        }
        if ((string)$amount!==$input['amount_units'] || $input['currency_code']!==$a->currency_code) {
            throw new \DomainException('Request must use the fixed current eligible amount and original currency.');
        }
        return [$amount,$context,$beneficiary,$policy,$executor];
    }
}
