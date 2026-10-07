---
name: SQLite money precision
description: Exact money representation in approval-gated accounting schema proposals.
---

Do not treat a SQLite DECIMAL declaration as guaranteed fixed-scale exact money.
New accounting proposals use integer atomic units with a frozen, validated native
scale. This is not permission to convert existing monetary columns or change
native rounding/fee policy; schema and implementation remain approval-gated.

**Why:** A read-only affinity check returned REAL for a decimal value. Exact
conservation and contribution claims must not depend on binary floating sums.

**How to apply:** Specify range/scale and exact arithmetic in future financial
designs, retain original currency/scale, and fail closed on unsupported precision
or inconsistent native quote normalization. Keep historical data unchanged.