import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/products/widgets/simple_list_page.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';
import '../../../application/master/master_bloc.dart';
import '../../app_assets.dart';

class LikePage extends StatefulWidget {
  const LikePage({super.key});

  @override
  State<LikePage> createState() => _LikePageState();
}

class _LikePageState extends State<LikePage>
    with SingleTickerProviderStateMixin {
  late RefreshController discountRefresh;
  late RefreshController likeRefresh;
  late TabController tabController;
  final isLtr = LocalStorage.getLangLtr();

  List<String> items = [
    TrKeys.salons,
    TrKeys.masters,
    if (AppHelpers.getProductsEnabled()) TrKeys.products,
  ];

  @override
  void initState() {
    tabController = TabController(length: items.length, vsync: this);
    discountRefresh = RefreshController();
    likeRefresh = RefreshController();
    context.read<ProductBloc>().add(ProductEvent.fetchLikeProduct(context));
    context.read<ShopBloc>().add(ShopEvent.fetchLikeShops(context));
    context.read<MasterBloc>().add(MasterEvent.fetchLikeMasters(context));
    super.initState();
  }

  @override
  void dispose() {
    tabController.dispose();
    discountRefresh.dispose();
    likeRefresh.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: Text(
                AppHelpers.getTranslation(TrKeys.favourites),
                style: CustomStyle.interNoSemi(
                  color: colors.textBlack,
                  size: 22,
                ),
              ),
            ),
            24.verticalSpace,
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: CustomTabBar(
                tabController: tabController,
                tabs: items
                    .map((e) => Tab(child: Text(AppHelpers.getTranslation(e))))
                    .toList(),
              ),
            ),
            Expanded(
              child: TabBarView(
                controller: tabController,
                children: [
                  _salon(colors, context),
                  _master(colors, context),
                  if (AppHelpers.getProductsEnabled())
                    _product(colors, context),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _product(CustomColorSet colors, BuildContext context) {
    return BlocBuilder<ProductBloc, ProductState>(
      builder: (context, state) {
        return state.isLoadingLike
            ? const ProductsShimmer()
            : state.isLoadingLike || state.likeProducts.isNotEmpty
            ? SimpleListPage(
                list: state.likeProducts,
                refreshController: likeRefresh,
                onRefresh: () {
                  context.read<ProductBloc>().add(
                    ProductEvent.fetchLikeProduct(
                      context,
                      controller: likeRefresh,
                      isRefresh: true,
                    ),
                  );
                },
              )
            : _noItem(colors);
      },
    );
  }

  Widget _salon(CustomColorSet colors, BuildContext context) {
    return BlocBuilder<ShopBloc, ShopState>(
      builder: (context, state) {
        return state.isLoadingLike
            ? const ShopsShimmer()
            : state.isLoadingLike || state.likeShops.isNotEmpty
            ? ListView.builder(
                shrinkWrap: true,
                padding: EdgeInsets.only(
                  right: 16.r,
                  left: 16.r,
                  top: 16.r,
                  bottom: 100.r,
                ),
                itemCount: state.likeShops.length,
                itemBuilder: (context, index) {
                  return Padding(
                    padding: EdgeInsets.only(bottom: 8.r),
                    child: ShopItem(
                      shop: state.likeShops[index],
                      colors: colors,
                    ),
                  );
                },
              )
            : _noSalonItem(colors);
      },
    );
  }

  Widget _master(CustomColorSet colors, BuildContext context) {
    return BlocBuilder<MasterBloc, MasterState>(
      builder: (context, state) {
        return state.likeMasters.isNotEmpty
            ? GridView.builder(
                padding: EdgeInsets.only(
                  right: 16.r,
                  left: 16.r,
                  top: 16.r,
                  bottom: 100.r,
                ),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  childAspectRatio: 0.8.r,
                  crossAxisCount: 2,
                  mainAxisExtent: 320.r,
                ),
                itemCount: state.likeMasters.length,
                itemBuilder: (context, index) {
                  return Padding(
                    padding: EdgeInsets.symmetric(horizontal: 4.r),
                    child: MasterItem(
                      like: true,
                      master: state.likeMasters[index],
                      colors: colors,
                    ),
                  );
                },
              )
            : _noMasterItem(colors);
      },
    );
  }

  Widget _noItem(CustomColorSet colors) {
    return Center(
      child: Column(
        children: [
          56.verticalSpace,
          Image.asset(Assets.imagesSalonEmpty),
          16.verticalSpace,
          Text(
            AppHelpers.getTranslation(TrKeys.emptyInFavorites),
            style: CustomStyle.interNoSemi(color: colors.textBlack, size: 24),
          ),
          12.verticalSpace,
          Padding(
            padding: EdgeInsets.symmetric(horizontal: 32.r),
            child: Text(
              AppHelpers.getTranslation(TrKeys.addYourFavoriteProduct),
              style: CustomStyle.interRegular(
                color: colors.textBlack,
                size: 16,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }

  Widget _noSalonItem(CustomColorSet colors) {
    return Center(
      child: Column(
        children: [
          56.verticalSpace,
          Image.asset(Assets.imagesSalonEmpty),
          16.verticalSpace,
          Text(
            AppHelpers.getTranslation(TrKeys.emptyInFavorites),
            style: CustomStyle.interNoSemi(color: colors.textBlack, size: 24),
          ),
          12.verticalSpace,
          Padding(
            padding: EdgeInsets.symmetric(horizontal: 32.r),
            child: Text(
              AppHelpers.getTranslation(TrKeys.addYourFavoriteSalons),
              style: CustomStyle.interRegular(
                color: colors.textBlack,
                size: 16,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }

  Widget _noMasterItem(CustomColorSet colors) {
    return Center(
      child: Column(
        children: [
          56.verticalSpace,
          Image.asset(Assets.imagesMasterEmpty),
          16.verticalSpace,
          Text(
            AppHelpers.getTranslation(TrKeys.emptyInFavorites),
            style: CustomStyle.interNoSemi(color: colors.textBlack, size: 24),
          ),
          12.verticalSpace,
          Padding(
            padding: EdgeInsets.symmetric(horizontal: 32.r),
            child: Text(
              AppHelpers.getTranslation(TrKeys.addYourFavoriteMasters),
              style: CustomStyle.interRegular(
                color: colors.textBlack,
                size: 16,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }
}
