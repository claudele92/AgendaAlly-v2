# Development payment capability representation

Current MVP classifications and remaining work are authoritative in
[AgendaAlly MVP readiness](agendaally-mvp-readiness.md). MySQL 8/InnoDB is selected;
tested source financial/DDL requirements pass, not whole-product production
readiness. The catalog/owned-development details below remain descriptive, not
permission to activate providers.

This matrix describes the original AgendaAlly payment contract and the
owned local development preview. Catalog presence is not proof of provider
availability, credentials, successful collection or settlement.

| Capability | Exists in original source | Current preview | Existing source/model | Development representation needed |
| --- | --- | --- | --- | --- |
| Cash / offline | Yes. The supported seller-confirmed offline method. It is not a gateway charge. | Active for local checkout; no provider request is made. | `Payment::TAG_CASH`; booking/order transaction has no external payment IDs. | Keep the single active cash method. A pending seller acceptance/order must not be described as a confirmed cash collection. |
| Internal wallet / balance | Yes. Wallet balances/history, wallet contribution to checkout, top-up/withdrawal workflows exist. Wallet is not a payment provider. | Synthetic internal balance/history may exist for development; this catalog does **not** activate wallet checkout or claim external funding. | `Wallet`, `WalletHistory`, `Transaction`, `Payment::TAG_WALLET`. | Keep the catalog entry inactive. Do not seed a paid top-up, activate contribution, or imply funding. Distinguish an existing balance from a charged method. |
| Standalone bank transfer | No standalone bank-transfer rail was found. Maksekeskus supports provider-managed banklinks, not generic offline bank transfer. | Unavailable. | No bank-transfer `Payment` tag; banklinks are part of `maksekeskus`. | Do not add or advertise a bank-transfer option. |
| Stripe / PayPal | Yes. Original web/mobile clients, services and configuration paths exist. | Present only as inactive catalog entries; payment mode is disabled. No credentials or provider calls. | `Payment`, `PaymentPayload`, `PlatformPaymentConfig`; `StripeService`, `PayPalService`; development provider policy. | Keep explicitly disabled. Sandbox mode is separately limited to Stripe/PayPal and requires correctly scoped test configuration; this seed does neither. |
| Flutterwave / Paystack | Yes. Original initiation services/routes exist. | Inactive and unavailable. | `Payment::TAG_FLUTTER_WAVE`, `TAG_PAY_STACK`; their payment services. | Keep disabled; do not imply tested sandbox behavior or configure credentials. |
| Orange Money / MTN Mobile Money | Yes. Original shop-direct and platform collection configuration paths exist. | Inactive and unavailable. | `ShopPayment` for vendor-direct credentials; `PlatformPaymentConfig` for platform collection. | Keep disabled. Orange initiation is unavailable because authoritative authenticated callback/status verification is incomplete. Do not configure shop/platform credentials or infer activation from the catalog row. |
| Other catalog gateways | Yes: PayTabs, ZainCash, Mercado Pago, Razorpay, MoyaSar, Mollie, Iyzico, Maksekeskus, PayFast and PayU are named in the original payment catalog. | Inactive and unavailable. | Original `PaymentSeeder` tags/services and provider policy. | Preserve catalog identity as disabled entries only; do not replace a disabled provider with another or claim a functioning payment flow. |
| Booking collection | Yes. Appointment checkout submits service/booking context. | Offline cash selection only; no fake provider transaction. | Booking, `Transaction`, payment controller and service checkout. | Treat booking status, payment status, collection and settlement as separate facts. |
| Product-order collection | Yes. Cart checkout creates commerce orders and may split across seller shops. | Offline cash order fixtures only; no fake provider transaction. | `Order`, `OrderDetail`, `Transaction`, cart-scoped payment listing. | Preserve per-shop/cart selection semantics. A seeded order or seller transition is not evidence of platform collection. |
| Vendor-direct vs platform collection | Yes, with different credential ownership. Orange/MTN are shop-credential rails; other gateway credentials are platform-level. | Neither route is configured. | `ShopPayment`, `PlatformPaymentConfig`, transaction routing snapshots. | Do not seed credentials, enable merchant routes or equate a vendor receivable with platform-held funds. |
| Platform fees / vendor payable | Yes. Ledger/accounting rows record fees and liabilities separately from transfers. | Synthetic pending offline ledger examples only where already seeded. | `PlatformFeeLedgerEntry`, transactions and order/booking fee snapshots. | Label synthetic or pending ledger state; never represent it as a charge or remittance. |
| Refund / reversal | Yes. Original order refund workflows exist with hardening limits. | No provider refund demonstrated or seeded by this catalog. | Order-refund service, `Transaction`, order records. | No simulated refund success, provider callback, or reversal. |
| Payout / settlement | Request/bookkeeping and liability records exist; independent real payout rails and reconciliation are not established. | Only an explicitly synthetic pending request may exist; no transfer or successful settlement. | `Payout`, wallet history, ledger and provider settlement services. | Keep pending/unapproved and visibly offline if shown. Never seed paid-out state or create a fake transfer. |

## Seed behavior and checkout safety

`DevelopmentPaymentCatalogSeeder` runs inside the owned, explicitly opted-in
SQLite development seeder. It keeps the existing cash checkout entry active
and uses `firstOrCreate` for every other original payment tag as inactive with
`sandbox=false`. It does not populate provider credentials/payloads,
country/shop merchant configurations, transactions, callbacks, refunds or
payouts. Repeated seeding is idempotent and does not overwrite an existing
provider's status/configuration.

The real REST checkout list filters active methods. For shop checkout it also
requires an active country allowance; Orange/MTN additionally require that
shop's enabled, valid credentials. Global provider listing and payment detail
also enforce the development provider policy. Development payment mode defaults
to `disabled`; only Stripe/PayPal are allowlisted for a separately configured
non-production sandbox. Consequently, inactive catalog rows are not checkout
choices and none of those conditions is changed here. Wallet is separately
treated as an internal balance by the REST listing, so its inactive row cannot
become an active balance contribution.

Focused isolated verification:

```sh
cd .migration-backup/backend
php vendor/bin/phpunit -c phpunit-development.xml tests/Development/DevelopmentPaymentCatalogTest.php
```

The test uses only its own guarded temporary SQLite file; it does not seed or
modify the active development database and makes no provider/network calls.