import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../../application/membership/membership_bloc.dart';
import '../../../domain/model/models.dart';
import '../../../infrastructure/local_storage/local_storage.dart';
import '../../../infrastructure/service/services.dart';
import '../../components/components.dart';
import '../../route/app_route_setting.dart';
import '../../style/style.dart';
import '../../style/theme/theme.dart';

class MembershipPaymentBottomSheet extends StatelessWidget {
  final CustomColorSet colors;
  final ScrollController controller;
  final MembershipModel? model;

  const MembershipPaymentBottomSheet({
    super.key,
    required this.colors,
    required this.controller,
    required this.model,
  });

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: Container(
        width: MediaQuery.sizeOf(context).width,
        height: 400,
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.only(left: 16.r, right: 16.r),
        child: BlocBuilder<MembershipBloc, MembershipState>(
          builder: (context, state) {
            return ListView(
              controller: controller,
              children: [
                8.verticalSpace,
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
                16.verticalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.payment),
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 22,
                  ),
                ),
                if (((LocalStorage.getUser().wallet?.price ?? 0) > 0))
                  UseWalletWidget(
                    colors: colors,
                    isActive: (state.walletPrice ?? 0) != 0,
                    totalPrice: state.walletPrice,
                    onTap: () {
                      if ((state.walletPrice ?? 0) != 0 ||
                          (LocalStorage.getUser().wallet?.price ?? 0) <= 0) {
                        context.read<MembershipBloc>().add(
                          MembershipEvent.setWalletPrice(price: null),
                        );
                        return;
                      }
                      AppHelpers.showAlertDialog(
                        context,
                        colors: colors,
                        child: WalletPayDialog(
                          colors: colors,
                          totalPrice: model?.price,
                          onChange: (price) {
                            context.read<MembershipBloc>().add(
                              MembershipEvent.setWalletPrice(price: price),
                            );
                          },
                        ),
                      );
                    },
                  ),
                16.verticalSpace,
                PriceItem(
                  title: AppHelpers.getTranslation(TrKeys.total),
                  price: model?.price,
                  colors: colors,
                ),
                if ((state.walletPrice ?? 0) > 0)
                  PriceItem(
                    title: AppHelpers.getTranslation(TrKeys.wallet),
                    discount: true,
                    price: state.walletPrice,
                    colors: colors,
                  ),
                16.verticalSpace,
                state.isPaymentLoading
                    ? const Loading()
                    : ListView.builder(
                        reverse: true,
                        padding: EdgeInsets.zero,
                        itemCount: state.availablePayments.length,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemBuilder: (context, index) {
                          return InkWell(
                            onTap: () {
                              context.read<MembershipBloc>().add(
                                MembershipEvent.selectPayment(
                                  payment: state.availablePayments[index],
                                ),
                              );
                            },
                            child: Column(
                              children: [
                                8.verticalSpace,
                                Row(
                                  children: [
                                    Icon(
                                      state.availablePayments[index].id ==
                                              state.selectedPayment?.id
                                          ? FlutterRemix.checkbox_circle_fill
                                          : FlutterRemix
                                                .checkbox_blank_circle_line,
                                      color:
                                          state.availablePayments[index].id ==
                                              state.selectedPayment?.id
                                          ? colors.primary
                                          : CustomStyle.black,
                                    ),
                                    10.horizontalSpace,
                                    Expanded(
                                      child: Text(
                                        AppHelpers.getTranslation(
                                          '${state.availablePayments[index].tag}',
                                        ),
                                        style: CustomStyle.interNormal(
                                          size: 14,
                                          color: colors.textBlack,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                Divider(color: colors.newBoxColor),
                                8.verticalSpace,
                              ],
                            ),
                          );
                        },
                      ),
                16.verticalSpace,
                CustomButton(
                  isLoading: state.isButtonLoading,
                  title: AppHelpers.getTranslation(TrKeys.confirm),
                  bgColor: colors.primary,
                  titleColor: colors.textWhite,
                  onTap: () {
                    final num wallet =
                        LocalStorage.getUser().wallet?.price ?? 0;
                    if (state.selectedPayment?.tag == TrKeys.wallet &&
                        wallet < (model?.price ?? 0)) {
                      AppHelpers.errorSnackBar(
                        context,
                        message: AppHelpers.getTranslation(
                          TrKeys.notEnoughMoney,
                        ),
                      );
                      return;
                    }
                    if (state.selectedPayment?.tag != TrKeys.wallet) {
                      context.read<MembershipBloc>().add(
                        MembershipEvent.fetchWebView(
                          context,
                          membership: model,
                          onSuccess: () {
                            Navigator.popUntil(context, (route) {
                              return route.isFirst;
                            });
                            AppRouteSetting.goMyMemberships(context);
                          },
                        ),
                      );
                    } else {
                      context.read<MembershipBloc>().add(
                        MembershipEvent.createTransaction(
                          context,
                          membershipId: model?.id ?? 0,
                          onSuccess: () {
                            Navigator.popUntil(context, (route) {
                              return route.isFirst;
                            });
                            AppRouteSetting.goMyMemberships(context);
                          },
                        ),
                      );
                    }
                  },
                ),
                16.verticalSpace,
              ],
            );
          },
        ),
      ),
    );
  }
}
