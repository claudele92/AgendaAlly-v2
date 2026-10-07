import 'package:dartz/dartz.dart';
import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/domain/model/response/blog_details_response.dart';
import 'package:demand/domain/model/response/booking_calculate_response.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/domain/model/response/check_time_response.dart';
import 'package:demand/domain/model/response/form_options_response.dart';
import 'package:demand/domain/model/response/service_of_master_response.dart';

import '../model/response/payments_response.dart';

abstract class BookingInterface {
  Future<Either<BookingResponse, dynamic>> getBooks({
    required int page,
    required String type,
  });

  Future<Either<BookingResponse, dynamic>> getBookingById({required int id});

  Future<Either<BlogDetailsResponse, dynamic>> getBlogDetails(int id);

  Future<Either<int, dynamic>> bookingService({
    required List<MasterModel> serviceMasters,
    PaymentData? payment,
    int? giftCartId,
    String? coupon,
    num? walletPrice,
    bool isPayment = true,
  });

  Future<Either<bool, dynamic>> cancelBook(int id);

  Future<Either<bool, dynamic>> saveForm({
    required List<FormOptionsData?> form,
    required int id,
  });

  Future<Either<BookingCalculateResponse, dynamic>> calculateBooking({
    required Map<int, MasterModel> selectMasters,
    int? giftCartId,
    String? coupon,
  });

  Future<Either<CheckTimeResponse, dynamic>> checkTime({
    required DateTime start,
    required List<int> serviceId,
  });

  Future<Either<ServiceOfMasterResponse, dynamic>> getServiceOfMasters({
    required int? masterId,
  });

  Future<Either<bool, dynamic>> updateBookingNotes({
    required int? id,
    required String? note,
  });
}
