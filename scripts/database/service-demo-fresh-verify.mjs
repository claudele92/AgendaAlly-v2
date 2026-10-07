import fs from "node:fs";
import path from "node:path";
import assert from "node:assert/strict";
import { fileURLToPath } from "node:url";
import { execFileSync } from "node:child_process";
import net from "node:net";
import { loadContract, verifyPackage } from "./service-demo-assets.mjs";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
assert.equal(process.env.AGENDAALLY_ASSET_CHECKOUT, root);
assert(process.permission, "Node filesystem permission mode required");
const originalInput = process.env.AGENDAALLY_DENIED_ORIGINAL_INPUT;
assert(originalInput && !originalInput.startsWith("/tmp/"), "Workspace denial probe required");
assert.throws(() => fs.readFileSync(originalInput), error => error.code === "ERR_ACCESS_DENIED");
assert.throws(() => fs.writeFileSync(path.join(root, "unauthorized-write"), "denial probe"), error => error.code === "ERR_ACCESS_DENIED");
assert.throws(() => fetch("https://example.invalid/"), /OFFLINE_NETWORK_OR_PROCESS_DENIED/);
assert.throws(() => net.connect(443, "example.invalid"), /OFFLINE_NETWORK_OR_PROCESS_DENIED/);
assert.throws(() => execFileSync("curl", ["https://example.invalid/"]), /OFFLINE_NETWORK_OR_PROCESS_DENIED/);
console.log(JSON.stringify({
  ...verifyPackage(root, loadContract(root)),
  isolation: {
    workspace_asset_read: "DENIED_BY_NODE_PERMISSION",
    verification_writes: "DENIED_BY_NODE_PERMISSION",
    fetch_socket_external_process: "DENIED_BY_OFFLINE_GUARD",
    mime_detector: "file only, approved checkout paths only",
    no_kernel_namespace_claim: true
  }
}, null, 2));
