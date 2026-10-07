import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:lottie/lottie.dart';

import '../../../../app_assets.dart';

class NewShopsOneProductList extends StatelessWidget {
  final CustomColorSet colors;
  final int shopId;

  const NewShopsOneProductList({
    super.key,
    required this.colors,
    required this.shopId,
  });

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ProductBloc, ProductState>(
      builder: (context, state) {
        return state.newShopProduct.isNotEmpty || state.isLoadingNew
            ? Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  32.verticalSpace,
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.newArrivals),
                    titleColor: colors.textBlack,
                    subTitle: AppHelpers.getTranslation(TrKeys.seeAll),
                    onTap: () async {
                      await AppRoute.goProductList(
                        context,
                        list: state.newShopProduct,
                        title: AppHelpers.getTranslation(TrKeys.newArrivals),
                        isNewProduct: true,
                        isMostSaleProduct: false,
                        shopId: shopId,
                      );
                      if (context.mounted) {
                        context.read<ProductBloc>().add(
                          const ProductEvent.updateState(),
                        );
                      }
                    },
                  ),
                  16.verticalSpace,
                  !state.isLoadingNew
                      ? GridView.builder(
                          physics: const NeverScrollableScrollPhysics(),
                          shrinkWrap: true,
                          padding: EdgeInsets.only(
                            right: 16.r,
                            left: 16.r,
                            top: 16.r,
                          ),
                          gridDelegate:
                              SliverGridDelegateWithFixedCrossAxisCount(
                                childAspectRatio: 0.8.r,
                                crossAxisCount: 2,
                                mainAxisExtent: 360.r,
                              ),
                          itemCount: state.newShopProduct.length,
                          itemBuilder: (context, index) {
                            return Padding(
                              padding: EdgeInsets.symmetric(horizontal: 4.r),
                              child: ProductItem(
                                product: state.newShopProduct[index],
                              ),
                            );
                          },
                        )
                      : const ProductsShimmer(),
                ],
              )
            : (state.newShopProduct.isNotEmpty || state.isLoadingNew)
            ? const SizedBox.shrink()
            : Column(
                children: [
                  42.verticalSpace,
                  Lottie.asset(Assets.lottieEmptyList),
                  24.verticalSpace,
                  Text(
                    AppHelpers.getTranslation(
                      TrKeys.thereAreNoProductsThereYet,
                    ),
                    style: CustomStyle.interNormal(color: colors.textBlack),
                  ),
                ],
              );
      },
    );
  }
}
