---
name: Native navigation scope
description: Why Stage 1 navigation uses verified self scope rather than broad role menus or an invented global branch selector.
---

Treat native role menus as presentation, not effective permissions. Navigation
must use authenticated server-resolved grants/context while existing Laravel
authorization remains authoritative. Keep legacy REST resource fields intact
when adding navigation information.

**Why:** Owners and invited shop staff shared a role menu, while country staff
used broad manager roles. Those lists would incorrectly imply ownership or
platform-wide access. The original self responses did not expose complete
effective permissions, so Stage 1 uses a separate additive read-only self
context rather than altering existing login/profile contracts.

**How to apply:** Missing, ambiguous, stale or actor-mismatched scope must not
produce operational menus through a role-based fallback. Shop association is
not ownership; business country geography is not country-administrator scope.
Menu visibility never replaces backend permission checks.

Apply the same verified grants to privileged shared startup reads, not only
to menu links. Ordinary formatting preferences should use their existing
public catalogue instead of a management endpoint.

**Why:** A country manager successfully authenticated, but an unconditional
currency-management request received a legitimate permission denial and the
legacy client cleared the session. Correct menu filtering alone could not
make that restricted workspace usable.

**How to apply:** Resolve current actor scope before protected bootstrap or
dashboard reads. Keep permission expansion and global error-policy changes
separate from navigation work. Cache invalidation must follow the same
identity/session boundary as refetching; a same-session profile setter must
not erase data or pending requests without scheduling replacement work.

Keep branch permissions resource-specific and follow the complete serialized
read chain, not only the top-level query.

**Why:** An appointment-wide branch permission is not a grant to read every
branch's management record. An authorized eager-loaded location collection
also does not constrain a serializer that independently looks up a matched
location from caller-supplied geography.

**How to apply:** Scope restricted reads before pagination and ensure derived
resource fields use the same authorized set. Preserve public marketplace
metadata separately. Ordinary specialist invitations may have no staff role;
do not exclude legitimate specialist availability by imposing manager-role
requirements on candidate records.

Do not introduce a global branch switch merely for visual fidelity.

**Why:** Native modules have local branch selections and assignments, but no
shared active-branch state honored by all APIs. A sidebar selection alone would
misrepresent scope or silently change which work is displayed.

**How to apply:** Display verified all/assigned/unassigned branch scope honestly.
Preserve module-specific selection. A future shared context switch requires
explicit approval and consistent server-validated support across affected
modules, not only a new navigation control.