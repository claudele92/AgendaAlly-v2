# AgendaAlly — Admin template library, Phase A checkpoint

This is an interim checkpoint, **not** authenticated UI acceptance or final
C1/O4 closure. The owner's current briefs authorize one Password Reset only
after Phase A passes. No reset request or real email was made during Phase A.
Subsequent Phase B state is recorded in `password-reset-phase-b-checkpoint.md`.

## Authority and diagnosis

- Normal Admin: `original-admin-preview`, port 3003, native Vite source.
- API: native same-origin `/api/v1/` proxy; default target is Laravel port 8000,
  not the selected acceptance backend on 8008.
- Normal Laravel: `original-laravel-preview`; owned-database startup guard passed.
- Database: `.migration-backup/backend/database/development/agendaally.sqlite`.
- Exactly one `verify`, one `reset` and one built-in `subscribe` record;
  no duplicate system identities.
- The native template route already existed at `/message/subscriber`.
  It was labeled “Message Subscriber” under Email Subscriber, whose legacy user
  management group was projected into **FINANCE**, not Settings.
  Email Settings had no link to this management route.
- Current tester cannot access the owner's authenticated browser. It saw
  `/login` and stopped, without credentials, account changes or authorization bypass.

## Bounded implementation

- Same route/menu identity now labeled **Email Templates**.
- Existing email group is placed in **SETTINGS**, retaining verified grants,
  identities, routes and all other section mappings.
- Existing Email Settings `/settings/emailProviders` now links to Email Templates.
- Library labels protected system versus custom Subscription content versus
  application-controlled legacy records.
- Create Email Template supports additional `subscribe` content under the
  existing registry, rather than filtering that type out after the first record.
- No arbitrary type, new workflow, recipient rule or sender is added.
- Legacy `order` presentation is not falsely editable: Order/Invoice still uses
  its native application-controlled invoice view. Its enum/records and actual
  invoice sender are retained.
- Create/update presentation does not directly dispatch a Subscription delivery
  event. Built-in and new custom Subscription presentation uses an inert library
  state excluded by the unchanged native scheduler's `status=0` selector.
  Existing campaign states are preserved on edit, not re-armed. The existing
  Subscription workflow and sender remain intact.
- Create requires presentation only, without a provider or delivery date;
  server-owned provider linkage and an inert required legacy date are retained.
- Server pagination metadata is consumed by the native list.
- Account source editing and multi-line alternate copy preserve exact saved
  wording, avoiding unwanted CKEditor normalization during a subject-only test.
- Existing system delete/bulk/drop-all/type/provider/schedule protections remain.
- Preview is synthetic; anonymous native/proxied list and preview remain 401.

## Authoritative library classification

| Path | Library scope |
|---|---|
| Verification | Existing persisted protected system default; editable presentation and synthetic preview |
| Password Reset | Existing persisted protected system default; editable presentation and synthetic preview |
| Dedicated Driver verification | Uses shared Verification presentation; Driver journey is not re-certified |
| Subscription / Digest | One persisted protected default, editable presentation and synthetic preview; additional custom presentation without campaign activation |
| Product Order / Invoice | Application-controlled invoice view; legacy template registration is not runtime wiring |
| Driver Invitation | Dedicated application-controlled sender |
| Admin Test Email | Application-controlled diagnostic; accepted test is not repeated |
| Framework notification hooks without native callers | Not promoted into invented application workflows |
| Refund/Payout notifications | Deferred; no templates or financial changes |

The owner explicitly resolved the conflict: the later three-template product
requirement is authoritative. One missing Subscription default is provisioned.
If Subscription content already exists, the oldest record is retained as the
default without changing any fields; additional records remain custom.
Subscription has no OTP placeholders. Preview's plain text matches the actual
Subscription sender rather than adding the account-template subject prefix.

## Current checks and preservation

- Current focused management/presentation/email audit suites:
  **24 tests / 376 assertions passed**, including customized existing
  Subscription preservation, idempotent provisioning, default protections,
  additional custom CRUD, capture-only real Subscription sender consumption,
  preview matching, scheduler exclusion and retained existing campaign behavior.
- Focused real-menu Settings/grant test: **passed**.
- Changed JSX/Redux parses; PHP syntax and `git diff --check` pass.
- Normal Admin/Laravel restarted only; startup succeeds.
- Subscription provisioning before/after fingerprints: **schema identical**,
  **account templates identical**, only `email_templates` and `migrations`
  changed. All other tables, including subscribers, SMTP, Customers, tokens,
  challenges, business/financial state and outbox/jobs are identical.
- Reviewed data-only migration applied once; repeat application is a no-op.
  Previous migration files match their reviewed hash exactly. The native
  manifest registers only this authorized addition; no guard was bypassed.
- Jobs=0; failed_jobs=0; delivery history remains 2 SENT / 3 EXPIRED.
- No native custom CRUD or template save was performed without authorized UI.
- Evidence: `.local/staging-mvp/admin-library-acceptance/`.

An older fingerprint comparison spans later activity between campaigns and
differs in the access-token table, in addition to the previously accepted two
templates/migration row. It is not used as this campaign's baseline, and the
source of that older token difference is not asserted. The fresh current
baseline/equality proof preserves all account/token/business/financial data.

## Acceptance status

Owner confirmed their authenticated normal Admin reaches the library and shows
Verification and Reset. That does **not** close the three-template requirement
or the remaining edit/save/reload/restore/custom CRUD acceptance.

The existing tester still sees `/login` and stopped without credentials,
storage/token inspection or auth bypass. The authenticated three-template
checks and remaining management interactions are **UNVERIFIED**, not failures.
The owner subsequently accepted the focused automated evidence for provisioning,
CRUD, Preview, protections and preservation as sufficient to proceed to Phase B.
Authenticated browser CRUD remains **UNVERIFIED**, not passing or failed. It is
not a remaining blocker under that explicit owner decision.

Phase B is authorized and its preflight is prepared. Verification was not resent; Admin SMTP Test was not
repeated; no worker, scheduler, Customer mutation, provider activation or
production action was performed.

C1=0.5, O4=0.5, score=16/20=80%. No new score is awarded.

Next gate: isolate and process exactly one normal Customer Password Reset,
then stop for Gmail receipt and normal UI reset completion. Do not repeat
Admin CRUD acceptance unless a real UI defect is observed.
