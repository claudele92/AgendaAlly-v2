import 'package:collection/collection.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/form_option/form_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/domain/model/response/form_options_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/booking/widget/booked_service.dart';
import 'package:demand/presentation/pages/shop/widgets/connect_button.dart';
import 'package:demand/presentation/pages/shop/widgets/share_like_widget.dart';
import 'package:demand/presentation/pages/shop/widgets/shop_avatar.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../order/widgets/order_transaction_item.dart';
import 'widget/cancel_screen.dart';

class BookingPage extends StatelessWidget {
  final int shopId;

  const BookingPage({super.key, required this.shopId});

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => Stack(
        children: [
          SizedBox(
            height: MediaQuery.sizeOf(context).height,
            width: MediaQuery.sizeOf(context).width,
            child: NestedScrollView(
              physics: const NeverScrollableScrollPhysics(),
              headerSliverBuilder: (BuildContext context, bool innerBox) {
                return [_appBar(colors, context)];
              },
              body: SizedBox(
                height: MediaQuery.sizeOf(context).height - 250.r,
                child: ListView(
                  shrinkWrap: true,
                  padding: EdgeInsets.zero,
                  children: [
                    16.verticalSpace,
                    BlocConsumer<BookingBloc, BookingState>(
                      // listenWhen: (p, n) {
                      //   return p.upcoming.length != n.upcoming.length;
                      // },
                      listener: (context, state) {
                        List<int> list = state.upcoming
                            .map((e) => e.serviceMasterId ?? 0)
                            .toList();

                        context.read<FormBloc>().add(
                          FormEvent.fetchForms(context, serviceMasterIds: list),
                        );
                      },
                      builder: (context, state) {
                        return state.isLoading
                            ? const Loading()
                            : Padding(
                                padding: EdgeInsets.symmetric(horizontal: 16.r),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      TimeService.dateFormatMDYHm(
                                        (state.upcoming.isNotEmpty)
                                            ? state.upcoming.first.startDate
                                            : DateTime.now(),
                                      ),
                                      style: CustomStyle.interNoSemi(
                                        color: colors.textBlack,
                                        size: 22,
                                      ),
                                    ),
                                    8.verticalSpace,
                                    Text(
                                      "${state.upcoming.fold(0, (e, element) => e + (element.serviceMaster?.interval ?? 0).toInt())} ${AppHelpers.getTranslation(TrKeys.minute)}",
                                      style: CustomStyle.interNormal(
                                        color: colors.textHint,
                                        size: 16,
                                      ),
                                    ),
                                    16.verticalSpace,
                                    const Divider(),
                                    if (!(state.upcoming.any(
                                      (e) =>
                                          e.status == TrKeys.canceled ||
                                          e.status == TrKeys.ended,
                                    )))
                                      const Divider(),
                                    if (!(state.upcoming.any(
                                      (e) =>
                                          e.status == TrKeys.canceled ||
                                          e.status == TrKeys.ended,
                                    )))
                                      _cancelBooking(context, colors, state),
                                    16.verticalSpace,
                                    Text(
                                      AppHelpers.getTranslation(TrKeys.details),
                                      style: CustomStyle.interNoSemi(
                                        color: colors.textBlack,
                                        size: 22,
                                      ),
                                    ),
                                    22.verticalSpace,
                                    ListView.builder(
                                      padding: EdgeInsets.zero,
                                      shrinkWrap: true,
                                      physics:
                                          const NeverScrollableScrollPhysics(),
                                      itemCount: state.upcoming.length,
                                      itemBuilder: (context, index) {
                                        return Column(
                                          children: [
                                            BookedServiceItem(
                                              shopId: shopId,
                                              colors: colors,
                                              bookingModel:
                                                  state.upcoming[index],
                                            ),
                                          ],
                                        );
                                      },
                                    ),
                                    const Divider(),
                                    _payLater(
                                      context,
                                      booking: state.upcoming.first,

                                      colors: colors,
                                      state: state,
                                    ),
                                    const Divider(),
                                    _formScreen(
                                      colors,
                                      state.upcoming
                                          .map((e) => e.address)
                                          .toList(),
                                    ),
                                    16.verticalSpace,
                                    const Divider(),
                                    16.verticalSpace,
                                    if ((state.upcoming.isNotEmpty
                                            ? state
                                                      .upcoming
                                                      .first
                                                      .giftCartPrice ??
                                                  0
                                            : 0) !=
                                        0)
                                      Padding(
                                        padding: REdgeInsets.only(bottom: 12),
                                        child: Row(
                                          children: [
                                            Text(
                                              AppHelpers.getTranslation(
                                                TrKeys.giftCartPrice,
                                              ),
                                              style: CustomStyle.interNoSemi(
                                                color: colors.textBlack,
                                                size: 18,
                                              ),
                                            ),
                                            const Spacer(),
                                            Text(
                                              "-${AppHelpers.numberFormat(state.upcoming.isNotEmpty ? state.upcoming.first.giftCartPrice ?? 0 : 0)}",
                                              style: CustomStyle.interNoSemi(
                                                color: CustomStyle.red,
                                                size: 18,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    Row(
                                      children: [
                                        Text(
                                          AppHelpers.getTranslation(
                                            TrKeys.total,
                                          ),
                                          style: CustomStyle.interNoSemi(
                                            color: colors.textBlack,
                                            size: 26,
                                          ),
                                        ),
                                        const Spacer(),
                                        Text(
                                          AppHelpers.numberFormat(
                                            (state.upcoming.isNotEmpty
                                                ? state
                                                      .upcoming
                                                      .first
                                                      .totalPriceByParent
                                                : 0),
                                          ),
                                          style: CustomStyle.interNoSemi(
                                            color: colors.textBlack,
                                            size: 26,
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (state.upcoming.isNotEmpty)
                                      ...?state.upcoming.first.transactions?.map(
                                        (e) => OrderTransactionItem(
                                          icon: Icon(
                                            e.paymentSystem?.tag == 'wallet'
                                                ? FlutterRemix.wallet_3_fill
                                                : e.paymentSystem?.tag == 'cash'
                                                ? FlutterRemix
                                                      .money_dollar_box_fill
                                                : FlutterRemix.bank_card_2_line,
                                            color: colors.textBlack,
                                          ),
                                          colors: colors,
                                          title:
                                              "${e.paymentSystem?.tag} (${AppHelpers.getTranslation(e.status ?? '')})",
                                          price: e.price,
                                        ),
                                      ),
                                    16.verticalSpace,
                                    const Divider(),
                                  ],
                                ),
                              );
                      },
                    ),
                    100.verticalSpace,
                  ],
                ),
              ),
            ),
          ),
          ShareAndLike(colors: colors, shopId: shopId, likeButton: false),
        ],
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) => Padding(
        padding: EdgeInsets.symmetric(horizontal: 16.r),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            PopButton(colors: colors),
            const Spacer(),
            ConnectButtonShop(colors: colors),
          ],
        ),
      ),
    );
  }

  SingleChildRenderObjectWidget _payLater(
    BuildContext context, {
    required BookingModel booking,
    required CustomColorSet colors,
    required BookingState state,
  }) {
    final payment = booking.transactions?.firstWhereOrNull(
      (transaction) =>
          (transaction.paymentSystem?.tag != 'cash' &&
          ListConstants.payLater.contains(transaction.status)),
    );
    if (payment != null) {
      return Padding(
        padding: REdgeInsets.only(bottom: 12),
        child: CustomButton(
          isLoading: state.isButtonLoading,
          title: AppHelpers.getTranslation(TrKeys.pay),
          onTap: () {
            AppRouteService.goPaymentPayLater(
              context,
              colors: colors,
              booking: booking,
            );
          },
          bgColor: colors.primary,
          titleColor: colors.white,
        ),
      );
    } else {
      return const SizedBox.shrink();
    }
  }

  BlocBuilder<FormBloc, FormOptionsState> _formScreen(
    CustomColorSet colors,
    List<BookingAddressModel?> list,
  ) {
    List<FormOptionsData?> listForm = [];
    for (var element in list) {
      listForm.addAll(element?.forms ?? []);
    }
    return BlocBuilder<FormBloc, FormOptionsState>(
      builder: (context, state) {
        return state.formOptionList.isEmpty
            ? const SizedBox.shrink()
            : Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  24.verticalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.form),
                    style: CustomStyle.interNoSemi(
                      color: colors.textBlack,
                      size: 26,
                    ),
                  ),
                  16.verticalSpace,
                  ListView.separated(
                    padding: EdgeInsets.zero,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: state.formOptionList.length,
                    itemBuilder: (context, index) {
                      return ButtonEffectAnimation(
                        onTap: () {
                          AppRouteService.goFormOptionPage(
                            context,
                            form: listForm.firstWhere(
                              (element) =>
                                  element?.id == state.formOptionList[index].id,
                              orElse: () => null,
                            ),
                            formOptionId: state.formOptionList[index].id,
                          );
                        },
                        child: Row(
                          children: [
                            Icon(
                              FlutterRemix.survey_line,
                              color: colors.textBlack,
                            ),
                            16.horizontalSpace,
                            Text(
                              state.formOptionList[index].translation?.title ??
                                  "",
                              style: CustomStyle.interNormal(
                                color: colors.textBlack,
                              ),
                            ),
                            const Spacer(),
                            Icon(
                              FlutterRemix.arrow_right_s_line,
                              color: colors.textBlack,
                            ),
                          ],
                        ),
                      );
                    },
                    separatorBuilder: (BuildContext context, int index) {
                      return const Divider();
                    },
                  ),
                  16.verticalSpace,
                ],
              );
      },
    );
  }

  Column _cancelBooking(
    BuildContext context,
    CustomColorSet colors,
    BookingState state,
  ) {
    return Column(
      children: [
        ButtonEffectAnimation(
          onTap: () {
            AppHelpers.showCustomModalBottomSheet(
              context,
              modal: BlocProvider.value(
                value: context.read<BookingBloc>(),
                child: CancelBookingScreen(
                  colors: colors,
                  bookId: state.upcoming.first.id ?? 0,
                ),
              ),
            );
          },
          child: Padding(
            padding: EdgeInsets.symmetric(vertical: 16.r),
            child: Row(
              children: [
                Icon(
                  FlutterRemix.close_circle_line,
                  color: colors.textBlack,
                  size: 26.r,
                ),
                16.horizontalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.cancelAppointment),
                  style: CustomStyle.interNormal(
                    color: colors.textBlack,
                    size: 20,
                  ),
                ),
                const Spacer(),
                Icon(
                  FlutterRemix.arrow_right_s_line,
                  color: colors.textBlack,
                  size: 26.r,
                ),
              ],
            ),
          ),
        ),
        const Divider(),
      ],
    );
  }

  SliverAppBar _appBar(CustomColorSet colors, BuildContext context) {
    return SliverAppBar(
      pinned: true,
      snap: false,
      floating: true,
      forceElevated: true,
      expandedHeight: 250.r,
      toolbarHeight: 56.r,
      elevation: 0,
      leading: const SizedBox.shrink(),
      backgroundColor: colors.backgroundColor,
      flexibleSpace: FlexibleSpaceBar(
        expandedTitleScale: 1.5,
        collapseMode: CollapseMode.pin,
        title: BlocBuilder<ShopBloc, ShopState>(
          buildWhen: (p, n) {
            return p.shop != n.shop;
          },
          builder: (context, state) {
            return Stack(
              children: [
                Positioned(
                  left: 14.r,
                  right: 100.r,
                  child: SizedBox(
                    width: double.infinity,
                    child: Center(
                      child: Text(
                        state.shop?.translation?.title ?? "",
                        style: CustomStyle.interSemi(
                          color: colors.textBlack,
                          size: 21,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ),
                ),
              ],
            );
          },
        ),
        titlePadding: EdgeInsets.only(
          top: MediaQuery.paddingOf(context).top + 10.r,
        ),
        background: ShopAvatar(colors: colors, isBooking: true),
      ),
    );
  }
}
