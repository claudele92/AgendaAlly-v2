import fs from 'node:fs';
import crypto from 'node:crypto';
import assert from 'node:assert/strict';
import {execFileSync} from 'node:child_process';

const out = 'docs/development/evidence/customer-mobile-phase1-auth-session';
const temp = '/tmp/agendaally-mobile-phase1';
const report = 'docs/development/agendaally-customer-mobile-phase1-auth-session.md';
const exportsDir = 'exports/customer-mobile-phase1';
fs.mkdirSync(exportsDir, {recursive: true});
const copies = {
  'supported-toolchain.log': 'toolchain.log',
  'baseline-dependency-resolution.log': 'baseline-pub-get.log',
  'final-dependency-resolution.log': 'final-pub-get.log',
  'baseline-analysis.log': 'analyze-baseline.log',
  'final-analysis.log': 'delivery-analyze.log',
  'focused-tests.log': 'delivery-tests.log',
  'android-debug-build.log': 'android-debug.log',
  'source-contracts.log': 'source-contracts.log',
  'preservation.log': 'preservation.log',
};
for (const [dest, source] of Object.entries(copies)) {
  fs.copyFileSync(`${temp}/${source}`, `${out}/${dest}`);
}
const exit = name => Number(fs.readFileSync(`${temp}/${name}.exit`, 'utf8').trim());
assert.equal(exit('delivery-tests'), 0);
assert.equal(exit('delivery-analyze'), 0);
assert.equal(exit('final-pub-get'), 0);
assert.equal(exit('analyze-baseline'), 0);
assert(fs.readFileSync(`${out}/focused-tests.log`, 'utf8').includes('+36: All tests passed!'));
assert(fs.readFileSync(`${out}/final-analysis.log`, 'utf8').includes('No issues found!'));
assert(fs.readFileSync(`${out}/android-debug-build.log`, 'utf8').includes('No Android SDK found'));
const preserved = JSON.parse(fs.readFileSync(`${out}/preservation-result.json`, 'utf8'));
assert.equal(preserved.databaseIdentical, true);
assert.equal(preserved.tablesCompared, 214);
assert.deepEqual(preserved.unexpectedNonMobileChanges, []);
const manifest = {
  toolchain: {flutter: '3.38.5', dart: '3.10.4', platform: 'Linux/Nix, isolated SDK'},
  checks: {
    lockedDependencyResolution: {exitCode: exit('final-pub-get'), status: 'PASS'},
    baselineAnalysis: {exitCode: exit('analyze-baseline'), status: 'PASS — no issues'},
    finalAnalysis: {exitCode: exit('delivery-analyze'), status: 'PASS — no issues'},
    focusedTests: {exitCode: exit('delivery-tests'), passed: 36, failed: 0},
    androidDebug: {exitCode: exit('android-debug'), status: 'BLOCKED-ENVIRONMENT'},
    ios: {status: 'NOT EXECUTED — no macOS/Xcode'},
    preservation: {applicationBaselineFiles: 5816, authorizedMobileChanges: preserved.changedFiles.length,
      unexpectedNonMobileChanges: 0, identicalDatabaseTables: 214, schemaIdentical: true},
  },
  acceptance: {phase1: 'PARTIAL', p001: 'CLOSED — application HTTP contract/logging',
    p002: 'PARTIAL — native secure persistence/erasure unqualified',
    remainingP0: 1, remainingP1: 9, phase2CanBegin: 'NO', activeMission: 'NONE'},
  nativeAcceptance: 'No Android/iOS compilation, encryption, real provider/device or release-runtime acceptance claimed.',
  syntheticOnly: 'No real emails/SMS, provider activation, backend requests or financial actions performed.',
};
fs.writeFileSync(`${out}/validation-results.json`, JSON.stringify(manifest, null, 2) + '\n');
const checksums = fs.readdirSync(out).filter(name => !['sha256-manifest.json'].includes(name))
  .sort().map(name => ({file: name, sha256: crypto.createHash('sha256')
    .update(fs.readFileSync(`${out}/${name}`)).digest('hex')}));
fs.writeFileSync(`${out}/sha256-manifest.json`, JSON.stringify(checksums, null, 2) + '\n');
const zip = `${exportsDir}/agendaally-customer-mobile-phase1-evidence.zip`;
fs.rmSync(zip, {force: true});
execFileSync('zip', ['-q', '-r', zip, out, report]);
const escape = s => s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
const inline = s => escape(s).replace(/`([^`]+)`/g, '<code>$1</code>').replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
const md = fs.readFileSync(report, 'utf8');
assert.equal((md.match(/^## \d+\./gm) ?? []).length, 29);
let code = false, table = false, rendered = '';
for (const line of md.split('\n')) {
  if (line.startsWith('```')) {
    if (table) { rendered += '</tbody></table>'; table = false; }
    rendered += code ? '</code></pre>' : '<pre><code>'; code = !code; continue;
  }
  if (code) { rendered += escape(line) + '\n'; continue; }
  if (line.startsWith('|')) {
    if (/^\|[-| :]+\|$/.test(line)) continue;
    const cells = line.split('|').slice(1, -1);
    if (!table) { rendered += '<table><tbody>'; table = true; }
    rendered += '<tr>' + cells.map(v => `<td>${inline(v.trim())}</td>`).join('') + '</tr>'; continue;
  }
  if (table) { rendered += '</tbody></table>'; table = false; }
  if (line.startsWith('## ')) rendered += `<h2>${inline(line.slice(3))}</h2>`;
  else if (line.startsWith('# ')) rendered += `<h1>${inline(line.slice(2))}</h1>`;
  else if (line.startsWith('- ')) rendered += `<p class="bullet">• ${inline(line.slice(2))}</p>`;
  else if (line.trim()) rendered += `<p>${inline(line)}</p>`;
}
const data = fs.readFileSync(zip).toString('base64');
const html = `<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AgendaAlly Customer Mobile Phase 1</title>
<style>body{margin:0;background:#f4f6f8;color:#17232d;font:16px/1.6 system-ui,sans-serif}main{max-width:1000px;margin:30px auto;padding:36px;background:white;border:1px solid #dce3e8;border-radius:10px}h1{font-size:30px;line-height:1.3}h2{margin-top:40px;padding-top:16px;border-top:1px solid #dce3e8;font-size:22px}code{font-size:13px;overflow-wrap:anywhere}pre{white-space:pre-wrap;background:#f3f5f7;padding:18px;border-radius:6px;overflow-wrap:anywhere}table{border-collapse:collapse;width:100%;font-size:14px}td{padding:10px;border:1px solid #dce3e8;vertical-align:top}tr:first-child{background:#edf2f6;font-weight:600}.bullet{padding-left:18px;margin:6px 0}.download{display:inline-block;background:#173e59;color:white;padding:12px 18px;text-decoration:none;border-radius:6px}.notice{background:#fff5da;padding:16px;border-left:4px solid #9b7113}@media(max-width:700px){main{margin:0;padding:20px;border:0}h1{font-size:25px}}@media print{.download{display:none}body{background:white}main{border:0;margin:0;padding:0}}</style>
<main><p class="notice"><strong>PARTIAL — stopped for owner review.</strong> 36 focused tests pass; analysis is clean. Native secure-storage acceptance is still pending.</p>
<a class="download" href="data:application/zip;base64,${data}" download="agendaally-customer-mobile-phase1-evidence.zip">Download focused evidence bundle</a>
${rendered}</main></html>`;
fs.writeFileSync(`${exportsDir}/agendaally-customer-mobile-phase1-auth-session.html`, html);
console.log(JSON.stringify({manifest, reportExport: `${exportsDir}/agendaally-customer-mobile-phase1-auth-session.html`,
  evidenceArchive: zip, exportedBytes: Buffer.byteLength(html)}, null, 2));
