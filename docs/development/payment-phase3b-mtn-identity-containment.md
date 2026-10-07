# AgendaAlly Phase 3B-2 — durable MTN identity containment

## Current result — approved identity migration and bounded verification

2026-10-03 (America/Chicago). **CONTAINED / VERIFIED in the owned development
SQLite and disposable fake-transport scope. STOP; MTN remains disabled.**
This supersedes the historical Phase 3B-1 schema stop reproduced below. It is
not Cameroon/XAF certification, activation, external settlement/refund
certification, production concurrency certification or full-project green.

### Approval comparison and exact schema

The creator explicitly approved the Phase 3B-1 section 4 existing-table proposal.
No material deviation or additional table was required. Existing
`payment_process.id` remains NOT NULL and gains the unique
`payment_process_identity_unique` index. The six approved nullable fields are:

| Field | Purpose |
| --- | --- |
| `mtn_funding_event_key` | Unique canonical Phase 2 funding-event UUID |
| `mtn_anchor_context_id` | Indexed FK to `payment_collection_contexts.id`, restrictive update/delete |
| `mtn_dispatch_state` | Separate protocol lifecycle; not financial authority |
| `mtn_attempt_version` | Optimistic lifecycle claims |
| `mtn_config_fingerprint` | Original merchant/configuration fingerprint |
| `mtn_dispatch_claimed_at` | Conservative committed dispatch-claim timestamp |

Five SQLite integrity triggers enforce coherent nullable legacy versus typed
bindings, immutable identity/model/payer, no historical backfill, lifecycle
rules and retention of recovery evidence. UUID process identity is represented
as a non-incrementing string in the ORM; SQLite rowid must not replace the
freshly created UUID in memory. The original ID column was not rewritten.

Migration: `2026_10_03_100300_add_mtn_attempt_identity.php`. Applied only to
`.migration-backup/backend/database/development/agendaally.sqlite`, inside an
isolated local transaction without booting provider/queue/scheduler services.
Preflight found zero process rows and zero collisions. Collision testing proves
the exact stop `LEGACY PAYMENT_PROCESS ID COLLISION REQUIRES DECISION`, with no
cleanup or partial schema change. No financial backfill occurs. Rollback refuses
to destroy typed evidence; empty/legacy-only rollback is tested.

The development migration manifest now includes the approved migration, new
reviewed set digest and accepted preceding digest. These are operational
migration metadata, not financial changes. No activation gate was changed.
The migration intentionally refuses unverified non-SQLite engines.

### Old and new sequence

**Old unsafe ordering:** create a request reference in memory → send RequestToPay
→ persist the process and bind canonical funding after the external request.
A timeout/crash between those operations could lose the recovery identity.

**New ordering:**
1. Reject an outer transaction and unsupported target. Authenticate the original
   payer and lock the original Cart/Booking before event creation.
2. Reuse the retained typed attempt, or let the accepted Phase 2 adapter stage
   the canonical funding event. Lock its canonical allocations.
3. Atomically persist one UUID `payment_process.id`, unique event, anchor,
   fingerprint, original payable/payer and complete canonical receipt binding.
4. **Commit** `RESERVED_NOT_DISPATCHED`; no external request exists yet.
5. Recheck existing eligibility and the original configuration. Compare-and-swap
   the dispatch claim to `DISPATCH_OUTCOME_UNKNOWN`; **commit again**.
6. Obtain the token/send exactly one RequestToPay, outside every DB transaction,
   with the retained UUID in `X-Reference-Id` and `externalId`.
7. A 202 means `ACCEPTED_PENDING`, not funding. Non-202, timeout, token failure or
   response-processing interruption retains the conservative original UNKNOWN.
8. Callback hints, authenticated polling and the worker reconcile the same UUID
   through one common native service. Authoritative exact reference, amount,
   currency and original merchant configuration are required.
9. Native canonical completion and protocol terminal state commit together.
   Process identity never becomes the economic authority.

Frozen process/canonical payer ownership also permits legitimate recovery after
successful native Product settlement deletes the live Cart. Replacement native
Transactions, stale/repeated input and customer-provided identity fields cannot
replace the original process, event or contribution.

### Lifecycle and unknown handling

`RESERVED_NOT_DISPATCHED` can resume the **same** reference after eligibility and
original-config checks. A committed UNKNOWN claim can never return to RESERVED
or POST again, including a crash before the socket actually transmits. This
deliberately favors avoiding duplicate collection over automatic resubmission.
Only same-reference authoritative lookup can resolve it.

`ACCEPTED_PENDING` / `PROVIDER_PENDING` carry no MTN paid authority. Unknown,
unrecognized, unavailable or not-found lookup results are not proof that no
collection happened and do not permit a replacement reference. Original
configuration rotation/missing lookup fails closed without current-config
fallback. Callback status is only a hint, never paid evidence.

Authoritative matching success applies the accepted canonical funding/commission/
payable effects once, then `VERIFIED_SUCCESS`. Authoritative matching failure
cancels/fails the original canonical contribution and records
`VERIFIED_FAILURE`; only then may a distinct legitimate canonical funding event
obtain a new attempt. Terminal references remain immutable and retained.
No MTN refund or external payout was implemented.

### Verification receipts

Focused results: **51 tests / 528 assertions**, zero failures/errors, one
unchanged PHP nullability deprecation. Protocol/schema/contention results are recorded in
`.local/payment-phase3b2-focused.txt` and the corresponding JUnit XML.
Selected-regression results are in `.local/payment-phase3b2-regressions.txt`
and corresponding JUnit XML. Synthetic merchant lookup/eligibility are isolated
fixtures; real initiation, token/RequestToPay/status client code and native
canonical completion run under fake HTTP with stray requests prohibited.
No real credentials, real MTN calls, production or marketplace transactions
were used.

| Required failure boundary | Explicit recovery result |
| --- | --- |
| A before persistence | No retained attempt/no POST; clean retry creates one |
| B after persistence before commit | Attempt/context changes roll back; clean retry creates one |
| C committed identity before dispatch | RESERVED survives; same UUID resumes |
| D committed claim/dispatch | Separate read-only connection sees committed UNKNOWN at POST |
| E ambiguous timeout | UNKNOWN retains original UUID; no new POST; later lookup resolves |
| F accepted response then processing failure | UNKNOWN retains original UUID; lookup resolves |
| G pending lookup | Pending, no MTN money; later same-reference success resolves |
| H authoritative success | Product and Booking canonical effects once |
| I authoritative failure | Original failed/canceled contribution; no paid effects |
| J unknown/unrecognized/unavailable | Unresolved original attempt; no new POST or paid effects |
| K before canonical completion | No money; same-reference recovery succeeds |
| L during canonical completion | All canonical/ledger/native effects roll back; retry succeeds |
| M response failure after canonical commit | Terminal paid evidence survives; repeated recovery is nonfinancial |

Replay entry points tested: double-click, repeated API initiation, frontend
retry, worker retry, timeout retry, service recreation, stale/repeated input,
replacement Transaction, callback hint, duplicate reconciliation and delayed
success. These are server-side fake-transport route/service fixtures, not a
claim of real-browser/real-provider integration certification.

For **both Product and Booking**: gross 100, commission 10, MTN platform funding
100 → Vendor payable 90. Reconciliation does not change contributions,
commission or payable on replay. Wallet 40 + MTN 60 is also verified for both:
one accepted native Wallet withdrawal and one MTN POST, total payable 90.
UNKNOWN does not confirm the MTN contribution; a separately confirmed native
Wallet leg remains its own already accepted economic evidence.

**SQLITE CONCURRENCY VERIFIED.** Four controlled file-backed, two-connection
tests cover same-event reservation, simultaneous initiation, simultaneous
authoritative-success reconciliation and canonical finalization. The loser
cannot acquire another identity/effect; after the winner commits, its replay
converges to the retained result. Independent connection inspection at fake
dispatch proves the claim is committed, not merely visible inside the sender.
Canonical finalization contention uses trusted synthetic receipt evidence and
an originally unbound Cart allocation subsequently bound to its native Order.

**PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.** SQLite lock/unique/CAS results
do not establish production-engine isolation, lock behavior or equivalent DDL.

Selected regressions cover Phase 3A separation, Phase 2B/2C accounting/native
integration, MTN verification/replay, Wallet/Cash, Booking paid authority,
Product finality, payout atomicity, Shop authorization and refund authorization.
Legacy MTN tests were corrected to assert the newly required fail-closed
untyped behavior; positive authoritative MTN coverage is supplied by the full
typed native fake-transport fixtures. Accounting-only fixtures pin their three
accepted Phase 2 migrations rather than accidentally running later provider DDL.
The unchanged `OrderHelper::checkShopDelivery` implicit-nullability PHP
deprecation is reported separately; no full-project green is claimed.

### Protected development state and catalogue

Before snapshot matches the accepted Phase 3B-1 stop. After migration and
backend preview restart, **55 protected table counts/fingerprints are identical**.
The original five-column PaymentProcess projection is unchanged (zero rows);
all new typed development MTN rows number **zero**. Financial comparisons include
ledger, allocations/contributions, Transactions, Wallet/history, refunds,
payouts, Orders, Bookings and provider configuration. No existing financial
value was changed. All **12** legacy Orders remain **`unverified`**.

Intentional metadata changes only: six added columns; three indexes; five
integrity triggers; restrictive FK; one migration registration; reviewed
development migration-set/control metadata. SQLite schema entries changed from
408 to 416; foreign-key check returns zero violations. No new table or
historical MTN classification was introduced.

Receipts: `.local/payment-phase3b2-before.json`, `-after.json`,
`-schema-before.json`, `-schema-after.json`, `-proposal-comparison.json`,
`-protection-result.json` and `-catalog.json`. Native public
`GET /api/v1/rest/payments` still returns **Wallet and Cash only**; no MTN.

### Required final answers

1. **Schema:** existing `payment_process`, unique ID, exactly six approved nullable MTN fields, event uniqueness, anchor FK/index and integrity guards.
2. **Exact approved proposal:** yes, the Phase 3B-1 minimum existing-table proposal.
3. **Material deviations:** none; SQLite-specific enforcement is deliberately not a production-engine claim.
4. **ID uniqueness protected:** yes, NOT NULL original ID plus unique index.
5. **Pre-existing collisions:** none; injected collision fixture stops without cleanup.
6. **Canonical provider-attempt identity:** retained UUID `payment_process.id`, also RequestToPay reference and externalId.
7. **Canonical binding:** unique `mtn_funding_event_key` plus restrictive anchor context FK and full original canonical receipt-group validation.
8. **Immutable request reference:** yes, database trigger plus typed repository validation.
9. **Persisted before POST:** yes.
10. **Committed before POST:** yes; identity and conservative dispatch claim are separately committed.
11. **Fake committed proof:** yes, independent read-only SQLite connection sees UNKNOWN at dispatch, with sender transaction level zero.
12. **Pre-dispatch:** `RESERVED_NOT_DISPATCHED`.
13. **Ambiguous dispatch:** `DISPATCH_OUTCOME_UNKNOWN`.
14. **UNKNOWN may create another reference:** no.
15. **UNKNOWN may mark paid:** no.
16. **UNKNOWN may create MTN confirmed contribution/commission/payable:** no; a distinct already accepted Wallet leg is not MTN funding.
17. **UNKNOWN may reconcile original reference:** yes, with original merchant/configuration.
18. **Later authoritative success:** one native canonical funding/commission/payable effect; terminal retained success.
19. **Later authoritative failure:** original attempt/contribution failed/canceled; no paid effects; only a new legitimate canonical event may subsequently obtain a new reference.
20. **Unknown status:** retained unresolved original identity, no replacement and no money.
21. **Frontend retry may create another attempt:** no.
22. **Worker retry may create another attempt:** no; worker only looks up typed unresolved attempts.
23. **Replacement Transaction may create another attempt:** no.
24. **Concurrent initiation may create two attempts:** not in the verified SQLite scope.
25. **Duplicate reconciliation duplicates Customer effects:** no in verified native fixtures.
26. **Duplicate reconciliation duplicates commission:** no.
27. **Duplicate reconciliation duplicates Vendor payable:** no.
28. **Product and Booking:** both covered, including mixed Wallet funding.
29. **Original merchant/config preserved:** yes, frozen canonical source/reference/revision/owner and typed fingerprint; rotation/missing lookup fails closed.
30. **Phase 2 economic identities changed:** no.
31. **Commission/custody/payable semantics changed:** no.
32. **Historical MTN rows classified/rewritten:** no.
33. **Existing financial values changed:** no; all protected fingerprints unchanged.
34. **Legacy Orders:** all 12 remain `unverified`.
35. **Focused results:** 51 tests, 528 assertions, zero failures/errors; one unchanged PHP deprecation.
36. **Selected regressions:** 392 tests, 2,880 assertions, zero failures/errors; one unchanged PHP deprecation.
37. **SQLite contention:** SQLITE CONCURRENCY VERIFIED for the four required controlled cases.
38. **Production-engine concurrency:** PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.
39. **Original durable-identity blocker:** CONTAINED / VERIFIED in this bounded development/fake-transport scope.
40. **MTN disabled:** yes; original activation state unchanged.
41. **Absent from real checkout catalogue:** yes; Wallet/Cash only.
42. **Cameroon/XAF certification incomplete:** yes; public sandbox EUR documentation does not certify Cameroon/XAF, and no EUR substitution/FX was made.
43. **Remaining activation blockers:** exact supported Cameroon/XAF test contract and environment; authorized test merchant/currency/configuration; original credential revision availability; intended-engine DDL/concurrency verification; separate controlled callback/polling and external settlement evidence; explicit creator approval. Refund/external payout readiness remains separately uncertified and unimplemented.
44. **Safest next action:** creator review of this stopped evidence, followed only if separately approved by a non-activating review of the exact MTN Cameroon/XAF test contract.

**STOP.** No real credentials/calls, production, provider activation, refunds,
external payouts, FX, legacy Order classification/deletion, other-provider
implementation, automatic general Phase 3B continuation or publishing.

## Historical accepted Phase 3B-1 schema gate (superseded by approval above)

Date: 2026-10-03 (America/Chicago).

**STOP — MTN PAYMENT IDENTITY SCHEMA CHANGE REQUIRED.**

**Containment NOT implemented or certified.** The authorized trace/schema gate
found that the existing schema does not enforce the required unique immutable
attempt-to-provider-reference binding. No migration or partial application
correction was created. This report supplies the minimum proposal required by
brief sections 5, 17 and 24.

MTN remains **NOT ACTIVATED / CHECKOUT UNAVAILABLE**. The original Phase 3B
identity/recovery blocker remains open. This is neither Cameroon/XAF certification
nor permission to resume that certification.

## 1. Scope, baseline and MTN evidence

The accepted [Phase 3B stop report](payment-phase3b-mtn-cameroon-readiness.md)
and Phase 2/3A accounting/ownership contracts are retained. The current
development baseline was captured before edits and matches the Phase 3B-stop
snapshot exactly.

First-party MTN sources already retrieved in Phase 3B on **2026-10-03**:

- https://momodeveloper.mtn.com/best-practices — generic MoMo Open API guidance:
  server UUIDs, persist transaction state before initiating requests, idempotent
  retry, status lookup after network failure, 202 is not final payment success.
- https://momodeveloper.mtn.com/api-documentation/api-description — generic
  authentication and asynchronous RequestToPay resource identity; GET uses the
  original POST reference; PENDING/SUCCESSFUL/FAILED and duplicate-reference
  responses.
- https://momodeveloper.mtn.com/api-documentation/testing — generic public
  sandbox target `sandbox`, currency **EUR**, distinct sandbox credentials.

These previously verified pages were not refetched. They do not establish an
exact Cameroon/XAF Collection test or production merchant contract. No external
provider operation, credential provisioning, login or real financial query was
performed here.

## 2. Exact existing sequence — traced before edits

Prefix `B` means `.migration-backup/backend/`. Source paths below are unchanged.

| Step | Existing behavior | Identity/effect |
|---|---|---|
| 1 | `PaymentService/BaseService.php::getPayload`, lines 604–640 | Strips caller accounting keys, requires one target, checks actor/eligibility, freezes owner routing |
| 2 | `PaymentAccounting/ProviderContributionAdapter.php::prepare` | Product native Cart allocations or Booking allocations establish accepted server checkout/economic identity |
| 3 | Adapter locks allocations and rejects existing unresolved/finalized selected-method funding | Existing native no-second-charge guard retained |
| 4 | Adapter resolves owner-scoped configuration and requires row/revision | Freezes configuration source/reference/revision, owner/custody, provider, currency/scale and exact amount |
| 5 | Adapter generates canonical funding-event UUID and stages contribution contexts | Event-to-allocation identity is committed under accepted Phase 2 contract |
| 6 | Adapter calls `AllocationWriter::pending($selected)` before the MTN call | Pending native accounting, but provider reference is still NULL |
| 7 | Booking adapter creates progress Transactions; `beforeBooking` also retains native progress behavior | Transaction is a lifecycle/link record, not proof of payment |
| 8 | `beforeCart` / `beforeBooking` calculate native amount/currency and permitted Wallet contribution | Persisted native target/quote remains authoritative |
| 9 | `PaymentService/MtnService.php::processTransaction`, lines 45–57 | Obtains payload, resolves credentials, rejects collector currency disagreement, obtains token |
| 10 | MTN service line 59 | Generates MTN UUID R1 **after** native funding preparation; R1 exists only in memory |
| 11 | MTN service lines 63–75 | Sends RequestToPay with R1 as `X-Reference-Id` and `externalId` |
| 12 | MTN service lines 77–80 | Non-202 throws; transport exception also leaves before subsequent process creation |
| 13 | MTN service lines 82–94 | **Only after 202** creates PaymentProcess with R1, payload/context IDs and configuration fingerprint |
| 14 | `Dashboard/Payment/MtnController.php` callback/status methods | Must find stored PaymentProcess; checks merchant fingerprint, authenticated MTN GET, reference, amount/currency and known status |
| 15 | `Console/Commands/ReconcilePendingMtnPayments.php` | Enumerates stored unresolved MTN processes; cannot discover an R1 never persisted |
| 16 | `BaseService::afterHook` / `ProviderContributionAdapter::complete` | Original verified-intent boundary links native Product Orders/Transactions or Booking Transactions and confirms/finalizes accepted canonical accounting |
| 17 | Accepted repeated settlement/replacement-Transaction handling | Canonical event/contribution/fee guards, not creation of a new provider request identity |

### Current identity chain

`checkout_key` identifies the accepted native economic checkout. Each canonical
allocation retains origin/payable and Shop. `funding_event_key` groups the
original electronic contribution contexts. Those contexts freeze money,
custody, merchant configuration source/row/revision and provider.

PaymentProcess stores model type/id and, in its mutable JSON data, context IDs,
funding event and original payload. Its `id`/`mtn_reference_id` identify the MTN
request. `AllocationWriter::pending` can bind a provider reference to an existing
context and rejects a conflicting non-NULL reference. Verification subsequently
uses the retained process plus canonical contexts. Transactions are linked
financial/lifecycle evidence, not the provider attempt's immutable identity.
Replacing a Transaction must not replace the canonical funding event.

**The accepted per-context guard is real containment and is not disregarded.**
However, the current initiation calls `pending` with no provider reference, and
there is no typed unique attempt/process-to-funding-event reservation. The
provider UUID/process is created only after external submission.

### Existing timeout/retry/crash behavior

If the request may have reached MTN but its response/save is lost, native funding
can remain pending without a durable discoverable MTN UUID. Native preparation
rejects a second unresolved selected-method charge. The current frontend path
does not provide a certified same-attempt lookup/recovery alternative. The worker
does not initiate requests; it can reconcile only known stored references.

No duplicate Customer debit was demonstrated. This bounded review did not
exercise real or fake transport, callbacks, paid settlement or native failure
injection. Neither current accounting guards nor successful stored-process
settlement certify the missing submission/recovery contract.

## 3. Existing-schema assessment and stop gate

Both migration source and the actual development SQLite metadata were inspected.

### A. PaymentProcess has no unique physical identity

`B/database/migrations/2023_02_22_110323_create_payment_process_table.php`
defines `id` using `$table->string('id')` without primary/unique declaration.
Actual `PRAGMA table_info("payment_process")` shows:

`id: varchar, NOT NULL, pk=0`.

Actual `PRAGMA index_list` contains only the **nonunique**
`payment_process_model_type_model_id_index`. No unique ID index or
PaymentProcess trigger exists.

`B/app/Models/PaymentProcess.php` assumes Eloquent's ordinary `id` key but has
`guarded=[]` and mutable JSON data; it supplies no immutable MTN attempt binding.
ORM key assumptions are not physical uniqueness constraints. Multiple rows with
the same UUID are accepted by this table's actual DDL.

### B. Canonical context constraints do not reserve an MTN attempt

`B/database/migrations/2026_10_03_100100_create_payment_collection_contexts.php`
and physical metadata show:

- Canonical context ID is a primary key.
- Existing unique keys cover allocation/funding key, funding event/allocation,
  confirmed slot and final receipt claim.
- `payment_process_reference` and `provider_payment_reference` are nullable,
  with nonunique lookup indexes; no FK/unique attempt association is supplied.
- `funding_event_key` is not itself a unique process/attempt key; the same event
  legitimately groups canonical allocation members.
- Existing states are accounting states:
  `committed,pending,confirmed,rejected,canceled,review_required`.
  They are not an authoritative dispatch reservation/lifecycle.
- Actual context triggers enforce integer precision, not provider-attempt
  immutability or dispatch ownership.
- `AllocationWriter::pending`, lines 143–166, has guarded versioned per-context
  reference assignment. It is not a unique MTN attempt record or a durable
  dispatch claim that frontend/worker recovery can independently locate.

These are **missing attempt-level invariants**, not grounds to redesign or
discredit the accepted Phase 2 economic identity.

### C. Disposable schema probe

The actual PaymentProcess CREATE TABLE DDL was copied into a **new in-memory
SQLite database**, with synthetic rows only and foreign keys enabled. No
development row was copied; no Laravel application or transport was invoked.
Three observations were asserted:

1. Two synthetic rows with an identical UUID are accepted.
2. Different UUIDs carrying the same synthetic JSON binding are accepted.
3. A synthetic row's UUID can be updated.

Receipt: `.local/payment-phase3b1-schema-probe.json`.

This is **3 schema checks**, not the requested 26-case application verification
matrix, a financial exploit, or concurrency certification. It demonstrates
absence of the physical identity constraints; it does not bypass the native
pending guard or demonstrate a second MTN charge.

### D. Why no application-only ordering patch was shipped

Moving PaymentProcess creation before POST would narrow the original persistence
window, but would not alone establish unique immutable canonical attempt
reservation, dispatch ownership and retry/recovery binding.

An application helper/model hook could serialize particular calls and guard
updates, but it would leave the durable attempt binding dependent on mutable
JSON and a generic process table without a unique physical key. Existing
canonical per-context guarded reference assignment does not provide the missing
attempt-level uniqueness/lifecycle. Treating an accounting `pending` state as
“sent” or “definitely unsent” would also give it an unapproved financial meaning.

Under the brief's required schema gate, this review does not certify that weaker
approach as the complete invariant. **No partial fix, migration, new state,
canonical writer change or identity shortcut was applied.**

The schema defect is not reported as another immediately exploitable financial
P0: no external actor route to duplicate records/collection was established,
real MTN activation is blocked, and no financial exploit was exercised.

## 4. Minimum schema proposal — separate approval required

The proposal below adds provider-attempt identity without changing Phase 2
checkout, contribution, commission, custody or Vendor-liability semantics.
It is a **design proposal, not created DDL or an approved migration**.

### Proposed existing-table extension

| Element | Proposed minimum invariant |
|---|---|
| Existing `payment_process.id` | Add a NOT NULL unique identity constraint; it is the MTN reference, not a second generated reference |
| Nullable `mtn_funding_event_key` | Typed UUID with UNIQUE constraint for new controlled MTN attempts; same canonical event cannot reserve R1 and R2 |
| Nullable `mtn_anchor_context_id` | FK to existing canonical context ID, restrictive deletion/update; binds original allocation/payable/Shop/configuration/money through accepted context |
| Nullable `mtn_dispatch_state` | Dedicated checked non-financial lifecycle; NULL on untouched legacy/non-MTN rows |
| Nullable `mtn_attempt_version` | Nonnegative compare-and-swap version for reservation/dispatch/outcome contention |
| Nullable `mtn_config_fingerprint` | Frozen MTN credential/routing digest, not credentials; immutable for the attempt |
| Nullable `mtn_dispatch_claimed_at` | Durable marker committed before possible transmission; no success/paid interpretation |
| MTN identity update guard | Once assigned, forbid changing ID, funding event, anchor context or configuration fingerprint for that attempt; permit only guarded lifecycle/version updates |

For new MTN records, require coherent non-NULL MTN binding/state/version/
fingerprint. The anchor's canonical funding event must match the typed event;
the entire accepted event membership, provider, currency, owner/configuration
revision and payable must be validated before reservation and reconciliation.
No new commission/custody/economic key is introduced.

The ID uniqueness constraint touches a shared table and therefore also requires
explicit schema approval; it is not silently added as MTN-only code work.
If a target database has duplicate existing IDs, **STOP the migration preflight**.
Do not deduplicate, delete, rename UUIDs or rewrite historical JSON/Transactions.
The current development process table has zero rows; this does not establish
production preflight results, and production access remains prohibited.

An alternative MTN-only attempt table can isolate the new authority, but its
relationship to the nonunique legacy process projection must be explicitly
designed and fail-closed. Do not assume adding that table alone makes
`PaymentProcess::find` uniquely authoritative. The smallest preferred proposal
above remains subject to the creator's schema approval and detailed validation.

### Intended lifecycle and corrected sequence — NOT implemented

1. Start a local transaction. Validate original actor/native quote, lock the
   existing canonical attempt/allocation and either find its reserved MTN
   attempt or establish its accepted native funding event.
2. Generate one server UUID. Persist unique typed process/event/context binding
   with `RESERVED_NOT_DISPATCHED`, version and frozen fingerprint. Commit.
3. If persistence/commit fails, send **no** RequestToPay. Do not do provider I/O
   inside that transaction or rely on nested transactions as proof of commit.
4. One dispatcher wins a versioned claim. Persist `DISPATCH_OUTCOME_UNKNOWN`
   and its claim marker, then **commit before** RequestToPay. This conservative
   marker covers a crash immediately before or after transmission.
5. Dispatch exactly the stored UUID. A returned 202 can become
   `ACCEPTED_PENDING`; it never means funded. Known rejection classification
   must follow the verified MTN contract, not an arbitrary transport assumption.
6. On timeout/disconnect/response-handling crash, preserve UNKNOWN and R1.
   UI/worker retries resolve/reconcile R1, not dispatch R2.
7. Authenticated original-context status lookup returns PENDING, verified
   SUCCESSFUL, verified FAILED, or remains unresolved. Unavailable lookup,
   unknown status or “not found” after ambiguous submission is not automatic
   definitive no-collection proof.
8. Only accepted authoritative success invokes existing canonical settlement.
   Integrate final attempt status with its existing atomic settlement boundary,
   preserving rollback/replay. Success/fee/payable are applied once.
9. A genuinely new reference requires a genuinely new attempt permitted by the
   accepted authoritative failure/checkout rules. Never manufacture a new
   checkout key to escape UNKNOWN, or reopen finalized success.

`RESERVED_NOT_DISPATCHED`, `DISPATCH_OUTCOME_UNKNOWN`, `ACCEPTED_PENDING`,
`PROVIDER_PENDING`, `VERIFIED_SUCCESS`, `VERIFIED_FAILURE` and `REVIEW_REQUIRED`
are proposed provider lifecycle states only. No schema or implementation for
them exists yet, and no existing accounting state was renamed.

### Crash/retry semantics proposed

- **Failure before identity persistence/commit:** no external call; native
  local changes roll back together; no reserved provider identity is advertised.
- **Committed RESERVED, before claim:** definitely not dispatched only if all
  dispatch paths enforce the claim gate; resume the same reserved UUID.
- **After dispatch claim, before actual POST:** conservatively UNKNOWN,
  reconcile R1; absence of a response does not authorize resending.
- **After possible POST/202, before local outcome save:** UNKNOWN/R1 survives;
  authenticated polling can recover eventual success/failure.
- **During unavailable reconciliation:** retain unresolved state and R1.
- **Failure during canonical completion:** accepted financial transaction
  rollback and same-identity retry; do not mint a new request.
- **After canonical completion, before response:** replay reads terminal
  canonical evidence and returns the retained attempt without another effect.
- **Concurrent initiation:** unique event binding plus locks/version claim;
  loser reuses/reconciles winner or fails safely.
- **Stale model/replacement Transaction:** resolve typed event/context binding,
  not mutable request fields or Transaction ID as a new financial identity.

### Historical migration treatment and rollback

Existing MTN/non-MTN process rows and Transactions must be left unchanged. New
nullable metadata must not infer historic dispatch outcome, references or
configuration revisions. Legacy rows without the invariant remain unverified/
fail-closed. Preflight rejects duplicate shared IDs rather than rewriting them.
No production preflight/migration is authorized.

Before new attempts exist, an approved schema rollback could remove only newly
introduced unused columns/constraints. Once durable MTN attempts may have been
dispatched, destructive rollback must be refused: retain their IDs/state for
reconciliation, keep activation off, and use a separately approved forward
correction. Never drop recovery evidence or call the provider from migration.

## 5. Product, Booking, ownership and accounting

The proposal uses the same event/process invariant for both Product and Booking.
Their existing native quote/binding differences remain in accepted factories and
settlement; there is no proposed separate economic identity.

Platform-managed merchant identity/configuration source/row/revision comes from
the existing canonical context. A changed current configuration must not
reinterpret the attempt; absent authorized historical credentials/revision
means fail-closed review, not fallback to another merchant. Fingerprint remains
an authenticated credential-context binding, not external legal-payee proof.
Vendor-direct remains non-activated/non-certified and was not completed.

A reserved/unknown/accepted/pending **MTN attempt** is not paid and must not
confirm an MTN contribution, commission or Vendor payable. Accepted Wallet
contribution semantics remain separate; do not alter mixed-funding accounting.
Verified success alone uses the existing original-context canonical path.
Replacement Transactions do not redefine the funding event.

No Product/Booking initiation, settlement, Wallet/Cash, fee writer, payout,
callback or reconciliation code was changed by this stopped review.

## 6. Verification, receipts and protected state

New application focused tests/assertions: **0/0**. New selected regressions:
**0/0**. The 26-case focused matrix, nine failure-injection points and required
two-connection initiation/reconciliation/finalization contention are **deferred
by the mandatory schema stop**, not passed.

Disposable schema checks: **3**, as described in section 3. They are not payment
tests or contention. **SQLITE CONCURRENCY NOT CERTIFIED for this Phase 3B-1
flow. PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.**

No whole-project-green claim. Accepted prior Phase 2/3A suites remain historical
evidence, not new identity-containment certification. The preexisting broad
hardening workflow remains failed; no unrelated repairs were attempted.

Current read-only development checks:

- Phase 3B-1 before snapshot equals the accepted Phase 3B-stop after snapshot.
- **55/55 protected tables** have unchanged counts and row SHA-256 fingerprints.
- **408/408 schema entries** unchanged before/after.
- PaymentProcess rows remain **0**; Transactions remain **15**, unchanged hashes.
- Canonical allocation/contribution contexts remain **0/0**.
- Merchant/platform/global configuration rows unchanged.
- All **12** legacy Product Orders remain `fulfillment_financial_state=unverified`.
- Foreign-key errors **0**.
- Running GET `/api/v1/rest/payments` returns **wallet,cash** only.

The accepted serialization/order/hash methodology and full table inventory
remain Phase 3A section 11; complete snapshots, not totals alone, were compared.
No development financial mutations, credential/environment changes, provider
calls, production access, browser financial journey or workflow restart occurred.
Application code was not changed, so no new native UI certification is claimed.

Receipts:

- `.local/payment-phase3b1-before.json`
- `.local/payment-phase3b1-after.json`
- `.local/payment-phase3b1-schema-before.json`
- `.local/payment-phase3b1-schema-after.json`
- `.local/payment-phase3b1-schema-probe.json`
- `.local/payment-phase3b1-protected-comparison.json`

## 7. Exact changed-file inventory

Application, model, service, migration, test-suite, dependency and configuration
files changed: **NONE**.

Report changes:

1. `docs/development/payment-phase3b-mtn-identity-containment.md` — this stop/
   schema proposal, trace and 35 decisions.
2. `docs/development/payment-phase3b-mtn-cameroon-readiness.md` — follow-on
   status; original identity blocker remains open.
3. `docs/development/payment-system-full-audit.md` — latest schema-gated result.

Approval-boundary memory and local verification receipts also updated. An
existing unrelated generated mockup file change observed at the start was not
edited, reverted or incorporated into this payment review.

## 8. Required 35 final answers

1. **Reference now persisted before RequestToPay?** No correction implemented;
   original ordering defect remains.
2. **Committed before external dispatch?** Not guaranteed by current code;
   required corrected sequence is proposed, not implemented.
3. **Reference immutable for attempt?** No complete attempt invariant certified;
   physical process ID lacks uniqueness/immutability; context guard is narrower.
4. **Timeout can generate new reference for same attempt?** No safe full
   same-attempt recovery contract is certified. Native pending guard blocks
   re-initiation; duplicate debit/reference from timeout was not demonstrated.
5. **UNKNOWN represented?** No dedicated existing dispatch lifecycle; proposed
   typed UNKNOWN/claim/version above, not created.
6. **UNKNOWN paid?** No authority to mark paid; no such new state implemented.
7. **UNKNOWN funded accounting?** Must not confirm MTN funding; no confirmed
   accounting was created in this review.
8. **Reconcile original reference?** Current stored-process path can; missing
   pre-persistence reference cannot be recovered through normal paths.
9. **Later success exactly once?** Accepted stored-process verifier/canonical
   replay exists; new submission/crash recovery not implemented or tested.
10. **Later failure closes safely?** Accepted verified-failure path exists for
    stored processes; new UNKNOWN lifecycle/failure matrix deferred.
11. **Crash before dispatch?** Current UUID may exist only in memory; no
    committed attempt reservation guaranteed. Proposed reserved/claim semantics
    distinguish safe unsent from conservative unknown.
12. **Crash after possible dispatch?** Original lost-reference gap remains;
    proposed committed UNKNOWN/R1 would recover it but is not implemented.
13. **Frontend retry another identity?** Full reuse/recovery invariant not
    contained; current native unresolved guard is retained; no duplicate proven.
14. **Worker retry another identity?** Existing reconciliation worker does not
    initiate requests; it cannot find a UUID never stored. New flow unverified.
15. **Replacement Transaction another identity?** Accepted canonical identity
    is independent of replacement Transaction; new attempt-binding tests deferred.
16. **Concurrent initiation two identities?** Required at-most-one attempt
    guarantee not certified; typed unique binding/schema approval required.
17. **Duplicate contribution?** Accepted canonical replay guards retained;
    new containment/regression tests deferred; none created here.
18. **Duplicate commission?** Same accepted guard/verification limitation.
19. **Duplicate payable?** Same accepted guard/verification limitation.
20. **Product/Booking covered?** Both traced and included in proposal; neither
    corrected or newly tested in this schema-stopped phase.
21. **Merchant/configuration ownership preserved?** Yes, unchanged accepted
    architecture; proposal binds existing frozen context, not current fallback.
22. **Phase 2 accounting identities changed?** No.
23. **Schema changes required?** **MTN PAYMENT IDENTITY SCHEMA CHANGE REQUIRED**;
    proposed, none created or migrated.
24. **Historical MTN records rewritten?** No.
25. **Existing financial records changed?** No; all 55 protected hashes match.
26. **12 legacy Product Orders unverified?** Confirmed directly.
27. **Focused tests/assertions?** Application 0/0 due schema STOP; 3 separate
    disposable schema checks, not the focused application matrix.
28. **Selected regressions/assertions?** New 0/0, deferred; no full-green claim.
29. **SQLite contention?** Not run for this flow; not certified.
30. **Production concurrency?** PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.
31. **Original identity blocker contained?** **NO.** Safe schema/implementation/
    tests remain gated.
32. **Certification blockers remain?** Durable attempt schema/implementation,
    required synthetic crash/retry/contention/regressions, exact Cameroon/XAF
    environment/product/merchant evidence, ownership/rotation and operational
    callback/refund/settlement/payout requirements in accepted Phase 3B report.
33. **Sandbox EUR still prevents XAF sandbox claim?** Yes. Generic EUR sandbox
    is not Cameroon/XAF certification; no EUR substitution or FX was added.
34. **MTN activated?** No; activation gate unchanged.
35. **Real Customer payment visibility?** No; running catalog only Wallet/Cash.

## 9. Final boundary

**STOP at the authorized schema gate.** Await explicit approval of the minimum
identity schema proposal before creating any migration or correction. Do not
automatically resume Phase 3B certification, activate MTN/Vendor-direct, configure
credentials, call MTN, implement refunds/payouts/FX, access production, work on
unrelated providers, rewrite legacy records or publish.