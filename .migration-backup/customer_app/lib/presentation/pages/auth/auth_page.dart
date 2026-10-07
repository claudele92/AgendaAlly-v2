import 'package:demand/infrastructure/session/session_coordinator.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../../application/auth/auth_bloc.dart';
import '../../../infrastructure/local_storage/local_storage.dart';
import '../../../infrastructure/service/services.dart';
import '../../app_assets.dart';
import '../../components/components.dart';
import '../../route/app_route.dart';
import '../../route/app_route_setting.dart';
import '../../style/style.dart';

class AuthPage extends StatefulWidget {
  const AuthPage({super.key});

  @override
  State<AuthPage> createState() => _AuthPageState();
}

class _AuthPageState extends State<AuthPage> {
  late TextEditingController _phoneController;
  late TextEditingController _emailController;

  @override
  void initState() {
    // AppsFlyerService.init(context);
    // AppLinksService.init(context);
    _phoneController = TextEditingController();
    _emailController = TextEditingController();
    super.initState();
  }

  @override
  void dispose() {
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        image: DecorationImage(
          image: AssetImage(Assets.imagesBackground),
          fit: BoxFit.cover,
        ),
      ),
      child: CustomScaffold(
        bgColor: CustomStyle.transparent,
        body: (colors) => BlocListener<AuthBloc, AuthState>(
          listenWhen: (prev, next) {
            return prev.screenType.name != next.screenType.name;
          },
          listener: (context, state) {
            debugPrint('==> update screen type ${state.screenType}');
            if (state.screenType == AuthType.confirm) {
              AppRoute.goConfirmBottomSheet(
                context,
                colors,
                _phoneController.text,
                _emailController.text,
              );
              return;
            }
            if (state.screenType == AuthType.signUpFull) {
              AppRoute.goSignUpFieldBottomSheet(
                context,
                colors,
                _phoneController.text.trim(),
                _emailController.text.trim(),
              );
              return;
            }
            if (state.screenType == AuthType.updatePassword) {
              AppRoute.goUpdatePasswordBottomSheet(
                context,
                colors,
                _phoneController,
              );
              return;
            }
          },
          child: SafeArea(
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: Column(
                children: [
                  if (SessionCoordinator.instance.logoutMessage != null)
                    Text(
                      SessionCoordinator.instance.logoutMessage!,
                      style: const TextStyle(color: Colors.white),
                    ),
                  16.verticalSpace,
                  Row(
                    children: [
                      Text(
                        AppHelpers.getAppName(),
                        style: CustomStyle.interNormal(
                          color: CustomStyle.white,
                        ),
                      ),
                      const Spacer(),
                      SecondButton(
                        title: AppHelpers.getTranslation(TrKeys.skip),
                        bgColor: CustomStyle.black,
                        titleColor: CustomStyle.white,
                        onTap: () {
                          if (LocalStorage.getAddress() == null) {
                            AppRouteSetting.goSelectCountry(context);
                            return;
                          }
                          AppRoute.goMain(context);
                        },
                      ),
                    ],
                  ),
                  const Spacer(),
                  Text(
                    AppHelpers.getTranslation(
                      TrKeys.findTheBestServicesInYourCity,
                    ),
                    style: CustomStyle.interNormal(
                      color: colors.white,
                      size: 35,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  32.verticalSpace,
                  CustomButton(
                    title: AppHelpers.getTranslation(TrKeys.login),
                    bgColor: colors.primary,
                    titleColor: colors.white,
                    onTap: () {
                      AppRoute.goLoginBottomSheet(
                        context,
                        colors,
                        _phoneController,
                        _emailController,
                      );
                    },
                  ),
                  10.verticalSpace,
                  CustomButton(
                    title: AppHelpers.getTranslation(TrKeys.signUp),
                    bgColor: colors.transparent,
                    titleColor: colors.white,
                    borderColor: colors.white,
                    onTap: () {
                      AppRoute.goSignUpBottomSheet(
                        context,
                        colors,
                        _phoneController,
                        _emailController,
                      );
                    },
                  ),
                  32.verticalSpace,
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
