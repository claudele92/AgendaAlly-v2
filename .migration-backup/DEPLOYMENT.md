# Deployment checklist

> **Historical deployment notes, not the current deployment authority.**
> Use [`docs/deployment/provider-neutral-production.md`](../docs/deployment/provider-neutral-production.md)
> for the maintained application layout and production configuration guidance.
> The accepted MySQL first-install validation track is closed. Do not replay
> synthetic bootstrap/proof procedures or blindly execute historical migrations
> or seeders during deployment. The observations below are retained for context;
> current Composer behavior and release authorization are clarified here.

Steps to run after pulling backend changes into an environment where
Laravel's config/route/event caches are enabled (staging, production).

## Every deploy

1. `composer install --no-dev --optimize-autoloader` (or your usual install step)
2. Apply schema changes only under an explicit, approved release-specific
   migration plan; there is no unconditional historical migration replay.
3. `php artisan optimize:clear`

Run `optimize:clear` **every time**, not only when you know a change
touched routes/config/migrations — it's cheap, and skipping it on the
assumption "this deploy didn't touch anything cached" is exactly how a
stale-cache symptom gets mistaken for a new bug (or a real bug gets
mistaken for a stale cache) after the fact. `optimize:clear` clears the
cached config, routes, views, and events in one call; follow it with
`php artisan optimize` (or the more targeted `config:cache`/`route:cache`)
if the environment normally runs with those caches warm.

If you run persistent queue workers, restart them after deploying so they
pick up the new code (`php artisan queue:restart`).

## First-time setup on a new environment

- Copy `backend/.env.example` to `backend/.env` and fill in real values
  (DB credentials, `APP_URL`, `IMG_HOST`, mail, etc. - see the next
  section for the two that have bitten this codebase in production).
- `public/storage` (the symlink to `storage/app/public`, without which
  every uploaded file 404s even once `IMG_HOST` points at the right host)
  is now created automatically by `composer install`/`composer update` -
  see "Every deploy" below. It no longer needs a manual step on a fresh
  environment either; this line stays only as a record of what that
  automation does and why. If you ever run composer with `--no-scripts`,
  run `php artisan storage:link` by hand afterwards - it's a no-op
  (exits 0) if the symlink already exists, so it's always safe to re-run.
- `storage/` and `bootstrap/cache` must be writable by whichever user your
  web server actually runs as. This only bites once you move off
  `php artisan serve` (which runs as whoever started it - `root`, on a
  VPS shell) onto a real PHP-FPM + Nginx/Apache stack, where PHP runs as
  a dedicated, unprivileged user instead. Confirmed on a real VPS: after
  that switch, file uploads failed with `Unable to create a directory at
  .../storage/app/public/images/settings`, because `storage/` and
  `bootstrap/cache` were still owned by `root` with restrictive `700`
  permissions from the `artisan serve` days, and PHP-FPM had zero access
  to them. Fix:

  ```bash
  chown -R www-data:www-data /path/to/backend/storage
  chown -R www-data:www-data /path/to/backend/bootstrap/cache
  chmod -R 775 /path/to/backend/storage
  chmod -R 775 /path/to/backend/bootstrap/cache
  ```

  Don't assume `www-data` - confirm the actual user/group your PHP-FPM
  pool runs as first, since it varies by distro/setup:
  `grep -E "^user|^group" /etc/php/{version}/fpm/pool.d/www.conf`.

## Every deploy

`composer install` (step 1 above) now also runs `php artisan storage:link`
automatically via a `post-install-cmd`/`post-update-cmd` composer script -
this was previously a manual "first-time setup" step, found missing on a
real deployment for the second time in one day (once, its absence was the
direct cause of every uploaded image 404ing; a fix was applied by hand
and not made to survive future deploys the first time, which is exactly
why it recurred). It's cheap and idempotent, so it now runs on *every*
deploy rather than relying on someone remembering it was a one-time step
on a *new* environment specifically - the same reasoning `optimize:clear`
below is unconditional rather than conditional on "did this deploy touch
anything cached."

**Correction:** the current Composer scripts do **not** automatically run
`TranslationSeeder`, `MissingTranslationsSeeder` or `UnitSeeder`.
The earlier automatic-seeding claim is obsolete. The following records the
historical translation/unit issue, not an instruction to run these seeders.
A translation key
correctly committed to one of the first two seeder files (`en/web`
group, matched by `locale`+`group`+`key`) is not actually present on a
deployed database until one of these seeders is re-run there - a deploy
that merges a PR adding a new key does not, by itself, add that row to any
already-running environment's database, `git pull`/`composer install`
alone included. Found live: `faqs.short` and `collect.via.platform`/
`collect.via.platform.description` were correctly committed with
`firstOrCreate` in their respective seeder files, but rendered as raw,
untranslated keys in production until these seeders were run there by
hand. The same gap was then found again for `UnitSeeder` specifically:
its 33 demo units were correctly committed with `updateOrCreate` keyed
on a fixed `id`, but `SELECT COUNT(*) FROM units` on production still
returned 1 (the pre-existing default row) until it was added here too.
The historical seeders use `firstOrCreate`/`updateOrCreate`, but this is not
authority to execute them against production. They do not run unconditionally
on deployment. Any proposed reference/data change requires its own approved
release procedure; the accepted baseline/reference authorities remain unchanged.

## Environment variables to re-check on every new environment

`backend/.env` is not committed, so these don't travel with the repo - they
must be set by hand on every new environment (a fresh VPS, a tunnel URL that
changed, a real domain replacing a tunnel). `backend/.env.example` documents
sane starting values for local development; both of the following need a
real, deliberate value in production:

- `IMG_HOST` - the base URL prepended to every uploaded file's path (see
  `config('app.img_host')`'s doc comment). Must match a host the *browser*
  can actually reach, not just the backend process itself, **and must
  include the port** the backend actually listens on (or the port a
  reverse proxy forwards to). Found shipping as the literal placeholder
  `https://api.example.com/`, which made every freshly-uploaded image
  (logos, favicons, category/service/shop images) silently fail to
  display while the upload itself "succeeded" - the file really did land
  on disk, just under a URL nobody could load. A later deploy fixed the
  placeholder but dropped the port (`http://localhost` instead of
  `http://localhost:8000`), reproducing the identical symptom - a bare
  host with no port is exactly as broken as no host at all, just less
  obviously so. Don't let the next value become the same kind of stale
  placeholder once a real production domain is in place - update it again
  at that point, port included if it's not the default for its scheme.

  **On the dev server (`php artisan serve`) specifically, use `127.0.0.1`
  in `IMG_HOST`, never the literal hostname `localhost`, and keep
  `web/next.config.js`'s matching `images.remotePatterns` entry in sync
  with whichever one you pick.** `next/image` fetches the source image
  server-side, from the Next.js process itself, not the browser - and
  Node resolves `localhost` to the IPv6 loopback (`::1`) first.
  `php artisan serve --host=0.0.0.0` binds the IPv4 wildcard only; it
  never listens on `::1`. The result is a 400 from `/_next/image` that
  looks identical to a hostname-allowlist gap (same visible symptom as
  the placeholder-`IMG_HOST` bug above) but has a different cause and
  fix - confirmed via `curl http://[::1]:8000` (connection refused) vs.
  `curl http://127.0.0.1:8000` (200 OK) on a real VPS. curl silently
  falls back to IPv4 after an IPv6 failure, which is why this only shows
  up in the image optimizer's own server-side fetch and not in ordinary
  API testing with curl/Postman. **This is specific to the `artisan
  serve` dev server's single-stack bind** - PHP's built-in server can't
  listen on both IPv4 and IPv6 at once. A real production stack
  (PHP-FPM behind Nginx or Apache) doesn't have this limitation, but
  isn't automatically safe either - confirm whatever reverse proxy you
  deploy behind actually has a `listen [::]:PORT` (or equivalent
  dual-stack) directive before assuming this class of bug can't recur
  there, and prefer setting `IMG_HOST`/`APP_URL` to the real public
  domain in production anyway rather than any loopback address.
- `APP_URL` - same category of host-mismatch risk, and `FileHelper::
  uploadFile()` falls back to it when `IMG_HOST` is unset. Laravel's own
  default when `APP_URL` is unset is the literal string `http://localhost`
  - no port - so an unset `APP_URL` reproduces the exact same missing-port
  bug as an unset `IMG_HOST`. `FileHelper::uploadFile()` now falls back
  further still (to the current request's own host:port) if `APP_URL` is
  left at that literal default, so a forgotten `APP_URL` no longer
  produces a broken image URL by itself - but that fallback exists as a
  safety net, not a reason to skip setting `APP_URL` correctly. Set it
  explicitly, port included, the same as `IMG_HOST`.

## web/ (storefront) environment variables

`web/.env`/`web/.env.local` are not committed either, and one variable in
particular is worth checking for explicitly on every environment:

- `NEXT_PUBLIC_UI_TYPE` - if this is set to *anything*, it silently
  overrides the platform's `ui_type` setting (Settings > UI type in the
  admin panel) everywhere, in both `web/middleware.ts` (the rewrite that
  picks which `/home-N` page serves `/`) and `web/app/layout.tsx`. There
  is no warning in the UI when this happens - the admin setting just
  stops doing anything, and the homepage freezes on whichever view this
  variable resolves to (or, if it's set to something that isn't literally
  `"2"`, `"3"`, or `"4"`, on View 1 always, since the rewrite's
  `["2","3","4"].find(...)` then matches nothing). Confirmed by
  reproducing directly: with this set, the DB's `ui_type` was changed
  through several different values and even deleted entirely, and every
  request kept serving the exact same frozen view regardless - this is
  what happened on a real deployment, and is likely why "UI type doesn't
  work" resurfaced twice, presenting as two different-looking bugs (stuck
  on View 1; later, stuck on a different single view) that were actually
  the same misconfiguration at two different values.

  This is intended as a way to force one fixed homepage variant
  site-wide, deliberately overriding admin control - it should only be
  set if that's genuinely what you want. If the admin panel's UI-type
  control is supposed to work at all, make sure this variable is unset
  everywhere the app actually runs (`.env`, `.env.local`, and any
  process manager/container env config, not just the repo's own files).

  Like `NEXT_PUBLIC_BASE_URL` and every other `NEXT_PUBLIC_*` variable,
  this is inlined into the compiled bundle at **build time**, not read
  from live process env per request - changing or removing it requires a
  full rebuild (`next build`), not just a restart, the same as the
  `VITE_WEBSITE_URL` build-time-baking gotcha on the admin panel side.

## Why this matters here

Two bugs found in this codebase were only reachable because a stale or
never-rebuilt cache and a genuine code bug produce the identical symptom
from the outside (an "unexplained 404" or "endpoint doesn't work"):

- A route file change (registration order) that only takes effect once
  the cached route table is rebuilt.
- A migration whose effects depend on when it's run relative to seeding.

`optimize:clear` doesn't fix a genuine code bug, but running it as a
routine, unconditional step after every deploy is what lets you tell the
two apart quickly instead of re-investigating from scratch each time.
