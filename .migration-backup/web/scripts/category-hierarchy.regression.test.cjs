const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const Module = require("node:module");
const ts = require("typescript");
const React = require("react");
const { renderToStaticMarkup } = require("react-dom/server");

const sourcePath = path.join(__dirname, "../utils/category-hierarchy.ts");
const compiled = ts.transpileModule(fs.readFileSync(sourcePath, "utf8"), {
  compilerOptions: { module: ts.ModuleKind.CommonJS, target: ts.ScriptTarget.ES2020 },
}).outputText;
const helperModule = new Module(sourcePath, module);
helperModule.filename = sourcePath;
helperModule.paths = Module._nodeModulePaths(path.dirname(sourcePath));
helperModule._compile(compiled, sourcePath);
const { getCategoryChildren, getCategoryHierarchy } = helperModule.exports;

const category = (id, title, parent_id = 0, children = []) => ({
  id,
  parent_id,
  translation: { title },
  children,
});
const nestedChild = category(11, "Haircut", 10);
const carService = category(
  40,
  "Car Service",
  0,
  [
    "Auto Repair & Maintenance",
    "Oil Change",
    "Tire Service",
    "Car Wash & Detailing",
    "Auto Electrical",
    "Diagnostics",
    "Brake Service",
  ].map((title, index) => category(41 + index, title, 40))
);
const roots = getCategoryHierarchy([
  category(10, "Hair Care", 0, [nestedChild]),
  category(11, "Haircut", 10),
  category(12, "Hair Coloring", 10),
  category(20, "Education", null),
  { id: 30, translation: { title: "Education 2" }, children: [] },
  category(90, "Unattached child", 77),
  carService,
]);

assert.deepEqual(
  roots.map(({ category: root }) => root.id),
  [10, 20, 30, 40],
  "null, zero and omitted parent_id values are explicit roots"
);
assert.deepEqual(
  roots[0].children.map(({ category: child }) => child.id),
  [11, 12],
  "actual parent_id children and nested children are exposed once"
);
assert.equal(roots[1].children.length, 0, "roots without children remain browsable");
assert.deepEqual(
  roots[3].children.map(({ title }) => title),
  [
    "Auto Repair & Maintenance",
    "Oil Change",
    "Tire Service",
    "Car Wash & Detailing",
    "Auto Electrical",
    "Diagnostics",
    "Brake Service",
  ],
  "Car Service children remain nested and cannot leak into the root grid"
);

const childrenEndpointFixture = {
  data: {
    id: 1,
    translation: { title: "Hair Care" },
    children: [category(2, "Haircut", 1), category(3, "Hair Coloring", 1)],
    parent: null,
  },
};
assert.deepEqual(
  getCategoryChildren(childrenEndpointFixture).map(({ id }) => id),
  [2, 3],
  "the parent-wrapped children endpoint exposes its direct child records"
);

const iconPath = path.join(__dirname, "../components/stage2/category-pictogram.tsx");
const iconCompiled = ts.transpileModule(fs.readFileSync(iconPath, "utf8"), {
  compilerOptions: {
    jsx: ts.JsxEmit.ReactJSX,
    module: ts.ModuleKind.CommonJS,
    target: ts.ScriptTarget.ES2020,
  },
}).outputText;
const iconModule = new Module(iconPath, module);
iconModule.filename = iconPath;
iconModule.paths = Module._nodeModulePaths(path.dirname(iconPath));
iconModule._compile(iconCompiled, iconPath);
const { CategoryPictogram } = iconModule.exports;

for (const title of [
  "Hair Care",
  "Nail Care",
  "Spa & Massage",
  "Makeup",
  "Barbershop",
  "Skin Care",
  "Tailoring",
  "Dental Care",
  "Healthcare",
  "Handyman",
  "Laundry & Dry Cleaning",
  "Home Cleaning",
  "Education",
  "Tattoo & Piercing",
  "Car Service",
  "Beauty & Personal Care",
  "Tailoring & Apparel Supplies",
]) {
  const markup = renderToStaticMarkup(React.createElement(CategoryPictogram, { category: title }));
  assert.match(markup, /<svg[^>]+viewBox="0 0 64 64"/, `${title} uses the shared frame`);
  assert.match(markup, /<path/, `${title} uses a semantic authored mark`);
}

const hairByTitle = renderToStaticMarkup(
  React.createElement(CategoryPictogram, { category: "Hair Care" })
);
const hairById = renderToStaticMarkup(
  React.createElement(CategoryPictogram, { category: "Soins capillaires", categoryId: 1 })
);
assert.equal(hairById, hairByTitle, "stable service IDs preserve icons across locales");
for (const [id, title] of [
  [1, "Hair Care"],
  [4, "Nail Care"],
  [7, "Spa & Massage"],
  [10, "Makeup"],
  [13, "Barbershop"],
  [16, "Skin Care"],
  [19, "Tailoring"],
  [24, "Dental Care"],
  [28, "Healthcare"],
  [32, "Handyman"],
  [36, "Laundry & Dry Cleaning"],
  [40, "Home Cleaning"],
  [44, "Education"],
  [50, "Tattoo & Piercing"],
]) {
  const expected = renderToStaticMarkup(
    React.createElement(CategoryPictogram, { category: title })
  );
  const localized = renderToStaticMarkup(
    React.createElement(CategoryPictogram, { category: `localized-${id}`, categoryId: id })
  );
  assert.equal(localized, expected, `service root ${id} retains its mapped semantic icon`);
}
const nailsBySlug = renderToStaticMarkup(
  React.createElement(CategoryPictogram, {
    category: "Soins des ongles",
    imageRef: "/assets/category-icons/nail-care-icon.svg",
  })
);
const nailsByTitle = renderToStaticMarkup(
  React.createElement(CategoryPictogram, { category: "Nail Care" })
);
assert.equal(nailsBySlug, nailsByTitle, "semantic image slugs resolve before translated titles");

const unknownMarkup = renderToStaticMarkup(
  React.createElement(CategoryPictogram, { category: "Unsupported category" })
);
assert.match(unknownMarkup, /<path/, "unknown labels retain a useful generic category glyph");
assert.doesNotMatch(unknownMarkup, /<circle/, "unknown labels no longer collapse to a circle");

const categoryGridSource = require("node:fs").readFileSync(
  require("node:path").join(
    __dirname,
    "../app/(store)/(booking)/(with-footer)/(home)/components/service-categories-grid.tsx"
  ),
  "utf8"
);
assert.match(
  categoryGridSource,
  /data: childrenResponse,\s*isInitialLoading: isChildrenLoading,[\s\S]*?\["service-category-children"/,
  "the child query uses fetching-aware initial loading, not disabled-query pending state"
);
assert.doesNotMatch(categoryGridSource, /isLoading: isChildrenLoading/);

const serviceDiscoverySource = require("node:fs").readFileSync(
  require("node:path").join(
    __dirname,
    "../app/(store)/(booking)/(witout-footer)/(navigation)/services/page.tsx"
  ),
  "utf8"
);
assert.match(serviceDiscoverySource, /perPage: 100,\s*type: "service"/,
  "the small service-root taxonomy is not cut off at twelve categories");