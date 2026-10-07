---
name: Empty-state query guard verification
description: Avoid false guard coverage across anonymous and authenticated client paths.
---

Verify anonymous and authenticated empty states separately. Bind a query-guard
regression to the exact query key and service call it protects; a broad match
for an enabled predicate elsewhere in the component is insufficient.

**Why:** An empty-cart review missed the anonymous calculation path while
existing authorized guards gave false confidence. A subsequent report also
misidentified a guarded payment query as unconditional. Exact query-specific
checks resolved both misunderstandings.

**How to apply:** Enumerate the automatic reads for the affected empty state.
Check each relevant calculation/payment dependency, disabled-query loading
behavior and visible errors for nonempty invalid data. Never label an invalid
populated collection as empty merely to suppress its requests.

For disabled React Query v4 queries, `isLoading` can remain true even though no
fetch is happening. Use a fetching-aware state when embedded data is already
available, rather than letting pending state cover valid content with skeletons.

**Why:** A category panel had all embedded child records, but a disabled child
query's pending state hid them. API/envelope and hierarchy tests alone did not
catch that visible rendering failure.

**How to apply:** Test both embedded-data/disabled-query and fetched-data paths.
Bind loading-state checks to the exact query and confirm visible populated data,
not merely the absence of extra requests.