# Payment P0 authorization containment — completed, audit still paused

Date: 2026-10-02, America/Chicago.

**Status: minimum approved containment applied and focused verification passed.
The broader payment audit remains incomplete and has not resumed.**

This report supersedes the current-authorization conclusion in the original
`payment-system-full-audit.md` stop report. That original report is retained as
pre-containment evidence. No existing financial transaction was used to execute
an exploit or prove the fix.

## 1. Root cause and before/after behavior

Affected route: `PUT /api/v1/payments/{type}/{id}/transactions`.
The `{id}` identifies a payable, not a transaction primary key. The controller
selects that payable's native root transaction; Booking and several other
payables retain their existing newest-transaction selection.

**Before:** Sanctum authenticated the actor. The controller accepted native
`admin` or `seller`, resolved the payable globally, and allowed cash status
changes without checking its owning Shop or a payment-management grant.
Request validation accepted `paid` and `canceled`. A Seller role alone was
sufficient to reach another Shop's cash mutation and its TransactionObserver.

**After:** The existing role gate remains. For non-Admin Sellers, before
transaction mutation or process-token deletion:

1. The URL type must explicitly match a supported Shop-owned payable class.
2. Ownership resolves from the persisted payable/related business record.
3. The owning Shop must exist and have an owner.
4. The actor must be that Shop's persisted owner or have an accepted invitation
   with a Shop role belonging to that same Shop.
5. Native `User::hasShopPermission()` must grant the existing
   **`payments.payouts.manage`** permission.

This key is deliberately reused from the existing Seller transaction-update
route (`POST dashboard/seller/transactions/{id}`), which invokes the native
transaction status-update service. No new permission, role, ownership model or
permission seeding was introduced despite the key's payout-oriented name.

Unauthorized/unresolvable Seller targets return the endpoint's existing
non-disclosing **404** denial contract. Unauthenticated requests remain **401**.
Caller-supplied Shop/vendor/seller/owner/order/booking IDs cannot influence the
policy; the policy receives only the server-resolved payable and authenticated
actor.

## 2. Canonical ownership and every accepted context

| URL type | Persisted ownership path | Seller outcome |
| --- | --- | --- |
| `order` | Transaction → Order → `Order.shop_id` → Shop | Requires native Shop relationship and payment grant |
| `booking` | Transaction → Booking → `Booking.shop_id` → Shop | Same requirements; a linked Service must exist and agree with the Booking's Shop |
| `subscription` | Transaction → ShopSubscription → Shop | Same requirements |
| `ads`, `ads-package` | Transaction → ShopAdsPackage → Shop | Both existing aliases retained with the same requirements |
| `member-ship` | Transaction → UserMemberShip → MemberShip → Shop | Same requirements; missing membership definition fails closed |
| `gift-cart` | Transaction → UserGiftCart → GiftCart → Shop | Same requirements; missing gift definition fails closed |
| `wallet` | User Wallet, no canonical Shop relationship | Seller denial; no fabricated Shop ownership |
| `parcel-order` | ParcelOrder, no canonical Shop relationship | Seller denial; no invented delivery-business ownership |
| Unknown type/default-to-Order alias | No explicitly supported Seller type | Seller denial, even when an Order with that ID exists |

Booking's persisted `shop_id` is the canonical business attribution, as already
used by payment context resolution, financial accounting and Seller transaction
queries. It is not replaced by a request field or solely inferred from Service.
Where `service_id` is populated, a missing Service or conflicting Service Shop
fails closed as inconsistent data. An otherwise-valid native Booking without an
optional Service link still authorizes through its persisted Shop.

Missing payable, missing Shop, null Shop ownership, missing linked definition,
foreign Shop role, unaccepted invitation, ungranted/revoked permission and
unsupported type all fail before mutation. No Seller role fallback is used.

## 3. Legitimate Seller and Admin behavior

- Native Shop owners retain automatic full Shop permissions. An owner without
  a role-permission row is **not** a missing-grant case under native policy.
- Accepted Seller-role staff with the payment grant can update that Shop's
  otherwise-valid cash transaction. Same-Shop staff holding only
  `payments.view`, pending staff, and staff whose payment grant was revoked
  are denied. Global Seller status is insufficient.
- Seller cash destinations remain exactly `paid` and `canceled`. No new
  destination or additional role authority was granted.
- Existing non-cash Seller denial, Admin reason requirement and process
  requirement remain unchanged.
- The native endpoint has destination validation, not a general
  source-status state machine. Its existing cash `paid` → `canceled` behavior
  remains allowed; inventing new terminal-state restrictions would exceed this
  authorization patch. Other destinations still fail validation with **422**.
- Native `admin` accounts bypass the new Seller Shop-membership/grant policy,
  retaining existing model country scopes and override checks. An Admin with
  no Seller Shop can still perform its legitimate native operation. An
  out-of-country target remains unavailable to a country-restricted Admin.
- Manager-only accounts, which were not admitted by this endpoint's existing
  `admin`/`seller` role gate, were not newly admitted.

Cash activation, checkout eligibility, fees, economics and accounting code were
not changed or disabled.

## 4. Narrow sibling inspection and minimum additional containment

| Sibling | Finding/action |
| --- | --- |
| Seller `POST transactions/{id}` | Existing payment grant middleware and service-supplied, actor-Shop-scoped `whereHasMorph` query for Order/Booking/membership/gift targets. No same cross-Shop lookup assumption; unchanged. |
| Admin `POST transactions/{id}` | Existing Admin route/authority and model country restrictions; not a Seller-only bypass. Unchanged. |
| Seller Order status update | Controller loads the target with `where('shop_id', $this->shop->id)` before native lifecycle processing. Unchanged. |
| Seller Booking update/status paths | Shared service includes actor/assignment checks with different Customer/Staff/Admin lifecycle semantics. Concerns below were recorded, not converted into a broader Booking authorization rewrite. |
| Seller refund update | **Additional source-confirmed cross-Shop financial mutation defect contained.** |

The additional vulnerable route was:
`PUT /api/v1/dashboard/seller/order-refunds/{orderRefund}`.

Its old guard invoked `$orderRefund->order->where('shop_id', ...)->first()`.
Calling `where()` on that Order instance starts a fresh Order query; it does not
constrain the query to the refund's actual Order. Any Order in Shop A could
therefore authorize Shop B's refund. The native refund service can alter payment
and wallet/accounting state, making this an immediately relevant sibling
financial mutation boundary.

The replacement checks the refund's actual persisted Order, requires that
Order's `shop_id` to equal the existing Seller controller Shop, and applies the
same `PayableShopAuthorization` with **`payments.refunds.manage`**. Missing Order
fails closed. Existing route grant middleware is retained; refund processing,
amounts, payouts and wallet behavior are untouched. Authorized owners, granted
staff and legitimate Admin operations within this existing Shop-scoped Seller
route still reach the unchanged service. No broader global Admin refund
authority is introduced.

## 5. Focused verification and accounting safety

All mutation verification ran in process-local, in-memory SQLite fixtures via
the isolated hardening bootstrap: no native application boot, development
database connection, migrations, seeders, real provider transport, credentials
or live financial requests. The test factory prevents stray external HTTP calls.

The status suite dispatches a real isolated Laravel route using the native
authentication middleware, native TransactionUpdateRequest validation and real
controller action. Authenticated roles are synthetic guard/User contexts.
Only unrelated constructor default-language/currency lookups are bypassed.

The real TransactionObserver is registered. Positive owned Order/Booking
`paid` cases create the expected synthetic fee entries (and Booking payable
entry where its collection snapshot requires it), proving the observer is
active rather than mocked away. Each denied request compares fingerprints of
**every isolated table** and the Transaction updated-event count. Denials produce
no transaction update event, ledger entry, process deletion, financial state
change or sentinel vendor/wallet/payout change.

Refund denial tests use the real refund controller and persisted foreign Order,
but prohibit any call to its refund-processing service. All isolated table
fingerprints remain unchanged. Authorized refund cases prove service delegation
with a spy; they do **not** certify the complete refund lifecycle.

Coverage includes both cash destinations for Product and Booking, forged
ownership IDs and process token, unauthenticated/other-role denial,
granted/ungranted/pending/revoked/foreign-role staff, missing/inconsistent
relationships, unsupported types, other supported payable relationships,
global and country-scoped Admin behavior, invalid destinations, existing
non-cash restrictions and native cash cancellation behavior.

Final focused command (from `.migration-backup/backend`):

```sh
php vendor/bin/phpunit -c phpunit-hardening.xml \
  --filter '(PaymentStatusAuthorizationTest|PaymentRefundAuthorizationTest|TransactionObserverHardeningTest)'
```

**Passed: 27 tests, 286 assertions.** Includes the two existing
TransactionObserver hardening cases, plus 25 new authorization cases.
All six changed/new PHP application/fixture/test files passed syntax checks.

Laravel preview restarted once after the coherent code batch and started
cleanly. A deliberately unauthenticated request to a nonexistent payable on the
running native endpoint returned **401 / ERROR_100**. No authenticated status
mutation was performed against the preview database. Customer and Admin
previews were not changed or restarted for this security task.

The broad hardening suite was **not rerun**. Its pre-existing Driver/Pickup
failures remain unchanged and no whole-project green result is claimed.

## 6. Existing financial-record protection

Before/after snapshots use the original audit methodology: read-only PDO,
`PRAGMA query_only=ON`, associative rows, identical JSON serialization,
byte-sorted encoded rows, newline joining and SHA-256.

- **53 protected tables: identical table sets, counts and fingerprints.**
- The original 52-table selector was retained and
  **`platform_fee_ledger_entries` was explicitly added**.
- **Fee-ledger count and SHA-256 fingerprint are unchanged.**
- No protected table changed. No existing financial record was modified.

Receipts:

- `.local/payment-containment-snapshot.php`
- `.local/payment-containment-before.json`
- `.local/payment-containment-after.json`
- `.local/payment-containment-fingerprint-result.json`
- `.local/payment-containment-tests.txt`

No credential values, financial row contents or owner identities are included
in this report.

## 7. Incidental findings — documented, not remediated

These are source observations, not a resumed payment audit or production
exploit assessment:

1. **P1, source-confirmed refund-read boundary:** Seller
   `OrderRefundsController::show` retains the same instance-to-unscoped-query
   pattern. This is a disclosure concern rather than the approved sibling
   mutation containment. It was not exercised against existing records or
   patched in this mutation-only pass.
2. **P1, source-confirmed alternate non-cash manual-status channel:** Seller
   transaction-update service, despite its correct Shop scoping, does not apply
   the general endpoint's non-cash Seller prohibition/Admin reason-and-evidence
   contract. Provider override policy requires separate audit; it was not
   changed here.
3. **Additional source concerns, impact not tested:** optional process tokens
   in the general endpoint are globally looked up/deleted without an explicit
   payable-evidence binding. Booking lifecycle assignment checks also have
   different role fallthrough semantics for Staff than Seller/Customer/Master.
   These require context-specific audit; the shared lifecycle/refund/callback
   architecture was not rewritten.

The additional cross-Shop refund **mutation** defect found in this pass is
contained as described above. Remaining concerns mean this report is not a
general certification of safe financial operations or provider readiness.

## 8. Exact application/test files changed

All paths below are under `.migration-backup/backend/`:

- `app/Policies/PayableShopAuthorization.php` — new shared persisted-Shop policy.
- `app/Http/Controllers/API/v1/Dashboard/Payment/TransactionController.php` —
  Seller policy gate before mutation; existing Admin/state rules retained.
- `app/Http/Controllers/API/v1/Dashboard/Seller/OrderRefundsController.php` —
  actual refund Order ownership/grant guard on update only.
- `tests/Hardening/PaymentStatusFixture.php` — isolated schema/router/guard,
  real observer and complete-state fingerprint fixture.
- `tests/Hardening/PaymentStatusAuthorizationTest.php` — focused status matrix.
- `tests/Hardening/PaymentRefundAuthorizationTest.php` — sibling refund boundary.

Supporting changes are this report, a historical-stop-report status annotation,
local fingerprint/test receipts and approval/isolated-fixture memory maintenance.
No Footer, Customer, Vendor UI, Driver, Pickup, provider, accounting, wallet or
refund-processing implementation file changed.

**STOPPED after containment and focused verification. No provider activation,
publication, merchant-direct remediation, payment phase or broader audit started.
Await explicit approval before resuming the READ-ONLY payment audit.**