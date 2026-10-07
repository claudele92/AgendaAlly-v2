# AgendaAlly — Phase 0 and foundational Phase 1 implementation report

Date: 2026-10-01 (America/Chicago).

## Scope and stop gate

Implemented the bounded attachment using
`payment-architecture-audit.md` as the historical baseline. The audit was not
repeated or rewritten. This is a payment architecture foundation, not a claim
of commercial provider readiness.

No external provider was activated, no provider API was called, and no real
payment, payout, refund, settlement, merchant onboarding or FX was performed.
No existing credential values were inspected, printed or rotated. No database
reset, broad demo reseed, accounting redesign, checkout redesign or publication
was performed. Work stops here; later payment phases require separate approval.

## Phase 0 containment

- Public and authenticated ShopPayment resources expose identities, status,
  safe currency/environment metadata and configuration-presence booleans, not
  credential values. Model serialization also hides credential attributes.
- Seller show/update/configuration/status/delete operations check the actor's
  actual shop and existing `payments.gateways.manage` grant. Authorized shop
  staff retain access; another shop's route-bound configuration is not enough.
  Update authorization happens before credential-presence validation.
- Status writes the real `status` field. The existing endpoint accepts explicit
  desired `status` or legacy `active` input; omitted input retains the old toggle
  contract. Retrying an explicit desired state does not invert it.
- Seller payout update requires the existing owner and prohibits `created_by`
  mutation. This is containment only, not a new payout implementation.
- Country-payment writes require the existing country transaction-management
  permission or global superadmin authority. Global catalog state/sandbox and
  provider-payload mutations require the existing superadmin authority.
- MTN initiation requires an existing catalog entry; it no longer creates and
  activates a missing method as a side effect.

### Owner action: possible historical credential exposure

The historical public serialization could expose credential material. Removing
that response does not invalidate anything already copied. The owner should
review prior exposure and arrange rotation of affected real credentials through
the provider's own process. This work did not determine which credentials were
real and did not inspect or rotate them.

## Shared eligibility foundation

`PaymentContextFactory` derives business country/currency from native service
or product locations and resolves owned booking/cart/order targets.
Customer-selected country, browser location and display currency do not become
business-country or charge-currency authority.

`PaymentEligibilityService` intersects catalog activation, represented adapter
readiness, country policy, transaction type, charge currency, collector mode,
actual collector configuration/status, and the existing environment safety
policy. The same decision is used for contextual discovery, Vendor registration,
manual booking/order initiation, internal cart checkout and external
booking/cart payload preparation. Wallet contributions are guarded before debit.

Important behavior:

- Different service/product countries require an explicit location type; an
  ambiguous shop request fails closed.
- Foreign owned targets are denied. Empty, multi-currency and stored-currency
  mismatch contexts fail closed for payment authorization.
- External multi-shop collection remains unsupported rather than choosing an
  arbitrary merchant.
- Booking `collect_via_platform` snapshots take precedence over later shop
  toggles. Product context uses the persisted shop collection setting.
- Vendor-direct MTN uses shop configuration; platform-collected MTN uses
  country platform configuration. Platform-only Stripe/PayPal/Paystack/
  Flutterwave do not use generic Vendor credential rows.
- Cash is offline; wallet is an internal balance mechanism. Neither implies
  platform custody, settlement or Vendor liability.
- Cash/wallet country exceptions remain explicitly enabled as
  `internal_country_policy=legacy_compatibility`. A strict alternative is
  represented and tested, but not activated as a breaking business-policy change.
- Configuration registration is separate from charge eligibility. An addable
  method can still require credentials before it is usable.
- Presence checks return booleans and currency metadata without decrypting or
  returning stored credentials. Presence is not proof of commercial onboarding,
  credential validity or provider approval.

### Currency representation

| Method | Foundation |
| --- | --- |
| Cash | Valid authoritative transaction currency |
| Wallet | Actor wallet must use that same currency |
| MTN | Exact collector-configured currency |
| Stripe | Narrow USD/CAD/EUR/GBP declaration plus existing precision checks |
| PayPal | Existing native verification currency list |
| Paystack / Flutterwave | UNKNOWN; unavailable until currency support is established |
| Orange and other unavailable adapters | Not made integration-ready by assignment |

The currency registry is code-level capability, not a provider-country
certification. Display/requested currency and authoritative transaction currency
are returned separately; there is no new conversion or FX routing.

## Contracts and clients

- `GET /api/v1/rest/payments` accepts additive `booking_id`, `cart_id`,
  `order_id`, `shop_id`, `location_type` and display `currency_id` context.
  Contextual responses retain the method list and add `meta.payment_context`.
  Owned target IDs require authentication/ownership.
- Contextless calls remain a compatibility catalog with
  `catalog_only=true` and `checkout_authorization=false`; they are not a
  bypass for shop checkout authorization.
- `GET /api/v1/dashboard/seller/shop-payments/policy` returns safe context and
  decisions for Vendor display/configuration. Product/service switching uses
  that server policy.
- Vendor Add/Edit surfaces use `available_for_configuration` and
  `vendor_configurable`, not a client country/provider rule list. Platform-only
  methods do not receive generic credential forms. Empty Add state has an
  explicit unavailable message and no method/status/submission controls.
- Web booking discovery uses the resolved service shop before booking creation;
  existing owned bookings can use `booking_id`. Cart uses its owned cart.
  Stale methods and wallet contributions clear on invalid/error/no-longer-
  eligible results; wallet controls require server-returned wallet eligibility.
  Loading/error/empty and charge-currency states are represented.
- Flutter repository parameters are additive. Exact event/screen plumbing and
  charge-currency metadata follow-up are documented in
  `payment-client-compatibility.md`; generated Freezed flows were not broadened.

## Development assignments

The guarded `development:payment-country-policy` command updates only the
existing country/payment pivot for the documented 25 pairs:

| Country | DEVELOPMENT POLICY ASSIGNMENTS |
| --- | --- |
| Cameroon | Cash, Wallet, MTN, Orange, Paystack, Flutterwave |
| Nigeria | Cash, Wallet, MTN, Paystack, Flutterwave, Stripe, PayPal |
| Ghana | Cash, Wallet, MTN, Paystack, Flutterwave, PayPal |
| Burkina Faso | Cash, Wallet, MTN, Orange, Flutterwave, Paystack |

Observed owned-development application: 19 inserted / 6 unchanged; second run:
25 unchanged. Country-policy assignment is not global activation. Existing
unlisted pairs, other countries, catalog flags, geography, credentials and
financial records remain unchanged. The fixture does not manufacture merchant
accounts, callbacks or successful transactions.

See `payment-development-policy.md` for the command's local ownership/marker/
migration-fingerprint/lock requirements and the separately classified matrix.

## Focused verification

- Final hardening run: **90 tests / 604 assertions passed**, with one
  deprecation warning and no failures. Development fixture: **1 test /
  15 assertions passed**. Combined: **91 tests / 619 assertions**.
- PHP lint passed for all 29 changed/new PHP files; `git diff --check` passed.
- Isolated hardening and development-fixture tests use disposable in-memory
  SQLite, not the application database. They cover public secret containment,
  cross-shop/staff permissions, desired status, payout ownership, scoped/global
  policy authority, country/currency/mode gates, foreign payables, pre-wallet
  guards, missing MTN catalog, and idempotent assignment scope.
- Web TypeScript `tsc --noEmit --incremental false` passed on the final changed
  Web sources. Changed Admin JSX parsed successfully; Dart formatting and
  client-contract checks passed. No full Flutter build was performed.
- One read-only signed-in Vendor browser pass confirmed Cameroon/XAF context,
  Products/Services policy switching, cash/offline eligibility and external/
  wallet unavailability. No runtime console errors or financial/provider
  mutations occurred. Existing Ant Design deprecation warnings remain.
- The pass found an empty disabled Add form; it was replaced with an explicit
  unavailable state. The fix was confirmed by JSX parsing and its conditional
  structure, not another browser journey.
- No live-provider or production acceptance is implied.
- Native Laravel, Admin/Vendor and Customer Web preview workflows were restored;
  unrelated Node artifacts were not substituted for the native applications.

Reproduction:

```sh
bash scripts/verify-original-hardening.sh
cd .migration-backup/backend
php vendor/bin/phpunit -c phpunit-hardening.xml \
  tests/Development/DevelopmentCountryPaymentPolicyTest.php
```

## Explicitly deferred

Provider activation/certification or sandbox operations, unknown-currency
enablement, Orange verified callbacks, external multi-merchant collection,
immutable FX quotes, liability accounting, payout/settlement architecture,
credential rotation, broad checkout changes, production migration/deployment,
and Flutter generated-event/screen propagation remain outside this approval.