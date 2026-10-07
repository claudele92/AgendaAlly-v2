part of 'become_seller_bloc.dart';

@freezed
abstract class BecomeSellerEvent with _$BecomeSellerEvent {
  const factory BecomeSellerEvent.updateLogoPath({required String? imagePath}) =
      UpdateLogoPath;

  const factory BecomeSellerEvent.updateFilePath({required String? file}) =
      UpdateFilePath;

  const factory BecomeSellerEvent.removeFilePath({required String? file}) =
      RemoveFilePath;

  const factory BecomeSellerEvent.updateBgPath({required String? imagePath}) =
      UpdateBgPath;

  const factory BecomeSellerEvent.setDeliveryTimeType({required String? type}) =
      SetDeliveryTimeType;

  const factory BecomeSellerEvent.setDeliveryType({required String? type}) =
      SetDeliveryType;

  const factory BecomeSellerEvent.createShop(
    BuildContext context, {
    required CreateShopModel request,
  }) = CreateShop;
}
