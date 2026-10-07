# AgendaAlly Payment Phase 3A — provider readiness

Date: 2026-10-03. Scope: accepted Phase 2B/2C accounting plus bounded provider
state separation for MTN, Orange, Flutterwave and Paystack. **Implementation and
synthetic verification complete; no real provider activated.**

This is a development architecture result, not external merchant onboarding,
production, settlement, refund or live-payment certification.

## 1. Four separate decisions

1. **Capability:** source-declared currency and collection-mode routing;
   initialization, verification and callback foundations. It never depends on
   stored credential presence. Null currency declarations mean **UNKNOWN**.
   Native adapters are merchant-parameterized; this is not evidence of
   country-specific legal onboarding or a provider's external market coverage.
2. **Configuration:** correct owner and source exist, with required fields
   present. States: `NOT_CONFIGURED`, `CONFIGURATION_INCOMPLETE`, `CONFIGURED`.
   Presence is checked in owner-scoped SQL without decrypting merchant secrets.
   Configuration can exist while disabled or not runtime-ready.
3. **Structural runtime readiness:** complete configuration, an implemented
   verifier, correct collector currency, a credential-free HTTPS callback origin
   and provider-specific routing requirements. MTN non-sandbox routing requires
   an explicitly configured credential-free HTTPS endpoint; sandbox/EUR and
   native XAF routing are distinct. `READY` is structural/test readiness, not
   proof of externally operational credentials or live certification.
4. **Checkout availability:** all runtime conditions plus global activation,
   country policy, the exact Shop/transaction/mode/currency, environment,
   activation permission and supported canonical quote. Only explicit
   `quote_supported=true` can produce `CHECKOUT_AVAILABLE`. Discovery without
   quote evidence says `REQUIRES_NATIVE_VALIDATION`; the existing native writer
   still validates and freezes the actual quote before initiation/funding.

Outside the disposable `testing` environment all four focused providers have
`activation_allowed=false`, including when a local environment selects sandbox
mode. Their non-testing public catalog discovery is also blocked. This is an
explicit Phase 3A safety boundary, not an implicit operational-support claim.
Any future activation requires separately approved work to change this gate.

Capability absence, missing merchant credentials, mismatched merchant currency,
missing verifier, missing callback URL and disabled checkout now have distinct
states/reasons. Country activation is a checkout policy, not provider capability.
Missing configuration must not turn XAF into “unsupported.”

## 2. Collection semantics and ownership

**AgendaAlly Payments** means the platform-managed collection preference for
the Shop's Products and Service bookings. Available methods still depend on the
actual native checkout. MTN/Orange use the platform country merchant profile;
Flutterwave/Paystack use global platform payloads. It does not mean “Admin
personally received money,” that all providers are ready, or that external
payouts are available.

**Use my own payment gateway** means a Shop-owned merchant configuration and
Vendor-direct collection preference. It is not the payout-method setting.
MTN/Orange have native Shop configuration foundations; Flutterwave/Paystack have
no native Vendor-direct ownership implementation. Vendors can create/edit their
own merchant setup while their saved collection preference remains platform.
Saving configuration never confirms funds or creates an allocation.

The existing accepted `payments.gateways.manage` permission, server-owned Shop
binding, cross-Shop 404s, scoped delete/status endpoints and encrypted MTN/Orange
secret fields remain in force. Platform and Shop lookup never fall back to each
other; Vendor A's configuration cannot satisfy Vendor B. One merchant row per
provider stores one currency: different Product/Service charge currencies cannot
be promised by one shop-wide MTN/Orange collection preference.

Configuration and preferences are current settings, not evidence of historical
receipt. Existing allocation/contribution identity, custody, commission,
liability, frozen evidence, transaction links and legacy classification are
unchanged. No current credential/configuration lookup rewrites those snapshots.

## 3. Cameroon/XAF provider matrix

“Declared” below refers to repository code, not provider marketing or inferred
national availability. No real credentials or external provider calls were used.

| Provider | XAF/country capability evidence | Platform configuration | Vendor configuration | Authoritative verification | Structural/native readiness | Checkout now |
|---|---|---|---|---|---|---|
| MTN | XAF declared; country routing merchant-parameterized; Cameroon onboarding **unverified** | Country-owned native profile | Shop-owned native profile | Implemented authenticated status reconciliation; frozen merchant fingerprint, reference, amount and currency | Achievable in complete synthetic configuration; actual development has no merchant profiles | Disabled |
| Orange | Currency declaration **UNKNOWN**; Cameroon/XAF contract not proven | Country-owned configuration foundation | Shop-owned configuration foundation | Missing; initiation/callback foundations are deliberately blocked | NOT_READY even with complete merchant fields | Disabled |
| Flutterwave | Currency declaration **UNKNOWN**, including XAF; country onboarding unverified | Global platform payload; secret/account/webhook presence shown safely | Unsupported by native ownership model | Webhook hash plus provider lookup, merchant account, intent reference, currency and amount | NOT_READY: immutable configuration revision absent and legacy secret storage uncertified, in addition to any missing fields | Disabled |
| Paystack | Currency declaration **UNKNOWN**, including XAF; country onboarding unverified | Global platform payload; secret presence shown safely | Unsupported by native ownership model | Raw-body HMAC and authenticated provider/intent verification | NOT_READY: immutable configuration revision absent and legacy secret storage uncertified, in addition to any missing fields | Disabled |

Orange merchant configuration being allowed does **not** upgrade its UNKNOWN
currency capability, supply its missing verifier or activate checkout.
Flutterwave/Paystack configuration being stored does **not** upgrade their
currency declarations or financial identity. Actual development configuration
row counts are zero for all three configuration sources.

## 4. Verification, callbacks and idempotency

- **MTN:** notifications/checkout metadata alone are not payment proof. The
  implemented reconciler uses authenticated provider status lookup and the
  frozen expected merchant, original reference, exact amount and currency.
  It is not advertised here as a signed-webhook verifier.
- **Flutterwave:** webhook secret hash is checked, then provider transaction,
  configured merchant account, original reference, currency and amount.
- **Paystack:** raw-body signature/HMAC and verified original intent/account/
  reference/money are required. This does not certify global configuration for
  the accepted native contribution adapter.
- **Orange:** no implemented authoritative result/status/signature contract;
  callback or redirect data cannot confirm funding.

Existing shared verified-intent/finality boundaries are retained. Callback and
settlement regressions cover invalid evidence and amount/currency/merchant/
reference mismatches, duplicate/replacement settlement and stale transactions.
Owner/mode checks are covered by configuration isolation and accepted accounting
tests, including rejection of another Shop's direct contribution. Native MTN
Product/Booking tests exercise platform/direct custody and Wallet mixing through
the real verifier/settlement boundary. This is layered synthetic coverage, not a
claim that every cross-product combination was newly browser-tested.

Duplicate provider results must not duplicate economic effects. No new provider
verifier or callback authentication contract was invented in Phase 3A.

## 5. Accounting regression results

The accepted native tests continue to prove, in frozen native currency units:

| Verified funding | Retained result |
|---|---|
| Platform 100, commission 10 | Platform contribution 100; commission 10; Vendor payable 90 |
| Vendor-direct 100, commission 10 | Direct contribution 100; platform payable 0; commission receivable 10 |
| Wallet 40 + Vendor-direct 60, commission 10 | Commission satisfied 10; Vendor payable 30; direct 60; commission receivable 0 |

Replay leaves one commission/payable claim. Cash is Vendor-held collection, not
platform-held principal. Wallet is platform-held funding, consumed once under
accepted finality boundaries. Configuration creation/rotation alone leaves
native allocations, contributions, ledger, transactions and Wallet unchanged,
including an existing synthetic paid Cash payment.

## 6. Refund and external settlement status

None of the four focused providers is certified **REFUND READY**.
MTN/Orange provider refund dispatch/verification remains missing; existing
Flutterwave/Paystack refund paths are not a complete verified native refund
lifecycle. The previously audited Paystack wrong credential/status handling is
not repaired or certified here. Internal refund finality/authorization tests do
not certify an external provider refund.

No focused provider has demonstrated complete external settlement/payout
behavior in this work. Which external merchant account receives a verified
collection follows original collector configuration/custody, not a personal
Admin designation. Bank/mobile-money payout rails, contractual merchant
settlement, reconciliation, refund allocation and operational certification
remain separate unapproved work.

## 7. Minimum Admin/Vendor changes

- Vendor create/edit no longer requires a live saved Vendor-direct preference,
  global provider activation or country activation. Authoritative business
  country/currency and permission/ownership checks are retained.
- Vendor list/setup remains accessible while platform collection is saved.
  Capability, merchant configuration, structural runtime and checkout states
  display separately. Configure controls respect the management grant.
- MTN native forms now expose the non-secret endpoint needed for non-sandbox
  routing. Credential fields are masked; existing-field presence supports
  blank-preserves-current editing, including Orange client identifier.
- Admin country configuration list exposes safe provider states. Global
  Flutterwave/Paystack edit exposes provider state, secret presence and the
  missing Flutterwave webhook/account fields without prefilled secrets.
- Focused global-payload resources use a strict safe display allowlist. Omitted
  or blank focused secrets are preserved on server-side edit, and entered
  global merchant secrets are not persisted in menu/Redux drafts.
- Configuration exception paths no longer log credential-bearing input or SQL
  bindings. No real merchant values were read for display or written in tests.
- Unloaded native resource projections retain presence-only serialization;
  adding provider state does not force an unrelated payment lookup there.

**Storage limitation:** native local merchant encryption is unchanged. Global
payload storage retains its preexisting legacy plaintext model; redaction is
not at-rest encryption. It is explicitly NOT certified for runtime activation.
Likewise global rows lack `updated_at` revisions required by the accepted native
adapter. A secure-storage or financial-identity redesign needs approval:
**SECRET STORAGE DESIGN REQUIRED / immutable revision design required before
activation; no migration or bypass performed.**

## 8. Current vs Phase 3A vs activation requirement

| Area | Before Phase 3A | Phase 3A result | Remaining activation requirement |
|---|---|---|---|
| AgendaAlly Payments | Preference easy to confuse with gateway availability/receipt | Platform collection preference, independent readiness/checkout | Ready, approved platform merchant and supported native quote |
| Own gateway | Configuration depended on saved mode/availability | Shop merchant setup independent of live checkout | Correct Shop merchant, supported domains/currency and explicit activation |
| MTN | XAF merchant absence/environment could look unsupported | XAF capability separate from merchant and runtime blockers | Cameroon merchant/endpoint/contract validation and controlled operational certification |
| Orange | Readiness/currency/verifier states conflated | UNKNOWN currency; configurable foundation; verifier missing | Authoritative market/currency and verification contracts |
| Flutterwave | Global payload and unknown currency | Safe presence/redaction; revision/storage blockers explicit | Approved immutable identity/storage, market/currency and native verification integration |
| Paystack | Global payload and unknown currency | Same independent ownership/state boundaries; no direct fallback | Approved identity/storage/currency/native contract and refund correction separately |
| Platform collection | Configured/eligible states mixed | Platform-only ownership and readiness | Verified original platform receipt, not current settings |
| Vendor-direct | Platform preference prevented merchant setup | Own configuration permitted without activation | Explicit live approval; direct principal must stay outside platform liability |
| Verification | Existing MTN/FW/Paystack; missing Orange | Preserved and represented independently | Production-equivalent operational validation, especially missing Orange proof |
| Callbacks | Existing provider/shared finality boundary | Synthetic invalid/mismatch/replay regressions retained | Provider-specific callback deployment, authenticity and retry certification |
| Refunds | Incomplete native external provider lifecycle | Unchanged, explicitly NOT_READY | Separately approved original-receipt refund implementation/verification |
| Settlement/payout | Internal liability not proof of external rails | Custody/accounting preserved; external status unverified | Contract, reconciliation, operational external rails and explicit approval |
| Cameroon/XAF | Could conflate capability with setup/country policy | MTN XAF declared; others UNKNOWN; no onboarding certification | Evidence-based first-provider merchant/currency/country approval |

## 9. Exact native production file inventory

Prefix `B` = `.migration-backup/backend/`; `A` = `.migration-backup/admin/`.

Backend:

1. `B/app/Services/PaymentEligibility/ProviderState.php` — new four-state resolver.
2. `B/app/Services/PaymentEligibility/PaymentEligibilityService.php` — focused
   dispatch, independent Vendor setup/runtime policy and activation-safe catalog.
3. `B/app/Services/ShopServices/ShopPaymentService.php` — independent setup,
   blank-preserves-fields, credential-safe failure handling.
4. `B/app/Services/PlatformPaymentConfigService/PlatformPaymentConfigService.php`
   — credential-safe failure handling.
5. `B/app/Services/PaymentPayloadService/PaymentPayloadService.php` — preserved
   redacted focused secrets and credential-safe failure handling.
6. `B/app/Http/Resources/ShopPaymentResource.php` — safe presence/routing/state.
7. `B/app/Http/Resources/PlatformPaymentConfigResource.php` — safe presence/state.
8. `B/app/Http/Resources/PaymentPayloadResource.php` — allowlist, presence/state.

Admin/Vendor:

9. `A/src/components/payment/provider-state-summary.jsx` — new safe state display.
10. `A/src/components/payment/gateway-credential-fields.jsx` — masked fields,
    keep-current behavior and MTN endpoint metadata.
11. `A/src/views/seller-views/payment/index.jsx` — independent setup/state UX.
12. `A/src/views/seller-views/payment/payment-add.jsx` — independent setup.
13. `A/src/views/seller-views/payment/payment-edit.jsx` — independent edit.
14. `A/src/views/platform-payment-configs/index.jsx` — platform state display.
15. `A/src/views/payment-payloads/payload-edit.jsx` — redacted global edit UX.

Test/support/report changes: `B/tests/Hardening/PaymentProviderStateTest.php`
(new), `PaymentEligibilityResolverTest.php`, `BookingPaidAuthorityFixture.php`;
`.local/payment-phase3a-focused.xml` and verification receipts; both payment
reports; approval-boundary memory. No models, financial writers, ledger
algorithms, callback controllers, provider SDKs, migrations, dependency manifests,
production configuration, credentials or environment settings were changed.

## 10. Verification receipts and limitations

| Run | Result |
|---|---|
| Phase 3A focused provider state/configuration/ownership plus existing eligibility/callback/Phase 0 tests | **50 tests / 275 assertions**, no errors/failures |
| Accepted Phase 2C native/Phase 2B accounting and concurrency | **78 / 532**, no errors/failures; one deprecation |
| Payout containment/concurrency and Booking electronic authority | **44 / 429**, no errors/failures |
| Selected fulfillment/refund/Wallet/staff/payment authorization, callback and settlement regressions | **454 / 3,067**, no errors/failures; one deprecation |

Selected runs overlap; do not interpret their sum as unique tests.
The older broad `original-hardening` workflow remains failed; no full-project
green claim is made.

Focused cases include capability with no credentials, incomplete configuration,
missing callback, runtime-ready without checkout activation, platform/direct/
cross-Shop isolation, collector currency mismatch versus unsupported currency,
Orange missing verifier, global unknown currency/revision/storage blockers,
encrypted own-Shop configuration while platform preference remains saved,
server-owned Shop binding through the native seller controller, cross-Shop
show/update rejection, global read redaction/blank-secret preservation,
quote/environment/endpoint gates, preserved historical native financial rows
after configuration rotation, and country activation independence.

Old fixtures were corrected to include a synthetic HTTPS callback origin and
to expect the deliberately separated country/configuration and merchant-currency
states. An Orange capability declaration is injected only in one isolated test
to prove that a capable/configured provider can still lack a verifier; production
Orange remains UNKNOWN.

Both changed native previews restarted and serve HTTP 200. Seven changed/new
JSX modules parse and transform through the running Vite server. Native public
payments GET returns only `wallet,cash`. The shared preview API health screenshot
shows `status:ok`; it is **not** native authenticated configuration browser
evidence. No signed-in/browser save journey was exercised. Native write behavior
is verified with isolated controllers/services, not real development mutations.
No full production build or production deployment/certification was attempted.

Receipts:

- `.local/payment-phase3a-focused-junit.xml`
- `.local/payment-phase3a-native-regressions-junit.xml`
- `.local/payment-phase3a-phase1-regressions-junit.xml`
- `.local/payment-phase3a-selected-regressions-junit.xml`

## 11. Protected development state

Before-edit baseline `.local/payment-phase3a-before.json` exactly matches the
accepted Phase 2C after snapshot. The row fingerprint codec is PDO `FETCH_ASSOC`,
JSON unescaped Unicode/slashes with preserved zero fractions, encoded rows
sorted `SORT_STRING`, newline joined, SHA-256. This proves field-level
immutability, not merely counts.

**55/55 tables unchanged**, **408/408 schema entries unchanged**. Intentional
provider configuration changes: **none**. Existing financial records changed:
**none**. Allocations/contributions remain **0/0**. All **12** legacy Product
Orders retain `fulfillment_financial_state=unverified`; foreign-key errors **0**.
No legacy order classification, deletion, migration or recalculation.

`E` below is the exact empty-row fingerprint:
`e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`.
Counts/hashes shown are identical before and after.

| Protected table | Count | SHA-256 |
|---|---:|---|
| booking_activities | 0 | E |
| booking_coupons | 0 | E |
| booking_extra_times | 0 | E |
| booking_extras | 0 | E |
| bookings | 4 | f69fa6c983b30976ea156dfed854eb2988c7c3d2b8ec1207098d1c067c8c10f0 |
| cart_detail_products | 2 | 3961506d34e48b481f45e1aaf362be34e9260092ab65ad0fbbc5d2b302bc51b0 |
| cart_details | 2 | 23684122c1c717dec57e7d501cb3520de8ea7a359758c05222958587f6c124c9 |
| carts | 2 | 67a35295b220cf46a7440e2d338e3a3d61bbd41535ed4b9c17d15a8409aa955f |
| commerce_payment_allocations | 0 | E |
| countries | 4 | e8d6e158f09925d157297835628c7f8a6132285f712ed466383fb2f4e750893d |
| country_admins | 0 | E |
| country_currency_backfill_backup | 0 | E |
| country_invitations | 2 | 30d4b12bcd5e93e9aecfe141bf6e11de621c51ccf700d9ec6f79675378dd786e |
| country_payments | 37 | 93f3afb0925e351a0b6e7e75d2286fa86989d8fffe7519b4a272a1685505b1a1 |
| country_payments_backfill_backup | 0 | E |
| country_permissions | 23 | bc8b2a8ce681b39ba9543fd32848ddfce0d03a7dcd99bcd5d295fdd4857da87e |
| country_role_permissions | 117 | 71d6860d7eda482abb7af9c07c96cd3a382b60d9bbfe86b9ec9a699680571f56 |
| country_roles | 13 | eed70fe98208e4e16adf0d713f2d117abc89a75c2e9b5fa02b2783f3358b8bd6 |
| country_translations | 4 | 489a802502e6c8e5f632ae3fca3512c50f8ca7099f549bbda45101cad26d1edf |
| currencies | 10 | 64543b77e69e44d0ba2180948c09793d3c5cf1c9eaaa751369fa9a3f2a27f7a0 |
| email_subscriptions | 0 | E |
| gift_cart_translations | 0 | E |
| gift_carts | 0 | E |
| member_ship_services | 0 | E |
| member_ship_translations | 0 | E |
| member_ships | 0 | E |
| order_coupons | 0 | E |
| order_details | 12 | f0b21a600d101a4a6c3565e484576c35d437ea79b7666d04b4b95d46aee78ec1 |
| order_refunds | 0 | E |
| order_status_notes | 0 | E |
| order_statuses | 7 | 075e89a6c33c4aa2adc900d16de60a436975c0bdd7d57a8f9bf5f7defe19878f |
| orders | 12 | 68d4a1dc28f56a2220ef1eab14650f5c312a4f4d1fd332ebc91950c401bd637b |
| parcel_order_settings | 0 | E |
| parcel_orders | 0 | E |
| payment_collection_contexts | 0 | E |
| payment_payloads | 0 | E |
| payment_process | 0 | E |
| payment_to_partners | 0 | E |
| payments | 18 | 4e9e9ec2fa5d1c68227be75160f74c138083f1878e6ad93d6e1c51570836cdb8 |
| payouts | 1 | cfbae11fba1078d5e8619746d8bf6cb356bea2ac082ac975a8053072cfafbf6a |
| platform_fee_ledger_entries | 1 | 7930a754c3a433cdaa19c7f10560d3aa92dfad2a57729faafd366166db482011 |
| platform_payment_configs | 0 | E |
| seller_booking_clients | 0 | E |
| seller_currency_backfill_backup | 0 | E |
| settings | 23 | f5c20ffc025e2c626ee5927d3c9ef81887b6aed93d9297a51f9e88c432f46439 |
| shop_payments | 0 | E |
| shop_subscriptions | 0 | E |
| shops | 9 | eb63a7ebb70684b6caa69b106d0f20894da96c199b1ce9fd71f6a3f59c5fbe1e |
| subscriptions | 4 | 31735a3113e6f757be5d1ed6f41277f479632a3fa5c04a85dc3186f9e1768acc |
| transactions | 15 | dae4966ccfccfa6ac6088642a23a29e7c2ce0b0da63885beb490f65bbf8e6d26 |
| user_carts | 2 | bdceaa2abd4b4472667d0a9086ca59733927ddb20539f17e581937fc3e23e108 |
| user_gift_carts | 0 | E |
| user_member_ships | 0 | E |
| wallet_histories | 1 | bc61a6e580b16e8a703d348dae7e820d556fbe84c579e8c6d469ae6e9fc4cbb2 |
| wallets | 36 | 418cc7fc78e1faf07e84bfab21e9d8eaf939402823acd2cc641de43e82cac16b |

Receipts: `.local/payment-phase3a-{before,after}.json`,
`.local/payment-phase3a-schema-after.json` compared to the accepted Phase 2C
schema baseline, and `.local/payment-phase3a-protected-comparison.json`.

## 12. Required 32 decisions

1. **AgendaAlly Payments:** platform-managed collection preference, not
   automatic provider availability, personal Admin receipt or payout readiness.
2. **Own gateway:** Shop-owned merchant setup and Vendor-direct collection
   preference; separate from activation and payout methods.
3. **Configure without going live?** Yes, through permitted owner-bound native
   configuration paths, including while platform collection is saved.
4. **Capability independent of credentials?** Yes; source declarations only.
5. **Configuration independent of runtime?** Yes; complete fields can still
   lack a verifier, callback, endpoint, revision or certified storage.
6. **Runtime independent of activation?** Yes; structural READY can remain
   CHECKOUT_UNAVAILABLE. Non-testing activation remains prohibited.
7. **Cameroon/XAF capable providers?** MTN has declared XAF code-level routing;
   Cameroon onboarding remains unverified. Orange/FW/Paystack currency support
   remains UNKNOWN. No provider is certified live for Cameroon.
8. **Platform configurable?** MTN/Orange native country profiles; FW/Paystack
   legacy global payload foundations, explicitly not activation-certified.
9. **Vendor-direct configurable?** MTN/Orange foundations only. Orange's
   configurable state does not establish missing capability/verification.
10. **Authoritative verification?** Implemented MTN/FW/Paystack, not Orange.
11. **Callback/webhook verification?** FW and Paystack authenticate webhook
    evidence; MTN independently reconciles authenticated status, not raw hints;
    Orange lacks an authoritative contract.
12. **Callback idempotency?** MTN/FW/Paystack use accepted shared verified-intent/
    settlement replay guards; native MTN custody/mixed-funding replay is tested.
    Orange never accepts a payment to settle.
13. **Payment ready in development?** None of the focused providers in the
    actual configured development runtime. MTN structural/native readiness is
    demonstrated synthetically, not enabled.
14. **Refund ready?** None of the four is certified native external-refund ready.
15. **Known external settlement/payout behavior?** None demonstrated/certified;
    native original collector/custody and internal liabilities are known.
16. **Why MTN formerly reported XAF unsupported?** Merchant currency/presence,
    sandbox policy and capability were conflated; XAF declaration is now
    evaluated independently, with separate setup/runtime blockers.
17. **Why Orange formerly reported XAF unsupported?** Missing currency
    declaration/verifier/configuration were conflated. The correct current
    capability is UNKNOWN, not credentials-derived “unsupported.”
18. **Platform credentials make direct ready?** No; no owner fallback.
19. **Vendor credentials make platform ready?** No; no owner fallback.
20. **Vendor A affects B?** No configuration reuse or authorized cross-Shop
    update; server binding and owner-scoped queries/tests retained.
21. **Configuration changes reinterpret history?** No. Frozen accounting is
    unchanged; configuration rotation leaves synthetic historical rows intact.
22. **Direct principal excluded from platform liability?** Yes; original
    custody determines liabilities, including Wallet/direct splitting.
23. **Platform verified funding creates correct payable?** Yes; 100/10 yields
    90 in accepted native synthetic settlement.
24. **Cash/Wallet unchanged?** Yes; native finality/accounting regressions pass.
25. **Schema changes?** None; 408 schema entries identical.
26. **Secret-storage changes?** No storage/encryption migration. Existing native
    encryption retained; redaction/preserve-on-edit improved; legacy global
    storage remains an explicit blocker, not certified secure.
27. **Existing financial records changed?** None; 55 protected table hashes
    match, and no real configuration row changed.
28. **All 12 legacy Orders still unverified?** Yes, directly checked.
29. **Focused tests/assertions?** 50 / 275.
30. **Selected regressions/assertions?** Native/accounting 78 / 532;
    payout/Booking authority 44 / 429; selected containment/provider 454 / 3,067.
    Overlapping, not full-project-green; two runs retain one deprecation each.
31. **Before first real Cameroon/XAF activation?** Explicit approval; validated
    legal/merchant/currency/endpoint/environment and secure operational
    configuration; approved activation gate change; reachable authenticated
    callback/reconciliation; exact native Product/Booking quote, evidence,
    ownership, retry/replay, custody and liabilities validated under controlled
    operational testing; refund/settlement gaps explicitly resolved or bounded.
    No real-money test or credential setup is authorized by this phase.
32. **Best first controlled candidate?** MTN, strictly because declared XAF,
    authoritative reconciliation and native custody/mixed-funding/replay are
    implemented and synthetically verified. This is a candidate recommendation,
    not permission or proof of live Cameroon readiness.

## 13. Final boundary

No independent new immediately exploitable financial P0 was confirmed in this
bounded work. No financial identity/custody/commission/liability/evidence/schema
deviation or secret-storage migration was performed. Unresolved global revision/
storage and Orange verification/currency contracts remain explicit blockers.

**STOP.** Do not activate any focused provider, enable real Vendor-direct
checkout, configure real credentials, call real provider APIs, implement missing
provider refunds/external payout rails, migrate/certify production, classify or
delete legacy Orders, publish or automatically begin Phase 3B. Await approval.