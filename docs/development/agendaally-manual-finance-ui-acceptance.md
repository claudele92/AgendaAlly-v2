# Bounded native Refund and Vendor Payout UI acceptance

Date: 2026-10-06. **Result: passed within the isolated scope below. Production remains NO-GO.**

## Authority and isolation

The native Finance, Customer and Vendor pages were exercised in one continuing browser campaign. All financial browser requests were fulfilled by actual native controller/service results through the test-owned adapter at `127.0.0.1:3137`. The adapter remained alive across role changes and invoked the CLI-only fixture; it was not attached to the normal Laravel HTTP application.

- Source: `scripts/development/manual-finance-browser-fixture.php`.
- Fresh disposable database: `.local/manual-finance/browser-ui-acceptance.sqlite`; the interrupted `browser-disposable.sqlite` and historical STOP proof were not reused or overwritten.
- Test-only adapter: `.local/manual-finance/browser-fixture-adapter.cjs`. Its one-shot response-loss hook commits one successful `action` **or `create`**, then destroys the acknowledgement. Errors do not consume the hook. Keep this private loopback adapter out of production.
- Synthetic Finance/Customer/Vendor identity and local permission fixtures only. This is **not full Sanctum, normal-account, native MySQL, external execution or production acceptance**.
- No normal-data mutation/grant assignment, real money, provider call, email, accepted-account repetition, backfill or activation.

The fresh fixture uses a 50% native cancellation fee on 10,000 collected units, producing a fixed 5,000-unit Refund quote. Payouts use the recorded 9,000-unit matured Vendor payable. Five independent original booking sources were funded in disposable storage: initial Refund on allocation 1; initial Payout on 2; fresh Customer source 3; untouched control source 4; fresh Vendor source 5.

At campaign end the two existing native preview workflows were restored to their
previous stopped state. The durable test-owned loopback adapter remains separate;
no normal backend or worker was started.

## Observed browser journeys and native effects

| Journey | Actual UI observations | Retained native effects |
|---|---|---|
| Finance Refund | Detail opened; approval warning explicitly states money has not moved; approved-not-refunded status; claim responsibility warning and operator 3 visible | Approval leaves RESERVED; claim leaves UNKNOWN; neither adds ledger/evidence/completion |
| Finance lost completion acknowledgement | Filled receipt/time/attestation; intentionally lost response; unresolved exact-command warning; closed/reopened native saved-completion action; rapid double-click replay; modal closes and saved intent clears | One completed Refund, one completion command/event/evidence, 5,000-unit principal effect and 500-unit commission reversal; exact replay changes **no retained table data** |
| Finance Payout review | Approved then claimed; sent to review; “DO NOT PAY AGAIN — RECONCILIATION REQUIRED”; only reconciliation remains available | Allocation 2 remains UNKNOWN/REQUIRES_REVIEW with no settlement, completion or external receipt |
| Customer Refund | Completed allocation 1 receipt/status visible, no cancel/new eligibility; selected source 3 at fixed 50.00 USD; lost create acknowledgement; saved request survives same-tab reload; exact native saved replay; Requested then Cancelled; source 3 becomes eligible again | Exactly one created workflow/request event/RESERVED operation; replay adds nothing; cancellation releases that operation without new financial effects |
| Vendor Payout request | Existing source 2 under review/not paid warning; selected fresh source 5 at fixed 90.00 USD; lost create acknowledgement; same-tab reload and exact saved replay; Requested/not paid status | Exactly one created workflow/request event/hold; replay adds nothing; source 5 disappears from fresh eligible choices |
| Vendor cross-role status | Finance approves source 5; Vendor refresh shows approved—not yet paid with no receipt; Finance claims and records synthetic evidence-backed completion; Vendor refresh shows completed with the exact reference and no cancel/second source eligibility | Approval adds no settlement; claim is not execution; completion records one 9,000-unit Vendor settlement and one evidence record; source 2 still remains under review without settlement |

The source amounts are fixed server authority, not arbitrary form inputs or Wallet balances. The Customer UI shows the 50.00 USD eligible amount, not the 100.00 USD gross/fee breakdown; the latter is supported by the retained native policy snapshot and original accounting effects.

Customer and Vendor projections contain their own kind/beneficiary records, not Finance reasons or private evidence. The browser trace and database effects are separate evidence; neither replaces the other.

## Small correction

The native API returns numeric allocation IDs while the Customer HTML select emits text. The Customer selection comparison and initial value now normalize to `String(allocation_id)`, avoiding a valid changed selection being treated as missing. The completed Customer browser interaction explicitly selected source 3, rather than only exercising the default option.

The earlier Ant Design Modal/table corrections were preserved and exercised successfully; no finance workflow semantics or schema were changed in this continuation.

## Incremental evidence

Private sanitized receipts and immutable per-transition snapshots are retained under `.local/manual-finance/ui-acceptance/`:

- `baseline.json`.
- `finance-approved.json`, `finance-claimed.json`, `finance-completion-lost.json`, `finance-completion-replayed.json`, `finance-payout-review.json`.
- `completion-lost-evidence.json`, `completion-reopened-evidence.json`, `completion-replayed-evidence.json`, `finance-ui-acceptance-report.json`.
- `customer-created-lost.json`, `customer-created-replayed.json`, `customer-cancelled.json`, corresponding create evidence receipts and `customer-acceptance-report.json`.
- `vendor-created-lost.json`, `vendor-created-replayed.json`, `vendor-finance-approved.json`, `vendor-finance-claimed.json`, `vendor-finance-completed.json`, `vendor-final.json`, corresponding evidence receipts and `vendor-status-report.json`.

Reports retain actual request bodies/statuses, response-loss events separately from HTTP success, saved browser payload identity and screenshots. Screenshot IDs are retained incrementally; the browser tool did not provide image-file export. Representative platform-held screenshots:

| Screenshot | What it proves visually |
|---|---|
| `qu9prq` | Finance approval-not-money-movement warning |
| `py4dwa` | Lost completion response leaves the exact saved-command warning |
| `hr7jbi` | Reopened saved completion action, before replay |
| `k6tmpq` | Post-replay Finance queue and closed modal |
| `cd5eav` | Reconciliation-required/do-not-pay-again warning |
| `cfy1qb` | Completed Refund alongside review Payout |
| `ybzvg3` | Customer source 3 selected at fixed amount |
| `rvj1p8`, `eicssq` | Customer lost acknowledgement and persisted saved request after reload |
| `36v61m` | Customer cancellation and separate completed receipt |
| `lun3nn` | Vendor saved request after response loss/reload, Requested/not paid |
| `4q777a` | Vendor approval—not yet paid alongside the review warning |
| `qb4gh0`, `3ye40r` | Finance source-5 claim and acknowledged completion |
| `5gsmg2` | Vendor completed receipt alongside the still-unpaid review Payout |

The saved receipts hold additional screenshot IDs, including Vendor request, retry, approval and completion states. Old screenshots from the interrupted campaign are not acceptance evidence for this run.

## Verification and preservation

Read-only immutable checkpoint capture:

```sh
php scripts/development/manual-finance-ui-snapshot.php <unique-phase-name>
```

The capture script only opens the explicitly disposable SQLite file, with `PRAGMA query_only=ON`, and refuses to overwrite checkpoints.

Retained-effect and exact saved-command verification:

```sh
node scripts/development/verify-manual-finance-ui-evidence.mjs
node --test .migration-backup/admin/src/helpers/manual-finance.test.mjs
```

This compares entire retained replay snapshots, not just counts; checks approval/claim non-movement, fixed policy principal, cancellation release, review hold and exactly one Vendor settlement; and checks the browser's original/replayed command bodies. It fails if required evidence is absent. It is not an unattended browser test or production concurrency certification.

Normal-development preservation was checked separately before and after the campaign using the original exact fingerprint recipe:

```sh
php scripts/development/manual-finance-preservation.php ui-acceptance-before
php scripts/development/manual-finance-preservation.php ui-acceptance-after
```

All 207 original tables and original schema definitions remain preserved. Only the earlier approved append-only definitions/templates/migration rows are allowed. Seven normal manual-finance tables remain empty, jobs and failed_jobs remain zero, and unexpected delta is empty. The evidence filename argument is restricted to a local basename and retains the existing immutable-output rule.

## Explicit remaining qualifications

- Normal backend was deliberately off. Unrelated server-layout/bootstrap GETs logged connection/JSON errors, and a synthetic local address selection was needed to dismiss discovery UI. Financial interactions used the completed adapter responses; this is not a claim that the whole normal preview is healthy.
- An initial Admin translation fixture/matcher was corrected before financial mutation; an early unhandled read-only capabilities request hit the stopped local backend. A redundant adapter launch hit an already-running listener. Neither incident created an escaped write.
- The Finance completion modal initially retains its submitted row's approved state while showing the unresolved command; the refreshed queue and reopened saved action show persisted completion. The report distinguishes these actual UI moments.
- The new Vendor payout approval's HTTP status was not captured because its listener was attached later. Approval is supported by the actual Finance/Vendor UI, cleared saved intent and persisted native actor/event/state; no HTTP status is invented for that specific step. Claim/completion statuses were captured as 200.
- No private file upload/download browser journey, complete deployed authentication path, recipient delivery, operational receipt custody, scanner certification or broad MySQL stress qualification is claimed.
- The separate [private receipt browser acceptance](agendaally-private-receipt-ui-acceptance.md) now covers synthetic uploads, signed downloads, expiry, revoked grants and role restrictions in its own fresh fixture. It does not retroactively extend this campaign's authority or production claims.
- Existing production build/type-check blockers and readiness gates remain unchanged. No new canonical gate points are awarded; the retained 16/20 baseline is not recertified.

See the [consolidated implementation report](agendaally-manual-finance-implementation.md) for the original financial constraints, repaired policy authority and production gates.

## Required completion validation — authorized fixture repair

The configured `bash scripts/verify-original-hardening.sh` command failed after
the bounded acceptance checks passed. Its full-suite result was 1,157 tests,
7,151 assertions, 72 errors and one failure:

- 72 `ManualFinanceNativeMySqlTest` cases could not connect to the stopped
  disposable MySQL listener on `127.0.0.1:33308`.
- `PaymentConfirmationReplayTest::test_finalized_replay_requires_retained_canonical_effects`
  expected the canonical-effect guard but supplied `synthetic-receipt`, while the
  current funding fixture retains `synthetic-receipt-1`; it therefore reached the
  different-evidence guard first.

The owner then explicitly approved repairing validation setup and test fixtures.
The existing `native-acceptance-mysql` workflow was started with its owned
`--no-defaults` datadir and loopback-only port. The MySQL class now shares the
abstract `ManualFinancialWorkflowFixture` rather than inheriting the entire
SQLite test class: all 65 SQLite cases still run once on SQLite, and all seven
purpose-built native cases still run on MySQL. Shared setup/helper method bodies
and monetary assertions were retained, including exact policy rollback controls.
No test was excluded from its intended engine and the required command is
unchanged.

The canonical-effect replay test now submits `synthetic-receipt-1`, matching
the fixture's retained original receipt, so it reaches the original unchanged
canonical-effect assertion. No payment/application behavior changed.

Focused validation passed **83 tests / 383 assertions**: all SQLite manual
workflow cases, seven native MySQL cases and the replay tests. Exact UI-effect
checks and the separate `ui-validation-repair-preservation.json` check also
passed again. Completion is accepted only with a successful recorded result
from the full required command, never from these focused counts alone.
The initial failed run is retained as historical evidence.

For full hardening validation, keep the owned `native-acceptance-mysql`
workflow running. Other opt-in native suites retain their existing explicit
configuration/skip boundaries; no production database is used.

The next full run removed the receipt failure and reported six native schema
setup errors (DDL deadlock/disappearing tables) rather than financial assertion
failures. Its shared hard-coded disposable schema was unsafe for overlapping
runs. Each native case now creates a new
`agendaally_manual_finance_disposable_<pid>_<random>` schema, refuses reuse,
shares only that case's configuration with its race workers, and drops only its
guarded owned schema in teardown. The historical shared schema is not cleared.
Two simultaneous focused native runs both passed (one test / five assertions
each), confirming independent setup/cleanup. The task's final required-command
receipt is authoritative for full-suite completion; prior failed logs remain
retained and focused checks do not replace that requirement.
