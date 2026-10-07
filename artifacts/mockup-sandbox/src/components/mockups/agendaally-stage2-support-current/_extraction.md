# AgendaAlly Stage 2 support surfaces — current native extraction

## Scope

`CurrentSupport.tsx` is a single sandbox composition of the requested native representative sections. It is **not** a complete native route or full-page rebuild. The JSX and utility classes below are copied from the original storefront source wherever noted; only the data, navigation, context, and component-library boundaries needed to render independently in the mockup sandbox are adapted.

## Source citations

All paths are relative to the repository root.

| Extracted section | Native source and relevant lines |
| --- | --- |
| Customer search controls | `.migration-backup/web/components/main-search-field/search-field.tsx:34-38, 68-119, 155-198`; icons from `.migration-backup/web/assets/icons/{search,location,calendar,clock}.tsx` |
| Customer search results | `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/search/page.tsx:17-54`; results header/list states and ShopCard usage from `.../search/components/shops/shops.tsx:100-150` |
| Search location control | `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/search/location/page.tsx:1-13`; manual and Google Places selector markup from `.migration-backup/web/components/search-field-core/place-select.tsx:18-105,107-260`; native floating-label input classes from `.migration-backup/web/components/input/input.tsx:53-113` |
| Search result shop card | `.migration-backup/web/components/shop-card-horizontal/shop-card.tsx:12-95`; rating phrase thresholds from `.migration-backup/web/utils/create-rating-text.ts:1-24`; map-pin and verification SVGs from `.migration-backup/web/assets/icons/{map-pin,verified}.tsx` |
| Shop profile top | `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/top-info/top-info.tsx:38-197` |
| Shop details and branch selector | `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/main-info/main-info.tsx:36-158` and `.../components/location/location.tsx:19-109`; source page composition at `.../shops/[id]/(detail)/page.tsx:124-150` |
| Blog list card | `.migration-backup/web/components/blog-card/blog-card.tsx:8-56`; list grid context from `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/blogs/page.tsx:10-14,36-61` |
| Blog article body | `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/blogs/[id]/page.tsx:67-106` |
| Terms reading layout | `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/terms/content.tsx:9-54` |
| Privacy reading layout | `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/privacy/content.tsx:9-56` |
| FAQ item | `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/faq/components/qa/qa.tsx:9-39`; section context from `.../faq/content.tsx:39-61` |
| Footer | `.migration-backup/web/components/footer/footer.tsx:12-279` |
| Contact notice | `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/contact/page.tsx:6-70` |
| Group typography/tokens | `.migration-backup/web/app/layout.tsx:35-39,131-143`; `.migration-backup/web/tailwind.config.js:36-89,107-120`; `.migration-backup/web/app/globals.css:410-412` |
| Local source assets | `.migration-backup/web/public/fonts/inter/InterVariable.woff2`; `.migration-backup/web/public/img/logo.png`; `.migration-backup/web/public/img/image-load-failed.png` |

## Stubs and limitations

- Router, `next/link`, query providers, APIs, settings, translation context, auth, and booking actions are not connected. Anchors are sandbox-local no-ops; the selector demonstrates local controlled input state only.
- Shop, blog, FAQ, and legal values are clearly marked local fixture snapshots in `CurrentSupport.tsx`; they preserve the source fields read by the extracted components, not authentic current API responses.
- The original API-driven shop/blog media and actual CMS legal, blog, and FAQ copy are unavailable in the checked-in source snapshot. Image slots use the original storefront fallback image copied into the group. The Terms/Privacy and Blog body copy is explicit local-only sample content, not published legal or editorial content.
- Google Places, Maps Static API, and map keys are intentionally not used. The location selector is the native manual-selector branch, with the native Google Places branch left outside this API-isolated preview.
- `next/image` is represented by a local `<img>` wrapper to preserve native `fill` sizing and local `/__mockup/images/` asset references. `next/link` is represented by the no-op `StaticLink`. Headless UI disclosure and transition wrappers are represented with local React state or native `<details>` because those native packages are not installed in the sandbox.
- The footer preview uses the native logo and information/help link groups. App-store links and social links remain absent because their settings values are not available.
- `_group.css` scopes the source Inter font and native Tailwind custom color/radius/container rules to this extraction only; no shared sandbox CSS or other mockup group was changed.