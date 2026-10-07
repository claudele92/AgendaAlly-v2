# AgendaAlly — disposable MySQL Strategy B versus PostgreSQL RC proof

## Current authority — MySQL MVP decision

MySQL 8/InnoDB is selected for the MVP. PostgreSQL is evaluated and technically
viable for tested invariants, but deferred for insufficient incremental MVP
benefit; no further PG work was performed. Strategy G remains deferred.

The permanent source owned-RR entry and equivalent MTN/completion MySQL DDL are
now verified: fresh original **13/51**, native core **20 groups**, Wallet
**28 groups**, complete **224** source migrations and empty tail **8 down/8 up**
PASS. Native stale-model/replacement-Transaction source tests also pass.
This is tested application financial correctness, not production certification.

See [the authoritative MVP readiness report](agendaally-mvp-readiness.md) for
exact receipts, scope limitations, provider classification and the one remaining
P0/P1/P2/P3 matrix. Owned **59/465** match; **12 legacy Orders remain unverified**.
The following cross-engine records are preserved historical evidence, including
their earlier no-selection/unported-MTN conclusions, now superseded.

## Historical decision — Wallet containment and exercised gates

2026-10-04. The Wallet failure below has been separately authorized and contained.
MySQL RR and PostgreSQL RC independently pass **28/28 native Wallet groups**,
including real deadlock/root retry, terminal replay and canonical rollback.
Both pass native fulfillment contention/replay and the exercised accounting,
confirmation, reservation, timeout and negative/lifecycle checks. PostgreSQL's
original-thirteen mirror passes 13/51.

**Neither full application engine is certified.** The actual unchanged MTN
migration still rejects both native drivers; PostgreSQL permanent schema/SQL
compatibility is also unfinished. No new independent source-confirmed financial
P0 appeared. No permanent engine port, owned migration or production selection
was made. The conditional post-certification application expansion was not entered.

Current evidence, split/interrupted receipt qualifications, exact balances,
preservation comparison and all 56 required answers:
[wallet-p0-engine-certification.md](wallet-p0-engine-certification.md).
Historical reports and original failure evidence below remain intact.

## Historical decision — narrow confirmation correction and rerun

2026-10-03, America/Chicago. **Confirmation corrected. BOTH ENGINES NOT CERTIFIED.
Both proofs STOPPED at a new independent Wallet overspend failure.**

Authorization: `attached_assets/Pasted-AgendaAlly-Payment-Engine-Concurrency-Certification-Cor_1791083410620.txt`.
This section supersedes the earlier confirmation failure and proof-only source
restriction below. The earlier report and evidence remain historical, not rerun passes.

### Required final report — all 24 fields

1. **Exact root cause:** `AllocationWriter::confirm()` materialized contribution
   contexts before its parent mutex. A waiting contender subsequently obtained
   the current finalized parent but reused its pending PHP context, incorrectly
   raising `Terminal allocation/contribution cannot accept funding.` The pre-edit
   trace and red receipts are in `correction-evidence/root-cause.md` and
   `sqlite-before*` under `.local/payment-engine-proof/`.
2. **Exact production source changed:** only
   `.migration-backup/backend/app/Services/PaymentAccounting/AllocationWriter.php`.
   The new retained test is
   `.migration-backup/backend/tests/Hardening/PaymentConfirmationReplayTest.php`.
   No other payment production source, migration, manifest or configuration changed.
3. **Post-lock authority:** YES. Routing discovery occurs before the owned root;
   inside the transaction, sorted parents are locked first, then each known
   context primary key is re-read with a portable current/locking read. Changed
   original routing/bindings are rejected. No engine-specific financial branch,
   child authority range scan, object-refresh timing or production sleep was added.
4. **Idempotent success:** the current context must already be confirmed; retain
   the original contribution/group, amount, frozen currency/scale, checkout and
   payer, provider/configuration/funding bindings, receipt namespace/anchor,
   confirmed slot and collector. For finalized allocations, frozen original
   shares and each retained unique base commission/Vendor-payable effect must
   match its original amount, currency, payee and policy. Missing/incompatible
   effects fail closed; none are repaired or recreated. Existing funding
   equations, custody, identity and commission semantics remain unchanged.
5. **Different receipt:** DENIED on both native engines after the same observed
   parent wait and post-lock reload. Retained allocation, contexts, effects,
   balances and versions are identical to the owner's committed snapshot.
6. **Focused test count:** SQLite **11 tests / 72 assertions PASS**. Before the
   edit, four expected red cases reproduced stale confirmation and specified
   replay-predicate gaps. Native focused workers separately pass **two scenarios
   per engine** (same/different receipt); CLI groups are not PHPUnit assertions.
7. **SQLite regression:** focused **11/72 PASS**, unchanged corresponding local
   completion contention **8/21 PASS**. No skip counted as success. A preliminary
   30-second interrupted contention invocation is retained but not counted;
   its subsequent completed invocation is the passing receipt. SQLite alone
   does not certify production.
8. **MySQL failed-case-first rerun:** PASS on real **8.0.42/InnoDB/RR** workers.
   Contender discovery captured pending, native parent wait was observed,
   current post-lock reload captured confirmed, both returned successfully.
   Same-receipt replay produced one contribution and two original base effects,
   with unchanged 10000 funding, 1000 commission and 9000 Vendor payable.
9. **MySQL complete matrix:** STOPPED, not fully passed. All **13 expanded
   reservation/conservation groups** were freshly rerun and passed, as did both
   confirmation races. The **unchanged original thirteen: 13 tests / 51 assertions
   PASS**, with zero failures/errors/skips. Native guard/type/index/JSON assessment
   and raw-expression assessment completed. Wallet failed; fulfillment was not run.
10. **PostgreSQL failed-case-first rerun:** PASS on real **16.15/READ COMMITTED**
    workers with the same pending/wait/confirmed sequence and exact unchanged
    owner-committed money/effect snapshots. Different receipt remains denied.
11. **PostgreSQL complete matrix:** STOPPED, not fully passed. All **13 expanded
    groups** and both confirmation races freshly passed. Guard/type/index/JSON,
    raw-expression assessment and the native serialization diagnostic completed.
    Wallet failed. Fulfillment and the previously unrun original-thirteen
    PostgreSQL semantic mirror remain **NOT RUN**, not implicit passes.
12. **Wallet compatibility:** **FAIL on BOTH engines.** Two separate concurrent
    real Wallet send actions each transfer 7000 USD minor units from an original
    sender balance of 10000. The owner pauses after its native debit, the
    contender waits on the actual Wallet row, then both commit and return
    HTTP 200. Before balances: **[10000,3000,8000]**; after:
    **[-4000,17000,8000]**. Four paid histories and four paid transactions exist.
    The recipient gained 14000 against only 10000 available sender funds.
    Source action/service/observer are real; authentication and unrelated response
    serialization are isolated fixtures, not end-to-end HTTP authentication proof.
13. **Fulfillment compatibility:** **NOT RUN on either engine after the new
    financial STOP.** No claim of native first-batch, replay, concurrency,
    Wallet/fee/points/referral finality certification is made.
14. **Other previously unrun gates:** native assessment checks self-anchor denial,
    merchant/attempt immutability, nullable UNIQUE, FK rejection, exact JSON
    number/container/order semantics and explicit index names. This is an
    assessment subset, **not complete negative-bootstrap/migration parity**.
    The actual unchanged MTN migration still refuses both native engines pending
    a separately verified port; no whole-application bootstrap is certified.
    PostgreSQL old-RR serialization produces native **40001**, is explicitly
    rolled back at root, and a fresh RC retry rejects excess and reserves the
    exact remaining 3000. All five inventoried raw-expression probes run:
    MySQL supports them; PostgreSQL rejects JSON_ARRAY_APPEND, SHOW VARIABLES,
    backticks, HAVING aliases and the two-argument double-precision Haversine ROUND.
    Assessing unsupported SQL is not passing application compatibility.
15. **Conservation:** canonical confirmation/reservation tested invariants PASS:
    no duplicate contribution, commission, payable/receivable, refund/settlement
    effects or orphan reservations in their respective exercised cases; exact
    capacities/remainders, rollback and replay are retained. **Global financial
    safety FAILS at Wallet:** total Wallet sum remains 21000 but the sender is
    insolvent by 4000, so equal aggregate sums do not prove no over-credit.
16. **Open transactions:** NONE in the completed confirmation, reservation,
    timeout/deadlock and Wallet worker receipts: Laravel level **0**, PDO **false**.
    PostgreSQL's diagnostic deliberately records the aborted old-RR root and
    explicitly rolls it back before its clean RC retry. Native failure snapshots
    have no outstanding worker transaction.
17. **Strategy G:** NOT implemented or demonstrated necessary. The new Wallet
    failure is not evidence that reservation child authority manifests are needed.
18. **Schema:** NO owned/application schema migration or configuration change.
    Disposable fixture DDL and the existing temporary PG overlay only.
19. **Existing financial data:** exact established baseline/final protected
    comparison **MATCHES**: all **59 table fingerprints** and **465 schema objects**.
    Four owned completion evidence tables remain empty. No provider call,
    real credential, production access, checkout activation or live money action.
20. **Legacy Orders:** all **12 remain `unverified`**, with row fingerprints unchanged.
21. **MySQL certification:** **NOT CERTIFIED**.
22. **PostgreSQL certification:** **NOT CERTIFIED**.
23. **Production engine recommendation:** retain **C — INSUFFICIENT EVIDENCE;
    select neither on demonstrated correctness.** Both fail the required Wallet
    gate. MySQL currently fits more existing native SQL; PostgreSQL needs explicit
    dialect/full-bootstrap work. These operational differences cannot override
    the shared financial failure or the unrun required gates.
24. **Remaining payment work:** the proven Wallet failure requires **separate
    authorization**, not recursive remediation here. Afterwards, complete fresh
    required certification including native fulfillment, full negative/bootstrap
    parity and the PG semantic mirror; existing MTN/bootstrap and unknown
    provider/payout contracts remain independent blockers. No provider activation,
    live payments/refunds/payouts, production or publishing is authorized.

### Exact new failure and preservation

Unchanged `Dashboard/User/WalletController::withDraw()` checks the hydrated
authenticated user's Wallet price before the debit. Unchanged
`WalletHistoryService::createHistory()` subsequently decrements through the Wallet
relation without making that earlier balance decision authoritative after waiting.
Both workers observed 100 available, and the second debit applied to committed 30,
producing -40. This is a newly exercised independent gate, not a speculative
production incident and not a change to Wallet under the confirmation correction.
**No Wallet source correction was made.**

Evidence root: `.local/payment-engine-proof/correction-evidence/`.
`focused/{mysql,pgsql}-expanded.json` and engine-prefixed worker snapshots retain
the failed-case-first proof. `matrix/{mysql,pgsql}-expanded.json`, engine-prefixed
confirmation snapshots and `{mysql,pgsql}-wallet-concurrency.json` retain the
fresh full matrix up to STOP. `mysql-wallet-failure.sql`,
`postgres-wallet-failure.sql`, worker JSON, native server logs and disposable
databases under `/tmp/agendaally-engine-proof` are preserved. The disposable
servers are deliberately left running; no failing state was cleaned.

MySQL's initial Wallet classification surfaced through the **unsigned test
balance decoder rejecting -40.00**, after both successful commits. The saved
database/snapshot independently proves the failure; it was not rerun to repair it.
The PG test reader supports signed observed balances and reports the explicit
-4000 invariant failure. A supplemental classification records the MySQL signed
units without changing its native financial execution.

Runner-only interruptions are retained separately: non-atomic worker result
publication, post-suite facade cleanup, and Wallet worker missing AliasLoader/
filesystem bootstrap contracts. Those were fixed only in disposable tooling;
incomplete setup is not certified and no failing financial invariant was repaired.
The fully completed original 13/51 JUnit/PASS receipt was recovered after a
runner teardown exception, not rerun or fabricated. Temporary executable proof
tooling is retired into `correction-evidence/retired-harness-source.tar.gz`;
no automatic rerun can erase the preserved failure. Production previews remain running.

---

## Historical proof-only report (superseded where stated above)

2026-10-03, America/Chicago. **PROOF ONLY. BOTH ENGINE PROOFS STOPPED.**

Approval:
`attached_assets/Pasted-Approved-for-proof-only-evaluation-of-the-two-smallest-_1791080678608.txt`.

## 1. Recommendation

**C — Insufficient evidence. Do not select or implement the winning production
database/transaction architecture yet.**

The reservation results support both candidates under their tested boundaries:

- MySQL 8.0.42/InnoDB: fresh owned **REPEATABLE READ**, allocation primary-key
  mutex first, original version/CAS, ordinary authority reads and unchanged writes.
- PostgreSQL 16.15: owned **READ COMMITTED**, same reservation mutex, equations,
  exclusion rules and original writes, with temporary schema/guard adapters.

However, a new identical-receipt **canonical contribution-confirmation replay
failure** occurred on both engines. Each engine stopped immediately at that
financial workflow gate, with no remediation. Wallet, fulfillment, full negative
guard parity and PostgreSQL's remaining serialization diagnostic were not run.
This prevents a complete same-invariant production-readiness comparison.

**Strategy G is not necessary for the demonstrated current reservation callers.**
Both smaller candidates conserved reservations and allowed unrelated allocations
to progress without a manifest or child authority range locks. The new confirmation
failure is a pre-lock context/read-order problem; adding G to reservation would not
repair it. This does not certify all future transaction compositions or sibling
writers, and no source correction is approved.

MySQL B remains the smaller demonstrated reservation candidate, not an authorized
production-engine choice. PostgreSQL's successful reservation results are meaningful
native evidence, not a complete application port or proof of inherent superiority.

## 2. What actually ran

### Environment and isolation

| Item | MySQL | PostgreSQL |
|---|---|---|
| Native version | 8.0.42, not MariaDB | 16.15, installed supported stable release |
| Location | New `/tmp/agendaally-engine-proof/mysql` datadir; loopback port 33308 | New `/tmp/agendaally-engine-proof/pgdata`; private Unix socket, port 55321; no TCP listener |
| Database | `agendaally_payment_disposable_proof` | Same explicitly disposable name in the separate instance |
| Money fixture | Exact integer native units; frozen USD, scale 2 | Identical accounting units/economics |
| Root isolation | REPEATABLE-READ | read committed, UTF8 |
| Native settings | `innodb_rollback_on_timeout=0`, `binlog_format=ROW`; fixture wait timeout 2s | Fixture `lock_timeout=2s`, `statement_timeout=15s` |
| Framework | PHP 8.4.16; installed Laravel 12.46.0 | Same |
| Connections | Real independent PHP worker processes/PDO connections | Same; separate native backend PIDs |

These are isolated proof settings, **not changes to AgendaAlly's connection
configuration, production defaults or the owned preview database**. PostgreSQL 18 was
an earlier illustrative target; the installed supported 16.15 was used without a
runtime/package upgrade. No workspace-managed PostgreSQL connection was used.

The original app/kernel/database environment was not booted. Existing isolated
fixtures supply in-memory configuration, synthetic authentication/encryption and an
HTTP factory that prevents stray requests. The original thirteen's provider responses
are fakes. No real provider request, credential, payment/refund/payout, production
access or deployment was made.

### Candidate prototype

`FinancialOperations::reserve` already executes the required B sequence when it owns
the root. A **proof-only facade**, not a native application edit, required transaction
level zero and delegated to that unchanged service. It rejected an already-open
caller before financial writes. Native worker query traces confirmed parent
`FOR UPDATE` first, existing version/CAS and ordinary child authority SELECTs.

No financial equation, exclusion, state-counting set, row identity, index, counter,
manifest, monetary semantics or permanent schema was changed for MySQL.

For PostgreSQL the same service and equations ran on dedicated RC connections.
Temporary PHP DDL/guard overlays were explicitly loaded only by the proof CLI.
Production-grade per-unit isolation/pool lifecycle helpers were **not implemented**.

The own-write/deadlock cases declared a fresh parent-first root, then exercised native
nested service savepoints within that valid unit. They did not allow arbitrary old
caller views or independently commit an inner reservation.

### Test discipline

- Real native lock waits were observed before releasing owner barriers.
- Independent B completed while A remained open both **after authority reads,
  before INSERT**, and **after its uncommitted INSERT**.
- Both initially empty and populated operation sets were tested.
- “Empty” refers to operation/counted-reservation and refund-effect sets; an
  operationally eligible finalized allocation necessarily already has confirmed
  funding contexts and base accounting evidence.
- Workers recorded SQL/bindings, native exception chains, retry attempts, transaction
  levels and PDO transaction status.
- A failed assertion did not become a pass by loosening financial expectations.
- Only necessary disposable fixture corrections were made before the new financial
  stop; native application files and original test bodies remained unchanged.
- Passing groups were retained rather than rerun while resuming from fixture setup
  interruptions.

## 3. Results — exact scope, not inferred certification

**Original MySQL thirteen:** restarted and passed unchanged, **13 tests / 51
assertions**, no skips/errors/failures. Output and JUnit are retained.

**Expanded custom native groups, separate from those thirteen:**

- MySQL: **13 passed, 1 failed, 3 not run**.
- PostgreSQL: **13 passed, 1 failed, 4 not run**.

The custom harness is assertion-driven PHP using native Laravel services; its groups
are not being represented as a PHPUnit thirteen-case PostgreSQL replacement.

| Required financial behavior | MySQL B | PostgreSQL RC | Exact observation |
|---|---|---|---|
| 10000, concurrent 7000 + 7000 | PASS | PASS | Owner held parent after ordinary authority reads. Contender waited; after owner committed 7000, second 7000 was rejected as exceeding unreserved authority. |
| Exact remaining 3000 | PASS | PASS | 3000 succeeded; retained same-key replay returned the same operation; another 1 failed. Counted total exactly 10000; only two durable reservation rows. |
| Independent, empty, after authority reads | PASS | PASS | B succeeded before A released/committed; A subsequently succeeded. |
| Independent, empty, after INSERT | PASS | PASS | B succeeded while A's real operation INSERT was uncommitted. |
| Independent, populated, after authority reads | PASS | PASS | Each allocation had a committed 100 reservation; distinct B still completed while A stayed open. |
| Independent, populated, after INSERT | PASS | PASS | Same populated setup; B completed before A's commit. No deliberate child SHARE/UPDATE read range locks. |
| Same-key and changed parameters | PASS | PASS | Full-capacity 10000 replay returned retained operation even with zero remainder. Changed amount, actor and context each rejected; one operation retained. |
| Newly committed refund effects | PASS | PASS | Original-context principal refund 1000 plus commission reversal 100 committed behind owner barrier. Contender 9001 rejected; 9000 then succeeded. |
| Vendor payable conservation | PASS | PASS | Frozen native payable 9000: 7000 owner, competing 7000 rejected, exact 2000 remainder once; total 9000. No altered commission to manufacture a 10000 payable. |
| Commission receivable conservation | PASS | PASS | Offline gross 10000 yields original receivable 1000: 700 owner, competing 700 rejected, exact 300 remainder once; total 1000. |
| Current committed exclusions | PASS | PASS | RESERVED payout blocked refund; RESERVED refund blocked payout; RESERVED receivable blocked refund. CANCELED released authority. Existing asymmetry—receivable can coexist with refund—remained unchanged, using valid mixed custody. Explicit UNKNOWN/PENDING transitions were not certified by this group. |
| Rollback, own writes, savepoint and safe retry | PASS | PASS | Own 7000+3000 visible in declared unit; own replay retained identity. Failed inner capacity check did not erase earlier own writes. Forced root rollback removed operations and restored version exactly; fresh same-key retry succeeded. Pre-existing caller facade rejection made no financial writes. |
| Native lock timeout | PASS | PASS | Native 1205/HY000 versus 55P03. Losing root clean: level 0, PDO not in transaction, no durable operation/version leak. Owner and subsequent fresh reservation succeeded. |
| Native deadlock, whole-root retry | PASS | PASS | Two genuine roots acquired opposite allocation order after their own reservation. Actual deadlock captured; attempts 2+1. Both eventually succeeded; exactly two operations/2000 units and only completed version increments. |
| Canonical contribution confirmation/finalization replay | **FAIL / STOP** | **FAIL / STOP** | Owner confirmed/finalized; waiting identical-receipt contender rejected with `Terminal allocation/contribution cannot accept funding.` Exact failure below. |
| Wallet conservation/overspend | NOT RUN | NOT RUN | Stop gate; no native Wallet readiness claim. |
| Fulfillment finality | NOT RUN | NOT RUN | Stop gate; no native Product/Booking finality claim. |
| Comprehensive negative guard/type/name/JSON assessment | NOT RUN | NOT RUN | Core bootstrap and positive operations executed; the expanded explicit mutation/projection assessment came after stop. |
| Extra old-RR serialization/RC retry diagnostic | Not this candidate | NOT RUN | Stop gate before diagnostic; 40001 changed-parent behavior remains documented expectation, not this run's result. |
| PostgreSQL mirror of all original thirteen | N/A | NOT RUN | Earlier plan not completed; seeded attempt/claim success is not thirteen-case contention certification. |

The split-case figures above describe **reserved authority**, not provider-funded
refunds, transfers, external payouts or real financial operations.

### Original thirteen, unchanged MySQL replay

All passed: economic_creation, contribution, confirmation, refund, vendor_settlement,
attempt, claim, provider_finalization, refund_reservation, refund_finalization,
receivable, payable_reservation, payout_finalization_blocked.

The external payout case remains a **blocked sequential two-connection case**, not
external rail readiness. Twelve involve overlapping native transactions. Their
existing contender timeout followed by a fresh replay did not cover the new
wait-through-commit confirmation schedule.

The originals call the unchanged native service, including artificial fresh owner
transactions. This is a baseline regression run, **not proof that a permanent strict
root-only facade has already been installed**. A later ownership change must preserve
their financial assertions and house synchronization inside a valid owned unit.

## 4. New financial stop — identical on both engines

### Reproduction

1. Disposable canonical allocation gross=10000, commission=1000 and electronic
   contribution amount=10000. Original synthetic generic attempt was claimed and
   terminally marked by the fixture; context was pending and allocation unfinalized.
2. Worker A called unchanged
   `AllocationWriter::confirm([$context], 'synthetic-native-receipt', 10000)`.
3. A updated confirmation and reached base-ledger insertion; a test-only listener
   paused it inside the transaction.
4. Worker B called the **same native method, context, receipt and amount**. Its first
   ordinary query obtained the still-pending context object; it then waited on A's
   allocation primary-key mutex.
5. A was released and committed; its worker returned success, level 0, PDO outside
   a transaction.
6. B acquired the now-finalized parent but continued with its original pre-lock
   context object and threw:

   ```text
   DomainException
   code: 0
   SQLSTATE: none (application domain rejection, not a database abort)
   Terminal allocation/contribution cannot accept funding.
   ```

7. B's root rolled back cleanly: level 0, PDO outside a transaction. No source was
   corrected, and no weaker “rejection counts as idempotent success” assertion was
   substituted.

### Source and native evidence

`AllocationWriter::confirm:181–190` calls `orderedContexts` **before** acquiring
allocation locks. `orderedContexts:408–415` materializes an ordinary `get()`.
The later confirmation loop retains those earlier objects.

Both contenders recorded this order:

```text
SELECT contexts by IDs, ordinary get()        -- object still pending
SELECT allocation ... FOR UPDATE              -- waits, then current finalized parent
SELECT event membership                       -- does not replace original objects
SELECT receipt anchor ... FOR UPDATE
DomainException / root rollback
```

On MySQL the pre-lock ordinary query can also establish the RR view before the mutex.
On PostgreSQL RC later SQL statements can be fresh, but **RC does not refresh an
already-materialized PHP object**. This is why avoiding InnoDB snapshots/gaps alone
is not an AgendaAlly correction.

Classification: new **canonical confirmation concurrency/idempotency failure on both
engines**, not an observed over-credit or over-capacity and not a proven production
callback incident. The stopped assertion required the legitimate duplicate to finish
as an idempotent replay. The contender rejected before another confirmation/effect
write; its trace has no duplicate INSERT.

The owner trace records successful confirmation/base-effect SQL and root completion.
Because the assertion failed before the planned post-test count queries, do not
mislabel an unperformed final row-count audit as a passing assertion. Failure-state
worker traces, transaction outcomes and unchanged source identify the issue; the
disposable fixture was then cleaned.

This test exercises the **current canonical sibling writer**, whose pre-lock lookup
does not itself satisfy the proposed universal parent-first financial-unit discipline.
Reservation results remain valid, but cannot be extrapolated to that sibling.
An approved later correction might involve authoritative re-read/order/complete-unit
ownership; none was attempted, including an outer-wrapper change merely to make this
test pass.

## 5. PostgreSQL bootstrap: verified subset versus remaining compatibility

The full pre-execution adaptation register is
`.local/payment-engine-proof/adaptations.md`; exact overlays/diffs are in the evidence
bundle. Financial identity, original money, custody, commission and capacity equations
were not rebound or changed.

| Area | Adaptation/observed evidence | Remaining uncertainty |
|---|---|---|
| Accounting DDL | Temporary `AccountingSchema` accepts pgsql; original CHECK/FK/index arrays retained. Native core bootstrap repeatedly succeeded. | Committed helper still rejects pgsql. Full historical application migration chain was not run. |
| Identity | `@pk` maps to BIGINT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY; `@ref` signed BIGINT. Generated financial allocation/context IDs and referenced links worked. | Copied explicit IDs/sequences, negative/overflow/coercion cases, nonfinancial schemas and full conversion unproved. |
| Receipt self-anchor | Original same-row `CHECK(anchor IS NULL OR anchor <> id)` stayed in native PostgreSQL CREATE TABLE and bootstrap succeeded. No MySQL workaround used there. | Direct invalid generated/explicit-ID self-anchor mutation test was not reached; complete rejection/rollback parity remains unverified. MySQL retained its approved trigger workaround. |
| Explicit names | Temporary completion branch chooses existing `eca_provider_reference_unique` for pgsql, unchanged columns/order/uniqueness. Core financial schema creation succeeded. | Explicit catalog assertions/full compiled-name collision audit were after stop; historical 64-byte names still need review. |
| Nullable uniqueness | Successful two refund reservations have same provider and NULL external_reference, exercising existing provider/external nullable UNIQUE positively. | Deliberate non-null duplicate rejection and every original nullable identity not fully certified. |
| FKs | Original financial FKs were installed; positive context/allocation/actor/revision/effect links worked. | Explicit invalid-FK rejection, delete/restrict/update/down and full historical parity not reached. |
| Completion guards | Temporary PL/pgSQL equivalents installed for retained revisions/receipts, original attempt/operation bindings, retained claims/references/provider-payment IDs, terminal state/version and valid amount/kind/state. Native permitted operations succeeded. | Complete mutation/rejection/lifecycle parity is not certified. No committed pgsql branch exists. |
| Unsigned versions | Nonnegative completion-version CHECKs preserve MySQL UNSIGNED's accepted lower bound; accounting signed exact units remain original. | Broader unsigned coercion/range semantics and full legacy schema portability require review. |
| UUID/text bindings | Temporary predicate compares native attempt funding/revision UUIDs as text to existing CHAR/VARCHAR context identities. No context identity conversion/backfill. Valid native bindings succeeded. | Case/collation/trailing-space/invalid-text policies and broader joins require proof; not automatically equivalent. |
| Merchant profile/revision | Existing SQLite guard contract represented with a NULL-safe PostgreSQL trigger; valid revision append/update worked. | All invalid cross-profile/retained-evidence paths not reached. |
| JSON | Existing storage definitions and strict application comparator preserved. Normal fixture JSON did not block native positive operations. | Dedicated large-number/container/array/type/equality tests stopped before execution. No blanket JSONB conversion or JSON-equality substitution. |
| Laravel transactions/savepoints | Owned reservation roots, parent-first composed own writes/savepoints, root rollback, genuine deadlock retry and clean 55P03 rollback passed. | Arbitrary nested old-RR success not required/certified; PostgreSQL 40001 diagnostic and broader transaction-manager recovery remain unrun. |
| Raw/database-specific SQL | Current source remains unchanged. Earlier inventory identifies notification JSON mutation, SHOW VARIABLES, backticks, HAVING aliases and PostgreSQL Haversine ROUND signature adaptations. | No nonfinancial route/SQL probe was run after stop. These are remaining source blockers/risks, not compatibility passes. |
| MTN identity migration | Source explicitly rejects non-SQLite; no bypass or replacement installed. | Not executed; full native MySQL and PostgreSQL bootstrap still need separately reviewed MTN extension guards/DDL. |

Temporary PostgreSQL triggers use `IS DISTINCT FROM`, RAISE EXCEPTION/23514 and
unchanged original predicates instead of MySQL `<=>`/SIGNAL. The profile binding
guard preserves an existing SQLite contract as well. The overlay's copied `down`
path is not a certified PostgreSQL rollback implementation; function/trigger dependency
cleanup, retention preflight and real migration deployment require later work.

**Result: the isolated accounting/completion subset was runnable with disclosed
adaptations; current AgendaAlly is still not PostgreSQL-compatible unchanged.**

## 6. Transaction, error, replay and maintenance comparison

| Requested comparison | MySQL Strategy B | PostgreSQL RC |
|---|---|---|
| Same-allocation conservation | Demonstrated for refund 10000, payable 9000 and receivable 1000, including wait-through-commit/exact remainders. | Same demonstrated bounds/equations. No automatic-engine-fix claim. |
| Independent concurrency | Empty/populated, pre-insert/post-insert A-open cases passed without child authority range locks. | Same passes; no throughput superiority inferred. |
| Isolation | First consistent read must occur after obtaining the parent mutex in a genuinely fresh RR root. Existing pre-parent snapshots invalid. | Each ordinary statement can see newer commits; parent participation stabilizes authority across statements. Earlier PHP objects still stale. |
| Replay | Same key/amount/actor/context retained before capacity; changed parameters rejected. | Same; native UUID/collation policy beyond tested canonical fixtures remains a port concern. |
| Rollback | Root restores operation/version; allowed savepoint failure preserves earlier own writes. | Same observed; do not extrapolate lock-release/aborted-state behavior to untested arbitrary roots. |
| Timeout | 1205/HY000 captured; losing service returned a clean root, no durable partial authority; fresh retry safe. Laravel can classify MySQL wait timeout as concurrency. | 55P03 captured; clean root rollback, fresh retry safe. Installed Laravel does not explicitly treat this native message as the same automatic-retry category; production policy must classify it deliberately. |
| Deadlock | 1213/40001 captured. Genuine complete-root retry 2+1; exactly two durable operations and only completed versions. | 40P01 captured. Same complete-root retry/result. This is a deadlock result, not proof of the separate 40001 serialization diagnostic. |
| Required permanent reservation changes | Explicit owned-root entry/guard and complete-unit retry/error contract; keep parent-first ordinary reads and equations. No reservation schema/index/manifest required by these passes. | Explicit owned RC unit/error/isolation lifecycle plus same discipline, and current schema/SQL port blockers. No manifest required by these passes. |
| Required sibling changes | New confirmation issue needs separate approval/analysis; no correction inferred from reservation proof. | Same engine-independent issue; RC alone insufficient. |
| Schema/migrations | No new reservation schema. Full fresh application migration, especially SQLite-only MTN extension, still not certified. | Reviewed accounting helper/identity/FKs, completion guards/name/type adaptations, MTN extension, generated-name audit, down/retention handling and complete migration/seed chain. |
| Conversion complexity | Lowest incremental surface for the current MySQL-oriented source; no engine data conversion for B. | Material pre-MVP application/data semantics port; historical migrations, exact logical-value reconciliation and sequence/identity/JSON/collation policies. Not a connection-string swap. |
| Operational complexity | Isolation/timeout/binlog/backup/restore/pooling/lock monitoring require real environment approval. Native proof's ROW logging is not production discovery. | RC/pooling/TLS/backup/restore/PITR/vacuum/WAL/lock/retry monitoring require approved deployment work. Private proof settings do not establish hosted readiness. |
| Long-term maintenance risk | Owned-unit guard is much smaller than complete manifest maintenance; forgotten pre-lock reads/writers can still invalidate semantics. | Native DDL/trigger lifecycle and SQL/type parity add maintenance. Same pre-lock object/writer risks; no intrinsic cure from engine selection. |
| Remaining uncertified | Native Wallet/fulfillment, current canonical replay failure, full migrations/guards, complete callback/provider/business route recovery, production/UAT. | Same plus full application PostgreSQL port, original-thirteen mirror, JSON/name/FK/collation/sequence/guard negatives and serialization diagnostic. |

No sustained-load benchmark, p95/p99 claim or operational hosting discovery was
performed. Distinct-allocation completion is proven for the controlled barriers,
not a guarantee that shared funding groups, unique references, Wallet accounts or
economic hot parents never contend.

Prior effort ranges remain **uncertified planning estimates**, not actual elapsed
engineering delivery: bounded MySQL B ownership work roughly 2–4 engineer-days;
PostgreSQL feasibility proof 5–10; full native MVP port 15–30+; full sibling corrections
and operational readiness additional. The new shared failure prevents treating any
range as an approved implementation plan.

## 7. Caller trace, interruptions and evidence integrity

### Reservation ownership reverified

The current application scan again found only
`PaymentOperationsController::store:84` calling `FinancialOperations::reserve`.
The current POST route remains behind `block.ip` and `auth:sanctum`; controller
scope/permission reads and validation precede the service, and `run` catches errors
without opening a transaction. Middleware/provider/bootstrap transaction search
found no enclosing root. No indirect production caller contradicting the earlier
trace was found. Projection/reserved/action references are not reservation callers.

Thus the caller stop gate did not trigger. This is source-supported, not inspection
of private production plugins/infrastructure. The artificial old-view fixture is not
promoted to a product requirement.

### Retained setup interruptions, not suppressed financial failures

1. The first independent fixture tried to replace the generated original provider
   reference when marking its synthetic attempt SUCCESS. Existing MySQL immutable
   guard rejected it (45000/1644). The fixture was changed to retain that original
   reference; source/guard/assertions unchanged.
2. The first mixed-custody fixture tried to confirm two contributions in the same
   allocation's `selected_method` slot. Existing UNIQUE rejected it (23000/1062).
   It was corrected to use the existing fixture's distinct wallet_contribution/
   selected_method slots; no slot/identity rule was relaxed.
3. The initial PostgreSQL daemon became unavailable before the first PostgreSQL
   financial case; 08006/connection refused was a provisioning interruption, not
   a financial result. A tracked disposable foreground background process was
   started and native cases then ran.

Original receipts/outputs are retained. Those setup fixes did not remediate an
application failure. At the subsequent genuine identical-receipt replay failure,
both engines stopped and no test/source repair was attempted.

### Evidence files

Primary records under `.local/payment-engine-proof/evidence/`:

- `original13-junit.xml`, `original13-output.txt`.
- `mysql-expanded.json`, `pgsql-expanded.json`: per-group status/evidence/native
  errors, explicit engine stop and not-run entries.
- `mysql-fixture-setup-interruption.json`,
  `mysql-mixed-slot-setup-interruption.json`, engine output receipts.
- Per-worker `*.input.json` / `*.result.json`, including all confirmation owner/
  contender SQL traces and complete exception chains.
- `AccountingSchema-proof-overlay.diff`, `CompletionSchema-proof-overlay.diff`.
- `caller-trace.txt`, `source-preservation.json`, `protected-comparison.json`.
- Pre-execution `adaptations.md` and quarantined proof-harness source archive.

The failure entries include owner/contender results; application DomainException
has no invented native SQLSTATE. Worker traces distinguish actual deadlock/timeout
native codes from the unrun serialization diagnostic.

## 8. Restoration, protection and final STOP

Native application/source files were **never edited**. Exact hashes of **1761**
native app/migration/test/config/script files match the pre-proof snapshot.
Original thirteen test classes/bodies and financial source remain unchanged.

Temporary overlays/runners are retired into an inert evidence archive; active
temporary overlays removed and both disposable database servers stopped. No prototype
is registered in app boot, workflows, migrations or connection configuration.

The exact owned-development snapshot equals the original Phase 3C baseline:

- **59 protected tables** with original stored-value/count fingerprints.
- **465 schema objects** with the original schema fingerprint.
- Four completion evidence tables remain empty.
- **All 12 legacy Orders remain unverified**.
- Existing legacy ledger/configuration/country/currency values remain preserved.

**Final recommendation: C — Insufficient evidence.**
Reservation B/RC passes justify further consideration, not a permanent engine
conversion or a provider-readiness claim. The new shared confirmation failure and
stopped required journeys prevent complete certification. **G is not necessary for
the demonstrated reservation boundary and does not solve this new failure.**

**STOP.** No winning architecture, financial correction, migration, provider activation,
legacy classification/deletion, production access or publishing is implemented.