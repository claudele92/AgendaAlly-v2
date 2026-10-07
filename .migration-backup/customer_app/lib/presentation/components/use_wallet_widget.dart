import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../infrastructure/local_storage/local_storage.dart';
import '../../infrastructure/service/services.dart';
import '../app_assets.dart';
import 'components.dart';
import '../style/style.dart';
import '../style/theme/theme.dart';

class UseWalletWidget extends StatelessWidget {
  final VoidCallback onTap;
  final CustomColorSet colors;
  final bool isActive;
  final num? totalPrice;

  const UseWalletWidget({
    super.key,
    required this.onTap,
    required this.colors,
    required this.isActive,
    this.totalPrice,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: REdgeInsets.only(top: 4),
      child: ButtonEffectAnimation(
        onTap: onTap,
        child: Container(
          width: double.infinity,
          padding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            border: Border.all(
              color: isActive ? colors.primary : colors.icon,
              width: .6.r,
            ),
            borderRadius: BorderRadius.circular(12.r),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Image.asset(Assets.imagesWallet, height: 24.r, width: 24.r),
                  8.horizontalSpace,
                  Text(
                    AppHelpers.numberFormat(
                      isActive
                          ? totalPrice
                          : LocalStorage.getUser().wallet?.price,
                    ),
                    style: CustomStyle.interNoSemi(
                      size: 18,
                      color: colors.textBlack,
                    ),
                  ),
                ],
              ),
              6.verticalSpace,
              Text(
                AppHelpers.getTranslation(
                  isActive ? TrKeys.hasPaidWallet : TrKeys.yourBalance,
                ),
                style: CustomStyle.interRegular(
                  size: 14,
                  color: colors.textBlack,
                ),
              ),
              6.verticalSpace,
              ((totalPrice ?? 0) >
                          (LocalStorage.getUser().wallet?.price ?? 0)) ||
                      (LocalStorage.getUser().wallet?.price ?? 0) <= 0
                  ? Text(
                      AppHelpers.getTranslation(TrKeys.youDontHaveBalance),
                      style: CustomStyle.interRegular(
                        size: 14,
                        color: colors.success,
                      ),
                    )
                  : Row(
                      children: [
                        Expanded(
                          child: Text(
                            AppHelpers.getTranslation(
                              isActive ? TrKeys.applied : TrKeys.doYouWant,
                            ),
                            style: CustomStyle.interRegular(
                              size: 14,
                              color: colors.textBlack,
                            ),
                          ),
                        ),
                        SecondButton(
                          title: isActive ? TrKeys.remove : TrKeys.use,
                          bgColor: isActive
                              ? colors.transparent
                              : colors.primary,
                          borderColor: isActive ? colors.error : colors.primary,
                          titleColor: isActive ? colors.error : colors.white,
                          onTap: onTap,
                        ),
                      ],
                    ),
            ],
          ),
        ),
      ),
    );
  }
}
