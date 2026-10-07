import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';

part 'main_event.dart';

part 'main_state.dart';

part 'main_bloc.freezed.dart';

class MainBloc extends Bloc<MainEvent, MainState> {
  MainBloc() : super(const MainState()) {
    on<ChangeIndex>(changeIndex);

    on<ChangeForYou>(changeForYou);

    on<ShowSearch>(showSearch);
  }

  void changeIndex(ChangeIndex event, Emitter<MainState> emit) {
    emit(state.copyWith(selectIndex: event.index));
  }

  void changeForYou(ChangeForYou event, Emitter<MainState> emit) {
    emit(state.copyWith(selectForYou: event.value));
  }

  void showSearch(ShowSearch event, Emitter<MainState> emit) {
    emit(state.copyWith(isShowSearch: event.value));
  }
}
