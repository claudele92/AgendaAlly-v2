# Approved Service demo source package — acceptance report

## Authority and scope

The owner explicitly approved Service-only source packaging against approval
JSON SHA256
`e3da6988652481194b281e231e1da8ce8f1608c4c26b3b13035b52cd9ebe3a31`.
The decision is recorded in
[the frozen owner record](local-synthetic-service-photo-owner-decision.json).
Its SHA256 is
`ce64984255b623a06ad32502aafd8b92c61be653edfc07eda04324f6e4ce2694`.

The original review and approval-proposal files were **not rewritten**. Their
historical pending/review-only statuses are not current execution authority.
The new explicit owner decision authorizes the selected Service bytes and
provenance only. It does not approve Product photos or runtime installation.
No live license fetch or independent rights certification was performed.
The owner's Pexels conditions, attribution statement and use restrictions are
retained in the selected provenance.

## Resulting source package

Approved sanitized repository:
`.local/agendaally-clean-repository`.

Repository-relative package:
`.migration-backup/backend/resources/demo-assets`.

The complete preflight checked all four frozen review/evidence hashes, the
approved proposal and owner record, all 15 inputs and every destination before
creating the package. The package contains exactly:

- `services/`: the 15 approved JPEGs, unchanged exact bytes, sizes, detected
  `image/jpeg` MIME types and SHA256 values.
- `provenance.json`: only the selected Service identities, sources, recorded
  license references, approved conditions and frozen Service/Gallery relations.
  No historical owner IDs, unused historical photo records or Product images.

The public namespace remains `/storage/portable-demo/services/`. This is a
**source package**, not a runtime/public storage installation. The physical
future public destination remains separately approval-gated.

| Receipt | SHA256 |
|---|---|
| Package canonical file manifest | `8b0c4ff93d5f02df6183319d4656fe7afbf1c200b8d25ec5758df4b27d22d37e` |
| `provenance.json` | `fae80427b5626b65295e7c5dddb8eee9b405b299b6ffa61395d2894ab52ee2c6` |

The package hash is SHA256 of UTF-8, two-space JSON with a trailing newline:
the ordered array of `{path, bytes, sha256}` for the 15 Service files in approved
inventory order, followed by `provenance.json`. Paths are repository-relative.
It is not a tar/archive hash. The
[machine-readable acceptance receipt](local-synthetic-service-photo-packaging-receipt.json)
lists every file hash and byte count.

The sanitized source snapshot is intentionally ignored by the historical parent
repository. No parent ignore rules were weakened, no original Git history was
reconstructed, and no GitHub repository or push was performed. This report
records source packaging at the approved location; it is not publication or
full-application installation approval.

## Fresh-checkout evidence

Acceptance created a new local Git seed with an explicit allowlist of:

- The 16 approved source-package files.
- The frozen approval proposal and owner decision.
- Three standalone verification/guard files.

It then made an independent local clone with `--no-local --no-hardlinks`,
checked its independent Git root, exact 21-file tracked inventory, clean state
and matching committed tree, and deleted the seed **before verification**.
There were no shared object alternates and no `attached_assets` directory in
the checkout. Only 15 raster files were present.

This is a **bounded asset-gate checkout**, not a full-app checkout, production
rehearsal or certification of the existing sanitized snapshot's Git history.
It proves the approved source package is self-contained and verifiable without
the original workspace photos; it does not claim that the full application
can initialize from this 21-file checkout.

The verifier ran under Node filesystem permissions:

- Reads of an actual original workspace Service JPEG were denied and asserted.
- Verification filesystem writes were denied and asserted.
- Fetch, socket and arbitrary external-process probes were blocked by the
  offline guard and asserted.
- The sole permitted child process was `file`, with exact MIME-detection
  arguments restricted to paths inside the checkout.

This is explicit verifier isolation, **not a kernel network namespace or a
general adversarial-code sandbox**. The verifier uses built-in Node modules,
local Git and the installed MIME detector; no dependency installation, package
download or network asset fetch is involved.

The fresh verifier confirmed every approved JPEG, exact provenance and all
51 Service / 51 Service-Gallery relationships. The original frozen review's
51 Service, 13 Product, 14 Stock and 64 Gallery relationships remain unchanged
in the hash-bound decision evidence. The 13 Product Gallery relationships and
all five Product media dependencies remain unresolved, not falsely certified
as available.

Every proposed NULL field remains unchanged, including Shop, Specialist/profile,
Brand, CMS/blog and country images. This is approved NULL preservation only,
**not** acceptance of photo-complete or visual parity.

## Failure evidence

The acceptance receipt records 28 passing rejection cases:

- Last-input absence, same-size corruption and size drift reject the complete
  selection without creating the destination or copying earlier valid photos.
- Symlink input files/ancestors, destination ancestors/root aliases/dangling
  links, non-regular inputs and non-directory destination ancestors reject.
- Empty occupied directories, occupied regular files and identical occupied
  approved packages reject.
- Edited/unapproved selections, private/runtime paths, URL inputs and path
  escapes cannot pass the frozen approval hash.
- MIME drift and raw traversal/absolute/backslash/empty-component paths reject.
- Fresh-checkout missing/corrupt/symlinked photos, symlinked/changed provenance,
  extra images, Product directories and private/runtime entries reject.

The shared complete-selection preflight was tested using disposable fixtures
whose JPEGs came **only from the verified checkout**. No original workspace
photos or network were needed by those negative cases. A separate unit test
invokes the actual packaging command against the identical occupied real source
package and verifies that it fails without changing any package file.

The real source package and accepted checkout were unchanged by failure tests.
All temporary local Git/test fixtures were removed after the campaign.

## Reproduction

Requires the already installed Node 24 permission support, Git and `file`.
There is no runtime download fallback.

```sh
# Before first approved packaging only; an occupied package always rejects:
node scripts/database/service-demo-assets.mjs --preflight
node scripts/database/service-demo-assets.mjs --package

# After packaging, read-only verification and bounded ephemeral acceptance:
node scripts/database/service-demo-assets.mjs --verify
node scripts/database/service-demo-acceptance.mjs --check
node --test scripts/database/service-demo-assets.test.mjs \
  scripts/database/demo-photo-approval.test.mjs \
  scripts/database/demo-asset-review.test.mjs
node scripts/database/check-synthetic-reference-review.mjs --check
```

`--package` is deliberately **not idempotent**: any occupied package destination,
even identical, fails closed. Never delete/overwrite an occupied directory merely
to make it succeed. Verification checks exact package contents without copying.
The acceptance command emits a receipt to stdout, writes only disposable test
directories, performs no remote Git actions, and leaves the approved source
package unchanged.

## Remaining gates

- All five Product dependencies remain blocked pending a separate frozen
  local-byte/licensing proposal. No Product image was fetched, substituted,
  generated, mapped from unrelated media or packaged.
- Runtime/public installation requires a separately frozen physical path for a
  **new isolated** portable-demo storage tree. It is not performed here.
- No database initialization or migration, administrator provisioning,
  credential access, application/recovery keys or escrow, provider/SMTP
  activation, workers, financial operations, writes to existing runtime/public
  storage, GitHub creation/push, VPS actions, Stories changes or production
  activation were performed.

The **Service source-packaging asset gate** is accepted by this evidence.
The Product-photo gate and runtime-installation gate remain blocked.
