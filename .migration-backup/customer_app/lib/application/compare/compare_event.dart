part of 'compare_bloc.dart';

@freezed
abstract class CompareEvent with _$CompareEvent {
  const factory CompareEvent.fetchCompare(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchCompare;

  const factory CompareEvent.setExtraGroup({
    required List<ProductData> products,
  }) = SetExtraGroup;
}
