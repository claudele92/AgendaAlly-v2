import 'translation_model.dart';

class ServiceExtra {
  int? id;
  int? serviceId;
  int? shopId;
  bool? active;
  int? price;
  String? img;
  DateTime? createdAt;
  DateTime? updatedAt;
  Translation? translation;

  ServiceExtra({
    this.id,
    this.serviceId,
    this.shopId,
    this.active,
    this.price,
    this.img,
    this.createdAt,
    this.updatedAt,
    this.translation,
  });

  ServiceExtra copyWith({
    int? id,
    int? serviceId,
    int? shopId,
    bool? active,
    int? price,
    String? img,
    DateTime? createdAt,
    DateTime? updatedAt,
    Translation? translation,
  }) => ServiceExtra(
    id: id ?? this.id,
    serviceId: serviceId ?? this.serviceId,
    shopId: shopId ?? this.shopId,
    active: active ?? this.active,
    price: price ?? this.price,
    img: img ?? this.img,
    createdAt: createdAt ?? this.createdAt,
    updatedAt: updatedAt ?? this.updatedAt,
    translation: translation ?? this.translation,
  );

  factory ServiceExtra.fromJson(Map<String, dynamic> json) => ServiceExtra(
    id: json["id"],
    serviceId: json["service_id"],
    shopId: json["shop_id"],
    active: json["active"],
    price: json["price"],
    img: json["img"],
    createdAt: json["created_at"] == null
        ? null
        : DateTime.parse(json["created_at"]),
    updatedAt: json["updated_at"] == null
        ? null
        : DateTime.parse(json["updated_at"]),
    translation: json["translation"] == null
        ? null
        : Translation.fromJson(json["translation"]),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "service_id": serviceId,
    "shop_id": shopId,
    "active": active,
    "price": price,
    "img": img,
    "created_at": createdAt?.toIso8601String(),
    "updated_at": updatedAt?.toIso8601String(),
    "translation": translation?.toJson(),
  };
}
