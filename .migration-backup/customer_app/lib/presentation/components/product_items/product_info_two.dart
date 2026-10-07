import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/domain/model/model/product_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../app_assets.dart';

class ProductInfoTwo extends StatelessWidget {
  final CustomColorSet colors;
  final ProductData product;
  final double width;
  final List<Extras> listExtra;

  const ProductInfoTwo({
    super.key,
    required this.product,
    required this.colors,
    this.width = 100,
    required this.listExtra,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        10.verticalSpace,
        if (product.stocks?.isNotEmpty ?? false)
          AppHelpers.numberFormat(product.stocks?.first.price ?? 0).length < 9
              ? Row(
                  children: [
                    Text(
                      AppHelpers.numberFormat(
                        product.stocks?.first.totalPrice ?? 0,
                      ),
                      style: CustomStyle.interSemi(
                        color: colors.textBlack,
                        size: 16,
                      ),
                    ),
                    if (product.stocks?.first.discount != null)
                      Padding(
                        padding: EdgeInsets.only(left: 10.r),
                        child: Text(
                          AppHelpers.numberFormat(product.stocks?.first.price),
                          style: CustomStyle.interRegular(
                            color: CustomStyle.textHint,
                            size: 14,
                            textDecoration: TextDecoration.lineThrough,
                          ),
                        ),
                      ),
                  ],
                )
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      AppHelpers.numberFormat(
                        product.stocks?.first.totalPrice ?? 0,
                      ),
                      style: CustomStyle.interSemi(
                        color: colors.textBlack,
                        size: 16,
                      ),
                    ),
                    if (product.stocks?.first.discount != null)
                      Padding(
                        padding: EdgeInsets.only(right: 10.r),
                        child: Text(
                          AppHelpers.numberFormat(
                            product.stocks?.first.price ?? 0,
                          ),
                          style: CustomStyle.interRegular(
                            color: CustomStyle.textHint,
                            size: 14,
                            textDecoration: TextDecoration.lineThrough,
                          ),
                        ),
                      ),
                  ],
                ),
        if (product.stocks?.isEmpty ?? true)
          Text(
            AppHelpers.getTranslation(TrKeys.outOfStock),
            style: CustomStyle.interSemi(color: colors.textBlack, size: 18),
          ),
        4.verticalSpace,
        SizedBox(
          width: width.r,
          child: Text(
            product.translation?.title ?? "",
            style: CustomStyle.interNormal(color: colors.textBlack, size: 14),
            maxLines: 2,
          ),
        ),
        4.verticalSpace,
        Row(
          children: [
            SvgPicture.asset(Assets.svgStart),
            4.horizontalSpace,
            Text(
              product.ratingAvg?.toStringAsPrecision(2) ?? "0.0",
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 12),
            ),
            Container(
              margin: EdgeInsets.symmetric(horizontal: 4.r),
              width: 4.r,
              height: 4.r,
              decoration: BoxDecoration(
                color: colors.textHint,
                shape: BoxShape.circle,
              ),
            ),
            Text(
              "${product.ratingCount ?? 0} ${AppHelpers.getTranslation(TrKeys.reviews)}",
              style: CustomStyle.interRegular(color: colors.textHint, size: 12),
            ),
          ],
        ),
        4.verticalSpace,
        if (AppHelpers.getType() == 1)
          Padding(
            padding: EdgeInsets.only(top: 8.r),
            child: SizedBox(
              height: 20,
              child: ListView.builder(
                shrinkWrap: true,
                padding: EdgeInsets.zero,
                scrollDirection: Axis.horizontal,
                itemCount: listExtra.length,
                itemBuilder: (context, index) {
                  return AppHelpers.checkIfHex(listExtra[index].value)
                      ? Container(
                          margin: EdgeInsets.only(right: 6.r),
                          width: 16.r,
                          height: 16.r,
                          decoration: BoxDecoration(
                            color: Color(
                              int.parse(
                                '0xFF${listExtra[index].value?.substring(1, 7)}',
                              ),
                            ),
                            shape: BoxShape.circle,
                            border: Border.all(color: colors.textHint),
                          ),
                        )
                      : const SizedBox.shrink();
                },
              ),
            ),
          ),
      ],
    );
  }
}
