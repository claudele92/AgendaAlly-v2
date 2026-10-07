part of 'service_bloc.dart';

@freezed
abstract class ServiceState with _$ServiceState {
  const factory ServiceState({
    @Default([]) List<ServiceModel> services,
    @Default([]) List<CategoryData> serviceCategories,
    @Default([]) List<ServiceModel> selectServices,
    @Default([]) List<ServiceExtra> selectExtras,
    @Default(true) bool isLoading,
    CategoryData? selectedCategory,
    CategoryData? selectedChildCategory,
  }) = _ServiceState;
}
