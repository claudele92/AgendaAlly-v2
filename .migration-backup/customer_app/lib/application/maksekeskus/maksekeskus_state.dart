part of 'maksekeskus_bloc.dart';

@freezed
abstract class MaksekeskusState with _$MaksekeskusState {
  const factory MaksekeskusState({
    @Default(false) bool isLoading,
    @Default(null) String? selectMethodLink,
    @Default(null) MaksekeskusData? maksekeskus,
  }) = _MaksekeskusState;
}
