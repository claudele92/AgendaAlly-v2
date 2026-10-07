part of 'order_bloc.dart';

@freezed
abstract class OrderEvent with _$OrderEvent {
  const factory OrderEvent.fetchActiveOrders(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchActiveOrders;

  const factory OrderEvent.fetchRefundOrders(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchRefundOrders;

  const factory OrderEvent.fetchOrderById(
    BuildContext context, {
    required int id,
    OrderModel? order,
  }) = FetchOrderById;

  const factory OrderEvent.fetchRefundOrderById(
    BuildContext context, {
    required int id,
    RefundModel? refund,
  }) = FetchRefundOrderById;

  const factory OrderEvent.createOrder(
    BuildContext context, {
    required CreateOrderModel order,
    required PaymentData payment,
    required num totalPrice,
    required VoidCallback onSuccess,
  }) = CreateOrder;

  const factory OrderEvent.cancelOrder(
    BuildContext context, {
    required int id,
    required VoidCallback onSuccess,
  }) = CancelOrder;

  const factory OrderEvent.refundOrder(
    BuildContext context, {
    required int id,
    required String reason,
    required VoidCallback onSuccess,
  }) = RefundOrder;
}
