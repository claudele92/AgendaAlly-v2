part of 'become_seller_bloc.dart';

@freezed
abstract class BecomeSellerState with _$BecomeSellerState {
  const factory BecomeSellerState({
    @Default(null) String? logoPath,
    @Default(null) String? bgPath,
    @Default([]) List<String> filepath,
    @Default(TrKeys.month) String type,
    @Default(TrKeys.wareHouse) String deliveryType,
    @Default(false) bool isLoading,
    @Default(false) bool isSuccess,
  }) = _BecomeSellerState;
}
