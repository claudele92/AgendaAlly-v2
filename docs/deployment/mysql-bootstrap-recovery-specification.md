# Empty MySQL bootstrap and recovery specification

Status: **LOCAL LAB QUALIFICATION ONLY — NOT A PRODUCTION INSTALLER OR RELEASE APPROVAL.**
Date: 2026-10-07.

## Scope and authority

This assessment uses the sanitized snapshot's
`docs/deployment/vps-deployment.md` and `docs/publication/campaign-report.md`.
The snapshot lives in `.local/agendaally-clean-repository`; neither it nor its
publication copy is edited. The original four applications, repository history,
normal databases and accepted financial behavior are protected.

Only locally owned, synthetic, initially empty MySQL schemas are authorized.
No VPS, production connection, DNS/TLS, remote, push, provider, SMTP, application
worker, financial service execution, ordinary demo seeding, normal dump copy,
database conversion or destructive existing-schema migration is performed.
No existing accepted financial journey is reopened by this assessment.

The laboratory runner is `scripts/database/mysql-bootstrap-rehearsal.php`.
It is deliberately tied to this owned lab and an unchanged, independently
restored sanitized Laravel dependency/source tree. It is **not portable
production automation**. There is no server/database URL argument. It verifies
the exact datadir, disabled TCP networking, ownership, empty schema, frozen
migration count/hash and disabled process/network transport functions before
the native Laravel migrator can run.

## Evidence and qualification matrix

Persistent private evidence: `.local/mysql-bootstrap-rehearsal/`, Git-ignored,
directory mode 0700; created evidence uses umask 077. Do not publish its datadir,
MySQL-generated keys, raw logs, grants or preservation inventories.
Persistence here is not independent off-host disaster-recovery custody.

| Gate | Required proof | Assessment |
|---|---|---|
| Source inventory | Actual sanitized migrations and delegated DDL services, byte identity | 229 migrations; frozen manifest verified; validation source comparison has zero differences |
| Normal data/history preservation | Before/after source/history and all normal row/schema fingerprints | 215 normal tables, original application bytes/HEAD and sanitized source unchanged; concurrent Git metadata drift disclosed below |
| Empty native MySQL DDL | Exact migration chain and ledger, native CHECK/FK/index/trigger metadata | DDL-ONLY PASS in the separately approved new lab: 229/229; historical 217-migration failure remains disclosed in §7 |
| Reference initialization | Minimal explicit allowlist, order, idempotency, owner-approved defaults | BLOCKED: not inferred from the demo seeder |
| Independent admin credentials | Owner-authorized identity, privilege scope and secure secret provisioning | NOT EXECUTED; no imported or demo administrator |
| Independent key custody | Application and selected authority material recoverable independently | Local synthetic PASS in §9; no borrowed normal/staging keys; off-host custody not certified |
| Populated non-destructive upgrade | Reviewed ledger/hash, compatible additive change, preserved invariants | Local synthetic 228→229 additive PASS in §9; not inferred from empty replay |
| Restore | Independently retained approved DB/files/keys and isolated reconciliation | Local synthetic third-instance PASS in §9 after separately approved retry; no normal/historical dump copying |
| Production deployment | Explicit separate owner approval and all remaining release gates | NO-GO |

This matrix distinguishes a source/schema result from a complete initializer.
An empty financial table is not a concurrency, principal-conservation,
authorization or provider certification.

## 1. Frozen native contract

The sanitized development manifest contains 229 migrations with SHA-256
`37954929be028d7e487a7720f2108d3f300b2862af85f46b724b3c9f63d3f896`.
Recipe: filename-sorted entries `basename + SPACE + SHA256(file)`, LF-joined
without a trailing LF, then SHA-256. This is a source identity receipt, not
production authorization from the SQLite development guard.

`migration-inventory.json` retains every filename, position, hash and static
create/alter/drop/data-touch classification. Static screening is not a complete
PHP dependency analyzer; delegated provisioners/backfillers and financial DDL
classes require source review and the native engine remains execution authority.
`validation-source-comparison.json` compares app/config/bootstrap (excluding
generated cache), migrations, seeders, routes and Composer locks against the
sanitized snapshot. No inherited application configuration is used.

Lab engine: MySQL **8.0.42**, InnoDB, UTF-8 `utf8mb4_unicode_ci`, UTC `+00:00`,
REPEATABLE READ. The connection uses Laravel strict mode and retains its actual
session `sql_mode`. Source's ordinary MySQL configuration declares `strict=false`;
the lab's stricter session is an explicit rehearsal choice, not an unnoticed
production configuration change. Freeze the actual target's supported version,
SQL mode, collation, isolation and timezone separately before release.

A separate newly initialized datadir is reached only by its private Unix socket.
TCP and mysqlx are disabled. Root provisions two schema-scoped lab identities:
migrator SELECT/INSERT/UPDATE/DELETE/CREATE/ALTER/DROP/INDEX/REFERENCES/TRIGGER,
and application SELECT/INSERT/UPDATE/DELETE. No FILE, SUPER, GRANT OPTION,
global privileges or cross-schema privileges are granted to these identities.
The lab's passwordless local accounts rely on its mode-0700 socket parent and
are **not a production credential design**. Root is never the Laravel identity.
The MySQL event scheduler is off in the final rehearsal; no events are created.

Resource bounds are laboratory-only: 64 MiB buffer pool, 32 MiB redo capacity.
The longer attempt requested 1/8 MiB full-text caches; MySQL adjusted them to
1,600,000/32,000,000 bytes, retained in server metadata/logs. They are not VPS
sizing or performance claims.

## 2. Migration order and existing-database hazards

Native chronological order starts with translations/users, then geography,
languages/currencies/shops, permission tables, catalog, commerce, booking and
staff modules. Later migrations introduce country/shop-scoped authorization,
location attribution, finance authority, intent identities and selected email.
Use Laravel's real migration repository; do not invoke every `up()` and invent
ledger entries afterward.

**Never infer that an empty replay is safe on an existing schema.**
`2023_12_07_064250_remigrate_orders_table.php` drops and recreates order/coupon/
refund/ticket/partner-payment tables in `up()`. A missing historical ledger entry
must fail closed, not cause that migration to be replayed. Other `up()` methods
drop or replace columns/FKs; reverse methods may be destructive, incomplete or
explicitly prohibited with retained records.

Data/order-sensitive families:

- `2026_09_05_000000_reassign_shop_manager_role`: creates a role if absent,
  separates shop staff from platform manager grants, records targeted changes.
- Country-currency/payment backfill: requires approved currency/country/payment
  catalogs; empty replay has no countries to attribute. Its electronic-method
  cross-product must not activate providers as an installation side effect.
- Shop-manager-holder migration: depends on shop permission definitions and
  preserves targeted backup authority.
- Default country roles: depends on the country permission catalog and intended
  global versus country scope; empty or incomplete catalogs are not approval.
- Invitation-location migration and seller-currency backfill: preserve real
  association/currency authority; never manufacture a country/location.
- Hero translations/content HTML repairs: data normalization, not arbitrary
  replacement of owner-approved legal content.
- MTN identity: inspects existing attempt identity before adding native guards.
- Account/subscription template migrations delegate to source provisioners;
  definitions alone are not SMTP configuration or a send.
- Manual-finance migration creates permission definitions, **not automatic
  finance grants to existing roles**.

DDL is not transactionally reversible as a whole in MySQL. A timeout can leave
committed tables/indexes without the migration ledger entry. Retain SHOW CREATE,
columns/indexes/FKs/triggers and exact ledger before deciding what happened.
Do not resume merely because a `PASS` line or a table exists.

## 3. Proposed minimal reference manifest — not executed

Every approved reference step needs a source hash, expected natural-key/ID
mapping, dependency position, expected rows and a second-run equality check.
Do not run `DatabaseSeeder`, `UserSeeder`, `OrderSeeder`, development or ordinary
demo seeds. They create accounts, shops, orders, subscriptions, settings and
provider catalog/SMTP definitions beyond initialization authority.

| Reference family | Source candidate and constraints | Proposed position |
|---|---|---|
| Role definitions | `RoleSeeder`; exact fixed IDs/names/web guard, no user grants | After permission-table creation and before manager-role/data migrations |
| Language | `LanguageSeeder` defines English default; approve actual launch locales | Before localized reference/content rows |
| Currency | `CurrencySeeder` currently defines XAF default/rate 1, USD ID 2 and historical relative rates | Owner reviews launch currencies/rates; do not treat demo rates as live quotations |
| Shop permission catalog | `ShopPermissionSeeder`; definitions only, never broaden membership | After shop permission table, before staff-role data migration |
| Country permission catalog | `CountryPermissionSeeder`; definitions only | After country permission table, before default country-role backfill |
| Country/region/city | Explicit owner-approved geography, currency association and active scope | Before approved country-default backfill, never Africa/demo businesses |
| Cash/Wallet definitions | Source-native tags and accepted methods only; empty Wallet means unfunded | Approved explicit method manifest; no synthetic opening balance in production |
| Electronic providers | No credentials/revisions/active configurations or enabled country gateways | Excluded pending independent activation |
| Settings/legal/content | Reviewed native required keys and actual policy/contact authority | No blind settings/subscription/legal/demo seed |
| Templates | Migration-supplied defaults inspected, duplicate natural keys rejected | Definition-only; no email/scheduler dispatch |
| Initial owner/admin | Independent authorized account and least necessary roles/scopes | Only after reference invariants; secure secret provisioning outside source |

**Role identity ordering is a material review item.** `RoleSeeder` uses user ID
1 and shop_manager ID 14. The manager-separation migration uses
`Role::findOrCreate('shop_manager','web')`, which can allocate ID 1 if roles are
empty. Blindly running fixed-ID `RoleSeeder` after a schema-only replay can
overwrite a different name at that ID or collide on the role uniqueness
constraint. Some seeders catch exceptions and continue, so command exit zero is
not row-level proof. Do not repair identities after grants exist. A reviewed
pre-migration definition checkpoint must be separately demonstrated on an
independent empty lab before approving the full reference initializer.

Required absence assertions: no demo users/shops/orders/bookings, no Wallet
opening balance, no payment attempts/operations/receipts, no SMTP/provider
credentials, no queued delivery, no automatic Finance grant. Check exact catalog
rows/foreign keys and one intended default language/currency where approved,
not just counts. No bootstrap seeding can declare a real Wallet funded.

## 4. Financial invariant inventory

Retain the accepted economic and financial architecture. Do not alter native
precision, remove constraints or replace engine guards with application checks
to make bootstrap pass.

| Authority | Source/native requirements to inspect |
|---|---|
| Legacy transactions/Wallet/bookings | Preserve actual DOUBLE/FLOAT/DECIMAL declarations and native scale; do not silently convert historical money |
| Commerce allocations | `2026_10_03_100000`; integer principal/components, identity uniqueness, allowed origin/purpose/state, native checks and FKs |
| Collection contexts | `2026_10_03_100100`; integer authority, anchor/context binding, receipt/funding identity, non-self anchoring and lifecycle protection |
| Accounting evidence | `PaymentAccounting/AccountingSchema.php`, `2026_10_03_100200`; transaction/type/effect uniqueness, exact amount and immutable persisted evidence |
| MTN attempts | `PaymentAccounting/MtnMySqlSchema.php`, `2026_10_03_100300`; durable attempt identity, immutable binding, retained evidence and UNKNOWN recovery |
| Completion identities | `PaymentAccounting/CompletionSchema.php`, `2026_10_03_100400`; global merchant revision uniqueness, immutable encrypted revisions, electronic event identity, original-linked operation reservations, retained receipt evidence |
| Manual finance | `ManualFinance/ManualSchema.php`, `2026_10_10`; command/workflow/receipt authority, permission definitions without grants, original-linked amounts and terminal evidence |

Verify BIGINT signedness, precision/nullability/defaults, CHECK enforcement,
uniques, leading FK-supporting indexes and all MySQL trigger bodies. AUTO_INCREMENT
columns cannot be used in MySQL CHECK expressions; source's reviewed native
equivalent must remain intact. Do not assume an SQLite pass proves native DDL.
Dropping a composite/unique index can remove the only InnoDB FK-supporting index;
do not automate migration `down()` as a release rollback.

Principal conservation, original-linked reservations, cancellation-policy
budgets, processing-once, durable UNKNOWN, country/shop authorization and immutable
JSON evidence remain accepted obligations. MySQL JSON formatting is not a raw
byte-equivalence promise. Concurrency requires its separately retained native
proof; this bootstrap task executes no funding/payment/refund/payout operation.

## 5. Independent admin and key custody

Production provisioning remains an owner decision, not a reused lab account.
Approve the initial administrator, roles/country scope and recovery access
independently. Create no default/demo password; never disclose a generated
credential in stdout, reports, URLs or Git. Use the authorized secure secrets
flow, verify the persisted native password hash/auth contract, then require
owner acceptance. Do not implicitly grant country Finance operations.

Generate a production application key once through approved custody and retain
it through updates/restores. Inventory selected use cases: Laravel encryption
of merchant revisions and selected email payloads; account challenge HMACs;
private financial attachments/receipts; any selected approval/recovery authority
outside ordinary application configuration. Retain original key/version
association; rotation is a separately reviewed operation, not a bootstrap step.
Imported provider settings and local managed-session-derived staging authority
are not independent production custody.

An owner-approved recovery design needs independently retained encrypted DB/
files/config, encryption/authority key escrow and access recovery, restricted
off-host retention, retention/rotation policy and RPO/RTO. Losing the only local
key authority cannot be solved by restoring source code. No off-host destination
or real credential was requested, contacted, copied or created here.

The owner-reviewable [independent custody/recovery contract](mysql-independent-custody-recovery-contract.md)
defines proposed off-host backup/key separation, retention, restore/access
authority and RPO/RTO. Its approval record is authoritative for design status;
neither this specification nor local rehearsal authorizes external execution.

## 6. Proposed non-destructive upgrade/recovery runbook

### Existing installation upgrade

1. Explicit approval; deployment lock; quiesce selected writes/jobs without
   replaying uncertain outcomes. Record exact release/source and approved runtime
   contract. No broad scheduler/worker activation.
2. Capture a consistent approved backup with independent key/files custody and
   a matching schema/row/invariant/ledger receipt. No normal dump copying in this
   campaign.
3. Compare the full historical ledger and migration hashes. Missing or altered
   historic destructive migration => STOP. Approve an exact pending migration
   allowlist; never default to every pending historical file.
4. Inspect populated preconditions, duplicate identities, orphan FKs, negative/
   fractional authority, data transformations and required reference scope.
   Native empty-schema success cannot discharge these checks.
5. Grant narrowly scoped migration privileges temporarily; apply only reviewed
   changes. On error or timeout retain partial state, logs and metadata.
6. Verify old rows/authority unchanged except explicitly approved transformations;
   exact new objects/triggers/ledger; no money/email/provider side effects.
   Remove migration privileges and accept read-only health/auth checks.
7. Roll back compatible **code**, never automatically run financial `down()`,
   regenerate keys or overwrite normal data. Incompatible schema requires an
   explicitly approved recovery decision.

### Isolated restore before any production recovery

1. Obtain approval for a separate owned restore location and the backup method;
   never point recovery at an existing normal schema. This task authorizes no
   dump copying, so actual data restore remains unexecuted.
2. Restore the selected consistent DB/files/config/key set to that location.
   Pin engine/session contract and source/ledger versions. Keep all outbound
   transports, general workers, financial handlers and event scheduler inactive.
3. Reproduce snapshot hashes with the **same declared ordering/serialization**.
   Retain raw SHOW CREATE differences. If import produces redundant charset
   syntax, prove exact column/index/FK/check/trigger equivalence with narrowly
   documented canonicalization; never ignore arbitrary schema mismatches.
4. Reconcile integer principal/component totals, original-linked reservations,
   immutable ledger/receipts, permissions, private storage and key decryption.
   Preserve PENDING/UNKNOWN outcomes; no automatic callback/email resend,
   refund, payout or “test” funding.
5. Confirm prior sessions/challenges/approvals have the intended restored
   authority lifetime; determine duplicate-effect exposure relative to the
   recovery point. Owner approves reconciliation before any traffic switch.
6. Measure actual RPO/RTO; retain independent evidence and owner acceptance.
   A same-host local file copy or source checkout is not disaster recovery.

## 7. Observed native rehearsal and remaining release decision

The first run was interrupted by a 300-second outer command timeout after
178 completed native migrations. The auction migration had committed partial
tables/full-text indexes but no ledger entry. Its datadir, receipt, logs and
interruption inspection are retained without repair/replay. This is a laboratory
interruption, not proof of an application SQL defect.

A second independently empty schema on the same owned socket-only server is
used for a longer background run with bounded full-text cache settings. No
historical rows or dumps are copied to it. Neither schema is reset with
`migrate:fresh`, and the runner refuses either once nonempty.

The final result, failure classification and preservation receipt are recorded
in the assessment addendum below. Regardless of DDL outcome, reference-order,
independent admin/key custody and populated upgrade/restore gates are not silently
promoted to PASS. Production remains **NO-GO**.

### Final assessment addendum

**Outcome: BLOCKED. A safe fresh-server initializer has not been certified.**

The second schema's actual ledger contains **217 of 229** source migrations.
The first incomplete migration is
`2026_10_03_100100_create_payment_collection_contexts.php`.
Its table was created, but installation of
`pcc_receipt_anchor_insert` failed with MySQL **1419**:
the migration identity does not have SUPER while binary logging is enabled.
Actual server contract: `@@log_bin=1`,
`@@log_bin_trust_function_creators=0`, event scheduler OFF. A scoped TRIGGER
grant is insufficient under that contract.

Native evidence retains **199 tables, 267 foreign-key constraints, 19 CHECK
constraints and zero triggers** in the partial second schema. These counts are
diagnostics, not success thresholds. Both receipt-anchor triggers are absent;
the later accounting/MTN/completion/manual-finance migrations remain unapplied.
Financial invariants must not be marked certified on this incomplete schema.

**Required stop:** obtain an explicitly reviewed DBA/bootstrap authority policy
that preserves binary logging/recovery, least-privilege runtime credentials and
the native financial guard/definer contracts. No SUPER grant, trusted-creator
switch, binary-log disabling, trigger omission, manual ledger insertion or
application/schema correction was performed. Do not replay this partial schema.
The next authorized execution starts with another owned empty schema and retains
this failure as evidence. This is an operational-authority blocker; no new
business-rule defect is asserted.

The only automatically created role is `shop_manager`, at ID 1. All users, shops,
orders, bookings, Wallets, countries, currencies, languages, payment definitions,
attempts, allocations and contexts have zero rows. No administrator, independent
key or reference seeder was provisioned. The observed role identity supports the
need for a reviewed reference-order checkpoint; no grant or normal identity was
rewritten to conceal it.

The long-running lab runner also had a **secondary evidence-collection bug**:
an unaliased information-schema column was addressed with the wrong case.
After the primary native STOP, Laravel reported that collection error and the
outer process recorded exit zero. Raw progress/logs are retained, not rewritten
as a pass. The helper was corrected, and a separate read-only native collector
recovered exact DDL/rows/columns/indexes/FKs/CHECKs/triggers/ledger and binary-log
settings without altering either schema. The primary native failure is now
persisted before any later collection. Assessment follows the STOP and ledger,
**never the process exit alone**.

Preservation receipts compare the original normal SQLite schema and **all 215
table row fingerprints** with a single declared serialization method: identical.
Original application tracked bytes, original HEAD and both sanitized snapshot
source trees are unchanged. Existing memory index/audit notes were intentionally
updated with non-secret durable findings; the specification/helpers are new
files, not application modifications.

Raw original Git metadata is **not byte-identical**: `FETCH_HEAD`, `config`,
`logs/refs/heads/main-repl/main`, and `refs/heads/main-repl/main` changed during
the run. No Git write, fetch, remote or push command was issued by this rehearsal.
The mismatch is retained and not attributed or manually restored. The sanitized
copies currently lack independent `.git` metadata; source equality is verified,
but the earlier campaign's independent-history claims are not re-certified by
falling back to the parent Git root.

Final artifacts in the private evidence directory:

- `migration-inventory.json`, `validation-source-comparison.json`;
- first interruption log/receipt and `interruption-inspection.tsv`;
- `retry-migrations.log`, original `retry-receipt.json`, reported exit receipt;
- `native-evidence.json`, `blocker-inspection.tsv`, `assessment.json`;
- original/after preservation receipts and the retained pre-memory mismatch;
- three negative ownership/transport/nonempty guard checks;
- initialization/provision/server/shutdown logs and both owned partial schemas.

The laboratory server was cleanly shut down. Existing preview/workflow/database
services were not restarted or retargeted. All excluded deployment/notification/
provider/financial actions remain unexecuted. The requested inventory and
bootstrap/recovery specification are complete as an assessment; the conditional
bootstrap/reference/admin/key/upgrade/restore proof stops at the required
authority boundary and remains unaccepted.

## 8. Owner-approved isolated authority rehearsal

The owner explicitly approved the following **local-only** policy on 2026-10-07:

- A new private socket-only MySQL 8.0.42 instance and a new empty schema;
  neither historical partial schema may be reused, repaired or replayed.
- Temporary SUPER only for the dedicated bootstrap account, with reviewed
  schema-scoped migration privileges. No elevated application credentials.
- Binary logging remains enabled; trusted function creators remain disabled;
  event scheduler, TCP, mysqlx and outbound application transports remain off.
  All native financial migrations/guards remain unchanged.
- After bootstrap, revoke all elevated/migration privileges and lock the
  retained definer identity. Regrant only table-level TRIGGER and SELECT required
  by the installed bodies (subject OLD/NEW references and referenced read tables).
  Any unexpected definer-write requirement stops qualification.
- Application grants remain schema-scoped SELECT/INSERT/UPDATE/DELETE, without
  DDL, TRIGGER, SUPER, FILE, GRANT OPTION or cross-schema authority.
- Retain actual child/supervisor exit receipts and verify the complete ordered
  native ledger, raw DDL/columns/indexes/FKs/enforced CHECKs, trigger bodies,
  definers, final grants and source preservation before declaring a DDL pass.

**Evidence availability disclosure:** The historical private directory
`.local/mysql-bootstrap-rehearsal/` and its assessment/native/log files were
absent when this work resumed. Section 7 is the retained historical report,
not a fresh inspection of those files or partial schemas. No historical evidence
was reconstructed, copied from normal data or silently promoted to current proof.
The independent new lab/evidence lives in
`.local/mysql-approved-bootstrap-rehearsal/` (Git-ignored, mode 0700, umask 077).

The laboratory-only helpers are `scripts/database/mysql-approved-lab.sh`,
`mysql-approved-source.php`, `mysql-approved-evidence.php` and the approved mode
of `mysql-bootstrap-rehearsal.php`. The shell wrapper refuses an existing lab,
retains the real migration child exit without a pipeline, and always attempts
privilege revocation/definer locking and clean shutdown after server startup.
Evidence-collection failures are not treated as successful migrations.
These helpers have no arbitrary server, database or production URL input.

Reviewed MySQL 8.0 authority references:

- [CREATE TRIGGER](https://dev.mysql.com/doc/refman/8.0/en/create-trigger.html):
  omitted DEFINER uses the creating identity; activation needs subject TRIGGER,
  SELECT for OLD/NEW and privileges required by body statements.
- [Stored program binary logging](https://dev.mysql.com/doc/refman/8.0/en/stored-programs-logging.html):
  error 1419/SUPER restriction with logging enabled and trusted creators disabled.
- [Account locking](https://dev.mysql.com/doc/refman/8.0/en/account-locking.html):
  locking prevents direct login without disabling stored objects using that definer.

This approval does **not** authorize production/VPS, normal database writes,
external services, workers, financial execution, reference/admin provisioning or
populated upgrade/restore. Production remains **NO-GO**.

### Completed native result

**Outcome: DDL-ONLY PASS; not a complete initializer or release approval.**

The new initially empty `agendaally_approved_empty_lab` completed the unchanged
229-file migration chain using Laravel's native migrator. Actual ordered ledger
entries match every frozen filename, and the frozen manifest hash remains
`37954929be028d7e487a7720f2108d3f300b2862af85f46b724b3c9f63d3f896`.
The dedicated migration child exited **0**, without a pipe masking its status.

Verified native evidence:

- **213 InnoDB tables, 307 foreign-key constraints, 21 enforced CHECKs and 29
  triggers.** Counts summarize inspected metadata, not substitute thresholds.
- Every installed trigger's body, table, event and timing matches SQL emitted by
  the unchanged source. Receipt non-self-anchoring, MTN binding/identity/lifecycle/
  retention, generic binding/original identity and manual evidence/workflow guards
  are all present. All definers are `lab_bootstrap@localhost`.
- Native signed BIGINT financial columns, unsigned AUTO_INCREMENT accounting
  identities, JSON evidence columns, 17 explicitly required named unique indexes
  and all leading FK-supporting indexes satisfy the inspected contracts.
- Binary logging is **ON**, ROW format; trusted creators **OFF**; MySQL **8.0.42**,
  UTC, REPEATABLE READ. TCP/mysqlx/event scheduler remain disabled. Binary logs
  are retained; no server-trust relaxation occurred.
- Bootstrap SUPER and migration privileges were revoked. The definer account is
  locked with exactly the required table-level TRIGGER/SELECT grants and no
  global/cross-schema/write/grant authority. Direct definer login was natively
  rejected. The application identity has exactly schema-scoped data privileges;
  its read-only ledger access returns 229 rows.
- All inspected user/shop/order/booking/Wallet/payment/finance/provider-profile/
  send-intent/user-grant tables are empty. Migration-created role, permission and
  template definitions are not an approved reference/admin initializer.
- Protected original and both sanitized source-tree fingerprints are unchanged.
  No normal database connection or write was used for this independent rehearsal.
  Historical normal-row/history preservation claims were not re-certified from
  missing private evidence.

**Honest evidence recovery:** The first post-bootstrap verifier erroneously
ordered `information_schema.check_constraints` by nonexistent `table_name`.
Migration exit was 0, privilege revocation succeeded and the server shut down,
but the supervisor correctly exited **92**, not success. The original error log
and exit receipt are retained unchanged. After correcting only the helper query,
`mysql-approved-reinspect.sh` restarted the **completed** lab for read-only
inspection, with no migration replay, grant changes or business-row writes.
Verification child and reinspection supervisor both exited **0**; clean shutdown
left no lab socket. All 213 raw table DDL/row counts/row hashes exactly match the
completed migration receipt, proving no schema/data change during reinspection.
No partial schema was replayed.

Negative checks also returned nonzero for enabled process/transports (**255**),
nonempty-schema replay (**255**) and lab-directory reuse (**65**). These guard
attempts did not initialize another server or alter migration evidence.

Private evidence includes approval/ownership, source-before/after receipts,
initialization/provision/server/revocation/shutdown logs, emitted DDL, full native
metadata/trigger SHOW CREATE, exact final grants, completed migration receipt,
actual child/supervisor exits, the failed verifier log, read-only reinspection
exits/preservation comparison, guard-rejection logs and final assessment.
Keep these private; this same-host retention is not independent recovery custody.

Non-secret integrity receipts (SHA-256):

| Private receipt | SHA-256 |
|---|---|
| `assessment.json` | `3384d4190c17bd852ff107c94df3c39aea5e8553f5305e8aaff2dc53f63486f0` |
| `native-evidence.json` | `7793874b3176ed248fca91113ec9e506df6b127e55dc15672858e621c13030c0` |
| `receipt.json` | `f960dc9b1d1d20fef06e6de2e90f25698121dd9515dd5b8369fea588bbde843e` |
| `child-exit.json` | `43ad1cde993443fe6bfabea5eaa1163e0471c3e52934891629e5669d4ded6fb0` |
| `supervisor-exit.json` | `9b21a2f0a3f28a3c32c1fdd41736b5f11d648c8544ccbddfbd058e0c38ed08ea` |
| `reinspection-exit.json` | `68694d3e5cfcc36fc9b5aea055789d4c4c18d34b5e13720cae38b0a0a944b056` |

Remaining first-install reference ordering/administrator/key custody and
populated upgrade/isolated restore are covered by the existing separate work.
This result does not establish concurrency, principal-conservation, authorization,
provider behavior, financial execution or production recovery qualification.

## 9. Separately approved populated synthetic upgrade and recovery

The owner subsequently approved a new local-only populated synthetic rehearsal
and a synthetic-only logical backup restored solely into a second new isolated
instance. This does not authorize normal data or historical dump copying and
does not extend the earlier empty-bootstrap approval to production.

The exact candidate, execution boundaries, fingerprint/metadata contract,
independent key recovery and stale-authority policy are documented in
[`mysql-synthetic-upgrade-recovery.md`](mysql-synthetic-upgrade-recovery.md).
Historical NOT EXECUTED/BLOCKED results above remain historical; only that
separate report and its actual retained receipts can qualify the new campaign.

**Completed local result:** the sole reviewed manual-finance migration upgraded
the populated synthetic baseline from 228 to 229 native ledger entries while
preserving old money/access authority. The first isolated import stopped on
unapproved restore-side table-lock authority. Its instance/evidence remained
unchanged. A separately approved corrected logical backup from the source under
read_only/super_read_only recovered to a new third instance without privilege
expansion or populated-migration replay.

All 213 table fingerprints and exact native FK/CHECK/index/trigger/definer
contracts matched. Raw redundant charset syntax differences remain retained and
narrowly qualified, not ignored. Independent local key/private-file/config
recovery, wrong-key rejection, native stale-session/token/challenge invalidation
and passive PENDING/UNKNOWN HOLD ALL reconciliation passed. Actual retry
supervisor exit is 0; all lab sockets are closed. Local recovery readiness was
54.35 seconds, with zero synthetic writes lost at the frozen recovery point.
This is not production/off-host recovery or external financial/SMTP acceptance.
