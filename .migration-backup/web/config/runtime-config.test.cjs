"use strict";

const assert = require("node:assert/strict");
const test = require("node:test");
const {
  getFirebaseConfiguration,
  isMapsEnabled,
  normalizeApiBaseUrl,
  parseAppEnvironment,
  resolveRuntimeConfig,
} = require("./runtime-config.cjs");

const valid = {
  NEXT_PUBLIC_APP_ENV: "local",
  NEXT_PUBLIC_DEVELOPMENT_MODE: "true",
  NEXT_PUBLIC_BASE_URL: "http://127.0.0.1:8000/api/",
  NEXT_PUBLIC_WEBSITE_URL: "http://127.0.0.1:3002/",
  NEXT_PUBLIC_ADMIN_PANEL_URL: "http://127.0.0.1:3003/",
};

test("preserves the Laravel /api/v1 endpoint contract", () => {
  assert.equal(
    normalizeApiBaseUrl("https://api.example.test/api"),
    "https://api.example.test/api/",
  );
  assert.equal(
    normalizeApiBaseUrl("https://api.example.test/api/"),
    "https://api.example.test/api/",
  );
  assert.throws(() => normalizeApiBaseUrl("https://api.example.test/"), /exactly \/api\//);
  assert.throws(() => normalizeApiBaseUrl("https://api.example.test/api/v1/"), /exactly \/api\//);
  assert.throws(
    () => normalizeApiBaseUrl("https://api.example.test/backend/api/"),
    /exactly \/api\//,
  );
});

test("requires all absolute, independently configured app URLs", () => {
  for (const [key, replacement] of [
    ["NEXT_PUBLIC_BASE_URL", "https://api.example.test/api/"],
    ["NEXT_PUBLIC_WEBSITE_URL", "https://store.example.test/"],
    ["NEXT_PUBLIC_ADMIN_PANEL_URL", "https://admin.example.test/"],
  ]) {
    const resolved = resolveRuntimeConfig({ ...valid, [key]: replacement }, "development");
    assert.ok(resolved[key === "NEXT_PUBLIC_BASE_URL" ? "apiBaseUrl" : key.includes("WEBSITE") ? "websiteUrl" : "adminUrl"]);
  }

  assert.throws(
    () => resolveRuntimeConfig({ ...valid, NEXT_PUBLIC_WEBSITE_URL: "" }, "development"),
    /NEXT_PUBLIC_WEBSITE_URL is required/,
  );
  assert.throws(
    () => resolveRuntimeConfig({ ...valid, NEXT_PUBLIC_BASE_URL: "https://api.example.test" }, "development"),
    /exactly \/api\//,
  );
  assert.throws(
    () => resolveRuntimeConfig({ ...valid, NEXT_PUBLIC_DEVELOPMENT_MODE: "false" }, "production"),
    /must use HTTPS in production/,
  );
  assert.doesNotThrow(() =>
    resolveRuntimeConfig(
      {
        ...valid,
        NEXT_PUBLIC_BASE_URL: "https://api.example.test/api/",
        NEXT_PUBLIC_WEBSITE_URL: "https://store.example.test/",
        NEXT_PUBLIC_ADMIN_PANEL_URL: "https://admin.example.test/",
        NEXT_PUBLIC_DEVELOPMENT_MODE: "false",
      },
      "production",
    ),
  );
});

test("rejects non-web, credential-bearing and malformed URLs", () => {
  for (const value of [
    "file:///etc/passwd",
    "ftp://api.example.test/api/",
    "http://user:password@api.example.test/api/",
    "http://api.example.test/api/?secret=value",
    "/api/",
    "http://[::1",
  ]) {
    assert.throws(() => normalizeApiBaseUrl(value));
  }
});

test("unrecognized app environments fail instead of enabling local shortcuts", () => {
  for (const value of ["", "preview", "test", "prod-ish"]) {
    if (!value) {
      assert.equal(parseAppEnvironment(undefined), "production");
    } else {
      assert.throws(() => parseAppEnvironment(value), /must be local, development/);
    }
  }

  assert.equal(
    resolveRuntimeConfig({
      ...valid,
      NEXT_PUBLIC_BASE_URL: "https://api.example.test/api/",
      NEXT_PUBLIC_WEBSITE_URL: "https://store.example.test/",
      NEXT_PUBLIC_ADMIN_PANEL_URL: "https://admin.example.test/",
      NEXT_PUBLIC_DEVELOPMENT_MODE: "false",
    }, "production").developmentServer,
    false,
    "A production build remains production even with an explicitly local deploy value.",
  );
  assert.throws(
    () =>
      resolveRuntimeConfig(
        {
          ...valid,
          NEXT_PUBLIC_BASE_URL: "https://api.example.test/api/",
          NEXT_PUBLIC_WEBSITE_URL: "https://store.example.test/",
          NEXT_PUBLIC_ADMIN_PANEL_URL: "https://admin.example.test/",
          NEXT_PUBLIC_DEVELOPMENT_MODE: "false",
          NEXT_PUBLIC_MAPS_ENABLED: "true",
        },
        "production",
      ),
    /Local Firebase\/Maps opt-ins require NEXT_PUBLIC_DEVELOPMENT_MODE=true/,
  );
  assert.equal(resolveRuntimeConfig(valid, "development").developmentServer, true);
});

test("explicit production Firebase enablement requires real configuration keys", () => {
  assert.throws(
    () =>
      resolveRuntimeConfig(
        {
          ...valid,
          NEXT_PUBLIC_APP_ENV: "production",
          NEXT_PUBLIC_DEVELOPMENT_MODE: "false",
          NEXT_PUBLIC_BASE_URL: "https://api.example.test/api/",
          NEXT_PUBLIC_WEBSITE_URL: "https://store.example.test/",
          NEXT_PUBLIC_ADMIN_PANEL_URL: "https://admin.example.test/",
          NEXT_PUBLIC_FIREBASE_ENABLED: "true",
        },
        "production",
      ),
    /Firebase was explicitly enabled.*missing/,
  );

  assert.doesNotThrow(() =>
    resolveRuntimeConfig(
      {
        ...valid,
        NEXT_PUBLIC_FIREBASE_ENABLED: "true",
        NEXT_PUBLIC_APP_ENV: "production",
        NEXT_PUBLIC_DEVELOPMENT_MODE: "false",
        NEXT_PUBLIC_BASE_URL: "https://api.example.test/api/",
        NEXT_PUBLIC_WEBSITE_URL: "https://store.example.test/",
        NEXT_PUBLIC_ADMIN_PANEL_URL: "https://admin.example.test/",
        NEXT_PUBLIC_API_KEY: "user-provided-public-key",
        NEXT_PUBLIC_AUTH_DOMAIN: "auth.example.test",
        NEXT_PUBLIC_PROJECT_ID: "agendalley-project",
        NEXT_PUBLIC_STORAGE_BUCKET: "storage.example.test",
        NEXT_PUBLIC_MESSAGING_SENDER_ID: "sender-id",
        NEXT_PUBLIC_APP_ID: "public-app-id",
      },
      "production",
    ),
  );
});

test("the Firebase adapter stays off until explicit enablement and full public configuration", () => {
  assert.equal(getFirebaseConfiguration({ NEXT_PUBLIC_API_KEY: "ignored" }), null);
  assert.throws(
    () => getFirebaseConfiguration({ NEXT_PUBLIC_FIREBASE_ENABLED: "true" }),
    /explicitly enabled.*not configured/,
  );

  const config = getFirebaseConfiguration({
    NEXT_PUBLIC_FIREBASE_ENABLED: "true",
    NEXT_PUBLIC_DEVELOPMENT_MODE: "false",
    NEXT_PUBLIC_API_KEY: "configured-public-key",
    NEXT_PUBLIC_AUTH_DOMAIN: "auth.example.test",
    NEXT_PUBLIC_PROJECT_ID: "agendalley-project",
    NEXT_PUBLIC_STORAGE_BUCKET: "storage.example.test",
    NEXT_PUBLIC_MESSAGING_SENDER_ID: "sender",
    NEXT_PUBLIC_APP_ID: "configured-app",
  });
  assert.equal(config.projectId, "agendalley-project");
  assert.equal(config.measurementId, undefined);
});

test("local Maps requires both explicit opt-in and a configured provider key", () => {
  const local = {
    NEXT_PUBLIC_APP_ENV: "local",
    NEXT_PUBLIC_DEVELOPMENT_MODE: "true",
    NEXT_PUBLIC_GOOGLE_MAPS_KEY: "configured-public-key",
  };
  assert.equal(isMapsEnabled(local, "development"), false);
  assert.equal(isMapsEnabled({ ...local, NEXT_PUBLIC_MAPS_ENABLED: "false" }, "development"), false);
  assert.equal(
    isMapsEnabled({ ...local, NEXT_PUBLIC_MAPS_ENABLED: "true" }, "development"),
    true,
  );
  assert.equal(
    isMapsEnabled(
      { ...local, NEXT_PUBLIC_MAPS_ENABLED: "true" },
      "production",
    ),
    false,
    "A local development opt-in cannot enable Maps in a production build.",
  );
  assert.equal(
    isMapsEnabled(
      {
        NEXT_PUBLIC_APP_ENV: "local",
        NEXT_PUBLIC_DEVELOPMENT_MODE: "true",
        NEXT_PUBLIC_MAPS_ENABLED: "true",
      },
      "development",
    ),
    false,
    "An enabled toggle cannot load Google Maps without a provider key.",
  );
});

test("local provider opt-ins require explicit local development mode", () => {
  assert.throws(
    () =>
      resolveRuntimeConfig(
        { ...valid, NEXT_PUBLIC_DEVELOPMENT_MODE: "false", NEXT_PUBLIC_MAPS_ENABLED: "true" },
        "development",
      ),
    /Local Firebase\/Maps opt-ins require NEXT_PUBLIC_DEVELOPMENT_MODE/,
  );
  assert.throws(
    () =>
      resolveRuntimeConfig(
        { ...valid, NEXT_PUBLIC_DEVELOPMENT_MODE: "false", NEXT_PUBLIC_FIREBASE_ENABLED: "true" },
        "development",
      ),
    /Local Firebase\/Maps opt-ins require NEXT_PUBLIC_DEVELOPMENT_MODE/,
  );
});