import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/model/response/payments_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class PaymentMethodsScreen extends StatelessWidget {
  final CustomColorSet colors;
  final List<PaymentData> payments;
  final PaymentData selectedPayment;
  final Function(PaymentData) select;
  final bool parcel;

  const PaymentMethodsScreen({
    super.key,
    required this.colors,
    required this.payments,
    required this.selectedPayment,
    required this.select,
    this.parcel = false,
  });

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      padding: EdgeInsets.only(
        top: 20.r,
        left: 16.r,
        right: 16.r,
        bottom: parcel ? 0 : 100.r,
      ),
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.paymentMethod),
          style: CustomStyle.interSemi(color: colors.textBlack, size: 16),
        ),
        16.verticalSpace,
        ListView.builder(
          padding: EdgeInsets.zero,
          itemCount: payments.length,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemBuilder: (context, index) {
            return InkWell(
              onTap: () {
                select.call(payments[index]);
              },
              child: Column(
                children: [
                  8.verticalSpace,
                  Row(
                    children: [
                      Icon(
                        payments[index].id == selectedPayment.id
                            ? FlutterRemix.checkbox_circle_fill
                            : FlutterRemix.checkbox_blank_circle_line,
                        color: payments[index].id == selectedPayment.id
                            ? colors.primary
                            : colors.textBlack,
                      ),
                      10.horizontalSpace,
                      Text(
                        AppHelpers.getTranslation(payments[index].tag ?? ""),
                        style: CustomStyle.interNormal(
                          size: 14,
                          color: colors.textBlack,
                        ),
                      ),
                    ],
                  ),
                  Divider(color: colors.newBoxColor),
                  8.verticalSpace,
                ],
              ),
            );
          },
        ),
      ],
    );
  }
}
