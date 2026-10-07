# Existing Google Maps configuration audit

Audit date: 2026-10-01. Maps remain disabled. No Google request, key collection,
key-value inspection or Maps source change was performed by this audit.
Stage 1 acceptance and Stage 2 approval are separate from Maps configuration.

## Exact existing variables

| Client | Key input | Development opt-in |
| --- | --- | --- |
| Native Customer web | `NEXT_PUBLIC_GOOGLE_MAPS_KEY` | `NEXT_PUBLIC_APP_ENV=local`, `NEXT_PUBLIC_DEVELOPMENT_MODE=true`, `NEXT_PUBLIC_MAPS_ENABLED=true` |
| Native Admin/Vendor | `VITE_MAP_API_KEY` | `VITE_APP_ENV=local`, `VITE_DEVELOPMENT_MODE=true`, `VITE_MAPS_ENABLED=true` |
| Laravel | `GOOGLE_MAPS_API_KEY` exists in `config/services.php`, but is **not consumed by the current geocoder** | `MAPS_ENABLED=true`; outbound transport is also disabled by the development launcher |
| Flutter | Compile-time `GOOGLE_MAPS_API_KEY` | Android manifest and iOS build settings consume the build-time key; the native launcher does not build Flutter |

Current Laravel geocoding instead reads the database setting `google_map_key`,
which `SettingsSeeder` initializes from the **different** variable
`GOOGLE_MAP_KEY`. Public `/api/v1/rest/settings` returns this setting. Web,
Admin and Flutter also have a settings-value key fallback. **Do not put a
private server key in that row or seed a new key into the database.**
Private backend geocoding requires wiring the existing server environment
configuration directly, without the public settings fallback.

Client key variables and disabled flags are already represented in the native
web/Admin environment examples. Examples and documentation must contain names
and empty/placeholder values only, never actual keys. New key values must remain
in secure environment configuration, not source, local credential files, seeds,
test fixtures, logs or screenshots.

## APIs actually used

| Surface | Existing Google calls | Google APIs to enable if preserving all those behaviors |
| --- | --- | --- |
| Customer web | JavaScript maps, legacy Places autocomplete/details, forward/reverse geocoding, static images | Maps JavaScript API; Places API (legacy entitlement required); Geocoding API; Maps Static API |
| Admin/Vendor | JavaScript maps and legacy Places autocomplete; direct Geocoding JSON | Maps JavaScript API; Places API (legacy); Geocoding API |
| Laravel | Server-side legacy Geocoding JSON | Geocoding API only |
| Flutter Android/iOS | Native maps, legacy Places autocomplete and static images | Maps SDK for Android / Maps SDK for iOS as applicable; Places API (legacy); Maps Static API |

No Google Routes, Directions, Distance Matrix or Route Matrix integration was
found. Flutter's `/v2/directions/driving-car` calls use separate `ROUTING_API`
and `ROUTING_KEY` configuration, not Google. OSM/Nominatim is also independent.
Do not enable unrelated paid Google APIs.

The legacy `AutocompleteService` used by web is unavailable to new Google
customers from 2025-03-01. Existing code must not be assumed compatible with
a newly created Google project: confirm entitlement, or approve a targeted
update within the current integration before claiming autocomplete works.

## Development key restrictions

A browser key is necessarily delivered to clients in JavaScript and Google
requests; a Replit Secret does not make `NEXT_PUBLIC_*` or `VITE_*` private.
Native app binaries and requests likewise expose their client keys.
Obtain informed agreement to this limitation before collecting a key.

For development Customer/Admin browser use, apply **Website/HTTP-referrer**
restrictions to only:

```text
https://b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev:3002/*
https://b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev:3003/*
```

Do not allow all Replit hosts, arbitrary origins, or production domains on a
development key. Update restrictions deliberately if the preview hostname
changes. API restrictions should contain only the APIs used by the chosen
browser surface above. Separate keys per browser client are preferable for
smaller permissions; a browser-only key for these two exact origins is not a
server or mobile key.

Other platforms require different keys:

- Private Laravel key: Geocoding API only, with an IP restriction for actual
  outbound server egress where feasible. Do not assume a stable Replit egress
  IP. Keep it out of the public settings response and client bundles.
- Android native SDK key: Android application restriction for
  `com.ibeauty.app` and the actual development signing-certificate SHA-1.
  No fingerprint was established by the audit.
- iOS native SDK key: iOS application restriction for `com.ibeauty.org`.
- Direct mobile Places/Static Maps web-service requests require additional
  deliberate restriction/transport handling; a native SDK key does not
  automatically make every REST call compatible.

Standard Replit project secrets may also reach deployments. Development-only
configuration must be explicitly separated from production configuration;
do not assume that naming a secret "development" provides environment isolation.
No publishing or production key/configuration change is authorized.

## Issues a key alone will not solve

- Customer legacy Places Details currently uses `mode: "no-cors"` followed by
  JSON parsing, which cannot read an opaque response.
- Direct browser Geocoding/Places web-service calls have CORS and application
  restriction compatibility issues. Reuse the existing JavaScript SDK/service
  wrappers or an authorized private backend path; do not remove restrictions
  to make an incompatible call appear to work.
- The Laravel development launcher disables outbound URL/curl/socket
  transports. Enabling `MAPS_ENABLED` alone cannot make its Google call succeed.
  Do not broadly enable unrelated payment/email/SMS transports.
- The audit found a key literal in tracked
  `customer_app/ios/Flutter/Dart-Defines.xcconfig`.
  Its value was not displayed. Treat it as exposed and rotate if valid.
  Removing it from the current file does not remove Git history.
  The controlled source-removal change has now removed that generated file
  and added environment-only native build injection; see
  [mobile Maps build guidance](mobile-maps-build.md). Rotation is still
  creator-owned and outstanding until confirmed. No key was collected and
  no Google request was made.

Seeded Cameroon/Douala selection, application bootstrap and existing
authorization must continue working without Google. No geography replacement
or grant widening is needed. Automated acceptance/regression tests must keep
Maps disabled and must not create unnecessary paid provider requests. Real
development Maps verification, after permission and secure configuration,
should use a small, explicitly bounded set of actual map/location interactions.

## Source and guidance

- Web: `config/integrations.ts`, `config/runtime-config.cjs`, map/address hooks,
  search-place selector and shop-location image component.
- Admin: `src/configs/runtime-config.mjs`, `src/helpers/googleMapsLoader.js`,
  map/address components and address helpers.
- Laravel: `config/services.php`, `config/development.php`,
  `app/Helpers/Utility.php`, `database/seeders/SettingsSeeder.php`,
  public settings route/resource.
- Flutter: `lib/app_constants.dart`, Android manifest/Gradle, iOS settings,
  address repository and map/location widgets.
- [Google API security best practices](https://developers.google.com/maps/api-security-best-practices)
- [Google Maps JavaScript Places legacy reference](https://developers.google.com/maps/documentation/javascript/reference/places-autocomplete-service)
- [Replit Secrets documentation](https://docs.replit.com/core-concepts/project-editor/app-setup/secrets)
