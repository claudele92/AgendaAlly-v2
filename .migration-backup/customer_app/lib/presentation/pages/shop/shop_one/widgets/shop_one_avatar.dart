import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/svg.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../../../app_assets.dart';

class ShopOneAvatar extends StatelessWidget {
  final CustomColorSet colors;

  const ShopOneAvatar({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ShopBloc, ShopState>(
      builder: (context, state) {
        return SizedBox(
          height: 250.r,
          width: MediaQuery.sizeOf(context).width,
          child: Stack(
            children: [
              CustomNetworkImage(
                url: state.shop?.backgroundImg ?? state.shop?.logoImg ?? "",
                height: 156.r + MediaQuery.paddingOf(context).top,
                width: double.infinity,
                radius: 0,
              ),
              SafeArea(
                child: Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16.r),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Spacer(),
                      Text(
                        state.shop?.translation?.title ?? "",
                        style: CustomStyle.interSemi(
                          color: colors.white,
                          size: 24,
                        ),
                      ),
                      16.verticalSpace,
                      Row(
                        children: [
                          Container(
                            padding: EdgeInsets.symmetric(
                              horizontal: 16.r,
                              vertical: 12.r,
                            ),
                            decoration: BoxDecoration(
                              border: Border.all(color: colors.icon),
                              borderRadius: BorderRadius.circular(100.r),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  TimeService.shopTime(state.shop?.workingDays),
                                  style: CustomStyle.interNormal(
                                    color: colors.textBlack,
                                    size: 16,
                                  ),
                                ),
                                24.horizontalSpace,
                                SvgPicture.asset(Assets.svgStart, width: 16.r),
                                4.horizontalSpace,
                                Text(
                                  state.shop?.ratingAvg?.toStringAsPrecision(
                                        2,
                                      ) ??
                                      "0.0",
                                  style: CustomStyle.interNoSemi(
                                    color: colors.textBlack,
                                    size: 12,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const Spacer(),
                          SecondButton(
                            title: AppHelpers.getTranslation(TrKeys.seeReview),
                            bgColor: colors.primary,
                            titleColor: colors.white,
                            onTap: () {
                              AppRoute.goReviewPage(
                                context,
                                colors: colors,
                                shopId: state.shop?.id,
                              );
                            },
                            radius: 100.r,
                            titleSize: 16,
                          ),
                        ],
                      ),
                      10.verticalSpace,
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class ShopOneName extends StatelessWidget {
  final CustomColorSet colors;

  const ShopOneName({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ShopBloc, ShopState>(
      builder: (context, state) {
        return Stack(
          children: [
            Positioned(
              left: 14.r,
              right: 14.r,
              child: SizedBox(
                width: double.infinity,
                child: Center(
                  child: Text(
                    state.shop?.translation?.title ?? "",
                    style: CustomStyle.interSemi(
                      color: colors.textBlack,
                      size: 21,
                    ),
                  ),
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}
