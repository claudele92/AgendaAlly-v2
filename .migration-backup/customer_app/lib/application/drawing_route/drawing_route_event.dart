part of 'drawing_route_bloc.dart';

@freezed
abstract class DrawingRouteEvent with _$DrawingRouteEvent {
  const factory DrawingRouteEvent.getRoutingAll(
    BuildContext context, {
    OrderShops? order,
    ParcelOrder? parcel,
  }) = GetRoutingAll;

  const factory DrawingRouteEvent.setMarkers(
    BuildContext context, {
    required CustomColorSet colors,
    OrderShops? order,
    ParcelOrder? parcel,
  }) = SetMarkers;

  const factory DrawingRouteEvent.updateDriverLocation(LocationModel? location) =
      UpdateDriverLocation;
}
