part of 'map_bloc.dart';

@freezed
abstract class MapState with _$MapState {
  const factory MapState({
    @Default(false) bool isSearching,
    @Default(false) bool isSearchLoading,
    @Default(false) bool isChoosing,
    @Default([]) List<Place> searchedPlaces,
    @Default({}) Set<Marker> markers,
    @Default(null) AddressInfo? address,
    TextEditingController? textController,
    GoogleMapController? mapController,
    LatLng? location,
  }) = _MapState;
}
