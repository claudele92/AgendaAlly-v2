import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../components.dart';

class CustomIconButton extends StatelessWidget {
  final VoidCallback? onTap;
  final IconData? icon;
  final CustomColorSet colors;
  final double size;
  final Color? iconColor;

  const CustomIconButton({
    super.key,
    required this.onTap,
    required this.icon,
    required this.colors,
    this.size = 38,
    this.iconColor,
  });

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: onTap,
      child: Container(
        height: size.r,
        width: size.r,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: colors.textWhite,
          boxShadow: [
            BoxShadow(
              color: colors.textBlack.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Icon(
          icon,
          size: size.r / 2,
          color: iconColor ?? colors.textBlack,
        ),
      ),
    );
  }
}
