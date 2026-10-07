# Wallet P0 containment and native engine evidence

2026-10-04, America/Chicago. Authorization: the attached Wallet Concurrent-Spend
P0 Containment brief. **Wallet containment verified; full engines NOT CERTIFIED.**
No production or provider activation is claimed.

## Boundary and exact results

The old cached balance check preceded an unguarded decrement. A contender could
subtract 70 from the first sender's committed 30. A transaction alone did not
serialize that sufficient-funds decision. `WalletDebit` now locks the current
Wallet PK inside the financial transaction, validates persisted ownership,
UUID/currency and conditionally decrements only sufficient current funds.
Transfer locks are sorted; debit failure throws so callers cannot ignore it and
commit uncovered history/Transaction/recipient credit.

Each engine independently demonstrated these final native balances. Recipient
starts at 30; the unrelated Wallet remains 80. “Units” use the fixture's frozen
two-decimal native scale, without binary-float conservation comparisons.

| Authorized pair from sender 100 | Accepted sends | Sender | Recipient | Unrelated |
|---|---:|---:|---:|---:|
| 70 + 70 | 1 | 30 | 100 | 80 |
| 60 + 60 | 1 | 40 | 90 | 80 |
| 70 + 40 | 1 | 30 | 100 | 80 |
| 40 + 50 | 2 | 10 | 120 | 80 |
| 100 + 1 | 1 | 0 | 130 | 80 |
| 50 + 50 | 2 | 0 | 130 | 80 |

For the original 70+70 gate: HTTP 200 and 400; exact units
**[3000,10000,8000]**, two paid histories and two Transactions, no uncovered leg.
Independent funded senders' 70+70 credits yield **[3000,17000,1000]**.
Opposing funded transfers preserve **[10000,10000,8000]**.

## Evidence register and honest scope

Primary current artifacts:
- `.local/wallet-engine-proof/evidence/{mysql,pgsql}/wallet-matrix.json`: **28/28**
  groups on each native engine, with SQL traces, real lock waits, root cleanup,
  exact rows, native Product/Booking contributions, canonical failure rollback,
  funded-payable replay, terminal transfer replay and real Wallet deadlock retry.
- `.local/wallet-certification/correction-evidence/matrix/{mysql,pgsql}-expanded.json`:
  exercised core reservations/confirmation/timeout/deadlock/fulfillment evidence.
  The retired negative Wallet assertion is deliberately superseded, not a pass.
  Earlier interrupted “original thirteen” entries are not fresh consolidated passes.
- `postgres13-output.txt`/`postgres13-junit.xml`: **13/51 PASS**, same original
  financial bodies with explicit PG connection, native SQLSTATE and timeout adapters.
- MySQL unchanged original thirteen: historical **13/51** receipt retained.
  Fresh run completed eleven dot-reported cases before the shell time limit;
  remaining two completed as **2/4 PASS** in `original13-last-two-*`.
  There is **no fresh consolidated thirteen-case JUnit receipt**.
- Native accounting: PostgreSQL **21/211 PASS**; MySQL seventeen completed
  dot-reported cases plus the remaining four **4/10 PASS**. The interrupted
  MySQL file is not presented as a complete 21-case JUnit receipt.
- Native completion/identity: MySQL **16/61 PASS**. PostgreSQL first nine complete
  cases preceded a temporary-adapter down/up error; failed case plus remaining
  six then pass **7/22**. Do not sum overlapping/interrupted assertion totals.
- Receipt self-anchor/FK/down-up guards: MySQL **7/26 PASS**; PG first three
  complete cases retained, last four **4/14 PASS** after adapting native FK
  SQLSTATE and managed savepoint semantics. Expected PG statement errors require
  rollback/savepoints; they are not MySQL statement-recovery semantics.
- SQLite changed/relevant regressions: spend **7/31**, transfer **24/183**,
  Booking Wallet **2/11**, Product Cash/Wallet **2/18**, amount **160/922**,
  fulfillment **42/572**, Booking refund **31/117**, all PASS. These are individual
  suite counts, not a deduplicated aggregate or production concurrency proof.
- `.local/wallet-engine-proof/evidence/protected-comparison.json`: 59 tables,
  465 schema objects, twelve unverified Orders all match.

Commerce fixture repairs were isolated: authentic worker guard, actual missing
native quote fields, correct FK/nullability/ID types and PG sequences. The PG
completion adapter's retained functions/legacy-table triggers needed idempotent
reinstallation on empty down/up. Changes exist only in proof adapters; original
production migration helpers and economic predicates were not weakened.

Both engines pass native fulfillment first-batch/replay and real two-worker
claim contention: one vendor income, cashback, point/commission batch and clean
roots. Core same-receipt confirmation converges; changed receipt fails closed.
Reservations conserve caps and exact remainders; independent allocations progress;
rollback, native timeout, root deadlock retry and PG old-RR/clean-RC diagnostics run.
Refund, receivable and payable reservation contention are exercised; external
payout finalization is deliberately denied, not simulated as an actual payout.

### Engine decisions

| Candidate | Wallet | Financial gates exercised | Full certification |
|---|---|---|---|
| MySQL 8.0.42/InnoDB, fresh owned RR | PASS 28/28 | PASS within the documented receipt scope | **NOT CERTIFIED** |
| PostgreSQL 16.15, owned RC | PASS 28/28 | PASS, including 13-case semantic mirror | **NOT CERTIFIED** |

The actual MTN identity migration rejects both native drivers pending separately
reviewed DDL. Committed PG accounting/completion helpers are not a permanent PG
port; temporary overlays do not make them one. The five native SQL probes all
execute: MySQL supports them; PG rejects the inventoried MySQL JSON append,
SHOW/backtick/HAVING-alias and Haversine ROUND forms. Unsupported probes are
compatibility failures, not passes. Full historical/bootstrap parity is therefore
**BLOCKED/UNPROVEN**, not silently marked PASS. No new independent financial P0
was demonstrated. No winning production engine is selected.

The requested conditional application expansion requires at least one certified
candidate. That condition was not met. Existing delivered provider/operations
capabilities remain as documented in the single completion matrix; no claim that
all remaining application work has become external is made.

## Source, preserved state and operations

Wallet-brief production edits:
1. `app/Services/WalletHistoryService/WalletDebit.php` (new).
2. `app/Services/WalletHistoryService/WalletHistoryService.php`.
3. `app/Http/Controllers/API/v1/Dashboard/User/WalletController.php`.
4. `app/Services/PaymentService/BaseService.php`.
5. `app/Services/BookingService/BookingService.php`.
6. `app/Services/BookingService/BookingCancellationSettlement.php`.
7. `app/Services/OrderService/OrderStatusUpdateService.php`.

All paths are under `.migration-backup/backend/`. The accepted earlier
`AllocationWriter`/confirmation-test diff is retained unchanged by this Wallet
correction. Focused `WalletSpendBoundaryTest` is added. No owned migration,
schema conversion, financial identity/equation/custody redesign, Strategy G,
provider activation, credentials, real provider transaction/refund/payout,
production access, legacy classification/deletion or publishing.

Original failed `agendaally_payment_disposable_proof` databases and historical
dumps remain untouched. New work used disposable `..._wallet`, `..._wallet_cert`
and a separate MySQL `..._walletthirteen`. Native servers remain available.
The Laravel preview restarted cleanly; its read-only payments catalogue returned
HTTP 200 with Wallet/Cash only. Proxy API health screenshot returned `status:ok`.
The pre-existing broad `original-hardening` failure was not rerun or claimed fixed.

## Required 56 answers

1. **Cause:** cached check followed by blind decrement; no shared current debit authority.
2. **Authority:** current PK lock, persisted identity check and sufficient-funds conditional decrement inside the existing financial unit.
3. **70+70 both succeed?** No; one succeeds, one is denied.
4. **Balances:** 30/100/80; exact native units 3000/10000/8000.
5. **60+60:** one succeeds; 40/90/80.
6. **70+40:** controlled 70 owner succeeds; 40 contender denied; 30/100/80.
7. **40+50:** both succeed; 10/120/80.
8. **50+50:** both succeed; 0/130/80.
9. **Product Wallet:** protected; native positive contribution, contention, canonical rollback and replay pass.
10. **Booking Wallet:** protected with the same native proofs and selected-Wallet regression.
11. **Withdrawals:** shared spend boundary; contention and pending-cancellation replay pass.
12. **Cross-operation conservation:** yes for exercised transfer/Product/Booking/withdrawal cases.
13. **Recipient credits:** both independently backed credits survive.
14. **Rollback:** all eight transfer points plus canonical Product/Booking integration preserve value/evidence.
15. **Replay:** completed transfer status transitions remain terminal; same funded payable cannot debit again, even after new funds; a separate funded transfer still works.
16. **Production files:** the seven Wallet-brief paths listed above; earlier confirmation edit retained.
17. **Schema:** no owned schema or migration change; disposable fixtures/temporary adapters only.
18. **MySQL Wallet:** 28/28 PASS.
19. **Full MySQL:** NOT CERTIFIED; native MTN/bootstrap DDL remains unapproved/unproven.
20. **PG Wallet:** 28/28 PASS independently.
21. **Full PG:** NOT CERTIFIED; permanent helpers, native MTN and legacy SQL port remain.
22. **Previously unrun gates:** targeted financial, fulfillment, PG mirror and negative/lifecycle gates executed; full historical bootstrap parity remains BLOCKED/UNPROVEN. NOT RUN/BLOCKED is not PASS.
23. **Confirmation:** same receipt converges with retained effects; different receipt fails closed.
24. **Fulfillment:** native first-batch, duplicate/replay and real contention pass on both.
25. **New financial P0:** none demonstrated; fixture, adapter lifecycle and known compatibility errors are not financial P0s.
26. **Certified engines:** neither full application candidate.
27. **Recommendation:** no forced winner; MySQL has fewer demonstrated SQL-port gaps, not a certified production selection.
28. **Flutterwave Product/Booking:** delivered bounded application initiation/verification/replay/recovery; not production/UAT certified; refund contract remains external.
29. **Paystack Product/Booking:** delivered application flows; merchant/currency/account UAT outstanding.
30. **Stripe Product/Booking:** delivered for declared narrow capability scope; no assumed Cameroon/XAF or connected-account support.
31. **PayPal Product/Booking:** delivered native collection/capture flows; market/merchant/currency UAT outstanding.
32. **Generic refunds:** functional identities, caps, reservations, cancellation, UNKNOWN retention and three supported adapters; not complete across unsupported provider contracts.
33. **Paystack refund defect:** contained; queued/accepted is not processed success; original revision/payment binding retained.
34. **PayPal refund defect:** contained; actual refund response/capture parsed, replay retained.
35. **Receivables:** authorized retained cash-receipt workflow operational; no automatic bank collection or Admin funding.
36. **Vendor payable reservation:** request/reserve/cancel operational; does not pay the Vendor.
37. **External payout rail:** NOT IMPLEMENTED/certified.
38. **Reconciliation:** partial internal original-identity recovery; bank settlement/payout ingestion absent.
39. **Admin operations:** bounded scoped UI/controls functional; live operational certification incomplete.
40. **Vendor operations:** bounded Shop-scoped views/configuration/granted controls functional; no cross-Shop access.
41. **MTN external:** Cameroon permission, exact XAF non-production environment, final Collection contract, onboarding/credentials/UAT. Native engine DDL is a separate application blocker.
42. **Orange external:** authoritative verification/refund/market contracts and merchant/UAT evidence missing.
43. **Platform managed:** application adapters for Paystack, Flutterwave, Stripe and PayPal in declared capability scope; no universal country/currency/account or live certification.
44. **Vendor-direct:** no blanket provider certification; only specifically proven Shop-owned account/market/currency models. Configuration alone is not collection permission.
45. **Controlled UAT readiness:** four delivered global-provider application adapters are candidates, contingent on merchant eligibility, native engine readiness, non-production credentials and separate UAT approval.
46. **Electronics disabled:** yes.
47. **Customer Wallet/Cash only:** yes; running catalogue independently confirmed.
48. **Real credentials used:** no; isolated synthetic fixtures.
49. **Real provider calls:** no.
50. **Real refunds/payouts:** no.
51. **Owned financial values changed:** no; all 59 exact row fingerprints match.
52. **Legacy Orders:** 12/12 remain unverified, fingerprints unchanged.
53. **Application work remaining:** native bootstrap/engine compatibility, contract-dependent missing adapters, actual external payout rail/funding/beneficiary integration and settlement ingestion; not another broad architecture audit.
54. **External work:** onboarding, eligible merchant accounts, authoritative missing contracts, non-production credentials, controlled UAT, bank/rail agreement and deployment/operations approval. Engine porting is not external onboarding.
55. **First controlled electronic payment:** resolve the bounded native-engine bootstrap gap, choose a supported merchant/country/currency model, obtain approved non-production configuration and UAT authorization, then verify one original-reference Product/Booking payment through canonical finality/replay.
56. **Production:** finish callback/refund/UNKNOWN/rotation and settlement UAT, least-privilege/security/operations checks, original-evidence reconciliation and separate activation/deployment approval; never activate merely because credentials exist.