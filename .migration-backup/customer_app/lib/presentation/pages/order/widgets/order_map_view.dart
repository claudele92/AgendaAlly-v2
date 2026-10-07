import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:demand/application/drawing_route/drawing_route_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:demand/domain/model/models.dart';

class OrderMapView extends StatefulWidget {
  final CustomColorSet colors;
  final OrderShops? order;
  final ParcelOrder? parcel;

  const OrderMapView({
    super.key,
    required this.colors,
    this.order,
    this.parcel,
  });

  @override
  State<OrderMapView> createState() => _OrderMapViewState();
}

class _OrderMapViewState extends State<OrderMapView> {
  GoogleMapController? controller;
  // WebSocketOrderClient? _webSocketClient;

  @override
  void initState() {
    super.initState();
    _initWebSocket();
  }

  void _initWebSocket() {
    final orderId = widget.order?.id ?? widget.parcel?.id;
    if (orderId != null) {
      // _webSocketClient = WebSocketOrderClient(
      //   orderId: orderId,
      //   driverLocationEvent: (location) {
      //     if (mounted) {
      //       context.read<DrawingRouteBloc>().add(
      //         DrawingRouteEvent.updateDriverLocation(location),
      //       );
      //       context.read<DrawingRouteBloc>().add(
      //         DrawingRouteEvent.getRoutingAll(context, order: widget.order),
      //       );
      //     }
      //   },
      //   orderUpdateEvent: (OrderShops order) {
      //     if (mounted) {
      //       context.read<OrderBloc>().add(
      //         OrderEvent.fetchOrderById(context, id: order.id!),
      //       );
      //     }
      //   },
      // );
      // _webSocketClient?.connect();
    }
  }

  @override
  void dispose() {
    // _webSocketClient?.dispose();
    controller?.dispose();
    super.dispose();
  }

  LatLngBounds _bounds(Set<Marker> markers) {
    if (markers.isEmpty) {
      return LatLngBounds(
        southwest: const LatLng(0, 0),
        northeast: const LatLng(0, 0),
      );
    }
    return _createBounds(markers.map((m) => m.position).toList());
  }

  LatLngBounds _createBounds(List<LatLng> positions) {
    final southwestLat = positions
        .map((p) => p.latitude)
        .reduce((value, element) => value < element ? value : element);
    final southwestLon = positions
        .map((p) => p.longitude)
        .reduce((value, element) => value < element ? value : element);
    final northeastLat = positions
        .map((p) => p.latitude)
        .reduce((value, element) => value > element ? value : element);
    final northeastLon = positions
        .map((p) => p.longitude)
        .reduce((value, element) => value > element ? value : element);
    return LatLngBounds(
      southwest: LatLng(southwestLat, southwestLon),
      northeast: LatLng(northeastLat, northeastLon),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: MediaQuery.viewInsetsOf(context),
      width: double.infinity,
      decoration: BoxDecoration(
        color: widget.colors.newBoxColor,
        borderRadius: BorderRadius.only(
          topRight: Radius.circular(24.r),
          topLeft: Radius.circular(24.r),
        ),
      ),
      padding: EdgeInsets.all(16.r),
      child: BlocBuilder<DrawingRouteBloc, DrawingRouteState>(
        builder: (context, state) {
          return state.isLoading
              ? const Loading()
              : ClipRRect(
                  borderRadius: BorderRadius.circular(24.r),
                  child: GoogleMap(
                    padding: REdgeInsets.only(bottom: 15),
                    myLocationButtonEnabled: false,
                    zoomGesturesEnabled: true,
                    markers: Set<Marker>.of(state.markers.values),
                    onMapCreated: (GoogleMapController c) {
                      controller = c;
                      if (state.markers.isNotEmpty) {
                        controller?.animateCamera(
                          state.markers.length == 1
                              ? CameraUpdate.newLatLngZoom(
                                  Set<Marker>.of(
                                    state.markers.values,
                                  ).first.position,
                                  10,
                                )
                              : CameraUpdate.newLatLngBounds(
                                  state.polylineCoordinates.isNotEmpty
                                      ? _createBounds(state.polylineCoordinates)
                                      : _bounds(
                                          Set<Marker>.of(state.markers.values),
                                        ),
                                  50,
                                ),
                        );
                      }
                    },
                    polylines: widget.order?.deliveryType != "point"
                        ? {
                            Polyline(
                              polylineId: const PolylineId("market"),
                              points: state.polylineCoordinates,
                              color: widget.colors.primary,
                              width: 6,
                            ),
                          }
                        : {},
                    initialCameraPosition: CameraPosition(
                      target: LatLng(
                        AppHelpers.getInitialLatitude(),
                        AppHelpers.getInitialLongitude(),
                      ),
                      zoom: 10,
                    ),
                    mapToolbarEnabled: false,
                    zoomControlsEnabled: false,
                  ),
                );
        },
      ),
    );
  }
}
