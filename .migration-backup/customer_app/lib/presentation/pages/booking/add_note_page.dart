// ignore_for_file: use_build_context_synchronously

import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/checkout/checkout_bloc.dart';
import 'package:demand/application/gift_cart/gift_cart_bloc.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/domain/model/response/booking_calculate_response.dart';
import 'package:demand/infrastructure/service/services.dart';

import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/style/style.dart';

import 'package:demand/presentation/style/theme/theme.dart';

import '../../route/app_route.dart';
import 'widget/coupon.dart';

class AddNotePage extends StatefulWidget {
  const AddNotePage({super.key});

  @override
  State<AddNotePage> createState() => _AddNotePageState();
}

class _AddNotePageState extends State<AddNotePage> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<BookingBloc>()
        ..add(BookingEvent.fetchPayments(context, payLater: false))
        ..add(BookingEvent.calculateBooking(context));
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => CustomScaffold(
        body: (colors) => KeyboardDismisser(
          isLtr: LocalStorage.getLangLtr(),
          child: SafeArea(
            bottom: false,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16.r),
                  child: Text(
                    AppHelpers.getTranslation(TrKeys.servicesOffered),
                    style: CustomStyle.interNoSemi(
                      color: colors.textBlack,
                      size: 22,
                    ),
                  ),
                ),
                Expanded(
                  child: BlocConsumer<BookingBloc, BookingState>(
                    listenWhen: (p, n) => p.coupon != n.coupon,
                    listener: (BuildContext context, BookingState state) {
                      context.read<BookingBloc>().add(
                        BookingEvent.calculateBooking(context),
                      );
                    },
                    builder: (context, state) {
                      return ListView(
                        shrinkWrap: true,
                        padding: EdgeInsets.only(bottom: 116.r),
                        children: [
                          _services(
                            colors: colors,
                            selectMasters: state.selectMasters,
                            calculate: state.calculate?.data,
                          ),
                          if (state.selectMasters.values.any(
                            (e) => e.serviceMaster?.type == TrKeys.offlineOut,
                          ))
                            _location(
                              colors,
                              (state.selectMasters.values.firstWhere(
                                (e) =>
                                    e.serviceMaster?.type == TrKeys.offlineOut,
                              )),
                            ),
                          Padding(
                            padding: REdgeInsets.symmetric(horizontal: 16),
                            child: Column(
                              children: [
                                CouponWidget(
                                  colors: colors,
                                  shopId:
                                      state
                                          .selectMasters
                                          .values
                                          .first
                                          .serviceMaster
                                          ?.shopId ??
                                      0,
                                ),
                                6.verticalSpace,
                                UseWalletWidget(
                                  colors: colors,
                                  isActive: (state.walletPrice ?? 0) != 0,
                                  totalPrice: state.walletPrice,
                                  onTap: () {
                                    if ((state.walletPrice ?? 0) != 0 ||
                                        (LocalStorage.getUser().wallet?.price ??
                                                0) <=
                                            0) {
                                      context.read<BookingBloc>().add(
                                        BookingEvent.setWalletPrice(
                                          price: null,
                                        ),
                                      );
                                      return;
                                    }
                                    AppHelpers.showAlertDialog(
                                      context,
                                      colors: colors,
                                      child: WalletPayDialog(
                                        colors: colors,
                                        totalPrice:
                                            state.calculate?.data?.totalPrice,
                                        onChange: (price) {
                                          context.read<BookingBloc>().add(
                                            BookingEvent.setWalletPrice(
                                              price: price,
                                            ),
                                          );
                                        },
                                      ),
                                    );
                                  },
                                ),
                              ],
                            ),
                          ),
                          12.verticalSpace,
                          _calculate(colors: colors, state: state),
                        ],
                      );
                    },
                  ),
                ),
              ],
            ),
          ),
        ),
        floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
        floatingButton: (colors) => _bottom(colors),
      ),
    );
  }

  Widget _services({
    required CustomColorSet colors,
    required Map<int, MasterModel> selectMasters,
    required Calculate? calculate,
  }) {
    return ListView.builder(
      physics: const NeverScrollableScrollPhysics(),
      padding: EdgeInsets.symmetric(vertical: 16.r, horizontal: 16.r),
      shrinkWrap: true,
      itemCount: selectMasters.length,
      itemBuilder: (context, index) {
        final selectMaster = selectMasters.values.elementAt(index);
        final Item? item = calculate?.items?.firstWhere(
          (element) =>
              element.serviceMaster?.service?.id ==
              selectMasters.keys.elementAt(index),
          orElse: () => Item(),
        );
        return _serviceItem(
          context,
          selectMaster: selectMaster,
          colors: colors,

          item: item,
        );
      },
    );
  }

  Widget _calculate({
    required CustomColorSet colors,
    required BookingState state,
  }) {
    return state.isLoading
        ? const Loading()
        : Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              BlocBuilder<GiftCartBloc, GiftCartState>(
                builder: (context, stateGift) {
                  return stateGift.myGiftCarts.isNotEmpty
                      ? Padding(
                          padding: EdgeInsets.symmetric(horizontal: 16.r),
                          child: ButtonEffectAnimation(
                            onTap: () {
                              AppRouteService.goSelectGiftCart(
                                context,
                                colors: colors,
                                shopId:
                                    state
                                        .selectMasters
                                        .values
                                        .first
                                        .invite
                                        ?.shopId ??
                                    0,
                              );
                            },
                            child: Container(
                              width: double.infinity,
                              padding: EdgeInsets.symmetric(
                                vertical: 16.r,
                                horizontal: 16.r,
                              ),
                              decoration: BoxDecoration(
                                border: Border.all(color: colors.icon),
                                borderRadius: BorderRadius.circular(8.r),
                              ),
                              child: Text(
                                state.giftCart == null
                                    ? AppHelpers.getTranslation(
                                        TrKeys.selectGiftCart,
                                      )
                                    : (state
                                              .giftCart
                                              ?.giftCart
                                              ?.translation
                                              ?.title ??
                                          ""),
                                style: CustomStyle.interNormal(
                                  color: state.giftCart == null
                                      ? colors.textHint
                                      : colors.textBlack,
                                ),
                              ),
                            ),
                          ),
                        )
                      : const SizedBox.shrink();
                },
              ),
              PriceItem(
                title: TrKeys.subtotal,
                price: state.calculate?.data?.price,
                colors: colors,
              ),
              PriceItem(
                title: TrKeys.extrasPrice,
                price: state.calculate?.data?.totalExtraPrice,
                colors: colors,
              ),
              // PriceItem(
              //   title: TrKeys.commissionFee,
              //   price: state.calculate?.data?.totalCommissionFee,
              //   colors: colors,
              // ),
              PriceItem(
                title: TrKeys.serviceFee,
                price: state.calculate?.data?.totalServiceFee,
                colors: colors,
              ),
              PriceItem(
                title: TrKeys.discount,
                price: state.calculate?.data?.totalDiscount,
                colors: colors,
                discount: true,
              ),
              PriceItem(
                title: TrKeys.coupon,
                price: state.calculate?.data?.couponPrice,
                colors: colors,
                discount: true,
              ),
              PriceItem(
                title: TrKeys.giftCart,
                price: state.calculate?.data?.totalGiftCartPrice,
                colors: colors,
                discount: true,
              ),
              Column(
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
                          AppHelpers.getTranslation(TrKeys.total),
                          style: CustomStyle.interNormal(
                            color: colors.textBlack,
                            size: 16,
                          ),
                        ),
                        Text(
                          AppHelpers.numberFormat(
                            state.calculate?.data?.totalPrice,
                          ),
                          style: CustomStyle.interNormal(
                            color: colors.textBlack,
                            size: 16,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              PriceItem(
                discount: true,
                colors: colors,
                title: TrKeys.wallet,
                price: state.walletPrice,
              ),
            ],
          );
  }

  Widget _serviceItem(
    BuildContext context, {
    required MasterModel selectMaster,
    required CustomColorSet colors,
    required Item? item,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        24.verticalSpace,
        Row(
          children: [
            Expanded(
              child: Text(
                item?.serviceMaster?.service?.translation?.title ?? "",
                style: CustomStyle.interNormal(color: colors.textBlack),
              ),
            ),
            Text(
              AppHelpers.numberFormat(item?.price),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
          ],
        ),
        4.verticalSpace,
        Row(
          children: [
            Expanded(
              child: Text(
                "${item?.serviceMaster?.master?.firstname ?? ""} ${item?.serviceMaster?.master?.lastname ?? ""}",
                style: CustomStyle.interNormal(color: colors.textBlack),
              ),
            ),
            Text(
              "${TimeService.timeFormat(item?.startDate)} - ${TimeService.timeFormat(item?.endDate)}",
              style: CustomStyle.interNormal(color: colors.textBlack, size: 14),
            ),
          ],
        ),
        4.verticalSpace,
        ...?item?.extras?.map(
          (e) => Padding(
            padding: REdgeInsets.only(top: 8),
            child: Row(
              children: [
                Text(
                  e.translation?.title ?? '',
                  style: CustomStyle.interRegular(
                    color: colors.textBlack,
                    size: 16,
                  ),
                ),
                const Spacer(),
                Text(
                  AppHelpers.numberFormat(e.price),
                  style: CustomStyle.interNormal(
                    color: colors.textBlack,
                    size: 18,
                  ),
                ),
              ],
            ),
          ),
        ),
        if (item?.errors?.isNotEmpty ?? false)
          Padding(
            padding: EdgeInsets.only(top: 4.r),
            child: Text(
              item?.errors?.toList().join(", ") ?? "",
              style: CustomStyle.interNormal(color: colors.error, size: 12),
            ),
          ),
        8.verticalSpace,
        CustomTextFormField(
          hint: AppHelpers.getTranslation(TrKeys.notesAndSpecial),
          onChanged: (text) {
            context.read<BookingBloc>().add(
              BookingEvent.selectMaster(
                serviceId: item?.serviceMaster?.service?.id,
                master: selectMaster.copyWith(note: text),
              ),
            );
          },
        ),
        // if (selectMaster.serviceMaster?.type == TrKeys.offlineOut)
        //   _location(colors, item?.serviceMaster?.service?.id, selectMaster),
        BlocBuilder<MembershipBloc, MembershipState>(
          builder: (context, state) {
            return state.myMemberships.isNotEmpty
                ? Padding(
                    padding: EdgeInsets.only(top: 8.r),
                    child: ButtonEffectAnimation(
                      onTap: () {
                        AppRouteService.goSelectMemberships(
                          context,
                          colors: colors,
                          serviceId: selectMaster.serviceMaster?.service?.id,
                          shopId: selectMaster.invite?.shopId ?? 0,
                          selectMaster: selectMaster,
                        );
                      },
                      child: Container(
                        width: double.infinity,
                        padding: EdgeInsets.symmetric(
                          vertical: 16.r,
                          horizontal: 16.r,
                        ),
                        decoration: BoxDecoration(
                          border: Border.all(color: colors.icon),
                          borderRadius: BorderRadius.circular(8.r),
                        ),
                        child: Text(
                          selectMaster.membership == null
                              ? AppHelpers.getTranslation(
                                  TrKeys.selectMembership,
                                )
                              : (selectMaster
                                        .membership
                                        ?.memberShip
                                        ?.translation
                                        ?.title ??
                                    ""),
                          style: CustomStyle.interNormal(
                            color: selectMaster.membership == null
                                ? colors.textHint
                                : colors.textBlack,
                          ),
                        ),
                      ),
                    ),
                  )
                : const SizedBox.shrink();
          },
        ),
      ],
    );
  }

  Widget _location(CustomColorSet colors, MasterModel? masterModel) {
    return BlocBuilder<CheckoutBloc, CheckoutState>(
      builder: (context, stateCheckout) {
        return Padding(
          padding: REdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: stateCheckout.address.isEmpty
              ? CustomButton(
                  title: AppHelpers.getTranslation(TrKeys.selectAddress),
                  bgColor: colors.primary,
                  titleColor: colors.white,
                  onTap: () {
                    AppRouteService.goAddressListServiceBottomSheet(
                      context,
                      colors: colors,
                    );
                  },
                )
              : ButtonEffectAnimation(
                  onTap: () {
                    AppRouteService.goAddressListServiceBottomSheet(
                      context,
                      colors: colors,
                    );
                  },
                  child: Container(
                    margin: EdgeInsets.only(top: 12.r),
                    padding: EdgeInsets.symmetric(
                      vertical: 16.r,
                      horizontal: 10.r,
                    ),
                    decoration: BoxDecoration(
                      border: Border.all(color: colors.icon),
                      color: colors.backgroundColor,
                      borderRadius: BorderRadius.circular(
                        AppConstants.radius.r,
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: colors.textBlack,
                          ),
                          padding: EdgeInsets.all(12.r),
                          child: Icon(
                            FlutterRemix.map_pin_range_fill,
                            color: colors.textWhite,
                          ),
                        ),
                        10.horizontalSpace,
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            SizedBox(
                              width: MediaQuery.sizeOf(context).width - 160.r,
                              child: Text(
                                AppHelpers.getTranslation(
                                  TrKeys.serviceAddress,
                                ),
                                style: CustomStyle.interRegular(
                                  color: colors.textBlack,
                                  size: 16,
                                ),
                              ),
                            ),
                            SizedBox(
                              width: MediaQuery.sizeOf(context).width - 160.r,
                              child: Text(
                                masterModel?.address?.location?.address ?? "",
                                style: CustomStyle.interRegular(
                                  color: colors.textBlack,
                                  size: 12,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const Spacer(),
                        Icon(
                          FlutterRemix.arrow_right_s_line,
                          color: colors.textBlack,
                        ),
                      ],
                    ),
                  ),
                ),
        );
      },
    );
  }

  Widget _bottom(CustomColorSet colors) {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16.r),
      child: Row(
        children: [
          PopButton(colors: colors),
          10.horizontalSpace,
          BlocBuilder<BookingBloc, BookingState>(
            builder: (context, state) {
              bool showButton = true;
              state.calculate?.data?.items?.forEach((element) {
                if (element.errors?.isNotEmpty ?? false) {
                  showButton = false;
                }
              });
              final directConfirm =
                  state.walletPrice != null &&
                  state.walletPrice! >=
                      (state.calculate?.data?.totalPrice ?? 0) &&
                  state.selectedPayment?.tag == 'wallet';
              return showButton
                  ? Expanded(
                      child: SizedBox(
                        height: 56.r,
                        child: CustomButton(
                          title: AppHelpers.getTranslation(
                            directConfirm ? TrKeys.confirm : TrKeys.next,
                          ),
                          bgColor: colors.primary,
                          titleColor: colors.textWhite,
                          isLoading: state.isButtonLoading,
                          onTap: () {
                            for (var element in state.selectMasters.values) {
                              if (element.address == null &&
                                  element.serviceMaster?.type ==
                                      TrKeys.offlineOut) {
                                AppHelpers.openDialog(
                                  context,
                                  title: AppHelpers.getTranslation(
                                    TrKeys.youMustEnterAddress,
                                  ),
                                );
                                return;
                              }
                            }
                            if (directConfirm) {
                              final event = context.read<BookingBloc>();
                              event.add(
                                BookingEvent.bookingService(
                                  context,
                                  totalPrice:
                                      state.calculate?.data?.totalPrice ?? 0,
                                  onSuccess: (id) {
                                    if (id == -1) {
                                      AppRouteService.goConfirmPage(context);
                                      return;
                                    }
                                    event.add(
                                      BookingEvent.fetchWebView(
                                        context,
                                        id: id,
                                        onSuccess: (value) async {
                                          final isPay =
                                              await AppRoute.goWebView(
                                                context,
                                                url: value,
                                              );
                                          if (isPay && context.mounted) {
                                            AppRouteService.goConfirmPage(
                                              context,
                                            );
                                            return;
                                          } else if (context.mounted) {
                                            AppRouteService.goFailPage(context);
                                          }
                                        },
                                        onFailure: (r) {
                                          AppRouteService.goFailPage(
                                            context,
                                            text: r,
                                          );
                                        },
                                      ),
                                    );
                                  },
                                ),
                              );
                            } else {
                              AppRouteService.goPaymentBottomSheet(
                                context,
                                serviceMasters: state.selectMasters.values
                                    .toList(),
                                colors: colors,
                                totalPrice: state.calculate?.data?.totalPrice,
                              );
                            }
                          },
                        ),
                      ),
                    )
                  : const SizedBox.shrink();
            },
          ),
        ],
      ),
    );
  }
}
