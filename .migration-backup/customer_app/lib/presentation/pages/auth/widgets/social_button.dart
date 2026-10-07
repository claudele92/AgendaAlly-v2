import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';

class SocialButton extends StatelessWidget {
  final Color bgColor;
  final Color iconColor;
  final IconData icon;
  final VoidCallback onTap;

  const SocialButton({
    super.key,
    required this.bgColor,
    required this.icon,
    required this.onTap,
    required this.iconColor,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 100.r,
      child: ButtonEffectAnimation(
        onTap: onTap,
        child: Container(
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(10.r),
            border: Border.all(color: CustomStyle.icon),
          ),
          padding: EdgeInsets.all(10.r),
          child: Icon(icon, color: CustomStyle.white),
        ),
      ),
    );
  }
}
