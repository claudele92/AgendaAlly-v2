import 'package:auto_size_text/auto_size_text.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../app_assets.dart';

class MasterItem extends StatelessWidget {
  final MasterModel master;
  final CustomColorSet colors;
  final double height;
  final bool selectService;
  final bool like;
  final VoidCallback? onTap;

  const MasterItem({
    super.key,
    required this.master,
    required this.colors,
    this.height = 180,
    this.selectService = false,
    this.like = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        if (onTap != null) {
          onTap?.call();
          return;
        }
        AppRouteService.goMasterPage(context, master: master);
      },
      child: Container(
        width: 164.r,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(10.r),
          border: Border.all(color: colors.icon),
          color: colors.backgroundColor,
        ),
        child: Stack(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CustomNetworkImage(
                  url: master.img ?? "",
                  height: height,
                  width: 180,
                  borderRadius: BorderRadius.only(
                    topRight: Radius.circular(10.r),
                    topLeft: Radius.circular(10.r),
                  ),
                ),
                Padding(
                  padding: EdgeInsets.symmetric(
                    horizontal: 12.r,
                    vertical: 16.r,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "${master.firstname ?? ""} ${master.lastname ?? ""}",
                        style: CustomStyle.interNormal(
                          color: colors.textBlack,
                          size: 15,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      10.verticalSpace,
                      Row(
                        children: [
                          Icon(
                            FlutterRemix.map_pin_2_fill,
                            color: colors.textHint,
                            size: 14.r,
                          ),
                          4.horizontalSpace,
                          ButtonEffectAnimation(
                            onTap: () {
                              if (!selectService) {
                                AppRouteShop.goShopPage(
                                  context,
                                  shop: master.invite?.shop,
                                );
                              }
                            },
                            child: SizedBox(
                              width: like
                                  ? 110.r
                                  : selectService
                                  ? 110.r
                                  : 120.r,
                              child: Text(
                                master.invite?.shop?.translation?.title ?? "",
                                style: CustomStyle.interRegular(
                                  color: colors.textHint,
                                  size: 14,
                                  textDecoration: TextDecoration.underline,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                        ],
                      ),
                      8.verticalSpace,
                      const Divider(height: 1),
                      8.verticalSpace,
                      Row(
                        children: [
                          Text(
                            AppHelpers.getTranslation(TrKeys.startFrom),
                            style: CustomStyle.interRegular(
                              color: colors.textBlack,
                              size: 14,
                            ),
                          ),
                          2.horizontalSpace,
                          Expanded(
                            child: AutoSizeText(
                              AppHelpers.numberFormat(
                                selectService
                                    ? master.serviceMaster?.price
                                    : master.serviceMinPrice,
                              ),
                              style: CustomStyle.interSemi(
                                color: colors.textBlack,
                                size: 16,
                              ),
                              maxLines: 1,
                              textAlign: TextAlign.right,
                            ),
                          ),
                        ],
                      ),
                      if (selectService)
                        Padding(
                          padding: EdgeInsets.only(top: 8.r),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                AppHelpers.getTranslation(TrKeys.type),
                                style: CustomStyle.interRegular(
                                  color: colors.textBlack,
                                  size: 14,
                                ),
                              ),
                              Text(
                                AppHelpers.getTranslation(
                                  master.serviceMaster?.type ?? "",
                                ),
                                style: CustomStyle.interNormal(
                                  color: colors.textBlack,
                                  size: 16,
                                ),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
            Positioned(
              top: 12.r,
              left: 12.r,
              child: ButtonEffectAnimation(
                onTap: () {
                  LocalStorage.setLikedMaster(master.id ?? 0);
                  context.read<MasterBloc>().add(
                    const MasterEvent.updateState(),
                  );
                },
                child: SvgPicture.asset(
                  LocalStorage.getLikedMaster().contains(master.id)
                      ? Assets.svgLikeButtom
                      : Assets.svgUnlike,
                  width: 26.r,
                  height: 26.r,
                ),
              ),
            ),
            Positioned(
              right: 12.r,
              top: (height - 12).r,
              child: Container(
                height: 30.r,
                width: 30.r,
                decoration: BoxDecoration(
                  color: colors.textWhite.withValues(alpha: 0.9),
                  border: Border.all(color: colors.icon),
                  borderRadius: BorderRadius.circular(10.r),
                ),
                child: Center(
                  child: Text(
                    master.rate?.toStringAsFixed(1) ?? "0",
                    style: CustomStyle.interNoSemi(
                      color: colors.textBlack,
                      size: 12,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
