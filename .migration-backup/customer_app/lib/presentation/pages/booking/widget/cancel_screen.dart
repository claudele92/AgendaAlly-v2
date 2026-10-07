import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class CancelBookingScreen extends StatelessWidget {
  final CustomColorSet colors;
  final int bookId;
  final bool bookScreen;

  const CancelBookingScreen({
    super.key,
    required this.colors,
    required this.bookId,
    this.bookScreen = true,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(top: 16.r),
      decoration: BoxDecoration(
        color: colors.backgroundColor,
        borderRadius: BorderRadius.only(
          topRight: Radius.circular(AppConstants.radius.r),
          topLeft: Radius.circular(AppConstants.radius.r),
        ),
      ),
      child: Padding(
        padding: EdgeInsets.symmetric(horizontal: 16.r),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              height: 4.r,
              width: 48.r,
              decoration: BoxDecoration(
                color: colors.icon,
                borderRadius: BorderRadius.circular(10.r),
              ),
            ),
            28.verticalSpace,
            Text(
              AppHelpers.getTranslation(TrKeys.areYouSureYouWantToCancel),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 22),
            ),
            24.verticalSpace,
            Row(
              children: [
                Expanded(
                  child: CustomButton(
                    title: AppHelpers.getTranslation(TrKeys.close),
                    bgColor: colors.transparent,
                    titleColor: colors.textBlack,
                    borderColor: colors.textBlack,
                    onTap: () {
                      Navigator.pop(context);
                    },
                  ),
                ),
                10.horizontalSpace,
                Expanded(
                  child: BlocBuilder<BookingBloc, BookingState>(
                    buildWhen: (p, n) {
                      return p.isButtonLoading != n.isButtonLoading;
                    },
                    builder: (context, state) {
                      return CustomButton(
                        isLoading: state.isButtonLoading,
                        title: AppHelpers.getTranslation(TrKeys.cancel),
                        bgColor: colors.error,
                        titleColor: colors.white,
                        onTap: () {
                          context.read<BookingBloc>()
                            ..add(
                              BookingEvent.cancelBook(
                                context,
                                id: bookId,
                                onSuccess: () {
                                  Navigator.pop(context);
                                  if (bookScreen) {
                                    Navigator.pop(context);
                                  }
                                },
                              ),
                            )
                            ..add(
                              BookingEvent.fetchBookPast(
                                context,
                                isRefresh: true,
                              ),
                            );
                        },
                      );
                    },
                  ),
                ),
              ],
            ),
            32.verticalSpace,
          ],
        ),
      ),
    );
  }
}
