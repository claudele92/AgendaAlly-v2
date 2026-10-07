---
name: Database snapshot contracts
description: Avoid unverifiable or misleading comparisons between native audit fingerprints.
---

Record and reuse the exact row ordering, fetch shape and serialization alongside
database snapshot hashes. A hash without this recipe is not a portable receipt.

**Why:** A previous receipt could not be reproduced using several ordinary PDO
encodings. Row-count comparison remained possible, but a fresh field-level
comparison could not honestly be claimed.

**How to apply:** Use the same declared fingerprint method for both snapshots.
If the baseline method is unavailable, distinguish count/source checks from
verified row hashes; do not interpret encoding drift as application corruption
or silently report an unverified comparison as passing.