import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/svg.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/domain/model/model/product_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/components/product_items/product_info_two.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:demand/presentation/style/theme/theme_wrapper.dart';

import '../../app_assets.dart';
import 'product_info.dart';

class OneProductItem extends StatelessWidget {
  final ProductData product;
  final double height;

  OneProductItem({super.key, required this.product, this.height = 180});

  final List<Extras> listExtra = [];

  @override
  Widget build(BuildContext context) {
    if (AppHelpers.getType() != 0) {
      product.stocks?.forEach((stocks) {
        stocks.extras?.forEach((extra) {
          if (extra.group?.type == "color") {
            if (listExtra.isEmpty) {
              listExtra.add(extra);
              return;
            }
            for (var element in listExtra) {
              if (element.value == extra.value) {
                return;
              }
            }
            listExtra.add(extra);
          }
        });
      });
    }
    return ThemeWrapper(
      builder: (colors, controller) {
        return Padding(
          padding: EdgeInsets.only(bottom: 8.r),
          child: InkWell(
            onTap: () async {
              await AppRoute.goProductPage(context, product: product);
              if (context.mounted) {
                context.read<ProductBloc>().add(
                  const ProductEvent.updateState(),
                );
              }
            },
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(AppConstants.radius.r),
                color: colors.transparent,
                border: Border.all(
                  color: AppHelpers.getType() == 2
                      ? colors.transparent
                      : colors.icon,
                ),
              ),
              child: Column(
                children: [
                  SizedBox(
                    width: double.infinity,
                    height: height.h,
                    child: Stack(
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            borderRadius: AppHelpers.getType() == 2
                                ? BorderRadius.circular(10.r)
                                : BorderRadius.zero,
                            border: Border.all(
                              color: AppHelpers.getType() == 2
                                  ? colors.icon
                                  : colors.transparent,
                            ),
                          ),
                          child: ClipRRect(
                            borderRadius: AppHelpers.getType() == 2
                                ? BorderRadius.circular(10.r)
                                : BorderRadius.only(
                                    topRight: Radius.circular(
                                      AppConstants.radius.r,
                                    ),
                                    topLeft: Radius.circular(
                                      AppConstants.radius.r,
                                    ),
                                  ),
                            child: CustomNetworkImage(
                              url: product.img ?? "",
                              height: height,
                              width: double.infinity,
                              fit: AppHelpers.getType() == 2
                                  ? BoxFit.cover
                                  : BoxFit.contain,
                              radius: 0,
                            ),
                          ),
                        ),
                        SizedBox(
                          height: height.r,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.max,
                            children: [
                              Row(
                                children: [
                                  16.horizontalSpace,
                                  if (product.stocks?.isNotEmpty ?? false)
                                    if (product.stocks?.first.discount != null)
                                      AppHelpers.getType() == 2
                                          ? Container(
                                              padding: EdgeInsets.symmetric(
                                                vertical: 4.r,
                                                horizontal: 10.r,
                                              ),
                                              decoration: BoxDecoration(
                                                color: colors.primary,
                                                borderRadius:
                                                    BorderRadius.circular(
                                                      100.r,
                                                    ),
                                              ),
                                              child: Text(
                                                AppHelpers.getTranslation(
                                                  TrKeys.hot,
                                                ).toUpperCase(),
                                                style: CustomStyle.interNoSemi(
                                                  color: colors.white,
                                                  size: 12,
                                                ),
                                              ),
                                            )
                                          : SvgPicture.asset(
                                              Assets.svgDiscount,
                                            ),
                                  const Spacer(),
                                  IconButton(
                                    splashColor: CustomStyle.transparent,
                                    highlightColor: CustomStyle.transparent,
                                    onPressed: () {
                                      LocalStorage.setLikedProductsList(
                                        product.id ?? 0,
                                      );
                                      context.read<ProductBloc>().add(
                                        const ProductEvent.updateState(),
                                      );
                                    },
                                    icon:
                                        LocalStorage.getLikedProductsList()
                                            .contains(product.id)
                                        ? Icon(
                                            FlutterRemix.heart_3_fill,
                                            color: CustomStyle.red,
                                            size: 24.r,
                                          )
                                        : Icon(
                                            FlutterRemix.heart_3_line,
                                            size: 24.r,
                                          ),
                                  ),
                                ],
                              ),
                              const Spacer(),
                              if (product.stocks?.isNotEmpty ?? false)
                                if (AppHelpers.getProductUiType())
                                  _addAndRemove(colors, context),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  Padding(
                    padding: AppHelpers.getType() == 2
                        ? EdgeInsets.zero
                        : EdgeInsets.all(16.r),
                    child: AppHelpers.getType() == 2
                        ? ProductInfoTwo(
                            product: product,
                            listExtra: listExtra,
                            colors: colors,
                            width: MediaQuery.sizeOf(context).width - 200.w,
                          )
                        : ProductInfo(
                            product: product,
                            listExtra: listExtra,
                            colors: colors,
                            width: MediaQuery.sizeOf(context).width - 200.w,
                          ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Align _addAndRemove(CustomColorSet colors, BuildContext context) {
    return Align(
      alignment: Alignment.centerRight,
      child: Padding(
        padding: EdgeInsets.all(8.r),
        child: Container(
          decoration: BoxDecoration(
            color: CustomStyle.bottomBarColorLight.withValues(alpha: 0.8),
            borderRadius: BorderRadius.circular(100.r),
          ),
          child:
              !AppHelpers.productInclude(
                productId: product.id,
                stockId: product.stocks?.first.id,
              )
              ? Padding(
                  padding: EdgeInsets.all(2.r),
                  child: ButtonEffectAnimation(
                    child: IconButton(
                      splashColor: CustomStyle.transparent,
                      highlightColor: CustomStyle.transparent,
                      onPressed: () {
                        AppHelpers.addProduct(
                          context,
                          product: product,
                          stock: product.stocks?.first,
                        );
                      },
                      icon: Icon(
                        FlutterRemix.add_line,
                        color: colors.white,
                        size: 24.r,
                      ),
                    ),
                  ),
                )
              : Padding(
                  padding: EdgeInsets.all(4.r),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      ButtonEffectAnimation(
                        child: Container(
                          decoration: BoxDecoration(
                            color: colors.backgroundColor.withValues(
                              alpha: 0.7,
                            ),
                            borderRadius: BorderRadius.circular(100.r),
                          ),
                          child: IconButton(
                            splashColor: CustomStyle.transparent,
                            highlightColor: CustomStyle.transparent,
                            onPressed: () {
                              AppHelpers.removeProduct(
                                context,
                                product: product,
                                stock: product.stocks?.first,
                              );
                            },
                            icon: Icon(
                              AppHelpers.getCountCart(
                                        productId: product.id,
                                        stockId: product.stocks?.first.id,
                                      ) ==
                                      1
                                  ? FlutterRemix.delete_bin_2_line
                                  : FlutterRemix.subtract_line,
                              color: colors.white,
                              size:
                                  AppHelpers.getCountCart(
                                        productId: product.id,
                                        stockId: product.stocks?.first.id,
                                      ) ==
                                      1
                                  ? 20.r
                                  : 24.r,
                            ),
                          ),
                        ),
                      ),
                      12.horizontalSpace,
                      Text(
                        (AppHelpers.getCountCart(
                                  productId: product.id,
                                  stockId: product.stocks?.first.id,
                                ) *
                                (product.interval ?? 1))
                            .toString(),
                        style: CustomStyle.interNormal(
                          color: CustomStyle.white,
                          size: 16,
                        ),
                      ),
                      12.horizontalSpace,
                      ButtonEffectAnimation(
                        child: Container(
                          decoration: BoxDecoration(
                            color: colors.backgroundColor.withValues(
                              alpha: 0.7,
                            ),
                            borderRadius: BorderRadius.circular(100.r),
                          ),
                          child: IconButton(
                            splashColor: CustomStyle.transparent,
                            highlightColor: CustomStyle.transparent,
                            onPressed: () {
                              AppHelpers.addProduct(
                                context,
                                product: product,
                                stock: product.stocks?.first,
                              );
                            },
                            icon: Icon(
                              FlutterRemix.add_line,
                              color: colors.white,
                              size: 24.r,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
        ),
      ),
    );
  }
}
