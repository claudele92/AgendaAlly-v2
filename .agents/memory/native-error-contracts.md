---
name: Native error contract verification
description: Why isolated client error mocks must match the actual Laravel wire response.
---

Use the native backend's HTTP status and stable machine code when classifying
an expected absence. Do not use localized display text as the primary contract,
and do not hide a conflicting machine code because its message resembles an
expected empty state.

**Why:** An isolated missing-cart regression passed against a canned English
phrase while the real development browser still raised an error for the
backend's differently worded missing-cart response. Passing a client mock did
not establish native integration acceptance.

**How to apply:** When testing native-client error handling, include an actual
wire-contract example, a translated message with the same machine code, a
conflicting code and unrelated failures. Keep absence handling specific to the
resource read; never turn every 404 into an empty success. Preserve valid
browser evidence and recheck only the changed integration behavior.

Native read-only API probes should send `Accept: application/json`.

**Why:** An unauthenticated probe without that header tried a missing named login
redirect and returned 404, while the same registered protected route returned the
correct 401 with the JSON header. That 404 was not evidence of a missing route.

**How to apply:** Match the native browser's JSON request headers before
diagnosing routing or authorization from shell HTTP status.