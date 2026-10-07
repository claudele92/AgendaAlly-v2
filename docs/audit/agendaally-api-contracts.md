# AgendaAlly — API / Flutter Compatibility Appendix

**Companion to:** `agendaally-original-audit.md`  
**Date:** 30 September 2026  
**Source:** Original `.migration-backup/` tree only.  
**Evidence:** Static source contracts and synthetic examples. No live HTTP request/response was captured. Examples below are representative subsets, not complete schemas or verified production payloads.

## 1. Transport, auth, wrappers and errors

### Transport

- Backend base prefix `/api/v1`: RouteServiceProvider adds `api`, routes add `v1`.
- Flutter `BASE_URL` and `WS_BASE_URL` are compile-time environment strings (`customer_app/lib/app_constants.dart:15–18`).
- Dio sets JSON headers and 30-second connect/receive/send timeouts (`infrastructure/service/http_service.dart:7–23`).
- TokenInterceptor adds `Authorization: Bearer <token>` only when `requireAuth=true` and token is nonempty (`token_interceptor.dart:10–18`).
- Query parameters frequently include `lang`, country/city/region, currency and rate. Not all are authoritative business inputs.
- Mobile and web use mixed query/body payloads; Laravel receives both. Keep compatibility while moving sensitive query parameters to bodies in an approved client/server change.

### Standard success helper

`backend/app/Traits/ApiResponse.php:20–27` emits HTTP 200:

```json
{
  "timestamp": "<server timestamp>",
  "status": true,
  "message": "<localized message>",
  "data": {}
}
```

Data may be an object, array, nullable result, Resource or serialized PaymentProcess. Some collection endpoints return Laravel resource collections directly rather than wrapping them with successResponse.

### Errors

`ApiResponse.php:38–65` uses `statusCode`, not a universal top-level `code`:

```json
{
  "timestamp": "<server timestamp>",
  "status": false,
  "statusCode": "ERROR_400",
  "message": "<localized validation message>",
  "params": {
    "data.0.start_date": ["<field message>"]
  }
}
```

- `BaseRequest.php:73–82` maps validation to **HTTP 422**, symbolic `ERROR_400`, and `params`.
- `OnResponse.php:16–31` defaults `ERROR_404` to HTTP 404 and other service errors to HTTP 400; an explicit `http` overrides it.
- `SanctumCheck.php:26–35` emits HTTP 401 with `ERROR_100`.
- `ApiResponse::errorResponse` itself defaults to HTTP 500 if called without another HTTP code.
- `ResponseError.php` defines canonical symbolic code strings. Localized `message` must not be treated as stable machine-readable state.
- Unexpected framework/runtime exception mappings are still unverified. Do not promise uniform status codes for every failure path.

### Pagination

Direct paginated ResourceCollections ordinarily expose `data`, `links`, `meta`; inspect each controller rather than wrapping all v1 lists anew:

```json
{
  "data": [{"id": 101}],
  "links": {
    "first": "<page URL>",
    "last": "<page URL>",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

This is a source-derived Laravel pagination sketch, not an observed full response. `ShopsPaginateResponse.fromJson` reads the top-level `data` list and ignores pagination metadata (`shops_paginate_response.dart:8–14`); other response classes may parse `links/meta`. Preserve the shape each caller expects.

## 2. Authentication / registration / profile

| Operation | Actual path/method | Fields and source dependency |
| --- | --- | --- |
| Password login | POST `/auth/login` | Flutter sends query parameters `{email,password}` or normalized `{phone,password}`; `auth_repository.dart:17–35` |
| Social login | POST `/auth/google/callback` | Flutter `{email,name,id,img?}`; `id` is Firebase ID token verified server-side, not a bare social user ID; `auth_repository.dart:43–61`; backend `LoginController.php:252–261` |
| Phone check | POST `/auth/check/phone` | `phone`; response includes `data.exist` |
| Registration start | POST `/auth/register` | Flutter phone OTP start sends body `{phone}`; email start sends query `{email}`. `RegisterRequest` also accepts `password,firstname,referral`; UserModel.toJson is used later for profile completion, not this initial registration payload. |
| Phone verification | POST `/auth/verify/phone` | User/verification data; `auth_repository.dart:89–99`; exact OTP issuance/confirmation follow server auth services |
| Email verification | GET `/auth/verify/{hash}` | Token-based route; response used by auth repositories |
| Reset flows | POST `/auth/forgot/password`, `/before`, `/confirm`, `/auth/forgot/email-password`, `/auth/forgot/email-password/{hash}` | Existing phone/email reset flows; insecure token generation must be fixed intentionally without preserving the vulnerability |
| Logout | POST `/auth/logout` | Authenticated client optionally sends `firebase_token`; backend deletes current personal token; `auth_repository.dart:106–122`; `LoginController.php:212–244` |
| Current profile | GET `/dashboard/user/profile/show` | ProfileResponse; Auth/User repository |
| Profile update | PUT `/dashboard/user/profile/update` | Existing profile fields and resource, not a new auth user format |
| Password update | POST `/dashboard/user/profile/password/update` | Existing password request rules |
| FCM registration | POST `/dashboard/user/profile/firebase/token/update` | `{firebase_token}`; `auth_repository.dart:68–83` |
| Language/currency | PUT `/dashboard/user/profile/lang/update`, `/currency/update` | Preserve selected preference fields |
| Delete account | DELETE `/dashboard/user/profile/delete` | Mobile clears local storage after call; secure deletion/deactivation policy requires audit of retention dependencies |

Source-derived login response subset:

```json
{
  "timestamp": "<server timestamp>",
  "status": true,
  "message": "User successfully login",
  "data": {
    "access_token": "<synthetic token placeholder>",
    "token_type": "Bearer",
    "user": {"id": 101}
  }
}
```

Backend may also include `token`. Flutter `LoginResponse` -> `UserData` specifically parses `data.access_token`, `data.token_type`, `data.user` (`login_response.dart:16–20,67–70`). Do not rename them to `jwt`/`profile`.

**Security compatibility gap:** Flutter login/social methods currently send credentials/tokens in query parameters. URLs may be captured by proxies/logging even on POST. Fix with coordinated server/client acceptance of body fields; do not emit real query strings in reports/logs.

### Two-stage registration/verification

Phone OTP starts with `{phone:"<synthetic normalized number>"}` in the body (`auth_repository.dart:181–188`); email registration starts with `{email:"synthetic@example.invalid"}` in query (`:241–246`). `RegisterRequest.php:17–28` accepts optional phone (numeric, unique among verified users), email (format/verified-user uniqueness), password (string), firstname (2–100 chars) and referral (existing `users.my_referral`).

Email verification GET `/auth/verify/{verifyCode}` specifically reads `response.data["data"]["token"]` and saves it locally (`auth_repository.dart:254–261`). The subsequent `sigUpWithData` operation is authenticated profile PUT using `UserModel.toJson()` (`:286–293`). Preserve this **`data.token` verification response**, distinct from password-login `data.access_token`.

Phone Firebase password confirmation sends `{phone,id,type:"firebase",password}` to `/auth/forgot/password/confirm` (`:155–173`). This is separate from the insecure timestamp-derived email reset shortcut described in the main report.

## 3. Marketplace / product / service data

| Domain | Public paths | Identifier/shape cautions |
| --- | --- | --- |
| Shops | `/rest/shops/paginate`, `/search`, `/{uuid}`, `/slug/{slug}`, `/{id}/categories`, galleries/reviews | UUID/slug for some detail calls, numeric IDs for related resources; Flutter parameter variable names alone do not prove UUID vs ID |
| Geography | `/rest/countries`, `/cities`, `/regions`, `/areas`, `/check/countries/{id}` | Numeric geography IDs; country/city/region filter parameters |
| Categories | `/rest/categories/parent`, `/children/{id}`, `/paginate`, `/search`, `/{uuid}`, `/slug/{slug}` | Mixed IDs/UUIDs; parent/child structure and translations |
| Products | `/rest/products/paginate`, `/search`, `/discount`, `/ids`, `/{uuid}`, `/slug/{slug}`, `/shop/{uuid}`, `/category/{uuid}`, related/reviews/compare | Product UUID is different from stock numeric ID used in carts/orders |
| Services | `/rest/services`, `/service-extras`, `/service-masters` | Numeric assignment IDs; service-master is specialist+service+shop assignment |
| Masters/time | `/rest/masters`, `/master/{id}/times`, `/master/times-all`, closed/disabled resources | Master user ID vs service_master ID are not interchangeable |
| Settings/localization | `/rest/settings`, `/translations/paginate`, `/languages/active`, `/currencies/active`, `/timezone`, term/policy/FAQ | Preserve typed setting value/null/localization behavior |
| Likes/reviews | Authenticated `/dashboard/likes`, `/like/store-many`; product/shop/booking review routes | Favorites are like records; review ownership/eligibility is server authority |

Primary route evidence: `backend/routes/api.php:70–157,210–261,298–299,422–426`; clients `web/services/{shop,product,category,master}.ts`, Flutter `shops_repository.dart`, `settings_repository.dart`.

## 4. Booking requests and responses

### Quote/create

- Quote: POST `/rest/bookings/calculate`.
- Create: POST `/dashboard/user/bookings`.
- Flutter quote constructs `currency_id`, `data[]` with `service_master_id`, `start_date`, extras/membership, optional coupon/gift.
- Backend may intentionally override requested currency with shop-country checkout currency.
- Start wire format is **`Y-m-d H:i`**. Quote derives end by interval+pause; create does not simply trust client end.

Synthetic create request:

```json
{
  "currency_id": 1,
  "payment_id": 2,
  "data": [
    {
      "service_master_id": 301,
      "start_date": "2026-10-15 10:30",
      "service_extras": [401],
      "note": "Synthetic appointment note"
    }
  ]
}
```

Optional top-level fields include `coupon`, `user_gift_cart_id`, `from_wallet_price`; optional item fields include `user_member_ship_id`, `price_id`, gender/notes/address data. The server supports optional `data.*.shop_location_id`, validates existence and checks actual shop/type/master assignment in BookingService.

**Current Flutter create/quote maps omit shop_location_id** (`booking_repository.dart:80–113,155–170`). Add branch choice compatibly; do not force a field into every existing request.

Validation source: `Booking/StoreRequest.php:21–73`. Creation uses authoritative authenticated user context; quote/create pricing/availability/branch safety must be rechecked on commit.

### Source-derived response subset

Booking create returns a list under `data`. Flutter picks `data.first.id` (`booking_repository.dart:121–124`):

```json
{
  "timestamp": "<server timestamp>",
  "status": true,
  "message": "<localized message>",
  "data": [
    {
      "id": 501,
      "ids_by_parent": "501",
      "total_price_by_parent": 50,
      "service_master_id": 301,
      "master_id": 201,
      "shop_id": 101,
      "currency_id": 1,
      "start_date": "2026-10-15 10:30:00",
      "end_date": "2026-10-15 11:15:00",
      "price": 45,
      "service_fee": 5,
      "total_price": 50,
      "status": "new"
    }
  ]
}
```

The numerical values/date serialization here are illustrative, not a captured quote or guaranteed serializer format. `BookingResource.php:40–95` defines fields: IDs, grouped totals/cancellation, currency/rate, start/end, price/discount/commission/extras/service fee/total/coupon/gift/tips/extra-time, status/notes/type/data/gender/timestamps, and conditional service/master/shop/location/user/membership/currency/transaction/review/activity relations.

Resources use `when` and `whenLoaded`; nested fields can be absent. `shop_location` relation exists; a direct numeric `shop_location_id` field is not emitted in the inspected BookingResource. Preserve those distinctions.

### Operations and status contracts

| Operation | Path | Client/server contract |
| --- | --- | --- |
| List | GET `/dashboard/user/bookings` | Mobile past statuses `canceled,ended`; active `new,progress,booked`; geography/lang filters |
| Group detail | GET `/dashboard/user/bookings/{id}/get-all` | Numeric parent/booking ID; list grouped children |
| Detail/update | GET/PUT `/dashboard/user/bookings/{id}` | Customer ownership check; update allows validated dates/master/extras/etc; not a dedicated complete reschedule policy |
| Parent cancellation | POST `/dashboard/user/booking/parent/{id}/canceled` | Flutter sends `{status:"canceled"}` |
| Status/notes/extra time | POST `/dashboard/user/bookings/{id}/status/update`, `/notes/update`, `/extra-time` | Role-specific restrictions and domain effects; do not expose arbitrary statuses |
| Review | POST `/dashboard/user/booking/review/{id}` | Existing review payload and ownership/eligibility |
| Master time change | POST `/dashboard/master/bookings/{id}/times/update` | Specialist route; current availability recheck gap |

Booking status strings are exactly `new,canceled,booked,progress,ended`. IDs and parent-group semantics must survive refactoring.

## 5. Cart requests, quantities and group wrappers

### Authenticated/public differences

- Authenticated `/dashboard/user/cart` get/create; `/insert-product`, `/open`, `/set-group/{id}`, `/calculate/{id}`, product/member/delete/my-delete/status endpoints.
- Public `/rest/cart`, `/rest/cart/{id}`, `/insert-product`, `/open`, product/member delete and `/status/{user_cart_uuid}` group endpoints.
- Public coupons `/rest/coupons/check`, order quote `/rest/order/products/calculate`.

Evidence: `api.php:191–199,379–389`; mobile `cart_repository.dart:43–424`; Cart request/service classes.

Cart line identity is numeric `stock_id` plus normalized **absolute quantity**. Backend writes supplied normalized quantity rather than adding a delta (`CartService.php:97–105,147–158,283–297`). Preserve grouping by user-cart/shop and owner/member UUIDs. Exact full cart insertion maps should be extracted into endpoint fixtures before changing them.

Synthetic authenticated cart insertion accepted by `backend/app/Http/Requests/Cart/InsertProductsRequest.php:18–38`:

```json
{
  "currency_id": 1,
  "region_id": 1,
  "country_id": 2,
  "city_id": 3,
  "products": [
    {"stock_id": 801, "quantity": 2}
  ]
}
```

Currency/region/country and the products array are required in that request; city/area are optional with hierarchical existence validation. Optional product `images[]` are strings. Flutter error handling depends on validation `params` keys beginning with `products.` to identify/remove a stale local cart item (`cart_repository.dart:48–64`). Preserve those field paths.

### Wrapper distinction

Flutter group setup:

```text
response = POST /dashboard/user/cart/open
cartId = response.data["data"]["id"]
POST /dashboard/user/cart/set-group/{cartId}
```

Here Dio `response.data` is the whole JSON body. The **JSON path is `data.id`**, not `data.data.id` (`cart_repository.dart:251–265`).

External gateway parsing below genuinely reads JSON `data.data.url`. Do not “standardize” both while v1 clients are active.

## 6. Product order checkout and history

### Source-derived checkout request

Flutter `CreateOrderModel.toJson` (`create_order_model.dart:35–76`) maps pickup to `point`, physical to `delivery`, otherwise `digital`.

Synthetic pickup request:

```json
{
  "cart_id": 601,
  "currency_id": 1,
  "rate": 1,
  "payment_id": 2,
  "delivery_type": "point",
  "delivery_point_id": 701,
  "delivery_date": "2026-10-15 14:00",
  "tips": 0,
  "lang": "en",
  "notes": {
    "product": {"801": "Synthetic item note"},
    "order": {"101": "Synthetic shop note"}
  }
}
```

- Physical delivery supplies `delivery_price_id` and usually `address_id`; user-owned address validation exists.
- Coupon map is shop-keyed, e.g. `"coupon":{"101":"SYNTHETIC"}`.
- Split-wallet amount is `from_wallet_price`; external initiation may omit `payment_id`.
- Server also accepts POS `data[]` with shop and product stock/quantity batches through its request rules.
- Backend recalculates price/stock/currency/fees; request `rate` is not permission to choose arbitrary economics.

Validation: `Order/StoreRequest.php:18–76`. Creation: `OrderService.php:101–155`.

### Paths and response

| Operation | Path |
| --- | --- |
| Checkout | POST `/dashboard/user/orders` |
| Paginate/history | GET `/dashboard/user/orders/paginate` |
| Active/completed | GET `/dashboard/user/orders/get-active`, `/get-completed` |
| Detail/group | GET `/dashboard/user/orders/{id}`, `/{id}/get-all` |
| Status/cancel | POST `/dashboard/user/orders/{id}/status/change` |
| Product/order/delivery review | Existing review routes under dashboard/user |
| Refund | `/dashboard/user/order-refunds` create/read; paginate/delete variants |
| Invoice | `/dashboard/user/export/order/{id}/pdf`, `/export/all/order/{id}/pdf` |

Flutter order cancellation sends status `canceled` (can be query data); order_repository maps parent and refund responses (`order_repository.dart:29–207`).

`OrderResource.php:21–90` includes id, grouped totals/ids, user/shop/currency/rate/status, economics, delivery type/date/address/point/price, details and loaded transaction/refund/fulfilment relations. Preserve conditional omission and parent grouping. Source may expose stock details under legacy nested names; do not replace them with a new `items` schema without adapters.

Statuses: `new,accepted,ready,on_a_way,pause,delivered,canceled`. Transactions have separate states; order delivered does not imply arbitrary provider payment acceptance.

### Critical semantic invariants

- A multi-shop cart creates multiple linked Orders—not one booking or an unscoped global order.
- Orders and booking IDs are separate namespaces/records.
- Stock is decremented on order detail/checkout flow; cancellations/refunds restoration must occur exactly once.
- Digital delivery/file entitlements must depend on an authoritative paid flow, not frontend success.
- Preserve replacements, bonus/wholesale pricing and partial-refund history.

## 7. Payments, transactions and wallet

### Listing/initiation

| Call | Contract |
| --- | --- |
| GET `/rest/payments` | List of enabled/eligible payment records; country/shop/cart context can affect list. Flutter `getPayments()` sends lang only in inspected method, so endpoint-context parity needs attention. |
| POST `/dashboard/user/{tag}-process` | Actual route spelling matters: `flutter-wave`, `pay-fast`, `moya-sar`, `mercado-pago`, etc.; provider model tags are not necessarily literal route strings. |
| POST `/payments/{type}/{id}/transactions` | Internal/manual transaction creation, e.g. `order`, `booking`, `wallet`, `member-ship`, `gift-cart`; `payment_sys_id`, optional wallet amount where supported |
| PUT `/payments/{type}/{id}/transactions` | Status update validates `paid,canceled`; non-cash requires admin+reason and process checks |
| GET `/dashboard/user/mtn-process/{referenceId}/status` | MTN reference polling; response status/body must be captured before client-specific integration |
| POST `/dashboard/user/wallet/send` | Mobile `{uuid,price,currency_id}`; ledger/balance authority remains backend |

Synthetic booking initiation:

```json
{
  "booking_id": 501,
  "currency_id": 1
}
```

Wallet topup additionally uses `wallet_id,total_price`; cart initiation uses checkout payload; membership/gift/parcel/subscription/ad purchases have distinct payable inputs. `PaymentRequest.php:18–84` scopes cart to owner, booking/parcel/wallet/auction to authenticated user where specified, and catalog membership/gift/package records by active state.

The `PaymentBaseController` currently passes `$request->all()` into service (`:32–42`), not only `$request->validated()`. Harden service input ownership/allowlisting in the shared action layer rather than assuming the FormRequest removed unvalidated keys.

### External response nesting

Synthetic hosted-provider response subset:

```json
{
  "timestamp": "<server timestamp>",
  "status": true,
  "message": "success",
  "data": {
    "id": "<synthetic payment-process reference>",
    "data": {
      "url": "<provider hosted checkout URL>"
    }
  }
}
```

Flutter `paymentWebView` reads body `data.data.url` and returns empty string when missing (`payments_repository.dart:63–99`). Web hook reads its response `res.data.data.url` (`web/hook/use-external-payment.ts:64–84`). The web service response abstractions are not necessarily the same object as Dio Response; keep caller-level fixtures.

PayFast uses its own nested data/settings/package/script path and currently references client-side merchant/passphrase constants; their actual values were not inspected. Private signing belongs on the backend. Maksekeskus has a separate method and `MaksekeskusResponse` parser (`payments_repository.dart:175–203`).

MTN request-to-pay/reference polling requires explicit handling; no customer polling UI was found in the searched paths. Orange already maps the provider deepLink to `url` (`OrangeService.php:77–87`), matching generic URL extraction. Deep-link opening, return navigation and authoritative settlement still need runtime tests; do not claim Orange is absent from the app merely because there is no provider-specific screen.

### Status and authenticity

Transaction statuses: `progress,paid,canceled,rejected,refund,refund_failed` (`Transaction.php:64–77`).

Provider statuses such as MTN `SUCCESSFUL`/Flutterwave `successful` are mapped to domain transaction states; preserve mapping intentionally. Do not accept frontend redirect success as payment proof.

Public callback paths are `/api/v1/webhook/...`; `/payment-success` is a browser redirect. Returning to a success page must not be treated as settlement. Several current callback paths/authenticity checks are incomplete (main report Section 10).

### Scope not established by this contract appendix

This appendix records static request/resource/client contracts; it does not certify all generic transaction type IDs are ownership-safe, all callbacks are authenticated, or all wallet amounts are valid. Those negative cases are mandatory baseline/hardening tests.

## 8. Notifications / messaging

- Dashboard notification list/show/delete, delete-all, read-at/read-all routes are defined (`api.php:301–305`).
- User notification preferences use `/dashboard/user/update/notifications` and `/dashboard/user/notifications`.
- FCM token update route/payload must survive.
- Client notification type redirects distinguish order/parcel/blog/booking. Do not redirect order notifications into appointment pages.
- Scheduler and Firebase delivery remain unverified; mocked API shape is not delivery proof.

## 9. Vendor / specialist / country API preservation

No separate vendor/master mobile project was found, but server contracts remain important:

- Seller role group `/dashboard/seller`: current shop, catalog/stock/orders, services/bookings, staff/roles/invites/locations, gateway configuration, finance/advertising/subscriptions.
- Specialist `/dashboard/master`: own service assignments/working/closed/disabled times/bookings/time updates/partner earnings.
- Deliveryman `/dashboard/deliveryman`: fulfilment-related operations.
- Admin `/dashboard/admin`: platform and country-scoped administration.
- Preserve existing invitation status and branch-assignment field names; map a future membership abstraction behind them.
- New Inertia controllers must call shared Laravel actions/Policies, not create a competing auth/availability/order/payment domain.

## 10. Compatibility test pack before migration

Create anonymized/synthetic fixtures and validate them with original parsers:

1. Email/phone/Firebase login, token/profile/logout and validation/auth failures.
2. Paginated shops/products/services and empty/null resources in both languages/directions.
3. Booking quote/create/list/group/detail/cancel/update/review, with extras/coupon/gift/membership and branch cases.
4. Cart absolute quantity, group ownership/member lifecycle, open/set-group wrapper, guest/shared endpoints.
5. Single/multi-shop order checkout, pickup/delivery/digital, parent totals, status/refund/replacement/history.
6. Cash/wallet exact balance/split payment, external hosted response, MTN reference/polling, Orange link and PayFast special response.
7. Provider tags vs route-name mapping; redirect vs authoritative payment; wrong amount/currency/status callbacks.
8. FCM/preferences/notifications and invoice/digital-file authorization.
9. Seller/master/country-admin scopes across create/read/update/delete/export and financial/config paths.
10. Stable symbolic errors/HTTP status, metadata/wrappers/nullable fields and timezone/date coercion.

Use these fixtures to preserve external v1 compatibility while repairing vulnerabilities. Do not lock in unsafe callback trust, insecure reset tokens, or financial rounding as intended contract behavior.

**No API or client change has been implemented by this appendix.**