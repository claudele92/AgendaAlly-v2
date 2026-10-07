import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/become_seller/become_seller_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class DeliveryInfo extends StatelessWidget {
  final List<String> types;
  final List<String> deliveryTypes;
  final CustomColorSet colors;
  final TextEditingController deliveryFrom;
  final TextEditingController deliveryTo;

  const DeliveryInfo({
    super.key,
    required this.colors,
    required this.types,
    required this.deliveryTypes,
    required this.deliveryFrom,
    required this.deliveryTo,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.deliveryInfo),
          style: CustomStyle.interNormal(color: colors.textBlack),
        ),
        16.verticalSpace,
        DropdownButtonFormField<String>(
          initialValue: deliveryTypes.first,
          items: deliveryTypes
              .map(
                (e) => DropdownMenuItem<String>(
                  value: e,
                  child: Text(AppHelpers.getTranslation(e)),
                ),
              )
              .toList(),
          onChanged: (s) {
            if (s != null) {
              context.read<BecomeSellerBloc>().add(
                BecomeSellerEvent.setDeliveryType(type: s),
              );
            }
          },
          style: CustomStyle.interNormal(color: colors.textBlack),
          dropdownColor: colors.backgroundColor,
          decoration: InputDecoration(
            labelText: AppHelpers.getTranslation(TrKeys.deliveryType),
            labelStyle: CustomStyle.interNormal(
              size: 14.sp,
              color: CustomStyle.textHint,
            ),
            floatingLabelBehavior: FloatingLabelBehavior.always,
            fillColor: colors.transparent,
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
        ),
        16.verticalSpace,
        DropdownButtonFormField<String>(
          initialValue: types.first,
          items: types
              .map(
                (e) => DropdownMenuItem<String>(
                  value: e,
                  child: Text(AppHelpers.getTranslation(e)),
                ),
              )
              .toList(),
          onChanged: (s) {
            if (s != null) {
              context.read<BecomeSellerBloc>().add(
                BecomeSellerEvent.setDeliveryType(type: s),
              );
            }
          },
          style: CustomStyle.interNormal(color: colors.textBlack),
          dropdownColor: colors.backgroundColor,
          decoration: InputDecoration(
            labelText: AppHelpers.getTranslation(TrKeys.deliveryTimeType),
            labelStyle: CustomStyle.interNormal(
              size: 14.sp,
              color: CustomStyle.textHint,
            ),
            floatingLabelBehavior: FloatingLabelBehavior.always,
            fillColor: colors.transparent,
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
        ),
        16.verticalSpace,
        Row(
          children: [
            Expanded(
              child: CustomTextFormField(
                hint: AppHelpers.getTranslation(TrKeys.deliveryTimeFrom),
                controller: deliveryFrom,
                validation: AppValidators.isNumberValidator,
                inputType: TextInputType.number,
              ),
            ),
            16.horizontalSpace,
            Expanded(
              child: CustomTextFormField(
                hint: AppHelpers.getTranslation(TrKeys.deliveryTimeTo),
                controller: deliveryTo,
                validation: AppValidators.isNumberValidator,
                inputType: TextInputType.number,
              ),
            ),
          ],
        ),
      ],
    );
  }
}
