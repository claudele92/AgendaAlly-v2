import 'package:dartz/dartz.dart';
import 'package:flutter/material.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/interface/booking.dart';
import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/domain/model/response/blog_details_response.dart';
import 'package:demand/domain/model/response/booking_calculate_response.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/domain/model/response/check_time_response.dart';
import 'package:demand/domain/model/response/form_options_response.dart';
import 'package:demand/domain/model/response/service_of_master_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';

import '../../domain/model/response/payments_response.dart';

class BookingRepository implements BookingInterface {
  @override
  Future<Either<BookingResponse, dynamic>> getBooks({
    required int page,
    required String type,
  }) async {
    final data = {
      'perPage': 5,
      'page': page,
      "parent": 1,
      if (type == TrKeys.past) 'statuses[0]': "canceled",
      if (type == TrKeys.past) 'statuses[1]': "ended",
      if (type != TrKeys.past) 'statuses[0]': "new",
      if (type != TrKeys.past) 'statuses[1]': "progress",
      if (type != TrKeys.past) 'statuses[2]': "booked",
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
    };
    try {
      final client = dioHttp.client(requireAuth: true);
      final response = await client.get(
        '/api/v1/dashboard/user/bookings',
        queryParameters: data,
      );
      return left(BookingResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get booking paginate failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<BlogDetailsResponse, dynamic>> getBlogDetails(int id) async {
    final data = {'lang': LocalStorage.getLanguage()?.locale};
    try {
      final client = dioHttp.client(requireAuth: true);
      final response = await client.get(
        '/api/v1/rest/blog-by-id/$id',
        queryParameters: data,
      );
      return left(BlogDetailsResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get booking list failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<int, dynamic>> bookingService({
    required List<MasterModel> serviceMasters,
    bool isPayment = true,
    PaymentData? payment,
    int? giftCartId,
    String? coupon,
    num? walletPrice,
  }) async {
    final data = {
      'currency_id': LocalStorage.getSelectedCurrency()?.id,
      "data": [
        for (var i = 0; i < serviceMasters.length; i++)
          {
            "start_date": TimeService.dateFormatYMDHm(serviceMasters[i].time),
            "service_master_id": serviceMasters[i].serviceMaster?.id,
            if (serviceMasters[i]
                    .serviceMaster
                    ?.service
                    ?.selectExtras
                    ?.isNotEmpty ??
                false)
              "service_extras": [
                ...?serviceMasters[i].serviceMaster?.service?.selectExtras?.map(
                  (e) => e.id,
                ),
              ],
            if (serviceMasters[i].membership != null)
              "user_member_ship_id": serviceMasters[i].membership?.id,
            if (serviceMasters[i].note != null &&
                (serviceMasters[i].note?.isNotEmpty ?? false))
              'note': serviceMasters[i].note,
            if (serviceMasters[i].address != null &&
                (serviceMasters[i].address != null))
              "data": serviceMasters[i].address?.toJson(),
          },
      ],
      if (coupon?.isNotEmpty ?? false) "coupon": coupon,
      if (giftCartId != null) 'user_gift_cart_id': giftCartId,
      if (isPayment) "payment_id": payment?.id,
      if (walletPrice != null && payment?.tag != 'wallet')
        "from_wallet_price": walletPrice,
    };
    debugPrint("Booking creation request prepared.");
    try {
      final client = dioHttp.client(requireAuth: true);
      final res = await client.post(
        '/api/v1/dashboard/user/bookings',
        data: data,
      );
      return left(
        (BookingResponse.fromJson(res.data).data?.isNotEmpty ?? false)
            ? (BookingResponse.fromJson(res.data).data?.first.id ?? 0)
            : 0,
      );
    } catch (e) {
      debugPrint('==> create book details failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> cancelBook(int id) async {
    final data = {'status': "canceled"};
    try {
      final client = dioHttp.client(requireAuth: true);

      await client.post(
        '/api/v1/dashboard/user/booking/parent/$id/canceled',
        data: data,
      );
      return left(true);
    } catch (e) {
      debugPrint('==> create book details failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<BookingCalculateResponse, dynamic>> calculateBooking({
    required Map<int, MasterModel> selectMasters,
    int? giftCartId,
    String? coupon,
  }) async {
    final data = {
      "currency_id": LocalStorage.getSelectedCurrency()?.id,
      if (giftCartId != null) "user_gift_cart_id": giftCartId,
      if (coupon?.isNotEmpty ?? false) "coupon": coupon,
      "data": [
        ...selectMasters.values.map(
          (e) => {
            "service_master_id": e.serviceMaster?.id,
            "start_date": TimeService.dateFormatYMDHm(e.time),
            if (e.serviceMaster?.service?.selectExtras?.isNotEmpty ?? false)
              "service_extras": [
                ...?e.serviceMaster?.service?.selectExtras?.map((e) => e.id),
              ],
            if (e.membership != null) "user_member_ship_id": e.membership?.id,
          },
        ),
      ],
    };
    debugPrint("Booking calculation request prepared.");
    try {
      final client = dioHttp.client(requireAuth: true);
      final res = await client.post(
        '/api/v1/rest/bookings/calculate',
        data: data,
      );
      return left(BookingCalculateResponse.fromJson(res.data));
    } catch (e) {
      debugPrint('==> calculate book failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<CheckTimeResponse, dynamic>> checkTime({
    required DateTime start,
    required List<int> serviceId,
  }) async {
    final data = {
      "start_date": TimeService.dateFormatYMDHm(start),
      for (int i = 0; i < serviceId.length; i++)
        "service_master_ids[$i]": serviceId[i],
    };
    try {
      final client = dioHttp.client(requireAuth: true);
      final res = await client.get(
        '/api/v1/rest/master/times-all',
        queryParameters: data,
      );
      return left(CheckTimeResponse.fromJson(res.data));
    } catch (e) {
      debugPrint('==> check time book failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<BookingResponse, dynamic>> getBookingById({
    required int id,
  }) async {
    final data = {
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
    };
    try {
      final client = dioHttp.client(requireAuth: true);
      final response = await client.get(
        '/api/v1/dashboard/user/bookings/$id/get-all',
        queryParameters: data,
      );
      return left(BookingResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get booking detail failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<ServiceOfMasterResponse, dynamic>> getServiceOfMasters({
    required int? masterId,
  }) async {
    final data = {
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
      if (LocalStorage.getAddress()?.regionId != null) 'master_id': masterId,
    };
    try {
      final client = dioHttp.client(requireAuth: true);
      final response = await client.get(
        '/api/v1/rest/service-masters',
        queryParameters: data,
      );
      return left(ServiceOfMasterResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get booking detail failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> saveForm({
    required List<FormOptionsData?> form,
    required int id,
  }) async {
    try {
      final client = dioHttp.client(requireAuth: true);

      await client.put(
        '/api/v1/dashboard/user/bookings/$id',
        data: {
          "data": {"form": List<dynamic>.from(form.map((x) => x?.toJson()))},
        },
      );
      return left(true);
    } catch (e) {
      debugPrint('==> create book details failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> updateBookingNotes({
    required int? id,
    required String? note,
  }) async {
    final data = {"note": note};

    try {
      final client = dioHttp.client(requireAuth: true);
      await client.post(
        '/api/v1/dashboard/user/bookings/$id/notes/update',
        data: data,
      );
      return left(true);
    } catch (e) {
      debugPrint('==> update booking notes failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }
}
