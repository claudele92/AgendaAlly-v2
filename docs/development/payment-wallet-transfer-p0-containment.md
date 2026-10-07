# Wallet transfer P0 — minimum containment and focused verification

Date: 2026-10-03. **FOUND AND CONTAINED / VERIFIED.**
The broader payment architecture audit remains **PAUSED**. No next phase,
provider work, historical repair or optional investigation is authorized.

## 1. Root cause and minimum native-compatible correction

The successful native send created:

1. Sender withdrawal history `processed`, Transaction `progress`, balance debit.
2. Recipient top-up history/Transaction `paid`, balance credit.

The sender's own history could then be canceled/rejected. The generic status
service restored the sender debit without reversing the recipient credit.
Ownership alone could not prevent this: the sender owned the affected history.

Native `WalletHistory::PAID` and `Transaction::STATUS_PAID` already provide
appropriate terminal states. The shared status service accepts changes only
from `processed`. No dependable paired transfer identifier exists in this
flow; the debit and credit have separate history/Transaction identities.

**Correction:** after successful recipient credit, mark the sender history and
its Transaction `paid` through the real native status service, **inside the
same outer database transaction**. The transaction commits both completed legs
or neither. Ordinary status actions cannot reverse either completed leg.

No new state, transfer schema, pair inference or customer reversal workflow
was introduced. No matching by amount, timestamps, users or neighboring IDs.
The transient sender debit is known directly from that operation's service
result, not discovered by a database heuristic.

## 2. Corrected lifecycle and conservation

| Operation/state | History | Transaction | Balance effect / next action |
| --- | --- | --- | --- |
| Transfer being executed inside the uncommitted transaction | Sender transiently `processed`; recipient credit not yet committed | Sender transiently `progress` | No committed partial transfer is exposed |
| Successful transfer, both legs committed | Sender and recipient `paid` | Both `paid` | Sender −x, recipient +x; completed legs cannot use generic status changes |
| Failed transfer: recipient absent/invalid, insufficient funds, failed service result or exception | No committed transfer histories | No committed transfer Transactions | Neither debit nor credit persists |
| Genuine pending non-transfer withdrawal | `processed` | `progress` | Native initial debit; owner cancellation/rejection remains available |
| Genuine pending withdrawal canceled/rejected | `canceled` | `canceled` | Native normalization preserved; restore once, reject subsequent requests |
| Genuine pending top-up approved by Admin | `paid` | `paid` | Native approval credits once; replay denied |
| Already-terminal history | Unchanged | Unchanged | Same native 404 / `ERROR_404`, no restoration or repeated credit |

Synthetic conservation test:

- Before: sender 100, recipient 30, combined 130.
- Successful transfer of 20: sender 80, recipient 50, combined **130**.
- After repeated denied customer cancellation/rejection and Admin attempts:
  sender 80, recipient 50, combined **130**, with table fingerprints unchanged.
- Synthetic self-transfer also remains value-neutral and terminal.
- The existing rate normalization is preserved and tested; no FX/economics
  redesign occurred.

These values are isolated fixtures, not existing Customer balances.

## 3. Atomicity and once-only status behavior

The native outer send transaction is retained. Previously, returning an error
response from its closure could **commit** an earlier sender debit if the
recipient service returned an unsuccessful result rather than throwing.
Unsuccessful debit, recipient-credit and finalization results now throw a
native `HttpResponseException` carrying the existing error response. Laravel
rolls back the transaction before returning that response.

Verified failure injection:

- Recipient service returns false **before** credit.
- Recipient service returns false **after** its real history/Transaction/credit.
- Finalization returns false **after** real sender finalization.
- Exception while creating recipient history.
- Exception during a genuine pending withdrawal's Transaction-status update.

All affected fixture tables and balances return to their before-operation
state; no sender-only debit or recipient-only credit remains.

Shared history-status changes now execute in a database transaction and lock the
history row before checking `processed`, status changes and restoration. This
keeps the once-only check and effects together for customer and Admin callers.
Sequential replay tests prove once-only behavior. SQLite `:memory:` tests do
**not** certify simultaneous production-engine locking or all Wallet races.

## 4. Authorization and narrow sibling review

Checked only the two native WalletHistory status actions and their shared
service:

- Customer:
  `POST /api/v1/dashboard/user/wallet/history/{uuid}/status/change`
- Admin:
  `POST /api/v1/dashboard/admin/wallet/history/{uuid}/status/change`
- Shared `WalletHistoryService::changeStatus`.

The customer action now supplies the authenticated owner ID. The service checks
the history's persisted Wallet ownership, not caller `user_id`, `owner_id`,
`wallet_uuid` or `created_by`. Missing/foreign histories share the existing 404
response, before mutation.

User, Seller, moderator and shop_manager actors cannot use the customer channel
to change another Customer's pending history. They gain no new authority.
Customer-supplied `paid` remains rejected by the native action. Anonymous
mutation requests retain the native 401.

Admin's existing route/middleware and unscoped privileged service call are
preserved. Admin may approve a genuine pending top-up or reject a genuine
pending withdrawal, but cannot override a completed transfer through this
shared service. Admin **action/service** behavior was tested in isolation;
the native Admin route-admission middleware was not recreated or certified.
No broader role, privacy or CRUD investigation occurred.

The real customer history action/repository/paginator returns only the actor's
Wallet histories, even with a forged Wallet filter, and returns the completed
sender debit as `paid`. Unrelated nested User-resource presentation is outside
the isolated financial fixture. No Wallet UI was changed.

## 5. Focused verification results

**24 new Wallet cases / 183 assertions, passed.**

```sh
bash scripts/verify-original-hardening.sh \
  --filter WalletTransferContainmentTest \
  --log-junit /tmp/wallet-transfer-final-junit.xml
```

Coverage: exact one debit/credit, terminal consistent histories/Transactions,
conservation, cancellation/rejection/replay denials, Admin sibling behavior,
legitimate non-transfer cancellation once, failures/rollback, foreign-history
denial, Seller/Staff compatibility, anonymous denial, owner-scoped history
query, native rate conversion and self-transfer.

Selected existing regressions also passed:

| Class | Tests | Assertions |
| --- | ---: | ---: |
| BookingStaffAuthorizationTest | 39 | 323 |
| PaymentStatusAuthorizationTest | 22 | 269 |
| SellerBookingAuthorizationMatrixTest | 3 | 32 |
| PaymentRefundAuthorizationTest | 3 | 9 |
| PaymentSettlementIdempotencyTest | 5 | 36 |
| TransactionObserverHardeningTest | 2 | 8 |
| PaymentProviderCallbackTest | 8 | 45 |
| **Existing regression subtotal** | **82** | **722** |
| **Wallet containment** | **24** | **183** |
| **Distinct passing total across focused runs** | **106** | **905** |

The existing classes passed in the selected combined run. That run also exposed
an unrelated nested User-resource dependency in the new isolated self-transfer
fixture. After limiting the fixture's response presentation to the financial
contract, all 24 Wallet cases passed separately. Initial test setup corrections
also established native translation/request dependencies and string conversion
of the newly created UUID object. Production economics and roles were not
changed to accommodate fixture failures.

The mutation route tests execute the real controller actions, FormRequests,
authentication middleware, service, Payable Transaction creation and registered
TransactionObserver. Only unrelated constructor/presentation lookups and
explicit failure injection are isolated. No production application boot,
original environment load, real provider transport, migration or seed occurs.
The GET history query is a real action/repository test, not a full browser or
nested resource serialization test.

Both changed production PHP files pass syntax checks; `git diff --check` passes.
The native Laravel preview was restarted once and started cleanly. The separate
API wrapper health screenshot returned `{"status":"ok"}`; this is **not** a
live Wallet transfer check. No mutating existing-data endpoint was called.

The full suite was **not** run or claimed green. Its prior Driver/Pickup
failures remain outside this containment.

## 6. Existing data and limitations

Read-only structural aggregate:

- **0 `processed` withdrawal histories** in the owned development database.
- **1 total Wallet history**; no row contents, identities or balances printed.
- This is a broad structural count, not a reconstruction of historical transfer
  pairs or evidence of exploitation.

No history was rewritten, normalized, reversed or repaired. Other databases,
including production, were not inspected. If another database contains legacy
`processed` transfer debits, this forward lifecycle correction does not
retroactively distinguish them from genuine pending withdrawals. Reliable
historical reconciliation requires separate approval; do not infer pairs.

Repeated POST sends are native **separate funded operations**, with a matching
new debit for every new credit. Tests prove that they conserve value, not
transport-level deduplication: the existing send API has no stable idempotency
key. No new transfer identity or network retry contract was introduced.

This containment does not certify all Wallet funding, currencies, negative
amount/balance rules, concurrent initiation, provider callbacks, accounting,
refunds or payouts. No unrelated implementation was started.

## 7. Financial fingerprint receipt

Before source remediation and after verification/preview restart:

- **53 protected tables; identical protected table sets.**
- **Identical row counts and SHA-256 fingerprints; zero changed tables.**
- **Identical established serialization codec.**
- **`platform_fee_ledger_entries` included and unchanged.**

Receipts:

- `.local/wallet-transfer-containment-before.json`
- `.local/wallet-transfer-containment-after.json`
- `.local/wallet-transfer-containment-fingerprint-result.json`
- `.local/wallet-transfer-history-observation.json`
- Established read-only serializer: `.local/payment-containment-snapshot.php`

All test mutations occurred in process-local SQLite `:memory:`, never the
existing application database.

## 8. Exact changed files and mandatory stop

Application source, **two files**:

- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/User/WalletController.php`
- `.migration-backup/backend/app/Services/WalletHistoryService/WalletHistoryService.php`

New isolated verification:

- `.migration-backup/backend/tests/Hardening/WalletTransferFixture.php`
- `.migration-backup/backend/tests/Hardening/WalletTransferContainmentTest.php`

Documentation and durable scope/fixture notes:

- `docs/development/payment-wallet-transfer-p0-containment.md`
- `docs/development/payment-system-full-audit.md`
- `.agents/memory/modernization-approval.md`
- `.agents/memory/MEMORY.md`
- `.agents/memory/native-isolated-fixtures.md`

No model, schema, enum, provider, payment policy, Shop preference, UI, Booking,
Product/Service checkout, commission, Vendor balance or payout file changed.

**STOPPED after Wallet P0 containment and focused verification.
The broader payment architecture audit remains PAUSED. Wait for explicit
approval before further audit or implementation. Nothing was published.**