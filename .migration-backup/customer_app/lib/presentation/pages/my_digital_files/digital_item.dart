import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/domain/model/response/digital_list_response.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class DigitalItem extends StatelessWidget {
  final DigitalProduct product;
  final CustomColorSet colors;
  final VoidCallback download;
  final int? progress;

  const DigitalItem({
    super.key,
    required this.product,
    required this.colors,
    required this.download,
    required this.progress,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: 24.r),
      child: InkWell(
        onTap: () {
          AppRoute.goProductPage(
            context,
            product: product.digitalFile?.product,
          );
        },
        child: Row(
          children: [
            CustomNetworkImage(
              url: product.digitalFile?.product?.galleries?.first.path ?? "",
              preview: product.digitalFile?.product?.galleries?.first.preview,
              height: 120,
              width: 90,
              radius: 16,
            ),
            16.horizontalSpace,
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.digitalFile?.product?.translation?.title ?? "",
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 14,
                    ),
                  ),
                  if (product.digitalFile?.product?.stocks?.first.discount !=
                      null)
                    Padding(
                      padding: EdgeInsets.only(top: 4.r),
                      child: Text(
                        AppHelpers.numberFormat(
                          product.digitalFile?.product?.stocks?.first.price ??
                              0,
                        ),
                        style: CustomStyle.interNoSemi(
                          color: CustomStyle.red,
                          size: 14,
                          decoration: TextDecoration.lineThrough,
                        ),
                      ),
                    ),
                  4.verticalSpace,
                  Text(
                    AppHelpers.numberFormat(
                      product.digitalFile?.product?.stocks?.first.totalPrice ??
                          0,
                    ),
                    style: CustomStyle.interSemi(
                      color: colors.textBlack,
                      size: 16,
                    ),
                  ),
                  16.verticalSpace,
                  progress == null
                      ? ButtonEffectAnimation(
                          onTap: download,
                          child: Container(
                            padding: EdgeInsets.all(10.r),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: colors.transparent,
                              border: Border.all(color: colors.icon),
                            ),
                            child: Icon(
                              FlutterRemix.download_2_fill,
                              color: colors.textBlack,
                            ),
                          ),
                        )
                      : Text(
                          "$progress%",
                          style: CustomStyle.interNormal(
                            color: colors.textBlack,
                            size: 16,
                          ),
                        ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
