import 'dart:async';
import 'dart:developer';
import 'package:app_links/app_links.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:flutter/material.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/join_dialog.dart';
import 'package:demand/presentation/route/app_route_shop.dart';

abstract class AppLinksService {
  AppLinksService._();

  static late AppLinks _appLinks;
  static StreamSubscription<Uri>? _linkSubscription;

  static Future<void> init(BuildContext context, {bool isMain = false}) async {
    _appLinks = AppLinks();

    try {
      final Uri? initialLink = await _appLinks.getInitialLink();
      if (initialLink != null) {
        log("Initial app link received.");
        if (context.mounted) {
          _handleDeepLink(context, initialLink);
        }
      }
    } catch (e) {
      log("Initial app link lookup failed.");
    }

    // Listen for incoming app links
    _linkSubscription = _appLinks.uriLinkStream.listen(
      (Uri uri) {
        log("App link received.");
        if (context.mounted) {
          _handleDeepLink(context, uri);
        }
      },
      onError: (err) {
        log("App link listener failed.");
      },
    );
  }

  static void _handleDeepLink(BuildContext context, Uri uri) {
    final String path = uri.path;
    final Map<String, String> queryParams = uri.queryParameters;

    log("Handling app link.");

    if (path.contains('group') || queryParams.containsKey('cart_id')) {
      if (LocalStorage.getUser().id !=
          int.tryParse(uri.queryParameters['user_id'] ?? '')) {
        showJoin(context, uri);
      }
      return;
    }

    // Handle shop links
    if (path.contains('shops') || queryParams.containsKey('shop_id')) {
      final String? shopIdStr = queryParams['shop_id'];
      if (shopIdStr != null) {
        final int? shopId = int.tryParse(shopIdStr);
        if (shopId != null) {
          AppRouteShop.goShopPage(context, shop: ShopData(id: shopId));
          return;
        }
      }
    }

    // Handle other deep link types
    if (path.contains('product') && queryParams.containsKey('product_id')) {
      log("Product app link received.");
      // Add your product navigation logic here
      return;
    }

    if (path.contains('category') && queryParams.containsKey('category_id')) {
      log("Category app link received.");
      // Add your category navigation logic here
      return;
    }

    log("Unhandled app link.");
  }

  static void showJoin(BuildContext context, Uri? link) {
    AppHelpers.showCustomDialog(context, content: JoinDialog(link: link));
  }

  static void dispose() {
    _linkSubscription?.cancel();
  }

  /// Create a deep link URL for sharing
  static String createDeepLink({
    required String path,
    required Map<String, String> parameters,
  }) {
    final Uri uri = Uri(
      scheme: 'https',
      host: AppConstants.deepLinkHost,
      path: path,
      queryParameters: parameters,
    );
    return uri.toString();
  }
}
