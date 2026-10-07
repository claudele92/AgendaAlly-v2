import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/domain/model/model/blog_model.dart';
import 'package:demand/infrastructure/service/services.dart';

import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../../app_assets.dart';

class BlogTitle extends StatelessWidget {
  final CustomColorSet colors;
  final BlogData? blog;

  const BlogTitle({super.key, required this.blog, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          TimeService.dateFormatDMY(blog?.updatedAt),
          style: CustomStyle.interRegular(color: colors.textHint, size: 14),
        ),
        8.verticalSpace,
        Text(
          blog?.translation?.title ?? "",
          style: CustomStyle.interSemi(color: colors.textBlack, size: 22),
        ),
        12.verticalSpace,
        Row(
          children: [
            SvgPicture.asset(Assets.svgStart),
            4.horizontalSpace,
            Text(
              "${blog?.rate ?? 0}",
              style: CustomStyle.interNormal(color: colors.textBlack, size: 14),
            ),
            const Spacer(),
            ButtonEffectAnimation(
              onTap: () {
                AppRoute.goReviewPage(
                  context,
                  colors: colors,
                  blogId: blog?.id,
                );
              },
              child: Container(
                padding: EdgeInsets.symmetric(vertical: 11.r, horizontal: 14.r),
                decoration: BoxDecoration(
                  color: colors.textBlack,
                  borderRadius: BorderRadius.circular(100.r),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(FlutterRemix.message_3_fill, color: colors.textWhite),
                    10.horizontalSpace,
                    Text(
                      AppHelpers.getTranslation(TrKeys.comment),
                      style: CustomStyle.interNormal(
                        color: colors.textWhite,
                        size: 14,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
