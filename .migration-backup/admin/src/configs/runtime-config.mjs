const APP_ENVIRONMENTS = new Set(["local", "development", "staging", "production"]);

export const ADMIN_RUNTIME_CONFIG_ERROR =
  "Application configuration is invalid. Contact your administrator.";

function absoluteHttpUrl(value, name) {
  if (!value) {
    throw new Error(`${name} is required. Copy .env.example and set this application URL.`);
  }

  let url;
  try {
    url = new URL(value);
  } catch {
    throw new Error(`${name} must be an absolute HTTP(S) URL.`);
  }
  if (
    !["http:", "https:"].includes(url.protocol) ||
    url.username ||
    url.password ||
    url.search ||
    url.hash
  ) {
    throw new Error(`${name} must be an absolute HTTP(S) URL without credentials or query data.`);
  }
  return url;
}

export function resolveAdminRuntimeConfig(env, command, nodeEnvironment) {
  const appEnvironment = (env.VITE_APP_ENV || "").trim().toLowerCase();
  if (!APP_ENVIRONMENTS.has(appEnvironment)) {
    throw new Error("VITE_APP_ENV is required and must be local, development, staging or production.");
  }
  const developmentMode = env.VITE_DEVELOPMENT_MODE === "true";
  if (developmentMode && !["local", "development"].includes(appEnvironment)) {
    throw new Error("VITE_DEVELOPMENT_MODE=true is only valid for a local/development app environment.");
  }
  if (developmentMode && nodeEnvironment === "production") {
    throw new Error("VITE_DEVELOPMENT_MODE=true cannot be shipped in a production build.");
  }
  if (
    appEnvironment === "local" &&
    !developmentMode &&
    (env.VITE_FIREBASE_ENABLED === "true" || env.VITE_MAPS_ENABLED === "true")
  ) {
    throw new Error(
      "Local Firebase/Maps opt-ins require VITE_DEVELOPMENT_MODE=true as well as the provider enable flag.",
    );
  }

  const apiUrl = absoluteHttpUrl(env.VITE_API_ORIGIN || env.VITE_BASE_URL, "VITE_API_ORIGIN");
  if (apiUrl.pathname !== "/" || env.VITE_API_ORIGIN && env.VITE_BASE_URL) {
    throw new Error(
      "VITE_API_ORIGIN must be an origin with no path. Remove the deprecated VITE_BASE_URL when VITE_API_ORIGIN is set.",
    );
  }

  const websiteUrl = absoluteHttpUrl(
    env.VITE_STOREFRONT_URL || env.VITE_WEBSITE_URL,
    "VITE_STOREFRONT_URL",
  );
  const adminUrl = absoluteHttpUrl(env.VITE_ADMIN_PANEL_URL, "VITE_ADMIN_PANEL_URL");
  if (websiteUrl.pathname !== "/" || adminUrl.pathname !== "/") {
    throw new Error(
      "VITE_STOREFRONT_URL and VITE_ADMIN_PANEL_URL must be origin-rooted; application paths are not supported.",
    );
  }
  const developmentServer =
    command === "serve" &&
    nodeEnvironment !== "production" &&
    developmentMode &&
    (appEnvironment === "local" || appEnvironment === "development");

  if (nodeEnvironment === "production") {
    for (const [name, url] of [
      ["VITE_API_ORIGIN", apiUrl],
      ["VITE_STOREFRONT_URL", websiteUrl],
      ["VITE_ADMIN_PANEL_URL", adminUrl],
    ]) {
      if (url.protocol !== "https:") {
        throw new Error(`${name} must use HTTPS in a production build.`);
      }
    }
    if (
      appEnvironment === "local" &&
      (env.VITE_MAPS_ENABLED === "true" || env.VITE_RECAPTCHA_DISABLED === "true")
    ) {
      throw new Error(
        "Production builds cannot ship local-only Maps/ CAPTCHA flags. Use a staging or production app environment.",
      );
    }
  }

  if (
    env.VITE_RECAPTCHA_DISABLED === "true" &&
    (appEnvironment !== "local" || !developmentServer)
  ) {
    throw new Error(
      "VITE_RECAPTCHA_DISABLED=true is allowed only for local Vite development. Never bypass production or staging CAPTCHA.",
    );
  }
  if (
    command === "build" &&
    env.VITE_RECAPTCHA_SITE_KEY === "6LezTuMqAAAAAOVRhcik9PO-dE-m1NnmL8uqE7UN"
  ) {
    throw new Error("The legacy demo CAPTCHA key cannot be used to build AgendaAlly.");
  }
  if (
    command === "build" &&
    !env.VITE_RECAPTCHA_SITE_KEY?.trim()
  ) {
    throw new Error(
      "VITE_RECAPTCHA_SITE_KEY is required for a production admin build; local development may instead explicitly disable CAPTCHA.",
    );
  }
  if (env.VITE_FIREBASE_ENABLED === "true") {
    const required = [
      "VITE_FIREBASE_API_KEY",
      "VITE_FIREBASE_AUTH_DOMAIN",
      "VITE_FIREBASE_PROJECT_ID",
      "VITE_FIREBASE_STORAGE_BUCKET",
      "VITE_FIREBASE_MESSAGING_SENDER_ID",
      "VITE_FIREBASE_APP_ID",
    ];
    const missing = required.filter((key) => !env[key]?.trim());
    if (missing.length) {
      throw new Error(
        `Firebase was explicitly enabled, but admin configuration is missing: ${missing.join(", ")}.`,
      );
    }
  }

  const allowedHosts = (env.VITE_ALLOWED_HOSTS || "")
    .split(",")
    .map((host) => host.trim())
    .filter(Boolean);
  if (allowedHosts.some((host) => host === "*" || host.includes("://") || host.includes("/"))) {
    throw new Error("VITE_ALLOWED_HOSTS must be a comma-separated list of explicit hostnames.");
  }

  const port = env.VITE_PORT === undefined ? 3003 : Number(env.VITE_PORT);
  if (!Number.isInteger(port) || port < 1 || port > 65535) {
    throw new Error("VITE_PORT must be an integer from 1 through 65535.");
  }

  return {
    appEnvironment,
    developmentServer,
    apiOrigin: apiUrl.origin,
    websiteUrl: websiteUrl.toString(),
    adminUrl: adminUrl.toString(),
    allowedHosts,
    port,
    mapApiKey: env.VITE_MAP_API_KEY?.trim() || "",
    mapsEnabled: env.VITE_MAPS_ENABLED === "true",
    recaptchaSiteKey: env.VITE_RECAPTCHA_SITE_KEY?.trim() || "",
    recaptchaDisabled: env.VITE_RECAPTCHA_DISABLED === "true",
    firebaseEnabled: env.VITE_FIREBASE_ENABLED === "true",
  };
}

export function resolveAdminRuntimeConfigSafely(env, command, nodeEnvironment) {
  try {
    return {
      runtime: resolveAdminRuntimeConfig(env, command, nodeEnvironment),
      error: "",
    };
  } catch {
    return {
      runtime: null,
      error: ADMIN_RUNTIME_CONFIG_ERROR,
    };
  }
}

export function getMapsKey(env, nodeEnvironment, runtimeSettingsKey = "", runtimeEnabled, environmentPermitted = true) {
  if (runtimeEnabled !== undefined) runtimeEnabled = runtimeEnabled === true || runtimeEnabled === '1' || runtimeEnabled === 'true';
  environmentPermitted = environmentPermitted === true || environmentPermitted === '1' || environmentPermitted === 'true';
  if (runtimeEnabled === false || environmentPermitted === false) return null;
  const isLocal =
    nodeEnvironment !== "production" &&
    env.VITE_APP_ENV === "local" &&
    env.VITE_DEVELOPMENT_MODE === "true";
  if (env.VITE_APP_ENV === "local" && !isLocal) return null;
  if (env.VITE_MAPS_ENABLED === "false" && !(isLocal && runtimeEnabled === true)) return null;
  if (isLocal && env.VITE_MAPS_ENABLED !== "true" && runtimeEnabled !== true) return null;
  return runtimeSettingsKey?.trim() || env.VITE_MAP_API_KEY?.trim() || null;
}

export function getRecaptchaMode(env, command, nodeEnvironment) {
  if (
    env.VITE_RECAPTCHA_DISABLED === "true" &&
    env.VITE_APP_ENV === "local" &&
    env.VITE_DEVELOPMENT_MODE === "true" &&
    command === "serve" &&
    nodeEnvironment !== "production"
  ) {
    return "disabled-local";
  }
  if (!env.VITE_RECAPTCHA_SITE_KEY?.trim()) return "unavailable";
  return "enabled";
}

export function isFirebaseConfigured(env) {
  if (env.VITE_FIREBASE_ENABLED !== "true") return false;
  return [
    "VITE_FIREBASE_API_KEY",
    "VITE_FIREBASE_AUTH_DOMAIN",
    "VITE_FIREBASE_PROJECT_ID",
    "VITE_FIREBASE_STORAGE_BUCKET",
    "VITE_FIREBASE_MESSAGING_SENDER_ID",
    "VITE_FIREBASE_APP_ID",
  ].every((key) => env[key]?.trim());
}