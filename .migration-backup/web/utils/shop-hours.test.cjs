const { test } = require("node:test");
const assert = require("node:assert/strict");
const { spawnSync } = require("node:child_process");
const { shopHours } = require("./shop-hours.cjs");
const schedule = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"]
  .map(day => ({ day, from: "09:00", to: "18:00", disabled: day === "sunday" }));
const shop = { open: true, hours_timezone: "Africa/Douala", shop_working_days: schedule };
const at = time => shopHours(shop, Date.parse(time));
test("opening inclusive, closing exclusive, shop-local date and disabled day", () => {
  assert.equal(at("2026-10-02T07:59:00Z").closed, true);
  assert.equal(at("2026-10-02T08:00:00Z").closed, false);
  assert.equal(at("2026-10-02T16:59:00Z").closed, false);
  assert.equal(at("2026-10-02T17:00:00Z").closed, true);
  assert.equal(at("2026-10-03T23:00:00Z").today.day, "sunday");
  assert.equal(at("2026-10-03T23:00:00Z").closed, true);
});
test("holiday is a local calendar date; manual closure wins", () => {
  assert.equal(shopHours({ ...shop, shop_closed_date: [{ day: "2026-10-03" }] }, Date.parse("2026-10-03T09:00:00Z")).closed, true);
  assert.equal(shopHours({ ...shop, open: false }, Date.parse("2026-10-02T10:00:00Z")).closed, true);
});
test("overnight interval and displayed interval stay paired", () => {
  const overnight = { ...shop, shop_working_days: [{day:"friday",from:"22:00",to:"02:00"}, {day:"saturday",disabled:true}] };
  const inside = shopHours(overnight, Date.parse("2026-10-03T00:00:00Z"));
  assert.equal(inside.closed, false);
  assert.equal(inside.today.day, "friday");
  assert.equal(shopHours(overnight, Date.parse("2026-10-03T01:00:00Z")).closed, true);
});
test("missing, malformed and ambiguous data do not invent Open/Closed", () => {
  assert.equal(shopHours({}, 0).closed, null);
  assert.equal(shopHours({ ...shop, hours_timezone: null }, 0).reason, "Shop timezone unavailable");
  assert.equal(shopHours({ ...shop, hours_timezone: "invalid" }, 0).closed, null);
  assert.equal(shopHours({ ...shop, shop_working_days: [{day:"friday",from:"99:00",to:"18:00"}] }, Date.parse("2026-10-02T10:00:00Z")).closed, null);
  assert.equal(shopHours(shop, NaN).closed, null);
});
test("server and first client share the serialized snapshot, irrespective of runtime timezone", () => {
  const timestamp = Date.parse("2026-10-02T17:59:59Z");
  const script = `const {shopHours}=require(${JSON.stringify(require.resolve("./shop-hours.cjs"))});console.log(JSON.stringify(shopHours(${JSON.stringify(shop)},${timestamp})))`;
  const results = ["UTC","America/Chicago","Asia/Tokyo"].map(TZ => {
    const result = spawnSync(process.execPath, ["-e", script], {env: {...process.env, TZ}, encoding:"utf8"});
    assert.equal(result.status, 0);
    return result.stdout.trim();
  });
  assert.equal(new Set(results).size, 1);
  assert.deepEqual(shopHours(shop, JSON.parse(JSON.stringify(timestamp))), JSON.parse(results[0]));
  assert.notEqual(shopHours(shop, Date.parse("2026-10-02T16:59:59Z")).closed, shopHours(shop, Date.parse("2026-10-02T17:00:00Z")).closed);
});