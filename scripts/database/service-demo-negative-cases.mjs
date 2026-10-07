import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import assert from "node:assert/strict";
import {
  APPROVAL_FILE, OWNER_FILE, PACKAGE, REPOSITORY, readRegular, safePath,
  preflightSelected, verifyPackage, validateJpeg, jsonBytes
} from "./service-demo-assets.mjs";

// Every JPEG fixture comes from the already verified fresh checkout, never
// from attached_assets, runtime uploads or a network. Cases are disposable.
export function negativeCases(checkout, contract) {
  const results = [];
  const assets = contract.proposal.service_photos;
  const last = assets.at(-1);
  function sourceCase(name, edit, expected) {
    const base = fs.mkdtempSync(path.join(os.tmpdir(), "service-demo-negative-"));
    const repo = path.join(base, REPOSITORY);
    try {
      fs.mkdirSync(path.join(repo, path.dirname(PACKAGE)), { recursive: true });
      for (const relative of [APPROVAL_FILE, OWNER_FILE]) {
        const destination = path.join(base, relative);
        fs.mkdirSync(path.dirname(destination), { recursive: true });
        fs.writeFileSync(destination, readRegular(checkout, relative), { flag: "wx" });
      }
      for (const asset of assets) {
        const destination = path.join(base, asset.file);
        fs.mkdirSync(path.dirname(destination), { recursive: true });
        fs.writeFileSync(destination, readRegular(checkout, asset.proposed_repository_destination), { flag: "wx" });
      }
      const packagePath = path.join(repo, PACKAGE);
      const effectiveBase = edit(base, repo, packagePath) || base;
      assert.throws(() => preflightSelected(effectiveBase), expected, name);
      // If an occupied/symlink destination was intentionally supplied, retain
      // it. For all failed input cases, no destination may have been created.
      if (!name.startsWith("occupied") && !name.startsWith("destination"))
        assert.throws(() => fs.lstatSync(packagePath), error => error.code === "ENOENT");
      results.push({ case: name, status: "PASS_FAIL_CLOSED_NO_COPY" });
    } finally { fs.rmSync(base, { recursive: true, force: true }); }
  }
  sourceCase("last-input-missing-complete-preflight", base => {
    fs.unlinkSync(path.join(base, last.file));
  }, /Missing input/);
  sourceCase("last-input-corruption-same-size", base => {
    const filename = path.join(base, last.file);
    const bytes = fs.readFileSync(filename);
    bytes[bytes.length >> 1] ^= 1;
    fs.writeFileSync(filename, bytes);
  }, /Hash mismatch/);
  sourceCase("last-input-size-drift", base => {
    fs.appendFileSync(path.join(base, last.file), "x");
  }, /Byte-size mismatch/);
  sourceCase("input-file-symlink", base => {
    const filename = path.join(base, last.file);
    fs.renameSync(filename, `${filename}.actual`);
    fs.symlinkSync(`${filename}.actual`, filename);
  }, /Symlink refused/);
  sourceCase("input-ancestor-symlink", base => {
    const directory = path.join(base, "attached_assets/service-photos");
    fs.renameSync(directory, `${directory}-actual`);
    fs.symlinkSync(`${directory}-actual`, directory);
  }, /Symlink refused/);
  sourceCase("non-regular-input-directory", base => {
    fs.unlinkSync(path.join(base, last.file));
    fs.mkdirSync(path.join(base, last.file));
  }, /Non-regular input/);
  sourceCase("destination-ancestor-symlink", (base, repo) => {
    const parent = path.join(repo, path.dirname(PACKAGE));
    fs.renameSync(parent, `${parent}-actual`);
    fs.symlinkSync(`${parent}-actual`, parent);
  }, /Symlink refused/);
  sourceCase("destination-dangling-symlink", (_base, _repo, target) => {
    fs.symlinkSync(`${target}-missing`, target);
  }, /Symlink refused/);
  sourceCase("destination-root-alias-symlink", base => {
    const alias = path.join(base, "root-alias");
    fs.symlinkSync(base, alias);
    return alias;
  }, /Symlink refused/);
  sourceCase("destination-non-directory-ancestor", (_base, repo) => {
    const parent = path.join(repo, path.dirname(PACKAGE));
    fs.rmdirSync(parent);
    fs.writeFileSync(parent, "not a directory");
  }, /Non-directory ancestor/);
  sourceCase("occupied-empty-directory", (_base, _repo, target) => {
    fs.mkdirSync(target);
  }, /Occupied destination/);
  sourceCase("occupied-regular-file", (_base, _repo, target) => {
    fs.writeFileSync(target, "occupied");
  }, /Occupied destination/);
  sourceCase("occupied-identical-approved-package", (_base, _repo, target) => {
    fs.mkdirSync(path.join(target, "services"), { recursive: true });
    for (const asset of assets)
      fs.writeFileSync(path.join(target, "services", `${asset.inventory_identity.key}.jpg`),
        readRegular(checkout, asset.proposed_repository_destination), { flag: "wx" });
    fs.writeFileSync(path.join(target, "provenance.json"),
      readRegular(checkout, `${PACKAGE}/provenance.json`), { flag: "wx" });
  }, /Occupied destination/);
  for (const [name, edit] of [
    ["unapproved-extra-asset", proposal => proposal.service_photos.push(proposal.service_photos[0])],
    ["private-runtime-input-selection", proposal => { proposal.service_photos[0].file = ".migration-backup/backend/storage/private.jpg"; }],
    ["network-input-selection", proposal => { proposal.service_photos[0].file = "https://example.invalid/image.jpg"; }],
    ["path-escape-selection", proposal => { proposal.service_photos[0].file = "../escape.jpg"; }]
  ]) sourceCase(name, base => {
    const proposal = JSON.parse(readRegular(base, APPROVAL_FILE));
    edit(proposal);
    fs.writeFileSync(path.join(base, APPROVAL_FILE), jsonBytes(proposal));
  }, /Approval hash mismatch/);

  const first = assets[0];
  const firstBytes = readRegular(checkout, first.proposed_repository_destination);
  assert.throws(() => validateJpeg(checkout, first.proposed_repository_destination,
    firstBytes, { ...first, mime: "text/html" }), /MIME mismatch/);
  results.push({ case: "MIME-mismatch", status: "PASS_FAIL_CLOSED_NO_COPY" });
  for (const relative of ["../escape", "/absolute", "services/../escape", "services\\escape", "services//escape"])
    assert.throws(() => safePath(checkout, relative), /Path escape/);
  results.push({ case: "raw-path-escapes", status: "PASS_FAIL_CLOSED_NO_COPY" });

  function checkoutCase(name, edit, expected) {
    const base = fs.mkdtempSync(path.join(os.tmpdir(), "service-demo-checkout-negative-"));
    try {
      const target = path.join(base, PACKAGE);
      fs.mkdirSync(path.join(target, "services"), { recursive: true });
      for (const asset of assets)
        fs.writeFileSync(path.join(base, asset.proposed_repository_destination),
          readRegular(checkout, asset.proposed_repository_destination), { flag: "wx" });
      fs.writeFileSync(path.join(target, "provenance.json"), readRegular(checkout, `${PACKAGE}/provenance.json`), { flag: "wx" });
      edit(base, target);
      assert.throws(() => verifyPackage(base, contract), expected, name);
      results.push({ case: name, status: "PASS_CHECKOUT_REJECTED" });
    } finally { fs.rmSync(base, { recursive: true, force: true }); }
  }
  checkoutCase("checkout-corruption", base => {
    const filename = path.join(base, first.proposed_repository_destination);
    const bytes = fs.readFileSync(filename); bytes[100] ^= 1; fs.writeFileSync(filename, bytes);
  }, /Hash mismatch/);
  checkoutCase("checkout-missing", base => fs.unlinkSync(path.join(base, first.proposed_repository_destination)), /Missing or unapproved image/);
  checkoutCase("checkout-leaf-symlink", base => {
    const filename = path.join(base, first.proposed_repository_destination);
    fs.unlinkSync(filename); fs.symlinkSync(path.join(checkout, first.proposed_repository_destination), filename);
  }, /Symlink refused/);
  checkoutCase("checkout-parent-symlink", (_base, target) => {
    fs.renameSync(path.join(target, "services"), path.join(target, "actual"));
    fs.symlinkSync(path.join(target, "actual"), path.join(target, "services"));
    // Remove the extra name from the package by placing it outside the package.
    fs.renameSync(path.join(target, "actual"), `${target}-actual`);
    fs.unlinkSync(path.join(target, "services"));
    fs.symlinkSync(`${target}-actual`, path.join(target, "services"));
  }, /Symlink refused/);
  checkoutCase("checkout-provenance-drift", (_base, target) => fs.appendFileSync(path.join(target, "provenance.json"), " "), /Provenance mismatch/);
  checkoutCase("checkout-provenance-symlink", (_base, target) => {
    const filename = path.join(target, "provenance.json");
    fs.unlinkSync(filename);
    fs.symlinkSync(path.join(checkout, `${PACKAGE}/provenance.json`), filename);
  }, /Symlink refused/);
  checkoutCase("checkout-unapproved-image", (_base, target) => fs.writeFileSync(path.join(target, "services/unapproved.jpg"), firstBytes), /Missing or unapproved image/);
  checkoutCase("checkout-Product-directory", (_base, target) => fs.mkdirSync(path.join(target, "products")), /Unapproved package entry/);
  checkoutCase("checkout-private-runtime-entry", (_base, target) => fs.mkdirSync(path.join(target, "uploads")), /Unapproved package entry/);
  return results;
}
