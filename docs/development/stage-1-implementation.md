# AgendaAlly — native Stage 1 integration

## Approved scope

The accepted visual system is integrated into native Customer
login/registration, Admin/Vendor login, customer navigation and permission-aware
business navigation. Laravel, Next.js and React/Ant Design remain the native
frameworks. Booking, checkout, product, finance and other major content screens
are not redesigned.

Authentication continues using existing form/API/session behavior. There is
no new identity provider, prototype submission handler, role-picker login or
impersonation feature. Configured optional providers and local-only development
verification exceptions retain their existing boundaries.

Static auth artwork and local Inter fonts are application assets. Shared
semantic tokens are scoped to authentication/navigation; they do not change
the visual treatment of major transaction screens.

## Authorization and context

The old client role menus were presentation lists, not effective grants. The
new navigation derives visibility from the authenticated actor's server-resolved
country/shop permission keys, structural ownership and Super Admin status.
Original Laravel/Spatie permission enforcement remains authoritative.

An additive authenticated read-only endpoint is provided:

`GET /api/v1/dashboard/user/navigation-context`

It uses the existing API envelope and exposes the current actor's role,
country/shop scope, effective permission keys and in-scope branch locations.
It does not change existing login/profile resource fields or REST contracts.
Client country, shop or branch parameters cannot select broader context.
Pending/revoked memberships do not grant navigation scope; unresolved or
ambiguous scope fails closed.

Navigation retains original routes, nested capabilities and feature switches.
Country administrators remain country-scoped; invited vendor staff retain
their shop grants and branch assignments. Masters/specialists retain their
personal scope and do not receive shop-owner bypass merely by association.

There was no global active-branch state shared by the native business modules.
Consequently the navigation exposes verified all/assigned/unassigned branch
scope rather than introducing a branch switcher ignored by existing APIs.
Existing module-specific branch selections remain unchanged. Country source
distinguishes an authorization restriction from a business's geography.

Shared formatting startup uses the existing public currency catalogue, not
the protected currency-management endpoint. Private shop reads wait for
matched SELF scope and the actual vendor/shop-settings read grant or
structural ownership. Dashboard mounting and drill-down links use verified
grants as well: a report grant does not imply orders/bookings list access.
Shop managers never fall through to admin statistics; unsupported booking
statistics cards are not mounted for that role. Currency requests/data follow
the actor/session boundary, retain same-session profile updates, and reject
obsolete completions after account changes or logout.

The country/city selector fetches cities using the temporary selected country
before Save, but cannot issue an unscoped query when that prerequisite is
missing. Saving remains an explicit browser-local marketplace preference
operation, not customer delivery-address creation.

The normal business startup no longer probes installer endpoints. Legacy
welcome/installation URLs enter normal authentication. Invalid runtime
configuration fails safely; public backend installer endpoints stay disabled.

## Verification

Production compiler
checks use current-source disposable snapshots, explicit public build-only
settings and blocked network transports. They do not access production,
verify real providers or produce deployment-ready credentials.

Run compilation only after pausing preview/browser services:

```sh
node scripts/development/verify-stage1-builds.mjs all
```

The helper excludes dotenv files and inherited secrets, retains restored
locked dependencies, runs customer then business compilation sequentially,
and removes its owned temporary source/build snapshots. Logs are written to
the ignored `.local/development/logs/` directory.

Existing security/domain/development tests use their isolated bootstraps,
not destructive historical migrations or the default Laravel Feature suite.
The real-browser acceptance pass covers representative session/role/context
behavior and responsive authentication/navigation. Registration and provider
behavior are never represented as successful if disabled configuration blocks
the real operation.

### Native browser acceptance — 2026-10-01

The historical checks below are not the creator's fresh final acceptance.
The authoritative current record is
[`stage-1-external-acceptance.md`](stage-1-external-acceptance.md).
Stage 1 remains **not accepted** while its recorded browser/runtime gaps
remain open; Stage 2 must not start.

All sign-ins below used native forms and real existing Laravel authentication.
No role-picker login, API-assisted sign-in, impersonation or mocked auth was
used. Business journeys were continued only where earlier genuine blockers
prevented verification; successful full flows were not repeatedly rerun.

| Actor | Verified behavior |
| --- | --- |
| Platform Super Admin | Real login, authenticated SELF scope, Country admins route, UI logout/confirmation. |
| Country manager | Known restricted Cameroon scope, no assigned branches, no global Country admins/payment navigation; permitted Bookings reads and UI logout. |
| Vendor owner | Verified shop and all-branches scope, product list, navigation search/clear and collapse; 390px drawer routing, backdrop, Escape and focus restoration; UI logout. |
| Invited shop staff | `shop_manager`, assigned Douala locations, permitted seller Calendar reads with no admin-statistics fallback; stable rendered identity/context after reload; UI logout; mobile drawer. A fresh SELF network request was not captured during that reload, so only the rendered reload result is claimed. |
| Specialist/master | Personal-work navigation without owner, finance, product or staff controls; personal Calendar reads and UI logout. |
| Country finance | Restricted Cameroon context, permitted Transactions read and UI logout. Four legacy unscoped finance links were subsequently excluded and covered by source/permission tests; this is not backend hardening. |
| Customer | Explicit Cameroon/Douala selection, real login, session/location after reload, services, products, appointments, orders, cart, account and favorites destinations; UI logout. |

Customer desktop/mobile login and mobile registration validation were checked.
The real registration contact submission returned 200 and advanced to email
verification. No OTP was read/entered, no resend or fake provider was used,
and verification/account completion and real delivery are **not** claimed.
A pending pre-verification probe contact may remain in the owned development
database. Customer phone validation was checked, not a completed phone/SMS
signup. No booking, order, payment, role or permission writes were performed.

The 390px customer public drawer closes through Close/Escape and restores
focus to its trigger; it excludes account/logout entries while signed out.
The account drawer's Escape focus return was corrected after the observed
minor inconsistency and checked by TypeScript/source inspection, not a new
browser pass. Captured native API paths contained no installer requests.
Viewport responsiveness was checked; hardware touch, complete accessibility
and RTL audits were not performed.

Managed native screenshot references (these are not Canvas screenshots):
business login `5niobe`; owner navigation `ns6fnk`/`00kxvh`; country Bookings
`szxw9z`; staff scope `8vg1yc`/`ned6bv`; finance Transactions `iqchls`;
customer login `mkghxj`/`d1ti65`; customer reload/context `785149`;
customer mobile navigation `fp8xtx`; registration validation `973z0p`;
verification boundary `mlrdev`. The browser did not provide filesystem exports.

### Automated checks and compilation

- Laravel hardening: **55 tests / 382 assertions passed**.
- Booking/product domain regression: **3 tests / 49 assertions passed**;
  existing warning/deprecation output remains.
- Current Laravel development suite: **29 tests / 628 assertions passed**.
- Native client/configuration/navigation/session/query-gate checks:
  **86 tests passed**.
- Both final current-source offline production compilations passed:
  **native customer (Next.js/Webpack)** and **native business (Vite)**.
  Disposable snapshots contained no dotenv files or inherited secrets, used
  build-only public settings and blocked network transports, and were removed
  afterward. Build logs are `.local/development/logs/stage1-web-production-build.log`
  and `.local/development/logs/stage1-admin-production-build.log`.
- Builds ran only after browser contexts were closed and native previews were
  paused. Laravel, customer, business and Canvas previews were restored
  sequentially afterward; the unrelated scaffold API remains stopped.
- Customer TypeScript, changed native JSX parsing and targeted formatting
  checks passed. No deployment or live-provider validation is implied.

### Known legacy-content limits and separate follow-ups

Native seller/master Calendar add forms logged an undefined `id` error for
staff/specialists although calendar reads rendered. Appointment creation and
editing were not exercised and are not certified by Stage 1. A separate
functional repair is proposed; it must not invent owner scope or grant private
shop-settings access.

Payout requests, payouts, subscriptions and shop-subscription legacy reads
need a separate backend country-scope audit/hardening pass. Restricted
navigation omits them because their reviewed read chains lacked a country
permission gate/scoping; hiding links does not enforce API authorization.
Distinguish shared public plan metadata from sensitive tenant finance records
when performing that work.

Other observed existing-content/dev warnings include Firebase-disabled
notifications, Redux Date serialization, customer appointments nested-button
hydration, empty-cart 404/422 responses despite a rendered empty state, and
Next.js development HMR WebSocket errors. These are not claims of successful
transactional edits, complete commerce testing or release readiness.

## Next boundary

Stop for visual review of native Stage 1 before Stage 2. This integration
does not authorize production operations, publishing, provider changes,
permission expansion or broader transaction-screen redesign.