<?php
declare(strict_types=1);
namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Services\PaymentAccounting\{FinancialOperations,AllocationBalances};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PaymentOperationsController extends Controller
{
    private function scope(int $id): array
    {
        $a=DB::table('commerce_payment_allocations')->find($id);
        $u=auth('sanctum')->user();
        if (!$a || !$u) abort(404);
        $country=$u->countryAdmin?->country_id;
        $admin=$u->hasRole('admin') && ($country===null || (int)$country===(int)$a->country_id);
        $shop=$u->hasShopPermission((int)$a->shop_id,'payments.gateways.manage');
        if (!$admin && !$shop) abort(403);
        return [$a,$u,$admin,$shop];
    }

    private function operation(string $id): array
    {
        $op=DB::table('payment_financial_operations')->find($id);
        if (!$op) abort(404);
        return [$op,...$this->scope((int)$op->allocation_id)];
    }

    public function index(int $allocation): array
    {
        return DB::transaction(fn()=>$this->snapshot($allocation));
    }
    private function snapshot(int $allocation): array
    {
        [$a,$u,$admin,$shop]=$this->scope($allocation);
        $ops=DB::table('payment_financial_operations')->where('allocation_id',$allocation)->orderByDesc('created_at')->get();
        foreach ($ops as $op) $op->amount_units=(string)$op->amount_units;
        $balances=(new AllocationBalances)->current($allocation);
        foreach ($balances as $key=>$value) if (is_int($value)) $balances[$key]=(string)$value;
        $contexts=DB::table('payment_collection_contexts')->where('allocation_id',$allocation)
            ->get(['id','provider_tag','collection_mode','custody_type','amount','state']);
        foreach ($contexts as $c) $c->amount=(string)$c->amount;
        $keys=DB::table('payment_collection_contexts')->where('allocation_id',$allocation)->pluck('funding_event_key');
        $attempts=DB::table('electronic_collection_attempts')->whereIn('funding_event_key',$keys)
            ->get(['id','provider','state','provider_reference','provider_payment_id','claimed_at','created_at','updated_at']);
        return ['data'=>['operations'=>$ops,'contexts'=>$contexts,'can_process'=>$admin,'can_request_payout'=>$shop||$admin,
            'balances'=>$balances,'currency_code'=>$a->currency_code,'money_scale'=>(int)$a->money_scale,
            'attempts'=>$attempts,'external_payout_ready'=>false,
            'reserved_vendor_payable'=>(string)(new FinancialOperations)->reserved($allocation,'payout')]];
    }

    public function collection(string $attempt): array
    {
        $a=DB::table('electronic_collection_attempts')->find($attempt);
        if (!$a) abort(404);
        $c=DB::table('payment_collection_contexts')->find($a->anchor_context_id);
        [,,$admin]=$this->scope((int)$c->allocation_id);
        if (!$admin) abort(403);
        return $this->run(fn()=>(new \App\Services\PaymentAccounting\CollectionReconciliation)->reconcile($attempt));
    }

    public function attempts(): array
    {
        $u=auth('sanctum')->user();
        if (!$u || !$u->hasRole('admin')) abort(403);
        $country=$u->countryAdmin?->country_id;
        $query=DB::table('electronic_collection_attempts as e')
            ->join('payment_collection_contexts as c','c.id','=','e.anchor_context_id')
            ->join('commerce_payment_allocations as a','a.id','=','c.allocation_id');
        if ($country!==null) $query->where('a.country_id',(int)$country);
        return ['data'=>['attempts'=>$query->orderByDesc('e.created_at')->limit(50)->get([
            'e.id','e.provider','e.state','e.provider_reference','e.provider_payment_id','e.claimed_at','e.created_at',
            'a.id as allocation_id','a.shop_id','a.country_id','a.currency_code','a.money_scale'])]];
    }

    public function store(Request $request, int $allocation): array
    {
        [$a,$u,$admin,$shop]=$this->scope($allocation);
        $v=$request->validate(['kind'=>'required|in:refund,receivable,payout','request_key'=>'required|uuid',
            'amount_units'=>'required|regex:/^[1-9][0-9]{0,17}$/','context_id'=>'nullable|integer|min:1']);
        if (!$admin && $v['kind']!=='payout') abort(403);
        return $this->run(fn()=>(new FinancialOperations)->reserveOwned($allocation,$v['kind'],$v['request_key'],
            (int)$v['amount_units'],(int)$u->id,isset($v['context_id'])?(int)$v['context_id']:null));
    }

    public function action(Request $request, string $operation, string $action): array
    {
        [$op,$a,$u,$admin,$shop]=$this->operation($operation);
        if (!$admin && !($op->kind==='payout' && $action==='cancel' && (int)$op->actor_id===(int)$u->id)) abort(403);
        return $this->run(function () use ($request,$op,$action) {
            $service=new FinancialOperations;
            if ($action==='cancel') {$service->cancel($op->id);return DB::table('payment_financial_operations')->find($op->id);}
            if ($action==='dispatch' || $action==='reconcile') return $service->refund($op->id,$action==='reconcile');
            if ($action==='receipt') {
                $data=$request->validate(['source'=>'required|in:cash_receipt','receipt_reference'=>'required|string|max:128',
                    'document_sha256'=>'required|regex:/^[a-f0-9]{64}$/','retained_evidence'=>'required|string|min:20|max:4000',
                    'received_at'=>'required|date|before_or_equal:now']);
                return $service->receipt($op->id,$data);
            }
            abort(404);
        });
    }

    private function run(callable $work): array
    {
        try {
            $data=$work(); if (is_object($data) && isset($data->amount_units)) $data->amount_units=(string)$data->amount_units;
            return ['data'=>$data];
        } catch (\DomainException $e) {
            abort(422,$e->getMessage());
        } catch (\Throwable $e) {
            // Do not return SQL bindings, retained documents or credential-bearing traces.
            abort(409,'Operation conflicted or original evidence is unavailable; retry the same operation.');
        }
    }
}