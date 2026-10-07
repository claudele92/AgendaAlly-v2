import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/svg.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/gift_cart/gift_cart_bloc.dart';
import 'package:demand/domain/model/response/my_gift_cart_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../../app_assets.dart';

class SelectGiftCartScreen extends StatefulWidget {
  final CustomColorSet colors;
  final ScrollController controller;

  const SelectGiftCartScreen({
    super.key,
    required this.colors,
    required this.controller,
  });

  @override
  State<SelectGiftCartScreen> createState() => _SelectGiftCartScreenState();
}

class _SelectGiftCartScreenState extends State<SelectGiftCartScreen> {
  late RefreshController giftCartRefresh;

  @override
  void initState() {
    giftCartRefresh = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    giftCartRefresh.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: Container(
        decoration: BoxDecoration(
          color: widget.colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.only(left: 16.r, right: 16.r),
        child: Column(
          children: [
            8.verticalSpace,
            Container(
              height: 4.r,
              margin: EdgeInsets.symmetric(
                horizontal: MediaQuery.sizeOf(context).width / 2 - 48.r,
              ),
              decoration: BoxDecoration(
                color: widget.colors.icon,
                borderRadius: BorderRadius.circular(100.r),
              ),
            ),
            16.verticalSpace,
            Text(
              AppHelpers.getTranslation(TrKeys.selectGiftCart),
              style: CustomStyle.interNormal(color: widget.colors.textBlack),
            ),
            24.verticalSpace,
            BlocBuilder<GiftCartBloc, GiftCartState>(
              builder: (context, state) {
                return Expanded(
                  child: state.isLoading
                      ? const Loading()
                      : state.myGiftCarts.isNotEmpty
                      ? SmartRefresher(
                          controller: giftCartRefresh,
                          scrollController: widget.controller,
                          enablePullUp: true,
                          enablePullDown: false,
                          onLoading: () {
                            context.read<GiftCartBloc>().add(
                              GiftCartEvent.myGiftCart(
                                context,
                                controller: giftCartRefresh,
                              ),
                            );
                          },
                          child: ListView.builder(
                            controller: widget.controller,
                            padding: EdgeInsets.symmetric(horizontal: 16.r),
                            shrinkWrap: true,
                            itemCount: state.myGiftCarts.length,
                            itemBuilder: (context, index) {
                              return _giftCartItem(
                                context,
                                myGiftCart: state.myGiftCarts[index],
                                index: index,
                              );
                            },
                          ),
                        )
                      : Column(
                          children: [
                            32.verticalSpace,
                            SvgPicture.asset(Assets.svgNoMembership),
                            32.verticalSpace,
                            Padding(
                              padding: EdgeInsets.symmetric(horizontal: 32.r),
                              child: Text(
                                AppHelpers.getTranslation(
                                  TrKeys.youHaveNoMembership,
                                ),
                                textAlign: TextAlign.center,
                                style: CustomStyle.interNoSemi(
                                  color: widget.colors.textBlack,
                                  size: 30,
                                ),
                              ),
                            ),
                            16.verticalSpace,
                            Padding(
                              padding: EdgeInsets.symmetric(horizontal: 32.r),
                              child: Text(
                                AppHelpers.getTranslation(
                                  TrKeys
                                      .youDontHaveAnyMembershipMaybeYouWillGetMembershipSoon,
                                ),
                                textAlign: TextAlign.center,
                                style: CustomStyle.interRegular(
                                  color: widget.colors.textBlack,
                                  size: 16,
                                ),
                              ),
                            ),
                          ],
                        ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _giftCartItem(
    BuildContext context, {
    required MyGiftCartModel? myGiftCart,
    required int index,
  }) {
    return Padding(
      padding: EdgeInsets.only(right: 8.r, bottom: 8.r),
      child: ButtonEffectAnimation(
        onTap: () {
          Navigator.pop(context);
          context.read<BookingBloc>()
            ..add(BookingEvent.setGiftCart(giftCart: myGiftCart))
            ..add(BookingEvent.calculateBooking(context));
        },
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
                  child: Icon(
                    FlutterRemix.gift_fill,
                    color: widget.colors.white,
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(8.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          myGiftCart?.giftCart?.translation?.title ?? "",
                          style: CustomStyle.interRegular(
                            color: widget.colors.textBlack,
                          ),
                        ),
                        Text(
                          "${AppHelpers.getTranslation(TrKeys.duration)}: ${myGiftCart?.giftCart?.time}",
                          style: CustomStyle.interRegular(
                            color: widget.colors.textBlack,
                          ),
                        ),
                        Text(
                          AppHelpers.numberFormat(myGiftCart?.giftCart?.price),
                          style: CustomStyle.interNormal(
                            color: widget.colors.textBlack,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
