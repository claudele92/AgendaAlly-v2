import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_keyboard_visibility/flutter_keyboard_visibility.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/auth/auth_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/auth/widgets/send_again_button.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:sms_autofill/sms_autofill.dart';

class ConfirmScreen extends StatefulWidget {
  final CustomColorSet colors;
  final String phone;
  final String email;
  final bool editPhone;

  const ConfirmScreen({
    super.key,
    required this.colors,
    required this.phone,
    required this.email,
    this.editPhone = false,
  });

  @override
  State<ConfirmScreen> createState() => _ConfirmScreenState();
}

class _ConfirmScreenState extends State<ConfirmScreen> {
  late TextEditingController controller;

  @override
  void initState() {
    controller = TextEditingController();
    super.initState();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return widget.editPhone
        ? _editPhone()
        : BlurWrap(
            radius: BorderRadius.only(
              topLeft: Radius.circular(AppConstants.radius.r),
              topRight: Radius.circular(AppConstants.radius.r),
            ),
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(AppConstants.radius.r),
                  topRight: Radius.circular(AppConstants.radius.r),
                ),
                color: CustomStyle.black.withValues(alpha: 0.25),
              ),
              child: SingleChildScrollView(
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
                            AppHelpers.getTranslation(TrKeys.enterOtpCode),
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
                      Center(
                        child: Text(
                          "${AppHelpers.getTranslation(TrKeys.weAreSendOTPCodeTo)} ${widget.phone}",
                          style: CustomStyle.interRegular(
                            size: 16,
                            color: widget.colors.white,
                          ),
                        ),
                      ),
                      24.verticalSpace,
                      SizedBox(
                        height: 64,
                        child: BlocBuilder<AuthBloc, AuthState>(
                          buildWhen: (n, l) {
                            return n.isError != l.isError;
                          },
                          builder: (context, state) {
                            return PinFieldAutoFill(
                              codeLength: 6,
                              controller: controller,
                              currentCode: controller.text,
                              onCodeSubmitted: (number) {
                                context.read<AuthBloc>().add(
                                  AuthEvent.checkVerify(
                                    context,
                                    phone: widget.phone,
                                    verifyId: state.verificationId,
                                    code: number,
                                  ),
                                );
                              },
                              cursor: Cursor(
                                width: 1,
                                height: 24,
                                color: widget.colors.white,
                                enabled: true,
                              ),
                              decoration: BoxLooseDecoration(
                                gapSpace: 10.r,
                                textStyle: CustomStyle.interNormal(
                                  size: 15,
                                  color: widget.colors.white,
                                ),
                                bgColorBuilder: FixedColorBuilder(
                                  widget.colors.transparent,
                                ),
                                radius: Radius.circular(30.r),
                                strokeColorBuilder: FixedColorBuilder(
                                  state.isError
                                      ? widget.colors.error
                                      : widget.colors.icon,
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                      16.verticalSpace,
                      BlocBuilder<AuthBloc, AuthState>(
                        buildWhen: (l, n) {
                          return l.isLoading != n.isLoading;
                        },
                        builder: (context, state) {
                          return Row(
                            children: [
                              Expanded(
                                flex: 1,
                                child: SendAgainButton(
                                  isLoading: state.isLoading,
                                  colors: widget.colors,
                                  phone: widget.phone,
                                  isReset: widget.editPhone,
                                ),
                              ),
                              8.horizontalSpace,
                              Expanded(
                                flex: 2,
                                child: CustomButton(
                                  isLoading: state.isLoading,
                                  title: AppHelpers.getTranslation(
                                    TrKeys.confirm,
                                  ),
                                  bgColor: widget.colors.primary,
                                  titleColor: CustomStyle.white,
                                  onTap: () => _checkConfirm(state),
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                      KeyboardVisibilityBuilder(
                        builder: (s, isKeyboard) {
                          return isKeyboard
                              ? 270.verticalSpace
                              : 24.verticalSpace;
                        },
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
  }

  void _checkConfirm(AuthState state) {
    if (state.isReset) {
      if (!AppHelpers.checkPhone(widget.phone)) {
        context.read<AuthBloc>().add(
          AuthEvent.forgotPasswordConfirm(
            context,
            code: controller.text,
            email: widget.phone,
          ),
        );
        return;
      }
      context.read<AuthBloc>().add(
        AuthEvent.checkVerify(
          context,
          editPhone: widget.editPhone,
          phone: widget.phone,
          verifyId: state.verificationId,
          code: controller.text,
        ),
      );
      return;
    }

    if (!AppHelpers.checkPhone(widget.phone)) {
      context.read<AuthBloc>().add(
        AuthEvent.checkVerifyEmail(context, code: controller.text),
      );
      return;
    }
    context.read<AuthBloc>().add(
      AuthEvent.checkVerify(
        context,
        editPhone: widget.editPhone,
        verifyId: state.verificationId,
        phone: widget.phone,
        code: controller.text,
      ),
    );
  }

  Container _editPhone() {
    final event = context.read<AuthBloc>();
    return Container(
      height: 300.r,
      decoration: BoxDecoration(color: widget.colors.backgroundColor),
      child: SingleChildScrollView(
        child: Padding(
          padding: EdgeInsets.all(20.r),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                AppHelpers.getTranslation(TrKeys.confirmation),
                style: CustomStyle.interNoSemi(
                  color: widget.colors.textBlack,
                  size: 30,
                ),
              ),
              16.verticalSpace,
              Text(
                AppHelpers.getTranslation(TrKeys.weAreSendOTPCodeTo),
                style: CustomStyle.interRegular(
                  size: 16,
                  color: widget.colors.textBlack,
                ),
              ),
              Text(
                widget.phone,
                style: CustomStyle.interRegular(
                  size: 16,
                  color: widget.colors.textBlack,
                ),
              ),
              24.verticalSpace,
              SizedBox(
                height: 64,
                child: BlocBuilder<AuthBloc, AuthState>(
                  buildWhen: (n, l) {
                    return n.isError != l.isError;
                  },
                  builder: (context, state) {
                    return PinFieldAutoFill(
                      codeLength: 6,
                      controller: controller,
                      currentCode: controller.text,
                      onCodeSubmitted: (number) {
                        if (AppValidators.isEmail(widget.phone)) {
                          event.add(
                            AuthEvent.checkVerifyEmail(context, code: number),
                          );
                        } else {
                          event.add(
                            AuthEvent.checkVerify(
                              context,
                              editPhone: widget.editPhone,
                              verifyId: state.verificationId,
                              code: number,
                              phone: widget.phone,
                            ),
                          );
                        }
                      },
                      cursor: Cursor(
                        width: 1,
                        height: 24,
                        color: widget.colors.textBlack,
                        enabled: true,
                      ),
                      decoration: BoxLooseDecoration(
                        gapSpace: 10.r,
                        textStyle: CustomStyle.interNormal(
                          size: 15,
                          color: widget.colors.textBlack,
                        ),
                        bgColorBuilder: FixedColorBuilder(
                          widget.colors.transparent,
                        ),
                        strokeColorBuilder: FixedColorBuilder(
                          state.isError
                              ? widget.colors.error
                              : widget.colors.bottomBarColor,
                        ),
                      ),
                    );
                  },
                ),
              ),
              16.verticalSpace,
              BlocBuilder<AuthBloc, AuthState>(
                buildWhen: (l, n) {
                  return l.isLoading != n.isLoading;
                },
                builder: (context, state) {
                  return Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: SendAgainButton(
                          isLoading: state.isLoading,
                          colors: widget.colors,
                          phone: widget.phone,
                        ),
                      ),
                      8.horizontalSpace,
                      Expanded(
                        flex: 2,
                        child: CustomButton(
                          isLoading: state.isLoading,
                          title: AppHelpers.getTranslation(TrKeys.confirm),
                          bgColor: widget.colors.primary,
                          titleColor: CustomStyle.white,
                          onTap: () {
                            if (AppValidators.isEmail(widget.phone)) {
                              event.add(
                                AuthEvent.checkVerifyEmail(
                                  context,
                                  code: controller.text,
                                ),
                              );
                            } else {
                              event.add(
                                AuthEvent.checkVerify(
                                  context,
                                  editPhone: widget.editPhone,
                                  verifyId: state.verificationId,
                                  code: controller.text,
                                  phone: widget.phone,
                                ),
                              );
                            }
                          },
                        ),
                      ),
                    ],
                  );
                },
              ),
              KeyboardVisibilityBuilder(
                builder: (s, isKeyboard) {
                  return isKeyboard ? 270.verticalSpace : 24.verticalSpace;
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
