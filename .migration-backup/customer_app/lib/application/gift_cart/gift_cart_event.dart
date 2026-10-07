part of 'gift_cart_bloc.dart';

@freezed
abstract class GiftCartEvent with _$GiftCartEvent {
  const factory GiftCartEvent.setWalletPrice({num? price}) = SetWalletPrice;

  const factory GiftCartEvent.fetchGiftCart(
    BuildContext context, {
    bool? isRefresh,
    int? shopId,
    RefreshController? controller,
  }) = FetchGiftCart;

  const factory GiftCartEvent.selectPayment({required PaymentData payment}) =
      SelectPayment;

  const factory GiftCartEvent.fetchPayments(
    BuildContext context, {
    GiftCartModel? currentGift,
  }) = FetchPayments;

  const factory GiftCartEvent.fetchWebView(
    BuildContext context, {
    required GiftCartModel? giftCart,
    required VoidCallback? onSuccess,
  }) = FetchWebView;

  const factory GiftCartEvent.createTransaction(
    BuildContext context, {
    required int giftCartId,
    required VoidCallback? onSuccess,
  }) = CreateTransaction;

  const factory GiftCartEvent.myGiftCart(
    BuildContext context, {
    bool? isRefresh,
    int? serviceId,
    int? shopId,
    bool? valid,
    RefreshController? controller,
  }) = MyGiftCart;
}
