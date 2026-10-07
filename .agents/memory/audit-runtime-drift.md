---
name: Audit runtime drift
description: A read-only document check can still cause platform runtime configuration drift.
---

For no-environment-change audits, use the already configured Node runtime for document processing rather than invoking another interpreter.

**Why:** A `python3` HTML/document validation invocation coincided with `.replit` acquiring a Python module, despite no explicit configuration or installation call. The original Node-only configuration was restored. Shell read-only intent does not guarantee the surrounding platform leaves runtime configuration untouched.

**How to apply:** Inspect tracked configuration changes at the end of a read-only audit, and avoid interpreter discovery through execution when the task forbids runtime changes. Preserve unrelated user edits.