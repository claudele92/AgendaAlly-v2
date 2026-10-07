import assert from "node:assert/strict";
import test from "node:test";
import {
  getMapsKey,
  getRecaptchaMode,
  isFirebaseConfigured,
  resolveAdminRuntimeConfig,
  resolveAdminRuntimeConfigSafely,
} from "./runtime-config.mjs";

const valid = {
  VITE_APP_ENV: "local",
  VITE_DEVELOPMENT_MODE: "true",
  VITE_API_ORIGIN: "http://127.0.0.1:8000",
  VITE_STOREFRONT_URL: "http://127.0.0.1:3002",
  VITE_ADMIN_PANEL_URL: "http://127.0.0.1:3003",
  VITE_RECAPTCHA_DISABLED: "true",
};

test("requires separate, origin-rooted API, storefront, and admin URLs", () => {
  assert.equal(
    resolveAdminRuntimeConfig(valid, "serve", "development").apiOrigin,
    "http://127.0.0.1:8000",
  );
  for (const key of ["VITE_API_ORIGIN", "VITE_STOREFRONT_URL", "VITE_ADMIN_PANEL_URL"]) {
    assert.throws(
      () => resolveAdminRuntimeConfig({ ...valid, [key]: "" }, "serve", "development"),
      new RegExp(`${key} is required`),
    );
  }
  assert.throws(
    () => resolveAdminRuntimeConfig({ ...valid, VITE_API_ORIGIN: "ftp://api.example.test" }, "serve", "development"),
    /absolute HTTP\(S\) URL/,
  );
  assert.throws(
    () => resolveAdminRuntimeConfig({ ...valid, VITE_API_ORIGIN: "http://user:pw@example.test" }, "serve", "development"),
    /without credentials/,
  );
  assert.throws(
    () => resolveAdminRuntimeConfig({ ...valid, VITE_API_ORIGIN: "https://api.example.test/api/v1/" }, "serve", "development"),
    /must be an origin/,
  );
});

test("only non-production local Vite serve may bypass CAPTCHA", () => {
  assert.equal(getRecaptchaMode(valid, "serve", "development"), "disabled-local");
  assert.equal(getRecaptchaMode(valid, "build", "production"), "unavailable");
  assert.throws(
    () => resolveAdminRuntimeConfig({ ...valid, VITE_APP_ENV: "staging" }, "serve", "development"),
    /VITE_DEVELOPMENT_MODE=true is only valid/,
  );
  assert.throws(
    () => resolveAdminRuntimeConfig({ ...valid, VITE_RECAPTCHA_DISABLED: "true" }, "build", "production"),
    /VITE_DEVELOPMENT_MODE=true cannot be shipped/,
  );
});

test("local maps require explicit development opt-in, public key, and no production bypass", () => {
  const maps = { ...valid, VITE_MAP_API_KEY: "configured-public-key" };
  assert.equal(getMapsKey(maps, "development"), null);
  assert.equal(getMapsKey({ ...maps, VITE_MAPS_ENABLED: "false" }, "development"), null);
  assert.equal(
    getMapsKey({ ...maps, VITE_MAPS_ENABLED: "true" }, "development"),
    "configured-public-key",
  );
  assert.equal(
    getMapsKey(
      { ...valid, VITE_APP_ENV: "production", VITE_MAPS_ENABLED: "true" },
      "production",
      "server-provided-key",
    ),
    "server-provided-key",
  );
  assert.throws(
    () =>
      resolveAdminRuntimeConfig(
        { ...maps, VITE_MAPS_ENABLED: "true", VITE_RECAPTCHA_DISABLED: "false" },
        "build",
        "production",
      ),
        /VITE_DEVELOPMENT_MODE=true cannot be shipped/,
  );
});

test("provider SDKs remain disabled until opted in and fully configured", () => {
  assert.equal(isFirebaseConfigured({ VITE_FIREBASE_API_KEY: "ignored" }), false);
  assert.equal(
    isFirebaseConfigured({
      VITE_FIREBASE_ENABLED: "true",
      VITE_FIREBASE_API_KEY: "public-key",
      VITE_FIREBASE_AUTH_DOMAIN: "auth.example.test",
      VITE_FIREBASE_PROJECT_ID: "project",
      VITE_FIREBASE_STORAGE_BUCKET: "storage.example.test",
      VITE_FIREBASE_MESSAGING_SENDER_ID: "sender",
      VITE_FIREBASE_APP_ID: "app",
    }),
    true,
  );
  assert.throws(
    () =>
      resolveAdminRuntimeConfig(
        { ...valid, VITE_FIREBASE_ENABLED: "true" },
        "serve",
        "development",
      ),
    /Firebase was explicitly enabled.*missing/,
  );
});

test("local provider opt-ins require explicit local development mode", () => {
  assert.throws(
    () =>
      resolveAdminRuntimeConfig(
        { ...valid, VITE_DEVELOPMENT_MODE: "false", VITE_MAPS_ENABLED: "true" },
        "serve",
        "development",
      ),
    /Local Firebase\/Maps opt-ins require VITE_DEVELOPMENT_MODE/,
  );
  assert.throws(
    () =>
      resolveAdminRuntimeConfig(
        { ...valid, VITE_DEVELOPMENT_MODE: "false", VITE_FIREBASE_ENABLED: "true" },
        "serve",
        "development",
      ),
    /Local Firebase\/Maps opt-ins require VITE_DEVELOPMENT_MODE/,
  );
});

test("malformed app configuration can be reported without exposing environment values", () => {
  const malformed = {
    ...valid,
    VITE_API_ORIGIN: "https://invalid.example/path",
  };
  const result = resolveAdminRuntimeConfigSafely(
    malformed,
    "serve",
    "development",
  );

  assert.equal(result.runtime, null);
  assert.equal(
    result.error,
    "Application configuration is invalid. Contact your administrator.",
  );
  assert.equal(result.error.includes("invalid.example"), false);
});