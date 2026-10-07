// Source-derived review only. No database, network, copy/install or approval mode.
import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import assert from "node:assert/strict";
import { execFileSync } from "node:child_process";
import { fileURLToPath } from "node:url";
import { literalArray } from "./reference-literal-parser.mjs";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
const baselineFile = "docs/deployment/local-synthetic-portable-baseline.json";
const reviewFile = "docs/deployment/local-synthetic-demo-asset-review.json";
const sha = bytes => crypto.createHash("sha256").update(bytes).digest("hex");

export function regularFile(base, relative, directory = false) {
  assert(typeof relative === "string" && relative.length > 0);
  assert(!path.isAbsolute(relative) && !relative.includes("\\"));
  const parts = relative.split("/");
  assert(parts.every(part => part && part !== "." && part !== ".." && !part.startsWith(".env")),
    "Unsafe source path");
  let current = base;
  assert(fs.lstatSync(current).isDirectory() && !fs.lstatSync(current).isSymbolicLink());
  for (const [index, part] of parts.entries()) {
    current = path.join(current, part);
    const stat = fs.lstatSync(current);
    assert(!stat.isSymbolicLink(), "Symlink refused");
    assert(index === parts.length - 1 && !directory ? stat.isFile() : stat.isDirectory(), "Non-regular source");
  }
  return current;
}

export function verifyPhoto(bytes, asset, mime) {
  assert.equal(bytes.length, asset.bytes, "Photo size drift");
  assert.equal(sha(bytes), asset.sha256, "Photo hash drift");
  assert.equal(mime, "image/jpeg", "Wrong photo MIME");
  assert(bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff, "Not JPEG bytes");
}

export function buildReview() {
  const read = relative => fs.readFileSync(regularFile(root, relative));
  const baselineBytes = read(baselineFile);
  const baseline = JSON.parse(baselineBytes);
  assert.equal(baseline.status, "DRAFT_REQUIRES_OWNER_APPROVAL");
  const source = relative => {
    assert(Object.hasOwn(baseline.source_hashes, relative), "Unfrozen source");
    const file = `${baseline.source_root}/${relative}`;
    const bytes = read(file);
    assert.equal(sha(bytes), baseline.source_hashes[relative], "Source hash drift");
    return { file, sha256: sha(bytes), text: bytes.toString("utf8") };
  };
  const productsSource = source("database/seeders/ProductCatalogDemoSeeder.php");
  const definitions = literalArray(productsSource.text, "private const PRODUCTS =");
  const photoMetadata = read(baseline.service_photo_source.file);
  assert.equal(sha(photoMetadata), baseline.service_photo_source.sha256);
  const metadata = JSON.parse(photoMetadata);
  const rows = baseline.rows;
  const relationship = (table, row) => {
    if (table === "services") return { table, id: row.id, shop_id: row.shop_id,
      title: rows.service_translations.find(t => t.service_id === row.id && t.locale === "en").title };
    if (table === "products") return { table, id: row.id, shop_id: row.shop_id,
      title: rows.product_translations.find(t => t.product_id === row.id && t.locale === "en").title };
    if (table === "stocks") return { table, id: row.id, product_id: row.product_id };
    return { table, id: row.id, loadable_type: row.loadable_type, loadable_id: row.loadable_id };
  };
  const relationships = target => ["services", "products", "stocks", "galleries"].flatMap(table =>
    rows[table].filter(row => (table === "galleries" ? row.path : row.img) === target)
      .map(row => relationship(table, row)));
  const servicePhotos = baseline.assets.filter(asset => asset.key).map(asset => {
    assert.equal(asset.file, `attached_assets/service-photos/${asset.key}.jpg`, "Unapproved input namespace");
    assert.equal(asset.target, `/storage/portable-demo/services/${asset.key}.jpg`);
    assert(metadata.assets.some(original => original.key === asset.key && original.sha256 === asset.sha256));
    const filename = regularFile(root, asset.file);
    const bytes = fs.readFileSync(filename);
    const mime = execFileSync("file", ["--brief", "--mime-type", "--", filename], { encoding: "utf8" }).trim();
    verifyPhoto(bytes, asset, mime);
    return { key: asset.key, file: asset.file, target: asset.target, bytes: bytes.length,
      mime, sha256: sha(bytes), source: asset.source, license_reference: asset.license,
      licensing_status: "RECORDED_REFERENCE_NOT_INDEPENDENTLY_VERIFIED_OR_OWNER_APPROVED",
      inclusion_status: "WORKSPACE_ONLY_NOT_PACKAGED",
      relationships: relationships(asset.target) };
  });
  assert.equal(servicePhotos.length, 15);
  assert.equal(servicePhotos.flatMap(asset => asset.relationships).filter(r => r.table === "services").length, 51);
  assert.equal(servicePhotos.flatMap(asset => asset.relationships).filter(r => r.table === "galleries").length, 51);
  assert.equal(new Set(servicePhotos.map(asset => asset.target)).size, 15);

  // Look only in source-controlled static public namespaces, not storage/uploads,
  // caches, exports or runtime snapshots. Filename matches are not license proof.
  const publicRoots = ["web/public", "admin/public", "backend/public"]
    .map(relative => `${path.dirname(baseline.source_root)}/${relative}`);
  const publicFiles = [];
  function walk(relative) {
    const directory = regularFile(root, relative, true);
    for (const entry of fs.readdirSync(directory, { withFileTypes: true }).sort((a, b) => a.name.localeCompare(b.name, "en"))) {
      if (entry.name === "storage" || entry.name === "uploads" || entry.name.startsWith(".")) continue;
      assert(!entry.isSymbolicLink(), "Symlinked static source refused");
      const file = `${relative}/${entry.name}`;
      if (entry.isDirectory()) walk(file);
      else if (entry.isFile() && /\.(jpe?g|png|webp)$/i.test(entry.name)) {
        regularFile(root, file);
        publicFiles.push(file);
      }
    }
  }
  for (const directory of publicRoots) {
    walk(directory);
  }
  const productPhotos = baseline.other_content_candidates.products.source_asset_candidates.map((asset, index) => {
    const definition = definitions[index];
    assert.equal(asset.source_url, definition.img);
    assert.equal(asset.target, `/storage/portable-demo/products/product-${index + 1}.jpg`);
    const token = new URL(asset.source_url).pathname.split("/").pop();
    const matches = publicFiles.filter(file => path.basename(file).includes(token) ||
      path.basename(file) === `product-${index + 1}.jpg`);
    const lines = productsSource.text.split("\n");
    const line = lines.findIndex(text => text.includes(asset.source_url));
    assert(line >= 0);
    const precedingComments = lines.slice(Math.max(0, line - 4), line)
      .filter(text => text.trim().startsWith("//")).map(text => text.trim().replace(/^\/\/\s?/, ""));
    return { target: asset.target, source_url: asset.source_url, title: definition.title,
      source_evidence: { file: productsSource.file, sha256: productsSource.sha256,
        line: line + 1, unverified_photo_comments: precedingComments },
      local_filename_candidates: matches, file: null, bytes: null, mime: null, sha256: null,
      licensing_status: "SOURCE_COMMENT_CLAIMS_UNSPLASH_NOT_LICENSE_PROOF",
      license_reference_to_review: "https://unsplash.com/license",
      inclusion_status: "BLOCKED_BYTES_LICENSE_SOURCE_AND_OWNER_APPROVAL",
      relationships: relationships(asset.target) };
  });
  assert.equal(productPhotos.length, 5);
  assert.equal(productPhotos.flatMap(a => a.relationships).filter(r => r.table === "products").length, 13);
  assert.equal(productPhotos.flatMap(a => a.relationships).filter(r => r.table === "stocks").length, 14);
  assert.equal(productPhotos.flatMap(a => a.relationships).filter(r => r.table === "galleries").length, 13);

  const remoteSources = ["DemoServiceCatalogSeeder", "DemoExpansionSeeder", "EducationTattooDemoSeeder",
    "ProductCatalogDemoSeeder", "DevelopmentPreviewContentSeeder", "ContentPagesSeeder"].map(name => {
    const record = source(`database/seeders/${name}.php`);
    const references = record.text.split("\n").flatMap((line, index) =>
      [...line.matchAll(/https:\/\/images\.unsplash\.com\/[^'"\s]+/g)].map(match =>
        ({ line: index + 1, url: match[0] })));
    return { file: record.file, sha256: record.sha256, references };
  });
  const nullReplacements = ["shops", "users", "brands", "pages", "blogs", "countries"].map(table => ({
    table, approval_status: "PROPOSED_NULL_NOT_ACCEPTED_PHOTO_PARITY",
    rows: rows[table].map(row => ({ id: row.id,
      ...(table === "users" ? { specialist: rows.model_has_roles.some(r => r.model_id === row.id && r.role_id === 22) } : {}),
      fields: Object.keys(row).filter(key => /^(img|bg_img|background_img|logo_img)$/.test(key) && row[key] === null) }))
      .filter(row => row.fields.length)
  }));
  return {
    status: "DRAFT_REVIEW_ONLY_NO_PACKAGING_AUTHORITY",
    baseline: { file: baselineFile, sha256: sha(baselineBytes) },
    service_metadata: { file: baseline.service_photo_source.file, sha256: sha(photoMetadata),
      historical_assets: metadata.assets.length, selected_assets: servicePhotos.length },
    sanitized_checkout: {
      source_root: baseline.source_root,
      service_photos_present: fs.existsSync(path.join(root, path.dirname(path.dirname(baseline.source_root)), "attached_assets/service-photos")),
      fresh_checkout_availability_proven: false,
      note: "Filesystem source snapshot only, not independently certified Git history. Workspace presence is not fresh-checkout proof; packaging remains blocked."
    },
    public_filename_review: { roots: publicRoots, raster_files_examined: publicFiles.length,
      exclusions: ["storage", "uploads", "hidden files", "runtime/cache/private namespaces"],
      note: "Filename-only search; no remote fetch or assumed equivalence to an unrelated local image." },
    service_photos: servicePhotos,
    product_photos: productPhotos,
    proposed_null_replacements: nullReplacements,
    remote_source_references: remoteSources,
    source_reference_note: "Source/line index is not a target-to-photo assignment or licensing certification. Service historical owner IDs are not reused.",
    approval_gates: [
      "Owner selects Service bytes and permits license/source metadata inclusion.",
      "Owner supplies or authorizes independently reviewed Product bytes with MIME/hash/license/source evidence.",
      "Owner accepts listed NULL differences or selects reviewed local Shop/Specialist/brand/CMS/flag replacements.",
      "Freeze approved asset inventory and exact Service/Product/Stock/Gallery relationships before any packaging.",
      "Separate execution authority must name the sanitized repository and fresh public demo destination."
    ],
    future_packaging_contract: {
      status: "SPECIFICATION_ONLY_NOT_IMPLEMENTED_OR_EXECUTED",
      reject: ["symlinked input or parent", "symlinked destination or parent", "non-regular file",
        "private/runtime upload", "unapproved asset", "missing/mismatched hash or bytes",
        "wrong MIME", "occupied destination even if identical", "path escape", "runtime download"],
      acceptance: "Verify exact approved bytes/MIME/hash and every relationship in a newly sanitized checkout with no access to workspace-only assets or network.",
      excluded: ["database initialization", "identities", "keys", "provider activation", "Stories fixtures"]
    }
  };
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  assert(process.argv.length === 3 && ["--print", "--check"].includes(process.argv[2]),
    "Use --print or --check only; no installation mode exists.");
  const review = buildReview();
  if (process.argv[2] === "--print") console.log(JSON.stringify(review, null, 2));
  else {
    const frozen = JSON.parse(fs.readFileSync(regularFile(root, reviewFile)));
    assert.deepEqual(frozen, review, "Asset review/source drift");
    console.log("PASS: 15 JPEG hashes/MIMEs; 51 Service, 13 Product, 14 Stock and 64 Gallery relationships; review-only gaps preserved.");
    console.log(`Review SHA256 ${sha(fs.readFileSync(regularFile(root, reviewFile)))}`);
  }
}
