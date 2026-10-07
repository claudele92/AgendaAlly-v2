import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import { fileURLToPath } from "node:url";

// Source-only inventory. Never opens an environment file, dump, or database.
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const backend = path.join(root, ".migration-backup/backend");
const digest = (file) =>
  crypto.createHash("sha256").update(fs.readFileSync(file)).digest("hex");
const directory = path.join(backend, "database/migrations");
const migrations = fs.readdirSync(directory).filter((name) => name.endsWith(".php")).sort();
const risky = [];
for (const name of migrations) {
  const source = fs.readFileSync(path.join(directory, name), "utf8");
  const operations = [...new Set(source.match(/\b(?:dropIfExists|drop|truncate|delete|dropColumn|renameColumn)\s*\(/g) ?? [])];
  if (operations.length) risky.push({ name, operations });
}
const cacheDirectory = path.join(backend, "bootstrap/cache");
const caches = ["config.php", "routes-v7.php", "services.php", "packages.php"]
  .filter((name) => fs.existsSync(path.join(cacheDirectory, name)));
console.log(JSON.stringify({
  scope: "preserved source only; no database connected",
  composer: {
    manifest_sha256: digest(path.join(backend, "composer.json")),
    lock_sha256: digest(path.join(backend, "composer.lock")),
  },
  migration_count: migrations.length,
  migration_manifest: migrations.map((name) => ({ name, sha256: digest(path.join(directory, name)) })),
  risk_candidates: risky,
  caveat: "Text scan includes rollback/down methods; candidates require manual review, not all are destructive on upgrade.",
  preserved_bootstrap_caches_present: caches,
  database_migration_state: "UNKNOWN: no original database or migrations table was accessed",
  original_database_backup_verified: false,
  production_restore_verified: false,
}, null, 2));