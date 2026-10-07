# AgendaAlly portable development

This is the **existing AgendaAlly application**, not an Express replacement or a new UI. The active projects remain in their historical directories:

| Application | Source | Default local origin |
| --- | --- | --- |
| Laravel REST v1 API | `.migration-backup/backend` | `http://localhost:8000` |
| Next.js customer marketplace | `.migration-backup/web` | `http://localhost:3002` |
| React / Ant Design vendor and administration portal | `.migration-backup/admin` | `http://localhost:3003` |

The directory name is historical: these are the maintained development sources. `.local/` preview copies and runtime-transform adapters are not part of normal setup. Root pnpm scaffold services are unrelated to AgendaAlly and are not its backend.

## Requirements

- Node.js 24 and Yarn Classic.
- PHP 8.4 with PDO SQLite, mbstring, XML, fileinfo, OpenSSL and the extensions required by the locked Composer packages.
- Composer 2.
- A writable checkout. No Docker, production database, provider credential, Firebase service account or Replit account is required for localhost development.

Use the checked-in Composer and Yarn lockfiles. The setup does not update dependency versions and does not run database-mutating Composer hooks.

The storefront uses Next's supported Webpack build compiler and a locally
bundled copy of the same Inter font, so strict offline builds do not need
Turbopack's TCP evaluation workers or Google Fonts requests. Headless UI is
pinned to the React 19-compatible release in its source Yarn lockfile; the
original React and Next versions are retained.

## Fresh checkout

From the repository root:

```sh
node scripts/development.mjs install
node scripts/development.mjs init
node scripts/development.mjs configure
node scripts/development.mjs bootstrap
node scripts/development.mjs seed
```

`init` copies each application's `.env.example` into a private, ignored `.env` only if the destination does not exist. It does not print or replace existing credentials. `configure` runs Laravel package discovery, generates a missing application key and creates the normal public-storage link.

`bootstrap` requires **all** of `APP_ENV=local`, `DEVELOPMENT_MODE=true`, `AGENDAALLY_DEVELOPMENT_DATABASE=true` and an explicitly selected SQLite file directly under the backend's `database/development/` directory. The default is `agendaally.sqlite`. It creates a new file exclusively and refuses foreign/unowned databases, server URLs, symlinks and staging/production. It never calls `migrate:fresh`, drops a populated database, or runs the old `DatabaseSeeder`.

The historical migration chain contains destructive `up()` operations, including the order-table replacement. The development manifest fingerprints the reviewed source. Replaying historical migrations after losing an existing database's migration ledger is refused. Read the [backend schema and seed documentation](../../.migration-backup/backend/database/development/README.md) before changing the reviewed baseline.

`seed` invokes the guarded, repeatable `development:database-seed` command. Demo entities are synthetic and intentionally labeled. It restores known demo fixture values without resetting the database; do not treat edits to those fixtures as permanent. Other development records must not be deleted. Accounting and payout examples are offline records, not executed financial operations.

The guarded demo seed includes an active **cash** payment catalog entry and a
translated Douala demo area, delivery price, and free pickup point with a
seven-day schedule. These use the existing checkout flow without Google Maps
or a remote payment provider. Cash checkout is not a simulated successful
online payment; external gateways remain disabled and unconfigured.

## Start the three applications

Open three terminals at the repository root:

```sh
node scripts/development.mjs serve backend
node scripts/development.mjs serve web
node scripts/development.mjs serve admin
```

The backend is the ordinary Laravel application and standard Laravel development router, not a substitute front controller. Its local launcher checks database ownership before serving and disables independent outbound SDK transports as defense in depth. The frontends run their ordinary Next/Vite servers and load native dotenv files.

The source projects also retain normal framework commands:

```sh
cd .migration-backup/backend
php artisan development:database-bootstrap --confirm-empty-sqlite
php artisan development:database-seed
php artisan serve --host=127.0.0.1 --port=8000
```

Run `yarn dev` in the storefront or admin source directory. The storefront's native launcher reads `PORT` (default 3002); Vite reads `VITE_PORT` (default 3003). Shell/deployment values take precedence in the frontend launchers. Development tooling selects the verified app-specific SQLite configuration, not a host's unrelated database URL.

## Replit development

The same sources and commands are used; there is no application dependency on Replit:

```sh
node scripts/development.mjs init --replit
# If .env files already exist and only public origins need changing:
node scripts/development.mjs origins --replit
```

The opt-in helper derives the three development HTTPS origins from the development hostname and configured ports. It changes only allowlisted public URLs/CORS values, not provider credentials. Existing workflows launch the direct source on 3002, 3003 and 8000. Do not confuse the unrelated scaffold or Canvas preview with the AgendaAlly storefront.

To return to localhost public URLs:

```sh
node scripts/development.mjs origins
```

Never point a local-mode client or demo account at a live API.

## Synthetic local accounts

Shared **local-only** password: `AgendaAlly-Dev-Only-2026!`. Never reuse it or seed these accounts outside the owned development database.

| Email | Purpose |
| --- | --- |
| `admin@agendaally.test` | Platform Super Admin |
| `country-manager@agendaally.test` | Invited Country Manager |
| `manager@agendaally.test` | Platform manager |
| `owner@agendaally.test` | Vendor owner |
| `staff@agendaally.test` | Shop-invited manager/staff where supported |
| `master@agendaally.test` | Master/specialist |
| `customer@agendaally.test` | Customer storefront |
| `finance@agendaally.test` | Invited country Main Accountant |

Platform `manager`, country invitations and shop `shop_manager` grants are different authorization concepts. Do not replace them with an invented universal “vendor manager” role. Supported country/shop grants and assigned branches are part of the fixture, not just display labels.

## Configuration and external integrations

| Area | Configuration | Safe local default |
| --- | --- | --- |
| Origins | `LARAVEL_BACKEND_URL`, `CUSTOMER_STOREFRONT_URL`, `VENDOR_ADMIN_URL`, `CORS_ALLOWED_ORIGINS` | Exact local application origins |
| Database | `DB_CONNECTION`, `DB_DATABASE`; explicit `DB_URL` where appropriate | Owned SQLite file; no inherited server URL |
| Cache/session/queues | `CACHE_DRIVER`/`CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION` | File cache/sessions; synchronous queue |
| Storage | Standard Laravel filesystem disks and `storage:link` | Local/public storage |
| Mail/SMS | `EMAIL_MODE`, `MAIL_MAILER`, `SMS_MODE` and provider-specific env | Logging/disabled, never real delivery |
| Payments | `PAYMENT_MODE` and provider configuration | External methods disabled; original cash/wallet retained |
| Firebase | `FIREBASE_ENABLED` and explicit credential/client variables | Disabled; no credential auto-discovery |
| reCAPTCHA | `RECAPTCHA_ENABLED` and admin `VITE_RECAPTCHA_DISABLED` | Explicit local-only bypass |
| Maps | `MAPS_ENABLED`, `NEXT_PUBLIC_MAPS_ENABLED`, `VITE_MAPS_ENABLED` and public keys | Provider components do not mount without an allowed key |
| Frontend API | `NEXT_PUBLIC_BASE_URL` (ends `/api/`), `VITE_BASE_URL` or preferred `VITE_API_ORIGIN` | Configured API origin; no hidden preview adapter |

Development behavior requires an explicit development flag **and** the appropriate local/testing environment. A staging or production environment does not become local because a flag is present. Admin CAPTCHA bypass is only available to local Vite development, not production builds.

Sandbox provider use is a separate explicit configuration step; it was not exercised for this foundation. Live payment mode is production-only. Missing or disabled providers are not reported as successful payments, SMS deliveries or Firebase authentication.

Localization comes from the original backend translation catalog and guarded development translation bootstrap. The admin waits for the saved locale and complete English fallback before rendering routes. Multilingual/RTL behavior remains in place.

## Checks

```sh
bash scripts/verify-original-hardening.sh
bash scripts/verify-original-baseline.sh
cd .migration-backup/backend
php vendor/bin/phpunit -c phpunit-development.xml
cd ../..
node --test scripts/development/config.test.mjs \
  .migration-backup/admin/src/configs/i18next-bootstrap.test.mjs \
  .migration-backup/admin/src/configs/runtime-config.test.mjs \
  .migration-backup/admin/src/redux/slices/statistics/topProductsResponse.test.mjs \
  .migration-backup/web/config/runtime-config.test.cjs \
  .migration-backup/web/config/integrations.test.cjs
```

The backend development suite creates its own temporary SQLite file; it does not use the original `RefreshDatabase`/`migrate:fresh` test defaults or an existing application database. Frontend production builds must use explicit staging/production configuration and real deployment-owned public CAPTCHA configuration—not the local bypass. Build-only fixtures can verify compilation offline but are not release credentials.

## Staging and production boundary

This phase supplies portable configuration and fail-closed checks; it does **not** provision or access production.

- Configure deployment-owned HTTPS origins, an independent application key, exact HTTPS CORS origins and `APP_DEBUG=false`.
- Use the original production database/migration process. The development SQLite commands refuse staging/production.
- Select payment and integration modes explicitly; configure authorized sandbox/live providers separately.
- Never copy local demo credentials or development switches into a release.
- Do not commit dotenv files, compiled Laravel configuration containing credentials, SQLite files, logs, uploaded files, vendor/node_modules or client build outputs.
- A cached Laravel configuration overrides dotenv. The development launcher refuses cached configuration/custom bootstrap paths instead of accidentally accepting a stale production database target.

After engineering verification, review [the separate UI/UX modernization proposal](modernization-proposal.md). No broad visual redesign is included in this phase.

Stage 1's isolated visual system, role-aware navigation and authentication
proposals are documented in [Stage 1 delivery](stage-1-delivery.md). These
Canvas previews do not replace native login or authorize a broad redesign.