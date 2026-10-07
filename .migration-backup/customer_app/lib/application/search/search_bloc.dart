import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/model/model/service_model.dart';
import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:demand/domain/model/model/brand_data.dart';
import 'package:demand/domain/model/model/product_model.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/domain/model/response/categories_paginate_response.dart';
import 'package:demand/infrastructure/service/services.dart';

part 'search_event.dart';

part 'search_state.dart';

part 'search_bloc.freezed.dart';

class SearchBloc extends Bloc<SearchEvent, SearchState> {
  SearchBloc() : super(const SearchState()) {
    int? shopId;

    on<SearchShops>((event, emit) async {
      emit(state.copyWith(isShopLoading: true));

      final res = await shopsRepository.getAllShops(
        query: state.query,
        page: 1,
      );
      res.fold(
        (l) {
          emit(state.copyWith(isShopLoading: false, shops: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isShopLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SetQuery>((event, emit) async {
      shopId = event.shopId;
      emit(state.copyWith(query: event.query));
    });

    on<SearchProduct>((event, emit) async {
      emit(state.copyWith(isProductLoading: true));

      final res = await productsRepository.fetchProducts(
        query: state.query,
        page: 1,
        shopId: shopId,
      );
      res.fold(
        (l) {
          emit(state.copyWith(isProductLoading: false, products: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isProductLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SearchService>((event, emit) async {
      emit(state.copyWith(isServiceLoading: true));

      final res = await serviceRepository.getAllService(
        query: state.query,
        page: 1,
        shopId: shopId,
      );
      res.fold(
        (l) {
          emit(state.copyWith(isServiceLoading: false, services: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isServiceLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SearchCategory>((event, emit) async {
      emit(state.copyWith(isCategoryLoading: true));

      final res = await categoriesRepository.getAllCategories(
        query: state.query,
        page: 1,
      );
      res.fold(
        (l) {
          emit(
            state.copyWith(isCategoryLoading: false, categories: l.data ?? []),
          );
        },
        (r) {
          emit(state.copyWith(isCategoryLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SearchBrand>((event, emit) async {
      emit(state.copyWith(isBrandLoading: true));

      final res = await brandsRepository.getAllBrands(
        query: state.query,
        page: 1,
      );
      res.fold(
        (l) {
          emit(state.copyWith(isBrandLoading: false, brands: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isBrandLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<UpdateRecently>((event, emit) async {
      emit(state.copyWith(isShopLoading: true));
      emit(state.copyWith(isShopLoading: false));
    });
  }
}
