# Customer mobile audit evidence

This directory belongs to the **read-only** Customer mobile audit of 2026-10-06.
It is evidence, not application implementation or a remediation campaign.

- `source-inventory.json`: all 875 existing mobile files with byte sizes; 39 screen families, 21 repositories and 36 BLoC directories.
- `api-callsite-inventory.json`: 172 exact repository HTTP call expressions, endpoint literals, auth expressions, model parsers, direct consumers and importing presentation files.
- `source-before.json`, `source-after.json`, `source-preservation.json`: exact SHA256 comparison over the original 5,816 mobile/web/admin/backend baseline files.
- `database-before.json`, `database-after.json`: normal development SQLite schema and row-ordered field fingerprints, **not row data**.
- `database-fingerprint-recipe.json`: read-only DSN, transaction, fetch/serialization/ordering recipe and comparison result.
- `sdk-version.txt`: available Flutter/Dart versions.
- `pub-get---offline.txt`, `build-apk---debug---no-pub.txt`, corresponding `.exit` files: exact command failures.
- `analyze---no-pub.txt.gz`: complete original analyzer output, compressed without editing; `analyze-excerpt.txt` is a convenience excerpt.
- `validation-commands.json`: validation command arguments, isolated environment, outcomes and explicitly unexecuted work.
- `git-status.txt`, `git-diff-stat.txt`: final independent addition/track-change check.
- `report.html`: self-contained readable copy of the durable Markdown report.
- `evidence-manifest.json`: byte sizes and SHA256 of this evidence package (excluding itself).

The inventory's field names and reverse import links are lexical discovery aids.
They are not a generated OpenAPI specification, exact event-to-screen execution
proof, or evidence that a flow worked. A similarly named method in another
subsystem can appear in reverse references. Where backend request/resource
compatibility was not established, the integration is explicitly **UNKNOWN /
REQUIRES RUNTIME ACCEPTANCE**. Critical contract findings and subsystem-level
comparisons are explained in the report.

No mobile build or runtime acceptance passed. The unresolved analyzer's issue
count must **not** be cited as a count of mobile source defects.

The collector writes only this evidence directory. It requires the temporary
baseline retained during this audit; it is not an application build script.
Normal database fingerprinting used a separate CLI-only PDO read transaction:

```sql
PRAGMA query_only=ON;
SELECT type,name,tbl_name,sql
FROM sqlite_master
WHERE name NOT LIKE 'sqlite_%'
ORDER BY type,name;
SELECT * FROM "<escaped-table>" ORDER BY rowid;
```

Each result was fetched with `PDO::FETCH_ASSOC` and hashed as
`SHA256(PHP serialize(fetchAll))`. The DSN included `?mode=ro`; the read
transaction was rolled back. All 214 table fingerprints and the schema
fingerprint matched the baseline. No Laravel application boot, migrations,
reseed, writes, service activation or financial commands were used.
