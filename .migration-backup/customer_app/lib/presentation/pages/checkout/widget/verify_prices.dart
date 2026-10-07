import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/model/response/cart_calculate_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../../../application/checkout/checkout_bloc.dart';
import '../../../../infrastructure/local_storage/local_storage.dart';
import '../../../components/components.dart';

class VerifyPrices extends StatelessWidget {
  final CustomColorSet colors;
  final CartCalculate? cart;

  const VerifyPrices({super.key, required this.colors, required this.cart});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<CheckoutBloc, CheckoutState>(
      builder: (context, state) {
        return Column(
          children: [
            UseWalletWidget(
              colors: colors,
              isActive: (state.walletPrice ?? 0) != 0,
              totalPrice: state.walletPrice,
              onTap: () {
                if ((state.walletPrice ?? 0) != 0 ||
                    (LocalStorage.getUser().wallet?.price ?? 0) <= 0) {
                  context.read<CheckoutBloc>().add(
                    CheckoutEvent.setWalletPrice(
                      price: null,
                      totalPrice: cart?.totalPrice,
                    ),
                  );
                  return;
                }
                AppHelpers.showAlertDialog(
                  context,
                  colors: colors,
                  child: WalletPayDialog(
                    colors: colors,
                    totalPrice: cart?.totalPrice,
                    onChange: (price) {
                      context.read<CheckoutBloc>().add(
                        CheckoutEvent.setWalletPrice(
                          price: price,
                          totalPrice: cart?.totalPrice,
                        ),
                      );
                    },
                  ),
                );
              },
            ),
            PriceItem(
              title: TrKeys.productsSubtotal,
              price: cart?.price,
              colors: colors,
            ),
            PriceItem(
              title: TrKeys.totalTax,
              price: cart?.totalShopTax,
              colors: colors,
            ),
            PriceItem(
              title: TrKeys.serviceFee,
              price: cart?.serviceFee,
              colors: colors,
            ),
            PriceItem(
              title: TrKeys.deliveryTip,
              price: cart?.deliveryTip,
              colors: colors,
            ),
            PriceItem(
              title: TrKeys.deliveryFee,
              colors: colors,
              price: cart?.deliveryFee?.fold(
                0.0,
                (previousValue, element) =>
                    (previousValue ?? 0) + (element.price ?? 0),
              ),
            ),
            PriceItem(
              colors: colors,
              title: TrKeys.coupon,
              price: cart?.coupon?.fold(
                0.0,
                (previousValue, element) =>
                    (previousValue ?? 0) + (element.price ?? 0),
              ),
              discount: true,
            ),
            PriceItem(
              colors: colors,
              title: TrKeys.discount,
              price: cart?.totalDiscount,
              discount: true,
            ),
            ...?cart?.errors?.map(
              (e) => Padding(
                padding: REdgeInsets.symmetric(horizontal: 16),
                child: Text(
                  e.message ?? '',
                  style: CustomStyle.interRegular(
                    color: colors.error,
                    size: 14,
                  ),
                ),
              ),
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
                    style: CustomStyle.interBold(
                      color: colors.textBlack,
                      size: 14,
                    ),
                  ),
                  Text(
                    AppHelpers.numberFormat(cart?.totalPrice ?? 0),
                    style: CustomStyle.interBold(
                      color: colors.textBlack,
                      size: 14,
                    ),
                  ),
                ],
              ),
            ),
            PriceItem(
              colors: colors,
              title: TrKeys.wallet,
              price: state.walletPrice,
              discount: true,
            ),
            24.verticalSpace,
          ],
        );
      },
    );
  }
}
