# Product fulfillment repeat-credit P0 — mandatory new-P0 stop

**Historical accepted handoff.** Subsequent dual-P0 approval/result:
[authorization verified; financial finality stopped before migration](payment-product-fulfillment-dual-p0-containment.md).
The original stop evidence below is retained; its zero-test/no-source-edit facts
describe that earlier attempt, not the subsequent containment.

2026-10-03. **CONTAINMENT NOT IMPLEMENTED / NOT VERIFIED.**

The completed payment architecture audit remains the authoritative baseline.
The latest bounded fulfillment brief was followed through source tracing and
protected-state capture. Its **section 15 new-financial-P0 exception** was reached
before application edits, test mutations or choosing a finality mechanism.

## Original finding and intended containment

The source-confirmed **fulfillment replay P0 remains UNCONTAINED**.
`OrderStatusUpdateService::statusUpdate` couples each entry into delivered to
`adminWalletTopUp`, which creates a paid gross Order-total top-up for the first
Admin with a Wallet. A lifecycle cycle through another non-canceled accepted
state creates another credit without another Customer payment.

The effect is **legacy internal gross accounting**, not verified provider/Cash
custody, Vendor entitlement or provider remittance. No new accounting meaning
was chosen. A durable per-Order financial boundary is still needed.

## New independent source-confirmed financial P0

**FOUND / SOURCE-CONFIRMED / UNCONTAINED — NOT EXERCISED.**

**An authenticated Customer can invoke the first delivery financial settlement
for a `new` Order without Seller/delivery authority or an ownership-constrained
lookup.** The Customer path accepts `delivered`, delegates the same financial
service, credits the Admin Wallet and can mark a Cash Transaction paid.

This is a first-settlement authority/ownership defect, not status-cycle replay.
A once-only claim would still permit its first unauthorized settlement.
Containment of repeated settlement alone therefore would not fix this finding.
It was discovered while identifying the required shared fulfillment callers,
not by starting a general Order authorization audit.

### Exact native source chain

All paths below are relative to `.migration-backup/backend`.

| Step | Evidence |
| --- | --- |
| Authenticated Customer route | `routes/api.php:297,318,337`: dashboard/user groups use `sanctum.check`; `POST /api/v1/dashboard/user/orders/{id}/status/change` dispatches User OrderController |
| Authentication is not settlement authority | `app/Http/Middleware/SanctumCheck.php:26–35` checks only authenticated Sanctum state. `User/UserBaseController.php` adds the same middleware, not a Seller/Shop settlement grant |
| No FormRequest ownership/actor restriction | `app/Http/Requests/BaseRequest.php::authorize` returns true. `app/Http/Requests/Order/StatusUpdateRequest.php:18–24` accepts every `Order::STATUSES`, including delivered |
| No Order-owner predicate | `app/Http/Controllers/API/v1/Dashboard/User/OrderController.php:225–238` loads `Order::with(...)->find($id)` without `where(user_id, authenticated id)` or Shop/grant authorization |
| Only lifecycle precondition | Controller:247–251 requires current `status=new`; it does not restrict requested status to Customer cancellation or prove fulfillment/payment |
| Shared financial dispatch | Controller:254 passes validated data to `OrderStatusUpdateService::statusUpdate` |
| Gross financial effect | `app/Services/OrderService/OrderStatusUpdateService.php:67–81,180–198`: delivered invokes Admin gross top-up, cashback and Cash paid-state update; no actor/funding authorization is added by this service |
| Actual credit/history/Transaction | `app/Services/WalletHistoryService/WalletHistoryService.php:32–38,49–99`: positive amount passes validation; a paid top-up history and paid Transaction are created and its user's Wallet is incremented |
| No Customer-owner global scope substitute | `app/Models/Order.php` uses the Shop-country scope, not a Customer ownership scope. `app/Models/Scopes/CountryRestrictionScope.php` restricts country admins only; ordinary Customers have no such ownership filter |

### Preconditions and financial consequence

- An authenticated ordinary Customer.
- An existing `new` Product Order with positive finite persisted gross total.
- The ordinary native Admin-with-Wallet and Wallet payment catalog prerequisites
  for `adminWalletTopUp` / WalletHistory creation.
- Successful ordinary SQL financial writes. No external merchant credentials,
  active electronic provider or provider call is required.
- For cross-owner mutation, the target is another Customer's `new` Order; its
  identifier is accepted by the unscoped lookup. No actual identifier was used
  or sought in this investigation.

For gross total `T > 0`, the path can create a paid `+T` Admin Wallet credit
without an authorized Seller/delivery completion and without the service
requiring a new Customer funding debit. If the existing Transaction is Cash,
the same path changes it to paid. Applicable configured cashback can additionally
credit the Order's Customer; that conditional effect is not needed to establish
the gross-credit finding.

The unauthorized first settlement applies even to the actor's own `new` Order.
The missing owner predicate additionally permits cross-Customer/cross-Shop target
selection. This does **not** allege that the separate Seller controller's
same-Shop query is bypassed; it is the Customer route to the shared service.

This qualifies as an immediate source-confirmed financial-mutation P0, not
dormant-provider behavior, speculative concurrency or ordinary missing payout/
custody architecture. No historical exploitation or real mutation is inferred.

## Bounded trace completed before the stop

- Seller and Admin Order status controllers call the shared service.
- Deliveryman controller calls it after its existing assigned-Order membership/
  ownership checks and allowed-status filter.
- Customer controller also calls it, exposing the independent P0 above.
- Generic Order update, all-digital cart completion and all-digital OrderDetail
  updates can write delivered directly; they do not themselves invoke the
  gross-top-up branch. Their lifecycle writes would matter to any proposed
  durable-marker reset/reopen review.
- Shared delivered effects: Admin paid WalletHistory/Transaction/credit,
  configured cashback history/credit/PointHistory, Cash paid-state observer/fee
  effects, and referral after-response dispatch. Status notes, Order logging,
  email/event/push and notifications are separate presentation/lifecycle effects.
- Cashback helper currently catches exceptions and ignores false Wallet results.
  This is relevant to the requested future rollback boundary; it was not changed.
- Stock changes are in Order creation/detail operations, not the shared delivered
  branch itself. No inventory/capacity effects were invented or repaired.

This is not a completed authorization matrix or full marker-reset/deletion audit.
Investigation stopped at the new P0, as required.

## Containment, financial identity and status behavior

**No mechanism implemented; no financial identity selected.**
The existing Order/Transaction/history/fee-ledger relationships were being
examined. No schema change, new ledger semantics or migration was performed.
The feasibility of a safe schema-free durable claim is not certified.

Current identical-status rejection is insufficient; delivered → another status
→ delivered remains able to repeat settlement. No lifecycle restriction was
added and no reopening policy was invented.

## Atomicity, false results and cashback finality

No new atomic claim, finality field or SQL uniqueness boundary was implemented.
Existing outer financial transaction does not provide durable financial
finality. Required rollback, native-false result, retry and cashback once-only
cases remain **not tested** for this containment.

No separate general cashback investigation or cashback repair was undertaken.

## Authorization and tests

**Focused tests run for this request: 0. Assertions: 0.**

No mutation fixture or endpoint was used to exploit either finding. Source
preconditions were checked through the exact native middleware/request/controller/
service chain. Seller, Staff, Admin, Customer and Driver authorization test
coverage requested for a successful containment remains pending because of the
mandatory stop. Previous seven accepted containments retain their existing
verification; no broader suite or full-project pass is claimed.

## Concurrency

No new two-connection fulfillment claim test was run; no claim was implemented.
**Production concurrency remains uncertified.**

## Protected owned-development state

Established `.local/payment-containment-snapshot.php` serializer:
PDO associative rows → JSON with unescaped Unicode/slashes and preserved zero
fraction → encoded rows sorted `SORT_STRING` → newline join → SHA-256.

Receipts:

- `.local/product-fulfillment-containment-before.json`
- `.local/product-fulfillment-containment-after.json`
- `.local/product-fulfillment-containment-fingerprint-result.json`

**53 protected tables; identical table sets, codec, per-table row counts and
fingerprints; zero changed tables. `platform_fee_ledger_entries` identical.**
The snapshot uses `PRAGMA query_only=ON`. No individual balances, identities or
Order financial values were inspected to infer historical exploitation.

## Changed files

**Application/source/test files changed: none.**

Documentation only:

- This blocked-containment/new-P0 report.
- `docs/development/payment-system-full-audit.md`: narrow follow-on stop note and
  new source-confirmed backlog entry; completed architecture conclusions retained.
- `.agents/memory/modernization-approval.md` and its index: latest bounded approval
  and new-independent-P0 stop rule, replacing the older automatic-completion rule
  as the current authorization.

The bounded snapshot receipt files above were generated. No workflow/package/
environment/credential/provider/configuration changes or restarts were needed.

## Limitations and required next approval

- Original fulfillment replay **not contained or verified**.
- New Customer first-settlement authority P0 **not contained or exercised**.
- Production concurrency not certified.
- No historical reconciliation/repair.
- No provider activation or real financial operations.
- No complete Product custody/settlement architecture certification.
- No refund/payout/reconciliation certification.
- No publishing or broader backlog continuation.

**STOPPED under section 15.** Further work requires explicit approval defining
whether to contain the Customer Order-status financial-authority defect and
then resume the original fulfillment-finality containment. No such work has
started, and no additional optional task was proposed.