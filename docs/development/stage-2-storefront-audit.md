# Stage 2 — storefront source audit and current-view extraction

**Status: proposal-only source audit.** Stage 1's accepted scope authorizes the
Stage 2 design proposal, not native redesign, publication, provider activation,
or production operations. This work changes no storefront route, middleware,
backend contract, business/native app, or live-canvas state. The extracted
components are isolated sandbox references, not browser acceptance.

## Scope and source map

The audited source is the actual Next.js app in
`.local/agendaally-preview/web`; the four current sources are:
Unless a citation explicitly starts with `.migration-backup/` or
`docs/`, shortened `components/`, `services/`, `types/`, `global-store/`, and
`app/` references below are rooted at `.local/agendaally-preview/web/`.

| Public path | Source |
| --- | --- |
| `/` (View 1 unless middleware rewrites it) | `app/(store)/(booking)/(with-footer)/(home)/page.tsx` |
| `/home-2` (View 2) | `app/(store)/(booking)/(with-footer)/(home-2)/home-2/page.tsx` |
| `/home-3` (View 3) | `app/(store)/(booking)/(with-footer)/home-3/page.tsx` |
| `/home-4` (View 4) | `app/(store)/(booking)/(with-footer)/home-4/page.tsx` and `content.tsx` |

These pages share the `(with-footer)` layout, which appends the settings-backed
Footer after page content (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/layout.tsx:6–18`).
This source review did not capture fresh screenshots or exercise the routes;
the accepted native journeys and their limits remain in
`stage-1-external-acceptance.md`.

### `ui_type`, middleware, and page selection

- The Admin's General Settings UI offers four storefront choices, View 1–4
  (`.local/agendaally-preview/admin/src/views/settings/general-settings/ui-type.jsx:16–36, 161–170`).
  It saves a choice as `ui_type` after confirmation (`:76–85`).
- Middleware fetches settings and rewrites only pathname `/` to `/home-2`,
  `/home-3`, or `/home-4` for `ui_type` `"2"`, `"3"`, or `"4"`.
  `"1"`/unset leaves `/` on the View 1 page. Direct variant URLs remain
  accessible. (`.local/agendaally-preview/web/middleware.ts:92–123`.)
- `NEXT_PUBLIC_UI_TYPE`, when set, overrides the stored setting in middleware
  and root layout; it can therefore hold the storefront on a single view
  despite a different Admin selection. This is documented in code and is a
  deployment/configuration concern, not a new setting recommendation
  (`middleware.ts:8–24, 92–97`; `app/layout.tsx:96–103`).
- Do not interpret this proposal as changing or disabling any variant.

## Current-view findings and disposition

The dispositions below describe the **proposal** only. No variant has been
removed or switched off.

| View | What the source currently composes | Finding and proposed disposition |
| --- | --- | --- |
| **1 — `/`** | Shared header with desktop links; image-backed, centered hero; shared five-control service/location/date/time search; then Stories, service categories, Recommended shops, Masters, Deals, optional Brands and Products, Salons, Near You, and customer/business mobile cards. The service/category module is shared with View 3. (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home)/page.tsx:187–258`; shared module `.local/agendaally-preview/web/app/(store)/(booking)/components/services/services.tsx:23–103`.) | **IMPROVE — recommended structural starting point.** It establishes a clear service-discovery sequence and keeps the service-card/API patterns shared. Its hero line, “Book Services. Shop Favorites. Simple.”, names both domains but is generic and makes no useful promise beyond them (`.migration-backup/backend/resources/lang/translations.php:3192`). Products are still feature-flagged and placed after multiple service/business sections, so the actual first-screen hierarchy is not service/product parity. Keep the composition as the least-fragmented foundation, not its copy or current ordering. |
| **2 — `/home-2`** | Two-column photo collage, salon-centered headline, desktop avatar row and “10 people booked”, custom four-field search, then Stories, Recommended shops, Services, Near You, optional Products, Salons and promotional panels. Categories appear after Stories and Recommended. (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/page.tsx:125–214`.) | **IMPROVE — retain as a distinct hero concept, not a separate primary system.** The layered photo composition and orange emphasis are meaningfully different. “the best salon near you” is narrower than the supported marketplace, and `10` is hard-coded rather than tied to a sourced booking metric (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/page.tsx:141–156`). The hero's description is supplied only as translation key `home-2.hero.description`, not as page copy (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/page.tsx:138–140`); its runtime translation must be confirmed before any copy decision. There is also a source-level initial-data mismatch: the page prefetch requests category `type: "sub_main"` (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/page.tsx:96–104`) while the category widget refetches `type: "service"` under the same `["services", locale]` query key (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/components/services/services.tsx:26–40`). Verify the first hydrated category set before consolidation. |
| **3 — `/home-3`** | Animated WebGL gradient, shared header with desktop links, beauty-services headline, compact category/location search, shared service cards, then Stories, Services, Recommended, Masters, promotional cards, Deals, optional Products, and Near You. (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-3/page.tsx:160–224`.) | **CONSOLIDATE into View 1's shared structure.** It duplicates View 1's service-card component, marketplace widgets and much of the layout. Preserve the gradient/energy as an optional art direction, but do not maintain another full homepage tree just to retain it. Its current headline remains beauty-specific and its visible search omits date/time controls (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-3/page.tsx:165–170`). |
| **4 — `/home-4`** | Fixed-height, image-backed hero with outlined headline and shared full search; then Stories, Recommended shops, Masters, optional Products, Near You and Find Best Salon. The content has **no Services/category-card section**. (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-4/content.tsx:94–139`.) | **RETIRE as an independent storefront only after an approved replacement exists.** Its hero treatment is distinct, but the body repeats common shop/master/product sections while omitting a core service-discovery entry point. Preserve the background/outlined-type treatment as a component-level concept if useful; do not fabricate a category row to make the current page look fuller. The proposal does not retire the live route. |

### Shared systems, navigation, cards, and content hierarchy

- All four routes use the same `Header`. Views 1 and 3 pass `showLinks`; Views
  2 and 4 omit it. The shared header can show Shops, Deals, Blog and My
  appointments, with a menu drawer, country indicator, cart, Login and
  Business entry points depending on viewport/session
  (`components/header/header.tsx:37–75`; `components/header/links.tsx`; each
  page's `<Header>` call). The desktop country indicator is hidden through
  1024px; on mobile the drawer supplies marketplace links and country-change
  action (`components/country-indicator/country-indicator.tsx:12–43`;
  `components/header/sidebar.tsx:35–82, 84–111, 172–185`).
- The footer is shared outside the four page components. Its information,
  help, social and configured-download content is settings-driven. On mobile
  its columns collapse into disclosures; unconfigured social/download fields
  are not populated by the component (`components/footer/footer.tsx:16–57,
  59–76, 103–119, 146–162`). Do not treat absent official links as design
  placeholders: Stage 1 intentionally retained safe examples and unavailable
  store links instead of inventing official destinations.
- Views 1/3 share the same responsive service cards (twelve background colors
  and matching `service1.png`–`service12.png`, category icon, title). View 2
  uses its own compact, image-led `ServiceCardUi2` and Swiper carousel
  (`.local/agendaally-preview/web/app/(store)/(booking)/components/services/service-card.tsx:11–41`;
  `.local/agendaally-preview/web/app/(store)/(booking)/components/services/services.tsx:43–101`;
  `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/components/services/services.tsx:23–55`). Their visual treatment
  differs, but their entry point is still a service category, not an
  independently supported service catalog card.
- View 2 also selects a distinct shop card: image and logo, title/description,
  optional distance, optional review score; the shared card instead includes
  matched-branch/address fallback and optional verified/review details
  (`components/shop-card/shop-card-ui-2.tsx:21–99`;
  `components/shop-card/shop-card.tsx:21–99`). Preserve `matched_location` as
  the address source for a city-matched shop; do not replace it with a
  guessed home city.
- Master cards have actual specialist identity, portrait, role/title, rating,
  starting service price, and optional service location; the homepage Masters
  query filters out masters without a live shop invite/slug before rendering
  (`.local/agendaally-preview/web/components/master-card/master-card.tsx:17–67`;
  `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home)/components/masters/masters.tsx:21–53`). Product cards use
  product identity/gallery plus stock/variant/price behavior; they are not
  merely store cards (`components/product-card/product-card-ui-1.tsx:19–104`;
  `types/product.ts:68–149`).
- Category and marketplace taxonomy already extends beyond beauty in the
  owned demo seeders. The accepted Stage 1 record confirms Hair Care (1) and
  the Haircut category/service example; `CategoryCatalogExpansionSeeder`
  adds Tailoring, Dental Care, Healthcare, Handyman, Laundry & Dry Cleaning,
  and Home Cleaning (`stage-1-external-acceptance.md:295–308`;
  `.migration-backup/backend/database/seeders/CategoryCatalogExpansionSeeder.php:14–54`).
  That proves taxonomy/media provenance, **not** that each category currently
  has a nearby provider or appointment inventory. The new design must show
  availability honestly.
- Across all four pages, `products_enabled === "1"` gates the Products
  carousel. View 1 additionally gates a Brands carousel; the Products module
  is below categories/shops/other service content in every template. Preserve
  the real flag and catalog behavior, but give Products an equally legible
  first-class discovery path in a future approved proposal rather than
  implying present prominence (`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home)/page.tsx:149–150, 224–237`;
  `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/page.tsx:107–110, 196–201`;
  `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-3/page.tsx:143–144, 214–219`;
  `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-4/page.tsx:22–25`, `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-4/content.tsx:127–132`).
- Responsive source patterns are real: the service cards use Swiper
  breakpoints/grid on Views 1/3 and horizontal auto slides on View 2; the
  shared search becomes stacked controls on mobile. This is a source
  inspection, not fresh verification of focus order, screen-reader behavior,
  text scaling, overflow, or all search-popover states. Known accepted
  keyboard/focus and Footer evidence is bounded by Stage 1's matrix.

### Search and location: current supported behavior

The shared search is not a unified services-and-products search. It controls
service/shop discovery; Products have separate catalog/search endpoints and
their own filters. The shared `SearchFieldCore` serializes `date`,
`category_id`, `revenue` (the selected category label), `timeFrom`, `timeTo`,
`latitude`, `longitude`, and `location` to `/search`
(`components/search-field-core/search-field-core.tsx:49–65`). Date state is
formatted `DD, MM YYYY`; selected times are `HH:mm` (`:67–84, 86–98, 100–136`).
The search-results shop query reads `category_id`, `take[]`, price range,
discount, service type, gender, date and time range; it requests
`location_type: "2"` and uses the selected location or configured settings
coordinates (`.local/agendaally-preview/web/app/(store)/(booking)/(witout-footer)/(navigation)/search/components/shops/shops.tsx:27–95`).
The products API remains distinct: `GET v1/rest/products/paginate`,
`GET v1/rest/products/search`, and `GET v1/rest/filter`
(`services/product.ts:7–16`).

The four hero controls are not identical: Views 1/4 use the shared category,
location, date, time and Search UI; View 3 exposes category, location and
Search; View 2 uses a custom Search/Type, Location, Date, Time form
(`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home)/page.tsx:191–196`; `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-3/page.tsx:165–170`;
`.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/home-4/content.tsx:98–104`; `.local/agendaally-preview/web/app/(store)/(booking)/(with-footer)/(home-2)/home-2/components/search-field/search-field.tsx:54–110`).
Mobile category/location/date/time controls route to separate `/search/*`
pages in the shared form (`components/main-search-field/search-field.tsx:90–179`).
Do not present View 3's shorter surface as evidence that date/time filtering is
unsupported; those inputs exist in shared search state and the results query.

Location is country + optional city, not only the free-text `Where` field:

- A desktop `CountryIndicator` shows the selected country and opens the
  country-select modal. On mobile, the country action is in the navigation
  drawer. The first-visit dialog currently says “items that ship to …”, an
  e-commerce phrase that does not explain service-city discovery
  (`components/country-indicator/country-indicator.tsx:12–70`;
  `components/header/sidebar.tsx:106–111, 172–185`).
- The address store is persisted under Zustand's `address` key; the selector
  writes `country_id`/`city_id` cookies (or clears them), and homepage server
  requests use those cookies with the configured default-country/city fallback
  (`global-store/address.ts:5–45`; `components/country-select/country-select-form.tsx:35–55`;
  `app/layout.tsx:104–120`; each homepage's cookie reads and
  `resolveDefaultLocation` call). Stage 1 accepted Cameroon → Douala selection
  and persistence for that bounded native journey; this extraction does not
  repeat it.
- Homepage shop calls scope by `country_id`, `city_id`, and
  `location_type: "2"`. A configured latitude/longitude can still be a default
  search coordinate; it is not proof of a permission-derived user position.
  Keep the selected city explicit in a future location component, expose
  “change”, preserve the user's choice, and avoid “near you”/distance claims
  unless the result supplies valid distance under the actual request semantics.

## Exact API and data-shape inventory

No backend changes are proposed. `Paginate<T>` is `{ data, links,
meta(current_page, from, last_page, path, links, per_page, to, total) }`;
`DefaultResponse<T>` is `{ data, message, status, timestamp }`
(`types/global.ts:3–37`). Relevant source calls are:

| Domain | Actual endpoint / query | Response and important source fields |
| --- | --- | --- |
| Homepage categories | `GET v1/rest/categories/paginate`. Views 1/3 server and client request `lang`, `type: "service"`, `perPage: 11`, `column: "input"`, `sort: "asc"`; View 2's server prefetch uses `type: "sub_main"` before its widget asks for `"service"`. | `Paginate<Category>`; `Category` includes `id`, `active`, `keywords`, `parent_id`, `type`, `uuid`, optional `img`, nullable `translation`, optional `children` (`services/category.ts:6–9`; `types/category.ts:3–15`). |
| Recommended, Deals, Near You, search-result shops | `GET v1/rest/shops/paginate`, `Paginate<Shop>`; homepage requests commonly use `perPage: 8`, country/city IDs and `location_type: "2"`. Recommended sorts `r_avg DESC`; Deals sorts `b_count DESC`; search passes category/price/take/discount/service type/gender/date/time and sort/distance inputs. | `Shop` includes `slug`, `background_img`, `logo_img`, `translation.{title,description,address}`, review values, `locations`, and optional `matched_location` with branch address/geography. `matched_location` is the branch that matched the request; `locations` is the branch switcher list (`services/shop.ts:14–17`; `types/shop.ts:30–90`; homepage source rows above). The public list uses `cache: "no-store"` (`services/shop.ts:6–17`). |
| Specialists | `GET v1/rest/masters`, `Paginate<Master>`; homepage Master carousel uses `perPage: 12`, rating-desc ordering, currency/country/city scope. | `Master` extends `UserDetail` with `r_avg`, `service_min_price`, live `invite`/shop and `service_master`/service assignments. Rendering filters out absent invite-shop slugs (`services/master.ts:7–18`; `types/master.ts:34–41`; Masters source above). |
| Brands | `GET v1/rest/brands/paginate`, `Paginate<Brand>`; View 1 only renders this homepage carousel when products are enabled. | `Brand`: `id`, `uuid`, nullable `title`, `active`, `img`, `products_count`, timestamps (`services/brand.ts:6–9`; `types/brand.ts:1–10`). |
| Products | `GET v1/rest/products/paginate`, separate `GET v1/rest/products/search` and `GET v1/rest/filter`. | `Paginate<Product>`; `Product` extends stock-backed identity and includes `translation`, `stocks`, `galleries`. Stock has price/quantity/tax/discount and gallery/extra data (`services/product.ts:7–16`; `types/product.ts:68–149`). Product search is not evidence of unified search. |
| Stories | `GET v1/rest/stories/paginate?lang={lang}`; homepage prop is `Story[][]`. | `Story` carries `shop_id`, `logo_img`, `title`, `url`, `model_type`, `model_uuid`, and optional `shop_slug`/`model_title` (`services/story.ts:6–9`; `types/story.ts:1–12`). |
| Location options/settings | Settings-backed defaults; the country selector queries actual country/city APIs and writes the selected IDs. | Country/City identity uses `id`, `translation.title`, and relation IDs; do not replace those with guessed labels/coordinates (`types/global.ts:34–38, 96–110`; `app/layout.tsx:104–120`; `global-store/address.ts:26–45`). |

`Shop` is the key safe-card contract: render `matched_location` when present;
the stable translated shop address is not necessarily the branch that matched
the customer's selected city. `distance` is optional, so omit distance UI
when absent (`types/shop.ts:49–90`; `shop-card/shop-card.tsx:25–29, 76–97`;
`shop-card/shop-card-ui-2.tsx:68–79`). The accepted Stage 1 search evidence
returned one unique Hair Care result without fabricated distance.

## Proposal recommendation and preserved concepts

1. Base one approved future storefront on View 1's shared category/search and
   cross-domain components; replace its vague headline, reduce duplicated
   homepage trees, and deliberately raise Products beside Services.
2. Keep View 2's layered photography as an alternate hero composition, but
   remove unsubstantiated social proof and salon-only positioning unless
   product evidence supports them.
3. Preserve View 3's gradient only as a considered visual option; do not make
   service discovery depend on a WebGL-only treatment.
4. Preserve View 4's outlined typography/background treatment as optional art
   direction, not as a separate homepage.
5. In every future iteration, distinguish what current API contracts support
   from proposed unified search, new filters, ranking changes, or broader
   inventory claims. Do not change search, booking, branch, country or
   permission semantics in this audit.

These recommendations are not native redesign approval. The complete Stage 2
review still needs the wider desktop/mobile visual package and the explicit
approval gate in `stage-2-review-package-brief.md`.

## Sandbox extraction and provenance

The isolated reference components are under
`artifacts/mockup-sandbox/src/components/mockups/agendaally-stage2-current/`:

- `CurrentHome1.tsx`, `CurrentHome2.tsx`, `CurrentHome3.tsx`, and
  `CurrentHome4.tsx` retain the source hero/header/category compositions and
  source English hero copy where the tracked English translation is known.
- `current-baseline.tsx` keeps the header/search/card JSX in one local
  component without Next router, server fetch, API query, account, or address
  context. Its country label is a static **visual fixture**, not current
  visitor state.
- `_group.css` contains scoped native color/layout tokens and hero-image
  rules; it does not modify the sandbox's global CSS or Tailwind config.
- `NativeCanvas.tsx` and `gradient.js` preserve View 3's local gradient source.
- `public/img/agendaally-stage2-current/` contains byte-copied storefront
  `logo.png`, View 1/2 hero art, hero-avatar art, and service-card backdrops.
  `public/img/agendaally-stage2-current/categories/` contains copied local
  category SVGs.

The UI compositions intentionally isolate **hero, header and category** for
comparison. Native pages place category sections after Stories and (in some
views) other content; the sandbox moves the category section directly beneath
the hero, so these components are not full-page screenshots. It omits the
real shared footer, Stories, shop/master/product carousels, conditional
settings, loading/error states, popovers, fallback-image component, and
Next.js image optimizations. View 4 intentionally has no category row.

`categoryFixtures` uses real seed-backed category names and copied icons. The
fixture is illustrative, its IDs/ordering are non-operative (only Hair Care
ID 1 is recorded in the accepted seed evidence), and links are prevented from
navigating. It must not be represented as an API snapshot or claim of
provider/service availability. The static “10 people booked” row deliberately
mirrors View 2's source constant but has no live metric behind it. View 2's
unresolved-in-page translation key is shown verbatim rather than replaced by
invented marketing copy.

The assets are sourced from the storefront's existing public asset paths
(`web/public/img/*`, `web/public/icons/categories/*`), not newly generated.
The source/accepted evidence does not establish per-image licenses or a
redistribution grant; media-rights/relevance review remains a separate
follow-up before public release. The static logo is the local public asset,
not a claim to reproduce a settings-uploaded tenant logo.

Validation: `pnpm --filter @workspace/mockup-sandbox typecheck` passed. No app
was launched and no workflow was run.