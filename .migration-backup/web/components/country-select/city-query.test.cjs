const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const shouldEnableAsyncSelectQuery = require("../async-select/should-enable-async-select-query.cjs");
const countrySelectSource = readFileSync(path.join(__dirname, "country-select-form.tsx"), "utf8");
const asyncSelectSource = readFileSync(path.join(__dirname, "../async-select/async-select.tsx"), "utf8");

test("a temporary country selection enables the real city query before SaveAddress", () => {
  const temporaryCountry = { id: 1 };

  assert.equal(
    shouldEnableAsyncSelectQuery({
      queryKey: "v1/rest/cities",
      queryEnabled: Boolean(temporaryCountry.id),
      isInputFocused: false,
      isMobile: false,
      isMobileDrawerOpen: false,
    }),
    true
  );

  const citySelector = countrySelectSource.match(
    /<AsyncSelect\s+label="select\.city"([\s\S]*?)\/>/
  )?.[1];
  assert.ok(citySelector, "country selector should keep a real city AsyncSelect");
  assert.match(citySelector, /queryKey="v1\/rest\/cities"/);
  assert.match(citySelector, /queryEnabled={Boolean\(tempCountry\?\.id\)}/);
  assert.match(
    citySelector,
    /queryParams=\{\{\s*country_id:\s*tempCountry\?\.id\s*\}\}/
  );

  const countrySelector = countrySelectSource.match(
    /<AsyncSelect\s+label="select\.country"([\s\S]*?)\/>/
  )?.[1];
  assert.ok(countrySelector, "country selector should remain present");
  assert.match(countrySelector, /setTempCountry\(value\)/);
  assert.match(countrySelector, /setTempCity\(null\)/);
  assert.doesNotMatch(countrySelector, /updateCountry|setCookie/);
  assert.doesNotMatch(countrySelector, /has_price/, "service discovery must not require Product delivery pricing");
  assert.match(countrySelector, /active:\s*1/);
});

test("an explicit false disables the city query even while focused or open on mobile", () => {
  assert.equal(
    shouldEnableAsyncSelectQuery({
      queryKey: "v1/rest/cities",
      queryEnabled: false,
      isInputFocused: true,
      isMobile: false,
      isMobileDrawerOpen: false,
    }),
    false
  );
  assert.equal(
    shouldEnableAsyncSelectQuery({
      queryKey: "v1/rest/cities",
      queryEnabled: false,
      isInputFocused: false,
      isMobile: true,
      isMobileDrawerOpen: true,
    }),
    false
  );
});

test("a missing query key disables even an explicitly enabled query", () => {
  assert.equal(
    shouldEnableAsyncSelectQuery({
      queryKey: undefined,
      queryEnabled: true,
      isInputFocused: true,
      isMobile: true,
      isMobileDrawerOpen: true,
    }),
    false
  );
});

test("ordinary desktop focus and mobile drawer opening still enable async selects", () => {
  assert.equal(
    shouldEnableAsyncSelectQuery({
      queryKey: "v1/rest/countries",
      isInputFocused: true,
      isMobile: false,
      isMobileDrawerOpen: false,
    }),
    true
  );
  assert.equal(
    shouldEnableAsyncSelectQuery({
      queryKey: "v1/rest/cities",
      isInputFocused: false,
      isMobile: true,
      isMobileDrawerOpen: true,
    }),
    true
  );
});

test("the visible mobile arrow opens the same drawer that enables the catalogue query", () => {
  const arrow = asyncSelectSource.match(/<Combobox\.Button\b([\s\S]*?)<\/Combobox\.Button>/)?.[1];
  assert.ok(arrow);
  assert.match(arrow, /onClick=\{\(\) => \{\s*if \(isMobile\) setIsMobileDrawerOpen\(\(old\) => !old\);/);
  assert.match(asyncSelectSource, /show=\{isMobile \? isMobileDrawerOpen : open\}/);
  const state = {
    queryKey: "v1/rest/countries",
    isInputFocused: false,
    isMobile: true,
    isMobileDrawerOpen: false,
  };
  assert.equal(shouldEnableAsyncSelectQuery(state), false);
  assert.equal(shouldEnableAsyncSelectQuery({ ...state, isMobileDrawerOpen: true }), true);
});

test("exactly 768px uses desktop option visibility, matching the CSS md controls", () => {
  assert.match(asyncSelectSource, /useMediaQuery\("\(max-width: 767px\)"\)/);
  assert.match(asyncSelectSource, /peer hidden md:block/);
  assert.match(asyncSelectSource, /show=\{isMobile \? isMobileDrawerOpen : open\}/);
});
