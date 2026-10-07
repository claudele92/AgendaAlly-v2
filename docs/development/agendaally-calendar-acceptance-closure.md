# Calendar acceptance closure — 2026-10-05

This is the current calendar conclusion, superseding the earlier browser-only
report and incomplete responsive conclusion. **Functional Cash blocker corrected;
Calendar acceptance CLOSED; U1/calendar responsive VERIFIED at browser viewports
1280/390/320. Same twenty gates: 16/20 = 80%. REMAIN IN STAGING.**

The authorized responsive-evidence-only follow-up retained 67 states, screenshots
and settled geometry using existing records and unsubmitted forms. No application
code changed, no new business/financial record was created, and no previously
passing scheduling, concurrency, idempotency or security campaign was repeated.

## Requested nineteen results

| # | Finding | Result |
|---|---|---|
| 1 | Existing-client/Cash root cause | Two defects: Ant Design Select returned a scalar while validation/serialization expected `payment_id.value`; its Cash option also hard-coded payment ID 1 although the isolated catalog's Cash ID is 101. Correcting the shape exposed the second defect as native HTTP 422, “The selected payment is invalid.” |
| 2 | Exact correction | Use `labelInValue`; resolve the unique active Cash entry from existing `rest/payments`; validate against that current ID; show an explicit unavailable state rather than inventing an ID. Cash-only options remain. Catalog discovery does not grant checkout authorization; native backend validation remains authoritative. |
| 3 | Existing-client Add Booking | PASS functional. Registered Customer 101 remained selected; calculation and one explicitly confirmed create returned 200. Booking 20 persisted for Shop 101, Service 101, assignment 102/Specialist 105, October 12, 08:00–09:00, price/total 100. No client was created for this path and no booking-create request occurred before confirmation. Native cancellation returned 200. |
| 4 | Cash Review/collection | PASS. Prior functional acceptance retained UNPAID/UNCOLLECTED and Cash 101; transaction 1505 was Cash/progress, without provider reference and unverified, not settlement. The responsive follow-up retained the same unsubmitted calculated draft at all three widths, Customer 101/Cash selected, total 100, visible “No payment recorded” and “Pending — no collection.” No accounting, Cash, Wallet or provider semantics changed. |
| 5 | Lost-response retry | PASS. Client 35 committed once; a deliberately dropped acknowledgement followed by same-reference retry returned that same ID. Exactly one corresponding client/receipt existed. No name-based identity inference or new implementation. |
| 6 | Concurrent retry | PASS: two overlapping native MySQL repeatable-read workers returned the same ID; exactly one client and one durable receipt. Restore/replay reused the result; synthetic rows removed. Six native checks passed. |
| 7 | Changed details/same reference | PASS: browser changed-name request returned 409; native changed-contact and changed-branch cases also rejected reuse. |
| 8 | Refresh receipt restoration | PASS organically before retry. A second isolated save committed client 36, then its acknowledgement was dropped. Reload automatically fetched the authorized receipt (200/resolved), cleared the opaque UUID key and made no second POST. The earlier after-success manually resumed UUID check is explicitly not organic recovery evidence. |
| 9 | Cross-actor/Shop isolation | PASS native: foreign actor/Shop could not recover the owner receipt; foreign branch rejected before insert/receipt creation. Browser cross-actor login was not repeated. Fifteen focused native regression checks passed, including cleanup and same-name/new-intent independence. |
| 10 | Saved-detail 403 | PASS. Object 403 retained valid authentication, cleared detail content, showed a generic local denial without injected foreign/sentinel data and allowed Retry to real GET 200. No detail calculation occurred. Only saved-detail GET opts into this policy; 401 still clears authentication and other 403 behavior is unchanged. Two auth-policy unit checks passed. |
| 11 | 1280 | PASS responsive. Retained current Agenda/Day/Week navigation, native date picker, selected Service/Specialist/date/time, calculated draft top/bottom and pinned footer, empty-Name local-client validation, existing booking 7 GET 200 detail drawer, help placement and settled bounds. No outer horizontal overflow. Prior functional create/cancel evidence retained, not repeated. |
| 12 | 390 | PASS responsive. Fresh mobile opens Agenda; Day forward/back, full-year date/native picker, Week scroll to Saturday within its container, Service/Specialist/date/time controls and real availability, same calculated draft/total/footer, local Name validation and booking 7 GET 200 details retained. Help/footer reachable without collision; no outer horizontal overflow. No confirmation or client save. |
| 13 | 320 | PASS responsive. Fresh Agenda-first, Day forward/back, contained Week scroll, visible slot instruction, full-year date and date/time dropdowns, existing Customer/Cash draft through calculation, totals/footer, local validation and saved GET 200 details retained. Native scrolling fully reveals cards, controls and help. Cancel retains selections then closes without creation; no outer horizontal overflow. |
| 14 | Specialist selector | PASS requested Shop 101 scope. Accepted shared Specialist 102 and Specialist 105 appeared initially. Temporarily withdrawing only Shop 101 invitation 101 removed 102 while 105 remained; 102's Shop 102 acceptance/assignment stayed intact. The exact invitation change was restored. A separate Shop 102 selector journey was not required or certified. Existing native authorization evidence retained. |
| 15 | Protected state | PASS rechecked: 59/59 protected fingerprints, 465/465 original schema objects, 12 untouched/unverified legacy Orders, normal API 200, 9 Shops/51 Services/75 assignments/14 Specialists, retained native operations unchanged and three sampled images 200. All 206 isolated table counts and native schema unchanged; 205 table fingerprints identical. Only the authentication-token table fingerprint changed during native token-use timestamp updates; existing 72 tokens remain 72. No new acceptance records or cleanup needed; 1811 application source files unchanged. |
| 16 | Calendar browser acceptance CLOSED? | **YES — bounded Calendar acceptance CLOSED.** Previously accepted functional evidence plus retained current responsive proof close this gate; no new booking was required. |
| 17 | U1/calendar responsive VERIFIED? | **YES — browser viewports 1280/390/320.** This does not certify physical-device keyboards, Maps setup, all-product responsive surfaces or production runtime. |
| 18 | Same twenty-gate score | **16/20 = 80%.** O6 moves from 0.5 to 1 for retained responsive/error-surface acceptance. V3 remains 1; all other gate credits unchanged. No points for appearance, compilation or repeated evidence. |
| 19 | GO/NO-GO | **GO for bounded Calendar/U1 responsive closure; REMAIN IN STAGING for the overall MVP.** No production deployment, provider activation or broader financial/recovery/infrastructure certification. |

## Same twenty-gate accounting

| Gate | Credit | Current basis |
|---|---:|---|
| C1 Customer access/reset | 0.5 | Unchanged partial; live email absent |
| C2 Discovery/location/detail | 1 | Retained |
| C3 Exclusive booking lifecycle | 1 | Retained B1; not reopened |
| C4 Native payment | 1 | Retained Cash/Wallet only |
| C5 Visible history/receipt | 1 | Retained |
| C6 Responsive error/retry | 0.5 | Unchanged partial |
| V1 Onboarding/Shop | 1 | Retained |
| V2 Service/staff setup | 1 | Retained |
| V3 Calendar lifecycle | 1 | Cash validation blocker corrected; explicit create, persisted record and cancel accepted |
| V4 Financial history/operations | 1 | Retained, not external payout |
| A1 Admin oversight | 1 | Retained |
| A2 Financial authorization | 1 | Retained |
| A3 UNKNOWN/provider intervention | 0.5 | Unchanged partial |
| A4 Refund/cancellation operations | 0.5 | Unchanged partial; no electronic refund certification |
| O1 MySQL DDL/financial integrity | 1 | Retained |
| O2 Production runtime/TLS/cutover | 0.5 | Unchanged local-only partial |
| O3 Restore | 0.5 | Unchanged off-host/key-custody limitations |
| O4 Communications/background reliability | 0.5 | Unchanged partial |
| O5 Monitoring/recovery | 0.5 | Unchanged local-only partial |
| O6 Responsive/error surfaces | 1 | Final current Calendar responsive proof retained; prior accepted error evidence not repeated |
| **Total** | **16** | **80%** |

## Evidence interpretation and boundaries

- Initial corrected build exposed invalid hard-coded Cash ID; final source was
  successfully production-built in `calendar-acceptance-closure-ready`.
  Unchanged customer source hashes were rechecked before reusing its build.
  An interrupted size-reporting build is retained as failed evidence, not a pass.
- The earlier functional campaign had continuation over its failed/incomplete Cash and
  recovery paths; no new full financial campaign. Its long retained trace did
  not preserve all desktop layout evidence. A subsequent clarification did no
  new browser testing or mutation. The subsequently authorized responsive-only
  follow-up supplies the missing current UI proof without new record creation.
- Desktop create root `start_date` was October 11 whereas the selected,
  persisted item was October 12. Unchanged native BookingService uses each
  item's calculated start/end for persistence, not this root field. Do not
  reconstruct missing nested request payload from the persisted row or call
  this a new scheduling fix.
- Current mobile cancel screenshots `bebh8a`/`h3ivjq` show the new full-width
  date row and non-overlaid grid. Older `c6lud1`/`lja66b` chat/clipped-year
  screenshots precede the latest layout correction; they are not current
  defect evidence. The responsive follow-up now retains current screenshots
  and rectangle/scroll measurements; the earlier limitation is superseded.
- Cleanup compared every isolated table fingerprint and native DDL definition;
  only naturally advanced AUTO_INCREMENT allocation counters were excluded
  from the isolated DDL comparison. No schema reset, replacement or protected
  baseline change occurred.
- No changes to contention, overlap, adjacency, recurrence, hours, closed dates,
  blocked time, capacity, rescheduling, cancellation-release implementation,
  appointment duration, local UNPAID, accounting, providers, refunds or payouts.
  Unrelated 954-test hardening failures were not rerun.

## Responsive-only follow-up evidence

| Surface | 1280 | 390 | 320 |
|---|---|---|---|
| Agenda / Day / Week | `wvofjc`, `8drbav`, `7whm6l` | `ualjx3`, `ovqss3`/`1alkqn`, `1bfgwi` | `r3tvpz`, `dzofdr`/`hp7qi0`, `qxd8h6` |
| Date / service-time controls | `h0umwr`, `odzzsi` | `3klwbm`, `u1gt06`, `8gn60c` | `i3qgky`, `rsk9b7`, `lzkrmt` |
| Calculated draft / total / footer | `yfucv3`, `f1773l` | `xcsgux`, `s9ozn2` | `w0wxmp`, `jickbj` |
| Existing saved booking 7 | `wduwgz` | `y6r28g` | `pv005e` |
| Empty local-client validation | `vnmj4t` | `13ltqu` | `gfpwox` |
| Help / final content reachability | Desktop placement measured | `ocvcyu`, `olqmfl` | `46fjg8`, `2o9t93` |

- Outer document/body scroll width does not exceed its own client width.
  At 320 the measured document is 320/320 and body 305/305; the narrower body
  is scrollbar space, not overflow. Optional Week scrolls internally.
- Initial sticky Add-button/card overlap is retained as a recoverable observation:
  ordinary scrolling exposes the cards and separates final content/help/footer.
  No inaccessible responsive defect was established; no application edit made.
- Native calculated draft controls are **Edit / Checkout / Cancel**, not a
  dedicated Review Back button. Checkout directly creates and was never clicked.
  The unsubmitted service subform's Back arrow was used; local-client Cancel
  visibly retained the parent Customer/Cash selections.
- Exactly one permitted non-persisting calculate POST returned 200. All other
  recorded requests were GET. Saved details used GET 200 only, without calculate.
  Required-Name validation prevented client-save POSTs. No creation, payment,
  Wallet, status-update, cancellation or invitation mutation was requested.
- Detail-status timing caveat: the first desktop listener missed its response;
  the later genuine GET 200 receipt is recorded separately, not inferred from UI.
- Final UI returned to Agenda with seven existing appointments and no open
  draft/drawer/slot prompt. The same draft was resized across widths; this is
  responsive evidence, not three repeated financial journeys.
- Maps was unavailable and the mobile More role lookup inconclusive; neither
  blocked the scoped native calendar paths or established a responsive defect.
- Preservation discloses the isolated `personal_access_tokens` fingerprint
  difference: native Sanctum authenticated reads update usage timestamps.
  No token was added and no login repeated; this metadata was not silently
  normalized, restored or claimed identical. All other 205 table fingerprints,
  all 206 counts and complete native schema matched.

## Receipts

- `.local/staging-mvp/calendar-closure-browser.json` — initial bounded campaign,
  including now-resolved invalid Cash ID and pre-final-layout observations.
- `.local/staging-mvp/calendar-closure-fixed-flow.json` — corrected functional
  paths, organic recovery, scoped Specialist evidence and explicit trace limits.
- `.local/staging-mvp/client-save-regression.json` and
  `client-save-concurrency.json` — focused native idempotency checks.
- `.local/staging-mvp/calendar-closure-cleanup.json` — exact isolated restoration.
- `.local/staging-mvp/final-preservation.json` and
  `calendar-closure-media-preview.json` — final normal/protected/media comparison.
- `.local/staging-mvp/build-calendar-acceptance-closure-ready.json` — passing
  production build receipt.
- `.local/staging-mvp/calendar-responsive-evidence.json` — 67 retained current
  states with screenshot IDs, rectangles, scroll extents and checks.
- `.local/staging-mvp/calendar-responsive-verdict.json` — coverage, genuine
  response statuses, request safety, recoverable-overlap classification.
- `.local/staging-mvp/calendar-responsive-preservation.json` — final protected,
  isolated/source/media comparison and explicit authentication-metadata exception.

No remaining bounded Calendar responsive acceptance work. Overall MVP remains
in staging under the unchanged partial gates; no broader work is authorized.
