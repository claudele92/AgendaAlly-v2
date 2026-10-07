// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'parcels_list_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$ParcelsListEvent {

 BuildContext get context; bool? get isRefresh; RefreshController? get controller;
/// Create a copy of ParcelsListEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ParcelsListEventCopyWith<ParcelsListEvent> get copyWith => _$ParcelsListEventCopyWithImpl<ParcelsListEvent>(this as ParcelsListEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ParcelsListEvent&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ParcelsListEvent(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $ParcelsListEventCopyWith<$Res>  {
  factory $ParcelsListEventCopyWith(ParcelsListEvent value, $Res Function(ParcelsListEvent) _then) = _$ParcelsListEventCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$ParcelsListEventCopyWithImpl<$Res>
    implements $ParcelsListEventCopyWith<$Res> {
  _$ParcelsListEventCopyWithImpl(this._self, this._then);

  final ParcelsListEvent _self;
  final $Res Function(ParcelsListEvent) _then;

/// Create a copy of ParcelsListEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(_self.copyWith(
context: null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}

}


/// Adds pattern-matching-related methods to [ParcelsListEvent].
extension ParcelsListEventPatterns on ParcelsListEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchActiveParcel value)?  fetchActiveParcel,TResult Function( FetchHistoryParcel value)?  fetchHistoryParcel,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchActiveParcel() when fetchActiveParcel != null:
return fetchActiveParcel(_that);case FetchHistoryParcel() when fetchHistoryParcel != null:
return fetchHistoryParcel(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchActiveParcel value)  fetchActiveParcel,required TResult Function( FetchHistoryParcel value)  fetchHistoryParcel,}){
final _that = this;
switch (_that) {
case FetchActiveParcel():
return fetchActiveParcel(_that);case FetchHistoryParcel():
return fetchHistoryParcel(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchActiveParcel value)?  fetchActiveParcel,TResult? Function( FetchHistoryParcel value)?  fetchHistoryParcel,}){
final _that = this;
switch (_that) {
case FetchActiveParcel() when fetchActiveParcel != null:
return fetchActiveParcel(_that);case FetchHistoryParcel() when fetchHistoryParcel != null:
return fetchHistoryParcel(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchActiveParcel,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchHistoryParcel,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchActiveParcel() when fetchActiveParcel != null:
return fetchActiveParcel(_that.context,_that.isRefresh,_that.controller);case FetchHistoryParcel() when fetchHistoryParcel != null:
return fetchHistoryParcel(_that.context,_that.isRefresh,_that.controller);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchActiveParcel,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchHistoryParcel,}) {final _that = this;
switch (_that) {
case FetchActiveParcel():
return fetchActiveParcel(_that.context,_that.isRefresh,_that.controller);case FetchHistoryParcel():
return fetchHistoryParcel(_that.context,_that.isRefresh,_that.controller);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchActiveParcel,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchHistoryParcel,}) {final _that = this;
switch (_that) {
case FetchActiveParcel() when fetchActiveParcel != null:
return fetchActiveParcel(_that.context,_that.isRefresh,_that.controller);case FetchHistoryParcel() when fetchHistoryParcel != null:
return fetchHistoryParcel(_that.context,_that.isRefresh,_that.controller);case _:
  return null;

}
}

}

/// @nodoc


class FetchActiveParcel implements ParcelsListEvent {
  const FetchActiveParcel(this.context, {this.isRefresh, this.controller});
  

@override final  BuildContext context;
@override final  bool? isRefresh;
@override final  RefreshController? controller;

/// Create a copy of ParcelsListEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchActiveParcelCopyWith<FetchActiveParcel> get copyWith => _$FetchActiveParcelCopyWithImpl<FetchActiveParcel>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchActiveParcel&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ParcelsListEvent.fetchActiveParcel(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchActiveParcelCopyWith<$Res> implements $ParcelsListEventCopyWith<$Res> {
  factory $FetchActiveParcelCopyWith(FetchActiveParcel value, $Res Function(FetchActiveParcel) _then) = _$FetchActiveParcelCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchActiveParcelCopyWithImpl<$Res>
    implements $FetchActiveParcelCopyWith<$Res> {
  _$FetchActiveParcelCopyWithImpl(this._self, this._then);

  final FetchActiveParcel _self;
  final $Res Function(FetchActiveParcel) _then;

/// Create a copy of ParcelsListEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchActiveParcel(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchHistoryParcel implements ParcelsListEvent {
  const FetchHistoryParcel(this.context, {this.isRefresh, this.controller});
  

@override final  BuildContext context;
@override final  bool? isRefresh;
@override final  RefreshController? controller;

/// Create a copy of ParcelsListEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchHistoryParcelCopyWith<FetchHistoryParcel> get copyWith => _$FetchHistoryParcelCopyWithImpl<FetchHistoryParcel>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchHistoryParcel&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ParcelsListEvent.fetchHistoryParcel(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchHistoryParcelCopyWith<$Res> implements $ParcelsListEventCopyWith<$Res> {
  factory $FetchHistoryParcelCopyWith(FetchHistoryParcel value, $Res Function(FetchHistoryParcel) _then) = _$FetchHistoryParcelCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchHistoryParcelCopyWithImpl<$Res>
    implements $FetchHistoryParcelCopyWith<$Res> {
  _$FetchHistoryParcelCopyWithImpl(this._self, this._then);

  final FetchHistoryParcel _self;
  final $Res Function(FetchHistoryParcel) _then;

/// Create a copy of ParcelsListEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchHistoryParcel(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc
mixin _$ParcelsListState {

 bool get isActiveLoading; bool get isHistoryLoading; int get totalActiveCount; List<ParcelOrder> get activeOrders; List<ParcelOrder> get historyOrders;
/// Create a copy of ParcelsListState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ParcelsListStateCopyWith<ParcelsListState> get copyWith => _$ParcelsListStateCopyWithImpl<ParcelsListState>(this as ParcelsListState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ParcelsListState&&(identical(other.isActiveLoading, isActiveLoading) || other.isActiveLoading == isActiveLoading)&&(identical(other.isHistoryLoading, isHistoryLoading) || other.isHistoryLoading == isHistoryLoading)&&(identical(other.totalActiveCount, totalActiveCount) || other.totalActiveCount == totalActiveCount)&&const DeepCollectionEquality().equals(other.activeOrders, activeOrders)&&const DeepCollectionEquality().equals(other.historyOrders, historyOrders));
}


@override
int get hashCode => Object.hash(runtimeType,isActiveLoading,isHistoryLoading,totalActiveCount,const DeepCollectionEquality().hash(activeOrders),const DeepCollectionEquality().hash(historyOrders));

@override
String toString() {
  return 'ParcelsListState(isActiveLoading: $isActiveLoading, isHistoryLoading: $isHistoryLoading, totalActiveCount: $totalActiveCount, activeOrders: $activeOrders, historyOrders: $historyOrders)';
}


}

/// @nodoc
abstract mixin class $ParcelsListStateCopyWith<$Res>  {
  factory $ParcelsListStateCopyWith(ParcelsListState value, $Res Function(ParcelsListState) _then) = _$ParcelsListStateCopyWithImpl;
@useResult
$Res call({
 bool isActiveLoading, bool isHistoryLoading, int totalActiveCount, List<ParcelOrder> activeOrders, List<ParcelOrder> historyOrders
});




}
/// @nodoc
class _$ParcelsListStateCopyWithImpl<$Res>
    implements $ParcelsListStateCopyWith<$Res> {
  _$ParcelsListStateCopyWithImpl(this._self, this._then);

  final ParcelsListState _self;
  final $Res Function(ParcelsListState) _then;

/// Create a copy of ParcelsListState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isActiveLoading = null,Object? isHistoryLoading = null,Object? totalActiveCount = null,Object? activeOrders = null,Object? historyOrders = null,}) {
  return _then(_self.copyWith(
isActiveLoading: null == isActiveLoading ? _self.isActiveLoading : isActiveLoading // ignore: cast_nullable_to_non_nullable
as bool,isHistoryLoading: null == isHistoryLoading ? _self.isHistoryLoading : isHistoryLoading // ignore: cast_nullable_to_non_nullable
as bool,totalActiveCount: null == totalActiveCount ? _self.totalActiveCount : totalActiveCount // ignore: cast_nullable_to_non_nullable
as int,activeOrders: null == activeOrders ? _self.activeOrders : activeOrders // ignore: cast_nullable_to_non_nullable
as List<ParcelOrder>,historyOrders: null == historyOrders ? _self.historyOrders : historyOrders // ignore: cast_nullable_to_non_nullable
as List<ParcelOrder>,
  ));
}

}


/// Adds pattern-matching-related methods to [ParcelsListState].
extension ParcelsListStatePatterns on ParcelsListState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ParcelsListState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ParcelsListState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ParcelsListState value)  $default,){
final _that = this;
switch (_that) {
case _ParcelsListState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ParcelsListState value)?  $default,){
final _that = this;
switch (_that) {
case _ParcelsListState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isActiveLoading,  bool isHistoryLoading,  int totalActiveCount,  List<ParcelOrder> activeOrders,  List<ParcelOrder> historyOrders)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ParcelsListState() when $default != null:
return $default(_that.isActiveLoading,_that.isHistoryLoading,_that.totalActiveCount,_that.activeOrders,_that.historyOrders);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isActiveLoading,  bool isHistoryLoading,  int totalActiveCount,  List<ParcelOrder> activeOrders,  List<ParcelOrder> historyOrders)  $default,) {final _that = this;
switch (_that) {
case _ParcelsListState():
return $default(_that.isActiveLoading,_that.isHistoryLoading,_that.totalActiveCount,_that.activeOrders,_that.historyOrders);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isActiveLoading,  bool isHistoryLoading,  int totalActiveCount,  List<ParcelOrder> activeOrders,  List<ParcelOrder> historyOrders)?  $default,) {final _that = this;
switch (_that) {
case _ParcelsListState() when $default != null:
return $default(_that.isActiveLoading,_that.isHistoryLoading,_that.totalActiveCount,_that.activeOrders,_that.historyOrders);case _:
  return null;

}
}

}

/// @nodoc


class _ParcelsListState implements ParcelsListState {
  const _ParcelsListState({this.isActiveLoading = false, this.isHistoryLoading = false, this.totalActiveCount = 0, final  List<ParcelOrder> activeOrders = const [], final  List<ParcelOrder> historyOrders = const []}): _activeOrders = activeOrders,_historyOrders = historyOrders;
  

@override@JsonKey() final  bool isActiveLoading;
@override@JsonKey() final  bool isHistoryLoading;
@override@JsonKey() final  int totalActiveCount;
 final  List<ParcelOrder> _activeOrders;
@override@JsonKey() List<ParcelOrder> get activeOrders {
  if (_activeOrders is EqualUnmodifiableListView) return _activeOrders;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_activeOrders);
}

 final  List<ParcelOrder> _historyOrders;
@override@JsonKey() List<ParcelOrder> get historyOrders {
  if (_historyOrders is EqualUnmodifiableListView) return _historyOrders;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_historyOrders);
}


/// Create a copy of ParcelsListState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ParcelsListStateCopyWith<_ParcelsListState> get copyWith => __$ParcelsListStateCopyWithImpl<_ParcelsListState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _ParcelsListState&&(identical(other.isActiveLoading, isActiveLoading) || other.isActiveLoading == isActiveLoading)&&(identical(other.isHistoryLoading, isHistoryLoading) || other.isHistoryLoading == isHistoryLoading)&&(identical(other.totalActiveCount, totalActiveCount) || other.totalActiveCount == totalActiveCount)&&const DeepCollectionEquality().equals(other._activeOrders, _activeOrders)&&const DeepCollectionEquality().equals(other._historyOrders, _historyOrders));
}


@override
int get hashCode => Object.hash(runtimeType,isActiveLoading,isHistoryLoading,totalActiveCount,const DeepCollectionEquality().hash(_activeOrders),const DeepCollectionEquality().hash(_historyOrders));

@override
String toString() {
  return 'ParcelsListState(isActiveLoading: $isActiveLoading, isHistoryLoading: $isHistoryLoading, totalActiveCount: $totalActiveCount, activeOrders: $activeOrders, historyOrders: $historyOrders)';
}


}

/// @nodoc
abstract mixin class _$ParcelsListStateCopyWith<$Res> implements $ParcelsListStateCopyWith<$Res> {
  factory _$ParcelsListStateCopyWith(_ParcelsListState value, $Res Function(_ParcelsListState) _then) = __$ParcelsListStateCopyWithImpl;
@override @useResult
$Res call({
 bool isActiveLoading, bool isHistoryLoading, int totalActiveCount, List<ParcelOrder> activeOrders, List<ParcelOrder> historyOrders
});




}
/// @nodoc
class __$ParcelsListStateCopyWithImpl<$Res>
    implements _$ParcelsListStateCopyWith<$Res> {
  __$ParcelsListStateCopyWithImpl(this._self, this._then);

  final _ParcelsListState _self;
  final $Res Function(_ParcelsListState) _then;

/// Create a copy of ParcelsListState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isActiveLoading = null,Object? isHistoryLoading = null,Object? totalActiveCount = null,Object? activeOrders = null,Object? historyOrders = null,}) {
  return _then(_ParcelsListState(
isActiveLoading: null == isActiveLoading ? _self.isActiveLoading : isActiveLoading // ignore: cast_nullable_to_non_nullable
as bool,isHistoryLoading: null == isHistoryLoading ? _self.isHistoryLoading : isHistoryLoading // ignore: cast_nullable_to_non_nullable
as bool,totalActiveCount: null == totalActiveCount ? _self.totalActiveCount : totalActiveCount // ignore: cast_nullable_to_non_nullable
as int,activeOrders: null == activeOrders ? _self._activeOrders : activeOrders // ignore: cast_nullable_to_non_nullable
as List<ParcelOrder>,historyOrders: null == historyOrders ? _self._historyOrders : historyOrders // ignore: cast_nullable_to_non_nullable
as List<ParcelOrder>,
  ));
}


}

// dart format on
