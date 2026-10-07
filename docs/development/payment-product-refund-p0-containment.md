# AgendaAlly — Product refund repeat-credit P0 containment

Date: 2026-10-03, America/Chicago.

**Finding: FOUND AND CONTAINED / VERIFIED.**

Scope is only the approved Product refund repeat-settlement defect and identical
shared paths. The broader payment architecture audit remains **PAUSED**.
Both accepted Wallet containments remain unchanged; their investigations and
full verification suites were not reopened.

## Root cause and native lifecycle

Customer request creation validates that `order_id` belongs to the authenticated
Customer. The Vendor update route requires `payments.refunds.manage` and the
controller's persisted same-Shop authorization. Admin update uses the same
`OrderRefundService`. No authorization bypass was necessary for this defect.

The only native refund states are `pending`, `accepted`, and `canceled`.
There is no native `rejected` state: declining a request uses `canceled` with an
answer. Previously, the update service rejected only an unchanged status.
`accepted → pending → accepted` was allowed. Each acceptance called the paid
Wallet top-up operation again. Its duplicate check searched for an Order
Transaction with status `refund`, but this commerce path never wrote that
marker.

The Customer credit is specifically the `WalletHistoryService::create` call
using the Order's Customer, `type=topup`, the persisted refundable total, and
`status=paid`. The native Wallet service creates a paid WalletHistory, creates
its paid Wallet-side Transaction, links them, and increments the Customer Wallet.
It is a different financial record from the original Order payment Transaction.

## Corrected state machine

| Current state | Requested state | Result |
| --- | --- | --- |
| `pending` | `accepted` | Allowed only for a paid, not already settled/refunded Order with a valid Customer Wallet and successful positive refund effects. |
| `pending` | `canceled` | Allowed; no financial effects. Native HTTP validation requires an answer. |
| `pending` | `pending` | Denied; no effects. |
| `accepted` | Any state, including `accepted` | Denied; no effects. Acceptance is terminal. |
| `canceled` | Any state | Denied; no effects. Cancellation is terminal for that request. |
| Any state | `rejected`, unknown, null, or malformed status | Denied; no effects. No new state is invented. |

A Customer can still create a **new** pending request after cancellation, as
the original creation policy allowed. This does not reopen the canceled row.
A new request is refused when **any** pending or accepted request already exists
for the Order, not just when the first matching row has such a status.

No lifecycle requires moving an accepted refund backward. The existing
`accepted` refund row is retained as the authoritative commerce-settlement
witness. **No schema change or new financial-status field was required.**

## Once-only financial invariant and retained payment history

One paid Order cannot acquire a second successful commerce refund settlement
through this service:

1. Reload persisted refund/Order identity; ignore stale caller status,
   relationships and payload ownership.
2. Lock the persisted Order, then the refund, inside one database transaction.
3. Require the current refund to be pending and the requested status to be
   accepted or canceled. Reject another accepted refund for the same Order.
4. Atomically claim `pending → accepted` using a conditional database update.
5. Require an original paid Order Transaction and no existing Order Transaction
   marked `refund`. Execute the native refund effects and check every Wallet
   operation's success.
6. Commit the accepted marker and financial effects together. Failure rolls
   everything back, including the claim.

The original Order payment Transaction remains `paid`, unchanged. The Order's
commerce status is also unchanged. Successful payment history is not rewritten
solely to align display statuses. Refund reversal is represented by the retained
accepted commerce refund and the new paid Wallet-side records.

Historical accepted rows are conservatively terminal too. This does **not**
assert that every old accepted row has complete financial history; the old
service could accept an unpaid Order without crediting it. No historical repair
or inferred settlement is performed.

## Exact financial and accounting effects

| Effect | First legitimate acceptance | Replay, reopening, or sibling acceptance |
| --- | --- | --- |
| Customer Wallet | One positive credit of the native persisted refundable total | None |
| Customer WalletHistory | One paid top-up linked to its Transaction | No new row |
| Customer refund-side Transaction | One paid Transaction whose payable is the WalletHistory | No new row |
| Original Order payment Transaction | Remains paid and unchanged | Unchanged |
| Order status | Preserved | Preserved |
| Product stock/order counters | Native stock restoration/counter update once | No repeat restoration |
| Delivered seller/delivery partner recovery | Existing paid-partner withdrawal effects once, where native conditions apply | No repeat debit, history or Transaction |
| Referral reversal scheduling | Existing delivered-Order after-response job registered only after successful settlement commit | No repeat registration |
| Service fee / commission ledger | Existing fee entry retained; this native refund path has no commission reversal | No new, repeated or altered ledger entry |
| Platform payable | This Product refund path has no native payable adjustment to repeat | No adjustment introduced |

The amount is derived from persisted `Order.total_price`, less persisted
downloaded digital-line totals under the existing calculation. Status requests
cannot supply the credited amount or redirect its recipient. Both the HTTP
validated input and shared service's field allowlist prevent forged Order,
Shop, Customer, Wallet, amount and other ownership fields from affecting the
transition. New requests are explicitly created pending.

This containment does not invent missing commission/payable/provider-refund
accounting. Those architecture questions remain paused.

## Narrow shared-path and deletion review

Vendor and Admin updates both call the corrected service, with no privileged
terminal-state bypass.

Deletion is directly relevant: erasing an accepted row would erase the selected
once-only witness and allow creation of a replacement request. Therefore:

- Shared Customer/Vendor/Admin deletion retains accepted refunds.
- Admin bulk deletion now uses the same protected deletion path rather than
  inherited unrestricted deletion.
- Nonsettled deletion remains available under the existing authority rules.
- Creation and deletion use the same Order-first locking order as settlement.

No Service Booking refunds, provider refund implementations, general Order
status, payouts, unrelated Wallet operations or unrelated authorization were
investigated or changed.

## Transaction, contention and failure verification

The Order lock serializes different request rows for the same entitlement on
row-locking databases. The conditional pending-state update also prevents a
stale route model from authorizing a second settlement.

SQLite does not implement `SELECT … FOR UPDATE`. A controlled isolated-file test
used **two independent SQLite connections**: while the first native settlement's
claim was uncommitted, the competing connection still saw pending but its write
was refused with database locking. After commit, the competing conditional
update affected zero rows, and a stale service retry produced no second credit.
Duplicate pending rows and stale loaded models were also tested.

Injected exceptions/false results verified whole-fixture persisted-state
rollback:

- immediately after the atomic claim, before credit;
- during stock restoration before credit;
- during Wallet credit;
- after real Customer credit and record creation but before outer completion;
- after the real Customer balance SQL update;
- during delivered partner recovery.

Successful retry after a rolled-back Wallet failure settles once. No failed
delivered settlement registers the referral job.

**Limitations:** This is not production concurrency certification or proof for
every database isolation level. Asynchronous referral job execution remains the
native after-response mechanism; only its once-only, post-success registration
was verified, not atomic delivery/execution with database commit. Provider
refunds and external remittance were not exercised or implemented.

## Focused tests and authorization regressions

Final combined isolated verification: **74 tests / 536 assertions passed**:

- 42 new Product refund cases covering legitimate acceptance, terminal states,
  replay/cycling, creation/deletion bypass prevention, stale models, duplicate
  requests, delivered partner recovery, persisted amounts, forged ownership,
  failure atomicity, contention, and refund Wallet-record terminality.
- 32 selected existing native payment/refund authorization, Transaction observer,
  and relevant Wallet ownership/Admin top-up regressions.

The real native refund/Wallet services, SQL records, Transaction observer,
FormRequest validation, Shop permission middleware and role admission are used.
The fixtures do not boot the native app or read its environment/database.
Unrelated controller constructor lookups/presentation are bypassed, and referral
job dispatch is observed without executing the asynchronous job. Provider
transport is prevented. The contention database is a temporary copy of
synthetic fixture data only.

Authorized Shop owners, granted same-Shop Staff and Admin settle once.
Foreign-Shop Vendors, ungranted Staff, Customers invoking Vendor settlement,
anonymous callers and non-Admins invoking Admin settlement are denied with
unchanged financial state. The previously contained Seller authorization code
was not changed.

There are **zero test failures/errors**. One pre-existing PHP 8.4 deprecation
remains in `OrderHelper::checkShopDelivery`'s nullable parameter declaration; it
was not changed. The failed broader `original-hardening` workflow was not rerun
or represented as a passing full-suite baseline.

Final command, from `.migration-backup/backend`:

```sh
vendor/bin/phpunit -c phpunit-hardening.xml \
  --filter 'ProductRefund(Containment|Authorization|Concurrency)Test|PaymentRefundAuthorizationTest|PaymentStatusAuthorizationTest|TransactionObserverHardeningTest|WalletTransferContainmentTest::test_(customer_channel_cannot_change_foreign_pending_history_even_with_forged_ownership|admin_can_finalize_genuine_pending_topup_once_without_recredit_on_replay)' \
  --display-deprecations
```

Test receipt: `.local/product-refund-containment-tests.txt`.
All changed PHP files pass syntax checks; `git diff --check` passes.

## Historical observation and protected-state receipt

Owned-development count-only observation:

- Pending Product refunds: **0**
- Accepted Product refunds: **0**
- Canceled Product refunds: **0**

No identities, monetary details or balances were inspected for that observation.
The native history lacks an authoritative refund-to-Wallet settlement link and
transition history, so do not infer historical repeat settlement from note text,
timestamps or similar amounts. No historical records were repaired.

**All 53 protected tables retain identical table sets, row counts and
fingerprints**, with matching codec and zero changed tables, including
`platform_fee_ledger_entries`.

Receipts:

- `.local/product-refund-containment-before.json`
- `.local/product-refund-containment-after.json`
- `.local/product-refund-containment-fingerprint-result.json`
- `.local/product-refund-history-observation.json`
- Established serializer: `.local/payment-containment-snapshot.php`

The native Laravel preview restarted once after the code batch; clean PHP
server startup was confirmed. No real payment, Order, Booking, Wallet operation,
refund/payout, seed, migration, credential/provider/policy/settings change,
historical repair or publishing occurred.

## Exact changed files and final stop

Application source:

- `.migration-backup/backend/app/Services/OrderService/OrderRefundService.php`

New isolated test files:

- `.migration-backup/backend/tests/Hardening/ProductRefundFixture.php`
- `.migration-backup/backend/tests/Hardening/ProductRefundContainmentTest.php`
- `.migration-backup/backend/tests/Hardening/ProductRefundAuthorizationTest.php`
- `.migration-backup/backend/tests/Hardening/ProductRefundConcurrencyTest.php`

Reports:

- `docs/development/payment-product-refund-p0-containment.md`
- `docs/development/payment-system-full-audit.md`

Approval/isolated-fixture knowledge notes were updated separately in
`.agents/memory/`; local receipt files listed above were generated.
No native controller, request, Wallet service, observer, frontend or schema was
changed.

**STOP. The Product refund P0 is contained and verified. The broader payment
architecture audit remains paused. No optional tasks are proposed. Wait for
explicit approval before further work.**