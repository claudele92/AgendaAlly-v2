// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'order_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$OrderEvent {

 BuildContext get context;
/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderEventCopyWith<OrderEvent> get copyWith => _$OrderEventCopyWithImpl<OrderEvent>(this as OrderEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderEvent&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'OrderEvent(context: $context)';
}


}

/// @nodoc
abstract mixin class $OrderEventCopyWith<$Res>  {
  factory $OrderEventCopyWith(OrderEvent value, $Res Function(OrderEvent) _then) = _$OrderEventCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$OrderEventCopyWithImpl<$Res>
    implements $OrderEventCopyWith<$Res> {
  _$OrderEventCopyWithImpl(this._self, this._then);

  final OrderEvent _self;
  final $Res Function(OrderEvent) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? context = null,}) {
  return _then(_self.copyWith(
context: null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderEvent].
extension OrderEventPatterns on OrderEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchActiveOrders value)?  fetchActiveOrders,TResult Function( FetchRefundOrders value)?  fetchRefundOrders,TResult Function( FetchOrderById value)?  fetchOrderById,TResult Function( FetchRefundOrderById value)?  fetchRefundOrderById,TResult Function( CreateOrder value)?  createOrder,TResult Function( CancelOrder value)?  cancelOrder,TResult Function( RefundOrder value)?  refundOrder,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchActiveOrders() when fetchActiveOrders != null:
return fetchActiveOrders(_that);case FetchRefundOrders() when fetchRefundOrders != null:
return fetchRefundOrders(_that);case FetchOrderById() when fetchOrderById != null:
return fetchOrderById(_that);case FetchRefundOrderById() when fetchRefundOrderById != null:
return fetchRefundOrderById(_that);case CreateOrder() when createOrder != null:
return createOrder(_that);case CancelOrder() when cancelOrder != null:
return cancelOrder(_that);case RefundOrder() when refundOrder != null:
return refundOrder(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchActiveOrders value)  fetchActiveOrders,required TResult Function( FetchRefundOrders value)  fetchRefundOrders,required TResult Function( FetchOrderById value)  fetchOrderById,required TResult Function( FetchRefundOrderById value)  fetchRefundOrderById,required TResult Function( CreateOrder value)  createOrder,required TResult Function( CancelOrder value)  cancelOrder,required TResult Function( RefundOrder value)  refundOrder,}){
final _that = this;
switch (_that) {
case FetchActiveOrders():
return fetchActiveOrders(_that);case FetchRefundOrders():
return fetchRefundOrders(_that);case FetchOrderById():
return fetchOrderById(_that);case FetchRefundOrderById():
return fetchRefundOrderById(_that);case CreateOrder():
return createOrder(_that);case CancelOrder():
return cancelOrder(_that);case RefundOrder():
return refundOrder(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchActiveOrders value)?  fetchActiveOrders,TResult? Function( FetchRefundOrders value)?  fetchRefundOrders,TResult? Function( FetchOrderById value)?  fetchOrderById,TResult? Function( FetchRefundOrderById value)?  fetchRefundOrderById,TResult? Function( CreateOrder value)?  createOrder,TResult? Function( CancelOrder value)?  cancelOrder,TResult? Function( RefundOrder value)?  refundOrder,}){
final _that = this;
switch (_that) {
case FetchActiveOrders() when fetchActiveOrders != null:
return fetchActiveOrders(_that);case FetchRefundOrders() when fetchRefundOrders != null:
return fetchRefundOrders(_that);case FetchOrderById() when fetchOrderById != null:
return fetchOrderById(_that);case FetchRefundOrderById() when fetchRefundOrderById != null:
return fetchRefundOrderById(_that);case CreateOrder() when createOrder != null:
return createOrder(_that);case CancelOrder() when cancelOrder != null:
return cancelOrder(_that);case RefundOrder() when refundOrder != null:
return refundOrder(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchActiveOrders,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchRefundOrders,TResult Function( BuildContext context,  int id,  OrderModel? order)?  fetchOrderById,TResult Function( BuildContext context,  int id,  RefundModel? refund)?  fetchRefundOrderById,TResult Function( BuildContext context,  CreateOrderModel order,  PaymentData payment,  num totalPrice,  VoidCallback onSuccess)?  createOrder,TResult Function( BuildContext context,  int id,  VoidCallback onSuccess)?  cancelOrder,TResult Function( BuildContext context,  int id,  String reason,  VoidCallback onSuccess)?  refundOrder,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchActiveOrders() when fetchActiveOrders != null:
return fetchActiveOrders(_that.context,_that.isRefresh,_that.controller);case FetchRefundOrders() when fetchRefundOrders != null:
return fetchRefundOrders(_that.context,_that.isRefresh,_that.controller);case FetchOrderById() when fetchOrderById != null:
return fetchOrderById(_that.context,_that.id,_that.order);case FetchRefundOrderById() when fetchRefundOrderById != null:
return fetchRefundOrderById(_that.context,_that.id,_that.refund);case CreateOrder() when createOrder != null:
return createOrder(_that.context,_that.order,_that.payment,_that.totalPrice,_that.onSuccess);case CancelOrder() when cancelOrder != null:
return cancelOrder(_that.context,_that.id,_that.onSuccess);case RefundOrder() when refundOrder != null:
return refundOrder(_that.context,_that.id,_that.reason,_that.onSuccess);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchActiveOrders,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchRefundOrders,required TResult Function( BuildContext context,  int id,  OrderModel? order)  fetchOrderById,required TResult Function( BuildContext context,  int id,  RefundModel? refund)  fetchRefundOrderById,required TResult Function( BuildContext context,  CreateOrderModel order,  PaymentData payment,  num totalPrice,  VoidCallback onSuccess)  createOrder,required TResult Function( BuildContext context,  int id,  VoidCallback onSuccess)  cancelOrder,required TResult Function( BuildContext context,  int id,  String reason,  VoidCallback onSuccess)  refundOrder,}) {final _that = this;
switch (_that) {
case FetchActiveOrders():
return fetchActiveOrders(_that.context,_that.isRefresh,_that.controller);case FetchRefundOrders():
return fetchRefundOrders(_that.context,_that.isRefresh,_that.controller);case FetchOrderById():
return fetchOrderById(_that.context,_that.id,_that.order);case FetchRefundOrderById():
return fetchRefundOrderById(_that.context,_that.id,_that.refund);case CreateOrder():
return createOrder(_that.context,_that.order,_that.payment,_that.totalPrice,_that.onSuccess);case CancelOrder():
return cancelOrder(_that.context,_that.id,_that.onSuccess);case RefundOrder():
return refundOrder(_that.context,_that.id,_that.reason,_that.onSuccess);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchActiveOrders,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchRefundOrders,TResult? Function( BuildContext context,  int id,  OrderModel? order)?  fetchOrderById,TResult? Function( BuildContext context,  int id,  RefundModel? refund)?  fetchRefundOrderById,TResult? Function( BuildContext context,  CreateOrderModel order,  PaymentData payment,  num totalPrice,  VoidCallback onSuccess)?  createOrder,TResult? Function( BuildContext context,  int id,  VoidCallback onSuccess)?  cancelOrder,TResult? Function( BuildContext context,  int id,  String reason,  VoidCallback onSuccess)?  refundOrder,}) {final _that = this;
switch (_that) {
case FetchActiveOrders() when fetchActiveOrders != null:
return fetchActiveOrders(_that.context,_that.isRefresh,_that.controller);case FetchRefundOrders() when fetchRefundOrders != null:
return fetchRefundOrders(_that.context,_that.isRefresh,_that.controller);case FetchOrderById() when fetchOrderById != null:
return fetchOrderById(_that.context,_that.id,_that.order);case FetchRefundOrderById() when fetchRefundOrderById != null:
return fetchRefundOrderById(_that.context,_that.id,_that.refund);case CreateOrder() when createOrder != null:
return createOrder(_that.context,_that.order,_that.payment,_that.totalPrice,_that.onSuccess);case CancelOrder() when cancelOrder != null:
return cancelOrder(_that.context,_that.id,_that.onSuccess);case RefundOrder() when refundOrder != null:
return refundOrder(_that.context,_that.id,_that.reason,_that.onSuccess);case _:
  return null;

}
}

}

/// @nodoc


class FetchActiveOrders implements OrderEvent {
  const FetchActiveOrders(this.context, {this.isRefresh, this.controller});
  

@override final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchActiveOrdersCopyWith<FetchActiveOrders> get copyWith => _$FetchActiveOrdersCopyWithImpl<FetchActiveOrders>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchActiveOrders&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'OrderEvent.fetchActiveOrders(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchActiveOrdersCopyWith<$Res> implements $OrderEventCopyWith<$Res> {
  factory $FetchActiveOrdersCopyWith(FetchActiveOrders value, $Res Function(FetchActiveOrders) _then) = _$FetchActiveOrdersCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchActiveOrdersCopyWithImpl<$Res>
    implements $FetchActiveOrdersCopyWith<$Res> {
  _$FetchActiveOrdersCopyWithImpl(this._self, this._then);

  final FetchActiveOrders _self;
  final $Res Function(FetchActiveOrders) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchActiveOrders(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchRefundOrders implements OrderEvent {
  const FetchRefundOrders(this.context, {this.isRefresh, this.controller});
  

@override final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchRefundOrdersCopyWith<FetchRefundOrders> get copyWith => _$FetchRefundOrdersCopyWithImpl<FetchRefundOrders>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchRefundOrders&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'OrderEvent.fetchRefundOrders(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchRefundOrdersCopyWith<$Res> implements $OrderEventCopyWith<$Res> {
  factory $FetchRefundOrdersCopyWith(FetchRefundOrders value, $Res Function(FetchRefundOrders) _then) = _$FetchRefundOrdersCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchRefundOrdersCopyWithImpl<$Res>
    implements $FetchRefundOrdersCopyWith<$Res> {
  _$FetchRefundOrdersCopyWithImpl(this._self, this._then);

  final FetchRefundOrders _self;
  final $Res Function(FetchRefundOrders) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchRefundOrders(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchOrderById implements OrderEvent {
  const FetchOrderById(this.context, {required this.id, this.order});
  

@override final  BuildContext context;
 final  int id;
 final  OrderModel? order;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchOrderByIdCopyWith<FetchOrderById> get copyWith => _$FetchOrderByIdCopyWithImpl<FetchOrderById>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchOrderById&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.order, order) || other.order == order));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,order);

@override
String toString() {
  return 'OrderEvent.fetchOrderById(context: $context, id: $id, order: $order)';
}


}

/// @nodoc
abstract mixin class $FetchOrderByIdCopyWith<$Res> implements $OrderEventCopyWith<$Res> {
  factory $FetchOrderByIdCopyWith(FetchOrderById value, $Res Function(FetchOrderById) _then) = _$FetchOrderByIdCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int id, OrderModel? order
});




}
/// @nodoc
class _$FetchOrderByIdCopyWithImpl<$Res>
    implements $FetchOrderByIdCopyWith<$Res> {
  _$FetchOrderByIdCopyWithImpl(this._self, this._then);

  final FetchOrderById _self;
  final $Res Function(FetchOrderById) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? order = freezed,}) {
  return _then(FetchOrderById(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderModel?,
  ));
}


}

/// @nodoc


class FetchRefundOrderById implements OrderEvent {
  const FetchRefundOrderById(this.context, {required this.id, this.refund});
  

@override final  BuildContext context;
 final  int id;
 final  RefundModel? refund;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchRefundOrderByIdCopyWith<FetchRefundOrderById> get copyWith => _$FetchRefundOrderByIdCopyWithImpl<FetchRefundOrderById>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchRefundOrderById&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.refund, refund) || other.refund == refund));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,refund);

@override
String toString() {
  return 'OrderEvent.fetchRefundOrderById(context: $context, id: $id, refund: $refund)';
}


}

/// @nodoc
abstract mixin class $FetchRefundOrderByIdCopyWith<$Res> implements $OrderEventCopyWith<$Res> {
  factory $FetchRefundOrderByIdCopyWith(FetchRefundOrderById value, $Res Function(FetchRefundOrderById) _then) = _$FetchRefundOrderByIdCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int id, RefundModel? refund
});




}
/// @nodoc
class _$FetchRefundOrderByIdCopyWithImpl<$Res>
    implements $FetchRefundOrderByIdCopyWith<$Res> {
  _$FetchRefundOrderByIdCopyWithImpl(this._self, this._then);

  final FetchRefundOrderById _self;
  final $Res Function(FetchRefundOrderById) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? refund = freezed,}) {
  return _then(FetchRefundOrderById(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as RefundModel?,
  ));
}


}

/// @nodoc


class CreateOrder implements OrderEvent {
  const CreateOrder(this.context, {required this.order, required this.payment, required this.totalPrice, required this.onSuccess});
  

@override final  BuildContext context;
 final  CreateOrderModel order;
 final  PaymentData payment;
 final  num totalPrice;
 final  VoidCallback onSuccess;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CreateOrderCopyWith<CreateOrder> get copyWith => _$CreateOrderCopyWithImpl<CreateOrder>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CreateOrder&&(identical(other.context, context) || other.context == context)&&(identical(other.order, order) || other.order == order)&&(identical(other.payment, payment) || other.payment == payment)&&(identical(other.totalPrice, totalPrice) || other.totalPrice == totalPrice)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,order,payment,totalPrice,onSuccess);

@override
String toString() {
  return 'OrderEvent.createOrder(context: $context, order: $order, payment: $payment, totalPrice: $totalPrice, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $CreateOrderCopyWith<$Res> implements $OrderEventCopyWith<$Res> {
  factory $CreateOrderCopyWith(CreateOrder value, $Res Function(CreateOrder) _then) = _$CreateOrderCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, CreateOrderModel order, PaymentData payment, num totalPrice, VoidCallback onSuccess
});




}
/// @nodoc
class _$CreateOrderCopyWithImpl<$Res>
    implements $CreateOrderCopyWith<$Res> {
  _$CreateOrderCopyWithImpl(this._self, this._then);

  final CreateOrder _self;
  final $Res Function(CreateOrder) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? order = null,Object? payment = null,Object? totalPrice = null,Object? onSuccess = null,}) {
  return _then(CreateOrder(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as CreateOrderModel,payment: null == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as PaymentData,totalPrice: null == totalPrice ? _self.totalPrice : totalPrice // ignore: cast_nullable_to_non_nullable
as num,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,
  ));
}


}

/// @nodoc


class CancelOrder implements OrderEvent {
  const CancelOrder(this.context, {required this.id, required this.onSuccess});
  

@override final  BuildContext context;
 final  int id;
 final  VoidCallback onSuccess;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CancelOrderCopyWith<CancelOrder> get copyWith => _$CancelOrderCopyWithImpl<CancelOrder>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CancelOrder&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,onSuccess);

@override
String toString() {
  return 'OrderEvent.cancelOrder(context: $context, id: $id, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $CancelOrderCopyWith<$Res> implements $OrderEventCopyWith<$Res> {
  factory $CancelOrderCopyWith(CancelOrder value, $Res Function(CancelOrder) _then) = _$CancelOrderCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int id, VoidCallback onSuccess
});




}
/// @nodoc
class _$CancelOrderCopyWithImpl<$Res>
    implements $CancelOrderCopyWith<$Res> {
  _$CancelOrderCopyWithImpl(this._self, this._then);

  final CancelOrder _self;
  final $Res Function(CancelOrder) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? onSuccess = null,}) {
  return _then(CancelOrder(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,
  ));
}


}

/// @nodoc


class RefundOrder implements OrderEvent {
  const RefundOrder(this.context, {required this.id, required this.reason, required this.onSuccess});
  

@override final  BuildContext context;
 final  int id;
 final  String reason;
 final  VoidCallback onSuccess;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RefundOrderCopyWith<RefundOrder> get copyWith => _$RefundOrderCopyWithImpl<RefundOrder>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RefundOrder&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.reason, reason) || other.reason == reason)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,reason,onSuccess);

@override
String toString() {
  return 'OrderEvent.refundOrder(context: $context, id: $id, reason: $reason, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $RefundOrderCopyWith<$Res> implements $OrderEventCopyWith<$Res> {
  factory $RefundOrderCopyWith(RefundOrder value, $Res Function(RefundOrder) _then) = _$RefundOrderCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int id, String reason, VoidCallback onSuccess
});




}
/// @nodoc
class _$RefundOrderCopyWithImpl<$Res>
    implements $RefundOrderCopyWith<$Res> {
  _$RefundOrderCopyWithImpl(this._self, this._then);

  final RefundOrder _self;
  final $Res Function(RefundOrder) _then;

/// Create a copy of OrderEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? reason = null,Object? onSuccess = null,}) {
  return _then(RefundOrder(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,reason: null == reason ? _self.reason : reason // ignore: cast_nullable_to_non_nullable
as String,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,
  ));
}


}

/// @nodoc
mixin _$OrderState {

 List<OrderShops> get activeOrders; List<RefundModel> get refundOrders; OrderModel? get order; RefundModel? get refundOrder; bool get isLoadingActive; bool get isLoadingHistory; bool get isLoadingRefund; bool get isLoading; bool get anotherOrder; bool get isButtonLoading;
/// Create a copy of OrderState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderStateCopyWith<OrderState> get copyWith => _$OrderStateCopyWithImpl<OrderState>(this as OrderState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderState&&const DeepCollectionEquality().equals(other.activeOrders, activeOrders)&&const DeepCollectionEquality().equals(other.refundOrders, refundOrders)&&(identical(other.order, order) || other.order == order)&&(identical(other.refundOrder, refundOrder) || other.refundOrder == refundOrder)&&(identical(other.isLoadingActive, isLoadingActive) || other.isLoadingActive == isLoadingActive)&&(identical(other.isLoadingHistory, isLoadingHistory) || other.isLoadingHistory == isLoadingHistory)&&(identical(other.isLoadingRefund, isLoadingRefund) || other.isLoadingRefund == isLoadingRefund)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.anotherOrder, anotherOrder) || other.anotherOrder == anotherOrder)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(activeOrders),const DeepCollectionEquality().hash(refundOrders),order,refundOrder,isLoadingActive,isLoadingHistory,isLoadingRefund,isLoading,anotherOrder,isButtonLoading);

@override
String toString() {
  return 'OrderState(activeOrders: $activeOrders, refundOrders: $refundOrders, order: $order, refundOrder: $refundOrder, isLoadingActive: $isLoadingActive, isLoadingHistory: $isLoadingHistory, isLoadingRefund: $isLoadingRefund, isLoading: $isLoading, anotherOrder: $anotherOrder, isButtonLoading: $isButtonLoading)';
}


}

/// @nodoc
abstract mixin class $OrderStateCopyWith<$Res>  {
  factory $OrderStateCopyWith(OrderState value, $Res Function(OrderState) _then) = _$OrderStateCopyWithImpl;
@useResult
$Res call({
 List<OrderShops> activeOrders, List<RefundModel> refundOrders, OrderModel? order, RefundModel? refundOrder, bool isLoadingActive, bool isLoadingHistory, bool isLoadingRefund, bool isLoading, bool anotherOrder, bool isButtonLoading
});




}
/// @nodoc
class _$OrderStateCopyWithImpl<$Res>
    implements $OrderStateCopyWith<$Res> {
  _$OrderStateCopyWithImpl(this._self, this._then);

  final OrderState _self;
  final $Res Function(OrderState) _then;

/// Create a copy of OrderState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? activeOrders = null,Object? refundOrders = null,Object? order = freezed,Object? refundOrder = freezed,Object? isLoadingActive = null,Object? isLoadingHistory = null,Object? isLoadingRefund = null,Object? isLoading = null,Object? anotherOrder = null,Object? isButtonLoading = null,}) {
  return _then(_self.copyWith(
activeOrders: null == activeOrders ? _self.activeOrders : activeOrders // ignore: cast_nullable_to_non_nullable
as List<OrderShops>,refundOrders: null == refundOrders ? _self.refundOrders : refundOrders // ignore: cast_nullable_to_non_nullable
as List<RefundModel>,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderModel?,refundOrder: freezed == refundOrder ? _self.refundOrder : refundOrder // ignore: cast_nullable_to_non_nullable
as RefundModel?,isLoadingActive: null == isLoadingActive ? _self.isLoadingActive : isLoadingActive // ignore: cast_nullable_to_non_nullable
as bool,isLoadingHistory: null == isLoadingHistory ? _self.isLoadingHistory : isLoadingHistory // ignore: cast_nullable_to_non_nullable
as bool,isLoadingRefund: null == isLoadingRefund ? _self.isLoadingRefund : isLoadingRefund // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,anotherOrder: null == anotherOrder ? _self.anotherOrder : anotherOrder // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderState].
extension OrderStatePatterns on OrderState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderState value)  $default,){
final _that = this;
switch (_that) {
case _OrderState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderState value)?  $default,){
final _that = this;
switch (_that) {
case _OrderState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<OrderShops> activeOrders,  List<RefundModel> refundOrders,  OrderModel? order,  RefundModel? refundOrder,  bool isLoadingActive,  bool isLoadingHistory,  bool isLoadingRefund,  bool isLoading,  bool anotherOrder,  bool isButtonLoading)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderState() when $default != null:
return $default(_that.activeOrders,_that.refundOrders,_that.order,_that.refundOrder,_that.isLoadingActive,_that.isLoadingHistory,_that.isLoadingRefund,_that.isLoading,_that.anotherOrder,_that.isButtonLoading);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<OrderShops> activeOrders,  List<RefundModel> refundOrders,  OrderModel? order,  RefundModel? refundOrder,  bool isLoadingActive,  bool isLoadingHistory,  bool isLoadingRefund,  bool isLoading,  bool anotherOrder,  bool isButtonLoading)  $default,) {final _that = this;
switch (_that) {
case _OrderState():
return $default(_that.activeOrders,_that.refundOrders,_that.order,_that.refundOrder,_that.isLoadingActive,_that.isLoadingHistory,_that.isLoadingRefund,_that.isLoading,_that.anotherOrder,_that.isButtonLoading);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<OrderShops> activeOrders,  List<RefundModel> refundOrders,  OrderModel? order,  RefundModel? refundOrder,  bool isLoadingActive,  bool isLoadingHistory,  bool isLoadingRefund,  bool isLoading,  bool anotherOrder,  bool isButtonLoading)?  $default,) {final _that = this;
switch (_that) {
case _OrderState() when $default != null:
return $default(_that.activeOrders,_that.refundOrders,_that.order,_that.refundOrder,_that.isLoadingActive,_that.isLoadingHistory,_that.isLoadingRefund,_that.isLoading,_that.anotherOrder,_that.isButtonLoading);case _:
  return null;

}
}

}

/// @nodoc


class _OrderState implements OrderState {
  const _OrderState({final  List<OrderShops> activeOrders = const [], final  List<RefundModel> refundOrders = const [], this.order = null, this.refundOrder = null, this.isLoadingActive = true, this.isLoadingHistory = true, this.isLoadingRefund = true, this.isLoading = false, this.anotherOrder = false, this.isButtonLoading = false}): _activeOrders = activeOrders,_refundOrders = refundOrders;
  

 final  List<OrderShops> _activeOrders;
@override@JsonKey() List<OrderShops> get activeOrders {
  if (_activeOrders is EqualUnmodifiableListView) return _activeOrders;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_activeOrders);
}

 final  List<RefundModel> _refundOrders;
@override@JsonKey() List<RefundModel> get refundOrders {
  if (_refundOrders is EqualUnmodifiableListView) return _refundOrders;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_refundOrders);
}

@override@JsonKey() final  OrderModel? order;
@override@JsonKey() final  RefundModel? refundOrder;
@override@JsonKey() final  bool isLoadingActive;
@override@JsonKey() final  bool isLoadingHistory;
@override@JsonKey() final  bool isLoadingRefund;
@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool anotherOrder;
@override@JsonKey() final  bool isButtonLoading;

/// Create a copy of OrderState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderStateCopyWith<_OrderState> get copyWith => __$OrderStateCopyWithImpl<_OrderState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderState&&const DeepCollectionEquality().equals(other._activeOrders, _activeOrders)&&const DeepCollectionEquality().equals(other._refundOrders, _refundOrders)&&(identical(other.order, order) || other.order == order)&&(identical(other.refundOrder, refundOrder) || other.refundOrder == refundOrder)&&(identical(other.isLoadingActive, isLoadingActive) || other.isLoadingActive == isLoadingActive)&&(identical(other.isLoadingHistory, isLoadingHistory) || other.isLoadingHistory == isLoadingHistory)&&(identical(other.isLoadingRefund, isLoadingRefund) || other.isLoadingRefund == isLoadingRefund)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.anotherOrder, anotherOrder) || other.anotherOrder == anotherOrder)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_activeOrders),const DeepCollectionEquality().hash(_refundOrders),order,refundOrder,isLoadingActive,isLoadingHistory,isLoadingRefund,isLoading,anotherOrder,isButtonLoading);

@override
String toString() {
  return 'OrderState(activeOrders: $activeOrders, refundOrders: $refundOrders, order: $order, refundOrder: $refundOrder, isLoadingActive: $isLoadingActive, isLoadingHistory: $isLoadingHistory, isLoadingRefund: $isLoadingRefund, isLoading: $isLoading, anotherOrder: $anotherOrder, isButtonLoading: $isButtonLoading)';
}


}

/// @nodoc
abstract mixin class _$OrderStateCopyWith<$Res> implements $OrderStateCopyWith<$Res> {
  factory _$OrderStateCopyWith(_OrderState value, $Res Function(_OrderState) _then) = __$OrderStateCopyWithImpl;
@override @useResult
$Res call({
 List<OrderShops> activeOrders, List<RefundModel> refundOrders, OrderModel? order, RefundModel? refundOrder, bool isLoadingActive, bool isLoadingHistory, bool isLoadingRefund, bool isLoading, bool anotherOrder, bool isButtonLoading
});




}
/// @nodoc
class __$OrderStateCopyWithImpl<$Res>
    implements _$OrderStateCopyWith<$Res> {
  __$OrderStateCopyWithImpl(this._self, this._then);

  final _OrderState _self;
  final $Res Function(_OrderState) _then;

/// Create a copy of OrderState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? activeOrders = null,Object? refundOrders = null,Object? order = freezed,Object? refundOrder = freezed,Object? isLoadingActive = null,Object? isLoadingHistory = null,Object? isLoadingRefund = null,Object? isLoading = null,Object? anotherOrder = null,Object? isButtonLoading = null,}) {
  return _then(_OrderState(
activeOrders: null == activeOrders ? _self._activeOrders : activeOrders // ignore: cast_nullable_to_non_nullable
as List<OrderShops>,refundOrders: null == refundOrders ? _self._refundOrders : refundOrders // ignore: cast_nullable_to_non_nullable
as List<RefundModel>,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderModel?,refundOrder: freezed == refundOrder ? _self.refundOrder : refundOrder // ignore: cast_nullable_to_non_nullable
as RefundModel?,isLoadingActive: null == isLoadingActive ? _self.isLoadingActive : isLoadingActive // ignore: cast_nullable_to_non_nullable
as bool,isLoadingHistory: null == isLoadingHistory ? _self.isLoadingHistory : isLoadingHistory // ignore: cast_nullable_to_non_nullable
as bool,isLoadingRefund: null == isLoadingRefund ? _self.isLoadingRefund : isLoadingRefund // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,anotherOrder: null == anotherOrder ? _self.anotherOrder : anotherOrder // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
