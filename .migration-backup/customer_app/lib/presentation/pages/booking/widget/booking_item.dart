import 'package:add_2_calendar/add_2_calendar.dart';
import 'package:collection/collection.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/infrastructure/service/services.dart';

import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/booking/widget/cancel_screen.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class BookingItem extends StatelessWidget {
  final BookingModel book;
  final CustomColorSet colors;
  final bool past;

  const BookingItem({
    super.key,
    required this.book,
    required this.colors,
    this.past = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(10.r),
      onTap: () {
        AppRouteService.goBookingPage(
          context,
          shop: book.shop,
          id: book.id ?? 0,
        );
      },
      child: Container(
        margin: EdgeInsets.only(bottom: 10.r),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(10.r),
          border: Border.all(color: colors.icon),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Padding(
                padding: EdgeInsets.only(top: 24.r, bottom: 24.r, left: 16.r),
                child: Row(
                  children: [
                    Container(
                      decoration: BoxDecoration(
                        border: Border.all(color: colors.icon),
                        shape: BoxShape.circle,
                      ),
                      child: CustomNetworkImage(
                        url: book.shop?.logoImg ?? "",
                        height: 60,
                        width: 60,
                        radius: 30,
                      ),
                    ),
                    10.horizontalSpace,
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Flexible(
                                child: Text(
                                  book.shop?.translation?.title ?? "",
                                  style: CustomStyle.interNoSemi(
                                    color: colors.textBlack,
                                    size: 20,
                                  ),
                                ),
                              ),
                              if (book.shop?.verify ?? false) const BadgeItem(),
                            ],
                          ),
                          10.verticalSpace,
                          Text(
                            TimeService.dateFormatMDYHm(book.startDate),
                            style: CustomStyle.interNormal(
                              color: colors.textHint,
                              size: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            if (book.status != 'canceled' &&
                book.transactions?.firstWhereOrNull(
                      (t) => ListConstants.payLater.contains(t.status),
                    ) !=
                    null)
              _popupMenu(),
          ],
        ),
      ),
    );
  }

  PopupMenuButton<dynamic> _popupMenu() {
    return PopupMenuButton(
      offset: const Offset(0, 32),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10.r)),
      itemBuilder: (context) {
        return [
          if (book.transactions?.firstWhere(
                (t) => ListConstants.payLater.contains(t.status),
              ) !=
              null)
            PopupMenuItem(
              child: Row(
                children: [
                  Icon(FlutterRemix.bank_card_2_line, color: colors.textBlack),
                  16.horizontalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.pay),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 12,
                    ),
                  ),
                ],
              ),
              onTap: () {
                AppRouteService.goPaymentPayLater(
                  context,
                  colors: colors,
                  booking: book,
                );
              },
            ),
          if (!past)
            PopupMenuItem(
              child: Row(
                children: [
                  Icon(
                    FlutterRemix.calendar_event_line,
                    color: colors.textBlack,
                  ),
                  16.horizontalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.addToCalendar),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 12,
                    ),
                  ),
                ],
              ),
              onTap: () {
                final Event event = Event(
                  title: book.serviceMaster?.shop?.translation?.title ?? "Shop",
                  description:
                      book.serviceMaster?.shop?.translation?.description ??
                      "description",
                  location: book.serviceMaster?.shop?.translation?.address,
                  startDate:
                      DateTime.tryParse(
                        book.startDate?.toIso8601String().replaceAll('Z', '') ??
                            '',
                      ) ??
                      DateTime.now(),
                  endDate:
                      DateTime.tryParse(
                        book.endDate?.toIso8601String().replaceAll('Z', '') ??
                            '',
                      ) ??
                      DateTime.now(),
                  iosParams: const IOSParams(reminder: Duration(hours: 1)),
                );
                Add2Calendar.addEvent2Cal(event);
              },
            ),
          if (!past)
            PopupMenuItem(
              value: 'cancel',
              child: Row(
                children: [
                  Icon(FlutterRemix.close_circle_line, color: colors.textBlack),
                  16.horizontalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.cancelAppointment),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 12,
                    ),
                  ),
                ],
              ),
              onTap: () {
                AppHelpers.showCustomModalBottomSheet(
                  context,
                  modal: BlocProvider.value(
                    value: context.read<BookingBloc>(),
                    child: CancelBookingScreen(
                      bookScreen: false,
                      colors: colors,
                      bookId: book.id ?? 0,
                    ),
                  ),
                );
              },
            ),
        ];
      },
    );
  }
}
