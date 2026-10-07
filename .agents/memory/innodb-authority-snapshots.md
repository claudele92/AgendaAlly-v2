---
name: InnoDB authority snapshots
description: Current allocation locks do not refresh preexisting repeatable-read monetary snapshots.
---

An allocation row lock/version claim is not enough to establish fresh monetary
authority when a caller already has an InnoDB REPEATABLE-READ consistent-read view.
Current locking reads and ordinary monetary aggregates can observe different states.

**Why:** Actual disposable MySQL testing reproduced excessive durable refund
reservations with a current allocation lock but an older reserved-value SUM.
Prepared lock-wait-then-fresh-transaction replay checks passed and missed this.
This was a native service transaction test, not proven route-level exploitability.

**How to apply:** Exercise an established reader snapshot across another connection's
commit, not only lock timeout and retry in a new transaction. Keep existing isolation
and financial meaning; obtain required approval before fixing operational reads.
Do not claim public API exposure or actual refunds from reservation-only evidence.

Current locking reads also need a physical lock-boundary test, not just a cap test.
Allocation-leading index predicates and FORCE INDEX do not eliminate InnoDB
REPEATABLE-READ missing-key/next-key/supremum locks.

**Why:** A disposable candidate correctly rejected the stale excess reservation
but blocked another allocation's insert after that allocation acquired its own
parent mutex. Empty operation ranges retained shared supremum locks.

**How to apply:** Test distinct allocations while the first outer transaction stays
open, including initially empty child tables. Inspect access plans and live locks;
do not interpret normal IS/IX intention locks as table-wide money locks. Do not
claim cap correctness alone satisfies the user's independent-allocation boundary,
or introduce sentinel financial records/isolation/schema changes to bypass it.

A safe read/lock proposal must distinguish arbitrary caller snapshots from
transactions whose first financial consistent read follows the parent mutex.
Known-existing point reads need a complete current membership source; an old
snapshot's ID enumeration cannot supply it.

**Why:** Empty-key locks can cross allocation boundaries, while removing those
locks without replacing freshness recreates the over-reservation defect.

**How to apply:** Treat fresh-owned transaction contracts, transaction-scoped RC,
and maintained parent membership/authority as different approval choices. Do not
present a nested-call rejection or separate reader connection missing own writes
as a drop-in preservation of existing RR/savepoint behavior.

Before recommending permanent authority/membership metadata, establish whether
the product actually requires successful reservation in an already-old caller view.
A service's generic nested-transaction support and adversarial fixture do not by
themselves establish that requirement.

**Why:** Maintaining a second completeness protocol can be needless permanent
financial risk when an explicitly owned business transaction satisfies real callers.

**How to apply:** Trace transaction ownership and indispensable prior reads/writes,
then distinguish ownership/isolation changes from preserving arbitrary nesting.
On PostgreSQL, first locking SELECT freezes RR's view before a potential wait;
locking is not InnoDB-style current visibility. Require root retry and all-writer
conflict fencing for RR, or an approved RC unit, rather than assuming an engine
switch or a parent manifest refreshes snapshots.

READ COMMITTED's later statement freshness does not refresh PHP/ORM objects obtained
before a parent-mutex wait. Do not infer a sibling writer is safe because reservation
is parent-first or because its later queries run under RC.

**Why:** Native wait-through-commit confirmation on both engines exposed a pending
context object retained before the mutex and used against a now-finalized parent;
timeout-then-fresh-replay coverage had not exposed that interleaving.

**How to apply:** Examine materialization order as well as SQL isolation. Test successful
lock-wait completion across a competing commit, not only a losing timeout followed
by a new request. Require authoritative objects/read state inside the validated
business-unit boundary; do not weaken replay assertions or remediate beyond approval.