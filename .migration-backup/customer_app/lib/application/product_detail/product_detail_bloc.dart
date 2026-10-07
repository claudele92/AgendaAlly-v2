import 'package:dartz/dartz.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../domain/di/dependency_manager.dart';
import '../../domain/model/models.dart';
import '../../infrastructure/local_storage/local_storage.dart';
import '../../infrastructure/service/services.dart';

part 'product_detail_event.dart';

part 'product_detail_state.dart';

part 'product_detail_bloc.freezed.dart';

class ProductDetailBloc extends Bloc<ProductDetailEvent, ProductDetailState> {
  ProductDetailBloc() : super(const ProductDetailState()) {
    int related = 0;
    int buyWith = 0;

    on<FetchViewedProducts>((event, emit) async {
      emit(state.copyWith(viewedProducts: []));
      List<int> list = LocalStorage.getViewedProductsList();
      list.remove(event.productId);
      if (list.isEmpty && LocalStorage.getToken().isEmpty) {
        return;
      }
      final Either<ProductsPaginateResponse, dynamic> res;
      if (LocalStorage.getToken().isEmpty) {
        res = await productsRepository.getProductsByIds(list);
      } else {
        res = await productsRepository.getProductsViewed(
          page: 0,
          productId: event.productId ?? 0,
        );
      }

      res.fold(
        (l) {
          emit(state.copyWith(viewedProducts: l.data ?? []));
          if (l.data?.isEmpty ?? true) {
            event.controller?.loadNoData();
            return;
          }
          event.controller?.loadComplete();
          return;
        },
        (r) {
          event.controller?.loadFailed();
          AppHelpers.errorSnackBar(event.context, typeFixed: true, message: r);
        },
      );
    });

    on<FetchRelatedProduct>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        related = 0;
        emit(state.copyWith(relatedProducts: []));
      }
      final res = await productsRepository.getRelatedProducts(
        page: ++related,
        productUuid: event.productUuid,
      );
      res.fold(
        (l) {
          List<ProductData> list = List.from(state.relatedProducts);
          list.addAll(l.data ?? []);
          emit(state.copyWith(relatedProducts: list));
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
          if (event.isRefresh ?? false) {
            event.controller?.refreshFailed();
          }
          event.controller?.loadFailed();
          AppHelpers.errorSnackBar(event.context, typeFixed: true, message: r);
        },
      );
    });

    on<FetchBuyWithProduct>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        buyWith = 0;
        emit(state.copyWith(recommendedProducts: []));
      }
      final res = await productsRepository.getBuyWithProducts(
        page: ++buyWith,
        productId: event.productId,
      );
      res.fold(
        (l) {
          List<ProductData> list = List.from(state.recommendedProducts);
          list.addAll(l.data ?? []);
          emit(state.copyWith(recommendedProducts: list));
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
          if (event.isRefresh ?? false) {
            event.controller?.refreshFailed();
          }
          event.controller?.loadFailed();
          AppHelpers.errorSnackBar(event.context, typeFixed: true, message: r);
        },
      );
    });

    on<FetchProductById>((event, emit) async {
      LocalStorage.setViewedProductsList(event.product?.id ?? 0);

      emit(
        state.copyWith(
          product: event.product,
          galleries: [Galleries(path: event.product?.img)],
          selectImage: (event.product?.galleries?.isNotEmpty ?? false)
              ? event.product?.galleries?.first
              : Galleries(path: event.product?.img),
        ),
      );
      final res = await productsRepository.getProductDetails(
        event.product?.uuid ?? "",
      );

      res.fold(
        (l) {
          final List<Stocks> stocks = l.data?.stocks ?? <Stocks>[];
          final List<Galleries> galleries = l.data?.galleries ?? [];
          for (var element in stocks) {
            galleries.addAll(element.galleries ?? []);
          }
          emit(
            state.copyWith(
              product: l.data,
              initialStocks: stocks,
              galleries: galleries,
              selectImage: (l.data?.galleries?.isNotEmpty ?? false)
                  ? l.data?.galleries?.first
                  : null,
            ),
          );
          event.controller?.refreshCompleted();
          final int groups = stocks[0].extras?.length ?? 0;
          final List<int> selectedIndexes = List.filled(groups, 0);

          emit(state.copyWith(selectedIndexes: selectedIndexes));

          final int groupsCount = state.initialStocks[0].extras?.length ?? 0;
          final List<TypedExtra> groupExtras = [];
          for (int i = 0; i < groupsCount; i++) {
            if (i == 0) {
              final TypedExtra extras = StockFormat.getFirstExtras(
                state.selectedIndexes[0],
                state.initialStocks,
              );
              groupExtras.add(extras);
            } else {
              final TypedExtra extras = StockFormat.getUniqueExtras(
                groupExtras,
                state.selectedIndexes,
                i,
                state.initialStocks,
              );
              groupExtras.add(extras);
            }
          }
          final Stocks? selectedStock = StockFormat.getSelectedStock(
            groupExtras,
            state.initialStocks,
            state.selectedIndexes,
          );
          int stockCount = 0;
          emit(
            state.copyWith(
              typedExtras: groupExtras,
              selectedStock: selectedStock,
              stockCount: stockCount,
            ),
          );

          return;
        },
        (r) {
          AppHelpers.errorSnackBar(event.context, message: r, typeFixed: true);
        },
      );
    });

    on<SetSelectedIndexes>((event, emit) {
      emit(state.copyWith(selectedIndexes: event.indexes));
      event.context.read<ProductDetailBloc>().add(
        const ProductDetailEvent.updateExtras(),
      );
    });

    on<UpdateSelectedIndexes>((event, emit) {
      final newList = state.selectedIndexes.sublist(0, event.index);
      newList.add(event.value);
      final postList = List.filled(
        state.selectedIndexes.length - newList.length,
        0,
      );
      newList.addAll(postList);
      event.context.read<ProductDetailBloc>().add(
        ProductDetailEvent.setSelectedIndexes(event.context, indexes: newList),
      );
    });

    on<UpdateExtras>((event, emit) {
      final int groupsCount = state.initialStocks[0].extras?.length ?? 0;
      final List<TypedExtra> groupExtras = [];
      for (int i = 0; i < groupsCount; i++) {
        if (i == 0) {
          final TypedExtra extras = StockFormat.getFirstExtras(
            state.selectedIndexes[0],
            state.initialStocks,
          );
          groupExtras.add(extras);
        } else {
          final TypedExtra extras = StockFormat.getUniqueExtras(
            groupExtras,
            state.selectedIndexes,
            i,
            state.initialStocks,
          );
          groupExtras.add(extras);
        }
      }
      final Stocks? selectedStock = StockFormat.getSelectedStock(
        groupExtras,
        state.initialStocks,
        state.selectedIndexes,
      );
      int stockCount = 0;
      emit(
        state.copyWith(
          typedExtras: groupExtras,
          selectedStock: selectedStock,
          stockCount: stockCount,
        ),
      );
    });

    on<UpdateState>((event, emit) {
      emit(state.copyWith(isLoadingNew: !state.isLoadingNew));
      emit(state.copyWith(isLoadingNew: !state.isLoadingNew));
    });

    on<SelectImage>((event, emit) {
      //Do not touch this place, it is necessary for animation when scrolling the image
      emit(
        state.copyWith(
          selectImage: event.image,
          jumpTo: (event.jumpTo ?? false),
          nextImageTo: (event.nextImageTo ?? false),
        ),
      );
    });
  }
}
