import 'package:add_2_calendar/add_2_calendar.dart';
import 'package:demand/application/review/review_bloc.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class BookedServiceItem extends StatelessWidget {
  final CustomColorSet colors;
  final BookingModel bookingModel;
  final int? shopId;

  const BookedServiceItem({
    super.key,
    required this.colors,
    required this.bookingModel,
    required this.shopId,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                bookingModel.serviceMaster?.service?.translation?.title ?? "",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 20,
                ),
              ),
            ),
            Text(
              AppHelpers.numberFormat(bookingModel.totalPrice),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
          ],
        ),
        6.verticalSpace,
        Text(
          "${bookingModel.serviceMaster?.interval} ${AppHelpers.getTranslation(TrKeys.minute)} ${AppHelpers.getTranslation(TrKeys.withKey)} ${bookingModel.master?.firstname}",
          style: CustomStyle.interNormal(color: colors.textHint, size: 14),
        ),
        6.verticalSpace,
        Text(
          "${AppHelpers.getTranslation(TrKeys.time)} :  ${TimeService.timeFormat(bookingModel.startDate)} - ${TimeService.timeFormat(bookingModel.endDate)} ${TimeService.dateFormatDMY(bookingModel.startDate)}",
          style: CustomStyle.interNormal(color: colors.textHint, size: 14),
        ),
        6.verticalSpace,
        if (bookingModel.extras?.isNotEmpty ?? false)
          Wrap(
            crossAxisAlignment: WrapCrossAlignment.start,
            children: [
              Padding(
                padding: REdgeInsets.only(bottom: 6),
                child: Text(
                  "${AppHelpers.getTranslation(TrKeys.extras)} : ",
                  style: CustomStyle.interNormal(
                    color: colors.textHint,
                    size: 14,
                  ),
                ),
              ),
              ...?bookingModel.extras?.map(
                (e) => Padding(
                  padding: REdgeInsets.only(bottom: 6, right: 4),
                  child: Text(
                    "${e.translation?.title} (+${AppHelpers.numberFormat(e.price)})",
                    style: CustomStyle.interNormal(
                      color: colors.textHint,
                      size: 14,
                    ),
                  ),
                ),
              ),
            ],
          ),
        Text(
          "${AppHelpers.getTranslation(TrKeys.status)} : ${AppHelpers.getTranslation(bookingModel.status ?? '')}",
          style: CustomStyle.interNormal(color: colors.textHint, size: 14),
        ),
        6.verticalSpace,
        Text(
          "${AppHelpers.getTranslation(TrKeys.bookingId)} : ${bookingModel.id}",
          style: CustomStyle.interNormal(color: colors.textHint, size: 14),
        ),
        if (bookingModel.address != null)
          Padding(
            padding: EdgeInsets.only(top: 6.r),
            child: Text(
              "${AppHelpers.getTranslation(TrKeys.address)} : ${bookingModel.address?.location?.address ?? bookingModel.address?.address ?? ""}",
              style: CustomStyle.interNormal(color: colors.textHint, size: 14),
            ),
          ),
        if (bookingModel.membership != null)
          Padding(
            padding: EdgeInsets.only(top: 6.r),
            child: Text(
              "${AppHelpers.getTranslation(TrKeys.membershipId)} : ${bookingModel.membership?.id ?? ""}",
              style: CustomStyle.interNormal(color: colors.textHint, size: 14),
            ),
          ),
        16.verticalSpace,
        if (bookingModel.status == TrKeys.ended)
          BlocBuilder<ReviewBloc, ReviewState>(
            builder: (context, state) {
              return state.isAddButton
                  ? Padding(
                      padding: EdgeInsets.only(bottom: 16.r),
                      child: CustomButton(
                        title: AppHelpers.getTranslation(TrKeys.addReview),
                        bgColor: colors.primary,
                        titleColor: colors.white,
                        onTap: () {
                          AppRouteService.goAddReviewBookingPage(
                            context,
                            shopId: shopId,
                            booking: bookingModel,
                          );
                        },
                      ),
                    )
                  : SizedBox.shrink();
            },
          ),
        _addCalendar(colors),
        Padding(
          padding: EdgeInsets.only(bottom: 16.r),
          child: CustomButton(
            title: AppHelpers.getTranslation(TrKeys.goNotes),
            bgColor: colors.primary,
            titleColor: colors.white,
            onTap: () {
              AppRouteService.goBookingNotesPage(context, book: bookingModel);
            },
          ),
        ),
      ],
    );
  }

  Padding _addCalendar(CustomColorSet colors) {
    return Padding(
      padding: EdgeInsets.only(bottom: 12),
      child: ButtonEffectAnimation(
        onTap: () {
          final Event event = Event(
            title:
                " ${bookingModel.serviceMaster?.service?.translation?.title} ${AppHelpers.getTranslation(TrKeys.inKey).toLowerCase()} ${bookingModel.shop?.translation?.title}",
            description:
                bookingModel.serviceMaster?.shop?.translation?.description ??
                "description",
            location: bookingModel.serviceMaster?.shop?.translation?.address,
            startDate:
                DateTime.tryParse(
                  bookingModel.startDate?.toIso8601String().replaceAll(
                        'Z',
                        '',
                      ) ??
                      '',
                ) ??
                DateTime.now(),
            endDate:
                DateTime.tryParse(
                  bookingModel.endDate?.toIso8601String().replaceAll('Z', '') ??
                      '',
                ) ??
                DateTime.now(),
            iosParams: const IOSParams(reminder: Duration(hours: 1)),
          );
          Add2Calendar.addEvent2Cal(event);
        },
        child: Padding(
          padding: EdgeInsets.symmetric(vertical: 16.r, horizontal: 12),
          child: Row(
            children: [
              Icon(
                FlutterRemix.calendar_event_line,
                color: colors.textBlack,
                size: 24.r,
              ),
              16.horizontalSpace,
              Text(
                AppHelpers.getTranslation(TrKeys.addToCalendar),
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 18,
                ),
              ),
              const Spacer(),
              Icon(
                FlutterRemix.arrow_right_s_line,
                color: colors.textBlack,
                size: 24.r,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
