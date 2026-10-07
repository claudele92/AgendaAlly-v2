import 'package:dartz/dartz.dart';
import 'package:flutter/cupertino.dart';
import 'package:demand/domain/model/response/cart_calculate_response.dart';
import 'package:demand/domain/model/response/cart_response.dart';
import 'package:demand/domain/model/response/product_calculate_response.dart';

abstract class CartInterface {
  Future<Either<CartModel, dynamic>> insertCart();

  Future<Either<CartModel, dynamic>> insertGroupCart();

  Future<Either<ProductCalculateResponse, dynamic>> productCalculateCart();

  Future<Either<CartModel, dynamic>> getCart();

  Future<Either<String, dynamic>> createLink({required int cartId});

  Future<Either<CartModel, dynamic>> startGroupOrder({int? cartId});

  Future<Either<bool, dynamic>> deleteCart({required int cartId});

  Future joinGroupOrder(
    BuildContext context, {
    required int cartId,
    required String name,
  });

  Future<Either<bool, dynamic>> deleteUser({
    required int cartId,
    required String uuid,
  });

  Future<Either<CartModel, dynamic>> getGroupCart();

  Future<Either<bool, dynamic>> changeStatus({
    required int cartId,
    required String uuid,
  });

  Future<Either<CartModel, dynamic>> removeProductCart({
    required int cartDetailId,
  });

  Future<Either<String, dynamic>> checkCoupon({
    required String coupon,
    required int shopId,
  });

  Future<Either<CartCalculateResponse, dynamic>> calculateCart({
    required int cartId,
    required bool fullDigital,
    int? pointId,
    required int? type,
    Map<int, String>? coupon,
    int? deliveryPriceId,
    int tips,
    bool withoutDeliveryFee = false,
  });
}
