import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/product_detail/product_detail_bloc.dart';
import 'package:demand/domain/model/model/product_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/custom_read_more.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class ProductTitleOne extends StatelessWidget {
  final ProductData? product;
  final Stocks? selectStock;
  final CustomColorSet colors;

  const ProductTitleOne({
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
          InkWell(
            onTap: () async {
              if (product?.shop != null) {
                await AppRouteShop.goShopPage(context, shop: product?.shop);
                if (context.mounted) {
                  context.read<ProductDetailBloc>().add(
                    const ProductDetailEvent.updateState(),
                  );
                }
              }
            },
            child: Row(
              children: [
                Icon(FlutterRemix.store_2_fill, color: colors.textBlack),
                10.horizontalSpace,
                Text(
                  product?.shop?.translation?.title ??
                      AppHelpers.getTranslation(TrKeys.unKnow),
                  style: CustomStyle.interNormal(
                    textDecoration: TextDecoration.underline,
                    color: colors.textBlack,
                    size: 14,
                  ),
                ),
              ],
            ),
          ),
          16.verticalSpace,
          SizedBox(
            width: MediaQuery.sizeOf(context).width,
            child: Text(
              product?.translation?.title ?? "",
              style: CustomStyle.interSemi(color: colors.textBlack, size: 22),
              overflow: TextOverflow.ellipsis,
            ),
          ),
          12.verticalSpace,
          Row(
            children: [
              Text(
                AppHelpers.numberFormat(selectStock?.totalPrice ?? 0),
                style: CustomStyle.interSemi(color: colors.textBlack, size: 22),
              ),
              if (selectStock?.discount != null)
                Padding(
                  padding: EdgeInsets.only(left: 16.r),
                  child: Text(
                    AppHelpers.numberFormat(selectStock?.price ?? 0),
                    style: CustomStyle.interNoSemi(
                      color: CustomStyle.red,
                      size: 14,
                      decoration: TextDecoration.lineThrough,
                    ),
                  ),
                ),
            ],
          ),
          16.verticalSpace,
          Row(
            children: [
              RatingBar.builder(
                unratedColor: colors.textHint,
                initialRating: double.parse(
                  product?.ratingAvg?.toStringAsPrecision(2) ?? "0.0",
                ),
                direction: Axis.horizontal,
                allowHalfRating: true,
                ignoreGestures: true,
                itemCount: 5,
                itemSize: 24.r,
                glowColor: CustomStyle.starColor,
                itemPadding: EdgeInsets.symmetric(horizontal: 4.r),
                onRatingUpdate: (rating) {},
                itemBuilder: (BuildContext context, int index) {
                  return const Icon(
                    FlutterRemix.star_smile_fill,
                    color: CustomStyle.starColor,
                  );
                },
              ),
              6.horizontalSpace,
              Text(
                product?.ratingAvg?.toStringAsPrecision(2) ?? "0.0",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 14,
                ),
              ),
              6.horizontalSpace,
              InkWell(
                onTap: () {
                  AppRoute.goReviewPage(
                    context,
                    colors: colors,
                    productId: product?.id,
                    productUuid: product?.uuid,
                  );
                },
                child: Text(
                  "${product?.ratingCount ?? 0} ${AppHelpers.getTranslation(TrKeys.reviews)}",
                  style: CustomStyle.interNormal(
                    color: colors.textBlack,
                    size: 14,
                    textDecoration: TextDecoration.underline,
                  ),
                ),
              ),
            ],
          ),
          12.verticalSpace,
          Row(
            children: [
              Text(
                "${product?.ordersCount ?? 0} ${AppHelpers.getTranslation(TrKeys.peopleBuyThisItem)}",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 14,
                ),
              ),
              const Spacer(),
              Text(
                "${selectStock?.quantity ?? 0} ${AppHelpers.getTranslation(TrKeys.inStock)}",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 14,
                ),
              ),
            ],
          ),
          12.verticalSpace,
          if (product?.translation?.description != null)
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                16.verticalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.description),
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 16,
                  ),
                ),
                10.verticalSpace,
                CustomReadMoreText(
                  product?.translation?.description ?? "",
                  colors: colors,
                ),
              ],
            ),
        ],
      ),
    );
  }
}
