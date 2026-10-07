"use strict";

const DEVELOPMENT_ENVIRONMENTS = new Set(["local", "development"]);

function requireHttpUrl(value, name) {
  if (!value) {
    throw new Error(`${name} is required. Copy .env.example and set the app URLs.`);
  }

  let parsed;
  try {
    parsed = new URL(value);
  } catch {
    throw new Error(`${name} must be an absolute HTTP(S) URL.`);
  }

  if (
    !["http:", "https:"].includes(parsed.protocol) ||
    parsed.username ||
    parsed.password ||
    parsed.search ||
    parsed.hash
  ) {
    throw new Error(`${name} must be an absolute HTTP(S) URL without credentials or query data.`);
  }

  return parsed;
}

function normalizeApiBaseUrl(value, name = "NEXT_PUBLIC_BASE_URL") {
  const parsed = requireHttpUrl(value, name);
  if (parsed.pathname !== "/api" && parsed.pathname !== "/api/") {
    throw new Error(`${name} must use exactly /api/ at the origin root to preserve the /api/v1/ contract.`);
  }
  parsed.pathname = "/api/";
  return parsed.toString();
}

function parseAppEnvironment(value) {
  const appEnvironment = value?.trim().toLowerCase() || "production";
  if (!["local", "development", "staging", "production"].includes(appEnvironment)) {
    throw new Error("NEXT_PUBLIC_APP_ENV must be local, development, staging or production.");
  }
  return appEnvironment;
}

function resolveRuntimeConfig(env, nodeEnvironment) {
  const appEnvironment = parseAppEnvironment(env.NEXT_PUBLIC_APP_ENV);
  const developmentMode = env.NEXT_PUBLIC_DEVELOPMENT_MODE === "true";
  if (developmentMode && !DEVELOPMENT_ENVIRONMENTS.has(appEnvironment)) {
    throw new Error("NEXT_PUBLIC_DEVELOPMENT_MODE=true is only valid for a local/development app environment.");
  }
  if (developmentMode && nodeEnvironment === "production") {
    throw new Error("NEXT_PUBLIC_DEVELOPMENT_MODE=true cannot be shipped in a production build.");
  }
  if (
    appEnvironment === "local" &&
    !developmentMode &&
    (env.NEXT_PUBLIC_FIREBASE_ENABLED === "true" || env.NEXT_PUBLIC_MAPS_ENABLED === "true")
  ) {
    throw new Error(
      "Local Firebase/Maps opt-ins require NEXT_PUBLIC_DEVELOPMENT_MODE=true as well as the provider enable flag.",
    );
  }
  const developmentServer =
    nodeEnvironment !== "production" &&
    DEVELOPMENT_ENVIRONMENTS.has(appEnvironment) &&
    developmentMode;
  if (
    nodeEnvironment === "production" &&
    appEnvironment === "local" &&
    (env.NEXT_PUBLIC_FIREBASE_ENABLED === "true" || env.NEXT_PUBLIC_MAPS_ENABLED === "true")
  ) {
    throw new Error(
      "A local-only Firebase/Maps opt-in cannot be shipped in a production build. Use the staging or production provider configuration.",
    );
  }
  const apiBaseUrl = normalizeApiBaseUrl(env.NEXT_PUBLIC_BASE_URL);
  const websiteUrl = requireHttpUrl(env.NEXT_PUBLIC_WEBSITE_URL, "NEXT_PUBLIC_WEBSITE_URL");
  const adminUrl = requireHttpUrl(env.NEXT_PUBLIC_ADMIN_PANEL_URL, "NEXT_PUBLIC_ADMIN_PANEL_URL");
  if (websiteUrl.pathname !== "/" || adminUrl.pathname !== "/") {
    throw new Error(
      "NEXT_PUBLIC_WEBSITE_URL and NEXT_PUBLIC_ADMIN_PANEL_URL must be origin-rooted; application subpaths are not supported.",
    );
  }

  if (nodeEnvironment === "production") {
    for (const [name, url] of [
      ["NEXT_PUBLIC_BASE_URL", new URL(apiBaseUrl)],
      ["NEXT_PUBLIC_WEBSITE_URL", websiteUrl],
      ["NEXT_PUBLIC_ADMIN_PANEL_URL", adminUrl],
    ]) {
      if (url.protocol !== "https:") {
        throw new Error(`${name} must use HTTPS in production.`);
      }
    }
  }

  if (env.NEXT_PUBLIC_FIREBASE_ENABLED === "true" && nodeEnvironment === "production") {
    const requiredFirebaseKeys = [
      "NEXT_PUBLIC_API_KEY",
      "NEXT_PUBLIC_AUTH_DOMAIN",
      "NEXT_PUBLIC_PROJECT_ID",
      "NEXT_PUBLIC_STORAGE_BUCKET",
      "NEXT_PUBLIC_MESSAGING_SENDER_ID",
      "NEXT_PUBLIC_APP_ID",
    ];
    const missing = requiredFirebaseKeys.filter((key) => !env[key]?.trim());
    if (missing.length) {
      throw new Error(
        `Firebase was explicitly enabled, but configuration is missing: ${missing.join(", ")}.`,
      );
    }
  }

  return {
    appEnvironment,
    developmentServer,
    apiBaseUrl,
    websiteUrl: websiteUrl.toString(),
    adminUrl: adminUrl.toString(),
    apiOrigin: new URL(apiBaseUrl).origin,
  };
}

function getFirebaseConfiguration(env = process.env) {
  if (env.NEXT_PUBLIC_FIREBASE_ENABLED !== "true") {
    return null;
  }

  const required = [
    "NEXT_PUBLIC_API_KEY",
    "NEXT_PUBLIC_AUTH_DOMAIN",
    "NEXT_PUBLIC_PROJECT_ID",
    "NEXT_PUBLIC_STORAGE_BUCKET",
    "NEXT_PUBLIC_MESSAGING_SENDER_ID",
    "NEXT_PUBLIC_APP_ID",
  ];
  const missing = required.filter((key) => !env[key]?.trim());
  if (missing.length) {
    throw new Error(
      `Firebase was explicitly enabled but is not configured. Missing: ${missing.join(", ")}.`,
    );
  }
  return {
    apiKey: env.NEXT_PUBLIC_API_KEY,
    authDomain: env.NEXT_PUBLIC_AUTH_DOMAIN,
    projectId: env.NEXT_PUBLIC_PROJECT_ID,
    storageBucket: env.NEXT_PUBLIC_STORAGE_BUCKET,
    messagingSenderId: env.NEXT_PUBLIC_MESSAGING_SENDER_ID,
    appId: env.NEXT_PUBLIC_APP_ID,
    ...(env.NEXT_PUBLIC_MEASUREMENT_ID
      ? { measurementId: env.NEXT_PUBLIC_MEASUREMENT_ID }
      : {}),
  };
}

function shouldEnableMaps(environment, nodeEnvironment, options = {}) {
  options = { ...options };
  if (options.runtimeEnabled !== undefined) options.runtimeEnabled =
    options.runtimeEnabled === true || options.runtimeEnabled === "1" || options.runtimeEnabled === "true";
  if (options.environmentPermitted !== undefined) options.environmentPermitted =
    options.environmentPermitted === true || options.environmentPermitted === "1" || options.environmentPermitted === "true";
  if (options.runtimeEnabled === false || options.environmentPermitted === false) return false;
  const localApp = environment.APP_ENV === "local";
  const developmentServer =
    nodeEnvironment !== "production" &&
    DEVELOPMENT_ENVIRONMENTS.has(environment.APP_ENV) &&
    environment.DEVELOPMENT_MODE === "true";
  if (localApp && !developmentServer) return false;
  if (environment.MAPS_ENABLED === "false" && !(developmentServer && options.runtimeEnabled === true)) return false;
  if (developmentServer && environment.MAPS_ENABLED !== "true" && options.runtimeEnabled !== true) return false;
  return Boolean(options.serverKey || environment.MAPS_KEY?.trim());
}

function isMapsEnabled(env, nodeEnvironment, configuredKey) {
  return shouldEnableMaps(
    {
      APP_ENV: env.NEXT_PUBLIC_APP_ENV,
      DEVELOPMENT_MODE: env.NEXT_PUBLIC_DEVELOPMENT_MODE,
      MAPS_ENABLED: env.NEXT_PUBLIC_MAPS_ENABLED,
      MAPS_KEY: env.NEXT_PUBLIC_GOOGLE_MAPS_KEY,
    },
    nodeEnvironment,
    { serverKey: configuredKey },
  );
}

module.exports = {
  getFirebaseConfiguration,
  isMapsEnabled,
  normalizeApiBaseUrl,
  parseAppEnvironment,
  requireHttpUrl,
  resolveRuntimeConfig,
  shouldEnableMaps,
};