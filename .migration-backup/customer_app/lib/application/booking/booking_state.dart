part of 'booking_bloc.dart';

@freezed
abstract class BookingState with _$BookingState {
  const factory BookingState({
    @Default([]) List<BookingModel> upcoming,
    @Default([]) List<BookingModel> past,
    @Default([]) List<PaymentData> payments,
    @Default([]) List<CheckData>? listDate,
    @Default(true) bool isLoading,
    @Default(false) bool isLoadingMaster,
    @Default(null) String? coupon,
    @Default(false) bool isButtonLoading,
    @Default(true) bool isPaymentLoading,
    PaymentData? selectedPayment,
    @Default(null) BookingCalculateResponse? calculate,
    @Default(null) DateTime? selectDateTime,
    @Default(null) String? selectBookTime,
    @Default([]) List<ServiceModel> selectServices,
    @Default({}) Map<int, MasterModel> selectMasters,
    @Default(null) MyGiftCartModel? giftCart,
    num? walletPrice,
  }) = _BookingState;
}
