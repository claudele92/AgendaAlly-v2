# AgendaAlly Customer Mobile — Phase 1 auth/session implementation

Date: 2026-10-06. Scope: the owner's attached Phase 1 authorization only.
Evidence directory: `docs/development/evidence/customer-mobile-phase1-auth-session/`.
Native source root: `.migration-backup/customer_app/`.

## 1. Executive summary

Implemented the bounded authentication transport, confidentiality and session-foundation repair in the existing Flutter application. No project regeneration, backend modification, financial implementation, provider activation, database mutation or Web/Admin change was performed.

The compatible Flutter/Dart graph resolves without changing any existing package version or checksum. The final focused suite passes **36 tests**; whole-application static analysis reports **no issues**. Customer-only changes are distinguished from the preserved application/database baseline.

Overall delivery is **PARTIAL**, not native-build acceptance. Android compilation cannot proceed without an Android SDK; macOS/Xcode is unavailable. P0-01 is closed at the application HTTP contract/logging level. P0-02 is implemented and passes focused storage/session tests, but is deliberately left **PARTIAL** until actual Android Keystore/iOS Keychain persistence and native cleanup are qualified. A Linux method-channel mock is not proof of native encryption or native credential erasure.

The campaign ends here for owner review. Phase 2, publishing and financial journeys were not started.

## 2. Exact original P0 findings

The historical audit remains unchanged:

- **P0-01:** “Sensitive URL/header/body/response logging” in the authentication repository and shared HTTP service. Required repair: body-only sensitive authentication transport; prohibit secret/token-bearing logs; verify release/debug confidentiality and request contracts.
- **P0-02:** “Bearer token in SharedPreferences.” Required repair: secure platform token adapter, coordinated legacy-token cleanup and account/session tests. The original audit did not claim an actual token disclosure.

Original finding inventory: two P0 and nine P1 findings.

## 3. Compatible toolchain/environment used

- Flutter **3.38.5**, Dart **3.10.4**; framework revision `f6ff1529fd`.
- Linux/Nix environment, isolated SDK, SDK tooling, pub cache and working copies under `/tmp/agendaally-mobile-phase1/`.
- Official Flutter tagged source and matching engine Dart tooling were used. Downloaded Dart executables required Nix-compatible ELF interpreter adjustment. This was confined to the temporary SDK, not the application or root environment.
- The source checkout reports a local/user branch; `toolchain.log` records the precise framework/engine/tool versions. This is not a claimed Android/iOS build.
- Flutter tools' own temporary bootstrap dependencies are distinct from the application graph. No root module/configuration modernization was performed.
- Original graph: `flutter pub get --enforce-lockfile` succeeded in the preserved baseline copy.
- Final graph: the same command succeeded after declaring the already-locked secure-storage package directly.

## 4. Exact authentication transport changes

Sensitive authentication arguments were moved from query parameters to JSON request bodies:

| Operation | Current transport |
|---|---|
| Email/phone password login | `POST /api/v1/auth/login`, email or normalized phone plus password |
| Social callback | `POST /api/v1/auth/google/callback`, existing identity/name/email/photo fields |
| Phone registration/verification | Existing POST endpoints and existing fields, now body transport |
| Phone existence check | `POST /api/v1/auth/check/phone`, phone in body |
| Email registration | `POST /api/v1/auth/register`, email in body |
| Ordinary email verification | `POST /api/v1/auth/verify/email`, email and otp in body |
| Email/phone recovery initiation | Existing recovery POST endpoints, recipient in body |
| Email reset verification | `POST /api/v1/auth/forgot/email-password/verify`, email and otp in body |
| Existing phone/Firebase reset completion | Existing POST contract, password and verification identity in body |

Protected profile/password operations retain their existing backend authority. Non-secret language/currency setting parameters were not reclassified as credentials. No backend route, controller, validation or token policy was changed.

## 5. HTTP logging/redaction design

Removed the verbose HTTP interceptor. The replacement permits only allowlisted HTTP method, event phase and bounded numeric status metadata:

- Default: disabled.
- Debug opt-in: `HTTP_DIAGNOSTICS`, or an injected test sink.
- Release restriction: `kDebugMode` is a mandatory gate even when diagnostics are explicitly requested.
- Never records URLs, query strings, headers, credentials, request bodies, response bodies or exception messages.

HTTP exception stringification is replaced with a fixed, non-secret type/status summary while retaining typed response/status access for existing error handling. Runtime checks in the existing helper accept the safe Dio exception subclass.

Authentication/Firebase catches, deep-link handling and WebView diagnostics no longer print credentials, SDK error detail or complete URLs. Five direct payload logs in booking/payment/order/product repositories were replaced with constant messages. Their request/mutation/calculation code is unchanged; the source-equivalence evidence excludes only logging, the unused convert import, formatting and optional trailing commas.

## 6. Secure token-storage design

`SecureTokenStore` uses the existing `flutter_secure_storage` **9.2.4** package:

- Android: encrypted preferences backed by the package's Android Keystore implementation, dedicated `AgendaAllyCustomerSecureStorage` namespace/prefix, automatic destructive reset disabled.
- iOS: dedicated Keychain account namespace, `unlocked_this_device`, synchronization disabled.
- Dedicated Android cloud-backup and device-transfer exclusions reference only the Customer encrypted preference file.
- Only the Customer bearer key is read/written/deleted. Existing provider storage namespaces are not migrated or deleted.

`SessionCoordinator` serializes secure-store mutations and provides a synchronous **in-process** token mirror for the existing architecture. SharedPreferences is not ongoing bearer authority. A non-secret invalidation marker is retained in preferences to reject an encrypted token left behind after a failed deletion.

The native package dispatches using `dart:io Platform`; overriding Flutter's UI target platform on Linux does not execute Android/iOS storage code. Configuration and mock plumbing tests are explicitly classified accordingly.

## 7. Legacy token migration behavior

Legacy plaintext tokens are **discarded**, not copied into the secure store:

1. Detect the old SharedPreferences token key.
2. Persist the non-secret invalidation marker.
3. Remove the legacy key and Customer/account preferences.
4. Delete the Customer secure token, if one exists.
5. Require reauthentication.

A valid new secure token can be restored when no invalidation marker exists. An invalidation marker prevents its restoration. Initialization clears any previously held in-memory token before reading.

No credentials are embedded in fixtures or reports. Synthetic tests use `.invalid` recipients.

## 8. Logout/session semantics

- Clear the in-memory bearer and advance the session generation before awaiting local cleanup.
- Serialize marker persistence, Customer cache/identity cleanup and secure-token deletion.
- Publish invalidation immediately after local cleanup, before waiting for remote revocation; replace private navigation/provider state with a signed-out pending screen.
- Deduplicate simultaneous logout requests.
- Block new token establishment while logout or an earlier identity operation is pending.
- Attempt the existing backend logout using only a transient snapshot of the previous bearer. A narrowly explicit logout-only transport bypass allows this revocation even if local cleanup failed; it is not used by private Customer requests.
- Optional FCM lookup is bounded to three seconds. Existing Dio connect/send/receive timeouts remain thirty seconds each. There is no claim that these form one thirty-second overall request deadline.
- Remote success and remote uncertainty are distinct. Device-cleanup failure is not returned as a completed local logout, including when server revocation was acknowledged.
- The sign-in/recovery screens display the relevant safe outcome. Device-cleanup failure blocks Customer/guest access behind a retry-only recovery page.

No refresh-token protocol was invented. Remote acknowledgement follows the existing idempotent backend logout contract. A timeout is not proof of server revocation.

## 9. 401 handling

A current protected 401 centrally invalidates the session, clears local authority/cache and replaces the Customer navigation lifetime. Repositories no longer independently clear only the token and navigate from stale contexts.

A public login 401 does not destroy an unrelated active session. An old generation's 401 cannot log a newer Customer out.

## 10. 403 handling

403 remains an authorization failure, not a global sign-out instruction. Token authority is retained. Startup validation that fails without confirmed revocation does not display private cached pages; it offers retry rather than interpreting forbidden/offline as logout.

## 11. Customer/account state isolation

- Generations guard request submission, response acceptance, auth callbacks and profile persistence.
- Previous-session success responses are rejected before they can update Customer state.
- Secure writes racing invalidation cannot resurrect the old bearer.
- Account preferences cleared: profile, group user/admin context, cart/group/board context, address/location/warehouse, account favorites/comparisons/saved stores, recent account activity and old token.
- Language, theme, translation/public settings and existing public currency preference behavior are retained.
- Profile cache serialization omits echoed password, confirmation and token/provider-credential fields.
- Invalidation replaces the Navigator and scoped provider tree. The widget tests verify actual `SessionBoundary` replacement and actual blocked recovery content.
- Restart with a secure bearer validates the current profile before exposing private cached pages.
- Existing Firebase/linked Google identities are signed out without activating providers. An SDK identity operation completing after logout is cleaned up before a new Customer token can be committed.

This is not native-device/provider acceptance. It does not implement downloaded-document custody or financial-command reconciliation.

## 12. Email verification contract alignment

The inspected existing ordinary-customer contract requires a recipient-bound **six-digit OTP consumed by POST**. The old token-bearing GET assumption belongs to a different Driver path and is not used.

The AuthBloc retains the pending email recipient. The repository sends `{email, otp}` to the current ordinary endpoint, awaits secure token establishment and treats failure/stale generation as unsuccessful verification.

Focused tests assert method, path, empty query parameters, exact body and issued-token handling. Invalid/expired/rate-limited fixture responses cannot establish a session. No live verification emails were sent.

## 13. Password recovery/reset transport findings

The existing email recovery verification POST endpoint already supports recipient plus otp. It is used directly rather than placing the code in the URL. Recovery initiation and reset secrets use bodies.

Existing protected password update behavior is retained. No reset policy, token lifetime, role, backend mail transport or provider behavior was changed. Fixture tests verify transport only; no real reset email/password change was performed.

## 14. Phone/social/Firebase boundaries

Changed only sensitive transport/logging and directly related async lifetime safeguards:

- Existing phone/social endpoint fields are retained.
- SDK operation completion is generation-guarded and late identities are cleaned up.
- Existing identity cleanup failure blocks establishment.
- No provider activation, Firebase/Google configuration change, SMS send, live social login or Apple provisioning occurred.

Full phone/social backend contract and native-provider acceptance remain deferred. The campaign does not claim those branches are production-ready.

## 15. Dependencies and exact justification

`flutter_secure_storage` **9.2.4 was already transitive in the original lockfile**. It was promoted to a direct, exact-version dependency because the application now imports it explicitly.

The only lockfile change is its dependency classification from `transitive` to `"direct main"`. **Every package version, checksum and platform implementation version remains unchanged.**

Newer secure-storage versions were investigated only in temporary copies. One had a Windows dependency conflict; another resolver run updated unrelated transitive packages. Neither graph was applied. The final solution reuses the original supported graph instead of modernizing unrelated packages.

No builders/generated Flutter project files were regenerated. The old verbose logger dependency was not broadly removed from the graph.

## 16. Tests added

Two focused Flutter test files under `test/phase1/`, covering:

- Secure authority, restoration, plaintext invalidation, write/read/delete failures and restart marker handling.
- Write/logout races, account switching and stale intent rejection.
- Current protected 401, 403 preservation, public login 401 and old-response/old-401 isolation.
- Captured synthetic confidentiality sentinels and diagnostics disabled by default.
- Declared Android/iOS options and actual secure-plugin method-channel read/write/delete plumbing under a Linux mock.
- Actual session navigation boundary replacement and blocked recovery content.
- Login, registration, email verification, recovery/reset and existing phone/social body transport.
- Logout success, timeout, already-invalid state, concurrent deduplication, immediate invalidation and local-cleanup failure with attempted remote revocation.
- Existing identity cleanup failure, late identity completion and anonymous background-isolate transport.

`source-contracts.mjs` separately verifies exact graph preservation, source confidentiality constraints, backup namespace exclusions and logging-only changes to the four financial repositories. It is not financial runtime acceptance.

## 17. Test results

**36 passed; zero failures**, on the final isolated working copy of the delivered source.

Commands:

```text
flutter pub get --enforce-lockfile
flutter test --no-pub --reporter expanded test/phase1
node docs/development/evidence/customer-mobile-phase1-auth-session/source-contracts.mjs
```

The preservation baseline is immutable and was not overwritten. Early development failures exposed a repeated-initialization token mirror and an incorrect platform test assumption; both were corrected. The final suite retains strong assertions and does not claim platform emulation from UI target overrides.

No live backend requests, financial actions, emails, SMS, FCM registration or provider logins were executed by these fixtures.

## 18. Static-analysis results

Original supported-SDK baseline: **no issues**.
Final entire application/test tree: **no issues**.

Command: `flutter analyze --no-pub`.

Intermediate warnings introduced by the edits were removed. There is no claimed preexisting source failure and no unrelated source repair.

## 19. Android build/environment qualification

Attempted `flutter build apk --debug --no-pub` in the isolated working copy.
Result: **BLOCKED-ENVIRONMENT — No Android SDK found**.

No APK, native encryption, emulator/device journey or Android release acceptance is claimed. The environment failure occurred before native compilation; it is not a failed-source result. Android manifest/backup rules and package option compatibility are source-validated.

## 20. iOS qualification

**NOT EXECUTED**: Linux host, no macOS/Xcode.

Keychain options are source/configuration-tested. Native Keychain persistence, provisioning/entitlements, simulator/device behavior and iOS compilation are not qualified.

## 21. P0-01 security acceptance

**CLOSED at the application contract/logging level.**

Evidence combines executed request-contract tests and executed Dio diagnostics/error-stringification tests, rather than code presence alone:

- Credential/code/identity fields are in bodies, not unsafe queries.
- Synthetic password, OTP, bearer, Authorization, signed-URL and private-response sentinels do not appear in captured HTTP diagnostics or exception stringification.
- Default logging is silent.
- Release-sensitive logging is hard-gated by `kDebugMode`; no native release build is claimed.
- Touched auth/Firebase/deep-link/WebView diagnostics contain no relevant secret-bearing URLs/values.

This closure is not a claim about third-party native SDK/device logs or an unexecuted release APK.

## 22. P0-02 security acceptance

**PARTIAL — implemented, focused tests pass; native persistence/erasure qualification remains open.**

Executed evidence establishes:

- Secure-plugin adapter calls and platform-specific configuration.
- No continuing SharedPreferences bearer writes.
- One-way plaintext discard and Customer cache cleanup.
- Successful secure deletion, in-memory invalidation and failed-delete restart marker behavior in fault-injected storage tests.
- Failed local cleanup blocks Customer access and does not claim success.

The tests use memory storage/method-channel fixtures. They do not prove actual encrypted native persistence, backup/restore behavior or native deletion. If all local durable invalidation operations fail and remote revocation is also uncertain, encrypted residue cannot be declared erased or safely invalidated across a restart solely from an in-memory flag. That combined native failure condition is not accepted by this report.

Consequently, do not close the native-storage finding or authorize financial Phase 2 based solely on this adapter/configuration.

## 23. P1-02/P1-03 status

- **P1-02: PARTIAL.** Ordinary email verification and email recovery transport are aligned and fixture-tested; full phone/social/native branch acceptance remains deferred.
- **P1-03: PARTIAL.** Coordinated logout/401/403/cache/navigation/late-identity behavior is implemented and focused-tested. Native secure storage, Firebase/Google cleanup and full real-device lifecycle acceptance remain unqualified.
- **SESSION/LOGOUT FOUNDATION: ALIGNED** describes the repaired contract and focused behavior, not native/provider acceptance.

These qualifications retain both findings in the remaining P1 inventory rather than silently treating source/mock evidence as full mobile acceptance.

## 24. Exact files changed

All 32 application changes below are relative to `.migration-backup/customer_app/`. Exact before/after fingerprints and the classification are in `preservation-result.json` and `after.json`.

```text
android/app/src/main/AndroidManifest.xml
android/app/src/main/res/xml/customer_auth_backup_rules.xml
android/app/src/main/res/xml/customer_auth_extraction_rules.xml
lib/application/auth/auth_bloc.dart
lib/domain/interface/auth.dart
lib/infrastructure/app_links/app_links_service.dart
lib/infrastructure/firebase/firebase_service.dart
lib/infrastructure/local_storage/local_storage.dart
lib/infrastructure/repository/auth_repository.dart
lib/infrastructure/repository/booking_repository.dart
lib/infrastructure/repository/order_repository.dart
lib/infrastructure/repository/payments_repository.dart
lib/infrastructure/repository/products_repository.dart
lib/infrastructure/repository/user_repository.dart
lib/infrastructure/service/helper.dart
lib/infrastructure/service/http_service.dart
lib/infrastructure/service/token_interceptor.dart
lib/infrastructure/service/safe_http_diagnostics.dart
lib/infrastructure/session/secure_token_store.dart
lib/infrastructure/session/session_coordinator.dart
lib/presentation/app_widget.dart
lib/presentation/components/web_view.dart
lib/presentation/pages/auth/auth_page.dart
lib/presentation/pages/drawer/drawer_page.dart
lib/presentation/pages/drawer/widgets/logout_button.dart
lib/presentation/pages/initial/splash_screen.dart
lib/presentation/session_boundary.dart
lib/presentation/session_recovery_page.dart
pubspec.yaml
pubspec.lock
test/phase1/auth_repository_test.dart
test/phase1/session_boundary_test.dart
```

Non-application deliverables: this report, focused evidence/logs/manifests and a standalone report export. Agent memory records approval boundaries and the isolated Nix SDK lesson; it is not an application behavior change. The supplied authorization attachment and historical audit are retained unchanged.

## 25. Database/schema preservation

Fresh before-state: **5,816 application baseline files and 214 normal database tables**.

After-state: **214/214 table fingerprints identical; schema fingerprint identical**. The table fingerprints include deterministic row ordering and PHP associative-row serialization, not counts alone.

The preservation check opens the normal SQLite database read-only, enables `query_only`, reads in a transaction and rolls back. No schema migration or normal database row write occurred. Test state is isolated/in-memory.

## 26. Web/Admin/backend preservation

**Zero unexpected non-mobile application changes.**

Every baseline Web/Admin/backend source fingerprint is unchanged. Database rows/schema are unchanged. Financial repository logic is unchanged apart from narrowly verified diagnostics/formatting removal.

The historical Web assessment stays **16/20, CONDITIONAL GO; production NO-GO**. No Web score or authority was updated. Existing previews/workflows, provider gates, currencies, payment/refund math, cancellation rules and policy documents were not modified.

## 27. Remaining mobile P0 findings

**Count: 1.**

- P0-01: CLOSED at application HTTP contract/logging level.
- P0-02: PARTIAL, awaiting actual native secure persistence/cleanup acceptance.

No new outside-scope security/financial P0 was established or investigated as a new campaign.

## 28. Remaining mobile P1 findings

**Count: 9**, including partially repaired/qualified findings:

| Finding | Status after Phase 1 |
|---|---|
| P1-01 Supported dependency/native bootstrap/build baseline | PARTIAL: compatible graph/analyzer/tests pass; native builds/bootstrap remain unqualified |
| P1-02 Authentication branch contract/acceptance | PARTIAL: ordinary email aligned; full phone/social/native acceptance deferred |
| P1-03 Coordinated session/account lifetime | PARTIAL: implemented/focused-tested; native lifecycle acceptance pending |
| P1-04 Checkout authorization context | OPEN, deliberately not implemented |
| P1-05 Authoritative collection/financial state UI/model/resume | OPEN, deliberately not implemented |
| P1-06 Manual refund eligibility/request/state/history/reference | OPEN, deliberately not implemented |
| P1-07 Durable unresolved mutation/intent reconciliation | OPEN, deliberately not implemented |
| P1-08 Refund & Cancellation Policy/flow links | OPEN, deliberately not implemented |
| P1-09 Critical-flow native tests/acceptance | PARTIAL: auth-focused coverage added; native/financial flow acceptance absent |

No booking/payment/refund/policy capability is presented as repaired by this campaign.

## 29. Recommended next bounded campaign

**Native Android authentication/session qualification only**, after owner review and separate authorization: obtain an approved Android SDK/device environment, build the existing app, qualify real secure persistence/plaintext discard/restart/logout/401/403/account switching/cleanup-failure behavior using isolated fixtures. Do not send real mail/SMS, activate providers or implement booking/payment/refund/policy behavior.

No next campaign has been started or scheduled.

## Final decision

```text
CUSTOMER MOBILE PHASE 1:
PARTIAL

P0-01 — AUTH/API CONFIDENTIALITY:
CLOSED

P0-02 — SECURE TOKEN STORAGE:
PARTIAL

CURRENT CUSTOMER EMAIL VERIFICATION CONTRACT:
ALIGNED

SESSION/LOGOUT FOUNDATION:
ALIGNED

SUPPORTED FLUTTER/DART VALIDATION:
PASS

ANDROID BUILD:
BLOCKED-ENVIRONMENT

IOS BUILD:
NOT EXECUTED

BACKEND CHANGES:
NONE

DATABASE/SCHEMA CHANGES:
NONE EXPECTED

WEB/ADMIN CHANGES:
NONE EXPECTED

REMAINING MOBILE P0 COUNT:
1

REMAINING MOBILE P1 COUNT:
9

CUSTOMER MOBILE PHASE 2 CAN BEGIN:
NO

NEXT RECOMMENDED CAMPAIGN:
Native Android authentication/session qualification only, after owner review.
```

**STOPPED FOR OWNER REVIEW. No active mission.**
