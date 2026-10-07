# Story seller security correction

**Scope:** focused authorization and media-ownership correction in the original
Laravel backend under `.migration-backup/backend`. This follows the existing
Stories audit in
[`stories-audit-and-stage-2-proposal.md`](stories-audit-and-stage-2-proposal.md);
it is not a broader Stories lifecycle or product audit. No schema, lifecycle,
fixture, accepted database row, frontend, Calendar, payment, or finance changes
were made.

## Root cause

The audit found seller Story list results were scoped to the resolved shop and
bulk delete already passed that shop ID into the service, but route-bound
`show` and `update` did not check Story ownership. Update replaced the Story's
`shop_id` with the acting shop ID, so a seller who supplied another Story's ID
could change its owner. Create forced the Story's shop ID but did not verify
that its related shop/product/service belonged to that shop. Neither create nor
update checked `file_urls` ownership. Story uploads used one shared `stories`
directory, while removal converted submitted URL text into a local path and
unlinked it without proving that the file belonged to the Story's shop.

The seller route group also had role authentication but no shop-role grant
check for Stories. Admin Story management is a separate existing route family
and remains separate.

## Corrected seller endpoints and contracts

All routes remain under the existing authenticated `dashboard/seller` group:

| Endpoint | Authorization and ownership |
| --- | --- |
| `GET stories` | Requires `stories.view`; returned records continue to be filtered by the authenticated shop, overriding a submitted `shop_id`. |
| `GET stories/{story}` | Requires `stories.view`; a Story from another shop returns 404 before its resource is loaded. |
| `POST stories` | Requires `stories.manage`; the server sets `shop_id` from the resolved shop. A shop, product, or service relation must exist and belong to that same shop. All media URLs must be in that shop's upload namespace or already attached to a Story of that shop. |
| `PUT/PATCH stories/{story}` | Requires `stories.manage`; foreign Story IDs return 404. Update cannot transfer the Story to another shop; its related model and all submitted media are checked against the existing owning shop before any update. |
| `DELETE stories/{story}` | Requires `stories.manage`; deletes only the route-bound Story if it belongs to the authenticated shop; foreign Story IDs return 404. |
| `DELETE stories/delete` | Requires `stories.manage`; the existing bulk-ID contract remains, with deletion scoped to the authenticated shop. Foreign IDs are ignored by the scoped query. |
| `POST stories/upload` | Requires `stories.manage`; files are placed under `stories/shops/{shop_id}` rather than a shared Story upload namespace. |

The API request and response shapes are unchanged: Story records still contain
the same `file_urls` array and the upload still returns an array of absolute
URLs. The URL path for newly uploaded media now includes the shop namespace
(`.../storage/images/stories/shops/{shop_id}/...`). Existing URLs attached to
that shop's existing Stories may still be retained or reused while editing.
There is no new frontend field or payload requirement. Staff who use the vendor
portal need the relevant new shop grant assigned; the portal's URL-valued media
field continues to work with the returned URL.

The existing `active` request field remains manageable for an authorized Story
owner. No separate seller status/visibility endpoint or visibility field was
found or added.

## Staff and Admin permissions

The existing shop-grant middleware now gates Story reads with `stories.view`
and Story changes/uploads with `stories.manage`. The shop owner continues to
pass through the existing `User::hasShopPermission()` owner rule. Other seller
staff need an accepted invitation and a shop role carrying the corresponding
grant. `ShopPermissionSeeder` now declares these two keys so that the existing
shop-role grant interface can assign them; no staff role is implicitly granted
Story access.

Admin management is unchanged: the separate existing `role:admin|manager`
Admin routes retain list/show/delete/drop-all behavior and continue using the
unscoped administrative service operations intentionally.

## Media safety and lifecycle boundaries

Story upload media is now namespaced by numeric shop ID. For a new URL,
create/update require the configured media origin, the exact shop namespace,
the generated upload-filename form, and an existing backing object on the
configured storage disk. A forged correct-looking URL, a URL from another
host/shop, or an encoded/traversal path is rejected. Existing URLs may pass
only as exact values already attached to a Story of the same shop. File
removal uses the same origin/path/existence proof and skips URLs still
referenced by another Story of that shop. Thus a malicious or legacy
`file_urls` entry cannot make Story deletion unlink an unrelated object.

Legacy shared-directory files (including URLs already attached to a Story)
are not automatically deleted: their historical owner cannot be established
from the URL alone. This is a deliberate fail-safe cleanup limitation, not a
new migration or backfill.

The audit's public active/expiry gaps are unchanged: `active` is not currently
a public-feed filter; the feed's recent-date cutoff, daily deletion, and
portal-displayed 24-hour expiry are not one shared rule; expiry deletion can
leave media behind. No lifecycle redesign or public response change is part
of this correction.

## Focused verification

The isolated Hardening test creates only synthetic in-memory SQLite tables and
rows. It does not boot the application, read `.env`, run migrations, touch the
accepted development database, or write Story fixtures there. Laravel HTTP
test auth state is isolated per the existing
`.agents/memory/laravel-session-isolation.md` guidance.

Commands run from `.migration-backup/backend`:

```sh
HARDENING_VENDOR_AUTOLOAD=vendor/autoload.php \
  vendor/bin/phpunit --bootstrap tests/Hardening/bootstrap.php \
  tests/Hardening/StoryOwnershipSecurityTest.php
```

Result: **7 tests, 41 assertions passed**. Coverage includes a forged list
filter, seller controller own/foreign show/update/destroy, `active` changes,
shop/product/service relation ownership, real accepted shop-role grants and
denials (including another shop), upload-then-create, foreign/forged media,
safe cleanup of shared/legacy/foreign URLs, scoped bulk delete, and unchanged
Admin delete semantics. PHPUnit reported one deprecation notice.

PHP syntax checks passed for:

```sh
php -l app/Http/Controllers/API/v1/Dashboard/Seller/StoryController.php
php -l app/Services/StoryService/StoryService.php
php -l routes/api.php
php -l database/seeders/ShopPermissionSeeder.php
php -l tests/Hardening/StoryOwnershipSecurityTest.php
```

No broad suite, server, browser, or final app verification was run.