<?php
declare(strict_types=1);
// Offline proof check. No database, application, secrets, transport or worker boot.
require __DIR__.'/mysql-recovery-evidence.php';
$root=dirname(__DIR__,2);$state="$root/.local/mysql-synthetic-recovery";
recoveryAssert(count($argv)===1 && realpath($state)===$state && !is_link($state),'Owned retained campaign proof required');
$read=static function(string $path):array {
    recoveryAssert(is_file($path)&&!is_link($path)&&(fileperms($path)&0777)===0600,'Private evidence receipt absent');
    return json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
};
$assessment=$read("$state/assessment.json");
recoveryAssert(($assessment['status']??null)==='LOCAL_SYNTHETIC_UPGRADE_RECOVERY_PASS','Campaign not accepted');
$side=$assessment['restoreSide']??'restore';
recoveryAssert(in_array($side,['restore','restore-retry'],true),'Unapproved restored evidence location');
$retry=$side==='restore-retry';$backupDir=$retry?"$state/retry":$state;
recoveryAssert(($read("$backupDir/supervisor-exit.json")['supervisorExit']??-1)===0,'Real supervisor cleanup/exit failed');
$backup=$read("$backupDir/backup-receipt.json");
recoveryAssert(hash_file('sha256',"$backupDir/synthetic.sql")===$backup['backupSha256'],'Retained logical backup changed');
$point=$retry?"$backupDir/source-at-backup.json":"$state/source/after.json";
recoveryAssert(hash_file('sha256',$point)===$backup['snapshotSha256'],'Selected-point snapshot receipt changed');
recoveryAssert(hash_file('sha256',"$state/migration-hashes.json")===$backup['migrationHashesSha256'],'Historical migration manifest receipt changed');
recoveryAssert($read("$state/source-before.json")===$read($retry?"$backupDir/source-after.json":"$state/source-after.json"),'Protected source preservation receipt differs');
recoveryAssert(recoverySource($root)===$read("$state/source-before.json"),'Current protected source no longer matches campaign');
$sourceStages=['bootstrap','definer','fixtures','before','qualify','upgrade','after','compare-upgrade','probe','backup-preconditions','backup-receipt'];
if(!$retry)$sourceStages[]='preservation';
$stages=['source'=>$sourceStages,
    $side=>['restore-preconditions','definer','restored','compare-restore','recover-keys','probe','invalidate','final','assessment']];
foreach($stages as $which=>$list) {
    recoveryAssert(!file_exists("$state/$which/mysql.sock"),'Lab server still listening');
    foreach($list as $stage)recoveryAssert(($read("$state/$which/$stage-exit.json")['childExit']??-1)===0,"Failed/missing real child receipt: $which/$stage");
}
if($retry) {
    foreach(['retry-freeze','retry-backup-preconditions','dump','retry-backup-receipt','retry-preservation'] as $stage)
        recoveryAssert(($read("$backupDir/$stage-exit.json")['childExit']??-1)===0,'Corrected consistent backup stage failed');
    recoveryAssert($read("$backupDir/failed-before.json")===$read("$backupDir/failed-after.json")
        &&recoveryFailedTree($state)===$read("$backupDir/failed-before.json"),'Failed original restore custody changed');
    recoveryAssert(($read("$state/supervisor-exit.json")['supervisorExit']??0)===1
        &&!file_exists("$state/restore/mysql.sock"),'Historical failed import evidence was rewritten/restarted');
    $original=$read("$state/backup-receipt.json");
    recoveryAssert(hash_file('sha256',"$state/synthetic.sql")===$original['backupSha256'],'Original failed dump changed');
    recoveryAssert(!preg_match('/^\s*(?:LOCK TABLES|UNLOCK TABLES)\b/m',file_get_contents("$backupDir/synthetic.sql")),'Corrected dump still emits restore locks');
}
recoveryAssert(($read("$state/$side/import-exit.json")['childExit']??-1)===0,'Restore import failed');
$before=$read("$state/source/before.json");$after=$read("$state/source/after.json");$restored=$read("$state/$side/restored.json");$final=$read("$state/$side/final.json");
recoveryAssert(count($before['ledger'])===228&&count($after['ledger'])===229&&$after['ledger']===$restored['ledger'],'Native full historical ledger mismatch');
recoveryAssert($before['ledger']===$read("$state/source/bootstrap-ledger.json")
    &&array_slice($after['ledger'],0,228)===$before['ledger'],'Historical ledger IDs/batches not preserved');
$manifest=$read("$state/migration-hashes.json");
recoveryAssert($manifest===recoveryManifest("$root/.local/agendaally-clean-repository/.migration-backup/backend"),'Frozen source hashes changed');
recoveryAssert(array_column($before['ledger'],'migration')===array_slice(array_keys($manifest),0,-1)
    &&array_column($after['ledger'],'migration')===array_keys($manifest),'Ledger/hash ordering mismatch');
recoveryAssert($read("$state/reviewed-upgrade.json")['allowlist']===['2026_10_10_010000_add_manual_financial_workflows'],'Unreviewed upgrade allowlist');
foreach(['source/before','source/after',"$side/restored","$side/final"] as $point)
    recoveryAssert($read("$state/$point-invariants.json")===$read("$state/source/before-invariants.json"),'Financial/access authority receipt differs');
foreach($after['tables'] as $name=>$table)
    recoveryAssert($table['count']===$restored['tables'][$name]['count']&&$table['sha256']===$restored['tables'][$name]['sha256'],"Restored fingerprint differs: $name");
recoveryAssert(recoveryMetadataContract($after)===recoveryMetadataContract($restored)
    &&recoveryMetadataContract($restored)===recoveryMetadataContract($final),'Native schema metadata receipt differs');
foreach([['source','upgrade-comparison'],['source','probe'],[$side,'restore-comparison'],[$side,'recover-keys'],[$side,'probe'],[$side,'invalidate']] as [$which,$name])
    recoveryAssert(($read("$state/$which/$name.json")['pass']??false)===true,"Missing accepted $which/$name");
foreach(['sessions','personal_access_tokens','password_resets'] as $table)
    recoveryAssert($final['tables'][$table]['count']===0,'Stale restored credential receipt remains');
foreach($final['tables']['users']['rows'] as $user)recoveryAssert($user['verify_token']===null&&$user['remember_token']===null,'Restored challenge/remember authority remains');
recoveryAssert(($read("$state/$side/reconciliation.json")['decision']??null)==='HOLD ALL. No automated callback, retry, money movement, SMTP resend or worker activation; operator reconciliation before any traffic switch.','Reconciliation hold missing');
recoveryAssert(!is_file("$state/source/key-material.json")&&is_file("$state/$side/key-material.json")
    &&is_file("$root/.local/mysql-synthetic-key-custody/key-material.json"),'Independent lost-runtime-key proof/custody absent');
echo "PASS: retained synthetic-only 228→229 upgrade, exact restore, independent keys, stale-authority invalidation, passive reconciliation, source preservation and clean shutdown proof.\n";
