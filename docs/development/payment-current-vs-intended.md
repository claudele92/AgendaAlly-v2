# Payment architecture — current versus intended

2026-10-03. Recommendations only; no implementation/activation in this phase.
See the [completed audit](payment-system-full-audit.md) and
[current provider matrix](payment-provider-current-matrix.md).

| Concern | Current native model | Intended safe model / decision needed |
| --- | --- | --- |
| Platform collection | Platform global payloads or country merchant config; verified initiation/settlement code on selected rails, currently disabled | Platform merchant as collector; provider-held funds/settlement evidence independent of any individual Admin Wallet |
| Vendor direct | Only MTN operational foundation; Orange configuration scaffold; one Shop-wide preference | Per-provider supported merchant context, immutable per funded entitlement; either support all Shop business currencies or introduce explicitly approved narrower scope |
| Capability vs readiness | Credentials excluded from configuration capability, but activation/environment/readiness list still gate registration and collection choice | Separate configure permission/capability from charge permission; retain policy and safety gates, never invent credentials or activation |
| Product currency | Business Product country/currency context at discovery/initiation; display currency is not charge currency | Preserve authoritative quote and currency scale; immutable monetary allocation per merchant/payable |
| Service currency | Service business country and persisted Booking currency/collection snapshot | Same discipline as Products; no live country/toggle changes to historical payment meaning |
| Collection snapshot | Booking has frozen `collect_via_platform`; cart intent freezes it; Order lacks its own collector snapshot | Order and Booking both persist actual collector/mode/provider/account/revision tied to original funding |
| Success evidence | Hardened selected provider callbacks and local Transaction; native generic Booking transaction creation can still mark an eligible electronic method paid without calling verifier | Only provider proof for electronic success; whitelist internal/offline transaction paths; no activation until bypass is removed |
| Platform fees | Paid Order/Booking service-fee ledger, one entry type per Transaction | Explicit gross/discount/tax/delivery/service/commission/Vendor allocation, currency precision and custody event |
| Vendor entitlement | Booking seller-fee accessor/payable; Product seller-fee accessor and legacy fulfillment Wallet writes, no symmetric payable | One immutable funded entitlement, distinct from provider principal, fee receivable and an internal Wallet credit |
| Platform liability | Booking payable controlled by stored Shop-choice flag, not actual Cash/internal custody; no equivalent Order payable | Liability only for principal actually held for Vendor, with funding/custodian attribution; no Vendor-direct principal payable |
| Vendor-direct commission | Pending service-fee ledger/receivable; no automatic split/debit/sweep/remittance | Decide contract: verified provider split, reserved balance fee, billed receivable, or other explicit collection method |
| Fulfillment | Product delivered action tops up first Admin Wallet by gross each time state returns to delivered | Lifecycle must not mint or repeat funded entitlement/cashback; immutable settlement claim separate from status |
| Internal refunds | Accepted Product and Booking once-only caller containments, original paid history preserved | Keep these baselines; add allocation/collector-aware external refund handling without reopening their approved state machines |
| Provider refunds | Legacy dispatch/adapters, global current credentials; no MTN/Orange branch; no common intent/claim/reconciliation | Original merchant/intent/reference; allocated refund amount; idempotency; pending/success/failure/recovery and authoritative confirmation |
| Cash | Offline, manual database state; legacy commission/Wallet/payable effects can imply unsupported custody | Record actual cash custodian and separate fee debt; no fictitious platform principal/payment |
| Wallet commerce | Guarded internal movements; paid commerce observer and legacy fulfillment effects | Reserve-backed same-currency spend; no duplicate fee/payable from partial contribution plus external remainder |
| Payout | Generic request and approving account's Wallet transfers; status marked before non-atomic legs; no external remittance/allocation | Reserve approved Vendor entitlements once; verify beneficiary/currency; provider transfer intent/reference; terminal settlement and rollback/recovery |
| Reconciliation | Process/Transaction/history/fee/payable/payout coexist without a single allocation-to-provider-remittance path | Reconcile provider collections/refunds/fees/settlement to immutable allocations, liability reserves and payout disbursements |
| Concurrency | Selected source locks/idempotent claims plus bounded SQLite tests; production unproven | Production-engine contention and uniqueness tests for funded entitlement/refund/payout; no blanket certification from SQLite |
| Activation | Some catalog rows active but all external rails environment-disabled; no collector rows | Explicit approval only after financial correctness, configuration, sandbox, reconciliation, production race evidence and operational controls |

## Required architecture classifications

| Area | Classification | Reason it is not fully correct |
| --- | --- | --- |
| Platform collection | PARTIALLY IMPLEMENTED | Selected verified charge paths; disabled/unconfigured, downstream financial settlement incomplete |
| Vendor-direct | PARTIALLY IMPLEMENTED / CONFLICTING | MTN foundation only; Shop-wide preference and asymmetric Product/Service accounting |
| Cash | CONFLICTING / UNSAFE | Offline custodian is not proved by paid state; platform payable/gross Admin Wallet can imply unsupported custody |
| Wallet-funded commerce | PARTIALLY IMPLEMENTED | Accepted invariants retained; reserve/partial contribution allocation and downstream settlement incomplete |
| Commission | PARTIALLY IMPLEMENTED | Service-fee bookkeeping does not implement complete allocation or direct commission recovery |
| Vendor entitlement | CONFLICTING | Booking payable versus Product accessor/legacy gross Admin credit, no common funded entitlement |
| Platform payable | PARTIALLY IMPLEMENTED / UNSAFE | Booking choice-flag condition, no Order parity or payout reservation/consumption |
| Refunds | PARTIALLY IMPLEMENTED | Accepted internal containments; missing/corrupt external refund intent, authority and allocation paths |
| Payout | UNSAFE / MISSING | Wallet bookkeeping has double debit/atomicity defects; provider remittance/allocation missing |
| Reconciliation | MISSING | No provider collection/refund/remittance-to-allocation reconciliation |
| Provider callbacks | PARTIALLY IMPLEMENTED / UNCERTAIN | Selected proof/replay guards; no complete out-of-order/refund state machine or production race certification |
| Cameroon/XAF | PARTIALLY IMPLEMENTED | MTN declared capability, not live readiness; all electronic checkout disabled/unconfigured |
| Product accounting | CONFLICTING / UNSAFE | No persisted Order collector/payable parity; fulfillment repeat-credit P0 |
| Service accounting | PARTIALLY IMPLEMENTED | Snapshot/payable and contained cancellation, but Cash custody/partial allocation/external settlement incomplete |
| Accepted seven containment invariants | ALREADY CORRECT in verified scope | Preserve accepted isolated evidence; do not expand this to whole-system or production certification |

## Accounting equations

Native observed formulas are **not** a completed target allocation contract:

- Booking `seller_fee = total_price - service_fee - coupon_price`.
- Order `seller_fee = total_price - total_tax - delivery_fee - service_fee - commission_fee`.
- `TransactionObserver` records **service_fee**, not a complete commission/fee
  allocation; positive Booking seller fee becomes a payable only when Booking
  `collect_via_platform` is true.
- A paid WalletHistory is not a second customer purchase, and a database fee/
  payable/accepted payout is not an external provider settlement.

Recommended conservation model:
`verified funded gross = refund allocation + tax/delivery/fee allocation +
Vendor net allocation`, with actual collector/currency and adjustment timing
defined explicitly. A Vendor-direct principal receipt must not independently
create a platform-funded principal liability. A reserved/paid Vendor entitlement
must not remain freely spendable or payout-eligible elsewhere.

These equations require business decisions about cancellation fees, fee payer,
cash custody, settlement schedule and rounding. None was silently chosen here.

## Principal versus Wallet, entitlement, payable and payout

| Collection / business | Vendor Wallet principal | Vendor entitlement | Platform principal payable | Payout eligibility today |
| --- | --- | --- | --- | --- |
| Direct Booking | No automatic principal top-up to Vendor Wallet in the traced charge/cancellation path; principal goes to Vendor merchant | Seller-fee amount is represented; no platform-remittance allocation | Normal direct Booking suppresses it | Generic Seller payout requests are not restricted to platform-held unpaid entitlement: duplicate compensation remains possible if approved |
| Direct Product | Verified direct charge does not itself credit Vendor Wallet; fulfillment instead credits Admin Wallet, regardless of collector | Seller-fee accessor, no common funded allocation | No Order equivalent | Generic payout can be requested without allocating the already direct-paid purchase; no software conservation boundary |
| Platform Booking | External charge is not itself an internal Vendor Wallet transfer | Positive seller-fee pending payable on paid record | Yes by Booking choice, not actual rail custody | Not reserved/consumed by generic payout; request approval is unrelated to payable balance |
| Platform Product | No provider-charge Vendor Wallet principal credit in the traced path; legacy gross Admin fulfillment credit | Seller-fee accessor without a funded liability allocation | Missing: platform can receive principal without matching Order Vendor liability | Generic payout cannot reconcile this missing allocation |

Thus direct principal plus automatic **Booking platform payable** is not the
normal source path. That does not make double payout impossible: generic approved
payouts have no entitlement allocation, products carry conflicting accounting,
and partial funding can duplicate per-Transaction fees/payables.

## External refund and already-paid Vendor handling

The **application server**, via legacy lifecycle refund callers, sends provider
requests using the payment's **current global payload**, not Customer browser
credentials. Adapter response checks differ; Paystack is non-authoritative and
PayPal's decoded-array response handling is broken. The dispatcher skips
Cash/Wallet/ZainCash; Order cancellation also skips external dispatch when any
OrderRefund record exists, not only a completed provider return.

Accepted Product internal reimbursement creates its once-only internal credit;
it does not establish external provider refund success or a complete original
commission/Vendor settlement reversal. Booking cancellation makes the once-only
native payable adjustment and preserves native fee policy, not an external
refund/paid-Vendor recovery. There is no durable allocation connecting either
internal reimbursement to provider refund and consumed Vendor payout.

For direct MTN/Orange, possessing collection credentials does **not** demonstrate
provider refund authority. No native direct-refund caller/verifier exists. A
Vendor might have to refund externally under its merchant agreement, but that
policy/authority cannot be inferred or silently imposed; authoritative refund
ingestion and commission recovery are missing.

No consumed payout allocation exists to distinguish unpaid payable reduction
from an already-paid Vendor's debt/clawback. The safe future model needs explicit
paid-allocation recovery/reversal states and confirmation, not another unbacked
Wallet credit or an assumed successful external refund.

## Ordered implementation sequence — requires further approval

1. **P0/P1 financial correctness:** contain the newly documented Product
   fulfillment repeat-credit path; fix payout double debit/atomicity; prevent
   generic electronic paid-state fabrication before activation. Do not reopen
   the seven accepted containment baselines.
2. **Accounting model:** define actual custody, funded allocations, explicit fee/
   commission/Vendor entitlement, currency precision and shared uniqueness;
   separate fee receivable from principal payable and internal Wallet reserve.
3. **Collection model:** immutable Order/Booking merchant snapshots; prove direct
   mode never creates duplicate platform principal liability; define mixed/partial
   Wallet contribution allocation and multi-Shop/currency rejection.
4. **Provider readiness:** distinguish registration capability from charging;
   declared country/currency/transaction coverage; original-account verification
   and credential rotation/recovery; no external legal assumptions.
5. **Refunds:** provider-aware durable refund intents and original-collector
   routing, partial allocation, authoritative confirmation, rollback/recovery,
   commission/entitlement reversals and double-refund prevention.
6. **Payout/reconciliation:** reserve specific earned entitlements, beneficiary/
   currency authorization, idempotent provider disbursement, failure recovery and
   provider-to-ledger reconciliation. No Admin Wallet custody proxy.
7. **Production verification:** approved sandbox fixtures, production-engine
   races, conservation and duplicate settlement/refund/payout tests, audit trails,
   secrets-at-rest review and operational runbooks.
8. **Activation:** explicit merchant/country/currency/provider approval only after
   the above gates; staged real operation and publishing need separate approval.