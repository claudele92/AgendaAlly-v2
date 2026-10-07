---
name: Native JSON evidence equality
description: Why cross-engine immutable JSON evidence cannot use serialized bytes or untyped PHP decoding.
---

Immutable native JSON evidence must compare semantic structure, not raw serialized
bytes: MySQL reformats and reorders JSON objects, unlike SQLite text storage.
Keep object membership independent of key order, array order exact, and scalar/
container types distinct. Never normalize or rewrite retained evidence just to compare it.

**Why:** Actual disposable MySQL rejected a correctly retained quote solely because
JSON storage added whitespace. Ordinary PHP decoding also rounds large numbers,
while BIGINT-as-string can falsely equate numeric evidence with quoted strings;
associative decoding can collapse empty objects into empty arrays.
The owner confirmed this semantic comparison approach for private JSON
projection assertions.

**How to apply:** Associative decoding followed by PHP strict array identity still
compares object-member order; decoding alone is not semantic JSON equality.
Use an explicitly object-order-independent, read-only, type-preserving comparison
for approved JSON immutability boundaries. Preserve exact numeric evidence as well as decoded type;
reject genuine value/member/type/order changes. Do not extend comparison changes
to financial identity fields or other boundaries without the required approval.