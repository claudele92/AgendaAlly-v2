import 'package:dartz/dartz.dart';

import '../model/models.dart';

abstract class ServiceInterface {
  Future<Either<ServicePaginationResponse, dynamic>> getAllService({
    required int page,
    String? query,
    int? shopId,
    int? categoryId,
    int? masterId,
  });

  Future<Either<SingleServiceResponse, dynamic>> getSingleService({
    int? serviceId,
  });

  Future<Either<CategoriesPaginateResponse, dynamic>> getServiceCategories({
    required int page,
    String? query,
    int? shopId,
    int? masterId,
  });
}
