# AgendaAlly Payment Phase 3B — MTN Cameroon/XAF readiness

Date: 2026-10-03 (America/Chicago).

## Phase 3B-4 preparation record — inquiry prepared, NOT sent

2026-10-03. [MTN Cameroon onboarding inquiry package](mtn-cameroon-onboarding-inquiry.md)
prepared: concise initial message, numbered technical questionnaire, internal
technical summary, response-mapping table and unpopulated answer template.
Only its initial message and questionnaire are intended for external sharing.
**NOT SENT:** no MTN contact, submission, account, onboarding, credentials,
provider APIs, implementation or testing. Preparation does not change the
existing decisions: public EUR technical testing **CONDITIONAL GO but not
approved**, Cameroon/XAF controlled non-production **UNKNOWN**, production
**NO-GO**. MTN remains disabled. **STOP; await explicit approval.**

## Current result — Phase 3B-3 official contract and controlled-test readiness review

**THIS PHASE DOES NOT AUTHORIZE ACTIVATION OR A REAL PAYMENT.**

The non-activating, read-only review is complete. Phase 3B-2 remains accepted:
**CONTAINED / VERIFIED** within owned development SQLite/fake transport;
**PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED**. No containment was reopened.

New official evidence establishes **Cameroon Collections availability** and
published **Cameroon production routing/currency `mtncameroon` / XAF**. This
supersedes the earlier interrupted review's *not established* market finding,
not its no-activation boundary. It does not establish an approved AgendaAlly
merchant, a Cameroon/XAF UAT environment, precise XAF operation rules or a
complete current Collection operation specification.

| Decision | Result | Meaning and remaining conditions |
|---|---|---|
| Public EUR sandbox technical exercise | **CONDITIONAL GO** | A possible future, separately approved EUR-only technical exercise, not permission to run now. Obtain the complete Collection specification, resolve contradictory callback instructions, and approve isolated EUR-native fixtures, sandbox-only credentials, callback registration and bounded reconciliation. Never relabel an XAF quote as EUR or configure real Customer checkout. |
| Cameroon/XAF controlled non-production test | **UNKNOWN** | No exact Cameroon/XAF non-production endpoint, target, currency/amount rules, credential entitlement or test-payer contract established. Operationally do not proceed. Published production routing is not a UAT contract. |
| Cameroon/XAF production activation | **NO-GO** | Merchant/marketplace approval, operation/callback contract, precision/MSISDN rules, settlement evidence, refund and Vendor-payout arrangements, operational controls and intended-engine certification remain incomplete. |

**STOP.** The single safest next action is to obtain written MTN Cameroon
confirmation of the exact Collection contract and approved non-production
environment, using the provider's published Cameroon Collections contact.
Do not obtain keys, onboard/configure a merchant, initiate testing or activate
anything automatically.

### 3B-3.1 Evidence classification and official-source inventory

Labels below are **PROVEN FROM OFFICIAL MTN SOURCE**, **PROVEN FROM AGENDAALLY
SOURCE**, **PRIOR ACCEPTED SYNTHETIC EVIDENCE**, **INFERRED — NOT CERTIFIED**,
**UNKNOWN — REQUIRES MTN CONFIRMATION**, **BLOCKED BY PROVIDER CONTRACT** and
**BLOCKED BY AGENDAALLY IMPLEMENTATION**. Absence of fetched evidence is not
evidence of unsupported functionality.

All sources below were freshly accessed **2026-10-03**. Publication/revision
dates were not supplied in the retrieved document bodies. Dates of community
replies are not the revision date of the provider's guidance.

| ID | Exact URL and retrieved title | Market/environment and provenance | Evidence/limit |
|---|---|---|---|
| O1 | https://momodeveloper.mtn.com/best-practices — Best Practices | First-party generic MoMo integration; Sandbox, Staging, Production mentioned | Server UUID, persist before submission, idempotency, 202 not final, authenticated status checks, polling/monitoring/reconciliation guidance. Merely mentioning Staging does not establish Cameroon UAT. |
| O2 | https://momodeveloper.mtn.com/api-documentation/api-description — api-description | First-party generic authentication and HTTP methods | Country-wallet access via subscription key/API user/key; Basic token acquisition, Bearer usage; asynchronous POST; original-reference GET; duplicate-reference error; PENDING/SUCCESSFUL/FAILED; PUT callback overview. |
| O3 | https://momodeveloper.mtn.com/api-documentation/callback — callback | First-party generic RequestToPay/Transfer overview; separate Sandbox/Production instructions | HTTPS, registered host, allow PUT/POST, callback once/no retry, GET fallback. Later `X-Callback-Url` and POST/status-shaped payload example is explicitly **Deposit-V1**, not a complete Collection specification. |
| O4 | https://momodeveloper.mtn.com/api-documentation/testing — testing | First-party generic public sandbox | Target `sandbox`, EUR, sandbox provisioning distinct from Partner GUI credentials; predefined RequestToPay/Refund/Deposit/Transfer outcomes. No Cameroon/XAF entitlement. |
| O5 | https://momodeveloper.mtn.com/api-documentation/getting-started — getting-started | First-party generic developer/product onboarding | Developer signup, product subscriptions and API-user/key steps. Not local merchant approval. |
| O6 | https://momoapi.mtn.com/Cameroon_Collection_productDetails — Cameroon_Collection_productDetails | **First-party Cameroon-specific Collections product** | Collection of goods/services, merchant-initiated debit subject to customer approval, partner Collections account, bank/GUI liquidation, fees and BEAC transaction limits. Establishes market product availability, not this app's entitlement. |
| O7 | https://momodevelopercommunity.mtn.com/how-to-59/momo-api-production-configuration-101 — MoMo API Production Configuration | MTN-hosted knowledge-base How-To; original author `allan.ddamulira` is visibly **Community Manager** | Provider-managed guidance, not an anonymous community answer or signed merchant contract. Table explicitly lists MTN Cameroon / `mtncameroon` / XAF; generic production host `proxy.momoapi.mtn.com`; sandbox host/target/EUR distinction; production credentials follow KYC/Partner Portal. Country manager must confirm current account-specific values. Other members' replies are not contract evidence. |
| O8 | https://momodeveloper.mtn.com/content/html_widgets/1sun5.html — Cameroon (body heading; no HTML title returned) | First-party Cameroon-labelled API terms | Registration, compliance, API limits, customer support, API-access resale/sublicensing restrictions and audit rights. Template contains mixed geography/entity references (including Accra and MoMo PSB); do not treat it as AgendaAlly's executed Cameroon merchant agreement. |
| O9 | https://momodeveloper.mtn.com/faqs — faqs | First-party generic FAQ including onboarding | Country-specific wallet credentials; UUIDv4; exact callback host (not a different subdomain); country/product selection, business-owner/business KYC, documents, signed contract, approval and Partner Portal. Sandbox section says HTTP, conflicting with O3's HTTPS. |
| O10 | https://momodeveloper.mtn.com/API-collections#api=collection&operation=requesttopay — APISandbox | First-party interactive operation portal | Fetched view says “No operations found”, “The specified API does not exist”, “No operation selected.” This is a retrieval limitation, **not** proof Collection no longer exists. Complete operation schema not available from this view. |
| O11 | https://momodeveloper.mtn.com/contact-support — contact-support | First-party generic support | Links FAQ, official community and provider-published Postman collection. No contact/account/credential action taken. |
| L1 | https://www.postman.com/momoapis/workspace/momo-open-apis/request/28452721-9b71cefd-ca06-4447-851f-3f96960ddf49 — MoMo Open APIs | External hosted-reference lead, located by search; provider support links its Postman workspace | Fetched content contained only navigation/sign-in. Search excerpt showing a RequestToPay callback header is **not used to certify that header/body contract**. |

Additional read-only retrieval inspected O10's public HTML/theme to locate
documentation metadata. Anonymous GET of
`https://momodeveloper.mtn.com/developer/apis?$top=50&$skip=0` returned HTTP 400,
not a usable operation definition. This was documentation retrieval, not an
MTN token, provisioning, payment, status, callback, refund or payout call.
Third-party payout documentation discovered by search was excluded.

**New versus historical evidence:** the inventory/source comparison and
protected-state/catalogue checks are new. Phase 3B-2's 51 focused tests / 528
assertions, 392 selected tests / 2,880 assertions and four SQLite contention
cases are accepted historical receipts, not new runtime/provider certification.
New application tests, provider tests and contention runs: **zero**.

### 3B-3.2 Market, currency and controlled-test environment

**PROVEN FROM OFFICIAL MTN SOURCE:** O6 establishes Cameroon Collections.
O7's provider-managed production configuration table establishes documented
Cameroon market currency XAF and target `mtncameroon`. Together they support a
documented Cameroon/XAF Collection path; neither certifies that a specific
AgendaAlly merchant/subscription accepts that operation today.

Public sandbox means MTN's generic developer simulation at
`https://sandbox.momodeveloper.mtn.com`, target `sandbox`, currency EUR
(O4/O7). It uses separately provisioned API users/keys and predefined
synthetic payer scenarios. O4 says other numbers produce success; that is a
simulator rule, not Cameroon subscriber/account validation.
**Public-sandbox XAF support: not established.** Its documented currency is
EUR; do not claim an observed XAF rejection because none was exercised.
Public EUR results cannot certify Cameroon precision, payer behavior, merchant
rights, real settlement or market operations.

**Cameroon non-production:** existence, URL, target, permitted currency,
provisioning/merchant identity, test MSISDNs/scenarios, callback contract,
transaction limits and transition to production are all **UNKNOWN — REQUIRES
MTN CONFIRMATION**. A portal title saying “Test environment,” O1's generic
Staging reference, and O7's production country row do not answer this question.
Do not use `mtncameroon` at the sandbox host on that inference.

### 3B-3.3 RequestToPay/source comparison and approval gate

Prefix `B` means `.migration-backup/backend/`. Relevant current source:
`B/app/Services/PaymentService/MtnService.php`, its `MtnAttempt` boundary,
`B/app/Http/Controllers/API/v1/Dashboard/Payment/MtnController.php`,
`B/app/Console/Commands/ReconcilePendingMtnPayments.php`, native payment
request validation, GatewayConfig models and `B/routes/api.php`.

| Contract element | Current AgendaAlly source | Official evidence and conclusion |
|---|---|---|
| Product/method/path | POST `{base}/collection/v1_0/requesttopay`; token POST `/collection/token/`; status GET `/collection/v1_0/requesttopay/{R}` | O2 establishes RequestToPay POST, token/Bearer model and reference-based GET. Exact versioned operation definition was not retrieved from O10. **Exact endpoint certification UNKNOWN**, not declared incorrect. |
| Authorization/subscription | Basic `API user:API key` for token; Bearer plus `Ocp-Apim-Subscription-Key` for requests | Matches O2/O9's generic contract; complete operation-specific required-header list **UNKNOWN**. |
| Reference | Server UUID, durable before dispatch; sent as `X-Reference-Id`; GET uses original UUID | Matches O1/O2/O9. UUIDv4 guidance confirmed. Existing-reference duplicate error is not a success/no-collection certificate. |
| Environment | `X-Target-Environment` from unchanged owner-scoped config | Header concept and sandbox/production values documented by O7; Cameroon UAT value unknown. No config rows exist. |
| Content type/body | JSON; amount string, currency, externalId, payer object, payerMessage, payeeNote and callbackUrl | Exact current Collection types, required/optional fields, lengths and extra-field acceptance **not certified** without operation schema. Do not borrow Deposit/Transfer body definitions. |
| Amount | Native integer amount converted with `ExactMoney::decimal(...,2)` | Source proven. O4 EUR simulation does not establish XAF precision, integer/decimal formatting, min/max, rounding or fee handling. **Do not modify monetary scale.** |
| Currency | Frozen actual quote, expected merchant currency and returned currency must match | Source/accepted containment proven; display currency supplies no authority; no EUR substitution/FX. Provider market evidence from O6/O7 is not complete operation validation. |
| externalId | Equal to retained server UUID; returned externalId must equal it | Self-consistent source correlation; full current official Collection externalId restrictions/echo/payload contract **UNKNOWN**. No evidence requires a different identity. |
| Payer | `partyIdType=MSISDN`, partyId from native `phone` | Source proven. Request validation is generic `phone => int`, not Cameroon format/network/account eligibility verification. Exact Cameroon rules unknown. |
| Receiving merchant | Owner-scoped credentials/config fingerprint, no independently checked returned legal payee | O2/O9 establish credentials grant a country-wallet context; O6 establishes partner Collection account. Actual legal account/settlement mapping must come from onboarding/provider evidence. |
| Callback URL transmission | JSON `callbackUrl` generated from request scheme/host; no `X-Callback-Url` in requestHeaders | **Unresolved material compatibility question**, not a proven Collection mismatch from O3's Deposit example or L1 search snippet. Obtain operation-specific contract before approving a correction. |
| Response | 202 gives ACCEPTED_PENDING; ambiguous failures retain UNKNOWN | Compatible with O1/O2; initiation acknowledgement never paid. No live response exercised. |

**No newly proven material contradiction requires redesigning the accepted
Phase 3B-2 identity schema.** This is not certification of all surrounding
RequestToPay fields. If authoritative Collection evidence confirms header-only
callback delivery, a different identity/body contract, or materially different
currency/merchant verification, the required result is
**MTN CONTRACT DEVIATION REQUIRES APPROVAL** and STOP before any code change.
The callback question remains approval-gated and **UNKNOWN**, not silently
fixed and not labelled a confirmed defect from non-authoritative evidence.

### 3B-3.4 Callback, statuses, idempotency and recovery

**PROVEN FROM OFFICIAL MTN SOURCE:** RequestToPay callbacks are supported
(O3/O9); host registration and per-request URL are required; listener should
allow PUT and POST; callbacks are sent once without retry; missing notifications
require GET fallback. O2 describes PUT generally. O3's concrete POST/payload
example is Deposit-V1 and cannot establish RequestToPay's exact method/body.

O3 says HTTPS in both environments; O9's sandbox section says HTTP instead.
**Provider documentation conflict:** retain secure HTTPS as the candidate,
but require MTN to confirm the actual sandbox/Collection contract before use.
Do not relax HTTPS or conclude it was runtime-verified. O9 requires callback
URL host to match registered providerCallbackHost exactly; a different subdomain
will not work. TLS trust-chain acceptance and the approved callback domain
remain externally unverified. Current URL comes from request host; actual
registered domain/proxy deployment suitability cannot be proven without setup.

`routes/api.php` uses `Route::any('mtn/payment', ...)`, so source permits both
PUT and POST. Controller takes externalId as a lookup hint and invokes the
expected unchanged configuration's authenticated reconciliation. It does not
grant paid authority from callback status alone. Exact RequestToPay notification
fields/authentication/signature requirements are **UNKNOWN**. The controller's
comment that callbacks are unsigned is a **source assertion**, not independently
certified by the retrieved operation contract. O1 explicitly says never blindly
trust callback payloads and recommends source-IP validation, immediate
acknowledgement/queued processing, monitoring and transaction-specific URL hashes;
current inline GET processing is not a certified high-scale callback service.
No speculative callback redesign was undertaken.

| Provider outcome | AgendaAlly meaning | Contract/financial authority |
|---|---|---|
| 202 | ACCEPTED_PENDING | Queued, not paid |
| PENDING | Nonfinal provider pending | Original attempt retained |
| SUCCESSFUL | Native paid through exact authenticated intent/canonical writer | Amount, currency, externalId and original config must match; no callback-only authority |
| FAILED | Native canceled/verified failure | Provider terminal failure; not refund/cancellation API entitlement |
| Missing/unknown/unreachable/new enum | Unresolved; no paid authority/new request | No invented terminal mapping or release of UNKNOWN |

**Phase 3B-2 compatibility:** O1 requires persist-before-submit; O2 identifies
GET by original POST reference and rejects duplicated references; O3 requires
polling when a callback is missed. These support committed identity, conservative
UNKNOWN and same-reference reconciliation. The provider does not prescribe
AgendaAlly's state names or database design. No-resubmission after ambiguity is
the accepted conservative policy, supported by that behavior, not an invented
provider guarantee of exactly-once delivery. A definite new eligible attempt
after verified failure is distinct from retransmitting an ambiguous one.

Source worker considers unresolved typed processes, waits at least two minutes
and is declared every five minutes. Typed reconciliation shares the original
reference/configuration and canonical writer with Customer status and webhook
hints. Scheduler execution, production rate limits, backoff, max polling
duration, alerts and outage/manual-review operations are **not certified**.
Do not blindly time out an unresolved financial attempt into failure.

### 3B-3.5 Merchant models, settlement, refund, payout and reporting

**Merchant onboarding:** O9 documents country and product/package choice,
business-owner/business KYC, uploaded documents, signed contract, vetting,
approval and Partner Portal access. O7 distinguishes production subscription
keys on the developer portal from country Partner Portal API user/key management.
O6 says bank details are captured if bank liquidation is chosen. Exact Cameroon
document checklist, contractual entity and AgendaAlly entitlements need MTN.
O8's template inconsistencies require an approved current local agreement,
not assumptions from a public widget.

**Platform-managed collection:** O6 supports a partner receiving customers'
goods/services payments into its own Collections account. Applicability to
AgendaAlly collecting for multiple independent Vendors, beneficial ownership,
custody, regulated activities and any aggregator/marketplace approval is
**UNKNOWN — REQUIRES MTN CONFIRMATION**. Generic business collection is not
marketplace certification.

**Vendor-direct:** separate native Shop credentials/currency and internal
accounting are accepted synthetic architecture, not MTN authorization for
delegated orchestration. Each Vendor's merchant onboarding, API access,
credential delegation, data/customer consent and settlement rights must be
confirmed. O8's API resale/sublicensing restrictions are not automatically a
blanket ban on this business model, nor permission for it. No Vendor-direct
activation or schema redesign.

**Settlement:** O6 says successfully collected money enters the partner's
Collections account. Access is described as available after execution;
liquidation may be to the partner's onboarded bank account or through Partner
GUI/other MoMo channels. This is product behavior, not evidence of AgendaAlly's
specific account, final bank settlement, timing/SLA or legal control. Source
`merchant_verified=true` means unchanged authenticated credential context,
not independent verification of a returned legal merchant/bank identifier.
Internal Vendor payable/commission records do not externally split funds.
No relevant provider split/commission API was established.

O6 describes per-transaction fees charged to the client's Collections account,
deducted on liquidation, a published 1% reference with business/value-dependent
negotiation and possible changes, and BEAC limits without numeric limits.
Do not hardcode fees, treat this as AgendaAlly's tariff or assume gross payer
amount equals net settled balance.

**Refund:** O4 proves generic sandbox refund scenarios exist; not full/partial
Cameroon Collection rights, original-payment identity field, asynchronous
contract, statuses, polling/callback, limits/window or negotiated fees. Those
are **UNKNOWN**. Native MTN refund dispatch/verification remains absent in the
accepted baseline: **BLOCKED BY AGENDAALLY IMPLEMENTATION**; **refund readiness
UNKNOWN / NOT CERTIFIED** externally. A failed RequestToPay is not a refund.
No Cameroon reversal/correction/cancellation operation was established.
Production needs an approved refund/exception policy and verified provider
process; no refund/manual financial workaround was added.

**Disbursement/Vendor payout:** O4/O5 establish generic Disbursement testing/
product context; O6 mentions onward partner/supplier/employee payments. It
could be a future payout rail (**INFERRED — NOT CERTIFIED**), but these do not
establish a Cameroon/XAF Disbursement API or that AgendaAlly may use it.
Separate product approval/subscription, funded sender account, matching API
credentials, recipient identity, limits/fees, idempotency, verification and
reconciliation must be confirmed. Do not reuse Collection entitlement by
assumption. Platform-collected Vendor liability needs a certified external
payout/settlement mechanism; Vendor-direct principal is not platform-held.

**Reconciliation/reporting:** O2/O3 support original-reference transaction GET;
O4 includes generic balance simulations; O6/O7 establish a partner account/
GUI context. O1 recommends financial reconciliation/reporting, logging and
monitoring. Exact Cameroon statement/export APIs, pagination, transaction/
fee/settlement identifiers, retained reports, delivery schedules and settlement
matching are **UNKNOWN**. Status success is not bank-settlement proof.

### 3B-3.6 Exact known credential/environment matrix

No credential values were requested, read, generated or displayed.

| Element | Public technical sandbox | Cameroon non-production | Published production guidance / Cameroon |
|---|---|---|---|
| Base URL | `https://sandbox.momodeveloper.mtn.com` (O7) | UNKNOWN | `https://proxy.momoapi.mtn.com` (O7); confirm account-specific endpoint |
| X-Target-Environment | `sandbox` (O4/O7) | UNKNOWN | `mtncameroon` (O7) |
| Actual currency | EUR (O4/O7) | UNKNOWN; XAF cannot be presumed | XAF in country table (O7); exact Collection operation rules unconfirmed |
| Product subscription key | Sandbox Collection subscription; secret | UNKNOWN provisioning/entitlement | Production Collection-approved subscription key; secret, environment/product scoped |
| API user / API key | Separate sandbox provisioning; encrypted sensitive identity / secret | UNKNOWN; provider-confirmed isolated credentials required | Merchant-managed country Partner Portal after approval (O2/O7/O9); encrypted sensitive identity / secret |
| Bearer token | Derived from matching API user/key, expiry applies | UNKNOWN exact token/endpoint contract | Matching approved account credentials; runtime secret, never payment evidence |
| Merchant/wallet identity | Simulated account, not real settlement | UNKNOWN test account and ownership | Approved partner Collections account; legal owner/account mapping must be supplied by MTN |
| Callback domain/URL | Register exact host; full URL per request; HTTPS candidate, conflict unresolved | UNKNOWN | Registered HTTPS host per portal; approved TLS chain/operation-specific payload/auth still required |
| Payer/test identity | O4 predefined simulator numbers/outcomes | UNKNOWN | Actual eligible Cameroon subscriber; exact format/network/account rules unconfirmed |
| Owner scope | Separate approved disposable fixtures only | Approved isolated platform or Vendor identity, not guessed | Native country platform profile versus Shop Vendor profile must match external legal owner |
| Refund/Disbursement access | Generic simulations do not confer live rights | UNKNOWN separate market/product approval | UNKNOWN Cameroon product rights, funding/credentials and operations |

**Existing schema:** GatewayConfig models carry encrypted subscription key/API
user/API key and target/currency/base URL; native owner-scoped rows/revisions
and callback construction cover known generic Collection connection fields.
Secret redaction/encryption is **accepted baseline**, not new UI/log testing.
There are zero actual platform/Shop/payload configuration rows.
This is **PARTIAL**, not proof the full Cameroon agreement/UAT/settlement or
simultaneous environment profiles fit. No definitive new schema requirement
was established. If provider evidence introduces one, obtain separate approval;
do not create a migration or hide new immutable financial identity in JSON.

### 3B-3.7 Readiness matrix and exact blockers

| Area | Status | Evidence / blocker |
|---|---|---|
| Cameroon Collections market | PROVEN FROM OFFICIAL MTN SOURCE | O6; actual merchant entitlement not established |
| Published Cameroon XAF/production routing | PROVEN FROM OFFICIAL MTN SOURCE | O7 provider-managed country table; no runtime/merchant certification |
| Public sandbox | PARTIAL / CONDITIONAL GO | EUR/target/host known; exact operation/callback contract and separate scope still required |
| Cameroon/XAF UAT | UNKNOWN / BLOCKED BY PROVIDER CONTRACT | Entire environment/test-account/operation contract missing |
| RequestToPay initiation/body | PARTIAL | Generic async/identity aligns; full operation fields/header/body specification unavailable |
| Callback | UNKNOWN exact compatibility | Host/method overview known; Collection header/body/auth unresolved; HTTP/HTTPS documentation conflict |
| Durable identity/UNKNOWN/replay/canonical finality | CONTAINED / VERIFIED in accepted scope | Phase 3B-2 historical fake/SQLite evidence; official guidance compatible |
| Authenticated amount/currency/config verification | PARTIAL | Current source plus prior synthetic tests; external precise contract/merchant ownership unconfirmed |
| XAF precision/limits/fees | UNKNOWN | No operation-level numeric limits/rounding; product fee reference not negotiated tariff |
| Cameroon payer validation | BLOCKED BY PROVIDER CONTRACT / PARTIAL SOURCE | `phone=int` is not the exact Cameroon contract |
| Merchant onboarding/secret foundation | PARTIAL | Generic KYC/contract steps and accepted encrypted fields; no profiles/approvals |
| Platform marketplace collection | UNKNOWN | Ordinary partner collection documented; aggregator/custody rights need approval |
| Vendor-direct orchestration | UNKNOWN externally | Native synthetic architecture is not provider permission |
| External collection/bank settlement proof | UNKNOWN | Partner account mechanism known; app-specific beneficiary, timing, tariff and statements absent |
| Split/commission API | UNKNOWN | No relevant authoritative operation established |
| Refund/reversal | BLOCKED implementation / UNKNOWN provider contract | Native refund missing; Cameroon operations/rights unconfirmed |
| Disbursement/Vendor payouts | UNKNOWN / NOT CERTIFIED | No Cameroon/XAF API/product entitlement or implemented certified rail |
| Product/Booking/mixed Wallet | PRIOR ACCEPTED SYNTHETIC EVIDENCE | No new runtime/provider flow certification |
| Multi-Shop | BLOCKED | No approved split-merchant contract; existing fail-closed policy retained |
| Reconciliation/operations | PARTIAL | Same-reference worker/status source; scheduling, backoff, monitoring, statements, outage/rotation plan unverified |
| Intended production engine | UNKNOWN certification | Repository default `mysql` is intent evidence, not deployed-engine proof; no production access |

**First controlled Cameroon/XAF test blockers:** MTN-written non-production
designation (host/target/XAF/test account), complete Collection
RequestToPay/status/callback definitions, payer/precision/limits/fees,
merchant ownership/marketplace rights and credential isolation, approved
callback domain/TLS and polling parameters, test scenarios/settlement meaning,
and a separately approved bounded test plan/configuration. Any confirmed
material source mismatch requires its own correction approval first.

**Production blockers:** all applicable contract gaps above, approved legal
merchant/KYC/marketplace agreement and account, external settlement/fees/reporting
evidence, refund/exception and Vendor payout arrangements, secure deployment/
callback/operational monitoring, configuration-rotation recovery procedure,
production-like controlled verification and intended-engine integrity/
concurrency certification. Production permission is not implied by a public
market table or a successful sandbox result.

Repository `config/database.php` defaults `DB_CONNECTION` to `mysql`; no
environment/secret values or production database were inspected. Before an
engine claim, a separately approved disposable matching-engine run must verify
nullable uniqueness and immutable-binding enforcement equivalent to the SQLite
triggers, CAS/claim row-count semantics, row locks/isolation, commit-before-HTTP,
atomic financial completion, rollback and concurrent initiation/reconciliation.
SQLite write-reservation behavior and SQLite-only triggers cannot certify that
other engine. **SQLITE CONCURRENCY VERIFIED** remains historical bounded
evidence; **PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED** remains unchanged.

### 3B-3.8 Protected-state and verification receipts

New read-only development checks:

- Before snapshot exactly matches accepted Phase 3B-2 after snapshot.
- **55/55 protected table counts and SHA-256 fingerprints unchanged**, using the
  same recorded row-serialization/order codec; no counts-only immutability claim.
- **416/416 schema entries unchanged** against the accepted post-migration
  schema; comparison canonicalizes schema-row order only.
- Platform config / Shop config / payment payload rows **0 / 0 / 0**.
- Typed development MTN processes **0**; foreign-key errors **0**.
- All **12 legacy Product Orders remain `unverified`**; no classification/deletion.
- Fresh development preview GET `/api/v1/rest/payments`: **wallet,cash only**.
  MTN remains disabled and absent from real Customer checkout.

Receipts: `.local/payment-phase3b3-before.json`,
`.local/payment-phase3b3-after.json`, `.local/payment-phase3b3-state.json`,
`.local/payment-phase3b3-protected-comparison.json` and
`.local/payment-phase3b3-catalog.json`. The small PDO inspector is query-only
against the existing owned development SQLite file and does not bootstrap
Laravel or connect to a provider.

Application code, schema, provider/configuration/financial rows, dependencies,
workflows, real credentials and activation policy were unchanged. Documentation,
query-only review helper and generated receipts are the only review outputs.
No MTN provisioning/token/transaction/status/callback/refund/disbursement calls,
production access, FX, refunds, payouts, Vendor-direct enablement, other-provider
work or publishing. No new independent source-confirmed immediately exploitable
financial P0 was established within this bounded contract trace.

### 3B-3.9 All 64 required final answers

1. **Does official evidence establish Collection availability in Cameroon?**
   Yes, documented product availability: O6's Cameroon Collections page.
2. **Does official evidence establish XAF for Cameroon Collection?**
   Yes as documented market configuration: O6's Collection availability paired
   with O7's Cameroon/XAF production table. Exact operation/account acceptance
   and controlled-test currency remain unverified.
3. **What exact evidence supports that answer?**
   O6 `https://momoapi.mtn.com/Cameroon_Collection_productDetails` and O7
   `https://momodevelopercommunity.mtn.com/how-to-59/momo-api-production-configuration-101`,
   fetched 2026-10-03; O7 original author visibly Community Manager. Not snippets.
4. **What exactly is the public EUR sandbox?**
   Generic simulated developer environment, separate sandbox provisioning,
   `https://sandbox.momodeveloper.mtn.com`, target `sandbox`, EUR (O4/O7).
5. **Does the public sandbox support XAF?**
   Not established. EUR is documented; no XAF request/rejection was exercised.
6. **Can the public EUR sandbox certify Cameroon/XAF?**
   No. Technical simulation cannot establish the different market/currency contract.
7. **Is there a documented Cameroon-specific test/staging/UAT environment?**
   Not established in retrieved evidence; UNKNOWN, not disproven.
8. **If yes, what is its currency?**
   UNKNOWN; do not assign either EUR or XAF without the test contract.
9. **What target environment value is required?**
   Public sandbox `sandbox`; published Cameroon production `mtncameroon`;
   Cameroon non-production UNKNOWN.
10. **What base URL is required?**
    Public sandbox `https://sandbox.momodeveloper.mtn.com`; O7 production guidance
    `https://proxy.momoapi.mtn.com`; account-specific confirmation/UAT URL missing.
11. **What credentials are required?**
    Matching Collection subscription key, API user, API key and derived Bearer
    token; sandbox and country production identities/provisioning are separate.
    Cameroon UAT entitlement/provisioning UNKNOWN.
12. **How is the receiving merchant identified?**
    Country-wallet credential context generically (O2/O9); O6's onboarded partner
    Collection account. Exact merchant identifier/operation response UNKNOWN.
13. **What is the API-user/wallet relationship?**
    API user/key plus subscription grant country-wallet access; the actual
    AgendaAlly legal owner/account binding must be confirmed by MTN onboarding.
14. **What onboarding is required?**
    Country/product selection, business/owner KYC, documents and signed contract,
    vetting/approval, Partner Portal; bank details if bank liquidation (O6/O9).
    Exact Cameroon checklist/marketplace approval UNKNOWN.
15. **Is the current RequestToPay endpoint correct?**
    Generic product/POST intent aligns; full exact versioned endpoint certification
    UNKNOWN because O10 did not expose the operation definition. No wrong-path
    finding is asserted.
16. **Are required headers correct?**
    Generic authentication/subscription/reference/environment align; complete
    Collection header list and callback transmission not certified.
17. **Is the RequestToPay body correct?**
    NOT CERTIFIED. Current fields documented in source table; exact Collection
    types/requirements, callbackUrl acceptance and XAF formatting UNKNOWN.
18. **Is X-Reference-Id usage correct?**
    Yes within accepted identity scope: server UUID durably committed before
    dispatch and reused for original-reference lookup (O1/O2/O9).
19. **Is externalId usage correct?**
    Internally consistent immutable correlation; official Collection restrictions/
    echo contract not retrieved. UNKNOWN full provider compatibility.
20. **Is callback handling contract-compatible?**
    PARTIAL: PUT/POST source route and hint-only authenticated recovery align
    generically. Exact Collection URL field/header, payload/auth and deployment
    contract remain UNKNOWN; official sandbox transport instructions conflict.
21. **Is callback authoritative payment evidence?**
    No under accepted policy; O1 warns against blindly trusting it. Authenticated
    matching status/canonical completion supplies paid authority.
22. **Is polling required/recommended after callback/network failure?**
    Yes (O1/O2/O3). Original-reference status lookup, no new ambiguous charge.
23. **Are PENDING/SUCCESSFUL/FAILED mappings correct?**
    Yes generically: nonfinal / verified paid / verified failure. Native canceled
    for FAILED does not establish a provider cancellation/refund operation.
24. **How should an unknown provider state be treated?**
    Retain unresolved original attempt; no paid authority, automatic final
    failure, release of ambiguity or new reference.
25. **Does official behavior support UNKNOWN recovery?**
    Yes: persisted reference, asynchronous execution and GET/poll fallback.
    UNKNOWN is AgendaAlly's conservative state, not an MTN enum.
26. **Does official behavior support no resubmission of an ambiguous attempt?**
    Yes as accepted conservative policy; original-reference GET and duplicate
    reference behavior support reconciliation, not blind duplicate dispatch.
27. **Does Phase 3B-2 remain contract-compatible?**
    Yes for its accepted durable identity/recovery/canonical scope. It does not
    certify the surrounding full operation or Cameroon environment contract.
28. **Does any Phase 3B-2 code/schema need modification?**
    No new requirement established. Do not reopen it. Any subsequently confirmed
    material contradiction requires separate approval before changes.
29. **What are authoritative XAF amount/precision rules?**
    UNKNOWN allowed scale/representation/min/max/rounding/fee amount treatment.
    O6 mentions BEAC limits/negotiable fees without exact operation rules.
30. **What are Cameroon MSISDN requirements?**
    Exact format, plus/national/international handling, prefixes, active-account
    eligibility and UAT numbers UNKNOWN. Generic MSISDN intent is source-proven.
31. **Can AgendaAlly reliably validate them today?**
    Not certified; generic `phone=int` does not establish those Cameroon rules.
32. **Does MTN support intended platform-managed collection?**
    Ordinary partner collection into its account documented; AgendaAlly's
    multi-Vendor/platform/custody rights UNKNOWN.
33. **Is special marketplace/aggregator approval required?**
    UNKNOWN — direct MTN Cameroon confirmation; do not assert yes or no.
34. **Does MTN support intended Vendor-direct orchestration?**
    UNKNOWN externally; separate native merchant configuration/accounting is
    accepted synthetic evidence, not provider delegation permission.
35. **Where does collected platform money externally settle?**
    O6: partner Collections account, then chosen onboarded bank/GUI liquidation.
    Actual AgendaAlly account/beneficiary/timing/fees UNKNOWN.
36. **Can AgendaAlly prove the destination from provider evidence?**
    No app-specific provider/account/settlement evidence; credential-context
    `merchant_verified` is not independent legal payee/bank verification.
37. **Does MTN provide relevant split-payment/commission functionality?**
    UNKNOWN; none established. Native allocation does not cause external split.
38. **Does Collection support refunds for Cameroon?**
    UNKNOWN. O4 generic sandbox refund scenarios are not local eligibility.
39. **Full refunds?**
    Cameroon full-refund rights/contract UNKNOWN.
40. **Partial refunds?**
    Cameroon partial-refund rights/contract UNKNOWN.
41. **What provider identity is required for a refund?**
    Exact original transaction/reference fields and merchant entitlement UNKNOWN.
    Do not infer from RequestToPay or Deposit.
42. **Are refunds asynchronous?**
    Generic O4 pending/delayed refund scenarios suggest asynchronous behavior
    (INFERRED — NOT CERTIFIED); exact Cameroon refund operation UNKNOWN.
43. **How are refund outcomes reconciled?**
    Exact refund GET/callback/status/identity contract UNKNOWN; require it before
    implementation, not a guessed reuse of Collection status.
44. **Is AgendaAlly MTN-refund ready?**
    No. Native MTN refund dispatch/verification absent; market contract uncertified.
45. **What reversal/correction operations exist?**
    Exact Cameroon rights/API/back-office process UNKNOWN; FAILED is not reversal.
46. **Could Disbursement potentially support Vendor payouts?**
    Potentially (INFERRED — NOT CERTIFIED); generic product/simulation and O6
    onward-payment description do not authorize this rail.
47. **Is Cameroon/XAF Disbursement proven?**
    No authoritative API/product/currency entitlement established in this review;
    UNKNOWN, not unsupported.
48. **What separate onboarding/credentials would it require?**
    Confirm Disbursement product approval/subscription, sender funding/account,
    environment API user/key, recipient/limit/fee rules and reconciliation.
    Exact Cameroon requirements UNKNOWN; Collection credentials are not assumed.
49. **What reconciliation/reporting facilities exist?**
    Original-reference GET, generic balance simulations, partner GUI/account
    context and O1 reconciliation guidance. Exact Cameroon statements/exports/
    settlement/fee identifiers and reporting APIs UNKNOWN.
50. **What exact credential/environment matrix applies?**
    Section 3B-3.6 gives known public sandbox and published production values;
    Cameroon non-production cells remain explicitly UNKNOWN.
51. **Is existing secret/configuration schema sufficient?**
    PARTIAL: known generic encrypted connection/owner/routing fields exist;
    full UAT/merchant/settlement/environment and revision-history needs uncertified.
52. **Is any new schema required?**
    None proven in this phase. No migration proposed/applied; confirmed future
    requirements require a separate schema decision/approval.
53. **Public EUR sandbox technical testing decision?**
    CONDITIONAL GO for a future separately approved isolated EUR-only exercise,
    after exact operation/callback evidence and bounded test plan. Not authorized now.
54. **Cameroon/XAF controlled non-production test decision?**
    UNKNOWN — exact environment/entitlement/test rules absent; do not proceed.
55. **Cameroon/XAF production activation decision?**
    NO-GO — contractual, implementation and operational certification gaps remain.
56. **Exact first controlled Cameroon/XAF test blockers?**
    Written environment/host/target/XAF/credential/test-payer contract; operation/
    callback/precision/limit rules; approved merchant/marketplace model; callback/
    TLS/polling setup and bounded test plan. Confirmed mismatches need separate approval.
57. **Exact production blockers?**
    Relevant test/contract gaps plus approved KYC/legal account, settlement/
    reporting/tariff evidence, refund/exception and Vendor payout arrangements,
    operations/rotation/deployment controls, controlled verification and matching
    production-engine integrity/concurrency certification (section 3B-3.7).
58. **Does MTN remain disabled?**
    Yes; accepted real-use gate unchanged, no merchant configuration or activation.
59. **Still absent from real Customer checkout?**
    Yes; fresh development public catalogue contains only wallet,cash.
60. **Did financial/configuration data change?**
    No: 55/55 protected fingerprints and 416 schema entries unchanged; no profile
    rows created. Docs/query-only helper/receipts only.
61. **All 12 legacy Product Orders still unverified?**
    Yes: read-only grouped query returns unverified, count 12.
62. **Production-engine concurrency still uncertified?**
    Yes. Repository mysql default is not a deployed-engine or concurrency claim;
    no production access/testing.
63. **What needs direct MTN confirmation rather than more code?**
    Current complete Collection/callback contract and transport conflict; Cameroon
    UAT host/target/currency/provisioning/test scenarios; XAF amount/MSISDN/limits/
    fees; actual merchant ownership, marketplace and Vendor delegation rights;
    beneficiary/liquidation/statements; split/refund/reversal/Disbursement rights;
    account-specific production routing, rate/polling/IP/TLS and operational terms.
64. **Single safest next action?**
    Obtain written MTN Cameroon Collection/non-production contract clarification
    via the O6 published Collections contact `MoMoCorporate.CM@mtn.com`.
    Do not contact/onboard, request keys, configure, test or activate automatically.

**FINAL STOP — await explicit approval.** This is an evidence/readiness review,
not provider runtime certification or authorization for any payment.

## Historical follow-on — Phase 3B-2 durable identity contained, activation still blocked

The explicitly approved minimum existing-table MTN identity migration is applied
to owned development SQLite. Its protocol identity commits before RequestToPay,
unknown outcomes retain one reference, and authoritative completion uses the
unchanged Phase 2 native canonical writer. Product/Booking, interruption,
replay, mixed Wallet and four two-connection contention fixtures pass with fake
HTTP only. See [the complete Phase 3B-2 report and 44 answers](payment-phase3b-mtn-identity-containment.md).

**CONTAINED / VERIFIED** applies only to the durable-identity blocker in that
bounded scope. **SQLITE CONCURRENCY VERIFIED; PRODUCTION-ENGINE CONCURRENCY NOT
CERTIFIED.** MTN remains disabled and absent from the real Wallet/Cash-only
Customer catalogue. All protected financial values and 12 unverified legacy
Orders are unchanged. No real merchant credentials or MTN calls were used.

**Cameroon/XAF remains uncertified.** The prior public sandbox EUR finding
remains unresolved; XAF was not changed to EUR and no FX was introduced.
Controlled activation still requires an exact provider-supported Cameroon/XAF
test contract/environment, authorized matching merchant/currency/configuration,
intended-engine verification and separately approved callback/polling/external
settlement evidence. No refund or external payout certification is implied.
The safest next action is review of this evidence and, only under separate
approval, non-activating contract confirmation. **STOP; do not resume general
Phase 3B or activate MTN automatically.**

## Historical follow-on status — Phase 3B-1 schema gate

The accepted identity finding was separately authorized for minimum
non-activating correction. Its before-edit trace/schema assessment stopped:
**MTN PAYMENT IDENTITY SCHEMA CHANGE REQUIRED.** No correction or migration
was applied; the original request-identity/unknown-outcome blocker remains open.
The physical process ID is not unique, and a typed unique immutable canonical
MTN attempt/dispatch binding requires separately approved schema work.

See [Phase 3B-1 identity containment stop/proposal](payment-phase3b-mtn-identity-containment.md)
for exact missing invariants, guarded existing context behavior, disposable
schema evidence, proposed columns/constraints/lifecycle, legacy/rollback
treatment and all 35 decisions. New application tests/contention deferred;
3 isolated schema checks only. All 55 protected table hashes and 408 schema
entries unchanged; legacy Orders still unverified; catalog still Wallet/Cash.
MTN is not activated. EUR sandbox and all external Cameroon/XAF certification
blockers remain unchanged. No automatic certification resumption is authorized.

## Original Phase 3B stop result

**STOP — MTN CONTRACT DEVIATION REQUIRES APPROVAL.**

**Controlled Cameroon/XAF sandbox/test activation: NOT READY. Live production
activation: NOT READY. Certification interrupted by the brief's section 51
stop rule; this is a bounded stop report, not completed Phase 3B certification.**

No application code, activation policy, credentials, configuration records,
dependencies, financial records or schema were changed. No transactional MTN
API was called. No synthetic payment was initiated in this review. The
post-Phase-3A protected baseline was captured before report edits.

## 1. Accepted baseline and evidence labels

The accepted baseline remains:

- [Full payment audit](payment-system-full-audit.md).
- [Phase 2 accounting/native integration](payment-phase2-collection-allocation.md).
- [Phase 3A provider architecture](payment-phase3-provider-readiness.md).
- [Phase 1 financial correctness](payment-phase1-financial-correctness.md).

No general payment re-audit or unrelated-provider implementation was undertaken.

Evidence in this report is distinguished as:

- **PROVEN FROM AGENDAALLY SOURCE:** inspected native implementation or current
  development read-only checks.
- **PROVEN FROM OFFICIAL MTN SOURCE:** facts on the public first-party pages
  listed below, within their stated scope.
- **SYNTHETICALLY VERIFIED — ACCEPTED BASELINE ONLY:** prior accepted Phase 2/3A
  tests, not rerun or expanded here.
- **UNKNOWN / REQUIRES EXTERNAL CONFIRMATION:** missing market, merchant,
  contractual or operational evidence, or work deferred at the mandatory stop.

The prior structural `READY` state does not certify operational readiness.

## 2. Official documentation inventory

All pages were accessed on **2026-10-03**. Only first-party MTN pages were used
for contract conclusions. No login, account creation, provisioning or credential
access was attempted. Search snippets were not treated as verified contracts.

| ID | Exact source | Product/environment scope | Cameroon-specific? | Evidence obtained |
|---|---|---|---|---|
| D1 | https://momodeveloper.mtn.com/best-practices | Generic MoMo Open API integration; mentions Sandbox, Staging and Production credentials | No | Server-generated UUID; persist transaction state before MoMo requests; idempotent retries; status checks after network failure; never treat 202 as final success |
| D2 | https://momodeveloper.mtn.com/api-documentation/api-description | Generic API authentication/methods; explicitly distinguishes sandbox provisioning from production Partner Portal | No | Subscription key, API user/API key, Basic token acquisition and Bearer authentication; asynchronous POST; reference-based GET; PENDING/SUCCESSFUL/FAILED; duplicate reference error |
| D3 | https://momodeveloper.mtn.com/api-documentation/callback | RequestToPay/Transfer notification overview; explicit sandbox and production sections; later header example is Deposit-V1/Disbursement | No | 202 queues processing; callback only once; no delivery retry; polling fallback; registered callback host; HTTPS; listener should allow PUT and POST |
| D4 | https://momodeveloper.mtn.com/api-documentation/testing | Sandbox only; generic predefined test scenarios including RequestToPay and Refund | No | Target environment `sandbox`; sandbox currency **EUR**; sandbox credentials distinct from existing Partner GUI accounts; predefined pending/delayed/error scenarios |
| D5 | https://momodeveloper.mtn.com/api-documentation/getting-started | Developer sandbox onboarding; Collection, Widget, Remittances and Disbursements subscriptions | No | Developer account, product subscription and API user/key steps; not a Cameroon live-merchant approval contract |
| D6 | https://momodeveloper.mtn.com/products | Product list portal page | Not established | Retrieved page rendered headings/footer without substantive product list; indexed Collection description was not promoted to verified evidence |
| D7 | https://momodeveloper.mtn.com/Product-descriptions | Generic product landing page | Not established | Retrieved page lacked substantive detailed contract content |
| D8 | https://momodeveloper.mtn.com/content/html_widgets/8rt2v.html and https://momodeveloper.mtn.com/content/html_widgets/sefqs.html | Older indexed callback widgets | Not established | Fetch returned error-page content; not used as evidence; current D3 supplies callback facts instead |

D1's exact instruction is: **“Persist transaction state before initiating MoMo
requests.”** D2 explains that the reference supplied to POST identifies the
resource for subsequent GET and that reuse of an existing reference returns a
duplication error. These facts establish the relevant identity/recovery contract.

The exact Collection operation schema, Cameroon environment/endpoint and merchant
terms were **not certified before the stop**. D3's `X-Callback-Url` example
explicitly concerns Deposit-V1; this report does not infer a complete
RequestToPay header schema from that example.

## 3. Stop finding — provider reference is not durable before submission

Prefix `B` below means `.migration-backup/backend/`.

### Source chain

1. `B/app/Services/PaymentService/MtnService.php::processTransaction`, lines
   45–55, obtains the native payload, resolves merchant configuration and rejects
   merchant/payment currency disagreement.
2. Lines 57–59 obtain the token and generate a new UUID in memory.
3. Lines 63–75 submit `/collection/v1_0/requesttopay`, with the UUID as both
   `X-Reference-Id` and `externalId`.
4. Lines 77–80 throw for a non-202 response. A transport exception likewise
   leaves this call before the later persistence.
5. **Only after the provider POST returns 202**, lines 82–94 create
   `PaymentProcess`, saving its UUID, `mtn_reference_id`, original context and
   configuration fingerprint.
6. `B/app/Http/Controllers/API/v1/Dashboard/Payment/MtnController.php` first
   looks up that persisted process: callback lines 36–41; authenticated status
   lookup lines 88–95. Missing processes are rejected, not reconstructed.
7. `B/app/Console/Commands/ReconcilePendingMtnPayments.php::handle`, lines
   51–55, enumerates stored MTN processes with an unresolved reference. It cannot
   enumerate an MTN reference which was never stored.

**PROVEN FROM OFFICIAL MTN SOURCE:** RequestToPay is asynchronous; the original
request reference is required for status lookup; transaction state should be
persisted before submitting requests (D1–D3).

**PROVEN FROM AGENDAALLY SOURCE:** the provider UUID/process is persisted after
the outbound collection request, leaving a submission-to-persistence gap.

### Failure window and financial consequence

If MTN accepts the request but AgendaAlly receives a transport timeout, the
original UUID has no durable `PaymentProcess`. The same gap exists if MTN returns
202 and the subsequent database creation fails or the process terminates.
MTN may subsequently collect after Customer authorization; the normal callback,
Customer status and scheduled reconciliation paths have no saved process
through which to recover that outcome. This is **lost provider payment identity
and blocked automatic reconciliation**, not evidence that money was collected
in this review.

The native accounting adapter already persists an in-flight contribution before
the network call. `ProviderContributionAdapter.php`, lines 55–62, rejects a new
selected-method charge while its original funding context remains committed,
pending, confirmed or review-required; lines 120–134 persist pending contexts.
This is an important containment: **an automatic duplicate native Customer
charge was not demonstrated, and is not claimed**. It does not retain the MTN
UUID or make the missing provider process recoverable. Simply clearing the
pending context or initiating another UUID is not an acceptable recovery fix.

Successful canonical settlement/replay guards therefore do not prove safe
request submission or safe recovery of this unknown outcome.

### Routes, actor, reachability and exercise

- Native route declarations: `routes/api.php` lines 412–413,
  `mtn-process` and `mtn-process/{referenceId}/status` in the payment dashboard
  group; line 1661, webhook `mtn/payment`.
- A legitimate Customer initiating an otherwise eligible MTN payment would
  encounter this failure window if activation were separately allowed.
- Normal real Customer MTN checkout is currently unavailable under the accepted
  non-testing activation gate. The running public catalog contains Wallet/Cash
  only. No actual merchant configuration/process rows exist in the protected
  development snapshot.
- No provider request, financial exploit, real or synthetic callback, duplicate
  collection or failure injection was exercised.
- This is a **material activation-contract blocker**, not a newly established
  immediately exploitable financial P0. No activation gate was bypassed.

### Minimum correction proposal — approval required, not implemented

1. Design a durable server-owned MTN attempt/reference binding **before** the
   outbound collection POST. Bind it to the original checkout, payable, Shop,
   funding event, merchant owner/configuration revision, exact amount/currency,
   environment and collection mode.
2. Persist an explicit submission/unknown-outcome lifecycle. A transport timeout,
   uncertain response or local failure after possible submission must preserve
   the same attempt and reference for authenticated reconciliation, not authorize
   a new charge or declare definitive failure.
3. Ensure all callback/status/scheduled recovery paths can discover that original
   attempt after the failure window. Preserve accepted canonical accounting and
   unresolved-attempt retry guards.
4. Establish whether existing schema supports an enforceable immutable binding.
   **No schema decision is approved here.** If material identity/revision schema
   changes are necessary, report **MTN SCHEMA DESIGN REQUIRED** and obtain
   approval before migration. Do not conceal financial identity in mutable JSON.
5. Under separately approved non-activating work, verify timeout followed by
   success/failure, provider-accepted/local-save failure, rollback/recovery,
   repeat terminal reconciliation, rotation and two-connection contention with
   isolated synthetic fixtures.

Section 51 requires STOP before broad redesign. Sections 40/41 retain a separate
schema gate. No implementation was attempted and the remaining certification
test programme is deferred pending approval.

## 4. Contract facts and unresolved scope

### Capability, actual payment currency and environment

**MTN CAMEROON + XAF = INTERNALLY DECLARED / EXTERNALLY UNCONFIRMED in this
interrupted review.** The inspected official generic pages do not establish the
specific Cameroon Collection/XAF contract. This is not a claim that Cameroon
or XAF is unsupported, or that no such documentation exists elsewhere.

D4 explicitly says public sandbox uses **EUR**, target `sandbox`. An XAF native
quote cannot be submitted as EUR under the approved same-currency contract.
Public EUR sandbox testing is not Cameroon/XAF certification. Obtain an official
Cameroon/XAF test-environment contract or separately scope EUR-only technical
sandbox testing; neither is authorized automatically by this finding.

Accepted Phase 2C supplies server-owned native Product/Booking quote and currency
validation; Phase 3A separates display currency from actual payment eligibility.
MTN independently checks native payload currency against resolved merchant
currency. No browser display selection, conversion, FX or quote bypass was added.
Full Phase 3B display-currency and environment tests were deferred at STOP.

### Exact native product/initiation

AgendaAlly targets direct **MTN MoMo Collection RequestToPay v1_0**:

- Token: POST `{base_url}/collection/token/`, Basic API user/key plus subscription
  key; subsequent Bearer token.
- Initiation: POST `{base_url}/collection/v1_0/requesttopay`.
- Status: GET `{base_url}/collection/v1_0/requesttopay/{reference}`.
- Payer: `MSISDN` and requested phone; server-generated UUID; external ID equals
  UUID; native total minor units formatted into a two-decimal amount string.
- Merchant/environment: resolved native GatewayConfig, subscription key and
  `X-Target-Environment`; credentials select context rather than an explicit
  payee body field.
- Native callback URL is currently a JSON `callbackUrl` field. Exact current
  Collection operation schema/header acceptance is not certified at this stop.
- HTTPS base URL check exists; default endpoint is sandbox. Phase 3A structural
  readiness requires an explicit non-sandbox HTTPS endpoint and sandbox EUR.

This implementation is **not fully contract-aligned/certified** because of the
durability finding. Exact Cameroon amount precision, limits, MSISDN rules,
endpoint/environment and merchant/payee contract still require official evidence.
No inference is made from XAF's general currency characteristics.

### Verification, statuses, merchant and configuration revision

For an existing stored process the controller authenticates a status lookup
using resolved merchant credentials and rejects a changed configuration
fingerprint. It checks provider identity, matching `externalId`, exact parsed
amount, current merchant currency and frozen intent currency before passing proof
to the accepted `BaseService::afterHook`/contribution boundary.

| Provider status | Native handling | Official generic contract |
|---|---|---|
| PENDING | Progress; not paid | Nonfinal (D2) |
| SUCCESSFUL | Paid only through verified-intent/canonical settlement | Final success (D2) |
| FAILED | Native canceled Transaction status | Final failure (D2); does not establish a provider cancellation API |
| Unknown/new state | Rejected (`null` mapping) | No invented success semantics |
| Rejection/expiry/error reasons | Not separately certified as top-level statuses | D4 lists test scenarios; reason labels are not automatically status enums |

Initiation acceptance, callback hint, redirect, Customer reference, selected
provider, credentials and Booking/Order lifecycle alone are not payment proof.

`merchant_verified=true` in application proof means the authenticated lookup
used the expected unchanged credential/configuration context. The inspected
controller does **not** compare an independent returned merchant/payee identifier.
It is not cryptographic proof of legal merchant ownership or settlement account.
Do not advertise that broader merchant claim.

The accepted contribution adapter freezes configuration source/row/revision
along with allocation/owner/currency. MTN saves a credential/routing fingerprint
in the post-202 process. Rotation causes fingerprint rejection, not historical
reinterpretation; recovery under old authorized configuration and complete
immutable attempt/revision sufficiency are not certified here.

### Ownership, accounting and native journeys

Accepted Phase 3A separates platform country-owned merchant profiles from
Shop-owned profiles without fallback or cross-Shop credential reuse. Native
merchant encryption, secret-redacted API responses and blank-preserves-current
editing are accepted baseline evidence, not a fresh Phase 3B storage/UI test.
No real values were accessed or copied into this report.

Platform-native custody represents platform-controlled accounting contribution
and Vendor liability; Vendor-direct principal remains outside platform payable.
It does **not** prove external legal custody. Vendor-direct is **PARTIAL
internally / UNKNOWN externally**: credentials and synthetic accounting exist,
but MTN's terms permitting Vendor merchant relationships plus AgendaAlly
orchestration were not established.

Prior native synthetic Product/Booking MTN, replay/replacement and Wallet mixing
remain accepted baseline results. The 100 gross / 10 commission / 90 payable
model and Wallet 40 + direct MTN 60 / commission 10 / payable 30 / direct
principal 60 / receivable 0 are baseline accounting evidence only. No new
Phase 3B Product, Booking, mixed-funding or failure-injection certification is
claimed. The full required Wallet 40 + platform MTN 60 retest is deferred.

No multi-Shop split-merchant semantics were added. Existing unsupported native
multi-Shop/quote paths remain fail-closed; comprehensive Phase 3B multi-Shop
certification was deferred. Unsupported tax/tip/delivery/coupon/FX contracts
were not loosened.

### Required configuration — provisional, not a complete Cameroon contract

| Element | Capability requirement | Configuration/readiness requirement | Secret? | Ownership/environment scope |
|---|---|---|---|---|
| Collection subscription key | Not evidence of market capability | Native authentication; confirmed generically by D2/D5 | Yes | Product subscription/environment; legal owner mapping requires confirmation |
| API user | Not evidence of market capability | Native token identity; confirmed by D2 | Sensitive credential identifier | Merchant wallet/country/environment |
| API key | Not evidence of market capability | Native token credential; confirmed by D2 | Yes | API user/environment |
| Bearer token | No | Runtime authentication; derived, not merchant setup | Yes | Same authenticated API context; expiry applies |
| Target environment | Relevant environment contract required | Native header; sandbox value confirmed by D2/D4 | No | Environment; exact Cameroon value unknown |
| Base URL | Relevant endpoint contract required | Native HTTPS endpoint; explicit non-sandbox structural check | No | Environment; exact Cameroon endpoint unknown |
| Actual currency | Required external currency capability | Must equal authoritative quote and provider evidence; sandbox EUR documented | No | Payment and merchant/environment |
| Callback origin/registered host | No currency capability proof | Structural native readiness; provider callback host/HTTPS documented in D3 | No | Merchant API user/environment/domain |
| Shop/platform country row and revision | Internal ownership, not external capability | Accepted native adapter identity/readiness | No | Platform country or Shop only |
| Independent merchant/payee/settlement identifier | External contract unknown | Must establish available authoritative evidence before certification | Depends on provider contract | Legal merchant relationship; not fabricated |

## 5. Refund, settlement, payout and onboarding boundaries

- **Refund:** generic sandbox refund scenarios are documented by D4. Exact
  Cameroon Collection refund eligibility/operation/terms are **UNKNOWN**.
  Accepted baseline says native MTN refund dispatch/verification is missing.
  Implementation **NO**; newly verified **NO**; activation-ready **NO**.
- **Reversal, cancellation, correction:** exact collection operations and market
  rights **UNKNOWN / REQUIRES MTN CONFIRMATION**. A FAILED status mapped to native
  canceled is not proof of a cancellation/refund API.
- Activating collections without a verified refund path leaves AgendaAlly unable
  to promise automated returns through the original MTN rail. An approved
  operational/refund policy and provider process must exist before activation.
  No manual financial workaround was implemented.
- **Settlement:** generic API credentials authorize a wallet/country context
  (D2), but the actual AgendaAlly Cameroon merchant wallet/bank relationship,
  beneficial/legal ownership, settlement timing, fees and withdrawability are
  **UNKNOWN**. No real merchant has been provisioned or validated. Internal
  `platform` custody does not establish those facts.
- **Vendor payout:** a supported, authorized, funded external settlement/payout
  mechanism with reconciliation and operational controls is still required to
  discharge platform Vendor liabilities. No rail was implemented or certified.
  Vendor-direct principal is not a platform principal liability.
- **Onboarding:** generic developer account/subscription, API-user/key and
  registered HTTPS callback requirements are documented by D2/D3/D5. Exact
  Cameroon business verification, market approval, legal contract, settlement
  account and delegated Vendor orchestration requirements need **MTN/acquirer
  confirmation**. No specific legal requirement is invented.

## 6. MTN readiness matrix

Statuses describe this interrupted certification, not production permission.
Accepted synthetic evidence is expressly historical.

| Area | Status | Exact limitation/blocker |
|---|---|---|
| Capability | UNKNOWN | Code declares XAF; Cameroon Collection/XAF externally unconfirmed here; generic sandbox EUR is confirmed |
| Merchant configuration | PARTIAL | Owner-scoped native foundation accepted; actual profiles zero; official Cameroon requirements incomplete |
| Secret handling | PARTIAL | Accepted native encryption/redaction retained; no fresh Phase 3B storage/UI/log certification |
| Environment separation | PARTIAL | Structural sandbox/EUR and HTTPS policy exists; exact Cameroon test/production contract not certified |
| Payment initiation | BLOCKED | MTN UUID/process saved after external submission |
| Authoritative verification | PARTIAL | Stored-process authenticated verifier exists; absent process cannot recover accepted request |
| Amount verification | PARTIAL | Exact native verifier check inspected; full Phase 3B evidence matrix/market precision deferred |
| Currency verification | PARTIAL | Frozen/current/provider match inspected; Cameroon contract/new tests deferred |
| Merchant verification | PARTIAL | Credential-context fingerprint, not independent returned legal payee verification |
| Callback/notification | PARTIAL | Hints trigger authenticated lookup; stored process required; full Collection callback schema/deployment not certified |
| Reconciliation | BLOCKED | No discoverable provider reference after pre-persistence timeout/save failure |
| Idempotency | PARTIAL | Accepted canonical replay guards; unresolved guard blocks native re-initiation; full submission/recovery contract blocked |
| Timeout recovery | BLOCKED | Original UUID missing from durable process if submission response/save fails |
| Product integration | PARTIAL | Accepted synthetic native integration; required new Phase 3B journey deferred |
| Booking integration | PARTIAL | Accepted synthetic native integration; required new Phase 3B journey deferred |
| Mixed funding | PARTIAL | Accepted synthetic model retained; complete Phase 3B retests/failure injections deferred |
| Multi-Shop | BLOCKED | No approved split-merchant MTN contract; existing unsupported paths not loosened |
| Refund/reversal | BLOCKED | Native refund missing; exact market operations/terms unknown |
| External settlement | UNKNOWN | No actual Cameroon merchant/settlement evidence |
| Vendor payout dependency | BLOCKED | No certified external mechanism to discharge platform liability |
| Cameroon onboarding | UNKNOWN | Generic developer steps are not market/legal merchant approval |
| Production concurrency | UNKNOWN | PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED; production access prohibited |

## 7. Verification and protected state

**Fresh Phase 3B focused tests: 0. Fresh selected regressions: 0. Fresh contention:
NOT RUN.** The material-contract stop occurred during source/documentation review,
before implementation or synthetic financial fixture work. Required capability,
17 verification cases, accounting, failure injections and two-connection
contention are deferred, **not passed**.

Prior accepted Phase 3A receipts remain historical: focused **50 tests / 275
assertions**; native/accounting **78 / 532**; payout/Booking authority **44 / 429**;
selected containment/provider **454 / 3,067**. These overlap; they are not new
Phase 3B results. Prior native SQLite contention does not certify this missing
MTN submission/recovery flow. No **SQLITE CONCURRENCY VERIFIED** claim is made
for Phase 3B. No whole-project-green claim; older broad hardening remains failed.

Read-only checks performed in this review:

- Before snapshot exactly matches accepted Phase 3A after snapshot.
- Before/after snapshots have identical codec and **55/55 table counts and
  SHA-256 fingerprints**. Provider configuration and financial records unchanged.
- **408/408 schema entries** equal the accepted Phase 3A schema snapshot.
- Allocations/contribution contexts remain **0/0**.
- Platform merchant, Shop merchant and global payload rows remain **0/0/0**.
- All **12** legacy Product Orders remain `fulfillment_financial_state=unverified`.
- Foreign-key errors **0**.
- Running development GET `/api/v1/rest/payments` returns only **wallet,cash**.
  This is catalog verification, not an authenticated browser journey or provider
  transaction. No native UI changes were made or certified.

Receipts: `.local/payment-phase3b-before.json`,
`.local/payment-phase3b-after.json`,
`.local/payment-phase3b-schema-after.json` and
`.local/payment-phase3b-protected-comparison.json`.
The unchanged full protected-table inventory/hashes and row serialization/order
methodology are in accepted Phase 3A section 11; this review compares the entire
snapshots, not just totals.

## 8. Required 40 decisions — interrupted certification answers

1. **Cameroon collection officially confirmed?** Not established from inspected
   first-party pages before STOP; UNKNOWN, not disproven.
2. **XAF officially confirmed?** Not for exact Cameroon Collection scope here.
   Generic public sandbox is explicitly EUR.
3. **Exact API?** Direct MoMo Collection RequestToPay v1_0, token and status GET.
4. **Initiation matches official contract?** Not certified; durability ordering
   conflicts with D1 and prevents D2/D3's reference-based recovery.
5. **Authoritative payment?** Authenticated expected-configuration status GET,
   known SUCCESSFUL status, matching reference/amount/currency and accepted
   original canonical verification/settlement boundary.
6. **Initiation success alone paid?** No.
7. **Callback hint alone paid?** No.
8. **Amount/currency verified?** Checks inspected; prior synthetic evidence
   accepted; full new Phase 3B cases deferred.
9. **Merchant ownership verified?** Expected credential-context fingerprint
   checked; independent legal/payee ownership not proven.
10. **Identity immutable/replay-safe?** Canonical baseline guards exist, but
    provider identity is not durable before submission. Overall BLOCKED.
11. **Timeout/unknown outcome?** Pending native accounting remains; original
    provider process/reference may never be saved; normal recovery cannot find it.
12. **Recover later success?** Stored-process path exists; missing-process
    submission window cannot safely recover through current normal paths.
13. **Duplicate reconciliation contribution?** Prior canonical replay guards
    accepted; no fresh Phase 3B duplicate/failure matrix run.
14. **Replacement Transaction duplicates commission/payable?** Prior accepted
    accounting containment; not newly certified in Phase 3B.
15. **Platform structurally ready?** Complete synthetic configuration can meet
    Phase 3A structural checks; actual profiles absent and operational readiness
    BLOCKED by request identity/recovery.
16. **Vendor-direct genuine support?** PARTIAL internally, UNKNOWN externally.
17. **Vendor/platform configuration separate?** Yes in accepted owner-bound
    architecture; no fallback; no configuration change here.
18. **Product XAF/MTN verified?** Accepted synthetic native baseline only; new
    Phase 3B certification deferred.
19. **Booking XAF/MTN verified?** Same limitation.
20. **Mixed Wallet/MTN verified?** Accepted synthetic baseline only; complete
    required fresh scenarios deferred.
21. **Multi-Shop?** No new support; unsupported contracts remain fail-closed.
22. **Refund/reversal documented?** Generic refund sandbox scenarios yes;
    exact Cameroon Collection refund/reversal/cancellation/correction unknown.
23. **Implemented?** Native MTN external refund dispatch/verification missing
    in accepted baseline; no implementation here.
24. **Activation-ready?** No.
25. **Actual platform settlement destination?** UNKNOWN; no established
    AgendaAlly Cameroon merchant/wallet/bank relationship or legal ownership.
26. **External payout still required?** Approved supported merchant-to-Vendor
    settlement/disbursement capability, funding and operational reconciliation.
27. **Cameroon onboarding remains?** Market/currency/test environment,
    merchant eligibility/contract, production provisioning, callback registration
    and settlement terms require exact official confirmation.
28. **Real credentials before next step?** No for the recommended bounded
    non-activating design. A future approved provider-environment exercise would
    require the correctly provisioned environment credentials; not requested here.
29. **Schema changes required?** None performed. Design must determine whether
    immutable attempt/revision changes are necessary; separate approval required.
30. **Existing financial records changed?** No; all 55 protected fingerprints
    identical.
31. **12 legacy Orders unverified?** Confirmed directly.
32. **Focused tests/assertions?** New Phase 3B: 0/0 due mandatory STOP.
33. **Selected regressions/assertions?** New Phase 3B: 0/0; historical counts in
    section 7 are not new certification.
34. **SQLite contention?** Not run for Phase 3B; not certified for this flow.
35. **Production concurrency?** PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.
36. **Controlled Cameroon/XAF sandbox/test activation ready?** NO.
37. **Controlled blockers in order?** (a) Approve durable MTN submission identity
    and unknown-outcome recovery design; (b) establish exact Cameroon/XAF test
    contract, not public EUR sandbox inference; (c) establish operation schema,
    merchant/rotation/credential/callback boundaries; (d) complete deferred
    capability/verification/accounting/failure/contention regressions; (e) obtain
    separate credentials/environment/activation-gate approval and operational plan.
38. **Live production ready?** NO.
39. **Production blockers separately?** All controlled blockers plus live
    Cameroon merchant/legal/onboarding and settlement evidence, production
    credentials/deployment/callback/reconciliation operations, refund/reversal
    policy/capability, external Vendor settlement mechanism, production-engine
    concurrency/certification and explicit live activation approval.
40. **Single safest next action?** Separately approve a non-activating MTN
    durable request-identity/unknown-outcome recovery design. Do not activate.

## 9. Final boundary

**STOP.** The section 51 finding is reported, not remediated. No broader redesign,
schema migration, provider transaction, real credentials, account/key creation,
Customer checkout visibility, Vendor-direct activation, refunds, external
payouts, FX, unrelated provider work, production access, legacy classification/
deletion, publishing or automatic activation phase. Await explicit approval.