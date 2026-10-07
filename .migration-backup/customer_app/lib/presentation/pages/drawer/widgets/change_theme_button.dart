import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/custom_toggle.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class ChangeThemeButton extends StatelessWidget {
  final CustomColorSet colors;
  final AppTheme controller;

  const ChangeThemeButton({
    super.key,
    required this.colors,
    required this.controller,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(16.r),
      decoration: BoxDecoration(
        color: colors.newBoxColor,
        borderRadius: BorderRadius.circular(AppConstants.radius.r),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              AppHelpers.getTranslation(TrKeys.appTheme),
              style: CustomStyle.interNormal(color: colors.textBlack, size: 16),
            ),
          ),
          CustomToggle(
            isOnline: !controller.mode.isDark,
            onChange: (s) {
              controller.toggle();
            },
            colors: colors,
          ),
        ],
      ),
    );
  }
}
