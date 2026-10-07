// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'product_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$ProductEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'ProductEvent()';
}


}

/// @nodoc
class $ProductEventCopyWith<$Res>  {
$ProductEventCopyWith(ProductEvent _, $Res Function(ProductEvent) __);
}


/// Adds pattern-matching-related methods to [ProductEvent].
extension ProductEventPatterns on ProductEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchMostSaleProduct value)?  fetchMostSaleProduct,TResult Function( FetchAllProduct value)?  fetchAllProduct,TResult Function( FetchNewProduct value)?  fetchNewProduct,TResult Function( FetchLikeProduct value)?  fetchLikeProduct,TResult Function( FetchDiscountProduct value)?  fetchDiscountProduct,TResult Function( FetchMostSaleShopProduct value)?  fetchMostSaleShopProduct,TResult Function( FetchNewShopProduct value)?  fetchNewShopProduct,TResult Function( FetchProducts value)?  fetchProducts,TResult Function( UpdateState value)?  updateState,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchMostSaleProduct() when fetchMostSaleProduct != null:
return fetchMostSaleProduct(_that);case FetchAllProduct() when fetchAllProduct != null:
return fetchAllProduct(_that);case FetchNewProduct() when fetchNewProduct != null:
return fetchNewProduct(_that);case FetchLikeProduct() when fetchLikeProduct != null:
return fetchLikeProduct(_that);case FetchDiscountProduct() when fetchDiscountProduct != null:
return fetchDiscountProduct(_that);case FetchMostSaleShopProduct() when fetchMostSaleShopProduct != null:
return fetchMostSaleShopProduct(_that);case FetchNewShopProduct() when fetchNewShopProduct != null:
return fetchNewShopProduct(_that);case FetchProducts() when fetchProducts != null:
return fetchProducts(_that);case UpdateState() when updateState != null:
return updateState(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchMostSaleProduct value)  fetchMostSaleProduct,required TResult Function( FetchAllProduct value)  fetchAllProduct,required TResult Function( FetchNewProduct value)  fetchNewProduct,required TResult Function( FetchLikeProduct value)  fetchLikeProduct,required TResult Function( FetchDiscountProduct value)  fetchDiscountProduct,required TResult Function( FetchMostSaleShopProduct value)  fetchMostSaleShopProduct,required TResult Function( FetchNewShopProduct value)  fetchNewShopProduct,required TResult Function( FetchProducts value)  fetchProducts,required TResult Function( UpdateState value)  updateState,}){
final _that = this;
switch (_that) {
case FetchMostSaleProduct():
return fetchMostSaleProduct(_that);case FetchAllProduct():
return fetchAllProduct(_that);case FetchNewProduct():
return fetchNewProduct(_that);case FetchLikeProduct():
return fetchLikeProduct(_that);case FetchDiscountProduct():
return fetchDiscountProduct(_that);case FetchMostSaleShopProduct():
return fetchMostSaleShopProduct(_that);case FetchNewShopProduct():
return fetchNewShopProduct(_that);case FetchProducts():
return fetchProducts(_that);case UpdateState():
return updateState(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchMostSaleProduct value)?  fetchMostSaleProduct,TResult? Function( FetchAllProduct value)?  fetchAllProduct,TResult? Function( FetchNewProduct value)?  fetchNewProduct,TResult? Function( FetchLikeProduct value)?  fetchLikeProduct,TResult? Function( FetchDiscountProduct value)?  fetchDiscountProduct,TResult? Function( FetchMostSaleShopProduct value)?  fetchMostSaleShopProduct,TResult? Function( FetchNewShopProduct value)?  fetchNewShopProduct,TResult? Function( FetchProducts value)?  fetchProducts,TResult? Function( UpdateState value)?  updateState,}){
final _that = this;
switch (_that) {
case FetchMostSaleProduct() when fetchMostSaleProduct != null:
return fetchMostSaleProduct(_that);case FetchAllProduct() when fetchAllProduct != null:
return fetchAllProduct(_that);case FetchNewProduct() when fetchNewProduct != null:
return fetchNewProduct(_that);case FetchLikeProduct() when fetchLikeProduct != null:
return fetchLikeProduct(_that);case FetchDiscountProduct() when fetchDiscountProduct != null:
return fetchDiscountProduct(_that);case FetchMostSaleShopProduct() when fetchMostSaleShopProduct != null:
return fetchMostSaleShopProduct(_that);case FetchNewShopProduct() when fetchNewShopProduct != null:
return fetchNewShopProduct(_that);case FetchProducts() when fetchProducts != null:
return fetchProducts(_that);case UpdateState() when updateState != null:
return updateState(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchMostSaleProduct,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchAllProduct,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchNewProduct,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchLikeProduct,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchDiscountProduct,TResult Function( BuildContext context,  int? shopId,  bool? isRefresh,  RefreshController? controller)?  fetchMostSaleShopProduct,TResult Function( BuildContext context,  int? shopId,  bool? isRefresh,  RefreshController? controller)?  fetchNewShopProduct,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller,  List<ProductData>? list,  bool? isNewProduct,  bool? isMostSaleProduct,  int? categoryId,  int? shopId,  int? bannerId,  List<int>? brandId,  List<int>? categoryIds,  List<int>? extrasId,  num? priceTo,  num? priceFrom)?  fetchProducts,TResult Function()?  updateState,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchMostSaleProduct() when fetchMostSaleProduct != null:
return fetchMostSaleProduct(_that.context,_that.isRefresh,_that.controller);case FetchAllProduct() when fetchAllProduct != null:
return fetchAllProduct(_that.context,_that.isRefresh,_that.controller);case FetchNewProduct() when fetchNewProduct != null:
return fetchNewProduct(_that.context,_that.isRefresh,_that.controller);case FetchLikeProduct() when fetchLikeProduct != null:
return fetchLikeProduct(_that.context,_that.isRefresh,_that.controller);case FetchDiscountProduct() when fetchDiscountProduct != null:
return fetchDiscountProduct(_that.context,_that.isRefresh,_that.controller);case FetchMostSaleShopProduct() when fetchMostSaleShopProduct != null:
return fetchMostSaleShopProduct(_that.context,_that.shopId,_that.isRefresh,_that.controller);case FetchNewShopProduct() when fetchNewShopProduct != null:
return fetchNewShopProduct(_that.context,_that.shopId,_that.isRefresh,_that.controller);case FetchProducts() when fetchProducts != null:
return fetchProducts(_that.context,_that.isRefresh,_that.controller,_that.list,_that.isNewProduct,_that.isMostSaleProduct,_that.categoryId,_that.shopId,_that.bannerId,_that.brandId,_that.categoryIds,_that.extrasId,_that.priceTo,_that.priceFrom);case UpdateState() when updateState != null:
return updateState();case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchMostSaleProduct,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchAllProduct,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchNewProduct,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchLikeProduct,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchDiscountProduct,required TResult Function( BuildContext context,  int? shopId,  bool? isRefresh,  RefreshController? controller)  fetchMostSaleShopProduct,required TResult Function( BuildContext context,  int? shopId,  bool? isRefresh,  RefreshController? controller)  fetchNewShopProduct,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller,  List<ProductData>? list,  bool? isNewProduct,  bool? isMostSaleProduct,  int? categoryId,  int? shopId,  int? bannerId,  List<int>? brandId,  List<int>? categoryIds,  List<int>? extrasId,  num? priceTo,  num? priceFrom)  fetchProducts,required TResult Function()  updateState,}) {final _that = this;
switch (_that) {
case FetchMostSaleProduct():
return fetchMostSaleProduct(_that.context,_that.isRefresh,_that.controller);case FetchAllProduct():
return fetchAllProduct(_that.context,_that.isRefresh,_that.controller);case FetchNewProduct():
return fetchNewProduct(_that.context,_that.isRefresh,_that.controller);case FetchLikeProduct():
return fetchLikeProduct(_that.context,_that.isRefresh,_that.controller);case FetchDiscountProduct():
return fetchDiscountProduct(_that.context,_that.isRefresh,_that.controller);case FetchMostSaleShopProduct():
return fetchMostSaleShopProduct(_that.context,_that.shopId,_that.isRefresh,_that.controller);case FetchNewShopProduct():
return fetchNewShopProduct(_that.context,_that.shopId,_that.isRefresh,_that.controller);case FetchProducts():
return fetchProducts(_that.context,_that.isRefresh,_that.controller,_that.list,_that.isNewProduct,_that.isMostSaleProduct,_that.categoryId,_that.shopId,_that.bannerId,_that.brandId,_that.categoryIds,_that.extrasId,_that.priceTo,_that.priceFrom);case UpdateState():
return updateState();case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchMostSaleProduct,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchAllProduct,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchNewProduct,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchLikeProduct,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchDiscountProduct,TResult? Function( BuildContext context,  int? shopId,  bool? isRefresh,  RefreshController? controller)?  fetchMostSaleShopProduct,TResult? Function( BuildContext context,  int? shopId,  bool? isRefresh,  RefreshController? controller)?  fetchNewShopProduct,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller,  List<ProductData>? list,  bool? isNewProduct,  bool? isMostSaleProduct,  int? categoryId,  int? shopId,  int? bannerId,  List<int>? brandId,  List<int>? categoryIds,  List<int>? extrasId,  num? priceTo,  num? priceFrom)?  fetchProducts,TResult? Function()?  updateState,}) {final _that = this;
switch (_that) {
case FetchMostSaleProduct() when fetchMostSaleProduct != null:
return fetchMostSaleProduct(_that.context,_that.isRefresh,_that.controller);case FetchAllProduct() when fetchAllProduct != null:
return fetchAllProduct(_that.context,_that.isRefresh,_that.controller);case FetchNewProduct() when fetchNewProduct != null:
return fetchNewProduct(_that.context,_that.isRefresh,_that.controller);case FetchLikeProduct() when fetchLikeProduct != null:
return fetchLikeProduct(_that.context,_that.isRefresh,_that.controller);case FetchDiscountProduct() when fetchDiscountProduct != null:
return fetchDiscountProduct(_that.context,_that.isRefresh,_that.controller);case FetchMostSaleShopProduct() when fetchMostSaleShopProduct != null:
return fetchMostSaleShopProduct(_that.context,_that.shopId,_that.isRefresh,_that.controller);case FetchNewShopProduct() when fetchNewShopProduct != null:
return fetchNewShopProduct(_that.context,_that.shopId,_that.isRefresh,_that.controller);case FetchProducts() when fetchProducts != null:
return fetchProducts(_that.context,_that.isRefresh,_that.controller,_that.list,_that.isNewProduct,_that.isMostSaleProduct,_that.categoryId,_that.shopId,_that.bannerId,_that.brandId,_that.categoryIds,_that.extrasId,_that.priceTo,_that.priceFrom);case UpdateState() when updateState != null:
return updateState();case _:
  return null;

}
}

}

/// @nodoc


class FetchMostSaleProduct implements ProductEvent {
  const FetchMostSaleProduct(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMostSaleProductCopyWith<FetchMostSaleProduct> get copyWith => _$FetchMostSaleProductCopyWithImpl<FetchMostSaleProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMostSaleProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ProductEvent.fetchMostSaleProduct(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchMostSaleProductCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchMostSaleProductCopyWith(FetchMostSaleProduct value, $Res Function(FetchMostSaleProduct) _then) = _$FetchMostSaleProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchMostSaleProductCopyWithImpl<$Res>
    implements $FetchMostSaleProductCopyWith<$Res> {
  _$FetchMostSaleProductCopyWithImpl(this._self, this._then);

  final FetchMostSaleProduct _self;
  final $Res Function(FetchMostSaleProduct) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchMostSaleProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchAllProduct implements ProductEvent {
  const FetchAllProduct(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchAllProductCopyWith<FetchAllProduct> get copyWith => _$FetchAllProductCopyWithImpl<FetchAllProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchAllProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ProductEvent.fetchAllProduct(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchAllProductCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchAllProductCopyWith(FetchAllProduct value, $Res Function(FetchAllProduct) _then) = _$FetchAllProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchAllProductCopyWithImpl<$Res>
    implements $FetchAllProductCopyWith<$Res> {
  _$FetchAllProductCopyWithImpl(this._self, this._then);

  final FetchAllProduct _self;
  final $Res Function(FetchAllProduct) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchAllProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchNewProduct implements ProductEvent {
  const FetchNewProduct(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchNewProductCopyWith<FetchNewProduct> get copyWith => _$FetchNewProductCopyWithImpl<FetchNewProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchNewProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ProductEvent.fetchNewProduct(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchNewProductCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchNewProductCopyWith(FetchNewProduct value, $Res Function(FetchNewProduct) _then) = _$FetchNewProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchNewProductCopyWithImpl<$Res>
    implements $FetchNewProductCopyWith<$Res> {
  _$FetchNewProductCopyWithImpl(this._self, this._then);

  final FetchNewProduct _self;
  final $Res Function(FetchNewProduct) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchNewProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchLikeProduct implements ProductEvent {
  const FetchLikeProduct(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchLikeProductCopyWith<FetchLikeProduct> get copyWith => _$FetchLikeProductCopyWithImpl<FetchLikeProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchLikeProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ProductEvent.fetchLikeProduct(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchLikeProductCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchLikeProductCopyWith(FetchLikeProduct value, $Res Function(FetchLikeProduct) _then) = _$FetchLikeProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchLikeProductCopyWithImpl<$Res>
    implements $FetchLikeProductCopyWith<$Res> {
  _$FetchLikeProductCopyWithImpl(this._self, this._then);

  final FetchLikeProduct _self;
  final $Res Function(FetchLikeProduct) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchLikeProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchDiscountProduct implements ProductEvent {
  const FetchDiscountProduct(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchDiscountProductCopyWith<FetchDiscountProduct> get copyWith => _$FetchDiscountProductCopyWithImpl<FetchDiscountProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchDiscountProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'ProductEvent.fetchDiscountProduct(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchDiscountProductCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchDiscountProductCopyWith(FetchDiscountProduct value, $Res Function(FetchDiscountProduct) _then) = _$FetchDiscountProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchDiscountProductCopyWithImpl<$Res>
    implements $FetchDiscountProductCopyWith<$Res> {
  _$FetchDiscountProductCopyWithImpl(this._self, this._then);

  final FetchDiscountProduct _self;
  final $Res Function(FetchDiscountProduct) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchDiscountProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchMostSaleShopProduct implements ProductEvent {
  const FetchMostSaleShopProduct(this.context, {required this.shopId, this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  int? shopId;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMostSaleShopProductCopyWith<FetchMostSaleShopProduct> get copyWith => _$FetchMostSaleShopProductCopyWithImpl<FetchMostSaleShopProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMostSaleShopProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,shopId,isRefresh,controller);

@override
String toString() {
  return 'ProductEvent.fetchMostSaleShopProduct(context: $context, shopId: $shopId, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchMostSaleShopProductCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchMostSaleShopProductCopyWith(FetchMostSaleShopProduct value, $Res Function(FetchMostSaleShopProduct) _then) = _$FetchMostSaleShopProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? shopId, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchMostSaleShopProductCopyWithImpl<$Res>
    implements $FetchMostSaleShopProductCopyWith<$Res> {
  _$FetchMostSaleShopProductCopyWithImpl(this._self, this._then);

  final FetchMostSaleShopProduct _self;
  final $Res Function(FetchMostSaleShopProduct) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? shopId = freezed,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchMostSaleShopProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchNewShopProduct implements ProductEvent {
  const FetchNewShopProduct(this.context, {required this.shopId, this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  int? shopId;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchNewShopProductCopyWith<FetchNewShopProduct> get copyWith => _$FetchNewShopProductCopyWithImpl<FetchNewShopProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchNewShopProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,shopId,isRefresh,controller);

@override
String toString() {
  return 'ProductEvent.fetchNewShopProduct(context: $context, shopId: $shopId, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchNewShopProductCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchNewShopProductCopyWith(FetchNewShopProduct value, $Res Function(FetchNewShopProduct) _then) = _$FetchNewShopProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int? shopId, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchNewShopProductCopyWithImpl<$Res>
    implements $FetchNewShopProductCopyWith<$Res> {
  _$FetchNewShopProductCopyWithImpl(this._self, this._then);

  final FetchNewShopProduct _self;
  final $Res Function(FetchNewShopProduct) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? shopId = freezed,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchNewShopProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchProducts implements ProductEvent {
  const FetchProducts(this.context, {this.isRefresh, this.controller, final  List<ProductData>? list, this.isNewProduct, this.isMostSaleProduct, this.categoryId, this.shopId, this.bannerId, final  List<int>? brandId, final  List<int>? categoryIds, final  List<int>? extrasId, this.priceTo, this.priceFrom}): _list = list,_brandId = brandId,_categoryIds = categoryIds,_extrasId = extrasId;
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;
 final  List<ProductData>? _list;
 List<ProductData>? get list {
  final value = _list;
  if (value == null) return null;
  if (_list is EqualUnmodifiableListView) return _list;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  bool? isNewProduct;
 final  bool? isMostSaleProduct;
 final  int? categoryId;
 final  int? shopId;
 final  int? bannerId;
 final  List<int>? _brandId;
 List<int>? get brandId {
  final value = _brandId;
  if (value == null) return null;
  if (_brandId is EqualUnmodifiableListView) return _brandId;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<int>? _categoryIds;
 List<int>? get categoryIds {
  final value = _categoryIds;
  if (value == null) return null;
  if (_categoryIds is EqualUnmodifiableListView) return _categoryIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<int>? _extrasId;
 List<int>? get extrasId {
  final value = _extrasId;
  if (value == null) return null;
  if (_extrasId is EqualUnmodifiableListView) return _extrasId;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  num? priceTo;
 final  num? priceFrom;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchProductsCopyWith<FetchProducts> get copyWith => _$FetchProductsCopyWithImpl<FetchProducts>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchProducts&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller)&&const DeepCollectionEquality().equals(other._list, _list)&&(identical(other.isNewProduct, isNewProduct) || other.isNewProduct == isNewProduct)&&(identical(other.isMostSaleProduct, isMostSaleProduct) || other.isMostSaleProduct == isMostSaleProduct)&&(identical(other.categoryId, categoryId) || other.categoryId == categoryId)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.bannerId, bannerId) || other.bannerId == bannerId)&&const DeepCollectionEquality().equals(other._brandId, _brandId)&&const DeepCollectionEquality().equals(other._categoryIds, _categoryIds)&&const DeepCollectionEquality().equals(other._extrasId, _extrasId)&&(identical(other.priceTo, priceTo) || other.priceTo == priceTo)&&(identical(other.priceFrom, priceFrom) || other.priceFrom == priceFrom));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller,const DeepCollectionEquality().hash(_list),isNewProduct,isMostSaleProduct,categoryId,shopId,bannerId,const DeepCollectionEquality().hash(_brandId),const DeepCollectionEquality().hash(_categoryIds),const DeepCollectionEquality().hash(_extrasId),priceTo,priceFrom);

@override
String toString() {
  return 'ProductEvent.fetchProducts(context: $context, isRefresh: $isRefresh, controller: $controller, list: $list, isNewProduct: $isNewProduct, isMostSaleProduct: $isMostSaleProduct, categoryId: $categoryId, shopId: $shopId, bannerId: $bannerId, brandId: $brandId, categoryIds: $categoryIds, extrasId: $extrasId, priceTo: $priceTo, priceFrom: $priceFrom)';
}


}

/// @nodoc
abstract mixin class $FetchProductsCopyWith<$Res> implements $ProductEventCopyWith<$Res> {
  factory $FetchProductsCopyWith(FetchProducts value, $Res Function(FetchProducts) _then) = _$FetchProductsCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller, List<ProductData>? list, bool? isNewProduct, bool? isMostSaleProduct, int? categoryId, int? shopId, int? bannerId, List<int>? brandId, List<int>? categoryIds, List<int>? extrasId, num? priceTo, num? priceFrom
});




}
/// @nodoc
class _$FetchProductsCopyWithImpl<$Res>
    implements $FetchProductsCopyWith<$Res> {
  _$FetchProductsCopyWithImpl(this._self, this._then);

  final FetchProducts _self;
  final $Res Function(FetchProducts) _then;

/// Create a copy of ProductEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,Object? list = freezed,Object? isNewProduct = freezed,Object? isMostSaleProduct = freezed,Object? categoryId = freezed,Object? shopId = freezed,Object? bannerId = freezed,Object? brandId = freezed,Object? categoryIds = freezed,Object? extrasId = freezed,Object? priceTo = freezed,Object? priceFrom = freezed,}) {
  return _then(FetchProducts(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,list: freezed == list ? _self._list : list // ignore: cast_nullable_to_non_nullable
as List<ProductData>?,isNewProduct: freezed == isNewProduct ? _self.isNewProduct : isNewProduct // ignore: cast_nullable_to_non_nullable
as bool?,isMostSaleProduct: freezed == isMostSaleProduct ? _self.isMostSaleProduct : isMostSaleProduct // ignore: cast_nullable_to_non_nullable
as bool?,categoryId: freezed == categoryId ? _self.categoryId : categoryId // ignore: cast_nullable_to_non_nullable
as int?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,bannerId: freezed == bannerId ? _self.bannerId : bannerId // ignore: cast_nullable_to_non_nullable
as int?,brandId: freezed == brandId ? _self._brandId : brandId // ignore: cast_nullable_to_non_nullable
as List<int>?,categoryIds: freezed == categoryIds ? _self._categoryIds : categoryIds // ignore: cast_nullable_to_non_nullable
as List<int>?,extrasId: freezed == extrasId ? _self._extrasId : extrasId // ignore: cast_nullable_to_non_nullable
as List<int>?,priceTo: freezed == priceTo ? _self.priceTo : priceTo // ignore: cast_nullable_to_non_nullable
as num?,priceFrom: freezed == priceFrom ? _self.priceFrom : priceFrom // ignore: cast_nullable_to_non_nullable
as num?,
  ));
}


}

/// @nodoc


class UpdateState implements ProductEvent {
  const UpdateState();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateState);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'ProductEvent.updateState()';
}


}




/// @nodoc
mixin _$ProductState {

 List<ProductData> get mostSaleProduct; List<ProductData> get mostSaleShopProduct; List<ProductData> get newProduct; List<ProductData> get newShopProduct; List<ProductData> get commonProduct; List<ProductData> get likeProducts; List<ProductData> get discountProduct; List<ProductData> get allProductList; bool get isLoadingMostSale; bool get isLoadingNew; bool get isLoading; bool get isLoadingLike; bool get isLoadingDiscount; int get newProductCount; int get mostSaleProductCount; int get totalCount;
/// Create a copy of ProductState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductStateCopyWith<ProductState> get copyWith => _$ProductStateCopyWithImpl<ProductState>(this as ProductState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductState&&const DeepCollectionEquality().equals(other.mostSaleProduct, mostSaleProduct)&&const DeepCollectionEquality().equals(other.mostSaleShopProduct, mostSaleShopProduct)&&const DeepCollectionEquality().equals(other.newProduct, newProduct)&&const DeepCollectionEquality().equals(other.newShopProduct, newShopProduct)&&const DeepCollectionEquality().equals(other.commonProduct, commonProduct)&&const DeepCollectionEquality().equals(other.likeProducts, likeProducts)&&const DeepCollectionEquality().equals(other.discountProduct, discountProduct)&&const DeepCollectionEquality().equals(other.allProductList, allProductList)&&(identical(other.isLoadingMostSale, isLoadingMostSale) || other.isLoadingMostSale == isLoadingMostSale)&&(identical(other.isLoadingNew, isLoadingNew) || other.isLoadingNew == isLoadingNew)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isLoadingLike, isLoadingLike) || other.isLoadingLike == isLoadingLike)&&(identical(other.isLoadingDiscount, isLoadingDiscount) || other.isLoadingDiscount == isLoadingDiscount)&&(identical(other.newProductCount, newProductCount) || other.newProductCount == newProductCount)&&(identical(other.mostSaleProductCount, mostSaleProductCount) || other.mostSaleProductCount == mostSaleProductCount)&&(identical(other.totalCount, totalCount) || other.totalCount == totalCount));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(mostSaleProduct),const DeepCollectionEquality().hash(mostSaleShopProduct),const DeepCollectionEquality().hash(newProduct),const DeepCollectionEquality().hash(newShopProduct),const DeepCollectionEquality().hash(commonProduct),const DeepCollectionEquality().hash(likeProducts),const DeepCollectionEquality().hash(discountProduct),const DeepCollectionEquality().hash(allProductList),isLoadingMostSale,isLoadingNew,isLoading,isLoadingLike,isLoadingDiscount,newProductCount,mostSaleProductCount,totalCount);

@override
String toString() {
  return 'ProductState(mostSaleProduct: $mostSaleProduct, mostSaleShopProduct: $mostSaleShopProduct, newProduct: $newProduct, newShopProduct: $newShopProduct, commonProduct: $commonProduct, likeProducts: $likeProducts, discountProduct: $discountProduct, allProductList: $allProductList, isLoadingMostSale: $isLoadingMostSale, isLoadingNew: $isLoadingNew, isLoading: $isLoading, isLoadingLike: $isLoadingLike, isLoadingDiscount: $isLoadingDiscount, newProductCount: $newProductCount, mostSaleProductCount: $mostSaleProductCount, totalCount: $totalCount)';
}


}

/// @nodoc
abstract mixin class $ProductStateCopyWith<$Res>  {
  factory $ProductStateCopyWith(ProductState value, $Res Function(ProductState) _then) = _$ProductStateCopyWithImpl;
@useResult
$Res call({
 List<ProductData> mostSaleProduct, List<ProductData> mostSaleShopProduct, List<ProductData> newProduct, List<ProductData> newShopProduct, List<ProductData> commonProduct, List<ProductData> likeProducts, List<ProductData> discountProduct, List<ProductData> allProductList, bool isLoadingMostSale, bool isLoadingNew, bool isLoading, bool isLoadingLike, bool isLoadingDiscount, int newProductCount, int mostSaleProductCount, int totalCount
});




}
/// @nodoc
class _$ProductStateCopyWithImpl<$Res>
    implements $ProductStateCopyWith<$Res> {
  _$ProductStateCopyWithImpl(this._self, this._then);

  final ProductState _self;
  final $Res Function(ProductState) _then;

/// Create a copy of ProductState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? mostSaleProduct = null,Object? mostSaleShopProduct = null,Object? newProduct = null,Object? newShopProduct = null,Object? commonProduct = null,Object? likeProducts = null,Object? discountProduct = null,Object? allProductList = null,Object? isLoadingMostSale = null,Object? isLoadingNew = null,Object? isLoading = null,Object? isLoadingLike = null,Object? isLoadingDiscount = null,Object? newProductCount = null,Object? mostSaleProductCount = null,Object? totalCount = null,}) {
  return _then(_self.copyWith(
mostSaleProduct: null == mostSaleProduct ? _self.mostSaleProduct : mostSaleProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,mostSaleShopProduct: null == mostSaleShopProduct ? _self.mostSaleShopProduct : mostSaleShopProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,newProduct: null == newProduct ? _self.newProduct : newProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,newShopProduct: null == newShopProduct ? _self.newShopProduct : newShopProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,commonProduct: null == commonProduct ? _self.commonProduct : commonProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,likeProducts: null == likeProducts ? _self.likeProducts : likeProducts // ignore: cast_nullable_to_non_nullable
as List<ProductData>,discountProduct: null == discountProduct ? _self.discountProduct : discountProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,allProductList: null == allProductList ? _self.allProductList : allProductList // ignore: cast_nullable_to_non_nullable
as List<ProductData>,isLoadingMostSale: null == isLoadingMostSale ? _self.isLoadingMostSale : isLoadingMostSale // ignore: cast_nullable_to_non_nullable
as bool,isLoadingNew: null == isLoadingNew ? _self.isLoadingNew : isLoadingNew // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isLoadingLike: null == isLoadingLike ? _self.isLoadingLike : isLoadingLike // ignore: cast_nullable_to_non_nullable
as bool,isLoadingDiscount: null == isLoadingDiscount ? _self.isLoadingDiscount : isLoadingDiscount // ignore: cast_nullable_to_non_nullable
as bool,newProductCount: null == newProductCount ? _self.newProductCount : newProductCount // ignore: cast_nullable_to_non_nullable
as int,mostSaleProductCount: null == mostSaleProductCount ? _self.mostSaleProductCount : mostSaleProductCount // ignore: cast_nullable_to_non_nullable
as int,totalCount: null == totalCount ? _self.totalCount : totalCount // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [ProductState].
extension ProductStatePatterns on ProductState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductState value)  $default,){
final _that = this;
switch (_that) {
case _ProductState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductState value)?  $default,){
final _that = this;
switch (_that) {
case _ProductState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<ProductData> mostSaleProduct,  List<ProductData> mostSaleShopProduct,  List<ProductData> newProduct,  List<ProductData> newShopProduct,  List<ProductData> commonProduct,  List<ProductData> likeProducts,  List<ProductData> discountProduct,  List<ProductData> allProductList,  bool isLoadingMostSale,  bool isLoadingNew,  bool isLoading,  bool isLoadingLike,  bool isLoadingDiscount,  int newProductCount,  int mostSaleProductCount,  int totalCount)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductState() when $default != null:
return $default(_that.mostSaleProduct,_that.mostSaleShopProduct,_that.newProduct,_that.newShopProduct,_that.commonProduct,_that.likeProducts,_that.discountProduct,_that.allProductList,_that.isLoadingMostSale,_that.isLoadingNew,_that.isLoading,_that.isLoadingLike,_that.isLoadingDiscount,_that.newProductCount,_that.mostSaleProductCount,_that.totalCount);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<ProductData> mostSaleProduct,  List<ProductData> mostSaleShopProduct,  List<ProductData> newProduct,  List<ProductData> newShopProduct,  List<ProductData> commonProduct,  List<ProductData> likeProducts,  List<ProductData> discountProduct,  List<ProductData> allProductList,  bool isLoadingMostSale,  bool isLoadingNew,  bool isLoading,  bool isLoadingLike,  bool isLoadingDiscount,  int newProductCount,  int mostSaleProductCount,  int totalCount)  $default,) {final _that = this;
switch (_that) {
case _ProductState():
return $default(_that.mostSaleProduct,_that.mostSaleShopProduct,_that.newProduct,_that.newShopProduct,_that.commonProduct,_that.likeProducts,_that.discountProduct,_that.allProductList,_that.isLoadingMostSale,_that.isLoadingNew,_that.isLoading,_that.isLoadingLike,_that.isLoadingDiscount,_that.newProductCount,_that.mostSaleProductCount,_that.totalCount);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<ProductData> mostSaleProduct,  List<ProductData> mostSaleShopProduct,  List<ProductData> newProduct,  List<ProductData> newShopProduct,  List<ProductData> commonProduct,  List<ProductData> likeProducts,  List<ProductData> discountProduct,  List<ProductData> allProductList,  bool isLoadingMostSale,  bool isLoadingNew,  bool isLoading,  bool isLoadingLike,  bool isLoadingDiscount,  int newProductCount,  int mostSaleProductCount,  int totalCount)?  $default,) {final _that = this;
switch (_that) {
case _ProductState() when $default != null:
return $default(_that.mostSaleProduct,_that.mostSaleShopProduct,_that.newProduct,_that.newShopProduct,_that.commonProduct,_that.likeProducts,_that.discountProduct,_that.allProductList,_that.isLoadingMostSale,_that.isLoadingNew,_that.isLoading,_that.isLoadingLike,_that.isLoadingDiscount,_that.newProductCount,_that.mostSaleProductCount,_that.totalCount);case _:
  return null;

}
}

}

/// @nodoc


class _ProductState implements ProductState {
  const _ProductState({final  List<ProductData> mostSaleProduct = const [], final  List<ProductData> mostSaleShopProduct = const [], final  List<ProductData> newProduct = const [], final  List<ProductData> newShopProduct = const [], final  List<ProductData> commonProduct = const [], final  List<ProductData> likeProducts = const [], final  List<ProductData> discountProduct = const [], final  List<ProductData> allProductList = const [], this.isLoadingMostSale = true, this.isLoadingNew = true, this.isLoading = true, this.isLoadingLike = true, this.isLoadingDiscount = true, this.newProductCount = 0, this.mostSaleProductCount = 0, this.totalCount = 0}): _mostSaleProduct = mostSaleProduct,_mostSaleShopProduct = mostSaleShopProduct,_newProduct = newProduct,_newShopProduct = newShopProduct,_commonProduct = commonProduct,_likeProducts = likeProducts,_discountProduct = discountProduct,_allProductList = allProductList;
  

 final  List<ProductData> _mostSaleProduct;
@override@JsonKey() List<ProductData> get mostSaleProduct {
  if (_mostSaleProduct is EqualUnmodifiableListView) return _mostSaleProduct;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_mostSaleProduct);
}

 final  List<ProductData> _mostSaleShopProduct;
@override@JsonKey() List<ProductData> get mostSaleShopProduct {
  if (_mostSaleShopProduct is EqualUnmodifiableListView) return _mostSaleShopProduct;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_mostSaleShopProduct);
}

 final  List<ProductData> _newProduct;
@override@JsonKey() List<ProductData> get newProduct {
  if (_newProduct is EqualUnmodifiableListView) return _newProduct;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_newProduct);
}

 final  List<ProductData> _newShopProduct;
@override@JsonKey() List<ProductData> get newShopProduct {
  if (_newShopProduct is EqualUnmodifiableListView) return _newShopProduct;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_newShopProduct);
}

 final  List<ProductData> _commonProduct;
@override@JsonKey() List<ProductData> get commonProduct {
  if (_commonProduct is EqualUnmodifiableListView) return _commonProduct;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_commonProduct);
}

 final  List<ProductData> _likeProducts;
@override@JsonKey() List<ProductData> get likeProducts {
  if (_likeProducts is EqualUnmodifiableListView) return _likeProducts;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_likeProducts);
}

 final  List<ProductData> _discountProduct;
@override@JsonKey() List<ProductData> get discountProduct {
  if (_discountProduct is EqualUnmodifiableListView) return _discountProduct;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_discountProduct);
}

 final  List<ProductData> _allProductList;
@override@JsonKey() List<ProductData> get allProductList {
  if (_allProductList is EqualUnmodifiableListView) return _allProductList;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_allProductList);
}

@override@JsonKey() final  bool isLoadingMostSale;
@override@JsonKey() final  bool isLoadingNew;
@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isLoadingLike;
@override@JsonKey() final  bool isLoadingDiscount;
@override@JsonKey() final  int newProductCount;
@override@JsonKey() final  int mostSaleProductCount;
@override@JsonKey() final  int totalCount;

/// Create a copy of ProductState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductStateCopyWith<_ProductState> get copyWith => __$ProductStateCopyWithImpl<_ProductState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductState&&const DeepCollectionEquality().equals(other._mostSaleProduct, _mostSaleProduct)&&const DeepCollectionEquality().equals(other._mostSaleShopProduct, _mostSaleShopProduct)&&const DeepCollectionEquality().equals(other._newProduct, _newProduct)&&const DeepCollectionEquality().equals(other._newShopProduct, _newShopProduct)&&const DeepCollectionEquality().equals(other._commonProduct, _commonProduct)&&const DeepCollectionEquality().equals(other._likeProducts, _likeProducts)&&const DeepCollectionEquality().equals(other._discountProduct, _discountProduct)&&const DeepCollectionEquality().equals(other._allProductList, _allProductList)&&(identical(other.isLoadingMostSale, isLoadingMostSale) || other.isLoadingMostSale == isLoadingMostSale)&&(identical(other.isLoadingNew, isLoadingNew) || other.isLoadingNew == isLoadingNew)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isLoadingLike, isLoadingLike) || other.isLoadingLike == isLoadingLike)&&(identical(other.isLoadingDiscount, isLoadingDiscount) || other.isLoadingDiscount == isLoadingDiscount)&&(identical(other.newProductCount, newProductCount) || other.newProductCount == newProductCount)&&(identical(other.mostSaleProductCount, mostSaleProductCount) || other.mostSaleProductCount == mostSaleProductCount)&&(identical(other.totalCount, totalCount) || other.totalCount == totalCount));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_mostSaleProduct),const DeepCollectionEquality().hash(_mostSaleShopProduct),const DeepCollectionEquality().hash(_newProduct),const DeepCollectionEquality().hash(_newShopProduct),const DeepCollectionEquality().hash(_commonProduct),const DeepCollectionEquality().hash(_likeProducts),const DeepCollectionEquality().hash(_discountProduct),const DeepCollectionEquality().hash(_allProductList),isLoadingMostSale,isLoadingNew,isLoading,isLoadingLike,isLoadingDiscount,newProductCount,mostSaleProductCount,totalCount);

@override
String toString() {
  return 'ProductState(mostSaleProduct: $mostSaleProduct, mostSaleShopProduct: $mostSaleShopProduct, newProduct: $newProduct, newShopProduct: $newShopProduct, commonProduct: $commonProduct, likeProducts: $likeProducts, discountProduct: $discountProduct, allProductList: $allProductList, isLoadingMostSale: $isLoadingMostSale, isLoadingNew: $isLoadingNew, isLoading: $isLoading, isLoadingLike: $isLoadingLike, isLoadingDiscount: $isLoadingDiscount, newProductCount: $newProductCount, mostSaleProductCount: $mostSaleProductCount, totalCount: $totalCount)';
}


}

/// @nodoc
abstract mixin class _$ProductStateCopyWith<$Res> implements $ProductStateCopyWith<$Res> {
  factory _$ProductStateCopyWith(_ProductState value, $Res Function(_ProductState) _then) = __$ProductStateCopyWithImpl;
@override @useResult
$Res call({
 List<ProductData> mostSaleProduct, List<ProductData> mostSaleShopProduct, List<ProductData> newProduct, List<ProductData> newShopProduct, List<ProductData> commonProduct, List<ProductData> likeProducts, List<ProductData> discountProduct, List<ProductData> allProductList, bool isLoadingMostSale, bool isLoadingNew, bool isLoading, bool isLoadingLike, bool isLoadingDiscount, int newProductCount, int mostSaleProductCount, int totalCount
});




}
/// @nodoc
class __$ProductStateCopyWithImpl<$Res>
    implements _$ProductStateCopyWith<$Res> {
  __$ProductStateCopyWithImpl(this._self, this._then);

  final _ProductState _self;
  final $Res Function(_ProductState) _then;

/// Create a copy of ProductState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? mostSaleProduct = null,Object? mostSaleShopProduct = null,Object? newProduct = null,Object? newShopProduct = null,Object? commonProduct = null,Object? likeProducts = null,Object? discountProduct = null,Object? allProductList = null,Object? isLoadingMostSale = null,Object? isLoadingNew = null,Object? isLoading = null,Object? isLoadingLike = null,Object? isLoadingDiscount = null,Object? newProductCount = null,Object? mostSaleProductCount = null,Object? totalCount = null,}) {
  return _then(_ProductState(
mostSaleProduct: null == mostSaleProduct ? _self._mostSaleProduct : mostSaleProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,mostSaleShopProduct: null == mostSaleShopProduct ? _self._mostSaleShopProduct : mostSaleShopProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,newProduct: null == newProduct ? _self._newProduct : newProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,newShopProduct: null == newShopProduct ? _self._newShopProduct : newShopProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,commonProduct: null == commonProduct ? _self._commonProduct : commonProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,likeProducts: null == likeProducts ? _self._likeProducts : likeProducts // ignore: cast_nullable_to_non_nullable
as List<ProductData>,discountProduct: null == discountProduct ? _self._discountProduct : discountProduct // ignore: cast_nullable_to_non_nullable
as List<ProductData>,allProductList: null == allProductList ? _self._allProductList : allProductList // ignore: cast_nullable_to_non_nullable
as List<ProductData>,isLoadingMostSale: null == isLoadingMostSale ? _self.isLoadingMostSale : isLoadingMostSale // ignore: cast_nullable_to_non_nullable
as bool,isLoadingNew: null == isLoadingNew ? _self.isLoadingNew : isLoadingNew // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isLoadingLike: null == isLoadingLike ? _self.isLoadingLike : isLoadingLike // ignore: cast_nullable_to_non_nullable
as bool,isLoadingDiscount: null == isLoadingDiscount ? _self.isLoadingDiscount : isLoadingDiscount // ignore: cast_nullable_to_non_nullable
as bool,newProductCount: null == newProductCount ? _self.newProductCount : newProductCount // ignore: cast_nullable_to_non_nullable
as int,mostSaleProductCount: null == mostSaleProductCount ? _self.mostSaleProductCount : mostSaleProductCount // ignore: cast_nullable_to_non_nullable
as int,totalCount: null == totalCount ? _self.totalCount : totalCount // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

// dart format on
