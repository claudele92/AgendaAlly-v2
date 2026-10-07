<?php
declare(strict_types=1);

// Disposable hardening bootstrap only: no .env/native database/providers.
$root = dirname(__DIR__, 2);
require $root.'/.migration-backup/backend/tests/Hardening/bootstrap.php';

final class FinancialReadinessPayoutProbe extends \Tests\Hardening\PayoutFinancialFixture
{
    private function state(): array
    {
        return ['wallets'=>$this->database->table('wallets')->orderBy('id')->get(['id','user_id','price'])->all(),
            'payout'=>$this->database->table('payouts')->where('id',1)->first(['id','created_by','approved_by','price','status']),
            'histories'=>$this->database->table('wallet_histories')->count(),
            'transactions'=>$this->database->table('transactions')->count(),
            'fingerprints'=>$this->payoutState()];
    }

    public function probe(bool $staleHistoryCache=false): array
    {
        $this->setUp();
        try {
            if($staleHistoryCache) {
                $property=new ReflectionProperty(\Illuminate\Database\Eloquent\Model::class,'guardableColumns');
                $cache=$property->getValue();
                $cache[\App\Models\WalletHistory::class]=array_values(array_diff(
                    $this->database->schema()->getColumnListing('wallet_histories'),['type']));
                $property->setValue(null,$cache);
            }
            $before=$this->state(); $injected=null; $enabled=true;
            \App\Models\WalletHistory::created(function ($history) use (&$injected,&$enabled): void {
                if ($enabled && $history->type==='withdraw') {
                    $deleted=\App\Models\Wallet::where('user_id',3)->delete();
                    $injected=['event'=>'WalletHistory.created(withdraw)','deleted_funding_rows'=>$deleted,
                        'inside_transaction_level'=>$this->database->getConnection()->transactionLevel(),
                        'immediate_state'=>$this->state()];
                }
            });
            $result=$this->approve();
            $after=$this->state();
            $enabled=false;
            $retry=$this->approve();
            $settled=$this->state();
            $replay=$this->approve();
            $replayState=$this->state();
            return ['driver'=>'sqlite; :memory:', 'actor'=>['id'=>3,'role'=>'admin'],
                'operation'=>'PayoutService.statusChange(1, accepted); wallet rail; value 20',
                'controlled_stale_history_column_cache'=>$staleHistoryCache,
                'before'=>$before,'injection'=>$injected,'response'=>$result,'after'=>$after,
                'rollback_exact'=>json_encode($before)===json_encode($after),'retry_response'=>$retry,'settled'=>$settled,
                'replay_response'=>$replay,'replay_unchanged'=>json_encode($settled)===json_encode($replayState)];
        } finally { $this->tearDown(); }
    }
}

function invokeFixture(string $class,string $method,callable $capture): array
{
    $test=new $class($method);
    (new ReflectionMethod($class,'setUp'))->invoke($test);
    try {
        $observed=null;
        try { $test->$method(); } catch (\PHPUnit\Framework\AssertionFailedError $failure) {
            $observed=$failure->getMessage();
        }
        $app=(new ReflectionProperty($class,'app'))->getValue($test);
        return ['original_assertion'=>$observed,'observations'=>$capture($app)];
    } finally { (new ReflectionMethod($class,'tearDown'))->invoke($test); }
}

$mode=$argv[1]??'';
if ($mode==='payout') {
    $facts=(new FinancialReadinessPayoutProbe('probe'))->probe();
} elseif($mode==='payout-cache') {
    $facts=(new FinancialReadinessPayoutProbe('probe'))->probe(true);
} elseif ($mode==='providers') {
    $facts=[];
    foreach ([
        'platform_routing'=>'test_platform_routing_does_not_use_vendor_shop_credentials_or_offer_vendor_configuration',
        'direct_policy'=>'test_optional_vendor_direct_collection_policy_fails_closed_and_lists_only_supported_mtn_setup',
    ] as $name=>$method) {
        $facts[$name]=invokeFixture(\Tests\Hardening\PaymentCollectionAmendmentTest::class,$method,function($app)use($name):array {
            $resolver=new \App\Services\PaymentEligibility\PaymentEligibilityService;
            $shop=\App\Models\Shop::findOrFail(7);
            $context=(new \App\Services\PaymentEligibility\PaymentContextFactory)->shop($shop,\App\Models\ShopLocation::PRODUCT);
            $payment=\App\Models\Payment::findOrFail(5);
            return ['policy'=>$resolver->collectionPolicy($shop,\App\Models\ShopLocation::PRODUCT),
                'platform'=>$resolver->decision($payment,array_merge($context,['collection_mode'=>'platform'])),
                'vendor_direct'=>$resolver->decision($payment,array_merge($context,['collection_mode'=>'vendor_direct'])),
                'configuration'=>$resolver->decision($payment,array_merge($context,['collection_mode'=>'platform']),true)];
        });
    }
    $facts['global_payload']=invokeFixture(\Tests\Hardening\PaymentEligibilityResolverTest::class,
        'test_global_provider_credentials_do_not_bypass_environment_or_global_active_gates',function($app):array {
            $context=['valid'=>true,'transaction_type'=>'product','collection_mode'=>'platform','shop_ids'=>[7],
                'country_ids'=>[10],'transaction_currency'=>'GBP','transaction_currency_id'=>1];
            return (new \App\Services\PaymentEligibility\PaymentEligibilityService)->decision(\App\Models\Payment::findOrFail(5),$context);
        });
} elseif($mode==='driver-cache') {
    $facts=invokeFixture(\Tests\Hardening\DeliveryDriverPhase2InvitationTest::class,
        'test_a_vendor_invites_for_its_shop_with_hashed_256_bit_token_and_honest_delivery_status',function($app):array {
            $property=new ReflectionProperty(\Illuminate\Database\Eloquent\Model::class,'guardableColumns');
            $original=$property->getValue();
            try {
                $property->setValue(null,[\App\Models\User::class=>['id','email','active']]);
                $stale=(new \App\Models\User)->fill(['email_verified_at'=>'2026-10-06 00:00:00']);
                $property->setValue(null,[]);
                $fresh=(new \App\Models\User)->fill(['email_verified_at'=>'2026-10-06 00:00:00']);
                return ['schema_has_verified_column'=>\Illuminate\Support\Facades\Schema::hasColumn('users','email_verified_at'),
                    'stale_cache_verified_attribute_present'=>array_key_exists('email_verified_at',$stale->getAttributes()),
                    'fresh_cache_verified_attribute_present'=>array_key_exists('email_verified_at',$fresh->getAttributes()),
                    'no_request_or_native_database_mutation'=>true];
            } finally {$property->setValue(null,$original);}
        });
} else { throw new RuntimeException('Use payout, providers or driver-cache.'); }
$suffix=$argv[2]??'';
if($suffix!=='' && $suffix!=='corrected')throw new RuntimeException('Invalid evidence suffix.');
$path=$root.'/.local/financial-readiness/'.$mode.'-reproduction'.($suffix!==''?'-'.$suffix:'').'.json';
if (file_exists($path)||is_link($path)) throw new RuntimeException('Evidence already exists; never overwrite.');
$handle=fopen($path,'xb');
if(!$handle||!chmod($path,0600))throw new RuntimeException('Private evidence unavailable.');
$content=json_encode($facts,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
if(fwrite($handle,$content)!==strlen($content)||!fflush($handle)||!fsync($handle))throw new RuntimeException('Evidence persistence failed.');
fclose($handle);
echo $mode.' evidence retained; no native database or transport used.'."\n";
