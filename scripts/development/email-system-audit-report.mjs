import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { createRequire } from 'node:module';

// Builds audit documentation/evidence only. No application bootstrap or transport.
const state = '.local/staging-mvp';
const proof = `${state}/email-system-audit`;
const docs = 'docs/development';
const B = '.migration-backup/backend';
const S = `${B}/app/Services/EmailSettingService/EmailSendService.php`;
const L = `${B}/resources/views/emails/layout.blade.php`;
const O = `${B}/resources/views/order-email-invoice.blade.php`;
const A = `${B}/app/Services/EmailSettingService/SelectedEmailDelivery.php`;
const J = `${B}/app/Jobs/SelectedAccountEmail.php`;
const D = `${B}/app/Services/DeliveryDriver/DriverInvitationService.php`;
const fields = [
  'Email/template name', 'Source files', 'Trigger', 'Recipient role',
  'Authoritative event/state', 'Execution mode', 'Enabled/suppressed state',
  'Shared AgendaAlly layout', 'Duplicated header/footer', 'Corrected logo handling',
  'Corrected social icons/links', 'External assets', 'Action URLs',
  'Sensitive information', 'Current MVP relevance', 'Actual delivery verified',
  'Recommended classification',
];
const rows = [];
function row(name, sources, trigger, recipient, authority, mode, enabled, shared, duplicate,
  logo, social, external, actions, sensitive, mvp, delivered, classification) {
  const values = [name, sources, trigger, recipient, authority, mode, enabled, shared, duplicate,
    logo, social, external, actions, sensitive, mvp, delivered, classification];
  if (values.length !== fields.length || values.some(v => typeof v !== 'string')) throw new Error('Invalid inventory row');
  rows.push(Object.fromEntries(fields.map((field, i) => [field, values[i]])));
}
const branded = 'YES — EmailPresentation owned raster CID; local exact-byte proof';
const socials = 'YES — Instagram/Facebook/LinkedIn PNG CID + normalized HTTPS; unconfigured X omitted';
const rich = 'No external assets in defaults. Privileged configured HTML can introduce remote assets';
const authState = 'General suppressed; missing selected_email_deliveries in normal DB. Migration applied only in prior disposable-MySQL N1 evidence';
row('Admin Test Email',
  `${B}/app/Http/Controllers/API/v1/Dashboard/Admin/EmailSettingController.php; ${B}/app/Helpers/AdminSmtpTestPolicy.php; ${S}; ${L}`,
  'Explicit approved Admin send-test request', 'Chosen intended diagnostic recipient',
  'Authenticated/authorized Admin tests the selected provider row; not a business event', 'Synchronous',
  'Only explicit Admin-test capability approved; not global delivery', 'YES', 'NO', branded, socials,
  'NO essential external assets', 'Configured normalized social URLs only',
  'Recipient envelope; diagnostic body contains no password/OTP', 'Diagnostic only',
  'YES — owner-confirmed SMTP, Gmail receipt and corrected logo/icons/footer', 'EXISTS + READY');
row('General User email verification',
  `${B}/app/Services/AuthService/AuthByEmail.php; ${B}/app/Http/Controllers/API/v1/Auth/VerifyAuthController.php; ${B}/app/Listeners/Mails/SendEmailVerificationListener.php; ${A}; ${J}; ${S}; ${L}`,
  'Native registration/resend; current recipient-bound challenge issued', 'Persisted platform User',
  'Unverified User, email-bound six-digit HMAC, cache-valid 10 minutes; consumed once', 'Queued after commit; selected recovery tick',
  authState, 'YES', 'NO', branded, socials, rich, 'No default action URL; recipient-bound code entry',
  'Expected OTP in message; payload encrypted at rest, recipient-bound; no unrelated data', 'REQUIRED',
  'NO for this workflow; do not infer from Admin test', 'EXISTS + NEEDS FUNCTIONAL/DELIVERY VERIFICATION');
row('Platform User password reset',
  `${B}/app/Http/Controllers/API/v1/Auth/LoginController.php; ${B}/app/Services/AuthService/PasswordResetService.php; ${A}; ${J}; ${S}; ${L}`,
  'Neutral recovery request for a known account, issuance limits', 'Persisted User matched to requested normalized email',
  'Email-bound challenge digest, 60-minute expiry, replacement/attempt limits and one-time consumption; actual password update follows', 'Queued after commit; selected recovery tick',
  authState, 'YES', 'NO', branded, socials, rich, 'No default action URL; code exchange requires recipient email',
  'Expected reset OTP; encrypted operational payload. Custom reset uses TYPE_VERIFY and needs separation', 'REQUIRED',
  'NO for reset delivery/completion', 'EXISTS + NEEDS FUNCTIONAL/DELIVERY VERIFICATION');
row('Dedicated Delivery Driver account verification',
  `${D}; ${B}/app/Listeners/Mails/SendEmailVerificationListener.php; ${S}; ${L}; ${B}/app/Http/Controllers/API/v1/Auth/VerifyAuthController.php`,
  'Dedicated contact-invitation registration/resend branch', 'User bound to pending Driver contact invitation',
  'Dedicated high-entropy native verification token/contact contract, not general Customer OTP', 'Synchronous legacy branch',
  'General delivery suppressed; Driver scope deferred', 'YES', 'NO',
  'Shared asset implementation; dedicated protocol not separately rendered in this audit', 'Shared social implementation', rich,
  'Default body is code text, not a new public link; dedicated native verification endpoint',
  'Expected dedicated challenge; redacted sender errors; no token printed in report', 'NOT current service-booking MVP',
  'NO', 'DEFERRED / NOT REQUIRED FOR CURRENT MVP');
for (const scheduled of [false, true]) row(
  scheduled ? 'Subscription/digest — scheduled path' : 'Subscription/digest — immediate Admin path',
  `${B}/app/Services/EmailTemplateService/EmailTemplateService.php; ${B}/app/Events/Mails/EmailSendByTemplate.php; ${B}/app/Listeners/Mails/EmailSendByTemplateListener.php; ${scheduled ? `${B}/app/Console/Commands/EmailSendByTime.php; ${B}/app/Console/Kernel.php; ` : ''}${S}; ${L}`,
  scheduled ? 'Legacy hourly selection by send_to/status/type' : 'Admin create/update with immediate-send intent',
  'All active EmailSubscription records with related User email',
  'Template/subscription state only; status marked before confirmed delivery', scheduled ? 'Scheduled hourly → synchronous listener send' : 'Synchronous event/listener send',
  'Suppressed; zero normal subscriptions/templates; no scheduler activated', 'YES', 'NO', branded, socials, rich,
  'Arbitrary privileged body URLs; no mandatory unsubscribe/List-Unsubscribe workflow',
  'HIGH: all recipients accumulated in visible To list; raw legacy transport diagnostics', 'Legacy marketing, not MVP',
  'NO', 'DEFERRED / NOT REQUIRED FOR CURRENT MVP');
for (const creation of [true, false]) row(
  creation ? 'Product order-email/invoice — creation path' : 'Product order-email/invoice — status path',
  `${B}/app/Services/OrderService/${creation ? 'OrderService' : 'OrderStatusUpdateService'}.php; ${S}; ${O}`,
  creation ? 'Order creation inside transaction, Shop email_statuses includes current status' : 'Permitted saved order status result, Shop email_statuses includes current status',
  'Order related User email', creation ? 'Created model before outer transaction commit; not durable success/capture proof' : 'Saved Order status, not proof of external payment settlement',
  'Synchronous; no selected operational outbox', 'General suppressed; Product purchasing deferred',
  'NO — standalone specialized view reuses asset helper', 'YES — separate header/footer/copyright', branded + '; legacy CSS 150×40 instead of shared 120×40', socials,
  'No product/gallery image references in this view; only owned branding', 'Social links only; no default order-management CTA',
  'Customer/address/items/money; transaction-status label not independent capture proof. No AltBody or PDF attachment',
  'NOT current service-booking purchase scope', 'NO', 'EXISTS + NEEDS PRESENTATION FIX');
for (const resend of [false, true]) row(
  resend ? 'Delivery Driver invitation — resend' : 'Delivery Driver invitation — issue',
  `${D}; ${B}/app/Http/Controllers/API/v1/DeliveryDriverInvitationController.php; ${S}; ${B}/app/Mail/DeliveryDriverInvitationMail.php; ${B}/resources/views/emails/delivery-driver-invitation.blade.php; ${B}/config/delivery_driver.php`,
  resend ? 'Permitted native resend with token/expiry rotation' : 'Persisted supported Driver contact invitation',
  'Bound invitation recipient, not a global role/email match',
  'Persisted Shop invitation/contact; seven-day expiry and native binding/revocation guards', 'Synchronous Laravel Mail/Mailable; not Admin provider-row PHPMailer',
  'Feature/default origin gated and general delivery suppressed; deferred', 'NO', 'NO — no branded header/footer at all',
  'NO logo', 'NO social footer', 'No image/font/CDN dependencies', 'Configured origin /delivery-driver-invitation with redacted token fragment; validity-only origin gate needs hardening',
  'Expected invitation action secret; escaped Shop text; no credentials supplied. Token not reproduced in report', 'NOT current MVP',
  'NO — local Blade/Symfony MIME only', 'EXISTS + NEEDS PRESENTATION FIX');
row('Framework Registered verification hook',
  `${B}/app/Providers/EventServiceProvider.php; ${B}/app/Models/User.php; framework Illuminate/Auth listener/notification`,
  'Registered event mapping exists, but no native application dispatch found', 'Framework User if a future emitter invokes it',
  'Framework verification capability is not native OTP protocol; compatibility/routes would need review', 'Framework synchronous notification unless separately queued',
  'No active application trigger found; native signup emits custom event instead', 'NO', 'Framework markup, not owned shared shell',
  'NO owned correction', 'NO owned social correction', 'Framework URL/from/configuration dependent',
  'Framework generated verify route, not established as a native public action flow', 'Verification capability/action token; do not enable accidentally',
  'NOT active native MVP path', 'NO', 'LEGACY / UNUSED — CANDIDATE FOR REMOVAL');
row('Framework password-reset notification capability',
  `${B}/app/Models/User.php; inherited framework reset trait/notification`,
  'No application Password-broker reset send call found; native LoginController uses selected reset instead', 'Framework User only if separately invoked',
  'Dormant inherited capability, not proof of native reset trigger or routes', 'Framework capability; no active queue/send path traced',
  'No active application caller found', 'NO', 'Framework markup, not owned shared shell', 'NO owned correction', 'NO owned social correction',
  'Framework configuration dependent', 'Framework reset action, not the native recipient-bound code completion',
  'Reset action capability; do not activate as a second recovery protocol', 'NOT active native MVP path', 'NO', 'LEGACY / UNUSED — CANDIDATE FOR REMOVAL');
row('Shared layout + owned asset/attachment helper (rendering infrastructure)',
  `${L}; ${B}/app/Support/EmailPresentation.php; ${B}/resources/email/icons/*.png`,
  'Called by actual PHPMailer sender views', 'N/A — not an independent email',
  'Presentation only; does not authorize accounts/bookings/money', 'Local rendering/CID preparation within caller',
  'Used by existing paths; no independent outbound policy', 'YES', 'NO',
  branded, socials, 'NO remote fetch; realpath/public-root file containment',
  'Allowlisted normalized social URLs; arbitrary raw content is a separate trust boundary',
  'No SMTP credential retrieval; owned public files only', 'Shared MVP infrastructure',
  'Baseline verified through Admin test only; no independent recipient', 'EXISTS + PRESENTATION VERIFIED LOCALLY');
row('Development/operations email controls and rehearsal harnesses',
  'scripts/development.mjs; scripts/development/admin-smtp-launch.mjs; scripts/staging/operations-probe.php; scripts/staging/operations-rehearsal.mjs; scripts/staging/test-smtp-settings.mjs',
  'Explicit developer/acceptance commands, not marketplace user events', 'Harness/explicit diagnostic recipient only under existing controls',
  'Fixture or capability context only; no new business authority', 'CLI/process controls or existing sender invocation',
  'Not executed in this audit; no workflow configuration/start change', 'Through the existing sender when applicable',
  'NO separate email shell', 'Existing sender-dependent', 'Existing sender-dependent',
  'No new asset dependency introduced by audit', 'No independent public action URL generated by audit',
  'Configuration/recipient handling remains privileged; no credential value accessed by audit',
  'Operational evidence/tools, not new MVP templates', 'No new delivery in audit', 'DEFERRED / NOT REQUIRED FOR CURRENT MVP');
function missing(name, recipient, event, relevance, sensitive = 'No emitted message; future fields require scoped/minimal disclosure') {
  row(name, 'No email implementation found — see retained repository mechanism index',
    'MISSING email trigger/template; any in-app/database relationship is not an email',
    recipient, event, 'N/A — proposal only', 'No outbound workflow', 'N/A — proposal would reuse approved shared shell',
    'N/A', 'N/A', 'N/A', 'N/A', 'N/A — no invented action URL', sensitive,
    relevance, 'NO', 'DEFERRED / NOT REQUIRED FOR CURRENT MVP');
}
missing('Customer booking created/confirmed email', 'Booking platform User',
  'Only successful committed creation, exact saved created/confirmed status; not Review/calculation', 'Useful later; current in-app channel accepted');
missing('Customer booking rescheduled email', 'Booking platform User',
  'Only real committed accepted start/end change; frozen native economics preserved', 'Useful later; current in-app channel accepted');
missing('Customer booking cancelled email', 'Booking platform User',
  'Only permitted committed cancellation; does not imply refund', 'Useful later; current in-app channel accepted');
missing('Appointment reminder email', 'Eligible platform User or separately approved scoped contact',
  'Saved eligible appointment, due window, correct channel preference, no cancelled/superseded occurrence', 'Optional later; existing reminder is in-app/push, not mail');
missing('Meaningful additional booking-status email', 'Authorized booking recipients',
  'No email for every status; only separately approved meaningful persisted change', 'Not required now');
missing('Vendor/Specialist booking operational/assignment email', 'Shop owner/current accepted same-Shop assigned Specialist',
  'Committed creation/reschedule/cancellation or authorized assignment event; never a global Specialist role list', 'Useful later; in-app/calendar already current channel');
missing('Local-client booking email', 'Valid Shop-scoped client contact, never auto-linked User',
  'Approved contact/preference contract plus committed Booking local_client_id; no User/account authority', 'Deferred until explicit local-client communication contract',
  'No credentials, account verification/login CTA or email-matched global linking');
missing('Password-changed/security notice', 'Affected platform User',
  'Only successful committed password/security change, not code request or preview', 'Useful follow-up, not current C1/O4 prerequisite');
missing('Welcome/account activation email', 'Verified/activated platform User',
  'Separate welcome trigger absent; verified state alone does not prove message', 'Optional, not current gate requirement');
missing('Specialist/Shop team invitation email', 'Recipient of supported same-Shop invitation',
  'Existing invitation relationship plus approved notice; no invented expiry/account grant', 'Useful later; native relationship/in-app path exists');
missing('Vendor/Admin/country invitation or approval email', 'Authorized invited/approved identity',
  'No separate mail trigger found; persisted relationship/approval is not delivery evidence', 'Optional/deferred governance/onboarding notice');
missing('Payment receipt/confirmation; refund; payout; Vendor settlement email', 'Only authoritative financial beneficiary/account recipient',
  'DEFERRED — FINANCIAL AUTHORITY NOT YET CERTIFIED; never cash selection, booking status, legacy credits or reservations', 'Deferred financial/product features',
  'PAID/COLLECTED/REFUNDED/SETTLED/PAYOUT COMPLETE claims require certified original evidence');

const csv = [fields, ...rows.map(r => fields.map(f => r[f]))]
  .map(values => values.map(v => `"${v.replaceAll('"', '""')}"`).join(',')).join('\r\n') + '\r\n';
fs.writeFileSync(`${docs}/agendaally-email-inventory.csv`, '\uFEFF' + csv);
fs.writeFileSync(`${proof}/inventory.json`, JSON.stringify({ fields, rows }, null, 2) + '\n');
const escape = value => String(value).replace(/[&<>"']/g, c => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));
const matrix = `<p>${rows.length} inventory entries. Each expandable entry includes all 17 fields. Missing/deferred capabilities are explicitly not implemented.</p><p><a download="agendaally-email-inventory.csv" href="data:text/csv;charset=utf-8;base64,${Buffer.from('\uFEFF' + csv).toString('base64')}">Download the full 17-column inventory CSV</a></p>` +
  rows.map(r => `<details><summary>${escape(r[fields[0]])} <small>${escape(r[fields[16]])}</small></summary><dl>${
    fields.map(f => `<dt>${escape(f)}</dt><dd>${escape(r[f])}</dd>`).join('')
  }</dl></details>`).join('');
const screenshots = [
  ['accounts-widths.jpg', 'Verification default at 900 / 390 / 320px. All branding images loaded from exact attached bytes.'],
  ['reset-320.jpg', 'Reset default at 320px. Code is deliberately synthetic; no message was sent.'],
  ['layout-spaced-320.jpg', 'Spaced long fields and CTA wrap at 320px; the shared footer remains visible. This is layout stress content, not a booking template.'],
  ['unbroken-320.jpg', 'Finding: a 320px viewport has 993px document width for unbroken fields; content/logo/footer can be offscreen.'],
  ['order-spaced-320.jpg', 'Actual order-email Blade at 320px with synthetic long fields. No PDF attachment or text alternative was invented.'],
];
const figures = '<div class="figures">' + screenshots.map(([name, caption]) =>
  `<figure><img src="data:image/jpeg;base64,${fs.readFileSync(`${proof}/${name}`).toString('base64')}" alt="${escape(caption)}"><figcaption>${escape(caption)}</figcaption></figure>`
).join('') + '</div>';
const require = createRequire(import.meta.url);
const packageDir = fs.readdirSync('node_modules/.pnpm').find(name => name.startsWith('markdown-it@'));
if (!packageDir) throw new Error('Installed markdown renderer unavailable');
const MarkdownIt = require(path.resolve(`node_modules/.pnpm/${packageDir}/node_modules/markdown-it`));
const md = new MarkdownIt({ html: false, linkify: false });
const style = `body{margin:0;background:#f3f5f7;color:#192536;font:16px/1.6 Arial,Helvetica,sans-serif}main{max-width:1160px;margin:24px auto;padding:32px;background:white}h1,h2,h3{line-height:1.25}h2{margin-top:38px;border-top:1px solid #d9dee5;padding-top:20px}table{border-collapse:collapse;display:block;overflow:auto;width:100%;font-size:14px}td,th{border:1px solid #d7dce3;padding:9px;text-align:left;vertical-align:top}th{background:#eef2f6}code{font-size:.9em;overflow-wrap:anywhere}a{color:#175b96}details{border:1px solid #d7dce3;padding:12px;margin:8px 0}summary{cursor:pointer;font-weight:bold}summary small{display:block;color:#526275;font-size:12px}dl{display:grid;grid-template-columns:220px 1fr;gap:6px 16px;font-size:14px}dt{font-weight:bold}dd{margin:0;overflow-wrap:anywhere}.figures{display:flex;flex-wrap:wrap;gap:20px;align-items:start}figure{margin:0;max-width:360px;background:#f3f5f7;padding:12px}figure:first-child{max-width:100%}figure img{display:block;max-width:100%;height:auto}figure:not(:first-child) img{max-height:1000px;margin:auto}figcaption{font-size:13px;margin-top:10px}@media(max-width:600px){main{padding:16px;margin:0;font-size:14px}dl{grid-template-columns:1fr}dd{margin-bottom:10px}figure{max-width:100%}}`;
const source = fs.readFileSync(`${docs}/agendaally-email-system-audit.md`, 'utf8');
if ((source.match(/^## \d+\./gm) || []).length !== 30) throw new Error('Report must contain exactly 30 requested sections');
const body = md.render(source)
  .replace('<p>[[EMAIL_INVENTORY_MATRIX]]</p>', matrix)
  .replace('<p>[[EMAIL_SCREENSHOTS]]</p>', figures);
if (body.includes('[[EMAIL_')) throw new Error('Report placeholders unresolved');
fs.writeFileSync(`${docs}/agendaally-email-system-audit.html`, `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AgendaAlly complete email-system MVP audit</title><style>${style}</style></head><body><main>${body}</main></body></html>\n`);

// Preserve previous owner receipt as history; current attachment explicitly confirms corrected Gmail presentation.
const receiptPath = `${state}/smtp-owner-delivery.json`;
const previous = JSON.parse(fs.readFileSync(receiptPath, 'utf8'));
const currentSource = 'attached_assets/Pasted-EMAIL-SYSTEM-MVP-AUDIT-COMPLETE-TEMPLATE-INVENTORY-PRES_1791231045626.txt';
const sha = file => crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
if (!fs.existsSync(`${proof}/smtp-owner-delivery-prior.json`)) {
  fs.writeFileSync(`${proof}/smtp-owner-delivery-prior.json`, JSON.stringify(previous, null, 2) + '\n');
}
const receipt = {
  ...previous,
  source: 'Owner explicitly confirms Admin SMTP success, Gmail delivery and corrected logo/Instagram/Facebook/LinkedIn/footer presentation',
  sourceSha256: sha(currentSource), correctedPresentationGmailReceipt: 'VERIFIED',
  scope: 'Current-build normal Admin diagnostic only; not verification/reset/booking/worker mailbox acceptance',
  agentSmtpConnections: 0, agentEmailsSent: 0, credentialsRetrieved: false, generalEmailActivation: false,
};
fs.writeFileSync(receiptPath, JSON.stringify(receipt, null, 2) + '\n');
fs.writeFileSync(`${proof}/smtp-owner-delivery-current.json`, JSON.stringify(receipt, null, 2) + '\n');
const oldProofPath = `${state}/build-email-presentation-local.json`;
if (fs.existsSync(oldProofPath)) {
  const oldProof = JSON.parse(fs.readFileSync(oldProofPath, 'utf8'));
  oldProof.correctedAdminPresentationGmailReceipt = 'VERIFIED — owner confirmation';
  oldProof.remainingLimit = 'Actual verification/reset/booking/worker mailbox journeys remain unverified; Admin baseline receipt is verified';
  fs.writeFileSync(oldProofPath, JSON.stringify(oldProof, null, 2) + '\n');
}
const observationRows = [
  ['verification-default', [900, 390, 320]],
  ['reset-default', [900, 390, 320]],
  ['layout-long-spaced', [900, 390, 320]],
  ['layout-long-unbroken', [993, 993, 993]],
  ['order-long-spaced', [900, 390, 320]],
  ['order-long-unbroken', [1251, 1121, 1121]],
  ['driver-invitation', [900, 390, 320]],
].flatMap(([fixture, widths]) => [900, 390, 320].map((viewport, i) => ({
  fixture, viewport, documentWidth: widths[i], horizontalOverflow: widths[i] > viewport,
  source: 'Screenshot browser console EMAIL_AUDIT DOM measurements; individual 320px follow-up captures retained',
  imagesLoaded: true, externalImageFetches: false,
})));
fs.writeFileSync(`${proof}/responsive-observations.json`, JSON.stringify({
  method: 'Actual Blade fixture documents in width-fixed same-origin iframes; browser screenshot observations, not Gmail emulation',
  rows: observationRows, screenshots: screenshots.map(([file]) => ({ file, sha256: sha(`${proof}/${file}`) })),
}, null, 2) + '\n');
fs.writeFileSync(`${proof}/report-manifest.json`, JSON.stringify({
  date: '2026-10-05', sections: 30, inventoryEntries: rows.length, inventoryFields: fields.length,
  currentScore: '16/20 = 80%', decision: 'REMAIN IN STAGING', C1: 0.5, O4: 0.5,
  conditionalOneGate: '16.5/20 = 82.5%', conditionalBoth: '17/20 = 85%',
  minimumFutureRealEmails: 2, emailsSentByAudit: 0, smtpConnectionsByAudit: 0,
  implementationApproved: false, workflowChanges: false,
  generated: ['agendaally-email-system-audit.html', 'agendaally-email-inventory.csv'],
}, null, 2) + '\n');
console.log(JSON.stringify({ sections: 30, inventoryEntries: rows.length, fields: fields.length, score: '16/20', correctedAdminGmail: 'VERIFIED', sent: 0 }));
