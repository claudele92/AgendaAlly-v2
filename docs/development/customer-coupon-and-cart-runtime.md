# AgendaAlly — Customer Coupon hydration and cart 404

## Scope

Narrow follow-up to the completed native booking/payment verification.
No booking/payment/provider action, financial change, fake cart or delivery
implementation was authorized or performed.

## Coupon hydration root cause and correction

The Coupon field reproduced an attribute-only mismatch on initial document
hydration of the payment route, not a translation/text mismatch:

- Server input ID and label `for`:
  `_R_m6klritqbn9esneknebn9et9epbalb_`
- Client expected ID and label `htmlFor`:
  `_R_66klritqbn9esneknebn9et9epbalb_`

The stack led through `PaymentFinish → BookingTotal → LoadableComponent →
BookingCouponCheck → Input`. The field's shared `Input` derives its accessible
input/label binding from React `useId`, which depends on the render-tree path.
The Coupon's Next dynamic-loading wrapper introduced the divergent SSR/client
path; the installed loader includes server-only preload rendering around its
lazy component. The observed cause was the lazy Coupon rendering boundary, not
localized Coupon terminology, currency text, an invalid label or a supplied
explicit input ID.

The correction replaces only that small Coupon component's dynamic import
with its normal module import. The fresh server and hydrated client now both
use `_R_66klritqbn9esneknebn9et9epbalb_`. The unrelated gift-card lazy import,
shared Input, Coupon validation/debounce/callbacks, booking and pricing remain
unchanged. No warning suppression, hardcoded/random ID, client-only rendering
or authentication/booking-guard workaround was used.

Only application source file changed:

`.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/booking-total/booking-total.tsx`

## Identified resource 404

Exact observed request:

```text
GET http://localhost:3002/api/v1/dashboard/user/cart?region_id=1&country_id=1&city_id=1&lang=en
```

Classification: application-owned authenticated **Fetch/API** request, JSON
HTTP 404; not image, favicon, font, source map, Next tooling or payment API.

Recorded initiator chain:

`useServerCart.useQuery → cartService.get → fetcher`

Purpose: retrieve the Customer's optional product cart in its selected
location, including shared storefront cart state during booking.

`Dashboard/User/CartController::get` deliberately returns `ERROR_404` /
“Item's not found.” when the scoped repository has no matching cart.
The repository enforces the authenticated owner and native location filtering.
Read-only development checks confirmed no cart matched this actor's selected
region/country/city, independently of the omitted area. This does **not** mean
the actor has no cart anywhere or authorize using a different location's cart.

The existing `useServerCart` adapter already recognizes only the known missing
cart status/machine-code contract and returns successful query `null`, clearing
local product/group state appropriately. Other errors remain errors.

No resource correction is warranted: this is an intentionally absent optional
cart, not an incorrectly missing required resource. Browser network logs may
still show its expected 404. No backend response change, query broadening,
fake cart, image/data fabrication or read-triggered database insertion was made.

Source evidence:

- `.migration-backup/backend/routes/api.php:380`
- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/User/CartController.php:33-47`
- `.migration-backup/backend/app/Repositories/CartRepository/CartRepository.php:54-70`
- `.migration-backup/backend/app/Models/Cart.php:113-132`
- `.migration-backup/web/hook/use-server-cart.ts:19-56`
- `.migration-backup/web/services/cart.ts:28-29`
- `.migration-backup/web/utils/cart-error.mjs:1-9`

## Focused verification

- Native Customer TypeScript check passed (`tsc --noEmit --incremental false`).
- Restarted only the affected existing Customer preview once; Admin/Vendor and
  Laravel previews were not restarted or reconfigured.
- Clean initial-document hydration: server input ID/label binding matched the
  hydrated DOM; no Coupon hydration warning or page error.
- Native flow: Douala → Beard Trim → Armand Fotso → offered October 6, 2026
  09:00 → Notes → Payment. Preserved native query and `offerId=5`.
- Settled total: 6,000 FCFA service + 1,200 FCFA fee = 7,200 FCFA.
- Coupon stayed empty; clicking its visible label focused its associated input.
- Cash loaded as the sole eligible method, initially unchecked; selecting its
  radio worked without the uncontrolled-to-controlled warning.
- No new changed-module runtime error. Existing development image/preload/HMR
  warnings and the expected empty-cart 404 were not misreported as fixed.
- Final booking/payment button was not clicked. No coupon/gift-card/membership,
  wallet or provider action was performed.
- Unchanged stale/zero-method and shared-consumer checks were not rerun.
- Read-only before/after fingerprints confirmed all 4 booking rows and 15
  transaction rows were unchanged. Customer, Admin/Vendor and Laravel previews
  are running; no replacement preview infrastructure was introduced.

The Delivery Driver audit is separate in `vendor-delivery-driver-audit.md`.
Its source findings and proposed phases are not implementation approval.