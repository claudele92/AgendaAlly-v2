import fs from 'node:fs';
import crypto from 'node:crypto';
import assert from 'node:assert/strict';
const out = 'docs/development/evidence/customer-mobile-android-auth-qualification';
const root = '.migration-backup/customer_app';
const before = JSON.parse(fs.readFileSync(`${out}/before.json`, 'utf8'));
const read = p => fs.readFileSync(p, 'utf8');
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(p)).digest('hex');
for (const p of ['pubspec.yaml','pubspec.lock']) {
  assert.equal(hash(`${root}/${p}`), before.sources[`${root}/${p}`]);
  assert.equal(read(`/tmp/agendaally-mobile-android-qualification/app/${p}`), read(`${root}/${p}`));
}
const secure = read(`${root}/lib/infrastructure/session/secure_token_store.dart`);
assert(secure.includes("sharedPreferencesName: 'AgendaAllyCustomerSecureStorage'"));
assert(secure.includes('encryptedSharedPreferences: true'));
assert(secure.includes('resetOnError: false'));
const manifest = read(`${root}/android/app/src/main/AndroidManifest.xml`);
assert(manifest.includes('@xml/customer_auth_backup_rules'));
assert(manifest.includes('@xml/customer_auth_extraction_rules'));
const backup = read(`${root}/android/app/src/main/res/xml/customer_auth_backup_rules.xml`);
const transfer = read(`${root}/android/app/src/main/res/xml/customer_auth_extraction_rules.xml`);
assert(backup.includes('<exclude domain="sharedpref" path="AgendaAllyCustomerSecureStorage.xml"/>'));
for (const element of ['cloud-backup','device-transfer']) {
  const content = transfer.match(new RegExp(`<${element}>([\\s\\S]*?)</${element}>`))?.[1];
  assert(content?.includes('<exclude domain="sharedpref" path="AgendaAllyCustomerSecureStorage.xml"/>'));
}
const auth = read(`${root}/lib/infrastructure/repository/auth_repository.dart`);
assert.equal((auth.match(/forLogout: true/g) ?? []).length, 1);
assert(!auth.replace(/Future updateSetting\(\) async \{[\s\S]*?\n  \}/, '').includes('queryParameters:'));
const diagnostic = read(`${root}/lib/infrastructure/service/safe_http_diagnostics.dart`);
assert(diagnostic.includes('kDebugMode &&'));
for (const forbidden of ['requestOptions.data','response.data','.headers','.uri']) assert(!diagnostic.includes(forbidden));
const financial = {};
for (const module of ['booking','payments','order','products']) {
  const p = `${root}/lib/infrastructure/repository/${module}_repository.dart`;
  assert.equal(hash(p), before.sources[p]);
  financial[module] = 'byte-identical to fresh campaign baseline';
}
const result = {
  dependencyGraph: 'EXACTLY UNCHANGED — pubspec.yaml and pubspec.lock',
  secureAdapter: 'SOURCE-CONFIGURED — Android encrypted preferences, dedicated namespace, no reset-on-error',
  backupExclusion: 'ANDROID BACKUP EXCLUSION CONFIGURATION VERIFIED — SOURCE-CONFIGURED',
  deviceTransferExclusion: 'CONFIGURATION VERIFIED — SOURCE-CONFIGURED',
  cloudBackupBehavior: 'NOT EXECUTED',
  nativePersistenceAndErasure: 'These source checks do not establish Android runtime behavior',
  authConfidentiality: 'body-only sensitive auth fields; release diagnostic gate retained',
  financial,
};
fs.writeFileSync(`${out}/source-checks.json`, JSON.stringify(result, null, 2) + '\n');
console.log(JSON.stringify(result, null, 2));
