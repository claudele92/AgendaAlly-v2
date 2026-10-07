<?php
declare(strict_types=1);
// Offline combined assessment only. Does not rewrite any failed-run receipt.
require __DIR__.'/mysql-recovery-evidence.php';
require __DIR__.'/mysql-temporal-recovery-policy.php';
$root=dirname(__DIR__,2);$state="$root/.local/mysql-temporal-recovery-completed";
$final="$root/.local/mysql-temporal-recovery-diagnostic";
recoveryAssert(count($argv)===1&&realpath($final)===$final&&!is_link($final)
    &&(fileperms($final)&0777)===0700&&fileowner($final)===posix_geteuid(),'Owned final diagnostic qualification required');
$read=static function($p):array {
    recoveryAssert(is_file($p)&&!is_link($p)&&(fileperms($p)&0777)===0600,'Private qualification receipt absent');
    return json_decode(file_get_contents($p),true,512,JSON_THROW_ON_ERROR);
};
foreach(['before','relocate','snapshot','after'] as $stage)
    recoveryAssert($read("$final/$stage-exit.json")['childExit']===0,'Diagnostic child failure');
recoveryAssert($read("$final/supervisor-exit.json")['supervisorExit']===0&&$read("$final/diagnosis.json")['pass'],'Diagnostic stop/failure');
recoveryAssert($read("$state/supervisor-exit.json")['supervisorExit']===92
    &&$read("$state/source/preservation-exit.json")['childExit']===255,'Historical failures must remain actual failures');
foreach(['source'=>['freeze','continuation-preconditions','clone-preconditions','clone-import','clone-point','definer',
    'point','backup-preconditions','backup-receipt','outcomes','later'],
    'restore'=>['restore-preconditions','import','definer','restored','reconcile','final']] as $side=>$stages) {
    recoveryAssert(!file_exists("$state/$side/mysql.sock"),'Prior source/restore still listening');
    foreach($stages as $stage)recoveryAssert($read("$state/$side/$stage-exit.json")['childExit']===0,'Temporal child failure');
}
recoveryAssert(!file_exists("$final/source/mysql.sock")&&!file_exists("$final/source/mysql.pid"),'Diagnostic still listening');
recoveryAssert(temporalEvidenceTree($state)===$read("$final/failed-before.json")
    &&$read("$final/failed-before.json")===$read("$final/failed-after.json"),'Failed campaign files modified');
$point=$read("$state/source/point.json");$later=$read("$state/source/later.json");
temporalAssertOnlyOutcomeChanges($point,$later);
$copy=$read("$final/source/snapshot.json");
recoveryAssert($later['tables']===$copy['tables']&&$later['ledger']===$copy['ledger']
    &&$later['grants']===$copy['grants']&&$later['accounts']===$copy['accounts']
    &&$later['session']===$copy['session']&&recoveryMetadataContract($later)===recoveryMetadataContract($copy),
    'Real post-point source authority difference');
$lost=temporalLostWrites($point,$later);
recoveryAssert($lost['changedOrInsertedRows']===11&&count($lost['tables'])===5,'Expected nonzero temporal loss missing');
recoveryAssert($read("$state/restore/restored.json")===$read("$state/restore/final.json")
    &&$read("$state/restore/reconciliation.json")['decision']==='HOLD ALL'
    &&count($read("$state/restore/quarantine-denials.json")['denials'])===14,'Restored hold proof missing');
recoveryWrite("$final/lost-writes.json",$lost);
$gap=$read("$state/source/gap-time.json");
recoveryWrite("$final/assessment.json",[
    'status'=>'LOCAL_SYNTHETIC_TEMPORAL_RECOVERY_PASS_WITH_DIAGNOSTIC_QUALIFICATION',
    'outcomesNewerThanBackup'=>8,'lostChangedOrInsertedRows'=>11,'lostCommitTransactions'=>1,
    'lostWriteTables'=>['electronic_collection_attempts'=>2,'payment_process'=>1,'payment_financial_operations'=>3,
        'payment_receipt_evidence'=>3,'selected_email_deliveries'=>2],
    'gapSeconds'=>($gap['sourceCommitNs']-$gap['backupCompletedNs'])/1e9,
    'exposure'=>['collectionUnits'=>1500,'refundUnits'=>500,'payoutUnits'=>400,'emailOutcomes'=>2,'currency'=>'XTS','scale'=>2,
        'meaning'=>'possible repeated effects from stale identities, not money moved or email delivered'],
    'continuedInNewTargets'=>true,'decision'=>'HOLD ALL','automaticActions'=>0,
    'originalPreservationExit'=>255,'originalSupervisorExit'=>92,'diagnosticSupervisorExit'=>0,
    'qualification'=>'native authority equivalent; six sampled cardinality entries only; redundant DCL denied by quarantine; failed receipts unchanged',
    'sourceRestoreEvidence'=>'mysql-temporal-recovery-completed','diagnostic'=>'new read-only synthetic datadir copy; no failed-source restart',
    'production'=>'NOT QUALIFIED','offHostCustody'=>'NOT TESTED','realExternalOutcomes'=>'NOT TESTED']);
echo "Combined temporal qualification retained separately; failed exits unchanged. Run the full offline proof checker.\n";
