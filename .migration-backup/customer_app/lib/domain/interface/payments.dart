import 'package:dartz/dartz.dart';
import 'package:demand/domain/model/model/create_order_model.dart';
import 'package:demand/domain/model/response/maksekeskus_response.dart';
import 'package:demand/domain/model/response/payments_response.dart';
import 'package:demand/domain/model/response/transactions_response.dart';

abstract class PaymentsInterface {
  Future<Either<PaymentsResponse, dynamic>> getPayments({
    int? bookingId,
    int? cartId,
    int? shopId,
    int? locationType,
    int? requestedCurrencyId,
  });

  Future<Either<TransactionsResponse, dynamic>> createTransaction({
    required int orderId,
    required int paymentId,
  });

  Future<Either<TransactionsResponse, dynamic>> createMembershipTransaction({
    required int membershipId,
    int? paymentId,
    num? walletPrice,
  });

  Future<Either<TransactionsResponse, dynamic>> createGiftCartTransaction({
    required int giftCartId,
    int? paymentId,
    num? walletPrice,
  });

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
  });

  Future<Either<bool, dynamic>> sendWallet({
    required String uuid,
    required num price,
  });

  Future<Either<MaksekeskusResponse, dynamic>> paymentMaksekeskusView({
    CreateOrderModel? order,
    bool parcel = false,
    bool wallet = false,
    int? parcelId,
    num? price,
  });
}
