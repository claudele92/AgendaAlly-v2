import 'package:demand/domain/model/model/master_model.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/domain/model/model/user_model.dart';
import 'package:demand/domain/model/response/form_options_response.dart';
import 'package:demand/domain/model/response/membership_response.dart';
import 'package:demand/domain/model/response/review_pagination_response.dart';

import '../model/location_model.dart';
import '../model/service_extra.dart';
import 'transactions_response.dart';

class BookingResponse {
  List<BookingModel>? data;

  BookingResponse({this.data});

  BookingResponse copyWith({List<BookingModel>? data}) =>
      BookingResponse(data: data ?? this.data);

  factory BookingResponse.fromJson(Map<String, dynamic> json) =>
      BookingResponse(
        data: json["data"] == null
            ? []
            : List<BookingModel>.from(
                json["data"]!.map((x) => BookingModel.fromJson(x)),
              ),
      );

  Map<String, dynamic> toJson() => {
    "data": data == null
        ? []
        : List<dynamic>.from(data!.map((x) => x.toJson())),
  };
}

class BookingModel {
  int? id;
  int? serviceMasterId;
  int? masterId;
  int? userId;
  int? parentId;
  int? currencyId;
  ReviewModel? review;
  num? rate;
  DateTime? startDate;
  DateTime? endDate;
  num? price;
  num? commissionFee;
  num? giftCartPrice;
  String? status;
  String? note;
  DateTime? createdAt;
  DateTime? updatedAt;
  num? ratePrice;
  num? rateCommissionFee;
  num? totalPrice;
  num? totalPriceByParent;
  ServiceMaster? serviceMaster;
  MasterModel? master;
  UserModel? user;
  ShopData? shop;
  BookingAddressModel? address;
  List<TransactionData>? transactions;
  MembershipModel? membership;
  List<String>? notes;
  List<ServiceExtra>? extras;

  BookingModel({
    this.id,
    this.parentId,
    this.serviceMasterId,
    this.masterId,
    this.userId,
    this.review,
    this.currencyId,
    this.rate,
    this.totalPriceByParent,
    this.startDate,
    this.endDate,
    this.price,
    this.commissionFee,
    this.status,
    this.note,
    this.address,
    this.createdAt,
    this.updatedAt,
    this.ratePrice,
    this.transactions,
    this.rateCommissionFee,
    this.totalPrice,
    this.serviceMaster,
    this.master,
    this.shop,
    this.user,
    this.notes,
    this.membership,
    this.giftCartPrice,
    this.extras,
  });

  BookingModel copyWith({
    int? id,
    int? serviceMasterId,
    int? masterId,
    int? userId,
    int? parentId,
    int? currencyId,
    ReviewModel? review,
    num? rate,
    DateTime? startDate,
    DateTime? endDate,
    num? price,
    num? giftCartPrice,
    num? commissionFee,
    String? status,
    String? note,
    List<TransactionData>? transactions,
    DateTime? createdAt,
    DateTime? updatedAt,
    num? ratePrice,
    num? rateCommissionFee,
    num? totalPrice,
    num? totalPriceByParent,
    ServiceMaster? serviceMaster,
    MasterModel? master,
    UserModel? user,
    ShopData? shop,
    MembershipModel? membership,
    BookingAddressModel? address,
    List<String>? notes,
    List<ServiceExtra>? extras,
  }) => BookingModel(
    id: id ?? this.id,
    parentId: parentId ?? this.parentId,
    giftCartPrice: giftCartPrice ?? this.giftCartPrice,
    serviceMasterId: serviceMasterId ?? this.serviceMasterId,
    masterId: masterId ?? this.masterId,
    userId: userId ?? this.userId,
    review: review ?? this.review,
    currencyId: currencyId ?? this.currencyId,
    rate: rate ?? this.rate,
    membership: membership ?? this.membership,
    transactions: transactions ?? this.transactions,
    address: address ?? this.address,
    startDate: startDate ?? this.startDate,
    endDate: endDate ?? this.endDate,
    price: price ?? this.price,
    totalPriceByParent: totalPriceByParent ?? this.totalPriceByParent,
    commissionFee: commissionFee ?? this.commissionFee,
    status: status ?? this.status,
    note: note ?? this.note,
    createdAt: createdAt ?? this.createdAt,
    updatedAt: updatedAt ?? this.updatedAt,
    ratePrice: ratePrice ?? this.ratePrice,
    rateCommissionFee: rateCommissionFee ?? this.rateCommissionFee,
    totalPrice: totalPrice ?? this.totalPrice,
    serviceMaster: serviceMaster ?? this.serviceMaster,
    master: master ?? this.master,
    user: user ?? this.user,
    shop: shop ?? this.shop,
    notes: notes ?? this.notes,
    extras: extras ?? this.extras,
  );

  factory BookingModel.fromJson(Map<String, dynamic> json) => BookingModel(
    id: json["id"],
    parentId: json["parent_id"],
    giftCartPrice: json["gift_cart_price"],
    serviceMasterId: json["service_master_id"],
    masterId: json["master_id"],
    userId: json["user_id"],
    review: json["review"] == null
        ? null
        : ReviewModel.fromJson(json["review"]),
    transactions: json["transactions"] == null
        ? []
        : List<TransactionData>.from(
            json["transactions"].map((x) => TransactionData.fromJson(x)),
          ),
    membership: json["user_member_ship"] == null
        ? null
        : MembershipModel.fromJson(json["user_member_ship"]),
    currencyId: json["currency_id"],
    rate: json["rate"],
    startDate: json["start_date"] == null
        ? null
        : DateTime.parse(json["start_date"]),
    endDate: json["end_date"] == null ? null : DateTime.parse(json["end_date"]),
    price: json["price"],
    totalPriceByParent: json["total_price_by_parent"],
    commissionFee: json["commission_fee"],
    status: json["status"],
    note: json["note"],
    createdAt: json["created_at"] == null
        ? null
        : DateTime.parse(json["created_at"]).toLocal(),
    updatedAt: json["updated_at"] == null
        ? null
        : DateTime.parse(json["updated_at"]).toLocal(),
    ratePrice: json["rate_price"],
    rateCommissionFee: json["rate_commission_fee"],
    totalPrice: json["total_price"],
    serviceMaster: json["service_master"] == null
        ? null
        : ServiceMaster.fromJson(json["service_master"]),
    address: json["data"] == null
        ? null
        : BookingAddressModel.fromJson(json["data"]),
    master: json["master"] == null
        ? null
        : MasterModel.fromJson(json["master"]),
    shop: json["shop"] == null ? null : ShopData.fromJson(json["shop"]),
    user: json["user"] == null ? null : UserModel.fromJson(json["user"]),
    notes: json["notes"] == null
        ? null
        : List<String>.from(json["notes"].map((x) => x)),
    extras: json["extras"] == null
        ? []
        : List<ServiceExtra>.from(
            json["extras"]!.map((x) => ServiceExtra.fromJson(x)),
          ),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "parent_id": parentId,
    "service_master_id": serviceMasterId,
    "master_id": masterId,
    "review": review?.toJson(),
    "transactions": List<dynamic>.from(transactions!.map((x) => x.toJson())),
    "user_id": userId,
    "currency_id": currencyId,
    "rate": rate,
    "gift_cart_price": giftCartPrice,
    "start_date": startDate?.toIso8601String(),
    "end_date": endDate?.toIso8601String(),
    "price": price,
    "total_price_by_parent": totalPriceByParent,
    "commission_fee": commissionFee,
    "status": status,
    "note": note,
    "user_member_ship": membership?.toJson(),
    "created_at": createdAt?.toIso8601String(),
    "updated_at": updatedAt?.toIso8601String(),
    "rate_price": ratePrice,
    "rate_commission_fee": rateCommissionFee,
    "total_price": totalPrice,
    "service_master": serviceMaster?.toJson(),
    "master": master?.toJson(),
    "user": user?.toJson(),
    "shop": shop?.toJson(),
    "data": address?.toJson(),
    "extras": extras == null
        ? []
        : List<dynamic>.from(extras!.map((x) => x.toJson())),
  };
}

class BookingAddressModel {
  String? firstname;
  String? lastname;
  String? phone;
  String? zipcode;
  String? streetHouseNumber;
  String? additionalDetails;
  String? address;
  LocationModel? location;
  List<FormOptionsData>? forms;

  BookingAddressModel({
    this.forms,
    this.firstname,
    this.lastname,
    this.phone,
    this.zipcode,
    this.streetHouseNumber,
    this.additionalDetails,
    this.address,
    this.location,
  });

  factory BookingAddressModel.fromJson(Map<String, dynamic> json) =>
      BookingAddressModel(
        address: json["address"],
        forms: json["form"] == null
            ? null
            : List<FormOptionsData>.from(
                json["form"]?.map((x) => FormOptionsData.fromJson(x)),
              ),
        firstname: json["firstname"],
        lastname: json["lastname"],
        phone: json["phone"],
        zipcode: json["zipcode"],
        streetHouseNumber: json["street_house_number"],
        additionalDetails: json["additional_details"],
        location: json["location"] != null
            ? LocationModel.fromJson(json["location"])
            : null,
      );

  Map<String, dynamic> toJson() => {
    "address": address,
    "form": List<dynamic>.from(forms?.map((x) => x.toJson()) ?? []),
  };
}
