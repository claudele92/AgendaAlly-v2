import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class ForYouAndDiscount extends StatelessWidget {
  final CustomColorSet colors;

  const ForYouAndDiscount({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        BlocBuilder<MainBloc, MainState>(
          buildWhen: (p, n) {
            return p.selectForYou != n.selectForYou;
          },
          builder: (context, state) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    16.horizontalSpace,
                    Expanded(
                      flex: 4,
                      child: Row(
                        spacing: 16.r,
                        children: [
                          SecondButton(
                            title: AppHelpers.getTranslation(TrKeys.forYou),
                            bgColor: state.selectForYou
                                ? colors.textBlack
                                : colors.newBoxColor,
                            titleColor: state.selectForYou
                                ? colors.textWhite
                                : colors.textBlack,
                            onTap: () {
                              context.read<MainBloc>().add(
                                const MainEvent.changeForYou(value: true),
                              );
                            },
                          ),
                          SecondButton(
                            title: AppHelpers.getTranslation(TrKeys.sale),
                            bgColor: state.selectForYou
                                ? colors.newBoxColor
                                : colors.textBlack,
                            titleColor: state.selectForYou
                                ? colors.textBlack
                                : colors.textWhite,
                            onTap: () {
                              context.read<MainBloc>().add(
                                const MainEvent.changeForYou(value: false),
                              );
                            },
                          ),
                        ],
                      ),
                    ),
                    Expanded(
                      child: ButtonEffectAnimation(
                        onTap: () async {
                          if (state.selectForYou) {
                            await AppRoute.goProductList(
                              context,
                              title: AppHelpers.getTranslation(
                                TrKeys.newArrivals,
                              ),
                              isNewProduct: true,
                              isMostSaleProduct: false,
                            );
                            if (context.mounted) {
                              context.read<ProductBloc>().add(
                                const ProductEvent.updateState(),
                              );
                            }
                            return;
                          }
                          await AppRoute.goProductList(
                            context,
                            title: AppHelpers.getTranslation(TrKeys.mostSales),
                            isNewProduct: false,
                            isMostSaleProduct: true,
                          );
                          if (context.mounted) {
                            context.read<ProductBloc>().add(
                              const ProductEvent.updateState(),
                            );
                          }
                        },
                        child: Text(
                          AppHelpers.getTranslation(TrKeys.seeAll),
                          style: CustomStyle.interNormal(
                            color: CustomStyle.seeAllColor,
                            size: 14,
                          ),
                          textAlign: TextAlign.end,
                        ),
                      ),
                    ),
                    16.horizontalSpace,
                  ],
                ),
                BlocBuilder<ProductBloc, ProductState>(
                  builder: (context, productState) {
                    final List list = state.selectForYou
                        ? productState.newProduct
                        : productState.mostSaleProduct;
                    return GridView.builder(
                      physics: const NeverScrollableScrollPhysics(),
                      shrinkWrap: true,
                      padding: EdgeInsets.only(
                        right: 16.r,
                        left: 16.r,
                        top: 16.r,
                      ),
                      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                        childAspectRatio: 0.8.r,
                        crossAxisCount: 2,
                        mainAxisExtent: 330.r,
                      ),
                      itemCount: list.length,
                      itemBuilder: (context, index) {
                        return Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4.r),
                          child: ProductItem(product: list[index], height: 224),
                        );
                      },
                    );
                  },
                ),
              ],
            );
          },
        ),
      ],
    );
  }
}
