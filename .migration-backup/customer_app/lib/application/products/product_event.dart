part of 'product_bloc.dart';

@freezed
abstract class ProductEvent with _$ProductEvent {
  const factory ProductEvent.fetchMostSaleProduct(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchMostSaleProduct;

  const factory ProductEvent.fetchAllProduct(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchAllProduct;

  const factory ProductEvent.fetchNewProduct(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchNewProduct;

  const factory ProductEvent.fetchLikeProduct(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchLikeProduct;

  const factory ProductEvent.fetchDiscountProduct(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchDiscountProduct;

  const factory ProductEvent.fetchMostSaleShopProduct(
    BuildContext context, {
    required int? shopId,
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchMostSaleShopProduct;

  const factory ProductEvent.fetchNewShopProduct(
    BuildContext context, {
    required int? shopId,
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchNewShopProduct;

  const factory ProductEvent.fetchProducts(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
    List<ProductData>? list,
    bool? isNewProduct,
    bool? isMostSaleProduct,
    int? categoryId,
    int? shopId,
    int? bannerId,
    List<int>? brandId,
    List<int>? categoryIds,
    List<int>? extrasId,
    num? priceTo,
    num? priceFrom,
  }) = FetchProducts;

  const factory ProductEvent.updateState() = UpdateState;
}
