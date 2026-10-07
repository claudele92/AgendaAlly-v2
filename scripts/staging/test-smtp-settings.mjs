// Bounded, synthetic, inactive SMTP CRUD verification. Never enables/sends SMTP.
import fs from 'node:fs';
import https from 'node:https';
import { spawnSync } from 'node:child_process';
import assert from 'node:assert/strict';
import { emailSettingPayload } from '../../.migration-backup/admin/src/views/email-provider/credential-form.mjs';

const state = `${process.cwd()}/.local/staging-mvp`;
const auth = JSON.parse(fs.readFileSync(`${state}/private-auth/107.json`, 'utf8'));
const token = auth.origins.find(o => o.origin === 'https://localhost:8444').localStorage.find(s => s.name === 'token').value;
const ca = fs.readFileSync(`${state}/tls/server.crt`);
const base = '/api/v1/dashboard/admin/email-settings';
const initial = 'SYNTHETIC-ONLY-INITIAL-ACCEPTANCE';
const replacement = 'SYNTHETIC-ONLY-REPLACEMENT-ACCEPTANCE';
const logPath = `${state}/private-logs/application.log`;
const offset = fs.existsSync(logPath) ? fs.statSync(logPath).size : 0;
let id;
const proof = {};
function request(method, path, body) {
  return new Promise((resolve, reject) => {
    const json = body === undefined ? undefined : JSON.stringify(body);
    const req = https.request({ hostname: 'localhost', port: 8444, path, method, ca,
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json',
        ...(json ? { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(json) } : {}) } },
      res => {
        let text = ''; res.on('data', b => text += b);
        res.on('end', () => {
          try {
            assert.ok(!text.includes(initial) && !text.includes(replacement), 'Response leaked synthetic credential');
            resolve({ status: res.statusCode, body: JSON.parse(text) });
          } catch { reject(new Error('Invalid or credential-bearing response (details suppressed)')); }
        });
      });
    req.on('error', () => reject(new Error('Isolated API request failed (details suppressed)')));
    req.setTimeout(15000, () => req.destroy());
    if (json) req.write(json);
    req.end();
  });
}
function hash() {
  const child = spawnSync('php', ['-r', `require "scripts/staging/db-evidence.php";$pdo=stagingRootPdo();$s=$pdo->prepare("SELECT password FROM email_settings WHERE id=?");$s->execute([${id}]);$v=$s->fetchColumn();echo json_encode(["encrypted"=>str_starts_with($v, "encrypted:v1:"),"hash"=>hash("sha256",$v)]);`], { encoding: 'utf8' });
  assert.equal(child.status, 0, 'Internal metadata check failed');
  return JSON.parse(child.stdout);
}
try {
  const create = await request('POST', base, emailSettingPayload({
    host: 'smtp.example.invalid', port: 465, from_to: 'fixture@example.invalid',
    from_site: 'Synthetic SMTP API acceptance', smtp_auth: true, smtp_debug: false,
    active: false, password: initial,
  }));
  assert.equal(create.status, 200, 'Synthetic create must succeed');
  // Native create deliberately returns an empty data array, not the new model.
  const createdId = spawnSync('php', ['-r', 'require "scripts/staging/db-evidence.php";$p=stagingRootPdo();$s=$p->prepare("SELECT id FROM email_settings WHERE host=? AND from_site=?");$s->execute(["smtp.example.invalid","Synthetic SMTP API acceptance"]);echo $s->fetchColumn();'], { encoding: 'utf8' });
  assert.equal(createdId.status, 0, 'Synthetic id lookup failed');
  id = Number(createdId.stdout);
  assert.ok(Number.isInteger(id) && id > 0, 'Missing synthetic id');
  const before = hash();
  assert.ok(before.encrypted);
  const get = await request('GET', `${base}/${id}`);
  assert.equal(get.status, 200);
  assert.ok(!Object.hasOwn(get.body.data, 'password'));
  assert.equal(get.body.data.password_configured, true);
  assert.equal(get.body.data.admin_test_available, false);
  proof.encryptedAndPasswordFreeGet = true;
  const values = { ...get.body.data, active: false, from_site: 'Edited synthetic fixture', password: '' };
  const blank = await request('PUT', `${base}/${id}`, emailSettingPayload(values));
  assert.equal(blank.status, 200, 'Native blank-password edit must succeed');
  assert.equal(hash().hash, before.hash);
  assert.ok(!Object.hasOwn(blank.body.data, 'password'));
  proof.blankPreserved = true;
  const nullEdit = await request('PUT', `${base}/${id}`, { ...emailSettingPayload(values), password: null });
  assert.equal(nullEdit.status, 200);
  assert.equal(hash().hash, before.hash);
  proof.nullPreserved = true;
  const update = await request('PUT', `${base}/${id}`, emailSettingPayload({ ...values, password: replacement }));
  assert.equal(update.status, 200, 'Native credential replacement must succeed');
  assert.ok(hash().encrypted && hash().hash !== before.hash);
  assert.ok(!Object.hasOwn(update.body.data, 'password'));
  proof.replacementReencrypted = true;
  // Guarded negative test only: runtime permission is already proven false.
  const suppressed = await request('POST', `${base}/${id}/send-test`, { email: 'fixture-recipient@example.invalid' });
  assert.equal(suppressed.status, 400);
  assert.equal(suppressed.body.error_code, 'email_delivery_disabled');
  assert.match(suppressed.body.message, /No email was sent/);
  proof.defaultTestSuppressed = true;
  const selectedLogs = fs.readFileSync(logPath).subarray(offset).toString();
  assert.ok(!selectedLogs.includes(initial) && !selectedLogs.includes(replacement), 'Selected logs leaked synthetic credential');
  proof.selectedLogsCredentialFree = true;
} finally {
  if (id) {
    const cleanup = await request('DELETE', `${base}/delete`, { ids: [id] });
    assert.equal(cleanup.status, 200, 'Synthetic cleanup must succeed');
    proof.syntheticFixtureRemoved = true;
  }
  fs.writeFileSync(`${state}/smtp-api-proof.json`, JSON.stringify(proof, null, 2));
}
console.log(JSON.stringify(proof));
