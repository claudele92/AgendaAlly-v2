import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../../application/service/direct/direct_service_bloc.dart';
import '../../../infrastructure/local_storage/local_storage.dart';
import '../../../infrastructure/service/services.dart';
import '../../components/components.dart';
import '../../route/app_route_service.dart';
import '../../style/style.dart';
import '../../style/theme/theme.dart';

class DirectServiceBottomSheet extends StatelessWidget {
  final CustomColorSet colors;
  final ScrollController controller;
  final int? shopId;

  const DirectServiceBottomSheet({
    super.key,
    required this.colors,
    required this.controller,
    this.shopId,
  });

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: Container(
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: REdgeInsets.only(left: 16, right: 16),
        child: BlocBuilder<DirectServiceBloc, DirectServiceState>(
          builder: (context, state) {
            num price = (state.service?.totalPrice ?? 0);
            for (var e in state.selectedExtras) {
              price += e.price ?? 0;
            }
            return state.isLoading
                ? Loading()
                : ListView(
                    controller: controller,
                    padding: EdgeInsets.only(top: 8.r, bottom: 16.r),
                    children: [
                      Container(
                        height: 4.r,
                        margin: EdgeInsets.symmetric(
                          horizontal:
                              MediaQuery.sizeOf(context).width / 2 - 48.r,
                        ),
                        decoration: BoxDecoration(
                          color: colors.icon,
                          borderRadius: BorderRadius.circular(100.r),
                        ),
                      ),
                      28.verticalSpace,
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              '${state.service?.translation?.title}',
                              style: CustomStyle.interNoSemi(
                                color: colors.textBlack,
                                size: 22,
                              ),
                            ),
                          ),
                          // const Spacer(),
                          Text(
                            AppHelpers.numberFormat(state.service?.totalPrice),
                            style: CustomStyle.interNoSemi(
                              color: colors.textBlack,
                              size: 18,
                            ),
                          ),
                        ],
                      ),
                      20.verticalSpace,
                      Wrap(
                        children: [
                          Container(
                            margin: EdgeInsets.only(right: 8.r),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(50.r),
                              border: Border.all(color: colors.textHint),
                            ),
                            padding: EdgeInsets.symmetric(
                              horizontal: 16.r,
                              vertical: 8.r,
                            ),
                            child: Text(
                              "${AppHelpers.getTranslation(TrKeys.from)} ${AppHelpers.numberFormat(state.service?.totalPrice)}",
                              style: CustomStyle.interNormal(
                                color: colors.textHint,
                                size: 12,
                              ),
                            ),
                          ),
                          Container(
                            margin: EdgeInsets.only(right: 8.r),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(50.r),
                              border: Border.all(color: colors.textHint),
                            ),
                            padding: EdgeInsets.symmetric(
                              horizontal: 16.r,
                              vertical: 8.r,
                            ),
                            child: Text(
                              "${state.service?.interval ?? 0} ${AppHelpers.getTranslation(TrKeys.minute)}",
                              style: CustomStyle.interNormal(
                                color: colors.textHint,
                                size: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                      24.verticalSpace,
                      CustomReadMoreText(
                        '${state.service?.translation?.description}',
                        trimLines: 4,
                        colors: colors,
                      ),
                      12.verticalSpace,
                      ListView.builder(
                        padding: EdgeInsets.zero,
                        itemCount: state.service?.extras?.length ?? 0,
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemBuilder: (context, index) {
                          return Padding(
                            padding: REdgeInsets.only(bottom: 8),
                            child: GestureDetector(
                              onTap: () {
                                context.read<DirectServiceBloc>().add(
                                  DirectServiceEvent.selectExtraIndex(
                                    extra: state.service?.extras?[index],
                                  ),
                                );
                              },
                              child: Container(
                                padding: REdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 12,
                                ),
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(10.r),
                                  border: Border.all(
                                    color:
                                        state.selectedExtras.any(
                                          (e) =>
                                              state
                                                  .service
                                                  ?.extras?[index]
                                                  .id ==
                                              e.id,
                                        )
                                        ? colors.textBlack
                                        : colors.textHint,
                                    width:
                                        state.selectedExtras.any(
                                          (e) =>
                                              state
                                                  .service
                                                  ?.extras?[index]
                                                  .id ==
                                              e.id,
                                        )
                                        ? 1.2
                                        : 1,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            '${state.service?.extras?[index].translation?.title}',
                                            style: CustomStyle.interRegular(
                                              color: colors.textBlack,
                                              size: 16,
                                            ),
                                          ),
                                          Text(
                                            AppHelpers.numberFormat(
                                              state
                                                  .service
                                                  ?.extras?[index]
                                                  .price,
                                            ),
                                            style: CustomStyle.interRegular(
                                              color: colors.textBlack,
                                              size: 16,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    if (state.selectedExtras.any(
                                      (e) =>
                                          state.service?.extras?[index].id ==
                                          e.id,
                                    ))
                                      Container(
                                        height: 18.r,
                                        width: 18.r,
                                        decoration: BoxDecoration(
                                          border: Border.all(
                                            color: colors.textBlack,
                                            width: 6.r,
                                          ),
                                          shape: BoxShape.circle,
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                      26.verticalSpace,
                      Row(
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                AppHelpers.getTranslation(TrKeys.price),
                                style: CustomStyle.interNormal(
                                  color: colors.textHint,
                                  size: 14,
                                ),
                              ),
                              Text(
                                AppHelpers.numberFormat(price),
                                style: CustomStyle.interNoSemi(
                                  color: colors.textBlack,
                                  size: 26,
                                ),
                              ),
                            ],
                          ),
                          24.horizontalSpace,
                          Expanded(
                            child: CustomButton(
                              title: AppHelpers.getTranslation(TrKeys.bookNow),
                              bgColor: colors.textBlack,
                              titleColor: colors.textWhite,
                              onTap: () {
                                AppRouteService.goSelectOptionsMaster(
                                  context,
                                  services: [state.service!],
                                  title: AppHelpers.getTranslation(
                                    TrKeys.selectMaster,
                                  ),
                                  shopId: shopId,
                                  colors: colors,
                                );
                              },
                            ),
                          ),
                        ],
                      ),
                    ],
                  );
          },
        ),
      ),
    );
  }
}
