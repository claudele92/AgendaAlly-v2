// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'wallet_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$WalletEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is WalletEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'WalletEvent()';
}


}

/// @nodoc
class $WalletEventCopyWith<$Res>  {
$WalletEventCopyWith(WalletEvent _, $Res Function(WalletEvent) __);
}


/// Adds pattern-matching-related methods to [WalletEvent].
extension WalletEventPatterns on WalletEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchTransactions value)?  fetchTransactions,TResult Function( FetchPayments value)?  fetchPayments,TResult Function( SelectPayment value)?  selectPayment,TResult Function( SearchUser value)?  searchUser,TResult Function( FillWallet value)?  fillWallet,TResult Function( SendWallet value)?  sendWallet,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchTransactions() when fetchTransactions != null:
return fetchTransactions(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case SelectPayment() when selectPayment != null:
return selectPayment(_that);case SearchUser() when searchUser != null:
return searchUser(_that);case FillWallet() when fillWallet != null:
return fillWallet(_that);case SendWallet() when sendWallet != null:
return sendWallet(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchTransactions value)  fetchTransactions,required TResult Function( FetchPayments value)  fetchPayments,required TResult Function( SelectPayment value)  selectPayment,required TResult Function( SearchUser value)  searchUser,required TResult Function( FillWallet value)  fillWallet,required TResult Function( SendWallet value)  sendWallet,}){
final _that = this;
switch (_that) {
case FetchTransactions():
return fetchTransactions(_that);case FetchPayments():
return fetchPayments(_that);case SelectPayment():
return selectPayment(_that);case SearchUser():
return searchUser(_that);case FillWallet():
return fillWallet(_that);case SendWallet():
return sendWallet(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchTransactions value)?  fetchTransactions,TResult? Function( FetchPayments value)?  fetchPayments,TResult? Function( SelectPayment value)?  selectPayment,TResult? Function( SearchUser value)?  searchUser,TResult? Function( FillWallet value)?  fillWallet,TResult? Function( SendWallet value)?  sendWallet,}){
final _that = this;
switch (_that) {
case FetchTransactions() when fetchTransactions != null:
return fetchTransactions(_that);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that);case SelectPayment() when selectPayment != null:
return selectPayment(_that);case SearchUser() when searchUser != null:
return searchUser(_that);case FillWallet() when fillWallet != null:
return fillWallet(_that);case SendWallet() when sendWallet != null:
return sendWallet(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchTransactions,TResult Function( BuildContext context)?  fetchPayments,TResult Function( int index)?  selectPayment,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller,  String search)?  searchUser,TResult Function( BuildContext context,  int walletId,  VoidCallback onSuccess,  VoidCallback onFailure,  CustomColorSet colors,  String price)?  fillWallet,TResult Function( BuildContext context,  String price,  VoidCallback onSuccess,  String uuid)?  sendWallet,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchTransactions() when fetchTransactions != null:
return fetchTransactions(_that.context,_that.isRefresh,_that.controller);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context);case SelectPayment() when selectPayment != null:
return selectPayment(_that.index);case SearchUser() when searchUser != null:
return searchUser(_that.context,_that.isRefresh,_that.controller,_that.search);case FillWallet() when fillWallet != null:
return fillWallet(_that.context,_that.walletId,_that.onSuccess,_that.onFailure,_that.colors,_that.price);case SendWallet() when sendWallet != null:
return sendWallet(_that.context,_that.price,_that.onSuccess,_that.uuid);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchTransactions,required TResult Function( BuildContext context)  fetchPayments,required TResult Function( int index)  selectPayment,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller,  String search)  searchUser,required TResult Function( BuildContext context,  int walletId,  VoidCallback onSuccess,  VoidCallback onFailure,  CustomColorSet colors,  String price)  fillWallet,required TResult Function( BuildContext context,  String price,  VoidCallback onSuccess,  String uuid)  sendWallet,}) {final _that = this;
switch (_that) {
case FetchTransactions():
return fetchTransactions(_that.context,_that.isRefresh,_that.controller);case FetchPayments():
return fetchPayments(_that.context);case SelectPayment():
return selectPayment(_that.index);case SearchUser():
return searchUser(_that.context,_that.isRefresh,_that.controller,_that.search);case FillWallet():
return fillWallet(_that.context,_that.walletId,_that.onSuccess,_that.onFailure,_that.colors,_that.price);case SendWallet():
return sendWallet(_that.context,_that.price,_that.onSuccess,_that.uuid);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchTransactions,TResult? Function( BuildContext context)?  fetchPayments,TResult? Function( int index)?  selectPayment,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller,  String search)?  searchUser,TResult? Function( BuildContext context,  int walletId,  VoidCallback onSuccess,  VoidCallback onFailure,  CustomColorSet colors,  String price)?  fillWallet,TResult? Function( BuildContext context,  String price,  VoidCallback onSuccess,  String uuid)?  sendWallet,}) {final _that = this;
switch (_that) {
case FetchTransactions() when fetchTransactions != null:
return fetchTransactions(_that.context,_that.isRefresh,_that.controller);case FetchPayments() when fetchPayments != null:
return fetchPayments(_that.context);case SelectPayment() when selectPayment != null:
return selectPayment(_that.index);case SearchUser() when searchUser != null:
return searchUser(_that.context,_that.isRefresh,_that.controller,_that.search);case FillWallet() when fillWallet != null:
return fillWallet(_that.context,_that.walletId,_that.onSuccess,_that.onFailure,_that.colors,_that.price);case SendWallet() when sendWallet != null:
return sendWallet(_that.context,_that.price,_that.onSuccess,_that.uuid);case _:
  return null;

}
}

}

/// @nodoc


class FetchTransactions implements WalletEvent {
  const FetchTransactions(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchTransactionsCopyWith<FetchTransactions> get copyWith => _$FetchTransactionsCopyWithImpl<FetchTransactions>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchTransactions&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'WalletEvent.fetchTransactions(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchTransactionsCopyWith<$Res> implements $WalletEventCopyWith<$Res> {
  factory $FetchTransactionsCopyWith(FetchTransactions value, $Res Function(FetchTransactions) _then) = _$FetchTransactionsCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchTransactionsCopyWithImpl<$Res>
    implements $FetchTransactionsCopyWith<$Res> {
  _$FetchTransactionsCopyWithImpl(this._self, this._then);

  final FetchTransactions _self;
  final $Res Function(FetchTransactions) _then;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchTransactions(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchPayments implements WalletEvent {
  const FetchPayments(this.context);
  

 final  BuildContext context;

/// Create a copy of WalletEvent
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
  return 'WalletEvent.fetchPayments(context: $context)';
}


}

/// @nodoc
abstract mixin class $FetchPaymentsCopyWith<$Res> implements $WalletEventCopyWith<$Res> {
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

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(FetchPayments(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class SelectPayment implements WalletEvent {
  const SelectPayment({required this.index});
  

 final  int index;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectPaymentCopyWith<SelectPayment> get copyWith => _$SelectPaymentCopyWithImpl<SelectPayment>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectPayment&&(identical(other.index, index) || other.index == index));
}


@override
int get hashCode => Object.hash(runtimeType,index);

@override
String toString() {
  return 'WalletEvent.selectPayment(index: $index)';
}


}

/// @nodoc
abstract mixin class $SelectPaymentCopyWith<$Res> implements $WalletEventCopyWith<$Res> {
  factory $SelectPaymentCopyWith(SelectPayment value, $Res Function(SelectPayment) _then) = _$SelectPaymentCopyWithImpl;
@useResult
$Res call({
 int index
});




}
/// @nodoc
class _$SelectPaymentCopyWithImpl<$Res>
    implements $SelectPaymentCopyWith<$Res> {
  _$SelectPaymentCopyWithImpl(this._self, this._then);

  final SelectPayment _self;
  final $Res Function(SelectPayment) _then;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? index = null,}) {
  return _then(SelectPayment(
index: null == index ? _self.index : index // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class SearchUser implements WalletEvent {
  const SearchUser(this.context, {this.isRefresh, this.controller, required this.search});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;
 final  String search;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchUserCopyWith<SearchUser> get copyWith => _$SearchUserCopyWithImpl<SearchUser>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchUser&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller)&&(identical(other.search, search) || other.search == search));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller,search);

@override
String toString() {
  return 'WalletEvent.searchUser(context: $context, isRefresh: $isRefresh, controller: $controller, search: $search)';
}


}

/// @nodoc
abstract mixin class $SearchUserCopyWith<$Res> implements $WalletEventCopyWith<$Res> {
  factory $SearchUserCopyWith(SearchUser value, $Res Function(SearchUser) _then) = _$SearchUserCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller, String search
});




}
/// @nodoc
class _$SearchUserCopyWithImpl<$Res>
    implements $SearchUserCopyWith<$Res> {
  _$SearchUserCopyWithImpl(this._self, this._then);

  final SearchUser _self;
  final $Res Function(SearchUser) _then;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,Object? search = null,}) {
  return _then(SearchUser(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,search: null == search ? _self.search : search // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class FillWallet implements WalletEvent {
  const FillWallet(this.context, {required this.walletId, required this.onSuccess, required this.onFailure, required this.colors, required this.price});
  

 final  BuildContext context;
 final  int walletId;
 final  VoidCallback onSuccess;
 final  VoidCallback onFailure;
 final  CustomColorSet colors;
 final  String price;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FillWalletCopyWith<FillWallet> get copyWith => _$FillWalletCopyWithImpl<FillWallet>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FillWallet&&(identical(other.context, context) || other.context == context)&&(identical(other.walletId, walletId) || other.walletId == walletId)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess)&&(identical(other.onFailure, onFailure) || other.onFailure == onFailure)&&(identical(other.colors, colors) || other.colors == colors)&&(identical(other.price, price) || other.price == price));
}


@override
int get hashCode => Object.hash(runtimeType,context,walletId,onSuccess,onFailure,colors,price);

@override
String toString() {
  return 'WalletEvent.fillWallet(context: $context, walletId: $walletId, onSuccess: $onSuccess, onFailure: $onFailure, colors: $colors, price: $price)';
}


}

/// @nodoc
abstract mixin class $FillWalletCopyWith<$Res> implements $WalletEventCopyWith<$Res> {
  factory $FillWalletCopyWith(FillWallet value, $Res Function(FillWallet) _then) = _$FillWalletCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int walletId, VoidCallback onSuccess, VoidCallback onFailure, CustomColorSet colors, String price
});




}
/// @nodoc
class _$FillWalletCopyWithImpl<$Res>
    implements $FillWalletCopyWith<$Res> {
  _$FillWalletCopyWithImpl(this._self, this._then);

  final FillWallet _self;
  final $Res Function(FillWallet) _then;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? walletId = null,Object? onSuccess = null,Object? onFailure = null,Object? colors = null,Object? price = null,}) {
  return _then(FillWallet(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,walletId: null == walletId ? _self.walletId : walletId // ignore: cast_nullable_to_non_nullable
as int,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,onFailure: null == onFailure ? _self.onFailure : onFailure // ignore: cast_nullable_to_non_nullable
as VoidCallback,colors: null == colors ? _self.colors : colors // ignore: cast_nullable_to_non_nullable
as CustomColorSet,price: null == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class SendWallet implements WalletEvent {
  const SendWallet(this.context, {required this.price, required this.onSuccess, required this.uuid});
  

 final  BuildContext context;
 final  String price;
 final  VoidCallback onSuccess;
 final  String uuid;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SendWalletCopyWith<SendWallet> get copyWith => _$SendWalletCopyWithImpl<SendWallet>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SendWallet&&(identical(other.context, context) || other.context == context)&&(identical(other.price, price) || other.price == price)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess)&&(identical(other.uuid, uuid) || other.uuid == uuid));
}


@override
int get hashCode => Object.hash(runtimeType,context,price,onSuccess,uuid);

@override
String toString() {
  return 'WalletEvent.sendWallet(context: $context, price: $price, onSuccess: $onSuccess, uuid: $uuid)';
}


}

/// @nodoc
abstract mixin class $SendWalletCopyWith<$Res> implements $WalletEventCopyWith<$Res> {
  factory $SendWalletCopyWith(SendWallet value, $Res Function(SendWallet) _then) = _$SendWalletCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String price, VoidCallback onSuccess, String uuid
});




}
/// @nodoc
class _$SendWalletCopyWithImpl<$Res>
    implements $SendWalletCopyWith<$Res> {
  _$SendWalletCopyWithImpl(this._self, this._then);

  final SendWallet _self;
  final $Res Function(SendWallet) _then;

/// Create a copy of WalletEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? price = null,Object? onSuccess = null,Object? uuid = null,}) {
  return _then(SendWallet(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,price: null == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as String,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback,uuid: null == uuid ? _self.uuid : uuid // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$WalletState {

 bool get isTransactionLoading; bool get isButtonLoading; bool get isSearchingLoading; int get selectPayment; List<TransactionModel> get transactions; List<PaymentData>? get list; List<UserModel>? get listOfUser;
/// Create a copy of WalletState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WalletStateCopyWith<WalletState> get copyWith => _$WalletStateCopyWithImpl<WalletState>(this as WalletState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is WalletState&&(identical(other.isTransactionLoading, isTransactionLoading) || other.isTransactionLoading == isTransactionLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isSearchingLoading, isSearchingLoading) || other.isSearchingLoading == isSearchingLoading)&&(identical(other.selectPayment, selectPayment) || other.selectPayment == selectPayment)&&const DeepCollectionEquality().equals(other.transactions, transactions)&&const DeepCollectionEquality().equals(other.list, list)&&const DeepCollectionEquality().equals(other.listOfUser, listOfUser));
}


@override
int get hashCode => Object.hash(runtimeType,isTransactionLoading,isButtonLoading,isSearchingLoading,selectPayment,const DeepCollectionEquality().hash(transactions),const DeepCollectionEquality().hash(list),const DeepCollectionEquality().hash(listOfUser));

@override
String toString() {
  return 'WalletState(isTransactionLoading: $isTransactionLoading, isButtonLoading: $isButtonLoading, isSearchingLoading: $isSearchingLoading, selectPayment: $selectPayment, transactions: $transactions, list: $list, listOfUser: $listOfUser)';
}


}

/// @nodoc
abstract mixin class $WalletStateCopyWith<$Res>  {
  factory $WalletStateCopyWith(WalletState value, $Res Function(WalletState) _then) = _$WalletStateCopyWithImpl;
@useResult
$Res call({
 bool isTransactionLoading, bool isButtonLoading, bool isSearchingLoading, int selectPayment, List<TransactionModel> transactions, List<PaymentData>? list, List<UserModel>? listOfUser
});




}
/// @nodoc
class _$WalletStateCopyWithImpl<$Res>
    implements $WalletStateCopyWith<$Res> {
  _$WalletStateCopyWithImpl(this._self, this._then);

  final WalletState _self;
  final $Res Function(WalletState) _then;

/// Create a copy of WalletState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isTransactionLoading = null,Object? isButtonLoading = null,Object? isSearchingLoading = null,Object? selectPayment = null,Object? transactions = null,Object? list = freezed,Object? listOfUser = freezed,}) {
  return _then(_self.copyWith(
isTransactionLoading: null == isTransactionLoading ? _self.isTransactionLoading : isTransactionLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isSearchingLoading: null == isSearchingLoading ? _self.isSearchingLoading : isSearchingLoading // ignore: cast_nullable_to_non_nullable
as bool,selectPayment: null == selectPayment ? _self.selectPayment : selectPayment // ignore: cast_nullable_to_non_nullable
as int,transactions: null == transactions ? _self.transactions : transactions // ignore: cast_nullable_to_non_nullable
as List<TransactionModel>,list: freezed == list ? _self.list : list // ignore: cast_nullable_to_non_nullable
as List<PaymentData>?,listOfUser: freezed == listOfUser ? _self.listOfUser : listOfUser // ignore: cast_nullable_to_non_nullable
as List<UserModel>?,
  ));
}

}


/// Adds pattern-matching-related methods to [WalletState].
extension WalletStatePatterns on WalletState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _WalletState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _WalletState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _WalletState value)  $default,){
final _that = this;
switch (_that) {
case _WalletState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _WalletState value)?  $default,){
final _that = this;
switch (_that) {
case _WalletState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isTransactionLoading,  bool isButtonLoading,  bool isSearchingLoading,  int selectPayment,  List<TransactionModel> transactions,  List<PaymentData>? list,  List<UserModel>? listOfUser)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _WalletState() when $default != null:
return $default(_that.isTransactionLoading,_that.isButtonLoading,_that.isSearchingLoading,_that.selectPayment,_that.transactions,_that.list,_that.listOfUser);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isTransactionLoading,  bool isButtonLoading,  bool isSearchingLoading,  int selectPayment,  List<TransactionModel> transactions,  List<PaymentData>? list,  List<UserModel>? listOfUser)  $default,) {final _that = this;
switch (_that) {
case _WalletState():
return $default(_that.isTransactionLoading,_that.isButtonLoading,_that.isSearchingLoading,_that.selectPayment,_that.transactions,_that.list,_that.listOfUser);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isTransactionLoading,  bool isButtonLoading,  bool isSearchingLoading,  int selectPayment,  List<TransactionModel> transactions,  List<PaymentData>? list,  List<UserModel>? listOfUser)?  $default,) {final _that = this;
switch (_that) {
case _WalletState() when $default != null:
return $default(_that.isTransactionLoading,_that.isButtonLoading,_that.isSearchingLoading,_that.selectPayment,_that.transactions,_that.list,_that.listOfUser);case _:
  return null;

}
}

}

/// @nodoc


class _WalletState implements WalletState {
  const _WalletState({this.isTransactionLoading = false, this.isButtonLoading = false, this.isSearchingLoading = false, this.selectPayment = 1, final  List<TransactionModel> transactions = const [], final  List<PaymentData>? list = const [], final  List<UserModel>? listOfUser = const []}): _transactions = transactions,_list = list,_listOfUser = listOfUser;
  

@override@JsonKey() final  bool isTransactionLoading;
@override@JsonKey() final  bool isButtonLoading;
@override@JsonKey() final  bool isSearchingLoading;
@override@JsonKey() final  int selectPayment;
 final  List<TransactionModel> _transactions;
@override@JsonKey() List<TransactionModel> get transactions {
  if (_transactions is EqualUnmodifiableListView) return _transactions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_transactions);
}

 final  List<PaymentData>? _list;
@override@JsonKey() List<PaymentData>? get list {
  final value = _list;
  if (value == null) return null;
  if (_list is EqualUnmodifiableListView) return _list;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<UserModel>? _listOfUser;
@override@JsonKey() List<UserModel>? get listOfUser {
  final value = _listOfUser;
  if (value == null) return null;
  if (_listOfUser is EqualUnmodifiableListView) return _listOfUser;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}


/// Create a copy of WalletState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$WalletStateCopyWith<_WalletState> get copyWith => __$WalletStateCopyWithImpl<_WalletState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _WalletState&&(identical(other.isTransactionLoading, isTransactionLoading) || other.isTransactionLoading == isTransactionLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isSearchingLoading, isSearchingLoading) || other.isSearchingLoading == isSearchingLoading)&&(identical(other.selectPayment, selectPayment) || other.selectPayment == selectPayment)&&const DeepCollectionEquality().equals(other._transactions, _transactions)&&const DeepCollectionEquality().equals(other._list, _list)&&const DeepCollectionEquality().equals(other._listOfUser, _listOfUser));
}


@override
int get hashCode => Object.hash(runtimeType,isTransactionLoading,isButtonLoading,isSearchingLoading,selectPayment,const DeepCollectionEquality().hash(_transactions),const DeepCollectionEquality().hash(_list),const DeepCollectionEquality().hash(_listOfUser));

@override
String toString() {
  return 'WalletState(isTransactionLoading: $isTransactionLoading, isButtonLoading: $isButtonLoading, isSearchingLoading: $isSearchingLoading, selectPayment: $selectPayment, transactions: $transactions, list: $list, listOfUser: $listOfUser)';
}


}

/// @nodoc
abstract mixin class _$WalletStateCopyWith<$Res> implements $WalletStateCopyWith<$Res> {
  factory _$WalletStateCopyWith(_WalletState value, $Res Function(_WalletState) _then) = __$WalletStateCopyWithImpl;
@override @useResult
$Res call({
 bool isTransactionLoading, bool isButtonLoading, bool isSearchingLoading, int selectPayment, List<TransactionModel> transactions, List<PaymentData>? list, List<UserModel>? listOfUser
});




}
/// @nodoc
class __$WalletStateCopyWithImpl<$Res>
    implements _$WalletStateCopyWith<$Res> {
  __$WalletStateCopyWithImpl(this._self, this._then);

  final _WalletState _self;
  final $Res Function(_WalletState) _then;

/// Create a copy of WalletState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isTransactionLoading = null,Object? isButtonLoading = null,Object? isSearchingLoading = null,Object? selectPayment = null,Object? transactions = null,Object? list = freezed,Object? listOfUser = freezed,}) {
  return _then(_WalletState(
isTransactionLoading: null == isTransactionLoading ? _self.isTransactionLoading : isTransactionLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isSearchingLoading: null == isSearchingLoading ? _self.isSearchingLoading : isSearchingLoading // ignore: cast_nullable_to_non_nullable
as bool,selectPayment: null == selectPayment ? _self.selectPayment : selectPayment // ignore: cast_nullable_to_non_nullable
as int,transactions: null == transactions ? _self._transactions : transactions // ignore: cast_nullable_to_non_nullable
as List<TransactionModel>,list: freezed == list ? _self._list : list // ignore: cast_nullable_to_non_nullable
as List<PaymentData>?,listOfUser: freezed == listOfUser ? _self._listOfUser : listOfUser // ignore: cast_nullable_to_non_nullable
as List<UserModel>?,
  ));
}


}

// dart format on
