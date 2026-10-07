---
name: MySQL lab supervision
description: Long native first-install proofs need supervision independent of foreground tool deadlines
---

Run long native MySQL proof supervisors as background jobs with persistent,
incremental evidence, rather than relying on a foreground command to survive
until all DDL completes. Verify final privilege revocation and shutdown explicitly.

**Why:** A foreground command deadline interrupted an otherwise progressing
first-install lab, leaving stale socket/PID artifacts and temporary bootstrap
authority that required isolated final cleanup. A shell EXIT trap alone did not
guarantee cleanup when the entire process group was terminated.

**How to apply:** Freeze authority and source inputs before creating the lab;
keep per-migration native-ledger receipts. Treat interrupted DDL as unaccepted
partial state, not permission to replay or repair it. Restrict post-interruption
inspection/revocation to the owned lab and do not touch other MySQL listeners.
