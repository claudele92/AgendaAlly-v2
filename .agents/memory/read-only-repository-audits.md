---
name: Read-only repository audit boundaries
description: Bounded source/history inspection without treating ignored runtime snapshots or host ignore rules as portable policy.
---

Inventory source and runtime metadata separately; do not read every ignored
release snapshot, cache and binary as if it were authoritative source.

**Why:** Full workspace traversal/content reads repeatedly exceeded bounded
audit time on the accumulated local release and build caches.

**How to apply:** Start with tracked paths, identify ignored source helpers and
secret/data candidates separately, prune dependency/SDK caches, and disclose
uninspected binary/large content. Keep helper processing outside the workspace
for read-only requests.

Repository ignore safety must be portable, not dependent on host-global excludes.

**Why:** The workspace's global ignore file hides generic logs, private agent
state and configuration that the checked-in rules alone do not cover.

**How to apply:** Compare repository/child rules with global excludes and actual
tracking; do not mistake ignored runtime material for safe-to-publish content.

Verify an independent Git root before interpreting a nested snapshot's history.

**Why:** Restored sanitized source directories lacked their own `.git`, but
`git -C <snapshot>` silently discovered the parent workspace repository.

**How to apply:** Check the resolved Git top-level against the intended directory.
If metadata is absent, fingerprint source files and report history unavailable;
never reconstruct or certify the prior independent history from parent results.
