# Disposable synthetic MySQL upgrade and recovery

Status: **LOCAL SYNTHETIC UPGRADE AND RECOVERY PASS — not production approval.**

## Authority

The owner separately approved this campaign only: new socket-only disposable
MySQL instances, newly created synthetic data, a synthetic-only logical backup
restored only into the second instance, and independently retained local lab
keys. The reviewed temporary-bootstrap-SUPER/locked-trigger-definer policy is
unchanged. No normal/live data, historical dumps, VPS, external services, real
credentials, providers, SMTP, general workers or real financial operations.

This extends only the local execution boundary of specification §§4–6. It does
not change the historical empty-schema assessment or approve production recovery.
Stop on a destructive/non-additive candidate, ledger/hash mismatch, unexplained
financial/access mutation, key failure or non-equivalent restore. Do not repair
or replay a failed populated migration.

## Exact release and candidate

- Frozen sanitized source: 229 migrations; filename/hash manifest SHA-256
  `37954929be028d7e487a7720f2108d3f300b2862af85f46b724b3c9f63d3f896`.
- Baseline: actual first 228 native migrations, initially empty, then direct
  synthetic fixture insertion. The native migration repository supplies the
  ledger; no invented entries or ordinary seeders.
- The **only** populated upgrade candidate:
  `2026_10_10_010000_add_manual_financial_workflows.php`.
- Review includes delegated `ManualSchema.php` and `FinanceScope.php`: seven new
  manual workflow/evidence tables and twelve Finance permission definitions per
  catalog. Existing definitions are retained with `insertOrIgnore`. No grants,
  balance updates, history transforms or transport.
- Historical source hashes and complete ordered ledger must match before
  execution. Duplicate/orphan, signed BIGINT, principal/reservation and absence
  of pending objects preconditions are explicit.
- `2023_12_07_064250_remigrate_orders_table.php` runs **only on the initially
  empty baseline**. Its complete historical ledger entry prevents populated
  replay. No migration `down()` is used, and no application code is rolled back.

## Laboratory and evidence contract

Runner: `bash scripts/database/mysql-recovery-lab.sh --confirm-approved-synthetic-only`.
It refuses an existing campaign directory. It is not a portable installer or
an unattended release tool. It has no arbitrary server/database URL input.

Two new datadirs use the same synthetic schema name
`agendaally_synthetic_recovery` on different private Unix sockets. MySQL 8.0.42,
InnoDB, UTC, REPEATABLE READ, UTF-8 `utf8mb4_unicode_ci`; binary logging ON/ROW,
trusted creators OFF, TCP/mysqlx/event scheduler OFF. Laravel's actual strict
pre-upgrade session contract is frozen separately from the metadata reader.

Bootstrap privileges are revoked and the retained definer locked after baseline,
after additive upgrade and after import. The definer retains only exact
table-level TRIGGER/SELECT needed by unchanged native bodies; direct login must
fail. The runtime account retains exactly schema SELECT/INSERT/UPDATE/DELETE.
Administrative backup/import cannot broaden runtime privileges.

Evidence is private, Git-ignored, umask 077 under
`.local/mysql-synthetic-recovery/`; keys and independent private files/config
are separately retained under `.local/mysql-synthetic-key-custody/`. Neither raw
database backups nor key files should be published.

Snapshots retain all table rows and raw `SHOW CREATE`, ordered column/index/
FK/referential/CHECK/trigger/definer metadata and ledger. Fingerprint recipe:
PDO lower-case associative keys; non-null scalar fetches as strings;
`json_encode` with `JSON_THROW_ON_ERROR` only; byte-sort complete encoded rows
with `SORT_STRING`; LF join without trailing LF; SHA-256. Same recipe at all
points, repeatable-read read-only transaction, all campaign writers quiescent.

Only index cardinality (sampled optimizer statistics) and trigger creation
timestamps (import execution time) are excluded from **definition** comparisons;
raw values remain retained. Trigger body/timing/table/action order, definer,
SQL mode, charset/collation, CHECK enforcement, FKs and leading supporting
indexes remain exact. Any raw DDL difference is persisted before equivalence
checking. Only redundant `CHARACTER SET utf8mb4` immediately before the same
explicit `COLLATE utf8mb4_*` may be canonicalized, and only with otherwise exact
native metadata. AUTO_INCREMENT is not ignored in restore equivalence.

## Synthetic authority and recovery policy

Fixtures include nonzero integer allocation components, original funding
contexts, original-linked refund/payout reservations, retained receipt/file
evidence, native accounting/transaction rows, native DOUBLE Wallet/history,
explicit and denied Finance roles, country/shop grants, disabled provider
definitions, encrypted merchant/email payloads, generic PENDING/UNKNOWN and
MTN DISPATCH_OUTCOME_UNKNOWN evidence. No financial handler is invoked.

The logical backup uses `mysqldump --single-transaction`, explicit synthetic
schema, all InnoDB, writers quiescent, retained triggers, no GTIDs/tablespaces.
Keys and independent private files/config are not borrowed from development or
staging and are not supplied by the DB dump.

Key recovery removes only the disposable source runtime key copy, then restores
from separate local custody. Wrong-key decryption must fail. Recovered keys must
decrypt original native merchant/email payloads, reproduce independent HMAC
authority and match the native retained private receipt hash.

The raw restored point must match **before** session invalidation. Only after
that gate, invalidate restored sessions, bearer tokens, remember tokens and
reset/verification challenge bindings. Native session/Sanctum/challenge checks
must deny stale authority, including a resurrected verification-cache entry.
Passwords, identities, roles/grants, encrypted evidence, reservations and all
financial/provider/email lifecycle records remain unchanged.

Reconciliation is a passive **HOLD ALL** inventory. No callback, attempt retry,
refund, payout, test funding, email resend, notification recovery method or
worker dispatch is invoked. Independent external outcome evidence and owner
acceptance would be needed before any real recovery traffic switch.

## Verification and exclusions

Offline retained-proof check:
`php scripts/database/check-mysql-recovery-proof.php`.
It requires real child/supervisor exit receipts, exact full ledger/hash and
schema/row/invariant evidence, key/stale-authority probe results, source
preservation and absence of lab sockets. It never starts an application or DB.

Full native HTTP finance-session security, callback concurrency and the normal
database preservation warning remain separate existing work. No normal database
connection is used; this campaign does not re-certify historical normal-data or
Git-metadata preservation from unavailable private evidence.

This is same-host, local-only custody, **not off-host disaster recovery**.
The separate [independent custody/recovery contract](mysql-independent-custody-recovery-contract.md)
is a design/owner-approval gate, not an extension of this laboratory's authority
or a claim that independently retained backups/keys already exist.
Production remains NO-GO. No reference/demo first-install manifest, independent
real administrator, production sizing, off-host retention, provider activation
or production RPO/RTO is approved or certified here.

## Original run: populated upgrade pass and required restore stop

The new source baseline completed 228/228 migrations. Its populated preconditions
passed, including all 283 native FK relationships, frozen migration hashes,
complete ledger and explicit integer/principal/original-linked reservation
checks. The sole approved upgrade completed the native ledger at 229/229.

| Point | Tables | FK constraints | Enforced CHECKs | Triggers | Ledger |
|---|---:|---:|---:|---:|---:|
| Synthetic populated baseline | 206 | 283 | 21 | 17 | 228 |
| After exact additive upgrade | 213 | 307 | 21 | 29 | 229 |

All old financial rows, Wallet/history, original reservations, receipts,
accounting evidence, encrypted payloads and permission grants matched exactly.
Seven new manual tables are empty. Existing Finance view definitions and grants
were retained; **eleven missing definitions per catalog** were added, making
twelve total per catalog. The private comparison receipt's phrase “twelve
definitions” describes the final catalog, not twelve newly inserted rows.
Historical metadata and source-emitted trigger bodies were checked. Locked
definer login was rejected and exact least-privilege grants verified. Native
read-only probes preserved explicit Finance view, denied ungranted Admin and
approval authority, and preserved Vendor/original-payer reads. They demonstrated
valid pre-invalidation synthetic session, token and key-bound challenges, without
persisted mutations. This is not full native HTTP finance-session certification.

The consistent synthetic backup and selected-point receipt completed. Import into
the second initially empty disposable instance **failed with MySQL 1044 at dump
line 29**:

```
LOCK TABLES `ads_package_translations` WRITE;
```

`mysqldump --skip-lock-tables` disables read-side dump locks; it does **not**
disable emitted restore-side `LOCK TABLES`. The reviewed bootstrap account has
no LOCK TABLES privilege. No privilege was added, no partial restore was repaired
or retried, and no failed restored state was declared equivalent.

The supervisor recorded actual exit **1** and cleanly shut down both instances,
revoking/locking bootstrap authority. The source upgrade and failed restore
datadirs, original dump and import error are retained. The original run did not
retain a standalone import-child exit receipt before its shell stop; the
supervisor exit and raw import error remain the actual observed evidence. The
helper now records that child exit on failure as well. Repeated source definer
preparation originally reused one diagnostic log/exit filename; separate native
pre/post snapshots retain both privilege contracts. Future helper executions
retain numbered diagnostic receipts, rather than recreating historical logs.

At this stop, the corrected `--skip-add-locks` method remained unexecuted.
The owner subsequently approved only the bounded retry described below. The
failed second instance remains evidence, never a replay target. Do not rerun the
full campaign against its existing state or repeat the populated migration.

Non-secret retained integrity receipts (SHA-256):

| Receipt | SHA-256 |
|---|---|
| Source before snapshot | `7c126fd397c08cf21672f5aa0458d2c584df8571eb5c8739bfdd9e582f17b20a` |
| Source after snapshot | `c9c860f9515d3e3ea89b34e12840a25a136e25412028f1a3948a1f85c607323e` |
| Populated qualification | `c9543b3ac3f130d36482873bc259ece1644e0ddef015e377f3e97975228d7ba2` |
| Upgrade comparison | `b08dbc83bb4dc37705bb818c1d4c16852083f4b11508dffec9c9273d6f85e81e` |
| Backup receipt | `30a77df4bfaf2f171771050efeafcaeba788157f46f37d3ea35051fe43d01598` |
| Failed supervisor exit | `c9c9c4f8ce6f240b839e6e05570585022ed3173652276203b6cea7e775db767d` |
| Original import error | `2ac6021045f3e819656a399f434589930e4173574e7b869543a0401a52813621` |

## Separately approved third-instance recovery: completed

Retry runner:
`bash scripts/database/mysql-recovery-retry.sh --confirm-approved-third-instance-retry`.
This one-shot runner refuses existing retry/third-instance directories. It has
no bootstrap or populated-upgrade execution step.

The completed synthetic source was restarted **only for backup reads** with
MySQL `read_only=ON` and `super_read_only=ON`. Its schema, all 213 table row
fingerprints, complete 229-entry ledger, financial/access authority and exact
guard/definer grants matched the selected post-upgrade point. All tables were
verified InnoDB. No application, scheduler, worker or writer ran against it.

The corrected synthetic-only backup retained `--single-transaction` and
`--skip-lock-tables`, adding **`--skip-add-locks`** to omit restore-side lock
commands. Backup consistency therefore did not rely on an unapproved privilege:
all tables were transactional and both ordinary and administrative source writes
were blocked throughout. Before/after selected-point snapshots matched exactly.
No GTIDs/tablespaces or normal data were included; the complete native trigger
set was retained. The source was shut down before third-instance recovery.

The new `.local/mysql-synthetic-recovery/restore-retry/` instance imported using
the **same** reviewed bootstrap privileges, with no LOCK TABLES grant or
server-trust relaxation. Import and every recovery child exited **0**. Elevated
privileges were revoked, the definer locked, and exact table-level SELECT/TRIGGER
authority verified; the application retained only schema DML. Its final
supervisor exited **0**, with no remaining source/restore/third-instance socket.

### Equivalence, keys and access

- All **213** restored table row hashes/counts and complete ordered ledger
  matched the selected source point using the declared serialization.
- Columns/types/precision/defaults, indexes, **307 FKs**, **21 enforced CHECKs**,
  **29 trigger bodies/timing/action order/definers/SQL modes**, and grant/account
  contracts were equivalent. Leading FK-supporting indexes were verified.
- Raw `SHOW CREATE TABLE` differences on **168 tables** are retained in
  `restore-retry/raw-ddl-differences.json`. Every difference was only the declared
  redundant per-column `CHARACTER SET utf8mb4` before the same explicit COLLATE;
  independently exact native metadata and narrowly canonicalized DDL matched.
  No AUTO_INCREMENT, precision, guard, definer or arbitrary mismatch was ignored.
- Principal/components, original-linked reservations, receipts, immutable
  accounting JSON, Wallet/history, transactions and all permission grants
  matched before upgrade, after upgrade, before restore invalidation and after
  restore invalidation. No old rows or grants were silently rewritten.
- The disposable source runtime key copy was removed **after source shutdown**.
  Separate local custody—not the DB dump or normal/staging credentials—recovered
  application and independent offline-authority keys, private receipt and frozen
  runtime config. Wrong-key decryption failed. Original native merchant/email
  plaintexts, HMAC authority and native retained receipt hash matched exactly.
- Before invalidation, native checks demonstrated valid restored synthetic
  sessions, bearer tokens and key-bound challenges. The quarantined restore then
  cleared only sessions, bearer tokens, remember tokens and reset/verify bindings.
  Native DatabaseSessionHandler, Sanctum, password-reset and email-verification
  checks denied stale authority, including resurrected verification-cache data.
  Password hashes, users' other fields, keys, grants and financial evidence
  remained identical. This is not a full native HTTP sign-in/session assessment.

### No-replay reconciliation and measured local recovery

The passive inventory retained two generic PENDING/UNKNOWN attempts, the MTN
DISPATCH_OUTCOME_UNKNOWN record, held original-linked financial operations and
two PENDING/UNKNOWN email records. Their exact rows/states/amounts were unchanged.
The decision remains **HOLD ALL**: no automatic callbacks, retries, financial
commands, notification recovery method, email delivery or workers.

Measured local schema import/equivalence: **45.97 seconds** from isolated restore
start. Measured isolated recovery readiness, including key/file recovery and
stale-authority invalidation: **54.35 seconds**. Synthetic selected-point RPO:
**zero writes lost**, because the approved source was frozen throughout. These
are laboratory measurements, not a production SLA or proof of external outcomes
newer than the backup. Production reconciliation and traffic switching remain
unapproved.

The failed second instance's complete file byte hashes, lengths, permissions and
ownership matched before/after the bounded retry. Its dump/error/exit evidence
was not rewritten. Original and sanitized source trees remain unchanged.

Offline proof check and PHP/Bash syntax checks passed. Negative checks rejected
enabled PHP transport functions and reuse of either campaign directory before
database access. No existing financial-concurrency, native HTTP finance-session,
normal database-warning or first-install/admin gate was reopened.

### Final retained integrity receipts

Private evidence includes actual numbered retry child exits, import/supervisor
exits, quiescent source snapshots, corrected backup receipt, raw/native restored
metadata, wrong-key/recovered-key checks, stale-authority checks, reconciliation
inventory, failed-target custody and source preservation.

| Receipt | SHA-256 |
|---|---|
| Final assessment | `96ecb431f03eb1350238d3bfc67c1de2dbcedba62d9a9f883b60f2c5bbd7227f` |
| Corrected backup receipt | `aa8c0a1f6259900a097ac104081019100746cb0efe688c0e870563ea0e7459f6` |
| Successful retry supervisor | `ddf870ff54d62fb1c586ee927c27883898628df2034a0e297abdaa1f3f22910e` |
| Raw restored snapshot | `25c2201db2d7a539d15e93d4d7992e5c1df7f47e1ac97735db81e80af75dc34b` |
| Final invalidated snapshot | `4ce3b8b9eb21f37b0fb24d0fe7a6d757e9fbb62867db8a063ddd90b2971382ac` |
| Restore comparison | `3cb6c400c5109d407eff92e5a4d478f0a9742cea2bcbe58f08cb5466df413748` |
| Independent key recovery | `64ac52287a764710e66d79bc54571e418232ee6d4aa2e70f6047a390451e4dd5` |
| Stale-authority invalidation | `e0c48826c0e618349d43340b12a9164bc4b23b3146fe7ac63b56fd4527e60249` |
| Failed-target custody, both points | `5df2392eacaee981293cee8898c603a83af11fb0c8e7db64c8d21415e074c5cb` |

The owner authorized the local procedure, not production recovery acceptance.
Independent off-host retention, real administrator recovery, external-outcome
reconciliation and any production/provider/SMTP/worker activation remain gated.

## Separate temporal qualification

The frozen-source zero-loss result above is unchanged. A separate fresh-data
campaign qualifies deliberately newer synthetic observations and nonzero lost
writes without restarting these instances or replaying this upgrade. See
[synthetic outcomes newer than a backup](mysql-synthetic-temporal-recovery.md)
for its distinct authority, private evidence paths, physical quarantine,
passive independent-evidence reconciliation and exclusions.
