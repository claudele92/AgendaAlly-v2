import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/application/product_detail/product_detail_bloc.dart';
import 'package:demand/domain/model/model/product_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../../app_assets.dart';

class ProductTitle extends StatelessWidget {
  final ProductData? product;
  final Stocks? selectStock;
  final CustomColorSet colors;

  const ProductTitle({
    super.key,
    required this.product,
    required this.colors,
    this.selectStock,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16.r),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (selectStock?.discount != null)
            Padding(
              padding: EdgeInsets.only(bottom: 8.r),
              child: Row(
                children: [
                  Container(
                    decoration: BoxDecoration(
                      color: CustomStyle.red,
                      borderRadius: BorderRadius.circular(100.r),
                    ),
                    padding: EdgeInsets.all(6.r),
                    child: Row(
                      children: [
                        SvgPicture.asset(
                          Assets.svgDiscount,
                          colorFilter: ColorFilter.mode(
                            colors.white,
                            BlendMode.srcIn,
                          ),
                        ),
                        4.horizontalSpace,
                        Text(
                          "-${(selectStock?.discount ?? 0) == (selectStock?.price ?? 0) + (selectStock?.tax ?? 0) ? 100 : ((1 - (selectStock?.totalPrice ?? 1) / (selectStock?.price ?? 1)) * 100).toStringAsPrecision(2)} %",
                          style: CustomStyle.interNormal(
                            color: colors.white,
                            size: 14,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Spacer(),
                  Text(
                    AppHelpers.numberFormat(
                      (selectStock?.discount ?? 0) +
                          (selectStock?.totalPrice ?? 0),
                    ),
                    style: CustomStyle.interNoSemi(
                      color: CustomStyle.red,
                      size: 14,
                      decoration: TextDecoration.lineThrough,
                    ),
                  ),
                ],
              ),
            ),
          Row(
            children: [
              SizedBox(
                width: MediaQuery.sizeOf(context).width / 2,
                child: Text(
                  product?.translation?.title ?? "",
                  style: CustomStyle.interSemi(
                    color: colors.textBlack,
                    size: 22,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const Spacer(),
              Text(
                AppHelpers.numberFormat(selectStock?.totalPrice ?? 0),
                style: CustomStyle.interSemi(color: colors.textBlack, size: 22),
              ),
            ],
          ),
          6.verticalSpace,
          Text(
            "${AppHelpers.getTranslation(TrKeys.sku)}: ${product?.id ?? ''}",
            style: CustomStyle.interNormal(color: colors.textBlack, size: 14),
          ),
          if (selectStock?.wholePrices?.isNotEmpty ?? false)
            ListView.builder(
              padding: EdgeInsets.symmetric(vertical: 8.r),
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: selectStock?.wholePrices?.length ?? 0,
              itemBuilder: (context, index) {
                return Row(
                  children: [
                    Text(
                      "${(selectStock?.wholePrices?[index].minQuantity ?? 0) * (product?.interval ?? 1)} - ${(selectStock?.wholePrices?[index].maxQuantity ?? 0) * (product?.interval ?? 1)} ${AppHelpers.getTranslation(product?.unit?.translation?.title ?? "")}",
                      style: CustomStyle.interRegular(
                        color: colors.textBlack,
                        size: 16,
                      ),
                    ),
                    const Spacer(),
                    Text(
                      AppHelpers.numberFormat(
                        selectStock?.wholePrices?[index].price,
                      ),
                      style: CustomStyle.interNoSemi(
                        color: colors.textBlack,
                        size: 18,
                      ),
                    ),
                  ],
                );
              },
            ),
          24.verticalSpace,
          InkWell(
            onTap: () async {
              if (product?.brand != null) {
                await AppRoute.goProductList(
                  context,
                  brandId: product?.brandId,
                  title: product?.brand?.title,
                );
                if (context.mounted) {
                  context.read<ProductDetailBloc>().add(
                    const ProductDetailEvent.updateState(),
                  );
                }
              }
            },
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(AppConstants.radius.r),
                border: Border.all(color: colors.icon),
              ),
              padding: EdgeInsets.all(16.r),
              child: Row(
                children: [
                  CustomNetworkImage(
                    url: product?.brand?.img ?? "",
                    height: 24,
                    width: 24,
                    radius: 0,
                  ),
                  10.horizontalSpace,
                  SizedBox(
                    width: MediaQuery.sizeOf(context).width / 2 - 80.r,
                    child: Text(
                      "${AppHelpers.getTranslation(TrKeys.brand)} - ${product?.brand?.title ?? AppHelpers.getTranslation(TrKeys.unKnow)}",
                      style: CustomStyle.interNormal(
                        textDecoration: TextDecoration.underline,
                        color: colors.textBlack,
                        size: 14,
                      ),
                    ),
                  ),
                  const Spacer(),
                  Container(
                    width: 6.r,
                    height: 6.r,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: colors.icon,
                    ),
                  ),
                  const Spacer(),
                  InkWell(
                    onTap: () async {
                      if (product?.shop != null) {
                        await AppRouteShop.goShopPage(
                          context,
                          shop: product?.shop,
                        );
                        if (context.mounted) {
                          context.read<ProductDetailBloc>().add(
                            const ProductDetailEvent.updateState(),
                          );
                        }
                      }
                    },
                    child: Row(
                      children: [
                        Icon(
                          FlutterRemix.store_2_fill,
                          color: colors.textBlack,
                        ),
                        10.horizontalSpace,
                        SizedBox(
                          width: MediaQuery.sizeOf(context).width / 2 - 80.r,
                          child: Text(
                            product?.shop?.translation?.title ??
                                AppHelpers.getTranslation(TrKeys.unKnow),
                            style: CustomStyle.interNormal(
                              textDecoration: TextDecoration.underline,
                              color: colors.textBlack,
                              size: 14,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          8.verticalSpace,
          Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(AppConstants.radius.r),
              border: Border.all(color: colors.icon),
            ),
            padding: EdgeInsets.all(16.r),
            child: Row(
              children: [
                ((selectStock?.quantity ?? 0) > 0)
                    ? SvgPicture.asset(Assets.svgInStock)
                    : SvgPicture.asset(
                        Assets.svgOutOfStock,
                        colorFilter: ColorFilter.mode(
                          colors.textBlack,
                          BlendMode.srcIn,
                        ),
                      ),
                10.horizontalSpace,
                Text(
                  "${AppHelpers.getTranslation(TrKeys.inStock)} - ${(selectStock?.quantity ?? 0) > 0 ? (selectStock?.quantity ?? 0) : 0} ${AppHelpers.getTranslation(TrKeys.products)}",
                  style: CustomStyle.interNormal(
                    color: colors.textBlack,
                    size:
                        "${AppHelpers.getTranslation(TrKeys.inStock)} - ${(selectStock?.quantity ?? 0) > 0 ? (selectStock?.quantity ?? 0) : 0} ${AppHelpers.getTranslation(TrKeys.products)}"
                                .length >
                            32
                        ? 14
                        : 12,
                  ),
                ),
                const Spacer(),
                Container(
                  width: 6.r,
                  height: 6.r,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: colors.icon,
                  ),
                ),
                const Spacer(),
                SvgPicture.asset(Assets.svgStart, height: 16.r),
                6.horizontalSpace,
                Text(
                  "${product?.ratingAvg?.toStringAsPrecision(2) ?? "0.0"} ${AppHelpers.getTranslation(TrKeys.rating).toLowerCase()}",
                  style: CustomStyle.interNormal(
                    color: colors.textBlack,
                    size: 14,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
