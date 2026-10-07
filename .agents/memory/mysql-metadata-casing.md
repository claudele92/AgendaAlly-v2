---
name: Native MySQL metadata casing
description: PDO information_schema field casing must be explicit in native evidence collectors
---

Do not assume lowercase information_schema result keys merely because the
SELECT identifiers were written in lowercase. Use explicit aliases or an
explicit PDO case-normalization policy in evidence collectors.

**Why:** MySQL returned DEFINER, EVENT_OBJECT_TABLE and ACTION_STATEMENT to a
collector that indexed lowercase names. Native migrations had all committed,
but the collector raised an ErrorException before portable initialization.

**How to apply:** Check the collector's actual fetch keys independently from
DDL acceptance. Distinguish evidence-harness failures from migration/application
failures; never repair or replay a partial installation to hide a collector bug.
