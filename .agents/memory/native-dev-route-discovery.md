---
name: Native development route discovery
description: Distinguishing runtime route-registry failures from page-level guards or backend lookup failures.
---

Prove which layer emits a native page's 404 before changing routing or guards.
Inspect the active development runtime, not a previous production build.

**Why:** A valid shop slug resolved through Laravel and its booking/payment
source existed, yet Next's running development matcher failed before executing
the page. Its lookup received the concrete URL instead of the dynamic route.
Restarting only that development server restored the same URL without source
changes. The original broad page catch misleadingly suggested an API failure,
and stale production output was not evidence of active development behavior.

**How to apply:** Separate framework route discovery, page execution, actual
API responses, authentication and missing client-side context. In Next 16,
active development output is under `.next/dev`; `.next/server` may belong to
an older production build. Do not weaken a legitimate not-found/ownership guard
to compensate for a runtime route-discovery problem, or claim the undocumented
trigger of stale runtime state is known merely because a restart resolves it.

Recheck live preview identity at each externally authorized acceptance phase,
not just when the campaign begins.

**Why:** A previously correct normal Customer preview later stopped while a
selected preview held the shared Next lock. Source/configuration had not changed,
but the previous routing proof was no longer evidence of the owner's live runtime.

**How to apply:** Check workflow state and the active development rewrite before
issuing time-limited account challenges. If restoring the intended runtime would
pause an unrelated preview, obtain authority for that pause rather than silently
testing against a different backend.