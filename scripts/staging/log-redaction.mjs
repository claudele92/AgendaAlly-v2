import fs from 'node:fs';
import crypto from 'node:crypto';
const state=`${process.cwd()}/.local/staging-mvp`;
const secret=process.env.SESSION_SECRET;
if(!secret)throw Error('Managed authority unavailable');
const forbidden=[
  ['managedSecret',secret],
  ['derivedAppKey',`base64:${crypto.createHmac('sha256',secret).update('AgendaAlly isolated staging APP_KEY').digest('base64')}`],
  ['derivedDatabasePassword',crypto.createHmac('sha256',secret).update('AgendaAlly isolated staging DB app').digest('hex')],
  ['authorizationCanary','Bearer STAGING_AUTHORIZATION_MUST_NOT_BE_LOGGED'],
  ['passwordCanary','STAGING_PASSWORD_MUST_NOT_BE_LOGGED'],
  ['resetChallengeCanary','STAGING_RESET_CHALLENGE_MUST_NOT_BE_LOGGED'],
  ['invitationChallengeCanary','STAGING_INVITATION_CHALLENGE_MUST_NOT_BE_LOGGED'],
  ['merchantCredentialCanary','STAGING_MERCHANT_CREDENTIAL_MUST_NOT_BE_LOGGED'],
];
const files=fs.readdirSync(`${state}/private-logs`).filter(name=>/\.(log|jsonl)$/.test(name));
const hits=[];
for(const filename of files) {
  const text=fs.readFileSync(`${state}/private-logs/${filename}`,'utf8');
  for(const [category,value]of forbidden)if(text.includes(value))hits.push({file:filename,category});
}
const result={status:hits.length?'FAIL':'PASS',files:files.length,categories:forbidden.map(([name])=>name),hits,
  scope:'Current selected staging logs scanned without exposing authority values; no real provider payload exists to certify'};
fs.writeFileSync(`${state}/log-redaction.json`,JSON.stringify(result,null,2));
console.log(JSON.stringify(result));
if(hits.length)process.exitCode=1;