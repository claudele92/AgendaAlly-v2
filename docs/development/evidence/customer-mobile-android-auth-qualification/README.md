# Android authentication/session qualification evidence

This directory is scoped to the owner's Android-only authorization. The existing Flutter application was not rebuilt as a new project, regenerated, or modernized. Native application behavior is not inferred from Linux fixtures.

## Evidence levels

- `source-checks.json`: **SOURCE-CONFIGURED**. Checks unchanged dependency declarations/lockfile, Android secure-storage options, manifest references, backup/device-transfer exclusions, sensitive-body transport and financial repository byte identity.
- `phase1-regression.log`: **TEST-VERIFIED**. The existing 36 focused Flutter tests, rerun on a fingerprint-verified isolated application copy. These are Linux fixtures, not Android Keystore/runtime proof.
- `static-analysis.log`: whole-application Flutter analysis.
- `android-debug-build.log`: final attempted Android debug build. Read the report for the classification; a host deadline or cancellation must not be interpreted as an application-source failure.
- `android-debug-initial.log`: the earlier build attempt using the ignored workspace Gradle cache.
- `runtime-environment.json`: boolean/aggregate host and device availability observations. No device identifiers or private application storage dumps.
- `before.json`, `after.json`, `preservation-result.json`: fresh application fingerprints and all 214 normal SQLite table/schema fingerprints. Database reads use read-only/query-only access and a rolled-back read transaction.
- `validation-results.json`: consolidated results, native evidence limitations and final status.
- `sha256-manifest.json`: evidence-file integrity hashes, excluding itself.

## Reproduction helpers

```text
node docs/development/evidence/customer-mobile-android-auth-qualification/preservation.mjs before
node docs/development/evidence/customer-mobile-android-auth-qualification/source-checks.mjs
node docs/development/evidence/customer-mobile-android-auth-qualification/android-build.mjs
node docs/development/evidence/customer-mobile-android-auth-qualification/preservation.mjs after
```

The first command refuses to overwrite the immutable baseline. These helpers require this repository and the campaign's isolated toolchain paths; the archive is retained proof, not a self-contained SDK/device laboratory.

Regression commands, run in the isolated app:

```text
flutter pub get --enforce-lockfile
flutter test --no-pub --reporter expanded test/phase1
flutter analyze --no-pub
```

The Android build helper calls the existing native build wrapper. It supplies only non-live bootstrap values, does not inherit secret-store credentials, keeps maps disabled, preserves package identity and targets x64 for debug qualification. Host-only UTF-8, JVM limits, filesystem-watch and temporary/cache settings do not modify application configuration source or the approved dependency graph.

## Tooling incidents

Initial SDK/NDK staging exhausted the temporary filesystem write quota even though `df` reported free capacity. JVM SIGBUS messages are host-tooling evidence, not Android source/runtime defects. Moving an incomplete SDK left absolute installer staging metadata; only this campaign's failed installation state was removed before the successful fresh NDK installation. Intermediate failure logs are retained where available; no missing exit code is invented.

SDK downloads came from Google's official repository; Java came from Adoptium. The archive SHA-256 values observed before temporary-download removal were:

- command-line tools archive: `7ec965280a073311c339e571cd5de778b9975026cfcbe79f2b1cdcb1e15317ee`
- JDK archive: `3808d1d15e3ec6bd5b84057fb5d84c33d8a1536a258146bcea2e603fc726e08e`

The bounded Android build command returned 124; its log reports a cancelled Gradle task with exit 143. Flutter/the wrapper nevertheless reported exit 0. No APK exists: a reported zero after cancellation is not a successful Android build.

No cryptographic keys, bearer values, Customer credentials, private storage dumps or raw thread diagnostics are included. No actual cloud backup, server revocation, iOS runtime or financial activity is claimed.
