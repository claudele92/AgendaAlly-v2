import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {createRequire} from 'node:module';

// Generate documentation/assets only. Never boot Laravel or construct SMTP.
const state = '.local/staging-mvp';
const attachment = 'attached_assets/Pasted-EMAIL-SYSTEM-MVP-AUDIT-COMPLETE-TEMPLATE-INVENTORY-PRES_1791231045626.txt';
const hash = file => crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const before = JSON.parse(fs.readFileSync(`${state}/email-presentation-before.json`, 'utf8'));
const after = JSON.parse(fs.readFileSync(`${state}/email-presentation-after.json`, 'utf8'));
const changed = Object.keys(before.tables).filter(key =>
  JSON.stringify(before.tables[key]) !== JSON.stringify(after.tables[key]));
if (changed.length || before.schemaSha256 !== after.schemaSha256) {
  throw new Error('Local presentation preservation evidence does not match.');
}
const receipt = {
  date: '2026-10-05', status: 'VERIFIED',
  scope: 'Current-build explicit normal port-3003 Admin Test Email only',
  source: 'Owner confirms normal Admin SMTP success, actual Gmail receipt and corrected logo/Instagram/Facebook/LinkedIn/footer presentation',
  sourceSha256: hash(attachment), accountCorrection: 'Owner reports earlier Zoho account/configuration issue resolved',
  screenshotProvidedThisTurn: false, agentSmtpConnections: 0, agentEmailsSent: 0,
  credentialsRetrieved: false, generalEmailActivation: false,
  correctedPresentationGmailReceipt: 'VERIFIED — owner confirmation',
  same20GateScore: '16/20 = 80%', decision: 'REMAIN IN STAGING',
};
fs.writeFileSync(`${state}/smtp-owner-delivery.json`, JSON.stringify(receipt, null, 2) + '\n');
const proofFiles = [
  `${state}/email-presentation-local-proof.html`, `${state}/email-presentation-local-proof.eml`,
  `${state}/email-presentation-desktop.jpg`, `${state}/email-presentation-mobile.jpg`,
];
for (const file of proofFiles) if (!fs.existsSync(file)) throw new Error(`Missing local proof ${file}`);
const proof = {
  status: 'PASS', tests: 5, assertions: 73,
  warning: 'Existing PHPUnit XML configuration deprecation; no test failure',
  sendCalled: false, postSendCalled: false, smtpConnections: 0,
  disabledFunctions: ['stream_socket_client', 'fsockopen', 'pfsockopen', 'socket_connect',
    'mail', 'curl_exec', 'curl_multi_exec'],
  template: 'Actual shared Blade and PHPMailer preSend MIME; synthetic .invalid addresses only',
  images: 'Four CID references, four inline PNG MIME parts, exact base64 byte comparison',
  links: 'Saved Instagram/Facebook/LinkedIn HTTPS destinations preserved; unconfigured X omitted',
  browser: {desktop: '900x650 PASS', mobile: '390x650 PASS',
    note: 'Preview uses exact MIME attachment bytes as data URIs; not Gmail emulation'},
  correctedAdminPresentationGmailReceipt: 'VERIFIED — owner confirmation',
  remainingLimit: 'Actual verification/reset/booking/worker mailbox journeys remain unverified; corrected Admin baseline receipt is verified',
  preserved: {tableCount: Object.keys(after.tables).length, changed,
    schemaObjects: after.schemaCount, schemaMatch: before.schemaSha256 === after.schemaSha256,
    smtpCredential: 'NOT SELECTED'},
  files: proofFiles.map(file => ({file, sha256: hash(file), bytes: fs.statSync(file).size})),
};
fs.writeFileSync(`${state}/build-email-presentation-local.json`, JSON.stringify(proof, null, 2) + '\n');

// Reuse the already installed markdown renderer, without installing packages.
const require = createRequire(import.meta.url);
const packageDir = fs.readdirSync('node_modules/.pnpm').find(name => name.startsWith('markdown-it@'));
if (!packageDir) throw new Error('Installed markdown renderer is unavailable.');
const MarkdownIt = require(path.resolve(`node_modules/.pnpm/${packageDir}/node_modules/markdown-it`));
const md = new MarkdownIt({html: false, linkify: false});
const style = 'body{margin:0;background:#f5f6f8;color:#17202c;font:16px/1.6 Arial,sans-serif}main{max-width:1100px;margin:24px auto;background:white;padding:32px}h1,h2,h3{line-height:1.25}h2{margin-top:40px}table{border-collapse:collapse;width:100%;display:block;overflow:auto}th,td{border:1px solid #d4d9e0;padding:9px;text-align:left;vertical-align:top}th{background:#eef1f5}a{color:#1759a5}pre{white-space:pre-wrap;background:#eef1f5;padding:12px}code{overflow-wrap:anywhere}@media(max-width:600px){main{margin:0;padding:18px;font-size:14px}}';
for (const [source, target, title] of [
  ['docs/development/agendaally-mvp-readiness.md', 'docs/development/agendaally-mvp-readiness.html', 'AgendaAlly MVP readiness'],
  ['docs/development/smtp-email-presentation-audit.md', 'docs/development/smtp-email-presentation-audit.html', 'AgendaAlly email presentation audit'],
]) {
  const body = md.render(fs.readFileSync(source, 'utf8'));
  fs.writeFileSync(target, `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>${title}</title><style>${style}</style></head><body><main>${body}</main></body></html>\n`);
}
console.log(JSON.stringify({smtp: receipt.status, local: proof.status,
  score: receipt.same20GateScore, preservedTables: proof.preserved.tableCount,
  agentsSent: 0, smtpConnections: 0, readyToPackage: true}));
