import '../model/master_model.dart';

class CheckTimeResponse {
  DateTime? timestamp;
  bool? status;
  String? message;
  List<CheckData>? data;

  CheckTimeResponse({this.timestamp, this.status, this.message, this.data});

  CheckTimeResponse copyWith({
    DateTime? timestamp,
    bool? status,
    String? message,
    List<CheckData>? data,
  }) => CheckTimeResponse(
    timestamp: timestamp ?? this.timestamp,
    status: status ?? this.status,
    message: message ?? this.message,
    data: data ?? this.data,
  );

  factory CheckTimeResponse.fromJson(Map<String, dynamic> json) =>
      CheckTimeResponse(
        timestamp: json["timestamp"] == null
            ? null
            : DateTime.parse(json["timestamp"]),
        status: json["status"],
        message: json["message"],
        data: json["data"] == null
            ? []
            : List<CheckData>.from(
                json["data"]!.map((x) => CheckData.fromJson(x)),
              ),
      );

  Map<String, dynamic> toJson() => {
    "timestamp": timestamp?.toIso8601String(),
    "status": status,
    "message": message,
    "data": data == null
        ? []
        : List<dynamic>.from(data!.map((x) => x.toJson())),
  };
}

class CheckData {
  ServiceMaster? serviceMaster;
  List<EnableDate>? times;

  CheckData({this.serviceMaster, this.times});

  CheckData copyWith({ServiceMaster? serviceMaster, List<EnableDate>? times}) =>
      CheckData(
        serviceMaster: serviceMaster ?? this.serviceMaster,
        times: times ?? this.times,
      );

  factory CheckData.fromJson(Map<String, dynamic> json) => CheckData(
    serviceMaster: json["service_master"] == null
        ? null
        : ServiceMaster.fromJson(json["service_master"]),
    times: json["times"] == null
        ? []
        : List<EnableDate>.from(
            json["times"]!.map((x) => EnableDate.fromJson(x)),
          ),
  );

  Map<String, dynamic> toJson() => {
    "service_master": serviceMaster?.toJson(),
    "times": times == null
        ? []
        : List<dynamic>.from(times!.map((x) => x.toJson())),
  };
}

class EnableDate {
  DateTime? date;
  String? month;
  String? day;
  String? name;
  bool? closed;
  List<String>? times;
  List<String>? disabledTimes;

  EnableDate({
    this.date,
    this.month,
    this.day,
    this.name,
    this.closed,
    this.times,
    this.disabledTimes,
  });

  EnableDate copyWith({
    DateTime? date,
    String? month,
    String? day,
    String? name,
    bool? closed,
    List<String>? times,
    List<String>? disabledTimes,
  }) => EnableDate(
    date: date ?? this.date,
    month: month ?? this.month,
    day: day ?? this.day,
    name: name ?? this.name,
    closed: closed ?? this.closed,
    times: times ?? this.times,
    disabledTimes: disabledTimes ?? this.disabledTimes,
  );

  factory EnableDate.fromJson(Map<String, dynamic> json) => EnableDate(
    date: json["date"] == null ? null : DateTime.parse(json["date"]),
    month: json["month"],
    day: json["day"],
    name: json["name"],
    closed: json["closed"],
    times: json["times"] == null
        ? []
        : List<String>.from(json["times"]!.map((x) => x)),
    disabledTimes: json["disabled_times"] == null
        ? []
        : List<String>.from(json["disabled_times"]!.map((x) => x)),
  );

  Map<String, dynamic> toJson() => {
    "date":
        "${date!.year.toString().padLeft(4, '0')}-${date!.month.toString().padLeft(2, '0')}-${date!.day.toString().padLeft(2, '0')}",
    "month": month,
    "day": day,
    "name": name,
    "closed": closed,
    "times": times == null ? [] : List<dynamic>.from(times!.map((x) => x)),
    "disabled_times": disabledTimes == null
        ? []
        : List<dynamic>.from(disabledTimes!.map((x) => x)),
  };
}
