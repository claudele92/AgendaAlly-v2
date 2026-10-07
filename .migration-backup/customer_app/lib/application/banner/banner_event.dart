part of 'banner_bloc.dart';

@freezed
abstract class BannerEvent with _$BannerEvent {
  const factory BannerEvent.fetchBanner(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchBanner;

  const factory BannerEvent.fetchAdsBanner(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchAdsBanner;

  const factory BannerEvent.fetchLooks(
    BuildContext context, {
    bool? isRefresh,
    int? shopId,
    RefreshController? controller,
  }) = FetchLooks;

  const factory BannerEvent.fetchProduct(
    BuildContext context, {
    required int id,
  }) = FetchProduct;

  const factory BannerEvent.updateProduct() = UpdateProduct;

  const factory BannerEvent.fetchAdsProduct(
    BuildContext context, {
    required int id,
  }) = FetchAdsProduct;

  const factory BannerEvent.fetchAdsListProduct(
    BuildContext context, {
    bool? isRefresh,
    int? shopId,
    RefreshController? controller,
  }) = FetchAdsListProduct;
}
