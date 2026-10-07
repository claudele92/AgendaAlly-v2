import fs from "node:fs";
import assert from "node:assert/strict";
import test from "node:test";
import { execFileSync } from "node:child_process";
import { deriveApproval, pins, renderApproval, serializeApproval } from "./demo-photo-approval.mjs";

const evidence = () => Object.fromEntries(Object.keys(pins).map(file => [file, fs.readFileSync(file)]));

test("proposal freezes exact Service identities, destinations and all consumer relationships", () => {
  const input = evidence();
  const proposal = deriveApproval(input);
  const inventory = JSON.parse(input["docs/deployment/local-synthetic-demo-asset-review.json"]);
  assert.equal(proposal.status, "OWNER_DECISION_PENDING_NO_COPY_AUTHORITY");
  assert.equal(proposal.service_photos.length, 15);
  assert.deepEqual(proposal.relationship_totals, { services: 51, products: 13, stocks: 14, galleries: 64 });
  proposal.service_photos.forEach((asset, index) => {
    const original = inventory.service_photos[index];
    for (const field of ["file", "sha256", "mime", "bytes", "source", "license_reference", "relationships"])
      assert.deepEqual(asset[field], original[field]);
    assert.equal(asset.inventory_identity.pointer, `/service_photos/${index}`);
    assert.equal(asset.public_demo_path, original.target);
    assert.equal(asset.owner_decision, "PENDING");
    assert.equal(asset.source_license_inclusion_decision, "PENDING");
    assert.equal(asset.proposed_repository_destination,
      `.migration-backup/backend/resources/demo-assets/services/${original.key}.jpg`);
  });
  assert.deepEqual(proposal.proposed_null_replacements, inventory.proposed_null_replacements);
  assert.equal(new Set(proposal.service_photos.map(a => a.proposed_repository_destination)).size, 15);
});

test("all Product dependencies stay unresolved with unchanged Product/Stock/Gallery relationships", () => {
  const input = evidence();
  const proposal = deriveApproval(input);
  const inventory = JSON.parse(input["docs/deployment/local-synthetic-demo-asset-review.json"]);
  assert.equal(proposal.product_dependencies.length, 5);
  proposal.product_dependencies.forEach((asset, index) => {
    for (const field of ["file", "bytes", "mime", "sha256"]) assert.equal(asset[field], null);
    assert.deepEqual(asset.local_filename_candidates, []);
    assert.deepEqual(asset.relationships, inventory.product_photos[index].relationships);
    assert.equal(asset.owner_decision, "PENDING_NO_DOWNLOAD_OR_SUBSTITUTION");
  });
});

test("missing or corrupted frozen evidence fails, without regenerating approval authority", () => {
  for (const file of Object.keys(pins)) {
    const absent = evidence();
    delete absent[file];
    assert.throws(() => deriveApproval(absent), /Missing evidence/);
    const corrupt = evidence();
    corrupt[file] = Buffer.concat([corrupt[file], Buffer.from(" ")]);
    assert.throws(() => deriveApproval(corrupt), /Frozen evidence drift/);
  }
});

test("committed JSON and owner-readable document are exact deterministic proposals", () => {
  const proposal = deriveApproval(evidence());
  const prefix = "docs/deployment/local-synthetic-demo-photo-approval";
  assert.equal(fs.readFileSync(`${prefix}.json`, "utf8"), serializeApproval(proposal));
  assert.equal(fs.readFileSync(`${prefix}.md`, "utf8"), renderApproval(proposal));
});

test("execution modes are rejected before any source read or write", () => {
  for (const mode of ["--install", "--copy", "--approve", "--initialize", "--download"])
    assert.throws(() => execFileSync(process.execPath, ["scripts/database/demo-photo-approval.mjs", mode],
      { stdio: "pipe" }), error => error.status === 1 && error.stderr.toString().includes("no installation/copy/approval mode exists"));
});
