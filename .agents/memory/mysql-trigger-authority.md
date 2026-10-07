---
name: MySQL trigger creation authority
description: Scoped TRIGGER grants alone do not certify an empty MySQL financial bootstrap with binary logging.
---

Freeze binary-log and trigger-creation authority alongside engine/session settings
before certifying an empty MySQL bootstrap. Never grant SUPER to the application,
disable binary logging, enable trusted function creation or omit financial
triggers merely to get a passing rehearsal.

**Why:** A socket-only MySQL 8.0.42 lab started with `--no-defaults` still had
binary logging enabled and trusted function creators disabled. A schema-scoped
migration identity with TRIGGER privileges reached the financial receipt table
but native trigger creation failed with error 1419, leaving partial DDL without
the migration ledger entry. Neither empty SQLite nor earlier privileged MySQL
proof establishes least-privilege deployment bootstrap.

**How to apply:** Inspect actual binary-log/trust settings and trigger definers.
Require an independently approved DBA/bootstrap authority policy; retain raw
failure, partial DDL and ledger, and stop before replay or privilege changes.
Schema-only success must remain separate from reference ordering, independent
admin/key custody and populated upgrade/restore qualification.

The owner approved temporary SUPER **only** for a dedicated bootstrap account in
a new private, socket-only local rehearsal instance. Preserve binary logging,
keep trusted creators disabled and install financial guards unchanged. Revoke
elevated privileges afterward, lock the retained definer identity and keep only
its reviewed trigger-table/read privileges; runtime access stays least-privilege.

**Why:** Explicit local-only owner approval resolves the lab creation-authority
boundary without changing server trust or granting elevated application access.

**How to apply:** This approval does not extend to production/VPS, normal
database writes, external services, workers or financial execution. Any such
expansion needs separate approval; do not reuse partial schemas.
