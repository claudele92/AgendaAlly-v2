import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../../app_constants.dart';
import '../../../application/auth/auth_bloc.dart';
import '../../../infrastructure/local_storage/local_storage.dart';
import '../../../infrastructure/service/services.dart';
import '../../components/components.dart';
import '../../route/app_route.dart';
import '../../route/app_route_setting.dart';
import '../../style/style.dart';
import '../../style/theme/theme.dart';
import 'widgets/social_button.dart';

class LoginScreen extends StatefulWidget {
  final CustomColorSet colors;
  final TextEditingController phoneController;
  final TextEditingController emailController;

  const LoginScreen({
    super.key,
    required this.colors,
    required this.phoneController,
    required this.emailController,
  });

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  late TextEditingController _passwordController;
  late TabController _tabController;
  final GlobalKey<FormState> formKey = GlobalKey<FormState>();

  @override
  void initState() {
    _passwordController = TextEditingController();
    _tabController = TabController(
      initialIndex: AppHelpers.getAuthOption() == SignUpType.email ? 1 : 0,
      length: 2,
      vsync: this,
    );
    super.initState();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlurWrap(
      radius: BorderRadius.only(
        topLeft: Radius.circular(AppConstants.radius.r),
        topRight: Radius.circular(AppConstants.radius.r),
      ),
      child: KeyboardDismisser(
        isLtr: LocalStorage.getLangLtr(),
        child: Container(
          margin: EdgeInsets.only(
            bottom: MediaQuery.viewInsetsOf(context).bottom,
          ),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(AppConstants.radius.r),
              topRight: Radius.circular(AppConstants.radius.r),
            ),
            color: CustomStyle.black.withValues(alpha: 0.25),
          ),
          child: Form(
            key: formKey,
            child: SingleChildScrollView(
              child: Column(
                children: [
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 20.r),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        20.verticalSpace,
                        Row(
                          children: [
                            IconButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              icon: Icon(
                                FlutterRemix.arrow_left_line,
                                color: widget.colors.white,
                                size: 26.r,
                              ),
                            ),
                            const Spacer(),
                            Text(
                              AppHelpers.getTranslation(TrKeys.login),
                              style: CustomStyle.interNoSemi(
                                color: widget.colors.white,
                                size: 20,
                              ),
                            ),
                            const Spacer(),
                            SizedBox(width: 42.r),
                          ],
                        ),
                        16.verticalSpace,
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children:
                              (Platform.isAndroid
                                      ? ListConstants.socialSignInAndroid
                                      : ListConstants.socialSignIn)
                                  .map(
                                    (e) => SocialButton(
                                      iconColor: widget.colors.textBlack,
                                      bgColor: widget.colors.socialButtonColor,
                                      icon: e,
                                      onTap: () {
                                        context.read<AuthBloc>().add(
                                          AuthEvent.socialSignIn(
                                            context,
                                            type: e,
                                            onSuccess: () {
                                              if (LocalStorage.getAddress() ==
                                                  null) {
                                                AppRouteSetting.goSelectCountry(
                                                  context,
                                                );
                                                return;
                                              }
                                              AppRoute.goMain(context);
                                            },
                                          ),
                                        );
                                      },
                                    ),
                                  )
                                  .toList(),
                        ),
                        10.verticalSpace,
                        Row(
                          children: [
                            Expanded(
                              child: Divider(color: widget.colors.white),
                            ),
                            8.horizontalSpace,
                            Text(
                              AppHelpers.getTranslation(TrKeys.orAccessQuickly),
                              style: CustomStyle.interNormal(
                                color: widget.colors.white,
                                size: 12,
                              ),
                            ),
                            8.horizontalSpace,
                            Expanded(
                              child: Divider(color: widget.colors.white),
                            ),
                          ],
                        ),
                        16.verticalSpace,
                        if (AppHelpers.getAuthOption() == SignUpType.both)
                          AuthTabBar(
                            tabController: _tabController,
                            tabs: [
                              AuthTab(
                                text: TrKeys.phone,
                                icon: FlutterRemix.phone_fill,
                              ),
                              AuthTab(
                                text: TrKeys.email,
                                icon: FlutterRemix.mail_fill,
                              ),
                            ],
                          ),
                        16.verticalSpace,
                        SizedBox(
                          height: 76.r,
                          child: TabBarView(
                            controller: _tabController,
                            physics: NeverScrollableScrollPhysics(),
                            children: [
                              CustomPhoneField(
                                colors: widget.colors,
                                onChanged: (phoneNum) {
                                  widget.phoneController.text = phoneNum;
                                  widget.emailController.clear();
                                },
                              ),
                              CustomTextFormField(
                                fillColor: widget.colors.socialButtonColor,
                                validation: AppValidators.isValidEmail,
                                controller: widget.emailController,
                                hint: AppHelpers.getTranslation(TrKeys.email),
                                onChanged: (value) {
                                  widget.emailController.text = value;
                                  widget.phoneController.clear();
                                },
                              ),
                            ],
                          ),
                        ),
                        12.verticalSpace,
                        Container(
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(10.r),
                            color: widget.colors.socialButtonColor,
                          ),
                          child: BlocBuilder<AuthBloc, AuthState>(
                            buildWhen: (l, n) {
                              return l.isShowPassword != n.isShowPassword;
                            },
                            builder: (context, state) {
                              return CustomTextFormField(
                                obscure: state.isShowPassword,
                                controller: _passwordController,
                                maxLines: 1,
                                validation: AppValidators.isValidPassword,
                                hint: AppHelpers.getTranslation(
                                  TrKeys.password,
                                ),
                                suffixIcon: IconButton(
                                  onPressed: () {
                                    context.read<AuthBloc>().add(
                                      const AuthEvent.showPassword(),
                                    );
                                  },
                                  icon: Icon(
                                    !state.isShowPassword
                                        ? FlutterRemix.eye_close_line
                                        : FlutterRemix.eye_line,
                                    color: widget.colors.textBlack,
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                        16.verticalSpace,
                        Row(
                          children: [
                            const Spacer(),
                            ButtonEffectAnimation(
                              onTap: () {
                                context.read<AuthBloc>().add(
                                  const AuthEvent.switchScreen(
                                    AuthType.forgetPassword,
                                  ),
                                );
                                Navigator.pop(context);
                                AppRoute.goForgetPasswordBottomSheet(
                                  context,
                                  widget.colors,
                                  widget.phoneController,
                                  widget.emailController,
                                );
                              },
                              child: Text(
                                AppHelpers.getTranslation(
                                  TrKeys.forgetPassword,
                                ),
                                style: CustomStyle.interNormal(
                                  color: widget.colors.primary,
                                ),
                              ),
                            ),
                          ],
                        ),
                        32.verticalSpace,
                        BlocBuilder<AuthBloc, AuthState>(
                          buildWhen: (l, n) {
                            return l.isLoading != n.isLoading;
                          },
                          builder: (context, state) {
                            return CustomButton(
                              isLoading: state.isLoading,
                              title: AppHelpers.getTranslation(TrKeys.login),
                              bgColor: widget.colors.primary,
                              titleColor: CustomStyle.white,
                              onTap: () {
                                if (formKey.currentState?.validate() ?? false) {
                                  context.read<AuthBloc>().add(
                                    AuthEvent.login(
                                      context,
                                      phone: widget.phoneController.text,
                                      email: widget.emailController.text,
                                      password: _passwordController.text,
                                      onSuccess: () {
                                        if (LocalStorage.getAddress() == null) {
                                          AppRouteSetting.goSelectCountry(
                                            context,
                                          );
                                          return;
                                        }
                                        AppRoute.goMain(context);
                                      },
                                    ),
                                  );
                                }
                              },
                            );
                          },
                        ),
                      ],
                    ),
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
