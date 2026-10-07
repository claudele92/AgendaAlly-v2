// Fresh-checkout verifier guard. This is not a kernel/network namespace.
// Node filesystem permissions separately deny all original workspace reads.
const assert = require("node:assert/strict");
const path = require("node:path");
const root = process.env.AGENDAALLY_ASSET_CHECKOUT;
assert(root && path.isAbsolute(root), "Isolated checkout required");
const deny = () => { throw new Error("OFFLINE_NETWORK_OR_PROCESS_DENIED"); };
for (const [module, methods] of [
  ["node:http", ["request", "get"]],
  ["node:https", ["request", "get"]],
  ["node:net", ["connect", "createConnection"]],
  ["node:tls", ["connect"]],
  ["node:dgram", ["createSocket"]],
  ["node:dns", ["lookup", "resolve", "resolve4", "resolve6"]],
  ["node:dns/promises", ["lookup", "resolve", "resolve4", "resolve6"]]
]) for (const method of methods) require(module)[method] = deny;
require("node:net").Socket.prototype.connect = deny;
global.fetch = deny;
global.WebSocket = class { constructor() { deny(); } };
const child = require("node:child_process");
const execFileSync = child.execFileSync;
child.execFileSync = (command, args, options) => {
  assert.equal(command, "file", "OFFLINE_NETWORK_OR_PROCESS_DENIED");
  assert.deepEqual(args.slice(0, 3), ["--brief", "--mime-type", "--"]);
  assert.equal(args.length, 4);
  const filename = args[3];
  assert(filename.startsWith(`${root}/`) && !path.relative(root, filename).startsWith(".."),
    "OFFLINE_NETWORK_OR_PROCESS_DENIED");
  return execFileSync(command, args, options);
};
for (const method of ["exec", "execSync", "execFile", "spawn", "spawnSync", "fork"]) child[method] = deny;
require("node:module").syncBuiltinESMExports();
