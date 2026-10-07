# AgendaAlly Vendor calendar: audit and approval-only proposal

**Decision: remain in staging. No visual redesign implemented. No production deployment.**

**Revised proposal:** the overall direction is approved, not implementation. These revisions require final approval: Agenda-first at 390/320px; Day secondary; Week optional/contained; quiet early booking steps with prominent payment state on final Review; durable New Local Client save retries without name-based deduplication; existing AgendaAlly theme tokens.

The drawings read the existing `agendaally-stage1-tokens.scss` canvas/surface/ink/muted/border/accent/radius tokens. The prior illustrative purple is not a required brand colour. Implementation must reuse the active AgendaAlly theme and its Inter font token, including its supported theme states; wireframe font rendering is a fallback, not a font replacement.

**Verification scope:** 34 native HTTP-kernel checks and the targeted 6 PHP tests/36 assertions pass. Saved-details and read-only Add Booking/New Client browser checks pass at the inspected desktop/mobile widths. The earlier full repository hardening workflow is **not green** (954 tests, 25 errors, 35 failures); its wider fixture/contract failures are not certified or repaired by this narrow calendar audit. This is not whole-app or production readiness.

This report separates bounded security/functional corrections from the proposed redesign. The drawings contain illustrative display data, not real appointments, Customers or collection evidence. The prior readiness score is not increased by this targeted audit.

## 1. Current calendar problems

- The Vendor Specialist filter used a public marketplace lookup with no Shop context.
- Add/edit selectors also depended on public discovery responses rather than an operational, authenticated Shop projection.
- Event click opened an update form, fetched a parent group, and requested a fresh calculation. Loading/error states could leave an incomplete or misleading detail panel.
- The existing desktop time grid remains dense. Mobile uses a compact appointment list, but hides the Day/Week/Agenda controls rather than providing an intentional mobile navigation design.
- Client creation and booking confirmation are separate operations. Their persistence boundaries need clearer wording.

## 2. Security and tenant isolation

**Confirmed operational enumeration defect:** an authenticated Vendor could obtain the public, exclusively foreign Specialist profile through the calendar's original lookup. This was an operational scope violation; it was not evidence that foreign booking creation or money movement succeeded.

**High-impact staff target-authorization gap:** the Seller notes/time controller methods lacked the operational target check already used by other lifecycle methods. The shared service's generic role check did not cover every operational staff role. The final synthetic staff actor can read an own-Shop booking (200), while the corrected foreign read/notes/time/status paths return 404. A read-only check reproduces the shared service authorization accepting a foreign staff target without the controller guard; its mutation is not invoked. Target checks have been added before those services run. No financial exploit is claimed.

**Assignment hardening:** Vendor create/calculate/edit validation now also requires the selected Specialist to be active and to have an accepted Master invitation to the same Shop. A stale active assignment is not sufficient after that relationship is revoked.

**Classification:** the enumeration issue is a confirmed tenant-scope defect. The missing staff target checks are a high-severity booking-integrity authorization defect, verified at the authorization-check boundary and corrected; a full harmful mutation was deliberately not executed.

## 3. Cross-Shop Specialist visibility root cause

`seller-views/calendar/components/filter.jsx` called `services/rest/masters` with `role=master` and no `shop_id`. The public repository intentionally discovers marketplace Specialists across Shops. That public discovery contract was the wrong data source for an operational Vendor selector.

The operational authority is an active `service_masters` relationship to the authenticated Shop plus an accepted same-Shop Master invitation and applicable branch visibility. It is not the Specialist's global role, a single “owning Shop” on the identity, or a client-supplied Shop ID. Accepted multi-Shop relationships remain valid independently.

## 4. Exact backend/API authorization result

The new `dashboard/seller/booking-masters` list/detail endpoints derive Shop context from the authenticated Seller/operational actor and enforce booking-view permission, same-Shop accepted assignments and branch restrictions. They return only identity display fields and that Shop's assignments, not contact details, wallets, invitations or foreign assignment relations.

Authenticated Seller/moderator/shop-manager calls to the old public Master list/detail routes are also routed through this scoped projection. Anonymous public marketplace discovery and ordinary Customer/Admin discovery retain their distinct contracts. Public profile metadata remains public; this change does not promise that information published to everyone becomes confidential.

Real isolated native HTTP-kernel evidence is in `vendor-calendar-api-evidence.json`:

| Request | Result |
|---|---|
| Shop A Specialist list | 200; own 105 and legitimate shared 102 |
| Shared 102 detail | 200; only Shop A assignments |
| Exclusively Shop B Specialist detail | 404 |
| Crafted foreign Shop lookup/body context | Native rejection, 400 |
| Foreign assignment / foreign Service's assignment | Native rejection, 400 |
| Foreign local-client ID | 422 |
| Foreign booking read/edit | Owner and permissioned synthetic staff 404 |
| Foreign notes/time/status requests | Denied before mutation |
| Own assignment + local-client preview | 200 |
| Revoked own Specialist lookup/preview | Denied |

The native exception handler uses 400 for controller-thrown validation errors; FormRequest errors can use 422. Failed validation previously passed nested arrays to Laravel's translation replacer and could produce 500. The error-response correction filters translation replacements without changing scheduling or accounting.

Temporary fixture records, roles, tokens and changes in the API audit are rolled back. The evidence explicitly checks the isolated fixture row fingerprints before/after.

## 5. Booking-click/detail root cause and correction

The event had a usable booking ID. The main defect was the chosen interaction/data path: `updateForm → parent-group GET → POST calculate`, rather than an authorized saved-record viewer.

Event click now opens a saved-detail drawer and calls `GET dashboard/seller/bookings/{event.id}` only. The component cancels stale responses when switching records, clears prior data, and has a visible error/retry state.

It shows saved reference, Service, client, Specialist, Shop, date, start/end, duration derived from the saved endpoints, status, native saved total, collection state and saved notes. It does not reprice from the current Service, create an appointment or imply collection. Existing lifecycle controls remain in the existing permission-governed Bookings module; this bounded fix does not invent a new action API.

The rebuilt staging browser verified reference #8 and its saved fields, zero POSTs on opening, close/reopen, and no page/drawer horizontal overflow at 1280, 390 and 320. This was synthetic staging acceptance, not production sign-in/CAPTCHA acceptance.

## 6. Add Booking audit

The existing native sequence is slot/action selection → client/branch information → Service → assigned Specialist → native availability/time → calculated preview → explicit create → refresh.

- Service selectors now use the Seller Service endpoint.
- Specialist selectors and individual lookups use the new operational endpoint, including edit selection.
- Server-side assignment/Service/Shop/client validation is independent of displayed choices.
- Availability, capacity, duration and final scheduling decisions remain on the existing backend.
- A preview is not a saved booking. The calendar refresh runs after successful creation; the detail viewer reads the saved record.
- This audit does not make final creation or lifecycle claims merely from a read-only form inspection. Previously accepted local-client create/reschedule/cancel behavior is retained separately.

Browser findings: an empty desktop calendar slot opened the native `new.booking` flow; an existing authorized Customer was selected; Service 101 and accepted Specialists 102/105 were available; a future 6 October/10:00 unsaved selection was inspected. The native `times-all` request is a **multi-date window**, keyed by date in the form, not a single-date query. Its original anchor remaining 5 October is not by itself a stale-date defect; the API evidence checks the selected next day's 10:00 slot in that returned window. No calculation, client-save or booking-create POST was fired. Back/cancel restored the calendar.

## 7. Add New Customer audit

“New Customer” creates a **Shop-scoped local booking-client directory record**, not a platform User.

- Name is required and normalized. Phone/email are optional, normalized and validated.
- Ownership comes from the authenticated Shop. Branch-specific records require an authorized service branch.
- Local client PII is excluded from another Shop's directory; the isolated HTTP test proves this.
- Contact uniqueness/deduplication is within the authorized Shop/branch scope, not a global public identity lookup.
- An authorized existing platform Customer with matching contact information is reused rather than creating a User. Unauthorized foreign/global accounts are not disclosed.
- Local creation generates no platform credentials, invitation or automatic account linkage.
- A contact-bearing repeated submission reuses the same authorized client.
- **Limit:** a name-only record has no reliable contact identity. The current contract cannot safely equate two people with the same name or guarantee server-side name-only network-retry idempotence. Do not claim that guarantee.
- Saving a client explicitly adds it to the directory. Abandoning the subsequent booking can leave that intentional directory entry; it does not create an orphan booking. Cancelling a booking does not delete the client.
- There is no supported automatic “claim/link this local history to a platform account” flow. A future link must require verified identity and explicit authorization, not an email-only merge.

Local-client booking validation rejects payment, Wallet, coupon, gift-card/membership and collection inputs. The accepted rule remains **UNPAID / UNCOLLECTED until a real supported collection occurs**.

## 8. Mobile audit

**Saved-booking journey:** 1280/390/320 verified in the real rebuilt staging browser. Drawer widths were 600/390/320; document scroll/client widths matched at each size. Saved values wrapped, zero console errors were observed, and opening made only saved-record GET requests.

**Current calendar navigation:** desktop Day/Week/Agenda were verified. Mobile intentionally shows a compact event list, not seven compressed day columns, but currently hides those mode controls. That is a usability limitation, not a claim that mobile Day/Week controls were tested.

**Add Booking/New Client:** see the accompanying browser findings for exactly inspected states. Final confirmation, keyboard variations across physical devices, every status action, arbitrary long datasets and all network-failure variants are not certified by a read-only browser pass.

The New Client modal fit at 1280/390/320 (520/374/304px wide), with no document/body horizontal overflow. A 129-character unsaved name stayed inside its single-line input, which scrolls horizontally rather than wrapping. Name shows a required marker; Phone/Email are optional. The Add Client button remains enabled with an empty name; no blur error was shown and the input lacked native `required`/`aria-required`. Submission validation was not invoked. Keyboard Tab reached Close → Name → Phone; full virtual-keyboard and focus-trap behavior remains a redesign acceptance item. Maps-unavailable messaging did not block Service/Specialist/date/time selection.

The proposed mobile designs below address navigation, long names, fixed-footers, keyboard clearance and nested-scroll risks explicitly.

## 9. Proposed desktop Week

See `desktop-week.png`. A compact workspace toolbar, fixed authorized Shop context, date arrows/Today, Day/Week/Agenda switch, search, assigned-Specialist filter and Add Booking sit above the calendar.

Use light one-hour guide lines instead of visually dominant sub-hour rules. Initial scroll targets native working hours while off-hours remain reachable. Highlight today and display the current-time line in the existing scheduling timezone.

Appointment cards carry time, Service, client, Specialist and native status only when their height allows it. Short slots show time + abbreviated Service; full details are available on click/focus. Blocked time uses a distinct neutral pattern and cannot be mistaken for a booking. Colour never replaces the text status.

Service/status filtering is enabled only against verified role-specific contracts. No invented resource capacity, drag-to-reschedule authority or payment collection is added.

## 10. Proposed Day

An appointment-focused single-day timeline initially fits the meaningful working/appointment range. Show the Specialist inside each card and allow filtering to an authorized Specialist.

For “all assigned Specialists,” use collision-safe stacking/list fallback rather than inventing a multi-resource scheduler. Sparse days display the next appointment, blocked periods and a useful empty-state action without a huge empty grid.

## 11. Proposed Agenda

First-class chronological groups by date, with time, Service, client, Specialist, native status and blocked-time rows. The whole item is keyboard/touch accessible. Preserve calendar date/filter/scroll state after closing detail.

Agenda is the default mobile mode. Desktop can switch freely between Week, Day and Agenda without changing the underlying query authority.

At both mobile widths, Agenda is the first, selected primary control; Day is secondary. Week is only an opt-in choice under More. The optional Week panel owns its horizontal scroll; it must not widen the page or replace Agenda as the default. See `mobile-320-week-contained.png`.

## 12. Proposed booking details

**Recommend a right-side desktop drawer**: it gives useful detail immediately without discarding calendar context. A modal has less room and a dedicated page unnecessarily interrupts routine scheduling.

**Mobile:** a full-width/full-height sheet with a visible Close/Back control, its own vertical scroll and safe-area footer. Keep saved reference, client/Service/Specialist/time/status/total near the top. Collection is explicit and independent from booking status.

Show reschedule/cancel/status actions only when the existing role-specific permission allows them. Those actions still invoke their native APIs and recheck authority. An initial saved-record GET must never recalculate the appointment.

## 13. Proposed Add Booking

Three focused steps:

1. **Client:** authorized existing-client search or explicit new local client; branch context where required.
2. **Service and time:** Service → accepted assignment/Specialist → date → backend availability → time.
3. **Review:** client, branch, Service, Specialist, authoritative duration/time, native amount and explicit unpaid/collection state → confirm.

Early Client and Service/time steps use only a short muted local-client explanation; remove the large UNPAID/UNCOLLECTED warning there. On final Review, show a prominent **Payment / collection state** block, the saved/calculated native amount and an explicit **Confirm unpaid booking** action for the supported local unpaid case. This is presentation only: no default Cash, Wallet debit, provider collection or new payment workflow.

See `mobile-390-review.png` and `mobile-320-review.png`. Existing native registered-Customer financial paths remain governed by their own certified contracts; the local unpaid presentation does not replace them.

Recheck availability at confirmation. A conflict preserves form input, displays the backend error and asks for another slot. No optimistic “saved” event or collection is displayed.

## 14. Proposed New Customer

Use the clearer label **“New local client”** with the note “Adds to this Shop's directory. Does not create a platform account.”

Require name; optional phone/email; verified branch context. Show authorized duplicate matches and reuse them rather than silently creating another contact-bearing record. Label the action **“Save to client directory”**, distinct from **“Confirm unpaid booking.”**

### Proposed safe-save/retry contract — implementation requires final approval

1. **Explicit save identity:** issue a random client-save intent when a genuinely new local-client Save begins. Retain it through uncertain responses, parent refresh, modal back/reopen and retry. A child component ref or a disabled button alone is not enough. An explicit new person/new Save gets a new intent.
2. **Server-derived authority:** bind it to the authenticated actor, verified Shop and permitted branch scope. Reauthorize every attempt and replay before disclosing the result. A client-supplied Shop/branch cannot override authorization.
3. **Canonical payload:** compare the normalized name/phone/email and verified branch context against the original intent. Same intent + same payload may return the same authorized result; changed payload is an explicit conflict, never another insert. Names participate in payload integrity, **not identity matching**.
4. **Durable atomic persistence:** use the approved native database and an owned migration, if required, with a uniqueness constraint and transaction/locking that couples the intent result to client creation. Concurrent same-intent requests commit at most one client. Memory-only caches, browser storage alone and expiry that silently permits another insert are insufficient.
5. **Replay result:** persist the minimal resolved reference (`local`/authorized existing `registered`, ID, outcome). Return the same resolved reference on retry; contact-based authorized reuse stays intact. Never recreate a client simply because its acknowledgement or subsequent lookup was lost.
6. **Uncertain acknowledgement:** retain entered values and the same reference; show “Save not confirmed” and **Retry same client save**. No automatic resend with a new key. If a request is in progress, show a bounded pending state. Keep payload changes/new-person intent separate until the previous save outcome is resolved.
7. **Abandon/recovery:** do not imply cancellation undid a possibly committed directory save. Preserve an unresolved reference so the user can resolve/retry it. An already resolved directory entry may remain when the booking is abandoned; no booking is created by saving the client.
8. **Different people:** two explicit new intents with the same name and no contacts can create two distinct people. No name-only merge, global account search, platform User, credentials, invitation or automatic history/account linking.
9. **Lifecycle/retention:** keep replay evidence through the supported retry and recovery window. An unresolved/expired or deleted-result intent fails explicitly rather than silently creating again. Native backup/restore must retain the intent-to-result relationship.
10. **Acceptance:** lost response after commit, concurrent identical saves, different-payload conflict, cross-Shop/branch denial, modal/parent refresh, abandonment/reopen, contact-bearing reuse, two same-name people under distinct intents and native restore/replay.

This is a proposed directory-write contract, **not implemented retry protection**. It must be included in the approved implementation scope and independently verified. It changes no booking capacity, availability, money, provider, Wallet or collection semantics. Do not offer unsupported account invitations/linking.

## 15. Proposed 390px

See `mobile-390-agenda.png`: date navigation + Today, Agenda selected first, Day secondary, More for optional Week, Filters, search and readable stacked appointment cards. Detail is full-screen; Add Booking is a safe-area footer with corresponding list padding.

Filters open a vertical sheet with apply/clear actions. Client/Service/Specialist selection uses searchable single-column lists. Keep entered values on back/error; move the focused input and footer above the keyboard.

## 16. Proposed 320px

See `mobile-320-agenda.png` and `mobile-320-add-booking.png`. Use a single column, shorter labels, wrapping names and no minimum-width form grids. Prioritize time/Service/status on the card; reveal the rest in saved detail.

Keep body width constrained. Any optional Week grid scrolls only inside its labeled bounded container; page/body must not scroll horizontally. Step progress uses compact text, not three wide tabs. Footer controls never cover the last appointment or validation error.

The 320px early Client screen now has a quiet explanatory line instead of a large unpaid warning. Final Review owns the prominent payment-state block. The optional Week illustration shows clipped columns and a contained scrollbar, with a direct return to Agenda.

## 17. Shared visual components

Vendor/Specialist/Admin may share: toolbar layout, date navigation, mode selector, calendar shell, time gutter, day headers, appointment and blocked-time presentations, current-time indicator, status treatments, responsive containers, loading/error/empty views and the visual filter primitives.

Share presentation props and accessible interactions, not an all-powerful shared business service.

## 18. Role-specific components/contracts

- **Vendor:** authenticated Shop/branch context, assigned-Specialist discovery, local-client directory and Seller booking/lifecycle services.
- **Specialist:** self/accepted-assignment scope and the existing Specialist action permissions.
- **Admin:** existing wider operational authority and explicit filters; not a Vendor lookup with an arbitrary Shop override.
- **Customer:** remains Service → Specialist/assignment → date → backend availability → time → confirmation. It does not become the business calendar.

## 19. Exact implementation files expected to change after approval

Primary shared surface:

- `.migration-backup/admin/src/components/scheduling/ScheduleSurface.jsx`
- `.migration-backup/admin/src/components/scheduling/schedule-surface.css`

Vendor presentation/workflow:

- `src/views/seller-views/calendar/index.jsx`
- `src/views/seller-views/calendar/provider.jsx`
- `src/views/seller-views/calendar/components/calendar.jsx`
- `src/views/seller-views/calendar/components/filter.jsx`
- `src/views/seller-views/calendar/components/drawer-view.jsx`
- `src/views/seller-views/calendar/components/saved-booking-details.jsx`
- `src/views/seller-views/calendar/components/service-views.jsx`
- `src/views/seller-views/calendar/components/action-type-selection.jsx`
- `src/views/seller-views/calendar/components/info-form.jsx`
- `src/views/seller-views/calendar/components/service-form.jsx`
- `src/views/seller-views/calendar/forms/info-form.jsx`
- `src/views/seller-views/calendar/forms/service-form.jsx`
- `src/assets/scss/components/seller-booking-calendar.scss`

Existing theme source, reused rather than recoloured or replaced: `src/styles/agendaally-stage1-tokens.scss`, with the active AgendaAlly theme's font, focus, semantic status and surface roles.

Proposed retry implementation after final approval:

- `.migration-backup/backend/app/Http/Controllers/API/v1/Dashboard/Seller/BookingClientController.php`
- `.migration-backup/backend/app/Support/SellerBookingClientIdentityMatcher.php` (retain contact-based authorized reuse; never add name-only merging)
- `.migration-backup/backend/app/Models/SellerBookingClient.php` and the native approved owned migration/test paths, **only if the selected durable intent store requires them**
- Focused intent persistence/service/model files, scoped to directory creation, to be named after inspecting the existing approved request-intent patterns.
- Vendor `components/info-form.jsx` and `provider.jsx` for explicit save/retry identity lifetime; no blanket shared financial-service rewrite.

New focused presentation components may be split into `components/scheduling/` and the Vendor calendar directory. Role wrappers continue to use their own services and permission checks. Projection-only changes to `src/redux/slices/booking.js` must retain saved IDs, native statuses and date semantics.

The existing wrappers to adapt to the shared presentation are `src/views/master-views/calendar/{index,provider}.jsx` and `components/calendar.jsx`, plus Admin `src/views/calendar/{index,provider}.jsx` and `components/calendar.jsx`. Their role-specific API/control contracts must remain separate.

Already changed in **Phase 1 only**: Vendor selector service/imports; the saved-detail viewer/context/click path; scoped `BookingMasterController` and routes; selected Seller booking target/context/assignment checks; authenticated operational Master fallback; error translation replacement handling. No new calendar layout/theme stylesheet was adopted.

## 20. Backend/scheduling behavior left untouched

No changes to capacity locking, same-slot contention, overlap exclusion, half-open adjacency, recurrence, working-hours/closed-date/blocked-time authority, Customer availability, duration computation, atomic rescheduling, cancellation release, Cash, Wallet, collection/provider behavior, payouts/refunds or accounting.

No provider calls, production deployment, normal-demo replacement or reseed occurred. Original protected verification passes: **59 fingerprints, 465 schema objects, 12 legacy Orders still untouched/unverified; normal demo 9 Shops, 51 Services, 75 assignments, 14 Specialists.** Retained native operations remain unchanged. No demo media was edited or replaced.

## 21. Expected regression scope after approved implementation

- Repeat role/Shop/branch/accepted-assignment positive and negative API tests, including legitimate multi-Shop Specialists and revoked relationships.
- Verify event/parent IDs and saved-record detail, quick switching, close/reopen, not-found/forbidden/network errors and zero calculation-on-view.
- Verify existing/new local client, authorized duplicate reuse, explicit directory persistence, retries and abandonment.
- Verify the proposed durable retry contract under native MySQL concurrency/lost acknowledgement, payload conflict, tenant/branch denial, same-name distinct intents and restore/replay.
- Exercise local unpaid create → refresh → detail → atomic reschedule → cancel/release in isolated fixtures.
- Recheck overlap/adjacency/contention/recurrence/working hours/closed dates/blocked time/native availability; verify no finance changes.
- Check Vendor/Specialist/Admin role-specific actions and Customer workflow separation.
- Check Day/Week/Agenda, filters, long names, keyboard/touch/focus, sticky/safe-area elements and nested scrolling at 1280/390/320.
- Confirm Agenda-first mobile entry and Day secondary, optional Week contained without body overflow, quiet early payment messaging and prominent final Review state; reuse existing light/dark/focus theme tokens rather than introducing a mandatory colour palette.
- Re-run protected fingerprints/schema/legacy Orders/demo counts/media checks and rebuild only the affected isolated clients.

**Stop:** these drawings and recommendations are presented for approval. Approval, revision or rejection comes before any visual redesign implementation.