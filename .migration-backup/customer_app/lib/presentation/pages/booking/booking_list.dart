import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:lottie/lottie.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../app_assets.dart';
import 'widget/booking_item.dart';

class BookingListPage extends StatefulWidget {
  const BookingListPage({super.key});

  @override
  State<BookingListPage> createState() => _BookingListPageState();
}

class _BookingListPageState extends State<BookingListPage>
    with SingleTickerProviderStateMixin {
  late RefreshController upcomingRefresh;
  late RefreshController historyRefresh;
  late TabController tabController;

  List<Tab> listTabs = [
    Tab(child: Text(AppHelpers.getTranslation(TrKeys.upcoming))),
    Tab(child: Text(AppHelpers.getTranslation(TrKeys.past))),
  ];

  @override
  void initState() {
    tabController = TabController(length: 2, vsync: this);
    upcomingRefresh = RefreshController();
    historyRefresh = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    upcomingRefresh.dispose();
    historyRefresh.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        bottom: false,
        child: Column(
          children: [
            Text(
              AppHelpers.getTranslation(TrKeys.appointments),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
            24.verticalSpace,
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: CustomTabBar(tabController: tabController, tabs: listTabs),
            ),
            BlocBuilder<BookingBloc, BookingState>(
              builder: (context, state) {
                return Expanded(
                  child: TabBarView(
                    controller: tabController,
                    children: [
                      state.isLoading
                          ? const Loading()
                          : state.upcoming.isEmpty
                          ? _resultEmpty(colors)
                          : _upcoming(context, state, colors),
                      state.isLoading
                          ? const Loading()
                          : state.past.isEmpty
                          ? _resultEmpty(colors)
                          : _history(context, state, colors),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  SmartRefresher _upcoming(
    BuildContext context,
    BookingState state,
    CustomColorSet colors,
  ) {
    return SmartRefresher(
      controller: upcomingRefresh,
      enablePullUp: true,
      onRefresh: () {
        context.read<BookingBloc>().add(
          BookingEvent.fetchBookUpcoming(
            context,
            isRefresh: true,
            controller: upcomingRefresh,
          ),
        );
      },
      onLoading: () {
        context.read<BookingBloc>().add(
          BookingEvent.fetchBookUpcoming(context, controller: upcomingRefresh),
        );
      },
      child: ListView.builder(
        padding: EdgeInsets.only(
          top: 24.r,
          left: 16.r,
          right: 16.r,
          bottom: 100.r,
        ),
        shrinkWrap: true,
        itemCount: state.upcoming.length,
        itemBuilder: (context, index) {
          return BookingItem(book: state.upcoming[index], colors: colors);
        },
      ),
    );
  }

  SmartRefresher _history(
    BuildContext context,
    BookingState state,
    CustomColorSet colors,
  ) {
    return SmartRefresher(
      controller: historyRefresh,
      enablePullUp: true,
      onRefresh: () {
        context.read<BookingBloc>().add(
          BookingEvent.fetchBookPast(
            context,
            isRefresh: true,
            controller: historyRefresh,
          ),
        );
      },
      onLoading: () {
        context.read<BookingBloc>().add(
          BookingEvent.fetchBookPast(context, controller: historyRefresh),
        );
      },
      child: ListView.builder(
        padding: EdgeInsets.only(
          top: 24.r,
          left: 16.r,
          right: 16.r,
          bottom: 100.r,
        ),
        shrinkWrap: true,
        itemCount: state.past.length,
        itemBuilder: (context, index) {
          return BookingItem(
            book: state.past[index],
            colors: colors,
            past: true,
          );
        },
      ),
    );
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
}
