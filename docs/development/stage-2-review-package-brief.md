# Stage 2 — complete AgendaAlly visual review package

## Gate and deliverable

Stage 1 bounded native development acceptance passed on 2026-10-01. See
`stage-1-external-acceptance.md` for the final matrix and
`preview-capability-matrix.md` for existing-capability representation.

Produce **one coherent visual review package**, including real representative
desktop and approximately 390px mobile screens—not a prose-only report or
disconnected experiments. Make design recommendations without asking the
creator to choose every small detail. After presenting the complete package,
**STOP for explicit approval**. No broad native redesign graduation yet.

## Product and identity

- One product family: Customer Marketplace and AgendaAlly for Business.
- Customer: services, products, businesses, specialists, booking, shopping,
  appointments/orders/favorites and country/city discovery.
- Business: only existing supported bookings, schedules, services, products,
  specialists/staff, branches, customers, commerce and permission-scoped finance.
- Services and Products must have comparable immediate prominence. Not
  primarily salon software; preserve beauty/wellness while inspecting real
  supported broader categories before choosing examples.
- Preserve black/neutral and warm bronze identity and approved native
  authentication direction. Recommend a professional typography, spacing,
  grid, color/status, card, form, search, icon and interaction system. Restrained
  supporting colors are allowed; avoid excessive bronze/gradients/template
  decoration.
- Africa-first, globally inclusive imagery: authentically feature Black
  African customers/professionals/entrepreneurs alongside diverse genders,
  ages, professions and settings. Not token diversity or salon-only imagery.

## Required package checklist

1. Audit actual storefronts `/`, `/home-2`, `/home-3`, `/home-4`, including
   `ui_type`, middleware/config and shared components. Compare hero,
   search/location, every discovery domain, navigation/cards/media/footer,
   desktop/mobile accessibility and maintenance duplication.
2. Classify each KEEP / IMPROVE / CONSOLIDATE / RETIRE; do not disable or
   delete variants during the proposal.
3. Recommend the future primary storefront and the concepts worth retaining.
4. At least three meaningfully different positioning/content directions.
   Do not retain “Book Services. Shop Favorites. Simple.” by default.
5. Recommend stronger headline/copy grounded in actual capabilities; avoid
   generic “everything you need,” “all in one place,” “made simple” language.
6. Proposed information architecture.
7. Representative desktop customer homepage.
8. Representative ~390px customer homepage.
9. Search/discovery: domains/tabs/grouping, real useful filters, categories,
   loading/empty/error states. Separate supported behavior from future unified
   search or other unsupported API recommendations.
10. Location: explicit current country/city, change action, effects and
    persistence; Cameroon → Douala example. Maps optional, no silent changes,
    permission-derived distance or fabricated nearby claims.
11. Marketplace business/vendor profile using real identity/logo/cover,
    description, branches, services, specialists, products, supported
    reviews/availability/contact and booking/shopping CTAs.
12. Explicit services/products parity across homepage/discovery/profile.
13. Audit existing public AgendaAlly for Business page: header/location,
    hero/Get started, Online Booking/Management/Payment/Notification,
    Stay in control, reservation image, Run your business your way,
    categories, app downloads and footer. Classify KEEP / IMPROVE /
    REPLACE / REMOVE.
14. Redesigned desktop business landing page.
15. Redesigned ~390px business landing page.
16. Replace rental/rooms/check-in/check-out/locked-rental screenshot in the
    **proposal** with a native AgendaAlly appointment/calendar/booking/team/
    product/branch showcase. Do not fabricate unsupported functionality.
17. At least three category-art directions: refined custom iconography,
    restrained illustration, photography/thoughtful hybrid or better
    justified alternatives. Recommend one.
18. Representative category cards with consistent semantic meaning, weight,
    proportion/padding, contrast, touch targets and scalable expansion.
19. Africa-first/global inclusive imagery direction throughout the package.
20. Seeded-media audit for businesses/services/specialists/products/categories/
    recommendations/blog: broken/missing URLs, dependencies, quality, matching
    and repetition. Propose reusable reproducible demo media with provenance/
    license/generation records. Keep static brand/auth assets separate.
21. Payment method UI: available vs unavailable vs development-disabled,
    offline/cash and wallet where applicable; preserve hardened behavior.
22. Native blog listing/article presentation; categories/tags only if actually
    supported. Improve the observed detail date formatting.
23. Readable professional Terms/Privacy pages; retain conspicuous development
    and owner/legal-review status until policies are actually approved.
24. Social/footer: customer discovery, Business, support/About/Blog/legal,
    legitimate downloads and location context as appropriate; no invented
    official profiles. Address observed FAQ answer contrast/spacing.
25. Logo audit/system across customer desktop/mobile/drawer/auth, business
    login/public/workspace, footer, favicon/app icon. Full/compact and
    light/dark use, minimum size/clear space/alignment/accessibility;
    preserve identity without distortion or unnecessary repetition.
26. Clear Customer ↔ AgendaAlly for Business navigation relationship.
27. Clearly label current supported capabilities versus future recommendations
    everywhere; representative data must match actual structures.

Every important proposed public/customer surface needs desktop **and** mobile
demonstration. Inspect navigation, search, location, cards, profiles, logo,
drawers, footer, overflow, touch targets, keyboard/focus, contrast and text
scaling intentionally—not compressed desktop layouts.

## Engineering and data boundaries

Use actual native source/components and real contracts as the starting point;
extract existing components for canvas work rather than reconstructing
unrelated approximations. Canvas demonstration data must be explicitly
illustrative and not presented as native-browser acceptance.

Do not casually change Laravel contracts, authentication/authorization,
country/shop/branch enforcement, booking, product/commerce, accounting,
payment hardening, Flutter REST compatibility, provider verification,
development proxy or portable setup. No schema redesign/framework migration,
production/publishing, provider credentials, live payments/messages or
permission expansion. Do not redesign transactional booking/checkout yet.

Keep engineering follow-ups separate: legacy payout/subscription country
scope, Staff/Specialist calendar forms assuming shop data, Maps key setup and
full Android/iOS verification. Document revealed usability/product problems
without silently changing backend semantics.

Current seed: three articles, three About sections, four FAQs, demo Terms and
Privacy, safe reserved social examples, and original payment catalog with
cash only active. Unknown official contacts/store links remain unconfigured.
No provider operation was verified. No missing-image condition should be
manufactured solely for browser testing.

## Review and stop

Present recommendations, comparisons and representative visual screens
together. The creator may approve one direction, combine elements or request
revisions. Only explicit approval of selected Stage 2 designs permits native
integration. A workspace mode switch is not redesign approval.