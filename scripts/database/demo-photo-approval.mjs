// Review documents only: no image copying, network, database or approval mode.
import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import assert from "node:assert/strict";
import { fileURLToPath } from "node:url";
import { buildReview, regularFile } from "./demo-asset-review.mjs";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
const output = "docs/deployment/local-synthetic-demo-photo-approval";
export const pins = Object.freeze({
  "docs/deployment/local-synthetic-portable-baseline.json": "7aba06496ffb8efd3aba99bc2599773245c196d453d902c9bcb370a92314335c",
  "docs/deployment/local-synthetic-demo-asset-review.json": "354e00c29323e66dd235bcd2e67627ade92bc1820d4851e0e7d4e18d468880a3",
  "docs/deployment/local-synthetic-demo-asset-review.md": "b255895e27deedd501a0eac22ffa7f4af52d7dc0b2fb1e557683c2843f19eac1",
  "attached_assets/service-photos/sources.json": "abb43fcd6b6bfefb153a8898177a22459ab4424b38ac25c498b74a89af1b1b7e"
});
const sha = bytes => crypto.createHash("sha256").update(bytes).digest("hex");
const repoRoot = ".local/agendaally-clean-repository";
const packageRoot = ".migration-backup/backend/resources/demo-assets";

export function deriveApproval(evidence) {
  for (const [file, hash] of Object.entries(pins)) {
    assert(Buffer.isBuffer(evidence[file]), `Missing evidence: ${file}`);
    assert.equal(sha(evidence[file]), hash, `Frozen evidence drift: ${file}`);
  }
  const baseline = JSON.parse(evidence[Object.keys(pins)[0]]);
  const inventory = JSON.parse(evidence[Object.keys(pins)[1]]);
  const metadata = JSON.parse(evidence[Object.keys(pins)[3]]);
  assert.equal(inventory.status, "DRAFT_REVIEW_ONLY_NO_PACKAGING_AUTHORITY");
  assert.equal(baseline.status, "DRAFT_REQUIRES_OWNER_APPROVAL");
  const count = table => [...inventory.service_photos, ...inventory.product_photos]
    .flatMap(asset => asset.relationships).filter(row => row.table === table).length;
  const totals = Object.fromEntries(["services", "products", "stocks", "galleries"].map(t => [t, count(t)]));
  assert.deepEqual(totals, { services: 51, products: 13, stocks: 14, galleries: 64 });
  return {
    status: "OWNER_DECISION_PENDING_NO_COPY_AUTHORITY",
    scope: "Bounded owner review only. No previous owner-approved photo packaging record exists.",
    frozen_evidence: Object.entries(pins).map(([file, sha256]) => ({ file, sha256 })),
    proposed_location: {
      sanitized_repository: repoRoot,
      repository_relative_package_root: packageRoot,
      provenance_destination: `${packageRoot}/provenance.json`,
      provenance_selection: "Only the 15 listed Service entries and their approved source/license evidence; never copy the entire 23-image historical metadata or historical owner/target arrays.",
      public_service_namespace: "/storage/portable-demo/services/",
      public_product_namespace: "/storage/portable-demo/products/",
      physical_public_destination: "Not selected or authorized. Public URLs do not authorize an existing storage tree.",
      status: "PROPOSAL_ONLY_NOT_CREATED"
    },
    relationship_totals: totals,
    service_photos: inventory.service_photos.map((asset, index) => ({
      inventory_identity: { file: Object.keys(pins)[1], pointer: `/service_photos/${index}`, key: asset.key },
      file: asset.file, sha256: asset.sha256, mime: asset.mime, bytes: asset.bytes,
      source: asset.source, license_reference: asset.license_reference,
      recorded_redistribution_basis: metadata.license,
      rights_status: asset.licensing_status,
      evidence_limit: "Recorded Pexels reference and historical demo-use statement only; no independently verified redistribution/person/mark rights or owner approval. Links are not fetched.",
      proposed_repository_destination: `${packageRoot}/services/${asset.key}.jpg`,
      proposed_sanitized_source_destination: `${repoRoot}/${packageRoot}/services/${asset.key}.jpg`,
      public_demo_path: asset.target,
      relationships: asset.relationships,
      owner_decision: "PENDING",
      source_license_inclusion_decision: "PENDING"
    })),
    product_dependencies: inventory.product_photos.map((asset, index) => ({
      inventory_identity: { file: Object.keys(pins)[1], pointer: `/product_photos/${index}` },
      ...asset,
      proposed_repository_destination: `${packageRoot}/products/product-${index + 1}.jpg`,
      dependency_status: "UNRESOLVED_NO_LOCAL_BYTES_HASH_OR_SUFFICIENT_REDISTRIBUTION_EVIDENCE",
      owner_decision: "PENDING_NO_DOWNLOAD_OR_SUBSTITUTION"
    })),
    proposed_null_replacements: inventory.proposed_null_replacements,
    null_policy: "Preserve every proposed NULL exactly, including Shop, Specialist/profile, Brand, CMS/blog and country flags. Proposed NULLs are not accepted photo-complete parity.",
    exact_decision_requested: [
      "Identify this approval JSON SHA256 and all four frozen evidence SHA256 values in the owner decision.",
      "List accepted Service inventory identities (not an inferred approve-all), exact bytes/MIME/SHA256 and unchanged Service/Gallery relationships or an explicitly revised frozen relationship proposal.",
      "For each selected Service, explicitly authorize public redistribution and source/license/provenance inclusion; provide sufficient license evidence, required attribution/restrictions and any person/mark rights basis. Recorded links alone are not certification.",
      "Accept or revise the proposed sanitized repository, repository-relative package paths and provenance destination. Separately name the fresh physical public demo destination before execution; do not authorize overwriting existing storage.",
      "Keep all five Product dependencies blocked, or supply a separately frozen local-byte/licensing proposal preserving all Product/Stock/Gallery relationships or explicitly approving revisions. No remote download or arbitrary substitution.",
      "Preserve the listed proposed NULL fields unless a separately reviewed exact replacement is approved; explicitly distinguish proposed NULL preservation from acceptance of visual parity.",
      "Give separate explicit execution authority before any image copy. This document, its existence and review completion confer none."
    ],
    deferred_execution: inventory.future_packaging_contract,
    acceptance_status: "REVIEW_ONLY; no package created and no fresh-checkout availability or installer negative-failure proof claimed.",
    unauthorized_actions: ["image packaging/copying", "database operations", "runtime storage writes",
      "external downloads", "credentials/identities/keys", "financial operations", "provider activation",
      "GitHub actions", "VPS actions", "Stories changes"]
  };
}

export const serializeApproval = value => `${JSON.stringify(value, null, 2)}\n`;

export function renderApproval(value) {
  const lines = [
    "# Demo photo packaging — bounded owner-approval package", "",
    "**REVIEW ONLY. Owner decision pending. No image copying is authorized.**", "",
    "No prior owner-approved packaging decision exists. This proposal preserves the frozen inventory and baseline without granting redistribution rights.",
    "No images are packaged, downloaded, substituted or embedded in this document.", "",
    `Approval JSON SHA256: \`${sha(Buffer.from(serializeApproval(value)))}\``,
    "This digest identifies the decision proposal, not an approval receipt.", "",
    "## Frozen evidence", "", "| Evidence | SHA256 |", "|---|---|"
  ];
  for (const record of value.frozen_evidence) lines.push(`| \`${record.file}\` | \`${record.sha256}\` |`);
  lines.push("", "## Proposed destination — not created or approved", "",
    `- Sanitized repository: \`${repoRoot}\` (existing source snapshot, not independently certified Git history).`,
    `- Repository-relative package root: \`${packageRoot}\`.`,
    `- Approved selected provenance would go to \`${value.proposed_location.provenance_destination}\`.`,
    `- ${value.proposed_location.provenance_selection}`,
    "- Public URLs remain `/storage/portable-demo/services/…` and `/storage/portable-demo/products/…`.",
    "- No physical public destination has been selected; no write into existing runtime storage is proposed.",
    "- The JSON retains exact per-file destinations, inventory pointers and every consumer relationship.", "",
    "## 15 Service JPEG decisions", "",
    "Every entry is PENDING for photo selection, public redistribution and provenance/license inclusion.",
    "The recorded basis below is a historical claim, not a verified license or person/mark-rights certificate.", "");
  for (const asset of value.service_photos) {
    lines.push(`### ${asset.inventory_identity.key}`, "",
      `- Inventory identity: \`${asset.inventory_identity.file}#${asset.inventory_identity.pointer}\`.`,
      `- Existing input: \`${asset.file}\`.`,
      `- SHA256: \`${asset.sha256}\`.`,
      `- MIME/size: \`${asset.mime}\`, **${asset.bytes} bytes**.`,
      `- Source/provenance: ${asset.source}`,
      `- Recorded license: ${asset.license_reference}`,
      `- Recorded redistribution basis: ${asset.recorded_redistribution_basis}`,
      `- Evidence limit: ${asset.evidence_limit}`,
      `- Proposed sanitized-source destination: \`${asset.proposed_sanitized_source_destination}\`.`,
      `- Public demo path: \`${asset.public_demo_path}\`.`, "",
      "| Service ID | Shop ID | Title |", "|---|---|---|");
    for (const row of asset.relationships.filter(r => r.table === "services"))
      lines.push(`| ${row.id} | ${row.shop_id} | ${row.title} |`);
    lines.push("", "| Gallery ID | Loadable type | Loadable ID |", "|---|---|---|");
    for (const row of asset.relationships.filter(r => r.table === "galleries"))
      lines.push(`| ${row.id} | \`${row.loadable_type}\` | ${row.loadable_id} |`);
    lines.push("");
  }
  lines.push("## Five unresolved Product photo dependencies", "",
    "All five remain BLOCKED. Frozen evidence has NULL local file, byte count, MIME and SHA256, no filename candidates, and insufficient redistribution evidence.",
    "The prior bounded search is not proof that no equivalent exists anywhere; no wider/private/runtime search is authorized.",
    "Remote URLs and source comments are evidence only, not authority to download, substitute or map an unrelated photo.", "");
  for (const asset of value.product_dependencies) {
    lines.push(`### ${asset.title}`, "",
      `- Inventory identity: \`${asset.inventory_identity.file}#${asset.inventory_identity.pointer}\`.`,
      `- Source URL (not fetched): ${asset.source_url}`,
      `- Source evidence: \`${asset.source_evidence.file}:${asset.source_evidence.line}\`; SHA256 \`${asset.source_evidence.sha256}\`.`,
      `- Unverified source claims: ${asset.source_evidence.unverified_photo_comments.join("; ")}`,
      `- License to review (not proof): ${asset.license_reference_to_review}`,
      `- Local file/bytes/MIME/SHA256: **NULL / NULL / NULL / NULL**. Filename candidates: ${asset.local_filename_candidates.length}.`,
      `- Proposed repository destination (blocked): \`${asset.proposed_repository_destination}\`.`,
      `- Public demo path: \`${asset.target}\`.`, "",
      "| Consumer | ID | Relationship |", "|---|---|---|");
    for (const row of asset.relationships) lines.push(`| ${row.table} | ${row.id} | ${
      row.table === "products" ? `Shop ${row.shop_id}; ${row.title}` :
      row.table === "stocks" ? `Product ${row.product_id}` : `${row.loadable_type} ${row.loadable_id}`} |`);
    lines.push("");
  }
  lines.push("## Proposed NULL photo fields — preserved, not accepted parity", "",
    value.null_policy, "", "| Family | Row ID | Proposed NULL fields |", "|---|---|---|");
  for (const family of value.proposed_null_replacements)
    for (const row of family.rows) lines.push(`| ${family.table}${row.specialist ? " (Specialist)" : ""} | ${row.id} | ${row.fields.join(", ")} |`);
  lines.push("", "## Exact owner decision requested", "");
  value.exact_decision_requested.forEach((decision, index) => lines.push(`${index + 1}. ${decision}`));
  lines.push("", "No selections or signatures are prefilled. Owner approval must be recorded before any bytes are copied.",
    "Approving this document for review alone does not authorize execution.", "",
    "## Deferred execution and acceptance", "",
    "Consumer totals remain **51 Service, 13 Product, 14 Stock and 64 Gallery**.",
    `Future rejection contract: ${value.deferred_execution.reject.join("; ")}.`,
    "Preflight the entire approved selection before copying. Never overwrite or fetch at bootstrap.",
    value.deferred_execution.acceptance,
    "Future installer tests must prove corruption, absence, symlinks and occupied-destination failures without modifying any database.",
    value.acceptance_status, "",
    `Not authorized: ${value.unauthorized_actions.join("; ")}.`, "",
    "## Read-only reproduction", "", "```sh",
    "node scripts/database/demo-photo-approval.mjs --check",
    "node --test scripts/database/demo-photo-approval.test.mjs scripts/database/demo-asset-review.test.mjs",
    "node scripts/database/check-synthetic-reference-review.mjs --check", "```", "",
    "`--check` compares both generated documents, pinned review hashes and existing source/photo evidence.",
    "`--print-json` and `--print-md` emit review text only; no install/copy/approval mode exists.",
    "Current workspace checks are not fresh-checkout availability proof.", "");
  return lines.join("\n");
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  assert(process.argv.length === 3 && ["--check", "--print-json", "--print-md"].includes(process.argv[2]),
    "Review text only; no installation/copy/approval mode exists.");
  const evidence = Object.fromEntries(Object.keys(pins).map(file => [file, fs.readFileSync(regularFile(root, file))]));
  const proposal = deriveApproval(evidence);
  assert.deepEqual(buildReview(), JSON.parse(evidence[Object.keys(pins)[1]]), "Existing photo/source evidence drift");
  if (process.argv[2] === "--print-json") process.stdout.write(serializeApproval(proposal));
  else if (process.argv[2] === "--print-md") process.stdout.write(renderApproval(proposal));
  else {
    for (const [ext, text] of [["json", serializeApproval(proposal)], ["md", renderApproval(proposal)]])
      assert.equal(fs.readFileSync(regularFile(root, `${output}.${ext}`), "utf8"), text, "Approval proposal drift");
    console.log("PASS: pinned owner-review package; 15 Service decisions, 5 blocked Product dependencies; 51/13/14/64 consumers and proposed NULLs preserved. No copying authorized.");
  }
}
