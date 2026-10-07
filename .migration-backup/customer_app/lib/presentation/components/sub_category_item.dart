import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/domain/model/response/categories_paginate_response.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';

class SubCategoryItem extends StatelessWidget {
  final CategoryData? categoryData;

  const SubCategoryItem({super.key, required this.categoryData});

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: () async {
        await AppRoute.goProductList(
          context,
          title: categoryData?.translation?.title ?? "",
          categoryId: categoryData?.id,
        );
        if (context.mounted) {
          context.read<ProductBloc>().add(const ProductEvent.updateState());
        }
      },
      child: Container(
        padding: EdgeInsets.all(8.r),
        width: 90.r,
        decoration: BoxDecoration(
          color: CustomStyle.subCategory,
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        child: Column(
          children: [
            CustomNetworkImage(
              url: categoryData?.img ?? "",
              height: 64,
              width: 64,
              radius: 8,
            ),
            6.verticalSpace,
            Text(
              categoryData?.translation?.title ?? "",
              style: CustomStyle.interNormal(
                color: CustomStyle.black,
                size: 12,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
