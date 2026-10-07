---
name: Partial public projections
description: Avoid false financial meaning when hydrating lightweight seller metadata for public cards.
---

Metadata-only relations must emit only the fields actually selected. Preserve
full-resource serialization for existing detail consumers; do not let partial
models pass through resources that cast absent financial fields into defaults.

**Why:** A proposed seller-label projection through the full shop resource would
have emitted `collect_via_platform=false` despite not reading its actual value.
That would invent payment meaning during a presentation-only task.

**How to apply:** When adding lightweight public metadata, verify serialized
output as well as query selection. Test both the limited metadata path and an
existing full resource with true financial flags. Keep financial routing,
existing full relations and their values unchanged.

On shared REST listings, make new web-only metadata opt-in when another client
uses relation presence as a behavior switch, even if its JSON parser is nullable.

**Why:** Flutter immediately displayed a list object before fetching full product
detail. Supplying a partial shop could enable seller/contact actions before its
location/contact data arrived, and remain partial if the detail fetch failed.
Default response preservation avoids this transient compatibility regression.

**How to apply:** Inspect interim list-to-detail state and presence-based actions,
not only parsing. Keep the old shared default and full detail contract; request
presentation-only metadata explicitly from the client that needs it.