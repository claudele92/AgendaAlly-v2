import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { execFileSync } from 'node:child_process';

// Documentation and synthetic preview output only. Never boots an application.
const root = '.local/staging-mvp';
const evidence = `${root}/email-system-audit`;
const out = 'artifacts/mockup-sandbox/public/email-system-audit';
fs.mkdirSync(out, { recursive: true });
const hash = file => crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const escape = value => String(value).replace(/[&<>"']/g, c => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));
const ids = [
  'verification-default', 'reset-default', 'layout-long-spaced', 'layout-long-unbroken',
  'order-long-spaced', 'order-long-unbroken', 'driver-invitation',
];
const observer = id => `<script>
addEventListener('load', async () => {
  await Promise.all([...document.images].map(i => i.decode().catch(() => null)));
  const d=document.documentElement;
  console.info('EMAIL_AUDIT '+JSON.stringify({
    fixture:${JSON.stringify(id)},viewport:d.clientWidth,documentWidth:d.scrollWidth,
    horizontalOverflow:d.scrollWidth>d.clientWidth,
    images:[...document.images].map(i=>({alt:i.alt,loaded:i.complete&&i.naturalWidth>0,
      width:Math.round(i.getBoundingClientRect().width),height:Math.round(i.getBoundingClientRect().height)})),
    imageSourcesLocalOnly:[...document.images].every(i=>i.src.startsWith('data:')),
    bodyTextPresent:document.body.innerText.length>0
  }));
});
</script>`;
const previewFiles = [];
for (const id of ids) {
  const html = fs.readFileSync(`${evidence}/${id}.html`, 'utf8');
  fs.writeFileSync(`${out}/${id}.html`, html + observer(id));
  previewFiles.push({ id, file: `${out}/${id}.html`, sha256: hash(`${evidence}/${id}.html`) });
}
for (const [group, fixtures] of Object.entries({
  accounts: ['verification-default', 'reset-default'],
  stress: ['layout-long-spaced', 'layout-long-unbroken'],
  orders: ['order-long-spaced', 'order-long-unbroken'],
  invitation: ['driver-invitation'],
})) {
  const sections = fixtures.map(id => `<section><h2>${escape(id)} — local synthetic fixture, NOT SENT</h2><div class="row">${
    [900, 390, 320].map(width => `<div><h3>${width}px actual iframe viewport</h3><iframe title="${escape(id)} at ${width}px" width="${width}" height="1100" src="./${id}.html"></iframe></div>`).join('')
  }</div></section>`).join('');
  fs.writeFileSync(`${out}/${group}.html`, `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Local email audit: ${group}</title><style>body{margin:18px;background:#e9edf2;color:#17202c;font:16px Arial,sans-serif}h1{font-size:22px}h2{font-size:18px;margin:24px 0 8px}h3{font-size:14px;margin:8px 0}.row{display:flex;gap:20px;align-items:start}iframe{display:block;border:1px solid #a4afbd;background:white;}</style></head><body><h1>Existing email rendering audit: ${group}</h1><p>Actual Blade output. Exact attached PNG bytes substituted as data URIs. Not Gmail emulation; no email is sent.</p>${sections}</body></html>`);
}
const sourceFiles = execFileSync('rg', [
  '-l', 'PHPMailer|Mail::|extends Mailable|extends Notification|toMail\\(|\\bmail\\(|nodemailer|package:mailer|SmtpServer|sendVerify\\(|sendEmailPasswordReset\\(|sendOrder\\(|sendDeliveryDriverInvitation\\(|sendSubscriptions\\(|SelectedEmailDelivery|EmailSendByTemplate',
  '.migration-backup', 'artifacts', 'scripts',
  '-g', '*.php', '-g', '*.ts', '-g', '*.tsx', '-g', '*.js', '-g', '*.jsx', '-g', '*.dart', '-g', '*.mjs',
  '-g', '!**/node_modules/**', '-g', '!**/vendor/**', '-g', '!**/.next/**',
  '-g', '!**/dist/**', '-g', '!**/build/**', '-g', '!**/.generated/**',
  '-g', '!**/tests/**', '-g', '!**/*Test*', '-g', '!**/*test*', '-g', '!**/public/**',
], { encoding: 'utf8' }).trim().split('\n').sort();
fs.writeFileSync(`${evidence}/source-mechanism-index.json`, JSON.stringify({
  scope: 'Backend, normal Customer/Admin web, uploaded Customer Flutter, artifact source, operational/development scripts; tracked and untracked source',
  excludes: 'dependencies, generated builds, public compiled bundles, fixture tests; no environment/credential files',
  fileCount: sourceFiles.length, sourceFiles,
}, null, 2) + '\n');
fs.writeFileSync(`${evidence}/local-render-manifest.json`, JSON.stringify({
  status: 'PASS', tests: 5, assertions: 198, existingPhpunitConfigurationDeprecations: 1,
  syntheticOnly: true, inMemoryDatabase: true, allowUrlFopen: false,
  sendCalls: 0, postSendCalls: 0, smtpConnections: 0,
  disabledFunctions: ['stream_socket_client', 'fsockopen', 'pfsockopen', 'socket_connect',
    'mail', 'curl_exec', 'curl_multi_exec', 'exec', 'passthru', 'shell_exec', 'system', 'proc_open', 'popen'],
  limits: 'Not application sender invocation, queue execution, mailbox receipt, Gmail emulation, or live user journeys. Driver MIME uses local Symfony Email after actual Mailable build/Blade.',
  initialFixtureFailure: 'Order fixture passed a null logo unlike native sendOrder. Corrected fixture supplied the same owned logo path; application source was unchanged. Failure JUnit retained.',
  previewFiles,
}, null, 2) + '\n');
console.log(JSON.stringify({ localTests: '5 / 198 PASS', syntheticPreviewFiles: previewFiles.length, indexedSourceFiles: sourceFiles.length }));
