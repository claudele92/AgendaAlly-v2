const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const ts = require("typescript");

const webRoot = path.resolve(__dirname, "..");
const read = (relativePath) => readFileSync(path.join(webRoot, relativePath), "utf8");
const footer = read("components/footer/footer.tsx");
const globalStyles = read("app/globals.css");
const contact = read("app/(store)/(booking)/(with-footer)/(simple)/contact/page.tsx");
const blogContent = read("app/(store)/(booking)/(with-footer)/(simple)/blogs/content.tsx");
const faq = read("app/(store)/(booking)/(with-footer)/(simple)/faq/content.tsx");
const terms = read("app/(store)/(booking)/(with-footer)/(simple)/terms/content.tsx");
const privacy = read("app/(store)/(booking)/(with-footer)/(simple)/privacy/content.tsx");
const businessDownloads = read("app/(store)/(business)/for-business/business-downloads.tsx");
const mobileLinks = read("app/(store)/(business)/components/mobile-links/mobile-links.tsx");

test("footer disclosure controls keep one element type for server and client hydration", () => {
  assert.equal((footer.match(/<Disclosure\.Button\s+as="button"/g) || []).length, 1);
  assert.doesNotMatch(footer, /as=\{isMobile\s*\?/);
  assert.equal((footer.match(/<Disclosure as="div" defaultOpen>/g) || []).length, 1);
  assert.equal((footer.match(/<Disclosure\.Panel>/g) || []).length, 1);
  assert.equal((footer.match(/<FooterGroup title=/g) || []).length, 3);
  assert.doesNotMatch(footer, /<Disclosure\.Panel static/);
  assert.doesNotMatch(footer, /\(isMobile\s*\?\s*open\s*:\s*true\)/);
});

test("shared gradient utility remains while removed homepage canvas styling is absent", () => {
  assert.match(globalStyles, /\.gradient\s*\{\s*background:\s*linear-gradient/);
  assert.doesNotMatch(globalStyles, /#gradient-canvas/);
});

test("configured public URLs allow HTTPS destinations and label reserved example hosts", async () => {
  const source = read("utils/parse-settings.ts");
  const transpiled = ts.transpileModule(source, {
    compilerOptions: {
      module: ts.ModuleKind.ESNext,
      target: ts.ScriptTarget.ES2022,
    },
  }).outputText;
  const settingsUtilities = await import(
    `data:text/javascript;base64,${Buffer.from(transpiled).toString("base64")}`
  );

  assert.equal(settingsUtilities.configuredExternalUrl(undefined), undefined);
  assert.equal(settingsUtilities.configuredExternalUrl(" "), undefined);
  assert.equal(
    settingsUtilities.configuredExternalUrl("example.com/agendaally"),
    "https://example.com/agendaally"
  );
  assert.equal(settingsUtilities.configuredExternalUrl("http://example.com"), undefined);
  assert.equal(settingsUtilities.configuredExternalUrl("javascript:alert(1)"), undefined);
  assert.equal(
    settingsUtilities.configuredExternalUrl("https://user:secret@example.com"),
    undefined
  );
  assert.equal(settingsUtilities.isDevelopmentExampleUrl("https://example.com/instagram"), true);
  assert.equal(
    settingsUtilities.isDevelopmentExampleUrl("https://instagram.com/agendaally"),
    false
  );
});

test("footer adds real blog discovery without changing stable disclosure hydration", () => {
  assert.match(footer, /href="\/blogs"/);
  assert.match(footer, /footerDestination/);
  assert.match(footer, /socialLinks\.map/);
  assert.doesNotMatch(footer, /development example|development preview|Synthetic content/i);
  assert.match(footer, /hasCustomerAppLinks && \(/);
});

test("support and download surfaces do not invent contact details or dead store links", () => {
  assert.match(contact, /Contact information could not be loaded/);
  assert.match(contact, /status-contact-settings-error/);
  assert.match(contact, /status-contact-not-configured/);
  assert.doesNotMatch(contact, /defaultLocation|hasConfiguredCoordinates/);

  assert.match(businessDownloads, /footerDestination\(value, destination\)/);
  assert.match(businessDownloads, /if \(availableApps\.length === 0\) return null/);
  assert.doesNotMatch(businessDownloads, /development example|development preview/i);
  assert.match(mobileLinks, /footerDestination\(settings\.customer_app_ios, "ios"\)/);
  assert.match(mobileLinks, /footerDestination\(settings\.customer_app_android, "android"\)/);
  assert.match(mobileLinks, /if \(links\.length === 0\) return null/);
  assert.doesNotMatch(mobileLinks, /unverifiedSeedDestinations|development preview/i);
  assert.doesNotMatch(mobileLinks, /href=\{settings\?\.[^}]+\|\| ""\}/);
  assert.match(faq, /status-faq-error/);
  assert.match(faq, /status-faq-unavailable/);
});

test("article and legal surfaces distinguish unavailable content from failed requests", () => {
  assert.match(blogContent, /status-blog-list-error/);
  assert.match(blogContent, /status-blog-list-empty/);
  assert.match(terms, /status-terms-error/);
  assert.match(terms, /status-terms-unavailable/);
  assert.match(privacy, /status-privacy-error/);
  assert.match(privacy, /status-privacy-unavailable/);
});