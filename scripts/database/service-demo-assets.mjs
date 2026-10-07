// Approved SOURCE packaging only. No network, runtime installer or database.
import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import assert from "node:assert/strict";
import { execFileSync } from "node:child_process";
import { fileURLToPath } from "node:url";

export const APPROVAL_SHA = "e3da6988652481194b281e231e1da8ce8f1608c4c26b3b13035b52cd9ebe3a31";
export const OWNER_SHA = "ce64984255b623a06ad32502aafd8b92c61be653edfc07eda04324f6e4ce2694";
export const APPROVAL_FILE = "docs/deployment/local-synthetic-demo-photo-approval.json";
export const OWNER_FILE = "docs/deployment/local-synthetic-service-photo-owner-decision.json";
export const REPOSITORY = ".local/agendaally-clean-repository";
export const PACKAGE = ".migration-backup/backend/resources/demo-assets";
export const sha256 = bytes => crypto.createHash("sha256").update(bytes).digest("hex");
export const jsonBytes = value => Buffer.from(`${JSON.stringify(value, null, 2)}\n`);

function parts(relative) {
  assert(typeof relative === "string" && relative.length && !path.isAbsolute(relative)
    && !relative.includes("\\") && !relative.includes("\0"), "Path escape refused");
  const values = relative.split("/");
  assert(values.every(value => value && value !== "." && value !== ".."), "Path escape refused");
  return values;
}

// Check every ancestor, including ancestors of the supplied root. Never use
// existsSync (which follows symlinks and treats a dangling symlink as absent).
export function safePath(base, relative, kind = "file") {
  parts(relative);
  assert(path.isAbsolute(base), "Absolute root required");
  const filename = path.join(base, relative);
  const components = filename.split(path.sep).filter(Boolean);
  let current = path.parse(filename).root;
  let missing = false;
  for (const [index, component] of components.entries()) {
    current = path.join(current, component);
    let stat;
    try { stat = fs.lstatSync(current); }
    catch (error) {
      if (error.code !== "ENOENT") throw error;
      assert(kind === "absent", `Missing input: ${current}`);
      missing = true;
      continue;
    }
    assert(!missing, "Ancestor appeared during validation");
    assert(!stat.isSymbolicLink(), `Symlink refused: ${current}`);
    if (index !== components.length - 1) assert(stat.isDirectory(), `Non-directory ancestor: ${current}`);
    else {
      if (kind === "absent") assert.fail(`Occupied destination: ${current}`);
      assert(kind === "directory" ? stat.isDirectory() : stat.isFile(), `Non-regular input: ${current}`);
    }
  }
  return filename;
}

export function readRegular(base, relative) {
  const filename = safePath(base, relative);
  const before = fs.lstatSync(filename);
  const fd = fs.openSync(filename, fs.constants.O_RDONLY | fs.constants.O_NOFOLLOW);
  try {
    const opened = fs.fstatSync(fd);
    assert(opened.isFile() && opened.dev === before.dev && opened.ino === before.ino, "Input changed");
    const bytes = fs.readFileSync(fd);
    const after = fs.fstatSync(fd);
    assert.equal(after.size, bytes.length, "Input size changed");
    safePath(base, relative);
    return bytes;
  } finally { fs.closeSync(fd); }
}

export function approvedContract(approvalBytes, ownerBytes) {
  assert.equal(sha256(approvalBytes), APPROVAL_SHA, "Approval hash mismatch / unapproved selection");
  assert.equal(sha256(ownerBytes), OWNER_SHA, "Owner decision hash mismatch");
  const proposal = JSON.parse(approvalBytes);
  const owner = JSON.parse(ownerBytes);
  assert.equal(owner.status, "OWNER_APPROVED_SERVICE_SOURCE_PACKAGING_ONLY");
  assert.equal(owner.approval_json_sha256, APPROVAL_SHA);
  assert.equal(owner.sanitized_repository, REPOSITORY);
  assert.equal(owner.repository_package_root, PACKAGE);
  assert.deepEqual(owner.frozen_evidence, Object.fromEntries(proposal.frozen_evidence.map(r => [r.file, r.sha256])));
  assert.equal(proposal.service_photos.length, 15);
  assert.deepEqual(owner.approved_service_inventory_identities, proposal.service_photos.map(a => a.inventory_identity.pointer));
  for (const asset of proposal.service_photos) {
    assert(/^[a-z0-9-]+$/.test(asset.inventory_identity.key), "Unapproved asset key");
    assert.equal(asset.file, `attached_assets/service-photos/${asset.inventory_identity.key}.jpg`, "Private/runtime input refused");
    assert.equal(asset.proposed_repository_destination, `${PACKAGE}/services/${asset.inventory_identity.key}.jpg`);
    assert.equal(asset.mime, "image/jpeg");
    assert.equal(asset.public_demo_path, `/storage/portable-demo/services/${asset.inventory_identity.key}.jpg`);
    assert(asset.relationships.every(r => ["services", "galleries"].includes(r.table)));
  }
  const relationships = proposal.service_photos.flatMap(a => a.relationships);
  assert.equal(relationships.filter(r => r.table === "services").length, 51);
  assert.equal(relationships.filter(r => r.table === "galleries").length, 51);
  assert.deepEqual(proposal.relationship_totals, { services: 51, products: 13, stocks: 14, galleries: 64 });
  assert.equal(proposal.product_dependencies.length, 5);
  for (const asset of proposal.product_dependencies)
    for (const field of ["file", "bytes", "mime", "sha256"]) assert.equal(asset[field], null);
  return { proposal, owner };
}

export function provenance(contract) {
  return {
    format: "agendaally-approved-service-demo-assets-v1",
    authority: "OWNER_APPROVED_SERVICE_SOURCE_PACKAGING_ONLY",
    approval_json_sha256: APPROVAL_SHA,
    owner_decision_sha256: OWNER_SHA,
    frozen_evidence: contract.owner.frozen_evidence,
    repository_package_root: PACKAGE,
    approved_use: contract.owner.authorized_use,
    restrictions: contract.owner.restrictions,
    attribution: contract.owner.attribution,
    license_authority: contract.owner.license_authority,
    public_namespace: contract.owner.future_public_namespace,
    runtime_installation: "NOT AUTHORIZED",
    products: "BLOCKED_NOT_PACKAGED",
    assets: contract.proposal.service_photos.map(asset => ({
      inventory_identity: asset.inventory_identity,
      path: `services/${asset.inventory_identity.key}.jpg`,
      bytes: asset.bytes, mime: asset.mime, sha256: asset.sha256,
      source: asset.source, license_reference: asset.license_reference,
      recorded_redistribution_basis: asset.recorded_redistribution_basis,
      public_demo_path: asset.public_demo_path,
      relationships: asset.relationships
    }))
  };
}

export function validateJpeg(base, relative, bytes, asset) {
  assert.equal(bytes.length, asset.bytes, `Byte-size mismatch: ${relative}`);
  assert.equal(sha256(bytes), asset.sha256, `Hash mismatch: ${relative}`);
  assert.equal(asset.mime, "image/jpeg", "MIME mismatch");
  assert(bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff, "MIME mismatch / not JPEG");
  const filename = safePath(base, relative);
  const mime = execFileSync("file", ["--brief", "--mime-type", "--", filename], { encoding: "utf8" }).trim();
  assert.equal(mime, asset.mime, `MIME mismatch: ${relative}`);
}

export function loadContract(base) {
  return approvedContract(readRegular(base, APPROVAL_FILE), readRegular(base, OWNER_FILE));
}

function assetPlan(base, contract) {
  const repository = safePath(base, REPOSITORY, "directory");
  safePath(repository, PACKAGE, "absent");
  safePath(repository, `${PACKAGE}/provenance.json`, "absent");
  const files = contract.proposal.service_photos.map(asset => {
    const bytes = readRegular(base, asset.file);
    validateJpeg(base, asset.file, bytes, asset);
    safePath(repository, asset.proposed_repository_destination, "absent");
    return { relative: asset.proposed_repository_destination, bytes };
  });
  files.push({ relative: `${PACKAGE}/provenance.json`, bytes: jsonBytes(provenance(contract)) });
  assert.equal(files.length, 16);
  return { contract, repository, files };
}

// The same asset/destination preflight can be exercised from a fresh checkout
// using only the hash-bound decision documents and approved packaged bytes.
export function preflightSelected(base) {
  return assetPlan(base, loadContract(base));
}

export function preflight(base) {
  const contract = loadContract(base);
  for (const [file, digest] of Object.entries(contract.owner.frozen_evidence))
    assert.equal(sha256(readRegular(base, file)), digest, `Frozen evidence mismatch: ${file}`);
  return assetPlan(base, contract);
}

// Exclusive writes, with a second destination check. Roll back only objects
// created by this invocation, never remove a pre-existing/foreign destination.
export function packageApproved(base) {
  const plan = preflight(base); // complete selection before mkdir/open/copy
  const createdFiles = [];
  const createdDirs = [];
  function mkdir(relative) {
    const target = safePath(plan.repository, relative, "absent");
    fs.mkdirSync(target);
    const stat = fs.lstatSync(target);
    createdDirs.push({ target, ino: stat.ino, dev: stat.dev });
  }
  try {
    // resources already belongs to the approved sanitized source snapshot.
    safePath(plan.repository, path.dirname(PACKAGE), "directory");
    mkdir(PACKAGE);
    mkdir(`${PACKAGE}/services`);
    for (const file of plan.files) {
      const target = safePath(plan.repository, file.relative, "absent");
      safePath(plan.repository, path.dirname(file.relative), "directory");
      const fd = fs.openSync(target, fs.constants.O_WRONLY | fs.constants.O_CREAT |
        fs.constants.O_EXCL | fs.constants.O_NOFOLLOW, 0o644);
      const stat = fs.fstatSync(fd);
      createdFiles.push({ target, ino: stat.ino, dev: stat.dev });
      try { fs.writeFileSync(fd, file.bytes); fs.fsyncSync(fd); }
      finally { fs.closeSync(fd); }
    }
    return verifyPackage(plan.repository, plan.contract);
  } catch (error) {
    for (const item of createdFiles.reverse()) {
      const stat = fs.lstatSync(item.target);
      if (stat.isFile() && stat.ino === item.ino && stat.dev === item.dev) fs.unlinkSync(item.target);
    }
    for (const item of createdDirs.reverse()) {
      const stat = fs.lstatSync(item.target);
      if (stat.isDirectory() && stat.ino === item.ino && stat.dev === item.dev) {
        try { fs.rmdirSync(item.target); }
        catch (cleanupError) { if (cleanupError.code !== "ENOTEMPTY") throw cleanupError; }
      }
    }
    throw error;
  }
}

export function verifyPackage(repository, contract) {
  safePath(repository, PACKAGE, "directory");
  assert.deepEqual(fs.readdirSync(path.join(repository, PACKAGE)).sort(), ["provenance.json", "services"], "Unapproved package entry");
  safePath(repository, `${PACKAGE}/services`, "directory");
  const expected = provenance(contract);
  const expectedProvenance = jsonBytes(expected);
  const actualProvenance = readRegular(repository, `${PACKAGE}/provenance.json`);
  assert(actualProvenance.equals(expectedProvenance), "Provenance mismatch");
  assert.deepEqual(fs.readdirSync(path.join(repository, `${PACKAGE}/services`)).sort(),
    expected.assets.map(a => path.basename(a.path)).sort(), "Missing or unapproved image");
  const files = [];
  for (const asset of expected.assets) {
    const relative = `${PACKAGE}/${asset.path}`;
    const bytes = readRegular(repository, relative);
    validateJpeg(repository, relative, bytes, asset);
    files.push({ path: relative, bytes: bytes.length, mime: asset.mime, sha256: sha256(bytes) });
  }
  files.push({ path: `${PACKAGE}/provenance.json`, bytes: actualProvenance.length, sha256: sha256(actualProvenance) });
  return {
    status: "PASS_APPROVED_SERVICE_SOURCE_PACKAGE",
    approval_json_sha256: APPROVAL_SHA, owner_decision_sha256: OWNER_SHA,
    provenance_sha256: sha256(actualProvenance),
    // Canonical ordered (path, byte-count, hash) manifest, not a tar hash.
    package_sha256: sha256(jsonBytes(files.map(({ path, bytes, sha256 }) => ({ path, bytes, sha256 })))),
    files, service_relationships: 51, service_gallery_relationships: 51,
    product_images: 0, unapproved_images: 0,
    preserved_review_relationship_totals: contract.proposal.relationship_totals,
    proposed_nulls_sha256: sha256(jsonBytes(contract.proposal.proposed_null_replacements)),
    runtime_installed: false, database_actions: false, network_actions: false
  };
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  const base = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
  assert(process.argv.length === 3 && ["--preflight", "--package", "--verify"].includes(process.argv[2]),
    "Use --preflight, --package or --verify only. No runtime installation/download mode.");
  const mode = process.argv[2];
  const result = mode === "--package" ? packageApproved(base)
    : mode === "--verify" ? verifyPackage(safePath(base, REPOSITORY, "directory"), loadContract(base))
    : { status: "PASS_COMPLETE_PREFLIGHT_NO_COPY", entries: preflight(base).files.length };
  console.log(JSON.stringify(result, null, 2));
}
