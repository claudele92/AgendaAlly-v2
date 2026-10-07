# Payment Phase 2 — immutable collection, custody, allocation and liability

**2026-10-03 — OUTCOME B: SCHEMA REQUIRED. TWO-LAYER DIRECTION APPROVED
CONCEPTUALLY; FINAL DESIGN REVISED; MIGRATION NOT AUTHORIZED.**

Phase 1 is accepted, not reopened. This pass completed the authorized current-source
trace and minimum schema proposal. **No application implementation, migration,
schema change, financial operation, configuration change or activation occurred.**
This report does not claim the Phase 2 accounting invariants are implemented.

## 1. Decision and evidence standard

The current schema cannot represent an immutable, Shop-specific economic payment
with its independently verified funding contributions and a once-only allocation
identity that survives Transaction replacement/deletion.

Booking's boolean is a useful **creation-time routing preference snapshot**, not
a four-mode custody/collector/merchant/amount allocation contract. Product has no
equivalent boolean. Provider intent JSON preserves useful verification evidence,
but is not the common, retained allocation boundary for Cash, Wallet and providers.
Existing fee uniqueness is `(transaction_id, entry_type)`, not one economic payment.

Therefore this is the required schema stop, not a reason to put authoritative
financial context into notes, mutable Booking data, today's Shop preferences,
or an in-memory claim. No partial non-schema correction was applied.

Evidence below is current PHP source and **read-only actual development schema**.
Financial consequences are source-derived, not production exploitation or provider
operations. Earlier audit findings remain historical evidence; no broad audit,
unrelated authorization work or accepted containment investigation was restarted.

## 2. Current authoritative source map

Paths in this section are relative to `.migration-backup/backend/`.

| Source | Current responsibility / limitation |
|---|---|
| `app/Models/Order.php:123–159,278–290` | Private fulfillment state protects one native fulfillment batch. It is **not** collector/custody/allocation state. Monetary columns and `seller_fee` are commerce values, not immutable payment snapshots. |
| `app/Models/Booking.php:95–107,216–218` | `collect_via_platform` boolean cast; guarded only by ID, without immutable financial setters/events. Derived seller fee reads current Booking monetary fields. |
| `app/Models/Transaction.php:62,109–111` | Payable, method, price, reference/status; no currency/collector/credential-owner/economic-allocation identity. Payment tag is a relation to current Payment. |
| `app/Models/Payment.php` | Catalog method/provider tag and current active state; not collector identity. |
| `app/Models/Shop.php:146–155`; `app/Services/ShopServices/ShopActivityService.php:91` | Current Shop preference, editable through native collection controls. New model default is platform collection; migration default/legacy data are a different question. |
| `app/Services/PaymentEligibility/PaymentContextFactory.php:20–105` | Owned targets/business country/currency. Product uses current Shop mode. Booking overrides that mode with its boolean. Multi-Shop cart context checks currency and represents mixed electronic preference as `mixed`. |
| `app/Services/PaymentEligibility/PaymentEligibilityService.php:39–105` | `offline` for Cash, `internal` for Wallet; electronic `platform`/`vendor_direct`. Current electronic eligibility rejects more than one Shop, including same-mode carts. Eligibility is not custody proof. |
| `app/Services/BookingService/BookingService.php:55–206,278–368,482–521` | Before creation derives Shop, service amounts, branch and boolean from server records. Native create/update/payment paths do not make these a complete immutable financial contract. |
| `app/Http/Requests/Booking/UpdateRequest.php:19–58`; Admin/Seller Booking controllers | Validated HTTP updates do **not** expose the boolean directly. They do expose currency/service/extras/data updates. The model/service accepts arrays without an immutable snapshot boundary; client-editable `data` is unsuitable for one. |
| `app/Services/TransactionService/BookingPaymentAuthority.php`; `TransactionService.php:377–446,449–524` | Phase 1 Cash/Wallet-only generic Booking paid authority and sibling guards remain. Native Wallet debit/history; existing paid same-method replay no-op. No new custody model. |
| `app/Services/OrderService/OrderService.php:105–116,168–198,229–347` | Trusted Product creation/calculation. Native Shop tax/percentage, fees, delivery/coupon/tips and Wallet payment; no frozen collector/allocation. |
| `app/Services/OrderService/CartOrderService.php:35–218` | One Order per cart/Shop/Customer, per-Shop currency, shared process method/reference. Does not transfer the process's collection fields into each Order/Transaction. |
| `app/Services/PaymentService/BaseService.php:65–118,260–346,430–583` | Verified process/amount/currency/provider/model binding; locks and terminal replay protections. Partial Wallet exception is contribution recognition, not once-only fee allocation. |
| `app/Services/PaymentService/BaseService.php:592–682,858–914,1085–1147,1161–1258` | Freezes intent routing; determines actual configuration source; partial Wallet debit; provider amount remainder. Booking config lookup still reads its boolean; Cart can use intent flag, otherwise Shop. Country/configuration are looked up later. |
| `app/Services/PaymentService/MtnService.php:82–97,139–150`; `StripeService.php:169–179`; native Orange/PayPal adapters | Process carries provider-specific binding/reference evidence. MTN fingerprint is a digest of configuration including secret inputs, not a typed non-secret collector-owner record. Stripe stores merchant ID evidence. These are not a universal allocation identity. |
| `app/Models/ShopPayment.php`; `PlatformPaymentConfig.php`; `PaymentPayload.php` | Shop/country/global configuration ownership can be resolved at initiation. Rows are editable; secrets remain in existing protected configuration, not in new financial snapshots. |
| `app/Traits/Payable.php:19–54` | `updateOrCreate` identity is payable + payment method. No unique database constraint for that key; method changes can produce another Transaction. Single `transaction` relation is not the complete contribution set. |
| `app/Observers/TransactionObserver.php:35–154` | Paid Order/Booking gets full current `service_fee` per Transaction. Booking alone gets full current seller fee payable if boolean is true, **without checking Cash/Wallet versus actual merchant custody**. |
| `app/Models/PlatformFeeLedgerEntry.php:39–71,116–140`; ledger create/entry-type migrations | Fee/payable/adjustment; pending/collected/waived. Unique per Transaction/type. Transaction FK has cascade delete. Shop balance aggregate is not currency-partitioned. No economic payment uniqueness. |
| `app/Services/OrderService/OrderStatusUpdateService.php:50–126,215–237` | Accepted authority, locked/CAS once-only fulfillment retained. Native funded batch still credits first Admin Wallet by gross, regardless of collection mode. Existing 12 unverified Orders cannot enter that batch. |
| `app/Services/PaymentToPartnerService/PaymentToPartnerService.php:25–137,157–246` | Admin seller settlement sibling: recomputes native seller fee; Wallet transfers from current actor to current Shop seller; Cash creates paid bookkeeping. Neither branch consumes immutable custody or fee-ledger liability. |
| `app/Http/Requests/Payout/StoreRequest.php`; Seller `PayoutsController.php:49–59`; `PayoutService.php:93–203` | Request price is not capped/reserved by an economic Vendor entitlement. Phase 1 approval conserves approver→creator Wallet value atomically; no collector-aware allocation reservation/remittance exists. |
| `app/Services/BookingService/BookingCancellationSettlement.php:26–84`; `BookingService.php:645–669` | Accepted terminal cancellation/refund claim retained. Wallet original payment caps refund; electronic branch is native cancellation fee, not provider refund. Reverses each existing payable row by original amount, per original Transaction. |
| `app/Services/OrderService/OrderRefundService.php`; `app/Traits/PaymentRefund.php:41–85,412–435` | Accepted internal refund finality retained. Provider refund dispatch obtains current global payload and legacy reference; no original collector-aware common refund allocation. |

No new independent immediately exploitable financial P0 was confirmed in this
narrow trace. The custody/allocation deficiencies are the already identified
Phase 2 architecture gaps, not newly exercised financial exploits.

## 3. Product versus Booking: the actual asymmetry

### Product

Persisted Order: Shop/Customer/currency, monetary components, cart/parent and
fulfillment finality. Persisted Transaction: method, amount, provider reference
and status. **Neither has an immutable collector or collection-mode contract.**

Product eligibility uses current Shop collection preference. Electronic initiation
adds `collect_via_platform` and `checkout_collection_mode` to `PaymentProcess.data`,
but the native cart→Order path copies method/reference/status, not those fields.
Provider verification freezes/checks its own intent; it does not produce durable
Order allocation parity with Booking.

The paid observer writes a service-fee entry, **not an Order Vendor-payable entry**.
Later eligible fulfillment writes gross into first Admin Wallet. That internal
entry does not establish where provider/Cash principal actually went.

### Booking

`beforeSave` derives `collect_via_platform` from the Shop at Booking creation.
Eligibility and gateway routing read that Booking value instead of later Shop
toggle; observer reads it for payable creation. Ordinary Shop preference changes
therefore **do not themselves change that Booking boolean**.

It does not identify Cash/Wallet, provider, actual merchant owner, verified receipt,
funding legs or immutable gross/fee/entitlement. There is no model/service
immutability rule. Validated ordinary HTTP updates omit the boolean, which must
not be misreported as a demonstrated public boolean-edit endpoint; their allowed
currency/extras/service updates can still change later-derived monetary meaning.

`Booking.data`/notes are mutable commerce input, not a safe financial workaround.
Creation-time intent/preference is not proof of paid custody. The historical boolean
migration default `false` is not proof that an old Booking used Vendor credentials.

### Meaning can still drift

Product live-mode decisions can change after Shop preference changes. Both systems
can later read changed configuration/country/currency or mutable commerce amounts.
Existing intent checks can **reject** a mismatched proof/configuration; that is not
the same as having a retained original collector for accounting/refunds.
An already-created fee row's amount is not automatically recomputed by Shop toggles,
but a later different Transaction can create another row from current commerce.

## 4. Four-mode custody and accounting matrix

Retain native vocabulary rather than introduce provider-named modes:

| Canonical concept | Native value proposed for durable context | Principal destination / platform obligation |
|---|---|---|
| PLATFORM_MANAGED | `platform` | Verified collection through platform merchant relationship. Vendor entitlement held for Vendor can become platform payable, subject to native fulfillment/refund obligations. Gross is not pure platform revenue. |
| VENDOR_DIRECT | `vendor_direct` | Verified Shop merchant collection. **No platform payable for principal already received by that Vendor.** Platform fee obligation remains a receivable unless separately proven recovered. |
| CASH | `offline` | Native offline Cash semantics; treat Shop/Vendor as receiving principal, not platform electronic custody. Native paid bookkeeping is not proof of physical cash possession. No fabricated platform payable. |
| WALLET | `internal` | Customer internal Wallet debit; value remains in platform-managed internal accounting. Vendor economic entitlement must be tracked, without pretending a new provider receipt occurred. |

Provider is separate from mode: MTN/Orange can theoretically use platform or Shop
credentials. Cash/Wallet cannot inherit their custody from the Shop's electronic
preference. Gifts/membership coverage and discounts are native benefit/price
components, not proof of another electronic collection; do not invent a fifth
merchant mode or infer original benefit funding from current Booking fields.

**Current results:**

- Platform electronic Booking can produce payable, but only via boolean and
  mutable seller amount; no complete receipt-linked allocation.
- Vendor-direct Booking skips payable while its boolean remains false. There is
  no typed immutable merchant-owner context or automatic commission recovery.
- Cash-paid Booking with boolean true creates **false platform payable**.
  Cash Product has no analogous payable row, but eligible delivery still creates
  the native Admin gross credit without establishing platform cash custody.
- Wallet-paid Booking with boolean false omits platform payable although its
  funding is internal. With true it can produce one, but mixed funding can duplicate
  full amounts. Product Wallet has no coherent immutable Vendor-payable parity.
  **Wallet transfer/amount safety is not invalidated by missing liability accounting.**

## 5. Existing fee formulas: preserve, do not invent economics

`Utility::resolveServiceFee` uses the existing fixed/percentage setting.
Product calculates percentage commission from its native post-tax subtotal when
not subscription-funded; fixed service fee is distributed over Shop Orders,
percentage service fee is computed per Order. Booking has its per-service fee
and ServiceMaster commission component.

Current native seller accessors are:

```text
Booking V = total_price − service_fee − commission_fee − coupon_price
Order V   = total_price − (delivery_fee if IN_HOUSE)
            − service_fee − commission_fee − coupon_price − tips
```

**Current-source clarification:** the earlier audit's shorthand formulas omitted
Booking `commission_fee` and described Order deductions differently. The accessors
above are the current authority: Order does not separately subtract `total_tax`
there. Some public routes return zero for Order seller fee; a financial writer must
not use a request-dependent presentation accessor as its allocation authority.

The observer records **service_fee**, not all `commission_fee`. It does not transfer
or prove recovery of that commission. Keep service fee, native commission component,
Vendor entitlement, custody liability and payout distinct. Do not label every
deduction platform revenue or invent delivery/tax/coupon beneficiary rules.

Future allocation must freeze these native components and their existing fee
inputs at commitment, reconcile `G = native fees + V + explicitly identified
adjustments`, and fail closed on inconsistency. No fee-rate or beneficiary policy
was changed here.

## 6. Economic identity, duplication and downstream consequences

The actual ledger uniqueness is `(transaction_id, entry_type)`. Observer
`firstOrCreate` suppresses repeating the same row/type, but:

- another method/replacement Transaction has a different key;
- Wallet contribution and electronic remainder can each record the full fee and
  full Booking payable, rather than their funded shares;
- Transaction cascade deletion removes the supposedly durable ledger witness;
- process-terminal and paid-target checks protect verified settlement, not every
  native accounting writer or the economic identity after mutable/deletable records;
- cancellation reverses each payable row; once-only reversal does not repair
  already-duplicated original allocations.

Do not solve this by unique payable/type alone on the existing ledger: legitimate
base charges versus extra-time/tips, contributions, legacy duplicates and separate
Shop obligations need explicit identity. Do not reuse fulfillment state, refund
time or accepted payout status as that identity.

**Multi-Shop:** native cart checkout materializes one Order per Shop and later
copies a shared process reference to each. Current electronic eligibility denies
multi-Shop collection; BaseService also requires a single Shop for merchant
routing. This prevents choosing Vendor A's account for Vendor B today; it is not
an implemented multi-merchant split. Cash/Wallet Order materialization remains
Shop-scoped but lacks durable allocation contexts. Future identity must be per
Shop obligation, never merely per cart/reference.

**Refunds:** immutable mode/owner/provider/original receipt must identify platform
merchant versus Shop merchant; Cash has no provider refund and Wallet has internal
reversal. Existing global refund dispatch is insufficient. No refunds implemented.

**Payouts:** only platform-held Vendor entitlement may support a platform payable.
Deduct actual linked reversals and proven settlements, grouped by currency.
Cash/Vendor-direct principal must not be paid again. Native Payout/PaymentToPartner
do not reserve or consume that liability today; their bookkeeping statuses are not
external remittance proof. No rails/reservations/reconciliation were implemented.

## 7. Final revised design: approved accounting policy

The creator conceptually approved the two layers and explicitly supplied the
**platform-held-funds-first commission rule** on 2026-10-03. This supersedes the
earlier unresolved mixed-funding proposal. **Migration and implementation were
not authorized during this design pass.** Phase 2B subsequently approves this
baseline subject to its material-deviation gate; section 12 records the current
pre-migration stop. No third core accounting table or new general ledger is proposed.

Layer 1 is a durable once-only **Shop economic obligation**. Layer 2 is immutable
**funding contribution evidence**. Existing fee-ledger rows become linked,
append-only economic effects; this also makes future multiple partial reversals
representable without mutating original contribution evidence.

### 7.1 Definitions and allocation formulas

All amounts below are in **one frozen business currency**, never display currency:

```text
G = original gross Customer amount
C = total applicable AgendaAlly commission, calculated ONCE in the economic quote
E = original Vendor economic entitlement from the native economic contract
A = other explicitly identified native adjustments = G − C − E
P = sum of confirmed platform-controlled contributions (platform + internal)
D = sum of confirmed Vendor-controlled contributions (vendor_direct + offline)

Require: G >= 0; C >= 0; A >= 0; E >= 0; G = C + A + E; P + D = G.
S = min(P, C)                         commission satisfied from held funds
R = C − S                            Vendor commission receivable
AP + AD = A                          native adjustments by custody responsibility
Require: 0 <= AP <= P − S; 0 <= AD <= D − R.
M = P − S − AP                       platform-held Vendor entitlement/payable
VD = D − R − AD                      entitlement already held by Vendor
E = M + VD
```

For the examples with no other adjustments, `A=AP=AD=0`, hence
`M=max(P−C,0)`, `R=max(C−P,0)`, and `E=G−C`. **No negative payable.**
Funding never calculates another C; it only determines S/R/M/VD.

Native fee rates/calculation inputs are unchanged. The immutable quote must
explicitly name which native service/commission components belong to AgendaAlly
and which are other adjustments; do **not** infer beneficiary from the field name
`commission_fee`, or blindly SUM every seller deduction into platform revenue.
The traced service-fee ledger is an established platform obligation;
ServiceMaster `commission_fee` is a stored numeric price component (its
`getTotalPriceAttribute` adds it), not evidence of actual recovery.
Frozen native inputs/components and their beneficiary/custody responsibilities
are required for C/A/E reconciliation. An unproven native adjustment assignment
blocks finalization rather than silently creating a new fee/beneficiary policy.
The provided held-funds-first **commission** rule is fully defined even when
native non-commission adjustment disposition needs a separate source-backed quote.

Wallet evidence: `BaseService::walletPriceWithdraw:1123–1144` locks/debits the
Customer Wallet; `TransactionService::walletHistoryAdd:502–524` creates paid
withdrawal history/Transaction. There is no Shop merchant transfer in that
contribution. Thus its verified value is **platform-controlled internal value**,
not new electronic collection or proof of bank cash. Native Wallet movement,
positive-amount/finality/currency safeguards are retained.

Before complete funding, report actual P/D received and quoted G/C/E, but do not
recognize the full fee or full Vendor entitlement. Commission is quoted once;
economic recognition occurs once on complete verified coverage. An incomplete
Wallet-funded attempt retains Customer held-value/refund exposure, not a fabricated
full Vendor payable or commission receivable.

### 7.2 Exact proposed `commerce_payment_allocations`

Types are logical SQL types; IDs use the matching existing engine's BIGINT
representation. **All new authoritative monetary columns are signed BIGINT
atomic units at the parent `money_scale`**, with bounds 0..2^63−1 except signed
payable deltas. Example: major amount100 at scale2 stores10000; amount2.5 stores250.
Use the existing approved native charge/fee normalization, not a new rounding
policy; reject unsupported precision/range or inconsistent quote reconciliation.
JSON monetary metadata uses canonical unit strings, never JSON floating money.
SQLite DECIMAL has NUMERIC affinity, not guaranteed exact fixed-decimal arithmetic.
Integer units make the proposal's conservation checks exact on SQLite and the
production SQL engine. This changes only the **proposed new representation**,
not existing native money or historical rows. No FX or fee-policy redesign is
implied; every API/example amount is decoded with the retained original scale.
`TIMESTAMP` values use UTC; VARCHAR enums get DB CHECK constraints plus server
validation where supported. `NN` means NOT NULL, `N` nullable.

| Column | Type/null/default | Purpose / mutability |
|---|---|---|
| `id` | BIGINT PK, NN | Server-generated retained economic identity |
| `checkout_key` | CHAR(36), NN | Server-owned checkout group; immutable |
| `origin_type`, `origin_id` | VARCHAR(16), BIGINT, NN | `cart/order/booking`, immutable original economic anchor |
| `shop_id`, `vendor_user_id` | BIGINT, NN | Frozen Shop and Vendor beneficiary; not current Shop owner |
| `payer_user_id`, `local_client_id` | BIGINT, N | Registered payer FK; native local-client scalar reference for legitimate offline case |
| `country_id`, `currency_id`, `currency_code` | BIGINT, BIGINT, VARCHAR(8), NN | Original business country/charge currency, immutable |
| `money_scale` | SMALLINT, NN | Validated native quote scale, CHECK 0..8, immutable |
| `purpose`, `obligation_key` | VARCHAR(16), VARCHAR(64), NN | `base/tip/extra_time`; base key `base`; supplement key trusted distinct native obligation, immutable |
| `payable_type`, `payable_id` | VARCHAR(16), BIGINT, N | `order/booking`, both NULL or both set; cart binds exactly once |
| `gross_amount`, `commission_amount`, `vendor_entitlement_amount`, `adjustment_amount` | BIGINT atomic units, NN | G/C/E/A from one trusted quote; immutable from commitment |
| `native_components` | JSON, NN | Typed server-only fee inputs/components, native beneficiary and adjustment responsibilities; no raw request data/secrets |
| `policy_key` | VARCHAR(48), NN | `platform_held_first_commission`, immutable |
| `state` | VARCHAR(24), NN, `committed` | State machine below; not payment-method selection |
| `version` | BIGINT, NN, 0 | SQL CAS revision; server-only monotonically increasing |
| `original_platform_amount`, `original_vendor_direct_amount` | BIGINT atomic units, N | Original P/D; write once at full funding, never reduced on refund |
| `original_commission_satisfied`, `original_commission_receivable` | BIGINT atomic units, N | Original S/R; write once, never another C calculation |
| `original_platform_adjustment`, `original_vendor_adjustment` | BIGINT atomic units, N | Original AP/AD from native adjustment contract; write once |
| `original_vendor_payable` | BIGINT atomic units, N | Original M; write once |
| `committed_at` | TIMESTAMP, NN | Trusted initial commitment |
| `finalized_at` | TIMESTAMP, N | Once-only economic claim; never cleared by reversal/replacement |
| `created_at`, `updated_at` | TIMESTAMP, NN | Server timestamps; not monetary authority |

Foreign keys: Shop→`shops`, Vendor/payer→`users`, country→`countries`,
currency→`currencies`, all **ON DELETE RESTRICT / ON UPDATE RESTRICT**.
`local_client_id`, origin and polymorphic payable IDs are retained scalar
references, **not** cascading polymorphic FKs; trusted native binding validates
their existence/ownership. Local-client ID is private, not public financial input.

Unique constraints:

1. `(checkout_key, origin_type, origin_id, shop_id, purpose, obligation_key)`
   (explicitly approved Phase 2B correction; see section 12);
2. `(payable_type, payable_id, purpose, obligation_key)` once bound.

Indexes: `(checkout_key,id)`, `(shop_id,currency_id,state,id)`,
`(vendor_user_id,currency_id,state,id)`, `(country_id,currency_id,state,id)`.
CHECK monetary nonnegativity and `G=C+A+E`; finalized original totals/splits must
be either all NULL or all populated, satisfy section 7.1, and `P+D=G`.
Neither replacement Transaction ID nor provider receipt is an economic key.

### 7.3 Exact proposed `payment_collection_contexts`

| Column | Type/null/default | Purpose / mutability |
|---|---|---|
| `id`, `allocation_id` | BIGINT PK / BIGINT FK, NN | Retained funding contribution and its economic parent |
| `funding_key` | VARCHAR(96), NN | Stable server logical slot + attempt key; duplicate request uses same key |
| `funding_slot` | VARCHAR(32), NN | `wallet_contribution/selected_method`; native two-leg boundary |
| `confirmed_slot` | VARCHAR(32), N | NULL until confirmed, then the slot; write once, never cleared |
| `funding_event_key` | CHAR(36), NN | Trusted original provider intent or Wallet/Cash receipt group, not client-supplied reference |
| `receipt_claim_key` | CHAR(64), N, UNIQUE | Global once-only original receipt claim on its anchor context; safe identity hash, set once |
| `receipt_anchor_context_id` | BIGINT N, self FK RESTRICT | Shared receipt's anchor context; NULL on anchor, set once on member contexts |
| `collection_mode` | VARCHAR(16), NN | `platform/vendor_direct/offline/internal`, immutable at initiation |
| `custody_type` | VARCHAR(16), NN | `platform/vendor`, frozen actual resolved custody classification |
| `expected_collector_type`, `expected_collector_id` | VARCHAR(16), BIGINT, NN/N | `platform/shop`; Shop ID required for Shop, platform identity has NULL ID |
| `confirmed_collector_type`, `confirmed_collector_id` | VARCHAR(16), BIGINT, N | Validated evidence/native custody rule; write once at confirmation |
| `credential_owner_type`, `credential_owner_id` | VARCHAR(16), BIGINT, NN/N | `platform/shop/none`, original owner; none for Cash/Wallet |
| `payment_id`, `provider_tag` | BIGINT NN, VARCHAR(32) N | Frozen native method; provider NULL for Cash/Wallet |
| `configuration_source`, `configuration_reference`, `configuration_revision` | VARCHAR(32), VARCHAR(96), VARCHAR(96), N | Original non-secret config owner/source/row/version metadata; required for electronic contexts |
| `merchant_binding_reference` | VARCHAR(128), N | Safe original provider-authenticated merchant binding, when available; no credential/phone/account number |
| `payment_process_reference`, `provider_payment_reference` | VARCHAR(191), N | Original intent/receipt; write-once provider reference after authenticated initiation |
| `source_transaction_id`, `wallet_id`, `wallet_history_reference` | BIGINT N, BIGINT N, VARCHAR(96) N | Retained scalar provenance; not mutable/current Transaction authority |
| `currency_id`, `currency_code`, `money_scale` | BIGINT NN, VARCHAR(8) NN, SMALLINT NN | Match parent original currency/scale |
| `amount`, `receipt_total_amount` | BIGINT atomic units, NN | Original contribution and shared verified receipt cap; immutable |
| `original_commission_share`, `original_receivable_share`, `original_adjustment_share`, `original_vendor_entitlement_share` | BIGINT atomic units, N | Deterministic partition of already-calculated parent results; write once at parent finalization |
| `state` | VARCHAR(24), NN, `committed` | Funding state machine below |
| `version` | BIGINT, NN, 0 | Server-only CAS revision |
| `committed_at`, `confirmed_at` | TIMESTAMP NN / N | Original trusted initiation/native acceptance and confirmation |
| `created_at`, `updated_at` | TIMESTAMP, NN | Server timestamps |

FK `allocation_id`→economic parent, `payment_id`→payments, `currency_id`→currencies,
all RESTRICT/RESTRICT. Owner IDs are explicitly tagged retained scalar references,
validated against the parent Shop at creation/confirmation; platform is not an
individual Admin. Configuration IDs, source Transaction/Wallet/history references
are retained scalar evidence: deleting an operational record never deletes this
context or erases its historical reference. No FK to deletable PaymentProcess.

Unique `(allocation_id,funding_key)`, `(funding_event_key,allocation_id)`,
and `(allocation_id,confirmed_slot)` (multiple NULLs permitted; two successful
attempts cannot claim the same logical slot), plus global `receipt_claim_key`.
Index `(allocation_id,state,id)`,
`(funding_event_key,id)`, `(provider_tag,credential_owner_type,credential_owner_id,
provider_payment_reference)` and `(payment_process_reference,id)`.

CHECK `amount>0`, receipt cap≥amount, currency/scale consistency checked under
parent lock, mode/owner consistency, and confirmed state iff confirmed timestamp/
slot/collector evidence exists. Slot can represent Wallet contribution plus a
selected Wallet remainder, without mistaking them for one duplicated charge.
At confirmation require aggregate confirmed amounts≤G and shared receipt sums
within the authenticated cap. Failed attempts do not count toward G.

Exactly one context anchors a shared receipt: on confirmation it claims the
globally unique receipt key; other legitimate Shop/service portions reference
that retained anchor and must share its immutable checkout/event/receipt binding.
Key is SHA-256 of canonical **non-secret** receipt identity: electronic provider +
stable original credential-owner namespace + safe merchant binding if available +
verified provider payment reference; Wallet original withdrawal-history UUID;
Cash original economic allocation + native acceptance slot. Do not include a
mutable config revision to make the same receipt appear new after rotation.
Without safe merchant account identity, conservative owner namespace may reject
an ambiguous collision for review; it must never permit duplicate allocation.
Confirmed context CHECK requires exactly one of claim key/anchor ID, no self/
cyclic member chain; service validates every member points directly to a claim
anchor. Anchor's receipt cap covers all its member contexts, not every member's
full gross. A new checkout cannot claim an existing anchor or mint a new key from
the same receipt. Pending external attempts may have neither until authenticated
confirmation. This global uniqueness closes cross-checkout reference reuse, while
allowing the legitimate native Booking children/shared Wallet case.

After full coverage, distribute **S**, not a new commission calculation, among
platform-held contributions in proportion to their held amounts; distribute R
among direct contributions similarly. Persist exact shares with deterministic
fixed-scale remainder to lowest context ID, so share sums equal parent totals.
Assign native adjustment shares using the frozen native component contract;
each contribution's entitlement share is amount minus its assigned commission/
receivable/adjustment. Example: Wallet40 + platform60, C10 gives commission shares
4+6 and entitlement shares36+54; Wallet40 + direct60 gives commission10 on Wallet,
receivable0 on direct and entitlement30+60. Custody priority is at parent level;
the subsequent held-to-held distribution does not violate commission-first.

Merchant limitation retained: configuration row/version alone is not authenticated
merchant ownership or credential history. Capture actual resolved owner and safe
provider binding; if unavailable after rotation, later provider routing is blocked/
reviewed, never silently sent through today's account. Cash/Wallet need no provider
binding. Accounting custody can remain known even when provider-refund execution
is unavailable. No secret-derived credential fingerprint becomes this schema's
merchant-ownership authority.

### 7.4 Exact additive links and append-only effect contract

`transactions`: add nullable BIGINT `allocation_id` and `collection_context_id`,
FKs→the two tables, RESTRICT/RESTRICT; index both. Both NULL for existing rows.
Trusted writer validates context belongs to allocation. A replacement links to
the original allocation/context rather than starting another base obligation.

`platform_fee_ledger_entries`: retain existing monetary/status/provenance fields;
add:

| Column | Type/default | Meaning |
|---|---|---|
| `allocation_id` | BIGINT N, default NULL, FK RESTRICT | Authoritative economic identity for new rows |
| `collection_context_id` | BIGINT N, default NULL, FK RESTRICT | Original funding attribution where applicable |
| `effect_key` | VARCHAR(191) N, default NULL | Economic effect idempotency, not Transaction identity |
| `event_group_key` | CHAR(36) N, default NULL | Trusted refund/reversal/settlement operation group |
| `effect_kind` | VARCHAR(32) N, default NULL | Typed semantic below; legacy NULL remains legacy |
| `exact_amount` | BIGINT atomic units N, default NULL | Exact authoritative linked-row amount at parent scale; existing float is compatibility projection, never the new accounting sum |
| `effect_data` | JSON N, default NULL | Immutable typed authorization/proof/native adjustment metadata; no secrets/current preferences |

Kinds: `base_commission`, `base_payable`, `refund_principal`,
`commission_reversal`, `adjustment_reversal`, `payable_delta`,
`vendor_settlement`, `settlement_reversal`, `receivable_collection`,
`receivable_collection_reversal`. Positive magnitudes for principal/commission/
adjustment/settlement/recovery; signed `payable_delta` only. Do not introduce
Customer funding for commission recovery: it is a separate Vendor settlement
effect and never increases original G/P/D.

Unique `(allocation_id,effect_key)`; index `(allocation_id,effect_kind,id)`,
`(collection_context_id,effect_kind,id)`, `(event_group_key,id)`.
Base keys exactly `base:commission` / `base:payable`; later keys
`refund:<trusted-group>:principal:<context-id>`, `refund:<group>:commission`,
`refund:<group>:adjustment:<context-id>`, `refund:<group>:payable`,
`settlement:<group>:vendor`, `settlement:<group>:reversal`,
`receivable:<group>:collection` / `receivable:<group>:reversal`.
One base C and M row per economic obligation, including meaningful zero M.
Legacy entry types may remain `fee/payable/payable_adjustment` as sanitized
compatibility labels; **effect_kind/exact_amount** govern new linked semantics.
New authorized query projections must filter linked effect kinds, not blindly
sum all compatibility rows.

**Correction to initial proposal:** do NOT add unique `(allocation_id,entry_type)`;
it would forbid multiple legitimate partial reversals. Retain the old
Transaction/type unique constraint as historical constraint, but make linked
authoritative rows' `transaction_id` NULL. Store retained source evidence in the
context, and use economic effect keys. Thus several partial effects of one type
do not collide on a replaced/single Transaction. Replace Transaction FK cascade
with nullable ON DELETE SET NULL, and ledger Shop FK cascade with ON DELETE RESTRICT.
Retain supporting indexes when replacing constraints. This is proposed DDL only;
no historical row is rewritten. Parent Shop FK also restricts deletion of new
authoritative Shop history.

Linked rows/effect data are immutable, non-CRUD-deletable. Existing legacy
`markCollected`/waiver/accepted statuses are **not** automatically original funding
proof, new liability settlement proof, or permission to mutate new effect rows.
No status override can rewrite C/P/D or make an unverified payment authoritative.

## 8. Mapping, lifecycle, concurrency and current-value queries

### 8.1 Product, Booking and multi-Shop identity

- Product cart: before the first debit/provider initiation, create exactly one
  base allocation per Shop quote under one server `checkout_key`. Its immutable
  origin is Cart + Shop + base; bind once to that Shop Order on native creation.
  Cart deletion cannot delete it. Later Order payment first resolves the bound
  allocation; it cannot create a parallel Order-origin base row.
- Direct native Order: Order + Shop + base. Authoritative server creation/quote
  resolves Shop/currency/fees, never fields supplied by Customer/Vendor.
- Booking: Booking + Shop + base per native booked service; parent/children share
  checkout/funding-event identity when legitimately paid together, not one full
  gross copied to each. Supplements use distinct trusted obligation keys and
  their native economic terms; never another base C because of a new Transaction.
- Multi-Shop: Shop A and B have separate G/C/E and contexts. Shared Wallet receipts
  are partitioned once, with sum of shares equal actual original debit in the
  receipt currency. No cross-Shop merchant binding. Existing electronic multi-Shop
  prohibition remains; no mixed-country currency conversion or split UI added.

### 8.2 Allocation state machine

`committed → funding → funded → partially_reversed → fully_reversed`.
Immediate fully funded Cash/Wallet can advance directly from committed to funded
inside one SQL transaction. `funded` is the one-time original allocation claim;
`finalized_at` never clears, including refund or later deficit. State does not
serve as proof of delivery, provider bank settlement or payout remittance.

`committed/funding → canceled` only with no retained confirmed value and no
unresolved in-flight receipt (including confirmed partial funding fully returned
through linked future effects). A timeout is not proof of no provider collection.
`canceled` and `fully_reversed` are terminal for new funding/base fee creation.
`review_required` suspends automatic effects from any nonterminal operational
state when proof/binding/caps/contracts conflict. A future authorized reconciliation
can resolve evidence and restore its appropriate lifecycle, not rewrite committed
terms or reopen original economic finality. No public/manual override here.

Snapshot terms never change after commitment. Binding and original custody totals/
shares are write-once. State/version/timestamps change only through trusted
transitions; **current** financial values come from retained original rows plus
append-only linked effects, not mutable total fields or today's settings.

### 8.3 Context state machine

`committed → pending → confirmed`; native synchronous Cash/Wallet can confirm
in the same acceptance/debit transaction. `committed/pending → rejected/canceled`
only on authoritative failure/no collection. `review_required` is nonterminal
evidence conflict, not success/no-custody inference. `confirmed`, `rejected`,
`canceled` are terminal evidence states. Refunds do **not** flip confirmed context
to unconfirmed; they append linked effects and preserve original amounts/weights.
Replacement Transaction has no authority to change either state machine.

### 8.4 Trusted creation and atomicity

Resolve owned native quote, currency/country, Shop policy, actual method/config
owner **before** committing contexts and before first Wallet debit/external request.
Persist initiation identity before outbound network work; record ambiguous network
results as pending/review, never start another charge merely because of timeout.
Wallet debit, history/context confirmation and required native saves share their
SQL unit; preserve existing false-result/save-veto rollback safeguards.

For confirmation, lock all affected allocations in ascending ID within the
checkout/receipt group, then contexts, then necessary Wallet rows in fixed order.
Lock the retained receipt anchor too; validate authoritative receipt owner/currency/total, checkout membership, slot
uniqueness and receipt-wide caps. Shared receipt membership was frozen before
initiation: same reference cannot fund an unrelated checkout/Shop.
Use version CAS + `WHERE finalized_at IS NULL` for the one-time parent claim;
insert base effects and write-once split snapshots in the same transaction.
Final claim, failed required write or uniqueness conflict rolls back together.
Replay reads retained result; a different overpayment receipt goes to review,
not another base allocation/fee.

Future refund/settlement groups lock the same parent/context set and atomically
insert their complete effect set and advance version/state. Their stable native
operation ID is reused on retry; never generate a new refund UUID for a replay.
Caps are checked on exact aggregate effects under lock. Do not ignore a
uniqueness exception or permit a partial set of a multi-row refund.
External transport retry/outbox behavior remains a separately approved provider
implementation; local uniqueness is not proof of exactly-once network delivery.
SQLite requires conditional write/CAS, not reliance on ignored FOR UPDATE;
production-engine two-connection behavior still needs actual future certification.

### 8.5 Every required current query, without current settings

For confirmed context j, `f_j=original amount`, `r_j=sum linked refund_principal`.
Require `0<=r_j<=f_j`; pending attempts never count.
The finalized-allocation algebra below applies once `finalized_at` is non-null.
Before then, P/D/returns are real funding evidence, G/C/E are explicitly quoted
terms, and recognized Vendor payable/commission effects are zero; expose outstanding
Customer held-value exposure instead. This prevents an incomplete attempt from
being presented as a fully paid sale or full Vendor liability.

```text
Refunded = Σ r_j
P' = Σ platform/internal (f_j − r_j)
D' = Σ direct/offline    (f_j − r_j)
G' = G − Refunded
C' = C − Σ commission_reversal               (0..C)
A' = A − Σ adjustment_reversal               (0..A)
E' = max(G' − C' − A', 0)
Economic shortfall = max(C' + A' − G', 0)     separate obligation, not negative entitlement
S' = min(P', C')
R' = C' − S'
AP'/AD' = residual native adjustment responsibilities from original shares
          and authorized linked adjustment effects; AP' <= P' − S',
          AD' <= max(D' − R', 0) unless a separate native deficit obligation
          is explicitly authorized. Never use nonexistent custody.
M' = P' − S' − AP'
U = Σ vendor_settlement − Σ settlement_reversal
Q = Σ receivable_collection − Σ receivable_collection_reversal
Remaining Vendor payable = max(M' − U, 0)
Settlement recovery due from Vendor = max(U − M', 0)
Outstanding commission receivable = max(R' − Q, 0)
Excess commission recovery owed back to Vendor = max(Q − R', 0)
Net platform value attributed after settlements = P' − U + Q
```

Native adjustment responsibility may need an explicit authorized reattribution
within a refund group if original slices no longer satisfy custody bounds; never
silently manufacture a negative balance or reinterpret a component from current
settings. Typed effect_data records that attribution against original components.
All original shares remain immutable. This is a future authorization constraint,
not a refund engine implemented now.

Thus original gross/commission/Vendor entitlement come from parent snapshots;
actual originally controlled principal P and direct D from confirmed contexts;
current controlled/direct values from per-context reversal effects; original/
current satisfied commission, receivable, payable and remaining liability from
the formulas; provenance from the contributing contexts and effect groups.
Original and remaining values are both available, not conflated. Additional
receivable collections Q are separate Vendor money, not duplicate Customer gross.
Expose those excess/recovery obligations separately, not a negative Vendor payable.
For explicitly authorized retained original commission after principal refund,
E' can be zero while R' stays positive: preserve Vendor commission debt rather
than negative entitlement/payable. A non-commission adjustment shortfall needs
its frozen native responsible-party authorization; review if unproven.
Conservation is `G' + shortfall = C' + A' + E'`.
Net attributed platform value is a signed economic position (it can show an
advance after refund), not proof of bank settlement, solvency or an individual
Admin Wallet balance. P/P' report original/net Customer principal collected;
the settlement-adjusted position is exposed separately.
Before original finalization, show quoted versus unrecognized economics separately.

## 9. Policy examples, refunds, rollout and rollback

### 9.1 Original complete-funding examples

Each example has G100, C10, A0, E90. Decimal amounts, U=Q=0.
`P/D` are actual controlled/direct gross, not economic entitlement.

| Funding | P | D | S satisfied | R receivable | M Vendor payable | Vendor already-held entitlement |
|---|---:|---:|---:|---:|---:|---:|
| 100% platform electronic | 100 | 0 | 10 | 0 | 90 | 0 |
| 100% Vendor-direct | 0 | 100 | 0 | 10 | 0 | 90 |
| 100% Vendor Cash | 0 | 100 | 0 | 10 | 0 | 90 |
| 100% internal Wallet | 100 | 0 | 10 | 0 | 90 | 0 |
| Wallet40 + platform60 | 100 | 0 | 10 | 0 | 90 | 0 |
| Wallet40 + direct60 | 40 | 60 | 10 | 0 | 30 | 60 |
| Wallet40 + Cash60 | 40 | 60 | 10 | 0 | 30 | 60 |
| Wallet5 + direct95 (insufficient held funds) | 5 | 95 | 5 | 5 | 0 | 90 |
| Wallet5 + Cash95 (insufficient held funds) | 5 | 95 | 5 | 5 | 0 | 90 |
| Platform40 + direct60, mathematical only | 40 | 60 | 10 | 0 | 30 | 60 |

Last row is **not a native supported checkout**: current native checkout chooses
one electronic collector and may add Wallet. No current platform+direct split
or electronic multi-Shop activation is implied. The schema can represent such
contributions if separately authorized later.

### 9.2 Partial refund of every funding pattern

**Illustrative future authorization, not a new refund policy:** return 50% of
original principal from each original funding context and authorize reversal
of 50% of original commission (5). No native refund percentage/window is changed.
AP=AD=0, no prior Vendor payout/commission recovery. G'=50, C'=5, E'=45.

| Original funding | Refund destinations / portions | P' | D' | S' | R' | M' |
|---|---|---:|---:|---:|---:|---:|
| platform100 | original platform merchant50 | 50 | 0 | 5 | 0 | 45 |
| direct100 | original Shop merchant50 | 0 | 50 | 0 | 5 | 0 |
| Cash100 | original Vendor offline refund50 | 0 | 50 | 0 | 5 | 0 |
| Wallet100 | original Customer Wallet50 | 50 | 0 | 5 | 0 | 45 |
| Wallet40 + platform60 | original Wallet20 + original platform merchant30 | 50 | 0 | 5 | 0 | 45 |
| Wallet40 + direct60 | original Wallet20 + original Shop merchant30 | 20 | 30 | 5 | 0 | 15 |
| Wallet40 + Cash60 | original Wallet20 + original Vendor offline30 | 20 | 30 | 5 | 0 | 15 |
| Wallet5 + direct95 | original Wallet2.5 + original Shop merchant47.5 | 2.5 | 47.5 | 2.5 | 2.5 | 0 |
| Wallet5 + Cash95 | original Wallet2.5 + original Vendor offline47.5 | 2.5 | 47.5 | 2.5 | 2.5 | 0 |
| platform40 + direct60 (hypothetical) | original platform merchant20 + original Shop merchant30 | 20 | 30 | 5 | 0 | 15 |

Original contribution weights remain 40:60 or 5:95, even after a reversal.
The future refund request's trusted authorization supplies principal/fee/adjustment
reversal amounts; custody routing reads original contexts, never current Shop,
current method tag or newly configured merchant account. For quantized proportional
refunds, use frozen weights and deterministic remainder; cap each against its
remaining funded amount. If an already-exhausted leg prevents the requested split,
review or explicitly authorize a new distribution, never silently change owner.

Non-proportional proof, Wallet40/direct60:

- Refund Wallet20 only; authorize commission reversal2: P'=20, D'=60,
  C'=8, S'=8, R'=0, M'=12, E'=72. Original Wallet amount40 is retained.
- Refund direct30 only; authorize commission reversal3: P'=40, D'=30,
  C'=7, S'=7, R'=0, M'=33, E'=63. The retained commission released from held
  funds becomes Vendor liability, **not** a second direct Customer refund.
- Refund the same direct30 with **no** commission reversal: C'=10,
  P'=40, D'=30, S'=10, R'=0, M'=30, E'=60.

These cases demonstrate why commission reversal is a separately authorized
economic effect, not a fee recomputation per refunded contribution. Commission
released/reversed is attributed to original commission-source contexts in typed
effect_data; the principal refund row references its own original funding context.
Current payout/receivable values are recomputed from frozen economics + effects,
not from the original commission shares alone.

Full refund authorization: refund every original context amount, reverse C and
applicable A. For every pattern, P'=D'=C'=E'=S'=R'=M'=0 and refunded=G100.
Original contexts, C10 and original fee effect remain retained; current commission
is zero due to linked reversal, not deletion. An alternative **explicitly
authorized** full principal refund retaining original C10 (A0) gives P'=D'=0,
S'=M'=E'=0 and R'=10: Vendor commission debt10, never payable−10. This is not
assumed refund policy; the retained original commission and authorized effects
distinguish it from reversing C. Non-commission deficits cannot be assigned
to Vendor without the original native responsible-party contract.
If M30 was already paid20 before a 50% proportional reversal gives M'15,
remaining payable is0 and Vendor settlement recovery is5; do not produce payable−5.
Actual external refunds, Vendor cash returns, Wallet credits, reserves/clawbacks
and recovery transport are **not implemented or asserted successful here**.

### 9.3 Replay and multi-Shop examples

- Replacement Transaction T1→T2 for Wallet40/direct60 keeps the same allocation
  and context links. G100/C10/S10/R0/M30 unchanged; no second base commission.
- Duplicate callback for original direct60 event finds confirmed slot/event and
  retained finalized parent. All values unchanged, even after native Shop toggles.
- Two different attempts claiming `selected_method` cannot both set its unique
  confirmed slot; only verified aggregate coverage≤G can finalize.
- Multi-Shop Wallet: A G100/C10 + B G200/C20, one actual Wallet debit300.
  Partition receipt100/200; A payable90, B payable180, total commission30 **not**
  full checkout commission per Shop/contribution. Distinct Shop identities, same
  validated receipt cap300. Two Cash Orders give A/B receivable10/20 and payable0.
  B cannot use A's direct merchant context. Electronic multi-Shop remains denied.
- Refund A50 only with authorized C reversal5 changes A's G'/C'/M'; B remains
  G200/C20/M180. Payout/recovery/receipt queries always include Shop and currency.

### 9.4 APIs, legacy and future migration ordering

All terms, owner references, keys, versions, money snapshots and effects are
**server-owned**; no Customer/Vendor/generic Admin mass-assignment input or raw
model serialization. Authorized projections may show sanitized economic totals,
mode/currency/status; configuration/provenance IDs stay private. Model/service
immutability plus DB unique/CHECK/FK restrictions are required; a privileged
ordinary CRUD endpoint is not an accounting correction interface.

Legacy: both new tables start empty; existing Transaction/ledger links and effect
columns default NULL. **No UPDATE/backfill/reconciliation of historical rows**.
Missing context means unverified, not platform/direct. No inference from current
Shop or paid/deletable history and no override. All 12 Product Orders keep
`fulfillment_financial_state=unverified`; new collection state never grants delivery
financial eligibility. Legacy rows keep their existing values and old uniqueness.

Proposed migration ordering, after explicit approval only:

1. Capture current schema/53-table receipts, inspect actual target engine, FK and
   existing unique supporting indexes; use compatible signedness/null semantics.
2. Create empty allocation table, constraints/indexes; create empty context table
   with parent FK, slot/event constraints/indexes.
3. Add nullable Transaction links and ledger effect columns/FKs/indexes/unique
   effect key. Modify only Transaction nullability/deletion FK and ledger Shop
   deletion FK; no row rewrites. Preserve old Transaction/type uniqueness.
4. Audit legacy controller/observer compatibility. The linked-Transaction observer
   path must bypass per-Transaction full fee/payable creation in favor of the
   economic writer; both paths must never run for the same new allocation.
   New rows use economic keys
   and NULL legacy transaction_id; old code must not write/modify linked effects.
   Deploy gated trusted writers/readers together before any new context creation.
5. Confirm constraints, unchanged historical fingerprints and protected legacy
   finality before enabling **new accounting writers only**. Provider activation,
   Vendor settings and payment readiness still need separate approval.

Rollback: before any authoritative rows, reverse additive links/indexes/empty
tables in child→parent order and restore original constraint definitions; verify
original transaction IDs are non-null before restoring NOT NULL. After contexts/
effects exist, a destructive down migration must refuse: retain financial history
and roll application behavior back fail-closed. Never drop evidence, restore cascade
delete over it or fabricate source Transaction IDs. No `migrate`, migration file,
model/observer/refund/payout implementation or financial operation ran in this pass.

## 10. Verification and protected-state receipt

No implementation occurred, so the prompt's 20-case implementation matrix was
**not faked against a temporary architecture** and no claim of Phase 2 test coverage
is made. No broad suite or browser financial journey was run.

Accepted Phase 1 evidence remains unchanged: 44 focused tests / 429 assertions;
454 selected regressions / 3,066 assertions, one existing deprecation. Those
are **prior receipts**, not new runs. All accepted production code/migrations/
routes/configuration were left untouched. No workflows required restarting.
Phase 1 SQLite payout contention remains its prior bounded receipt; new Phase 2
allocation contention is **not tested** and production-engine concurrency remains
**NOT CERTIFIED**.

Before: `.local/payment-phase2-before.json`, captured from the current
post-Phase-1 development database before any edit. It exactly matches the Phase 1
completion snapshot. After: `.local/payment-phase2-after.json`.

Method: PDO `FETCH_ASSOC`, unescaped Unicode/slashes, preserved zero fractions;
sort each full encoded row lexically, newline join, SHA-256. Explicitly includes
`platform_fee_ledger_entries`, not counts alone.

Completion comparison: **all 53 table counts and full-row fingerprints unchanged**.
Both complete snapshot files have SHA-256:

`d500edda9dcb1448ac7f82090fe4b7f35a6326246ddf35831d7af765da45bd56`

Final design-revision receipt: `.local/payment-phase2-design-before.json` and
`.local/payment-phase2-design-after.json` also match this exact fingerprint,
the accepted Phase 1 completion and original Phase 2 receipt. All 53 counts and
complete-row fingerprints, the read-only schema hash and all 12 unverified Order
states remain unchanged. The policy tables are worked design examples, with
pure arithmetic checks only, **not implementation/refund/concurrency tests**.

The complete identical 53-table matrix is retained in
[Phase 1 protected-state matrix](payment-phase1-financial-correctness.md#6-protected-development-state).
Important current counts: Orders 12, Bookings 4, Transactions 15, Wallets 36,
Wallet histories 1, Payouts 1, fee ledger 1, payment processes 0, Shop configs 0,
platform configs 0. All 12 Orders remain unverified.
The read-only schema fingerprint is
`3cdb5cb4e903e95e8885d203d723caec01907deec85e52caeda4a2c11fd7cf30`
(ordered `sqlite_master` type/name/table/SQL, associative JSON with unescaped slashes).

Files changed by this phase:

- this report;
- appended Phase 2 decision to `payment-system-full-audit.md`;
- agent project approval-memory index/topic, recording the new stop boundary.

**Production files changed: none. Migrations created: none.**
The earlier unrelated generated Canvas registry edit was not part of this work;
the current design revision changes no Canvas/application files.
The failed historical full-hardening workflow is not claimed fixed or green.

## 11. Required decision summary — all 21 answers

1. **Booking today:** server-derived creation-time boolean plus provider intent
   evidence, not a complete immutable collector/custody/amount/allocation contract.
2. **Product today:** commerce amounts/currency and protected fulfillment state;
   no durable Order/Transaction collection snapshot. Process JSON is incomplete parity.
3. **Current settings:** can affect Product decisions and later configuration/
   monetary interpretation. Shop toggle alone does not rewrite Booking boolean.
4. **Canonical model:** native `platform`, `vendor_direct`, `offline`, `internal`;
   provider and mixed funding composition remain separate.
5. **Platform identification:** actual platform configuration ownership and verified
   original receipt, frozen per contribution; not an Admin Wallet or preference alone.
6. **Direct identification:** actual Shop merchant ownership and verified original
   receipt; never infer it from MTN/Orange tag alone.
7. **No secrets:** immutable owner classification/Shop ID, provider, safe config
   source/reference/revision and available authenticated merchant binding. Missing
   original binding blocks automatic later routing.
8. **Principal:** platform merchant; Shop merchant; offline Shop/Vendor Cash;
   internal Customer Wallet debit respectively. No physical bank receipt claimed.
9. **Vendor principal owed:** platform-held funded entitlement, less legitimate
   linked reversal/settlement, subject to native contractual conditions.
10. **No platform principal payable:** already Vendor-collected direct/Cash
    principal. Mixed funding requires custody-specific shares, not full gross twice.
11. **Platform commission today:** pending service-fee ledger; native commission
    component is separate, not completely captured/recovered by that ledger.
12. **Direct commission today:** same pending service-fee obligation; no automatic
    split/sweep/remittance recovery established. Do not fabricate collected status.
13. **Cash false liability:** yes, Booking boolean=true creates payable without
    platform cash custody; Product gross Admin credit has the related custody gap.
14. **Wallet liability:** incomplete/conditional/duplicable; safe Wallet movement
    does not prove complete correct Vendor liability.
15. **Replacement duplication:** yes, a new Transaction key can produce another
    full fee/payable for the same obligation; mixed contributions share the issue.
16. **Fee-ledger identity:** per Transaction/type, cascade-deletable through
    Transaction; not retained one-economic-payment allocation.
17. **One coherent model:** yes, retained per-Shop economic obligations plus
    separately frozen funding contexts for Product and Booking.
18. **Schema:** REQUIRED.
19. **Minimum:** the two proposed tables, nullable allocation/context links on
    Transactions/fee ledger, economic effect keys (not allocation/type uniqueness),
    exact linked amounts and retained append-only effect/provenance history.
20. **Legacy:** no automatic backfill or override; missing context means unverified.
    All 12 fulfillment-unverified Orders remain unchanged and fail closed.
21. **Before readiness:** separately approve migration and implementation of this
    final schema/contract, verify immutability/custody/once-only allocation and
    the approved held-funds-first rule, resolve any unproven native adjustment/
    beneficiary and merchant-binding limitations, preserve accepted regressions.
    Readiness/Vendor settings/refunds/rails/production remain subsequent approvals.

**STOPPED. Await explicit migration approval. No provider activation, Vendor-direct
enablement, own-gateway UI correction, credential configuration, provider refund,
external payout, legacy classification, publishing or automatic Phase 3.**

## 12. Phase 2B approval — schema-deviation gate, before migration

### 12.1 Authorization and current outcome

The creator's Phase 2B attachment approves this baseline's required migrations
and bounded native Product/Booking accounting implementation. It does not approve
provider activation/configuration, Vendor-direct enablement, provider refunds,
external payout rails, historical classification/deletion, publishing or Phase 3.
It expressly requires STOP before a material change to accounting identity.

**STOPPED BEFORE MIGRATION: Cart checkout-generation identity needs approval.**
No migration file, new model, financial writer or application change was made.
This is a design correctness gap, not a newly claimed financial P0.

### 12.2 Current-source evidence

Native source paths below are relative to `.migration-backup/backend/`:

- `app/Services/CartService/CartService.php:113–122` finds the owner's existing
  Cart and calls `createToExistCart`, rather than issuing a new Cart ID.
- `createToExistCart:131–167` updates existing Shop/detail/product rows and
  returns the same Cart ID. `insertProducts` / `cartDetailsUpdate:624–690` also
  support replacing contents within an existing Cart.
- `app/Services/PaymentService/BaseService.php:842–878` (`beforeCart`) calculates
  a quote from the current Cart, before native Wallet/provider work.
- `BaseService::afterHook:257–272` calls Order creation only for verified PAID.
  A verified rejected/canceled attempt does not create an Order or delete its Cart.
- `app/Services/OrderService/CartOrderService.php:153–178` deletes the Cart during
  successful Order creation. That does not rotate Cart identity after rejection.

The approved first unique key is:

```text
(origin_type, origin_id, shop_id, purpose, obligation_key)
```

For Cart base obligations, `purpose=base` and `obligation_key=base`.
It excludes `checkout_key`, so one historical canceled quote permanently occupies
the identity of every later quote for the same Cart/Shop.

Counterexample: checkout A quotes Cart123/Shop9 at G100/C10. Its electronic
attempt is authoritatively rejected without Wallet funding or any collected
value; the allocation is safely canceled. The Customer changes quantities in
the same Cart and starts checkout B, now G200/C20. A fresh allocation violates
the original unique key. Reopening A violates terminality; changing A's terms
violates snapshot immutability. Reusing G100 for a verified G200 receipt violates
coverage/quote reconciliation. None is an acceptable silent implementation.

An isolated SQLite `:memory:` check using precisely the original unique tuple
reproduced the collision between canceled checkout A and fresh checkout B.
This is a reduced uniqueness-contract reproduction, **not** a migrated schema,
accounting implementation, full integration test or concurrency certification.

### 12.3 Minimum proposed change — NOT applied

Replace the first origin unique key with:

```text
(checkout_key, origin_type, origin_id, shop_id, purpose, obligation_key)
```

Retain the approved bound-payable key unchanged:

```text
(payable_type, payable_id, purpose, obligation_key)
```

This adds **no table or column**. It permits a distinct, server-owned Cart checkout
generation while retaining immutable canceled history. Direct Order/Booking
allocations bind to their payable at initial commitment and remain once-only
through the bound-payable key; Cart allocations bind once to their resulting
Shop Orders. A later Transaction must resolve that existing bound allocation.

Required implementation rules if approved:

1. Generate/reuse the checkout key under serialized native Cart ownership and
   quote-generation claims; concurrent requests must not mint two live groups.
   SQLite needs an actual write/CAS serialization boundary; production engine
   locking requires its own later certification.
2. Freeze the Shop membership/quotes before initiation. All Shop allocations
   in that generation share the retained server checkout key.
3. Reuse the original allocation for retry/replacement of the same quote.
   Never infer a fresh checkout from a replacement Transaction or callback.
4. Permit a fresh Cart generation only after authoritative cancellation proves
   no retained controlled/direct value and no unresolved receipt/in-flight work.
   A timeout, disabled provider or edited Cart is not that proof.
5. Preserve old contexts, receipt claims, ledger effects, terminality and original
   finality. Old callbacks cannot fund the new generation or reuse its context.
6. Keep the existing bound-payable, confirmed-slot, receipt and effect uniqueness;
   retain integer units and the approved commission/custody/liability formulas.

Merely deleting old allocations, altering `base` obligation keys arbitrarily,
rotating native Cart IDs silently or adding unapproved financial fields is not
an implementation workaround. The proposed origin-key change affects economic
identity, so it is submitted for explicit approval under Phase 2B section 29.
The original schema in section 7 remains the baseline until that approval.

### 12.4 Protected-state and verification receipt

- Before: `.local/payment-phase2b-before.json`.
- Gate-stop after: `.local/payment-phase2b-gate-after.json`.
- Both SHA-256:
  `d500edda9dcb1448ac7f82090fe4b7f35a6326246ddf35831d7af765da45bd56`.
- All **53 protected full-row fingerprints/counts unchanged**.
- Schema compared against `.local/payment-phase2b-schema-before.json`:
  **unchanged**. Intentional schema/backfill changes: **none**.
- All **12 Product Orders remain fulfillment `unverified`**.
- Existing Wallet/WalletHistory/Transaction/ledger/payout/refund/commerce values
  were not rewritten. No existing-data financial operation/provider call.
- No migration, implementation arithmetic suite or selected regression suite ran.
  Prior design arithmetic receipts remain prior receipts, not Phase 2B tests.
- SQLite allocation concurrency: **NOT VERIFIED**.
  Production-engine concurrency: **NOT CERTIFIED**.

The requested 23 implementation-completion answers cannot be claimed complete
before migration/integration/tests. Current result is a bounded pre-migration
identity-gate report, with the minimum change proposed for approval.

**STOPPED. Await checkout-generation identity approval before Phase 2B migration
or code. No provider readiness, activation, Vendor-direct, refunds, external rails,
historical classification/deletion, publishing or automatic Phase 3.**

## 13. Phase 2B implementation and development migration receipt (2026-10-03)

This section supersedes section 12's **current** gate status, not its historical
receipt. The creator explicitly approved including `checkout_key` in origin
uniqueness, retaining payable uniqueness and requiring authoritative cancellation,
zero retained principal and no unresolved receipt before another Cart generation.
The three reviewed migrations have now been applied to the **development SQLite
database only**. No other material identity/custody/commission deviation was adopted.

### Production source inventory

Added:

- `database/migrations/2026_10_03_100000_create_commerce_payment_allocations.php`;
- `database/migrations/2026_10_03_100100_create_payment_collection_contexts.php`;
- `database/migrations/2026_10_03_100200_link_payment_accounting_evidence.php`;
- `app/Models/CommercePaymentAllocation.php`, `app/Models/PaymentCollectionContext.php`;
- `app/Services/PaymentAccounting/AccountingSchema.php`, `ExactMoney.php`,
  `AllocationWriter.php`, `AllocationBalances.php`, `AccountingEffects.php`,
  `NativeQuoteFactory.php`, `NativePaymentAccounting.php`, `CartAllocationFactory.php`,
  `ProviderContributionAdapter.php`, `WalletContributionAdapter.php`.

Changed:

- `app/Models/Transaction.php`, `app/Models/PlatformFeeLedgerEntry.php`;
- `app/Observers/TransactionObserver.php`, `app/Traits/Payable.php`;
- `app/Services/BookingService/BookingService.php`, `BookingCancellationSettlement.php`;
- `app/Services/OrderService/CartOrderService.php`, `OrderService.php`,
  `OrderStatusUpdateService.php`, `OrderRefundService.php`;
- `app/Services/PaymentService/BaseService.php`;
- `app/Services/TransactionService/TransactionService.php`;
- `app/Services/PaymentToPartnerService/PaymentToPartnerService.php`.

Paths above are relative to `.migration-backup/backend`. The unrelated Express
artifact and provider credentials/configuration/activation were not changed.
No new finance HTTP write endpoint or raw financial-model response was added.
The reviewed development manifest was updated to admit exactly these three
migrations and the previous fingerprint, without bypassing its ownership guard.

### Implemented schema and lifecycle

The approved two-table schema, typed frozen original amounts, nullable native
Transaction/effect links, unique effect keys, receipt anchor, per-slot confirmed
contribution uniqueness and retained audit FKs are installed. Existing
Transaction/type ledger uniqueness remains. Ledger Transaction deletion now
retains the ledger via `SET NULL`; Shop deletion is restricted. Original model
records are not backfilled. SQLite uses explicit CHECK DDL and integer-type
triggers rather than assuming DECIMAL affinity is exact. MySQL generation is
present but **not exercised/certified against a production engine**.

Trusted commitment freezes native quote/beneficiary/country/currency/scale,
`G=C+E+A` and one economic identity. Funding evidence is staged before debit/
outbound work; unresolved provider attempts remain pending and block another
charge. Confirmation validates complete frozen receipt membership, one checkout,
original payer, merchant/configuration and Wallet binding, exact receipt total
and global receipt reuse. Confirmed contributions cannot be replaced/reclassified.

Full verified funding recognizes the original custody split and commission once;
Cart roots additionally require native Order binding. Partial or unbound Cart
funding exposes retained principal but does not recognize the full quoted fee or
Vendor entitlement. `S=min(P,C)`, `R=C-S`, `M=P-S-AP`; direct/Cash principal is not
included in `M`. Original shares remain frozen; current balances derive from
separate append-only effects. Original payable, current payable, prior settlement,
remaining payable, Vendor recovery, receivable and excess recovery are separate.

Cancellation requires no unresolved contribution and no retained principal.
A fully returned partial contribution retains its confirmed evidence and return
effect while allowing cancellation, without recognizing a full economic fee.
Canceled Cart snapshots are preserved, never reopened/repriced. Same-quote live
retries resolve the retained identity; changed live quotes fail closed.

Refund/principal return, commission/adjustment reversal, Vendor settlement,
settlement reversal and receivable collection/reversal have durable allocation/
context/effect/group identities. Complete groups are transactional and replay
checked. These are internal accounting primitives exercised with trusted synthetic
proofs, **not provider refunds or external payout implementations**.

### Bounded native integration and limitations

New Product Cart quotes are committed before Order construction, with one root
per Shop. Completed native totals must reconcile before one-time Order binding.
New Booking parent/children receive distinct base economic roots under the server
checkout; native routing remains useful but is not accounting authority.
Server accounting keys are not accepted as client-controlled funding identity.

Authorized native Cash paid acceptance confirms Vendor-controlled funding.
Verified Wallet withdrawals confirm platform-controlled funding using the native
Wallet UUID/withdrawal UUID/paid history evidence. Separate partial/selected Wallet
legs retain separate native Transactions and receipt identities. Shared receipts
must cover their complete precommitted Shop portions. Native Wallet debit/history/
paid writes are transactional and required writes are checked.

Provider contributions are confirmed only **after** the existing
`BaseService::matchesVerifiedIntent` boundary accepts authenticated merchant,
reference, target, amount and currency. Booking pending Transactions are prepared
before initiation. Native fees are suppressed when the canonical root governs
accounting; replacing a Transaction does not recreate the economic base effects.

Linked Product fulfillment no longer fabricates a first-Admin gross Wallet
credit. Linked legacy refund/cancellation/Partner-settlement routines fail closed
instead of executing unpaired legacy gross transfers against canonical liability.
Future authorized internal operations must pair their financial movement with
the durable effect group. Legacy Phase 1 paths outside the new model retain
their existing boundaries.

**Coverage is deliberately bounded, not blanket native checkout readiness.**
Current quote adapters accept proven unit-rate/two-decimal native amounts with
zero unproven non-service-fee adjustments. Non-unit FX, native commission/tax
contracts not reconciled by the Cart adapter, coupons, tips and delivery-adjustment
responsibilities are rejected rather than assigned an invented beneficiary.
Supplement quotes require a distinct trusted adapter. This is not approval to
silently narrow future business coverage or enable Vendor-direct checkout.

The tests below verify accounting arithmetic/lifecycle and focused native
Cash/Wallet/Booking callback paths. They do **not** certify every real Product
Cart/user journey, every native adjustment, every provider adapter, operational
provider retries/reconciliation, production deployment or banking settlement.
Disabled providers, own-gateway and configuration readiness remain unchanged.

### Verification receipts

All financial mutations in tests used isolated synthetic databases:

- `.local/payment-phase2b-focused.xml`: **30 tests / 255 assertions**, passing.
  Covers held-first arithmetic, full/partial refunds, retained commission,
  settlement/receivable effects, partial held exposure/return, replacement and
  stale/replay behavior, shared multi-Shop Wallet receipt, global receipt reuse,
  Cart binding/cancellation, immutable models, integer limits/partitioning,
  native Cash/Wallet legs and synthetic verified Booking callback.
- `.local/payment-phase1-focused.xml`: **44 tests / 429 assertions**, passing.
- `.local/payment-phase1-regressions.xml`: **454 tests / 3,066 assertions**,
  passing with **one existing deprecation**.
- PHP syntax and patch-whitespace checks passed for the changed accounting code.
- The configured broader `original-hardening` workflow remains failed in its
  stored run (682 tests; 14 errors, 9 failures). It is not claimed passing or
  silently included in the selected green receipts above; its broader Driver,
  pickup and collection-setting failures were not remediated in this phase.
- Backend startup initially stopped at the reviewed development-manifest guard;
  admitting exactly the approved three migrations restored owned-database
  verification and the Laravel preview. Read-only backend settings and customer
  frontend requests both returned HTTP **200**.

Five controlled tests use **two independent connections to an isolated SQLite
file**, overlap a competing write while the first transaction owns the write
reservation, observe lock contention, then retry/replay after commit. They cover
economic creation, contribution recording, confirmation/base effects, refund
groups and Vendor settlement groups. This is real SQLite write contention,
not production-engine certification or a real-provider callback load test.

Development migration command applied only the three `2026_10_03_100*` paths
using the native SQLite development file. All three reported **DONE**.
`PRAGMA foreign_key_check`: **0 errors**. No test financial operations were run
against that development database.

### Protected-state comparison

Before receipt: `.local/payment-phase2b-before.json`.
Immediately before migration the same **53-table full-row receipt** was reproduced:

`d500edda9dcb1448ac7f82090fe4b7f35a6326246ddf35831d7af765da45bd56`.

After receipt: `.local/payment-phase2b-after.json`. Additive nullable fields mean
full-row representations intentionally changed; **the unprojected before/after
files are not claimed identical**.

`.local/payment-phase2b-legacy-projection-after.json` removes only the two newly
added Transaction fields and seven newly added ledger fields, selects the original
53 tables and reuses the exact baseline serializer/row ordering. Its SHA-256 is
**the same** `d500edda9dcb1448ac7f82090fe4b7f35a6326246ddf35831d7af765da45bd56`.
Thus all original columns/values and row counts are unchanged, including the
retained fee-ledger row. The snapshot collector explicitly includes that ledger;
its generic financial-name filter alone was insufficient.

- New allocations: **0**. New funding contexts: **0**.
- Existing Transaction allocation/context links: **all NULL**.
- Existing ledger allocation/context/effect fields: **all NULL**.
- Orders: **12**, all fulfillment financial state **`unverified`**.
- No historical accounting classification, deletion, financial backfill, Wallet
  mutation, refund, payout or fee/effect recognition occurred in development data.
- Intentional changes: the two new empty tables, additive nullable columns,
  approved constraints/indexes/audit-retention FKs/triggers, and migration records.

### Required final answers

1. Baseline implemented with the separately approved checkout-generation origin
   key; no other material schema deviation adopted.
2. Economic identity is the retained Shop/purpose/obligation root under server
   checkout+origin, with independent bound payable uniqueness.
3. Contribution identity is allocation+funding key, frozen funding event/membership,
   global receipt anchor and one confirmed contribution per funding slot.
4. Native commission is quoted once; canonical base recognition occurs once at
   fully verified, bound funding, never independently per contribution.
5. Replacement Transactions cannot create another canonical full commission.
6. Retained receipt claims, economic uniqueness, finality and effect keys prevent
   duplicate allocation/effects on callback replay.
7. Vendor-direct principal creates no platform principal payout liability.
8. Vendor-received Cash principal creates no platform principal payout liability.
9. Verified Wallet value is platform controlled; held value satisfies commission
   first, then only its remaining authorized amount becomes Vendor payable.
10. Unsatisfied original/current commission is separate Vendor receivable.
11. Later settings do not rewrite/reclassify frozen original context.
12. Product and Booking adapters use one canonical core, subject to the explicit
    quote/user-journey limitations above; blanket integration is not certified.
13. Roots, recipients, custody shares and liability are Shop-isolated.
14. Future refunds have retained context/share/effect identities, not current
    settings; operational refund adapters remain future approved work.
15. Future settlement reads current actual platform liability minus retained
    settlement effects, with recovery/excess separately represented.
16. Legacy records were retained, unclassified and unbackfilled.
17. All **12** Product Orders remain fulfillment **`unverified`**.
18. Intentional schema changes are listed in the migration/protected receipt above.
19. **No original financial column/value changed.**
20. Focused Phase 2B: **30 tests / 255 assertions**.
21. Selected regressions: **44/429** and **454/3,066**, one existing deprecation.
22. Controlled two-connection SQLite scope only; production **not certified**.
23. Explicit approval, broader native quote/user-journey verification, target-engine
    certification and provider-specific merchant/retry/reconciliation readiness
    remain before operational provider/configuration readiness.

**STOP. No provider activation/configuration, own-gateway enablement, provider
refunds, external payout rails, legacy classification/deletion, publishing or
automatic Phase 3.**

## 14. PHASE 2C — NATIVE CHECKOUT / QUOTE COVERAGE

### Pre-edit native entry inventory (2026-10-03)

The accepted Phase 2B baseline is retained. No provider readiness, production
connection/certification, real financial operation or new migration is authorized.
Before application edits, `.local/payment-phase2c-before.json` captured all 55
protected post-Phase-2B tables, including both new accounting tables and all
Transaction/ledger links. `.local/payment-phase2c-schema-before.json` captures
the actual schema separately. Financial fixtures must remain disposable.

Paths below are relative to `.migration-backup/backend`; routes have `/api/v1`.

| Native route/controller family | Service / monetary boundary | Trusted quote / identities / canonical entry |
|---|---|---|
| REST/User Cart store/change/calculate; REST `order/products/calculate` | `CartService`, `CartRepository::calculateByCartId`, `OrderHelper::setItemParams` | Owned Cart, per-detail Shop, stock/quantity/native listing price/discount/product tax, Shop tax, native service fee, coupon/tip/delivery and resolved country currency. Calculation is **not funding**. `CartAllocationFactory` commits server checkout/Cart/Shop/base roots only at checkout. |
| User/Seller/Admin `orders` store; POS and Cart branches | `OrderService::create`, `POSOrderService`, `CartOrderService`, `calculateOrder` | Server-owned Customer/Shop/currency; completed persisted stock/quantity monetary components. `NativeQuoteFactory` commits new Order or validates/binds the Cart root. Cash acceptance goes through the authorized paid boundary; Wallet through checked debit/history. |
| `payments/order/{id}/transactions` | `TransactionService::orderTransaction`, `checkPayment`, `walletHistoryAdd` | Persisted payable/Customer/Shop/gross/method, existing authority checks. Cash selection is not acceptance; Wallet debit/history must prepare and confirm `NativePaymentAccounting`/`WalletContributionAdapter` context. |
| User/Master/Seller/Admin `bookings/calculate`, `bookings` store | `BookingRepository::calculate`, `BookingService::beforeSave/create` | ServiceMaster/service/branch/master, payer/local client, Shop country/currency, service price/discount/extras, fee, coupon/gift/membership coverage. `NativeQuoteFactory` commits one Booking base allocation; Wallet/Cash must use authoritative native boundaries. |
| `payments/booking/{id}/transactions` | `TransactionService::bookingTransaction`, `BookingPaymentAuthority` | Owned Booking and same-currency owned children. Cash/Wallet only here; electronic selection cannot authorize payment. Native quote/context/debit/history must remain one SQL operation. |
| REST/User/Seller/Admin/Master provider initiation routes (`*-process`, including MTN/Orange/Stripe and remaining existing adapters) | Provider `processTransaction` → `BaseService::getPayload` → `ProviderContributionAdapter::prepare` | Persisted Cart/Booking identities and gross; server routing and actual merchant/config owner, payment/provider/currency, Wallet remainder. Per-Shop checkout roots and stable contribution/event keys; eligibility and pending intent do **not confirm funding**. Electronic multi-Shop remains blocked. |
| Existing provider callbacks/verifiers/poll paths | Authenticated adapter → `BaseService::afterHook/matchesVerifiedIntent` → `ProviderContributionAdapter::complete` | Matching original method/reference/model/amount/currency plus authenticated merchant evidence. Contribution confirms exactly once; Product Order binding and Booking Transaction update must complete atomically. MTN is tested synthetically, never called live. |
| `payments/{type}/{id}/transactions` PUT | `TransactionController::updateStatus` | Existing Admin/Seller/Shop authority retained. Native Cash acceptance invokes the canonical writer. Manual Wallet/electronic status is not debit/receipt proof. |
| Booking status/update and Product fulfillment/digital paths | `BookingService::update/statusUpdate`, `OrderStatusUpdateService`, `TransactionService::digitalFile` | Lifecycle is not electronic or Wallet proof. Canonical fee/liability identity survives paid/lifecycle replay and Transaction replacement; linked Product fulfillment suppresses legacy Admin gross credit. |
| Booking `extra-time`, extras/update, payment `tips`/`extra_time` branches | `BookingService`, `BookingRepository`, `BaseService` | Distinct adjustment economics must be source-proven before funding. Unknown sponsor/beneficiary/commission basis must fail closed, not mutate a frozen base obligation. No scheduling/permission redesign. |
| User/Seller/Admin Order refunds; Booking cancellation | `OrderRefundService`, `PaymentRefund`, `BookingCancellationSettlement` | Linked new records require original-custody durable effect groups; legacy conservative finality remains. No provider refund is implemented. |
| Admin `payment-to-partners/store/many` and `payment-to-partners/bookings/store/many`; payout approvals | `PaymentToPartnerService`, `PayoutService` | Native gross/seller accessor cannot authorize a linked canonical settlement. Remaining platform liability and paired effects are required; general Phase 1 Wallet-transfer atomicity is retained, not relabeled as Vendor principal or external remittance. |

Inventory findings to close within this scope: generic Order Wallet payment
does not prepare its native debit context; Booking partner bookkeeping lacks the
Order sibling's linked-allocation guard; native paid flags/Cart construction can
precede canonical proof/binding; Product line tax and Booking gift/membership
benefits require explicit fail-closed checks. These are integration gaps in the
already identified accounting architecture, not an independent new P0 finding.
No exploit or real financial operation was exercised.

### 14.1 Implementation and native journey results — 2026-10-03

The inventory above is the Phase 2C entry-point inventory, not a restarted
architecture audit. Its identified integration gaps are now closed for the
bounded, source-proven contracts below. No migration or financial policy was
changed. Unsupported contracts are rejected, not assigned guessed beneficiaries.

**Product:** native Cart lines → native `OrderHelper` pricing → immutable
per-Shop Cart allocation → real `CartOrderService`/`OrderService` creation →
bound Order → confirmed contribution → one canonical fee/liability recognition.
Binding retains the original Cart identity and checkout key. Standalone native
Order/POS calculation uses the same quote factory after native calculation;
generic Cash/Wallet payment uses that retained allocation. Product digital
delivery waits for full funding. Existing native commission/gross-credit helpers
cannot independently recognize linked new Order principal.

**Booking:** actual Service/Master/branch selection, native availability and
`BookingRepository::calculate` → real `BookingService::create` → frozen Booking
quote → native Cash/Wallet or verified provider contribution → canonical core.
Booking status `ended` does not establish Wallet/electronic funding. Existing
`BookingPaymentAuthority`, Shop, branch, country, payer and currency boundaries
remain; no scheduling or permission redesign was performed.

The real native two-Service/same-Shop Wallet creation test succeeds. Each native
Booking is an original distinct priced obligation: the approved identity includes
checkout, original Booking ID, Shop, purpose and obligation key. Both share the
checkout key; each original native service fee is recognized once, not once per
funding leg. An initially suspected grouping restriction was disproved by this
test; no identity change or deviation approval was required.

**Trusted Product quote:** persisted Cart owner, line Stock/Product/Shop ownership,
actual accepted quantity, native net line price and listing discount, Shop-resolved
country/currency, native service fee and exact final native gross. Same-gross
replacement Stock is not the same quote. Quantity, financial identity and quoted
money cannot change beneath an active allocation. Native Order binding compares
the final native calculation with frozen Cart gross, commission, entitlement,
adjustment, currency and scale; mismatch rolls back.

**Trusted Booking quote:** persisted Booking user/local-client, Shop/branch,
Service/Master, Shop country and invoice currency, native persisted price/discount,
service fee, source-priced Service extras and final total. Master commission,
coupon/gift/membership sponsorship and additional billing are not inferred.
Models and linked Transactions cannot rewrite retained quoted money, owner,
currency or binding. Confirmation uses persisted money, not unsynchronized
created/updated-event originals.

For the supported native contracts, `G = C + E + A`, `C` is the original native
service fee, `E = G - C`, and `A = 0`, in integer units at frozen scale **2**.
Native vendor listing net prices and source-priced Booking Service extras remain
Vendor entitlement; service fee belongs to the platform. This is not permission
to reinterpret taxes, coupon sponsorship, tips, delivery or FX as entitlement.

### 14.2 Supported and fail-closed monetary contracts

| Contract | Current boundary |
| --- | --- |
| Native net listing price / Vendor-owned listing discount | Included in trusted native gross; Cart snapshots retain line identity/quantity/net/discount units. Cross-Shop discount ownership and quantity repricing are rejected. |
| Original fixed native service fee | Frozen commission, once per original economic allocation. Percentage-fee semantics are not invented. |
| Native source-priced Booking Service extras | Included in original Booking gross/Vendor entitlement; cross-Service/Shop extras are rejected. Not the later `extra-time` billing path. |
| Product/Shop tax, coupon subsidy, tips, delivery money | Nonzero/unproven contracts fail closed before funding; no assumed beneficiary or sponsor. Nonzero delivery remains unavailable even when a native delivery calculation exists. |
| Master commission, gift/membership benefits, rewards/cashback, bonus items | Unproven economics fail closed. Newly activated cashback cannot create an unquoted linked lifecycle reward. |
| Later extra-time, repricing or separate billing adjustments | Linked base quote cannot be mutated; separate original adjustment contract/effect identity is not manufactured. |
| XAF and Wallet currency | Known same-currency native quotes at unit native rate are supported. The actual owned Wallet currency, not the default Currency, must match. |
| Nonunit native rate / FX / currency mismatch | Blocked. No exchange rate, rounding, settlement conversion or new currency policy was introduced. Existing nonunit-rate catalog checkouts may therefore remain unavailable. |

These restrictions are important native checkout limitations, not evidence that
all present catalog combinations or operational financial features are enabled.
No provider readiness, own-gateway setting or supported-currency policy changed.

### 14.3 Custody, finality and retained effects

- Authorized native Cash acceptance is offline Vendor custody. It creates
  unsatisfied commission receivable, never platform principal payable. It is not
  proof of an external electronic receipt.
- Native Wallet is platform-controlled funding only after the actual owned
  Wallet debit and matching durable withdrawal history. Preparation precedes
  debit; confirmation precedes paid flags. Cash/Wallet generic paths retain their
  original authorization. A Wallet amount/currency mismatch fails before debit.
- Electronic eligibility and pending intent create no funded accounting.
  The real MTN controller verifier, native contribution adapter, `afterHook`,
  Cart-to-Order creation and Booking update were exercised with synthetic
  merchant/network responses only. Exact amount/currency/reference/configuration
  evidence is required. Four native custody/funding cases for each of Cart and
  Booking passed: platform, Vendor-direct, Wallet+platform and Wallet+direct.
- For each allocation, held funds satisfy commission first. With `G=100`,
  `C=10`: platform/Wallet funding yields `M=90`; direct/Cash yields `R=10,M=0`;
  Wallet `40` plus direct `60` yields `M=30,R=0`. Integer-unit test equivalents
  are `G=10000,C=1000`. Core matrices additionally cover held funds equal to,
  below and above commission, refunds and prior settlement.
- Verified provider completion, native binding, paid state and fee effects share
  a SQL transaction. Failed native Order creation throws through `afterHook`;
  original pending intent remains retryable. Electronic Transaction price is
  its contribution, not rewritten to allocation gross. Legacy Cart Transactions
  are not remorphed to an arbitrary first Order.
- Same-quote retries reuse checkout identity. An authoritatively canceled
  no-collection Cart can begin a new generation after changes; the canceled
  allocation stays retained. Active changed quotes and same-gross different
  goods are rejected. Held/unresolved funding is not treated as cancelable.
- Multi-Shop Cart allocation, binding, Cash and core shared-receipt partitioning
  remain Shop-isolated. Native single-merchant electronic multi-Shop limitations
  remain; no split-provider orchestration was invented.
- Replacement Transactions and callback replay do not multiply allocation,
  contributions, commission or payable. Old gross-credit and linked partner
  bookkeeping cannot independently settle canonical principal.

### 14.4 Refund and payout interaction

Linked new Order refunds, Booking cancellation/settlement and Booking/Order
partner bookkeeping require original-custody durable paired accounting effects;
they cannot fall through to legacy raw gross/seller-price financial helpers.
The typed core retains original funding shares, fee policy and remaining
platform-held liability for future refund/settlement effects. Native operational
refund/partner-payout adapters are **not** declared complete or enabled by this
phase. Direct/Cash principal cannot authorize platform payout.

Existing conservative legacy finality and Phase 1 ordinary Wallet-transfer
atomicity remain. Ordinary legacy Wallet bookkeeping is not recertified as
canonical Vendor principal or external remittance. No provider refund, external
payout, real settlement or legacy reclassification occurred.

### 14.5 Verification, failure injection and exact change inventory

Final commands/evidence:

- `.local/payment-phase2c-focused.xml`: **78 tests / 532 assertions**, all pass,
  one existing PHP 8.4 nullable-parameter deprecation in `OrderHelper`.
- `.local/payment-phase1-focused.xml`: **44 / 429**, all pass.
- `.local/payment-phase1-regressions.xml`: **454 / 3,066**, all pass, one existing
  deprecation. Phase 2C JUnit receipts are in `.local/payment-phase2c-*-junit.xml`.
- Real native Cart-to-Order Cash/Wallet, Booking selection/calculation/creation
  Wallet, same-Shop multi-Service Booking Wallet, generic native payments,
  synthetic MTN/native finalization and mixed funding passed.
- Wrong electronic amount/currency, unsupported quotes, live Cart changes,
  manual electronic/Wallet paid flags and Wallet currency mismatch create no
  funded fee meaning.
- SQLite triggers before and after fee insertion inject failures after real
  native Wallet debit: Orders, Transactions, histories, quote/contribution/fee
  rows, Wallet balance and Stock quantity roll back together.
- Verified provider effect failure leaves the original intent and contribution
  pending, no Order/fee effects; removal of the injected fault allows one safe
  completion. Verified no-collection rejection retains canceled old checkout
  and permits a different new generation.
- Core controlled two-connection contention covers economic creation,
  contribution recording, confirmation/base effects, refund and settlement.
  Additional native two-connection tests cover same-Cart quote retry and native
  Cash contribution/finalization/fee. Contender is lock-rejected; retry yields
  one identity and effect set. This is SQLite evidence, not production-engine
  race/deadlock certification. Schema-cloned native fixtures do not claim
  production referential-integrity certification.
- Native fixtures clone the actual schema into disposable databases. Native
  calculators, business services, verifier and financial writes are real;
  authentication/framework plumbing, notification dispatch and merchant/network
  lookup are synthetic. No application kernel boots against existing financial
  records during those tests. No browser checkout or live provider certification
  is claimed.
- Laravel preview restarted cleanly. Native public currencies and Customer
  homepage return HTTP **200**. Registered API health screenshot shows `ok`;
  it is not a screenshot verification of signed-in native checkout.
- The pre-existing broad `original-hardening` failure remains outside this
  phase's selected passing suites: previously **682 tests, 14 errors, 9 failures**.
  The complete broad suite is not claimed green or rerun as unrelated work.

Net Phase 2C native code/test files (all paths below relative to
`.migration-backup/backend/`; includes work before the replenished-credit resume):

```
app/Http/Controllers/API/v1/Dashboard/Payment/TransactionController.php
app/Models/Booking.php
app/Models/Concerns/ProtectsCanonicalPayment.php
app/Models/Order.php
app/Models/Transaction.php
app/Services/BookingService/BookingService.php
app/Services/OrderService/OrderService.php
app/Services/OrderService/OrderStatusUpdateService.php
app/Services/PaymentAccounting/CartAllocationFactory.php
app/Services/PaymentAccounting/NativeFinancialBoundary.php
app/Services/PaymentAccounting/NativePaymentAccounting.php
app/Services/PaymentAccounting/NativeQuoteFactory.php
app/Services/PaymentAccounting/ProviderContributionAdapter.php
app/Services/PaymentAccounting/WalletContributionAdapter.php
app/Services/PaymentService/BaseService.php
app/Services/PaymentToPartnerService/PaymentToPartnerService.php
app/Services/TransactionService/TransactionService.php
tests/Hardening/PaymentAccountingNativeTest.php
tests/Hardening/PaymentNativeCheckoutFixture.php
tests/Hardening/PaymentNativeCheckoutTest.php
tests/Hardening/PaymentNativeCheckoutConcurrencyTest.php
tests/Hardening/NativeCheckoutSyntheticMtn.php
```

Reports changed: this file and `docs/development/payment-system-full-audit.md`.
Local verification configuration/receipts: Phase 2C focused XML/JUnit, Phase 1
focused/regression JUnit, before/after protected and schema JSON and
`.local/payment-phase2c-protected-comparison.json`. No migration file changed.
The temporary thin-fixture default-Currency assumption was removed; owned Wallet
currency is now represented in the native accounting test fixture.

### 14.6 Protected-state comparison

Post-2B baseline `.local/payment-phase2c-before.json` file SHA-256:
`fbcd30955d11033874c0b87201fa1e0684236f592fa58acf41d4f95eac1b3c0f`.
Same PDO/JSON/sorted-row/newline SHA-256 codec before and after. **All 55 protected
table counts and full-row fingerprints are identical**, including after public
runtime checks. This phase needs no projections or intentional financial/default
change exemptions. All **408** retained schema entries are identical.

Existing financial values changed: **none**. Development allocations **0**;
contributions **0**. All **12** legacy Orders retain
`fulfillment_financial_state=unverified`. Foreign-key check errors **0**.
No real credential, charge, debit, refund, payout or legacy test mutation occurred.

### 14.7 Required final decisions (request section 39)

1. **Product paid paths:** supported new native Cash/Wallet and verified
   electronic paths reach canonical accounting; unproven quotes fail closed.
   Legacy records remain outside guessed/backfilled canonical classification.
2. **Booking paid paths:** same bounded result, including actual creation and
   original distinct same-Shop Service obligations. Broad all-catalog and
   every multi-Service/provider combination certification is not claimed.
3. **Outside-canonical financial meaning:** no known supported new linked path
   bypasses fee/funding guards. Preserved legacy domains are not claimed migrated;
   unknown adjustments and linked legacy financial helpers are blocked.
4. **Product contract:** native persisted owned lines/accepted quantities/net
   prices, frozen discount ownership, Shop country/invoice currency, fixed
   service fee and final gross; exact bind-match and integer scale 2.
5. **Booking contract:** native persisted priced Service/Master/branch,
   payer/local-client, country/currency, native discount, service fee, source
   Service extras and final total; immutable base obligation.
6. **Supported adjustments:** net Vendor listing prices/owned listing discounts,
   original fixed service fee and source-priced upfront Booking Service extras.
7. **Fail closed:** unproven tax/subsidy/coupon/tip/delivery, Master commission,
   gift/membership, cashback/bonus, extra-time/repricing and FX contracts.
8. **Cash custody:** offline Vendor custody at authorized native acceptance.
9. **Wallet custody:** platform-controlled after actual debit/history evidence.
10. **Eligibility alone:** cannot create funded accounting.
11. **Verified evidence:** one retained contribution; native synthetic verifier
    and replay checks passed.
12. **Replacement Transactions:** no duplicate allocation/commission/payable.
13. **Duplicate callbacks:** no duplicate contribution or canonical fee effects.
14. **Checkout key:** retry, authoritative no-collection cancellation, changed
    new generation and retained canceled origin verified.
15. **Multi-Shop:** allocation, Cash/binding and core receipt partitioning are
    Shop-isolated; native single-merchant provider limitations remain.
16. **Vendor-direct principal:** excluded from platform principal payout liability.
17. **Cash principal:** excluded from platform principal payout liability.
18. **Held value:** commission-first; only remaining eligible held value is payable.
19. **Refund bypass:** linked native legacy-helper bypass is blocked; operational
    original-custody refund adapters remain gated, not certified implemented.
20. **Payout bypass:** linked raw-gross partner payouts are blocked; no direct/Cash
    principal enters canonical platform payable. External payout is not implemented.
21. **XAF:** supported for proven same-currency, unit-rate native contracts at
    frozen scale 2; this does not certify all existing nonunit-rate catalog data.
22. **Unsupported FX:** nonunit native rate, Wallet/invoice currency mismatch,
    conversion/rounding and settlement FX without original frozen proof.
23. **Migrations:** none additional; all schema fingerprints unchanged.
24. **Legacy Orders:** all 12 remain `unverified`.
25. **Existing development financial changes:** none; all 55 full-row protected
    fingerprints and counts unchanged.
26. **Focused tests:** 78 tests / 532 assertions, pass; one existing deprecation.
27. **Selected regressions:** 44 / 429 and 454 / 3,066, pass; one existing
    deprecation in the regression suite.
28. **SQLite contention:** core and additional native two-connection tests pass
    with lock rejection followed by idempotent retry.
29. **Production engine:** not accessed or certified.
30. **Phase 3 readiness:** bounded supported native integration is verified;
    **not an unconditional go-ahead**. Important unsupported monetary/FX contracts,
    native provider orchestration limits and operational refund/settlement gaps
    remain explicitly unavailable. No all-native/provider/production certification
    is inferred. Further scope requires explicit approval.

**STOP.** No automatic Phase 3, provider/configuration activation, own gateway,
provider refunds, external rails, legacy classification/deletion or publishing.