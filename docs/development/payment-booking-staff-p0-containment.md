# Booking Staff cross-Shop P0 — bounded containment and verification

Date: 2026-10-03, America/Chicago.

**Approved containment completed. The broader payment audit remains PAUSED.
STOP: no further audit, provider work or security remediation is authorized
automatically.**

## 1. Scope and root cause

Primary route:

`POST /api/v1/dashboard/seller/bookings/{id}/status/update`

The operational route admits `moderator` and `shop_manager`. Its native
`bookings.status` middleware checks the actor's own Shop, while
`BookingService::statusUpdate` loads the requested Booking globally.
`checkAssignedBeforeUpdate` has Admin, Master, Seller and Customer branches,
but no rejection branch for Staff-only roles. An own-Shop grant therefore
reached another Shop's lifecycle and financial effects.

The pre-containment, source-only evidence is preserved in
[`payment-audit-resumed-p0-stop.md`](payment-audit-resumed-p0-stop.md).
This report does not claim an exploit against existing data or production.

## 2. Authorization before and after

**Before:** actor → own-Shop `bookings.status` → arbitrary Booking ID →
Staff fallthrough → lifecycle transaction.

**After, for operational Staff:** authenticated actor → native persisted
operational Shop → requested persisted Booking → matching authoritative
Booking Shop → existing target-bound Shop policy/grant → otherwise-valid
native lifecycle operation.

The new private controller guard runs **before invoking the mutation service**,
before activity, status, Wallet, points, accounting, notification or deletion
effects. It does not authorize by mutating and rolling back.

### Canonical ownership and Staff relationship

- **Target:** persisted `Booking.shop_id`, with a resolvable persisted Shop.
  No request Shop/Vendor/Seller/branch/master field supplies ownership.
- **Operational Shop:** the native Seller controller's Shop, resolved from
  `User.shop ?? User.moderatorShop`; the latter is the existing Invitation-based
  `HasOneThrough`, not a new membership model.
- **Target authorization:** reuse the existing `PayableShopAuthorization`.
  A persisted native owner retains owner authority. Non-owner Staff require
  an accepted Invitation for that target Shop, a ShopRole belonging to that
  same Shop and the existing requested permission.
- **Consistency:** the reused policy fails closed for missing Shop attribution,
  missing Shop and missing/foreign associated Service attribution. It does not
  replace a missing Booking Shop with caller-provided ownership.
- **Permission:** `bookings.status` is unchanged. Adjacent management mutations
  retain their existing `bookings.manage` grant. No new permission was created.

Existing middleware remains active. Anonymous requests receive the native
401. Missing grants and non-accepted relationships generally receive the
existing 403; missing operational relationships receive the existing 401.
An admitted Staff actor targeting a missing, foreign or inconsistent Booking
receives the same native **404 / `ERROR_404`**, without Booking-existence
disclosure or exception file/line details.

## 3. Actor and role-combination results

| Actor/path | Focused result |
| --- | --- |
| Moderator only | Own-Shop valid status operations allowed; same-country foreign Shop denied before service entry. |
| Shop manager only | Independently verified with the same own/foreign results. |
| Seller | Original shared Shop-owner/Customer assignment semantics preserved. Own-Shop update and the existing Seller-as-Customer fallback remain reachable; foreign unassigned status update stays denied. |
| Master/Specialist | Real shared lifecycle remains reachable for assigned Bookings without Seller Shop membership; unassigned Booking denied. |
| Customer/user | Own Booking reaches the real shared lifecycle without Seller Shop membership; another Customer's Booking denied. |
| Admin | Existing global assignment exemption preserved, including Admin+Staff combinations; existing country scopes still restrict target lookup. |
| Staff+Seller | Native owner operation preserved, irrespective of tested role order; foreign operational Staff target remains denied. |
| Staff+Customer / Staff+Master | Operational Seller/Staff channel is target-Shop bound even if the actor is the foreign Booking's Customer/Master. Their separate shared Customer/Master lifecycle semantics are unchanged. |

Native `CheckSellerShop` uses `User.role`, whose accessor returns the last
loaded Spatie role. Some Staff+Customer/Master orderings therefore receive
the **pre-existing 401 before the controller**; orderings admitted by that
middleware receive target-bound 404 for the foreign Shop and allow the valid
own-Shop operation. The tests verify both orders; this task did not reorder
roles, redesign precedence or expand route admission.

The shared assignment service was not changed to require Seller membership
for everybody.

## 4. Status, branch and country contracts

The native `StatusUpdateRequest` accepts:

`new`, `booked`, `progress`, `ended`, `canceled`.

Both Staff roles were tested against every destination:

- Foreign-Shop requests denied before service entry, including forged
  ownership fields.
- Otherwise-valid own-Shop transitions remain reachable through the real
  lifecycle.
- Non-native destinations retain validation rejection.

No transition graph, cancellation formula, price/commission formula or native
status validator changed.

**Branch semantics:** status update, extra-time and bulk deletion were native
Shop-wide mutation paths. They did not call the existing branch-visibility
helpers. This containment binds those operations to the authorized Shop and
does not invent a new branch restriction. Existing list/detail/full-update
branch scopes remain unchanged. Forged branch/master fields cannot authorize
another Shop.

**Country semantics:** Booking/Shop country scopes remain unchanged.
The isolated foreign-Shop cases place both Shops in the same country and
include an active country scope; they still fail target-Shop authorization.
Country is not membership. An Admin's out-of-country target remains unavailable.

## 5. Cancellation: real effects and zero-effect denial

Fixtures use SQLite **`:memory:`**, never the existing application database.
The native controller action, status FormRequest, Sanctum/role/Shop/grant
middleware, target policy, Booking lifecycle, Booking activity and payable
reversal execute. The TransactionObserver remains registered.

Only response-resource presentation and post-success notification transport
are stubbed; authorization and financial logic are not mocked. Customer/Master
compatibility checks call the real shared service rather than claiming a full
browser/client integration test.

### Authorized own-Shop cancellation

For each Staff role, a synthetic paid non-cash Booking exercises the existing
cancellation behavior:

- Booking becomes `canceled`, with native activity recorded.
- Fixture Wallet starts at 100; unchanged native total-price debit of 40 and
  existing point reversal of 2 leave 58.
- Its existing point-history row is removed; the other Booking's row remains.
- Existing payable of 33 receives the native **-33 payable adjustment**.
- Other Customer Wallet stays 100; the existing Transaction remains `paid`.

These are fixture values, not existing financial records. The debit direction
and formulas were deliberately not changed or certified as a complete refund
architecture. No provider collection, external refund or payout occurs.

### Denied foreign-Shop cancellation

Every relevant denial compares all isolated table counts/hashes, including
Wallets, point/history, payments/Transactions, Booking activity, Bookings,
Shop/User statistics, payable ledger and other fixture accounting records.

It also checks **zero mutation-service entries and zero Booking
updating/deleting events** for affected Staff. Therefore denial precedes
lifecycle execution rather than depending on rollback. Existing financial
state and Vendor liability remain unchanged.

## 6. Narrow sibling inspection and minimum containment

| Adjacent operational path | Finding and disposition |
| --- | --- |
| Full Booking update | Already checks target Shop and native branch scope before its update service. Preserved. |
| Extra-time mutation | Same Staff assignment fallthrough on a global Booking lookup; creates priced BookingExtraTime records and activity. Added the same target-bound guard with existing `bookings.manage`, without altering duration, pricing or scheduling code. Own-Shop creation allowed; foreign creation has zero effects. |
| Bulk deletion / delete alias | Native controller forwarded caller IDs/filter to a service with no assignment check. That could remove another Shop's financial Booking record; caller `shop_id` was only a query filter. Added target-bound checks for **every actual mutation ID before any deletion**, including Sellers because this service has no existing Seller assignment boundary. Mixed own/foreign batches fail as a whole. Own-Shop deletion remains reachable. Both native delete routes use this guarded action; their existing ID/body semantics were not redesigned. |
| Notes and times mutation | Shared Staff assignment fallthrough remains a source concern. No direct Wallet/payable mutation was established in those paths during this bounded review; metadata/scheduling authorization needs resumed audit. Left unchanged, not certified safe. |
| Creation | Existing native Seller validation/boundaries retained; no create/scheduling redesign. |

Only the immediately adjacent financial-record mutations were contained.
No process-token redesign was needed or performed.

## 7. Focused verification

Successful runs:

```sh
bash scripts/verify-original-hardening.sh \
  --filter 'BookingStaffAuthorizationTest|PaymentStatusAuthorizationTest|RefundMutationAuthorizationTest|SellerBookingAuthorizationMatrixTest'
# 64 tests, 624 assertions; actual matching classes:
# BookingStaffAuthorizationTest, PaymentStatusAuthorizationTest,
# SellerBookingAuthorizationMatrixTest.

bash scripts/verify-original-hardening.sh --filter PaymentRefundAuthorizationTest
# 3 tests, 9 assertions.
```

**Total: 67 distinct tests, 633 assertions, all passed.**
The new Booking class contributes **39 cases**. The first command included a
nonmatching refund class name, so the actual refund class was verified
separately; no missing test group is represented as having run.

Coverage includes both Staff roles, all native destinations, anonymous
requests, missing/revoked permission, pending/rejected/canceled/deleted
Invitations, wrong-Shop ShopRole binding, forged ownership fields, absent
Booking/Shop, inconsistent Service attribution, real cancellation accounting,
both role orders, Seller/Customer/Master/Admin compatibility, country
restrictions, priced extra-time and all-or-nothing deletion.

Initial failures concerned isolated schema completeness and expectations for
native last-role/localized-error behavior; fixture/assertion corrections were
made without changing production role precedence, lifecycle or economics.

Production PHP syntax and patch-whitespace checks passed. The existing Laravel
preview was restarted once and came up cleanly. The API wrapper health endpoint
also responds `{"status":"ok"}`. No existing-data mutating route was called.

The **full hardening suite was not rerun**. Its prior Driver/Pickup failures
remain outside scope; these focused successes do not establish a green full
suite or production certification.

## 8. Existing financial data: 53-table fingerprint receipt

Established PDO serializer:
`PRAGMA query_only=ON`, associative rows, identical JSON flags, byte-sorted
serialized rows, newline join and SHA-256.

- **53 protected tables; identical table sets and serialization codec.**
- **Zero changed table counts/fingerprints.**
- **`platform_fee_ledger_entries` included and unchanged.**
- Snapshot before containment and after verification/preview restart.

Receipts:

- `.local/booking-staff-containment-before.json`
- `.local/booking-staff-containment-after.json`
- `.local/booking-staff-containment-fingerprint-result.json`
- Existing serializer: `.local/payment-containment-snapshot.php`

No existing payments, Bookings, orders, refunds, Wallet balances, payout rows,
payables, settings or provider records were seeded or changed.

## 9. Exact files changed

Application source — **one file**:

- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/BookingController.php`

New isolated verification files:

- `.migration-backup/backend/tests/Hardening/BookingStaffFixture.php`
- `.migration-backup/backend/tests/Hardening/BookingStaffAuthorizationTest.php`

Documentation/approval notes:

- `docs/development/payment-booking-staff-p0-containment.md`
- `docs/development/payment-system-full-audit.md`
- `.agents/memory/modernization-approval.md`
- `.agents/memory/MEMORY.md`

The uploaded approval attachment was read, not edited. Local generated receipts
are listed above. No policy/service/schema/provider/UI/configuration file was
changed.

## 10. Outstanding concerns and mandatory stop

This contains the confirmed operational Staff Booking financial-target defect
and the bounded adjacent priced-extra-time/deletion paths. It is **not** proof
that every Booking/payment endpoint is safe.

Still outstanding and not remediated:

- Refund-detail read boundary.
- Alternate Seller non-cash manual-status channel.
- Unverified process-token/payable binding concern.
- Deferred notes/time authorization concerns and unfinished audit coverage.
- All remaining provider/merchant-direct, custody/refund/payout and concurrent
  callback certification questions.

No other P0 is asserted as resolved without evidence. Provider activation,
credentials, MTN/Orange/Flutterwave/Paystack, merchant-direct toggles, Cash,
Wallet/commission/refund/payout economics, UI, Service scheduling algorithms,
Driver/Pickup and Footer remain untouched. Nothing was published.

**STOPPED after approved containment and focused verification.
The broader read-only payment audit remains PAUSED. Await explicit approval.**