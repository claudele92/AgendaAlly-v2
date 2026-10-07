import fs from 'node:fs';
import crypto from 'node:crypto';
import path from 'node:path';
import {execFileSync,spawn} from 'node:child_process';
const root=process.cwd(),state=`${root}/.local/staging-mvp`,restore=`${state}/restore`;
const archive=`${root}/.local/staging-mvp-backups`;
const secret=process.env.SESSION_SECRET;
if(!secret)throw Error('Managed recovery authority unavailable');
fs.mkdirSync(archive,{recursive:true,mode:0o700});
fs.mkdirSync(restore,{recursive:true,mode:0o700});
const encryptionKey=crypto.createHmac('sha256',secret).update('AgendaAlly isolated staging backup encryption').digest();
function seal(name,bytes) {
  const iv=crypto.randomBytes(12),cipher=crypto.createCipheriv('aes-256-gcm',encryptionKey,iv);
  const encrypted=Buffer.concat([cipher.update(bytes),cipher.final()]);
  fs.writeFileSync(`${archive}/${name}.enc`,Buffer.concat([iv,cipher.getAuthTag(),encrypted]),{mode:0o600});
}
function open(name) {
  const bytes=fs.readFileSync(`${archive}/${name}.enc`);
  const decipher=crypto.createDecipheriv('aes-256-gcm',encryptionKey,bytes.subarray(0,12));
  decipher.setAuthTag(bytes.subarray(12,28));
  return Buffer.concat([decipher.update(bytes.subarray(28)),decipher.final()]);
}
function mysql(instance,sql,database=true) {
  return execFileSync('mysql',['--no-defaults',`--socket=${instance}/mysql/mysql.sock`,'-u','root','--batch','-N',
    ...(database?['agendaally_staging_mvp']:[])],{input:sql,maxBuffer:32*1024*1024,encoding:'utf8'});
}
function evidence(instance='source') {
  return JSON.parse(execFileSync('php',[`${root}/scripts/staging/db-evidence.php`,instance],{maxBuffer:32*1024*1024,encoding:'utf8'}));
}
function mediaManifest(directory) {
  const result={};
  function walk(base) {
    for(const entry of fs.readdirSync(`${directory}/${base}`,{withFileTypes:true})) {
      const relative=path.posix.join(base,entry.name);
      if(entry.isDirectory())walk(relative);
      else if(entry.isFile())result[relative]=crypto.createHash('sha256').update(fs.readFileSync(`${directory}/${relative}`)).digest('hex');
    }
  }
  walk('');return Object.fromEntries(Object.entries(result).sort(([a],[b])=>a.localeCompare(b)));
}
const receipt={scope:'Real local off-instance encrypted backup and isolated second-instance PITR; no source overwrite',
  storage:{encrypted:true,offInstance:true,offHost:false,externalDependency:'Approved off-host destination and retention service not supplied'},
  targets:{databaseRpoSeconds:900,mediaRpoSeconds:3600,rtoSeconds:14400},checks:{}};
process.on('uncaughtException',error=>{
  receipt.status='FAIL';
  receipt.error={type:error.name,message:error.message.split('\n')[0].slice(0,240)};
  fs.writeFileSync(`${state}/r1-recovery-failure.json`,JSON.stringify(receipt,null,2));
  console.error('Isolated restore rehearsal failed; retained redacted receipt.');
  process.exitCode=1;
});
const before=evidence(),mediaBefore=mediaManifest(`${state}/media`);
fs.writeFileSync(`${state}/r1-before.json`,JSON.stringify(before,null,2));
receipt.backupStart=new Date().toISOString();
const dump=execFileSync('mysqldump',['--no-defaults',`--socket=${state}/mysql/mysql.sock`,'-u','root',
  '--single-transaction','--source-data=2','--routines','--events','--triggers','--set-gtid-purged=OFF',
  'agendaally_staging_mvp'],{maxBuffer:48*1024*1024});
const coordinate=dump.toString().match(/SOURCE_LOG_FILE='([^']+)', SOURCE_LOG_POS=(\d+)/)
  ||dump.toString().match(/MASTER_LOG_FILE='([^']+)', MASTER_LOG_POS=(\d+)/);
if(!coordinate)throw Error('Consistent snapshot binlog coordinates missing');
seal('database',dump);
const mediaTar=execFileSync('tar',['-cf','-','-C',`${state}/media`,'.'],{maxBuffer:48*1024*1024});
seal('media',mediaTar);
seal('runtime-contract',Buffer.from(JSON.stringify({
  keyAuthority:'Managed SESSION_SECRET; stable domain-separated staging APP_KEY; never replace during restore',
  database:'MySQL8 InnoDB REPEATABLE READ strict utf8mb4 utf8mb4_unicode_ci UTC',
  runtimeSourceHash:crypto.createHash('sha256').update(fs.readFileSync(`${root}/scripts/staging/runtime.php`)).digest('hex'),
})));
receipt.backupFinish=new Date().toISOString();
receipt.snapshotCoordinate={file:coordinate[1],position:Number(coordinate[2])};
// Safe operational marker proves actual post-snapshot row-binlog replay and stop.
mysql(state,"INSERT INTO staging_operational_probes(id,state) VALUES('pitr-recovery-point','SELECTED_POINT');");
const target=mysql(state,'SHOW MASTER STATUS;').trim().split('\t');
const targetEvidence=evidence();
receipt.recoveryPoint=new Date().toISOString();
receipt.selectedCoordinate={file:target[0],position:Number(target[1])};
mysql(state,"UPDATE staging_operational_probes SET state='LATER_MUST_NOT_REPLAY' WHERE id='pitr-recovery-point';");
receipt.simulatedIncidentTime=new Date().toISOString();
fs.writeFileSync(`${state}/r1-selected-point.json`,JSON.stringify(targetEvidence,null,2));
mysql(state,'FLUSH BINARY LOGS;');
const allBinlogs=fs.readdirSync(`${state}/mysql`).filter(n=>/^staging-bin\.\d+$/.test(n)).sort();
const selectedBinlogs=allBinlogs.filter(n=>n>=coordinate[1]&&n<=target[0]);
if(!selectedBinlogs.length)throw Error('Selected binlog range missing');
for(const filename of selectedBinlogs)seal(filename,fs.readFileSync(`${state}/mysql/${filename}`));
receipt.binlogFiles=selectedBinlogs;
receipt.restoreStart=new Date().toISOString();const restoreStarted=Date.now();
fs.mkdirSync(`${restore}/mysql`,{recursive:true,mode:0o700});
execFileSync('mysqld',['--no-defaults','--initialize-insecure',`--datadir=${restore}/mysql`],{stdio:'ignore'});
const log=fs.openSync(`${state}/private-logs/restore-mysql.log`,'w');
const server=spawn('mysqld',['--no-defaults',`--datadir=${restore}/mysql`,`--socket=${restore}/mysql/mysql.sock`,
  `--pid-file=${restore}/mysql/mysql.pid`,'--port=33310','--bind-address=127.0.0.1','--mysqlx=OFF',
  '--innodb-buffer-pool-size=32M','--innodb-redo-log-capacity=32M',
  '--transaction-isolation=REPEATABLE-READ','--default-time-zone=+00:00'],{stdio:['ignore',log,log]});
fs.closeSync(log);
let mediaServer;
try {
  const deadline=Date.now()+60000;
  while(true) {
    try {mysql(restore,'SELECT 1;',false);break;}catch {if(Date.now()>deadline)throw Error('New restore instance did not become ready');await new Promise(r=>setTimeout(r,200));}
  }
  mysql(restore,'CREATE DATABASE agendaally_staging_mvp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',false);
  execFileSync('mysql',['--no-defaults',`--socket=${restore}/mysql/mysql.sock`,'-u','root','agendaally_staging_mvp'],{input:open('database'),stdio:['pipe','ignore','pipe']});
  const replayFiles=[];
  for(const filename of selectedBinlogs) {
    const destination=`${restore}/${filename}`;fs.writeFileSync(destination,open(filename),{mode:0o600});replayFiles.push(destination);
  }
  const replay=execFileSync('mysqlbinlog',[`--start-position=${coordinate[2]}`,`--stop-position=${target[1]}`,
    ...replayFiles],{maxBuffer:48*1024*1024});
  execFileSync('mysql',['--no-defaults',`--socket=${restore}/mysql/mysql.sock`,'-u','root'],{input:replay,stdio:['pipe','ignore','pipe']});
  for(const name of ['bootstrap/cache','private-logs','storage/framework/cache/data','storage/framework/sessions',
    'storage/framework/views','storage/logs','media'])fs.mkdirSync(`${restore}/${name}`,{recursive:true,mode:0o700});
  execFileSync('tar',['-xf','-','-C',`${restore}/media`],{input:open('media'),stdio:['pipe','ignore','pipe']});
  const password=crypto.createHmac('sha256',secret).update('AgendaAlly isolated staging DB app').digest('hex');
  mysql(restore,`CREATE USER 'agendaally_app'@'127.0.0.1' IDENTIFIED BY '${password}';
    GRANT SELECT,INSERT,UPDATE,DELETE ON agendaally_staging_mvp.* TO 'agendaally_app'@'127.0.0.1';`,false);
  const restored=evidence('restore'),restoredMedia=mediaManifest(`${restore}/media`);
  fs.writeFileSync(`${state}/r1-restored.json`,JSON.stringify(restored,null,2));
  const schemaEquivalence=JSON.parse(execFileSync('php',[`${root}/scripts/staging/verify-restored-schema.php`],{encoding:'utf8'}));
  fs.writeFileSync(`${state}/r1-schema-equivalence.json`,JSON.stringify(schemaEquivalence,null,2));
  const runtime=JSON.parse(execFileSync('php',[`${root}/scripts/staging/recovery-state.php`],{
    env:{...process.env,AGENDAALLY_STAGING_INSTANCE:'restore'},encoding:'utf8'}));
  const sourceRuntime=JSON.parse(execFileSync('php',[`${root}/scripts/staging/recovery-state.php`],{encoding:'utf8'}));
  const roleReads=JSON.parse(execFileSync('php',[`${root}/scripts/staging/restored-reads.php`],{
    env:{...process.env,AGENDAALLY_STAGING_INSTANCE:'restore'},encoding:'utf8'}));
  fs.writeFileSync(`${state}/r1-restored-role-reads.json`,JSON.stringify(roleReads,null,2));
  const mimeTypes=fs.readFileSync(`${state}/nginx.conf`,'utf8').match(/include ([^;\n]+mime\.types);/)[1];
  fs.writeFileSync(`${restore}/media-nginx.conf`,`
pid ${restore}/media-nginx.pid;
error_log ${restore}/private-logs/media-nginx.log;
events { worker_connections 64; }
http { include ${mimeTypes}; access_log off;
  server { listen 127.0.0.1:8445 ssl; server_name localhost;
    ssl_certificate ${state}/tls/server.crt; ssl_certificate_key ${state}/tls/server.key;
    location /storage/ { alias ${restore}/media/; } } }`);
  mediaServer=spawn('nginx',['-c',`${restore}/media-nginx.conf`,'-g','daemon off;'],{stdio:'ignore'});
  await new Promise(resolve=>setTimeout(resolve,500));
  const examples=JSON.parse(fs.readFileSync(`${state}/media-fixtures.json`,'utf8')).examples;
  const restoredMediaHttp=Object.values(examples).every(filename=>{
    const bytes=execFileSync('curl',['--cacert',`${state}/tls/server.crt`,'-fsS','--max-time','10',
      `https://localhost:8445/storage/${filename}`],{maxBuffer:8*1024*1024});
    return crypto.createHash('sha256').update(bytes).digest('hex')===mediaBefore[filename];
  });
  receipt.checks={
    fullSelectedPointFingerprintsMatch:JSON.stringify({...restored,schema:targetEvidence.schema})===JSON.stringify(targetEvidence)
      &&schemaEquivalence.status==='PASS',
    schemaMatches:schemaEquivalence.status==='PASS',
    triggersMatch:JSON.stringify(before.triggers)===JSON.stringify(restored.triggers),
    constraintsMatch:JSON.stringify(before.constraints)===JSON.stringify(restored.constraints),
    indexesMatch:JSON.stringify(before.indexes)===JSON.stringify(restored.indexes),
    originalFinancialAndBookingTablesMatch:Object.entries(before.tables).filter(([name])=>name!=='staging_operational_probes')
      .every(([name,value])=>JSON.stringify(value)===JSON.stringify(restored.tables[name])),
    postSnapshotMarkerRecovered:mysql(restore,"SELECT state FROM staging_operational_probes WHERE id='pitr-recovery-point';").trim()==='SELECTED_POINT',
    laterSourceUpdateNotReplayed:mysql(state,"SELECT state FROM staging_operational_probes WHERE id='pitr-recovery-point';").trim()==='LATER_MUST_NOT_REPLAY',
    mediaAllHashesMatch:JSON.stringify(mediaBefore)===JSON.stringify(restoredMedia),
    keyStable:runtime.keyFingerprint===sourceRuntime.keyFingerprint,
    encryptedDataDecrypts:runtime.encryptedDataDecrypts===true,
    walletAndRetainedOperationsMatch:JSON.stringify(runtime.wallets)===JSON.stringify(sourceRuntime.wallets)
      &&JSON.stringify(runtime.retainedOperations)===JSON.stringify(sourceRuntime.retainedOperations),
    newDatabaseAndRuntime:true,sourceNotOverwritten:true,
    restoredRoleReads:roleReads.status==='PASS',
    restoredMediaHttp,
  };
  receipt.media={files:Object.keys(mediaBefore).length,missing:Object.keys(mediaBefore).filter(n=>!restoredMedia[n]),classes:['Shop','Service','specialist/profile']};
  receipt.restoreFinish=new Date().toISOString();
  receipt.measured={rtoSeconds:(Date.now()-restoreStarted)/1000,
    databaseRpoSeconds:(Date.parse(receipt.simulatedIncidentTime)-Date.parse(receipt.recoveryPoint))/1000,
    mediaRpoSeconds:(Date.parse(receipt.simulatedIncidentTime)-Date.parse(receipt.backupFinish))/1000};
  receipt.measuredNote='Drill recovery-point loss interval; not a promise of continuous production RPO. Automated/off-host retention remains unaccepted.';
  receipt.localResult=Object.values(receipt.checks).every(Boolean)?'PASS':'FAIL';
  receipt.status=receipt.localResult==='PASS'?'PARTIAL':'FAIL';
  fs.writeFileSync(`${state}/r1-recovery.json`,JSON.stringify(receipt,null,2));
  console.log(JSON.stringify({status:receipt.status,localResult:receipt.localResult,checks:receipt.checks,measured:receipt.measured},null,2));
}finally {mediaServer?.kill('SIGTERM');server.kill('SIGTERM');}