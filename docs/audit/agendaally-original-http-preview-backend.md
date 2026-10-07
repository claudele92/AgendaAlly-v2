# Original Laravel HTTP preview backend

## Scope and safety

The backend preview uses the original application source in
`.local/agendaally-preview/backend` and its original locked Composer packages.
On first use, the provisioner copies only the application, entry point,
bootstrap/config/routes/resources, public files, Composer manifests, and
factories into that owned directory. It restores Composer packages only if
the local vendor autoloader is absent, with Composer scripts and plugins
disabled.
The provisioning scripts do not edit the application, composer manifests/lock,
or `.migration-backup`. They do not run Laravel migrations, framework seeders,
or any production configuration. A small SQLite schema is declared explicitly
in `scripts/seed-original-http-preview.php`, based on the reviewed original
migration definitions and the queries/eager-loaded relations exercised by the
original routes.

The persistent synthetic database is
`.local/agendaally-preview/backend/storage/preview.sqlite`. Its fixtures are
limited to English as the active locale, Central African CFA franc, Cameroon/Douala, one studio,
one public service/master, one public product, one future synthetic booking,
and private synthetic local accounts. Original-schema tables needed by the
frontend root/country requests (`brands`, `stories`, `payments`,
`country_payments`, `banners`, `banner_translations`, `banner_products`, and
`likes`) are present without external payment configuration or production
media. The banner paginator filters through products and stocks and counts
products and likes, so its original query requires those four banner-related
tables even when no banner fixture is desired; the synthetic banner collection
remains empty. The `admin`, `user`, and `master` roles
use the IDs defined by the original `RoleSeeder`; the admin is an unrestricted
global administrator under the original `User::isSuperAdmin()` rules. The
original app has no `bootstrap/providers.php`; its application providers are
registered in `config/app.php`, which is copied unchanged along with the
bootstrap directory. No provider behavior is changed. Product/service media
fields are nullable; no stock photos or production media are included.
Database access, generated app key, and local account credentials are
restricted to the owned runtime. Generated passwords are not printed or
included here.

Without a development domain, the serve script defaults to loopback on port
8000 for local verification. In Replit it uses the available public development
hostname.
Passing the public development hostname (without scheme or port) configures
`APP_URL=https://<domain>:8000`, binds to `0.0.0.0`, and permits only the exact
`https://<domain>:3002` and `https://<domain>:3003` browser origins. The
optional `loopback` third argument retains loopback binding while testing that
public-origin configuration. The script clears inherited process variables.
PHP network streams,
cURL execution and process launch functions are disabled; URL fopen is off,
and Laravel's HTTP client blocks unregistered outbound requests. Mail, queue,
cache, and session are local-only.

## Provision and serve

```sh
scripts/provision-original-http-preview.sh
scripts/serve-original-http-preview-backend.sh 8000
```

For an exact-origin loopback check before exposing the backend, provide the
development hostname and the `loopback` bind mode:

```sh
scripts/serve-original-http-preview-backend.sh 8000 "$REPLIT_DEV_DOMAIN" loopback
scripts/verify-original-http-preview-backend.sh http://127.0.0.1:8000 "$REPLIT_DEV_DOMAIN"
```

Omit `loopback` only after route and origin checks pass and the owning agent
intends to expose the backend port. **Loopback binding alone is not an access
control boundary when Replit already forwards that port.** Use private
development URLs to restrict viewers; the runtime contains synthetic data only.

Provisioning is idempotent and preserves the SQLite database and private
credentials between runs. It refuses to seed if the runtime ownership marker,
source path, or Composer manifests do not match the expected original runtime.
The three local-account credentials are saved in the runtime-only
`.preview-credentials` file with mode 0600. Do not copy or display it.

## HTTP check

With the backend already running, use:

```sh
scripts/verify-original-http-preview-backend.sh
```

The check exercises the actual original routes for settings, active languages
and currencies, English translations, country/city discovery, product/service
discovery, geographically filtered product and service discovery using the
storefront's English/XAF/Douala query, and the exact service-category request
used by the customer `/search/service` route. It also requires the root
`/banners/paginate?lang=en` request to return a successful, valid empty
collection. Other checks cover CORS allow/reject behavior,
invalid-credential rejection, missing and invalid bearer-token rejection,
original email/password login, and token-protected admin product/booking lists.
It requires non-empty data for settings, catalogs, public discovery, customer
service search, and both protected admin lists. It does not print credentials
or the issued bearer token.

## Findings and limits

The provided schema is intentionally narrower than the production database.
Additional original endpoints may require further reviewed, endpoint-scoped
tables and columns. Such gaps should be added only to the synthetic SQLite
schema/fixtures after confirming their original migration definitions; do not
change application behavior or run historical migrations. No image content is
seeded, so media-dependent UI regions may remain blank.

The parent agent's verifier after its CORS-configured restart reported PASS for
public routes, exact-origin CORS allow/reject, invalid credentials, and missing
or invalid bearer tokens. Real admin login then failed with HTTP 500 because
the persistent synthetic `personal_access_tokens` table lacked `expires_at`.
The preview seeder now adds that nullable column from the exact original
2025-11-25 migration, without invoking migrations or changing Laravel behavior.
The parent also reported original-schema table errors for `brands`, `stories`,
`payments`, and `country_payments` in the country/root SSR paths; their reviewed,
scoped table definitions are now provisioned, with no gateway credentials or
media seeded.

The parent reran the full verifier after these additions: every check passed,
including real admin login, non-empty token-protected product and booking
lists, exact-origin CORS and all negative authentication checks. Country and
city detail requests returned HTTP 200. Credentials and tokens were withheld.
Composer and both frontend Yarn locks still match the originals, and no
tracked original-source files changed.

## Customer discovery/root catalog gap

The customer product listing sends `lang=en`, `currency_id=1`, and the active
`region_id=1`, `country_id=1`, and `city_id=1` filters. The existing synthetic
shop locations and public product/service fixtures match that geography. The
`/search/service` UI uses the original category paginator with
`type=service`, `has_service=1`, and `column=input`; the local Wellness
synthetic category satisfies that request. These exact original requests now
have explicit non-empty assertions in the backend verifier.

The root product page also requests `/api/v1/rest/banners/paginate?lang=en`.
The pre-fix request returned HTTP 500 because the owned SQLite subset omitted
tables used by the original banner repository/resource path. The reviewed
original migration definitions are now represented as synthetic SQLite
definitions for `banners` (including its later `shop_id` addition),
`banner_translations`, `banner_products`, and polymorphic `likes`. No banner or
like rows are added; the verifier asserts a successful empty collection.

After reprovisioning the owned database, the full command
`bash scripts/verify-original-http-preview-backend.sh http://127.0.0.1:8000 "$REPLIT_DEV_DOMAIN"`
passed, including geographic customer product/service requests, customer
service-category search, and the empty root banner collection. Provisioning
preserved the persistent database and credentials. No application behavior,
original source/locks, migrations, providers, or dependencies were changed.

The `original-laravel-preview` workflow now serves this owned runtime on port
8000. The two separate frontend root pages return HTTP 200. API-level success
alone did not establish the admin browser sign-in path. The creator
subsequently approved preview-only reCAPTCHA and disabled-installer
redirect exceptions. Those guarded development overlays are now implemented,
and real admin browser sign-in, reload persistence and visible product/booking
rows are verified. Original source and production authentication remain
unchanged. See `agendaally-connected-preview-status.md` for evidence and limits,
and `agendaally-preview-localization.md` for original translation restoration.