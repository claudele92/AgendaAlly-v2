---
name: MySQL restored DDL equivalence
description: Preserve raw schema mismatch evidence and prove only known redundant DDL syntax is semantically equivalent.
---

MySQL dump/import can make SHOW CREATE TABLE emit a redundant per-column
CHARACTER SET before the same explicit COLLATE. Raw DDL hashes can therefore
differ even when the native schema is equivalent.

**Why:** An actual isolated restore matched all selected-point rows and financial
invariants but initially failed a schema hash on an operational probe. Native
column contracts established the redundant syntax difference; this was not a
missing or changed field.

**How to apply:** Retain the raw mismatch/failure. Require exact column contracts
(types, precision, nullability, defaults, charset/collation, generated values),
indexes, constraints and triggers, plus narrowly canonicalized DDL for the
identified difference. Never blanket-ignore schema hashes, AUTO_INCREMENT or
arbitrary text differences to make a restore pass. Compare captured evidence at
the selected point, not a later live source changed by operational drills.