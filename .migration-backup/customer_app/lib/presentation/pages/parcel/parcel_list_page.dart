import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/parcels_list/parcels_list_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:lottie/lottie.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../app_assets.dart';
import 'parcel_item.dart';

class ParcelListPage extends StatefulWidget {
  const ParcelListPage({super.key});

  @override
  State<ParcelListPage> createState() => _ParcelListPageState();
}

class _ParcelListPageState extends State<ParcelListPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  late RefreshController activeRefreshController;
  late RefreshController historyRefreshController;

  final _tabs = [
    Tab(text: AppHelpers.getTranslation(TrKeys.activeParcel)),
    Tab(text: AppHelpers.getTranslation(TrKeys.parcelHistory)),
  ];

  @override
  void initState() {
    activeRefreshController = RefreshController();
    historyRefreshController = RefreshController();
    _tabController = TabController(length: 2, vsync: this);
    super.initState();
  }

  @override
  void dispose() {
    _tabController.dispose();
    activeRefreshController.dispose();
    historyRefreshController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: CustomScaffold(
        body: (colors) => SafeArea(
          bottom: false,
          child: Column(
            children: [
              Text(
                AppHelpers.getTranslation(TrKeys.parcels),
                style: CustomStyle.interNoSemi(
                  color: colors.textBlack,
                  size: 18,
                ),
              ),
              16.verticalSpace,
              Expanded(
                child: Column(
                  children: [
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16.r),
                      child: CustomTabBar(
                        isScrollable: false,
                        tabController: _tabController,
                        tabs: _tabs,
                      ),
                    ),
                    BlocBuilder<ParcelsListBloc, ParcelsListState>(
                      builder: (context, state) {
                        return Expanded(
                          child: TabBarView(
                            controller: _tabController,
                            children: [
                              state.isActiveLoading
                                  ? const Loading()
                                  : SmartRefresher(
                                      controller: activeRefreshController,
                                      enablePullDown: true,
                                      enablePullUp: true,
                                      onRefresh: () {
                                        context.read<ParcelsListBloc>().add(
                                          ParcelsListEvent.fetchActiveParcel(
                                            context,
                                            controller: activeRefreshController,
                                            isRefresh: true,
                                          ),
                                        );
                                      },
                                      onLoading: () {
                                        ParcelsListEvent.fetchActiveParcel(
                                          context,
                                          controller: activeRefreshController,
                                        );
                                      },
                                      child: state.activeOrders.isNotEmpty
                                          ? ListView.separated(
                                              padding: EdgeInsets.only(
                                                top: 24.r,
                                              ),
                                              shrinkWrap: true,
                                              itemCount:
                                                  state.activeOrders.length,
                                              itemBuilder: (context, index) {
                                                return ParcelItem(
                                                  index: index,
                                                  colors: colors,
                                                  parcel:
                                                      state.activeOrders[index],
                                                );
                                              },
                                              separatorBuilder:
                                                  (
                                                    BuildContext context,
                                                    int index,
                                                  ) {
                                                    return Padding(
                                                      padding:
                                                          EdgeInsets.symmetric(
                                                            horizontal: 16.r,
                                                          ),
                                                      child: const Divider(),
                                                    );
                                                  },
                                            )
                                          : _resultEmpty(colors),
                                    ),
                              state.isHistoryLoading
                                  ? const Loading()
                                  : SmartRefresher(
                                      controller: historyRefreshController,
                                      enablePullDown: true,
                                      enablePullUp: true,
                                      onRefresh: () {
                                        context.read<ParcelsListBloc>().add(
                                          ParcelsListEvent.fetchHistoryParcel(
                                            context,
                                            controller:
                                                historyRefreshController,
                                            isRefresh: true,
                                          ),
                                        );
                                      },
                                      onLoading: () {
                                        context.read<ParcelsListBloc>().add(
                                          ParcelsListEvent.fetchHistoryParcel(
                                            context,
                                            controller:
                                                historyRefreshController,
                                          ),
                                        );
                                      },
                                      child: state.historyOrders.isNotEmpty
                                          ? ListView.separated(
                                              padding: EdgeInsets.only(
                                                top: 24.r,
                                              ),
                                              shrinkWrap: true,
                                              itemCount:
                                                  state.historyOrders.length,
                                              itemBuilder: (context, index) {
                                                return ParcelItem(
                                                  index: index,
                                                  colors: colors,
                                                  parcel: state
                                                      .historyOrders[index],
                                                );
                                              },
                                              separatorBuilder:
                                                  (
                                                    BuildContext context,
                                                    int index,
                                                  ) {
                                                    return Padding(
                                                      padding:
                                                          EdgeInsets.symmetric(
                                                            horizontal: 16.r,
                                                          ),
                                                      child: const Divider(),
                                                    );
                                                  },
                                            )
                                          : _resultEmpty(colors),
                                    ),
                            ],
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        floatingButtonLocation: FloatingActionButtonLocation.startFloat,
        floatingButton: (colors) => PopButton(colors: colors),
      ),
    );
  }
}

Widget _resultEmpty(CustomColorSet colors) {
  return Column(
    children: [
      24.verticalSpace,
      Lottie.asset(Assets.lottieNotificationEmpty),
      16.verticalSpace,
      Padding(
        padding: EdgeInsets.symmetric(horizontal: 32.w),
        child: Text(
          AppHelpers.getTranslation(TrKeys.thereIsNothingHere),
          style: CustomStyle.interNoSemi(size: 16, color: colors.textBlack),
          textAlign: TextAlign.center,
        ),
      ),
    ],
  );
}
