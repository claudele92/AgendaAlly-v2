// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'drawing_route_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$DrawingRouteEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DrawingRouteEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'DrawingRouteEvent()';
}


}

/// @nodoc
class $DrawingRouteEventCopyWith<$Res>  {
$DrawingRouteEventCopyWith(DrawingRouteEvent _, $Res Function(DrawingRouteEvent) __);
}


/// Adds pattern-matching-related methods to [DrawingRouteEvent].
extension DrawingRouteEventPatterns on DrawingRouteEvent {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( GetRoutingAll value)?  getRoutingAll,TResult Function( SetMarkers value)?  setMarkers,TResult Function( UpdateDriverLocation value)?  updateDriverLocation,required TResult orElse(),}){
final _that = this;
switch (_that) {
case GetRoutingAll() when getRoutingAll != null:
return getRoutingAll(_that);case SetMarkers() when setMarkers != null:
return setMarkers(_that);case UpdateDriverLocation() when updateDriverLocation != null:
return updateDriverLocation(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( GetRoutingAll value)  getRoutingAll,required TResult Function( SetMarkers value)  setMarkers,required TResult Function( UpdateDriverLocation value)  updateDriverLocation,}){
final _that = this;
switch (_that) {
case GetRoutingAll():
return getRoutingAll(_that);case SetMarkers():
return setMarkers(_that);case UpdateDriverLocation():
return updateDriverLocation(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( GetRoutingAll value)?  getRoutingAll,TResult? Function( SetMarkers value)?  setMarkers,TResult? Function( UpdateDriverLocation value)?  updateDriverLocation,}){
final _that = this;
switch (_that) {
case GetRoutingAll() when getRoutingAll != null:
return getRoutingAll(_that);case SetMarkers() when setMarkers != null:
return setMarkers(_that);case UpdateDriverLocation() when updateDriverLocation != null:
return updateDriverLocation(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  OrderShops? order,  ParcelOrder? parcel)?  getRoutingAll,TResult Function( BuildContext context,  CustomColorSet colors,  OrderShops? order,  ParcelOrder? parcel)?  setMarkers,TResult Function( LocationModel? location)?  updateDriverLocation,required TResult orElse(),}) {final _that = this;
switch (_that) {
case GetRoutingAll() when getRoutingAll != null:
return getRoutingAll(_that.context,_that.order,_that.parcel);case SetMarkers() when setMarkers != null:
return setMarkers(_that.context,_that.colors,_that.order,_that.parcel);case UpdateDriverLocation() when updateDriverLocation != null:
return updateDriverLocation(_that.location);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  OrderShops? order,  ParcelOrder? parcel)  getRoutingAll,required TResult Function( BuildContext context,  CustomColorSet colors,  OrderShops? order,  ParcelOrder? parcel)  setMarkers,required TResult Function( LocationModel? location)  updateDriverLocation,}) {final _that = this;
switch (_that) {
case GetRoutingAll():
return getRoutingAll(_that.context,_that.order,_that.parcel);case SetMarkers():
return setMarkers(_that.context,_that.colors,_that.order,_that.parcel);case UpdateDriverLocation():
return updateDriverLocation(_that.location);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  OrderShops? order,  ParcelOrder? parcel)?  getRoutingAll,TResult? Function( BuildContext context,  CustomColorSet colors,  OrderShops? order,  ParcelOrder? parcel)?  setMarkers,TResult? Function( LocationModel? location)?  updateDriverLocation,}) {final _that = this;
switch (_that) {
case GetRoutingAll() when getRoutingAll != null:
return getRoutingAll(_that.context,_that.order,_that.parcel);case SetMarkers() when setMarkers != null:
return setMarkers(_that.context,_that.colors,_that.order,_that.parcel);case UpdateDriverLocation() when updateDriverLocation != null:
return updateDriverLocation(_that.location);case _:
  return null;

}
}

}

/// @nodoc


class GetRoutingAll implements DrawingRouteEvent {
  const GetRoutingAll(this.context, {this.order, this.parcel});
  

 final  BuildContext context;
 final  OrderShops? order;
 final  ParcelOrder? parcel;

/// Create a copy of DrawingRouteEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GetRoutingAllCopyWith<GetRoutingAll> get copyWith => _$GetRoutingAllCopyWithImpl<GetRoutingAll>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GetRoutingAll&&(identical(other.context, context) || other.context == context)&&(identical(other.order, order) || other.order == order)&&(identical(other.parcel, parcel) || other.parcel == parcel));
}


@override
int get hashCode => Object.hash(runtimeType,context,order,parcel);

@override
String toString() {
  return 'DrawingRouteEvent.getRoutingAll(context: $context, order: $order, parcel: $parcel)';
}


}

/// @nodoc
abstract mixin class $GetRoutingAllCopyWith<$Res> implements $DrawingRouteEventCopyWith<$Res> {
  factory $GetRoutingAllCopyWith(GetRoutingAll value, $Res Function(GetRoutingAll) _then) = _$GetRoutingAllCopyWithImpl;
@useResult
$Res call({
 BuildContext context, OrderShops? order, ParcelOrder? parcel
});




}
/// @nodoc
class _$GetRoutingAllCopyWithImpl<$Res>
    implements $GetRoutingAllCopyWith<$Res> {
  _$GetRoutingAllCopyWithImpl(this._self, this._then);

  final GetRoutingAll _self;
  final $Res Function(GetRoutingAll) _then;

/// Create a copy of DrawingRouteEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? order = freezed,Object? parcel = freezed,}) {
  return _then(GetRoutingAll(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderShops?,parcel: freezed == parcel ? _self.parcel : parcel // ignore: cast_nullable_to_non_nullable
as ParcelOrder?,
  ));
}


}

/// @nodoc


class SetMarkers implements DrawingRouteEvent {
  const SetMarkers(this.context, {required this.colors, this.order, this.parcel});
  

 final  BuildContext context;
 final  CustomColorSet colors;
 final  OrderShops? order;
 final  ParcelOrder? parcel;

/// Create a copy of DrawingRouteEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetMarkersCopyWith<SetMarkers> get copyWith => _$SetMarkersCopyWithImpl<SetMarkers>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetMarkers&&(identical(other.context, context) || other.context == context)&&(identical(other.colors, colors) || other.colors == colors)&&(identical(other.order, order) || other.order == order)&&(identical(other.parcel, parcel) || other.parcel == parcel));
}


@override
int get hashCode => Object.hash(runtimeType,context,colors,order,parcel);

@override
String toString() {
  return 'DrawingRouteEvent.setMarkers(context: $context, colors: $colors, order: $order, parcel: $parcel)';
}


}

/// @nodoc
abstract mixin class $SetMarkersCopyWith<$Res> implements $DrawingRouteEventCopyWith<$Res> {
  factory $SetMarkersCopyWith(SetMarkers value, $Res Function(SetMarkers) _then) = _$SetMarkersCopyWithImpl;
@useResult
$Res call({
 BuildContext context, CustomColorSet colors, OrderShops? order, ParcelOrder? parcel
});




}
/// @nodoc
class _$SetMarkersCopyWithImpl<$Res>
    implements $SetMarkersCopyWith<$Res> {
  _$SetMarkersCopyWithImpl(this._self, this._then);

  final SetMarkers _self;
  final $Res Function(SetMarkers) _then;

/// Create a copy of DrawingRouteEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? colors = null,Object? order = freezed,Object? parcel = freezed,}) {
  return _then(SetMarkers(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,colors: null == colors ? _self.colors : colors // ignore: cast_nullable_to_non_nullable
as CustomColorSet,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderShops?,parcel: freezed == parcel ? _self.parcel : parcel // ignore: cast_nullable_to_non_nullable
as ParcelOrder?,
  ));
}


}

/// @nodoc


class UpdateDriverLocation implements DrawingRouteEvent {
  const UpdateDriverLocation(this.location);
  

 final  LocationModel? location;

/// Create a copy of DrawingRouteEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UpdateDriverLocationCopyWith<UpdateDriverLocation> get copyWith => _$UpdateDriverLocationCopyWithImpl<UpdateDriverLocation>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateDriverLocation&&(identical(other.location, location) || other.location == location));
}


@override
int get hashCode => Object.hash(runtimeType,location);

@override
String toString() {
  return 'DrawingRouteEvent.updateDriverLocation(location: $location)';
}


}

/// @nodoc
abstract mixin class $UpdateDriverLocationCopyWith<$Res> implements $DrawingRouteEventCopyWith<$Res> {
  factory $UpdateDriverLocationCopyWith(UpdateDriverLocation value, $Res Function(UpdateDriverLocation) _then) = _$UpdateDriverLocationCopyWithImpl;
@useResult
$Res call({
 LocationModel? location
});




}
/// @nodoc
class _$UpdateDriverLocationCopyWithImpl<$Res>
    implements $UpdateDriverLocationCopyWith<$Res> {
  _$UpdateDriverLocationCopyWithImpl(this._self, this._then);

  final UpdateDriverLocation _self;
  final $Res Function(UpdateDriverLocation) _then;

/// Create a copy of DrawingRouteEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? location = freezed,}) {
  return _then(UpdateDriverLocation(
freezed == location ? _self.location : location // ignore: cast_nullable_to_non_nullable
as LocationModel?,
  ));
}


}

/// @nodoc
mixin _$DrawingRouteState {

 bool get isLoading; Map<MarkerId, Marker> get markers; List<LatLng> get polylineCoordinates; UserModel? get driver; LatLng? get driverLocation;
/// Create a copy of DrawingRouteState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DrawingRouteStateCopyWith<DrawingRouteState> get copyWith => _$DrawingRouteStateCopyWithImpl<DrawingRouteState>(this as DrawingRouteState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DrawingRouteState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&const DeepCollectionEquality().equals(other.markers, markers)&&const DeepCollectionEquality().equals(other.polylineCoordinates, polylineCoordinates)&&(identical(other.driver, driver) || other.driver == driver)&&(identical(other.driverLocation, driverLocation) || other.driverLocation == driverLocation));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,const DeepCollectionEquality().hash(markers),const DeepCollectionEquality().hash(polylineCoordinates),driver,driverLocation);

@override
String toString() {
  return 'DrawingRouteState(isLoading: $isLoading, markers: $markers, polylineCoordinates: $polylineCoordinates, driver: $driver, driverLocation: $driverLocation)';
}


}

/// @nodoc
abstract mixin class $DrawingRouteStateCopyWith<$Res>  {
  factory $DrawingRouteStateCopyWith(DrawingRouteState value, $Res Function(DrawingRouteState) _then) = _$DrawingRouteStateCopyWithImpl;
@useResult
$Res call({
 bool isLoading, Map<MarkerId, Marker> markers, List<LatLng> polylineCoordinates, UserModel? driver, LatLng? driverLocation
});




}
/// @nodoc
class _$DrawingRouteStateCopyWithImpl<$Res>
    implements $DrawingRouteStateCopyWith<$Res> {
  _$DrawingRouteStateCopyWithImpl(this._self, this._then);

  final DrawingRouteState _self;
  final $Res Function(DrawingRouteState) _then;

/// Create a copy of DrawingRouteState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoading = null,Object? markers = null,Object? polylineCoordinates = null,Object? driver = freezed,Object? driverLocation = freezed,}) {
  return _then(_self.copyWith(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,markers: null == markers ? _self.markers : markers // ignore: cast_nullable_to_non_nullable
as Map<MarkerId, Marker>,polylineCoordinates: null == polylineCoordinates ? _self.polylineCoordinates : polylineCoordinates // ignore: cast_nullable_to_non_nullable
as List<LatLng>,driver: freezed == driver ? _self.driver : driver // ignore: cast_nullable_to_non_nullable
as UserModel?,driverLocation: freezed == driverLocation ? _self.driverLocation : driverLocation // ignore: cast_nullable_to_non_nullable
as LatLng?,
  ));
}

}


/// Adds pattern-matching-related methods to [DrawingRouteState].
extension DrawingRouteStatePatterns on DrawingRouteState {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DrawingRouteState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DrawingRouteState() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DrawingRouteState value)  $default,){
final _that = this;
switch (_that) {
case _DrawingRouteState():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DrawingRouteState value)?  $default,){
final _that = this;
switch (_that) {
case _DrawingRouteState() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoading,  Map<MarkerId, Marker> markers,  List<LatLng> polylineCoordinates,  UserModel? driver,  LatLng? driverLocation)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DrawingRouteState() when $default != null:
return $default(_that.isLoading,_that.markers,_that.polylineCoordinates,_that.driver,_that.driverLocation);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoading,  Map<MarkerId, Marker> markers,  List<LatLng> polylineCoordinates,  UserModel? driver,  LatLng? driverLocation)  $default,) {final _that = this;
switch (_that) {
case _DrawingRouteState():
return $default(_that.isLoading,_that.markers,_that.polylineCoordinates,_that.driver,_that.driverLocation);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoading,  Map<MarkerId, Marker> markers,  List<LatLng> polylineCoordinates,  UserModel? driver,  LatLng? driverLocation)?  $default,) {final _that = this;
switch (_that) {
case _DrawingRouteState() when $default != null:
return $default(_that.isLoading,_that.markers,_that.polylineCoordinates,_that.driver,_that.driverLocation);case _:
  return null;

}
}

}

/// @nodoc


class _DrawingRouteState implements DrawingRouteState {
  const _DrawingRouteState({this.isLoading = false, final  Map<MarkerId, Marker> markers = const {}, final  List<LatLng> polylineCoordinates = const [], this.driver = null, this.driverLocation}): _markers = markers,_polylineCoordinates = polylineCoordinates;
  

@override@JsonKey() final  bool isLoading;
 final  Map<MarkerId, Marker> _markers;
@override@JsonKey() Map<MarkerId, Marker> get markers {
  if (_markers is EqualUnmodifiableMapView) return _markers;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_markers);
}

 final  List<LatLng> _polylineCoordinates;
@override@JsonKey() List<LatLng> get polylineCoordinates {
  if (_polylineCoordinates is EqualUnmodifiableListView) return _polylineCoordinates;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_polylineCoordinates);
}

@override@JsonKey() final  UserModel? driver;
@override final  LatLng? driverLocation;

/// Create a copy of DrawingRouteState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DrawingRouteStateCopyWith<_DrawingRouteState> get copyWith => __$DrawingRouteStateCopyWithImpl<_DrawingRouteState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _DrawingRouteState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&const DeepCollectionEquality().equals(other._markers, _markers)&&const DeepCollectionEquality().equals(other._polylineCoordinates, _polylineCoordinates)&&(identical(other.driver, driver) || other.driver == driver)&&(identical(other.driverLocation, driverLocation) || other.driverLocation == driverLocation));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,const DeepCollectionEquality().hash(_markers),const DeepCollectionEquality().hash(_polylineCoordinates),driver,driverLocation);

@override
String toString() {
  return 'DrawingRouteState(isLoading: $isLoading, markers: $markers, polylineCoordinates: $polylineCoordinates, driver: $driver, driverLocation: $driverLocation)';
}


}

/// @nodoc
abstract mixin class _$DrawingRouteStateCopyWith<$Res> implements $DrawingRouteStateCopyWith<$Res> {
  factory _$DrawingRouteStateCopyWith(_DrawingRouteState value, $Res Function(_DrawingRouteState) _then) = __$DrawingRouteStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoading, Map<MarkerId, Marker> markers, List<LatLng> polylineCoordinates, UserModel? driver, LatLng? driverLocation
});




}
/// @nodoc
class __$DrawingRouteStateCopyWithImpl<$Res>
    implements _$DrawingRouteStateCopyWith<$Res> {
  __$DrawingRouteStateCopyWithImpl(this._self, this._then);

  final _DrawingRouteState _self;
  final $Res Function(_DrawingRouteState) _then;

/// Create a copy of DrawingRouteState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoading = null,Object? markers = null,Object? polylineCoordinates = null,Object? driver = freezed,Object? driverLocation = freezed,}) {
  return _then(_DrawingRouteState(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,markers: null == markers ? _self._markers : markers // ignore: cast_nullable_to_non_nullable
as Map<MarkerId, Marker>,polylineCoordinates: null == polylineCoordinates ? _self._polylineCoordinates : polylineCoordinates // ignore: cast_nullable_to_non_nullable
as List<LatLng>,driver: freezed == driver ? _self.driver : driver // ignore: cast_nullable_to_non_nullable
as UserModel?,driverLocation: freezed == driverLocation ? _self.driverLocation : driverLocation // ignore: cast_nullable_to_non_nullable
as LatLng?,
  ));
}


}

// dart format on
