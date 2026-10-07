# Bounded invoice, footer/social and PaymentList pass

## Invoice trace and implementation

The native Customer and calendar download path remains
`GET /api/v1/dashboard/user/export/booking/{id}/pdf` →
`Dashboard/User/ExportController` → `OrderRepository::bookingExportPDF()` →
DomPDF `booking-invoice.blade.php` → `invoice.pdf`. Invoice number is the existing
root booking ID, not a newly generated sequence. Existing service/booking dates
remain authoritative; the document also labels its actual generation date.
Product/order and parent-order PDFs use separate methods/templates, unchanged.
Flutter's existing product export contract remains unchanged; no Flutter
booking-invoice consumer was found.

Changed: `.migration-backup/backend/resources/views/booking-invoice.blade.php`
and `app/Repositories/OrderRepository/OrderRepository.php`.

Replaced raw, widely spaced rows with a compact branded header, separate
Business/Client panels, wrapping service details, Specialist presentation,
pricing, payment information, and a restrained copyright/configured legal footer.
Branch address/city/country fallback is preserved. Client name/email/phone
and existing address render only when available. Shop phone is its own contact
field; a private owner email/phone is not substituted for a business contact.
Additional relation fields are read-only data plumbing, not API/schema changes.

The existing approved Stage2Brand wordmark is reused in portable invoice
markup. The old web `public/img/logo.png` is a Shopopopo mark and the old Admin
asset is a green G, not AgendaAlly; neither was falsely used as an official
AgendaAlly image. No new logo artwork was generated. A separate official
AgendaAlly raster asset was not found.

Shop logos resolve from existing public-disk raster files only. Path traversal,
unsupported formats and missing files fail closed; images embed as data URIs,
not remote fetches. Missing/unavailable logo renders a neutral SHOP placeholder.
Existing checked bookings have no resolvable local Vendor logo, so live
logo-present document coverage is not exercised.

The original stored rate_discount, rate_service_fee, rate_extra_price,
rate_coupon_price and rate_total_price values, number formatting and booking
currency symbol/position remain unchanged. Nonzero existing rate_gift_cart_price
is displayed without inventing or calculating a new value. No subtotal, tax,
commission, conversion, rounding, transaction or status calculation changed.
Payment and booking statuses remain distinct: invoice #4 still displays Cash,
Progress, New and total 7,200.00 FCFA.

All four existing root invoices generated successfully as one-page A4 PDFs.
Old/new templates were compared against their authoritative pricing fields;
booking and transaction rows were identical before/after rendering.
Neutral-logo fallback and traversal/missing-image handling were checked.
Visual inspection corrected a DomPDF issue where an `html { margin: 0 }` reset
overrode page margins. Print boundaries now retain 13/14/15mm margins and
readable point-size detail typography.

## Footer architecture and exact seed

Native Footer reads the original `settings` key/value table through existing
uncached public settings/layout requests. Admin Social settings uses its existing
settings update endpoint. No parallel CMS/schema was introduced.

Changed: native web `components/footer/footer.tsx`, `footer.css`,
`utils/footer-settings.ts`; native Admin `src/views/settings/socialSettings.jsx`;
guarded `DevelopmentPreviewContentSeeder`, `SeedDevelopmentData`, and the
existing development launcher.

Only these settings are eligible for the explicit footer-only update:
description, footer_text, instagram, facebook, linkedin, twitter, tiktok.
Configured official defaults:

- Instagram: https://www.instagram.com/agendaally
- Facebook: https://www.facebook.com/AgendaAlly/
- LinkedIn: https://www.linkedin.com/company/agendaally
- TikTok: blank; optional Admin field, hidden publicly until a valid URL exists.
- Twitter/X: no invented default; known old development handle is cleared,
  while an independently configured valid destination remains editable/renderable.

Blank/unconfigured social values and known owned old defaults receive supplied
official destinations. Nonblank custom destinations/copy are preserved.
Recognized old development/template copy is replaced with non-claiming marketplace
copy. Current-year copyright retains customized footer text. Approved social
destinations are no longer removed by the older content cleanup.

Follow Us uses existing installed Remix Icon font glyphs, accessible labels,
monochrome styling and safe external-link attributes. The older React icon
wrapper does not export TikTok; no new package was installed. Existing native
Discover, Your Account, Information and legal routes remain. The preferred
marketplace tagline is used, persisted location is displayed without changing its
behavior, and app badges remain hidden without valid configured store URLs.

Reproduce only this intended configuration:

```sh
node scripts/development.mjs seed --footer-only
```

`php scripts/development/verify-footer-only.php` executed the guarded update
twice: the second run was row-level identical and no duplicates were created.
Fingerprints of 198 tables, including all rows outside the seven allowed settings,
were unchanged. No booking, transaction, wallet, payment, marketplace, user,
other CMS or app-store/contact configuration was seeded.

## PaymentList root cause and correction

BookingProvider omits optional `payment` from its initial state.
BookingPaymentList forwards `state.payment`, and its wallet/price reset may
dispatch SetPayment(undefined). MembershipPayment and GiftCartPayment also
initialize optional Payment state without a selected value. Selecting a method
later sends a Payment object, causing Headless UI RadioGroup to switch from
uncontrolled undefined to controlled.

Changed only native `components/payment-list/payment-list.tsx`:
normalize absence to controlled null at the RadioGroup boundary, preserve the
optional Payment/onChange contract, and compare choices by payment ID across
fresh response objects. No defaultValue or automatic payment selection added.
The owner still receives undefined when a choice is cleared.

Selections outside current results or the caller's filter are cleared, including
zero methods, query failure and invalid eligibility context. Changing shop,
location type, cart, booking or currency clears old selection while replacement
results load. A same-context refresh retains an eligible choice. Country eligibility
continues to come from the existing server-owned shop/cart/booking context,
not a new browser-side country/provider fallback.

Shared consumers: booking, gift card, membership. Cart has its own different
RadioGroup component, untouched. The platform-consumer optional/filter contract
was checked through the real shared component's deterministic hook boundary.
No payment initiation, provider, accounting, wallet or backend resolver changed.

## Focused verification

- Native Web semantic TypeScript passed.
- Admin Social JSX parsing passed.
- Affected PHP and development-launcher syntax passed.
- Nine focused PaymentList/footer regressions passed (loading, no default,
  selection/clear, zero/error/invalid/filtered methods, context change, stable
  refetch, platform consumer and safe footer destinations).
- Actual guarded footer seed/fingerprint and invoice render checks passed.
- An extra disposable ContentTest regression timed out in fixture preparation,
  before its test body; it is not claimed as passed. No broad payment suite was
  deliberately run.
- Native Customer, Admin/Vendor and Laravel workflows were restarted together
  after implementation and came up cleanly. Existing framework warnings remain.
- The resumed bounded native browser pass confirmed desktop 1280px and mobile
  390px footer links, correct destinations, icon glyphs, tagline, location,
  hidden unconfigured platforms/app badges, and no horizontal overflow.
  It caught inherited white wordmark text on ivory; a more-specific footer-only
  text/heading rule corrects that legacy dark-footer override. The correction
  was checked against stylesheet specificity, without another broad browser run.
- The existing Customer appointment #4 native Download invoice control succeeded
  and downloaded `booking-invoice-4.pdf`, retaining Cash/New/7200 FCFA in its UI.
- The exact existing booking/payment page returns HTTP 404, independently
  reproduced with a read-only local request. Browser loading/radio selection
  and warning disappearance are therefore not claimed as exercised. No booking
  creation, status mutation, provider/payment, order or wallet action was used
  to work around that unrelated route failure.
- The tester exercised the separate mobile header “Open navigation” control
  instead of a footer heading. It reported an expanded-state/panel inconsistency
  there. Header navigation was not changed or investigated in this bounded
  pass; footer disclosure behavior is supported by the native Headless UI
  markup, not claimed as independently keyboard-tested.
- The first tester attempt returned a platform infrastructure error. Only that
  same bounded assignment was resumed; no second broad acceptance suite or new
  application fixtures were created.