# AgendaAlly production database architecture: PostgreSQL versus MySQL

2026-10-03. **READ-ONLY ARCHITECTURE COMPARISON. PHASE 3C REMAINS STOPPED.**

Instruction:
`attached_assets/Pasted-Do-not-implement-Strategy-G-or-any-other-Phase-3C-corre_1791079557003.txt`.

Subsequent proof-only approval and execution are recorded in
[the disposable two-engine proof report](payment-engine-proof-comparison.md).
Both reservation candidates passed the expanded conservation/progress groups; both
then stopped at a new canonical confirmation-replay failure. This earlier document
remains the pre-proof static assessment; do not interpret its “not executed” statements
as the later proof's status.

## 1. Decision

**Recommendation C: insufficient evidence—native PostgreSQL proof is required before
choosing the production engine.** Do not adopt Strategy G on the strength of the
earlier comparison. That recommendation was conditional on preserving arbitrary
already-established MySQL RR snapshots, not evidence that AgendaAlly needs that
application contract.

The current caller trace finds **one application call site**, the standalone
financial-operations POST controller. It does not own an outer transaction. Its
authorization reads occur before the reservation's root transaction. No current
application workflow was found that requires reservation success inside an older
transaction snapshot. Tests deliberately exercise this supported service composition;
they do not establish a product requirement to retain it indefinitely.

Separate three decisions:

1. **Engine:** undecided pending PostgreSQL proof, complete native migration proof,
   application parity and an operational hosting/backup decision. Neither the
   historical MySQL 13/51 pass nor PostgreSQL's lack of InnoDB gaps is sufficient.
2. **Transaction architecture:** prefer an explicitly owned financial unit, with
   parent-allocation locking before authority reads, replay before capacity, and
   retries of the whole unit. MySQL Strategy B is the smaller RR-preserving option.
   Strategy C is the more directly portable RC pattern on either engine. Changing
   that contract or isolation still requires approval. Do not add permanent membership
   metadata simply to preserve a hypothetical nested caller.
3. **Payments/providers:** unchanged and not certified. A database choice does not
   approve provider activation, external payout, live refund execution or legacy
   classification. Existing stopped financial scope remains stopped.

No PostgreSQL server was started or connected to. No new financial suite ran.
No application, migration, schema, test, financial record, configuration, workflow,
credential or provider was modified. Document and project-memory updates only.

## 2. Scope and evidence standard

This comparison concerns `.migration-backup/backend`, AgendaAlly's native Laravel
backend, not the separate Express/Drizzle artifact. Laravel remains the application
under comparison; it is not replaced by the workspace's PostgreSQL infrastructure.

Read-only work included:

- Static inventory of all **224 native migration files**, raw/query-builder SQL in
  application services/repositories/controllers, driver branches and transaction use.
- Exact reservation service/controller/route/UI caller trace, middleware/bootstrap
  transaction checks, relevant fixtures and installed Laravel database grammar/retry
  source. No installer, migration runner, application bootstrap or financial command
  was executed.
- The Phase 3C report, prior ten-strategy design comparison, candidate diff, native
  schema/JSON/stale-snapshot/independent-allocation receipts and JUnit summaries.
- Official PostgreSQL and MySQL documentation. These establish expected engine
  behavior, not a successful AgendaAlly run.
- Protected development SQLite fingerprint reads with `PRAGMA query_only=ON`;
  no production access. The current snapshot equals the original baseline.

Labels used below:

- **V:** verified in retained native execution, with its original scope.
- **S:** established by current source/installed framework inspection.
- **E:** expected from engine documentation plus source; native execution still needed.
- **U:** unresolved until native execution or separately authorized deployment discovery.

Production MySQL version, isolation, collation, logging and configuration are **U**.
The retained test engine was **MySQL 8.0.42/InnoDB/RR**, not production.
For a future PostgreSQL proof, use a supported stable major such as **18** and record
the exact minor/build. Official support lists 18 through November 2030; do not target
a development/beta release or infer a hosting provider's available version.

This is a bounded source comparison, not certification of every route, package,
historical migration or SQL resource. Unknowns below are explicit acceptance gates,
not assertions of compatibility.

## 3. Current compatibility inventory

Classification:

- **P:** portable unchanged in the inspected expression/contract.
- **M:** MySQL-specific handling.
- **G:** PostgreSQL-specific handling required.
- **I:** engine-independent application issue.
- **U:** unknown until native execution.

Multiple labels identify different parts of the same dependency.

| Actual dependency / location | Classification and AgendaAlly-specific assessment |
|---|---|
| Runtime/dependencies | **P/U.** Project requires PHP `^8.4`; lock pins Laravel **12.46.0**, PHPUnit **10.5.60**, Spatie permissions **6.24.0**. Installed PDO drivers include `pgsql`, `mysql`, `sqlite`, `odbc`. This removes a local driver prerequisite, not an application-compatibility proof. Sanctum/Spatie use Laravel database facilities; full package migration/runtime behavior is U. Provider SDK presence has no bearing on database readiness. No dependency upgrade is intrinsically required by this inventory. |
| `config/database.php` | **M/G/U.** Default is MySQL; MySQL specifies `utf8mb4_unicode_ci` and `strict=false`. A `pgsql` branch already exists with `public` schema and `sslmode=prefer`, but is unverified. It uses `DATABASE_URL`, unlike the explicitly isolated SQLite branch's `DB_URL`; connection scoping must not accidentally select the workspace artifact's database. Production TLS, timeout, timezone and connection policy need approval/discovery later. |
| General Blueprint migrations | **P/U.** IDs, decimals, timestamps, enum declarations, JSON/JSONB, indexes and ordinary FKs have installed PostgreSQL grammar. Enum maps to varchar + CHECK; incrementing big integer maps to `bigserial` unless explicit identity is requested. This is generated-SQL support, not proof that all 224 migrations run in their historical order. `change`, foreign-key alterations, data migrations and empty down/up need native execution. |
| Accounting DDL helper | **G, definite blocker.** `AccountingSchema::create` explicitly allows only `sqlite` and `mysql`; PostgreSQL throws before table creation. `@pk` maps to SQLite autoincrement or MySQL unsigned AUTO_INCREMENT; `@ref` handles unsigned MySQL references. A reviewed PostgreSQL identity/reference branch is required. Money/version columns remain signed, not unsigned identities. |
| Accounting migration CHECKs | **P/G/U.** Row-local state, scale, amount and decomposition checks have PostgreSQL-compatible logic; the receipt self-anchor CHECK can remain native on PostgreSQL. SQLite `typeof(...)='integer'` triggers are SQLite-only and should not be pasted into PostgreSQL. Prove integer input/coercion, overflow, NULL and each accepted equation natively. |
| Completion guards | **M/G, definite blocker.** `CompletionSchema` installs SQLite guards or MySQL guards, but no PostgreSQL guards. Removing the DDL helper rejection alone would silently leave required completion retention/immutability/binding safeguards absent. Implement reviewed PostgreSQL trigger functions/row checks before claiming equivalence; compare all accepted guard predicates, not just table existence. |
| MTN attempt-identity migration | **G/M/U.** `2026_10_03_100300_add_mtn_attempt_identity.php` rejects every non-SQLite engine. Its raw ADD COLUMN, references, trigger installation and DROP TRIGGER syntax need an engine branch. This is also a full-native-MySQL deployment limitation; the original thirteen's smaller synthetic bootstrap did not certify the whole migration chain or this extension. Do not infer MySQL MVP readiness from the subset. |
| JSON storage and application equality | **P/G/U.** Native migrations already mix `json` and `jsonb`; accounting raw DDL uses JSON. MySQL canonicalizes JSON output; PostgreSQL `json` preserves input text while `jsonb` canonicalizes and removes duplicate object keys. Existing `SemanticJson` avoids byte equality, retains container/scalar distinctions and handles exact numbers. Preserve that application contract; do not replace it with generic `jsonb =` or containment, or convert all columns to JSONB automatically. PostgreSQL JSON has no general equality operator. Duplicate keys, Unicode/NUL, number normalization and PDO representation need proof. |
| JSON mutation | **M/G.** `Traits/Notification.php:122–124` uses portable `whereJsonContains`, then raw `JSON_REMOVE/JSON_SEARCH/JSON_UNQUOTE`; the update is MySQL-specific. PostgreSQL needs a bound, semantically equivalent array-element removal. This also embeds a receiver into SQL text: parameterization is an I issue, not a reason to pick an engine. No notification code changed here. |
| Explicit/generated names | **P/M/G/U.** Explicit financial names are short enough for both engines. But the shortened `eca_provider_reference_unique` is currently selected only for MySQL; PostgreSQL receives Laravel's 65-byte default. PostgreSQL's normal limit is **63 bytes**, truncating longer identifiers; MySQL limit was 64 characters and caused 1059. Extend reviewed explicit naming to PostgreSQL, preserve columns/order/uniqueness, inspect collisions and drop/up names. Static composite-name candidates also include two 64-byte translation indexes; see §7. |
| FKs and nullable uniqueness | **P/G/U.** RESTRICT/ordinary immediate FKs and default multiple-NULL uniqueness exist in both engines. Do **not** add `NULLS NOT DISTINCT`, delete legacy duplicates or alter provider/event identities. PostgreSQL signed FK types must match referenced signed IDs; UUID/text type boundaries need deliberate handling. Parent FK locks and shared profile/actor identities still cause legitimate contention. |
| UUID/text binding guards | **G/U.** Blueprint completion UUID fields become native PostgreSQL UUID, while raw context `funding_event_key` is CHAR(36) and `configuration_revision` is VARCHAR. The existing MySQL binding trigger compares these directly. A PostgreSQL port must make those comparisons type-correct without rebinding identities or casting arbitrary legacy revision text to UUID. Canonical UUID output and any JOIN predicates also need audit/proof. |
| Collation, case and trailing-space semantics | **M/G/U.** MySQL connection specifies a case-insensitive Unicode collation; PostgreSQL ordinary text equality/LIKE are not an interchangeable policy. Native UUID equality naturally handles UUID case, but provider/reference/receipt/email/text keys have separate semantics. Define and approve equality per identity; test upper/lower case, accents, whitespace and CHAR padding. Do not add `citext` or a global insensitive collation without a policy decision. Existing LOWER-based lookups are portable expressions, not complete uniqueness guarantees. |
| IDs/sequences | **M/G/U.** Use generated IDs actually returned by the connection, never max/count/assumed contiguous sequences. PostgreSQL sequences are not rolled back; explicit fixture/import IDs do not advance their sequence automatically. BY DEFAULT identity can permit the existing explicit-ID fixtures. Prove `insertGetId`, copied-ID sequence advancement, FK compatibility and rollback gaps; UUID financial identities remain unchanged. |
| Integer money and aggregates | **P/I/U.** Accounting/operation amounts use signed BIGINT native units and frozen scale; PostgreSQL should retain those, not its `money` type or floats. PostgreSQL SUM(bigint) returns NUMERIC; MySQL exact integer SUM yields a decimal result. PHP `(int)` casts in `reserved`/refund effect sums require bounded, exact input. Overflow/representation checks are I acceptance work; changing engine does not make unchecked PHP casts safe. Empty SUM returns NULL in SQL, while Laravel's `sum` supplies zero. Native decimal/PDO and large-unit tests remain required. |
| Ordinary raw SQL | **P/U.** Version `version+1`, conditional CASE sums, COUNT/MIN/MAX, LOWER/TRIM/COALESCE and simple row-local checks are broadly portable. Actual query shape/grouping/binding still needs native proof. No blanket assertion that 384 static raw/lock-related matches are executable on PostgreSQL. |
| Reporting helper | **P/G/U.** `Helpers/PortableSql` already has PostgreSQL date, month, interval, JSON-number and Haversine branches. However `distanceKilometers` returns `ROUND(double-precision-expression,1)` through ASIN/SQRT; PostgreSQL's two-argument ROUND takes NUMERIC. A PostgreSQL-specific final cast/round adaptation is required; a driver's existence is not proof that nearby-Shop queries run. `jsonNumber` uses double precision for non-authoritative reporting, not exact financial capacity. |
| HAVING/query shape | **M/G.** `OrderReportRepository:775/851` uses aggregate aliases in HAVING and related OR HAVING branches; PostgreSQL does not resolve output aliases there like MySQL. Use equivalent aggregate expressions or an outer query. `Models/User.php:733` uses HAVING on rating filtering; review for a WHERE/equivalent grouped form rather than assuming MySQL's permissive behavior. GROUP BY aliases, functional dependencies, report joins and aliases such as `sum`/`rating` need native route coverage. |
| Diagnostic/raw administrative SQL | **M/G.** `Rest/SettingController::systemInformation` runs SHOW VARIABLES before its later try/catch. PostgreSQL needs engine-appropriate metadata retrieval. `RebaseCurrencyToXaf` uses raw backtick-quoted arithmetic at 205/214/276; change driver quoting/binding if that command is supported after port. Do not run it or rebase any values. |
| Full-text and spatial support | **P/U/G.** Auction migration enables Blueprint fullText on any non-SQLite driver; installed PostgreSQL grammar supports it. Tokenization/ranking and native execution remain U. Geography uses the application's Haversine branch, not an established PostGIS requirement. Fix the ROUND signature; do not install PostGIS merely because MySQL uses ST_Distance_Sphere. |
| SQL resources/seeding | **U/G.** `TranslationSeeder` executes `resources/lang/translations_en.sql` directly. Native import syntax, encoding and explicit-ID sequence behavior require inspection/execution in a disposable destination; no raw SQL resource was imported here. Data migrations/seeding must not alter preserved finance, country/currency/configuration values during a port. |
| Upserts/ignore | **P/M/G/U.** No direct application `upsert`/`updateOrInsert` call was found in the scanned native app/migrations. `AllocationWriter:55/134` uses `insertOrIgnore`; installed PostgreSQL compiles ON CONFLICT DO NOTHING, while MySQL INSERT IGNORE has broader warning/coercion semantics. Current lookup/immutable binding validation must be proved after a conflict, especially in RR. Future `upsert` would need declared PostgreSQL conflict targets; do not treat duplicate insert as retained replay. |
| Builder locks | **P/M/G/U.** Installed grammar maps `lockForUpdate` to FOR UPDATE and `sharedLock` to PostgreSQL FOR SHARE versus MySQL LOCK IN SHARE MODE. Syntax ports; freshness and empty-set locks do not. MySQL FORCE INDEX in the withdrawn candidate/evidence is not PostgreSQL SQL and is not installed application behavior. |
| Transactions/savepoints/retries | **P/M/G/I/U.** Laravel implements root transactions/nested savepoints, but engine abort/lock retention differs. `reserve(...,3)` can retry owned roots; nested concurrency failures are rethrown as DeadlockException, not independently retried into a fresh view. Explicit scoped isolation must be applied at each root attempt; native SQLSTATE/transaction-manager cleanup tests are required (§6). |
| Other financial paths | **I/U.** Wallet overspend/conservation, callback/finalization, receipt/effect visibility, contribution and fulfillment require their own native proofs. Source existence, accepted SQLite results and a chosen engine do not certify them. No sibling remediation is undertaken. |
| Development-only SQLite tooling | **P/U.** Bootstrap/guard commands and `scripts/development.mjs` intentionally use the owned SQLite preview; they are not a production-engine abstraction to rewrite. Retain those protections. A PostgreSQL proof harness must be separate and opt-in. |

## 4. Reassessment of all encountered Phase 3C findings

All execution results in this section are **historical**, not new tests.

| Finding | MySQL result/meaning | PostgreSQL assessment |
|---|---|---|
| Receipt self-anchor CHECK | **V:** MySQL 3818/HY000 forbids the AUTO_INCREMENT ID in this CHECK. Approved AFTER INSERT / BEFORE UPDATE triggers enforce only this invariant without changing the other checks/FKs. SQLite rejected self-anchor using its original CHECK. | **E:** PostgreSQL permits a row-local CHECK comparing the generated ID and receipt anchor. Keep `CHECK(receipt_anchor_context_id IS NULL OR receipt_anchor_context_id <> id)`; assigned default/identity is checked on insert. No MySQL-style workaround is required for this invariant. Native generated-ID, explicit-ID, UPDATE, NULL, valid different anchor, FK and rollback tests remain U. |
| Identifier length | **V:** completion bootstrap hit 1059 for the 65-character generated UNIQUE name; approved MySQL-only short name passed 2/28 on MySQL and 2/28 on SQLite, retaining unique `(provider,provider_reference)` and multiple NULL references. | **E/S:** 63-byte normal limit; the unchanged PostgreSQL branch still generates the long name. Truncation is not equivalent to the reviewed explicit naming/collision contract. Existing short name is suitable; apply a reviewed PostgreSQL name branch and metadata/down/up tests, not a uniqueness change. |
| JSON bytes/equality | **V:** MySQL returned added spaces in a synthetic JSON object, byte inequality but strict semantic equality. Approved comparator tests and regression results are retained. | **E:** JSON may preserve bytes, JSONB may not; different numeric spellings/Unicode and duplicate-key behavior remain relevant. Existing strict semantic comparison is still the application authority. Native representation and immutable replay cases are U. |
| Original thirteen financial cases | **V:** after approved guard/JSON/name corrections, 13 passed / 51 assertions / no skips or errors. Twelve involve overlapping connections; blocked external payout finalization is sequential through two connections, not an external payout race. | **U:** no AgendaAlly PostgreSQL case ran. Equivalent row/unique/FK waits and replay are plausible, but native abort semantics, guards, types and test setup must be ported. Neither “Laravel supports PG” nor “PG has row locks” certifies these cases. |
| Refund stale-snapshot over-capacity | **V:** old reader SUM=0; owner committed 7000; reader current parent version=3; second 7000 accepted; committed total 14000 against 10000, no refund effects/provider requests. This is service-level excessive durable reservation, not paid-out money or a demonstrated route incident. | **E:** in RC, subsequent reads after obtaining the parent see earlier commits plus own writes, subject to all authority writers using the mutex. In RR, current reservation updates parent version: the old reader's attempt to lock the parent changed after its snapshot should raise 40001, not return a fresh parent and child view. Abort+whole-root retry, not silent compatibility. If an authority writer only locks but does not update a conflict row, RR can still miss its committed children; PostgreSQL alone is not a general fix. |
| Rejected child SHARE strategy | **V:** candidate cap 1/10 passed, but unrelated allocation 1/5 failed; candidate withdrawn, original reads remain. | **E:** SHARE is not a PostgreSQL RR snapshot refresh. RC already has fresh statement views; additional row SHARE may be unnecessary. RR returns snapshot-visible rows or raises a changed-row serialization error; absent/new child membership remains invisible. Do not transplant a MySQL “current read” assumption. |
| Independent-allocation gap/supremum blocking | **V:** B acquired its separate parent lock but did not complete; A retained shared supremum/S,GAP on absent replay UNIQUE and counted-operation range indexes. Correct leading allocation indexes were used, reserved plan was range/key_len=220. No live wait graph/native error chain was retained, so first blocking index and 1205 versus 1213 are not verified. | **E:** RC/RR FOR SHARE locks returned tuples, not InnoDB insertion gaps/supremum; an empty result does not acquire the candidate's inhibitive range locks. Unrelated rows normally progress. Unique/FK conflicts, shared funding groups, explicit table locks, DDL or deadlocks can still block. Serializable SIRead predicate locks are a different nonblocking mechanism that can cause aborts; absence of gaps does not prove capacity conservation. |
| Nested transactions/savepoints | **S/V:** nested `reserve` uses a Laravel savepoint; root RR view survives. Savepoint rollback cannot refresh it. InnoDB may retain locks after ROLLBACK TO; a nested deadlock can invalidate the whole root. | **E/S:** savepoints preserve atomic composition and own writes but do not provide a new RR snapshot/isolation. PostgreSQL releases locks acquired after a rolled-back savepoint, but not pre-savepoint locks. Errors require rollback to a usable savepoint or root before more SQL. Laravel's nested concurrency handler needs native verification; root retry is still required for fresh authority. |
| Deadlock/lock-wait handling | **V:** fixture engine used rollback_on_timeout=0; lock timeout need not undo previous statements, while InnoDB deadlock normally rolls back its chosen transaction. Independent receipt's wrapped code is null. | **E/S:** 40P01 deadlock, 40001 serialization, 55P03 lock timeout/lock-not-available, possibly 57014 statement cancellation and 25P02 aborted-transaction follow-up. Preserve the original chain/state; do not equate them. Installed Laravel recognizes PDO 40001 and deadlock messages; it has no explicit 55P03 handling. Retry complete safe roots, not the failed SQL or provider dispatch. |
| Unrun siblings/expanded gates | **V:** exact 3000 remainder, newer refund effects, payable/receivable old-view caps, broader replay/rollback/deadlock, Wallet and fulfillment were stopped/unrun at the financial/boundary gates. | **U:** all remain unproved here. A PostgreSQL assertion rejecting an operation by serialization is only a containment outcome until its root retry and correct remainder/replay are demonstrated. |

The retained evidence stages include the initial schema-blocked setup, resumed JSON
failure, naming-blocked restart, final original 13/51 pass, expanded 1/7 cap failure,
candidate 1/10 cap pass and candidate 1/5 independent failure. Earlier interrupted
JUnit output is not a passing suite. This comparison does not relabel any of those.

## 5. Reservation caller trace: is arbitrary old-snapshot nesting required?

### 5.1 Application call chain

```text
Admin/Vendor transaction modal or pending-collection panel
  -> shared financial-operations-panel.jsx:postOperation
  -> POST /api/v1/dashboard/payment-operations/{allocation}
  -> api middleware + block.ip + auth:sanctum
  -> PaymentOperationsController::store
     -> scope / authorization reads / request validation
     -> run(callback)                         [NOT a DB transaction wrapper]
     -> FinancialOperations::reserve
        -> DB::transaction(callback, 3)        [owns root for this current caller]
        -> allocation FOR UPDATE / version CAS
        -> replay -> authority/capacity -> operation INSERT
```

`routes/api.php:20–30`, `Kernel` middleware groups, bootstrap/provider transaction
searches and controller `run:106–116` show no surrounding application transaction
on this route. No database transaction middleware or alternate reserve job/service/
listener/console call site was found. `DurableCollections::reserve` is a different
service for collection-attempt identity and is not a financial-operation caller.

The UI performs its GET/reload and later POST as separate requests; the GET's
`index` transaction is complete before POST and cannot establish POST's root view.
The shared panel is used by Admin and Seller transaction modals and collection
recovery, not three distinct backend transaction owners. Its in-memory retry key is
retained on POST failure; that is not proof of automatic root retry or durable
browser-restart idempotency.

### 5.2 All detected financial-reservation call sites

| Caller/source | Ownership, preceding work and atomicity | Parent-first / owned root / scoped RC feasibility |
|---|---|---|
| `PaymentOperationsController::store:84` — all refund, receivable, payout POSTs | **S:** no outer transaction in route/controller chain; `reserve` owns root. Before it: ordinary allocation lookup, Sanctum actor/roles/country/Shop-permission reads, request validation. No financial writes precede it. These reads do not establish a still-open RR view. No need to atomically create an Order/Booking/Wallet movement with this standalone reservation was found. | **Yes architecturally:** fresh root can begin with allocation lock; existing service already orders its internal reads that way. B guard/ownership contract or C root isolation can be introduced only after approval and proof. Authorization can be revalidated against locked scope if required; permission-revocation TOCTOU is an I policy question, not evidence that G is needed. |
| `PaymentCompletionIdentityTest`, fifteen direct financial-reserve occurrences | Fixture/funding calls complete before each reservation; no test-level outer financial transaction was found. Refund identity/replay/cap tests, provider-response parsing, unknown refund retention, receivable receipts, payout bounds and role checks call the service as its own unit. Two receipt reservations are sequential completed calls, not one required business transaction. | Fresh root/RC fits these service uses. Provider mocks are subsequent synthetic test work, not operations that must be atomically committed with reservation. Preserve all original assertions; no test ran now. |
| `PaymentCompletionContentionTest:54/60/61` setup calls | Refund-finalization, receivable and blocked-payout cases prepare a reservation before opening the owner contention transaction. | Fresh service-owned root feasible; test preparation needs no older snapshot. |
| `PaymentCompletionContentionTest:71/74` dataset action calls, also inherited by MySQL class | The **owner** intentionally opens an outer transaction to retain uncommitted locks for its barrier. Reads/setup before BEGIN are committed. The reservation action begins with parent lock; no indispensable prior financial write inside that root was identified. The **contender** normally starts a service-owned root; after owner commit it calls again in a fresh root. | Test synchronization requires an open owner, not a production old-snapshot contract. A strict root-only B implementation needs a fixture barrier inside the service-owned transaction or an explicit valid parent-first unit interface. Keep this coverage, do not simply skip owner calls or weaken assertions. C is feasible if the test-owned root starts RC. |
| `PaymentPhase3cSnapshotMySqlTest:30` owner call | Service-owned root; synthetic funding already committed; owner reserves 7000 and commits while reader stays open. | B/C feasible as a normal owned financial unit. |
| `PaymentPhase3cSnapshotMySqlTest:40` reader call | **Explicit old RR root**: BEGIN, ordinary reserved SUM=0, owner's commit of 7000, second old SUM=0, parent locking/version observation, then nested service savepoint. Preceding reads exist to reproduce the defect; no business write requiring atomic reservation was identified. | Not supported successfully by strict B/C within that existing old RR root. Rejection/root rollback+approved whole-unit retry is a changed contract, not a pass of the unchanged nested-success fixture. G would preserve this MySQL behavior at permanent schema/writer cost. No current application need found. |
| `PaymentPhase3cIndependentMySqlTest:37` Allocation A call | Test begins owner root solely to hold A's reservation open. Funding setup completed first. No prior required business write inside root. | Parent-first test root feasible; strict B would require a root-owned barrier. C can start that root RC. Do not count B's nested-call rejection as progress. |
| `PaymentPhase3cIndependentMySqlTest:46` Allocation B call | Separate connection begins root and locks B's parent before nested reserve. No other authority work precedes this lock. Test requires B completing while A remains open. | Valid parent-first root is feasible. Strict B guard needs the service-owned barrier variant; C outer root can start RC. Preserve the historical nested fixture and its failed receipt separately; prove actual success before A commits, not “retry after A.” |

The identity-test count excludes its separate `DurableCollections::reserve` call.
No other current `FinancialOperations::reserve` application/test call site was found
in native app/routes/tests, scripts or scanned API/admin consumers.

**Conclusion:** there is no demonstrated current business requirement for successful
financial reservation inside a transaction whose snapshot predates the allocation
mutex. Do not silently claim that nesting is forbidden today: the service accepts it
and the tests exercise it. An explicit ownership contract, tests for rejection/
supported parent-first composition, and whole-unit retry are required to change it.
Future workflows with genuinely inseparable preceding writes must be designed at
the complete financial-unit boundary, not solved by an independent inner commit.

## 6. Designs on both engines

“A/B/C/G” retain the earlier report's meanings. All forecasts are **E**, except
the documented current MySQL failure. None is implemented/certified here.

### 6.1 Exact timing and read sequences

**A — arbitrary nested RR, existing ordinary authority reads**

```sql
BEGIN;                       -- RR caller, both engines
SELECT SUM(amount_units) FROM payment_financial_operations WHERE ...; -- old view
-- another transaction commits reservation/effect/parent changes
SAVEPOINT reservation;
SELECT * FROM commerce_payment_allocations WHERE id=:allocation FOR UPDATE;
UPDATE commerce_payment_allocations SET version=version+1 WHERE id=:allocation AND version=:v;
-- ordinary retained-key/context/attempt/effect/reservation reads, same equations
INSERT INTO payment_financial_operations (...) VALUES (...);
-- no independent inner COMMIT
```

MySQL returns a current parent but leaves ordinary child queries on the old view:
the observed 14000 outcome. PostgreSQL RR does **not** implement MySQL's current-read
escape. Because this actual reservation writer increments the parent, the stale
reader should get 40001 while locking/updating the changed parent. That is safer
than committing 14000 for this schedule, but requires full root restart; it does
not satisfy successful old-view nested composition. If a writer only locks the
parent and commits new children without updating a common conflict row, PostgreSQL
RR can retain stale children and allow an invalid later decision. A mutex alone is
not an all-writer RR fence.

**B — fresh owned root, parent-first RR**

```sql
-- MySQL: START TRANSACTION under unchanged RR; NOT WITH CONSISTENT SNAPSHOT.
-- PostgreSQL: BEGIN ISOLATION LEVEL REPEATABLE READ.
SELECT * FROM commerce_payment_allocations WHERE id=:allocation FOR UPDATE;
-- original version CAS, retained replay, then ordinary child authority reads
-- identical capacity equations and real operation INSERT
COMMIT;
```

InnoDB's first **consistent** read follows lock acquisition/wait completion, so it
sees earlier participating commits. PostgreSQL fixes its RR snapshot at the start
of the **first non-transaction-control statement**, including this locking SELECT.
If that SELECT waits for a parent update, 40001/root retry is expected. If the owner
only locked the parent without updating it, PostgreSQL can win the lock after wait
but retain a pre-wait child view. PostgreSQL B therefore needs a demonstrated
parent-update conflict/fence for **every authority-changing writer**, or use C.
The actual reservation version increment covers reservation/reservation conflict;
it is not certification of all sibling writers.

**C — deliberately scoped RC root**

```sql
-- MySQL, BEFORE every owned root attempt:
SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
START TRANSACTION;
-- PostgreSQL, new owned root:
-- BEGIN ISOLATION LEVEL READ COMMITTED;
SELECT * FROM commerce_payment_allocations WHERE id=:allocation FOR UPDATE;
-- original version CAS
SELECT * FROM payment_financial_operations
 WHERE allocation_id=:allocation AND kind=:kind AND request_key=:request LIMIT 1;
-- ordinary context/attempt/ledger/counted-reservation reads
-- replay binding check BEFORE new-capacity evaluation; unchanged INSERT
COMMIT;
```

On both engines, each subsequent plain child SELECT sees commits before that
statement plus its transaction's own writes. All capacity/eligibility-changing
writers must use the parent mutex, making the multi-query authority stable while
held. This is why C works—not RC by itself. Empty child sets need no synthetic
financial row and no child SHARE range lock.

MySQL's one-transaction setting must be reapplied before **each retry**, not once
outside Laravel's multi-attempt loop. Active RR cannot be upgraded (1568); abandoned
one-shot setup cannot leak to unrelated connection work. PostgreSQL uses BEGIN with
isolation or SET TRANSACTION at the allowed beginning of its root; after prior data
queries it cannot repair the snapshot, and a savepoint is not a new isolation unit.
Never toggle a global/session default for the rest of AgendaAlly. MySQL RC requires
compatible row-based binlogging when enabled; current production logging is U.

**G — complete parent membership, existing-key locking reads**

```sql
SELECT * FROM commerce_payment_allocations WHERE id=:allocation FOR UPDATE;
-- proposed complete operational_evidence_manifest on this locked parent
SELECT * FROM payment_financial_operations WHERE id=:known_existing_id FOR SHARE;
-- MySQL equivalent is LOCK IN SHARE MODE
-- point-read every registered context/effect/attempt; original equations
-- real INSERT + atomic complete membership UPDATE, same transaction
```

On MySQL, current parent/known-existing unique point reads can bypass old RR views
without deliberate missing-key/range gaps, conditional on complete atomic membership
maintenance across all writers. NULL is not an empty manifest; deleted/dangling/
unregistered IDs fail closed. Retain terminal IDs for replay; no pruning or new
financial identities. Unbounded retained history adds parent JSON/write/lock costs.

On PostgreSQL RR, parent and child point locks are **not** a fresh-current-state
channel. A manifest committed after the snapshot either conflicts and causes 40001,
or is not visible under the applicable snapshot. G cannot preserve the same successful
arbitrary old-view behavior as proposed for MySQL. In PostgreSQL RC it can work with
mutex/writer discipline, but ordinary child reads already provide the needed
visibility without maintained inventory. It adds no demonstrated AgendaAlly benefit
commensurate with its maintenance risk.

### 6.2 Conservation, progress, nesting and own-write visibility

| Engine/design | Same-allocation conservation | Independent allocation progress | Existing nested callers / own writes |
|---|---|---|---|
| MySQL A | **V: fails** in reproduced old view; parent lock/version alone insufficient. | No candidate read gaps; expected distinct-parent progress, subject to real shared identities. | Accepts old nested callers and sees own writes, but misses newer other commits. Unsafe. |
| PostgreSQL A/RR | Expected 40001 for the reproduced changed parent, not a fresh-read guarantee; lock-only authority writers remain hazardous. | No InnoDB gap blocking; native proof required. | Own writes visible; successful arbitrary old-view reservation not guaranteed. Root retry required after conflict. |
| MySQL B | Expected correct for fresh parent-first roots and complete participating writer protocol. | Expected; plain children do not retain S search gaps. | Existing arbitrary roots rejected/restructured; supported declared parent-first units can nest only under an explicit contract. Own writes in that valid unit visible; unrelated caller writes cannot be silently moved to a separate connection. |
| PostgreSQL B/RR | Conditional on universal parent-update fencing and root serialization retry; fresh parent-first ordering alone differs from InnoDB. | Expected; tuple/unique/FK conflicts remain. | Same ownership constraint; own writes visible. Old caller cannot be refreshed with a savepoint. |
| MySQL C/RC | Expected with complete parent-mutex writer discipline and unchanged equations. | Expected; unique/FK checks still take legitimate locks, not the rejected S child gaps. | Compatible with a root deliberately started RC, including savepoints/own writes; incompatible with an existing queried RR root. Applies to the entire owned financial unit. |
| PostgreSQL C/RC | Expected with complete parent-mutex writer discipline and unchanged equations. | Expected; no candidate-style gap lock. | Same RC-unit composition/own-write visibility; do not change isolation late or independently commit a nested operation. |
| MySQL G/RR | Conditional on validated complete membership and correct current point reads, not merely valid JSON. | Expected if every probe is truly known-existing unique and identities are independent. | Designed to support old RR caller and own uncommitted parent/child changes in the same connection. Still requires deadlock/root-abort handling. |
| PostgreSQL G | RR does not obtain G's proposed MySQL current visibility; conflicts require root retry. RC works but duplicates unnecessary inventory. | No gap equivalent, but extra tuples/metadata still cost locks. | Own writes visible; old-view successful behavior is not preserved. Not a drop-in portable implementation of MySQL G. |

### 6.3 Replay, rollback, errors and schema

For **every** supported design:

- Retained `(allocation,kind,request_key)` lookup precedes capacity; matching immutable
  amount/actor/context returns the retained operation even after capacity is consumed.
  Changed parameters fail. UNIQUE is a backstop, not replay logic.
- RESERVED/UNKNOWN/PENDING retain authority. Refund subtracts original-context
  principal effects and counted reservations; payout subtracts reserved payable and
  excludes refund; receivable subtracts its counted reservations. Preserve all
  existing asymmetric exclusions and frozen scale/economics.
- Same-connection reads include own uncommitted data. Separate-reader designs do not,
  and are not recommended. A root rollback must undo operation, version and any
  membership/effect changes; no separate inner commit may survive caller rollback.
- Keep deterministic lock ordering for genuine multi-allocation/funding groups.
  No application retry may repeat an external provider dispatch as though it were
  pure SQL.

| Design | Replay/rollback/error implications on MySQL | On PostgreSQL | Schema |
|---|---|---|---|
| A | Old view can miss replay/effect/reservation; savepoint does not refresh it. Timeout requires rollback of earlier version writes; deadlock can destroy root. | Old view can miss new rows; changed parent should abort. 40001 needs complete root retry, not repeated SAVEPOINT reservation. Errors must not leave 25P02 state. | None; therefore not a general correction. |
| B | Fresh root sees retained replay; root deadlock retry repeats parent-first setup. Savepoints inside this valid root undo later writes but not an old external view. | RR replay/authority complete only with required fencing/retry; 40001/40P01 restart complete root. Savepoint rollback releases later-acquired locks, not earlier ones. | No financial schema/index change; ownership contract change and tests. PostgreSQL migration prerequisites still apply. |
| C | Fresh replay and authority; retry must reset one-shot RC each attempt. On 1205 do not continue a partially executed unit; enforce clean root state. | Fresh statements, replay and own writes; classify 40001/40P01/55P03 rather than reuse MySQL numeric codes. Roll back root/safe savepoint before more SQL; bounded retries and same key. | No financial schema/index change; approved per-unit isolation/helper lifecycle change. |
| G | Retained complete operation IDs permit replay; amount/binding checks and native equality preserved. Manifest + child insertion roll back together. Missing membership invalidates safety. MySQL savepoint locks may persist. | RR cannot refresh via manifest or point locks. RC still needs clean rollback and whole-unit error handling. Native UUID/JSON comparison differs. | Additive parent JSON field, initialization, complete creation/state writer protocol, metadata guards, rollout/preflight and retained-history limits; PostgreSQL guard/DDL adaptations too. |

Installed Laravel facts: `reserve` requests three attempts; the concurrency detector
recognizes PDO code 40001 and deadlock/lock-wait messages. The nested handler decrements
its transaction counter and throws `DeadlockException` with the previous exception;
it is not a successful inner savepoint retry/refresh. PostgreSQL 55P03 lock-timeout
messages are not explicitly covered. The controller converts non-Domain failures to
generic 409 without leaking SQL; client retry with the same key is possible, but is
not certification of correct root retry, state cleanup or isolation reapplication.

### 6.4 Complexity, portability and expected contention

| Option | Complexity / long-term risk | Expected workload characteristics |
|---|---|---|
| MySQL B | Smallest incremental application contract; no maintained financial metadata. Must prevent undocumented old-root callers. Less portable RR timing than C. | Same-allocation writes serialize; different allocations normally progress. Plain range sums cost history scans; indexes can optimize performance, not change correctness. Long business roots still hold parent locks. |
| PostgreSQL B | Moderate: audit parent-write fences plus serialization/root retries; cannot assume InnoDB timing. No financial schema inventory. | Contended changed parents can cause abort/retry rather than just a wait followed by fresh reads. Throughput depends on retry rate and parent hotness; no measured advantage asserted. |
| MySQL C | Moderate helper/connection-lifecycle work; smaller maintenance surface than G. Requires explicit root scoping and logging compatibility. | Stable authority under parent mutex, no S child range-gap inventory; RC does not remove unique/FK contention. Different allocations should scale without this candidate obstruction. |
| PostgreSQL C | Conventional RC pattern after migration blockers are fixed; strongest direct cross-engine architecture portability. Not proof of all sibling writers. | Parent hotspots still serialize; different parents normally progress. MVCC/vacuum/WAL, indexes, retained history and connection-pool sizing require workload measurement. |
| MySQL G | Largest lasting protocol: every relevant creator must register exact IDs; initialization/version/rollback/retention/rollout must remain correct. A forgotten writer can silently undercount. MySQL-specific old-view benefit. | O(retained child history) point reads plus potentially large JSON rewrites/WAL/binlog and longer parent-lock hold times. Batched optimization needs equivalent known-existing access proof. No throughput claim without benchmarking. |
| PostgreSQL G | Migration work plus manifest cost without RR fresh-current benefit; weak portability of the original purpose. Prefer C unless a separate real requirement emerges. | Extra metadata contention/storage and tuple reads; does not avoid RR serialization retries. |

Neither engine removes economic hot allocations, shared funding groups, global
provider/receipt uniqueness or Wallet account lock contention. “Independent progress”
is about unrelated identities, not a promise that every SQL write is wait-free.

## 7. PostgreSQL migration feasibility and concrete work

**Feasible in principle, not compatible now and not a connection-string switch.**
Known required changes/validation work, all proposed only:

1. **Accounting schema branch:** add reviewed PostgreSQL identity generation and
   matching reference types in `AccountingSchema`; retain every row-local CHECK,
   FK, original identity and exact BIGINT amount. A possible native definition is:

   ```sql
   id BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
   receipt_anchor_context_id BIGINT,
   CHECK(receipt_anchor_context_id IS NULL OR receipt_anchor_context_id <> id)
   -- retain the existing anchor FK and other table constraints
   ```

   This is illustrative DDL, not a migration. PostgreSQL can use this CHECK rather
   than MySQL receipt triggers (**E**, not executed). An ID sequence is not monetary
   evidence; generated-ID gaps after failure/rollback are legitimate.

2. **Completion guards and naming:** a PostgreSQL `CompletionSchema` branch for
   immutable revisions/receipts, retained attempts/operations, original bindings,
   terminal/lifecycle constraints and profile/revision relationships required by the
   accepted contract. Use row-local CHECK where sufficient, PL/pgSQL trigger
   functions for OLD/NEW immutability and cross-row bindings. NULL-safe comparisons
   use `IS DISTINCT FROM`, not MySQL `<=>`/SIGNAL or SQLite RAISE. JSON comparisons
   must preserve strict semantic evidence; no generic JSON equality shortcut.
   Resolve native UUID versus context CHAR/VARCHAR comparisons explicitly.
   An empty down/up must remove trigger functions/dependencies safely and refuse
   destructive rollback when retained evidence exists.

3. **MTN extension:** separately reviewed PostgreSQL branch in the existing
   SQLite-only attempt-identity migration, equivalent binding/lifecycle/retention
   safeguards and down syntax (`DROP TRIGGER ... ON ...`, function dependencies).
   Preflight duplicate legacy IDs without modifying/classifying them. MySQL full
   native migration also needs its own approved counterpart; no extra MySQL fix here.

4. **Names:** `eca_provider_reference_unique` is short enough for PostgreSQL and
   must retain exactly `(provider,provider_reference)`, unique status and nullability.
   Other explicit financial names (`financial_operation_request_unique`,
   `financial_operation_external_unique`, `pcc_allocation_idx`,
   `pfle_allocation_idx`, etc.) fit the standard limits. Extend the driver condition
   so PostgreSQL actually receives reviewed names. Static unnamed composite
   candidates needing explicit-name/collision review:
   - `2023_10_17_120324_create_master_disabled_times_table.php`:
     `master_disabled_time_translations_disabled_time_id_locale_unique` (64 bytes).
   - `2024_02_16_032351_create_service_master_notifications_table.php`:
     `service_master_notification_translations_notify_id_locale_unique` (64 bytes).
   This static candidate list is not a complete compiled-schema name certification.

5. **Definite application SQL changes:** PostgreSQL equivalents for notification
   JSON element removal; engine-neutral/driver-aware system-information query;
   proper quoting in the dormant currency-rebase command; Haversine final ROUND
   type; report HAVING aggregate aliases and User rating HAVING shape. Do not change
   underlying prices/rates/report meaning to make SQL pass. Search actual PostgreSQL
   error traces for further cast/group/column issues after permission to execute.

6. **All migrations/data/seed resources:** run the full ordered chain against an
   empty isolated PostgreSQL database, not only financial table fixtures. Review
   unsigned-range expectations, FK types/targets, enum/check behavior, fulltext
   definitions, alter/change/drop operations and raw translation import. Confirm
   explicitly seeded/copied IDs leave sequences safe for subsequent writes.
   No blanket rewrite of all JSON or enum fields is presently justified.

7. **Financial transaction architecture:** approve owned roots and B/C semantics
   separately. For PostgreSQL prefer C; B/RR requires all-writer parent fencing and
   native serialization recovery. Retain own-write visibility, replay and exact
   balances. Do not add G as a prerequisite to the engine port.

8. **Tests/harness:** MySQL subclasses currently use MySQL driver/config, SHOW
   TABLES/metadata, CONNECTION_ID, session InnoDB timeout, performance_schema locks
   and MySQL error codes. PostgreSQL needs a separate opt-in adapter with schema
   catalog checks, pg_backend_pid, pg_stat_activity/pg_locks/pg_blocking_pids,
   transaction-local lock timeouts and SQLSTATE assertions. Existing SQLite/MySQL
   cases and their receipts remain unchanged. Test helpers must not treat every
   non-SQLite connection as MySQL.

9. **Approved later operational work:** native production configuration, TLS,
   pool/connection lifecycle, isolation policy, backup/restore/PITR and restore
   drills, schema deploy ownership, log/error monitoring and capacity sizing.
   Deployment hosting/support is unknown and was not accessed. Any later copying
   of real/development data needs independent approval, logical-value reconciliation,
   sequence advancement and rollback planning. Cross-engine catalog/PDO serialization
   will differ; compare reviewed logical invariants, not pretend a PostgreSQL schema
   must have SQLite's identical 465 catalog objects. The current baseline stays exact.

### 7.1 Relative cost/risk before MVP

Indicative engineer-day ranges, **estimates rather than executed work or delivery
commitments**; assume an experienced Laravel/database engineer, isolated fixtures,
no live cutover and no undisclosed migration failures. Workstreams overlap; totals
should not be mechanically added.

| Direction | Bounded estimated work | Main retained risk |
|---|---|---|
| PostgreSQL feasibility/certification spike only | Roughly **5–10 days**: core DDL/guards adapter, native financial harness, thirteen cases + highest-priority expanded gates and blocker receipts. Full suite/sibling findings can increase this. | Can falsify compatibility cheaply before committing; is not a complete MVP port and does not authorize corrections surfaced by proof. |
| PostgreSQL native MVP port and verification | Roughly **15–30+ days**: core migration/guard work, full historical schema/seed chain, SQL/identity/type adaptations, engine-level financial proof, authenticated application smoke/parity and operational restore rehearsal. | Additional migrations/data semantics are U; changing engine and transaction contract together makes regressions harder to attribute. Pre-MVP reduces live-cutover exposure but does not eliminate protected legacy/identity and correctness obligations. |
| Continue MySQL with B | Roughly **2–4 days** for a bounded reservation ownership change and its relevant native tests after approval; full MVP native migration/sibling certification is additional. | Lowest correction surface; active-root callers must be explicitly rejected or restructured. Must prove both invariants and not advertise the original thirteen as complete financial readiness. |
| Continue MySQL with C | Roughly **3–5 days** for scoped isolation/retry lifecycle plus native relevant gates; full MVP readiness remains additional. | Smaller long-term authority maintenance than G, but configuration/lifecycle/root-retry correctness and binary-log compatibility need proof. |
| Continue MySQL with G | Roughly **6–12+ days** for manifest protocol/rollout/writer inventory, validation and contention proof, with ongoing maintenance cost. | Completeness, retained-history growth and every future writer become permanent correctness dependencies. Not justified by the current caller trace. |

MySQL B/C preserve more existing migration/source work than a PostgreSQL adoption.
PostgreSQL adoption now avoids a later live-engine conversion if it proves superior,
but no AgendaAlly throughput/reliability result currently establishes that superiority.
G is not the necessary price of continuing MySQL. Conversely, MySQL's full native
schema/financial readiness is not already complete just because it has more receipts.

## 8. Proposed native PostgreSQL certification suite — NOT EXECUTED

### 8.1 Preconditions and proof structure

Only after explicit authorization:

1. A dedicated empty loopback/disposable PostgreSQL database/account/schema; explicit
   opt-in and checked empty destination. No production URL, workspace database
   fallback or protected SQLite connection. No provider credentials/activation.
2. Record native version/build, isolation, encoding/collation, timezone, timeout
   settings, Laravel/PHP/PDO versions and distinct backend PIDs. Start with a
   supported stable release; fail, do not skip, on wrong engine/unavailable fixtures.
3. First prove DDL/guard/name/FK/type/JSON equivalence with mutation-rejection tests
   in synthetic tables. Then the full native migration/seed chain separately.
   A smaller financial bootstrap is not the full application schema certificate.
4. Independent real PDO connections/processes and deterministic barriers. Capture
   live waits/queries/exception chains **before rollback**, not just surviving locks.
   Trace 40001, 40P01, 55P03 and transaction state without secrets/documents.
5. Run a **current-source negative-control** for the old RR scenario after only
   approved compatibility prerequisites; distinguish parent serialization abort from
   14000 acceptance and from capacity rejection. Then test the separately approved
   owned-root architecture. No plan assumes compatibility prerequisites alone fix money.
6. Preserve original thirteen financial datasets, amounts, identity/replay bounds and
   assertions. The originals remain untouched. Their MySQL setup/message checks
   (`locked|lock wait|deadlock`) are not native-PG compatible with 55P03's “lock timeout”.
   A proposed PG adapter/mirror must explicitly adapt **only engine setup/metadata/
   error classification**, retaining financial assertions and recording this difference.
   Do not claim byte-identical original test bodies passed if they did not.
   If literal unchanged bodies are required, report the harness incompatibility
   rather than fake a MySQL exception or weaken a financial assertion.

### 8.2 Original thirteen case inventory

These are unchanged financial scenarios, not thirteen newly invented equivalents:

| # | Existing dataset | Required retained invariant on PostgreSQL |
|---|---|---|
| 1 | economic_creation | Competing same obligation creates one immutable allocation; replay returns it. |
| 2 | contribution | One allocation/funding-slot/event context; no duplicate contribution after contention/replay. |
| 3 | confirmation | One confirmation and once-only base commission/payable effects. |
| 4 | refund | One original synthetic refund/reversal effect group; preserve original money and group identity. |
| 5 | vendor_settlement | One internal synthetic settlement effect group; not an external payout rail. |
| 6 | attempt | Durable original collection attempt identity once, replay same attempt. |
| 7 | claim | One dispatch claim; contender/replay cannot claim twice. |
| 8 | provider_finalization | Once-only original contribution/effects and terminal original attempt. Synthetic responses only. |
| 9 | refund_reservation | Original cap/count assertion and distinct-request contention/replay behavior unchanged. |
| 10 | refund_finalization | Original verified refund becomes terminal once; principal effect once, no ambiguous redispatch. |
| 11 | receivable | Retained verified unique receipt/settlement once; no duplicate collection/effect. |
| 12 | payable_reservation | Original Vendor-liability limit and competing reservation assertion unchanged. |
| 13 | payout_finalization_blocked | External payout remains blocked via both connections; no fabricated SUCCESS or settlement. This original case is sequential, not an overlapping payout race. |

The expected lock-loss exception is evidence of contention, not by itself successful
financial conservation. Require post-owner-commit replay/effect/count assertions and
clean usable connections. Record all tests/skips/errors and distinguish engine adapter
changes from unchanged financial outcomes.

### 8.3 Expanded acceptance matrix

| Case | Barrier/action and required proof |
|---|---|
| 10000 / 7000 + 7000 | Establish reader view, commit owner's 7000 while reader is open, attempt second 7000. Test PG RC and explicit RR separately. Under RR expect changed-parent 40001 where applicable, then whole-root retry and correct rejection; never total >10000. Do not count a caught error without rollback/usable-root/retry assertions as certification. |
| Exact 3000 remainder | After owner's 7000 and clean approved root retry, 3000 succeeds, 3001 fails, final counted authority exactly 10000. If preserving a nested-old-view contract is requested, test its success explicitly; B/C cannot claim that guarantee by changing the assertion. |
| Independent A/B progress | Initially empty operations, distinct parents/request identities; A valid reservation stays uncommitted at an internal test-only barrier, B valid root reservation completes **before A commits**. Use service-owned root barrier for B/strict ownership; retain historical nested fixture separately. No rejection-as-progress, sequential-after-A substitute or timeout masking. Include sparse/populated indexes, shared compatible profile/actor FKs, and reversed order. |
| Refund effect visibility | Commit a refund-principal effect after reader view; new reservation subtracts it, preserves original context/identity and balance/effect equations. Test existing-row changes/new inserts, UNKNOWN/PENDING held authority and finalization atomicity. |
| Vendor-payable reservations | Competing requests against exact payable; newer reservation/settlement/refund effects visible under approved unit; unchanged exclusion policy, exact remainder and zero over-liability. |
| Commission-receivable reservations | Offline/vendor-direct authority, competing receipts/reservations and new effects; exact cap, held-funds-first policy and unchanged asymmetric exclusions. |
| Replay/idempotency | Same key+bindings returns same operation before capacity after exhaustion/terminal state; changed amount, actor, context rejected. Own uncommitted key replay in an allowed unit; concurrent same key, NULL/non-NULL external reference and case/UUID policy. UNIQUE collision is not automatically replay. |
| Rollback/savepoint | Root rollback restores operation/version/effects; allowed unit savepoint rollback preserves earlier own writes and removes later ones. Capture released/retained locks and Laravel transaction level vs PDO state. Arbitrary old RR callers receive documented contract outcome, never an independent committed reservation. |
| Deadlock/serialization retry | Deterministic opposite-lock-order deadlock and actual RR parent-update 40001; bounded whole-root retry, unchanged key/bindings, no partial data/effects, fresh authority. Force each retry attempt to prove intended isolation. Lock timeout cleanup and exhausted retry must fail explicitly. |
| Wallet conservation/overspend | Two debits individually valid but jointly excessive; one funded balance, exact units. Transfers lock accounts deterministically, preserve combined value, once-only history and terminal state; rollback/replay/old ORM model tests. No assumption that allocation mutex protects Wallet. |
| Contribution/finalization | Duplicate callback/result/confirmation across connections, original amount/currency/provider/revision evidence, once-only funding/effects and full economics. Partial funding, updated states, failures/ambiguous pending and caller own writes; no real callbacks/provider calls. |
| Fulfillment finality | Concurrent valid Product/Booking settlement transitions and repeated terminal commands cannot duplicate payable/effect release or regress finality. Test accepted native state/commission policy rather than invent a new fulfillment rule. Synthetic legacy-unverified rows remain fail-closed; never classify the protected 12 Orders. |
| Full schema/application parity | Full empty migration chain and approved synthetic seeds; names/FKs/guards/catalog, ID sequence next writes, large integers, JSON type/container/number preservation, nullable references, collation policy, fulltext/nearby shops/HAVING/system info and authorized finance routes. No payment/provider-readiness inference from route success. |

A future approved run should stop and report on a new invariant failure, material
guard/identity deviation, broken independent-progress boundary or required correction
outside its scope. Do not repair siblings or switch isolation/test assertions to
hide a failure. If an existing failure gate prevents the rest, mark it unrun and
seek scope clarification rather than claim the full suite certified.

### 8.4 Decision acceptance gates

To choose PostgreSQL, require native schema/guard + thirteen-case parity, all expanded
critical monetary boundaries, actual financial-route compatibility and a credible
operational restore plan. Compare MySQL **B or C**, not only the rejected child-lock
candidate or G, under equivalent approved business-unit semantics and fixture load.
Measure independent completion, parent-lock duration, p95/p99 latency, retry/timeout
rate, retained-history read/write cost and resource use. No benchmark executed here.

An engine may show fewer blocking gaps but more serialization retries; count completed
correct financial units, not merely “no deadlock” or fastest isolated SELECT.

## 9. Protection, final recommendation and STOP

The read-only development snapshot matches
`.local/payment-phase3c-protected-before.json` exactly:

- **59 protected tables**: row counts and exact stored-value fingerprints preserved.
- **465 schema objects**: complete catalog fingerprint preserved.
- Four completion evidence tables remain empty:
  `payment_merchant_revisions`, `electronic_collection_attempts`,
  `payment_financial_operations`, `payment_receipt_evidence`.
- **12/12 legacy Orders remain unverified**.
- Existing legacy ledger evidence is preserved; “four empty evidence tables” does
  not mean every ledger table is empty.

**Choose C—native PostgreSQL proof required before choosing the production engine.**
The current implementation has explicit PostgreSQL blockers and no PostgreSQL
financial execution evidence. MySQL has meaningful historical evidence but also
the reproduced service defect and incomplete full-native readiness.

Prefer approving a bounded engine-proof/transaction-ownership decision before a
maintained-authority schema. For current AgendaAlly callers, B/C are credible smaller
alternatives to G. This conclusion supersedes treating the earlier *conditional*
retained-RR G recommendation as the production architecture recommendation.

**STOP.** No engine conversion, migration, Strategy G, B/C transaction change or
financial correction is approved or implemented. No provider/credential activity,
real financial operation, production access, legacy modification or publishing.

## 10. References

### Project evidence

- [Phase 3C native MySQL receipts/status](payment-phase3c-mysql-concurrency.md).
- [Earlier conditional reservation design comparison](payment-phase3c-reservation-design-comparison.md).
- `.local/payment-phase3c-schema-diagnostics.json`.
- `.local/payment-phase3c-json-representation.json` and JSON/regression JUnit receipts.
- `.local/payment-phase3c-index-{mysql,sqlite,restarted-prepared,expanded}-junit.xml`.
- `.local/payment-phase3c-stale-snapshot-evidence.json`.
- `.local/payment-phase3c-reservation-current-read-candidate.diff`.
- `.local/payment-phase3c-reservation-{current-read,independent}-{evidence.json,junit.xml}`.
- Current native service/controller/route/fixture paths identified above; installed
  Laravel `ManagesTransactions`, `ConcurrencyErrorDetector`, PostgreSQL query/schema
  grammars. Source/framework behavior is not labeled native certification.

### Official engine documentation consulted

- [PostgreSQL transaction isolation](https://www.postgresql.org/docs/18/transaction-iso.html).
- [Application-level consistency and snapshot/lock timing](https://www.postgresql.org/docs/18/applevel-consistency.html).
- [Explicit locks, deadlocks and savepoints](https://www.postgresql.org/docs/18/explicit-locking.html).
- [Serialization retry handling](https://www.postgresql.org/docs/18/mvcc-serialization-failure-handling.html).
- [CHECK/FK/nullable UNIQUE constraints](https://www.postgresql.org/docs/18/ddl-constraints.html).
- [Identity columns](https://www.postgresql.org/docs/18/ddl-identity-columns.html).
- [Identifier limits](https://www.postgresql.org/docs/18/sql-syntax-lexical.html).
- [JSON/JSONB representation](https://www.postgresql.org/docs/18/datatype-json.html).
- [Aggregate types and empty SUM](https://www.postgresql.org/docs/18/functions-aggregate.html).
- [Math functions and ROUND signatures](https://www.postgresql.org/docs/18/functions-math.html).
- [Supported release policy](https://www.postgresql.org/support/versioning/).
- [InnoDB isolation/current versus consistent reads](https://dev.mysql.com/doc/refman/8.0/en/innodb-transaction-isolation-levels.html).
- [MySQL CHECK restrictions](https://dev.mysql.com/doc/refman/8.0/en/create-table-check-constraints.html).