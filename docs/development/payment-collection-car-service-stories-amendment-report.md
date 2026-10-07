# Payment collection, Car Service, and Stories amendment report

**Status:** bounded implementation and focused verification complete. Native
Customer, Admin/Vendor and Laravel previews are running. Stories remain audit
and proposal only; no later payment phase was started.

This report answers sections A–V of the amendment request, using
`payment-phase-0-foundation-report.md` as the preserved payment baseline. It
records only the bounded payment-collection amendment, Car Service taxonomy
addition, and read-only Stories audit/design proposal. It is not a replacement
for the Phase 0 report or the payment architecture audit.

## A–F. Preserved Phase 0 baseline

Phase 0 security and wiring work remains as documented in
[`payment-phase-0-foundation-report.md`](payment-phase-0-foundation-report.md);
it was not reimplemented or weakened in this amendment.

- **A — Security fixes:** public and authenticated ShopPayment resources expose
  safe identity/status/configuration metadata rather than credential material;
  seller gateway operations are scoped to the actor's authorized shop and
  `payments.gateways.manage` grant; country-payment writes retain scoped or
  global-superadmin authorization; and MTN initiation no longer creates a
  missing catalog entry as a side effect.
- **B — ShopPayment ownership:** seller show, update, status, and delete
  operations verify the ShopPayment belongs to the authenticated seller's
  authorized shop, comparing record ownership against the server-resolved
  actor shop, not accepting a client-supplied shop identity. Mixed/foreign
  delete IDs cannot authorize another shop's configuration. Update authorization precedes credential-presence
  validation. Authorized shop staff retain the existing permitted access.
- **C — Payout ownership:** seller payout updates remain owner-scoped, reject
  `created_by` reassignment, and do not constitute a payout-processing
  implementation.
- **D — Secret redaction:** secret IDs and equivalent credential values are
  omitted from public resources and hidden by model serialization. Hidden fields
  are `client_id`, `secret_id`, `merchant_email`, `payment_key`, `merchant_key`,
  `subscription_key`, `api_user`, `api_key`, `private_key`, and `access_token`.
  Only safe
  configuration-presence and metadata booleans are returned. This does not
  reverse historical disclosure; the prior report retains the owner action
  concerning possible credential exposure.
- **E — Country policy authorization:** country-payment mutations require the
  existing scoped country transaction-management authority or global
  superadmin authority. Global catalog/provider-payload mutation remains
  superadmin-only.
- **F — Gateway status defect:** the status endpoint updates the actual
  `status` field. Explicit desired `status` or legacy `active` is supported;
  omitted state preserves the historical toggle contract and repeating an
  explicit desired state does not invert it.

## G–J. Collection control, payout-method boundary, assignments, resolver

### G — Collection-mode semantics

Platform collection is now the default for **new native Eloquent Shop records**
through protected model defaults: `collect_via_platform=true`. The defaults
apply only when creating a new Shop through the native Eloquent model; they do
not rewrite persisted shops and do not override explicit collection flags or
booking collection intent. All native writers continue to use Eloquent. No
migration, SQL default change, or bulk data update was made. A stored explicit
`false` remains false.

The Vendor experience presents the choice as **Payment collection**, with
AgendaAlly Payments as the recommended/default selection and vendor-direct
collection as an optional, explicitly selected radio choice. Server-side
permissions and business-country policy gate the control and the
available-direct decision. Credential forms are shown only in direct mode when
the server confirms direct mode and `context.valid=true`; the actual form method
list is additionally limited to resolver-authorized configuration methods. The
My Shop surface shows a read-only collection-mode label and link to payment
settings rather than another editable control. An unavailable direct option
cannot be newly selected through the collection-mode request. Existing direct
shops can keep their persisted flag while the option is unavailable; the
availability restriction does not silently reactivate platform collection or
erase owner intent.

The Shop update contract accepts optional desired collection mode as either
`collect_via_platform` or `collection_mode`, validates it under that shop's
`payments.gateways.manage` authorization, and rejects conflicting simultaneous
arguments. `true` selects platform collection, which is always available;
idempotent retries preserve the requested state. This is configuration only:
no provider setup or collection is activated.

The intended custody contract is unchanged: verified platform-collected
electronic proceeds, after applicable fees, should become a vendor payable;
vendor-direct funds must not be classified as platform-held vendor liability.
This pass supplies authoritative/frozen collection provenance and eligibility,
not a complete payable/settlement engine. Cash/offline and wallet/internal
methods retain their distinct native modes; a platform selection does not
assert that AgendaAlly holds those funds.

### H — Payout methods: implemented versus proposed

**Implemented:** the preserved Phase 0 payout ownership protections only. The
current Vendor request form (`.migration-backup/admin/src/views/seller-views/payouts/payoutRequest.jsx`)
asks for `price` and `note`. The native Payout model
(`.migration-backup/backend/app/Models/Payout.php`) has bookkeeping fields
including `price`, `payment_id`, `currency_id`, `cause`, and `answer`; its
update request (`.migration-backup/backend/app/Http/Requests/Payout/UpdateRequest.php`)
validates a payment catalog ID and amount but does not represent an authorized
or validated bank/mobile-money destination. A generic Payment ID identifies a
payment method, and an amount is not destination information or proof of
payout eligibility.

**Proposed, not implemented:** if approved later, introduce an additive,
country-authorized payout-method model with owner verification and appropriately
protected/encrypted destination fields. Such a model should collect only
necessary destination data and keep payout methods separate from customer
payment collection and ShopPayment gateway credentials. There is no payout
engine, destination collection, settlement, or disbursement in this amendment.

The minimum proposed destination contract would identify the actor-owned
Vendor/shop and actual business location/country, approved rail, supported
currency, provider reference or encrypted rail-specific destination, verification
state, and enabled/disabled state. Create/show/update/delete must remain owned;
country/rail support and destination verification must be server-enforced.
Never substitute a generic Payment ID, shop phone, merchant API secret, or
unrelated country invitation for an authorized destination. Do not collect bank
or mobile-money destination details until the specific rail/model is approved.

### I — Development country-payment assignments

The preserved guarded development policy fixture assigns the following 25
country/payment pairs. These are **development policy assignments only**, not
real-provider availability, legal availability, merchant eligibility, global
activation, or production readiness.

| Country | Development policy assignments | Real provider availability |
| --- | --- | --- |
| Cameroon | Cash, Wallet, MTN, Orange, Paystack, Flutterwave | Not established by this work |
| Nigeria | Cash, Wallet, MTN, Paystack, Flutterwave, Stripe, PayPal | Not established by this work |
| Ghana | Cash, Wallet, MTN, Paystack, Flutterwave, PayPal | Not established by this work |
| Burkina Faso | Cash, Wallet, MTN, Orange, Flutterwave, Paystack | Not established by this work |

The previous recorded run inserted 19 and left 6 unchanged; its second run
left all 25 unchanged. The guarded fixture changes only the existing
country/payment pivot and does not activate catalog methods, configure
credentials, or perform transactions. See the preserved
`payment-development-policy.md` and Phase 0 report for fixture safeguards and
the full development-only qualification.

### J — Resolver foundation and collection-policy response

The shared resolver foundation remains authoritative for business country,
transaction type, transaction/charge currency, collection mode, collector
configuration, and vendor/customer eligibility as described in the Phase 0
report. This amendment extends configuration discovery to require an actual
business-context decision for vendor-direct collection:

- `available_for_configuration` for a direct collector now requires the
  resolver to establish that vendor-direct is available for the actual
  context; a method cannot be offered merely because its provider/country is
  present in a broad catalog.
- Vendor payment policy includes a safe `collection` decision containing
  mode, platform-default semantics, platform/direct availability, direct
  method IDs, management capability, and `payout_methods_implemented=false`.
- Direct candidates are evaluated for capability/setup eligibility, not
  described as charge-ready or commercially approved.
- Existing context resolution, catalog/country policy intersections,
  transaction/currency constraints, collector configuration checks, and
  payment-initiation guards from Phase 0 remain in force. This amendment does
  not broaden checkout or create a new Web API.

The storefront currency selector is preserved. Display/requested currency is
not a promise of an authoritative charge in that currency; charge eligibility
uses the actual payable currency and provider capability. Vendor-payable
currency/accounting is a separate future contract, not an implemented FX or
liability ledger. Unsupported/unknown currency capability remains fail-closed.

## K–L. Financial-operation stop boundaries

**K — No external provider was activated.** No provider credentials were
requested, inspected, configured, or changed; no provider API, live or sandbox,
was called.

**L — No application financial mutation was performed.** No payment, payout,
refund, settlement, wallet-balance change, or fake financial records were
performed/created in the accepted development marketplace or production. The
focused regression suite uses disposable in-memory synthetic fixtures and
mocked provider responses; these are not provider calls or marketplace
transactions.

## M. Car Service taxonomy result

The existing native taxonomy uses category `type=11` for roots and
`type=12` for direct children. The accepted dataset had no matching automotive
root or requested automotive child labels, so no existing category was
renamed, replaced, or duplicated.

The new hierarchy is:

```text
Car Service (root, id 77)
├── Auto Repair & Maintenance (78)
├── Oil Change (79)
├── Tire Service (80)
├── Car Wash & Detailing (81)
├── Auto Electrical (82)
├── Diagnostics (83)
└── Brake Service (84)
```

The guarded, idempotent `development:car-service-taxonomy` command adds only
the eight taxonomy rows, their English translations, and category-image
references. First run: 8 categories, 8 translations, 8 references. Second run:
0 categories, 0 translations, 0 references added, retaining root 77 and child
IDs 78–84. No other domain records were created or modified. No migration was
added.

The original local semantic automotive SVG is
`/icons/categories/car-service.svg`; the Stage 2 category pictogram uses the
matching semantic car glyph. Category discovery/filter routes remain the
existing native routes, and the service-category grid retains children under
the root instead of flattening them into root cards.

Focused native browser verification passed at desktop 1280px and mobile 390px:
one Car Service root card, seven children in the separate “Explore within”
panel, and native Oil Change `/search?category_id=79` returning an honest
“No results found” state. No document overflow was observed: hierarchy widths
1265/1280 and 375/390; search widths 1274/1280 and 375/390. No Car Service
listings were needed or manufactured.

## N–R. Stories source audit and design proposal

### N — Audited implementation surfaces

The read-only audit is detailed in
[`stories-audit-and-stage-2-proposal.md`](stories-audit-and-stage-2-proposal.md).
Its material findings are:

- **Backend/schema:** a Story belongs to a shop and a polymorphic
  shop/product/service model; its model stores `shop_id`, model identity,
  `active`, and JSON media URLs. Native seller create/list/manage and public
  feed paths exist. Audit identified route-bound seller `show`/`update`
  ownership hardening as a separate issue, and noted that the public list does
  not filter by `active`.
- **Admin:** Admin/Manager can list and delete; the audited UI does not offer
  approve/reject or active-status moderation controls.
- **Vendor:** authenticated sellers can create and manage Stories through the
  portal, which constrains the list to the authenticated shop. Its form selects
  supported related model types and uploads one image.
- **Customer Web:** the public feed supplies recent Stories for approved
  shops; the existing viewer navigates to supported shop/product/service
  destinations. Public feed filtering is based on age and approved shop, not
  requested location or the `active` flag.
- **Flutter:** the app consumes the public feed and provides a Story rail and
  viewer with shop/service/product navigation.
- **Media:** the schema stores URL arrays; the seller upload route accepts
  files, while the customer surfaces are image-oriented. A reliably validated,
  customer-supported video format was not established.
- **Lifecycle/expiry:** the public cutoff, daily deletion timing, and portal
  display of `created_at + 24 hours` are not one explicit shared expiry
  contract. The expiry deletion path may leave uploaded files behind.
- **Interactions:** no view/read counter or per-customer interaction tracking
  was found. Do not imply viewed/unviewed state.
- **Stage 2:** Stories are already conditionally present on the Stage 2
  homepage when the endpoint returns content, after hero/discovery and before
  categories. This audit does not propose adding a duplicate rail.
- **Development content:** read-only aggregate inspection recorded zero Story
  rows in the owned development database. The accepted curated seed excludes
  the legacy expiring Story seeder.

### O–P — Classification and recommendation

**Classification: ACTIVE** as a source-flow classification, not a claim about
production usage. The create/manage/read path is present across native seller
API/portal, public API, Web, and Flutter. **Recommendation: IMPROVE** (retain as
a secondary, business-attributed marketplace discovery feature), not retire
or turn it into a social feed. The full audit distinguishes demonstrated
source behavior from operational/production assumptions.

### Q — Proposed Stage 2 treatment

Preserve the existing conditional homepage placement: below hero/discovery and
above service categories, keeping Services, Products, Businesses, Specialists,
and Categories primary. Label the restrained rail **Business updates**; use
Stage 2 warm ivory/off-white, dark text, restrained bronze accents/borders, and
low visual noise. Cards must identify the associated business using actual API
identity and media. Do not invent promotions, availability, location ranking,
engagement, or viewed/unviewed states.

For desktop, align to the existing content container and use a single
horizontal rail with compact cards (approximately 168 × 266 px, 16 px gap).
For approximately 390 px mobile, keep page gutters near 16 px, use a confined
horizontal rail with approximately 112 × 168 px cards and 12 px gaps, and do
not create document-level horizontal overflow. Keep the opened media at natural
portrait proportions (`contain`); a 4:5 crop is suitable only for rail
thumbnails. Provide semantic, keyboard-accessible card and viewer controls,
visible focus, touch-friendly targets, and reduced-motion behavior in any
future implementation.

An empty successful response should leave the rail absent as today. Do not
mislabel a failed request as an empty result. Exclude expired content under a
single future server-authoritative expiry rule; do not label stale cache as
current. If an open Story becomes unavailable, close or refresh it rather than
inventing a new status.

### R — Development Story data/media requirements

No Story fixture or media was added. If a fixture is separately approved, the
audit proposes the smallest reproducible option: one idempotent Story attached
to an existing synthetic approved development shop and that shop's own
`Shop` model, using one reviewed, rights-cleared local image. It must use the
guarded owned-development seed; no manual database patch, customer
communication, unsupported promotion/availability claim, booking/order, or
financial activity. If no approved image is available, defer the fixture.

## S. Exact files, migrations, and API contracts

### Preserved Phase 0/foundational baseline (not new in this amendment)

The following exact paths are the baseline implementation recorded by the
preceding Phase 0 report. They are listed to distinguish inherited protections
and resolver work from the amendment delta.

**Backend**

- `.migration-backup/backend/app/Console/Commands/SeedDevelopmentCountryPaymentPolicy.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Admin/CountryController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Admin/PaymentController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Admin/PaymentPayloadController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Payment/TransactionController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/PaymentPolicyController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/PayoutsController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/ShopPaymentController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/User/OrderController.php`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Rest/PaymentController.php`
- `.migration-backup/backend/app/Http/Requests/Payout/UpdateRequest.php`
- `.migration-backup/backend/app/Http/Requests/ShopPayment/StoreRequest.php`
- `.migration-backup/backend/app/Http/Requests/ShopPayment/UpdateRequest.php`
- `.migration-backup/backend/app/Http/Resources/ShopPaymentResource.php`
- `.migration-backup/backend/app/Models/ShopPayment.php`
- `.migration-backup/backend/app/Policies/CountryPaymentPolicy.php`
- `.migration-backup/backend/app/Repositories/ShopPaymentRepository/ShopPaymentRepository.php`
- `.migration-backup/backend/app/Services/PaymentEligibility/PaymentContextFactory.php`
- `.migration-backup/backend/app/Services/PaymentEligibility/PaymentEligibilityService.php`
- `.migration-backup/backend/app/Services/PaymentService/BaseService.php`
- `.migration-backup/backend/app/Services/PaymentService/MtnService.php`
- `.migration-backup/backend/app/Services/ShopServices/ShopPaymentService.php`
- `.migration-backup/backend/config/payment_eligibility.php`
- `.migration-backup/backend/database/seeders/DevelopmentCountryPaymentPolicySeeder.php`
- `.migration-backup/backend/database/seeders/Fixtures/DevelopmentCountryPaymentPolicy.php`
- `.migration-backup/backend/routes/api.php`
- `.migration-backup/backend/tests/Development/DevelopmentCountryPaymentPolicyTest.php`
- `.migration-backup/backend/tests/Hardening/PaymentEligibilityResolverTest.php`
- `.migration-backup/backend/tests/Hardening/PaymentPhaseZeroContainmentTest.php`

**Admin/Vendor, Web, and Flutter baseline**

- `.migration-backup/admin/src/components/payment/gateway-credential-fields.jsx`
- `.migration-backup/admin/src/services/seller/payment.js`
- `.migration-backup/admin/src/views/seller-views/payment/index.jsx`
- `.migration-backup/admin/src/views/seller-views/payment/payment-add.jsx`
- `.migration-backup/admin/src/views/seller-views/payment/payment-edit.jsx`
- `.migration-backup/customer_app/lib/domain/interface/payments.dart`
- `.migration-backup/customer_app/lib/infrastructure/repository/payments_repository.dart`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/cart/authorized-cart.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/cart/components/payment/payment.tsx`
- `.migration-backup/web/components/payment-list/payment-list.tsx`
- `.migration-backup/web/services/order.ts`

**Baseline reports and request artifact**

- `docs/development/payment-client-compatibility.md`
- `docs/development/payment-development-policy.md`
- `docs/development/payment-phase-0-foundation-report.md`
- `attached_assets/Pasted-AgendaAlly-Implement-Payment-Architecture-Phase-0-Found_1790898699105.txt`

The baseline API contracts remain those documented in the Phase 0 report:
contextual `GET /api/v1/rest/payments`, the existing seller payment-policy
route, and existing payment initiation/client payload paths. **No new Web API,
migration, Flutter change, or replacement payment endpoint was introduced in
this amendment.**

### New/changed files for this bounded amendment

**Backend**

- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/ShopController.php`
- `.migration-backup/backend/app/Models/Shop.php`
- `.migration-backup/backend/app/Services/PaymentEligibility/PaymentEligibilityService.php`
- `.migration-backup/backend/app/Services/ShopServices/ShopActivityService.php`
- `.migration-backup/backend/tests/Hardening/PaymentCollectionAmendmentTest.php`
- `.migration-backup/backend/app/Console/Commands/SeedDevelopmentCarServiceTaxonomy.php`
- `.migration-backup/backend/database/seeders/CarServiceTaxonomySeeder.php`
- `.migration-backup/backend/tests/Development/CarServiceTaxonomySeederTest.php`

**Admin/Vendor and category visual**

- `.migration-backup/admin/src/services/seller/shop.js`
- `.migration-backup/admin/src/views/seller-views/my-shop/index.jsx`
- `.migration-backup/admin/src/views/seller-views/payment/index.jsx`
- `.migration-backup/admin/src/views/seller-views/payment/payment-add.jsx`
- `.migration-backup/admin/src/views/seller-views/payment/payment-edit.jsx`
- `.migration-backup/web/components/stage2/category-pictogram.tsx`
- `.migration-backup/web/public/icons/categories/car-service.svg`
- `.migration-backup/web/scripts/category-hierarchy.regression.test.cjs`

**Audit/proposal documentation**

- `docs/development/car-service-taxonomy.md`
- `docs/development/stories-audit-and-stage-2-proposal.md`
- `docs/development/payment-collection-car-service-stories-amendment-report.md` (this report)
- `.agents/memory/modernization-approval.md` and `.agents/memory/MEMORY.md`
  — approval boundaries updated; no additional product implementation.

The Admin payment index/Add/Edit files overlap the preserved baseline paths;
their current amendment changes are limited to the collection semantics,
policy gates, and direct-mode form conditions described above. No migration
file changed. The shared `PaymentEligibilityService.php` path also has
amendment changes, extending rather than replacing the preserved resolver.
No Stories source, seed, media, schema, or API file changed.

### API contract delta

The seller Shop update request accepts an optional desired collection mode
(`collection_mode` and/or `collect_via_platform`) under existing per-shop
`payments.gateways.manage` authorization. The pair is validated for conflicts;
unavailable new direct selection is rejected. The policy response adds safe
collection metadata to the existing Vendor policy response. This is an additive
client contract change, not a new endpoint or public payment API. No Flutter API
change was made in this amendment.

Exact additive contracts:

- `GET /api/v1/dashboard/seller/shop-payments/policy?location_type=1|2`
  returns its existing `data.context` and `data.methods`, plus `data.collection`:
  `mode`, `default_mode`, `platform_available`, `vendor_direct_available`,
  `vendor_direct_method_ids`, `can_manage`, `payout_methods_implemented`.
  The latter is `false`; capability/setup availability is not payment readiness.
- `POST /api/v1/dashboard/seller/shops/collect-via-platform` accepts
  `{"collection_mode":"platform","location_type":1}` or
  `{"collection_mode":"vendor_direct","location_type":2}`.
  Optional `collect_via_platform` boolean is also supported; contradictory
  simultaneous choices are rejected. Omission preserves the legacy toggle
  contract. The existing success envelope returns a fresh safe Shop resource;
  the UI refetches policy before confirming current collection mode.
- Existing native category APIs and `/search?category_id=` contracts remain
  unchanged. No Story or payout-destination API was added.

## T. Focused checks and exact recorded results

- The new payment-collection amendment tests together with the existing
  resolver suite: **20 tests, 101 assertions passed** (targeted isolated
  in-memory test run).
- Existing Car Service seeder regression: **1 test, 18 assertions passed**;
  the test uses disposable in-memory SQLite and verifies preservation,
  hierarchy, and zero-addition idempotent rerun.
- Category-hierarchy regression script: passed (exit 0).
- Changed Web TypeScript check: passed (`tsc --noEmit --incremental false`).
- Final `bash scripts/verify-original-hardening.sh`: **95 tests, 637 assertions,
  exit 0**; PHPUnit reports **one existing deprecation**.
- PHP lint passed for the four changed payment backend files; Car Service's
  new command, seeder and test also passed lint.
- Babel JSX/JS parsing passed for all five changed Admin/Vendor files.
- `git diff --check`: passed.
- The narrow Car Service development seed was recorded as executed twice:
  first pass 8 categories/8 translations/8 media references; second pass zero
  additions. This is the only described data write, limited to owned
  development taxonomy rows and references.
- Earlier Phase 0 test results remain in the preserved baseline report and are
  not represented as new amendment tests.
- **Vendor browser check passed:** the existing shop remained vendor-direct;
  AgendaAlly Payments was recommended but not falsely shown selected. Switching
  Products→Services refreshed policy. External providers remained unavailable;
  Add exposed no merchant credentials. My Shop showed the actual read-only
  mode and settings link, not the old collection switch. No setting/config
  was saved and no credentials were inspected.
- **Car Service browser check passed:** hierarchy and native filter at desktop
  and 390px, with the widths reported under M. The initial location dialog was
  handled only in a new anonymous browser with a confirmed empty local cart;
  Cameroon→Yaoundé selection affected only that QA browser preference, not an
  existing customer/cart/account or accepted marketplace records.
- Browser evidence: Vendor settings `7urt7k`, Add `xn0xdy`, My Shop `e0gvhl`;
  Car Service desktop `4wkne9` and mobile `nxiz9t` in this session.

The three native previews were restarted once after the coherent code batch,
then confirmed running; healthy previews were not repeatedly restarted.
No broad acceptance, real/sandbox provider test, payment operation, or populated
Stories runtime test was performed. Stories remain source/aggregate audit only.

## U. Remaining blockers/decisions and verification status

No blocker remains for the authorized collection semantics, Car Service seed
or audit/proposal. Remaining decisions are outside this completed bounded pass:

1. A future payout-destination model/verification policy requires separate
   approval before collecting or storing destination information.
2. Any future Stories implementation or development fixture requires separate
   approval; the read-only audit identifies lifecycle, `active` filtering,
   seller route ownership, location expectations, and expired-media cleanup
   as prerequisites/follow-up areas, not changes made here. In particular,
   route-bound Story show/update ownership is a security gap, not a cosmetic
   issue to ignore while integrating the proposed rail.
3. Provider activation, external provider calls, FX, platform liability
   accounting, payouts, refunds, and broader checkout work all require separate
   approval.
4. Potential historical credential exposure still requires the owner action
   documented in the preserved Phase 0 report. No credential inspection or
   rotation was performed here.

## V. Stories implementation stop

**Confirmed: no unapproved Stories implementation occurred.** Stories source,
schema, media, seed data, runtime behavior, and development records were not
changed. The Stories work is source audit and Stage 2 proposal only.

## Stop boundary

This amendment stops after the bounded collection-mode configuration,
guarded Car Service taxonomy addition, and Stories audit/proposal. No broad
acceptance or publication is implied. The original Phase 0 report remains the
authority for its prior hardening details and historical credential-exposure
owner action. Wait for explicit approval before any next payment or Stories
phase.