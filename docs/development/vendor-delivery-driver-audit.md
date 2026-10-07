# AgendaAlly — Delivery Driver audit and architecture recommendation

## Scope and method

Source inspection only. No delivery records, accounts, migrations, roles, APIs,
fees, status rules, portal screens or mobile code were changed. No live
authorization exploit or delivery transaction was attempted.

This report distinguishes current behavior from proposed behavior. “Delivery
Driver” is the recommended terminology; existing `deliveryman`, `DeliveryMan`
and “Deliveryman” identifiers remain documented as legacy contracts.

Source prefixes used below:

- **B:** `.migration-backup/backend/`
- **A:** `.migration-backup/admin/`
- **W:** `.migration-backup/web/`
- **C:** `.migration-backup/customer_app/`

Line references describe the inspected source, not runtime acceptance results.
The Customer Flutter source is available; the Delivery mobile app is not yet
uploaded. An existing API is not evidence that its mobile consumer works.

## A. Executive summary

There is an existing delivery subsystem worth extending: global driver users,
shop invitations, driver settings, order assignment, driver APIs, location
events, customer visibility and partner-payment plumbing.

Vendors can provision driver accounts through the active legacy invitation
management screen. This requires the Vendor to choose the driver's password
and immediately creates an accepted shop association. It is **not** the desired
hybrid invitation/self-registration workflow.

Vendor assignment is **partially wired**, not working end-to-end by contract:
the modal sends `deliveryman`, while the API requires `deliveryman_id`.
The API correctly scopes the order to the Vendor's shop, but does not validate
the chosen driver's accepted, active relationship with that shop.

Important authorization/privacy issues remain. Report them and obtain approval
before implementing Phase 0 or any later phase. Do not fix the assignment
payload alone while leaving the authorization boundary unresolved.

## B. Current architecture diagram

```text
Vendor: Business / invitation management / deliverymen
  → reusable Deliveryman list + account-creation form
  → dashboard/seller/users and shop/users/role/deliveryman
  → Seller UserController → UserService / UserRepository
  → users + global deliveryman role + accepted invitations

Vendor order board → OrderDeliveryman modal
  → POST dashboard/seller/order/{id}/deliveryman
  → Seller OrderController → OrderService::updateDeliveryMan
  → own-shop order lookup → global driver-role check
  → orders.deliveryman_id → push notification
  [UI/API payload mismatch; no accepted-shop-membership check]

Driver: Sanctum token + global deliveryman role
  → dashboard/deliveryman/orders, settings, status and attach/me
  → Driver controllers → OrderRepository / OrderService
  → own assignments OR some unassigned-order access
  → shared order-status service → events/customer/Vendor notifications

Customer Web / Customer Flutter
  → order resources → order status, assigned driver/contact and location
```

Evidence: `B/routes/api.php:496-524,627-637,693`; Seller
`UserController.php:62-94,169-180`; `B/app/Services/UserServices/UserService.php:34-76`;
`B/app/Services/OrderService/OrderService.php:382-429`;
`A/src/views/seller-views/order/orderDeliveryman.jsx:22-49`.

## C. Existing models and tables

| Structure | Current purpose and limitation |
|---|---|
| `users` + Spatie roles | Global identities; `deliveryman` is a role, not a separate driver account. |
| `invitations` | Existing user/shop association with role, status and timestamps. Requires an existing `user_id`; no contact-only invitation, acceptance token or expiry in the reviewed schema. |
| `deliveryman_settings` | Per-user vehicle, online and location settings; not a shop-owned roster. |
| `shop_deliveryman_settings` | Shop delivery configuration/pricing type/value/period; not driver membership. |
| `orders` | Own `shop_id`, nullable `deliveryman_id` pointing to users; one current assignee. |
| `parcel_orders` | Separate parcel subsystem with driver assignment; not interchangeable with product orders. |
| `payment_to_partners`, wallets/history and payouts | Existing delivery economics infrastructure, not proof of a complete Vendor payroll/COD design. |

Evidence: `B/app/Models/User.php:378-383,461-484`; `DeliveryManSetting.php:56-105`;
`ShopDeliverymanSetting.php:28-35`; `Order.php:310-318`;
`B/database/migrations/2022_04_15_020426_create_invitations_table.php:16-29`;
`2023_12_07_064250_remigrate_orders_table.php:48,67`;
`2023_08_20_044222_create_parcel_orders_table.php:49`.

Multiple invitation rows are possible, but some authorization checks use the
singular `User::invite()` relation. This is not reliable multi-shop membership.
Deleting a user nulls the order assignment foreign key; that is not a managed
driver-offboarding workflow.

## D. Roles and permissions

- Driver endpoints use Sanctum checking and global `role:deliveryman`.
- Seller routes admit `seller|moderator|admin|shop_manager`, with the current
  shop resolved by the native seller middleware/base controller.
- Driver list/read endpoints are protected by `shop.permission:staff.view`.
- Seller user create/update/global-active toggles are protected by
  `shop.permission:staff.invite`. These are **not unguarded**.
- Assignment and separate deliveryman-settings routes lack an explicit
  equivalent fine-grained driver-management/assignment permission wrapper.
- Existing `orders.delivery_settings` permission concerns delivery pricing and
  settings; its presence alone does not secure every delivery route.

Evidence: `B/routes/api.php:496,529,627-637,693,798-800,905-906`;
`B/database/seeders/ShopPermissionSeeder.php:50-53`;
`B/app/Http/Middleware/CheckSellerShop.php`;
`B/app/Http/Controllers/API/v1/Dashboard/Deliveryman/DeliverymanBaseController.php:14-17`.

## E. Current Vendor delivery-management capability

| Question | Source-supported answer |
|---|---|
| Can Vendor add drivers? | Yes: directly creates a global user with driver role and accepted shop invitation. |
| Can Staff manage them? | Where admitted to seller routes, list/read uses `staff.view`; user create/update/active changes use `staff.invite`. Assignment/settings need stronger dedicated permission enforcement. |
| Admin-only creation? | No. Vendor account creation exists; Admin has broader user/settings operations. |
| Global or shop-owned? | Global user; shop relationship is invitation-based. |
| Ownership field? | `invitations.shop_id` and `user_id`, not a driver's own `shop_id`. |
| Multiple shops? | Structurally multiple invitations; singular-invite checks and global account status make safe operational multi-shop ownership incomplete. |
| Access representation? | Global driver role, user account, invitations, optional driver setting and existing shop permissions. |
| Authentication? | Native account authentication/Sanctum token; driver endpoints require the role. |
| Who chooses credentials? | Legacy Vendor Add form collects password and confirmation. Backend permits omission and uses a predictable fallback. |
| Invitations/registration? | Generic invitation to an existing user exists, including acceptance. Contact-only invitation plus new-user registration/acceptance does not. |
| Disable/remove? | Global user active-status toggle exists; no clean shop-only membership suspension/removal lifecycle. |
| Assigned orders after disable? | No automatic unassignment/reassignment workflow found. Existing assignment persists; do not assume token revocation or continued-access behavior is safely handled. |

Evidence: `A/src/views/seller-views/deliverymen/user-form.jsx:39-47,90-190`;
`B/app/Http/Requests/UserCreateRequest.php:17-61`;
`B/app/Services/UserServices/UserService.php:34-76`;
`B/app/Http/Controllers/API/v1/Dashboard/Seller/UserController.php:62-94,169-180`;
`B/app/Models/User.php:378-383,623-635`.

The picker can include accounts without invitations, and accepted status is
not mandatory unless the corresponding filter is supplied. Frontend filtering
is not authoritative assignment authorization.

## F. Current Admin capability

Admin has global driver listing/settings and order-assignment endpoints.
Broad order scope is intentional for authorized Admin operations; it is not a
Vendor tenant leak. Driver account provisioning remains the normal user
architecture rather than a dedicated secure driver invitation subsystem.

Evidence: `B/routes/api.php:1106,1258-1264`;
`A/src/services/order.js:18`; `A/src/services/delivery.js:6-18`.

## G. Current Delivery Driver capability

Authenticated drivers can access order lists/details, permitted status updates,
reviews/current flags, settings/online/location, statistics/reporting, parcel
operations, payouts and partner-payment reads.

Ordinary order listing injects the authenticated driver ID. However,
`empty-deliveryman` removes that scope; detail permits self-assigned **or
unassigned** orders. Another driver's assigned order is excluded by that
repository query, and status updates explicitly require the caller's assignment.
Self-claim of an unassigned delivery is supported without shop-membership checks.

The list controller also forces `type = Order::IN_HOUSE`; assigned orders typed
`SELLER` are not included by that list, although assignment does not impose
that type restriction. Do not assume numeric order type, shop delivery setting
and fulfillment type are interchangeable. Their mapping must be reconciled for
Vendor delivery before reusing this listing in the future app.

Evidence: `B/routes/api.php:496-524`;
`B/app/Http/Controllers/API/v1/Dashboard/Deliveryman/OrderController.php:30-116`;
`B/app/Repositories/OrderRepository/DeliveryMan/OrderRepository.php:25-64`;
`B/app/Services/OrderService/OrderService.php:448-474`.

## H. Current order-assignment workflow

| Question | Current answer |
|---|---|
| Who assigns? | Seller-group actors through own-shop endpoint; authorized Admin through Admin endpoint. |
| Staff assignment? | Broad seller-role/shop access; no dedicated assignment permission wrapper at the route. |
| Which fulfillment? | Service requires `Order::DELIVERY` (`delivery`), not pickup (`point`) or digital. |
| Which status? | Assignment method has no order-status gate. |
| Change assignment? | Yes, API overwrites `deliveryman_id`; no assignment-history model identified. |
| Unassign/reject? | Assignment request requires a driver ID; no explicit null-unassign or driver rejection workflow identified. |
| Admin override? | Broad Admin assignment exists. |
| Vendor A assign B's order? | **No through this endpoint:** controller passes current shop ID and service scopes the lookup. |
| Vendor A assign B's driver? | **Yes by source enforcement:** selected account needs driver role, not this shop's accepted membership. |
| Driver A access B's assignment? | Own-assigned detail/status restrictions exist; unassigned list/detail/self-claim is the separate exposure. |

Assignment persists the foreign key and sends push notification. The Vendor
modal sends `{deliveryman: ...}`; request/controller require
`deliveryman_id`. Consequently the normal modal does not satisfy the backend
contract. Its required field also provides no unassign action.

Evidence: `A/src/views/seller-views/order/orderDeliveryman.jsx:22-31,66-84`;
`A/src/services/seller/order.js:11-14`;
`B/app/Http/Requests/Order/DeliveryManUpdateRequest.php:16-24`;
Seller `OrderController.php:214-216`;
`B/app/Services/OrderService/OrderService.php:382-429`.

## I. Native status lifecycle and visibility

Native order values are `new`, `accepted`, `ready`, `on_a_way`, `pause`,
`delivered`, `canceled` (`B/app/Models/Order.php:134-150`). These are order
statuses, not a separate assignment state machine.

- Driver may request `ready`, `on_a_way`, `pause`, `delivered` on own assignments.
  This whitelist is not proof of complete predecessor-state/transition validation.
- Seller/Admin use shared order-status operations; Vendor can override through
  its own-shop status endpoint.
- Shared status service updates the same Customer-visible order and invokes
  events/notifications. Terminal behavior includes the existing current flag.
- No dedicated driver acceptance/rejection/pickup state, proof photo/signature,
  verified delivery OTP or dedicated delivery milestones was established.
- Order OTP is generated/serialized, but no verification consumer was found:
  its existence is **not** proof of delivery.
- Driver location/online settings and assigned-order location events exist;
  these are not evidence of an operational uploaded Delivery app.

Evidence: driver `OrderController.php:78-116`; seller `OrderController.php:131-148`;
`B/app/Services/OrderService/OrderStatusUpdateService.php:50-172`;
`B/app/Services/DeliveryManSettingService/DeliveryManSettingService.php:108-147`;
`B/app/Listeners/Order/SendDeliveryManLocationByOrderListener.php:46-69`;
`B/app/Http/Resources/OrderResource.php:68`.

## J. Delivery economics — separate, audit only

| Concept | What exists / what is not established |
|---|---|
| Customer delivery fee | Configured shop delivery calculation and order `delivery_fee`; parcel fee is distance-derived. |
| Platform vs Vendor delivery | Shop constants distinguish in-house `1` and seller `2`; order's `type` is separate from fulfillment `delivery_type = delivery/point/digital`. |
| Shop delivery pricing | Shop delivery settings/pricing UI and backend structures exist. |
| Driver settlement | `PaymentToPartner::DELIVERYMAN` and fee-priced partner records/transactions; wallet/cash tags and wallet histories exist. |
| Driver wallet/payout | Legacy endpoints/plumbing exist; not evidence of complete Vendor employee payroll. |
| Commission | Order resources expose commission/financial fields; no newly recommended earnings formula or proven driver-specific commission model. |
| Cash/COD | Cash payment tags exist; no complete driver cash custody/COD reconciliation workflow established. |
| Vendor payout/platform settlement | Separate existing finance concerns; driver assignment does not establish their correctness or authorize changes. |

Evidence: `B/app/Models/Shop.php:182-192`; `Order.php:131-166`;
`B/app/Services/OrderService/OrderService.php:240-245,295-308`;
`B/app/Services/ParcelOrderService/ParcelOrderService.php:278-295`;
`B/app/Models/PaymentToPartner.php:42-46`;
`B/app/Services/PaymentToPartnerService/PaymentToPartnerService.php:254-290`;
`B/routes/api.php:510-524`.

## K. Security and tenant-isolation findings

No live data was exploited. Priority is based on source-visible exposure.

| Priority | Finding | Required approved correction |
|---|---|---|
| Critical | User provisioning accepts omitted password and creates a predictable fallback credential; seller creation also treats supplied contact as verified. | Remove unsafe provisioning defaults; require genuine self-registration/contact verification or secure approved provisioning. |
| High | Assignment accepts any global driver-role account; no accepted/active shop membership or active-account check. | Validate driver relationship and account eligibility server-side; retain existing own-shop order scope. |
| High | Seller settings update finds arbitrary setting ID, validates the **submitted replacement user**, then updates without checking the setting's current owner/shop. | Scope the existing setting before any update; do not allow ownership reassignment to authorize a foreign row. |
| High | Driver APIs expose/claim unassigned orders across shops. | Decide whether legacy platform dispatch is intentional; explicitly authorize its pool separately. Vendor-owned deliveries must never be globally enumerable/claimable. |
| Medium | Singular invitation authorization is ambiguous with multiple shop invitations; global active toggle is not shop-only offboarding. | Authoritative accepted membership queries and per-shop membership status. |
| Medium | Assignment/settings lack dedicated staff permission wrappers. | Reuse shop permission architecture; enforce explicit management/assignment grants. |
| Medium | Driver resources load customer/account and financial relations wider than fulfillment needs. | Driver-specific least-privilege projection and completion-time privacy restrictions. |

Evidence: `B/app/Services/UserServices/UserService.php:34-76`;
Seller `UserController.php:62-94`; `OrderService.php:382-474`;
Seller `DeliveryManSettingController.php:104-125`;
driver `OrderRepository.php:25-64`; `B/routes/api.php:627-637,693,905-906`;
`B/app/Http/Resources/OrderResource.php:50-72`.

Do **not** report Vendor cross-order assignment as a vulnerability: that order
lookup is scoped. Do **not** describe permission-protected driver provisioning
routes as unguarded. Do not equate loaded payment relations with a proven
credential leak; financial/account overexposure is the established concern.

## L. Existing API inventory

All paths below are under **`/api/v1/dashboard/`**, not `/api/v1/seller/`.

| Actor | Existing paths/methods |
|---|---|
| Seller | `GET shop/users/role/deliveryman`, `POST users`, `PUT users/{user}`, `POST users/{uuid}/change/status`; `POST order/{id}/deliveryman`; REST `deliveryman-settings`, `shop-deliveryman-settings` and bulk-delete routes. |
| Seller invitations | Existing-user lookup/create and generic invitation APIs; current delivery Add does not use a new-user invitation flow. |
| User invitation | `POST shop/invitation/{id}/status/change`; accepted role is appended to existing roles. |
| Admin | `GET deliveryman/paginate`, `GET deliveryman-settings/paginate`, settings REST operations, order assignment and shop delivery settings. |
| Driver | `GET orders/paginate`, `GET orders/{id}`, `POST order/{id}/status/update`, `POST orders/{id}/review`, `POST orders/{id}/current`, `POST order/{id}/attach/me`. |
| Driver profile/operations | `GET/POST settings`, `POST settings/location`, `POST settings/online`, `GET statistics/count`, `GET order/report`. |
| Driver parcel/finance | Parcel list/status/review/current/attach routes; payouts REST/bulk delete; partner-payment index/show. Keep separate contracts. |

Actor prefixes are `seller/`, `admin/`, `deliveryman/`, or `user/` respectively.
Evidence: `B/routes/api.php:334-337,496-524,627-637,693,798-800,905-906,1106,1258-1264`;
Seller `InviteController.php:35-68,94-114`.

## M. Current Admin/Vendor/Customer UI inventory

- Active Vendor route: `/seller/invitations/deliverymen`, under native BUSINESS
  invitation management (`staff.view`), reusing legacy Deliveryman list/add.
- `/seller/deliverymen` routes and standalone menu entry are commented out;
  route-definition files alone do not prove current navigation.
- Add/invitation-status actions are UI-gated by shop `delivery_type === 2`.
- Order board has the assignment modal, but the payload mismatch blocks its
  contract and its initial numeric selection differs from label/value options.
- Vendor delivery-price screens and Admin global driver/settings services exist.
- Customer Web order detail and Customer Flutter order screen expose assigned
  driver/contact information.

Evidence: `A/src/routes/index.js:212,221-223`;
`A/src/routes/seller/invitations.js:19-30`;
`A/src/configs/menu-config.js:1867-1884,1976-1981`;
`A/src/configs/navigation.mjs:13-30,497-504`;
`A/src/views/seller-views/deliverymen/index.jsx:123-139,240-249`;
`A/src/views/seller-views/order/orders-board.jsx:348-353`;
`W/components/order-detail/order-detail-collapse.tsx:270-305`;
`C/lib/presentation/pages/order/widgets/order_bottom.dart:133-153`.

## N. Future Delivery-app compatibility

Preserve `/api/v1/dashboard/deliveryman`, `deliveryman_id`, the existing
`deliveryman` resource field and native status strings during migration.
Security containment can intentionally restrict unauthorized access, but do
not silently repurpose legacy platform dispatch as Vendor delivery.

When the app is uploaded, inventory its actual auth, payloads, pagination,
status assumptions, notification registration and errors before refactoring.
Publish additive capabilities/projections or an explicitly versioned contract;
keep legacy fields where safe. Customer Flutter is not the missing Driver app.

## O. Gap matrix

| Classification | Current evidence |
|---|---|
| Works already at source level | Global driver auth/role, order FK persistence, notification invocation, assigned-driver status ownership, location events. |
| Partially wired | Invitation-backed relationship, existing-user invitation acceptance, Vendor provisioning/settings, order modal/service integration. |
| UI-only/legacy-disabled | Standalone `/seller/deliverymen` route/menu disabled; current invitation routes reuse its components. |
| Missing backend contract | Contact-only expiring invite + registration acceptance; explicit reject/unassign and proof workflow. |
| Authorization/security issue | Driver-membership validation, foreign-setting update, unassigned exposure, provisioning defaults and privacy projection. |
| Model limitation | Singular invite checks, global account status, no assignment history or explicit driver membership lifecycle; driver list forces in-house order type. |
| Future mobile requirement | Uploaded-app contract review, assigned-only DTO, allowed transitions, contact/privacy, notifications and safe retry behavior. |

“Works” here means the source implements the operation, not that delivery
transactions or mobile acceptance tests were run.

## P. Recommended target architecture

Reuse **User + existing driver role/settings + shop invitations/permissions +
Order.deliveryman_id**. Add an authoritative shop-driver membership capability,
not a competing account/order/delivery subsystem. Membership acceptance and
active status must authorize Vendor operations, while the global role is only
an entry capability. Keep intentional platform-dispatch permissions separate.

Support multiple shops through explicit independent memberships if approved;
otherwise enforce one active shop. Do not let an arbitrary singular invitation
decide access. Reuse native identity/authentication and preserve existing roles.

## Q. Hybrid invitation and registration

Recommended: Vendor with permission invites verified email/phone; an existing
user signs in and accepts, or a new user registers/verifies identity and accepts.
Vendor never knows or creates the password.

Reuse generic invitation acceptance where safe. Add contact-only pending
invitations, hashed single-use tokens, expiry, cancellation/revocation,
deduplication and verified-recipient binding. Activate membership only after
acceptance; append required capability without replacing customer/staff roles.
Pending invitations must not grant driver APIs or platform dispatch access.

## R. Proposed Vendor portal workflow

Native BUSINESS → **Delivery Drivers** → **Invite Delivery Driver**.
Roster columns: name, permitted contact, membership status, active/completed
delivery counts and actions; last activity only if legitimately collected.
Suggested future membership states: Invited, Active, Suspended, Inactive.
These are proposals, **not current driver-model states**.

Use existing approved Business styling and permission-aware navigation. Keep
global account administration separate from suspension at this Vendor.

## S. Proposed assignment workflow

Own eligible product order → Vendor delivery → select own accepted active
driver → Assign → assignment notification → driver-authorized retrieval →
permitted updates → Customer/Vendor visibility.

Check ownership, fulfillment policy, status, account/membership eligibility and
staff grant on the server. Persist assignment atomically; protect against
simultaneous claims/reassignments. Audit old/new assignee and actor. Change or
unassign only at approved stages; never silently orphan an active delivery.

## T. Driver authorization and privacy

List/detail/update queries must require the current authenticated assignee and
authorized active membership for Vendor deliveries. Keep any platform pool
behind its separately approved dispatch grant and minimal pre-acceptance data.
Reject cross-driver, cross-shop and expired/suspended access server-side.

Expose only necessary package details, delivery address/instructions, suitable
navigation coordinates and contact mechanism. Exclude unrelated account data,
transaction/provider internals and Vendor secrets. On completion, restrict
history/contact and stop/expire live location sharing according to policy.

## U. Future mobile API contract

| Capability | Reuse / additive work |
|---|---|
| Authentication/profile | Reuse native auth and settings; add authorized shop memberships/capabilities. |
| Assigned deliveries/detail | Reuse order routes with secure queries, pagination and driver-specific DTO; reconcile the current forced in-house type filter with approved Vendor fulfillment. |
| Accept/reject | Add only after approved lifecycle; do not reinterpret order `accepted` as assignment acceptance. |
| Pickup/progress/completion | Reuse native status values where semantically correct; document allowed predecessor transitions, retry/idempotency and concurrency. |
| Address/contact/navigation | Least-privilege projection, legitimate coordinates, consent/retention rules. |
| Proof of delivery | Add only after business approval; existing order OTP is not a verified proof contract. |
| Notifications/location | Reuse events/settings; specify device registration, delivery retries and active-assignment location scope. |
| History | Restricted completed-delivery projection, not unrestricted current customer data. |
| Earnings | Expose existing approved economics only; no invented Vendor earnings/COD model. |

Document error codes for unauthorized membership, expired invite, reassignment,
invalid transition and duplicate submission. Validate actual app compatibility
after upload before deprecating legacy fields/endpoints.

## V. Minimal additive data-model recommendation

1. Prefer extending the existing invitation/membership architecture for
   accepted per-shop driver capability, unique shop/user/role association and
   membership status. Reuse branch scope if delivery needs it.
2. If generic invitations cannot safely represent membership lifecycle, add a
   narrowly scoped shop-driver membership referencing existing users/shops;
   do not duplicate identities, settings or orders.
3. Add pending-contact invitation/token-hash/expiry fields or a small pending
   invite structure so invitations do not require a fabricated user.
4. Retain `orders.deliveryman_id`; add assignment history/concurrency version
   and operational milestone/proof data only in their approved phases.
5. Separate per-shop suspension from global `users.active`. Do not change
   financial tables or calculate a new earnings model for this foundation.

## W. Ordered implementation phases — proposal only

| Phase | Scope and approval boundary |
|---|---|
| 0 | Contain proven authorization/privacy/provisioning defects; verify tenant queries and existing staff grants. Resolve platform-pool policy before changing intended dispatch. No economics redesign. |
| 1 | Normalize Vendor↔driver accepted membership, active status and multi-shop policy using existing structures first. |
| 2 | Hybrid invitation, verified self-registration/sign-in, secure acceptance and revocation. |
| 3 | Permission-aware native Vendor driver-management UI. |
| 4 | Correct assignment payload together with secure membership/eligibility validation; approved change/unassign/history/concurrency behavior. |
| 5 | Driver-scoped DTO/API contract, privacy and documented transitions; preserve safe `/api/v1` compatibility. |
| 6 | Refactor the Delivery mobile app only after its source is uploaded and audited. |
| 7 | Approved notifications reliability, proof of delivery and operational improvements. |
| 8 | Separately approved driver earnings, COD custody/reconciliation and delivery economics, if needed. |

No phase has started. Implementation and targeted authorization tests require
approval; findings in this report are not permission to mutate live data.

## X. Explicit exclusions and next approval

Do not yet add migrations, drivers, role changes, invitations, APIs, assignment
flows, status values, delivery fees, portal redesign or a mobile application.
Do not modify payments, payouts, wallets, accounting, commission formulas,
refunds, providers or completed booking/Stage 2/invoice/footer/Stories work.

Approve the security containment and membership/onboarding direction before
implementation. Preserve safe native contracts, then validate only the affected
delivery boundaries in an isolated test environment—not live delivery records.