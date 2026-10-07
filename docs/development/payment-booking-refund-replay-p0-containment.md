# Booking cancellation refund replay — bounded containment

Date: 2026-10-03. **FOUND AND CONTAINED / VERIFIED.**

## Root cause and native chain

An authorized same-Shop Seller could use `canceled → booked → canceled`.
`BookingService::statusUpdate` guarded only identical current/next values. Each
Wallet cancellation directly incremented the Customer Wallet by Booking total,
then deducted the cancellation percentage. Reopening took no replacement payment.
The ledger's `firstOrCreate` prevented a duplicate payable adjustment, not another
Customer credit. BookingObserver logs status changes; it did not guard settlement.

Source roots below use `B = .migration-backup/backend`:

- `B/routes/api.php`: Seller Booking management routes; authenticated Admin,
  Master and User Booking status routes and User parent-cancellation route.
- `B/app/Http/Controllers/API/v1/Dashboard/{Seller,Admin,Master,User}/BookingController.php`:
  status delegation; Seller retains the accepted operational authorization checks.
- `B/app/Http/Requests/Booking/StatusUpdateRequest.php`, `B/app/Models/Booking.php`:
  native `new`, `booked`, `progress`, `ended`, `canceled`.
- `B/app/Services/BookingService/BookingService.php`: shared lifecycle,
  assignment checks, parent grouping, cashback/statistics and payable reversal.
- `B/app/Traits/Payable.php`: original payable Transaction relations exclude
  child Transactions; update-or-create omits `refund_time` except canceled payment
  creation, so ordinary updates do not erase that field.
- `B/database/migrations/2022_08_06_190654_create_transactions_table.php`:
  existing nullable `refund_time` (no new schema).
- `B/app/Observers/TransactionObserver.php`: paid-commerce fee and payable
  creation; WalletHistory commerce projection is distinct.

## Corrected financial/lifecycle boundary

One original Booking payment's cancellation settlement is consumed at most once.
The original payable Transaction's existing `refund_time` is conditionally claimed
inside the lifecycle transaction. Original `paid` status remains **paid**.
Any non-null original-payment refund marker blocks further lifecycle transitions
even if a stale/legacy writer overwrites Booking status. Current canceled state
is also terminal, protecting unpaid/Cash/no-Transaction cancellations.

The native Appointment UI suppresses canceled appointment actions; no documented,
independently funded renewal lifecycle was found. This containment does not invent
one. Before cancellation, native non-canceled transitions retain their existing
assignment/authorization behavior. Canceled → any state and repeated canceled
are refused. Generic update cannot reopen a canceled/settled Booking; nonfinancial
updates retaining its status are not broadly redesigned.

The User parent is itself the first funded appointment, not an unfunded aggregate.
Parent cancellation now invokes the identical guarded child settlement for each
appointment, in stable order, inside one outer transaction. It formerly separately
debited the aggregate Wallet without the shared claim. Failure in any child
rolls back all children. No general Booking CRUD or sibling authorization expansion.

## Policy and amount authority

The percentage comes from global `settings.booking_canceled_commission`, not a
Shop/Service percentage. Native SettingsSeeder supplies 10; Admin booking settings
exposes a number control. That seed is **not** a runtime fallback. Both the
percentage and `booking_refund_canceled_hour` rows are absent in owned development.
Old null multiplication silently produced zero fee/full refund; no documented
intentional missing-percentage full-refund policy was found.

When the native calculation window requires the percentage, absence/malformed/
nonfinite/out-of-range values now abort and roll back. Explicit numeric 0 remains
valid full refund; 100 is valid zero net refund. No settings were inserted or
changed. Missing hour retains the existing 24-hour fallback; explicit finite
nonnegative hours are respected, malformed/negative hours fail closed.
The native comparison remains `start_date > now - hours`; it was not replaced
with a new advance-notice business rule.

Eligible paid Wallet refund = `min(persisted Booking total, original paid
Transaction price) × (1 - percentage/100)`. This preserves normal matched-price
behavior and caps credit at actual original funding. Nonpositive/nonfinite
funding, unpaid Wallet payment, missing Wallet and charge-currency mismatch refuse
a positive refund. Outside the native window, percentage is 100 and no zero-value
Wallet operation is manufactured.

Request refund amount, total, Customer, Wallet, Shop, Booking, payment and policy
fields cannot select or increase the settlement. Only validated lifecycle/note
input is consumed; amount/owner/method are reloaded from persisted relationships.

## Records and accounting

- A positive Wallet refund uses the established `WalletHistoryService::create`
  with `topup/paid`: exactly one positive finite history, one paid WalletHistory
  Transaction and one actual Customer credit. Its note identifies Booking/payment.
- The original paid Booking Transaction only gains the settlement timestamp.
  It is not visually relabeled refunded/canceled and original commerce fee/
  payment history remains intact.
- Native commission/service fee calculation is not redesigned. Existing
  `reversePayableForCanceledBooking` makes one negative payable adjustment
  against original recorded payable; the whole settlement now shares its guard.
- Native electronic cancellation's direct Customer-Wallet fee debit is preserved,
  bounded/validated and guarded once. It is **not a provider refund** and still
  does not create a WalletHistory. Cash has no prepaid principal credit.
- Existing cashback recovery/deletion and ended-Booking statistics rollback
  remain in the same transaction. No Product stock path exists in this Service
  cancellation. No new scheduling/capacity restoration or partner payout is invented.
- BookingObserver presentation/logging and notifications are not financial
  authority. Push notifications remain outside the SQL atomicity guarantee.

## Atomicity and competing claims

Booking load/assignment validation and settlement run inside one transaction
with `lockForUpdate`. A conditional Booking status write occurs before money;
the original Transaction is also locked and atomically updated only where
`refund_time IS NULL`. This matters because SQLite ignores `FOR UPDATE`.
Claims, history/Transaction creation, credit, cashback recovery, final lifecycle
and payable adjustment commit together or roll back together. A false native
Wallet result is treated as failure, not silently accepted.

Controlled test: a second independent PDO connection sees the uncommitted claim
as absent, but cannot write while the first owns SQLite's writer lock. After
commit its conditional claim affects zero rows. Native retry refuses and there
remains one history/credit. **This is not production concurrency certification.**

## Verification

Command: `vendor/bin/phpunit -c phpunit-hardening.xml --filter
'BookingRefund|BookingStaffAuthorizationTest|PaymentRefundAuthorizationTest|TransactionObserverHardeningTest'
--display-deprecations` from `B`.

**87 tests / 498 assertions passed**: 43 new Booking cases and 44 narrow accepted
authorization/observer regressions. No full Wallet/Product containment rerun.

Coverage: initial paid Wallet cancellation; repeat and every canceled reopening;
marker with externally overwritten lifecycle; original paid status; exact
history/Transaction/payable counts; funding cap; forged fields; absent/invalid/
0/10/100 policy; invalid window; invalid funding and currency; false/exception
before/after credit; SQL exceptions after Booking/payment claims, credit and ledger;
finalization failure with cashback; once-only cashback; Cash/electronic behavior;
late zero refund; grouped User cancellation/child failure; SQLite contention.
Real native services/observers/accounting run; presentation/push transport is
stubbed. Seller/Shop Manager/Admin/User/assigned Master positive route fixtures
pass. Existing Staff regression covers granted Moderator/Manager, ungranted,
foreign Shop and actor ownership checks; Customer financial privileged routes deny.

Owned-development receipts:

- `.local/booking-refund-containment-before.json`
- `.local/booking-refund-containment-after.json`
- `.local/booking-refund-containment-fingerprint-result.json`
- `.local/booking-refund-containment-tests.txt`

Established ordered-row serializer: `.local/payment-containment-snapshot.php`.
**53 tables; same table set/codec/counts/fingerprints; zero changed tables;
fee ledger unchanged.** All mutations used disposable fixtures. Native Laravel
preview restarted once with clean startup. Existing unrelated hardening workflow
failure was not relabeled as a passing full-project suite.

## Exact remediation/test files

Under `B`:

1. `app/Services/BookingService/BookingService.php`
2. `app/Services/BookingService/BookingCancellationSettlement.php`
3. `tests/Hardening/BookingStaffFixture.php` (native refund/currency fixture fields)
4. `tests/Hardening/BookingRefundFixture.php`
5. `tests/Hardening/BookingRefundContainmentTest.php`
6. `tests/Hardening/BookingRefundAuthorizationTest.php`
7. `tests/Hardening/BookingRefundConcurrencyTest.php`

Reports, isolated receipt files and approval-memory update are additional
documentation, not native financial configuration/schema changes.

## Limitations and next approved phase

No historical aggregate replay claim is made. The old direct-credit path lacks
an authoritative refund linkage that can prove historical repetition; absence of
a timestamp is not proof of no prior refund. No historical rows are repaired.
Production concurrency, transport deduplication, precision, mixed/partial-payment
accounting and provider refunds remain separate architecture limitations.
Changing generic payment history administratively is not independently funded
renewal. Deletion/creation of unrelated new Bookings is not refunded legacy funding.

No provider activation, credentials, collection preferences, real operations,
native migrations/seeds or publishing occurred. Both Phase 1 gates passed;
Phase 2 proceeds automatically at the interrupted cancellation/accounting trace,
strictly read-only. New natural findings are documented, not fixed or exploited.