# Customer Shop hours and favicon correction

Date: 2026-10-02, America/Chicago.

Scope: approved hydration/favicon correction, followed by read-only payment
audit. The latter stopped at a P0; see `payment-system-full-audit.md`.
No Shop redesign, additional media pass or payment implementation.

## 1. Exact hydration cause

The Shop profile's `TopInfo` and sidebar `MainInfo` independently derived
weekday/time during rendering from each machine's local clock. Native Node
used UTC; the verification browser used America/Chicago. They could choose
different calendar days and business status for the same server-rendered page.
The legacy helper also failed to enforce the opening boundary and compared
holiday values incorrectly, so simply removing the hydration warning would
not make “Open” semantically correct.

Wouri's error was reproduced before correction. The first corrected browser
pass exposed the second legacy calculation in `MainInfo`; that consumer was
corrected too.

## 2. Authoritative Shop-local clock and deterministic rendering

- Added read-only `hours_timezone` metadata. The selected native branch's
  country code is used; otherwise all loaded branch countries must be
  unambiguous. PHP's IANA country identifiers supply a zone only when there
  is exactly one. Cameroon resolves to `Africa/Douala`.
- Missing geography, mixed countries and multi-zone countries yield `null`,
  not server/browser timezone or a fabricated default.
- Native Service/Shop hours do not have a dedicated timezone column. Product
  Pickup's separate configured timezone was deliberately not repurposed.
- The Shop server page serializes one timestamp into both indicators. SSR and
  the first client render calculate from that identical timestamp and zone.
- After mount the shared hook refreshes immediately, every 15 seconds and on
  window focus. Other shared `MainInfo` consumers lacking a server snapshot
  initially render deterministic “Checking business hours.”
- The pure helper implements opening-inclusive/closing-exclusive boundaries,
  shop-local holidays, manual closure, disabled days, overnight carryover and
  explicit unknown/malformed schedule states.
- No `suppressHydrationWarning`, disabled SSR, hardcoded Open/Closed, schema
  change, data mutation or booking-action change.

## 3. Favicon source findings and correction

Customer metadata already consumed the editable `favicon` setting.
Admin/Vendor consumed `admin_favicon` through their separate React bootstrap.
No overriding Next convention `favicon.ico`, `icon.*` or manifest source was
found in the native Customer app.

The approved uploaded compact symbol, Customer mark, Admin mark and served
Admin favicon had identical bytes:

`0e2bbcff4732e05710344437e647e2b258c92b5b193b66cc98db1f55226eace7`

**There is no demonstrated Customer-to-Admin field leakage in this checkout.**
Consequently the reported Admin-image appearance cannot honestly be
attributed to a wrong settings field. Reuse of the previous icon URL by
browser caches is a plausible explanation, not a proven historical cause.

The Customer resolver now maps only the owned AgendaAlly compact mark to
the immutable URL `/brand/agendaally-mark.0e2bbcff.png`, containing those
exact approved bytes. Custom editable favicon values remain unchanged.
Header/auth mark URLs and all Admin/Vendor source/settings/assets are
unchanged.

No optical crop or rescaling was applied; no wordmark was substituted.
The separate planned 16/32-pixel optical-padding inspection was not completed
before the payment P0 stop, so this report does not certify that optical
measurement.

## 4. Focused verification performed before the payment stop

- Nine Node Shop-hours/favicon regression tests passed.
- PHP country/timezone test passed, six assertions; one PHPUnit deprecation.
- PHP/syntax checks passed.
- Full native Customer `tsc --noEmit` completed without diagnostics. No full
  production build was required or claimed.
- Browser America/Chicago: Wouri hard reload, second hard reload, native
  Next-Link navigation away/history back, Le Sawa and Learning Hub all
  completed without hydration errors or console errors.
- At approximately 03:39 UTC / 04:39 Africa/Douala, the inspected
  09:00–18:00 schedule correctly displayed
  “Closed based on shop hours · 09:00–18:00.”
- Server metadata returned HTTP 200 with the content-versioned icon on `/`,
  `/shops`, `/masters`, Wouri's Shop profile, `/products`, Wouri's booking
  route, `/cart` and `/login`.
- `/payment` returned 404; it is not claimed as checkout coverage. No booking,
  order or payment was submitted.
- Browser captures disabled cache and verified the actual document icon link.
  This confirms the new source URL, not a reproduction of the creator's old
  browser cache contents or an OS/tab-strip screenshot.
- Actual Customer/Admin icon byte checks matched the approved symbol.
  Vendor shares Admin's favicon bootstrap; no separate signed-in Vendor
  tab-icon observation was performed.

Evidence: `.local/hours-favicon-browser.json`, `.local/favicon-routes.json`
and `reports/customer-hours-favicon/{before,wouri-hard-reload,le-sawa,learning-hub}.jpg`.

Customer/Laravel previews were restarted once after the coherent changes and
are running; Admin/Vendor is running and was not modified. The owned temporary
verification Chromium was stopped. Existing image-size/dev-framework warnings
remain. The separate configured broad hardening workflow is not green
(Delivery Driver and Pickup fixtures); see the payment stop report. No claim
of a green whole-project suite is made.

## 5. Exact application/test files changed

Backend paths under `.migration-backup/backend/`:

- `app/Http/Resources/ShopResource.php`
- `app/Support/ShopHoursTimezone.php` (new)
- `tests/Hardening/ShopHoursTimezoneTest.php` (new)

Customer paths under `.migration-backup/web/`:

- `app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/(detail)/page.tsx`
- `app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/top-info/top-info.tsx`
- `app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/main-info/main-info.tsx`
- `app/layout.tsx`
- `types/shop.ts`
- `utils/agendaally-brand-assets.ts`
- `hook/use-shop-hours.ts` (new)
- `utils/shop-hours.cjs` (new)
- `utils/shop-hours.d.cts` (new)
- `utils/shop-hours.test.cjs` (new)
- `utils/storefront-favicon.test.cjs` (new)
- `public/brand/agendaally-mark.0e2bbcff.png` (new, byte-identical approved mark)

No payment application file was changed. Additional files are these reports,
verification receipts/screenshots and project approval-memory maintenance.