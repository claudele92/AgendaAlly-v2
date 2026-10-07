import fs from 'node:fs';
import crypto from 'node:crypto';
import assert from 'node:assert/strict';
import {execFileSync} from 'node:child_process';
const out = 'docs/development/evidence/customer-mobile-android-auth-qualification';
const temp = '/tmp/agendaally-mobile-android-qualification';
const report = 'docs/development/agendaally-customer-mobile-android-auth-qualification.md';
const exportsDir = 'exports/customer-mobile-android-auth-qualification';
const tools = '.local/customer-mobile-android-qualification-tools';
const read = p => fs.readFileSync(p, 'utf8');
const exit = name => Number(read(`${temp}/${name}.exit`).trim());
const copies = ['toolchain','isolated-copy','dependency-resolution','phase1-regression','static-analysis',
  'sdk-install','sdk-install-retry','sdk-minimal-install','ndk-install','ndk-fresh-install',
  'android-debug-initial','android-debug-build','android-debug-retry-command'];
for (const name of copies) {
  if (fs.existsSync(`${temp}/${name}.log`)) fs.copyFileSync(`${temp}/${name}.log`, `${out}/${name}.log`);
  if (fs.existsSync(`${temp}/${name}.exit`)) fs.copyFileSync(`${temp}/${name}.exit`, `${out}/${name}.exit`);
}
for (const name of ['dependency-resolution','phase1-regression','static-analysis','sdk-minimal-install','ndk-fresh-install']) assert.equal(exit(name), 0);
assert(read(`${out}/phase1-regression.log`).includes('+36: All tests passed!'));
assert(read(`${out}/static-analysis.log`).includes('No issues found!'));
const preserved = JSON.parse(read(`${out}/preservation-result.json`));
assert(preserved.databaseIdentical);
assert.equal(preserved.tablesCompared, 214);
assert.deepEqual(preserved.changedFiles, []);
const md = read(report);
assert.equal((md.match(/^## \d+\./gm) ?? []).length, 31);
const buildStatus = md.match(/ANDROID DEBUG BUILD:\n([^\n]+)/)?.[1];
assert(['PASS','FAILED-SOURCE','BLOCKED-ENVIRONMENT','BLOCKED-CONFIGURATION'].includes(buildStatus));
const packages = {};
for (const name of ['cmdline-tools/latest','platform-tools','platforms/android-36','build-tools/35.0.0','ndk/29.0.14206865']) {
  const props = read(`${tools}/android-sdk/${name}/source.properties`);
  packages[name] = {revision: props.match(/^Pkg.Revision\s*=\s*(.+)$/m)?.[1].trim()};
  const api = props.match(/^AndroidVersion.ApiLevel\s*=\s*(.+)$/m)?.[1];
  if (api) packages[name].apiLevel = Number(api);
}
const manifest = {
  toolchain: {flutter:'3.38.5', dart:'3.10.4', java: read(`${tools}/jdk/release`).match(/^JAVA_VERSION="([^"]+)"/m)?.[1],
    androidPackages:packages, gradle:'8.13', androidGradlePlugin:'8.9.1', kotlin:'2.2.0'},
  checks: {
    dependencyResolution:{exitCode:exit('dependency-resolution'),status:'PASS',packageChanges:0},
    phase1Regression:{exitCode:exit('phase1-regression'),status:'PASS',passed:36,failed:0},
    staticAnalysis:{exitCode:exit('static-analysis'),status:'PASS',issues:0},
    androidDebugBuild:{wrapperReportedExitCode:exit('android-debug-build'),
      overallCommandExitCode:exit('android-debug-retry-command'),gradleTaskExitCode:143,
      apkProduced:fs.existsSync(`${temp}/app/build/app/outputs/flutter-apk/app-debug.apk`),
      status:buildStatus,target:'android-x64',buildMode:'debug',stallRootCause:'UNKNOWN'},
    preservation:{baselineFiles:5825,applicationChanges:0,normalTablesIdentical:214,schemaIdentical:true},
  },
  evidenceLevels:{secureConfiguration:'SOURCE-CONFIGURED',phase1Behavior:'TEST-VERIFIED',
    androidTokenPersistence:'NOT VERIFIED',androidTokenErasure:'NOT VERIFIED',androidJourneys:'NOT EXECUTED'},
  acceptance:{androidQualification:'BLOCKED',p001:'CLOSED — application HTTP contract/logging',
    p002:'PARTIAL',androidFirstRemainingP0:1,remainingP1:9,iosNative:'NOT EXECUTED',phase2Recommended:false},
  activity:{realCustomerCredentials:false,customerBackendRequests:false,realMailSms:false,
    providerActivation:false,financialActions:false,normalDatabaseWrites:false},
};
fs.writeFileSync(`${out}/validation-results.json`, JSON.stringify(manifest,null,2)+'\n');
const checksums = fs.readdirSync(out).filter(n => n !== 'sha256-manifest.json').sort().map(file =>
  ({file,sha256:crypto.createHash('sha256').update(fs.readFileSync(`${out}/${file}`)).digest('hex')}));
fs.writeFileSync(`${out}/sha256-manifest.json`,JSON.stringify(checksums,null,2)+'\n');
fs.mkdirSync(exportsDir,{recursive:true});
const zip = `${exportsDir}/agendaally-customer-mobile-android-auth-evidence.zip`;
fs.rmSync(zip,{force:true});
execFileSync('zip',['-q','-r',zip,out,report]);
const escape = s => s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
const inline = s => escape(s).replace(/`([^`]+)`/g,'<code>$1</code>').replace(/\*\*([^*]+)\*\*/g,'<strong>$1</strong>');
let code=false, table=false, rendered='';
for (const line of md.split('\n')) {
  if (line.startsWith('```')) {
    if (table) {rendered+='</tbody></table>';table=false;}
    rendered+=code?'</code></pre>':'<pre><code>';code=!code;continue;
  }
  if (code) {rendered+=escape(line)+'\n';continue;}
  if (line.startsWith('|')) {
    if (/^\|[-| :]+\|$/.test(line)) continue;
    if (!table) {rendered+='<table><tbody>';table=true;}
    rendered+='<tr>'+line.split('|').slice(1,-1).map(v=>`<td>${inline(v.trim())}</td>`).join('')+'</tr>';continue;
  }
  if (table) {rendered+='</tbody></table>';table=false;}
  if (line.startsWith('## ')) rendered+=`<h2>${inline(line.slice(3))}</h2>`;
  else if (line.startsWith('# ')) rendered+=`<h1>${inline(line.slice(2))}</h1>`;
  else if (line.startsWith('- ')) rendered+=`<p class="bullet">• ${inline(line.slice(2))}</p>`;
  else if (line.trim()) rendered+=`<p>${inline(line)}</p>`;
}
const data=fs.readFileSync(zip).toString('base64');
const html=`<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>AgendaAlly Android Authentication Qualification</title>
<style>body{margin:0;background:#f4f6f8;color:#17232d;font:16px/1.6 system-ui,sans-serif}main{max-width:1000px;margin:30px auto;padding:36px;background:white;border:1px solid #dce3e8;border-radius:10px}h1{font-size:30px;line-height:1.3}h2{margin-top:40px;padding-top:16px;border-top:1px solid #dce3e8;font-size:22px}code{font-size:13px;overflow-wrap:anywhere}pre{white-space:pre-wrap;background:#f3f5f7;padding:18px;border-radius:6px;overflow-wrap:anywhere}table{border-collapse:collapse;width:100%;font-size:14px}td{padding:10px;border:1px solid #dce3e8;vertical-align:top}tr:first-child{background:#edf2f6;font-weight:600}.bullet{padding-left:18px;margin:6px 0}.download{display:inline-block;background:#173e59;color:white;padding:12px 18px;text-decoration:none;border-radius:6px}.notice{background:#fff5da;padding:16px;border-left:4px solid #9b7113}@media(max-width:700px){main{margin:0;padding:20px;border:0}h1{font-size:25px}}@media print{.download{display:none}body{background:white}main{border:0;margin:0;padding:0}}</style>
<main><p class="notice"><strong>BLOCKED — stopped for owner review.</strong> 36 regression tests pass; analysis is clean. P0-02 remains PARTIAL: Android runtime persistence/erasure is not verified.</p>
<a class="download" href="data:application/zip;base64,${data}" download="agendaally-customer-mobile-android-auth-evidence.zip">Download focused evidence bundle</a>${rendered}</main></html>`;
const file=`${exportsDir}/agendaally-customer-mobile-android-auth-qualification.html`;
fs.writeFileSync(file,html);
console.log(JSON.stringify({manifest,reportExport:file,evidenceArchive:zip,exportedBytes:Buffer.byteLength(html)},null,2));
