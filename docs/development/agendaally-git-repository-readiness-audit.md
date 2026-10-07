# AgendaAlly — full-project Git repository readiness audit

**Date:** 2026-10-06  
**Decision:** Existing repository; **not ready for a milestone push without cleanup and review**.  
**Scope:** Read-only repository/source/history inspection. No application, dependency, database, ignore-rule, Git configuration, commit, push, remote, deployment or provider changes. This report/export is the requested documentation exception.

## 1. Current Git state

| Item | Observed state |
|---|---|
| Repository | EXISTS; root `/home/runner/workspace` |
| Current branch | `main` |
| Reachable history | 271 commits across local refs |
| Tracked files | 6,667 |
| Working tree at audit start | Tracked tree clean; one untracked file: this audit's uploaded instruction |
| Candidate inventory | 53,336 files; 46,669 ignored candidates, excluding pruned dependency/SDK/cache directories |
| Nested project Git repositories | None found in the inspected project/preview/release trees |
| GitHub remote | `origin`: `https://github.com/claudele92/AgendaAlly` |
| Other configured remotes | `gitsafe-backup`: `git://gitsafe:5418/backup.git`; seven `subrepl-*` SSH workspace remotes |
| Locally recorded origin comparison | `main` ahead 133, behind 0 versus `refs/remotes/origin/main` |
| Remote visibility / current remote contents | UNKNOWN; no network query, fetch, push or remote authentication performed |

The other remotes are existing backup/isolated-workspace transport entries, not additional authoritative AgendaAlly source trees. Their names are `subrepl-buwttggf`, `subrepl-cnpmd4p0`, `subrepl-cvvltnfc`, `subrepl-msenhspb`, `subrepl-r5po443h`, `subrepl-wmb3kzq7` and `subrepl-zr5awqye`; they use the existing SSH workspace transport. Do not remove or modify them as part of this audit.

**No initial `git init` is needed.** Existing history already preserves much of the completed work. Local remote-tracking refs are not proof of today's GitHub visibility or contents.

## 2. Authoritative source map

| Component | Current authoritative location | Tracked coverage / interpretation |
|---|---|---|
| Backend/API | `.migration-backup/backend/` | 1,969 files; Laravel REST `/api/v1`, not the root Express scaffold |
| Customer Web | `.migration-backup/web/` | 1,114 files; Next.js storefront |
| Admin/Vendor portal | `.migration-backup/admin/` | 1,852 files; React/Ant Design/Vite |
| Customer Mobile | `.migration-backup/customer_app/` | 884 files; existing Flutter app, including Phase 1 repair/tests |
| Database source | Backend `database/migrations/`, `development/migrations/`, `isolated-migrations/`, `factories/`, `seeders/`; reviewed bootstrap source under Backend `app/` | 229 main migration files; one isolated migration; source, not normal database contents |
| Tests | Backend `tests/` and `phpunit*.xml`; Mobile `test/`; Web/Admin colocated regression tests; `scripts/` test/probe code | 154 Backend test-tree files; two Mobile test files containing the 36-test focused suite |
| Build/configuration | Component manifests/lockfiles, sanitized `.env.example`, Next/Vite/Flutter/native configuration; `scripts/development.mjs`, `scripts/build-mobile.mjs`, `scripts/staging/` | Preserve source configuration; exclude generated credential-bearing configuration |
| Documentation | `replit.md`, `docs/audit/`, `docs/development/`, `docs/security/`, `docs/staging/` | Current development instructions and milestone reports |
| Evidence | Curated `docs/development/evidence/`, `reports/`, `deliverables/`, `exports/` | Mixed source, summaries and binary captures; not blanket-approved |
| Shared contracts | Laravel request/resource/model contracts and existing client consumers | Root `lib/` is scaffold code, not the authoritative AgendaAlly domain schema |
| Design prototypes | AgendaAlly-related `artifacts/mockup-sandbox/` and `deliverables/vendor-calendar-proposal/` | Preserve approved design work separately from production applications |

`replit.md` and `docs/development/README.md` explicitly identify the four `.migration-backup/` application trees as active maintained source. Their historical name reflects the scaffold/import arrangement; it does **not** mean they are obsolete backups. Git history includes both their initial import and subsequent Mobile repairs. **Do not ignore, move or rename the entire `.migration-backup/` directory.**

Separately classify `.local/agendaally-preview/`, `.local/staging-mvp/releases/*`, build-qualification copies and runtime backups as generated/historical snapshots, not authoritative applications. `artifacts/api-server`, root `lib/` and the original root pnpm scaffold must not be represented as the AgendaAlly Laravel backend. `.migration-backup/docs/` and imported top-level deployment/demo notes are legacy context, subordinate to current reports/instructions.

## 3. A–M source-control classification

| Class | Concrete material | Recommendation |
|---|---|---|
| A — COMMIT application source | Four active application trees, authored assets, route/resource/model/client code | Preserve; omit runtime/generated subtrees |
| B — COMMIT tests | Existing Backend/Mobile/colocated tests and safe `scripts/` probes | Preserve assertions and synthetic provenance |
| C — COMMIT build/deployment configuration | Manifests/lockfiles, sanitized templates, native project configuration, portable launch/build scripts | Preserve; not `.env`, signing material or compiled configuration |
| D — COMMIT migration/schema source | Backend migration/development/isolated migration source, factories and reviewed seed definitions | Preserve; never exclude all SQL/database-related source by extension |
| E — COMMIT durable documentation | Current technical reports, README, architecture/security decisions | Preserve after contact/credential review |
| F — COMMIT safe acceptance evidence | Synthetic test scripts, boolean outcomes, checksums, source/schema/table fingerprints, sanitized summaries | Curated allowlist, not the whole evidence tree |
| G — DO NOT COMMIT secrets | Runtime `.env`, historical native credential configuration, TLS/private/signing keys, credential-bearing generated config | Exclude; historical exposure requires separate handling |
| H — DO NOT COMMIT normal database data | Normal SQLite database/journals, MySQL datadirs/binlogs, runtime backups/dumps | Exclude regardless of whether encrypted or called “evidence” |
| I — DO NOT COMMIT private financial/identity evidence | Real receipts, private uploaded files, real account/contact captures, live signed URLs/auth artifacts | Keep in access-controlled storage, not source control |
| J — DO NOT COMMIT generated output | `.next/`, `build/`, `dist/`, generated APK/AAB, compiled caches, coverage, runtime logs | Exclude; review existing standalone exports separately |
| K — DO NOT COMMIT SDK/tool cache | Android SDK/NDK/JDK, Flutter/Pub/Gradle/tool caches, dependency-managed `vendor/` and `node_modules/` | Restore from manifests/toolchain instructions |
| L — DO NOT COMMIT temporary/backup data | `.local/` runtime state, old release copies, temporary DBs and downloaded archives | Do not wholesale add; extract indispensable safe helper source separately |
| M — OWNER DECISION | Imported licensing/assets, scaffold retention, screenshots, bundled evidence archives, selected agent memory | Decide visibility, provenance and durable value explicitly |

Important clone gap: ignored `.local/booking-forward/` contains 45 PHP/Node helper files. `scripts/staging/bounded-b1.php` requires its `execution-support.php`; `scripts/staging/final-preservation.mjs` calls its `protected-state.php`; the configured native acceptance workflow uses `native-http.php`. Other ignored certification helpers exist under `.local/mvp-mysql/`, `mvp-wallet/` and `wallet-certification/`. **Do not unignore `.local/` wholesale.** A later approved cleanup should preserve only required safe source helpers in a tracked tooling/test location and update references.

## 4. Secret/credential safety findings

Values were never printed. Candidates are not claimed to be valid/live without provider-side verification.

| Path | Category | State | Safe to commit | Recommended action |
|---|---|---|---|---|
| `.migration-backup/customer_app/ios/Flutter/Dart-Defines.xcconfig` | Historical native configuration with non-placeholder credential-shaped settings; Google API-key pattern confirmed | Absent/untracked in current tree, but retained in reachable history including local `origin/main` history | NO | Owner/provider review; consider rotation/restriction; decide authorized history treatment before wider publication |
| Same historical file | Maps/Firebase, websocket, routing, PayFast passphrase/merchant key and Facebook client-token settings | Historical tracked blob | NO / OWNER REVIEW | Verify which were real/active; do not assume current deletion revoked them |
| `.local/agendaally-preview/web/scripts/run-original-build.sh` | Old Google API-key pattern in preview build source | IGNORED | NO | Keep excluded; include in credential-rotation review |
| `.migration-backup/backend/.env` | Runtime environment; nonempty application encryption-key candidate | IGNORED | NO | Continue exclusion; provision independent keys elsewhere |
| `.migration-backup/web/.env`, `.migration-backup/admin/.env` | Local runtime configuration | IGNORED | NO | Keep excluded even when providers are disabled |
| `.local/staging-mvp/tls/server.key` | TLS private-key material | IGNORED | NO | Keep local/private; do not copy into a source checkpoint |
| Component `.env.example` files | Templates inspected for configured secret candidates | TRACKED | YES for reviewed current templates | Preserve empty/placeholders; `Bearer` token type is not a credential |
| Runtime-config/transport tests containing credential-bearing URL strings | Deliberately synthetic rejection fixtures | TRACKED | YES where synthetic provenance is preserved | Do not mistake negative tests for real credentials |
| `.local/` approval, key/state and private-log material | Runtime authority/private configuration | IGNORED | NO / OWNER REVIEW | Exclude from any curated helper promotion |

The historical Google key pattern is reachable from both `main` and the **locally recorded** `origin/main`. That is evidence of historical tracking, not proof that GitHub is public or that a key is usable today. Do not rewrite shared history or rotate credentials during this audit.

Inspection covered 7,039 current candidate text files (~55.8 MB) and 7,852 reachable historical text-candidate blobs (~74.5 MB). History inventory covered 12,472 reachable objects/8,107 blobs. Exclusions: unreachable/dangling history, external remotes, SDK/dependency trees, large files ≥10 MB, and binary/compressed content except selected current ZIP text members. This is a bounded credential/data-readiness review, **not a guarantee of secret absence or a new full dependency/SAST/privacy scan**.

## 5. Database safety

The normal database is `.migration-backup/backend/database/development/agendaally.sqlite`, currently ignored. Temporary SQLite copies/journals and `.local/staging-mvp/mysql*` runtime storage are also ignored. None should be source-controlled.

The audit used read-only/query-only SQLite access and rolled-back read transactions. It recorded deterministic row-serialization and schema fingerprints for **all 214 tables**, not merely counts. Before/after preservation is identical.

Safe source includes migration/schema/bootstrap definitions, reviewed factories and synthetic seed definitions. The tracked `backend/resources/lang/translations_en.sql` is translation-catalog source, not a normal application database dump. Retain its source role rather than applying a blanket `*.sql` ignore.

Historical migration replay is not automatically safe. Current development instructions explicitly prohibit blindly running `migrate:fresh` or using unknown/production databases. A source checkpoint is not a database backup, data-migration approval or recovery-key backup.

## 6. Financial and identity evidence safety

Preserve sanitized manual refund/payout, permission-boundary and private-receipt **reports and reproducible scripts**. The private-receipt report documents locally generated synthetic documents, independent identities and no money movement; its retained summaries/manifests are different from real receipts.

Current ZIP inventories:

| Already tracked archive | Entries | Determination |
|---|---:|---|
| Email operational closure evidence | 27 | Text members inspected; screenshots/binary/privacy and reuse remain OWNER REVIEW |
| Email system audit evidence | 52 | Same; no blanket publication approval |
| MySQL MVP evidence | 70 | Primarily technical runtime summaries; curate and retain reproducible helper source |
| Payment engine proof evidence | 114 | Technical captures; verify synthetic provenance and curate |
| Android auth qualification evidence | 36 | Fingerprints/aggregate outcomes/helpers; no native credentials or raw normal DB in inspected contents |
| Mobile Phase 1 evidence | 20 | Technical auth/session proof; reviewed text, binary/embedded packaging still deliberate |

No database file/dump entry was found in these six archive inventories. Selected text-member inspection found no detector hits for long bearer values, private-key material or signed-capability values. This does **not** visually clear every screenshot or validate all possible encoded content.

Three tracked reports contain non-placeholder contact-address candidates: `docs/audit/agendaally-preview-localization.md`, `docs/development/mtn-cameroon-onboarding-inquiry.md`, and `docs/development/payment-phase3b-mtn-cameroon-readiness.md`. They may be public business/provider contacts; owner review must distinguish these from private identity data before publication. Values are deliberately omitted here.

## 7. Generated/temporary material

Exclude dependency installs, `.next`, Flutter builds/cache, Gradle caches, SDK/JDK/NDK, native acceptance database datadirs, private logs, release snapshots and recovery archives. Do not delete them now.

`exports/` HTML may embed a ZIP in base64: review the embedded payload as well as visible prose. `attached_assets/` mixes user instructions and demo photos, not just source assets. Keep only deliberately required, provenance-reviewed assets; do not publish the entire attachment history automatically. Approved synthetic calendar design screenshots can be retained separately from private operational captures.

## 8. Ignore-rule assessment

Current root/child rules correctly cover `.env`/variants with example exceptions, SQLite/journals, root `.local`/`.cache`, dependency directories, relevant Laravel storage, Next output and Mobile build/native signing configuration.

**Not sufficient as a portable publication policy:**

- `*.db` is not generally excluded (only DB WAL/SHM variants are).
- Root-level private keys/keystores and `key.properties`/`local.properties` are not generally excluded; the Customer Android child rules protect its known paths only.
- Generic `build/`, private evidence/dumps and signing/service-account locations outside existing component paths need policy.
- `.config/`, broad `*.log` and `.agents/memory/user/` currently rely on machine-global **`/etc/.gitignore`**, not portable repository rules.
- Child rules exclude some generated registrants, Gradle launch files and workflow source; document reconstruction rather than blindly adding generated/native credential files.
- `.local/` hides some indispensable test/helper source as well as data.

Proposed later rules should cover local runtime DBs/journals, private keys/signing/service-account files, logs, caches and private evidence locations. Use explicit safe-evidence exceptions where necessary. Do not blanket-ignore migration/schema SQL, public certificates, authored fixtures or all `.migration-backup/`. Ignoring a path never untracks an existing file or removes historical content. **No ignore rules were changed.**

## 9. Clean-clone reproducibility

| Component | Result | Source/prerequisites and remaining gap |
|---|---|---|
| Backend | PARTIAL | Laravel 12.46.0 lock; PHP 8.4, Composer 2 and required extensions; owned SQLite development DB / MySQL 8 target. Portable setup is documented, but some accepted native/certification paths require ignored helpers. Production schema/recovery are not certified by cloning. |
| Web | PARTIAL | Next 16.0.10 / React 19.2.3; Node 24, Yarn Classic, locked restore, controlled API/media origins. Source and offline font/build metadata are present; fresh clone/build not executed here; selected staging snapshots are runtime artifacts. |
| Admin | PARTIAL | React 18 / Ant Design 4.20.6 / Vite 7.3 range with Yarn lock. Node/Yarn and reviewed env templates present. Legitimate production CAPTCHA/public configuration is separate from local bypass; fresh clone/build not executed. |
| Customer Mobile | PARTIAL | Flutter 3.38.5, Dart 3.10.4, secure storage 9.2.4, exact `pubspec.lock`; Java 17, Android API 36, build tools 35.0.0, NDK 29.0.14206865, existing Gradle 8.13/AGP 8.9.1/Kotlin 2.2.0. Auth tests/analyzer pass, but APK build/runtime remain blocked/unqualified. Native env/signing and approved provider configuration must be supplied independently. |

Observed host versions: Node 24.13.0, pnpm 10.26.1, PHP 8.4.16, Composer 2.9.2. Root pnpm manages scaffold/design tooling; it is not a substitute for the component Yarn/Composer/Flutter lockfiles.

Documented local reconstruction begins with `node scripts/development.mjs install`, `init`, `configure`, `bootstrap`, and `seed`, then `serve backend/web/admin`. These commands were **not run** during this audit. Database-mutating steps require an independently owned disposable local database. Restore Flutter with `flutter pub get --enforce-lockfile`; retain the existing wrapper/maps gate for native builds.

Required metadata: Node/Yarn/PHP/Composer extensions, Flutter/Android tool versions, authorized local DB provisioning, public origins/CORS, independent application keys, provider disablement defaults, and separate deployment-owned public/secret configuration. Missing live credentials is intentional, not a reason to commit runtime `.env`.

## 10. Durable reports to preserve

Keep these current reports (with safe scripts/sanitized summaries), while retaining historical findings as dated findings—not current production certification:

- `agendaally-mvp-readiness.md`, `agendaally-final-20-gate-audit.md`.
- `payment-architecture-audit.md`, `payment-system-full-audit.md`, `payment-phase1-financial-correctness.md`, `payment-phase2-collection-allocation.md`, `payment-phase3c-mysql-concurrency.md`, and later containment/financial-readiness reports.
- `agendaally-manual-finance-implementation.md`, `agendaally-manual-finance-ui-acceptance.md`, `agendaally-manual-finance-http-identity-acceptance.md`.
- `agendaally-private-receipt-ui-acceptance.md`.
- `agendaally-customer-mobile-audit.md`, `agendaally-customer-mobile-phase1-auth-session.md`, `agendaally-customer-mobile-android-auth-qualification.md`.
- `agendaally-calendar-acceptance-closure.md`, email/template closure reports, `agendaally-legal-policy-alignment.md`, native build qualification and production-database comparison.
- Original contract/domain/security audits; current development/build/security instructions; accepted design proposals and media provenance.

Prefer reports plus tracked safe reproduction scripts and normalized outcomes over stale snapshots, whole debug bundles or raw private captures. Preserve unresolved/blocked results honestly.

## 11. Repository architecture recommendation

**MONOREPO for the complete AgendaAlly platform**, with independent app builds/releases and restricted secret/data storage outside Git.

Reasons: existing cross-component REST/domain contracts, coordinated backend/client security changes, shared acceptance history and current root tooling. Separate repositories would increase contract/version coordination before providing a demonstrated access-boundary benefit.

Independent deployment and mobile release cadence do not require separate repositories. Use component CI paths, scoped jobs/artifacts and versioned contracts. If future contributors require truly separate source access, revisit repository separation: folder organization or CODEOWNERS does not itself enforce confidentiality.

## 12. Proposed logical layout — no moves authorized

```text
agendaally/
  apps/
    backend/
    customer-web/
    admin/
    mobile/
      customer/
      vendor/       # future, not created
      driver/       # future, not created
    pos/            # future desktop/tablet, not created
  contracts/        # actual versioned REST schemas/fixtures, not scaffold substitutes
  tooling/          # curated build/test/reproduction helpers
  infrastructure/   # deployment configuration source, never runtime keys/data
  design/           # accepted prototypes/provenance, not production claims
  docs/
    audit/
    development/
    evidence/       # curated synthetic/sanitized evidence only
```

This is a future organization proposal. **Retain the current physical paths for the immediate milestone checkpoint.** Renaming is not necessary for safe Git preservation and would require separately updating many scripts/tests/contracts. Owner decides whether unrelated API/lib scaffold source is retained in a clearly labelled legacy/tooling area.

## 13. Checkpoint strategy

Use existing `main`, not a new repository or a destructive “initial history” replacement. After approved cleanup, review one explicit milestone commit containing curated source/documentation and any safely promoted missing helpers. An optional `checkpoint/pre-mobile-phase2` branch can identify the preserved milestone; subsequent bounded work can use feature branches.

Recommended **annotated checkpoint tag**:

`agendaally-pre-mobile-phase2-baseline-2026-10-06`

Tag description must state:

- Web MVP **16/20, CONDITIONAL GO; production NO-GO**.
- Mobile Phase 1 implementation complete but overall **PARTIAL**.
- P0-01 **CLOSED** at application HTTP/logging scope; P0-02 **PARTIAL**.
- Android native qualification **BLOCKED-ENVIRONMENT**; Android-first remaining P0 **1**.
- iOS **NOT NATIVE-QUALIFIED**; Phase 2 **NOT STARTED**.

Do not use a production release tag/“v1.0 ready” claim. Resolve credential exposure/history policy before pushing a checkpoint tag, since tags expose reachable history. No branch, commit or tag was created here.

## 14. Private versus public publication

**PRIVATE: AFTER CLEANUP.** A private repository still distributes credentials/history to collaborators, integrations and backups. Review historical credentials, runtime data exclusions, selected evidence, missing source helpers and the existing remote's actual visibility/authority.

**PUBLIC: AFTER ADDITIONAL REVIEW, not safe today.** First complete private-readiness cleanup, then review licensing/redistribution rights, photos/fonts/imported code, business/security/financial reports, contacts, agent notes and all binary/embedded evidence. Composer/package declarations do not prove the owner has public redistribution rights for the whole imported application.

No remote was created, contacted or reconfigured. Do not assume `origin` is private because private is preferred.

## 15. Future clients

The owner anticipates Customer, Vendor, Driver and POS desktop/tablet clients. Reserve logical app boundaries and versioned REST contracts without creating placeholder applications or forcing all clients onto one framework.

Keep independent app release/signing pipelines, shared server-authoritative permissions and contract fixtures. The future Driver source must be audited when available; this audit does not authorize creating/replacing it, Vendor work or POS implementation.

## 16. Preservation and limitations

Before/after comparison covers **6,472 existing protected application/document/script/config files**, Git HEAD/refs/index/configuration, and all **214 normal SQLite tables/schema**. Existing protected bytes and normal database fingerprints are unchanged. Git HEAD, refs, index and configuration are unchanged; working-tree additions/edits are limited to the report/export, uploaded audit instruction and disclosed project-memory refinements.

Only the requested new report/export and non-sensitive project-memory refinements were written. Audit processing files are under `/tmp`; no application files, ignore rules, dependency declarations, source locations, workflows or databases were modified.

No builds/tests, package installs, migrations/seeding, SDK downloads, remote access, provider calls, financial actions, commit/tag/branch operations, pushes or remote creation were executed. Cached/live runtime directories and binary evidence were not exhaustively content-cleared. This is repository readiness, not production acceptance.

## 17. Final decision

```text
CURRENT GIT REPOSITORY:
EXISTS

AUTHORITATIVE PROJECT ROOT:
/home/runner/workspace

RECOMMENDED REPOSITORY MODEL:
MONOREPO

SAFE TO CREATE PRIVATE REMOTE:
AFTER CLEANUP

SAFE TO CREATE PUBLIC REMOTE:
AFTER ADDITIONAL REVIEW

SECRET CLEANUP REQUIRED:
YES

DATABASE/RUNTIME DATA EXCLUSION REQUIRED:
YES

CUSTOMER MOBILE AUTHORITATIVE SOURCE:
.migration-backup/customer_app/

CLEAN-CLONE REPRODUCIBILITY:
PARTIAL

RECOMMENDED CHECKPOINT NAME:
agendaally-pre-mobile-phase2-baseline-2026-10-06

READY FOR INITIAL COMMIT:
AFTER CLEANUP

READY FOR PUSH:
AFTER CLEANUP

NEXT ACTION:
Authorize one bounded private-repository preparation campaign: review historical credential/rotation and history policy, portable exclusions, curated safe evidence and indispensable ignored helper source; stop before any push for owner approval.
```

“Initial commit” is the requested decision label; an initial repository commit already exists. The recommendation concerns a new reviewed milestone checkpoint, not reinitialization.

**STOPPED FOR OWNER REVIEW. No repository creation, Git publication or Mobile Phase 2 authorized.**
