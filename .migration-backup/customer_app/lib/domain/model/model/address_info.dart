import 'package:demand/infrastructure/service/services.dart';
import 'location_model.dart';

class AddressInfo {
  double? latitude;
  double? longitude;
  String? address;
  String? zipcode;

  AddressInfo({this.latitude, this.longitude, this.address, this.zipcode});

  AddressInfo copyWith({
    double? latitude,
    double? longitude,
    String? address,
    String? zipcode,
  }) => AddressInfo(
    latitude: latitude ?? this.latitude,
    longitude: longitude ?? this.longitude,
    address: address ?? this.address,
    zipcode: zipcode ?? this.zipcode,
  );

  factory AddressInfo.fromJson(Map<String, dynamic> json) => AddressInfo(
    latitude: json["latitude"]?.toDouble(),
    longitude: json["longitude"]?.toDouble(),
    address: json["address"],
    zipcode: json["zipcode"],
  );

  Map<String, dynamic> toJson() => {
    "latitude": latitude,
    "longitude": longitude,
    "address": address,
    "zipcode": zipcode,
  };

  LocationModel toLocation() {
    return LocationModel(
      address: address,
      latitude: latitude ?? AppHelpers.getInitialLatitude(),
      longitude: longitude ?? AppHelpers.getInitialLongitude(),
    );
  }
}
