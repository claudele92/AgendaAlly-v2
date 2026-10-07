part of 'shop_bloc.dart';

@freezed
abstract class ShopEvent with _$ShopEvent {
  const factory ShopEvent.fetchShops(
    BuildContext context, {
    bool? isRefresh,
    FilterShopModel? filter,
    RefreshController? controller,
  }) = FetchShops;

  const factory ShopEvent.fetchNewShops(
    BuildContext context, {
    bool? isRefresh,
    FilterShopModel? filter,
    RefreshController? controller,
  }) = FetchNewShops;

  const factory ShopEvent.fetchNearShops(
    BuildContext context, {
    bool? isRefresh,
    FilterShopModel? filter,
    RefreshController? controller,
    int? withoutShopId,
    List<int>? categoryIds,
    required VoidCallback onSuccess,
    required LocationModel? location,
  }) = FetchNearShops;

  const factory ShopEvent.fetchPopularShops(
    BuildContext context, {
    bool? isRefresh,
    FilterShopModel? filter,
    RefreshController? controller,
  }) = FetchPopularShops;

  const factory ShopEvent.fetchLikeShops(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchLikeShops;

  const factory ShopEvent.generateLink(BuildContext context) = GenerateLink;

  const factory ShopEvent.fetchShopById(
    BuildContext context, {
    required ShopData? shop,
  }) = FetchShopById;

  const factory ShopEvent.updateState() = UpdateState;

  const factory ShopEvent.fetchShopsImages(
    BuildContext context, {
    required int? shopId,
  }) = FetchShopsImages;

  const factory ShopEvent.setFilter({required FilterShopModel? filter}) =
      SetFilter;
}
