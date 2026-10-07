"use strict";

const assert = require("node:assert/strict");
const test = require("node:test");
const { isMapsEnabled } = require("./runtime-config.cjs");

test("local mapping features do not load provider SDKs without explicit opt-in", () => {
  const local = {
    NEXT_PUBLIC_APP_ENV: "local",
    NEXT_PUBLIC_DEVELOPMENT_MODE: "true",
    NEXT_PUBLIC_GOOGLE_MAPS_KEY: "configured-public-key",
  };
  assert.equal(isMapsEnabled(local, "development"), false);
  assert.equal(
    isMapsEnabled({ ...local, NEXT_PUBLIC_MAPS_ENABLED: "true" }, "development"),
    true,
  );
  assert.equal(
    isMapsEnabled({ ...local, NEXT_PUBLIC_MAPS_ENABLED: "false" }, "development"),
    false,
  );
});