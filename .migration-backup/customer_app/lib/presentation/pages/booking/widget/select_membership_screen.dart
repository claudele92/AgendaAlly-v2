import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/svg.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/domain/model/response/my_membership_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../../app_assets.dart';

class SelectMembershipScreen extends StatefulWidget {
  final CustomColorSet colors;
  final ScrollController controller;
  final int? serviceId;
  final MasterModel? selectMaster;

  const SelectMembershipScreen({
    super.key,
    required this.colors,
    required this.controller,
    required this.serviceId,
    required this.selectMaster,
  });

  @override
  State<SelectMembershipScreen> createState() => _SelectMembershipScreenState();
}

class _SelectMembershipScreenState extends State<SelectMembershipScreen> {
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
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: Container(
        decoration: BoxDecoration(
          color: widget.colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.only(left: 16.r, right: 16.r),
        child: Column(
          children: [
            8.verticalSpace,
            Container(
              height: 4.r,
              margin: EdgeInsets.symmetric(
                horizontal: MediaQuery.sizeOf(context).width / 2 - 48.r,
              ),
              decoration: BoxDecoration(
                color: widget.colors.icon,
                borderRadius: BorderRadius.circular(100.r),
              ),
            ),
            16.verticalSpace,
            Text(
              AppHelpers.getTranslation(TrKeys.selectMembership),
              style: CustomStyle.interNormal(color: widget.colors.textBlack),
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
                          scrollController: widget.controller,
                          enablePullUp: true,
                          enablePullDown: false,
                          onLoading: () {
                            context.read<MembershipBloc>().add(
                              MembershipEvent.myMemberships(
                                context,
                                controller: membershipRefresh,
                              ),
                            );
                          },
                          child: ListView.builder(
                            controller: widget.controller,
                            padding: EdgeInsets.zero,
                            shrinkWrap: true,
                            itemCount: state.myMemberships.length,
                            itemBuilder: (context, index) {
                              return _membershipItem(
                                context,
                                myMembership: state.myMemberships[index],
                                index: index,
                                colors: widget.colors,
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
                                  color: widget.colors.textBlack,
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
                                  color: widget.colors.textBlack,
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
        if ((myMembership.remainder ?? 0) == 0) return;
        Navigator.pop(context);
        context.read<BookingBloc>()
          ..add(
            BookingEvent.selectMaster(
              serviceId: widget.serviceId,
              master: widget.selectMaster?.copyWith(membership: myMembership),
            ),
          )
          ..add(BookingEvent.calculateBooking(context));
      },
      child: Container(
        height: 120.r,
        width: double.infinity,
        margin: EdgeInsets.only(bottom: 16.r),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
          image: const DecorationImage(
            image: AssetImage(Assets.imagesGift),
            fit: BoxFit.contain,
            alignment: Alignment.topLeft,
          ),
          color: AppHelpers.checkIfHex(myMembership.color)
              ? Color(int.parse('0xFF${myMembership.color?.substring(1, 7)}'))
              : ListConstants.serviceColors[index],
          border: Border.all(color: colors.icon),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.start,
          children: [
            Padding(
              padding: EdgeInsets.all(8.r),
              child: Text(
                "${myMembership.remainder ?? 0}/${myMembership.sessionsCount ?? 0}",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 16,
                ),
              ),
            ),
            24.horizontalSpace,
            Expanded(
              child: Padding(
                padding: EdgeInsets.all(16.r),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Text(
                      myMembership.memberShip?.translation?.title ?? "",
                      style: CustomStyle.interNormal(color: colors.textBlack),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      textAlign: TextAlign.right,
                    ),
                    Text(
                      myMembership.memberShip?.shop?.translation?.title ?? "",
                      style: CustomStyle.interRegular(
                        color: colors.textHint,
                        size: 14,
                      ),
                      textAlign: TextAlign.right,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    Text(
                      "${AppHelpers.getTranslation(TrKeys.expiredAt)}: ${TimeService.dateFormatMDYHm(myMembership.expiredAt)}",
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
            ),
          ],
        ),
      ),
    );
  }
}
