# Controlled account-email acceptance

Date: 2026-10-05. Normal owned Customer/Laravel preview; no code, schema, credential or global transport changes.

## Authority and account safety
The owner approved one new disposable Customer, one verification email and one reset email only. Read-only normalized/case-insensitive checks found no matching user of any role, driver invitation, booking-client contact or password-reset record. Existing account reuse was not allowed.

The native signup UI made one register request (HTTP 200), created exactly one new standard Customer (`user` role) and reached the six-digit verification screen. All 38 previous user rows and their role rows are preserved. Native new-account audit entries and a zero-valued points row are the only additional signup effects beyond the user/role and selected outbox/job.

## Verification transport
Preflight: one PENDING verification, one fresh unreserved job, no unrelated pending messages or failed jobs. Exact-record expiring private approval authorized the existing selected CLI processor only.

One isolated native worker invocation finished **SENT** at 2026-10-05 23:02:16 UTC. No shared queue was consumed; no retry occurred. Temporary approval was removed immediately afterward. General transport remains log-only; selected authority remains default-off.

After processing: one historical SENT verification row, zero pending/uncertain messages, zero jobs and zero failed jobs. No unexpected table changes during sending; schema unchanged. All pre-existing business/financial/provider/account rows remain preserved. SMTP credential was not retrieved, displayed, copied or changed.

## Still open
The owner confirmed actual Gmail receipt and correct presentation, logo,
social icons, content and layout. Verification must not be resent.

The owner also reported successful UI verification. However, the required
read-only reset preflight still found the approved disposable Customer
unverified (`email_verified_at` absent), with no password set. The normal
verification controller persists that timestamp when a code is successfully
consumed, before profile/password completion. The reported UI success and
approved-source persisted state therefore need reconciliation; no flag was
forced or account changed to bypass this gate.

**Reset acceptance stopped before issuance or sending.** There are zero reset
outbox records, zero pending/uncertain deliveries, zero jobs and zero failed
jobs; temporary approval remains absent. No generic Admin SMTP test or other
email occurred. No broad worker/scheduler was activated by this campaign.

Server acknowledgement is not mailbox delivery confirmation. The native verification lifetime is 10 minutes; expiry does not authorize a resend.

**C1=0.5; O4=0.5; 16/20=80% remain unchanged** pending sufficient live acceptance evidence.

### Subsequent read-only trace
The owner corrected the earlier successful-UI-verification claim. Current
external port-3002 routing was confirmed to the normal Laravel/owned SQLite
source. Retained logs do not contain a verification POST; the original code
has expired. Later ordinary signup requests renewed the challenge but did
not send email under log-only/default-off delivery.

Additional independently recorded requests have since created two unsent
verification records and one unsent reset record, with three unreserved jobs.
They were discovered read-only and were not issued, processed or deleted by
the diagnosis. Thus the earlier empty-queue result is historical, not current.
See `account-verification-readonly-trace.md` for the timestamped evidence and
its limitations. Reset acceptance remains stopped.

### Bounded recovery checkpoint
At the owner's explicit request, the three unsent acceptance artifacts were
cancelled through a guarded application operation, without processing their
jobs. Outbox evidence was retained with terminal cancellation reasons; only
the three exact unattempted jobs were removed. The existing native reset-token
discard mechanism revoked only the matching stale acceptance challenge.
The historical SENT verification and Customer state were preserved.

The normal owned selected outbox now has zero active/uncertain records, zero
jobs and zero failed jobs. The verification-only retry evidence context is
prepared without a new challenge or sending authority. The normal Customer
preview must be restored and the owner's actual code-entry session ready
before issuance; explicit approval is required for one new verification send.
See `account-email-bounded-recovery.md`. No reset or new verification was sent;
C1/O4 and 16/20 remain unchanged.

The owner subsequently authorized one verification retry and confirmed their
fresh six-digit screen was ready. The exact fresh native record/job passed
isolation checks and was processed once. SMTP DATA received a numeric 250
acknowledgement at 2026-10-06 01:24:08 UTC; the record is SENT and temporary
approval was removed immediately. Queue/failed-job state is clean and
unrelated data/schema preserved. Gmail receipt and verification
submission/state transition still require evidence; no reset was issued.
The detailed chain is in `account-email-bounded-recovery.md`.

The owner then confirmed the new Gmail receipt and Verify opening the next
screen. The backend journal proves a successful HTTP 200 verification
response and unverified→verified transition at 2026-10-06 01:24:55 UTC.
Read-only normal-source inspection confirms that timestamp, cleared
verification marker and one access token. An earlier HTTP 404 rejection
is retained separately without guessing its cause or recording any code.
The current account has a password, but reset/password-change/login behavior
has not been certified.

The verification chain is closed and its temporary evidence context archived.
Outbox/jobs/failed-jobs are clean and send authority absent. No reset was issued.
Full C1/O4 requirements remain incomplete, so C1=0.5, O4=0.5 and 16/20=80%
remain unchanged.

### Owner-completed login/logout reconciliation
The owner subsequently confirmed successful normal Customer UI login/logout
without another email/code and requested no unnecessary repeat. Read-only
inspection corroborates login through a new account-bound token created at
2026-10-06 01:40:07 UTC, subsequent token usage and matching retained native
login/profile requests.

The UI logout confirmation is preserved, but server revocation is not certified:
both account tokens remain, with no configured expiry, and no logout request
appears in the finite retained workflow window. Native Customer source at that
checkpoint gated server logout on a push token; the no-push-token path cleared
client auth only.
That is a matching source explanation, not a captured owner push-token value.

The agent did not repeat login/logout, inspect credentials, delete tokens, alter
the account, change code or issue/send any email. Verification and selected
outbox metadata remain unchanged; active outbox/jobs/failed-jobs/reset rows
remain zero. The sanitized private checkpoint is
`.local/staging-mvp/account-login-logout-checkpoint.json`.

Remaining: server-side logout revocation, separately authorized reset/password
change and remaining selected operational acceptance. Score remains unchanged.

Redacted operational evidence: `.local/staging-mvp/account-email-live-issuance.json`, `account-email-live-pre-send.json`, `account-email-live-verification-result.json`, `account-email-verification-worker.log`, and `account-email-reset-preflight-stop.json`.

### Subsequent bounded logout repair

The owner also observed that logout required manual refresh and explicitly
authorized a compatible repair. Server logout is now unconditional on push
registration; optional push cleanup is separate, and successful logout
publishes shared signed-out state and automatically revalidates navigation.
Native isolated and browser-fixture checks confirm push/no-push logout,
revoked-token 401, sibling/other-account preservation, safe repetition and
failure-state retention. See
[`customer-logout-repair.md`](customer-logout-repair.md) for the fixture limits
and private evidence pointers.

No completed owner journey was repeated, no email/code was issued or sent,
and the previous account token records were not manually revoked.
Verification and queue evidence remain preserved; all 24 retained
financial/booking fingerprints match the earlier single-send checkpoint.
The historical logout gap remains historical, and reset plus remaining live
operational acceptance still require separate authority.
**C1=0.5; O4=0.5; 16/20=80% unchanged.**
