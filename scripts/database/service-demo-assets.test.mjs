import fs from "node:fs";
import path from "node:path";
import os from "node:os";
import assert from "node:assert/strict";
import test from "node:test";
import { execFileSync } from "node:child_process";
import {
  APPROVAL_FILE, OWNER_FILE, REPOSITORY, PACKAGE, loadContract,
  approvedContract, safePath, readRegular, verifyPackage, provenance, jsonBytes
} from "./service-demo-assets.mjs";
import { negativeCases } from "./service-demo-negative-cases.mjs";

const root = process.cwd();
const contract = loadContract(root);

test("owner-authorized package is exactly 15 JPEGs and selected provenance, with unchanged relationships", () => {
  const repository = safePath(root, REPOSITORY, "directory");
  const receipt = verifyPackage(repository, contract);
  assert.equal(receipt.files.length, 16);
  assert.equal(receipt.service_relationships, 51);
  assert.equal(receipt.service_gallery_relationships, 51);
  assert.equal(receipt.product_images, 0);
  assert.equal(receipt.runtime_installed, false);
  assert.deepEqual(receipt.preserved_review_relationship_totals,
    { services: 51, products: 13, stocks: 14, galleries: 64 });
  const selected = provenance(contract);
  assert.equal(selected.assets.length, 15);
  assert(!JSON.stringify(selected).includes("owner_id"));
  assert.equal(selected.runtime_installation, "NOT AUTHORIZED");
  assert.equal(selected.products, "BLOCKED_NOT_PACKAGED");
});

test("modified owner authority or proposal cannot authorize packaging", () => {
  const approval = readRegular(root, APPROVAL_FILE);
  const owner = readRegular(root, OWNER_FILE);
  assert.throws(() => approvedContract(Buffer.concat([approval, Buffer.from(" ")]), owner), /Approval hash mismatch/);
  assert.throws(() => approvedContract(approval, Buffer.concat([owner, Buffer.from(" ")])), /Owner decision hash mismatch/);
});

test("actual repeated packaging rejects the identical occupied destination without mutation", () => {
  const repository = safePath(root, REPOSITORY, "directory");
  const before = verifyPackage(repository, contract);
  assert.throws(() => execFileSync(process.execPath, ["scripts/database/service-demo-assets.mjs", "--package"],
    { encoding: "utf8", stdio: "pipe" }),
  error => error.status === 1 && error.stderr.includes("Occupied destination"));
  assert.deepEqual(verifyPackage(repository, contract), before);
});

test("negative cases exercise the shared complete-selection preflight and checkout verification", () => {
  // Build only the frozen decision + exact approved package fixture, so the
  // negative suite cannot need historical workspace input media.
  const repository = safePath(root, REPOSITORY, "directory");
  const receipt = verifyPackage(repository, contract);
  const temp = fs.mkdtempSync(path.join(os.tmpdir(), "service-demo-unit-"));
  try {
    for (const relative of [APPROVAL_FILE, OWNER_FILE]) {
      const destination = path.join(temp, relative);
      fs.mkdirSync(path.dirname(destination), { recursive: true });
      fs.writeFileSync(destination, readRegular(root, relative), { flag: "wx" });
    }
    for (const file of receipt.files) {
      const destination = path.join(temp, file.path);
      fs.mkdirSync(path.dirname(destination), { recursive: true });
      fs.writeFileSync(destination, readRegular(repository, file.path), { flag: "wx" });
    }
    const results = negativeCases(temp, loadContract(temp));
    assert.equal(results.length, 28);
    assert(results.every(r => r.status.startsWith("PASS_")));
  } finally { fs.rmSync(temp, { recursive: true, force: true }); }
});

test("runtime installation, alternate roots, download and selection modes are rejected", () => {
  for (const mode of ["--install", "--download", "--approve-all", "--copy-private"])
    assert.throws(() => execFileSync(process.execPath, ["scripts/database/service-demo-assets.mjs", mode],
      { stdio: "pipe" }),
    error => error.status === 1 && error.stderr.toString().includes("No runtime installation/download mode"));
  assert.throws(() => execFileSync(process.execPath,
    ["scripts/database/service-demo-assets.mjs", "--package", "/alternate/destination"], { stdio: "pipe" }));
});

test("receipt and provenance bind the owner decision and approval, not historical pending statuses", () => {
  const recorded = JSON.parse(fs.readFileSync("docs/deployment/local-synthetic-service-photo-packaging-receipt.json"));
  const actual = verifyPackage(safePath(root, REPOSITORY, "directory"), contract);
  assert.deepEqual(recorded.source_package, actual);
  assert.equal(recorded.negative_cases.length, 28);
  assert.equal(recorded.fresh_checkout.tracked_file_count, 21);
  assert.equal(recorded.fresh_checkout.verification.isolation.workspace_asset_read, "DENIED_BY_NODE_PERMISSION");
  assert.equal(recorded.fresh_checkout.verification.isolation.verification_writes, "DENIED_BY_NODE_PERMISSION");
  assert.equal(recorded.fresh_checkout.verification.provenance_sha256, actual.provenance_sha256);
  assert(readRegular(safePath(root, REPOSITORY, "directory"), `${PACKAGE}/provenance.json`).equals(jsonBytes(provenance(contract))));
});
