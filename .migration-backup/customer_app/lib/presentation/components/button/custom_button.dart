import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';

class CustomButton extends StatelessWidget {
  final String title;
  final bool isLoading;
  final bool changeColor;
  final double radius;
  final Color bgColor;
  final Color titleColor;
  final Color borderColor;
  final VoidCallback onTap;

  const CustomButton({
    super.key,
    required this.title,
    this.radius = AppConstants.radius,
    required this.bgColor,
    required this.titleColor,
    required this.onTap,
    this.isLoading = false,
    this.changeColor = false,
    this.borderColor = CustomStyle.transparent,
  });

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: isLoading ? null : onTap,
      child: Container(
        width: double.infinity,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(radius.r),
          color: bgColor,
          border: Border.all(color: borderColor),
        ),
        padding: EdgeInsets.symmetric(vertical: 16.r),
        child: Center(
          child: isLoading
              ? SizedBox(
                  height: 22.r,
                  width: 20.r,
                  child: Loading(changeColor: changeColor),
                )
              : Text(title, style: CustomStyle.interNoSemi(color: titleColor)),
        ),
      ),
    );
  }
}
