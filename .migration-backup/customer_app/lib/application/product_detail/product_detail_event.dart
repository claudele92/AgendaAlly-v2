part of 'product_detail_bloc.dart';

@freezed
abstract class ProductDetailEvent with _$ProductDetailEvent {
  const factory ProductDetailEvent.fetchProductById(
    BuildContext context, {
    RefreshController? controller,
    ProductData? product,
  }) = FetchProductById;

  const factory ProductDetailEvent.fetchRelatedProduct(
    BuildContext context, {
    required String? productUuid,
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchRelatedProduct;

  const factory ProductDetailEvent.fetchViewedProducts(
    BuildContext context, {
    required int? productId,
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchViewedProducts;

  const factory ProductDetailEvent.fetchBuyWithProduct(
    BuildContext context, {
    required int? productId,
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchBuyWithProduct;

  const factory ProductDetailEvent.updateState() = UpdateState;

  const factory ProductDetailEvent.setSelectedIndexes(
    BuildContext context, {
    required List<int> indexes,
  }) = SetSelectedIndexes;

  const factory ProductDetailEvent.updateExtras() = UpdateExtras;

  const factory ProductDetailEvent.updateSelectedIndexes(
    BuildContext context,
    int index,
    int value,
  ) = UpdateSelectedIndexes;

  const factory ProductDetailEvent.selectImage({
    required Galleries image,
    bool? jumpTo,
    bool? nextImageTo,
  }) = SelectImage;
}
