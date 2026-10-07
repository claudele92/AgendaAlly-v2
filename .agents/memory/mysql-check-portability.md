---
name: MySQL CHECK portability
description: Accepted SQLite guards can fail MySQL schema bootstrap before concurrency tests begin.
---

MySQL 8 CHECK expressions cannot reference AUTO_INCREMENT columns, although
SQLite permits the equivalent self-reference guard.

**Why:** Actual MySQL 8.0.42 rejected a retained receipt non-self-anchor guard
before any financial race could run. This was a schema portability failure,
not evidence that the invariant failed or that concurrency was safe.

**How to apply:** Compile the full accepted native DDL on the actual engine
before claiming concurrency certification. Preserve the database invariant;
never omit the CHECK or substitute an application precheck to obtain passes.
Any engine-specific equivalent guard must obey the creator's schema approval
boundary. Before-insert AUTO_INCREMENT values also require careful timing:
generated identity is available in AFTER INSERT, not a presumed NEW.id beforehand.

## InnoDB implicit FK index replacement

InnoDB can remove a legacy implicit foreign-key-supporting index after a newly
added composite/unique index also supports that FK. Dropping the new index
later must restore equivalent support first, not remove the FK.

**Why:** A complete native empty down/reapply encountered error 1553 even though
source up succeeded: the profile unique index had become the only payment FK
support. SQLite down behavior did not reveal the dependency.

**How to apply:** Inspect native FK columns and remaining leading index columns
when reviewing additive-index rollback. Preserve the original FK and physical
support; test the real legacy-FK schema, not only a thinner fixture without it.