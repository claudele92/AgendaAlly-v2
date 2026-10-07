---
name: Laravel HTTP test session isolation
description: Why simulated stateless API requests can leave country-scoped identity active during later console fixture tests
---

Laravel HTTP-kernel simulations can share the array session across requests.
Forgetting authentication guards alone does not empty that session. Sanctum
checks the web-session identity before bearer authentication.

**Why:** A sequence of role logins followed by a simulated CLI seed retained
the last country-restricted session identity, hiding another country's shop.
Replacing the bound request and forgetting guards did not resolve the issue
until the shared session was cleared.

**How to apply:** When a test models independent stateless API requests or a
new CLI process, isolate the request, resolved request facade, session and
authentication guards. Do not weaken production country scopes to compensate
for a test harness retaining authenticated state.

Minimal fixtures that register the real authentication/session providers need
process isolation from suites built around lightweight authentication stubs.

**Why:** A real-guard fixture passed alone, and the existing account-security
suite passed alone, but running them together triggered missing-provider
bindings and unrelated fixture failures. Separate-process execution restored
the combined pass without changing production authentication or other tests.

**How to apply:** Verify the combined suite, not only standalone results.
Keep real-Sanctum bootstrap minimal and isolate global framework state rather
than weakening application rules or extending unrelated fixtures.

For finance authentication qualification, a real loopback HTTP campaign using
persisted disposable tokens and encrypted file-session cookies is distinct from
both synthetic controller auth and complete application/login acceptance.

**Why:** Minimal schema fixtures initially lacked framework/package prerequisites;
those failures were setup failures, not permission denials. Actual HTTP also
confirmed Sanctum's web-session precedence over a different bearer identity.

**How to apply:** Preserve unexpected 500s separately; never count them as denials.
Retain incremental HTTP responses and exact non-auth database effects, omit
credentials from evidence, retire test tokens/sessions, and explicitly qualify
omitted login/challenge, stateful CSRF, native-engine and production boundaries.