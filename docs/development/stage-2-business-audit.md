# Stage 2 — AgendaAlly for Business page audit and native Calendar grounding

This is a proposal-only audit of the checked-in public Business route and
seller Calendar implementation. It does not approve a native redesign or
change application behavior. The extracted baseline is
`artifacts/mockup-sandbox/src/components/mockups/agendaally-stage2-business-current/CurrentBusiness.tsx`,
export `CurrentBusiness` (also the default export). The root public route is
`/for-business`; shared header and footer are rendered by the business layout.
For citations below, `web/` and `admin/` paths are relative to
`.local/agendaally-preview/`.

## Public Business page — section-by-section audit

| Current section | Decision | Source-grounded observation and recommendation |
|---|---|---|
| Shared header and customer links | **IMPROVE** | The Business layout passes `showLinks` and `showBusinessButton={false}` to the shared Header. Desktop links are Shops, Deals, Blog, and My appointments; the logo goes to `/`, and this Business page therefore has no explicit desktop return-to-customer / customer-to-business switch. The menu and action controls are shared with customer pages. Keep the native shared header and make the two product destinations explicit in the proposal; do not imply the current desktop header already has that switch. [web/app/(store)/(business)/layout.tsx, lines 7–15; web/components/header/header.tsx, lines 28–75; web/components/header/links.tsx, lines 30–57; web/components/header-buttons/header-buttons.tsx, lines 44–68] |
| Header location | **IMPROVE** | `CountryIndicator` renders only after mount, with a country selected, and outside its mobile breakpoint; otherwise it returns no UI. It exposes a country-selection action, not an explicit country-and-city context on this page. Keep location explicit in a future proposal, but do not imply the current Business route shows a mobile location label or city. The extracted baseline omits a seeded location because no authentic country/city was part of the checked-in page source. [web/components/country-indicator/country-indicator.tsx, lines 12–42; web/components/header/header.tsx, lines 47–74] |
| Hero and “Get started” | **IMPROVE** | The hero uses server-managed translation keys for title and description; its button uses `/be-seller` for a signed-in user and `/login` otherwise. Retain a direct onboarding CTA, but make its destination and eligibility clear, and replace generic/unclear copy only after verifying the actual translated wording and current onboarding behavior. The sandbox copy stubs those routes and does not submit an application. [web/app/(store)/(business)/for-business/page.tsx, lines 54–61; web/app/(store)/(business)/components/top-header/top-header.tsx, lines 16–32] |
| Online Booking / Management / Payment / Notification cards | **KEEP** | These are the four native feature cards, with their original icons, responsive grid, hover treatment, and translation keys. Keep the four topics as the page's capability summary. The latter three descriptions are translation-API values, not hard-coded source copy; verify exact wording and payment/notification availability before asserting an outcome. Avoid turning the cards into claims about guaranteed online payment processing or message delivery. [web/app/(store)/(business)/for-business/page.tsx, lines 31–52, 62–83] |
| “Stay in control” and “intuitive software” | **IMPROVE** | The section heading and subheading are translated at runtime. Retain the section's intent, but make the visual and copy demonstrate actual booking/schedule management rather than making an unqualified control/automation claim. [web/app/(store)/(business)/for-business/page.tsx, lines 84–103] |
| Reservation / manager screenshot | **REPLACE** | The asset `web/public/img/manager_dashboard.png` is visibly a dated hotel-room reservation grid: it labels room groups (“Double lux”, “King Lux”), room numbers, room prices, and statuses including “Checked In”, “Checked Out”, and “Locked Rental”. It is not evidence of the native appointment Calendar. Replace it in the *proposal* with a screenshot grounded in the native seller Calendar described below. Preserve the current asset in `CurrentBusiness` as the actual-page baseline; do not edit its source or repurpose that screenshot as proof of a supported current feature. [web/app/(store)/(business)/for-business/page.tsx, lines 91–102; web/public/img/manager_dashboard.png] |
| “Run your business your way” and categories | **IMPROVE** | The section loads the shared public `Services` component with `showTitle={false}`. That component requests up to 11 category rows with `type: "service"`, `column: "input"`, and ascending sort, and links each result to `/search?category_id=…`. It is therefore a service-category showcase, not a neutral set of all business categories; products are absent. Retain the category entry point but redesign its proposal to give Services and Products comparable prominence, using real category rows when available. Do not invent category names, counts, imagery, or service/product availability. [web/app/(store)/(business)/for-business/page.tsx, lines 104–112; web/app/(store)/(booking)/components/services/services.tsx, lines 23–43, 83–99; web/services/category.ts, lines 6–9; web/types/category.ts, lines 3–15] |
| Category art | **IMPROVE** | Native service cards use API-provided category title/image, one of 12 local service background assets, and index-based background colors. This is the source of the existing category-card visual language; it is not a business-created category taxonomy or evidence that product categories have matching assets. Keep its semantic card basis for the current comparison and recommend a consistent Services/Products visual system separately. [web/app/(store)/(booking)/components/services/service-card.tsx, lines 11–41; web/config/global.ts, lines 29–73] |
| App-download section and phone image | **IMPROVE** | The list can link to Client, Business, POS, and Driver apps using device-specific URLs from public settings. The adjacent `mobile_app.png` is a customer marketplace image (search/category and business-profile screens), not a Business Calendar or staff-management screen. Keep the real app-list section, show only verified configured store destinations, and replace the customer-phone graphic in a proposal for business software with actual supported Business app/workspace visuals. Do not invent official store links. [web/app/(store)/(business)/for-business/page.tsx, lines 113–125; web/app/(store)/(business)/components/app-list/app-list.tsx, lines 9–45; web/public/img/mobile_app.png; docs/development/stage-2-review-package-brief.md, lines 121–125] |
| “Fastest-growing companies” brand marquee | **REMOVE** *(the unverified claim; preserve the section only if substantiated)* | The headline is followed by two repeated, animated rows fetched from `brandService.getAll()`. The route contains no attribution, source, or evidence for the headline's growth assertion. Remove or replace that claim in the proposal unless substantiated; if genuine partner marks are retained, use the real API rows and approved logo assets rather than invented sample companies. [web/app/(store)/(business)/for-business/page.tsx, lines 127–140; web/app/(store)/(business)/components/brands/brands.tsx, lines 5–55; web/services/brand.ts, lines 6–9] |
| “Grow your business” metrics | **REMOVE** *(pending evidence)* | The route hard-codes `121m+`, `12%`, and `221k+`; their translated labels/description are runtime values. No provenance, denominator, period, methodology, or qualification is in this page source. Do not repeat these quantitative claims in a proposal without substantiation; replace this section with a verified capability explanation or verified, attributed evidence. [web/app/(store)/(business)/for-business/page.tsx, lines 141–167] |
| Shared footer | **IMPROVE** | The footer provides Information (About/Careers/Contact), Help (FAQs/Terms/Privacy), configured social links, and configured Customer app badges. Its source contains no explicit Business destination; the socials and app links appear only when settings provide them. Preserve these legitimate destinations and make the Customer ↔ Business relationship visible. Do not synthesize social profiles, app-store URLs, footer contact copy, or legal approval. [web/components/footer/footer.tsx, lines 16–57, 59–209, 211–242; docs/development/stage-2-review-package-brief.md, lines 121–125] |

## Native Calendar — what a replacement visual may show

The reference implementation is the seller-side Calendar in the native Admin
source, not the room-reservation image. It fetches seller bookings and
master-disabled times separately, then combines them as events. The initial
calendar is a day view with selectable slots, 30-minute steps and three
timeslots; it calls the `getHourFormat()` helper for the time labels. A
selected slot offers “Add booking” or “Add blocked time”. Selecting an existing
booking fetches its booking details, calculates the booking, and opens its
update/detail flow. These are grounded Calendar behaviors; a proposal can show
the real time-grid/event idiom and a selected booking detail, but must not
present unrelated rooms, reservations, occupancy or check-in/out states.

* `admin/src/views/seller-views/calendar/index.jsx`, lines 17–50: fetches the
  seller booking list and renders the filter, Calendar, action menu, forms and
  booking detail/update components.
* `admin/src/views/seller-views/calendar/components/calendar.jsx`, lines
  17–29: consumes `bookingList` and `disabledTimes`; lines 30–34: slot
  selection; lines 35–64: booking calculation; lines 66–115: disabled-slot
  and booking-event selection/detail loading; lines 117–139: date/time setup
  and disabled-time fetch; lines 141–167: merged events, selectable day view,
  three timeslots, 30-minute step, and status styling.
* `admin/src/views/seller-views/calendar/components/action-type-selection.jsx`,
  lines 19–50: the selected slot's two actions are “Add booking” and “Add
  blocked time”.
* `admin/src/redux/slices/booking.js`, lines 7–20 and 33–39: seller booking
  list defaults (`page: 1`, `perPage: 100`) and request path through the seller
  booking service; lines 98–115: Calendar event mapping includes booking ID,
  service-master pause, service title, booking status, start/end date and
  parent booking ID.
* `admin/src/services/seller/booking.js`, lines 3–15: seller booking list,
  detail, create/update/delete, calculate, status, and notes service paths.
* `admin/src/redux/slices/disabledTimes.js`, lines 24–30 and 131–145: seller
  disabled-time data includes ID, translated title, repeats, date, from/to and
  derived start/end timestamps, rendered with `disabled: true`.
* `admin/src/views/seller-views/calendar/components/filter.jsx`, lines 10–47
  and 53–85: search plus master-role filter options refresh both bookings and
  disabled times.
* `admin/src/views/seller-views/calendar/components/service-card.jsx`, lines
  89–105, 107–129, 143–153: booking detail items show service, time and
  interval/pause, status/type, master, client, payment type and transaction
  status. Lines 155–203 also render amount/fee fields. Do not expose or enlarge
  finance details in a proposal without separately verifying existing
  permission scope.
* `admin/src/views/seller-views/calendar/components/calendar.jsx`, lines
  92–109: the fetched booking detail includes shop, client and payment
  selection data in the update form. The event mapping itself does not carry
  shop/branch or product fields. Show shop/branch context only where the
  underlying detail contract supports it; do not imply branch filtering or
  product scheduling in Calendar.

### Replacement composition recommendation

1. Base the calendar graphic on the native seller day-view and status/event
   model, with a clear “AgendaAlly for Business” heading and source-accurate
   booking event labels. The Calendar event mapping supports service title,
   times, booking status and master-disabled time; it does not support
   hotel/rental room names, room rates, check-in/out, occupancy or locked
   rental states.
2. If a booking detail is shown, keep it to fields present in the native
   detail: service, scheduled time, status/type, service master and client.
   Payment/transaction values exist in the detail component, but display them
   only after confirming the existing role/permission rules.
3. Treat products and branches as adjacent, separately verified business
   surfaces—not as Calendar event capabilities. The Calendar's event mapping
   contains neither product data nor a branch field. A shop selection is
   present in the update form, but that alone does not prove multi-branch
   Calendar filtering or branch scheduling.
4. Do not fabricate events, staff names, branches, booking counts, availability
   or store URLs in a native-looking screenshot. If a representative dataset
   must be used on the canvas, label it illustrative and keep it distinct from
   native-browser acceptance, as required by the Stage 2 brief.

## Extraction and data boundary

`CurrentBusiness` preserves the source page's ordered sections, utility-class
layout, feature icon SVGs, local public images, headline metric literals, and
shared-header/footer structure for visual comparison. Copied files are scoped
to `artifacts/mockup-sandbox/src/components/mockups/agendaally-stage2-business-current/`;
assets are under
`artifacts/mockup-sandbox/public/images/agendaally-stage2-business-current/`
and referenced with `/__mockup/images/` paths.

The original services/categories, brands, translations, site settings,
location, authentication, and application router are runtime/backend
dependencies. This sandbox copy uses no live API, auth or navigation. Its
category and brand API-shaped data are empty rather than fabricated; unknown
translation descriptions and dynamic settings are not asserted as fact.
Accordingly, the sandbox is an extracted source baseline, not a claim that
these API sections are empty in production and not a native-browser acceptance
test. The copied reservation screenshot and customer-app image intentionally
remain unchanged in this “Current” baseline so the review can compare them
against a separately proposed native Calendar illustration.

No Laravel contracts, schema, production source, credentials, package
manifests, global styles or workflows were changed.