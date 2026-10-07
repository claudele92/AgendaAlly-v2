import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class StepScreen extends StatelessWidget {
  final CustomColorSet colors;
  final int step;

  const StepScreen({super.key, required this.colors, required this.step});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Container(
          padding: EdgeInsets.all(20.r),
          decoration: const BoxDecoration(
            shape: BoxShape.circle,
            color: CustomStyle.greenColor,
          ),
          child: Icon(
            step == 1
                ? Icons.location_on_rounded
                : step == 2
                ? FlutterRemix.truck_fill
                : step == 3
                ? FlutterRemix.shield_check_fill
                : FlutterRemix.bank_card_fill,
            color: CustomStyle.white,
          ),
        ),
        16.verticalSpace,
        Text(
          AppHelpers.getTranslation(
            step == 1
                ? TrKeys.shipping
                : step == 2
                ? TrKeys.deliveryTip
                : step == 3
                ? TrKeys.verify
                : TrKeys.payment,
          ),
          style: CustomStyle.interBold(color: colors.textBlack, size: 16),
        ),
        2.verticalSpace,
        Text(
          AppHelpers.getTranslation(
            step == 1
                ? TrKeys.nextStepDeliveryTip
                : step == 2
                ? TrKeys.nextStepVerify
                : step == 3
                ? TrKeys.nextStepPayment
                : TrKeys.lastStep,
          ),
          style: CustomStyle.interNormal(color: colors.textBlack, size: 12),
        ),
      ],
    );
  }
}
