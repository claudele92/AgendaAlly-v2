# Native manual-finance HTTP identity acceptance

Date: 2026-10-06. **Bounded local acceptance only; production remains NO-GO.**

## Scope and authority

This supplements, rather than repeats, the accepted
[native UI campaign](agendaally-manual-finance-ui-acceptance.md).
The test uses existing authorization for isolated local native authentication
and disposable synthetic finance fixtures. No normal account, normal-data grant,
provider, email, production, financial completion or actual money is activated.
Application controllers, financial semantics, authentication and schema
definitions are unchanged.

Unlike the earlier synthetic guard adapter, this campaign sends actual HTTP
requests to a short-lived PHP server bound to an ephemeral **loopback-only** port.
It imports the original `routes/api.php`, including its `auth:sanctum` finance
routes, native authentication and IP middleware. It resolves the real Sanctum
Guard/User provider against persisted personal access tokens and stored Spatie
roles/grants. There is no injected authenticated user or mocked authentication
guard in the HTTP dispatch.

The original reviewed accounting/manual schema is provisioned in memory then
copied into a newly owned disposable SQLite database. Every HTTP request opens
that file, never the normal application database or `.env`. Disposable identities
cover Vendor owner, Customer, globally granted Finance, structural Country Admin
without financial grants, foreign Country Admin with grants, second Finance
operator, unrelated Customer and accepted country-role Finance.

File-session middleware and encrypted cookies are explicitly test-owned
transport setup. The native web guard's persisted session key is provisioned
directly for a disposable identity; there is no test-login endpoint. This proves
real cookie/session identity resolution, not the production sign-in challenge,
full provider bootstrap, or stateful cross-origin CSRF configuration.

## HTTP checks and exact effects

The campaign checks both Refund and Vendor Payout:

- Anonymous requests are 401.
- Missing explicit grants and foreign scope return an empty queue and 404 for
  inaccessible detail/actions, including a structural Country Admin.
- Customer/Vendor ownership exposes only the actor's own supported kind. Safe
  beneficiary projections omit private attachments and Finance event reasons/
  actor identity; owners cannot approve (403).
- Unrelated identities cannot enumerate eligible sources or create a workflow
  against another beneficiary's allocation (404).
- Eligible owners create actual native requests through HTTP and replay the
  exact request without changing any retained non-auth row.
- Accepted country-role Finance can read single-country sources. A mixed-country
  shop footprint denies both invited and direct-grant country-restricted Finance.
- Granted Finance can approve and claim. The second granted operator cannot
  complete another operator's claim (403). Stale workflow versions fail (409).
- Removing financial grants takes effect on an already issued Finance token:
  both completion and detail fail closed (404).
- Removing only the required completion grant still allows authorized viewing,
  but removes the completion action and denies its HTTP execution (403).
  Revoking a country-role invitation also removes queued/detail/action access
  immediately from its already-issued token.
- An actual encrypted cookie session can read Finance data. Sanctum correctly
  prioritizes its web identity over an accompanying unrelated bearer identity.
  Revoking its grants denies reading/actions; invalidating its session file
  produces 401; a surviving unrelated bearer cannot inherit the old Finance role.
- Expired tokens, revoked Finance/Customer tokens and a deleted token owner
  cannot read or act (401).

Every request is retained **before assertions**, including failed setup receipts.
Expected read-only/denied/replay requests compare entire ordered non-auth table
contents before and after. Token rows are excluded because native Sanctum
updates `last_used_at`; tokens/cookies are never included in request evidence.
Explicit fixture grant/location/revocation changes occur between request
snapshots, not as hidden exceptions to the immutability assertion.

At campaign end there are no completion evidence records or completed workflows.
The two approved/claimed originals remain UNKNOWN; the two owner-created
requests remain RESERVED. Approval and claim do not settle money. Exact table
snapshots retain ledger, allocation, operation, command, event and notification
effects. Tokens and session files are retired even when a campaign assertion
fails; the owned server is terminated and retained proof databases are not reused.

## Reproduction and evidence

```sh
bash scripts/verify-original-hardening.sh --filter ManualFinanceHttpIdentityTest
```

Implementation:

- `.migration-backup/backend/tests/Hardening/ManualFinanceHttpFixture.php`
- `.migration-backup/backend/tests/Hardening/ManualFinanceHttpIdentityTest.php`
- `scripts/development/manual-finance-identity-http.php`

Each campaign retains `baseline.json`, numbered per-request receipts,
`server.log`, and `passed.json` only on successful completion under a new
`.local/manual-finance/http-identity-<random>/` directory. These private local
receipts deliberately contain no Authorization header, cookie, session ID or
plain token. The owned native SQLite file is retained with restrictive access.
This is HTTP evidence, not new browser screenshot evidence; the earlier UI
campaign remains separate and no browser UI replay is claimed.

The first complete expanded campaign retained at
`.local/manual-finance/http-identity-fef253716679204e/` passed **71 HTTP requests,
165 assertions**. Subsequent required-command campaigns generate independent
immutable receipts; their validation result is authoritative.

### Normal-development preservation

The historical preservation command was run before identity testing:

```sh
php scripts/development/manual-finance-preservation.php http-identity-before
```

It reported a **pre-existing** `agendaally_development_environment` fingerprint
discrepancy (same row count). The original schema was unchanged, all seven
normal manual-finance tables were empty, and jobs/failed_jobs were zero. This
historical-baseline warning has not been cleared, rebaselined or called a pass.

To independently prove current-state preservation:

```sh
php scripts/development/manual-finance-identity-preservation.php <unique-phase>
```

The `identity-preservation-before.json` and `identity-preservation-after.json`
receipts compare identically: exact schema hash and all **214 current table**
row counts/serialized row-order fingerprints. No normal data/grants were
assigned or changed by this campaign.

## Qualifications

No native MySQL identity campaign, provider call, money completion, full deployed
login/bootstrap, CSRF certification, new browser UI acceptance, private receipt
upload/download campaign or production concurrency/readiness is claimed.
Private receipt access remains separate work. Existing build and production
readiness blockers remain unchanged. No readiness score is recertified.
