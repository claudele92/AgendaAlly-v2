---
name: Temporal recovery outcome authority
description: Why an older backup cannot authorize repeating a financial or email effect.
---

Treat restored PENDING/UNKNOWN identities as unresolved, not failed. Require
independently retained outcome evidence joined to the original intent before
owner-approved reconciliation; even a verified observation does not by itself
authorize repeating the effect or releasing quarantine.

**Why:** A frozen-source restore can prove zero lost database writes while
certifying nothing about external outcomes newer than the backup. Restoring an
older point resurrects uncertainty even when a later outcome was successful.

**How to apply:** Keep callbacks, funding/refund/payout handlers, sends and
workers stopped during recovery. Database read-only quarantine blocks persisted
writes but cannot prevent an independently launched process from contacting an
external system. Distinguish synthetic simulator evidence from actual trusted
external evidence and same-host custody from off-host disaster recovery.

Independently retained observations may exist even when the corresponding
database outcome transaction rolled back. Preserve both the failed transaction
evidence and those observations; do not repeat dispatch just because the
database still shows uncertainty.

**Why:** A local temporal rehearsal retained its simulator observations before
a native fixture transaction failed. Transaction rollback did not erase the
independent observations, which is the same ordering risk real recovery must
consider without mistaking mock observations for actual external evidence.

**How to apply:** Distinguish missing database writes, missing independent
observations and conflicting observations during reconciliation. A failed
fixture/restore target remains evidence, never a target to repair or replay.

Global read-only quarantine freezes application writes, not sampled optimizer
statistics, and also blocks redundant privilege-changing cleanup.

**Why:** Native MySQL changed sampled index cardinality after synthetic outcome
writes despite subsequent read-only quarantine. A byte-strict metadata check
mistook that for authority drift; redundant cleanup DCL was correctly denied.

**How to apply:** Retain raw differences before interpreting them, compare exact
row/DDL/native definition authority independently of sampled statistics, and
keep verified locked-definer grants in place through shutdown. Do not disable
quarantine just to repeat already-completed privilege revocation.
