import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../../app_constants.dart';
import '../../../application/auth/auth_bloc.dart';
import '../../../infrastructure/service/services.dart';
import '../../components/components.dart';
import '../../style/style.dart';
import '../../style/theme/theme.dart';

class ForgetPasswordScreen extends StatefulWidget {
  final CustomColorSet colors;
  final TextEditingController phoneController;
  final TextEditingController emailController;

  const ForgetPasswordScreen({
    super.key,
    required this.colors,
    required this.phoneController,
    required this.emailController,
  });

  @override
  State<ForgetPasswordScreen> createState() => _ForgetPasswordScreenState();
}

class _ForgetPasswordScreenState extends State<ForgetPasswordScreen>
    with SingleTickerProviderStateMixin {
  final GlobalKey<FormState> formKey = GlobalKey<FormState>();
  late TabController _tabController;

  @override
  void initState() {
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
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlurWrap(
      radius: BorderRadius.only(
        topLeft: Radius.circular(AppConstants.radius.r),
        topRight: Radius.circular(AppConstants.radius.r),
      ),
      child: Container(
        margin: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(AppConstants.radius.r),
            topRight: Radius.circular(AppConstants.radius.r),
          ),
          color: CustomStyle.black.withValues(alpha: 0.25),
        ),
        child: SingleChildScrollView(
          child: Form(
            key: formKey,
            child: Padding(
              padding: EdgeInsets.all(20.r),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
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
                        AppHelpers.getTranslation(TrKeys.forgetPassword),
                        style: CustomStyle.interNoSemi(
                          color: widget.colors.white,
                          size: 20,
                        ),
                      ),
                      const Spacer(),
                      SizedBox(width: 42.r),
                    ],
                  ),
                  32.verticalSpace,
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
                            widget.phoneController.text =
                                phoneNum;
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
                  16.verticalSpace,
                  BlocBuilder<AuthBloc, AuthState>(
                    buildWhen: (l, n) {
                      return l.isLoading != n.isLoading;
                    },
                    builder: (context, state) {
                      return CustomButton(
                        isLoading: state.isLoading,
                        title: AppHelpers.getTranslation(TrKeys.continueText),
                        bgColor: widget.colors.primary,
                        titleColor: CustomStyle.white,
                        onTap: () {
                          if (formKey.currentState?.validate() ?? false) {
                            if (AppHelpers.checkPhone(
                                  widget.phoneController.text.replaceAll(
                                    " ",
                                    "",
                                  ),
                                ) &&
                                widget.emailController.text.isEmpty) {
                              context.read<AuthBloc>().add(
                                AuthEvent.checkPhone(
                                  context,
                                  phone: widget.phoneController.text,
                                  onSuccess: () {
                                    context.read<AuthBloc>().add(
                                      AuthEvent.forgotPassword(
                                        context,
                                        phone: widget.phoneController.text,
                                      ),
                                    );
                                  },
                                ),
                              );
                            } else {
                              context.read<AuthBloc>().add(
                                AuthEvent.forgotPassword(
                                  context,
                                  phone: widget.emailController.text,
                                ),
                              );
                            }
                          }
                        },
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
