# Stage 2 native marketplace homepage

The approved Stage 2 homepage direction is implemented in the original
storefront root route at:

- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(home)/page.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(home)/components/`

The page now uses the approved headline, keeps the existing native `Header`
invocation, and places service and product discovery paths together above the
marketplace sections. The hero reads country and city from the persisted
address store; country/city changes remain with the existing header control.
The service category selector is populated from the native service category
request and submits the selected supported category to `/search`.
The root homepage replaces the old backdrop-based service cards with a
CategoryPictogram grid built from all pages of the native service category API,
including the nested categories returned with parents. Each card uses its real
category ID and title and opens the existing category search; the other
homepage variants and shared legacy service widget remain untouched.

Product discovery stays behind the native `products_enabled` setting. Product
categories come from the separate product filter API. A submitted product
query uses the existing `productService.search` API and shows its actual
results; result links use the existing `/products/[uuid]` detail route. The
native `/products` route remains the browse-all destination. Its current list
does not consume a `search` query parameter, so this homepage does not pretend
that `/products?search=...` is a working filter. The existing product-search
endpoint does not establish city/distance scope, so the homepage does not
describe those search results as city-filtered or nearby.

The homepage retains its native service taxonomy, stories, business/shop
discovery rails, specialists, product carousel and brands, location-aware shop
queries, feature flags, account/business mobile cards, and existing routes.
New photography is used only as visibly
labeled illustrative promotional art; it is not associated with a named
business, specialist, product, price, rating, or availability claim.

No backend, schema, seed data, payment, booking, authentication, authorization,
proxy, middleware, header/footer implementation, theme, or other route was
changed in this scoped homepage implementation. Build, server, and test checks
were intentionally not run; final checks remain with the parent agent.