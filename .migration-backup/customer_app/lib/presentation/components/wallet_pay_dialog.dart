import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../infrastructure/local_storage/local_storage.dart';
import '../../infrastructure/service/services.dart';
import 'components.dart';
import '../style/style.dart';
import '../style/theme/theme.dart';

class WalletPayDialog extends StatefulWidget {
  final CustomColorSet colors;
  final num? totalPrice;
  final ValueChanged<num?>? onChange;

  const WalletPayDialog({
    super.key,
    required this.colors,
    this.totalPrice,
    this.onChange,
  });

  @override
  State<WalletPayDialog> createState() => _WalletPayDialogState();
}

class _WalletPayDialogState extends State<WalletPayDialog> {
  TextEditingController controller = TextEditingController();
  num? maxPrice;

  @override
  void initState() {
    if ((widget.totalPrice ?? 1) >=
        (LocalStorage.getUser().wallet?.price ?? 1)) {
      maxPrice = LocalStorage.getUser().wallet?.price;
    } else {
      maxPrice = num.tryParse(widget.totalPrice?.toStringAsFixed(2) ?? '');
    }

    controller = TextEditingController(text: '${maxPrice ?? 1}');
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.youCanPayFullAmount),
          style: CustomStyle.interNormal(color: widget.colors.textBlack),
          textAlign: TextAlign.center,
        ),
        12.verticalSpace,
        Text(
          AppHelpers.getTranslation(TrKeys.wantToPartiallyWallet),
          style: CustomStyle.interRegular(
            color: widget.colors.textBlack,
            size: 14,
          ),
          textAlign: TextAlign.center,
        ),
        12.verticalSpace,
        // Text(
        //   AppHelpers.numberFormat(totalPrice),
        //   style: AppStyle.interNoSemi(color: colors.primary),
        //   textAlign: TextAlign.center,
        // ),
        CurrencyTextField(
          colors: widget.colors,
          controller: controller,
          onChange: (val) {
            if (val?.isNotEmpty ?? false) {
              if (num.parse(val!) >= (maxPrice ?? 1)) {
                controller.text = "${(maxPrice ?? 1)}";
              }
            }
          },
        ),
        12.verticalSpace,
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Expanded(
              child: Text(
                '${AppHelpers.getTranslation("remaining.wallet.balance")}: ',
                style: CustomStyle.interNormal(
                  color: widget.colors.textBlack,
                  size: 14,
                ),
                textAlign: TextAlign.start,
              ),
            ),
            Expanded(
              child: Text(
                AppHelpers.numberFormat(LocalStorage.getUser().wallet?.price),
                style: CustomStyle.interNoSemi(
                  color: widget.colors.textBlack,
                  size: 14,
                ),
                textAlign: TextAlign.end,
              ),
            ),
          ],
        ),
        12.verticalSpace,
        Row(
          children: [
            Expanded(
              child: SecondButton(
                title: TrKeys.no,
                bgColor: widget.colors.newBoxColor,
                titleColor: widget.colors.textBlack,
                onTap: () {
                  Navigator.pop(context);
                },
              ),
            ),
            12.horizontalSpace,
            Expanded(
              child: SecondButton(
                title: TrKeys.yesPay,
                bgColor: widget.colors.primary,
                titleColor: widget.colors.textWhite,
                onTap: () {
                  if ((num.tryParse(controller.text) ?? 0) < 1) {
                    AppHelpers.errorSnackBar(
                      context,
                      message: AppHelpers.getTranslation(TrKeys.minQty1),
                    );
                    return;
                  }
                  widget.onChange?.call(num.tryParse(controller.text));
                  Navigator.pop(context);
                },
              ),
            ),
          ],
        ),
      ],
    );
  }
}
