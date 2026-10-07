<?php
declare(strict_types=1);
// Fixed owned campaign only; no environment credentials, server URL or arbitrary path.
require __DIR__.'/mysql-recovery-evidence.php';
$root=dirname(__DIR__,2);
$state="$root/.local/mysql-synthetic-recovery";
$base="$root/.local/clean-publication-validation/.migration-backup/backend";
$frozen="$root/.local/agendaally-clean-repository/.migration-backup/backend";
$upgrade='2026_10_10_010000_add_manual_financial_workflows';
$mode=$argv[1]??'';$side=$argv[2]??'';
recoveryAssert(count($argv)===3 && in_array($side,['source','restore','restore-retry'],true)
    && realpath($state)===$state && !is_link($state) && (fileperms($state)&0777)===0700
    && fileowner($state)===posix_geteuid() && is_file("$state/approval.txt"),'Explicit owned synthetic campaign required');
recoveryAssert(in_array($mode,['freeze','bootstrap','definer','fixtures','before','qualify','upgrade','after',
    'compare-upgrade','probe','backup-preconditions','backup-receipt','restore-preconditions','restored',
    'compare-restore','recover-keys','invalidate','final','preservation','assessment',
    'retry-freeze','retry-backup-preconditions','retry-backup-receipt','retry-preservation'],true),'Unknown campaign mode refused');
foreach(['curl_exec','curl_multi_exec','fsockopen','pfsockopen','stream_socket_client','socket_connect',
    'mail','exec','shell_exec','proc_open','popen','system','passthru'] as $f)
    recoveryAssert(!function_exists($f),'Transports/process execution must be disabled');
$dir="$state/$side";
if($side==='restore-retry'||str_starts_with($mode,'retry-'))
    recoveryAssert(is_file("$state/retry/approval.txt")&&realpath("$state/retry")==="$state/retry"
        &&(fileperms("$state/retry")&0777)===0700,'Separate third-instance retry approval required');
if($side==='restore-retry')
    recoveryAssert(in_array($mode,['definer','restore-preconditions','restored','compare-restore','recover-keys',
        'probe','invalidate','final','assessment'],true),'No bootstrap or populated upgrade is allowed in recovery retry');
if($mode==='retry-freeze') {
    recoveryAssert($side==='source'&&!file_exists("$state/source/mysql.sock")&&!file_exists("$state/restore/mysql.sock"),'All previous lab instances must be stopped');
    recoveryWrite("$state/retry/failed-before.json",recoveryFailedTree($state));
    recoveryAssert(recoverySource($root)===json_decode(file_get_contents("$state/source-before.json"),true,512,JSON_THROW_ON_ERROR),'Protected source changed before retry');
    echo "Failed restore byte/permission custody frozen; no server contacted.\n";exit;
}
if($mode==='freeze') {
    recoveryWrite("$state/source-before.json",recoverySource($root));
    $manifest=recoveryManifest($frozen);
    recoveryWrite("$state/migration-hashes.json",$manifest);
    recoveryAssert(array_key_last($manifest)===$upgrade,'Additive upgrade must be exactly final source migration');
    $delegates=[];
    foreach(["database/migrations/$upgrade.php",'app/Services/ManualFinance/ManualSchema.php',
        'app/Services/ManualFinance/FinanceScope.php'] as $path)$delegates[$path]=hash_file('sha256',"$frozen/$path");
    recoveryWrite("$state/reviewed-upgrade.json",[
        'allowlist'=>[$upgrade],'hashes'=>$delegates,
        'historicalLedger'=>array_slice(array_keys($manifest),0,-1),
        'review'=>'Source reviewed: seven NEW manual_* tables, native CHECK/FK/index/retention triggers; insertOrIgnore twelve Finance permission DEFINITIONS in two catalogs; NO grants, no balance writes, no transformations or transport. Full down() forbidden.',
        'allowedOldRowChanges'=>['migrations'=>'one actual native ledger append','country_permissions'=>'missing exact FinanceScope definitions only','permissions'=>'missing exact web-guard FinanceScope definitions only'],
        'newTables'=>['manual_financial_workflows','manual_financial_commands','manual_financial_events',
            'manual_financial_attachments','manual_financial_evidence','manual_financial_notifications','manual_financial_evidence_access'],
    ]);
    echo "Frozen complete historical source and exact one-file additive allowlist.\n";exit;
}
$p=recoveryRoot($state,$side);
if($mode==='definer') {recoveryDefiner($p,$dir);echo "Narrow reviewed definer grants prepared.\n";exit;}
// Fail before bootstrapping if source changes at any campaign phase.
recoveryAssert(recoverySource($root)===json_decode(file_get_contents("$state/source-before.json"),true,512,JSON_THROW_ON_ERROR),'Protected source drift');
recoveryAssert(recoveryManifest($frozen)===json_decode(file_get_contents("$state/migration-hashes.json"),true,512,JSON_THROW_ON_ERROR),'Historical hash mismatch');
$read=static fn($path)=>json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
$ledger=static fn()=>array_column($p->query('SELECT migration FROM migrations ORDER BY id')->fetchAll(),'migration');
$expected=array_keys(recoveryManifest($frozen));$prior=array_slice($expected,0,-1);
$needsApp=in_array($mode,['bootstrap','upgrade','fixtures','before','probe','recover-keys','invalidate'],true);
if($needsApp) {
    foreach(array_unique(array_merge(array_keys($_SERVER),array_keys($_ENV))) as $key)
        if(preg_match('/^(APP_|DB_|DATABASE_URL$|AGENDAALLY_|DEVELOPMENT_|MAIL_|AWS_|SESSION_|CACHE_|QUEUE_|REDIS_)/',$key)){
            putenv($key);unset($_ENV[$key],$_SERVER[$key]);
        }
    putenv('APP_ENV=testing');$_ENV['APP_BASE_PATH']=$base;
    require "$base/vendor/autoload.php";
    $app=require "$base/bootstrap/app.php";
    $app->useEnvironmentPath($dir);$app->loadEnvironmentFrom('absent.env');
    $app->useStoragePath("$dir/storage");$app->useBootstrapPath("$dir/bootstrap");
    $identity=in_array($mode,['bootstrap','upgrade'],true)?'lab_bootstrap':'lab_app';
    $app->beforeBootstrapping(Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        static function($app)use($dir,$identity):void {
            $app['env']='testing';
            $app['config']->set([
                'app.env'=>'testing','app.debug'=>false,'app.key'=>null,'app.timezone'=>'UTC',
                'database.default'=>'lab','database.connections'=>['lab'=>[
                    'driver'=>'mysql','unix_socket'=>"$dir/mysql.sock",'database'=>'agendaally_synthetic_recovery',
                    'username'=>$identity,'password'=>'','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci',
                    'prefix'=>'','strict'=>true,'timezone'=>'+00:00',
                ]],
                'cache.default'=>'array','session.driver'=>'database','session.connection'=>'lab',
                'queue.default'=>'null','broadcasting.default'=>'null',
                'logging.default'=>'single','logging.channels.single.path'=>"$dir/application.log",
                'development.enabled'=>false,'development.database.owned_sqlite_enabled'=>false,
                'development.payments.mode'=>'disabled','development.sms.mode'=>'disabled',
                'development.email.mode'=>'log','development.email.admin_test_enabled'=>false,
                'development.maps.enabled'=>false,'development.firebase.enabled'=>false,
            ]);
        });
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    Illuminate\Support\Facades\DB::listen(static function($q)use($dir,$mode):void {
        if(preg_match('/^\s*(CREATE|ALTER|DROP)\b/i',$q->sql))
            file_put_contents("$dir/$mode-ddl.jsonl",json_encode(['sql'=>$q->sql],JSON_THROW_ON_ERROR)."\n",FILE_APPEND|LOCK_EX);
    });
}
if($mode==='bootstrap') {
    recoveryAssert($side==='source' && (int)$p->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='agendaally_synthetic_recovery'")->fetchColumn()===0,'Empty NEW baseline only');
    $m=$app->make('migrator');$m->getRepository()->createRepository();
    recoveryWrite("$dir/bootstrap-engine.json",(array)Illuminate\Support\Facades\DB::selectOne(
        'SELECT @@version version,@@sql_mode sql_mode,@@session.time_zone timezone,
        @@transaction_isolation isolation_level,@@character_set_database charset,@@collation_database collation_name'));
    foreach($prior as $name) {
        echo "RUN $name\n";flush();
        $m->run(["$base/database/migrations/$name.php"],['step'=>true]);
    }
    recoveryAssert($ledger()===$prior,'Full 228-entry native baseline ledger mismatch');
    recoveryWrite("$dir/bootstrap-ledger.json",$p->query('SELECT * FROM migrations ORDER BY id')->fetchAll());
    echo "228-migration baseline PASS.\n";exit;
}
if($mode==='upgrade') {
    recoveryAssert($side==='source' && $ledger()===$prior,'Historical ledger differs; NEVER replay historic files');
    recoveryAssert($read("$dir/qualified.json")['allowlist']===[$upgrade],'Qualified allowlist required');
    recoveryAssert(recoverySnapshot($p)['tables']===$read("$dir/before.json")['tables'],'Populated snapshot changed after qualification');
    $engine=(array)Illuminate\Support\Facades\DB::selectOne('SELECT @@version version,@@sql_mode sql_mode,
        @@session.time_zone timezone,@@transaction_isolation isolation_level,@@character_set_database charset,@@collation_database collation_name');
    recoveryAssert($engine===$read("$dir/before-engine.json"),'Frozen pre-upgrade native session contract changed');
    recoveryWrite("$dir/upgrade-engine.json",$engine);
    $app->make('migrator')->run(["$base/database/migrations/$upgrade.php"],['step'=>true]);
    recoveryAssert($ledger()===$expected,'Upgraded full ledger mismatch');
    echo "Exact additive native upgrade applied.\n";exit;
}
if(in_array($mode,['fixtures','probe','recover-keys','invalidate'],true)) {
    require __DIR__.'/mysql-recovery-fixtures.php';
    recoveryFixtureMode($mode,$p,$app,$root,$state,$side);
    exit;
}
if(in_array($mode,['before','after','restored','final'],true)) {
    if(in_array($mode,['before','after'],true)) {
        recoveryWrite("$dir/$mode-definer-exit.json",$read("$dir/definer-exit.json"));
        recoveryAssert(copy("$dir/revoke.log","$dir/$mode-revoke.log"),'Definer revocation log retention failed');
        chmod("$dir/$mode-revoke.log",0600);
    }
    if($mode==='before')recoveryWrite("$dir/before-engine.json",(array)Illuminate\Support\Facades\DB::selectOne(
        'SELECT @@version version,@@sql_mode sql_mode,@@session.time_zone timezone,
        @@transaction_isolation isolation_level,@@character_set_database charset,@@collation_database collation_name'));
    $snapshot=recoverySnapshot($p);
    recoveryPrivilegeContract($p,$snapshot,$state,$side);
    recoveryEmittedTriggerContract($snapshot,"$state/source",$mode!=='before');
    recoveryWrite("$dir/$mode.json",$snapshot);
    recoveryWrite("$dir/$mode-invariants.json",recoveryInvariants($p));
    echo "Consistent schema/rows/authority snapshot retained: $mode.\n";exit;
}
if($mode==='qualify') {
    recoveryAssert($ledger()===$prior,'Full historical ledger precondition failed');
    recoveryAssert($p->query('SELECT * FROM migrations ORDER BY id')->fetchAll()===$read("$dir/bootstrap-ledger.json"),
        'Historical ledger IDs/batches changed since completed baseline');
    $s=$read("$dir/before.json");$review=$read("$state/reviewed-upgrade.json");
    foreach($review['newTables'] as $t)recoveryAssert(!isset($s['tables'][$t]),'Non-additive pending table already exists');
    recoveryAssert($read("$dir/before-invariants.json")['principalViolations']===[],'Principal conservation precondition');
    // Explicitly inspect native FK relationships, including nullable/composite keys.
    $groups=[];
    foreach($s['foreignKeys'] as $fk)if($fk['referenced_table_name']!==null)
        $groups[$fk['table_name'].'|'.$fk['constraint_name']][]=$fk;
    foreach($groups as $fks) {
        $t=$fks[0]['table_name'];$parent=$fks[0]['referenced_table_name'];$join=[];$nonnull=[];
        foreach($fks as $fk){$c=$fk['column_name'];$r=$fk['referenced_column_name'];$join[]="c.`$c`=p.`$r`";$nonnull[]="c.`$c` IS NOT NULL";}
        $sql="SELECT COUNT(*) FROM `$t` c WHERE ".implode(' AND ',$nonnull)." AND NOT EXISTS (SELECT 1 FROM `$parent` p WHERE ".implode(' AND ',$join).')';
        recoveryAssert((int)$p->query($sql)->fetchColumn()===0,"Orphan precondition: $t");
    }
    foreach(['payment_financial_operations'=>'allocation_id,kind,request_key','electronic_collection_attempts'=>'funding_event_key',
        'payment_receipt_evidence'=>'operation_id','payment_payloads'=>'payment_id'] as $t=>$cols)
        recoveryAssert($p->query("SELECT $cols FROM `$t` GROUP BY $cols HAVING COUNT(*)>1")->fetchAll()===[],"Duplicate identity: $t");
    recoveryAssert((int)$p->query('SELECT COUNT(*) FROM payment_financial_operations WHERE amount_units<=0')->fetchColumn()===0,'Invalid original-linked reservation amount');
    recoveryAssert($p->query("SELECT * FROM payment_financial_operations WHERE kind='refund' AND (original_payment_id IS NULL OR context_id IS NULL OR revision_id IS NULL)")->fetchAll()===[],'Missing original refund authority');
    recoveryAssert($p->query("SELECT a.id FROM commerce_payment_allocations a JOIN payment_financial_operations o ON o.allocation_id=a.id
        WHERE o.kind='refund' AND o.state IN ('RESERVED','PENDING','UNKNOWN','SUCCESS')
        GROUP BY a.id,a.gross_amount HAVING SUM(o.amount_units)>a.gross_amount")->fetchAll()===[],'Original-linked refund principal over-reserved');
    recoveryAssert($p->query("SELECT a.id FROM commerce_payment_allocations a JOIN payment_financial_operations o ON o.allocation_id=a.id
        WHERE o.kind='payout' AND o.state IN ('RESERVED','PENDING','UNKNOWN','SUCCESS')
        GROUP BY a.id,a.original_vendor_payable HAVING SUM(o.amount_units)>a.original_vendor_payable")->fetchAll()===[],'Vendor payable over-reserved');
    foreach($s['columns'] as $c)if(in_array($c['table_name'],['commerce_payment_allocations','payment_collection_contexts','payment_financial_operations'],true)
        &&in_array($c['column_name'],['gross_amount','commission_amount','vendor_entitlement_amount','adjustment_amount','amount','amount_units','receipt_total_amount'],true))
        recoveryAssert($c['data_type']==='bigint'&&!str_contains($c['column_type'],'unsigned'),'Native signed integer monetary authority changed');
    recoveryWrite("$dir/qualified.json",['allowlist'=>[$upgrade],'historicalEntries'=>count($prior),
        'migrationHashes'=>$read("$state/migration-hashes.json"),'orphanConstraintsChecked'=>count($groups),
        'duplicates'=>'absent','negativeFractionalAuthority'=>'native BIGINT/CHECK + positive reservation and component checks',
        'noTransforms'=>true,'baselineSnapshotSha256'=>hash_file('sha256',"$dir/before.json")]);
    echo "Exact populated additive preconditions qualified.\n";exit;
}
if($mode==='compare-upgrade') {
    require "$base/vendor/autoload.php";
    require __DIR__.'/mysql-recovery-compare.php';
    recoveryCompareUpgrade($read("$dir/before.json"),$read("$dir/after.json"),$read("$state/reviewed-upgrade.json"));
    recoveryAssert($read("$dir/before-invariants.json")===$read("$dir/after-invariants.json"),'Financial or permission grant authority changed');
    recoveryWrite("$dir/upgrade-comparison.json",['pass'=>true,'oldAuthority'=>'identical','exceptions'=>'one native ledger append; twelve definitions in each permission catalog; seven new empty tables and their exact emitted native guards']);
    echo "Populated upgrade preservation PASS.\n";exit;
}
if(in_array($mode,['backup-preconditions','retry-backup-preconditions'],true)) {
    recoveryAssert($side==='source' && $ledger()===$expected,'Full upgrade ledger required for backup');
    $s=recoverySnapshot($p);$old=$read("$dir/after.json");
    recoveryAssert($s['tables']===$old['tables']&&recoveryMetadataContract($s)===recoveryMetadataContract($old)
        &&$s['ledger']===$old['ledger']&&$s['accounts']===$old['accounts']&&$s['grants']===$old['grants'],'Source changed before consistent backup');
    if($mode==='retry-backup-preconditions') {
        recoveryAssert($p->query('SELECT @@global.read_only')->fetchColumn()==='1'
            &&$p->query('SELECT @@global.super_read_only')->fetchColumn()==='1','Source must remain read-only for bounded backup retry');
        recoveryPrivilegeContract($p,$s,$state,$side);
        recoveryWrite("$state/retry/source-at-backup.json",$s);
        recoveryWrite("$state/retry/source-at-backup-invariants.json",recoveryInvariants($p));
        recoveryAssert(recoveryInvariants($p)===$read("$dir/after-invariants.json"),'Source financial/access authority changed before backup retry');
    }
    recoveryAssert($p->query("SELECT COUNT(*) FROM information_schema.events WHERE event_schema='agendaally_synthetic_recovery'")->fetchColumn()==='0','No events allowed');
    echo "Quiescent synthetic backup preconditions PASS.\n";exit;
}
if(in_array($mode,['backup-receipt','retry-backup-receipt'],true)) {
    $retry=$mode==='retry-backup-receipt';$backupDir=$retry?"$state/retry":$state;
    $point=$retry?"$backupDir/source-at-backup.json":"$dir/after.json";
    recoveryAssert(recoverySnapshot($p)===$read($point),'Backup not at selected unchanged recovery point');
    if($retry) {
        recoveryAssert($p->query('SELECT @@global.super_read_only')->fetchColumn()==='1','Read-only source backup guard failed');
        recoveryAssert(!preg_match('/^\s*(?:LOCK TABLES|UNLOCK TABLES)\b/m',file_get_contents("$backupDir/synthetic.sql")),'Corrected dump still emits restore-side locks');
    }
    recoveryWrite("$backupDir/backup-receipt.json",['method'=>'mysqldump single-transaction, all InnoDB writers quiescent, synthetic schema only; no GTID or tablespaces; triggers retained'.($retry?'; source read_only/super_read_only ON; skip-add-locks (restore lock commands omitted)':''),
        'backupSha256'=>hash_file('sha256',"$backupDir/synthetic.sql"),'snapshotSha256'=>hash_file('sha256',$point),
        'migrationHashesSha256'=>hash_file('sha256',"$state/migration-hashes.json"),
        'time'=>$read("$backupDir/backup-time.json")]);
    echo "Selected-point logical backup receipt retained.\n";exit;
}
if($mode==='restore-preconditions') {
    $backupDir=$side==='restore-retry'?"$state/retry":$state;
    recoveryAssert(in_array($side,['restore','restore-retry'],true) && (int)$p->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='agendaally_synthetic_recovery'")->fetchColumn()===0,'Restore target must be a new empty owned instance');
    recoveryAssert(hash_file('sha256',"$backupDir/synthetic.sql")===$read("$backupDir/backup-receipt.json")['backupSha256'],'Synthetic backup hash mismatch');
    recoveryWrite("$dir/restore-start.json",['ns'=>(string)hrtime(true),'wallNs'=>sprintf('%.0f',microtime(true)*1e9)]);
    echo "New isolated target and exact synthetic backup qualified.\n";exit;
}
if($mode==='compare-restore') {
    require __DIR__.'/mysql-recovery-compare.php';
    recoveryCompareRestore($read("$state/source/after.json"),$read("$dir/restored.json"),"$dir");
    recoveryAssert($read("$state/source/after-invariants.json")===$read("$dir/restored-invariants.json"),'Restored financial/access invariant mismatch');
    recoveryAssert($ledger()===$expected,'Restored ledger mismatch');
    recoveryWrite("$dir/restore-comparison.json",['pass'=>true,'rows'=>'all exact hashes/counts','metadata'=>'exact native contracts; only sampled index cardinality/trigger creation time excluded from definitions',
        'rpo'=>'zero synthetic writes between selected backup point and source shutdown; NOT production RPO',
        'restoreElapsedSeconds'=>(hrtime(true)-(int)$read("$dir/restore-start.json")['ns'])/1e9]);
    echo "Isolated restored equivalence PASS.\n";exit;
}
if($mode==='preservation') {
    recoveryWrite("$state/source-after.json",recoverySource($root));
    recoveryAssert($read("$state/source-after.json")===$read("$state/source-before.json"),'Protected source changed');
    recoveryAssert(recoverySnapshot($p)===$read("$dir/after.json"),'Source authority changed during recovery');
    echo "Original source and quiescent source lab preserved.\n";exit;
}
if($mode==='retry-preservation') {
    recoveryAssert($side==='source'&&$p->query('SELECT @@global.super_read_only')->fetchColumn()==='1','Source retry preservation must be read-only');
    recoveryAssert(recoverySnapshot($p)===$read("$state/retry/source-at-backup.json"),'Read-only source changed during corrected backup');
    recoveryWrite("$state/retry/source-after.json",recoverySource($root));
    recoveryAssert($read("$state/retry/source-after.json")===$read("$state/source-before.json"),'Protected source changed during backup retry');
    echo "Read-only source financial/access/schema/row preservation PASS.\n";exit;
}
if($mode==='assessment') {
    recoveryAssert(in_array($side,['restore','restore-retry'],true),'Final assessment requires restored side');
    foreach([['upgrade-comparison','source'],['restore-comparison',$side],['probe','source'],['probe',$side],
        ['recover-keys',$side],['invalidate',$side]] as [$file,$which])
        recoveryAssert(($read("$state/$which/$file.json")['pass']??false)===true,"Missing accepted $which $file");
    if($side==='restore-retry') {
        recoveryWrite("$state/retry/failed-after.json",recoveryFailedTree($state));
        recoveryAssert($read("$state/retry/failed-before.json")===$read("$state/retry/failed-after.json"),'Failed original restore was modified');
        recoveryAssert(recoverySource($root)===$read("$state/source-before.json"),'Protected source changed after recovery retry');
    }
    recoveryWrite("$state/assessment.json",['status'=>'LOCAL_SYNTHETIC_UPGRADE_RECOVERY_PASS',
        'scope'=>'228 to 229 frozen native migrations, exact single additive upgrade, NEW socket-only synthetic logical restore',
        'restoreSide'=>$side,'backupDirectory'=>$side==='restore-retry'?'retry':'.',
        'production'=>'NOT QUALIFIED','offHostCustody'=>'NOT TESTED; local separate key custody only',
        'financialOperations'=>'NONE; direct synthetic fixtures, no service execution','providerSmtpWorkers'=>'DISABLED',
        'existingSeparateTasks'=>'callback concurrency, full HTTP finance sessions and normal database warning not reopened',
        'recoveryReadyElapsedSeconds'=>(hrtime(true)-(int)$read("$dir/restore-start.json")['ns'])/1e9,
        'rpo'=>'0 synthetic writes lost at frozen selected point; no production RPO or external-outcome certification']);
    echo "Local synthetic campaign PASS; no production recovery claim.\n";exit;
}
throw new RuntimeException('Unknown mode; no action taken');
