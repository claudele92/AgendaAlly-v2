import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../../../application/service/service_bloc.dart';
import '../../../../domain/model/models.dart';
import '../../../../infrastructure/service/services.dart';
import '../../../components/components.dart';
import '../../../components/service_category_item.dart';
import '../../../route/app_route_service.dart';
import '../../../style/style.dart';
import '../../../style/theme/theme.dart';

class ServicesWidget extends StatefulWidget {
  final CustomColorSet colors;
  final int shopId;

  const ServicesWidget({super.key, required this.colors, required this.shopId});

  @override
  State<ServicesWidget> createState() => _ServicesWidgetState();
}

class _ServicesWidgetState extends State<ServicesWidget> {
  late RefreshController refreshController;

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

  @override
  Widget build(BuildContext context) {
    final event = context.read<ServiceBloc>();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        24.verticalSpace,
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Text(
            AppHelpers.getTranslation(TrKeys.services),
            style: CustomStyle.interNoSemi(
              color: widget.colors.textBlack,
              size: 22,
            ),
          ),
        ),
        20.verticalSpace,
        BlocBuilder<ServiceBloc, ServiceState>(
          builder: (context, state) {
            return SizedBox(
              height: 40.r,
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
                              padding: EdgeInsets.symmetric(horizontal: 16.r),
                              height: 40.r,
                              decoration: BoxDecoration(
                                color: state.selectedCategory == null
                                    ? widget.colors.textBlack
                                    : widget.colors.transparent,
                                border: Border.all(
                                  color: widget.colors.textBlack,
                                ),
                                borderRadius: BorderRadius.circular(10.r),
                              ),
                              child: Row(
                                children: [
                                  Text(
                                    AppHelpers.getTranslation(TrKeys.all),
                                    style: CustomStyle.interRegular(
                                      color: state.selectedCategory == null
                                          ? widget.colors.textWhite
                                          : widget.colors.textBlack,
                                      size: 16,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          )
                        : ServiceCategoryItem(
                            category: state.serviceCategories[index - 1],
                            select:
                                state.serviceCategories[index - 1] ==
                                state.selectedCategory,
                            selectedCategory:
                                state.selectedChildCategory ??
                                state.selectedCategory,
                            colors: widget.colors,
                            onTapParent: () {
                              event.add(
                                ServiceEvent.selectServiceCategory(
                                  category: state.serviceCategories[index - 1],
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
                                  parent: state.serviceCategories[index - 1],
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
            );
          },
        ),
        BlocBuilder<ServiceBloc, ServiceState>(
          builder: (context, state) {
            return state.isLoading
                ? Padding(
                    padding: REdgeInsets.symmetric(vertical: 16),
                    child: SizedBox(height: 400.r, child: Loading()),
                  )
                : Column(
                    children: [
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        padding: EdgeInsets.symmetric(horizontal: 16.r),
                        itemCount: state.services.length,
                        itemBuilder: (context, index) {
                          return ServiceItem(
                            shopId: widget.shopId,
                            colors: widget.colors,
                            booked: state.selectServices.any(
                              (e) => e.id == state.services[index].id,
                            ),
                            service: state.services[index],
                            bookButton: false,
                          );
                        },
                      ),
                      16.verticalSpace,
                      Padding(
                        padding: EdgeInsets.symmetric(horizontal: 16.r),
                        child: CustomButton(
                          title: AppHelpers.getTranslation(TrKeys.viewAll),
                          bgColor: widget.colors.transparent,
                          titleColor: widget.colors.textBlack,
                          borderColor: widget.colors.textBlack,
                          onTap: () {
                            AppRouteService.goServiceListPage(
                              context,
                              shopId: widget.shopId,
                            );
                          },
                        ),
                      ),
                    ],
                  );
          },
        ),
      ],
    );
  }
}
