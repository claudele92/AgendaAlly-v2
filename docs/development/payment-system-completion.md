# AgendaAlly payment completion — implementation tracker

## Current MVP authority — 2026-10-04

MySQL 8/InnoDB is selected. PostgreSQL proof remains preserved and further work
is deferred; Strategy G is not authorized. The permanent owned-RR POST entry,
equivalent source MTN DDL and completion FK-index rollback correction are verified.
Fresh MySQL receipts: original **13/51**, expanded **20 groups**, Wallet
**28 groups**, ordered **224** source migrations and **8 down/8 reapply** steps
pass. Focused source MTN/owned-entry and stale/replacement fulfillment receipts
also pass. These certify the tested application financial boundary, not production.

[AgendaAlly MVP readiness](agendaally-mvp-readiness.md) is authoritative for the
current payment classification, whole-product assessment, one remaining-work
matrix and next-phase proposal. Its booking correctness and production/restore/
selected-role acceptance gates remain open. No provider activation or deployment.
All **59/465** owned fingerprints/schema objects match; **12 Orders unverified**.

The records below describe earlier milestones. Their “no engine selected” and
unported-MTN status are historical, superseded by the current authority above.

2026-10-03. **Approved common boundary implemented; application milestone delivered
with synthetic verification. NOT production-certified. No electronic activation.**

The earlier identity-approval stop is superseded by the creator's explicit approval
of the five durable identity families. Accepted economics, original custody,
frozen currency/scale, Cash/Wallet finality and typed MTN recovery remain authoritative.
This is the single current implementation tracker, not a new architecture audit.

### Current Wallet containment and native verification — 2026-10-04

The separately authorized Wallet correction supersedes the Wallet stop below.
Current PK locking, persisted owner/UUID/currency validation and a conditional
sufficient-funds decrement now govern the existing native debit paths. Transfers
lock both Wallets in ID order and retain bounded whole-transaction retries.
The accepted contribution-confirmation correction is unchanged.

**MySQL RR and PostgreSQL RC: 28/28 Wallet groups PASS independently.**
Native Product/Booking funding, cross-operation contention, all nine rollback
boundaries, terminal replay, concurrent credits and real Wallet deadlock retry pass.
Both engines also pass native fulfillment contention/replay, confirmation,
reservation conservation, timeout/deadlock and exercised negative/lifecycle guards.
PostgreSQL's original-thirteen semantic mirror passes **13/51**.
Interrupted MySQL checks were resumed without repeating completed Wallet groups;
split receipts are identified explicitly, not reported as consolidated JUnit runs.

**Full MySQL: NOT CERTIFIED. Full PostgreSQL: NOT CERTIFIED.**
The unchanged native MTN migration rejects both drivers pending separately reviewed
DDL. PostgreSQL still needs permanent application/SQL porting. Financial passes do
not certify whole-application bootstrap; no new independent financial P0 was found.
No winning engine was selected and the conditional post-certification expansion
was not triggered. This is not “application-side payment system fully complete.”

Final protected comparison: **59 table fingerprints and 465 schema objects MATCH;
12/12 legacy Orders remain unverified.** The original failed proof database/dumps
remain preserved. No owned migration, real credential/provider call/live money,
production access, activation or publishing occurred. Native Laravel restarts
cleanly; its public catalogue still returns Wallet/Cash only.

See [Wallet report, evidence register and all 56 answers](wallet-p0-engine-certification.md).
The earlier dated stop reports below are historical, not current engine outcomes.

### Historical narrow confirmation correction — 2026-10-03

The subsequently authorized correction changes only native
`PaymentAccounting/AllocationWriter.php` plus its focused replay test. Contexts
are reloaded by known primary key **after sorted parent locking**, inside the
transaction. Same-receipt replay retains receipt/economic/binding identity and
requires exact existing canonical base effects; different evidence fails closed.
There is no migration, provider configuration, Wallet/fulfillment/refund/payout
redesign, Strategy G implementation or operational activation.

SQLite focused **11/72** and corresponding unchanged contention **8/21 PASS**.
Real MySQL 8.0.42/RR and PostgreSQL 16.15/RC both pass failed-case-first
same-receipt convergence and different-receipt rejection, then freshly pass all
13 expanded reservation groups. Unchanged original MySQL thirteen **13/51 PASS**.
Previously unrun native guard/JSON/index assessments and PG serialization
diagnostic execute; this does not certify full bootstrap/guard parity.

**Both engine proofs STOP at a new independent Wallet failure:** two concurrent
7000-unit sends from 10000 both commit/return 200; balances change from
[10000,3000,8000] to **[-4000,17000,8000]**, with four paid histories/transactions.
All workers exit outside transactions. Wallet source is unchanged and not
remediated. Native fulfillment and remaining full-parity/PG-mirror gates are
**NOT RUN**, not passed. Both engines are **NOT CERTIFIED**; recommendation
remains **C, insufficient evidence**, with no production winner.

All 59 protected table fingerprints, 465 schema objects and the 12 legacy
`unverified` Orders exactly match baseline/final. Disposable databases, logical
dumps, native logs and worker evidence are retained; failing state is not cleaned.
Proof executables are archived/retired and no further financial work is started.
The exact required 24-field report, preservation details and remaining separate
approval boundaries are in [payment-engine-proof-comparison.md](payment-engine-proof-comparison.md).

### Phase 3C engine status — 2026-10-03

Actual disposable **MySQL 8.0.42 / InnoDB / REPEATABLE-READ** first exposed
error 3818 for the receipt self-anchor CHECK. The explicitly approved minimum
MySQL database-trigger equivalent is now implemented: **MySQL 9 tests / 34
assertions**, **SQLite 8 / 23**, including fresh/migration/upgrade/rollback behavior.
No migration file/manifest or owned development schema was changed.
The separately approved cross-engine semantic JSON comparison for
`native_components`/`effect_data` passes **MySQL 3/35 + SQLite 3/35**, with persisted
JSON unchanged and strict type/member/order mismatches rejected. Selected existing
SQLite regressions pass **50/319**; financial identity/semantics are unchanged.
The separately approved MySQL identifier-only UNIQUE correction preserves every
column/constraint/identity and SQLite DDL. Fresh bootstrap, original migration
down/up, duplicate rejection and legitimate inserts pass **MySQL 2/28 + SQLite 2/28**.
The original thirteen restarted unchanged from case one: **13 passes / 51 assertions**.
The first expanded established-snapshot test then fails: two overlapping native
refund reservations commit **7000+7000 against original 10000 funding**, despite
current allocation locking, because ordinary monetary reads retain an older RR
view. **1 test / 7 assertions / 1 cap failure**; further expanded tests stopped.
No reservation logic change remains installed. No provider call/refund effect or authenticated
API exploit was demonstrated; this is a reproducible native service invariant failure.
The separately approved current-read candidate passed the exact stale refund cap
test (1/10) but failed independent-allocation progress (1/5) because shared gap/
supremum locks blocked another allocation. Candidate withdrawn; original defect
remains. No alternate isolation/schema/locking strategy or other path remediation
was attempted. Required remaining tests stopped at that gate.
Full Phase 3C MySQL financial concurrency is **NOT CERTIFIED**.
All **59 protected tables** (55 prior + four evidence tables), **465
schema objects**, four empty evidence tables and twelve unverified Orders still match.
The disposable DB/account/server and temporary opt-in flags were cleaned up.
See [Phase 3C report, matrix, 44 answers and next bounded proposal](payment-phase3c-mysql-concurrency.md).

## Delivered application boundary

- Immutable encrypted global merchant revisions, a nullable active-profile pointer,
  one global profile per payment method, and original revision resolution after rotation.
  No historical credential/configuration backfill or invented revisions.
- Non-MTN collection attempts and native `payment_process` records committed before
  transport; atomic dispatch claim, separate provider references/payment identities,
  UNKNOWN retention, no blind redispatch and authenticated original-reference recovery.
  Native Product/Booking initiation, verification, canonical settlement and replay
  integration for Paystack, Flutterwave, Stripe and PayPal.
- Exact-unit refund operation identity, original confirmed contribution/revision/payment
  linkage, capped outstanding reservations, partial/cumulative append-once reversals,
  undisbursed cancellation and ambiguous-outcome retention. Original-evidence
  Paystack/Stripe/PayPal adapters; unsupported contracts fail closed before dispatch.
  Paystack acceptance/queued status is not refund success; only `processed` is.
  PayPal `COMPLETED` is parsed from the actual refund response and original capture.
- Commission-receivable reservation and authorized retained **cash receipt** workflow:
  unique reference, document digest, provenance/text, UTC receipt timestamp and recorded
  actor evidence. One append-only effect per operation. No approving-Admin Wallet debit,
  invented external receipt or success toggle.
- Vendor-payable request/reservation/cancellation against unreserved liability.
  Reservations do not pay the Vendor or create `vendor_settlement` effects.
  No external beneficiary/transfer rail or legitimate external platform-funding
  contract has been certified, so external payout success cannot be submitted.
- Native Admin/Seller transaction financial controls, exact integer-string balances,
  operation/attempt histories, server permission checks, refresh and controlled errors.
  Admin frozen-country-scoped pending collection recovery also covers Carts that have
  no completed Order transaction. Seller processing remains restricted to actual grants;
  Vendor visibility/requesting is not permission to refund or record platform receipts.
- Blank-preserve secret editing, public field allowlists and masked stored-presence
  indicators remain. PayPal merchant identity is configurable. Capability,
  configuration, readiness and activation remain separate.

## Final implementation matrix

“Functional” below means bounded application/native synthetic evidence, not
merchant eligibility, live money, external settlement or production certification.

| Area | Classification | Delivered / remaining boundary |
|---|---|---|
| Cash | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Native Product/Booking and authorized offline finality; no platform Vendor principal invented |
| Wallet | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Native terminal debit, conservation, failure rollback and replay; traced currency/custody retained |
| MTN | EXTERNAL DEPENDENCY | Accepted typed attempts/UNKNOWN recovery and native synthetic flows unchanged; Cameroon merchant/environment/contract/UAT unresolved |
| Orange | EXTERNAL DEPENDENCY | Existing country/Shop configuration; authoritative verification/refund/market contract missing, so no claimed working collection |
| Flutterwave | PARTIAL | Global revision, durable initiation, native Product/Booking verification/replay/recovery functional; refund contract and market/account UAT unresolved |
| Paystack | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Original-revision native collection and refund workflow, processed-status correction, replay/recovery; account/capability/UAT outstanding |
| Stripe | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Original-revision native collection/refunds in declared narrow markets; no Cameroon/XAF or Connect/Vendor-account certification |
| PayPal | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Original-revision native collection/capture/refunds and corrected response parsing; supported merchant/currency/account/UAT outstanding |
| Refunds | PARTIAL | Generic reservation/orchestration and three adapters functional; MTN/Orange/Flutterwave contract-dependent adapters not enabled |
| Commission receivables | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Actual retained cash-receipt workflow and once-only canonical effects; no automatic bank/provider collection or Admin funding |
| Vendor payouts | PARTIAL | Request/reservation/cancellation functional; actual external rail/funding/beneficiary proof NOT IMPLEMENTED |
| Reconciliation | PARTIAL | Original collection/refund identity recovery and internal histories functional; bank settlement/report ingestion and external payout reconciliation absent |
| Platform-managed collection | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Four declared global-provider application flows and original-revision recovery; merchant eligibility, engine bootstrap and controlled UAT remain |
| Vendor-direct | PARTIAL | Shop-scoped configuration and canonical direct economics; only separately proven account/country/currency models may collect; no general Vendor-direct activation |
| Admin operations | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Scoped canonical panel, pending recovery, refund/receipt/reservation controls; no live provider or bank operational certification |
| Vendor operations | FUNCTIONAL BUT NOT PRODUCTION-CERTIFIED | Shop-scoped finance/configuration and granted controls; another Shop's data and platform-only processing remain denied |
| Production-engine concurrency | PARTIAL | Both native Wallet matrices and exercised financial gates pass; neither full application engine is certified because native bootstrap/porting remains incomplete |

## Verification, migration and protected state

- Selected milestone: **132 cases**, including **13 explicit disposable-MySQL skips**.
  All **119 executable selected cases** were verified across the milestone and focused
  corrections. The initial milestone's only failure was an incorrect Stripe fixture
  assertion expecting Bearer rather than the adapter's valid Basic authentication;
  corrected focused check passed. One PHP deprecation remains, not a test failure.
  Receipts: `.local/payment-completion-final-tests.txt`,
  `.local/payment-completion-final-junit.xml`,
  `.local/payment-completion-final-corrections-tests.txt` and corresponding JUnit.
  Overlapping focused checks are not added to distinct-case totals.
- Coverage includes native Cash/Wallet/MTN regression, provider callback foundations,
  secret/configuration contracts, Shop/country projection scope, new operation authority,
  original credentials after rotation, three refund adapter outcomes and receipt replay.
  Eight native global-provider journeys cover all four providers × Product/Booking.
- Eight new two-connection SQLite cases: duplicate attempt creation, dispatch claim,
  provider finalization, refund reservation, refund finalization, receivable settlement,
  payable reservation and **blocked** external payout finalization. The seven writable
  paths prove contention loss and post-commit replay/cap behavior. The eighth proves
  that neither connection can manufacture external payout success. Existing five core
  contention paths also pass; their synthetic settlement groups are not real payouts.
- Native Admin browser verification uses **intercepted synthetic frontend contracts**,
  not real sign-in/provider certification: pending recovery/error retention, exact
  scoped finance, refund reservation/cancel, disabled dispatch, payout reserve/cancel,
  receipt validation and refreshed synthetic success. No actual financial writes.
  Narrow-screen history and pending-allocation summary corrections passed focused
  390×844 rechecks with no horizontal overflow and readable retained identity/money.
  Browser receipt: `.local/payment-completion-ui-verification.md`.
- Pre-migration protected fingerprint baseline, duplicate/orphan-profile checks,
  retained legacy status, migration-order inspection and disposable rollback/reapply
  tests preceded the exact approved development migration.
- Applied only `2026_10_03_100400_add_payment_completion_identity` to the owned development
  SQLite database. Intentional changes: four new retained tables
  (`payment_merchant_revisions`, `electronic_collection_attempts`,
  `payment_financial_operations`, `payment_receipt_evidence`), nullable profile revision
  linkage, profile uniqueness, foreign keys, indexes and retention/state/binding guards.
  Reviewed development migration manifest/bookkeeping updated. No MTN schema change.
- **All 55 pre-existing protected tables have identical count/full-row fingerprints.**
  All four new tables are empty. All **12 legacy Orders remain `unverified`**.
  Schema changes are reported separately, not claimed schema-identical.
  Evidence: `.local/payment-completion-identity-before.json`,
  `.local/payment-completion-identity-after.json`,
  `.local/payment-completion-identity-schema-before.json`,
  `.local/payment-completion-identity-schema-after.json` and
  `.local/payment-completion-identity-protected-comparison.json`.
- Public native catalogue remains **Wallet/Cash only**. No real credentials, provider
  transactional/account API calls, payments/refunds/payouts, provider contact/onboarding,
  production access, historical classification/deletion or publishing.
  Public provider documentation was retrieved read-only for adapter contracts.
- The previously failed broad `original-hardening` workflow was not rerun or claimed
  passing. Its older fixture/contract failures are not erased by selected evidence.
  No whole-repository, load or production-concurrency certification is asserted.

## Engine certification and remaining dependencies

MySQL **8.0.16+** DDL/retention and opt-in loopback-only initially-empty disposable
harnesses are prepared: five accepted core paths plus eight completion-identity paths.
At the earlier milestone no local MySQL instance was available, so thirteen cases
explicitly skipped. Phase 3C subsequently provisioned a disposable server and
first encountered receipt guard, JSON compatibility and index naming blockers,
corrected only after separate approvals. All thirteen prepared paths now pass.
The stale-snapshot refund reservation invariant failure above blocks certification
and the remaining expanded coverage.
The actual intended hosted engine/version was not accessed.
**PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.**

Use only a separately provisioned empty `agendaally_payment_disposable_<suffix>` database,
dedicated least-privileged test account and approved secret tooling. Never production
connection variables/accounts. Dedicated variables: `PAYMENT_TEST_MYSQL_ENABLE=1`,
`PAYMENT_TEST_MYSQL_DATABASE`, `PAYMENT_TEST_MYSQL_PORT`, `PAYMENT_TEST_MYSQL_USER`,
`PAYMENT_TEST_MYSQL_PASSWORD`. The host is hardcoded to loopback. Run both:

```sh
cd .migration-backup/backend
vendor/bin/phpunit -c phpunit-hardening.xml \
  --filter 'PaymentAccountingMySqlContentionTest|PaymentCompletionMySqlContentionTest' \
  tests/Hardening
```

Prepared checks are not executed/certified MySQL behavior or full native-engine
Product/Booking/fulfillment/authorization coverage. External payout finalization remains
blocked even in the prepared fixture; certification needs a separately approved real rail.

## Answers to all 38 required questions

1. **Common durable identity schema implemented?** Yes: four additive tables represent
   all five approved identity families; development migration applied.
2. **Global revisions immutable?** Yes: encrypted retained revisions and binding guards;
   update/delete rejected in disposable SQLite tests.
3. **Can rotation reinterpret old payments?** No fallback to current credentials:
   attempts/verifiers/refunds resolve their retained original revision.
4. **Non-MTN attempts before dispatch?** Yes; attempt and native process commit first,
   then an independent atomic claim. Dispatch inside an uncommitted outer transaction is rejected.
5. **Ambiguous recovery without blind redispatch?** Yes where original provider identity
   is queryable. UNKNOWN retains identity/reservation; missing remote refund identity
   requires provider-assisted investigation, not a new request.
6. **Product/Booking integrated for each applicable provider?** Yes for Cash/Wallet,
   accepted MTN synthetic paths and all four global providers. Orange cannot be certified
   without its authoritative verifier contract.
7. **Generic electronic refund orchestration?** Yes, for supported original-evidence
   adapters; unavailable provider contracts fail closed.
8. **Refunds tied to original custody/configuration/payment?** Yes, confirmed contribution,
   original revision and captured provider identity are retained.
9. **Over-refund/replay prevented?** Exact caps subtract returned/reserved authority;
   locked reservations and unique append-only operation groups prevent replay effects.
10. **Paystack defect fixed?** Yes in the active original-evidence path: Paystack secret,
    not Stripe secret; accepted/queued responses do not create refund success.
11. **PayPal defect fixed?** Yes: original capture refund response is parsed directly;
    matching amount/currency and `COMPLETED` are required.
12. **Receivable settlement operational?** Yes, scoped actual retained cash receipt
    reservation/evidence/once-only effect workflow; no invented Admin funding.
13. **Can it settle twice?** Duplicate operation/receipt reference/effect identities are
    rejected or replayed without another collection.
14. **Vendor payable reservation?** Yes, against unreserved canonical liability.
15. **Can payable be paid twice?** Reservation overdraw/replay is prevented; no API can
    declare external payout success. Real rail duplicate-payment certification is pending.
16. **External success distinguished from accounting?** Yes. Reserved/canceled/internal
    effects never establish verified external settlement.
17. **Actual external payout rail?** No. No beneficiary/transfer/funding contract was invented.
18. **MTN external blockers?** Written Cameroon receiving-merchant/model permission,
    exact environment/API/currency/refund contract, credentials and controlled UAT;
    public EUR sandbox is not Cameroon/XAF evidence.
19. **Orange external blockers?** Authoritative verification, callback, refund, market,
    merchant and environment contracts, then credentials/UAT.
20. **Application-ready for controlled UAT?** Global Paystack/Flutterwave/Stripe/PayPal
    confirmed collection flows and retained MTN foundation, only in a separately approved
    supported account/market/currency/environment. Refund UAT: Paystack/Stripe/PayPal.
21. **Contract-blocked providers?** Orange collection; Cameroon-specific MTN readiness;
    MTN/Orange/Flutterwave refunds; uncertified account/market/settlement capabilities.
22. **Platform-managed collection?** Global four providers and accepted MTN structural
    owner model; actual permission/market readiness unverified. Orange remains a foundation.
23. **Safe Vendor-direct?** MTN has preserved scoped structural support, but external
    permission/UAT is unknown. Orange is contract-blocked; no global-four Vendor model added.
24. **All electronics still disabled?** Yes, real checkout/discovery and non-testing
    collection/refund activation locks remain.
25. **Customer Wallet/Cash only?** Yes, confirmed by the public native catalogue.
26. **Real credentials used?** No.
27. **Real provider calls?** No transactional/account/refund/payout APIs. Public documentation
    only; executable provider transport was HTTP-faked in disposable tests.
28. **Real refunds/payouts?** None.
29. **Intentional schema changes?** The four tables, nullable revision pointer, profile
    uniqueness, retained foreign keys/indexes/guards and migration bookkeeping above.
30. **Existing financial values changed?** No; all 55 protected table fingerprints match.
31. **All 12 legacy Orders unverified?** Yes.
32. **Tests passed?** 119 distinct executable selected cases verified; 13 MySQL skips.
    Corrected Stripe assertion and receipt timestamp checks have focused receipts;
    frontend checks are explicitly intercepted contracts, not live financial certification.
33. **SQLite contention verified?** The eight requested identities with the blocked
    payout-finalization limitation above, plus five accepted core contention paths.
34. **MySQL/production uncertified?** All thirteen prepared engine cases and actual native
    production-engine behavior/load; no production instance or engine/version accessed.
35. **Application-side work remains?** Contract-dependent verifier/refund/beneficiary/rail
    adapters, any actual-engine incompatibilities and controlled release of intentional
    activation gates after approval. Broad legacy fixture alignment remains outside the
    selected milestone. No known independent global Product/Booking operation/UI feature
    remains blocked on the superseded identity proposal.
36. **Needs onboarding/credentials/UAT rather than new architecture?** Merchant/legal
    receiving-account permissions, market/currency/environment confirmation, scoped
    credentials, actual callback/refund/settlement evidence and production-engine testing.
37. **Shortest first controlled electronic payment?** Select one approved provider/market.
    For Cameroon/XAF MTN, obtain the already prepared written clarification after separate
    contact approval; for an alternative, confirm supported account/currency rather than
    infer Cameroon eligibility. Approve an isolated non-production environment and its
    dispatch gates, provision scoped credentials, execute one native Product or Booking
    collection with callback/replay/UNKNOWN recovery. No unrelated payout/Disbursement
    architecture is prerequisite. Real-money testing requires separate explicit approval.
38. **Shortest production activation?** Complete the chosen provider's merchant/market,
    callback/refund/recovery/settlement UAT; execute actual-engine native/concurrency tests,
    resolve proven incompatibilities and operational refund/settlement policy, then obtain
    separate production credential/activation/first-transaction approval. External Vendor
    payouts stay unavailable until their own approved funding/beneficiary/rail proof exists.

**STOP.** No provider activation, contact, credential setup, real financial operation,
production work, historical classification/deletion or publishing is authorized by
this delivered milestone. Further steps require explicit approval.