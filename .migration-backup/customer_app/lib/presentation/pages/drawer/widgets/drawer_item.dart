import 'package:demand/infrastructure/service/helper.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class DrawerItem extends StatelessWidget {
  final CustomColorSet colors;
  final IconData icon;
  final String title;
  final VoidCallback onTap;

  const DrawerItem({
    super.key,
    required this.colors,
    required this.icon,
    required this.title,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: onTap,
      child: Column(
        children: [
          16.verticalSpace,
          Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.r),
            child: Row(
              children: [
                Icon(icon, color: colors.textBlack),
                12.horizontalSpace,
                Expanded(
                  child: Text(
                    AppHelpers.getTranslation(title),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 16,
                    ),
                  ),
                ),
              ],
            ),
          ),
          8.verticalSpace,
          Divider(color: colors.textHint),
        ],
      ),
    );
  }
}
