import assert from 'node:assert/strict';
import test from 'node:test';
import { safeEmailSettingForm, emailSettingPayload, passwordStatusText } from './credential-form.mjs';

test('stored password is never hydrated or retained in menu state', () => {
  const result = safeEmailSettingForm({ host: 'smtp.example.invalid', password: 'synthetic-fixture-secret', password_configured: true });
  assert.equal(result.password, '');
  assert.equal(result.password_configured, true);
  assert.equal(JSON.stringify(result).includes('synthetic-fixture-secret'), false);
});
test('blank, whitespace and null edit credentials are omitted', () => {
  for (const password of ['', '  ', null, undefined]) {
    assert.equal(Object.hasOwn(emailSettingPayload({ password, host: 'smtp.example.invalid' }), 'password'), false);
  }
});
test('Active switch values follow the native zero/one validation contract', () => {
  assert.equal(emailSettingPayload({ active: false }).active, 0);
  assert.equal(emailSettingPayload({ active: true }).active, 1);
});
test('explicit replacement remains in only the submission payload', () => {
  const replacement = 'synthetic-replacement-only';
  assert.equal(emailSettingPayload({ password: replacement }).password, replacement);
  assert.equal(safeEmailSettingForm({ password: replacement }).password, '');
});
test('safe configured and legacy states contain no credential value', () => {
  assert.match(passwordStatusText('configured'), /Leave blank/);
  assert.match(passwordStatusText('replacement_required'), /blocked/);
  assert.match(passwordStatusText('unavailable'), /cannot be decrypted/);
});
