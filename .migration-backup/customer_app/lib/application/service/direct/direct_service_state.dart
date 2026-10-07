part of 'direct_service_bloc.dart';

@freezed
abstract class DirectServiceState with _$DirectServiceState {
  const factory DirectServiceState({
    @Default([]) List<ServiceExtra> selectedExtras,
    @Default(false) bool isLoading,
    ServiceModel? service,
  }) = _DirectServiceState;
}
