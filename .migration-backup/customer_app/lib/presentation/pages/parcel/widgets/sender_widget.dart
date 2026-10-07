import 'package:demand/domain/model/model/address_info.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/parcel/parcel_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:phone_text_field/phone_text_field.dart';
import 'package:demand/app_constants.dart';
import 'custom_expanded.dart';

class SenderWidget extends StatelessWidget {
  final ParcelState state;
  final TextEditingController username;
  final TextEditingController phone;
  final TextEditingController house;
  final TextEditingController flour;
  final TextEditingController comment;
  final CustomColorSet colors;

  const SenderWidget({
    super.key,
    required this.state,
    required this.username,
    required this.phone,
    required this.house,
    required this.flour,
    required this.comment,
    required this.colors,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (state.expand)
          Padding(
            padding: EdgeInsets.only(bottom: 16.r),
            child: Text(
              AppHelpers.getTranslation(TrKeys.sender),
              style: CustomStyle.interNoSemi(size: 16, color: colors.textBlack),
            ),
          ),
        InkWell(
          onTap: () async {
            final data = await AppRoute.goMap(context);
            if (data.runtimeType == AddressInfo) {
              if (context.mounted) {
                context.read<ParcelBloc>().add(
                  ParcelEvent.setFromAddress(
                    context,
                    title: (data as AddressInfo).address,
                    location: data.toLocation(),
                  ),
                );
              }
            }
          },
          child: ButtonEffectAnimation(
            child: Container(
              decoration: BoxDecoration(
                color: colors.newBoxColor,
                borderRadius: BorderRadius.circular(10.r),
              ),
              padding: EdgeInsets.symmetric(horizontal: 20.r, vertical: 16.r),
              child: Row(
                children: [
                  Icon(
                    FlutterRemix.map_pin_range_line,
                    color: colors.textBlack,
                  ),
                  12.horizontalSpace,
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (state.addressFrom != null)
                        Text(
                          AppHelpers.getTranslation(TrKeys.pickup),
                          style: CustomStyle.interRegular(
                            size: 12,
                            color: colors.textBlack,
                          ),
                        ),
                      SizedBox(
                        width: MediaQuery.sizeOf(context).width / 2 - 20.r,
                        child: Text(
                          state.addressFrom ??
                              AppHelpers.getTranslation(TrKeys.pickup),
                          style: CustomStyle.interSemi(
                            size: 16,
                            color: colors.textBlack,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const Spacer(),
                  Icon(
                    FlutterRemix.arrow_right_s_line,
                    color: colors.textBlack,
                  ),
                ],
              ),
            ),
          ),
        ),
        ExpandedSection(
          expand: state.expand,
          child: Column(
            children: [
              24.verticalSpace,
              _phoneTextFormField(colors),
              16.verticalSpace,
              CustomTextFormField(
                hint: AppHelpers.getTranslation(TrKeys.fullName),
                controller: username,
                validation: (s) {
                  if (s?.isNotEmpty ?? false) {
                    return null;
                  }
                  return AppHelpers.getTranslation(TrKeys.canNotBeEmpty);
                },
              ),
              16.verticalSpace,
              CustomTextFormField(
                hint: AppHelpers.getTranslation(TrKeys.house),
                controller: house,
              ),
              16.verticalSpace,
              CustomTextFormField(
                hint: AppHelpers.getTranslation(TrKeys.floor),
                controller: flour,
              ),
              16.verticalSpace,
              CustomTextFormField(
                hint: AppHelpers.getTranslation(TrKeys.comment),
                controller: comment,
              ),
              24.verticalSpace,
            ],
          ),
        ),
      ],
    );
  }

  PhoneTextField _phoneTextFormField(CustomColorSet colors) {
    return PhoneTextField(
      initialValue: phone.text,
      initialCountryCode:
          LocalStorage.getAddress()?.countryCode ?? AppConstants.countryCodeISO,
      // LocalStorage.getAddress()?.countryCode?.toUpperCase(),
      textStyle: CustomStyle.interNormal(color: colors.textBlack),
      decoration: InputDecoration(
        hintText: AppHelpers.getTranslation(TrKeys.phoneNumber),
        hintStyle: CustomStyle.interNormal(
          size: 14.sp,
          color: CustomStyle.textHint,
        ),
        floatingLabelBehavior: FloatingLabelBehavior.always,
        fillColor: CustomStyle.black,
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
