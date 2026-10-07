import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../domain/di/dependency_manager.dart';
import '../../domain/model/models.dart';
import '../../infrastructure/service/services.dart';

part 'master_event.dart';

part 'master_state.dart';

part 'master_bloc.freezed.dart';

class MasterBloc extends Bloc<MasterEvent, MasterState> {
  MasterBloc() : super(const MasterState()) {
    int page = 0;

    on<FetchMasters>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        page = 0;
        emit(state.copyWith(masters: [], isLoading: true));
      }

      final res = await masterRepository.getMasters(
        page: ++page,
        shopId: event.shopId,
        serviceId: event.serviceId,
        serviceIds: event.serviceIds,
      );

      res.fold(
        (l) {
          List<MasterModel> list = List.from(state.masters);
          list.addAll(l.data ?? []);
          emit(state.copyWith(isLoading: false, masters: list));
          if (event.isRefresh ?? false) {
            event.controller?.refreshCompleted();
            return;
          } else if (l.data?.isEmpty ?? true) {
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
    });

    on<FetchMasterById>((event, emit) async {
      emit(state.copyWith(isLoading: true, master: event.master));

      final res = await masterRepository.getMasterById(
        id: event.master.id ?? 0,
      );

      res.fold(
        (l) {
          emit(state.copyWith(isLoading: false, master: l));
        },
        (r) {
          emit(state.copyWith(isLoading: false));

          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<FetchLikeMasters>((event, emit) async {
      if (LocalStorage.getLikedMaster().isEmpty) {
        return;
      }
      emit(state.copyWith(likeMasters: []));

      final res = await masterRepository.getLikedMaster();

      res.fold(
        (l) {
          emit(state.copyWith(likeMasters: l.data ?? []));
        },
        (r) {
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<FetchMasterImage>((event, emit) async {
      emit(state.copyWith(masterImages: []));

      final res = await masterRepository.getMastersImage(event.id);
      res.fold(
        (list) {
          emit(state.copyWith(masterImages: list));
        },
        (r) {
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<UpdateState>((event, emit) {
      emit(state.copyWith(isLoading: true));
      emit(state.copyWith(isLoading: false));
    });
  }
}
