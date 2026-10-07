<?php
declare(strict_types=1);
// Pure offline negative cases. No framework, transport, secrets or DB.
require __DIR__.'/mysql-recovery-evidence.php';
require __DIR__.'/mysql-temporal-recovery-policy.php';
$key=str_repeat('synthetic-test-only-',2);$lineage=str_repeat('a',64);
$b=['table'=>'payment_financial_operations','id'=>'synthetic-id','state'=>'UNKNOWN',
    'identity'=>['request_key'=>'original-request','original_payment_id'=>'original-payment','context_id'=>'1'],
    'units'=>'300','currency'=>'XTS','scale'=>'2','kind'=>'refund'];
$r=$b;unset($r['state'],$r['kind']);
$r+=['lineage'=>$lineage,'syntheticOnly'=>true,'outcome'=>'SUCCESS','reference'=>'synthetic-receipt',
    'backupCompletedNs'=>100,'observedNs'=>101];
$sign=static fn($r)=>['record'=>$r,'signature'=>temporalSignature($r,$key)];
$check=static function(array $records,string $reason)use($b,$key,$lineage):void {
    $decision=temporalDecision($b,$records,$key,$lineage);
    recoveryAssert($decision['reason']===$reason,"Wrong reconciliation reason: $reason");
    recoveryAssert($decision['decision']==='HOLD'&&!$decision['automaticAction']&&!$decision['releaseQuarantine'],
        'Any evidence case must retain quarantine without an effect');
};
$check([],'INDEPENDENT_EVIDENCE_MISSING');
$check([$sign($r)],'INDEPENDENT_OUTCOME_OBSERVED_REQUIRES_OWNER_RECONCILIATION');
$failed=$r;$failed['outcome']='FAILURE';
$check([$sign($failed)],'INDEPENDENT_OUTCOME_OBSERVED_REQUIRES_OWNER_RECONCILIATION');
$check([$sign($r),$sign($r)],'INDEPENDENT_OUTCOME_OBSERVED_REQUIRES_OWNER_RECONCILIATION');
recoveryAssert(temporalDecision($b,[$sign($r)],str_repeat('wrong-key-',4),$lineage)['reason']
    ==='EVIDENCE_AUTHENTICITY_FAILED','Wrong independent authority must fail closed');
$tampered=$sign($r);$tampered['record']['outcome']='FAILURE';
$check([$tampered],'EVIDENCE_AUTHENTICITY_FAILED');
foreach(['id','table'] as $f) {
    $bad=$r;$bad[$f]='different';
    $check([$sign($bad)],'INDEPENDENT_EVIDENCE_MISSING');
}
foreach(['lineage','identity','units','currency','scale','syntheticOnly'] as $f) {
    $bad=$r;$bad[$f]=$f==='identity'?['request_key'=>'replacement-request']:($f==='syntheticOnly'?false:'different');
    $check([$sign($bad)],'EVIDENCE_BINDING_MISMATCH');
}
foreach(['outcome','reference','observedNs','backupCompletedNs'] as $f) {
    $bad=$r;$bad[$f]=match($f){'outcome'=>'UNKNOWN','reference'=>'','observedNs'=>100,'backupCompletedNs'=>'100'};
    $check([$sign($bad)],'OUTCOME_OR_TIME_INVALID');
}
$conflict=$r;$conflict['outcome']='FAILURE';
$check([$sign($r),$sign($conflict)],'CONFLICTING_EVIDENCE');
$conflict=$r;$conflict['reference']='another-receipt';
$check([$sign($r),$sign($conflict)],'CONFLICTING_EVIDENCE');
$email=$b;$email['kind']='email';$email['units']=null;$email['currency']=null;$email['scale']=null;
$er=$email;unset($er['kind'],$er['state']);
$er+=['lineage'=>$lineage,'syntheticOnly'=>true,'outcome'=>'SENT','reference'=>'synthetic-smtp-observation',
    'backupCompletedNs'=>100,'observedNs'=>101];
recoveryAssert(temporalDecision($email,[$sign($er)],$key,$lineage)['reason']
    ==='INDEPENDENT_OUTCOME_OBSERVED_REQUIRES_OWNER_RECONCILIATION','Email SENT observation must reconcile without resend');
$er['outcome']='SUCCESS';
recoveryAssert(temporalDecision($email,[$sign($er)],$key,$lineage)['reason']==='OUTCOME_OR_TIME_INVALID','Email success cannot substitute for SENT');
$point=['tables'=>['fixture'=>['rows'=>[['id'=>'1','state'=>'UNKNOWN']]]]];
$later=['tables'=>['fixture'=>['rows'=>[['id'=>'1','state'=>'SUCCESS'],['id'=>'2','state'=>'SENT']]]]];
recoveryAssert(temporalLostWrites($point,$later)['changedOrInsertedRows']===2,'Loss must count both replacements and inserts');
echo "PASS: missing, forged, mismatched, stale, conflicting and valid synthetic outcomes all HOLD; nonzero loss counts replacements and inserts.\n";
