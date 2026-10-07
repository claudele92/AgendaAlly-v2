---
name: Request intent lifetime
description: Preserve financial request identity through same-record refresh, not just within the child component.
---
A same-signature retry must retain its request identity through acknowledgement and parent refresh. Make a genuinely new request explicit.

**Why:** The native transaction dialog unmounted its financial form during refresh, so keeping a child ref alone still lost both input and retry identity. Distinct within-capacity reservations are not evidence of duplicated custody, but cannot establish same-key retry acceptance.

**How to apply:** Check the whole modal/component lifetime and both Seller/Admin consumers. Preserve same-record form state, expose operation IDs, and distinguish a new intent from retrying an existing one.