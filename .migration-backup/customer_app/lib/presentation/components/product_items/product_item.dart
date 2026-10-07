import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:provider/provider.dart';

import '../../../application/products/product_bloc.dart';
import '../../../domain/model/models.dart';
import '../../../infrastructure/local_storage/local_storage.dart';
import '../../../infrastructure/service/services.dart';
import '../../app_assets.dart';
import '../../route/app_route.dart';
import '../../style/style.dart';
import '../../style/theme/theme.dart';
import '../../style/theme/theme_wrapper.dart';
import '../button/animation_button_effect.dart';
import '../product_items/product_info.dart';
import '../product_items/product_info_two.dart';
import '../custom_network_image.dart';

class ProductItem extends StatelessWidget {
  final ProductData product;
  final double height;
  final double width;
  final VoidCallback? onLike;

  ProductItem({
    super.key,
    required this.product,
    this.height = 250,
    this.width = 210,
    this.onLike,
  });

  final List<Extras> listExtra = [];

  void setColorList() {
    if (AppHelpers.getType() == 1) {
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
  }

  @override
  Widget build(BuildContext context) {
    setColorList();
    return ThemeWrapper(
      builder: (colors, controller) {
        return InkWell(
          borderRadius: BorderRadius.circular(8.r),
          onTap: () async {
            await AppRoute.goProductPage(context, product: product);
            if (context.mounted) {
              context.read<ProductBloc>().add(const ProductEvent.updateState());
            }
          },
          child: SizedBox(
            width: width.r,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(8.r),
                    border: Border.all(color: CustomStyle.icon),
                    color: CustomStyle.shimmerBase,
                  ),
                  child: Stack(
                    children: [
                      CustomNetworkImage(
                        url: product.galleries?.isNotEmpty ?? false
                            ? '${product.galleries?.first.path}'
                            : '${product.img}',
                        preview: product.galleries?.isNotEmpty ?? false
                            ? product.galleries?.first.preview
                            : null,
                        height: height,
                        width: width,
                        radius: 8,
                      ),
                      SizedBox(
                        height: height.r,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.max,
                          children: [
                            if (product.stocks?.isNotEmpty ?? false)
                              Row(
                                children: [
                                  16.horizontalSpace,
                                  if (product.stocks?.first.discount != null)
                                    AppHelpers.getType() == 2
                                        ? Container(
                                            padding: REdgeInsets.symmetric(
                                              vertical: 4,
                                              horizontal: 10,
                                            ),
                                            decoration: BoxDecoration(
                                              color: colors.primary,
                                              borderRadius:
                                                  BorderRadius.circular(100.r),
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
                                        : SvgPicture.asset(Assets.svgDiscount),
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
                                      onLike?.call();
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
                Expanded(
                  child: AppHelpers.getType() == 2
                      ? ProductInfoTwo(
                          product: product,
                          colors: colors,
                          listExtra: listExtra,
                        )
                      : ProductInfo(
                          width: width,
                          product: product,
                          colors: colors,
                          listExtra: listExtra,
                        ),
                ),
              ],
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
            borderRadius: BorderRadius.circular(10.r),
          ),
          child:
              !AppHelpers.productInclude(
                productId: product.id,
                stockId: product.stocks?.first.id,
              )
              ? ButtonEffectAnimation(
                  onTap: () {
                    AppHelpers.addProduct(
                      context,
                      product: product,
                      stock: product.stocks?.first,
                    );
                    onLike?.call();
                  },
                  child: Container(
                    decoration: BoxDecoration(
                      color: colors.transparent,
                      borderRadius: BorderRadius.circular(8.r),
                    ),
                    padding: EdgeInsets.symmetric(
                      vertical: 8.r,
                      horizontal: 10.r,
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          FlutterRemix.add_line,
                          color: colors.white,
                          size: 24.r,
                        ),
                        8.horizontalSpace,
                        Text(
                          AppHelpers.numberFormat(
                            product.stocks?.first.totalPrice,
                          ),
                          style: CustomStyle.interNormal(
                            color: colors.white,
                            size: 14,
                          ),
                        ),
                      ],
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
                            color: CustomStyle.socialButtonLight,
                            borderRadius: BorderRadius.circular(10.r),
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
                              onLike?.call();
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
                            color: CustomStyle.socialButtonLight,
                            borderRadius: BorderRadius.circular(10.r),
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
                              onLike?.call();
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
