# AgendaAlly native production-build qualification

Date: 2026-10-06. Scope: build/static qualification and required unchanged
hardening/preservation validation only. No deployment, activation, score change
or repeat of accepted financial, receipt, authentication, email, calendar or
browser acceptance campaigns.

## Conclusion

**Native Web production build, whole-Web TypeScript validation and native Admin
production build: VERIFIED. Required hardening suite: PASS (exit 0).**

The known historical normal-database environment-marker mismatch remains an
explicit **preservation qualification**, not a PASS or a silently exempted
assertion. The build work is complete and the project can proceed to its final
bounded 20-gate audit with that open warning carried forward. An unconditional
“all validations green” closure is not justified until the separate preservation
warning is resolved. This is not production approval.

Canonical owner-accepted baseline remains **16/20 = 80%**. No gate points were
reassigned here.

## Findings and minimum source change

- Admin previously exhausted a 2 GiB Node heap after module transformation.
  Current isolated compilation transforms all **7,329 modules** and completes
  with a bounded **4,096 MiB V8 old-space limit**. This supports classification
  of the earlier failure as a resource-limit failure, not an application source
  defect. No Admin application behavior/configuration was changed to reduce
  compilation load.
- The current Web source still passed `color="transparent"` in the account-reset
  form. Although later retained build logs showed success, the fresh build
  reproduced this unsupported Button color type and exited 1.
- The only application source change adds `transparent: ""` to the existing
  Button color map. The previous runtime lookup returned `undefined`, which
  supplied no color classes; the new empty entry also supplies no color
  classes. A local comparison confirmed identical composed transparent classes
  and unchanged values for every existing color. No form submission, email,
  financial, permission, receipt or other behavior was changed.
- The build harness adds optional `--retain-evidence`; default execution remains
  supported. It retains fresh logs/results/artifact manifests without
  overwriting previous successful logs.

No accepted browser evidence was invalidated by the class-equivalent type fix;
no browser campaign was repeated.

## Exact commands and settings

From the workspace root:

```sh
node scripts/development/verify-stage1-builds.mjs all --retain-evidence
```

Within sanitized disposable source snapshots, the harness invokes the current
Node executable with:

```sh
node node_modules/next/dist/bin/next build --webpack
node node_modules/typescript/bin/tsc --noEmit --incremental false --pretty false
node node_modules/vite/bin/vite.js build
```

Build settings:

- `NODE_ENV=production`, `CI=true`, `NEXT_TELEMETRY_DISABLED=1`.
- `NODE_OPTIONS=--max-old-space-size=4096 --require=<workspace>/scripts/block-original-client-network.cjs`.
- Fresh isolated HOME/TMPDIR; no inherited environment secrets or dotenv files.
- Public staging-only `*.example.invalid` API/site/image fixtures.
- Development mode, Firebase and Maps disabled in build fixtures.
- Network guard retained; no real provider calls or disabled production guards.
- Native dependencies reused; no package/lockfile changes.
- Sequential Web/static/Admin execution, not overlapping builds.
- Native Web used three page-data/static-generation workers.
- Workspace memory limit observed: 8,589,934,592 bytes. The 4 GiB setting is a
  per-Node V8 old-space ceiling, not a measured aggregate RSS guarantee; peak RSS
  was not instrumented.

Unused managed frontend/acceptance processes were temporarily paused rather
than terminating platform editor/browser workers. Original application/runtime
configuration and provider/email settings were not changed.

## Retained successful build packet

```text
.local/development/build-qualification/2026-10-06T18-51-52-502Z/
```

| Result | Exit | Retained output |
| --- | --- | --- |
| Native Web production | 0 | `stage1-web-production-build.log`, `web-artifacts/` |
| Whole-Web TypeScript including fresh generated Next types | 0 | `web-typescript.log` |
| Native Admin production | 0 | `stage1-admin-production-build.log`, `admin-artifacts/` |

`results.json` retains actual executable arguments, explicitly constructed
environment/resource settings, exit codes/signals and build start/end
timestamps. Separate artifact manifests retain file paths, sizes and SHA-256
fingerprints: **1,007 Web files / 744,831,237 bytes** and **695 Admin files /
20,671,154 bytes**, including retained Web build cache. The temporary source
snapshot was removed, not the retained build outputs.

The failed fresh Web reproduction is separately retained under
`.local/development/build-qualification/2026-10-06T18-48-32-414Z/`.
Earlier build logs remain untouched. After the interruption, none of the
already-passed builds was repeated.

Warnings remain visible: deprecated Next middleware convention, outdated
baseline-browser/Browserslist data, and Admin chunks above 500 kB (including
roughly 2,052 kB main, 1,005 kB editor and 540 kB chart bundles). They are warnings,
not suppressed or treated as errors. No unrelated optimization was attempted.

## Required hardening

```sh
bash scripts/verify-original-hardening.sh
```

The script and PHPUnit assertions/configuration were not modified.

The first run exited 2 because seven native MySQL tests could not connect to
the deliberately paused disposable listener. Its log is retained as
`original-hardening.log`; it is not reported as an application regression or a
passing run.

The original `native-acceptance-mysql` workflow was restored on its existing
owned data directory/socket and port 33308. The unchanged complete script was
then rerun, not filtered to omit those tests:

**Exit 0; 1,093 tests; 7,398 assertions; one deprecation; 29 existing skips.**
Those skips were reported by the unchanged existing suite, not added for this
task. Native tests that failed for the absent listener were included in the
successful rerun.

Final evidence: `original-hardening-restored-mysql.log` and
`original-hardening-exit-status.txt` in the successful packet. PHP application,
route and hardening-test linting is part of the unchanged script. Admin
JS/JSX/module static parsing is covered by its production Vite compilation;
there is no claim of a separate Admin TypeScript project check.

## Preservation — warning retained without waiver

```sh
php scripts/development/manual-finance-preservation.php build-qualification-20261006
php scripts/development/manual-finance-identity-preservation.php build-qualification-20261006
php scripts/development/manual-finance-identity-preservation.php build-qualification-restored-20261006
```

The original 207-table check **exits 2**, reporting only the already-known
`agendaally_development_environment` mismatch. Original schema definitions
match; approved append-only counts remain country_permissions +12,
permissions +12, email_templates +8, migrations +1. All seven normal
manual-finance tables are empty; jobs and failed_jobs remain zero.

The full current fingerprints cover **214 tables**. Comparison with the
retained private-receipt campaign's `normal-after.json` reports that same
environment-marker table difference, no removed tables and matching schema.
It is not represented as a successful all-table historical comparison.

The two current build/restoration checkpoints compare exactly: **214 tables,
identical row fingerprints, identical schema, no changed tables**. This proves
preview restoration added no normal-data delta; it does not erase or replace
the historical warning. No normal data was edited, normalized or backfilled
to make validation pass.

Evidence: `historical-preservation.log`, its exit-status file,
`normal-data-comparison.json`, `restoration-preservation.json`, and the immutable
named checkpoints under `.local/manual-finance/`.

## Restoration and remaining qualifications

All processes paused for resource isolation are restored to their prior running
state: original Customer/Admin previews, native acceptance PHP/MySQL, selected
business supervisor, and mockup preview. Normal Laravel remained running.
Configured workflow commands/ports were not changed. Existing failed
selected-customer/scaffold-API workflow states were not “fixed” by starting
duplicates or reopening unrelated acceptance.

Startup logs show Customer ready/HTTP 200, Admin Vite ready, native PHP listening,
owned MySQL ready and the selected supervisor's services started. Existing
image/dependency warnings and the owned test MySQL self-signed-certificate/
`mbind` warnings remain qualified. These are startup observations, not renewed
browser acceptance.

No production-like provider configuration, financial rules, receipt security,
permissions, account/email behavior or legal/business rule was changed. No
production deployment/activation occurred. The remaining preservation warning
and other previously open audit gates remain for the final bounded review;
successful compilation is not an automatic gate-score increase.
