# AgendaAlly — Wallet amount P0 containment

Verified: 2026-10-03, America/Chicago.

**Negative-amount Wallet finding: FOUND AND CONTAINED / VERIFIED.**

Scope: minimum native amount-sign containment, directly shared Wallet sibling,
isolated verification and existing-state protection only. The previously
accepted Wallet transfer terminal-state containment remains intact. The broader
payment architecture audit remains **PAUSED / INCOMPLETE**; it was not resumed.

## 1. Root cause and validation gap

Customer withdrawal used generic `FilterParamsRequest` with an optional
`numeric` price; send required `numeric` but not positive price. The service's
truthiness check admitted negative nonzero amounts. The insufficient-balance
comparison admitted negatives against an ordinary nonnegative Wallet balance.

The service created WalletHistory and Transaction records and used the signed
amount in native balance arithmetic:

- Withdrawal: `A - (-x) = A + x`, creating unbacked spendable balance.
- Send: sender increased and recipient decreased, reversing authorization.

Native signed `double` columns had no positivity constraint. Paid finality
contained cancellation, not invalid direction. This correction does not
redesign finality, schema, precision, pairing, accounting or concurrency.

## 2. Exact invariant and containment locations

All paths covered here require a **numeric, finite amount whose native float
representation is strictly greater than zero**, before financial side effects.
No absolute-value conversion, clamp or sign reversal was introduced.

`B/` below means `.migration-backup/backend/`.

| Boundary | Enforced contract |
| --- | --- |
| `B/app/Rules/PositiveWalletAmount.php` | Shared validation rule and server helper; `is_numeric`, finite native float, `> 0` |
| Customer withdrawal `WithdrawRequest` | Required numeric positive price; extends the existing filter request to preserve other native field rules; invalid requests return the native 422 validation envelope |
| Customer send `SendRequest` | Required numeric positive **original** price, before currency normalization; existing recipient/currency validation preserved |
| `WalletController::send` | Rechecks original input, validates usable positive finite rate before division and normalized positive finite price before the transaction/either leg |
| `WalletController::withDraw` | Rejects invalid input before actor Wallet lookup, balance comparison or service creation; covers direct internal calls and normalized send |
| `WalletHistoryService::create` | Defense in depth before its transaction, history creation, linked Transaction creation, observer dispatch or balance mutation; applies to both debit and credit creation |
| `WalletHistoryService::changeStatus` | After existing owner/processed-state lookup, rejects invalid persisted magnitudes before approval, restoration, status update or financial observer effects; does not repair historical rows |
| Wallet-targeted `PaymentRequest` | For a selected `wallet_id` only, `total_price` is required, numeric and positive; non-Wallet `total_price` rules remain exactly `['numeric']` |

Request failures are 422. Controller/service defense-in-depth failures use
native `ERROR_400`/400 responses; unavailable or terminal/foreign history
semantics remain unchanged. Existing positive insufficient-balance checks
remain authoritative for withdrawal and send.

The rate's existing absent/null fallback is preserved; this pass does not
certify currency policy or conversion readiness. Nonpositive rates and
division overflow/underflow that cannot produce a positive finite magnitude
are rejected without beginning either leg.

## 3. Bounded shared-caller review

The directly shared `WalletHistoryService::create` callers were inspected only
to establish amount semantics and compatibility:

| Caller | Native amount semantics |
| --- | --- |
| Customer Wallet withdrawal/send | Direction is forced by `withdraw` or `topup`; now require positive input and normalized magnitude |
| Payment `BaseService` Wallet top-up settlement | Uses frozen provider amount divided by 100 as a `topup` magnitude; service guard rejects invalid settlement magnitudes, without contacting a provider in this verification |
| `PaymentToPartnerService` Seller Order/Booking and delivery allocations | Existing code expresses direction through type and removes the sign before calling the Wallet service |
| `OrderDetailService` replacement difference | Existing caller selects type and removes the sign before service invocation |
| `PayoutService` Wallet history legs | Uses a payout magnitude with separate top-up/withdrawal types |
| `OrderRefundService` | Refund total and fee reversals use typed positive magnitudes; zero was already rejected by the previous service truthiness check |
| `OrderStatusUpdateService`, `ParcelOrderStatusUpdateService` | Typed total-price credit/refund magnitudes |
| Admin WalletHistory approval/rejection | Changes a genuine processed history; positive top-up approval and pending-withdrawal restoration remain once-only |

No reviewed caller has a legitimate signed-magnitude convention at this
service boundary. Existing internal sign-to-type conversions were not changed;
the new code never converts a customer's negative amount to positive.
Invalid data now fails closed rather than reaching balance arithmetic.

This is not an end-to-end certification of refunds, payouts, checkout or
provider settlement: those subsystems were not redesigned or exercised live.
Some legacy callers do not propagate the service's returned error; no claim is
made that all upstream reporting is corrected by this bounded containment.
They nevertheless cannot create Wallet histories/Transactions or mutate
balances through this service with an invalid magnitude.

### Narrow Customer-reachable sibling

The generic payment request's Wallet top-up branch accepted only `numeric`
`total_price`; `BaseService::beforeWallet` forwards its minor-unit conversion,
and the Wallet settlement branch ultimately calls the same history service.
This is the directly shared amount-sign sibling, not a broader provider audit.

The same invariant now protects its Wallet-only request contract and shared
financial boundary. Sub-unit rounding that produces zero is rejected at the
service boundary; money precision was not redesigned. No provider activation,
credential/configuration change or real payment was performed. Booking,
Product and Service checkout rules were not changed.

No other Customer Wallet creation action calling this same history service
was found in the bounded route/controller/caller review. History listing and
status actions retain their native roles, with the shared invalid-history
guard described above. Unrelated Wallet arithmetic outside this service is not
audited or certified here.

## 4. Numeric boundary verification

Withdrawal/send and direct top-up/withdrawal service tests reject:

- `-1`, `-0.01`, `0`, `0.00`.
- Numeric strings `'-1'`, `'-0.01'`, `'0'`, `'0.00'`, `'-0.00'`, `'+0'`.
- Negative scientific notation `'-1e2'`, scientific zero `'0e10'`.
- Positive scientific underflow `'1e-999'` (native zero) and overflow
  `'1e999'` (native infinity), plus negative overflow.
- Whitespace-wrapped negative values, `NaN`/`INF` strings and native
  non-finite floats, booleans, arrays, null, missing and empty request price.

Native positive `0.01`, `20`, decimal/numeric strings, `'2e1'`, `'1e-2'`,
`'+20'` and whitespace-wrapped positive strings retain the expected direction.
Scientific notation does not bypass the invariant. Normalization tests also
cover zero/negative rates and positive original inputs whose division produces
zero or infinity.

This is positivity at the native arithmetic boundary, not arbitrary-precision
money validation, minimum currency-unit enforcement or a new maximum amount
policy. Existing floating-point precision limitations remain.

## 5. Withdrawal, authorization, atomicity and finality

Rejected withdrawal/send requests leave every fixture Wallet unchanged, create
zero WalletHistory and Transaction records and dispatch zero Transaction
create/update observer events. Fixture fingerprints are compared for every
invalid request. The real native Transaction observer is installed; rejected
requests cannot reach its financial accounting handlers.

Positive withdrawal still debits only the authenticated Customer, creates a
`processed` history with a `progress` Transaction and rejects insufficient
funds. Native pending non-transfer cancellation/rejection restores once only.

Positive send still debits the authenticated sender once and credits the
selected recipient once. Both histories and linked Transactions finish `paid`,
total Wallet value is conserved, and cancellation/rejection cannot reverse
completed value movement. Fault injection before/after recipient creation and
during finalization, plus an exception during recipient creation, verifies
atomic rollback of both legs and records.

Forged `user`, `user_id`, `wallet_uuid`, `owner_id`, type and status fields do
not change the actor Wallet debit in withdrawal or send. Customer-channel user,
Seller, moderator/Staff and Shop manager fixtures cannot mutate foreign
histories. They gain no financial authority from this change. Existing
anonymous denials and Customer inability to approve paid status still pass.
Admin genuine pending top-up approval works once, without replay credit.
Synthetic invalid legacy histories cannot be approved/restored; no actual
historical data was altered.

## 6. Exact focused verification results

The final combined run passed:

| Test class | Tests | Assertions |
| --- | ---: | ---: |
| WalletAmountContainmentTest (new) | 160 | 922 |
| WalletTransferContainmentTest (accepted regressions) | 24 | 183 |
| PaymentCollectionAmendmentTest | 5 | 33 |
| PaymentIntentAuthorizationTest | 6 | 28 |
| PaymentProviderCallbackTest | 8 | 45 |
| PaymentRefundAuthorizationTest | 3 | 9 |
| PaymentSettlementIdempotencyTest | 5 | 36 |
| PaymentStatusAuthorizationTest | 22 | 269 |
| TransactionObserverHardeningTest | 2 | 8 |
| **Total** | **235** | **1,533** |

Wallet-specific subtotal: **184 tests / 1,105 assertions**.
Other selected native regression subtotal: **51 tests / 428 assertions**.
These are unique cases from the final run, not a sum of repeated executions.

```sh
bash scripts/verify-original-hardening.sh \
  --filter 'Wallet(Amount|Transfer)ContainmentTest|Payment(StatusAuthorization|RefundAuthorization|SettlementIdempotency|ProviderCallback|IntentAuthorization|CollectionAmendment)Test|TransactionObserverHardeningTest' \
  --log-junit /tmp/wallet-amount-final.xml
```

The command also lints native app/routes/hardening PHP files. `git diff --check`
passes. Tests use the existing isolated bootstrap and a fresh process-local
SQLite database per case, not the owned development database, native `.env`,
production providers, seeds or migrations. HTTP provider stray requests are
disabled; no real provider call is allowed.

The Wallet fixture dispatches real native request/controller/service/model
actions and observer logic, while mocking authenticated actors and bypassing
unrelated nested User-resource presentation. It does not certify full Sanctum
login, native privileged Admin route admission, UI or deployed SQL concurrency.
No UI changed, and no live financial browser journey was run.

The native Laravel preview was restarted once after the application changes;
logs confirm a clean owned-development-database check and PHP server startup.
The unrelated already-failed full hardening workflow was not restarted or
represented as passing; the explicit focused command above passed.

## 7. Existing data and 53-table protection

A permitted read-only aggregate query against the owned development database
found **0 negative and 0 zero WalletHistory amounts**. Only counts were emitted;
no Customer identities, balances or transaction details were exposed. This is
not evidence of historical exploitation or a production observation.

The established serializer uses `PRAGMA query_only=ON`, the same sorted-row
encoding and SHA-256 fingerprints before remediation and after final tests
and native backend restart:

- **53 protected tables; identical table sets.**
- **Identical row counts and field-level fingerprints.**
- **Matching serialization codec; zero changed tables.**
- **`platform_fee_ledger_entries` included and unchanged.**

Receipts:

- `.local/wallet-amount-containment-before.json`
- `.local/wallet-amount-containment-after.json`
- `.local/wallet-amount-containment-fingerprint-result.json`
- `.local/wallet-amount-history-observation.json`
- `.local/wallet-amount-containment-tests.txt`
- Established serializer: `.local/payment-containment-snapshot.php`

There was no historical normalization, financial data repair, database schema
change, real Wallet mutation, seed, migration, refund or payout.

## 8. Exact files changed

Application/test files:

1. `B/app/Rules/PositiveWalletAmount.php` — new shared invariant/rule.
2. `B/app/Http/Requests/WalletHistory/WithdrawRequest.php` — new native withdrawal request.
3. `B/app/Http/Requests/WalletHistory/SendRequest.php` — positive price validation.
4. `B/app/Http/Requests/Payment/PaymentRequest.php` — Wallet-only top-up validation.
5. `B/app/Http/Controllers/API/v1/Dashboard/User/WalletController.php` — withdrawal binding and original/normalized amount guards.
6. `B/app/Services/WalletHistoryService/WalletHistoryService.php` — create/status financial boundary guards.
7. `B/tests/Hardening/WalletAmountContainmentTest.php` — new isolated focused tests.

Documentation/approval notes:

8. `docs/development/payment-wallet-amount-p0-containment.md` — this report.
9. `docs/development/payment-system-full-audit.md` — latest containment and audit pause.
10. `.agents/memory/modernization-approval.md` — current bounded approval/stop rule.
11. `.agents/memory/MEMORY.md` — approval-boundary index updated.

The uploaded instruction file was not edited. Local verification receipts are
listed separately above. Existing Wallet transfer tests/fixtures were not
changed. No frontend, routes, provider, schema, environment or policy file was
changed.

## 9. Remaining limitations and stop

- No transport-level send idempotency key; repeated valid send requests remain
  separate funded operations.
- No production concurrency certification or Wallet concurrency redesign.
- No money precision redesign or production/provider readiness certification.
- No historical reconciliation, exploitation inference or repair.
- No commission, payout, Vendor-direct, country/currency policy, collection
  preferences or checkout architecture changes.
- The original payment architecture questions remain incomplete.

**STOP after this verified containment. Do not resume the broader architecture
audit, propose another optional task, publish or implement another phase.
Wait for explicit creator approval.**