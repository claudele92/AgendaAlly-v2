# AgendaAlly — Original Repository Audit

**Date:** 30 September 2026  
**Scope:** The original imported repository in `.migration-backup/`, not the Replit API/Canvas scaffold.  
**Deliverable:** Source audit, proposed Laravel-first architecture, and approval-gated migration plan.  
**Implementation status:** No original application code, schema, dependencies, configuration, or data changed during this audit. No rebuild, port, deployment, live payment, payout, or message was performed.

## Executive assessment

AgendaAlly is already a substantial Laravel-backed marketplace with **two first-class business lines: service bookings and product orders**. It is not a blank application and does not need an unrelated Express backend or a fresh Laravel replacement.

The original source contains Laravel 12, a Next.js customer storefront, a React administrative/vendor application, and a Flutter customer application. Recent changes already introduce shop roles, country administration, branch assignments, country-specific payment availability, encrypted shop/platform gateway configuration, fixed/percentage service fees, fee ledger entries, and booking platform-collection snapshots. These must be retained and hardened, not assumed absent.

The strongest reasons not to proceed directly to a rebuild are:

1. **Critical installation exposure:** unauthenticated routes can issue a token for the first user and invoke destructive fresh migrations; the IP-block middleware expressly exempts installation routes.
2. **Payment authenticity and accounting gaps:** several public callbacks accept caller-supplied payment status; collection routing uses the shop's live setting while booking liabilities use a frozen setting; orders lack the equivalent seller-payable lifecycle.
3. **Inventory and scheduling integrity:** transactional code exists, but stock writes are read/modify/write operations without row locks, and appointment overlap/availability checks have gaps.
4. **Existing mobile contracts are specific and inconsistent:** preserve `/api/v1`, identifiers, status strings, conditional resource fields, wrappers, pagination, and date formats before changing internals.
5. **Runtime is unverified:** PHP, Composer, Flutter, Dart, original dependencies, and an isolated original database are unavailable here. This report does not claim that any original client builds, that tests pass, or that a provider successfully moves money.

**Recommendation:** approve an isolated runtime baseline and targeted security containment first. Then progressively extract a shared Laravel domain layer while preserving both booking and product-commerce behavior. Introduce Inertia/React by selected web surface, not by removing the REST API or replacing every client at once.

## Evidence, methodology, and limitations

### Evidence labels

- **S — Static implementation:** traced executable source, validation, relationships, and relevant callers. It establishes code behavior, not a successful deployed flow.
- **D — Documentation claim:** operational guidance or comments; not independent proof.
- **R — Runtime verified:** none of the original application's flows qualify in this audit.
- **U — Unknown:** depends on runtime, deployed configuration, provider accounts, data, or unexecuted tests.
- **G — Gap/partial implementation:** a target capability is incomplete in the traced source.
- **B — Source-demonstrated defect:** a specific predicate, amount, route mismatch, or unsafe operation can be identified statically. Production exploitability or financial loss is not claimed without runtime evidence.

### Inspection performed

The original component trees were recursively inventoried. Dependency manifests and lockfiles, route groups, request/resource formats, models/migrations, middleware, services/repositories, observers, client repositories/hooks, scheduled work, tests, assets, and deployment guidance were inspected. Five independent read-only traces covered payments/accounting; marketplace/booking; commerce; identity/country; and clients/operations. Critical findings and contradictory initial observations were reconciled against the source.

This is comprehensive coverage of the requested domains, **not a claim that every line or every authorization action in a repository of thousands of files was formally verified**. Ancillary module existence is distinguished from deep end-to-end tracing. An unexecuted test is evidence of intent/assertions, not a passing result.

### Runtime and data blockers

- `php`, `composer`, `flutter`, and `dart` are not installed; Node and pnpm are available for the existing scaffold only.
- Original `backend/vendor`, `web/node_modules`, `admin/node_modules`, and `customer_app/.dart_tool` are absent.
- No complete application SQL dump/database snapshot was found. `backend/resources/lang/translations_en.sql` is translation data, not a business database.
- No live database, secrets, gateway accounts, demo account values, customer records, or production deployment was accessed.
- No original PHP lint, Artisan route listing, migration, PHPUnit, Next/Vite build, Flutter analysis, browser flow, or provider sandbox call was executed.
- Existing Replit API Server and Canvas workflows are **not AgendaAlly** and were not used to establish product functionality.
- There is no basis to certify production readiness or real provider functionality from this workspace alone.

### Source citation convention

Unless otherwise noted, original-source paths below are relative to `.migration-backup/`; `docs/audit/` paths refer to the report files in the workspace. Line ranges identify the inspected source snapshot; future edits may move them. For example, `backend/routes/api.php:60–66` means `.migration-backup/backend/routes/api.php`, lines 60–66.

Shorthand citations use these locations:

| Shorthand | Exact original-source base |
| --- | --- |
| `api.php`, `web.php` | `backend/routes/` |
| `*Resource.php` | `backend/app/Http/Resources/` |
| `BookingService.php`, `BookingRepository.php` | `backend/app/Services/BookingService/`, `backend/app/Repositories/BookingRepository/` |
| `OrderService.php`, `CartOrderService.php`, `POSOrderService.php`, `OrderDetailService.php`, `OrderRefundService.php`, `OrderStatusUpdateService.php` | `backend/app/Services/OrderService/` |
| `CartService.php` | `backend/app/Services/CartService/` |
| `BaseService.php` and named gateway services | `backend/app/Services/PaymentService/` |
| Named gateway controllers | `backend/app/Http/Controllers/API/v1/Dashboard/Payment/` |
| `TransactionObserver.php` | `backend/app/Observers/` |
| `User.php`, `Order.php`, `Booking.php`, other domain models | `backend/app/Models/` |
| `Utility.php`, `OrderHelper.php`, `CountryContext.php` | `backend/app/Helpers/` |
| `InviteService.php`, `CountryAdminService.php`, `CountryInviteService.php`, `PaymentToPartnerService.php`, `PayoutService.php`, `ShopLocationService.php` | Respective same-named service directories under `backend/app/Services/` |
| Test names without directories | `backend/tests/Feature/`, except nested test paths explicitly supplied |
| Flutter repository/model file names | `customer_app/lib/infrastructure/repository/`, `customer_app/lib/domain/model/response/` or `model/` as stated |

---

## 1. CURRENT STACK

| Component | Actual technology | Evidence and implication |
| --- | --- | --- |
| Business backend | PHP `^8.4`, Laravel `^12.0`; lockfile Laravel `v12.46.0` | `backend/composer.json:7–44`; `backend/composer.lock`. Existing Laravel engine should be modernized in place. |
| Authentication/permissions | Sanctum `^4.2` / locked `v4.2.2`; Spatie permissions `^6.0` / locked `6.24.0` | Personal tokens plus custom middleware and shop/country permission logic, not an existing comprehensive Policy architecture. |
| Customer web | Next `16.0.10`, React/DOM `19.2.3`, TypeScript, App Router, TanStack Query v4 | `web/package.json:1–17,19–68`; `web/app/layout.tsx`; independent `yarn.lock`. Package name `ibeauty` is an internal legacy name, not reason to replace branding. |
| Admin/vendor/master web | Vite `^7.3.0`, React 18, Ant Design `4.20.6`, React Router v6, Axios `1.12.0`, Redux | `admin/package.json:1–82`; `admin/src/app.jsx`. Package name `demand` is also legacy. |
| Customer mobile | Flutter `>=3.38.5`, Dart `>=3.10`, Dio, BLoC, Firebase, maps, WebView | `customer_app/pubspec.yaml:1–12,18–32,82–149`; `pubspec.lock`. One customer Flutter project is present. |
| Data storage | Eloquent, MySQL-default configuration; alternative connection definitions exist | `backend/config/database.php:18,38–85`. No database engine/version or actual applied migration state was verified. Do not swap to PostgreSQL from configuration alone. |
| Background work | Laravel commands, jobs, scheduler, configurable queues | `backend/app/Console/Kernel.php:12–31`; queue default is `sync`, configurable to database/Redis/etc. |
| Cache/sessions/files | Cache default `file`; sessions default `database`; local/public/private/S3 disk definitions | `backend/config/{cache,session,filesystems}.php`. These are defaults, not verified deployed settings. |
| Provider libraries | Stripe, PayPal, Paystack, Razorpay, PayTabs, Mercado Pago, Firebase, AWS/S3, Twilio/Vonage, PHPMailer/SendGrid, Dompdf/Excel | `backend/composer.json:17–44`. A listed package is not proof an integration works. Some other source providers reference SDKs missing from Composer. |

The backend still uses the older explicit Application/Kernel bootstrap structure despite Laravel 12 dependencies (`backend/bootstrap/app.php:14–44`). This is not itself a demonstrated incompatibility: Laravel upgrades need not adopt a new skeleton. Preserve it until an isolated boot establishes what actually needs change.

## 2. CURRENT APPLICATION STRUCTURE

### Source inventory

| Original component | Files recursively inventoried | Important internal counts |
| --- | --- | --- |
| `backend/` | 1,692 | 183 models; 288 controllers; 246 request classes; 137 resources; 150 services; 114 repositories; 213 migration files; 43 seeders; 40 PHP files under tests, including support classes |
| `admin/` | 1,776 | Predominantly JSX/JS, route/menu/auth infrastructure, admin/seller/master/deliveryman screens and assets |
| `web/` | 1,073 | Predominantly TSX/TS; App Router pages, services, providers, middleware, assets |
| `customer_app/` | 876 | 694 Dart files plus Android/iOS configuration and assets |

Counts include utility/support files, not just features. There are 37 Feature test files, not 37 verified test outcomes. A textual inventory counted 1,011 `Route::` lines in `api.php`; this includes declarations/groups/comments and is **not** the expanded runtime endpoint count.

### Current request flow

```text
Next storefront / React admin-vendor / Flutter customer
                  |
          /api/v1 REST + Bearer tokens
                  |
Laravel route groups and middleware
                  |
FormRequests -> API controllers -> repositories/services/helpers
                  |
Eloquent models -> migrations/MySQL
                  |
Payment services, observers, queue jobs, scheduler, notifications
```

`backend/app/Providers/RouteServiceProvider.php:37–43` mounts API routes at `/api`; `backend/routes/api.php:19` adds `v1`. Public `rest`, authentication and installation routes precede authenticated dashboard groups. User, master, deliveryman, seller, and admin groups have distinct roles/permissions. `backend/routes/web.php:16–20` contains payment browser redirects, an MTN page, and a welcome view—not an existing Inertia marketplace.

### Client/application map

- **Storefront:** service discovery/booking, product browsing/cart/checkout, appointment/order history, memberships/gift cards, profile/wallet, content and vendor onboarding. Evidence: `web/services/{booking,product,cart,order,shop,user}.ts`, actual App Router page groups, and checkout components.
- **Admin/vendor application:** a shared SPA uses role-dependent route/menu selection. Evidence: `admin/src/app.jsx:33–78,120–130`, `admin/src/layout/app-layout.jsx:24–98`, protected/superadmin route components. Frontend menus are UX, not security.
- **Customer mobile:** calls the same REST APIs through repository classes. Its master-related models primarily support choosing service providers; they do not establish a vendor/master administrative mobile app.
- **Backend:** most HTML views are welcome/payment/export/email-related; business behavior is mainly API/controller/service driven.
- **Operational guidance:** root README is minimal; `DEPLOYMENT.md` explains deployment, storage, queues, image URLs, and storefront UI-type overrides. Operational documentation is not runtime verification.

## 3. DATABASE / DOMAIN MODEL

### Important relationships

```text
User --owns--> Shop
User --accepted Invitation + ShopRole + branch assignments--> Shop staff
User --global master role--> ServiceMaster(service, shop, price, interval)
Country --locations--> ShopLocation(type: service/product) --belongs to--> Shop
Shop --services--> Service --assignments--> ServiceMaster --bookings--> Booking
Shop --products--> Product --variants/inventory--> Stock --items--> OrderDetail
Cart --UserCarts--> CartDetails(shop) --CartDetailProducts(stock, quantity)
Cart checkout --> one Order per shop, first Order links child Orders
Booking/Order/etc --polymorphic payable--> Transaction
Transaction --fee/payable/adjustment records--> PlatformFeeLedgerEntry
User --Wallet--> WalletHistory; partner settlement and Payout are separate records
Country --currency--> Currency; Country --allowed payments--> Payment
CountryAdmin / CountryInvitation + CountryRole --> country-scoped administration
```

### Domain table map

| Domain | Main schema and relationships | Preserve / concern |
| --- | --- | --- |
| Identity/access | `users`, `personal_access_tokens`, `sessions`, `social_providers`; Spatie role/permission schema; `invitations`, `shop_roles`, `shop_permissions`, `shop_role_permissions`, `invitation_shop_locations` | Preserve user IDs, role membership, accepted invitations, credentials and branch assignments. A staff invitation is currently also a business-membership record. |
| Country administration | `country_admins`, `country_invitations`, `country_roles`, `country_permissions`, `country_role_permissions` | Direct admin unique on user: one direct country per user; country invitation unique on user+country. Not the same as shop membership. |
| Marketplace/geography | `shops`, `shop_translations`, `shop_locations`, working/closed days, socials/galleries/tags; `regions`, `countries`, `cities`, `areas` and translations | `shop_locations`, not a new `branches` table, is the existing branch/location concept. Product/service locations may be separate rows for one physical site. |
| Service catalog/specialists | `services`, translations/FAQs/extras; `service_masters`, prices/notifications/form options; user working days, master closed dates/disabled times | `service_masters` is an assignment entity, not an independent authentication account. |
| Appointments | `bookings`, `booking_extras`, `booking_coupons`, `booking_extra_times`, `booking_activities` | Parent-child groups; shop/service/category/master/user/currency/location references; frozen money/rate and collection fields. |
| Products/inventory | `products`, `product_translations`, `stocks`, `stock_extras`, extra groups/values, product properties, property groups/values, brands, units, discounts, `whole_sale_prices`, bonuses | Product is catalog identity; stock is the variant/SKU used in cart/order lines. Images include primary images and galleries. |
| Carts/orders | `carts`, `user_carts`, `cart_details`, `cart_detail_products`, `orders`, `order_details`, `order_coupons`, `order_statuses`, status notes, `order_refunds` | Preserve group-cart semantics, parent/child orders, snapshots, quantities, replacement-stock references and customer history. |
| Fulfilment | Delivery prices/points/working/closed days, warehouses, deliveryman/shop-deliveryman settings; parcel orders/options/settings | Physical delivery, pickup (`point`), digital orders, and parcel delivery are distinct. |
| Payments/accounting | `payments`, `payment_payloads`, `shop_payments`, `country_payments`, `platform_payment_configs`, `payment_process`, `transactions`, `platform_fee_ledger_entries`, `payment_to_partners`, `payouts`, `wallets`, `wallet_histories` | Collection configuration, payment attempts, accounting liabilities, internal wallets and payout requests are different concepts. |
| Additional customer value | Membership/service tables, user memberships, gift carts/user gift carts, digital files/user digital files, referrals, points/histories | These are dependent on booking/ordering/payment workflows; do not discard them as unrelated legacy code. |
| Content/operations | Reviews, likes, banners/blogs/pages/FAQs/stories, tickets, notifications, email/SMS settings/templates/payloads, jobs/failed jobs, settings/translations/languages, ads/subscriptions, auctions, logs/backups | Some modules have routed/client implementations; each ancillary module still needs acceptance coverage before replacement. |

### Primary schema evidence

- Shop/product/service schema: `database/migrations/2022_02_22_192557_create_shops_table.php`; `2022_05_27_164617_create_products_table.php:17–63`; `2023_10_17_102051_create_services_table.php:15–37`.
- Variant stock: `2022_11_16_150240_create_stocks_table.php:16–50`.
- Specialist assignment: `2023_10_17_120318_create_service_masters_table.php:15–31`.
- Booking: `2023_11_03_093553_create_bookings_table.php:15–37`, followed by shop/coupon/total/location/collection additions.
- Reconstructed order schema: `2023_12_07_064250_remigrate_orders_table.php:42–94`.
- Recent country/shop permission/configuration work: migrations beginning `2026_09_05_*`, `2026_09_06_*`, `2026_09_07_*`, `2026_09_08_*`, and ledger/booking work through `2026_09_27_*`.
- Model relations: `app/Models/Booking.php:234–284`, `ServiceMaster.php:108–128`, `Service.php:146–166`, `Shop.php:376–386`.

### Schema and migration cautions

1. Migrations represent **intended schema**, not confirmed production state. Never infer the deployed schema by replaying all files.
2. **The forward `up()` migration `2023_12_07_064250_remigrate_orders_table.php:29–40` drops orders, order details, coupons, refunds, partner payments, tickets and other tables before recreating them.** This is not merely a rollback method. Determine whether it has already run; never apply it to an unverified populated database.
3. Other forward migrations drop/replace columns. For example, invitation single-location conversion has an explicit backfill before the old column is removed: `2026_09_08_010000_create_invitation_shop_locations_table.php:10–40`. Preserve and validate the backfill rather than recreating membership data.
4. Existing price/tax/fee fields use `double`/`float` in multiple schemas. A future decimal/minor-unit conversion needs currency-aware rules and reconciliation, not a mass type swap.
5. Cascading deletes and nullable foreign keys affect historical records. Business deactivation must not silently cascade financial/customer history away.
6. Do not rename `gift_cart`, `member_ship`, `master`, `on_a_way` or other legacy fields/tags externally while clients still depend on them.

## 4. ADMIN FEATURES

**S:** Administrative APIs and SPA screens cover:

- Dashboards/statistics and financial/sales/appointment/working-hours reports.
- Shops/vendor approval/status, locations, services, masters, scheduling, bookings, catalog/products/stock, discounts/coupons and orders/refunds.
- Geography, currencies, languages/translations, settings, content, media, delivery/warehouse configuration and customer/support management.
- Payments/payment payloads, country allowed payments, country platform gateway configuration, fee-ledger reporting, partner payments, wallets and payout workflows.
- Country-admin assignments, country roles/permissions and country invitations.
- Subscriptions, ad packages, memberships/gift cards and other ancillary marketplace modules.

Evidence: `backend/routes/api.php:921–1060,1090–1194,1488–1600`; report chain at `:934–947` -> `Dashboard/Admin/ReportController` -> `DashboardRepository/ReportRepository`; admin SPA role routing/layout cited above. Country/shop feature controllers and services are separately present and traced in Sections 8/11.

**Important qualification:** not every admin route carries the same fine-grained country permission middleware. Some isolation is provided by country model scopes and controller/repository predicates. Route-group presence is not proof that every report/export/show/update/delete is country-safe.

## 5. VENDOR FEATURES

**S:** The seller group admits `seller|moderator|admin|shop_manager` and uses shop checks and many fine-grained permission gates (`backend/routes/api.php:536,635–711,743–781,855–870`).

Traced vendor capabilities include shop/branch management; service/master/schedule/booking management; product/stock/discount/category management; customer/staff invitation/account creation; order/POS/refund/fulfilment; payment-method configuration; subscriptions/advertising; and reporting/partner-payment/payout requests.

Positive tenant-enforcement examples:

- Seller user list/create/update inject current `shop_id`: `Dashboard/Seller/UserController.php:64–81,105–118,141`.
- Location show/update reject another shop: `Seller/ShopLocationController.php:46,66–92`.
- Shop payment configuration is forced to the current shop: `Seller/ShopPaymentController.php:32–48`.
- Seller base sets `$user->shop ?? $user->moderatorShop` after shop middleware: `Seller/SellerBaseController.php:17–28`.

**G/U:** That base resolution is single-shop-oriented. It does not establish a complete active-business selector for users belonging to several shops. See invitation/role-retention risks in Section 13.

## 6. CUSTOMER FEATURES AND MARKETPLACE

### Discovery and storefront

| Capability | Traced implementation | Status |
| --- | --- | --- |
| Shops/businesses and branch/location display | Public shops/search/paginate/show/slug/categories/galleries/reviews -> Rest ShopController -> ShopRepository and location filters -> Shop/ShopLocation resources -> web shop clients/Flutter ShopsRepository | S, not R |
| Countries/cities/categories | Public geography and category hierarchy/search/filter routes; country/category client services; Flutter settings/shop filters | S |
| Geographic distance/filtering | Shared `backend/app/Traits/ByLocation.php:99–126`; location matching distinguishes service/product location; Flutter displays `shop_name.dart:165–168` distance | S; actual query accuracy/index/performance U |
| Services/specialists/availability | Public services, service-masters, masters/times/times-all/closed/disabled dates -> repositories -> booking clients | S |
| Products/catalog/variants | Public product search/paginate/discount/compare/related/by-shop/by-category; stock/extras resource parsing -> web and Flutter catalog UI | S |
| Ratings/reviews/favorites | Review routes and booking/shop/product reviews; authenticated dashboard `likes` and `like/store-many`; web like store and Flutter shop state | S; favorites are implemented as likes, not a separate favorites domain |
| Customer account/history | Profile/auth/address/wallet/notifications; appointments; product orders/parent details/refunds; memberships/gifts/digital-file access | S |
| Support/content | Tickets, blogs/pages/FAQs/terms/policy/stories and other routed content | S surface; full acceptance U |

Evidence: `backend/routes/api.php:98–157,191–261,298–305,313–435`; `web/services/{product,shop,booking,order,notification}.ts`; `customer_app/lib/infrastructure/repository/{shops,booking,order,settings,user}_repository.dart`.

### Branding/UI that must survive

The storefront reads logo/favicon/title/theme colors from settings; its default primary is `#BB9B6A`, and layout sets RTL/locale/theme (`web/app/layout.tsx:59–82,85–173`; `web/config/global.ts:1–5`). Existing home variants, service/category icons, images, fonts, layouts and navigation workflows form the baseline. Approximate original asset-directory sizes are 11 MB web public, 7.5 MB admin public, 3.7 MB admin source assets and 11 MB mobile assets; these are inventory observations, not proof uploaded customer media is included.

Preserve originals with an asset manifest and visual acceptance captures once a runtime exists. Do not substitute a generic generated marketplace theme.

## 7. SERVICE BOOKING FEATURES

### End-to-end chain

```text
Public service/master listing and time availability
 -> /rest/bookings/calculate
 -> BookingRepository::calculate
 -> authenticated User BookingController::store
 -> BookingService::create transaction
 -> beforeSave + branch resolution + authoritative snapshots
 -> Booking + extras/coupon/membership/gift usage + Transaction
 -> payment callback/internal payment + TransactionObserver
 -> status changes, cancellation, reviews, notifications and reports
```

Evidence: `backend/routes/api.php:237–256,426–435`; `Dashboard/User/BookingController.php:73–107`; `BookingRepository/BookingRepository.php:161–384`; `BookingService/BookingService.php:55–210,486–625`; `web/services/booking.ts:16–44`; Flutter `booking_repository.dart:77–179`.

### Implemented rules and behavior

- **Specialist assignment:** `service_masters` links service, master user and shop, with price/commission/interval/pause/active fields.
- **Availability:** user working days, master closed dates, disabled/repeating intervals and existing bookings feed date-keyed availability; MasterRepository contains daily/weekly/monthly/custom repeat handling. Quote derives end time from interval+pause rather than trusting a client end time.
- **Branch assignments:** accepted invitations carry many location assignments. Booking write validates service location belongs to shop, has the correct location type, and is assigned to the specialist where restricted.
- **Omitted branch:** legacy no-location/single-location cases remain supported; one unambiguous assigned branch can be resolved. Ambiguous multi-branch cases are rejected, not guessed.
- **Money:** customer-facing quote selects the shop checkout country's currency/rate; creation freezes service/master/shop/category, price, commission, service fee, location and `collect_via_platform`.
- **Extras/coupons/memberships/gifts:** quote includes extras and reductions; creation persists extras and coupon snapshots; membership sessions and gift balances have update paths.
- **Statuses:** `new`, `canceled`, `booked`, `progress`, `ended` (`app/Models/Booking.php:109–120`).
- **Grouped bookings:** first booking links children; create returns a list; parent expansion/cancellation/review/notes/export routes are present.
- **Cancellation:** customer cancellation is restricted to cancellation semantics in its status path; service applies window/commission/wallet/refund rules and inserts negative payable adjustments. This is not proof every gateway executes a real refund.
- **Rescheduling:** customer generic `PUT bookings/{id}` validates start/end/master-related changes; master has `POST bookings/{id}/times/update`. A dedicated, fully tested customer reschedule policy/UI was not established.
- **Reviews:** booking review delegates to assigned master; web/Flutter review callers are present.

Branch evidence: `BookingService.php:549–625`; `BookingBranchValidationTest.php:88–240`; source/client branch omission is also visible in Flutter creation payload and documented in the test.

### Source defects and important gaps

1. Quote disabled-time check uses interval **containment**, not general overlap (`BookingRepository.php:355–376`), and inspects the start-day disabled intervals even when end-day data was fetched. Partial or cross-day conflicts can be missed.
2. Creation is wrapped in a database transaction, but no atomic slot reservation/row-lock/exclusion constraint was found in the traced save path. Concurrent successful quotes can compete.
3. Master `timesUpdate` updates dates after a past-date check without the full quote availability recheck (`BookingService.php:769–807`). Cascading shifts use containment conflict tests (`:895–950`).
4. Generic customer update checks ownership (`User/BookingController.php:154–163`) but permits date/master changes through `Booking/UpdateRequest.php:20–56`. Consolidate it with the same availability/branch/price rules as creation rather than invent a separate reschedule backend.
5. Flutter/storefront branch selection is incomplete for ambiguous branches; retain safe backend rejection and add client branch choice before changing that behavior.
6. Appointments use bare datetime columns and PHP/server date parsing. The resource appends `Z` to created/updated timestamps (`BookingResource.php:75–76`); appending a suffix is not proof the underlying timezone is UTC. A country/branch IANA timezone contract is not established.
7. Exact-wallet-balance payment is rejected in booking creation because the check is `wallet.price <= totalPrice` (`BookingService.php:174–180`), not `<`.
8. Booking-specific tax support is not established by the traced quote/schema; order tax functionality must not be advertised as appointment tax.

## 8. PRODUCT / ORDERING FEATURES

**Product ordering is real source functionality and is a mandatory preservation requirement.**

### Catalog and stock

- Products belong to shop/category/brand/unit, with images, active/status/visibility, min/max quantities, digital flags and translations.
- Stocks are variant/SKU inventory identities with price, quantity, image, discounts and stock extras. Product properties/extras/wholesale ranges/bonuses are supported in the pricing/resource layer.
- Seller/admin product/category/discount/stock management surfaces exist, with customer browsing in Next and Flutter.
- Quote and checkout recalculate economics; do not trust UI-calculated totals.

Evidence: public routes `api.php:98–124`; `Rest/ProductController` -> `ProductRepository`; stock/product migrations cited in Section 3; `OrderHelper.php:97–175`; `OrderDetailService.php:63–75,183–205`; web `product.ts:8–27`; Flutter `product_model.dart:538–556,647–720`.

### Cart, checkout and orders

```text
Catalog -> absolute stock quantity in cart
 -> Cart/UserCart/CartDetail(shop)/CartDetailProduct(stock)
 -> cart calculate with current stock/discount/wholesale/coupon
 -> authenticated order checkout OR external gateway initiation
 -> OrderService transaction -> CartOrderService or POSOrderService
 -> one order per shop; parent/child grouping
 -> OrderDetails + stock decrement + prices/tax/fees/delivery/tips snapshots
 -> transaction/payment -> fulfilment/status/history/refund/digital access
```

- **Carts:** authenticated, anonymous/shared group-cart routes are distinct. Insertion uses normalized absolute quantity with `updateOrCreate(stock_id)`, **not an additive delta**. Do not label overwrite semantics a bug without showing a delta-sending caller.
- **Group carts:** retain owner/member UUID, open/set-group/member status and product/member deletion semantics. Public group operations need explicit possession/ownership acceptance tests.
- **Multi-shop checkout:** CartOrderService produces per-shop orders and links later orders to the first; mixed shop currencies/fees must not be flattened.
- **POS:** seller/admin flows can submit `data[]` shop/product/stock batches instead of a cart.
- **Totals:** discounts, item tax, coupons, shop percentage commission, service fee, delivery fee and tips are recalculated.
- **Delivery/pickup/digital:** wire values are `delivery`, `point`, `digital`. Delivery needs delivery-price/address data; pickup uses delivery point. All-digital paid flows can mark delivered and issue digital entitlements.
- **History/statuses:** customer paginate/active/completed/get-all, review, cancellation, invoice and refunds are wired; vendor orders are not appointment records.
- **Statuses:** `new`, `accepted`, `ready`, `on_a_way`, `pause`, `delivered`, `canceled` (`Order.php:133–148`). Preserve the spelling `on_a_way`.
- **Refunds:** physical stock restoration is in OrderRefundService; cancellation delegates to refund when no refund already exists. It is not simply `status=canceled -> increment all stock`.

Evidence: `OrderService.php:101–155,225–343`; `CartOrderService.php:37–55,95–209`; `POSOrderService.php:36–85`; `OrderDetailService.php:39–133`; `OrderRefundService.php:83–109,321–332`; `OrderStatusUpdateService.php:50–95`; `Order/StoreRequest.php:18–76`; mobile `create_order_model.dart:35–76`; `web/services/order.ts:13–66`.

### Product-order risks and source defects

1. **Stock race:** `OrderHelper.php:177–205` uses a previously loaded stock quantity then model `update()`. There is no row lock or conditional atomic decrement. The outer transaction helps rollback but does not prevent concurrent lost updates/overselling.
2. Quantities are clamped to current stock/min/max (`OrderHelper.php:97–116`); this is not a reservation. A future migration must preserve whether clients expect clamping or explicit validation errors.
3. Order wallet equality check rejects a wallet that exactly covers total (`OrderService.php:334–340`). The thrown error is inside the order transaction, so expected rollback is static control-flow evidence, not a runtime-tested guarantee.
4. Status service lacks a complete transition matrix; same-status rejection is not sufficient role-aware state-machine enforcement (`OrderStatusUpdateService.php:50–95`).
5. Coupon decrement/check and wallet balance movements need concurrency/idempotency protections.
6. Order update exposes exception message/file/line in a returned message (`OrderService.php:207–213`).
7. `CartServiceTest.php:15–21` only exercises an authenticated profile request. Its name does **not** establish cart/checkout regression coverage.
8. Digital-file access, partial replacements, refunds, split-wallet/external payment, parent-child totals and mixed-shop checkout require dedicated fixtures before extracting services.

## 9. VENDOR / STAFF / MASTER MODEL

### Current meanings—not target renaming

| Existing concept | Actual meaning |
| --- | --- |
| Seller/vendor owner | User with direct `shop()` owner relation; owns the shop; owner bypasses shop-specific permission checks. |
| `moderator` | Legacy global shop-related role, associated with a shop through invitation rather than the direct owner relationship. |
| `shop_manager` | Global route-entry role for custom shop-role staff. The fine-grained authority comes from accepted invitation + shop role, not the role name alone. |
| `master` | Global specialist role; service assignments (`service_masters`) connect that user to bookable services, schedules and bookings. It does not mean business owner. |
| Shop staff | Existing users/new accounts associated with a shop through invitation, shop role/permissions and location assignments. Not every staff member must be a specialist. |
| Country `manager`/admin | Different administration domain. A global manager/admin without country restriction can be superadmin; restricted direct assignment/accepted country invitation narrows scope. |
| Branch manager | Can be represented by shop permissions + assigned locations; no independently established canonical BranchManager entity. |
| Deliveryman/waiter-related UI | Additional operational roles/surfaces; not to be conflated with specialists. |

Evidence: `User.php:227–310,332–398`; `SellerBaseController.php:17–28`; route role groups at `api.php:447,503,536,921`; `ServiceMaster` relations.

### Invitation/account workflow already exists

1. Existing-user seller invite rejects seller/admin targets; a supplied shop role assigns route role `shop_manager`.
2. Invitation stores shop/user/creator/role/status/custom shop role; supplied location IDs are synced into `invitation_shop_locations`.
3. Status acceptance adds the invitation's global role; acceptance and edit predicates are in InviteService, not merely the frontend.
4. Seller-created new staff accounts can be created **already accepted** with their branch assignments.
5. Accepted invitation edits update permissions/branch assignments live; clearing the custom role does not automatically revoke the global route-entry role.
6. Invite rejection/cancellation can deactivate the user's service-master rows; this cross-domain side effect needs preservation/review.

Evidence: `InviteService.php:26–53,80–110,135–187,236–283`; `UserServices/UserService.php:34–75`; seller controller `:64–81,105–118`.

### Organization gaps

- `moderatorShop()` is a single `hasOneThrough` relation without accepted-status filtering in the relation itself (`User.php:244–248`). Fine-grained permission/branch methods explicitly query accepted invitations, but base shop selection can disagree with them.
- Membership selection, route-entry roles and fine-grained permissions need one consistent active-business context.
- Staff-role clearing and country unassignment must not accidentally create unrestricted administrators or retain broader route authority.
- Do not migrate all staff into a specialist table. Preserve receptionist/accountant/manager access independently from specialist service assignments.
- Invitation-created accounts bypass a later acceptance step; security hardening should retain the workflow while adding safe activation/password delivery rather than silently disabling staff creation.

## 10. PAYMENT PROVIDERS AND ACTUAL IMPLEMENTATION STATUS

### Status definitions

**Implemented but unverified** means meaningful initiation and handling code exists, not production-ready or tested against a provider. **Partial** means a demonstrable missing/incorrect dependency, callback, amount, routing or settlement step prevents treating it as complete. **Absent/not established** means no concrete implementation for that rail was found in the audited tree. **Verified** is not assigned to any provider.

### Collection model and enforcement

- **Vendor-direct credentials:** `Payment::SHOP_CREDENTIAL_TAGS` is Orange/MTN; shop configuration is explicit. This is **not** a per-vendor configuration implementation for every listed provider.
- **Platform credentials:** other providers use platform/global PaymentPayload; country PlatformPaymentConfig also handles relevant country/config/currency resolution.
- **Vendor opt-in:** `shops.collect_via_platform` can route Orange/MTN booking/cart initiation through country platform config rather than ShopPayment (`BaseService.php:841–869`).
- **Platform-fee purchases:** subscription/ad-package fees resolve platform-country configuration independently (`BaseService.php:872–883`). Buying a subscription is not the same as collecting a vendor's customer revenue.
- **Country allowlist and mixed-shop limits:** Rest PaymentController filters country active payments; Orange/MTN configuration is required and multi-shop carts are excluded; zero-decimal Stripe cases are explicitly blocked.
- **Manual non-cash confirmation:** TransactionController permits cash handling separately and requires admin+reason for non-cash override. Validation restricts status to `paid,canceled` (`TransactionUpdateRequest.php:16–29`); it is not arbitrary status input.
- **Missing target controls:** the audited source does not establish platform policy modes “vendor choice / force direct / force platform” applied consistently to both business lines.

### Provider matrix

| Provider/mode | Implementation and credentials—names only | Checkout/callback evidence | Classification and payout status |
| --- | --- | --- | --- |
| Stripe | StripeService; global payload `stripe_pk`, `stripe_sk`; optional `wallet_payment_method_configuration` | Hosted Checkout, `/dashboard/user/stripe-process`; callback `/webhook/stripe/payment` queries Stripe session status using payment-intent input; browser return `/payment-success` | **Implemented but unverified, security gaps.** No Stripe-Signature validation in callback. Explicit zero-decimal rejection is a limitation, not a fake successful African-currency implementation. No vendor payout rail established. |
| PayPal | PayPalService; `paypal_mode`, sandbox/live client ID/secret/app ID; action/currency/locale/SSL fields in admin form | Orders v2 creation and server capture on approved event; completed/denied callback branches | **Implemented but unverified, defects.** No webhook signature/amount comparison in traced callback. Whole-unit ceiling can overcharge. Global credentials; country config may resolve settlement currency. No PayPal vendor payout rail established. |
| Flutterwave | FlutterWaveService; actual secret key is `flw_sk` | `/flutter-wave-process`, `/v3/payments`, posted successful/status and tx_ref callback | **Implemented but unverified, unsafe callback.** Do not misreport the key as `flutterwave_sk`. No outgoing payout implementation established. |
| Paystack | PayStackService; `paystack_sk`, client/admin `paystack_pk` | Initialize transaction with amount/currency/reference; callback trusts posted event/reference | **Implemented but unverified, unsafe callback.** No HMAC/provider verification/amount check in traced controller; no payout rail established. |
| MTN MoMo | MtnService, MtnController; `subscription_key`, `api_user`, `api_key`, `target_environment`, currency/base URL | UUID request-to-pay, token acquisition, phone payer; public callback; authenticated reference polling and five-minute reconciliation | **Implemented but unverified.** Independent provider polling is positive; callback trusts status/externalId. Supports shop or platform collection in specified modes; collection is not MTN disbursement. |
| Orange Money | OrangeService/Controller; config `client_id`, `merchant_key` mapped to client secret, currency/base URL | OAuth plus QR/deep-link initiation; callback trusts posted id/status/reference | **Partial, unverified.** TLS verification disabled in two calls; hardcoded request code; status/amount authenticity not established. No Orange disbursement rail. |
| Cash/offline | Internal Payment/Transaction flow | Cash manual confirmation and fulfilment payment paths | **Implemented internal mode, unverified.** Do not call it an authenticated external gateway. |
| Wallet | Wallet/history, split-wallet withdrawals/topups/transfers | Internal payments and external topup afterHook | **Implemented internal mode, unverified.** Exact-balance defect and race/idempotency concerns. Not a bank account or money-transfer provider certification. |
| Bank transfer | No dedicated bank-transfer service/controller/verification rail established | Bank details/config/UI labels alone would not prove settlement | **Absent/not established as a bank-payment implementation.** Manual payout/request records are not bank execution. |
| Razorpay | RazorPayService/Controller; `razorpay_key`, `razorpay_secret` | Payment-link initiation; posted link entity id/status callback | **Implemented but unverified; callback authenticity gap.** No provider payout rail established. |
| PayTabs | PayTabsService/Controller and package; `server_key`, `profile_id` | Initiation plus posted status/id callback | **Implemented but unverified; callback authenticity gap.** Needs payload/provider/runtime reconciliation before enabling. |
| PayU | PayuService/Controller; `client_id`, `client_secret`, `merchant_id`, `sandbox` | Initiation/callback parser; signature-related input is not proof it is validated | **Implemented but unverified; callback validation gap.** |
| Mollie | MollieService/Controller; `secret_key` | Provider initiation; callback accepts posted status and model IDs | **Implemented but unverified; provider-status/ownership verification gap.** |
| Moyasar | MoyasarService/Controller; `secret_key`, `secret_token` | Initiation; explicit shared-secret comparison in callback | **Implemented but unverified.** Shared-secret check is positive; amount/currency/event idempotency still need verification. |
| Mercado Pago | MercadoPagoService/Controller; payload `token`, sandbox | Reachable initiation forces unit price 1; callback provider-resource lookup has process-reference mismatch | **Partial/broken source path.** Not production-ready; callback also lacks signature/amount checks and accepts caller resource URL. |
| PayFast | PayFastService/Controller; `merchant_id`, `merchant_key`, `pass_phrase`, sandbox | Outbound signature and web/mobile special handling exist; emitted `/webhook/pay-fast/payment` is not registered in inspected api/web routes | **Partial.** Outbound signing does not secure callback; callback route mismatch needs repair/verification. |
| Iyzico | IyzicoService/Controller; `api_key`, `secret_key`, sub-merchant/sandbox fields | SDK-dependent initiation; webhook route points at `processTransaction` rather than callback handler | **Partial.** Iyzipay SDK is not a Composer dependency; route/middleware mismatch also needs repair. |
| Maksekeskus | MaksekeskusService/Controller; `shop_id`, `key_publishable`, `key_secret`, `demo`, `country` | SDK initiation and posted JSON callback; source imports `Maksekeskus\\Maksekeskus`, absent from Composer dependency/lock tree | **Partial unless SDK supplied outside audited dependency tree.** Flutter has a specialized Maksekeskus response/UI path; it is not only generic WebView parsing. |
| ZainCash | ZainCashService/Controller; `msisdn`, `key`, `merchantId`, `url`; uses installed Firebase JWT library plus custom HTTP | Source initiation and callback code exist; no registered Zain callback found in inspected `backend/routes/api.php` | **Partial.** Route/provider authenticity/runtime require verification; do not claim a missing ZainCash SDK. |

Primary evidence: `PaymentService/{Base,Stripe,PayPal,FlutterWave,PayStack,Mtn,Orange,MercadoPago,PayFast,Iyzico,Maksekeskus,ZainCash}Service.php`; matching `Dashboard/Payment/*Controller.php`; `api.php:399–415,1606–1621`; `web.php:16–17`; admin `payment-payloads/payment-form.jsx`. Exact key requirements for MTN/Orange are in `ShopPayment` and `PlatformPaymentConfig` Store/Update requests. No key values are included.

Exact rounding/dependency/route evidence: `backend/app/Services/PaymentService/PayPalService.php:53` rounds `ceil(settlementAmount/100)` (e.g. 1,999 minor units becomes 20 whole major units); `BaseService.php:533–537` rounds the booking major-unit total before multiplying by 100. These are specific paths, not a claim all providers always round this way. `PayFastService.php:40` emits an unregistered notify URL. `backend/routes/api.php:1612` points Iyzico callback to `processTransaction` while `IyzicoController.php:25–35,43–56` defines distinct initiation/callback methods. `composer.json`/`composer.lock` have no Iyzipay or Maksekeskus package; source references them (`IyzicoService.php:11–23`; `MaksekeskusService.php:11,42`). ZainCash uses `Firebase\\JWT\\JWT`, which is present in Composer, and HTTP initiation (`ZainCashService.php:47–75`).

### Callback/security details

- Stripe callback (`StripeController.php:116–140`) does a real provider lookup; it does **not** simply trust `paid` from the request. It nevertheless has no signature validation and logs full request data.
- PayPal approved events invoke server capture; completed/denied branches and resource linkage still need authenticated webhook/amount validation (`PayPalController.php:32–66`, `PayPalService.php:110–155`).
- Flutterwave (`FlutterWaveController.php:21–34`), Paystack (`PayStackController.php:19–32`), MTN (`MtnController.php:31–50`) and Orange (`OrangeController.php:19–22`) directly extract posted status/reference then mutate through afterHook. This is a specific source finding, not merely an inability to locate proof.
- Moyasar has a shared-secret check (`MoyasarController.php:24–30`). Preserve it while implementing provider-appropriate verification.
- Public callbacks are declared `Route::any`, and PaymentBaseController excludes `paymentWebHook` from Sanctum (`PaymentBaseController.php:19–23`). Public reachability is appropriate for providers; authenticity must come from provider verification, not a customer Bearer token.
- `BaseService::afterHook` primarily prevents repeating the identical stored status (`:62–92`). It is not an immutable provider-event inbox with globally enforced transition/idempotency rules.
- Server status lookup alone does not prove exact amount, currency, merchant account, order identity and final settlement state all match.

### Actual client integration—not provider-name inference

The web cart routes **every non-cash/non-wallet method** through a generic external-payment hook (`authorized-cart.tsx:266–274`). The shorter `externalPayments` constant is not proof other provider tags are unreachable. The hook expects `res.data.data.url`, and PayFast has a special script flow (`use-external-payment.ts:53–84`).

Flutter dynamically calls `/dashboard/user/$name-process` and normally expects HTTP-body `data.data.url` (`payments_repository.dart:63–99`). PayFast uses a special package path; Maksekeskus has a dedicated parser/UI path (`:175–203`). MTN request-to-pay reference/status is not automatically a hosted checkout URL, and no mobile MTN polling UI was found in the searched repository paths. **Orange does place its provider `deepLink` into `data.url`** (`OrangeService.php:77–87`), so generic URL extraction can succeed; native deep-link/WebView behavior and final callback settlement remain unverified. Do not classify Orange as absent or incompatible merely for lacking a dedicated UI.

**International card collection and African-vendor payout are separate requirements.** Current collection code, PayPal currency conversion and MTN/Orange configuration do not prove an implemented US-card-to-Cameroon-mobile-money payout flow.

## 11. COMMISSION / EARNINGS MODEL

### Current calculation

| Amount | Actual source rule | Meaning |
| --- | --- | --- |
| Platform service fee | `Utility::resolveServiceFee(key, base, rate)` uses shared `service_fee_type`; percentage is `max(base/100 * settingFee,0)`; fixed is `max(settingFee,0)*rate` | Booking key `booking_service_fee`, order key `service_fee`; fixed order fee can be split across shop orders |
| Order shop commission | Order calculation uses shop `percentage` against its subtotal | Percentage commission exists; do not conflate with service fee |
| Booking commission | Booking beforeSave snapshots `max(ServiceMaster.commission,0)` as commission fee | A specialist-assignment monetary commission field; not a proven hierarchical platform/country/category rule engine |
| Booking seller net | `total_price - service_fee - commission_fee - coupon_price` | Existing formula to preserve/reconcile; determine whether coupon is intentionally deducted again from the selected total before “correcting” economics |
| Order seller net | `total_price - in-house delivery_fee - service_fee - commission_fee - coupon_price - tips` | Model returns zero for customer/rest request paths; business economics currently depend on request context |

Evidence: `Utility.php:62–96`; `OrderService.php:250–312`; `BookingService.php:507–523`; `Booking.php:210–226`; `Order.php:238–249`.

Money/rate/fee fields are persisted on orders/bookings, which is a useful historical baseline. Do not recompute old transactions using today's settings.

### Current ledgers, earnings and payouts

- `TransactionObserver` records platform **service-fee** ledger rows for paid bookings/orders, including paid-at-creation transactions. It uses `firstOrCreate(transaction_id,entry_type)` and stored payable currency/fee.
- On updated-to-paid bookings with frozen `collect_via_platform=true`, it records seller **payable** rows from stored booking seller net.
- Booking cancellation inserts negative payable adjustments, not destructive edits to historical liability.
- Partner payment services create wallet-history topup/withdraw movements based on seller earnings.
- Payout services/controllers expose request/status/internal wallet movement workflows. These do **not** prove a bank transfer or MTN/Orange vendor disbursement executed.
- “Pending ledger row,” “paid provider transaction,” “settled fee,” “vendor available balance” and “completed payout” must remain distinguishable.

Evidence: `TransactionObserver.php:33–150`; `BookingService.php:640–674`; `PaymentToPartnerService.php:98–126,163–240`; `PayoutService.php:94–186`; ledger migrations `2026_09_20_120000_*`, `2026_09_26_030000_*`.

### Concrete accounting gaps

1. **Frozen/live collection mismatch:** booking creation freezes the mode, and the observer reads that snapshot; **gateway resolution reads the current Shop flag** (`BaseService.php:841–859`). A toggle change between booking creation and payment initiation can route money to one merchant while recording the opposite liability. Comments saying the same frozen intent is read do not override this code.
2. **Orders lack symmetric seller payables:** observer's payable method is Booking-only; order fee entries exist but not a matching order platform-collection payable/snapshot/reversal lifecycle (`TransactionObserver.php:54–55,126`).
3. **Paid-at-creation booking gap:** `created()` records the fee only; booking payable recording occurs in `updated()`. If a platform-collected booking transaction is created already paid, that branch does not record its payable.
4. **Request-context economics:** order seller fee returns zero under customer/rest paths. Shared domain accounting must not depend on the HTTP route from which a model accessor happens to be evaluated.
5. Payout state and wallets require locks and repeat-status protections; current service checks balance then writes without a complete atomic transition/idempotency boundary.
6. No complete hierarchy of platform/country/vendor/category commission overrides with frozen rule identity was established.
7. No unified external payout connector, reconciliation lifecycle, safeguarding/currency-separated available-vs-pending balances or provider-returned settlement reconciliation was established.

Tests exist for fee entries, order created-paid behavior, booking routing, frozen collection and negative payable adjustments; none were executed. Snapshot and routing tests separately cover their paths, not the cross-path toggle mismatch described above.

## 12. MULTI-COUNTRY MODEL

### What countries actually affect

| Area | Current implementation | Limitation |
| --- | --- | --- |
| Shops/branches | ShopLocation has country; current location service rejects sibling locations in different countries | Existing shop is effectively single-country. Do not assume one vendor can span countries today. |
| Geography/search | Country/region/city/area and coordinates, service/product location filtering/distance | Validate actual geographic consistency and indexes against real anonymized data. |
| Currency | Country `currency_id`, backfills, shop checkout-country currency; order/POS/booking resolution | Preserve original rates/snapshots. Presentation-selected currency is not always checkout settlement currency. |
| Payment methods | Country-payment allowlist; per-shop Orange/MTN; per-country platform config | Not automatic legal/provider eligibility for every currency/country; Stripe zero-decimal restrictions exist. |
| Customers | Addresses/filter requests carry geography; phone verification and selected locale/currency exist | No universal customer-country tenancy boundary should be assumed. |
| Phone | Unique nullable phone; formatting/OTP/service code | No complete country-aware E.164/number-country contract established. |
| Language | Language records/translations, `lang` query, locale/RTL and client preferences | Not proven automatically derived from country. |
| Timezone | Admin/public timezone settings and server date use | Branch/country IANA timezone and explicit UTC/local conversion contract not established. |
| Administration | CountryAdmin + CountryInvitation role/permissions, CountryContext and model scopes | Substantial implementation exists, but not all models/actions are uniformly scoped. |

Evidence: country/currency/payment migrations `2026_09_05_*`; `ShopLocationService.php:100–122`; `BookingRepository.php:163–181`; `POSOrderService.php:47–57`; `api.php:70–90,222–225`.

### Country administration mechanics

- Direct `country_admins.user_id` is unique; one direct country per user (`2026_09_05_100000_create_country_admins_table.php:24–28`).
- Country invitations allow one user-country pair per row; multiple country rows can structurally exist.
- CountryContext chooses direct CountryAdmin first, otherwise `.value('country_id')` of an accepted role-bearing invitation (`Helpers/CountryContext.php:21–36`). This is **one effective country**, not a deterministic multi-country selector.
- Global `admin`/`manager` is superadmin only when not country-restricted (`User.php:352–373`).
- Country-admin grant adds `manager` when necessary and remembers `manager_role_granted`. Removal removes manager only if it was granted by that assignment (`CountryAdminService.php:36–59,82–90`).
- Country invite existing-user and new-account paths are distinct; new country staff accounts can be created already accepted with manager role (`CountryInviteService.php:24–98`).
- `CountryRestrictionScope` calls model-specific `inCountry()` only when CountryContext returns a restriction. Traits/scopes cover several finance/marketplace/support models, not every model.
- Examples include shop-location country filters for transactions, order-shop country for tickets, creator-shop country for payouts, partner-model shop country for settlements (`Models/Scopes/CountryRestrictionScope.php:12–32`; local predicates in Transaction/Ticket/Payout/PaymentToPartner).

### Security implications

CountryManager is not simply a frontend menu. Preserve backend restrictions and add action-level Policies/scoped queries for reads and writes. Explicitly test assignment/removal:

- A pre-existing manager role retained after its last restriction is removed may revert to global superadmin semantics; this must be an intentional, audited action.
- Accepted country invitations can continue to authorize after a direct-admin row is removed.
- Multiple accepted invitations need an explicit authorized country selector or a validated one-country rule; “first row returned” must not decide administrative scope.

## 13. API / MOBILE CONTRACTS

The companion **API/mobile compatibility appendix** is part of this report: `docs/audit/agendaally-api-contracts.md`. It records concrete payload examples, wrappers, IDs, status strings, request validation, client parsing and compatibility gaps. All examples are source-derived and synthetic, not live HTTP captures.

### Essential compatibility rules

1. Keep `/api/v1` and existing public `rest`, authenticated dashboard/user, seller/master/deliveryman/admin and payments paths until a versioned transition is approved.
2. Sanctum Bearer tokens are consumed by Flutter. Preserve `access_token`, `token_type`, user shape and current-token logout semantics.
3. Backend success helpers return HTTP 200 with `timestamp,status,message,data`. Resource-collection endpoints may instead directly return `data,links,meta`. Do not impose one new envelope on all v1 endpoints.
4. Failures use **`statusCode`**, not a universal `code` field. Service-internal `code` is mapped by controller helpers.
5. FormRequest validation emits **HTTP 422**, with `statusCode=ERROR_400` and field `params` (`BaseRequest.php:73–82`). Symbolic code and HTTP status are different.
6. Public product/shop/category paths mix UUID/slug and numeric IDs; order/booking/service-master/cart/payment IDs are usually numeric. `ids_by_parent` is a hyphen-joined string, not a list.
7. Preserve legacy wire names/status spelling and conditional resource omissions.
8. Group-cart opening reads HTTP-body `data.id` in Flutter; external gateway URL uses HTTP-body `data.data.url`. These are different nesting contracts.
9. Existing Flutter booking payloads omit branch ID. Adding safe optional branch selection is additive; making it required for every legacy booking would break the app.
10. Pagination query parameters, nullable nested resources, date strings, localized messages and integer/number coercion need contract fixtures.

### Separate mobile project status

Only `customer_app/` was found. Vendor/specialist/delivery APIs and administrative SPA role screens exist; no separate vendor/master mobile application was identified. A future mobile app should reuse the REST/domain layer but is not required to “restore” a missing project in this audit.

## 14. SECURITY FINDINGS

These findings are a source review, **not a dependency/SAST/production security scan**. No exploit was executed. Severity prioritizes potential impact and reachable code; deployed exposure/data loss remain unverified.

| ID | Severity / evidence class | Evidence and affected scope | Recommended remediation after approval |
| --- | --- | --- | --- |
| SEC-01 | **Critical — B** | Unauthenticated install routes `api.php:60–66`; `InstallController.php:117–129` calls `migrate:fresh --seed --force`; BlockIp explicitly allows `api/v1/install/*` (`BlockIpMiddleware.php:12–25`). All original data potentially affected. | Remove network-accessible installation actions from normal deployments; require offline/admin-authenticated one-time installation gates and irreversible-action controls; regression tests. |
| SEC-02 | **Critical — B** | `InstallController.php:131–142` issues a first-user Bearer token without validating that user's credentials. Public route + install IP exemption. Potential account/admin takeover. | Disable this shortcut; authenticate explicitly; never return an existing user's token from setup. Audit existing token issuance if deployed exposure is confirmed. |
| SEC-03 | **High — B** | Flutterwave/Paystack/MTN/Orange and others trust posted status/reference; public webhook routes and afterHook mutate payments/accounting. | Provider-specific signature/secret verification plus server retrieval where appropriate; compare amount/currency/merchant/payable identity; durable inbox/idempotency; negative tampering tests. |
| SEC-04 | **High — B/risk** | Timestamp-derived six-digit reset token (`LoginController.php:288–297`), global token lookup and login-token issuance (`:307–333`), no token expiry predicate in method. Account access at risk. | Cryptographically random, hashed, user-bound, expiring one-use reset token; strict reset-specific rate limits and no token-as-login shortcut. |
| SEC-05 | **High — B** | Orange OAuth/payment HTTP calls `withoutVerifying()` (`OrangeService.php:61–66,101–107`); provider credentials/payment contents at risk. | Enable TLS validation with trusted certificate roots; fail explicitly; sandbox tests. |
| SEC-06 | **High — B** | Live shop collection flag controls merchant routing, frozen booking flag controls payable (`BaseService.php:841–859`; `BookingService.php:518–523`; `TransactionObserver.php:118–149`). | Freeze an authoritative PaymentAttempt/CollectionIntent for both domains; route and settle from it, including config identity/version. |
| SEC-07 | **High — source race risk** | Stock read/modify/write without locking (`OrderHelper.php:177–205`); quote/booking save/time-change lacks one reservation boundary. Inventory and availability at risk. | Atomic guarded stock mutations; booking resource/day locks/reservations and general interval overlap; concurrent tests. |
| SEC-08 | **High — source race/state risk** | Wallet/payout prechecks and status writes not protected by complete atomic/idempotent transitions (`PayoutService.php:94–186`; wallet payment paths). | Lock payout/wallet, permitted role/state matrix, unique ledger event keys, approved beneficiary and balance-currency validation. |
| SEC-09 | **Medium–High — B/risk** | Install writes generated PHP from user-supplied fields (`InstallController.php:37–53`), and database setup misuses file contents as the output filename (`:67–101`). | Eliminate generated executable config from untrusted inputs; disable setup surface; use validated secure configuration tooling. No injection exploit was attempted. |
| SEC-10 | **Medium — B** | Complete request/provider payload logging: Stripe `:118`, Flutterwave `:23`, Mercado Pago `:25,48`, other callbacks; mobile Dio logs headers/body (`http_service.dart:24–30`). | Redact tokens/PII/payment metadata; disable sensitive production logging; retention and access policy. |
| SEC-11 | **Medium — B** | Booking calculation and order update can return exception file/line/message (`BookingRepository.php:138–148`; `OrderService.php:207–213`). | Stable public error codes/messages with internal correlation IDs; restrict detailed logs. |
| SEC-12 | **Medium — B** | `Seller/ShopAdsPackageController.php:60` uses `!$shopAdsPackage->shop_id !== $this->shop->id`. Negating numeric ID yields bool, then strict comparison to int produces wrong ownership behavior. | Use direct ID ownership policy. This primarily fails valid reads for integer IDs; do not mislabel it proven cross-tenant disclosure. |
| SEC-13 | **High-priority authorization gap — S/U** | Policies/Gates empty; single moderatorShop selection differs from accepted invitation permission queries; role clearing/revocation can retain route roles; country restriction loss changes superadmin semantics. | Central Policies + explicit active business/country context; test read/write/export/financial/config/deactivation across roles and tenants. No blanket isolation failure/safety claim. |
| SEC-14 | **Medium — S/risk** | Web CSRF middleware is commented out (`Http/Kernel.php:62–75`); auth/reset public paths share broad `throttle:5000,1`; token-based clients store tokens; upload/gallery operations require detailed ownership verification. | Session+CSRF for future Inertia web; narrow auth/OTP throttles; preserve REST token auth; harden upload ownership/MIME/path/private downloads. |
| SEC-15 | **High-priority review — S/risk** | Mercado callback GETs caller-supplied resource URL with bearer authorization (`MercadoPagoController.php:23–48`). | Restrict to provider API origin/resource identifier, verify event/signature and amount; test SSRF/credential forwarding safely in isolated fixtures. |
| SEC-16 | **High-priority client design review — S/U** | Flutter PayFast builds a signed URL using app constants `passphrase`, `merchantId`, `merchantKey` (`payments_repository.dart:51–104`). Actual values were not read and may be blank/demo; no live-secret leak is asserted. | Move private signing to the backend; public mobile packages must not contain a configured merchant signing secret. Review artifacts securely, never include credential values in audit output. |

Sanctum configuration sets `expiration=null`, so a server-wide finite token lifetime is not established by configuration; keep current-token logout compatibility while introducing approved token expiry/revocation rules. This differs from browser sessions, whose security must be reviewed separately.

### Positive security evidence to preserve

- Sanctum check returns HTTP 401 (`SanctumCheck.php:26–35`).
- Social login verifies Firebase ID token and matches claimed email before user-token creation (`LoginController.php:252–261`). It is not merely trusting posted email/provider ID.
- Seller location/user/payment configuration and customer booking/address/transaction ownership checks exist.
- Fine-grained shop and country permission middleware exists.
- Shop/platform gateway credentials use encrypted casts and feature assertions; encryption depends on retaining the correct application key and secure key management.
- Country restrictions and fee-ledger uniqueness/negative adjustments are useful foundations.

Positive samples do not establish that every endpoint, implicit binding, export, upload, guest cart, transaction or payout is safely scoped.

## 15. TECHNICAL DEBT

1. **Cross-cutting domain logic:** request-aware model accessors, helpers, repositories, services and observers share money/availability decisions. Domain output can change with request path.
2. **Uneven authorization:** coarse global roles, custom shop/country permissions, constructor middleware, model scopes and manual ownership checks are spread across layers; no registered Policy mappings.
3. **Payment coupling:** one BaseService handles many unrelated payable types, minor-unit conversion, callbacks, wallet changes and configuration resolution. Collection and payout are not consistently separated.
4. **Money representation:** floating-point database fields, blanket `*100` conversion and `ceil()` paths conflict with exact currency/provider economics.
5. **State machines:** booking/order/payment/refund/payout transitions are not one explicit role-aware, idempotent domain model.
6. **Concurrency:** outer transactions are useful but not a replacement for locks, conditional updates, unique event constraints and reservation semantics.
7. **Migration safety:** historical forward destructive remigrations and seed hooks are dangerous on unknown data.
8. **Testing:** numerous useful focused Feature tests exist; the cart test is misleadingly named, concurrency and external-contract coverage remain weak/unestablished.
9. **Separate client stacks:** Next/React 19, React 18/AntD 4 admin and Flutter must each remain buildable. Versions/lockfiles and git/`any` Flutter dependencies complicate repeatable builds.
10. **Operational defaults:** sync queue/file cache/database sessions may be intentional or environment overrides; shared scheduler/worker/file persistence needs explicit production configuration.
11. **Frontend configuration:** invalid/missing `NEXT_PUBLIC_WEBSITE_URL` can break metadata construction (`layout.tsx:59–64`); `NEXT_PUBLIC_UI_TYPE` overrides admin settings and can fall back unexpectedly.
12. **Source dependencies/callback routing:** some named payment SDKs/routes are incomplete. Do not expose them as successful integrations.
13. **Legacy names:** `demand`, `ibeauty`, `gift_cart`, `member_ship` and older domain naming are not justification for breaking external contracts or removing branding.

## 16. FEATURES THAT MUST BE PRESERVED

- Both complete business lines: discovery -> booking -> payment -> fulfilment/review/history **and** product -> stock/variant -> cart -> checkout -> order -> fulfilment/refund/history.
- Existing vendor/shop/location hierarchy and typed product/service locations; country geography and currency.
- Owner/staff/specialist distinction; global and custom roles, accepted invitations, permissions, branch assignments and account-creation workflow.
- Schedules, repeats/closed/disabled dates, extras, grouped appointments, cancellation policies, membership/gift balances and reviews.
- Product images/translations/properties/extras/variants/SKUs, stock/min/max quantities, wholesale/discount/bonus behavior, group carts, mixed-shop orders, POS, delivery/pickup/digital entitlement, replacements/refunds.
- Historical IDs, parent links, references, money/rate/fee/commission snapshots, transactions, liabilities, wallet history, payout/settlement records and audit evidence.
- Country-scoped administration and country payment/currency configuration.
- Safe per-vendor gateway configuration and platform collection/fee capabilities already present, while repairing authenticity and ledger inconsistencies.
- REST v1 and Flutter parsing; auth/logout/FCM/profile/address/notification interfaces.
- Original logos/colors/assets/layouts and useful user/vendor workflows.
- Scheduled notification/reconciliation work, email/SMS/push, file access and localization behavior.

Preservation does **not** require reproducing vulnerabilities, exact-wallet failure, fake amounts or disabled TLS. Fix these intentionally with compatibility/security tests rather than silently carrying them forward.

## 17. FEATURES THAT SHOULD BE REFACTORED

- Centralize business/country/branch context and Policies while retaining existing IDs/permission names behind an adapter.
- Reuse one pricing/fee/coupon/tax service from quote, create, update, web and API paths.
- Unify booking creation/rescheduling/extra-time with branch and atomic availability enforcement.
- Extract inventory reservation/mutation/restoration and order state transitions.
- Normalize PaymentAttempt/CollectionIntent/WebhookInbox, collection adapters, accounting ledger and payout adapters as separate services.
- Replace request-dependent economics with explicit stored snapshots and DTOs/resources for presentation.
- Consolidate invitation activation/revocation and specialist assignment without forcing every staff member into specialist status.
- Introduce Inertia/React by surface after domain parity; keep REST/mobile endpoints unchanged.
- Replace unsafe deploy/test/migration recipes with isolated, additive, rehearsed operations.

## 18. FEATURES THAT ARE BROKEN

“Broken” here means **source-demonstrated defect**, not an observed production failure:

- Installation first-user token shortcut and destructive network installer are unsafe to expose.
- Installation database-writing code uses file contents as filename, not the `.env` path.
- Orange disables TLS verification and sends a hardcoded integration code.
- Mercado Pago creates a one-unit amount regardless of computed checkout total and has callback linkage defects.
- PayFast notification URL is emitted without matching registered callback route in inspected routes.
- Iyzico depends on an absent SDK and its webhook registration points to the initiation method.
- Exact-wallet-balance payment is rejected for both booking and order.
- Booking quote/cascading overlap checks use containment where general overlap is needed.
- Vendor ad-package show ownership predicate is malformed and can reject legitimate reads.
- Gateway routing/booking liability snapshots can disagree after shop toggle changes.
- PayPal/provider preprocessing rounds positive fractional major-unit amounts upward in `ceil()` paths rather than preserving exact amounts.
- Callback authenticity is insufficient for several providers; payment-status mutation is not a verified settlement.

Potential stock overselling, wallet double-spend, authorization leakage, timezone drift and runtime build failures are classified as **risks/unknowns**, not asserted observed incidents.

## 19. MISSING FEATURES / INCOMPLETE TARGET COVERAGE

| Target capability | Current gap |
| --- | --- |
| Fully enforced owner/admin/branch-manager/staff/specialist organization | Useful role/invitation/branch pieces exist, but active membership context, revocation and uniform action authorization remain incomplete |
| Country managers administering explicitly assigned country sets | One direct country plus first accepted invitation resolution; multi-country selection/sets not complete |
| Direct vs platform collection across both domains | Orange/MTN routing and booking snapshot/payable exist; order payable/snapshot lifecycle is incomplete |
| Admin force-direct/force-platform/vendor-choice policy | Complete consistent policy enforcement not established |
| Independent real payout rails | Wallet/request bookkeeping exists; bank/MTN/Orange disbursement adapters and reconciliation not established |
| Exact country/vendor/category commission hierarchy | Fixed/percentage service fees and shop/master commission exist; hierarchical rules/version snapshots incomplete |
| Secure uniform payment processing | Authenticated verified callbacks, exact amount/currency/merchant match, durable inbox, immutable event IDs and replay handling incomplete |
| Atomic inventory and appointment integrity | Reservations/guarded stock updates, general overlap and validated rescheduling need implementation |
| Branch/timezone-aware client contracts | Server branch checks exist; customer branch choice and explicit timezone conversion are incomplete |
| Full compatibility/financial regression baseline | Useful tests exist, but no complete executable/mobile fixtures or concurrency/provider-negative suite established |
| Bank-payment functionality | No dedicated implemented/verified bank rail found |

Separate vendor/master mobile applications were not found; they are a future product choice, not a reason to invalidate the customer API.

## 20. PROPOSED LARAVEL ARCHITECTURE

**Proposal only; not implemented.** Retain the existing Laravel 12 application and data; progressively introduce a modular monolith with shared application services.

```text
Presentation:
  Existing /api/v1 controllers/resources (compatibility adapters)
  Future Inertia web controllers + React pages
  Existing Next/admin clients during transition
  Existing Flutter client
                    |
Application actions + validated DTOs + Policies
                    |
Domains:
  Identity & Organization / Countries & Geography / Marketplace
  Service Catalog & Specialists / Availability & Booking
  Product Catalog & Inventory / Cart & Ordering & Fulfilment
  Pricing & Promotions & Taxes / Commission Policy
  Payment Collection / Accounting Ledger / Payouts
  Notifications / Content & Memberships & Gifts & Digital Entitlements
                    |
Eloquent persistence adapters, provider adapters, jobs/outbox/inbox
```

### Authoritative responsibilities

Laravel owns authentication/authorization, tenant/country scoping, availability, inventory, booking/order states, money/discount/coupon/tax/commission, collection credentials, transactions, balances/liabilities, payouts and database operations. React handles presentation, forms and interaction—not an independent business backend.

### Organization model approach

- Preserve `users`, `shops`, `shop_locations`, invitations, roles and branch pivots initially.
- Introduce an explicit membership abstraction/application service over accepted invitations; keep legacy REST resources.
- Separate business role/permissions from specialist profile/service assignments.
- Model country grants and business grants separately, with explicit authorized active-country/business selection.
- Only propose a canonical physical Branch table after reconciling SERVICE/PRODUCT ShopLocation pairs and reviewing mobile/foreign-key dependencies. Keep a legacy location mapping if a future branch entity is approved.
- Policies authorize both an action and its resource scope. Repository/controller queries must remain scoped; frontend hiding is never enforcement.

### Booking/inventory boundaries

- `QuoteBooking`, `CreateBooking`, `RescheduleBooking`, `CancelBooking`, `ExtendBooking` reuse one availability/branch/pricing boundary.
- Store UTC instants plus branch IANA timezone and original local-time context after a reviewed conversion plan; adapt v1 date strings.
- Introduce short-lived reservations if product requirements call for them; lock stable master/resource/day keys and recheck general interval overlap inside the commit.
- `SetCartQuantity`, `QuoteOrder`, `CreateOrder`, `ChangeOrderStatus`, `RefundOrder` use authoritative stocks/prices and currency.
- Guard inventory decrements with row locks or atomic conditional SQL; stock restoration emits once per approved cancellation/refund event.
- Preserve multi-shop parent/child and delivery/digital workflows; never model an order as a booking.

### Money/commission boundary

- Introduce a currency-aware Money representation; distinguish zero/2/3-decimal currencies and provider-specific units.
- Define the exact fee base, fixed/percentage rules, override precedence (platform -> country -> vendor -> category as approved), tax inclusion, discount/coupon sponsor and rounding rule.
- Freeze applied rule/version, currency/exchange rate, components and payable amount at commit.
- Retain old snapshots untouched; correct future calculations behind a feature flag after business approval.

### Payments/accounting/payouts

```text
Checkout quote -> frozen CollectionIntent -> PaymentAttempt
 -> VendorDirect OR PlatformCollection provider adapter
 -> authenticated WebhookInbox / provider-status reconciliation
 -> exact verified settlement -> transaction + ledger + domain transition
 -> platform liability / available vendor balance
 -> separately approved PayoutRequest -> payout-rail adapter -> reconciliation
```

- A collection intent freezes domain/payable, collection mode, merchant/config identity/version, currency/amount and fee breakdown.
- Admin collection policy resolves allow-choice/force-direct/force-platform before initiation and applies equally to bookings/orders.
- Provider capability registry distinguishes supported country/currency/collection method/payout rail; unsupported combinations fail clearly.
- Provider callbacks authenticate, retrieve/compare settlement data where appropriate, deduplicate and atomically record an immutable event before domain updates.
- Accounting records fees, vendor payable, refunds, reversals, payout reservations and payouts without recomputing history from live settings.
- A future US-card -> platform -> Cameroon MTN payout flow requires **both** a real supported card collection adapter and a separate eligible disbursement adapter. No fake integration or simulated success should be presented.
- Use outbox/inbox and after-commit queue dispatch for reliable side effects.

### Inertia/React adoption

Use Inertia first for an approved administrative/vendor surface where Laravel sessions and Policies can simplify coordination. Build React pages from preserved AgendaAlly UI/assets. Retain Next storefront until public SEO, redirects, locale, caching and checkout parity are proven; then decide whether all or selected public pages benefit from Inertia/SSR.

Mobile continues to use Sanctum REST. Future Inertia uses session cookies+CSRF and shares the same actions/Policies, not `/api/v1` controller code duplicated in a new frontend backend.

## 21. PROPOSED MIGRATION STRATEGY

### Non-negotiable safety rules

1. Obtain approval before runtime installation, code/config changes, schemas, seeders, provider operations or UI replacement.
2. Establish source commit/release and actual schema/migration state; capture safe database and media backups through an authorized process.
3. Build an isolated, anonymized working database. Original Composer install/update hooks execute seeding/storage publishing; initial baseline must suppress scripts and run only explicitly reviewed commands.
4. **Never run `migrate:fresh`, bulk seed, `migrate:rollback`, destructive historical remigrations or tests against an unknown/prod database.**
5. The test guard only checks that resolved DB name contains `test` (`tests/TestCase.php:34–47`); this is useful but insufficient proof of isolation. Use distinct credentials/host and independently assert environment/schema ownership.
6. Extract/fix one boundary at a time. Preserve v1 controllers/resources as adapters and keep old clients functional.
7. Use additive expand -> backfill -> compare -> switch -> deprecate -> contract migrations. Retain reversible mappings and enough historical data for reconciliation.
8. Make any changed business economics explicit and approved; do not silently “fix” commission sponsorship/rate assumptions.
9. Feature flags per domain/surface/provider/country allow gradual rollout. Rollback a presentation switch is different from undoing committed financial operations.
10. No table/column removal until old and new readers/writers, mobile versions, reports, exports and scheduled work have been checked.

### Required reconciliation

- Row counts/ID maps and orphan checks for users, roles, invitations, shops, locations, services/masters, products/stocks, carts, bookings/orders/details.
- Per-currency totals: gross, discounts, tax, commission, platform fee, vendor payable, wallets, refunds/reversals and payouts.
- Frozen historical rates/fees and grouped parent/child sums; no old amount recomputation.
- Stock on-hand/reserved/sold/restored and specialist branch/schedule assignments.
- Files/images/digital entitlements, notification preferences/FCM tokens, localization/theme assets.
- API fixtures parsed by existing Flutter models and web/admin clients.
- Compare anonymized old/new outputs; resolve every material difference before switching writes.

### Rollback approach

Before cutover, preserve old read paths and compatibility mappings. After cutover, use explicit compensating financial/stock/reservation events rather than deleting ledger history. A DB restore is only safe with a defined write pause/replay window and accounting reconciliation. Provider charges/refunds/payouts cannot be rolled back by switching UI or restoring code.

## 22. RECOMMENDED IMPLEMENTATION PHASES

**Every phase below is a recommendation awaiting approval, not work started by this audit.**

| Phase | Scope | Approval/exit gate |
| --- | --- | --- |
| 0 — Confirm baseline and protect originals | Approve runtime/environment setup; copy/anonymize DB; safe dependency restore with scripts suppressed; inspect migration state; build original clients; capture AgendaAlly UX/contracts; confirm installer deployment exposure | Original backend/routes/tests/clients demonstrably run in isolated environment; reproducible findings and recoverable backups |
| 1 — Contain critical security/financial defects | Disable installer token/destructive surface; reset-token fix; provider authenticity/TLS; unsafe amounts/routes; redacted logs; active scope/revocation checks; freeze collection intent | Negative unauthorized/tampered callback tests; exact-money fixtures; no prod operation needed to prove containment |
| 2 — Establish compatibility and authorization boundaries | v1 contract fixtures; Policies/context; staff/country assignment/activation/revocation; branch mapping; customer/admin/seller/master tests | Cross-tenant/country/branch read/write/export/config/financial isolation matrix passes |
| 3 — Make booking and commerce reliable | Shared pricing, inventory guarded mutations, appointment overlap/reservations/reschedule, state transitions, exact-wallet fix, cancellation/refund/digital restoration | Concurrent booking/stock/wallet tests; booking+product journeys and Flutter contract regression fixtures pass |
| 4 — Complete payment modes and accounting | Symmetric booking/order snapshots/payables/reversals; enforced admin modes; commission rule snapshots; verified collection adapters; currency-separated ledger; separate real payout rails | Repeated/out-of-order/wrong-amount callbacks reconcile once; vendor balances and refund/payout liabilities match fixtures/provider sandbox |
| 5 — Introduce selected Inertia/React pages | Preserve branding/assets/workflows; migrate approved admin/vendor surface first; keep legacy clients and REST | Feature-by-feature parity, accessibility, permissions, reports and rollback feature flags demonstrated |
| 6 — Storefront modernization and controlled rollout | Decide Next retention vs selected/full Inertia with SEO/SSR; staged country/vendor rollout; queue/storage/observability/runbooks | Booking **and product ordering** parity; mobile compatibility; ledger/stock reconciliation; monitored rollout and approved rollback plan |
| 7 — Deprecate only proven obsolete structures | Remove old readers/UI/schema only after usage/mapping/data verification | Explicit approval; compatible supported mobile versions; no data or financial history lost |

### Existing tests and the gaps they actually leave

Static assertions exist for country assignments/scoping/roles, shop permissions/staff search/invites, branch booking validation, checkout-country currency, filters, gateway config encryption, MTN/Orange faked requests, MTN reconciliation, mixed-shop gateway restrictions, PayPal conversion, Stripe zero-decimal/wallet configuration, fee ledgers and booking collection snapshots.

Examples:

- `MtnServiceTest.php:86–155`: fake request-to-pay amount/currency/environment/reference and missing config.
- `OrangeServiceTest.php:82–117`: fake configured currency/real amount and unconfigured-shop rejection.
- `ReconcilePendingMtnPaymentsTest.php:99–201`: paid/fresh/abandoned/platform reconciliation.
- `PlatformFeeLedgerTest.php:68–125`, `OrderPlatformFeeLedgerTest.php:50–159`: pending fee, created-paid orders, duplicate/no-fee behavior.
- `CollectViaPlatformSnapshotTest.php:107–157`: booking liability remains tied to frozen flag despite current-shop toggle.
- `BookingBranchValidationTest.php:88–240`: unassigned/cross-shop/ambiguous/single-location behavior.
- `Services/CartService/CartServiceTest.php:15–21`: profile smoke test only, not commerce coverage.

**Required new coverage:** installer/reset protection; forged provider callbacks; amount/currency/merchant mismatch; SDK/route smoke tests; live-vs-frozen routing across initiation+settlement; paid-at-creation booking payable; order platform payable; concurrent stock/booking/wallet/payout; transitions and cancellation/refund restoration; branch/timezone updates; cart quantities; partial refunds/digital access; old Flutter parsers; full role/tenant/country matrices.

## 23. OPERATIONS / CONFIGURATION / INTEGRATIONS APPENDIX

### Scheduled work and infrastructure

- Scheduler includes hourly email, every-minute booking/service-master notifications, cleanup/auction/auto-ended booking jobs and MTN reconciliation every five minutes (`Console/Kernel.php:12–31`).
- Deployment documentation requires queue restart after code changes (`DEPLOYMENT.md:21–23`); actual workers, cron and execution are U.
- Queue default `sync`, cache default `file`, session default `database`. Scaling across instances requires shared, explicitly chosen cache/session/queue/files; it should not be assumed Redis is already configured.
- Public storage symlink, writable `storage`/`bootstrap/cache`, persistent uploads and correct `APP_URL`/`IMG_HOST` are documented (`DEPLOYMENT.md:29–71,95–151`). Repository `storage` is not a copy of all live media.
- FileHelper/gallery upload paths, digital-file download authorization and tenant ownership need acceptance/negative tests before changing storage. Local/public/private/S3 disk definitions exist; S3 existence is not verified usage.

### Messaging/integrations

- SMTP/PHPMailer uses DB email settings and storage attachments (`EmailSendService.php:58–120,325–364`).
- Vonage/Nexmo SMS service validates configured keys and substitutes OTP before calling the provider (`SMSGatewayService/NexmoService.php:22–39`); OTP base caches data (`SMSBaseService.php:24–47`). Composer also includes Twilio/SendGrid, but package presence is not proof those execution paths are configured.
- Firebase push is used by jobs/commands (`AttachDeliveryMan.php:65–105`, `ServiceMasterSendNotification.php:54–76`) and client FCM token registration.
- Firebase social authentication token verification is present; map/geocoding services and web/mobile map dependencies exist.
- WebSocket/Firestore-related chat clients/configuration exist. A separately deployed websocket service and reliable end-to-end chat delivery were not verified.
- AI logs/services/config keys exist; no paid provider invocation or data-sharing behavior was tested.
- No Zoom integration was established; do not advertise it without tracing a concrete implementation.
- Languages/translations/settings drive locale, RTL, theme and API messages. Translation SQL is not application data.

### Configuration names only

- Backend: `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY`, `FRONT_URL`, `ADMIN_URL`, `IMG_HOST`, `DB_CONNECTION`, DB host/database/user/password key names, `SESSION_DRIVER`, `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, `SESSION_LIFETIME`, `CACHE_DRIVER`, `QUEUE_CONNECTION`, Redis settings, `FILESYSTEM_DRIVER`, S3/AWS key names, Firebase/email/SMS config, `WS_URL`, `WS_SECRET`, `OPEN_AI_TOKEN`.
- Web: `NEXT_PUBLIC_BASE_URL`, `NEXT_PUBLIC_IMAGE_URL`, `NEXT_PUBLIC_WEBSITE_URL`, `NEXT_PUBLIC_PROJECT_NAME`, `NEXT_PUBLIC_UI_TYPE`, default token/language/country/location/cache names, Google Maps/Firebase/public Stripe/VAPID names, admin URL and demo flag.
- Flutter: compile-time `BASE_URL`, `WS_BASE_URL`; platform Firebase/maps/app-link configuration.
- Payment credentials are named in Section 10; actual values are intentionally excluded.

The configured Replit `SESSION_SECRET` is not evidence that the original Laravel `APP_KEY` or original session setup is available. Retain encryption keys securely during any authorized migration; do not export decrypted gateway secrets into reports.

## Final approval boundary

This audit supplies the requested stack, structure, domain/features, payments/accounting/country/mobile/security findings and safe Laravel modernization proposal. It does **not** certify working production behavior. Runtime validation is an explicit next phase requiring approval.

**Stop here. No Laravel rebuild, Inertia integration, schema change, dependency installation, provider connection, data migration or application replacement is authorized by this report.**