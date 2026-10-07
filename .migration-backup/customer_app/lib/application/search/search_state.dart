part of 'search_bloc.dart';

@freezed
abstract class SearchState with _$SearchState {
  const factory SearchState({
    @Default([]) List<ShopData> shops,
    @Default([]) List<ProductData> products,
    @Default([]) List<CategoryData> categories,
    @Default([]) List<BrandData> brands,
    @Default([]) List<ServiceModel> services,
    @Default(false) bool isShopLoading,
    @Default(false) bool isProductLoading,
    @Default(false) bool isCategoryLoading,
    @Default(false) bool isBrandLoading,
    @Default(false) bool isServiceLoading,
    @Default("") String query,
  }) = _SearchState;
}
