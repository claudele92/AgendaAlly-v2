<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\PaymentProcess;
use App\Models\PlatformPaymentConfig;
use App\Services\PaymentService\MtnAttempt;
use App\Services\PaymentAccounting\NativeQuoteFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

abstract class MtnDurableFixture extends PaymentNativeCheckoutFixture
{
    protected MtnDurableSyntheticService $mtn;
    protected array $input;
    protected string $providerStatus='SUCCESSFUL';
    protected string $dispatch='accepted';
    protected int $posts=0;
    protected array $observed=[];
    protected bool $lookupUnavailable=false;
    protected ?string $wrongProof=null;
    protected ?string $independentFile=null;

    protected function prepareMtn(string $type='booking',int $wallet=0): void
    {
        $this->app->instance('encrypter',new \Illuminate\Encryption\Encrypter(str_repeat('s',32),'AES-256-CBC'));
        if ($type==='cart') {
            $this->cart(); DB::table('products')->update(['digital'=>1]);
        } else (new NativeQuoteFactory)->commitNew($this->booking());
        $config=(new PlatformPaymentConfig)->forceFill([
            'id'=>1,'payment_id'=>3,'country_id'=>1,'currency'=>'XAF','api_user'=>'synthetic-user',
            'api_key'=>'synthetic-key','subscription_key'=>'synthetic-subscription',
            'target_environment'=>'synthetic','base_url'=>'https://synthetic.invalid',
            'updated_at'=>'2026-10-03 12:00:00',
        ]);
        $this->mtn=new MtnDurableSyntheticService($config);
        $this->input=[($type==='cart'?'cart_id':'booking_id')=>1,
            'payment_id'=>3,'user_id'=>2,'delivery_type'=>\App\Models\Order::DIGITAL,
            'from_wallet_price'=>$wallet,'phone'=>'synthetic-payer'];
        // Fresh factory prevents accumulating earlier fake stubs.
        $factory=new \Illuminate\Http\Client\Factory;
        $factory->preventStrayRequests();
        $this->app->instance(\Illuminate\Http\Client\Factory::class,$factory);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance(\Illuminate\Http\Client\Factory::class);
        Http::fake(function ($request) {
            if (str_ends_with($request->url(),'/token/')) return Http::response(['access_token'=>'synthetic-token'],200);
            if ($request->method()==='POST') {
                ++$this->posts;
                $id=$request['externalId'];
                $p=PaymentProcess::findOrFail($id);
                $this->observed[]=['id'=>$id,'state'=>$p->mtn_dispatch_state,'level'=>DB::transactionLevel()];
                self::assertSame(0,DB::transactionLevel());
                self::assertSame(MtnAttempt::UNKNOWN,$p->mtn_dispatch_state);
                self::assertSame($id,DB::table('payment_collection_contexts')->value('provider_payment_reference'));
                if ($this->independentFile) {
                    $reader=new \PDO('sqlite:'.$this->independentFile);
                    $reader->exec('PRAGMA query_only=ON');
                    $q=$reader->prepare('SELECT mtn_dispatch_state FROM payment_process WHERE id=?');
                    $q->execute([$id]);
                    self::assertSame(MtnAttempt::UNKNOWN,$q->fetchColumn()); // independently committed
                }
                if ($this->dispatch==='timeout') throw new \Illuminate\Http\Client\ConnectionException('synthetic timeout');
                return Http::response([], $this->dispatch==='rejection'?500:202);
            }
            if ($this->lookupUnavailable) throw new \Illuminate\Http\Client\ConnectionException('synthetic lookup unavailable');
            $id=basename($request->url()); $p=PaymentProcess::findOrFail($id);
            $r=['status'=>$this->providerStatus,'externalId'=>$id,
                'amount'=>\App\Services\PaymentAccounting\ExactMoney::decimal((int)$p->data['total_price'],2),
                'currency'=>'XAF'];
            if ($this->wrongProof) $r[$this->wrongProof]=$this->wrongProof==='amount'?'99':'wrong';
            return Http::response($r,200);
        });
    }

    protected function noMtnMoney(): void
    {
        self::assertSame(0,DB::table('payment_collection_contexts')->where('provider_tag','mtn')->where('state','confirmed')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }
}

final class MtnDurableAttemptTest extends MtnDurableFixture
{
    public static function targets(): array { return [['cart'],['booking']]; }
    #[\PHPUnit\Framework\Attributes\DataProvider('targets')]
    public function test_committed_dispatch_success_and_repeated_recovery(string $type): void
    {
        $this->prepareMtn($type);
        $p=$this->mtn->processTransaction($this->input);
        self::assertSame(1,$this->posts); $this->noMtnMoney();
        self::assertSame(MtnAttempt::ACCEPTED,$p->mtn_dispatch_state);
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        $a=DB::table('commerce_payment_allocations')->first();
        self::assertSame(10000,(int)$a->gross_amount);
        self::assertSame(1000,(int)$a->commission_amount);
        self::assertSame(10000,(int)$a->original_platform_amount);
        self::assertSame(9000,(int)$a->original_vendor_payable);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        $before=DB::table('payment_collection_contexts')->get()->toJson();
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        self::assertSame($p->id,$this->mtn->processTransaction($this->input)->id);
        self::assertSame($before,DB::table('payment_collection_contexts')->get()->toJson());
        self::assertSame(1,$this->posts);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
    }

    public static function faults(): array
    {
        $result=[];
        foreach (['cart','booking'] as $type) foreach ([
            'before_persistence','after_persistence','identity_committed','dispatch_claimed',
            'response_received','before_accounting','during_accounting','accounting_committed',
        ] as $fault) $result[]=[$type,$fault];
        return $result;
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('faults')]
    public function test_interruption_explicit_recovery(string $type,string $fault): void
    {
        $this->prepareMtn($type); $this->mtn->fault=$fault;
        try { $this->mtn->processTransaction($this->input); }
        catch (\RuntimeException $e) { self::assertStringContainsString('Synthetic',$e->getMessage()); }
        if (in_array($fault,['before_persistence','after_persistence'],true)) {
            self::assertSame(0,PaymentProcess::count()); self::assertSame(0,$this->posts);
            self::assertSame(0,DB::table('payment_collection_contexts')->count());
            $this->mtn->fault=null;
            $this->mtn->processTransaction($this->input);
            self::assertSame(1,$this->posts);
            return;
        }
        $p=PaymentProcess::firstOrFail();
        if ($fault==='identity_committed') {
            self::assertSame(MtnAttempt::RESERVED,$p->mtn_dispatch_state);
            self::assertSame(0,$this->posts);
            $this->mtn->fault=null;
            self::assertSame($p->id,$this->mtn->processTransaction($this->input)->id);
            self::assertSame(1,$this->posts);
        } elseif ($fault==='dispatch_claimed') {
            self::assertSame(MtnAttempt::UNKNOWN,$p->mtn_dispatch_state);
            self::assertSame(0,$this->posts);
            $this->mtn->fault=null;
            self::assertSame($p->id,$this->mtn->processTransaction($this->input)->id);
            self::assertSame(0,$this->posts); // conservative unknown, no resubmit
        } elseif ($fault==='response_received') {
            self::assertSame(MtnAttempt::UNKNOWN,$p->mtn_dispatch_state);
            self::assertSame(1,$this->posts); $this->mtn->fault=null;
        } else {
            self::assertFalse($this->mtn->reconcileAttempt($p->id)['status']);
            if ($fault==='accounting_committed') {
                self::assertSame(MtnAttempt::SUCCESS,$p->fresh()->mtn_dispatch_state);
                self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
            } else $this->noMtnMoney();
            $this->mtn->fault=null;
        }
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        self::assertSame(MtnAttempt::SUCCESS,$p->fresh()->mtn_dispatch_state);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(1,PaymentProcess::count());
    }

    public static function outcomes(): array
    { return [['timeout','PENDING'],['timeout','FAILED'],['timeout','UNKNOWN'],['rejection','UNKNOWN'],['accepted','PENDING']]; }
    #[\PHPUnit\Framework\Attributes\DataProvider('outcomes')]
    public function test_unknown_pending_failure_never_implies_funding(string $dispatch,string $status): void
    {
        $this->prepareMtn(); $this->dispatch=$dispatch; $this->providerStatus=$status;
        $p=$this->mtn->processTransaction($this->input); $this->noMtnMoney();
        $r=$this->mtn->reconcileAttempt($p->id); $this->noMtnMoney();
        self::assertSame($status!=='UNKNOWN',$r['status']);
        self::assertSame($status==='FAILED'?MtnAttempt::FAILURE:($status==='PENDING'?MtnAttempt::PENDING:MtnAttempt::UNKNOWN),$p->fresh()->mtn_dispatch_state);
        if ($status!=='FAILED') {
            self::assertSame($p->id,$this->mtn->processTransaction($this->input)->id);
            self::assertSame(1,$this->posts);
            $this->providerStatus='SUCCESSFUL';
            self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        }
    }

    public static function mixed(): array { return [['cart',40],['booking',40]]; }
    #[\PHPUnit\Framework\Attributes\DataProvider('mixed')]
    public function test_mixed_wallet_mtn_replay_debits_wallet_once(string $type,int $wallet): void
    {
        $this->prepareMtn($type,$wallet);
        $p=$this->mtn->processTransaction($this->input);
        self::assertSame(960,(int)DB::table('wallets')->value('price'));
        self::assertSame($p->id,$this->mtn->processTransaction($this->input)->id);
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        self::assertSame(9000,(int)DB::table('commerce_payment_allocations')->value('original_vendor_payable'));
        self::assertSame(1,DB::table('wallet_histories')->count());
        self::assertSame(1,$this->posts);
    }

    public function test_ledger_failure_rolls_back_and_original_identity_recovers(): void
    {
        $this->prepareMtn('cart'); $p=$this->mtn->processTransaction($this->input);
        DB::unprepared('CREATE TRIGGER injected_failure AFTER INSERT ON platform_fee_ledger_entries BEGIN SELECT RAISE(ABORT,"synthetic ledger interruption"); END');
        self::assertFalse($this->mtn->reconcileAttempt($p->id)['status']);
        $this->noMtnMoney(); self::assertSame(0,DB::table('orders')->count());
        DB::unprepared('DROP TRIGGER injected_failure');
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        self::assertSame(1,PaymentProcess::count());
    }

    public function test_config_rotation_missing_lookup_and_outer_transaction_fail_closed(): void
    {
        $this->prepareMtn(); $this->dispatch='timeout'; $p=$this->mtn->processTransaction($this->input);
        $this->mtn->gateway->updated_at='2026-10-04 12:00:00';
        try { $this->mtn->reconcileAttempt($p->id); self::fail(); }
        catch (\DomainException $e) { self::assertStringContainsString('revision',$e->getMessage()); }
        $this->noMtnMoney();
        $this->mtn->gateway->updated_at='2026-10-03 12:00:00';
        $this->mtn->configurationMissing=true;
        try { $this->mtn->reconcileAttempt($p->id); self::fail(); }
        catch (\DomainException $e) { self::assertStringContainsString('no current-config fallback',$e->getMessage()); }
        $this->noMtnMoney();
        DB::beginTransaction();
        try { $this->mtn->processTransaction($this->input); self::fail(); }
        catch (\DomainException $e) { self::assertStringContainsString('outer transaction',$e->getMessage()); }
        finally { DB::rollBack(); }
        self::assertSame(1,$this->posts);
    }

    public function test_database_identity_immutability_and_no_destructive_rollback(): void
    {
        $this->prepareMtn(); $p=$this->mtn->processTransaction($this->input);
        foreach (['id'=>'33333333-3333-4333-8333-333333333333','mtn_funding_event_key'=>'33333333-3333-4333-8333-333333333333',
            'mtn_config_fingerprint'=>str_repeat('b',64),'mtn_anchor_context_id'=>null] as $field=>$value) {
            try { DB::table('payment_process')->where('id',$p->id)->update([$field=>$value]); self::fail(); }
            catch (\Illuminate\Database\QueryException $e) { self::assertNotSame('',$e->getMessage()); }
        }
        try { (require dirname(__DIR__,2).'/database/migrations/2026_10_03_100300_add_mtn_attempt_identity.php')->down(); self::fail(); }
        catch (\RuntimeException $e) { self::assertStringContainsString('rollback refused',$e->getMessage()); }
        self::assertSame($p->id,PaymentProcess::firstOrFail()->id);
    }

    public static function replays(): array
    {
        return array_map(fn($v)=>[$v],['double_click','api_repeat','frontend_retry','worker_retry',
            'timeout_retry','restart','stale_model','replacement_transaction','callback_hint','duplicate_reconcile','delayed_success']);
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('replays')]
    public function test_replay_keeps_one_provider_identity(string $case): void
    {
        $this->prepareMtn(); $this->dispatch='timeout'; $p=$this->mtn->processTransaction($this->input);
        if ($case==='replacement_transaction') {
            \App\Models\Transaction::where('payment_sys_id',3)->delete();
            \App\Models\Booking::findOrFail(1)->createTransaction(['payment_sys_id'=>3,'price'=>100,'status'=>'progress','user_id'=>2]);
        }
        if ($case==='restart') $this->mtn=new MtnDurableSyntheticService($this->mtn->gateway);
        // Caller reference/financial fields are deliberately untrusted.
        $input=$this->input+['mtn_reference_id'=>'customer-reference','mtn_funding_event_key'=>'customer-event'];
        self::assertSame($p->id,$this->mtn->processTransaction($input)->id);
        self::assertSame(1,$this->posts); $this->noMtnMoney();
        $this->providerStatus='PENDING';
        if ($case==='worker_retry') {
            $p->update(['data'=>array_merge($p->data,['requested_at'=>now()->subMinutes(5)->toIso8601String()])]);
            (new \App\Console\Commands\ReconcilePendingMtnPayments)->handle($this->mtn);
        } elseif ($case==='callback_hint') {
            $controller=(new \ReflectionClass(\App\Http\Controllers\API\v1\Dashboard\Payment\MtnController::class))->newInstanceWithoutConstructor();
            (new \ReflectionProperty($controller,'service'))->setValue($controller,$this->mtn);
            (new \ReflectionProperty(\App\Http\Controllers\Controller::class,'language'))->setValue($controller,'en');
            $hint=\Illuminate\Http\Request::create('/webhook','POST',['externalId'=>$p->id,'status'=>'SUCCESSFUL']);
            self::assertTrue($controller->paymentWebHook($hint)->getData(true)['status']);
            $this->noMtnMoney();
        } else self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        $this->providerStatus='SUCCESSFUL';
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        self::assertSame(1,PaymentProcess::count());
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_unavailable_reconciliation_preserves_unknown_then_recovers(): void
    {
        $this->prepareMtn(); $this->dispatch='timeout'; $p=$this->mtn->processTransaction($this->input);
        $this->lookupUnavailable=true;
        try { $this->mtn->reconcileAttempt($p->id); self::fail(); }
        catch (\Illuminate\Http\Client\ConnectionException $e) { self::assertStringContainsString('unavailable',$e->getMessage()); }
        self::assertSame(MtnAttempt::UNKNOWN,$p->fresh()->mtn_dispatch_state);
        self::assertSame($p->id,$this->mtn->processTransaction($this->input)->id);
        $this->noMtnMoney(); $this->lookupUnavailable=false;
        self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        self::assertSame(1,$this->posts);
    }

    public static function wrongProofs(): array { return [['externalId'],['amount'],['currency']]; }
    #[\PHPUnit\Framework\Attributes\DataProvider('wrongProofs')]
    public function test_original_reference_amount_currency_are_required(string $wrong): void
    {
        $this->prepareMtn(); $p=$this->mtn->processTransaction($this->input); $this->wrongProof=$wrong;
        self::assertFalse($this->mtn->reconcileAttempt($p->id)['status']); $this->noMtnMoney();
    }

    public function test_terminal_failure_allows_new_event_without_rewriting_old_attempt(): void
    {
        $this->prepareMtn(); $p=$this->mtn->processTransaction($this->input);
        $this->providerStatus='FAILED'; self::assertTrue($this->mtn->reconcileAttempt($p->id)['status']);
        $next=$this->mtn->processTransaction($this->input);
        self::assertNotSame($p->id,$next->id);
        self::assertNotSame($p->mtn_funding_event_key,$next->mtn_funding_event_key);
        self::assertSame(MtnAttempt::FAILURE,$p->fresh()->mtn_dispatch_state);
        self::assertSame(2,$this->posts);
    }

    public function test_activation_gate_blocks_committed_reserved_resume(): void
    {
        $this->prepareMtn(); $this->mtn->fault='identity_committed';
        try { $this->mtn->processTransaction($this->input); } catch (\RuntimeException) {}
        $this->mtn->fault=null; $this->mtn->eligible=false;
        try { $this->mtn->processTransaction($this->input); self::fail(); }
        catch (\DomainException $e) { self::assertStringContainsString('gate closed',$e->getMessage()); }
        self::assertSame(0,$this->posts); $this->noMtnMoney();
        self::assertSame(MtnAttempt::RESERVED,PaymentProcess::firstOrFail()->mtn_dispatch_state);
    }
}