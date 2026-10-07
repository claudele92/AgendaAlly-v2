import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../../application/booking/booking_bloc.dart';
import '../../../application/service/service_bloc.dart';
import '../../../domain/model/models.dart';
import '../../../infrastructure/service/services.dart';
import '../../components/components.dart';
import '../../components/service_category_item.dart';
import '../../route/app_route_service.dart';
import '../../style/style.dart';
import 'widget/service_tab_view.dart';

class ServiceListPage extends StatefulWidget {
  final int? shopId;
  final MasterModel? master;

  const ServiceListPage({super.key, this.shopId, this.master});

  @override
  State<ServiceListPage> createState() => _ServiceListPageState();
}

class _ServiceListPageState extends State<ServiceListPage>
    with TickerProviderStateMixin {
  late RefreshController refreshController;
  num totalPrice = 0;
  num duration = 0;
  int service = 0;

  @override
  void initState() {
    refreshController = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    refreshController.dispose();
    super.dispose();
  }

  String formatDuration(Duration duration) {
    final int hours = duration.inHours;
    final int minutes = duration.inMinutes % 60;

    // Handle pluralization for minutes

    if (hours > 0) {
      // Show hours and minutes
      return "${AppHelpers.getTranslation(TrKeys.time)} : $hours ${AppHelpers.getTranslation(TrKeys.hour)} $minutes ${AppHelpers.getTranslation(TrKeys.minute)}";
    } else {
      // Show only minutes
      return "${AppHelpers.getTranslation(TrKeys.time)} : $minutes ${AppHelpers.getTranslation(TrKeys.minute)}";
    }
  }

  @override
  Widget build(BuildContext context) {
    final event = context.read<ServiceBloc>();
    return CustomScaffold(
      body: (colors) => SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.start,
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
            20.verticalSpace,
            Expanded(
              child: BlocConsumer<ServiceBloc, ServiceState>(
                listener: (context, state) {
                  totalPrice = 0;
                  duration = 0;
                  service = 0;
                  if (state.selectServices.isEmpty) {
                    return;
                  }
                  for (var element in state.selectServices) {
                    totalPrice += (element.totalPrice ?? 0);
                    duration += (element.interval ?? 0);
                    for (ServiceExtra e in element.selectExtras ?? []) {
                      totalPrice += (e.price ?? 0);
                    }
                  }
                  service = state.selectServices.length;
                },
                builder: (context, state) {
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.start,
                    children: [
                      SizedBox(
                        height: 44.r,
                        child: SmartRefresher(
                          controller: refreshController,
                          scrollDirection: Axis.horizontal,
                          enablePullUp: true,
                          enablePullDown: false,
                          onLoading: () {
                            event.add(
                              ServiceEvent.fetchServiceCategories(
                                context,
                                controller: refreshController,
                              ),
                            );
                          },
                          child: ListView.builder(
                            padding: EdgeInsets.symmetric(horizontal: 16.r),
                            scrollDirection: Axis.horizontal,
                            itemCount: state.serviceCategories.length + 1,
                            itemBuilder: (context, index) {
                              return index == 0
                                  ? ButtonEffectAnimation(
                                      onTap: () {
                                        event.add(
                                          ServiceEvent.selectServiceCategory(
                                            category: null,
                                            isRefresh: true,
                                            shopId: widget.shopId,
                                            controller: refreshController,
                                          ),
                                        );
                                      },
                                      child: Container(
                                        margin: EdgeInsets.only(right: 10.r),
                                        padding: EdgeInsets.symmetric(
                                          horizontal: 16.r,
                                        ),
                                        height: 40.r,
                                        decoration: BoxDecoration(
                                          color: state.selectedCategory == null
                                              ? colors.textBlack
                                              : colors.transparent,
                                          border: Border.all(
                                            color: colors.textBlack,
                                          ),
                                          borderRadius: BorderRadius.circular(
                                            10.r,
                                          ),
                                        ),
                                        child: Row(
                                          children: [
                                            Text(
                                              AppHelpers.getTranslation(
                                                TrKeys.all,
                                              ),
                                              style: CustomStyle.interRegular(
                                                color:
                                                    state.selectedCategory ==
                                                        null
                                                    ? colors.textWhite
                                                    : colors.textBlack,
                                                size: 16,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    )
                                  : ServiceCategoryItem(
                                      category:
                                          state.serviceCategories[index - 1],
                                      select:
                                          state.serviceCategories[index - 1] ==
                                          state.selectedCategory,
                                      selectedCategory:
                                          state.selectedChildCategory ??
                                          state.selectedCategory,
                                      colors: colors,
                                      onTapParent: () {
                                        event.add(
                                          ServiceEvent.selectServiceCategory(
                                            category: state
                                                .serviceCategories[index - 1],
                                            isRefresh: true,
                                            shopId: widget.shopId,
                                            controller: refreshController,
                                          ),
                                        );
                                      },
                                      onTapChild: (CategoryData? value) {
                                        event.add(
                                          ServiceEvent.selectServiceCategoryChild(
                                            category: value,
                                            parent: state
                                                .serviceCategories[index - 1],
                                            isRefresh: true,
                                            shopId: widget.shopId,
                                            controller: refreshController,
                                          ),
                                        );
                                      },
                                    );
                            },
                          ),
                        ),
                      ),
                      Expanded(
                        child: state.selectedChildCategory != null
                            ? ServiceTabView(
                                key: ValueKey<int>(
                                  state.selectedChildCategory?.id ?? 0,
                                ),
                                shopId: widget.shopId,
                                colors: colors,
                                masterId: widget.master?.id,
                                categoryId: state.selectedChildCategory?.id,
                              )
                            : state.selectedCategory != null
                            ? ServiceTabView(
                                key: ValueKey<int>(
                                  state.selectedCategory?.id ?? 0,
                                ),
                                shopId: widget.shopId,
                                colors: colors,
                                masterId: widget.master?.id,
                                categoryId: state.selectedCategory?.id,
                              )
                            : ServiceTabView(
                                colors: colors,
                                shopId: widget.shopId,
                                masterId: widget.master?.id,
                              ),
                      ),
                    ],
                  );
                },
              ),
            ),
          ],
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) => Padding(
        padding: EdgeInsets.symmetric(horizontal: 16.r),
        child: Row(
          children: [
            PopButton(colors: colors),
            BlocBuilder<ServiceBloc, ServiceState>(
              buildWhen: (p, n) {
                return p.selectServices != n.selectServices;
              },
              builder: (context, state) {
                return (service != 0)
                    ? Expanded(
                        child: ButtonEffectAnimation(
                          onTap: () {
                            if (widget.master != null) {
                              context.read<BookingBloc>().add(
                                BookingEvent.setServices(
                                  services: state.selectServices,
                                  master: widget.master,
                                  onSuccess: () {
                                    AppRouteService.goSelectMaster(
                                      context,
                                      shopId: widget.shopId,
                                    );
                                  },
                                  onFailure: () {
                                    AppHelpers.errorSnackBar(
                                      context,
                                      message: AppHelpers.getTranslation(
                                        TrKeys.selectedMasterDosntSupport,
                                      ),
                                    );
                                  },
                                ),
                              );
                              return;
                            }
                            AppRouteService.goSelectOptionsMaster(
                              context,
                              services: state.selectServices,
                              title: AppHelpers.getTranslation(
                                TrKeys.selectMaster,
                              ),
                              shopId: widget.shopId,
                              colors: colors,
                            );
                            // AppRouteService.goSelectMaster(context,//
                            //     shopId: widget.shopId,
                            //     services: state.selectServices);
                          },
                          child: Container(
                            margin: EdgeInsets.only(left: 16.r),
                            height: 64.r,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10.r),
                              color: colors.primary,
                            ),
                            padding: EdgeInsets.symmetric(horizontal: 16.r),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  "${AppHelpers.getTranslation(TrKeys.from)} ${AppHelpers.numberFormat(totalPrice)}",
                                  style: CustomStyle.interNormal(
                                    color: colors.textWhite,
                                    size: 16,
                                  ),
                                ),
                                Text(
                                  "$service ${AppHelpers.getTranslation(TrKeys.services)} - ${formatDuration(Duration(minutes: duration.toInt()))}",
                                  style: CustomStyle.interRegular(
                                    color: colors.textWhite,
                                    size: 16,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      )
                    : const SizedBox();
              },
            ),
          ],
        ),
      ),
    );
  }
}
