import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';

class SecondButton extends StatelessWidget {
  final String title;
  final double radius;
  final Color bgColor;
  final Color? borderColor;
  final Color titleColor;
  final double titleSize;
  final VoidCallback onTap;

  const SecondButton({
    super.key,
    required this.title,
    this.radius = 36,
    required this.bgColor,
    this.borderColor,
    required this.titleColor,
    required this.onTap,
    this.titleSize = 14,
  });

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(radius.r),
          color: bgColor,
          border: borderColor != null ? Border.all(color: borderColor!) : null,
        ),
        padding: EdgeInsets.symmetric(vertical: 10.r, horizontal: 18.r),
        alignment: Alignment.center,
        child: Text(
          title,
          style: CustomStyle.interNoSemi(color: titleColor, size: titleSize),
        ),
      ),
    );
  }
}
