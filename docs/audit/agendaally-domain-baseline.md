# Original booking and product-order compatibility baseline

## Approval, scope and isolation

This approved baseline exercises original Laravel models, repositories,
services and resources under `.migration-backup/backend/app`. It does not
replace or rewrite backend behavior. The test harness reuses
`tests/Hardening/IsolatedTestCase`, its disposable Laravel container and its
process-local SQLite `:memory:` database. The only added tables are the
explicit synthetic schema in `tests/Baseline/OriginalDomainBaselineTest.php`.
It registers only Laravel framework providers needed to resolve the actual
filesystem, after-response dispatch and JSON-resource response dependencies;
the Spatie role model is configured against test-only role tables.

It does not load an original `.env`, boot original application providers, run
Artisan, migrations, seeders, `RefreshDatabase`, or contact payment/email/SMS
providers. No production systems or data are used. Restore the original locked
dependencies in a disposable directory with `composer install --no-scripts
--no-plugins`; then run:

```sh
HARDENING_VENDOR_AUTOLOAD=/tmp/agendaally-hardening-runtime/vendor/autoload.php \
  bash scripts/verify-original-baseline.sh
```

Set `BASELINE_WRITE_FIXTURES=1` when running the command to regenerate
`.migration-backup/backend/tests/Baseline/fixtures/original-domain.json` from
the original backend outcomes. The fixture uses synthetic IDs and currency,
and is intended as a backend-generated Flutter peer contract example.

## Covered source behavior

- `BookingService::create()` is checked for both a missing-service
  `ERROR_501`/no-write outcome and a successful scheduled booking. The success
  uses synthetic service, shop/service location, master role and working-day
  records, with frozen time and a future appointment. The original
  `BookingRepository::show()` and `BookingResource` produce the booking
  response envelope. History semantics are checked against
  `Booking::scopeFilter()` and the actual polymorphic transaction relation:
  an unpaid `new` row is hidden, while a transactional `new` row and a
  canceled row remain visible.
- `CartService::create()` is called twice for the same original stock. The
  baseline records quantity clamping/price calculation and the observed
  existing-cart `updateOrCreate` result (replacement of the line quantity,
  not accumulation). The returned `CartResource` is serialized through
  Laravel's JSON resource response to capture the actual `data` envelope.
- `OrderService::create()` uses the actual cart checkout path and real
  `CartOrderService`, then checks the saved order detail, total, cart removal,
  and the original `OrderRepository::ordersPaginate()` user-history result.
  The original `OrderResource` is likewise serialized as a Laravel JSON
  response, including order details and the created refund. Its randomized
  one-time code is set to a fixed synthetic value after checkout for stable
  fixture output.
- `OrderRefundService::create()` creates the initial pending request.
  `OrderRefundRepository::paginate()` supplies actual refund history; both the
  singular resource and paginated `OrderRefundResource` response envelope are
  captured. A second service-created request checks the original duplicate
  rejection.

All resource envelopes come from Laravel `response()->getData(true)` calls,
not hand-assembled fields. The JSON is produced from service/model/resource
state during these isolated tests, not copied from client mocks. It
deliberately contains no provider tokens, real customer information, or
external payment responses.

## Synthetic-schema fidelity limits

The schema only has fields read or written by these focused paths and empty
tables for Eloquent relations those paths eager-load. It is not a schema
compatibility certification: database constraints, indexes, SQL dialect
differences, real foreign keys, concurrent writes, production defaults,
scheduled tasks, provider callbacks and full HTTP middleware/request
validation are not represented. A service booking succeeds against the
synthetic working-day schedule; this does not certify the production schedule
database, all availability rules, or full HTTP endpoint behavior.

The original focused `CustomerAppointmentsListTest` and broader application
feature suite use `RefreshDatabase`/migrations and are not run. The standalone
legacy cart-service example is not a behavior test. Do not report those tests
as passing. The focused runner passed with **3 tests and 49 assertions** both
when regenerating the fixture and when comparing a fresh run with the checked-in
fixture. On PHP 8.4, the deliberately invalid booking request surfaces the
original `BookingRepository.php:453` undefined-`shop_id` warning; checkout
surfaces the original `OrderHelper::checkShopDelivery()` implicit-nullable
deprecation. The runner displays these diagnostics rather than suppressing or
changing original code. No behavior fix is part of this baseline.