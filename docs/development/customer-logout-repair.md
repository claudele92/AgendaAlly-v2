# Bounded Customer logout repair

## Authority and scope

The owner reported that normal Customer logout required a manual browser
refresh and authorized the smallest compatible repair: always call
authoritative server logout, regardless of push registration; update shared
Customer UI state without manual refresh; keep push cleanup separate from
authentication/session revocation.

No owner login/logout or completed verification acceptance was repeated.
No email was sent, no verification/reset challenge was issued, and no reset
or retrospective deletion of existing account tokens was authorized.

## Behavior

- The actual Customer hook awaits server logout with optional push metadata.
  It keeps client credentials/state on server failure and reports a retry error.
- Confirmed logout removes the auth cookie, publishes null shared user state,
  cancels queries, clears authenticated caches, and navigates/revalidates
  automatically. All hook consumers subscribe to the shared user change.
- Optional Firebase cleanup does not delay the logged-out UI.
- Native logout revokes only the current persistent access token. Exceptions
  or cancelled deletion cannot be reported as successful revocation.
- A matching web session is invalidated; another account's ambient web session
  is preserved. Sibling and unrelated access tokens are not broadly deleted.
- Optional exact-match push cleanup runs after revocation. Its failure cannot
  keep authentication active or expose push credentials in a warning.

## Verification

The targeted native logout suite and existing isolated account-reset regression
suite passed together: **28 tests, 174 assertions**. The inherited PHPUnit
deprecation remains; there were no test failures or PHP warnings.
Customer TypeScript checking passed.

Native fixtures cover push/no-push/legacy metadata, real Sanctum authentication
and protected-route denial, repetition, sibling/other-account preservation,
optional push failure, thrown/cancelled revocation, matching web-session
invalidation and unrelated ambient-session preservation. Fixtures never use
the normal account/database.

The browser pass used the **actual native hook, store, cookies, React Query and
auth fetcher**, with synthetic push generation and a Next router adapter.
Its HTTP fixture calls the **actual native logout controller and Sanctum**
against a separate synthetic-only SQLite database. The protected fixture route
uses real `auth:sanctum`, not a mocked authentication result.

Observed for both push and no-push:

- One logout request for the first click; expected push-metadata presence.
- Both independent consumers become signed-out/anonymous after async logout,
  with token cookie absent, without a browser navigation or load event.
- Revoked credential receives **401**; sibling and other-account credentials
  remain **200**. Repetition retains these results without error.
- Simulated HTTP 503 retains auth state/cookie and displays the retry error.
  Removing the failure marker and retrying succeeds.
- Correctly nested Zustand persisted state has `state.user === null` on the
  existing signed-out page after retry. An earlier assertion incorrectly
  compared the whole persisted wrapper to literal null; that was a test
  assertion error, not a discovered application failure.

The initial browser prerequisite was checked before the synthetic session
request completed. The continuation waited for the request/state transition;
it did not repeat any previously passing logout journey. The unexplained
earlier resource 404 is not attributed to the native session endpoint.

Private sanitized report and screenshots:
`.local/testing/customer-logout-browser-report.md`.
This proves isolated repair behavior, **not** another acceptance run in the
owner's actual browser or retrospective revocation of its previous tokens.

## Preserved acceptance and data

Read-only normal-runtime evidence:
`.local/staging-mvp/customer-logout-repair-preservation.json`.

- Verification remains **2026-10-06 01:24:55 UTC**, marker cleared.
- Existing verification/login-era token records **74 and 75 remain**. They
  were not manually deleted to make historical acceptance appear successful.
- Outbox remains five retained records, zero active/uncertain deliveries,
  zero jobs/failed jobs/reset rows, and no selected send approval.
- All **24** retained booking/payment/ledger/transaction/Wallet table
  fingerprints match the earlier single-send checkpoint using the frozen row
  serialization contract. This comparison is explicitly to that earlier
  checkpoint, not an invented repair-start snapshot.
- No unrelated account, role, booking, payment, Wallet or financial behavior
  was changed.

The old logout gap remains historical evidence. Any cleanup of those earlier
tokens and the live reset/password-change journey require separate authority.
The bounded repair does not complete C1/O4:
**C1=0.5; O4=0.5; 16/20=80% unchanged.**
