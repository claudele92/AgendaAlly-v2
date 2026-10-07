const assert = require("node:assert/strict");
const { existsSync, readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const webRoot = path.resolve(__dirname, "..");
const read = (relativePath) => readFileSync(path.join(webRoot, relativePath), "utf8");
const queryBlockForKey = (source, key) => {
  const keyIndex = source.indexOf(`"${key}"`);
  assert.notEqual(keyIndex, -1, `Expected a ${key} query key`);
  const queryKeyIndex = source.lastIndexOf("queryKey:", keyIndex);
  const queryCallIndex = source.lastIndexOf("useQuery(", queryKeyIndex);
  const queryEndIndex = source.indexOf("\n  });", keyIndex);

  assert.ok(queryCallIndex !== -1 && queryKeyIndex > queryCallIndex);
  assert.notEqual(queryEndIndex, -1, `Expected the ${key} query block to close`);
  return source.slice(queryCallIndex, queryEndIndex + "\n  });".length);
};
const priceFilter = read(
  'app/(store)/(booking)/(witout-footer)/(navigation)/components/filters/price.tsx',
);
const serverCart = read("hook/use-server-cart.ts");
const groupCart = read("app/(store)/@detail/(.)group/page.tsx");
const authorizedCart = read(
  'app/(store)/(booking)/(with-footer)/(simple)/cart/authorized-cart.tsx',
);
const countrySelect = read("components/country-select/country-select-panel.tsx");

test("price inputs keep SSR and client values as finite, serializable strings", () => {
  assert.match(priceFilter, /useState\(\s*searchParams\.has\("priceFrom"\)\s*\?\s*normalizePriceInput/);
  assert.match(priceFilter, /value=\{priceFrom\}/);
  assert.match(priceFilter, /value=\{priceTo\}/);
  assert.match(priceFilter, /const value = e\.currentTarget\.value/);
  assert.match(priceFilter, /Number\.isFinite\(parsed\) \? String\(parsed\) : "0"/);
  assert.doesNotMatch(priceFilter, /valueAsNumber/);
});

test("only the known missing-cart 404 becomes an empty query result", async () => {
  const { isKnownMissingCartError } = await import("../utils/cart-error.mjs");

  assert.equal(
    isKnownMissingCartError(Object.assign(new Error("Items not found"), { statusCode: 404 })),
    true,
  );
  assert.equal(
    isKnownMissingCartError(Object.assign(new Error("Item's not found."), { statusCode: 404, code: "ERROR_404" })),
    true,
    "The observed native Laravel missing-cart response must match, not only the canned legacy phrase.",
  );
  assert.equal(
    isKnownMissingCartError(Object.assign(new Error("Item's not found."), { statusCode: 500, code: "ERROR_404" })),
    false,
  );
  assert.equal(
    isKnownMissingCartError(Object.assign(new Error("Translated not-found message"), { statusCode: 404, code: "ERROR_404" })),
    true,
  );
  assert.equal(
    isKnownMissingCartError(Object.assign(new Error("Items not found"), { statusCode: 404, code: "ERROR_102" })),
    false,
  );
  assert.equal(
    isKnownMissingCartError(Object.assign(new Error("Unauthorized"), { statusCode: 404 })),
    false,
  );
  assert.equal(
    isKnownMissingCartError(Object.assign(new Error("Items not found"), { statusCode: 422 })),
    false,
  );
  assert.equal(isKnownMissingCartError({ message: "Items not found", statusCode: 404 }), false);
  assert.match(serverCart, /Promise<DefaultResponse<Cart> \| null>/);
  assert.match(serverCart, /if \(isKnownMissingCartError\(error\)\) return null/);
  assert.match(serverCart, /throw error/);
  assert.match(serverCart, /if \(res === null\)\s*\{\s*updateLocalCart\(\[\]\)/);
  assert.match(groupCart, /if \(cartMissing && !openCartRequestSent\.current\)/);
  assert.match(groupCart, /if \(isCartError\) \{[\s\S]*?role="alert"/);
  assert.doesNotMatch(groupCart, /if \(\(!cart \|\| isCartError\)/);
});

test("cart calculate and payment queries only run for populated, resolved carts", () => {
  const anonymousCart = read(
    'app/(store)/(booking)/(with-footer)/(simple)/cart/unauthorized-cart.tsx',
  );
  const anonymousCalculateQuery = queryBlockForKey(anonymousCart, "calculate");
  const authorizedCalculateQuery = queryBlockForKey(authorizedCart, "calculate");
  const authorizedPaymentQuery = queryBlockForKey(authorizedCart, "payments");

  assert.match(anonymousCalculateQuery, /cartService\.restCalculate\(body\)/);
  assert.match(
    anonymousCalculateQuery,
    /enabled:\s*!!currency\s*&&\s*hasCalculableCartProducts/,
  );
  assert.match(
    anonymousCart,
    /cartList\.every\(\(cartProduct\) => Number\.isInteger\(cartProduct\.stockId\) && cartProduct\.stockId > 0\)/,
  );
  assert.match(anonymousCart, /if \(isLoading && hasCalculableCartProducts\)/);
  assert.match(
    anonymousCart,
    /\(cartList\.length > 0 && !hasCalculableCartProducts\)[\s\S]*?\(cartError && hasCalculableCartProducts\)[\s\S]*?role="alert"/,
  );
  assert.match(anonymousCart, /if \(!hasCalculableCartProducts \|\| cartTotal\?\.data\.shops\.length === 0\)/);

  assert.match(authorizedCalculateQuery, /cartService\.calculate\(data\?\.data\?\.id, body\)/);
  assert.match(
    authorizedCalculateQuery,
    /enabled:\s*!isCartFetching\s*&&\s*!cartError\s*&&\s*!!userCart\s*&&\s*!!cartDetailsLength\s*&&\s*\(checkoutState\.deliveryType/,
  );
  assert.match(authorizedPaymentQuery, /orderService\.paymentList\(\{/);
  assert.match(
    authorizedPaymentQuery,
    /enabled:\s*!isCartFetching\s*&&\s*!cartError\s*&&\s*!!data\?\.data\?\.id\s*&&\s*!!cartDetailsLength/,
  );
  assert.match(authorizedCart, /if \(cartError\) \{[\s\S]*?role="alert"/);
  assert.match(
    authorizedCart,
    /if \(\(!userCart && !cartDetailsLength\) \|\| cartDetailsLength === 0\)/,
  );
  assert.doesNotMatch(authorizedCart, /cartError \|\| cartDetailsLength/);
});

test("country selection uses the existing empty-cart artwork", () => {
  assert.match(countrySelect, /\/img\/empty_cart\.png/);
  assert.doesNotMatch(countrySelect, /cartempty\.png/);
  assert.equal(existsSync(path.join(webRoot, "public/img/empty_cart.png")), true);
});