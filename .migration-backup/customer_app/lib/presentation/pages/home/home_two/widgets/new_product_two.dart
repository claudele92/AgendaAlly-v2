import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class NewProductTwoList extends StatelessWidget {
  final CustomColorSet colors;

  const NewProductTwoList({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ProductBloc, ProductState>(
      builder: (context, state) {
        return state.newProduct.isNotEmpty || state.isLoadingNew
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
                  ListView.builder(
                    physics: const NeverScrollableScrollPhysics(),
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    shrinkWrap: true,
                    itemCount: state.newProduct.length,
                    itemBuilder: (context, index) {
                      return Padding(
                        padding: EdgeInsets.only(right: 8.r),
                        child: OneProductItem(
                          height: 360,
                          product: state.newProduct[index],
                        ),
                      );
                    },
                  ),
                ],
              )
            : const SizedBox.shrink();
      },
    );
  }
}
