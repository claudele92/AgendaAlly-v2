"use strict";

const DEFAULT_DEV_API_TARGET = "http://127.0.0.1:8000";

function resolveDevApiTarget(value) {
  const configuredTarget = value?.trim() || DEFAULT_DEV_API_TARGET;
  let target;

  try {
    target = new URL(configuredTarget);
  } catch {
    throw new Error("AGENDAALLY_DEV_API_TARGET must be an absolute HTTP(S) origin");
  }

  if (
    !["http:", "https:"].includes(target.protocol) ||
    target.username ||
    target.password ||
    target.pathname !== "/" ||
    target.search ||
    target.hash
  ) {
    throw new Error(
      "AGENDAALLY_DEV_API_TARGET must be an HTTP(S) origin without credentials, path, query, or fragment",
    );
  }

  return target.origin;
}

module.exports = { DEFAULT_DEV_API_TARGET, resolveDevApiTarget };