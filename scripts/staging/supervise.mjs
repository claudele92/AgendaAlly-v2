import fs from 'node:fs';
import path from 'node:path';
import {spawn} from 'node:child_process';
const root=process.cwd(),dir=path.join(root,'.local/staging-mvp');
const children=new Map();let stopping=false;
const wanted=['mysql','fpm','nginx'];
const paused=name=>{
  try{return JSON.parse(fs.readFileSync(`${dir}/operator-pauses.json`,'utf8')).includes(name);}catch{return false;}
};
const services={
  mysql:['mysqld',['--no-defaults',`--datadir=${dir}/mysql`,`--socket=${dir}/mysql/mysql.sock`,
    `--pid-file=${dir}/mysql/mysql.pid`,'--port=33309','--bind-address=127.0.0.1','--mysqlx=OFF',
    '--innodb-buffer-pool-size=48M','--innodb-redo-log-capacity=32M','--transaction-isolation=REPEATABLE-READ',
    '--sql-mode=STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION',
    '--character-set-server=utf8mb4','--collation-server=utf8mb4_unicode_ci','--default-time-zone=+00:00',
    '--server-id=91',`--log-bin=${dir}/mysql/staging-bin`,'--binlog-format=ROW']],
  fpm:['php-fpm',['--fpm-config',`${dir}/php-fpm.conf`]],
  nginx:['nginx',['-c',`${dir}/nginx.conf`,'-g','daemon off;']],
  customer:[process.execPath,[`${dir}/current/.migration-backup/web/node_modules/next/dist/bin/next`,
    'start','--hostname','127.0.0.1','--port','3102']],
  worker:['php',[`${root}/scripts/staging/console.php`,'queue:work','database','--queue=mvp-notifications',
    '--sleep=2','--tries=1','--timeout=30']],
  scheduler:[process.execPath,[`${root}/scripts/staging/scheduler.mjs`]],
  monitor:[process.execPath,[`${root}/scripts/staging/monitor.mjs`]],
};
function state() {
  fs.writeFileSync(`${dir}/process-state.json`,JSON.stringify({
    observedAt:new Date().toISOString(),services:Object.fromEntries([...children].map(([name,p])=>[name,{pid:p.pid}]))},null,2));
}
function launch(name) {
  const [binary,args]=services[name];
  const log=fs.openSync(`${dir}/private-logs/${name}.log`,'a');
  const env=name==='customer'?{
    PATH:process.env.PATH,HOME:process.env.HOME,NODE_ENV:'production',NEXT_TELEMETRY_DISABLED:'1',
    NODE_EXTRA_CA_CERTS:`${dir}/tls/server.crt`,
    NEXT_PUBLIC_APP_ENV:'staging',NEXT_PUBLIC_DEVELOPMENT_MODE:'false',
    NEXT_PUBLIC_BASE_URL:'https://localhost:8443/api/',NEXT_PUBLIC_WEBSITE_URL:'https://localhost:8443/',
    NEXT_PUBLIC_ADMIN_PANEL_URL:'https://localhost:8444/',NEXT_PUBLIC_IMAGE_URL:'https://localhost:8443/storage/',
    NEXT_PUBLIC_DEFAULT_LANGUAGE_CODE:'en',NEXT_PUBLIC_DEFAULT_TOKEN_TYPE:'Bearer',NEXT_PUBLIC_CACHE_TIME:'60',
    NEXT_PUBLIC_FIREBASE_ENABLED:'false',NEXT_PUBLIC_MAPS_ENABLED:'false',
  }:process.env;
  const child=spawn(binary,args,{cwd:name==='customer'?`${dir}/current/.migration-backup/web`:root,
    env,stdio:['ignore',log,log]});fs.closeSync(log);
  children.set(name,child);state();console.log(`Started ${name} pid=${child.pid}`);
  child.on('exit',(code,signal)=>{
    children.delete(name);state();console.log(`${name} exited (${signal||code})`);
    if(!stopping)setTimeout(()=>{if(!children.has(name)&&!paused(name))launch(name);},3000);
  });
}
for(const name of ['mysql','fpm','nginx'])launch(name);
if(fs.existsSync(`${dir}/current/.migration-backup/web/.next/BUILD_ID`)) {wanted.push('customer');launch('customer');}
if(process.argv.includes('--operations'))for(const name of ['worker','scheduler','monitor']) {wanted.push(name);launch(name);}
const ticker=setInterval(()=>{
  for(const name of wanted)if(!children.has(name)&&!paused(name)&&!stopping)launch(name);
  state();
},5000);
function shutdown() {if(stopping)return;stopping=true;clearInterval(ticker);for(const child of children.values())child.kill('SIGTERM');setTimeout(()=>process.exit(0),2000);}
process.on('SIGTERM',shutdown);process.on('SIGINT',shutdown);