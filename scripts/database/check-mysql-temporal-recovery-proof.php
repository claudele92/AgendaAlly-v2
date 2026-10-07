<?php
declare(strict_types=1);
// Retained proof only. No DB/application boot and no external access.
require __DIR__.'/mysql-recovery-evidence.php';
require __DIR__.'/mysql-recovery-compare.php';
require __DIR__.'/mysql-temporal-recovery-policy.php';
$root=dirname(__DIR__,2);
$continued=file_exists("$root/.local/mysql-temporal-recovery-completed");
$state="$root/.local/".($continued?'mysql-temporal-recovery-completed':'mysql-temporal-recovery-qualified');
$diagnostic=file_exists("$root/.local/mysql-temporal-recovery-diagnostic");
$evidence=$diagnostic?"$root/.local/mysql-temporal-recovery-diagnostic":$state;
recoveryAssert(count($argv)===1&&realpath($state)===$state&&!is_link($state)
    &&(fileperms($state)&0777)===0700&&fileowner($state)===posix_geteuid(),'Owned temporal proof required');
recoveryAssert(realpath($evidence)===$evidence&&!is_link($evidence)
    &&(fileperms($evidence)&0777)===0700&&fileowner($evidence)===posix_geteuid(),'Owned final qualification evidence required');
$read=static function(string $path):array {
    recoveryAssert(is_file($path)&&!is_link($path)&&(fileperms($path)&0777)===0600,'Private temporal receipt absent');
    return json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
};
$a=$read("$evidence/assessment.json");
recoveryAssert($a['status']===($diagnostic?'LOCAL_SYNTHETIC_TEMPORAL_RECOVERY_PASS_WITH_DIAGNOSTIC_QUALIFICATION'
    :'LOCAL_SYNTHETIC_TEMPORAL_RECOVERY_PASS'),'Temporal campaign not accepted');
recoveryAssert($a['continuedInNewTargets']===$continued,'Assessment/selected campaign mismatch; no failed proof fallback');
if($diagnostic) {
    recoveryAssert($continued&&$read("$state/supervisor-exit.json")['supervisorExit']===92
        &&$read("$state/source/preservation-exit.json")['childExit']===255
        &&$read("$evidence/supervisor-exit.json")['supervisorExit']===0,'Real failed/diagnostic exits changed');
    foreach(['before','relocate','snapshot','after'] as $stage)
        recoveryAssert($read("$evidence/$stage-exit.json")['childExit']===0,'Diagnostic child failed');
    recoveryAssert(!file_exists("$evidence/source/mysql.sock")&&!file_exists("$evidence/source/mysql.pid"),'Diagnostic still listening');
    recoveryAssert($read("$evidence/failed-before.json")===$read("$evidence/failed-after.json")
        &&temporalEvidenceTree($state)===$read("$evidence/failed-before.json"),'Failed campaign custody changed');
    recoveryAssert($read("$evidence/relocation.json")['initialCopyAllBytesExact']
        &&$read("$evidence/diagnosis.json")['pass'],'Read-only synthetic copy qualification absent');
    foreach(['source','restore'] as $side)foreach(['emergency-revoke','final-revoke'] as $log)
        recoveryAssert(str_contains(file_get_contents("$state/$side/$log.log"),'ERROR 1290')
            &&str_contains(file_get_contents("$state/$side/$log.log"),'--super-read-only'),'Unexplained cleanup failure');
} else recoveryAssert($read("$state/supervisor-exit.json")['supervisorExit']===0,'Supervisor cleanup failed');
foreach(['source'=>array_merge(['freeze'],$continued?['continuation-preconditions','clone-preconditions','clone-point']:['bootstrap','fixtures'],
        ['definer','point','backup-preconditions','backup-receipt','outcomes','later'],$diagnostic?[]:['preservation']),
    'restore'=>array_merge(['restore-preconditions','definer','restored','reconcile','final'],$diagnostic?[]:['assessment'])] as $side=>$stages) {
    recoveryAssert(!file_exists("$state/$side/mysql.sock")&&!file_exists("$state/$side/mysql.pid"),'Temporal server still running');
    foreach($stages as $stage)recoveryAssert($read("$state/$side/$stage-exit.json")['childExit']===0,"Real child failure: $side/$stage");
}
recoveryAssert($read("$state/dump-exit.json")['childExit']===0&&$read("$state/restore/import-exit.json")['childExit']===0,'Dump/import failure');
$backup=$read("$state/backup-receipt.json");
recoveryAssert(hash_file('sha256',"$state/synthetic.sql")===$backup['backupSha256']
    &&hash_file('sha256',"$state/source/point.json")===$backup['pointSha256'],'Backup lineage hash changed');
$point=$read("$state/source/point.json");$later=$read("$state/source/later.json");
temporalAssertOnlyOutcomeChanges($point,$later);
if($diagnostic) {
    $copy=$read("$evidence/source/snapshot.json");$diff=[];$cardinalityChanges=0;
    foreach($later as $name=>$value)if($value!==$copy[$name])$diff[$name]=['before'=>$value,'after'=>$copy[$name]];
    recoveryAssert(array_keys($diff)===['indexes']&&$diff===$read("$evidence/raw-source-differences.json")
        &&recoveryMetadataContract($later)===recoveryMetadataContract($copy),'Real post-point authority difference or unexplained diagnostic mismatch');
    foreach($later['indexes'] as $i=>$row)if($row['cardinality']!==$copy['indexes'][$i]['cardinality'])$cardinalityChanges++;
    recoveryAssert($cardinalityChanges===6&&$read("$evidence/diagnosis.json")['changedCardinalityEntries']===$cardinalityChanges,
        'Declared optimizer-only difference incorrect');
}
$restored=$read("$state/restore/restored.json");$final=$read("$state/restore/final.json");
recoveryAssert($restored===$final,'Restored uncertain evidence changed during passive reconciliation');
recoveryAssert(array_keys($point['tables'])===array_keys($restored['tables']),'Restored table inventory changed');
foreach($point['tables'] as $t=>$row) {
    recoveryAssert($row['sha256']===$restored['tables'][$t]['sha256']&&$row['count']===$restored['tables'][$t]['count'],'Restored row mismatch');
    recoveryAssert(recoveryCanonicalDdl($row['ddl'])===recoveryCanonicalDdl($restored['tables'][$t]['ddl']),'Restored DDL mismatch');
}
recoveryAssert(recoveryMetadataContract($point)===recoveryMetadataContract($restored)
    &&$point['ledger']===$restored['ledger']&&$point['grants']===$restored['grants']&&$point['accounts']===$restored['accounts'],'Restored authority mismatch');
recoveryAssert($read("$state/migration-hashes.json")===recoveryManifest("$root/.local/agendaally-clean-repository/.migration-backup/backend")
    &&array_column($point['ledger'],'migration')===array_keys($read("$state/migration-hashes.json")),'Native ledger/source hashes changed');
$lost=temporalLostWrites($point,$later);
recoveryAssert($lost===$read("$evidence/lost-writes.json")&&$lost['changedOrInsertedRows']===11
    &&$a['lostChangedOrInsertedRows']===11&&$a['outcomesNewerThanBackup']===8&&$a['lostCommitTransactions']===1,'Nonzero loss receipt incorrect');
$counts=['electronic_collection_attempts'=>2,'payment_process'=>1,'payment_financial_operations'=>3,
    'payment_receipt_evidence'=>3,'selected_email_deliveries'=>2];
recoveryAssert(count($lost['tables'])===5&&$a['lostWriteTables']===$counts,'Lost-write allowlist differs');
foreach($counts as $t=>$n)recoveryAssert(count($lost['tables'][$t]['newRowsMissingFromBackup'])===$n
    &&count($lost['tables'][$t]['oldRowsMissing'])===($t==='payment_receipt_evidence'?0:$n),'Lost-write update/insert count differs');
recoveryAssert(recoveryMetadataContract($point)===recoveryMetadataContract($later)
    &&$point['ledger']===$later['ledger']&&$point['grants']===$later['grants'],'Source gap changed schema/grants');
$gap=$read("$state/source/gap-time.json");
recoveryAssert($gap['backupCompletedNs']===$backup['completedNs']&&$gap['sourceCommitNs']>$backup['completedNs']
    &&$a['gapSeconds']===($gap['sourceCommitNs']-$backup['completedNs'])/1e9,'Temporal gap not measured correctly');
$custody="$root/.local/".($continued?'mysql-temporal-key-custody-completed':'mysql-temporal-key-custody');
$key=base64_decode($read("$custody/outcome-authority.json")['key'],true);
recoveryAssert(is_string($key)&&strlen($key)===32,'Independent simulator authority missing');
$records=$read("$custody/outcomes.json");$bindings=temporalBindings($restored);
if($continued) {
    recoveryAssert($read("$state/continuation-input.json")['pass']
        &&$read("$state/source/clone-point.json")['pass']&&$read("$state/source/clone-import-exit.json")['childExit']===0,
        'Bounded new-target qualification missing');
    recoveryAssert(hash_file('sha256',"$state/input-synthetic.sql")===$read("$state/continuation-input.json")['inputBackupSha256'],
        'Current campaign input backup changed');
    $inputPoint=$read("$root/.local/mysql-temporal-recovery-qualified/source/point.json");
    foreach($inputPoint['tables'] as $name=>$t) {
        recoveryAssert(isset($point['tables'][$name])&&$t['sha256']===$point['tables'][$name]['sha256']
            &&$t['count']===$point['tables'][$name]['count']
            &&recoveryCanonicalDdl($t['ddl'])===recoveryCanonicalDdl($point['tables'][$name]['ddl']),
            'Continuation source did not retain selected synthetic point');
    }
    recoveryAssert(array_keys($inputPoint['tables'])===array_keys($point['tables'])
        &&recoveryMetadataContract($inputPoint)===recoveryMetadataContract($point)
        &&$inputPoint['ledger']===$point['ledger']&&$inputPoint['grants']===$point['grants']
        &&$inputPoint['accounts']===$point['accounts'],'Continuation changed selected-point authority');
}
recoveryAssert(count($records)===8&&count($bindings)===8,'Outcome/uncertain inventory missing');
$exposure=['collectionUnits'=>0,'refundUnits'=>0,'payoutUnits'=>0,'emailOutcomes'=>0,'currency'=>'XTS','scale'=>2];
foreach($bindings as $b) {
    if($b['kind']==='email'){$exposure['emailOutcomes']++;continue;}
    recoveryAssert($b['currency']==='XTS'&&$b['scale']==='2'
        &&is_string($b['units'])&&ctype_digit($b['units']),'Invalid native synthetic exposure');
    $exposure[$b['kind'].'Units']+=(int)$b['units'];
}
recoveryAssert(array_intersect_key($a['exposure'],$exposure)===$exposure,'Measured monetary/email exposure differs');
$reconciliation=$read("$state/restore/reconciliation.json");
foreach($bindings as $i=>$b) {
    $d=temporalDecision($b,$records,$key,$backup['backupSha256']);
    recoveryAssert($d===$reconciliation['withIndependentEvidence'][$i]
        &&$d['reason']==='INDEPENDENT_OUTCOME_OBSERVED_REQUIRES_OWNER_RECONCILIATION'
        &&$d['decision']==='HOLD'&&!$d['automaticAction']&&!$d['releaseQuarantine'],'Replay authorized or independent evidence invalid');
    recoveryAssert(temporalDecision($b,[],$key,$backup['backupSha256'])===$reconciliation['withoutIndependentEvidence'][$i],'Missing evidence not held');
    recoveryAssert($records[$i]['record']['backupCompletedNs']===$backup['completedNs']
        &&$records[$i]['record']['observedNs']<=$gap['sourceCommitNs'],'Independent observation outside measured gap');
    $r=$records[$i]['record'];
    $sourceRow=array_values(array_filter($later['tables'][$b['table']]['rows'],static fn($row)=>$row['id']===$b['id']));
    recoveryAssert(count($sourceRow)===1,'Source outcome identity missing');
    recoveryAssert(($sourceRow[0][$b['table']==='payment_process'?'mtn_dispatch_state':'state'])
        ===($r['outcome']==='SENT'?'SENT':($b['table']==='payment_process'?'VERIFIED_SUCCESS':'SUCCESS')),'Observed outcome/source state differs');
    if(in_array($b['table'],['electronic_collection_attempts','payment_financial_operations'],true))
        recoveryAssert($sourceRow[0][$b['table']==='electronic_collection_attempts'?'provider_reference':'external_reference']
            ===$r['reference'],'Independent/source outcome reference differs');
    if($b['table']==='payment_financial_operations') {
        $receipts=array_values(array_filter($later['tables']['payment_receipt_evidence']['rows'],
            static fn($row)=>$row['operation_id']===$b['id']));
        recoveryAssert(count($receipts)===1&&$receipts[0]['source']==='synthetic-temporal'
            &&$receipts[0]['receipt_reference']===$r['reference']
            &&$receipts[0]['document_sha256']===hash('sha256',json_encode($r,JSON_THROW_ON_ERROR))
            &&json_decode($receipts[0]['retained_evidence'],true,512,JSON_THROW_ON_ERROR)===$records[$i],
            'Lost native receipt does not match independent retained observation');
    }
}
$denials=$read("$state/restore/quarantine-denials.json");
recoveryAssert($denials['pass']&&$denials['readOnly']&&$denials['superReadOnly']&&count($denials['denials'])===14,'Quarantine denial probes incomplete');
$actions=['late_callback','mtn_callback','funding','refund','payout','email_resend','replacement_intent'];
foreach(['lab_app','root'] as $user)foreach($actions as $action)
    recoveryAssert(in_array(['identity'=>$user,'action'=>$action,'mysqlCode'=>1290,'persistedWrites'=>0],$denials['denials'],true),'Actual MySQL quarantine rejection missing');
recoveryAssert($read("$state/protected-before.json")===$read("$evidence/protected-after.json")
    &&recoverySource($root)===$read("$state/protected-before.json"),'Protected original/frozen/execution source changed');
if(!$diagnostic)recoveryAssert($read("$state/previous-before.json")===$read("$state/previous-after.json"),'Earlier evidence custody changed');
// Check currently retained prior bytes too, without booting or reconstructing.
foreach($read("$state/previous-before.json") as $name=>$prior) {
    $dir="$root/.local/$name";
    if(!$prior['available']){recoveryAssert(!file_exists($dir),'Previously absent custody unexpectedly created');continue;}
    $files=[];
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS)) as $f) {
        recoveryAssert(!$f->isLink(),'Prior custody symlink refused');
        $files[substr($f->getPathname(),strlen($dir)+1)]=['type'=>$f->getType(),
            'sha256'=>$f->isFile()?hash_file('sha256',$f->getPathname()):null,
            'bytes'=>$f->getSize(),'mode'=>fileperms($f->getPathname())&0777,'owner'=>fileowner($f->getPathname())];
    }
    ksort($files,SORT_STRING);recoveryAssert($files===$prior['files'],'Prior evidence bytes changed');
}
echo "PASS: synthetic temporal gap, 8 outcomes / 11 lost row writes / 1 commit, exact stale restore, 14 physical denials, independent evidence HOLD".
    ($diagnostic?"; separately qualified read-only diagnosis, actual failed exits retained, diagnostic exit 0.\n":" and clean shutdown.\n");
