import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class DescriptionScreen extends StatelessWidget {
  final ScrollController? controller;
  final CustomColorSet colors;
  final String? description;

  const DescriptionScreen({
    super.key,
    required this.colors,
    required this.description,
    this.controller,
  });

  @override
  Widget build(BuildContext context) {
    return BlurWrap(
      radius: BorderRadius.only(
        topRight: Radius.circular(24.r),
        topLeft: Radius.circular(24.r),
      ),
      child: Container(
        width: double.infinity,
        decoration: BoxDecoration(
          color: colors.backgroundColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.symmetric(horizontal: 16.r),
        child: controller == null
            ? Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  24.verticalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.description),
                    style: CustomStyle.interSemi(
                      color: colors.textBlack,
                      size: 22,
                    ),
                  ),
                  16.verticalSpace,
                  Html(
                    data: description ?? "",
                    style: {
                      "body": Style(
                        color: colors.textBlack,
                        fontSize: FontSize(16),
                      ),
                    },
                  ),
                  32.verticalSpace,
                ],
              )
            : ListView(
                padding: EdgeInsets.zero,
                controller: controller,
                shrinkWrap: true,
                children: [
                  24.verticalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.description),
                    style: CustomStyle.interSemi(
                      color: colors.textBlack,
                      size: 22,
                    ),
                  ),
                  16.verticalSpace,
                  Html(
                    data: description ?? "",
                    style: {
                      "body": Style(
                        color: colors.textBlack,
                        fontSize: FontSize(16),
                      ),
                    },
                  ),
                  32.verticalSpace,
                ],
              ),
      ),
    );
  }
}
