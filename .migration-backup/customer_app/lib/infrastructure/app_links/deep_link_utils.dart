import 'package:demand/infrastructure/app_links/app_links_service.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';

/// Utility class for creating and managing deep links
class DeepLinkUtils {
  DeepLinkUtils._();

  /// Create a deep link for sharing a group order
  static String createGroupOrderLink(int? cartId) {
    final Map<String, String> parameters = {
      'cart_id': cartId.toString(),
      'country_id': LocalStorage.getAddress()?.countryId?.toString() ?? '',
      'region_id': LocalStorage.getAddress()?.regionId?.toString() ?? '',
      'user_id': LocalStorage.getUser().id.toString(),
    };

    if (LocalStorage.getAddress()?.cityId != null) {
      parameters['city_id'] = LocalStorage.getAddress()!.cityId.toString();
    }
    return AppLinksService.createDeepLink(
      path: 'group',
      parameters: parameters,
    );
  }

  /// Create a deep link for sharing a shop
  static String createShopLink({required int? shopId, required String? slug}) {
    final Map<String, String> parameters = {'shop_id': shopId.toString()};

    return AppLinksService.createDeepLink(
      path: 'shops/$slug',
      parameters: parameters,
    );
  }

  /// Create a deep link for sharing a product
  static String createProductLink({
    required int productId,
    int? shopId,
    String? categoryId,
  }) {
    final Map<String, String> parameters = {'product_id': productId.toString()};

    if (shopId != null) {
      parameters['shop_id'] = shopId.toString();
    }

    if (categoryId != null) {
      parameters['category_id'] = categoryId.toString();
    }

    return AppLinksService.createDeepLink(
      path: 'product',
      parameters: parameters,
    );
  }
}
