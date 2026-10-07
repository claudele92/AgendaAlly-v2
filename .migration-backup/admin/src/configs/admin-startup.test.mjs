import assert from "node:assert/strict";
import { readFile } from "node:fs/promises";
import test from "node:test";
import {
  getAuthRouteDestination,
  getAuthenticatedDestination,
  LEGACY_PUBLIC_ROUTE_REDIRECT,
} from "./admin-startup.mjs";
import { resolveAdminRuntimeConfigSafely } from "./runtime-config.mjs";

const configuredEnvironment = {
  VITE_APP_ENV: "local",
  VITE_DEVELOPMENT_MODE: "true",
  VITE_API_ORIGIN: "http://127.0.0.1:8000",
  VITE_STOREFRONT_URL: "http://127.0.0.1:3002",
  VITE_ADMIN_PANEL_URL: "http://127.0.0.1:3003",
  VITE_RECAPTCHA_DISABLED: "true",
};

test("a configured unauthenticated admin stays on the normal login route", () => {
  const { runtime, error } = resolveAdminRuntimeConfigSafely(
    configuredEnvironment,
    "serve",
    "development",
  );

  assert.ok(runtime);
  assert.equal(error, "");
  assert.equal(getAuthRouteDestination(null), "/login");
});

test("an authenticated admin is sent to the selected role dashboard destination", () => {
  const user = { id: 42, role: "seller" };
  const activeRoleRoute = { url: "orders/seller" };

  assert.equal(
    getAuthRouteDestination(user, activeRoleRoute),
    "/orders/seller",
  );
  assert.equal(getAuthenticatedDestination(null), "/");
});

test("obsolete public routes redirect through the normal login/authentication path", async () => {
  const appSource = await readFile(new URL("../app.jsx", import.meta.url), "utf8");

  assert.equal(LEGACY_PUBLIC_ROUTE_REDIRECT, "/login");
  for (const path of ["/welcome", "/installation"]) {
    const escapedPath = path.replace("/", "\\/");
    assert.match(
      appSource,
      new RegExp(
        `path='${escapedPath}'[\\s\\S]*?Navigate to=\\{LEGACY_PUBLIC_ROUTE_REDIRECT\\}`,
      ),
    );
  }
  assert.doesNotMatch(appSource, /GlobalSettings|WelcomeLayout|views\/welcome/);
});

test("invalid runtime config visibly fails closed before startup requests", async () => {
  const appSource = await readFile(new URL("../app.jsx", import.meta.url), "utf8");

  assert.match(appSource, /if \(!ADMIN_RUNTIME_CONFIG_VALID\) return;/);
  assert.match(appSource, /message=\{ADMIN_RUNTIME_CONFIG_ERROR\}/);
  assert.match(appSource, /if \(!ADMIN_RUNTIME_CONFIG_VALID\) \{/);
});

test("normal login and obsolete-route startup paths do not probe installer endpoints", async () => {
  const paths = [
    "../context/path-logout.jsx",
    "../layout/welcome-layout.jsx",
    "../app.jsx",
    "../services/request.js",
    "../redux/slices/backup.js",
    "../views/system-information/index.jsx",
    "../services/admin-maintenance.js",
  ];
  const sources = await Promise.all(
    paths.map((path) => readFile(new URL(path, import.meta.url), "utf8")),
  );
  const startupSources = sources.join("\n");

  assert.doesNotMatch(startupSources, /installationService|checkInitFile/);
  assert.doesNotMatch(startupSources, /services\/installation/);
  assert.doesNotMatch(startupSources, /install\/init\/check|isDisabledInstallerCheck/);
  assert.match(startupSources, /error\.response\?\.status === 401/);
  assert.match(startupSources, /store\.dispatch\(clearUser\(\)\)/);
});