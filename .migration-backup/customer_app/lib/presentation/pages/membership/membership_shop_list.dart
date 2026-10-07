import 'package:auto_size_text/auto_size_text.dart';
import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/domain/model/response/membership_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class MembershipShopList extends StatefulWidget {
  final CustomColorSet colors;
  final int shopId;

  const MembershipShopList({
    super.key,
    required this.colors,
    required this.shopId,
  });

  @override
  State<MembershipShopList> createState() => _MembershipShopListState();
}

class _MembershipShopListState extends State<MembershipShopList> {
  late RefreshController controller;

  @override
  void initState() {
    controller = RefreshController();
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<MembershipBloc, MembershipState>(
      builder: (context, state) {
        return state.memberships.isEmpty
            ? const SizedBox.shrink()
            : state.isLoading
            ? const Loading()
            : Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  24.verticalSpace,
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.memberships),
                    titleColor: widget.colors.textBlack,
                  ),
                  16.verticalSpace,
                  SizedBox(
                    height: 100.r,
                    child: SmartRefresher(
                      controller: controller,
                      scrollDirection: Axis.horizontal,
                      enablePullDown: false,
                      enablePullUp: true,
                      onLoading: () {
                        context.read<MembershipBloc>().add(
                          MembershipEvent.fetchMembership(
                            context,
                            shopId: widget.shopId,
                            controller: controller,
                          ),
                        );
                      },
                      child: ListView.builder(
                        padding: EdgeInsets.symmetric(horizontal: 16.r),
                        shrinkWrap: true,
                        scrollDirection: Axis.horizontal,
                        itemCount: state.memberships.length,
                        itemBuilder: (context, index) {
                          return _membershipItem(
                            context,
                            membership: state.memberships[index],
                            index: index,
                          );
                        },
                      ),
                    ),
                  ),
                ],
              );
      },
    );
  }

  Widget _membershipItem(
    BuildContext context, {
    required MembershipModel membership,
    required int index,
  }) {
    return ButtonEffectAnimation(
      onTap: () {
        AppRouteShop.goMembershipBottomSheet(
          context,
          model: membership,
          colors: widget.colors,
        );
      },
      child: Padding(
        padding: EdgeInsets.only(right: 8.r),
        child: Container(
          width: 220.r,
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
            border: Border.all(color: widget.colors.icon),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              SizedBox(
                width: 100.r,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    AutoSizeText(
                      membership.translation?.title ?? "",
                      style: CustomStyle.interNormal(
                        color: widget.colors.textBlack,
                        size: 18,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    Text(
                      "${AppHelpers.getTranslation(TrKeys.validFor)} ${membership.time ?? ""}",
                      style: CustomStyle.interRegular(
                        color: widget.colors.textBlack,
                        size: 14,
                      ),
                      textAlign: TextAlign.right,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const Spacer(),
              SizedBox(
                width: 64.r,
                child: Text(
                  AppHelpers.numberFormat(membership.price),
                  style: CustomStyle.interNormal(
                    color: widget.colors.textBlack,
                  ),
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
