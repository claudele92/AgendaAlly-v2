import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class NewShopsProductList extends StatelessWidget {
  final CustomColorSet colors;
  final int shopId;

  const NewShopsProductList({
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
                  24.verticalSpace,
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.allProduct),
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
                  SizedBox(
                    height: 348.r,
                    child: !state.isLoadingNew
                        ? ListView.builder(
                            padding: EdgeInsets.symmetric(horizontal: 16.r),
                            shrinkWrap: true,
                            scrollDirection: Axis.horizontal,
                            itemCount: state.newShopProduct.length,
                            itemBuilder: (context, index) {
                              return Padding(
                                padding: EdgeInsets.only(right: 8.r),
                                child: ProductItem(
                                  product: state.newShopProduct[index],
                                ),
                              );
                            },
                          )
                        : const HProductShimmer(),
                  ),
                ],
              )
            : const SizedBox.shrink();
      },
    );
  }
}
