import fs from "node:fs";
import path from "node:path";
import os from "node:os";
import { fileURLToPath } from "node:url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const target = fs.mkdtempSync(path.join(os.tmpdir(), "agendaally-client-baseline-test."));
const extensions = new Set([".js", ".jsx", ".ts", ".tsx", ".mjs", ".cjs", ".json", ".css", ".scss", ".html"]);
function copyCode(source, destination) {
  const stat = fs.lstatSync(source);
  if (stat.isSymbolicLink()) return;
  if (stat.isDirectory()) {
    for (const name of fs.readdirSync(source)) {
      if (name.startsWith(".") || ["node_modules", "public", "build", "dist"].includes(name)) continue;
      copyCode(path.join(source, name), path.join(destination, name));
    }
  } else if (extensions.has(path.extname(source))) {
    fs.mkdirSync(path.dirname(destination), { recursive: true });
    fs.copyFileSync(source, destination);
  }
}
for (const name of ["web", "admin"]) {
  const source = path.join(root, ".migration-backup", name);
  const destination = path.join(target, name);
  fs.mkdirSync(destination);
  copyCode(source, destination);
  // Exact original lock is copied explicitly, never regenerated here.
  fs.copyFileSync(path.join(source, "yarn.lock"), path.join(destination, "yarn.lock"));
  fs.mkdirSync(path.join(destination, "public"));
}
console.log(JSON.stringify({
  directory: target,
  web: path.join(target, "web"),
  admin: path.join(target, "admin"),
  scope: "source only; no dependencies, environment files, public/media assets or symlinks",
  caveat: "Builds may fail on omitted assets. This script is not an asset-complete app restore.",
}, null, 2));