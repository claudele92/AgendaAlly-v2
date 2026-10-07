// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'direct_service_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$DirectServiceEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DirectServiceEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'DirectServiceEvent()';
}


}

/// @nodoc
class $DirectServiceEventCopyWith<$Res>  {
$DirectServiceEventCopyWith(DirectServiceEvent _, $Res Function(DirectServiceEvent) __);
}


/// Adds pattern-matching-related methods to [DirectServiceEvent].
extension DirectServiceEventPatterns on DirectServiceEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( Init value)?  init,TResult Function( SelectExtraIndex value)?  selectExtraIndex,required TResult orElse(),}){
final _that = this;
switch (_that) {
case Init() when init != null:
return init(_that);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( Init value)  init,required TResult Function( SelectExtraIndex value)  selectExtraIndex,}){
final _that = this;
switch (_that) {
case Init():
return init(_that);case SelectExtraIndex():
return selectExtraIndex(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( Init value)?  init,TResult? Function( SelectExtraIndex value)?  selectExtraIndex,}){
final _that = this;
switch (_that) {
case Init() when init != null:
return init(_that);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  int? serviceId)?  init,TResult Function( ServiceExtra? extra)?  selectExtraIndex,required TResult orElse(),}) {final _that = this;
switch (_that) {
case Init() when init != null:
return init(_that.context,_that.serviceId);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that.extra);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  int? serviceId)  init,required TResult Function( ServiceExtra? extra)  selectExtraIndex,}) {final _that = this;
switch (_that) {
case Init():
return init(_that.context,_that.serviceId);case SelectExtraIndex():
return selectExtraIndex(_that.extra);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  int? serviceId)?  init,TResult? Function( ServiceExtra? extra)?  selectExtraIndex,}) {final _that = this;
switch (_that) {
case Init() when init != null:
return init(_that.context,_that.serviceId);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that.extra);case _:
  return null;

}
}

}

/// @nodoc


class Init implements DirectServiceEvent {
  const Init(this.context, {this.serviceId});
  

 final  BuildContext context;
 final  int? serviceId;

/// Create a copy of DirectServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$InitCopyWith<Init> get copyWith => _$InitCopyWithImpl<Init>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Init&&(identical(other.context, context) || other.context == context)&&(identical(other.serviceId, serviceId) || other.serviceId == serviceId));
}


@override
int get hashCode => Object.hash(runtimeType,context,serviceId);

@override
String toString() {
  return 'DirectServiceEvent.init(context: $context, serviceId: $serviceId)';
}


}

/// @nodoc
abstract mixin class $InitCopyWith<$Res> implements $DirectServiceEventCopyWith<$Res> {
  factory $InitCopyWith(Init value, $Res Function(Init) _then) = _$InitCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? serviceId
});




}
/// @nodoc
class _$InitCopyWithImpl<$Res>
    implements $InitCopyWith<$Res> {
  _$InitCopyWithImpl(this._self, this._then);

  final Init _self;
  final $Res Function(Init) _then;

/// Create a copy of DirectServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? serviceId = freezed,}) {
  return _then(Init(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,serviceId: freezed == serviceId ? _self.serviceId : serviceId // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class SelectExtraIndex implements DirectServiceEvent {
  const SelectExtraIndex({required this.extra});
  

 final  ServiceExtra? extra;

/// Create a copy of DirectServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectExtraIndexCopyWith<SelectExtraIndex> get copyWith => _$SelectExtraIndexCopyWithImpl<SelectExtraIndex>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectExtraIndex&&(identical(other.extra, extra) || other.extra == extra));
}


@override
int get hashCode => Object.hash(runtimeType,extra);

@override
String toString() {
  return 'DirectServiceEvent.selectExtraIndex(extra: $extra)';
}


}

/// @nodoc
abstract mixin class $SelectExtraIndexCopyWith<$Res> implements $DirectServiceEventCopyWith<$Res> {
  factory $SelectExtraIndexCopyWith(SelectExtraIndex value, $Res Function(SelectExtraIndex) _then) = _$SelectExtraIndexCopyWithImpl;
@useResult
$Res call({
 ServiceExtra? extra
});




}
/// @nodoc
class _$SelectExtraIndexCopyWithImpl<$Res>
    implements $SelectExtraIndexCopyWith<$Res> {
  _$SelectExtraIndexCopyWithImpl(this._self, this._then);

  final SelectExtraIndex _self;
  final $Res Function(SelectExtraIndex) _then;

/// Create a copy of DirectServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? extra = freezed,}) {
  return _then(SelectExtraIndex(
extra: freezed == extra ? _self.extra : extra // ignore: cast_nullable_to_non_nullable
as ServiceExtra?,
  ));
}


}

/// @nodoc
mixin _$DirectServiceState {

 List<ServiceExtra> get selectedExtras; bool get isLoading; ServiceModel? get service;
/// Create a copy of DirectServiceState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DirectServiceStateCopyWith<DirectServiceState> get copyWith => _$DirectServiceStateCopyWithImpl<DirectServiceState>(this as DirectServiceState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DirectServiceState&&const DeepCollectionEquality().equals(other.selectedExtras, selectedExtras)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.service, service) || other.service == service));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(selectedExtras),isLoading,service);

@override
String toString() {
  return 'DirectServiceState(selectedExtras: $selectedExtras, isLoading: $isLoading, service: $service)';
}


}

/// @nodoc
abstract mixin class $DirectServiceStateCopyWith<$Res>  {
  factory $DirectServiceStateCopyWith(DirectServiceState value, $Res Function(DirectServiceState) _then) = _$DirectServiceStateCopyWithImpl;
@useResult
$Res call({
 List<ServiceExtra> selectedExtras, bool isLoading, ServiceModel? service
});




}
/// @nodoc
class _$DirectServiceStateCopyWithImpl<$Res>
    implements $DirectServiceStateCopyWith<$Res> {
  _$DirectServiceStateCopyWithImpl(this._self, this._then);

  final DirectServiceState _self;
  final $Res Function(DirectServiceState) _then;

/// Create a copy of DirectServiceState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? selectedExtras = null,Object? isLoading = null,Object? service = freezed,}) {
  return _then(_self.copyWith(
selectedExtras: null == selectedExtras ? _self.selectedExtras : selectedExtras // ignore: cast_nullable_to_non_nullable
as List<ServiceExtra>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,service: freezed == service ? _self.service : service // ignore: cast_nullable_to_non_nullable
as ServiceModel?,
  ));
}

}


/// Adds pattern-matching-related methods to [DirectServiceState].
extension DirectServiceStatePatterns on DirectServiceState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DirectServiceState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DirectServiceState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DirectServiceState value)  $default,){
final _that = this;
switch (_that) {
case _DirectServiceState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DirectServiceState value)?  $default,){
final _that = this;
switch (_that) {
case _DirectServiceState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<ServiceExtra> selectedExtras,  bool isLoading,  ServiceModel? service)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DirectServiceState() when $default != null:
return $default(_that.selectedExtras,_that.isLoading,_that.service);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<ServiceExtra> selectedExtras,  bool isLoading,  ServiceModel? service)  $default,) {final _that = this;
switch (_that) {
case _DirectServiceState():
return $default(_that.selectedExtras,_that.isLoading,_that.service);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<ServiceExtra> selectedExtras,  bool isLoading,  ServiceModel? service)?  $default,) {final _that = this;
switch (_that) {
case _DirectServiceState() when $default != null:
return $default(_that.selectedExtras,_that.isLoading,_that.service);case _:
  return null;

}
}

}

/// @nodoc


class _DirectServiceState implements DirectServiceState {
  const _DirectServiceState({final  List<ServiceExtra> selectedExtras = const [], this.isLoading = false, this.service}): _selectedExtras = selectedExtras;
  

 final  List<ServiceExtra> _selectedExtras;
@override@JsonKey() List<ServiceExtra> get selectedExtras {
  if (_selectedExtras is EqualUnmodifiableListView) return _selectedExtras;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_selectedExtras);
}

@override@JsonKey() final  bool isLoading;
@override final  ServiceModel? service;

/// Create a copy of DirectServiceState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DirectServiceStateCopyWith<_DirectServiceState> get copyWith => __$DirectServiceStateCopyWithImpl<_DirectServiceState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _DirectServiceState&&const DeepCollectionEquality().equals(other._selectedExtras, _selectedExtras)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.service, service) || other.service == service));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_selectedExtras),isLoading,service);

@override
String toString() {
  return 'DirectServiceState(selectedExtras: $selectedExtras, isLoading: $isLoading, service: $service)';
}


}

/// @nodoc
abstract mixin class _$DirectServiceStateCopyWith<$Res> implements $DirectServiceStateCopyWith<$Res> {
  factory _$DirectServiceStateCopyWith(_DirectServiceState value, $Res Function(_DirectServiceState) _then) = __$DirectServiceStateCopyWithImpl;
@override @useResult
$Res call({
 List<ServiceExtra> selectedExtras, bool isLoading, ServiceModel? service
});




}
/// @nodoc
class __$DirectServiceStateCopyWithImpl<$Res>
    implements _$DirectServiceStateCopyWith<$Res> {
  __$DirectServiceStateCopyWithImpl(this._self, this._then);

  final _DirectServiceState _self;
  final $Res Function(_DirectServiceState) _then;

/// Create a copy of DirectServiceState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? selectedExtras = null,Object? isLoading = null,Object? service = freezed,}) {
  return _then(_DirectServiceState(
selectedExtras: null == selectedExtras ? _self._selectedExtras : selectedExtras // ignore: cast_nullable_to_non_nullable
as List<ServiceExtra>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,service: freezed == service ? _self.service : service // ignore: cast_nullable_to_non_nullable
as ServiceModel?,
  ));
}


}

// dart format on
