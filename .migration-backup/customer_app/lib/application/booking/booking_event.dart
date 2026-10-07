part of 'booking_bloc.dart';

@freezed
abstract class BookingEvent with _$BookingEvent {
  const factory BookingEvent.fetchBookUpcoming(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchBookUpcoming;

  const factory BookingEvent.setWalletPrice({num? price}) = SetWalletPrice;

  const factory BookingEvent.fetchBookPast(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchBookPast;

  const factory BookingEvent.fetchBookingById(
    BuildContext context, {
    required int id,
  }) = FetchBookingById;

  const factory BookingEvent.bookingService(
    BuildContext context, {
    required num totalPrice,
    required ValueChanged<int> onSuccess,
  }) = BookingService;

  const factory BookingEvent.calculateBooking(BuildContext context) =
      CalculateBooking;

  const factory BookingEvent.checkTime(
    BuildContext context, {
    required DateTime startTime,
  }) = CheckTime;

  const factory BookingEvent.selectPayment({required PaymentData payment}) =
      SelectPayment;

  const factory BookingEvent.payLater(
    BuildContext context, {
    required int? id,
    required num totalPrice,
    required ValueChanged<int> onSuccess,
  }) = PayLater;

  const factory BookingEvent.fetchPayments(
    BuildContext context, {
    required bool payLater,
  }) = FetchPayments;

  const factory BookingEvent.fetchWebView(
    BuildContext context, {
    required int id,
    required ValueChanged onSuccess,
    ValueChanged? onFailure,
  }) = FetchWebView;

  const factory BookingEvent.cancelBook(
    BuildContext context, {
    required int id,
    VoidCallback? onSuccess,
  }) = CancelBook;

  const factory BookingEvent.selectDateTime({
    required DateTime selectDateTime,
  }) = SelectDateTime;

  const factory BookingEvent.selectBookingTime({required String time}) =
      SelectBookingTime;

  const factory BookingEvent.setServices({
    required List<ServiceModel> services,
    VoidCallback? onSuccess,
    VoidCallback? onFailure,
    MasterModel? master,
  }) = SetServices;

  const factory BookingEvent.selectMaster({
    required int? serviceId,
    required MasterModel? master,
  }) = SelectMaster;

  const factory BookingEvent.selectTime({
    required DateTime? selectTime,
    required int? serviceId,
  }) = SelectTime;

  const factory BookingEvent.selectAddress({required UserAddress? address}) =
      SelectAddress;

  const factory BookingEvent.setGiftCart({required MyGiftCartModel? giftCart}) =
      SetGiftCart;

  const factory BookingEvent.saveForm(
    BuildContext context, {
    required FormOptionsData? form,
    VoidCallback? onSuccess,
  }) = SaveForm;

  const factory BookingEvent.updateNotes(
    BuildContext context, {
    required int id,
    required String message,
    VoidCallback? onSuccess,
  }) = UpdateNotes;

  const factory BookingEvent.checkCoupon(
    BuildContext context, {
    required String coupon,
    required int shopId,
    bool? clear,
  }) = CheckCoupon;
}
