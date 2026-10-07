import 'dart:ui';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/brand/brand_bloc.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/application/story/story_bloc.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/home/widgets/masters_list.dart';
import 'package:demand/presentation/pages/home/widgets/new_shop.dart';
import 'package:demand/presentation/pages/home/widgets/story_list.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/banner/banner_bloc.dart';
import 'package:demand/application/blog/blog_bloc.dart';
import 'package:demand/application/notification/notification_bloc.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';

import 'package:demand/presentation/pages/home/widgets/upcoming_list.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import 'widgets/all_shop_list.dart';
import 'widgets/blog_list.dart';
import 'widgets/brands_list.dart';
import 'widgets/near_shops_list.dart';
import 'widgets/popular_shop.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  late RefreshController shopRefresh;
  late RefreshController storyRefresh;
  late RefreshController brandRefresh;
  late RefreshController bannerRefresh;
  late RefreshController masterRefresh;
  late PageController pageController;
  bool _isRefreshing = false;

  @override
  void initState() {
    super.initState();
    shopRefresh = RefreshController();
    storyRefresh = RefreshController();
    brandRefresh = RefreshController();
    bannerRefresh = RefreshController();
    masterRefresh = RefreshController();
    pageController = PageController();
  }

  @override
  void dispose() {
    shopRefresh.dispose();
    storyRefresh.dispose();
    brandRefresh.dispose();
    bannerRefresh.dispose();
    masterRefresh.dispose();
    pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      appBar: (colors) => _buildAppBar(colors),
      body: (colors) {
        return SmartRefresher(
          controller: shopRefresh,
          enablePullUp: true,
          onRefresh: () => _handleRefresh(context),
          onLoading: () => _handleLoading(context),
          child: ListView(
            padding: EdgeInsets.symmetric(vertical: 16.r),
            children: [
              if (LocalStorage.getUser().firstname != null) _hello(colors),
              UpComingList(colors: colors),
              StoryList(storyRefresh: storyRefresh, colors: colors),
              ShopsPopularList(colors: colors),
              NearShopsList(colors: colors),
              MastersList(
                colors: colors,
                controller: masterRefresh,
                onLoading: () {
                  if (!mounted) return;
                  context.read<MasterBloc>().add(
                    MasterEvent.fetchMasters(
                      context,
                      controller: masterRefresh,
                    ),
                  );
                },
              ),
              BrandsList(brandRefresh: brandRefresh, colors: colors),
              NewShopList(colors: colors),
              BlogList(colors: colors),
              AllShopList(colors: colors),
              80.verticalSpace,
            ],
          ),
        );
      },
    );
  }

  void _handleRefresh(BuildContext context) {
    if (_isRefreshing) return;

    setState(() {
      _isRefreshing = true;
    });

    // Fetch stories
    context.read<StoryBloc>().add(
      StoryEvent.fetchStory(
        context,
        isRefresh: true,
        controller: shopRefresh,
      ),
    );

    // Fetch all shop types
    final shopBloc = context.read<ShopBloc>();
    shopBloc
      ..add(
        ShopEvent.fetchShops(
          context,
          isRefresh: true,
          controller: shopRefresh,
        ),
      )
      ..add(
        ShopEvent.fetchNewShops(
          context,
          isRefresh: true,
          controller: shopRefresh,
        ),
      )
      ..add(
        ShopEvent.fetchPopularShops(
          context,
          isRefresh: true,
          controller: shopRefresh,
        ),
      )
      ..add(
        ShopEvent.fetchNearShops(
          context,
          isRefresh: true,
          controller: shopRefresh,
          location: LocalStorage.getLocation(),
          onSuccess: () {},
        ),
      );

    // Fetch blogs
    context.read<BlogBloc>().add(
      BlogEvent.fetchBlog(
        context,
        isRefresh: true,
        controller: shopRefresh,
      ),
    );

    // Fetch brands
    context.read<BrandBloc>().add(
      BrandEvent.fetchBrands(
        context,
        isRefresh: true,
        controller: shopRefresh,
      ),
    );

    // Fetch banners
    final bannerBloc = context.read<BannerBloc>();
    bannerBloc
      ..add(
        BannerEvent.fetchBanner(
          context,
          isRefresh: true,
          controller: shopRefresh,
        ),
      )
      ..add(
        BannerEvent.fetchAdsBanner(
          context,
          isRefresh: true,
          controller: shopRefresh,
        ),
      );

    // Fetch masters
    context.read<MasterBloc>().add(
      MasterEvent.fetchMasters(
        context,
        isRefresh: true,
        controller: shopRefresh,
      ),
    );

    // Fetch upcoming bookings if user is logged in
    if (LocalStorage.getToken().isNotEmpty) {
      context.read<BookingBloc>().add(
        BookingEvent.fetchBookUpcoming(
          context,
          isRefresh: true,
          controller: shopRefresh,
        ),
      );
    }

    shopRefresh.resetNoData();

    // Reset refreshing state after a delay
    Future.delayed(const Duration(milliseconds: 500), () {
      if (mounted) {
        setState(() {
          _isRefreshing = false;
        });
      }
    });
  }

  void _handleLoading(BuildContext context) {
    if (!mounted) return;

    context.read<ShopBloc>().add(
      ShopEvent.fetchShops(context, controller: shopRefresh),
    );
  }

  Widget _hello(CustomColorSet colors) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: BlocBuilder<ProfileBloc, ProfileState>(
            builder: (context, state) {
              return Text(
                "${AppHelpers.getTranslation(TrKeys.hello)} 👋 \n${LocalStorage.getUser().firstname} ${LocalStorage.getUser().lastname ?? ""}",
                style: CustomStyle.interNoSemi(
                  color: colors.textBlack,
                  size: 32,
                ),
              );
            },
          ),
        ),
        24.verticalSpace,
      ],
    );
  }

  AppBar _buildAppBar(CustomColorSet colors) {
    return AppBar(
      backgroundColor: colors.backgroundColor.withValues(alpha: 0.8),
      elevation: 0.0,
      flexibleSpace: ClipRect(
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  colors.backgroundColor.withValues(alpha: 0.8),
                  colors.backgroundColor.withValues(alpha: 0.6),
                ],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
          ),
        ),
      ),
      actions: [
        Container(
          margin: EdgeInsets.only(right: 8.r),
          decoration: BoxDecoration(
            color: colors.newBoxColor,
            borderRadius: BorderRadius.circular(12.r),
          ),
          child: IconButton(
            onPressed: () {
              AppRouteSetting.goNotification(context);
            },
            icon: Badge(
              label: (LocalStorage.getToken().isEmpty)
                  ? const Text("0")
                  : BlocBuilder<NotificationBloc, NotificationState>(
                      builder: (context, state) {
                        return Text(
                          state.countOfNotifications?.notification.toString() ??
                              "0",
                        );
                      },
                    ),
              child: Icon(
                FlutterRemix.notification_line,
                color: colors.textBlack,
                size: 22.r,
              ),
            ),
          ),
        ),
      ],
      leading: Container(
        margin: EdgeInsets.only(left: 8.r),
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.circular(12.r),
        ),
        child: IconButton(
          onPressed: () {
            Scaffold.of(context).openDrawer();
          },
          icon: Icon(FlutterRemix.menu_line, color: colors.textBlack, size: 22.r),
        ),
      ),
      title: Text(
        AppHelpers.getAppName(),
        style: CustomStyle.interSemi(color: colors.textBlack, size: 16),
      ),
    );
  }
}
