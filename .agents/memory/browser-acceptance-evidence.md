---
name: Browser acceptance evidence retention
description: Preserve critical browser proof incrementally, before long campaigns lose trace detail.
---

The owner reported that Customer logout required a manual browser refresh.
For authentication work, verify immediate changes across independent UI
consumers separately from authoritative server revocation.

**Why:** A logged-out UI can coexist with active server credentials, and one
hook instance's local state does not prove the rest of the menu updated.

**How to apply:** Use synthetic sessions when completed owner acceptance must
not be repeated. Distinguish browser reload from intentional automatic router
revalidation. Before treating a persisted-state failure as a product defect,
check whether the assertion tests the actual field or merely its wrapper. Wait for
request completion and the resulting DOM state before declaring failure.

Write sanitized acceptance receipts at critical checkpoints rather than only
at the end of a long browser campaign. Preserve screenshot IDs, viewport/body
measurements, authoritative payload fields and observed response statuses.

**Why:** A long campaign retained successful functional effects but lost
desktop Review/layout trace details across context changes. Later evidence
recovery could not honestly certify the missing responsive checks.

**How to apply:** Ask the tester to persist each critical result immediately.
Distinguish old pre-fix screenshots from current output and unresolved lost-ack
recovery from recovery attempted after a successful retry already cleared its
reference. Use existing server logs for missing HTTP statuses, but never
infer missing UI state or responsive measurements from database effects.

Compare each element's scroll width with its own client width, not the browser
viewport. A body narrower than the viewport due to scrollbar space is not
horizontal overflow. For sticky overlays, verify that normal scrolling exposes
every required control and final content before classifying an overlap as an
inaccessible defect.

**Why:** A responsive evidence pass initially marked contained modal states as
overflow and recoverable sticky overlays as defects; retained measurements and
scroll-end checks resolved both without changing application code.

For mutation-free authenticated campaigns, account explicitly for native token
usage timestamps in preservation. Record token counts and normalized non-usage
field fingerprints before the browser starts; keep full raw fingerprint
differences as evidence rather than silently exempting the whole auth table.

**Why:** Native authenticated GETs can update token usage metadata even when
every business/financial table and record count remains unchanged.

Classify owner-confirmed UI logout separately from server-side authentication
revocation. Preserve a completed owner journey without unnecessarily repeating
it, but do not infer invalidation merely from a signed-out screen.

**Why:** An owner-confirmed successful UI logout left account-bound bearer
records unrevoked. Accepting the UI report as server logout would have hidden
an authentication gap.

**How to apply:** Reconcile sanitized token metadata and retained server requests
read-only. State incomplete historical coverage explicitly. Preserve the UI
confirmation, keep server revocation unresolved when evidence conflicts, and
do not read credential values or silently delete tokens to make acceptance pass.
