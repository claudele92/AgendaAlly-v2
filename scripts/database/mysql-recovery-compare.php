<?php
declare(strict_types=1);

function recoveryCanonicalDdl(string $ddl): string {
    // The ONLY permitted SHOW CREATE syntactic equivalence: redundant column
    // CHARACTER SET utf8mb4 when the same explicit utf8mb4 COLLATE is retained.
    return preg_replace('/ CHARACTER SET utf8mb4(?= COLLATE utf8mb4_[a-z0-9_]+(?:[ ,\n]|$))/', '', $ddl);
}
function recoveryCompareUpgrade(array $a,array $b,array $review):void {
    recoveryAssert($a['serialization']===$b['serialization'],'Serialization recipe changed');
    $new=array_values(array_diff(array_keys($b['tables']),array_keys($a['tables'])));
    $want=$review['newTables'];sort($new,SORT_STRING);sort($want,SORT_STRING);
    recoveryAssert($new===$want,'Unexpected additive table inventory');
    foreach($a['tables'] as $name=>$table) {
        recoveryAssert(isset($b['tables'][$name]),"Historical table removed: $name");
        if(!in_array($name,['migrations','country_permissions','permissions'],true)) {
            recoveryAssert($table===$b['tables'][$name],"Old schema/row authority mutated: $name");
        } else {
            // Only native auto-increment advance in explicitly appended catalogs.
            recoveryAssert(preg_replace('/ AUTO_INCREMENT=\d+/','',$table['ddl'])===
                preg_replace('/ AUTO_INCREMENT=\d+/','',$b['tables'][$name]['ddl']),"Unreviewed old DDL: $name");
            foreach($table['rows'] as $r)recoveryAssert(in_array($r,$b['tables'][$name]['rows'],true),"Existing catalog/ledger row changed: $name");
        }
    }
    $keys=\App\Services\ManualFinance\FinanceScope::keys();
    foreach(['country_permissions'=>'key','permissions'=>'name'] as $table=>$field) {
        $added=[];
        foreach($b['tables'][$table]['rows'] as $row)if(!in_array($row,$a['tables'][$table]['rows'],true)) {
            recoveryAssert(in_array($row[$field],$keys,true),"Unreviewed permission definition: $table");
            if($table==='permissions')recoveryAssert($row['guard_name']==='web','Unreviewed permission guard');
            else recoveryAssert($row['group']==='payments' && $row['label']===ucwords(str_replace('.',' ',$row['key'])),'Unreviewed country definition');
            $added[]=$row[$field];
        }
        foreach($keys as $key) {
            $before=array_values(array_filter($a['tables'][$table]['rows'],static fn($r)=>$r[$field]===$key));
            $after=array_values(array_filter($b['tables'][$table]['rows'],static fn($r)=>$r[$field]===$key));
            recoveryAssert(count($after)===1 && (count($before)===1 || in_array($key,$added,true)),'Incomplete permission definitions');
        }
        recoveryAssert(count($added)===count($keys)-count(array_filter($a['tables'][$table]['rows'],static fn($r)=>in_array($r[$field],$keys,true))),'Unexpected permission catalog delta');
    }
    recoveryAssert(array_slice($b['ledger'],0,count($a['ledger']))===$a['ledger'] && count($b['ledger'])===count($a['ledger'])+1,'Historical native ledger mutated');
    foreach($new as $name)recoveryAssert($b['tables'][$name]['count']===0,'Migration inserted unexpected manual authority');
    $ma=recoveryMetadataContract($a);$mb=recoveryMetadataContract($b);
    foreach($ma as $key=>$rows) {
        if($key==='triggerDdl') {
            foreach($rows as $name=>$r)recoveryAssert(($mb[$key][$name]??null)===$r,"Historical trigger DDL changed: $name");
        } else foreach($rows as $row)recoveryAssert(in_array($row,$mb[$key],true),"Historical native metadata changed: $key");
    }
    foreach($b['accounts'] as $r)recoveryAssert($r['super_priv']==='N' && $r['grant_priv']==='N'
        && $r['account_locked']===($r['user']==='lab_bootstrap'?'Y':'N'),'Retained elevated privilege');
}
function recoveryCompareRestore(array $a,array $b,string $dir):void {
    recoveryAssert($a['serialization']===$b['serialization'],'Restore serialization mismatch');
    recoveryAssert(array_keys($a['tables'])===array_keys($b['tables']),'Restore table inventory mismatch');
    $raw=[];
    foreach($a['tables'] as $name=>$table) {
        $other=$b['tables'][$name];
        recoveryAssert($table['sha256']===$other['sha256'] && $table['count']===$other['count'],"Restore rows differ: $name");
        if($table['ddl']!==$other['ddl'])$raw[$name]=['before'=>$table['ddl'],'restored'=>$other['ddl']];
    }
    recoveryWrite("$dir/raw-ddl-differences.json",$raw); // persist before asserting equivalence
    recoveryAssert(recoveryMetadataContract($a)===recoveryMetadataContract($b),'Restore native column/index/FK/CHECK/trigger/definer metadata mismatch');
    foreach($raw as $name=>$diff)
        recoveryAssert(recoveryCanonicalDdl($diff['before'])===recoveryCanonicalDdl($diff['restored']),"Unexplained restored raw DDL: $name");
    recoveryAssert($a['ledger']===$b['ledger'] && $a['accounts']===$b['accounts'] && $a['grants']===$b['grants'],'Restore ledger/accounts/grants mismatch');
    recoveryAssert($a['session']===$b['session'],'Restored read-session contract mismatch');
}
