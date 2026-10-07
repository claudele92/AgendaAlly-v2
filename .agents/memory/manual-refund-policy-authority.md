---
name: Manual refund policy authority
description: Cancellation-policy authority must be conserved independently of collected principal across distinct request intents.
---

Conserving original collected principal is not enough for a fee-limited refund. Held requests and later financial recording must also conserve the shared native policy-limited authority, including distinct valid command identities.

**Why:** Separate fixed-policy workflows could each complete a policy-sized refund and together consume more than the cancellation policy permitted, while principal conservation, same-key replay and atomic effect tests still passed.

**How to apply:** Include same-context, different-intent, nonzero-fee adversarial scenarios in Refund qualification. Preserve original cancellation/time/custody semantics; do not invent a replacement policy when repairing the shared authority boundary. After the valid P0 stop, the owner explicitly authorized the bounded repair and regression tests. Real money, financial email, provider and production activation remain unauthorized.
