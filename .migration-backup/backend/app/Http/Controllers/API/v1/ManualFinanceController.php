<?php
declare(strict_types=1);
namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ManualFinance\{WorkflowService,WorkflowQueries,FinanceScope,Eligibility,PrivateEvidence};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ManualFinanceController extends Controller
{
    private function actor(): User { return auth('sanctum')->user()??abort(401); }
    private function workflow(string $id): object
    {
        $w=DB::table('manual_financial_workflows')->where('id',$id)->first();
        if (!$w) abort(404);
        return $w;
    }
    public function capabilities(): array
    {
        $u=$this->actor(); $scope=new FinanceScope; $finance=false;
        foreach (DB::table('countries')->select('id')->get() as $country) {
            $shopIds=DB::table('shop_locations')->where('country_id',$country->id)->pluck('shop_id')->all();
            foreach (array_unique(array_merge([null],$shopIds)) as $shopId) {
            $a=(object)['country_id'=>$country->id,'shop_id'=>$shopId];
            foreach (['refund','payout'] as $kind) foreach (['view','approve','complete','reconcile'] as $grant) {
                if ($scope->has($u,$a,$kind,$grant)) $finance=true;
            }
            }
        }
        return ['data'=>['finance'=>$finance,'can_request_refund'=>true,
            'can_request_payout'=>DB::table('shops')->where('user_id',$u->id)->exists(),'specialist_payout_enabled'=>false]];
    }
    public function index(Request $r): array
    {
        $query=DB::table('manual_financial_workflows')->orderByDesc('requested_at');
        foreach (['kind','state','shop_id'] as $field) if ($r->filled($field)) $query->where($field,$r->input($field));
        if ($r->boolean('claimed')) $query->whereNotNull('claim_id')->whereNull('completed_at');
        if ($r->boolean('aged')) $query->where('requested_at','<',now()->subDays(7))->whereNotIn('state',['COMPLETED','REJECTED','CANCELLED']);
        $u=$this->actor(); $scope=new FinanceScope; $rows=[];
        // Scope before result limit: a foreign record cannot hide a visible record.
        foreach ($query->cursor() as $w) {
            $a=DB::table('commerce_payment_allocations')->where('id',$w->allocation_id)->first();
            if ($a && $scope->readable($u,$a,$w->kind)) $rows[]=(new WorkflowQueries)->safe($u,$w,false);
            if (count($rows)===100) break;
        }
        return ['data'=>$rows,'meta'=>['limit'=>100]];
    }
    public function show(string $workflow): array { return ['data'=>(new WorkflowQueries)->safe($this->actor(),$this->workflow($workflow))]; }
    public function sources(Request $r): array
    {
        $kind=$r->validate(['kind'=>'required|in:refund,payout'])['kind']; $u=$this->actor(); $scope=new FinanceScope; $rows=[];
        $query=DB::table('commerce_payment_allocations')->whereNotNull('finalized_at')->orderByDesc('id');
        if ($kind==='payout') $query->where('vendor_user_id',$u->id);
        else $query->where('payer_user_id',$u->id);
        foreach ($query->cursor() as $a) {
            try {
                if ($kind==='payout') {
                    if (!$scope->vendor($u,$a)) continue;
                    $amount=(new Eligibility)->payout($a); $contexts=[null];
                } else $contexts=DB::table('payment_collection_contexts')->where('allocation_id',$a->id)->where('state','confirmed')->pluck('id')->all();
                foreach ($contexts as $context) {
                    try {
                        if ($kind==='refund') [$amount]=(new Eligibility)->refund($a,(int)$context);
                        $rows[]=['allocation_id'=>$a->id,'context_id'=>$context,'kind'=>$kind,'source_type'=>$a->payable_type,
                            'source_id'=>$a->payable_id,'shop_id'=>$a->shop_id,'beneficiary_id'=>$kind==='refund'?$a->payer_user_id:$a->vendor_user_id,
                            'amount_units'=>(string)$amount,'currency_code'=>$a->currency_code,'money_scale'=>(int)$a->money_scale];
                    } catch (\DomainException $e) { /* Unsupported authority is not made eligible. */ }
                }
            } catch (\DomainException $e) { /* Legacy/unmatured sources remain ineligible. */ }
            if (count($rows)>=100) break;
        }
        return ['data'=>array_slice($rows,0,100)];
    }
    public function store(Request $r): array
    {
        $i=$r->validate(['command_key'=>'required|uuid','kind'=>'required|in:refund,payout','allocation_id'=>'required|integer|min:1',
            'context_id'=>'nullable|integer|min:1','amount_units'=>'required|string|regex:/^[1-9][0-9]{0,17}$/',
            'currency_code'=>'required|string|max:8','method'=>'required|in:bank_transfer,mobile_money',
            'institution'=>'required|string|max:48','destination_mask'=>'required|string|max:80']);
        return $this->run(fn()=>(new WorkflowService)->request($this->actor(),$i));
    }
    public function action(Request $r,string $workflow,string $action): array
    {
        $i=$r->validate(['command_key'=>'required|uuid','version'=>'required|integer|min:0','reason'=>'nullable|string|max:500',
            'no_execution_attested'=>'nullable|boolean','target'=>'nullable|in:COMPLETED,APPROVED,REJECTED',
            'evidence'=>'nullable|array','evidence.allocation_id'=>'required_with:evidence|integer|min:1',
            'evidence.amount_units'=>'required_with:evidence|string|regex:/^[1-9][0-9]{0,17}$/',
            'evidence.currency_code'=>'required_with:evidence|string|max:8','evidence.beneficiary_id'=>'required_with:evidence|integer|min:1',
            'evidence.method'=>'required_with:evidence|in:bank_transfer,mobile_money','evidence.institution'=>'required_with:evidence|string|max:48',
            'evidence.destination_mask'=>'required_with:evidence|string|max:80','evidence.external_reference'=>'required_with:evidence|string|max:128',
            'evidence.executed_at'=>'required_with:evidence|date','evidence.state'=>'required_with:evidence|in:SUCCESS,NO_MOVEMENT',
            'evidence.attested'=>'required_with:evidence|boolean','evidence.attachment_id'=>'nullable|uuid']);
        if (preg_match('/[0-9]{13,}|\b(cvv|password|smtp credential|api[_ -]?key|secret)\b/i',$i['reason']??'')) abort(422,'Do not include credentials or full account/card numbers in audit notes.');
        return $this->run(fn()=>(new WorkflowService)->action($this->actor(),$workflow,$action,$i));
    }
    public function upload(Request $r,string $workflow): array
    {
        $r->validate(['file'=>'required|file|max:2048|mimetypes:application/pdf,image/png,image/jpeg']);
        return $this->run(fn()=>(new PrivateEvidence)->upload($this->actor(),$this->workflow($workflow),$r->file('file')));
    }
    public function link(string $workflow,string $attachment): array
    {
        $file=DB::table('manual_financial_attachments')->where('id',$attachment)->first()??abort(404);
        return $this->run(fn()=>['url'=>(new PrivateEvidence)->link($this->actor(),$this->workflow($workflow),$file)]);
    }
    public function download(Request $r,string $workflow,string $attachment): \Symfony\Component\HttpFoundation\Response
    {
        if (!$r->hasValidSignature(false)) abort(403,'Expired or invalid private evidence link.');
        $u=User::find((int)$r->query('actor'))??abort(403);
        $file=DB::table('manual_financial_attachments')->where('id',$attachment)->first()??abort(404);
        return (new PrivateEvidence)->download($u,$this->workflow($workflow),$file);
    }
    private function run(callable $work): array
    {
        try { return ['data'=>$work()]; }
        catch (HttpException $e) { throw $e; }
        catch (\DomainException $e) { abort(422,$e->getMessage()); }
        catch (\Throwable $e) { abort(409,'Required financial recording did not commit. Refresh/reconcile the same intent; never repeat external payment.'); }
    }
}
