import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/model/model/create_order_model.dart';
import 'package:demand/domain/model/model/maksekeskus_data.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';

part 'maksekeskus_event.dart';

part 'maksekeskus_state.dart';

part 'maksekeskus_bloc.freezed.dart';

class MaksekeskusBloc extends Bloc<MaksekeskusEvent, MaksekeskusState> {
  MaksekeskusBloc() : super(const MaksekeskusState()) {
    on<SelectMethod>(selectMethod);
    on<FetchMaksekeskus>(fetchMaksekeskus);
  }

  Future<void> selectMethod(SelectMethod event, emit) async {
    emit(state.copyWith(selectMethodLink: event.link));
  }

  Future<void> fetchMaksekeskus(FetchMaksekeskus event, emit) async {
    emit(state.copyWith(isLoading: true));
    final res = await paymentsRepository.paymentMaksekeskusView(
      order: event.order,
      parcel: event.parcel ?? false,
      parcelId: event.parcelId,
      wallet: event.wallet ?? false,
      price: event.price,
    );
    res.fold(
      (l) async {
        emit(state.copyWith(isLoading: false, maksekeskus: l.data));
      },
      (r) {
        emit(state.copyWith(isLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }
}
