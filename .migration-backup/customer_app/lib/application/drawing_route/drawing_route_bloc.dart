import 'package:demand/app_constants.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/model/model/user_model.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:demand/domain/model/models.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:map_launcher/map_launcher.dart';

part 'drawing_route_event.dart';

part 'drawing_route_state.dart';

part 'drawing_route_bloc.freezed.dart';

class DrawingRouteBloc extends Bloc<DrawingRouteEvent, DrawingRouteState> {
  DrawingRouteBloc() : super(const DrawingRouteState()) {
    on<GetRoutingAll>((event, emit) async {
      final LatLng start;
      final LatLng end;
      if (event.parcel != null) {
        start = LatLng(
          event.parcel?.addressFrom?.latitude ??
              AppHelpers.getInitialLatitude(),
          event.parcel?.addressFrom?.longitude ??
              AppHelpers.getInitialLongitude(),
        );

        end = LatLng(
          event.parcel?.addressTo?.latitude ?? AppHelpers.getInitialLatitude(),
          event.parcel?.addressTo?.longitude ??
              AppHelpers.getInitialLongitude(),
        );
      } else if (event.order?.deliveryType == "point") {
        return;
      } else {
        final OrderShops? order = event.order;
        if (order?.status == 'on_a_way') {
          start = LatLng(
            state.driverLocation?.latitude ??
                LocalStorage.getWareHouse().location?.latitude ??
                AppHelpers.getInitialLatitude(),
            state.driverLocation?.longitude ??
                LocalStorage.getWareHouse().location?.longitude ??
                AppHelpers.getInitialLongitude(),
          );
        } else {
          start = LatLng(
            LocalStorage.getWareHouse().location?.latitude ??
                AppHelpers.getInitialLatitude(),
            LocalStorage.getWareHouse().location?.longitude ??
                AppHelpers.getInitialLongitude(),
          );
        }
        end = LatLng(
          order?.address?.location?.latitude ?? AppHelpers.getInitialLatitude(),
          order?.address?.location?.longitude ??
              AppHelpers.getInitialLongitude(),
        );
      }

      emit(state.copyWith(polylineCoordinates: [], isLoading: true));
      final response = await addressRepository.getRouting(
        start: start,
        end: end,
      );
      response.fold(
        (l) {
          List<LatLng> list = [];
          List ls = l.features[0].geometry.coordinates;
          for (int i = 0; i < ls.length; i++) {
            list.add(LatLng(ls[i][1], ls[i][0]));
          }
          emit(state.copyWith(polylineCoordinates: list, isLoading: false));
        },
        (r) => emit(state.copyWith(polylineCoordinates: [], isLoading: false)),
      );
    });

    on<SetMarkers>((event, emit) async {
      final ImageCropperForMarker image = ImageCropperForMarker();
      // add(DrawingRouteEvent.updateDriverLocation(event.order?.de));
      if (event.parcel != null) {
        final parcel = event.parcel;
        Map<MarkerId, Marker> list = {
          const MarkerId("user"): Marker(
            markerId: const MarkerId("user"),
            onTap: () {
              AppHelpers.showCustomModalBottomSheet(
                event.context,
                modal: Container(
                  padding: EdgeInsets.only(top: 32.r),
                  decoration: BoxDecoration(
                    color: event.colors.backgroundColor,
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(AppConstants.radius.r),
                      topLeft: Radius.circular(AppConstants.radius.r),
                    ),
                  ),
                  child: MapsList(
                    colors: event.colors,
                    location: Coords(
                      event.parcel?.addressFrom?.latitude ??
                          AppHelpers.getInitialLatitude(),
                      event.parcel?.addressFrom?.longitude ??
                          AppHelpers.getInitialLongitude(),
                    ),
                    title: event.parcel?.user?.firstname ?? '',
                  ),
                ),
              );
            },
            position: LatLng(
              parcel?.addressFrom?.latitude ?? AppHelpers.getInitialLatitude(),
              parcel?.addressFrom?.longitude ??
                  AppHelpers.getInitialLongitude(),
            ),
            icon: await image.resizeAndCircle(
              LocalStorage.getUser().img ?? "",
              120,
            ),
          ),
          const MarkerId("receiver"): Marker(
            markerId: const MarkerId("receiver"),
            onTap: () {
              AppHelpers.showCustomModalBottomSheet(
                event.context,
                modal: Container(
                  padding: EdgeInsets.only(top: 32.r),
                  decoration: BoxDecoration(
                    color: event.colors.backgroundColor,
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(AppConstants.radius.r),
                      topLeft: Radius.circular(AppConstants.radius.r),
                    ),
                  ),
                  child: MapsList(
                    colors: event.colors,
                    location: Coords(
                      parcel?.addressTo?.latitude ??
                          AppHelpers.getInitialLatitude(),
                      parcel?.addressTo?.longitude ??
                          AppHelpers.getInitialLongitude(),
                    ),
                    title: "Githubit",
                  ),
                ),
              );
            },
            position: LatLng(
              parcel?.addressTo?.latitude ?? AppHelpers.getInitialLatitude(),
              parcel?.addressTo?.longitude ?? AppHelpers.getInitialLongitude(),
            ),
            icon: await image.resizeAndCircle("", 120),
          ),
        };
        emit(state.copyWith(markers: list));
        return;
      }
      if (event.order?.deliveryType == "point") {
        final point = event.order?.deliveryPoint;
        Map<MarkerId, Marker> list = {
          const MarkerId("point"): Marker(
            markerId: const MarkerId("point"),
            onTap: () {
              AppHelpers.showCustomModalBottomSheet(
                event.context,
                modal: Container(
                  padding: EdgeInsets.only(top: 32.r),
                  decoration: BoxDecoration(
                    color: event.colors.backgroundColor,
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(AppConstants.radius.r),
                      topLeft: Radius.circular(AppConstants.radius.r),
                    ),
                  ),
                  child: MapsList(
                    colors: event.colors,
                    location: Coords(
                      point?.location?.latitude ??
                          AppHelpers.getInitialLatitude(),
                      point?.location?.longitude ??
                          AppHelpers.getInitialLongitude(),
                    ),
                    title: "User",
                  ),
                ),
              );
            },
            position: LatLng(
              point?.location?.latitude ?? AppHelpers.getInitialLatitude(),
              point?.location?.longitude ?? AppHelpers.getInitialLongitude(),
            ),
            icon: await image.resizeAndCircle(point?.img, 120),
          ),
        };
        emit(state.copyWith(markers: list));
        return;
      }
      final order = event.order;

      final location = (order?.type == 1
          ? LocalStorage.getWareHouse().location
          : order?.shop?.location);
      emit(state.copyWith(driver: order?.deliveryMan));
      Map<MarkerId, Marker> list = {
        const MarkerId("shop"): Marker(
          markerId: const MarkerId("shop"),
          onTap: () {
            AppHelpers.showCustomModalBottomSheet(
              event.context,
              modal: Container(
                padding: EdgeInsets.only(top: 32.r),
                decoration: BoxDecoration(
                  color: event.colors.backgroundColor,
                  borderRadius: BorderRadius.only(
                    topRight: Radius.circular(AppConstants.radius.r),
                    topLeft: Radius.circular(AppConstants.radius.r),
                  ),
                ),
                child: MapsList(
                  colors: event.colors,
                  location: Coords(
                    location?.latitude ?? AppHelpers.getInitialLatitude(),
                    location?.longitude ?? AppHelpers.getInitialLongitude(),
                  ),
                  title: "shop",
                ),
              ),
            );
          },
          position: LatLng(
            location?.latitude ?? AppHelpers.getInitialLatitude(),
            location?.longitude ?? AppHelpers.getInitialLongitude(),
          ),
          icon: await image.resizeAndCircle(
            order?.type == 1
                ? LocalStorage.getWareHouse().img
                : order?.shop?.logoImg,
            120,
          ),
        ),
        const MarkerId("user"): Marker(
          markerId: const MarkerId("user"),
          onTap: () {
            AppHelpers.showCustomModalBottomSheet(
              event.context,
              modal: Container(
                padding: EdgeInsets.only(top: 32.r),
                decoration: BoxDecoration(
                  color: event.colors.backgroundColor,
                  borderRadius: BorderRadius.only(
                    topRight: Radius.circular(AppConstants.radius.r),
                    topLeft: Radius.circular(AppConstants.radius.r),
                  ),
                ),
                child: MapsList(
                  colors: event.colors,
                  location: Coords(
                    order?.address?.location?.latitude ??
                        AppHelpers.getInitialLatitude(),
                    order?.address?.location?.longitude ??
                        AppHelpers.getInitialLongitude(),
                  ),
                  title: "user",
                ),
              ),
            );
          },
          position: LatLng(
            order?.address?.location?.latitude ??
                AppHelpers.getInitialLatitude(),
            order?.address?.location?.longitude ??
                AppHelpers.getInitialLongitude(),
          ),
          icon: await image.resizeAndCircle(
            LocalStorage.getUser().img ?? "",
            120,
          ),
        ),
      };
      emit(state.copyWith(markers: list));
    });

    on<UpdateDriverLocation>((event, emit) async {
      if (event.location == null) return;

      final newLocation = LatLng(
        event.location!.latitude,
        event.location!.longitude,
      );

      emit(state.copyWith(driverLocation: newLocation));
      final MarkerId driverMarkerId = const MarkerId("driver");
      final existingMarkers = Map<MarkerId, Marker>.from(state.markers);

      if (existingMarkers.containsKey(driverMarkerId)) {
        final Marker oldMarker = existingMarkers[driverMarkerId]!;
        final updatedMarker = oldMarker.copyWith(positionParam: newLocation);
        existingMarkers[driverMarkerId] = updatedMarker;
        emit(state.copyWith(markers: existingMarkers));
      } else {
        final ImageCropperForMarker image = ImageCropperForMarker();
        final driverMarker = Marker(
          markerId: driverMarkerId,
          position: newLocation,
          icon: await image.resizeAndCircle(state.driver?.img ?? '', 120),
          infoWindow: const InfoWindow(
            title: "Driver",
            snippet: "Delivery in progress",
          ),
        );
        existingMarkers[driverMarkerId] = driverMarker;
        emit(state.copyWith(markers: existingMarkers));
      }
    });
  }
}
