part of 'game_bloc.dart';

@freezed
abstract class GameState with _$GameState {
  const factory GameState({
    @Default(null) Board? board,
    @Default(null) SwipeDirection? swipeDirection,
    @Default(true) bool position,
  }) = _GameState;
}
