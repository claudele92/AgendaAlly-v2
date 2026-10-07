// Read-only document/source checks. No Laravel boot, PHP execution, database,
// dotenv/secret access, directories, downloads or approval/execution path.
import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import assert from "node:assert/strict";
import { execFileSync } from "node:child_process";
import { fileURLToPath } from "node:url";
import { literalArray } from "./reference-literal-parser.mjs";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
const sha = bytes => crypto.createHash("sha256").update(bytes).digest("hex");
const mode = process.argv[2];
if (process.argv.length !== 3 || !["--check", "--self-test"].includes(mode)) {
  throw new Error("Use --check or --self-test; no initialization mode exists.");
}
const main = JSON.parse(fs.readFileSync(path.join(root, "docs/deployment/local-synthetic-reference-manifest.json")));
const filename = path.join(root, main.portable_baseline_file);
const baseline = JSON.parse(fs.readFileSync(filename));
function safeSource(relative) {
  assert(!path.isAbsolute(relative) && !relative.split("/").includes(".."), "Unsafe source path");
  let current = root;
  for (const part of relative.split("/")) {
    current = path.join(current, part);
    assert(!fs.lstatSync(current).isSymbolicLink(), "Symlinked source refused");
  }
  assert(!relative.split("/").some(part => part.startsWith(".env")), "dotenv refused");
  return fs.readFileSync(current);
}
function walk(relative) {
  const result = [];
  for (const entry of fs.readdirSync(path.join(root, relative), { withFileTypes: true })) {
    assert(!entry.isSymbolicLink(), "Symlinked execution source refused");
    if (entry.name === "cache") continue;
    assert(!entry.name.startsWith(".env"), "dotenv in execution tree refused");
    const filename = `${relative}/${entry.name}`;
    if (entry.isDirectory()) result.push(...walk(filename));
    else if (entry.isFile()) result.push(filename);
    else throw new Error("Non-regular execution source refused");
  }
  return result;
}
function digest(entries) {
  return sha(entries.map(([filename, hash]) => `${filename} ${hash}`).sort().join("\n"));
}
function checkRows(primary, draft) {
  assert.equal(primary.status, "DRAFT_REQUIRES_OWNER_APPROVAL");
  assert.equal(draft.status, "DRAFT_REQUIRES_OWNER_APPROVAL");
  const roles = Object.fromEntries(primary.rows.roles.map(row => [row.name, row.id]));
  assert.deepEqual(roles, { user: 1, seller: 11, moderator: 12, deliveryman: 13,
    shop_manager: 14, manager: 21, master: 22, admin: 99 });
  assert.deepEqual(draft.schema_contracts.brands.required_fields, ["uuid", "title"]);
  assert.equal(draft.schema_contracts.brands.source, "database/migrations/2022_04_06_181958_create_brands_table.php");
  for (const [table, contract] of Object.entries(draft.schema_contracts)) {
    for (const row of draft.rows[table]) for (const field of contract.required_fields) {
      assert(Object.hasOwn(row, field) && row[field] !== null && row[field] !== "",
        `Missing required native field: ${table}.${field}`);
    }
  }
  for (const table of ["users", "shops", "categories", "products", "brands"]) {
    for (const row of draft.rows[table]) {
      assert.match(row.uuid ?? "", /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/,
        `Missing/invalid native UUID: ${table}`);
      const digest = sha(`agendaally-portable-demo|${table}|${row.id}`).slice(0, 32);
      const expected = `${digest.slice(0, 8)}-${digest.slice(8, 12)}-${digest.slice(12, 16)}-${digest.slice(16, 20)}-${digest.slice(20)}`;
      assert.equal(row.uuid, expected, `Nondeterministic demo identity: ${table}`);
    }
  }
  for (const [table, rows] of Object.entries(draft.rows)) assert.equal(draft.counts[table], rows.length, `Count mismatch: ${table}`);
  for (const [table, count] of Object.entries({ countries: 4, cities: 7, categories: 84, shops: 9, users: 29,
    services: 51, service_masters: 75, shop_locations: 26, invitations: 20, invitation_shop_locations: 42,
    products: 13, stocks: 14, units: 33, currencies: 8, shop_roles: 9, shop_role_permissions: 62 })) {
    assert.equal(draft.rows[table].length, count, `Baseline mismatch: ${table}`);
  }
  for (const table of ["country_role_permissions", "country_admins", "country_invitations", "country_payments",
    "role_has_permissions", "model_has_permissions", "wallets", "wallet_histories", "orders", "order_details",
    "transactions", "bookings", "payment_process", "payment_payloads", "platform_payment_configs",
    "email_settings", "email_templates", "shop_subscriptions"]) {
    assert.equal(draft.rows[table].length, 0, `Forbidden activity/grant: ${table}`);
  }
  function unique(table, columns) {
    const keys = draft.rows[table].map(row => JSON.stringify(columns.map(key => row[key])));
    assert.equal(new Set(keys).size, keys.length, `Duplicate natural key: ${table}`);
  }
  for (const [table, columns] of [
    ["countries", ["code"]], ["country_roles", ["country_id", "name"]], ["users", ["email"]],
    ["model_has_roles", ["model_type", "model_id", "role_id"]],
    ["shop_locations", ["shop_id", "region_id", "country_id", "city_id", "type"]],
    ["invitations", ["shop_id", "user_id"]], ["service_masters", ["service_id", "master_id"]],
    ["invitation_shop_locations", ["invitation_id", "shop_location_id"]], ["settings", ["key"]],
    ["translations", ["locale", "group", "key"]], ["shop_role_permissions", ["shop_role_id", "shop_permission_id"]]
  ]) unique(table, columns);
  function fk(table, column, referenced, nullable = false) {
    const ids = new Set(draft.rows[referenced].map(row => row.id));
    for (const row of draft.rows[table]) {
      if (nullable && row[column] === null) continue;
      assert(ids.has(row[column]), `Draft FK mismatch: ${table}.${column}`);
    }
  }
  for (const [table, column, referenced, nullable] of [
    ["countries", "region_id", "regions"], ["countries", "currency_id", "currencies"],
    ["cities", "country_id", "countries"], ["shops", "user_id", "users"],
    ["users", "currency_id", "currencies"], ["shop_locations", "shop_id", "shops"],
    ["shop_locations", "country_id", "countries"], ["shop_locations", "city_id", "cities"],
    ["shop_locations", "area_id", "areas", true], ["services", "shop_id", "shops"],
    ["services", "category_id", "categories"], ["service_masters", "service_id", "services"],
    ["service_masters", "master_id", "users"], ["invitations", "shop_role_id", "shop_roles", true],
    ["invitation_shop_locations", "invitation_id", "invitations"],
    ["invitation_shop_locations", "shop_location_id", "shop_locations"],
    ["products", "category_id", "categories"], ["products", "unit_id", "units"],
    ["products", "brand_id", "brands"], ["stocks", "product_id", "products"]
  ]) fk(table, column, referenced, nullable);
  for (const row of draft.rows.users) {
    assert.equal(row.password, null);
    assert.equal(row.phone, null);
    assert.equal(row.email, `demo-${row.id}@agendaally.invalid`);
    assert.equal(row.email_verified_at, null);
  }
  const shopPermissionIds = new Map(primary.rows.shop_permissions.map(row => [row.id, row.key]));
  const shopRoleIds = new Set(draft.rows.shop_roles.map(row => row.id));
  for (const grant of draft.rows.shop_role_permissions) {
    assert(shopRoleIds.has(grant.shop_role_id));
    const key = shopPermissionIds.get(grant.shop_permission_id);
    assert(key && !key.startsWith("payments.") && !key.startsWith("transactions."), "Financial shop grant");
  }
  assert.equal(draft.rows.model_has_roles.length, draft.rows.users.length);
  assert(draft.rows.model_has_roles.every(row => [11, 14, 22].includes(row.role_id)), "Unintended demo authority");
  assert(draft.rows.blogs.every(row => row.user_id === primary.administrator_proposal.identity.id), "Unknown blog author");
}
checkRows(main, baseline);
if (mode === "--self-test") {
  const mutate = fn => {
    const copy = structuredClone(baseline);
    fn(copy);
    assert.throws(() => checkRows(main, copy));
  };
  mutate(copy => copy.rows.wallets.push({ id: 1, price: 1 }));
  mutate(copy => { copy.rows.country_role_permissions.push({ country_role_id: 1, country_permission_id: 24 }); copy.counts.country_role_permissions++; });
  mutate(copy => copy.rows.shop_role_permissions[0].shop_permission_id = 6);
  mutate(copy => copy.rows.users[0].password = "forbidden-demo-value");
  mutate(copy => delete copy.rows.brands[0].uuid);
  mutate(copy => copy.rows.brands[0].uuid = null);
  mutate(copy => copy.rows.brands[0].uuid = "");
  mutate(copy => delete copy.rows.brands[0].title);
  mutate(copy => copy.schema_contracts.brands.required_fields = []);
  mutate(copy => copy.rows.services[0].category_id = 99999);
  mutate(copy => { copy.rows.settings.push(copy.rows.settings[0]); copy.counts.settings++; });
  const swapped = structuredClone(main);
  [swapped.rows.roles[0].id, swapped.rows.roles[4].id] = [14, 1];
  assert.throws(() => checkRows(swapped, baseline));
  assert.deepEqual(literalArray("x = ['url'=>'https://example.invalid/a', 'amount'=>5 * 600, 'body'=>'a' . 'b',]", "x ="),
    { url: "https://example.invalid/a", amount: 3000, body: "ab" });
  assert.deepEqual(literalArray("return array(array('a'=>1), array('b'=>null));", "return"), [{ a: 1 }, { b: null }]);
  assert.throws(() => literalArray("x = ['password'=>bcrypt('forbidden')];", "x ="));
  assert.throws(() => literalArray("x = ['a'=>1,'a'=>2];", "x ="));
  assert.throws(() => safeSource("../outside"));
  console.log("PASS: read-only manifest/parser negative self-tests. No native execution or approval.");
} else {
  assert.equal(main.source_root, ".local/agendaally-clean-repository/.migration-backup/backend");
  assert.equal(baseline.source_root, main.source_root);
  const source = main.source_root;
  for (const document of [main, baseline]) for (const [relative, expected] of Object.entries(document.source_hashes)) {
    assert.equal(sha(safeSource(`${source}/${relative}`)), expected, `Stale source: ${relative}`);
  }
  const brandSource = safeSource(`${source}/${baseline.schema_contracts.brands.source}`).toString("utf8");
  assert(/\$table->uuid\('uuid'\)->index\(\);/.test(brandSource), "Brand UUID schema contract drift");
  assert(/\$table->string\('title'/.test(brandSource), "Brand title schema contract drift");
  const migrations = walk(`${source}/database/migrations`).map(filename => [
    path.basename(filename), sha(safeSource(filename))
  ]).sort(([a], [b]) => a.localeCompare(b));
  assert.equal(migrations.length, main.migration_count);
  assert.equal(digest(migrations), main.migration_sha256);
  for (const checkpoint of main.checkpoints) {
    assert.equal(migrations[checkpoint.ledger_count - 1][0], checkpoint.after_migration);
    assert.equal(digest(migrations.slice(0, checkpoint.ledger_count)), checkpoint.prefix_sha256);
  }
  const execution = ["app", "config", "bootstrap", "database/migrations", "database/seeders", "routes"]
    .flatMap(tree => walk(`${source}/${tree}`)).concat(`${source}/composer.json`, `${source}/composer.lock`)
    .map(filename => [filename.slice(source.length + 1), sha(safeSource(filename))]);
  assert.equal(execution.length, main.execution_source_count);
  assert.equal(digest(execution), main.execution_source_sha256);
  for (const asset of baseline.assets) {
    const bytes = safeSource(asset.file);
    assert.equal(sha(bytes), asset.sha256);
    assert.equal(bytes.length, asset.bytes);
  }
  assert.equal(sha(safeSource(baseline.service_photo_source.file)), baseline.service_photo_source.sha256);
  const generated = JSON.parse(execFileSync(process.execPath, [path.join(root, "scripts/database/prepare-portable-baseline-review.mjs")], {
    encoding: "utf8", maxBuffer: 3 * 1024 * 1024
  }));
  assert.deepEqual(baseline, generated, "Draft row/source generation drift");
  console.log("PASS: frozen source, checkpoint hashes, proposed rows/relationships and workspace asset bytes.");
  console.log("DRAFT ONLY: no native FK/idempotency/login/custody proof; portable media packaging remains approval-gated.");
  console.log(`Manifest SHA256 ${sha(safeSource("docs/deployment/local-synthetic-reference-manifest.json"))}`);
  console.log(`Baseline SHA256 ${sha(safeSource(main.portable_baseline_file))}`);
}
