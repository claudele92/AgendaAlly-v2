import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/domain/model/response/my_membership_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../app_assets.dart';

class MyMembershipsPage extends StatefulWidget {
  const MyMembershipsPage({super.key});

  @override
  State<MyMembershipsPage> createState() => _MyMembershipsPageState();
}

class _MyMembershipsPageState extends State<MyMembershipsPage> {
  late RefreshController membershipRefresh;

  @override
  void initState() {
    membershipRefresh = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    membershipRefresh.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        bottom: false,
        child: Column(
          children: [
            Text(
              AppHelpers.getTranslation(TrKeys.myMemberships),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
            24.verticalSpace,
            BlocBuilder<MembershipBloc, MembershipState>(
              builder: (context, state) {
                return Expanded(
                  child: state.isLoading
                      ? const Loading()
                      : state.myMemberships.isNotEmpty
                      ? SmartRefresher(
                          controller: membershipRefresh,
                          enablePullUp: true,
                          onRefresh: () {
                            context.read<MembershipBloc>().add(
                              MembershipEvent.myMemberships(
                                context,
                                isRefresh: true,
                                controller: membershipRefresh,
                              ),
                            );
                          },
                          onLoading: () {
                            context.read<MembershipBloc>().add(
                              MembershipEvent.myMemberships(
                                context,
                                controller: membershipRefresh,
                              ),
                            );
                          },
                          child: ListView.builder(
                            padding: EdgeInsets.symmetric(horizontal: 16.r),
                            shrinkWrap: true,
                            itemCount: state.myMemberships.length,
                            itemBuilder: (context, index) {
                              return _membershipItem(
                                context,
                                myMembership: state.myMemberships[index],
                                index: index,
                                colors: colors,
                              );
                            },
                          ),
                        )
                      : Column(
                          children: [
                            32.verticalSpace,
                            SvgPicture.asset(Assets.svgNoMembership),
                            32.verticalSpace,
                            Padding(
                              padding: EdgeInsets.symmetric(horizontal: 32.r),
                              child: Text(
                                AppHelpers.getTranslation(
                                  TrKeys.youHaveNoMembership,
                                ),
                                textAlign: TextAlign.center,
                                style: CustomStyle.interNoSemi(
                                  color: colors.textBlack,
                                  size: 30,
                                ),
                              ),
                            ),
                            16.verticalSpace,
                            Padding(
                              padding: EdgeInsets.symmetric(horizontal: 32.r),
                              child: Text(
                                AppHelpers.getTranslation(
                                  TrKeys
                                      .youDontHaveAnyMembershipMaybeYouWillGetMembershipSoon,
                                ),
                                textAlign: TextAlign.center,
                                style: CustomStyle.interRegular(
                                  color: colors.textBlack,
                                  size: 16,
                                ),
                              ),
                            ),
                          ],
                        ),
                );
              },
            ),
          ],
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.startFloat,
      floatingButton: (colors) => PopButton(colors: colors),
    );
  }

  Widget _membershipItem(
    BuildContext context, {
    required MyMembershipModel myMembership,
    required int index,
    required CustomColorSet colors,
  }) {
    return ButtonEffectAnimation(
      onTap: () {
        AppRouteShop.goMembershipBottomSheet(
          context,
          model: myMembership.memberShip?.copyWith(
            remainder: myMembership.remainder,
          ),
          colors: colors,
          enableBuy: false,
        );
      },
      child: Padding(
        padding: EdgeInsets.only(right: 8.r),
        // child: Container(
        //   height: 120.r,
        //   width: double.infinity,
        //   margin: EdgeInsets.only(bottom: 16.r),
        //   decoration: BoxDecoration(
        //       image: const DecorationImage(
        //           image: AssetImage("assets/images/gift.png"),
        //           fit: BoxFit.contain,
        //           alignment: Alignment.topLeft),
        //       color: AppHelper.checkIfHex(myMembership.color)
        //           ? Color(
        //               int.parse('0xFF${myMembership.color?.substring(1, 7)}'))
        //           : ListConstants.serviceColors[index],
        //       border: Border.all(color: colors.icon)),
        //   child: Row(
        //     crossAxisAlignment: CrossAxisAlignment.start,
        //     mainAxisAlignment: MainAxisAlignment.start,
        //     children: [
        //       Padding(
        //         padding: EdgeInsets.all(8.r),
        //         child: SizedBox(
        //           width: 64.r,
        //           child: Text(
        //             "${myMembership.remainder ?? 0}/${myMembership.sessionsCount ?? 0}",
        //             style: CustomStyle.interNormal(
        //                 color: colors.textBlack, size: 16),
        //           ),
        //         ),
        //       ),
        //       const Spacer(),
        //       Padding(
        //         padding: EdgeInsets.all(16.r),
        //         child: Column(
        //           crossAxisAlignment: CrossAxisAlignment.end,
        //           mainAxisAlignment: MainAxisAlignment.start,
        //           children: [
        //             Text(
        //               myMembership.memberShip?.translation?.title ?? "",
        //               style: CustomStyle.interNormal(color: colors.textBlack),
        //               maxLines: 1,
        //               overflow: TextOverflow.ellipsis,
        //               textAlign: TextAlign.right,
        //             ),
        //             Text(
        //               myMembership.memberShip?.shop?.translation?.title ?? "",
        //               style: CustomStyle.interRegular(
        //                   color: colors.textHint, size: 14),
        //               textAlign: TextAlign.right,
        //               maxLines: 1,
        //               overflow: TextOverflow.ellipsis,
        //             ),
        //             const Spacer(),
        //             Text(
        //               "${AppHelper.getTrn(TrKeys.expiredAt)}: ${TimeService.dateFormatMDYHm(myMembership.expiredAt)}",
        //               style: CustomStyle.interRegular(
        //                   color: colors.textBlack, size: 14),
        //               textAlign: TextAlign.right,
        //               maxLines: 1,
        //               overflow: TextOverflow.ellipsis,
        //             ),
        //           ],
        //         ),
        //       )
        //     ],
        //   ),
        // ),
        child: Container(
          width: 220.r,
          margin: REdgeInsets.only(bottom: 8),
          padding: EdgeInsets.all(12.r),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(AppConstants.radius.r),
            gradient: LinearGradient(
              colors: [
                ListConstants.serviceColors[index % 12],
                ListConstants.serviceColors[index + 1 % 12],
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            border: Border.all(color: colors.icon),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      myMembership.memberShip?.translation?.title ?? "",
                      style: CustomStyle.interNormal(
                        color: colors.textBlack,
                        size: 18,
                      ),
                    ),
                    Text(
                      "${AppHelpers.getTranslation(TrKeys.validFor)} ${myMembership.memberShip?.time ?? ""}",
                      style: CustomStyle.interRegular(
                        color: colors.textBlack,
                        size: 14,
                      ),
                      textAlign: TextAlign.right,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              SizedBox(
                width: 100.r,
                child: Text(
                  AppHelpers.numberFormat(myMembership.memberShip?.price),
                  style: CustomStyle.interNormal(color: colors.textBlack),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.right,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
