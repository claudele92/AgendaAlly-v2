import 'package:demand/app_constants.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:intl/intl.dart';
import 'package:map_launcher/map_launcher.dart';

class ShopLocationAndDate extends StatelessWidget {
  final CustomColorSet colors;

  const ShopLocationAndDate({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ShopBloc, ShopState>(
      buildWhen: (p, n) => p.shop != n.shop,
      builder: (context, state) {
        final shop = state.shop;
        return Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              24.verticalSpace,
              Text(
                AppHelpers.getTranslation(TrKeys.location),
                style: CustomStyle.interNoSemi(
                  color: colors.textBlack,
                  size: 22,
                ),
              ),
              20.verticalSpace,
              Row(
                children: [
                  Icon(
                    FlutterRemix.map_pin_2_fill,
                    color: colors.textBlack,
                    size: 16.r,
                  ),
                  4.horizontalSpace,
                  ButtonEffectAnimation(
                    onTap: () {
                      AppHelpers.showCustomModalBottomSheet(
                        context,
                        modal: Container(
                          padding: EdgeInsets.only(top: 32.r),
                          decoration: BoxDecoration(
                            color: colors.backgroundColor,
                            borderRadius: BorderRadius.only(
                              topRight: Radius.circular(AppConstants.radius.r),
                              topLeft: Radius.circular(AppConstants.radius.r),
                            ),
                          ),
                          child: MapsList(
                            colors: colors,
                            location: Coords(
                              shop?.location?.latitude ??
                                  AppHelpers.getInitialLatitude(),
                              shop?.location?.longitude ??
                                  AppHelpers.getInitialLongitude(),
                            ),
                            title: AppHelpers.getAppName(),
                          ),
                        ),
                      );
                    },
                    child: SizedBox(
                      width: MediaQuery.sizeOf(context).width - 60.r,
                      child: Text(
                        shop?.translation?.address ?? "",
                        style: CustomStyle.interRegular(
                          color: colors.textBlack,
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
              20.verticalSpace,
              CustomNetworkImage(
                url:
                    "https://maps.googleapis.com/maps/api/staticmap?center=${shop?.location?.latitude ?? AppHelpers.getInitialLatitude()},${shop?.location?.longitude ?? AppHelpers.getInitialLongitude()}&zoom=10&size=400x400&markers=color:black|label:${(shop?.ratingAvg ?? 0).toStringAsFixed(0)}|${shop?.location?.latitude ?? ""},${shop?.location?.longitude ?? ""}&key=${AppHelpers.getMapKey()}",
                height: 390,
                width: double.infinity,
                borderRadius: BorderRadius.circular(10.r),
              ),
              24.verticalSpace,
              if (shop?.workingDays?.isNotEmpty ?? false)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      AppHelpers.getTranslation(TrKeys.businessHours),
                      style: CustomStyle.interNoSemi(
                        color: colors.textBlack,
                        size: 22,
                      ),
                    ),
                    20.verticalSpace,
                    ...shop?.workingDays?.map((e) {
                          final isToday =
                              e.day ==
                              DateFormat.EEEE()
                                  .format(DateTime.now())
                                  .toLowerCase();
                          return Padding(
                            padding: EdgeInsets.only(bottom: 8.r),
                            child: Row(
                              children: [
                                Text(
                                  AppHelpers.getTranslation(e.day ?? ""),
                                  style: isToday
                                      ? CustomStyle.interNoSemi(
                                          color: colors.textBlack,
                                          size: 16,
                                        )
                                      : CustomStyle.interRegular(
                                          color: colors.textBlack,
                                          size: 18,
                                        ),
                                ),
                                const Spacer(),
                                if (!(e.disabled ?? false))
                                  Text(
                                    "${TimeService.timeFormatTime(e.from)} - ${TimeService.timeFormatTime(e.to)}",
                                    style: isToday
                                        ? CustomStyle.interNoSemi(
                                            color: colors.textBlack,
                                            size: 15,
                                          )
                                        : CustomStyle.interRegular(
                                            color: colors.textBlack,
                                            size: 18,
                                          ),
                                  ),
                                if ((e.disabled ?? false))
                                  Text(
                                    AppHelpers.getTranslation(
                                      TrKeys.shopIsClosed,
                                    ),
                                    style: CustomStyle.interRegular(
                                      color: colors.textBlack,
                                      size: 18,
                                    ),
                                  ),
                              ],
                            ),
                          );
                        }) ??
                        [],
                  ],
                ),
            ],
          ),
        );
      },
    );
  }
}
