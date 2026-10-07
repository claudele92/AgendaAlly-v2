---
name: MySQL backup loader authority
description: Consistent backup options must also satisfy the independently reviewed restore identity's execution privileges.
---

Qualify both backup consistency and the emitted import commands against the
approved restore-loader privilege contract before claiming a recoverable backup.
Do not broaden privileges or replay a partially imported schema to hide a failure.

**Why:** A synthetic logical backup was consistent but its default emitted
restore-side table locks required a privilege absent from the reviewed bootstrap
policy. Read-side snapshot/lock options did not establish loader compatibility.

**How to apply:** Keep failed dumps/import evidence unchanged, retain real
failure exits and require approval for a new empty restore target. Treat empty
bootstrap, populated additive upgrade and isolated recovery as separate gates.
