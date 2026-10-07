<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\User;
use App\Services\ManualFinance\{ManualSchema,WorkflowService,FinanceScope};
use App\Services\PaymentAccounting\{AllocationBalances,FinancialOperations};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema,Http};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/** Shared setup only: SQLite cases must not be inherited by native MySQL tests. */
abstract class ManualFinancialWorkflowFixture extends PaymentCompletionFixture
{
    protected User $customer;
    protected User $finance;
    protected User $vendor;
    protected WorkflowService $manual;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.defaults.guard'=>'web','auth.guards.web'=>['driver'=>'session','provider'=>'users'],
            'auth.providers.users'=>['driver'=>'eloquent','model'=>User::class]]);
        config(['manual_finance'=>require __DIR__.'/../../config/manual_finance.php']);
        Http::preventStrayRequests();
        foreach (['country_admins','country_roles','country_invitations','country_permissions','country_role_permissions',
            'permissions','model_has_permissions','role_has_permissions','model_has_roles'] as $table) {
            Schema::create($table,function(Blueprint $t) use($table) {
                $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->unsignedBigInteger('country_id')->nullable();
                $t->unsignedBigInteger('country_role_id')->nullable(); $t->unsignedBigInteger('country_permission_id')->nullable();
                $t->unsignedBigInteger('model_id')->nullable(); $t->string('model_type')->nullable();
                $t->unsignedBigInteger('permission_id')->nullable(); $t->unsignedBigInteger('role_id')->nullable();
                $t->string('key')->nullable(); $t->string('name')->nullable(); $t->string('guard_name')->nullable();
                $t->string('status')->nullable();
            });
        }
        DB::table('users')->insert(['id'=>3]);
        Schema::table('bookings',function(Blueprint $t) { $t->string('status')->default('ended'); $t->timestamp('start_date')->nullable(); });
        DB::table('bookings')->insert(['id'=>1,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,'total_price'=>100,'status'=>'ended','start_date'=>now()->subHour()]);
        Schema::create('settings',function(Blueprint $t) { $t->id(); $t->string('key'); $t->string('value'); });
        DB::table('settings')->insert([['key'=>'booking_refund_canceled_hour','value'=>'24'],['key'=>'booking_canceled_commission','value'=>'0']]);
        (new ManualSchema)->up();
        $this->customer=$this->actor(2);
        $this->vendor=$this->actor(1,'seller');
        $this->vendor->setRelation('shop',(object)['id'=>1]);
        $this->finance=$this->actor(3,'admin');
        foreach (FinanceScope::keys() as $index=>$key) {
            DB::table('permissions')->insert(['id'=>$index+1,'name'=>$key,'guard_name'=>'web']);
            DB::table('model_has_permissions')->insert(['model_id'=>3,'model_type'=>User::class,'permission_id'=>$index+1]);
        }
        $this->manual=new WorkflowService;
    }
    protected function actor(int $id,?string $role=null): User
    {
        $user=(new User)->forceFill(['id'=>$id]);
        $user->setRelation('roles',collect($role?[(new Role)->forceFill(['id'=>1,'name'=>$role,'guard_name'=>'web'])]:[]));
        $user->setRelation('countryAdmin',null);
        return $user;
    }
    protected function requestInput(int $allocation,int $context,string $kind='refund'): array
    {
        return ['command_key'=>(string)Str::uuid(),'kind'=>$kind,'allocation_id'=>$allocation,
            'context_id'=>$kind==='refund'?$context:null,'amount_units'=>$kind==='refund'?'10000':'9000',
            'currency_code'=>'USD','method'=>'bank_transfer','institution'=>'synthetic_bank','destination_mask'=>'****1234'];
    }
    protected function command(array $w,string $reason='Synthetic verified operator reason'): array
    {
        return ['command_key'=>(string)Str::uuid(),'version'=>$w['version'],'reason'=>$reason];
    }
    protected function terminalEvidence(array $w,string $state='SUCCESS'): array
    {
        return ['allocation_id'=>$w['allocation_id'],'amount_units'=>$w['amount_units'],'currency_code'=>$w['currency_code'],
            'beneficiary_id'=>$w['beneficiary_id'],'method'=>$w['method'],'institution'=>$w['institution'],
            'destination_mask'=>$w['destination_mask'],'external_reference'=>'synthetic-'.Str::uuid(),
            'executed_at'=>now()->utc()->toIso8601String(),'state'=>$state,'attested'=>true];
    }
    protected function approved(string $kind='refund'): array
    {
        [$id,$context]=$this->funded();
        $w=$this->manual->request($kind==='refund'?$this->customer:$this->vendor,$this->requestInput($id,$context,$kind));
        return $this->manual->action($this->finance,$w['id'],'approve',$this->command($w));
    }
    protected function claimed(string $kind='refund'): array
    {
        $w=$this->approved($kind);
        return $this->manual->action($this->finance,$w['id'],'claim',$this->command($w));
    }
    protected function digest(): string
    {
        $rows=[];
        foreach (array_merge(['commerce_payment_allocations','payment_collection_contexts','payment_financial_operations',
            'platform_fee_ledger_entries','bookings','users','transactions'],ManualSchema::TABLES) as $table) {
            $rows[$table]=DB::table($table)->orderBy('id')->get()->map(fn($r)=>(array)$r)->all();
        }
        return hash('sha256',serialize($rows));
    }
    protected function denies(callable $work,int $status=0): void
    {
        $before=$this->digest();
        try { $work(); self::fail('Expected financial denial.'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { if ($status) self::assertSame($status,$e->getStatusCode()); }
        catch (\DomainException|\RuntimeException|\Illuminate\Database\QueryException $e) { self::assertNotSame('',$e->getMessage()); }
        self::assertSame($before,$this->digest(),'Denied command must preserve exact persisted state.');
    }

    protected function policyRequest(int $allocation,int $context,string $amount='5000'): array
    {
        return array_replace($this->requestInput($allocation,$context),['amount_units'=>$amount]);
    }

    protected function finishPolicyRefund(array $w): array
    {
        $w=$this->manual->action($this->finance,$w['id'],'approve',$this->command($w));
        $w=$this->manual->action($this->finance,$w['id'],'claim',$this->command($w));
        return $this->manual->action($this->finance,$w['id'],'complete',
            $this->command($w)+['evidence'=>$this->terminalEvidence($w)]);
    }

    public static function policyForwardBoundaries(): array
    {
        return [['approve'],['claim'],['complete'],['reconcile-complete'],['reconcile-approved']];
    }

    protected function assertPolicyForwardBoundary(string $boundary,int $id,int $c): void
    {
        $w=$this->manual->request($this->customer,$this->policyRequest($id,$c));
        if ($boundary!=='approve') $w=$this->manual->action($this->finance,$w['id'],'approve',$this->command($w));
        if ($boundary==='complete' || str_starts_with($boundary,'reconcile-')) {
            $w=$this->manual->action($this->finance,$w['id'],'claim',$this->command($w));
        }
        if (str_starts_with($boundary,'reconcile-')) {
            $w=$this->manual->action($this->finance,$w['id'],'review',$this->command($w));
        }
        // Valid core reservation, but invalid combined manual policy authority.
        // Do not corrupt workflow snapshots or bypass monetary/schema guards.
        $extra=(new FinancialOperations)->reserveOwned($id,'refund',(string)Str::uuid(),5000,(int)$this->finance->id,$c);
        $action=str_starts_with($boundary,'reconcile-') ? 'reconcile' : $boundary;
        $input=$this->command($w);
        if ($boundary==='complete' || $boundary==='reconcile-complete') {
            $input+=['evidence'=>$this->terminalEvidence($w)];
            if ($action==='reconcile') $input['target']='COMPLETED';
        } elseif ($boundary==='reconcile-approved') {
            $input+=['target'=>'APPROVED','evidence'=>$this->terminalEvidence($w,'NO_MOVEMENT')];
        }
        $this->denies(fn()=>$this->manual->action($this->finance,$w['id'],$action,$input));
        self::assertSame(0,(new AllocationBalances)->current($id)['refunded']);
        self::assertSame(10000,(new FinancialOperations)->reserved($id,'refund',$c));
        // Releasing only the independent unclaimed core hold restores the
        // existing mandate; retry the exact previously denied intent.
        (new FinancialOperations)->cancel($extra->id);
        $result=$this->manual->action($this->finance,$w['id'],$action,$input);
        self::assertSame(in_array($boundary,['complete','reconcile-complete'],true)?'COMPLETED':'APPROVED',$result['state']);
    }
}
