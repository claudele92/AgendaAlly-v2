# AgendaAlly

AgendaAlly contains a Laravel marketplace API, Next.js customer storefront,
React/Ant Design Vendor/Admin frontend and Flutter customer application.
Service booking and product commerce remain part of the maintained product.

## Maintained applications

| Application | Source | Native toolchain |
|---|---|---|
| Laravel backend | `.migration-backup/backend/` | PHP 8.4, Composer 2 |
| Next.js storefront | `.migration-backup/web/` | Node.js 24, Yarn Classic |
| Vendor/Admin frontend | `.migration-backup/admin/` | Node.js 24, Yarn Classic / Vite |
| Flutter customer application | `.migration-backup/customer_app/` | Flutter / Dart and the existing mobile build tooling |

The historical `.migration-backup` name does **not** mean these are disposable
copies. Keep this layout and its relative imports; do not deploy the unrelated
Express scaffold as the Laravel backend.

Replit is optional development tooling, not an application runtime or deployment
requirement. Hostinger is the planned first VPS provider, not an application
dependency. The production layout is provider-neutral.

## Native installation and build entrypoints

Use the existing Composer and Yarn lockfiles, without updating dependency
versions. Root pnpm installation is **not** required for Laravel, Web or Admin.
For separately authorized setup, the native dependency installation entrypoints
are:

```sh
cd .migration-backup/backend
composer install --no-scripts --no-plugins --no-interaction --prefer-dist
cd ../web
yarn install --frozen-lockfile --ignore-scripts --non-interactive
cd ../admin
yarn install --frozen-lockfile --ignore-scripts --non-interactive
```

These intentionally suppress installation hooks. They do not provision a
database, generate credentials, discover Laravel packages or create storage
links. Those operational steps require deployment-owned configuration and a
separately approved procedure. See the
[production guide](docs/deployment/provider-neutral-production.md) for production
dependency flags and release responsibilities.

Once native dependencies and valid build-time configuration exist, the root
commands are:

```sh
npm run agendaally:build:web
npm run agendaally:build:admin
npm run agendaally:build       # Web, then Admin
npm run build                 # alias for the AgendaAlly frontend builds
```

`npm run` here only dispatches scripts; it does not require `npm install` at the
root. The underlying commands remain `yarn --cwd .migration-backup/web build`
and `yarn --cwd .migration-backup/admin build`.

Laravel does not have a frontend-style compilation command. Its release
preparation uses locked Composer dependencies and explicitly managed framework
discovery/caches. Neither frontend build migrates or seeds the database.
The Admin build retains its existing build-date metadata behavior, including
updates to its own `package.json` and `public/meta.json`.

The storefront's native production startup is `yarn start` in its source
directory, with deployment-owned `HOST` and `PORT`. Admin produces static
`build/` assets; Vite development/preview servers are not the production server.

For Flutter, preserve the existing customer source, `pubspec.lock` and
`scripts/build-mobile.mjs` configuration handling. Mobile SDK installation and
APK/App Bundle/iOS builds are separate from VPS web deployment.

## Development versus production

- [Portable development](docs/development/README.md): local-only setup and
  guarded development commands, including optional `--replit`.
- [Provider-neutral production](docs/deployment/provider-neutral-production.md):
  prerequisites, configuration, storage and service responsibilities.
- `scripts/development.mjs` and `scripts/post-merge.sh` are development tooling,
  **not** production installers.
- `scripts/staging/` is the existing local rehearsal implementation, **not**
  a ready-made VPS service configuration.

Production builds require HTTPS application URLs and must not inherit local
development flags or CAPTCHA bypasses. Existing local `.env.example` files
remain local examples. Production guidance contains placeholders only.

## Optional workspace and design tooling

`artifacts/api-server/`, `lib/`, the root pnpm workspace and
`artifacts/mockup-sandbox/` support separate scaffold/design tooling. They are
not the maintained AgendaAlly production applications.

```sh
pnpm run build:workspace       # the former root scaffold build
pnpm run typecheck:workspace   # alias for the existing workspace typecheck
```

The original `typecheck` and `typecheck:libs` commands remain available.
`.replit`, `.replitignore`, `replit.md` and optional preview plugins are retained;
none is required to serve the maintained applications on a VPS.

## Evidence and safety boundaries

Historical audit/preview documents, canonical deployment manifests and accepted
proofs are preserved, not rewritten to remove provider names. Private `.local/`
evidence stays private and ignored; preservation is not permission to commit it.
Do not commit private dotenv files, credentials, databases, uploads or generated
runtime/build output.

The accepted MySQL first-install validation track is **closed**. Do not replay
synthetic bootstrap, migration, media, recovery, JSON, financial or email
campaigns during ordinary deployment. Canonical reference data, accepted
security controls, Product NULL-media policy and approved Service media remain
unchanged.

The final clean-clone/build validation, private GitHub publication and later VPS
preparation each require separate authorization. This documentation does not
authorize those operations.
