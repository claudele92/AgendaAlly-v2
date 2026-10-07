// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'search_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$SearchEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'SearchEvent()';
}


}

/// @nodoc
class $SearchEventCopyWith<$Res>  {
$SearchEventCopyWith(SearchEvent _, $Res Function(SearchEvent) __);
}


/// Adds pattern-matching-related methods to [SearchEvent].
extension SearchEventPatterns on SearchEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( SetQuery value)?  setQuery,TResult Function( SearchShops value)?  searchShops,TResult Function( SearchProduct value)?  searchProduct,TResult Function( SearchService value)?  searchService,TResult Function( SearchCategory value)?  searchCategory,TResult Function( SearchBrand value)?  searchBrand,TResult Function( UpdateRecently value)?  updateRecently,required TResult orElse(),}){
final _that = this;
switch (_that) {
case SetQuery() when setQuery != null:
return setQuery(_that);case SearchShops() when searchShops != null:
return searchShops(_that);case SearchProduct() when searchProduct != null:
return searchProduct(_that);case SearchService() when searchService != null:
return searchService(_that);case SearchCategory() when searchCategory != null:
return searchCategory(_that);case SearchBrand() when searchBrand != null:
return searchBrand(_that);case UpdateRecently() when updateRecently != null:
return updateRecently(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( SetQuery value)  setQuery,required TResult Function( SearchShops value)  searchShops,required TResult Function( SearchProduct value)  searchProduct,required TResult Function( SearchService value)  searchService,required TResult Function( SearchCategory value)  searchCategory,required TResult Function( SearchBrand value)  searchBrand,required TResult Function( UpdateRecently value)  updateRecently,}){
final _that = this;
switch (_that) {
case SetQuery():
return setQuery(_that);case SearchShops():
return searchShops(_that);case SearchProduct():
return searchProduct(_that);case SearchService():
return searchService(_that);case SearchCategory():
return searchCategory(_that);case SearchBrand():
return searchBrand(_that);case UpdateRecently():
return updateRecently(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( SetQuery value)?  setQuery,TResult? Function( SearchShops value)?  searchShops,TResult? Function( SearchProduct value)?  searchProduct,TResult? Function( SearchService value)?  searchService,TResult? Function( SearchCategory value)?  searchCategory,TResult? Function( SearchBrand value)?  searchBrand,TResult? Function( UpdateRecently value)?  updateRecently,}){
final _that = this;
switch (_that) {
case SetQuery() when setQuery != null:
return setQuery(_that);case SearchShops() when searchShops != null:
return searchShops(_that);case SearchProduct() when searchProduct != null:
return searchProduct(_that);case SearchService() when searchService != null:
return searchService(_that);case SearchCategory() when searchCategory != null:
return searchCategory(_that);case SearchBrand() when searchBrand != null:
return searchBrand(_that);case UpdateRecently() when updateRecently != null:
return updateRecently(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( String query,  int? shopId)?  setQuery,TResult Function( BuildContext context)?  searchShops,TResult Function( BuildContext context)?  searchProduct,TResult Function( BuildContext context)?  searchService,TResult Function( BuildContext context)?  searchCategory,TResult Function( BuildContext context)?  searchBrand,TResult Function()?  updateRecently,required TResult orElse(),}) {final _that = this;
switch (_that) {
case SetQuery() when setQuery != null:
return setQuery(_that.query,_that.shopId);case SearchShops() when searchShops != null:
return searchShops(_that.context);case SearchProduct() when searchProduct != null:
return searchProduct(_that.context);case SearchService() when searchService != null:
return searchService(_that.context);case SearchCategory() when searchCategory != null:
return searchCategory(_that.context);case SearchBrand() when searchBrand != null:
return searchBrand(_that.context);case UpdateRecently() when updateRecently != null:
return updateRecently();case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( String query,  int? shopId)  setQuery,required TResult Function( BuildContext context)  searchShops,required TResult Function( BuildContext context)  searchProduct,required TResult Function( BuildContext context)  searchService,required TResult Function( BuildContext context)  searchCategory,required TResult Function( BuildContext context)  searchBrand,required TResult Function()  updateRecently,}) {final _that = this;
switch (_that) {
case SetQuery():
return setQuery(_that.query,_that.shopId);case SearchShops():
return searchShops(_that.context);case SearchProduct():
return searchProduct(_that.context);case SearchService():
return searchService(_that.context);case SearchCategory():
return searchCategory(_that.context);case SearchBrand():
return searchBrand(_that.context);case UpdateRecently():
return updateRecently();case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( String query,  int? shopId)?  setQuery,TResult? Function( BuildContext context)?  searchShops,TResult? Function( BuildContext context)?  searchProduct,TResult? Function( BuildContext context)?  searchService,TResult? Function( BuildContext context)?  searchCategory,TResult? Function( BuildContext context)?  searchBrand,TResult? Function()?  updateRecently,}) {final _that = this;
switch (_that) {
case SetQuery() when setQuery != null:
return setQuery(_that.query,_that.shopId);case SearchShops() when searchShops != null:
return searchShops(_that.context);case SearchProduct() when searchProduct != null:
return searchProduct(_that.context);case SearchService() when searchService != null:
return searchService(_that.context);case SearchCategory() when searchCategory != null:
return searchCategory(_that.context);case SearchBrand() when searchBrand != null:
return searchBrand(_that.context);case UpdateRecently() when updateRecently != null:
return updateRecently();case _:
  return null;

}
}

}

/// @nodoc


class SetQuery implements SearchEvent {
  const SetQuery({required this.query, this.shopId});
  

 final  String query;
 final  int? shopId;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetQueryCopyWith<SetQuery> get copyWith => _$SetQueryCopyWithImpl<SetQuery>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetQuery&&(identical(other.query, query) || other.query == query)&&(identical(other.shopId, shopId) || other.shopId == shopId));
}


@override
int get hashCode => Object.hash(runtimeType,query,shopId);

@override
String toString() {
  return 'SearchEvent.setQuery(query: $query, shopId: $shopId)';
}


}

/// @nodoc
abstract mixin class $SetQueryCopyWith<$Res> implements $SearchEventCopyWith<$Res> {
  factory $SetQueryCopyWith(SetQuery value, $Res Function(SetQuery) _then) = _$SetQueryCopyWithImpl;
@useResult
$Res call({
 String query, int? shopId
});




}
/// @nodoc
class _$SetQueryCopyWithImpl<$Res>
    implements $SetQueryCopyWith<$Res> {
  _$SetQueryCopyWithImpl(this._self, this._then);

  final SetQuery _self;
  final $Res Function(SetQuery) _then;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? query = null,Object? shopId = freezed,}) {
  return _then(SetQuery(
query: null == query ? _self.query : query // ignore: cast_nullable_to_non_nullable
as String,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class SearchShops implements SearchEvent {
  const SearchShops(this.context);
  

 final  BuildContext context;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchShopsCopyWith<SearchShops> get copyWith => _$SearchShopsCopyWithImpl<SearchShops>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchShops&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'SearchEvent.searchShops(context: $context)';
}


}

/// @nodoc
abstract mixin class $SearchShopsCopyWith<$Res> implements $SearchEventCopyWith<$Res> {
  factory $SearchShopsCopyWith(SearchShops value, $Res Function(SearchShops) _then) = _$SearchShopsCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$SearchShopsCopyWithImpl<$Res>
    implements $SearchShopsCopyWith<$Res> {
  _$SearchShopsCopyWithImpl(this._self, this._then);

  final SearchShops _self;
  final $Res Function(SearchShops) _then;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(SearchShops(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class SearchProduct implements SearchEvent {
  const SearchProduct(this.context);
  

 final  BuildContext context;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchProductCopyWith<SearchProduct> get copyWith => _$SearchProductCopyWithImpl<SearchProduct>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchProduct&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'SearchEvent.searchProduct(context: $context)';
}


}

/// @nodoc
abstract mixin class $SearchProductCopyWith<$Res> implements $SearchEventCopyWith<$Res> {
  factory $SearchProductCopyWith(SearchProduct value, $Res Function(SearchProduct) _then) = _$SearchProductCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$SearchProductCopyWithImpl<$Res>
    implements $SearchProductCopyWith<$Res> {
  _$SearchProductCopyWithImpl(this._self, this._then);

  final SearchProduct _self;
  final $Res Function(SearchProduct) _then;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(SearchProduct(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class SearchService implements SearchEvent {
  const SearchService(this.context);
  

 final  BuildContext context;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchServiceCopyWith<SearchService> get copyWith => _$SearchServiceCopyWithImpl<SearchService>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchService&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'SearchEvent.searchService(context: $context)';
}


}

/// @nodoc
abstract mixin class $SearchServiceCopyWith<$Res> implements $SearchEventCopyWith<$Res> {
  factory $SearchServiceCopyWith(SearchService value, $Res Function(SearchService) _then) = _$SearchServiceCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$SearchServiceCopyWithImpl<$Res>
    implements $SearchServiceCopyWith<$Res> {
  _$SearchServiceCopyWithImpl(this._self, this._then);

  final SearchService _self;
  final $Res Function(SearchService) _then;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(SearchService(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class SearchCategory implements SearchEvent {
  const SearchCategory(this.context);
  

 final  BuildContext context;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchCategoryCopyWith<SearchCategory> get copyWith => _$SearchCategoryCopyWithImpl<SearchCategory>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchCategory&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'SearchEvent.searchCategory(context: $context)';
}


}

/// @nodoc
abstract mixin class $SearchCategoryCopyWith<$Res> implements $SearchEventCopyWith<$Res> {
  factory $SearchCategoryCopyWith(SearchCategory value, $Res Function(SearchCategory) _then) = _$SearchCategoryCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$SearchCategoryCopyWithImpl<$Res>
    implements $SearchCategoryCopyWith<$Res> {
  _$SearchCategoryCopyWithImpl(this._self, this._then);

  final SearchCategory _self;
  final $Res Function(SearchCategory) _then;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(SearchCategory(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class SearchBrand implements SearchEvent {
  const SearchBrand(this.context);
  

 final  BuildContext context;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchBrandCopyWith<SearchBrand> get copyWith => _$SearchBrandCopyWithImpl<SearchBrand>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchBrand&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'SearchEvent.searchBrand(context: $context)';
}


}

/// @nodoc
abstract mixin class $SearchBrandCopyWith<$Res> implements $SearchEventCopyWith<$Res> {
  factory $SearchBrandCopyWith(SearchBrand value, $Res Function(SearchBrand) _then) = _$SearchBrandCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$SearchBrandCopyWithImpl<$Res>
    implements $SearchBrandCopyWith<$Res> {
  _$SearchBrandCopyWithImpl(this._self, this._then);

  final SearchBrand _self;
  final $Res Function(SearchBrand) _then;

/// Create a copy of SearchEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(SearchBrand(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class UpdateRecently implements SearchEvent {
  const UpdateRecently();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateRecently);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'SearchEvent.updateRecently()';
}


}




/// @nodoc
mixin _$SearchState {

 List<ShopData> get shops; List<ProductData> get products; List<CategoryData> get categories; List<BrandData> get brands; List<ServiceModel> get services; bool get isShopLoading; bool get isProductLoading; bool get isCategoryLoading; bool get isBrandLoading; bool get isServiceLoading; String get query;
/// Create a copy of SearchState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchStateCopyWith<SearchState> get copyWith => _$SearchStateCopyWithImpl<SearchState>(this as SearchState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchState&&const DeepCollectionEquality().equals(other.shops, shops)&&const DeepCollectionEquality().equals(other.products, products)&&const DeepCollectionEquality().equals(other.categories, categories)&&const DeepCollectionEquality().equals(other.brands, brands)&&const DeepCollectionEquality().equals(other.services, services)&&(identical(other.isShopLoading, isShopLoading) || other.isShopLoading == isShopLoading)&&(identical(other.isProductLoading, isProductLoading) || other.isProductLoading == isProductLoading)&&(identical(other.isCategoryLoading, isCategoryLoading) || other.isCategoryLoading == isCategoryLoading)&&(identical(other.isBrandLoading, isBrandLoading) || other.isBrandLoading == isBrandLoading)&&(identical(other.isServiceLoading, isServiceLoading) || other.isServiceLoading == isServiceLoading)&&(identical(other.query, query) || other.query == query));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(shops),const DeepCollectionEquality().hash(products),const DeepCollectionEquality().hash(categories),const DeepCollectionEquality().hash(brands),const DeepCollectionEquality().hash(services),isShopLoading,isProductLoading,isCategoryLoading,isBrandLoading,isServiceLoading,query);

@override
String toString() {
  return 'SearchState(shops: $shops, products: $products, categories: $categories, brands: $brands, services: $services, isShopLoading: $isShopLoading, isProductLoading: $isProductLoading, isCategoryLoading: $isCategoryLoading, isBrandLoading: $isBrandLoading, isServiceLoading: $isServiceLoading, query: $query)';
}


}

/// @nodoc
abstract mixin class $SearchStateCopyWith<$Res>  {
  factory $SearchStateCopyWith(SearchState value, $Res Function(SearchState) _then) = _$SearchStateCopyWithImpl;
@useResult
$Res call({
 List<ShopData> shops, List<ProductData> products, List<CategoryData> categories, List<BrandData> brands, List<ServiceModel> services, bool isShopLoading, bool isProductLoading, bool isCategoryLoading, bool isBrandLoading, bool isServiceLoading, String query
});




}
/// @nodoc
class _$SearchStateCopyWithImpl<$Res>
    implements $SearchStateCopyWith<$Res> {
  _$SearchStateCopyWithImpl(this._self, this._then);

  final SearchState _self;
  final $Res Function(SearchState) _then;

/// Create a copy of SearchState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? shops = null,Object? products = null,Object? categories = null,Object? brands = null,Object? services = null,Object? isShopLoading = null,Object? isProductLoading = null,Object? isCategoryLoading = null,Object? isBrandLoading = null,Object? isServiceLoading = null,Object? query = null,}) {
  return _then(_self.copyWith(
shops: null == shops ? _self.shops : shops // ignore: cast_nullable_to_non_nullable
as List<ShopData>,products: null == products ? _self.products : products // ignore: cast_nullable_to_non_nullable
as List<ProductData>,categories: null == categories ? _self.categories : categories // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,brands: null == brands ? _self.brands : brands // ignore: cast_nullable_to_non_nullable
as List<BrandData>,services: null == services ? _self.services : services // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,isShopLoading: null == isShopLoading ? _self.isShopLoading : isShopLoading // ignore: cast_nullable_to_non_nullable
as bool,isProductLoading: null == isProductLoading ? _self.isProductLoading : isProductLoading // ignore: cast_nullable_to_non_nullable
as bool,isCategoryLoading: null == isCategoryLoading ? _self.isCategoryLoading : isCategoryLoading // ignore: cast_nullable_to_non_nullable
as bool,isBrandLoading: null == isBrandLoading ? _self.isBrandLoading : isBrandLoading // ignore: cast_nullable_to_non_nullable
as bool,isServiceLoading: null == isServiceLoading ? _self.isServiceLoading : isServiceLoading // ignore: cast_nullable_to_non_nullable
as bool,query: null == query ? _self.query : query // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [SearchState].
extension SearchStatePatterns on SearchState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SearchState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SearchState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SearchState value)  $default,){
final _that = this;
switch (_that) {
case _SearchState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SearchState value)?  $default,){
final _that = this;
switch (_that) {
case _SearchState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<ShopData> shops,  List<ProductData> products,  List<CategoryData> categories,  List<BrandData> brands,  List<ServiceModel> services,  bool isShopLoading,  bool isProductLoading,  bool isCategoryLoading,  bool isBrandLoading,  bool isServiceLoading,  String query)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SearchState() when $default != null:
return $default(_that.shops,_that.products,_that.categories,_that.brands,_that.services,_that.isShopLoading,_that.isProductLoading,_that.isCategoryLoading,_that.isBrandLoading,_that.isServiceLoading,_that.query);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<ShopData> shops,  List<ProductData> products,  List<CategoryData> categories,  List<BrandData> brands,  List<ServiceModel> services,  bool isShopLoading,  bool isProductLoading,  bool isCategoryLoading,  bool isBrandLoading,  bool isServiceLoading,  String query)  $default,) {final _that = this;
switch (_that) {
case _SearchState():
return $default(_that.shops,_that.products,_that.categories,_that.brands,_that.services,_that.isShopLoading,_that.isProductLoading,_that.isCategoryLoading,_that.isBrandLoading,_that.isServiceLoading,_that.query);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<ShopData> shops,  List<ProductData> products,  List<CategoryData> categories,  List<BrandData> brands,  List<ServiceModel> services,  bool isShopLoading,  bool isProductLoading,  bool isCategoryLoading,  bool isBrandLoading,  bool isServiceLoading,  String query)?  $default,) {final _that = this;
switch (_that) {
case _SearchState() when $default != null:
return $default(_that.shops,_that.products,_that.categories,_that.brands,_that.services,_that.isShopLoading,_that.isProductLoading,_that.isCategoryLoading,_that.isBrandLoading,_that.isServiceLoading,_that.query);case _:
  return null;

}
}

}

/// @nodoc


class _SearchState implements SearchState {
  const _SearchState({final  List<ShopData> shops = const [], final  List<ProductData> products = const [], final  List<CategoryData> categories = const [], final  List<BrandData> brands = const [], final  List<ServiceModel> services = const [], this.isShopLoading = false, this.isProductLoading = false, this.isCategoryLoading = false, this.isBrandLoading = false, this.isServiceLoading = false, this.query = ""}): _shops = shops,_products = products,_categories = categories,_brands = brands,_services = services;
  

 final  List<ShopData> _shops;
@override@JsonKey() List<ShopData> get shops {
  if (_shops is EqualUnmodifiableListView) return _shops;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_shops);
}

 final  List<ProductData> _products;
@override@JsonKey() List<ProductData> get products {
  if (_products is EqualUnmodifiableListView) return _products;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_products);
}

 final  List<CategoryData> _categories;
@override@JsonKey() List<CategoryData> get categories {
  if (_categories is EqualUnmodifiableListView) return _categories;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_categories);
}

 final  List<BrandData> _brands;
@override@JsonKey() List<BrandData> get brands {
  if (_brands is EqualUnmodifiableListView) return _brands;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_brands);
}

 final  List<ServiceModel> _services;
@override@JsonKey() List<ServiceModel> get services {
  if (_services is EqualUnmodifiableListView) return _services;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_services);
}

@override@JsonKey() final  bool isShopLoading;
@override@JsonKey() final  bool isProductLoading;
@override@JsonKey() final  bool isCategoryLoading;
@override@JsonKey() final  bool isBrandLoading;
@override@JsonKey() final  bool isServiceLoading;
@override@JsonKey() final  String query;

/// Create a copy of SearchState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SearchStateCopyWith<_SearchState> get copyWith => __$SearchStateCopyWithImpl<_SearchState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _SearchState&&const DeepCollectionEquality().equals(other._shops, _shops)&&const DeepCollectionEquality().equals(other._products, _products)&&const DeepCollectionEquality().equals(other._categories, _categories)&&const DeepCollectionEquality().equals(other._brands, _brands)&&const DeepCollectionEquality().equals(other._services, _services)&&(identical(other.isShopLoading, isShopLoading) || other.isShopLoading == isShopLoading)&&(identical(other.isProductLoading, isProductLoading) || other.isProductLoading == isProductLoading)&&(identical(other.isCategoryLoading, isCategoryLoading) || other.isCategoryLoading == isCategoryLoading)&&(identical(other.isBrandLoading, isBrandLoading) || other.isBrandLoading == isBrandLoading)&&(identical(other.isServiceLoading, isServiceLoading) || other.isServiceLoading == isServiceLoading)&&(identical(other.query, query) || other.query == query));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_shops),const DeepCollectionEquality().hash(_products),const DeepCollectionEquality().hash(_categories),const DeepCollectionEquality().hash(_brands),const DeepCollectionEquality().hash(_services),isShopLoading,isProductLoading,isCategoryLoading,isBrandLoading,isServiceLoading,query);

@override
String toString() {
  return 'SearchState(shops: $shops, products: $products, categories: $categories, brands: $brands, services: $services, isShopLoading: $isShopLoading, isProductLoading: $isProductLoading, isCategoryLoading: $isCategoryLoading, isBrandLoading: $isBrandLoading, isServiceLoading: $isServiceLoading, query: $query)';
}


}

/// @nodoc
abstract mixin class _$SearchStateCopyWith<$Res> implements $SearchStateCopyWith<$Res> {
  factory _$SearchStateCopyWith(_SearchState value, $Res Function(_SearchState) _then) = __$SearchStateCopyWithImpl;
@override @useResult
$Res call({
 List<ShopData> shops, List<ProductData> products, List<CategoryData> categories, List<BrandData> brands, List<ServiceModel> services, bool isShopLoading, bool isProductLoading, bool isCategoryLoading, bool isBrandLoading, bool isServiceLoading, String query
});




}
/// @nodoc
class __$SearchStateCopyWithImpl<$Res>
    implements _$SearchStateCopyWith<$Res> {
  __$SearchStateCopyWithImpl(this._self, this._then);

  final _SearchState _self;
  final $Res Function(_SearchState) _then;

/// Create a copy of SearchState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? shops = null,Object? products = null,Object? categories = null,Object? brands = null,Object? services = null,Object? isShopLoading = null,Object? isProductLoading = null,Object? isCategoryLoading = null,Object? isBrandLoading = null,Object? isServiceLoading = null,Object? query = null,}) {
  return _then(_SearchState(
shops: null == shops ? _self._shops : shops // ignore: cast_nullable_to_non_nullable
as List<ShopData>,products: null == products ? _self._products : products // ignore: cast_nullable_to_non_nullable
as List<ProductData>,categories: null == categories ? _self._categories : categories // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,brands: null == brands ? _self._brands : brands // ignore: cast_nullable_to_non_nullable
as List<BrandData>,services: null == services ? _self._services : services // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,isShopLoading: null == isShopLoading ? _self.isShopLoading : isShopLoading // ignore: cast_nullable_to_non_nullable
as bool,isProductLoading: null == isProductLoading ? _self.isProductLoading : isProductLoading // ignore: cast_nullable_to_non_nullable
as bool,isCategoryLoading: null == isCategoryLoading ? _self.isCategoryLoading : isCategoryLoading // ignore: cast_nullable_to_non_nullable
as bool,isBrandLoading: null == isBrandLoading ? _self.isBrandLoading : isBrandLoading // ignore: cast_nullable_to_non_nullable
as bool,isServiceLoading: null == isServiceLoading ? _self.isServiceLoading : isServiceLoading // ignore: cast_nullable_to_non_nullable
as bool,query: null == query ? _self.query : query // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

// dart format on
