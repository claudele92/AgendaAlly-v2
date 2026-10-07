const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const webRoot = path.resolve(__dirname, "../../../../../../../../");
const workspaceRoot = path.resolve(webRoot, "../..");
const source = (relativePath) => readFileSync(path.join(webRoot, relativePath), "utf8");
const backendSource = (relativePath) =>
  readFileSync(path.join(workspaceRoot, ".migration-backup/backend", relativePath), "utf8");

const productCheckboxes = source(
  "app/(store)/(booking)/(with-footer)/(simple)/products/components/filters/checkboxes.tsx"
);
const productFilters = source(
  "app/(store)/(booking)/(with-footer)/(simple)/products/components/filters/filter-list.tsx"
);
const productList = source(
  "app/(store)/(booking)/(with-footer)/(simple)/products/components/filtered-product-list/product-list.tsx"
);
const sharedDrawer = source("components/drawer/drawer.tsx");

test("product facet checkboxes follow URL state after Clear all replaces the route", () => {
  assert.match(
    productCheckboxes,
    /checked=\{selectedItems\.includes\(valueExtractor\(item\)\.toString\(\)\)\}/
  );
  assert.doesNotMatch(productCheckboxes, /defaultChecked=/);
  assert.match(productCheckboxes, /const selectedItems = searchParams\.getAll\(queryKey \|\| title\)/);
  assert.match(productFilters, /queryKey="in_stock"/);
  assert.match(productFilters, /router\.replace\("\/products"\)/);
});

test("native category discovery callers send the required service type", () => {
  const categoryRequest = backendSource("app/Http/Requests/CategoryFilterRequest.php");
  assert.match(categoryRequest, /'type'\s*=>\s*'required'/);

  const callers = [
    "components/search-field-core/service-select.tsx",
    "app/(store)/(booking)/(witout-footer)/(navigation)/services/page.tsx",
    "app/(store)/(booking)/components/services/services.tsx",
    "app/(store)/(booking)/(with-footer)/(home)/page.tsx",
    "app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/services/services.tsx",
    "app/(store)/(business)/for-business/page.tsx",
  ];

  callers.forEach((relativePath) => {
    const code = source(relativePath);
    const calls = [
      ...code.matchAll(/categoryService[\s\S]{0,100}?getAll\(\s*\{([\s\S]*?)\}\s*\)/g),
    ];
    assert.ok(calls.length > 0, `${relativePath} should call the native category service`);
    calls.forEach((call) => assert.match(call[1], /type:\s*"service"/, relativePath));
  });
});

test("the native product facet request sends the required filter type", () => {
  const filterRequest = backendSource("app/Http/Requests/FilterRequest.php");
  assert.match(filterRequest, /'type'\s*=>\s*'required\|in:news_letter,category,most_sold'/);
  assert.match(productFilters, /type:\s*"category"/);
});

test("the product filters drawer stacks above the native header for pointer access", () => {
  assert.match(productList, /<Drawer[\s\S]*?zIndexClassName="z-50"/);
  assert.match(sharedDrawer, /zIndexClassName\?:\s*string/);
  assert.match(sharedDrawer, /className=\{clsx\("relative", zIndexClassName\)\}/);
});