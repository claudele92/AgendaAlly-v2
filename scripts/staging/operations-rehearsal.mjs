import fs from 'node:fs';
import {execFileSync} from 'node:child_process';
const root=process.cwd(),state=`${root}/.local/staging-mvp`;
const result={scope:'Safe isolated staging failure rehearsals; local alert receiver, no providers or financial retries',checks:{},evidence:{}};
const sql=query=>execFileSync('mysql',['--no-defaults',`--socket=${state}/mysql/mysql.sock`,'-u','root','-N','--batch',
  'agendaally_staging_mvp','-e',query],{encoding:'utf8'}).trim();
const tick=ms=>new Promise(resolve=>setTimeout(resolve,ms));
function monitor(extra=[]) {
  execFileSync(process.execPath,[`${root}/scripts/staging/monitor.mjs`,'--once',...extra],{stdio:'ignore'});
  return JSON.parse(fs.readFileSync(`${state}/monitor-latest.json`,'utf8'));
}
function pause(name) {
  fs.writeFileSync(`${state}/operator-pauses.json`,JSON.stringify([name]));
  const processState=JSON.parse(fs.readFileSync(`${state}/process-state.json`,'utf8'));
  if(processState.services[name]?.pid)process.kill(processState.services[name].pid,'SIGTERM');
}
function resume(){fs.writeFileSync(`${state}/operator-pauses.json`,'[]');}
const has=(sample,code)=>sample.alerts.some(alert=>alert.code===code);
const financialSnapshot=()=>execFileSync('php',[`${root}/scripts/staging/db-evidence.php`],{encoding:'utf8',maxBuffer:32*1024*1024});
const financialTables=all=>Object.fromEntries(Object.entries(JSON.parse(all).tables)
  .filter(([name])=>/wallet|ledger|payment|receipt|refund|contribution|effect|economic|quote|transaction/i.test(name)));
const before=financialTables(financialSnapshot());
const retainedBefore=sql("SELECT id,state,amount_units FROM payment_financial_operations WHERE state IN('UNKNOWN','PENDING','CANCELED') ORDER BY id;");
try {
  const initial=monitor();result.evidence.initial=initial;
  pause('worker');await tick(1000);
  const queued=JSON.parse(execFileSync('php',[`${root}/scripts/staging/operations-probe.php`,'enqueue-selected'],{encoding:'utf8'}));
  const stopped=monitor();
  result.checks.workerFailureAlert=has(stopped,'PROCESS_WORKER_DOWN');
  result.checks.selectedQueuedWorkVisible=stopped.metrics.queueBacklog>0;
  resume();await tick(8000);
  const restarted=monitor();
  result.checks.workerRestarted=restarted.components.worker.running;
  result.checks.selectedWorkRecovered=Number(sql("SELECT COUNT(*) FROM jobs WHERE queue='mvp-notifications';"))===0;
  result.evidence.selectedDelivery={...queued,state:sql(`SELECT state FROM selected_email_deliveries WHERE id='${queued.deliveryId}';`),
    externalSmtpAccepted:false};
  pause('scheduler');await tick(1000);
  fs.writeFileSync(`${state}/scheduler-heartbeat.json`,JSON.stringify({at:new Date(Date.now()-120000).toISOString(),exitCode:0}));
  const missed=monitor();
  result.checks.schedulerInterruptionAlert=has(missed,'SCHEDULER_HEARTBEAT_MISSED')&&has(missed,'PROCESS_SCHEDULER_DOWN');
  result.evidence.reminder=JSON.parse(execFileSync('php',[`${root}/scripts/staging/operations-probe.php`,'reminder'],{encoding:'utf8'}));
  result.checks.selectedReminderCatchUp=result.evidence.reminder.created;
  result.checks.reminderDeduplicated=result.evidence.reminder.deduplicated;
  resume();await tick(7000);
  result.checks.schedulerResumed=monitor().components.scheduler.running;
  monitor();
  const failure=execFileSync('curl',['--cacert',`${state}/tls/server.crt`,'-s','-o','/dev/null','-w','%{http_code}',
    '-H','Authorization: Bearer STAGING_AUTHORIZATION_MUST_NOT_BE_LOGGED',
    '-H','Content-Type: application/json','--data',
    '{"password":"STAGING_PASSWORD_MUST_NOT_BE_LOGGED","reset":"STAGING_RESET_CHALLENGE_MUST_NOT_BE_LOGGED","invitation":"STAGING_INVITATION_CHALLENGE_MUST_NOT_BE_LOGGED","merchant":"STAGING_MERCHANT_CREDENTIAL_MUST_NOT_BE_LOGGED"}',
    'https://localhost:8443/staging/simulation/failure'],{encoding:'utf8'});
  const httpFailure=monitor();
  result.checks.syntheticHttpFailureVisible=failure==='500'&&has(httpFailure,'HTTP_5XX_INCREASE');
  result.checks.safeDiskThresholdAlert=has(monitor(['--disk-available=101']),'LOW_DISK_THRESHOLD');
  result.checks.unknownPendingVisible=has(monitor(),'FINANCIAL_INTERVENTION_REQUIRED');
  pause('worker');await tick(1000);
  execFileSync('php',[`${root}/scripts/staging/operations-probe.php`,'failed-job'],{stdio:'ignore'});
  execFileSync('php',[`${root}/scripts/staging/console.php`,'queue:work','database','--queue=mvp-notifications','--once','--tries=1'],{stdio:'ignore'});
  const failed=sql("SELECT uuid FROM failed_jobs WHERE exception LIKE '%STAGING_SAFE_QUEUE_FAILURE%' ORDER BY id DESC LIMIT 1;");
  result.checks.failedJobVisible=Boolean(failed)&&has(monitor(),'FAILED_JOBS_VISIBLE');
  if(failed) {
    execFileSync('php',[`${root}/scripts/staging/console.php`,'queue:retry',failed],{stdio:'ignore'});
    execFileSync('php',[`${root}/scripts/staging/console.php`,'queue:work','database','--queue=mvp-notifications','--once','--tries=1'],{stdio:'ignore'});
  }
  result.checks.failedJobSafelyRecovered=sql("SELECT state FROM staging_operational_probes WHERE id='queue-recovery';")==='RECOVERED';
  resume();await tick(6000);
  let last=0;
  for(let i=0;i<12;i++)last=Number(execFileSync('curl',['--cacert',`${state}/tls/server.crt`,'-s','-o','/dev/null','-w','%{http_code}',
    '-H','Content-Type: application/json','--data',
    '{"email":"staging-abuse@agendaally.test","password":"STAGING_PASSWORD_MUST_NOT_BE_LOGGED"}',
    'https://localhost:8444/api/v1/auth/login'],{encoding:'utf8'}));
  result.checks.authAbuse429Visible=last===429&&has(monitor(),'LOGIN_ABUSE_429');
  result.checks.retainedOperationsUnchanged=retainedBefore===sql("SELECT id,state,amount_units FROM payment_financial_operations WHERE state IN('UNKNOWN','PENDING','CANCELED') ORDER BY id;");
  result.checks.noFinancialEffects=JSON.stringify(before)===JSON.stringify(financialTables(financialSnapshot()));
  execFileSync(process.execPath,[`${root}/scripts/staging/log-redaction.mjs`],{stdio:'ignore'});
  result.checks.selectedLogRedaction=JSON.parse(fs.readFileSync(`${state}/log-redaction.json`,'utf8')).status==='PASS';
  result.localResult=Object.values(result.checks).every(Boolean)?'PASS':'FAIL';
  result.status=result.localResult==='PASS'?'PARTIAL':'FAIL';
  result.externalDependency='No approved external alert destination, off-host monitoring owner, or live SMTP; late after-start reminder semantics not widened.';
}catch(error) {
  result.status='FAIL';result.error={type:error.name,message:error.message.slice(0,400)};
}finally {
  resume();
  fs.writeFileSync(`${state}/o1-rehearsals.json`,JSON.stringify(result,null,2));
}
console.log(JSON.stringify({status:result.status,localResult:result.localResult,checks:result.checks,error:result.error},null,2));
if(result.status==='FAIL')process.exitCode=1;