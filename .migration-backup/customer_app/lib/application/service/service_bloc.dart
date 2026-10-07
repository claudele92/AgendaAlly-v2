import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../domain/di/dependency_manager.dart';
import '../../domain/model/models.dart';
import '../../infrastructure/service/services.dart';

part 'service_event.dart';

part 'service_state.dart';

part 'service_bloc.freezed.dart';

class ServiceBloc extends Bloc<ServiceEvent, ServiceState> {
  ServiceBloc() : super(const ServiceState()) {
    int page = 0;
    int pageCategory = 0;

    on<FetchServices>((event, emit) async {
      if (event.isRefresh) {
        event.controller?.resetNoData();
        page = 0;
        emit(state.copyWith(services: [], isLoading: true));
      }

      final res = await serviceRepository.getAllService(
        page: ++page,
        shopId: event.shopId,
        categoryId: event.categoryId,
        masterId: event.masterId,
      );

      res.fold(
        (l) {
          List<ServiceModel> list = List.from(state.services);
          list.addAll(l.data ?? []);
          emit(state.copyWith(isLoading: false, services: list));
          if (event.isRefresh) {
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
          if (event.isRefresh) {
            event.controller?.refreshFailed();
          }
          event.controller?.loadFailed();

          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<FetchServiceCategories>((event, emit) async {
      if (event.isRefresh) {
        event.controller?.resetNoData();
        pageCategory = 0;
        emit(state.copyWith(serviceCategories: [], isLoading: true));
      }

      final res = await serviceRepository.getServiceCategories(
        page: ++pageCategory,
        shopId: event.shopId,
        masterId: event.masterId,
      );

      res.fold(
        (l) {
          List<CategoryData> list = List.from(state.serviceCategories);
          list.addAll(l.data ?? []);

          emit(
            state.copyWith(
              isLoading: false,
              serviceCategories: list,
              selectedCategory: list.any((e) => e.id == event.categoryId)
                  ? list.firstWhere((e) => event.categoryId == e.id)
                  : null,
            ),
          );
          if (event.isRefresh) {
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
          if (event.isRefresh) {
            event.controller?.refreshFailed();
          }
          event.controller?.loadFailed();

          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SelectServiceCategory>((event, emit) async {
      emit(
        state.copyWith(
          selectedCategory: event.category,
          selectedChildCategory: null,
        ),
      );
      if (event.isRefresh) {
        event.controller?.resetNoData();
        page = 0;
        emit(state.copyWith(services: [], isLoading: true));
        final res = await serviceRepository.getAllService(
          page: ++page,
          shopId: event.shopId,
          categoryId: event.category?.id,
          masterId: event.masterId,
        );
        res.fold(
          (l) {
            emit(state.copyWith(isLoading: false, services: l.data ?? []));
            event.controller?.refreshCompleted();
          },
          (r) {
            emit(state.copyWith(isLoading: false));
            event.controller?.refreshFailed();
            event.controller?.loadFailed();
          },
        );
      }
    });

    on<SelectServiceCategorChild>((event, emit) async {
      emit(
        state.copyWith(
          selectedChildCategory: event.category,
          selectedCategory: event.parent,
        ),
      );
      if (event.isRefresh) {
        event.controller?.resetNoData();
        page = 0;
        emit(state.copyWith(services: [], isLoading: true));
        final res = await serviceRepository.getAllService(
          page: ++page,
          shopId: event.shopId,
          categoryId: event.category?.id,
          masterId: event.masterId,
        );
        res.fold(
          (l) {
            emit(state.copyWith(isLoading: false, services: l.data ?? []));
            event.controller?.refreshCompleted();
          },
          (r) {
            emit(state.copyWith(isLoading: false));
            event.controller?.refreshFailed();
            event.controller?.loadFailed();
          },
        );
      }
    });

    on<SelectService>((event, emit) {
      if (event.service == null) return;
      List<ServiceModel> list = List.from(state.selectServices);
      if (event.extra != null) {
        List<ServiceExtra> extras = List.from(state.selectExtras);
        if (extras.any((e) => e.id == event.extra?.id)) {
          extras.removeWhere((e) => e.id == event.extra?.id);
        } else {
          extras.add(event.extra!);
        }
        int index = list
            .map((e) => e.id)
            .toList()
            .indexOf(event.service?.id ?? 0);
        list[index] = list[index].copyWith(selectExtras: extras);
        emit(state.copyWith(selectServices: list, selectExtras: extras));
        return;
      }
      if (list.any((e) => e.id == event.service?.id)) {
        int index = list
            .map((e) => e.id)
            .toList()
            .indexOf(event.service?.id ?? 0);
        list.removeAt(index);
        emit(state.copyWith(selectServices: list));
        return;
      }

      list.add(event.service!);
      emit(state.copyWith(selectServices: list));
    });

    on<SelectExtraIndex>((event, emit) {
      if (event.extra == null) return;
      List<ServiceExtra> extras = List.from(state.selectExtras);
      if (extras.any((e) => e.id == event.extra?.id)) {
        extras.removeWhere((e) => e.id == event.extra?.id);
      } else {
        extras.add(event.extra!);
      }
      emit(state.copyWith(selectExtras: extras));
    });

    on<SelectAllExtras>((event, emit) {
      emit(state.copyWith(selectExtras: event.extra ?? []));
    });
  }
}
