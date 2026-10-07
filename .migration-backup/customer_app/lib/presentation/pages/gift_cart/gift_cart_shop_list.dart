import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../../app_constants.dart';
import '../../../application/gift_cart/gift_cart_bloc.dart';
import '../../../domain/model/models.dart';
import '../../../infrastructure/service/services.dart';
import '../../components/components.dart';
import '../../route/app_route_shop.dart';
import '../../style/style.dart';
import '../../style/theme/theme.dart';

class GiftCartShopList extends StatefulWidget {
  final CustomColorSet colors;
  final int shopId;

  const GiftCartShopList({
    super.key,
    required this.colors,
    required this.shopId,
  });

  @override
  State<GiftCartShopList> createState() => _GiftCartShopListState();
}

class _GiftCartShopListState extends State<GiftCartShopList> {
  late RefreshController controller;

  @override
  void initState() {
    controller = RefreshController();
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<GiftCartBloc, GiftCartState>(
      builder: (context, state) {
        return state.giftCarts.isEmpty
            ? const SizedBox.shrink()
            : state.isLoading
            ? const Loading()
            : Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  32.verticalSpace,
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.giftCarts),
                    titleColor: widget.colors.textBlack,
                  ),
                  16.verticalSpace,
                  SizedBox(
                    height: 180.r,
                    child: SmartRefresher(
                      controller: controller,
                      scrollDirection: Axis.horizontal,
                      enablePullDown: false,
                      enablePullUp: true,
                      onLoading: () {
                        context.read<GiftCartBloc>().add(
                          GiftCartEvent.fetchGiftCart(
                            context,
                            shopId: widget.shopId,
                            controller: controller,
                          ),
                        );
                      },
                      child: ListView.builder(
                        padding: EdgeInsets.symmetric(horizontal: 16.r),
                        shrinkWrap: true,
                        scrollDirection: Axis.horizontal,
                        itemCount: state.giftCarts.length,
                        itemBuilder: (context, index) {
                          return _giftCartItem(
                            context,
                            giftCart: state.giftCarts[index],
                            index: index % 11,
                          );
                        },
                      ),
                    ),
                  ),
                ],
              );
      },
    );
  }

  Widget _giftCartItem(
    BuildContext context, {
    required GiftCartModel giftCart,
    required int index,
  }) {
    return Padding(
      padding: EdgeInsets.only(right: 8.r),
      child: Container(
        width: 240.r,
        decoration: BoxDecoration(
          border: Border.all(color: widget.colors.icon),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.start,
          children: [
            Container(
              height: 100.r,
              width: double.infinity,
              decoration: BoxDecoration(
                color: ListConstants.serviceColors[index],
                borderRadius: BorderRadius.only(
                  topRight: Radius.circular(AppConstants.radius.r),
                  topLeft: Radius.circular(AppConstants.radius.r),
                ),
              ),
              child: Center(
                child: Icon(FlutterRemix.gift_fill, color: widget.colors.white),
              ),
            ),
            Padding(
              padding: REdgeInsets.all(8.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          giftCart.translation?.title ?? '-',
                          style: CustomStyle.interRegular(
                            color: widget.colors.textBlack,
                          ),
                          maxLines: 1,
                        ),
                        Text(
                          "${AppHelpers.getTranslation(TrKeys.duration)}: ${giftCart.time}",
                          style: CustomStyle.interRegular(
                            color: widget.colors.textBlack,
                          ),
                        ),
                        Text(
                          AppHelpers.numberFormat(giftCart.price),
                          style: CustomStyle.interNormal(
                            color: widget.colors.textBlack,
                            size: 15,
                          ),
                        ),
                      ],
                    ),
                  ),
                  ButtonEffectAnimation(
                    onTap: () {
                      AppRouteShop.goGiftCartPaymentBottomSheet(
                        context,
                        model: giftCart,
                        colors: widget.colors,
                      );
                    },
                    child: Container(
                      padding: EdgeInsets.symmetric(
                        vertical: 10.r,
                        horizontal: 16.r,
                      ),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(8.r),
                        color: widget.colors.primary,
                      ),
                      child: Text(
                        AppHelpers.getTranslation(TrKeys.buy),
                        style: CustomStyle.interNormal(
                          color: widget.colors.white,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
