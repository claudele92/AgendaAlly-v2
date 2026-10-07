import assert from 'node:assert/strict';
import test from 'node:test';
import {
  bookingClientFromCreateResponse,
  bookingClientsFromResponse,
} from './booking-client-response.mjs';
import { clientReference } from './client-reference.mjs';

test('client search unwraps the shared request interceptor response once', () => {
  const clients = [{ id: 7, kind: 'local', name: 'Walk-in' }];
  assert.deepEqual(bookingClientsFromResponse({ data: clients }), clients);
});

test('client creation reads the client and reuse flag at the response root', () => {
  const client = { id: 7, kind: 'local', name: 'Walk-in' };
  assert.deepEqual(
    bookingClientFromCreateResponse({ data: client, reused_existing: true }),
    { client, reusedExisting: true },
  );
});

test('the calendar client reference serializes a local client rather than an account id', () => {
  assert.deepEqual(
    clientReference({ user_id: null, local_client_id: 23 }),
    { local_client_id: 23 },
  );
});