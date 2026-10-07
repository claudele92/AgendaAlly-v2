import 'package:dartz/dartz.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/domain/model/response/maksekeskus_response.dart';
import 'package:flutter/material.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/interface/payments.dart';
import 'package:demand/domain/model/model/create_order_model.dart';
import 'package:demand/domain/model/response/payments_response.dart';
import 'package:demand/domain/model/response/transactions_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:payfast/payfast.dart';

class PaymentsRepository implements PaymentsInterface {
  @override
  Future<Either<PaymentsResponse, dynamic>> getPayments({
    int? bookingId,
    int? cartId,
    int? shopId,
    int? locationType,
    int? requestedCurrencyId,
  }) async {
    final data = {
      'lang': LocalStorage.getLanguage()?.locale,
      'active': 1,
      if (bookingId != null) 'booking_id': bookingId,
      if (cartId != null) 'cart_id': cartId,
      if (shopId != null) 'shop_id': shopId,
      if (locationType != null) 'location_type': locationType,
      'currency_id':
          requestedCurrencyId ?? LocalStorage.getSelectedCurrency()?.id,
    };
    try {
      final client = dioHttp.client(
        requireAuth: bookingId != null || cartId != null,
      );
      final response = await client.get(
        '/api/v1/rest/payments',
        queryParameters: data,
      );
      return left(PaymentsResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get payments failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<TransactionsResponse, dynamic>> createTransaction({
    required int orderId,
    required int paymentId,
  }) async {
    final data = {'payment_sys_id': paymentId};
    try {
      final client = dioHttp.client(requireAuth: true);
      final response = await client.post(
        '/api/v1/payments/order/$orderId/transactions',
        data: data,
      );
      return left(TransactionsResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> create transaction failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<String, dynamic>> paymentWebView({
    CreateOrderModel? order,
    required String name,
    int? parcelId,
    int? bookingId,
    int? membershipId,
    int? giftCartId,
    int? walletId,
    num? price,
    num? walletPrice,
  }) async {
    try {
      final data = {
        if (parcelId != null) "parcel_id": parcelId,
        if (bookingId != null) "booking_id": bookingId,
        if (membershipId != null) "member_ship_id": membershipId,
        if (giftCartId != null) "gift_cart_id": giftCartId,
        if (walletId != null) 'wallet_id': walletId,
        if (price != null) 'total_price': price,
        if ((walletPrice ?? 0) != 0) 'from_wallet_price': walletPrice,
        "currency_id": LocalStorage.getSelectedCurrency()?.id,
      };
      final client = dioHttp.client(requireAuth: true);
      final res = await client.post(
        '/api/v1/dashboard/user/$name-process',
        data: order != null ? order.toJson(isPayment: false) : data,
      );
      if (name == "pay-fast") {
        final data = res.data["data"]["data"];
        var payfast = Payfast(
          passphrase: AppConstants.passphrase,
          paymentType: PaymentType.simplePayment,
          production: data["sandbox"] != 1,
          merchantDetails: MerchantDetails(
            merchantId: AppConstants.merchantId,
            merchantKey: AppConstants.merchantKey,
            notifyUrl: data["notify_url"],
            cancelUrl: data["cancel_url"],
            returnUrl: data["return_url"],
            paymentId: res.data["data"]["id"].toString(),
          ),
        );
        payfast.createSimplePayment(
          amount: data["amount"].toString(),
          itemName: data["item_name"],
        );
        return left(payfast.generateURL());
      }
      return left(res.data["data"]["data"]["url"] ?? "");
    } catch (e) {
      debugPrint('==> web view failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> sendWallet({
    required String uuid,
    required num price,
  }) async {
    try {
      final data = {
        'uuid': uuid,
        'price': price,
        "currency_id": LocalStorage.getSelectedCurrency()?.id,
      };

      final client = dioHttp.client(requireAuth: true);
      await client.post('/api/v1/dashboard/user/wallet/send', data: data);

      return left(true);
    } catch (e) {
      debugPrint('==> send wallet failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<TransactionsResponse, dynamic>> createMembershipTransaction({
    required int membershipId,
    int? paymentId,
    num? walletPrice,
  }) async {
    final data = {
      'payment_sys_id': paymentId,
      if (walletPrice != null) 'from_wallet_price': walletPrice,
    };
    try {
      final client = dioHttp.client(requireAuth: true);
      final response = await client.post(
        '/api/v1/payments/member-ship/$membershipId/transactions',
        data: data,
      );
      return left(TransactionsResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> create transaction membership failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<TransactionsResponse, dynamic>> createGiftCartTransaction({
    required int giftCartId,
    int? paymentId,
    num? walletPrice,
  }) async {
    final data = {
      'payment_sys_id': paymentId,
      if (walletPrice != null) 'from_wallet_price': walletPrice,
    };
    try {
      final client = dioHttp.client(requireAuth: true);
      final response = await client.post(
        '/api/v1/payments/gift-cart/$giftCartId/transactions',
        data: data,
      );
      return left(TransactionsResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> create transaction gift failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<MaksekeskusResponse, dynamic>> paymentMaksekeskusView({
    CreateOrderModel? order,
    bool parcel = false,
    bool wallet = false,
    int? parcelId,
    num? price,
  }) async {
    try {
      final data = parcel
          ? {"parcel_id": parcelId}
          : wallet
          ? {
              'wallet_id': LocalStorage.getUser().wallet?.id,
              'total_price': price ?? 0,
              "currency_id": LocalStorage.getSelectedCurrency()?.id,
            }
          : order?.toJson(isPayment: false);
      debugPrint('Payment request prepared.');
      final client = dioHttp.client(requireAuth: true);
      final res = await client.post(
        '/api/v1/dashboard/user/maksekeskus-process',
        data: data,
      );

      return left(MaksekeskusResponse.fromJson(res.data["data"]));
    } catch (e) {
      debugPrint('==> payment maksekeskus  failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }
}
