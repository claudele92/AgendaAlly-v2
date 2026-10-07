# AgendaAlly payment system audit

## Current accepted implementation and Phase 3C stop

The subsequently approved durable identity/operation boundary is implemented;
the pre-approval paragraph below is historical, not current outstanding work.
See [current completion tracker](payment-system-completion.md).
The approved equivalent MySQL receipt self-anchor database guard now passes
17 tests / 57 assertions across MySQL and SQLite, with no owned schema/money changes.
The separately approved semantic JSON comparison passes focused MySQL 3/35 and
SQLite 3/35 checks and existing SQLite regressions 50/319, without rewriting JSON.
The separately approved MySQL index-identifier-only correction passes fresh
bootstrap/down-up/uniqueness tests on both engines, 2/28 each. All original
thirteen MySQL cases now pass, 51 assertions. The first expanded stale-RR-view
test then commits 14000 refund reservations against 10000 funding despite current
allocation lock/version. No reservation fix is implemented; no provider request/
refund or live HTTP exploit is claimed. Full financial concurrency remains
uncertified and all electronic activation remains blocked.
The later approved reservation current-read candidate passes the exact refund
snapshot cap (1/10) but fails independent-allocation progress (1/5). Shared
gap/supremum locks inhibit another allocation despite its independent parent lock.
Candidate withdrawn; original defect remains, no other financial path changed,
and remaining verification stopped at the broader-locking gate.
[Phase 3C source/reproduction, protected-state proof and approval gate](payment-phase3c-mysql-concurrency.md).

## Historical pre-identity-approval increment — completion program, partial

2026-10-03. The accepted audit/Phase 1–3 architecture was not reopened.
[Completion tracker and implementation matrix](payment-system-completion.md)
record encrypted global-provider configuration, masked/blank-preserve Admin
editing, six-provider state separation, Shop/country-scoped read-only canonical
financial views, focused native regressions and a prepared disposable MySQL
contention harness. All electronic checkout remains non-activated.

**The program is NOT complete.** Global original-configuration revisions and
durable pre-dispatch identity, generic electronic refund operations and
Vendor-payable payout/receivable workflows remain application-side work.
The tracker reports the minimum identity/custody/liability schema proposal
before crossing the completion brief's defined migration approval gate.
Existing provider contracts/certification blockers remain; known legacy
Paystack/PayPal refund defects are not reported repaired. No financial,
configuration or schema records were changed, and no real provider operation,
credentials, production access or publishing occurred.

## Latest bounded result — Phase 3B-3 official MTN contract/readiness review

2026-10-03. **NON-ACTIVATING / READ-ONLY. THIS PHASE DOES NOT AUTHORIZE
ACTIVATION OR A REAL PAYMENT.** The consolidated
[MTN readiness report](payment-phase3b-mtn-cameroon-readiness.md) now contains
the fresh exact-URL/source-date inventory, generic versus Cameroon evidence,
current source comparison, credential/environment and readiness matrices,
remaining contract gaps and **all 64 required final answers**.

New first-party Cameroon Collections product evidence establishes market
availability. MTN-hosted provider-managed production guidance (original author
visibly Community Manager) lists **Cameroon / `mtncameroon` / XAF**, with
production-host guidance `https://proxy.momoapi.mtn.com`. This updates the prior
interrupted review's “not established” finding; it does not prove an approved
AgendaAlly merchant, account-specific operation acceptance or Cameroon UAT.

- **Public EUR sandbox technical testing: CONDITIONAL GO** for a future,
  separately approved isolated EUR-native exercise, after complete Collection
  operation/callback evidence and a bounded test plan. No XAF-to-EUR relabelling.
- **Cameroon/XAF controlled non-production test: UNKNOWN**; exact host, target,
  currency/amount rules, merchant/credential entitlement and test-payer
  contract were not established. Do not proceed.
- **Cameroon/XAF production activation: NO-GO**; contract, merchant/marketplace,
  precision/MSISDN, settlement/reporting, refund/payout, operations and
  intended-engine certification remain incomplete.

The exact current Collection operation definition could not be retrieved from
the interactive portal. Current source sends callbackUrl in JSON without an
X-Callback-Url header, but the fetched official header example concerns
Deposit-V1; a search snippet is not sufficient to certify a Collection defect.
Callback compatibility remains UNKNOWN/approval-gated. Official callback and
FAQ pages also conflict on public-sandbox HTTPS versus HTTP. Do not silently
relax HTTPS or patch an unproven contract mismatch. Any subsequently confirmed
material mismatch requires **MTN CONTRACT DEVIATION REQUIRES APPROVAL / STOP**.

Accepted **Phase 3B-2 CONTAINED / VERIFIED** remains compatible within its
durable identity/UNKNOWN/same-reference recovery scope. Its tests/contention
are prior accepted evidence, not new runtime certification. No containment
redesign or new migration was required/proposed. Repository MySQL default is
intent evidence only: **PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED**.

Fresh read-only development checks: **55/55 protected counts/fingerprints
unchanged**, same snapshot codec; before snapshot matches accepted Phase 3B-2;
**416/416 post-migration schema entries unchanged**; platform/Shop/payload
configuration counts **0/0/0**; typed MTN processes **0**; FK errors **0**;
all **12 legacy Product Orders remain `unverified`**. Running development
public catalogue is **wallet,cash only**, with MTN still disabled/absent.
Receipts are named in readiness section 3B-3.8.

No application/configuration/financial/schema changes, credentials, provider
provisioning/token/payment/status/callback/refund/payout calls, production
access, FX, refund/payout implementation, Vendor-direct activation, other-provider
work or publishing. New runtime/regression/contention runs: **zero**.
No new independent source-confirmed immediately exploitable financial P0 was
identified within the authorized trace.

**STOP.** Safest next action: obtain written MTN Cameroon confirmation of the
exact Collection and approved non-production contract through the published
Cameroon Collections contact, without automatically contacting/onboarding,
obtaining keys or activating anything. Wait for explicit approval.

## Historical bounded result — Phase 3B-2 durable MTN identity

2026-10-03. The creator approved the minimum Phase 3B-1 existing-table proposal;
it is now implemented and applied only to owned development SQLite. Unique
immutable process/event/configuration identity and full canonical binding
commit before a conservative dispatch claim, which itself commits before
RequestToPay. UNKNOWN never resubmits or supplies paid authority. Typed native
polling/worker/callback-hint recovery retains the original reference and the
accepted Phase 2 economic identities/writers.

**Original MTN durable-identity blocker: CONTAINED / VERIFIED in bounded
development/fake-transport scope.** Focused receipts: 51 tests / 528 assertions;
selected accepted regressions: 392 tests /
2,880 assertions, zero failures/errors, one unchanged PHP nullability
deprecation. Product/Booking gross 100 / commission 10 / funding 100 / payable
90 and Wallet 40 + MTN 60 replay are verified. Four controlled two-connection
cases establish **SQLITE CONCURRENCY VERIFIED**. **PRODUCTION-ENGINE
CONCURRENCY NOT CERTIFIED.** No full-project green is claimed.

The approved migration adds six nullable fields, three indexes and five SQLite
integrity triggers to the existing process table, with no new table or
historical backfill. Preflight found zero legacy ID collisions; injected
collisions stop without cleanup. Original financial projections/fingerprints
of 55 protected tables are unchanged. All 12 legacy Orders stay `unverified`.
Migration/index/trigger/manifest/control metadata changes are separately
reported. FK check returns zero violations; no typed development MTN rows exist.

Real Customer catalogue remains Wallet/Cash only. MTN is disabled; the
Cameroon/XAF versus public sandbox EUR contract finding remains unresolved.
No credentials, real MTN calls, refunds, external payouts, FX, production,
legacy reclassification/deletion, other-provider implementation or publishing.
No new independent source-confirmed immediately exploitable financial P0 was
identified within this bounded work.

See [complete identity/migration report and 44 answers](payment-phase3b-mtn-identity-containment.md)
and [unchanged external-contract/activation blockers](payment-phase3b-mtn-cameroon-readiness.md).
**STOP.** Further provider/engine/activation certification needs separate approval.

## Historical containment review — Phase 3B-1 schema gate

2026-10-03. **MTN PAYMENT IDENTITY SCHEMA CHANGE REQUIRED. Identity containment
NOT implemented/certified; no migration created.** The separately authorized
minimum correction stopped under its before-edit schema gate.
See [MTN identity containment stop/proposal](payment-phase3b-mtn-identity-containment.md)
for full current sequence, identity relationships, minimum schema proposal,
legacy/rollback treatment, remaining blockers and all 35 final answers.

- Actual `payment_process.id` is NOT NULL but has no primary/unique constraint.
  Existing canonical context/provider-reference guards are narrower than a
  unique immutable pre-dispatch MTN attempt/funding-event binding.
- Three disposable in-memory SQLite schema checks confirm duplicate process
  UUIDs, different UUIDs with the same synthetic JSON binding and UUID updates
  are accepted. This is not a native financial exploit or MTN transaction.
- Moving process persistence earlier without completing attempt uniqueness/
  dispatch/recovery binding was not shipped as a partial containment.
- Application code, Phase 2 identities, credentials, financial/configuration
  rows and schema unchanged. **55/55** protected tables and **408/408** schema
  entries identical; all **12** legacy Orders unverified; foreign-key errors **0**.
- New focused application/regression/contention runs deferred at STOP; no new
  SQLite flow or production concurrency certification. Native catalog still
  **wallet,cash**. Original Phase 3B identity blocker remains open.

**STOP.** Separate identity-schema approval is required before migration or
correction. MTN remains disabled; public sandbox EUR does not certify Cameroon/
XAF. No automatic Phase 3B certification, activation or publishing.

## Latest review result — Payment Phase 3B MTN mandatory stop

2026-10-03. **MTN CONTRACT DEVIATION REQUIRES APPROVAL. Certification interrupted,
not completed. Controlled Cameroon/XAF test activation and live activation:
NOT READY.** See [MTN Phase 3B stop/readiness report](payment-phase3b-mtn-cameroon-readiness.md)
for official source inventory, source chain, minimum correction proposal,
readiness matrix, limitations and all 40 decision answers.

- Current official MTN best practices require transaction state persistence
  before requests. MTN API documentation identifies asynchronous RequestToPay
  outcomes through the original request reference. Native `MtnService` generates
  and sends the UUID before persisting `PaymentProcess`. A provider-accepted
  request followed by response timeout/local save failure has no saved MTN
  process/reference for normal callback/status/scheduled recovery.
- Accepted native accounting persists pending funding and blocks another charge
  while unresolved. **Automatic duplicate native collection was not demonstrated.**
  The confirmed blocker is lost provider identity/recovery, not a newly proven
  immediately exploitable financial P0. No provider call or exploit was exercised.
- Official public sandbox documentation specifies **EUR**, target `sandbox`;
  it does not certify the exact Cameroon/XAF collection/test contract. Cameroon/
  XAF remains internally declared, externally unconfirmed in this interrupted
  review, not generically “unsupported.” No FX workaround was implemented.
- No code/schema/credential/configuration/financial changes; **55/55** protected
  table counts/hashes and **408/408** schema entries unchanged; all **12** legacy
  Orders still `unverified`; foreign-key errors **0**. Running native payment
  catalog still **wallet,cash**.
- New focused/regression/contention runs: **none**, deferred by section 51 STOP.
  Previous Phase 3A synthetic passes remain accepted historical evidence, not
  new Phase 3B certification. Production-engine concurrency remains uncertified.

**STOP before correction/redesign.** Safest next action: separately approve a
non-activating durable MTN request-identity/unknown-outcome recovery design.
No activation, provider transactions, real credentials, production work or
automatic next phase. The accepted Phase 3A implementation below is unchanged.

## Latest implementation result — Payment Phase 3A

2026-10-03. **PROVIDER STATE SEPARATION IMPLEMENTED; NO PROVIDER ACTIVATED.**
This bounded, explicitly approved result supersedes earlier provider-status
descriptions below, not the accepted Phase 2 accounting contract or historical
evidence. Full current matrices, native file inventory, test receipts,
protected-state hashes, activation blockers and all 32 decisions are in
[Payment Phase 3 provider readiness](payment-phase3-provider-readiness.md).

- Capability is source-declared, independent of credentials. MTN declares XAF
  code-level routing; Cameroon merchant onboarding is not certified. Orange,
  Flutterwave and Paystack currency capability remains **UNKNOWN**, not invented
  support or an absence-of-credentials conclusion of unsupported.
- Owner-scoped configuration, structural runtime readiness, native quote
  support and checkout activation are independent. Vendor merchant setup no
  longer requires a saved Vendor-direct preference, country activation or a
  live provider. Platform credentials cannot satisfy Shop configuration, and
  Shop credentials cannot satisfy platform configuration.
- Admin/Vendor screens show capability, configuration, runtime and checkout
  separately. Native merchant secrets retain their existing storage model;
  focused global-payload reads are redacted and blank edits preserve secrets.
- Orange lacks authoritative verification. Global Flutterwave/Paystack payloads
  lack the immutable configuration revision required by the native contribution
  adapter and retain uncertified legacy secret storage. Neither blocker was
  bypassed through a financial identity or storage migration.
- Focused: **50 tests / 275 assertions**. Selected native accounting:
  **78 / 532**; payout/Booking payment authority: **44 / 429**; selected
  containment/provider regressions: **454 / 3,067**. Two selected suite runs
  report one deprecation each, with no failure/error. These overlap and are
  not a full-project-green claim. The preexisting broad hardening workflow
  remains failed and was not rerun as unrelated work.
- Protected state: **55/55** table counts/fingerprints and **408/408** schema
  entries unchanged; existing financial/configuration records changed **none**;
  all **12** legacy Product Orders retain `unverified`; foreign-key errors **0**.
- Native Admin and Laravel previews restart cleanly, changed Vite modules
  transform successfully, and the running public payment catalog exposes only
  **Cash and Wallet**. Authenticated configuration browser journeys were not
  exercised; isolated native controller/service tests supply the write evidence.

**STOP.** No real credentials, provider calls, activation, real Vendor-direct
checkout, provider refund implementation, external payout rails, production
work, legacy classification/deletion, publishing or automatic Phase 3B.

## Current result — both approved phases complete

2026-10-03. **READ-ONLY ARCHITECTURE AUDIT COMPLETE; NOT ACTIVATION CERTIFICATION.**
The completed audit's two-phase approval superseded the historical audit-stop
instructions below. Subsequent implementation remained separately bounded.

**Subsequent dual-P0 fulfillment containment:** Customer first-settlement authority
and Product fulfillment replay are both **FOUND AND CONTAINED / VERIFIED**.
The explicitly approved private `unverified | pending | settled` Order state
is implemented and migrated. **84 combined fulfillment tests / 780 assertions;
415 selected financial/authorization tests / 2,861 assertions passed**, including
focus, with one existing deprecation. Controlled contention uses two real SQLite
3.51.1 connections and native fulfillment calls; production concurrency remains
uncertified. All 52 other protected tables are unchanged. Orders have only the
approved new column: their original-field projection reproduces the original
hash exactly. All **12 existing Orders remain unverified**, with settlement
fail-closed; no legacy rollout or financial rewrite was performed.
No full-project green claim: the earlier full hardening run had 14 errors and
9 failures in unrelated coverage and was not rerun or repaired here.
See [current bounded implementation, migration and verification report](payment-product-fulfillment-dual-p0-containment.md)
and the [accepted historical stop/source chain](payment-product-fulfillment-p0-containment.md).
This does not undo completion of the architecture audit or alter its accepted
seven containment baselines.

**Phase 1:** Booking cancellation refund replay is **FOUND AND CONTAINED /
VERIFIED**. **87 focused isolated tests / 498 assertions passed**; all **53**
protected-table sets, counts and fingerprints unchanged, including the fee ledger.
Full native trace, scope, implementation, atomicity/concurrency, authorization,
exact changed files, tests and limitations:
[Booking containment](payment-booking-refund-replay-p0-containment.md).

**Phase 2:** Automatically resumed at the interrupted **Service cancellation/
accounting boundary**, without another approval, restart of the audit or rerun of
completed containment suites. Completed the remaining collection, provider,
refund, payout and reconciliation trace, current gates/matrices, all 32 answers
and ordered backlog. A new source-confirmed Product **fulfillment** repeat-credit
P0 is documented below; it did not trigger the superseded stop rule and was
neither exploited nor remediated.

Seven accepted Seller payment-status, Seller Product-refund, Booking Staff,
Wallet finality, Wallet amounts, Product refund finality and Booking refund
baselines remain authoritative. Production concurrency remains uncertified.
Transport deduplication and precision limitations were carried forward, not
reopened. No broader general CRUD/security audit was undertaken.

Current matrices:

- [Provider capability, Cameroon/XAF readiness and verification/refunds](payment-provider-current-matrix.md).
- [Current versus intended accounting/collection/refund/payout architecture and implementation sequence](payment-current-vs-intended.md).

### Architecture found — collection through settlement

**Platform-managed / “AgendaAlly Payments”:**

`Customer → owned checkout/authoritative business currency → eligible provider
→ platform merchant credentials → provider collection → verified server evidence
→ local process/paid Transaction → Order or Booking → fee/entitlement records
→ incomplete liability/refund/payout/reconciliation layers`.

MTN resolves a platform-country merchant row when the persisted collection
choice is platform. Global-payload gateways use platform merchant configuration,
not arbitrary Vendor credentials. Money is initially held/settled through that
registered **platform merchant account**, subject to provider settlement terms.
This is not payment collected by an individual Admin. The platform controls
that merchant relationship; its internal Admin Wallet is not proof of physical
cash/provider custody. No such external collection occurred or was certified.

**Vendor-direct / “Use my own payment gateway”:**

`Vendor/Shop preference → eligible Shop-scoped merchant config → owned checkout
→ frozen merchant/collection/reference → provider collection to Vendor merchant
→ verified Transaction → platform fee receivable → incomplete commission recovery/
refund accounting`.

MTN has native initiation/status-verification foundations in both modes. Orange
has fields/routing/forms but initiation deliberately fails closed and its callback
has no authoritative verifier. Other verified gateways are platform-only. The
implemented direct model is **PARTIALLY IMPLEMENTED / ACCOUNTING INCONSISTENT**,
not fully implemented. No silently substituted platform or peer-Shop merchant
is allowed when a direct Shop config is absent (E04/E06/E11).

Service Booking persists the collection choice at creation; initiation,
verification and Booking payable creation use that native snapshot.
Product cart intent freezes its choice, but Order has no equivalent immutable
collector/collection field. Product accounting therefore does not consistently
carry the same custody context through fulfillment/refund/payout (E04/E07/E08).

### Current Vendor issue — actual gates, not hypothetical blockers

Le Sawa Beauty Studio's owned-development Shop currently selects **platform**.
Both its actual Product and Service contexts resolve **Cameroon / XAF**.
The Shop-wide policy returns:

- `vendor_direct_available=false`
- `vendor_direct_state=unavailable`
- `vendor_direct_unavailable_reason=no_supported_gateway_for_every_business_context`
- no available direct method IDs;
- no implemented payout methods.

The anonymous read-only evaluator deliberately does not assert signed-in
`can_manage`/buyer Wallet readiness. Native ownership/grant logic remains the
accepted baseline; no impersonating login or financial endpoint was used.
The observed capability failure alone explains the unavailable choice.

| Gate | Current state / source | Why it passes/fails | Classification |
| --- | --- | --- | --- |
| Native preference | Platform; actual Shop metadata / E01-E02 | Describes current collection, not a credential or fee payment | CONFIGURATION |
| Global activation | MTN/Orange are **on**; also Cash/Wallet/Stripe/PayPal/Flutterwave/Paystack on | Global activation is **not** the MTN/Orange blocker; older inactive-catalog observations are historical | POLICY |
| Environment | Native public flags local / development true / payment mode disabled; E03/E14 | All online methods fail `environment_disabled`; sandbox allowlist is only Stripe/PayPal, not MTN | READINESS |
| Implementation | MTN in READY; Orange absent / E02/E06 | MTN verifier foundation exists; Orange fails `integration_unavailable` | CAPABILITY |
| Cameroon policy | MTN/Orange/Flutterwave/Paystack allowed; Stripe/PayPal denied / E02/E14 | Country passes for MTN/Orange; denies the two platform-only international alternatives | POLICY |
| Declared XAF | MTN explicitly listed; Orange no declaration / E03 | MTN capability passes; Orange capability fails independently of merchant credentials | CAPABILITY |
| Transaction types | MTN Product and Booking; Orange none / E02 | MTN passes both; Orange fails `transaction_type_unsupported` | CAPABILITY |
| Collection modes | MTN direct/platform; Orange scaffold direct/platform; others platform-only / E02/E04 | Only MTN could currently provide a verified direct rail; others cannot substitute | CAPABILITY |
| Shop-wide coverage | Actual Product and Service both XAF / E01/E14 | No two-currency conflict for this Shop, but **every** business context lacks an available direct rail | CAPABILITY |
| Configurability | `available_for_configuration=false` / E02 | Credential absence is not its predicate; disabled environment blocks MTN, unavailable verifier/XAF additionally block Orange | READINESS / CAPABILITY |
| Configured Shop state | Zero Shop merchant rows / E14 | Neither actual direct collector exists; `collector_not_configured` | CONFIGURATION |
| Platform/global state | Zero platform-country rows and global payload rows / E14 | Neither actual alternative collector exists; `collector_not_configured` | CONFIGURATION |
| Collector enabled/currency | No matching row to be enabled or match XAF / E02/E14 | MTN/Orange also return `collector_disabled` and `currency_unsupported` | CONFIGURATION / READINESS |
| Configuration preference | Actual platform context cannot create a direct config; E11 requires direct configuration mode | Registration additionally requires the native direct preference/context; this is distinct from why the collection option itself is unavailable | POLICY |
| Checkout readiness | No online method eligible in this instance / E02/E14 | Combined above gates fail; no provider probe/charge needed to establish it | READINESS |
| Financial safety | Incomplete Product custody, commission recovery, refunds and payout allocation / E07-E10 | Separate activation blockers; **not** the literal UI policy predicate | ACCOUNTING SAFETY |

MTN's `currency_unsupported` diagnosis conflates declared capability with absence
of a merchant row whose registered currency matches XAF. It is misleading if
read as “this application declares MTN incapable of XAF.” Orange's negative
XAF result agrees with the **current implementation**, not an externally verified
statement about the provider's commercial coverage.

Registration is partly separated from readiness: `available_for_configuration`
does not require credential presence, collector enabled state or an already
registered collector currency. It **does** require global activation, environment,
verified implementation, country, direct mode, declared currency and type/context.
Thus setup can precede credentials/readiness once those gates pass, but current
disabled online environment prevents MTN setup too. No implementation changed.

### Accounting — Product versus Service; Cash and Wallet

- `TransactionObserver` records a paid Order/Booking **service-fee** entry once
  per Transaction/type, on paid creation or transition. This is bookkeeping,
  not an automatic merchant split or commission transfer (E07).
- Booking `seller_fee = total_price - service_fee - coupon_price`.
  Order `seller_fee = total_price - total_tax - delivery_fee - service_fee -
  commission_fee`. The stored commission field, service-fee ledger and seller
  entitlement are not a single complete allocation contract (E07).
- A positive paid Booking seller fee creates a pending platform payable when
  Booking `collect_via_platform` is true. Direct Booking skips that payable.
  The condition is a preference snapshot, not verified actual Cash custody;
  it can create a payable for an offline Cash-paid record (E07).
- No equivalent paid-Order platform payable/collector snapshot exists. Product
  delivered handling instead credits the **first Admin account's Wallet by gross
  Order total**, regardless of actual merchant/cash collection. This legacy
  accounting must not be described as where the Customer's provider money went
  (E08). It is not a coherent Vendor entitlement or provider reconciliation.
- In direct mode, Vendor principal already goes to the Vendor merchant. Pending
  fee ledger can express an AgendaAlly receivable, but no provider split,
  automatic debit/sweep or confirmed fee-remittance completes commission recovery.
- Wallet-funded commerce is an internal balance debit/payment, not another
  provider principal receipt. Accepted Wallet invariants stand. Partial Wallet
  funding plus an external paid Transaction can still create separate fee/
  Booking payable entries keyed by **each Transaction**, not one funded allocation;
  callback's narrow contribution exception does not solve allocation duplication.
- Cash is offline. Manual paid state, service-fee bookkeeping and legacy
  fulfillment credits do not prove platform cash possession. Actual cash
  custodian and commission debt need explicit policy, not an invented gateway.

### Refund, payout and reconciliation conclusions

Internal Product acceptance and Booking cancellation now have their accepted
once-only SQL boundaries. The original paid commerce Transaction can correctly
remain paid while refund is represented separately. Those containments are
**not** complete provider-level refunds or collection-aware refund allocation.

Legacy `PaymentRefund` dispatches selected global gateways using current global
payload and local references; there is no common durable refund intent/claim,
original collector revision, provider idempotency key, asynchronous refund
confirmation/recovery or payout/fee allocation linkage. MTN and Orange have no
dispatcher branch. Paystack's refund adapter uses `flw_sk` and converts HTTP 200
to Transaction `progress`, not authoritative refund success. For Orders,
`getModelData` replaces the external reference with `cart.paymentProcess.id`;
correct provider-specific reference/amount must be demonstrated, not assumed.
Internal refund plus a separate provider-return path needs a shared allocation
boundary to prevent double reimbursement (E10).

PayPal's adapter also decodes its response to an associative array and then calls
`status()`/`json()` methods on it: the local confirmation path is broken even if
the remote request succeeded. This is another source-confirmed provider-refund
activation blocker, not a provider call made by this audit.

`PayoutService` is a generic user request/approval/Wallet bookkeeping system,
**not** a implemented provider/bank Vendor disbursement. It neither reserves
specific earned Vendor entitlements nor allocates fee/payable rows to an external
transfer. Acceptance uses the approving account's Wallet, not a verified
platform settlement reserve. Non-Wallet acceptance has no provider transfer call.
There is no end-to-end provider collection/refund/payout reconciliation (E09).

### Historical source-confirmed P0 — Product fulfillment gross-credit replay

**At read-only discovery: P0 / FOUND / NOT CONTAINED; SOURCE-CONFIRMED ONLY.**
**Current disposition: FOUND AND CONTAINED / VERIFIED**, using the separately
approved durable Order claim described in the linked current report above.
The following source trace describes the pre-containment implementation; its
historical line references and discovery restrictions are not current code.
This is **not** the accepted Product `OrderRefund` finality finding.

Natural discovery while tracing the required Product-versus-Service entitlement/
commission path:

1. Authenticated same-Shop Seller status route is
   `POST /api/v1/dashboard/seller/order/{id}/status` (`routes/api.php:725`).
2. Seller `OrderController::orderStatusUpdate` at 132–155 loads its own Shop's
   Order and delegates `StatusUpdateRequest`.
3. `app/Http/Requests/Order/StatusUpdateRequest.php:18–24` accepts the native
   `Order::STATUSES`, not a terminal/funded transition matrix.
4. `OrderStatusUpdateService.php:50–59` rejects only identical state.
   At 67–81 each transition into delivered calls `adminWalletTopUp` and
   eligible cashback, then stores the lifecycle at 90–95.
5. `adminWalletTopUp` at 179–199 unconditionally creates a paid top-up history
   for the first Admin with a Wallet, amount = positive persisted Order total.
   There is no per-Order financial settlement marker/funding claim.
6. A transition away from delivered to another accepted non-canceled state
   does not take back this gross credit or charge the Customer anew.
   Returning to delivered creates another paid WalletHistory/Transaction/credit.

Preconditions: an authorized owning Seller, positive native Order total and an
Admin with a Wallet; ordinary native records suffice. With total `T > 0`, each
return to delivered can add another `+T` to that internal account without another
funding debit. Cashback can also repeat where its configured eligibility applies;
that additional effect is conditional, not required for the gross-credit finding.
Native SQL atomicity per status update does not make sequential repeats once-only.
Global positive Wallet validation cannot distinguish funded first credit from
replayed positive credit. No cross-Shop bypass or dormant provider is required.

This source-confirmed financial P0 was added to the backlog and the read-only
audit continued under the latest approval. No existing Order/Wallet, real endpoint,
fixture exploit, history repair or further remediation was used.

### Consolidated remaining backlog — source-confirmed versus uncertified

| Priority / class | Remaining finding and financial consequence | Evidence / disposition |
| --- | --- | --- |
| **Contained P0 financial mutation** | Historical Product fulfillment gross-credit replay; private Order-level claim now prevents another financial batch | E08; FOUND AND CONTAINED / VERIFIED in bounded P0-B phase; legacy rollout and production concurrency remain separate |
| **P0 first-settlement financial authority — subsequent finding** | Customer caller previously admitted delivered and an unscoped target; now own-new cancellation only, plus shared actor/target/grant guard before settlement | FOUND AND CONTAINED / VERIFIED; 41 focused tests / 177 assertions; see dual-P0 containment report |
| **P1 conservation/atomicity** | Wallet-method payout explicitly sets approving Wallet `A-P`, then native withdrawal decrements another `P`; recipient receives `P`. Status is saved before legs; no encompassing transaction and false service results are ignored | E09 `PayoutService:117–185`; source-confirmed double debit/partial-settlement risk, not executed |
| **P1 activation-critical authority** | Generic Booking Transaction creation runs eligibility but then sets paid for any active eligible non-Wallet method without provider verification. Disabled environment blocks current online use; enabling a configured electronic rail exposes this alternate paid path | E12; dormant online bypass, not claimed current enabled exploit |
| **P1 custody/liability** | Cash preference can produce Booking platform payable; Product lacks immutable collector/payable parity; legacy gross Admin Wallet credit ignores actual collector | E07/E08; source-confirmed accounting inconsistency |
| **P1 allocation duplication** | Partial Wallet and external remainder can each trigger fee/payable entry keyed by separate Transaction; no single allocation claim deduplicates commerce entitlement | E05/E07; source-derived architecture risk, production behavior not certified |
| **P1 payout eligibility** | Generic requested amount/recipient currency is not reserved against earned Vendor liability; no beneficiary/currency/allocation-to-remittance proof | E09; privileged approval is not reconciliation |
| **P1 provider refunds** | Missing MTN/Orange refund branches; current global collector/local reference assumptions; Paystack wrong credential key/status; PayPal decoded-array response method calls; no common durable refund allocation | E10; source-confirmed, no calls |
| **P1 direct fee recovery** | Direct Vendor principal receipt has pending fee receivable but no actual commission collection or reserve/remittance | E04/E07; do not activate on a ledger entry alone |
| **P1 currency/reserve** | Legacy gross Wallet writes/payouts do not establish a same-currency reserve/custodian contract; provider currency scales and mixed contributions not fully modeled | E07-E10; accepted precision/Wallet concurrency limitations preserved |
| **P1 secrets-at-rest risk** | Shop/platform merchant secrets have encrypted casts; global PaymentPayload uses JSON-array cast, not an equivalent declared encryption boundary | E11/E15; no credential value read or exposure claimed |
| **P2 capability/readiness UX** | Environment blocks merchant registration; collector-currency absence appears as intrinsic MTN XAF unsupported; country-denied methods have unknown derived currency state | E02/E03/E14; document-only |
| **P2 legacy surface** | Dormant provider identifiers/forms/adapters exceed verified READY set; old refund/fulfillment paths remain influential | E06/E08/E10; retain disabled until approved disposition |
| **P2/P3 operational assurance** | Production races, callback send transport deduplication, reconciliation/monitoring/runbooks and clear cashier/custody UI unproven | Accepted reports/current source; not a new exploit claim |

Accepted seven containments are **not reopened** or relabeled uncontained.
The new findings are separate callers/architecture boundaries. No remediation
sequence was executed. Prioritized proposed order is in the current-versus-intended
matrix: correctness → accounting → collection → readiness → refunds → payout/
reconciliation → production verification → separately approved activation.

### Final answers — all 32 required questions

1. **What does AgendaAlly Payments actually do today?** It routes eligible owned
   purchases through platform merchant configuration and records verified payment/
   fees; its liability, external refund and payout/reconciliation layers are
   incomplete. Current development permits no electronic charge (E01–E07/E14).
2. **Does platform-managed electronic collection work end-to-end?** Selected
   rails have initiation/verification/paid-record foundations; no configured or
   enabled end-to-end charge→Vendor remittance was demonstrated. Financially,
   the complete end-to-end system is **not implemented/certified** (E04–E10).
3. **Whose credentials are used?** Platform global payloads for supported global
   gateways; platform-country MTN for platform choice; Shop-owned MTN for direct.
   Orange has config scaffolding only. Not an individual Admin's credentials (E04/E11).
4. **Where does Customer money go first?** The selected provider's registered
   platform merchant in platform mode, or Vendor merchant in direct mode.
   Internal Wallet/Cash are distinct custody cases; no real receipt was observed (E04/E06).
5. **What establishes authoritative payment success?** Trusted provider/server
   evidence bound to frozen intent reference, merchant, target, method, positive
   amount and currency—not a browser return status or unsigned body (E05/E06).
6. **How does AgendaAlly earn/record commission?** Paid commerce records pending
   service-fee ledger; stored commission fields/accessors have additional native
   calculation meaning. No complete fee allocation/confirmed collection contract,
   particularly direct mode, exists (E07).
7. **How is Vendor entitlement represented?** Booking seller-fee/payable when
   platform-choice flag is true; Order seller-fee accessor but no symmetric paid
   payable allocation. Legacy gross Admin Wallet writes are not Vendor entitlement (E07/E08).
8. **When does AgendaAlly owe a Vendor money?** Source records Booking payable
   on paid Transaction plus platform-choice snapshot. Financially liability should
   depend on actual held-for-Vendor principal; Cash/direct/Product cases make the
   current rule incomplete (E07).
9. **How is the Vendor actually paid?** No ledger-entitlement→provider remittance
   exists. Generic approved Wallet payout credits request creator from approving
   account, with the documented defects; this is not external Vendor payment (E09).
10. **What does “Use my own payment gateway” do?** Changes Shop-wide preference
    and directs supported merchant resolution to that Shop's gateway; Booking
    freezes it, cart intent carries it. It does not implement commission recovery (E02/E04).
11. **Is Vendor-direct actually implemented?** **PARTIALLY IMPLEMENTED**:
    MTN foundation; Orange configuration-only/unavailable verifier; remaining
    supported electronic rails platform-only; downstream accounting inconsistent (E04/E06/E07).
12. **Why is the option currently disabled?** Actual local payment environment is
    disabled, so MTN configuration capability fails; Orange also lacks verifier/
    types/XAF declaration. Every Shop business context therefore has no eligible
    direct rail. Credentials absent further block charging, not that predicate (E02/E14).
13. **Can Vendors configure before runtime checkout readiness?** Yes before
    credential/collector readiness, **only after** capability, country, activation,
    environment and direct-context gates. Current environment prevents it; current
    platform preference additionally prevents direct config creation (E02/E11).
14. **Which providers support Vendor credentials?** Native Shop schema/routing
    tags are MTN and Orange; only MTN has current verifier foundations (E02/E11).
15. **Where does money go in Vendor-direct?** Vendor's registered provider
    merchant, not platform merchant; missing direct configuration fails rather
    than falling back to another Shop/platform collector (E04/E06).
16. **How does AgendaAlly receive commission in Vendor-direct?** It currently
    does not complete automatic receipt: pending fee receivable/bookkeeping,
    no implemented split, debit, sweep or confirmed remittance (E07/E09).
17. **Can Vendor-direct create duplicate platform payout liability?** Direct
    Booking suppresses normal principal payable; not a blanket certification:
    Products lack equivalent snapshot/accounting, legacy credits ignore collector,
    partial contributions and generic payouts lack allocation/reservation (E07–E09).
18. **Which providers actually work for Cameroon/XAF?** No electronic provider
    is checkout-ready here. MTN is explicitly XAF-capable with verifier foundation,
    not certified live operation. Orange unavailable; Flutterwave/Paystack currency
    unknown; Stripe/PayPal denied and XAF undeclared. Offline/internal are separate (E02/E03/E14).
19. **Why do MTN/Orange produce current XAF diagnostics?** MTN lacks the matching
    configured collector currency despite declared XAF support; Orange lacks
    declared capability and verifier as well as collector. These are application
    diagnostics, not external provider commercial conclusions (E02/E03).
20. **What is Flutterwave's actual state?** Implemented initiation/authenticated
    callback lookup, platform-only, globally on and CM-allowed; environment off,
    payload absent, XAF capability unknown; not checkout-ready (E06/E14).
21. **What is Paystack's actual state?** Same platform readiness blockers as
    Flutterwave, with HMAC/lookup foundations; additionally incorrect legacy
    refund credential/status handling. Not checkout-ready (E06/E10/E14).
22. **Are capability/configuration/readiness properly separated?** Partly:
    config capability excludes credentials but still requires charge-environment
    permission; currency diagnostics mix declaration and collector registration;
    source READY is not legal/live/accounting readiness (E02/E03/E14).
23. **Are provider callbacks authoritative/idempotent?** Selected verified rails
    require server proof and source-level same-intent/other-paid guards. Orange/
    dormant adapters are unavailable. Production race, late/out-of-order refund
    and transport certification are not established (E05/E06; accepted limitations).
24. **Are Product and Service accounting coherent?** No: Booking snapshot/payable
    versus Order missing collector/payable and gross Admin fulfillment credits;
    differing allocation formulas and incomplete reserve/refund/payout coupling (E07/E08).
25. **How does Cash interact with commission/Vendor entitlement?** Offline paid
    state can trigger service-fee and Booking payable by preference, while Product
    delivered credits gross Admin Wallet. These do not establish actual cash
    custody or a safe Vendor/fee allocation (E07/E08/E12).
26. **How does Wallet-funded commerce interact with commission/Vendor entitlement?**
    Internal spend and paid commerce observer produce fee/payable bookkeeping.
    Accepted Wallet invariants stand; partial/external and later fulfillment
    representations still lack one allocation/reserve boundary (E05/E07/E08/E13).
27. **Are refunds complete under platform collection?** No. Accepted internal
    once-only reimbursements are verified; legacy provider adapters, allocations,
    original merchant/reference, asynchronous recovery and reconciliation are
    incomplete, particularly MTN and Paystack (E10/E13).
28. **Are refunds complete under Vendor-direct?** No MTN/Orange provider refund
    route or original direct-merchant refund intent; internal Wallet reimbursement
    cannot be assumed to return Vendor-held provider principal (E04/E10/E13).
29. **Is payout/settlement/reconciliation actually implemented?** Request/
    bookkeeping only; no reserved entitlement allocation, verified beneficiary/
    currency provider disbursement or reconciliation. Wallet approval itself has
    conservation/atomicity defects (E09).
30. **What double-credit/double-payout risks remain?** Product fulfillment replay
    is now contained by the separately approved durable claim. Remaining:
    separate Transaction-based partial fee/payable duplication; generic
    unreserved payouts; internal plus provider refund allocation gaps. Accepted
    refund/Wallet replay containments are not reopened (E07–E10/E13).
31. **What blocks safe real electronic payment/Vendor-direct activation?** The
    documented P0/P1 finance/paid-authority/custody defects; missing collectors,
    environment/policy/currency gates; complete fee/refund/payout allocation,
    merchant verification and production/conservation/reconciliation evidence.
    Activating a row alone would not solve these (E02–E15).
32. **What implementation sequence should come next?** P0/P1 financial correctness
    → allocation/custody model → immutable collection model → provider/configuration
    readiness → authoritative refunds → payout/reconciliation → production
    verification → explicitly approved staged activation. Proposal only.

### Current source evidence index

`B=.migration-backup/backend`, `A=.migration-backup/admin`. Function names are
authoritative anchors where future edits shift line ranges.

| Evidence | Source |
| --- | --- |
| E01 | `B/app/Services/PaymentEligibility/PaymentContextFactory.php:20–105`; business location country, charge currency, ownership, collection snapshot |
| E02 | `B/app/Services/PaymentEligibility/PaymentEligibilityService.php:26–179,216–248`; decision/configuration/Shop-wide coverage and SQL existence readiness |
| E03 | `B/config/payment_eligibility.php`; `B/config/development.php`; `B/app/Helpers/EnvironmentPolicy.php::paymentProviderEnabled` |
| E04 | `B/app/Services/PaymentService/BaseService.php:592–681,1161–1291`; target ownership, routing freeze, single-Shop and merchant resolution |
| E05 | `BaseService.php:65–127,229–346,430–584`; trusted proof, terminal replay, other paid intent/contribution, commerce settlement |
| E06 | `B/app/Services/PaymentService/{Mtn,Orange,Stripe,PayPal,FlutterWave,PayStack}Service.php`; corresponding Dashboard Payment controllers; `Verification/PayPalVerification.php` and `DecimalAmount.php` |
| E07 | `B/app/Observers/TransactionObserver.php:35–154`; `B/app/Models/Booking.php::getSellerFeeAttribute`; `B/app/Models/Order.php::getSellerFeeAttribute`; Booking payable adjustment |
| E08 | `B/app/Services/OrderService/OrderStatusUpdateService.php:50–98,179–199,239–256`; Seller OrderController:132–155; Order StatusUpdateRequest and `routes/api.php:725` |
| E09 | `B/app/Services/PayoutService/PayoutService.php:82–187`; Seller/Admin PayoutsController; Payout Store/UpdateRequest and model; no payable allocation/provider transfer |
| E10 | `B/app/Traits/PaymentRefund.php:41–87,93–171,388–436`; legacy dispatch, Paystack semantics, original-reference assumptions and no durable common refund lifecycle |
| E11 | `B/app/Services/ShopServices/ShopPaymentService.php::prepareGatewayConfig`; Seller ShopPaymentController; ShopPayment and PlatformPaymentConfig models; `A/src/views/seller-views/payment/index.jsx` |
| E12 | `B/app/Http/Controllers/API/v1/Dashboard/Payment/TransactionController.php:41–64`; `B/app/Services/TransactionService/TransactionService.php:376–445`; generic Booking paid path |
| E13 | Accepted Seller/Staff/Wallet/Product reports and latest Booking containment report in this directory; referenced as baselines, not reopened in Phase 2 |
| E14 | `.local/payment-architecture-completed-readiness.json`; query-only minimal native evaluator `.local/payment-architecture-readonly-observation.php`; public runtime flags only, no credential values |
| E15 | `B/app/Models/PaymentPayload.php` JSON-array cast versus `ShopPayment` / `PlatformPaymentConfig` encrypted secret casts; no stored credentials retrieved |

### Audit safety, state receipt and final stop

Phase 1 receipt/test paths are in its report. Phase 2:

- `.local/payment-architecture-completed-before.json`
- `.local/payment-architecture-completed-after.json`
- `.local/payment-architecture-completed-fingerprint-result.json`
- `.local/payment-architecture-completed-readiness.json`

Same established serializer and protected set: **53 tables**, including
`platform_fee_ledger_entries`. **Identical table sets, codec, row counts and
fingerprints; zero changed tables** across both phases. Source/policy discovery
uses owned SQLite `PRAGMA query_only=ON`, no native application kernel, observers
or provider/network calls. Configuration readiness uses SQL existence tests,
not credential retrieval. Runtime observation exposes only public environment
flags. No identities, balances or monetary rows are printed by the audit.

Phase 2 ran no containment suites, mutating endpoints, financial fixtures,
migrations/seeds, real payments/refunds/payouts/Wallet operations, credentials,
provider/country/currency/collection/commission edits, repair or publishing.
Its workspace changes are reports and the bounded read-only observation helper.
No historical exploitation or live funds custody inferred. Full-system and
production concurrency/activation certification are explicitly **not** claimed.

**STOP AFTER COMPLETED REPORT.** Further remediation, real operations, provider
activation and publishing require explicit approval; this sequence was not begun.

## Historical interrupted Booking discovery — superseded stop

Latest read-only architecture resumption: 2026-10-03, America/Chicago.

**Audit status: INCOMPLETE / MANDATORY NEW-FINANCIAL-P0 STOP.**

## Current resumption — accepted baselines and bounded scope

The creator explicitly approved completing the remaining read-only payment
architecture audit. These three containments remain accepted as
**FOUND AND CONTAINED / VERIFIED**:

1. Wallet transfer terminal-state / repeat-restoration containment.
2. Wallet strictly-positive amount containment.
3. Product refund repeat-credit / settlement-finality containment.

None of their internal investigations or containment suites was reopened or
rerun. Their accepted reports are the baselines, including the Product refund's
once-only accepted state and preserved original paid Order Transaction.
Production concurrency remains uncertified.

The approved remaining work includes platform/Vendor-direct collection,
provider readiness, commission/entitlement, Product-versus-Service accounting,
provider-level refunds, payouts/reconciliation, callback verification, matrices
and the 32-answer decision summary. The brief expressly requires stopping
without remediation if a **new source-confirmed, immediately exploitable
financial-mutation P0** is encountered naturally in that work.

That exception was reached while tracing **Service cancellation at the
refund/accounting boundary**. It is not a reinvestigation of the accepted
Product refund service or Wallet transfer/amount machinery.

## New P0 — reopening a canceled Wallet-funded Booking repeats Customer credit

**Finding: SOURCE-CONFIRMED / UNREMEDIATED.**

An authorized same-Shop Vendor can change a canceled Service Booking back to
`booked`, then cancel it again. For a Wallet-funded Booking within the native
cancellation calculation window, every cancellation directly increments the
Customer Wallet again. Reopening does not take another purchase debit. No
once-only financial-refund marker stops this sequential cycle.

The same-state check stops only `canceled → canceled`, not
`canceled → booked → canceled`. A database transaction around each individual
status update makes its SQL atomic but does not make repeated settlement
once-only.

### Preconditions and current policy evidence

- An authenticated Vendor owns the Booking's Shop and can use its native
  Booking-management/status route. No Admin, cross-Shop access or Staff
  authorization bypass is necessary.
- The Booking has a valid Shop, assigned Master, Customer and Customer Wallet.
- Its associated payment method tag is `wallet`. A legitimately funded Booking
  suffices; the defect does not depend on proving another payment-status flaw.
- Select a Booking whose `start_date` is later than `now - refundHour`, including
  an ordinary future Booking, and whose cancellation percentage is below 100%.
- No remaining point-history deductions are needed for the source arithmetic;
  using a Booking with no such records makes that condition explicit.

The read-only owned-development metadata observation found:

| Metadata | Observed state | Effective native behavior |
| --- | --- | --- |
| Wallet payment global activation | `active=1` | The internal Wallet rail is activated; this defect does not require enabling a dormant electronic provider. |
| `booking_refund_canceled_hour` setting | No row | The service falls back to 24 hours via `?: 24`. |
| `booking_canceled_commission` setting | No row | The lookup returns null; the in-window percentage calculation multiplies by null, producing a zero cancellation debit. |

Thus the source-derived in-window net credit in the observed configuration is
the entire Booking total on **each** cancellation. This is a policy-metadata
observation, not an executed financial operation. No identities, balances,
Booking monetary values or credential values were exposed.

### Exact native authorization and financial source chain

Here `B` means `.migration-backup/backend`.

1. `B/routes/api.php` registers the authenticated Seller role group and
   `POST /api/v1/dashboard/seller/bookings/{id}/status/update` under
   `shop.permission:bookings.manage` (Booking routes at lines 891–902).
   This is an existing authorized business operation.
2. `B/app/Http/Controllers/API/v1/Dashboard/Seller/BookingController.php:187–205`
   calls `canManageOperationalBooking($id, 'bookings.status')`, then delegates
   validated input to `BookingService::statusUpdate`. The helper at lines
   307–329 does not impose terminal-state rules. The accepted Staff boundary is
   left unchanged.
3. `B/app/Http/Requests/Booking/StatusUpdateRequest.php:18–23` validates membership
   in `Booking::STATUSES`, not a current-to-next transition matrix.
   `B/app/Models/Booking.php:109–121` includes `new`, `canceled`, `booked`,
   `progress` and `ended`.
4. `B/app/Services/BookingService/BookingService.php:379–397` loads a Booking with
   a Shop and Master, rejects only an identical requested status, and checks
   assignment. At lines 1065–1087 the Seller ownership check permits its own
   Shop's Booking. There is no rejection of reopening a canceled Booking.
5. Within that service's transaction, lines 423–438 do:
   - resolve the associated payment method tag;
   - for `wallet`, increment the Customer Wallet by `Booking.total_price`;
   - for a start date inside the calculation window, replace the debit amount
     with `total_price / 100 * canceledCommission`;
   - decrement the Customer Wallet by that calculated amount.
6. Lines 451–457 persist the requested Booking status and invoke the payable
   reversal on cancellation. A `booked` request executes neither another Wallet
   purchase debit nor a financial-refund claim.
7. `B/app/Observers/BookingObserver.php:18–32` logs creation/update; it does not
   impose cancellation finality or guard the Wallet credit.
8. `BookingService.php:674–698` uses `firstOrCreate` for the
   `payable_adjustment`, keyed by Transaction and entry type. This makes the
   **ledger adjustment** idempotent, not the preceding Customer Wallet mutation.
   It cannot prevent the repeat credit.

The Customer-facing controller independently allows only cancellation, so this
report does **not** claim that a Customer can directly reopen through that
route. The authorized same-Shop Vendor route is sufficient. An assigned Master
or Admin need not be used to establish the finding.

### Source-derived financial consequence — not an executed exploit

Let the persistently charged Booking total be `T > 0` and the in-window
cancellation percentage be `c < 100`.

Each cancellation produces:

`Customer Wallet change = +T - T*c/100`.

The initial legitimate cancellation can return the refundable amount. Moving
the same Booking back to `booked` makes no replacement debit. A second
cancellation therefore creates another positive credit for the **same original
funding**, and successive cycles repeat it.

In the observed missing-percentage configuration, `c` is effectively zero,
so each completed cancellation credits `T` net. This is arithmetic from the
source; no real Booking or Wallet was used to demonstrate it.

Unlike the accepted Product refund baseline, this Service cancellation path
does not call the guarded refund settlement service. It directly updates the
Wallet balance rather than creating a corresponding refund WalletHistory/
Transaction or once-only settled-refund record. It does not change the original
Booking payment Transaction's status. The existing paid-commerce fee entry
and once-only payable adjustment do not account for later repeated credits.

### Why this qualifies for the mandatory P0 stop

This is not merely dormant-provider readiness, an incomplete design matrix,
privileged payout accounting, or a speculative production race:

- The native authenticated Vendor action is reachable and permitted for its
  own Shop.
- Both reopening and cancellation are permitted native statuses.
- The replay results in a positive Customer balance mutation with no new
  funding or corresponding once-only guard.
- It is sequential; concurrent requests are not necessary.
- Internal Wallet is currently activated, and the observed settings allow the
  positive in-window net credit.
- The issue was encountered while answering the approved Service
  refund/accounting question, not by searching completed containment surfaces.

The accepted Product refund and Wallet containments are **not invalidated** by
this separate commerce caller's direct balance mutation.

**STOP. No remediation, provider activation, architecture continuation or
optional tasks are performed. Explicit approval is required before further
work.**

## Current pass — read-only receipt and unfinished deliverables

**All 53 protected tables retain identical table sets, row counts and
fingerprints**, with the same codec and zero changed tables. This includes
`platform_fee_ledger_entries`.

Receipts:

- `.local/payment-architecture-complete-before.json`
- `.local/payment-architecture-complete-after.json`
- `.local/payment-architecture-complete-fingerprint-result.json`
- `.local/payment-architecture-complete-policy-observation.json`
- Established serializer: `.local/payment-containment-snapshot.php`

No application source, native tests, credentials, provider configuration,
collection preference, policy, commission, financial records or workflows were
changed. No tests or containment suites, payments, Orders/Bookings, refunds,
payouts, Wallet operations, migrations, seeds or publishing were executed.
No historical exploitation was inferred or historical records repaired.

Before reaching this stop, the source trace confirmed the existing separation
between configuration choice and credential readiness in
`PaymentEligibilityService`, Shop-versus-platform merchant resolution in
`BaseService`, Booking-specific payable creation in `TransactionObserver`,
and the incomplete external-remittance nature of `PayoutService`. These are
bounded source observations, **not** a freshly completed provider-readiness
assessment or a new architecture certification.

The actual all-provider/Cameroon-XAF gate evaluation, completed provider and
current-versus-intended matrices, final 32 answers and ordered implementation
sequence remain **unfinished under the explicit new-P0 exception**. They are
not represented as completed. No further audit work proceeds past this finding.

---

## Preserved accepted containment and prior architecture evidence

The following sections are preserved historical records. Their former pause
instructions and pre-containment defects do not replace the current approval
and the new Service Booking P0 stop above.

## Latest bounded containment — Product refund repeat-credit P0

**Product refund finding: FOUND AND CONTAINED / VERIFIED.**

The approved correction makes `accepted` terminal and commits the native
financial effects with the refund's once-only accepted marker. Repeated
acceptance, backward transitions, stale models and duplicate refund requests
cannot repeat settlement. Accepted records are retained across shared deletion
and Admin bulk deletion, preventing deletion/recreation from erasing that
marker. Cancellation is terminal for that request; the native ability to submit
a new request after cancellation is preserved.

No schema change was needed. The original paid Order Transaction, Order status,
accepted Wallet containments and existing commission/payable architecture are
unchanged. False Wallet-operation results and exceptions now roll back the
claim, stock changes and related SQL financial effects instead of leaving a
partially accepted refund.

- **74 focused isolated tests / 536 assertions passed**, including 42 new
  Product refund cases and 32 selected native regressions.
- Same-Shop Vendor, granted Staff and Admin paths pass; foreign/ungranted/
  Customer settlement paths remain denied.
- A controlled two-connection SQLite claim-contention test passed. No
  production concurrency certification is claimed.
- **All 53 protected tables retain identical table sets, counts and fingerprints;
  zero changed tables**, including `platform_fee_ledger_entries`.
- Owned-development Product refund counts: **0 pending / 0 accepted / 0 canceled**.
  No historical repair or inference of exploitation.
- Native Laravel preview restarted once; clean startup confirmed.
- One unrelated pre-existing PHP 8.4 deprecation remains. No full-suite claim.

Full state machine, side effects, locking/atomicity, shared-path review,
authorization evidence, exact changed files and limitations:
[`payment-product-refund-p0-containment.md`](payment-product-refund-p0-containment.md).
Receipts: `.local/product-refund-containment-{before,after}.json`,
`.local/product-refund-containment-fingerprint-result.json`,
`.local/product-refund-history-observation.json`, and
`.local/product-refund-containment-tests.txt`.

The broader architecture audit was **not resumed**. Its matrices and 32 final
answers remain unfinished. **STOP; await explicit approval. No optional tasks
proposed.**

---

## Preserved Product refund discovery and read-only resumption record

The source chain below describes the pre-containment discovery. Its formerly
reopenable statuses and deletion paths are historical, not the current
implementation. Its read-only snapshots and partial architecture observations
remain preserved evidence.

Both Wallet containments remain accepted as **FOUND AND CONTAINED / VERIFIED**.
This pass resumed the unfinished architecture questions after that work; it did
not restart the Wallet investigation or repeat its tests. During the authorized
Product refund trace, a new source-confirmed financial mutation P0 required the
stop specified in section 17 of the current brief.

## New P0 — repeat Product refund approval creates repeat Customer credit

**Status: FOUND / NOT CONTAINED. Source-confirmed; not executed.**

An authenticated Vendor owning the Order's Shop, or an actor with the native
`payments.refunds.manage` grant for that Shop, can change the same accepted
Product refund back to `pending` and accept it again. For an otherwise valid paid
physical Product Order with an existing Customer Wallet and positive refundable
total, each acceptance calls the existing paid Customer top-up operation again.
The original Order Transaction remains `paid`; the refund path does not write
the `refund` Transaction marker that its own duplicate check requires.

This is not anonymous access, cross-Shop authorization bypass, a negative
amount, Wallet transfer cancellation, or a concurrency-only hypothesis. It is a
sequential commerce-refund finality defect reachable through an authorized
ordinary Vendor endpoint. A malicious Vendor can repeat credits to a cooperating
Customer using one paid Order, without another Customer payment. Platform Admin
privilege and live electronic-provider activation are not prerequisites.

### Native source chain

For the references below, `B` means `.migration-backup/backend`.

| Boundary | Current source evidence |
| --- | --- |
| Ordinary Vendor route | `B/routes/api.php:785–793`: `PUT /api/v1/dashboard/seller/order-refunds/{orderRefund}` is guarded by `shop.permission:payments.refunds.manage`. |
| Shop authorization remains intact | `B/app/Http/Controllers/API/v1/Dashboard/Seller/OrderRefundsController.php:76–88` checks the persisted Order's Shop and `PayableShopAuthorization`, then calls `OrderRefundService::update`. `B/app/Models/User.php:259–272` grants the Shop owner native permission; properly granted same-Shop actors also qualify. No foreign Shop is needed. |
| Request admits reopening | `B/app/Http/Requests/OrderRefund/UpdateRequest.php:17–25` accepts any `OrderRefund::STATUSES` value. `B/app/Models/OrderRefund.php:47–55` includes `pending`, `accepted`, and `canceled`; validation has no terminal-transition restriction. Returning to `pending` does not require a cancellation answer. |
| Service guard is insufficient | `B/app/Services/OrderService/OrderRefundService.php:114–175` rejects only an unchanged status and, when accepting, an existing Order Transaction with status `refund`. It does not reject reopening an already accepted refund. |
| Reset does not reverse or seal the first credit | The same service at `179–199` updates the refund status, returns immediately for a non-accepted status, and otherwise continues using the Order's paid Transaction. The first credit is not undone by setting `pending`. |
| Repeat financial effect | The same service at `256–264` calculates the refundable total and calls `WalletHistoryService::create` with `type=topup`, positive `price`, `status=paid`, and the Order's Customer. This is a legitimate positive-credit caller of the accepted Wallet baseline, not a reopening of Wallet amount mechanics. |
| Duplicate marker is never established here | The accepted branch ends at `266–269` without marking the original Order Transaction `refund`, consuming a refund-once key, or linking this refund to a unique financial effect. Its new Wallet-side records do not satisfy the earlier Order Transaction query. `OrderRefund` has no refund-finalization observer registered in `B/app/Providers/EventServiceProvider.php:82–100`. |
| No provider-based once-only backstop | This service does not invoke `PaymentRefund::paymentRefund` or obtain provider-refund proof. `refundProduct` at `317–342` adjusts the refundable total for downloaded digital content and restores Product stock; it is not a provider refund or an original-Transaction status transition. |
| Clean qualifying case avoids recovery constraints | Seller/delivery partner withdrawals are inside the delivered-Order branch at `202–254`. A paid, not-delivered physical Product Order reaches the Customer-credit branch without needing that partner reversal. This finding does not depend on insufficient seller funds, provider behavior, or manipulated amount signs. |

The inspected owned-development schema has no refund-effect uniqueness
constraint or trigger that supplies the missing once-only boundary. Request
authorization and database transaction wrapping do not supply refund finality:
separate sequential requests can each commit successfully.

### Source-derived financial consequence

Let the positive refundable total be `R`. The first accepted refund produces its
ordinary Customer credit of `R`. Reopening to `pending` changes refund workflow
status only. Accepting again produces another credit of `R`, while the original
Order still supplies the same paid Transaction. Repeating that cycle permits
further credits against the same original payment.

The excess is therefore not funded by a second Customer payment. On the
not-delivered case above, there is no matching partner-wallet debit in this
branch either. Product stock restoration also repeats, but that consequence was
not expanded into a separate investigation.

This conclusion is source-confirmed, **not a claim of a performed exploit,
observed misuse, measured live balances, or production reproduction**. No refund
request or financial mutation was submitted. No isolated mutation test was run
because the instruction is to stop and report without remediation.

### Why this invokes the new-P0 exception

- **New:** distinct from the accepted amount-sign and transfer-terminal-state
  containments, and distinct from prior cross-Shop refund authorization work.
- **Within authorized remaining analysis:** encountered while tracing Product
  refund authorization → Transaction → Wallet effects in section 11.
- **Source-confirmed:** the public Vendor route, valid statuses, persisted
  same-Shop authorization, status transitions, missing original-Transaction
  marker, and repeated positive-credit call are all visible in current source.
- **Immediately exploitable financial mutation:** an ordinary authorized Vendor
  can drive sequential repeat credits from one paid physical Product Order.
  No race, negative amount, forged provider callback, or Admin-only payout is
  needed.

**STOP. No containment, provider changes, optional tasks, or continued
architecture investigation are authorized by this discovery.**

## Bounded architecture progress before this stop

These source conclusions were carried forward or extended before the new P0.
They are not the completed architecture matrices or a runtime-readiness
certificate.

| Area | Bounded conclusion |
| --- | --- |
| Platform collection | MTN resolves platform country merchant configuration for platform-mode checkout. Flutterwave and Paystack use global platform `PaymentPayload`. Customer funds would first reach the configured provider merchant relationship, not an Admin personally. Actual receipt/custody and complete settlement were not demonstrated. |
| Vendor-direct | Shop-scoped MTN collection and frozen intent merchant/configuration verification exist. Orange has configuration storage but initiation and callbacks fail closed. The other inspected ready electronic adapters are platform-only. Vendor-direct remains partial, not a certified complete financial lifecycle. |
| Configuration versus checkout | Native configuration availability skips credential/configured/enabled requirements, but still requires global activation, ready implementation, environment permission, country policy, transaction type, declared currency capability, and Vendor-direct mode. The Shop-wide choice requires a configurable method for every Product/Service context; differing currencies veto Shop-wide MTN. |
| MTN/Orange diagnostics | MTN declares XAF, but checkout `currency_supported` also requires a matching collector row. A missing collector can therefore contribute `currency_unsupported`. Orange lacks the ready implementation and authoritative verification boundary needed for initiation. |
| Flutterwave/Paystack | Their null currency declarations mean unknown capability and fail-closed checkout, not demonstrated XAF support. Flutterwave verifies webhook hash plus provider transaction/account/reference/amount/currency. Paystack verifies raw-body HMAC and intent reference/provider/amount/currency. |
| Other callback source | Stripe checks webhook signature, provider account/session, frozen merchant, reference, paid state, amount and currency before shared settlement. PayPal delegates to its service's verified webhook; that complete service chain was not finished in this pass. |
| Shared settlement | Authenticated, merchant-verified evidence must match the provider intent, reference, payable, amount and currency. Intent/payable locks, paid-replay guards and paid-to-nonpaid rejection exist. Production concurrency was not certified. |
| Commission/payables | Paid Order and Booking observer paths record `service_fee`. Booking platform-payable entries use frozen `collect_via_platform` and `seller_fee`, without establishing provider custody. Orders do not receive that equivalent Booking-only payable entry. A complete commission-recovery and entitlement/payout lifecycle remains unproven. |
| Refunds | The Product approval path triggers the P0 above. Legacy dispatcher handlers are not a complete unified refund lifecycle: MTN/Orange are absent; Paystack uses the Flutterwave credential key and sets progress; PayPal treats a decoded array as a response object. These other observations are document-only, not separate remediation work. |
| Payouts | Inspected payout approval does not demonstrate payable allocation/reservation/provider remittance/reconciliation. Its Wallet branch credits the recipient and both explicitly debits the approver and invokes the withdrawal service. Approval is on the Admin route, so this bounded observation was not classified as another ordinary-actor P0 or expanded into a separate investigation. |
| Wallet | Both accepted containments remain the baseline. No amount/finality tests, historical amount census, transport deduplication work, precision redesign or concurrency certification were repeated. |

The exact current Shop/provider readiness evaluation was not completed before
the stop. Current configuration, activation, Cameroon policy, and actual
own-gateway-disabled gate values are **not newly certified**. Earlier historical
findings do not substitute for that unfinished current-state evaluation.

## This pass — protected-state receipt and remaining deliverables

- Established serializer used in read-only SQLite mode; same row serialization,
  sorting and SHA-256 codec as the before receipt.
- **All 53 protected tables have identical table sets, counts and fingerprints.**
- **Zero changed tables; `platform_fee_ledger_entries` included and unchanged.**
- No application source, schema, provider/settings/policy, credentials,
  collection preference, commission, balance or payable changes.
- No real payments, Wallet operations, Orders/Bookings, refunds, payouts,
  provider calls, seeds, migrations, tests, workflow changes/restarts or publishing.
- Only this report, approval-boundary notes and local read-only receipts were
  retained as changes from the audit work.

Receipts:

- `.local/payment-architecture-final-before.json`
- `.local/payment-architecture-final-after.json`
- `.local/payment-architecture-final-fingerprint-result.json`
- Serializer: `.local/payment-containment-snapshot.php`

The complete current-state provider matrix, current-versus-intended matrix,
exact disabled-option diagnosis, remaining source chains, and all 32 final
decision answers remain **unfinished because the mandatory P0 stop takes
priority**. No safe electronic/Vendor-direct activation recommendation or full
refund/payout certification is issued.

**Await explicit approval before either containment or further audit.
No remediation has been implemented and no optional task has been proposed.**

---

## Accepted Wallet containment baseline and historical audit record

The following sections are preserved prior reports. Their older audit pauses,
uncontained Wallet findings, and workflow observations describe those earlier
passes, not current defects or newly repeated verification. The latest status
and new stop are stated above.

## Accepted bounded containment — negative-amount Wallet P0

**Negative-amount Wallet finding: FOUND AND CONTAINED / VERIFIED.**
The creator approved only minimum amount-sign containment and focused isolated
verification; the broader architecture audit was **not resumed**.

Strictly positive finite native numeric amounts are now enforced in Customer
withdrawal/send requests, the original and normalized send/controller boundary,
and `WalletHistoryService::create` before histories, Transactions, observers or
balance arithmetic. The shared status boundary also refuses approval/restoration
of invalid legacy magnitudes without repairing data. The directly shared
Wallet top-up sibling now requires positive `total_price` only for Wallet-targeted
payment requests; non-Wallet checkout rules are unchanged.

The accepted transfer terminal-state/atomicity correction is preserved:
positive transfers debit/credit once, conserve value, finalize both histories
and Transactions `paid`, remain non-cancellable and roll back both legs on failure.
Legitimate pending withdrawal restoration and Admin top-up approval remain
once-only; foreign histories remain inaccessible.

- **235 isolated tests / 1,533 assertions passed** in the final combined run:
  160 new amount cases, 24 accepted Wallet transfer cases and 51 selected native
  payment/observer regressions.
- **All 53 protected tables have identical table sets, row counts and
  fingerprints**, matching codec and zero changed tables, including
  `platform_fee_ledger_entries`.
- Owned-development aggregate observation: **0 negative / 0 zero**
  WalletHistory amounts. No identities, balances or transaction details exposed.
- No real Wallet operation, provider call, seed, migration, schema/data repair,
  refund/payout, credential/policy change, frontend change or publishing.
- Native Laravel preview restarted once; clean startup confirmed.

Exact code locations, caller rationale, numeric formats, verification boundaries,
changed files and receipts:
[`payment-wallet-amount-p0-containment.md`](payment-wallet-amount-p0-containment.md).
Current receipts: `.local/wallet-amount-containment-{before,after}.json`,
`.local/wallet-amount-containment-fingerprint-result.json`,
`.local/wallet-amount-history-observation.json` and
`.local/wallet-amount-containment-tests.txt`.

No transport-level send idempotency key, production concurrency certification,
money precision redesign or historical reconciliation was added. Original
architecture deliverables remain incomplete. **STOP; wait for explicit approval
before resuming the payment architecture audit. No optional tasks proposed.**

## Preserved pre-containment architecture resumption — amount-sign P0 stop

The following source chain and read-only receipt describe the discovery pass
**before** the approved correction above. They are preserved as historical
evidence, not a claim that the current routes still admit negative amounts.

The creator accepted the Wallet transfer containment as **FOUND AND CONTAINED /
VERIFIED** and authorized resuming the architecture-focused **READ-ONLY** audit
from the Wallet accounting boundary, not restarting it. All previous
containments remain intact. No application source was changed in this pass.

At that resumed accounting boundary, the existing amount-sign contract reveals
a separate immediately exploitable financial mutation:

**An authenticated Customer can submit a negative Wallet withdrawal amount,
which increases their own spendable Wallet balance without funding. The shared
send path also accepts negative amounts and reverses the direction of transfer:
crediting the caller and debiting the selected recipient.**

This is a pre-existing amount-validation defect, **not** a regression in the
accepted completed-transfer terminal-state correction. No cancellation,
rejection, duplicate request, provider activation or concurrent request is
needed. The accepted finalization logic does not reject negative amounts at
creation time.

### Why this meets the mandatory P0 rule

The defect was encountered within the explicitly authorized Wallet accounting
and server-authoritative amount trace. It is not a new investigation of Staff,
Booking, Driver, general CRUD or unrelated permissions.

The standalone withdrawal path creates balance with no opposite financial leg.
The send path can debit another Customer without that Customer authorizing a
withdrawal. Both are direct current-source financial mutations, not merely
untested production concurrency or a provider capability assumption. Under the
creator's exception, the audit **stops without remediation**.

### Preconditions and exact source chain

`B/` means `.migration-backup/backend/`. Source references below describe the
discovery pass before amount containment; subsequent edits have shifted lines.

For the standalone withdrawal: an authenticated Customer with an existing
Wallet and an ordinary nonnegative balance; the native Wallet payment/
Transaction relationship must be available. A funded positive balance is not
required. No recipient, currency conversion or electronic gateway is required.

For the send variant: additionally an existing recipient with a Wallet, its
UUID, and an existing currency with an ordinary positive rate. No recipient
authorization or ownership of the recipient Wallet is required by this route.
No identities, balances or actual transaction values were read to establish
these preconditions.

| Boundary | Current evidence | Result |
| --- | --- | --- |
| Reachable customer routes | `B/routes/api.php:318,359–362`; `B/app/Http/Controllers/API/v1/Dashboard/User/UserBaseController.php:17–18`; `B/app/Http/Middleware/SanctumCheck.php:26–29` | Authenticated customer channel exposes withdrawal and send; no provider-activation or recipient-debit consent gate |
| Withdrawal amount validation | `B/app/Http/Requests/FilterParamsRequest.php:33`; `B/app/Http/Requests/BaseRequest.php:27–29` | `numeric`, not a required strictly positive amount; base authorization adds no amount rule |
| Send amount validation | `B/app/Http/Requests/WalletHistory/SendRequest.php:18–20` | `required|numeric`, plus existing currency/user; negative numeric amounts pass the declared rules |
| Global/API middleware | `B/app/Http/Kernel.php:45–77`; `B/app/Http/Middleware/PurifyHtmlFields.php` | No sign normalization/positive-money validation; HTML purification does not apply to `price` |
| Native withdrawal action | `B/app/Http/Controllers/API/v1/Dashboard/User/WalletController.php:66–76,85–101` | Checks only missing Wallet or `wallet.price < requested.price`; for an ordinary nonnegative balance and negative amount, this check permits the operation |
| Server-forced actor/type/status | Same controller `96–101` | Sets the authenticated sender, `withdraw`, `processed`; it does not replace the negative price |
| Service precondition and history persistence | `B/app/Services/WalletHistoryService/WalletHistoryService.php:29–39,42–58` | Truthiness/absence check rejects zero but accepts negative nonzero numeric values; persists the supplied amount |
| Real Transaction linkage | Same service `60–79`; `B/app/Traits/Payable.php:19–44` | Creates native linked Transaction; `processed` maps to `progress`; no positive-amount enforcement |
| Balance mutation | Wallet history service `81–86` | Withdrawal calls `decrement('price', history.price)`; paid top-up calls `increment('price', history.price)` with that signed amount |
| Database arithmetic | `B/vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:4103–4135` | `decrement` accepts numeric amounts and constructs column minus amount; subtracting a negative increases the balance |
| No positive-value schema backstop | `B/database/migrations/2022_08_06_193757_create_wallets_table.php:27`; `B/database/migrations/2022_08_06_193823_create_wallet_histories_table.php:22`; read-only owned SQLite `sqlite_master` definitions | Signed `double` prices; no positive-price CHECK constraint or associated Wallet table trigger in the inspected database |
| No model/observer backstop | `B/app/Models/{Wallet,WalletHistory,Transaction}.php`; `B/app/Providers/EventServiceProvider.php:84–100`; `B/app/Observers/TransactionObserver.php:25–69` | No amount-sign mutator/Wallet observer was found; Transaction observer's fee/accounting eligibility is Booking/Order only and does not validate WalletHistory amounts |
| Send inherits the same debit | Wallet controller `109–160`; Wallet history service `81–86` | Positive-rate normalization preserves a negative sign; sender subtracts a negative, recipient top-up adds a negative; both can then finalize `paid` |

### Financial effect — source arithmetic, not an executed exploit

Let `x > 0`. A supplied amount of `−x` produces:

| Flow | Caller Wallet | Recipient Wallet | Combined change |
| --- | --- | --- | --- |
| Standalone withdrawal | `A − (−x) = A + x` | No recipient leg | **+x without funding** |
| Send at positive normalized rate | `A − (−x) = A + x` | `B + (−x) = B − x` | Total conserved, but **recipient debited without consent** |

The standalone path remains `processed` / Transaction `progress` while its
balance has already increased. The send path's new terminal states can remain
consistent (`paid` / `paid`) even though the direction of value movement is
unauthorized. **Conservation alone cannot establish authorized accounting;
status finality alone cannot establish a valid positive debit.**

Native balance checks consume the Wallet's persisted `price`, not a calculation
excluding pending histories. Thus a pending history does not quarantine the
increased balance. No exploit was sent, no fixture simulation was executed and
no evidence of historical exploitation is claimed.

### Read-only receipt and mandatory stop

The established PDO serializer used `PRAGMA query_only=ON` before and after this
pass. Database inspection beyond fingerprints read only Wallet table/trigger
definitions, not financial row values or customer information.

- **53 protected tables: identical table sets, row counts and fingerprints.**
- **Serialization codec matches; zero changed tables.**
- **`platform_fee_ledger_entries` included and unchanged.**
- No application source, schema, environment, provider/country/currency policy,
  credentials, collection preference or payment settings changed.
- No HTTP payment/withdrawal/send/status request, live provider call, order,
  Booking, refund, payout, seed, migration or test was executed.
- No workflows were restarted or modified; nothing was published.

Current pass receipts:

- `.local/payment-architecture-resume-before.json`
- `.local/payment-architecture-resume-after.json`
- `.local/payment-architecture-resume-fingerprint-result.json`
- Read-only serializer: `.local/payment-containment-snapshot.php`

Only this report and durable approval-boundary notes were updated. Prior
containment verification remains accepted, not rerun or represented as testing
this newly identified negative-amount path.

### Remaining architecture deliverables

The mandatory exception prevents completing the remaining platform collection,
Vendor-direct disabled-option root cause, current Cameroon/XAF provider state,
capability/readiness matrices, commission/custody/entitlement, double-payout,
refund, settlement/reconciliation and final implementation-sequence answers.
Historical partial observations below are **not** a completed current
architecture or production-readiness certification. No optional tasks or
remediation sequence were implemented or proposed.

Carry forward the accepted Wallet containment limitations:

- Repeated send requests have **no transport-level idempotency key**; each
  remains a separate funded operation in the tested positive-amount domain.
- **No production concurrency certification.**
- No historical Wallet transfer reconciliation or normalization was performed.

**STOPPED under the mandatory financial P0 rule. No remediation.
Await explicit approval before further architecture analysis or changes.**

---

## Previously accepted Wallet transfer containment

**Latest bounded containment: Wallet transfer P0 — FOUND AND CONTAINED /
VERIFIED.** Completed transfers now finalize both histories/Transactions as
`paid` inside one transaction; customer and Admin status paths cannot independently
restore the completed sender debit. Failed transfer service results roll back
both legs; genuine pending non-transfer cancellation remains available once.
No schema or historical-data rewrite was required.

See [`payment-wallet-transfer-p0-containment.md`](payment-wallet-transfer-p0-containment.md)
for 24 new Wallet tests / 183 assertions, 82 existing focused regressions /
722 assertions, unchanged fingerprints for all 53 protected tables including
the fee ledger, exact changes and limitations. **106 distinct tests /
905 assertions passed across focused runs; the full suite was not rerun.**
The latest authorized resumption stopped on the separate amount-sign P0 above.
The source chain below records the **pre-containment** defect, not a claim that
newly completed transfers still have that lifecycle.

**Historical architecture-pass stop:** The architecture-prioritized
resumption encountered a new source-confirmed, immediately exploitable financial
mutation during the explicitly authorized Wallet accounting trace. No exploit
was executed, no application fix was made and no provider was contacted.
The original stop evidence is retained below as history.

## Executive decision for the architecture-prioritized resumption

The completed cross-Shop payment/refund and Booking Staff containments remain
**FOUND AND CONTAINED / VERIFIED**. Their accepted 67-test / 633-assertion baseline
was not rerun, reopened or altered.

The new issue is **completed Wallet transfer debit cancellation without
recipient-credit reversal**. It violates money conservation even when the sender
only touches their own history. It is not an expansion into Booking notes/time,
Staff, Driver, Pickup, scheduling or general CRUD authorization.

The user's mandatory P0 exception takes priority over finishing the remaining
architecture questions. Therefore this pass does **not** certify electronic
collection, Vendor-direct, Cameroon/XAF readiness, refund/payout correctness,
concurrency safety or production readiness. It does not provide a completed
provider/current-vs-intended matrix or all 32 final answers. Those deliverables
remain incomplete; no unsupported conclusion replaces the unfinished work.

## New P0 — a completed Wallet transfer can create unbacked balance

### Routes and actors

- `POST /api/v1/dashboard/user/wallet/send` performs the transfer.
- `GET /api/v1/dashboard/user/wallet/histories` exposes the sender's own debit
  history UUID.
- `POST /api/v1/dashboard/user/wallet/history/{uuid}/status/change` accepts
  cancellation/rejection of that debit.

Preconditions: an authenticated account with a funded Wallet, an existing
recipient account with a Wallet, and a valid currency with a usable rate.
No Seller/Staff/Admin privilege, foreign history discovery, concurrent requests,
electronic-provider credentials or active electronic gateway is required.
The reachable route chain checks authentication, not payment-catalog activation
or provider environment readiness.

### Missing accounting boundary

A successful send credits the recipient immediately but leaves the sender's
withdrawal history **`processed`**, the very state accepted by cancellation.
There is no completed-transfer state boundary or paired transfer identity in
this flow that prevents reversal of only the sender leg.

Cancellation changes the debit history and its Transaction to `canceled` and
credits the sender's Wallet. It does not inspect or reverse the recipient top-up.
The recipient's already-paid history and credited balance remain.

The outer send transaction makes the initial debit/credit atomic; it does not
protect the later, separately callable cancellation. An ownership check alone
would not fix this defect: the sender can use their **own** history UUID.

### Exact current source chain

All `B/` paths below mean `.migration-backup/backend/`.

| Evidence | Current source |
| --- | --- |
| API `v1` → dashboard → user prefixes; authenticated route admission | `B/routes/api.php:20,297,318,359–362`; `B/app/Http/Middleware/SanctumCheck.php:26–36`; `B/app/Http/Controllers/API/v1/Dashboard/User/UserBaseController.php` |
| Send validates numeric price, existing currency and recipient; no completed-transfer authority is introduced | `B/app/Http/Requests/WalletHistory/SendRequest.php:15–21` |
| Sender balance check, then forced `processed` / `withdraw` / authenticated sender | `B/app/Http/Controllers/API/v1/Dashboard/User/WalletController.php:84–100` |
| Send converts the amount, calls that withdrawal, then creates recipient `paid` / `topup`; no subsequent sender-finalization call | same controller, `108–177`, specifically `120–145` |
| Histories persist their supplied status; withdrawal decrements sender; paid top-up increments recipient | `B/app/Services/WalletHistoryService/WalletHistoryService.php:42–86` |
| Sender can retrieve their own histories, including UUID | Wallet controller `44–57`; `B/app/Http/Resources/WalletHistoryResource.php:21–29` |
| Customer cancellation accepts `rejected` or `canceled`, forwarding the UUID | Wallet controller `184–199` |
| Service accepts a `processed` history, normalizes cancellation, updates history/Transaction and restores withdrawal balance, without touching the transfer recipient | Wallet history service `100–136` |
| Status spellings are compatible: WalletHistory and Transaction both use `canceled` | `B/app/Models/WalletHistory.php`; `B/app/Models/Transaction.php` |

The WalletHistory model and inspected observer registrations do not add a
paired-transfer reversal/finalization boundary. The paid Transaction observer
only records Booking/Order fee/payable entries; it does not reverse this
WalletHistory recipient credit.

### Financial impact — source-derived, not executed

For a positive, rate-normalized transfer amount `x`, with starting balances
`A` and `B`:

| State | Sender | Recipient | Combined |
| --- | --- | --- | --- |
| Before | A | B | A + B |
| Successful send | A − x | B + x | A + B |
| Sender debit cancellation | A | B + x | A + B + x |

This creates spendable internal balance without matching funding. A fresh
successful send followed by cancellation can repeat the defect; this does not
depend on canceling the same already-canceled row repeatedly. Provider disabling
does not guard these Wallet endpoints. Actual external withdrawal or payout,
production exposure and existing affected amounts were not established.

**Classification:** new source-confirmed, immediately exploitable financial P0,
not merely an untested concurrency risk or a speculative provider capability.
The arithmetic is an explanation of current source behavior, not an isolated
test result or an exploit receipt. No transfer, withdrawal, cancellation, status
change, refund, payout or balance mutation was executed against any database.

## Architecture observations rechecked before the mandatory stop

These are bounded source observations, not a completed readiness assessment:

| Area | Rechecked conclusion |
| --- | --- |
| Platform credentials and first recipient | MTN's resolver selects platform country configuration when platform collection is selected; Flutterwave/Paystack initiation reads platform PaymentPayload. Funds would first reach the configured merchant relationship, not an Admin personally. Actual collection was not demonstrated; Stripe/PayPal were not retraced in this pass. |
| Vendor-direct | Shop-scoped MTN credential resolution exists. Orange has credential storage/legacy code but initiation fails closed. Other ready electronic adapters are platform-only. This is partial implementation, not complete Vendor-direct accounting/refund/payout certification. |
| Own-gateway selectability | Frontend consumes backend `vendor_direct_available`; collection policy requires a configurable method in every relevant Product/Service context. Registration does not require existing credentials, but does require global activation, environment permission, country permission and declared currency capability. Shop-wide MTN currency conflict is an additional veto. The exact current Shop decision was not evaluated before the stop. |
| Cameroon/XAF declarations | MTN explicitly declares XAF for Vendor-direct capability; checkout also requires a matching collector currency row. Orange lacks a ready integration/capability declaration. Flutterwave/Paystack currency lists are `null`, so their XAF capability is unknown in this codebase. Current activation/configuration/country readiness was not freshly queried. |
| Diagnostics | MTN `currency_supported` combines declared capability with a matching collector row. Thus `currency_unsupported` can reflect missing collector configuration, not absence of XAF capability. The cause depends on the particular context/query branch. |
| Authoritative success | Shared settlement requires normalized authenticated/merchant-verified evidence with reference, provider, target, amount and currency matching; provider intent and payable locks plus paid-replay guards exist. MTN polls the resolved merchant and checks a credential/config fingerprint; Flutterwave checks webhook hash plus independent API verification; Paystack checks raw-body HMAC. No live or concurrent certification occurred. |
| Entitlement and commission | Paid Booking/Order hooks record `service_fee`; Booking platform-payable entries use frozen collection preference and `seller_fee`. Product Orders do not get that Booking-only payable entry. The hook itself does not prove possession of funds, collect commission debt or execute a payout. |
| Refunds/payouts | Existing payout code is not a complete payable-ledger allocation/reservation/remittance lifecycle. Refund dispatcher and commerce reversal paths remain to be completed in this architecture pass. No external refund or payout was attempted. |
| Wallet | Real internal balances and histories exist, but the transfer/cancellation lifecycle fails conservation as documented above. Safe Wallet accounting cannot be certified. |

Evidence areas: `B/app/Services/PaymentEligibility/{PaymentEligibilityService,PaymentContextFactory}.php`,
`B/config/payment_eligibility.php`, `B/app/Services/PaymentService/{BaseService,MtnService,OrangeService,FlutterWaveService,PayStackService}.php`,
the corresponding payment controllers, `B/app/Observers/TransactionObserver.php`,
and `B/app/Services/PayoutService/PayoutService.php`.
Historical architecture findings are not automatically current without retracing.

## Financial-state protection and final stop receipt

- Opened the existing owned development SQLite database through the established
  PDO serializer with `PRAGMA query_only=ON`.
- **53 protected tables; identical table sets, row counts and fingerprints.**
- **Codec matches; zero changed tables.**
- **`platform_fee_ledger_entries` included and unchanged.**
- Only source/report/approval-note inspection and read-only snapshots occurred.
  No credential contents were inspected or printed. No application code, schema,
  settings, capability declarations, collection preferences or providers changed.

Receipts:

- `.local/payment-architecture-resume-before.json`
- `.local/payment-architecture-resume-after.json`
- `.local/payment-architecture-resume-fingerprint-result.json`
- Serializer: `.local/payment-containment-snapshot.php`

No tests, mutating HTTP requests or provider calls were run. Existing workflows
were not restarted or changed. No full-suite or runtime readiness claim is made.
The architecture remediation sequence has not been completed or proposed as
tasks because the mandatory stop occurred first.

**STOPPED. Await explicit approval before containment or further audit.
No optional workstream, implementation, provider activation or publishing.**

---

## Historical audit and containment record

**Latest approved Booking containment (2026-10-03):** the operational Staff
Booking target-Shop boundary and matching adjacent priced-extra-time/deletion
boundaries have been contained and verified using isolated fixtures. See
[`payment-booking-staff-p0-containment.md`](payment-booking-staff-p0-containment.md)
for native actor/branch/country semantics, 67 focused tests / 633 assertions,
and unchanged fingerprints for all 53 protected tables including the fee ledger.
The broader payment audit remains **PAUSED**. No P1, provider architecture,
activation or configuration work was performed. The prior stop report below
remains historical evidence, not a claim that the contained path is still open.

**Latest resumed read-only pass:** the creator authorized resuming against the
approved platform-default/optional Vendor-direct collection model. The audit
stopped again on a newly confirmed Staff-role cross-Shop Booking lifecycle
authorization defect with wallet/platform-payable effects. See
[`payment-audit-resumed-p0-stop.md`](payment-audit-resumed-p0-stop.md)
for the exact source chain, role preconditions, mandatory stop and unchanged
53-table fingerprints including the fee ledger. No application remediation,
P1 fix, provider configuration or financial mutation occurred in that pass.

**Subsequent approved containment:** the Seller transaction-status authorization
boundary and a matching Seller refund-mutation boundary have now been patched
and verified in isolated fixtures. See
[`payment-p0-authorization-containment.md`](payment-p0-authorization-containment.md)
for current behavior, tests and 53-table fingerprints including the fee ledger.
The original source line references and stop recommendations below describe the
pre-containment audit state, not a claim that the patched status path remains
vulnerable. The full audit resumed only until the new P0 stop above; production
safety is not certified. The remaining original report below is historical.

The creator approved hydration/favicon correction followed by a read-only
payment audit. The payment brief expressly requires stopping if an immediately
exploitable P0 makes continuing the preview unsafe. This report records that
stop, not a completed architecture certification.

## P0: seller can change another shop's cash payment status

**Evidence classification:** source-confirmed authorization defect, with
non-mutating local configuration/aggregate confirmation. No forged request or
financial write was performed.

Affected route:

`PUT /api/v1/payments/{type}/{id}/transactions`

### Exact authorization and mutation chain

1. `backend/routes/api.php:291–295` registers this general endpoint beneath
   `sanctum.check`, not seller shop-permission/country middleware.
2. `backend/app/Http/Middleware/SanctumCheck.php:26–36` checks authentication
   only.
3. `backend/app/Http/Requests/Payment/TransactionUpdateRequest.php` inherits
   `BaseRequest::authorize()`, which returns `true`. Validation permits
   `paid`/`canceled`; a process token is not required.
4. `backend/app/Http/Controllers/API/v1/Dashboard/Payment/TransactionController.php:157–176`
   permits `admin` or `seller`, then loads the target by unrestricted primary
   key. No actor-shop ownership condition is applied.
5. Lines 189–209 deny seller overrides for non-cash, but expressly allow cash.
   This is not a check that it is **the seller's own** cash transaction.
6. Lines 211–233 accept missing process evidence for cash, then directly update
   the transaction's status. The store method's owned-target eligibility check
   at lines 42–46 does not run for `updateStatus`.

All backend paths above are relative to `.migration-backup/`.

### Preconditions and impact

- Requires a valid account with the native `seller` role and an existing cash
  payable ID. It is not an anonymous bypass.
- A read-only local aggregate confirmed cash is globally active and there are
  three cash transactions. No transaction values or owner identities were
  displayed.
- An authenticated seller can target another shop's order/booking cash
  transaction and request `paid` or `canceled` without that shop's grant.
  This is unauthorized financial-state modification even though cash is an
  offline rail.
- `TransactionObserver::created/updated` reacts to paid Booking/Order
  transactions. Where applicable fee fields are positive, the unauthorized
  transition can also produce fee/payable bookkeeping. It does **not** prove
  that cash was received or that an external payout occurred.
- External-provider disabling does not contain this endpoint. Global method
  activation is not checked by its status-update path either.
- The comment “A seller can still confirm their own cash bookings” describes
  intended policy but is not an enforced ownership condition.

This fits the brief's P0 example, **unauthorized payment status changes**.
The missing check is deterministic in the current source. Exploit execution,
actual affected financial amounts, production configuration and production
exposure were deliberately not investigated.

## Required decision before continuing

Do not regard the preview as safe for financial-status operations by
untrusted seller accounts. Do not enable live providers or publish on the
strength of this partial audit.

No endpoint was disabled, preview shut down, role changed, financial row
modified or patch implemented. Such containment/remediation requires separate
approval. The minimum proposed correction would bind the payable to the actor's
authorized shop and payment-management grant before any mutation, retaining
properly authorized administrative behavior. Verification should use isolated
fixtures to prove cross-shop and ungranted denial with unchanged transaction
and ledger fingerprints, alongside legitimate own-shop confirmation.

This is a recommendation only, not a new task or implementation phase.

## Read-only evidence and immutability receipt

- Opened the owned development SQLite database with PDO and
  `PRAGMA query_only=ON`.
- Before/after snapshots used the identical serializer: associative rows,
  JSON with unescaped Unicode/slashes and preserved zero fractions, byte-sorted
  serialized rows, newline join, SHA-256.
- **52 selected financial/configuration/business tables have identical counts
  and fingerprints.** Included transactions, orders, bookings, wallets,
  wallet histories, payouts, refunds, provider configuration, country payment
  policy, currencies, shops and settings.
- Local receipts: `.local/payment-audit-before.json`,
  `.local/payment-audit-after.json`; reproducible read-only serializer:
  `.local/payment-audit-snapshot.php`.
- Scope limitation: the selector did not include `platform_fee_ledger_entries`;
  its immutability is not independently proven by those snapshot receipts.
  No audited financial service, callback or status-mutating endpoint was
  executed.
- No provider requests, payments, order/booking submissions, refunds, wallet
  changes, payouts, financial seeds, configuration updates or publication.
- Credential contents were not inspected or printed.

## Partial architecture observations established before the stop

These are current source observations, not a substitute for the remaining audit.

| Area | Observation |
| --- | --- |
| Eligibility | `PaymentContextFactory` resolves owned checkout targets and native product/service country/currency. `PaymentEligibilityService` intersects activation, integration readiness, country policy, transaction type, currency, collection mode, collector configuration and environment. |
| Country exceptions | Cash/wallet retain the explicit `legacy_compatibility` policy. Assignment is not provider activation or merchant certification. |
| Registration versus charge | `available_for_configuration` is separate from `eligible`; neither is proof of successful onboarding or collection. |
| Shop collection | One shop-wide preference applies to products and bookings. Multi-context MTN currency conflicts can make merchant-direct unavailable. |
| MTN/XAF | XAF is explicitly in `vendor_direct_currencies.mtn`; absence of a matching enabled collector row may still yield `currency_unsupported`. That reason is not a general claim that MTN cannot support XAF. |
| Orange | Absent from the readiness allowlist; initiation explicitly fails closed because authoritative callback/status verification is incomplete. |
| Flutterwave/Paystack | Adapter readiness is represented, but currency declarations are `null`, deliberately making charge currency support unknown rather than claiming checkout readiness. |
| Callback settlement | `BaseService::afterHook` requires normalized authenticated/merchant-verified evidence, exact reference/provider/payable/amount/currency, transaction scope and intent/payable locks. Same-reference paid replays return before settlement. Concurrent production correctness was not exercised. |
| Accounting | `TransactionObserver` records fee entries for Booking/Order and platform-payable entries for Booking only. It does not establish a complete platform custody, refund or payout lifecycle. |
| Payout | Existing `PayoutService::statusChange` checks/writes without a complete reservation lifecycle, and uses the approving account's wallet rather than a demonstrated vendor-liability allocation. This needs further audit; no payout was attempted. |
| Credentials | Shop/platform mobile-money models hide credential fields and encrypt newer secret columns. Global `PaymentPayload` remains an array-cast container. Full credential security and historical exposure review are unfinished. |

The prior `payment-architecture-audit.md` is historical and predates subsequent
foundation work. Its old ownership, toggle and country-resolution findings
must not be treated as current without retracing them.

## Not completed because the mandatory stop takes priority

The complete current provider matrix; Le Sawa's exact runtime merchant-direct
decision; remaining Admin/Vendor/Web/Flutter configuration and payment flows;
gift/membership/other contexts; complete cash/wallet, invoice, refund, payout,
commission and source-of-truth map; full seed/test inventory; all requested
question answers; and ordered architecture/remediation phases remain
unfinished.

No claim of full payment-audit completion, safe online payment readiness,
verified refund/payout behavior or production certification is made.

## Existing verification warning observed at handoff

The configured `original-hardening` workflow is failed: 220 tests,
11 errors, 3 failures, 2 deprecations. Reported failures concern Delivery Driver
invitation/verification fixtures and native pickup snapshot persistence.
These were not diagnosed or repaired in this bounded request, and the suite
was not rerun for the payment audit. It cannot be described as a green full
regression baseline.

**Stopped. Await explicit approval before containment, remediation or resuming
the payment audit.**

## Approved Payment Phase 1 — financial correctness verification (2026-10-03)

**P1-A payout conservation/atomicity: CONTAINED / VERIFIED.**

Current native tracing confirmed early separately persisted acceptance, ignored
required service results, and two approver debits in Wallet payout. Approval now
uses a locked native payout plus stable-order Wallet locks, conditional accepted
claim, exactly one paid credit/withdrawal pair, checked required histories/
Transactions/summaries/finalization, and one encompassing database transaction.
False results and exceptions roll back. Accepted financial terms cannot be
reset through generic/stale updates; Admin/Vendor replay cannot repeat effects.
Existing non-Wallet payout acceptance remains bookkeeping, not provider remittance.

**P1-B alternate Booking electronic paid authority: CONTAINED / VERIFIED.**

The generic Booking transaction endpoint no longer treats active/configured/
eligible provider selection as payment proof. It allows native Cash/Wallet only
and uses persisted ownership/amount plus existing authoritative context.
The same electronic-paid rule covers related manual status and Booking-ended
payment transitions. Legitimate commerce lifecycle remains allowed while
electronic payment stays pending. Existing provider verifiers remain the only
trusted electronic success path; real native MTN verifier/settlement was
demonstrated with synthetic transport and no provider call.

Receipts: **44 focused tests / 429 assertions**, **454 selected accepted payment/
provider regressions / 3,066 assertions**, one existing deprecation. Selected
regressions preserve Product fulfillment/refund, Booking refund/Staff, Seller
ownership, Wallet transfer/amount and observer containments.
The complete project suite was not rerun and is not claimed green.

Two independent SQLite 3.51.1 connections exercised the full competing native
payout service. One conserved batch commits; the contender cannot settle and
post-commit replay has no effect:
**SQLITE CONCURRENCY VERIFIED; PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.**

No schema, provider configuration/activation, readiness, country/currency,
allocation/custody, refund, external payout or UI implementation was added.
All **53 protected tables**, explicitly including `platform_fee_ledger_entries`,
have unchanged current post-P0-B counts and full-row fingerprints. All **12
legacy Orders remain unverified**. No real financial operation or publishing.

Detailed current-source chains, exact production changes, failure/replay evidence,
complete protected-state matrix and deferred architecture interactions:
[Payment Phase 1 financial correctness](payment-phase1-financial-correctness.md).
Earlier findings remain historical source evidence; these two contained paths
do not imply external settlement capability or electronic checkout readiness.

**STOPPED. Await explicit approval before any subsequent payment phase.**

## Approved Payment Phase 2 — schema gate decision (2026-10-03)

**OUTCOME B: SCHEMA REQUIRED. STOPPED BEFORE MIGRATION.**

Phase 1 is accepted and unchanged. The authorized read-only current-source/schema
trace confirms Product lacks an immutable collector snapshot, Booking has only a
server-derived creation-time routing boolean, and ledger uniqueness is
`(transaction_id, entry_type)` rather than one retained economic obligation.
Provider intent verification is not a common Cash/Wallet/provider allocation
identity. Transaction cascade deletion and method/replacement/contribution keys
prevent treating the current fee ledger as that durable boundary.

The four collection concepts map to native `platform`, `vendor_direct`, `offline`
and `internal`; method/provider is separate. Cash/direct principal already held by
Vendor must not become a duplicate platform payable. Wallet liability is not
established merely by safe internal movement. Mixed Wallet/direct custody was
unresolved at the initial schema stop. The creator has since supplied the explicit
platform-held-funds-first commission policy: commission once per Shop obligation;
held funds satisfy it first, remaining held Vendor entitlement is payable, and
unsatisfied commission is Vendor receivable. Vendor-direct/Cash principal never
becomes a duplicate platform payable.

Current-source clarification of earlier accounting shorthand:
Booking seller fee subtracts `service_fee`, `commission_fee`, `coupon_price`;
Order seller fee subtracts conditional IN_HOUSE delivery, service fee, commission,
coupon and tips, not a separate total-tax deduction in that accessor. Native fee
rules are preserved; no economic formula or beneficiary policy was changed.

Minimum proposal: retained per-Shop `commerce_payment_allocations`, immutable
`payment_collection_contexts`, nullable allocation links on Transactions/fee ledger,
economic effect-key uniqueness and non-cascading retained financial provenance.
Cart-origin allocations bind once to their Shop Orders; existing electronic
multi-Shop fail-closed gates are preserved, not replaced with a new split-payment UI.
The proposal specifies states, ownership, amounts/currency, safe merchant/config
references, commitment/verification lifecycle, immutability, SQL claims, API
privacy, legacy handling and non-destructive rollback.

No application files, configuration, migration or schema changed. No temporary
implementation tests were used to claim accounting completion. Accepted P0/P1
production code was left untouched; prior test receipts remain prior receipts.
All **53 protected table counts/full-row fingerprints** match the current
post-Phase-1 baseline, including the fee ledger. All **12 legacy Orders remain
`unverified`**. No financial operation/provider call/activation or publishing.
New allocation concurrency is untested; production-engine concurrency remains
**NOT CERTIFIED**.

Full current-source trace, custody matrix, minimum schema proposal, remaining
limitations, protected-state receipt and all 21 requested decisions:
[Payment Phase 2 collection/allocation](payment-phase2-collection-allocation.md).

**STOPPED. Await explicit schema approval; do not implement a workaround,
create a migration, enable Vendor-direct/own gateway or start Phase 3.**

### Final schema design revision — conceptual approval only (2026-10-03)

Two-layer direction is conceptually approved, **migration is not authorized**.
The Phase 2 report now gives exact fields/types/FKs/CHECKs/unique constraints,
server ownership, immutable economic/funding snapshots and separate state
machines. Original receipt anchors provide global identity without denying
legitimate shared receipts; funding slots and economic base effect keys prevent
replacement/callback duplication. Commission is calculated once, then assigned
against platform-held value before Vendor payable; residual commission is receivable.

Worked complete/partial/full-refund examples cover all four modes, Wallet mixtures,
insufficient held funds, unsupported hypothetical electronic mixtures, replacement,
duplicate callback and Shop-isolated checkout. Future refund effects reference
original contexts, preserve original proportions and separately authorize fee
reversal. Future settled amounts/recovery effects determine remaining payable
without negative Vendor liability or current settings. None of these are implemented
refunds, external payouts or runtime accounting/concurrency tests.

The initial `(allocation_id,entry_type)` proposal is **superseded** by
`(allocation_id,effect_key)` so multiple legitimate partial reversals remain
possible. Nullable additive links/exact effect columns leave historical records
unmodified; no migration backfill, inference or override is allowed.

All 53 protected table full-row fingerprints/counts and schema remain unchanged;
all 12 Orders remain fulfillment-unverified. No production code/migration/config/
provider activation/financial operation/publishing. Stop at finalized design,
await explicit migration approval; provider readiness remains later work.

### Payment Phase 2B — approved baseline, material identity gate (2026-10-03)

The creator now approves the finalized two-layer schema migrations and bounded
native accounting implementation, subject to explicit STOP for material schema/
identity deviations. Provider/configuration readiness, Vendor-direct enablement,
provider refunds, external payout rails, historical financial classification/
deletion, production/publishing and automatic Phase 3 remain outside approval.

**STOPPED BEFORE MIGRATION.** The approved origin unique key omits checkout
generation. Native `CartService` updates an existing owner's Cart in place;
`BaseService::beforeCart` quotes its current contents; verified rejection does
not create Orders or delete the Cart. `CartOrderService` deletes it on successful
Order creation, not on a failed electronic attempt.

Consequently `(cart,CartID,ShopID,base,base)` remains occupied by a canceled,
unfunded quote. After Customer quantity changes, a legitimate new quote on the
same Cart cannot create a fresh allocation without breaking uniqueness,
immutability or terminality. This is a design correctness gap, not a new P0.

Minimum proposal: include the existing server-owned `checkout_key` in origin
uniqueness, retain bound Order/Booking uniqueness, and require serialized checkout
generation selection plus authoritative no-value/no-unresolved-receipt
cancellation before a fresh Cart generation. No new table/column; no changed
commission/custody formula. Because identity changes, it is **not implemented**.

Source chain, counterexample, reduced SQLite in-memory unique-key reproduction,
minimum proposed key and safety rules are in section 12 of the Phase 2 report.
That reproduction is not an accounting/concurrency/integration certification.
No migration file/application changes, provider operations or financial tests
against existing data. All 53 protected full-row fingerprints/counts and schema
unchanged; all 12 legacy Orders remain fulfillment `unverified`. Native financial
values preserved; intentional migration/backfill changes **none**.

**Await explicit checkout-generation identity approval before continuing Phase
2B. Do not begin provider readiness or Phase 3.**

### Payment Phase 2B — development migration and bounded accounting receipt (2026-10-03)

The creator subsequently approved the checkout-scoped origin key. The historical
gate above is therefore superseded by section 13 of
`payment-phase2-collection-allocation.md`, which contains the exact changed-file
inventory, implementation/lifecycle details, limitations and all 23 final answers.

The three reviewed `2026_10_03_100*` migrations applied successfully to development
SQLite: economic roots, immutable contributions, nullable Transaction/effect links,
durable effect identities and audit-retention FKs. Existing Transaction/type fee
uniqueness remains. No other material schema deviation was adopted. No production
migration, provider configuration/activation, own-gateway enablement, external
payout, provider refund, historical classification/deletion or publishing occurred.

Canonical recognition is once per fully funded/bound Shop obligation, not once
per Transaction. Held funds satisfy commission first; only the remaining authorized
held amount is Vendor payable. Vendor-direct/Cash principal is never duplicated
as platform payable. Verified Customer Wallet withdrawals are platform-controlled
internal funding. Frozen custody/configuration identity, global receipt claims,
funding-slot/economic/effect uniqueness and transactional groups govern replay.
Separate refund/settlement/receivable effects preserve original contribution shares
and determine remaining liability/recovery without negative Vendor payable.

Product/Booking adapters use this shared core. Cart roots bind once after native
Order totals reconcile; Booking roots retain their native routing while accounting
uses frozen context. Linked fulfillment suppresses fabricated Admin gross credits;
linked legacy cancellation/refund/Partner-transfer routines require future paired,
authorized effect operations rather than silently using the old gross model.

**Implementation is bounded, not universal checkout/provider readiness.** Current
native adapters reject unproven non-unit FX, adjustment/commission/tax contracts,
coupons/tips/delivery responsibilities and supplement quotes instead of inventing
beneficiaries. The arithmetic and focused Cash/Wallet/Booking verification paths
are exercised; every real Product Cart journey and provider retry/reconciliation
path is not certified. Source-proven broader quote coverage, native journey
verification and target production-engine certification remain explicit limits.

Isolated synthetic verification:

- Phase 2B focused: **30 tests / 255 assertions**, passing.
- Phase 1 focused: **44 / 429**, passing.
- Selected accepted regressions: **454 / 3,066**, passing, one existing deprecation.
- Five controlled two-connection SQLite tests exercise allocation creation,
  contribution recording, confirmation/base effects, refund groups and settlement
  groups under overlapping writes, followed by retry/replay.
- SQLite contention is **not** a real-provider load test or production certification.
- The separate broad `original-hardening` workflow remains failed; these selected
  receipts are not a claim that the entire project test suite is green.
- Reviewed development-manifest admission restored the native backend preview;
  backend settings and the customer frontend returned HTTP **200**.

Protected receipt: all **53 original tables' original columns/values and counts**
match baseline SHA-256
`d500edda9dcb1448ac7f82090fe4b7f35a6326246ddf35831d7af765da45bd56`
after projecting out only intentionally added nullable fields. Full-row
representations changed intentionally; unprojected fingerprints are not claimed
identical. New roots **0**, contributions **0**, historical links/effect metadata
**NULL**, foreign-key errors **0**. All **12 Orders** remain fulfillment financial
state **`unverified`**. No original financial value changed or financial test
operation ran against existing development records.

**STOP. Await explicit approval for further coverage/readiness work. No automatic
Phase 3 or publishing.**

## PHASE 2C — NATIVE CHECKOUT / QUOTE COVERAGE

2026-10-03. Continued from the existing checkpoint without restarting the payment
architecture audit or repeating completed Phase 2B work.

The complete Phase 2C native entry-point inventory, trusted Product/Booking
contracts, exact changed-file list, test boundaries and all **30 required final
decisions** are in `payment-phase2-collection-allocation.md`, section **14**.
That section is the detailed implementation receipt; the historical Phase 2B
receipt above is not substituted for Phase 2C verification.

### Native paths and outcomes

| Native path | Phase 2C outcome |
| --- | --- |
| Cart/Stock/Shop pricing and Cart-to-Order | Actual native calculation freezes Shop allocation, line identity/quantity/net money and service fee; real Order creation binds original Cart context. Paid contribution precedes financially meaningful finalization. |
| Native Order/POS and generic Cash/Wallet | Completed native price is quoted; generic Wallet prepares owned-currency context before debit/history and paid flags. Cash has offline Vendor custody. |
| Booking Service/Master/branch selection and creation | Actual repository calculation/creation verified; payer, branch, country/currency and priced original obligation retained. |
| Same-Shop multi-Service Booking | Real native Wallet creation succeeds for two original distinct Booking obligations sharing checkout; each original service fee recognized once. No identity deviation was needed. |
| Booking generic payment/status/lifecycle | Original authority retained; Wallet proof precedes paid status; lifecycle `ended` is not electronic/Wallet settlement proof. |
| Provider selection, intent, MTN verification, `afterHook` | Eligibility/pending do not fund. Native verifier/controller and native Cart-to-Order/Booking finalization exercised synthetically. Actual provider lookup/merchant/network are synthetic; none activated. |
| Platform/direct and Wallet+electronic | Both Cart and Booking tested for platform, direct, Wallet+platform and Wallet+direct. Held-first commission; direct principal never platform payable. |
| Checkout retry/cancellation and multi-Shop | Same quote retries retain key; canceled/no-collection changed Cart gets a new retained generation. Changed active quotes rejected, including different goods at same gross. Shop allocation/binding/Cash and core shared receipts are isolated. |
| Extras, taxes, coupon, tips, delivery, benefits, FX | Source-priced upfront Service extras remain Vendor entitlement. Unproven adjustment beneficiaries/sponsors/bases, nonunit rates and currency mismatch fail closed. No inferred FX/currency policy. |
| Refund/cancellation and partner/payout helpers | Linked canonical money cannot fall through legacy financial helpers. Original-custody paired effects and remaining held liability required; operational refund/payout adapters remain gated. |

### Quote and financial policy

Product authority is the persisted owned native line/accepted quantity/net price
calculation plus Shop country/invoice currency, original fixed service fee and
final gross. Booking authority is the persisted priced Service/Master/branch,
user/local-client, country/currency, native discount, source-priced extras,
service fee and final total. No customer gross, paid flag or current configuration
reconstructs original economic authority.

Supported contracts freeze integer units at scale **2**, `G=C+E+A`,
`C=original native service fee`, `E=G-C`, `A=0`. Vendor listing net prices/extras
and platform service fee have their original source owners. Unproven Product/Shop
tax, coupon sponsorship, tips, nonzero delivery, Master commission,
gift/membership, cashback/bonus, extra-time/repricing or FX are not assigned
guessed beneficiaries. Important catalog cases with nonunit native rates remain
unavailable; XAF support is not universal catalog/settlement FX certification.

Cash contributes Vendor-custodied principal and commission receivable, no
platform principal payable. Actual Wallet withdrawal/history establishes
platform custody. Verified electronic amount/currency/reference/configuration
establishes one retained contribution. Held funds satisfy commission first;
remaining held funds alone become payable. Replacement Transactions, callbacks,
current settings and legacy gross-credit helpers do not multiply original money.

Verified provider completion, Order binding, paid status and canonical effects
are atomic. A failed native Order finalization propagates for rollback and safe
original-intent retry. Linked electronic Transaction price remains contribution
money, not overwritten gross; Cart Transactions are not remorphed arbitrarily.

### Verification and limits

- Focused Phase 2C: **78 tests / 532 assertions**, pass.
- Selected Phase 1 containment: **44 / 429**, pass.
- Selected regressions: **454 / 3,066**, pass.
- One existing nullable-parameter PHP deprecation in focused/regression results.
- Actual native Cart-to-Order Cash/Wallet, single/multi-Service Booking Wallet,
  generic native payments and synthetic MTN/native completion/mixed funding
  exercised in disposable schema-cloned fixtures.
- Real Wallet debit with injected fee failure before/after insert rolls back
  Wallet, history, Order/Stock, quote/contribution and fee effects. Verified
  provider effect failure preserves pending original intent; subsequent retry
  completes exactly once. Wrong proof and unsupported terms create no funding.
- Core two-connection creation/contribution/confirmation/refund/settlement
  contention plus native Cart retry/Cash finalization contention pass. Lock
  rejection and idempotent retry are SQLite evidence only. Production race,
  deadlock and referential-integrity certification are not claimed.
- Auth/framework plumbing, notifications and merchant/network lookup are
  synthetic; calculators/business services/verifier/financial writes are native.
  This is not live-provider or browser checkout certification.
- Laravel restart is clean; native public currencies and Customer homepage
  return **200**. Registered API health screenshot shows `ok` only.
- Existing broad hardening baseline remains **682 tests, 14 errors, 9 failures**;
  unrelated failures were not expanded or disguised as a full passing suite.

### Protected state

Post-2B baseline file SHA-256:
`fbcd30955d11033874c0b87201fa1e0684236f592fa58acf41d4f95eac1b3c0f`.
Same codec; **55/55 protected table counts and full-row fingerprints identical**,
including after public runtime checks. **408/408 schema entries unchanged**.
No additional migration, financial/default exemption or projected comparison
was required. Development allocations **0**, contributions **0**, foreign-key
errors **0**. All **12** legacy Orders retain fulfillment `unverified`.
**Existing development financial values changed: none.**

Local receipts: `.local/payment-phase2c-before.json`,
`.local/payment-phase2c-after.json`, before/after schema JSON,
`.local/payment-phase2c-protected-comparison.json` and focused/selected JUnit XML.
Exact native/report file inventory is in the companion report section 14.5.

### Decision

Supported bounded native integration is verified; unsupported contracts are
explicitly unavailable. No independent new immediately exploitable financial P0
was confirmed, no material identity/schema deviation was made and no real
financial operation was exercised. This is **not an unconditional Phase 3
go-ahead** or certification of all catalog, provider, refund, payout or production
combinations. Important unproven monetary/FX contracts and native merchant
orchestration limitations remain for separately approved work.

**STOP.** No Phase 3, production certification, provider activation/configuration,
own gateway, provider refunds, external payout rails, legacy classification/
deletion or publishing. Await explicit approval.