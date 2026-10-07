part of 'cart_bloc.dart';

@freezed
abstract class CartEvent with _$CartEvent {
  const factory CartEvent.getCart(
    BuildContext context, {
    bool? refresh,
    String? coupon,
  }) = GetCart;

  const factory CartEvent.insertCart(
    BuildContext context, {
    bool? refresh,
    String? coupon,
    required VoidCallback onSuccess,
  }) = InsertCart;

  const factory CartEvent.removeProductCart(
    BuildContext context, {
    required int? cartDetailId,
  }) = RemoveProductCart;

  const factory CartEvent.setNotes({
    required int stockId,
    required String note,
  }) = SetNotes;

  const factory CartEvent.changeReady(
    BuildContext context, {
    required String uuid,
    required VoidCallback onSuccess,
  }) = ChangeReady;

  const factory CartEvent.startGroupOrder(
    BuildContext context, {
    required VoidCallback onSuccess,
  }) = StartGroupOrder;

  const factory CartEvent.createLink(BuildContext context) = CreateLink;

  const factory CartEvent.deleteCart(BuildContext context) = DeleteCart;

  const factory CartEvent.deleteUser(
    BuildContext context, {
    required String uuid,
    required VoidCallback onSuccess,
  }) = DeleteUser;

  const factory CartEvent.checkCoupon(
    BuildContext context, {
    required String coupon,
    required int shopId,
    bool? clear,
  }) = CheckCoupon;

  const factory CartEvent.updateState() = UpdateState;

  const factory CartEvent.calculateCart(
    BuildContext context, {
    required int? deliveryPriceId,
    required int deliveryTip,
    required int? pointId,
    required int? type,
    required bool fullDigital,
  }) = CalculateCart;

  const factory CartEvent.calculateCartWithCoupon(BuildContext context) =
      CalculateCartWithCoupon;
}
