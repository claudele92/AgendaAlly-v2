import 'package:auto_size_text/auto_size_text.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class BottomOneItem extends StatelessWidget {
  final bool isActive;
  final int bagCount;
  final String icon;
  final String name;
  final CustomColorSet colors;
  final VoidCallback onTap;

  const BottomOneItem({
    super.key,
    required this.isActive,
    required this.icon,
    required this.onTap,
    this.bagCount = -1,
    required this.name,
    required this.colors,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: ButtonEffectAnimation(
        onTap: onTap,
        child: Column(
          children: [
            Badge(
              label: Text(bagCount.toString()),
              isLabelVisible: bagCount != -1,
              child: SvgPicture.asset(
                icon,
                height: 25.r,
                colorFilter: ColorFilter.mode(
                  isActive ? colors.primary : colors.textHint,
                  BlendMode.srcIn,
                ),
              ),
            ),
            AutoSizeText(
              name,
              style: CustomStyle.interNormal(
                color: isActive ? colors.primary : colors.textHint,
                size: 12,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
