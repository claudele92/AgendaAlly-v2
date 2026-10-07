part of 'maksekeskus_bloc.dart';

@freezed
abstract class MaksekeskusEvent with _$MaksekeskusEvent {
  const factory MaksekeskusEvent.selectMethod({required String link}) =
      SelectMethod;

  const factory MaksekeskusEvent.fetchMaksekeskus(
    BuildContext context, {
    CreateOrderModel? order,
    bool? parcel,
    bool? wallet,
    int? parcelId,
    num? price,
    VoidCallback? onSuccess,
  }) = FetchMaksekeskus;
}
