import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';

const root = process.cwd();
const directory = path.resolve(process.argv[2] || '');
assert.match(directory, new RegExp(`^${root.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}/\\.local/manual-finance/http-identity-[a-f0-9]{16}$`));
assert(!fs.lstatSync(directory).isSymbolicLink());
const snapshot = JSON.parse(execFileSync('php', [
  'scripts/development/manual-finance-receipt-fixture.php', 'snapshot', directory,
], { encoding: 'utf8', maxBuffer: 16 * 1024 * 1024 }));
assert.equal(snapshot.attachments.length, 3);
const checks = [];
for (const file of snapshot.attachments) {
  const privateBytes = fs.readFileSync(path.join(directory, 'private', file.path));
  const surfaces = [
    ['native-storage', `http://127.0.0.1:3138/storage/${file.path}`, 404],
    ['vite-storage-fallback', `http://127.0.0.1:3003/storage/${file.path}`, null],
    ['vite-sibling-filesystem', `http://127.0.0.1:3003/@fs${directory}/private/${file.path}`, 403],
  ];
  for (const [surface, url, expectedStatus] of surfaces) {
    // Deliberately no auth/cookie/signed capability.
    const response = await fetch(url);
    const body = Buffer.from(await response.arrayBuffer());
    const exposed = body.equals(privateBytes) || body.includes(privateBytes);
    assert.equal(exposed, false, `${surface} must not serve private receipt bytes`);
    if (expectedStatus !== null) assert.equal(response.status, expectedStatus);
    checks.push({
      surface, attachment_id: file.id, mime: file.mime, status: response.status,
      response_content_type: response.headers.get('content-type'),
      response_sha256: createHash('sha256').update(body).digest('hex'),
      receipt_bytes_exposed: exposed,
    });
  }
}
const receipt = {
  provenance: 'Main-agent unauthenticated direct-path HTTP probes, not a substitute for role browser checks',
  note: 'A Vite SPA fallback may return HTTP 200 HTML for an absent storage path; status alone is not proof of receipt exposure.',
  checks,
};
fs.writeFileSync(path.join(directory, 'browser/public-serving-boundary.json'),
  JSON.stringify(receipt, null, 2) + '\n', { flag: 'wx', mode: 0o600 });
console.log(JSON.stringify({ result: 'passed', unauthenticated_direct_path_checks: checks.length,
  receipt_bytes_exposed: false, statuses: checks.map(({ surface, status }) => ({ surface, status })) }));
