import 'package:dartz/dartz.dart';
import 'package:demand/domain/model/request/create_shop_request.dart';
import 'package:demand/infrastructure/app_links/deep_link_utils.dart';
import 'package:flutter/material.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/interface/shop.dart';
import 'package:demand/domain/model/model/filter_shop_model.dart';
import 'package:demand/domain/model/model/location_model.dart';
import 'package:demand/domain/model/model/review_data.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/domain/model/model/story_model.dart';
import 'package:demand/domain/model/response/gift_cart_response.dart';
import 'package:demand/domain/model/response/membership_response.dart';
import 'package:demand/domain/model/response/my_gift_cart_response.dart';
import 'package:demand/domain/model/response/my_membership_response.dart';
import 'package:demand/domain/model/response/shop_images_response.dart';
import 'package:demand/domain/model/response/shops_paginate_response.dart';
import 'package:demand/domain/model/response/single_shop_response.dart';
import 'package:demand/domain/model/model/shop_filter_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';

class ShopsRepository implements ShopsInterface {
  @override
  Future<Either<ShopsPaginateResponse, dynamic>> getAllShops({
    String? query,
    int? page,
    int? categoryId,
    FilterShopModel? filter,
    int type = 2,
  }) async {
    final data = {
      'perPage': 5,
      'page': page,
      'lang': LocalStorage.getLanguage()?.locale,
      'location_type': type,
      if (filter?.sort == null) "column": "id",
      if (filter?.sort == null) "sort": "asc",
      if (categoryId != null) "category_id": categoryId,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
      if (query != null) "search": query,
      if (LocalStorage.getLatLong() != null)
        "address": {
          "latitude":
              LocalStorage.getLatLong()?.latitude ?? AppConstants.demoLatitude,
          "longitude":
              LocalStorage.getLatLong()?.longitude ??
              AppConstants.demoLongitude,
        },
    };
    data.addAll(filter?.toJson() ?? {});
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shops/paginate',
        queryParameters: data,
      );
      return left(ShopsPaginateResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get all shops failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<SingleShopResponse, dynamic>> getSingleShop(int id) async {
    final data = {
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getLatLong() != null)
        "address": {
          "latitude":
              LocalStorage.getLatLong()?.latitude ?? AppConstants.demoLatitude,
          "longitude":
              LocalStorage.getLatLong()?.longitude ??
              AppConstants.demoLongitude,
        },
    };
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shops/$id',
        queryParameters: data,
      );
      return left(SingleShopResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get single shop failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> createShop({
    required CreateShopModel shopRequest,
  }) async {
    try {
      final client = dioHttp.client(requireAuth: true);
      await client.post(
        '/api/v1/dashboard/user/shops',
        data: shopRequest.toJson(),
      );
      return left(true);
    } catch (e) {
      debugPrint('==> edit shop failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<List<List<StoryModel?>?>, dynamic>> getStory(int page) async {
    final data = {
      'perPage': 30,
      'page': page,
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
    };
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/stories/paginate',
        queryParameters: data,
      );
      return left(storyModelFromJson(response.data) ?? []);
    } catch (e) {
      debugPrint('==> get all shops failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<ShopsPaginateResponse, dynamic>> getPopularShops({
    int? page,
    FilterShopModel? filter,
    int type = 2,
  }) async {
    final data = {
      'perPage': 5,
      'page': page,
      'lang': LocalStorage.getLanguage()?.locale,
      'location_type': type,
      if (filter?.sort == null) "column": "b_count",
      if (filter?.sort == null) "sort": "desc",
      if (filter?.tag?.id != null) "take": filter?.tag?.id ?? 0,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
      if (LocalStorage.getLatLong() != null)
        "address": {
          "latitude":
              LocalStorage.getLatLong()?.latitude ?? AppConstants.demoLatitude,
          "longitude":
              LocalStorage.getLatLong()?.longitude ??
              AppConstants.demoLongitude,
        },
    };
    data.addAll(filter?.toJson() ?? {});
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shops/paginate',
        queryParameters: data,
      );
      return left(ShopsPaginateResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get all shops failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<ShopsPaginateResponse, dynamic>> getNewShops({
    int? page,
    FilterShopModel? filter,
    int type = 2,
  }) async {
    final data = {
      'perPage': 5,
      'page': page,
      'lang': LocalStorage.getLanguage()?.locale,
      'location_type': type,
      if (filter?.sort == null) "column": "id",
      if (filter?.sort == null) "sort": "desc",
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
      if (LocalStorage.getLatLong() != null)
        "address": {
          "latitude":
              LocalStorage.getLatLong()?.latitude ?? AppConstants.demoLatitude,
          "longitude":
              LocalStorage.getLatLong()?.longitude ??
              AppConstants.demoLongitude,
        },
    };
    data.addAll(filter?.toJson() ?? {});
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shops/paginate',
        queryParameters: data,
      );
      return left(ShopsPaginateResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get all shops failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<ShopsPaginateResponse, dynamic>> getShopsByIds(
    List<int> ids,
  ) async {
    final data = <String, dynamic>{
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getLatLong() != null)
        "address": {
          "latitude":
              LocalStorage.getLatLong()?.latitude ?? AppConstants.demoLatitude,
          "longitude":
              LocalStorage.getLatLong()?.longitude ??
              AppConstants.demoLongitude,
        },
    };
    for (int i = 0; i < ids.length; i++) {
      data['shops[$i]'] = ids[i];
    }
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shops',
        queryParameters: data,
      );
      return left(ShopsPaginateResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get all shops failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<String, dynamic>> createLink({required ShopData? shop}) async {
    try {
      final String deepLink = DeepLinkUtils.createShopLink(
        shopId: shop?.id,
        slug: shop?.slug,
      );
      return left(deepLink);
    } catch (e) {
      return right(e.toString());
    }
  }

  @override
  Future<Either<ShopsPaginateResponse, dynamic>> getNearShops({
    int? page,
    int? withoutShopId,
    List<int>? categoryIds,
    required LocationModel? location,
    FilterShopModel? filter,
    int type = 2,
  }) async {
    final data = {
      'perPage': 12,
      'page': page,
      'lang': LocalStorage.getLanguage()?.locale,
      'location_type': type,
      if (filter?.sort == null) "column": "distance",
      if (filter?.sort == null) "sort": "asc",
      if (categoryIds != null) for(int i = 0; i < categoryIds.length; i++) 'category_ids[$i]': categoryIds[i],
      if (withoutShopId != null) "without_shop_id": withoutShopId,
      "address": {
        "latitude":
            location?.latitude ??
            LocalStorage.getLatLong()?.latitude ??
            AppConstants.demoLatitude,
        "longitude":
            location?.longitude ??
            LocalStorage.getLatLong()?.longitude ??
            AppConstants.demoLongitude,
      },
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
    };
    data.addAll(filter?.toJson() ?? {});
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shops/paginate',
        queryParameters: data,
      );
      return left(ShopsPaginateResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get near shops failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<ShopFilterModel, dynamic>> getShopFilter() async {
    final data = {'lang': LocalStorage.getLanguage()?.locale};
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shop-filter',
        queryParameters: data,
      );
      return left(ShopFilterModel.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get shops filter failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<List<Galleries>, dynamic>> getShopImage(int? id) async {
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get('/api/v1/rest/shops/$id/galleries');
      return left(
        ShopImagesResponse.fromJson(response.data).data?.galleries ?? [],
      );
    } catch (e) {
      debugPrint('==> get shops images failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  ///---> Memberships <---///

  @override
  Future<Either<List<MembershipModel>, dynamic>> getMembership({
    required int page,
    required int id,
  }) async {
    try {
      final data = {
        'perPage': 5,
        'page': page,
        'lang': LocalStorage.getLanguage()?.locale,
      };
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shop/$id/memberships',
        queryParameters: data,
      );
      return left(MembershipResponse.fromJson(response.data).data ?? []);
    } catch (e) {
      debugPrint('==> get shops membership failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<MembershipModel, dynamic>> getMembershipDetails({
    required int? membershipId,
    required int? shopId,
  }) async {
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/shop/$shopId/membership/$membershipId',
      );
      return left(MembershipModel.fromJson(response.data['data']));
    } catch (e) {
      debugPrint('==> get shops membership detail failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<List<MyMembershipModel>, dynamic>> myMemberships({
    required int page,
    int? shopId,
    int? serviceId,
  }) async {
    try {
      final data = {
        'perPage': 10,
        'page': page,
        if (shopId != null) 'shop_id': shopId,
        if (serviceId != null) 'service_id': serviceId,
        'lang': LocalStorage.getLanguage()?.locale,
      };
      final client = dioHttp.client(requireAuth: true);
      final response = await client.get(
        '/api/v1/dashboard/user/user-memberships',
        queryParameters: data,
      );
      return left(MyMembershipResponse.fromJson(response.data).data ?? []);
    } catch (e) {
      debugPrint('==> get my membership failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  ///---> Gift Cart <---///

  @override
  Future<Either<List<GiftCartModel>, dynamic>> getGiftCart({
    required int page,
    required int shopId,
  }) async {
    try {
      final data = {
        'perPage': 5,
        'page': page,
        'shop_id': shopId,
        'lang': LocalStorage.getLanguage()?.locale,
      };
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/gift-carts/paginate',
        queryParameters: data,
      );
      return left(GiftCartResponse.fromJson(response.data).data ?? []);
    } catch (e) {
      debugPrint('==> get shops gift failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<List<MyGiftCartModel>, dynamic>> myGiftCarts({
    required int page,
    int? shopId,
    int? serviceId,
    bool? valid,
  }) async {
    try {
      final data = {
        'perPage': 10,
        'page': page,
        if (valid ?? false) 'valid': 1,
        if (shopId != null) 'shop_id': shopId,
        if (serviceId != null) 'service_id': serviceId,
        'lang': LocalStorage.getLanguage()?.locale,
      };
      final client = dioHttp.client(requireAuth: true);
      final response = await client.get(
        '/api/v1/dashboard/user/gift-carts/my-carts',
        queryParameters: data,
      );
      return left(MyGiftCartResponse.fromJson(response.data).data ?? []);
    } catch (e) {
      debugPrint('==> get my gift cart failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }
}
