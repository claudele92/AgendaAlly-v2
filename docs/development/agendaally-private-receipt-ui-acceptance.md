# Private Refund and Vendor Payout receipt acceptance

Date: 2026-10-06. **Passed in the isolated scope below. Production remains NO-GO.**

This is the separate private-file journey excluded from the earlier
[bounded financial UI acceptance](agendaally-manual-finance-ui-acceptance.md).
No application authorization, finance state-machine, database schema or storage
implementation was changed.

## Isolation and authority

- Fresh owned fixture:
  `.local/manual-finance/http-identity-256a591f53d48feb/native.sqlite`.
  It was not copied from or attached to normal data.
- Test entry point: `scripts/development/manual-finance-receipt-fixture.php`.
  The native API routes, Sanctum token guard, FinanceScope, controllers,
  PrivateEvidence, WorkflowService and local storage driver supplied the actual
  finance/upload/link/download results. The fixture bypasses normal application
  bootstrap and uses generated identities, permissions and signing authority.
- The private HTTP process bound only to `127.0.0.1:3138`. The existing native
  Admin and Customer previews temporarily proxied their same-origin API requests
  to this process. No financial response was mocked in the successful campaign.
- Unrelated layout/navigation/profile/catalog bootstrap fixtures were allowed.
  Customer acceptance needed a narrow read-only countries lookup and a generic
  synthetic location. Unrelated public bootstrap/HMR errors remained; this does
  not certify the whole normal application.
- PDFs and images were generated locally by
  `scripts/development/manual-finance-synthetic-receipts.php`. They explicitly
  say they are synthetic and that no money moved. No real documents, normal
  accounts, real money, provider call, email send, production access, backfill or
  activation was used. Automatically started normal backend/business operations
  were stopped before the browser campaign.

The fresh fixture contains a claimed 10,000-unit Refund and a claimed
9,000-unit Vendor Payout, both USD with scale 2. Later completion records and
internal accounting effects exist **only in this disposable database**. They
do not represent actual external money movement.

## Uploads, retained hashes and completion binding

| Synthetic file | Bytes | SHA-256 | Native UI result |
|---|---:|---|---|
| PDF | 614 | `881d58fc6d63352596ab5be572479afb0b334ed6a6e9315c3294dfb44a026af4` | Refund upload 200 |
| PNG | 658 | `33e79b8e63286ab2bc40a0a3ff81006ddfa6e30752e1991c7ad70178880cf0fd` | Refund upload 200 |
| JPEG | 4,316 | `52a409062aadd6b2d308e8e7b32e8ae7aa24228ed4b1a3d2d597201cf936081e` | Payout upload 200 |

All selections used the native completion dialog's file input, with an
“Evidence attached” confirmation. Native attachment rows retain the intended
workflow, Finance actor 3, MIME, byte count, generated private path and exact
digest. Retained files independently match these rows and the synthetic
manifest. Cross-workflow link generation and a separately signed wrong-binding
download both returned 404.

Reopening the native completion dialog clears its attachment choice and provides
no existing-attachment selector. Consequently, the same safe PNG and JPEG were
reattached once each in the two completion dialogs. No old attachment was
changed or removed. Both native submissions returned 200 / COMPLETED with fixed
workflow authority and synthetic reference/time/attestation.

Final retained rows: **5 attachments, 2 completion-evidence rows, 14
evidence-access audit rows**. Each completion evidence row binds its new
attachment ID and `document_sha256` to the retained PNG/JPEG digest. The original
three uploads remain unchanged.

These last two uploads/completions occurred **after evidence-view revocation**.
The complete/upload grants remained, but the evidence-view grants were not
restored. Neither new file was linked, viewed or downloaded; access rows stayed
at 14. Completion authority is not private-receipt view authority.

## Signed downloads and access restrictions

The actual native “View evidence” controls were clicked for the original PDF,
PNG and JPEG. All three link requests returned 200, followed by download 200 and
real browser download events. Saved browser bytes match the size and SHA-256 in
the table above.

All three responses supplied:

```text
Content-Type: application/pdf | image/png | image/jpeg
Content-Disposition: attachment; filename="receipt.pdf" | "receipt.png" | "receipt.jpg"
X-Content-Type-Options: nosniff
Content-Security-Policy: sandbox; default-src 'none'
Cache-Control: no-store, private
Referrer-Policy: no-referrer
```

| Check | Observed native result |
|---|---|
| Customer and Vendor request links for either workflow | 403, no URL |
| Customer and Vendor try valid synthetic receipt uploads | 403, no attachment mutation |
| Structural Admin without grants, foreign-country actor, outsider request links | 403, no URL |
| Customer / Vendor own list and detail | 200, own kind only; no attachments, private paths/digests or evidence actions |
| Ungranted / foreign / outsider list and detail | Empty scoped list; detail 404 |
| Past-expiry URL generated with a valid native signature | 403 |
| Missing/tampered signature, altered actor, altered expiry | 403 |
| Cross-workflow attachment link / signed wrong-binding download | 404 |
| Previously issued, still-unexpired links after grant removal | 403, no receipt bytes |
| New link issuance after grant removal | 403, no URL |
| Finance detail refresh after grant removal | Financial details remain; attachment field/evidence-view action absent; zero View evidence buttons |

The two revocation-test links were issued at **18:08:25 UTC**, expired at
**18:13:25 UTC**, and were denied after revocation at **18:09:12 UTC**. This is
revocation denial inside the validity window, not expiration mistaken for grant
revocation. Native pre/post snapshots prove denial added no access rows or
financial effects.

**Important boundary:** a native signed URL is a five-minute bearer capability
for its embedded issuing actor. Download requires a valid signature and that
actor's current evidence-view grant; it does not require the viewing browser to
be signed in. Possession of a live, unrevoked URL is authority under the current
design. This campaign does not claim session-bound links or that forwarding a
valid URL would be denied. Customer/Vendor/outsider projections cannot obtain
these paths or issue these links themselves.

The native Vendor UI showed its own Payout row with no private receipt action.
The outsider Finance page showed an access warning and empty table. Customer
`/manual-refunds` ultimately showed its actual owned Completed Refund and
synthetic external reference, with no private document view/upload/hash/path
controls; its native finance GET returned 200.

Supplemental unauthenticated direct-path probes covered all three original
formats: native public-storage URLs returned 404; Vite sibling-filesystem URLs
returned 403; absent Vite storage paths returned SPA HTML 200, **not receipt
bytes**. A 200 fallback alone is not evidence of private-file exposure.

## Retained proof and screenshots

Sanitized, source-retained proof is under
[`evidence/private-receipt-acceptance/`](evidence/private-receipt-acceptance/).
It includes native attachment/evidence/access rows and relevant financial
tables, fixture/file manifests, normal-data fingerprints, chronological browser
receipts and immutable checkpoints. Runtime tokens, sessions, signed signature
values, private document bytes and raw normal rows are excluded.

Private original files, actual browser download binaries and full native
snapshots remain in the owned `.local` fixture. They are not independent
off-host custody. The browser tool supplied platform-held screenshot IDs, not
exportable screenshot files. These IDs are preserved in the reports:

| Screenshot | Evidence |
|---|---|
| `aexzji`, `yes1kh`, `n7mdws` | Accepted native PDF, PNG and JPEG uploads |
| `pfwz3y`, `lrzsep`, `tmy1o9` | Safe detail state before actual private download clicks |
| `qd1g0m`, `w2na05` | Detail after successful image-download actions |
| `xa7imp`, `qd08o4`, `29vgg8` | Expired signature and wrong-workflow denials |
| `rjkegr`, `aabq4j` | Finance detail after evidence-view revocation |
| `8eth5m`, `zzboac`, `9dvfym` | Vendor no-private-action row and outsider empty scope |
| `6jsduc`, `90y7u1` | Native reattachments for file-bound completion |
| `lh5v6m`, `hlwx61` | Completed disposable Finance rows, references, empty Actions |
| `hkol1b` | Customer's actual completed Refund without private receipt controls |

Screenshots containing an ephemeral signed capability were withheld rather than
publishing the capability. Earlier blocked screenshots/reports remain historical
and are not substituted for successful download or Customer acceptance.

## Harness corrections and qualifications

1. Intercepted multipart request forwarding omitted selected file bytes, causing
   422 validation failures. The existing frontend's native same-origin proxy
   resolved this. No API-injected attachment replaced a browser upload.
2. The isolated router initially omitted RouteServiceProvider's post-boot named
   route lookup refresh. Link generation returned 409 after writing one
   attempted-link audit row. The fixture now performs the native lookup refresh.
3. The isolated fixture also omitted FoundationServiceProvider's native request
   signature macros. An early download returned 500. Registering the framework's
   own signature-validation macros fixed this without changing application code.
4. One main-agent diagnostic link/download is retained separately from browser
   acceptance. The 14 audit rows include earlier link attempts as well as
   observed accesses; they are **not described as 14 successful downloads**.
5. The normal Customer API target caused early 500s. The isolated target and a
   narrow unrelated country lookup fixture permitted the final native Customer
   row to render. Public bootstrap errors are still explicitly qualified.

The same continuing browser tester resumed only blocked/unrun work; successful
uploads/downloads/role-denial/revocation checks were not repeated as new campaigns.
No malware scanner certification, full normal-account authentication acceptance,
production/MySQL/concurrency qualification, recipient delivery, independent
off-host custody or new readiness gate points are claimed.

## Verification and preservation

From the workspace root, for this retained owned fixture:

```sh
D="$PWD/.local/manual-finance/http-identity-256a591f53d48feb"
node scripts/development/verify-manual-finance-receipts.mjs "$D"
```

This checks actual browser download binaries/headers, native attachment and
completion bindings, observed denial/projection/revocation receipts, unchanged
audit rows, direct-path probes and exact normal-data preservation. It is not an
unattended replacement for the browser campaign.

Before/after read-only fingerprints compared **all 214 normal tables and their
exact row serialization plus original schema definitions**. The campaign
changed none. The seven normal manual-finance tables remain empty, with zero
jobs/failed jobs.

The historical 207-table baseline still reports the already-existing
`agendaally_development_environment` warning both before and after this campaign.
It was not repaired, normalized or silently exempted. The separate preservation
warning task remains open; fresh exact campaign fingerprints establish only
that this receipt work introduced no additional normal-data delta.

Temporary API target overrides are restored and the test-only HTTP process is
stopped after evidence capture. Normal backend, email, provider and business
operations remain off. The owned native MySQL listener is kept available solely
for required hardening validation.
