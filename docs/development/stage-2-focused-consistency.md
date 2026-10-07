# Stage 2 — focused UI/data consistency

Authority: the creator's focused correction attachment of 1 October 2026.
This is not a redesign, Stage 1 reopening, payment audit or publishing approval.

## Implementation

- Service discovery represents actual category parent IDs: 14 service roots,
  with direct children revealed on selection through the existing category API.
  Product taxonomy remains distinct. Native search/category/geography contracts
  are preserved.
- Locally authored semantic SVG category marks share a padded 64px viewBox and
  consistent stroke; canonical image slugs and supported root identities select
  marks, with a catalog-list fallback. Child choices use restrained chips.
- Shared business, horizontal/mini-business, specialist, product, brand and deal
  cards use the approved native visual language. Customer English uses
  Specialists/Top specialists/New businesses; internal IDs remain unchanged.
- Repeated serum cards represent distinct intentionally seeded shop listings.
  Seller metadata distinguishes them without deleting data. Lightweight public
  metadata avoids invented finance values; full product-detail shop relations
  retain their existing resource behavior. Native web card requests explicitly
  opt in with `seller_metadata=1`; shared default listings remain unchanged for
  Flutter, including its interim list-to-detail shop behavior.
- Admin displays one read-only AgendaAlly Marketplace storefront. Internal
  `ui_type` remains compatible with Flutter and existing presentation consumers;
  web root/legacy bookmark routing is unchanged.
- Footer uses existing Settings description/footer_text and configured
  social/customer-app fields. Missing, invalid and example destinations are
  hidden; copyright supports the current year and location uses persisted
  country/city. Official destinations can be configured later without redesign.
- Existing TermCondition/PrivacyPolicy translations contain original AgendaAlly
  legal drafts, with owner/legal review inside the documents. Existing Page/
  PageTranslation About content and Faq/FaqTranslation are upgraded only when
  recognized as owned placeholders; educational Blog/BlogTranslation is retained.

## Reproducibility and protected data

`node scripts/development.mjs seed --content-only` uses the existing owned SQLite
guard and transaction, and now limits that branch to translations/CMS/footer.
The full bootstrap/demo path is unchanged. No payment catalog is seeded by the
content-only branch.

`scripts/development/verify-owned-content-seed.php` ran that seed twice, checking
ownership and comparing fingerprints without emitting row data. Content values,
IDs and counts were identical after both runs (updated timestamps ignored).
176 protected tables / 1,875 rows were unchanged, including accounts, category/
product records, bookings/orders, payments, accounting and wallets. No reset,
destructive migration, schema change or production access occurred.

Owner inputs remain official social/app-store destinations, final legal
identity/contact and approval of legal drafts, and canonical production brand
assets if required. Their absence is not a development blocker.

## Focused verification

Native web TypeScript, scoped category/footer/legal/routing tests and Admin
storefront regression passed. Both clean native web/Admin production compilations
passed, without provider/release verification or native mobile builds. Subsequent
small query/loading corrections passed TypeScript/scoped regressions. Serializer
coverage passed with 4 tests/31 assertions; scoped seed coverage passed with
6 tests/151 assertions.

Desktop and 390px review verified populated homepage/discovery/product cards,
distinct serum seller labels, child choices, persisted Douala footer context,
hidden unconfigured social/app destinations, copyright, Terms and Privacy.
Embedded-child loading and the twelve-root services cutoff were corrected.
The first multi-route review hit native preview availability/cold-navigation
delays. Resource-isolated route checks succeeded; no unrelated journeys were
opened, no broad acceptance was repeated and no production access occurred.

Final `/services` review confirmed all 14 roots at desktop/390px with Education's
five and Tattoo & Piercing's four children. Admin's read-only storefront panel
passed both widths with no theme controls or horizontal overflow. Both original
previews were restored: public Customer `/` and Admin `/login` returned HTTP 200;
all three legacy homepage URLs returned 302 to `/` preserving both test query
parameters. The existing targeted ESLint command could not start because
`@eslint/compat` is absent; no dependency changes were made for this focused pass.