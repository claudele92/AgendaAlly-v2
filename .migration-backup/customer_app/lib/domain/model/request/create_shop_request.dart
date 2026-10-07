import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/infrastructure/service/services.dart';

import '../model/location_model.dart';

class CreateShopModel {
  final String? logo;
  final String? bgImage;
  final String? shopName;
  final String? description;
  final String? deliveryTimeType;
  final String? deliveryType;
  final String? phone;
  final String deliveryTo;
  final String deliveryFrom;
  final String minAmount;
  final String tax;
  final List<String>? documents;
  final LocationModel? location;

  CreateShopModel({
    this.logo,
    this.bgImage,
    this.shopName,
    this.description,
    this.deliveryTimeType,
    this.deliveryType,
    this.phone,
    this.documents,
    required this.deliveryTo,
    required this.deliveryFrom,
    required this.minAmount,
    required this.tax,
    required this.location,
  });

  factory CreateShopModel.fromJson({
    required String logo,
    required String bgImage,
    required String shopName,
    required String description,
    required String deliveryTimeType,
    required String deliveryType,
    required String phone,
    required String deliveryTo,
    required String deliveryFrom,
    required String minAmount,
    required String tax,
    required List<String> filePath,
    required LocationModel? location,
  }) {
    return CreateShopModel(
      logo: logo,
      bgImage: bgImage,
      shopName: shopName,
      description: description,
      deliveryTimeType: deliveryTimeType,
      deliveryType: deliveryType,
      phone: phone,
      deliveryTo: deliveryTo,
      deliveryFrom: deliveryFrom,
      minAmount: minAmount,
      tax: tax,
      location: location,
      documents: filePath,
    );
  }

  CreateShopModel copyWith({
    String? logo,
    String? bgImage,
    List<String>? documents,
    String? shopName,
    String? description,
    String? deliveryTimeType,
    String? deliveryType,
    String? phone,
    String? deliveryTo,
    String? deliveryFrom,
    String? minAmount,
    String? tax,
    LocationModel? location,
  }) => CreateShopModel(
    logo: logo ?? this.logo,
    bgImage: bgImage ?? this.bgImage,
    shopName: shopName ?? this.shopName,
    documents: documents ?? this.documents,
    description: description ?? this.description,
    deliveryTimeType: deliveryTimeType ?? this.deliveryTimeType,
    deliveryType: deliveryType ?? this.deliveryType,
    phone: phone ?? this.phone,
    deliveryTo: deliveryTo ?? this.deliveryTo,
    deliveryFrom: deliveryFrom ?? this.deliveryFrom,
    minAmount: minAmount ?? this.minAmount,
    tax: tax ?? this.tax,
    location: location ?? this.location,
  );

  Map<String, dynamic> toJson() {
    return {
      'tax': tax,
      'phone': phone,
      'min_amount': minAmount,
      'title': {LocalStorage.getLanguage()?.locale ?? 'en': shopName},
      'description': {LocalStorage.getLanguage()?.locale ?? 'en': description},
      if (location != null)
        'address': {
          LocalStorage.getLanguage()?.locale ?? 'en': location?.address,
        },
      'images': [logo, bgImage],
      'documents': documents,
      'delivery_time_type': deliveryTimeType,
      'delivery_time_from': deliveryFrom,
      'delivery_time_to': deliveryTo,
      'delivery_type': deliveryType == TrKeys.wareHouse ? 1 : 2,
      if (location != null) 'lat_long': location?.toJson(),
    };
  }
}
