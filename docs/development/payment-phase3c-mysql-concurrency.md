# Payment Phase 3C — MySQL financial concurrency certification

2026-10-03. **MYSQL CERTIFICATION FAILED — CURRENT-READ CANDIDATE FAILS INDEPENDENT-ALLOCATION BOUNDARY.**
The separately approved minimum receipt guard, semantic JSON and MySQL index-name
corrections are delivered. All thirteen original cases pass after restart from
case one. The first expanded test committed 14000 reserved units against 10000
confirmed refundable units; the financial-invariant stop gate is honored.
The approved current-read candidate fixes the tested refund snapshot cap but fails
independent-allocation progress under InnoDB next-key locking. It was withdrawn;
no reservation/authority-read correction remains installed.
Disposable synthetic fixtures only.
Production MySQL version/configuration is unknown; no production discovery.
No provider activity, activation, external payout rail, FX or legacy classification.

## Current result — approved reservation candidate withdrawn at locking-boundary gate

### Subsequent production database architecture comparison — read-only

The later [proof-only MySQL B / PostgreSQL RC report](payment-engine-proof-comparison.md)
records an unchanged MySQL 13/51 pass and thirteen expanded passing groups on each
engine, followed by a new identical-receipt canonical confirmation/replay failure
on both. Both engine proofs stopped without correction. Wallet/fulfillment and
remaining native parity gates were unrun; no permanent prototype or architecture
was installed. See that report for current execution status and exact receipts.

The [AgendaAlly PostgreSQL versus MySQL comparison](agendaally-production-database-comparison.md)
traces current reservation callers, inventories concrete compatibility work and
defines an unexecuted PostgreSQL proof suite. It recommends **insufficient evidence:
native PostgreSQL proof before selecting the production engine**. No current
application caller was found to require successful old-snapshot nested reservation;
the earlier Strategy G recommendation was conditional on retaining that contract,
not justification for a permanent manifest in AgendaAlly. Fresh owned B/C units are
smaller alternatives requiring separate approval. No engine, schema, transaction,
test or financial correction is implemented; Phase 3C remains stopped.

### Revised design review — no implementation

The subsequent design-only instruction is answered in
[revised reservation design comparison](payment-phase3c-reservation-design-comparison.md).
It maps the actual queries/indexes to retained gap/supremum evidence, distinguishes
the missing native wait-graph receipt, and compares ten strategy families.
For unchanged RR/nested semantics it recommends separately approved complete parent
membership metadata plus known-existing current primary-key reads, not a covering
index cure or a duplicate monetary counter. A smaller fresh-transaction pattern
requires changing the caller transaction contract. No recommendation is implemented;
the original defect, stopped status and uncertified scope remain.

Approval is supplied in
`attached_assets/Pasted-Approved-only-for-the-minimum-current-read-reservation-_1791077352472.txt`.
It permits current authoritative reads only in the three existing `reserve`
branches and an opt-in balance reader under the existing allocation mutex.
Other financial paths are testing/tracing only, not remediation. No broader
serialization, new schema/index, isolation, identity, semantics or provider change
is authorized. The independent-allocation requirement is an acceptance assertion,
not merely an observation to ignore after the cap passes.

### Candidate and successful cap reproduction

Only `FinancialOperations::reserve` and an opt-in `AllocationBalances` reader were
temporarily changed. Ordinary projections, public `reserved`, other financial
writers and existing equations were retained. MySQL current child reads used
`LOCK IN SHARE MODE` with existing allocation-leading/unique index hints;
exact-unit sums operated on returned rows. Replay was still evaluated first.
No schema, migration, index, unique/FK/guard or isolation setting changed.

The original open-reader-snapshot scenario passed: **1 test / 10 assertions**.
The reader still saw ordinary reserved SUM **0** after owner committed **7000**,
but its reservation's current reads rejected the additional **7000**.
Final reserved units **7000**, operation count **1**, allocation version **3**,
refund-principal effect count **0**, provider requests **none**.
The original `reserved <= 10000` assertion is retained; new strict assertions also
require rejection, exactly 7000 and exactly one operation.

Receipts:
`.local/payment-phase3c-reservation-current-read-{tests.txt,junit.xml,evidence.json}`.
The original failing 14000 snapshot evidence is retained separately, not overwritten.

### Required independent-allocation test — FAILED

`PaymentPhase3cIndependentMySqlTest` prepares two separate fully funded allocations
and begins with an empty financial-operation table:

- Allocation 1: verified platform funding 10000; owner reserves refund **1000**,
  while the outer owner transaction remains open.
- Allocation 2: independently verified offline funding 10000; outstanding
  commission receivable **1000**. A separate PDO connection successfully obtains
  allocation 2's own primary-key `FOR UPDATE` lock.
- That second connection tries to reserve receivable **100**. It does not complete
  before allocation 1 commits. Laravel reports `Illuminate\Database\DeadlockException`
  for the nested concurrency failure; the captured native driver code is null.
  Do not claim a specific native 1205/1213 code from this receipt.
- The second transaction rolls back. Committed operation evidence contains only
  allocation 1's **1000 RESERVED refund**, no allocation 2 reservation.

**1 test / 5 assertions / 1 genuine independent-progress failure.**
The assertion requires the unrelated allocation to succeed **before** owner commit;
it is not weakened to permit failure/retry after the unrelated owner commits.

Retained `performance_schema.data_locks`, captured while owner was still open,
shows:

- Allocation 1 primary row `X,REC_NOT_GAP`.
- `financial_operation_request_unique`: shared `supremum pseudo-record` lock,
  plus `S,GAP` evidence.
- Allocation/kind/state operations index: shared `supremum pseudo-record` lock,
  plus `S,GAP` evidence.
- Context/ledger allocation indexes also have next-key boundary gap evidence.

These missing-key/range/supremum locks can inhibit allocation 2's INSERT despite
its separate parent mutex. This is not evidence of a deliberate table-wide mutex:
`TABLE IS/IX` entries are normal intention locks, not table-wide money serialization.
Nevertheless, observed independent-allocation progress violates the approved
boundary. Both MySQL connections keep REPEATABLE READ.

`EXPLAIN` evidence shows the reserved query using the existing allocation/kind/state
index with access type **range**, not an unhinted full-table scan. The empty replay
lookup reports `no matching row in const table`. Index hints therefore did not
eliminate missing-key/next-key interference.

Receipts:
`.local/payment-phase3c-reservation-independent-{tests.txt,junit.xml,evidence.json}`.
Historical candidate:
`.local/payment-phase3c-reservation-current-read-candidate.diff`.

### Stop, withdrawal and remaining scope

The broader-locking acceptance gate was honored immediately. No alternate locking
strategy, isolation change, schema/index addition or other financial-path correction
was attempted. The candidate's two application files were restored exactly to the
pre-approval state; the unsafe candidate was not promoted as contained.
The new/strengthened tests and exact candidate diff/evidence remain.

**Current active source still has the originally reproduced stale-snapshot defect.**
The candidate's cap pass is historical evidence, not a passing result for current
application source. Full financial concurrency remains uncertified.

Unrun because of the gate: exact 3000 remainder; replay parameter matrix; concurrent
committed refund effects; stale payable/receivable caps; exclusions; broader
rollback/cancellation/finalization/UNKNOWN/PENDING cases; dedicated deadlock cases;
SQLite contention/regression; restart of the original thirteen; tests of the other
authorized financial paths. The original thirteen's prior 13/51 pass is unchanged
historical evidence, not falsely claimed as rerun in this increment.

Next direction requires separately agreed scope: determine a bounded current-
authority strategy that handles absent rows without cross-allocation gap blocking.
No compliant replacement is yet proved. Merely switching SHARE to UPDATE, adding
another index hint or ignoring the progress assertion is not an established fix.
A materialized per-allocation/per-contribution authority design would be a **schema/
maintained-accounting-state change requiring a new proposal and approval**, not an
implementation authorized here. No such design or migration is made.

Protected before/after receipts:
`.local/payment-phase3c-reservation-approved-{before,after,comparison}.json`.
All **59** protected tables match the original row fingerprints, all **465** schema
objects match, all four evidence tables remain empty, all **12** legacy Orders
remain unverified. Dedicated database/user were dropped, MySQL shut down cleanly
and all four disposable development opt-in flags were removed.

## Historical result — approved index naming correction and financial stop

Only the existing `electronic_collection_attempts` UNIQUE index identifier is
shortened on MySQL to `eca_provider_reference_unique`. Exactly
`(provider, provider_reference)`, column order, uniqueness, reference nullability,
FKs, guards and financial/provider identity are retained. SQLite still receives
the original default name and identical DDL. No index was dropped, replaced with
nonunique behavior or broadened. No migration file/manifest or owned schema changed.

### Required naming verification

**MySQL 2 tests / 28 assertions; SQLite 2 / 28**, all passed:

- Full fresh completion/accounting bootstrap.
- Original completion migration empty down/up recreating the same index/guards.
- Native metadata proves exact ordered columns and unique status; MySQL nullable
  reference remains YES, SQLite retains its original 65-character index name.
- Same provider/reference across distinct valid funding events is rejected:
  MySQL 1062 names the exact new UNIQUE index.
- Distinct references insert; multiple NULL references insert only with distinct
  event/process/attempt identities and correctly bound contexts/revisions.
  This is legitimate nullable-reference behavior, not duplicate economic identity.
- Original retained attempt remains unchanged.

Receipts: `.local/payment-phase3c-index-{mysql,sqlite}-{tests.txt,junit.xml}`.
Application change: only the driver-conditional index name in `CompletionSchema`.
Tests: three index fixture/classes and two disposable expanded-test fixture/classes.
Financial services and the original thirteen classes/datasets/assertions are unchanged.

### Original thirteen — all executed from case one

**13 passed / 51 assertions / zero failures, errors or skips.**
Receipt: `.local/payment-phase3c-index-restarted-prepared-{tests.txt,junit.xml}`.
All twelve writable cases exercise overlapping independent connections, native
lock loss and post-commit replay/cap behavior. The thirteenth tests blocked external
payout finalization sequentially through two independent connections; it is not
claimed as an overlapping payout race or a working external rail.

### First expanded test — financial invariant failure

`PaymentPhase3cSnapshotMySqlTest::test_existing_repeatable_read_snapshot_cannot_overreserve_refund`
uses two distinct real PDO connections and a deterministic open-transaction
barrier, not sequential calls on one connection.

Synthetic original authority: one verified platform Paystack-context collection,
**10000 units**, native scale 2, commission 1000, Vendor payable 9000.
Both reservations have different valid request identities and the same original
confirmed contribution. Trusted synthetic service caller records actor 2;
HTTP/Admin authorization is not exercised.

1. Reader B opens REPEATABLE-READ transaction and performs ordinary reserved SUM:
   **0**, establishing its consistent-read view.
2. During B's open transaction, owner A calls unchanged `FinancialOperations::reserve`
   for **7000 units** and commits. Owner current aggregate sees **7000**.
3. B's ordinary read still sees **0**. Its locking read nevertheless sees the
   current allocation version **3**; the row is not a stale PHP object.
4. B calls unchanged native reservation service for another **7000 units**, inside
   its existing transaction/savepoint. Native allocation lock/version claim succeeds,
   but ordinary monetary aggregates use B's older read view. Request is accepted.
5. B commits. Independent owner reads **two RESERVED operations totaling 14000**,
   allocation version **4**, against the unchanged original refundable **10000**.

**Observed excess: 4000 reserved units.** No refund principal effect occurred
(count **0**), no provider request was made, no external refund/payout was performed.
This proves excessive durable reservation, **not** 14000 actually paid/refunded.

Expanded result: **1 test / 7 assertions / 1 genuine failed cap assertion**.
The assertion remains `reserved <= original funding`; it is not changed to accept
the reproduced defect. Remaining expanded scenarios were not run after the gate.
Receipts: `.local/payment-phase3c-index-expanded-{tests.txt,junit.xml}`,
`.local/payment-phase3c-stale-snapshot-evidence.json`.

### Source cause and exposure boundary

`FinancialOperations::reserve` → `lock` → `AllocationWriter::lock`
(`SELECT ... FOR UPDATE`) and current version increment →
`reserved` (ordinary `SUM(amount_units)` over RESERVED/UNKNOWN/PENDING) →
capacity subtraction → permitted INSERT.
MySQL current locking reads observe the latest committed allocation, but do not
refresh an earlier REPEATABLE-READ consistent-read view used by ordinary queries.
The same source family reads balances/context/ledger/operation evidence ordinarily;
the allocation mutex alone therefore does not prove current monetary authority.

**Source-confirmed, reproducible native service financial invariant failure.**
The existing API `PaymentOperationsController::store` calls the service directly
after scope/validation; no pre-service outer read transaction was demonstrated
there. This reproduction deliberately uses supported nested Laravel transactions.
Do **not** label it a proven unauthenticated/Customer HTTP exploit, immediately
exploitable route-level P0, or production incident. The creator's explicit stop
rule includes any additional financial invariant issue beyond naming, so the
stop applies even without that stronger route-exploitability claim.

SQLite's prior accepted contention cases and current naming tests pass, but the
same stale-read-view scenario was **not** separately reproduced on SQLite.
Their results do not refute this MySQL failure. Payable reservation uses the same
ordinary reserved aggregate, but its stale-view outcome was **not executed**.

Minimum containment direction, **proposal only**: while retaining the existing
allocation mutex, make operational capacity/idempotency evidence come from current
committed authority under an established MySQL snapshot, not an older consistent
view. Preserve financial meaning, identity, scale, FKs/guards, SQLite behavior
and REPEATABLE-READ; do not globally weaken isolation or merely add a precheck.
Review the nested-transaction contract and related authoritative reads before
implementation. Obtain separate approval for that application containment and
focused stale-snapshot/rollback/cap verification, then restart Phase 3C.

## Historical result — approved semantic JSON correction

The creator approved deterministic **semantic** JSON equality for only
`native_components` and `effect_data`, not the earlier proposed MySQL CAST
comparison. Both engines use the same read-only comparator. Object membership
is compared independent of property order; arrays retain order; strings, numbers,
booleans, null, missing members, objects and arrays remain distinct.
No existing JSON is normalized or rewritten.

Changed application sites are only `AllocationWriter::commit`'s
`native_components` comparison and `AccountingEffects::append`'s `effect_data`
comparison, with a shared `SemanticJson` helper. All other field comparisons,
financial identities, money/commission/custody semantics, lock/retry strategy,
serialization, schemas, migration files and manifests remain unchanged.
The helper validates JSON, compares tagged trees, sorts object membership only,
and preserves exact numeric tokens as well as decoded scalar types. This avoids
float rounding or BIGINT-as-string silently equating genuinely different evidence.
Invalid JSON and unrepresentable exponent arithmetic fail closed.

### Focused verification and SQLite regression

- **MySQL 3 tests / 35 assertions; SQLite 3 / 35**: 14 comparison cases per
  engine, invalid JSON, and actual quote/effect replay and mutation paths.
- Equivalent whitespace, object-key ordering, escaped slash/Unicode, nested
  objects and equivalent float notation pass.
- Numeric/string, boolean/numeric, missing/null, changed object membership,
  array order, nested type, object/array and decoded integer/float mismatches fail.
  Large-integer/string, adjacent large integers and distinct fractional values
  do not collapse through PHP numeric decoding.
- Successful replay leaves retained quote JSON and the entire effect row
  byte/value-identical; changed native evidence/proof is rejected.
- Selected existing SQLite regression: **50 tests / 319 assertions**, covering
  `PaymentAccountingTest`, `PaymentAccountingConcurrencyTest`,
  `PaymentCompletionContentionTest`, `PaymentCompletionIdentityTest`.
- Receipts: `.local/payment-phase3c-json-focused-{tests.txt,junit.xml}` and
  `.local/payment-phase3c-json-regression-{tests.txt,junit.xml}`.
- The first MySQL comparison invocation timed out during repeated per-case
  schema setup after ten cases, with no assertion failure. Its interrupted log
  is retained; it is **not counted as a completed suite**. Grouping the same
  comparison assertions into one fixture produced the complete passing run.

### Exact original thirteen restarted from case one

Original classes, datasets and assertions are unchanged.
**5 passed, 1 setup error, 7 not executed; 6 attempted tests / 30 assertions.**
The first five use independent owner/contender PDO connections, overlapping
transactions, actual InnoDB lock-loss errors 1205/1213, then post-commit replay.
They verify allocation creation, contribution creation, confirmation/base effects,
synthetic refund effect replay and synthetic internal settlement effect replay.
They do not establish external payout execution or expanded conservation races.

The next dataset, completion `attempt`, failed before its financial body:

```text
SQLSTATE[42000] / MySQL 1059
Identifier name 'electronic_collection_attempts_provider_provider_reference_unique'
is too long
```

Source chain: `PaymentCompletionMySqlContentionTest` →
`PaymentCompletionContentionTest::setUp` → `PaymentCompletionFixture::setUp` →
`CompletionSchema::up` → Laravel Blueprint
`$t->unique(['provider','provider_reference'])` →
native ALTER TABLE ADD UNIQUE.
The generated identifier is **65 characters**, exceeding MySQL's **64-character**
index-name limit. SQLite accepts the same name; its existing selected identity
tests pass. The unique columns/invariant are not shown defective: the engine
rejects the index name before completion bootstrap finishes.

**Historical MYSQL SCHEMA CORRECTION REQUIRED.** This was a schema naming compatibility stop,
not a reproduced financial P0 or nullable-uniqueness defect. The remaining seven
prepared completion cases and expanded Phase 3C races were not run.
No schema was changed to get past this failure.
Receipts: `.local/payment-phase3c-json-restarted-prepared-{tests.txt,junit.xml}`.

Historical minimum next proposal, **subsequently approved and implemented**:
give this existing composite UNIQUE
index an explicit MySQL-safe name of at most 64 characters in fresh MySQL DDL,
retaining exactly `(provider, provider_reference)`, uniqueness, nullability,
all FKs/guards, financial/provider identity and SQLite's existing DDL/name.
No owned-development migration/backfill is necessary. Any existing MySQL upgrade
must first inspect whether the same index already exists under another name;
no live database is assumed or accessed. Obtain separate schema approval before
implementation, verify disposable bootstrap/uniqueness/migration behavior and
restart original certification from case one under unchanged stop gates.

## Approved receipt guard correction and resumed result

The creator approved **only** equivalent MySQL database INSERT/UPDATE enforcement
of `receipt_anchor_context_id IS NULL OR receipt_anchor_context_id <> id`,
preserving financial identity, FKs, accounting semantics and SQLite behavior.

`AccountingSchema` replaces only that exact CHECK, only for MySQL
`payment_collection_contexts`. It installs **AFTER INSERT** and **BEFORE UPDATE**
triggers with SIGNAL 45000 / native error 1644. AFTER INSERT sees the assigned
AUTO_INCREMENT identity, including explicit IDs. Every other CHECK, unique key,
column and FK remains unchanged. Missing expected CHECK fails closed.
SQLite continues to create the exact original CHECK; no MySQL trigger is added there.

No migration file/manifest was added or edited and no owned development migration
was run. Fresh original migration bootstrap installs the guard via its existing
helper. The explicit existing-table installation helper refuses invalid
preexisting self-anchors without rewriting/classifying rows. It is not automatically
run on an existing database, and no production upgrade was performed.

### Guard verification

**17 tests / 57 assertions passed**:

- Disposable MySQL: **9 tests / 34 assertions**.
- Disposable SQLite: **8 tests / 23 assertions**.
- Fresh full accounting bootstrap; generated and explicit-ID self-anchor INSERT
  rejection; self-anchor UPDATE rejection with unchanged row; NULL/distinct anchors;
  restrictive retained FK; failed-statement rollback inside a live transaction;
  empty original migration down/up reinstalling enforcement; nonempty rollback
  refusal; existing valid-table installation preserving rows; existing invalid
  synthetic-row preflight refusing without mutation.
- SQLite original CHECK retained with no MySQL guard triggers.

The new guard fixtures seed valid disposable allocation rows directly to isolate
DDL enforcement from application quote serialization. They do **not** bypass or
alter the original thirteen financial tests.

Receipts: `.local/payment-phase3c-approved-guard-tests.txt`,
`.local/payment-phase3c-approved-guard-junit.xml`. The original first schema-failure
receipts are retained separately.

### Historical guard-only restart — JSON failure subsequently corrected

The exact original classes/datasets/assertions were unchanged and restarted.
First `economic_creation` now passes schema setup, but raises:
**`DomainException: Committed quote is immutable: native_components`**.
Run: **1 attempted test, 1 error, 1 driver assertion, 0 financial passes;
12 not executed**. A driver assertion is not a conservation assertion.
Receipts: `.local/payment-phase3c-resumed-prepared-tests.txt`,
`.local/payment-phase3c-resumed-prepared-junit.xml`.

Source: `PaymentAccountingMySqlContentionTest` → `AllocationWriter::commit`
→ compact PHP JSON serialized into the native JSON column → retained MySQL
representation → raw string equality across frozen quote terms.
An isolated read-only MySQL CAST probe gives:

```text
Input:    {"authority":"isolated_native_fixture","service_fee_units":"1000"}
Retained: {"authority": "isolated_native_fixture", "service_fee_units": "1000"}
Bytes equal: false
Strict decoded values and scalar types equal: true
```

The same raw-text comparison existed for retained `AccountingEffects::append`
`effect_data`. That sibling is source-confirmed but was not reached by the resumed
financial tests. Both sites were subsequently corrected under semantic approval.
No production behavior or exploit is claimed.
Evidence: `.local/payment-phase3c-json-probe.php`,
`.local/payment-phase3c-json-representation.json`.

**MYSQL APPLICATION COMPATIBILITY BLOCKER.** This is fail-closed serialization
compatibility, not reproduced excessive refund/reservation or unbacked money.
The winner transaction did not complete, so no economic-creation conservation
pass or certified owner/contender outcome is claimed.
Do not mask it by changing the fixture, JSON column, assertion or database driver.

Historical next proposal, **superseded by the creator's semantic-comparison approval**:
compare expected frozen JSON using
MySQL's own canonical JSON representation for these two JSON-column comparisons
only. Preserve all JSON scalar values/types and array order, all identity/money
fields and SQLite's existing comparison behavior. No schema change or migration.
Changing content, amount/type, proof, operation or identity must remain rejected.
The creator instead approved the cross-engine semantic comparison described above.

## Prepared cases recorded before execution

The exact previously skipped cases are the two existing data providers, unchanged.
SQLite cannot establish InnoDB row/gap locks, lock-timeout/deadlock handling,
MySQL retained-evidence constraints or repeatable-read snapshot behavior.

`PaymentAccountingMySqlContentionTest::test_real_mysql_locks_and_once_only_accounting`:

| Dataset | Invariant and fixture rows | Expected InnoDB/identity boundary | Actual result |
|---|---|---|---|
| `economic_creation` | One allocation for the frozen booking/Shop/checkout obligation | Allocation unique key blocks competing insert; post-commit replay reuses row | PASSED |
| `contribution` | One collection context for allocation/funding slot/event | Locked allocation and contribution unique identity; replay does not duplicate | PASSED |
| `confirmation` | One confirmed contribution/base commission/liability | Allocation/context locks and once-only base ledger effects | PASSED |
| `refund` | One synthetic refund/reversal effect group against held funding | Locked allocation and append-only effect identity; no duplicate principal/reversal | PASSED; not complete operation finalization certification |
| `vendor_settlement` | One synthetic internal accounting effect group | Locked allocation/group identity; NOT external payout verification | PASSED; synthetic internal effects only |

`PaymentCompletionMySqlContentionTest::test_two_connections_serialize_durable_authority_and_replay`:

| Dataset | Invariant and fixture rows | Expected InnoDB/identity boundary | Actual result |
|---|---|---|---|
| `attempt` | One attempt/process for original funding event/revision/context | Frozen allocation/context locks plus unique event/process identity | PASSED |
| `claim` | One dispatch claim for retained attempt | Conditional state/claim UPDATE; competing claim waits then loses | PASSED |
| `provider_finalization` | Once-only original contribution, commission and payable | Locked canonical confirmation and terminal retained attempt identity | PASSED |
| `refund_reservation` | Reservation 6000; competing 5000 cannot exceed original 10000 | Authoritative allocation lock; persisted reservation/effect capacity | PASSED for fresh transaction; expanded stale-snapshot case FAILED |
| `refund_finalization` | One refund principal/reversal after synthetic pending→processed | Original operation/allocation lock; unique once-only effect group | PASSED |
| `receivable` | Retained receipt satisfies original 1000 receivable once | Operation/allocation locks, unique receipt/reference and effect | PASSED |
| `payable_reservation` | Only one 9000 reservation against 9000 payable | Locked allocation and persisted unreserved-liability calculation | PASSED for fresh transaction; expanded stale-snapshot case unrun |
| `payout_finalization_blocked` | Neither connection can fabricate external settlement | No approved external rail; no transfer ID/effect/liability reduction | PASSED; rejection only, sequential independent connections |

The prepared payout-block case is sequential independent-connection rejection,
not yet an overlapping transaction race. Additional tests must distinguish this.
The other prepared cases use overlapping transactions on independent PDO connections:
the owner keeps its write uncommitted, the contender waits/times out, then replay
occurs after owner commit. That is actual DB contention, not simultaneous CPU dispatch.

## Protected-state baseline

Before environment/harness changes: **59 protected tables**, covering the previous
55 plus all four new evidence tables; **465 schema objects** fingerprinted.
All 12 legacy Orders are `unverified`. Baseline:
`.local/payment-phase3c-protected-before.json`.

## Actual engine and isolation

- MySQL **8.0.42**, InnoDB; production version/configuration **UNKNOWN**.
- Server and Laravel-created session: **REPEATABLE-READ**, autocommit **1**.
- SQL modes: `ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION`.
- Server lock-wait timeout 50 seconds; prepared contender session specifies 1 second.
  `innodb_rollback_on_timeout=0`. No isolation weakening.
- PHP **8.4.16**, PDO MySQL / **mysqlnd 8.4.16**, Laravel **v12.46.0**,
  PHPUnit **10.5.60**.
- Disposable server bound only to `127.0.0.1:3307`; X protocol and binary log off,
  64 MB InnoDB buffer pool, no preview/public port exposure. Dedicated initially
  empty `agendaally_payment_disposable_phase3c`, DB-scoped synthetic test account
  with no password; no application/provider/production credential used.
- Prepared harness has separate owner/contender PDO connections. After approved
  corrections all thirteen pass in their stated scope. Expanded snapshot test
  keeps the reader transaction open across owner commit and proves over-reservation.

## Historical reproduced schema blocker — corrected under explicit approval

Source chain:

`PaymentAccountingMySqlContentionTest` → `PaymentAccountingFixture::setUp`
→ `2026_10_03_100100_create_payment_collection_contexts.php`
→ `AccountingSchema::create` → native MySQL `CREATE TABLE`.

The unchanged approved creation statement contains:

```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
...
CHECK(receipt_anchor_context_id IS NULL OR receipt_anchor_context_id <> id)
```

MySQL rejects `payment_collection_contexts_chk_6` with **3818 / SQLSTATE HY000**:
“Check constraint ... cannot refer to an auto-increment column.”

The intended guard prohibits a context from being its **own child receipt anchor**.
Root contexts retain the unique receipt claim with a NULL anchor; other contexts
reference a distinct retained root. This is not an expendable assertion.
MySQL prohibits any AUTO_INCREMENT column in a CHECK expression; the failure
is in accepted application DDL, **not a SQLite-only test assertion**.
It cannot be fixed by connection configuration, relaxed isolation or deleting a test.

An independent minimal disposable reproduction confirms:

- SQLite creates the same semantic guard and accepts `(1,NULL)` and `(2,1)`.
- SQLite rejects `(3,3)` without persisting the invalid row.
- MySQL rejects the corresponding auto-increment DDL with **3818** before rows exist.

Official MySQL restriction:
https://dev.mysql.com/doc/refman/8.0/en/create-table-check-constraints.html

Evidence:
`.local/payment-phase3c-prepared-tests.txt`,
`.local/payment-phase3c-prepared-junit.xml`,
`.local/payment-phase3c-diagnostics.php`,
`.local/payment-phase3c-schema-diagnostics.json`.

### Historical first-run counts and stop interpretation

**13 cases identified and documented before execution.**
**1 attempted setup, 1 error, 0 passed cases, 0 PHPUnit assertions;
12 cases NOT EXECUTED after the stop.** They are not reported as successful,
failed financial races or environment skips.
Minimal schema diagnostics matched the expected engine difference; these are
not financial concurrency tests or extra PHPUnit passes.

No excessive refund/reservation, unbacked money, duplicate liability or nullable
uniqueness failure was observed. Those invariants were **not reached**, not proved
safe. This is a schema compatibility stop, not a reproduced financial P0.
No additional refund/payable/Wallet/callback/stale-model/rollback/deadlock/timeout
races were run after this gate. No broad or selected financial regressions were
rerun because financial source and tests were unchanged and certification stopped.

## Source lock strategy — prepared cases pass; stale-snapshot capacity fails

- Allocation creation: economic uniqueness plus locked retained-row reuse.
- Contribution/confirmation/base commission: `AllocationWriter` allocation/context
  `FOR UPDATE`, conditional version/state updates and once-only ledger effects.
- Attempt creation: retained allocation/context locks and unique event/process
  identities; dispatch uses a conditional state/claim update.
- Refund/payable/receivable reservation: `FinancialOperations` allocation row lock,
  version serialization and persisted capacity/reservation calculation.
- Refund/receipt finalization: retained operation/allocation locking, terminal
  transitions, original evidence and unique effect/receipt identity.
- Cancellation/release: locked allocation and conditional unclaimed RESERVED
  transition; no external payout-success method.
- Wallet debit/transfer, internal payout bookkeeping and Product fulfillment:
  accepted native transaction/terminal/CAS protections from baseline containment
  reports. Their actual InnoDB lock ordering and rollback remain untested here.

Nullable unique-key behavior, retained-evidence FK delete/rollback behavior,
integer range/signedness/casts/SUM behavior and repeatable-read stale snapshots
remain **PARTIAL/BLOCKED** except the named prepared/name/receipt guard checks.
Source BIGINT/exact-unit design and prior SQLite evidence
are not substituted for these engine tests.

## Historical minimum schema proposal — subsequently approved and implemented

Keep the same economic identity, receipt-root meaning, PK type, nullable anchor,
unique receipt claim, retained FK and every other guard. Keep SQLite's CHECK.
On MySQL only, replace this prohibited self-anchor CHECK with equivalent
**database-enforced** guards: AFTER INSERT (generated NEW.id is then available)
and BEFORE UPDATE rejecting a non-NULL anchor equal to NEW.id using SIGNAL.
Cover auto-generated and explicit IDs, direct SQL and ORM writes, rollback and
retained FK behavior. Do not replace it with application-only prechecks.

The creator subsequently approved the reviewed fresh-bootstrap MySQL DDL correction
and its bounded migration treatment. No owned development migration/backfill is needed for the reproduced
SQLite state. Any supported existing MySQL installation would need a separately
reviewed additive guard-upgrade/preflight strategy; no live schema/data is assumed
or accessed. No historical migration/manifest was changed; the helper was changed
only for the approved MySQL guard as described above.
Other MySQL incompatibilities may remain and must be discovered after this
first correction, not guessed or bypassed now.

## Protected state and cleanup

**All 59 protected tables have identical before/after row counts and full-row
fingerprints.** That includes the previous 55 plus all four new evidence tables.
**465 schema objects unchanged**, SHA256 before/after:
`f6d193bf60ccf39285e8ee4d1b87eb62cfe8ff9a2a9f825b476a7da594fb37ea`.

Counts before→after: Orders 12→12 (all unverified), Bookings 4→4,
Transactions 15→15, Wallets 36→36, Wallet histories 1→1,
`platform_fee_ledger_entries` 1→1, `order_refunds` 0→0, payouts 1→1,
global `payment_payloads` 0→0 and Shop `shop_payments` 0→0.
Each of the four new evidence tables is **0→0**, with unchanged empty-row digest.
Full per-table fingerprints/counts:
`.local/payment-phase3c-protected-before.json`,
`.local/payment-phase3c-protected-after.json`,
`.local/payment-phase3c-protected-comparison.json`.
Approved resumption independently reconfirmed the same original baseline:
`.local/payment-phase3c-approved-before.json`,
`.local/payment-phase3c-approved-after.json`,
`.local/payment-phase3c-approved-comparison.json`.
Latest semantic-correction run also matches the **original** baseline exactly:
`.local/payment-phase3c-json-approved-before.json`,
`.local/payment-phase3c-json-approved-after.json`,
`.local/payment-phase3c-json-approved-comparison.json`.
Index correction and failed expanded test also match the **original** baseline:
`.local/payment-phase3c-index-approved-before.json`,
`.local/payment-phase3c-index-approved-after.json`,
`.local/payment-phase3c-index-approved-comparison.json`.

Initial work added the `mysql80` system dependency, disposable environment/test
account and diagnostic receipts/reports. Approved resumption changed only the
schema helper and added three isolated guard fixture/test files plus a read-only
JSON probe. Subsequent explicit semantic approval changed only the two JSON
comparison sites/shared helper and added three focused test files. No original
test assertion, migration file/manifest, economic meaning or UI changed.
Subsequent approved naming changed only the MySQL UNIQUE identifier; new naming
and stale-snapshot fixture/tests are described above. No failed assertion or
reservation implementation was altered.
The package remains available for a subsequently approved correction.
The dedicated database/account were removed, server shut down gracefully,
and all four temporary test opt-in environment variables removed.
No real provider/customer/vendor/financial operation or production contact occurred.
Cheap native checks: Admin `/transactions` HTML 200; protected finance API 401
without authentication when requesting JSON. No UI redesign or authenticated
browser/financial-UI claim was made.

## Final boundary matrix

SQLite classifications refer only to previously accepted bounded evidence;
PARTIAL means the expanded Phase 3C scenario was not established there.

| Financial boundary | SQLite | MySQL | Invariant | Result / blocker |
|---|---|---|---|---|
| Provider attempt identity | VERIFIED | VERIFIED | One retained attempt per economic event | Original attempt contention/replay passed |
| Dispatch claim | VERIFIED | VERIFIED | One claim, no duplicate dispatch | Original claim contention/replay passed |
| Provider success finalization | VERIFIED | VERIFIED | One funding/commission/liability result | Original finalization contention/replay passed |
| Callback/polling replay | PARTIAL | BLOCKED | Same evidence converges once | Native overlapping race unrun |
| Canonical allocation creation | VERIFIED | VERIFIED | One frozen economic allocation | Original contention/replay case passed |
| Contribution confirmation | VERIFIED | VERIFIED | One confirmation/base effect | Original contention/replay case passed |
| Commission recognition | VERIFIED | VERIFIED | No duplicate commission | Original confirmation produced two once-only base ledger entries |
| Wallet debit | VERIFIED | BLOCKED | Debits never exceed balance | Expanded MySQL races unrun |
| Wallet transfer | VERIFIED | BLOCKED | Combined value conserved, terminal finality | Expanded MySQL races unrun |
| Refund reservation | PARTIAL | FAILED | Reserved/refunded ≤ refundable value | 14000 reserved against 10000 under stale RR view |
| Refund finalization | VERIFIED | PARTIAL | One principal/reversal effect | Prepared operation finalizer/replay passed; expanded stale/rollback unrun |
| Refund release | PARTIAL | BLOCKED | Released capacity reusable once | Release/new-request race unrun |
| Commission receivable settlement | VERIFIED | PARTIAL | Receipt collects original receivable once | Prepared exact receipt/replay passed; expanded variants unrun |
| Vendor payable reservation | VERIFIED | PARTIAL | Reservations ≤ unreserved liability | Prepared fresh-view cap passed; stale-view/exhaustion variants unrun |
| Payout reservation cancellation | PARTIAL | BLOCKED | No double release/stale cancel | Expanded race unrun |
| External payout finalization | VERIFIED | PARTIAL | No settlement without approved rail | Two-connection sequential rejection passed; overlapping rejection unrun |
| Product fulfillment finality | VERIFIED | BLOCKED | One trusted settlement; legacy fail closed | Native MySQL race unrun |
| Configuration revision retention | VERIFIED | BLOCKED | R1 evidence remains R1 after rotation | Native MySQL race unrun |
| Receipt self-anchor schema guard | VERIFIED | VERIFIED | Child receipt anchor cannot equal its own context ID | Approved DB triggers; fresh/upgrade/rollback cases pass |

## All 44 required answers

1. **Version?** Actual disposable MySQL 8.0.42; production unknown.
2. **Engine?** InnoDB.
3. **Isolation?** REPEATABLE-READ, unchanged; autocommit 1.
4. **All 13 executed?** Yes, restarted unchanged from case one.
5. **Passed/failed?** Original thirteen: 13 passed/51 assertions, no errors/skips.
   First expanded test: one genuine cap failure/7 assertions; then stop.
6. **Can concurrent refunds exceed refundable value?** Reservations can:
   native overlapping transactions reserved 7000+7000 against 10000 with an earlier
   RR consistent-read view. Actual provider refunds/finalization were not performed.
7. **Refund finalization twice?** Prepared finalizer/replay creates one principal
   effect; expanded stale-worker/rollback variations remain untested.
8. **Double refund release?** Not established on MySQL.
9. **Excess payable reservations?** Prepared fresh-view cap/replay passed.
   Stale-view capacity is source-related but untested after refund failure.
10. **Double payout-cancellation release?** Not established on MySQL.
11. **External payout without rail?** Both independent connections rejected it;
    no vendor-settlement effect and operation still RESERVED. No overlapping
    external-finalization race or real rail is claimed.
12. **Receivable settled twice?** Prepared receipt/replay creates one evidence
    row/effect. Different-operation receipt/stale-cap/partial cases remain untested.
13. **Wallet debit overspend?** Not established on MySQL.
14. **Concurrent transfer conservation?** Not established on MySQL.
15. **Duplicate provider attempt?** Original same-event contention/replay retains
    one attempt/process identity; global provider/reference duplicate SQL rejected.
16. **Double dispatch claim?** Prepared claim contention/replay admits one winner,
    then false for the loser. No actual provider dispatch or claim-rollback expansion.
17. **UNKNOWN replacement identity?** Retained same-identity design remains;
    MySQL recovery race untested.
18. **Callback/polling duplicate funding?** Not established on MySQL.
19. **Duplicate commission?** Core confirmation and prepared provider finalization
    keep two base effect rows. Callback/polling/replacement expansions remain unrun.
20. **Duplicate Vendor payable?** Prepared confirmation/provider finalization
    retains once-only base effects and core payable 9000. Replacement/stale expansions
    remain untested; excess reservations are not a duplicated payable ledger effect.
21. **Replacement Transaction duplication?** Not established on MySQL.
22. **Duplicate allocation?** Original independent-connection creation/replay
    passed with exactly one retained allocation.
23. **Duplicate contribution confirmation?** Original core confirmation/replay
    passed with one context and two base ledger effects, payable 9000.
24. **Duplicate fulfillment settlement?** Not established on MySQL.
25. **Stale models bypass locks?** Ordinary stale PHP objects are not proved
    exploitable. An earlier RR read view demonstrably defeats capacity despite
    a current allocation row lock/version; authority freshness is not certified.
26. **Deadlocks observed?** No intentional deadlock was established. Five core
    cases accept native 1205/1213 contention loss; they do not distinguish/certify
    a deadlock scenario. Expanded deadlock work stopped at refund invariant failure.
27. **Safe deadlock handling?** Untested, not certified.
28. **Lock-wait timeout tested?** Yes, original five use one-second independent
    contenders and assert native 1205/1213 before successful post-commit replay.
    Dedicated timeout/deadlock injection and broader rollback tests remain unrun.
29. **Post-rollback retry idempotent?** Prepared contenders safely replay after
    losing transaction/owner commit, without duplicate identity/effects; expanded
    failure injection and stale-view retries remain unrun.
30. **Nullable unique boundaries correct?** Provider/reference exact uniqueness
    and legitimate distinct-event NULL inserts pass. All nullable financial
    boundaries are not certified; no nullable-uniqueness defect is claimed.
31. **Retained-evidence FKs correct?** Receipt-root restrictive FK and nonempty
    accounting rollback refusal passed in guard tests. Full provider/revision/
    operation/receipt-evidence retention suite remains untested on MySQL.
32. **Exact integer money?** Existing BIGINT/integer source design unchanged;
    MySQL range/cast/SUM behavior untested.
33. **Financial meaning changed?** No; no assertion weakened.
34. **Migrations required/applied?** No new/edited migration or manifest and none
    run on owned development. Approved MySQL name-only helper correction is
    implemented; original disposable completion migration down/up passed.
35. **Existing development money changed?** No; all 59 protected tables match.
36. **Four evidence tables unchanged?** Yes, each 0→0 with matching digest.
37. **Twelve legacy Orders unverified?** Yes, all twelve.
38. **Tests/assertions passed?** Latest candidate: MySQL cap 1/10 passes and
    independent allocation 1/5 fails; candidate withdrawn, no current containment.
    Historical naming MySQL 2/28 + SQLite 2/28; original
    MySQL 13/51 pass; expanded MySQL 1/7 fails. MySQL: 15 passes, 1 failure,
    86 assertions. Both engines: 17 passes, 1 failure, 114 assertions.
    Prior JSON/SQLite regression/receipt-guard counts are historical, not added.
39. **Selected regressions rerun?** Not in the latest stopped candidate increment.
    Historical SQLite name/DDL/down-up/insert tests 2/28;
    exact original thirteen MySQL cases from case one. Previous JSON and SQLite
    50/319 remain separately recorded; no broad hardening rerun.
40. **MySQL financial concurrency certified?** No. Candidate corrects the tested
    cap but violates independent-allocation progress and is withdrawn. Original thirteen pass in
    bounded scope, but expanded native refund capacity fails with stale RR view.
41. **Uncertified scope?** Established-snapshot reservation authority and all
    remaining expanded refund/payable/Wallet/provider/fulfillment/rotation/
    stale-model/rollback/FK/money/deadlock scenarios; actual production config/load.
42. **Blocks controlled provider UAT?** Yes for a MySQL-backed application until
    reservation authority and relevant expanded financial boundaries pass.
    SQLite/provider UAT is separately bounded and not approved by this phase.
43. **Blocks production activation?** Yes; reproduced reservation-capacity failure,
    actual intended engine/configuration and provider certification are unresolved.
44. **Single safest next step?** Agree a revised bounded-current-authority design
    addressing absent-row gap interference before another implementation. No
    schema/materialized authority, broader boundary or isolation change is authorized.

**STOP — current-read candidate withdrawn after independent-allocation failure; revised scope/design required.** No activation, credentials,
provider UAT, external payouts, FX, production work or publishing.