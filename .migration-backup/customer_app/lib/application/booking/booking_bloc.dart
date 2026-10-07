import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/domain/model/model/service_model.dart';
import 'package:demand/domain/model/response/booking_calculate_response.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/domain/model/response/check_time_response.dart';
import 'package:demand/domain/model/response/form_options_response.dart';
import 'package:demand/domain/model/response/my_gift_cart_response.dart';
import 'package:demand/domain/model/response/payments_response.dart';
import 'package:demand/domain/model/response/service_of_master_response.dart';
import 'package:demand/domain/model/response/user_address_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

part 'booking_event.dart';

part 'booking_state.dart';

part 'booking_bloc.freezed.dart';

class BookingBloc extends Bloc<BookingEvent, BookingState> {
  BookingBloc() : super(const BookingState()) {
    int pagePast = 0;
    int pageUpcoming = 0;

    on<FetchBookPast>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        pagePast = 0;
        emit(state.copyWith(past: [], isLoading: true));
      }
      final res = await bookingRepository.getBooks(
        page: ++pagePast,
        type: TrKeys.past,
      );
      res.fold(
        (l) {
          List<BookingModel> list = List.from(state.past);
          list.addAll(l.data ?? []);
          emit(state.copyWith(isLoading: false, past: list));
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

    on<SetWalletPrice>((event, emit) {
      final payment =
          ((event.price ?? -1) >= (state.calculate?.data?.totalPrice ?? 0))
          ? state.payments.firstWhere((element) => element.tag == "wallet")
          : state.selectedPayment;
      emit(state.copyWith(walletPrice: event.price, selectedPayment: payment));
    });

    on<FetchBookUpcoming>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        pageUpcoming = 0;
        emit(state.copyWith(upcoming: [], isLoading: true));
      }
      final res = await bookingRepository.getBooks(
        page: ++pageUpcoming,
        type: TrKeys.upcoming,
      );
      res.fold(
        (l) {
          List<BookingModel> list = List.from(state.upcoming);
          list.addAll(l.data ?? []);
          emit(state.copyWith(isLoading: false, upcoming: list));
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

    on<FetchBookingById>((event, emit) async {
      emit(state.copyWith(upcoming: [], isLoading: true));
      final res = await bookingRepository.getBookingById(id: event.id);
      res.fold(
        (l) {
          emit(state.copyWith(isLoading: false, upcoming: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<BookingService>((event, emit) async {
      emit(state.copyWith(isButtonLoading: true));
      final payment = state.payments.firstWhere(
        (element) => element.id == state.selectedPayment?.id,
        orElse: () => PaymentData(),
      );
      final num wallet = LocalStorage.getUser().wallet?.price ?? 0;
      if (payment.tag == "wallet" && wallet < event.totalPrice) {
        AppHelpers.errorSnackBar(
          event.context,
          message: AppHelpers.getTranslation(TrKeys.notEnoughMoney),
        );
        emit(state.copyWith(isButtonLoading: false));
        return;
      }

      final res = await bookingRepository.bookingService(
        serviceMasters: state.selectMasters.values.toList(),
        giftCartId: state.giftCart?.id,
        payment: state.selectedPayment,
        coupon: state.coupon,
        walletPrice: state.walletPrice,
      );
      res.fold(
        (id) {
          if (payment.tag != "cash" && payment.tag != "wallet") {
            event.onSuccess.call(id);
            userRepository.getProfileDetails(event.context);
            return;
          }
          event.onSuccess.call(-1);
          emit(state.copyWith(isButtonLoading: false));
          userRepository.getProfileDetails(event.context);
        },
        (r) {
          emit(state.copyWith(isButtonLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<PayLater>((event, emit) async {
      emit(state.copyWith(isButtonLoading: true));
      final payment = state.payments.firstWhere(
        (element) => element.id == state.selectedPayment?.id,
        orElse: () => PaymentData(),
      );
      final num wallet = LocalStorage.getUser().wallet?.price ?? 0;
      if (payment.tag == "wallet" && wallet < event.totalPrice) {
        AppHelpers.errorSnackBar(
          event.context,
          message: AppHelpers.getTranslation(TrKeys.notEnoughMoney),
        );
        emit(state.copyWith(isButtonLoading: false));
        return;
      }
      if (payment.tag != "cash" && payment.tag != "wallet") {
        event.onSuccess.call(event.id ?? 0);
        return;
      }
      event.onSuccess.call(-1);
    });

    on<FetchPayments>((event, emit) async {
      emit(state.copyWith(isPaymentLoading: true));
      final res = await paymentsRepository.getPayments();
      res.fold(
        (l) {
          int index = 0;
          for (int i = 0; i < (l.data?.length ?? 0); i++) {
            if (l.data?[i].tag == "cash") {
              index = i;
            }
          }
          final list = event.payLater
              ? l.data
                    ?.where((e) => e.tag != 'cash' && e.tag != 'wallet')
                    .toList()
              : l.data;
          emit(
            state.copyWith(
              payments: list ?? [],
              selectedPayment: event.payLater ? l.data?.first : l.data?[index],
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
      final payment = state.payments.firstWhere(
        (element) => element.id == state.selectedPayment?.id,
        orElse: () => PaymentData(),
      );
      final res = await paymentsRepository.paymentWebView(
        name: payment.tag ?? "",
        bookingId: event.id,
      );
      res.fold(
        (l) async {
          emit(state.copyWith(isButtonLoading: false));
          event.onSuccess.call(l);
        },
        (r) {
          emit(state.copyWith(isButtonLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
          event.onFailure?.call(r);
        },
      );
    });

    on<CancelBook>((event, emit) async {
      emit(state.copyWith(isButtonLoading: true));
      final res = await bookingRepository.cancelBook(event.id);
      res.fold(
        (l) async {
          List<BookingModel> list = List.from(state.upcoming);
          list.removeWhere((element) => element.id == event.id);
          emit(state.copyWith(isButtonLoading: false, upcoming: list));
          event.onSuccess?.call();
        },
        (r) {
          emit(state.copyWith(isButtonLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<CalculateBooking>((event, emit) async {
      emit(state.copyWith(isLoading: true));
      final res = await bookingRepository.calculateBooking(
        selectMasters: state.selectMasters,
        giftCartId: state.giftCart?.id,
        coupon: state.coupon,
      );
      res.fold(
        (l) async {
          emit(state.copyWith(isLoading: false, calculate: l));
        },
        (r) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<CheckTime>((event, emit) async {
      emit(state.copyWith(isLoading: true));

      final res = await bookingRepository.checkTime(
        serviceId: state.selectMasters.values
            .map((e) => e.serviceMaster?.id ?? 0)
            .toList(),
        start: event.startTime,
      );
      res.fold(
        (l) async {
          emit(
            state.copyWith(
              isLoading: false,
              listDate: l.data ?? [],
              selectDateTime: DateTime.now(),
            ),
          );
        },
        (r) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SelectPayment>((event, emit) {
      emit(state.copyWith(selectedPayment: event.payment));
    });

    on<SelectBookingTime>((event, emit) {
      emit(state.copyWith(selectBookTime: event.time));
    });

    on<SelectDateTime>((event, emit) {
      emit(state.copyWith(selectDateTime: event.selectDateTime));
    });

    on<SelectAddress>((event, emit) {
      Map<int, MasterModel> maps = Map.from(state.selectMasters);
      maps.forEach((key, value) {
        if (value.serviceMaster?.type == TrKeys.offlineOut) {
          maps[key] = value.copyWith(address: event.address);
        }
      });
      emit(state.copyWith(selectMasters: maps));
    });

    on<SetServices>((event, emit) async {
      emit(
        state.copyWith(selectServices: event.services, isLoadingMaster: true),
      );
      if (event.master != null) {
        final res = await bookingRepository.getServiceOfMasters(
          masterId: event.master?.id,
        );
        res.fold((l) {
          Map<int, MasterModel> selectMasters = {};
          for (var element in event.services) {
            if (!(l.data?.any((e) => e.serviceId == element.id) ?? false)) {
              emit(state.copyWith(isLoadingMaster: false));
              event.onFailure?.call();
              return;
            }
            final serviceOfMaster = l.data?.firstWhere(
              (e) => e.serviceId == element.id,
              orElse: () => ServiceOfMaster(),
            );
            if (serviceOfMaster != null) {
              final service = event.master?.serviceMaster?.service?.copyWith(
                type: serviceOfMaster.type,
                selectExtras: element.selectExtras,
              );

              final serviceMaster =
                  event.master?.serviceMaster?.copyWith(
                    id: serviceOfMaster.id,
                    service: service,
                  ) ??
                  ServiceMaster(
                    id: serviceOfMaster.id,
                    service: service,
                    shopId: event.master?.invite?.shopId ?? 0,
                  );

              selectMasters[element.id ?? 0] =
                  event.master?.copyWith(serviceMaster: serviceMaster) ??
                  MasterModel();
            }
          }
          emit(
            state.copyWith(
              selectMasters: selectMasters,
              isLoadingMaster: false,
            ),
          );
          event.onSuccess?.call();
        }, (r) {});
      } else {
        emit(state.copyWith(isLoadingMaster: false));
        event.onSuccess?.call();
      }
    });

    on<SelectMaster>((event, emit) {
      Map<int, MasterModel> maps = Map.from(state.selectMasters);
      List<ServiceModel> service = List.from(state.selectServices);
      final extra = service
          .firstWhere(
            (e) => e.id == event.serviceId,
            orElse: () => ServiceModel(),
          )
          .selectExtras;
      final master = event.master?.copyWith(
        serviceMaster: event.master?.serviceMaster?.copyWith(
          service: event.master?.serviceMaster?.service?.copyWith(
            selectExtras: extra,
          ),
        ),
      );
      maps.addAll({event.serviceId ?? 0: master ?? MasterModel()});
      emit(state.copyWith(selectMasters: maps));
    });

    on<SelectTime>((event, emit) {
      Map<int, MasterModel> maps = Map.from(state.selectMasters);
      maps.forEach((i, e) {
        if (e.serviceMaster?.id == event.serviceId) {
          e = e.copyWith(time: event.selectTime);
          maps[i] = e;
          emit(state.copyWith(selectMasters: maps));
          return;
        }
      });
    });

    on<SetGiftCart>((event, emit) {
      emit(state.copyWith(giftCart: event.giftCart));
    });

    on<SaveForm>((event, emit) async {
      emit(state.copyWith(isButtonLoading: true));
      List<FormOptionsData?> forms = List.from(
        state.upcoming.map((e) => e.address?.forms),
      );
      List<int> ids = forms.map((e) => e?.id ?? 0).toList();
      ids.sort();
      int index = AppHelpers.searchIndex(ids, event.form?.id ?? 0);
      if (index != -1) {
        forms.removeAt(index);
        forms.insert(index, event.form);
      } else {
        forms.add(event.form);
      }

      final res = await bookingRepository.saveForm(
        form: forms,
        id: state.upcoming.first.id ?? 0,
      );
      res.fold(
        (l) {
          emit(state.copyWith(isButtonLoading: false));
          event.onSuccess?.call();
        },
        (r) {
          emit(state.copyWith(isButtonLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<UpdateNotes>((event, emit) async {
      emit(state.copyWith(isLoading: true));
      final res = await bookingRepository.updateBookingNotes(
        id: event.id,
        note: event.message,
      );
      res.fold(
        (l) {
          emit(state.copyWith(isLoading: false));
          event.onSuccess?.call();
        },
        (r) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });
    on<CheckCoupon>((event, emit) async {
      if (event.clear ?? false) {
        emit(state.copyWith(coupon: null));
        return;
      }
      if (event.coupon.isEmpty) {
        emit(state.copyWith(coupon: null));
        return;
      }

      final res = await cartRepository.checkCoupon(
        coupon: event.coupon,
        shopId: event.shopId,
      );
      res.fold(
        (l) {
          if (l == 'booking_total_price') {
            emit(state.copyWith(coupon: event.coupon));
          }
        },
        (r) {
          emit(state.copyWith(coupon: null));
          if (event.coupon.trim().isNotEmpty) {
            AppHelpers.errorSnackBar(event.context, message: r);
          }
        },
      );
    });
  }
}
