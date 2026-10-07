part of 'parcel_bloc.dart';

@freezed
abstract class ParcelEvent with _$ParcelEvent {
  const factory ParcelEvent.addReview(
    BuildContext context, {
    required String comment,
    required double rating,
  }) = AddReview;

  const factory ParcelEvent.changeExpand() = ChangeExpand;

  const factory ParcelEvent.showParcel(
    BuildContext context, {
    required int orderId,
    ParcelOrder? parcel,
  }) = ShowParcel;

  const factory ParcelEvent.setTime({required TimeOfDay time}) = SetTime;

  const factory ParcelEvent.switchAddress(BuildContext context) = SwitchAddress;

  const factory ParcelEvent.setFromAddress(
    BuildContext context, {
    required String? title,
    required LocationModel? location,
  }) = SetFromAddress;

  const factory ParcelEvent.setToAddress(
    BuildContext context, {
    required String? title,
    required LocationModel? location,
  }) = SetToAddress;

  const factory ParcelEvent.selectType(
    BuildContext context, {
    required int index,
  }) = SelectType;

  const factory ParcelEvent.changeAnonymous() = ChangeAnonymous;

  const factory ParcelEvent.fetchTypes(BuildContext context) = FetchTypes;

  const factory ParcelEvent.getCalculate(BuildContext context) = GetCalculate;

  const factory ParcelEvent.orderParcel(
    BuildContext context, {
    required String note,
    required String usernameTo,
    required String usernameFrom,
    required String phoneTo,
    required String phoneFrom,
    required String houseFrom,
    required String houseTo,
    required String floorTo,
    required String floorFrom,
    required String comment,
    required String value,
    required String instruction,
    required PaymentData paymentData,
  }) = OrderParcel;
}
