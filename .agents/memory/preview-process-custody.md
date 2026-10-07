---
name: Preview process custody
description: A failed managed start can coexist with an older healthy preview listener.
---

Do not interpret a managed preview's EADDRINUSE failure as proof that its
application is down, or repeat an accepted account/mail journey to compensate.

**Why:** Automatic workflow starts attempted duplicate normal Customer/Laravel
listeners; their managed starts failed while the existing endpoints still
returned HTTP 200. Other automatically started processes were not evidence of
bounded operational approval.

**How to apply:** Check the exact listener and intended route before restarting
or interrupting services. Keep native runtime/route binding distinct from
workflow status, preserve owner acceptance, and never infer worker/scheduler
authority from an automatically running process.

Normal and acceptance Customer previews cannot run concurrently when they share
the native source and Next development cache, even on different ports.

**Why:** The shared development lock can block the normal storefront while the
acceptance frontend owns the cache. Different API targets/ports do not establish
independent runtime custody.

**How to apply:** Check both source/cache ownership and listener health. Preserve
the normal demo's backend provenance; do not retarget it to an acceptance fixture
to bypass the conflict or repeat completed acceptance journeys.
