import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/model/response/transaction_histories_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class TransactionItem extends StatelessWidget {
  final CustomColorSet colors;
  final TransactionModel transaction;

  const TransactionItem({
    super.key,
    required this.colors,
    required this.transaction,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.only(bottom: 8.r, left: 16.r, right: 16.r),
      width: double.infinity,
      padding: EdgeInsets.symmetric(vertical: 20.r, horizontal: 22.r),
      decoration: BoxDecoration(
        color: colors.newBoxColor,
        borderRadius: BorderRadius.circular(10.r),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                TimeService.dateFormatForNotification(transaction.createdAt),
                style: CustomStyle.interNormal(
                  color: colors.textHint,
                  size: 12,
                ),
              ),
            ],
          ),
          16.verticalSpace,
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppHelpers.getTranslation(TrKeys.sender),
                style: CustomStyle.interRegular(
                  size: 16,
                  color: colors.textBlack,
                ),
              ),
              Text(
                transaction.author?.firstname ?? "",
                style: CustomStyle.interRegular(
                  size: 16,
                  color: colors.textBlack,
                ),
              ),
            ],
          ),
          16.verticalSpace,
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppHelpers.getTranslation(TrKeys.note),
                style: CustomStyle.interRegular(
                  size: 16,
                  color: colors.textBlack,
                ),
              ),
              Text(
                transaction.note ?? "",
                style: CustomStyle.interRegular(
                  size: 16,
                  color: colors.textBlack,
                ),
              ),
            ],
          ),
          16.verticalSpace,
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppHelpers.getTranslation(
                  transaction.transactionData?.paymentSystem?.tag ??
                      TrKeys.price,
                ),
                style: CustomStyle.interRegular(
                  size: 16,
                  color: colors.textBlack,
                ),
              ),
              Text(
                transaction.type == "withdraw"
                    ? "-${AppHelpers.numberFormat(transaction.price)}"
                    : AppHelpers.numberFormat(transaction.price),
                style: CustomStyle.interRegular(
                  size: 16,
                  color: colors.textBlack,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
