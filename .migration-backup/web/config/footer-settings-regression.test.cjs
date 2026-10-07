const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const ts = require("typescript");
const root = path.resolve(__dirname, "..");
const modules = {};
function load(name) {
  if (modules[name]) return modules[name];
  const module = { exports: {} };
  const js = ts.transpileModule(fs.readFileSync(path.join(root, "utils", `${name}.ts`), "utf8"), {
    compilerOptions: { module: ts.ModuleKind.CommonJS, target: ts.ScriptTarget.ES2022 },
  }).outputText;
  new Function("module", "exports", "require", js)(module, module.exports, (relative) => load(relative.replace("./", "")));
  modules[name] = module.exports;
  return module.exports;
}
const { footerDestination, footerCopyright } = load("footer-settings");
test("official supplied social destinations and optional TikTok remain data driven", () => {
  for (const [kind, url] of [
    ["instagram", "https://www.instagram.com/agendaally"],
    ["facebook", "https://www.facebook.com/AgendaAlly/"],
    ["linkedin", "https://www.linkedin.com/company/agendaally"],
    ["tiktok", "https://www.tiktok.com/@agendaally"],
  ]) {
    assert.equal(footerDestination(url, kind), url);
  }
  assert.equal(footerDestination("", "tiktok"), undefined);
  assert.equal(footerDestination("https://tiktok.com.evil.invalid/@agendaally", "tiktok"), undefined);
  const footer = fs.readFileSync(path.join(root, "components/footer/footer.tsx"), "utf8");
  assert.match(footer, /Follow Us/);
  assert.match(footer, /settings\?\.tiktok/);
  assert.match(footer, /Book local expertise\. Shop local businesses\./);
  assert.match(footer, /ri-instagram-line/);
  assert.match(footer, /ri-facebook-box-fill/);
  assert.match(footer, /ri-linkedin-box-fill/);
  assert.match(footer, /ri-tiktok-fill/);
  assert.doesNotMatch(footer, /https:\/\/www\.(instagram|facebook|linkedin|tiktok)/);
});
test("missing, example, unsafe and wrong-network destinations stay hidden", () => {
  for (const value of [undefined, "", "example.com/profile", "https://example.org/profile",
    "https://social.example/profile", "http://instagram.com/profile",
    "https://instagram.com.evil.test/profile", "https://user:password@instagram.com/profile",
    "https://facebook.com/profile"]) {
    assert.equal(footerDestination(value, "instagram"), undefined, value);
  }
  assert.equal(footerDestination("instagram.com/owner-configured-profile", "instagram"),
    "https://instagram.com/owner-configured-profile");
  assert.equal(footerDestination("https://x.com/owner-configured-profile", "twitter"),
    "https://x.com/owner-configured-profile");
});
test("only configured real-format app-store destinations are usable", () => {
  assert.equal(footerDestination(undefined, "ios"), undefined);
  assert.equal(footerDestination("https://apps.apple.com/app/placeholder/id0000000000", "ios"), undefined);
  assert.equal(footerDestination("https://play.google.com/store/apps/details", "android"), undefined);
  assert.equal(footerDestination("https://apps.apple.com/app/configured-app/id123456789", "ios"),
    "https://apps.apple.com/app/configured-app/id123456789");
  assert.equal(footerDestination("https://play.google.com/store/apps/details?id=org.owner.customer", "android"),
    "https://play.google.com/store/apps/details?id=org.owner.customer");
});
test("copyright is configured, supports the current year and preserves custom text", () => {
  assert.equal(footerCopyright("© 2025 AgendaAlly. All rights reserved.", 2026),
    "© 2026 AgendaAlly. All rights reserved.");
  assert.equal(footerCopyright(undefined, 2026), "© 2026 AgendaAlly. All rights reserved.");
  assert.equal(footerCopyright("Custom owner copyright", 2026), "Custom owner copyright");
});
test("footer keeps distinct native discovery paths and actual persisted geography", () => {
  const footer = fs.readFileSync(path.join(root, "components/footer/footer.tsx"), "utf8");
  for (const route of ["/services", "/products", "/shops", "/masters", "/terms", "/privacy"]) {
    assert.ok(footer.includes(`href="${route}"`), route);
  }
  assert.match(footer, /useAddressStore\(\(state\) => state\.country\)/);
  assert.match(footer, /useAddressStore\(\(state\) => state\.city\)/);
  assert.match(footer, /socialLinks\.length > 0/);
  assert.doesNotMatch(footer, /Yaoundé|Douala|development example|development preview/i);
});