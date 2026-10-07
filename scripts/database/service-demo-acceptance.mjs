// Bounded asset-gate acceptance, not certification of full application history.
import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import assert from "node:assert/strict";
import { execFileSync } from "node:child_process";
import { fileURLToPath } from "node:url";
import {
  APPROVAL_FILE, OWNER_FILE, PACKAGE, REPOSITORY, loadContract, readRegular,
  verifyPackage, safePath, sha256, jsonBytes
} from "./service-demo-assets.mjs";
import { negativeCases } from "./service-demo-negative-cases.mjs";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
assert(process.argv.length === 3 && process.argv[2] === "--check",
  "Use --check only; no remote, runtime installer, database or download mode.");
const contract = loadContract(root);
const source = safePath(root, REPOSITORY, "directory");
const sourceReceipt = verifyPackage(source, contract);
const support = [APPROVAL_FILE, OWNER_FILE,
  "scripts/database/service-demo-assets.mjs",
  "scripts/database/service-demo-offline.cjs",
  "scripts/database/service-demo-fresh-verify.mjs"];
const selection = [
  ...sourceReceipt.files.map(file => ({ path: file.path, bytes: readRegular(source, file.path) })),
  ...support.map(file => ({ path: file, bytes: readRegular(root, file) }))
];
// Complete allowlist preflight before copying any seed file.
assert.equal(selection.length, 21);
assert.equal(new Set(selection.map(file => file.path)).size, 21);
assert.equal(selection.filter(file => /\.(jpe?g|png|webp)$/i.test(file.path)).length, 15);
const temp = fs.mkdtempSync(path.join(os.tmpdir(), "agendaally-service-checkout-"));
try {
  const seed = path.join(temp, "seed");
  const checkout = path.join(temp, "checkout");
  fs.mkdirSync(seed);
  for (const file of selection) {
    const target = safePath(seed, file.path, "absent");
    fs.mkdirSync(path.dirname(target), { recursive: true });
    fs.writeFileSync(target, file.bytes, { flag: "wx" });
  }
  const gitEnv = {
    PATH: process.env.PATH, HOME: temp,
    GIT_CONFIG_NOSYSTEM: "1", GIT_CONFIG_GLOBAL: "/dev/null",
    GIT_AUTHOR_NAME: "Synthetic asset acceptance", GIT_AUTHOR_EMAIL: "asset-acceptance@example.invalid",
    GIT_COMMITTER_NAME: "Synthetic asset acceptance", GIT_COMMITTER_EMAIL: "asset-acceptance@example.invalid",
    GIT_AUTHOR_DATE: "2026-10-07T00:00:00Z", GIT_COMMITTER_DATE: "2026-10-07T00:00:00Z"
  };
  const git = args => execFileSync("git", ["-c", "core.hooksPath=/dev/null", ...args],
    { env: gitEnv, encoding: "utf8", stdio: ["ignore", "pipe", "pipe"] }).trim();
  git(["init", "--initial-branch=asset-acceptance", seed]);
  git(["-C", seed, "add", "--", "."]);
  git(["-C", seed, "commit", "-m", "Approved Service asset acceptance seed"]);
  const seedTree = git(["-C", seed, "rev-parse", "HEAD^{tree}"]);
  git(["clone", "--no-local", "--no-hardlinks", "--", seed, checkout]);
  assert.equal(git(["-C", checkout, "rev-parse", "--show-toplevel"]), checkout);
  assert.equal(git(["-C", checkout, "rev-parse", "HEAD^{tree}"]), seedTree);
  assert.equal(git(["-C", checkout, "status", "--porcelain"]), "");
  const checkoutFiles = git(["-C", checkout, "ls-files"]).split("\n");
  assert.deepEqual(checkoutFiles.sort(), selection.map(file => file.path).sort());
  assert.throws(() => fs.lstatSync(path.join(checkout, "attached_assets")), error => error.code === "ENOENT");
  assert.throws(() => fs.lstatSync(path.join(checkout, ".git/objects/info/alternates")), error => error.code === "ENOENT");
  // Remove the seed before verification: neither it nor its original media is
  // needed by the checkout. Clone has its own objects; no shared/hardlinked seed.
  fs.rmSync(seed, { recursive: true });
  const verification = JSON.parse(execFileSync(process.execPath, [
    "--permission", "--allow-fs-read=/tmp", "--allow-child-process",
    "--require", path.join(checkout, "scripts/database/service-demo-offline.cjs"),
    path.join(checkout, "scripts/database/service-demo-fresh-verify.mjs")
  ], {
    cwd: checkout,
    env: {
      PATH: process.env.PATH, HOME: checkout, TMPDIR: temp,
      AGENDAALLY_ASSET_CHECKOUT: checkout,
      AGENDAALLY_DENIED_ORIGINAL_INPUT: path.join(root, contract.proposal.service_photos[0].file)
    }, encoding: "utf8", stdio: ["ignore", "pipe", "pipe"]
  }));
  assert.equal(verification.package_sha256, sourceReceipt.package_sha256);
  assert.equal(verification.provenance_sha256, sourceReceipt.provenance_sha256);
  const negatives = negativeCases(checkout, loadContract(checkout));
  // No negative test modifies the real package or the accepted checkout.
  assert.deepEqual(verifyPackage(source, contract), sourceReceipt);
  assert.equal(git(["-C", checkout, "status", "--porcelain"]), "");
  const receipt = {
    status: "PASS_BOUNDED_SERVICE_ASSET_FRESH_CHECKOUT",
    scope: "New local Git history and independent clone of the 16 approved package files plus five frozen-decision/verification files. Asset gate only; not a full-app checkout or certification of the pre-existing sanitized snapshot's Git history.",
    source_repository: REPOSITORY,
    fresh_checkout: {
      kind: "independent local Git clone --no-local --no-hardlinks",
      committed_tree: seedTree,
      tracked_files: checkoutFiles,
      tracked_file_count: checkoutFiles.length,
      raster_file_count: 15,
      shared_object_alternates: false,
      seed_removed_before_verification: true,
      original_attached_assets_present: false,
      network_remote_contacted: false,
      verification
    },
    source_package: sourceReceipt,
    negative_cases: negatives,
    preserved_relationships: { services: 51, products: 13, stocks: 14, galleries: 64 },
    approved_packaged_relationships: { services: 51, galleries: 51 },
    product_dependency_status: "ALL_FIVE_BLOCKED_NO_PRODUCT_IMAGE_PACKAGED",
    null_policy: "All hash-bound proposed NULLs preserved as proposals; no photo-complete/visual parity acceptance.",
    runtime_installation: "NOT_AUTHORIZED_NOT_PERFORMED",
    database_identity_key_provider_SMTP_worker_finance_GitHub_VPS_Stories_production_actions: false,
    verification_tool_hashes: Object.fromEntries([
      ...support, "scripts/database/service-demo-acceptance.mjs",
      "scripts/database/service-demo-negative-cases.mjs"
    ].map(file => [file, sha256(readRegular(root, file))]))
  };
  process.stdout.write(jsonBytes(receipt));
} finally { fs.rmSync(temp, { recursive: true, force: true }); }
