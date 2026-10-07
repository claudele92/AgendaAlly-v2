part of 'category_bloc.dart';

@freezed
abstract class CategoryEvent with _$CategoryEvent {
  const factory CategoryEvent.fetchCategory(
    BuildContext context, {
    bool? isRefresh,
    int? shopId,
    RefreshController? controller,
  }) = FetchCategory;

  const factory CategoryEvent.fetchCategoryOfService(
    BuildContext context, {
    bool? isRefresh,
    int? shopId,
    RefreshController? controller,
  }) = FetchCategoryOfService;

  const factory CategoryEvent.selectCategory(
    BuildContext context, {
    CategoryData? category,
  }) = SelectCategory;

  const factory CategoryEvent.selectCategoryTwo(
    BuildContext context, {
    CategoryData? category,
  }) = SelectCategoryTwo;
}
