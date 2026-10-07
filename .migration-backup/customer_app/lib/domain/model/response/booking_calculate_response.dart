import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/domain/model/model/service_extra.dart';

class BookingCalculateResponse {
  DateTime? timestamp;
  bool? status;
  String? message;
  Calculate? data;

  BookingCalculateResponse({
    this.timestamp,
    this.status,
    this.message,
    this.data,
  });

  BookingCalculateResponse copyWith({
    DateTime? timestamp,
    bool? status,
    String? message,
    Calculate? data,
  }) => BookingCalculateResponse(
    timestamp: timestamp ?? this.timestamp,
    status: status ?? this.status,
    message: message ?? this.message,
    data: data ?? this.data,
  );

  factory BookingCalculateResponse.fromJson(Map<String, dynamic> json) =>
      BookingCalculateResponse(
        timestamp: json["timestamp"] == null
            ? null
            : DateTime.parse(json["timestamp"]),
        status: json["status"],
        message: json["message"],
        data: json["data"] == null ? null : Calculate.fromJson(json["data"]),
      );

  Map<String, dynamic> toJson() => {
    "timestamp": timestamp?.toIso8601String(),
    "status": status,
    "message": message,
    "data": data?.toJson(),
  };
}

class Calculate {
  bool? status;
  num? price;
  num? totalPrice;
  num? couponPrice;
  num? totalDiscount;
  num? totalExtraPrice;
  num? totalCommissionFee;
  num? totalServiceFee;
  num? totalGiftCartPrice;
  num? rate;
  List<Item>? items;

  Calculate({
    this.status,
    this.price,
    this.couponPrice,
    this.totalPrice,
    this.totalDiscount,
    this.totalCommissionFee,
    this.totalServiceFee,
    this.totalExtraPrice,
    this.totalGiftCartPrice,
    this.rate,
    this.items,
  });

  Calculate copyWith({
    bool? status,
    num? price,
    num? totalPrice,
    num? couponPrice,
    num? totalDiscount,
    num? totalExtraPrice,
    num? totalCommissionFee,
    num? totalServiceFee,
    num? totalGiftCartPrice,
    num? rate,
    List<Item>? items,
  }) => Calculate(
    status: status ?? this.status,
    price: price ?? this.price,
    couponPrice: couponPrice ?? this.couponPrice,
    totalPrice: totalPrice ?? this.totalPrice,
    totalDiscount: totalDiscount ?? this.totalDiscount,
    totalCommissionFee: totalCommissionFee ?? this.totalCommissionFee,
    totalServiceFee: totalServiceFee ?? this.totalServiceFee,
    totalExtraPrice: totalExtraPrice ?? this.totalExtraPrice,
    rate: rate ?? this.rate,
    totalGiftCartPrice: totalGiftCartPrice ?? this.totalGiftCartPrice,
    items: items ?? this.items,
  );

  factory Calculate.fromJson(Map<String, dynamic> json) => Calculate(
    status: json["status"],
    price: json["price"],
    couponPrice: json["coupon_price"],
    totalPrice: json["total_price"],
    totalDiscount: json["total_discount"],
    totalCommissionFee: json["total_commission_fee"],
    totalServiceFee: json["total_service_fee"],
    totalExtraPrice: json["total_extra_price"],
    totalGiftCartPrice: json["total_gift_cart_price"],
    rate: json["rate"],
    items: json["items"] == null
        ? []
        : List<Item>.from(json["items"]!.map((x) => Item.fromJson(x))),
  );

  Map<String, dynamic> toJson() => {
    "status": status,
    "price": price,
    "total_price": totalPrice,
    "coupon_price": couponPrice,
    "total_discount": totalDiscount,
    "total_commission_fee": totalCommissionFee,
    "total_service_fee": totalServiceFee,
    "total_gift_cart_price": totalGiftCartPrice,
    "total_extra_price": totalExtraPrice,
    "rate": rate,
    "items": items == null
        ? []
        : List<dynamic>.from(items!.map((x) => x.toJson())),
  };
}

class Item {
  ServiceMaster? serviceMaster;
  num? serviceFee;
  num? totalPrice;
  num? price;
  num? giftCartPrice;
  DateTime? startDate;
  List<String>? errors;
  List<ServiceExtra>? extras;
  DateTime? endDate;

  Item({
    this.serviceMaster,
    this.serviceFee,
    this.giftCartPrice,
    this.totalPrice,
    this.startDate,
    this.errors,
    this.endDate,
    this.price,
    this.extras,
  });

  Item copyWith({
    ServiceMaster? serviceMaster,
    num? serviceFee,
    num? giftCartPrice,
    num? price,
    num? totalPrice,
    DateTime? startDate,
    List<String>? errors,
    DateTime? endDate,
    List<ServiceExtra>? extras,
  }) => Item(
    serviceMaster: serviceMaster ?? this.serviceMaster,
    serviceFee: serviceFee ?? this.serviceFee,
    giftCartPrice: giftCartPrice ?? this.giftCartPrice,
    totalPrice: totalPrice ?? this.totalPrice,
    startDate: startDate ?? this.startDate,
    endDate: endDate ?? this.endDate,
    errors: errors ?? this.errors,
    extras: extras ?? this.extras,
    price: price ?? this.price,
  );

  factory Item.fromJson(Map<String, dynamic> json) => Item(
    serviceMaster: json["service_master"] == null
        ? null
        : ServiceMaster.fromJson(json["service_master"]),
    serviceFee: json["service_fee"],
    giftCartPrice: json["gift_cart_price"],
    price: json["price"],
    totalPrice: json["total_price"],
    startDate: json["start_date"] == null
        ? null
        : DateTime.parse(json["start_date"]),
    errors: json["errors"] == null
        ? []
        : List<String>.from(json["errors"] ?? []),
    extras: json["extras"] == null
        ? []
        : List<ServiceExtra>.from(
            json["extras"]!.map((x) => ServiceExtra.fromJson(x)),
          ),
    endDate: json["end_date"] == null ? null : DateTime.parse(json["end_date"]),
  );

  Map<String, dynamic> toJson() => {
    "service_master": serviceMaster?.toJson(),
    "service_fee": serviceFee,
    "price": price,
    "gift_cart_price": giftCartPrice,
    "total_price": totalPrice,
    "start_date": startDate,
    "errors": errors == null ? [] : List<dynamic>.from(errors ?? []),
    "end_date": endDate?.toIso8601String(),
    "extras": extras == null
        ? []
        : List<dynamic>.from(extras!.map((x) => x.toJson())),
  };
}
