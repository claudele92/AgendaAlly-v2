import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../app_assets.dart';

class ShopItem extends StatelessWidget {
  final CustomColorSet colors;
  final ShopData shop;
  final bool blurUi;

  const ShopItem({
    super.key,
    required this.colors,
    required this.shop,
    this.blurUi = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () async {
        await AppRouteShop.goShopPage(context, shop: shop);
        if (context.mounted) {
          context.read<ShopBloc>().add(const ShopEvent.updateState());
        }
      },
      child: blurUi
          ? Container(
              width: 252.r,
              decoration: BoxDecoration(
                border: Border.all(color: colors.icon),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(10.r),
                  topRight: Radius.circular(10.r),
                ),
              ),
              child: Stack(
                children: [
                  CustomNetworkImage(
                    url: shop.backgroundImg ?? "",
                    height: double.infinity,
                    width: 294,
                    borderRadius: BorderRadius.only(
                      topLeft: Radius.circular(10.r),
                      topRight: Radius.circular(10.r),
                    ),
                  ),
                  _titleTwo(),
                ],
              ),
            )
          : _simpleShop(context),
    );
  }

  Container _simpleShop(BuildContext context) {
    return Container(
      width: 280.r,
      decoration: BoxDecoration(
        color: colors.backgroundColor,
        border: Border.all(color: colors.icon),
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(10.r),
          topRight: Radius.circular(10.r),
        ),
        boxShadow: [
          BoxShadow(
            color: colors.textBlack.withValues(alpha: 0.08),
            blurRadius: 12,
            offset: const Offset(0, 4),
            spreadRadius: 0,
          ),
          BoxShadow(
            color: colors.textBlack.withValues(alpha: 0.04),
            blurRadius: 4,
            offset: const Offset(0, 2),
            spreadRadius: -2,
          ),
        ],
      ),
      child: Stack(
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CustomNetworkImage(
                url: shop.backgroundImg ?? "",
                height: 170,
                width: double.infinity,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(10.r),
                  topRight: Radius.circular(10.r),
                ),
              ),
              24.verticalSpace,
              _title(),
            ],
          ),
          _likeAndReview(context),
          Positioned(
            top: 148.r,
            left: 16.r,
            child: Container(
              margin: REdgeInsets.only(left: 6),
              decoration: BoxDecoration(
                border: Border.all(color: colors.textWhite, width: 2.r),
                color: colors.textWhite.withValues(alpha: 0.8),
                shape: BoxShape.circle,
              ),
              child: CustomNetworkImage(
                url: shop.logoImg ?? '',
                height: 40,
                width: 40,
                radius: 20,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _likeAndReview(BuildContext context) {
    return Padding(
      padding: EdgeInsets.all(12.r),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              ButtonEffectAnimation(
                onTap: () {
                  LocalStorage.setLikedShopsList(shop.id ?? 0);
                  context.read<ShopBloc>().add(const ShopEvent.updateState());
                },
                child: SvgPicture.asset(
                  LocalStorage.getLikedShopsList().contains(shop.id)
                      ? Assets.svgLikeButtom
                      : Assets.svgUnlike,
                  width: 26.r,
                  height: 26.r,
                ),
              ),
              const Spacer(),
              Container(
                width: 30.r,
                height: 30.r,
                decoration: BoxDecoration(
                  border: Border.all(color: colors.textWhite),
                  color: colors.backgroundColor.withValues(alpha: 0.8),
                  borderRadius: BorderRadius.circular(10.r),
                ),
                child: Center(
                  child: Text(
                    (shop.ratingAvg ?? 0).toStringAsFixed(1),
                    style: CustomStyle.interNoSemi(
                      color: colors.textBlack,
                      size: 12,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _title() {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16.r),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Flexible(
                child: Text(
                  shop.translation?.title ?? "",
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 16,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (shop.verify ?? false) const BadgeItem(),
            ],
          ),
          6.verticalSpace,
          Text(
            shop.translation?.description ?? "",
            style: CustomStyle.interRegular(color: colors.textHint, size: 12),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          10.verticalSpace,
          Row(
            children: [
              Icon(
                FlutterRemix.map_pin_2_fill,
                color: colors.textBlack,
                size: 16.r,
              ),
              4.horizontalSpace,
              Expanded(
                child: Text(
                  shop.distance == null || shop.distance == 0
                      ? (shop.translation?.address ?? "")
                      : ("${AppHelpers.getTranslation(TrKeys.from)} ${shop.distance!.toStringAsFixed(1)} ${AppHelpers.getTranslation(TrKeys.km)}"),
                  style: CustomStyle.interRegular(
                    color: colors.textHint,
                    size: 12,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          12.verticalSpace,
          const Divider(),
          Row(
            children: [
              Text(
                AppHelpers.reviewText(shop.ratingAvg),
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 12,
                ),
              ),
              8.horizontalSpace,
              Container(
                height: 4.r,
                width: 4.r,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: colors.textBlack,
                ),
              ),
              8.horizontalSpace,
              Text(
                "${(shop.ratingCount ?? 0.0).toStringAsFixed(0)} ${AppHelpers.getTranslation(TrKeys.reviews)}",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 12,
                ),
              ),
            ],
          ),
          8.verticalSpace,
        ],
      ),
    );
  }

  Widget _titleTwo() {
    return Align(
      alignment: Alignment.bottomCenter,
      child: Container(
        height: 72.r,
        margin: EdgeInsets.all(8.r),
        padding: EdgeInsets.all(12.r),
        decoration: BoxDecoration(
          color: colors.textWhite.withValues(alpha: 0.9),
          border: Border.all(color: colors.textWhite),
          borderRadius: BorderRadius.circular(10.r),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Flexible(
                        child: Text(
                          shop.translation?.title ?? "",
                          style: CustomStyle.interNoSemi(
                            color: colors.textBlack,
                            size: 14,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (shop.verify ?? false) const BadgeItem(),
                    ],
                  ),
                  6.verticalSpace,
                  Expanded(
                    child: Text(
                      shop.translation?.description ?? "",
                      style: CustomStyle.interRegular(
                        color: colors.textHint,
                        size: 14,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            8.horizontalSpace,
            CustomNetworkImage(
              url: shop.logoImg ?? "",
              height: 46,
              width: 46,
              radius: 23,
            ),
          ],
        ),
      ),
    );
  }
}
