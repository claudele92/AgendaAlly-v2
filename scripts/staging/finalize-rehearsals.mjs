import fs from 'node:fs';
import {execFileSync} from 'node:child_process';
const state='.local/staging-mvp',read=name=>JSON.parse(fs.readFileSync(`${state}/${name}.json`,'utf8'));
const schema=read('r1-schema-equivalence'),r1=read('r1-recovery');
const selected=read('r1-selected-point'),restored=read('r1-restored');
r1.rawSchemaTextIdentical=false;
r1.schemaVerification=schema;
r1.checks.schemaMatches=schema.status==='PASS';
r1.checks.fullSelectedPointFingerprintsMatch=schema.status==='PASS'
  &&JSON.stringify({...restored,schema:selected.schema})===JSON.stringify(selected);
r1.correction='First FAIL retained. The sole raw DDL mismatch was redundant column CHARACTER SET before the identical COLLATE on the operational probe. All native columns, original other schema hashes, indexes, constraints, triggers and captured selected-point data match. No new backup/restore or financial mutation was performed to hide it.';
r1.localResult=Object.values(r1.checks).every(Boolean)?'PASS':'FAIL';
r1.status=r1.localResult==='PASS'?'PARTIAL':'FAIL';
fs.writeFileSync(`${state}/r1-recovery.json`,JSON.stringify(r1,null,2));
const o1=read('o1-rehearsals');
const correctedAt=new Date().toISOString();
const httpStatus=Number(execFileSync('curl',['--cacert',`${state}/tls/server.crt`,'-s','-o','/dev/null','-w','%{http_code}',
  'https://localhost:8443/staging/simulation/failure'],{encoding:'utf8'}));
execFileSync(process.execPath,['scripts/staging/monitor.mjs','--once'],{stdio:'ignore'});
const delivered=fs.readFileSync(`${state}/alerts.jsonl`,'utf8').trim().split('\n').map(line=>JSON.parse(line))
  .findLast(alert=>alert.code==='HTTP_5XX_INCREASE'&&alert.at>=correctedAt);
const redaction=read('log-redaction');
o1.checks.syntheticHttpFailureVisible=httpStatus===500&&Boolean(delivered);
o1.checks.selectedLogRedaction=redaction.status==='PASS';
o1.httpProbeCorrection={status:httpStatus,alertSample:delivered?.at,
  cause:'Initial isolated simulation route fell through to the Customer app rather than FPM; explicit loopback-only location added and only the failed probe repeated.',
  originalFailureRetained:true};
o1.localResult=Object.values(o1.checks).every(Boolean)?'PASS':'FAIL';
o1.status=o1.localResult==='PASS'?'PARTIAL':'FAIL';
fs.writeFileSync(`${state}/o1-rehearsals.json`,JSON.stringify(o1,null,2));
console.log(JSON.stringify({r1:{status:r1.status,localResult:r1.localResult,measured:r1.measured},
  o1:{status:o1.status,localResult:o1.localResult,checks:o1.checks}},null,2));
if(r1.localResult!=='PASS'||o1.localResult!=='PASS')process.exitCode=1;