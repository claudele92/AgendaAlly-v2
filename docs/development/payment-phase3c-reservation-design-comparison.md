# Phase 3C — revised reservation design comparison

2026-10-03. **DESIGN ONLY. PHASE 3C REMAINS STOPPED.**

Instruction:
`attached_assets/Pasted-Phase-3C-remains-stopped-Before-another-implementation-_1791078520803.txt`.
No application, test, schema, migration, isolation, environment or workflow changes
are made in this review. No MySQL server or new financial test is started.
All execution results below are retained historical results, not new certification.

## 1. Recommendation and decision boundary

The later [disposable MySQL B / PostgreSQL RC proof](payment-engine-proof-comparison.md)
demonstrated reservation conservation and independent progress without G, then
stopped both engines at a new canonical confirmation-replay failure. No permanent
architecture was implemented; see that report for current execution evidence.

The subsequent [production database architecture comparison](agendaally-production-database-comparison.md)
examines whether AgendaAlly actually needs the retained nested/old-view contract.
No current application requirement was demonstrated; production engine choice
requires native PostgreSQL proof. The G recommendation below is conditional on that
unchanged contract, not approval or an unconditional product architecture choice.

**For the existing contract—MySQL REPEATABLE READ, already-open caller
transactions/savepoints, current authoritative evidence and independent allocations—
recommend a complete per-allocation evidence-membership manifest on the existing
locked parent, followed by current point reads of known-existing primary keys.**
Keep the existing child rows as the monetary source of truth and run the existing
capacity equations over those rows. Do not replace them with invented available
money or rewrite the frozen quote.

This is a proposed **maintained-authority/schema change**, not another approved
two-file read correction. It requires a separately approved additive parent field
and narrowly bounded membership-maintenance hooks in child-creation paths.
Nothing is implemented or approved here. Its correctness depends on complete
atomic membership maintenance; validation must prove that before deployment.

A smaller, conventional no-schema design is possible **if the caller transaction
contract may change**: own a fresh REPEATABLE-READ transaction, lock the allocation
before its first consistent read, then use ordinary child reads. This establishes
both invariants for that transaction contract, but cannot complete a reservation
inside an arbitrary already-established caller snapshot. Rejecting every nested
call is not a drop-in fix, not independent-allocation progress for the existing
fixture, and not grounds to claim the present acceptance tests passed.

Transaction-only READ COMMITTED is another sound parent-mutex pattern for a
transaction deliberately started in that mode. It also cannot upgrade an existing
REPEATABLE-READ transaction, and changes the previously retained isolation contract.
It is an alternative requiring approval, not the recommendation under unchanged RR.

**An index-only correction is not supported by the retained evidence.** The
relevant indexes already lead with allocation identity. Extra covering columns
can reduce work but cannot turn an empty/missing-key RR locking search into a
record-only lock. Do not create an index to claim this defect contained.

## 2. Mandatory invariants and common assumptions

1. **Same-allocation conservation:** after observing current eligible context,
   ledger, reservation and exclusion authority, a new reservation cannot make the
   authorized total exceed the unchanged capacity. Count RESERVED, UNKNOWN and
   PENDING exactly as today.
2. **Independent-allocation progress:** keeping allocation A's valid transaction
   open must not prevent unrelated allocation B's valid reservation completing
   merely because A performed a missing-key/child-range authority lookup.

Unrelated means different allocation and request identities, not two uses of the
same globally unique provider/receipt identity or a genuinely shared funding group.
Short engine latches are not the prohibited transaction-long financial lock.

Every viable parent-mutex design requires **all writers that change eligibility,
capacity or counted membership to participate in that allocation's mutex protocol**.
A foreign key is not a general replacement for this writer discipline. Existing
source takes the parent lock in reservation/cancellation, refund-result application,
receipt settlement, contribution confirmation and effect append. These paths are
traced only; this is not certification of their other equations or transaction reads.

The dispatch claim outside the mutex changes RESERVED to UNKNOWN without changing
the immutable amount or membership in the counted state set. Do not infer from
that source example that arbitrary unlocked authority changes are safe.
The complete writer inventory and the pending sibling tests remain acceptance gates.

## 3. Exact reconstruction of cross-allocation locking

### 3.1 Evidence reviewed

- `.local/payment-phase3c-reservation-current-read-candidate.diff`
- `.local/payment-phase3c-reservation-independent-evidence.json`
- `.local/payment-phase3c-reservation-independent-{tests.txt,junit.xml}`
- `.local/payment-phase3c-reservation-current-read-evidence.json`
- Strengthened snapshot and independent-allocation test source.
- Current `FinancialOperations`, `AllocationBalances`, `AllocationWriter`,
  `AccountingEffects`, completion indexes and original accounting index definitions.

Candidate cap result: 1/10 passed; second 7000 rejected, committed authority 7000.
Independent result: 1/5 failed; allocation B obtained its own parent lock, but
its service reservation did not complete while A remained open.
The candidate remains withdrawn.

### 3.2 Query/index/lock combinations

The candidate first obtained A's parent by its existing primary key:

```sql
SELECT * FROM commerce_payment_allocations WHERE id = :a FOR UPDATE;
UPDATE commerce_payment_allocations
SET version = version + 1, updated_at = :now
WHERE id = :a AND version = :locked_version;
```

Observed parent lock: `PRIMARY`, `X,REC_NOT_GAP`, record A. B obtained its different
parent record. **This parent mutex was not the resource preventing B's progress.**

The candidate introduced these child locking reads; parameters below are schematic,
not commands executed in this review:

| Candidate read | Existing access path | Retained lock evidence and significance |
|---|---|---|
| `SELECT * FROM payment_financial_operations FORCE INDEX(financial_operation_request_unique) WHERE allocation_id=:a AND kind=:kind AND request_key=:key LIMIT 1 LOCK IN SHARE MODE` | UNIQUE `(allocation_id,kind,request_key)`; all key parts specified | Initially absent request: shared gap/supremum protection. Owner snapshot after its insert retains `S,GAP` at its operation and `S` on `supremum pseudo-record`. A found full unique key is record-only; an absent key is not. |
| `SELECT amount_units FROM payment_financial_operations FORCE INDEX(payment_financial_operations_allocation_id_kind_state_index) WHERE allocation_id=:a AND kind=:kind AND state IN ('RESERVED','UNKNOWN','PENDING') [AND context_id=:context] ORDER BY state,id LOCK IN SHARE MODE` | Nonunique `(allocation_id,kind,state)`; context is a residual filter | `S,GAP` plus shared supremum on this index. This is a range/current read even with the correct leading allocation key. |
| Confirmed contexts by allocation, `ORDER BY id LOCK IN SHARE MODE` | `pcc_allocation_idx (allocation_id,state,id)` | S record/next-key on A's confirmed context and S,GAP before B's confirmed context. Another potential boundary for context insertion, not an operations-index lock. |
| Allocation ledger/effect rows, including context-specific principal, `ORDER BY id LOCK IN SHARE MODE` | `pfle_allocation_idx (allocation_id,effect_kind,id)` | S record/next-key on A's base entries and S,GAP before B's first entry. A possible ledger-insertion boundary, not directly the financial-operation INSERT index. |
| Original context by primary key | Existing `PRIMARY` | Found existing context: `S,REC_NOT_GAP`, allocation validated. |
| Original attempt by funding event | Existing UNIQUE funding-event key | This fixture finds the attempt: S record-only on unique entry and primary row. A permitted absent-attempt branch would still need scrutiny for missing-key gap locking. |

The operation table was empty before A's reservation. The lock snapshot was taken
**after A inserted its first operation and after B rolled back**, while A was still
open. It is not a snapshot of simultaneous waiting transactions.
The two operation indexes contain shared supremum locks retained from A's current
searches across absent/empty ranges.

A physical index gap is not a logical `allocation_id` partition. When there is
no later key, its supremum interval extends past A into possible B keys. B's
INSERT requires insert-intention permission in **each** affected secondary index;
A's retained inhibitive gap lock can prevent that insertion. Shared gap locks
can coexist with B's shared search locks, yet still prevent B's subsequent insert.
Replacing SHARE with UPDATE would retain inhibitive gaps and is not a fix.

The stored `EXPLAIN` for reserved authority is `type=range`, uses the expected
allocation/kind/state index, `key_len=220`, `Using index condition`. It is not
an unhinted full-table scan. The empty replay plan says
`no matching row in const table`; that plan description does not mean no locks.
`TABLE IS/IX` entries are normal intention locks, not a global financial mutex.

### 3.3 Precision limit of the receipt

The stored exception is Laravel `DeadlockException`; native code is null.
The fixture did not retain the underlying exception chain, failed SQL, transaction/
engine-lock identifiers or `performance_schema.data_lock_waits`. Therefore it
does **not** identify which of the two operations-index gaps was reported as the
first wait, or prove native 1205 versus 1213.

Source sequence plus retained physical locks identify the two relevant operations
search/index combinations capable of obstructing the new operation INSERT. Either
retained supremum interval is sufficient; both must be removed from the design.
The context/ledger gaps are additional future-writer hazards, not evidence that
B's financial-operation INSERT writes those other tables.

A future approved validation should capture the live wait graph and full native
exception chain before rollback, and isolate each read in a synthetic fixture.
No such new experiment or exact-first-blocker claim is made here.

**Conclusion:** the allocation mutex design is not disproved. Combining it with
RR current child range/absence locks is the problem. The evidence does not support
calling the existing leading indexes inadequate or promising a covering index cure.

## 4. Shared exact authority/read sequence

For the viable strategies below, authority must include all of the following,
using the SAME transaction's own writes as well as appropriate committed state:

1. Acquire the existing allocation primary-key mutex and preserve version/CAS.
2. Read retained operation identity **before** evaluating new capacity. A matching
   `(allocation,kind,request_key)` returns the retained operation; different amount,
   actor or context fails. Do not treat duplicate INSERT as equivalent to replay.
3. Obtain complete contexts and ledger effects; run the existing balance equations.
   Validate finalized canonical economics and the original refund context/attempt.
4. Apply the unchanged rules:
   - refund: original confirmed context amount minus its refund-principal effects
     minus its counted refund reservations; exclude outstanding payout/receivable;
   - payout: projected Vendor payable minus counted payout reservations; exclude
     outstanding refund reservations;
   - receivable: projected commission receivable minus counted receivable reservations.
     Do not invent a symmetric exclusion that the source does not contain.
5. Insert the existing real operation with unchanged UUID/request key/bindings and
   existing unique/FK/guard backstops. No dummy operation, sentinel amount, PREPARED
   state or external provider action is introduced.
6. Commit only when the complete financial operation succeeds. Capacity rejection
   rolls back its version change and insert; same-key retry re-evaluates authority.

For B/C's valid transaction contracts, the plain child-read SQL is:

```sql
SELECT * FROM payment_financial_operations
WHERE allocation_id=:a AND kind=:kind AND request_key=:key LIMIT 1; -- replay FIRST
SELECT * FROM payment_collection_contexts
WHERE allocation_id=:a AND state='confirmed';
SELECT * FROM platform_fee_ledger_entries WHERE allocation_id=:a;
SELECT * FROM payment_financial_operations
WHERE allocation_id=:a AND state IN ('RESERVED','UNKNOWN','PENDING');
-- Refund eligibility additionally uses the original context point and event:
SELECT * FROM payment_collection_contexts
WHERE id=:context AND allocation_id=:a AND state='confirmed' LIMIT 1;
SELECT * FROM electronic_collection_attempts WHERE funding_event_key=:event LIMIT 1;
-- Use the locked parent and these complete row sets in the unchanged equations.
-- No FOR SHARE/UPDATE clause on any of the above child reads.
```

These statements do not refresh an arbitrary RR caller's old snapshot; they are
valid only under B's parent-first fresh view or C's deliberately started RC mode.

## 5. Strategy comparison

In each entry, SQL is a **proposed sequence**, not executed code.

### A. Existing parent mutex + ordinary reads inside arbitrary RR caller — reject

**Sequence:** caller `BEGIN`; ordinary child read establishes old view; another
transaction commits authority; `SAVEPOINT reserve`; parent `FOR UPDATE`/version
CAS; ordinary replay/context/effect/reservation reads; capacity; INSERT.

**Locks:** existing parent record X; no child-read gaps; normal INSERT unique/FK
and record/insert-intention locks. B's different parent and INSERT can progress.
**Freshness/empty/commits:** no freshness guarantee. An old empty operation set
stays empty after a committed reservation; newly committed effects also disappear.
The reproduced 14000 against 10000 is precisely this failure.
**Replay:** an old snapshot can miss a retained key; UNIQUE is a backstop, not a
replacement for replay-before-capacity. **Rollback:** atomic DML/savepoints do not
repair the stale capacity decision; deadlock retry inside the same old view does
not refresh it. **Changes:** none, therefore no fix; existing SQLite behavior
unchanged. **PostgreSQL:** RR does not provide a fresh read by locking a parent;
some changed-row locks abort with serialization failure, not a freshness substitute
for child authority whose writer only locked the parent.

### B. Own fresh RR transaction, parent-first ordinary child reads — viable new contract

```sql
-- Require no active caller transaction; never commit/rollback it implicitly.
START TRANSACTION; -- ordinary RR, NOT WITH CONSISTENT SNAPSHOT
SELECT * FROM commerce_payment_allocations WHERE id=:a FOR UPDATE;
-- Existing version CAS; then first consistent read is retained-key replay.
SELECT * FROM payment_financial_operations
WHERE allocation_id=:a AND kind=:kind AND request_key=:key LIMIT 1;
-- Plain context, attempt, effect and operation queries from section 4.
-- Same equations and real operation INSERT.
COMMIT;
```

**Locks:** parent found unique record X, no S child range/absence locks.
INSERT's compatible distinct-key insert intentions and shared FK checks do not
create the candidate's owner search gap locks. B can complete on its own parent.
**Freshness:** under InnoDB RR, first consistent read occurs after winning the
mutex, so it includes all earlier committed participating writers. They cannot
alter authority while the mutex is held. Empty sets need no artificial boundary
record. If the owner waited for another same-allocation commit, that commit is
included. Own uncommitted changes are visible.
**Replay:** first plain read sees retained keys before capacity. **Rollback:** root
rollback releases authority/version; a savepoint inside this valid transaction can
undo later DML, but cannot make an older arbitrary caller snapshot valid. Root
deadlocks retry the complete transaction, again parent-first. Timeout must explicitly
roll back the operation/root, not retain a preceding version write.
**Changes:** no schema/index or monetary equation/identity changes. It changes
MySQL caller composition: an already-open caller must fail without financial writes
and retry the whole unit at a fresh boundary. Never commit a separate inner
reservation that survives caller rollback. SQLite's accepted path stays unchanged.
**PostgreSQL:** RC + parent mutex ports directly. Under PostgreSQL RR, the initial
locking statement fixes the transaction snapshot and a changed parent can raise
40001; whole-transaction retry is mandatory. All authority-changing writers must
also provide a parent-update conflict/fence, or use RC: MySQL's parent-first RR
snapshot timing must not be assumed identical on PostgreSQL.
**Verdict:** smallest if the nested/old-view contract can change; not a solution
to successfully completing valid new requests within the existing stale RR caller.

### C. Operation-scoped READ COMMITTED + parent mutex + ordinary child reads — viable new isolation contract

```sql
-- Only before a new, deliberately owned financial transaction:
SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- no SESSION/GLOBAL keyword
START TRANSACTION;
SELECT * FROM commerce_payment_allocations WHERE id=:a FOR UPDATE;
-- Existing version CAS.
-- Plain replay and all complete section-4 authority reads; same INSERT.
COMMIT;
-- Subsequent transactions use the unchanged session default.
```

**Locks:** parent record X; no child-read gaps. RC disables search/scan gap locks,
not duplicate-key/FK checking; those remain appropriate backstops. B has distinct
parent/request identities and no A-held shared supremum search lock blocking it.
**Freshness:** each plain query uses a new committed snapshot plus own writes.
The participating parent mutex makes all relevant authority stable across these
queries; RC alone is not sufficient. Empty sets and commits before acquiring the
mutex are handled without missing-key locks. Later same-allocation writers wait.
**Replay:** fresh matching-key read precedes capacity; changed immutable parameters
still fail. **Rollback:** same connection preserves own writes and savepoints
within a transaction known to have started RC. Root deadlock retries must reapply
the one-transaction setting before **each** new BEGIN; setting it once outside
Laravel's three-attempt transaction helper would let a later retry revert to RR.
Timeouts require rollback, never continued partial financial authority.
**Boundary:** MySQL rejects `SET TRANSACTION` inside an active transaction (1568).
A savepoint is not a new isolation boundary. Existing active RR callers must abort
the requested reservation without writes and retry the entire unit in an approved
RC financial transaction. No silent upgrade, independent inner commit or session
toggle. RC on an outer transaction applies to the whole outer transaction, not
just this method; do not claim arbitrary nonfinancial outer work is unaffected.
An unused one-shot setting must not leak to another operation; abandoned connection
setup must be disposed/reset under an explicit connection-lifecycle contract.
**Changes:** no schema/index, identity or accounting equation changes; a real
transaction-isolation/composition change requiring approval. MySQL RC also requires
row-based binary logging when logging is enabled; production configuration remains
UNKNOWN and is not inspected. SQLite follows its existing writer/busy behavior.
**PostgreSQL:** idiomatic RC + same parent lock + ordinary reads; no InnoDB gap
lock counterpart. PostgreSQL error handling/savepoint and serialization rules still
need native verification.
**Verdict:** sound standard pattern, but not within the unchanged RR approval.

### D. Separate fresh/autocommit read connection under owner's parent mutex — reject as general fix

**Sequence:** writer remains in old RR transaction and locks A; another connection
performs plain autocommit queries for replay and authority; writer INSERTs.
**Locks/B:** no read gaps, B can progress. **Empty/commits:** sees other transactions'
committed empty/nonempty state freshly, but **cannot see writer's own uncommitted
reservations/effects/context changes**. Two calls in the same outer transaction can
both subtract zero reservations and over-authorize. Replay of the writer's own
uncommitted key is also missed. **Rollback:** reader has no financial writes to undo,
but cannot restore caller atomic authority semantics; separate writer commit is
forbidden. A complete own-write overlay would become another maintained-authority
protocol, not this simple strategy. **Changes:** no schema by itself; unacceptable
transaction/visibility semantics; keep SQLite unchanged. **PostgreSQL:** same
cross-connection own-write invisibility. **Verdict:** not general containment.

### E. Point/unique reads alone or snapshot-enumerated IDs — insufficient

**Sequence:** lock A; discover child IDs with ordinary RR query; fetch each known
ID `WHERE id=:id LOCK IN SHARE MODE`; unique request lookup with SHARE.
**Locks:** known-existing complete unique keys are record-only, but an absent
request still takes a gap; partial unique prefixes/ranges remain next-key.
**Freshness/empty/commits:** point reads refresh enumerated rows on MySQL, not IDs
missing from the old enumeration. An old empty set cannot discover a committed
reservation/effect. B can progress only if every probe is known-existing; the
ordinary missing-request probe defeats that claim. **Replay:** old enumeration can
omit a retained key. **Rollback:** savepoints undo DML, not omitted authority;
missing-probe gaps can survive rollback-to-savepoint. **Changes:** no schema by
itself, no equation/identity changes, SQLite unchanged. **PostgreSQL:** point locks
do not refresh an RR snapshot; changed rows can raise 40001.
**Verdict:** useful component only with complete current membership (strategy G).

### F. More covering/allocation-leading indexes + same RR child locks — not a correctness remedy

**Sequence:** candidate queries from section 3, force a new narrower/covering index,
retain SHARE/UPDATE. **Locks:** searched ranges still receive gap/next-key locks;
empty key intervals still reach an adjacent key/supremum. Fewer clustered fetches
do not remove the physical insertion boundary. **Freshness/commits:** current
capacity reads are fresh, including new effects, but B progress and the empty-set
case remain unproved and can fail. **Replay:** absent full UNIQUE key still gaps.
**Rollback:** same RR retained-gap/savepoint and root-retry requirements.

No index is recommended for correctness. If a later measured workload warrants
a performance-only proposal, exact illustrative additive indexes are:

```sql
CREATE INDEX pfo_alloc_kind_state_ctx_cover
ON payment_financial_operations
  (allocation_id, kind, state, context_id, id, amount_units);
CREATE INDEX pfle_alloc_ctx_kind_cover
ON platform_fee_ledger_entries
  (allocation_id, collection_context_id, effect_kind, id, exact_amount);
```

The first covers context-specific counted units; the second targets context-specific
effect sums. Cross-context ordering can still require a sort. Neither prevents
empty-range gap contention, replaces the retained UNIQUE keys, nor makes an absent
replay lookup safe. An index on allocation alone already exists implicitly as
leading parts of these indexes; adding another does not create one physical gap
per logical allocation.
**Migration:** separately approved additive indexes; preflight existing names/
definitions, write/storage cost, version-specific online DDL/metadata locks;
retain all existing unique/FK/guard constraints. No migration, index or owned
schema change is made. **Meaning/SQLite:** no monetary identity/equation change,
but adding shared-driver indexes would change SQLite schema and need approval.
**PostgreSQL:** use ordinary btree or key columns with INCLUDE for payload columns;
it has no matching InnoDB next-key blocking guarantee to optimize away.
**Verdict:** not the safest correction to this failure.

### G. Complete parent membership manifest + current existing-key point reads — recommended retained-RR design

Proposed new metadata on the **existing allocation row**, not a new financial
identity or money balance: `operational_evidence_manifest` (MySQL JSON, initially
nullable during a separately approved staged migration).
NULL/unvalidated/incomplete manifest cannot authorize an operation. Do not repurpose
`native_components`, original amounts, `original_vendor_payable` or `version`.
Those frozen economics/version fields are not an inventory of current child rows.

The complete manifest must contain exact canonical child identifiers:

- all allocation context primary keys, including not-yet-confirmed contexts;
- all allocation ledger/effect primary keys;
- all retained operation primary keys, **including terminal operations for replay**;
- an explicit original-attempt binding for each applicable funding event/context,
  identifying an existing attempt primary key or a verified permitted absence.

IDs are exact strings, not floating-point monetary data. No pruning of retained
request/effect identity is allowed. For a funding event spanning multiple allocations,
the appropriate bound attempt membership must be installed in every affected parent
while taking those parents in deterministic order. Those allocations are genuinely
related, not an unrelated B in the independent-progress invariant.

**Creation protocol, inside the existing writer transaction:**

```sql
SELECT * FROM commerce_payment_allocations WHERE id=:a FOR UPDATE;
-- Read current manifest from that locked row; validate it.
INSERT INTO <existing_child_table> (...) VALUES (...); -- real existing identity
UPDATE commerce_payment_allocations
SET operational_evidence_manifest=:complete_manifest_with_child_id,
    version=version+1, updated_at=:now
WHERE id=:a AND version=:expected_version;
-- Both child and its membership commit or roll back together.
```

Integrate this with existing version/CAS rather than blindly adding a second
increment. Context/operation/attempt creation and ledger group append must update
membership atomically, under the same parent locks. Changing counted state or
eligibility still follows the existing parent protocol. Dispatch changes within
the counted set must not release capacity.

**Reservation read protocol in the SAME connection/caller transaction:**

```sql
-- BEGIN or existing valid caller SAVEPOINT; keep REPEATABLE READ.
SELECT * FROM commerce_payment_allocations WHERE id=:a FOR UPDATE;
-- Existing version/CAS and read manifest from this CURRENT parent row.

-- For each KNOWN-EXISTING operation ID, in deterministic order:
SELECT p.*, (p.kind=:kind AND p.request_key=:key) AS replay_match
FROM payment_financial_operations AS p WHERE p.id=:operation_id LOCK IN SHARE MODE;
-- Find retained (allocation,kind,request_key) in this complete current set.
-- Compare amount/actor/context and return replay BEFORE new capacity.
-- replay_match uses native column equality; do not expose this helper in the DTO.

-- For each manifest context/effect/attempt ID, current point-read its PRIMARY:
SELECT * FROM payment_collection_contexts WHERE id=:context_id LOCK IN SHARE MODE;
SELECT * FROM platform_fee_ledger_entries WHERE id=:effect_id LOCK IN SHARE MODE;
SELECT * FROM electronic_collection_attempts WHERE id=:attempt_id LOCK IN SHARE MODE;

-- Validate allocation/event membership and states AFTER fetching the point row.
-- Do not add state predicates that turn an existing-key read into a missing result.
-- Apply exactly section 4 / current AllocationBalances equations.
INSERT INTO payment_financial_operations (...) VALUES (...);
-- Atomically append this REAL operation ID to the locked parent manifest.
-- No missing-key FOR SHARE replay search, child range scan, or dummy operation.
-- COMMIT, or release the service savepoint without independently committing caller.
```

An implementation may optimize point fetches only after proving equivalent
known-existing unique access/locks; do not assume a broad `IN (...)` plan has the
same lock footprint. Individual primary-key queries provide the baseline proof.

**Locks:** A's existing parent X; S record-only for known-existing complete primary
keys, normal insert-intention/unique/FK locks for its real insert. There are no
deliberate child-range or missing-key authority locks. B's primary row/child keys
are different; compatible insert intentions in sparse/empty indexes do not inherit
A's candidate shared supremum searches. Shared existing actor/revision FK records
are compatible, not a financial serialization boundary.

**Stale snapshot:** the locking parent read obtains current committed manifest plus
the transaction's own parent writes. It supplies complete current child membership,
not an old consistent-read enumeration. Current existing-key child reads supply
current values, including rows committed after the old RR snapshot and own inserts.
Thus the old zero-SUM cannot hide the owner’s 7000 reservation or a new refund effect.
The existing allocation mutex keeps participating same-allocation writers out
until capacity and new membership are committed.

**Empty set:** a validated empty list means no child records exist under the
protocol; no SQL range/absence lock is needed. When A adds the first operation,
its real INSERT and parent membership update are atomic. B's different empty
manifest does not query A's supremum. An absent request is established by the
complete manifest-backed current operation set, not by a missing-key locking lookup.

**After another commit:** winning the parent lock exposes the newly committed
manifest and existing IDs; after own uncommitted inserts, the same transaction
also sees its updated manifest and rows. The 3000 remainder can succeed inside
the stale RR caller after rejecting 7000, without discarding that caller's root.

**Replay:** retain all operation IDs forever; filter the complete current rows by
unchanged native request-identity equality/collation, compare immutable bindings
first, then return retained
operation regardless of depleted capacity. Existing UNIQUE remains a backstop.
No operation UUID is made deterministic or rebound. A PHP map must not silently
make UUID/request equality case-sensitive where the existing SQL key comparison
is not, or normalize/rewrite retained keys. Match native equality explicitly.

**Rollback/savepoints/deadlocks:** parent version/manifest and inserted operation
undo together. Valid savepoint retry reads own remaining parent state and current
known rows, not cached metadata from an aborted attempt. InnoDB may retain
record locks after savepoint rollback; they are scoped to A's existing evidence,
not the candidate empty-range supremum. Root deadlock invalidates the whole
transaction: surface it to the root owner and retry the full unit with the same
request identity. Timeout must not leave partial manifest/version/authority.
Never cache a manifest across transaction retries or blindly retry a deadlocked
Laravel savepoint.

**Integrity conditions:** list completeness, no duplicate IDs, correct table/type/
allocation/event binding, retained identities and atomic maintenance are mandatory.
JSON references do not acquire relational FK enforcement merely by being listed.
Iterating the listed IDs cannot detect a silently omitted child. Completeness must
be established at every canonical creation/write boundary, not claimed from a
“valid JSON” check. The approved implementation would need an enforced central
writer/transaction protocol or separately designed database maintenance/guards,
including a fail-closed initialization/admission gate. If any canonical writer can
commit a child without registration, this recommendation is NOT safe.
Initialization of a newly created canonical parent installs a validated empty
manifest; NULL on an existing parent never means empty.
Known-ID existence depends on existing child FKs/retention rules plus atomic
maintenance. Corrupt/missing/uninitialized references fail closed; do not fall back
to stale scans, silently repair membership or continue a corrupted financial root.
Root rollback is required after a missing-ID point lookup that may have taken
a gap lock. Validation must explicitly test this failure path.

Registration must capture actual existing child identities. For bulk ledger groups,
do not infer auto-increment IDs from a consecutive-number assumption; capture each
inserted ID or read its known-existing retained unique effect identity within the
same transaction. Attempt membership must not be inferred from a missing-key probe.
The manifest grows with retained evidence and rewrites a hot parent value.
Point reads add round trips. Measure row/document limits, write amplification and
large-history cost before selecting an implementation; never prune terminal replay
identities to make a performance test pass. A compact chain/head design is a distinct
schema proposal, not a silent substitute for complete membership.

**Changes:** one proposed additive parent field and maintenance in every relevant
child-creation path; staged initialization/validation/guards and migration approval.
No new index is required for the point reads; existing PRIMARY keys suffice.
No identity, provider behavior, integer equation, original custody, commission or
liability semantics changes. It introduces a maintained current-membership authority
contract, so it is a material design change, not “just a read optimization.”
SQLite may retain its accepted non-manifest transaction path with MySQL-only
metadata hooks; shared-driver schema changes would require explicit SQLite approval.
Do not assert SQLite semantics tested in this design-only turn.

**PostgreSQL:** same parent inventory and known-row point strategy is representable
with JSONB/explicit constraints and ordinary primary-key locks. PostgreSQL RR is
not MySQL current-read RR: a parent changed after its snapshot causes 40001 rather
than exposing the latest manifest. Retry the full root; never downgrade/ignore that
error. Authority changes must update/fence the parent, including metadata-neutral
changes where needed, or use deliberately scoped RC. Native verification and guard/
DDL porting are separate; no PostgreSQL certification is claimed.

**Verdict:** supports the existing MySQL RR/nested/own-write contract without child
absence/range locks. Larger than a transaction-boundary change but smaller in
monetary responsibility than maintaining another full money-balance projection.
Requires approval and a validated write protocol, not immediate implementation.

### H. Maintain current monetary capacities/counters on the locked parent — viable larger maintained-state design

**Sequence:** parent primary `FOR UPDATE`; current typed parent values for per-context
returned/reserved funding and allocation-wide payable/receivable/reservations;
fresh replay from a complete retained-key mapping/known-existing point record;
apply unchanged equations/exclusions; real INSERT plus atomic parent values UPDATE.
Every confirmation/effect/state change updates the same parent values in its
transaction under the parent mutex.

```sql
SELECT * FROM commerce_payment_allocations WHERE id=:a FOR UPDATE;
-- Read proposed operational_capacity_state and complete current replay membership.
SELECT p.*, (p.kind=:kind AND p.request_key=:key) AS replay_match
FROM payment_financial_operations AS p WHERE p.id=:known_existing_id LOCK IN SHARE MODE;
-- Validate original immutable bindings; return replay, otherwise same capacity math.
INSERT INTO payment_financial_operations (...) VALUES (...);
UPDATE commerce_payment_allocations
SET operational_capacity_state=:exact_post_state,
    operational_evidence_manifest=:retained_identity_membership,
    version=:next_existing_cas_version, updated_at=:now
WHERE id=:a AND version=:expected_existing_cas_version;
-- One transaction/savepoint owns every write; no independent inner COMMIT.
```

`operational_capacity_state` is another proposed new field, not an existing original
balance column. It must represent all per-context and allocation-level quantities
required by the current equations, with exact integer units and counted-state
membership; `:exact_post_state` is not permission to substitute simpler equations.
**Locks/B:** parent and known existing replay record locks only; no child range
searches. B uses another parent. **Freshness/empty/commits:** locking parent returns
current maintained values and own writes, including legitimate zero values; prior
commits are included atomically. No stale child SUM participates.
**Replay:** counters alone are insufficient: missing-key replay SHARE recreates the
same gap problem. A complete current request-to-operation mapping is also needed.
**Rollback:** counter/operation/version changes must undo together for full and
savepoint rollback; state transitions UNKNOWN/PENDING never release authority.
Root deadlock retry reloads all values and original identity.
**Changes:** existing original financial fields cannot store mutable operational
balances. Needs new typed fields or exact-integer-string JSON, initialization,
invariant checks and maintenance across all authority-changing writers.
Projection math, rounding and effect reversal relationships must stay exactly
equivalent to existing equations; a naive “remaining money” counter changes meaning.
SQLite would need conditional non-counter behavior or a separately proved equivalent
implementation. PostgreSQL supports atomic current-row updates at RC; RR may abort
with 40001 and require full-root retry.
**Verdict:** can establish both invariants with comprehensive maintenance, but
greater drift/accounting-sync risk and sibling write scope than membership + original
equations. Not the minimum recommendation.

### I. Existing parent version as optimistic freshness fence — insufficient as-is

**Sequence:** ordinary snapshot parent version; current parent lock; compare versions;
if equal, ordinary child reads; otherwise abort/retry root.

```sql
SELECT version FROM commerce_payment_allocations WHERE id=:a; -- old consistent view
SELECT * FROM commerce_payment_allocations WHERE id=:a FOR UPDATE; -- current MySQL row
-- Compare before own version mutation; mismatch aborts the requested financial unit.
-- Equality is usable only with a universal epoch/own-write protocol (absent today).
-- Otherwise ordinary child SELECTs remain unsafe.
```
**Locks/B:** parent record only, so independent B can progress; no read gaps.
**Freshness/empty/commits:** equal versions prove freshness only if EVERY monetary/
eligibility change increments an epoch covering those children, and the comparison
cannot be masked by earlier own parent writes. Existing version is not that contract:
`AccountingEffects::append` increments it conditionally after refund, not universally
for every settlement/recovery append. Own writes can also make an ordinary parent
read appear current while child rows remain from an older view.
**Replay:** a valid fence would cover retained-key membership before capacity;
today it cannot guarantee that. **Rollback:** full retry can refresh; a savepoint
cannot refresh the consistent-read view. **Changes:** a rigorous epoch protocol
needs new maintained rules across siblings and caller own-write handling, not merely
another version comparison. No monetary equation change intended; SQLite unchanged
unless the protocol is generalized. PostgreSQL needs parent updates covering every
change to obtain the expected RR serialization conflict.
**Verdict:** not a safe read-only correction using today's version field.

### J. DML-derived reads, lock modifiers and serialization substitutions — reject

**DML-derived read sequence:** parent lock then `INSERT ... SELECT`/`UPDATE ...
(SELECT ...)` over child authority. These are not an ordinary nonlocking “current
SELECT” escape hatch: MySQL documents stronger selected-table locks under RR,
and INSERT-SELECT shared next-key locks. Conditional parent UPDATE with child
subqueries is not accepted as record-only/current evidence without an independent
native proof; no such proof exists here. Empty sources can still require absence
protection. Replay and unchanged projection math remain necessary. Savepoint/root
rollback does not remove the original broad-lock concern. No schema necessarily,
but SQL/trigger/driver behavior and SQLite/PostgreSQL portability change.

**NOWAIT/SKIP LOCKED sequence:** apply modifiers to candidate child queries.
NOWAIT rejects on contention rather than enabling B's required progress.
SKIP LOCKED omits authority and can undercount; empty result does not mean zero
committed reservations. Neither removes every gap/insert boundary. Replay of a
locked retained key can be missed. Rollback cannot make omitted capacity correct.
No schema, but unacceptable capacity/read semantics; SQLite has no equivalent
scheduling contract. PostgreSQL also treats SKIP LOCKED as an incomplete result.

**Global/per-allocation advisory/table lock substitution:** acquire it then ordinary
old RR reads. Per-allocation advisory lock does not refresh a read view; a global
mutex/LOCK TABLES prevents unrelated B or implicitly changes transaction boundaries.
SERIALIZABLE on MySQL adds shared locking scans rather than eliminating this gap
problem. An advisory lock does not replace atomic retained replay/version/rollback
without another protocol; MySQL named-lock lifetime is not automatic transaction
rollback. No schema necessarily, but isolation/serialization/SQLite behavior changes.
PostgreSQL SSI uses nonblocking predicate locks and abort/retry, not InnoDB-style
gap prevention; it is a different, separately scoped strategy, not this repair.
**Verdict:** none is selected, implemented or certified.

## 6. What would need approval and validation for the recommendation

Scope a proposal separately to membership metadata, not financial-semantic fixes:

1. Exact parent manifest schema, integrity/initialization rules and bounded writer
   inventory. No automatic legacy classification, economic backfill or migration
   of original frozen values. Existing canonical rows, if any, need a separate
   complete-membership preflight/init plan; NULL does not mean an empty manifest.
2. Atomically maintain current membership for real context/operation/effect/attempt
   creation and preserve all existing CAS, immutable/FK/unique guards. Shared funding
   events must keep correct cross-allocation binding and deterministic parent order.
3. Reservation-only manifest/point authority reader, current unique-key access,
   same equations and replay-before-capacity. Do not extend its claimed certification
   to sibling finalization, Wallet, receipt, confirmation or fulfillment.
4. Retain the old-snapshot 10000 / 7000+7000 rejection assertions. Add exact 3000
   success once **within the still-open old-snapshot transaction** and total 10000.
   This distinguishes a working current authority design from rejecting all nesting.
5. Keep independent B success before A commit; test initially empty operations,
   sparse/nonempty indexes, reversed allocation/key order and multiple contexts.
   Capture live `data_lock_waits`, transaction/lock IDs and underlying native error
   before cleanup. Verify no owner child-range/missing-key supremum search locks.
6. Verify new committed effects; payable/receivable capacity; original exclusions;
   complete retained replay (including terminal), changed amount/actor/context;
   own uncommitted writes; rollback/savepoint/version/membership cleanup; counted
   UNKNOWN/PENDING; deadlock/timeout root behavior; initialization/corruption gates.
7. Verify unchanged SQLite semantics and restart the unchanged original thirteen
   MySQL cases from case one. Do not relabel the retained 13/51 as a new pass.
8. Stop for any sibling invariant failure before modifying that financial path.
   Approval of membership hooks would not authorize correcting sibling accounting.

No prototype, schema patch, test relaxation or implementation is made in this turn.

## 7. Protected-state verification and source boundary

Read-only PDO/`PRAGMA query_only=ON` snapshot matches the original protected
baseline exactly: **59 tables, 465 schema objects, four evidence tables with zero
rows, 12 legacy Orders total and all 12 unverified**.
Existing final application files remain pre-candidate source. Test/evidence files
are unchanged during this design review. No production access, real credentials,
provider calls, activation, cleanup/classification of legacy records or publishing.

## 8. Official database references

These references support the design reasoning; they are not new runtime tests.
The retained runtime is MySQL 8.0.42. Production configuration remains unknown.

1. [MySQL 8.0 consistent nonlocking reads](https://dev.mysql.com/doc/refman/8.0/en/innodb-consistent-read.html):
   RR first-read view, own-write exception, no in-place snapshot refresh by parent lock.
2. [MySQL 8.0 locking reads](https://dev.mysql.com/doc/refman/8.0/en/innodb-locking-reads.html):
   current locking values and incomplete SKIP LOCKED results.
3. [MySQL 8.0 locks and supremum](https://dev.mysql.com/doc/refman/8.0/en/innodb-locking.html):
   gap/next-key/insert-intention behavior, compatible gap locks still inhibit insert.
4. [MySQL 8.0 locks by statement](https://dev.mysql.com/doc/refman/8.0/en/innodb-locks-set.html):
   found unique record versus range locks, INSERT/duplicate/FK/source-table locks.
5. [MySQL 8.0 transaction isolation](https://dev.mysql.com/doc/refman/8.0/en/innodb-transaction-isolation-levels.html):
   RR/RC read views, remaining RC FK/duplicate gaps, RC binary-log constraint.
6. [MySQL 8.0 SET TRANSACTION](https://dev.mysql.com/doc/refman/8.0/en/set-transaction.html):
   next-transaction-only scope, automatic reversion, active-transaction rejection.
7. [MySQL 8.0 savepoints](https://dev.mysql.com/doc/refman/8.0/en/savepoint.html):
   rollback-to-savepoint generally retains stored locks; it does not reset the view.
8. [MySQL 8.0 error handling](https://dev.mysql.com/doc/refman/8.0/en/innodb-error-handling.html):
   native deadlock rolls back the root; default timeout rolls back the failed statement.
9. [PostgreSQL transaction isolation](https://www.postgresql.org/docs/current/transaction-iso.html):
   RC statement snapshots, RR/SSI snapshots and whole-transaction serialization retry.
10. [PostgreSQL explicit locks](https://www.postgresql.org/docs/current/explicit-locking.html):
    tuple-lock behavior, savepoint lock release and advisory-lock lifetime differences.

**STOP — design recommendation only. Current stale-snapshot defect remains;
no replacement containment is installed or financially certified.**