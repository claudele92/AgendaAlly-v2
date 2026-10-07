import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {execFileSync} from 'node:child_process';
const root=process.cwd(),dir=path.join(root,'.local/staging-mvp');
if(!process.env.SESSION_SECRET)throw Error('Managed staging secret unavailable');
const secret=process.env.SESSION_SECRET;
for(const name of ['mysql','bootstrap/cache','private-logs','media','tls','releases',
  'storage/logs','storage/framework/cache/data','storage/framework/sessions','storage/framework/views'])
  fs.mkdirSync(path.join(dir,name),{recursive:true,mode:0o700});
if(!fs.existsSync(`${dir}/tls/server.key`)) {
  execFileSync('openssl',['req','-x509','-newkey','rsa:2048','-nodes','-days','30',
    '-subj','/CN=localhost','-addext','subjectAltName=DNS:localhost,IP:127.0.0.1',
    '-keyout',`${dir}/tls/server.key`,'-out',`${dir}/tls/server.crt`],{stdio:'ignore'});
  fs.chmodSync(`${dir}/tls/server.key`,0o600);
}
if(!fs.existsSync(`${dir}/mysql/auto.cnf`))
  execFileSync('mysqld',['--no-defaults','--initialize-insecure',`--datadir=${dir}/mysql`],{stdio:['ignore','ignore','ignore']});
const phpConfig=`[global]\npid=${dir}/php-fpm.pid\nerror_log=${dir}/private-logs/fpm.log\ndaemonize=no\n[staging]\nlisten=127.0.0.1:19009\nuser=${process.env.USER||'runner'}\ngroup=${process.env.USER||'runner'}\npm=ondemand\npm.max_children=2\npm.process_idle_timeout=10s\nclear_env=no\ncatch_workers_output=yes\nphp_admin_value[memory_limit]=192M\nphp_admin_flag[display_errors]=off\n`;
fs.writeFileSync(`${dir}/php-fpm.conf`,phpConfig);
const common=`location = /staging/health { include ${root}/scripts/staging/fastcgi.conf; }
location = /staging/simulation/failure { include ${root}/scripts/staging/fastcgi.conf; }
location /api/ { include ${root}/scripts/staging/fastcgi.conf; }
location /storage/ { alias ${dir}/media/; autoindex off; }
location ~ (^|/)\\. { deny all; }
location ~* \\.(php|env|key|pem|sqlite|sql)$ { deny all; }`;
const fastcgi=`fastcgi_pass 127.0.0.1:19009;
fastcgi_param SCRIPT_FILENAME ${root}/scripts/staging/http.php;
fastcgi_param SCRIPT_NAME /index.php;
fastcgi_param REQUEST_URI $request_uri;
fastcgi_param QUERY_STRING $query_string;
fastcgi_param REQUEST_METHOD $request_method;
fastcgi_param CONTENT_TYPE $content_type;
fastcgi_param CONTENT_LENGTH $content_length;
fastcgi_param SERVER_PROTOCOL $server_protocol;
fastcgi_param SERVER_NAME $host;
fastcgi_param SERVER_PORT $server_port;
fastcgi_param REMOTE_ADDR $remote_addr;
fastcgi_param HTTPS on;
fastcgi_param HTTP_HOST $http_host;
fastcgi_param HTTP_AUTHORIZATION $http_authorization;
fastcgi_param HTTP_COOKIE $http_cookie;
fastcgi_param HTTP_ORIGIN $http_origin;
fastcgi_param HTTP_ACCEPT $http_accept;
fastcgi_param HTTP_X_REQUESTED_WITH $http_x_requested_with;
fastcgi_param HTTP_X_XSRF_TOKEN $http_x_xsrf_token;
fastcgi_param HTTP_X_CSRF_TOKEN $http_x_csrf_token;
fastcgi_param HTTP_X_FORWARDED_PROTO https;
fastcgi_param HTTP_X_FORWARDED_HOST $http_host;
fastcgi_read_timeout 60s;`;
fs.writeFileSync(`${dir}/fastcgi.conf`,fastcgi);
const resolved=common.replaceAll(`${root}/scripts/staging/fastcgi.conf`,`${dir}/fastcgi.conf`);
fs.writeFileSync(`${dir}/nginx.conf`,`
worker_processes 1;
pid ${dir}/nginx.pid;
error_log ${dir}/private-logs/nginx-error.log;
events { worker_connections 128; }
http {
  include ${execFileSync('sh',['-c','dirname "$(readlink -f "$(command -v nginx)")"'],{encoding:'utf8'}).trim()}/../conf/mime.types;
  access_log ${dir}/private-logs/nginx-access.log;
  client_body_temp_path ${dir}/storage/client-body;
  proxy_temp_path ${dir}/storage/proxy;
  fastcgi_temp_path ${dir}/storage/fastcgi;
  server {
    listen 127.0.0.1:8443 ssl;
    server_name localhost;
    ssl_certificate ${dir}/tls/server.crt;
    ssl_certificate_key ${dir}/tls/server.key;
    ${resolved}
    location / { proxy_pass http://127.0.0.1:3102; proxy_set_header Host $http_host; proxy_set_header X-Forwarded-Proto https; }
  }
  server {
    listen 127.0.0.1:8444 ssl;
    server_name localhost;
    ssl_certificate ${dir}/tls/server.crt;
    ssl_certificate_key ${dir}/tls/server.key;
    ${resolved}
    root ${dir}/current/.migration-backup/admin/build;
    location / { try_files $uri $uri/ /index.html; }
  }
}`);
console.log('Prepared isolated FPM/Nginx/TLS configuration; no secret values serialized.');
if(process.argv.includes('--database')) {
  const socket=`${dir}/mysql/mysql.sock`;
  const sql=q=>execFileSync('mysql',['--no-defaults',`--socket=${socket}`,'-u','root'],{input:q,stdio:['pipe','ignore','pipe']});
  const password=crypto.createHmac('sha256',secret).update('AgendaAlly isolated staging DB app').digest('hex');
  sql(`CREATE DATABASE IF NOT EXISTS agendaally_staging_mvp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER IF NOT EXISTS 'agendaally_app'@'127.0.0.1' IDENTIFIED BY '${password}';
    ALTER USER 'agendaally_app'@'127.0.0.1' IDENTIFIED BY '${password}';
    GRANT SELECT,INSERT,UPDATE,DELETE ON agendaally_staging_mvp.* TO 'agendaally_app'@'127.0.0.1';`);
  const count=execFileSync('mysql',['--no-defaults',`--socket=${socket}`,'-u','root','-N','-e',
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='agendaally_staging_mvp'"],{encoding:'utf8'}).trim();
  if(count==='0') {
    const dump=execFileSync('mysqldump',['--no-defaults',`--socket=${root}/.local/booking-forward/mysql-data/mysql.sock`,
      '-u','root','--single-transaction','--routines','--events','--triggers','--set-gtid-purged=OFF',
      'agendaally_payment_disposable_booking_execution_2'],{maxBuffer:32*1024*1024});
    execFileSync('mysql',['--no-defaults',`--socket=${socket}`,'-u','root','agendaally_staging_mvp'],{input:dump,stdio:['pipe','ignore','pipe']});
    console.log('Restored isolated synthetic acceptance snapshot into separate staging MySQL; normal data untouched.');
  }
  fs.cpSync(`${root}/.local/booking-forward/http-isolation/media`,`${dir}/media`,{recursive:true});
  console.log('Restricted application principal ready; DDL/root authority is not used by runtime.');
}