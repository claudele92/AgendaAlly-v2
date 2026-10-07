import assert from 'node:assert/strict';
import test from 'node:test';
import {
  normalizeTopProductsResponse,
  topProductsFailureState,
} from './topProductsResponse.mjs';

const stockRow = {
  id: 31,
  product_id: 14,
  quantity: 24,
  count: 6,
  img: null,
  product: {
    id: 14,
    img: '/storage/synthetic/product.png',
    translation: { locale: 'en', title: 'Synthetic product' },
  },
};

const laravelProductsResponse = {
  status: true,
  data: {
    data: [stockRow],
    links: { first: null, last: null, prev: null, next: null },
    meta: { per_page: 5 },
  },
};

for (const role of ['admin', 'seller']) {
  test(`normalizes the original paginated StockResource envelope for ${role}`, () => {
    const products = normalizeTopProductsResponse(laravelProductsResponse);

    assert.equal(products.length, 1);
    assert.equal(products[0].title, 'Synthetic product');
    assert.equal(products[0].img, '/storage/synthetic/product.png');
    assert.equal(products[0].count, 6);
    assert.equal(products[0].id, 31);
  });
}

test('supports the response shape returned by the Axios interceptor', () => {
  const products = normalizeTopProductsResponse(laravelProductsResponse);
  assert.equal(products[0].title, 'Synthetic product');
});

test('supports an unwrapped Axios response for shared callers that bypass the interceptor', () => {
  const products = normalizeTopProductsResponse({
    status: 200,
    data: laravelProductsResponse,
  });
  assert.equal(products[0].title, 'Synthetic product');
});

test('accepts an empty paginator as a successful empty result', () => {
  assert.deepEqual(
    normalizeTopProductsResponse({
      status: true,
      data: {
        data: [],
        links: { first: null, last: null, prev: null, next: null },
        meta: { per_page: 5 },
      },
    }),
    [],
  );
});

test('uses the stock image when present and retains a zero sales count', () => {
  const products = normalizeTopProductsResponse({
    status: true,
    data: [
      {
        ...stockRow,
        img: '/storage/synthetic/variant.png',
        count: 0,
      },
    ],
  });

  assert.equal(products[0].img, '/storage/synthetic/variant.png');
  assert.equal(products[0].count, 0);
});

test('rejects an API failure and malformed data instead of reporting false emptiness', () => {
  assert.throws(
    () =>
      normalizeTopProductsResponse({
        status: false,
        message: 'The request failed.',
      }),
    /request failed/i,
  );
  assert.throws(
    () => normalizeTopProductsResponse({ status: true, data: { data: {} } }),
    /did not contain a list/i,
  );
  assert.throws(
    () => normalizeTopProductsResponse({ status: true, data: [null] }),
    /invalid item/i,
  );
});

test('a request failure clears old products and exposes an explicit error state', () => {
  assert.deepEqual(topProductsFailureState('Request failed'), {
    loading: false,
    topProducts: [],
    error: 'Request failed',
  });
});