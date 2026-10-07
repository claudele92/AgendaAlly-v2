import 'package:flutter/material.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';

import '../../../domain/di/dependency_manager.dart';
import '../../../domain/model/models.dart';
import '../../../infrastructure/service/services.dart';

part 'direct_service_event.dart';

part 'direct_service_state.dart';

part 'direct_service_bloc.freezed.dart';

class DirectServiceBloc extends Bloc<DirectServiceEvent, DirectServiceState> {
  DirectServiceBloc() : super(const DirectServiceState()) {
    on<Init>((event, emit) async {
      emit(state.copyWith(isLoading: true, selectedExtras: [], service: null));
      final response = await serviceRepository.getSingleService(
        serviceId: event.serviceId,
      );
      response.fold(
        (left) {
          emit(state.copyWith(isLoading: false, service: left.data));
        },
        (right) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(event.context, message: right);
        },
      );
    });

    on<SelectExtraIndex>((event, emit) {
      if (event.extra == null) return;
      List<ServiceExtra> extras = List.from(state.selectedExtras);
      if (extras.any((e) => e.id == event.extra?.id)) {
        extras.removeWhere((e) => e.id == event.extra?.id);
      } else {
        extras.add(event.extra!);
      }
      emit(state.copyWith(selectedExtras: extras));
    });
  }
}
