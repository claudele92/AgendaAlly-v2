import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/product_detail/product_detail_bloc.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/domain/model/model/review_data.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/product_detail/widgets/bottom_widget.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'widgets/image_screen_one.dart';
import 'widgets/product_extras_one.dart';
import 'widgets/product_info_one.dart';
import 'widgets/product_title_one.dart';
import 'widgets/related_and_viewed_products_one.dart';

class ProductOnePage extends StatefulWidget {
  const ProductOnePage({super.key});

  @override
  State<ProductOnePage> createState() => _ProductOnePageState();
}

class _ProductOnePageState extends State<ProductOnePage> {
  late PageController pageController;

  @override
  void initState() {
    pageController = PageController();

    super.initState();
  }

  @override
  void dispose() {
    pageController.dispose();

    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => BlocConsumer<ProductDetailBloc, ProductDetailState>(
        listenWhen: (p, n) {
          return p.selectImage?.id != n.selectImage?.id ||
              p.galleries.length != n.galleries.length;
        },
        listener: (context, state) {
          if (state.galleries.length == 1) {
            return;
          }
          if (!state.jumpTo) {
            pageController.jumpToPage(
              state.galleries.indexOf(state.selectImage ?? Galleries()),
            );
          }
        },
        builder: (context, state) {
          return SafeArea(
            bottom: false,
            child: Column(
              children: [
                _appBar(colors, state, context),
                Expanded(
                  child: SingleChildScrollView(
                    child: Padding(
                      padding: EdgeInsets.only(bottom: 170.r),
                      child: Column(
                        children: [
                          ImageOneScreen(
                            controller: pageController,
                            colors: colors,
                            product: state.product,
                            selectImage: state.selectImage,
                            galleries: state.galleries,
                          ),
                          20.verticalSpace,
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              ProductTitleOne(
                                product: state.product,
                                colors: colors,
                                selectStock: state.selectedStock,
                              ),
                              ProductExtrasOne(
                                list: state.galleries,
                                stocks: state.product?.stocks ?? [],
                                types: state.typedExtras,
                                colors: colors,
                                selectStock: state.selectedStock,
                              ),
                              RelatedAndViewedProductsOne(
                                colors: colors,
                                list: state.recommendedProducts,
                                title: AppHelpers.getTranslation(
                                  TrKeys.withThisProductAlsoBuy,
                                ),
                              ),
                              RelatedAndViewedProductsOne(
                                colors: colors,
                                list: state.relatedProducts,
                                title: AppHelpers.getTranslation(
                                  TrKeys.relatedProducts,
                                ),
                              ),
                              ProductInfoOne(
                                colors: colors,
                                product: state.product,
                              ),
                              RelatedAndViewedProductsOne(
                                colors: colors,
                                list: state.viewedProducts,
                                title: AppHelpers.getTranslation(
                                  TrKeys.historyView,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) =>
          BlocBuilder<ProductDetailBloc, ProductDetailState>(
            builder: (context, state) {
              return BottomWidget(
                selectStock: state.selectedStock,
                colors: colors,
                product: state.product,
              );
            },
          ),
    );
  }

  Widget _appBar(
    CustomColorSet colors,
    ProductDetailState state,
    BuildContext context,
  ) {
    return Row(
      children: [
        4.horizontalSpace,
        BackButton(),
        const Spacer(),
        IconButton(
          splashColor: CustomStyle.transparent,
          highlightColor: CustomStyle.transparent,
          onPressed: () {
            LocalStorage.setCompareList(state.product?.id ?? 0);
            context.read<ProductDetailBloc>().add(
              const ProductDetailEvent.updateState(),
            );
          },
          icon: LocalStorage.getCompareList().contains(state.product?.id)
              ? Icon(
                  FlutterRemix.arrow_up_down_fill,
                  color: CustomStyle.primary,
                  size: 24.r,
                )
              : Icon(
                  FlutterRemix.arrow_up_down_line,
                  size: 24.r,
                  color: colors.textBlack,
                ),
        ),
        IconButton(
          splashColor: CustomStyle.transparent,
          highlightColor: CustomStyle.transparent,
          onPressed: () {
            LocalStorage.setLikedProductsList(state.product?.id ?? 0);
            context.read<ProductBloc>().add(const ProductEvent.updateState());
            context.read<ProductDetailBloc>().add(
              const ProductDetailEvent.updateState(),
            );
          },
          icon: LocalStorage.getLikedProductsList().contains(state.product?.id)
              ? Icon(
                  FlutterRemix.heart_3_fill,
                  color: CustomStyle.red,
                  size: 24.r,
                )
              : Icon(
                  FlutterRemix.heart_3_line,
                  size: 24.r,
                  color: colors.textBlack,
                ),
        ),
      ],
    );
  }
}
