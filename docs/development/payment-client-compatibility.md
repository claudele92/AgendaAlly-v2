# Payment client context compatibility

The Web/Admin changes consume contextual payment eligibility and safe payment
policy metadata. Flutter's `PaymentsRepository.getPayments` now accepts
optional `bookingId`, `cartId`, `shopId`, `locationType`, and
`requestedCurrencyId` parameters and forwards them to
`GET /api/v1/rest/payments`. Booking/cart identifiers use the existing
authenticated Dio client; shop-only discovery remains public. The selected
currency is sent only as `currency_id` display preference, never as charge
currency authority.

The Web booking payment picker does not have an owned `booking_id` at the
moment it lists methods. The current route is
`app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/booking/payment`;
`ProtectedPayment` passes its resolved `shopId` to the booking picker, which
uses service `location_type: 2`. `PaymentFinish` creates the booking only after
the customer selects a method, so there is no route booking ID to pass at that
point. This uses the API's public `shop_id + location_type` context rather
than inventing an identifier. If the flow later loads methods for an existing
booking, pass that owned ID through `ProtectedPayment` →
`components/payment-list/payment-list.tsx` → shared `PaymentList.bookingId`.

Flutter's current payment-list callers still invoke `getPayments()` without
transaction context:

- `application/booking/booking_bloc.dart` (`FetchPayments`)
- `application/checkout/checkout_bloc.dart` (`FetchPayments`)
- `application/wallet/wallet_bloc.dart`
- `application/membership/membership_bloc.dart`
- `application/gift_cart/gift_cart_bloc.dart`

The booking flow must thread its selected service shop ID and
`locationType: 2` into `BookingEvent.fetchPayments`. Product checkout must
thread the authenticated cart ID into `CheckoutEvent.fetchPayments`; if a
single product shop is available, it may also send that shop ID with
`locationType: 1`. Both require changes to the Freezed event declarations and
generated event code, and the relevant screens must supply the context. That
plumbing was left for a coordinated follow-up rather than guessing at
identifiers from display state. Until then, those calls can show the legacy
context-free catalog only; its results must not be treated as authorization to
initiate a transaction. Wallet, membership, and gift-cart screens are
non-shop payment contexts and were intentionally not assigned fabricated
booking/cart ownership.

The API's `meta.payment_context` can additionally expose the authoritative
charge currency and whether the requested display currency equals it. Flutter
does not yet parse or display that metadata; it must not infer or perform FX.