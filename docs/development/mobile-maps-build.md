# Environment-only mobile Maps builds

The formerly tracked `customer_app/ios/Flutter/Dart-Defines.xcconfig` has been
removed, including its exposed Maps literal. Do not restore it from history.
Do not commit credentials, local credential files, generated configuration,
encoded Dart defines, or build outputs. Base64 is not encryption.

## Outstanding creator action

Treat the old Maps key as exposed. **Rotate/revoke it in Google Cloud if valid**
and review its usage/billing. Rotation is **outstanding until the creator confirms
it**. Removing today's file cannot erase Git history, existing clones, cached
builds, binaries, or prior logs. Git history has not been rewritten; doing so
requires separate approval. No replacement credentials have been collected.

Before any key collection, obtain explicit agreement: **native client keys are
visible in app binaries and Google requests**. Secret storage protects the
source/build input, not the distributed client key. Maps activation, API
enablement, real provider requests and production/publishing need separate
approval. This change does none of those.

## Inputs (names only; no sample key values)

Choose exactly one platform/environment key in secure build environment
configuration:

| Build scope | Secret input |
| --- | --- |
| Development Android | `MOBILE_MAPS_DEVELOPMENT_ANDROID_KEY` |
| Production Android | `MOBILE_MAPS_PRODUCTION_ANDROID_KEY` |
| Development iOS | `MOBILE_MAPS_DEVELOPMENT_IOS_KEY` |
| Production iOS | `MOBILE_MAPS_PRODUCTION_IOS_KEY` |

Keep development and production secret stores/build jobs separate. The wrapper
does not fall back to another scope or to `GOOGLE_MAPS_API_KEY`. Do not put these
values in shell history, source examples, `.env` files, command-line flags, seed
data, test fixtures, logs or screenshots. Do not set new values during this task.

Restrict Android keys to `com.ibeauty.app` **and the actual signing-certificate
SHA-1 for that build** (including the applicable distribution signer). Restrict
iOS keys to `com.ibeauty.org`. Separate development/production keys even if the
package or bundle identifiers match. Limit each key to its required APIs.
Direct legacy Places/Static Maps requests are not automatically compatible with
native SDK restrictions; do not weaken restrictions to make them work.

The wrapper also preserves the original native build inputs through environment
variables: `APP_NAME`, `APP_ID`, `BASE_URL`, `WEB_URL`, `ADMIN_URL`,
`DEEP_LINK_URL`, `WS_BASE_URL`, `WS_SECRET`, `FIREBASE_API_KEY`,
`ROUTING_API`, `ROUTING_KEY`, `PAYFAST_PASSPHRASE`, `PAYFAST_MERCHANT_ID`,
`PAYFAST_MERCHANT_KEY`, `FACEBOOK_APP_ID`, `FACEBOOK_CLIENT_TOKEN`.
Supply your existing settings securely as needed; the removed generated file
is no longer a source of defaults. This change does not activate or reconfigure
those providers.

## Build entry point

From the workspace root, keyless compilation (no Maps key injected):

```sh
node scripts/build-mobile.mjs development apk --maps=disabled --no-pub
node scripts/build-mobile.mjs development ios --maps=disabled --no-pub --no-codesign
```

After separate consent, rotation, restrictions and secure provisioning, the
same commands can use `--maps=enabled`. Production builds explicitly select
`production`, never development inputs. `appbundle` is also supported. Use
long-form Flutter build options; CLI Dart defines, define files and verbose
logging are rejected. This is a build wrapper, not a launcher or deployment.
Keyless injection does not disable existing map widgets or their public-settings
fallback; no runtime/provider request is made by the verification tests.

Build on a compatible native toolchain, with existing Flutter dependencies
already restored. iOS requires macOS/Xcode/CocoaPods; Android requires its
configured SDK/NDK/JDK and signing settings. Keep SDK dependencies and native
integration versions unchanged.

## Implementation and lifecycle

The wrapper selects the single key from its environment, supplies Flutter's
existing compile-time `GOOGLE_MAPS_API_KEY` through a private temporary JSON
build input (not process arguments), and supplies the Android resource from
`AGENDAALLY_NATIVE_MAPS_KEY`. Gradle rejects mismatched Dart/native Maps inputs.
The iOS `Info.plist` setting and `GMSServices` initialization remain intact.

For iOS, a mode-0600 temporary xcconfig is linked at the ignored
`ios/Flutter/Native-Build.generated.xcconfig` only during the build. URL slashes
are escaped for xcconfig comment parsing. Other native settings are preserved.
The old plist-injection phase no longer prints values or overwrites Maps from
arbitrary Dart defines. Empty/unexpanded Maps settings do not initialize the
iOS SDK with a bogus key.

Temporary inputs and the symlink are removed after success/failure/handled
termination. Builds in one checkout are serialized by an ignored lock.
SIGKILL/power loss cannot run cleanup: remove the stale generated link, private
temporary directory and lock without displaying their contents before retrying.
Do not share native generated outputs or enable tool tracing. Flutter/Xcode/
Gradle still embed the client key in ordinary generated build artifacts; secure
the build machine and destroy disposable build artifacts after use.

## Verification and limitations

Run `node --test scripts/build-mobile.test.mjs`. Tests use synthetic non-provider
values only: scope separation, missing inputs, override rejection, native wiring,
URL escaping, raw/base64 output filtering, private-file permissions, successful
and failed subprocess cleanup, stale-artifact refusal, and a synthetic
compile-time Dart executable. The latter checks `String.fromEnvironment` input,
not full Flutter/native app compilation.

The workspace currently has Dart 3.8, while the app requires Dart >=3.10; no
JDK is available, and Linux has no Xcode. Full Android/iOS app compilation is
therefore blocked by the existing native toolchain, not claimed as passing.
No Google requests, key validation calls, database changes or publishing are
part of this verification. Native compilation on compatible Android/macOS
builders remains an explicit follow-up.