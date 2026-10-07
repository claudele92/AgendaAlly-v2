// ignore_for_file: use_build_context_synchronously

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:share_plus/share_plus.dart';

class ReferralPage extends StatelessWidget {
  const ReferralPage({super.key});

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        child: Column(
          children: [
            Text(
              AppHelpers.getTranslation(TrKeys.referral),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
            24.verticalSpace,
            BlocBuilder<ProfileBloc, ProfileState>(
              builder: (context, state) {
                return state.isReferralLoading
                    ? const Loading()
                    : Padding(
                        padding: EdgeInsets.all(16.r),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _info(state, colors, context),
                            CustomButton(
                              title: AppHelpers.getTranslation(TrKeys.share),
                              bgColor: colors.primary,
                              titleColor: colors.textWhite,
                              onTap: () {
                                SharePlus.instance.share(
                                  ShareParams(
                                    text: LocalStorage.getUser().referral ?? "",
                                    sharePositionOrigin: Rect.fromLTWH(
                                      0,
                                      0,
                                      100,
                                      100,
                                    ),
                                  ),
                                );
                              },
                            ),
                            16.verticalSpace,
                            CustomButton(
                              title: AppHelpers.getTranslation(TrKeys.copyCode),
                              bgColor: colors.textBlack,
                              titleColor: colors.textWhite,
                              onTap: () async {
                                await Clipboard.setData(
                                  ClipboardData(
                                    text: LocalStorage.getUser().referral ?? "",
                                  ),
                                );
                                AppHelpers.errorSnackBar(
                                  context,
                                  message: AppHelpers.getTranslation(
                                    TrKeys.copyCode,
                                  ),
                                );
                              },
                            ),
                            16.verticalSpace,
                            _balance(colors),
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

  Widget _balance(CustomColorSet colors) {
    return Container(
      height: 74.r,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10.r),
        border: Border.all(color: colors.textBlack),
      ),
      alignment: Alignment.center,
      child: Padding(
        padding: REdgeInsets.symmetric(horizontal: 24),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(FlutterRemix.coins_fill, size: 45.r, color: colors.textBlack),
            10.horizontalSpace,
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  AppHelpers.getTranslation(TrKeys.balance),
                  style: CustomStyle.interNormal(
                    size: 14,
                    color: colors.textBlack,
                    letterSpacing: -0.3,
                  ),
                ),
                Text(
                  AppHelpers.numberFormat(
                    (LocalStorage.getUser().referralFromPrice ?? 0) -
                        (LocalStorage.getUser().referralFromWithdrawPrice ?? 0),
                  ),
                  style: CustomStyle.interSemi(
                    size: 18,
                    color: colors.textBlack,
                    letterSpacing: -0.3,
                  ),
                ),
              ],
            ),
            const Spacer(),
            Container(width: 1.r, height: 46.r, color: colors.textBlack),
            const Spacer(),
            Text(
              ((LocalStorage.getUser().referralFromPrice ?? 0) -
                      (LocalStorage.getUser().referralFromWithdrawPrice ?? 0))
                  .toString(),
              style: CustomStyle.interSemi(
                size: 18,
                color: colors.textBlack,
                letterSpacing: -0.3,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _info(
    ProfileState state,
    CustomColorSet colors,
    BuildContext context,
  ) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          height: 200.h,
          width: double.infinity,
          child: CustomNetworkImage(
            url: state.referralData?.img ?? "",
            height: 42.r,
            width: 42.r,
            radius: 8.r,
          ),
        ),
        16.verticalSpace,
        Text(
          state.referralData?.translation?.title ?? "",
          style: CustomStyle.interNoSemi(size: 20, color: colors.textBlack),
        ),
        16.verticalSpace,
        GestureDetector(
          onTap: () {
            AppHelpers.openDialog(
              context,
              title: state.referralData?.translation?.shortDesc ?? "",
            );
          },
          child: RichText(
            text: TextSpan(
              text: "${state.referralData?.translation?.description} ",
              style: CustomStyle.interNoSemi(size: 14, color: colors.textBlack),
              children: [
                TextSpan(
                  text: AppHelpers.getTranslation(
                    TrKeys.referralFaq,
                  ).toLowerCase(),
                  style: CustomStyle.interNoSemi(
                    size: 14,
                    color: colors.textBlack,
                    decoration: TextDecoration.underline,
                  ),
                ),
              ],
            ),
          ),
        ),
        16.verticalSpace,
      ],
    );
  }
}
