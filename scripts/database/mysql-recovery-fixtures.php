<?php
declare(strict_types=1);
// Direct synthetic fixture insertion only; no checkout, refund, payout,
// notification enqueue/deliver/recover or payment handler is invoked.
function recoveryUuid(int $n):string {return sprintf('58000000-0000-4000-8000-%012d',$n);}
function recoveryInsert(PDO $p,string $table,array $fields):void {
    recoveryAssert((bool)preg_match('/^[a-z0-9_]+$/D',$table),'Invalid fixture identifier');
    $cols=array_column($p->query("SHOW COLUMNS FROM `$table`")->fetchAll(),'Field');
    // Laravel PDO uses its native upper-case SHOW column keys.
    if($cols===[])$cols=array_column($p->query("SHOW COLUMNS FROM `$table`")->fetchAll(),'field');
    foreach(['created_at','updated_at'] as $key)if(in_array($key,$cols,true)&&!array_key_exists($key,$fields))$fields[$key]=gmdate('Y-m-d H:i:s');
    foreach(array_keys($fields) as $key)recoveryAssert(in_array($key,$cols,true),"Fixture contract field absent: $table.$key");
    $quoted=implode(',',array_map(static fn($c)=>"`$c`",array_keys($fields)));
    $q=$p->prepare("INSERT INTO `$table` ($quoted) VALUES (".implode(',',array_fill(0,count($fields),'?')).')');
    $q->execute(array_values($fields));
}
function recoveryKeys(string $path):array {
    recoveryAssert(is_file($path)&&!is_link($path)&&(fileperms($path)&0777)===0600,'Private independent key material unavailable');
    return json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
}
function recoveryInstallKeys($app,array $keys):Illuminate\Encryption\Encrypter {
    $e=new Illuminate\Encryption\Encrypter(base64_decode($keys['app'],true),'AES-256-CBC');
    $app['config']->set('app.key','base64:'.$keys['app']);
    $app->instance('encrypter',$e);
    Illuminate\Support\Facades\Crypt::swap($e);
    return $e;
}
function recoveryFixtureMode(string $mode,PDO $rootDb,$app,string $root,string $state,string $side,?string $custody=null,bool $catalogsInstalled=false):void {
    $dir="$state/$side";$custody??="$root/.local/mysql-synthetic-key-custody";
    $p=Illuminate\Support\Facades\DB::connection()->getPdo();
    $p->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $p->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);
    if($mode==='fixtures') {
        recoveryAssert($side==='source'&&!file_exists($custody),'New independent synthetic custody required; never reuse keys');
        mkdir($custody,0700);
        $keys=['app'=>base64_encode(Illuminate\Encryption\Encrypter::generateKey('AES-256-CBC')),
            'offlineAuthority'=>base64_encode(random_bytes(32))];
        recoveryWrite("$dir/key-material.json",$keys);
        recoveryWrite("$custody/key-material.json",$keys);
        $e=recoveryInstallKeys($app,$keys);
        $insert=static fn($t,$f)=>recoveryInsert($p,$t,$f);
        $now=gmdate('Y-m-d H:i:s');
        $p->beginTransaction();
        try {
            foreach([1=>'reviewer',2=>'vendor',3=>'customer',4=>'ungranted-admin'] as $id=>$name)
                $insert('users',['id'=>$id,'uuid'=>recoveryUuid($id),'firstname'=>"Synthetic $name",
                    'email'=>"$name@synthetic.invalid",'password'=>password_hash(bin2hex(random_bytes(24)),PASSWORD_BCRYPT),
                    'verify_token'=>null,'remember_token'=>'synthetic-remember-'.$id]);
            $insert('currencies',['id'=>1,'title'=>'XTS','symbol'=>'XTS','active'=>0,'default'=>0]);
            $insert('countries',['id'=>1,'code'=>'ZZ','active'=>0,'currency_id'=>1]);
            $insert('shops',['id'=>501,'uuid'=>recoveryUuid(501),'user_id'=>2,'type'=>1,
                'delivery_time'=>'{"from":"00:00","to":"00:00","type":"minute"}','status'=>'inactive']);
            foreach([100=>'admin',101=>'seller',102=>'user'] as $id=>$name)$insert('roles',['id'=>$id,'name'=>$name,'guard_name'=>'web']);
            foreach([1=>100,2=>101,3=>102,4=>100] as $user=>$role)
                $insert('model_has_roles',['model_id'=>$user,'model_type'=>App\Models\User::class,'role_id'=>$role]);
            // An existing Finance definition/grant must survive insertOrIgnore.
            $permissionId=100;
            if($catalogsInstalled) {
                $permissionId=$p->query("SELECT id FROM permissions WHERE name='payments.refunds.view' AND guard_name='web'")->fetchColumn();
                recoveryAssert($permissionId!==false,'Installed Finance definition required');
            } else $insert('permissions',['id'=>100,'name'=>'payments.refunds.view','guard_name'=>'web']);
            $insert('model_has_permissions',['model_id'=>1,'model_type'=>App\Models\User::class,'permission_id'=>$permissionId]);
            $insert('permissions',['id'=>101,'name'=>'synthetic.read','guard_name'=>'web']);
            $insert('role_has_permissions',['role_id'=>101,'permission_id'=>101]);
            $countryPermissionId=100;
            if($catalogsInstalled) {
                $countryPermissionId=$p->query("SELECT id FROM country_permissions WHERE `key`='payments.refunds.view'")->fetchColumn();
                recoveryAssert($countryPermissionId!==false,'Installed country Finance definition required');
            } else $insert('country_permissions',['id'=>100,'key'=>'payments.refunds.view','group'=>'payments','label'=>'Synthetic retained label']);
            $insert('country_roles',['id'=>100,'country_id'=>1,'name'=>'Synthetic country reader']);
            $insert('country_role_permissions',['country_role_id'=>100,'country_permission_id'=>$countryPermissionId]);
            $insert('country_invitations',['id'=>100,'country_id'=>1,'user_id'=>3,'created_by'=>1,'country_role_id'=>100,'status'=>2]);
            $insert('shop_permissions',['id'=>100,'key'=>'payments.payouts.manage','group'=>'payments','label'=>'Synthetic vendor control']);
            $insert('shop_roles',['id'=>100,'shop_id'=>501,'name'=>'Synthetic shop reader']);
            $insert('shop_role_permissions',['shop_role_id'=>100,'shop_permission_id'=>100]);
            // Disabled definitions only; encrypted payloads contain no real credential.
            $insert('payments',['id'=>1,'tag'=>'paypal','active'=>0,'sandbox'=>1]);
            $insert('payments',['id'=>2,'tag'=>'mtn','active'=>0,'sandbox'=>1]);
            $insert('payment_merchant_revisions',['id'=>recoveryUuid(10),'payment_id'=>1,'provider'=>'paypal',
                'owner_type'=>'platform','encrypted_payload'=>$e->encryptString('{"synthetic":true,"credentials":"NONE"}')]);
            $allocation=['id'=>1,'checkout_key'=>recoveryUuid(11),'origin_type'=>'booking','origin_id'=>580001,
                'shop_id'=>501,'vendor_user_id'=>2,'payer_user_id'=>3,'country_id'=>1,'currency_id'=>1,'currency_code'=>'XTS',
                'money_scale'=>2,'purpose'=>'base','obligation_key'=>'base','gross_amount'=>10000,'commission_amount'=>1000,
                'vendor_entitlement_amount'=>9000,'adjustment_amount'=>0,'native_components'=>'{"gross":10000,"commission":1000,"vendor":9000}',
                'policy_key'=>'platform_held_first_commission','state'=>'funded','version'=>1,'committed_at'=>$now,'finalized_at'=>$now,
                'original_platform_amount'=>10000,'original_vendor_direct_amount'=>0,'original_commission_satisfied'=>1000,
                'original_commission_receivable'=>0,'original_platform_adjustment'=>0,'original_vendor_adjustment'=>0,'original_vendor_payable'=>9000];
            $insert('commerce_payment_allocations',$allocation);
            $context=['id'=>1,'allocation_id'=>1,'funding_key'=>'synthetic-original','funding_slot'=>'selected_method',
                'confirmed_slot'=>'selected_method','funding_event_key'=>recoveryUuid(12),'receipt_claim_key'=>str_repeat('a',64),
                'collection_mode'=>'platform','custody_type'=>'platform','expected_collector_type'=>'platform',
                'confirmed_collector_type'=>'platform','credential_owner_type'=>'platform','payment_id'=>1,'provider_tag'=>'paypal',
                'configuration_source'=>'global','configuration_reference'=>'synthetic-disabled','configuration_revision'=>recoveryUuid(10),
                'provider_payment_reference'=>'synthetic-original-payment','currency_id'=>1,'currency_code'=>'XTS','money_scale'=>2,
                'amount'=>10000,'receipt_total_amount'=>10000,'original_commission_share'=>1000,'original_receivable_share'=>0,
                'original_adjustment_share'=>0,'original_vendor_entitlement_share'=>9000,'state'=>'confirmed','version'=>1,
                'committed_at'=>$now,'confirmed_at'=>$now];
            $insert('payment_collection_contexts',$context);
            // Two distinct nonterminal generic attempts; neither can be dispatched.
            foreach([2=>'PENDING',3=>'UNKNOWN',4=>'MTN_UNKNOWN'] as $id=>$status) {
                $c=$context;$c['id']=$id;$c['funding_key']="synthetic-$status";$c['funding_event_key']=recoveryUuid(20+$id);
                $c['state']='pending';$c['version']=0;
                foreach(['confirmed_slot','confirmed_collector_type','receipt_claim_key','confirmed_at',
                    'original_commission_share','original_receivable_share','original_adjustment_share','original_vendor_entitlement_share'] as $f)$c[$f]=null;
                $c['amount']=500;$c['receipt_total_amount']=500;
                $c['provider_payment_reference']=null;
                if($id===4){$c['payment_id']=2;$c['provider_tag']='mtn';$c['configuration_revision']='synthetic-mtn-revision';}
                $insert('payment_collection_contexts',$c);
                if($id!==4)$insert('electronic_collection_attempts',['id'=>recoveryUuid(30+$id),'funding_event_key'=>$c['funding_event_key'],
                    'anchor_context_id'=>$id,'revision_id'=>recoveryUuid(10),'provider'=>'paypal','state'=>$status,
                    'process_reference'=>"synthetic-process-$id",'provider_payment_id'=>"synthetic-pending-$id",'version'=>1,'claimed_at'=>$now]);
                else $insert('payment_process',['id'=>recoveryUuid(34),'user_id'=>3,'model_type'=>App\Models\Booking::class,
                    'model_id'=>580001,'data'=>'{"synthetic":true,"providerCall":false}',
                    'mtn_funding_event_key'=>$c['funding_event_key'],'mtn_anchor_context_id'=>4,
                    'mtn_dispatch_state'=>'DISPATCH_OUTCOME_UNKNOWN','mtn_attempt_version'=>1,
                    'mtn_config_fingerprint'=>str_repeat('b',64),'mtn_dispatch_claimed_at'=>$now]);
            }
            foreach([40=>['refund','PENDING',300],41=>['refund','UNKNOWN',200],42=>['payout','RESERVED',400],43=>['receivable','SUCCESS',100]] as $id=>[$kind,$status,$amount])
                $insert('payment_financial_operations',['id'=>recoveryUuid($id),'kind'=>$kind,'allocation_id'=>1,'context_id'=>1,
                    'revision_id'=>recoveryUuid(10),'actor_id'=>1,'request_key'=>recoveryUuid(100+$id),'amount_units'=>$amount,
                    'provider'=>'paypal','original_payment_id'=>'synthetic-original-payment','state'=>$status,'version'=>1,
                    'claimed_at'=>$kind==='payout'?null:$now,'external_reference'=>$status==='SUCCESS'?'synthetic-receipt-43':null,
                    'completed_at'=>$status==='SUCCESS'?$now:null]);
            $receipt="Synthetic retained receipt only. No real money movement.\n";
            mkdir("$dir/private",0700);file_put_contents("$dir/private/receipt.txt",$receipt);chmod("$dir/private/receipt.txt",0600);
            $insert('payment_receipt_evidence',['id'=>recoveryUuid(50),'operation_id'=>recoveryUuid(43),'source'=>'synthetic',
                'receipt_reference'=>'synthetic-receipt-43','document_sha256'=>hash('sha256',$receipt),
                'retained_evidence'=>'{"synthetic":true,"accepted":false}','received_at'=>$now]);
            $insert('transactions',['id'=>1500,'payable_type'=>App\Models\Booking::class,'payable_id'=>580001,'price'=>'100.00',
                'user_id'=>3,'payment_sys_id'=>1,'payment_trx_id'=>'synthetic-original-payment','status'=>'paid',
                'status_description'=>'Synthetic persisted fixture, not a financial service execution','allocation_id'=>1,'collection_context_id'=>1]);
            $insert('platform_fee_ledger_entries',['id'=>1,'payable_type'=>App\Models\Booking::class,'payable_id'=>580001,'shop_id'=>501,
                'transaction_id'=>1500,'payment_id'=>1,'currency_id'=>1,'amount'=>'10.00','entry_type'=>'fee','status'=>'pending',
                'allocation_id'=>1,'collection_context_id'=>1,'effect_key'=>'synthetic-fee','event_group_key'=>recoveryUuid(12),
                'effect_kind'=>'commission','exact_amount'=>1000,'effect_data'=>'{"units":1000,"synthetic":true}']);
            $insert('wallets',['id'=>1,'uuid'=>recoveryUuid(60),'user_id'=>3,'currency_id'=>1,'price'=>'123.45']);
            $insert('wallet_histories',['id'=>1,'uuid'=>recoveryUuid(61),'wallet_uuid'=>recoveryUuid(60),'transaction_id'=>1500,
                'type'=>'topup','price'=>'123.45','note'=>'Synthetic opening fixture only','status'=>'paid','created_by'=>1]);
            $digest=hash_hmac('sha256','customer@synthetic.invalid|624831','base64:'.$keys['app']);
            $q=$p->prepare('UPDATE users SET verify_token=? WHERE id=3');$q->execute(['email:'.$digest]);
            $insert('password_resets',['email'=>'email-reset:customer@synthetic.invalid',
                'token'=>hash_hmac('sha256',"email-reset\0customer@synthetic.invalid\0".'624831','base64:'.$keys['app'])]);
            $insert('sessions',['id'=>'synthetic-recovery-session','user_id'=>3,
                'payload'=>base64_encode(serialize(['synthetic'=>true,'login_web_'.sha1(Illuminate\Auth\SessionGuard::class)=>3])),
                'last_activity'=>time()]);
            $insert('personal_access_tokens',['id'=>1,'tokenable_type'=>App\Models\User::class,'tokenable_id'=>3,'name'=>'synthetic-stale-token',
                'token'=>hash('sha256','synthetic-recovery-token'),'abilities'=>'["*"]','expires_at'=>gmdate('Y-m-d H:i:s',time()+3600)]);
            foreach([70=>'PENDING',71=>'UNKNOWN'] as $id=>$status)$insert('selected_email_deliveries',['id'=>recoveryUuid($id),
                'event_key'=>hash('sha256',"synthetic-email-$id"),'user_id'=>3,'kind'=>'verify',
                'encrypted_payload'=>$e->encryptString('{"email":"customer@synthetic.invalid","challenge":"624831"}'),
                'state'=>$status,'expires_at'=>gmdate('Y-m-d H:i:s',time()+600)]);
            $p->commit();
        } catch(Throwable $e){if($p->inTransaction())$p->rollBack();throw $e;}
        // Independent files/config custody, outside both datadirs and the DB dump.
        recoveryWrite("$custody/private-file-manifest.json",['receipt.txt'=>hash('sha256',$receipt)]);
        file_put_contents("$custody/receipt.txt",$receipt);chmod("$custody/receipt.txt",0600);
        recoveryWrite("$custody/authority-proof.json",['payload'=>'synthetic-offline-recovery-authority',
            'signature'=>hash_hmac('sha256','synthetic-offline-recovery-authority',base64_decode($keys['offlineAuthority'],true))]);
        recoveryWrite("$custody/runtime-contract.json",['engine'=>'8.0.42','schema'=>'agendaally_synthetic_recovery',
            'isolation'=>'REPEATABLE-READ','timezone'=>'+00:00','cipher'=>'AES-256-CBC',
            'migrationManifestSha256'=>hash_file('sha256',"$state/migration-hashes.json"),
            'providerSmtpWorkers'=>'disabled','definer'=>'lab_bootstrap@localhost']);
        recoveryAssert($e->decryptString($rootDb->query('SELECT encrypted_payload FROM payment_merchant_revisions')->fetchColumn())
            ==='{"synthetic":true,"credentials":"NONE"}','Source pre-upgrade key binding failed');
        recoveryWrite("$dir/fixtures.json",['pass'=>true,'syntheticOnly'=>true,'users'=>4,'allocatedPrincipalUnits'=>10000,
            'refundReservationsUnits'=>500,'payoutReservationUnits'=>400,'nativeMoneyScale'=>2,
            'wallet'=>'synthetic native DOUBLE 123.45 retained without conversion','sourcePreUpgradeKeyBinding'=>true,'liveServicesExecuted'=>false]);
        echo "Synthetic authority and independent private custody created; no credentials printed.\n";return;
    }
    if($mode==='recover-keys') {
        recoveryAssert(in_array($side,['restore','restore-retry'],true)&&is_file("$state/source/key-material.json"),'Independent key-loss drill requires source-only runtime keys');
        // Deliberately remove ONLY this campaign's disposable runtime key copy.
        // Escrow is outside the database backup, restore instance and source datadir.
        unlink("$state/source/key-material.json");
        recoveryAssert(!is_file("$state/source/key-material.json"),'Source runtime key loss not established');
        $keys=recoveryKeys("$custody/key-material.json");
        recoveryWrite("$dir/key-material.json",$keys);
        $runtime=json_decode(file_get_contents("$custody/runtime-contract.json"),true,512,JSON_THROW_ON_ERROR);
        recoveryAssert($runtime['migrationManifestSha256']===hash_file('sha256',"$state/migration-hashes.json")
            &&$runtime['engine']===$rootDb->query('SELECT @@version')->fetchColumn()
            &&$runtime['schema']===$rootDb->query('SELECT DATABASE()')->fetchColumn()
            &&$runtime['isolation']===$rootDb->query('SELECT @@transaction_isolation')->fetchColumn()
            &&$runtime['timezone']===$rootDb->query('SELECT @@time_zone')->fetchColumn()
            &&$runtime['providerSmtpWorkers']==='disabled','Independent recovered runtime contract differs');
        recoveryWrite("$dir/recovered-runtime-contract.json",$runtime);
        $e=recoveryInstallKeys($app,$keys);
        $encrypted=$rootDb->query('SELECT encrypted_payload FROM payment_merchant_revisions ORDER BY id')->fetchColumn();
        $wrong=new Illuminate\Encryption\Encrypter(Illuminate\Encryption\Encrypter::generateKey('AES-256-CBC'),'AES-256-CBC');
        $denied=false;try{$wrong->decryptString($encrypted);}catch(Illuminate\Contracts\Encryption\DecryptException){$denied=true;}
        recoveryAssert($denied,'Wrong-key decryption must fail closed');
        recoveryAssert($e->decryptString($encrypted)==='{"synthetic":true,"credentials":"NONE"}','Recovered native encrypted merchant revision differs');
        foreach($rootDb->query('SELECT encrypted_payload FROM selected_email_deliveries ORDER BY id')->fetchAll() as $row)
            recoveryAssert($e->decryptString($row['encrypted_payload'])==='{"email":"customer@synthetic.invalid","challenge":"624831"}','Selected encrypted payload key recovery failed');
        mkdir("$dir/private",0700);copy("$custody/receipt.txt","$dir/private/receipt.txt");chmod("$dir/private/receipt.txt",0600);
        $manifest=json_decode(file_get_contents("$custody/private-file-manifest.json"),true,512,JSON_THROW_ON_ERROR);
        recoveryAssert(hash_file('sha256',"$dir/private/receipt.txt")===$manifest['receipt.txt']
            && $rootDb->query('SELECT document_sha256 FROM payment_receipt_evidence')->fetchColumn()===$manifest['receipt.txt'],'Private receipt recovery mismatch');
        $proof=json_decode(file_get_contents("$custody/authority-proof.json"),true,512,JSON_THROW_ON_ERROR);
        recoveryAssert(hash_equals($proof['signature'],hash_hmac('sha256',$proof['payload'],base64_decode($keys['offlineAuthority'],true))),'Independent offline authority key failure');
        recoveryWrite("$dir/recover-keys.json",['pass'=>true,'sourceRuntimeKeyRemoved'=>true,'custody'=>'separate local-only directory, not dump or source datadir',
            'wrongKeyRejected'=>true,'nativeMerchantAndEmailDecryption'=>'exact plaintext match','privateReceipt'=>'native retained document hash match',
            'independentAuthorityProof'=>'HMAC matches','independentRuntimeConfig'=>'exact engine/source/authority contract',
            'offHostCustody'=>'not qualified']);
        echo "Independent synthetic key/file recovery PASS.\n";return;
    }
    $keys=recoveryKeys("$dir/key-material.json");$encrypter=recoveryInstallKeys($app,$keys);
    if($mode==='probe') {
        $before=recoverySnapshot($rootDb);
        recoveryAssert($encrypter->decryptString($rootDb->query('SELECT encrypted_payload FROM payment_merchant_revisions')->fetchColumn())
            ==='{"synthetic":true,"credentials":"NONE"}','Post-upgrade/restored native key binding failed');
        foreach($rootDb->query('SELECT encrypted_payload FROM selected_email_deliveries')->fetchAll() as $row)
            recoveryAssert($encrypter->decryptString($row['encrypted_payload'])==='{"email":"customer@synthetic.invalid","challenge":"624831"}','Post-upgrade/restored email key binding failed');
        $scope=new App\Services\ManualFinance\FinanceScope();
        $a=(object)$rootDb->query('SELECT * FROM commerce_payment_allocations WHERE id=1')->fetch();
        $reviewer=App\Models\User::findOrFail(1);$denied=App\Models\User::findOrFail(4);$customer=App\Models\User::findOrFail(3);$vendor=App\Models\User::findOrFail(2);
        recoveryAssert($scope->has($reviewer,$a,'refund','view'),'Persisted explicit Finance view grant no longer authorizes');
        recoveryAssert(!$scope->has($reviewer,$a,'refund','approve'),'Upgrade silently grants approval');
        recoveryAssert(!$scope->has($denied,$a,'refund','view'),'Ungrant­ed admin gained Finance authority');
        recoveryAssert($scope->vendor($vendor,$a),'Vendor/shop authority not preserved');
        recoveryAssert($scope->readable($customer,$a,'refund'),'Original payer read authority not preserved');
        $session=new Illuminate\Session\DatabaseSessionHandler($app['db']->connection('lab'),'sessions',120,$app);
        recoveryAssert($session->read('synthetic-recovery-session')!=='','Synthetic database session precondition absent');
        recoveryAssert(Laravel\Sanctum\PersonalAccessToken::findToken('1|synthetic-recovery-token')!==null,'Synthetic persisted token precondition absent');
        $reset=new App\Services\AuthService\PasswordResetService();
        recoveryAssert($reset->isCurrentEmailToken($customer,'624831'),'Native restored key-dependent reset challenge precondition failed');
        // Verification cache is intentionally NOT backed up; demonstrate that its
        // persisted hash still matches, then exercise native validation transiently.
        $digest=hash_hmac('sha256','customer@synthetic.invalid|624831',(string)config('app.key'));
        recoveryAssert($customer->verify_token==='email:'.$digest,'Native challenge key-dependent binding mismatch');
        Illuminate\Support\Facades\Cache::put('email-verification:3',$digest,600);
        recoveryAssert((new App\Services\AuthService\EmailVerificationService())->isCurrent($customer,'624831'),'Native challenge validity precondition failed');
        Illuminate\Support\Facades\Cache::forget('email-verification:3');
        recoveryAssert(recoverySnapshot($rootDb)===$before,'Read-only health/auth probe mutated persisted authority');
        recoveryWrite("$dir/probe.json",['pass'=>true,'nativeFinance'=>'explicit grant preserved; approval and ungranted admin denied',
            'nativeVendorAndPayer'=>'preserved','sessionTokenResetChallenge'=>'pre-invalidation validity demonstrated',
            'persistedMutations'=>0,'httpFinanceSessions'=>'separate task; not certified here']);
        echo "Native read-only authority probes PASS.\n";return;
    }
    if($mode==='invalidate') {
        recoveryAssert(in_array($side,['restore','restore-retry'],true),'Only restored disposable authority may be invalidated');
        $before=recoverySnapshot($rootDb);$invariants=recoveryInvariants($rootDb);
        $p->beginTransaction();
        try {
            $p->exec('DELETE FROM sessions');
            $p->exec('DELETE FROM personal_access_tokens');
            $p->exec('DELETE FROM password_resets');
            $p->exec('UPDATE users SET verify_token=NULL,remember_token=NULL WHERE verify_token IS NOT NULL OR remember_token IS NOT NULL');
            $p->commit();
        }catch(Throwable $e){if($p->inTransaction())$p->rollBack();throw $e;}
        Illuminate\Support\Facades\Cache::flush();
        $session=new Illuminate\Session\DatabaseSessionHandler($app['db']->connection('lab'),'sessions',120,$app);
        recoveryAssert($session->read('synthetic-recovery-session')==='','Stale restored session survived invalidation');
        recoveryAssert(Laravel\Sanctum\PersonalAccessToken::findToken('1|synthetic-recovery-token')===null,'Stale restored bearer token survived');
        $user=App\Models\User::findOrFail(3);
        recoveryAssert(!(new App\Services\AuthService\PasswordResetService())->isCurrentEmailToken($user,'624831'),'Stale native reset challenge survived');
        // Even a mistakenly resurrected cache entry cannot validate the old DB binding.
        $digest=hash_hmac('sha256','customer@synthetic.invalid|624831',(string)config('app.key'));
        Illuminate\Support\Facades\Cache::put('email-verification:3',$digest,600);
        recoveryAssert((new App\Services\AuthService\EmailVerificationService())->consume('customer@synthetic.invalid','624831')===null,'Stale verification challenge consumed');
        Illuminate\Support\Facades\Cache::flush();
        $after=recoverySnapshot($rootDb);
        foreach($before['tables'] as $name=>$table) {
            if(!in_array($name,['sessions','personal_access_tokens','password_resets','users'],true))
                recoveryAssert($table===$after['tables'][$name],"Recovery invalidation changed financial/access evidence: $name");
        }
        foreach($before['tables']['users']['rows'] as $row) {
            $want=$row;$want['verify_token']=null;$want['remember_token']=null;
            recoveryAssert(in_array($want,$after['tables']['users']['rows'],true),'Recovery changed user password, identity or unrelated fields');
        }
        recoveryAssert(recoveryMetadataContract($before)===recoveryMetadataContract($after),'Recovery invalidation changed native guards');
        recoveryAssert(recoveryInvariants($rootDb)===$invariants,'Recovery invalidation changed money/reservations/permissions/outbox');
        $pending=$rootDb->query("SELECT id,state FROM electronic_collection_attempts WHERE state IN ('PENDING','UNKNOWN') ORDER BY id")->fetchAll();
        $ops=$rootDb->query("SELECT id,kind,state,amount_units,original_payment_id FROM payment_financial_operations WHERE state IN ('PENDING','UNKNOWN','RESERVED') ORDER BY id")->fetchAll();
        $email=$rootDb->query("SELECT id,state FROM selected_email_deliveries WHERE state IN ('PENDING','UNKNOWN') ORDER BY id")->fetchAll();
        recoveryAssert(count($pending)===2&&count($ops)===3&&count($email)===2,'Uncertain evidence missing from reconciliation inventory');
        recoveryWrite("$dir/reconciliation.json",['pendingUnknownAttempts'=>$pending,'heldOperations'=>$ops,'pendingUnknownEmail'=>$email,
            'mtn'=>$rootDb->query("SELECT id,mtn_dispatch_state FROM payment_process ORDER BY id")->fetchAll(),
            'decision'=>'HOLD ALL. No automated callback, retry, money movement, SMTP resend or worker activation; operator reconciliation before any traffic switch.',
            'duplicateEffectExposure'=>'Backup recovery cannot prove external outcomes. This synthetic campaign had NO external activity; real recovery needs independent outcome evidence.']);
        recoveryWrite("$dir/invalidate.json",['pass'=>true,'policy'=>'Restore quarantined; explicitly invalidate sessions, bearer tokens, remember tokens, reset/verify challenges; preserve keys/passwords/grants/approval and receipt evidence',
            'nativeStaleSessionTokenChallengeChecks'=>'denied','allowedMutations'=>['sessions','personal_access_tokens','password_resets','users.verify_token','users.remember_token'],
            'allOtherRowsMetadataAuthority'=>'identical','automaticMoneyEmailReplay'=>false]);
        echo "Restored stale-authority invalidation and passive reconciliation PASS.\n";return;
    }
    throw new RuntimeException('Unknown fixture/probe mode');
}
