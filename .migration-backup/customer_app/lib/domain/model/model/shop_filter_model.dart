import '../response/categories_paginate_response.dart';
import 'tag_model.dart';

class ShopFilterModel {
  Map<String, String>? orderBy;
  Map<String, String>? serviceType;
  num? serviceMinPrice;
  num? serviceMaxPrice;
  int? intervalMin;
  int? intervalMax;
  List<TagModel> tags;
  List<CategoryData> categories;
  Map<String, int>? gender;

  ShopFilterModel({
    this.orderBy,
    this.serviceType,
    this.serviceMinPrice,
    this.serviceMaxPrice,
    this.intervalMin,
    this.intervalMax,
    required this.tags,
    this.gender,
    required this.categories,
  });

  ShopFilterModel copyWith({
    Map<String, String>? orderBy,
    Map<String, String>? serviceType,
    num? serviceMinPrice,
    num? serviceMaxPrice,
    int? intervalMin,
    int? intervalMax,
    List<TagModel>? tags,
    List<CategoryData>? categories,
    Map<String, int>? gender,
  }) => ShopFilterModel(
    orderBy: orderBy ?? this.orderBy,
    serviceType: serviceType ?? this.serviceType,
    serviceMinPrice: serviceMinPrice ?? this.serviceMinPrice,
    serviceMaxPrice: serviceMaxPrice ?? this.serviceMaxPrice,
    intervalMin: intervalMin ?? this.intervalMin,
    intervalMax: intervalMax ?? this.intervalMax,
    tags: tags ?? this.tags,
    categories: categories ?? this.categories,
    gender: gender ?? this.gender,
  );

  factory ShopFilterModel.fromJson(Map<String, dynamic> json) =>
      ShopFilterModel(
        orderBy: Map.from(json["order_by"]),
        serviceType: Map.from(json["service_type"]),
        serviceMinPrice: json["service_min_price"],
        serviceMaxPrice: json["service_max_price"],
        intervalMin: json["interval_min"],
        intervalMax: json["interval_max"],
        tags: json["takes"] == null
            ? []
            : List<TagModel>.from(
                json["takes"]!.map((x) => TagModel.fromJson(x)),
              ),
        categories: json["categories"] == null
            ? []
            : List<CategoryData>.from(
                json["categories"]!.map((x) => CategoryData.fromJson(x)),
              ),
        gender: Map.from(json["gender"]),
      );

  Map<String, dynamic> toJson() => {
    "order_by": orderBy,
    "service_type": serviceType,
    "service_min_price": serviceMinPrice,
    "service_max_price": serviceMaxPrice,
    "interval_min": intervalMin,
    "interval_max": intervalMax,
    "takes": List<dynamic>.from(tags.map((x) => x.toJson())),
    "categories": List<dynamic>.from(categories.map((x) => x.toJson())),
    "gender": gender,
  };
}
