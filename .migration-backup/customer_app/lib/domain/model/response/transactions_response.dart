import '../model/user_model.dart';

class TransactionsResponse {
  TransactionsResponse({
    String? timestamp,
    bool? status,
    String? message,
    TransactionData? data,
  }) {
    _timestamp = timestamp;
    _status = status;
    _message = message;
    _data = data;
  }

  TransactionsResponse.fromJson(dynamic json) {
    _timestamp = json['timestamp'];
    _status = json['status'];
    _message = json['message'];
    _data = json['data'] != null
        ? TransactionData.fromJson(json['data'])
        : null;
  }

  String? _timestamp;
  bool? _status;
  String? _message;
  TransactionData? _data;

  TransactionsResponse copyWith({
    String? timestamp,
    bool? status,
    String? message,
    TransactionData? data,
  }) => TransactionsResponse(
    timestamp: timestamp ?? _timestamp,
    status: status ?? _status,
    message: message ?? _message,
    data: data ?? _data,
  );

  String? get timestamp => _timestamp;

  bool? get status => _status;

  String? get message => _message;

  TransactionData? get data => _data;

  Map<String, dynamic> toJson() {
    final map = <String, dynamic>{};
    map['timestamp'] = _timestamp;
    map['status'] = _status;
    map['message'] = _message;
    if (_data != null) {
      map['data'] = _data?.toJson();
    }
    return map;
  }
}

class TransactionData {
  int? id;
  int? payableId;
  double? price;
  String? note;
  String? request;
  DateTime? performTime;
  String? status;
  String? type;
  String? statusDescription;
  DateTime? createdAt;
  DateTime? updatedAt;
  PaymentSystem? paymentSystem;
  UserModel? user;

  TransactionData({
    this.id,
    this.payableId,
    this.price,
    this.note,
    this.type,
    this.request,
    this.performTime,
    this.status,
    this.statusDescription,
    this.createdAt,
    this.updatedAt,
    this.paymentSystem,
    this.user,
  });

  TransactionData copyWith({
    int? id,
    int? payableId,
    double? price,
    String? note,
    String? request,
    DateTime? performTime,
    String? status,
    String? type,
    String? statusDescription,
    DateTime? createdAt,
    DateTime? updatedAt,
    PaymentSystem? paymentSystem,
    UserModel? user,
  }) => TransactionData(
    id: id ?? this.id,
    payableId: payableId ?? this.payableId,
    price: price ?? this.price,
    note: note ?? this.note,
    request: request ?? this.request,
    performTime: performTime ?? this.performTime,
    status: status ?? this.status,
    type: type ?? this.type,
    statusDescription: statusDescription ?? this.statusDescription,
    createdAt: createdAt ?? this.createdAt,
    updatedAt: updatedAt ?? this.updatedAt,
    paymentSystem: paymentSystem ?? this.paymentSystem,
    user: user ?? this.user,
  );

  factory TransactionData.fromJson(Map<String, dynamic> json) =>
      TransactionData(
        id: json["id"],
        payableId: json["payable_id"],
        price: json["price"]?.toDouble(),
        note: json["note"],
        request: json["request"],
        performTime: json["perform_time"] == null
            ? null
            : DateTime.parse(json["perform_time"]),
        status: json["status"],
        type: json["type"],
        statusDescription: json["status_description"],
        createdAt: json["created_at"] == null
            ? null
            : DateTime.parse(json["created_at"]).toLocal(),
        updatedAt: json["updated_at"] == null
            ? null
            : DateTime.parse(json["updated_at"]).toLocal(),
        paymentSystem: json["payment_system"] == null
            ? null
            : PaymentSystem.fromJson(json["payment_system"]),
        user: json["user"] == null ? null : UserModel.fromJson(json["user"]),
      );

  Map<String, dynamic> toJson() => {
    "id": id,
    "payable_id": payableId,
    "price": price,
    "note": note,
    "type": type,
    "user": user?.toJson(),
    "request": request,
    "perform_time": performTime?.toIso8601String(),
    "status": status,
    "status_description": statusDescription,
    "created_at": createdAt?.toIso8601String(),
    "updated_at": updatedAt?.toIso8601String(),
    "payment_system": paymentSystem?.toJson(),
  };
}

class PaymentSystem {
  int? id;
  String? tag;
  int? input;
  bool? active;
  DateTime? createdAt;
  DateTime? updatedAt;

  PaymentSystem({
    this.id,
    this.tag,
    this.input,
    this.active,
    this.createdAt,
    this.updatedAt,
  });

  PaymentSystem copyWith({
    int? id,
    String? tag,
    int? input,
    bool? active,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) => PaymentSystem(
    id: id ?? this.id,
    tag: tag ?? this.tag,
    input: input ?? this.input,
    active: active ?? this.active,
    createdAt: createdAt ?? this.createdAt,
    updatedAt: updatedAt ?? this.updatedAt,
  );

  factory PaymentSystem.fromJson(Map<String, dynamic> json) => PaymentSystem(
    id: json["id"],
    tag: json["tag"],
    input: json["input"],
    active: json["active"],
    createdAt: json["created_at"] == null
        ? null
        : DateTime.parse(json["created_at"]),
    updatedAt: json["updated_at"] == null
        ? null
        : DateTime.parse(json["updated_at"]),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "tag": tag,
    "input": input,
    "active": active,
    "created_at": createdAt?.toIso8601String(),
    "updated_at": updatedAt?.toIso8601String(),
  };
}

class TransactionDetails {
  TransactionDetails({
    int? id,
    int? shopId,
    num? deliveryFee,
    num? price,
    num? tax,
    String? status,
    String? deliveryDate,
    String? deliveryTime,
    String? createdAt,
    String? updatedAt,
  }) {
    _id = id;
    _shopId = shopId;
    _deliveryFee = deliveryFee;
    _price = price;
    _tax = tax;
    _status = status;
    _deliveryDate = deliveryDate;
    _deliveryTime = deliveryTime;
    _createdAt = createdAt;
    _updatedAt = updatedAt;
  }

  TransactionDetails.fromJson(dynamic json) {
    _id = json['id'];
    _shopId = json['shop_id'];
    _deliveryFee = json['delivery_fee'];
    _price = json['price'];
    _tax = json['tax'];
    _status = json['status'];
    _deliveryDate = json['delivery_date'];
    _deliveryTime = json['delivery_time'];
    _createdAt = json['created_at'];
    _updatedAt = json['updated_at'];
  }

  int? _id;
  int? _shopId;
  num? _deliveryFee;
  num? _price;
  num? _tax;
  String? _status;
  String? _deliveryDate;
  String? _deliveryTime;
  String? _createdAt;
  String? _updatedAt;

  TransactionDetails copyWith({
    int? id,
    int? shopId,
    num? deliveryFee,
    num? price,
    num? tax,
    String? status,
    String? deliveryDate,
    String? deliveryTime,
    String? createdAt,
    String? updatedAt,
  }) => TransactionDetails(
    id: id ?? _id,
    shopId: shopId ?? _shopId,
    deliveryFee: deliveryFee ?? _deliveryFee,
    price: price ?? _price,
    tax: tax ?? _tax,
    status: status ?? _status,
    deliveryDate: deliveryDate ?? _deliveryDate,
    deliveryTime: deliveryTime ?? _deliveryTime,
    createdAt: createdAt ?? _createdAt,
    updatedAt: updatedAt ?? _updatedAt,
  );

  int? get id => _id;

  int? get shopId => _shopId;

  num? get deliveryFee => _deliveryFee;

  num? get price => _price;

  num? get tax => _tax;

  String? get status => _status;

  String? get deliveryDate => _deliveryDate;

  String? get deliveryTime => _deliveryTime;

  String? get createdAt => _createdAt;

  String? get updatedAt => _updatedAt;

  Map<String, dynamic> toJson() {
    final map = <String, dynamic>{};
    map['id'] = _id;
    map['shop_id'] = _shopId;
    map['delivery_fee'] = _deliveryFee;
    map['price'] = _price;
    map['tax'] = _tax;
    map['status'] = _status;
    map['delivery_date'] = _deliveryDate;
    map['delivery_time'] = _deliveryTime;
    map['created_at'] = _createdAt;
    map['updated_at'] = _updatedAt;
    return map;
  }
}
