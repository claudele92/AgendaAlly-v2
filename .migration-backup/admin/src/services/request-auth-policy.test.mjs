import test from 'node:test';
import assert from 'node:assert/strict';
import { shouldClearAuthForResponse } from './request-auth-policy.mjs';

test('object-level forbidden preserves auth, but unauthorized still clears it', () => {
  assert.equal(shouldClearAuthForResponse(403, { preserveAuthOnForbidden: true }), false);
  assert.equal(shouldClearAuthForResponse(401, { preserveAuthOnForbidden: true }), true);
});

test('ordinary forbidden retains existing application auth handling', () => {
  assert.equal(shouldClearAuthForResponse(403), true);
  assert.equal(shouldClearAuthForResponse(403, { preserveAuthOnForbidden: false }), true);
  for (const status of [undefined, 404, 409, 422, 500]) {
    assert.equal(shouldClearAuthForResponse(status), false);
  }
});
