import 'package:demand/presentation/pages/initial/ui_selection_page.dart';
import 'package:demand/presentation/pages/location/location_permission_page.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:demand/application/address/address_bloc.dart';
import 'package:demand/application/auth/auth_bloc.dart';
import 'package:demand/application/banner/banner_bloc.dart';
import 'package:demand/application/blog/blog_bloc.dart';
import 'package:demand/application/brand/brand_bloc.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/application/chat/chat_bloc.dart';
import 'package:demand/application/game/game_bloc.dart';
import 'package:demand/application/gift_cart/gift_cart_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/application/notification/notification_bloc.dart';
import 'package:demand/application/order/order_bloc.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/application/story/story_bloc.dart';
import 'package:demand/application/wallet/wallet_bloc.dart';
import 'package:demand/domain/model/response/notification_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/game/game.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/pages/app_setting/app_setting.dart';
import 'package:demand/presentation/pages/app_setting/widgets/currency.dart';
import 'package:demand/presentation/pages/app_setting/widgets/language.dart';
import 'package:demand/presentation/pages/blog/blog_list_page.dart';
import 'package:demand/presentation/pages/chat/chat_list_page.dart';
import 'package:demand/presentation/pages/chat/chat_page.dart';
import 'package:demand/presentation/pages/country/city_page.dart';
import 'package:demand/presentation/pages/country/country_page.dart';
import 'package:demand/presentation/pages/gift_cart/my_gift_cart.dart';
import 'package:demand/presentation/pages/group_order/group_order_page.dart';
import 'package:demand/presentation/pages/help_policy_term/help_page.dart';
import 'package:demand/presentation/pages/help_policy_term/policy_page.dart';
import 'package:demand/presentation/pages/help_policy_term/term_page.dart';
import 'package:demand/presentation/pages/membership/my_memberships.dart';
import 'package:demand/presentation/pages/notification/notification_page.dart';
import 'package:demand/presentation/pages/notification/widgets/notification_bottom_sheet.dart';
import 'package:demand/presentation/pages/profile/change_password.dart';
import 'package:demand/presentation/pages/profile/edit_account.dart';
import 'package:demand/presentation/pages/profile/my_account.dart';
import 'package:demand/presentation/pages/profile/referral_page.dart';
import 'package:demand/presentation/pages/transactions/transaction_list.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import 'app_route.dart';

abstract class AppRouteSetting {
  AppRouteSetting._();

  static Future goLocationAccess(BuildContext context) {
    return Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const LocationPermissionPage()),
    );
  }

  static Future goNotification(BuildContext context) {
    if (LocalStorage.getToken().isEmpty) {
      return AppRoute.goLogin(context);
    }
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(
              value: context.read<NotificationBloc>()
                ..add(
                  NotificationEvent.fetchNotification(context, isRefresh: true),
                )
                ..add(NotificationEvent.fetchCount(context)),
            ),
            BlocProvider.value(value: context.read<BlogBloc>()),
            BlocProvider(create: (context) => OrderBloc()),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: NotificationPage(),
        ),
      ),
    );
  }

  static void goNotificationBottomSheet(
    BuildContext context, {
    required NotificationModel notification,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: NotificationBottomSheetSheet(
        notification: notification,
        colors: colors,
      ),
    );
  }

  static Future goHelp(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<ProfileBloc>()
            ..add(ProfileEvent.getHelps(context)),
          child: const HelpPage(),
        ),
      ),
    );
  }

  static Future goPolicy(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<ProfileBloc>()
            ..add(ProfileEvent.getPolicy(context)),
          child: const PolicyPage(),
        ),
      ),
    );
  }

  static Future goTerm(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<ProfileBloc>()
            ..add(ProfileEvent.getTerm(context)),
          child: const TermPage(),
        ),
      ),
    );
  }

  static Future goBlog(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<BlogBloc>(),
          child: const BlogListPage(),
        ),
      ),
    );
  }

  static void goLanguage(BuildContext context) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: MultiBlocProvider(
        providers: [
          BlocProvider.value(value: context.read<ProductBloc>()),
          BlocProvider.value(value: context.read<CategoryBloc>()),
          BlocProvider.value(value: context.read<BannerBloc>()),
          BlocProvider.value(value: context.read<ProfileBloc>()),
          BlocProvider.value(value: context.read<BlogBloc>()),
          BlocProvider.value(value: context.read<BrandBloc>()),
          BlocProvider.value(value: context.read<ShopBloc>()),
          BlocProvider.value(value: context.read<StoryBloc>()),
        ],
        child: const LanguagePage(),
      ),
    );
  }

  static void goCurrency(BuildContext context) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      maxChildSize: 0.8,
      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider.value(value: context.read<ProductBloc>()),
          BlocProvider.value(value: context.read<CategoryBloc>()),
          BlocProvider.value(value: context.read<BannerBloc>()),
          BlocProvider.value(value: context.read<ProfileBloc>()),
          BlocProvider.value(value: context.read<BlogBloc>()),
          BlocProvider.value(value: context.read<BrandBloc>()),
          BlocProvider.value(value: context.read<ShopBloc>()),
          BlocProvider.value(value: context.read<StoryBloc>()),
        ],
        child: CurrencyPage(controller: c),
      ),
    );
  }

  static Future goMyAccount(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProfileBloc>()),
            BlocProvider.value(value: context.read<NotificationBloc>()),
            BlocProvider.value(value: context.read<BlogBloc>()),
            BlocProvider(create: (context) => OrderBloc()),
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
          ],
          child: const MyAccount(),
        ),
      ),
    );
  }

  static Future goAppSetting(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProductBloc>()),
            BlocProvider.value(value: context.read<BannerBloc>()),
            BlocProvider.value(value: context.read<ProfileBloc>()),
            BlocProvider.value(value: context.read<BlogBloc>()),
            BlocProvider.value(value: context.read<ShopBloc>()),
            BlocProvider.value(value: context.read<StoryBloc>()),
            BlocProvider.value(value: context.read<BrandBloc>()),
            BlocProvider.value(value: context.read<CategoryBloc>()),
          ],
          child: const AppSettingPage(),
        ),
      ),
    );
  }

  static Future goSelectCountry(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) =>
              AddressBloc()
                ..add(AddressEvent.fetchCountry(context, isRefresh: true)),
          child: const CountryPage(),
        ),
      ),
    );
  }

  static Future goSelectCity(
    BuildContext context, {
    required int countryId,
    bool pushAddress = false,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) => AddressBloc()
            ..add(
              AddressEvent.fetchCity(
                context,
                isRefresh: true,
                countyId: countryId,
              ),
            ),
          child: CityPage(countryId: countryId, pushAddress: pushAddress),
        ),
      ),
    );
  }

  static Future goTransactionList(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<ProfileBloc>()),
            BlocProvider(
              create: (context) => WalletBloc()
                ..add(WalletEvent.fetchTransactions(context, isRefresh: true))
                ..add(WalletEvent.fetchPayments(context)),
            ),
          ],
          child: const TransactionList(),
        ),
      ),
    );
  }

  static Future goMyReferral(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider.value(
          value: context.read<ProfileBloc>()
            ..add(ProfileEvent.fetchReferral(context)),
          child: const ReferralPage(),
        ),
      ),
    );
  }

  static Future goSelectUIType(
    BuildContext context, {
    bool hasBackButton = true,
  }) {
    if (hasBackButton) {
      return Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => UISelectionPage(hasBackButton: hasBackButton),
        ),
      );
    } else {
      return Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => UISelectionPage(hasBackButton: hasBackButton),
        ),
      );
    }
  }

  static Future goGamePage(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) => GameBloc()..add(const GameEvent.init()),
          child: const Game(),
        ),
      ),
    );
  }

  static void goGroupOrder(BuildContext context, CustomColorSet colors) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: LocalStorage.getGroupOrder().id == null
            ? (context.read<CartBloc>()..add(CartEvent.createLink(context)))
            : (context.read<CartBloc>()..add(CartEvent.getCart(context))),
        child: GroupOrderPage(colors: colors),
      ),
    );
  }

  static Future goChatsList(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) => ChatBloc(),
          child: const ChatListPage(),
        ),
      ),
    );
  }

  static Future goChat(
    BuildContext context, {
    required int senderId,
    String? chatId,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) =>
              ChatBloc()..add(ChatEvent.checkChatId(sellerId: senderId)),
          child: ChatPage(senderId: senderId, chatId: chatId),
        ),
      ),
    );
  }

  static void goEditProfile(
    BuildContext context, {
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: MultiBlocProvider(
        providers: [
          BlocProvider.value(value: context.read<ProfileBloc>()),
          BlocProvider(create: (context) => AuthBloc()),
        ],
        child: EditAccountScreen(colors: colors),
      ),
    );
  }

  static void goChangePassword(
    BuildContext context, {
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheet(
      context,
      modal: BlocProvider.value(
        value: context.read<ProfileBloc>(),
        child: ChangePassword(colors: colors),
      ),
    );
  }

  static Future goMyMemberships(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) =>
              MembershipBloc()..add(MembershipEvent.myMemberships(context)),
          child: const MyMembershipsPage(),
        ),
      ),
    );
  }

  static Future goMyGiftCart(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) =>
              GiftCartBloc()..add(GiftCartEvent.myGiftCart(context)),
          child: const MyGiftCartPage(),
        ),
      ),
    );
  }
}
