function isRecord(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}

export function normalizeTopProductsResponse(response) {
  // services/request returns response.data. Support a raw Axios response only
  // for consumers which intentionally bypass that shared interceptor.
  const body =
    typeof response?.status === 'number' && response.data
      ? response.data
      : response;

  if (!isRecord(body) || body.status === false) {
    throw new Error(
      body?.message || 'The top-selling products request was unsuccessful.',
    );
  }

  // ApiResponse returns { status, data }. Its StockResource paginator adds
  // another { data, links, meta } wrapper around the ordered stock rows.
  const collection = body.data;
  const rows = Array.isArray(collection)
    ? collection
    : isRecord(collection) && Array.isArray(collection.data)
      ? collection.data
      : null;

  if (!rows) {
    throw new Error('The top-selling products response did not contain a list.');
  }

  return rows.map((stock) => {
    if (!isRecord(stock)) {
      throw new Error('The top-selling products response contained an invalid item.');
    }

    // The endpoint returns StockResource. Names and the product image are
    // nested under product; only the sold count belongs to the stock row.
    return {
      ...stock,
      title: stock.product?.translation?.title || stock.title || null,
      img: stock.img || stock.product?.img || null,
    };
  });
}

export function topProductsFailureState(message) {
  return {
    loading: false,
    topProducts: [],
    error: message,
  };
}