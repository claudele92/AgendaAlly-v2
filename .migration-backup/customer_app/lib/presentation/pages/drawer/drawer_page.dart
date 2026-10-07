import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/route/app_route_parcel.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme_wrapper.dart';

import 'widgets/drawer_item.dart';
import 'widgets/wallet_widget.dart';

class DrawerPage extends StatelessWidget {
  const DrawerPage({super.key});

  @override
  Widget build(BuildContext context) {
    return ThemeWrapper(
      builder: (colors, controller) {
        return KeyboardDismisser(
          isLtr: LocalStorage.getLangLtr(),
          child: Container(
            decoration: BoxDecoration(color: colors.backgroundColor),
            child: ListView(
              padding: EdgeInsets.symmetric(vertical: 20.r),
              shrinkWrap: true,
              children: [
                32.verticalSpace,
                if (LocalStorage.getToken().isNotEmpty)
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: WalletScreen(colors: colors),
                  ),
                24.verticalSpace,
                if (LocalStorage.getToken().isEmpty)
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.login_circle_line,
                    title: TrKeys.login,
                    onTap: () {
                      AppRoute.goLogin(context);
                    },
                  ),
                if (LocalStorage.getToken().isNotEmpty)
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.user_6_line,
                    title: TrKeys.myAccount,
                    onTap: () {
                      AppRouteSetting.goMyAccount(context);
                    },
                  ),

                if (LocalStorage.getToken().isNotEmpty)
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.calendar_2_line,
                    title: TrKeys.myAppointments,
                    onTap: () {
                      Navigator.pop(context);
                      context.read<MainBloc>().add(
                        const MainEvent.changeIndex(index: 2),
                      );
                      context.read<BookingBloc>().add(
                        BookingEvent.fetchBookUpcoming(
                          context,
                          isRefresh: true,
                        ),
                      );
                      context.read<BookingBloc>().add(
                        BookingEvent.fetchBookPast(context, isRefresh: true),
                      );
                    },
                  ),
                if (AppConstants.isDemo)
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.magic_line,
                    title: TrKeys.selectUiType,
                    onTap: () {
                      AppRouteSetting.goSelectUIType(context);
                    },
                  ),

                if (LocalStorage.getToken().isNotEmpty &&
                    AppHelpers.getParcel())
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.archive_line,
                    title: TrKeys.parcel,
                    onTap: () {
                      AppRouteParcel.goParcel(context);
                    },
                  ),
                if (LocalStorage.getToken().isNotEmpty &&
                    AppHelpers.getParcel())
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.archive_drawer_line,
                    title: TrKeys.parcelHistory,
                    onTap: () {
                      AppRouteParcel.goParcelList(context);
                    },
                  ),
                if (LocalStorage.getToken().isNotEmpty &&
                    AppHelpers.getProductsEnabled())
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.file_list_2_line,
                    title: TrKeys.orderHistory,
                    onTap: () {
                      AppRoute.goOrdersList(context);
                    },
                  ),
                if (LocalStorage.getToken().isNotEmpty &&
                    AppHelpers.getProductsEnabled())
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.file_list_3_line,
                    title: TrKeys.myDigitalList,
                    onTap: () {
                      AppRoute.goMyDigitalList(context);
                    },
                  ),
                if (LocalStorage.getToken().isNotEmpty &&
                    AppHelpers.getReferralActive())
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.money_dollar_circle_line,
                    title: TrKeys.inviteFriend,
                    onTap: () {
                      AppRouteSetting.goMyReferral(context);
                    },
                  ),

                if (LocalStorage.getToken().isNotEmpty)
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.gift_line,
                    title: TrKeys.myGiftCarts,
                    onTap: () {
                      AppRouteSetting.goMyGiftCart(context);
                    },
                  ),
                if (LocalStorage.getToken().isNotEmpty &&
                    AppHelpers.getProductsEnabled())
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.stack_line,
                    title: TrKeys.compare,
                    onTap: () {
                      AppRoute.goComparePage(context);
                    },
                  ),
                if (AppHelpers.getProductsEnabled())
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.archive_line,
                    title: TrKeys.categories,
                    onTap: () {
                      AppRoute.goCategoryPage(context);
                    },
                  ),
                DrawerItem(
                  colors: colors,
                  icon: FlutterRemix.store_2_line,
                  title: TrKeys.shops,
                  onTap: () {
                    AppRouteShop.goShopListPage(context);
                  },
                ),
                if (LocalStorage.getToken().isNotEmpty) ...[
                  24.verticalSpace,
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: Text(
                      AppHelpers.getTranslation(
                        TrKeys.forBusiness,
                      ).toUpperCase(),
                      style: CustomStyle.interNormal(
                        color: colors.textBlack,
                        size: 12,
                      ),
                    ),
                  ),
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.coupon_3_line,
                    title: TrKeys.myMemberships,
                    onTap: () {
                      AppRouteSetting.goMyMemberships(context);
                    },
                  ),
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.briefcase_line,
                    title: TrKeys.becomeSeller,
                    onTap: () {
                      AppRouteShop.goBecomeSeller(context);
                    },
                  ),
                  if (AppHelpers.getGroupOrder() &&
                      AppHelpers.getProductsEnabled())
                    DrawerItem(
                      colors: colors,
                      icon: FlutterRemix.team_line,
                      title: TrKeys.groupOrder,
                      onTap: () {
                        AppRouteSetting.goGroupOrder(context, colors);
                      },
                    ),
                ],
                24.verticalSpace,
                Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16.r),
                  child: Text(
                    AppHelpers.getTranslation(TrKeys.setting).toUpperCase(),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 12,
                    ),
                  ),
                ),
                DrawerItem(
                  colors: colors,
                  icon: FlutterRemix.settings_3_line,
                  title: TrKeys.appSetting,
                  onTap: () {
                    AppRouteSetting.goAppSetting(context);
                  },
                ),
                if (LocalStorage.getToken().isNotEmpty)
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.message_3_line,
                    title: TrKeys.messages,
                    onTap: () {
                      AppRouteSetting.goChatsList(context);
                    },
                  ),
                DrawerItem(
                  colors: colors,
                  icon: FlutterRemix.error_warning_line,
                  title: TrKeys.helpInfo,
                  onTap: () {
                    AppRouteSetting.goHelp(context);
                  },
                ),
                DrawerItem(
                  colors: colors,
                  icon: FlutterRemix.alarm_warning_line,
                  title: TrKeys.privacy,
                  onTap: () {
                    AppRouteSetting.goPolicy(context);
                  },
                ),
                DrawerItem(
                  colors: colors,
                  icon: FlutterRemix.spam_line,
                  title: TrKeys.terms,
                  onTap: () {
                    AppRouteSetting.goTerm(context);
                  },
                ),
                if (LocalStorage.getToken().isNotEmpty)
                  DrawerItem(
                    colors: colors,
                    icon: FlutterRemix.logout_circle_line,
                    title: TrKeys.logout,
                    onTap: () async {
                      await authRepository.logout();
                      if (context.mounted) AppRoute.goLogin(context);
                    },
                  ),
                16.verticalSpace,
              ],
            ),
          ),
        );
      },
    );
  }
}
