part of 'direct_service_bloc.dart';

@freezed
abstract class DirectServiceEvent with _$DirectServiceEvent {
  const factory DirectServiceEvent.init(
    BuildContext context, {
    int? serviceId,
  }) = Init;

  const factory DirectServiceEvent.selectExtraIndex({
    required ServiceExtra? extra,
  }) = SelectExtraIndex;
}
