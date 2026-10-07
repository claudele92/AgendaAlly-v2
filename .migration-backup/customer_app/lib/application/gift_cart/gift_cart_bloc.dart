import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../domain/di/dependency_manager.dart';
import '../../domain/model/models.dart';
import '../../infrastructure/service/services.dart';
import '../../presentation/route/app_route.dart';

part 'gift_cart_event.dart';

part 'gift_cart_state.dart';

part 'gift_cart_bloc.freezed.dart';

class GiftCartBloc extends Bloc<GiftCartEvent, GiftCartState> {
  GiftCartBloc() : super(const GiftCartState()) {
    int page = 0;

    on<SetWalletPrice>((event, emit) {
      final availableList = state.payments
          .where((p) => p.tag != TrKeys.wallet && p.tag != TrKeys.cash)
          .toList();
      final payments = (event.price == null)
          ? availableList
          : ((event.price! >= (state.currentGift?.price ?? 0)
                    ? [state.payments.firstWhere((p) => p.tag == TrKeys.wallet)]
                    : availableList)
                .toList());
      emit(
        state.copyWith(
          walletPrice: event.price,
          availablePayments: payments,
          selectedPayment: payments.firstOrNull,
        ),
      );
    });

    on<FetchGiftCart>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        page = 0;
        emit(state.copyWith(giftCarts: [], isLoading: true));
      }
      final res = await shopsRepository.getGiftCart(
        page: ++page,
        shopId: event.shopId ?? 0,
      );
      res.fold(
        (l) {
          List<GiftCartModel> list = List.from(state.giftCarts);
          list.addAll(l);
          emit(state.copyWith(isLoading: false, giftCarts: list));
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
    });

    on<FetchPayments>((event, emit) async {
      emit(state.copyWith(isPaymentLoading: true));
      final res = await paymentsRepository.getPayments();
      res.fold(
        (l) {
          final List<PaymentData> availablePayments = [];
          for (var e in (l.data ?? [])) {
            if (e.tag != TrKeys.cash && e.tag != TrKeys.wallet) {
              availablePayments.add(e);
            }
          }
          emit(
            state.copyWith(
              payments: l.data ?? [],
              availablePayments: availablePayments,
              selectedPayment: availablePayments.firstOrNull,
              isPaymentLoading: false,
            ),
          );
        },
        (r) {
          emit(state.copyWith(isPaymentLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<FetchWebView>((event, emit) async {
      emit(state.copyWith(isButtonLoading: true));
      final payment = state.payments.firstWhere(
        (element) => element.id == state.selectedPayment?.id,
        orElse: () => PaymentData(),
      );
      final res = await paymentsRepository.paymentWebView(
        name: payment.tag ?? "",
        giftCartId: event.giftCart?.id,
        price: event.giftCart?.price,
        walletPrice: state.walletPrice,
      );
      res.fold(
        (l) async {
          emit(state.copyWith(isButtonLoading: false));
          final result = await AppRoute.goWebView(event.context, url: l);
          if (result) {
            event.onSuccess?.call();
          }
        },
        (r) {
          emit(state.copyWith(isButtonLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SelectPayment>((event, emit) {
      emit(state.copyWith(selectedPayment: event.payment));
    });

    on<CreateTransaction>((event, emit) async {
      emit(state.copyWith(isButtonLoading: true));
      final res = await paymentsRepository.createGiftCartTransaction(
        giftCartId: event.giftCartId,
        paymentId: state.selectedPayment?.id,
        walletPrice: state.selectedPayment?.tag == 'wallet'
            ? null
            : state.walletPrice,
      );
      res.fold(
        (l) {
          emit(state.copyWith(isButtonLoading: false));
          event.onSuccess?.call();
          userRepository.getProfileDetails(event.context);
        },
        (r) {
          emit(state.copyWith(isButtonLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<MyGiftCart>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        page = 0;
        emit(state.copyWith(myGiftCarts: [], isLoading: true));
      }
      final res = await shopsRepository.myGiftCarts(
        page: ++page,
        shopId: event.shopId,
        serviceId: event.serviceId,
        valid: event.valid,
      );
      res.fold(
        (l) {
          List<MyGiftCartModel> list = List.from(state.myGiftCarts);
          list.addAll(l);
          emit(state.copyWith(isLoading: false, myGiftCarts: list));
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
    });
  }
}
