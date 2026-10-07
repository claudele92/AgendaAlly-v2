// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'checkout_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$CheckoutEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckoutEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'CheckoutEvent()';
}


}

/// @nodoc
class $CheckoutEventCopyWith<$Res>  {
$CheckoutEventCopyWith(CheckoutEvent _, $Res Function(CheckoutEvent) __);
}


/// Adds pattern-matching-related methods to [CheckoutEvent].
extension CheckoutEventPatterns on CheckoutEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( DeleteAddress value)?  deleteAddress,TResult Function( SelectAddress value)?  selectAddress,TResult Function( SetWalletPrice value)?  setWalletPrice,TResult Function( SetDeliveryTip value)?  setDeliveryTip,TResult Function( FetchUserAddress value)?  fetchUserAddress,TResult Function( ChangeStep value)?  changeStep,TResult Function( ChangeActive value)?  changeActive,TResult Function( FetchPayments value)?  fetchPayments,TResult Function( ChangePayment value)?  changePayment,TResult Function( ChangeDate value)?  changeDate,TResult Function( FetchDeliveryPoint value)?  fetchDeliveryPoint,TResult Function( FetchDeliveryPrice value)?  fetchDeliveryPrice,TResult Function( SelectPoint value)?  selectPoint,TResult Function( SetMapController value)?  setMapController,required TResult orElse(),}){
final _that = this;
switch (_that) {
case DeleteAddress() when deleteAddress != null:
return deleteAddress(_that);case SelectAddress() when selectAddress != null:
return selectAddress(_that);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that);case SetDeliveryTip() when setDeliveryTip != null:
return setDeliveryTip(_that);case FetchUserAddress() when fetchUserAddress != null:
return fetchUserAddress(_that);case ChangeStep() when changeStep != null:
return changeStep(_that);case ChangeActive() when changeActive != null:
return changeActive(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case ChangePayment() when changePayment != null:
return changePayment(_that);case ChangeDate() when changeDate != null:
return changeDate(_that);case FetchDeliveryPoint() when fetchDeliveryPoint != null:
return fetchDeliveryPoint(_that);case FetchDeliveryPrice() when fetchDeliveryPrice != null:
return fetchDeliveryPrice(_that);case SelectPoint() when selectPoint != null:
return selectPoint(_that);case SetMapController() when setMapController != null:
return setMapController(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( DeleteAddress value)  deleteAddress,required TResult Function( SelectAddress value)  selectAddress,required TResult Function( SetWalletPrice value)  setWalletPrice,required TResult Function( SetDeliveryTip value)  setDeliveryTip,required TResult Function( FetchUserAddress value)  fetchUserAddress,required TResult Function( ChangeStep value)  changeStep,required TResult Function( ChangeActive value)  changeActive,required TResult Function( FetchPayments value)  fetchPayments,required TResult Function( ChangePayment value)  changePayment,required TResult Function( ChangeDate value)  changeDate,required TResult Function( FetchDeliveryPoint value)  fetchDeliveryPoint,required TResult Function( FetchDeliveryPrice value)  fetchDeliveryPrice,required TResult Function( SelectPoint value)  selectPoint,required TResult Function( SetMapController value)  setMapController,}){
final _that = this;
switch (_that) {
case DeleteAddress():
return deleteAddress(_that);case SelectAddress():
return selectAddress(_that);case SetWalletPrice():
return setWalletPrice(_that);case SetDeliveryTip():
return setDeliveryTip(_that);case FetchUserAddress():
return fetchUserAddress(_that);case ChangeStep():
return changeStep(_that);case ChangeActive():
return changeActive(_that);case FetchPayments():
return fetchPayments(_that);case ChangePayment():
return changePayment(_that);case ChangeDate():
return changeDate(_that);case FetchDeliveryPoint():
return fetchDeliveryPoint(_that);case FetchDeliveryPrice():
return fetchDeliveryPrice(_that);case SelectPoint():
return selectPoint(_that);case SetMapController():
return setMapController(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( DeleteAddress value)?  deleteAddress,TResult? Function( SelectAddress value)?  selectAddress,TResult? Function( SetWalletPrice value)?  setWalletPrice,TResult? Function( SetDeliveryTip value)?  setDeliveryTip,TResult? Function( FetchUserAddress value)?  fetchUserAddress,TResult? Function( ChangeStep value)?  changeStep,TResult? Function( ChangeActive value)?  changeActive,TResult? Function( FetchPayments value)?  fetchPayments,TResult? Function( ChangePayment value)?  changePayment,TResult? Function( ChangeDate value)?  changeDate,TResult? Function( FetchDeliveryPoint value)?  fetchDeliveryPoint,TResult? Function( FetchDeliveryPrice value)?  fetchDeliveryPrice,TResult? Function( SelectPoint value)?  selectPoint,TResult? Function( SetMapController value)?  setMapController,}){
final _that = this;
switch (_that) {
case DeleteAddress() when deleteAddress != null:
return deleteAddress(_that);case SelectAddress() when selectAddress != null:
return selectAddress(_that);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that);case SetDeliveryTip() when setDeliveryTip != null:
return setDeliveryTip(_that);case FetchUserAddress() when fetchUserAddress != null:
return fetchUserAddress(_that);case ChangeStep() when changeStep != null:
return changeStep(_that);case ChangeActive() when changeActive != null:
return changeActive(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case ChangePayment() when changePayment != null:
return changePayment(_that);case ChangeDate() when changeDate != null:
return changeDate(_that);case FetchDeliveryPoint() when fetchDeliveryPoint != null:
return fetchDeliveryPoint(_that);case FetchDeliveryPrice() when fetchDeliveryPrice != null:
return fetchDeliveryPrice(_that);case SelectPoint() when selectPoint != null:
return selectPoint(_that);case SetMapController() when setMapController != null:
return setMapController(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  int addressId,  int index)?  deleteAddress,TResult Function( int index)?  selectAddress,TResult Function( num? price,  num? totalPrice)?  setWalletPrice,TResult Function( int tips)?  setDeliveryTip,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchUserAddress,TResult Function( BuildContext context,  int? step)?  changeStep,TResult Function( bool active)?  changeActive,TResult Function( BuildContext context)?  fetchPayments,TResult Function( PaymentData payment)?  changePayment,TResult Function( DateTime date)?  changeDate,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchDeliveryPoint,TResult Function( BuildContext context)?  fetchDeliveryPrice,TResult Function( DeliveryPoint? point)?  selectPoint,TResult Function( GoogleMapController controller)?  setMapController,required TResult orElse(),}) {final _that = this;
switch (_that) {
case DeleteAddress() when deleteAddress != null:
return deleteAddress(_that.context,_that.addressId,_that.index);case SelectAddress() when selectAddress != null:
return selectAddress(_that.index);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that.price,_that.totalPrice);case SetDeliveryTip() when setDeliveryTip != null:
return setDeliveryTip(_that.tips);case FetchUserAddress() when fetchUserAddress != null:
return fetchUserAddress(_that.context,_that.isRefresh,_that.controller);case ChangeStep() when changeStep != null:
return changeStep(_that.context,_that.step);case ChangeActive() when changeActive != null:
return changeActive(_that.active);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context);case ChangePayment() when changePayment != null:
return changePayment(_that.payment);case ChangeDate() when changeDate != null:
return changeDate(_that.date);case FetchDeliveryPoint() when fetchDeliveryPoint != null:
return fetchDeliveryPoint(_that.context,_that.isRefresh,_that.controller);case FetchDeliveryPrice() when fetchDeliveryPrice != null:
return fetchDeliveryPrice(_that.context);case SelectPoint() when selectPoint != null:
return selectPoint(_that.point);case SetMapController() when setMapController != null:
return setMapController(_that.controller);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  int addressId,  int index)  deleteAddress,required TResult Function( int index)  selectAddress,required TResult Function( num? price,  num? totalPrice)  setWalletPrice,required TResult Function( int tips)  setDeliveryTip,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchUserAddress,required TResult Function( BuildContext context,  int? step)  changeStep,required TResult Function( bool active)  changeActive,required TResult Function( BuildContext context)  fetchPayments,required TResult Function( PaymentData payment)  changePayment,required TResult Function( DateTime date)  changeDate,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchDeliveryPoint,required TResult Function( BuildContext context)  fetchDeliveryPrice,required TResult Function( DeliveryPoint? point)  selectPoint,required TResult Function( GoogleMapController controller)  setMapController,}) {final _that = this;
switch (_that) {
case DeleteAddress():
return deleteAddress(_that.context,_that.addressId,_that.index);case SelectAddress():
return selectAddress(_that.index);case SetWalletPrice():
return setWalletPrice(_that.price,_that.totalPrice);case SetDeliveryTip():
return setDeliveryTip(_that.tips);case FetchUserAddress():
return fetchUserAddress(_that.context,_that.isRefresh,_that.controller);case ChangeStep():
return changeStep(_that.context,_that.step);case ChangeActive():
return changeActive(_that.active);case FetchPayments():
return fetchPayments(_that.context);case ChangePayment():
return changePayment(_that.payment);case ChangeDate():
return changeDate(_that.date);case FetchDeliveryPoint():
return fetchDeliveryPoint(_that.context,_that.isRefresh,_that.controller);case FetchDeliveryPrice():
return fetchDeliveryPrice(_that.context);case SelectPoint():
return selectPoint(_that.point);case SetMapController():
return setMapController(_that.controller);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  int addressId,  int index)?  deleteAddress,TResult? Function( int index)?  selectAddress,TResult? Function( num? price,  num? totalPrice)?  setWalletPrice,TResult? Function( int tips)?  setDeliveryTip,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchUserAddress,TResult? Function( BuildContext context,  int? step)?  changeStep,TResult? Function( bool active)?  changeActive,TResult? Function( BuildContext context)?  fetchPayments,TResult? Function( PaymentData payment)?  changePayment,TResult? Function( DateTime date)?  changeDate,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchDeliveryPoint,TResult? Function( BuildContext context)?  fetchDeliveryPrice,TResult? Function( DeliveryPoint? point)?  selectPoint,TResult? Function( GoogleMapController controller)?  setMapController,}) {final _that = this;
switch (_that) {
case DeleteAddress() when deleteAddress != null:
return deleteAddress(_that.context,_that.addressId,_that.index);case SelectAddress() when selectAddress != null:
return selectAddress(_that.index);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that.price,_that.totalPrice);case SetDeliveryTip() when setDeliveryTip != null:
return setDeliveryTip(_that.tips);case FetchUserAddress() when fetchUserAddress != null:
return fetchUserAddress(_that.context,_that.isRefresh,_that.controller);case ChangeStep() when changeStep != null:
return changeStep(_that.context,_that.step);case ChangeActive() when changeActive != null:
return changeActive(_that.active);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context);case ChangePayment() when changePayment != null:
return changePayment(_that.payment);case ChangeDate() when changeDate != null:
return changeDate(_that.date);case FetchDeliveryPoint() when fetchDeliveryPoint != null:
return fetchDeliveryPoint(_that.context,_that.isRefresh,_that.controller);case FetchDeliveryPrice() when fetchDeliveryPrice != null:
return fetchDeliveryPrice(_that.context);case SelectPoint() when selectPoint != null:
return selectPoint(_that.point);case SetMapController() when setMapController != null:
return setMapController(_that.controller);case _:
  return null;

}
}

}

/// @nodoc


class DeleteAddress implements CheckoutEvent {
  const DeleteAddress(this.context, {required this.addressId, required this.index});
  

 final  BuildContext context;
 final  int addressId;
 final  int index;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeleteAddressCopyWith<DeleteAddress> get copyWith => _$DeleteAddressCopyWithImpl<DeleteAddress>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeleteAddress&&(identical(other.context, context) || other.context == context)&&(identical(other.addressId, addressId) || other.addressId == addressId)&&(identical(other.index, index) || other.index == index));
}


@override
int get hashCode => Object.hash(runtimeType,context,addressId,index);

@override
String toString() {
  return 'CheckoutEvent.deleteAddress(context: $context, addressId: $addressId, index: $index)';
}


}

/// @nodoc
abstract mixin class $DeleteAddressCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $DeleteAddressCopyWith(DeleteAddress value, $Res Function(DeleteAddress) _then) = _$DeleteAddressCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int addressId, int index
});




}
/// @nodoc
class _$DeleteAddressCopyWithImpl<$Res>
    implements $DeleteAddressCopyWith<$Res> {
  _$DeleteAddressCopyWithImpl(this._self, this._then);

  final DeleteAddress _self;
  final $Res Function(DeleteAddress) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? addressId = null,Object? index = null,}) {
  return _then(DeleteAddress(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,addressId: null == addressId ? _self.addressId : addressId // ignore: cast_nullable_to_non_nullable
as int,index: null == index ? _self.index : index // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class SelectAddress implements CheckoutEvent {
  const SelectAddress({required this.index});
  

 final  int index;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectAddressCopyWith<SelectAddress> get copyWith => _$SelectAddressCopyWithImpl<SelectAddress>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectAddress&&(identical(other.index, index) || other.index == index));
}


@override
int get hashCode => Object.hash(runtimeType,index);

@override
String toString() {
  return 'CheckoutEvent.selectAddress(index: $index)';
}


}

/// @nodoc
abstract mixin class $SelectAddressCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $SelectAddressCopyWith(SelectAddress value, $Res Function(SelectAddress) _then) = _$SelectAddressCopyWithImpl;
@useResult
$Res call({
 int index
});




}
/// @nodoc
class _$SelectAddressCopyWithImpl<$Res>
    implements $SelectAddressCopyWith<$Res> {
  _$SelectAddressCopyWithImpl(this._self, this._then);

  final SelectAddress _self;
  final $Res Function(SelectAddress) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? index = null,}) {
  return _then(SelectAddress(
index: null == index ? _self.index : index // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class SetWalletPrice implements CheckoutEvent {
  const SetWalletPrice({this.price, this.totalPrice});
  

 final  num? price;
 final  num? totalPrice;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetWalletPriceCopyWith<SetWalletPrice> get copyWith => _$SetWalletPriceCopyWithImpl<SetWalletPrice>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetWalletPrice&&(identical(other.price, price) || other.price == price)&&(identical(other.totalPrice, totalPrice) || other.totalPrice == totalPrice));
}


@override
int get hashCode => Object.hash(runtimeType,price,totalPrice);

@override
String toString() {
  return 'CheckoutEvent.setWalletPrice(price: $price, totalPrice: $totalPrice)';
}


}

/// @nodoc
abstract mixin class $SetWalletPriceCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $SetWalletPriceCopyWith(SetWalletPrice value, $Res Function(SetWalletPrice) _then) = _$SetWalletPriceCopyWithImpl;
@useResult
$Res call({
 num? price, num? totalPrice
});




}
/// @nodoc
class _$SetWalletPriceCopyWithImpl<$Res>
    implements $SetWalletPriceCopyWith<$Res> {
  _$SetWalletPriceCopyWithImpl(this._self, this._then);

  final SetWalletPrice _self;
  final $Res Function(SetWalletPrice) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? price = freezed,Object? totalPrice = freezed,}) {
  return _then(SetWalletPrice(
price: freezed == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as num?,totalPrice: freezed == totalPrice ? _self.totalPrice : totalPrice // ignore: cast_nullable_to_non_nullable
as num?,
  ));
}


}

/// @nodoc


class SetDeliveryTip implements CheckoutEvent {
  const SetDeliveryTip({required this.tips});
  

 final  int tips;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetDeliveryTipCopyWith<SetDeliveryTip> get copyWith => _$SetDeliveryTipCopyWithImpl<SetDeliveryTip>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetDeliveryTip&&(identical(other.tips, tips) || other.tips == tips));
}


@override
int get hashCode => Object.hash(runtimeType,tips);

@override
String toString() {
  return 'CheckoutEvent.setDeliveryTip(tips: $tips)';
}


}

/// @nodoc
abstract mixin class $SetDeliveryTipCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $SetDeliveryTipCopyWith(SetDeliveryTip value, $Res Function(SetDeliveryTip) _then) = _$SetDeliveryTipCopyWithImpl;
@useResult
$Res call({
 int tips
});




}
/// @nodoc
class _$SetDeliveryTipCopyWithImpl<$Res>
    implements $SetDeliveryTipCopyWith<$Res> {
  _$SetDeliveryTipCopyWithImpl(this._self, this._then);

  final SetDeliveryTip _self;
  final $Res Function(SetDeliveryTip) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? tips = null,}) {
  return _then(SetDeliveryTip(
tips: null == tips ? _self.tips : tips // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class FetchUserAddress implements CheckoutEvent {
  const FetchUserAddress(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchUserAddressCopyWith<FetchUserAddress> get copyWith => _$FetchUserAddressCopyWithImpl<FetchUserAddress>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchUserAddress&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'CheckoutEvent.fetchUserAddress(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchUserAddressCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $FetchUserAddressCopyWith(FetchUserAddress value, $Res Function(FetchUserAddress) _then) = _$FetchUserAddressCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchUserAddressCopyWithImpl<$Res>
    implements $FetchUserAddressCopyWith<$Res> {
  _$FetchUserAddressCopyWithImpl(this._self, this._then);

  final FetchUserAddress _self;
  final $Res Function(FetchUserAddress) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchUserAddress(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class ChangeStep implements CheckoutEvent {
  const ChangeStep(this.context, {this.step});
  

 final  BuildContext context;
 final  int? step;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChangeStepCopyWith<ChangeStep> get copyWith => _$ChangeStepCopyWithImpl<ChangeStep>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChangeStep&&(identical(other.context, context) || other.context == context)&&(identical(other.step, step) || other.step == step));
}


@override
int get hashCode => Object.hash(runtimeType,context,step);

@override
String toString() {
  return 'CheckoutEvent.changeStep(context: $context, step: $step)';
}


}

/// @nodoc
abstract mixin class $ChangeStepCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $ChangeStepCopyWith(ChangeStep value, $Res Function(ChangeStep) _then) = _$ChangeStepCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? step
});




}
/// @nodoc
class _$ChangeStepCopyWithImpl<$Res>
    implements $ChangeStepCopyWith<$Res> {
  _$ChangeStepCopyWithImpl(this._self, this._then);

  final ChangeStep _self;
  final $Res Function(ChangeStep) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? step = freezed,}) {
  return _then(ChangeStep(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,step: freezed == step ? _self.step : step // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class ChangeActive implements CheckoutEvent {
  const ChangeActive({required this.active});
  

 final  bool active;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChangeActiveCopyWith<ChangeActive> get copyWith => _$ChangeActiveCopyWithImpl<ChangeActive>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChangeActive&&(identical(other.active, active) || other.active == active));
}


@override
int get hashCode => Object.hash(runtimeType,active);

@override
String toString() {
  return 'CheckoutEvent.changeActive(active: $active)';
}


}

/// @nodoc
abstract mixin class $ChangeActiveCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $ChangeActiveCopyWith(ChangeActive value, $Res Function(ChangeActive) _then) = _$ChangeActiveCopyWithImpl;
@useResult
$Res call({
 bool active
});




}
/// @nodoc
class _$ChangeActiveCopyWithImpl<$Res>
    implements $ChangeActiveCopyWith<$Res> {
  _$ChangeActiveCopyWithImpl(this._self, this._then);

  final ChangeActive _self;
  final $Res Function(ChangeActive) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? active = null,}) {
  return _then(ChangeActive(
active: null == active ? _self.active : active // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc


class FetchPayments implements CheckoutEvent {
  const FetchPayments(this.context);
  

 final  BuildContext context;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchPaymentsCopyWith<FetchPayments> get copyWith => _$FetchPaymentsCopyWithImpl<FetchPayments>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchPayments&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'CheckoutEvent.fetchPayments(context: $context)';
}


}

/// @nodoc
abstract mixin class $FetchPaymentsCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $FetchPaymentsCopyWith(FetchPayments value, $Res Function(FetchPayments) _then) = _$FetchPaymentsCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$FetchPaymentsCopyWithImpl<$Res>
    implements $FetchPaymentsCopyWith<$Res> {
  _$FetchPaymentsCopyWithImpl(this._self, this._then);

  final FetchPayments _self;
  final $Res Function(FetchPayments) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(FetchPayments(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class ChangePayment implements CheckoutEvent {
  const ChangePayment({required this.payment});
  

 final  PaymentData payment;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChangePaymentCopyWith<ChangePayment> get copyWith => _$ChangePaymentCopyWithImpl<ChangePayment>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChangePayment&&(identical(other.payment, payment) || other.payment == payment));
}


@override
int get hashCode => Object.hash(runtimeType,payment);

@override
String toString() {
  return 'CheckoutEvent.changePayment(payment: $payment)';
}


}

/// @nodoc
abstract mixin class $ChangePaymentCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $ChangePaymentCopyWith(ChangePayment value, $Res Function(ChangePayment) _then) = _$ChangePaymentCopyWithImpl;
@useResult
$Res call({
 PaymentData payment
});




}
/// @nodoc
class _$ChangePaymentCopyWithImpl<$Res>
    implements $ChangePaymentCopyWith<$Res> {
  _$ChangePaymentCopyWithImpl(this._self, this._then);

  final ChangePayment _self;
  final $Res Function(ChangePayment) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? payment = null,}) {
  return _then(ChangePayment(
payment: null == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as PaymentData,
  ));
}


}

/// @nodoc


class ChangeDate implements CheckoutEvent {
  const ChangeDate({required this.date});
  

 final  DateTime date;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChangeDateCopyWith<ChangeDate> get copyWith => _$ChangeDateCopyWithImpl<ChangeDate>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChangeDate&&(identical(other.date, date) || other.date == date));
}


@override
int get hashCode => Object.hash(runtimeType,date);

@override
String toString() {
  return 'CheckoutEvent.changeDate(date: $date)';
}


}

/// @nodoc
abstract mixin class $ChangeDateCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $ChangeDateCopyWith(ChangeDate value, $Res Function(ChangeDate) _then) = _$ChangeDateCopyWithImpl;
@useResult
$Res call({
 DateTime date
});




}
/// @nodoc
class _$ChangeDateCopyWithImpl<$Res>
    implements $ChangeDateCopyWith<$Res> {
  _$ChangeDateCopyWithImpl(this._self, this._then);

  final ChangeDate _self;
  final $Res Function(ChangeDate) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? date = null,}) {
  return _then(ChangeDate(
date: null == date ? _self.date : date // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

/// @nodoc


class FetchDeliveryPoint implements CheckoutEvent {
  const FetchDeliveryPoint(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchDeliveryPointCopyWith<FetchDeliveryPoint> get copyWith => _$FetchDeliveryPointCopyWithImpl<FetchDeliveryPoint>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchDeliveryPoint&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'CheckoutEvent.fetchDeliveryPoint(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchDeliveryPointCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $FetchDeliveryPointCopyWith(FetchDeliveryPoint value, $Res Function(FetchDeliveryPoint) _then) = _$FetchDeliveryPointCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchDeliveryPointCopyWithImpl<$Res>
    implements $FetchDeliveryPointCopyWith<$Res> {
  _$FetchDeliveryPointCopyWithImpl(this._self, this._then);

  final FetchDeliveryPoint _self;
  final $Res Function(FetchDeliveryPoint) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchDeliveryPoint(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchDeliveryPrice implements CheckoutEvent {
  const FetchDeliveryPrice(this.context);
  

 final  BuildContext context;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchDeliveryPriceCopyWith<FetchDeliveryPrice> get copyWith => _$FetchDeliveryPriceCopyWithImpl<FetchDeliveryPrice>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchDeliveryPrice&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'CheckoutEvent.fetchDeliveryPrice(context: $context)';
}


}

/// @nodoc
abstract mixin class $FetchDeliveryPriceCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $FetchDeliveryPriceCopyWith(FetchDeliveryPrice value, $Res Function(FetchDeliveryPrice) _then) = _$FetchDeliveryPriceCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$FetchDeliveryPriceCopyWithImpl<$Res>
    implements $FetchDeliveryPriceCopyWith<$Res> {
  _$FetchDeliveryPriceCopyWithImpl(this._self, this._then);

  final FetchDeliveryPrice _self;
  final $Res Function(FetchDeliveryPrice) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(FetchDeliveryPrice(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class SelectPoint implements CheckoutEvent {
  const SelectPoint({required this.point});
  

 final  DeliveryPoint? point;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectPointCopyWith<SelectPoint> get copyWith => _$SelectPointCopyWithImpl<SelectPoint>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectPoint&&(identical(other.point, point) || other.point == point));
}


@override
int get hashCode => Object.hash(runtimeType,point);

@override
String toString() {
  return 'CheckoutEvent.selectPoint(point: $point)';
}


}

/// @nodoc
abstract mixin class $SelectPointCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $SelectPointCopyWith(SelectPoint value, $Res Function(SelectPoint) _then) = _$SelectPointCopyWithImpl;
@useResult
$Res call({
 DeliveryPoint? point
});




}
/// @nodoc
class _$SelectPointCopyWithImpl<$Res>
    implements $SelectPointCopyWith<$Res> {
  _$SelectPointCopyWithImpl(this._self, this._then);

  final SelectPoint _self;
  final $Res Function(SelectPoint) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? point = freezed,}) {
  return _then(SelectPoint(
point: freezed == point ? _self.point : point // ignore: cast_nullable_to_non_nullable
as DeliveryPoint?,
  ));
}


}

/// @nodoc


class SetMapController implements CheckoutEvent {
  const SetMapController({required this.controller});
  

 final  GoogleMapController controller;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetMapControllerCopyWith<SetMapController> get copyWith => _$SetMapControllerCopyWithImpl<SetMapController>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetMapController&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,controller);

@override
String toString() {
  return 'CheckoutEvent.setMapController(controller: $controller)';
}


}

/// @nodoc
abstract mixin class $SetMapControllerCopyWith<$Res> implements $CheckoutEventCopyWith<$Res> {
  factory $SetMapControllerCopyWith(SetMapController value, $Res Function(SetMapController) _then) = _$SetMapControllerCopyWithImpl;
@useResult
$Res call({
 GoogleMapController controller
});




}
/// @nodoc
class _$SetMapControllerCopyWithImpl<$Res>
    implements $SetMapControllerCopyWith<$Res> {
  _$SetMapControllerCopyWithImpl(this._self, this._then);

  final SetMapController _self;
  final $Res Function(SetMapController) _then;

/// Create a copy of CheckoutEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? controller = null,}) {
  return _then(SetMapController(
controller: null == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as GoogleMapController,
  ));
}


}

/// @nodoc
mixin _$CheckoutState {

 bool get isLoadingPoint; bool get isLoading; bool get isActive; int get selectAddress; int get step; int get tips; PaymentData? get selectedPayment; int get selectPointId; DateTime? get deliveryDate; List<PaymentData> get payments; List<PaymentData> get availablePayments; List<DeliveryPoint>? get deliveryPoints; List<DeliveryPoint>? get deliveryPrice; List<UserAddress> get address; num? get walletPrice; GoogleMapController? get mapController; Set<Marker>? get markers;
/// Create a copy of CheckoutState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CheckoutStateCopyWith<CheckoutState> get copyWith => _$CheckoutStateCopyWithImpl<CheckoutState>(this as CheckoutState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckoutState&&(identical(other.isLoadingPoint, isLoadingPoint) || other.isLoadingPoint == isLoadingPoint)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.selectAddress, selectAddress) || other.selectAddress == selectAddress)&&(identical(other.step, step) || other.step == step)&&(identical(other.tips, tips) || other.tips == tips)&&(identical(other.selectedPayment, selectedPayment) || other.selectedPayment == selectedPayment)&&(identical(other.selectPointId, selectPointId) || other.selectPointId == selectPointId)&&(identical(other.deliveryDate, deliveryDate) || other.deliveryDate == deliveryDate)&&const DeepCollectionEquality().equals(other.payments, payments)&&const DeepCollectionEquality().equals(other.availablePayments, availablePayments)&&const DeepCollectionEquality().equals(other.deliveryPoints, deliveryPoints)&&const DeepCollectionEquality().equals(other.deliveryPrice, deliveryPrice)&&const DeepCollectionEquality().equals(other.address, address)&&(identical(other.walletPrice, walletPrice) || other.walletPrice == walletPrice)&&(identical(other.mapController, mapController) || other.mapController == mapController)&&const DeepCollectionEquality().equals(other.markers, markers));
}


@override
int get hashCode => Object.hash(runtimeType,isLoadingPoint,isLoading,isActive,selectAddress,step,tips,selectedPayment,selectPointId,deliveryDate,const DeepCollectionEquality().hash(payments),const DeepCollectionEquality().hash(availablePayments),const DeepCollectionEquality().hash(deliveryPoints),const DeepCollectionEquality().hash(deliveryPrice),const DeepCollectionEquality().hash(address),walletPrice,mapController,const DeepCollectionEquality().hash(markers));

@override
String toString() {
  return 'CheckoutState(isLoadingPoint: $isLoadingPoint, isLoading: $isLoading, isActive: $isActive, selectAddress: $selectAddress, step: $step, tips: $tips, selectedPayment: $selectedPayment, selectPointId: $selectPointId, deliveryDate: $deliveryDate, payments: $payments, availablePayments: $availablePayments, deliveryPoints: $deliveryPoints, deliveryPrice: $deliveryPrice, address: $address, walletPrice: $walletPrice, mapController: $mapController, markers: $markers)';
}


}

/// @nodoc
abstract mixin class $CheckoutStateCopyWith<$Res>  {
  factory $CheckoutStateCopyWith(CheckoutState value, $Res Function(CheckoutState) _then) = _$CheckoutStateCopyWithImpl;
@useResult
$Res call({
 bool isLoadingPoint, bool isLoading, bool isActive, int selectAddress, int step, int tips, PaymentData? selectedPayment, int selectPointId, DateTime? deliveryDate, List<PaymentData> payments, List<PaymentData> availablePayments, List<DeliveryPoint>? deliveryPoints, List<DeliveryPoint>? deliveryPrice, List<UserAddress> address, num? walletPrice, GoogleMapController? mapController, Set<Marker>? markers
});




}
/// @nodoc
class _$CheckoutStateCopyWithImpl<$Res>
    implements $CheckoutStateCopyWith<$Res> {
  _$CheckoutStateCopyWithImpl(this._self, this._then);

  final CheckoutState _self;
  final $Res Function(CheckoutState) _then;

/// Create a copy of CheckoutState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoadingPoint = null,Object? isLoading = null,Object? isActive = null,Object? selectAddress = null,Object? step = null,Object? tips = null,Object? selectedPayment = freezed,Object? selectPointId = null,Object? deliveryDate = freezed,Object? payments = null,Object? availablePayments = null,Object? deliveryPoints = freezed,Object? deliveryPrice = freezed,Object? address = null,Object? walletPrice = freezed,Object? mapController = freezed,Object? markers = freezed,}) {
  return _then(_self.copyWith(
isLoadingPoint: null == isLoadingPoint ? _self.isLoadingPoint : isLoadingPoint // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,selectAddress: null == selectAddress ? _self.selectAddress : selectAddress // ignore: cast_nullable_to_non_nullable
as int,step: null == step ? _self.step : step // ignore: cast_nullable_to_non_nullable
as int,tips: null == tips ? _self.tips : tips // ignore: cast_nullable_to_non_nullable
as int,selectedPayment: freezed == selectedPayment ? _self.selectedPayment : selectedPayment // ignore: cast_nullable_to_non_nullable
as PaymentData?,selectPointId: null == selectPointId ? _self.selectPointId : selectPointId // ignore: cast_nullable_to_non_nullable
as int,deliveryDate: freezed == deliveryDate ? _self.deliveryDate : deliveryDate // ignore: cast_nullable_to_non_nullable
as DateTime?,payments: null == payments ? _self.payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,availablePayments: null == availablePayments ? _self.availablePayments : availablePayments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,deliveryPoints: freezed == deliveryPoints ? _self.deliveryPoints : deliveryPoints // ignore: cast_nullable_to_non_nullable
as List<DeliveryPoint>?,deliveryPrice: freezed == deliveryPrice ? _self.deliveryPrice : deliveryPrice // ignore: cast_nullable_to_non_nullable
as List<DeliveryPoint>?,address: null == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as List<UserAddress>,walletPrice: freezed == walletPrice ? _self.walletPrice : walletPrice // ignore: cast_nullable_to_non_nullable
as num?,mapController: freezed == mapController ? _self.mapController : mapController // ignore: cast_nullable_to_non_nullable
as GoogleMapController?,markers: freezed == markers ? _self.markers : markers // ignore: cast_nullable_to_non_nullable
as Set<Marker>?,
  ));
}

}


/// Adds pattern-matching-related methods to [CheckoutState].
extension CheckoutStatePatterns on CheckoutState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CheckoutState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CheckoutState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CheckoutState value)  $default,){
final _that = this;
switch (_that) {
case _CheckoutState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CheckoutState value)?  $default,){
final _that = this;
switch (_that) {
case _CheckoutState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoadingPoint,  bool isLoading,  bool isActive,  int selectAddress,  int step,  int tips,  PaymentData? selectedPayment,  int selectPointId,  DateTime? deliveryDate,  List<PaymentData> payments,  List<PaymentData> availablePayments,  List<DeliveryPoint>? deliveryPoints,  List<DeliveryPoint>? deliveryPrice,  List<UserAddress> address,  num? walletPrice,  GoogleMapController? mapController,  Set<Marker>? markers)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CheckoutState() when $default != null:
return $default(_that.isLoadingPoint,_that.isLoading,_that.isActive,_that.selectAddress,_that.step,_that.tips,_that.selectedPayment,_that.selectPointId,_that.deliveryDate,_that.payments,_that.availablePayments,_that.deliveryPoints,_that.deliveryPrice,_that.address,_that.walletPrice,_that.mapController,_that.markers);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoadingPoint,  bool isLoading,  bool isActive,  int selectAddress,  int step,  int tips,  PaymentData? selectedPayment,  int selectPointId,  DateTime? deliveryDate,  List<PaymentData> payments,  List<PaymentData> availablePayments,  List<DeliveryPoint>? deliveryPoints,  List<DeliveryPoint>? deliveryPrice,  List<UserAddress> address,  num? walletPrice,  GoogleMapController? mapController,  Set<Marker>? markers)  $default,) {final _that = this;
switch (_that) {
case _CheckoutState():
return $default(_that.isLoadingPoint,_that.isLoading,_that.isActive,_that.selectAddress,_that.step,_that.tips,_that.selectedPayment,_that.selectPointId,_that.deliveryDate,_that.payments,_that.availablePayments,_that.deliveryPoints,_that.deliveryPrice,_that.address,_that.walletPrice,_that.mapController,_that.markers);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoadingPoint,  bool isLoading,  bool isActive,  int selectAddress,  int step,  int tips,  PaymentData? selectedPayment,  int selectPointId,  DateTime? deliveryDate,  List<PaymentData> payments,  List<PaymentData> availablePayments,  List<DeliveryPoint>? deliveryPoints,  List<DeliveryPoint>? deliveryPrice,  List<UserAddress> address,  num? walletPrice,  GoogleMapController? mapController,  Set<Marker>? markers)?  $default,) {final _that = this;
switch (_that) {
case _CheckoutState() when $default != null:
return $default(_that.isLoadingPoint,_that.isLoading,_that.isActive,_that.selectAddress,_that.step,_that.tips,_that.selectedPayment,_that.selectPointId,_that.deliveryDate,_that.payments,_that.availablePayments,_that.deliveryPoints,_that.deliveryPrice,_that.address,_that.walletPrice,_that.mapController,_that.markers);case _:
  return null;

}
}

}

/// @nodoc


class _CheckoutState implements CheckoutState {
  const _CheckoutState({this.isLoadingPoint = true, this.isLoading = true, this.isActive = false, this.selectAddress = 0, this.step = 1, this.tips = 0, this.selectedPayment, this.selectPointId = -1, this.deliveryDate = null, final  List<PaymentData> payments = const [], final  List<PaymentData> availablePayments = const [], final  List<DeliveryPoint>? deliveryPoints = const [], final  List<DeliveryPoint>? deliveryPrice = const [], final  List<UserAddress> address = const [], this.walletPrice, this.mapController, final  Set<Marker>? markers}): _payments = payments,_availablePayments = availablePayments,_deliveryPoints = deliveryPoints,_deliveryPrice = deliveryPrice,_address = address,_markers = markers;
  

@override@JsonKey() final  bool isLoadingPoint;
@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isActive;
@override@JsonKey() final  int selectAddress;
@override@JsonKey() final  int step;
@override@JsonKey() final  int tips;
@override final  PaymentData? selectedPayment;
@override@JsonKey() final  int selectPointId;
@override@JsonKey() final  DateTime? deliveryDate;
 final  List<PaymentData> _payments;
@override@JsonKey() List<PaymentData> get payments {
  if (_payments is EqualUnmodifiableListView) return _payments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_payments);
}

 final  List<PaymentData> _availablePayments;
@override@JsonKey() List<PaymentData> get availablePayments {
  if (_availablePayments is EqualUnmodifiableListView) return _availablePayments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_availablePayments);
}

 final  List<DeliveryPoint>? _deliveryPoints;
@override@JsonKey() List<DeliveryPoint>? get deliveryPoints {
  final value = _deliveryPoints;
  if (value == null) return null;
  if (_deliveryPoints is EqualUnmodifiableListView) return _deliveryPoints;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<DeliveryPoint>? _deliveryPrice;
@override@JsonKey() List<DeliveryPoint>? get deliveryPrice {
  final value = _deliveryPrice;
  if (value == null) return null;
  if (_deliveryPrice is EqualUnmodifiableListView) return _deliveryPrice;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<UserAddress> _address;
@override@JsonKey() List<UserAddress> get address {
  if (_address is EqualUnmodifiableListView) return _address;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_address);
}

@override final  num? walletPrice;
@override final  GoogleMapController? mapController;
 final  Set<Marker>? _markers;
@override Set<Marker>? get markers {
  final value = _markers;
  if (value == null) return null;
  if (_markers is EqualUnmodifiableSetView) return _markers;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableSetView(value);
}


/// Create a copy of CheckoutState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CheckoutStateCopyWith<_CheckoutState> get copyWith => __$CheckoutStateCopyWithImpl<_CheckoutState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CheckoutState&&(identical(other.isLoadingPoint, isLoadingPoint) || other.isLoadingPoint == isLoadingPoint)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isActive, isActive) || other.isActive == isActive)&&(identical(other.selectAddress, selectAddress) || other.selectAddress == selectAddress)&&(identical(other.step, step) || other.step == step)&&(identical(other.tips, tips) || other.tips == tips)&&(identical(other.selectedPayment, selectedPayment) || other.selectedPayment == selectedPayment)&&(identical(other.selectPointId, selectPointId) || other.selectPointId == selectPointId)&&(identical(other.deliveryDate, deliveryDate) || other.deliveryDate == deliveryDate)&&const DeepCollectionEquality().equals(other._payments, _payments)&&const DeepCollectionEquality().equals(other._availablePayments, _availablePayments)&&const DeepCollectionEquality().equals(other._deliveryPoints, _deliveryPoints)&&const DeepCollectionEquality().equals(other._deliveryPrice, _deliveryPrice)&&const DeepCollectionEquality().equals(other._address, _address)&&(identical(other.walletPrice, walletPrice) || other.walletPrice == walletPrice)&&(identical(other.mapController, mapController) || other.mapController == mapController)&&const DeepCollectionEquality().equals(other._markers, _markers));
}


@override
int get hashCode => Object.hash(runtimeType,isLoadingPoint,isLoading,isActive,selectAddress,step,tips,selectedPayment,selectPointId,deliveryDate,const DeepCollectionEquality().hash(_payments),const DeepCollectionEquality().hash(_availablePayments),const DeepCollectionEquality().hash(_deliveryPoints),const DeepCollectionEquality().hash(_deliveryPrice),const DeepCollectionEquality().hash(_address),walletPrice,mapController,const DeepCollectionEquality().hash(_markers));

@override
String toString() {
  return 'CheckoutState(isLoadingPoint: $isLoadingPoint, isLoading: $isLoading, isActive: $isActive, selectAddress: $selectAddress, step: $step, tips: $tips, selectedPayment: $selectedPayment, selectPointId: $selectPointId, deliveryDate: $deliveryDate, payments: $payments, availablePayments: $availablePayments, deliveryPoints: $deliveryPoints, deliveryPrice: $deliveryPrice, address: $address, walletPrice: $walletPrice, mapController: $mapController, markers: $markers)';
}


}

/// @nodoc
abstract mixin class _$CheckoutStateCopyWith<$Res> implements $CheckoutStateCopyWith<$Res> {
  factory _$CheckoutStateCopyWith(_CheckoutState value, $Res Function(_CheckoutState) _then) = __$CheckoutStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoadingPoint, bool isLoading, bool isActive, int selectAddress, int step, int tips, PaymentData? selectedPayment, int selectPointId, DateTime? deliveryDate, List<PaymentData> payments, List<PaymentData> availablePayments, List<DeliveryPoint>? deliveryPoints, List<DeliveryPoint>? deliveryPrice, List<UserAddress> address, num? walletPrice, GoogleMapController? mapController, Set<Marker>? markers
});




}
/// @nodoc
class __$CheckoutStateCopyWithImpl<$Res>
    implements _$CheckoutStateCopyWith<$Res> {
  __$CheckoutStateCopyWithImpl(this._self, this._then);

  final _CheckoutState _self;
  final $Res Function(_CheckoutState) _then;

/// Create a copy of CheckoutState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoadingPoint = null,Object? isLoading = null,Object? isActive = null,Object? selectAddress = null,Object? step = null,Object? tips = null,Object? selectedPayment = freezed,Object? selectPointId = null,Object? deliveryDate = freezed,Object? payments = null,Object? availablePayments = null,Object? deliveryPoints = freezed,Object? deliveryPrice = freezed,Object? address = null,Object? walletPrice = freezed,Object? mapController = freezed,Object? markers = freezed,}) {
  return _then(_CheckoutState(
isLoadingPoint: null == isLoadingPoint ? _self.isLoadingPoint : isLoadingPoint // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isActive: null == isActive ? _self.isActive : isActive // ignore: cast_nullable_to_non_nullable
as bool,selectAddress: null == selectAddress ? _self.selectAddress : selectAddress // ignore: cast_nullable_to_non_nullable
as int,step: null == step ? _self.step : step // ignore: cast_nullable_to_non_nullable
as int,tips: null == tips ? _self.tips : tips // ignore: cast_nullable_to_non_nullable
as int,selectedPayment: freezed == selectedPayment ? _self.selectedPayment : selectedPayment // ignore: cast_nullable_to_non_nullable
as PaymentData?,selectPointId: null == selectPointId ? _self.selectPointId : selectPointId // ignore: cast_nullable_to_non_nullable
as int,deliveryDate: freezed == deliveryDate ? _self.deliveryDate : deliveryDate // ignore: cast_nullable_to_non_nullable
as DateTime?,payments: null == payments ? _self._payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,availablePayments: null == availablePayments ? _self._availablePayments : availablePayments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,deliveryPoints: freezed == deliveryPoints ? _self._deliveryPoints : deliveryPoints // ignore: cast_nullable_to_non_nullable
as List<DeliveryPoint>?,deliveryPrice: freezed == deliveryPrice ? _self._deliveryPrice : deliveryPrice // ignore: cast_nullable_to_non_nullable
as List<DeliveryPoint>?,address: null == address ? _self._address : address // ignore: cast_nullable_to_non_nullable
as List<UserAddress>,walletPrice: freezed == walletPrice ? _self.walletPrice : walletPrice // ignore: cast_nullable_to_non_nullable
as num?,mapController: freezed == mapController ? _self.mapController : mapController // ignore: cast_nullable_to_non_nullable
as GoogleMapController?,markers: freezed == markers ? _self._markers : markers // ignore: cast_nullable_to_non_nullable
as Set<Marker>?,
  ));
}


}

// dart format on
