import test from 'node:test';
import assert from 'node:assert/strict';
import { formatManualFinanceAmount, manualFinanceStateLabels, vendorPayoutStateLabels } from './manual-finance-copy.mjs';
import {
  clearManualFinanceIntent,
  readManualFinanceIntent,
  saveManualFinanceIntent,
} from './manual-finance-intent.mjs';

const values = new Map();
globalThis.sessionStorage = {
  getItem: (key) => values.get(key) ?? null,
  setItem: (key, value) => values.set(key, value),
  removeItem: (key) => values.delete(key),
};
test('approved requests are never labeled paid or refunded', () => {
  assert.equal(manualFinanceStateLabels.APPROVED, 'Approved — not yet paid/refunded');
  assert.equal(vendorPayoutStateLabels.APPROVED, 'Payout approved — not yet paid');
  assert.match(manualFinanceStateLabels.REQUIRES_REVIEW, /do not pay again/i);
  assert.equal(vendorPayoutStateLabels.REQUIRES_REVIEW, 'Payout under review');
  assert.equal(formatManualFinanceAmount('10509', 'USD', 2), '105.09 USD');
});

test('manual finance command identity and exact submitted payload survive retries', () => {
  const workflow = 'test:finance-action';
  const original = saveManualFinanceIntent(workflow, { version: 4, reason: 'verified' });
  const retry = saveManualFinanceIntent(workflow, { version: 5, reason: 'changed after conflict' });
  assert.equal(retry.command_key, original.command_key);
  assert.deepEqual(retry.payload, original.payload);
  assert.deepEqual(readManualFinanceIntent(workflow), original);
  clearManualFinanceIntent(workflow);
  assert.equal(readManualFinanceIntent(workflow), null);
});
