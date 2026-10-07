import 'package:demand/domain/model/request/add_user_address_model.dart';
import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:google_place/google_place.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/model/model/country_model.dart';
import 'package:demand/domain/model/response/city_pagination_response.dart';
import 'package:demand/domain/model/response/user_address_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

part 'address_event.dart';

part 'address_state.dart';

part 'address_bloc.freezed.dart';

class AddressBloc extends Bloc<AddressEvent, AddressState> {
  int page = 0;
  int city = 0;

  AddressBloc() : super(const AddressState()) {
    on<SelectAddress>(selectAddress);

    on<EditAddress>(editAddress);

    on<AddAddress>(addAddress);

    on<FetchUserAddress>(fetchUserAddress);

    on<SearchAddress>(searchAddress);

    on<FetchCountry>(fetchCountry);

    on<SearchCountry>(searchCountry);

    on<FetchCity>(fetchCity);

    on<SearchCity>(searchCity);
  }

  void selectAddress(SelectAddress event, emit) {
    emit(state.copyWith(selectAddress: event.index));
  }

  Future<void> editAddress(EditAddress event, emit) async {
    emit(state.copyWith(isButtonLoading: true));
    final res = await addressRepository.editUserAddress(
      addressModel: event.editUserAddress,
      addressId: event.addressId ?? 0,
    );
    res.fold((l) {
      event.onSuccess?.call();
    }, (r) => AppHelpers.errorSnackBar(event.context, message: r));
    emit(state.copyWith(isButtonLoading: false));
  }

  Future<void> addAddress(AddAddress event, emit) async {
    emit(state.copyWith(isButtonLoading: true));
    final res = await addressRepository.addUserAddress(
      addressModel: event.addUserAddress,
    );
    res.fold((l) {
      event.onSuccess?.call();
    }, (r) => AppHelpers.errorSnackBar(event.context, message: r));
    emit(state.copyWith(isButtonLoading: false));
  }

  Future<void> fetchUserAddress(FetchUserAddress event, emit) async {
    if (event.isRefresh ?? false) {
      event.controller?.resetNoData();
      page = 0;
      emit(state.copyWith(address: [], isLoading: true));
    }
    final res = await addressRepository.getUserAddress(page: ++page);
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
  }

  Future<void> searchAddress(SearchAddress event, emit) async {
    final res = await GooglePlace(AppHelpers.getMapKey()).autocomplete.get(
      event.search ?? "",
      components: [Component("country", "gb")],
    );
    emit(state.copyWith(autoTitles: res?.predictions ?? []));
  }

  Future<void> fetchCountry(FetchCountry event, emit) async {
    if (event.isRefresh ?? false) {
      event.controller?.resetNoData();
      page = 0;
      emit(state.copyWith(countries: [], isLoading: true));
    }
    final res = await addressRepository.getCountry(page: ++page);
    res.fold(
      (l) {
        List<CountryModel> list = List.from(state.countries);
        list.addAll(l.data ?? []);
        emit(state.copyWith(isLoading: false, countries: list));
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
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> searchCountry(SearchCountry event, emit) async {
    final res = await addressRepository.searchCountry(
      search: event.search ?? "",
    );
    res.fold(
      (l) {
        emit(state.copyWith(countries: l.data ?? []));
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> fetchCity(FetchCity event, emit) async {
    if (event.isRefresh ?? false) {
      event.controller?.resetNoData();
      city = 0;
      emit(state.copyWith(cities: [], isCityLoading: true));
    }
    final res = await addressRepository.getCity(
      page: ++city,
      countyId: event.countyId,
    );
    res.fold(
      (l) {
        List<CityModel> list = List.from(state.cities);
        list.addAll(l.data ?? []);
        emit(state.copyWith(isCityLoading: false, cities: list));
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
        emit(state.copyWith(isCityLoading: false));
        if (event.isRefresh ?? false) {
          event.controller?.refreshFailed();
        }
        event.controller?.loadFailed();

        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> searchCity(SearchCity event, emit) async {
    final res = await addressRepository.searchCity(
      search: event.search ?? "",
      countyId: event.countyId,
    );
    res.fold(
      (l) {
        emit(state.copyWith(cities: l.data ?? []));
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }
}
