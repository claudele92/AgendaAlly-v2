import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/model/response/banners_paginate_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class BannerBottomSheet extends StatelessWidget {
  final BannerData banner;
  final CustomColorSet colors;

  const BannerBottomSheet({
    super.key,
    required this.banner,
    required this.colors,
  });

  @override
  Widget build(BuildContext context) {
    return BlurWrap(
      radius: BorderRadius.only(
        topRight: Radius.circular(24.r),
        topLeft: Radius.circular(24.r),
      ),
      child: Container(
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.all(16.r),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CustomNetworkImage(
                url: (banner.galleries?.first.path ?? ""),
                preview: banner.galleries?.first.preview,
                height: 180,
                width: double.infinity,
                radius: 24,
              ),
              16.verticalSpace,
              Text(
                banner.translation?.description ?? "",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 18,
                ),
              ),
              16.verticalSpace,
              Text(
                banner.translation?.description ?? "",
                style: CustomStyle.interRegular(
                  color: colors.textBlack,
                  size: 16,
                ),
              ),
              24.verticalSpace,
              CustomButton(
                title: AppHelpers.getTranslation(TrKeys.viewProducts),
                bgColor: CustomStyle.black,
                titleColor: CustomStyle.white,
                onTap: () {
                  AppRoute.goProductList(
                    context,
                    title: banner.translation?.description ?? "",
                    bannerId: banner.id,
                  );
                },
              ),
              16.verticalSpace,
            ],
          ),
        ),
      ),
    );
  }
}
