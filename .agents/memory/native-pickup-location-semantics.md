---
name: Native Product pickup location semantics
description: Distinguish geographic Product locations from usable postal pickup branches and preserve legacy ready-based pickup.
---

Scope scheduled pickup to existing Product ShopLocations, never Service locations or Collection Points. Require an authoritative postal address: use the location address, or the Shop's public address only when there is exactly one Product location. Do not copy one Shop address across multiple branches.

Preserve ready-based legacy pickup even when a Shop has no Product location record. Do not invent a branch or timestamp to make that checkout fit the scheduled flow.

**Why:** Native Product location records can describe geography/currency without containing their own postal addresses, while the legacy Shop Pickup flow uses the Shop address without a calendar.

**How to apply:** Audit actual location/address data before adding scheduling or branch controls. Keep legacy ASAP checkout separate from the stronger location requirements for scheduled windows.