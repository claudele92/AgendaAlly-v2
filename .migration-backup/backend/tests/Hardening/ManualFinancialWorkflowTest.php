<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\User;
use App\Services\ManualFinance\{ManualSchema,WorkflowService,WorkflowQueries,FinanceScope,Eligibility};
use App\Services\PaymentAccounting\{AllocationBalances,FinancialOperations};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema,Http};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ManualFinancialWorkflowTest extends ManualFinancialWorkflowFixture
{
    public function test_distinct_refund_intents_share_nonzero_fee_policy_cap(): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$id,$c]=$this->funded();
        $first=$this->policyRequest($id,$c);
        $w=$this->manual->request($this->customer,$first);
        self::assertSame(5000,(new FinancialOperations)->reserved($id,'refund',$c));
        $replay=$this->digest();
        self::assertSame($w,$this->manual->request($this->customer,$first));
        self::assertSame($replay,$this->digest());
        $other=$this->policyRequest($id,$c);
        $this->denies(fn()=>$this->manual->request($this->customer,$other));
        $this->denies(fn()=>(new Eligibility)->refund(DB::table('commerce_payment_allocations')->find($id),$c));
        $done=$this->finishPolicyRefund($w);
        self::assertSame('COMPLETED',$done['state']);
        self::assertSame(5000,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(0,(new FinancialOperations)->reserved($id,'refund',$c));
        $this->denies(fn()=>$this->manual->request($this->customer,$other));
        self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('effect_kind','refund_principal')->count());
    }

    public function test_live_frozen_policy_cannot_be_expanded_by_a_later_quote(): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$id,$c]=$this->funded();
        $w=$this->manual->request($this->customer,$this->policyRequest($id,$c));
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'0']);
        $this->denies(fn()=>$this->manual->request($this->customer,$this->policyRequest($id,$c)));
        $this->finishPolicyRefund($w);
        [$amount]=(new Eligibility)->refund(DB::table('commerce_payment_allocations')->find($id),$c);
        self::assertSame(5000,$amount);
        $next=$this->manual->request($this->customer,$this->policyRequest($id,$c));
        $this->finishPolicyRefund($next);
        self::assertSame(10000,(new AllocationBalances)->current($id)['refunded']);
    }

    public function test_frozen_policy_is_not_recalculated_at_later_execution(): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$id,$c]=$this->funded();
        $w=$this->manual->request($this->customer,$this->policyRequest($id,$c));
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'100']);
        DB::table('settings')->where('key','booking_refund_canceled_hour')->update(['value'=>'0']);
        $this->finishPolicyRefund($w);
        self::assertSame(5000,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(5000,json_decode(DB::table('manual_financial_workflows')->value('policy_snapshot'),true)['fee_basis_points']);
    }

    public static function policyHoldStates(): array
    {
        return [['RESERVED'],['UNKNOWN'],['PENDING']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('policyHoldStates')]
    public function test_all_core_hold_states_reduce_policy_quote(string $state): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$id,$c]=$this->funded();
        $op=(new FinancialOperations)->reserveOwned($id,'refund',(string)Str::uuid(),1000,(int)$this->finance->id,$c);
        DB::table('payment_financial_operations')->where('id',$op->id)->update(['state'=>$state]);
        [$amount]=(new Eligibility)->refund(DB::table('commerce_payment_allocations')->find($id),$c);
        self::assertSame(4000,$amount);
        $this->denies(fn()=>$this->manual->request($this->customer,$this->policyRequest($id,$c)));
        $this->manual->request($this->customer,$this->policyRequest($id,$c,'4000'));
        self::assertSame(5000,(new FinancialOperations)->reserved($id,'refund',$c));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('policyForwardBoundaries')]
    public function test_policy_cap_revalidated_at_every_forward_boundary(string $boundary): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$id,$c]=$this->funded();
        $this->assertPolicyForwardBoundary($boundary,$id,$c);
    }

    public function test_no_movement_rejection_can_release_an_overheld_policy_without_refunding(): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$id,$c]=$this->funded();
        $w=$this->manual->request($this->customer,$this->policyRequest($id,$c));
        $w=$this->manual->action($this->finance,$w['id'],'approve',$this->command($w));
        $w=$this->manual->action($this->finance,$w['id'],'claim',$this->command($w));
        $w=$this->manual->action($this->finance,$w['id'],'review',$this->command($w));
        (new FinancialOperations)->reserveOwned($id,'refund',(string)Str::uuid(),5000,(int)$this->finance->id,$c);
        $result=$this->manual->action($this->finance,$w['id'],'reconcile',$this->command($w)+[
            'target'=>'REJECTED','evidence'=>$this->terminalEvidence($w,'NO_MOVEMENT')]);
        self::assertSame('REJECTED',$result['state']);
        self::assertSame(0,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(5000,(new FinancialOperations)->reserved($id,'refund',$c));
    }

    public function test_completed_refunds_reduce_a_later_policy_budget(): void
    {
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'90']);
        [$id,$c]=$this->funded();
        $w=$this->manual->request($this->customer,$this->policyRequest($id,$c,'1000'));
        $this->finishPolicyRefund($w);
        DB::table('settings')->where('key','booking_canceled_commission')->update(['value'=>'50']);
        [$amount]=(new Eligibility)->refund(DB::table('commerce_payment_allocations')->find($id),$c);
        self::assertSame(4000,$amount);
        $w=$this->manual->request($this->customer,$this->policyRequest($id,$c,'4000'));
        $this->finishPolicyRefund($w);
        self::assertSame(5000,(new AllocationBalances)->current($id)['refunded']);
        $this->denies(fn()=>$this->manual->request($this->customer,$this->policyRequest($id,$c)));
    }

    public function test_customer_refund_request_reserves_once_and_changed_intent_conflicts(): void
    {
        [$id,$c]=$this->funded(); $input=$this->requestInput($id,$c);
        $w=$this->manual->request($this->customer,$input); $before=$this->digest();
        self::assertSame($w,$this->manual->request($this->customer,$input));
        self::assertSame($before,$this->digest()); self::assertSame('REQUESTED',$w['state']);
        self::assertSame(10000,(new FinancialOperations)->reserved($id,'refund',$c));
        $input['destination_mask']='****9999';
        $this->denies(fn()=>$this->manual->request($this->customer,$input),409);
        self::assertSame(0,(new AllocationBalances)->current($id)['refunded']);
    }
    public function test_approval_and_claim_hold_authority_without_money_effect(): void
    {
        $w=$this->claimed(); self::assertSame('APPROVED',$w['state']);
        self::assertSame(10000,(new FinancialOperations)->reserved($w['allocation_id'],'refund'));
        self::assertSame(0,(new AllocationBalances)->current($w['allocation_id'])['refunded']);
        self::assertSame('UNKNOWN',DB::table('payment_financial_operations')->where('id',$w['operation_id'])->value('state'));
        self::assertSame(0,DB::table('manual_financial_evidence')->count());
        self::assertSame(3,DB::table('manual_financial_events')->count());
    }
    public function test_refund_completion_and_replay_have_one_effect_and_event_group(): void
    {
        $w=$this->claimed(); $command=$this->command($w)+['evidence'=>$this->terminalEvidence($w)];
        $done=$this->manual->action($this->finance,$w['id'],'complete',$command); $before=$this->digest();
        self::assertSame('COMPLETED',$done['state']); self::assertSame($done,$this->manual->action($this->finance,$w['id'],'complete',$command));
        self::assertSame($before,$this->digest()); self::assertSame(10000,(new AllocationBalances)->current($w['allocation_id'])['refunded']);
        self::assertSame(0,(new FinancialOperations)->reserved($w['allocation_id'],'refund'));
        self::assertSame(1,DB::table('manual_financial_evidence')->count());
        self::assertSame(4,DB::table('manual_financial_events')->count());
        self::assertSame(3,DB::table('manual_financial_notifications')->count());
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'approve',$this->command($done)),409);
        Http::assertNothingSent();
    }
    public function test_vendor_payout_completes_matured_payable_not_wallet_balance(): void
    {
        $w=$this->claimed('payout');
        self::assertSame(9000,(new AllocationBalances)->current($w['allocation_id'])['vendor_payable']);
        self::assertSame(9000,(new FinancialOperations)->reserved($w['allocation_id'],'payout'));
        $done=$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)+['evidence'=>$this->terminalEvidence($w)]);
        self::assertSame('COMPLETED',$done['state']); self::assertSame(0,(new AllocationBalances)->current($w['allocation_id'])['vendor_payable']);
        self::assertSame(1,DB::table('platform_fee_ledger_entries')->where('effect_kind','vendor_settlement')->count());
    }
    public function test_uncertain_execution_cannot_cancel_or_claim_again_and_reconciles_once(): void
    {
        $w=$this->claimed(); $review=$this->manual->action($this->finance,$w['id'],'review',$this->command($w));
        self::assertSame('REQUIRES_REVIEW',$review['state']);
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'cancel',$this->command($review)),409);
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'claim',$this->command($review)),409);
        $done=$this->manual->action($this->finance,$w['id'],'reconcile',$this->command($review)+['target'=>'COMPLETED','evidence'=>$this->terminalEvidence($review)]);
        self::assertSame('COMPLETED',$done['state']);
    }
    public function test_proven_no_movement_reauthorizes_explicitly_without_erasing_attempt(): void
    {
        $w=$this->claimed(); $review=$this->manual->action($this->finance,$w['id'],'review',$this->command($w));
        $approved=$this->manual->action($this->finance,$w['id'],'reconcile',$this->command($review)+['target'=>'APPROVED','evidence'=>$this->terminalEvidence($review,'NO_MOVEMENT')]);
        self::assertSame('APPROVED',$approved['state']); self::assertNull($approved['claimed_at']);
        self::assertSame(10000,(new FinancialOperations)->reserved($w['allocation_id'],'refund'));
        $claimed=$this->manual->action($this->finance,$w['id'],'claim',$this->command($approved));
        self::assertSame(2,DB::table('manual_financial_workflows')->where('id',$w['id'])->value('attempt'));
        self::assertSame(1,DB::table('manual_financial_evidence')->count());
        self::assertNotNull($claimed['claimed_at']);
    }
    public function test_requested_cancel_and_reject_release_authority_not_refund(): void
    {
        [$id,$c]=$this->funded();
        foreach (['cancel','reject'] as $action) {
            $w=$this->manual->request($this->customer,$this->requestInput($id,$c));
            $done=$this->manual->action($action==='cancel'?$this->customer:$this->finance,$w['id'],$action,$this->command($w));
            self::assertSame($action==='cancel'?'CANCELLED':'REJECTED',$done['state']);
            self::assertSame(0,(new FinancialOperations)->reserved($id,'refund'));
            self::assertSame(0,(new AllocationBalances)->current($id)['refunded']);
        }
    }
    public static function badRequestCases(): array
    {
        return [['amount_units','9999'],['currency_code','EUR'],['destination_mask','1234567890123456'],
            ['method','wallet'],['institution','unsafe institution'],['context_id',999]];
    }
    /** @dataProvider badRequestCases */
    public function test_request_mismatch_is_denied(string $field,mixed $value): void
    {
        [$id,$c]=$this->funded(); $i=$this->requestInput($id,$c); $i[$field]=$value;
        $this->denies(fn()=>$this->manual->request($this->customer,$i));
    }
    public static function badEvidenceCases(): array
    {
        return [['amount_units','9999'],['currency_code','EUR'],['beneficiary_id',1],['allocation_id',999],
            ['state','PENDING'],['attested',false],['destination_mask','****7777'],['method','mobile_money'],
            ['institution','another_bank'],['external_reference',''],['executed_at','2099-01-01T00:00:00Z'],['attachment_id','aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa']];
    }
    /** @dataProvider badEvidenceCases */
    public function test_bad_terminal_evidence_rolls_back_everything(string $field,mixed $value): void
    {
        $w=$this->claimed(); $e=$this->terminalEvidence($w); $e[$field]=$value;
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)+['evidence'=>$e]));
    }
    public function test_completion_requires_approval_claim_and_evidence(): void
    {
        [$id,$c]=$this->funded(); $w=$this->manual->request($this->customer,$this->requestInput($id,$c));
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)),409);
        $w=$this->manual->action($this->finance,$w['id'],'approve',$this->command($w));
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)),403);
        $w=$this->manual->action($this->finance,$w['id'],'claim',$this->command($w));
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)));
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'cancel',$this->command($w)+['no_execution_attested'=>true]));
    }
    public function test_foreign_customer_specialist_and_ordinary_admin_have_no_finance_authority(): void
    {
        [$id,$c]=$this->funded(); $i=$this->requestInput($id,$c);
        $this->denies(fn()=>$this->manual->request($this->vendor,$i),404);
        $this->denies(fn()=>$this->manual->request($this->customer,$this->requestInput($id,$c,'payout')),404);
        $w=$this->manual->request($this->customer,$i);
        DB::table('model_has_permissions')->delete();
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'approve',$this->command($w)),404);
    }
    public function test_completion_grant_does_not_follow_approval_and_private_summary_has_no_notes(): void
    {
        $w=$this->approved();
        $pid=DB::table('permissions')->where('name','payments.refunds.complete')->value('id');
        DB::table('model_has_permissions')->where('permission_id',$pid)->delete();
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'claim',$this->command($w)),403);
        $safe=(new WorkflowQueries)->safe($this->customer,DB::table('manual_financial_workflows')->where('id',$w['id'])->first());
        self::assertArrayNotHasKey('attachments',$safe); self::assertArrayNotHasKey('policy_snapshot',$safe);
        self::assertArrayNotHasKey('reason',$safe['events'][1]); self::assertArrayNotHasKey('snapshot_digest',$safe);
    }
    public static function offlineRefundCases(): array { return [['internal'],['offline']]; }
    /** @dataProvider offlineRefundCases */
    public function test_wallet_and_cash_selection_cannot_become_external_refund_authority(string $mode): void
    {
        $id=$this->writer->commit($this->quote());
        $e=$this->evidence($mode,10000,$mode==='internal'?'wallet_contribution':'selected_method');
        $context=$this->writer->stage($id,$e);
        $this->writer->confirm([$context],'synthetic-offline-receipt',10000);
        $this->denies(fn()=>$this->manual->request($this->customer,$this->requestInput($id,$context)));
    }
    public function test_uncollected_source_has_no_refund_or_matured_payout(): void
    {
        [$id,$c]=$this->funded('platform','paystack',true,false);
        $this->denies(fn()=>$this->manual->request($this->customer,$this->requestInput($id,$c)));
        $this->denies(fn()=>$this->manual->request($this->vendor,$this->requestInput($id,$c,'payout')));
    }
    public function test_reservation_competition_and_legacy_mutation_bridge_are_denied(): void
    {
        [$id,$c]=$this->funded(); $w=$this->manual->request($this->vendor,$this->requestInput($id,$c,'payout'));
        $this->denies(fn()=>$this->manual->request($this->customer,$this->requestInput($id,$c)));
        $this->denies(fn()=>(new FinancialOperations)->cancel($w['operation_id']));
        $this->denies(fn()=>(new FinancialOperations)->refund($w['operation_id']));
    }
    public static function requestFaultTables(): array { return [['payment_financial_operations'],['manual_financial_workflows'],['manual_financial_commands'],['manual_financial_events'],['manual_financial_notifications']]; }
    /** @dataProvider requestFaultTables */
    public function test_required_request_write_failure_has_exact_rollback(string $table): void
    {
        [$id,$c]=$this->funded();
        DB::unprepared("CREATE TRIGGER injected_write BEFORE INSERT ON $table BEGIN SELECT RAISE(ABORT,'Synthetic required failure'); END");
        $this->denies(fn()=>$this->manual->request($this->customer,$this->requestInput($id,$c)));
    }
    public static function completeFaultTables(): array { return [['manual_financial_evidence','INSERT'],['platform_fee_ledger_entries','INSERT'],['payment_financial_operations','UPDATE'],['manual_financial_workflows','UPDATE'],['manual_financial_commands','INSERT'],['manual_financial_events','INSERT'],['manual_financial_notifications','INSERT']]; }
    /** @dataProvider completeFaultTables */
    public function test_required_completion_write_failure_has_exact_rollback(string $table,string $verb): void
    {
        $w=$this->claimed();
        DB::unprepared("CREATE TRIGGER injected_write BEFORE $verb ON $table BEGIN SELECT RAISE(ABORT,'Synthetic required failure'); END");
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)+['evidence'=>$this->terminalEvidence($w)]));
    }
    public function test_silently_ignored_effect_cannot_report_completion(): void
    {
        $w=$this->claimed();
        DB::unprepared("CREATE TRIGGER injected_ignore BEFORE INSERT ON platform_fee_ledger_entries BEGIN SELECT RAISE(IGNORE); END");
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],'complete',$this->command($w)+['evidence'=>$this->terminalEvidence($w)]));
    }
    public function test_command_and_audit_history_are_immutable(): void
    {
        $w=$this->approved();
        $this->denies(fn()=>DB::table('manual_financial_events')->update(['new_state'=>'COMPLETED']));
        $this->denies(fn()=>DB::table('manual_financial_commands')->delete());
        $this->denies(fn()=>DB::table('manual_financial_workflows')->where('id',$w['id'])->update(['amount_units'=>1]));
    }
    public function test_owned_boundary_rejects_inherited_transaction(): void
    {
        [$id,$c]=$this->funded();
        DB::beginTransaction();
        try { $this->denies(fn()=>$this->manual->request($this->customer,$this->requestInput($id,$c))); }
        finally { DB::rollBack(); }
    }
    public function test_country_scoped_grant_requires_whole_shop_footprint_and_distinct_action(): void
    {
        [$id]=$this->funded();
        $a=DB::table('commerce_payment_allocations')->find($id);
        Schema::create('shop_locations',function(Blueprint $t) { $t->id(); $t->unsignedBigInteger('shop_id'); $t->unsignedBigInteger('country_id')->nullable(); });
        DB::table('shop_locations')->insert(['shop_id'=>1,'country_id'=>1]);
        DB::table('country_roles')->insert(['id'=>1,'country_id'=>1]);
        DB::table('country_permissions')->insert(['id'=>1,'key'=>'payments.refunds.approve']);
        DB::table('country_role_permissions')->insert(['country_role_id'=>1,'country_permission_id'=>1]);
        DB::table('country_invitations')->insert(['user_id'=>3,'country_id'=>1,'country_role_id'=>1,'status'=>'accepted']);
        $this->finance->setRelation('countryAdmin',(object)['country_id'=>1]);
        DB::table('model_has_permissions')->delete();
        $scope=new FinanceScope;
        self::assertTrue($scope->has($this->finance,$a,'refund','approve'));
        self::assertFalse($scope->has($this->finance,$a,'refund','complete'));
        self::assertFalse($scope->has($this->finance,$a,'refund','evidence.view'));
        DB::table('shop_locations')->insert(['shop_id'=>1,'country_id'=>2]);
        self::assertFalse($scope->has($this->finance,$a,'refund','approve'));
        DB::table('shop_locations')->where('country_id',2)->update(['country_id'=>null]);
        self::assertFalse($scope->has($this->finance,$a,'refund','approve'));
    }
    public function test_external_reference_cannot_be_reused_for_another_completion(): void
    {
        $w=$this->claimed();
        $proof=$this->terminalEvidence($w);
        $this->manual->action($this->finance,$w['id'],'complete',$this->command($w)+['evidence'=>$proof]);
        $this->checkout=(string)Str::uuid();
        DB::table('bookings')->insert(['id'=>2,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,'total_price'=>100,'status'=>'ended','start_date'=>now()->subHour()]);
        [$id,$c]=$this->funded(sourceId:2);
        $next=$this->manual->request($this->customer,$this->requestInput($id,$c));
        $next=$this->manual->action($this->finance,$next['id'],'approve',$this->command($next));
        $next=$this->manual->action($this->finance,$next['id'],'claim',$this->command($next));
        $this->denies(fn()=>$this->manual->action($this->finance,$next['id'],'complete',
            $this->command($next)+['evidence'=>array_replace($this->terminalEvidence($next),['external_reference'=>strtoupper($proof['external_reference'])])]));
        self::assertSame(1,DB::table('manual_financial_evidence')->count());
    }
    public function test_private_receipt_hash_binding_and_fresh_view_grant(): void
    {
        $w=$this->claimed();
        $record=DB::table('manual_financial_workflows')->find($w['id']);
        $root=sys_get_temp_dir().'/manual-receipt-'.Str::uuid();
        config(['filesystems.disks.local'=>['driver'=>'local','root'=>$root,'throw'=>true]]);
        $this->app->singleton('filesystem',fn($app)=>new \Illuminate\Filesystem\FilesystemManager($app));
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('filesystem');
        $routes=new \Illuminate\Routing\RouteCollection;
        $routes->add((new \Illuminate\Routing\Route('GET','evidence/{workflow}/{attachment}',fn()=>null))->name('manual-finance.evidence'));
        $url=new \Illuminate\Routing\UrlGenerator($routes,\Illuminate\Http\Request::create('http://localhost'));
        $url->setKeyResolver(fn()=>'synthetic-disposable-signing-key');
        $this->app->instance('url',$url);
        $this->app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class,new \Illuminate\Routing\ResponseFactory(
            \Mockery::mock(\Illuminate\Contracts\View\Factory::class),new \Illuminate\Routing\Redirector($url)));
        $service=new \App\Services\ManualFinance\PrivateEvidence;
        $path=tempnam(sys_get_temp_dir(),'receipt-');
        file_put_contents($path,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j9V8AAAAASUVORK5CYII='));
        try {
            $file=new \Illuminate\Http\UploadedFile($path,'receipt.png','image/png',null,true);
            $saved=$service->upload($this->finance,$record,$file);
            $attachment=DB::table('manual_financial_attachments')->find($saved['id']);
            self::assertSame(hash_file('sha256',$path),$attachment->sha256);
            $this->denies(fn()=>$service->link($this->customer,$record,$attachment),403);
            $link=$service->link($this->finance,$record,$attachment);
            self::assertStringContainsString('actor=3',$link);
            self::assertTrue($url->hasValidSignature(\Illuminate\Http\Request::create('http://localhost'.$link),false));
            $response=$service->download($this->finance,$record,$attachment);
            self::assertSame('nosniff',$response->headers->get('X-Content-Type-Options'));
            self::assertStringContainsString('attachment;',$response->headers->get('Content-Disposition'));
            DB::table('model_has_permissions')->where('permission_id',6)->delete();
            $this->denies(fn()=>$service->download($this->finance,$record,$attachment),403);
            \Illuminate\Support\Facades\Storage::disk('local')->put($attachment->path,'tampered');
            $this->denies(fn()=>$service->verify($attachment));
        } finally {
            @unlink($path);
            foreach (glob($root.'/manual-finance/*')?:[] as $stored) unlink($stored);
            @rmdir($root.'/manual-finance'); @rmdir($root);
        }
    }
    public function test_financial_notification_wording_invariants_and_library_only_definitions(): void
    {
        $this->app->instance('files',new \Illuminate\Filesystem\Filesystem);
        foreach (\App\Support\FinancialEmailTemplates::definitions() as $type=>$content) {
            \App\Support\FinancialEmailTemplates::validate($content+['type'=>$type]);
            self::assertNotEmpty($content['body']);
        }
        foreach (['refund_requested','refund_approved','payout_rejected'] as $type) {
            try {
                \App\Support\FinancialEmailTemplates::validate(['type'=>$type,'subject'=>'Money paid','body'=>'Your payout was completed']);
                self::fail('False financial completion wording accepted.');
            } catch (\Illuminate\Validation\ValidationException $e) { self::assertArrayHasKey('body',$e->errors()); }
        }
        self::assertFalse(config('manual_finance.smtp_enabled',false));
    }
    public function test_specialist_role_and_wallet_display_cannot_reserve_platform_vendor_payable(): void
    {
        [$id,$context]=$this->funded();
        DB::table('users')->insert(['id'=>4]);
        $specialist=$this->actor(4,'master');
        $specialist->setRelation('wallet',(object)['price'=>9999999]);
        $this->denies(fn()=>$this->manual->request($specialist,$this->requestInput($id,$context,'payout')),404);
        self::assertSame(0,DB::table('manual_financial_workflows')->count());
        self::assertSame(0,(new FinancialOperations)->reserved($id,'payout'));
        self::assertSame(9000,(new AllocationBalances)->current($id)['vendor_payable']);
    }
}
