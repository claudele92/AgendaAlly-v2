# AgendaAlly original-client compatibility baseline

## Scope and evidence boundary

This audit reads the preserved original client trees under
`.migration-backup/web`, `.migration-backup/admin`, and
`.migration-backup/customer_app`, alongside the original Laravel route/resource
source for comparison. It does not change client behavior, backend behavior,
branding, or the main baseline documents. It performs no HTTP requests and
does not contact an application, payment provider, production system, or
customer account.

Evidence is separated deliberately:

- **Static source evidence** means the request paths, wrapper access, type
  declarations, model mappings, and matching Laravel declarations were read
  from source. It does not establish that a deployed server returns those
  values.
- **Executable outcome** means a local command actually ran. The
  dependency-free verifier and Node's JavaScript parser ran here; isolated
  frozen Yarn restores succeeded, the admin compiler ran, and the web compiler
  was started with external network access blocked. None of these is a browser
  integration test.
- **Not executed** means the relevant runtime/dependencies were unavailable
  or a command did not complete. The comprehensive Flutter parser suite did not run because both the
  original locked restore and the reduced DTO-only dependency solve are blocked
  by the installed Dart SDK version.

## Executable outcomes recorded in this environment

Command:

```sh
bash scripts/verify-original-clients.sh
```

The command completed successfully. It ran `node --check` over the original
admin booking/order/refund service modules and passed 17 dependency-free
source/fixture assertions in `scripts/verify-original-clients.mjs`. These
checks establish JavaScript syntax, source-string invariants, and presence of
the generated backend booking/cart/order/refund resources. They do not execute
the Flutter parser test.

A separate dependency-free Dart run **did execute the unchanged original
`CartCalculateResponse` parser**: 7 checks passed for a synthetic quote,
decimal prices, integer totals, empty errors, serialization round-trip and
null failure data. Reproduce with
`DART_SUPPRESS_ANALYTICS=true dart scripts/verify-original-cart-quote-parser.dart`.
The client verifier runs this smoke check when Dart is available. This narrow
result does not execute the booking, cart-detail, order or refund parsers, and
does not claim that a real endpoint returned the synthetic quote.

Frozen dependency restores were run only in disposable source-only copies
under `/tmp/agendaally-locked-clients.iQFq2t`; no `.env` files, production
assets, or secrets were copied. Yarn 1.22.22 completed both restores with
`yarn install --frozen-lockfile --ignore-scripts`. Peer-dependency warnings
were reported. The copies' `yarn.lock` files remained byte-for-byte identical
to the preserved originals.

The installed toolchain is Flutter 3.32.0 / Dart 3.8.0. The original Flutter
manifest requires Flutter >=3.38.5 / Dart >=3.10.0. In a disposable copy,
`flutter pub get --enforce-lockfile` exited 1 with that SDK constraint error;
the original `pubspec.lock` comparison passed. A subsequent
`flutter test --no-pub` could not resolve `flutter_test`, so no parser assertion
ran from the full-app copy.

The web and admin builds were attempted against their restored copies with a
cleared environment, API variables set to a closed loopback port, and the
parent's `scripts/block-original-client-network.cjs` guard active only during
builds (never during dependency restore):

| Client | Command outcome | Interpretation |
| --- | --- | --- |
| Web | The command reached Next.js 16.0.10's optimized production build, then the tool timed out at 300 seconds before reporting an exit status. | Incomplete build; not a pass and not a demonstrated source defect. No external network access was allowed. |
| Admin | Vite 7.3.0 transformed 1,235 modules, then exited 1 on unresolved `assets/images/user.jpg` imported by `src/views/deliveriesMap/delivery-map-orders.jsx`. | The copy intentionally omitted original binary/media assets. This is an artifact-isolation build failure, not evidence that the original asset-complete client fails. |

The admin `update-build.js` step changed only the disposable copy's generated
metadata. The preserved client source and lockfiles were not changed. A full
browser/client build or end-to-end behavior is not certified.

A reduced DTO-only attempt copied `.migration-backup/customer_app/lib/domain/model`
unchanged, verified it with `diff -qr`, and gave the harness package the
required name `demand`. Its temporary manifest pinned 71 external packages to
versions in the original Flutter lock, including all three Git packages at
their locked commits. `flutter pub get` still exited 1 under Dart 3.8.0:
`shared_preferences 2.5.4` requires Dart >=3.9.0. The source graph also reaches
outside `lib/domain/model` into hundreds of app files through the preserved
booking/user model imports, so this is not a small, independent parser graph.
No package was downgraded and no original SDK constraint or lockfile was
weakened to make the harness resolve. **Zero assertions ran in that comprehensive
Flutter parser suite**; the separate dependency-free cart-quote smoke check
described above did run.

The generated fixture
`.migration-backup/backend/tests/Baseline/fixtures/original-domain.json` now
contains genuine synthetic backend-serialized booking, cart, checkout/order,
refund, and refund-history resources. The Dart test has been updated to feed
the actual resource data into `BookingModel`, `CartModel`, `OrderShops`,
`RefundModel`, and `RefundOrdersModel`; the parser test has not executed.
Booking and checkout resources are single-resource `data` objects, while the
refund history is a paginated collection with `data`, `links`, and `meta`.
Other test cases continue to exercise synthetic quote and wrapper shapes.

## Source-derived client contract map

| Flow | Preserved web client | Preserved Flutter client | Wrapper/shape expected by original clients |
| --- | --- | --- | --- |
| Booking quote/create/history/detail | `web/services/booking.ts` | `customer_app/lib/infrastructure/repository/booking_repository.dart` | Quote and create use the standard `data` envelope; booking create/list/group detail parsers read a list at `data`. `BookingCalculateResponse` instead parses quote fields from object `data`. |
| Cart read/group/checkout quote | `web/services/cart.ts` and cart pages | `customer_app/lib/infrastructure/repository/cart_repository.dart` | Cart responses are standard envelopes with a cart object at `data`; group opening extracts `data.id` from the response body. Product-cart quote uses `ProductCalculateResponse`; authenticated cart calculation uses the distinct `CartCalculateResponse`. The resource keys `user_carts`, `cartDetails`, and `cartDetailProducts` intentionally mix snake_case and camelCase in the original Laravel resources and Flutter parser. |
| Product checkout/history/detail | `web/services/order.ts` and orders/cart pages | `customer_app/lib/infrastructure/repository/order_repository.dart` | Checkout returns a standard envelope whose `data` is an order-resource collection. Web types this as `DefaultResponse<OrderFull[]>`. Flutter's checkout repository only treats a 2xx response as `true` and does not parse its body. Order history uses a direct Laravel paginated resource collection (`data` list plus pagination metadata); Flutter parses `data` and `meta`. Group/detail reads parse an envelope containing an order list. |
| Refund create/history/detail | `web/services/refund.ts` and refund page | `customer_app/lib/infrastructure/repository/order_repository.dart` | Refund create is a standard success response. Refund history is a direct paginated resource collection; the Flutter `RefundOrdersModel` reads its `data` list but does not model pagination metadata. Flutter detail explicitly unwraps body `data` before `RefundModel.fromJson`; the refund resource's nested order is optional/conditionally loaded. |
| Admin operations | `admin/src/services/{booking,order,refund}.js` | Not an admin mobile client | Admin booking calculate, booking CRUD/status/detail, paginated orders and refunds, and refund status paths are separate dashboard/admin routes. Source route declarations exist for the inspected calls. No admin request was sent. |

The mapping above follows original client and Laravel source, not a recorded
server exchange. Laravel standard `successResponse` wraps values as
`timestamp/status/message/data`; resource-collection pagination instead
ordinarily exposes top-level `data`, `links`, and `meta`. Do not globally
rewrap paginated responses or flatten external response shapes during a
future migration.

## Compatibility observations and review points

1. **Booking wrapper is a list, not one booking object.** Flutter's
   `BookingResponse.fromJson` maps `json["data"]` to a list. The original user
   booking controller returns `BookingResource::collection(...)` in the
   standard success envelope on create; the Flutter repository then selects
   the first returned booking ID. Web create is typed as an array as well.
   Quote `data`, by contrast, is one calculate object.
2. **Product quote is not the same DTO as server-cart calculation.** The
   unauthenticated product-cart quote uses
   `/rest/order/products/calculate` and parses shops/stocks using
   `ProductCalculateResponse`. Authenticated cart recalculation parses totals,
   delivery fees, coupons, and errors through `CartCalculateResponse`. Keep
   both paths and DTOs distinct.
3. **Checkout response consumption differs by client.** Web declares the
   checkout result as `DefaultResponse<OrderFull[]>`. Flutter posts the same
   user order route but discards the successful body and returns `true`. This
   is a source-level difference, not a demonstrated defect or a fix in this
   baseline.
4. **Pagination metadata is not uniform across Flutter models.** The order
   history model parses `data` and `meta`; the refund history model parses only
   `data`. Both web services declare paginated results. This audit did not
   change UI pagination behavior.
5. **Nested resource casing is an external compatibility detail.** Laravel's
   original cart resources emit `user_carts`, then `cartDetails`, then
   `cartDetailProducts`. The Flutter parser reads those exact spellings.
   Normalizing the server keys without an adapter would break the preserved
   parser.
6. **Admin route prefixes are not interchangeable with customer routes.**
   Admin service methods target `dashboard/admin/...`; web and Flutter
   customer checkout/history flows target `dashboard/user/...` or public
   `rest/...`. Admin endpoints and mutation controls were inspected only;
   destructive admin methods were never invoked.
7. **Runtime-critical client behavior remains unverified.** In the original clients, no authenticated
   booking/cart/order/refund calls, successful checkout, cancellation,
   refund creation, provider return, authorization boundary, browser
   navigation, persisted/reloaded state, or actual API pagination was tested.
   Existing client and server source alone cannot establish these outcomes.

These are compatibility observations, not behavior fixes. Preserve existing
AgendaAlly branding and externally visible routes/status strings while
performing separately approved changes.

## Reproducing the checks

Run the checks that do not require restored application dependencies:

```sh
bash scripts/verify-original-clients.sh
```

To reproduce the isolated web and admin build attempts, first restore each
client in a disposable, source-only copy using the preserved `package.json` and
lockfile. `node scripts/prepare-original-client-copies.mjs` creates a fresh
owned temporary directory and prints its paths. Substitute those paths for
the example below; no original environment files or media assets are copied:

```sh
(cd /tmp/agendaally-locked-clients.iQFq2t/web &&
  yarn install --frozen-lockfile --ignore-scripts)
(cd /tmp/agendaally-locked-clients.iQFq2t/admin &&
  yarn install --frozen-lockfile --ignore-scripts)
RUN_WEB_ADMIN_BUILDS=1 \
  WEB_CLIENT_DIR=/tmp/agendaally-locked-clients.iQFq2t/web \
  ADMIN_CLIENT_DIR=/tmp/agendaally-locked-clients.iQFq2t/admin \
  bash scripts/verify-original-clients.sh
```

The build verifier uses `NODE_OPTIONS=--require=.../scripts/block-original-client-network.cjs`
only around build commands. It refuses environment files, public assets, and
binary/media assets in the copies. To retry just one build, use `RUN_WEB_BUILD=1`
or `RUN_ADMIN_BUILD=1` with the matching `WEB_CLIENT_DIR` or
`ADMIN_CLIENT_DIR`; the admin copy also needs the original `update-build.js`.
Do not set the network guard during restore, and do not run a dependency restore
in the preserved source tree. The admin build-date script modifies only its
disposable copy's `package.json` metadata and `public/meta.json`; refresh that
copy before rerunning the verifier.

The Flutter parser suite requires a compatible SDK and a fully resolved
isolated copy. The original full-app restore cannot resolve under the installed
Flutter 3.32.0 / Dart 3.8.0 SDK, and the reduced DTO-only attempt also cannot
resolve its exact locked package versions. If a compatible SDK becomes
available, use an isolated copy and do not change the original SDK constraints
or lockfile:

```sh
FLUTTER_CLIENT_DIR=/tmp/agendaally-original-client-copy \
  bash scripts/verify-original-clients.sh
```

The verifier refuses to use `.migration-backup/customer_app` itself, requires
the isolated copy's existing `.dart_tool/package_config.json`, and invokes
`flutter test --no-pub` against a temporary test file that it removes on exit.
With `FLUTTER_PARSER_MODE=dto-only`, it additionally requires a `demand` harness
whose copied `lib/domain/model` matches the preserved source directory exactly.
It never restores dependencies itself. The suite includes synthetic quote and
wrapper cases plus the backend-generated resource fixture; even a successful
parser run would not be live HTTP or end-to-end application evidence.