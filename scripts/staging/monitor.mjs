import fs from 'node:fs';
import os from 'node:os';
import crypto from 'node:crypto';
import {execFileSync} from 'node:child_process';
const state=`${process.cwd()}/.local/staging-mvp`;
const once=process.argv.includes('--once');
const diskThreshold=Number(process.argv.find(x=>x.startsWith('--disk-available='))?.split('=')[1]||10);
const connectionArgs=['--no-defaults',`--socket=${state}/mysql/mysql.sock`,'-u','root','-N','--batch','agendaally_staging_mvp'];
const sql=query=>execFileSync('mysql',[...connectionArgs,'-e',query],{encoding:'utf8',timeout:5000}).trim();
function alive(pid) {
  try {process.kill(pid,0);return !/^\d+ \(.+\) [TZ]/.test(fs.readFileSync(`/proc/${pid}/stat`,'utf8'));}catch{return false;}
}
function http(url) {
  try{return Number(execFileSync('curl',['--cacert',`${state}/tls/server.crt`,'-s','-o','/dev/null','-w','%{http_code}',
    '--max-time','8',url],{encoding:'utf8'}));}catch{return 0;}
}
function countStatus(filename,pattern) {
  try{return fs.readFileSync(filename,'utf8').split('\n').filter(line=>pattern.test(line)).length;}catch{return 0;}
}
function sample() {
  const snapshot={at:new Date().toISOString(),receiver:'Local operator: .local/staging-mvp/alerts.jsonl; no external delivery approved',components:{},metrics:{},alerts:[]};
  const alert=(code,details={})=>snapshot.alerts.push({code,...details});
  let processes={};
  try {processes=JSON.parse(fs.readFileSync(`${state}/process-state.json`,'utf8')).services;}catch{alert('SUPERVISOR_STATE_MISSING');}
  for(const name of ['mysql','fpm','nginx','customer','worker','scheduler']) {
    const running=alive(processes[name]?.pid);
    snapshot.components[name]={running};
    if(!running)alert(`PROCESS_${name.toUpperCase()}_DOWN`);
  }
  snapshot.components.customer.httpStatus=http('https://localhost:8443/');
  snapshot.components.business={httpStatus:http('https://localhost:8444/')};
  snapshot.components.api={httpStatus:http('https://localhost:8443/staging/health')};
  for(const name of ['customer','business','api'])if(snapshot.components[name].httpStatus!==200)alert(`HTTP_${name.toUpperCase()}_UNHEALTHY`);
  try {
    snapshot.metrics.db=Object.fromEntries(sql("SHOW GLOBAL STATUS WHERE Variable_name IN('Threads_connected','Threads_running','Aborted_connects','Innodb_deadlocks','Innodb_row_lock_waits');")
      .split('\n').filter(Boolean).map(line=>{const[k,v]=line.split('\t');return[k,Number(v)];}));
    snapshot.metrics.queueBacklog=Number(sql("SELECT COUNT(*) FROM jobs WHERE queue='mvp-notifications';"));
    snapshot.metrics.failedJobs=Number(sql('SELECT COUNT(*) FROM failed_jobs;'));
    snapshot.metrics.outboxIntervention=sql("SELECT state,COUNT(*) FROM selected_email_deliveries WHERE state IN('UNKNOWN','FAILED','BLOCKED') GROUP BY state;").split('\n').filter(Boolean);
    snapshot.metrics.retainedOperations=sql("SELECT id,state,amount_units FROM payment_financial_operations WHERE state IN('UNKNOWN','PENDING') ORDER BY id;")
      .split('\n').filter(Boolean).map(line=>{const[id,state,amountUnits]=line.split('\t');return{id,state,amountUnits};});
    snapshot.metrics.refundPayoutIntervention=sql("SELECT kind,state,COUNT(*) FROM payment_financial_operations WHERE kind LIKE '%refund%' OR kind LIKE '%payout%' GROUP BY kind,state;").split('\n').filter(Boolean);
    snapshot.metrics.lateReminderCandidates=Number(sql("SELECT COUNT(*) FROM bookings WHERE status IN('new','booked') AND start_date < UTC_TIMESTAMP() AND start_date >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 HOUR);"));
    if(snapshot.metrics.failedJobs)alert('FAILED_JOBS_VISIBLE',{count:snapshot.metrics.failedJobs});
    if(snapshot.metrics.queueBacklog)alert('SELECTED_QUEUE_BACKLOG',{count:snapshot.metrics.queueBacklog});
    if(snapshot.metrics.retainedOperations.length)alert('FINANCIAL_INTERVENTION_REQUIRED',{count:snapshot.metrics.retainedOperations.length,automaticResend:false,automaticReclassification:false});
    if(snapshot.metrics.lateReminderCandidates)alert('LATE_REMINDER_REVIEW_REQUIRED',{count:snapshot.metrics.lateReminderCandidates});
    if(snapshot.metrics.db.Innodb_deadlocks)alert('DB_DEADLOCK_HISTORY',{count:snapshot.metrics.db.Innodb_deadlocks});
  }catch {alert('DB_PROBE_FAILED');}
  try {
    const last=JSON.parse(fs.readFileSync(`${state}/scheduler-heartbeat.json`,'utf8'));
    snapshot.metrics.scheduler={ageSeconds:(Date.now()-Date.parse(last.at))/1000,lastExitCode:last.exitCode};
    if(snapshot.metrics.scheduler.ageSeconds>90||last.exitCode!==0)alert('SCHEDULER_HEARTBEAT_MISSED');
  }catch {alert('SCHEDULER_HEARTBEAT_MISSING');}
  const df=execFileSync('df',['-Pk',state],{encoding:'utf8'}).trim().split('\n').at(-1).trim().split(/\s+/);
  const inode=execFileSync('df',['-Pi',state],{encoding:'utf8'}).trim().split('\n').at(-1).trim().split(/\s+/);
  snapshot.metrics.resources={diskAvailablePct:100-Number(df[4].replace('%','')),
    inodeUsedPct:Number(inode[4].replace('%','')),loadAverage:os.loadavg(),cpuCount:os.cpus().length,
    memoryAvailableBytes:os.freemem(),memoryTotalBytes:os.totalmem()};
  if(snapshot.metrics.resources.diskAvailablePct<diskThreshold)alert('LOW_DISK_THRESHOLD',{threshold:diskThreshold,simulation:diskThreshold>100});
  if(snapshot.metrics.resources.inodeUsedPct>90)alert('INODE_THRESHOLD');
  const certificate=new crypto.X509Certificate(fs.readFileSync(`${state}/tls/server.crt`));
  snapshot.metrics.tls={expiresAt:certificate.validTo,remainingDays:(Date.parse(certificate.validTo)-Date.now())/86400000};
  if(snapshot.metrics.tls.remainingDays<14)alert('TLS_EXPIRY');
  snapshot.metrics.http5xx=countStatus(`${state}/private-logs/nginx-access.log`,/"\s5\d\d\s/);
  snapshot.metrics.login429=countStatus(`${state}/private-logs/nginx-access.log`,/"\s429\s/);
  let prior={};
  try {prior=JSON.parse(fs.readFileSync(`${state}/monitor-state.json`,'utf8'));}catch{}
  if(snapshot.metrics.http5xx>(prior.http5xx||0))alert('HTTP_5XX_INCREASE',{delta:snapshot.metrics.http5xx-(prior.http5xx||0)});
  if(snapshot.metrics.login429>(prior.login429||0))alert('LOGIN_ABUSE_429',{delta:snapshot.metrics.login429-(prior.login429||0)});
  fs.writeFileSync(`${state}/monitor-state.json`,JSON.stringify(snapshot.metrics));
  fs.writeFileSync(`${state}/monitor-latest.json`,JSON.stringify(snapshot,null,2));
  for(const item of snapshot.alerts)fs.appendFileSync(`${state}/alerts.jsonl`,JSON.stringify({at:snapshot.at,receiver:snapshot.receiver,...item})+'\n');
  console.log(JSON.stringify({at:snapshot.at,alerts:snapshot.alerts.map(x=>x.code),queue:snapshot.metrics.queueBacklog}));
  return snapshot;
}
sample();
if(!once)setInterval(sample,30000);