#!/usr/bin/env node

import { readFileSync } from "node:fs";
import { resolve } from "node:path";
import { fileURLToPath } from "node:url";

const root = resolve(fileURLToPath(new URL("..", import.meta.url)));
let checks = 0;

function source(relativePath) {
  return readFileSync(resolve(root, relativePath), "utf8");
}

function includes(relativePath, fragments, label) {
  const text = source(relativePath);
  const missing = fragments.filter((fragment) => !text.includes(fragment));
  if (missing.length) {
    throw new Error(
      `${label}: missing expected source fragment(s) in ${relativePath}: ${missing.join(", ")}`,
    );
  }
  checks += 1;
  console.log(`PASS static source: ${label}`);
}

includes(
  ".migration-backup/web/services/booking.ts",
  [
    "v1/dashboard/user/bookings/calculate",
    "v1/dashboard/user/bookings",
    "v1/dashboard/user/bookings/${id}/get-all",
    "DefaultResponse<Booking[]>",
    "Paginate<Booking>",
  ],
  "web booking quote/create/list/detail paths and response types",
);
includes(
  ".migration-backup/web/services/cart.ts",
  [
    "v1/dashboard/user/cart/calculate/${id}",
    "v1/rest/order/products/calculate",
    "v1/dashboard/user/cart/open",
    "v1/dashboard/user/cart/set-group/${id}",
  ],
  "web cart, separate quote and group paths",
);
includes(
  ".migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/cart/authorized-cart.tsx",
  ["data?.data?.id", "cartService.calculate(data?.data?.id"],
  "web cart caller extracts cart id from response data",
);
includes(
  ".migration-backup/web/services/order.ts",
  [
    "v1/dashboard/user/orders",
    "v1/dashboard/user/orders/paginate",
    "DefaultResponse<OrderFull[]>",
    "Paginate<Order>",
  ],
  "web checkout and paginated order history contracts",
);
includes(
  ".migration-backup/web/services/refund.ts",
  [
    "v1/dashboard/user/order-refunds/paginate",
    "fetcher.post(`v1/dashboard/user/order-refunds`",
    "Paginate<Refund>",
  ],
  "web refund create and paginated history paths",
);
includes(
  ".migration-backup/admin/src/services/booking.js",
  [
    "dashboard/admin/bookings/calculate",
    "dashboard/admin/bookings/${id}/get-all",
    "dashboard/admin/bookings/${id}/status/update",
  ],
  "admin booking service route family",
);
includes(
  ".migration-backup/admin/src/services/order.js",
  [
    "dashboard/admin/orders/paginate",
    "dashboard/admin/orders/${id}/get-all",
  ],
  "admin order list and grouped detail paths",
);
includes(
  ".migration-backup/admin/src/services/refund.js",
  [
    "dashboard/admin/order-refunds/paginate",
    "dashboard/admin/order-refunds/${id}",
    "dashboard/admin/order-refunds/delete",
  ],
  "admin refund list/detail paths",
);
includes(
  ".migration-backup/customer_app/lib/infrastructure/repository/booking_repository.dart",
  [
    "/api/v1/rest/bookings/calculate",
    "/api/v1/dashboard/user/bookings",
    "BookingCalculateResponse.fromJson",
    "BookingResponse.fromJson",
  ],
  "Flutter booking callers select the booking DTOs for quote and list/create",
);
includes(
  ".migration-backup/customer_app/lib/infrastructure/repository/cart_repository.dart",
  [
    "/api/v1/rest/order/products/calculate",
    "/api/v1/dashboard/user/cart/calculate/$cartId",
    "ProductCalculateResponse.fromJson",
    "CartCalculateResponse.fromJson",
    'resCart.data["data"]["id"]',
  ],
  "Flutter product quote, cart calculation and group wrapper paths",
);
includes(
  ".migration-backup/customer_app/lib/infrastructure/repository/order_repository.dart",
  [
    "/api/v1/dashboard/user/orders",
    "/api/v1/dashboard/user/orders/paginate",
    "/api/v1/dashboard/user/order-refunds/paginate",
    "OrderPaginateResponse.fromJson",
    "RefundOrdersModel.fromJson",
    "RefundModel.fromJson(res.data[\"data\"])",
  ],
  "Flutter checkout/history/refund paths and parser choices",
);
includes(
  ".migration-backup/customer_app/lib/domain/model/response/booking_response.dart",
  ['json["data"]', "BookingModel.fromJson"],
  "Flutter booking response reads a list from data",
);
includes(
  ".migration-backup/customer_app/lib/domain/model/response/cart_response.dart",
  [
    'json["data"] == null ? null : Cart.fromJson(json["data"])',
    'json["user_carts"]',
    'json["cartDetails"]',
    'json["cartDetailProducts"]',
  ],
  "Flutter cart parser preserves original envelope and mixed-case resource keys",
);
includes(
  ".migration-backup/customer_app/lib/domain/model/response/order_pagenation_response.dart",
  ["json['data']", "json['meta']", "OrderShops.fromJson"],
  "Flutter order history parses data and pagination metadata",
);
includes(
  ".migration-backup/customer_app/lib/domain/model/response/refund_pagination_response.dart",
  ['json["data"]', "RefundModel.fromJson", 'json["order"]'],
  "Flutter refund history and nested order parser",
);
includes(
  ".migration-backup/backend/routes/api.php",
  [
    "bookings/calculate",
    "orders/paginate",
    "order-refunds/paginate",
    "cart/calculate/{id}",
    "order/products/calculate",
  ],
  "original route source contains inspected customer flow route families",
);

const peerFixture = JSON.parse(
  source(".migration-backup/backend/tests/Baseline/fixtures/original-domain.json"),
);
if (
  peerFixture.source !== "original Laravel backend; synthetic isolated SQLite :memory:" ||
  peerFixture.booking?.successful_create?.resource?.data?.id !== 1 ||
  peerFixture.cart?.resource?.data?.id !== 1 ||
  peerFixture.checkout?.resource?.data?.id !== 1 ||
  peerFixture.refund?.resource?.data?.status !== "pending" ||
  peerFixture.refund?.history_resource?.data?.[0]?.id !== 1
) {
  throw new Error("original backend peer fixture is missing expected serialized client resources");
}
checks += 1;
console.log("PASS generated backend fixture: booking/cart/order/refund resources are present");

console.log(
  `PASS: ${checks} dependency-free source/fixture assertions. These assertions do not make HTTP requests or execute Flutter parsers.`,
);