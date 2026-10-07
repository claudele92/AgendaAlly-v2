# AgendaAlly MVP readiness — MySQL target

Current decision: **REMAIN IN STAGING**, limited service-booking MVP only; no production approval or deployment.
Current same-20-gate score: **16 / 20 = 80%**. Calendar/browser authority is section 20; current SMTP/presentation/email-audit authority is sections 21–22.
Latest calendar acceptance: [bounded closure](agendaally-calendar-acceptance-closure.md). V3 and O6 are both 1.
**SMTP transport and actual Gmail delivery VERIFIED by the owner's normal port-3003 Admin test.** No agent retest was performed.
Only the explicit Admin-test capability is approved. Reset/verification/booking/worker mail remains suppressed; C1 and O4 remain partial.
**Targeted B1 and bounded calendar/browser acceptance VERIFIED; D1/R1/O1 local drills PASS, overall PARTIAL.**
The normal demo is restored without reseeding: 9 visible Shops, 51 Services,
14 assigned Specialists, 75 assignments and 90 existing media files.
Sections 20–21 are the current conclusion; earlier failure receipts
and superseded conclusions are retained below as history, not current blockers.
No production deployment, payment-provider activation, payout or refund occurred.

Historical prior-run status below is superseded by sections 20–22. Native **B1 VERIFIED**; selected A1/U1 role acceptance
and bounded N1/S1 application evidence are recorded in section 17. Earlier
failure receipts remain historical evidence, not current unresolved defects.
**U1 remains PARTIAL:** the tester's browser runtime closed and could not create
a new context. Cash post-Vendor history passed; final Wallet recheck and Cash
reschedule/cancellation UI reflection are explicitly unverified.
No production-like staging, deployment, restore program or live provider/email
activation had been performed at that earlier assessment. Later accepted local
runtime/restore evidence and the owner's SMTP receipt are recorded below.

## Architecture decision and bounded work order

MySQL 8/InnoDB is selected for the MVP, not production-certified. PostgreSQL is
“evaluated and technically viable for tested financial invariants, but deferred
because conversion/porting work provides insufficient MVP benefit relative to
the existing MySQL implementation.” Existing PG receipts are preserved.

Strategy G/operational_evidence_manifest remain deferred. No real production
caller requires arbitrary stale nested reservation success.

Initial production/prototype comparison: the existing reservation body already
locks the allocation PK and increments the existing version/CAS before ordinary
child authority reads, with bounded root retry. The prototype additionally
enforces owned/fresh RR at its entry boundary. The only production caller is
`PaymentOperationsController::create`, whose `run` wrapper does not start a
transaction. The minimum correction is an explicit owned entry at that caller,
plus the MySQL connection's RR configuration; preserve the lower-level existing
body and tests for managed fresh-parent transactions. No child range locks,
counter/membership schema, equation, identity or custody change is needed.

The MTN migration initially had an explicit SQLite-only gate. Its existing additive fields,
no-backfill rule, retained FK and five lifecycle/binding/retention triggers can
be represented with equivalent MySQL DDL. Review/install/down/reapply tests must
cover that whole contract, including legacy rows and native implicit DDL commits.
Only empty disposable MySQL databases may be used here.

Bounded implementation order:
1. Make the owned reservation entry permanent and verify native source behavior.
2. Represent the existing MTN DDL contract for MySQL, without changing identities.
3. Run one clean MySQL Phase 3C/canonical/Wallet certification batch; preserve all
   failure receipts and stop affected financial work on a new independent P0.
4. Finish traced Customer/Vendor/Admin, booking, commerce, security and VPS
   readiness assessment; consolidate one priority matrix and selected MVP scope.
5. STOP before a new major phase, production deployment, provider activation,
   destructive migration, new architecture or new financial/security P0 remediation.

## Decision summary — 2026-10-04

### Current forward-execution status: core and recurring-calendar corrections verified; full booking gate PARTIAL

The forward service-booking execution phase is now authorized and has started.
Database selection, payment architecture, Product purchasing and deployment were
not reopened. The immediate booking gate was investigated first.

**Native reproduction:** unchanged `BookingRepository::calculate` accepts
09:30–10:30 over an occupied 09:00–10:00 appointment. It also accepts
17:30–18:30 when the specialist's working day ends at 18:00. These are calculator
defects reproduced on MySQL 8.0.42/InnoDB/REPEATABLE READ, not claims that a full
Customer checkout or two-worker booking creation succeeded.

**Creator-confirmed endpoint contract:** half-open `[start,end)` per specialist;
stored end includes processing/pause once; exact endpoint adjacency is allowed;
the full appointment must fit working hours; distinct specialists remain independent.
The earlier Phase A adjacency stop is resolved, not a pending decision.

**Implemented:** shared authoritative interval/hour validation, existing-resource
PK mutexes in fresh-owned transactions, protected mutation and atomic cascade
planning, intra-request overlap rejection, and server-authorized preview exclusions.
No new scheduling schema, capacity identity, financial equation or isolation change.

**Native verified, bounded:** eight corrected calculator cases; 17 real-service
Cash/independent-worker checks; 13 extended Wallet/lifecycle/calendar checks; three
additional calendar boundary checks. Three independent MySQL connections proved
one durable same-slot booking and a distinct specialist committing while the first
transaction remained open. Financial rollback, duplicate rejection and funded
Wallet rescheduling preserved tested original economics. These are service-level
receipts with synthetic actors, not Customer/Vendor/Admin browser acceptance.

**Retained reproduction, now corrected:** a native daily/never disabled block originating
2030-01-07 is omitted when 2030-01-14 is requested: availability offers the
blocked hour and authoritative validation admits 09:45–10:45 over 09:30–10:30.
This existing recurring-calendar defect is now corrected in shared discovery/
interval validation, not a new financial invariant failure.

**Creator-confirmed recurrence contract:** preserve the original monthly day;
skip months without it, never clamp. Count scheduled calendar occurrences from
origin, including the first, regardless of working hours/closed dates/query window.
Missing monthly dates have no occurrence. The existing custom `[every, weekdays…]`
payload and native Sunday-based weekly grouping remain; no new schema/identity.

**New recurrence evidence:** **27 tests / 48 assertions PASS** (one existing
PHPUnit configuration deprecation) and **75 native MySQL calendar checks PASS**.
Daily/weekly/monthly, custom intervals/weekdays, never/inclusive date/count ends,
window slicing, missing dates/leap anchors, closed-origin/count behavior and
authoritative rejection are covered. These calendar-only receipts do not replace
the guarded Cash/Wallet/service or broader finance baseline.

The scheduler's approximate `origin + count units` deletion could remove a live
custom/count rule, and its date-ended cleanup could erase the inclusive final day.
It now retains counted metadata rather than guessing expiry, and existing
date-ended cleanup waits until the final date has passed. Exact counted-rule
garbage collection is deferred; no owned/production cleanup was executed.

Cascade races, cross-Shop assignment contention, deadlock/root-retry certification,
terminal-state races and end-to-end clock interpretation remain unaccepted.
Downstream role/security/communication gates have not started. The updated final
source/receipt answers are in section 16. **VPS staging: NO-GO.**

**The tested MySQL application-side financial boundary passes. AgendaAlly is not
production-ready.** The next work should be booking correctness and selected
Customer/Vendor/Admin acceptance, not another payment redesign.

The selected MVP is **service-booking-first**: approved Vendor onboarding, Cash,
Wallet spending from legitimately funded/verified balances, basic in-app and
transactional email communication, and restricted Admin financial operations.
The creator explicitly deferred Product purchasing, electronic providers, Vendor-direct/own-gateway
checkout, external payouts and multi-Shop checkout. Existing Product discovery
may remain clearly separate; no promise of a launchable purchasing workflow.

Wallet/Cash can support that scope after the booking and operational gates below.
Cash does not become platform custody, and disabling electronic top-ups does not
authorize an Admin to manufacture Wallet balances. A zero-balance Customer must
have a working Cash alternative.

### Evidence levels and limitations

- **Native verified:** actual source on disposable MySQL 8.0.42/InnoDB/RR; real
  financial writers/controllers/helpers with synthetic actors/transport.
- **Isolated verified:** source domain/auth tests on process-local SQLite; not
  a MySQL contention result and not external delivery UAT.
- **Source traced:** caller, service, repository, model, permission and UI chains
  inspected; not proof that the entire browser journey succeeds.
- **Browser sampled:** current public customer page/location dialog and business
  sign-in at 1280, 390 and 320 pixels. No horizontal document overflow in the
  final samples. Authenticated calendars, checkout, financial modals and role
  screens are **not accepted across all three widths** in this milestone.

Historical native acceptance reports remain useful evidence, but are not silently
relabelled as a fresh MySQL production rehearsal. No live SMTP/SMS/push, payment
provider, VPS, backup restore, production load test or complete new-account →
booking → Vendor completion → Customer receipt browser journey was executed.
Protected development data was not used for destructive lifecycle experiments.

## 1. Permanent MySQL correction and certification

Implemented the bounded comparison above:

1. `FinancialOperations::reserveOwned` rejects both Laravel- and PDO-owned open
   transactions and incorrect MySQL session isolation. The actual POST caller
   uses it. Existing allocation-PK-first lock/CAS, ordinary child reads,
   equations, native integer money and three-attempt root retry remain unchanged.
   Lower-level managed fresh-parent transaction behavior is preserved.
2. Configured MySQL `REPEATABLE READ`; no range-authority scans or membership/
   counter/manifest schema introduced.
3. Installed equivalent MTN source DDL: same six nullable metadata fields,
   unique process/event identities, restrictive retained-context FK and five
   binary/null-safe coherence, immutability, lifecycle and retention triggers.
   Legacy rows remain all-null/unclassified. Colliding legacy IDs stop before
   DDL. Populated evidence refuses destructive down. MySQL implicit commits
   are acknowledged; the helper refuses migration inside an open transaction.
4. Native down testing exposed InnoDB replacing an implicit payment FK index
   with the completion unique profile index. `CompletionSchema::down` now
   restores supporting legacy FK indexing before dropping that unique index.
   No FK/invariant was removed or weakened.
5. Updated the reviewed development migration manifest for the authorized
   engine branch, accepting the previous marker without mutating the owned
   SQLite database. Original Laravel/customer/business previews are running.

### Receipts

All current receipts are under `.local/mvp-mysql` and `.local/mvp-wallet`.
The evidence archive preserves final results, responsive samples and relevant
failure/timeout receipts. Source harnesses remain in the project under those
directories; historical PG evidence remains separately intact.

| Verification | Result | Receipt |
|---|---|---|
| Original Phase 3C bodies | **13 tests / 51 assertions PASS**, no skips | `correction-evidence/matrix/original13-junit.xml` |
| Clean expanded native core | **20 groups PASS** | `correction-evidence/matrix/mysql-expanded.json` |
| Current native Wallet proof | **28 groups PASS** | `../mvp-wallet/evidence/mysql/wallet-matrix.json` |
| MTN/owned entry whole-contract boundary | **5 tests / 38 assertions PASS** | `boundary-junit.xml` |
| MTN retained-anchor FK, lifecycle/down refusal | **1 / 7 PASS** | `boundary-retained-fk-junit.xml` |
| Completion legacy-FK down/reapply | **1 / 5 PASS** | `completion-lifecycle-junit.xml` |
| Native stale-model / replacement-Transaction finality | **2 / 37 PASS** | `fulfillment-replay-junit.xml` |
| Actual complete ordered source migration chain | **224 up migrations PASS; 203 target-schema tables** | `bootstrap-final.json`, `bootstrap-final-timeout.txt` |
| Empty financial/fulfillment tail down and reapply | **8 down + 8 up steps PASS** | `bootstrap-final.json`, `bootstrap-lifecycle-final.txt` |
| Focused product/auth/security/domain regressions | **98 / 649 PASS**, one deprecation | `product-security-checks.txt` |
| Existing SQLite MTN/completion contract | **18 / 86 PASS** | `sqlite-ddl-regression.txt` |
| Owned protected comparison | **59 unchanged / 465 unchanged / 12 unverified** | `protected-comparison.json` |

The core verifies 10,000 authority against competing 7,000 reservations, exact
3,000 remainder, independent allocation progress with empty/populated evidence,
same-key replay and changed amount/actor/context rejection, newly committed
refund effects, payable/receivable conservation, exclusion rules, rollback,
native lock timeout, native deadlock with whole-root retry, confirmation and
canonical finalization. Wallet verifies 70+70 from 100 permits one spend, exact
conservation, debit/transfer ordering, spend/credit interactions, all required
rollback stages and native Product/Booking funding/replay. Fulfillment core
gates and additional source tests cover commission/cashback replay, stale
models, replacement transactions and native claim contention.

The obsolete negative Wallet-overspend expectation is not counted as a pass;
the current 28-group conservation proof supersedes it.

Full ordered source up, source down/reapply, and the native guard cases cover
integer columns/generated IDs, nullable uniqueness, explicit short index names,
FK retention, receipt non-self-anchor equivalent protection, exact JSON
number/type/container/array-order semantics, immutability and native trigger
behavior/cleanup. This is not certification of every application endpoint or
every historical destructive `down()` migration.

Failures preserved: missing framework fixture aliases/configuration, a native
snapshot's SQLite-specific enumeration, qualified snapshot keys, the genuine
legacy-FK index-drop error and an interrupted disposable DDL roundtrip. The
interrupted **empty** fixture was explicitly normalized, then the complete
eight-migration lifecycle passed. No failed native state was mistaken for
economic conservation. A workspace restart removed the transient services;
completed receipts survived in the project. Only MySQL was restored; no PG
implementation, adaptation or new certification was performed.

**Scope conclusion:** MySQL Phase 3C application financial requirements listed
in the work order pass within these receipts. No newly confirmed independent
financial/security P0 was remediated. Production certification still requires
selected end-to-end journeys, operator controls and VPS/restore acceptance.

## 2. Payment readiness matrix

Statuses below concern each payment feature, not approval to launch AgendaAlly.
All electronic providers remain disabled; customer checkout remains Wallet/Cash.

| Feature | Classification | Verified boundary / remaining requirement |
|---|---|---|
| Cash | **VERIFIED** — baseline accounting slice; selected journey **PARTIAL** | Preserve offline custody and receivable semantics; require Customer/Vendor collection/completion acceptance and Admin SOP before launch. |
| Wallet | **VERIFIED** — baseline financial slice; selected journey **PARTIAL** | Debit, transfer, concurrent spend/credit, native Product/Booking and replay pass. Verified funding/currency, zero-balance Cash fallback and visible history still need selected-role acceptance. No real top-up activation. |
| MTN | **DEFERRED**; external UAT dependency | Baseline MySQL identity/recovery guards retained. Activation, merchant contracts, credentials, calls and UAT are outside this phase. |
| Orange | **DEFERRED**; verification remains **BLOCKED** | Authoritative callback/status verification and provider contract remain incomplete; keep initiation disabled. |
| Flutterwave | **DEFERRED** | Common identity/reservation/receipt baseline retained; provider-specific UAT and activation outside this phase. |
| Paystack | **DEFERRED** | Original identity and common completion baseline retained; provider calls/activation outside this phase. |
| Stripe | **DEFERRED** | Captured-payment identity baseline retained; capture/refund callback UAT and activation outside this phase. |
| PayPal | **DEFERRED** | Original captured identity retained; external capture/refund/status contracts and activation outside this phase. |
| Refunds | **PARTIAL** | Protected native Product/Booking/Wallet refund authorization/replay evidence exists. Electronic reservations are not proof of remote refunds. Launch only supported original-evidence paths with clear Customer state; no real refund executed here. |
| Commission receivables | **PARTIAL** | Baseline native conservation verified; operator settlement/reference/visible-history acceptance required. Never classify Cash as platform collection. |
| Vendor payable reservations | **PARTIAL** | Baseline owned-entry/conservation verified; selected-role request/cancel/UNKNOWN UX and approval SOP remain unaccepted. |
| External Vendor payouts | **DEFERRED** | No bank/mobile-money execution activated; a reservation is not a payment. |
| Electronic reconciliation | **DEFERRED** | Retained baseline evidence/recovery paths; do not enable provider reconciliation jobs in this phase. |

Vendor-direct and own-gateway collection are **DEFERRED / disabled**, regardless
of existing configuration/catalog screens. Neither another gateway nor current
Shop settings may manufacture original custody or historical refund authority.

## 3. Customer readiness

Trace: native auth services/requests → Sanctum/session middleware → profile/
address and public repositories → ServiceMaster availability/calculation →
BookingService or Cart/Order services → native accounting/Wallet/Cash →
resources/history/receipt/notification consumers.

| Customer journey | Assessment |
|---|---|
| Registration and verification | Email/phone initiation exists; identity verification/delivery and complete new-account acceptance remain external/unverified. Do not count a success response as delivered email/SMS. |
| Login/logout/password reset | Existing flows; reset expiry, one-time consumption and hashed challenges have isolated regression coverage. Live delivery and logout/cache clearing on all role surfaces need acceptance. |
| Profile, location/address | Native public/profile restoration evidence exists. Current country/city dialog and no-location state render at all sampled widths. Changing discovery context may clear a local Cart; communicate it. |
| Shop/Service discovery, search/filter/details | Real public shop regression and restored native business/service surfaces; home APIs return real application content. No claim that every filter or empty result combination was retested. |
| Availability and booking creation | **Not MVP accepted:** source overlap/concurrency weaknesses below. Payment correctness does not validate resource availability. |
| Rescheduling/cancellation | Source update/status and authorization/refund protections exist; exclusion, stale availability, capacity release and reschedule atomicity need a native lifecycle acceptance. Frozen economics must not be silently repriced. |
| Cart/checkout/order creation | Native Product/Wallet tests available; purchasing deferred for first scope because inventory exclusion is not guaranteed. No multi-merchant electronic checkout. |
| Wallet/Cash | Financial boundary verified; source history/method selection exists. Verify Customer-visible pending/failed/success/retry and a working Cash path without electronic top-up. |
| Booking/order status, history, receipts | Native resources/detail/history and invoice work exist; a complete Customer → Vendor → Customer refreshed state/receipt acceptance remains a launch gate. |
| Refund/cancellation visibility | Authorized domain paths have evidence; UI must distinguish requested/accepted/rejected/unknown, cancellation versus monetary return, and unsupported historical records. |
| Notifications, retries and errors | Native in-app/push hooks and reset guards exist; no reliable live external delivery proof. Financial replay is verified, but ordinary booking submission deduplication is not equivalent to contribution replay. |

No new Customer or booking was created in the protected development database.

## 4. Vendor readiness

Trace: native registration/application and Admin approval → owned Shop/
invitation/branch grants → service, pricing and specialist/resource setup →
seller booking/calendar or order management → permitted status changes →
financial projections/operations and notifications.

- Onboarding, Shop creation/configuration, Service creation/pricing, accepted
  staff/invitations and scoped business surfaces exist. Their combined new-Vendor
  journey is not currently accepted end-to-end.
- Country/Shop/branch grants must remain authoritative. Owner access is not a
  global staff grant; do not solve acceptance failures by broadening roles.
- Booking management and staff/ownership matrices have isolated coverage.
  Calendar concurrency, reschedule collision and cancellation release do not.
- Order/fulfillment source and native financial finality are materially verified,
  but Product purchasing/stock is intentionally excluded from first launch.
- Financial history, commission, receivable/payable and payout-request surfaces
  need role-specific native acceptance. Clearly label a reserved request as
  **not paid**; an unavailable external execution action must not imply success.
- Merchant/payment configuration may be inspected/configured only under existing
  ownership, encryption and activation gates. No provider activation by saving
  a profile. Requests cannot attribute creator-level money using unrelated
  invitations or current country settings.
- Notifications are component-supported, not transport-reliability certified.
  Mobile authenticated calendar/history/forms remain P1 acceptance work.

## 5. Admin readiness

Trace: auth/role/country gates → user/Vendor/Shop administration and approvals →
booking/order oversight → canonical allocations/evidence/operations →
refund/reservation/intervention and provider configuration.

The actual financial POST permits only Admin-authorized kinds or scoped Vendor
payout reservation, and now owns its RR transaction. Existing operation/action
guards remain unchanged. Accounting evidence cannot be edited into a success.

Outstanding acceptance: approve a fresh Vendor without excessive permissions;
read correct Customer/Vendor and booking data; inspect original allocation/
contribution history; create/replay/cancel permitted reservations; see
UNKNOWN/PENDING without freeing capacity; distinguish external execution from
reservation; handle unsupported legacy rows; prove audit references and
notifications remain visible after reload. Generic legacy payout/history screens
are not silently assumed to be canonical allocation-operation UX.

## 6. Booking correctness — first-launch blocker

Original native source reproduced partial overlap, closing-hour and inconsistent
adjacency defects; those before-correction receipts are retained. Current creation
owns a fresh transaction, locks known assignment and specialist PKs before ordinary
availability reads, and shares exact interval validation with protected mutations.
It rejects overlapping batches, cross-Shop checkout and forged preview exclusions.
No range-lock, scheduling schema or new capacity identity was introduced.

**Verified service-level evidence:** corrected calculator cases, independent
same-slot contenders, independent specialist progress, Cash/Wallet rollback/replay,
supported unfunded cancellation and original-price-preserving reschedule. Details
and limitations are in section 16; this is not complete role/browser acceptance.

**Recurring-calendar correction:** load recurring rules whose origin precedes the
query window; derive occurrences from their original anchors and counts before
applying closed/working dates. Monthly dates do not overflow/clamp; custom daily
steps, weekly interval/weekday selection and monthly steps preserve native payloads.
Native discovery and interval validation pass 75 checks; unit/window-slicing
tests pass 27/48. Cleanup cannot approximate counted expiry or delete the inclusive
final date's capacity. Counted metadata is conservatively retained.

**Conclusion:** the broader **P0 booking correctness gate remains PARTIAL**.
Recurrence semantics are confirmed and the reproduced range omission is corrected.
Complete remaining native contention/lifecycle/cascade and clock/role acceptance,
including concurrent calendar-setting mutation, before declaring the full gate PASS.
The accepted finance baseline is not rerun or replaced; no new money/schema/
permission redesign or downstream acceptance was inferred.

Acceptance must cover identical/partial/containing/adjacent intervals, independent
resources, same-key retry/double click, stale availability, service duration and
pause/buffer, closed dates, recurring/custom disabled times, cancellation release,
atomic reschedule, and rollback. Current shop-hour timezone tests do **not**
certify appointment timezone/DST behavior: availability uses server date/time
operations. Explicitly settle Vendor versus Customer timezone, midnight and
DST boundaries where relevant. Preserve original currency/custody and frozen
financial economics; unsupported extra-time/repricing remains fail closed.

## 7. Product and multi-Shop marketplace

Actual trace: Stock/Product/Shop → Cart detail per Shop → order/detail creation →
`OrderHelper::actualQuantity` and `updateStatCount` → native payment →
Vendor fulfillment → locked financial finality/refund boundary.

Native source helper proof on disposable MySQL:
two hydrated Stock callers read quantity 10; each accepts 6; each writes an
absolute remaining 4 from its cached quantity. Final stock is 4 despite accepted
quantity 12. This reproduces a **lost decrement in the real helpers**, not a
whole two-Customer checkout. Source Cart/POS uses these hydrated reads and
updates; no stock admission mutex was found in the traced paths.

The application has finite quantities and min/max ordering behavior, so it does
claim more than an unlimited catalog. It cannot currently claim verified
concurrent stock exclusion. Do not invent warehouse/reservation architecture.
If purchasing is retained for launch, this becomes a P0 gate with full native
checkout race/rollback acceptance. Under the recommended service-first scope,
Product purchasing and its stock correction are deferred.

Multi-Shop Cart code splits creation into per-Shop Orders. That is not proof of
all-or-nothing stock admission, cross-Shop currencies, one shared provider
collection or one merchant profile. Defer multi-Shop checkout; test a single-Shop
currency/quantity/fulfillment contract before any later Product launch.

## 8. Bounded security findings

No destructive exploitation or broad scanner run was performed.

- **Authorization evidence:** current reset/staff/seller-booking/story/public-shop
  and native workstream tests pass. Existing financial/refund/fulfillment
  containment receipts remain relevant. This does not certify every endpoint,
  export, configuration action or role combination.
- **Sensitive rate limits:** API middleware currently permits 5,000 requests per
  minute. Specific reset/invitation limits exist, but that global ceiling is not
  an adequate login abuse policy. Narrow login/registration/verification limits,
  neutral enumeration behavior and 429/retry UX need P1 acceptance.
- **Mass assignment/validation:** typed immutable financial identities and
  server-owned projections/guarded status paths are retained. Review selected
  account/Shop/resource mutations using validated inputs, including cross-Shop
  IDs and forged role/country/resource identifiers, before launch.
- **API/CSRF/cookies:** bearer/Sanctum checking exists; stateful API middleware is
  not automatically enabled. Apply CSRF to actual cookie-auth state-changing
  paths; do not treat SameSite as universal CSRF protection. Sessions default to
  database, HttpOnly and Lax; secure-cookie, TLS, proxy and domain configuration
  must be explicitly pinned for production.
- **Credentials:** `PaymentPayload` hides payloads and uses
  `EncryptedProviderPayload`. Durable revision encryption/retention exists.
  APP_KEY must survive restore. No real credential was requested/read here.
  Earlier imported client-configuration exposure still requires a value-free
  rotation/history/build-artifact review; source deletion does not prove rotation.
- **Logging:** require redaction of auth headers, reset/invitation challenges,
  decrypted merchant payloads and provider responses. This was not a full
  historical log/secret scan, and no absence-of-secrets certification is claimed.
- **Uploads:** general image gallery handlers validate image MIME/types and 5 MB
  limits; Story upload request only visibly bounds file sizes (20 MB), without
  a MIME allowlist in that request. Review its downstream storage processing,
  ownership and executable-file handling. No executable-upload exploit proven.
- **Development exposure:** existing file-serving boundary evidence and guarded
  development commands must be retained; deployment must exclude backups,
  debug routes, source trees, development credentials and build artifacts.
  `APP_DEBUG=false` is a release configuration requirement, not the local setting.

No new immediately exploitable financial/security P0 was confirmed. The findings
above are not permission to widen roles, rotate credentials, rewrite history or
change production settings automatically.

## 9. Notifications, communication and background work

| Event | In-app / source hook | Email | SMS | Push |
|---|---|---|---|---|
| Booking created / status confirmed / canceled | BookingService/notification hooks and stored notification paths | Conditional/template path; delivery not accepted | No accepted universal event channel | Native hooks; configured transport/UAT missing |
| Booking rescheduled | Update path exists; prove correct recipients and one visible event | Not accepted | Not accepted | Not accepted |
| Appointment reminder | `booking:send:notification`, minute window 30 minutes ahead | No accepted guaranteed channel | Not accepted | Source dispatch exists; scheduler/delivery not accepted |
| Order created / accepted / canceled / delivered | Native order/status notification hooks | Conditional; not reliable-delivery certified | Not accepted | Native dispatch hooks; UAT outstanding |
| Refund state | Domain paths and native history exist; complete state communication not accepted | Not accepted | Not accepted | Not accepted end-to-end |
| Payout request/transition | Durable operations/history exist; recipient notification acceptance missing | Not accepted | Not accepted | Not accepted |
| Account/reset | Hashed expiring challenges and source email/phone paths | Live transport/template acceptance required | Depends on configured account channel, unaccepted | Not a reset-delivery substitute |

MVP minimum: durable in-app state/history plus transactional account/booking
email with actual delivery and retry acceptance. SMS/push may wait; do not
purchase external providers automatically. A reminder must not be the only
way a Customer can verify booking time or status.

Actual scheduler inventory: hourly `email:send:by:time`; minute booking reminders
and auction updates; daily expired master-disabled-time/closed-date/delivery
cleanup; minute `booking:auto:ended`; five-minute MTN pending reconciliation.
Actual jobs include delivery assignment, exports, file deletion, referral,
Wallet-currency normalization, activity and filter work.

Queue defaults to `sync`; database/Redis definitions have `after_commit=false`.
Some notifications use `afterResponse`. That is not durable processing across
process/VPS restarts. Email scheduling marks its template status before event
dispatch; reminder selection is a narrow minute window without demonstrated
catch-up/idempotence. `booking:auto:ended` directly bulk-updates status, bypassing
model/service transition hooks; review financial/notification compatibility
before scheduling it for live records. No live cleanup/reconciliation job ran.

Minimum deployment: supervised supported queue worker(s), failed-job visibility,
one scheduler invocation each minute, only reviewed selected-MVP commands,
idempotent recipient/event identity and retry/catch-up tests. Do not blindly
enable auction, electronic-reconciliation or destructive cleanup schedules.
Retain evidence and UNKNOWN capacity; retries must not post money twice.

## 10. Files/media and minimal VPS architecture

Default filesystem is local. Profile/Shop/Product/Service galleries and uploads
need a persistent `storage/app`/public-media volume outside release directories,
correct public symlink, constrained permissions and off-VPS backups. Replacing a
release or reboot must not lose uploads. Review external/demo URL reliance and
file-delete jobs; never execute uploaded PHP/scripts. One VPS does not itself
require S3/object storage; multiple application hosts require a deliberate shared
media strategy before scaling.

**Proposed architecture, not deployed:**

- Nginx with TLS terminates public traffic; expose only intended customer/business
  hosts and API, not development ports. PHP 8.4.x was exercised here; pin a
  maintained compatible version using the lockfile and a production rehearsal.
  Required composer/runtime extensions include cURL, DOM, fileinfo, GD, JSON,
  OpenSSL, SimpleXML, ZIP, PDO MySQL, mbstring/XML and worker-required pcntl.
- Laravel PHP-FPM; production-built Next customer under supervised Node (use the
  tested supported Node/Next combination), static production business bundle.
  No Vite/Next development server or PHP built-in server in production.
- MySQL 8/InnoDB, explicit RR and strict mode, loopback/private access. Separate
  restricted application account and temporary migration account; no root
  application login and no public 3306. Pin charset/collation/timezone and verify
  native ID, money, JSON and FK contracts.
- `APP_ENV=production`, `APP_DEBUG=false`, stable separately secured APP_KEY,
  secrets outside Git and public bundles, exact trusted proxies/CORS origins,
  secure cookies and HTTPS-only auth. Do not rotate an existing APP_KEY casually.
- systemd/supervision for PHP-FPM, Next and queue worker; reviewed cron/scheduler,
  graceful worker restart and failed-job inspection. Redis is optional if the
  chosen queue/session/cache contracts do not require it.
- Deploy user and runtime user separated; writable Laravel cache/storage only;
  log rotation/retention, upload quota/free-disk alerts, no world-writable source.
  Firewall 80/443; restricted key-based SSH/admin access, no exposed database/
  queue dashboards or development tooling.
- Initial sizing recommendation: 4 vCPU / 8 GB RAM with builds off the serving
  host; not a measured capacity promise. Set worker/PHP/MySQL connection limits
  together and monitor disk, CPU, RSS, connection use and backlog before resizing.

Before real cutover: approved data-transition plan, staging schema/data integrity
rehearsal, source/lockfile release, secret injection, config caching, maintenance
window, backed-up migration plan, health/role checks, gradual resume and a code
rollback compatible with retained new evidence. MySQL DDL is not transactionally
undoable as a complete Laravel migration chain. Never automatically reverse
populated financial migrations or reclassify the 12 legacy Orders.

## 11. Backup, recovery and observability contract

**Recommended targets requiring approval and demonstration:** financial database
RPO ≤15 minutes, media RPO ≤1 hour, RTO ≤4 hours. These are targets, not achieved
service levels.

- Nightly consistent InnoDB full backup including triggers/routines/events and
  exact native data; encrypted binlog/PITR capture shipped off-VPS at least every
  15 minutes. Verify restoration of guards, identities and native integers.
- Encrypted off-VPS copy: 7 daily + 4 weekly + 3 monthly full backups, with
  matching PITR retention sufficient for the daily window. Separate backup
  credentials and restore permissions; alert on failed/missing copies.
- Hourly incremental persistent-media backup, daily retained snapshots aligned
  with DB references; restore checks must include Customer/Shop/Service images.
- APP_KEY and required configuration/secrets backed up separately in restricted
  encrypted recovery storage. Do not mix plaintext secrets or development
  credential sheets into application/media archives.
- Restore to an isolated host/database first; replay to the selected point,
  verify schema/guard inventory, row/value fingerprints, allocation/Wallet
  conservation, history/receipts and decryption, then selected-role journeys.
  Record elapsed time, missing media and achieved RPO/RTO. A backup command is
  not a completed backup service or restore drill.

Minimum observability: uptime and readiness checks for frontend/API/DB; redacted
application/HTTP errors; failed queue/retry counts and worker liveness; scheduler
heartbeat/missed reminders; MySQL connections/locks/storage; free disk/inodes,
CPU/memory; TLS expiry; failed login/429 abuse; old UNKNOWN/PENDING payments,
recovery failures, refund failures and intervention-required payout requests.
Use existing logs plus simple alerts/operator dashboards first, not a new
enterprise monitoring architecture. Keep immutable financial evidence separate
from disposable diagnostic logs.

## 12. Performance and responsive acceptance

Public repository pagination/search and eager-loading work exist; native booking
resources previously corrected singular/plural transaction loading. Availability
constructs a date range per specialist and processes working days/exceptions/
bookings; bounded range caps and realistic calendar fixtures need measurement.
Inspect history page limits, per-page validation, branch/Shop filters before
pagination, query plans/indexes and export memory. No general N+1 certification
or production load estimate was established.

Large/demo images, repeated discovery calls, financial history and dashboards
need production-mode timings, not development compile timings. The observed
15-second cold Next compile is not a production latency measurement. No speculative
index/caching redesign was implemented. Optimization becomes P1 only when a
selected journey demonstrably fails its agreed latency/capacity acceptance.

Current final public samples at 1280/390/320: customer home/location dialog and
business login render without horizontal document overflow. An initial blank
business screenshot was caused by the **test** blocking local Firebase module
URLs; domain-scoped blocking corrected the sample, not the application.
An artifact-proxy screenshot returned 404 rather than the native application;
native-port CDP samples, not that 404, are the UI evidence.

The public samples above are historical baseline evidence. The authenticated
selected-role receipts in section 17 supersede them for service booking,
calendars, finance history and approval screens. Product Cart/checkout remains
excluded; neither a login screenshot nor compilation alone is UI acceptance.

## 13. One authoritative remaining-work matrix

This is the single matrix reconciled against same-run native acceptance below.
Confirmed contract decisions no longer block B1. Application acceptance and
external/production readiness are distinct gates.
`Approval` specifies residual
boundaries for semantics, external operations or production actions. No separate
competing backlog is created in this report.

| ID / priority | Exact issue and affected workflow | Evidence / severity | Type and minimum correction | Acceptance | Approval |
|---|---|---|---|---|---|
| B1 **VERIFIED / closed P0** | Exclusive Service admission, recurrence, cancellation/reschedule and lifecycle contention | 143 native execution checks; 14 terminal/contention checks; 27 recurrence tests/48 assertions and 75 native recurrence checks PASS | Existing specialist mutex/shared intervals and original economics retained; no resource/custody redesign | Finite native acceptance closed; failure and financial receipts retained | No unresolved scheduling decision; not a production certification |
| A1 **APPLICATION ACCEPTED / EXTERNAL DEPENDENCY** | Selected account, profile/address, Vendor onboarding and role ownership | Native auth/reset/email/address/forged-ID acceptance; real fresh Vendor setup and Admin approval UI. Live account email not delivered | Named auth limiter buckets, neutral responses, challenge binding and Shop-filter correction | Application paths accepted with local/native fixtures; delivered account mail and full live signup/reset remain external | No provider credential request or external activation authorized |
| U1 **VERIFIED** | Selected Customer/Vendor/Specialist/Admin booking/history and finance UI | Cash/Wallet creation/reload; reschedule/reflection; unpaid local-client creation/cancel; Customer Cash cancel; Specialist persisted detail/hours/blocks and1280/390/320 PASS | Ordinary draft/detail/nullable/responsive defects fixed; existing unfunded writer integrated, funded guard preserved; UNKNOWN untouched | Section18 current actual browser and authoritative release receipts close the former browser gap | No broad redesign, role widening or external financial action authorized |
| N1 **LOCAL APPLICATION VERIFIED / EXTERNAL DEPENDENCY** | Selected operational outbox, queue restart and reminder scheduler | 22 native process/transport/dedup/rollback checks plus four selected-scheduler checks PASS; SMTP not activated | Encrypted operational evidence; selected queue; in-app status/history and reminder catch-up; UNKNOWN never automatically resent | Real worker restart/loss tested around acceptance; native schedule selects only MVP tick. Live deliverability and production supervision unverified | Broad cleanup/auto-ended/provider schedule excluded; live transport/configuration separately approved |
| S1 **BOUNDED APPLICATION VERIFIED / RELEASE DEPENDENCIES** | Selected abuse/ownership/session/upload/redaction boundaries | Native expiry/replay/429, cross-role/Shop, upload/gallery and conditional CSRF/bearer-identity checks PASS | Narrow named buckets; MIME/ownership/nonexecution and selected SMTP redaction | Selected application scope accepted; production TLS/secure-cookie/proxy settings and historical key rotation not certified | No material permission redesign or real credential rotation performed |
| D1 **P1** | Production MySQL/data cutover and deployment/rollback are not rehearsed | Disposable source DDL passes; owned baseline remains SQLite and unchanged; no VPS exercised | Config/operations/data migration plan; pin native schema, least privilege, stable keys, guarded release and compatible rollback | Staging native boot/cutover; exact money/IDs/guards, legacy unverified handling, maintenance/health/restart and code rollback without deleting evidence | **Yes**, including data transfer and real VPS deployment |
| R1 **P1** | DB/media/key backup and restore service absent from acceptance | No off-VPS copy/PITR or timed restore proof | Operations/config; implement approved encrypted retention and restore contract | Isolated timed restore; guard/fingerprint/conservation/decryption/media and roles checks; demonstrate RPO/RTO; alert on failed archive | **Yes**, production access/storage costs separately |
| O1 **P1** | Production error/queue/scheduler/disk/DB/intervention alerts not accepted | Logs/retained states exist, no continuous monitoring proof | Config/operations; minimal health checks, redaction and actionable alerts | Simulated worker/scheduler outage, low disk, HTTP failure, old UNKNOWN/refund failure and intervention request; named operator sees and resolves safely | **Yes** |
| E1 **P2** | Electronic merchant contracts, country/currency profile and provider truth/UAT incomplete | Synthetic identities/guards are not external readiness; Orange blocked | External/config/code; select one approved platform provider, exact profile and authenticated callback/status/captured identity contracts | Sandbox then approved UAT; timeout/duplicate/out-of-order/wrong amount/currency/owner evidence; no capture duplication or false success | **Yes** before credentials, calls or activation |
| E2 **P2** | Electronic refunds/reconciliation/payout-side custody intervention not operational | Reservations conserve authority but remote action/recovery unproven | Code/config/external SOP; original evidence only, retained UNKNOWN/PENDING, reconciled original-provider outcomes | Remote refund replay, callback loss, provider unknown, operator recovery; exact original custody and capacity; real actions only separately approved | **Yes** |
| E3 **P2** | Electronic activation safety and selected-provider operational monitoring | Disabled policy retained; whole live enable/disable rehearsal absent | Config/operations; explicit per-country/currency/merchant allowlist and kill switch, safe worker policies | Unsupported profiles fail closed; saving config cannot activate; UAT approval gate, alert and disable/recovery exercise | **Yes** |
| P1 **P3**, becomes **P0 if Product purchasing launches** | Concurrent stock admission is not guaranteed; Product Cart/order/fulfillment | Native real-helper lost decrement: 10 → two accepted 6 → persisted 4; full checkout race unaccepted | Code, no invented warehouse schema; existing Stock PK exclusion/conditional decrement with actual checkout rollback contract | Exactly one/limited quantity sold, different stocks progress, multi-item failure restores stock/money, retries/cancel returns follow actual contract | **Yes**, Product scope and any material stock semantics separately |
| P2 **P3** | Multi-Shop purchasing and complex cross-currency/merchant checkout not accepted | Per-Shop order splitting is not atomic shared collection or stock proof | Product/code/possible architecture; scope explicitly, single-Shop first | Defined partial-failure/currency/stock contract; never imply one arbitrary merchant covers all orders | **Yes** |
| P3 **P3** | External Vendor payouts, Vendor-direct/own-gateway and advanced features | No live execution activated; common architecture retained | External/architecture; defer without repurposing original custody | Separately approved original authority, merchant contract, beneficiary/settlement/recovery and UAT | **Yes** |
| P4 **P3** | SMS/push expansion, Delivery app, advanced exports/analytics and speculative performance work | No essential delivery dependency for service-first MVP; no Product/Delivery launch acceptance | Feature/external; defer; measure actual production-mode bottlenecks before optimization | Feature-specific ownership/retry/load acceptance; audit Delivery app after upload before claiming compatibility | **Yes** |

The earlier scheduling P0 is **closed at native B1 acceptance** by the retained
execution/terminal/recurrence receipts below. Selected-role UI acceptance remains
an independent downstream gate; it does not reopen already-passing native capacity
proof or certify production.
Product stock remains excluded/deferred. No new financial accounting equation/
custody correction is proposed.

## 14. How far from the limited MVP?

**Evidence-weighted limited-MVP readiness: 14.5 / 20 = 72.5%.** This is not
a feature/file, engineering-effort or revenue percentage, nor production
certification. The retained pre-run baseline is 8.5 / 20 = 42.5%.

Basis: twenty selected journey/operations gates, awarding one only to a completed
relevant acceptance and half to substantial component/source evidence with the
end-to-end/operational gate still missing:

| Gate set | Evidence status used for estimate |
|---|---|
| Six Customer gates: access/reset, discovery/location/detail, exclusive booking lifecycle, native payment, visible history/receipt, responsive error/retry | Partial 0.5 (live account email absent), verified 1, verified 1, verified 1, verified 1, partial 0.5 (selected layouts/native errors accepted, not every browser network-failure variation): **5 / 6** |
| Four Vendor gates: onboarding/Shop, service/staff setup, calendar lifecycle, financial history/operations | Verified1, verified1, verified1 (creation/reschedule/cancellation, reload, Customer reflection and responsive Vendor/Specialist detail accepted), verified1: **4 / 4**. Reservations/history are accepted, not external payouts. |
| Four Admin gates: approval/user oversight, financial boundary authorization, UNKNOWN/provider/intervention visibility, refund/cancellation operation | Verified 1, verified 1, partial 0.5, partial 0.5: **3 / 4**. UNKNOWN retained and reservation cancellation accepted; real provider intervention/refunds deferred. |
| Six operations gates: MySQL DDL/financial integrity, production runtime/TLS/cutover, restore, communications/background reliability, observability, authenticated responsive acceptance | Verified 1, unverified 0, unverified 0, partial 0.5 (local worker/scheduler verified, live mail/supervision absent), unverified 0, verified 1: **2.5 / 6** |

Same twenty gates and weighting: 5 + 4 + 3 + 2.5 = **14.5 / 20 = 72.5%**,
a0.5-gate-equivalent increase over the current work order's14/20 baseline.
The older8.5/20 assessment is retained as historical context. Partial credit is retained for
live account transport, broader error coverage and external financial
intervention. No credit is awarded for production cutover, restore or monitoring.
A later approved deployment/restore rehearsal must earn those gates separately.

Explicit answers:

1. **Selected P0:** B1's finite native booking gate is closed. Product stock
   becomes P0 only if purchasing is separately included; it remains deferred.
2. **Remaining P1:** live account-email transport, production TLS/cookie/proxy
   and historical rotation evidence; approved cutover/release, restore,
   worker supervision and monitoring. Selected local application acceptance
   does not certify these operational dependencies.
3. **Defer payment features:** all electronic activation, Vendor-direct/
   own-gateway, remote refunds/payouts and advanced cross-merchant reconciliation.
4. **Wallet + Cash:** yes for the limited scope after P0/P1 gates, with actual
   verified Wallet funding/currency and Cash fallback; not unlimited manual credit.
5. **Product commerce necessary?** No. Its independent stock/cross-Shop work would
   delay booking-first launch; browse-only discovery is not purchasing readiness.
6. **External Vendor payout necessary?** No. Label retained requests/reservations
   honestly and do not represent deferred execution as paid.
7. **Electronic providers necessary?** No. Keep disabled until selected-provider
   contracts, external UAT and recovery/activation gates pass.
8. **Shortest safe limited-MVP path:** B1 → A1/U1 selected Customer/Vendor/Admin
   service/Cash/Wallet journey → N1/S1 reliability/security → D1/R1/O1 isolated
   staging and restore acceptance → separately approved VPS launch.
9. **Shortest path to electronics:** choose one platform provider for one approved
   country/currency/merchant profile; E1/E2/E3 sandbox/UAT and operational recovery;
   explicit activation approval. Do not finish six providers in parallel or
   reopen PG/Strategy G for parity.
10. **Before production VPS:** pin/runtime/build/TLS/secrets/APP_KEY/proxy/cookies,
    restricted MySQL RR users and limits, persistent media, supervised reviewed
    worker/scheduler, guarded migration/data plan, backups/PITR and timed restore,
    alerts, selected-role acceptance and rollback rehearsal.
11. **Next bounded action:** seek separate approval for the prepared isolated
    D1/R1/O1 runtime/restore/observability rehearsal. U1 receipts are now complete.
    No VPS staging, provider activation or public launch is authorized.

## 15. Protected state and stop

The final comparison uses the accepted native snapshot serialization contract,
not counts alone. All **59** protected row/value fingerprints are identical;
all **465** owned schema objects are identical. Completion evidence tables
`payment_merchant_revisions`, `electronic_collection_attempts`,
`payment_financial_operations` and `payment_receipt_evidence` retain the expected
empty state. All **12 legacy Orders remain unverified**. No legacy meaning,
custody or classification was backfilled.

No real provider credentials/calls/refunds/payouts, electronic or Vendor-direct/
own-gateway activation, production VPS access/deployment, publishing, PG work,
Strategy G or new maintained financial membership/counter schema occurred.
The financial baseline remains accepted. **The forward phase started with
contract tracing/native probes and is stopped at its explicit unresolved-contract
gate.** No concurrency correction, downstream acceptance or VPS phase is claimed.

## 16. Forward-phase contract, receipts and explicit answers

### Short source-backed contract

- **Resource:** `Booking.master_id` identifies the specialist `User`. The
  `User::masterBookings` relationship and availability query span that master's
  ServiceMaster assignments and Shops; not one independent capacity per
  ServiceMaster or per Shop. No capacity-greater-than-one field/contract was
  established in this path.
- **Eligibility/ownership:** an active ServiceMaster links an active specialist,
  accepted Service belonging to its approved Shop, and an accepted `master`
  invitation to that Shop. Working days/closed dates/disabled times belong to
  the User. Preserve these identities and authorization; no new resource model.
- **Interval:** calculate starts from each requested naive `Y-m-d H:i`; stored
  end is start plus `ServiceMaster.interval + pause`. Processing time is already
  in that end. Priced extra-time is already fail-closed for frozen allocations.
- **Capacity states:** `new`, `booked`, `progress` consume availability;
  `canceled` and `ended` are excluded. This is source-established, not a freshly
  accepted cancellation/status race.
- **Hours/exceptions:** User working day `from/to` and `disabled`, closed dates,
  disabled periods with `can_booking=false` and existing recurrence settings are
  source inputs. Endpoint/full-hour enforcement is corrected for tested ordinary
  cases; recurring origins outside the request range are now included. Confirmed
  anchor/count rules are implemented; closed dates do not reset counts. Do not
  infer new capacity or role/clock acceptance.
- **Reschedule:** `update`, `timesUpdate`, plus optional
  `can_move_the_reservation_time` / `next_times_update` cascade. Existing cascade
  code now plans forward shifts using stored durations, validates all destinations
  before writes, and rejects cross-Shop/unauthorized affected rows. This is implemented,
  not yet cascade-race certified; no removal of this mode was inferred.
- **Clock:** Laravel's configured comparison zone is UTC; requests/persistence
  use naive date-time strings. Customer forms and Vendor moment forms send wall
  clocks, while detail/calendar consumers include UTC formatting. No Shop/
  Customer timezone or DST conversion contract was established. Preserve
  existing clock behavior; timezone acceptance remains open.
- **Request identity:** booking PK and optional `ids` preview/edit inputs are not
  a durable creation request key. No existing creation-idempotency identity was
  found. Do not manufacture one; creation exclusion must hold despite retries
  and cannot rely on client `ids` to waive it.
- **Confirmed endpoint:** half-open `[start,end)` with processing included once.
  Availability and protected admission now share interval/hour evidence.
- **Confirmed recurrence meaning:** skip missing monthly dates without moving the
  anchor; count all actual scheduled occurrences from origin, including the first,
  independently of working/closed dates and the requested availability window.

### Original unmodified native receipt — retained

`.local/booking-forward/contract-native.json`: MySQL **8.0.42**, **RR**, cloned
source table schema in `agendaally_payment_disposable_booking_contract`.
Unchanged real `BookingRepository::calculate` and `MasterRepository::times`;
synthetic actors/assignment/working day/occupied appointment; no provider/network
calls, payment writer, service creation or HTTP endpoint. Table cloning does
**not** claim copying/certifying financial triggers. The accepted financial
baseline was not rerun or replaced.

| Native case | Actual result | Classification |
|---|---|---|
| Exact 09:00–10:00 over 09:00–10:00 | Rejected | EXPECTED EXISTING BEHAVIOR in this case |
| Left-partial 08:30–09:30 | Rejected | EXPECTED EXISTING BEHAVIOR in this case |
| Right-partial 09:30–10:30 | **Accepted** | **REPRODUCED DEFECT**, calculator only |
| Contained 09:30–10:00 inside 09:00–11:00 | Rejected | EXPECTED EXISTING BEHAVIOR in this case |
| Containing 09:00–11:00 around 09:30–10:00 | Rejected | EXPECTED EXISTING BEHAVIOR in this case |
| After-adjacent 10:00–11:00 | Calculator accepts; availability omits 10:00 | **SOURCE-CONFIRMED RISK** — endpoint contract conflict, natively observed |
| Before-adjacent 08:00–09:00 | Calculator rejects; availability omits 08:00 | **SOURCE-CONFIRMED RISK** — endpoint contract conflict, natively observed |
| 17:30–18:30 with working day ending 18:00 | **Accepted** | **REPRODUCED DEFECT**, calculator only |

Initial fixture failures are preserved separately: missing required synthetic
UUIDs and using an invitation status string instead of the native integer
constant. These are **FIXTURE/TEST ISSUES**, not scheduling/financial failures.
The fixture was resumed explicitly without deleting its database or receipts.
The source-backed successful complete receipt supersedes those setup failures.

`.local/booking-forward/protected-comparison.json`: **59/465 unchanged**, all four
completion evidence tables empty, **12/12 legacy Orders still unverified**.
No permanent schema change, real APP_KEY access/rotation, production or external action.

### Corrected service and calendar receipts

- `contract-corrected.json`: all eight ordinary cases behave under the confirmed
  half-open/full-hour contract. The original unmodified receipt remains intact.
- `service-certification.json`: **17 checks PASS**, MySQL 8.0.42/RR, source-cloned
  tables plus all **17 native source financial guards**, real BookingService,
  independent worker connections. Cash creation, contention, independent specialist,
  stale retry, downstream rollback, rescheduling, overlap batches and closing time.
- `extended-certification.json`: **13 checks PASS** before a mixed-duration fixture
  assertion stopped the pass. Native Wallet creation consumes 100.00 from a
  documented synthetic 500.00 opening Wallet/history authority, leaving 400.00;
  failed native write, replay, overlap and reschedule preserve tested state.
  No live funding/provider call and no unauthorized Admin adjustment. Unfunded
  cancellation/replay, stale RR rejection and closed/disabled dates also pass.
  The failed adjacency assertion used an already occupied destination, not a
  free boundary; it is a **FIXTURE/TEST ISSUE**, not financial/scheduling evidence.
- `calendar-boundaries.json`: **three boundary checks PASS** on calendar-only
  restored native source: a 75-minute future service can start at stored 10:00,
  processing is included once, and midnight outside 08–18 is rejected.
  **REPRODUCED EXISTING DEFECT:** daily/never exception with earlier origin omitted
  for the later requested day in both discovery and authoritative validation.
- `service-certification-fixture-failure.json`: initial rollback test never
  triggered its write hook and correctly committed a normal booking; native
  writer statements bypass that listener. Corrected injection at the native
  allocation point-read verified actual rollback. Preserve both receipts.

Synthetic notifications/Bus delivery and authentication are isolated; economic
writers, persisted money and MySQL resource contention are not replaced with mocks.
Snapshot comparisons cover Wallets, Wallet histories, Transactions, allocations,
collection contexts, financial operations and platform fee ledger entries (plus
Bookings in the extended pass). They do not constitute a complete new snapshot of
every quote/effect/ledger table. Complete that coverage in remaining B1 certification;
the accepted broader finance baseline is retained, not replaced by these checks.
Public request fields cannot request internal interval metadata/exclusions; those
are typed server-only arguments, and creation never honors preview `ids`.

An environment restart discarded the ephemeral native data/server after these
service receipts. Retained JSON/query receipts survive. A separate runtime-recovery
bootstrap stopped during implicit DDL and is not a new accepted full bootstrap.
Calendar-only recovery is clearly segregated from guarded financial certification;
it does not replace the accepted 224-migration/finance baseline.

Latest source lint and diff checks pass; original Customer and Laravel previews
restart cleanly and their public/read-only endpoints return HTTP 200.
No current-role browser acceptance is claimed. Full B1 certification remains
**PARTIAL**, and downstream gates remain unrun.

### Recurrence correction receipt — after creator confirmation

`recurrence-unit-junit.xml`: **27 tests / 48 assertions PASS**, one existing
PHPUnit configuration deprecation. Daily/weekly/monthly/custom, inclusive date,
exact count, no pre-origin occurrence, month skipping/leap anchors, native weekly
payload and single-day/window-slicing equivalence. Invalid rules fail closed.

`recurrence-certification.json`: MySQL **8.0.42 / RR**, **75 checks PASS** on
the restored calendar-only disposable clone. Real MasterRepository discovery and
BookingCapacity interval validation agree for never/date/count ends, old origins,
custom intervals/weekdays, monthly skips and closed-date counting. Public starts
do not overlap the original 09:30–10:30 daily case; 10:30 adjacency remains valid.
Native cleanup retains a still-live custom counted rule and the inclusive final
date, while existing expired date-ended cleanup still works. Default-horizon and
explicit clamped-horizon discovery also preserve old-origin recurrence.

Fixtures run inside a rolled-back transaction; snapshotted available Bookings/
money tables are unchanged. This is not a fresh guarded financial or complete
BookingService creation/race pass. Retained failure/before-correction receipts
remain intact. No owned or production scheduling rows were changed.

Counted metadata is deliberately retained rather than deleted with guessed
expiry. Exact counted-rule garbage collection remains separate bounded maintenance;
it must not become a new scheduling rule or affect economic identities.

`final-boundary-check.txt` confirms the final typed internal-evidence signature:
forged public private/exclusion flags are ignored, and the exact disabled-end
adjacency remains valid. All four changed PHP files lint cleanly.

### All requested decision answers

| Brief item | Answer / current classification |
|---|---|
| 1 Overlap reproduced? | **VERIFIED:** original right-partial calculator and recurring-range defects retained; corrected native calculator/service and recurring interval cases reject. No whole-gate/role PASS inferred. |
| 2 Same-slot race reproduced? | **VERIFIED corrected contention:** three independent native connections, one successful durable same-slot Cash booking; loser rejects after the winner commits. No claim of demonstrated dual creation in the old source. |
| 3 Exact capacity owner? | Specialist User identified by `Booking.master_id`, globally across that master's assignments/Shops. |
| 4 Final exclusion mechanism? | **VERIFIED finite B1:** fresh-owned RR; known assignment PKs and sorted specialist User PK mutexes before first consistent read; shared authoritative intervals/hours. Native cascade/terminal/calendar-setting receipts pass. |
| 5 Can two Customers receive one slot? | **TESTED pair: no.** One durable admission, no losing financial effects. Not a blanket certification of every scheduling path. |
| 6 All overlaps correct? | **VERIFIED required finite set:** ordinary/recurring intervals, native contention, lifecycle and midnight boundaries pass. This is not a claim about every hypothetical future scheduling feature. |
| 7 Legitimate adjacency? | **CONFIRMED and native verified:** half-open endpoints, processing included once; before/after adjacency and mixed-duration boundary offered. |
| 8 Independent resources progress? | **VERIFIED:** independent specialist progresses; same specialist across applicable Shops/assignments is serialized in native B1. |
| 9 Stale availability? | Protected creation recalculates after resource lock; losing request rejects. Existing stale outer RR rejected. No client request flag can supply internal exclusions. |
| 10 Rollback capacity-safe? | Native downstream accounting and Wallet-write injection restore tested booking/financial state; same slot succeeds after rollback. |
| 11 Cancellation release? | **VERIFIED supported native contract:** release/repeat/rollback/terminal contention pass. Unsupported linked original-context cancellation stays fail-closed; no fabricated refund. |
| 12 Atomic rescheduling? | **VERIFIED finite B1:** native Cash/Wallet and cascade/time-change tests preserve economics; failed destination retains original, old slot releases and destination occupies. |
| 13 Retry/double click? | Same-slot Cash/Wallet retry rejects without another booking/debit/contribution. No new request identity; not certified for a replay that intentionally changes slots or for all network retries. |
| 14 Authoritative timezone? | UTC/naive baseline retained. Native midnight checks pass; selected Customer/Vendor/Admin display the persisted 09:00 and 11:00 appointments without an observed shift. No Shop-timezone redesign. |
| 15 Midnight/timezone/DST? | Midnight outside tested same-day hours rejects. UTC has no DST fold; Customer/Vendor wall-clock interpretation still **PARTIAL/unaccepted**. No invented conversion contract. |
| 16 Economics changed? | No intended financial semantics/code change; tested Cash/Wallet rollback/replay/reschedule preserve original financial rows and funding. Scheduling correction does not reprice originals. |
| 17 Schema changed? | **VERIFIED:** no owned/permanent schema change; separate disposable table clone only. |
| 18 Existing finance changed? | No owned financial rows written by this work; isolated synthetic service financial effects are tested. Retained protected-state comparison remains unchanged, not a new live production check. |
| 19 Fingerprints match? | Latest retained comparison: 59 protected tables/465 schema objects unchanged, serialization/order/count pinned. No owned schema or source data migration performed. |
| 20 Legacy Orders unverified? | **VERIFIED:** all 12 remain unverified. |
| 21 Customer selected journey? | Native UI Cash/Wallet creation and reload/history accepted; post-Vendor transition and final responsive history receipts are in section 17. Live account email remains external. |
| 22 Cash booking? | **VERIFIED native/UI:** zero-Wallet Customer108 creates Cash booking1 at 09:00–10:00/100, reload persists. Offline Cash is not falsely marked remotely paid. |
| 23 Funded Wallet booking? | **VERIFIED native/UI:** Customer101 creates Wallet booking2 at 11:00–12:00/100; balance 5000→4900 from accepted synthetic opening evidence, not Admin adjustment or live top-up. |
| 24 Wallet replay? | Baseline 28-group and native admission replay retained; UI reducer prevents lost draft/double submission. Financial reservation same-input retry returns one existing ID; no second debit or remote action. |
| 25 Vendor selected journey? | **VERIFIED selected UI:** booking1 new→progress→ended; fresh Vendor113 Shop503/service103/invitation/assignment105 setup, with only owned assignment105 displayed. |
| 26 Vendor isolation? | **VERIFIED bounded:** native forged Shop/service/staff IDs reject; UI foreign assignment104 disappears under Shop503 filter while owned105 remains. |
| 27 Admin selected journey? | **VERIFIED selected UI:** Admin107 sees bookings/transactions, approves Shop504 and applicant becomes Seller without Admin menus; only identified RESERVED receipts canceled. |
| 28 1280/390/320 role screens? | Authenticated selected Customer/Vendor/Admin receipts recorded, with targeted mobile filter/calendar corrections. Final Customer history evidence is in section 17; deferred purchasing is not certified. |
| 29 Security gates? | **VERIFIED bounded application:** native auth/reset/email/429, cross-role/Shop, uploads/gallery, conditional CSRF, explicit bearer identity and selected SMTP redaction pass. Production TLS/cookies and historical rotation remain dependencies. |
| 30 Communication/background gates? | **VERIFIED local application:** 22 outbox/process/PHPMailer/reminder checks and four selected-scheduler checks pass; encrypted payload, commit ordering, UNKNOWN/no-resend and failed-job visibility. No live SMTP or broad scheduler. |
| 31 External services? | Transactional email/verification delivery remains **EXTERNAL DEPENDENCY**; live transport not tested. |
| 32 P0 remaining? | Native B1 closed; no new independent financial P0 demonstrated. Distinct request keys explained earlier reservations; final unchanged-key UI retry passes. Deferred Product stock P0 is not selected MVP scope. |
| 33 P1 remaining? | Live account transport, production TLS/cookie/proxy and rotation evidence, D1/R1/O1 release/cutover/restore/supervision/monitoring remain. Application and external readiness are separate in the single matrix. |
| 34 Deferred? | Product purchasing/stock/multi-Shop, electronics, Vendor-direct/own gateway, payouts, Delivery expansion, SMS/push expansion, speculative architecture. |
| 35 Product excluded? | **VERIFIED:** selected MVP excludes purchasing; no stock correction or checkout activation. |
| 36 Electronics disabled? | **VERIFIED:** no configuration/activation change; Cash/Wallet-only catalogue retained. |
| 37 External payouts unavailable? | **VERIFIED:** no execution activated; reservation is not payout. |
| 38 Cash + legitimate Wallet sufficient? | Yes for selected scope after booking/P1 acceptance; Cash must work at zero Wallet balance. Not a readiness claim today. |
| 39 VPS staging ready? | **NO-GO / BLOCKED.** |
| 40 Exact staging blockers? | No separate D1/R1/O1 approval; production data/key/runtime/cookie/proxy/TLS/compatible release/restore/monitoring rehearsal absent. Live account email remains external. Do not reopen closed native B1 defects. |
| 41 Boundaries permitting staging? | B1 and bounded selected application evidence support a later isolated staging proposal, not execution or a public launch. Separate approval still required. |
| 42 Verify in later staging? | Compatible production builds/runtimes, native MySQL RR/strict guards, TLS/proxy/cookies/least privilege, persistent media, supervised reviewed worker/scheduler, restart and rollback, selected-role journeys. |
| 43 Backup/restore? | Section 11: encrypted DB/PITR/media/key recovery, isolated timed restoration, guards/decryption/fingerprints/conservation/roles, achieved RPO/RTO and failure alerts. Not performed. |
| 44 First-release monitoring? | Section 11: frontend/API/DB availability, redacted errors, queue/scheduler liveness, locks/connections/disk/memory, auth abuse/TLS expiry, retained UNKNOWN/refund/intervention states. |
| 45 New readiness range? | **Approximately65–75%, central score70%**, based on native and actual selected-role acceptance, not code/report completion; U1 browser gap retained in section17. |
| 46 Derivation? | Same20 gates: Customer5 + Vendor3.5 + Admin3 + operations2.5 =14/20 (70%), versus8.5/20 baseline (42.5%). No production/restore/monitoring credit; no full UI cancellation/reschedule credit. |
| 47 Coding completion? | No: evidence-weighted limited-launch readiness only, not effort/time/code percentage. |
| 48 Safest next bounded work? | Finish current selected-role receipt, retain protected comparison and stop before separately approved D1/R1/O1. Closed recurrence/B1 semantics are not reopened; deferred features remain excluded. |

**Stop honored:** no speculative scheduling policy was installed to make tests
pass, and no downstream journey was claimed accepted before booking correctness.

## 17. Limited-MVP same-run forward acceptance — 2026-10-04

This is the continuation of the same readiness report/evidence bundle, not a
competing backlog or a production certificate. The 8.5/20 (42.5%) figure above
is the retained **pre-run baseline**; the final matrix will use only completed
selected acceptances, not count fixed files or partial screenshots as gates.

### Native B1: PASS; downstream work continues

- Native execution receipt: 143 checks pass; terminal contention: 14 checks pass.
- Recurrence: 27 tests/48 assertions and 75 native calendar checks pass under
  the confirmed half-open/full-hour and origin-based occurrence contracts.
- Owned protected baseline after B1: 59 fingerprints and 465 schema objects
  unchanged. Twelve legacy Orders remain unverified; no reinterpretation.
- Native disposable MySQL is persisted separately from the owned SQLite store.
  No Product/provider/Delivery, electronic activation, production or staging action.

### Selected account/ownership and Customer receipts

- Fresh Vendor113/Shop503: native registration/verification, application,
  approval, branch103, Service103 (100/60), accepted invitation10001 to Master102,
  and assignment105 persist. Forged Shop/branch/Service assignments reject.
- Invitation acceptance derives trusted Shop ownership from the recipient's
  invitation. Staff assignment ownership and Shop-scoped withdrawal reject
  unrelated actors and preserve unrelated Shop assignments.
- Protected booking reads reject anonymous/other Customer/unrelated Vendor/
  different specialist actors. Rightful Vendor/specialist/Admin reads pass;
  Customer/Vendor Admin-only route access rejects.
- Customer108 submitted Cash booking1 through UI at zero Wallet availability:
  Oct5 09:00–10:00, assignment101, total100, reloaded receipt/history agree.
- Customer101 submitted Wallet booking2 through UI:
  Oct5 11:00–12:00, total100, reloaded receipt/history agree. Native MySQL confirms
  user101/assignment101 and persisted Wallet4900 from5000.
- Fresh unsubmitted date flow passes at1280/390/320: no horizontal document/body
  overflow; calendar/time/summary/continuation controls reachable and hit-testable.
  Completed selections are cleared; a new booking no longer reuses occupied
  dates. Assignment-specific date restoration and invalidation remain separate.
- Six booking draft/reducer regressions pass. Changed-source JSX syntax passes;
  an interrupted full native typecheck is **not** counted as passing.

### Selected security receipts to date

- General email codes are random, recipient-bound, keyed-digest stored,
  ten-minute expiring and single-use. Native expiry/replay/wrong-recipient/
  legacy-global-GET rejection and neutral resend tests pass.
  Dedicated high-entropy Driver verification remains separate.
- Narrow auth verification uses the named path limiter, not the shared numeric
  API/IP bucket. Existing native login/429/reset expiry/replay receipts retained.
- Selected profile/Shop/Service raster uploads reject scripts, active SVG,
  spoofed content and oversize files; native authenticated PNG upload passes
  with actor/UUID filename and content-derived extension. Deferred upload
  categories are unchanged.
- Actual selected profile read/mutation rejects session-cookie-only requests
  with401; explicit bearer read passes. Conditional CSRF protection covers any
  protected dashboard route using a web-session principal without a bearer.
  Production secure-cookie/proxy/domain settings are requirements, not silently
  enabled in local development.

### Vendor/Admin and same-intent financial UI — PASS

Native Vendor103 moved Cash booking1 new→progress→ended through the actual UI.
Booking status does not falsely change its separate Cash transaction into an
electronic paid receipt. Admin107 observed the selected bookings/transactions
and approved Shop504; its applicant became Seller without Admin menus.
Fresh Vendor113 completed Shop503/service103/invitation10001/assignment105 setup.
After correcting the Shop filter, its Service master screen contains owned105
and excludes foreign104. Native forged-ID receipts remain authoritative.

Authenticated desktop/mobile work covered1280/390/320. Corrected Admin booking
type controls wrap and their filters operate; tables scroll internally. Vendor
Day calendar/filter controls fit320 (document/body305), with a minor3px
left-edge sidebar-toggle bleed, not a calendar overflow. Selected financial
panels/forms/receipts remain reachable. These are targeted role receipts,
not a certification of deferred Product screens or every responsive route.

Four test50-unit reservations were initially distinct within-capacity intents;
different request keys, not duplicated custody/effects. The correction preserves
form input and retry identity through same-transaction parent refresh and makes
new intent explicit. Final Vendor103 test retained input50 and returned the
same operation c960ced6-33cb-4ec4-85be-73b29955bd85 on repeat, once in history.
Admin107 canceled only that and the three explicitly identified test receipts.
After reload: four CANCELED, zero RESERVED; retained UNKNOWN
7ea12b83-fea2-47af-b645-79b6e1e1b5f1 remains UNKNOWN,25 integer units
(0.25USD at scale2), with no cancel action. Vendor payable100USD and
commission0 remain; no external funds dispatched/executed/reconciled.
Operation IDs and exact scale are exposed; no separate available-capacity figure
is claimed. A reservation is never represented as a payout.

### N1 — local selected application PASS; SMTP external

`n1-acceptance.json` retains22 native checks: committed outbox/queue ordering,
rollback, recipient/event dedup, encrypted challenges, real worker loss/restart
before send and after local transport acceptance, live claim protection,
UNKNOWN/no resend, visible failed jobs, expiry/superseded-code rejection,
opt-out-safe late reminders, notification rollback and secret-free status/logs.
Templates and envelopes were rendered at a local PHPMailer boundary; no real
recipient, SMTP provider or live delivery was activated.

One additive **operational** selected-email outbox is applied only in disposable
native MySQL; it is not financial/capacity/resource authority. Populated delivery
evidence cannot be removed by routine migration rollback. Selected account work
uses database queue `mvp-notifications`. In-app booking state/history survives
independently of reminder/email availability. Booking email is not an existing
supported transport here; it is not falsely claimed delivered.

`n1-scheduler.json` adds four PASS checks for the actual native Laravel scheduler:
the explicit selected schedule contains only `mvp:background-tick`, excludes
reconciliation/provider/cleanup/auction/raw auto-ended commands and safely runs
without Wallet change or external mail. Native scheduler children use the isolated
bootstrap, not owned artisan. No general cron or production supervisor was enabled.

Later approved configuration must select
`AGENDAALLY_SELECTED_MVP_SCHEDULER_ONLY=true` **before** running the Laravel
scheduler, and supervise only the selected notification queue. The legacy broad
schedule is not a reviewed limited-MVP release instruction. UNKNOWN acknowledgement
requires intervention, never automatic resend. Live mail delivery, SMTP/TLS
configuration and production worker/scheduler supervision remain dependencies.

### S1 and final preservation

Native auth/reset/address/email, booking/staff/gallery ownership, upload and
session/CSRF receipts are retained. After outbox changes, email expiry/replay
checks and conditional CSRF/explicit-bearer/SMTP-debug checks passed again.
Six native address CRUD/validation/ownership checks use the real User response
envelope, required native country/region and no invented map location.
Seven draft/intent regressions and final changed JSX/PHP syntax checks pass.
Production HTTPS/proxy/domain/secure-cookie evidence and historical credential
rotation are **not** inferred from isolated local tests.

The protected final comparison retains59 row/value fingerprints and465 owned
schema objects unchanged; all12 legacy Orders remain unverified. Accepted financial
and historical failure receipts remain in the same evidence bundle.

### Final Customer lifecycle receipt

Customer108 normally signed in and reloaded `/appointments`: booking1 is Ended,
05Oct09:00–10:00, total100USD/Cash100USD, zero discount/service fee/extras and
no falsely paid indicator. At1280×720,390×844 and320×700 the document does not
overflow horizontally; detail and policy rows remain reachable by vertical scroll.
Its own profile shows Wallet0; Customer101's Wallet receipt must never be expected
under Customer108. The earlier separate Customer101 UI booking/reload receipt
remains valid; native Wallet price4900 and booking2 NEW11:00–12:00 remain unchanged.

The active browser then closed before another Cash booking was submitted.
Navigation and DOM access failed with `Browser closed` despite a stale page
handle reporting open. Recovery closed unused Business contexts; creating a fresh
normal Customer101 context failed before page creation with
`browser.newContext … Browser closed`. No new tester, API login, extra booking,
refund/provider/UNKNOWN action or database mutation was used to bypass it.

**Remaining U1 evidence:** final Customer101 Wallet/mobile recheck and real UI
Cash reschedule/cancellation reflection. Native atomic cancellation/reschedule
receipts pass, but no missing UI flow is claimed accepted. This is an actual
browser-tool/runtime blocker, not a new financial/business defect. Resume after
the browser runtime is relaunched; do not spend more follow-ups calling
newContext on the same closed browser.

Temporary `AGENDAALLY_DEV_API_TARGET` was restored to its original absent state.
Customer/Business previews are paused rather than silently exposing native test
identity to the owned8000 baseline. The disposable native database is retained;
no protected owned money/schema or legacy classification was altered.
D1/R1/O1, live email and production remain unstarted and separately approval-gated.

## 18. Current limited-MVP forward conclusion — 2026-10-04

**READY TO ENTER STAGING.** This is permission to propose a separately approved,
isolated D1/R1/O1 rehearsal, not a production release or approval to execute it.
The baseline for this work order was14/20=70%; the same gates now total
**14.5/20=72.5%**. Only the Vendor calendar lifecycle half-gate closed; report,
tests, screenshots, inventory and design proposals earn no independent credit.
Sections1–17 and their failures are retained as historical evidence. The prior
closed-browser blocker and remaining selected U1 UI receipts are superseded by
the receipts below.

### Phase 0 — normal demo restored first, no reseed

The demo was **not deleted**. A development-scoped API target8008 exposed the
disposable acceptance database instead of the existing owned SQLite demo on8000.
The normal8000 preflight also detected the added selected-email migration in the
source manifest. The reviewed source/predecessor fingerprint was accepted for
serving; this did **not** approve or apply that migration to the owned database.
The development/shared API override was removed. Dedicated acceptance frontends
used per-process targets only; they are paused and normal previews restored.

No seeder, DatabaseSeeder, migration, reset or media replacement was executed.
The persisted **DevelopmentDemoSeeder** catalog and reviewed catalog/branch/
Service/demo seeders were the authoritative existing data source, not a newly
run restoration fixture.

| Owned demo measure | Before | After |
|---|---|---|
| Approved, visible Shops | 9 | 9 |
| Services | 51 | 51 |
| Service/Specialist assignments | 75 | 75 |
| Distinct assigned Specialists | 14 | 14 |
| Countries / cities | 4 / 7 | 4 / 7 |
| Gallery records / existing media files | 66 / 90 | 66 / 90 |

Normal Customer storefront, photos, location recommendations, Service detail and
availability were browser-verified at1280/390/320 before acceptance continued.
Bridal Service6 / Specialist112 retained Oct5 slots09:00,10:40,12:20,14:30,15:40.
“Restored” means restored correct routing/serving of preserved records and media,
not insertion of synthetic rows or replacement photographs.
Receipts: `forward-demo-data.json`, `forward-demo-browser.json`,
`forward-demo-availability-browser.json`, `protected-demo-restored.json`.

### Phase 1 — U1 VERIFIED for the selected MVP

- Customer101 Wallet booking2: Oct5 11–12, $100, one debit, balance4900,
  saved receipt/history/reload/no duplicate accepted at1280/390/320.
- Customer108 retained ended Cash booking1, created exactly one new Cash booking5
  at10–11, $100. Vendor rescheduled5 to13–14; saved Customer history/receipt and
  Vendor/Specialist detail reflect13–14 without repricing, new payment or duplicate.
- Vendor created one local client and one UNPAID local booking6 at15–16, $100.
  Existing SellerStoreRequest prohibits local-client payment selection: this is
  **Pending—no collection**, not an invented Cash payment. Draft stays separate
  from confirmed events; no10–10:30 ghost or occupied draft remains.
- Vendor canceled6 once. The missing integration now calls the **existing**
  canonical unfunded allocation writer inside the booking transaction. It still
  rejects held money, finalization and unresolved receipts. Rollback-only checks
  passed6 assertions, including funded Wallet rejection and eight unchanged
  financial fingerprints. No refund or accounting-policy bypass was introduced.
- Customer canceled5 through normal UI once. Success, Canceled history after
  reload, one5 entry, original13–14/60min/$100/Cash receipt accepted at all three
  widths. Wallet108 remains0; no credit or collection was manufactured.
- Public native availability separately confirms13:00 and15:00 released, with
  funded11:00 still disabled. Canceled historical calendar cards may remain
  visible; that does not mean occupied capacity or deleted history.
- Specialist102: own saved Wallet2 and Cash5 detail, status options, assignment,
  weekly08–18 hours/closed-date page and empty blocked-time list read normally.
  Detail fits1280/390/320. No Specialist status/assignment/hours/payment was changed.
  Saved detail now reads persisted records, not a creation quote; optional form
  metadata is handled without a crash. Three accepted Shop invitations keep the
  existing ambiguous-context navigation fail-closed. No Shop/branch selection or
  permission expansion was added.
- Prior accepted Admin approvals, financial history, authorization, same-intent
  reservation retry/cancellation, UNKNOWN visibility and intervention boundary
  receipts remain accepted. They were not repeated or promoted to payout proof.

Ordinary failures and superseded receipts remain retained: draft event leak,
edit setter, misleading local-client Cash fallback, fixed-width drawer, linked
unfunded cancellation, Master missing-start quote and omitted form metadata.
All fixes preserve B1 capacity, recurrence, timezone and financial contracts.
Non-blocking Redux Date/Ant Design warnings and a dev-tools overlap remain
cosmetic follow-up, not a hidden failed journey. Tablet is proposed, not certified.

Current receipts under `.local/booking-forward/`:
`forward-customer-wallet-cash-browser.json`,
`forward-vendor-master-corrected-browser.json`,
`forward-vendor-master-final-browser.json`,
`forward-master-final-browser.json`,
`forward-customer-cancel-auth-browser.json`,
`forward-linked-cancellation-check.json`,
`forward-availability-release-check.json`,
`forward-final-native-state.json`. Earlier failed `forward-*failure.json` files
remain in the evidence bundle.

### Phase 2 — A1, local N1 and selected S1

**A1 VERIFIED locally:** retained native auth/reset/profile/address CRUD,
booking/staff/gallery ownership and isolation receipts remain accepted. Customer
normal UI logout and wrong-password login were exercised; generic, non-enumerating
errors were visible and the first observed429 showed “Too many attempts. Please
try again later.” No raw trace or user-existence disclosure was shown. The observed
block was submission7; six preceding responses were400. Configured named buckets
remain5/min per account+IP and30/min per IP;12/min was a test expectation error,
not a business policy. No source limit was changed to satisfy it. UI does not
display a retry duration; broader network/timeout variations retain partial credit.

**N1 VERIFIED locally:** retained22 native outbox/worker/transport checks and four
selected-scheduler checks pass. Local PHPMailer boundary, deduplication, claim/
restart, rollback, expiry, visible failed work and UNKNOWN/no automatic resend are
accepted. Live SMTP/TLS/delivery and production supervision are external/operational
dependencies. Booking in-app history is independent; unsupported booking email,
SMS and push are not claimed delivered.

**S1 VERIFIED for selected local scope:** retained auth/session/CSRF, email
challenge, address, staff/booking/gallery ownership, upload and sensitive
logging/response checks remain accepted. No general security re-audit or954-test
hardening rerun was used as a substitute for selected acceptance. That earlier
suite failure remains a fixture/stale-test receipt, not a new financial P0.
Production cookie/proxy/TLS and provider-side historical credential rotation
remain unverified; this does not authorize reuse of historical credentials.

### Phase 3 — complete scheduling inventory and assessment only

The counting rule is independently owned role-specific surfaces/editors, not
every import, dependency or ordinary date filter. **17 selected implementation
units across eight role workflows** exist: five Customer discovery/booking and
Admin/Vendor/Specialist calendar surfaces; three weekly-hours/closed-date editors;
six blocked-time editors; three recurrence editors. Selected UI uses four engine
families: react-day-picker, react-datepicker, react-big-calendar and Ant Design
date/time inputs. The wider source also uses react-mobile-datepicker in deferred
Product delivery: five families overall, not five selected-MVP calendar engines.

Three calendar trees repeat providers, event mappers, detail sheets/cards, status,
service, block and recurrence forms. Three role-specific availability editors
repeat controls. Customer Day.js/date-fns and Business Moment are distinct
formatting stacks. Customer backend slots and operational historical events have
different meanings. Working-hours, closed dates, blocks and recurrence require
common presentation, not a shared authority engine.

Exact selected paths and neighboring non-MVP date controls are inventoried in
`scheduling-inventory.md`, included below in the same report. Admin already has
booking list/detail/status surfaces; list/search/date-range/Shop/Vendor/Specialist/
status filters and intervention detail are the appropriate default task. No new
Admin calendar is required for visual consistency.

Three distinct directions are documented below:
1. **Task-focused booking and scheduling — recommended AFTER MVP.**
2. Staff-grid operations workspace — LATER, if parallel staff workload justifies it.
3. Compact agenda-first scheduling — AFTER MVP pilot for low-density schedules.

Share date/time presentation preserving wire values, accessible date/slot controls,
toolbar, appointment card/status chip, summary/detail sheet and composed hours/
block/recurrence fields within each frontend first. Keep permissions, role APIs,
actions, Shop/branch scope, Customer slot selection and Admin intervention distinct.
No redesign, prototype, component library, new capacity or permission model was
implemented. Existing unusable controls were fixed; modernization must not delay MVP.

### Phase 4 — same 20 gates, no new framework

| Existing gate | Current classification | Credit |
|---|---|---|
| C1 Customer access/reset | PARTIAL — local A1 accepted, live email absent | 0.5 |
| C2 Discovery/location/detail | VERIFIED | 1 |
| C3 Exclusive booking lifecycle | VERIFIED — B1 retained | 1 |
| C4 Native payment | VERIFIED — Cash/Wallet only | 1 |
| C5 Visible history/receipt | VERIFIED | 1 |
| C6 Responsive error/retry | PARTIAL — selected widths/errors/retries accepted; broader network failures not claimed | 0.5 |
| V1 Onboarding/Shop | VERIFIED | 1 |
| V2 Service/staff setup | VERIFIED | 1 |
| V3 Calendar lifecycle | VERIFIED — previously0.5; actual UI reflection now accepted | 1 |
| V4 Financial history/operations | VERIFIED — reservations/history, not external payout | 1 |
| A1 Admin approval/user oversight | VERIFIED | 1 |
| A2 Financial boundary authorization | VERIFIED | 1 |
| A3 UNKNOWN/provider/intervention visibility | PARTIAL — local UNKNOWN accepted, external intervention absent | 0.5 |
| A4 Refund/cancellation operation | PARTIAL — reservation cancellation accepted, real electronic refund deferred | 0.5 |
| O1 MySQL DDL/financial integrity | VERIFIED | 1 |
| O2 Production runtime/TLS/cutover | BLOCKED — no D1 execution | 0 |
| O3 Restore | BLOCKED — no R1 execution | 0 |
| O4 Communications/background reliability | PARTIAL — local N1, not live delivery/supervision | 0.5 |
| O5 Observability | BLOCKED — no O1 operational rehearsal | 0 |
| O6 Authenticated responsive acceptance | VERIFIED | 1 |
| **Total: Customer5 + Vendor4 + Admin3 + operations2.5** | **14.5/20 =72.5%** | **14.5** |

Gate labels are the existing twenty; A1/O1 row shorthand here is not a renamed
work-order framework. Only V3 materially increased, by0.5. A1 account verification
and local N1/S1 confirmations do not double-count already earned points.

Remaining selected P0: **none identified**. Remaining P1: operational runtime/
TLS/proxy/cookie/secret isolation, historical credential rotation evidence,
encrypted restore/PITR and measured RPO/RTO, queue/scheduler supervision and
monitoring; live account-email delivery before an email-dependent public launch.
Broader network/error variations retain partial credit. No selected U1 defect
remains a staging-entry blocker.

External dependencies: approved isolated staging host/runtime/TLS and persistent
storage, SMTP/TLS/delivery configuration, backup/binlog storage, alerting and
provider-side historical credential rotation evidence. Do not treat these as
completed by this report.

Deferred unchanged: Product purchasing/stock, multi-Shop checkout, all MTN/
Orange/Flutterwave/Paystack/Stripe/PayPal activation, Vendor-direct/own-gateway,
external Vendor payout, electronic refund/reconciliation, Delivery/SMS/push
expansion, PostgreSQL conversion, Strategy G and speculative infrastructure.
Product browse-only discovery is not purchase readiness.

### Phase 5 — bounded D1/R1/O1 work order prepared, NOT executed

Entry decision: **READY TO ENTER STAGING**. Finite staging-entry blockers: **none
in the selected local application scope**. Separate approval, an isolated target
and injected staging-only secrets are prerequisites for beginning the next work
order, not evidence that it has run. Public production launch is **not approved**.

**Bounded next-phase scope:** one isolated staging release rehearsal using
sanitized/disposable data, Cash/Wallet test-only scope and local/sandbox mail sink.
Do not copy the owned financial DB into a writable environment or reuse the
acceptance bootstrap's public synthetic APP_KEY. Deny external electronic
activation, real payouts/refunds and unreviewed scheduler jobs.

**D1 runtime rehearsal plan:**
- Build Customer Next and Business production bundles once with explicit
  release provenance; build success alone is not runtime acceptance.
- Supported PHP plus reviewed Laravel-required extensions under PHP-FPM;
  supervised Next production runtime and static Business build behind Nginx.
- TLS, `APP_ENV=production`, `APP_DEBUG=false`, one stable staging-only APP_KEY,
  injected secrets, trusted proxy/CORS, HTTPS secure/HttpOnly/SameSite cookies.
  Verify forwarded host/protocol, authenticated sessions and no internal debug
  output. Do not reuse imported historical provider credentials.
- MySQL8/InnoDB, REPEATABLE READ and strict mode; least-privilege app/worker users
  separate from reviewed migration authority. Review all source migrations,
  canonical triggers and selected operational outbox on the isolated target.
  Serving the owned224 predecessor never approved an owned migration.
- Persistent media with storage links/permissions, selected queue
  `mvp-notifications` supervised, explicit selected-only scheduler switch before
  scheduler execution. Exclude reconciliation, broad cleanup, auction and raw
  auto-ended jobs. Verify worker/scheduler restart without duplicate delivery.
- Record release manifest, health probes and bounded release rollback preserving
  media/APP_KEY/DB compatibility. No routine rollback deleting populated evidence.

**R1 restore plan:**
- Encrypted MySQL backup plus binlog/PITR policy, media backup and separate safe
  APP_KEY/config recovery. Define the recovery point and retain provenance.
- Restore into a separate isolated target with no outbound delivery/provider
  actions. Verify schema/triggers, protected fingerprint serialization, financial
  conservation, encrypted-data decryption and actual restored media.
- Run role smoke tests against restored data. Measure rather than assume RPO/RTO;
  compare restored evidence with the chosen backup/PITR target. Do not change
  protected/legacy classification to manufacture a passing restoration.

**O1 observability plan:**
- Frontend/API/DB health; worker liveness, selected scheduler heartbeat and failed
  jobs; HTTP/application errors; MySQL locks/connections; CPU/memory/disk/inodes.
- TLS expiry and auth abuse/429; UNKNOWN/PENDING operations and refund/intervention
  failure alerts. UNKNOWN acknowledgement requires intervention, never automatic
  money retry or email resend.
- Redact credentials/tokens/request-sensitive payloads; alerts must name an owner,
  threshold, escalation and safe response. Test alert delivery with synthetic
  failures only. Operational acceptance requires observed detection, not config
  files or screenshots alone.

**Exit before any production proposal:** record actual D1 runtime/rollback,
R1 restore/conservation/decryption/media and measured recovery, O1 detection/
supervision evidence; live account-email acceptance where required; credential
rotation evidence. Reassess the same gates. Stop for new financial/security P0,
custody/resource/permission changes, destructive migration or external action.
No VPS or production deployment was executed here.

### Current explicit answers to all 38 requested questions

| # | Answer |
|---|---|
| 1 Original demo returned? | Yes; correct normal serving restored and browser-verified. |
| 2 Why disappeared? | Development API override pointed to8008 acceptance DB;8000 manifest preflight also needed source/predecessor review. |
| 3 Deleted/filtered/absent/another DB? | Not deleted; in preserved owned SQLite, masked by another DB. No synthetic replacement. |
| 4 Which seeder restored it? | None was run. Existing authoritative DevelopmentDemoSeeder data was reused after routing/preflight repair. |
| 5 Visible demo Shops? | 9 approved/visible. |
| 6 Restored Services? | 51 existing Services. |
| 7 Restored Specialists? | 14 distinct assigned Specialists,75 assignment links; not a claim about all user accounts. |
| 8 Media restored? | Yes;66 gallery rows/90 existing files and storage link retained, actual photos load. |
| 9 Restoration changed protected finance? | No. |
| 10 Start readiness? | 14/20=70%. |
| 11 Readiness now? | 14.5/20=72.5%, limited scope; not production readiness. |
| 12 B1 still VERIFIED? | Yes, accepted immutable native/race/recurrence receipts retained. |
| 13 U1 VERIFIED? | Yes for selected Customer/Vendor/Specialist/Admin scope at1280/390/320. |
| 14 A1 VERIFIED? | Yes locally; live account-email transport remains external. |
| 15 N1 VERIFIED locally? | Yes;22 native and four selected-scheduler checks retained. |
| 16 Live communication dependency? | Approved SMTP/TLS/delivery and operational worker/scheduler supervision. |
| 17 S1 VERIFIED selected MVP? | Yes locally; production TLS/cookies/proxy and historical rotation proof remain unverified. |
| 18 Cash semantics changed? | No; offline Cash remains distinct from collection proof; local-client booking is UNPAID, not Cash. |
| 19 Wallet semantics changed? | No; one verified synthetic Wallet debit, currency/authority and funded-cancellation guard preserved. |
| 20 Protected financial records changed? | No; only disposable synthetic acceptance lifecycle records changed. |
| 21 All59 fingerprints match? | Yes; protected-forward-final.json reports no changed tables. |
| 22 All465 schema objects match? | Yes. |
| 23 All12 legacy Orders unverified? | Yes. |
| 24 Real provider call? | No; isolated HTTP fake/disabled provider configuration. |
| 25 Real payout/refund? | No. |
| 26 Production deployment? | No; no staging execution either. |
| 27 Calendar/date/time count? | 17 selected implementation units/eight role workflows; four selected UI engine families, five wider-source families including deferred delivery. |
| 28 Roles? | Customer discovery/booking, Admin/Vendor/Specialist calendars, role-specific hours/closed/block/recurrence; Admin list oversight. |
| 29 Recommended design? | Task-focused booking and scheduling, not a universal calendar. |
| 30 Before/after MVP? | AFTER MVP; only unusable controls were fixed now. |
| 31 Remaining P0? | None identified for selected scope. Product purchasing would reopen separate stock risk only if added; it stays deferred. |
| 32 Remaining P1? | Runtime/TLS/proxy/cookies/secret and rotation evidence, restore/PITR/RPO/RTO, supervision/monitoring and public account-mail acceptance. |
| 33 External dependencies? | Isolated target/TLS/storage, SMTP, backup/binlogs/alert delivery and provider-side historical rotation evidence. |
| 34 Deferred? | Product/stock, multi-Shop, all electronic activation/direct gateways/payout/refund/reconciliation, Delivery/SMS/push, PostgreSQL, Strategy G and speculative infrastructure. |
| 35 READY TO ENTER STAGING? | **READY TO ENTER STAGING**, no execution or production approval. |
| 36 If NO-GO, finite blockers? | Not NO-GO: zero selected staging-entry blockers; separate target/secret/approval prerequisites remain before executing the prepared work order. |
| 37 New same20 score? | 14.5/20=72.5%; only V3 increased0.5. |
| 38 Single safest next phase? | Separately approved isolated D1/R1/O1 runtime, restore and observability rehearsal; no live financial action. |

Final receipts: `protected-forward-final.json` reports59 unchanged/465 schema
match/12 unverified; `forward-final-native-state.json` reports Wallet1014900,
Wallet1080 and all retained financial operations unchanged. Transaction1501
remains Paid/internal Wallet100/allocation2/context1; external settlement is not
verified. UNKNOWN25-unit operation and four CANCELED50-unit operations remain
unchanged. These are not real payout or refund confirmations.

### Scheduling inventory — exact source-owned units

All paths below are relative to `.migration-backup/`. A numbered family counts
once even when it has create/edit/list wrappers. Ordinary report filters, birth
dates and deferred delivery/table-reservation controls are not additional
selected scheduling units.

| Units | Role | Exact source |
|---|---|---|
| 1 | Customer booking date/slots | `web/app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/date-time/date-time.tsx`; caller `shops/[id]/booking/date/bookingDate.tsx` |
| 2 | Customer discovery date/time pair | `web/components/search-field-core/date-select.tsx`, `time-select.tsx`; `/search/date`, `/search/time` callers |
| 3–5 | Admin, Vendor, Specialist operational calendars | `admin/src/views/calendar/`, `views/seller-views/calendar/`, `views/master-views/calendar/`; each `components/calendar.jsx`, `provider.jsx`, `index.jsx` |
| 6 | Admin hours/closed dates | `admin/src/views/user/master-working-and-closed-days.jsx` |
| 7 | Vendor invited-specialist hours/closed dates | `admin/src/views/seller-views/master-invitations/working-and-closed-days.jsx` |
| 8 | Specialist own hours/closed dates | `admin/src/views/master-views/closed-days/closed-days.jsx` |
| 9–11 | Calendar block editors, all three operational roles | `forms/blocktime-form.jsx` in each of the three calendar trees |
| 12 | Admin actor blocks | `admin/src/views/user/disabled-times/form.jsx` |
| 13 | Vendor invited-specialist blocks | `admin/src/views/seller-views/master-invitations/disabled-times/form.jsx` |
| 14 | Specialist own blocks | `admin/src/views/master-views/disabled-times/disabled-time-form.jsx` |
| 15–17 | Three recurrence editors | `forms/repeat-form-option.jsx` in each calendar tree |

Neighboring inventory: Admin `views/booking/{index,details-modal,status-modal}.jsx`,
Vendor `views/seller-views/bookings/`, booking/report/financial-operation date
filters; Product cart `components/delivery-time/delivery-time.tsx`,
`admin/src/components/forms/{shop-delivery-form,booking-time-form}.jsx`,
`views/seller-views/my-shop/shopDelivery.jsx`, Delivery/order/parcel/warehouse
windows, coupon validity, account birth dates and notification scheduling.
These are not evidence that deferred commerce or delivery is launch-ready.

### Three coherent scheduling directions — all requested dimensions

These are source-informed alternatives, not implemented designs. The full
supplemental assessment is `design-assessment/three-directions.md` in the same
evidence bundle. Device descriptions are proposed layouts; only the current U1
1280/390/320 interfaces were tested.

| Dimension | 1 Task-focused — recommended | 2 Staff-grid workspace | 3 Compact agenda-first |
|---|---|---|---|
| Visual/layout concept | Role task lanes, explicit decision sequence, detail sheet | Existing-specialist columns and time axis, scoped detail | Date-led chronological list and progressive detail; grid secondary |
| Customer | Preserve Service/Specialist → date → backend slots → summary → checkout | Same discrete-slot journey; operational grid is not a customer capacity engine | Same date/slots; do not infer openings from an agenda |
| Vendor | Existing day/week plus agenda switch and scoped actions | Parallel existing-specialist comparison; blocks/history explicitly distinguished | Next appointment/action first; secondary grid for heavy comparisons |
| Specialist | Own day schedule and availability, ambiguity fail-closed | Own specialist column/day; no peer/global grant | Personal chronological schedule and own settings |
| Admin | Existing list/search/date/Shop/Vendor/Specialist/status/detail/intervention default | Keep list default; grid optional, no new financial console | Keep oversight list, not an invented Admin agenda |
| Availability/settings | Composed hours/closed dates/block/recurrence controls with role adapters | Existing settings in contextual sheets; no new resource model | Progressive settings panels retaining full accepted rules |
| Desktop | Decision + summary; schedule + detail | Staff columns and time axis with side detail | Agenda + contextual detail, grid switch |
| Tablet | Sheet detail, fewer visible calendar columns | Reduced column set with explicit horizontal navigation | Agenda primary; optional wide grid |
| 390px | Single-column Customer; agenda fallback for operations | Role-scoped staff selector + single-column day, not a squeezed all-staff grid | Natural one-column agenda and full-width sheet |
| 320px | Stacked controls, no clipped primary actions | One specialist/day, stacked actions, explicit grid navigation | Date/status/actions stacked; no mandatory wide grid |
| Shared presentation | Wire-preserving format adapter, date/slots, toolbar, card/chip, summary/sheet, settings fields | Same core plus scoped staff-header/grid presentation | Agenda row/card, status/evidence labels, sheet/settings presentation |
| Advantages | Lowest conceptual change, proven Customer flow, clear role boundaries | Strong parallel workload comparison and operational context | Low-density daily work and small-screen readability |
| Disadvantages | Multiple role paradigms remain; limited cross-frontend sharing | Higher density and mobile complexity; easy to imply new capacity semantics | Weaker at-a-glance overlaps/parallel comparison; extra navigation |
| Complexity | Medium; approximately12–20 person-days | Medium–high; approximately16–26 person-days | Medium; approximately10–18 person-days |
| Migration risk | Low–medium; date/status/frozen-price display drift | Medium–high; wrong grouping/scope and visual capacity inference | Medium; omitted blocks/history or hidden existing actions |
| Exact affected components | Customer date-time/bookingDate/search pair; three calendar providers/cards/toolbar/drawers; three recurrence/six block/three hours editors listed above | Three operational `components/calendar.jsx`, event mapper/filter/toolbar/detail/provider paths; same scoped availability editors. Customer slots structurally unchanged | Three calendar event/card/detail/toolbar paths and optional secondary grid; same settings editors. Customer structurally unchanged |
| Approximate scope | Incremental presentation extraction within Next and Vite separately, role adapters retained | New operational grid presentation using existing specialist records, no backend/resource expansion | Vendor/Specialist agenda default with existing grid retained and independent settings consolidation |
| Timing | **AFTER MVP**. BEFORE: only usability fixes, now done. LATER: cross-app compatibility after separate review | **LATER**, only if actual parallel workload justifies complexity; none before MVP | **AFTER MVP** pilot for low density; LATER elevate grid if comparison workload requires it |

Estimates are planning ranges, not commitments; exclude backend/permission/
custody changes. Shared names are candidate concepts, not approval to create
all components or a cross-frontend package. Common presentation stays separate
from role APIs and authority.

Recommend direction1. Customer needs a reliable booking decision, Vendor needs
scoped schedule operations, Specialist needs own appointments/availability, and
Admin needs oversight/intervention. A universal calendar would obscure those
differences. Direction2 should win only with demonstrated parallel-staff need;
direction3 only when low-density daily execution dominates comparisons.

Safe later migration: freeze wire-format round trips and status/evidence
contracts; extract Customer presentation first; consolidate Business presentation
behind separate role adapters; compose scoped settings; review cross-app sharing
separately. Verify B1 half-open/full-hour/specialist exclusion, interval/pause,
monthly-date skipping and origin-based counts before replacing any surface.
Preserve backend slots, persisted identity/time/price/currency, Cash/Wallet,
permissions and ambiguous Shop contexts. No new scheduling model or broad redesign
is approved by this assessment.

## 19. Current-authority scheduling and isolated staging conclusion — 2026-10-04

This section is the current authority. Sections1–18, previous same-file reports/ZIP and failed receipts are preserved as history; their old scores/blockers are not current completion claims.

### Decision and measured scope

**REMAIN IN STAGING. Same twenty gates: 15.5/20=77.5%.** Started14.5/20=72.5%. No production deployment, protected mutation, provider activation, payout/refund or legacy completion certification.

The normal demo was not retargeted. Final read-only preservation and native financial readback passed. Scheduling source is implemented with native role contracts. B1 passes; U1 is PARTIAL with the final verification limits below. D1/R1/O1 local drills PASS but overall remain PARTIAL. Approved SMTP is unavailable and was not configured.

### Same twenty gates — earned acceptance only

| Existing gate | Current classification | Credit |
|---|---|---|
| C1 Customer access/reset | PARTIAL — local A1 accepted, live email absent | 0.5 |
| C2 Discovery/location/detail | VERIFIED | 1 |
| C3 Exclusive booking lifecycle | VERIFIED — B1 retained | 1 |
| C4 Native payment | VERIFIED — Cash/Wallet only | 1 |
| C5 Visible history/receipt | VERIFIED | 1 |
| C6 Responsive error/retry | PARTIAL — selected widths/errors/retries accepted; broader network failures not claimed | 0.5 |
| V1 Onboarding/Shop | VERIFIED | 1 |
| V2 Service/staff setup | VERIFIED | 1 |
| V3 Calendar lifecycle | VERIFIED — previously0.5; actual UI reflection now accepted | 1 |
| V4 Financial history/operations | VERIFIED — reservations/history, not external payout | 1 |
| A1 Admin approval/user oversight | VERIFIED | 1 |
| A2 Financial boundary authorization | VERIFIED | 1 |
| A3 UNKNOWN/provider/intervention visibility | PARTIAL — local UNKNOWN accepted, external intervention absent | 0.5 |
| A4 Refund/cancellation operation | PARTIAL — reservation cancellation accepted, real electronic refund deferred | 0.5 |
| O1 MySQL DDL/financial integrity | VERIFIED | 1 |
| O2 Production runtime/TLS/cutover | PARTIAL — production-built local HTTPS/FPM/MySQL and compatible rollback accepted; public TLS/CAPTCHA/cookie paths pending | 0.5 |
| O3 Restore | PARTIAL — real encrypted off-instance restore/PITR accepted; off-host retention/independent key custody pending | 0.5 |
| O4 Communications/background reliability | PARTIAL — selected local worker/scheduler recovery accepted; approved SMTP/live delivery absent | 0.5 |
| O5 Observability | PARTIAL — local metrics, receiver and failure drills accepted; external alert destination/owner absent | 0.5 |
| O6 Authenticated responsive acceptance | PARTIAL — selected corrected spots accepted; final read-only populated Specialist390/modal checks not fully accepted | 0.5 |
| **Total: Customer5 + Vendor4 + Admin3 + operations3.5** | **15.5/20=77.5%** | **15.5** |

No points for compilation, scripts or appearance. Operational rows receive only half credit while external acceptance is missing. Existing partial Cash/Wallet/provider/communications boundaries are unchanged. O6 is conservatively reduced until the changed current responsive surfaces are accepted.

### Failed evidence and corrections retained

- Original Customer30min-versus60min card, literal-null summary and misleading reschedule copy remain in u1-customer.json; corrected390 text/images are independently checked, not a repeated financial journey.
- Original Business sidebar/modal/narrow-table/navigation findings and452px Vendor390 measurement remain in u1-business/u1-corrected.json. The later diagnostic establishes375px document/body with an825px table inside a303px overflow:auto container; no speculative global overflow hiding was used. It does not prove why the earlier measurement differed.
- Local-client9 draft/no-ghost/persist-once/status/reschedule/cancel/reload receipt is immutable. Canceled records remain historical cards while availability releases. Draft-copy/status-initialization/name bugs were fixed in the final Admin-only production build; Customer build was reused only after source-hash verification.
- Initial fixture-relative image paths and unset Serviceimg caused fallbacks. Only isolated approved synthetic media metadata was corrected to native absolute URLs. Actual Service/Specialist browser images and Shop detached native Image GET200 pass; hidden mobile Shop DOM remains explicitly distinguished.
- First R1 raw-schema FAIL and first O1 HTTP-probe FAIL are archived. R1 all-row evidence matched; strict1735-column and narrow redundant-charset DDL comparison resolved the sole probe-table textual mismatch. O1 initial route fell through to Customer; explicit isolated FPM routing fixed it, and only the failed probe/redaction verification was repeated.

### Current local runtime and recovery receipts

D1: 14 checksPASS. Production/debugfalse Nginx/FPM/restricted native MySQL, exact CORS, compatible rollback/redeploy, persistent90-file media/key/full-native evidence and safe empty scratch failed-DDL containment. Local TLS only; fixture API auth is not CAPTCHA UI/cookie/CSRF acceptance.

R1: real AES256-GCM encrypted dump/media/binlogs/runtime contract; native isolated second instance; selected post-snapshot marker restored, later change not replayed; source not overwritten; restored reads/media/invariantsPASS. Measured databaseRPO0.039s, mediaRPO0.606s, RTO128.437s. Off-instance yes, off-host no; no continuous production RPO or historical ciphertext/key-custody claim.

O1: 17 selected checksPASS. Worker/backlog/restart, scheduler/heartbeat/catch-up/dedup, safe failed-job recovery, realHTTP500/429, safe low-disk threshold, UNKNOWN/PENDING visibility and selected log redaction. Financial snapshots unchanged. Local receiver/supervision only; no external delivery/alerts or provider retries.

### All51 explicit completion answers

**1. Did the modern calendar/scheduling implementation complete?**

The selected incremental implementation is complete and production-built. End-to-end responsive acceptance is PARTIAL; do not equate source completion or compilation with every requested responsive acceptance.

**2. Which of the 17 scheduling implementation units changed?**

All seventeen selected units received the shared direction: Customer booking date/backend-slot unit; Customer discovery date/time pair; Admin/Vendor/Specialist calendars (3); their weekly-hours/closed-date editors (3); their calendar blocked-time and separate disabled-time editors (6); and their recurrence editors (3). The accompanying implementation inventory names the paths. Existing Customer checkout/history/cancel flows remain native.

**3. Which were consolidated/shared?**

The three Business calendars share ScheduleSurface navigation, day/week/agenda, narrow-screen agenda and loading/error/empty presentation. Three recurrence editors use SchedulingFieldSection; hours and blocked-time editors use shared scheduling-field styling. Role services, validation, authorization, selection, providers and persisted state are not merged. Master option name normalization is shared.

**4. Which legacy calendar implementations remain, and why?**

Native Customer backend-slot selection and date/time picker engines remain, as do role-specific hours/closed-date/blocked-time/recurrence forms and their contracts. Admin list/search/filter/detail/status oversight stays primary; its calendar is secondary. Deferred Delivery source is untouched. No new universal scheduling model, calendar library or framework migration.

**5. Did Customer scheduling pass 1280/390/320?**

The real Cash/Wallet date/slot/confirmation flows fit all three widths (document/body1280,375,305 respectively). Initial 30-versus60min, literal-null and misleading reschedule-copy failures are preserved and corrected. Fresh390 public checks confirm Master105,60min and no literal null; real Service/Specialist image GETs pass. All three assets load; the mobile Shop node is hidden, not visually certified there. The corrected Customer journey was not repeated at every width.

**6. Did Vendor scheduling pass 1280/390/320?**

PARTIAL. Local-client lifecycle passed1280;320 cards and corrected320 navigation passed; settled390 list fits375 with internal table scrolling. The first452px list/modal failures remain historical. Do not claim every final modal width passed without the final receipt.

**7. Did Specialist scheduling pass 1280/390/320?**

PARTIAL. Master1051280/320 reads/cards passed; corrected390 empty calendar visibly fits. The initially420px populated390 calendar is retained; populated corrected390 acceptance remains a gap absent a final PASS receipt.102 remains deliberately fail-closed at all widths.

**8. Did Admin scheduling pass applicable responsive checks?**

PARTIAL. Existing Admin107 list/search and native booking/transaction reads passed. Corrected1280/390 heading/stacked tables are readable. Final320 corrected modal acceptance is not claimed absent the final PASS receipt. Calendar remains optional.

**9. Did targeted B1 regression pass?**

Yes: 20 focused native checks passed in the isolated staging MySQL. Not a historical-suite rerun.

**10. Did targeted U1 regression pass?**

PARTIAL overall: real Customer Cash/Wallet and Vendor local-client journeys passed, including no-ghost/persist-once/reschedule/cancel/reload. Remaining final responsive checks prevent an unqualified U1 PASS. D1 results are local drill evidence, not conditional full staging certification.

**11. Did same-slot native exclusion remain correct?**

Yes. Same-slot contention had exactly one durable winner, with no losing financial effect; cross-Shop shared-Specialist overlap remains excluded.

**12. Did legitimate adjacency remain correct?**

Yes. Native left/right endpoint adjacency passed. In UI, canceling Cash09:00 released that slot while active Wallet10:00 stayed occupied.

**13. Did recurrence remain correct?**

Selected native recurring blocked-time authority passed; the existing half-open/monthly missing-date/origin-occurrence contracts and native form values were retained. This campaign does not certify every new recurrence variation.

**14. Did cancellation release remain correct?**

Yes. Native unfunded cancellation and UI Cash7/local-client9 cancellation released capacity. Local-client14:00 was offered again. Historical canceled calendar cards may remain visible but do not reserve capacity.

**15. Did rescheduling remain atomic?**

Yes. Failed native reschedule preserved original state; successful reschedule retained frozen economics/released the old interval. UI local-client9 moved13:00–14:00 to14:00–15:00, remained$100/60min, then canceled without collection. Customer UI has no reschedule control; copy no longer promises one.

**16. Did Cash semantics remain unchanged?**

Yes. Cash7 remained native offline/progress, not fabricated electronic collection; UI cancellation persisted. Native readback supplies collection evidence because the Customer UI has no explicit collected/uncollected flag.

**17. Did Wallet semantics remain unchanged?**

Yes. Wallet8 double-click emitted one booking POST and one$100 debit,4900→4800; Wallet paid/New persisted. Cancel returned400 and left the booking/capacity intact. No provider/refund/payout was invoked.

**18. Did local-client UNPAID remain uncollected?**

Yes. UI-created client/booking9 stayed unpaid/pending-no-collection with$100/60min, no collection/refund/contribution effect through status, atomic reschedule, cancellation and reload. Discarded drafts caused no booking create or ghost.

**19. Did protected financial state remain unchanged?**

Yes. Final original field-level fingerprints/schema, retained native operations and completion evidence match. O1 financial-table fingerprints/retained UNKNOWN/PENDING/CANCELED states match before/after. Only explicitly isolated synthetic booking/client/payment fixtures and operational markers were exercised.

**20. Did all 59 fingerprints match?**

Yes; final-preservation statusPASS,59/59, changed[].

**21. Did all 465 schema objects match?**

Yes; final-preservation465/465 and schemaMatchtrue. No owned/original schema change.

**22. Do all 12 legacy Orders remain unverified?**

Yes;12 total and12 unverified. No completion evidence was fabricated.

**23. Did the normal demo remain intact?**

Yes:9 Shops,51 Services,75 assignments,14 Specialists and normal API200/9 Shops. Normal previews were temporarily paused only for build-resource isolation and restored; the normal storefront was never retargeted to disposable acceptance data in this work.

**24. Did all expected demo media remain usable?**

All90 copied approved media hashes survived D1 and R1. Browser Service/Specialist GETs and detached native Shop Image GET200/natural dimensions pass; the mobile hidden Shop image is not a visible-slot claim. No placeholder metadata remains in the corrected three synthetic media classes. Normal demo media were not rewritten.

**25. D1 status?**

PARTIAL overall; local compatible code rollback/redeploy, runtime/security/CORS/media/key checksPASS. Public host/TLS, real CAPTCHA UI sign-in and applicable cookie/CSRF acceptance are missing. Not a production deployment or full D1 certification.

**26. Was a production-like staging runtime actually exercised?**

Yes: isolated production-built Customer/Business, loopback HTTPS8443/8444, Nginx/PHP-FPM, production/debugfalse, MySQL8/InnoDB/REPEATABLE READ/strict/utf8mb4/UTC on33309, restricted DML app principal and persistent media outside releases. Local self-signed TLS is not approved public TLS.

**27. Did code rollback/redeploy pass?**

Yes locally. Rolled to foundation-corrected then redeployed calendar-final; restarted Customer/FPM/proxy/DB; full native evidence, key and media remained unchanged. Empty scratch malformed migration failed safely; no populated down migration.

**28. Did persistent media survive?**

Yes:90 files and their hashes match across code rollback/redeploy and independent restore; class-specific original/restored HTTPS bytes match.

**29. Did APP_KEY remain stable?**

Yes through restarts, rollback/redeploy and restored runtime; encrypted selected canary decrypts. Stable local authority is derived from managed SESSION_SECRET. No actual key value is reported; independent production escrow/rotation/retention is not proved.

**30. R1 status?**

PARTIAL overall; real local encrypted off-instance backup/restore/PITRPASS. Approved off-host destination, automated retention/monitoring and independent key custody remain missing.

**31. Was a REAL isolated restore performed?**

Yes. A consistent native dump with binlog coordinates, media and runtime contract were AES256-GCM encrypted, restored into a new MySQL33310/database/runtime/media root, and real row binlogs replayed to a selected post-snapshot marker. The later source update was excluded and source was never overwritten.

**32. Measured RPO?**

Drill loss intervals: database0.039s; media0.606s. Targets15min database/1h media are met in this drill only—not continuous production guarantees.

**33. Measured RTO?**

128.437s measured local restore; below4h target. Production scale, off-host retrieval and human recovery are not measured.

**34. Did restored financial invariants match?**

Yes. Selected-point row fingerprints, financial/booking/wallet/retained operation state, indexes, constraints, triggers and1735 column contracts match. First raw schema hash FAIL is retained: redundant column CHARACTER SET before identical COLLATE differed on the operational probe only. Strict narrow DDL equivalence, not arbitrary hash suppression, resolved it.

**35. Did restored media work?**

Yes:90/90 hashes and Shop/Service/Specialist examples served through isolated HTTPS8445 with matching bytes. Restored real actor/controller reads passed. Recovery endpoints were stopped after the drill.

**36. O1 status?**

PARTIAL overall;17 selected local failure/alert/recovery/redaction checksPASS; independent external alert channel/owner remains unaccepted.

**37. Were worker-failure alerts exercised?**

Yes: worker stopped, PROCESS_WORKER_DOWN and backlog visible; selected notification queue recovered after supervised restart. Without SMTP, delivery remainsBLOCKED—not falsely delivered.

**38. Was scheduler interruption exercised?**

Yes: stopped scheduler/stale heartbeat alerted; selected reminder catch-up/dedup passed; supervised scheduler resumed. Late after-start semantics were not widened.

**39. Was failed-job recovery exercised?**

Yes: isolated safe operational queue job failed visibly, specifically retried and recovered. No payment, provider or financial job was retried.

**40. Was UNKNOWN/PENDING operational visibility exercised without reclassification?**

Yes: FINANCIAL_INTERVENTION_REQUIRED alerted on retained states; native IDs/states/amounts and financial effects remained identical. No reclassification or automatic provider retry.

**41. Did log-redaction checks pass?**

Yes: selected current staging logs scanned for managed/derived authority and authorization/password/reset/invitation/merchant canaries, zero hits. Safe HTTP500/log marker and429 abuse were exercised. No real provider payload exists to certify.

**42. Was live SMTP actually tested?**

No. No approved staging SMTP/TLS/provider credentials or delivery destinations were available. Local transport/recovery acceptance is not live delivery.

**43. If not, what exact external dependency remains?**

An approved staging SMTP service/TLS endpoint/port, authorized sender/domain and safe recipient, provider credentials entered through managed secrets, and delivered-message evidence. Do not enable unauthorized default SMTP or record passwords in reports.

**44. Did any new P0 appear?**

No new selected financial/security P0 identified. Transient/failed UI, media, restore-schema and HTTP-probe receipts remain in history with corrections/verification limits; no production acceptance was implied.

**45. What P1 remains?**

Public staging/TLS, real production CAPTCHA login and applicable cookie/CSRF checks; final responsive acceptance gaps; approved live SMTP; off-host backups/retention/independent key custody; external monitoring owner/channel; provider-side historical credential rotation evidence. Existing Admin numeric-ID/type-filter observations remain unclassified, not certified correct.

**46. What external dependencies remain?**

Approved publicly reachable isolated staging host/domain/TLS and durable storage; CAPTCHA key; SMTP/TLS/sender/recipient; off-host encrypted backup/binlog retention and independent key escrow; external alerts/on-call owner; historical provider credential rotation evidence. No dependency was silently treated as done.

**47. Which features remain deferred?**

Product purchasing/stock, multi-Shop checkout, all MTN/Orange/Flutterwave/Paystack/Stripe/PayPal activation, Vendor-direct/own-gateway collection, external payout, electronic refund/reconciliation, Delivery/SMS/push expansion, PostgreSQL conversion, StrategyG and speculative infrastructure. Product browse-only is not purchase readiness.

**48. Updated same-20-gate score?**

15.5/20=77.5%; same labels/framework, not a new certification. Local acceptance earns partial operational credit only.

**49. Exact score derivation?**

Customer5 + Vendor4 + Admin3 + operations3.5 =15.5. Starting14.5: O2+0.5 for accepted local production runtime/rollback; O3+0.5 for real encrypted restore/PITR; O5+0.5 for exercised local monitoring/recovery. O6 reduced1→0.5 while current responsive correction acceptance is incomplete. O4 remains0.5, not credited twice for existing N1; builds, appearance, scripts and previously earned B1/U1/A1/N1/S1 are not extra points.

**50. Is the decision READY FOR LIMITED PRODUCTION APPROVAL, REMAIN IN STAGING, or NO-GO?**

REMAIN IN STAGING. Local production-like drills do not resolve public/runtime-auth/recovery-custody/live-delivery/alerting dependencies. No production deployment or full production certification.

**51. What is the exact safest next action?**

Keep the isolated release/data unchanged. Close only the explicitly unverified read-only populated Specialist390/final modal responsive spots, then obtain owner-approved public staging/TLS/CAPTCHA and external SMTP/backup/alert/key-custody configuration. Do not repeat financial journeys, broadly redesign, activate providers or deploy production.

### Evidence and exclusions

Current staging receipts are in .local/staging-mvp: b1-bounded, d1-security, d1-release, u1-customer, u1-business, u1-corrected, u1-local-client, u1-overflow-diagnostic, u1-surface-spots, u1-media-corrected, u1-final-readonly, r1-selected-point/r1-restored/r1-schema-equivalence/r1-recovery/r1-restored-role-reads, o1-rehearsals, log-redaction and final-preservation. Calendar implementation inventory accompanies these. Screenshots are real browser receipt IDs; the ZIP does not claim archived pixels. Private auth, keys, dump/binlogs, dotenv, encrypted backup payloads and raw logs are excluded. Normal original previews and Canvas were restored; isolated operations supervision remains running. Restore-only services are stopped. No active mission is created.

## 20. Bounded calendar correction — current authority, 2026-10-05

The [nineteen-result calendar closure report](agendaally-calendar-acceptance-closure.md)
supersedes earlier calendar conclusions. Existing-client Cash selection and
native confirmation/cancellation now pass; local saved-detail 403 handling,
organic receipt recovery, scoped Specialist selection and preservation pass.

**16/20 = 80%, REMAIN IN STAGING.** V3 remains 1 for functional lifecycle
acceptance. The authorized responsive-evidence-only follow-up retained 67 current
states at 1280/390/320 and moves O6 from 0.5 to 1. All other credits remain as in
the existing twenty-gate model. Calendar browser acceptance is **CLOSED**;
U1/calendar responsive is **VERIFIED at those browser viewports**. This does not
certify physical devices or production. No application edit, record creation,
production/provider activation or broader campaign was done.

Final preservation: 59 protected fingerprints/465 original schema objects,
12 unverified legacy Orders, normal API and 9 Shops/51 Services/75 assignments/
14 Specialists unchanged; sampled media 200. Exact isolated browser changes
were removed/restored, private snapshot deleted, normal previews restored.

After responsive follow-up, protected fingerprints/schema, counts, legacy
Orders, native operations, normal API and three media samples passed again.
All 206 isolated table counts/schema and 1811 application-source files matched;
205 table fingerprints matched. Native token-use timestamps changed the
authentication table fingerprint only (72 existing tokens, no additions);
the exception is disclosed in `calendar-responsive-preservation.json`.

## 21. Owner-confirmed SMTP delivery and bounded presentation correction — 2026-10-05

This is current SMTP authority; earlier failed attempts and unavailable-SMTP
statements remain historical evidence, not current transport blockers.

The owner reports that the normal port-3003 Admin displayed “Test email sent
successfully” and the test message arrived in their Gmail mailbox. The owner
attributes the earlier failure to Zoho account/configuration, now corrected.
Record **SMTP end-to-end transport and actual mailbox delivery VERIFIED** for
this controlled current-build Admin test. This is owner-confirmed receipt, not
an agent packet trace or a new send. No password or mailbox address is retained
in this evidence update.

The initially received logo used a localhost URL, and social badges were literal IG/FB/in
text with double-prefixed HTTPS URLs. The shared template and legacy email
invoice now embed owned PNG/raster files via CID, use explicit dimensions,
table-based links and normalized allowlisted HTTPS destinations. Invalid social
destinations are omitted; unavailable logos use a visible text wordmark.
Gallery attachments now resolve owned public-storage files rather than treating
an HTTP host as a filesystem path. PHPMailer controls invoice MIME headers.
See [bounded audit and correction](smtp-email-presentation-audit.md).

Local acceptance builds real Blade output and MIME without calling send or
postSend. Network/mail functions are disabled during the focused checks.
Every HTML CID resolves to an inline PNG MIME part with matching bytes. Browser
preview uses those same attachment bytes as data URIs. This verifies local
rendering/MIME structure, not mailbox receipt by themselves. The owner's later
confirmation in section 22 verifies the corrected Admin presentation in Gmail.

### Readiness credit — unchanged twenty-gate framework

**16/20 = 80%, REMAIN IN STAGING.**

| Group | Credit | Current relevant limits |
|---|---|---|
| Customer C1–C6 | 5 / 6 | C1 stays 0.5: actual reset/verification mailbox journeys not approved/verified; C6 stays 0.5 |
| Vendor V1–V4 | 4 / 4 | Previously accepted selected lifecycle evidence reused |
| Admin A1–A4 | 3 / 4 | A3 and A4 remain 0.5; no external provider/refund acceptance |
| Operations O1–O6 | 4 / 6 | O1/O6 are 1; O2/O3/O4/O5 remain 0.5 |

The SMTP/live-mailbox **portion** of the communications gate is closed for the
approved normal Admin environment. O4 remains 0.5 because operational mail paths
are still suppressed and their real-delivery/supervision acceptance is not
established by a Test Email. C1 gains no credit for unrelated test-message receipt.
No credit is awarded for appearance, asset changes, compilation or previously
earned worker/scheduler checks.

SMTP TLS verification, encrypted credentials, password-free API responses,
sanitized transport errors and scoped Admin capability remain unchanged.
General email, electronic providers, payouts/refunds, SMS, push and other
deferred integrations remain suppressed/disabled. No scheduling, financial,
security, recovery or responsive campaign was rerun. The agent made no SMTP
connection and sent no email. Stop here; another real email requires explicit
owner approval.

## 22. Complete email-system audit and corrected Admin receipt — 2026-10-05

Current owner authority explicitly confirms **SMTP transport, Gmail mailbox
delivery and corrected Admin Test Email logo, Instagram/Facebook/LinkedIn icons
and footer VERIFIED**. No additional agent send or SMTP connection was made.
The earlier local-only correction qualification in section 21 is historical.

See the [complete 30-section audit](agendaally-email-system-audit.md) and its
self-contained [HTML report](agendaally-email-system-audit.html), including the
17-column inventory, actual triggers, rendering/MIME/security findings,
missing-template analysis and proposal-only architecture.

The isolated audit passed **5 tests / 198 assertions** with network/mail/process
functions disabled. Defaults fit 900/390/320px; long unbroken shared/order
content overflows. Legacy subscription recipient privacy and pre-commit order
mail are deferred activation issues, not fixes performed in this task.

**C1 = 0.5; O4 = 0.5; current score remains 16/20 = 80%, REMAIN IN STAGING.**
The protected normal database lacks `selected_email_deliveries`; the existing
operational migration has only retained disposable-MySQL application evidence.
Generic diagnostic receipt does not close real signup/verification/password
change or selected worker/scheduler supervision. Booking email does not exist
and is not a hidden new rubric prerequisite.

The minimum future approved campaign is **two real account messages**—one
verification and one reset—combined with the selected queue's safe-before-send
restart/dedup and operational-supervision acceptance. No repeat Admin test is
needed. Explicit target/schema and purpose/recipient delivery approval is
required; do not globally enable suppressed marketing/order/Driver transport.
Reuse accepted UNKNOWN/no-resend proof rather than provoking another uncertain
live acknowledgement.

Only after the specified C1 or O4 closure would the score be 16.5/20 = 82.5%;
both would be 17/20 = 85%, still subject to other open gates. These are not
earned current points. Optional booking notices and all new financial emails
remain outside implementation approval.

Preservation passed: 206 table fingerprints and 467 schema objects unchanged,
4,683 normal application source files unchanged. The SMTP credential was not
selected/decrypted or modified. No worker/scheduler, payment/SMS/push behavior,
workflow configuration or deployment changed. Stop pending explicit approval.
