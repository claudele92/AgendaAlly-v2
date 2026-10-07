---
name: Vendor calendar product direction
description: User-approved proposal direction and constraints for mobile navigation, theme reuse, payment messaging and local-client retries.
---

Vendor calendar direction: mobile Agenda-first at 390/320px, Day secondary,
Week optional and contained. Keep early Add Booking payment messaging small;
make payment state prominent on final Review. Use existing AgendaAlly theme
tokens rather than treating illustrative mockup purple as a required colour.

New Local Client creation needs safe retry/idempotency protection without
deduplicating people by name alone. Preserve security fixes and certified
scheduling/financial semantics.

The revised proposal is approved for implementation. Use the shared
presentation layer where appropriate, without changing certified scheduling,
Cash, Wallet, local-client, accounting, capacity, recurrence, availability or
role-permission semantics. Run the bounded proposal regression plan at
1280/390/320 and preserve the protected baseline. Exclude unrelated full-suite
failures, provider activation, production deployment and other deferred scope.

**Why:** The user explicitly approved implementation of the revised proposal
with these preservation and verification boundaries.

**How to apply:** Keep UI proposals distinct from shipped behavior. Scope
client-save retry identity to the authorized actor/Shop/branch, retain the same
intent through uncertain acknowledgement and refresh, and make a genuinely
new person/save explicit. Reuse native authority and theme contracts.