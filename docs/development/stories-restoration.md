# Focused native Stories restoration

This completes the existing implementation, not another architecture audit.

## Restoration

The native seller menu, routes and pages survived Stage 2. The effective
navigation filter omitted `stories.view/manage` and rejected Stories in the
native Content group. Stories is now exposed under Growth using verified shop
permissions. Read-only staff cannot use management actions; no staff grants
were assigned automatically.

The existing Vendor list/form now shows associations, actual media, active
state and server-provided expiry, with responsive form columns. Story uploads
use the seller Story endpoint rather than the unrelated shared gallery.
Editing retains every existing image unless explicitly removed.

Existing seller shop-bound security remains authoritative for all operations,
including related Product/Service ownership and safe namespaced media. Admin
management remains separate.

## Public lifecycle

A Story is public only when its shop is approved, it is active, and its
creation time is strictly newer than now minus 24 hours. At exactly 24 hours
it is expired. Editing/reactivation does not extend its lifetime. No historical
timestamps or schema were rewritten.

The hourly scheduled expiry command uses the same model cutoff and normal
Story service deletion, including reference-aware local media cleanup.
Legacy shared-directory media is deliberately retained when its historical
owner cannot safely be proven. Unused uploads never attached to a Story are
not covered by expiry cleanup.

Management responses add `active`, `expires_at` and `expired`. Existing
public grouped responses and Flutter fields are unchanged.

## Development and presentation

See [development fixture provenance](../development-story-fixtures.md).
Three active Stories belong to existing approved synthetic shop 501, one each
for an owned Shop, Product and Service. Reviewed generated Stage 2 illustrations
are local fixture media, not documentary Vendor photography or product claims.

Reproduce visible fixtures explicitly with:

```sh
node scripts/development.mjs seed --stories-only
```

Only exact unchanged fixture rows have creation time refreshed. Normal
production Stories receive no expiry exemption. Edited/conflicting rows are
preserved, and unrelated tables are not seeded.

The storefront now selects the approved rectangular Story-card variant, not
the legacy gradient-ring variant. Business updates remain secondary between
discovery and categories. The horizontal rail has contained overflow and
labelled browsing controls when multiple businesses are present. Native
viewer destinations and keyboard/reduced-motion behavior are retained.

The development-only Next image allowance now checks the configured media
origin rather than the API origin. This matters after same-origin API routing:
local Story uploads still use the backend media origin. Production private-IP
protection and constrained image paths remain unchanged. An actual optimized
fixture request returned HTTP 200 with JPEG bytes.

## Focused checks

- Story security/lifecycle suite: 10 tests, 58 assertions passed; one existing
  PHPUnit deprecation notice.
- Guarded disposable-development fixture test: 1 test, 32 assertions passed,
  including repeat seeding and unchanged non-Story table fingerprints.
- Stories navigation regression: 1 test passed.
- Changed Vendor JSX parsing, PHP syntax and Web semantic TypeScript check passed.
- Accepted local before/after: Stories 0 → 3; users 38, bookings 3,
  transactions 14, wallets 36, payouts 1, wallet total 100 unchanged.

## Bounded browser results

The same native tester verified Vendor desktop list/navigation, all three
seeded rows, Add/Edit forms and shop-owned choices. At 390px the list uses
table-local horizontal scrolling; list and forms have no document overflow.
No Story data or uploads were changed.

Customer desktop/390px verification confirmed secondary placement, actual
Story images and business identity, keyboard opening, Escape close and the
three supported destination pages. The mobile viewer's document width stayed
390px. The Service destination reached the existing branch-selection step;
no booking or purchasing action was taken. Inactive/expired exclusion was
verified with isolated automated fixtures, not accepted-record manipulation.

The first customer check was blocked by the normal first-visit location
dialog. Its continuation used a fresh empty browser context and selected
existing synthetic Cameroon/Douala, without changing an existing cart.

The browser caught local-image optimizer 400s before the development media
guard correction. Both the viewer-sized and exact rail-sized optimized
requests subsequently returned HTTP 200 JPEGs. No Story JavaScript runtime
failure was observed; existing framework/deprecation/image-sizing warnings
remain. The viewer's labelled Previous control was present, but a backward
navigation click was not independently completed before timed playback closed
the viewer. No Flutter-device verification was performed.

Customer, Admin/Vendor and Laravel previews remain running. Accepted database
counts and wallet totals remain unchanged apart from the three Story fixtures.