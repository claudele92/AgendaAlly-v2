import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/banner/banner_bloc.dart';
import 'package:demand/application/become_seller/become_seller_bloc.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/application/filter/filter_bloc.dart';
import 'package:demand/application/gift_cart/gift_cart_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/application/product_detail/product_detail_bloc.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/application/review/review_bloc.dart';
import 'package:demand/application/service/service_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/application/story/story_bloc.dart';
import 'package:demand/domain/model/model/filter_shop_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/pages/become_seller/become_seller.dart';
import 'package:demand/presentation/pages/filter/filter_shop_page.dart';
import 'package:demand/presentation/pages/gift_cart/gift_cart_payment.dart';
import 'package:demand/presentation/pages/membership/membership_payment.dart';
import 'package:demand/presentation/pages/shop/shop_list/new_shop_list_page.dart';
import 'package:demand/presentation/pages/shop/shop_list/shop_list_page.dart';
import 'package:demand/presentation/pages/shop/shop_page.dart';
import 'package:demand/presentation/pages/membership/membership_bottom_sheet.dart';
import 'package:demand/presentation/pages/story/story_page.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../domain/model/models.dart';
import '../pages/shop/gallery_list_page.dart';
import '../pages/shop/gallery_page.dart';

abstract class AppRouteShop {
  AppRouteShop._();

  static Future goShopPage(
    BuildContext context, {
    required ShopData? shop,
  }) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => ProductBloc()
                ..add(
                  ProductEvent.fetchMostSaleShopProduct(
                    context,
                    isRefresh: true,
                    shopId: shop?.id,
                  ),
                )
                ..add(
                  ProductEvent.fetchNewShopProduct(
                    context,
                    isRefresh: true,
                    shopId: shop?.id,
                  ),
                ),
            ),
            BlocProvider(
              create: (context) => ShopBloc()
                ..add(ShopEvent.fetchShopById(context, shop: shop))
                ..add(ShopEvent.generateLink(context))
                ..add(
                  ShopEvent.fetchNearShops(
                    context,
                    onSuccess: () {},
                    isRefresh: true,
                    withoutShopId: shop?.id,
                    location: shop?.location,
                  ),
                )
                ..add(ShopEvent.fetchShopsImages(context, shopId: shop?.id)),
            ),
            BlocProvider(
              create: (context) => ServiceBloc()
                ..add(
                  ServiceEvent.fetchServiceCategories(
                    context,
                    isRefresh: true,
                    shopId: shop?.id ?? 0,
                  ),
                )
                ..add(
                  ServiceEvent.fetchServices(
                    context,
                    shopId: shop?.id,
                    isRefresh: true,
                  ),
                ),
            ),
            BlocProvider(
              create: (context) => ReviewBloc()
                ..add(
                  ReviewEvent.fetchReviewList(
                    context,
                    isRefresh: true,
                    shopId: shop?.id ?? 0,
                  ),
                )
                ..add(
                  ReviewEvent.fetchReviewOptions(context, shopId: shop?.id),
                ),
            ),
            if (AppHelpers.getType() != 0)
              BlocProvider(
                create: (context) => CategoryBloc()
                  ..add(
                    CategoryEvent.fetchCategory(
                      context,
                      isRefresh: true,
                      shopId: shop?.id ?? 0,
                    ),
                  ),
              ),
            if (AppHelpers.getType() != 0)
              BlocProvider(
                create: (context) => BannerBloc()
                  ..add(
                    BannerEvent.fetchLooks(
                      context,
                      isRefresh: true,
                      shopId: shop?.id ?? 0,
                    ),
                  )
                  ..add(
                    BannerEvent.fetchAdsListProduct(
                      context,
                      isRefresh: true,
                      shopId: shop?.id ?? 0,
                    ),
                  ),
              ),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
            BlocProvider(create: (context) => ProductDetailBloc()),
            BlocProvider(
              create: (context) => MasterBloc()
                ..add(
                  MasterEvent.fetchMasters(
                    context,
                    isRefresh: true,
                    shopId: shop?.id ?? 0,
                  ),
                ),
            ),
            BlocProvider(
              create: (context) => MembershipBloc()
                ..add(
                  MembershipEvent.fetchMembership(
                    context,
                    isRefresh: true,
                    shopId: shop?.id ?? 0,
                  ),
                ),
            ),
            BlocProvider(
              create: (context) => GiftCartBloc()
                ..add(
                  GiftCartEvent.fetchGiftCart(
                    context,
                    isRefresh: true,
                    shopId: shop?.id ?? 0,
                  ),
                ),
            ),
          ],
          child: ShopPage(shopId: shop?.id ?? 0),
          // : AppHelper.getType() == 1
          //     ? ShopOnePage(
          //         shopId: shop?.id ?? 0,
          //       )
          //     : ShopTwoPage(
          //         shopId: shop?.id ?? 0,
          //       ),
        ),
      ),
    );
  }

  static Future goShopListPage(
    BuildContext context, {
    bool isNew = false,
  }) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider(
              create: (context) => ShopBloc()
                ..add(
                  isNew
                      ? ShopEvent.fetchNewShops(context)
                      : ShopEvent.fetchShops(context),
                ),
            ),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: isNew ? const NewShopListPage() : const ShopListPage(),
        ),
      ),
    );
  }

  static void goGalleryListPage(
    BuildContext context, {
    required List<Galleries> list,
    int index = 0,
  }) {
    Navigator.of(context).push(GalleryListPage.route(context, list: list));
  }

  static void goGalleryPage(
    BuildContext context, {
    required List<Galleries> list,
    int index = 0,
  }) {
    Navigator.of(
      context,
    ).push(GalleryPage.route(context, list: list, index: index));
  }

  static Future goBecomeSeller(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(create: (context) => BecomeSellerBloc()),
            BlocProvider.value(
              value: context.read<ProfileBloc>()
                ..add(ProfileEvent.fetchProfile(context)),
            ),
          ],
          child: const BecomeSellerPage(),
        ),
      ),
    );
  }

  static void goFilterShopBottomSheet(
    BuildContext context, {
    required CustomColorSet colors,
    FilterShopModel? filter,
    bool isNear = false,
    bool isNew = false,
    LocationModel? location,
    int? categoryId,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      paddingTop: 90.r,

      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => FilterBloc()
              ..add(FilterEvent.fetchTags(context))
              ..add(FilterEvent.setFilter(filter: filter)),
          ),
          BlocProvider.value(value: context.read<ShopBloc>()),
        ],
        child: FilterShopPage(
          controller: c,
          colors: colors,
          filter: filter,
          isNear: isNear,
          isNew: isNew,
          location: location,
          categoryId: categoryId,
        ),
      ),
    );
  }

  static Future goStoryPage(
    BuildContext context, {
    required RefreshController controller,
    required int index,
    required CustomColorSet colors,
  }) async {
    return await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<StoryBloc>()),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: StoryPage(
            controller: controller,
            index: index,
            colors: colors,
          ),
        ),
      ),
    );
  }

  static void goMembershipBottomSheet(
    BuildContext context, {
    required MembershipModel? model,
    required CustomColorSet colors,
    bool enableBuy = true,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      initialChildSize: 0.5,

      modal: (c) => BlocProvider.value(
        value: context.read<MembershipBloc>()
          ..add(
            MembershipEvent.fetchMembershipDetails(
              context,
              shopId: model?.shopId,
              shopSlug: model?.shop?.slug,
              id: model?.id,
              enable: enableBuy,
            ),
          ),
        child: MembershipBottomSheet(
          colors: colors,
          controller: c,
          membership: model,
          enableBuy: enableBuy,
        ),
      ),
    );
  }

  static void goMembershipPaymentBottomSheet(
    BuildContext context, {
    required MembershipModel? model,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      initialChildSize: 0.5,

      modal: (c) => BlocProvider.value(
        value: context.read<MembershipBloc>()
          ..add(MembershipEvent.fetchPayments(context)),
        child: MembershipPaymentBottomSheet(
          colors: colors,
          controller: c,
          model: model,
        ),
      ),
    );
  }

  static void goGiftCartPaymentBottomSheet(
    BuildContext context, {
    required GiftCartModel? model,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      initialChildSize: 0.5,

      modal: (c) => BlocProvider.value(
        value: context.read<GiftCartBloc>()
          ..add(GiftCartEvent.fetchPayments(context, currentGift: model)),
        child: GiftCartPaymentBottomSheet(
          colors: colors,
          controller: c,
          model: model,
        ),
      ),
    );
  }
}
