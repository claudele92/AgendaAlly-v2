# AgendaAlly email-system MVP audit

**2026-10-05 · Audit, local verification and proposal only · REMAIN IN STAGING**

## 1. Executive conclusion

SMTP transport, actual Gmail receipt, and the **corrected Admin Test Email logo, Instagram/Facebook/LinkedIn icons and footer are VERIFIED by the owner**. This is accepted evidence, not a new agent send.

The minimum existing account-email content is sufficient to pursue MVP acceptance; adding a large collection of templates is not necessary. However, **verification/reset are not operationally ready on the normal database**: their selected outbox table is absent. General delivery remains suppressed. Booking communications are currently in-app, not email.

New local proof: **5 tests / 198 assertions PASS**, with network/mail/process-launch functions disabled and remote PHP file access disabled. Account defaults, actual order-email Blade, and the driver-invitation Blade were rendered with synthetic data. Mobile and MIME findings below are not live-delivery certification.

**Current score remains 16/20 = 80%; C1 and O4 remain 0.5 each.** No application source, business data, workflow, credentials or delivery policy was changed.

## 2. Complete existing email inventory

The embedded matrix contains all **17 requested fields**, including actual-delivery status and the prescribed classifications. The matching CSV is `agendaally-email-inventory.csv`. Sources refer to actual files, not invented template names.

[[EMAIL_INVENTORY_MATRIX]]

There are three owned email Blade views: `emails/layout`, `order-email-invoice`, and `emails/delivery-driver-invitation`. Verification/reset/subscription content is composed inside `EmailSendService` or supplied by `EmailTemplate` records; there are **zero configured template records and zero subscription records in the normal database**. Default bodies still exist in source.

`booking-invoice`, `order-invoice` and `parent-order-invoice` are printable/PDF views, **not emailed attachments or additional email workflows**. `welcome`, `iyzico` and `mtn` are web/payment views, not account-welcome or payment-email templates.

## 3. Email-producing application paths

Repository-wide source discovery covered the backend, Customer/Admin web, uploaded Customer Flutter source, artifact source and development/operations scripts, including untracked source. Dependencies/generated builds were excluded from application discovery; framework fallback hooks were checked separately. The retained mechanism index has 23 source-match files, **not 23 working email flows**.

| Path | Authoritative chain and delivery |
|---|---|
| Admin test | Admin email-setting controller → explicit `AdminSmtpTestPolicy` capability → `EmailSendService::sendTest` → shared Blade + PHPMailer → chosen test recipient. Owner verified this exact diagnostic capability. |
| General verification | Register/AuthByEmail or resend → `SendEmailVerification` → listener issues recipient-bound challenge → `SelectedEmailDelivery::enqueue` → database queue `mvp-notifications` after commit → `SelectedAccountEmail` → `deliver` → `sendVerify` → shared Blade → persisted User email. |
| Password reset | Neutral reset request → known User lookup and issuance limits → `PasswordResetService::issueEmailToken` → selected outbox/queue → current-challenge and recipient checks → `sendEmailPasswordReset` → shared Blade. |
| Dedicated Driver verification | Driver contact-invitation branch → high-entropy native challenge → synchronous `sendVerify`; **not** the general six-digit queued protocol. |
| Subscription immediate | Admin template create/update with immediate-send intent → `EmailSendByTemplate` listener → `sendSubscriptions` → active subscriptions' related User emails → one PHPMailer envelope. |
| Subscription scheduled | Legacy hourly `email:send:by:time` selects matching unsent subscription templates → marks status → same listener/sender. Not part of the selected MVP scheduler. |
| Order creation | Persisted Order object **inside the creation transaction**, when status matches Shop `email_statuses` → synchronous `sendOrder` → actual order-email Blade → Order's related User email. It can send before outer commit if later enabled. |
| Order status | Saved status service result and Shop status allowlist → synchronous `sendOrder` after its transaction → same recipient/view. Not payment capture proof. |
| Driver invitation | Supported invitation issue/resend, persisted invitation/contact/expiry → feature flag and configured origin → `sendDeliveryDriverInvitation` → Laravel `Mail::to(...)->send(Mailable)` → bare invitation Blade. Separate Laravel transport configuration, not the Admin PHPMailer provider-row path. |
| Framework hooks | `Registered` maps to framework verification listener; User inherits framework reset/verification capabilities. No application `Registered` dispatch, password-broker reset send or separate active Notification mail-channel workflow was found. Do not activate these as substitutes for native auth. |
| Tools/harnesses | Admin launcher grants only the already-approved test exception; staging operations/probe and SMTP-settings tests exercise existing services under fixture/policy controls. They are not new marketplace email workflows and were not executed in this audit. |

No independent direct PHP `mail()`, nodemailer, Flutter SMTP sender or artifact-server email transport was found in the searched application source. Native booking notification storage and Firebase transport are **not mail channels**.

## 4. Shared layout audit

The shared layout calls `EmailPresentation` and uses table-based markup, system Arial/Helvetica typography, explicit image dimensions and inline essential styles. Default verification/reset preserve the accepted Admin branding. Essential instructions remain text; images are not necessary to understand the code.

Limitations: the visible content has no consistent purpose heading/structured primary CTA; OTP defaults need code-entry instructions rather than a fabricated verification link. Content and configured footer HTML are rendered unescaped by design. Normal spaced text wraps, but unbroken references/names can expand the table. Future structured templates should escape each field and add safe wrapping without changing booking semantics.

## 5. Logo audit

Shared-layout fixtures loaded the owned PNG at **120×40** at 900/390/320px. Four inline CID parts include the exact owned bytes; no localhost/private-preview image URL reaches the rendered HTML. An unavailable logo has a text-wordmark fallback. Remote logos are not fetched.

The order email uses the same safe resolver/CID bytes, but its legacy CSS displays the logo at **150×40**, unlike the approved shared-layout dimensions. This is a presentation inconsistency, not an SMTP failure. The driver invitation has no logo and is deferred.

## 6. Footer/social audit

Shared/account/order fixtures contain Instagram, Facebook and LinkedIn PNG icons, meaningful alt text and normalized HTTPS links. Unconfigured X/Twitter is omitted. No double-prefixed scheme, private/social placeholder destination, credential-bearing social URL, external icon CDN, essential SVG, webfont or background-image dependency was present in these fixture outputs.

The shared dark footer and the order email's white footer/copyright are separate markup. Order email does not reproduce configured shared `footer_text`; the invitation has no shared footer. Current fixtures show 2026 copyright. Configured footer HTML can contain a static year or arbitrary markup; safe configuration and future year maintenance remain operator responsibilities.

## 7. Action-link audit

General verification and reset defaults contain a **code**, not an action URL. Verification posts recipient email + OTP to the native endpoint. Reset code exchange requires the bound recipient email, after which the client must actually update the password. Do not replace these protocols with links during a branding change.

The driver invitation builds a configured-origin URL with the token in a fragment; this avoids putting that invitation token in the initial HTTP request query. However, its source only validates “a URL”: it does **not** itself require HTTPS, reject credentials/private hosts, or certify that the destination is public and supports the flow. Harden/approve that origin before future driver activation.

Social destinations are allowlisted separately. Admin-configured rich HTML can add arbitrary URLs/assets; there is no general action-link sanitizer. The order-email view has no booking/order-management CTA. No new public domain was selected and no URL was changed.

## 8. Account/security emails

| Capability | Audit result |
|---|---|
| Email verification | Existing shared-layout fallback. Six-digit recipient-bound HMAC challenge, 10-minute cache expiry, replacement invalidation and one-time consumption in source; A1/N1 local evidence retained. Live signup → mailbox → code entry → verified account → usable login remains unaccepted. |
| Password reset | Existing shared-layout fallback. Six-digit recipient-bound digest, 60-minute expiry, issuance/IP/account attempt limits, supersession and atomic consumption. Reset exchange revokes existing API tokens and creates a token; acceptance must continue through password update and old/new-password/session checks. |
| Independent configured reset content | **Not separated**: reset and verification both query `TYPE_VERIFY`. With zero template rows their distinct defaults work, but a configured verification subject/body will also be used for reset. Separate purposes before relying on custom account content. |
| Password-changed/security notice | No dedicated trigger/template found. Useful follow-up, not a prerequisite established by the present C1/O4 rubric. |
| Welcome/activation message | No separate workflow found; verified account state does not imply a welcome email was sent. Optional, not required for these gates. |
| Account/team invitation | Supported relationship records are not automatically email or account-creation authority; see invitation section. |

**Normal-runtime blocker:** `selected_email_deliveries` does not exist in the protected normal SQLite database; `jobs` and `failed_jobs` exist and are empty. The migration exists in source and N1 explicitly applied it only to disposable MySQL. The normal verification listener catches enqueue errors; a neutral registration response is therefore not proof that mail was queued. Reset enqueue can fail after challenge issuance. No account request was executed to mutate the normal demo.

## 9. Customer booking emails

No booking-created/confirmed, rescheduled, cancelled, reminder or meaningful-status **email** sender/template/outbox was found. Existing Booking methods write durable in-app notifications; the reminder command selects upcoming `new`/`booked` bookings within 30 minutes and checks the existing notification preference. It is not an email command.

These emails are useful but **not required to close the current C1/O4 definitions**, which already accept the selected in-app Booking path locally. If approved later, one parameterized Customer booking-notice template can cover committed creation, actual reschedule and actual cancellation. Label creation as “created” or “confirmed” according to its saved status—not merely “confirmed” for every successful save. Review/calculation/opening a dialog must never trigger it. Do not email every intermediate status.

## 10. Vendor/Specialist emails

New-booking, reschedule and cancellation communications exist as in-app hooks deriving recipients from the Booking's Shop owner, assigned Master/Specialist and Customer where present. No corresponding email workflow exists.

Email is optional while these accepted in-app/calendar surfaces remain the MVP channel. A future operational booking notice could serve the Shop owner and current accepted same-Shop assigned Specialist. Recheck tenant/assignment eligibility at delivery; global Specialist role or matching email is never a recipient grant. Assignment-change notices should give only authorized necessary information; no generic reminder or every-status email is required now.

## 11. Local-client emails

Local clients are Shop-scoped directory records, not platform Users. Booking creation can retain `local_client_id` and a null `user_id`. Existing User-based mail/outbox code cannot safely treat that as an account recipient.

A valid local-client email could receive a factual booking notice **only after an approved shop/contact/preference contract and dedicated recipient handling exist**. Confirmation/reschedule/cancellation/reminder mail is currently proposed/deferred for that audience. No matching-email account linking, credentials, signup/verification message, platform-login CTA or automatic Customer creation is permitted.

## 12. Financial/payment emails

No standalone certified service-payment receipt, refund-completion, payout-completion or Vendor-settlement mail flow was found. Financial record existence, an operation reservation, legacy Admin Wallet credits and provider initiation/status are not external settlement evidence.

**DEFERRED — FINANCIAL AUTHORITY NOT YET CERTIFIED.** No new financial email is part of this proposal. A future booking notice should omit payment amounts/status unless an accepted projection exists; if method is shown, distinguish “Cash selected” from “UNPAID / UNCOLLECTED.” Booking status must remain separate. No accounting/provider/refund/payout behavior was changed.

## 13. Product/order/invoice emails

The actual order-email Blade renders Customer/Shop/address/item/price/status fields and escapes synthetic Customer markup. Logo/social CID handling passes local MIME tests. No product/gallery image is referenced by this view; generic template gallery attachments are a separate mechanism.

`sendOrder` sets DomPDF options but **does not generate or attach a PDF**; it sends the HTML summary and inline branding only. It also has **no AltBody**. Printable invoice routes do not establish email attachment behavior. The native email reads the Order's transaction/status for payment labels, without independently certifying external capture; it must not be advertised as a verified paid receipt, particularly for known unverified legacy Orders.

Creation-time email can occur before transaction commit, so later rollback could leave a nonexistent-order message if this deferred path is enabled. Both order triggers lack the selected account outbox's recipient/event claims and UNKNOWN handling. Long unbroken fields overflow; branding/footer differs from the approved baseline. Product purchasing and its known fulfillment/accounting limits remain deferred.

## 14. Subscription/digest emails

This is legacy/general-platform marketing functionality, not the selected service-booking MVP. The immediate event and legacy hourly scheduler share the same sender/layout. Normal data contains zero subscriptions and zero templates; delivery is suppressed and the scheduler was not run.

The sender adds all active subscribers to **one visible `To` list**, exposing their addresses to one another if used. Template status is set before confirmed success, including suppression/failure. There is no required in-email unsubscribe action/List-Unsubscribe construction. The active flag is respected for selection, but that does not certify a safe unsubscribe journey, consent or private delivery. Defer activation until independently corrected and accepted.

## 15. Invitation emails

Specialist/Shop team invitations have native records and in-app supported relationships; no separate email send was found. Do not invent a role grant, account creation, expiry or signed acceptance protocol just to add an email. Vendor/Admin/country invitation/approval records are likewise not evidence of an emitted welcome/invitation email.

Delivery-driver invitations have a dedicated persisted invitation/contact contract, **7-day expiry**, token rotation on resend, recipient binding and feature/origin gates. The bare Blade renders escaped Shop name, review action and expiry text. It uses Laravel Mail rather than the Admin provider-row PHPMailer path, lacks the shared branding, and has no live receipt acceptance. Delivery/Driver work is deferred from this service-booking MVP. Dedicated Driver verification is a different, high-entropy protocol and must not be confused with Customer OTP acceptance.

## 16. Security findings

Presentation problems are recorded separately; no destructive exploit or broad security campaign was run.

| Priority / scope | Security or integrity finding | Evidence / disposition |
|---|---|---|
| High before marketing activation | One visible `To` envelope discloses subscriber addresses | Static sender loop; suppressed, zero subscription rows. Use isolated per-recipient envelopes, reviewed consent/unsubscribe and no premature “sent” status later. |
| High before order-email activation | Creation can send before commit; transaction labels can be interpreted beyond certified payment authority | Actual creation call inside transaction and order-email payment projection. Deferred; do not use as collected/settled receipt. |
| Medium before custom content | Shared body/footer accept raw configured HTML and URLs; no general content/link sanitizer | Intentional Blade raw blocks. Current default OTP strings are source-generated and safe; keep configuration privileged and sanitize/structure future recipient-controlled fields. |
| Medium before legacy activation | Subscription/order/base-auth diagnostics are less redacted than the controlled Admin/selected mail path | Source logs/return structures can contain recipient/provider diagnostics. No historical-log clearance or actual credential-leak claim is made. |
| Medium before Driver activation | Public-origin URL validation permits more than approved public HTTPS destinations | Static origin gate; flag-gated and deferred. Do not copy the Driver URL rule into MVP booking links. |
| Medium configuration integrity | Reset custom template aliases verification type | Source query; zero current custom rows mitigates this audit's fallback fixtures, not future configuration. |

Positive bounded evidence: actual order Customer markup was HTML-escaped; PHPMailer rejected newline-bearing recipient syntax and did not create an injected Bcc header during local `preSend`; owned-file realpath/root containment rejects traversal, unavailable files and remote fetches; social URL guards reject unsupported/private/malformed/credential-bearing destinations; selected payloads are encrypted and bind current recipient/challenge; uncertain delivery is terminal UNKNOWN, not automatic resend.

No cross-Shop booking-email exposure exists to test because that email workflow does not exist. Existing invitation/booking authorizations remain unchanged; this audit is not a new full tenant-security certification. SMTP encryption, blank-password behavior, password-free API responses, sanitized Admin errors, port-465 implicit TLS and peer/hostname verification were not modified.

## 17. Responsive/local-render results

Actual generated email documents were loaded in **900/390/320px iframe viewports**; individual 320px captures include the full long-text footer. These are browser layout checks, not Gmail/Outlook emulation. Exact attached PNG bytes were used as data URIs only for browser display.

| Fixture | 900px document width | 390px | 320px | Result |
|---|---:|---:|---:|---|
| Verification default | 900 | 390 | 320 | PASS: readable code, logo/footer/icons |
| Reset default | 900 | 390 | 320 | PASS: readable code, logo/footer/icons |
| Shared spaced long names/reference/date/time/CTA | 900 | 390 | 320 | PASS: all five field categories and long CTA wrap; footer visible |
| Shared unbroken names/reference | 993 | 993 | 993 | **FAIL: horizontal overflow; logo/footer can be offscreen** |
| Actual order email, spaced long fields/items | 900 | 390 | 320 | PASS for page overflow; item columns very narrow at 320 |
| Actual order email, unbroken fields | 1251 | 1121 | 1121 | **FAIL: horizontal overflow** |
| Driver invitation, long spaced Shop | 900 | 390 | 320 | PASS for overflow/readable link; lacks shared branding |

The order fixture intentionally supplies only the title translation; visible fallback keys in those screenshots are fixture translations, **not a finding that the normal translation database is broken**. Synthetic Customer markup is intentionally displayed as escaped text. Shared booking-like content is a layout stress fixture, not an implemented booking template or a real booking.

[[EMAIL_SCREENSHOTS]]

## 18. MIME/CID/attachment results

The new isolated suite passed **5 tests / 198 assertions**; one pre-existing PHPUnit XML deprecation remains. It used in-memory settings/translations, `.invalid` addresses, `preSend` only, remote-file access disabled, and disabled socket, mail, cURL and process-launch functions. The initial order fixture incorrectly passed a null logo; it was corrected to pass the same owned logo path as native `sendOrder`. The failure receipt is retained; no application defect was “fixed” to make the test pass.

Six shared/order messages each had four HTML CID references matched to four inline PNG MIME parts and **exact embedded-byte comparisons**. Account cases include text alternatives. Order cases truthfully omit AltBody/PDF; there are zero ordinary/PDF attachments. Prior 5-test/73-assertion evidence already covers local gallery attachment filename/disposition/root resolution and rejects traversal; it was reused, not rerun.

The actual Driver Mailable build and Blade were rendered; a local Symfony Email produced valid HTML MIME without sending. This proves local view/payload packaging only, **not Laravel transport configuration or receipt**.

| Email | Template/content exists | Local render | MIME | Trigger | Queue/worker | SMTP for this email | Mailbox for this email | User journey |
|---|---|---|---|---|---|---|---|---|
| Admin test | Yes | Prior PASS | Prior PASS | Owner-confirmed | N/A, synchronous | VERIFIED | VERIFIED, corrected presentation | VERIFIED diagnostic only |
| Verification | Yes, fallback | PASS | PASS | Source + retained A1 | Retained disposable-MySQL N1 only; absent normal outbox | Not verified | Not verified | Live completion not verified |
| Reset | Yes, fallback | PASS | PASS | Source + retained A1 | Same limitation | Not verified | Not verified | Live password change not verified |
| Subscription paths | Yes, configurable/shared | Shared shell only | Shared assets only | Source | Legacy schedule source only | Not verified | Not verified | Not verified |
| Order paths | Yes | Actual Blade PASS; stress failures noted | CID PASS; AltBody/PDF absent | Source, timing defect noted | Synchronous, no selected outbox | Not verified | Not verified | Deferred |
| Driver invitation | Yes | PASS, unbranded | Local Symfony PASS only | Source | Synchronous | Not verified | Not verified | Deferred |
| Driver verification | Shared view + dedicated challenge | Shared asset infrastructure only | Not separately certified for dedicated protocol | Source | Synchronous | Not verified | Not verified | Deferred |
| Framework fallbacks | Framework capability, not owned active path | Not run | Not run | No application dispatch found | Not active here | Not verified | Not verified | Not verified |

## 19. Missing MVP emails

**No additional email template is necessary solely to close the current C1/O4 rubric.** The missing capability is safe operational deployment/acceptance of existing verification and reset, including the normal outbox schema and a purpose-scoped transport allowance.

Customer booking creation/reschedule/cancellation and Vendor/Specialist booking notices are missing as email, but remain useful post-MVP additions given accepted in-app notifications. If the owner changes the launch promise to require them, they become explicit additional acceptance work—not credit earned in this audit.

## 20. Deferred/non-MVP emails

Defer local-client email until its Shop-scoped contact/preference/read-access contract is approved; reminders until channel preferences and due/superseded behavior are defined; password-changed/welcome/Shop invitation email as useful enhancements; delivery/Driver and marketing as separate product work.

Defer payment, financial receipt, refund, payout, settlement, electronic reconciliation, multi-Shop commerce and Product payment/order launch mail until their authoritative financial/product features are certified. Do not trigger them from cash selection, booking confirmation or reserved operations.

## 21. Legacy/unused email paths

The unused framework `Registered` verification hook and generic broker/reset capability are candidates for removal only after a compatibility review; do not delete a User verification contract used elsewhere. The source does not emit those framework events for native signup.

Legacy marketing and Product order-email flows are **deferred, not proven dead**: controllers/services/scheduler still reference them. Driver invitation code is supported future work, not dead solely because delivery is disabled. No code was removed.

## 22. Recommended minimum MVP email set

| Group | Minimum decision |
|---|---|
| A — required | Existing User email verification and password reset, with correct purpose, safe recipient/challenge, usable client completion and selected operational delivery. Retain Admin test as an accepted diagnostic, not a customer notification. |
| B — useful later | One Customer booking-notice design for creation/reschedule/cancellation; one role-aware Vendor/Specialist operational notice if email becomes a promised channel. Optional assignment notice, reminder, welcome/password-change and Shop invitation notices. |
| C — financial deferral | No collected/paid/refunded/settled/payout-complete mail without certified evidence. No new financial template now. |
| D — legacy/not relevant | Bulk subscription/digest and inactive framework fallback paths are not first-launch requirements; Product/Driver belongs to later scope. |

There is no reason to generate a template for every status or repeat the already-accepted generic SMTP test.

## 23. Proposed implementation files/architecture

**Proposal only. No files in this section were implemented.**

First, deploy the **existing** `database/migrations/2026_10_07_010000_add_selected_email_deliveries.php` only after explicit schema/environment approval and backup/preservation review. It is operational evidence, not financial/booking capacity authority. Do not silently migrate the protected normal demo. Respect the owner's chosen normal Admin environment; do not create another Admin preview solely for SMTP.

Add an explicitly reviewed purpose/recipient allowance for verification/reset while preserving general log-only delivery, so opening C1 does not silently activate legacy marketing, order or Driver mail. Likely files: `app/Helpers/EnvironmentPolicy.php`, proposed `app/Helpers/ApprovedEmailWorkflowPolicy.php`, `config/development.php`, `app/Services/EmailSettingService/EmailSendService.php`, `SelectedEmailDelivery.php`, and `Jobs/SelectedAccountEmail.php`. The factory and suppression guard must both enforce it. Do not automatically copy/retrieve credentials. Retain encrypted payloads, after-commit dispatch, committed claims, challenge expiry/supersession, recipient checks and terminal UNKNOWN.

For independent custom reset content, review `app/Models/EmailTemplate.php`, `app/Http/Requests/EmailSetting/EmailTemplateRequest.php`, the native Admin `src/views/message-subscribers/subciribed-add.jsx`/`subciribed-edit.jsx` type options and `EmailSendService.php`. Backend paths are relative to `.migration-backup/backend`; Admin paths are relative to `.migration-backup/admin`. Keep distinct safe defaults; do not add a second auth protocol.

Optional future template proposals:

| Proposed template | Recipient / trigger / authoritative source | Subject and fields | CTA / payment wording | Delivery and tests | Likely exact files |
|---|---|---|---|---|---|
| Customer booking notice, parameterized by created/rescheduled/cancelled | Booking's actual platform User; only successful committed `BookingService::create`, a real committed start/end change, or permitted committed cancellation. Persisted Booking/Shop/Service/assigned Specialist; accepted saved times/duration/status and action identity. | `Booking created — {reference}`, `Booking rescheduled — {reference}`, or `Booking cancelled — {reference}`. Reference, Shop, Service, Specialist, date/start/end, duration, status; old/new times only for real reschedule. “Confirmed” only for authoritative confirmed status. | One existing authorized booking-detail CTA using owner-approved public app origin; login must not expose another Shop. No bearer token in URL. Omit money unless certified; if method is shown, `Cash selected — UNPAID / UNCOLLECTED`, separate from booking status. | Persist operational intent with mutation; queue after commit; dedup replay; recheck recipient/current eligibility; suppress rolled-back, obsolete or superseded notices. Tests: no Review/calculation send, rollback/no outbox, replay, cross-Shop and removed-assignment denial, saved fields, 900/390/320 long data, HTML escaping/CID and later approved receipt. | Proposed `resources/views/emails/booking-notice.blade.php`; `app/Services/EmailSettingService/BookingEmailDelivery.php`; `app/Jobs/DeliverBookingEmail.php`; operational migration only if approved; existing `BookingService.php`, relevant `Dashboard/{User,Seller,Master,Admin}/BookingController.php`; `tests/Hardening/BookingEmailDeliveryTest.php`. |
| Vendor/Specialist operational booking notice | Persisted Shop owner/current same-Shop accepted assigned Specialist, not a global role list. Committed creation/reschedule/cancellation; separate assignment event only if needed and authorized. Same persisted reference/schedule/status, minimal customer contact details. | `New booking — {reference}`, `Booking rescheduled — {reference}`, `Booking cancelled — {reference}` or authorized `Booking assigned — {reference}`. Required common fields above; no internal notes or unrelated Shop data. | One role-authorized native dashboard/calendar/detail route. No claim of collection/settlement. | Same selected queue/operational dedup pattern; dedup one platform identity with overlapping roles; delivery-time access recheck. Test revoked membership, reassignment, same-Shop boundaries, rollback/retry and minimal disclosure. | Proposed `resources/views/emails/booking-operations-notice.blade.php`; reuse proposed delivery/job; existing authoritative Booking/controller/assignment hooks; proposed `BookingEmailDeliveryTest.php`. |
| Appointment reminder, if later approved | Eligible saved non-cancelled/non-ended appointment, correct recipient and separately approved channel preference. Preserve accepted reminder window/current start, never infer new scheduling rules. | `Upcoming appointment — {reference}`; reference/Shop/Service/Specialist/date/start/end/duration/status. | Same authorized view action for platform User; no payment claim. | Selected scheduler only; one intent per accepted occurrence/recipient; skip expired, cancelled or superseded appointments. Test delayed tick, opt-out, reschedule/cancel and duplicate ticks. | Proposed `resources/views/emails/appointment-reminder.blade.php`; existing `Console/Commands/BookingNotification.php`/`SelectedMvpBackground.php` only after channel approval; proposed delivery service/job and reminder tests. |

Use the approved header, clear visible purpose, escaped authoritative details, one accessible text/button action when needed, native support path, configured social footer and current copyright. Consolidate asset preparation and safe header/footer partials, not financial logic: proposed `emails/partials/header.blade.php`/`footer.blade.php`, existing `emails/layout.blade.php` and `app/Support/EmailPresentation.php`. Preserve specialized order/PDF semantics; fix long-string wrapping and logo ratio separately if approved.

No new booking status, scheduling identity, money equation, timezone conversion or repricing is authorized. Use accepted native date/time formatting with an explicit approved timezone label; if that display contract is unresolved, stop rather than invent it. Event identity must survive retry; a second-resolution `updated_at` value alone is not an adequate new email dedup contract.

Local clients are **not recipients of these platform-User proposals**. A later separate client-contact variant requires Shop ownership/contact preference checks, no account linking/credentials, and no private platform account CTA. Its implementation/portal semantics need approval before concrete workflow work.

## 24. C1 readiness result

**C1 Customer access/reset: 0.5, unchanged.** It currently proves retained local/native A1 challenge binding, expiry/replay/attempt controls and selected application access evidence. The shared account presentation/MIME is now locally checked. Generic SMTP/diagnostic receipt is accepted infrastructure evidence.

It does **not** prove actual normal-runtime outbox deployment, account-email delivery, fresh account activation through the Customer UI or successful live reset/password change. The missing table is a specific deployment blocker, not a reason to rerun the SMTP diagnostic.

To move to 1: obtain bounded schema/purpose-delivery approval; install/check the existing outbox on the approved target; one fresh verification journey must reach the intended mailbox, verify the exact account and complete usable login/logout. One reset journey must deliver to the intended account, complete actual password update, prove old/new password behavior and reject reuse/wrong-recipient/expired or superseded challenge with safe UI errors. Reuse existing local negative security evidence where unchanged; actual completion is essential.

## 25. O4 readiness result

**O4 Communications/background reliability: 0.5, unchanged.** It proves retained N1: 22 native checks for outbox ordering/encryption/dedup/rollback, worker loss/restart, claims, UNKNOWN/no-resend, expiry/supersession, failures, in-app notifications and reminders; four selected-scheduler checks; plus now owner-confirmed SMTP and corrected Admin mailbox presentation.

It does **not** prove approved selected worker mail delivery, operational schema on the intended runtime, or production-like worker/scheduler supervision/health. Admin synchronous diagnostic success bypasses those paths.

To move to 1: deliver the same two approved account journeys through the real selected queue on the approved target, verify committed outbox/claim/SENT evidence and actual receipt without duplicate delivery; show safe-before-transport worker stop/restart recovery, supervisor persistence and redacted failure/health visibility. Verify only the selected `mvp:background-tick` schedule and `mvp-notifications` queue; verify in-app reminder/dedup behavior without broad cron/provider jobs. Reuse accepted uncertain-ack/rollback tests rather than deliberately provoking an ambiguous live SMTP resend. Supervisor process being alive alone is insufficient.

Booking email is not a hidden O4 prerequisite under the existing rubric. If added to launch scope later, its own authoritative-event/recipient/mailbox acceptance is additional.

## 26. Current 20-gate score — do not inflate it

| Area | Current points |
|---|---:|
| Customer C1–C6 | 5 / 6 |
| Vendor V1–V4 | 4 / 4 |
| Admin A1–A4 | 3 / 4 |
| Operations O1–O6 | 4 / 6 |
| **Total** | **16 / 20 = 80%** |

All other partial/full gates remain exactly as accepted in readiness sections 20–21. This audit does not re-award N1, A1, SMTP or calendar points. **REMAIN IN STAGING**.

## 27. Hypothetical score after specifically identified missing acceptance tests pass

| Later verified closure | Conditional score |
|---|---|
| C1 only: all specific access/reset requirements above | 16.5/20 = 82.5% |
| O4 only: all specific selected-delivery/supervision requirements above | 16.5/20 = 82.5% |
| Both C1 and O4 | 17/20 = 85% |

These are hypotheses, **not current earned points or production approval**. Unrelated remaining gates still apply.

## 28. Minimum future real-email acceptance campaign

**Proposed minimum: two real messages, not a new Admin Test Email, and not executed.**

1. Fresh controlled Customer verification: source trigger → committed selected outbox → safely paused/restarted selected worker → intended Gmail receipt and baseline presentation → recipient-bound code entry → verified account and usable login/logout.
2. Same controlled account's password reset: neutral request → selected outbox/worker → Gmail receipt → bound code exchange → actual password update → old/new-password and consumed-code behavior.

Their real queue/receipt traces can serve both C1 and O4. Cover duplicate job/no duplicate message, expired/superseded suppression, redacted diagnostics and selected scheduler/supervisor health with local/operational evidence, **not additional unnecessary real emails**. Existing N1 uncertain-ack evidence is reusable; do not force another real send after UNKNOWN.

No Customer booking or Vendor/Specialist email is needed to close the present rubric because those email workflows are not the accepted MVP communication channel. If the owner explicitly adds them, later approval must include a committed booking notice and separately proven authorized role recipients; no generic mailbox result can substitute.

Prerequisites: owner-approved environment and schema change, purpose/recipient confinement without global delivery activation, credential-use authority without retrieval/copying, supported public/TLS/client completion configuration, selected process supervision and a preservation plan. No extra preview, SMTP credential transfer, scheduler activation or send is approved by this proposal.

## 29. Preservation result

**PASS:** read-only before/after snapshots show **206 tables unchanged** across their stored row counts and fingerprints; **467 schema objects unchanged** including SQLite internals. A separate comparison shows **4,683 application source files unchanged** in the audited normal source scopes.

Bookings, local clients/Users, payments, Wallets, Orders and non-credential SMTP provider fields were not mutated. The SMTP password column was **not selected or decrypted**; no credential-write operation occurred, so no independent password-byte comparison is claimed. This is a no-write audit, not a secret fingerprinting operation.

No normal account/booking creation, SMTP connection, mail send/postSend, worker/scheduler activation, payment/SMS/push change, package installation, workflow reconfiguration/restart or deployment occurred. Only audit tests, documentation, evidence and synthetic static preview files were added/updated. Existing previews remain running; unrelated campaigns were not rerun.

## 30. Final recommendation

Do **not** add a large template set or repeat generic SMTP testing. First seek explicit approval for the existing outbox deployment and purpose-scoped verification/reset allowance, then the two-message C1/O4 campaign with selected process acceptance. Keep booking email optional unless the launch promise changes; keep marketing, Product/Driver and financial mail deferred.

The audit is complete. **STOPPED pending explicit approval.** Current decision remains **REMAIN IN STAGING, 16/20 = 80%**.
