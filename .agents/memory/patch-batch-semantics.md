---
name: Patch batch semantics
description: Avoid assuming a failed multi-file patch is an all-or-nothing operation.
---

Order update hunks in source order, and inspect results for each file after a patch failure. Earlier file actions can already have succeeded when a later action fails.

**Why:** The patch tool has applied earlier files before rejecting a later unmatched context. A backwards context sequence also failed until its hunks were ordered by source position.

**How to apply:** Retry only the failed file actions with current, minimal context. Do not blindly repeat the whole batch or assume all changes were discarded.