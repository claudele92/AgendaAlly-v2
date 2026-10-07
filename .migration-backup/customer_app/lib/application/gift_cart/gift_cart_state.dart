part of 'gift_cart_bloc.dart';

@freezed
abstract class GiftCartState with _$GiftCartState {
  const factory GiftCartState({
    @Default([]) List<GiftCartModel> giftCarts,
    @Default([]) List<MyGiftCartModel> myGiftCarts,
    @Default(true) bool isLoading,
    @Default(false) bool isButtonLoading,
    @Default(true) bool isPaymentLoading,
    @Default([]) List<PaymentData> payments,
    @Default([]) List<PaymentData> availablePayments,
    num? walletPrice,
    PaymentData? selectedPayment,
    GiftCartModel? currentGift,
  }) = _GiftCartState;
}
