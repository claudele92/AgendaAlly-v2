import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/domain/model/response/cart_calculate_response.dart';
import 'package:demand/domain/model/response/product_calculate_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class ChequeWidget extends StatelessWidget {
  final CustomColorSet colors;
  final CartCalculate? cart;
  final ProductCalculateResponseData? productCalculate;
  final bool fullDigital;
  final bool ready;
  final bool group;

  const ChequeWidget({
    super.key,
    required this.colors,
    required this.cart,
    this.productCalculate,
    required this.fullDigital,
    this.ready = false,
    this.group = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: colors.newBoxColor,
      width: double.infinity,
      padding: EdgeInsets.symmetric(horizontal: 16.r),
      child: Column(
        children: [
          Container(
            decoration: BoxDecoration(
              color: colors.backgroundColor,
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(24.r),
                bottomRight: Radius.circular(24.r),
              ),
            ),
            child: Theme(
              data: Theme.of(context).copyWith(
                dividerColor: CustomStyle.transparent,
                primaryColor: colors.textBlack,
                unselectedWidgetColor: colors.textBlack,
                colorScheme: Theme.of(context).colorScheme.copyWith(
                  secondary: colors.textBlack,
                  primary: colors.textBlack,
                ),
              ),
              child: ExpansionTile(
                title: Text(
                  AppHelpers.getTranslation(TrKeys.cheque),
                  style: CustomStyle.interRegular(
                    color: colors.textBlack,
                    size: 14,
                  ),
                ),
                children: [
                  16.verticalSpace,
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          AppHelpers.getTranslation(TrKeys.productsSubtotal),
                          style: CustomStyle.interRegular(
                            color: colors.textBlack,
                            size: 14,
                          ),
                        ),
                        Text(
                          AppHelpers.numberFormat(
                            LocalStorage.getToken().isNotEmpty
                                ? cart?.price
                                : productCalculate?.price,
                          ),
                          style: CustomStyle.interRegular(
                            color: colors.textBlack,
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
                  16.verticalSpace,
                  if (cart?.totalShopTax != 0 &&
                      cart?.totalShopTax != null &&
                      LocalStorage.getToken().isNotEmpty)
                    _priceItem(
                      title: AppHelpers.getTranslation(TrKeys.totalTax),
                      price: cart?.totalShopTax,
                    ),
                  if (productCalculate?.totalTax != 0 &&
                      productCalculate?.totalTax != null &&
                      LocalStorage.getToken().isEmpty)
                    _priceItem(
                      title: AppHelpers.getTranslation(TrKeys.totalTax),
                      price: productCalculate?.totalTax,
                    ),
                  if (cart?.serviceFee != 0 &&
                      cart?.serviceFee != null &&
                      LocalStorage.getToken().isNotEmpty)
                    _priceItem(
                      title: AppHelpers.getTranslation(TrKeys.serviceFee),
                      price: cart?.serviceFee,
                    ),
                  if (productCalculate?.serviceFee != 0 &&
                      productCalculate?.serviceFee != null &&
                      LocalStorage.getToken().isEmpty)
                    _priceItem(
                      title: AppHelpers.getTranslation(TrKeys.serviceFee),
                      price: productCalculate?.serviceFee,
                    ),
                  if (cart?.totalDiscount != 0 &&
                      cart?.totalDiscount != null &&
                      LocalStorage.getToken().isNotEmpty)
                    _priceItem(
                      title: AppHelpers.getTranslation(TrKeys.discount),
                      price: cart?.totalDiscount,
                      discount: true,
                    ),
                  if (productCalculate?.totalDiscount != 0 &&
                      productCalculate?.totalDiscount != null &&
                      LocalStorage.getToken().isEmpty)
                    _priceItem(
                      title: AppHelpers.getTranslation(TrKeys.discount),
                      price: productCalculate?.totalDiscount,
                      discount: true,
                    ),
                  if (cart?.coupon?.fold(
                            0.0,
                            (previousValue, element) =>
                                previousValue + (element.price ?? 0),
                          ) !=
                          0 &&
                      cart?.coupon?.fold(
                            0.0,
                            (previousValue, element) =>
                                previousValue + (element.price ?? 0),
                          ) !=
                          null &&
                      LocalStorage.getToken().isNotEmpty)
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16.r),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            AppHelpers.getTranslation(TrKeys.coupon),
                            style: CustomStyle.interRegular(
                              color: colors.textBlack,
                              size: 14,
                            ),
                          ),
                          Text(
                            "-${AppHelpers.numberFormat(cart?.coupon?.fold(0.0, (previousValue, element) => (previousValue ?? 0) + (element.price ?? 0)))}",
                            style: CustomStyle.interRegular(
                              color: colors.error,
                              size: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                  16.verticalSpace,
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: Divider(color: colors.textBlack),
                  ),
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: Divider(color: colors.textBlack),
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
                          AppHelpers.numberFormat(
                            LocalStorage.getToken().isNotEmpty
                                ? cart?.totalPrice
                                : productCalculate?.totalPrice,
                          ),
                          style: CustomStyle.interBold(
                            color: colors.textBlack,
                            size: 14,
                          ),
                        ),
                      ],
                    ),
                  ),
                  24.verticalSpace,
                ],
              ),
            ),
          ),
          24.verticalSpace,
          CustomButton(
            title: LocalStorage.getGroupOrder().id == null
                ? "${AppHelpers.getTranslation(TrKeys.checkout)} - ${AppHelpers.getTranslation(TrKeys.total)} ${AppHelpers.numberFormat(LocalStorage.getToken().isNotEmpty ? cart?.totalPrice : productCalculate?.totalPrice)}"
                : ready
                ? AppHelpers.getTranslation(TrKeys.cancel)
                : AppHelpers.getTranslation(TrKeys.ready),
            bgColor: colors.primary,
            titleColor: colors.white,
            onTap: () {
              if (LocalStorage.getGroupOrder().id != null) {
                context.read<CartBloc>().add(
                  CartEvent.changeReady(
                    context,
                    uuid: LocalStorage.getGroupOrder().uuid ?? "",
                    onSuccess: () {
                      context.read<CartBloc>().add(
                        CartEvent.insertCart(context, onSuccess: () {}),
                      );
                    },
                  ),
                );
                return;
              }
              if (AppHelpers.getMinAmount() *
                      (LocalStorage.getSelectedCurrency()?.rate ?? 0) >
                  ((LocalStorage.getToken().isNotEmpty
                          ? cart?.price
                          : productCalculate?.price) ??
                      0)) {
                AppHelpers.openDialog(
                  context,
                  title:
                      "${AppHelpers.numberFormat(AppHelpers.getMinAmount() * (LocalStorage.getSelectedCurrency()?.rate ?? 0) - ((LocalStorage.getToken().isNotEmpty ? cart?.price : productCalculate?.price) ?? 0))} ${AppHelpers.getTranslation(TrKeys.enoughForMinAmount)}",
                );
                return;
              }
              if (LocalStorage.getToken().isEmpty) {
                AppRoute.goLogin(context);
                return;
              }
              if (group) {
                AppHelpers.showCustomDialog(
                  context,
                  content: _checkMemberStatus(context),
                );
                return;
              }

              AppRoute.goCheckoutPage(context, fullDigital: fullDigital);
            },
          ),
        ],
      ),
    );
  }

  Widget _checkMemberStatus(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(16.r),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(AppConstants.radius.r),
        color: !LocalStorage.getMode() ? CustomStyle.bgDark : CustomStyle.white,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            AppHelpers.getTranslation(TrKeys.groupOrderProgress),
            style: CustomStyle.interNormal(size: 16, color: colors.textBlack),
            textAlign: TextAlign.center,
          ),
          36.verticalSpace,
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: CustomButton(
                  title: AppHelpers.getTranslation(TrKeys.cancel),
                  onTap: () {
                    Navigator.pop(context);
                  },
                  bgColor: colors.textBlack,
                  titleColor: colors.textWhite,
                ),
              ),
              16.horizontalSpace,
              Expanded(
                child: CustomButton(
                  title: AppHelpers.getTranslation(TrKeys.continueText),
                  onTap: () {
                    AppRoute.goCheckoutPage(context, fullDigital: fullDigital);
                  },
                  bgColor: colors.primary,
                  titleColor: colors.white,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Column _priceItem({
    required String title,
    required num? price,
    bool discount = false,
  }) {
    return Column(
      children: [
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                title,
                style: CustomStyle.interRegular(
                  color: colors.textBlack,
                  size: 14,
                ),
              ),
              discount
                  ? Text(
                      "-${AppHelpers.numberFormat(price)}",
                      style: CustomStyle.interRegular(
                        color: colors.error,
                        size: 14,
                      ),
                    )
                  : Text(
                      AppHelpers.numberFormat(price),
                      style: CustomStyle.interRegular(
                        color: colors.textBlack,
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
        16.verticalSpace,
      ],
    );
  }
}
