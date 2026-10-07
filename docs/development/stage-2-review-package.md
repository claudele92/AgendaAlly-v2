# AgendaAlly — Stage 2 visual review

**Subsequent approval:** On 1 October 2026 the creator approved native integration
of this complete package. The proposal-only boundaries below describe the
original review phase. See `stage-2-native-implementation.md` for current scope
and verification. The creator subsequently approved the native result and
bounded legacy cleanup. This document is historical visual/provenance evidence,
not an active proposal or authorization to publish.

**Scope:** design proposal only. Stage 1 bounded development acceptance passed.
Native redesign, booking/checkout changes, provider activation, production
operations and publishing are not approved by this package.

## Recommendation

Use **View 1's shared composition as the structural foundation**, not its
current headline, section ordering or service-first bias. Consolidate the
shared discovery widgets from View 3. Keep the country/city visible and put
**Book services** and **Shop products** together at the first decision point.
Use a search-led city marketplace as the recommended direction, with people
and businesses as meaningful secondary discovery routes.

Recommended headline: **“Book local expertise. Shop local businesses.”**
Domain labels and city context clarify the booking/shopping distinction without
unsupported proximity or appointment-availability claims.

Do not change `ui_type`, its environment override, middleware or live routes
now. Root `/` can currently rewrite to Views 2–4; direct variant routes remain
available. Consolidation/retirement below is a recommendation pending approval.

### Four native storefronts

| Native view | Recommendation | Keep | Change / trade-off |
| --- | --- | --- | --- |
| `/` / View 1 | **IMPROVE; future primary foundation** | Shared service/category widgets and broad discovery composition | Replace generic headline, move products to equal prominence, reduce carousel repetition and clarify mobile country/city |
| `/home-2` | **IMPROVE concept; consolidate shared body** | Human/photo-led hero concept and compact cards | Remove salon-only positioning and unsourced “10 people booked”; investigate differing initial/refetched category types |
| `/home-3` | **CONSOLIDATE** | Energy of the gradient as an optional, motion-safe decorative concept | Do not maintain another complete homepage tree for a background; extend beyond beauty and services |
| `/home-4` | **RETIRE independent system after approved replacement** | Distinctive image/outlined-type hero as a reusable concept if useful | Native body omits service/category discovery and repeats common widgets; no route is retired now |

The Current canvas references are **source-extracted hero/category or section
excerpts**, not reconstructed full pages, live API acceptance, or invented
complete datasets. See [storefront audit](stage-2-storefront-audit.md) for
source citations, contracts, category placement limitations and extraction
provenance.

### Three positioning hypotheses

| Direction | What leads | Why consider it | Trade-off |
| --- | --- | --- | --- |
| **City marketplace — recommended** | Explicit city, separate service/product/business search entry and comparable booking/shopping paths | Useful for customers who already know what they need; easiest fit with distinct current APIs | Needs careful category/empty-state treatment so it does not become a dense directory |
| **People and expertise first** | Professionals, their skills and relationship to actual businesses, with products still immediately reachable | Helps customers choosing a person rather than only a category | Specialist-centric narratives must not imply specialists own standalone product stores; browsing is slower for exact product queries |
| **Businesses first** | Business identity, branch context and dual services/products within a profile | Best expresses businesses that offer both domains; gives the business profile a clear role | Adds a business-selection step for customers who want a particular service/product |

These are different content hierarchies and task-entry patterns, not three
color treatments of one layout. The screens carry their proposed headlines;
none requires keeping “Book Services. Shop Favorites. Simple.”

## Proposed information architecture

- **Marketplace:** Book services, Shop products, Businesses, Specialists.
- **Discovery:** explicit domain tabs; category and domain-appropriate filters;
  country/city always visible. Use separate existing APIs. Cross-domain
  ranking or a single unified-search endpoint is a future recommendation.
- **Business profile:** identity/cover/description → branch context →
  services, specialists, products, hours, supported reviews/contact.
- **Customer account:** existing appointments, orders, favorites, cart and
  approved authentication; this proposal does not redesign those transactions.
- **AgendaAlly for Business:** public explanation → existing sign-in/onboarding
  boundary → permission-scoped native workspace. Explicit return to Marketplace.
- **Editorial/support:** Blog/article, About, FAQ, Contact, Terms, Privacy.
- **Footer:** coherent customer/business destinations, editorial/help/legal,
  location context and only legitimate configured social/store links.

### Search and location

Service, product and business searches remain separate contracts. Appropriate
filters are text/category/price and supported specialist/duration criteria
for services; brand/category/stock/price for products; branch geography and
supported categories for businesses. A calendar/date filter based on schedules
is **not** proof of an unoccupied appointment slot.

Location changes require deliberate selection and confirmation. Show Cameroon
→ Douala, explain the effect on results, allow cancel, keep focus/keyboard
behavior coherent and never silently switch a native branch. Prototype-local
interaction/persistence is not native location acceptance. Maps remain
optional and disabled; no GPS/distance or “nearby” claim without real inputs.

### Business profile

The proposal uses the exact public synthetic Le Sawa title, description,
logo-photo and cover fields in `stage-2-profile-fixture.json`. Branch street
address/alias and linked gallery media are absent; do not infer them from the
shop-level address. Catalog examples are labeled representative separately.

Use city-matched branch addresses, not a guessed shop headquarters. Keep the
shop description separate from branch address/hours. Display actual-shaped
services, specialist relationships, product stock/variant fields and available
review data. No fabricated ratings, testimonials, verified appointments or
contact destinations. Prototype CTAs browse or explain their boundary; they
do not book, order, review or message.

## AgendaAlly for Business

| Existing public concept | Decision | Proposed treatment |
| --- | --- | --- |
| Shared header/location | IMPROVE | Clear Marketplace ↔ Business relationship and explicit geographic context |
| Hero / Get started | IMPROVE | Specific supported business benefit and honest existing sign-in/onboarding destination |
| Online Booking / Management / Payment / Notification | KEEP topics, IMPROVE claims | Explain supported tools, qualify disabled external payment/message operations |
| Stay in control | IMPROVE | Show real appointment/time/team/branch concepts instead of generic automation |
| Rental/rooms reservation image | REPLACE | AgendaAlly appointment-calendar showcase, visibly illustrative and grounded in native event fields; no rooms/check-in/occupancy |
| Run your business your way / categories | IMPROVE | Broader supported service taxonomy plus distinct product-commerce explanation |
| Category cards | IMPROVE | Consistent semantic art, no fabricated seller supply |
| App downloads / phone graphic | IMPROVE | Genuine supported app/workspace concepts; official destinations remain unavailable until configured |
| Fastest-growing companies | REMOVE unverified claim | No invented partner marks or social proof |
| `121m+`, `12%`, `221k+` success figures | REMOVE pending evidence | Replace unsourced quantities with truthful capability demonstration |
| Footer | IMPROVE | Product-family navigation, useful help/editorial/legal and honest destinations |

See [business audit](stage-2-business-audit.md) for exact source citations and
native Calendar field/behavior grounding. The proposed showcase is not a
claim that a redesigned native Calendar already exists.

## Category art and shared visual language

**Recommend custom, consistent pictograms for taxonomy plus selective editorial
photography**, rather than making every category photo-dependent.

1. **Refined pictograms:** strongest small-size consistency and scalable
   taxonomy; exact semantic glyphs replace misleading dental/education/tattoo
   proxies. Maintain optical weight, padding and accessible text labels.
2. **Restrained illustrations:** warmer and more expressive but need a managed
   illustration vocabulary and more production effort as categories grow.
3. **Photography/hybrid:** useful human/context cues but harder cropping,
   licensing and consistency. Not proof of a real provider's identity or stock.

Actual broader service categories include Tailoring, Dental Care, Healthcare,
Handyman, Laundry & Dry Cleaning, Home Cleaning, Education and Tattoo & Piercing.
Education/Tattoo have bookable demo supply; taxonomy existence does not mean
every category has a seller. Product taxonomy is separate: Beauty & Personal
Care and Tailoring & Apparel Supplies. Do not invent construction/project-quote
support or silently unify category models.

Keep the accepted Stage 1 **a.** mark/AgendaAlly wordmark as this proposal's
working family anchor, including compact/full and light/dark variants. Audit
and reconcile settings-driven, old green/purple and legacy app “24” identities
only after approval. Any replacement master mark needs explicit approval.
Specify minimum optical size, clear space, square app/favicon usage,
accessible name, alignment and no distortion or repeated full logos.

The visual specification demonstrates typography, spacing/grid, neutral/bronze
hierarchy, status colors, cards/forms/search/icons, visible focus and intentional
desktop/mobile behavior. Preserve approved authentication rather than reopening
its design.

## Media, content and payment honesty

The existing seed uses remote Unsplash catalog/editorial images and local
category SVGs. Previous accepted journeys encountered no broken image; that
is not a full inventory availability/license audit. Source repetition,
category proxies and copy/image mismatches are documented separately.

New proposal photography is **AI-generated, synthetic and local to the
sandbox**. It is not an actual entrepreneur, specialist portrait, customer
testimonial, stock confirmation or backend media update. Generation provenance
and subject-to-copy mapping are recorded in `stage-2-media-provenance.md`.
A future reproducible catalog media pack should pin reviewed local assets,
retain source/creator/license/permission and mapping metadata, avoid excessive
reuse, and remain distinct from static brand/auth artwork.

- Blog listing/detail use title, short description, body, image, author/date;
  no native blog categories/tags are invented. Correct date presentation is a
  design proposal, not a native change.
- Terms/Privacy remain conspicuous **non-operative development samples** with
  owner/qualified legal review required. No new promises, retention periods,
  cancellation entitlements or jurisdiction are represented as approved.
- About/FAQ/Contact explain actual preview scope. FAQ contrast/spacing improves
  in the proposal. No invented support office/map/form/phone/email.
- Payment UI demonstrates **cash only active**, 17 inactive original identities,
  and wallet as distinct internal balance/history—not a live gateway.
  Disabled/unavailable reasons and future states are labeled. No standalone
  bank-transfer rail, wallet funding, charge/refund/settlement/payout success.
  Booking/checkout and financial semantics remain unchanged.
- No fake official social profiles or store destinations; unavailable means
  unavailable, not “coming soon” with an invented promise.

## Review coverage

Each proposed screen below is represented at desktop and approximately 390px.
The Current references are extracted sections with their limitations labeled.

| Requested coverage | Review evidence |
| --- | --- |
| 1–3: four native variants, disposition and future primary | CurrentHome1–4 excerpts; storefront audit; recommendation above |
| 4–5: three positioning/copy directions | Homepage, PeopleFirst, BusinessFirst |
| 6: information architecture | Review and proposed shared navigation/footer |
| 7–8: customer desktop/mobile | Homepage pair |
| 9–10: search/discovery/location/states | Discovery pair and interactive prototype controls |
| 11–12: profile and service/product parity | BusinessProfile pair and Homepage/Discovery paths |
| 13–16: business audit, desktop/mobile, native-calendar replacement | CurrentBusiness excerpt, business audit, ForBusiness pair |
| 17–18: three category-art directions/cards | CategoryDirections pair |
| 19–20: inclusive imagery and seed-media audit/provenance | Local proposed photos, media audit/provenance and source limitations |
| 21: payment-method states | PaymentStates pair; original payment matrix |
| 22: blog/article | Blog and Article pairs |
| 23: legal pages | Terms and Privacy pairs |
| 24: support/social/footer | Support pair; shared Footer across recommended pages |
| 25: logo/shared system | BrandSystem pair and common family across screens |
| 26: Customer ↔ Business relationship | Shared navigation and ForBusiness |
| 27: supported versus future | Explicit proposal/boundary notices and source audits |

Responsive review must cover overflow, navigation/drawers, country/city,
search/cards/profile/logo/footer, touch targets, keyboard/focus, contrast and
text growth. Preview checks are proposal-rendering evidence, not renewed native
authentication or transactional acceptance.

## Approval boundary

Prototype persistence is opt-in under a dedicated browser preference key;
confirmation changes only demonstration context. Unchecking and confirming
clears that preference. Country-specific city choices, service/product
taxonomies and product navigation are distinct. Native country, shop/branch
permissions and APIs are unchanged.

Current excerpts intentionally use API/settings stubs. Their copied static
fallback logo can read “Shoppopo”; it is source-reference material, not evidence
of current settings-backed branding or native-browser acceptance. Asset URLs
are prefixed for the sandbox, and baseline navigation is non-operative.

This is **one review package**, not an implementation approval. Keep engineering
follow-ups separate: legacy payout/subscription country scope, Staff/Specialist
calendar forms, Maps setup and full Android/iOS builds. Do not silently repair
backend semantics while working on visual proposals.

After the complete package is presented, **STOP**. The creator can approve a
direction, combine elements or request revisions. Only explicit approval
authorizes integrating selected designs into the native applications.