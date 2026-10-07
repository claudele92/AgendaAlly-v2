<?php
declare(strict_types=1);
// Read-only diagnosis on a NEW COPY of this campaign's synthetic datadir only.
require __DIR__.'/mysql-recovery-evidence.php';
$root=dirname(__DIR__,2);$state="$root/.local/mysql-temporal-recovery-diagnostic";
$prior="$root/.local/mysql-temporal-recovery-completed";$mode=$argv[1]??'';
recoveryAssert(count($argv)===2&&in_array($mode,['before','relocate','snapshot','after'],true)
    &&realpath($state)===$state&&!is_link($state)&&(fileperms($state)&0777)===0700
    &&fileowner($state)===posix_geteuid()&&is_file("$state/approval.txt"),'Owned synthetic diagnostic copy required');
foreach(['curl_exec','curl_multi_exec','fsockopen','pfsockopen','stream_socket_client','socket_connect',
    'mail','exec','shell_exec','proc_open','popen','system','passthru'] as $f)
    recoveryAssert(!function_exists($f),'No diagnostic transport/process execution');
$read=static fn($p)=>json_decode(file_get_contents($p),true,512,JSON_THROW_ON_ERROR);
function temporalStoppedTree(string $dir):array {
    recoveryAssert(realpath($dir)===$dir&&!is_link($dir),'Owned stopped evidence tree required');
    $out=[];
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS)) as $f) {
        recoveryAssert(!$f->isLink(),'Stopped evidence symlink refused');
        $out[substr($f->getPathname(),strlen($dir)+1)]=['type'=>$f->getType(),
            'sha256'=>$f->isFile()?hash_file('sha256',$f->getPathname()):null,'bytes'=>$f->getSize(),
            'mode'=>fileperms($f->getPathname())&0777,'owner'=>fileowner($f->getPathname())];
    }
    ksort($out,SORT_STRING);return $out;
}
if($mode==='before') {
    recoveryAssert(!file_exists("$prior/source/mysql.sock")&&!file_exists("$prior/restore/mysql.sock")
        &&$read("$prior/source/preservation-exit.json")['childExit']===255
        &&$read("$prior/supervisor-exit.json")['supervisorExit']===92
        &&str_contains(file_get_contents("$prior/source/preservation.log"),'Post-point source changed'),
        'Exact stopped campaign gate failure required');
    foreach(['source','restore'] as $side)foreach(['emergency-revoke','final-revoke'] as $log)
        recoveryAssert(str_contains(file_get_contents("$prior/$side/$log.log"),'ERROR 1290')
            &&str_contains(file_get_contents("$prior/$side/$log.log"),'--super-read-only'),
            'Only expected read-only administrative cleanup denials qualify for diagnosis');
    recoveryWrite("$state/failed-before.json",temporalStoppedTree($prior));
    recoveryWrite("$state/protected-before.json",recoverySource($root));
    recoveryAssert(recoverySource($root)===$read("$prior/protected-before.json"),'Protected source drift');
    exit;
}
if($mode==='relocate') {
    recoveryAssert(temporalStoppedTree("$prior/source/data")===temporalStoppedTree("$state/source/data"),
        'Copied synthetic datadir must first match every source byte/mode/owner');
    $index="$state/source/data/binlog.index";
    $old=file_get_contents($index);$new=[];
    foreach(explode("\n",trim($old)) as $line) {
        recoveryAssert((bool)preg_match('#^'.preg_quote("$prior/source/data/",'#').'binlog\.[0-9]+$#D',$line),
            'Unexpected copied binlog path; never contact original evidence files');
        $new[]="$state/source/data/".basename($line);
    }
    recoveryAssert(file_put_contents($index,implode("\n",$new)."\n",LOCK_EX)!==false,'Copied binlog index relocation failed');
    recoveryWrite("$state/relocation.json",['originalDatadirUntouched'=>true,'initialCopyAllBytesExact'=>true,
        'changedCopyFile'=>'binlog.index only; absolute paths relocated to the diagnostic copy',
        'beforeSha256'=>hash('sha256',$old),'afterSha256'=>hash_file('sha256',$index)]);
    exit;
}
if($mode==='snapshot') {
    $p=recoveryRoot($state,'source');
    recoveryAssert($p->query('SELECT @@global.read_only')->fetchColumn()==='1'
        &&$p->query('SELECT @@global.super_read_only')->fetchColumn()==='1','Diagnostic copy must be read-only from startup');
    $s=recoverySnapshot($p);
    recoveryWrite("$state/source/snapshot.json",$s); // persist raw mismatch BEFORE assertions
    $old=$read("$prior/source/later.json");
    recoveryPrivilegeContract($p,$s,$state,'source');
    recoveryEmittedTriggerContract($s,"$prior/source",false);
    recoveryAssert($old['tables']===$s['tables']&&$old['ledger']===$s['ledger']&&$old['accounts']===$s['accounts']
        &&$old['grants']===$s['grants']&&$old['session']===$s['session']
        &&recoveryMetadataContract($old)===recoveryMetadataContract($s),'Diagnostic copy shows real authority difference: STOP');
    $diff=[];
    foreach($old as $name=>$value)if($value!==$s[$name])$diff[$name]=['before'=>$value,'after'=>$s[$name]];
    recoveryWrite("$state/raw-source-differences.json",$diff);
    recoveryAssert($diff!==[],'Original source mismatch not reproduced; no invented explanation allowed');
    // Source triggers were NOT reimported. Only actual optimizer cardinality
    // changes may explain this gate; no other raw difference is normalized.
    recoveryAssert(array_keys($diff)===['indexes'],'Only sampled optimizer index statistics may differ');
    $changed=0;
    foreach($old['indexes'] as $i=>$r) {
        $other=$s['indexes'][$i];
        $a=$r;$b=$other;unset($a['cardinality'],$b['cardinality']);
        recoveryAssert($a===$b,'Native index definition changed');
        if($r['cardinality']!==$other['cardinality'])$changed++;
    }
    recoveryAssert($changed>0,'No actual sampled cardinality difference retained');
    recoveryWrite("$state/diagnosis.json",['pass'=>true,'sourceAuthority'=>'all table rows/raw DDL/ledger/accounts/grants/session/native metadata identical',
        'rawDifference'=>'sampled index cardinality only','changedCardinalityEntries'=>$changed,
        'originalFailure'=>'raw snapshot equality incorrectly included non-definition optimizer statistics',
        'cleanupFailure'=>'redundant DCL was correctly denied by super_read_only; locked least-privilege grants retained',
        'sourceRestarted'=>false,'diagnostic'=>'new read-only copy of current campaign synthetic source; no original data/evidence writes']);
    exit;
}
recoveryWrite("$state/failed-after.json",temporalStoppedTree($prior));
recoveryAssert($read("$state/failed-before.json")===$read("$state/failed-after.json"),'Stopped campaign evidence modified by diagnosis');
recoveryWrite("$state/protected-after.json",recoverySource($root));
recoveryAssert($read("$state/protected-before.json")===$read("$state/protected-after.json"),'Protected source changed during diagnosis');
echo "PASS: new read-only copy explains only optimizer statistics; failed source/restore bytes unchanged.\n";
