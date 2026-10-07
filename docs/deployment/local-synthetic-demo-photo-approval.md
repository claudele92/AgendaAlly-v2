# Demo photo packaging — bounded owner-approval package

**REVIEW ONLY. Owner decision pending. No image copying is authorized.**

No prior owner-approved packaging decision exists. This proposal preserves the frozen inventory and baseline without granting redistribution rights.
No images are packaged, downloaded, substituted or embedded in this document.

Approval JSON SHA256: `e3da6988652481194b281e231e1da8ce8f1608c4c26b3b13035b52cd9ebe3a31`
This digest identifies the decision proposal, not an approval receipt.

## Frozen evidence

| Evidence | SHA256 |
|---|---|
| `docs/deployment/local-synthetic-portable-baseline.json` | `7aba06496ffb8efd3aba99bc2599773245c196d453d902c9bcb370a92314335c` |
| `docs/deployment/local-synthetic-demo-asset-review.json` | `354e00c29323e66dd235bcd2e67627ade92bc1820d4851e0e7d4e18d468880a3` |
| `docs/deployment/local-synthetic-demo-asset-review.md` | `b255895e27deedd501a0eac22ffa7f4af52d7dc0b2fb1e557683c2843f19eac1` |
| `attached_assets/service-photos/sources.json` | `abb43fcd6b6bfefb153a8898177a22459ab4424b38ac25c498b74a89af1b1b7e` |

## Proposed destination — not created or approved

- Sanitized repository: `.local/agendaally-clean-repository` (existing source snapshot, not independently certified Git history).
- Repository-relative package root: `.migration-backup/backend/resources/demo-assets`.
- Approved selected provenance would go to `.migration-backup/backend/resources/demo-assets/provenance.json`.
- Only the 15 listed Service entries and their approved source/license evidence; never copy the entire 23-image historical metadata or historical owner/target arrays.
- Public URLs remain `/storage/portable-demo/services/…` and `/storage/portable-demo/products/…`.
- No physical public destination has been selected; no write into existing runtime storage is proposed.
- The JSON retains exact per-file destinations, inventory pointers and every consumer relationship.

## 15 Service JPEG decisions

Every entry is PENDING for photo selection, public redistribution and provenance/license inclusion.
The recorded basis below is a historical claim, not a verified license or person/mark-rights certificate.

### beard-trim

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/0`.
- Existing input: `attached_assets/service-photos/beard-trim.jpg`.
- SHA256: `c3c037350ad6dff3011f13b4bb1404c5cb2cc8590ed427e4cd3257372213c755`.
- MIME/size: `image/jpeg`, **76210 bytes**.
- Source/provenance: https://www.pexels.com/photo/3998417/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/beard-trim.jpg`.
- Public demo path: `/storage/portable-demo/services/beard-trim.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 5 | 501 | Beard Trim |
| 11 | 502 | Beard Trim |
| 17 | 503 | Beard Trim |
| 23 | 504 | Beard Trim |
| 29 | 505 | Beard Trim |
| 35 | 506 | Beard Trim |
| 41 | 507 | Beard Trim |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 5 | `App\Models\Service` | 5 |
| 11 | `App\Models\Service` | 11 |
| 17 | `App\Models\Service` | 17 |
| 23 | `App\Models\Service` | 23 |
| 29 | `App\Models\Service` | 29 |
| 35 | `App\Models\Service` | 35 |
| 41 | `App\Models\Service` | 41 |

### hair-coloring

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/1`.
- Existing input: `attached_assets/service-photos/hair-coloring.jpg`.
- SHA256: `109cdcd88574ee68a0e3d43c0fda88cb119b478faf659628e2c15cfe6ffe64e7`.
- MIME/size: `image/jpeg`, **76776 bytes**.
- Source/provenance: https://www.pexels.com/photo/4981460/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/hair-coloring.jpg`.
- Public demo path: `/storage/portable-demo/services/hair-coloring.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 2 | 501 | Hair Coloring |
| 8 | 502 | Hair Coloring |
| 14 | 503 | Hair Coloring |
| 20 | 504 | Hair Coloring |
| 26 | 505 | Hair Coloring |
| 32 | 506 | Hair Coloring |
| 38 | 507 | Hair Coloring |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 2 | `App\Models\Service` | 2 |
| 8 | `App\Models\Service` | 8 |
| 14 | `App\Models\Service` | 14 |
| 20 | `App\Models\Service` | 20 |
| 26 | `App\Models\Service` | 26 |
| 32 | `App\Models\Service` | 32 |
| 38 | `App\Models\Service` | 38 |

### music-lessons

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/2`.
- Existing input: `attached_assets/service-photos/music-lessons.jpg`.
- SHA256: `0c738c821996adfc28d56ea071be0c325364d1ba42232293b30f7c37a044b011`.
- MIME/size: `image/jpeg`, **81424 bytes**.
- Source/provenance: https://www.pexels.com/photo/8520499/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/music-lessons.jpg`.
- Public demo path: `/storage/portable-demo/services/music-lessons.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 46 | 508 | Music Lessons |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 46 | `App\Models\Service` | 46 |

### custom-tattoo

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/3`.
- Existing input: `attached_assets/service-photos/custom-tattoo.jpg`.
- SHA256: `0c977c87e55fd624862a41761d02d772dccc84650f168f552eb1b98d16e4f47f`.
- MIME/size: `image/jpeg`, **104367 bytes**.
- Source/provenance: https://www.pexels.com/photo/28943303/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/custom-tattoo.jpg`.
- Public demo path: `/storage/portable-demo/services/custom-tattoo.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 48 | 509 | Custom Tattoo |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 48 | `App\Models\Service` | 48 |

### tattoo-touch-up

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/4`.
- Existing input: `attached_assets/service-photos/tattoo-touch-up.jpg`.
- SHA256: `7c42bfd9a6938d6d6ba83bd126b93325bab7efcd096297c405432f86edea224a`.
- MIME/size: `image/jpeg`, **126510 bytes**.
- Source/provenance: https://www.pexels.com/photo/29251824/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/tattoo-touch-up.jpg`.
- Public demo path: `/storage/portable-demo/services/tattoo-touch-up.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 49 | 509 | Tattoo Cover-Up & Touch-Up |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 49 | `App\Models\Service` | 49 |

### body-piercing

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/5`.
- Existing input: `attached_assets/service-photos/body-piercing.jpg`.
- SHA256: `d7f194de667ee635de3b2b51700f79374b02d46db0c83483972c35386e82e89b`.
- MIME/size: `image/jpeg`, **58186 bytes**.
- Source/provenance: https://www.pexels.com/photo/7901237/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/body-piercing.jpg`.
- Public demo path: `/storage/portable-demo/services/body-piercing.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 51 | 509 | Body Piercing |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 51 | `App\Models\Service` | 51 |

### ear-piercing

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/6`.
- Existing input: `attached_assets/service-photos/ear-piercing.jpg`.
- SHA256: `c3fe6419b9eff980539d3b3b3a01d5891b8fda51fc0166ef465407cc7fbf62ff`.
- MIME/size: `image/jpeg`, **75045 bytes**.
- Source/provenance: https://www.pexels.com/photo/19875392/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/ear-piercing.jpg`.
- Public demo path: `/storage/portable-demo/services/ear-piercing.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 50 | 509 | Ear Piercing |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 50 | `App\Models\Service` | 50 |

### haircut-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/7`.
- Existing input: `attached_assets/service-photos/haircut-africa-first.jpg`.
- SHA256: `3e4f2ac6f6da13a4915758a4a48f6fe8a5f417a67f23081622a98984f79542f7`.
- MIME/size: `image/jpeg`, **62536 bytes**.
- Source/provenance: https://www.pexels.com/photo/7447126/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/haircut-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/haircut-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 1 | 501 | Haircut |
| 7 | 502 | Haircut |
| 13 | 503 | Haircut |
| 19 | 504 | Haircut |
| 25 | 505 | Haircut |
| 31 | 506 | Haircut |
| 37 | 507 | Haircut |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 1 | `App\Models\Service` | 1 |
| 7 | `App\Models\Service` | 7 |
| 13 | `App\Models\Service` | 13 |
| 19 | `App\Models\Service` | 19 |
| 25 | `App\Models\Service` | 25 |
| 31 | `App\Models\Service` | 31 |
| 37 | `App\Models\Service` | 37 |

### bridal-makeup-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/8`.
- Existing input: `attached_assets/service-photos/bridal-makeup-africa-first.jpg`.
- SHA256: `448697ee57c25205aff282e5ca999d12cda01c9baf262ed794e79ecc7dc25b93`.
- MIME/size: `image/jpeg`, **41220 bytes**.
- Source/provenance: https://www.pexels.com/photo/11360229/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/bridal-makeup-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/bridal-makeup-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 6 | 501 | Bridal Makeup |
| 12 | 502 | Bridal Makeup |
| 18 | 503 | Bridal Makeup |
| 24 | 504 | Bridal Makeup |
| 30 | 505 | Bridal Makeup |
| 36 | 506 | Bridal Makeup |
| 42 | 507 | Bridal Makeup |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 6 | `App\Models\Service` | 6 |
| 12 | `App\Models\Service` | 12 |
| 18 | `App\Models\Service` | 18 |
| 24 | `App\Models\Service` | 24 |
| 30 | `App\Models\Service` | 30 |
| 36 | `App\Models\Service` | 36 |
| 42 | `App\Models\Service` | 42 |

### manicure-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/9`.
- Existing input: `attached_assets/service-photos/manicure-africa-first.jpg`.
- SHA256: `0e30dccb1edb99cf23abde2478d04da4e80be588db1168708d515938c9e8114c`.
- MIME/size: `image/jpeg`, **81265 bytes**.
- Source/provenance: https://www.pexels.com/photo/7755248/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/manicure-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/manicure-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 3 | 501 | Manicure |
| 9 | 502 | Manicure |
| 15 | 503 | Manicure |
| 21 | 504 | Manicure |
| 27 | 505 | Manicure |
| 33 | 506 | Manicure |
| 39 | 507 | Manicure |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 3 | `App\Models\Service` | 3 |
| 9 | `App\Models\Service` | 9 |
| 15 | `App\Models\Service` | 15 |
| 21 | `App\Models\Service` | 21 |
| 27 | `App\Models\Service` | 27 |
| 33 | `App\Models\Service` | 33 |
| 39 | `App\Models\Service` | 39 |

### massage-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/10`.
- Existing input: `attached_assets/service-photos/massage-africa-first.jpg`.
- SHA256: `abc59d92be779f6df7b229c62f0d34eb801dcb1d1c6275a2b6acfbd7e011a130`.
- MIME/size: `image/jpeg`, **104117 bytes**.
- Source/provenance: https://www.pexels.com/photo/19641816/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/massage-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/massage-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 4 | 501 | Massage Therapy |
| 10 | 502 | Massage Therapy |
| 16 | 503 | Massage Therapy |
| 22 | 504 | Massage Therapy |
| 28 | 505 | Massage Therapy |
| 34 | 506 | Massage Therapy |
| 40 | 507 | Massage Therapy |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 4 | `App\Models\Service` | 4 |
| 10 | `App\Models\Service` | 10 |
| 16 | `App\Models\Service` | 16 |
| 22 | `App\Models\Service` | 22 |
| 28 | `App\Models\Service` | 28 |
| 34 | `App\Models\Service` | 34 |
| 40 | `App\Models\Service` | 40 |

### academic-tutoring-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/11`.
- Existing input: `attached_assets/service-photos/academic-tutoring-africa-first.jpg`.
- SHA256: `521af75496781305188b57ec1e1315d1fb3c281e3722d3f33d71d30976c7a004`.
- MIME/size: `image/jpeg`, **47472 bytes**.
- Source/provenance: https://www.pexels.com/photo/5905444/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/academic-tutoring-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/academic-tutoring-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 43 | 508 | Academic Tutoring |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 43 | `App\Models\Service` | 43 |

### language-lessons-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/12`.
- Existing input: `attached_assets/service-photos/language-lessons-africa-first.jpg`.
- SHA256: `27aceff04fbb10a01c5f8571611f6f97b27ba675c3f2223393eeb48bf2a7479b`.
- MIME/size: `image/jpeg`, **45978 bytes**.
- Source/provenance: https://www.pexels.com/photo/5905936/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/language-lessons-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/language-lessons-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 45 | 508 | Language Lessons |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 45 | `App\Models\Service` | 45 |

### computer-skills-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/13`.
- Existing input: `attached_assets/service-photos/computer-skills-africa-first.jpg`.
- SHA256: `2acd95ea89d21b5ecc647d0e10f1aeaeb36343f061a3a6e6730f1fce9f5c3f69`.
- MIME/size: `image/jpeg`, **61369 bytes**.
- Source/provenance: https://www.pexels.com/photo/5940706/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/computer-skills-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/computer-skills-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 47 | 508 | Computer & Digital Skills |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 47 | `App\Models\Service` | 47 |

### exam-preparation-africa-first

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/service_photos/14`.
- Existing input: `attached_assets/service-photos/exam-preparation-africa-first.jpg`.
- SHA256: `8f7282e47fda1a0cd860565aa84cf6066e5df973557bee078aacbd58167c29c0`.
- MIME/size: `image/jpeg`, **49195 bytes**.
- Source/provenance: https://www.pexels.com/photo/5940708/
- Recorded license: https://www.pexels.com/license/
- Recorded redistribution basis: Pexels License, https://www.pexels.com/license/; development/demo use, original downloaded assets, no runtime hotlinks. No implied endorsement. Do not resell unaltered assets or portray depicted people in a misleading or offensive context.
- Evidence limit: Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.
- Proposed sanitized-source destination: `.local/agendaally-clean-repository/.migration-backup/backend/resources/demo-assets/services/exam-preparation-africa-first.jpg`.
- Public demo path: `/storage/portable-demo/services/exam-preparation-africa-first.jpg`.

| Service ID | Shop ID | Title |
|---|---|---|
| 44 | 508 | Exam Preparation |

| Gallery ID | Loadable type | Loadable ID |
|---|---|---|
| 44 | `App\Models\Service` | 44 |

## Five unresolved Product photo dependencies

All five remain BLOCKED. Frozen evidence has NULL local file, byte count, MIME and SHA256, no filename candidates, and insufficient redistribution evidence.
The prior bounded search is not proof that no equivalent exists anywhere; no wider/private/runtime search is authorized.
Remote URLs and source comments are evidence only, not authority to download, substitute or map an unrelated photo.

### Moroccan Argan Oil Hair Serum

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/product_photos/0`.
- Source URL (not fetched): https://images.unsplash.com/photo-1779492907379-0894f8807533?auto=format&fit=crop&w=800&h=800&q=80
- Source evidence: `.local/agendaally-clean-repository/.migration-backup/backend/database/seeders/ProductCatalogDemoSeeder.php:87`; SHA256 `01754031e6f691003a35f963c95c3dbaaa0f529dd9f7575a77e0b6bbe9cae81d`.
- Unverified source claims: "Hair serum bottle resting on a stack of elegant books"; (photo id eLm-P_CEdw0, by Ela De Pure).
- License to review (not proof): https://unsplash.com/license
- Local file/bytes/MIME/SHA256: **NULL / NULL / NULL / NULL**. Filename candidates: 0.
- Proposed repository destination (blocked): `.migration-backup/backend/resources/demo-assets/products/product-1.jpg`.
- Public demo path: `/storage/portable-demo/products/product-1.jpg`.

| Consumer | ID | Relationship |
|---|---|---|
| products | 1 | Shop 501; Moroccan Argan Oil Hair Serum |
| products | 6 | Shop 502; Moroccan Argan Oil Hair Serum |
| products | 7 | Shop 503; Moroccan Argan Oil Hair Serum |
| products | 8 | Shop 504; Moroccan Argan Oil Hair Serum |
| products | 9 | Shop 505; Moroccan Argan Oil Hair Serum |
| products | 10 | Shop 506; Moroccan Argan Oil Hair Serum |
| products | 11 | Shop 507; Moroccan Argan Oil Hair Serum |
| products | 12 | Shop 508; Moroccan Argan Oil Hair Serum |
| products | 13 | Shop 509; Moroccan Argan Oil Hair Serum |
| stocks | 1 | Product 1 |
| stocks | 6 | Product 6 |
| stocks | 7 | Product 7 |
| stocks | 8 | Product 8 |
| stocks | 9 | Product 9 |
| stocks | 10 | Product 10 |
| stocks | 11 | Product 11 |
| stocks | 12 | Product 12 |
| stocks | 13 | Product 13 |
| stocks | 14 | Product 1 |
| galleries | 52 | App\Models\Product 1 |
| galleries | 57 | App\Models\Product 6 |
| galleries | 58 | App\Models\Product 7 |
| galleries | 59 | App\Models\Product 8 |
| galleries | 60 | App\Models\Product 9 |
| galleries | 61 | App\Models\Product 10 |
| galleries | 62 | App\Models\Product 11 |
| galleries | 63 | App\Models\Product 12 |
| galleries | 64 | App\Models\Product 13 |

### Moisturizing Shampoo

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/product_photos/1`.
- Source URL (not fetched): https://images.unsplash.com/photo-1747858989102-cca0f4dc4a11?auto=format&fit=crop&w=800&h=800&q=80
- Source evidence: `.local/agendaally-clean-repository/.migration-backup/backend/database/seeders/ProductCatalogDemoSeeder.php:97`; SHA256 `01754031e6f691003a35f963c95c3dbaaa0f529dd9f7575a77e0b6bbe9cae81d`.
- Unverified source claims: "Moisturizing shampoo bottle on a neutral background" (photo; id K1k8M_bb2bM, by Ela De Pure).
- License to review (not proof): https://unsplash.com/license
- Local file/bytes/MIME/SHA256: **NULL / NULL / NULL / NULL**. Filename candidates: 0.
- Proposed repository destination (blocked): `.migration-backup/backend/resources/demo-assets/products/product-2.jpg`.
- Public demo path: `/storage/portable-demo/products/product-2.jpg`.

| Consumer | ID | Relationship |
|---|---|---|
| products | 2 | Shop 501; Moisturizing Shampoo |
| stocks | 2 | Product 2 |
| galleries | 53 | App\Models\Product 2 |

### Moisturizing Conditioner

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/product_photos/2`.
- Source URL (not fetched): https://images.unsplash.com/photo-1755655618085-e0267f9fbd81?auto=format&fit=crop&w=800&h=800&q=80
- Source evidence: `.local/agendaally-clean-repository/.migration-backup/backend/database/seeders/ProductCatalogDemoSeeder.php:107`; SHA256 `01754031e6f691003a35f963c95c3dbaaa0f529dd9f7575a77e0b6bbe9cae81d`.
- Unverified source claims: "Two pump bottles of moisturizing shampoo and conditioner"; (photo id 5eoYsqzmDW4, by Ela De Pure).
- License to review (not proof): https://unsplash.com/license
- Local file/bytes/MIME/SHA256: **NULL / NULL / NULL / NULL**. Filename candidates: 0.
- Proposed repository destination (blocked): `.migration-backup/backend/resources/demo-assets/products/product-3.jpg`.
- Public demo path: `/storage/portable-demo/products/product-3.jpg`.

| Consumer | ID | Relationship |
|---|---|---|
| products | 3 | Shop 501; Moisturizing Conditioner |
| stocks | 3 | Product 3 |
| galleries | 54 | App\Models\Product 3 |

### Vitamin C Brightening Serum

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/product_photos/3`.
- Source URL (not fetched): https://images.unsplash.com/photo-1723951174326-2a97221d3b7f?auto=format&fit=crop&w=800&h=800&q=80
- Source evidence: `.local/agendaally-clean-repository/.migration-backup/backend/database/seeders/ProductCatalogDemoSeeder.php:117`; SHA256 `01754031e6f691003a35f963c95c3dbaaa0f529dd9f7575a77e0b6bbe9cae81d`.
- Unverified source claims: "A bottle of vitamin c on top of lemon slices" (photo id; rqK_baq_XgI, by Ela De Pure).
- License to review (not proof): https://unsplash.com/license
- Local file/bytes/MIME/SHA256: **NULL / NULL / NULL / NULL**. Filename candidates: 0.
- Proposed repository destination (blocked): `.migration-backup/backend/resources/demo-assets/products/product-4.jpg`.
- Public demo path: `/storage/portable-demo/products/product-4.jpg`.

| Consumer | ID | Relationship |
|---|---|---|
| products | 4 | Shop 501; Vitamin C Brightening Serum |
| stocks | 4 | Product 4 |
| galleries | 55 | App\Models\Product 4 |

### Wide-Tooth Detangling Comb

- Inventory identity: `docs/deployment/local-synthetic-demo-asset-review.json#/product_photos/4`.
- Source URL (not fetched): https://images.unsplash.com/photo-1587477444258-096b2d514c12?auto=format&fit=crop&w=800&h=800&q=80
- Source evidence: `.local/agendaally-clean-repository/.migration-backup/backend/database/seeders/ProductCatalogDemoSeeder.php:127`; SHA256 `01754031e6f691003a35f963c95c3dbaaa0f529dd9f7575a77e0b6bbe9cae81d`.
- Unverified source claims: "brown hair comb on white wooden table" (photo id; kkQdRnasB98, by Apothecary 87).
- License to review (not proof): https://unsplash.com/license
- Local file/bytes/MIME/SHA256: **NULL / NULL / NULL / NULL**. Filename candidates: 0.
- Proposed repository destination (blocked): `.migration-backup/backend/resources/demo-assets/products/product-5.jpg`.
- Public demo path: `/storage/portable-demo/products/product-5.jpg`.

| Consumer | ID | Relationship |
|---|---|---|
| products | 5 | Shop 501; Wide-Tooth Detangling Comb |
| stocks | 5 | Product 5 |
| galleries | 56 | App\Models\Product 5 |

## Proposed NULL photo fields — preserved, not accepted parity

Preserve every proposed NULL exactly, including Shop, Specialist/profile, Brand, CMS/blog and country flags. Proposed NULLs are not accepted photo-complete parity.

| Family | Row ID | Proposed NULL fields |
|---|---|---|
| shops | 501 | background_img, logo_img |
| shops | 502 | background_img, logo_img |
| shops | 503 | background_img, logo_img |
| shops | 504 | background_img, logo_img |
| shops | 505 | background_img, logo_img |
| shops | 506 | background_img, logo_img |
| shops | 507 | background_img, logo_img |
| shops | 508 | background_img, logo_img |
| shops | 509 | background_img, logo_img |
| users | 107 | img |
| users (Specialist) | 112 | img |
| users | 113 | img |
| users | 114 | img |
| users (Specialist) | 116 | img |
| users | 117 | img |
| users (Specialist) | 118 | img |
| users | 119 | img |
| users | 120 | img |
| users (Specialist) | 121 | img |
| users | 122 | img |
| users | 123 | img |
| users (Specialist) | 124 | img |
| users | 125 | img |
| users | 126 | img |
| users (Specialist) | 127 | img |
| users | 128 | img |
| users | 129 | img |
| users (Specialist) | 130 | img |
| users | 131 | img |
| users | 132 | img |
| users (Specialist) | 133 | img |
| users (Specialist) | 134 | img |
| users (Specialist) | 135 | img |
| users (Specialist) | 136 | img |
| users (Specialist) | 137 | img |
| users | 138 | img |
| users (Specialist) | 139 | img |
| users (Specialist) | 140 | img |
| brands | 1 | img |
| pages | 1 | img, bg_img |
| pages | 2 | img, bg_img |
| pages | 3 | img, bg_img |
| pages | 4 | img, bg_img |
| blogs | 1 | img |
| blogs | 2 | img |
| blogs | 3 | img |
| countries | 1 | img |
| countries | 2 | img |
| countries | 3 | img |
| countries | 4 | img |

## Exact owner decision requested

1. Identify this approval JSON SHA256 and all four frozen evidence SHA256 values in the owner decision.
2. List accepted Service inventory identities (not an inferred approve-all), exact bytes/MIME/SHA256 and unchanged Service/Gallery relationships or an explicitly revised frozen relationship proposal.
3. For each selected Service, explicitly authorize public redistribution and source/license/provenance inclusion; provide sufficient license evidence, required attribution/restrictions and any person/mark rights basis. Recorded links alone are not certification.
4. Accept or revise the proposed sanitized repository, repository-relative package paths and provenance destination. Separately name the fresh physical public demo destination before execution; do not authorize overwriting existing storage.
5. Keep all five Product dependencies blocked, or supply a separately frozen local-byte/licensing proposal preserving all Product/Stock/Gallery relationships or explicitly approving revisions. No remote download or arbitrary substitution.
6. Preserve the listed proposed NULL fields unless a separately reviewed exact replacement is approved; explicitly distinguish proposed NULL preservation from acceptance of visual parity.
7. Give separate explicit execution authority before any image copy. This document, its existence and review completion confer none.

No selections or signatures are prefilled. Owner approval must be recorded before any bytes are copied.
Approving this document for review alone does not authorize execution.

## Deferred execution and acceptance

Consumer totals remain **51 Service, 13 Product, 14 Stock and 64 Gallery**.
Future rejection contract: symlinked input or parent; symlinked destination or parent; non-regular file; private/runtime upload; unapproved asset; missing/mismatched hash or bytes; wrong MIME; occupied destination even if identical; path escape; runtime download.
Preflight the entire approved selection before copying. Never overwrite or fetch at bootstrap.
Verify exact approved bytes/MIME/hash and every relationship in a newly sanitized checkout with no access to workspace-only assets or network.
Future installer tests must prove corruption, absence, symlinks and occupied-destination failures without modifying any database.
REVIEW_ONLY; no package created and no fresh-checkout availability or installer negative-failure proof claimed.

Not authorized: image packaging/copying; database operations; runtime storage writes; external downloads; credentials/identities/keys; financial operations; provider activation; GitHub actions; VPS actions; Stories changes.

## Read-only reproduction

```sh
node scripts/database/demo-photo-approval.mjs --check
node --test scripts/database/demo-photo-approval.test.mjs scripts/database/demo-asset-review.test.mjs
node scripts/database/check-synthetic-reference-review.mjs --check
```

`--check` compares both generated documents, pinned review hashes and existing source/photo evidence.
`--print-json` and `--print-md` emit review text only; no install/copy/approval mode exists.
Current workspace checks are not fresh-checkout availability proof.
