# AgendaAlly — MTN Cameroon/XAF onboarding inquiry package

Prepared: 2026-10-03. **DRAFT — PREPARED, NOT SENT.**

This package is documentation only. No provider contact, form submission,
account creation, onboarding, credentials, API calls, testing or activation is
authorized by its preparation.

## Sharing instructions — internal

- **External:** send section A as the initial message and attach/copy **section B
  only** as the detailed questionnaire. Replace the sender placeholders.
- **Internal:** sections C–E, these instructions and the completion record
  support later comparison of MTN's response. Do not send the entire document
  or the internal payment audit/readiness reports.
- The initial contact needs no business-registration documents, identity
  documents, bank details or credentials attached. Ask for the applicable
  checklist and approved submission channel first. Supply documents only in a
  separately approved onboarding step after verifying the recipient/channel.
- Intended recipient: **MTN Cameroon MoMo Collections commercial/merchant
  onboarding**, with referral to the **MoMo Open API technical integration/UAT
  team**. The accepted review recorded the public Cameroon Collections contact
  **MoMoCorporate.CM@mtn.com**. This is a routing lead, not proof a particular
  representative can grant approval; no new lookup or contact was performed.
- Preparing or later sending an inquiry does not authorize merchant
  registration, accepting terms, obtaining keys, configuring payments or testing.

## A. Concise initial email/contact-form message — external

**Subject:** AgendaAlly — Cameroon/XAF MoMo Collection marketplace onboarding and test environment

Dear MTN Cameroon MoMo Collections / merchant-onboarding team,

We are preparing AgendaAlly, a multi-Vendor marketplace and service-booking
platform where Customers purchase Products and book Services from independent
Vendors.

Our proposed platform-managed payment flow is: Customer MoMo Collection into
AgendaAlly's approved merchant relationship/account, if permitted; AgendaAlly
records its commission and the remaining Vendor entitlement internally; Vendor
settlement takes place separately. Cash, Wallet and electronic payments have
separate custody/accounting treatment.

We have reviewed MTN's Cameroon Collections information and published
`mtncameroon` / XAF production guidance. We are not claiming MTN merchant,
partner, aggregator or Cameroon/XAF certification status.

Could you confirm which commercial classification and onboarding approvals
apply to this marketplace model, and refer us to the appropriate technical
integration/UAT contact?

Before any testing or activation, we also need the exact approved Cameroon/XAF
non-production environment and access prerequisites. The public sandbox
documentation we reviewed describes EUR; we do not propose converting XAF
transactions to EUR.

The accompanying questionnaire sets out the remaining commercial and technical
clarifications. Please provide current official specifications, the relevant
onboarding checklist and written answers applicable to Cameroon and the
specified environment. This is an information inquiry, not an account,
credential or activation request. Future Vendor-direct and Disbursement
questions are secondary to the initial Customer Collection test.

Kind regards,  
[Sender name]  
[Role / legal business name, if applicable]  
[Business reply email]

## B. Detailed technical questionnaire — external

**AgendaAlly: Cameroon/XAF Collection contract and non-production readiness**

Please answer by question ID. Where possible, identify the applicable **market,
product/API version, environment, document revision/date and official
specification or contract reference**. If a matter requires another team,
please identify that team rather than assuming approval.

Immediate priority is permission for the platform-managed model and the exact
contract for a controlled **non-production Customer Collection test**.
Production requirements are requested for planning, not go-live approval.
Vendor-direct and Disbursement are optional future-model questions.
Please do **not** send API keys, credentials, tokens or real Customer data in
your reply; we are requesting provisioning requirements, not credential values.

### 1. Marketplace/commercial model

**Q01.** Is the following proposed flow permitted in Cameroon for our
marketplace/service-booking model: Customer → MoMo Collection → AgendaAlly's
approved merchant relationship/account → internal commission and Vendor
entitlement accounting → separate Vendor settlement? Please identify applicable
conditions or restrictions on collecting on behalf of independent Vendors.

**Q02.** Which merchant/product classification applies? Is ordinary merchant
onboarding sufficient, or is aggregator, marketplace, payment-facilitator or
another specific approval required? Please identify applicable requirements,
not an assumed classification.

**Q03.** Must individual Vendors/submerchants be separately registered,
verified or onboarded by MTN? Which obligations apply to AgendaAlly and which
apply to each Vendor?

**Q04.** Does MTN offer native split payment, submerchant allocation, marketplace
commission/platform fees or multiple-beneficiary functionality for this model?
If not, may an approved AgendaAlly merchant collect and account for/settle Vendor
entitlements separately, and under which commercial conditions?

**Q05 — secondary future model.** If each Vendor has its own approved MTN
merchant relationship, may AgendaAlly technically initiate Collection using
that Vendor's authorized API credentials? What delegated-access/onboarding/API
model, consent, credential-management and settlement conditions would apply?
We are not requesting activation of this model.

### 2. Cameroon/XAF environment

**Q06.** Which exact approved sandbox/UAT/staging/pre-production environment
should AgendaAlly use for Cameroon/XAF Collection? Please provide its name,
base URL, `X-Target-Environment` value and supported currency, explicitly
confirming whether XAF transactions are permitted there. Please distinguish
non-production routing from published production routing.

**Q07.** How is access provisioned, and must merchant onboarding/approval precede
access? Does it use a simulated or real merchant wallet; can any real funds
move; are specific Cameroon test credentials/accounts and test MSISDNs issued?
Please describe access prerequisites without sending credential values.

**Q08.** The public sandbox documentation reviewed describes EUR. Is that
sandbox a required technical step before Cameroon onboarding, separate from
Cameroon/XAF UAT, permitted to test XAF, and/or not representative of Cameroon
payment behavior? Please clarify which aspects it can and cannot certify;
we do not propose relabelling or converting XAF payments to EUR.

**Q09.** What Collection scenarios and test accounts/numbers are available in the
approved environment, including success, failure, rejection, pending/delayed,
timeout/uncertain submission, duplicate reference and callback failure? How are
test access and successful evidence transitioned to production approval?

### 3. RequestToPay

**Q10.** Please provide the current authoritative Cameroon Collection
RequestToPay and related token/status specification, preferably a versioned
OpenAPI/API document, with its effective date and non-production/production
differences. What are the exact RequestToPay endpoint and HTTP method?

**Q11.** What authentication and headers are required: token acquisition,
subscription key, Bearer token, `X-Reference-Id`, `X-Target-Environment`,
callback URL/header and content type? Please specify required versus optional
headers, token expiry/renewal and product/environment scope.

**Q12.** What is the current request body with required/optional fields, types,
length constraints and extra-field acceptance? Please cover `externalId`,
`amount`, `currency`, payer `partyIdType`/`partyId`, `payerMessage`, `payeeNote`
and any callback field. What are `externalId` semantics, allowed values and
echo/correlation behavior in status responses and notifications?

**Q13.** What response acknowledges valid initiation, including HTTP status,
body/headers and errors? Please distinguish acceptance for asynchronous
processing from confirmed collection and identify errors that definitively
prove no collection was created versus those requiring reconciliation.

### 4. Identity/retry/reconciliation

AgendaAlly generates and durably commits a unique server-owned request reference
before RequestToPay. After an ambiguous result, it retains the reference and
queries the original transaction rather than generating another reference.

**Q14.** Please confirm applicability to the current Cameroon contract:
is `X-Reference-Id` the correct durable RequestToPay/status identity, and what
format, uniqueness scope and retention period apply?

**Q15.** Following an ambiguous timeout/network result, should the merchant
query that original reference rather than submit a new RequestToPay? What
exact recovery procedure applies, including temporary “not found,” provider
outage and uncertainty about whether the request was received?

**Q16.** What happens when RequestToPay is submitted again using the same
`X-Reference-Id`: rejected duplicate, idempotent replay or another defined
behavior? Please specify responses and whether a different reference could
create another collection for the same purchase.

**Q17.** What is the exact authenticated status endpoint and response schema?
Please give every possible state, the meanings of PENDING/SUCCESSFUL/FAILED
and any additional states, terminal-state rules, possible later corrections
and handling of unknown/unrecognized states.

**Q18.** Is SUCCESSFUL obtained through authenticated status lookup the
authoritative Collection confirmation? Which returned identity, amount,
currency and merchant fields must be checked, and what does this confirmation
prove about collection versus subsequent bank settlement?

**Q19.** What polling interval/backoff, rate limits and maximum duration or
request expiration apply? When must unresolved attempts be escalated for
manual/provider investigation, rather than treated as failed or resubmitted?

### 5. Callback

**Q20.** How is the current Collection callback host/URL registered and supplied
per request? Is `X-Callback-Url`, a JSON field or another mechanism required?
Must its exact domain match the registered host; are subdomains allowed?

**Q21.** Please resolve the conflicting public HTTP/HTTPS sandbox instructions
for the applicable Collection environments. What scheme, allowed ports,
public reachability, TLS version, certificate/trust-chain and source-IP/network
requirements apply to Cameroon testing and production?

**Q22.** What HTTP method and exact payload/identity fields are sent for
RequestToPay callbacks? Is there authentication/signature, and how should it
be verified? What acknowledgement/status and response-time requirements apply?
Please provide the Collection contract rather than a Deposit/Transfer example.

**Q23.** Are callbacks retried, duplicated, delayed or delivered out of order;
what delivery guarantees exist? Is notification itself authoritative payment
evidence, or must the merchant confirm by authenticated status lookup?
AgendaAlly intends to treat callbacks as signals and reconcile authoritatively
unless your current contract requires a different model.

### 6. Amount/MSISDN

**Q24.** What are the authoritative XAF amount rules: allowed decimal places,
integer-only requirements, JSON string/number representation, rounding and
minimum/maximum amounts? Please distinguish testing from production and any
merchant/account-specific limits.

**Q25.** How are Collection fees calculated/charged and reported? Do they alter
the Customer-facing RequestToPay amount, debit an additional Customer charge,
or reduce merchant settlement? Please distinguish gross collected amount
from fees/net balance and provide the applicable tariff/approval process.

**Q26.** What exact payer identifier/`partyIdType` and Cameroon MSISDN format
apply: national versus international, country code, acceptance/omission of
`+237`, prefixes, normalization and active-account/network eligibility?
Are authoritative validation facilities available?

**Q27.** Which synthetic test MSISDNs, formatting/eligibility cases and amount
boundary scenarios should we use in the approved environment? Please provide
test identities only, not real Customer numbers.

### 7. Merchant identity/credentials

**Q28.** How are developer account, product subscription key, API user, API key,
access token, target environment, Cameroon merchant wallet/account, legal
merchant entity and settlement beneficiary related? Which credentials/accounts
are environment- and product-specific?

**Q29.** Which provider-side identifier or authenticated relationship determines
the actual receiving merchant for a successful Collection? What official
record/response verifies it so AgendaAlly can retain the correct original
merchant identity with each payment?

**Q30.** What credential rotation/revocation and historical-transaction access
rules apply? How can an approved merchant reconcile an unresolved original
reference after rotation or account changes without reinterpreting it under
a different receiving merchant?

### 8. Settlement

**Q31.** For the approved model, where do funds first arrive after SUCCESSFUL:
which merchant wallet/account, in which currency and under which legal
beneficiary relationship? What availability/hold timing applies?

**Q32.** How are funds liquidated/withdrawn, including bank settlement availability,
eligible beneficiary accounts, funding/withdrawal restrictions, timing/SLA,
fees and failed-settlement handling? Please distinguish collection confirmation
from bank receipt and requirements for separate Vendor settlement.

**Q33.** Which transaction/account/settlement identifiers and reports provide
evidence of the receiving account, gross collection, fees, net balance and
completed bank settlement? Are these available through APIs, portal or exports?

### 9. Refund/reversal

**Q34.** What refund, reversal, cancellation and correction capabilities are
available for Cameroon Collection, and how do these operations differ?
Are full and partial refunds supported, with which eligibility, windows,
limits, fees and non-production availability?

**Q35.** Please provide the applicable product/API name, endpoint/method,
authentication, original payment identifier, amount/currency rules and
idempotency reference/key for each supported return/correction operation.

**Q36.** Are these operations asynchronous? What states, authenticated outcome
lookup, callback, polling, terminal rules and ambiguous-timeout recovery apply?
How are refunds matched to the original successful collection?

**Q37.** What approved operational process applies when a refund cannot be
automated, a transaction is disputed or an outcome conflicts with settlement
reports? Which procedures must exist for UAT and before production go-live?

### 10. Future Disbursement

**Q38 — future only.** Is Cameroon/XAF MoMo Disbursement available as a potential
Vendor payout rail for this model? If so, what commercial/merchant eligibility,
currency, recipient requirements, limits and fees apply?

**Q39 — future only.** What separate product approval, subscription, credentials,
sender account/funding, settlement and test environment apply? Are Collection
and Disbursement accounts/credentials separate? Please provide the relevant
specification and outcome-reconciliation requirements. This future rail is
**not required for the initial Customer Collection test**.

### 11. Reconciliation/reporting

**Q40.** Which facilities are provided for transaction lookup, lists/history,
account balance, merchant portal, downloadable reports and API reporting?
Please specify identifiers, access scopes, retention, pagination, time zones
and report availability/delivery schedules.

**Q41.** What settlement, fee, dispute, refund/reversal and payout reports allow
end-to-end financial reconciliation? Which fields correlate original Collection
to its returns and settlement movements?

**Q42.** What provider reconciliation/support procedure resolves discrepancies,
long-pending transactions, missing notifications or unavailable APIs, and which
team/evidence/escalation channel should merchants use?
AgendaAlly needs to reconcile Collections to internal funding, commission,
Vendor entitlement, refunds and later settlement/payout.

### 12. UAT/production certification

**Q43.** Please provide the actual Cameroon onboarding checklist for this
marketplace model: business registration, tax information, merchant
wallet/bank requirements, agreement, applicable approvals/security requirements,
callback domain and document-submission channel. Please identify requirements
applicable to this model rather than assumed licensing classifications.

**Q44.** What approval process, responsible teams and prerequisites apply for
non-production access/UAT and production go-live respectively? Which agreements
or conditions must be satisfied at each stage?

**Q45.** Is formal technical certification required before production? Please
provide mandatory test cases/evidence, including success/failure/pending,
callback, timeout/retry, duplicate handling, reconciliation and refund
scenarios, together with the production-readiness checklist.

**Q46.** Who should provide authoritative written confirmation of the commercial
model, non-production contract and certification result? Please supply current
versioned specifications/terms, clarify conflicting public instructions and
identify how material contract changes are communicated to merchants.

## C. One-page AgendaAlly technical summary — internal, do not send

**Purpose:** compare MTN's eventual answers with the accepted design, not claim
provider certification or invite a source-code review.

AgendaAlly supports Customer Product purchases and Service bookings across
independent Vendors. The immediate proposed MTN model is platform-managed
Collection into an approved platform merchant account, if permitted.
Commission and remaining Vendor entitlement are recorded internally; external
Vendor settlement is a separate responsibility. Cash, Wallet and electronic
funding retain their original custody semantics. Internal allocations do not
constitute provider split payments or evidence of external settlement.

- Payment-attempt identity is generated server-side; the provider reference
  and original funding/merchant context are retained before external dispatch.
- Ambiguous submission outcomes preserve the same reference and unresolved
  attempt. Reconciliation queries the original request; uncertainty does not
  justify a new charge or reference.
- Provider initiation acceptance, a callback, a redirect or a Customer-supplied
  claim is not sufficient funded-accounting authority.
- Authoritative provider status must match the original reference, amount,
  actual payment currency and merchant context before funded accounting.
  Repeated verification must be idempotent.
- Accounting retains original collection custody. Vendor entitlement,
  commission and provider principal collection are distinct; Vendor-direct
  principal is not platform-held principal.
- Vendor-direct orchestration and future Disbursement require separate
  commercial/product approval. A Disbursement rail is not a prerequisite
  for the initial controlled Customer Collection test.

Compare responses first against the platform model, approved non-production
environment, exact operation/callback contract, amount/payer rules and merchant
identity. Then assess settlement, refund/reporting obligations and production
certification. Provider clarification does not automatically approve code,
schema, configuration, onboarding or activation changes.

**Unchanged decisions:** public EUR technical sandbox CONDITIONAL GO but not
authorized here; Cameroon/XAF controlled testing UNKNOWN; production NO-GO.
Accepted durable identity/recovery containment remains within its existing
bounded scope. No new certification is claimed.

## D. Response-mapping table — internal, do not send

Every questionnaire ID has a corresponding decision. “Unblocks” identifies
what the answer informs; it does not mean a positive answer automatically
grants approval. Contract/legal questions require appropriate review.

| Question | Decision or material blocker informed | Priority |
|---|---|---|
| Q01 | Permission/restrictions for platform-managed multi-Vendor collection | Initial model/test |
| Q02 | Correct merchant/product classification and special approvals | Initial model/test |
| Q03 | Vendor/submerchant onboarding dependencies | Initial model/test |
| Q04 | Provider split/commission capability versus separately approved platform accounting/settlement model | Initial model/test; production |
| Q05 | Delegated Vendor-owned credential orchestration | Optional future Vendor-direct |
| Q06 | Exact Cameroon/XAF non-production host, target and currency | Initial test |
| Q07 | Test access prerequisites, account ownership, isolation and risk of real funds | Initial test |
| Q08 | EUR sandbox prerequisite/limits versus actual Cameroon/XAF UAT | Initial test; no FX inference |
| Q09 | Available controlled scenarios and test-to-production evidence path | Initial test; production |
| Q10 | Versioned authoritative RequestToPay/token/status contract | Initial test |
| Q11 | Required headers/authentication, callback transmission and token handling | Initial test |
| Q12 | Exact body/types/externalId correlation contract | Initial test |
| Q13 | Acceptance/error semantics and no-collection versus ambiguous result | Initial test; recovery |
| Q14 | Original reference identity, format, uniqueness and retention | Initial test; identity compatibility |
| Q15 | Same-reference ambiguous/“not found”/outage recovery procedure | Initial test; recovery |
| Q16 | Duplicate-reference behavior and safe retry contract | Initial test; duplicate prevention |
| Q17 | Complete provider states/terminal/late-correction contract | Initial test; reconciliation |
| Q18 | Authoritative paid evidence and reference/amount/currency/merchant checks | Initial test; accounting authority |
| Q19 | Polling/backoff/expiry/rate limits and unresolved-case escalation | Initial test; operations |
| Q20 | Registered callback host and header/body URL requirements | Initial test |
| Q21 | HTTP/HTTPS conflict, port, reachability, TLS and network requirements | Initial test; production deployment |
| Q22 | Collection callback method/payload/authentication/acknowledgement | Initial test |
| Q23 | Delivery semantics and notification-versus-status authority | Initial test; reconciliation |
| Q24 | XAF representation, precision, rounding and transaction limits | Initial test; amount compatibility |
| Q25 | Gross versus net, Customer fees and applicable merchant tariff | Initial test; settlement/accounting |
| Q26 | Authoritative Cameroon payer format/network/account validation | Initial test |
| Q27 | Safe synthetic payer and amount-boundary fixtures | Initial test |
| Q28 | Product/environment credential isolation and legal account relationships | Initial test; ownership |
| Q29 | Evidence binding the original receiving merchant/beneficiary | Initial test; immutable merchant context |
| Q30 | Original-reference access and merchant continuity after rotation | Recovery; production operations |
| Q31 | Immediate funds destination, currency, control and holds | Initial model/test meaning; production |
| Q32 | Bank liquidation, Vendor settlement conditions, timing/failure/fees | Production settlement |
| Q33 | Independent collection/net/bank-settlement evidence | Reconciliation; production |
| Q34 | Local full/partial return/correction capability and restrictions | Refund design; UAT scope; production |
| Q35 | Refund identity/API/auth/amount/idempotency contract | Future refund design; production |
| Q36 | Refund lifecycle and original-payment reconciliation | Future refund design; production |
| Q37 | Approved dispute/manual-exception process | UAT scope; production operations |
| Q38 | Cameroon/XAF Vendor payout commercial/product eligibility | Optional future Disbursement |
| Q39 | Separate funded sender/credentials/UAT and payout verification | Optional future Disbursement |
| Q40 | Lookup/history/balance/export/report facilities and retention | Reconciliation; production |
| Q41 | Collection-return-fee-settlement/payout matching | Reconciliation; production |
| Q42 | Discrepancy/outage/long-pending escalation and required evidence | Initial test; operations |
| Q43 | Actual market/model-specific documents/security/agreements | Onboarding prerequisite decision |
| Q44 | UAT versus go-live approval sequencing | Initial test; production |
| Q45 | Required technical scenarios, evidence and certification | UAT test plan; production |
| Q46 | Authoritative approval ownership, document version and change control | All contract decisions |

After receiving answers: record evidence in E; distinguish Cameroon-specific
from generic and test from production; reconcile conflicts; compare against C
and the accepted readiness baseline; identify proposed changes and separate
approval gates. Do not overwrite accepted identity or readiness decisions
merely because an inquiry received a reply. Missing or partial answers remain
unresolved. Future payout answers must not delay an otherwise separately
approved initial Collection test solely because they are optional.

## E. MTN-answer recording template — internal, unpopulated

Copy this block once per question. **No MTN answers have been received or
populated.** Keep the answer and interpretation separate. Retain a redacted
official source/reference; never copy credentials, Customer data or secrets.

| Field | Entry to complete after response |
|---|---|
| QUESTION ID | [Q__] |
| MTN ANSWER | [Exact relevant answer or faithful excerpt; currently blank] |
| SOURCE | [email / official document / portal / contract / API specification; document/version/reference] |
| DATE | [Answer/document date and date received] |
| MTN CONTACT/TEAM | [Responsible business team/role; verified authority] |
| MARKET | [Cameroon / generic; other scope if explicitly stated] |
| ENVIRONMENT | [sandbox / UAT / production; exact name if different] |
| STATUS | [Select after review: CONFIRMED / PARTIAL / UNKNOWN / CONFLICTING] |
| AGENDAALLY IMPACT | [Decision informed; interpretation, conditions and missing evidence] |
| CODE CHANGE REQUIRED | [YES / NO / UNKNOWN; no change approved by recording] |
| SCHEMA CHANGE REQUIRED | [YES / NO / UNKNOWN; no migration approved by recording] |
| ACTIVATION BLOCKER | [YES / NO; explain remaining blocker or conditional applicability] |

Useful supplementary fields: specification effective/revision date,
product/API version, conflicting-source reference, related question IDs,
follow-up needed and reviewer. Do not classify an answer CONFIRMED solely
because it came by email; check its team authority, scope, conditions and
consistency. A generic or production-only answer cannot certify Cameroon UAT.

## Internal preparation and protected-state record

This file supplies A–E and maps all **46** detailed question IDs. A and B exclude
internal vulnerabilities/security history, test receipts, protected-state
details, source/database names, private URLs, Customer data, secrets and
infrastructure-hosting references. They do not claim merchant, partner,
aggregator, financial-license or Cameroon/XAF certification status.

Preparation involved documentation edits and existing query-only development
checks, not application changes or regression/provider tests. Read-only receipts:
`.local/payment-phase3b4-before.json`, `.local/payment-phase3b4-after.json`,
`.local/payment-phase3b4-state-before.json`, `.local/payment-phase3b4-state-after.json`,
`.local/payment-phase3b4-protected-comparison.json` and
`.local/payment-phase3b4-catalog.json`.

Protected-state verification is recorded separately in these internal receipts;
it must not be included in the external questionnaire. Nothing has been sent
to MTN, submitted, registered or accepted. No credentials, provider API calls,
configuration, financial/schema/application changes, payment testing,
production access or publishing.

**FINAL STOP.** After creator approval, the creator can send A with B only to
the Cameroon Collections onboarding/commercial contact and ask for referral
to the technical/UAT team. No automatic sending, onboarding or testing.