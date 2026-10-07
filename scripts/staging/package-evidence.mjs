import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {execFileSync} from 'node:child_process';
const root=process.cwd(),state='.local/staging-mvp',destination=`${state}/evidence-package`;
fs.mkdirSync(destination,{recursive:true});
const sensitive=/^(password|secret|authorization|cookie|set-cookie|token|access_token|refresh_token|plaintexttoken|app_key|session_secret)$/i;
function redact(value) {
  if(Array.isArray(value))return value.map(redact);
  if(value&&typeof value==='object')return Object.fromEntries(Object.entries(value).map(([key,item])=>
    [key,sensitive.test(key)&&typeof item==='string'?'[REDACTED]':redact(item)]));
  return typeof value==='string'?value.replace(/\bBearer\s+[A-Za-z0-9._|+/=-]+/g,'Bearer [REDACTED]'):value;
}
const files=[
  'docs/development/agendaally-mvp-readiness.md','docs/development/agendaally-mvp-readiness.html',
  'docs/development/smtp-email-presentation-audit.md',
  'docs/development/smtp-email-presentation-audit.html',
  `${state}/smtp-owner-delivery.json`,
  `${state}/email-presentation-before.json`, `${state}/email-presentation-after.json`,
  `${state}/calendar-implementation.md`,
  ...fs.readdirSync(state).filter(file=>/^(build-|b1-|d1-|u1-|r1-|o1-|log-redaction|final-|protected-|media-fixtures)/.test(file)
    &&file.endsWith('.json')).map(file=>`${state}/${file}`),
  ...fs.readdirSync(`${state}/history`).filter(file=>/\.(json|md|html|zip)$/.test(file)).map(file=>`${state}/history/${file}`),
];
const manifest=[];
for(const file of files) {
  if(!fs.existsSync(file))continue;
  let content=fs.readFileSync(file);
  if(file.endsWith('.json'))content=Buffer.from(JSON.stringify(redact(JSON.parse(content)),null,2)+'\n');
  const relative=file.startsWith(`${state}/`)?file.slice(state.length+1):file;
  const output=path.join(destination,relative);
  fs.mkdirSync(path.dirname(output),{recursive:true});fs.writeFileSync(output,content);
  manifest.push({source:file,archivePath:relative,bytes:content.length,sha256:crypto.createHash('sha256').update(content).digest('hex')});
}
const markdown=fs.readFileSync('docs/development/agendaally-mvp-readiness.md','utf8');
fs.writeFileSync(`${destination}/manifest.json`,JSON.stringify({
  title:'AgendaAlly same-authority scheduling and isolated staging evidence',
  decision:markdown.match(/Current decision: \*\*([^*]+)\*\*/)?.[1],
  same20Score:markdown.match(/Current same-20-gate score: \*\*([^*]+)\*\*/)?.[1],
  noProductionDeployment:true,
  screenshots:'Real browser screenshot IDs retained in receipts; archived pixels are not claimed.',
  history:'Pre-staging SAME reports/ZIP and superseded failure receipts retained.',
  exclusions:'No database dump/binlog, media backup, private auth state, dotenv, private key, raw log, cookie/session store or secret values.',
  files:manifest,
},null,2));
const target=path.join(root,'docs/development/agendaally-mysql-mvp-evidence.zip'),temporary=target+'.staging-new.zip';
execFileSync('zip',['-q','-r',temporary,'.'],{cwd:destination});fs.renameSync(temporary,target);
console.log(`Updated SAME evidence ZIP with ${manifest.length} current/historical redacted files.`);