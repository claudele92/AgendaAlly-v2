import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

// Checks retained disposable effects, not an alternative to the browser trace.
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const directory = path.join(root, '.local/manual-finance/ui-acceptance');
const read = (name) => JSON.parse(fs.readFileSync(path.join(directory, `${name}.json`), 'utf8'));
const workflow = (snapshot, allocation, kind) => {
  const rows = snapshot.manual_financial_workflows.filter(
    (row) => Number(row.allocation_id) === allocation && row.kind === kind,
  );
  assert.equal(rows.length, 1, `Exactly one ${kind} workflow on allocation ${allocation}`);
  return rows[0];
};
const operation = (snapshot, row) => snapshot.payment_financial_operations.find(
  (item) => item.id === row.operation_id,
);
const sameRetainedData = (left, right) => {
  const data = ({ phase, captured_at, ...rest }) => rest;
  assert.deepEqual(data(left), data(right), 'Exact replay must not change retained fixture data');
};
const noNewLedger = (before, after) =>
  assert.deepEqual(before.platform_fee_ledger_entries, after.platform_fee_ledger_entries);

const baseline = read('baseline');
assert.equal(baseline.settings.find((row) => row.key === 'booking_canceled_commission').value, '50');
const approved = read('finance-approved');
const claimed = read('finance-claimed');
const completed = read('finance-completion-lost');
const replayed = read('finance-completion-replayed');
const review = read('finance-payout-review');
for (const snapshot of [approved, claimed]) {
  const row = workflow(snapshot, 1, 'refund');
  assert.equal(row.state, 'APPROVED');
  assert.equal(row.completed_at, null);
  assert.equal(snapshot.manual_financial_evidence.length, 0);
  noNewLedger(baseline, snapshot);
}
assert.equal(operation(approved, workflow(approved, 1, 'refund')).state, 'RESERVED');
assert.equal(operation(claimed, workflow(claimed, 1, 'refund')).state, 'UNKNOWN');
assert.equal(workflow(claimed, 1, 'refund').claim_actor_id, 3);
const refund = workflow(completed, 1, 'refund');
assert.equal(refund.state, 'COMPLETED');
assert.equal(String(refund.amount_units), '5000');
assert.equal(JSON.parse(refund.policy_snapshot).fee_basis_points, 5000);
assert.equal(operation(completed, refund).state, 'SUCCESS');
assert.equal(completed.manual_financial_evidence.length, 1);
const principal = completed.platform_fee_ledger_entries.filter((row) => row.effect_kind === 'refund_principal');
assert.equal(principal.length, 1);
assert.equal(String(principal[0].exact_amount), '5000');
sameRetainedData(completed, replayed);
assert.equal(workflow(review, 2, 'payout').state, 'REQUIRES_REVIEW');
assert.equal(operation(review, workflow(review, 2, 'payout')).state, 'UNKNOWN');
noNewLedger(replayed, review);

const customerLost = read('customer-created-lost');
const customerReplay = read('customer-created-replayed');
const customerCancel = read('customer-cancelled');
assert.equal(workflow(customerLost, 3, 'refund').state, 'REQUESTED');
assert.equal(operation(customerLost, workflow(customerLost, 3, 'refund')).state, 'RESERVED');
assert.equal(customerLost.manual_financial_events.length, review.manual_financial_events.length + 1);
noNewLedger(review, customerLost);
sameRetainedData(customerLost, customerReplay);
const cancelled = workflow(customerCancel, 3, 'refund');
assert.equal(cancelled.state, 'CANCELLED');
assert.equal(cancelled.completed_at, null);
assert.equal(operation(customerCancel, cancelled).state, 'CANCELED');
noNewLedger(customerReplay, customerCancel);

const vendorLost = read('vendor-created-lost');
const vendorReplay = read('vendor-created-replayed');
const vendorApproved = read('vendor-finance-approved');
const vendorCompleted = read('vendor-finance-completed');
const final = read('vendor-final');
assert.equal(workflow(vendorLost, 5, 'payout').state, 'REQUESTED');
assert.equal(String(workflow(vendorLost, 5, 'payout').amount_units), '9000');
assert.equal(vendorLost.manual_financial_events.length, customerCancel.manual_financial_events.length + 1);
noNewLedger(customerCancel, vendorLost);
sameRetainedData(vendorLost, vendorReplay);
assert.equal(workflow(vendorApproved, 5, 'payout').state, 'APPROVED');
assert.equal(workflow(vendorApproved, 5, 'payout').completed_at, null);
noNewLedger(vendorReplay, vendorApproved);
const payout = workflow(vendorCompleted, 5, 'payout');
assert.equal(payout.state, 'COMPLETED');
assert.equal(operation(vendorCompleted, payout).state, 'SUCCESS');
const settlements = vendorCompleted.platform_fee_ledger_entries.filter((row) => row.effect_kind === 'vendor_settlement');
assert.equal(settlements.length, 1);
assert.equal(String(settlements[0].exact_amount), '9000');
assert.equal(Number(settlements[0].allocation_id), 5);
assert.equal(vendorCompleted.manual_financial_evidence.length, 2);
assert.equal(workflow(final, 2, 'payout').state, 'REQUIRES_REVIEW');
sameRetainedData(vendorCompleted, final);
assert.equal(final.manual_financial_workflows.some((row) => Number(row.allocation_id) === 4), false);

const financeUi = read('completion-replayed-evidence');
assert.equal(financeUi.exactRequestInputMatch, true);
assert.deepEqual(financeUi.initialEvent.input, financeUi.replayEvent.input);
assert.equal(financeUi.savedCommandReplayCount, 1);
const customerUi = read('customer-created-replayed-evidence');
assert.equal(customerUi.allReplayBodiesMatchOriginal, true);
assert.equal(customerUi.createCallCount, 2);
const vendorUi = read('vendor-created-replayed-evidence');
assert.equal(vendorUi.replayPayloadsMatch, true);
assert.equal(vendorUi.replayCalls.length, 1);
assert.deepEqual(vendorUi.initialLostCreate.input.body, vendorUi.replayCalls[0].input.body);
console.log(JSON.stringify({
  verified: true,
  scope: 'retained disposable native SQLite effects plus saved browser command receipts; not Sanctum or production acceptance',
  refund_principal_units: '5000',
  vendor_settlement_units: '9000',
  response_loss_replays_leave_data_unchanged: ['Finance completion', 'Customer create', 'Vendor create'],
  normal_data: 'checked separately by manual-finance-preservation.php',
}, null, 2));
