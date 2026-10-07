<?php
declare(strict_types=1);
require __DIR__.'/mysql-recovery-evidence.php';
require __DIR__.'/mysql-recovery-compare.php';
require __DIR__.'/mysql-temporal-recovery-policy.php';
require __DIR__.'/mysql-recovery-fixtures.php';
$root=dirname(__DIR__,2);
$continuation=($argv[3]??null)==='continuation';
$state="$root/.local/".($continuation?'mysql-temporal-recovery-completed':'mysql-temporal-recovery-qualified');
$custody="$root/.local/".($continuation?'mysql-temporal-key-custody-completed':'mysql-temporal-key-custody');
$base="$root/.local/clean-publication-validation/.migration-backup/backend";
$frozen="$root/.local/agendaally-clean-repository/.migration-backup/backend";
$mode=$argv[1]??'';$side=$argv[2]??'';
recoveryAssert(count($argv)===($continuation?4:3)&&in_array($side,['source','restore'],true)
    &&realpath($state)===$state&&!is_link($state)&&(fileperms($state)&0777)===0700
    &&fileowner($state)===posix_geteuid()&&is_file("$state/approval.txt"),'Owned approved temporal campaign required');
$modes=['freeze','bootstrap','definer','fixtures','point','backup-preconditions','backup-receipt','outcomes',
    'later','restore-preconditions','restored','reconcile','final','preservation','assessment',
    'continuation-preconditions','clone-preconditions','clone-point'];
recoveryAssert(in_array($mode,$modes,true),'Unknown temporal mode refused');
$restoreModes=['definer','restore-preconditions','restored','reconcile','final','assessment'];
recoveryAssert($side==='source'?!in_array($mode,['restore-preconditions','restored','reconcile','final','assessment'],true)
    :in_array($mode,$restoreModes,true),'Wrong temporal side refused');
foreach(['curl_exec','curl_multi_exec','fsockopen','pfsockopen','stream_socket_client','socket_connect',
    'mail','exec','shell_exec','proc_open','popen','system','passthru'] as $f)
    recoveryAssert(!function_exists($f),'Transports/process execution must be disabled');
$read=static fn($path)=>json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
// Freeze entire previous private recovery/custody tree when available; never
// create/reconstruct old receipts when this checkout did not retain them.
function temporalPreviousCustody(string $root,bool $includeFailed=false):array {
    $out=[];
    foreach(array_merge(['mysql-synthetic-recovery','mysql-synthetic-key-custody','mysql-temporal-recovery'],
        $includeFailed?['mysql-temporal-recovery-qualified','mysql-temporal-key-custody']:[]) as $name) {
        $dir="$root/.local/$name";
        if(!file_exists($dir)){$out[$name]=['available'=>false];continue;}
        recoveryAssert(realpath($dir)===$dir&&!is_link($dir),'Previous custody path unsafe');
        $files=[];
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS)) as $f) {
            recoveryAssert(!$f->isLink(),'Previous custody symlink refused');
            $files[substr($f->getPathname(),strlen($dir)+1)]=['type'=>$f->getType(),
                'sha256'=>$f->isFile()?hash_file('sha256',$f->getPathname()):null,
                'bytes'=>$f->getSize(),'mode'=>fileperms($f->getPathname())&0777,'owner'=>fileowner($f->getPathname())];
        }
        ksort($files,SORT_STRING);$out[$name]=['available'=>true,'files'=>$files];
    }
    return $out;
}
if($mode==='freeze') {
    recoveryWrite("$state/protected-before.json",recoverySource($root));
    recoveryWrite("$state/previous-before.json",temporalPreviousCustody($root,$continuation));
    recoveryWrite("$state/migration-hashes.json",recoveryManifest($frozen));
    echo "New campaign only; prior custody presence/bytes frozen, protected source hashed.\n";exit;
}
recoveryAssert(recoverySource($root)===$read("$state/protected-before.json"),'Protected source drift');
recoveryAssert(recoveryManifest($frozen)===$read("$state/migration-hashes.json"),'Frozen native migrations changed');
if($mode==='continuation-preconditions') {
    recoveryAssert($continuation&&$side==='source','Only bounded new-target continuation');
    $prior="$root/.local/mysql-temporal-recovery-qualified";
    foreach(['bootstrap','definer','fixtures','point','backup-preconditions','backup-receipt'] as $stage)
        recoveryAssert($read("$prior/source/$stage-exit.json")['childExit']===0,'Selected fresh synthetic backup not qualified');
    recoveryAssert($read("$prior/source/outcomes-exit.json")['childExit']===255
        &&$read("$prior/supervisor-exit.json")['supervisorExit']===255
        &&str_contains(file_get_contents("$prior/source/outcomes.log"),"1054 Unknown column 'updated_at'")
        &&!file_exists("$prior/source/mysql.sock")&&!file_exists("$prior/restore/mysql.sock"),
        'Bounded continuation requires exact stopped synthetic fixture failure');
    $receipt=$read("$prior/backup-receipt.json");
    recoveryAssert(hash_file('sha256',"$state/input-synthetic.sql")===$receipt['backupSha256']
        &&hash_file('sha256',"$prior/source/point.json")===$receipt['pointSha256'],'Only this campaign fresh synthetic dump may be imported');
    recoveryAssert(!file_exists($custody),'New independent continuation custody required');
    mkdir($custody,0700);
    foreach(['key-material.json','private-file-manifest.json','receipt.txt','authority-proof.json','runtime-contract.json'] as $file) {
        $input="$root/.local/mysql-temporal-key-custody/$file";
        recoveryAssert(is_file($input)&&!is_link($input)&&(fileperms($input)&0777)===0600,'Selected synthetic custody absent');
        recoveryAssert(copy($input,"$custody/$file"),'New-target key/file custody copy failed');
        chmod("$custody/$file",0600);
    }
    recoveryWrite("$state/continuation-input.json",['pass'=>true,'inputBackupSha256'=>$receipt['backupSha256'],
        'priorFailure'=>'native MTN fixture assumed absent updated_at; transaction rolled back; failed instance untouched',
        'scope'=>'new disposable targets; this campaign fresh synthetic dump only; no normal/historical dump or old-instance restart']);
    exit;
}
$dir="$state/$side";$p=recoveryRoot($state,$side);
if($mode==='definer'){recoveryDefiner($p,$dir);exit;}
if(in_array($mode,['clone-preconditions','clone-point'],true)) {
    recoveryAssert($continuation&&$side==='source','New-target clone allowed only in bounded continuation');
    $prior="$root/.local/mysql-temporal-recovery-qualified";
    if($mode==='clone-preconditions') {
        recoveryAssert((int)$p->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='agendaally_synthetic_recovery'")->fetchColumn()===0
            &&hash_file('sha256',"$state/input-synthetic.sql")===$read("$state/continuation-input.json")['inputBackupSha256'],
            'New empty target / exact current campaign synthetic dump required');
    } else {
        $s=recoverySnapshot($p);recoveryPrivilegeContract($p,$s,$state,$side);
        recoveryCompareRestore($read("$prior/source/point.json"),$s,$dir);
        recoveryAssert(copy("$prior/source/bootstrap-ddl.jsonl","$dir/bootstrap-ddl.jsonl"),'Native emitted guard proof copy failed');
        chmod("$dir/bootstrap-ddl.jsonl",0600);
        recoveryWrite("$dir/clone-point.json",['pass'=>true,'nativeSelectedPointEquivalent'=>true,
            'noBootstrapOrPopulatedMigrationReplayed'=>true]);
    }
    exit;
}
recoveryAssert(!$continuation||!in_array($mode,['bootstrap','fixtures'],true),'No bootstrap/fixture replay in bounded continuation');
if(in_array($mode,['bootstrap','fixtures'],true)) {
    foreach(array_unique(array_merge(array_keys($_SERVER),array_keys($_ENV))) as $key)
        if(preg_match('/^(APP_|DB_|DATABASE_URL$|AGENDAALLY_|DEVELOPMENT_|MAIL_|AWS_|SESSION_|CACHE_|QUEUE_|REDIS_)/',$key)){
            putenv($key);unset($_ENV[$key],$_SERVER[$key]);
        }
    putenv('APP_ENV=testing');$_ENV['APP_BASE_PATH']=$base;
    require "$base/vendor/autoload.php";
    $app=require "$base/bootstrap/app.php";
    $app->useEnvironmentPath($dir);$app->loadEnvironmentFrom('absent.env');
    $app->useStoragePath("$dir/storage");$app->useBootstrapPath("$dir/bootstrap");
    $identity=$mode==='bootstrap'?'lab_bootstrap':'lab_app';
    $app->beforeBootstrapping(Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        static function($app)use($dir,$identity):void {
            $app['env']='testing';
            $app['config']->set([
                'app.env'=>'testing','app.debug'=>false,'app.key'=>null,'app.timezone'=>'UTC',
                'database.default'=>'lab','database.connections'=>['lab'=>[
                    'driver'=>'mysql','unix_socket'=>"$dir/mysql.sock",'database'=>'agendaally_synthetic_recovery',
                    'username'=>$identity,'password'=>'','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci',
                    'prefix'=>'','strict'=>true,'timezone'=>'+00:00']],
                'cache.default'=>'array','session.driver'=>'array','queue.default'=>'null','broadcasting.default'=>'null',
                'logging.default'=>'single','logging.channels.single.path'=>"$dir/application.log",
                'development.enabled'=>false,'development.database.owned_sqlite_enabled'=>false,
                'development.payments.mode'=>'disabled','development.sms.mode'=>'disabled',
                'development.email.mode'=>'log','development.email.admin_test_enabled'=>false,
                'development.maps.enabled'=>false,'development.firebase.enabled'=>false,
            ]);
        });
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if($mode==='bootstrap') {
        recoveryAssert((int)$p->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='agendaally_synthetic_recovery'")->fetchColumn()===0,'Fresh empty schema required');
        Illuminate\Support\Facades\DB::listen(static function($q)use($dir):void {
            if(preg_match('/^\s*CREATE TRIGGER\b/i',$q->sql))
                file_put_contents("$dir/bootstrap-ddl.jsonl",json_encode(['sql'=>$q->sql],JSON_THROW_ON_ERROR)."\n",FILE_APPEND|LOCK_EX);
        });
        $m=$app->make('migrator');$m->getRepository()->createRepository();
        foreach(array_keys(recoveryManifest($frozen)) as $name) {
            echo "SETUP $name\n";flush();
            $m->run(["$base/database/migrations/$name.php"],['step'=>true]);
        }
        recoveryAssert(array_column($p->query('SELECT migration FROM migrations ORDER BY id')->fetchAll(),'migration')
            ===array_keys(recoveryManifest($frozen)),'Fresh full native ledger mismatch');
    } else recoveryFixtureMode('fixtures',$p,$app,$root,$state,$side,$custody,true);
    exit;
}
$readOnly=static function()use($p):void {
    recoveryAssert($p->query('SELECT @@global.read_only')->fetchColumn()==='1'
        &&$p->query('SELECT @@global.super_read_only')->fetchColumn()==='1','Physical read-only quarantine required');
};
if(in_array($mode,['point','later','restored','final'],true)) {
    if($mode!=='point')$readOnly();
    $s=recoverySnapshot($p);recoveryPrivilegeContract($p,$s,$state,$side);
    // All triggers were installed in empty setup; no populated upgrade is run.
    recoveryEmittedTriggerContract($s,"$state/source",false);
    recoveryWrite("$dir/$mode.json",$s);
    if($mode==='restored') {
        recoveryCompareRestore($read("$state/source/point.json"),$s,$dir);
        recoveryAssert(count(temporalBindings($s))===8,'Restored uncertain inventory differs');
        recoveryWrite("$dir/equivalence.json",['pass'=>true,'tables'=>count($s['tables']),
            'rowsMetadataLedgerGrants'=>'selected backup point exactly equivalent before any probe']);
    }
    if($mode==='final')recoveryAssert($s===$read("$dir/restored.json"),'Reconciliation or blocked probes changed restored authority');
    exit;
}
if($mode==='backup-preconditions'||$mode==='backup-receipt') {
    $readOnly();recoveryAssert(recoverySnapshot($p)===$read("$dir/point.json"),'Selected backup point changed');
    recoveryAssert($p->query("SELECT COUNT(*) FROM information_schema.events WHERE event_schema='agendaally_synthetic_recovery'")->fetchColumn()==='0','No events allowed');
    if($mode==='backup-receipt') {
        recoveryAssert(!preg_match('/^\s*(?:LOCK TABLES|UNLOCK TABLES)\b/m',file_get_contents("$state/synthetic.sql")),'Restore locks forbidden');
        recoveryWrite("$state/backup-receipt.json",['backupSha256'=>hash_file('sha256',"$state/synthetic.sql"),
            'pointSha256'=>hash_file('sha256',"$dir/point.json"),'completedNs'=>hrtime(true),
            'method'=>'fresh synthetic single-transaction; all InnoDB; read_only/super_read_only; skip-add-locks; triggers; no GTIDs/tablespaces']);
    }
    exit;
}
if($mode==='outcomes') {
    recoveryAssert($p->query('SELECT @@global.read_only')->fetchColumn()==='0','Only fresh synthetic gap source may be writable');
    recoveryAssert(recoverySnapshot($p)===$read("$dir/point.json"),'Pre-gap authority changed');
    $backup=$read("$state/backup-receipt.json");$bindings=temporalBindings($read("$dir/point.json"));
    recoveryAssert(count($bindings)===8,'Expected eight synthetic uncertain identities');
    $key=random_bytes(32);
    recoveryWrite("$custody/outcome-authority.json",['key'=>base64_encode($key)]);
    $records=[];
    foreach($bindings as $i=>$b) {
        $r=$b;unset($r['state'],$r['kind']);
        $r+=['lineage'=>$backup['backupSha256'],'syntheticOnly'=>true,'outcome'=>$b['kind']==='email'?'SENT':'SUCCESS',
            'reference'=>'synthetic-temporal-outcome-'.$i,'backupCompletedNs'=>$backup['completedNs'],'observedNs'=>hrtime(true)];
        $records[]=['record'=>$r,'signature'=>temporalSignature($r,$key)];
    }
    // Independent local simulator observations survive loss of the DB writes.
    // Not provider proof, not SMTP delivery, and not off-host custody.
    recoveryWrite("$custody/outcomes.json",$records);
    $appDb=new PDO("mysql:unix_socket=$dir/mysql.sock;dbname=agendaally_synthetic_recovery;charset=utf8mb4",'lab_app','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $appDb->beginTransaction();
    try {
        foreach($records as $i=>$envelope) {
            $r=$envelope['record'];$now=gmdate('Y-m-d H:i:s');
            switch($r['table']) {
                case 'electronic_collection_attempts':
                    $q=$appDb->prepare("UPDATE electronic_collection_attempts SET state='SUCCESS',provider_reference=?,version=version+1,updated_at=? WHERE id=?");
                    $q->execute([$r['reference'],$now,$r['id']]);break;
                case 'payment_process':
                    $q=$appDb->prepare("UPDATE payment_process SET mtn_dispatch_state='VERIFIED_SUCCESS',mtn_attempt_version=mtn_attempt_version+1 WHERE id=?");
                    $q->execute([$r['id']]);break;
                case 'payment_financial_operations':
                    $q=$appDb->prepare("UPDATE payment_financial_operations SET state='SUCCESS',external_reference=?,completed_at=?,version=version+1,updated_at=? WHERE id=?");
                    $q->execute([$r['reference'],$now,$now,$r['id']]);
                    recoveryInsert($appDb,'payment_receipt_evidence',['id'=>recoveryUuid(200+$i),'operation_id'=>$r['id'],
                        'source'=>'synthetic-temporal','receipt_reference'=>$r['reference'],
                        'document_sha256'=>hash('sha256',json_encode($r,JSON_THROW_ON_ERROR)),
                        'retained_evidence'=>json_encode($envelope,JSON_THROW_ON_ERROR),'received_at'=>$now]);break;
                case 'selected_email_deliveries':
                    $q=$appDb->prepare("UPDATE selected_email_deliveries SET state='SENT',sent_at=?,updated_at=? WHERE id=?");
                    $q->execute([$now,$now,$r['id']]);break;
            }
            recoveryAssert($q->rowCount()===1,'Exactly one synthetic outcome row must change');
        }
        $appDb->commit();
    }catch(Throwable $e){if($appDb->inTransaction())$appDb->rollBack();throw $e;}
    recoveryWrite("$dir/gap-time.json",['backupCompletedNs'=>$backup['completedNs'],'sourceCommitNs'=>hrtime(true),
        'independentOutcomeCount'=>count($records),'sourceTransactions'=>1,'directSyntheticFixturesOnly'=>true]);
    echo "Eight post-backup synthetic observations and eleven changed/inserted DB rows committed; no handlers/transport.\n";exit;
}
if($mode==='restore-preconditions') {
    recoveryAssert((int)$p->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='agendaally_synthetic_recovery'")->fetchColumn()===0,'New empty restore only');
    recoveryAssert(hash_file('sha256',"$state/synthetic.sql")===$read("$state/backup-receipt.json")['backupSha256'],'Fresh backup hash mismatch');
    recoveryWrite("$dir/start.json",['ns'=>hrtime(true)]);exit;
}
if($mode==='reconcile') {
    $readOnly();$s=recoverySnapshot($p);
    recoveryAssert($s===$read("$dir/restored.json"),'Pre-reconciliation restore changed');
    $backup=$read("$state/backup-receipt.json");$bindings=temporalBindings($s);
    $key=base64_decode($read("$custody/outcome-authority.json")['key'],true);
    recoveryAssert(is_string($key)&&strlen($key)===32,'Independent observation authority missing');
    $records=$read("$custody/outcomes.json");
    $results=[];$missing=[];
    foreach($bindings as $b) {
        $missing[]=temporalDecision($b,[],$key,$backup['backupSha256']);
        $result=temporalDecision($b,$records,$key,$backup['backupSha256']);
        recoveryAssert($result['reason']==='INDEPENDENT_OUTCOME_OBSERVED_REQUIRES_OWNER_RECONCILIATION'
            &&$result['decision']==='HOLD'&&!$result['automaticAction']&&!$result['releaseQuarantine'],'No independent authority to replay');
        $results[]=$result;
    }
    // Exercise actual MySQL write denials as both DML runtime and administrative
    // identity. These are write probes, NOT native handlers or network callbacks.
    $sql=[
        'late_callback'=>"UPDATE electronic_collection_attempts SET state='SUCCESS',version=version+1 WHERE id='".recoveryUuid(32)."'",
        'mtn_callback'=>"UPDATE payment_process SET mtn_dispatch_state='VERIFIED_SUCCESS' WHERE id='".recoveryUuid(34)."'",
        'funding'=>"UPDATE payment_collection_contexts SET version=version+1 WHERE id=2",
        'refund'=>"UPDATE payment_financial_operations SET state='SUCCESS',version=version+1 WHERE id='".recoveryUuid(40)."'",
        'payout'=>"UPDATE payment_financial_operations SET state='SUCCESS',version=version+1 WHERE id='".recoveryUuid(42)."'",
        'email_resend'=>"UPDATE selected_email_deliveries SET state='SENT',sent_at=UTC_TIMESTAMP() WHERE id='".recoveryUuid(70)."'",
        'replacement_intent'=>"UPDATE payment_financial_operations SET request_key='".recoveryUuid(999)."' WHERE id='".recoveryUuid(41)."'",
    ];
    $denials=[];
    foreach(['lab_app','root'] as $user) {
        $db=new PDO("mysql:unix_socket=$dir/mysql.sock;dbname=agendaally_synthetic_recovery;charset=utf8mb4",$user,'',
            [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        foreach($sql as $action=>$statement) {
            $denied=false;
            try{$db->exec($statement);}catch(PDOException $e){$denied=($e->errorInfo[1]??null)===1290;}
            recoveryAssert($denied,"Physical quarantine failed: $user/$action");
            $denials[]=['identity'=>$user,'action'=>$action,'mysqlCode'=>1290,'persistedWrites'=>0];
        }
    }
    recoveryAssert(recoverySnapshot($p)===$s,'Quarantine/reconciliation probes mutated DB');
    recoveryWrite("$dir/reconciliation.json",['pass'=>true,'withoutIndependentEvidence'=>$missing,
        'withIndependentEvidence'=>$results,'decision'=>'HOLD ALL','automaticActions'=>0,
        'providerSmtpWorkers'=>'NONE','evidenceScope'=>'local synthetic simulator; not real external or off-host evidence']);
    recoveryWrite("$dir/quarantine-denials.json",['pass'=>true,'readOnly'=>true,'superReadOnly'=>true,'denials'=>$denials]);
    exit;
}
if($mode==='preservation') {
    $readOnly();$s=recoverySnapshot($p);$old=$read("$dir/later.json");
    recoveryWrite("$dir/preservation.json",$s);
    $diff=[];
    foreach($old as $name=>$value)if($value!==$s[$name])$diff[$name]=['before'=>$value,'after'=>$s[$name]];
    recoveryWrite("$dir/preservation-raw-differences.json",$diff);
    recoveryAssert($diff===[]||(array_keys($diff)===['indexes']
        &&recoveryMetadataContract($s)===recoveryMetadataContract($old)),
        'Post-point source changed beyond sampled optimizer cardinality');
    recoveryWrite("$state/protected-after.json",recoverySource($root));
    recoveryWrite("$state/previous-after.json",temporalPreviousCustody($root,$continuation));
    recoveryAssert($read("$state/protected-before.json")===$read("$state/protected-after.json")
        &&$read("$state/previous-before.json")===$read("$state/previous-after.json"),'Protected/prior evidence changed');
    exit;
}
if($mode==='assessment') {
    $readOnly();
    $point=$read("$state/source/point.json");$later=$read("$state/source/later.json");
    temporalAssertOnlyOutcomeChanges($point,$later);
    $lost=temporalLostWrites($point,$later);
    $counts=['electronic_collection_attempts'=>2,'payment_process'=>1,'payment_financial_operations'=>3,
        'payment_receipt_evidence'=>3,'selected_email_deliveries'=>2];
    recoveryAssert(count($lost['tables'])===5&&$lost['changedOrInsertedRows']===11,'Expected nonzero lost-write exposure not measured');
    foreach($counts as $name=>$n) {
        recoveryAssert(count($lost['tables'][$name]['newRowsMissingFromBackup'])===$n
            &&count($lost['tables'][$name]['oldRowsMissing'])===($name==='payment_receipt_evidence'?0:$n),
            'Unexpected temporal update/insert count');
    }
    recoveryAssert(recoveryMetadataContract($point)===recoveryMetadataContract($later)&&$point['ledger']===$later['ledger']
        &&$point['grants']===$later['grants'],'Gap changed schema/ledger/grants');
    $gap=$read("$state/source/gap-time.json");
    recoveryAssert($gap['sourceCommitNs']>$gap['backupCompletedNs'],'No post-backup commit measured');
    foreach(['source'=>array_merge(['freeze'],$continuation?['continuation-preconditions','clone-preconditions','clone-point']:['bootstrap','fixtures'],
            ['definer','point','backup-preconditions','backup-receipt','outcomes','later','preservation']),
        'restore'=>['restore-preconditions','definer','restored','reconcile','final']] as $which=>$stages)
        foreach($stages as $stage)recoveryAssert($read("$state/$which/$stage-exit.json")['childExit']===0,'Actual temporal child failed');
    recoveryAssert($read("$state/dump-exit.json")['childExit']===0
        &&$read("$state/restore/import-exit.json")['childExit']===0,'Backup/import child failed');
    recoveryWrite("$state/lost-writes.json",$lost);
    recoveryWrite("$state/assessment.json",['status'=>'LOCAL_SYNTHETIC_TEMPORAL_RECOVERY_PASS','outcomesNewerThanBackup'=>8,
        'lostChangedOrInsertedRows'=>11,'lostCommitTransactions'=>1,'lostWriteTables'=>$counts,
        'gapSeconds'=>($gap['sourceCommitNs']-$gap['backupCompletedNs'])/1e9,
        'reconciliationElapsedSeconds'=>(hrtime(true)-$read("$dir/start.json")['ns'])/1e9,
        'exposure'=>['collectionUnits'=>1500,'refundUnits'=>500,'payoutUnits'=>400,'emailOutcomes'=>2,'currency'=>'XTS','scale'=>2,
            'meaning'=>'possible repeated effects from stale identities; no actual money or emails; categories are not an account balance'],
        'restore'=>'all selected-point rows/metadata/ledger/grants equivalent; PENDING/UNKNOWN/RESERVED still unchanged',
        'quarantine'=>'physical read_only/super_read_only; no HTTP app, handlers, callbacks, transports or workers',
        'reconciliation'=>'independently observed outcomes retained; HOLD ALL; no automatic replay or writeback',
        'previousEvidence'=>'preserved where available; absence explicitly retained, never recreated',
        'continuedInNewTargets'=>$continuation,
        'production'=>'NOT QUALIFIED','offHostCustody'=>'NOT TESTED','realExternalOutcomes'=>'NOT TESTED']);
    echo "PASS: nonzero temporal loss measured; restore held unchanged; independent observations required, no replay.\n";exit;
}
throw new RuntimeException('No temporal action matched');
