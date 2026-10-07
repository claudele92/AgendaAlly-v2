import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/gift_cart/gift_cart_shop_list.dart';
import 'package:demand/presentation/pages/home/widgets/masters_list.dart';
import 'package:demand/presentation/pages/membership/membership_shop_list.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';
import 'widgets/services_widget.dart';
import 'widgets/shop_location_and_date.dart';
import 'widgets/new_products_shop.dart';
import 'widgets/connect_button.dart';
import 'widgets/review_shop.dart';
import 'widgets/shop_appbar.dart';
import 'widgets/near_shops.dart';
import 'widgets/shop_name.dart';

class ShopPage extends StatefulWidget {
  final int shopId;

  const ShopPage({super.key, required this.shopId});

  @override
  State<ShopPage> createState() => _ShopPageState();
}

class _ShopPageState extends State<ShopPage> {
  late RefreshController masterRefresh;

  @override
  void initState() {
    masterRefresh = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    masterRefresh.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => NestedScrollView(
        headerSliverBuilder: (_, innerBoxIsScrolled) => [
          ShopAppbar(
            colors,
            shopId: widget.shopId,
            innerBoxIsScrolled: innerBoxIsScrolled,
            onTapImage: (images, index) {
              AppRouteShop.goGalleryListPage(
                context,
                list: images,
                index: index,
              );
            },
          ),
        ],
        body: ListView(
          shrinkWrap: true,
          padding: EdgeInsets.zero,
          children: [
            12.verticalSpace,
            BlocBuilder<ShopBloc, ShopState>(
              buildWhen: (p, n) {
                return p.shop != n.shop;
              },
              builder: (context, state) {
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ShopName(
                      colors: colors,
                      shop: state.shop,
                      images: state.shopImages,
                    ),
                  ],
                );
              },
            ),
            ServicesWidget(colors: colors, shopId: widget.shopId),
            MastersList(
              isShop: true,
              colors: colors,
              controller: masterRefresh,
              onLoading: () {
                context.read<MasterBloc>().add(
                  MasterEvent.fetchMasters(
                    context,
                    controller: masterRefresh,
                    shopId: widget.shopId,
                  ),
                );
              },
            ),
            ReviewShop(colors: colors, shopId: widget.shopId),
            ShopLocationAndDate(colors: colors),
            if (AppHelpers.getProductsEnabled())
              NewShopsProductList(colors: colors, shopId: widget.shopId),
            MembershipShopList(colors: colors, shopId: widget.shopId),
            GiftCartShopList(colors: colors, shopId: widget.shopId),
            NearShops(colors: colors),
            116.verticalSpace,
          ],
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) => Padding(
        padding: EdgeInsets.symmetric(horizontal: 16.r),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Align(
              alignment: Alignment.bottomRight,
              child: ConnectButtonShop(colors: colors),
            ),
            16.verticalSpace,
            Row(
              children: [
                PopButton(colors: colors),
                10.horizontalSpace,
                Expanded(
                  child: SizedBox(
                    height: 56.r,
                    child: CustomButton(
                      title: AppHelpers.getTranslation(TrKeys.bookNow),
                      bgColor: colors.primary,
                      titleColor: colors.white,
                      onTap: () {
                        AppRouteService.goServiceListPage(
                          context,
                          shopId: widget.shopId,
                        );
                      },
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
