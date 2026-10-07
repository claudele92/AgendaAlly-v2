part of 'new_ui_bloc.dart';

@freezed
abstract class NewUiState with _$NewUiState {
  const factory NewUiState({
    @Default(50) double top,
    @Default(-200) double left,
  }) = _NewUiState;
}
