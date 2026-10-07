import 'package:demand/application/story/story_bloc.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/application/banner/banner_bloc.dart';
import 'package:demand/application/blog/blog_bloc.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/brand/brand_bloc.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/application/notification/notification_bloc.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/pages/home/widgets/all_shop_list.dart';
import 'package:demand/presentation/pages/home/widgets/banner_list.dart';
import 'package:demand/presentation/pages/home/widgets/blog_list.dart';
import 'package:demand/presentation/pages/home/widgets/new_shop.dart';
import 'package:demand/presentation/pages/home/widgets/popular_shop.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../../app_assets.dart';
import '../home_three/widgets/story_three_list.dart';
import 'widgets/category_one_list.dart';
import 'widgets/for_you_and_discount.dart';
import 'widgets/upcoming_one_list.dart';

class HomeOnePage extends StatefulWidget {
  const HomeOnePage({super.key});

  @override
  State<HomeOnePage> createState() => _HomeOnePageState();
}

class _HomeOnePageState extends State<HomeOnePage> {
  late RefreshController categoryRefresh;
  late RefreshController shopRefresh;
  late RefreshController looksRefresh;
  late RefreshController adsRefresh;
  late RefreshController bannerRefresh;
  late PageController pageController;
  late ScrollController scrollController;
  late RefreshController storyRefresh;

  void listen() {
    final direction = scrollController.position.userScrollDirection;
    if (direction == ScrollDirection.reverse) {
      context.read<MainBloc>().add(const MainEvent.showSearch(value: false));
    } else if (direction == ScrollDirection.forward) {
      context.read<MainBloc>().add(const MainEvent.showSearch(value: true));
    }
  }

  @override
  void initState() {
    categoryRefresh = RefreshController();
    shopRefresh = RefreshController();
    looksRefresh = RefreshController();
    adsRefresh = RefreshController();
    bannerRefresh = RefreshController();
    pageController = PageController();
    scrollController = ScrollController();
    storyRefresh = RefreshController();
    scrollController.addListener(listen);
    super.initState();
  }

  @override
  void dispose() {
    categoryRefresh.dispose();
    shopRefresh.dispose();
    pageController.dispose();
    looksRefresh.dispose();
    adsRefresh.dispose();
    bannerRefresh.dispose();
    scrollController.removeListener(listen);
    scrollController.dispose();
    storyRefresh.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      appBar: (colors) => _buildAppBar(colors),
      body: (colors) {
        return Column(
          children: [
            16.verticalSpace,
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: BlocBuilder<MainBloc, MainState>(
                buildWhen: (p, n) {
                  return p.isShowSearch != n.isShowSearch;
                },
                builder: (context, state) {
                  return AnimatedContainer(
                    duration: const Duration(milliseconds: 500),
                    height: state.isShowSearch ? 50.r : 0,
                    child: state.isShowSearch
                        ? CustomTextFormField(
                            onTap: () {
                              AppRoute.goSearchPage(context);
                            },
                            readOnly: true,
                            radius: 16,
                            prefixIcon: const Icon(
                              FlutterRemix.search_2_line,
                              color: CustomStyle.textHint,
                            ),
                            hint: AppHelpers.getTranslation(TrKeys.search),
                          )
                        : const SizedBox.shrink(),
                  );
                },
              ),
            ),
            Expanded(
              child: SmartRefresher(
                scrollController: scrollController,
                controller: shopRefresh,
                enablePullUp: true,
                onRefresh: () {
                  context.read<CategoryBloc>().add(
                    CategoryEvent.fetchCategoryOfService(
                      context,
                      isRefresh: true,
                      controller: shopRefresh,
                    ),
                  );
                  context.read<ProductBloc>()
                    ..add(
                      ProductEvent.fetchMostSaleProduct(
                        context,
                        isRefresh: true,
                        controller: shopRefresh,
                      ),
                    )
                    ..add(
                      ProductEvent.fetchNewProduct(
                        context,
                        isRefresh: true,
                        controller: shopRefresh,
                      ),
                    )
                    ..add(
                      ProductEvent.fetchAllProduct(
                        context,
                        isRefresh: true,
                        controller: shopRefresh,
                      ),
                    );
                  context.read<ShopBloc>()
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
                    );
                  context.read<BlogBloc>().add(
                    BlogEvent.fetchBlog(
                      context,
                      isRefresh: true,
                      controller: shopRefresh,
                    ),
                  );
                  context.read<BrandBloc>().add(
                    BrandEvent.fetchBrands(
                      context,
                      isRefresh: true,
                      controller: shopRefresh,
                    ),
                  );
                  context.read<BannerBloc>()
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
                  context.read<MasterBloc>().add(
                    MasterEvent.fetchMasters(
                      context,
                      isRefresh: true,
                      controller: shopRefresh,
                    ),
                  );
                  context.read<BookingBloc>().add(
                    BookingEvent.fetchBookUpcoming(
                      context,
                      isRefresh: true,
                      controller: shopRefresh,
                    ),
                  );
                  context.read<StoryBloc>().add(
                    StoryEvent.fetchStory(
                      context,
                      isRefresh: true,
                      controller: shopRefresh,
                    ),
                  );
                  shopRefresh.resetNoData();
                },
                onLoading: () {
                  context.read<ShopBloc>().add(
                    ShopEvent.fetchShops(context, controller: shopRefresh),
                  );
                },
                child: ListView(
                  controller: scrollController,
                  shrinkWrap: true,
                  padding: EdgeInsets.symmetric(vertical: 16.r),
                  children: [
                    StoryThreeList(storyRefresh: storyRefresh, colors: colors),
                    BannerList(
                      pageController: pageController,
                      colors: colors,
                      controller: bannerRefresh,
                      onLoading: () {
                        context.read<BannerBloc>().add(
                          BannerEvent.fetchBanner(
                            context,
                            controller: bannerRefresh,
                          ),
                        );
                      },
                    ),
                    CategoryOneList(
                      categoryRefresh: categoryRefresh,
                      colors: colors,
                    ),
                    UpComingOneList(colors: colors),
                    ShopsPopularList(colors: colors),
                    16.verticalSpace,
                    if (AppHelpers.getProductsEnabled())
                      ForYouAndDiscount(colors: colors),
                    NewShopList(colors: colors),
                    BlogList(colors: colors),
                    AllShopList(colors: colors),
                    80.verticalSpace,
                  ],
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  AppBar _buildAppBar(CustomColorSet colors) {
    return AppBar(
      backgroundColor: colors.backgroundColor,
      elevation: 0.0,
      centerTitle: false,
      leadingWidth: 56.r,
      actions: [
        IconButton(
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
            child: SvgPicture.asset(
              Assets.svgNotification,
              height: 24.r,
              colorFilter: ColorFilter.mode(colors.textBlack, BlendMode.srcIn),
            ),
          ),
        ),
      ],
      leading: LocalStorage.getToken().isEmpty
          ? IconButton(
              onPressed: () {
                Scaffold.of(context).openDrawer();
              },
              icon: Icon(FlutterRemix.menu_line, color: colors.textBlack),
            )
          : InkWell(
              onTap: () {
                Scaffold.of(context).openDrawer();
              },
              child: Padding(
                padding: EdgeInsets.only(left: 8.r),
                child: CustomNetworkImage(
                  url: LocalStorage.getUser().img,
                  height: 40,
                  width: 40,
                  radius: 30,
                  name: LocalStorage.getUser().firstname,
                ),
              ),
            ),
      title: InkWell(
        onTap: () {
          AppRouteSetting.goSelectCountry(context);
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              LocalStorage.getUser().firstname == null
                  ? AppHelpers.getTranslation(TrKeys.guest)
                  : "${LocalStorage.getUser().firstname ?? ""} ${LocalStorage.getUser().lastname ?? ""}",
              style: CustomStyle.interNormal(color: colors.textBlack, size: 14),
            ),
            Row(
              children: [
                Text(
                  "${LocalStorage.getAddress()?.country ?? ""} ${LocalStorage.getAddress()?.city ?? ""}",
                  style: CustomStyle.interRegular(
                    color: colors.textHint,
                    size: 14,
                  ),
                ),
                Icon(FlutterRemix.arrow_down_s_line, color: colors.textHint),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
