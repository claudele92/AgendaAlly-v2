import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/domain/model/response/membership_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class MembershipBottomSheet extends StatelessWidget {
  final CustomColorSet colors;
  final MembershipModel? membership;
  final ScrollController controller;
  final bool enableBuy;

  const MembershipBottomSheet({
    super.key,
    required this.colors,
    required this.controller,
    required this.membership,
    this.enableBuy = true,
  });

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: Container(
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.only(left: 16.r, right: 16.r),
        child: BlocBuilder<MembershipBloc, MembershipState>(
          buildWhen: (p, n) => p.membership != n.membership,
          builder: (context, state) {
            return ListView(
              controller: controller,
              padding: EdgeInsets.only(top: 8.r, bottom: 16.r),
              children: [
                Container(
                  height: 4.r,
                  margin: EdgeInsets.symmetric(
                    horizontal: MediaQuery.sizeOf(context).width / 2 - 48.r,
                  ),
                  decoration: BoxDecoration(
                    color: colors.icon,
                    borderRadius: BorderRadius.circular(100.r),
                  ),
                ),
                28.verticalSpace,
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        membership?.translation?.title ?? "",
                        style: CustomStyle.interNoSemi(
                          color: colors.textBlack,
                          size: 20,
                        ),
                      ),
                    ),
                    Text(
                      AppHelpers.numberFormat(membership?.price),
                      style: CustomStyle.interNoSemi(
                        color: colors.textBlack,
                        size: 18,
                      ),
                    ),
                  ],
                ),
                24.verticalSpace,
                Text(
                  membership?.translation?.description ?? "",
                  style: CustomStyle.interRegular(
                    color: colors.textBlack,
                    size: 16,
                  ),
                ),
                26.verticalSpace,
                Wrap(
                  spacing: 8.r,
                  runSpacing: 8.r,
                  children: [
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(50.r),
                        border: Border.all(color: colors.textHint),
                      ),
                      padding: EdgeInsets.symmetric(
                        horizontal: 16.r,
                        vertical: 8.r,
                      ),
                      child: Text(
                        AppHelpers.getTranslation(membership?.time ?? ""),
                        style: CustomStyle.interNormal(
                          color: colors.textHint,
                          size: 12,
                        ),
                      ),
                    ),
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(50.r),
                        border: Border.all(color: colors.textHint),
                      ),
                      padding: EdgeInsets.symmetric(
                        horizontal: 16.r,
                        vertical: 8.r,
                      ),
                      child: Text(
                        membership?.sessions == 2
                            ? AppHelpers.getTranslation(TrKeys.unlimited)
                            : enableBuy
                            ? "${membership?.sessionsCount ?? 0} ${AppHelpers.getTranslation(TrKeys.count)}"
                            : "${membership?.remainder ?? 0}/${membership?.sessionsCount ?? 0} ${AppHelpers.getTranslation(TrKeys.count)}",
                        style: CustomStyle.interNormal(
                          color: colors.textHint,
                          size: 12,
                        ),
                      ),
                    ),
                    ...?(state.membership ?? membership)?.services?.map(
                      (e) => Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(50.r),
                          border: Border.all(color: colors.textHint),
                        ),
                        padding: EdgeInsets.symmetric(
                          horizontal: 16.r,
                          vertical: 8.r,
                        ),
                        child: Text(
                          e.service?.translation?.title ?? '',
                          style: CustomStyle.interNormal(
                            color: colors.textHint,
                            size: 12,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                24.verticalSpace,
                Text(
                  membership?.translation?.term ?? "",
                  style: CustomStyle.interRegular(
                    color: colors.textBlack,
                    size: 16,
                  ),
                ),
                24.verticalSpace,
                if (enableBuy)
                  CustomButton(
                    title: AppHelpers.getTranslation(TrKeys.buyNow),
                    bgColor: colors.primary,
                    titleColor: colors.white,
                    onTap: () {
                      AppRouteShop.goMembershipPaymentBottomSheet(
                        context,
                        model: membership,
                        colors: colors,
                      );
                    },
                  ),
              ],
            );
          },
        ),
      ),
    );
  }
}
