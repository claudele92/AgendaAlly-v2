<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

use App\Models\User;
use App\Services\PaymentAccounting\{AccountingEffects,AllocationBalances,AllocationWriter,ExactMoney,FinancialOperations};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Human external execution is never performed here. All commands own their SQL boundary. */
final class WorkflowService
{
    private FinanceScope $scope;
    public function __construct() { $this->scope = new FinanceScope; }

    public function request(User $actor, array $input): array
    {
        return $this->owned((int)$input['allocation_id'], $actor, 'request',
            $input['kind'].':'.$input['allocation_id'], $input, function(object $a, string $command) use ($actor,$input): array {
                [$amount,$context,$beneficiary,$policy,$executor] = (new Eligibility)->request($actor,$a,$input);
                $this->method($input);
                if (DB::table('payment_financial_operations')->where('allocation_id',$a->id)
                    ->where('kind',$input['kind'])->where('request_key',$input['command_key'])->exists()) {
                    throw new \DomainException('An existing non-manual reservation cannot be adopted or reclassified.');
                }
                $operation = (new FinancialOperations)->reserve((int)$a->id,$input['kind'],$input['command_key'],
                    $amount,(int)$actor->id,$context ? (int)$context->id : null);
                if (DB::table('manual_financial_workflows')->where('operation_id',$operation->id)->exists()) {
                    throw new \DomainException('Canonical operation is already bound to a manual workflow.');
                }
                $id = (string)Str::uuid();
                $binding = ['allocation'=>$a->id,'operation'=>$operation->id,'context'=>$context?->id,
                    'revision'=>$operation->revision_id,'payment'=>$operation->original_payment_id,
                    'beneficiary'=>$beneficiary,'amount'=>(string)$amount,'currency'=>$a->currency_code,
                    'scale'=>$a->money_scale,'method'=>$input['method'],'institution'=>$input['institution'],
                    'destination'=>$input['destination_mask'],'executor'=>$executor,'policy'=>$policy];
                $this->insert('manual_financial_workflows',[
                    'id'=>$id,'operation_id'=>$operation->id,'allocation_id'=>$a->id,'requester_id'=>$actor->id,
                    'beneficiary_id'=>$beneficiary,'shop_id'=>$a->shop_id,'country_id'=>$a->country_id,
                    'kind'=>$input['kind'],'amount_units'=>$amount,'currency_code'=>$a->currency_code,
                    'money_scale'=>$a->money_scale,'state'=>'REQUESTED','version'=>0,
                    'method'=>$input['method'],'institution'=>$input['institution'],'destination_mask'=>$input['destination_mask'],
                    'destination_fingerprint'=>$this->destination($beneficiary,$input),
                    'execution_scope'=>$executor,'policy_snapshot'=>$this->json($policy),
                    'snapshot_digest'=>hash('sha256',$this->json($binding)),'requested_at'=>now()->utc(),
                ]);
                return [$id,null,'REQUESTED',0,'original_source_request',[]];
            });
    }

    public function action(User $actor, string $id, string $action, array $input): array
    {
        $binding = DB::table('manual_financial_workflows')->where('id',$id)->first();
        if (!$binding) abort(404);
        return $this->owned((int)$binding->allocation_id,$actor,$action,$id,$input,
            function(object $a,string $command) use ($actor,$id,$action,$input): array {
                $w = DB::table('manual_financial_workflows')->where('id',$id)->lockForUpdate()->first();
                $op = DB::table('payment_financial_operations')->where('id',$w->operation_id)->lockForUpdate()->first();
                if ((int)$w->version !== (int)$input['version']) abort(409,'Stale workflow; refresh before a new intent.');
                if (in_array($w->state,['COMPLETED','CANCELLED','REJECTED'],true)) abort(409,'Terminal financial workflow cannot be changed.');
                (new Eligibility)->source($a);
                $this->held($w,$op);
                $next = $w->state; $change = []; $proof = [];
                switch ($action) {
                    case 'approve':
                        $this->scope->require($actor,$a,$w->kind,'approve');
                        $this->state($w,'REQUESTED'); $this->reason($input);
                        $this->revalidate($a,$w,$op);
                        $next='APPROVED'; $change=['approved_by'=>$actor->id,'approved_at'=>now()->utc()];
                        break;
                    case 'reject':
                        $this->scope->require($actor,$a,$w->kind,'approve');
                        $this->state($w,'REQUESTED'); $this->reason($input); $next='REJECTED';
                        $this->release($op,'FAILURE');
                        break;
                    case 'cancel':
                        $this->reason($input);
                        if ($w->state==='REQUESTED') {
                            if ((int)$w->requester_id!==(int)$actor->id) $this->scope->require($actor,$a,$w->kind,'approve');
                        } else {
                            $this->state($w,'APPROVED'); $this->scope->require($actor,$a,$w->kind,'approve');
                            if (($input['no_execution_attested']??false)!==true) throw new \DomainException('Affirmative no-execution attestation required.');
                            $proof=['no_execution_attested'=>true];
                        }
                        if ($w->claim_id || $op->claimed_at) throw new \DomainException('Claimed or uncertain execution cannot be cancelled.');
                        $next='CANCELLED'; $this->release($op,'CANCELED');
                        break;
                    case 'claim':
                        $this->state($w,'APPROVED'); $this->scope->require($actor,$a,$w->kind,'complete');
                        $this->mandate($actor,$a,$w); $this->revalidate($a,$w,$op);
                        if ($w->claim_id) abort(409,'Execution responsibility is already claimed. Do not pay again.');
                        $claim=(string)Str::uuid(); $stamp=now()->utc();
                        $change=['claim_id'=>$claim,'claim_actor_id'=>$actor->id,'claimed_at'=>$stamp,'attempt'=>(int)$w->attempt+1];
                        $this->operation($op,['state'=>'UNKNOWN','claimed_at'=>$op->claimed_at??$stamp]);
                        $proof=['claim_id'=>$claim,'attempt'=>(int)$w->attempt+1,'claimed_by'=>$actor->id];
                        break;
                    case 'review':
                        $this->state($w,'APPROVED'); $this->reason($input);
                        if (!$w->claim_id) throw new \DomainException('A claimed attempt is required for execution review.');
                        if ((int)$w->claim_actor_id===(int)$actor->id) $this->scope->require($actor,$a,$w->kind,'complete');
                        else $this->scope->require($actor,$a,$w->kind,'reconcile');
                        $next='REQUIRES_REVIEW';
                        break;
                    case 'complete':
                        $this->state($w,'APPROVED'); $this->scope->require($actor,$a,$w->kind,'complete');
                        if ((int)$w->claim_actor_id!==(int)$actor->id) abort(403,'Only the assigned execution operator can record ordinary completion.');
                        $this->mandate($actor,$a,$w); $this->revalidate($a,$w,$op);
                        $proof=$this->evidence($w,$op,$actor,$input['evidence']??[],'SUCCESS');
                        $this->effects($a,$w,$op,$proof); $next='COMPLETED';
                        $change=['completed_by'=>$actor->id,'completed_at'=>now()->utc()];
                        break;
                    case 'reconcile':
                        $this->state($w,'REQUIRES_REVIEW'); $this->scope->require($actor,$a,$w->kind,'reconcile');
                        $this->mandate($actor,$a,$w); $this->reason($input);
                        $target=$input['target']??'';
                        if (!in_array($target,['COMPLETED','APPROVED','REJECTED'],true)) throw new \DomainException('Invalid reconciliation target.');
                        $proof=$this->evidence($w,$op,$actor,$input['evidence']??[],$target==='COMPLETED'?'SUCCESS':'NO_MOVEMENT');
                        $next=$target;
                        if ($target==='COMPLETED') {
                            $this->revalidate($a,$w,$op); $this->effects($a,$w,$op,$proof);
                            $change=['completed_by'=>$actor->id,'completed_at'=>now()->utc()];
                        } elseif ($target==='REJECTED') {
                            $this->release($op,'FAILURE');
                        } else {
                            $this->revalidate($a,$w,$op);
                            // Previous claim and no-movement proof remain in immutable history.
                            // Core UNKNOWN continues to reserve; its original claim time is not reset.
                            $change=['claim_id'=>null,'claim_actor_id'=>null,'claimed_at'=>null,
                                'approved_by'=>$actor->id,'approved_at'=>now()->utc()];
                        }
                        break;
                    default: abort(404);
                }
                if (DB::table('manual_financial_workflows')->where('id',$id)->where('version',$w->version)->where('state',$w->state)
                    ->update($change+['state'=>$next,'version'=>(int)$w->version+1])!==1) {
                    throw new \RuntimeException('Required workflow transition was not saved.');
                }
                $proof += ['reason'=>$input['reason']??''];
                return [$id,$w->state,$next,(int)$w->version+1,
                    'payments.'.($w->kind==='refund'?'refunds':'payouts').'.'.$action,$proof];
            });
    }

    private function owned(int $allocationId,User $actor,string $action,string $scope,array $input,callable $work): array
    {
        $c=DB::connection();
        if ($c->transactionLevel()!==0 || $c->getPdo()->inTransaction()) throw new \DomainException('Manual financial commands require a fresh owned transaction.');
        if (!Str::isUuid($input['command_key']??'')) throw new \DomainException('Durable UUID command identity required.');
        if ($c->getDriverName()==='mysql' && $c->getPdo()->query('SELECT @@transaction_isolation')->fetchColumn()!=='REPEATABLE-READ') {
            throw new \DomainException('Native MySQL REPEATABLE READ required.');
        }
        $digest=hash('sha256',$this->json(['action'=>$action,'scope'=>$scope,'input'=>$input]));
        try { return DB::transaction(function() use($allocationId,$actor,$action,$scope,$input,$digest,$work): array {
            // First authority read follows the parent mutex; never inherit an older RR view.
            $a=(new AllocationWriter)->lock($allocationId);
            if (DB::table('commerce_payment_allocations')->where('id',$a->id)->where('version',$a->version)
                ->update(['version'=>DB::raw('version+1'),'updated_at'=>now()])!==1) throw new \DomainException('Stale original authority.');
            if (!$this->scope->readable($actor,$a,str_starts_with($scope,'refund:')?'refund':
                (str_starts_with($scope,'payout:')?'payout':(DB::table('manual_financial_workflows')->where('id',$scope)->value('kind')??'refund')))) abort(404);
            $old=DB::table('manual_financial_commands')->where('actor_id',$actor->id)->where('command_key',$input['command_key'])->first();
            if ($old) {
                if ($old->payload_digest!==$digest) abort(409,'Command identity cannot change intent.');
                // Replay is read-only, including parent metadata. Roll back our mutex write.
                throw new ReplayedCommand(json_decode($old->result,true,512,JSON_THROW_ON_ERROR));
            }
            $command=(string)Str::uuid();
            [$id,$previous,$next,$version,$authority,$proof]=$work($a,$command);
            $w=DB::table('manual_financial_workflows')->where('id',$id)->first();
            $result=json_decode($this->json((new WorkflowQueries)->safe($actor,$w,false)),true,512,JSON_THROW_ON_ERROR);
            $this->insert('manual_financial_commands',['id'=>$command,'actor_id'=>$actor->id,
                'command_key'=>$input['command_key'],'action'=>$action,'scope'=>$scope,'payload_digest'=>$digest,
                'result'=>$this->json($result),'created_at'=>now()->utc()]);
            $event=(string)Str::uuid();
            $this->insert('manual_financial_events',['id'=>$event,'workflow_id'=>$id,'command_id'=>$command,
                'version'=>$version,'action'=>$action,'previous_state'=>$previous,'new_state'=>$next,
                'actor_id'=>$actor->id,'authority'=>$authority,
                'evidence'=>$this->json($proof+['operation_id'=>$w->operation_id,'allocation_id'=>$a->id,
                    'amount_units'=>(string)$w->amount_units,'currency'=>$w->currency_code,'scale'=>$w->money_scale,
                    'snapshot_digest'=>$w->snapshot_digest]),'created_at'=>now()->utc()]);
            if (in_array($next,['REQUESTED','APPROVED','COMPLETED','REJECTED'],true) && $previous!==$next) {
                $this->insert('manual_financial_notifications',['id'=>(string)Str::uuid(),'event_id'=>$event,
                    'recipient_id'=>$w->beneficiary_id,'template_type'=>$w->kind.'_'.strtolower($next),
                    'state'=>'LIBRARY_ONLY','created_at'=>now()->utc()]);
            }
            return $result;
        },3); } catch (ReplayedCommand $replay) { return $replay->result; }
    }

    private function held(object $w,object $op): void
    {
        if ((int)$op->allocation_id!==(int)$w->allocation_id || $op->kind!==$w->kind
            || (string)$op->amount_units!==(string)$w->amount_units
            || !in_array($op->state,['RESERVED','UNKNOWN','PENDING'],true)) throw new \DomainException('Original reservation is not held.');
    }
    private function state(object $w,string $state): void
    {
        if ($w->state!==$state) abort(409,'Transition is unavailable in the current financial state.');
    }
    private function reason(array $input): void
    {
        if (trim($input['reason']??'')==='') throw new \DomainException('An audit reason is required.');
    }
    private function method(array $i): void
    {
        $policy=config('manual_finance.evidence_methods.'.$i['method']);
        if (!$policy || ($policy['terminal_reference_required']??null)!==true
            || ($policy['reference_scope']??null)!=='institution_method_original_executor') {
            throw new \DomainException('An approved native structured-reference evidence policy is required.');
        }
        if (!in_array($i['method']??'',['bank_transfer','mobile_money'],true)
            || !preg_match('/^[a-z][a-z0-9_-]{1,47}$/D',$i['institution']??'')
            || !preg_match('/^[a-zA-Z0-9 +*._-]{4,80}$/D',$i['destination_mask']??'')
            || !str_contains($i['destination_mask'],'*') || preg_match('/[0-9]{5,}/',$i['destination_mask'])) {
            throw new \DomainException('Approved method, institution classification and masked destination required.');
        }
    }
    private function destination(int $beneficiary,array $i): string
    {
        return hash('sha256',$this->json([$beneficiary,$i['method'],$i['institution'],$i['destination_mask']]));
    }
    private function mandate(User $u,object $a,object $w): void
    {
        if ($w->kind==='refund' && $w->execution_scope!=='platform') $this->scope->require($u,$a,'refund','vendor_direct.execute');
    }
    private function revalidate(object $a,object $w,object $op): void
    {
        $this->held($w,$op);
        if ((new FinancialOperations)->reserved((int)$a->id,$w->kind,$op->context_id)<(int)$w->amount_units) throw new \DomainException('Reservation authority unavailable.');
        $b=(new AllocationBalances)->current((int)$a->id);
        if (!$b['finalized']) throw new \DomainException('Verified finalized economics required.');
        if ($w->kind==='payout') {
            $source=(new Eligibility)->source($a);
            if (($a->payable_type==='booking' && $source->status!=='ended')
                || ($a->payable_type==='order' && ($source->status!=='delivered' || ($source->fulfillment_financial_state??null)!=='settled'))
                || (int)$b['vendor_payable']<(int)$w->amount_units) throw new \DomainException('Original matured payable no longer eligible.');
        } else {
            $ctx=DB::table('payment_collection_contexts')->where('id',$op->context_id)->first();
            $old=(int)DB::table('platform_fee_ledger_entries')->where('allocation_id',$a->id)
                ->where('collection_context_id',$op->context_id)->where('effect_kind','refund_principal')->sum('exact_amount');
            if (!$ctx || $ctx->state!=='confirmed' || !in_array($ctx->collection_mode,['platform','vendor_direct'],true)
                || !$ctx->provider_payment_reference || (int)$ctx->amount-$old<(int)$w->amount_units
                || ($ctx->custody_type==='platform'?'platform':'shop:'.$a->shop_id)!==$w->execution_scope) throw new \DomainException('Original verified collected principal unavailable.');
            (new Eligibility)->assertRefundPolicyHeld($a,$ctx,
                json_decode($w->policy_snapshot,true,512,JSON_THROW_ON_ERROR),(int)$w->amount_units);
        }
    }
    private function evidence(object $w,object $op,User $actor,array $e,string $state): array
    {
        if (!$w->claim_id || !$w->approved_by || !$w->approved_at) throw new \DomainException('Prior approval and execution claim required.');
        if (($e['state']??'')!==$state || ($e['attested']??false)!==true
            || (string)($e['amount_units']??'')!==(string)$w->amount_units || ($e['currency_code']??'')!==$w->currency_code
            || (int)($e['allocation_id']??0)!==(int)$w->allocation_id
            || (int)($e['beneficiary_id']??0)!==(int)$w->beneficiary_id
            || ($e['method']??'')!==$w->method || ($e['institution']??'')!==$w->institution
            || ($e['destination_mask']??'')!==$w->destination_mask) throw new \DomainException('Terminal evidence must exactly match approved original terms.');
        $reference=trim($e['external_reference']??'');
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._:\/-]{2,127}$/D',$reference)) throw new \DomainException('Structured institution execution reference required.');
        try { $time=\Carbon\CarbonImmutable::parse($e['executed_at']??'')->utc(); }
        catch (\Throwable $error) { throw new \DomainException('Actual execution timestamp required.'); }
        if (empty($e['executed_at']) || $time->isFuture() || $time->lt(\Carbon\CarbonImmutable::parse($w->claimed_at)->utc())) {
            throw new \DomainException('Execution evidence must follow the committed claim and not be future-dated.');
        }
        $attachment=null;
        if (config('manual_finance.evidence_methods.'.$w->method.'.attachment_required',true) && empty($e['attachment_id'])) {
            throw new \DomainException('This execution method requires a private receipt.');
        }
        if (!empty($e['attachment_id'])) {
            $attachment=DB::table('manual_financial_attachments')->where('id',$e['attachment_id'])->where('workflow_id',$w->id)->first();
            if (!$attachment) throw new \DomainException('Evidence attachment belongs to another workflow or is unavailable.');
            (new PrivateEvidence)->verify($attachment);
        }
        // Conservative institution/method/original-executor scope across all amounts.
        // NO_MOVEMENT proof is not an external execution and has a separate namespace.
        $identity=hash('sha256',$this->json([$state,$w->method,$w->institution,$w->execution_scope,strtoupper($reference)]));
        if (DB::table('manual_financial_evidence')->where('execution_identity',$identity)->exists()) throw new \DomainException('External reference is already retained.');
        $id=(string)Str::uuid();
        $this->insert('manual_financial_evidence',['id'=>$id,'workflow_id'=>$w->id,'operation_id'=>$op->id,
            'claim_id'=>$w->claim_id,'attempt'=>$w->attempt,'state'=>$state,'amount_units'=>$w->amount_units,
            'currency_code'=>$w->currency_code,'beneficiary_id'=>$w->beneficiary_id,'method'=>$w->method,
            'institution'=>$w->institution,'destination_fingerprint'=>$w->destination_fingerprint,
            'external_reference'=>$reference,'execution_identity'=>$identity,'attachment_id'=>$attachment?->id,
            'document_sha256'=>$attachment?->sha256,'recorded_by'=>$actor->id,
            'executed_at'=>$time,'recorded_at'=>now()->utc(),'attested'=>true]);
        return ['evidence_id'=>$id,'execution_identity'=>$identity,'external_reference'=>$reference,
            'authority'=>'authorized_retained_manual_execution','executed_at'=>$time->toIso8601String()];
    }
    private function effects(object $a,object $w,object $op,array $proof): void
    {
        $proof+=['operation'=>$op->id,'collector_scope'=>$w->execution_scope,'original_payment_id'=>$op->original_payment_id,
            'revision'=>$op->revision_id];
        if ($w->kind==='payout') $group=[['kind'=>'vendor_settlement','amount'=>(int)$w->amount_units,'proof'=>$proof]];
        else {
            $ctx=DB::table('payment_collection_contexts')->where('id',$op->context_id)->first();
            $b=(new AllocationBalances)->current((int)$a->id);
            $entries=DB::table('platform_fee_ledger_entries')->where('allocation_id',$a->id)->get();
            $oldContext=(int)$entries->where('effect_kind','refund_principal')->where('collection_context_id',$ctx->id)->sum('exact_amount');
            $commission=ExactMoney::portion((int)$a->commission_amount,$b['refunded']+(int)$w->amount_units,(int)$a->gross_amount)
                -(int)$entries->where('effect_kind','commission_reversal')->sum('exact_amount');
            $adjustment=ExactMoney::portion((int)$ctx->original_adjustment_share,$oldContext+(int)$w->amount_units,(int)$ctx->amount)
                -(int)$entries->where('effect_kind','adjustment_reversal')->where('collection_context_id',$ctx->id)->sum('exact_amount');
            $group=[['kind'=>'refund_principal','context_id'=>(int)$ctx->id,'amount'=>(int)$w->amount_units,'proof'=>$proof]];
            if ($commission>0) $group[]=['kind'=>'commission_reversal','amount'=>$commission,'proof'=>$proof];
            if ($adjustment>0) $group[]=['kind'=>'adjustment_reversal','context_id'=>(int)$ctx->id,'amount'=>$adjustment,'proof'=>$proof];
        }
        (new AccountingEffects)->append((int)$a->id,$op->id,$group);
        $saved=DB::table('platform_fee_ledger_entries')->where('allocation_id',$a->id)->where('event_group_key',$op->id)->get();
        if ($saved->count()!==count($group)) throw new \RuntimeException('Required completion effect group was not saved.');
        foreach ($group as $effect) {
            $row=$saved->firstWhere('effect_kind',$effect['kind']);
            if (!$row || (int)$row->exact_amount!==$effect['amount']
                || ($row->collection_context_id??null)!=($effect['context_id']??null)) throw new \RuntimeException('Required completion effect was not saved exactly.');
        }
        $this->operation($op,['state'=>'SUCCESS','external_reference'=>'manual:'.$proof['execution_identity'],'completed_at'=>now()->utc()]);
    }
    private function release(object $op,string $state): void { $this->operation($op,['state'=>$state,'completed_at'=>now()->utc()]); }
    private function operation(object $op,array $data): void
    {
        if (DB::table('payment_financial_operations')->where('id',$op->id)->where('version',$op->version)
            ->whereIn('state',['RESERVED','UNKNOWN','PENDING'])->update($data+['version'=>(int)$op->version+1,'updated_at'=>now()])!==1) {
            throw new \RuntimeException('Required reservation transition was not saved.');
        }
    }
    private function insert(string $table,array $data): void
    {
        if (!DB::table($table)->insert($data) || !DB::table($table)->where('id',$data['id'])->exists()) throw new \RuntimeException('Required manual financial record was not saved.');
    }
    private function json(mixed $data): string
    {
        $sort=function($value) use(&$sort) {
            if (!is_array($value)) return $value;
            if (!array_is_list($value)) ksort($value);
            return array_map($sort,$value);
        };
        return json_encode($sort($data),JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
    }
}
