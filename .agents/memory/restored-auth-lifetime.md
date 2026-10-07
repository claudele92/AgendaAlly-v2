---
name: Restored authentication lifetime
description: Preserve encryption keys while explicitly invalidating restored session/token/challenge authority in quarantine.
---

Preserve original encryption/authority keys through restore. Invalidate restored
authentication records explicitly while the recovered application is quarantined;
do not regenerate keys as a substitute for session or challenge invalidation.

**Why:** Same-key recovery correctly restores encrypted financial/email evidence
but can also restore valid old sessions, tokens and key-bound challenges. Key
rotation would destroy retained encrypted authority rather than establish its
intended lifetime.

**How to apply:** Prove raw restored financial/schema/grant equivalence first.
Then allow only reviewed authentication invalidations, retain passwords, grants
and financial/outbox evidence, and verify native stale-authority denial. This does
not certify full HTTP finance-session security or approve production activation.
