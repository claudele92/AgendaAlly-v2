# Owned development preview content

The native AgendaAlly application already has Laravel-backed settings/footer,
English editorial locale, Terms, Privacy, About-page, FAQ and blog/article contracts. The guarded
`DevelopmentPreviewContentSeeder` fills those existing contracts; it does not
add a CMS, route, migration, browser-only fixture or new policy.

## Reproduction and safety

- Fresh disposable database only:
  `node scripts/development.mjs bootstrap` followed by
  `node scripts/development.mjs seed`.
- Existing owned database: `node scripts/development.mjs seed` runs the regular
  idempotent development demo seed. That general-purpose path now also seeds
  preview content.
- Content/footer and safe cash catalog only, without re-running accounts,
  business listings, inventory, carts, bookings or orders:
  in `.migration-backup/backend`, run
  `php artisan development:database-seed --content-only` with
  `APP_ENV=local`, `DEVELOPMENT_MODE=true`,
  `AGENDAALLY_DEVELOPMENT_DATABASE=true` and the expected owned SQLite
  connection configured.
- From the repository root, the equivalent convenience command is
  `node scripts/development.mjs seed --content-only`. It accepts only the
  optional `--content-only` flag and delegates to the same guarded Laravel
  command.

Both paths use the command's existing explicit local-only opt-in, path checks,
reviewed ownership marker, exclusive file lock and a database transaction.
The dedicated content seeder repeats the ownership check so direct use cannot
silently seed an unowned database. `--content-only` does not advance or rewrite
the demo seed version: it changes no market/catalog, account, stock, booking,
cart, order, ledger or wallet fixture. The seed is additive, uses only existing
tables, never calls the legacy broad-reset `DatabaseSeeder`, `ContentPagesSeeder`
or `BlogStorySeeder`, and does not mutate shop socials or create ephemeral
stories.

Existing settings and About page types are preserved. Existing legal singleton
records are preserved. The seeded FAQ/blog identities are stable UUIDs, and
reruns do not update existing rows or translations. Missing settings are
filled only when absent; existing or intentionally blank values always win.
Payment catalog seeding retains the current rule that only offline cash is
enabled. No credentials, real contact details, store listings, provider
operations, successful charges, messages, refunds or settlements are created.

## Editorial and media limits

- Terms and Privacy are visibly marked **DEVELOPMENT SAMPLE — NOT OPERATIVE**
  and require owner and qualified legal review before production. They do not
  state cancellation/refund entitlements, collection/retention promises or
  customer duties.
- About and FAQ are sample descriptions of existing product behavior, with
  synthetic-data notices. No phone, address, support mailbox or official
  social/store destination is inferred. Footer social fields, when absent, use
  non-official `example.com/development-preview/...` destinations; the native
  footer constructs a secure link from those values. These example pages are
  placeholders, not AgendaAlly social accounts.
- Three English articles use the existing blog, translation, author and
  publication-date fields. The original schema has no article category or tag
  relationship, so the seed does not invent one.
- Blog and About images reuse exact remote Unsplash URLs already present in the
  original `BlogStorySeeder` and `ContentPagesSeeder`; no new local/remote asset
  is claimed or supplied by this seed. They remain remotely hosted dependencies.
  Exact photograph authorship, image-to-copy fit, availability and current
  licensing have **not** been independently verified here; owner review is
  required before production. Settings intentionally omit placeholder official
  social handles, telephone/address claims, map credentials and app-store URLs
  from the legacy `SettingsSeeder`.

The original native UI can read these rows over its existing `/api/v1/rest`
settings, `pages`, `faqs/paginate`, `blogs/paginate`, `term`, and `policy`
routes. The current footer navigation includes links to About, FAQ, Terms,
Privacy and Blog. Footer and preview-surface presentation adjustments belong
to the separate Stage 1 UI work, not this backend seed. Browser rendering
still needs the owning agent's focused development-preview verification.

Focused owned-bootstrap regression (both full-demo repeat and content-only
rollback coverage):
`cd .migration-backup/backend && php vendor/bin/phpunit -c phpunit-development.xml --filter 'DevelopmentDemoSeederTest|DevelopmentPreviewContentTest' tests/Development`