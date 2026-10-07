// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'booking_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$BookingEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BookingEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'BookingEvent()';
}


}

/// @nodoc
class $BookingEventCopyWith<$Res>  {
$BookingEventCopyWith(BookingEvent _, $Res Function(BookingEvent) __);
}


/// Adds pattern-matching-related methods to [BookingEvent].
extension BookingEventPatterns on BookingEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchBookUpcoming value)?  fetchBookUpcoming,TResult Function( SetWalletPrice value)?  setWalletPrice,TResult Function( FetchBookPast value)?  fetchBookPast,TResult Function( FetchBookingById value)?  fetchBookingById,TResult Function( BookingService value)?  bookingService,TResult Function( CalculateBooking value)?  calculateBooking,TResult Function( CheckTime value)?  checkTime,TResult Function( SelectPayment value)?  selectPayment,TResult Function( PayLater value)?  payLater,TResult Function( FetchPayments value)?  fetchPayments,TResult Function( FetchWebView value)?  fetchWebView,TResult Function( CancelBook value)?  cancelBook,TResult Function( SelectDateTime value)?  selectDateTime,TResult Function( SelectBookingTime value)?  selectBookingTime,TResult Function( SetServices value)?  setServices,TResult Function( SelectMaster value)?  selectMaster,TResult Function( SelectTime value)?  selectTime,TResult Function( SelectAddress value)?  selectAddress,TResult Function( SetGiftCart value)?  setGiftCart,TResult Function( SaveForm value)?  saveForm,TResult Function( UpdateNotes value)?  updateNotes,TResult Function( CheckCoupon value)?  checkCoupon,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchBookUpcoming() when fetchBookUpcoming != null:
return fetchBookUpcoming(_that);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that);case FetchBookPast() when fetchBookPast != null:
return fetchBookPast(_that);case FetchBookingById() when fetchBookingById != null:
return fetchBookingById(_that);case BookingService() when bookingService != null:
return bookingService(_that);case CalculateBooking() when calculateBooking != null:
return calculateBooking(_that);case CheckTime() when checkTime != null:
return checkTime(_that);case SelectPayment() when selectPayment != null:
return selectPayment(_that);case PayLater() when payLater != null:
return payLater(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that);case CancelBook() when cancelBook != null:
return cancelBook(_that);case SelectDateTime() when selectDateTime != null:
return selectDateTime(_that);case SelectBookingTime() when selectBookingTime != null:
return selectBookingTime(_that);case SetServices() when setServices != null:
return setServices(_that);case SelectMaster() when selectMaster != null:
return selectMaster(_that);case SelectTime() when selectTime != null:
return selectTime(_that);case SelectAddress() when selectAddress != null:
return selectAddress(_that);case SetGiftCart() when setGiftCart != null:
return setGiftCart(_that);case SaveForm() when saveForm != null:
return saveForm(_that);case UpdateNotes() when updateNotes != null:
return updateNotes(_that);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchBookUpcoming value)  fetchBookUpcoming,required TResult Function( SetWalletPrice value)  setWalletPrice,required TResult Function( FetchBookPast value)  fetchBookPast,required TResult Function( FetchBookingById value)  fetchBookingById,required TResult Function( BookingService value)  bookingService,required TResult Function( CalculateBooking value)  calculateBooking,required TResult Function( CheckTime value)  checkTime,required TResult Function( SelectPayment value)  selectPayment,required TResult Function( PayLater value)  payLater,required TResult Function( FetchPayments value)  fetchPayments,required TResult Function( FetchWebView value)  fetchWebView,required TResult Function( CancelBook value)  cancelBook,required TResult Function( SelectDateTime value)  selectDateTime,required TResult Function( SelectBookingTime value)  selectBookingTime,required TResult Function( SetServices value)  setServices,required TResult Function( SelectMaster value)  selectMaster,required TResult Function( SelectTime value)  selectTime,required TResult Function( SelectAddress value)  selectAddress,required TResult Function( SetGiftCart value)  setGiftCart,required TResult Function( SaveForm value)  saveForm,required TResult Function( UpdateNotes value)  updateNotes,required TResult Function( CheckCoupon value)  checkCoupon,}){
final _that = this;
switch (_that) {
case FetchBookUpcoming():
return fetchBookUpcoming(_that);case SetWalletPrice():
return setWalletPrice(_that);case FetchBookPast():
return fetchBookPast(_that);case FetchBookingById():
return fetchBookingById(_that);case BookingService():
return bookingService(_that);case CalculateBooking():
return calculateBooking(_that);case CheckTime():
return checkTime(_that);case SelectPayment():
return selectPayment(_that);case PayLater():
return payLater(_that);case FetchPayments():
return fetchPayments(_that);case FetchWebView():
return fetchWebView(_that);case CancelBook():
return cancelBook(_that);case SelectDateTime():
return selectDateTime(_that);case SelectBookingTime():
return selectBookingTime(_that);case SetServices():
return setServices(_that);case SelectMaster():
return selectMaster(_that);case SelectTime():
return selectTime(_that);case SelectAddress():
return selectAddress(_that);case SetGiftCart():
return setGiftCart(_that);case SaveForm():
return saveForm(_that);case UpdateNotes():
return updateNotes(_that);case CheckCoupon():
return checkCoupon(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchBookUpcoming value)?  fetchBookUpcoming,TResult? Function( SetWalletPrice value)?  setWalletPrice,TResult? Function( FetchBookPast value)?  fetchBookPast,TResult? Function( FetchBookingById value)?  fetchBookingById,TResult? Function( BookingService value)?  bookingService,TResult? Function( CalculateBooking value)?  calculateBooking,TResult? Function( CheckTime value)?  checkTime,TResult? Function( SelectPayment value)?  selectPayment,TResult? Function( PayLater value)?  payLater,TResult? Function( FetchPayments value)?  fetchPayments,TResult? Function( FetchWebView value)?  fetchWebView,TResult? Function( CancelBook value)?  cancelBook,TResult? Function( SelectDateTime value)?  selectDateTime,TResult? Function( SelectBookingTime value)?  selectBookingTime,TResult? Function( SetServices value)?  setServices,TResult? Function( SelectMaster value)?  selectMaster,TResult? Function( SelectTime value)?  selectTime,TResult? Function( SelectAddress value)?  selectAddress,TResult? Function( SetGiftCart value)?  setGiftCart,TResult? Function( SaveForm value)?  saveForm,TResult? Function( UpdateNotes value)?  updateNotes,TResult? Function( CheckCoupon value)?  checkCoupon,}){
final _that = this;
switch (_that) {
case FetchBookUpcoming() when fetchBookUpcoming != null:
return fetchBookUpcoming(_that);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that);case FetchBookPast() when fetchBookPast != null:
return fetchBookPast(_that);case FetchBookingById() when fetchBookingById != null:
return fetchBookingById(_that);case BookingService() when bookingService != null:
return bookingService(_that);case CalculateBooking() when calculateBooking != null:
return calculateBooking(_that);case CheckTime() when checkTime != null:
return checkTime(_that);case SelectPayment() when selectPayment != null:
return selectPayment(_that);case PayLater() when payLater != null:
return payLater(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that);case CancelBook() when cancelBook != null:
return cancelBook(_that);case SelectDateTime() when selectDateTime != null:
return selectDateTime(_that);case SelectBookingTime() when selectBookingTime != null:
return selectBookingTime(_that);case SetServices() when setServices != null:
return setServices(_that);case SelectMaster() when selectMaster != null:
return selectMaster(_that);case SelectTime() when selectTime != null:
return selectTime(_that);case SelectAddress() when selectAddress != null:
return selectAddress(_that);case SetGiftCart() when setGiftCart != null:
return setGiftCart(_that);case SaveForm() when saveForm != null:
return saveForm(_that);case UpdateNotes() when updateNotes != null:
return updateNotes(_that);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBookUpcoming,TResult Function( num? price)?  setWalletPrice,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBookPast,TResult Function( BuildContext context,  int id)?  fetchBookingById,TResult Function( BuildContext context,  num totalPrice,  ValueChanged<int> onSuccess)?  bookingService,TResult Function( BuildContext context)?  calculateBooking,TResult Function( BuildContext context,  DateTime startTime)?  checkTime,TResult Function( PaymentData payment)?  selectPayment,TResult Function( BuildContext context,  int? id,  num totalPrice,  ValueChanged<int> onSuccess)?  payLater,TResult Function( BuildContext context,  bool payLater)?  fetchPayments,TResult Function( BuildContext context,  int id,  ValueChanged onSuccess,  ValueChanged? onFailure)?  fetchWebView,TResult Function( BuildContext context,  int id,  VoidCallback? onSuccess)?  cancelBook,TResult Function( DateTime selectDateTime)?  selectDateTime,TResult Function( String time)?  selectBookingTime,TResult Function( List<ServiceModel> services,  VoidCallback? onSuccess,  VoidCallback? onFailure,  MasterModel? master)?  setServices,TResult Function( int? serviceId,  MasterModel? master)?  selectMaster,TResult Function( DateTime? selectTime,  int? serviceId)?  selectTime,TResult Function( UserAddress? address)?  selectAddress,TResult Function( MyGiftCartModel? giftCart)?  setGiftCart,TResult Function( BuildContext context,  FormOptionsData? form,  VoidCallback? onSuccess)?  saveForm,TResult Function( BuildContext context,  int id,  String message,  VoidCallback? onSuccess)?  updateNotes,TResult Function( BuildContext context,  String coupon,  int shopId,  bool? clear)?  checkCoupon,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchBookUpcoming() when fetchBookUpcoming != null:
return fetchBookUpcoming(_that.context,_that.isRefresh,_that.controller);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that.price);case FetchBookPast() when fetchBookPast != null:
return fetchBookPast(_that.context,_that.isRefresh,_that.controller);case FetchBookingById() when fetchBookingById != null:
return fetchBookingById(_that.context,_that.id);case BookingService() when bookingService != null:
return bookingService(_that.context,_that.totalPrice,_that.onSuccess);case CalculateBooking() when calculateBooking != null:
return calculateBooking(_that.context);case CheckTime() when checkTime != null:
return checkTime(_that.context,_that.startTime);case SelectPayment() when selectPayment != null:
return selectPayment(_that.payment);case PayLater() when payLater != null:
return payLater(_that.context,_that.id,_that.totalPrice,_that.onSuccess);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context,_that.payLater);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that.context,_that.id,_that.onSuccess,_that.onFailure);case CancelBook() when cancelBook != null:
return cancelBook(_that.context,_that.id,_that.onSuccess);case SelectDateTime() when selectDateTime != null:
return selectDateTime(_that.selectDateTime);case SelectBookingTime() when selectBookingTime != null:
return selectBookingTime(_that.time);case SetServices() when setServices != null:
return setServices(_that.services,_that.onSuccess,_that.onFailure,_that.master);case SelectMaster() when selectMaster != null:
return selectMaster(_that.serviceId,_that.master);case SelectTime() when selectTime != null:
return selectTime(_that.selectTime,_that.serviceId);case SelectAddress() when selectAddress != null:
return selectAddress(_that.address);case SetGiftCart() when setGiftCart != null:
return setGiftCart(_that.giftCart);case SaveForm() when saveForm != null:
return saveForm(_that.context,_that.form,_that.onSuccess);case UpdateNotes() when updateNotes != null:
return updateNotes(_that.context,_that.id,_that.message,_that.onSuccess);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that.context,_that.coupon,_that.shopId,_that.clear);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchBookUpcoming,required TResult Function( num? price)  setWalletPrice,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchBookPast,required TResult Function( BuildContext context,  int id)  fetchBookingById,required TResult Function( BuildContext context,  num totalPrice,  ValueChanged<int> onSuccess)  bookingService,required TResult Function( BuildContext context)  calculateBooking,required TResult Function( BuildContext context,  DateTime startTime)  checkTime,required TResult Function( PaymentData payment)  selectPayment,required TResult Function( BuildContext context,  int? id,  num totalPrice,  ValueChanged<int> onSuccess)  payLater,required TResult Function( BuildContext context,  bool payLater)  fetchPayments,required TResult Function( BuildContext context,  int id,  ValueChanged onSuccess,  ValueChanged? onFailure)  fetchWebView,required TResult Function( BuildContext context,  int id,  VoidCallback? onSuccess)  cancelBook,required TResult Function( DateTime selectDateTime)  selectDateTime,required TResult Function( String time)  selectBookingTime,required TResult Function( List<ServiceModel> services,  VoidCallback? onSuccess,  VoidCallback? onFailure,  MasterModel? master)  setServices,required TResult Function( int? serviceId,  MasterModel? master)  selectMaster,required TResult Function( DateTime? selectTime,  int? serviceId)  selectTime,required TResult Function( UserAddress? address)  selectAddress,required TResult Function( MyGiftCartModel? giftCart)  setGiftCart,required TResult Function( BuildContext context,  FormOptionsData? form,  VoidCallback? onSuccess)  saveForm,required TResult Function( BuildContext context,  int id,  String message,  VoidCallback? onSuccess)  updateNotes,required TResult Function( BuildContext context,  String coupon,  int shopId,  bool? clear)  checkCoupon,}) {final _that = this;
switch (_that) {
case FetchBookUpcoming():
return fetchBookUpcoming(_that.context,_that.isRefresh,_that.controller);case SetWalletPrice():
return setWalletPrice(_that.price);case FetchBookPast():
return fetchBookPast(_that.context,_that.isRefresh,_that.controller);case FetchBookingById():
return fetchBookingById(_that.context,_that.id);case BookingService():
return bookingService(_that.context,_that.totalPrice,_that.onSuccess);case CalculateBooking():
return calculateBooking(_that.context);case CheckTime():
return checkTime(_that.context,_that.startTime);case SelectPayment():
return selectPayment(_that.payment);case PayLater():
return payLater(_that.context,_that.id,_that.totalPrice,_that.onSuccess);case FetchPayments():
return fetchPayments(_that.context,_that.payLater);case FetchWebView():
return fetchWebView(_that.context,_that.id,_that.onSuccess,_that.onFailure);case CancelBook():
return cancelBook(_that.context,_that.id,_that.onSuccess);case SelectDateTime():
return selectDateTime(_that.selectDateTime);case SelectBookingTime():
return selectBookingTime(_that.time);case SetServices():
return setServices(_that.services,_that.onSuccess,_that.onFailure,_that.master);case SelectMaster():
return selectMaster(_that.serviceId,_that.master);case SelectTime():
return selectTime(_that.selectTime,_that.serviceId);case SelectAddress():
return selectAddress(_that.address);case SetGiftCart():
return setGiftCart(_that.giftCart);case SaveForm():
return saveForm(_that.context,_that.form,_that.onSuccess);case UpdateNotes():
return updateNotes(_that.context,_that.id,_that.message,_that.onSuccess);case CheckCoupon():
return checkCoupon(_that.context,_that.coupon,_that.shopId,_that.clear);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBookUpcoming,TResult? Function( num? price)?  setWalletPrice,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBookPast,TResult? Function( BuildContext context,  int id)?  fetchBookingById,TResult? Function( BuildContext context,  num totalPrice,  ValueChanged<int> onSuccess)?  bookingService,TResult? Function( BuildContext context)?  calculateBooking,TResult? Function( BuildContext context,  DateTime startTime)?  checkTime,TResult? Function( PaymentData payment)?  selectPayment,TResult? Function( BuildContext context,  int? id,  num totalPrice,  ValueChanged<int> onSuccess)?  payLater,TResult? Function( BuildContext context,  bool payLater)?  fetchPayments,TResult? Function( BuildContext context,  int id,  ValueChanged onSuccess,  ValueChanged? onFailure)?  fetchWebView,TResult? Function( BuildContext context,  int id,  VoidCallback? onSuccess)?  cancelBook,TResult? Function( DateTime selectDateTime)?  selectDateTime,TResult? Function( String time)?  selectBookingTime,TResult? Function( List<ServiceModel> services,  VoidCallback? onSuccess,  VoidCallback? onFailure,  MasterModel? master)?  setServices,TResult? Function( int? serviceId,  MasterModel? master)?  selectMaster,TResult? Function( DateTime? selectTime,  int? serviceId)?  selectTime,TResult? Function( UserAddress? address)?  selectAddress,TResult? Function( MyGiftCartModel? giftCart)?  setGiftCart,TResult? Function( BuildContext context,  FormOptionsData? form,  VoidCallback? onSuccess)?  saveForm,TResult? Function( BuildContext context,  int id,  String message,  VoidCallback? onSuccess)?  updateNotes,TResult? Function( BuildContext context,  String coupon,  int shopId,  bool? clear)?  checkCoupon,}) {final _that = this;
switch (_that) {
case FetchBookUpcoming() when fetchBookUpcoming != null:
return fetchBookUpcoming(_that.context,_that.isRefresh,_that.controller);case SetWalletPrice() when setWalletPrice != null:
return setWalletPrice(_that.price);case FetchBookPast() when fetchBookPast != null:
return fetchBookPast(_that.context,_that.isRefresh,_that.controller);case FetchBookingById() when fetchBookingById != null:
return fetchBookingById(_that.context,_that.id);case BookingService() when bookingService != null:
return bookingService(_that.context,_that.totalPrice,_that.onSuccess);case CalculateBooking() when calculateBooking != null:
return calculateBooking(_that.context);case CheckTime() when checkTime != null:
return checkTime(_that.context,_that.startTime);case SelectPayment() when selectPayment != null:
return selectPayment(_that.payment);case PayLater() when payLater != null:
return payLater(_that.context,_that.id,_that.totalPrice,_that.onSuccess);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context,_that.payLater);case FetchWebView() when fetchWebView != null:
return fetchWebView(_that.context,_that.id,_that.onSuccess,_that.onFailure);case CancelBook() when cancelBook != null:
return cancelBook(_that.context,_that.id,_that.onSuccess);case SelectDateTime() when selectDateTime != null:
return selectDateTime(_that.selectDateTime);case SelectBookingTime() when selectBookingTime != null:
return selectBookingTime(_that.time);case SetServices() when setServices != null:
return setServices(_that.services,_that.onSuccess,_that.onFailure,_that.master);case SelectMaster() when selectMaster != null:
return selectMaster(_that.serviceId,_that.master);case SelectTime() when selectTime != null:
return selectTime(_that.selectTime,_that.serviceId);case SelectAddress() when selectAddress != null:
return selectAddress(_that.address);case SetGiftCart() when setGiftCart != null:
return setGiftCart(_that.giftCart);case SaveForm() when saveForm != null:
return saveForm(_that.context,_that.form,_that.onSuccess);case UpdateNotes() when updateNotes != null:
return updateNotes(_that.context,_that.id,_that.message,_that.onSuccess);case CheckCoupon() when checkCoupon != null:
return checkCoupon(_that.context,_that.coupon,_that.shopId,_that.clear);case _:
  return null;

}
}

}

/// @nodoc


class FetchBookUpcoming implements BookingEvent {
  const FetchBookUpcoming(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchBookUpcomingCopyWith<FetchBookUpcoming> get copyWith => _$FetchBookUpcomingCopyWithImpl<FetchBookUpcoming>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchBookUpcoming&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'BookingEvent.fetchBookUpcoming(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchBookUpcomingCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $FetchBookUpcomingCopyWith(FetchBookUpcoming value, $Res Function(FetchBookUpcoming) _then) = _$FetchBookUpcomingCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchBookUpcomingCopyWithImpl<$Res>
    implements $FetchBookUpcomingCopyWith<$Res> {
  _$FetchBookUpcomingCopyWithImpl(this._self, this._then);

  final FetchBookUpcoming _self;
  final $Res Function(FetchBookUpcoming) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchBookUpcoming(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class SetWalletPrice implements BookingEvent {
  const SetWalletPrice({this.price});
  

 final  num? price;

/// Create a copy of BookingEvent
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
  return 'BookingEvent.setWalletPrice(price: $price)';
}


}

/// @nodoc
abstract mixin class $SetWalletPriceCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
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

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? price = freezed,}) {
  return _then(SetWalletPrice(
price: freezed == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as num?,
  ));
}


}

/// @nodoc


class FetchBookPast implements BookingEvent {
  const FetchBookPast(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchBookPastCopyWith<FetchBookPast> get copyWith => _$FetchBookPastCopyWithImpl<FetchBookPast>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchBookPast&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'BookingEvent.fetchBookPast(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchBookPastCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $FetchBookPastCopyWith(FetchBookPast value, $Res Function(FetchBookPast) _then) = _$FetchBookPastCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchBookPastCopyWithImpl<$Res>
    implements $FetchBookPastCopyWith<$Res> {
  _$FetchBookPastCopyWithImpl(this._self, this._then);

  final FetchBookPast _self;
  final $Res Function(FetchBookPast) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchBookPast(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchBookingById implements BookingEvent {
  const FetchBookingById(this.context, {required this.id});
  

 final  BuildContext context;
 final  int id;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchBookingByIdCopyWith<FetchBookingById> get copyWith => _$FetchBookingByIdCopyWithImpl<FetchBookingById>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchBookingById&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,context,id);

@override
String toString() {
  return 'BookingEvent.fetchBookingById(context: $context, id: $id)';
}


}

/// @nodoc
abstract mixin class $FetchBookingByIdCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $FetchBookingByIdCopyWith(FetchBookingById value, $Res Function(FetchBookingById) _then) = _$FetchBookingByIdCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int id
});




}
/// @nodoc
class _$FetchBookingByIdCopyWithImpl<$Res>
    implements $FetchBookingByIdCopyWith<$Res> {
  _$FetchBookingByIdCopyWithImpl(this._self, this._then);

  final FetchBookingById _self;
  final $Res Function(FetchBookingById) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,}) {
  return _then(FetchBookingById(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class BookingService implements BookingEvent {
  const BookingService(this.context, {required this.totalPrice, required this.onSuccess});
  

 final  BuildContext context;
 final  num totalPrice;
 final  ValueChanged<int> onSuccess;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BookingServiceCopyWith<BookingService> get copyWith => _$BookingServiceCopyWithImpl<BookingService>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BookingService&&(identical(other.context, context) || other.context == context)&&(identical(other.totalPrice, totalPrice) || other.totalPrice == totalPrice)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,totalPrice,onSuccess);

@override
String toString() {
  return 'BookingEvent.bookingService(context: $context, totalPrice: $totalPrice, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $BookingServiceCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $BookingServiceCopyWith(BookingService value, $Res Function(BookingService) _then) = _$BookingServiceCopyWithImpl;
@useResult
$Res call({
 BuildContext context, num totalPrice, ValueChanged<int> onSuccess
});




}
/// @nodoc
class _$BookingServiceCopyWithImpl<$Res>
    implements $BookingServiceCopyWith<$Res> {
  _$BookingServiceCopyWithImpl(this._self, this._then);

  final BookingService _self;
  final $Res Function(BookingService) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? totalPrice = null,Object? onSuccess = null,}) {
  return _then(BookingService(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,totalPrice: null == totalPrice ? _self.totalPrice : totalPrice // ignore: cast_nullable_to_non_nullable
as num,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as ValueChanged<int>,
  ));
}


}

/// @nodoc


class CalculateBooking implements BookingEvent {
  const CalculateBooking(this.context);
  

 final  BuildContext context;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CalculateBookingCopyWith<CalculateBooking> get copyWith => _$CalculateBookingCopyWithImpl<CalculateBooking>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CalculateBooking&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'BookingEvent.calculateBooking(context: $context)';
}


}

/// @nodoc
abstract mixin class $CalculateBookingCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $CalculateBookingCopyWith(CalculateBooking value, $Res Function(CalculateBooking) _then) = _$CalculateBookingCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$CalculateBookingCopyWithImpl<$Res>
    implements $CalculateBookingCopyWith<$Res> {
  _$CalculateBookingCopyWithImpl(this._self, this._then);

  final CalculateBooking _self;
  final $Res Function(CalculateBooking) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(CalculateBooking(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class CheckTime implements BookingEvent {
  const CheckTime(this.context, {required this.startTime});
  

 final  BuildContext context;
 final  DateTime startTime;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CheckTimeCopyWith<CheckTime> get copyWith => _$CheckTimeCopyWithImpl<CheckTime>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckTime&&(identical(other.context, context) || other.context == context)&&(identical(other.startTime, startTime) || other.startTime == startTime));
}


@override
int get hashCode => Object.hash(runtimeType,context,startTime);

@override
String toString() {
  return 'BookingEvent.checkTime(context: $context, startTime: $startTime)';
}


}

/// @nodoc
abstract mixin class $CheckTimeCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $CheckTimeCopyWith(CheckTime value, $Res Function(CheckTime) _then) = _$CheckTimeCopyWithImpl;
@useResult
$Res call({
 BuildContext context, DateTime startTime
});




}
/// @nodoc
class _$CheckTimeCopyWithImpl<$Res>
    implements $CheckTimeCopyWith<$Res> {
  _$CheckTimeCopyWithImpl(this._self, this._then);

  final CheckTime _self;
  final $Res Function(CheckTime) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? startTime = null,}) {
  return _then(CheckTime(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,startTime: null == startTime ? _self.startTime : startTime // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

/// @nodoc


class SelectPayment implements BookingEvent {
  const SelectPayment({required this.payment});
  

 final  PaymentData payment;

/// Create a copy of BookingEvent
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
  return 'BookingEvent.selectPayment(payment: $payment)';
}


}

/// @nodoc
abstract mixin class $SelectPaymentCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
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

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? payment = null,}) {
  return _then(SelectPayment(
payment: null == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as PaymentData,
  ));
}


}

/// @nodoc


class PayLater implements BookingEvent {
  const PayLater(this.context, {required this.id, required this.totalPrice, required this.onSuccess});
  

 final  BuildContext context;
 final  int? id;
 final  num totalPrice;
 final  ValueChanged<int> onSuccess;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PayLaterCopyWith<PayLater> get copyWith => _$PayLaterCopyWithImpl<PayLater>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PayLater&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.totalPrice, totalPrice) || other.totalPrice == totalPrice)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,totalPrice,onSuccess);

@override
String toString() {
  return 'BookingEvent.payLater(context: $context, id: $id, totalPrice: $totalPrice, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $PayLaterCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $PayLaterCopyWith(PayLater value, $Res Function(PayLater) _then) = _$PayLaterCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? id, num totalPrice, ValueChanged<int> onSuccess
});




}
/// @nodoc
class _$PayLaterCopyWithImpl<$Res>
    implements $PayLaterCopyWith<$Res> {
  _$PayLaterCopyWithImpl(this._self, this._then);

  final PayLater _self;
  final $Res Function(PayLater) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = freezed,Object? totalPrice = null,Object? onSuccess = null,}) {
  return _then(PayLater(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: freezed == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int?,totalPrice: null == totalPrice ? _self.totalPrice : totalPrice // ignore: cast_nullable_to_non_nullable
as num,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as ValueChanged<int>,
  ));
}


}

/// @nodoc


class FetchPayments implements BookingEvent {
  const FetchPayments(this.context, {required this.payLater});
  

 final  BuildContext context;
 final  bool payLater;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchPaymentsCopyWith<FetchPayments> get copyWith => _$FetchPaymentsCopyWithImpl<FetchPayments>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchPayments&&(identical(other.context, context) || other.context == context)&&(identical(other.payLater, payLater) || other.payLater == payLater));
}


@override
int get hashCode => Object.hash(runtimeType,context,payLater);

@override
String toString() {
  return 'BookingEvent.fetchPayments(context: $context, payLater: $payLater)';
}


}

/// @nodoc
abstract mixin class $FetchPaymentsCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $FetchPaymentsCopyWith(FetchPayments value, $Res Function(FetchPayments) _then) = _$FetchPaymentsCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool payLater
});




}
/// @nodoc
class _$FetchPaymentsCopyWithImpl<$Res>
    implements $FetchPaymentsCopyWith<$Res> {
  _$FetchPaymentsCopyWithImpl(this._self, this._then);

  final FetchPayments _self;
  final $Res Function(FetchPayments) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? payLater = null,}) {
  return _then(FetchPayments(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,payLater: null == payLater ? _self.payLater : payLater // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc


class FetchWebView implements BookingEvent {
  const FetchWebView(this.context, {required this.id, required this.onSuccess, this.onFailure});
  

 final  BuildContext context;
 final  int id;
 final  ValueChanged onSuccess;
 final  ValueChanged? onFailure;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchWebViewCopyWith<FetchWebView> get copyWith => _$FetchWebViewCopyWithImpl<FetchWebView>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchWebView&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess)&&(identical(other.onFailure, onFailure) || other.onFailure == onFailure));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,onSuccess,onFailure);

@override
String toString() {
  return 'BookingEvent.fetchWebView(context: $context, id: $id, onSuccess: $onSuccess, onFailure: $onFailure)';
}


}

/// @nodoc
abstract mixin class $FetchWebViewCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $FetchWebViewCopyWith(FetchWebView value, $Res Function(FetchWebView) _then) = _$FetchWebViewCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int id, ValueChanged onSuccess, ValueChanged? onFailure
});




}
/// @nodoc
class _$FetchWebViewCopyWithImpl<$Res>
    implements $FetchWebViewCopyWith<$Res> {
  _$FetchWebViewCopyWithImpl(this._self, this._then);

  final FetchWebView _self;
  final $Res Function(FetchWebView) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? onSuccess = null,Object? onFailure = freezed,}) {
  return _then(FetchWebView(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as ValueChanged,onFailure: freezed == onFailure ? _self.onFailure : onFailure // ignore: cast_nullable_to_non_nullable
as ValueChanged?,
  ));
}


}

/// @nodoc


class CancelBook implements BookingEvent {
  const CancelBook(this.context, {required this.id, this.onSuccess});
  

 final  BuildContext context;
 final  int id;
 final  VoidCallback? onSuccess;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CancelBookCopyWith<CancelBook> get copyWith => _$CancelBookCopyWithImpl<CancelBook>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CancelBook&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,onSuccess);

@override
String toString() {
  return 'BookingEvent.cancelBook(context: $context, id: $id, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $CancelBookCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $CancelBookCopyWith(CancelBook value, $Res Function(CancelBook) _then) = _$CancelBookCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int id, VoidCallback? onSuccess
});




}
/// @nodoc
class _$CancelBookCopyWithImpl<$Res>
    implements $CancelBookCopyWith<$Res> {
  _$CancelBookCopyWithImpl(this._self, this._then);

  final CancelBook _self;
  final $Res Function(CancelBook) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? onSuccess = freezed,}) {
  return _then(CancelBook(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class SelectDateTime implements BookingEvent {
  const SelectDateTime({required this.selectDateTime});
  

 final  DateTime selectDateTime;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectDateTimeCopyWith<SelectDateTime> get copyWith => _$SelectDateTimeCopyWithImpl<SelectDateTime>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectDateTime&&(identical(other.selectDateTime, selectDateTime) || other.selectDateTime == selectDateTime));
}


@override
int get hashCode => Object.hash(runtimeType,selectDateTime);

@override
String toString() {
  return 'BookingEvent.selectDateTime(selectDateTime: $selectDateTime)';
}


}

/// @nodoc
abstract mixin class $SelectDateTimeCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SelectDateTimeCopyWith(SelectDateTime value, $Res Function(SelectDateTime) _then) = _$SelectDateTimeCopyWithImpl;
@useResult
$Res call({
 DateTime selectDateTime
});




}
/// @nodoc
class _$SelectDateTimeCopyWithImpl<$Res>
    implements $SelectDateTimeCopyWith<$Res> {
  _$SelectDateTimeCopyWithImpl(this._self, this._then);

  final SelectDateTime _self;
  final $Res Function(SelectDateTime) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? selectDateTime = null,}) {
  return _then(SelectDateTime(
selectDateTime: null == selectDateTime ? _self.selectDateTime : selectDateTime // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

/// @nodoc


class SelectBookingTime implements BookingEvent {
  const SelectBookingTime({required this.time});
  

 final  String time;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectBookingTimeCopyWith<SelectBookingTime> get copyWith => _$SelectBookingTimeCopyWithImpl<SelectBookingTime>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectBookingTime&&(identical(other.time, time) || other.time == time));
}


@override
int get hashCode => Object.hash(runtimeType,time);

@override
String toString() {
  return 'BookingEvent.selectBookingTime(time: $time)';
}


}

/// @nodoc
abstract mixin class $SelectBookingTimeCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SelectBookingTimeCopyWith(SelectBookingTime value, $Res Function(SelectBookingTime) _then) = _$SelectBookingTimeCopyWithImpl;
@useResult
$Res call({
 String time
});




}
/// @nodoc
class _$SelectBookingTimeCopyWithImpl<$Res>
    implements $SelectBookingTimeCopyWith<$Res> {
  _$SelectBookingTimeCopyWithImpl(this._self, this._then);

  final SelectBookingTime _self;
  final $Res Function(SelectBookingTime) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? time = null,}) {
  return _then(SelectBookingTime(
time: null == time ? _self.time : time // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class SetServices implements BookingEvent {
  const SetServices({required final  List<ServiceModel> services, this.onSuccess, this.onFailure, this.master}): _services = services;
  

 final  List<ServiceModel> _services;
 List<ServiceModel> get services {
  if (_services is EqualUnmodifiableListView) return _services;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_services);
}

 final  VoidCallback? onSuccess;
 final  VoidCallback? onFailure;
 final  MasterModel? master;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetServicesCopyWith<SetServices> get copyWith => _$SetServicesCopyWithImpl<SetServices>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetServices&&const DeepCollectionEquality().equals(other._services, _services)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess)&&(identical(other.onFailure, onFailure) || other.onFailure == onFailure)&&(identical(other.master, master) || other.master == master));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_services),onSuccess,onFailure,master);

@override
String toString() {
  return 'BookingEvent.setServices(services: $services, onSuccess: $onSuccess, onFailure: $onFailure, master: $master)';
}


}

/// @nodoc
abstract mixin class $SetServicesCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SetServicesCopyWith(SetServices value, $Res Function(SetServices) _then) = _$SetServicesCopyWithImpl;
@useResult
$Res call({
 List<ServiceModel> services, VoidCallback? onSuccess, VoidCallback? onFailure, MasterModel? master
});




}
/// @nodoc
class _$SetServicesCopyWithImpl<$Res>
    implements $SetServicesCopyWith<$Res> {
  _$SetServicesCopyWithImpl(this._self, this._then);

  final SetServices _self;
  final $Res Function(SetServices) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? services = null,Object? onSuccess = freezed,Object? onFailure = freezed,Object? master = freezed,}) {
  return _then(SetServices(
services: null == services ? _self._services : services // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,onFailure: freezed == onFailure ? _self.onFailure : onFailure // ignore: cast_nullable_to_non_nullable
as VoidCallback?,master: freezed == master ? _self.master : master // ignore: cast_nullable_to_non_nullable
as MasterModel?,
  ));
}


}

/// @nodoc


class SelectMaster implements BookingEvent {
  const SelectMaster({required this.serviceId, required this.master});
  

 final  int? serviceId;
 final  MasterModel? master;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectMasterCopyWith<SelectMaster> get copyWith => _$SelectMasterCopyWithImpl<SelectMaster>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectMaster&&(identical(other.serviceId, serviceId) || other.serviceId == serviceId)&&(identical(other.master, master) || other.master == master));
}


@override
int get hashCode => Object.hash(runtimeType,serviceId,master);

@override
String toString() {
  return 'BookingEvent.selectMaster(serviceId: $serviceId, master: $master)';
}


}

/// @nodoc
abstract mixin class $SelectMasterCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SelectMasterCopyWith(SelectMaster value, $Res Function(SelectMaster) _then) = _$SelectMasterCopyWithImpl;
@useResult
$Res call({
 int? serviceId, MasterModel? master
});




}
/// @nodoc
class _$SelectMasterCopyWithImpl<$Res>
    implements $SelectMasterCopyWith<$Res> {
  _$SelectMasterCopyWithImpl(this._self, this._then);

  final SelectMaster _self;
  final $Res Function(SelectMaster) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? serviceId = freezed,Object? master = freezed,}) {
  return _then(SelectMaster(
serviceId: freezed == serviceId ? _self.serviceId : serviceId // ignore: cast_nullable_to_non_nullable
as int?,master: freezed == master ? _self.master : master // ignore: cast_nullable_to_non_nullable
as MasterModel?,
  ));
}


}

/// @nodoc


class SelectTime implements BookingEvent {
  const SelectTime({required this.selectTime, required this.serviceId});
  

 final  DateTime? selectTime;
 final  int? serviceId;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectTimeCopyWith<SelectTime> get copyWith => _$SelectTimeCopyWithImpl<SelectTime>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectTime&&(identical(other.selectTime, selectTime) || other.selectTime == selectTime)&&(identical(other.serviceId, serviceId) || other.serviceId == serviceId));
}


@override
int get hashCode => Object.hash(runtimeType,selectTime,serviceId);

@override
String toString() {
  return 'BookingEvent.selectTime(selectTime: $selectTime, serviceId: $serviceId)';
}


}

/// @nodoc
abstract mixin class $SelectTimeCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SelectTimeCopyWith(SelectTime value, $Res Function(SelectTime) _then) = _$SelectTimeCopyWithImpl;
@useResult
$Res call({
 DateTime? selectTime, int? serviceId
});




}
/// @nodoc
class _$SelectTimeCopyWithImpl<$Res>
    implements $SelectTimeCopyWith<$Res> {
  _$SelectTimeCopyWithImpl(this._self, this._then);

  final SelectTime _self;
  final $Res Function(SelectTime) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? selectTime = freezed,Object? serviceId = freezed,}) {
  return _then(SelectTime(
selectTime: freezed == selectTime ? _self.selectTime : selectTime // ignore: cast_nullable_to_non_nullable
as DateTime?,serviceId: freezed == serviceId ? _self.serviceId : serviceId // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class SelectAddress implements BookingEvent {
  const SelectAddress({required this.address});
  

 final  UserAddress? address;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectAddressCopyWith<SelectAddress> get copyWith => _$SelectAddressCopyWithImpl<SelectAddress>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectAddress&&(identical(other.address, address) || other.address == address));
}


@override
int get hashCode => Object.hash(runtimeType,address);

@override
String toString() {
  return 'BookingEvent.selectAddress(address: $address)';
}


}

/// @nodoc
abstract mixin class $SelectAddressCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SelectAddressCopyWith(SelectAddress value, $Res Function(SelectAddress) _then) = _$SelectAddressCopyWithImpl;
@useResult
$Res call({
 UserAddress? address
});




}
/// @nodoc
class _$SelectAddressCopyWithImpl<$Res>
    implements $SelectAddressCopyWith<$Res> {
  _$SelectAddressCopyWithImpl(this._self, this._then);

  final SelectAddress _self;
  final $Res Function(SelectAddress) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? address = freezed,}) {
  return _then(SelectAddress(
address: freezed == address ? _self.address : address // ignore: cast_nullable_to_non_nullable
as UserAddress?,
  ));
}


}

/// @nodoc


class SetGiftCart implements BookingEvent {
  const SetGiftCart({required this.giftCart});
  

 final  MyGiftCartModel? giftCart;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetGiftCartCopyWith<SetGiftCart> get copyWith => _$SetGiftCartCopyWithImpl<SetGiftCart>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetGiftCart&&(identical(other.giftCart, giftCart) || other.giftCart == giftCart));
}


@override
int get hashCode => Object.hash(runtimeType,giftCart);

@override
String toString() {
  return 'BookingEvent.setGiftCart(giftCart: $giftCart)';
}


}

/// @nodoc
abstract mixin class $SetGiftCartCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SetGiftCartCopyWith(SetGiftCart value, $Res Function(SetGiftCart) _then) = _$SetGiftCartCopyWithImpl;
@useResult
$Res call({
 MyGiftCartModel? giftCart
});




}
/// @nodoc
class _$SetGiftCartCopyWithImpl<$Res>
    implements $SetGiftCartCopyWith<$Res> {
  _$SetGiftCartCopyWithImpl(this._self, this._then);

  final SetGiftCart _self;
  final $Res Function(SetGiftCart) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? giftCart = freezed,}) {
  return _then(SetGiftCart(
giftCart: freezed == giftCart ? _self.giftCart : giftCart // ignore: cast_nullable_to_non_nullable
as MyGiftCartModel?,
  ));
}


}

/// @nodoc


class SaveForm implements BookingEvent {
  const SaveForm(this.context, {required this.form, this.onSuccess});
  

 final  BuildContext context;
 final  FormOptionsData? form;
 final  VoidCallback? onSuccess;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SaveFormCopyWith<SaveForm> get copyWith => _$SaveFormCopyWithImpl<SaveForm>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SaveForm&&(identical(other.context, context) || other.context == context)&&(identical(other.form, form) || other.form == form)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,form,onSuccess);

@override
String toString() {
  return 'BookingEvent.saveForm(context: $context, form: $form, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $SaveFormCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $SaveFormCopyWith(SaveForm value, $Res Function(SaveForm) _then) = _$SaveFormCopyWithImpl;
@useResult
$Res call({
 BuildContext context, FormOptionsData? form, VoidCallback? onSuccess
});




}
/// @nodoc
class _$SaveFormCopyWithImpl<$Res>
    implements $SaveFormCopyWith<$Res> {
  _$SaveFormCopyWithImpl(this._self, this._then);

  final SaveForm _self;
  final $Res Function(SaveForm) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? form = freezed,Object? onSuccess = freezed,}) {
  return _then(SaveForm(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,form: freezed == form ? _self.form : form // ignore: cast_nullable_to_non_nullable
as FormOptionsData?,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class UpdateNotes implements BookingEvent {
  const UpdateNotes(this.context, {required this.id, required this.message, this.onSuccess});
  

 final  BuildContext context;
 final  int id;
 final  String message;
 final  VoidCallback? onSuccess;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UpdateNotesCopyWith<UpdateNotes> get copyWith => _$UpdateNotesCopyWithImpl<UpdateNotes>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateNotes&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.message, message) || other.message == message)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,message,onSuccess);

@override
String toString() {
  return 'BookingEvent.updateNotes(context: $context, id: $id, message: $message, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $UpdateNotesCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
  factory $UpdateNotesCopyWith(UpdateNotes value, $Res Function(UpdateNotes) _then) = _$UpdateNotesCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int id, String message, VoidCallback? onSuccess
});




}
/// @nodoc
class _$UpdateNotesCopyWithImpl<$Res>
    implements $UpdateNotesCopyWith<$Res> {
  _$UpdateNotesCopyWithImpl(this._self, this._then);

  final UpdateNotes _self;
  final $Res Function(UpdateNotes) _then;

/// Create a copy of BookingEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? message = null,Object? onSuccess = freezed,}) {
  return _then(UpdateNotes(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,message: null == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class CheckCoupon implements BookingEvent {
  const CheckCoupon(this.context, {required this.coupon, required this.shopId, this.clear});
  

 final  BuildContext context;
 final  String coupon;
 final  int shopId;
 final  bool? clear;

/// Create a copy of BookingEvent
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
  return 'BookingEvent.checkCoupon(context: $context, coupon: $coupon, shopId: $shopId, clear: $clear)';
}


}

/// @nodoc
abstract mixin class $CheckCouponCopyWith<$Res> implements $BookingEventCopyWith<$Res> {
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

/// Create a copy of BookingEvent
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
mixin _$BookingState {

 List<BookingModel> get upcoming; List<BookingModel> get past; List<PaymentData> get payments; List<CheckData>? get listDate; bool get isLoading; bool get isLoadingMaster; String? get coupon; bool get isButtonLoading; bool get isPaymentLoading; PaymentData? get selectedPayment; BookingCalculateResponse? get calculate; DateTime? get selectDateTime; String? get selectBookTime; List<ServiceModel> get selectServices; Map<int, MasterModel> get selectMasters; MyGiftCartModel? get giftCart; num? get walletPrice;
/// Create a copy of BookingState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BookingStateCopyWith<BookingState> get copyWith => _$BookingStateCopyWithImpl<BookingState>(this as BookingState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BookingState&&const DeepCollectionEquality().equals(other.upcoming, upcoming)&&const DeepCollectionEquality().equals(other.past, past)&&const DeepCollectionEquality().equals(other.payments, payments)&&const DeepCollectionEquality().equals(other.listDate, listDate)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isLoadingMaster, isLoadingMaster) || other.isLoadingMaster == isLoadingMaster)&&(identical(other.coupon, coupon) || other.coupon == coupon)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isPaymentLoading, isPaymentLoading) || other.isPaymentLoading == isPaymentLoading)&&(identical(other.selectedPayment, selectedPayment) || other.selectedPayment == selectedPayment)&&(identical(other.calculate, calculate) || other.calculate == calculate)&&(identical(other.selectDateTime, selectDateTime) || other.selectDateTime == selectDateTime)&&(identical(other.selectBookTime, selectBookTime) || other.selectBookTime == selectBookTime)&&const DeepCollectionEquality().equals(other.selectServices, selectServices)&&const DeepCollectionEquality().equals(other.selectMasters, selectMasters)&&(identical(other.giftCart, giftCart) || other.giftCart == giftCart)&&(identical(other.walletPrice, walletPrice) || other.walletPrice == walletPrice));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(upcoming),const DeepCollectionEquality().hash(past),const DeepCollectionEquality().hash(payments),const DeepCollectionEquality().hash(listDate),isLoading,isLoadingMaster,coupon,isButtonLoading,isPaymentLoading,selectedPayment,calculate,selectDateTime,selectBookTime,const DeepCollectionEquality().hash(selectServices),const DeepCollectionEquality().hash(selectMasters),giftCart,walletPrice);

@override
String toString() {
  return 'BookingState(upcoming: $upcoming, past: $past, payments: $payments, listDate: $listDate, isLoading: $isLoading, isLoadingMaster: $isLoadingMaster, coupon: $coupon, isButtonLoading: $isButtonLoading, isPaymentLoading: $isPaymentLoading, selectedPayment: $selectedPayment, calculate: $calculate, selectDateTime: $selectDateTime, selectBookTime: $selectBookTime, selectServices: $selectServices, selectMasters: $selectMasters, giftCart: $giftCart, walletPrice: $walletPrice)';
}


}

/// @nodoc
abstract mixin class $BookingStateCopyWith<$Res>  {
  factory $BookingStateCopyWith(BookingState value, $Res Function(BookingState) _then) = _$BookingStateCopyWithImpl;
@useResult
$Res call({
 List<BookingModel> upcoming, List<BookingModel> past, List<PaymentData> payments, List<CheckData>? listDate, bool isLoading, bool isLoadingMaster, String? coupon, bool isButtonLoading, bool isPaymentLoading, PaymentData? selectedPayment, BookingCalculateResponse? calculate, DateTime? selectDateTime, String? selectBookTime, List<ServiceModel> selectServices, Map<int, MasterModel> selectMasters, MyGiftCartModel? giftCart, num? walletPrice
});




}
/// @nodoc
class _$BookingStateCopyWithImpl<$Res>
    implements $BookingStateCopyWith<$Res> {
  _$BookingStateCopyWithImpl(this._self, this._then);

  final BookingState _self;
  final $Res Function(BookingState) _then;

/// Create a copy of BookingState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? upcoming = null,Object? past = null,Object? payments = null,Object? listDate = freezed,Object? isLoading = null,Object? isLoadingMaster = null,Object? coupon = freezed,Object? isButtonLoading = null,Object? isPaymentLoading = null,Object? selectedPayment = freezed,Object? calculate = freezed,Object? selectDateTime = freezed,Object? selectBookTime = freezed,Object? selectServices = null,Object? selectMasters = null,Object? giftCart = freezed,Object? walletPrice = freezed,}) {
  return _then(_self.copyWith(
upcoming: null == upcoming ? _self.upcoming : upcoming // ignore: cast_nullable_to_non_nullable
as List<BookingModel>,past: null == past ? _self.past : past // ignore: cast_nullable_to_non_nullable
as List<BookingModel>,payments: null == payments ? _self.payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,listDate: freezed == listDate ? _self.listDate : listDate // ignore: cast_nullable_to_non_nullable
as List<CheckData>?,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isLoadingMaster: null == isLoadingMaster ? _self.isLoadingMaster : isLoadingMaster // ignore: cast_nullable_to_non_nullable
as bool,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as String?,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isPaymentLoading: null == isPaymentLoading ? _self.isPaymentLoading : isPaymentLoading // ignore: cast_nullable_to_non_nullable
as bool,selectedPayment: freezed == selectedPayment ? _self.selectedPayment : selectedPayment // ignore: cast_nullable_to_non_nullable
as PaymentData?,calculate: freezed == calculate ? _self.calculate : calculate // ignore: cast_nullable_to_non_nullable
as BookingCalculateResponse?,selectDateTime: freezed == selectDateTime ? _self.selectDateTime : selectDateTime // ignore: cast_nullable_to_non_nullable
as DateTime?,selectBookTime: freezed == selectBookTime ? _self.selectBookTime : selectBookTime // ignore: cast_nullable_to_non_nullable
as String?,selectServices: null == selectServices ? _self.selectServices : selectServices // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,selectMasters: null == selectMasters ? _self.selectMasters : selectMasters // ignore: cast_nullable_to_non_nullable
as Map<int, MasterModel>,giftCart: freezed == giftCart ? _self.giftCart : giftCart // ignore: cast_nullable_to_non_nullable
as MyGiftCartModel?,walletPrice: freezed == walletPrice ? _self.walletPrice : walletPrice // ignore: cast_nullable_to_non_nullable
as num?,
  ));
}

}


/// Adds pattern-matching-related methods to [BookingState].
extension BookingStatePatterns on BookingState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BookingState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BookingState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BookingState value)  $default,){
final _that = this;
switch (_that) {
case _BookingState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BookingState value)?  $default,){
final _that = this;
switch (_that) {
case _BookingState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<BookingModel> upcoming,  List<BookingModel> past,  List<PaymentData> payments,  List<CheckData>? listDate,  bool isLoading,  bool isLoadingMaster,  String? coupon,  bool isButtonLoading,  bool isPaymentLoading,  PaymentData? selectedPayment,  BookingCalculateResponse? calculate,  DateTime? selectDateTime,  String? selectBookTime,  List<ServiceModel> selectServices,  Map<int, MasterModel> selectMasters,  MyGiftCartModel? giftCart,  num? walletPrice)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BookingState() when $default != null:
return $default(_that.upcoming,_that.past,_that.payments,_that.listDate,_that.isLoading,_that.isLoadingMaster,_that.coupon,_that.isButtonLoading,_that.isPaymentLoading,_that.selectedPayment,_that.calculate,_that.selectDateTime,_that.selectBookTime,_that.selectServices,_that.selectMasters,_that.giftCart,_that.walletPrice);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<BookingModel> upcoming,  List<BookingModel> past,  List<PaymentData> payments,  List<CheckData>? listDate,  bool isLoading,  bool isLoadingMaster,  String? coupon,  bool isButtonLoading,  bool isPaymentLoading,  PaymentData? selectedPayment,  BookingCalculateResponse? calculate,  DateTime? selectDateTime,  String? selectBookTime,  List<ServiceModel> selectServices,  Map<int, MasterModel> selectMasters,  MyGiftCartModel? giftCart,  num? walletPrice)  $default,) {final _that = this;
switch (_that) {
case _BookingState():
return $default(_that.upcoming,_that.past,_that.payments,_that.listDate,_that.isLoading,_that.isLoadingMaster,_that.coupon,_that.isButtonLoading,_that.isPaymentLoading,_that.selectedPayment,_that.calculate,_that.selectDateTime,_that.selectBookTime,_that.selectServices,_that.selectMasters,_that.giftCart,_that.walletPrice);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<BookingModel> upcoming,  List<BookingModel> past,  List<PaymentData> payments,  List<CheckData>? listDate,  bool isLoading,  bool isLoadingMaster,  String? coupon,  bool isButtonLoading,  bool isPaymentLoading,  PaymentData? selectedPayment,  BookingCalculateResponse? calculate,  DateTime? selectDateTime,  String? selectBookTime,  List<ServiceModel> selectServices,  Map<int, MasterModel> selectMasters,  MyGiftCartModel? giftCart,  num? walletPrice)?  $default,) {final _that = this;
switch (_that) {
case _BookingState() when $default != null:
return $default(_that.upcoming,_that.past,_that.payments,_that.listDate,_that.isLoading,_that.isLoadingMaster,_that.coupon,_that.isButtonLoading,_that.isPaymentLoading,_that.selectedPayment,_that.calculate,_that.selectDateTime,_that.selectBookTime,_that.selectServices,_that.selectMasters,_that.giftCart,_that.walletPrice);case _:
  return null;

}
}

}

/// @nodoc


class _BookingState implements BookingState {
  const _BookingState({final  List<BookingModel> upcoming = const [], final  List<BookingModel> past = const [], final  List<PaymentData> payments = const [], final  List<CheckData>? listDate = const [], this.isLoading = true, this.isLoadingMaster = false, this.coupon = null, this.isButtonLoading = false, this.isPaymentLoading = true, this.selectedPayment, this.calculate = null, this.selectDateTime = null, this.selectBookTime = null, final  List<ServiceModel> selectServices = const [], final  Map<int, MasterModel> selectMasters = const {}, this.giftCart = null, this.walletPrice}): _upcoming = upcoming,_past = past,_payments = payments,_listDate = listDate,_selectServices = selectServices,_selectMasters = selectMasters;
  

 final  List<BookingModel> _upcoming;
@override@JsonKey() List<BookingModel> get upcoming {
  if (_upcoming is EqualUnmodifiableListView) return _upcoming;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_upcoming);
}

 final  List<BookingModel> _past;
@override@JsonKey() List<BookingModel> get past {
  if (_past is EqualUnmodifiableListView) return _past;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_past);
}

 final  List<PaymentData> _payments;
@override@JsonKey() List<PaymentData> get payments {
  if (_payments is EqualUnmodifiableListView) return _payments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_payments);
}

 final  List<CheckData>? _listDate;
@override@JsonKey() List<CheckData>? get listDate {
  final value = _listDate;
  if (value == null) return null;
  if (_listDate is EqualUnmodifiableListView) return _listDate;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isLoadingMaster;
@override@JsonKey() final  String? coupon;
@override@JsonKey() final  bool isButtonLoading;
@override@JsonKey() final  bool isPaymentLoading;
@override final  PaymentData? selectedPayment;
@override@JsonKey() final  BookingCalculateResponse? calculate;
@override@JsonKey() final  DateTime? selectDateTime;
@override@JsonKey() final  String? selectBookTime;
 final  List<ServiceModel> _selectServices;
@override@JsonKey() List<ServiceModel> get selectServices {
  if (_selectServices is EqualUnmodifiableListView) return _selectServices;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_selectServices);
}

 final  Map<int, MasterModel> _selectMasters;
@override@JsonKey() Map<int, MasterModel> get selectMasters {
  if (_selectMasters is EqualUnmodifiableMapView) return _selectMasters;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_selectMasters);
}

@override@JsonKey() final  MyGiftCartModel? giftCart;
@override final  num? walletPrice;

/// Create a copy of BookingState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BookingStateCopyWith<_BookingState> get copyWith => __$BookingStateCopyWithImpl<_BookingState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BookingState&&const DeepCollectionEquality().equals(other._upcoming, _upcoming)&&const DeepCollectionEquality().equals(other._past, _past)&&const DeepCollectionEquality().equals(other._payments, _payments)&&const DeepCollectionEquality().equals(other._listDate, _listDate)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isLoadingMaster, isLoadingMaster) || other.isLoadingMaster == isLoadingMaster)&&(identical(other.coupon, coupon) || other.coupon == coupon)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isPaymentLoading, isPaymentLoading) || other.isPaymentLoading == isPaymentLoading)&&(identical(other.selectedPayment, selectedPayment) || other.selectedPayment == selectedPayment)&&(identical(other.calculate, calculate) || other.calculate == calculate)&&(identical(other.selectDateTime, selectDateTime) || other.selectDateTime == selectDateTime)&&(identical(other.selectBookTime, selectBookTime) || other.selectBookTime == selectBookTime)&&const DeepCollectionEquality().equals(other._selectServices, _selectServices)&&const DeepCollectionEquality().equals(other._selectMasters, _selectMasters)&&(identical(other.giftCart, giftCart) || other.giftCart == giftCart)&&(identical(other.walletPrice, walletPrice) || other.walletPrice == walletPrice));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_upcoming),const DeepCollectionEquality().hash(_past),const DeepCollectionEquality().hash(_payments),const DeepCollectionEquality().hash(_listDate),isLoading,isLoadingMaster,coupon,isButtonLoading,isPaymentLoading,selectedPayment,calculate,selectDateTime,selectBookTime,const DeepCollectionEquality().hash(_selectServices),const DeepCollectionEquality().hash(_selectMasters),giftCart,walletPrice);

@override
String toString() {
  return 'BookingState(upcoming: $upcoming, past: $past, payments: $payments, listDate: $listDate, isLoading: $isLoading, isLoadingMaster: $isLoadingMaster, coupon: $coupon, isButtonLoading: $isButtonLoading, isPaymentLoading: $isPaymentLoading, selectedPayment: $selectedPayment, calculate: $calculate, selectDateTime: $selectDateTime, selectBookTime: $selectBookTime, selectServices: $selectServices, selectMasters: $selectMasters, giftCart: $giftCart, walletPrice: $walletPrice)';
}


}

/// @nodoc
abstract mixin class _$BookingStateCopyWith<$Res> implements $BookingStateCopyWith<$Res> {
  factory _$BookingStateCopyWith(_BookingState value, $Res Function(_BookingState) _then) = __$BookingStateCopyWithImpl;
@override @useResult
$Res call({
 List<BookingModel> upcoming, List<BookingModel> past, List<PaymentData> payments, List<CheckData>? listDate, bool isLoading, bool isLoadingMaster, String? coupon, bool isButtonLoading, bool isPaymentLoading, PaymentData? selectedPayment, BookingCalculateResponse? calculate, DateTime? selectDateTime, String? selectBookTime, List<ServiceModel> selectServices, Map<int, MasterModel> selectMasters, MyGiftCartModel? giftCart, num? walletPrice
});




}
/// @nodoc
class __$BookingStateCopyWithImpl<$Res>
    implements _$BookingStateCopyWith<$Res> {
  __$BookingStateCopyWithImpl(this._self, this._then);

  final _BookingState _self;
  final $Res Function(_BookingState) _then;

/// Create a copy of BookingState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? upcoming = null,Object? past = null,Object? payments = null,Object? listDate = freezed,Object? isLoading = null,Object? isLoadingMaster = null,Object? coupon = freezed,Object? isButtonLoading = null,Object? isPaymentLoading = null,Object? selectedPayment = freezed,Object? calculate = freezed,Object? selectDateTime = freezed,Object? selectBookTime = freezed,Object? selectServices = null,Object? selectMasters = null,Object? giftCart = freezed,Object? walletPrice = freezed,}) {
  return _then(_BookingState(
upcoming: null == upcoming ? _self._upcoming : upcoming // ignore: cast_nullable_to_non_nullable
as List<BookingModel>,past: null == past ? _self._past : past // ignore: cast_nullable_to_non_nullable
as List<BookingModel>,payments: null == payments ? _self._payments : payments // ignore: cast_nullable_to_non_nullable
as List<PaymentData>,listDate: freezed == listDate ? _self._listDate : listDate // ignore: cast_nullable_to_non_nullable
as List<CheckData>?,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isLoadingMaster: null == isLoadingMaster ? _self.isLoadingMaster : isLoadingMaster // ignore: cast_nullable_to_non_nullable
as bool,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as String?,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isPaymentLoading: null == isPaymentLoading ? _self.isPaymentLoading : isPaymentLoading // ignore: cast_nullable_to_non_nullable
as bool,selectedPayment: freezed == selectedPayment ? _self.selectedPayment : selectedPayment // ignore: cast_nullable_to_non_nullable
as PaymentData?,calculate: freezed == calculate ? _self.calculate : calculate // ignore: cast_nullable_to_non_nullable
as BookingCalculateResponse?,selectDateTime: freezed == selectDateTime ? _self.selectDateTime : selectDateTime // ignore: cast_nullable_to_non_nullable
as DateTime?,selectBookTime: freezed == selectBookTime ? _self.selectBookTime : selectBookTime // ignore: cast_nullable_to_non_nullable
as String?,selectServices: null == selectServices ? _self._selectServices : selectServices // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,selectMasters: null == selectMasters ? _self._selectMasters : selectMasters // ignore: cast_nullable_to_non_nullable
as Map<int, MasterModel>,giftCart: freezed == giftCart ? _self.giftCart : giftCart // ignore: cast_nullable_to_non_nullable
as MyGiftCartModel?,walletPrice: freezed == walletPrice ? _self.walletPrice : walletPrice // ignore: cast_nullable_to_non_nullable
as num?,
  ));
}


}

// dart format on
