// Read-only application inspection. Writes only this explicitly authorized evidence directory.
import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import zlib from "node:zlib";

const out = path.dirname(new URL(import.meta.url).pathname);
const temporary = "/tmp/agendaally-mobile-audit";
const root = ".migration-backup/customer_app";
const sha = (b) => crypto.createHash("sha256").update(b).digest("hex");
const inventory = JSON.parse(fs.readFileSync(`${temporary}/source-inventory.json`));
const sources = inventory.files.filter(x => x.path.endsWith(".dart") && !/\.(g|freezed)\.dart$/.test(x.path))
  .map(x => ({ path: x.path, text: fs.readFileSync(x.path, "utf8") }));
const write = (name, value) => fs.writeFileSync(`${out}/${name}`, JSON.stringify(value, null, 2) + "\n");

// Find a complete call expression, not the next line with ");".
function callExpression(text, start) {
  let depth = 0, quote = null, escape = false;
  for (let i = text.indexOf("(", start); i < text.length; i++) {
    const ch = text[i];
    if (quote) {
      if (escape) escape = false;
      else if (ch === "\\") escape = true;
      else if (ch === quote) quote = null;
      continue;
    }
    if (ch === "'" || ch === '"') quote = ch;
    else if (ch === "(") depth++;
    else if (ch === ")" && --depth === 0) return text.slice(start, i + 1);
  }
  throw new Error(`Unbalanced call at ${start}`);
}

const calls = [];
for (const source of sources.filter(x => x.path.includes("/infrastructure/repository/"))) {
  for (const m of source.text.matchAll(/(?:client|dio)\.(get|post|put|delete|patch)\s*\(/g)) {
    const start = source.text.lastIndexOf("@override", m.index);
    const next = source.text.indexOf("@override", m.index);
    const context = source.text.slice(start < 0 ? 0 : start, next < 0 ? undefined : next);
    const operation = context.match(/\b(\w+)\s*\([^]*?\)\s*async\s*\{/)?.[1] || "inspect";
    const expression = callExpression(source.text, m.index);
    const consumers = [];
    const pattern = new RegExp("\\." + operation + "\\s*\\(", "g");
    for (const consumer of sources.filter(x => x.path !== source.path)) {
      for (const hit of consumer.text.matchAll(pattern)) {
        consumers.push({ path: consumer.path, line: consumer.text.slice(0, hit.index).split("\n").length });
      }
    }
    const blocPaths = consumers.filter(x => x.path.includes("/application/")).map(x => x.path.split("/lib/")[1]);
    const screens = sources.filter(x => x.path.includes("/presentation/") && blocPaths.some(b => x.text.includes(b)))
      .map(x => x.path);
    calls.push({
      file: source.path, line: source.text.slice(0, m.index).split("\n").length,
      operation, httpMethod: m[1].toUpperCase(),
      paths: [...expression.matchAll(/(['"])([^'"\n]*(?:api\/v1|https:\/\/)[^'"\n]*)\1/g)].map(x => x[2]),
      requestExpression: expression,
      authExpression: context.match(/requireAuth:\s*([^\n,;)]+)/)?.[1] || "inspect",
      literalFieldNamesInMethod: [...new Set([...context.matchAll(/['"]([A-Za-z_][\w\[\]$.{}-]*)['"]\s*:/g)].map(x => x[1]))],
      responseParsers: [...new Set([...context.matchAll(/(\w+)\.(fromJson\w*)\(/g)].map(x => x[1] + "." + x[2]))],
      directConsumers: consumers, presentationImportsOfConsumerBloc: screens,
      integrationClassification: "UNKNOWN / REQUIRES RUNTIME ACCEPTANCE",
      evidenceLevel: "SOURCE-CONFIRMED call expression; lexical fields/imports are discovery aids, not runtime proof",
    });
  }
}

// Explicit contract findings override the default unknown classification.
for (const c of calls) {
  if (c.operation === "verifyEmail") c.integrationClassification = "STALE";
  else if (c.operation === "login") c.integrationClassification = "UNSAFE / INCORRECT";
  else if (c.operation === "logout") c.integrationClassification = "PARTIALLY MATCHES";
  else if (c.operation === "getPayments") c.integrationClassification = "PARTIALLY MATCHES";
  else if (c.operation === "getPolicy" || c.operation === "getTerm") c.integrationClassification = "PARTIALLY MATCHES";
  else if (c.file.includes("booking_repository")) c.integrationClassification = "PARTIALLY MATCHES";
}

write("source-inventory.json", inventory);
write("api-callsite-inventory.json", calls);
const baseline = JSON.parse(fs.readFileSync(`${temporary}/source-before.json`));
const after = Object.fromEntries(Object.keys(baseline).map(file => [file, fs.existsSync(file) ? sha(fs.readFileSync(file)) : null]));
const changed = Object.keys(baseline).filter(file => baseline[file] !== after[file]);
write("source-before.json", baseline);
write("source-after.json", after);
write("source-preservation.json", {
  algorithm: "SHA256 of exact file bytes, compared over the original 5,816-file baseline",
  baselineFiles: Object.keys(baseline).length, comparedFiles: Object.keys(after).length,
  changedOrMissingFiles: changed, identical: changed.length === 0,
  scope: "Existing mobile/web/admin/backend files represented by baseline; dependencies, generated caches, storage and SQLite excluded",
  limitation: "Fixed baseline detects modification/deletion, not newly created files; use the independent git status evidence for additions and tracked root configuration.",
});
for (const name of ["database-before.json", "database-after.json", "database-fingerprint-recipe.json",
  "sdk-version.txt", "pub-get---offline.txt", "pub-get---offline.exit",
  "analyze---no-pub.exit", "build-apk---debug---no-pub.txt", "build-apk---debug---no-pub.exit"]) {
  fs.copyFileSync(`${temporary}/${name}`, `${out}/${name}`);
}
fs.writeFileSync(`${out}/analyze---no-pub.txt.gz`, zlib.gzipSync(fs.readFileSync(`${temporary}/analyze---no-pub.txt`)));
const analyzer = fs.readFileSync(`${temporary}/analyze---no-pub.txt`, "utf8").split("\n");
fs.writeFileSync(`${out}/analyze-excerpt.txt`, [...analyzer.slice(0, 30), "\n[Complete unchanged output retained in analyze---no-pub.txt.gz]\n", ...analyzer.slice(-12)].join("\n"));
write("validation-commands.json", {
  isolation: { cwd: "/tmp/agendaally-mobile-audit/customer_app",
    environment: { HOME: `${temporary}/home`, PUB_CACHE: `${temporary}/pub-cache`,
      XDG_CONFIG_HOME: `${temporary}/config`, CI: "true",
      FLUTTER_SUPPRESS_ANALYTICS: "true", DART_SUPPRESS_ANALYTICS: "true" } },
  commands: [
    { command: "flutter --version", result: "Flutter 3.32.0; Dart 3.8.0", log: "sdk-version.txt" },
    { command: "flutter pub get --offline", exit: 1, classification: "DEPENDENCY/VERSION ISSUE", log: "pub-get---offline.txt" },
    { command: "flutter analyze --no-pub", exit: 1, classification: "STATIC VALIDATION NOT AVAILABLE: dependencies unresolved", log: "analyze---no-pub.txt.gz" },
    { command: "flutter build apk --debug --no-pub", exit: 1, classification: "UNSUPPORTED LOCAL BUILD ENVIRONMENT: Android SDK absent", log: "build-apk---debug---no-pub.txt" },
  ],
  notExecuted: ["online dependency installation", "dependency upgrades", "project regeneration", "iOS native build", "native runtime acceptance", "server boot", "Web acceptance campaigns", "provider calls", "email sends", "financial mutations"],
});
if (changed.length) throw new Error("Preservation mismatch: inspect source-preservation.json; do not overwrite application files.");
console.log(JSON.stringify({ sourceFilesUnchanged: Object.keys(after).length, repositories: inventory.repositories,
  screenFamilies: inventory.screenFamilies.length, httpCallsites: calls.length }));
