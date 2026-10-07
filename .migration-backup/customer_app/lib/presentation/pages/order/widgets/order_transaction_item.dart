import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../../../infrastructure/service/helper.dart';
import '../../../style/style.dart';
import '../../../style/theme/theme.dart';

class OrderTransactionItem extends StatelessWidget {
  final Widget icon;
  final String title;
  final num? price;
  final CustomColorSet colors;

  const OrderTransactionItem({
    super.key,
    required this.icon,
    required this.title,
    required this.colors,
    this.price,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 8.r),
      child: Row(
        children: [
          icon,
          8.horizontalSpace,
          Expanded(
            child: Text(
              AppHelpers.getTranslation(title),
              style: CustomStyle.interNormal(size: 14, color: colors.textBlack),
            ),
          ),
          if (price != null)
            Text(
              AppHelpers.numberFormat(price),
              style: CustomStyle.interNormal(size: 14, color: colors.textBlack),
            ),
        ],
      ),
    );
  }
}
