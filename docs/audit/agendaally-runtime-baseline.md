# Original AgendaAlly isolated runtime baseline

## Approval and boundaries

The creator explicitly approved disposable runtime/dependency setup and
synthetic booking, product-order and original-client compatibility checks.
This supersedes the earlier read-only gate for this baseline only. No rebuild,
port, Express replacement, Inertia adoption, database replacement, deployment,
production access, live charges/refunds/payouts/messages or destructive
migration replay is authorized.

The existing API Server and Canvas are Replit scaffolds, not AgendaAlly.
Their previews are not evidence of original application compatibility.
Original app behavior, branding and mobile v1 contracts are unchanged.

## Reproduction

Use the existing PHP 8.4 runtime and Composer. Restore the preserved lock,
not a newly resolved dependency set, in a disposable directory:

```sh
mkdir -p /tmp/agendaally-hardening-runtime
cp .migration-backup/backend/composer.{json,lock} /tmp/agendaally-hardening-runtime/
(cd /tmp/agendaally-hardening-runtime &&
  composer install --no-scripts --no-plugins --no-interaction --prefer-dist)
node scripts/inspect-original-baseline-state.mjs
bash scripts/probe-original-boot.sh
HARDENING_VENDOR_AUTOLOAD=/tmp/agendaally-hardening-runtime/vendor/autoload.php \
  bash scripts/verify-original-hardening.sh
HARDENING_VENDOR_AUTOLOAD=/tmp/agendaally-hardening-runtime/vendor/autoload.php \
  bash scripts/verify-original-baseline.sh
bash scripts/verify-original-clients.sh
```

Composer hooks must remain disabled: the preserved manifest contains forced
translation/unit seeds, and project creation invokes `migrate:fresh --seed`.
Do not run those commands. No automatic install/import hook was introduced.
The root post-merge script targets the unrelated scaffold; it is not an
original-application restoration procedure.

## Actual full-app boot result

`probe-original-boot.sh` copies allowlisted original code to an explicitly owned
`mktemp` directory, creates empty local storage/cache directories, and removes
that owned directory on exit. It does not copy `.env`, original storage, uploads,
database dumps or cached configuration. The process environment is cleared.
All database connections are replaced with process-owned SQLite `:memory:`
before original service providers register. Session/cache/mail use in-memory
drivers; queues and broadcasting use null drivers. Laravel HTTP stray requests,
independent socket/cURL transports and remote URL file reads are blocked.
No configured secret values were inspected or printed.

The original HTTP kernel booted under Laravel **12.46.0 / PHP 8.4.16** and
registered **1,525 routes**: 60 containing `booking`, 40 `cart`, and 114 `order`
(substring counts, not counts of tested endpoints). The database contained
**zero tables**. No requests, migrations, seeders or schedules executed.

This confirms provider/route bootstrap in the controlled environment, not a
fully provisioned database, working web page, authenticated HTTP flow,
middleware coverage, live provider readiness or production deployment.

## Backup and migration-state checks

`inspect-original-baseline-state.mjs` performs source-only SHA-256 inventory
of Composer files and all 213 migration files. It reports no preserved
bootstrap config/routes/services/packages caches. The text scan identifies
194 candidates for manual review; it includes `down()` methods and does not
claim all candidates are destructive on upgrade.

In particular,
`2023_12_07_064250_remigrate_orders_table.php::up()` drops orders, order
details/refunds, coupons, partner payments, tickets and other tables, then
recreates them. Its empty `down()` cannot restore deleted data. Never replay it
as an ordinary upgrade or test bootstrap.

No original database was connected. Therefore its migration ledger,
pending migrations, schema drift, backup freshness and restoreability are
**unknown**, not verified safe. No production backup was requested or accessed.
The source copy is not a database backup. Disposable synthetic databases
contain no customer history and require no retained data backup.

Before any future approved real-schema work, the owner must establish:

1. An independently named/owned isolated database, with credentials that cannot
   access production and no inherited URL overriding the intended connection.
2. A restorable, encrypted owner-managed database backup; test restore in a
   separate disposable target before any schema operation.
3. Read-only schema and migration-ledger inventory against that restored target,
   compared with the source hash manifest. A pending destructive remigration
   requires a reviewed preservation plan, not blind `migrate`.
4. An allowlisted minimal forward setup or reviewed schema snapshot rather than
   replaying all historical migrations, `migrate:fresh`, or bulk unknown seeds.

The original full PHPUnit suite uses `RefreshDatabase`. Its substring-based
database-name guard does not independently prove database ownership and does
not make destructive migration replay acceptable. Those tests remain unrun
through their original bootstrap; any safely adapted focused cases must be
identified as adaptations, not full-suite passes.

## Compatibility evidence

- Backend focused results, synthetic schema limitations and generated contract
  fixture: [domain baseline](agendaally-domain-baseline.md). **3 tests / 49
  assertions passed**, including successful scheduled booking creation,
  booking history visibility, product cart updates and checkout, order history,
  initial refund requests, refund history and duplicate request rejection.
  Actual original resource envelopes are frozen in
  `.migration-backup/backend/tests/Baseline/fixtures/original-domain.json`.
- Original web/admin and Flutter executable outcomes versus static evidence:
  [client baseline](agendaally-client-baseline.md). Frozen web/admin Yarn
  restores succeeded with scripts disabled and original locks unchanged.
  The admin compiler transformed 1,235 modules but stopped on an image
  intentionally omitted from the isolated copy; that is an isolation
  limitation, not a demonstrated original-source defect. The network-blocked
  Next production compilation did not finish within its 300-second limit.
  Neither full client build is reported as passing.
- Existing security suite: **55 tests / 382 assertions passed**, with the known
  PHP 8.4 implicitly-nullable-parameter deprecation in `WalletHistoryService`.
- Preserved external API contracts: [API appendix](agendaally-api-contracts.md).

Flutter was installed through the environment's package manager for approved
client verification. The available stable runtime is Flutter 3.32.0 / Dart
3.8.0, while the original full client's manifest/lock requires Flutter
3.38.5 / Dart 3.10.0 or newer. The original requirements must not be lowered
to manufacture a successful full-app restore. Any separately isolated DTO
parser harness is narrower evidence, not a full mobile build.
The reduced comprehensive parser harness also fails dependency resolution:
the original locked `shared_preferences` needs a newer Dart runtime.
No original constraints or package versions were weakened. A separate
dependency-free run of the unchanged original cart-quote Dart parser passed
**7 checks** against synthetic JSON. The full booking/cart-detail/order/refund
Flutter parser suite is prepared and linked to actual backend envelopes, but
remains **unexecuted**, not passed.

## Next approval gate

This is a bounded compatibility baseline, not release certification. Obtain
separate approval before fixing observed business behavior, upgrading client
dependencies, provisioning a production-engine schema snapshot, importing
anonymized historical data, using provider sandboxes, or beginning modernization.
Any approved modernization must preserve both services and product ordering,
branding, and existing Flutter REST v1 envelopes/parsing.