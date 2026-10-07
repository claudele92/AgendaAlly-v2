# Development country/payment policy fixture

This fixture creates reproducible assignments in the existing
`country_payments(country_id, payment_id, active)` pivot for Cameroon (`cm`),
Nigeria (`ng`), Ghana (`gh`) and Burkina Faso (`bf`). Each listed pair is
marked active as a **DEVELOPMENT POLICY ASSIGNMENT**: policy may consider the
method for that country. It is not evidence of legal approval, provider
availability, currency support, merchant onboarding, integration readiness,
configuration, or checkout eligibility.

The fixture deliberately does not create or edit countries, currencies,
payment-catalog rows, provider configuration, credentials, orders, bookings,
transactions, wallet balances, payouts or any finance records. It inserts or
activates only pairs in the matrix below. Unlisted pairs—including other
assignments for these four countries—and assignments for every other country
are left unchanged. Catalog activation state is never changed.

## Assignment matrix

| Country | Development policy assignments |
| --- | --- |
| Cameroon | Cash, Wallet, MTN MoMo, Orange Money, Paystack, Flutterwave |
| Nigeria | Cash, Wallet, MTN MoMo, Paystack, Flutterwave, Stripe, PayPal |
| Ghana | Cash, Wallet, MTN MoMo, Paystack, Flutterwave, PayPal |
| Burkina Faso | Cash, Wallet, MTN MoMo, Orange Money, Flutterwave, Paystack |

The country differences are intentionally synthetic exercises of an allowlist,
not researched commercial recommendations. Method names and assignment
presence must not be marketed as provider-country availability.
**Provider-country availability is UNKNOWN for every assigned external
provider.**

## Assignment versus operational readiness

“Known local catalog” below refers only to the read-only development catalog
snapshot recorded by the payment architecture audit; this assignment seeder
does not query or change runtime activation. A different database may have
different catalog/configuration state. No credentials or provider configuration
values were read for this fixture.

| Assigned methods | Known local catalog globally active? | Integration/readiness | Vendor configurable? | Platform configured by this fixture? | Supported transaction currencies represented? | Collection mode / configuration owner | Usable at checkout now? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Cash | Yes in the audited development snapshot; runtime state is not checked by this seed | Offline/manual path, not an external integration; custody/readiness is not certified | No meaningful gateway credentials; a generic shop row is not the cash policy | No gateway configuration applies | Resolver requires a valid authoritative transaction currency; no FX | Cash/offline; no external gateway charge | Eligible for the verified owned Vendor business context; this assignment alone does not establish eligibility or payment receipt. |
| Wallet | No in the audited development snapshot; runtime state is not checked by this seed | Internal balance mechanism, not provider readiness | No; wallet is not vendor merchant configuration | No external platform gateway configuration; internal balance is a distinct mechanism | Resolver requires an actor wallet in the authoritative transaction currency; no FX | Wallet/internal balance; not equivalent to external settlement | No in the audited catalog snapshot; independently gated and not enabled by this assignment. |
| MTN MoMo | No in the audited development snapshot; runtime state is not checked by this seed | Code-level adapter represented, NOT commercial certification | Yes, an existing shop-credential path exists; this fixture adds no shop config | No; this fixture creates no country platform configuration | Resolver requires exact collector-configured currency; provider-country approval remains UNKNOWN | Vendor-direct only with an actual shop configuration, or platform collection only with actual country platform configuration | No in the audited catalog snapshot; configuration, currency, environment and provider gates remain independent. |
| Orange Money | No in the audited development snapshot; runtime state is not checked by this seed | NOT CERTIFIED; initiation remains unavailable in the audited code | Yes, an existing shop-credential path exists; this fixture adds no shop config | No; this fixture creates no country platform configuration | UNKNOWN; an ad-hoc configuration currency is not a supported-currency capability declaration | Vendor-direct or platform configuration architecture exists; charge path remains unavailable | No in the audited source state; assignment cannot enable the unavailable charge path. |
| Paystack | No in the audited development snapshot; runtime state is not checked by this seed | NOT CERTIFIED; source paths are not verified integration readiness | No; generic vendor credential rows are not used as its configuration | No; this fixture does not create global payload/configuration | UNKNOWN; provider request checks are not a normalized capability registry | Platform-managed global provider configuration only | No in the audited catalog snapshot; assignment does not activate or configure it. |
| Flutterwave | No in the audited development snapshot; runtime state is not checked by this seed | NOT CERTIFIED; source paths are not verified integration readiness | No; generic vendor credential rows are not used as its configuration | No; this fixture does not create global payload/configuration | UNKNOWN; provider request checks are not a normalized capability registry | Platform-managed global provider configuration only | No in the audited catalog snapshot; assignment does not activate or configure it. |
| Stripe | No in the audited development snapshot; runtime state is not checked by this seed | Code-level adapter represented, NOT commercial certification | No; vendor rows are not Stripe credentials | No; this fixture does not create global payload/configuration | Narrow foundation declares USD, CAD, EUR, GBP and retains native precision checks; country approval UNKNOWN | Platform-managed global provider configuration only | No in the audited catalog snapshot; assignment does not activate or configure it. |
| PayPal | No in the audited development snapshot; runtime state is not checked by this seed | Code-level adapter represented, NOT commercial certification | No; vendor rows are not PayPal credentials | No; this fixture does not create global payload/configuration | Foundation declares the existing native verification currency list; country approval UNKNOWN | Platform-managed global provider configuration only | No in the audited catalog snapshot; assignment does not activate or configure it. |

For Paystack, Flutterwave, Stripe and PayPal, payment execution depends on
separate platform configuration, environment, provider, currency, collection,
and transaction-specific checks. The fixture does not inspect credential
presence and never prints configuration or secret values. “NOT CERTIFIED” is
not a claim about provider commercial availability.

### Cash and Wallet compatibility

Cash and Wallet are included in the pivot so the development policy fixture
expresses a uniform matrix. Existing `Country::activePaymentIds()` behavior
also treats globally active Cash and Wallet as country-independent exceptions.
This fixture does not alter that accepted behavior. The shared resolver records
the compatibility exception explicitly as
`payment_eligibility.internal_country_policy=legacy_compatibility`, and can
evaluate strict country policy without enabling it by default. Wallet
remains subject to its independent global activation and internal-balance
rules; cash remains an offline/manual flow. A future business-policy decision
should decide explicitly whether those methods should be restricted by the
country pivot, rather than changing compatibility as a side effect of seeding.

## Safe, narrow command

After the ordinary guarded development database bootstrap has already
completed, run only this command from the repository root:

```sh
cd .migration-backup/backend
php artisan development:payment-country-policy
```

The command requires `APP_ENV=local`,
`DEVELOPMENT_MODE=true`, and
`AGENDAALLY_DEVELOPMENT_DATABASE=true`, plus the existing ownership marker,
reviewed migration fingerprint, SQLite path, and exclusive database lock. It
fails closed if any check fails. It does not run migrations or call the general
demo seed. Its aggregate completion message reports assignment counts only.

The operation is idempotent: existing active pairs are not rewritten, inactive
listed pairs are activated, and missing listed pairs are inserted. Repeating it
does not change the resulting pivot rows. If a required country or payment
catalog entry is missing, it reports an error and rolls back; it does not seed
or repair either catalog.

The isolated test
`tests/Development/DevelopmentCountryPaymentPolicyTest.php` runs the fixture
against a disposable in-memory SQLite schema. It verifies both-pass stability,
the precise scope of writes, preservation of catalog activation, and the
representative country differences without opening the local development DB.