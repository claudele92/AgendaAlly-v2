// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'maksekeskus_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$MaksekeskusEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MaksekeskusEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'MaksekeskusEvent()';
}


}

/// @nodoc
class $MaksekeskusEventCopyWith<$Res>  {
$MaksekeskusEventCopyWith(MaksekeskusEvent _, $Res Function(MaksekeskusEvent) __);
}


/// Adds pattern-matching-related methods to [MaksekeskusEvent].
extension MaksekeskusEventPatterns on MaksekeskusEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( SelectMethod value)?  selectMethod,TResult Function( FetchMaksekeskus value)?  fetchMaksekeskus,required TResult orElse(),}){
final _that = this;
switch (_that) {
case SelectMethod() when selectMethod != null:
return selectMethod(_that);case FetchMaksekeskus() when fetchMaksekeskus != null:
return fetchMaksekeskus(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( SelectMethod value)  selectMethod,required TResult Function( FetchMaksekeskus value)  fetchMaksekeskus,}){
final _that = this;
switch (_that) {
case SelectMethod():
return selectMethod(_that);case FetchMaksekeskus():
return fetchMaksekeskus(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( SelectMethod value)?  selectMethod,TResult? Function( FetchMaksekeskus value)?  fetchMaksekeskus,}){
final _that = this;
switch (_that) {
case SelectMethod() when selectMethod != null:
return selectMethod(_that);case FetchMaksekeskus() when fetchMaksekeskus != null:
return fetchMaksekeskus(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( String link)?  selectMethod,TResult Function( BuildContext context,  CreateOrderModel? order,  bool? parcel,  bool? wallet,  int? parcelId,  num? price,  VoidCallback? onSuccess)?  fetchMaksekeskus,required TResult orElse(),}) {final _that = this;
switch (_that) {
case SelectMethod() when selectMethod != null:
return selectMethod(_that.link);case FetchMaksekeskus() when fetchMaksekeskus != null:
return fetchMaksekeskus(_that.context,_that.order,_that.parcel,_that.wallet,_that.parcelId,_that.price,_that.onSuccess);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( String link)  selectMethod,required TResult Function( BuildContext context,  CreateOrderModel? order,  bool? parcel,  bool? wallet,  int? parcelId,  num? price,  VoidCallback? onSuccess)  fetchMaksekeskus,}) {final _that = this;
switch (_that) {
case SelectMethod():
return selectMethod(_that.link);case FetchMaksekeskus():
return fetchMaksekeskus(_that.context,_that.order,_that.parcel,_that.wallet,_that.parcelId,_that.price,_that.onSuccess);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( String link)?  selectMethod,TResult? Function( BuildContext context,  CreateOrderModel? order,  bool? parcel,  bool? wallet,  int? parcelId,  num? price,  VoidCallback? onSuccess)?  fetchMaksekeskus,}) {final _that = this;
switch (_that) {
case SelectMethod() when selectMethod != null:
return selectMethod(_that.link);case FetchMaksekeskus() when fetchMaksekeskus != null:
return fetchMaksekeskus(_that.context,_that.order,_that.parcel,_that.wallet,_that.parcelId,_that.price,_that.onSuccess);case _:
  return null;

}
}

}

/// @nodoc


class SelectMethod implements MaksekeskusEvent {
  const SelectMethod({required this.link});
  

 final  String link;

/// Create a copy of MaksekeskusEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectMethodCopyWith<SelectMethod> get copyWith => _$SelectMethodCopyWithImpl<SelectMethod>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectMethod&&(identical(other.link, link) || other.link == link));
}


@override
int get hashCode => Object.hash(runtimeType,link);

@override
String toString() {
  return 'MaksekeskusEvent.selectMethod(link: $link)';
}


}

/// @nodoc
abstract mixin class $SelectMethodCopyWith<$Res> implements $MaksekeskusEventCopyWith<$Res> {
  factory $SelectMethodCopyWith(SelectMethod value, $Res Function(SelectMethod) _then) = _$SelectMethodCopyWithImpl;
@useResult
$Res call({
 String link
});




}
/// @nodoc
class _$SelectMethodCopyWithImpl<$Res>
    implements $SelectMethodCopyWith<$Res> {
  _$SelectMethodCopyWithImpl(this._self, this._then);

  final SelectMethod _self;
  final $Res Function(SelectMethod) _then;

/// Create a copy of MaksekeskusEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? link = null,}) {
  return _then(SelectMethod(
link: null == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class FetchMaksekeskus implements MaksekeskusEvent {
  const FetchMaksekeskus(this.context, {this.order, this.parcel, this.wallet, this.parcelId, this.price, this.onSuccess});
  

 final  BuildContext context;
 final  CreateOrderModel? order;
 final  bool? parcel;
 final  bool? wallet;
 final  int? parcelId;
 final  num? price;
 final  VoidCallback? onSuccess;

/// Create a copy of MaksekeskusEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMaksekeskusCopyWith<FetchMaksekeskus> get copyWith => _$FetchMaksekeskusCopyWithImpl<FetchMaksekeskus>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMaksekeskus&&(identical(other.context, context) || other.context == context)&&(identical(other.order, order) || other.order == order)&&(identical(other.parcel, parcel) || other.parcel == parcel)&&(identical(other.wallet, wallet) || other.wallet == wallet)&&(identical(other.parcelId, parcelId) || other.parcelId == parcelId)&&(identical(other.price, price) || other.price == price)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,order,parcel,wallet,parcelId,price,onSuccess);

@override
String toString() {
  return 'MaksekeskusEvent.fetchMaksekeskus(context: $context, order: $order, parcel: $parcel, wallet: $wallet, parcelId: $parcelId, price: $price, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $FetchMaksekeskusCopyWith<$Res> implements $MaksekeskusEventCopyWith<$Res> {
  factory $FetchMaksekeskusCopyWith(FetchMaksekeskus value, $Res Function(FetchMaksekeskus) _then) = _$FetchMaksekeskusCopyWithImpl;
@useResult
$Res call({
 BuildContext context, CreateOrderModel? order, bool? parcel, bool? wallet, int? parcelId, num? price, VoidCallback? onSuccess
});




}
/// @nodoc
class _$FetchMaksekeskusCopyWithImpl<$Res>
    implements $FetchMaksekeskusCopyWith<$Res> {
  _$FetchMaksekeskusCopyWithImpl(this._self, this._then);

  final FetchMaksekeskus _self;
  final $Res Function(FetchMaksekeskus) _then;

/// Create a copy of MaksekeskusEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? order = freezed,Object? parcel = freezed,Object? wallet = freezed,Object? parcelId = freezed,Object? price = freezed,Object? onSuccess = freezed,}) {
  return _then(FetchMaksekeskus(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as CreateOrderModel?,parcel: freezed == parcel ? _self.parcel : parcel // ignore: cast_nullable_to_non_nullable
as bool?,wallet: freezed == wallet ? _self.wallet : wallet // ignore: cast_nullable_to_non_nullable
as bool?,parcelId: freezed == parcelId ? _self.parcelId : parcelId // ignore: cast_nullable_to_non_nullable
as int?,price: freezed == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as num?,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc
mixin _$MaksekeskusState {

 bool get isLoading; String? get selectMethodLink; MaksekeskusData? get maksekeskus;
/// Create a copy of MaksekeskusState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MaksekeskusStateCopyWith<MaksekeskusState> get copyWith => _$MaksekeskusStateCopyWithImpl<MaksekeskusState>(this as MaksekeskusState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MaksekeskusState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.selectMethodLink, selectMethodLink) || other.selectMethodLink == selectMethodLink)&&(identical(other.maksekeskus, maksekeskus) || other.maksekeskus == maksekeskus));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,selectMethodLink,maksekeskus);

@override
String toString() {
  return 'MaksekeskusState(isLoading: $isLoading, selectMethodLink: $selectMethodLink, maksekeskus: $maksekeskus)';
}


}

/// @nodoc
abstract mixin class $MaksekeskusStateCopyWith<$Res>  {
  factory $MaksekeskusStateCopyWith(MaksekeskusState value, $Res Function(MaksekeskusState) _then) = _$MaksekeskusStateCopyWithImpl;
@useResult
$Res call({
 bool isLoading, String? selectMethodLink, MaksekeskusData? maksekeskus
});




}
/// @nodoc
class _$MaksekeskusStateCopyWithImpl<$Res>
    implements $MaksekeskusStateCopyWith<$Res> {
  _$MaksekeskusStateCopyWithImpl(this._self, this._then);

  final MaksekeskusState _self;
  final $Res Function(MaksekeskusState) _then;

/// Create a copy of MaksekeskusState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoading = null,Object? selectMethodLink = freezed,Object? maksekeskus = freezed,}) {
  return _then(_self.copyWith(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,selectMethodLink: freezed == selectMethodLink ? _self.selectMethodLink : selectMethodLink // ignore: cast_nullable_to_non_nullable
as String?,maksekeskus: freezed == maksekeskus ? _self.maksekeskus : maksekeskus // ignore: cast_nullable_to_non_nullable
as MaksekeskusData?,
  ));
}

}


/// Adds pattern-matching-related methods to [MaksekeskusState].
extension MaksekeskusStatePatterns on MaksekeskusState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MaksekeskusState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MaksekeskusState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MaksekeskusState value)  $default,){
final _that = this;
switch (_that) {
case _MaksekeskusState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MaksekeskusState value)?  $default,){
final _that = this;
switch (_that) {
case _MaksekeskusState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoading,  String? selectMethodLink,  MaksekeskusData? maksekeskus)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MaksekeskusState() when $default != null:
return $default(_that.isLoading,_that.selectMethodLink,_that.maksekeskus);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoading,  String? selectMethodLink,  MaksekeskusData? maksekeskus)  $default,) {final _that = this;
switch (_that) {
case _MaksekeskusState():
return $default(_that.isLoading,_that.selectMethodLink,_that.maksekeskus);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoading,  String? selectMethodLink,  MaksekeskusData? maksekeskus)?  $default,) {final _that = this;
switch (_that) {
case _MaksekeskusState() when $default != null:
return $default(_that.isLoading,_that.selectMethodLink,_that.maksekeskus);case _:
  return null;

}
}

}

/// @nodoc


class _MaksekeskusState implements MaksekeskusState {
  const _MaksekeskusState({this.isLoading = false, this.selectMethodLink = null, this.maksekeskus = null});
  

@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  String? selectMethodLink;
@override@JsonKey() final  MaksekeskusData? maksekeskus;

/// Create a copy of MaksekeskusState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MaksekeskusStateCopyWith<_MaksekeskusState> get copyWith => __$MaksekeskusStateCopyWithImpl<_MaksekeskusState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _MaksekeskusState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.selectMethodLink, selectMethodLink) || other.selectMethodLink == selectMethodLink)&&(identical(other.maksekeskus, maksekeskus) || other.maksekeskus == maksekeskus));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,selectMethodLink,maksekeskus);

@override
String toString() {
  return 'MaksekeskusState(isLoading: $isLoading, selectMethodLink: $selectMethodLink, maksekeskus: $maksekeskus)';
}


}

/// @nodoc
abstract mixin class _$MaksekeskusStateCopyWith<$Res> implements $MaksekeskusStateCopyWith<$Res> {
  factory _$MaksekeskusStateCopyWith(_MaksekeskusState value, $Res Function(_MaksekeskusState) _then) = __$MaksekeskusStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoading, String? selectMethodLink, MaksekeskusData? maksekeskus
});




}
/// @nodoc
class __$MaksekeskusStateCopyWithImpl<$Res>
    implements _$MaksekeskusStateCopyWith<$Res> {
  __$MaksekeskusStateCopyWithImpl(this._self, this._then);

  final _MaksekeskusState _self;
  final $Res Function(_MaksekeskusState) _then;

/// Create a copy of MaksekeskusState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoading = null,Object? selectMethodLink = freezed,Object? maksekeskus = freezed,}) {
  return _then(_MaksekeskusState(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,selectMethodLink: freezed == selectMethodLink ? _self.selectMethodLink : selectMethodLink // ignore: cast_nullable_to_non_nullable
as String?,maksekeskus: freezed == maksekeskus ? _self.maksekeskus : maksekeskus // ignore: cast_nullable_to_non_nullable
as MaksekeskusData?,
  ));
}


}

// dart format on
