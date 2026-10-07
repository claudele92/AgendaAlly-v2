# Guarded local Story fixtures

The development Story seed is a local illustration fixture, not vendor media.
Its only owner is the reviewed synthetic seller `owner@agendaally.test`
(reserved test phone `+12025550107`, user 107), whose approved synthetic shop
is shop 501. It links one existing published Product and one existing accepted
Service from that same shop, plus one Story for the shop itself. It creates
three active rows with `created_at` set during the explicit seed operation;
the application can therefore treat them as ordinary roughly 24-hour Stories.
There is no schema marker, permanent expiry exemption, or scheduler.

Image provenance is the checked-in Stage 2 mockup-sandbox illustration set:

| Story target | Source | SHA-256 |
| --- | --- | --- |
| Shop | `artifacts/mockup-sandbox/public/images/stage2-local-business.jpg` | `b97162f16e748af78779ec2455f1edcfcdc931f37a7f620dde59c39c6dd903b2` |
| Product | `artifacts/mockup-sandbox/public/images/stage2-products.jpg` | `94c8a65b6d513efb235dd64b6b7045fcfdc90e16f15d2971740e3dded3ab183e` |
| Service | `artifacts/mockup-sandbox/public/images/stage2-local-business.jpg` | `b97162f16e748af78779ec2455f1edcfcdc931f37a7f620dde59c39c6dd903b2` |

At seed time, these files are copied into the standard shop-scoped Story upload
namespace `storage/app/public/images/stories/shops/501/`. Fixed timestamp/UUID
filenames follow `StoryService`'s generated upload filename format. Existing
files with different contents are never replaced. The database identity is the
exact shop/model/media tuple; an exact unchanged row has only `created_at`
refreshed on an explicit seed. A conflicting, edited, or differently-owned row
is preserved rather than claimed or overwritten.

Both modes use the guarded `development:database-seed` command, which requires
the opted-in, manifest-owned local SQLite database and its exclusive lock:

```sh
node scripts/development.mjs seed --stories-only
node scripts/development.mjs seed
```

`--stories-only` changes only Story rows and their three shop-scoped local image
files; it does not seed the catalog, accounts, bookings, orders, payments,
finance, translations, or CMS content. The development integration test runs
the fixture repeatedly and hashes every non-Story table to assert that boundary.