import 'package:dartz/dartz.dart';
import 'package:flutter/material.dart';

import '../../domain/di/dependency_manager.dart';
import '../../domain/interface/service.dart';
import '../../domain/model/models.dart';
import '../local_storage/local_storage.dart';
import '../service/helper.dart';

class ServiceRepository implements ServiceInterface {
  @override
  Future<Either<ServicePaginationResponse, dynamic>> getAllService({
    required int page,
    String? query,
    int? shopId,
    int? categoryId,
    int? masterId,
  }) async {
    final data = {
      'per_page': 5,
      "page": page,
      if (shopId != null) "shop_id": shopId,
      if (categoryId != null) "category_id": categoryId,
      if (masterId != null) "master_id": masterId,
      "has_master": 1,
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getSelectedCurrency()?.id != null)
        'currency_id': LocalStorage.getSelectedCurrency()?.id,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
      if (query != null) 'search': query,
    };
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/services',
        queryParameters: data,
      );
      return left(ServicePaginationResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get services failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<CategoriesPaginateResponse, dynamic>> getServiceCategories({
    required int page,
    String? query,
    int? shopId,
    int? masterId,
  }) async {
    final data = {
      'per_page': 10,
      "page": page,
      "type": "service",
      "has_service": 1,
      if (shopId != null) "shop_id": shopId,
      if (masterId != null) "master_id": masterId,
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getAddress()?.countryId != null)
        'country_id': LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.cityId != null)
        'city_id': LocalStorage.getAddress()?.cityId,
      if (LocalStorage.getAddress()?.regionId != null)
        'region_id': LocalStorage.getAddress()?.regionId,
      if (query != null) 'search': query,
    };
    try {
      final client = dioHttp.client(requireAuth: false);
      final response = await client.get(
        '/api/v1/rest/categories/paginate',
        queryParameters: data,
      );
      return left(CategoriesPaginateResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get services category failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<SingleServiceResponse, dynamic>> getSingleService({
    int? serviceId,
  }) async {
    final data = {
      'lang': LocalStorage.getLanguage()?.locale,
      if (LocalStorage.getSelectedCurrency()?.id != null)
        'currency_id': LocalStorage.getSelectedCurrency()?.id,
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
        '/api/v1/rest/services/$serviceId',
        queryParameters: data,
      );
      return left(SingleServiceResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('==> get single service failure: $e');
      return right(AppHelpers.errorHandler(e));
    }
  }
}
