import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class MasterAvatar extends StatelessWidget {
  final CustomColorSet colors;

  const MasterAvatar({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<MasterBloc, MasterState>(
      builder: (context, state) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CustomNetworkImage(
              url: state.master?.img ?? "",
              height: 220.r + MediaQuery.paddingOf(context).top,
              width: double.infinity,
              radius: 0,
            ),
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r, vertical: 20.r),
              child: Row(
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "${state.master?.firstname ?? ""} ${state.master?.lastname ?? ""}",
                        style: CustomStyle.interNoSemi(
                          color: colors.textBlack,
                          size: 20,
                        ),
                      ),
                      6.verticalSpace,
                      Row(
                        children: [
                          Icon(
                            FlutterRemix.map_pin_2_fill,
                            color: colors.textBlack,
                            size: 18.r,
                          ),
                          7.horizontalSpace,
                          ButtonEffectAnimation(
                            onTap: () {
                              AppRouteShop.goShopPage(
                                context,
                                shop: state.master?.invite?.shop,
                              );
                            },
                            child: SizedBox(
                              width: MediaQuery.sizeOf(context).width - 190.r,
                              child: Text(
                                state
                                        .master
                                        ?.invite
                                        ?.shop
                                        ?.translation
                                        ?.title ??
                                    "",
                                style: CustomStyle.interNoSemi(
                                  color: colors.textHint,
                                  size: 14,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const Spacer(),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        AppHelpers.getTranslation(TrKeys.startFrom),
                        style: CustomStyle.interRegular(
                          color: colors.textBlack,
                          size: 14,
                        ),
                      ),
                      6.verticalSpace,
                      Text(
                        AppHelpers.numberFormat(state.master?.serviceMinPrice),
                        style: CustomStyle.interSemi(
                          color: colors.textBlack,
                          size: 20,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            10.verticalSpace,
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: CustomButton(
                title: AppHelpers.getTranslation(TrKeys.bookNow),
                bgColor: colors.textBlack,
                titleColor: colors.textWhite,
                onTap: () {
                  AppRouteService.goServiceListPage(
                    context,
                    master: state.master,
                  );
                },
              ),
            ),
            30.verticalSpace,
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: const Divider(),
            ),
          ],
        );
      },
    );
  }
}
