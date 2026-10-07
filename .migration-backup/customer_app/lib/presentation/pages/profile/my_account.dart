import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/notification/notification_bloc.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import 'widgets/button_item.dart';

class MyAccount extends StatelessWidget {
  const MyAccount({super.key});

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        child: Column(
          children: [
            Row(
              children: [
                16.horizontalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.account),
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 18,
                  ),
                ),
                const Spacer(),
                IconButton(
                  onPressed: () {
                    AppRouteSetting.goNotification(context);
                  },
                  icon: Badge(
                    label: (LocalStorage.getToken().isEmpty)
                        ? const Text("0")
                        : BlocBuilder<NotificationBloc, NotificationState>(
                            builder: (context, state) {
                              return Text(
                                state.countOfNotifications?.notification
                                        .toString() ??
                                    "0",
                              );
                            },
                          ),
                    child: Icon(
                      FlutterRemix.notification_line,
                      color: colors.textBlack,
                    ),
                  ),
                ),
              ],
            ),
            24.verticalSpace,
            ButtonItem(
              icon: FlutterRemix.settings_3_line,
              title: AppHelpers.getTranslation(TrKeys.editAccount),
              onTap: () {
                AppRouteSetting.goEditProfile(context, colors: colors);
              },
              colors: colors,
            ),
            ButtonItem(
              icon: FlutterRemix.lock_2_line,
              title: AppHelpers.getTranslation(TrKeys.changePassword),
              onTap: () {
                AppRouteSetting.goChangePassword(context, colors: colors);
              },
              colors: colors,
            ),
            if (LocalStorage.getToken().isNotEmpty)
              ButtonItem(
                colors: colors,
                icon: FlutterRemix.logout_box_line,
                title: AppHelpers.getTranslation(TrKeys.deleteAccount),
                onTap: () {
                  AppHelpers.showCustomDialog(
                    context,
                    content: Container(
                      decoration: BoxDecoration(
                        color: colors.backgroundColor,
                        borderRadius: BorderRadius.circular(8.r),
                      ),
                      padding: EdgeInsets.all(16.r),
                      child: _deleteAlert(colors, context),
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

  Widget _deleteAlert(CustomColorSet colors, BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.areYouSureDeleteAccount),
          style: CustomStyle.interNormal(color: colors.textBlack, size: 18),
        ),
        16.verticalSpace,
        Row(
          children: [
            Expanded(
              child: CustomButton(
                title: AppHelpers.getTranslation(TrKeys.back),
                bgColor: colors.newBoxColor,
                titleColor: colors.textBlack,
                onTap: () {
                  Navigator.pop(context);
                },
              ),
            ),
            16.horizontalSpace,
            Expanded(
              child: CustomButton(
                title: AppHelpers.getTranslation(TrKeys.yes),
                bgColor: colors.primary,
                titleColor: colors.white,
                onTap: () {
                  AppRoute.goLogin(context);
                  authRepository.deleteAccount();
                },
              ),
            ),
          ],
        ),
      ],
    );
  }
}
