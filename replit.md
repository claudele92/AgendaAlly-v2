# AgendaAlly

AgendaAlly is a Laravel marketplace API with a Next.js customer storefront,
React/Ant Design vendor/admin portal and Flutter client. Booking services and
product commerce are both core workflows.

## Active source and ownership

### MVP database and launch direction

Current bounded assessment and the single remaining-work matrix:
`docs/development/agendaally-mvp-readiness.md`. Tested MySQL source financial
requirements pass; whole-product production readiness does not. The forward
booking-correctness/selected-journey phase is authorized. Half-open adjacency,
processing-once and full working-hour rules are confirmed and implemented.
Bounded native Cash/Wallet and independent-resource checks pass; the whole booking
gate still requires remaining contention/lifecycle/role certification.
The creator confirmed: skip months missing the original day-of-month anchor,
never clamp; count all scheduled occurrences from origin, including the first,
regardless of working hours/closed dates/query windows. Apply capacity filters
only after calendar counting. Recurrence correction passes bounded unit/native
calendar checks. Counted metadata is retained rather than using approximate
expiry; date-ended cleanup preserves the inclusive final day.
complete remaining contention cases before selected downstream acceptance.

MySQL 8/InnoDB is the selected production database target, not a production
certification. Deployment is expected on a VPS; do not access or deploy to it
without separate approval. PostgreSQL was evaluated and is technically viable
for tested financial invariants, but further conversion/porting/certification is
deferred because it provides insufficient MVP benefit relative to the existing
MySQL implementation. Preserve its evidence; reopen work only by explicit request.
Do not implement Strategy G or operational_evidence_manifest without a real,
separately approved requirement for nested stale-transaction reservations.
The selected MVP is service-booking-first with Cash and legitimately funded
Wallet. Product purchasing/stock/multi-Shop checkout, electronic activation,
Vendor-direct/own-gateway, external payout execution, Delivery expansion and
speculative architecture are deferred. Do not begin VPS staging or production
deployment automatically after application acceptance.

The active original applications remain in `.migration-backup/backend`,
`.migration-backup/web`, `.migration-backup/admin` and
`.migration-backup/customer_app`. The directory's historical name does not
make this code read-only. Intentional source changes are now authorized.
Retaining these paths avoids a broad file move while preserving existing
security/domain test and API-contract references.

`artifacts/api-server`, `lib/` and the root pnpm workspace are unrelated
initial scaffolding, not the AgendaAlly Laravel API. Do not substitute
Express/Drizzle/PostgreSQL for the original backend or push their schema as
part of AgendaAlly setup.

## Development

See `docs/development/README.md` for prerequisites, environment configuration,
bootstrap, synthetic accounts and verification.

```sh
node scripts/development.mjs install
node scripts/development.mjs init           # localhost; use --replit on Replit
node scripts/development.mjs configure
node scripts/development.mjs bootstrap
node scripts/development.mjs seed
node scripts/development.mjs serve backend
node scripts/development.mjs serve web      # separate terminal/workflow
node scripts/development.mjs serve admin    # separate terminal/workflow
```

The applications also support normal Laravel/Next/Vite commands with their
own `.env` configuration. Do not copy application code into disposable
preview trees for ordinary development. The old isolated preview scripts
remain historical/security evidence, not the primary setup path.

## Safety and compatibility

- Development bootstrap/seeding requires explicit local/testing mode and an
  independently owned disposable database. Never use a production connection,
  inherited database URL, unknown database or production data.
- Do not blindly replay the historical migrations or run `migrate:fresh`.
  Reviewed development bootstrap is separate from production upgrades.
- Dependency installation must not seed or migrate a database implicitly.
- Development-only reCAPTCHA/integration exceptions must fail closed outside
  their explicitly allowed environments. No live payment/message operations
  are authorized by development setup.
- Preserve hardening/domain checks and Flutter REST `/api/v1` contracts.
- Preserve multi-vendor, multi-branch, country and role workflows, branding,
  commissions/accounting, service booking and carts/orders.
- Protect private environment, database and agent files from frontend dev
  servers, including encoded/nonexistent filesystem requests.
- Broad UI redesign is not authorized in this engineering phase. Propose
  design-system, navigation and screen improvements for approval after the
  development foundation is verified.