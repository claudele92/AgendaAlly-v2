import assert from 'node:assert/strict';
import test from 'node:test';
import {
  clientSaveIntentStorageKey,
  clearClientSaveIntent,
  clientSavePayload,
  createClientSaveIntentId,
  isDefinitiveClientSaveRejection,
  readClientSaveIntent,
  writeClientSaveIntent,
} from './client-save-intent.mjs';

const memoryStorage = () => {
  const values = new Map();
  return {
    getItem: (key) => values.get(key) ?? null,
    setItem: (key, value) => values.set(key, value),
    removeItem: (key) => values.delete(key),
  };
};

test('pending persistence stores only the opaque UUID and is scoped to actor and Shop', () => {
  const storage = memoryStorage();
  const scope = clientSaveIntentStorageKey(81, 14);
  const intentId = '7e2b64f1-bf3c-4a7b-a2f3-2d278a21802d';
  assert.equal(writeClientSaveIntent(storage, scope, intentId), true);
  assert.equal(readClientSaveIntent(storage, scope), intentId);
  assert.equal(storage.getItem(scope), intentId);
  assert.notEqual(scope, clientSaveIntentStorageKey(81, 15));
  assert.notEqual(scope, clientSaveIntentStorageKey(82, 14));
});

test('each explicit new-person Save receives a distinct secure intent identity', () => {
  const ids = [
    '7e2b64f1-bf3c-4a7b-a2f3-2d278a21802d',
    '1f87642d-df41-4d81-aad0-0d98b9cab921',
  ];
  const cryptoSource = { randomUUID: () => ids.shift() };
  assert.notEqual(
    createClientSaveIntentId(cryptoSource),
    createClientSaveIntentId(cryptoSource),
  );
});

test('clear only removes the matching unresolved intent', () => {
  const storage = memoryStorage();
  const scope = clientSaveIntentStorageKey(81, 14);
  writeClientSaveIntent(storage, scope, '7e2b64f1-bf3c-4a7b-a2f3-2d278a21802d');
  clearClientSaveIntent(storage, scope, 'different-intent');
  assert.ok(readClientSaveIntent(storage, scope));
  clearClientSaveIntent(storage, scope);
  assert.equal(readClientSaveIntent(storage, scope), null);
});

test('only native validation and conflict responses are definitive', () => {
  assert.equal(isDefinitiveClientSaveRejection({ response: { status: 422 } }), true);
  assert.equal(isDefinitiveClientSaveRejection({ response: { status: 409 } }), true);
  assert.equal(isDefinitiveClientSaveRejection(new Error('network')), false);
  assert.equal(isDefinitiveClientSaveRejection({ response: { status: 500 } }), false);
});

test('payload is normalized once for replay and never inferred from a name', () => {
  assert.deepEqual(
    clientSavePayload({ name: '  Amina  ', phone: ' ', email: ' a@b.test ' }, 5),
    { name: 'Amina', phone: null, email: 'a@b.test', shop_location_id: 5 },
  );
});