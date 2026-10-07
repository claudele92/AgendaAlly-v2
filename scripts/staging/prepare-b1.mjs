import {execFileSync} from 'node:child_process';
const root=process.cwd(),socket=`${root}/.local/staging-mvp/mysql/mysql.sock`;
const sql=query=>execFileSync('mysql',['--no-defaults',`--socket=${socket}`,'-u','root'],{input:query,stdio:['pipe','ignore','pipe']});
sql('CREATE DATABASE agendaally_staging_mvp_b1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
const dump=execFileSync('mysqldump',['--no-defaults',`--socket=${socket}`,'-u','root','--single-transaction',
  '--routines','--events','--triggers','--set-gtid-purged=OFF','agendaally_staging_mvp'],{maxBuffer:32*1024*1024});
execFileSync('mysql',['--no-defaults',`--socket=${socket}`,'-u','root','agendaally_staging_mvp_b1'],
  {input:dump,stdio:['pipe','ignore','pipe']});
console.log('Copied current native schema/guards into dedicated B1 scratch database on staging MySQL; UI staging untouched.');