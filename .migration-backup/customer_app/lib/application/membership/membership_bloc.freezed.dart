// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'membership_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$MembershipEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MembershipEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'MembershipEvent()';
}


}

/// @nodoc
class $MembershipEventCopyWith<$Res>  {
$MembershipEventCopyWith(MembershipEvent _, $Res Function(MembershipEvent) __);
}


/// Adds pattern-matching-related methods to [MembershipEvent].
extension MembershipEventPatterns on MembershipEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( SetWalletPrice value)?  setWalletPrice,TResult Function( FetchMembership value)?  fetchMembership,TResult Function( FetchMembershipDetails value)?  fetchMembershipDetails,TResult Function( SelectPayment value)?  selectPayment,TResult Function( FetchPayments value)?  fetchPayments,TResult Function( FetchWebView value)?  fetchWebView,TResult Function( CreateTransaction value)?  createTransaction,TResult Function( MyMemberships value)?  myMemberships,required TResult orElse(),}){
final _that = this;
switch (_that) {
case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that);case FetchMembership() when fetchMembership != null:
return fetchMembership(_that);case FetchMembershipDetails() when fetchMembershipDetails != null:
return fetchMembershipDetails(_that);case SelectPayment() when selectPayment != null:
return selectPayment(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that);case CreateTransaction() when createTransaction != null:
return createTransaction(_that);case MyMemberships() when myMemberships != null:
return myMemberships(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( SetWalletPrice value)  setWalletPrice,required TResult Function( FetchMembership value)  fetchMembership,required TResult Function( FetchMembershipDetails value)  fetchMembershipDetails,required TResult Function( SelectPayment value)  selectPayment,required TResult Function( FetchPayments value)  fetchPayments,required TResult Function( FetchWebView value)  fetchWebView,required TResult Function( CreateTransaction value)  createTransaction,required TResult Function( MyMemberships value)  myMemberships,}){
final _that = this;
switch (_that) {
case SetWalletPrice():
return setWalletPrice(_that);case FetchMembership():
return fetchMembership(_that);case FetchMembershipDetails():
return fetchMembershipDetails(_that);case SelectPayment():
return selectPayment(_that);case FetchPayments():
return fetchPayments(_that);case FetchWebView():
return fetchWebView(_that);case CreateTransaction():
return createTransaction(_that);case MyMemberships():
return myMemberships(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( SetWalletPrice value)?  setWalletPrice,TResult? Function( FetchMembership value)?  fetchMembership,TResult? Function( FetchMembershipDetails value)?  fetchMembershipDetails,TResult? Function( SelectPayment value)?  selectPayment,TResult? Function( FetchPayments value)?  fetchPayments,TResult? Function( FetchWebView value)?  fetchWebView,TResult? Function( CreateTransaction value)?  createTransaction,TResult? Function( MyMemberships value)?  myMemberships,}){
final _that = this;
switch (_that) {
case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that);case FetchMembership() when fetchMembership != null:
return fetchMembership(_that);case FetchMembershipDetails() when fetchMembershipDetails != null:
return fetchMembershipDetails(_that);case SelectPayment() when selectPayment != null:
return selectPayment(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that);case CreateTransaction() when createTransaction != null:
return createTransaction(_that);case MyMemberships() when myMemberships != null:
return myMemberships(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( num? price)?  setWalletPrice,TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchMembership,TResult Function( BuildContext context,  int? shopId,  int? id,  String? shopSlug,  bool? enable)?  fetchMembershipDetails,TResult Function( PaymentData payment)?  selectPayment,TResult Function( BuildContext context)?  fetchPayments,TResult Function( BuildContext context,  MembershipModel? membership,  VoidCallback? onSuccess)?  fetchWebView,TResult Function( BuildContext context,  int membershipId,  VoidCallback? onSuccess)?  createTransaction,TResult Function( BuildContext context,  bool? isRefresh,  int? serviceId,  int? shopId,  RefreshController? controller)?  myMemberships,required TResult orElse(),}) {final _that = this;
switch (_that) {
case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that.price);case FetchMembership() when fetchMembership != null:
return fetchMembership(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchMembershipDetails() when fetchMembershipDetails != null:
return fetchMembershipDetails(_that.context,_that.shopId,_that.id,_that.shopSlug,_that.enable);case SelectPayment() when selectPayment != null:
return selectPayment(_that.payment);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that.context,_that.membership,_that.onSuccess);case CreateTransaction() when createTransaction != null:
return createTransaction(_that.context,_that.membershipId,_that.onSuccess);case MyMemberships() when myMemberships != null:
return myMemberships(_that.context,_that.isRefresh,_that.serviceId,_that.shopId,_that.controller);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( num? price)  setWalletPrice,required TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)  fetchMembership,required TResult Function( BuildContext context,  int? shopId,  int? id,  String? shopSlug,  bool? enable)  fetchMembershipDetails,required TResult Function( PaymentData payment)  selectPayment,required TResult Function( BuildContext context)  fetchPayments,required TResult Function( BuildContext context,  MembershipModel? membership,  VoidCallback? onSuccess)  fetchWebView,required TResult Function( BuildContext context,  int membershipId,  VoidCallback? onSuccess)  createTransaction,required TResult Function( BuildContext context,  bool? isRefresh,  int? serviceId,  int? shopId,  RefreshController? controller)  myMemberships,}) {final _that = this;
switch (_that) {
case SetWalletPrice():
return setWalletPrice(_that.price);case FetchMembership():
return fetchMembership(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchMembershipDetails():
return fetchMembershipDetails(_that.context,_that.shopId,_that.id,_that.shopSlug,_that.enable);case SelectPayment():
return selectPayment(_that.payment);case FetchPayments():
return fetchPayments(_that.context);case FetchWebView():
return fetchWebView(_that.context,_that.membership,_that.onSuccess);case CreateTransaction():
return createTransaction(_that.context,_that.membershipId,_that.onSuccess);case MyMemberships():
return myMemberships(_that.context,_that.isRefresh,_that.serviceId,_that.shopId,_that.controller);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( num? price)?  setWalletPrice,TResult? Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchMembership,TResult? Function( BuildContext context,  int? shopId,  int? id,  String? shopSlug,  bool? enable)?  fetchMembershipDetails,TResult? Function( PaymentData payment)?  selectPayment,TResult? Function( BuildContext context)?  fetchPayments,TResult? Function( BuildContext context,  MembershipModel? membership,  VoidCallback? onSuccess)?  fetchWebView,TResult? Function( BuildContext context,  int membershipId,  VoidCallback? onSuccess)?  createTransaction,TResult? Function( BuildContext context,  bool? isRefresh,  int? serviceId,  int? shopId,  RefreshController? controller)?  myMemberships,}) {final _that = this;
switch (_that) {
case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that.price);case FetchMembership() when fetchMembership != null:
return fetchMembership(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchMembershipDetails() when fetchMembershipDetails != null:
return fetchMembershipDetails(_that.context,_that.shopId,_that.id,_that.shopSlug,_that.enable);case SelectPayment() when selectPayment != null:
return selectPayment(_that.payment);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that.context,_that.membership,_that.onSuccess);case CreateTransaction() when createTransaction != null:
return createTransaction(_that.context,_that.membershipId,_that.onSuccess);case MyMemberships() when myMemberships != null:
return myMemberships(_that.context,_that.isRefresh,_that.serviceId,_that.shopId,_that.controller);case _:
  return null;

}
}

}

/// @nodoc


class SetWalletPrice implements MembershipEvent {
  const SetWalletPrice({this.price});
  

 final  num? price;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetWalletPriceCopyWith<SetWalletPrice> get copyWith => _$SetWalletPriceCopyWithImpl<SetWalletPrice>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetWalletPrice&&(identical(other.price, price) || other.price == price));
}


@override
int get hashCode => Object.hash(runtimeType,price);

@override
String toString() {
  return 'MembershipEvent.setWalletPrice(price: $price)';
}


}

/// @nodoc
abstract mixin class $SetWalletPriceCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
  factory $SetWalletPriceCopyWith(SetWalletPrice value, $Res Function(SetWalletPrice) _then) = _$SetWalletPriceCopyWithImpl;
@useResult
$Res call({
 num? price
});




}
/// @nodoc
class _$SetWalletPriceCopyWithImpl<$Res>
    implements $SetWalletPriceCopyWith<$Res> {
  _$SetWalletPriceCopyWithImpl(this._self, this._then);

  final SetWalletPrice _self;
  final $Res Function(SetWalletPrice) _then;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? price = freezed,}) {
  return _then(SetWalletPrice(
price: freezed == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as num?,
  ));
}


}

/// @nodoc


class FetchMembership implements MembershipEvent {
  const FetchMembership(this.context, {this.isRefresh, this.shopId, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  int? shopId;
 final  RefreshController? controller;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMembershipCopyWith<FetchMembership> get copyWith => _$FetchMembershipCopyWithImpl<FetchMembership>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMembership&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,controller);

@override
String toString() {
  return 'MembershipEvent.fetchMembership(context: $context, isRefresh: $isRefresh, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchMembershipCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
  factory $FetchMembershipCopyWith(FetchMembership value, $Res Function(FetchMembership) _then) = _$FetchMembershipCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$FetchMembershipCopyWithImpl<$Res>
    implements $FetchMembershipCopyWith<$Res> {
  _$FetchMembershipCopyWithImpl(this._self, this._then);

  final FetchMembership _self;
  final $Res Function(FetchMembership) _then;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(FetchMembership(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchMembershipDetails implements MembershipEvent {
  const FetchMembershipDetails(this.context, {this.shopId, this.id, this.shopSlug, this.enable});
  

 final  BuildContext context;
 final  int? shopId;
 final  int? id;
 final  String? shopSlug;
 final  bool? enable;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMembershipDetailsCopyWith<FetchMembershipDetails> get copyWith => _$FetchMembershipDetailsCopyWithImpl<FetchMembershipDetails>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMembershipDetails&&(identical(other.context, context) || other.context == context)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.id, id) || other.id == id)&&(identical(other.shopSlug, shopSlug) || other.shopSlug == shopSlug)&&(identical(other.enable, enable) || other.enable == enable));
}


@override
int get hashCode => Object.hash(runtimeType,context,shopId,id,shopSlug,enable);

@override
String toString() {
  return 'MembershipEvent.fetchMembershipDetails(context: $context, shopId: $shopId, id: $id, shopSlug: $shopSlug, enable: $enable)';
}


}

/// @nodoc
abstract mixin class $FetchMembershipDetailsCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
  factory $FetchMembershipDetailsCopyWith(FetchMembershipDetails value, $Res Function(FetchMembershipDetails) _then) = _$FetchMembershipDetailsCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? shopId, int? id, String? shopSlug, bool? enable
});




}
/// @nodoc
class _$FetchMembershipDetailsCopyWithImpl<$Res>
    implements $FetchMembershipDetailsCopyWith<$Res> {
  _$FetchMembershipDetailsCopyWithImpl(this._self, this._then);

  final FetchMembershipDetails _self;
  final $Res Function(FetchMembershipDetails) _then;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? shopId = freezed,Object? id = freezed,Object? shopSlug = freezed,Object? enable = freezed,}) {
  return _then(FetchMembershipDetails(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,id: freezed == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int?,shopSlug: freezed == shopSlug ? _self.shopSlug : shopSlug // ignore: cast_nullable_to_non_nullable
as String?,enable: freezed == enable ? _self.enable : enable // ignore: cast_nullable_to_non_nullable
as bool?,
  ));
}


}

/// @nodoc


class SelectPayment implements MembershipEvent {
  const SelectPayment({required this.payment});
  

 final  PaymentData payment;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectPaymentCopyWith<SelectPayment> get copyWith => _$SelectPaymentCopyWithImpl<SelectPayment>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectPayment&&(identical(other.payment, payment) || other.payment == payment));
}


@override
int get hashCode => Object.hash(runtimeType,payment);

@override
String toString() {
  return 'MembershipEvent.selectPayment(payment: $payment)';
}


}

/// @nodoc
abstract mixin class $SelectPaymentCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
  factory $SelectPaymentCopyWith(SelectPayment value, $Res Function(SelectPayment) _then) = _$SelectPaymentCopyWithImpl;
@useResult
$Res call({
 PaymentData payment
});




}
/// @nodoc
class _$SelectPaymentCopyWithImpl<$Res>
    implements $SelectPaymentCopyWith<$Res> {
  _$SelectPaymentCopyWithImpl(this._self, this._then);

  final SelectPayment _self;
  final $Res Function(SelectPayment) _then;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? payment = null,}) {
  return _then(SelectPayment(
payment: null == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as PaymentData,
  ));
}


}

/// @nodoc


class FetchPayments implements MembershipEvent {
  const FetchPayments(this.context);
  

 final  BuildContext context;

/// Create a copy of MembershipEvent
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
  return 'MembershipEvent.fetchPayments(context: $context)';
}


}

/// @nodoc
abstract mixin class $FetchPaymentsCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
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

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(FetchPayments(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class FetchWebView implements MembershipEvent {
  const FetchWebView(this.context, {required this.membership, required this.onSuccess});
  

 final  BuildContext context;
 final  MembershipModel? membership;
 final  VoidCallback? onSuccess;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchWebViewCopyWith<FetchWebView> get copyWith => _$FetchWebViewCopyWithImpl<FetchWebView>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchWebView&&(identical(other.context, context) || other.context == context)&&(identical(other.membership, membership) || other.membership == membership)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,membership,onSuccess);

@override
String toString() {
  return 'MembershipEvent.fetchWebView(context: $context, membership: $membership, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $FetchWebViewCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
  factory $FetchWebViewCopyWith(FetchWebView value, $Res Function(FetchWebView) _then) = _$FetchWebViewCopyWithImpl;
@useResult
$Res call({
 BuildContext context, MembershipModel? membership, VoidCallback? onSuccess
});




}
/// @nodoc
class _$FetchWebViewCopyWithImpl<$Res>
    implements $FetchWebViewCopyWith<$Res> {
  _$FetchWebViewCopyWithImpl(this._self, this._then);

  final FetchWebView _self;
  final $Res Function(FetchWebView) _then;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? membership = freezed,Object? onSuccess = freezed,}) {
  return _then(FetchWebView(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,membership: freezed == membership ? _self.membership : membership // ignore: cast_nullable_to_non_nullable
as MembershipModel?,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class CreateTransaction implements MembershipEvent {
  const CreateTransaction(this.context, {required this.membershipId, required this.onSuccess});
  

 final  BuildContext context;
 final  int membershipId;
 final  VoidCallback? onSuccess;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CreateTransactionCopyWith<CreateTransaction> get copyWith => _$CreateTransactionCopyWithImpl<CreateTransaction>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CreateTransaction&&(identical(other.context, context) || other.context == context)&&(identical(other.membershipId, membershipId) || other.membershipId == membershipId)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,membershipId,onSuccess);

@override
String toString() {
  return 'MembershipEvent.createTransaction(context: $context, membershipId: $membershipId, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $CreateTransactionCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
  factory $CreateTransactionCopyWith(CreateTransaction value, $Res Function(CreateTransaction) _then) = _$CreateTransactionCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int membershipId, VoidCallback? onSuccess
});




}
/// @nodoc
class _$CreateTransactionCopyWithImpl<$Res>
    implements $CreateTransactionCopyWith<$Res> {
  _$CreateTransactionCopyWithImpl(this._self, this._then);

  final CreateTransaction _self;
  final $Res Function(CreateTransaction) _then;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? membershipId = null,Object? onSuccess = freezed,}) {
  return _then(CreateTransaction(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,membershipId: null == membershipId ? _self.membershipId : membershipId // ignore: cast_nullable_to_non_nullable
as int,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class MyMemberships implements MembershipEvent {
  const MyMemberships(this.context, {this.isRefresh, this.serviceId, this.shopId, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  int? serviceId;
 final  int? shopId;
 final  RefreshController? controller;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MyMembershipsCopyWith<MyMemberships> get copyWith => _$MyMembershipsCopyWithImpl<MyMemberships>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MyMemberships&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.serviceId, serviceId) || other.serviceId == serviceId)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,serviceId,shopId,controller);

@override
String toString() {
  return 'MembershipEvent.myMemberships(context: $context, isRefresh: $isRefresh, serviceId: $serviceId, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $MyMembershipsCopyWith<$Res> implements $MembershipEventCopyWith<$Res> {
  factory $MyMembershipsCopyWith(MyMemberships value, $Res Function(MyMemberships) _then) = _$MyMembershipsCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, int? serviceId, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$MyMembershipsCopyWithImpl<$Res>
    implements $MyMembershipsCopyWith<$Res> {
  _$MyMembershipsCopyWithImpl(this._self, this._then);

  final MyMemberships _self;
  final $Res Function(MyMemberships) _then;

/// Create a copy of MembershipEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? serviceId = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(MyMemberships(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,serviceId: freezed == serviceId ? _self.serviceId : serviceId // ignore: cast_nullable_to_non_nullable
as int?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc
mixin _$MembershipState {

 List<MembershipModel> get memberships; List<MyMembershipModel> get myMemberships; bool get isLoading; bool get isButtonLoading; bool get isPaymentLoading; List<PaymentData> get payments; List<PaymentData> get availablePayments; num? get walletPrice; MembershipModel? get membership; PaymentData? get selectedPayment;
/// Create a copy of MembershipState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MembershipStateCopyWith<MembershipState> get copyWith => _$MembershipStateCopyWithImpl<MembershipState>(this as MembershipState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MembershipState&&const DeepCollectionEquality().equals(other.memberships, memberships)&&const DeepCollectionEquality().equals(other.myMemberships, myMemberships)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isPaymentLoading, isPaymentLoading) || other.isPaymentLoading == isPaymentLoading)&&const DeepCollectionEquality().equals(other.payments, payments)&&const DeepCollectionEquality().equals(other.availablePayments, availablePayments)&&(identical(other.walletPrice, walletPrice) || other.walletPrice == walletPrice)&&(identical(other.membership, membership) || other.membership == membership)&&(identical(other.selectedPayment, selectedPayment) || other.selectedPayment == selectedPayment));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(memberships),const DeepCollectionEquality().hash(myMemberships),isLoading,isButtonLoading,isPaymentLoading,const DeepCollectionEquality().hash(payments),const DeepCollectionEquality().hash(availablePayments),walletPrice,membership,selectedPayment);

@override
String toString() {
  return 'MembershipState(memberships: $memberships, myMemberships: $myMemberships, isLoading: $isLoading, isButtonLoading: $isButtonLoading, isPaymentLoading: $isPaymentLoading, payments: $payments, availablePayments: $availablePayments, walletPrice: $walletPrice, membership: $membership, selectedPayment: $selectedPayment)';
}


}

/// @nodoc
abstract mixin class $MembershipStateCopyWith<$Res>  {
  factory $MembershipStateCopyWith(MembershipState value, $Res Function(MembershipState) _then) = _$MembershipStateCopyWithImpl;
@useResult
$Res call({
 List<MembershipModel> memberships, List<MyMembershipModel> myMemberships, bool isLoading, bool isButtonLoading, bool isPaymentLoading, List<PaymentData> payments, List<PaymentData> availablePayments, num? walletPrice, MembershipModel? membership, PaymentData? selectedPayment
});




}
/// @nodoc
class _$MembershipStateCopyWithImpl<$Res>
    implements $MembershipStateCopyWith<$Res> {
  _$MembershipStateCopyWithImpl(this._self, this._then);

  final MembershipState _self;
  final $Res Function(MembershipState) _then;

/// Create a copy of MembershipState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? memberships = null,Object? myMemberships = null,Object? isLoading = null,Object? isButtonLoading = null,Object? isPaymentLoading = null,Object? payments = null,Object? availablePayments = null,Object? walletPrice = freezed,Object? membership = freezed,Object? selectedPayment = freezed,}) {
  return _then(_self.copyWith(
memberships: null == memberships ? _self.memberships : memberships // ignore: cast_nullable_to_non_nullable
as List<MembershipModel>,myMemberships: null == myMemberships ? _self.myMemberships : myMemberships // ignore: cast_nullable_to_non_nullable
as List<MyMembershipModel>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isPaymentLoading: null == isPaymentLoading ? _self.isPaymentLoading : isPaymentLoading // ignore: cast_nullable_to_non_nullable
as bool,payments: null == payments ? _self.payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,availablePayments: null == availablePayments ? _self.availablePayments : availablePayments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,walletPrice: freezed == walletPrice ? _self.walletPrice : walletPrice // ignore: cast_nullable_to_non_nullable
as num?,membership: freezed == membership ? _self.membership : membership // ignore: cast_nullable_to_non_nullable
as MembershipModel?,selectedPayment: freezed == selectedPayment ? _self.selectedPayment : selectedPayment // ignore: cast_nullable_to_non_nullable
as PaymentData?,
  ));
}

}


/// Adds pattern-matching-related methods to [MembershipState].
extension MembershipStatePatterns on MembershipState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MembershipState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MembershipState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MembershipState value)  $default,){
final _that = this;
switch (_that) {
case _MembershipState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MembershipState value)?  $default,){
final _that = this;
switch (_that) {
case _MembershipState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<MembershipModel> memberships,  List<MyMembershipModel> myMemberships,  bool isLoading,  bool isButtonLoading,  bool isPaymentLoading,  List<PaymentData> payments,  List<PaymentData> availablePayments,  num? walletPrice,  MembershipModel? membership,  PaymentData? selectedPayment)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MembershipState() when $default != null:
return $default(_that.memberships,_that.myMemberships,_that.isLoading,_that.isButtonLoading,_that.isPaymentLoading,_that.payments,_that.availablePayments,_that.walletPrice,_that.membership,_that.selectedPayment);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<MembershipModel> memberships,  List<MyMembershipModel> myMemberships,  bool isLoading,  bool isButtonLoading,  bool isPaymentLoading,  List<PaymentData> payments,  List<PaymentData> availablePayments,  num? walletPrice,  MembershipModel? membership,  PaymentData? selectedPayment)  $default,) {final _that = this;
switch (_that) {
case _MembershipState():
return $default(_that.memberships,_that.myMemberships,_that.isLoading,_that.isButtonLoading,_that.isPaymentLoading,_that.payments,_that.availablePayments,_that.walletPrice,_that.membership,_that.selectedPayment);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<MembershipModel> memberships,  List<MyMembershipModel> myMemberships,  bool isLoading,  bool isButtonLoading,  bool isPaymentLoading,  List<PaymentData> payments,  List<PaymentData> availablePayments,  num? walletPrice,  MembershipModel? membership,  PaymentData? selectedPayment)?  $default,) {final _that = this;
switch (_that) {
case _MembershipState() when $default != null:
return $default(_that.memberships,_that.myMemberships,_that.isLoading,_that.isButtonLoading,_that.isPaymentLoading,_that.payments,_that.availablePayments,_that.walletPrice,_that.membership,_that.selectedPayment);case _:
  return null;

}
}

}

/// @nodoc


class _MembershipState implements MembershipState {
  const _MembershipState({final  List<MembershipModel> memberships = const [], final  List<MyMembershipModel> myMemberships = const [], this.isLoading = true, this.isButtonLoading = false, this.isPaymentLoading = true, final  List<PaymentData> payments = const [], final  List<PaymentData> availablePayments = const [], this.walletPrice, this.membership, this.selectedPayment}): _memberships = memberships,_myMemberships = myMemberships,_payments = payments,_availablePayments = availablePayments;
  

 final  List<MembershipModel> _memberships;
@override@JsonKey() List<MembershipModel> get memberships {
  if (_memberships is EqualUnmodifiableListView) return _memberships;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_memberships);
}

 final  List<MyMembershipModel> _myMemberships;
@override@JsonKey() List<MyMembershipModel> get myMemberships {
  if (_myMemberships is EqualUnmodifiableListView) return _myMemberships;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_myMemberships);
}

@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isButtonLoading;
@override@JsonKey() final  bool isPaymentLoading;
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

@override final  num? walletPrice;
@override final  MembershipModel? membership;
@override final  PaymentData? selectedPayment;

/// Create a copy of MembershipState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MembershipStateCopyWith<_MembershipState> get copyWith => __$MembershipStateCopyWithImpl<_MembershipState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _MembershipState&&const DeepCollectionEquality().equals(other._memberships, _memberships)&&const DeepCollectionEquality().equals(other._myMemberships, _myMemberships)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isPaymentLoading, isPaymentLoading) || other.isPaymentLoading == isPaymentLoading)&&const DeepCollectionEquality().equals(other._payments, _payments)&&const DeepCollectionEquality().equals(other._availablePayments, _availablePayments)&&(identical(other.walletPrice, walletPrice) || other.walletPrice == walletPrice)&&(identical(other.membership, membership) || other.membership == membership)&&(identical(other.selectedPayment, selectedPayment) || other.selectedPayment == selectedPayment));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_memberships),const DeepCollectionEquality().hash(_myMemberships),isLoading,isButtonLoading,isPaymentLoading,const DeepCollectionEquality().hash(_payments),const DeepCollectionEquality().hash(_availablePayments),walletPrice,membership,selectedPayment);

@override
String toString() {
  return 'MembershipState(memberships: $memberships, myMemberships: $myMemberships, isLoading: $isLoading, isButtonLoading: $isButtonLoading, isPaymentLoading: $isPaymentLoading, payments: $payments, availablePayments: $availablePayments, walletPrice: $walletPrice, membership: $membership, selectedPayment: $selectedPayment)';
}


}

/// @nodoc
abstract mixin class _$MembershipStateCopyWith<$Res> implements $MembershipStateCopyWith<$Res> {
  factory _$MembershipStateCopyWith(_MembershipState value, $Res Function(_MembershipState) _then) = __$MembershipStateCopyWithImpl;
@override @useResult
$Res call({
 List<MembershipModel> memberships, List<MyMembershipModel> myMemberships, bool isLoading, bool isButtonLoading, bool isPaymentLoading, List<PaymentData> payments, List<PaymentData> availablePayments, num? walletPrice, MembershipModel? membership, PaymentData? selectedPayment
});




}
/// @nodoc
class __$MembershipStateCopyWithImpl<$Res>
    implements _$MembershipStateCopyWith<$Res> {
  __$MembershipStateCopyWithImpl(this._self, this._then);

  final _MembershipState _self;
  final $Res Function(_MembershipState) _then;

/// Create a copy of MembershipState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? memberships = null,Object? myMemberships = null,Object? isLoading = null,Object? isButtonLoading = null,Object? isPaymentLoading = null,Object? payments = null,Object? availablePayments = null,Object? walletPrice = freezed,Object? membership = freezed,Object? selectedPayment = freezed,}) {
  return _then(_MembershipState(
memberships: null == memberships ? _self._memberships : memberships // ignore: cast_nullable_to_non_nullable
as List<MembershipModel>,myMemberships: null == myMemberships ? _self._myMemberships : myMemberships // ignore: cast_nullable_to_non_nullable
as List<MyMembershipModel>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isPaymentLoading: null == isPaymentLoading ? _self.isPaymentLoading : isPaymentLoading // ignore: cast_nullable_to_non_nullable
as bool,payments: null == payments ? _self._payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,availablePayments: null == availablePayments ? _self._availablePayments : availablePayments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,walletPrice: freezed == walletPrice ? _self.walletPrice : walletPrice // ignore: cast_nullable_to_non_nullable
as num?,membership: freezed == membership ? _self.membership : membership // ignore: cast_nullable_to_non_nullable
as MembershipModel?,selectedPayment: freezed == selectedPayment ? _self.selectedPayment : selectedPayment // ignore: cast_nullable_to_non_nullable
as PaymentData?,
  ));
}


}

// dart format on
