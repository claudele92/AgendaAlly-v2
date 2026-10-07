// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'banner_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$BannerEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BannerEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'BannerEvent()';
}


}

/// @nodoc
class $BannerEventCopyWith<$Res>  {
$BannerEventCopyWith(BannerEvent _, $Res Function(BannerEvent) __);
}


/// Adds pattern-matching-related methods to [BannerEvent].
extension BannerEventPatterns on BannerEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchBanner value)?  fetchBanner,TResult Function( FetchAdsBanner value)?  fetchAdsBanner,TResult Function( FetchLooks value)?  fetchLooks,TResult Function( FetchProduct value)?  fetchProduct,TResult Function( UpdateProduct value)?  updateProduct,TResult Function( FetchAdsProduct value)?  fetchAdsProduct,TResult Function( FetchAdsListProduct value)?  fetchAdsListProduct,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchBanner() when fetchBanner != null:
return fetchBanner(_that);case FetchAdsBanner() when fetchAdsBanner != null:
return fetchAdsBanner(_that);case FetchLooks() when fetchLooks != null:
return fetchLooks(_that);case FetchProduct() when fetchProduct != null:
return fetchProduct(_that);case UpdateProduct() when updateProduct != null:
return updateProduct(_that);case FetchAdsProduct() when fetchAdsProduct != null:
return fetchAdsProduct(_that);case FetchAdsListProduct() when fetchAdsListProduct != null:
return fetchAdsListProduct(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchBanner value)  fetchBanner,required TResult Function( FetchAdsBanner value)  fetchAdsBanner,required TResult Function( FetchLooks value)  fetchLooks,required TResult Function( FetchProduct value)  fetchProduct,required TResult Function( UpdateProduct value)  updateProduct,required TResult Function( FetchAdsProduct value)  fetchAdsProduct,required TResult Function( FetchAdsListProduct value)  fetchAdsListProduct,}){
final _that = this;
switch (_that) {
case FetchBanner():
return fetchBanner(_that);case FetchAdsBanner():
return fetchAdsBanner(_that);case FetchLooks():
return fetchLooks(_that);case FetchProduct():
return fetchProduct(_that);case UpdateProduct():
return updateProduct(_that);case FetchAdsProduct():
return fetchAdsProduct(_that);case FetchAdsListProduct():
return fetchAdsListProduct(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchBanner value)?  fetchBanner,TResult? Function( FetchAdsBanner value)?  fetchAdsBanner,TResult? Function( FetchLooks value)?  fetchLooks,TResult? Function( FetchProduct value)?  fetchProduct,TResult? Function( UpdateProduct value)?  updateProduct,TResult? Function( FetchAdsProduct value)?  fetchAdsProduct,TResult? Function( FetchAdsListProduct value)?  fetchAdsListProduct,}){
final _that = this;
switch (_that) {
case FetchBanner() when fetchBanner != null:
return fetchBanner(_that);case FetchAdsBanner() when fetchAdsBanner != null:
return fetchAdsBanner(_that);case FetchLooks() when fetchLooks != null:
return fetchLooks(_that);case FetchProduct() when fetchProduct != null:
return fetchProduct(_that);case UpdateProduct() when updateProduct != null:
return updateProduct(_that);case FetchAdsProduct() when fetchAdsProduct != null:
return fetchAdsProduct(_that);case FetchAdsListProduct() when fetchAdsListProduct != null:
return fetchAdsListProduct(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBanner,TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchAdsBanner,TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchLooks,TResult Function( BuildContext context,  int id)?  fetchProduct,TResult Function()?  updateProduct,TResult Function( BuildContext context,  int id)?  fetchAdsProduct,TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchAdsListProduct,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchBanner() when fetchBanner != null:
return fetchBanner(_that.context,_that.isRefresh,_that.controller);case FetchAdsBanner() when fetchAdsBanner != null:
return fetchAdsBanner(_that.context,_that.isRefresh,_that.controller);case FetchLooks() when fetchLooks != null:
return fetchLooks(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchProduct() when fetchProduct != null:
return fetchProduct(_that.context,_that.id);case UpdateProduct() when updateProduct != null:
return updateProduct();case FetchAdsProduct() when fetchAdsProduct != null:
return fetchAdsProduct(_that.context,_that.id);case FetchAdsListProduct() when fetchAdsListProduct != null:
return fetchAdsListProduct(_that.context,_that.isRefresh,_that.shopId,_that.controller);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchBanner,required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchAdsBanner,required TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)  fetchLooks,required TResult Function( BuildContext context,  int id)  fetchProduct,required TResult Function()  updateProduct,required TResult Function( BuildContext context,  int id)  fetchAdsProduct,required TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)  fetchAdsListProduct,}) {final _that = this;
switch (_that) {
case FetchBanner():
return fetchBanner(_that.context,_that.isRefresh,_that.controller);case FetchAdsBanner():
return fetchAdsBanner(_that.context,_that.isRefresh,_that.controller);case FetchLooks():
return fetchLooks(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchProduct():
return fetchProduct(_that.context,_that.id);case UpdateProduct():
return updateProduct();case FetchAdsProduct():
return fetchAdsProduct(_that.context,_that.id);case FetchAdsListProduct():
return fetchAdsListProduct(_that.context,_that.isRefresh,_that.shopId,_that.controller);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBanner,TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchAdsBanner,TResult? Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchLooks,TResult? Function( BuildContext context,  int id)?  fetchProduct,TResult? Function()?  updateProduct,TResult? Function( BuildContext context,  int id)?  fetchAdsProduct,TResult? Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchAdsListProduct,}) {final _that = this;
switch (_that) {
case FetchBanner() when fetchBanner != null:
return fetchBanner(_that.context,_that.isRefresh,_that.controller);case FetchAdsBanner() when fetchAdsBanner != null:
return fetchAdsBanner(_that.context,_that.isRefresh,_that.controller);case FetchLooks() when fetchLooks != null:
return fetchLooks(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchProduct() when fetchProduct != null:
return fetchProduct(_that.context,_that.id);case UpdateProduct() when updateProduct != null:
return updateProduct();case FetchAdsProduct() when fetchAdsProduct != null:
return fetchAdsProduct(_that.context,_that.id);case FetchAdsListProduct() when fetchAdsListProduct != null:
return fetchAdsListProduct(_that.context,_that.isRefresh,_that.shopId,_that.controller);case _:
  return null;

}
}

}

/// @nodoc


class FetchBanner implements BannerEvent {
  const FetchBanner(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchBannerCopyWith<FetchBanner> get copyWith => _$FetchBannerCopyWithImpl<FetchBanner>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchBanner&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'BannerEvent.fetchBanner(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchBannerCopyWith<$Res> implements $BannerEventCopyWith<$Res> {
  factory $FetchBannerCopyWith(FetchBanner value, $Res Function(FetchBanner) _then) = _$FetchBannerCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchBannerCopyWithImpl<$Res>
    implements $FetchBannerCopyWith<$Res> {
  _$FetchBannerCopyWithImpl(this._self, this._then);

  final FetchBanner _self;
  final $Res Function(FetchBanner) _then;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchBanner(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchAdsBanner implements BannerEvent {
  const FetchAdsBanner(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchAdsBannerCopyWith<FetchAdsBanner> get copyWith => _$FetchAdsBannerCopyWithImpl<FetchAdsBanner>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchAdsBanner&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'BannerEvent.fetchAdsBanner(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchAdsBannerCopyWith<$Res> implements $BannerEventCopyWith<$Res> {
  factory $FetchAdsBannerCopyWith(FetchAdsBanner value, $Res Function(FetchAdsBanner) _then) = _$FetchAdsBannerCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchAdsBannerCopyWithImpl<$Res>
    implements $FetchAdsBannerCopyWith<$Res> {
  _$FetchAdsBannerCopyWithImpl(this._self, this._then);

  final FetchAdsBanner _self;
  final $Res Function(FetchAdsBanner) _then;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchAdsBanner(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchLooks implements BannerEvent {
  const FetchLooks(this.context, {this.isRefresh, this.shopId, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  int? shopId;
 final  RefreshController? controller;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchLooksCopyWith<FetchLooks> get copyWith => _$FetchLooksCopyWithImpl<FetchLooks>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchLooks&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,controller);

@override
String toString() {
  return 'BannerEvent.fetchLooks(context: $context, isRefresh: $isRefresh, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchLooksCopyWith<$Res> implements $BannerEventCopyWith<$Res> {
  factory $FetchLooksCopyWith(FetchLooks value, $Res Function(FetchLooks) _then) = _$FetchLooksCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$FetchLooksCopyWithImpl<$Res>
    implements $FetchLooksCopyWith<$Res> {
  _$FetchLooksCopyWithImpl(this._self, this._then);

  final FetchLooks _self;
  final $Res Function(FetchLooks) _then;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(FetchLooks(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchProduct implements BannerEvent {
  const FetchProduct(this.context, {required this.id});
  

 final  BuildContext context;
 final  int id;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchProductCopyWith<FetchProduct> get copyWith => _$FetchProductCopyWithImpl<FetchProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,context,id);

@override
String toString() {
  return 'BannerEvent.fetchProduct(context: $context, id: $id)';
}


}

/// @nodoc
abstract mixin class $FetchProductCopyWith<$Res> implements $BannerEventCopyWith<$Res> {
  factory $FetchProductCopyWith(FetchProduct value, $Res Function(FetchProduct) _then) = _$FetchProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int id
});




}
/// @nodoc
class _$FetchProductCopyWithImpl<$Res>
    implements $FetchProductCopyWith<$Res> {
  _$FetchProductCopyWithImpl(this._self, this._then);

  final FetchProduct _self;
  final $Res Function(FetchProduct) _then;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,}) {
  return _then(FetchProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class UpdateProduct implements BannerEvent {
  const UpdateProduct();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateProduct);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'BannerEvent.updateProduct()';
}


}




/// @nodoc


class FetchAdsProduct implements BannerEvent {
  const FetchAdsProduct(this.context, {required this.id});
  

 final  BuildContext context;
 final  int id;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchAdsProductCopyWith<FetchAdsProduct> get copyWith => _$FetchAdsProductCopyWithImpl<FetchAdsProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchAdsProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,context,id);

@override
String toString() {
  return 'BannerEvent.fetchAdsProduct(context: $context, id: $id)';
}


}

/// @nodoc
abstract mixin class $FetchAdsProductCopyWith<$Res> implements $BannerEventCopyWith<$Res> {
  factory $FetchAdsProductCopyWith(FetchAdsProduct value, $Res Function(FetchAdsProduct) _then) = _$FetchAdsProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int id
});




}
/// @nodoc
class _$FetchAdsProductCopyWithImpl<$Res>
    implements $FetchAdsProductCopyWith<$Res> {
  _$FetchAdsProductCopyWithImpl(this._self, this._then);

  final FetchAdsProduct _self;
  final $Res Function(FetchAdsProduct) _then;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,}) {
  return _then(FetchAdsProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class FetchAdsListProduct implements BannerEvent {
  const FetchAdsListProduct(this.context, {this.isRefresh, this.shopId, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  int? shopId;
 final  RefreshController? controller;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchAdsListProductCopyWith<FetchAdsListProduct> get copyWith => _$FetchAdsListProductCopyWithImpl<FetchAdsListProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchAdsListProduct&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,controller);

@override
String toString() {
  return 'BannerEvent.fetchAdsListProduct(context: $context, isRefresh: $isRefresh, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchAdsListProductCopyWith<$Res> implements $BannerEventCopyWith<$Res> {
  factory $FetchAdsListProductCopyWith(FetchAdsListProduct value, $Res Function(FetchAdsListProduct) _then) = _$FetchAdsListProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$FetchAdsListProductCopyWithImpl<$Res>
    implements $FetchAdsListProductCopyWith<$Res> {
  _$FetchAdsListProductCopyWithImpl(this._self, this._then);

  final FetchAdsListProduct _self;
  final $Res Function(FetchAdsListProduct) _then;

/// Create a copy of BannerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(FetchAdsListProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc
mixin _$BannerState {

 List<BannerData> get banners; List<BannerData> get adsBanners; List<BannerData> get looks; List<ProductData> get products; List<ShopAdsPackage> get shopAds; List<AdModel> get shopListAds; bool get isLoadingBanner; bool get isLoadingProduct;
/// Create a copy of BannerState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BannerStateCopyWith<BannerState> get copyWith => _$BannerStateCopyWithImpl<BannerState>(this as BannerState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BannerState&&const DeepCollectionEquality().equals(other.banners, banners)&&const DeepCollectionEquality().equals(other.adsBanners, adsBanners)&&const DeepCollectionEquality().equals(other.looks, looks)&&const DeepCollectionEquality().equals(other.products, products)&&const DeepCollectionEquality().equals(other.shopAds, shopAds)&&const DeepCollectionEquality().equals(other.shopListAds, shopListAds)&&(identical(other.isLoadingBanner, isLoadingBanner) || other.isLoadingBanner == isLoadingBanner)&&(identical(other.isLoadingProduct, isLoadingProduct) || other.isLoadingProduct == isLoadingProduct));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(banners),const DeepCollectionEquality().hash(adsBanners),const DeepCollectionEquality().hash(looks),const DeepCollectionEquality().hash(products),const DeepCollectionEquality().hash(shopAds),const DeepCollectionEquality().hash(shopListAds),isLoadingBanner,isLoadingProduct);

@override
String toString() {
  return 'BannerState(banners: $banners, adsBanners: $adsBanners, looks: $looks, products: $products, shopAds: $shopAds, shopListAds: $shopListAds, isLoadingBanner: $isLoadingBanner, isLoadingProduct: $isLoadingProduct)';
}


}

/// @nodoc
abstract mixin class $BannerStateCopyWith<$Res>  {
  factory $BannerStateCopyWith(BannerState value, $Res Function(BannerState) _then) = _$BannerStateCopyWithImpl;
@useResult
$Res call({
 List<BannerData> banners, List<BannerData> adsBanners, List<BannerData> looks, List<ProductData> products, List<ShopAdsPackage> shopAds, List<AdModel> shopListAds, bool isLoadingBanner, bool isLoadingProduct
});




}
/// @nodoc
class _$BannerStateCopyWithImpl<$Res>
    implements $BannerStateCopyWith<$Res> {
  _$BannerStateCopyWithImpl(this._self, this._then);

  final BannerState _self;
  final $Res Function(BannerState) _then;

/// Create a copy of BannerState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? banners = null,Object? adsBanners = null,Object? looks = null,Object? products = null,Object? shopAds = null,Object? shopListAds = null,Object? isLoadingBanner = null,Object? isLoadingProduct = null,}) {
  return _then(_self.copyWith(
banners: null == banners ? _self.banners : banners // ignore: cast_nullable_to_non_nullable
as List<BannerData>,adsBanners: null == adsBanners ? _self.adsBanners : adsBanners // ignore: cast_nullable_to_non_nullable
as List<BannerData>,looks: null == looks ? _self.looks : looks // ignore: cast_nullable_to_non_nullable
as List<BannerData>,products: null == products ? _self.products : products // ignore: cast_nullable_to_non_nullable
as List<ProductData>,shopAds: null == shopAds ? _self.shopAds : shopAds // ignore: cast_nullable_to_non_nullable
as List<ShopAdsPackage>,shopListAds: null == shopListAds ? _self.shopListAds : shopListAds // ignore: cast_nullable_to_non_nullable
as List<AdModel>,isLoadingBanner: null == isLoadingBanner ? _self.isLoadingBanner : isLoadingBanner // ignore: cast_nullable_to_non_nullable
as bool,isLoadingProduct: null == isLoadingProduct ? _self.isLoadingProduct : isLoadingProduct // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [BannerState].
extension BannerStatePatterns on BannerState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BannerState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BannerState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BannerState value)  $default,){
final _that = this;
switch (_that) {
case _BannerState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BannerState value)?  $default,){
final _that = this;
switch (_that) {
case _BannerState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<BannerData> banners,  List<BannerData> adsBanners,  List<BannerData> looks,  List<ProductData> products,  List<ShopAdsPackage> shopAds,  List<AdModel> shopListAds,  bool isLoadingBanner,  bool isLoadingProduct)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BannerState() when $default != null:
return $default(_that.banners,_that.adsBanners,_that.looks,_that.products,_that.shopAds,_that.shopListAds,_that.isLoadingBanner,_that.isLoadingProduct);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<BannerData> banners,  List<BannerData> adsBanners,  List<BannerData> looks,  List<ProductData> products,  List<ShopAdsPackage> shopAds,  List<AdModel> shopListAds,  bool isLoadingBanner,  bool isLoadingProduct)  $default,) {final _that = this;
switch (_that) {
case _BannerState():
return $default(_that.banners,_that.adsBanners,_that.looks,_that.products,_that.shopAds,_that.shopListAds,_that.isLoadingBanner,_that.isLoadingProduct);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<BannerData> banners,  List<BannerData> adsBanners,  List<BannerData> looks,  List<ProductData> products,  List<ShopAdsPackage> shopAds,  List<AdModel> shopListAds,  bool isLoadingBanner,  bool isLoadingProduct)?  $default,) {final _that = this;
switch (_that) {
case _BannerState() when $default != null:
return $default(_that.banners,_that.adsBanners,_that.looks,_that.products,_that.shopAds,_that.shopListAds,_that.isLoadingBanner,_that.isLoadingProduct);case _:
  return null;

}
}

}

/// @nodoc


class _BannerState implements BannerState {
  const _BannerState({final  List<BannerData> banners = const [], final  List<BannerData> adsBanners = const [], final  List<BannerData> looks = const [], final  List<ProductData> products = const [], final  List<ShopAdsPackage> shopAds = const [], final  List<AdModel> shopListAds = const [], this.isLoadingBanner = true, this.isLoadingProduct = true}): _banners = banners,_adsBanners = adsBanners,_looks = looks,_products = products,_shopAds = shopAds,_shopListAds = shopListAds;
  

 final  List<BannerData> _banners;
@override@JsonKey() List<BannerData> get banners {
  if (_banners is EqualUnmodifiableListView) return _banners;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_banners);
}

 final  List<BannerData> _adsBanners;
@override@JsonKey() List<BannerData> get adsBanners {
  if (_adsBanners is EqualUnmodifiableListView) return _adsBanners;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_adsBanners);
}

 final  List<BannerData> _looks;
@override@JsonKey() List<BannerData> get looks {
  if (_looks is EqualUnmodifiableListView) return _looks;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_looks);
}

 final  List<ProductData> _products;
@override@JsonKey() List<ProductData> get products {
  if (_products is EqualUnmodifiableListView) return _products;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_products);
}

 final  List<ShopAdsPackage> _shopAds;
@override@JsonKey() List<ShopAdsPackage> get shopAds {
  if (_shopAds is EqualUnmodifiableListView) return _shopAds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_shopAds);
}

 final  List<AdModel> _shopListAds;
@override@JsonKey() List<AdModel> get shopListAds {
  if (_shopListAds is EqualUnmodifiableListView) return _shopListAds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_shopListAds);
}

@override@JsonKey() final  bool isLoadingBanner;
@override@JsonKey() final  bool isLoadingProduct;

/// Create a copy of BannerState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BannerStateCopyWith<_BannerState> get copyWith => __$BannerStateCopyWithImpl<_BannerState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BannerState&&const DeepCollectionEquality().equals(other._banners, _banners)&&const DeepCollectionEquality().equals(other._adsBanners, _adsBanners)&&const DeepCollectionEquality().equals(other._looks, _looks)&&const DeepCollectionEquality().equals(other._products, _products)&&const DeepCollectionEquality().equals(other._shopAds, _shopAds)&&const DeepCollectionEquality().equals(other._shopListAds, _shopListAds)&&(identical(other.isLoadingBanner, isLoadingBanner) || other.isLoadingBanner == isLoadingBanner)&&(identical(other.isLoadingProduct, isLoadingProduct) || other.isLoadingProduct == isLoadingProduct));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_banners),const DeepCollectionEquality().hash(_adsBanners),const DeepCollectionEquality().hash(_looks),const DeepCollectionEquality().hash(_products),const DeepCollectionEquality().hash(_shopAds),const DeepCollectionEquality().hash(_shopListAds),isLoadingBanner,isLoadingProduct);

@override
String toString() {
  return 'BannerState(banners: $banners, adsBanners: $adsBanners, looks: $looks, products: $products, shopAds: $shopAds, shopListAds: $shopListAds, isLoadingBanner: $isLoadingBanner, isLoadingProduct: $isLoadingProduct)';
}


}

/// @nodoc
abstract mixin class _$BannerStateCopyWith<$Res> implements $BannerStateCopyWith<$Res> {
  factory _$BannerStateCopyWith(_BannerState value, $Res Function(_BannerState) _then) = __$BannerStateCopyWithImpl;
@override @useResult
$Res call({
 List<BannerData> banners, List<BannerData> adsBanners, List<BannerData> looks, List<ProductData> products, List<ShopAdsPackage> shopAds, List<AdModel> shopListAds, bool isLoadingBanner, bool isLoadingProduct
});




}
/// @nodoc
class __$BannerStateCopyWithImpl<$Res>
    implements _$BannerStateCopyWith<$Res> {
  __$BannerStateCopyWithImpl(this._self, this._then);

  final _BannerState _self;
  final $Res Function(_BannerState) _then;

/// Create a copy of BannerState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? banners = null,Object? adsBanners = null,Object? looks = null,Object? products = null,Object? shopAds = null,Object? shopListAds = null,Object? isLoadingBanner = null,Object? isLoadingProduct = null,}) {
  return _then(_BannerState(
banners: null == banners ? _self._banners : banners // ignore: cast_nullable_to_non_nullable
as List<BannerData>,adsBanners: null == adsBanners ? _self._adsBanners : adsBanners // ignore: cast_nullable_to_non_nullable
as List<BannerData>,looks: null == looks ? _self._looks : looks // ignore: cast_nullable_to_non_nullable
as List<BannerData>,products: null == products ? _self._products : products // ignore: cast_nullable_to_non_nullable
as List<ProductData>,shopAds: null == shopAds ? _self._shopAds : shopAds // ignore: cast_nullable_to_non_nullable
as List<ShopAdsPackage>,shopListAds: null == shopListAds ? _self._shopListAds : shopListAds // ignore: cast_nullable_to_non_nullable
as List<AdModel>,isLoadingBanner: null == isLoadingBanner ? _self.isLoadingBanner : isLoadingBanner // ignore: cast_nullable_to_non_nullable
as bool,isLoadingProduct: null == isLoadingProduct ? _self.isLoadingProduct : isLoadingProduct // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
