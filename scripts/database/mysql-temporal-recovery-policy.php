<?php
declare(strict_types=1);
// Passive, synthetic-only reconciliation. A valid observation NEVER authorizes
// a callback, credit, refund, payout, dispatch, resend or quarantine release.
function temporalBindings(array $snapshot):array {
    $out=[];
    $contexts=array_column($snapshot['tables']['payment_collection_contexts']['rows'],null,'id');
    foreach($snapshot['tables']['electronic_collection_attempts']['rows'] as $r) {
        if(!in_array($r['state'],['PENDING','UNKNOWN'],true))continue;
        $c=$contexts[$r['anchor_context_id']];
        $out[]=['table'=>'electronic_collection_attempts','id'=>$r['id'],'state'=>$r['state'],
            'identity'=>array_intersect_key($r,array_flip(['funding_event_key','anchor_context_id','revision_id','provider','provider_payment_id','process_reference'])),
            'units'=>$c['amount'],'currency'=>$c['currency_code'],'scale'=>$c['money_scale'],'kind'=>'collection'];
    }
    foreach($snapshot['tables']['payment_process']['rows'] as $r) {
        if($r['mtn_dispatch_state']!=='DISPATCH_OUTCOME_UNKNOWN')continue;
        $c=$contexts[$r['mtn_anchor_context_id']];
        $out[]=['table'=>'payment_process','id'=>$r['id'],'state'=>$r['mtn_dispatch_state'],
            'identity'=>array_intersect_key($r,array_flip(['mtn_funding_event_key','mtn_anchor_context_id','mtn_config_fingerprint','model_id','model_type','user_id'])),
            'units'=>$c['amount'],'currency'=>$c['currency_code'],'scale'=>$c['money_scale'],'kind'=>'collection'];
    }
    foreach($snapshot['tables']['payment_financial_operations']['rows'] as $r) {
        if(!in_array($r['state'],['PENDING','UNKNOWN','RESERVED'],true))continue;
        $c=$contexts[$r['context_id']];
        $out[]=['table'=>'payment_financial_operations','id'=>$r['id'],'state'=>$r['state'],
            'identity'=>array_intersect_key($r,array_flip(['kind','allocation_id','context_id','revision_id','request_key','provider','original_payment_id'])),
            'units'=>$r['amount_units'],'currency'=>$c['currency_code'],'scale'=>$c['money_scale'],'kind'=>$r['kind']];
    }
    foreach($snapshot['tables']['selected_email_deliveries']['rows'] as $r) {
        if(!in_array($r['state'],['PENDING','UNKNOWN'],true))continue;
        $out[]=['table'=>'selected_email_deliveries','id'=>$r['id'],'state'=>$r['state'],
            'identity'=>['event_key'=>$r['event_key'],'user_id'=>$r['user_id'],'kind'=>$r['kind'],
                'payloadSha256'=>hash('sha256',$r['encrypted_payload'])],
            'units'=>null,'currency'=>null,'scale'=>null,'kind'=>'email'];
    }
    return $out;
}
function temporalSignature(array $record,string $key):string {
    return hash_hmac('sha256',json_encode($record,JSON_THROW_ON_ERROR),$key);
}
function temporalDecision(array $binding,array $records,string $key,string $lineage):array {
    $result=['table'=>$binding['table'],'id'=>$binding['id'],'restoredState'=>$binding['state'],
        'decision'=>'HOLD','automaticAction'=>false,'releaseQuarantine'=>false];
    $matches=array_values(array_filter($records,static fn($r)=>($r['record']['table']??null)===$binding['table']
        &&($r['record']['id']??null)===$binding['id']));
    if(!$matches)return $result+['reason'=>'INDEPENDENT_EVIDENCE_MISSING'];
    $observations=[];
    foreach($matches as $envelope) {
        $r=$envelope['record'];
        if(!is_string($envelope['signature']??null)||!hash_equals(temporalSignature($r,$key),$envelope['signature']))
            return $result+['reason'=>'EVIDENCE_AUTHENTICITY_FAILED'];
        if(($r['lineage']??null)!==$lineage||($r['syntheticOnly']??null)!==true
            ||($r['identity']??null)!==$binding['identity']||($r['units']??null)!==$binding['units']
            ||($r['currency']??null)!==$binding['currency']||($r['scale']??null)!==$binding['scale'])
            return $result+['reason'=>'EVIDENCE_BINDING_MISMATCH'];
        if(!in_array($r['outcome']??null,['SUCCESS','FAILURE','SENT'],true)
            ||(($binding['kind']==='email')!==($r['outcome']==='SENT'))
            ||!is_string($r['reference']??null)||$r['reference']===''
            ||!is_int($r['observedNs']??null)||!is_int($r['backupCompletedNs']??null)
            ||$r['observedNs']<=$r['backupCompletedNs'])
            return $result+['reason'=>'OUTCOME_OR_TIME_INVALID'];
        $observations[]=[$r['outcome'],$r['reference']];
    }
    foreach($observations as $o)if($o!==$observations[0])return $result+['reason'=>'CONFLICTING_EVIDENCE'];
    return $result+['reason'=>'INDEPENDENT_OUTCOME_OBSERVED_REQUIRES_OWNER_RECONCILIATION',
        'observedOutcome'=>$observations[0][0],'reference'=>$observations[0][1],
        'nextStep'=>'Retain evidence and reconcile existing identity; never create a replacement or automatically repeat its effect.'];
}
function temporalLostWrites(array $point,array $later):array {
    recoveryAssert(array_keys($point['tables'])===array_keys($later['tables']),'Temporal table inventory changed');
    $out=[];$total=0;
    foreach($point['tables'] as $name=>$t) {
        $old=array_map(static fn($r)=>json_encode($r,JSON_THROW_ON_ERROR),$t['rows']);
        $new=array_map(static fn($r)=>json_encode($r,JSON_THROW_ON_ERROR),$later['tables'][$name]['rows']);
        $removed=array_values(array_diff($old,$new));$added=array_values(array_diff($new,$old));
        if(!$removed&&!$added)continue;
        // This campaign permits only existing-id replacements and new receipt
        // rows; assert the exact allowlist separately in the runtime/checker.
        $out[$name]=['oldRowsMissing'=>array_map(static fn($s)=>json_decode($s,true,512,JSON_THROW_ON_ERROR),$removed),
            'newRowsMissingFromBackup'=>array_map(static fn($s)=>json_decode($s,true,512,JSON_THROW_ON_ERROR),$added)];
        $total+=count($added);
    }
    return ['tables'=>$out,'changedOrInsertedRows'=>$total];
}
function temporalAssertOnlyOutcomeChanges(array $point,array $later):void {
    $allowed=[
        'electronic_collection_attempts'=>['state','provider_reference','version','updated_at'],
        'payment_process'=>['mtn_dispatch_state','mtn_attempt_version','updated_at'],
        'payment_financial_operations'=>['state','external_reference','completed_at','version','updated_at'],
        'selected_email_deliveries'=>['state','sent_at','updated_at'],
    ];
    foreach($allowed as $table=>$fields) {
        $old=array_column($point['tables'][$table]['rows'],null,'id');
        $new=array_column($later['tables'][$table]['rows'],null,'id');
        recoveryAssert(array_keys($old)===array_keys($new),'Gap replaced/lost an original identity');
        foreach($old as $id=>$r) {
            $omit=array_flip($fields);
            recoveryAssert(array_diff_key($r,$omit)===array_diff_key($new[$id],$omit),
                'Gap rewrote original binding, amount, encrypted payload or unrelated evidence');
        }
    }
    foreach($point['tables']['payment_receipt_evidence']['rows'] as $r)
        recoveryAssert(in_array($r,$later['tables']['payment_receipt_evidence']['rows'],true),'Gap modified an old receipt');
    recoveryAssert($point['ledger']===$later['ledger']&&$point['grants']===$later['grants']
        &&$point['accounts']===$later['accounts']&&recoveryMetadataContract($point)===recoveryMetadataContract($later),
        'Gap changed native schema/ledger/account/grant authority');
}
function temporalEvidenceTree(string $dir):array {
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
