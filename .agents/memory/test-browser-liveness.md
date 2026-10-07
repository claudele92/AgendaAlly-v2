---
name: Test browser liveness
description: Closed shared tester browser versus stale page/context handles.
---
A page handle reporting isClosed=false does not prove the underlying tester browser is alive. Calling newContext on a closed shared browser does not relaunch it.

**Why:** Native acceptance encountered successful page-handle inspection followed by Browser closed on DOM/navigation; fresh context creation failed identically after unused contexts were closed.

**How to apply:** Recover the actual browser runtime through a supported relaunch, not repeated newContext calls. A single fresh tester in a subsequent authorized continuation recovered the browser when follow-ups could not. Preserve earned receipts instead of restarting every journey. Do not classify this transport failure as an application or financial defect.