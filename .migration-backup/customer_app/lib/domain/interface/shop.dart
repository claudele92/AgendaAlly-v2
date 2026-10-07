import 'package:dartz/dartz.dart';
import 'package:demand/domain/model/model/filter_shop_model.dart';
import 'package:demand/domain/model/model/location_model.dart';
import 'package:demand/domain/model/model/review_data.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/domain/model/model/story_model.dart';
import 'package:demand/domain/model/request/create_shop_request.dart';
import 'package:demand/domain/model/response/gift_cart_response.dart';
import 'package:demand/domain/model/response/membership_response.dart';
import 'package:demand/domain/model/response/my_gift_cart_response.dart';
import 'package:demand/domain/model/response/my_membership_response.dart';
import 'package:demand/domain/model/response/shops_paginate_response.dart';
import 'package:demand/domain/model/response/single_shop_response.dart';
import 'package:demand/domain/model/model/shop_filter_model.dart';

abstract class ShopsInterface {
  Future<Either<ShopsPaginateResponse, dynamic>> getAllShops({
    String? query,
    int? page,
    int? categoryId,
    FilterShopModel? filter,
    int type = 2,
  });

  Future<Either<ShopsPaginateResponse, dynamic>> getPopularShops({
    int? page,
    FilterShopModel? filter,
    int type = 2,
  });

  Future<Either<ShopsPaginateResponse, dynamic>> getNewShops({
    int? page,
    FilterShopModel? filter,
    int type = 2,
  });

  Future<Either<ShopsPaginateResponse, dynamic>> getNearShops({
    int? page,
    int? withoutShopId,
    List<int>? categoryIds,
    int type = 2,
    required LocationModel? location,
    FilterShopModel? filter,
  });

  Future<Either<SingleShopResponse, dynamic>> getSingleShop(int id);

  Future<Either<List<Galleries>, dynamic>> getShopImage(int? id);

  Future<Either<String, dynamic>> createLink({required ShopData? shop});

  Future<Either<bool, dynamic>> createShop({
    required CreateShopModel shopRequest,
  });

  Future<Either<List<List<StoryModel?>?>, dynamic>> getStory(int page);

  Future<Either<ShopsPaginateResponse, dynamic>> getShopsByIds(List<int> ids);

  Future<Either<ShopFilterModel, dynamic>> getShopFilter();

  ///---> Memberships <---///

  Future<Either<List<MembershipModel>, dynamic>> getMembership({
    required int page,
    required int id,
  });

  Future<Either<MembershipModel, dynamic>> getMembershipDetails({
    required int? membershipId,
    required int? shopId,
  });

  Future<Either<List<MyMembershipModel>, dynamic>> myMemberships({
    required int page,
    int? shopId,
    int? serviceId,
  });

  ///---> Gift Cart <---///

  Future<Either<List<GiftCartModel>, dynamic>> getGiftCart({
    required int page,
    required int shopId,
  });

  Future<Either<List<MyGiftCartModel>, dynamic>> myGiftCarts({
    required int page,
    int? shopId,
    int? serviceId,
    bool? valid,
  });
}
