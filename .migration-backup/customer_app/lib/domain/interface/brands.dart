import 'package:dartz/dartz.dart';
import 'package:demand/domain/model/response/brands_paginate_response.dart';
import 'package:demand/domain/model/response/single_brand_response.dart';

abstract class BrandsInterface {
  Future<Either<SingleBrandResponse, dynamic>> getSingleBrand(int id);

  Future<Either<BrandsPaginateResponse, dynamic>> getAllBrands({
    String? query,
    required int page,
    int? shopId,
  });
}
