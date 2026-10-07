import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/model/model/order_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import 'order_transaction_item.dart';

class PriceInfo extends StatelessWidget {
  final CustomColorSet colors;
  final OrderShops? order;

  const PriceInfo({super.key, required this.colors, required this.order});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        priceItem(title: TrKeys.productsSubtotal, price: order?.originPrice),
        priceItem(title: TrKeys.serviceFee, price: order?.serviceFee),
        priceItem(title: TrKeys.deliveryTip, price: order?.tips),
        priceItem(title: TrKeys.deliveryFee, price: order?.deliveryFee),
        priceItem(title: TrKeys.totalTax, price: order?.tax),
        priceItem(
          title: TrKeys.coupon,
          price: order?.totalCouponPrice,
          discount: true,
        ),
        priceItem(
          title: TrKeys.discount,
          price: order?.totalDiscount,
          discount: true,
        ),
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Divider(color: colors.textHint),
        ),
        16.verticalSpace,
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppHelpers.getTranslation(TrKeys.total),
                style: CustomStyle.interBold(color: colors.textBlack, size: 14),
              ),
              Text(
                AppHelpers.numberFormat(
                  (order?.totalPrice ?? 0) > 0 ? order?.totalPrice : 0,
                ),
                style: CustomStyle.interBold(color: colors.textBlack, size: 14),
              ),
            ],
          ),
        ),
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Divider(color: colors.textHint),
        ),
        ...?order?.transactions?.map(
          (e) => Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.r),
            child: OrderTransactionItem(
              icon: Icon(
                e.paymentSystem?.tag == 'wallet'
                    ? FlutterRemix.wallet_3_fill
                    : e.paymentSystem?.tag == 'cash'
                    ? FlutterRemix.money_dollar_box_fill
                    : FlutterRemix.bank_card_2_line,
                color: colors.textBlack,
              ),
              colors: colors,
              title:
                  "${e.paymentSystem?.tag} (${AppHelpers.getTranslation(e.status ?? '')})",
              price: e.price,
            ),
          ),
        ),
        24.verticalSpace,
      ],
    );
  }

  Widget priceItem({
    required num? price,
    required String title,
    bool discount = false,
  }) {
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
                        color: discount ? colors.primary : colors.textBlack,
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
