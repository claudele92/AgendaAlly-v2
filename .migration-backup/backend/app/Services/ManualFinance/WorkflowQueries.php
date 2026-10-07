<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class WorkflowQueries
{
    public function safe(User $actor,object $w,bool $detail=true): array
    {
        $a=DB::table('commerce_payment_allocations')->where('id',$w->allocation_id)->first();
        $scope=new FinanceScope;
        if (!$a || !$scope->readable($actor,$a,$w->kind)) abort(404);
        $data=array_intersect_key((array)$w,array_flip(['id','operation_id','allocation_id','kind','state','version','requester_id',
            'beneficiary_id','shop_id','amount_units','currency_code','money_scale','requested_at','approved_at','completed_at',
            'method','institution','destination_mask','claim_actor_id','claimed_at']));
        $data['amount_units']=(string)$w->amount_units; $data['version']=(int)$w->version; $data['money_scale']=(int)$w->money_scale;
        $data['actions']=[];
        if ($w->state==='REQUESTED') {
            if ((int)$w->requester_id===(int)$actor->id) $data['actions'][]='cancel';
            if ($scope->has($actor,$a,$w->kind,'approve')) $data['actions']=array_merge($data['actions'],['approve','reject','cancel']);
        }
        if ($w->state==='APPROVED') {
            if (!$w->claim_id && $scope->has($actor,$a,$w->kind,'approve')) $data['actions'][]='cancel';
            if ($scope->has($actor,$a,$w->kind,'complete') && ($w->execution_scope==='platform'||$scope->has($actor,$a,'refund','vendor_direct.execute'))) {
                if (!$w->claim_id) $data['actions'][]='claim';
                elseif ((int)$w->claim_actor_id===(int)$actor->id) $data['actions']=array_merge($data['actions'],['complete','review']);
            }
        }
        if ($w->state==='REQUIRES_REVIEW' && $scope->has($actor,$a,$w->kind,'reconcile')) $data['actions'][]='reconcile';
        if ($scope->has($actor,$a,$w->kind,'evidence.view')) $data['actions'][]='evidence-view';
        if (!in_array($w->state,['COMPLETED','CANCELLED','REJECTED'],true)
            && ($scope->has($actor,$a,$w->kind,'complete')||$scope->has($actor,$a,$w->kind,'reconcile'))) $data['actions'][]='upload-evidence';
        $data['actions']=array_values(array_unique($data['actions']));
        $e=DB::table('manual_financial_evidence')->where('workflow_id',$w->id)->where('state','SUCCESS')->first();
        $data['external_reference']=$w->state==='COMPLETED'?$e?->external_reference:null;
        $data['executed_at']=$w->state==='COMPLETED'?$e?->executed_at:null;
        if ($detail) {
            $finance=$scope->has($actor,$a,$w->kind,'view')||$scope->has($actor,$a,$w->kind,'approve')
                ||$scope->has($actor,$a,$w->kind,'complete')||$scope->has($actor,$a,$w->kind,'reconcile');
            $data['events']=DB::table('manual_financial_events')->where('workflow_id',$w->id)->orderBy('version')->get()
                ->map(function($event) use($finance) {
                    $row=['action'=>$event->action,'state'=>$event->new_state,'created_at'=>$event->created_at,'version'=>(int)$event->version];
                    if ($finance) { $row['actor_id']=$event->actor_id; $row['reason']=json_decode($event->evidence,true)['reason']??''; }
                    return $row;
                })->all();
            if ($scope->has($actor,$a,$w->kind,'evidence.view')) {
                $data['attachments']=DB::table('manual_financial_attachments')->where('workflow_id',$w->id)->get(['id','mime','bytes','sha256','created_at'])->all();
            }
        }
        return $data;
    }
}
