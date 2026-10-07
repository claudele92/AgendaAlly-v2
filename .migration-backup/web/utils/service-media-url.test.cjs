const { test } = require("node:test");
const assert = require("node:assert/strict");
const { resolveServiceMediaUrl } = require("./service-media-url.cjs");
const path = "/storage/images/services/shops/505/1000000000-d82c1c42-3195-c506-3e17-517a88d0ac4d.jpg";
const source = `https://preview.example.invalid:8000${path}`;
const config = {
  nodeEnvironment: "development",
  developmentMode: "true",
  appEnvironment: "local",
  imageBaseUrl: "http://127.0.0.1:8000/storage/",
};

test("native development photos use the configured optimizer origin", () => {
  assert.equal(resolveServiceMediaUrl(source, config), `http://127.0.0.1:8000${path}`);
  assert.equal(resolveServiceMediaUrl(path, config), `http://127.0.0.1:8000${path}`);
});
test("production and development without explicit opt-in remain untouched", () => {
  for (const override of [
    { nodeEnvironment: "production" },
    { developmentMode: "false" },
    { appEnvironment: "production" },
  ]) assert.equal(resolveServiceMediaUrl(source, { ...config, ...override }), source);
});
test("existing external and cloud photographs are untouched", () => {
  for (const src of [
    "https://foodyman.s3.amazonaws.com/public/images/services/photo.jpg",
    "https://images.unsplash.com/photo-example?w=800",
    "/icons/categories/hair-care.svg",
    null,
    { src: "/imported.jpg", width: 100, height: 100 },
  ]) assert.equal(resolveServiceMediaUrl(src, config), src);
});
test("noncanonical, signed, credentialed and private paths are not remapped", () => {
  for (const src of [
    `${source}?signature=example`, `${source}#fragment`,
    source.replace("https://", "https://user:password@"),
    "https://preview.example.invalid:8000/storage/private/file.jpg",
    "https://preview.example.invalid:8000/storage/images/services/shops/505/not-native.jpg",
  ]) assert.equal(resolveServiceMediaUrl(src, config), src);
});
test("only a configured loopback public-storage origin is accepted", () => {
  for (const imageBaseUrl of [
    undefined, "not-a-url", "https://cdn.example.invalid/storage/",
    "http://127.0.0.1:8000/private/", "http://127.0.0.1:8000/storage/?token=x",
  ]) assert.equal(resolveServiceMediaUrl(source, { ...config, imageBaseUrl }), source);
});