import 'package:demand/application/maksekeskus/maksekeskus_bloc.dart';
import 'package:demand/domain/model/model/create_order_model.dart';
import 'package:demand/domain/model/models.dart';
import 'package:demand/presentation/pages/checkout/maksekeskus_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/address/address_bloc.dart';
import 'package:demand/application/auth/auth_bloc.dart';
import 'package:demand/application/banner/banner_bloc.dart';
import 'package:demand/application/blog/blog_bloc.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/brand/brand_bloc.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/application/checkout/checkout_bloc.dart';
import 'package:demand/application/compare/compare_bloc.dart';
import 'package:demand/application/filter/filter_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/application/map/map_bloc.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/application/notification/notification_bloc.dart';
import 'package:demand/application/order/order_bloc.dart';
import 'package:demand/application/product_detail/product_detail_bloc.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/application/review/review_bloc.dart';
import 'package:demand/application/search/search_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/domain/model/model/blog_model.dart';
import 'package:demand/domain/model/response/banners_paginate_response.dart';
import 'package:demand/domain/model/response/cart_response.dart';
import 'package:demand/domain/model/response/delivery_point_response.dart';
import 'package:demand/domain/model/response/refund_pagination_response.dart';
import 'package:demand/domain/model/response/user_address_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/bottom_sheets/ads_bottom_sheet.dart';
import 'package:demand/presentation/components/bottom_sheets/banner_bottom_sheet.dart';
import 'package:demand/presentation/components/bottom_sheets/blog_bottom_sheet.dart';
import 'package:demand/presentation/components/bottom_sheets/looks_bottom_sheet.dart';
import 'package:demand/presentation/pages/address/add_edit_address.dart';
import 'package:demand/presentation/pages/auth/confirm_screen.dart';
import 'package:demand/presentation/pages/auth/forget_password.dart';
import 'package:demand/presentation/pages/auth/login_screen.dart';
import 'package:demand/presentation/pages/auth/sign_up_field_screen.dart';
import 'package:demand/presentation/pages/auth/sign_up_screen.dart';
import 'package:demand/presentation/pages/auth/update_password.dart';
import 'package:demand/presentation/pages/blog/blog_screen.dart';
import 'package:demand/presentation/pages/category/category_page.dart';
import 'package:demand/presentation/pages/checkout/widget/select_delivery_point.dart';
import 'package:demand/presentation/pages/compare/compare_list_page.dart';
import 'package:demand/presentation/pages/compare/compare_product_page.dart';
import 'package:demand/presentation/pages/order/refund_screen.dart';
import 'package:demand/presentation/pages/product_detail/product_detail_one/product_one_page.dart';
import 'package:demand/presentation/pages/product_detail/product_detail_two/product_two_page.dart';
import 'package:demand/presentation/pages/search/search_location.dart';
import 'package:demand/presentation/pages/search/search_map.dart';
import 'package:demand/presentation/pages/my_digital_files/my_digital_list.dart';
import 'package:demand/presentation/pages/auth/auth_page.dart';
import 'package:demand/presentation/pages/cart/widget/note_product.dart';
import 'package:demand/presentation/pages/search/search_page.dart';
import 'package:demand/presentation/pages/checkout/checkout_page.dart';
import 'package:demand/presentation/pages/checkout/congrats_page.dart';
import 'package:demand/presentation/pages/checkout/widget/select_date.dart';
import 'package:demand/presentation/pages/filter/filter_page.dart';
import 'package:demand/presentation/pages/main/main_page.dart';
import 'package:demand/presentation/pages/map/map_page.dart';
import 'package:demand/presentation/pages/order/order_screen.dart';
import 'package:demand/presentation/pages/order/orders_list.dart';
import 'package:demand/presentation/components/web_view.dart';
import 'package:demand/presentation/pages/product_detail/product_page.dart';
import 'package:demand/presentation/pages/product_detail/widgets/only_image.dart';
import 'package:demand/presentation/pages/products/products_list_page.dart';
import 'package:demand/presentation/pages/review/review_page.dart';
import 'package:demand/presentation/pages/review/widgets/review_images.dart';
import 'package:demand/presentation/pages/search/select_time.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:osm_nominatim/osm_nominatim.dart';

import '../../application/story/story_bloc.dart';

abstract class AppRoute {
  AppRoute._();

  static Future goMain(
    BuildContext context, {
    bool isListen = true,
  }) => Navigator.of(context).pushAndRemoveUntil(
    MaterialPageRoute(
      builder: (_) {
        return MultiBlocProvider(
          providers: [
            BlocProvider<MainBloc>(create: (context) => MainBloc()),
            BlocProvider<ProfileBloc>(create: (context) => ProfileBloc()),
            BlocProvider<ShopBloc>(
              create: (context) => ShopBloc()
                ..add(ShopEvent.fetchShops(context, isRefresh: true))
                ..add(ShopEvent.fetchPopularShops(context, isRefresh: true))
                ..add(ShopEvent.fetchNewShops(context, isRefresh: true))
                ..add(
                  ShopEvent.fetchNearShops(
                    context,
                    isRefresh: true,
                    onSuccess: () {},
                    location: LocalStorage.getLocation(),
                  ),
                ),
            ),
            BlocProvider<StoryBloc>(
              create: (context) =>
                  StoryBloc()
                    ..add(StoryEvent.fetchStory(context, isRefresh: true)),
            ),
            BlocProvider<ProductBloc>(
              create: (context) => ProductBloc()
                ..add(
                  ProductEvent.fetchMostSaleProduct(context, isRefresh: true),
                )
                ..add(ProductEvent.fetchNewProduct(context, isRefresh: true))
                ..add(ProductEvent.fetchAllProduct(context, isRefresh: true)),
            ),
            BlocProvider<BlogBloc>(
              create: (context) =>
                  BlogBloc()
                    ..add(BlogEvent.fetchBlog(context, isRefresh: true)),
            ),
            BlocProvider<BannerBloc>(
              create: (context) => BannerBloc()
                ..add(BannerEvent.fetchBanner(context, isRefresh: true))
                ..add(BannerEvent.fetchAdsBanner(context, isRefresh: true))
                ..add(BannerEvent.fetchLooks(context, isRefresh: true))
                ..add(
                  BannerEvent.fetchAdsListProduct(context, isRefresh: true),
                ),
            ),
            LocalStorage.getToken().isNotEmpty
                ? BlocProvider(
                    create: (context) =>
                        CartBloc()..add(CartEvent.getCart(context)),
                  )
                : BlocProvider(create: (context) => CartBloc()),
            if (LocalStorage.getToken().isNotEmpty)
              BlocProvider(
                create: (context) =>
                    NotificationBloc()
                      ..add(NotificationEvent.fetchCount(context)),
              ),
            BlocProvider<BrandBloc>(
              create: (context) =>
                  BrandBloc()..add(BrandEvent.fetchBrands(context)),
            ),
            BlocProvider<MasterBloc>(
              create: (context) =>
                  MasterBloc()..add(MasterEvent.fetchMasters(context)),
            ),
            LocalStorage.getToken().isNotEmpty
                ? BlocProvider<BookingBloc>(
                    create: (context) => BookingBloc()
                      ..add(
                        BookingEvent.fetchBookUpcoming(
                          context,
                          isRefresh: true,
                        ),
                      )
                      ..add(
                        BookingEvent.fetchBookPast(context, isRefresh: true),
                      ),
                  )
                : BlocProvider<BookingBloc>(create: (context) => BookingBloc()),
            BlocProvider<CategoryBloc>(
              create: (context) => CategoryBloc()
                ..add(
                  CategoryEvent.fetchCategoryOfService(
                    context,
                    isRefresh: true,
                  ),
                ),
            ),
          ],
          child: MainPage(isListen: isListen),
        );
      },
    ),
    (route) => false,
  );

  static Future goLogin(BuildContext context) =>
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(
          builder: (_) => BlocProvider(
            create: (context) => AuthBloc(),
            child: const AuthPage(),
          ),
        ),
        (route) => false,
      );

  static Future<void> goProductPage(
    BuildContext context, {
    required ProductData? product,
  }) async {
    product ??= ProductData();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => ProductDetailBloc()
                ..add(
                  ProductDetailEvent.fetchProductById(
                    context,
                    product: product,
                  ),
                )
                ..add(
                  ProductDetailEvent.fetchRelatedProduct(
                    context,
                    productUuid: product?.uuid,
                    isRefresh: true,
                  ),
                )
                ..add(
                  ProductDetailEvent.fetchViewedProducts(
                    context,
                    productId: product?.id,
                  ),
                )
                ..add(
                  ProductDetailEvent.fetchBuyWithProduct(
                    context,
                    productId: product?.id,
                  ),
                ),
            ),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: AppHelpers.getType() == 0
              ? const ProductPage()
              : AppHelpers.getType() == 1
              ? const ProductOnePage()
              : const ProductTwoPage(),
        ),
      ),
    );
  }

  static void goOnlyImagePage(
    BuildContext context, {
    required List<Galleries> list,
    int? selectIndex,
  }) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProductDetailBloc>()),
          ],
          child: OnlyImagePage(list: list, selectIndex: selectIndex ?? 0),
        ),
      ),
    );
  }

  static void goCheckoutPage(
    BuildContext context, {
    required bool fullDigital,
  }) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => CheckoutBloc()
                ..add(CheckoutEvent.fetchPayments(context))
                ..add(CheckoutEvent.fetchUserAddress(context, isRefresh: true))
                ..add(CheckoutEvent.fetchDeliveryPoint(context))
                ..add(CheckoutEvent.fetchDeliveryPrice(context)),
            ),
            BlocProvider(create: (context) => OrderBloc()),
            BlocProvider(create: (context) => AddressBloc()),
            BlocProvider.value(value: context.read<CartBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<ProductBloc>()),
          ],
          child: CheckoutPage(fullDigital: fullDigital),
        ),
      ),
    );
  }

  static void goMaksekeskusBottomSheet(
    BuildContext context, {
    CreateOrderModel? order,
    bool? parcel,
    bool? wallet,
    int? parcelId,
    num? price,
    required CustomColorSet colors,
    required ValueChanged<String> onSuccess,
  }) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider<MaksekeskusBloc>(
        create: (context) => MaksekeskusBloc()
          ..add(
            MaksekeskusEvent.fetchMaksekeskus(
              context,
              order: order,
              price: price,
              wallet: wallet,
              parcel: parcel,
              parcelId: parcelId,
            ),
          ),
        child: MaksekeskusScreen(colors: colors, onSuccess: onSuccess),
      ),
    );
  }

  static Future goProductList(
    BuildContext context, {
    List<ProductData>? list,
    String? title,
    int? total,
    bool? isNewProduct,
    bool? isMostSaleProduct,
    int? categoryId,
    int? brandId,
    int? bannerId,
    int? shopId,
  }) async => await Navigator.of(context).push(
    MaterialPageRoute(
      builder: (_) => MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => ProductBloc()
              ..add(
                ProductEvent.fetchProducts(
                  context,
                  isRefresh: true,
                  list: list,
                  isNewProduct: isNewProduct,
                  isMostSaleProduct: isMostSaleProduct,
                  categoryId: categoryId,
                  bannerId: bannerId,
                  brandId: brandId == null ? null : [brandId],
                  shopId: shopId,
                ),
              ),
          ),
          BlocProvider.value(value: context.read<MainBloc>()),
          BlocProvider.value(value: context.read<CartBloc>()),
          BlocProvider(
            create: (context) => FilterBloc()
              ..add(
                FilterEvent.fetchExtras(
                  context,
                  categoryId: categoryId,
                  type: categoryId != null
                      ? "category"
                      : isMostSaleProduct ?? false
                      ? "most_sold"
                      : isNewProduct ?? false
                      ? "news_letter"
                      : null,
                  isPrice: true,
                ),
              )
              ..add(FilterEvent.setBrands(id: brandId ?? -1)),
          ),
        ],
        child: ProductsListPage(
          list: list ?? [],
          title: title ?? "",
          totalCount: total ?? 0,
          isMostSaleProduct: isMostSaleProduct,
          isNewProduct: isNewProduct,
          categoryId: categoryId,
          shopId: shopId,
          brandId: brandId,
          bannerId: bannerId,
        ),
      ),
    ),
  );

  static void goBlogBottomSheet(BuildContext context, BlogData blog) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<BlogBloc>()
          ..add(BlogEvent.fetchBlogById(context, id: blog.id ?? 0, blog: blog)),
        child: const BlogBottomSheet(),
      ),
    );
  }

  static void goLoginBottomSheet(
    BuildContext context,
    CustomColorSet colors,
    TextEditingController phoneController,
    TextEditingController emailController,
  ) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<AuthBloc>(),
        child: LoginScreen(
          colors: colors,
          phoneController: phoneController,
          emailController: emailController,
        ),
      ),
    );
  }

  static void goConfirmBottomSheet(
    BuildContext context,
    CustomColorSet colors,
    String phone,
    String email,
  ) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<AuthBloc>(),
        child: ConfirmScreen(colors: colors, phone: phone, email: email),
      ),
    );
  }

  static void goSignUpFieldBottomSheet(
    BuildContext context,
    CustomColorSet colors,
    String phone,
    String email,
  ) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<AuthBloc>(),
        child: SignUpFieldScreen(
          colors: colors,
          isPhone: AppHelpers.checkPhone(phone) && email.isEmpty,
          phone: phone,
          email: email,
        ),
      ),
    );
  }

  static void goSignUpBottomSheet(
    BuildContext context,
    CustomColorSet colors,
    TextEditingController phoneController,
    TextEditingController emailController,
  ) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<AuthBloc>(),
        child: SignUpScreen(
          colors: colors,
          phoneController: phoneController,
          emailController: emailController,
        ),
      ),
    );
  }

  static void goForgetPasswordBottomSheet(
    BuildContext context,
    CustomColorSet colors,
    TextEditingController phoneController,
    TextEditingController emailController,
  ) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<AuthBloc>(),
        child: ForgetPasswordScreen(
          colors: colors,
          phoneController: phoneController,
          emailController: emailController,
        ),
      ),
    );
  }

  static void goUpdatePasswordBottomSheet(
    BuildContext context,
    CustomColorSet colors,
    TextEditingController phone,
  ) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<AuthBloc>(),
        child: UpdatePasswordScreen(colors: colors, phone: phone.text),
      ),
    );
  }

  static Future goBlogPage(BuildContext context, BlogData blog) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<BlogBloc>()
            ..add(
              BlogEvent.fetchBlogById(context, id: blog.id ?? 0, blog: blog),
            ),
          child: const BlogScreen(),
        ),
      ),
    );
  }

  static void goNoteForProduct(
    BuildContext context,
    CartDetailProduct? product,
    String? note,
  ) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      paddingTop: 0,

      modal: BlocProvider.value(
        value: context.read<CartBloc>(),
        child: NoteProduct(cartItem: product, note: note),
      ),
    );
  }

  static Future goMap(BuildContext context) async {
    return await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [BlocProvider(create: (context) => MapBloc())],
          child: const MapPage(),
        ),
      ),
    );
  }

  static Future goSearchMap(
    BuildContext context, {
    LocationModel? location,
    CategoryData? category,
  }) async {
    final categories = [
      category?.id ?? -1,
      ...?category?.children?.map((e) => e.id ?? -1),
    ];
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(create: (context) => MapBloc()),
            BlocProvider.value(
              value: context.read<ShopBloc>()
                ..add(
                  ShopEvent.fetchNearShops(
                    context,
                    onSuccess: () {},
                    isRefresh: true,
                    categoryIds: categories,
                    location:
                        location ??
                        LocationModel(
                          latitude: AppHelpers.getInitialLatitude(),
                          longitude: AppHelpers.getInitialLongitude(),
                        ),
                  ),
                ),
            ),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CategoryBloc>()),
          ],
          child: SearchMapPage(
            categoryIds: categories,
            location: location,
            category: category,
          ),
        ),
      ),
    );
  }

  static Future<Place> goSearchLocation(BuildContext context) async {
    return await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [BlocProvider(create: (context) => MapBloc())],
          child: const SearchLocationPage(),
        ),
      ),
    );
  }

  static void goLookBottomSheet(
    BuildContext context,
    BannerData banner,
    CustomColorSet colors,
  ) {
    AppHelpers.showCustomModalBottomSheet(
      context,
      modal: MultiBlocProvider(
        providers: [
          BlocProvider.value(
            value: context.read<BannerBloc>()
              ..add(BannerEvent.fetchProduct(context, id: banner.id ?? 0)),
          ),
          BlocProvider.value(value: context.read<MainBloc>()),
          BlocProvider.value(value: context.read<CartBloc>()),
          BlocProvider.value(value: context.read<ProductBloc>()),
        ],
        child: LooksBottomSheet(look: banner, colors: colors),
      ),
    );
  }

  static void goAdsBottomSheet(
    BuildContext context,
    BannerData banner,
    CustomColorSet colors,
  ) {
    AppHelpers.showCustomModalBottomSheet(
      context,
      modal: MultiBlocProvider(
        providers: [
          BlocProvider.value(
            value: context.read<BannerBloc>()
              ..add(BannerEvent.fetchAdsProduct(context, id: banner.id ?? 0)),
          ),
          BlocProvider.value(value: context.read<MainBloc>()),
          BlocProvider.value(value: context.read<CartBloc>()),
          BlocProvider.value(value: context.read<ProductBloc>()),
        ],
        child: AdsBottomSheet(look: banner, colors: colors),
      ),
    );
  }

  static void goBannerBottomSheet(
    BuildContext context,
    BannerData banner,
    CustomColorSet colors,
  ) {
    AppHelpers.showCustomModalBottomSheet(
      context,
      modal: MultiBlocProvider(
        providers: [
          BlocProvider.value(value: context.read<BannerBloc>()),
          BlocProvider.value(value: context.read<MainBloc>()),
          BlocProvider.value(value: context.read<CartBloc>()),
        ],
        child: BannerBottomSheet(banner: banner, colors: colors),
      ),
    );
  }

  static void goFilterBottomSheet(BuildContext context, CustomColorSet colors) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      paddingTop: 90.r,

      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider.value(value: context.read<FilterBloc>()),
          BlocProvider.value(value: context.read<ProductBloc>()),
        ],
        child: FilterPage(controller: c, colors: colors),
      ),
    );
  }

  static Future goCategoryPage(BuildContext context) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<ShopBloc>()),
            BlocProvider<CategoryBloc>(
              create: (context) => CategoryBloc()
                ..add(CategoryEvent.fetchCategory(context, isRefresh: true))
                ..add(
                  CategoryEvent.fetchCategoryOfService(
                    context,
                    isRefresh: true,
                  ),
                ),
            ),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: const CategoryPage(),
        ),
      ),
    );
  }

  static Future goSearchPage(BuildContext context, {int? shopId}) {
    return Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<ShopBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
            BlocProvider(create: (context) => SearchBloc()),
          ],
          child: SearchPage(shopId: shopId),
        ),
      ),
    );
  }

  static Future goComparePage(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
            BlocProvider(
              create: (context) =>
                  CompareBloc()
                    ..add(CompareEvent.fetchCompare(context, isRefresh: true)),
            ),
          ],
          child: const CompareListPage(),
        ),
      ),
    );
  }

  static void goReviewPage(
    BuildContext context, {
    required CustomColorSet colors,
    int? shopId,
    int? productId,
    int? bookingId,
    int? blogId,
    int? driverId,
    int? orderId,
    String? productUuid,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      paddingTop: 0,

      modal: (c) => BlocProvider(
        create: (context) => ReviewBloc()
          ..add(
            ReviewEvent.fetchReviewList(
              context,
              shopId: shopId,
              productUuid: productUuid,
              blogId: blogId,
              driverId: driverId,
              isRefresh: true,
            ),
          )
          ..add(
            ReviewEvent.fetchReview(
              context,
              shopId: shopId,
              driverId: driverId,
              bookingId: bookingId,
              productId: productId,
              blogId: blogId,
            ),
          ),
        child: ReviewPage(
          colors: colors,
          blogId: blogId,
          controller: c,
          shopId: shopId,
          orderId: orderId,
          productId: productId,
          productUuid: productUuid,
        ),
      ),
    );
  }

  static void goSelectDate(
    BuildContext context, {
    required CustomColorSet colors,
    required DateTime? dateTime,
  }) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      paddingTop: 400.r,
      modal: BlocProvider.value(
        value: context.read<CheckoutBloc>(),
        child: SelectDate(colors: colors, dateTime: dateTime),
      ),
    );
  }

  static Future<DateTime?> goSelectDateSearch(
    BuildContext context, {
    required CustomColorSet colors,
    required DateTime? dateTime,
  }) async {
    return await AppHelpers.showSelectDate(context, colors: colors);
  }

  static Future goSelectTime(BuildContext context) async {
    return await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<ProfileBloc>()
            ..add(ProfileEvent.getHelps(context)),
          child: const SelectTimePage(),
        ),
      ),
    );
  }

  static void goCongratsPage(BuildContext context) {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => const CongratsPage()),
      (route) => route.isFirst,
    );
  }

  static Future goSelectDeliveryPoint(
    BuildContext context, {
    required List<DeliveryPoint> list,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<CheckoutBloc>(),
          child: SelectDeliveryPoint(list: list),
        ),
      ),
    );
  }

  static Future goOrdersList(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => OrderBloc()
                ..add(OrderEvent.fetchActiveOrders(context))
                ..add(OrderEvent.fetchRefundOrders(context)),
            ),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: const OrdersListPage(),
        ),
      ),
    );
  }

  static void goRefundOrderPage(
    BuildContext context,
    CustomColorSet colors,
    RefundModel? refund,
  ) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      paddingTop: MediaQuery.sizeOf(context).height / 10,
      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => OrderBloc()
              ..add(
                OrderEvent.fetchRefundOrderById(
                  context,
                  id: refund?.id ?? 0,
                  refund: refund,
                ),
              ),
          ),
          BlocProvider.value(value: context.read<ProductBloc>()),
          BlocProvider.value(value: context.read<MainBloc>()),
          BlocProvider.value(value: context.read<CartBloc>()),
        ],
        child: RefundScreen(colors: colors, controller: c),
      ),
    );
  }

  static void goOrderPage(BuildContext context, OrderShops order) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      paddingTop: MediaQuery.sizeOf(context).height / 10,
      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => OrderBloc()
              ..add(
                OrderEvent.fetchOrderById(
                  context,
                  id: order.id ?? 0,
                  order: OrderModel(orderShops: [order]),
                ),
              ),
          ),
          BlocProvider.value(value: context.read<ProductBloc>()),
          BlocProvider.value(value: context.read<MainBloc>()),
          BlocProvider.value(value: context.read<CartBloc>()),
        ],
        child: OrderScreen(controller: c),
      ),
    );
  }

  static Future goAddEditAddress(
    BuildContext context, {
    UserAddress? address,
  }) async {
    return await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) => AddressBloc(),
          child: AddEditAddressPage(address: address),
        ),
      ),
    );
  }

  static Future<bool> goWebView(BuildContext context, {String? url}) async {
    return await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => BlocProvider(
              create: (context) => AddressBloc(),
              child: WebViewPage(url: url ?? ""),
            ),
          ),
        ) ??
        false;
  }

  static Future goCompareProductPage(
    BuildContext context, {
    required List<ProductData> list,
  }) async {
    return await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<CompareBloc>()),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: CompareProductPage(list: list),
        ),
      ),
    );
  }

  static Future goMyDigitalList(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(
              value: context.read<ProfileBloc>()
                ..add(ProfileEvent.fetchDigitalList(context, isRefresh: true)),
            ),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: const MyDigitalList(),
        ),
      ),
    );
  }

  static Future goReviewImages(
    BuildContext context, {
    required int index,
    required List<Galleries> list,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ReviewImages(selectIndex: index, list: list),
      ),
    );
  }
}
