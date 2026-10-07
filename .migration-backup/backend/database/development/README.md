# Development database bootstrap

The development commands use only a private SQLite file directly inside this
directory. They never connect to MySQL, PostgreSQL, a SQLite URL, a database
outside this directory, or `:memory:`. Both `APP_ENV=local` and the explicit
`DEVELOPMENT_MODE=true` / `AGENDAALLY_DEVELOPMENT_DATABASE=true` opt-ins are
required. A first-time bootstrap additionally requires
`--confirm-empty-sqlite`, which creates the file exclusively, locks it, and
checks that it contains no application tables before running migrations.

## What is reviewed

`manifest.php` records the reviewed baseline's migration count and a SHA-256
fingerprint of **each migration filename and its contents** (213 migrations at
the original review). The fingerprint detects drift; it is not, by itself,
evidence that the migrations are safe. The application baseline was also
reviewed for destructive behavior:

- `2023_12_07_064250_remigrate_orders_table.php` runs `Schema::dropIfExists`
  for `orders`, `order_details`, `order_refunds`, `order_coupons`, coupon
  translations, and related tables in its **`up()`** method, then recreates
  them. Its `down()` is a no-op. It is safe on the first bootstrap only because
  that bootstrap proves the newly created file has no application tables.
- Ordinary create-table migrations also use `dropIfExists` in **`down()`**;
  these are destructive rollback operations. Do not run rollbacks on a
  development database whose data matters.
- `2023_10_14_101126_add_column_in_ads_package_translations_table.php`
  removes the `shop_ads_packages.banner_id` foreign key and column in `up()`.
  Its original string-form `dropForeign()` worked on MySQL but SQLite's
  schema builder could not drop a key by its name. The reviewed portability
  correction passes `['banner_id']` so Laravel infers the same canonical
  `shop_ads_packages_banner_id_foreign` name on MySQL and can rebuild the
  table on SQLite. The resulting schema and production behavior are unchanged.
  The same narrowly reviewed form was applied only to legacy migrations that
  dropped canonical `table_column_foreign` constraints for stories, services,
  orders, form options, bookings, points, payment-to-partner rows, and
  invitations. Each column list infers its original conventional MySQL index
  name; no constraint or column semantics were changed. The updated manifest
  fingerprint records these source portability corrections. The prior
  fingerprint is accepted solely so already-owned SQLite files with the same
  completed migration ledger can bootstrap into the reviewed current marker;
  incomplete ledgers are still refused.
- `2023_12_07_064250_remigrate_orders_table.php` also adds an `id` primary
  key and timestamps to `user_addresses`. SQLite cannot add an AUTOINCREMENT
  primary key with `ALTER TABLE`, so the table-creation migration now creates
  those same two columns up front; the later migration retains its existence
  checks and becomes a no-op for those fields on a fresh chain. This preserves
  the final schema for fresh MySQL installations and leaves all other
  destructive behavior in the later migration unchanged.
- `2025_01_20_104326_create_auctions_table.php` keeps the original MySQL
  full-text indexes on auction questions and translation fields, but creates
  those indexes only when the active driver supports them. SQLite retains the
  same columns for local fixtures and queries; it does not claim the
  MySQL-specific full-text index behavior.
- The guarded `User` model otherwise discards the explicit IDs in the
  historical `UserSeeder`. The development seeder scopes Eloquent's unguarded
  mode to that fixture seeder only, so linked demo records keep their reviewed
  IDs. Similarly, SQLite ignores the shop migration's MySQL
  `id()->from(501)` starting value; the local seed normalizes the original
  Cameroon seller shop to ID 501 before creating dependent fixtures. Neither
  compatibility adjustment changes production behavior.
- The legacy `DatabaseSeeder` calls translation seeders that can fall back to
  `truncate()`. Development seeding never calls it or that SQL fallback.

The original migration chain remains the canonical source of the full
production-shaped schema: changing/replacing it with `migrate:fresh`, copying
an incomplete mock schema, or running it against an unknown database is not
the bootstrap strategy.

## Existing-file and history protections

An ownership migration records the exact project-relative SQLite path and
reviewed manifest version. Later bootstraps use the normal `migrate` command,
never reset tables, and require the ownership marker **and** every previously
reviewed migration version to remain in Laravel's migration ledger. If the
ledger is missing, has lost any old entry (especially
`2023_12_07_064250_remigrate_orders_table`), contains a version absent from
source, or sees an unreviewed new migration, bootstrap fails before invoking
the historical chain. It does not repair, mark, or replay migrations on an
existing file.

When adding a new **forward-only** development migration, first inspect and
test it, bump the manifest's `schema_version`, update the migration count and
fingerprint, and add its new timestamped filename to
`approved_incremental_migrations`. New names must sort after
`latest_reviewed_migration`. Historical migrations must not be edited,
renamed, or added to that allow-list as a way to get around the protections.
The next bootstrap then runs only Laravel's normal pending-migration path on
an already-owned development file.

To create a fresh isolated schema, use the guarded bootstrap against a new
private `.sqlite` filename. A failed or interrupted bootstrap intentionally
leaves the file unowned; do not auto-resume or delete it. Inspect it manually,
preserve any useful data, and choose a new private SQLite filename after
determining it is disposable.

The existing data backend and production migration history are unchanged.
`tests/Development/DevelopmentDemoSeederTest.php` exercises the original
historical chain only against an exclusively created, throw-away SQLite file.

## Rebuilding and local demo data

For a new database, first run the guarded bootstrap, then the repeatable seed:

```sh
APP_ENV=local DEVELOPMENT_MODE=true AGENDAALLY_DEVELOPMENT_DATABASE=true \
  php artisan development:database-bootstrap --confirm-empty-sqlite

APP_ENV=local DEVELOPMENT_MODE=true AGENDAALLY_DEVELOPMENT_DATABASE=true \
  php artisan development:database-seed
```

The bootstrap command chooses a private database filename and owns the
first-time migration chain; do not point Artisan's `migrate`, `migrate:fresh`,
`db:seed`, or `DatabaseSeeder` at another database. Seeding requires the owned
database marker and is repeatable without resetting tables.

The curated seed retains the reviewed Cameroon and Africa demo fixtures and
adds real linked catalog products to the existing country/vendor branches.
Those rows include branch services/specialists and working schedules, an
option-bearing stock variant, quantity-backed product inventory, a customer
cart, and an unpaid order. For the existing dashboard query and
`GET /api/v1/admin/statistics/products`, it also records dated **synthetic,
offline-cash** delivered orders and matching order details and stock
decrements. Its top-selling counts are derived from those actual rows and are
not API-only fixtures.
Each shop seller's local display currency is synchronized from the shop's
configured country (including XAF in Cameroon, XOF in Burkina Faso, NGN in
Nigeria, and GHS in Ghana); cart/order and booking/fee fixtures use the
currency configured for their respective product or service location.

Accounting examples are equally explicit and local-only: a completed synthetic
offline service booking carries its source service's commission, a pending
platform-fee ledger row, and an offline wallet history/top-up. The payout
example stays pending and unapproved. Payment-provider identifiers and
credentials are never set; no provider, transfer, SMTP, or SMS integrations are
called or configured. Demo account email addresses use the reserved
`@agendaally.test` domain and phone numbers use the reserved fictional
`+1-202-555-01xx` range.

The canonical local demo logins are:

| Account | Email |
| --- | --- |
| Administrator | `admin@agendaally.test` |
| Manager | `manager@agendaally.test` |
| Seller | `owner@agendaally.test` |
| Specialist | `master@agendaally.test` |
| Staff | `staff@agendaally.test` |
| Finance manager | `finance@agendaally.test` |
| Cameroon Country Manager | `country-manager@agendaally.test` |
| Nigeria branch manager | `branch-manager-ng@agendaally.test` |
| Customer | `customer@agendaally.test` |

These curated and legacy branch `.test` identities share the local-only
password `AgendaAlly-Dev-Only-2026!`. Do not reuse it outside this local
development SQLite database.

The Staff login is an accepted `shop_manager` invitation for the Cameroon
shop's Branch Manager role, assigned to both Douala branch locations. Its login
resource exposes the `shop_manager` staff-portal role rather than the generic
customer role. The Country Manager login retains its accepted Cameroon
Country Manager grant.