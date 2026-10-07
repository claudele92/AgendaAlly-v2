# Payment Phase 1 — activation-critical financial correctness

**Verified 2026-10-03. P1-A and P1-B: CONTAINED / VERIFIED within the bounded
native implementation and isolated verification below. STOPPED.**

This is not provider activation, external payout implementation, accounting
allocation completion, or production certification. The completed architecture
audit and accepted payment P0 reports remain authoritative. Earlier findings are
not erased by these corrections.

## 1. Current-source trace and root causes

### P1-A: payout conservation / atomicity

Native source chain:

- `POST /api/v1/dashboard/admin/payouts/{id}/status`
  → Admin `PayoutsController::statusChange`
  → `PayoutService::statusChange`.
- Admin/Seller/Deliveryman payout creation and updates use the same
  `PayoutService`; their update requests do not authorize status changes.
  Seller/Deliveryman updates require the persisted creator to match the actor.
- Admin approval is protected by the existing Admin/manager route. Existing
  country scopes remain in effect.
- `Payout` has native `pending`, `canceled`, and `accepted` statuses. The service
  already rejected an accepted payout, but that check was not locked.
- Wallet payout means **approver Wallet → request creator Wallet**, not provider
  remittance or allocation against an earned Vendor liability.
- `WalletHistoryService` credits a paid topup and debits a withdrawal itself.
  Its histories have their own Transactions. Native Wallet-payable summaries
  use the shared `Payable` Wallet/payment key and default to `progress`.
- The Transaction observer's fee/payable effects apply to Booking/Order, not
  Wallet/WalletHistory. This payout flow has no separate earned-liability
  reservation, payable allocation, or external provider operation.

Previously, approval saved status/approver before financial work, ignored native
service failure results, explicitly reduced the approver Wallet, then invoked
the withdrawal history which reduced it again. It had no encompassing
transaction. It also wrote Wallet summaries for non-accepted status requests.

### P1-B: generic Booking electronic paid authority

Native source chain:

- `POST /api/v1/payments/booking/{id}/transactions`
  → Payment `TransactionController::store`
  → native `TransactionRequest`
  → `PaymentContextFactory::target` and
  `PaymentEligibilityService::assertEligible`
  → `TransactionService::bookingTransaction`.
- The authenticated Customer must own the persisted Booking. Context resolves
  its persisted Shop, business currency, and frozen collection mode. Client
  market/currency/Shop/collection hints are not the authority.
- The request accepts an active payment ID, optional reference, price and user
  ID. These are not provider verification.
- Previously `checkPayment` returned success for **any active non-Wallet
  method**, after which the parent and children were written `paid`. Paid
  Booking Transactions can trigger the native fee and Shop-payable observer.

The correction does not mistake eligibility/configuration for a collected
provider payment. The generic path is now a Cash/Wallet path only; electronic
methods fail closed before creating a Transaction, performing a Wallet debit,
or producing fee/payable/refund/payout entitlement.

## 2. Production files changed

All paths below are relative to `.migration-backup/backend/`:

| File | Bounded change |
|---|---|
| `app/Services/PayoutService/PayoutService.php` | One locked transaction; once-only native accepted claim; one credit/one debit; checked required saves/results; immutable accepted financial terms; fresh locked updates. |
| `app/Services/WalletHistoryService/WalletHistoryService.php` | Strict payout opt-in using the existing required-history contract; checked withdrawal affected-row result. Ordinary `create(array)` and its override signature are unchanged. |
| `app/Services/TransactionService/BookingPaymentAuthority.php` | Shared small rule distinguishing native Cash/Wallet from electronic methods; manual Booking status is not provider proof. |
| `app/Services/TransactionService/TransactionService.php` | Owned generic Booking selection; active Cash/Wallet only; server amounts; child owner/currency consistency; same-method already-paid Cash/Wallet no-op; Booking class dispatch cannot bypass the rule; Admin service status sibling guarded. |
| `app/Http/Controllers/API/v1/Dashboard/Payment/TransactionController.php` | Shared guard on the manual Booking electronic-paid sibling. Non-Booking status ownership/reason contracts are retained. |
| `app/Services/BookingService/BookingService.php` | Ending a Booking no longer turns electronic `progress` into `paid`; commerce update remains permitted. Native Cash/Wallet behavior is retained. |

**Schema changes: none. Application financial/non-financial configuration
changes: none.** Existing provider code, capability metadata, credentials,
country/currency support, and Order finality implementation are unchanged.

## 3. Financial transaction and replay boundary

Payout approval:

1. Validate the native status and Admin/manager actor.
2. Start `DB::transaction`; refetch and lock the payout.
3. Reject accepted/same-status replay. Pending/canceled lifecycle changes have
   no money or new Wallet-summary legs.
4. Validate amount, creator, method, and both Wallets; lock Wallets in stable ID
   order and read the funding balance after those locks.
5. Acquire a conditional payout-status claim to `accepted`. This is **inside
   the transaction**, not a separately committed success indicator.
6. For Wallet, require a paid recipient topup history/Transaction/link/credit,
   then a paid approver withdrawal history/Transaction/link/debit.
7. Require native Wallet-payable `progress` summaries and the final payout
   save/approver. Keep the native Wallet/payment summary identity. Use an
   explicit `firstOrNew`/checked `save` here because `updateOrCreate` can hide a
   vetoed save of an already-existing summary.
8. Commit all effects together. Required false results or exceptions throw
   inside the enclosing transaction and restore the prior retryable state.

For amount T and different Wallets:

`funding_after = funding_before − T`

`recipient_after = recipient_before + T`

`funding_after + recipient_after = funding_before + recipient_before`

The isolated native T=20 fixture changes funding 80→60 and recipient 100→120,
not funding 80→40. It persists one withdrawal and one matching topup with paid
history Transactions. Shared-Wallet self-transfer keeps net balance unchanged.
The conserved quantities are the existing nominal Wallet accounting units;
this is not a new currency/custody/allocation model.

Accepted financial price/currency/method cannot be edited through generic
updates; request status/creator/approver fields cannot reopen the boundary.
Fresh locking prevents stale-model updates from undoing completion. Metadata
remains separately editable. Admin replay and reachable Vendor updates cannot
repeat financial effects. Rolled-back attempts can retry and complete once.

Non-Wallet `accepted` remains **native bookkeeping only**, with atomic
`progress` summaries and no transfer or provider-success assertion. It is not
external settlement certification. Historical accepted records are not
reconciled or inferred to have correctly settled.

## 4. Electronic verification and narrow sibling review

Only native Cash and Wallet are declared distinct offline/internal methods.
Unknown/provider tags fail closed; names such as `zain-cash` do not turn an
electronic provider into offline cash.

For the generic Booking path:

- Persisted ownership and amount determine Customer/Booking/children.
- Existing eligibility context determines Shop, currency and frozen collection
  mode on HTTP requests; service-level denial cannot be bypassed by skipping the
  controller or using the alternate Booking class parameter.
- Request `paid`, `verified`, merchant proof flags, currency hints, prices,
  actor/Shop/Booking fields, and reference strings cannot authorize settlement.
- A fully already-paid same-method Cash/Wallet target is non-financial on replay.
  Cash retains its native paid-bookkeeping meaning. Wallet keeps its native
  persisted amount and withdrawal-history arithmetic.
- The generic electronic endpoint rejects even a submitted real reference; it
  does not act as a second verifier or reference-reuse settlement surface.
  Existing verified payment state remains untouched.

The related native paid paths were also traced:

- `PUT /api/v1/payments/booking/{id}/transactions`: manual Admin reason/token
  lookup is not electronic provider proof; shared guard denies electronic paid.
- `POST /api/v1/dashboard/admin/transactions/{id}`:
  `TransactionService::updateStatus` applies the same Booking guard.
- `BookingService::update` with commerce status `ended`: commerce can complete
  while electronic payment remains `progress`.
- Native Booking creation initializes Cash/Wallet using their existing meaning
  and electronic progress; trusted provider settlement remains separate.
- Existing provider-specific controllers/reconciliation call verified
  `BaseService::afterHook`; those verifier paths were not changed.

The supported isolated MTN positive test exercises the **real MTN status
verifier and real native afterHook/Transaction observer**. Only configuration
lookup and the outbound status response are synthetic. The verifier checks
owner/provider/config fingerprint, successful result, external reference,
merchant currency and exact amount; afterHook checks the bound intent evidence.
Matching evidence pays once and produces the native fee/payable rows. Replays
add none. Wrong amount, currency, reference, foreign HTTP Booking and
inconsistent intent/model binding fail closed. No provider is called.

Native progress→paid Booking fee/payable effects are preserved for legitimate
verification. Their existing transaction-based allocation limitations are not
redesigned here.

## 5. Verification receipts

All financial actions ran in isolated test databases, never development
financial records. PHP 8.4.16; SQLite 3.51.1.

| Verification | Result |
|---|---|
| Payout containment | 24 tests / 319 assertions |
| Payout controlled contention | 1 test / 19 assertions |
| Booking paid authority | 19 tests / 91 assertions |
| **Focused total** | **44 tests / 429 assertions; passed** |
| **Selected accepted payment/provider regressions** | **454 tests / 3,066 assertions; passed, one existing deprecation** |
| Production-file PHP lint and diff whitespace check | Passed |
| Native backend workflow restart | Started cleanly on its existing port |
| Running API preview health capture | `{"status":"ok"}` |

Commands:

```sh
php .migration-backup/backend/vendor/bin/phpunit \
  -c .local/payment-phase1-focused.xml \
  --log-junit .local/payment-phase1-focused-junit.xml
php .migration-backup/backend/vendor/bin/phpunit \
  -c .local/payment-phase1-regressions.xml \
  --log-junit .local/payment-phase1-regressions-junit.xml
```

Payout faults cover before credit; after credit returning false/throwing;
withdrawal before/after mutation; required history creation/link/Transaction
save veto; first/second Wallet summary creation and **existing summary update**
veto; zero-row withdrawal arithmetic; finalization false/exception;
insufficient balance; missing Wallet; invalid amount; replay; successful retry.
Whole persisted payout/Wallet/history/Transaction/Shop/Order/refund/fee-ledger
fingerprints are checked, not just HTTP responses.

Native Admin and Seller HTTP fixtures exercise request validation, middleware,
binding and the actual controllers/service. Booking fixtures exercise actual
eligibility and request processing. Only nested response presentation/push
transport is isolated where unrelated to financial authority.

Selected regressions cover all accepted Product fulfillment, Product refund,
Booking refund, Booking Staff, Seller status/refund, Wallet transfer/amount and
observer containments, plus eligibility, intent verification, provider callbacks,
settlement idempotency and refund authorization.

The first expanded-regression run had four isolated-bootstrap errors (`Str` and
`Http` aliases absent). Selecting the existing native hardening bootstrap fixed
fixture initialization; no production code/provider configuration was changed
for those errors. The completed rerun is the receipt above.

The full project suite was **not** rerun and is **not** claimed green.
The pre-existing failed `original-hardening` workflow remains outside this
bounded correction.

### Controlled contention

The payout fixture copies its isolated SQLite database to a temporary file and
opens two independent connections. While the winner holds its uncommitted
native payout claim, the second **full native service** reads pending state but
cannot acquire the write. It returns a locked failure without partial effects.
The winner commits one conserved batch. The second connection then sees
accepted; retry cannot settle again and a pending-only raw claim affects zero
rows. Temporary databases are removed.

**SQLITE CONCURRENCY VERIFIED.**

**PRODUCTION-ENGINE CONCURRENCY NOT CERTIFIED.**

Existing native locking/CAS is retained, not weakened to suit SQLite. No
production database connection, concurrency exercise or provider operation ran.

## 6. Protected development state

Before any application edit, `.local/payment-phase1-before.json` captured the
**current post-P0-B migrated database**, using
`.local/payment-containment-snapshot.php`. Completion receipt:
`.local/payment-phase1-after.json`.

Codec: PDO `FETCH_ASSOC`, JSON unescaped Unicode/slashes with preserved zero
fraction, sort encoded full rows lexically, newline join, SHA-256. This compares
actual original fields, not counts alone. Both complete snapshot files have
SHA-256:

`d500edda9dcb1448ac7f82090fe4b7f35a6326246ddf35831d7af765da45bd56`

**All 53 protected tables have exactly identical before/after counts and
fingerprints. Zero unintended financial mutations.**

All 12 existing Orders remain `unverified`. No classification, override,
historical inference or legacy financial repair occurred. Their current
migrated-schema fingerprint, not a pre-migration schema receipt, is used.

In the matrix, both count and SHA-256 are **before = after**.

| Protected table | Count | SHA-256 |
|---|---:|---|
| `booking_activities` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `booking_coupons` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `booking_extra_times` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `booking_extras` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `bookings` | 4 | `f69fa6c983b30976ea156dfed854eb2988c7c3d2b8ec1207098d1c067c8c10f0` |
| `cart_detail_products` | 2 | `3961506d34e48b481f45e1aaf362be34e9260092ab65ad0fbbc5d2b302bc51b0` |
| `cart_details` | 2 | `23684122c1c717dec57e7d501cb3520de8ea7a359758c05222958587f6c124c9` |
| `carts` | 2 | `67a35295b220cf46a7440e2d338e3a3d61bbd41535ed4b9c17d15a8409aa955f` |
| `countries` | 4 | `e8d6e158f09925d157297835628c7f8a6132285f712ed466383fb2f4e750893d` |
| `country_admins` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `country_currency_backfill_backup` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `country_invitations` | 2 | `30d4b12bcd5e93e9aecfe141bf6e11de621c51ccf700d9ec6f79675378dd786e` |
| `country_payments` | 37 | `93f3afb0925e351a0b6e7e75d2286fa86989d8fffe7519b4a272a1685505b1a1` |
| `country_payments_backfill_backup` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `country_permissions` | 23 | `bc8b2a8ce681b39ba9543fd32848ddfce0d03a7dcd99bcd5d295fdd4857da87e` |
| `country_role_permissions` | 117 | `71d6860d7eda482abb7af9c07c96cd3a382b60d9bbfe86b9ec9a699680571f56` |
| `country_roles` | 13 | `eed70fe98208e4e16adf0d713f2d117abc89a75c2e9b5fa02b2783f3358b8bd6` |
| `country_translations` | 4 | `489a802502e6c8e5f632ae3fca3512c50f8ca7099f549bbda45101cad26d1edf` |
| `currencies` | 10 | `64543b77e69e44d0ba2180948c09793d3c5cf1c9eaaa751369fa9a3f2a27f7a0` |
| `email_subscriptions` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `gift_cart_translations` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `gift_carts` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `member_ship_services` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `member_ship_translations` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `member_ships` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `order_coupons` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `order_details` | 12 | `f0b21a600d101a4a6c3565e484576c35d437ea79b7666d04b4b95d46aee78ec1` |
| `order_refunds` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `order_status_notes` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `order_statuses` | 7 | `075e89a6c33c4aa2adc900d16de60a436975c0bdd7d57a8f9bf5f7defe19878f` |
| `orders` | 12 | `68d4a1dc28f56a2220ef1eab14650f5c312a4f4d1fd332ebc91950c401bd637b` |
| `parcel_order_settings` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `parcel_orders` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `payment_payloads` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `payment_process` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `payment_to_partners` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `payments` | 18 | `4e9e9ec2fa5d1c68227be75160f74c138083f1878e6ad93d6e1c51570836cdb8` |
| `payouts` | 1 | `cfbae11fba1078d5e8619746d8bf6cb356bea2ac082ac975a8053072cfafbf6a` |
| `platform_fee_ledger_entries` | 1 | `5363b4df983a62eeee9332a417dcbc606b5342bd57bc23c3f9a23cf060abe896` |
| `platform_payment_configs` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `seller_booking_clients` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `seller_currency_backfill_backup` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `settings` | 23 | `f5c20ffc025e2c626ee5927d3c9ef81887b6aed93d9297a51f9e88c432f46439` |
| `shop_payments` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `shop_subscriptions` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `shops` | 9 | `eb63a7ebb70684b6caa69b106d0f20894da96c199b1ce9fd71f6a3f59c5fbe1e` |
| `subscriptions` | 4 | `31735a3113e6f757be5d1ed6f41277f479632a3fa5c04a85dc3186f9e1768acc` |
| `transactions` | 15 | `2a7ea8122814b3574864e3276c8e04e69f11bde8fd233ae92c00796e139c02ae` |
| `user_carts` | 2 | `bdceaa2abd4b4472667d0a9086ca59733927ddb20539f17e581937fc3e23e108` |
| `user_gift_carts` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `user_member_ships` | 0 | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `wallet_histories` | 1 | `bc61a6e580b16e8a703d348dae7e820d556fbe84c579e8c6d469ae6e9fc4cbb2` |
| `wallets` | 36 | `418cc7fc78e1faf07e84bfab21e9d8eaf939402823acd2cc641de43e82cac16b` |

## 7. Remaining limitations and final stop

- No electronic rail is made checkout-ready by this phase.
- Payout allocation against earned liability, reserves, custody, beneficiary
  verification, remittance and reconciliation remain deferred.
- Native Wallet-payable summaries are shared/reused records, not an immutable
  per-payout external settlement ledger.
- Existing transaction-based Booking fee/payable partial-duplication,
  provider refunds, Vendor-direct commission recovery, Product collection
  snapshots and immutable allocation need later separately approved work.
- No historical payout correction, legacy Order classification, financial
  inference, override or reconciliation tool was added.
- No generalized payment-idempotency framework or new financial schema was
  introduced.
- No independent immediately exploitable financial P0 was confirmed by the
  required narrow sibling review. No general audit was restarted.
- No application provider was activated/configured; synthetic eligible/configured
  states existed only inside isolated fixtures.
- No real charge, refund, payout, Wallet transfer or existing-data financial
  operation occurred. No publishing.

**Both bounded findings may be marked CONTAINED / VERIFIED with these
limitations. STOPPED; await explicit approval before the next phase.**