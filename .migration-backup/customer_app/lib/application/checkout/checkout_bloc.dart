import 'package:demand/domain/di/dependency_manager.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:demand/domain/model/response/delivery_point_response.dart';
import 'package:demand/domain/model/response/payments_response.dart';
import 'package:demand/domain/model/response/user_address_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

part 'checkout_event.dart';

part 'checkout_state.dart';

part 'checkout_bloc.freezed.dart';

class CheckoutBloc extends Bloc<CheckoutEvent, CheckoutState> {
  CheckoutBloc() : super(const CheckoutState()) {
    int page = 0;
    int addressPage = 0;

    on<SetWalletPrice>((event, emit) {
      final fullWallet = (event.price ?? -1) >= (event.totalPrice ?? 0);
      final availablePayments = fullWallet
          ? [state.payments.firstWhere((element) => element.tag == "wallet")]
          : state.payments.where((element) => element.tag != "wallet").toList();
      emit(
        state.copyWith(
          walletPrice: event.price,
          availablePayments: availablePayments,
          selectedPayment: availablePayments[0],
        ),
      );
    });

    on<SelectAddress>((event, emit) async {
      emit(state.copyWith(selectAddress: event.index));
    });
    on<SetDeliveryTip>((event, emit) async {
      emit(state.copyWith(tips: event.tips));
    });

    on<DeleteAddress>((event, emit) async {
      List<UserAddress> list = List.from(state.address);
      list.removeAt(event.index);
      emit(state.copyWith(address: list));
      final res = await addressRepository.deleteAddress(
        addressId: event.addressId,
      );
      res.fold((l) {}, (r) {
        emit(state.copyWith(isLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      });
    });

    on<FetchUserAddress>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        addressPage = 0;
        emit(state.copyWith(address: [], isLoading: true));
      }
      final res = await addressRepository.getUserAddress(page: ++addressPage);
      res.fold(
        (l) {
          List<UserAddress> list = List.from(state.address);
          list.addAll(l.data ?? []);
          emit(state.copyWith(isLoading: false, address: list));
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

    on<ChangeActive>((event, emit) {
      emit(state.copyWith(isActive: event.active));
    });

    on<ChangeStep>((event, emit) {
      if (event.step != null) {
        emit(state.copyWith(step: event.step ?? 1));
        return;
      }
      if (state.step == 4) {
        return;
      }
      emit(state.copyWith(step: state.step + 1));
    });

    on<SetMapController>((event, emit) {
      emit(state.copyWith(mapController: event.controller));
    });

    on<SelectPoint>((event, emit) {
      emit(state.copyWith(selectPointId: event.point?.id ?? 0));
    });

    on<FetchDeliveryPoint>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        page = 0;
        emit(state.copyWith(deliveryPoints: [], isLoadingPoint: true));
      }
      final res = await addressRepository.getDeliveryPoint(page: ++page);
      res.fold(
        (l) {
          List<DeliveryPoint> list = List.from(state.deliveryPoints ?? []);
          list.addAll(l.data ?? []);
          emit(
            state.copyWith(
              isLoadingPoint: false,
              deliveryPoints: list,
              selectPointId: list.isNotEmpty ? (list.first.id ?? 0) : -1,
            ),
          );
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
          emit(state.copyWith(isLoadingPoint: false));
          if (event.isRefresh ?? false) {
            event.controller?.refreshFailed();
          }
          event.controller?.loadFailed();

          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<FetchDeliveryPrice>((event, emit) async {
      emit(state.copyWith(deliveryPrice: []));
      final response = await addressRepository.getDeliveryPrice();
      response.fold(
        (l) {
          emit(state.copyWith(isLoadingPoint: false, deliveryPrice: l.data));
        },
        (r) {
          emit(state.copyWith(isLoadingPoint: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<FetchPayments>((event, emit) async {
      final res = await paymentsRepository.getPayments();
      res.fold(
        (l) {
          final payments = l.data ?? [];
          payments.sort(
            (a, b) => a.tag == 'cash' ? -1 : (b.tag == 'cash' ? 1 : 0),
          );
          emit(
            state.copyWith(
              payments: payments,
              availablePayments: payments,
              selectedPayment: payments[0],
            ),
          );
        },
        (r) {
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<ChangePayment>((event, emit) async {
      emit(state.copyWith(selectedPayment: event.payment));
    });

    on<ChangeDate>((event, emit) async {
      emit(state.copyWith(deliveryDate: event.date));
    });
  }
}
