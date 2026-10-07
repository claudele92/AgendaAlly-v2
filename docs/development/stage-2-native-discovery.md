# Stage 2 native discovery implementation

This note records the native discovery slice implemented after Stage 2 visual
approval. It does not retire or rewrite the legacy storefront variants.

## Changed paths

- `.migration-backup/web/components/search-field-core/discovery-domain-nav.tsx`
- `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/search/page.tsx`
- `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/search/components/shops/shops.tsx`
- `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/components/filters/filter-list.tsx`
- `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/services/page.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/products/page.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/products/components/filtered-product-list/filtered-product-list.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/products/components/filtered-product-list/product-list.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/products/components/filters/filter-list.tsx`

## Native behavior retained

- The shared discovery navigation links service discovery, products, businesses
  and specialists to their existing native routes. The products destination is
  shown only when the existing `products_enabled` setting is enabled.
- Service/category and matching-shop results continue to use the existing
  search context, shop/category APIs, filter query parameters and pagination.
  Product listing continues to use its separate product catalog/filter APIs,
  native currency/language/location inputs, pagination and product cards.
- The product filters expose the supported `in_stock` query parameter alongside
  the existing brand, category, price and product-property filters.
- Current persisted city/country context is displayed without changing it.
  Existing location selection controls, mobile service/location/date/time
  routes and optional map mode are unchanged.
- Search date/time criteria are explicitly described as discovery filters, not
  proof of an unoccupied booking slot. No shared search endpoint, distance
  claim, sample records, booking action or product transaction was introduced.
- Existing loading and empty presentations remain. Service/shop results,
  product results and their filter requests now surface retry controls on
  request errors; loaded results remain visible if a subsequent refresh fails.

## Verification and remaining scope

`git diff --check` passed. Per the implementation instruction, no build,
application server, workflow or test suite was run. The parent agent owns the
resource-isolated checks and preview review.

This slice does not implement the rest of the approved Stage 2 surfaces:
storefront home, business landing, business profiles, CMS/legal/support/footer,
or wider category art. Those surfaces and all four existing UI variants remain
available for the parent-owned implementation and review.