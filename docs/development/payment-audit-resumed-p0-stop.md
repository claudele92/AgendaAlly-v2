# Read-only payment audit — stopped on a newly confirmed P0

Date: 2026-10-02, America/Chicago.

**Status: STOPPED. The full payment audit remains incomplete. No remediation
was performed in this resumed pass.**

## Approved target collection model used

The audit resumed against the creator's approved model, not an assumed new
architecture:

- Super Admin defines country payment policy; the Vendor business country
  determines the applicable methods.
- AgendaAlly/platform collection is the default. Ordinary Vendors do not need
  merchant API credentials to participate.
- Verified platform-collected funds create the appropriate Vendor
  payable/liability, followed by separately authorized payout processing.
- Vendor-direct collection is optional and requires an explicitly chosen,
  supported, country-permitted merchant integration and appropriate credentials.
- Vendor-direct funds must not be treated as platform-held Vendor liability.
- Customer collection credentials and Vendor payout destinations are separate.
- Existing persisted choices and frozen booking/intent collection attribution
  are preserved; no bulk conversion is authorized.

Authority: the approved payment amendment
`attached_assets/Pasted-AGENDAALLY-PAYMENT-ARCHITECTURE-AMENDMENT-CAR-SERVICE-C_1790913210906.txt`,
especially its target flow and collection/payout separation.

The previous P0 transaction-status/refund-update containment remains in place.
The P1 findings from that pass were not remediated.

## Newly confirmed P0: Staff role fallthrough allows cross-Shop Booking financial mutation

**Classification:** source-confirmed authorization defect. No exploit request,
Booking cancellation, wallet operation or ledger mutation was executed.
Production exposure, affected amounts and actual eligible actor/target
availability were not tested.

Affected route:

`POST /api/v1/dashboard/seller/bookings/{id}/status/update`

This is a Booking lifecycle endpoint with financial side effects, not the
previously contained `PUT payments/{type}/{id}/transactions` endpoint.

### Exact authorization chain

All backend paths below are relative to `.migration-backup/backend/`.

| Layer | Current evidence | Consequence |
| --- | --- | --- |
| Route | `routes/api.php:547` admits `seller`, `moderator`, `admin` and `shop_manager`. Lines 901–902 apply `shop.permission:bookings.status` to this route. | Staff roles are intentionally admitted; possession of a grant is not proof of target ownership. |
| Seller-Shop middleware | `app/Http/Kernel.php:102` maps `check.shop` to CheckSellerShop. `app/Http/Middleware/CheckSellerShop.php` admits a moderator/shop_manager with `moderatorShop`. | Establishes an actor-side Shop, not the requested Booking's Shop. |
| Permission middleware | `app/Http/Middleware/CheckShopPermission.php:27–41` resolves `$user->shop ?? $user->moderatorShop` and checks `hasShopPermission` for **that Shop**. | A valid Shop A `bookings.status` grant satisfies the gate even when the requested Booking belongs to Shop B. |
| Request | `app/Http/Requests/Booking/StatusUpdateRequest.php` validates native Booking statuses, including `canceled`. It inherits `BaseRequest::authorize()` returning true. | No target-Shop authorization is supplied by the request. |
| Controller | `app/Http/Controllers/API/v1/Dashboard/Seller/BookingController.php:186–201` forwards the numeric ID and validated status to the service. | No predicate binds the target Booking to the controller's Shop or branch. |
| Service lookup | `app/Services/BookingService/BookingService.php:379–397` loads the Booking by ID, requiring a master and Shop, then invokes `checkAssignedBeforeUpdate`. | Target lookup is not scoped to the actor's Shop. |
| Assignment check | Same service, lines 1065–1087: Admin returns; only `master`, `seller`, and `user` roles have rejection branches. | A Staff actor with `moderator` or `shop_manager` but none of those checked roles falls through without a target check. |

The Booking model's `BelongsToShopCountry` restriction is country-based, not
Shop-based. A target in another Shop in the same permitted country is not
protected by that restriction.

### Preconditions and important limits

- Authenticated **moderator/shop_manager Staff** actor with a valid actor-side
  Shop and its existing **`bookings.status`** grant.
- Actor does **not** also carry `admin`, `master`, `seller` or `user`. Accounts
  carrying those roles take different service branches; no blanket claim is
  made about every Staff account.
- Another Shop's valid Booking with a master and Shop, whose current status
  differs from the requested native destination.
- Any existing country restriction must permit the target; same-country
  cross-Shop targeting is sufficient.
- Particular financial effects require the corresponding Customer wallet,
  paid/collection ledger, point history, totals and cancellation settings.
  Their availability and values were not inspected to manufacture an exploit.

These conditions expose a deterministic authorization gap in a supported route.
An own-Shop grant does not authorize changing another Shop's Booking or its
financial records.

### Financial mutation path

`BookingService::statusUpdate`, lines 401–461, enters a database transaction
**after** the inadequate assignment check:

1. Native Booking activity recording is called; its cancellation branch records
   activity and does not add a target-Shop authorization check.
2. For cancellation, lines 423–445 calculate existing payment/cancellation
   values and may increment/decrement the **target Customer's wallet**, including
   existing point-history reversals.
3. Lines 451–454 change the **foreign Booking's status**.
4. Lines 456–457 invoke `reversePayableForCanceledBooking`.
5. Lines 674–701 select existing platform-payable ledger entries for that
   Booking and create a **negative payable adjustment** using the entry's own
   Shop, transaction, payment and currency attribution.

Thus the missing Staff target check is not only a Booking display/state problem.
It can reach unauthorized wallet/accounting and platform-held Vendor liability
effects under the approved collection model.

The normal same-status rejection and the database transaction do not authorize
the actor. A transaction can atomically commit an unauthorized operation.
Provider disabling also does not remove this local lifecycle/ledger path.

The route does not have to mark a Transaction `paid` for this finding to apply.
No claim is made that an external refund, collection or payout occurred.

## Why the previous containment does not cover this path

The shared persisted-Shop policy was applied to the general Seller payment
status action and Seller refund update. This Booking lifecycle action calls a
different shared assignment routine.

The previous containment report explicitly recorded Booking Staff role
fallthrough as an **unverified source concern**, not a P1 certified as harmless.
The resumed trace now establishes the complete own-Shop-grant → foreign Booking
→ financial-effect path, elevating that concern to a source-confirmed P0.

No earlier fix was reverted or bypassed through the same patched action.
No P1 finding was silently remediated or reclassified merely to expand scope.

## Mandatory stop and decision boundary

The creator required another immediate stop on discovering a P0. Broader
architecture/provider/readiness/current Vendor-decision tracing stopped here.
No application patch, middleware change, endpoint disabling, role/grant change,
provider configuration, preview restart, callback call or live financial request
was performed.

The preview should not be considered safe for financial lifecycle operations by
untrusted Staff under these preconditions. Current production exposure was not
investigated or certified.

A potential containment would need a **target-bound native Shop/grant check in
the Seller operational path**, without imposing Seller membership on legitimate
Admin/Customer/Master lifecycle paths. This is a decision prerequisite only,
not an implemented fix or an automatically proposed remediation phase.

Separate explicit approval is required for any containment. Any later
verification should use isolated fixtures and retain legitimate role, Shop,
branch, country and lifecycle behavior.

## Read-only safety and receipts

- Read-only source tracing only; no status-mutating endpoint, financial service,
  real payment, order, Booking, refund, wallet operation or payout was executed.
- No provider was activated/configured; credentials and environment values were
  not read or printed.
- No application, test, provider, UI, accounting or runtime configuration file
  changed in this resumed pass.
- No test/build/browser loop or workflow restart was performed. Previous
  containment test results are not claimed as verification of this new path.
- Existing Driver/Pickup failures and the closed Footer investigation remained
  untouched.

Before/after snapshots used the established PDO read-only serializer:
`PRAGMA query_only=ON`, associative rows, the same JSON flags, byte-sorted
encoded rows, newline joining and SHA-256.

**All 53 protected tables have identical counts and fingerprints.**
The table sets and serializer match. **`platform_fee_ledger_entries` is included
and its count/fingerprint is unchanged.** No protected financial data changed.

Local receipts:

- `.local/payment-audit-resumed-before.json`
- `.local/payment-audit-resumed-after.json`
- `.local/payment-audit-resumed-fingerprint-result.json`
- Serializer: `.local/payment-containment-snapshot.php`

Only audit documentation, local receipts and approval-boundary memory were
updated. No credential contents, Customer/Vendor identities, financial row
contents or exploit execution are included in this report.

## Unfinished audit deliverables

The full provider inventory/matrix, current merchant-direct radio decision,
complete payout/refund/Wallet/commission custody model, client/Flutter flows,
all requested question answers and ordered full remediation plan remain
incomplete. Prior partial observations are not a complete architecture or
production-readiness certification.

**STOPPED on the newly confirmed P0. Await explicit approval; do not resume the
full audit or perform remediation automatically.**