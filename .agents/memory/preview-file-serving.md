---
name: Preview file-serving boundaries
description: Vite workspace inference and SPA fallback can obscure private runtime exposure.
---

Original-client development servers must have a file-serving boundary separate
from the owner's Unix permissions. Do not infer private runtime files are
protected merely because they are mode 0600 or outside the client directory.

**Why:** Vite inferred this monorepo's workspace root and included the original
preview's sibling backend runtime. Its owner could serve private files through
filesystem URLs. Separately, missing database sidecars returned SPA HTML with
HTTP 200 even after filesystem permissions were narrowed; that was not file
content exposure, but it made absence-dependent boundary tests unreliable.

**How to apply:** Keep original production configuration intact and restrict
development serving to the intended client/dependencies. Reject outside-client
filesystem requests before SPA fallback, including encoded traversal and
nonexistent paths. Check both public and local origins without reading secret
contents. If a private path was exposed, rotate synthetic secrets and invalidate
preview tokens rather than assuming a subsequent 403 cures earlier access.