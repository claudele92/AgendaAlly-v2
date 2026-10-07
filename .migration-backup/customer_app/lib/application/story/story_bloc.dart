import 'package:demand/domain/di/dependency_manager.dart';
import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:demand/domain/model/model/story_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

part 'story_event.dart';

part 'story_state.dart';

part 'story_bloc.freezed.dart';

class StoryBloc extends Bloc<StoryEvent, StoryState> {
  int page = 0;

  StoryBloc() : super(const StoryState()) {
    on<FetchStory>(fetchStory);
  }

  Future<void> fetchStory(FetchStory event, emit) async {
    if (event.isRefresh ?? false) {
      event.controller?.resetNoData();
      page = 0;
      emit(state.copyWith(stories: [], isLoading: true));
    }
    final res = await shopsRepository.getStory(++page);
    res.fold(
      (l) {
        List<List<StoryModel?>?> list = List.from(state.stories);
        list.addAll(l);
        emit(state.copyWith(isLoading: false, stories: list));
        if (event.isRefresh ?? false) {
          event.controller?.refreshCompleted();
          return;
        } else if (l.isEmpty) {
          event.controller?.loadNoData();
          return;
        }
        event.controller?.loadComplete();
        return;
      },
      (r) {
        emit(state.copyWith(isLoading: false));
        if (event.isRefresh ?? false) {
          event.controller?.refreshFailed();
        }
        event.controller?.loadFailed();
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }
}
