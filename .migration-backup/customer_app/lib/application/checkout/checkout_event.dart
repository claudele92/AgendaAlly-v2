part of 'checkout_bloc.dart';

@freezed
abstract class CheckoutEvent with _$CheckoutEvent {
  const factory CheckoutEvent.deleteAddress(
    BuildContext context, {
    required int addressId,
    required int index,
  }) = DeleteAddress;

  const factory CheckoutEvent.selectAddress({required int index}) =
      SelectAddress;

  const factory CheckoutEvent.setWalletPrice({num? price, num? totalPrice}) =
      SetWalletPrice;

  const factory CheckoutEvent.setDeliveryTip({required int tips}) =
      SetDeliveryTip;

  const factory CheckoutEvent.fetchUserAddress(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchUserAddress;

  const factory CheckoutEvent.changeStep(BuildContext context, {int? step}) =
      ChangeStep;

  const factory CheckoutEvent.changeActive({required bool active}) =
      ChangeActive;

  const factory CheckoutEvent.fetchPayments(BuildContext context) =
      FetchPayments;

  const factory CheckoutEvent.changePayment({required PaymentData payment}) =
      ChangePayment;

  const factory CheckoutEvent.changeDate({required DateTime date}) = ChangeDate;

  const factory CheckoutEvent.fetchDeliveryPoint(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchDeliveryPoint;

  const factory CheckoutEvent.fetchDeliveryPrice(BuildContext context) =
      FetchDeliveryPrice;

  const factory CheckoutEvent.selectPoint({required DeliveryPoint? point}) =
      SelectPoint;

  const factory CheckoutEvent.setMapController({
    required GoogleMapController controller,
  }) = SetMapController;
}
