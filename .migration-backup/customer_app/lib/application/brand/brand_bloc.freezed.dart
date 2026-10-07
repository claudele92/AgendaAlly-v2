// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'brand_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$BrandEvent {

 BuildContext get context; bool? get isRefresh; int? get shopId; RefreshController? get controller;
/// Create a copy of BrandEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BrandEventCopyWith<BrandEvent> get copyWith => _$BrandEventCopyWithImpl<BrandEvent>(this as BrandEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BrandEvent&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,controller);

@override
String toString() {
  return 'BrandEvent(context: $context, isRefresh: $isRefresh, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $BrandEventCopyWith<$Res>  {
  factory $BrandEventCopyWith(BrandEvent value, $Res Function(BrandEvent) _then) = _$BrandEventCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$BrandEventCopyWithImpl<$Res>
    implements $BrandEventCopyWith<$Res> {
  _$BrandEventCopyWithImpl(this._self, this._then);

  final BrandEvent _self;
  final $Res Function(BrandEvent) _then;

/// Create a copy of BrandEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(_self.copyWith(
context: null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}

}


/// Adds pattern-matching-related methods to [BrandEvent].
extension BrandEventPatterns on BrandEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchBrands value)?  fetchBrands,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchBrands() when fetchBrands != null:
return fetchBrands(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchBrands value)  fetchBrands,}){
final _that = this;
switch (_that) {
case FetchBrands():
return fetchBrands(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchBrands value)?  fetchBrands,}){
final _that = this;
switch (_that) {
case FetchBrands() when fetchBrands != null:
return fetchBrands(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchBrands,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchBrands() when fetchBrands != null:
return fetchBrands(_that.context,_that.isRefresh,_that.shopId,_that.controller);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)  fetchBrands,}) {final _that = this;
switch (_that) {
case FetchBrands():
return fetchBrands(_that.context,_that.isRefresh,_that.shopId,_that.controller);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchBrands,}) {final _that = this;
switch (_that) {
case FetchBrands() when fetchBrands != null:
return fetchBrands(_that.context,_that.isRefresh,_that.shopId,_that.controller);case _:
  return null;

}
}

}

/// @nodoc


class FetchBrands implements BrandEvent {
  const FetchBrands(this.context, {this.isRefresh, this.shopId, this.controller});
  

@override final  BuildContext context;
@override final  bool? isRefresh;
@override final  int? shopId;
@override final  RefreshController? controller;

/// Create a copy of BrandEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchBrandsCopyWith<FetchBrands> get copyWith => _$FetchBrandsCopyWithImpl<FetchBrands>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchBrands&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,controller);

@override
String toString() {
  return 'BrandEvent.fetchBrands(context: $context, isRefresh: $isRefresh, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchBrandsCopyWith<$Res> implements $BrandEventCopyWith<$Res> {
  factory $FetchBrandsCopyWith(FetchBrands value, $Res Function(FetchBrands) _then) = _$FetchBrandsCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$FetchBrandsCopyWithImpl<$Res>
    implements $FetchBrandsCopyWith<$Res> {
  _$FetchBrandsCopyWithImpl(this._self, this._then);

  final FetchBrands _self;
  final $Res Function(FetchBrands) _then;

/// Create a copy of BrandEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(FetchBrands(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc
mixin _$BrandState {

 List<BrandData> get brands; bool get isLoading;
/// Create a copy of BrandState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BrandStateCopyWith<BrandState> get copyWith => _$BrandStateCopyWithImpl<BrandState>(this as BrandState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BrandState&&const DeepCollectionEquality().equals(other.brands, brands)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(brands),isLoading);

@override
String toString() {
  return 'BrandState(brands: $brands, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class $BrandStateCopyWith<$Res>  {
  factory $BrandStateCopyWith(BrandState value, $Res Function(BrandState) _then) = _$BrandStateCopyWithImpl;
@useResult
$Res call({
 List<BrandData> brands, bool isLoading
});




}
/// @nodoc
class _$BrandStateCopyWithImpl<$Res>
    implements $BrandStateCopyWith<$Res> {
  _$BrandStateCopyWithImpl(this._self, this._then);

  final BrandState _self;
  final $Res Function(BrandState) _then;

/// Create a copy of BrandState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? brands = null,Object? isLoading = null,}) {
  return _then(_self.copyWith(
brands: null == brands ? _self.brands : brands // ignore: cast_nullable_to_non_nullable
as List<BrandData>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [BrandState].
extension BrandStatePatterns on BrandState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BrandState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BrandState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BrandState value)  $default,){
final _that = this;
switch (_that) {
case _BrandState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BrandState value)?  $default,){
final _that = this;
switch (_that) {
case _BrandState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<BrandData> brands,  bool isLoading)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BrandState() when $default != null:
return $default(_that.brands,_that.isLoading);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<BrandData> brands,  bool isLoading)  $default,) {final _that = this;
switch (_that) {
case _BrandState():
return $default(_that.brands,_that.isLoading);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<BrandData> brands,  bool isLoading)?  $default,) {final _that = this;
switch (_that) {
case _BrandState() when $default != null:
return $default(_that.brands,_that.isLoading);case _:
  return null;

}
}

}

/// @nodoc


class _BrandState implements BrandState {
  const _BrandState({final  List<BrandData> brands = const [], this.isLoading = true}): _brands = brands;
  

 final  List<BrandData> _brands;
@override@JsonKey() List<BrandData> get brands {
  if (_brands is EqualUnmodifiableListView) return _brands;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_brands);
}

@override@JsonKey() final  bool isLoading;

/// Create a copy of BrandState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BrandStateCopyWith<_BrandState> get copyWith => __$BrandStateCopyWithImpl<_BrandState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BrandState&&const DeepCollectionEquality().equals(other._brands, _brands)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_brands),isLoading);

@override
String toString() {
  return 'BrandState(brands: $brands, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class _$BrandStateCopyWith<$Res> implements $BrandStateCopyWith<$Res> {
  factory _$BrandStateCopyWith(_BrandState value, $Res Function(_BrandState) _then) = __$BrandStateCopyWithImpl;
@override @useResult
$Res call({
 List<BrandData> brands, bool isLoading
});




}
/// @nodoc
class __$BrandStateCopyWithImpl<$Res>
    implements _$BrandStateCopyWith<$Res> {
  __$BrandStateCopyWithImpl(this._self, this._then);

  final _BrandState _self;
  final $Res Function(_BrandState) _then;

/// Create a copy of BrandState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? brands = null,Object? isLoading = null,}) {
  return _then(_BrandState(
brands: null == brands ? _self._brands : brands // ignore: cast_nullable_to_non_nullable
as List<BrandData>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
