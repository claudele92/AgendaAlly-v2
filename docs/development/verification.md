# Portable foundation verification

## Scope and safety

Verification used the native Laravel, Next.js and React/Ant Design sources.
The unrelated scaffold API server and Canvas are not AgendaAlly interfaces.
There was no production access, database reset, live-provider operation or
publishing. SQLite files, dotenv files, logs and build outputs remain local
and ignored; the reproducible source and templates are the deliverable.

## Engineering results

| Check | Result |
| --- | --- |
| Reviewed Laravel migrations | 213 application migrations plus one separately reviewed ownership marker. |
| Fresh and repeat bootstrap | Passed through the actual guarded command in a disposable SQLite database; repeat bootstrap also passed on the canonical owned development database without resetting or reseeding it. |
| Development suite | 29 tests, 520 assertions passed. Includes relational fixtures, role logins, repeated seeding, unchanged wallet identities/balances and inventory, foreign keys, country restrictions and ranked products. |
| Interrupted seeding | Injected exception after legacy wallet mutations; complete wallet rows, stock rows and fixture counts stayed unchanged; transaction level returned to zero. |
| Existing hardening | 55 tests, 382 assertions passed; one existing deprecation reported. |
| Existing domain baseline | 3 tests, 49 assertions passed in the earlier baseline verification. |
| Admin/CLI Node checks | 23 passed. |
| Customer config/integration Node checks | 9 passed. |
| Customer production compilation | Passed using supported webpack mode, local licensed font and explicit non-development build configuration. |
| Admin production compilation | Passed with explicit staging/HTTPS build configuration, local CAPTCHA bypass disabled and a build-only public site-key fixture. Existing bundle-size warnings remain. |
| Public API connectivity | Real country/language preflights return 204 with the exact customer origin; customer and admin return HTTP 200. |
| Service pagination repair | Real API returns IDs 6, 5, 4, 3, 2, 1, total 6, with at-venue mode; repeated schedule-related service rows are grouped before pagination/count. |
| Top Selling Products | Delivered quantities aggregated by product before pagination; three distinct fixture products rank 5, 3, 2 units. Existing resource/count/filter contracts retained. |

Production builds were compilation checks, **not deployable configured
releases**. Network transports were blocked for these builds; example-invalid
origins and build-only CAPTCHA fixtures are not release credentials.

## Browser evidence

- Customer sign-in, catalog, real product/stock selection and cart were exercised.
- Cash pickup checkout created a single pending/New order in the owned local
  database; no online payment or wallet action was taken.
- The actual business profile and Douala branch were inspected.
- The admin interface, sidebar and dashboard were inspected.
- Browser captures were viewed directly. The general artifact screenshot
  tool cannot target these legacy/native workflow directories; no unrelated
  registered artifact is presented as AgendaAlly.

- Customer booking completed through the real UI: Cameroon → Douala branch →
  Haircut at the venue → Armand Fotso → 16 October 2026, 14:20 → Cash.
  One booking-create request succeeded. Confirmation and My appointments
  both showed booking #3, status New and the matching service, specialist,
  date/time and Cash method. A read-only database check confirmed master 112
  and status `new`. The total was 11,100 FCFA: 9,000 service, 900 commission
  and 1,200 service fee. This is synthetic local cash bookkeeping, not an
  online processor charge or evidence of real cash being collected.

### Remaining non-blocking observations

- The catalog's 9,900 FCFA Haircut price changed to the master's 9,000 FCFA
  service price when the specialist was selected. Explain master-specific
  pricing and included fees more clearly in the approved UX work.
- The specialist card still says “Service location Online” although the
  selected service is correctly at the venue. Reconcile the specialist and
  service-mode presentation during the approved review.
- A cart-not-found response and controlled-input warning did not prevent
  booking creation or the saved appointment view.
- A 390px viewport attempt timed out in the browser runner. Desktop journeys
  passed; a full mobile and multilingual/RTL usability audit is not claimed.
- Practical REST compatibility is preserved; a Flutter build was not run.

## Reproduction and review

Follow [the native setup guide](README.md), then its listed checks. The
development suite creates and removes its own owned temporary SQLite file and
does not use `migrate:fresh` against an application database.

Review [the modernization proposal](modernization-proposal.md) separately.
Stage 1 visual proposals are now approved; their scope and verification are
recorded in [the Stage 1 delivery](stage-1-delivery.md). Broad redesign, a
rewrite, live integrations and publishing remain outside authorization.