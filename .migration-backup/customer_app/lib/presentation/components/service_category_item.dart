import 'package:demand/app_constants.dart';
import 'package:demand/presentation/components/button/animation_button_effect.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/material.dart';
import 'package:demand/domain/model/models.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import 'custom_network_image.dart';

class ServiceCategoryItem extends StatelessWidget {
  final CategoryData category;
  final CategoryData? selectedCategory;
  final bool select;
  final CustomColorSet colors;
  final VoidCallback onTapParent;
  final ValueChanged<CategoryData?> onTapChild;

  const ServiceCategoryItem({
    super.key,
    required this.category,
    required this.selectedCategory,
    required this.select,
    required this.colors,
    required this.onTapParent,
    required this.onTapChild,
  });

  @override
  Widget build(BuildContext context) {
    if (category.children?.isEmpty ?? true) {
      return ButtonEffectAnimation(
        onTap: onTapParent,
        child: Container(
          margin: EdgeInsets.only(right: 10.r),
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          height: 40.r,
          decoration: BoxDecoration(
            color: select ? colors.textBlack : colors.transparent,
            border: Border.all(color: colors.textBlack),
            borderRadius: BorderRadius.circular(10.r),
          ),
          child: Row(
            children: [
              CustomNetworkImage(url: category.img, height: 20, width: 20),
              10.horizontalSpace,
              Text(
                category.translation?.title ?? '',
                style: CustomStyle.interRegular(
                  color: select ? colors.textWhite : colors.textBlack,
                  size: 16,
                ),
              ),
            ],
          ),
        ),
      );
    }
    int titleLength =
        ((select ? selectedCategory : category)?.translation?.title?.length ??
        1);
    return Padding(
      padding: REdgeInsets.only(right: 12),
      child: Container(
        width: titleLength * 8 + 90.r,
        height: 44.r,
        decoration: BoxDecoration(
          color: select ? colors.textBlack : colors.transparent,
          border: Border.all(color: colors.textBlack),
          borderRadius: BorderRadius.circular(10.r),
        ),
        child: DropdownButton<CategoryData>(
          value: select ? selectedCategory : null,
          isExpanded: true,
          padding: EdgeInsets.symmetric(horizontal: 12.r),
          style: CustomStyle.interRegular(
            color: select ? colors.textWhite : colors.textBlack,
            size: 16,
          ),
          hint: _item(category),
          dropdownColor: select ? colors.textBlack : colors.textWhite,
          borderRadius: BorderRadius.circular(AppConstants.radius),
          underline: SizedBox.shrink(),
          // decoration: InputDecoration(
          //   border: InputBorder.none,
          //   contentPadding: EdgeInsets.zero,
          // ),
          items: [
            DropdownMenuItem<CategoryData>(
              value: category,
              child: _item(category),
            ),
            ...category.children!.map((child) {
              return DropdownMenuItem<CategoryData>(
                value: child,
                child: _item(child),
              );
            }),
          ],
          onChanged: onTapChild,
        ),
      ),
    );
  }

  Row _item(CategoryData category) {
    return Row(
      children: [
        CustomNetworkImage(url: category.img, height: 20, width: 20),
        10.horizontalSpace,
        Expanded(
          child: Text(
            category.translation?.title ?? '-',
            style: CustomStyle.interRegular(
              color: select ? colors.textWhite : colors.textBlack,
              size: 16,
            ),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}
