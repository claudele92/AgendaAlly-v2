# Native Stage 2 business profile

The maintained Next.js shop profile now presents the actual identity, cover,
square logo, description, gallery, service and product catalogs, specialists,
reviews and native booking links. Missing fields are not replaced with
invented streets, scores, media, stock or slots.

Existing `matched_location`, location query parameters, BranchGate and the
native branch selector remain authoritative. Shop-level hours are not assumed
to be branch-specific. Gallery, service and specialist failures have explicit
states; successful empty responses remain distinct.

No Laravel contract, database, seed, payment or provider operation changed.
Final native browser verification is recorded in the integration report.