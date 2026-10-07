# Bounded C1/O4 recovery

## Scope and classification
Recovery was explicitly authorized for the three documented unsent acceptance artifacts belonging to the approved disposable Customer only.

The application inspected the native encrypted recipient binding without exposing its email or challenge. Each record belongs to Customer 144, and each has exactly one native `SelectedAccountEmail` job on `mvp-notifications`, with zero attempts and no reservation. Creation/job timestamps match the previously documented native requests:

| Kind | Created (UTC) | Native job | Classification at inspection |
|---|---|---|---|
| Reset | 2026-10-05 23:42:19 | 2 | Unsent acceptance artifact cancelled explicitly; not yet expired |
| Verification | 2026-10-05 23:48:28 | 3 | Expired, later superseded |
| Verification | 2026-10-05 23:52:56 | 4 | Expired |

The retained request logs do not identify the initiating person/browser. Ownership and exact record/job bindings are proven; actor attribution is not claimed.

## Authoritative cleanup
The existing general recovery function was deliberately not called: it could enqueue other live records or expire unrelated messages.

A narrowly scoped application cancellation service was added and exercised in isolated tests. It checks the complete frozen selection before changing anything, refuses altered/attempted/reserved/sent/ambiguous jobs, and performs the cancellation atomically:

- All three outbox rows retained, terminal `EXPIRED`, reason `ACCEPTANCE_RECOVERY_CANCELLED`.
- Only exact native jobs 2, 3 and 4 removed without processing.
- The reset challenge discarded by the existing exact-recipient/exact-token `PasswordResetService` mechanism. Newer/unrelated tokens are not discarded.
- The historical SENT verification is unchanged.
- No verification timestamp, password, account row, role or financial data altered.
- No send, new challenge, broad worker, scheduler, Admin test or transport activation occurred.

Cleanup committed at **2026-10-06 00:34:26 UTC**.

Before-state fingerprints, pre-commit intent and committed receipt are retained as private operational evidence:

- `.local/staging-mvp/account-email-recovery-before.json`
- `.local/staging-mvp/account-email-recovery-intent.json`
- `.local/staging-mvp/account-email-recovery-after.json`

The row ordering/serialization contract is included with the fingerprints. SMTP credential fields were not selected. Schema and every table outside `jobs`, `password_resets`, and `selected_email_deliveries` matched the before snapshot.

## Clean-state checkpoint
After cleanup and retry preparation:

- Active selected outbox records (`PENDING`, `BLOCKED`, `SENDING`, `UNKNOWN`): **0**.
- Native database jobs across all queues: **0**.
- Failed jobs: **0**.
- Outbox evidence retained: one historical SENT and three terminal cancellation records.
- No unrelated pending email in the normal owned selected outbox/queue boundary.
- Selected SMTP approval file absent; general delivery remains suppressed.
- Disposable Customer remains unverified, with no password or access token.

This statement concerns the approved normal owned SQLite runtime, not separate staging/acceptance databases.

## Fresh attempt prepared, not issued or sent
A private, expiring, verification-only evidence context has been prepared. It has **no SMTP authority**. A fresh challenge/outbox record has intentionally not yet been created:

1. The original Customer preview on 3002 is currently failed while the selected Customer preview on 3000 holds the shared Next development-server lock.
2. Code issuance would begin the native ten-minute lifetime before the owner has the correct code-entry screen available, repeating the previous handoff problem.

Before issuance, obtain permission to temporarily pause the conflicting selected Customer preview and restore the existing normal Customer preview against normal Laravel on 8000. Do not change the separate business/staging workers or schedulers.

Ask for explicit authority for **one new verification email only**, conditional on:

1. Normal preview runtime/proxy/database identity rechecked.
2. Owner's actual native signup/code-entry session ready.
3. Exactly one fresh native verification request; owner does not repeat signup or resend.
4. Exactly one current, recipient-bound outbox/job, no unrelated pending mail/jobs, no failed jobs.
5. One expiring exact-record CLI SMTP approval; one isolated worker invocation, no automatic retry.

Do not issue any reset. A reset requires separate later authority and real persisted verification.

## Safe evidence chain for the retry
The instrumented backend records only safe metadata in:

`.local/staging-mvp/account-email-recovery-events.jsonl`

The context is private, expiring, restricted to the normal owned local SQLite source and the approved disposable recipient. It cannot enable email transport. No request body, live code, reset token, access token, SMTP credential, raw dialogue or message body is logged.

The expected proof sequence is:

1. **Verification request:** backend-generated request correlation ID, approved user ID, prior verification state.
2. **Selected outbox:** response-linked new outbox UUID, kind/state/creation/expiry.
3. **Isolated processing:** exact native job, original queue and selected-only queue, unattempted/unreserved preconditions and committed claim.
4. **SMTP acknowledgement:** actual PHPMailer `DATA` result and numeric SMTP reply only; successful sender return and persisted SENT state.
5. **Gmail receipt:** owner's explicit receipt confirmation persisted as its own evidence event. SMTP acknowledgement does not substitute for it.
6. **Verification submission:** separate request correlation ID and prior state; never the submitted code.
7. **Backend response and transition:** HTTP status, safe machine code, persisted verification timestamp/state and access-token count.

Keep the evidence context alive only for the bounded campaign and remove/close it afterward. If the context expires or another challenge is issued, stop rather than silently renewing it. If SMTP acknowledgement is uncertain, preserve UNKNOWN/failed evidence; do not retry.

## Owner-authorized restoration and readiness handoff
The owner explicitly authorized restoring the preview and one fresh verification
email, conditional on the real recipient's code-entry session being ready.
Only `selected-customer-acceptance` was paused; the existing
`original-customer-preview` was restored on 3002. The normal Laravel service
remains on 8000. Business/staging workers and all other previews were untouched.

The supplied external `/sign-up` and local normal Customer page both returned
HTTP 200. The active development manifest maps `/api/v1/:path*` to normal
Laravel on 8000; both normal public country probes returned four countries.
The normal outbox/jobs/failed-jobs state was rechecked clean, general transport
remains log-only, and the selected SMTP approval file remains absent.

Safe owner-authorization and runtime-restoration events were persisted.
No fresh challenge or email was issued by the agent during restoration.
Await the owner preparing **one fresh native signup request in their own
browser**, leaving its six-digit screen open, and immediately confirming
readiness without sharing any code. That request starts the native ten-minute
clock. Do not repeat signup, resend, reset or issue another challenge if the
handoff fails; stop and report the evidence instead.

## Authorized single retry: SMTP checkpoint
The owner confirmed one fresh signup in their own browser and that the
six-digit screen was open, with no code entry, resend, repeated signup or reset.
Native request `9afc079c-67e0-4117-a8cd-9a65341a51b9` returned HTTP 200
and created verification delivery `98a6bc42-0877-4fb7-bf41-3aaa75b0c45f`
at **2026-10-06 01:21:11 UTC**, expiring **01:31:11 UTC**.

Preflight proved exactly one current, recipient-bound verification and its
one unattempted/unreserved native job (5), no unrelated pending messages and
zero failed jobs. One expiring exact-record CLI approval was created and one
isolated native worker invocation performed. No challenge was reissued.

The provider acknowledged SMTP DATA with **250** at **01:24:08 UTC**.
The native delivery became **SENT**; the private send approval was removed
immediately. Post-send: zero active/uncertain records, zero jobs and zero failed
jobs. Only `jobs` and `selected_email_deliveries` changed during the send;
all other tables and schema matched the before-send snapshot.

Private preflight, one-shot intent and result receipts:

- `.local/staging-mvp/account-email-retry-pre-send.json`
- `.local/staging-mvp/account-email-retry-send-intent.json`
- `.local/staging-mvp/account-email-retry-result.json`

The safe event journal links the native request, outbox, exact isolated job,
processing claim, numeric DATA acknowledgement, SENT state and permission
removal. No live code, SMTP credential, dialogue or message body was exposed
or retained in these receipts.

At the SMTP checkpoint, owner's Gmail receipt and verification submission
were still awaiting confirmation. They are now reconciled below. No reset
was issued, and no retry/new challenge is authorized automatically.

## Verification receipt and state-transition closure
The owner confirmed receipt of the new Gmail email and that Verify opened
the next screen. Read-only inspection found the exact approved disposable
Customer verified at **2026-10-06 01:24:55 UTC (8:24:55pm Central)**.

The safe journal retains both submission outcomes without any submitted code:

- **01:24:31 UTC:** request `9735f622-1fb1-4103-90ea-9a525a83617e`;
  HTTP 404 / `ERROR_404`, verification remained false and token count zero.
  The code-free evidence does not establish why that submission was rejected.
- **01:24:55–56 UTC:** request `b471745e-3d36-4f78-8abe-8d55d568da81`;
  HTTP 200, state changed from unverified to verified, exact persisted
  timestamp **01:24:55 UTC**, access-token count changed to one.

The later native success, current database timestamp and owner's UI/mailbox
confirmation match. The verification marker is cleared. Current read-only
inspection also reports a password present, but this is **not** proof of a
reset/password-change journey or old/new-password login behavior.

The seven-stage chain is now retained: fresh native request → exact outbox
record → isolated job/claim → numeric SMTP acknowledgement → owner-confirmed
Gmail receipt → correlated code submission/response → persisted verification.
The final private checkpoint is:

`.local/staging-mvp/account-email-retry-verification-checkpoint.json`

Zero active/uncertain outbox records, zero jobs, zero failed jobs and no send
approval remain. The temporary evidence context was archived/closed after
persisting the owner's confirmation and checkpoint. No account flag,
verification timestamp or password was written by the agent to obtain this
result. No reset, extra verification, Admin test or broad worker was started.

## Owner-completed login/logout: read-only reconciliation
The owner explicitly confirmed successful login and logout through the normal
Customer UI, no additional email/code request or send, and prohibited repeating
that completed journey unnecessarily. The agent did not repeat it.

Read-only normal owned application inspection after the owner's report
corroborates authenticated login; its exact observation time is retained in
the private checkpoint:

- A new account-bound access-token record was created **01:40:07 UTC**, used
  through **01:40:21 UTC**.
- Retained native workflow output contains `POST /api/v1/auth/login` at
  **01:40:07 UTC** and authenticated profile requests at **01:40:09 UTC**.
- Verification timestamp remains **01:24:55 UTC** and the verification marker
  remains cleared. No password or credential value was inspected.

**Server-side logout/revocation is not certified.** Both the verification-era
token record and the new login-era token record remain. Their expiry fields
are null and Sanctum's configured expiration is null. No logout request appears
in the retained finite workflow scrollback; this is not exhaustive historical
coverage and does not identify every request's actor.

Source inspection at that reconciliation checkpoint explains a matching
possible path: the native Customer `useAuth` hook called the server logout
mutation only when `fcmToken` was present;
otherwise it clears the local cookie/client state only. The owner's actual
push-token value was not captured, so that branch is not asserted as directly
observed. The owner-confirmed logged-out UI is retained as acceptance evidence,
but is **not substituted for server-token revocation**.

Private, sanitized evidence:

`.local/staging-mvp/account-login-logout-checkpoint.json`

Outbox count/metadata are unchanged: no new verification/reset message, zero
active/uncertain records, zero jobs, zero failed jobs and zero reset rows.
Selected send approval and active evidence context remain absent; general mail
remains log-only. No account/token mutation, manual revocation, repeated journey,
code change, worker activation or email send was performed by the agent.

### Remaining gate requirements
Under the existing rubric (`agendaally-email-system-audit.md`, sections 24–25),
C1 still requires resolving/proving server-side logout revocation and the bounded live reset/password
change with old/new-password behavior and reused negative security evidence.
O4 still requires both account journeys plus the specified selected
supervision/recovery/health and in-app reminder/dedup acceptance.

Verification receipt and activation are complete; the two full gates are not.
Any reset or operational activation requires separate explicit authority.

## Checks and rubric
Isolated native account-email tests passed: **18 tests, 151 assertions**, including exact-selection cancellation, complete rollback on an attempted job, unrelated-row/job preservation, newer-reset-token preservation and code/email/access-token-free verification-response evidence. Tests used captured mail only; no network send.

The normal Laravel workflow was restarted for the evidence hooks and its public countries endpoint returned HTTP 200. This is backend readiness, not proof that the stopped Customer preview or live verification journey is ready. A post-response evidence write failure is visibly flagged without converting an already committed verification success into an apparent account failure.

Live verification and owner-confirmed UI login/logout are complete; login is
corroborated server-side. Server-side logout revocation, reset/password-change
behavior and the remaining selected operational acceptance are incomplete.

**C1=0.5; O4=0.5; 16/20=80% unchanged.**

## Subsequent owner-authorized bounded logout repair

The owner additionally reported that logout required a manual browser refresh.
The subsequently authorized compatible fix always invokes authoritative logout,
separates optional push cleanup, publishes shared signed-out state, clears auth
caches and revalidates the route automatically.

The isolated native checks and browser evidence are documented in
[`customer-logout-repair.md`](customer-logout-repair.md). They prove the repaired
behavior with synthetic sessions, including revoked-token denial and
sibling/unrelated-session preservation, without repeating the owner's completed
acceptance or issuing/sending any email/code.

The normal verification timestamp, clean selected queue state and all 24
retained financial/booking table fingerprints remain preserved. Earlier token
records remain untouched; the historical server-revocation gap is not
retroactively certified. Old-token cleanup and live reset require separate
authority. **C1=0.5; O4=0.5; 16/20=80% remain unchanged.**
