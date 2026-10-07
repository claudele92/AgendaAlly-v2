---
name: Native merchant currency capacity
description: Capability versus merchant-profile capacity for the shop-wide collection choice.
---

Evaluate the shop-wide collection choice across every relevant Product and Service context, including invalid contexts. A provider's supported-currency list does not prove one merchant profile can serve different currencies simultaneously.

**Why:** Native MTN merchant configuration holds one exact charge currency, whereas the collection choice applies shop-wide. Treating two independently supported currencies as sufficient would offer setup that cannot become ready for both domains.

**How to apply:** Keep configuration capability separate from credentials/readiness. Block shared MTN collection when relevant contexts have different charge currencies unless the native merchant storage and initiation contract are explicitly redesigned to support that case. Do not invent a combined location-type token or let a caller choose only the easier context.