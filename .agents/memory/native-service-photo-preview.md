---
name: Native Service photo preview boundary
description: Local media origin drift and why server success or image onError do not prove browser safety.
---

Keep development-only native Service photo origin adaptation explicitly opted in, confined to canonical public Service media and the configured image origin. Preserve production/cloud URLs and the constrained Next image allowlist.

**Why:** Laravel's local public disk can serialize portable photos using a development APP_URL that differs from the Customer optimizer's configured image host. Next rejects that host synchronously, before image onError can run. A successful Shop document response therefore still led to the client error boundary. Generic serialized console errors and secondary React warnings obscured the real image-loader exception.

**How to apply:** Inspect the actual browser Error description/stack and verify both page hydration and loaded photo bytes. Check direct next/image consumers as well as the shared fallback component; correcting only the shared wrapper does not cover every Service image.