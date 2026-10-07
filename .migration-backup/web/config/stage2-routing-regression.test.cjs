const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const ts = require("typescript");
const root = path.resolve(__dirname, "..");
const middleware = fs.readFileSync(path.join(root, "middleware.ts"), "utf8");
const redirectHelperSource = fs.readFileSync(
  path.join(root, "config/approved-home-redirect.ts"),
  "utf8"
);
const redirectHelperModule = { exports: {} };
new Function("module", "exports", ts.transpileModule(redirectHelperSource, {
  compilerOptions: { module: ts.ModuleKind.CommonJS, target: ts.ScriptTarget.ES2022 },
}).outputText)(redirectHelperModule, redirectHelperModule.exports);

test("the approved root marketplace is not rewritten by legacy ui_type", () => {
  assert.doesNotMatch(middleware, /NextResponse\.rewrite/);
  assert.doesNotMatch(middleware, /settings\.ui_type\s*=/);
  assert.match(middleware, /NextResponse\.next\(\)/);
});
test("authentication and stale-language handling remain in native middleware", () => {
  assert.match(middleware, /cookies\(\)\)\.has\("token"\)/);
  assert.match(middleware, /pathname\.includes\("\/profile"\)/);
  assert.match(middleware, /pathname\.includes\("\/orders"\)/);
  assert.match(middleware, /withLangCookieCleared\(NextResponse\.redirect/);
  assert.match(middleware, /loginUrl\.searchParams\.set\("redirect", `\$\{pathname\}\$\{request\.nextUrl\.search\}`\)/);
  assert.match(middleware, /response\.cookies\.delete\("lang"\)/);
});
test("legacy storefront bookmarks redirect to the approved root", () => {
  const base = "app/(store)/(booking)/(with-footer)";
  for (const [route, file] of [
    ["/home-2", "(home-2)/home-2/page.tsx"],
    ["/home-3", "home-3/page.tsx"],
    ["/home-4", "home-4/page.tsx"],
  ]) {
    assert.match(middleware, new RegExp(`"${route}"`));
    const page = fs.readFileSync(path.join(root, base, file), "utf8");
    assert.match(page, /redirect\("\/"\)/, file);
    assert.doesNotMatch(page, /components\/|HomePage|<main/, file);
  }
  assert.match(middleware, /NextResponse\.redirect\(approvedHomeRedirectUrl\(request\.nextUrl\), 302\)/);
});
test("legacy bookmark redirects preserve the original query string", () => {
  const original = new URL("https://agendaally.example/home-3?country_id=7&term=hair+cut");
  const redirected = redirectHelperModule.exports.approvedHomeRedirectUrl(original);

  assert.notEqual(redirected, original);
  assert.equal(redirected.pathname, "/");
  assert.equal(redirected.search, original.search);
});
test("city selection remains native API-backed and clears stale city on country change", () => {
  const form = fs.readFileSync(path.join(root, "components/country-select/country-select-form.tsx"), "utf8");
  assert.match(form, /queryEnabled=\{Boolean\(tempCountry\?\.id\)\}/);
  assert.match(form, /setTempCountry\(value\);\s*setTempCity\(null\)/);
  assert.match(form, /setCookie\("country_id", tempCountry\.id/);
  assert.match(form, /setCookie\("city_id", tempCity\.id/);
  assert.match(form, /deleteCookie\("city_id"\)/);
  assert.match(form, /clearLocalCart\(\)/);
});