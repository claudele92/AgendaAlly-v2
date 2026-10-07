part of 'product_detail_bloc.dart';

@freezed
abstract class ProductDetailState with _$ProductDetailState {
  const factory ProductDetailState({
    @Default([]) List<ProductData> relatedProducts,
    @Default([]) List<ProductData> viewedProducts,
    @Default([]) List<ProductData> recommendedProducts,
    @Default([]) List<TypedExtra> typedExtras,
    @Default([]) List<Stocks> initialStocks,
    @Default([]) List<int> selectedIndexes,
    @Default([]) List<Galleries> galleries,
    @Default(true) bool jumpTo,
    @Default(true) bool nextImageTo,
    @Default(true) bool isLoading,
    @Default(true) bool isLoadingNew,
    @Default(0) int totalCount,
    @Default(0) int stockCount,
    ProductData? product,
    Galleries? selectImage,
    Stocks? selectedStock,
  }) = _ProductDetailState;
}
