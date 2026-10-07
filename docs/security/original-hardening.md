# Original backend security hardening

## Scope and approval

The creator approved targeted original-source hardening with a disposable local
verification environment on 2026-09-30. This is not a port, rebuild, deployment,
production-data change, or approval to run real payments, payouts, email or SMS.
The original Laravel backend remains under `.migration-backup/backend`; the
Node API/Canvas artifacts are not replacements for it.

## Safe verification

PHP 8.4 is required by the original locked dependencies. The isolated suite
uses its own bootstrap, a minimal Laravel container and SQLite `:memory:`.
It does not load original `.env` files, boot original service providers, invoke
Artisan, use `RefreshDatabase`, or execute migrations/seeders. Laravel HTTP
transports reject unfaked requests. Fixtures contain synthetic identities and
provider responses, not imported customer or payment data.

Restore the locked dependencies in a disposable directory, never with scripts
enabled. The original Composer scripts include database seeding:

```sh
mkdir -p /tmp/agendaally-hardening-runtime
cp .migration-backup/backend/composer.{json,lock} /tmp/agendaally-hardening-runtime/
cd /tmp/agendaally-hardening-runtime
composer install --no-scripts --no-plugins --no-interaction --prefer-dist
```

From the workspace root:

```sh
HARDENING_VENDOR_AUTOLOAD=/tmp/agendaally-hardening-runtime/vendor/autoload.php \
  bash scripts/verify-original-hardening.sh
```

The runner lints original app/routes plus isolated tests, then runs only
`phpunit-hardening.xml`. Do **not** substitute the original full test suite:
it contains destructive database-refresh traits and requires separate baseline
approval and independently established database isolation.

Temporary dependencies are not committed. An existing reviewed original
`vendor` can also be used by omitting the environment variable.

## Security boundaries

- Public installer routes are removed. Legacy installer controller methods
  fail closed without token issuance, executable config generation or migration.
- Reset challenges are cryptographically random, identity-bound, digest-stored,
  expiring, single-use and throttled. Existing v1 reset request fields and
  six-digit client entry flows are preserved. Email confirmation requires the
  issuance email; mobile already supplies it, and the web request now does too.
  Legacy unscoped six-digit lookups fail closed. Email and phone reset purposes
  must not be interchangeable.
  SMS registration and recovery use explicit separate purposes. Superseded
  recovery challenges remain revoked through expiry and cannot fall back to
  registration OTP verification.
  Public Firebase phone verification requires a verified, unexpired ID token
  whose signed phone claim matches the requested phone. Registration cannot
  overwrite an existing account; existing users must use recovery.
- Provider callbacks may mutate payment state only with authenticated,
  authoritative evidence matching the frozen checkout intent. Browser status,
  redirect parameters and unverified posted status are not payment evidence.
- Settlement is serialized and terminal paid state cannot be downgraded by
  duplicates or late events. Routing and payable settlement use the same
  stored collection mode instead of a later shop-setting change.
  Reusable catalog offerings are settled per verified purchase reference,
  not globally or once per customer's lifetime. Independent gift-card
  purchases and membership renewals remain possible. Partial wallet
  contributions do not block verified provider settlement of the remainder.
- Payment initiation must reject unauthorized identities and country/tenant
  configuration before contacting providers.
- Provider TLS certificate validation is enabled. Supported amounts must be
  exact; unsupported precision/currency fails instead of rounding up.
- Error responses and application logs omit credentials, callback payloads,
  provider response bodies and internal source paths.

## Explicitly unavailable integrations

RazorPay, PayTabs, Payu, MercadoPago, Mollie, Moyasar, PayFast, Iyzico,
Maksekeskus, ZainCash and Orange do not yet have the complete authenticated,
authoritative amount/currency/merchant/payable verification needed here.
Both initiation and callbacks return a safe HTTP 503 rather than pretending
success. Their source, route names and historical data remain intact.

Re-enablement requires a provider-specific verifier and fixtures, not removing
the gate or trusting a posted status. No provider was replaced by another.

## PayPal requirements

PayPal verifies webhook signatures using PayPal's own
`/v1/notifications/verify-webhook-signature` endpoint. Checkout/order/capture
retrieval uses the configured merchant credentials over validated TLS.
The configured mode must be `sandbox` or `live`, with client ID/secret plus:

- `paypal_{mode}_merchant_id` (or `paypal_merchant_id`)
- `paypal_{mode}_webhook_id` (or `paypal_webhook_id`)

These are names only; no credentials were accessed or requested during this
work. Missing configuration fails explicitly. Merchant, checkout reference,
currency and exact captured amount must match the frozen intent. An approval
event alone cannot mark a payment paid. Unknown legacy intents lacking a
checkout reference fail closed and require authorized reconciliation.

PayPal collection in a currency different from the payable's frozen currency
is unavailable until an explicit conversion quote can be frozen and verified.
No fallback USD conversion is silently applied. Fractional JPY/HUF/TWD
amounts are rejected instead of rounded; supported two-decimal amounts are
formatted without floating-point ceilings.

## Limits

Passing the isolated suite is not certification of live provider credentials,
network behavior, production schema, concurrency on the original database
engine, or original client builds. No original production data or historical
accounting was recalculated, and nothing was deployed. Original booking and
product-order baseline verification remains a separate approved task.

The completed local verification passed 55 tests with 382 assertions, and
PHP syntax validation passed for original app/route and isolated-test
files. Regression fixtures include new-phone registration, revoked reset
challenges, multiple customers purchasing the same catalog offering,
same-customer gift-card repurchases and membership renewals, split
wallet/provider checkout, missing/wrong-phone/expired Firebase proof,
and exact provider decimals such as 19.99 and 0.29. PHPUnit reports a
pre-existing PHP 8.4 implicitly-nullable-parameter
deprecation in `WalletHistoryService`; it is not a failed security test.
The existing Node API and Canvas workflows still start cleanly; this does
not imply that the original Laravel application has been launched or deployed.