import 'package:demand/infrastructure/local_storage/local_storage.dart';

import '../model/location_model.dart';

class AddUserAddressModel {
  final String firstName;
  final String lastName;
  final String phone;
  final String zipcode;
  final String? city;
  final String detail;
  final String homeNumber;
  final LocationModel? locationModel;

  AddUserAddressModel({
    required this.firstName,
    required this.lastName,
    required this.phone,
    required this.zipcode,
    required this.city,
    required this.detail,
    required this.homeNumber,
    this.locationModel,
  });

  factory AddUserAddressModel.fromJson({
    required String firstName,
    required String lastName,
    required String phone,
    required String zipcode,
    required String? city,
    required String detail,
    required String homeNumber,
    LocationModel? locationModel,
  }) {
    return AddUserAddressModel(
      firstName: firstName,
      lastName: lastName,
      phone: phone,
      zipcode: zipcode,
      city: city,
      detail: detail,
      homeNumber: homeNumber,
      locationModel: locationModel,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      "firstname": firstName,
      "lastname": lastName,
      "phone": phone,
      "zipcode": zipcode,
      "city_id": city,
      "additional_details": detail,
      "street_house_number": homeNumber,
      if (LocalStorage.getAddress()?.countryId != null)
        "country_id": LocalStorage.getAddress()?.countryId,
      if (LocalStorage.getAddress()?.regionId != null)
        "region_id": LocalStorage.getAddress()?.regionId,
      "location": locationModel?.toJson(),
    };
  }
}
