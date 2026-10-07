# AgendaAlly — bounded Admin email-template closure

Date: 2026-10-06 UTC (2026-10-05 America/Chicago).

Current authority: **complete Admin templates and local verification; pause real verification.**
Reset sending has no separate approval. No real challenge, selected worker, SMTP
send, account login/logout, or Customer state change was performed.

## 1. Before-state matrix

The normal owned SQLite `email_templates` table contained **zero records**.

| Existing type/path | Before registration and Admin visibility | Before runtime | Before preview |
|---|---|---|---|
| General User verification (`verify`) | Backend and create/edit option registered; no record to list. List lacked type/subject columns. | DB lookup existed, then accepted fallback copy. | None |
| Platform password reset (`reset`) | Same registered-but-empty condition. | DB lookup existed, then accepted fallback copy. | None |
| Dedicated Driver verification | Shares `verify`; no independent Admin type. | Same `sendVerify` presentation path; Driver security stays application controlled. | None |
| Subscription/digest, immediate and scheduled (`subscribe`) | Registered; existing CRUD; no records. | Existing template-based subscriber sender and scheduler. | None |
| Product order/invoice, creation and status (`order`) | Legacy type and CRUD registered; no records. | Separate invoice view; DB `order` template is not the sender's presentation source. | None |
| Driver invitation, issue and resend | No registered Admin template type. | Dedicated application-controlled Mailable. | No Admin preview |
| Admin Test Email | No registered template type. | Existing diagnostic sender and shared layout. | Existing diagnostic behavior; not template management |
| Framework Registered verification hook | Framework capability, not an active native signup trigger. | Native signup uses its own event. | No Admin template |
| Framework password-reset notification capability | Framework capability; no active native caller found. | Native reset uses its own service/outbox path. | No Admin template |

Shared layout/assets and development controls are infrastructure, not additional
notification types. No additional existing non-financial **Admin-managed** type
was found outside the existing four-type registry.

## 2. Root cause

Verification and Reset were not missing enum registrations. Their persisted rows
were absent. The existing list also omitted type/subject labels, and there was
no preview endpoint. Verification creation previously deleted existing
Verification rows, so it could destroy customization.

## 3. Files changed

Under `.migration-backup/admin/src/`:
- `services/messageSubscriber.js`
- `views/message-subscribers/index.jsx`
- `views/message-subscribers/subciribed-add.jsx`
- `views/message-subscribers/subciribed-edit.jsx`
- `views/message-subscribers/textEditor.jsx`
- `views/message-subscribers/EmailTemplatePreview.jsx` — new

Under `.migration-backup/backend/`:
- `app/Http/Controllers/API/v1/Dashboard/Admin/EmailTemplateController.php`
- `app/Http/Requests/EmailSetting/EmailTemplatePreviewRequest.php` — new
- `app/Http/Resources/EmailTemplateResource.php`
- `app/Services/EmailTemplateService/EmailTemplateService.php`
- `app/Services/EmailSettingService/EmailSendService.php` — account presentation calls only
- `app/Support/SystemEmailTemplates.php` — new
- `app/Support/EmailTemplateContent.php` — new
- `app/Support/ManagedEmailPresentation.php` — new
- `database/migrations/2026_10_08_010000_provision_account_email_templates.php` — new, data only
- `database/development/manifest.php` — bounded migration allow-list/fingerprint update
- `routes/api.php`
- `tests/Hardening/AdminEmailTemplateManagementTest.php` — new
- `tests/Hardening/EmailPresentationTest.php` — shared-renderer source contract
- `tests/Hardening/EmailSystemAuditTest.php` — actual canonical managed rendering, rather than old inline-source extraction

Development helpers:
- `scripts/development.mjs`
- `scripts/development/account-template-preservation.php` — new read-only fingerprint proof
- `scripts/development/render-account-template-previews.php` — new read-only/sockets-disabled rendering proof

This report is new. Temporary public synthetic proof HTML files were removed
after browser verification; retained proof is under `.local/staging-mvp/`.

## 4. Schema changes

**None.** Full SQLite schema-object fingerprint stayed identical. The new
migration inserts presentation records; it does not create/alter tables,
indexes, constraints, or financial schema. Its rollback deliberately does not
delete potentially customized records.

## 5. Data/default changes

Exactly two rows inserted: `verify` and `reset`, using the existing active native
provider (id 1), the exact accepted fallback subjects/copy, and `$verify_code`.
Alternate copy matches the accepted fallback.

Legacy required fields: `status=0`, `send_to=2099-01-01 00:00:00`. Account senders
do not use campaign scheduling/status. The existing scheduler filters
`subscribe`; account defaults do not create a subscription send.

Exactly one row added to the native migration journal. Only `email_templates`
and `migrations` fingerprints changed. Provisioning checks type existence,
serializes on the native provider, and preserves existing/customized rows.
Migration and authenticated Admin listing provide bounded provisioning; a
missing provider defers creation until one exists. No destructive seed was run.

## 6–7. Verification and Reset Admin status

Both are persisted, labeled, listable/viewable, presentation-editable and
synthetically previewable in the existing management views. System template
identity, provider and schedule cannot be reassigned; individual/bulk/drop-all
deletion is prevented. Subject also supplies the existing heading.

No account-template enable/disable feature was invented. Legacy `status` is
not an account-delivery capability or outbox state.

**Evidence limit:** PHP resource/edit/protection tests pass, and source parses.
Signed-in native Admin browser interactions are **unverified**: the available
browser redirected to login, and no new login was authorized/attempted.

## 8. After-state classification matrix

| Existing email type/path | Classification | Visible/editable | Preview | Runtime connection |
|---|---|---|---|---|
| General User verification | Admin managed | Yes; 1 persisted record | Yes, synthetic | Yes; native sender uses managed renderer |
| Platform password reset | Admin managed | Yes; 1 persisted record | Yes, synthetic | Yes; native sender uses managed renderer |
| Dedicated Driver verification | Admin managed presentation through shared `verify`; Driver security application controlled | Shared record | Shared synthetic preview | Yes; no Driver journey/send certified |
| Subscription/digest, immediate and scheduled | Admin managed; operational delivery deferred | Existing create/edit retained; zero records, so no invented default | Synthetic preview implemented | Existing template-based sender/scheduler retained; not activated |
| Product order/invoice, creation and status | Financial/deferred; existing code-controlled invoice retained | Legacy `order` option retained; no new record | No new preview | DB `order` template is **not** wired into native invoice sender |
| Driver invitation, issue and resend | Intentionally application controlled; Driver scope deferred | Not moved into Admin templates | No new preview | Existing dedicated Mailable retained |
| Admin Test Email | Intentionally application controlled | Existing Email Settings diagnostic retained | Not a managed template preview | Accepted sender/transport unchanged |
| Framework Registered verification hook | Intentionally not implemented as an additional native flow | Not promoted | None | Registered capability; no active native trigger found |
| Framework reset notification capability | Intentionally not implemented as an additional native flow | Not promoted | None | No active native caller found |

No claim is made that all application emails are Admin managed.

## 9. Placeholder contract and editing boundary

Canonical syntax remains literal **`$verify_code`** in Verification and Reset
body **and** alternate body. Subjects have no dynamic placeholders. Subscription
and legacy Order content have no supported dynamic placeholders.

No customer-name, URL, expiry, application-name, password or reset-token
placeholder was invented. Unknown dollar/Blade placeholders, PHP/Blade
execution syntax, unsafe HTML/attributes and arbitrary account/action links
are rejected. Account editor upload/media/link controls are removed. Existing
trusted shared footer URLs/assets remain application controlled.

The editor does not control recipient identity/binding, code generation,
lifetimes, replay/consumption, account/outbox/queue state, authorization,
worker behavior, SMTP credentials/transport or financial state.

## 10. Preview result

New authenticated Admin `POST email-templates/preview` consumes only
presentation input and returns subject/HTML/plain text with
`mode=synthetic_no_send`. It constructs no SMTP transport and resolves only a
clearly synthetic sample. It does not issue a challenge, use a live User,
enqueue a job, or alter account/template data.

It shares the actual account sender's `ManagedEmailPresentation` and accepted
Blade layout. Branding uses existing local/CID assets; preview converts CID
references to inline data images. The UI sandbox grants no script permission,
supports alternate text and invalidates stale preview requests.

Native anonymous preview returns **401**. Positive signed-in HTTP/browser
interaction remains unverified, rather than being faked or bypassing auth.

## 11. Responsive result

Actual normal-DB managed Verification and Reset were rendered with synthetic
values using query-only SQLite and socket/network functions disabled.
Browser checked both at **900/390/320px**: document/body matched viewport,
content/code/footer remained readable, and all four images loaded.
One initial resource 404 was observed; visible image assets all loaded.

Retained result: `.local/staging-mvp/account-template-preview/results.md`;
HTML/text/JSON render evidence is in that directory. Browser screenshot
observation IDs are retained in the results; screenshot export to local image
files was unavailable.

## 12. Runtime wiring

Capture-only in-memory tests exercised **actual** `sendVerify` and
`sendEmailPasswordReset` methods with customized persisted templates, plus
missing-record fallbacks. Both use managed subjects/body/alternate body and
retain recipient/transport/outbox behavior. No real SMTP was invoked.

## 13–16. Real verification

**Not attempted; paused by the owner's superseding choice.** No new native
request, challenge, worker or email. No new Gmail receipt or native Verify
completion is claimed.

The previously approved disposable Customer was already genuinely verified
before this campaign; native consumption/current-challenge checks require an
unverified account. The retained earlier authoritative transition was
2026-10-06 01:24:55 UTC. That account/state is preserved, not reset or forced.

## 17–21. Password reset

Separate real-reset authorization: **not granted**. No reset request/send,
Gmail receipt, native completion or real replay proof in this campaign.
Local isolated reset-transition/non-replay tests passed; these do not replace
real operational acceptance.

## 22. Final outbox/jobs

- Active/uncertain selected deliveries: **0**
- Historical selected deliveries: **2 SENT, 3 EXPIRED**, unchanged
- Jobs: **0**
- Failed jobs: **0**

## 23. Preservation

Read-only, row-ordered/type-preserving fingerprints of every normal owned
SQLite table prove all non-template/non-journal tables unchanged, including
accounts, access-token records, settings, outbox and protected financial data.
No credential was decrypted/displayed; evidence stores counts/hashes only.
Full schema hash unchanged.

Evidence:
- `.local/staging-mvp/account-template-before-fingerprints.json`
- `.local/staging-mvp/account-template-after-fingerprints.json`

Payout/refund/Wallet/provider/booking/staff-permission/logout code and
business/staging workers, unrelated schedulers and published environments
were not modified. Only normal Admin and normal Laravel previews restarted.

## 24. Focused checks

**56 unique focused tests passed across management/account-security and
presentation/audit runs.** The combined pass initially exposed two stale
inline-source assertions after rendering was shared; those contracts were
updated, and all 10 affected presentation/audit tests then passed
(**271 assertions**). The other 46 focused tests had already passed. A final
challenge-in-HTML-attribute rejection was added and the 8 management tests
passed again (**56 assertions**); unchanged account-security paths were not rerun.

Coverage: default migration/idempotency/customization/schema preservation;
system resource visibility/edit/delete/reassignment protections; dangerous
placeholders/HTML; sample-only preview/no User/job/outbox/send; actual account
sender managed content/fallback; selected recipient binding/isolation;
verification/reset transitions and reset replay; actual Blade/CID/MIME.

Changed JSX parsed with installed esbuild; installed AntD 4.20.6 uses `visible`
for preview Modal. PHP syntax and `git diff --check` passed. Normal Admin
responded 200; normal Laravel passed owned-database startup guard; anonymous
preview responded 401. No full hardening run, dependency install or broad build.

Signed-in Admin list/edit/preview interaction remains the only uncompleted
local browser check. It was not falsely marked passing.

## 25. Financial templates deferred

No Payout Requested/Approved/Completed/Rejected or Refund
Requested/Approved/Completed/Rejected templates were added. These await the
manual Refund/Payout state-machine design. Approved must not imply completed;
future Completed/Refunded messaging needs authoritative financial completion.

## 26. Findings/limitations

- Normal migration guard correctly required reviewed allow-list/fingerprint
  registration of the new data-only migration. Prior historical migration
  inventory matched exactly before adding it; no historical file changed.
- Two existing tests asserted the old inline renderer's source shape; updated
  to the canonical shared presentation contract, without invoice behavior edits.
- No authenticated Admin browser session was available. No auth bypass or
  account creation/login was attempted.
- Earlier full-hardening failures, normal Customer Next lock conflict and
  acceptance MySQL socket conflict remain outside this campaign. They were not
  rerun, repaired or used to broaden scope.

## 27–29. Rubric and score

**C1=0.5; O4=0.5; 16/20=80%, unchanged.** Admin template implementation and
local proofs are not real operational closure. Verification is paused and
reset has no separate authorization, so neither gate receives additional points.

## 30. GO/NO-GO

**GO for the bounded implemented template/data/rendering changes.**
**NO-GO for declaring signed-in Admin browser acceptance or C1/O4 operational
closure.** Real acceptance remains paused. No publishing or financial
notification implementation is authorized by this result.
