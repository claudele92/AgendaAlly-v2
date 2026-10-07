import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/infrastructure/service/services.dart';

import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class SelectMasterPage extends StatelessWidget {
  final BookingState? state;
  final int? shopId;

  const SelectMasterPage({super.key, required this.shopId, this.state});

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => KeyboardDismisser(
        isLtr: LocalStorage.getLangLtr(),
        child: SafeArea(
          bottom: false,
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.r),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppHelpers.getTranslation(TrKeys.selectStaff),
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 18,
                  ),
                ),
                24.verticalSpace,
                BlocBuilder<BookingBloc, BookingState>(
                  builder: (context, state) {
                    return ListView.builder(
                      padding: EdgeInsets.zero,
                      shrinkWrap: true,
                      itemCount: state.selectServices.length,
                      itemBuilder: (context, index) {
                        final MasterModel? master =
                            state.selectMasters[state.selectServices[index].id];
                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              state.selectServices[index].translation?.title ??
                                  "",
                              style: CustomStyle.interNormal(
                                color: colors.textBlack,
                                size: 18,
                              ),
                            ),
                            8.verticalSpace,
                            Row(
                              children: [
                                _addMaster(
                                  context,
                                  state,
                                  index,
                                  colors,
                                  master,
                                ),
                                const Spacer(),
                                if (master != null)
                                  _addTime(
                                    context,
                                    state,
                                    index,
                                    colors,
                                    master,
                                  ),
                              ],
                            ),
                            8.verticalSpace,
                            const Divider(),
                            8.verticalSpace,
                          ],
                        );
                      },
                    );
                  },
                ),
              ],
            ),
          ),
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) => Align(
        alignment: Alignment.bottomLeft,
        child: Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Row(
            children: [
              PopButton(colors: colors),
              10.horizontalSpace,
              Expanded(
                child: SizedBox(
                  height: 56.r,
                  child: BlocBuilder<BookingBloc, BookingState>(
                    buildWhen: (p, n) {
                      return (p.selectMasters != n.selectMasters) ||
                          (p.selectServices != n.selectServices);
                    },
                    builder: (context, state) {
                      final active =
                          !state.selectMasters.values.any(
                            (e) => e.time == null,
                          ) &&
                          AppHelpers.checkServiceTime(state.selectMasters);
                      return CustomButton(
                        title: AppHelpers.getTranslation(TrKeys.next),
                        bgColor: active
                            ? colors.primary
                            : colors.primary.withValues(alpha: 0.8),
                        titleColor: colors.white,
                        onTap: () {
                          if (state.selectMasters.values.any(
                            (e) => e.time == null,
                          )) {
                            AppHelpers.openDialog(
                              context,
                              title: AppHelpers.getTranslation(
                                TrKeys.youMustSelectTime,
                              ),
                            );
                            return;
                          }
                          if (!AppHelpers.checkServiceTime(
                            state.selectMasters,
                          )) {
                            AppHelpers.openDialog(
                              context,
                              title: AppHelpers.getTranslation(
                                TrKeys.pleaseChangeTime,
                              ),
                            );
                            return;
                          }
                          List list = state.selectMasters.keys.toList()..sort();
                          if (listEquals(
                            list,
                            state.selectServices.map((e) => e.id).toList()
                              ..sort(),
                          )) {
                            AppRouteService.goAddNotePage(
                              context,
                              shopId:
                                  state
                                      .selectMasters
                                      .values
                                      .first
                                      .serviceMaster
                                      ?.shopId ??
                                  0,
                            );
                            return;
                          }
                          AppHelpers.openDialog(
                            context,
                            title: AppHelpers.getTranslation(
                              TrKeys.youMustSelectMaster,
                            ),
                          );
                        },
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  ButtonEffectAnimation _addMaster(
    BuildContext context,
    BookingState state,
    int index,
    CustomColorSet colors,
    MasterModel? master,
  ) {
    return ButtonEffectAnimation(
      onTap: () {
        AppRouteService.goSelectMasterBottomSheet(
          context,
          serviceId: state.selectServices[index].id,
          title: state.selectServices[index].translation?.title ?? "",
          shopId: shopId,
          colors: colors,
        );
      },
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(10.r),
          border: Border.all(color: colors.icon),
        ),
        padding: EdgeInsets.symmetric(horizontal: 12.r, vertical: 8.r),
        child: master == null
            ? Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    FlutterRemix.account_circle_line,
                    color: colors.textBlack,
                    size: 24.r,
                  ),
                  8.horizontalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.addMaster),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 16,
                    ),
                  ),
                ],
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CustomNetworkImage(
                    url: master.img ?? "",
                    height: 32,
                    width: 32,
                    radius: 4,
                  ),
                  8.horizontalSpace,
                  Text(
                    master.firstname ?? "",
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 16,
                    ),
                  ),
                ],
              ),
      ),
    );
  }

  ButtonEffectAnimation _addTime(
    BuildContext context,
    BookingState state,
    int index,
    CustomColorSet colors,
    MasterModel? master,
  ) {
    return ButtonEffectAnimation(
      onTap: () {
        AppRouteService.goSelectTomeBottomSheet(
          context,
          selectService: master?.serviceMaster?.id,
          title: state.selectServices[index].translation?.title ?? "",
          shopId: shopId,
          colors: colors,
        );
      },
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(10.r),
          border: Border.all(color: colors.icon),
        ),
        padding: EdgeInsets.symmetric(horizontal: 12.r, vertical: 8.r),
        child: master?.time == null
            ? Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    FlutterRemix.calendar_2_line,
                    color: colors.textBlack,
                    size: 21.r,
                  ),
                  8.horizontalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.selectTime),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 16,
                    ),
                  ),
                ],
              )
            : Column(
                mainAxisSize: MainAxisSize.min,
                spacing: 4.r,
                children: [
                  Text(
                    TimeService.dateFormatDMY(master?.time),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 14,
                    ),
                  ),
                  Text(
                    "${TimeService.timeFormat(master?.time)} - ${TimeService.timeFormat(master?.time?.add(Duration(minutes: (master.serviceMaster?.interval?.toInt() ?? 0) + (master.serviceMaster?.pause?.toInt() ?? 0))))}",
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 14,
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}
