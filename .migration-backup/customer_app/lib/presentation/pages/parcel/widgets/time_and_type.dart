// ignore_for_file: use_build_context_synchronously

import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/parcel/parcel_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/parcel/widgets/type_item.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:lottie/lottie.dart';

import '../../../app_assets.dart';

class TypeAndTime extends StatelessWidget {
  final ParcelState state;
  final CustomColorSet colors;

  const TypeAndTime({super.key, required this.state, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        if (state.expand)
          InkWell(
            child: Container(
              margin: EdgeInsets.only(top: 16.r),
              decoration: BoxDecoration(
                color: colors.newBoxColor,
                borderRadius: BorderRadius.circular(10.r),
              ),
              padding: EdgeInsets.symmetric(horizontal: 20.r, vertical: 16.r),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        AppHelpers.getTranslation(TrKeys.remainAnonymous),
                        style: CustomStyle.interSemi(
                          size: 16,
                          color: colors.textBlack,
                        ),
                      ),
                      Text(
                        AppHelpers.getTranslation(TrKeys.dontNotifyRecipient),
                        style: CustomStyle.interRegular(
                          size: 14,
                          color: colors.textBlack,
                        ),
                      ),
                    ],
                  ),
                  const Spacer(),
                  SizedBox(
                    width: 76.r,
                    child: CustomToggle(
                      offTitle: AppHelpers.getTranslation(TrKeys.off),
                      onTitle: AppHelpers.getTranslation(TrKeys.on),
                      isOnline: false,
                      colors: colors,
                      onChange: (bool value) {},
                    ),
                  ),
                ],
              ),
            ),
          ),
        InkWell(
          onTap: () async {
            final time = await showTimePicker(
              context: context,
              initialTime: TimeOfDay.now(),
              initialEntryMode: TimePickerEntryMode.dialOnly,
            );

            if (time != null) {
              if (time.hour < TimeOfDay.now().hour) {
                if (context.mounted) {
                  AppHelpers.errorSnackBar(
                    context,
                    message: AppHelpers.getTranslation(
                      TrKeys.thisTimeIsNotAvailable,
                    ),
                  );
                }

                return;
              }
              if (time.hour == TimeOfDay.now().hour &&
                  time.minute < TimeOfDay.now().hour) {
                if (context.mounted) {
                  AppHelpers.errorSnackBar(
                    context,
                    message: AppHelpers.getTranslation(
                      TrKeys.thisTimeIsNotAvailable,
                    ),
                  );
                }
                return;
              }
              context.read<ParcelBloc>().add(ParcelEvent.setTime(time: time));
            }
          },
          child: ButtonEffectAnimation(
            child: Container(
              margin: EdgeInsets.only(top: 16.r, bottom: 24.r),
              decoration: BoxDecoration(
                color: colors.newBoxColor,
                borderRadius: BorderRadius.circular(10.r),
              ),
              padding: EdgeInsets.symmetric(horizontal: 20.r, vertical: 16.r),
              child: Row(
                children: [
                  Icon(FlutterRemix.time_line, color: colors.textBlack),
                  12.horizontalSpace,
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        AppHelpers.getTranslation(TrKeys.deliveryTime),
                        style: CustomStyle.interRegular(
                          size: 12,
                          color: colors.textBlack,
                        ),
                      ),
                      SizedBox(
                        width: MediaQuery.sizeOf(context).width / 2,
                        child: Text(
                          "${AppHelpers.getTranslation(TrKeys.pickupAt)} ${TimeService.timeFormatTime(state.time?.format(context) ?? TimeOfDay.now().format(context))}",
                          style: CustomStyle.interSemi(
                            size: 16,
                            color: colors.textBlack,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
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
        ),
        state.types.isNotEmpty
            ? Container(
                margin: EdgeInsets.only(top: 10.r, bottom: 24.r),
                decoration: BoxDecoration(
                  color: colors.newBoxColor,
                  borderRadius: BorderRadius.circular(AppConstants.radius.r),
                ),
                padding: EdgeInsets.all(16.r),
                child: GridView.builder(
                  padding: EdgeInsets.zero,
                  itemCount: state.types.length,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    childAspectRatio: 1,
                    crossAxisCount: 2,
                  ),
                  itemBuilder: (context, index) {
                    return TypeItem(
                      colors: colors,
                      type: state.types[index],
                      index: index,
                      selectIndex: state.selectType,
                    );
                  },
                ),
              )
            : Column(
                children: [
                  32.verticalSpace,
                  Lottie.asset(Assets.lottieEmptyList),
                  24.verticalSpace,
                  Padding(
                    padding: EdgeInsets.all(16.r),
                    child: Text(
                      AppHelpers.getTranslation(
                        TrKeys.ifYouWantToUseThisService,
                      ),
                      style: CustomStyle.interNormal(color: colors.textBlack),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ],
              ),
      ],
    );
  }
}
