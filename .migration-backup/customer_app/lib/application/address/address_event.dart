part of 'address_bloc.dart';

@freezed
abstract class AddressEvent with _$AddressEvent {
  const factory AddressEvent.searchAddress(
    BuildContext context, {
    String? search,
  }) = SearchAddress;

  const factory AddressEvent.selectAddress(
    BuildContext context, {
    required int index,
  }) = SelectAddress;

  const factory AddressEvent.editAddress(
    BuildContext context, {
    required int? addressId,
    required AddUserAddressModel editUserAddress,
    VoidCallback? onSuccess,
  }) = EditAddress;

  const factory AddressEvent.addAddress(
    BuildContext context, {
    required AddUserAddressModel addUserAddress,
    VoidCallback? onSuccess,
  }) = AddAddress;

  const factory AddressEvent.fetchUserAddress(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchUserAddress;

  const factory AddressEvent.fetchCountry(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchCountry;

  const factory AddressEvent.searchCountry(
    BuildContext context, {
    String? search,
  }) = SearchCountry;

  const factory AddressEvent.fetchCity(
    BuildContext context, {
    required int countyId,
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchCity;

  const factory AddressEvent.searchCity(
    BuildContext context, {
    required int countyId,
    String? search,
  }) = SearchCity;
}
