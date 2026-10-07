# Stories audit and focused Stage 2 proposal

**Scope:** read-only trace of the original Laravel, Admin/Vendor portal, customer
Web and Flutter Stories flow, plus a design proposal only. No Stories source,
schema, media, seed, runtime or database changes were made.

## Summary and disposition

| Decision | Finding |
| --- | --- |
| Classification | **ACTIVE** — there is a complete create/manage/read path in the native seller API and portal, public Web API and homepage viewer, and Flutter app. This is a source-flow classification, not a claim about production usage. |
| Recommendation | **IMPROVE** — retain Stories as a secondary, business-attributed discovery feature. Improve the lifecycle/status contract and present the existing capability in a restrained Stage 2 business-updates rail. Do not make it a social feed or invent views, promotions, availability, reactions, or new Story types. |
| Current Stage 2 representation | **Present.** The Stage 2 native homepage renders Stories after the hero/discovery paths and before service categories when the endpoint returns nonempty data. (`.migration-backup/web/app/(store)/(booking)/(with-footer)/(home)/page.tsx:113–138`; `docs/development/stage-2-native-home.md:31–36`.) |
| Accepted development content | **None at audit time.** A read-only aggregate/schema query against the owned development SQLite database returned 0 Story rows; no Story media or row contents were read. The safe curated seed deliberately excludes `BlogStorySeeder` and its expiring Story examples. (`.migration-backup/backend/database/development/README.md:70–89, 101–116`; `.migration-backup/backend/database/seeders/DevelopmentDemoSeeder.php:80–108`; `.migration-backup/backend/database/seeders/DevelopmentPreviewContentSeeder.php:21–42`.) |

## Native domain and lifecycle

### Meaning, ownership and schema

- A Story is a set of media URLs attached to a shop and one polymorphic related
  entity. `Story::TYPES` permits `shop`, `product`, or `service`; the model has
  `shop_id`, `model_id`, `model_type`, `active`, and JSON-cast `file_urls`.
  (`.migration-backup/backend/app/Models/Story.php:38–65`.)
- The original table began with `product_id`, `shop_id`, `active`, JSON
  `file_urls`, and timestamps (`.migration-backup/backend/database/migrations/2022_12_04_074133_create_stories_table.php:14–34`).
  Later migrations made `product_id` nullable and added the `model` morph
  columns (`.migration-backup/backend/database/migrations/2023_10_16_085931_change_product_id_in_stories_table.php:14–27`;
  `.migration-backup/backend/database/migrations/2025_06_17_190026_change_product_id_to_morph_in_stories_table.php:14–19`).
- The read-only `PRAGMA table_info(stories)` on
  `.migration-backup/backend/database/development/agendaally.sqlite` confirmed
  the current development schema includes `id`, `shop_id`, `active`,
  `file_urls`, timestamps, legacy nullable `product_id`, `model_type`, and
  `model_id`. Aggregate-only output was: **0 total rows, 0 linked shops**.
  `SUM(active)` and date extrema were null because the table was empty. This
  query did not inspect file URLs, customer data, or credentials. The path is
  the owned development SQLite documented in
  `docs/development/stage-2-media-audit.md:5–12`; the backend README requires
  the ownership marker and reviewed migration ledger for existing-file use
  (`.migration-backup/backend/database/development/README.md:70–89`).

### Create/manage permissions and moderation

- Authenticated seller routes are under `dashboard/seller`, whose role group is
  `seller|moderator|admin|shop_manager`; `SellerBaseController` applies
  `check.shop` and resolves the authenticated user's shop
  (`.migration-backup/backend/routes/api.php:529–530, 717–722`;
  `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/SellerBaseController.php:17–28`).
  Story routes do not have a separate `shop.permission:*` wrapper in the
  route group; creation does force the record's `shop_id` to the current shop
  (`.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/StoryController.php:46–51`).
- The vendor portal has a Stories list with Add/Edit/Delete, and the form
  selects a supported model type, related product/service or the vendor's shop,
  and one image (`.migration-backup/admin/src/routes/seller/story.js:4–17`;
  `.migration-backup/admin/src/views/seller-views/story/index.jsx:193–255`;
  `.migration-backup/admin/src/views/seller-views/story/components/form/form.jsx:108–119, 146–180, 183–231`).
  The list is constrained by the authenticated shop in the controller
  (`.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/StoryController.php:32–36`).
- **Ownership hardening gap:** route-bound `show` and `update` accept a `Story`
  and do not compare its `shop_id` to the authenticated shop before returning
  or updating it (`.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/StoryController.php:69–103`). The service's
  delete path does scope by shop ID when passed one (`.migration-backup/backend/app/Services/StoryService/StoryService.php:62–76`).
  Keep the route/model-binding behavior in mind for a separate focused
  authorization fix; this audit did not exercise mutation routes.
- Admin/manager dashboard routes expose list, show, delete, and drop-all under
  `role:admin|manager` (`.migration-backup/backend/routes/api.php:915–916,
  1225–1228`). The Admin page lists image, related model type, title and
  expiry, with pagination and loading (`.migration-backup/admin/src/views/story/index.jsx:24–70, 74–89, 109–121`).
  It has no approve/reject or active-status moderation control; delete is the
  available management action (`.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Admin/StoryController.php:29–47, 56–74`).
- The model and requests permit an `active` boolean, but the seller form does
  not expose it and the public `StoryRepository::list()` does not filter it.
  The model scope can filter by `active` only when a caller supplies it
  (`.migration-backup/backend/app/Models/Story.php:67–80`;
  `.migration-backup/backend/app/Http/Requests/Story/StoreRequest.php:17–25`;
  `.migration-backup/backend/app/Repositories/StoryRepository/StoryRepository.php:40–52`).
  Public exposure currently requires an associated shop whose status is
  `approved` (`.migration-backup/backend/app/Repositories/StoryRepository/StoryRepository.php:60–68`), but does not require
  `active=true`.

### Media, public response and customer interaction

- `file_urls` is an array of strings. The seller upload request requires a
  files array with a size range but the video-duration validator is commented
  out; the service uploads into the `stories` storage path
  (`.migration-backup/backend/app/Http/Requests/Story/StoreRequest.php:19–25`;
  `.migration-backup/backend/app/Http/Requests/Story/UploadFileRequest.php:15–31`;
  `.migration-backup/backend/app/Services/StoryService/StoryService.php:80–118`).
  The vendor UI uses a single `MediaUpload` field and saves one URL
  (`.migration-backup/admin/src/views/seller-views/story/components/form/form.jsx:193–223, 108–119`).
  The source proves URL-array storage and image-oriented customer displays; it
  does not establish a reliably validated, customer-supported video format.
- The public route is `GET v1/rest/stories/paginate`, backed by
  `StoryController::paginate()` and `StoryRepository::list()`
  (`.migration-backup/backend/routes/api.php:192–194`;
  `.migration-backup/backend/app/Http/Controllers/API/v1/Rest/StoryController.php:22–25`).
  It groups results by `shop_id`, returns shop identity (translated business
  title, logo, UUID/slug) and associated model identity plus one entry per
  media URL. It limits by `created_at >= date(-1 day)` and approved shop, not
  the requested country/city or `active` flag
  (`.migration-backup/backend/app/Repositories/StoryRepository/StoryRepository.php:40–52, 60–101`).
- The Web homepage invokes this API with `lang`; a failure is caught as
  `undefined`, and the Stories section is omitted for absent/empty response.
  Its Stage 2 placement is after the hero and discovery paths, before
  categories (`.migration-backup/web/app/(store)/(booking)/(with-footer)/(home)/page.tsx:75–86, 113–138`).
  The Web rail groups by shop and opens a viewer; the viewer advances between
  attached items and offers only the currently supported shop, product, or
  service destination (`.migration-backup/web/app/(store)/(booking)/components/stories/stories.tsx:23–40`;
  `.migration-backup/web/app/(store)/(booking)/components/stories/sub-stories.tsx:37–50, 53–85, 121–124`).
- Flutter requests the same public endpoint without requiring authentication,
  sending language and local country/city/region parameters
  (`.migration-backup/customer_app/lib/infrastructure/repository/shops_repository.dart:114–137`).
  The server-side feed method currently uses language and per-page for its
  query; those location parameters are not applied by `StoryRepository::list()`
  (`.migration-backup/backend/app/Repositories/StoryRepository/StoryRepository.php:40–52`).
  The Flutter home shows a horizontal Story rail, tap opens a full-screen viewer,
  and viewer actions route to associated shop/service/product
  (`.migration-backup/customer_app/lib/presentation/pages/home/widgets/story_list.dart:22–75`;
  `.migration-backup/customer_app/lib/presentation/pages/story/story_page.dart:41–90`;
  `.migration-backup/customer_app/lib/presentation/pages/story/widgets/story_image.dart:69–91, 173–207`).
- **No view/read/interactions tracking was found.** The schema has no view
  counter or viewer relation; the public controller only reads, and the Web
  viewer's timer/index state is client-side navigation (`.migration-backup/backend/app/Http/Controllers/API/v1/Rest/StoryController.php:22–25`;
  `.migration-backup/backend/database/migrations/2022_12_04_074133_create_stories_table.php:16–34`;
  `.migration-backup/web/app/(store)/(booking)/components/stories/sub-stories.tsx:34–36, 74–85`). There is no
  supported per-customer “seen” state to display.

### Expiry and development content

- Public feed includes Stories created from the prior calendar date onward.
  A scheduled daily command deletes Stories through the end of the prior day;
  its `orWhere` repeats the same cutoff (`.migration-backup/backend/app/Repositories/StoryRepository/StoryRepository.php:51–52`;
  `.migration-backup/backend/app/Console/Kernel.php:17–27`;
  `.migration-backup/backend/app/Console/Commands/RemoveExpiredStories.php:43–63`).
  Admin/Vendor tables independently display `created_at + 24 hours` as expiry
  (`.migration-backup/admin/src/views/story/index.jsx:46–59`;
  `.migration-backup/admin/src/views/seller-views/story/index.jsx:67–82`).
  This makes expiry operationally present, but the exact feed cutoff, daily
  deletion timing and portal's 24-hour display are not one explicit shared
  expiry contract. The scheduled expiry command deletes the Story model
  directly; unlike `StoryService::delete()`, that path does not call
  `removeFiles()`. Local uploaded media can therefore be left behind when
  expiry removes a row (`.migration-backup/backend/app/Console/Commands/RemoveExpiredStories.php:45–51`;
  `.migration-backup/backend/app/Services/StoryService/StoryService.php:62–71`).
- Legacy `BlogStorySeeder` creates up to three sample shop Stories only when
  the entire Story table is empty and points to remote image URLs
  (`.migration-backup/backend/database/seeders/BlogStorySeeder.php:76–94`).
  It is not part of the accepted guarded development seed: the curated seed's
  allowlist ends without it (`.migration-backup/backend/database/seeders/DevelopmentDemoSeeder.php:80–108`); preview
  content explicitly avoids legacy content seeders because
  `BlogStorySeeder` writes expiring Stories (`.migration-backup/backend/database/seeders/DevelopmentPreviewContentSeeder.php:21–42`).
  The read-only owned-database count is therefore **0**, not evidence that the
  application implementation is dead.

## Classification: ACTIVE; recommendation: IMPROVE

**Why ACTIVE:** the supported flow is more than a leftover model/table:
seller creates and manages shop-attributed Stories; Admin/Manager can review
the list and delete; the public API provides recent Stories for approved
businesses; both current Web and Flutter customers display media and navigate
to its attached shop/product/service. (Citations above.)

**Why IMPROVE rather than retire:** the native customer surfaces and Stage 2
homepage already use the feature. Retiring it would remove an existing
marketplace-discovery path. A narrow improvement should: (1) settle active and
  expiry semantics and media cleanup so a disabled or expired item is never
  offered publicly and expiry does not leave orphaned uploads;
(2) scope seller `show`/`update` to the seller's shop; (3) align location
expectations or clearly document that Stories are not location-filtered; and
(4) bring the rail into the Stage 2 visual language without claiming
viewed/unviewed analytics. This document proposes, but does not implement,
those changes.

## Focused Stage 2 design proposal (desktop + 390px)

### Placement and visual language

Keep a secondary **Business updates** section in the existing homepage order:
below the hero and discovery paths, above the service category grid. This
preserves the current Stage 2 flow while leaving Services, Products, Businesses,
Specialists and Categories as primary destinations
(`.migration-backup/web/app/(store)/(booking)/(with-footer)/(home)/page.tsx:113–138`;
`docs/development/stage-2-native-home.md:9–19, 31–36`).

Use warm ivory/off-white surface, dark text, a restrained bronze hairline/accent,
and understated radius; no social-feed language, engagement counters or
Instagram-style progress ring on the business cards. Give every card a visible
business title and associated business logo/photo when supplied by the API.
Use the actual current Story media as media—not a category image, product
substitute, or generated image falsely assigned to a business. On the opened
viewer, preserve the existing media's natural portrait proportions with
`contain`; use a consistent `4:5` crop only for small rail thumbnails. Do not
add a viewed/unviewed border: the backend supplies no viewer state.

### Layout and interaction sizes

| Surface | Proposal |
| --- | --- |
| Desktop (≥1024px) | Align to the Stage 2 content container (max-width `1200px`). Section header and a single horizontally scrolling rail. Cards approximately `168px` wide × `266px` high, with a `4:5` image panel and a `56px` business identity row; `16px` gap. At a 1200px content width, show several complete cards and a partial next card as an overflow affordance without expanding page width. |
| Mobile (390px) | Keep page gutters `16px` (358px usable rail width). Cards `112px` wide × `168px` high, `4:5` preview plus compact business-name strip; `12px` gap. Horizontal scrolling is confined to the rail, never the document. Make each entire card a semantic button with at least `44×44px` target and a truncated-but-readable business label/accessible name. |
| Keyboard / assistive technology | Rail receives a clear “Business updates” label. Cards are in normal tab order; Enter/Space opens the selected existing Story viewer. Provide visible focus, labelled previous/next/close controls with `44×44px` hit areas, and left/right key support only while the viewer is open. Escape closes the viewer. Do not require swipe gestures; respect reduced-motion preference for transitions/autoplay. These are recommendations; current Web source does not prove all these behaviors. |
| Loading | The homepage currently awaits server data and silently omits Stories on a fetch failure. If the fetch becomes client/refetch driven, use fixed-size neutral skeleton cards to prevent layout shift, then replace them with actual results. Do not expose fake thumbnails or business names. |
| Empty / error | A successful empty response should leave the rail absent, as today. A failed request should be distinguishable from a successful empty response and offer a retry only if a retryable request is introduced; do not relabel failure as “no businesses.” |
| Expired | Rely on a single server-authoritative expiry rule and exclude expired items from the public rail. Never render stale cache as current. If an already-open item becomes unavailable, close/refresh the viewer rather than inventing an expiry badge or a new Story state. The existing contract needs lifecycle alignment before claiming exact 24-hour behavior. |

Business identity and navigation remain grounded in actual fields and current
links: the feed has business title/logo/slug and model type/UUID; existing viewer
destinations are shop, product, and service. Do not add a destination for any
other type, location-specific ordering, ranking or synthetic customer signal
(`.migration-backup/backend/app/Repositories/StoryRepository/StoryRepository.php:77–93`;
`.migration-backup/web/app/(store)/(booking)/components/stories/sub-stories.tsx:40–50`).

### Smallest future synthetic fixture (proposal only)

No accepted Story fixture exists in the owned development database. If a
representative Stage 2 preview is approved later, the minimal safe fixture is
one idempotent Story attached to one existing approved **synthetic**
development shop and its own `Shop` model (`model_type=shop`, `model_id` that
shop ID), with one reviewed, rights-cleared local image. Add it only through
the guarded owned-development seed; do not insert a database row manually,
use customer communications, claim a promotion/availability, or create
bookings/orders/payments. Do not copy the legacy remote Story image URLs into
a new fixture without provenance review: the Stage 2 media audit says image
rights were not independently verified (`docs/development/stage-2-media-audit.md:33–42, 75–81`).
If no approved local image is available, defer the fixture rather than invent
or generate media in this audit.

## Explicit boundaries / open questions

- This is not a production record count or a production-use statement; the
  count is only for the owned local SQLite file.
- Source audit did not sign in to Admin, Vendor, Web, or Flutter and did not
  run or test any route. No interactive/keyboard behavior beyond the cited
  source was externally verified.
- No Stories modifications, synthetic Story fixture, image generation,
  app build, database writes, or customer-data access were performed.
- Before implementation approval, decide the exact shared expiry cutoff,
  whether `active` should gate public Stories, and the authorization correction
  needed for seller route-bound `show`/`update`. Keep the feature's scope to
  business/service/product marketplace updates; do not add view tracking or
  social interactions without separate product and privacy approval.