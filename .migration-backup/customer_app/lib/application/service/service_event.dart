part of 'service_bloc.dart';

@freezed
abstract class ServiceEvent with _$ServiceEvent {
  const factory ServiceEvent.fetchServices(
    BuildContext context, {
    @Default(false) bool isRefresh,
    int? shopId,
    int? categoryId,
    int? masterId,
    RefreshController? controller,
  }) = FetchServices;

  const factory ServiceEvent.fetchServiceCategories(
    BuildContext context, {
    @Default(false) bool isRefresh,
    int? shopId,
    int? masterId,
    int? categoryId,
    RefreshController? controller,
  }) = FetchServiceCategories;

  const factory ServiceEvent.selectServiceCategory({
    CategoryData? category,
    int? shopId,
    int? masterId,
    RefreshController? controller,
    @Default(false) bool isRefresh,
  }) = SelectServiceCategory;

  const factory ServiceEvent.selectServiceCategoryChild({
    CategoryData? category,
    CategoryData? parent,
    int? shopId,
    int? masterId,
    RefreshController? controller,
    @Default(false) bool isRefresh,
  }) = SelectServiceCategorChild;

  const factory ServiceEvent.selectService({
    required ServiceModel? service,
    ServiceExtra? extra,
  }) = SelectService;

  const factory ServiceEvent.selectExtraIndex({required ServiceExtra? extra}) =
      SelectExtraIndex;

  const factory ServiceEvent.selectAllExtras({
    required List<ServiceExtra>? extra,
  }) = SelectAllExtras;
}
