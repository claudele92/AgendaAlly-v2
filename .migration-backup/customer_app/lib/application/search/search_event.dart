part of 'search_bloc.dart';

@freezed
abstract class SearchEvent with _$SearchEvent {
  const factory SearchEvent.setQuery({required String query, int? shopId}) =
      SetQuery;

  const factory SearchEvent.searchShops(BuildContext context) = SearchShops;

  const factory SearchEvent.searchProduct(BuildContext context) = SearchProduct;

  const factory SearchEvent.searchService(BuildContext context) = SearchService;

  const factory SearchEvent.searchCategory(BuildContext context) =
      SearchCategory;

  const factory SearchEvent.searchBrand(BuildContext context) = SearchBrand;

  const factory SearchEvent.updateRecently() = UpdateRecently;
}
