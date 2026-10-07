# Isolated test database setup

`phpunit.xml` sets `APP_ENV=testing` but does **not** set `DB_CONNECTION`/
`DB_DATABASE` (those lines are commented out). Laravel resolves the actual
env file to load from `APP_ENV`: it looks for `.env.testing` and, if that
file doesn't exist, silently falls back to loading the real `.env` instead
— the same database the live app uses. Most Feature tests use
`RefreshDatabase`, which runs `migrate:fresh` (drops every table) the
moment the first test in a run starts. Without `.env.testing`, running
`vendor/bin/phpunit` drops and rebuilds the real application database.

`tests/TestCase.php` has a hard runtime guard that refuses to run any test
unless the resolved database name contains `test` — so a missing/broken
`.env.testing` now fails loudly instead of silently wiping the wrong
database. This guide is how to make it pass, correctly.

## 1. Pre-flight: clear any cached config

If `bootstrap/cache/config.php` exists (left over from a `php artisan
config:cache` during deploy), Laravel uses it and skips loading any `.env*`
file at all — `.env.testing` would be silently ignored.

```
php artisan config:clear
ls bootstrap/cache/config.php   # should not exist afterward
```

## 2. Create a real, separate MySQL database

Don't use SQLite here. Several migrations use MySQL-specific constructs
that don't translate cleanly (custom starting auto-increment values via
`->from(1500)`/`->from(10001)` on `transactions`/`invitations`, several
`enum()` columns) — a SQLite test run risks passing against schema/ID
behavior that doesn't match production. Use the same engine as production,
just a different database name:

```
mysql -u root -p -e "CREATE DATABASE ibeauty_new_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

(Substitute your real MySQL user; match whatever `.env` already uses.)

## 3. Create `.env.testing`

Copy the real `.env` as a base — this guarantees `APP_KEY` and every other
required setting are already valid — then change exactly the database name
and environment:

```
cp .env .env.testing
```

Edit `.env.testing`:

```
APP_ENV=testing
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ibeauty_new_testing
DB_USERNAME=root
DB_PASSWORD=
```

`phpunit.xml` additionally forces `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`,
`CACHE_DRIVER=array`, and `SESSION_DRIVER=array` for every test run
regardless of what's in `.env.testing` (PHPUnit's `<server>` values are set
before Laravel loads any env file and always win), so those don't need
changing. As a precaution, since those aren't used during tests anyway,
you can also blank out real third-party credentials (payment gateway keys,
SMTP password) in `.env.testing` so a test can never accidentally reach a
real external service.

`.env.testing` is gitignored — it will contain real-looking credentials
(copied from `.env`) and must never be committed.

## 4. Run migrations once to confirm the connection resolves correctly

```
php artisan migrate --env=testing
```

This should create every table in `ibeauty_new_testing`, not `ibeauty_new`.
Confirm directly:

```
mysql -u root -p -e "SHOW TABLES;" ibeauty_new_testing   # populated
mysql -u root -p -e "SHOW TABLES;" ibeauty_new           # unchanged
```

## 5. Run the suite

```
vendor/bin/phpunit
```

If `.env.testing` is missing, misconfigured, or shadowed by a stale
`bootstrap/cache/config.php`, `tests/TestCase.php`'s guard throws
immediately, before any migration runs, naming the database it resolved
and why it refused. A clean run touching only `ibeauty_new_testing` is the
only way the suite proceeds at all.
