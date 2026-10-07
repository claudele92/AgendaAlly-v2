import 'dart:convert';
import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:demand/domain/model/model/order_model.dart';
import 'package:demand/domain/model/response/booking_calculate_response.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/domain/model/response/cart_calculate_response.dart';
import 'package:demand/domain/model/response/cart_response.dart';
import 'package:demand/domain/model/response/order_pagenation_response.dart';
import 'package:demand/domain/model/response/product_calculate_response.dart';
import 'package:demand/domain/model/response/refund_pagination_response.dart';

void main() {
  group('AgendaAlly preserved Flutter response parser baseline', () {
    test('booking quote and booking collection envelope', () {
      final quote = BookingCalculateResponse.fromJson({
        'timestamp': '2026-09-30T12:00:00Z',
        'status': true,
        'message': 'synthetic quote',
        'data': {'status': true, 'price': 45, 'total_price': 50, 'items': []},
      });

      expect(quote.status, isTrue);
      expect(quote.data?.price, 45);
      expect(quote.data?.totalPrice, 50);

      final createdOrListed = BookingResponse.fromJson({
        'timestamp': '2026-09-30T12:00:00Z',
        'status': true,
        'message': 'synthetic booking',
        'data': [
          {
            'id': 501,
            'service_master_id': 301,
            'start_date': '2026-10-15T10:30:00Z',
            'end_date': '2026-10-15T11:15:00Z',
            'total_price': 50,
            'status': 'new',
            'transactions': [],
            'extras': [],
          },
        ],
      });

      expect(createdOrListed.data, hasLength(1));
      expect(createdOrListed.data?.single.id, 501);
    });

    test('cart response and authenticated cart calculation', () {
      final cart = CartModel.fromJson({
        'timestamp': '2026-09-30T12:00:00Z',
        'status': true,
        'message': 'synthetic cart',
        'data': {
          'id': 601,
          'currency_id': 1,
          'total_price': 20,
          'user_carts': [
            {
              'id': 602,
              'cart_id': 601,
              'cartDetails': [
                {
                  'id': 603,
                  'shop_id': 101,
                  'cartDetailProducts': [
                    {
                      'id': 604,
                      'quantity': 2,
                      'price': 10,
                      'stock': {'id': 801, 'product_id': 802},
                    },
                  ],
                },
              ],
            },
          ],
        },
      });

      expect(cart.cart?.id, 601);
      expect(cart.cart?.userCarts?.single.cartDetails?.single.id, 603);
      expect(
        cart
            .cart
            ?.userCarts
            ?.single
            .cartDetails
            ?.single
            .cartDetailProducts
            ?.single
            .id,
        604,
      );

      final calculated = CartCalculateResponse.fromJson({
        'timestamp': '2026-09-30T12:00:00Z',
        'status': true,
        'message': 'synthetic cart quote',
        'data': {
          'price': 20,
          'total_price': 22,
          'delivery_fee': [],
          'coupon': [],
          'errors': [],
        },
      });
      expect(calculated.data?.totalPrice, 22);
    });

    test('public product cart quote parser', () {
      final calculated = ProductCalculateResponse.fromJson({
        'timestamp': '2026-09-30T12:00:00Z',
        'status': true,
        'message': 'synthetic product quote',
        'data': {
          'shops': [
            {
              'price': 20,
              'total_price': 22,
              'stocks': [
                {
                  'id': 801,
                  'quantity': 2,
                  'total_price': 20,
                  'stock': {'id': 801, 'product_id': 802},
                },
              ],
            },
          ],
          'total_price': 22,
          'delivery_fee': [],
          'coupon': [],
        },
      });

      expect(calculated.data?.shops, hasLength(1));
      expect(calculated.data?.shops?.single.stocks?.single.id, 801);
      expect(calculated.data?.totalPrice, 22);
    });

    test('checkout/group detail and paginated order history', () {
      final orderItem = {
        'id': 901,
        'ids_by_parent': '901',
        'parent_id': null,
        'total_price': 22,
        'total_price_by_parent': 22,
        'status': 'new',
        'details': [],
        'transactions': [],
        'refunds': [],
        'order_refunds': [],
      };

      final checkoutOrDetail = OrderModel.fromJson({
        'timestamp': '2026-09-30T12:00:00Z',
        'status': true,
        'message': 'synthetic order',
        'data': [orderItem],
      });
      expect(checkoutOrDetail.orderShops?.single.id, 901);

      final history = OrderPaginateResponse.fromJson({
        'data': [orderItem],
        'links': {'first': 'synthetic', 'last': 'synthetic'},
        'meta': {'current_page': 1, 'per_page': 10, 'total': 1},
      });
      expect(history.data, hasLength(1));
      expect(history.data?.single.id, 901);
      expect(history.meta?.total, 1);
    });

    test('refund history and detail parsers', () {
      final refundResource = {
        'id': 1001,
        'status': 'pending',
        'cause': 'synthetic reason',
        'answer': null,
        'created_at': '2026-09-30 12:00:00Z',
        'updated_at': '2026-09-30 12:00:00Z',
        'order': {
          'id': 901,
          'ids_by_parent': '901',
          'status': 'new',
          'details': [],
          'transactions': [],
          'refunds': [],
          'order_refunds': [],
        },
      };

      final history = RefundOrdersModel.fromJson({
        'data': [refundResource],
        'links': {'first': 'synthetic', 'last': 'synthetic'},
        'meta': {'current_page': 1, 'per_page': 10, 'total': 1},
      });
      expect(history.data, hasLength(1));
      expect(history.data?.single.order?.id, 901);

      final detail = RefundModel.fromJson(refundResource);
      expect(detail.id, 1001);
      expect(detail.status, 'pending');
      expect(detail.order?.id, 901);
    });

    test(
      'parses original backend-generated booking/cart/order/refund envelopes',
      () {
        final fixturePath = Platform.environment['AGENDAALLY_BACKEND_FIXTURE'];
        expect(fixturePath, isNotNull);

        final fixture =
            jsonDecode(File(fixturePath!).readAsStringSync())
                as Map<String, dynamic>;
        expect(fixture['source'], contains('original Laravel backend'));

        final bookingEnvelope = Map<String, dynamic>.from(
          fixture['booking']['successful_create']['resource'] as Map,
        );
        final booking = BookingModel.fromJson(
          Map<String, dynamic>.from(bookingEnvelope['data'] as Map),
        );
        expect(booking.id, 1);
        expect(booking.serviceMasterId, 1);
        expect(booking.status, 'new');
        expect(booking.totalPrice, 40);

        final cartEnvelope = Map<String, dynamic>.from(
          fixture['cart']['resource'] as Map,
        );
        final cart = CartModel.fromJson(cartEnvelope);
        expect(cart.cart?.id, 1);
        expect(
          cart
              .cart
              ?.userCarts
              ?.single
              .cartDetails
              ?.single
              .cartDetailProducts
              ?.single
              .quantity,
          4,
        );

        final orderEnvelope = Map<String, dynamic>.from(
          fixture['checkout']['resource'] as Map,
        );
        final order = OrderShops.fromJson(
          Map<String, dynamic>.from(orderEnvelope['data'] as Map),
        );
        expect(order.id, 1);
        expect(order.status, 'new');
        expect(order.details?.single.quantity, 2);

        final refundEnvelope = Map<String, dynamic>.from(
          fixture['refund']['resource'] as Map,
        );
        final refund = RefundModel.fromJson(
          Map<String, dynamic>.from(refundEnvelope['data'] as Map),
        );
        expect(refund.id, 1);
        expect(refund.status, 'pending');
        expect(refund.cause, 'synthetic return reason');
        expect(refund.answer, '');
        expect(refund.createdAt?.year, 2030);

        final refundHistory = RefundOrdersModel.fromJson(
          Map<String, dynamic>.from(
            fixture['refund']['history_resource'] as Map,
          ),
        );
        expect(refundHistory.data, hasLength(1));
        expect(refundHistory.data?.single.id, 1);
        expect(refundHistory.data?.single.order?.id, 1);
      },
    );
  });
}
