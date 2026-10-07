const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const source = (relativePath) =>
  readFileSync(path.resolve(__dirname, "..", relativePath), "utf8");

const provider = source("context/search/search.provider.tsx");
const searchField = source("components/search-field-core/search-field-core.tsx");
const placeSelect = source("components/search-field-core/place-select.tsx");
const shops = source(
  "app/(store)/(booking)/(witout-footer)/(navigation)/search/components/shops/shops.tsx",
);
const markerCluster = source(
  "app/(store)/(booking)/(witout-footer)/(navigation)/search/components/shops-in-map/marker-cluster.tsx",
);
const map = source(
  "app/(store)/(booking)/(witout-footer)/(navigation)/search/components/shops-in-map/shops-in-map.tsx",
);

test("only browser-permission coordinates are restored or sent as a distance origin", () => {
  assert.match(provider, /location_source"\) === "browser"/);
  assert.match(searchField, /location_source: state\.location\.geolocation \? "browser" : undefined/);
  assert.doesNotMatch(searchField, /latitude: state\.location\.geolocation\?\.latitude \|\| settings/);
  assert.match(shops, /searchParams\.get\("location_source"\) === "browser"/);
  assert.match(markerCluster, /urlSearchParams\.get\("location_source"\) === "browser"/);
  assert.doesNotMatch(shops, /settings\?\.latitude|settings\?\.longitude/);
  assert.doesNotMatch(markerCluster, /settings\?\.latitude|settings\?\.longitude/);
});

test("manual place changes clear a prior user position and map panning does not write it", () => {
  assert.match(placeSelect, /payload: \{ query: inputValue \|\| "", geolocation: undefined \}/);
  assert.match(placeSelect, /payload: \{ query: value \|\| "", geolocation: undefined \}/);
  assert.match(map, /setQueryParams\(\{ distance: debouncedCenter\.distance \}, false\)/);
  assert.doesNotMatch(map, /setQueryParams\(debouncedCenter/);
});