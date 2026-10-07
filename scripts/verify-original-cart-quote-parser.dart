import 'dart:convert';

// Directly executes the preserved parser; no copied/reimplemented DTO or pub
// dependencies. This is a narrow smoke check, not a full Flutter app build.
import '../.migration-backup/customer_app/lib/domain/model/response/cart_calculate_response.dart';

void main() {
  var checks = 0;
  void check(bool condition, String message) {
    if (!condition) throw StateError(message);
    checks++;
  }

  final quote = cartCalculateResponseFromJson(jsonEncode({
    'timestamp': '2026-09-30T12:00:00Z',
    'status': true,
    'message': 'synthetic quote',
    'data': {
      'price': 19.99,
      'total_price': 22,
      'total_tax': 2.01,
      'rate': 1,
      'delivery_fee': [],
      'coupon': [],
      'errors': [],
    },
  }));
  check(quote.status == true, 'Quote success wrapper changed.');
  check(quote.data?.price == 19.99, 'Decimal price changed.');
  check(quote.data?.totalPrice == 22, 'Integer total no longer parses.');
  check(quote.data?.totalTax == 2.01, 'Tax key changed.');
  check(quote.data?.errors?.isEmpty == true, 'Empty errors no longer parse.');
  final roundTrip = cartCalculateResponseFromJson(
    cartCalculateResponseToJson(quote),
  );
  check(roundTrip.data?.totalPrice == 22, 'Quote round trip changed.');
  final absent = CartCalculateResponse.fromJson({
    'status': false,
    'message': 'synthetic failure',
    'data': null,
  });
  check(absent.status == false && absent.data == null, 'Null failure data changed.');
  print('PASS original CartCalculateResponse parser: $checks checks.');
  print('Scope: synthetic quote only; booking/order/refund parsers and full app are NOT certified.');
}