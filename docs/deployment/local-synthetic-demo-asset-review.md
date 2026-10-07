# Portable demo photographs — source-derived owner review

**DRAFT / REVIEW ONLY. No photograph is approved for packaging by this document.**
No assets were copied into a public installation, fetched from the network or
borrowed from private uploads. No database, identity, key, provider or Stories
fixture was initialized or modified.

This is the narrowed **review-package** deliverable, not completion of an asset
installation. The category name “APPROVED PORTABLE DEMO” in the baseline is a
proposed content classification, not an approval receipt.

## Frozen evidence

- [Existing baseline and exact row proposal](local-synthetic-portable-baseline.json)
- [Existing first-install differences and authority](local-synthetic-reference-review.md)
- [Image inventory, MIME/hash evidence and exact relationships](local-synthetic-demo-asset-review.json)

The image inventory binds itself to the baseline SHA256 and the historical
Service metadata SHA256. Each selected Service image has its own byte count,
detected MIME, SHA256, source page and recorded license link. Product entries
preserve the remote URL, source hash/line and unverified source photo comments,
with **NULL bytes, MIME and hashes** until actual approved local bytes exist.
The inventory lists source URL occurrences separately from proposed NULL row
fields; that index is **not** an approved mapping of those remote photos to rows.

The existing baseline files have not been rewritten or silently approved.
Checks read the sanitized source snapshot; they do not certify its independent
Git history or prove availability in a new installation.

## Findings

| Family | Review finding | Decision still needed |
|---|---|---|
| Service photographs | 15 workspace JPEGs match frozen bytes/hashes and detected `image/jpeg`; 51 Service rows and 51 Gallery links | Select acceptable photographs and approve public redistribution plus source/license metadata inclusion |
| Product photographs | Five remote Unsplash URLs; no matching URL-token or proposed filename among 107 raster files in the three reviewed static public trees | Supply or authorize reviewed local bytes, original source/license evidence, and inclusion |
| Product consumers | Five proposed paths feed 13 Products, 14 Stocks (including the variant), and 13 Gallery links | Preserve all three consumer families, not just Gallery paths |
| Shop photos | Nine Shops, 18 proposed NULL logo/background fields | Accept the visible source-parity difference or select reviewed replacements |
| Profile photos | 29 non-login profiles have proposed NULL `img`; 14 are Specialist profiles | Accept NULLs or select reviewed replacements, keeping legitimate private media excluded |
| Brand | One proposed NULL image for Ela De Pure | Accept NULL or approve an appropriate locally licensed image; no inferred brand endorsement |
| CMS | Four pages have eight proposed NULL image/background fields; three blogs have NULL images | Select acceptable public content photo parity; source defaults are not publication approval |
| Country flags | Four proposed NULL images | Accept NULLs or approve local flag bytes; geography must not be removed to avoid media decisions |

The Product filename review searched only `.migration-backup/web/public`,
`.migration-backup/admin/public` and `.migration-backup/backend/public` in the
sanitized snapshot, excluding storage, uploads and hidden entries. It is a
bounded filename search, **not** a claim that no equivalent image exists anywhere
on the host. Neither private storage nor runtime/cache/export snapshots were
searched or used to satisfy missing media.

### Service selection

| Selected filename (`.jpg`) | Proposed Service IDs |
|---|---|
| beard-trim | 5, 11, 17, 23, 29, 35, 41 |
| hair-coloring | 2, 8, 14, 20, 26, 32, 38 |
| music-lessons | 46 |
| custom-tattoo | 48 |
| tattoo-touch-up | 49 |
| body-piercing | 51 |
| ear-piercing | 50 |
| haircut-africa-first | 1, 7, 13, 19, 25, 31, 37 |
| bridal-makeup-africa-first | 6, 12, 18, 24, 30, 36, 42 |
| manicure-africa-first | 3, 9, 15, 21, 27, 33, 39 |
| massage-africa-first | 4, 10, 16, 22, 28, 34, 40 |
| academic-tutoring-africa-first | 43 |
| language-lessons-africa-first | 45 |
| computer-skills-africa-first | 47 |
| exam-preparation-africa-first | 44 |

Only these 15 of the 23 historical files are candidates. Historical owner IDs
must not be reused as native Gallery ownership; the baseline's reviewed
Service/Shop/title mapping is the authority proposed for review. The JSON retains
that relationship detail and Product/Stock/Gallery consumers.

### Product source claims, not license proof

| Proposed filename | Product | Source comment claims |
|---|---|---|
| product-1.jpg | Moroccan Argan Oil Hair Serum | Unsplash `eLm-P_CEdw0`, Ela De Pure |
| product-2.jpg | Moisturizing Shampoo | Unsplash `K1k8M_bb2bM`, Ela De Pure |
| product-3.jpg | Moisturizing Conditioner | Unsplash `5eoYsqzmDW4`, Ela De Pure |
| product-4.jpg | Vitamin C Brightening Serum | Unsplash `rqK_baq_XgI`, Ela De Pure |
| product-5.jpg | Wide-Tooth Detangling Comb | Unsplash `kkQdRnasB98`, Apothecary 87 |

Pexels/Unsplash links and “free tier” source comments do not independently prove
the rights for these exact bytes or every depicted person, mark or product.
No live license/source verification was performed in this source-only review.
Before inclusion, retain reviewed source provenance, the applicable license
evidence and any required attribution/restrictions. Product names are demo
labels, not authentication of a pictured product's identity or brand rights.

## Owner decision record required before execution

The approval must identify the reviewed baseline and inventory hashes, then state:

1. Which Service files are accepted, and whether their source/license metadata
   may be distributed with the sanitized source.
2. The approved Product files, exact bytes/MIME/SHA256, provenance and license
   evidence (or an explicitly revised Product-photo proposal).
3. Whether each proposed NULL family above is acceptable, or the exact reviewed
   replacement relationships. Do not call accepted NULLs photo-complete parity.
4. The approved repository inclusion location and fresh public demo namespace.
   For the present baseline, public Service and Product URLs are
   `/storage/portable-demo/services/…` and `/storage/portable-demo/products/…`;
   this is not authority to write into an existing storage tree.

No “approve all” value, installed file or inferred approval is generated by these
checks. Changes to selection, licensing evidence or relationships require a new
frozen review; they must not silently reuse this inventory.

## Later packaging contract — deliberately not implemented here

After explicit owner authority, package **only** approved public demo bytes and
their approved provenance. Reject symlinks in inputs and destination ancestors,
non-regular files, private/runtime uploads, path escapes, missing or mismatched
hashes/size/MIME, unapproved assets and occupied destinations (even identical
ones). Never overwrite, fetch at bootstrap or substitute arbitrary local media.
Preflight all selected entries before making any approved copy.

Fresh-checkout acceptance must use a separately sanitized checkout containing
the approved package, without the original workspace photos or network access.
Verify all exact bytes/MIMEs/hashes and all Service, Product, Stock and Gallery
relationships. Prove missing/corrupted media, symlink and occupied-destination
failures without changing a database. Record the package and checkout receipts.
This acceptance **has not run**: the current sanitized snapshot lacks the
Service-photo folder and the Product bytes remain unresolved.

Database initialization, administrator recovery, identities, key custody and
Stories fixtures remain separate work.

## Read-only reproduction

From the workspace containing the already frozen sanitized source and selected
Service photographs:

```sh
node scripts/database/demo-asset-review.mjs --check
node --test scripts/database/demo-asset-review.test.mjs
node scripts/database/check-synthetic-reference-review.mjs --check
```

`--print` emits a newly derived review to stdout for comparison; `--check` checks
the committed inventory without writing it. Other modes are rejected. Missing
source, symlinks, hash/size/MIME drift and edited review evidence fail explicitly.
The tests cover the read-only source boundary and photo-evidence validation,
not an unimplemented installer. Do not generate a replacement review merely to
hide a failed frozen-hash check.
