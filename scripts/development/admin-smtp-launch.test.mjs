import test from 'node:test';
import assert from 'node:assert/strict';
import { adminSmtpLaunchOptions, DEFAULT_DISABLED_FUNCTIONS } from './admin-smtp-launch.mjs';

const safe = {
  enabled: true, backend: '/owned/backend', port: '8000', published: false,
  environment: {
    APP_ENV: 'local', DEVELOPMENT_MODE: 'true', AGENDAALLY_DEVELOPMENT_DATABASE: 'true',
    APP_DEBUG: 'false', DB_CONNECTION: 'sqlite', EMAIL_MODE: 'log', MAIL_MAILER: 'log',
    PAYMENT_MODE: 'disabled', SMS_MODE: 'log', FIREBASE_ENABLED: 'false', MAPS_ENABLED: 'false',
  },
};
test('default launcher keeps every existing network/command restriction', () => {
  const result = adminSmtpLaunchOptions({ ...safe, enabled: false });
  assert.deepEqual(result.directives, []);
  assert.equal(result.disabled, DEFAULT_DISABLED_FUNCTIONS.join(','));
});
test('opt-in releases only the verified SMTP socket and pins the authority', () => {
  const result = adminSmtpLaunchOptions(safe);
  assert.deepEqual(result.directives, ['-d', 'agendaally.normal_admin_smtp_test_authority=/owned/backend']);
  assert.deepEqual(result.disabled.split(','), DEFAULT_DISABLED_FUNCTIONS.filter(f => f !== 'stream_socket_client'));
});
test('unsafe integrations, environment, debug, database, port and published runtime fail closed', () => {
  for (const [key, value] of Object.entries({
    APP_ENV: 'production', DEVELOPMENT_MODE: 'false', AGENDAALLY_DEVELOPMENT_DATABASE: 'false',
    APP_DEBUG: 'true', DB_CONNECTION: 'mysql', EMAIL_MODE: 'smtp', MAIL_MAILER: 'smtp',
    PAYMENT_MODE: 'test', SMS_MODE: 'provider', FIREBASE_ENABLED: 'true', MAPS_ENABLED: 'true',
  })) assert.throws(() => adminSmtpLaunchOptions({
    ...safe, environment: { ...safe.environment, [key]: value },
  }));
  assert.throws(() => adminSmtpLaunchOptions({ ...safe, port: '8080' }));
  assert.throws(() => adminSmtpLaunchOptions({ ...safe, published: true }));
});
