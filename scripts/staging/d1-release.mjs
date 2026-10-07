import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {execFileSync} from 'node:child_process';
const root=process.cwd(),state=`${root}/.local/staging-mvp`;
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
function curl(url,more=[]) {
  return execFileSync('curl',['--cacert',`${state}/tls/server.crt`,'-s','--max-time','10',...more,url],{encoding:'utf8'});
}
function health() {return JSON.parse(curl('https://localhost:8443/staging/health'));}
function mediaHashes() {
  const output={};
  function walk(base) {
    for(const entry of fs.readdirSync(`${state}/media/${base}`,{withFileTypes:true})) {
      const relative=path.posix.join(base,entry.name);
      if(entry.isDirectory())walk(relative);
      else if(entry.isFile())output[relative]=crypto.createHash('sha256').update(fs.readFileSync(`${state}/media/${relative}`)).digest('hex');
    }
  }
  walk('');return output;
}
function fingerprint() {return execFileSync('php',[`${root}/scripts/staging/db-evidence.php`],{encoding:'utf8',maxBuffer:32*1024*1024});}
function switchRelease(label) {
  const destination=`${state}/releases/${label}`;
  if(!fs.existsSync(`${destination}/.migration-backup/web/.next/BUILD_ID`))throw Error('Release has no accepted production build');
  fs.symlinkSync(destination,`${state}/rollback-next`,'dir');fs.renameSync(`${state}/rollback-next`,`${state}/current`);
}
function pid(name){return JSON.parse(fs.readFileSync(`${state}/process-state.json`,'utf8')).services[name]?.pid;}
async function restart(name) {
  const previous=pid(name);
  if(!previous)throw Error(`Required ${name} is not supervised`);
  process.kill(previous,'SIGTERM');
  const end=Date.now()+45000;
  while(Date.now()<end) {
    await sleep(1000);
    const next=pid(name);
    if(next&&next!==previous) {
      try {if(health().status==='ok')return {old:previous,new:next};}catch{}
    }
  }
  throw Error(`Restart did not recover: ${name}`);
}
const result={scope:'Local code-only compatible rollback/redeploy; no populated down migration or owned/production action',checks:{},restarts:{}};
const before=health(),moneyBefore=fingerprint(),mediaBefore=mediaHashes();
const selectedRelease=path.basename(fs.realpathSync(`${state}/current`));
try {
  switchRelease('foundation-corrected');
  result.restarts.rollbackCustomer=await restart('customer');
  execFileSync('nginx',['-c',`${state}/nginx.conf`,'-s','reload'],{stdio:'ignore'});
  result.checks.priorCustomerLoads=curl('https://localhost:8443/',['-o','/dev/null','-w','%{http_code}'])==='200';
  result.checks.priorBusinessLoads=curl('https://localhost:8444/',['-o','/dev/null','-w','%{http_code}'])==='200';
  result.checks.rollbackKeyStable=health().keyFingerprint===before.keyFingerprint;
  result.checks.rollbackFullNativeEvidenceUnchanged=fingerprint()===moneyBefore;
  result.checks.rollbackMediaUnchanged=JSON.stringify(mediaHashes())===JSON.stringify(mediaBefore);
  switchRelease(selectedRelease);
  result.restarts.redeployCustomer=await restart('customer');
  result.restarts.api=await restart('fpm');
  result.restarts.businessProxy=await restart('nginx');
  result.restarts.database=await restart('mysql');
  result.checks.redeployKeyStable=health().keyFingerprint===before.keyFingerprint;
  result.checks.redeployFullNativeEvidenceUnchanged=fingerprint()===moneyBefore;
  result.checks.redeployMediaUnchanged=JSON.stringify(mediaHashes())===JSON.stringify(mediaBefore);
  const fixture=JSON.parse(fs.readFileSync(`${state}/media-fixtures.json`,'utf8'));
  result.checks.mediaExamplesServe=Object.values(fixture.examples).every(file=>
    curl(`https://localhost:8443/storage/${file}`,['-o','/dev/null','-w','%{http_code}'])==='200');
  result.checks.customerLoads=curl('https://localhost:8443/',['-o','/dev/null','-w','%{http_code}'])==='200';
  result.checks.businessLoads=curl('https://localhost:8444/',['-o','/dev/null','-w','%{http_code}'])==='200';
  // A malformed DDL attempt against an empty scratch schema demonstrates failure
  // without deleting or modifying financial state. Restricted app DDL was denied separately.
  execFileSync('mysql',['--no-defaults',`--socket=${state}/mysql/mysql.sock`,'-u','root','-e',
    'CREATE DATABASE agendaally_staging_failed_release;'],{stdio:'ignore'});
  let failed=false;
  try {execFileSync('mysql',['--no-defaults',`--socket=${state}/mysql/mysql.sock`,'-u','root',
    'agendaally_staging_failed_release'],{input:'CREATE TABLE deliberately_invalid (;',stdio:['pipe','ignore','pipe']});}
  catch {failed=true;}
  result.checks.failedMigrationAttemptContained=failed&&fingerprint()===moneyBefore;
  const allowed=curl('https://localhost:8443/api/v1/rest/settings',['-X','OPTIONS','-D','-','-o','/dev/null',
    '-H','Origin: https://localhost:8444','-H','Access-Control-Request-Method: GET']);
  const denied=curl('https://localhost:8443/api/v1/rest/settings',['-X','OPTIONS','-D','-','-o','/dev/null',
    '-H','Origin: https://unapproved.invalid','-H','Access-Control-Request-Method: GET']);
  result.checks.exactCorsAllowed=/access-control-allow-origin:\s*https:\/\/localhost:8444/i.test(allowed);
  result.checks.unapprovedCorsDenied=!/access-control-allow-origin:\s*https:\/\/unapproved.invalid/i.test(denied);
  result.localResult=Object.values(result.checks).every(Boolean)?'PASS':'FAIL';
  result.status=result.localResult==='PASS'?'PARTIAL':'FAIL';
  result.remaining=['Public approved staging host and trusted public TLS','Real production UI CAPTCHA sign-in',
    'Cookie/CSRF-path acceptance where applicable','Approved production data migration/retention and historical rotation evidence'];
}catch(error) {result.status='FAIL';result.error={type:error.name,message:error.message.slice(0,400)};}
fs.writeFileSync(`${state}/d1-release.json`,JSON.stringify(result,null,2));
console.log(JSON.stringify(result,null,2));
if(result.status==='FAIL')process.exitCode=1;