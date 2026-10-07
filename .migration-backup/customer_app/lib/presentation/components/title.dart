import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';

import 'button/animation_button_effect.dart';

class TitleWidget extends StatelessWidget {
  final String title;
  final String? subTitle;
  final VoidCallback? onTap;
  final Color titleColor;
  final bool isSale;

  const TitleWidget({
    super.key,
    required this.title,
    this.subTitle,
    this.onTap,
    required this.titleColor,
    this.isSale = false,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          flex: 2,
          child: Row(
            children: [
              Flexible(
                child: Padding(
                  padding: EdgeInsets.only(left: 16.r),
                  child: Text(
                    title,
                    style: CustomStyle.interNoSemi(
                      color: titleColor,
                      size: AppHelpers.getType() == 3 ? 18 : 22,
                    ),
                  ),
                ),
              ),
              if (isSale && AppHelpers.getType() != 3)
                Container(
                  margin: EdgeInsets.only(left: 8.r),
                  padding: EdgeInsets.symmetric(vertical: 4.r, horizontal: 8.r),
                  decoration: BoxDecoration(
                    color: CustomStyle.red,
                    borderRadius: BorderRadius.circular(100.r),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        FlutterRemix.percent_fill,
                        color: CustomStyle.white,
                        size: 14.r,
                      ),
                      4.horizontalSpace,
                      Text(
                        AppHelpers.getTranslation(TrKeys.sale.toUpperCase()),
                        style: CustomStyle.interNoSemi(
                          color: CustomStyle.white,
                          size: 10,
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          ),
        ),
        if (subTitle != null)
          Expanded(
            child: ButtonEffectAnimation(
              onTap: () {
                onTap?.call();
              },
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 4.r, horizontal: 16.r),
                child: Text(
                  subTitle ?? "",
                  style: CustomStyle.interNormal(
                    color: CustomStyle.seeAllColor,
                    size: 14,
                  ),
                  textAlign: TextAlign.end,
                ),
              ),
            ),
          ),
      ],
    );
  }
}
