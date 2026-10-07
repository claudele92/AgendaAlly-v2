# Current provider capability and readiness matrix

2026-10-03. Native source plus read-only owned-development evaluation; no provider
calls, activation or live certification. Current data supersedes the older
18-method catalog's activation observations.

Evidence: `.local/payment-architecture-completed-readiness.json` and its
query-only evaluator `.local/payment-architecture-readonly-observation.php`.
Native sources: `PaymentEligibilityService`, `PaymentContextFactory`,
`config/payment_eligibility.php`, `EnvironmentPolicy`, payment controllers/
services, `BaseService`, and `app/Traits/PaymentRefund.php` under
`.migration-backup/backend`.

## Cameroon/XAF — both actual Product and Service contexts

Public native runtime flags: `APP_ENV=local`, `DEVELOPMENT_MODE=true`,
`PAYMENT_MODE=disabled`. No `shop_payments`, `platform_payment_configs` or
`payment_payloads` rows exist. Credential values were not retrieved.

| Rail | Integration today | Platform / Vendor direct | Product / Service | Global / CM | Declared XAF | Collector available / currency / enabled | Current checkout |
| --- | --- | --- | --- | --- | --- | --- | --- |
| MTN (`mtn`) | Initiation, authoritative status lookup and frozen-merchant verification | Both, conditional | Both | On / allowed | Yes, explicit capability | Neither collector exists / no registered currency / not enabled | No: environment disabled, collector not configured, collector disabled, currency unsupported |
| Orange (`orange`) | Initiation deliberately throws 503; no authoritative callback verifier | Configuration/routing scaffold for both, not operational | Neither eligible: verifier unavailable | On / allowed | No declaration | Neither collector exists / no registered currency / not enabled | No: integration unavailable, environment disabled, transaction type unsupported, collector absent/disabled, currency unsupported |
| Flutterwave (`flutter-wave`) | Initiation and verified callback/provider lookup | Platform only | Both when eligible | On / allowed | Unknown: capability list is null | No global payload / not applicable / no operational merchant | No: environment disabled, collector not configured, currency support unknown |
| Paystack (`paystack`) | Initiation, HMAC callback and provider lookup | Platform only | Both when eligible | On / allowed | Unknown: capability list is null | No global payload / not applicable / no operational merchant | No: environment disabled, collector not configured, currency support unknown |
| Stripe (`stripe`) | Initiation, signature plus authoritative session/account verification | Platform only | Both when eligible | On / denied | No: narrow declared list excludes XAF; zero-decimal backstop | No global payload / not applicable / no operational merchant | No: environment disabled, country policy denied, collector absent, current currency check unknown because country gate short-circuits |
| PayPal (`paypal`) | Initiation, provider signature verification and authoritative capture | Platform only | Both when eligible | On / denied | No: declared list excludes XAF | No global payload / not applicable / no operational merchant | No: environment disabled, country policy denied, collector absent, current currency check unknown because country gate short-circuits |
| Cash (`cash`) | Offline/manual rail; not an online gateway | Offline, not either merchant mode | Both | On / legacy compatibility | Authoritative business currency | Not applicable | Offline eligible; database paid state is not custody evidence |
| Wallet (`wallet`) | Internal funded-balance rail; accepted containment baseline | Internal, not either merchant mode | Both | On / legacy compatibility | Requires actual owner's same-currency Wallet | Actor-specific, not merchant credentials | Capability present; anonymous evaluator is not a buyer-readiness test |

For electronic rails, current Product and Service results agree. In a
Vendor-direct evaluation Flutterwave, Paystack, Stripe and PayPal additionally
fail `collection_mode_unsupported`. There is no implemented Shop credential
model for those rails. MTN's XAF diagnostic is a missing configured collector
currency, **not** absence of declared XAF capability.

## All remaining catalog providers

| Catalog rail | Native operational capability / P-S types | Platform / direct | Current global / CM | XAF and collector state | Disposition |
| --- | --- | --- | --- | --- | --- |
| PayU (`payu`) | Not in verified READY set; no eligible P/S types | Legacy platform shape / no direct | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| Razorpay (`razorpay`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| PayTabs (`paytabs`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| Mercado Pago (`mercado-pago`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| Moyasar (`moya-sar`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| Mollie (`mollie`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| ZainCash (`zain-cash`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| Iyzico (`iyzico`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| Maksekeskus (`maksekeskus`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |
| PayFast (`pay-fast`) | Same | Same | Off / denied | Unknown / unconfigured | LEGACY; unavailable |

These ten currently fail global activation, integration, environment, country,
transaction type, collector configuration and unknown currency. Direct adds an
unsupported collection mode. Retaining an SDK/service/form does not establish an
authenticated, enabled online rail. No external provider marketing claims were
used to fill these capability gaps.

## Verification and refund matrix

| Rail | Callback trust / authoritative confirmation | Provider-level refund today | Principal/fee/payable/payout completeness |
| --- | --- | --- | --- |
| MTN | Callback only triggers provider lookup; frozen merchant row/fingerprint, reference, successful status, amount/currency are checked | No native refund dispatcher branch | Partial collection; no complete external refund/remittance/reconciliation |
| Orange | No trusted signature/status verifier; callback refuses and initiation fails closed | No dispatcher branch | Configuration scaffold, not safe online settlement |
| Stripe | Signature + authoritative session/payment/account facts bound to local intent | Legacy adapter checks succeeded; global current credentials/local identifiers, not a durable common refund intent | Partial; not complete liability/refund/reconciliation |
| PayPal | Provider signature verification and capture/merchant/order/amount/currency | Legacy adapter decodes response to array then calls response methods: local confirmation is broken; no common refund state machine | Incorrect refund confirmation; partial architecture |
| Flutterwave | Secret-hash authentication plus authoritative provider transaction lookup | Legacy adapter expects completed result | Partial; currency declaration and merchant configuration absent |
| Paystack | HMAC plus authoritative provider lookup and local intent match | Legacy code uses `flw_sk`, not Paystack's key, and marks Transaction progress on HTTP 200 | Incorrect refund semantics; unsafe to activate |
| Cash | No provider callback; manual/native cash state | No provider refund; offline cash policy required | Paid state can be mistaken for platform custody |
| Wallet | No external callback for internal spend; top-ups use their external provider verifier | Accepted commerce reimbursements are internal credits, not provider returns | Accepted caller containments do not certify reserve/reconciliation/payout |
| PayU / Mercado Pago / PayFast | Not enabled/verified operational rails | No common dispatcher branch | Legacy; unavailable |
| Razorpay / PayTabs / Iyzico | Not enabled/verified operational rails | Legacy HTTP/adapter-result paths exist, without a shared authoritative refund lifecycle | Legacy; unavailable |
| Moyasar / Mollie / Maksekeskus | Not enabled/verified operational rails | Legacy adapter status checks exist; original intent/collector/payout coupling absent | Legacy; unavailable |
| ZainCash | Not enabled/verified operational rail | Explicitly excluded by dispatcher | Legacy; unavailable |

`BaseService::afterHook` requires trusted server proof, locks the process/payable,
checks exact positive amount/currency/reference/payment/target, stops same-reference
paid replay and guards another paid intent (with a narrow exact partial-Wallet
contribution exception). These are source-level protections, not production
race certification or a durable external-refund/payout ledger.

Neither platform nor Vendor-direct is financially complete end-to-end. No
Cameroon/XAF electronic rail is checkout-ready in this development instance.
MTN is the only declared XAF-capable Shop-credential rail with a verifier;
that does not establish merchant approval, live success or safe accounting.

## Callback binding details and limits

| Rail | Authenticity / merchant | Reference, intent, method, payable, Shop, Transaction, amount/currency, mode |
| --- | --- | --- |
| MTN | Unsigned callback is only a lookup trigger, not proof; authoritative lookup uses exact original Shop/platform config type/id and fingerprint | Frozen reference plus provider facts feed shared matcher; owned persisted target/Shop and merchant scope, locked payable, positive amount/currency and competing paid-Transaction guard |
| Stripe | Signature plus authoritative session/payment under platform account | Provider session/reference and metadata bind local intent/method/target and frozen amount/currency; shared locked-target/other-paid guards; platform-only initiation |
| PayPal | Provider webhook signature verification; authoritative capture checks payee merchant and local order | Capture/reference and local intent/amount/currency bind persisted target; shared guards; platform-only initiation |
| Flutterwave | Secret-hash authentication plus authoritative transaction/account facts | Verified provider reference/local intent/method/target/amount/currency feed shared guards; platform-only initiation |
| Paystack | HMAC authentication plus authoritative transaction lookup | Verified provider reference/local intent/method/target/amount/currency feed shared guards; platform-only initiation |
| Orange | No authoritative verifier | Refuses settlement; no successful callback route certified |

The shared match is of payment method and model/intent identifiers, not a
separately authenticated immutable **Transaction-ID allocation**. Transactions
are selected/guarded through the persisted payable and intent. Shop and collection
mode come from owned target/frozen routing, not a trusted browser Shop/mode field.
Global-payload credentials are current platform configuration, not a complete
merchant-revision recovery contract like MTN's stored fingerprint.

Same-reference paid replay returns already-settled; a later non-paid callback
cannot downgrade a paid intent. However progress/canceled/rejected states are
not all mutually terminal: a later authoritatively successful result can still
advance to paid if no other paid intent blocks it. No complete charge/refund/
payout out-of-order state machine or failed-merchant-rotation recovery exists.
Local guards do not prove external transport deduplication, one fee allocation
across partial funding, or production-engine race safety.