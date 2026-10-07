import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:demand/domain/model/model/user_model.dart';
import 'package:demand/domain/model/response/payments_response.dart';
import 'package:demand/domain/model/response/transaction_histories_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

part 'wallet_event.dart';

part 'wallet_state.dart';

part 'wallet_bloc.freezed.dart';

class WalletBloc extends Bloc<WalletEvent, WalletState> {
  int page = 0;
  int search = 0;

  WalletBloc() : super(const WalletState()) {
    on<FetchTransactions>(fetchTransactions);

    on<FetchPayments>(fetchPayments);

    on<SearchUser>(searchUser);

    on<SelectPayment>(selectPayment);

    on<FillWallet>(fillWallet);

    on<SendWallet>(sendWallet);
  }

  Future<void> fetchTransactions(FetchTransactions event, emit) async {
    if (event.isRefresh ?? false) {
      event.controller?.resetNoData();
      page = 0;
      emit(state.copyWith(transactions: [], isTransactionLoading: true));
    }
    final res = await userRepository.getTransactions(++page);
    res.fold(
      (l) {
        List<TransactionModel> list = List.from(state.transactions);
        list.addAll(l.data ?? []);
        emit(state.copyWith(isTransactionLoading: false, transactions: list));
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
        emit(state.copyWith(isTransactionLoading: false));
        if (event.isRefresh ?? false) {
          event.controller?.refreshFailed();
        }
        event.controller?.loadFailed();

        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> fetchPayments(FetchPayments event, emit) async {
    final res = await paymentsRepository.getPayments();
    res.fold(
      (l) {
        List<PaymentData> list = [];
        for (int i = 0; i < (l.data?.length ?? 0); i++) {
          if (l.data?[i].tag != "cash" && l.data?[i].tag != "wallet") {
            list.add(l.data?[i] ?? PaymentData());
          }
        }
        emit(state.copyWith(list: list, selectPayment: 0));
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> searchUser(SearchUser event, emit) async {
    if (event.isRefresh ?? false) {
      event.controller?.resetNoData();
      search = 0;
      emit(state.copyWith(listOfUser: [], isSearchingLoading: true));
    }

    final res = await userRepository.searchUser(
      name: event.search,
      page: ++search,
    );
    res.fold(
      (l) {
        List<UserModel> list = List.from(state.listOfUser ?? []);
        list.addAll(l);
        emit(state.copyWith(listOfUser: list, isSearchingLoading: false));
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
        emit(state.copyWith(isSearchingLoading: false));
        if (event.isRefresh ?? false) {
          event.controller?.refreshFailed();
        }
        event.controller?.loadFailed();
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  void selectPayment(SelectPayment event, emit) {
    emit(state.copyWith(selectPayment: event.index));
  }

  Future<void> fillWallet(FillWallet event, emit) async {
    if (state.list?[state.selectPayment].tag == 'maksekeskus') {
      AppRoute.goMaksekeskusBottomSheet(
        event.context,
        colors: event.colors,
        price: double.tryParse(event.price),
        wallet: true,
        onSuccess: (url) async {
          final res = await AppRoute.goWebView(event.context, url: url);
          if (res) {
            event.onSuccess();
          } else {
            event.onFailure();
          }
        },
      );
      return;
    }
    emit(state.copyWith(isButtonLoading: true));
    final res = await paymentsRepository.paymentWebView(
      name: state.list?[state.selectPayment].tag ?? "",
      walletId: event.walletId,
      price: double.tryParse(event.price) ?? 0,
    );
    res.fold(
      (l) async {
        emit(state.copyWith(isButtonLoading: false));
        final res = await AppRoute.goWebView(event.context, url: l);
        if (res) {
          event.onSuccess();
        } else {
          event.onFailure();
        }
      },
      (r) {
        emit(state.copyWith(isButtonLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> sendWallet(SendWallet event, emit) async {
    emit(state.copyWith(isButtonLoading: true));
    final res = await paymentsRepository.sendWallet(
      uuid: event.uuid,
      price: double.tryParse(event.price) ?? 0,
    );
    res.fold(
      (l) async {
        emit(state.copyWith(isButtonLoading: false));
        event.onSuccess();
      },
      (r) {
        emit(state.copyWith(isButtonLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }
}
