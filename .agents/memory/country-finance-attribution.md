---
name: Country finance attribution
description: Authorization limits when financial records have creator or shop ownership but no explicit country allocation.
---

Do not infer country ownership of unallocated financial totals from any one
convenient local association. An owner's unrelated accepted invitation must
not attribute foreign-owned money to the invitation's country. For records
without explicit allocation, conflicting, missing or null geography must
fail closed for restricted country readers.

**Why:** Checking that one associated shop is local can still disclose foreign
finance when the creator owns a foreign shop or has accepted associations in
multiple countries. Existing country filters can hide those conflicting
relationships and make an ambiguous attribution appear safe.

**How to apply:** Preserve unrestricted and legitimate personal/owner reads,
but require defensible, unambiguous geography for country-scoped aggregate
reads. Evaluate disqualifying associations without country-filtered
relationships concealing them. If legitimate cross-country allocation is
needed later, design explicit allocation rather than granting each country
the entire unallocated record.