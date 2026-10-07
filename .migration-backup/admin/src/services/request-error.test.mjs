import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { getRequestErrorMessage } from './request-error.mjs';

const firebaseSource = readFileSync(new URL('../firebase.js', import.meta.url), 'utf8');
const pushNotificationSource = readFileSync(
  new URL('../components/push-notification.jsx', import.meta.url),
  'utf8',
);
const requestSource = readFileSync(new URL('./request.js', import.meta.url), 'utf8');
const requestWithoutTimeoutSource = readFileSync(
  new URL('./requestWithoutTimeout.js', import.meta.url),
  'utf8',
);

test('request errors preserve validation, translated, and network messages without blank toasts', () => {
  assert.equal(
    getRequestErrorMessage(
      { response: { data: { params: { email: ['The email is invalid.'] } } } },
      (message) => `translated:${message}`,
    ),
    'translated:The email is invalid.',
  );
  assert.equal(
    getRequestErrorMessage(
      { response: { data: { message: 'Bookings unavailable.' } } },
      () => '',
    ),
    'Bookings unavailable.',
  );
  assert.equal(
    getRequestErrorMessage({ message: 'Network Error' }),
    'Network Error',
  );
  assert.equal(
    getRequestErrorMessage({ response: { status: 502 } }),
    'Request failed with status 502.',
  );
  assert.ok(getRequestErrorMessage({}).trim());
});

test('optional Firebase notifications do not toast or reject at disabled startup, while enabled failures remain visible', () => {
  assert.match(firebaseSource, /if \(!messaging\) return Promise\.resolve\(null\)/);
  assert.match(firebaseSource, /if \(!messaging\) return \(\) => \{\}/);
  assert.match(firebaseSource, /Firebase push notifications need VITE_FIREBASE_VAPID_KEY/);
  assert.match(pushNotificationSource, /if \(!pushMessagingEnabled\) return undefined/);
  assert.match(pushNotificationSource, /requestForToken\(\)\.catch\(reportNotificationError\)/);
  assert.match(pushNotificationSource, /set\(title, body\)\.catch\(reportNotificationError\)/);
  assert.doesNotMatch(pushNotificationSource, /onMessageListener\(\)\s*\.then/);
});

test('both admin request clients render a useful message for response-less failures', () => {
  for (const source of [requestSource, requestWithoutTimeoutSource]) {
    assert.match(source, /getRequestErrorMessage\(error, \(key\) => i18n\.t\(key\)\)/);
    assert.match(source, /toast\.error\(message/);
    assert.match(source, /return Promise\.reject\(error\)/);
  }
  assert.doesNotMatch(requestWithoutTimeoutSource, /error\.response\.status/);
});