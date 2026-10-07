# Original AgendaAlly frontend restoration

Date: 30 September 2026 (America/Chicago).

Follow-on provisioning status (1 October 2026):
[`agendaally-connected-preview-status.md`](agendaally-connected-preview-status.md).
The build-restoration results below remain the historical evidence; they do not
claim completion of the subsequently approved connected-preview work.

## Result: frontend builds restored; connected previews blocked

Both original frontend dependency trees and asset-complete production builds have been restored in isolated, ignored runtime copies. The original application code, dependency manifests/locks, framework configuration, branding, UI, API contracts and database schema were not changed.

**The requested connected Replit previews have not been configured.** The earlier backend validation is not an HTTP-ready development environment: its temporary runtime/database were discarded, domain tests use mocked authentication and a deliberately partial in-memory schema, and no persistent synthetic HTTP database or login/token setup exists.

This is a backend-environment prerequisite, not a demonstrated frontend architectural failure. Creating an HTTP-ready environment might be possible without changing the original applications; it has not been attempted because it would require extending the synthetic database/setup beyond the supplied baseline under this request's no-schema-change boundary.

## Customer storefront: passed

- Runtime copy: `.local/agendaally-preview/web`.
- Dependencies restored from the original Yarn lock with Yarn 1.22.22, frozen-lockfile mode and installation scripts disabled.
- Next.js **16.0.10**, React **19.2.3** retained.
- Original `yarn run build` / `next build`, using default Turbopack, completed successfully. No webpack fallback, framework replacement, disabled type checking or configuration workaround was used.
- Wall time: **47 seconds**; sampled aggregate process CPU: 85.54 seconds; peak sampled aggregate RSS: 2,277,096 KiB (approximately 2.17 GiB).
- Compilation, TypeScript, page-data collection and static-page generation passed.
- All original storefront assets were restored, including the 112-file public asset tree. `.env*`, source dependencies/caches and symlinks were excluded.
- Original and runtime `package.json` and `yarn.lock` match.

### Timeout diagnosis

The preceding baseline attempt ended at a 300-second tool timeout after entering the optimized-production-build stage; it did not record a complete build result or a reproducible compiler error.

This attempt captured bounded execution, process resource samples, stage transitions and a final exit code. It completed with exit code 0 using the original default compiler. **The prior timeout did not reproduce; its root cause cannot be established from the earlier incomplete evidence.**

Nonfatal diagnostics include multiple lockfiles causing Turbopack to infer the workspace root, the middleware convention warning, stale browser-mapping data, and pre-existing peer-version warnings. These did not prevent this build. No root/config/dependency changes were made to suppress them.

Evidence:

- `.local/agendaally-preview/logs/build-default.log`
- `.local/agendaally-preview/logs/build-default.status`
- `.local/agendaally-preview/logs/build-default-stages.log`
- `.local/agendaally-preview/logs/build-default-resources.tsv`

## Admin/vendor application: passed

- Runtime copy: `.local/agendaally-preview/admin`.
- Original Yarn lock restored with scripts disabled; Vite **7.3.0**, React **18**, Ant Design **4.20.6** retained.
- Asset-complete Vite production build passed: 7,285 modules transformed, 667 output files, approximately 20 MB output, 44.67-second reported build time.
- The original `update-build.js` ran only in the runtime copy, updating its intended `buildDate` / public metadata. The original manifest is untouched; runtime dependency definitions and lock remain unchanged.
- Build retained the application-network guard while running the original build steps; the original package build command overwrites `NODE_OPTIONS`, so the guarded invocation called the same update script and Vite build steps explicitly. No application/configuration change was needed.
- Nonfatal diagnostics: large-chunk advisory and tsconfig-paths warnings involving unrelated template tsconfigs.

### Missing image resolved from the original source

The previous source-only copy intentionally omitted binary assets. The missing file is present at:

`.migration-backup/admin/src/assets/images/user.jpg`

The complete original asset tree was restored. The resulting output includes byte-identical original user/shop/courier images:

- `build/assets/user-0Pl6KT5L.jpg`
- `build/assets/shop-DpGjsCs1.png`
- `build/assets/courier-Zu-UzA4c.png`

No replacement image, invented logo, altered import or branding redesign was used.

Evidence: `.local/agendaally-preview/logs/admin-build.log`.

## Safety and verification limits

- Builds used cleared environments, synthetic local API/website values and the application-network guard in `scripts/block-original-client-network.cjs`.
- Package downloads were allowed for dependency restoration. No production API credentials, production database, live payment/refund/payout, SMS/email or external provider operations were used.
- Successful production compilation does **not** establish browser functionality, API integration, authenticated sessions, appointment/order workflows or live provider behavior.
- No original preview workflows/artifacts were created, and the existing scaffold previews are not claimed as AgendaAlly.
- Existing source/configuration files remained unchanged. Runtime copies, dependency trees and logs are under the already-ignored `.local/` tree.
- An initial Yarn command briefly targeted the workspace root before its working directory was corrected; no tracked files changed, but ignored root dependency/cache metadata may have been touched. The shared dependency cache was not destructively replaced.

## Exact backend blocker

The inspected baseline establishes useful service/resource compatibility, not a persistent frontend-facing API:

1. `scripts/probe-original-boot.sh` creates an owned temporary runtime and deletes it on exit.
2. The referenced `/tmp/agendaally-hardening-runtime/vendor/autoload.php` is absent in the current workspace. Earlier temporary dependency restoration did not carry into this environment.
3. `tests/Hardening/IsolatedTestCase.php:18–55` creates a bare Laravel Application and SQLite `:memory:` connection with selected providers—not the complete original HTTP application.
4. `tests/Baseline/OriginalDomainBaselineTest.php:491–606` defines 58 deliberately partial synthetic tables, not the full migrated original schema.
5. Domain authentication is mocked (`OriginalDomainBaselineTest.php:364–374`); the baseline schema does not include Sanctum `personal_access_tokens`. A separate account-reset test defines its own schema, but it is not a reusable HTTP database.
6. The committed JSON fixture contains booking/cart/order/refund resource outputs, not a database snapshot with HTTP users, tokens and complete settings/configuration.

The Next root layout consumes settings, languages, currencies, geography and translations over the API. Admin sign-in and role-dependent screens need real Laravel authentication/middleware and database state. Serving either application against a dead loopback URL, an empty database or a fabricated response proxy would not meet the requested goal.

## Next approval boundary

To complete the connected previews, first explicitly approve provisioning an **HTTP-ready, disposable synthetic Laravel environment** with the original schema requirements, necessary development fixtures and real local authentication. Retain the original backend and API contracts; do not substitute Express or invent missing tables merely to suppress errors. Do not use production data, destructive migration replay or external provider operations.

Then configure separate origin-root customer/admin previews. Both original frontends currently assume root-relative routes/assets; preserve them through separate previews rather than merging the applications or introducing a partial subpath rewrite.

**Stopped here. No Inertia, Next-to-Vite migration, schema extension, feature change, UI redesign or backend workaround was introduced.**