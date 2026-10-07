---
name: Imported native secret exposure
description: Security constraints learned from auditing imported native build configuration.
---

Treat ignore rules as intent, not evidence that imported native configuration
is absent from Git. Verify tracking without printing credential-bearing
contents. Environment injection protects source inputs, not client binaries.
Source cleanup and provider-side rotation are separate outcomes.

**Why:** An imported native build configuration was already tracked despite
an ignore rule. Ignoring or deleting the current file cannot invalidate
credentials retained in history, prior build outputs, or distributed clients.
Read-only history review also found credential-shaped native settings reachable
from both the working branch and its locally recorded origin history after
the configuration disappeared from the current source tree.

**How to apply:** Check tracking explicitly during future native configuration
audits. Keep credential inspection value-free, report rotation as outstanding
until the creator confirms it, and obtain informed agreement about client-key
visibility before collecting replacements. Do not rewrite history or activate
providers without separate approval.