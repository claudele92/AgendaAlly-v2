import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/auth/auth_bloc.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/firebase/firebase_service.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/auth/confirm_screen.dart';
import 'package:demand/presentation/pages/profile/widgets/avatar_edit.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:phone_text_field/phone_text_field.dart';

import 'package:demand/app_constants.dart';

class EditAccountScreen extends StatefulWidget {
  final CustomColorSet colors;

  const EditAccountScreen({super.key, required this.colors});

  @override
  State<EditAccountScreen> createState() => _EditAccountScreenState();
}

class _EditAccountScreenState extends State<EditAccountScreen> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  late TextEditingController firstName;
  late TextEditingController lastName;
  late TextEditingController phone;
  late TextEditingController email;

  @override
  void initState() {
    firstName = TextEditingController(text: LocalStorage.getUser().firstname);
    lastName = TextEditingController(text: LocalStorage.getUser().lastname);
    phone = TextEditingController(text: LocalStorage.getUser().phone);
    email = TextEditingController(text: LocalStorage.getUser().email);
    super.initState();
  }

  @override
  void dispose() {
    firstName.dispose();
    lastName.dispose();
    phone.dispose();
    email.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlurWrap(
      radius: BorderRadius.only(
        topRight: Radius.circular(24.r),
        topLeft: Radius.circular(24.r),
      ),
      child: Container(
        margin: MediaQuery.viewInsetsOf(context),
        width: double.infinity,
        decoration: BoxDecoration(
          color: widget.colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.all(16.r),
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                12.verticalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.editAccount),
                  style: CustomStyle.interNoSemi(
                    color: widget.colors.textBlack,
                    size: 22,
                  ),
                ),
                24.verticalSpace,
                AvatarEditScreen(colors: widget.colors),
                24.verticalSpace,
                CustomTextFormField(
                  hint: AppHelpers.getTranslation(TrKeys.firstName),
                  controller: firstName,
                  validation: AppValidators.isNotEmptyValidator,
                ),
                16.verticalSpace,
                CustomTextFormField(
                  hint: AppHelpers.getTranslation(TrKeys.lastName),
                  controller: lastName,
                  validation: AppValidators.isNotEmptyValidator,
                ),
                16.verticalSpace,
                _phoneTextFormField(widget.colors),
                16.verticalSpace,
                CustomTextFormField(
                  hint: AppHelpers.getTranslation(TrKeys.email),
                  controller: email,
                  validation: AppValidators.isValidEmail,
                ),
                24.verticalSpace,
                BlocBuilder<ProfileBloc, ProfileState>(
                  buildWhen: (p, n) {
                    return p.isLoading != n.isLoading;
                  },
                  builder: (context, state) {
                    return BlocConsumer<AuthBloc, AuthState>(
                      listenWhen: (p, n) {
                        return p.verificationId != n.verificationId;
                      },
                      listener: (con, s) {
                        if (s.verificationId.isNotEmpty) {
                          showDialog(
                            context: context,
                            builder: (_) {
                              return Dialog(
                                backgroundColor: widget.colors.transparent,
                                insetPadding: EdgeInsets.symmetric(
                                  horizontal: 16.w,
                                ),
                                child: BlocProvider.value(
                                  value: context.read<AuthBloc>(),
                                  child: ConfirmScreen(
                                    editPhone: true,
                                    colors: widget.colors,
                                    phone: phone.text,
                                    email: email.text,
                                  ),
                                ),
                              );
                            },
                          ).then((value) {
                            if (value as bool) {
                              if (context.mounted) {
                                context.read<ProfileBloc>().add(
                                  ProfileEvent.updateUser(
                                    context,
                                    firstName: firstName.text,
                                    lastName: lastName.text,
                                    phone: phone.text,
                                    email: email.text,
                                    onSuccess: () => Navigator.pop(context),
                                  ),
                                );
                              }
                            }
                          });
                        }
                      },
                      builder: (context, stateAuth) {
                        return CustomButton(
                          isLoading: state.isLoading || stateAuth.isLoading,
                          title: AppHelpers.getTranslation(TrKeys.save),
                          bgColor: widget.colors.primary,
                          titleColor: widget.colors.white,
                          onTap: () {
                            if (_formKey.currentState?.validate() ?? false) {
                              if (phone.text ==
                                  (LocalStorage.getUser().phone ?? "")) {
                                context.read<ProfileBloc>().add(
                                  ProfileEvent.updateUser(
                                    context,
                                    firstName: firstName.text,
                                    lastName: lastName.text,
                                    onSuccess: () => Navigator.pop(context),
                                    phone: phone.text,
                                    email: email.text,
                                  ),
                                );
                                return;
                              }
                              context.read<AuthBloc>().add(
                                AuthEvent.checkPhone(
                                  context,
                                  phone: phone.text,
                                  onSuccess: () {
                                    FirebaseService.sendCode(
                                      phone: phone.text,
                                      onSuccess: (id) {
                                        context.read<AuthBloc>().add(
                                          AuthEvent.setVerificationId(id: id),
                                        );
                                      },
                                      onError: (e) {
                                        AppHelpers.errorSnackBar(
                                          context,
                                          message: e,
                                        );
                                      },
                                    );
                                  },
                                ),
                              );
                            }
                          },
                        );
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
    );
  }

  PhoneTextField _phoneTextFormField(CustomColorSet colors) {
    return PhoneTextField(
      initialValue: phone.text,
      initialCountryCode:
          LocalStorage.getAddress()?.countryCode ?? AppConstants.countryCodeISO,
      // LocalStorage.getAddress()?.countryCode?.toUpperCase(),
      isRequired: false,
      textStyle: CustomStyle.interNormal(color: colors.textBlack),
      decoration: InputDecoration(
        hintText: AppHelpers.getTranslation(TrKeys.phoneNumber),
        hintStyle: CustomStyle.interNormal(
          size: 14.sp,
          color: CustomStyle.textHint,
        ),
        floatingLabelBehavior: FloatingLabelBehavior.always,
        filled: false,
        enabledBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        errorBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        border: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        disabledBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        focusedBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
      ),
      onChanged: (s) {
        phone.text = s.completeNumber;
      },
    );
  }
}
