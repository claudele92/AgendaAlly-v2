---
name: Native preview API transport
description: Why native development browsers use same-origin API transport while production retains its configured backend origin.
---

Keep native development browser API requests on the frontend origin and
forward them server-side to the local Laravel listener. Keep production API
configuration and the native REST contract separate from that dev transport.

Synthetic acceptance targets must be per-process overrides, not shared or
development-wide settings that replace the normal demo/storefront source.

**Why:** A shared acceptance target masked a preserved demo with a different
database. The user explicitly prohibited replacing the normal AgendaAlly demo
dataset with synthetic acceptance fixtures.

**How to apply:** Isolate acceptance frontends, leave normal preview defaults on
their owned source, and restore normal previews after resource-heavy checks.
Check routing/database provenance before reseeding when demo records disappear.

**Why:** The creator's ordinary external browser repeatedly failed translation
and geography initialization while the internal tester authenticated against
a separate API preview port. A frontend websocket and container-side CORS
success do not prove an external browser can reach another preview origin. On
2026-10-01 the creator explicitly confirmed that the same-origin solution
fixed their own ordinary-browser Admin and Customer experience, including
real Admin login/reload and Cameroon → Douala location persistence.
The exact browser-edge rejection was not conclusively established; do not
describe it as a proven Laravel permission or CORS-policy failure.

**How to apply:** Scope development proxies to the native versioned API prefix
so Next's own cache routes remain intact. Keep local upstream addresses out
of browser URLs, preserve authorization, and never substitute wildcard CORS,
mock geography or synthetic login. Validate the creator's actual preview
experience before treating internal-browser evidence as acceptance.

Use the active source roots resolved by the development launcher. An ignored
preview mirror can be stale and edits there do not repair the tracked runtime.

Verify local media separately from successful same-origin API transport.

**Why:** After API requests moved to the frontend origin, reviewed local Story
images still used a loopback media origin. Native Vendor images loaded, while
Next's optimizer rejected the same files and silently displayed fallback art.
Successful feed JSON and Vendor thumbnails did not prove customer media worked.

**How to apply:** Evaluate development-only image allowances against the
configured media origin, not the API origin. Keep production private-IP
protection intact and confirm the actual optimized Story image returns image
bytes, not just a successful feed or placeholder.

Before asking the owner to enter an isolated SMTP credential, provide and verify
a creator-accessible isolated Admin URL and its database provenance. A
workspace-only loopback address is not a usable owner entry point.

**Why:** Instructions to use the isolated Admin omitted an externally usable
entry point. The owner used the normal Admin instead; readiness confirmation did
not establish that the isolated provider existed.

**How to apply:** Never copy credentials or silently retarget the normal Admin to
overcome an access mismatch. Honor the owner's choice of test runtime; the SMTP
workflow now uses the normal preview, not another isolated environment.