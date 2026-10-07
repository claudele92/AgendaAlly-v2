import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class NewProductThreeList extends StatelessWidget {
  final CustomColorSet colors;

  const NewProductThreeList({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ProductBloc, ProductState>(
      builder: (context, state) {
        return state.newProduct.isNotEmpty || state.isLoadingNew
            ? Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  16.verticalSpace,
                  Divider(color: CustomStyle.dividerColor, thickness: 16.r),
                  16.verticalSpace,
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.newArrivals),
                    titleColor: colors.textBlack,
                    subTitle: AppHelpers.getTranslation(TrKeys.seeAll),
                    onTap: () async {
                      await AppRoute.goProductList(
                        context,
                        list: state.newProduct,
                        title: AppHelpers.getTranslation(TrKeys.newArrivals),
                        total: state.newProductCount,
                        isNewProduct: true,
                        isMostSaleProduct: false,
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
                    height: 330.r,
                    child: ListView.builder(
                      padding: EdgeInsets.symmetric(horizontal: 16.r),
                      shrinkWrap: true,
                      scrollDirection: Axis.horizontal,
                      itemCount: state.newProduct.length,
                      itemBuilder: (context, index) {
                        return Padding(
                          padding: EdgeInsets.only(right: 8.r),
                          child: ProductItem(
                            product: state.newProduct[index],
                            height: 260,
                            width: 236,
                          ),
                        );
                      },
                    ),
                  ),
                ],
              )
            : const SizedBox.shrink();
      },
    );
  }
}
