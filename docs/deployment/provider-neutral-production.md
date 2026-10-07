# Provider-neutral production guide

This is configuration and deployment guidance, **not** an executable installer
or authorization to provision services. It preserves the current applications,
dependencies, authentication and financial containment.

Ubuntu 24.04 LTS is the immediate target. Ubuntu 26.04 compatibility has **not**
been validated. Express compatibility through the requirements below rather
than assuming a distribution's default packages satisfy them. Hostinger is a
planned provider, not a runtime dependency.

## 1. Current application prerequisites

| Component | Requirement |
|---|---|
| Backend CLI and PHP-FPM | PHP 8.4, matching CLI/FPM extensions and configuration |
| Backend framework | Existing Laravel 12 dependencies from `composer.lock`; no framework upgrade |
| Dependency tooling | Composer 2; Yarn Classic (1.x) for Web/Admin |
| Frontend build and Next runtime | Node.js 24; existing Web/Admin Yarn locks |
| Database | MySQL 8.x/InnoDB; accepted native first-install evidence used MySQL 8.0.42, not every 8.x release |
| Reverse proxy/static hosting | Nginx with deployment-owned configuration and HTTPS |
| Mobile, separately | Compatible Flutter/Dart/native SDKs for the existing source and `pubspec.lock` |

The current backend manifest and locked runtime packages require these PHP
extensions, including modules normally compiled into PHP:

`ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `iconv`, `json`,
`libxml`, `mbstring`, `openssl`, `pcre`, `session`, `simplexml`, `sodium`,
`tokenizer`, `xml`, `xmlreader`, `xmlwriter`, `zip`, `zlib`.

Also provide PDO and `pdo_mysql` for the selected MySQL backend. PDO SQLite is
needed for the existing **local development** path, not to replace production
MySQL. Do not bypass Composer platform checks or change locked versions to fit
an unsuitable host. Operational tools/extensions for any later approved worker
or backup setup belong to that setup's separately approved requirements.

## 2. Locked dependencies and build boundaries

Run native commands from the maintained projects, not the root scaffold.
For a separately authorized production dependency installation, the backend
entrypoint is:

```sh
cd .migration-backup/backend
composer install --no-dev --no-scripts --no-plugins --no-interaction \
  --prefer-dist --optimize-autoloader
```

Suppressing hooks makes their absence explicit; approved release preparation
must arrange Laravel package discovery and the public storage link deliberately.
The actual unsuppressed Composer hooks currently perform package discovery and
`storage:link`. They do **not** automatically execute `TranslationSeeder`,
`MissingTranslationsSeeder` or `UnitSeeder`.

Web/Admin use `yarn install --frozen-lockfile --ignore-scripts --non-interactive`
in their respective source directories. Keep frontend build dependencies:
installing only production dependencies before compiling would omit build tools.
Preserve all existing lockfiles. Do not use `composer update`, dependency
upgrades or lockfile regeneration as deployment preparation.

Frontend build entrypoints, once correct configuration exists:

```sh
yarn --cwd .migration-backup/web build
yarn --cwd .migration-backup/admin build
```

The root `agendaally:build:*` commands dispatch these same native builds without
requiring root pnpm installation. The Admin build's existing tracked timestamp
metadata updates are expected behavior, not a proposed source refactor.

## 3. Production configuration: timing and ownership

The [example directory](examples/) contains documentation templates only:

- `backend.env.production.example`
- `web.env.production.example`
- `admin.env.production.example`

They are deliberately incomplete, contain no credentials and must not be used
unchanged for a production build or runtime. Preserve the local application
`.env.example` files. Arrange private runtime configuration outside version
control, with appropriate ownership and access permissions.

### Backend: server-only environment

Use ordinary process environment variables or a private Laravel `.env` supplied
by the deployment. A private file may be stored outside releases and linked or
supplied through a deliberately configured service mechanism; this guide does
not install one. Do not assume arbitrary `*_FILE` variables are supported.

Required settings include:

- `APP_ENV=production`, `APP_DEBUG=false`, stable deployment-owned `APP_KEY`.
- `APP_URL` and `LARAVEL_BACKEND_URL`: externally reachable API/backend origin.
- `CUSTOMER_STOREFRONT_URL`, `VENDOR_ADMIN_URL`: actual frontend origins.
- `IMG_HOST`: browser-reachable base URL for uploaded media, with the expected
  trailing slash; normally the backend origin unless media is deliberately hosted
  elsewhere.
- `DB_CONNECTION=mysql`; `DB_HOST` / `DB_PORT` or an explicit `DB_SOCKET`;
  `DB_DATABASE`, least-privilege `DB_USERNAME`, private `DB_PASSWORD`.
- Exact `CORS_ALLOWED_ORIGINS`, with scheme and any non-default port, not `*`.
- `FILESYSTEM_DRIVER`, the actual selector consumed by this application's
  `config/filesystems.php`. `FILESYSTEM_DISK` is not its default-disk selector.
- `CACHE_DRIVER`, `SESSION_DRIVER`, cookie settings and stable persistent paths.

Choose the session/cache drivers explicitly. The example uses file storage;
database/Redis drivers require their corresponding approved operational setup.
Set `SESSION_SECURE_COOKIE=true` for HTTPS. Prefer a host-only cookie by leaving
`SESSION_DOMAIN` unset unless cross-subdomain use is actually required. Do not
invent a shared cookie domain or change the established token/session design.
The current configuration retains its existing HTTP-only/SameSite settings.

Laravel cached configuration can contain secrets and supersedes later dotenv
changes. Build/rebuild caches only with the approved deployment environment,
protect their files and reload long-lived processes when configuration changes.
Do not generate, rotate or replace existing authorities during routine cleanup.

### Storefront: build-time public configuration and server runtime

- `NEXT_PUBLIC_APP_ENV=production`.
- `NEXT_PUBLIC_DEVELOPMENT_MODE=false`.
- `NEXT_PUBLIC_BASE_URL=https://<api-host>/api/` (exact origin-root `/api/`;
  services preserve `/api/v1/`).
- `NEXT_PUBLIC_WEBSITE_URL`, `NEXT_PUBLIC_ADMIN_PANEL_URL`: origin-root HTTPS URLs.
- `NEXT_PUBLIC_IMAGE_URL`: approved HTTPS storage/media base URL.

`NEXT_PUBLIC_*` values are public browser configuration baked into the build;
changing them generally requires rebuilding, not just restarting Next.
The native Next launcher separately reads runtime `HOST` and `PORT`. For a
reverse-proxied service, use an explicitly selected loopback host and private
port. The existing development heap cap does not apply to production startup.

Keep complete repository relative paths: frontend configuration imports
`scripts/development/dev-api-target.cjs`, even though the development proxy is
not enabled in production. Do not extract only the frontend directory and
silently drop its required shared helper.

### Admin: static build-time configuration

- `VITE_APP_ENV=production`, `VITE_DEVELOPMENT_MODE=false`.
- `VITE_API_ORIGIN`: HTTPS origin only, without `/api/`; do not also set deprecated
  `VITE_BASE_URL`.
- `VITE_STOREFRONT_URL`, `VITE_ADMIN_PANEL_URL`: origin-root HTTPS URLs.
- `VITE_RECAPTCHA_DISABLED=false`; a valid, deployment/domain-owned
  `VITE_RECAPTCHA_SITE_KEY` is required for the production build.

`VITE_*` values are public and compiled into static assets. Serve `admin/build/`
through Nginx; do not deploy the Vite development or preview server as the Admin
production server. Preserve existing file-serving and host/CAPTCHA controls.

### External integrations and development flags

Production must not inherit `DEVELOPMENT_MODE=true`,
`AGENDAALLY_DEVELOPMENT_DATABASE=true`, frontend development flags,
`AGENDAALLY_DEV_API_TARGET`, SMTP test enablement or CAPTCHA bypasses.

Keep payment/provider execution disabled until separately approved. Configure
Firebase, Maps, email, SMS and optional provider SDKs only according to their
accepted policy and an approved release scope. Their absence is not proof that
every production default is disabled: production email/SMS defaults differ from
local development. The examples therefore leave email/SMS release modes as
explicit unresolved placeholders rather than inventing a bypass.

Never put private application, database, CAPTCHA-secret, SMTP, recovery or
payment credentials into `NEXT_PUBLIC_*` or `VITE_*`. Public site keys/SDK
identifiers belong there only when their integration is approved. Existing
defensive `REPLIT_DEPLOYMENT` checks remain intact as additional containment.

## 4. Neutral production service layout

Illustrative paths, identities and ports below are not configured services:

| Responsibility | Proposed ownership/layout |
|---|---|
| Releases | `/srv/agendaally/releases/<release>/` containing the complete maintained layout; `/srv/agendaally/current` selects a release |
| Shared state | `/srv/agendaally/shared/` for private environment, persistent Laravel storage and approved operational state |
| Deploy identity | Dedicated unprivileged deployment identity writes releases; no broad runtime write access to source |
| PHP-FPM identity | Dedicated application identity/pool; reads code/environment and writes only approved Laravel runtime paths |
| Next identity | Dedicated unprivileged service identity; native `yarn start`, `HOST=127.0.0.1`, explicitly chosen private `PORT` (for example 3002) |
| Nginx identity | Distribution-managed unprivileged web identity; reads public/static files and forwards authorized requests |
| MySQL | Managed database service or external approved MySQL endpoint; separate least-privilege runtime account |
| Supervision | Distribution-supported process supervision such as systemd, with explicit environment, working directory, restart and graceful-stop policy |

Nginx terminates HTTPS for separately configured origin-root hosts:

- API host: document root is Laravel's **`public/`**, not the repository or
  backend root; dynamic requests go to the selected PHP-FPM socket/endpoint.
- Storefront host: reverse proxy to the supervised Next service, forwarding
  the appropriate host/protocol headers.
- Admin host: serve the generated static build with SPA navigation fallback.
- Media: serve the deliberately configured public storage path; never expose
  private storage, dotenv, logs, databases, `.local` or framework caches.

Use the actual distribution Nginx MIME configuration path, FPM pool identity and
socket path. Do not reuse executable-relative MIME discovery, self-signed
localhost certificates or hard-coded rehearsal ports from `scripts/staging/`.
Proxy trust and cookie/origin configuration must match the actual HTTPS boundary,
without broadly trusting arbitrary forwarded headers.

### Persistence and writable paths

Preserve Laravel `storage/` across release switches: uploads under
`storage/app/public`, configured private application data, file sessions/cache
and logs must not disappear when a release directory is replaced. Ensure the
`public/storage` link targets the intended persistent public storage.

`storage/` and release-specific `bootstrap/cache/` need narrowly scoped write
access for the actual PHP-FPM/deploy identities. Use least-privilege ownership
and group permissions, not blanket `777` or a guessed `www-data` owner.
Environment files and secret-bearing compiled configuration require restricted
read access. Do not make the entire checkout writable by the web identity.

Do not copy private validation databases into production. Preserve Product
NULL-media policy and the approved Service assets/relationships; filesystem
deployment must not fabricate images or fetch replacement media.

## 5. Deployment authority and closed validation

The MySQL first-install track is **closed with PASS_FIRST_INSTALL**. The
canonical manifests and accepted private evidence remain unchanged and are not
instructions for ordinary deployment. Do not rerun their synthetic bootstrap,
first-install, migration, recovery, media, JSON, financial or email campaigns.

An ordinary future release needs an approved operational plan for configuration,
locked dependencies, storage, framework discovery/caches, build assets and
service reloads. Schema changes require an explicit release-specific migration
decision; do not blindly run historical migrations or seeders. A new production
installation also needs a separately approved provisioning/data plan, not a
replay of disposable acceptance fixtures.

Worker/scheduler activation is separate: do not start a broad legacy schedule
or external-action worker merely because a service template exists. Preserve
the selected-MVP operational scope and reconciliation/containment requirements.

This cleanup does not provision Ubuntu, MySQL, PHP-FPM, Nginx, certificates,
service users, provider accounts, secrets or DNS. Final clean-clone/build
validation, private GitHub publication and VPS preparation remain separately
authorized work.
