// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'cart_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$CartEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'CartEvent()';
}


}

/// @nodoc
class $CartEventCopyWith<$Res>  {
$CartEventCopyWith(CartEvent _, $Res Function(CartEvent) __);
}


/// Adds pattern-matching-related methods to [CartEvent].
extension CartEventPatterns on CartEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( GetCart value)?  getCart,TResult Function( InsertCart value)?  insertCart,TResult Function( RemoveProductCart value)?  removeProductCart,TResult Function( SetNotes value)?  setNotes,TResult Function( ChangeReady value)?  changeReady,TResult Function( StartGroupOrder value)?  startGroupOrder,TResult Function( CreateLink value)?  createLink,TResult Function( DeleteCart value)?  deleteCart,TResult Function( DeleteUser value)?  deleteUser,TResult Function( CheckCoupon value)?  checkCoupon,TResult Function( UpdateState value)?  updateState,TResult Function( CalculateCart value)?  calculateCart,TResult Function( CalculateCartWithCoupon value)?  calculateCartWithCoupon,required TResult orElse(),}){
final _that = this;
switch (_that) {
case GetCart() when getCart != null:
return getCart(_that);case InsertCart() when insertCart != null:
return insertCart(_that);case RemoveProductCart() when removeProductCart != null:
return removeProductCart(_that);case SetNotes() when setNotes != null:
return setNotes(_that);case ChangeReady() when changeReady != null:
return changeReady(_that);case StartGroupOrder() when startGroupOrder != null:
return startGroupOrder(_that);case CreateLink() when createLink != null:
return createLink(_that);case DeleteCart() when deleteCart != null:
return deleteCart(_that);case DeleteUser() when deleteUser != null:
return deleteUser(_that);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that);case UpdateState() when updateState != null:
return updateState(_that);case CalculateCart() when calculateCart != null:
return calculateCart(_that);case CalculateCartWithCoupon() when calculateCartWithCoupon != null:
return calculateCartWithCoupon(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( GetCart value)  getCart,required TResult Function( InsertCart value)  insertCart,required TResult Function( RemoveProductCart value)  removeProductCart,required TResult Function( SetNotes value)  setNotes,required TResult Function( ChangeReady value)  changeReady,required TResult Function( StartGroupOrder value)  startGroupOrder,required TResult Function( CreateLink value)  createLink,required TResult Function( DeleteCart value)  deleteCart,required TResult Function( DeleteUser value)  deleteUser,required TResult Function( CheckCoupon value)  checkCoupon,required TResult Function( UpdateState value)  updateState,required TResult Function( CalculateCart value)  calculateCart,required TResult Function( CalculateCartWithCoupon value)  calculateCartWithCoupon,}){
final _that = this;
switch (_that) {
case GetCart():
return getCart(_that);case InsertCart():
return insertCart(_that);case RemoveProductCart():
return removeProductCart(_that);case SetNotes():
return setNotes(_that);case ChangeReady():
return changeReady(_that);case StartGroupOrder():
return startGroupOrder(_that);case CreateLink():
return createLink(_that);case DeleteCart():
return deleteCart(_that);case DeleteUser():
return deleteUser(_that);case CheckCoupon():
return checkCoupon(_that);case UpdateState():
return updateState(_that);case CalculateCart():
return calculateCart(_that);case CalculateCartWithCoupon():
return calculateCartWithCoupon(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( GetCart value)?  getCart,TResult? Function( InsertCart value)?  insertCart,TResult? Function( RemoveProductCart value)?  removeProductCart,TResult? Function( SetNotes value)?  setNotes,TResult? Function( ChangeReady value)?  changeReady,TResult? Function( StartGroupOrder value)?  startGroupOrder,TResult? Function( CreateLink value)?  createLink,TResult? Function( DeleteCart value)?  deleteCart,TResult? Function( DeleteUser value)?  deleteUser,TResult? Function( CheckCoupon value)?  checkCoupon,TResult? Function( UpdateState value)?  updateState,TResult? Function( CalculateCart value)?  calculateCart,TResult? Function( CalculateCartWithCoupon value)?  calculateCartWithCoupon,}){
final _that = this;
switch (_that) {
case GetCart() when getCart != null:
return getCart(_that);case InsertCart() when insertCart != null:
return insertCart(_that);case RemoveProductCart() when removeProductCart != null:
return removeProductCart(_that);case SetNotes() when setNotes != null:
return setNotes(_that);case ChangeReady() when changeReady != null:
return changeReady(_that);case StartGroupOrder() when startGroupOrder != null:
return startGroupOrder(_that);case CreateLink() when createLink != null:
return createLink(_that);case DeleteCart() when deleteCart != null:
return deleteCart(_that);case DeleteUser() when deleteUser != null:
return deleteUser(_that);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that);case UpdateState() when updateState != null:
return updateState(_that);case CalculateCart() when calculateCart != null:
return calculateCart(_that);case CalculateCartWithCoupon() when calculateCartWithCoupon != null:
return calculateCartWithCoupon(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? refresh,  String? coupon)?  getCart,TResult Function( BuildContext context,  bool? refresh,  String? coupon,  VoidCallback onSuccess)?  insertCart,TResult Function( BuildContext context,  int? cartDetailId)?  removeProductCart,TResult Function( int stockId,  String note)?  setNotes,TResult Function( BuildContext context,  String uuid,  VoidCallback onSuccess)?  changeReady,TResult Function( BuildContext context,  VoidCallback onSuccess)?  startGroupOrder,TResult Function( BuildContext context)?  createLink,TResult Function( BuildContext context)?  deleteCart,TResult Function( BuildContext context,  String uuid,  VoidCallback onSuccess)?  deleteUser,TResult Function( BuildContext context,  String coupon,  int shopId,  bool? clear)?  checkCoupon,TResult Function()?  updateState,TResult Function( BuildContext context,  int? deliveryPriceId,  int deliveryTip,  int? pointId,  int? type,  bool fullDigital)?  calculateCart,TResult Function( BuildContext context)?  calculateCartWithCoupon,required TResult orElse(),}) {final _that = this;
switch (_that) {
case GetCart() when getCart != null:
return getCart(_that.context,_that.refresh,_that.coupon);case InsertCart() when insertCart != null:
return insertCart(_that.context,_that.refresh,_that.coupon,_that.onSuccess);case RemoveProductCart() when removeProductCart != null:
return removeProductCart(_that.context,_that.cartDetailId);case SetNotes() when setNotes != null:
return setNotes(_that.stockId,_that.note);case ChangeReady() when changeReady != null:
return changeReady(_that.context,_that.uuid,_that.onSuccess);case StartGroupOrder() when startGroupOrder != null:
return startGroupOrder(_that.context,_that.onSuccess);case CreateLink() when createLink != null:
return createLink(_that.context);case DeleteCart() when deleteCart != null:
return deleteCart(_that.context);case DeleteUser() when deleteUser != null:
return deleteUser(_that.context,_that.uuid,_that.onSuccess);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that.context,_that.coupon,_that.shopId,_that.clear);case UpdateState() when updateState != null:
return updateState();case CalculateCart() when calculateCart != null:
return calculateCart(_that.context,_that.deliveryPriceId,_that.deliveryTip,_that.pointId,_that.type,_that.fullDigital);case CalculateCartWithCoupon() when calculateCartWithCoupon != null:
return calculateCartWithCoupon(_that.context);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? refresh,  String? coupon)  getCart,required TResult Function( BuildContext context,  bool? refresh,  String? coupon,  VoidCallback onSuccess)  insertCart,required TResult Function( BuildContext context,  int? cartDetailId)  removeProductCart,required TResult Function( int stockId,  String note)  setNotes,required TResult Function( BuildContext context,  String uuid,  VoidCallback onSuccess)  changeReady,required TResult Function( BuildContext context,  VoidCallback onSuccess)  startGroupOrder,required TResult Function( BuildContext context)  createLink,required TResult Function( BuildContext context)  deleteCart,required TResult Function( BuildContext context,  String uuid,  VoidCallback onSuccess)  deleteUser,required TResult Function( BuildContext context,  String coupon,  int shopId,  bool? clear)  checkCoupon,required TResult Function()  updateState,required TResult Function( BuildContext context,  int? deliveryPriceId,  int deliveryTip,  int? pointId,  int? type,  bool fullDigital)  calculateCart,required TResult Function( BuildContext context)  calculateCartWithCoupon,}) {final _that = this;
switch (_that) {
case GetCart():
return getCart(_that.context,_that.refresh,_that.coupon);case InsertCart():
return insertCart(_that.context,_that.refresh,_that.coupon,_that.onSuccess);case RemoveProductCart():
return removeProductCart(_that.context,_that.cartDetailId);case SetNotes():
return setNotes(_that.stockId,_that.note);case ChangeReady():
return changeReady(_that.context,_that.uuid,_that.onSuccess);case StartGroupOrder():
return startGroupOrder(_that.context,_that.onSuccess);case CreateLink():
return createLink(_that.context);case DeleteCart():
return deleteCart(_that.context);case DeleteUser():
return deleteUser(_that.context,_that.uuid,_that.onSuccess);case CheckCoupon():
return checkCoupon(_that.context,_that.coupon,_that.shopId,_that.clear);case UpdateState():
return updateState();case CalculateCart():
return calculateCart(_that.context,_that.deliveryPriceId,_that.deliveryTip,_that.pointId,_that.type,_that.fullDigital);case CalculateCartWithCoupon():
return calculateCartWithCoupon(_that.context);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? refresh,  String? coupon)?  getCart,TResult? Function( BuildContext context,  bool? refresh,  String? coupon,  VoidCallback onSuccess)?  insertCart,TResult? Function( BuildContext context,  int? cartDetailId)?  removeProductCart,TResult? Function( int stockId,  String note)?  setNotes,TResult? Function( BuildContext context,  String uuid,  VoidCallback onSuccess)?  changeReady,TResult? Function( BuildContext context,  VoidCallback onSuccess)?  startGroupOrder,TResult? Function( BuildContext context)?  createLink,TResult? Function( BuildContext context)?  deleteCart,TResult? Function( BuildContext context,  String uuid,  VoidCallback onSuccess)?  deleteUser,TResult? Function( BuildContext context,  String coupon,  int shopId,  bool? clear)?  checkCoupon,TResult? Function()?  updateState,TResult? Function( BuildContext context,  int? deliveryPriceId,  int deliveryTip,  int? pointId,  int? type,  bool fullDigital)?  calculateCart,TResult? Function( BuildContext context)?  calculateCartWithCoupon,}) {final _that = this;
switch (_that) {
case GetCart() when getCart != null:
return getCart(_that.context,_that.refresh,_that.coupon);case InsertCart() when insertCart != null:
return insertCart(_that.context,_that.refresh,_that.coupon,_that.onSuccess);case RemoveProductCart() when removeProductCart != null:
return removeProductCart(_that.context,_that.cartDetailId);case SetNotes() when setNotes != null:
return setNotes(_that.stockId,_that.note);case ChangeReady() when changeReady != null:
return changeReady(_that.context,_that.uuid,_that.onSuccess);case StartGroupOrder() when startGroupOrder != null:
return startGroupOrder(_that.context,_that.onSuccess);case CreateLink() when createLink != null:
return createLink(_that.context);case DeleteCart() when deleteCart != null:
return deleteCart(_that.context);case DeleteUser() when deleteUser != null:
return deleteUser(_that.context,_that.uuid,_that.onSuccess);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that.context,_that.coupon,_that.shopId,_that.clear);case UpdateState() when updateState != null:
return updateState();case CalculateCart() when calculateCart != null:
return calculateCart(_that.context,_that.deliveryPriceId,_that.deliveryTip,_that.pointId,_that.type,_that.fullDigital);case CalculateCartWithCoupon() when calculateCartWithCoupon != null:
return calculateCartWithCoupon(_that.context);case _:
  return null;

}
}

}

/// @nodoc


class GetCart implements CartEvent {
  const GetCart(this.context, {this.refresh, this.coupon});
  

 final  BuildContext context;
 final  bool? refresh;
 final  String? coupon;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GetCartCopyWith<GetCart> get copyWith => _$GetCartCopyWithImpl<GetCart>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GetCart&&(identical(other.context, context) || other.context == context)&&(identical(other.refresh, refresh) || other.refresh == refresh)&&(identical(other.coupon, coupon) || other.coupon == coupon));
}


@override
int get hashCode => Object.hash(runtimeType,context,refresh,coupon);

@override
String toString() {
  return 'CartEvent.getCart(context: $context, refresh: $refresh, coupon: $coupon)';
}


}

/// @nodoc
abstract mixin class $GetCartCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $GetCartCopyWith(GetCart value, $Res Function(GetCart) _then) = _$GetCartCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? refresh, String? coupon
});




}
/// @nodoc
class _$GetCartCopyWithImpl<$Res>
    implements $GetCartCopyWith<$Res> {
  _$GetCartCopyWithImpl(this._self, this._then);

  final GetCart _self;
  final $Res Function(GetCart) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? refresh = freezed,Object? coupon = freezed,}) {
  return _then(GetCart(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,refresh: freezed == refresh ? _self.refresh : refresh // ignore: cast_nullable_to_non_nullable
as bool?,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class InsertCart implements CartEvent {
  const InsertCart(this.context, {this.refresh, this.coupon, required this.onSuccess});
  

 final  BuildContext context;
 final  bool? refresh;
 final  String? coupon;
 final  VoidCallback onSuccess;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$InsertCartCopyWith<InsertCart> get copyWith => _$InsertCartCopyWithImpl<InsertCart>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is InsertCart&&(identical(other.context, context) || other.context == context)&&(identical(other.refresh, refresh) || other.refresh == refresh)&&(identical(other.coupon, coupon) || other.coupon == coupon)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,refresh,coupon,onSuccess);

@override
String toString() {
  return 'CartEvent.insertCart(context: $context, refresh: $refresh, coupon: $coupon, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $InsertCartCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $InsertCartCopyWith(InsertCart value, $Res Function(InsertCart) _then) = _$InsertCartCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? refresh, String? coupon, VoidCallback onSuccess
});




}
/// @nodoc
class _$InsertCartCopyWithImpl<$Res>
    implements $InsertCartCopyWith<$Res> {
  _$InsertCartCopyWithImpl(this._self, this._then);

  final InsertCart _self;
  final $Res Function(InsertCart) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? refresh = freezed,Object? coupon = freezed,Object? onSuccess = null,}) {
  return _then(InsertCart(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,refresh: freezed == refresh ? _self.refresh : refresh // ignore: cast_nullable_to_non_nullable
as bool?,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as String?,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,
  ));
}


}

/// @nodoc


class RemoveProductCart implements CartEvent {
  const RemoveProductCart(this.context, {required this.cartDetailId});
  

 final  BuildContext context;
 final  int? cartDetailId;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RemoveProductCartCopyWith<RemoveProductCart> get copyWith => _$RemoveProductCartCopyWithImpl<RemoveProductCart>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RemoveProductCart&&(identical(other.context, context) || other.context == context)&&(identical(other.cartDetailId, cartDetailId) || other.cartDetailId == cartDetailId));
}


@override
int get hashCode => Object.hash(runtimeType,context,cartDetailId);

@override
String toString() {
  return 'CartEvent.removeProductCart(context: $context, cartDetailId: $cartDetailId)';
}


}

/// @nodoc
abstract mixin class $RemoveProductCartCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $RemoveProductCartCopyWith(RemoveProductCart value, $Res Function(RemoveProductCart) _then) = _$RemoveProductCartCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? cartDetailId
});




}
/// @nodoc
class _$RemoveProductCartCopyWithImpl<$Res>
    implements $RemoveProductCartCopyWith<$Res> {
  _$RemoveProductCartCopyWithImpl(this._self, this._then);

  final RemoveProductCart _self;
  final $Res Function(RemoveProductCart) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? cartDetailId = freezed,}) {
  return _then(RemoveProductCart(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,cartDetailId: freezed == cartDetailId ? _self.cartDetailId : cartDetailId // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class SetNotes implements CartEvent {
  const SetNotes({required this.stockId, required this.note});
  

 final  int stockId;
 final  String note;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetNotesCopyWith<SetNotes> get copyWith => _$SetNotesCopyWithImpl<SetNotes>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetNotes&&(identical(other.stockId, stockId) || other.stockId == stockId)&&(identical(other.note, note) || other.note == note));
}


@override
int get hashCode => Object.hash(runtimeType,stockId,note);

@override
String toString() {
  return 'CartEvent.setNotes(stockId: $stockId, note: $note)';
}


}

/// @nodoc
abstract mixin class $SetNotesCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $SetNotesCopyWith(SetNotes value, $Res Function(SetNotes) _then) = _$SetNotesCopyWithImpl;
@useResult
$Res call({
 int stockId, String note
});




}
/// @nodoc
class _$SetNotesCopyWithImpl<$Res>
    implements $SetNotesCopyWith<$Res> {
  _$SetNotesCopyWithImpl(this._self, this._then);

  final SetNotes _self;
  final $Res Function(SetNotes) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? stockId = null,Object? note = null,}) {
  return _then(SetNotes(
stockId: null == stockId ? _self.stockId : stockId // ignore: cast_nullable_to_non_nullable
as int,note: null == note ? _self.note : note // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class ChangeReady implements CartEvent {
  const ChangeReady(this.context, {required this.uuid, required this.onSuccess});
  

 final  BuildContext context;
 final  String uuid;
 final  VoidCallback onSuccess;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChangeReadyCopyWith<ChangeReady> get copyWith => _$ChangeReadyCopyWithImpl<ChangeReady>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChangeReady&&(identical(other.context, context) || other.context == context)&&(identical(other.uuid, uuid) || other.uuid == uuid)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,uuid,onSuccess);

@override
String toString() {
  return 'CartEvent.changeReady(context: $context, uuid: $uuid, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $ChangeReadyCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $ChangeReadyCopyWith(ChangeReady value, $Res Function(ChangeReady) _then) = _$ChangeReadyCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String uuid, VoidCallback onSuccess
});




}
/// @nodoc
class _$ChangeReadyCopyWithImpl<$Res>
    implements $ChangeReadyCopyWith<$Res> {
  _$ChangeReadyCopyWithImpl(this._self, this._then);

  final ChangeReady _self;
  final $Res Function(ChangeReady) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? uuid = null,Object? onSuccess = null,}) {
  return _then(ChangeReady(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,uuid: null == uuid ? _self.uuid : uuid // ignore: cast_nullable_to_non_nullable
as String,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,
  ));
}


}

/// @nodoc


class StartGroupOrder implements CartEvent {
  const StartGroupOrder(this.context, {required this.onSuccess});
  

 final  BuildContext context;
 final  VoidCallback onSuccess;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$StartGroupOrderCopyWith<StartGroupOrder> get copyWith => _$StartGroupOrderCopyWithImpl<StartGroupOrder>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is StartGroupOrder&&(identical(other.context, context) || other.context == context)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,onSuccess);

@override
String toString() {
  return 'CartEvent.startGroupOrder(context: $context, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $StartGroupOrderCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $StartGroupOrderCopyWith(StartGroupOrder value, $Res Function(StartGroupOrder) _then) = _$StartGroupOrderCopyWithImpl;
@useResult
$Res call({
 BuildContext context, VoidCallback onSuccess
});




}
/// @nodoc
class _$StartGroupOrderCopyWithImpl<$Res>
    implements $StartGroupOrderCopyWith<$Res> {
  _$StartGroupOrderCopyWithImpl(this._self, this._then);

  final StartGroupOrder _self;
  final $Res Function(StartGroupOrder) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? onSuccess = null,}) {
  return _then(StartGroupOrder(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,
  ));
}


}

/// @nodoc


class CreateLink implements CartEvent {
  const CreateLink(this.context);
  

 final  BuildContext context;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CreateLinkCopyWith<CreateLink> get copyWith => _$CreateLinkCopyWithImpl<CreateLink>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CreateLink&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'CartEvent.createLink(context: $context)';
}


}

/// @nodoc
abstract mixin class $CreateLinkCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $CreateLinkCopyWith(CreateLink value, $Res Function(CreateLink) _then) = _$CreateLinkCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$CreateLinkCopyWithImpl<$Res>
    implements $CreateLinkCopyWith<$Res> {
  _$CreateLinkCopyWithImpl(this._self, this._then);

  final CreateLink _self;
  final $Res Function(CreateLink) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(CreateLink(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class DeleteCart implements CartEvent {
  const DeleteCart(this.context);
  

 final  BuildContext context;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeleteCartCopyWith<DeleteCart> get copyWith => _$DeleteCartCopyWithImpl<DeleteCart>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeleteCart&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'CartEvent.deleteCart(context: $context)';
}


}

/// @nodoc
abstract mixin class $DeleteCartCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $DeleteCartCopyWith(DeleteCart value, $Res Function(DeleteCart) _then) = _$DeleteCartCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$DeleteCartCopyWithImpl<$Res>
    implements $DeleteCartCopyWith<$Res> {
  _$DeleteCartCopyWithImpl(this._self, this._then);

  final DeleteCart _self;
  final $Res Function(DeleteCart) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(DeleteCart(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class DeleteUser implements CartEvent {
  const DeleteUser(this.context, {required this.uuid, required this.onSuccess});
  

 final  BuildContext context;
 final  String uuid;
 final  VoidCallback onSuccess;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeleteUserCopyWith<DeleteUser> get copyWith => _$DeleteUserCopyWithImpl<DeleteUser>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeleteUser&&(identical(other.context, context) || other.context == context)&&(identical(other.uuid, uuid) || other.uuid == uuid)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,uuid,onSuccess);

@override
String toString() {
  return 'CartEvent.deleteUser(context: $context, uuid: $uuid, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $DeleteUserCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $DeleteUserCopyWith(DeleteUser value, $Res Function(DeleteUser) _then) = _$DeleteUserCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String uuid, VoidCallback onSuccess
});




}
/// @nodoc
class _$DeleteUserCopyWithImpl<$Res>
    implements $DeleteUserCopyWith<$Res> {
  _$DeleteUserCopyWithImpl(this._self, this._then);

  final DeleteUser _self;
  final $Res Function(DeleteUser) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? uuid = null,Object? onSuccess = null,}) {
  return _then(DeleteUser(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,uuid: null == uuid ? _self.uuid : uuid // ignore: cast_nullable_to_non_nullable
as String,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,
  ));
}


}

/// @nodoc


class CheckCoupon implements CartEvent {
  const CheckCoupon(this.context, {required this.coupon, required this.shopId, this.clear});
  

 final  BuildContext context;
 final  String coupon;
 final  int shopId;
 final  bool? clear;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CheckCouponCopyWith<CheckCoupon> get copyWith => _$CheckCouponCopyWithImpl<CheckCoupon>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckCoupon&&(identical(other.context, context) || other.context == context)&&(identical(other.coupon, coupon) || other.coupon == coupon)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.clear, clear) || other.clear == clear));
}


@override
int get hashCode => Object.hash(runtimeType,context,coupon,shopId,clear);

@override
String toString() {
  return 'CartEvent.checkCoupon(context: $context, coupon: $coupon, shopId: $shopId, clear: $clear)';
}


}

/// @nodoc
abstract mixin class $CheckCouponCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $CheckCouponCopyWith(CheckCoupon value, $Res Function(CheckCoupon) _then) = _$CheckCouponCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String coupon, int shopId, bool? clear
});




}
/// @nodoc
class _$CheckCouponCopyWithImpl<$Res>
    implements $CheckCouponCopyWith<$Res> {
  _$CheckCouponCopyWithImpl(this._self, this._then);

  final CheckCoupon _self;
  final $Res Function(CheckCoupon) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? coupon = null,Object? shopId = null,Object? clear = freezed,}) {
  return _then(CheckCoupon(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,coupon: null == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as String,shopId: null == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int,clear: freezed == clear ? _self.clear : clear // ignore: cast_nullable_to_non_nullable
as bool?,
  ));
}


}

/// @nodoc


class UpdateState implements CartEvent {
  const UpdateState();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateState);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'CartEvent.updateState()';
}


}




/// @nodoc


class CalculateCart implements CartEvent {
  const CalculateCart(this.context, {required this.deliveryPriceId, required this.deliveryTip, required this.pointId, required this.type, required this.fullDigital});
  

 final  BuildContext context;
 final  int? deliveryPriceId;
 final  int deliveryTip;
 final  int? pointId;
 final  int? type;
 final  bool fullDigital;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CalculateCartCopyWith<CalculateCart> get copyWith => _$CalculateCartCopyWithImpl<CalculateCart>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CalculateCart&&(identical(other.context, context) || other.context == context)&&(identical(other.deliveryPriceId, deliveryPriceId) || other.deliveryPriceId == deliveryPriceId)&&(identical(other.deliveryTip, deliveryTip) || other.deliveryTip == deliveryTip)&&(identical(other.pointId, pointId) || other.pointId == pointId)&&(identical(other.type, type) || other.type == type)&&(identical(other.fullDigital, fullDigital) || other.fullDigital == fullDigital));
}


@override
int get hashCode => Object.hash(runtimeType,context,deliveryPriceId,deliveryTip,pointId,type,fullDigital);

@override
String toString() {
  return 'CartEvent.calculateCart(context: $context, deliveryPriceId: $deliveryPriceId, deliveryTip: $deliveryTip, pointId: $pointId, type: $type, fullDigital: $fullDigital)';
}


}

/// @nodoc
abstract mixin class $CalculateCartCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $CalculateCartCopyWith(CalculateCart value, $Res Function(CalculateCart) _then) = _$CalculateCartCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? deliveryPriceId, int deliveryTip, int? pointId, int? type, bool fullDigital
});




}
/// @nodoc
class _$CalculateCartCopyWithImpl<$Res>
    implements $CalculateCartCopyWith<$Res> {
  _$CalculateCartCopyWithImpl(this._self, this._then);

  final CalculateCart _self;
  final $Res Function(CalculateCart) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? deliveryPriceId = freezed,Object? deliveryTip = null,Object? pointId = freezed,Object? type = freezed,Object? fullDigital = null,}) {
  return _then(CalculateCart(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,deliveryPriceId: freezed == deliveryPriceId ? _self.deliveryPriceId : deliveryPriceId // ignore: cast_nullable_to_non_nullable
as int?,deliveryTip: null == deliveryTip ? _self.deliveryTip : deliveryTip // ignore: cast_nullable_to_non_nullable
as int,pointId: freezed == pointId ? _self.pointId : pointId // ignore: cast_nullable_to_non_nullable
as int?,type: freezed == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as int?,fullDigital: null == fullDigital ? _self.fullDigital : fullDigital // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc


class CalculateCartWithCoupon implements CartEvent {
  const CalculateCartWithCoupon(this.context);
  

 final  BuildContext context;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CalculateCartWithCouponCopyWith<CalculateCartWithCoupon> get copyWith => _$CalculateCartWithCouponCopyWithImpl<CalculateCartWithCoupon>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CalculateCartWithCoupon&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'CartEvent.calculateCartWithCoupon(context: $context)';
}


}

/// @nodoc
abstract mixin class $CalculateCartWithCouponCopyWith<$Res> implements $CartEventCopyWith<$Res> {
  factory $CalculateCartWithCouponCopyWith(CalculateCartWithCoupon value, $Res Function(CalculateCartWithCoupon) _then) = _$CalculateCartWithCouponCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$CalculateCartWithCouponCopyWithImpl<$Res>
    implements $CalculateCartWithCouponCopyWith<$Res> {
  _$CalculateCartWithCouponCopyWithImpl(this._self, this._then);

  final CalculateCartWithCoupon _self;
  final $Res Function(CalculateCartWithCoupon) _then;

/// Create a copy of CartEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(CalculateCartWithCoupon(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc
mixin _$CartState {

 bool get isLoading; bool get isButtonLoading; Cart? get cart; List<ProductNote>? get notes; Map<int, String> get coupons; CartCalculate? get cartCalculate; String? get groupOrderLink; ProductCalculateResponse? get productCalculate;
/// Create a copy of CartState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartStateCopyWith<CartState> get copyWith => _$CartStateCopyWithImpl<CartState>(this as CartState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.cart, cart) || other.cart == cart)&&const DeepCollectionEquality().equals(other.notes, notes)&&const DeepCollectionEquality().equals(other.coupons, coupons)&&(identical(other.cartCalculate, cartCalculate) || other.cartCalculate == cartCalculate)&&(identical(other.groupOrderLink, groupOrderLink) || other.groupOrderLink == groupOrderLink)&&(identical(other.productCalculate, productCalculate) || other.productCalculate == productCalculate));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,isButtonLoading,cart,const DeepCollectionEquality().hash(notes),const DeepCollectionEquality().hash(coupons),cartCalculate,groupOrderLink,productCalculate);

@override
String toString() {
  return 'CartState(isLoading: $isLoading, isButtonLoading: $isButtonLoading, cart: $cart, notes: $notes, coupons: $coupons, cartCalculate: $cartCalculate, groupOrderLink: $groupOrderLink, productCalculate: $productCalculate)';
}


}

/// @nodoc
abstract mixin class $CartStateCopyWith<$Res>  {
  factory $CartStateCopyWith(CartState value, $Res Function(CartState) _then) = _$CartStateCopyWithImpl;
@useResult
$Res call({
 bool isLoading, bool isButtonLoading, Cart? cart, List<ProductNote>? notes, Map<int, String> coupons, CartCalculate? cartCalculate, String? groupOrderLink, ProductCalculateResponse? productCalculate
});




}
/// @nodoc
class _$CartStateCopyWithImpl<$Res>
    implements $CartStateCopyWith<$Res> {
  _$CartStateCopyWithImpl(this._self, this._then);

  final CartState _self;
  final $Res Function(CartState) _then;

/// Create a copy of CartState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoading = null,Object? isButtonLoading = null,Object? cart = freezed,Object? notes = freezed,Object? coupons = null,Object? cartCalculate = freezed,Object? groupOrderLink = freezed,Object? productCalculate = freezed,}) {
  return _then(_self.copyWith(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,cart: freezed == cart ? _self.cart : cart // ignore: cast_nullable_to_non_nullable
as Cart?,notes: freezed == notes ? _self.notes : notes // ignore: cast_nullable_to_non_nullable
as List<ProductNote>?,coupons: null == coupons ? _self.coupons : coupons // ignore: cast_nullable_to_non_nullable
as Map<int, String>,cartCalculate: freezed == cartCalculate ? _self.cartCalculate : cartCalculate // ignore: cast_nullable_to_non_nullable
as CartCalculate?,groupOrderLink: freezed == groupOrderLink ? _self.groupOrderLink : groupOrderLink // ignore: cast_nullable_to_non_nullable
as String?,productCalculate: freezed == productCalculate ? _self.productCalculate : productCalculate // ignore: cast_nullable_to_non_nullable
as ProductCalculateResponse?,
  ));
}

}


/// Adds pattern-matching-related methods to [CartState].
extension CartStatePatterns on CartState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartState value)  $default,){
final _that = this;
switch (_that) {
case _CartState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartState value)?  $default,){
final _that = this;
switch (_that) {
case _CartState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoading,  bool isButtonLoading,  Cart? cart,  List<ProductNote>? notes,  Map<int, String> coupons,  CartCalculate? cartCalculate,  String? groupOrderLink,  ProductCalculateResponse? productCalculate)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartState() when $default != null:
return $default(_that.isLoading,_that.isButtonLoading,_that.cart,_that.notes,_that.coupons,_that.cartCalculate,_that.groupOrderLink,_that.productCalculate);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoading,  bool isButtonLoading,  Cart? cart,  List<ProductNote>? notes,  Map<int, String> coupons,  CartCalculate? cartCalculate,  String? groupOrderLink,  ProductCalculateResponse? productCalculate)  $default,) {final _that = this;
switch (_that) {
case _CartState():
return $default(_that.isLoading,_that.isButtonLoading,_that.cart,_that.notes,_that.coupons,_that.cartCalculate,_that.groupOrderLink,_that.productCalculate);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoading,  bool isButtonLoading,  Cart? cart,  List<ProductNote>? notes,  Map<int, String> coupons,  CartCalculate? cartCalculate,  String? groupOrderLink,  ProductCalculateResponse? productCalculate)?  $default,) {final _that = this;
switch (_that) {
case _CartState() when $default != null:
return $default(_that.isLoading,_that.isButtonLoading,_that.cart,_that.notes,_that.coupons,_that.cartCalculate,_that.groupOrderLink,_that.productCalculate);case _:
  return null;

}
}

}

/// @nodoc


class _CartState implements CartState {
  const _CartState({this.isLoading = false, this.isButtonLoading = true, this.cart = null, final  List<ProductNote>? notes = const [], final  Map<int, String> coupons = const {}, this.cartCalculate = null, this.groupOrderLink = null, this.productCalculate = null}): _notes = notes,_coupons = coupons;
  

@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isButtonLoading;
@override@JsonKey() final  Cart? cart;
 final  List<ProductNote>? _notes;
@override@JsonKey() List<ProductNote>? get notes {
  final value = _notes;
  if (value == null) return null;
  if (_notes is EqualUnmodifiableListView) return _notes;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  Map<int, String> _coupons;
@override@JsonKey() Map<int, String> get coupons {
  if (_coupons is EqualUnmodifiableMapView) return _coupons;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_coupons);
}

@override@JsonKey() final  CartCalculate? cartCalculate;
@override@JsonKey() final  String? groupOrderLink;
@override@JsonKey() final  ProductCalculateResponse? productCalculate;

/// Create a copy of CartState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartStateCopyWith<_CartState> get copyWith => __$CartStateCopyWithImpl<_CartState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.cart, cart) || other.cart == cart)&&const DeepCollectionEquality().equals(other._notes, _notes)&&const DeepCollectionEquality().equals(other._coupons, _coupons)&&(identical(other.cartCalculate, cartCalculate) || other.cartCalculate == cartCalculate)&&(identical(other.groupOrderLink, groupOrderLink) || other.groupOrderLink == groupOrderLink)&&(identical(other.productCalculate, productCalculate) || other.productCalculate == productCalculate));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,isButtonLoading,cart,const DeepCollectionEquality().hash(_notes),const DeepCollectionEquality().hash(_coupons),cartCalculate,groupOrderLink,productCalculate);

@override
String toString() {
  return 'CartState(isLoading: $isLoading, isButtonLoading: $isButtonLoading, cart: $cart, notes: $notes, coupons: $coupons, cartCalculate: $cartCalculate, groupOrderLink: $groupOrderLink, productCalculate: $productCalculate)';
}


}

/// @nodoc
abstract mixin class _$CartStateCopyWith<$Res> implements $CartStateCopyWith<$Res> {
  factory _$CartStateCopyWith(_CartState value, $Res Function(_CartState) _then) = __$CartStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoading, bool isButtonLoading, Cart? cart, List<ProductNote>? notes, Map<int, String> coupons, CartCalculate? cartCalculate, String? groupOrderLink, ProductCalculateResponse? productCalculate
});




}
/// @nodoc
class __$CartStateCopyWithImpl<$Res>
    implements _$CartStateCopyWith<$Res> {
  __$CartStateCopyWithImpl(this._self, this._then);

  final _CartState _self;
  final $Res Function(_CartState) _then;

/// Create a copy of CartState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoading = null,Object? isButtonLoading = null,Object? cart = freezed,Object? notes = freezed,Object? coupons = null,Object? cartCalculate = freezed,Object? groupOrderLink = freezed,Object? productCalculate = freezed,}) {
  return _then(_CartState(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,cart: freezed == cart ? _self.cart : cart // ignore: cast_nullable_to_non_nullable
as Cart?,notes: freezed == notes ? _self._notes : notes // ignore: cast_nullable_to_non_nullable
as List<ProductNote>?,coupons: null == coupons ? _self._coupons : coupons // ignore: cast_nullable_to_non_nullable
as Map<int, String>,cartCalculate: freezed == cartCalculate ? _self.cartCalculate : cartCalculate // ignore: cast_nullable_to_non_nullable
as CartCalculate?,groupOrderLink: freezed == groupOrderLink ? _self.groupOrderLink : groupOrderLink // ignore: cast_nullable_to_non_nullable
as String?,productCalculate: freezed == productCalculate ? _self.productCalculate : productCalculate // ignore: cast_nullable_to_non_nullable
as ProductCalculateResponse?,
  ));
}


}

// dart format on
