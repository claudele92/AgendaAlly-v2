import fs from 'node:fs';
import assert from 'node:assert/strict';
import crypto from 'node:crypto';
const root = '.migration-backup/customer_app';
const out = 'docs/development/evidence/customer-mobile-phase1-auth-session';
const read = p => fs.readFileSync(p, 'utf8');
const before = JSON.parse(read(`${out}/before.json`));
const hash = p => crypto.createHash('sha256').update(fs.readFileSync(p)).digest('hex');
const baseline = process.env.MOBILE_PHASE1_BASELINE ?? '/tmp/agendaally-mobile-phase1/baseline-app';
const baselineLock = read(`${baseline}/pubspec.lock`);
assert.equal(read(`${root}/pubspec.lock`), baselineLock.replace(
  '  flutter_secure_storage:\n    dependency: transitive',
  '  flutter_secure_storage:\n    dependency: "direct main"'));
const auth = read(`${root}/lib/infrastructure/repository/auth_repository.dart`);
const noSettings = auth.replace(/Future updateSetting\(\) async \{[\s\S]*?\n  \}/, '');
assert(!noSettings.includes('queryParameters:'));
assert.equal((auth.match(/forLogout: true/g) ?? []).length, 1);
assert(auth.includes("'/api/v1/auth/verify/email'"));
assert(auth.includes("data: {'email': email.trim(), 'otp': verifyCode}"));
assert(auth.includes("'/api/v1/auth/forgot/email-password/verify'"));
assert(!auth.includes("'/api/v1/auth/verify/email/$verifyCode'"));
const storage = read(`${root}/lib/infrastructure/local_storage/local_storage.dart`);
assert(!/setString\(StorageKeys\.keyToken/.test(storage));
assert(storage.includes('legacyTokenPresent: local!.containsKey(StorageKeys.keyToken)'));
assert(storage.includes('StorageKeys.keyToken,'));
const logger = read(`${root}/lib/infrastructure/service/safe_http_diagnostics.dart`);
assert(logger.includes('kDebugMode &&'));
assert(logger.includes("bool.fromEnvironment('HTTP_DIAGNOSTICS')"));
assert(!logger.includes('requestOptions.data'));
assert(!logger.includes('response.data'));
assert(!logger.includes('.headers'));
assert(!logger.includes('.uri'));
const androidName = 'AgendaAllyCustomerSecureStorage.xml';
for (const file of ['customer_auth_backup_rules.xml', 'customer_auth_extraction_rules.xml']) {
  assert(read(`${root}/android/app/src/main/res/xml/${file}`).includes(androidName));
}

// This is a narrow source-equivalence check, NOT financial-flow acceptance.
// It discards logger calls, the now-unused convert import, whitespace and
// optional trailing commas. Request/mutation/calculation code must still match.
function withoutLogs(source) {
  let result = source;
  const re = /\b(?:debugPrint|log)\s*\(/g;
  for (let match; (match = re.exec(result));) {
    let i = re.lastIndex, depth = 1, quote = null;
    while (i < result.length && depth) {
      const char = result[i++];
      if (quote) {
        if (char === '\\') i++;
        else if (char === quote) quote = null;
      } else if (char === "'" || char === '"') quote = char;
      else if (char === '(') depth++;
      else if (char === ')') depth--;
    }
    assert.equal(depth, 0);
    while (/\s/.test(result[i] ?? '') && i < result.length) i++;
    if (result[i] === ';') i++;
    result = result.slice(0, match.index) + result.slice(i);
    re.lastIndex = match.index;
  }
  return result.replace(/import 'dart:convert';/g, '').replace(/\s/g, '').replace(/,(?=[)\]}])/g, '');
}
const financialSource = {};
for (const name of ['booking', 'payments', 'order', 'products']) {
  const relative = `lib/infrastructure/repository/${name}_repository.dart`;
  const original = read(`${baseline}/${relative}`);
  assert.equal(crypto.createHash('sha256').update(original).digest('hex'),
    before.sources[`${root}/${relative}`]);
  assert.equal(withoutLogs(read(`${root}/${relative}`)), withoutLogs(original),
    `STOP: non-logging change in ${name} repository`);
  financialSource[name] = 'unchanged after narrowly documented logging/format normalization';
}
const result = {
  dependencyGraph: 'all existing package versions/checksums preserved; one direct-dependency classification',
  authCredentials: 'body-only; no auth queryParameters outside non-secret language/currency settings',
  emailVerification: 'recipient-bound POST email + otp',
  resetVerification: 'recipient-bound POST email + otp',
  sharedPreferencesTokenAuthority: 'removed; legacy key included in cleanup',
  releaseDiagnostics: 'kDebugMode hard gate; metadata only; source validation, not native release execution',
  androidBackupExclusions: 'dedicated Customer encrypted preference namespace only',
  financialSource,
  lockedGraphSha256: hash(`${root}/pubspec.lock`),
};
fs.writeFileSync(`${out}/source-contracts.json`, JSON.stringify(result, null, 2) + '\n');
console.log(JSON.stringify(result, null, 2));
