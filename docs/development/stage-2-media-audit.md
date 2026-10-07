# Stage 2 media audit — read-only

## Scope and result

This audit used only the owned development SQLite database at
`.migration-backup/backend/database/development/agendaally.sqlite`, opened
read-only, plus the relevant original category, shop, product, gallery, About,
and blog seeder image references. The database projection was limited to
record IDs, public display labels/category hierarchy, and media fields in
`categories`, `shops`, `products`, `brands`, `blogs`, `pages`, and `galleries`.
No environment or credential values were queried. No user email, contact, or
private data is included here.

| Owned development content | Rows / populated media fields | Unique references |
| --- | ---: | ---: |
| Categories | 76 rows; 54 local image paths; 22 null | 14 local SVGs; 0 remote URLs |
| Shops | 9 rows; 18 image fields (9 backgrounds, 9 logo slots) | 18 remote URLs |
| Products | 13 rows; 13 image fields | 5 remote URLs |
| Galleries | 13 rows; 13 remote paths | 5 remote URLs |
| Brand images | 1 populated image field | 1 remote URL |
| Blogs | 3 rows; 3 image fields | 3 remote URLs |
| About pages | 3 rows; 6 image fields (`img` and `bg_img`) | 3 remote URLs |
| **Current remote media total** | **54 field/row references** | **30 exact URLs** |

Every gallery row is a product gallery and its path exactly equals that
product's `img` value (13/13). About-page `img` and `bg_img` repeat the same
URL for each of the three pages. Product IDs 1 and 6–13 (nine synthetic
Moroccan Argan Oil Hair Serum listings across shops) reuse one image URL; their
nine gallery rows repeat it as well. The other four product URLs each map to
one product and its matching gallery. Thus the product catalog has five
distinct photo URLs for 13 product rows, not 13 distinct photos.

All 54 current remote references resolve to `images.unsplash.com`. A bounded,
three-at-a-time `HEAD` check of the 30 distinct current URLs returned **200 /
`image/jpeg` for 30/30**. The original `BlogStorySeeder` also contains three
vertical story-crop URLs absent from the selected current rows. Those were
checked separately, two as alternate crops of current article photo IDs and
one as a source-only photo ID; all returned **200 / `image/jpeg`** (concurrency
2). Total checked: **33 exact URL/crop variants; 33 successful HEAD responses;
zero known broken HTTP responses**. These checks establish current response
headers only—not GET-body validity, decodability, dimensions, client rendering,
or future availability. No source image bytes were copied into the sandbox.

## Local category art and semantic review

All 54 populated category paths are local SVGs under
`.migration-backup/web/public/icons/categories/`; the 14 distinct paths exist
as files. These are family-level vector icons reused by service parent and
child categories, not photography. The 22 null category images are exactly
the legacy product-category rows (types 1–3); there are no remote category
photos in the inspected database. Their null values are a coverage gap, not
evidence of a broken file or a reason to create a missing-image fixture.

Source-to-label checks flag these review points, not independently verified
visual findings:

- `logo_img` is a photo field, not a supplied/verified business wordmark. The
  shop seed comments describe photographic content in logo slots (for example,
  salon chairs/products, Learning Hub pencils, and a tattoo machine). The
  brand image comment likewise describes a product lineup photo, not a
  verified logo. Keep these separate from approved identity artwork.
- The seeded products' descriptions and source comments are semantically
  plausible for the shampoo, conditioner, skincare serum, comb, and argan
  serum labels. The repeated argan image is intentional seed reuse across
  synthetic shop listings, not evidence of distinct stock photography or
  actual inventory imagery.
- The current “Browse products as well as appointments” article reuses the
  image URL paired by the original `BlogStorySeeder` with an appointment-led
  article. Treat this source-to-copy shift as a possible article-image
  mismatch for owner/editor review; this audit did not fetch or visually
  inspect the image.
- The existing service-family icons align at a broad category level; no
  product-category icon has been substituted for a service photo.

The Unsplash image URLs and their `auto=format`/`fit=crop`/size query
parameters are runtime CDN dependencies. Source comments identify some
photographers or describe subjects and sometimes call photos “free tier”; that
is not license evidence. Photographer identity, authorship, releases,
commercial-use rights, attribution requirements, current terms, and
image-to-record fit were **not independently verified**. No photo authorship
or license is claimed by this report.

## Keep these asset classes separate

Static brand/authentication artwork in the mockup sandbox (including
`images/agendaally-current/` and `images/agendaally-stage1/`) is presentation
material, not catalog media, and is excluded from the counts above. Category
SVGs are local taxonomy icons. Neither class should be treated as proof of
business/product imagery or copied into listing rows.

The three parent-provided generated illustrations already present under
`artifacts/mockup-sandbox/public/images/` are proposed *new* Stage 2 visuals,
not copies of current seed media:

| Existing generated source file | Proposed illustrative placement |
| --- | --- |
| `artifacts/mockup-sandbox/public/images/stage2-local-business.jpg` | General local-business showcase |
| `artifacts/mockup-sandbox/public/images/stage2-learning.jpg` | Learning/education showcase |
| `artifacts/mockup-sandbox/public/images/stage2-products.jpg` | Generic product-catalog showcase |
| `artifacts/mockup-sandbox/public/images/stage2-tailoring.jpg` | Added inclusive Tailoring category-art proposal; synthetic scene, not live supplier evidence |

Keep these mappings as mockup-only proposals. They do not depict or verify a
named current business, seller, brand, or SKU; do not replace native database
media or imply real inventory. No files were copied into
`artifacts/mockup-sandbox/public/images/agendaally-stage2-media-current/`
because representative third-party downloads were not needed and their
licensing is unresolved.

## Reproducible future local media pack

Before any native seed or production-media change, make an owner-reviewed,
offline-capable pack from a read-only allowlisted export. A package-free Node or
PHP tool can write a sorted manifest with record type/ID, public label, source
field, original URL and photo ID, crop parameters, intended local filename,
MIME type, dimensions, SHA-256, source seeder, alt-text intent, rights evidence,
attribution, reviewer, and review date. Deduplicate by original photo ID,
retain one reviewed master, and generate deterministic derivatives for each
display role; do not silently infer permission from a successful HEAD check or
a seeder comment. Store only rights-cleared files in a local media directory,
preserve the source-to-copy mapping, and keep generated illustrations marked
as generated. Separately approve any database path changes; this audit changed
no database rows, source seeders, native app files, packages, or workflows and
created no synthetic missing-image case.