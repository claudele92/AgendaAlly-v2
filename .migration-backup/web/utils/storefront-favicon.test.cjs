const { test } = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const crypto = require("node:crypto");
const ts = require("typescript");
const source = fs.readFileSync(require.resolve("./agendaally-brand-assets.ts"), "utf8");
const js = ts.transpileModule(source, { compilerOptions: { module: ts.ModuleKind.CommonJS } }).outputText;
const moduleExports = {};
new Function("exports", js)(moduleExports);
const { resolveStorefrontFavicon, AGENDAALLY_STOREFRONT_FAVICON } = moduleExports;
test("native favicon setting and fallback use the immutable Customer URL", () => {
  assert.equal(resolveStorefrontFavicon("http://localhost:8000/storage/images/settings/agendaally-platform-mark.png"), AGENDAALLY_STOREFRONT_FAVICON);
  assert.equal(resolveStorefrontFavicon(null), AGENDAALLY_STOREFRONT_FAVICON);
  assert.equal(resolveStorefrontFavicon("/brand/agendaally-mark.png"), AGENDAALLY_STOREFRONT_FAVICON);
});
test("custom owner branding is not replaced by a platform fallback", () => {
  assert.equal(resolveStorefrontFavicon("https://cdn.example.com/customer.png"), "https://cdn.example.com/customer.png");
  assert.equal(resolveStorefrontFavicon("/storage/images/custom.png"), "/storage/images/custom.png");
});
test("versioned icon bytes are the exact approved compact symbol", () => {
  const bytes = fs.readFileSync(require("node:path").join(__dirname, "../public", AGENDAALLY_STOREFRONT_FAVICON));
  assert.equal(crypto.createHash("sha256").update(bytes).digest("hex"), "0e2bbcff4732e05710344437e647e2b258c92b5b193b66cc98db1f55226eace7");
  assert.deepEqual(bytes, fs.readFileSync(require("node:path").join(__dirname, "../public/brand/agendaally-mark.png")));
});
test("root metadata consumes Customer favicon, not the Admin field", () => {
  const layout = fs.readFileSync(require("node:path").join(__dirname, "../app/layout.tsx"), "utf8");
  assert.match(layout, /resolveStorefrontFavicon\(parsedSettings\.favicon\)/);
  assert.doesNotMatch(layout, /admin_favicon/);
});