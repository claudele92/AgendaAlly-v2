# Final calendar browser acceptance — 2026-10-05

This report supersedes only the remaining calendar browser acceptance claims.
Prior native/API, financial, authorization and recovery evidence was not rerun.
No application source was changed, booking confirmed, provider activated or
production deployment performed.

## New acceptance results

| Requested result | Outcome |
|---|---|
| Add Booking interactions | **FAIL** — existing client and Cash visibly selected, but service submission rejects them before calculation/Review. Independent local-client draft reaches authoritative unpaid Review and cancels safely. |
| New Local Client | **PASS for exercised paths** — clear directory/not-platform-account explanation, required Name, optional contacts, long input containment, distinct Save, visible mobile validation and pre/post-save abandonment. Contact matching and same-name authority retain accepted native evidence, not new browser certification. |
| Client-save retry/recovery UI | **PARTIAL** — completed upstream save with deliberately dropped acknowledgement; same UUID retry returns the same client and attaches once. Refresh/GET receipt restoration and changed-details conflict were not independently exercised. |
| Saved-booking detail | **PARTIAL** — exact saved GETs #8/#7, matching fields, separate collection/status, loading, close/reopen/switch and 404/network Retry without calculation/mutation. Injected 403 redirects to login instead of usable detail error handling. |
| 1280px | **PARTIAL** — Week, controls, details and local-client Review exercised; existing-client/Cash Review blocked. |
| 390px | **PARTIAL** — Agenda/Day/Week, appointment detail, Add Booking entry and local-client validation exercised; full mobile Service-to-Review interaction not completed. |
| 320px | **PARTIAL** — same bounded mobile interactions, readable saved details and contained modal/errors; full mobile Service-to-Review interaction not completed. |
| Mobile Agenda-first | **PASS** at both widths with fresh calendar state. |
| Contained optional Week | **PASS** — internal grid scrolling, no document/body horizontal overflow. |
| Menu closure | **PASS** — selecting Week closes More. |
| Chat/help overlap | **PARTIAL** — calendar final appointment/Add Booking/chat are separated; overlap across every composer/Review state and reduced-height focused-input state was not certified. |
| Vendor Specialist selector | **PARTIAL** — Shop-scoped operational endpoint and accepted Specialists 102/105 observed, including shared Specialist105. No new foreign/revoked browser fixtures were introduced; native authorization evidence remains accepted. |
| Harness interruption | No fatal browser interruption. Locator/filesystem issues recovered. Private staging session used; invalid CAPTCHA site key is an existing external login limitation, not new login acceptance. |
| Application defects | Existing-client/Cash value-shape validation blocker; injected403 redirects to login. Minor issue: mobile slot-selection instruction is hidden despite usable slot selection. |
| Final preservation | **PASS** — 59 fingerprints, 465 original schema objects, approved additive ledger only, 12 untouched/unverified Orders, normal API and 9/51/75/14 counts, retained native operations. Both normal previews return200; sampled normal Shop media returns image/jpeg200. |
| Calendar browser acceptance CLOSED | **NO**. |
| U1/calendar responsive VERIFIED | **NO** — partial responsive and retry coverage plus a blocking Add Booking defect. Physical-device keyboard behavior was not tested. |
| Same twenty-gate readiness score | **15 / 20 = 75%**. Existing baseline15.5 is reduced by0.5 because the new Add Booking blocker contradicts full V3 Calendar lifecycle verification. V3 becomes PARTIAL; O6 stays PARTIAL0.5. All other gate credits remain unchanged. |
| Decision | **REMAIN IN STAGING**; not ready for the next staging gate. |

## Evidence and cleanup

- Browser evidence: `.local/staging-mvp/calendar-final-browser.json`.
- Exact guarded disposable fixture cleanup:
  `.local/staging-mvp/calendar-browser-cleanup.json`. One local client and its
  receipt removed; no linked booking existed. Isolated counts restored to two
  directory clients, zero save receipts, seven bookings,26 users and four wallets.
- Final comparison: `.local/staging-mvp/final-preservation.json`.
- Screenshots: blocker `3rv0tp`; unpaid Review `zhj918`; mobile detail `8l7x6m`,
  `cj2b5d`; visible320 validation `ph4g34`; final Agenda controls `egifvu`;
  synthetic error states `07m842`, `nt5cg8`, `vz4af7`.
- Synthetic responses/dropped acknowledgements are explicitly labelled as test
  injections, not genuine backend authorization or availability failures.
- No new P0 security or financial corruption was observed. Cash/Wallet/native
  accounting acceptance was not reopened or changed. No booking-create,
  payment/provider request or platform-account creation was performed.

No additional feature, broad campaign or production action was started.

## Superseding bounded correction — 2026-10-05

See [the calendar closure report](agendaally-calendar-acceptance-closure.md)
for the current nineteen requested results. The Cash blocker is corrected,
organic lost-ack refresh and scoped Specialist checks passed, and isolated
fixtures were restored. The authorized responsive-only follow-up retained 67
current states without application edits or record creation. Current same-model
score is **16/20 (80%)**: V3 remains 1 and O6 moves to 1. Calendar acceptance is
**CLOSED** and U1/calendar responsive **VERIFIED** at 1280/390/320 browser
viewports. Overall **REMAIN IN STAGING**; no broader campaign, production action
or physical-device certification was performed. Original failed findings above
are historical, not a claim that the corrected defects still occur.
