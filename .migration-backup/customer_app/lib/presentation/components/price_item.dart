import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class PriceItem extends StatelessWidget {
  final num? price;
  final String title;
  final bool discount;
  final CustomColorSet colors;

  const PriceItem({
    super.key,
    this.price,
    required this.title,
    this.discount = false,
    required this.colors,
  });

  @override
  Widget build(BuildContext context) {
    return price == 0 || price == null
        ? const SizedBox.shrink()
        : Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              16.verticalSpace,
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 16.r),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      AppHelpers.getTranslation(title),
                      style: CustomStyle.interRegular(
                        color: colors.textBlack,
                        size: 14,
                      ),
                    ),
                    Text(
                      discount
                          ? "-${AppHelpers.numberFormat(price)} "
                          : AppHelpers.numberFormat(price),
                      style: CustomStyle.interRegular(
                        color: discount ? colors.error : colors.textBlack,
                        size: 14,
                      ),
                    ),
                  ],
                ),
              ),
              16.verticalSpace,
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 16.r),
                child: const Divider(color: CustomStyle.textHint),
              ),
            ],
          );
  }
}
