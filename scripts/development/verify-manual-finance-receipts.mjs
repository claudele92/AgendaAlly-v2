import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';

// Offline verification of this explicitly owned campaign, never normal data.
const root = process.cwd();
const directory = path.resolve(process.argv[2] || '');
assert.match(directory, new RegExp(`^${root.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}/\\.local/manual-finance/http-identity-[a-f0-9]{16}$`));
assert(!fs.lstatSync(directory).isSymbolicLink(), 'Fixture cannot be a symlink');
const read = (name) => JSON.parse(fs.readFileSync(path.join(directory, name), 'utf8'));
const manifest = read('manifest.json');
assert.equal(manifest.directory, directory);
const synthetic = read('synthetic/manifest.json');
const browserDownloaded = new Set();
for (const [capture, originalName] of [
  ['download-refund-pdf', 'receipt.pdf'],
  ['download-refund-png', 'receipt.png'],
  ['download-payout-jpg', 'receipt.jpg'],
]) {
  const observed = read(`browser/${capture}.json`);
  assert.equal(observed.link_status, 200, 'Browser must observe actual native link issuance');
  assert.equal(observed.download_status, 200, 'Browser must observe actual native download');
  assert.equal(observed.redacted_url.actor, '3');
  assert.equal(observed.redacted_url.has_lang, false, 'Signed navigation must not append language parameters');
  const match = observed.redacted_url.path.match(/\/attachments\/([a-f0-9-]{36})\/download$/);
  assert(match, 'Browser capture must identify its native attachment');
  browserDownloaded.add(match[1]);
  const bytes = fs.readFileSync(path.join(directory, `browser/${capture}.bin`));
  const original = synthetic[originalName];
  assert.equal(bytes.length, original.bytes);
  assert.equal(createHash('sha256').update(bytes).digest('hex'), original.sha256);
  assert.equal(observed.headers['content-type'], original.mime);
  assert.match(observed.headers['content-disposition'], /^attachment; filename="receipt\.(pdf|png|jpg)"$/);
  assert.equal(observed.headers['x-content-type-options'], 'nosniff');
  assert.equal(observed.headers['content-security-policy'], "sandbox; default-src 'none'");
  assert.deepEqual(observed.headers['cache-control'].split(',').map((s) => s.trim()).sort(), ['no-store', 'private']);
  assert.equal(observed.headers['referrer-policy'], 'no-referrer');
}
const final = JSON.parse(execFileSync('php', [
  'scripts/development/manual-finance-receipt-fixture.php', 'snapshot', directory,
], { encoding: 'utf8', maxBuffer: 16 * 1024 * 1024 }));
const attachments = final.attachments;
assert.equal(attachments.length, 5, 'Three format uploads plus two native dialog reattachments are expected');
assert.equal(browserDownloaded.size, 3);
const workflowIds = Object.values(manifest.workflows).map((w) => w.id);
const expectedKind = { 'application/pdf': 'refund', 'image/png': 'refund', 'image/jpeg': 'payout' };
for (const file of attachments) {
  assert(workflowIds.includes(file.workflow_id), 'Attachment belongs to a campaign workflow');
  assert.equal(file.workflow_id, manifest.workflows[expectedKind[file.mime]].id);
  assert.equal(file.created_by, 3, 'Native actor, not client-selected uploader');
  assert.match(file.path, /^manual-finance\/[a-f0-9-]{36}\.(pdf|png|jpg)$/);
  const original = Object.values(synthetic).find((value) => value.mime === file.mime);
  assert(original, 'Only one of the three generated documents is allowed');
  assert.equal(file.sha256, original.sha256);
  assert.equal(file.retained_sha256, file.sha256);
  assert.equal(Number(file.bytes), original.bytes);
  assert.equal(file.retained_bytes, original.bytes);
  const retained = fs.readFileSync(path.join(directory, 'private', file.path));
  assert.equal(createHash('sha256').update(retained).digest('hex'), original.sha256);
  if (browserDownloaded.has(file.id)) {
    for (const action of ['link', 'download']) {
      assert(final.access.some((a) => a.attachment_id === file.id && a.actor_id === 3 && a.action === action),
        `Native ${action} audit required for browser-downloaded ${file.mime}`);
    }
  } else {
    assert(!final.access.some((a) => a.attachment_id === file.id),
      'Later completion reattachments must remain unviewed after the evidence grant is revoked');
  }
}
assert.equal(final.evidence.length, 2, 'Both disposable workflows must retain file-bound completion evidence');
for (const evidence of final.evidence) {
  const file = attachments.find((a) => a.id === evidence.attachment_id);
  assert(file, 'Completion evidence must bind an uploaded attachment');
  assert.equal(evidence.workflow_id, file.workflow_id);
  assert.equal(evidence.document_sha256, file.sha256);
  assert.equal(evidence.state, 'SUCCESS');
  assert.equal(evidence.recorded_by, 3);
  const workflow = final.effects.manual_financial_workflows.find((w) => w.id === evidence.workflow_id);
  assert.equal(workflow.state, 'COMPLETED');
  assert.equal(workflow.completed_by, 3);
  assert.equal(evidence.amount_units, workflow.amount_units);
  assert.equal(evidence.currency_code, workflow.currency_code);
  assert.equal(evidence.beneficiary_id, workflow.beneficiary_id);
  assert.equal(evidence.method, workflow.method);
}
assert(final.access.every((a) => a.actor_id === 3 && ['link', 'download'].includes(a.action)),
  'Denied identities must never generate successful evidence-access rows');
const permissionIds = final.effects.permissions
  .filter((p) => ['payments.refunds.evidence.view', 'payments.payouts.evidence.view'].includes(p.name))
  .map((p) => p.id);
assert(!final.effects.model_has_permissions.some((p) => p.model_id === 3 && permissionIds.includes(p.permission_id)),
  'Both evidence-view grants must be revoked in disposable authority');
const beforeRevoke = read('browser/pre-revocation.json');
const afterRevoke = read('browser/post-revocation.json');
assert.deepEqual(afterRevoke.access, beforeRevoke.access,
  'Revocation and subsequent denials must add no successful evidence-access rows');
const normal = read('normal-after.json');
assert.equal(normal.unchanged, true, 'Campaign must preserve every normal row and original schema');
assert.deepEqual(normal.changed_tables, []);
assert(fs.existsSync(path.join(directory, 'browser/report.json')), 'Browser report is required, not inferred from DB');
const report = read('browser/report.json');
assert(Object.keys(report).length > 0, 'Browser report cannot be empty');
assert.deepEqual(report.not_completed_or_unverified, [], 'Original browser requirements cannot remain deferred');
const continuation = read('browser/phase-continuation.json');
const denials = continuation.authorization_negatives;
assert.equal(denials.link_issuance_403_no_url.length, 10);
assert.equal(denials.upload_attempts_403.length, 4);
assert.equal(denials.uploads_were_denied_only, true);
assert.equal(denials.cross_workflow_link_refund_attachment_on_payout_404, true);
assert.equal(denials.signed_wrong_binding_download_404, true);
for (const name of [
  'cli_expired_link_browser_status', 'missing_signature_status', 'tampered_actor_status',
  'changed_expiry_status', 'tampered_signature_status',
]) assert.equal(denials[name], 403, `Browser denial required: ${name}`);
for (const key of ['customer2_native_api', 'vendor1_native_api']) {
  const projection = continuation.safe_projections[key];
  assert.equal(projection.list_status, 200);
  assert.equal(projection.detail_status, 200);
  assert.equal(projection.attachments_or_private_keys, false);
  assert.deepEqual(projection.evidence_actions, []);
}
assert.equal(continuation.safe_projections.admin4_foreign5_outsider7.rows, 0);
assert.equal(continuation.safe_projections.admin4_foreign5_outsider7.detail_status, 404);
assert.equal(continuation.safe_projections.vendor_ui.private_attachment_fields_or_view_action, false);
assert.equal(continuation.safe_projections.outsider_ui.access_warning_and_empty_scope, true);
const revoked = continuation.revocation;
assert.equal(revoked.cli_removed_grants, 2);
const retainedLinks = Object.entries(revoked).find(([key]) => key.startsWith('two_retained_links_issued_'))?.[1];
assert(retainedLinks, 'Fresh-link revocation observations are required');
assert.equal(retainedLinks.refund_status, 403);
assert.equal(retainedLinks.payout_status, 403);
assert.equal(retainedLinks.credentials_omitted, true);
assert.equal(retainedLinks.receipt_bytes_delivered, false);
assert.equal(revoked.new_link_issuance_refund_and_payout.status, 403);
assert.equal(revoked.refund_detail_ui_view_evidence_buttons, 0);
assert.equal(revoked.payout_detail_ui_view_evidence_buttons, 0);
assert.deepEqual(final.access, afterRevoke.access, 'Later completion must not regain evidence-view access');
const customer = report.final_acceptance.customer2_acceptance;
assert.equal(customer.native_finance_get_http, 200);
assert.equal(customer.native_finance_get_row_count, 1);
assert.equal(customer.private_attachment_hash_path_or_evidence_fields, false);
assert.equal(customer.visible_private_receipt_controls, false);
assert(customer.customer_screenshot, 'Customer native UI screenshot must be preserved');
const publicBoundary = read('browser/public-serving-boundary.json');
assert.equal(publicBoundary.checks.length, 9, 'Every format needs all three direct-path checks');
for (const check of publicBoundary.checks) {
  assert.equal(check.receipt_bytes_exposed, false);
  if (check.surface === 'native-storage') assert.equal(check.status, 404);
  if (check.surface === 'vite-sibling-filesystem') assert.equal(check.status, 403);
}
console.log(JSON.stringify({
  result: 'passed', native_ui_attachments: attachments.length,
  native_completion_evidence: final.evidence.length, access_audit_rows: final.access.length,
  exact_normal_tables_preserved: Object.keys(normal.tables).length,
  scope: 'Offline native binding/digest/access checks; browser report carries observed download headers and denials',
}, null, 2));
