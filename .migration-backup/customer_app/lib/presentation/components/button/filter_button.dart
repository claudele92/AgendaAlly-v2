import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/svg.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:demand/presentation/app_assets.dart';

class FilterButton extends StatelessWidget {
  final CustomColorSet colors;
  final VoidCallback onTap;

  const FilterButton({super.key, required this.colors, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: () {
        onTap.call();
      },
      child: Container(
        width: 40.r,
        height: 40.r,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          border: Border.all(color: colors.icon),
        ),
        child: Center(
          child: SvgPicture.asset(
            Assets.svgFilter,
            colorFilter: ColorFilter.mode(colors.textHint, BlendMode.srcIn),
          ),
        ),
      ),
    );
  }
}
