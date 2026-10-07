part of 'story_bloc.dart';

@freezed
abstract class StoryState with _$StoryState {
  const factory StoryState({
    @Default([]) List<List<StoryModel?>?> stories,
    @Default(true) bool isLoading,
  }) = _StoryState;
}
