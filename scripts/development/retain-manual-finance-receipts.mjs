import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';

// Keep sanitized, synthetic-only proof with the source; runtime auth stays private.
const root = process.cwd();
const directory = path.resolve(process.argv[2] || '');
assert.match(directory, new RegExp(`^${root.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}/\\.local/manual-finance/http-identity-[a-f0-9]{16}$`));
assert(!fs.lstatSync(directory).isSymbolicLink());
const target = path.join(root, 'docs/development/evidence/private-receipt-acceptance');
assert(!fs.existsSync(target), 'Retained acceptance proof must never be overwritten');
const read = (name) => JSON.parse(fs.readFileSync(path.join(directory, name), 'utf8'));
const auth = Object.values(read('auth.json'));
const clean = (value) => {
  let text = JSON.stringify(value, null, 2);
  for (const token of auth) text = text.replaceAll(token, '[REDACTED SYNTHETIC AUTH]');
  text = text.replace(/signature=[^&"\\\s]+/gi, 'signature=[REDACTED]');
  // A bearer transport value must not become durable acceptance evidence.
  assert(!/Bearer\s+(?!\[REDACTED)[A-Za-z0-9|._-]{8,}/i.test(text));
  return text + '\n';
};
const native = JSON.parse(execFileSync('php', [
  'scripts/development/manual-finance-receipt-fixture.php', 'snapshot', directory,
], { encoding: 'utf8', maxBuffer: 16 * 1024 * 1024 }));
const preservedTables = Object.fromEntries(Object.entries(native.effects)
  .filter(([table]) => table.startsWith('manual_financial_') || [
    'commerce_payment_allocations', 'payment_financial_operations', 'platform_fee_ledger_entries',
  ].includes(table)));
fs.mkdirSync(target, { recursive: true });
const write = (name, value) => fs.writeFileSync(path.join(target, name), clean(value), { flag: 'wx' });
write('native-final.json', {
  attachments: native.attachments, evidence: native.evidence, access: native.access,
  financial_tables: preservedTables,
});
write('fixture-manifest.json', read('manifest.json'));
write('synthetic-manifest.json', read('synthetic/manifest.json'));
write('normal-before.json', read('normal-before.json'));
write('normal-after.json', read('normal-after.json'));
for (const name of fs.readdirSync(path.join(directory, 'browser')).filter((name) => name.endsWith('.json'))) {
  const value = read(`browser/${name}`);
  // Do not publish incidental bootstrap/config/auth tables from full snapshots.
  if (value.effects) {
    value.effects = Object.fromEntries(Object.entries(value.effects)
      .filter(([table]) => table.startsWith('manual_financial_') || [
        'commerce_payment_allocations', 'payment_financial_operations', 'platform_fee_ledger_entries',
      ].includes(table)));
  }
  write(`browser-${name}`, value);
}
console.log(JSON.stringify({
  retained: path.relative(root, target), files: fs.readdirSync(target),
  excludes: ['auth.json', 'native token values', 'sessions', 'signed signatures', 'normal raw rows', 'private document bytes'],
}));
