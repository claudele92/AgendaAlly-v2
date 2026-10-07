// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'filter_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$FilterEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FilterEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'FilterEvent()';
}


}

/// @nodoc
class $FilterEventCopyWith<$Res>  {
$FilterEventCopyWith(FilterEvent _, $Res Function(FilterEvent) __);
}


/// Adds pattern-matching-related methods to [FilterEvent].
extension FilterEventPatterns on FilterEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( SelectType value)?  selectType,TResult Function( FetchExtras value)?  fetchExtras,TResult Function( SetExtras value)?  setExtras,TResult Function( SetRangePrice value)?  setRangePrice,TResult Function( SetCategory value)?  setCategory,TResult Function( SetBrands value)?  setBrands,TResult Function( ClearFilter value)?  clearFilter,TResult Function( SetSort value)?  setSort,TResult Function( SetServiceType value)?  setServiceType,TResult Function( SetGender value)?  setGender,TResult Function( SetAmenities value)?  setAmenities,TResult Function( FetchTags value)?  fetchTags,TResult Function( SetFilter value)?  setFilter,required TResult orElse(),}){
final _that = this;
switch (_that) {
case SelectType() when selectType != null:
return selectType(_that);case FetchExtras() when fetchExtras != null:
return fetchExtras(_that);case SetExtras() when setExtras != null:
return setExtras(_that);case SetRangePrice() when setRangePrice != null:
return setRangePrice(_that);case SetCategory() when setCategory != null:
return setCategory(_that);case SetBrands() when setBrands != null:
return setBrands(_that);case ClearFilter() when clearFilter != null:
return clearFilter(_that);case SetSort() when setSort != null:
return setSort(_that);case SetServiceType() when setServiceType != null:
return setServiceType(_that);case SetGender() when setGender != null:
return setGender(_that);case SetAmenities() when setAmenities != null:
return setAmenities(_that);case FetchTags() when fetchTags != null:
return fetchTags(_that);case SetFilter() when setFilter != null:
return setFilter(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( SelectType value)  selectType,required TResult Function( FetchExtras value)  fetchExtras,required TResult Function( SetExtras value)  setExtras,required TResult Function( SetRangePrice value)  setRangePrice,required TResult Function( SetCategory value)  setCategory,required TResult Function( SetBrands value)  setBrands,required TResult Function( ClearFilter value)  clearFilter,required TResult Function( SetSort value)  setSort,required TResult Function( SetServiceType value)  setServiceType,required TResult Function( SetGender value)  setGender,required TResult Function( SetAmenities value)  setAmenities,required TResult Function( FetchTags value)  fetchTags,required TResult Function( SetFilter value)  setFilter,}){
final _that = this;
switch (_that) {
case SelectType():
return selectType(_that);case FetchExtras():
return fetchExtras(_that);case SetExtras():
return setExtras(_that);case SetRangePrice():
return setRangePrice(_that);case SetCategory():
return setCategory(_that);case SetBrands():
return setBrands(_that);case ClearFilter():
return clearFilter(_that);case SetSort():
return setSort(_that);case SetServiceType():
return setServiceType(_that);case SetGender():
return setGender(_that);case SetAmenities():
return setAmenities(_that);case FetchTags():
return fetchTags(_that);case SetFilter():
return setFilter(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( SelectType value)?  selectType,TResult? Function( FetchExtras value)?  fetchExtras,TResult? Function( SetExtras value)?  setExtras,TResult? Function( SetRangePrice value)?  setRangePrice,TResult? Function( SetCategory value)?  setCategory,TResult? Function( SetBrands value)?  setBrands,TResult? Function( ClearFilter value)?  clearFilter,TResult? Function( SetSort value)?  setSort,TResult? Function( SetServiceType value)?  setServiceType,TResult? Function( SetGender value)?  setGender,TResult? Function( SetAmenities value)?  setAmenities,TResult? Function( FetchTags value)?  fetchTags,TResult? Function( SetFilter value)?  setFilter,}){
final _that = this;
switch (_that) {
case SelectType() when selectType != null:
return selectType(_that);case FetchExtras() when fetchExtras != null:
return fetchExtras(_that);case SetExtras() when setExtras != null:
return setExtras(_that);case SetRangePrice() when setRangePrice != null:
return setRangePrice(_that);case SetCategory() when setCategory != null:
return setCategory(_that);case SetBrands() when setBrands != null:
return setBrands(_that);case ClearFilter() when clearFilter != null:
return clearFilter(_that);case SetSort() when setSort != null:
return setSort(_that);case SetServiceType() when setServiceType != null:
return setServiceType(_that);case SetGender() when setGender != null:
return setGender(_that);case SetAmenities() when setAmenities != null:
return setAmenities(_that);case FetchTags() when fetchTags != null:
return fetchTags(_that);case SetFilter() when setFilter != null:
return setFilter(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( LayoutType selectType)?  selectType,TResult Function( BuildContext context,  String? type,  bool isPrice,  int? categoryId,  int? shopId)?  fetchExtras,TResult Function( int id)?  setExtras,TResult Function( RangeValues price)?  setRangePrice,TResult Function( int id)?  setCategory,TResult Function( int id)?  setBrands,TResult Function()?  clearFilter,TResult Function( String? value)?  setSort,TResult Function( String? value)?  setServiceType,TResult Function( int index)?  setGender,TResult Function( int? value)?  setAmenities,TResult Function( BuildContext context)?  fetchTags,TResult Function( FilterShopModel? filter)?  setFilter,required TResult orElse(),}) {final _that = this;
switch (_that) {
case SelectType() when selectType != null:
return selectType(_that.selectType);case FetchExtras() when fetchExtras != null:
return fetchExtras(_that.context,_that.type,_that.isPrice,_that.categoryId,_that.shopId);case SetExtras() when setExtras != null:
return setExtras(_that.id);case SetRangePrice() when setRangePrice != null:
return setRangePrice(_that.price);case SetCategory() when setCategory != null:
return setCategory(_that.id);case SetBrands() when setBrands != null:
return setBrands(_that.id);case ClearFilter() when clearFilter != null:
return clearFilter();case SetSort() when setSort != null:
return setSort(_that.value);case SetServiceType() when setServiceType != null:
return setServiceType(_that.value);case SetGender() when setGender != null:
return setGender(_that.index);case SetAmenities() when setAmenities != null:
return setAmenities(_that.value);case FetchTags() when fetchTags != null:
return fetchTags(_that.context);case SetFilter() when setFilter != null:
return setFilter(_that.filter);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( LayoutType selectType)  selectType,required TResult Function( BuildContext context,  String? type,  bool isPrice,  int? categoryId,  int? shopId)  fetchExtras,required TResult Function( int id)  setExtras,required TResult Function( RangeValues price)  setRangePrice,required TResult Function( int id)  setCategory,required TResult Function( int id)  setBrands,required TResult Function()  clearFilter,required TResult Function( String? value)  setSort,required TResult Function( String? value)  setServiceType,required TResult Function( int index)  setGender,required TResult Function( int? value)  setAmenities,required TResult Function( BuildContext context)  fetchTags,required TResult Function( FilterShopModel? filter)  setFilter,}) {final _that = this;
switch (_that) {
case SelectType():
return selectType(_that.selectType);case FetchExtras():
return fetchExtras(_that.context,_that.type,_that.isPrice,_that.categoryId,_that.shopId);case SetExtras():
return setExtras(_that.id);case SetRangePrice():
return setRangePrice(_that.price);case SetCategory():
return setCategory(_that.id);case SetBrands():
return setBrands(_that.id);case ClearFilter():
return clearFilter();case SetSort():
return setSort(_that.value);case SetServiceType():
return setServiceType(_that.value);case SetGender():
return setGender(_that.index);case SetAmenities():
return setAmenities(_that.value);case FetchTags():
return fetchTags(_that.context);case SetFilter():
return setFilter(_that.filter);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( LayoutType selectType)?  selectType,TResult? Function( BuildContext context,  String? type,  bool isPrice,  int? categoryId,  int? shopId)?  fetchExtras,TResult? Function( int id)?  setExtras,TResult? Function( RangeValues price)?  setRangePrice,TResult? Function( int id)?  setCategory,TResult? Function( int id)?  setBrands,TResult? Function()?  clearFilter,TResult? Function( String? value)?  setSort,TResult? Function( String? value)?  setServiceType,TResult? Function( int index)?  setGender,TResult? Function( int? value)?  setAmenities,TResult? Function( BuildContext context)?  fetchTags,TResult? Function( FilterShopModel? filter)?  setFilter,}) {final _that = this;
switch (_that) {
case SelectType() when selectType != null:
return selectType(_that.selectType);case FetchExtras() when fetchExtras != null:
return fetchExtras(_that.context,_that.type,_that.isPrice,_that.categoryId,_that.shopId);case SetExtras() when setExtras != null:
return setExtras(_that.id);case SetRangePrice() when setRangePrice != null:
return setRangePrice(_that.price);case SetCategory() when setCategory != null:
return setCategory(_that.id);case SetBrands() when setBrands != null:
return setBrands(_that.id);case ClearFilter() when clearFilter != null:
return clearFilter();case SetSort() when setSort != null:
return setSort(_that.value);case SetServiceType() when setServiceType != null:
return setServiceType(_that.value);case SetGender() when setGender != null:
return setGender(_that.index);case SetAmenities() when setAmenities != null:
return setAmenities(_that.value);case FetchTags() when fetchTags != null:
return fetchTags(_that.context);case SetFilter() when setFilter != null:
return setFilter(_that.filter);case _:
  return null;

}
}

}

/// @nodoc


class SelectType implements FilterEvent {
  const SelectType({required this.selectType});
  

 final  LayoutType selectType;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectTypeCopyWith<SelectType> get copyWith => _$SelectTypeCopyWithImpl<SelectType>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectType&&(identical(other.selectType, selectType) || other.selectType == selectType));
}


@override
int get hashCode => Object.hash(runtimeType,selectType);

@override
String toString() {
  return 'FilterEvent.selectType(selectType: $selectType)';
}


}

/// @nodoc
abstract mixin class $SelectTypeCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SelectTypeCopyWith(SelectType value, $Res Function(SelectType) _then) = _$SelectTypeCopyWithImpl;
@useResult
$Res call({
 LayoutType selectType
});




}
/// @nodoc
class _$SelectTypeCopyWithImpl<$Res>
    implements $SelectTypeCopyWith<$Res> {
  _$SelectTypeCopyWithImpl(this._self, this._then);

  final SelectType _self;
  final $Res Function(SelectType) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? selectType = null,}) {
  return _then(SelectType(
selectType: null == selectType ? _self.selectType : selectType // ignore: cast_nullable_to_non_nullable
as LayoutType,
  ));
}


}

/// @nodoc


class FetchExtras implements FilterEvent {
  const FetchExtras(this.context, {this.type, required this.isPrice, this.categoryId, this.shopId});
  

 final  BuildContext context;
 final  String? type;
 final  bool isPrice;
 final  int? categoryId;
 final  int? shopId;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchExtrasCopyWith<FetchExtras> get copyWith => _$FetchExtrasCopyWithImpl<FetchExtras>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchExtras&&(identical(other.context, context) || other.context == context)&&(identical(other.type, type) || other.type == type)&&(identical(other.isPrice, isPrice) || other.isPrice == isPrice)&&(identical(other.categoryId, categoryId) || other.categoryId == categoryId)&&(identical(other.shopId, shopId) || other.shopId == shopId));
}


@override
int get hashCode => Object.hash(runtimeType,context,type,isPrice,categoryId,shopId);

@override
String toString() {
  return 'FilterEvent.fetchExtras(context: $context, type: $type, isPrice: $isPrice, categoryId: $categoryId, shopId: $shopId)';
}


}

/// @nodoc
abstract mixin class $FetchExtrasCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $FetchExtrasCopyWith(FetchExtras value, $Res Function(FetchExtras) _then) = _$FetchExtrasCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String? type, bool isPrice, int? categoryId, int? shopId
});




}
/// @nodoc
class _$FetchExtrasCopyWithImpl<$Res>
    implements $FetchExtrasCopyWith<$Res> {
  _$FetchExtrasCopyWithImpl(this._self, this._then);

  final FetchExtras _self;
  final $Res Function(FetchExtras) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? type = freezed,Object? isPrice = null,Object? categoryId = freezed,Object? shopId = freezed,}) {
  return _then(FetchExtras(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,type: freezed == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String?,isPrice: null == isPrice ? _self.isPrice : isPrice // ignore: cast_nullable_to_non_nullable
as bool,categoryId: freezed == categoryId ? _self.categoryId : categoryId // ignore: cast_nullable_to_non_nullable
as int?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class SetExtras implements FilterEvent {
  const SetExtras({required this.id});
  

 final  int id;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetExtrasCopyWith<SetExtras> get copyWith => _$SetExtrasCopyWithImpl<SetExtras>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetExtras&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,id);

@override
String toString() {
  return 'FilterEvent.setExtras(id: $id)';
}


}

/// @nodoc
abstract mixin class $SetExtrasCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetExtrasCopyWith(SetExtras value, $Res Function(SetExtras) _then) = _$SetExtrasCopyWithImpl;
@useResult
$Res call({
 int id
});




}
/// @nodoc
class _$SetExtrasCopyWithImpl<$Res>
    implements $SetExtrasCopyWith<$Res> {
  _$SetExtrasCopyWithImpl(this._self, this._then);

  final SetExtras _self;
  final $Res Function(SetExtras) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? id = null,}) {
  return _then(SetExtras(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class SetRangePrice implements FilterEvent {
  const SetRangePrice({required this.price});
  

 final  RangeValues price;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetRangePriceCopyWith<SetRangePrice> get copyWith => _$SetRangePriceCopyWithImpl<SetRangePrice>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetRangePrice&&(identical(other.price, price) || other.price == price));
}


@override
int get hashCode => Object.hash(runtimeType,price);

@override
String toString() {
  return 'FilterEvent.setRangePrice(price: $price)';
}


}

/// @nodoc
abstract mixin class $SetRangePriceCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetRangePriceCopyWith(SetRangePrice value, $Res Function(SetRangePrice) _then) = _$SetRangePriceCopyWithImpl;
@useResult
$Res call({
 RangeValues price
});




}
/// @nodoc
class _$SetRangePriceCopyWithImpl<$Res>
    implements $SetRangePriceCopyWith<$Res> {
  _$SetRangePriceCopyWithImpl(this._self, this._then);

  final SetRangePrice _self;
  final $Res Function(SetRangePrice) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? price = null,}) {
  return _then(SetRangePrice(
price: null == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as RangeValues,
  ));
}


}

/// @nodoc


class SetCategory implements FilterEvent {
  const SetCategory({required this.id});
  

 final  int id;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetCategoryCopyWith<SetCategory> get copyWith => _$SetCategoryCopyWithImpl<SetCategory>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetCategory&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,id);

@override
String toString() {
  return 'FilterEvent.setCategory(id: $id)';
}


}

/// @nodoc
abstract mixin class $SetCategoryCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetCategoryCopyWith(SetCategory value, $Res Function(SetCategory) _then) = _$SetCategoryCopyWithImpl;
@useResult
$Res call({
 int id
});




}
/// @nodoc
class _$SetCategoryCopyWithImpl<$Res>
    implements $SetCategoryCopyWith<$Res> {
  _$SetCategoryCopyWithImpl(this._self, this._then);

  final SetCategory _self;
  final $Res Function(SetCategory) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? id = null,}) {
  return _then(SetCategory(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class SetBrands implements FilterEvent {
  const SetBrands({required this.id});
  

 final  int id;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetBrandsCopyWith<SetBrands> get copyWith => _$SetBrandsCopyWithImpl<SetBrands>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetBrands&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,id);

@override
String toString() {
  return 'FilterEvent.setBrands(id: $id)';
}


}

/// @nodoc
abstract mixin class $SetBrandsCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetBrandsCopyWith(SetBrands value, $Res Function(SetBrands) _then) = _$SetBrandsCopyWithImpl;
@useResult
$Res call({
 int id
});




}
/// @nodoc
class _$SetBrandsCopyWithImpl<$Res>
    implements $SetBrandsCopyWith<$Res> {
  _$SetBrandsCopyWithImpl(this._self, this._then);

  final SetBrands _self;
  final $Res Function(SetBrands) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? id = null,}) {
  return _then(SetBrands(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class ClearFilter implements FilterEvent {
  const ClearFilter();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ClearFilter);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'FilterEvent.clearFilter()';
}


}




/// @nodoc


class SetSort implements FilterEvent {
  const SetSort({required this.value});
  

 final  String? value;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetSortCopyWith<SetSort> get copyWith => _$SetSortCopyWithImpl<SetSort>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetSort&&(identical(other.value, value) || other.value == value));
}


@override
int get hashCode => Object.hash(runtimeType,value);

@override
String toString() {
  return 'FilterEvent.setSort(value: $value)';
}


}

/// @nodoc
abstract mixin class $SetSortCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetSortCopyWith(SetSort value, $Res Function(SetSort) _then) = _$SetSortCopyWithImpl;
@useResult
$Res call({
 String? value
});




}
/// @nodoc
class _$SetSortCopyWithImpl<$Res>
    implements $SetSortCopyWith<$Res> {
  _$SetSortCopyWithImpl(this._self, this._then);

  final SetSort _self;
  final $Res Function(SetSort) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? value = freezed,}) {
  return _then(SetSort(
value: freezed == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class SetServiceType implements FilterEvent {
  const SetServiceType({required this.value});
  

 final  String? value;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetServiceTypeCopyWith<SetServiceType> get copyWith => _$SetServiceTypeCopyWithImpl<SetServiceType>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetServiceType&&(identical(other.value, value) || other.value == value));
}


@override
int get hashCode => Object.hash(runtimeType,value);

@override
String toString() {
  return 'FilterEvent.setServiceType(value: $value)';
}


}

/// @nodoc
abstract mixin class $SetServiceTypeCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetServiceTypeCopyWith(SetServiceType value, $Res Function(SetServiceType) _then) = _$SetServiceTypeCopyWithImpl;
@useResult
$Res call({
 String? value
});




}
/// @nodoc
class _$SetServiceTypeCopyWithImpl<$Res>
    implements $SetServiceTypeCopyWith<$Res> {
  _$SetServiceTypeCopyWithImpl(this._self, this._then);

  final SetServiceType _self;
  final $Res Function(SetServiceType) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? value = freezed,}) {
  return _then(SetServiceType(
value: freezed == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class SetGender implements FilterEvent {
  const SetGender({required this.index});
  

 final  int index;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetGenderCopyWith<SetGender> get copyWith => _$SetGenderCopyWithImpl<SetGender>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetGender&&(identical(other.index, index) || other.index == index));
}


@override
int get hashCode => Object.hash(runtimeType,index);

@override
String toString() {
  return 'FilterEvent.setGender(index: $index)';
}


}

/// @nodoc
abstract mixin class $SetGenderCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetGenderCopyWith(SetGender value, $Res Function(SetGender) _then) = _$SetGenderCopyWithImpl;
@useResult
$Res call({
 int index
});




}
/// @nodoc
class _$SetGenderCopyWithImpl<$Res>
    implements $SetGenderCopyWith<$Res> {
  _$SetGenderCopyWithImpl(this._self, this._then);

  final SetGender _self;
  final $Res Function(SetGender) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? index = null,}) {
  return _then(SetGender(
index: null == index ? _self.index : index // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class SetAmenities implements FilterEvent {
  const SetAmenities({required this.value});
  

 final  int? value;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetAmenitiesCopyWith<SetAmenities> get copyWith => _$SetAmenitiesCopyWithImpl<SetAmenities>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetAmenities&&(identical(other.value, value) || other.value == value));
}


@override
int get hashCode => Object.hash(runtimeType,value);

@override
String toString() {
  return 'FilterEvent.setAmenities(value: $value)';
}


}

/// @nodoc
abstract mixin class $SetAmenitiesCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetAmenitiesCopyWith(SetAmenities value, $Res Function(SetAmenities) _then) = _$SetAmenitiesCopyWithImpl;
@useResult
$Res call({
 int? value
});




}
/// @nodoc
class _$SetAmenitiesCopyWithImpl<$Res>
    implements $SetAmenitiesCopyWith<$Res> {
  _$SetAmenitiesCopyWithImpl(this._self, this._then);

  final SetAmenities _self;
  final $Res Function(SetAmenities) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? value = freezed,}) {
  return _then(SetAmenities(
value: freezed == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class FetchTags implements FilterEvent {
  const FetchTags(this.context);
  

 final  BuildContext context;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchTagsCopyWith<FetchTags> get copyWith => _$FetchTagsCopyWithImpl<FetchTags>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchTags&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'FilterEvent.fetchTags(context: $context)';
}


}

/// @nodoc
abstract mixin class $FetchTagsCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $FetchTagsCopyWith(FetchTags value, $Res Function(FetchTags) _then) = _$FetchTagsCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$FetchTagsCopyWithImpl<$Res>
    implements $FetchTagsCopyWith<$Res> {
  _$FetchTagsCopyWithImpl(this._self, this._then);

  final FetchTags _self;
  final $Res Function(FetchTags) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(FetchTags(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class SetFilter implements FilterEvent {
  const SetFilter({required this.filter});
  

 final  FilterShopModel? filter;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetFilterCopyWith<SetFilter> get copyWith => _$SetFilterCopyWithImpl<SetFilter>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetFilter&&(identical(other.filter, filter) || other.filter == filter));
}


@override
int get hashCode => Object.hash(runtimeType,filter);

@override
String toString() {
  return 'FilterEvent.setFilter(filter: $filter)';
}


}

/// @nodoc
abstract mixin class $SetFilterCopyWith<$Res> implements $FilterEventCopyWith<$Res> {
  factory $SetFilterCopyWith(SetFilter value, $Res Function(SetFilter) _then) = _$SetFilterCopyWithImpl;
@useResult
$Res call({
 FilterShopModel? filter
});




}
/// @nodoc
class _$SetFilterCopyWithImpl<$Res>
    implements $SetFilterCopyWith<$Res> {
  _$SetFilterCopyWithImpl(this._self, this._then);

  final SetFilter _self;
  final $Res Function(SetFilter) _then;

/// Create a copy of FilterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? filter = freezed,}) {
  return _then(SetFilter(
filter: freezed == filter ? _self.filter : filter // ignore: cast_nullable_to_non_nullable
as FilterShopModel?,
  ));
}


}

/// @nodoc
mixin _$FilterState {

 LayoutType get selectType; bool get isLoading; String get type; FilterResponse? get filter; Price? get filterPrices; List<int>? get extras; List<int>? get categories; List<int>? get brands; List<int> get prices; ShopFilterModel? get shopFilter; String? get serviceType; String? get sort; int? get tagId; int get genderIndex; RangeValues? get rangeValues;
/// Create a copy of FilterState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FilterStateCopyWith<FilterState> get copyWith => _$FilterStateCopyWithImpl<FilterState>(this as FilterState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FilterState&&(identical(other.selectType, selectType) || other.selectType == selectType)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.type, type) || other.type == type)&&(identical(other.filter, filter) || other.filter == filter)&&(identical(other.filterPrices, filterPrices) || other.filterPrices == filterPrices)&&const DeepCollectionEquality().equals(other.extras, extras)&&const DeepCollectionEquality().equals(other.categories, categories)&&const DeepCollectionEquality().equals(other.brands, brands)&&const DeepCollectionEquality().equals(other.prices, prices)&&(identical(other.shopFilter, shopFilter) || other.shopFilter == shopFilter)&&(identical(other.serviceType, serviceType) || other.serviceType == serviceType)&&(identical(other.sort, sort) || other.sort == sort)&&(identical(other.tagId, tagId) || other.tagId == tagId)&&(identical(other.genderIndex, genderIndex) || other.genderIndex == genderIndex)&&(identical(other.rangeValues, rangeValues) || other.rangeValues == rangeValues));
}


@override
int get hashCode => Object.hash(runtimeType,selectType,isLoading,type,filter,filterPrices,const DeepCollectionEquality().hash(extras),const DeepCollectionEquality().hash(categories),const DeepCollectionEquality().hash(brands),const DeepCollectionEquality().hash(prices),shopFilter,serviceType,sort,tagId,genderIndex,rangeValues);

@override
String toString() {
  return 'FilterState(selectType: $selectType, isLoading: $isLoading, type: $type, filter: $filter, filterPrices: $filterPrices, extras: $extras, categories: $categories, brands: $brands, prices: $prices, shopFilter: $shopFilter, serviceType: $serviceType, sort: $sort, tagId: $tagId, genderIndex: $genderIndex, rangeValues: $rangeValues)';
}


}

/// @nodoc
abstract mixin class $FilterStateCopyWith<$Res>  {
  factory $FilterStateCopyWith(FilterState value, $Res Function(FilterState) _then) = _$FilterStateCopyWithImpl;
@useResult
$Res call({
 LayoutType selectType, bool isLoading, String type, FilterResponse? filter, Price? filterPrices, List<int>? extras, List<int>? categories, List<int>? brands, List<int> prices, ShopFilterModel? shopFilter, String? serviceType, String? sort, int? tagId, int genderIndex, RangeValues? rangeValues
});




}
/// @nodoc
class _$FilterStateCopyWithImpl<$Res>
    implements $FilterStateCopyWith<$Res> {
  _$FilterStateCopyWithImpl(this._self, this._then);

  final FilterState _self;
  final $Res Function(FilterState) _then;

/// Create a copy of FilterState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? selectType = null,Object? isLoading = null,Object? type = null,Object? filter = freezed,Object? filterPrices = freezed,Object? extras = freezed,Object? categories = freezed,Object? brands = freezed,Object? prices = null,Object? shopFilter = freezed,Object? serviceType = freezed,Object? sort = freezed,Object? tagId = freezed,Object? genderIndex = null,Object? rangeValues = freezed,}) {
  return _then(_self.copyWith(
selectType: null == selectType ? _self.selectType : selectType // ignore: cast_nullable_to_non_nullable
as LayoutType,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,filter: freezed == filter ? _self.filter : filter // ignore: cast_nullable_to_non_nullable
as FilterResponse?,filterPrices: freezed == filterPrices ? _self.filterPrices : filterPrices // ignore: cast_nullable_to_non_nullable
as Price?,extras: freezed == extras ? _self.extras : extras // ignore: cast_nullable_to_non_nullable
as List<int>?,categories: freezed == categories ? _self.categories : categories // ignore: cast_nullable_to_non_nullable
as List<int>?,brands: freezed == brands ? _self.brands : brands // ignore: cast_nullable_to_non_nullable
as List<int>?,prices: null == prices ? _self.prices : prices // ignore: cast_nullable_to_non_nullable
as List<int>,shopFilter: freezed == shopFilter ? _self.shopFilter : shopFilter // ignore: cast_nullable_to_non_nullable
as ShopFilterModel?,serviceType: freezed == serviceType ? _self.serviceType : serviceType // ignore: cast_nullable_to_non_nullable
as String?,sort: freezed == sort ? _self.sort : sort // ignore: cast_nullable_to_non_nullable
as String?,tagId: freezed == tagId ? _self.tagId : tagId // ignore: cast_nullable_to_non_nullable
as int?,genderIndex: null == genderIndex ? _self.genderIndex : genderIndex // ignore: cast_nullable_to_non_nullable
as int,rangeValues: freezed == rangeValues ? _self.rangeValues : rangeValues // ignore: cast_nullable_to_non_nullable
as RangeValues?,
  ));
}

}


/// Adds pattern-matching-related methods to [FilterState].
extension FilterStatePatterns on FilterState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _FilterState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _FilterState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _FilterState value)  $default,){
final _that = this;
switch (_that) {
case _FilterState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _FilterState value)?  $default,){
final _that = this;
switch (_that) {
case _FilterState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( LayoutType selectType,  bool isLoading,  String type,  FilterResponse? filter,  Price? filterPrices,  List<int>? extras,  List<int>? categories,  List<int>? brands,  List<int> prices,  ShopFilterModel? shopFilter,  String? serviceType,  String? sort,  int? tagId,  int genderIndex,  RangeValues? rangeValues)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _FilterState() when $default != null:
return $default(_that.selectType,_that.isLoading,_that.type,_that.filter,_that.filterPrices,_that.extras,_that.categories,_that.brands,_that.prices,_that.shopFilter,_that.serviceType,_that.sort,_that.tagId,_that.genderIndex,_that.rangeValues);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( LayoutType selectType,  bool isLoading,  String type,  FilterResponse? filter,  Price? filterPrices,  List<int>? extras,  List<int>? categories,  List<int>? brands,  List<int> prices,  ShopFilterModel? shopFilter,  String? serviceType,  String? sort,  int? tagId,  int genderIndex,  RangeValues? rangeValues)  $default,) {final _that = this;
switch (_that) {
case _FilterState():
return $default(_that.selectType,_that.isLoading,_that.type,_that.filter,_that.filterPrices,_that.extras,_that.categories,_that.brands,_that.prices,_that.shopFilter,_that.serviceType,_that.sort,_that.tagId,_that.genderIndex,_that.rangeValues);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( LayoutType selectType,  bool isLoading,  String type,  FilterResponse? filter,  Price? filterPrices,  List<int>? extras,  List<int>? categories,  List<int>? brands,  List<int> prices,  ShopFilterModel? shopFilter,  String? serviceType,  String? sort,  int? tagId,  int genderIndex,  RangeValues? rangeValues)?  $default,) {final _that = this;
switch (_that) {
case _FilterState() when $default != null:
return $default(_that.selectType,_that.isLoading,_that.type,_that.filter,_that.filterPrices,_that.extras,_that.categories,_that.brands,_that.prices,_that.shopFilter,_that.serviceType,_that.sort,_that.tagId,_that.genderIndex,_that.rangeValues);case _:
  return null;

}
}

}

/// @nodoc


class _FilterState implements FilterState {
  const _FilterState({this.selectType = LayoutType.twoH, this.isLoading = true, this.type = "category", this.filter = null, this.filterPrices = null, final  List<int>? extras = const [], final  List<int>? categories = const [], final  List<int>? brands = const [], final  List<int> prices = const [], this.shopFilter = null, this.serviceType = null, this.sort = null, this.tagId = null, this.genderIndex = 2, this.rangeValues = null}): _extras = extras,_categories = categories,_brands = brands,_prices = prices;
  

@override@JsonKey() final  LayoutType selectType;
@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  String type;
@override@JsonKey() final  FilterResponse? filter;
@override@JsonKey() final  Price? filterPrices;
 final  List<int>? _extras;
@override@JsonKey() List<int>? get extras {
  final value = _extras;
  if (value == null) return null;
  if (_extras is EqualUnmodifiableListView) return _extras;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<int>? _categories;
@override@JsonKey() List<int>? get categories {
  final value = _categories;
  if (value == null) return null;
  if (_categories is EqualUnmodifiableListView) return _categories;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<int>? _brands;
@override@JsonKey() List<int>? get brands {
  final value = _brands;
  if (value == null) return null;
  if (_brands is EqualUnmodifiableListView) return _brands;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  List<int> _prices;
@override@JsonKey() List<int> get prices {
  if (_prices is EqualUnmodifiableListView) return _prices;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_prices);
}

@override@JsonKey() final  ShopFilterModel? shopFilter;
@override@JsonKey() final  String? serviceType;
@override@JsonKey() final  String? sort;
@override@JsonKey() final  int? tagId;
@override@JsonKey() final  int genderIndex;
@override@JsonKey() final  RangeValues? rangeValues;

/// Create a copy of FilterState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$FilterStateCopyWith<_FilterState> get copyWith => __$FilterStateCopyWithImpl<_FilterState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _FilterState&&(identical(other.selectType, selectType) || other.selectType == selectType)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.type, type) || other.type == type)&&(identical(other.filter, filter) || other.filter == filter)&&(identical(other.filterPrices, filterPrices) || other.filterPrices == filterPrices)&&const DeepCollectionEquality().equals(other._extras, _extras)&&const DeepCollectionEquality().equals(other._categories, _categories)&&const DeepCollectionEquality().equals(other._brands, _brands)&&const DeepCollectionEquality().equals(other._prices, _prices)&&(identical(other.shopFilter, shopFilter) || other.shopFilter == shopFilter)&&(identical(other.serviceType, serviceType) || other.serviceType == serviceType)&&(identical(other.sort, sort) || other.sort == sort)&&(identical(other.tagId, tagId) || other.tagId == tagId)&&(identical(other.genderIndex, genderIndex) || other.genderIndex == genderIndex)&&(identical(other.rangeValues, rangeValues) || other.rangeValues == rangeValues));
}


@override
int get hashCode => Object.hash(runtimeType,selectType,isLoading,type,filter,filterPrices,const DeepCollectionEquality().hash(_extras),const DeepCollectionEquality().hash(_categories),const DeepCollectionEquality().hash(_brands),const DeepCollectionEquality().hash(_prices),shopFilter,serviceType,sort,tagId,genderIndex,rangeValues);

@override
String toString() {
  return 'FilterState(selectType: $selectType, isLoading: $isLoading, type: $type, filter: $filter, filterPrices: $filterPrices, extras: $extras, categories: $categories, brands: $brands, prices: $prices, shopFilter: $shopFilter, serviceType: $serviceType, sort: $sort, tagId: $tagId, genderIndex: $genderIndex, rangeValues: $rangeValues)';
}


}

/// @nodoc
abstract mixin class _$FilterStateCopyWith<$Res> implements $FilterStateCopyWith<$Res> {
  factory _$FilterStateCopyWith(_FilterState value, $Res Function(_FilterState) _then) = __$FilterStateCopyWithImpl;
@override @useResult
$Res call({
 LayoutType selectType, bool isLoading, String type, FilterResponse? filter, Price? filterPrices, List<int>? extras, List<int>? categories, List<int>? brands, List<int> prices, ShopFilterModel? shopFilter, String? serviceType, String? sort, int? tagId, int genderIndex, RangeValues? rangeValues
});




}
/// @nodoc
class __$FilterStateCopyWithImpl<$Res>
    implements _$FilterStateCopyWith<$Res> {
  __$FilterStateCopyWithImpl(this._self, this._then);

  final _FilterState _self;
  final $Res Function(_FilterState) _then;

/// Create a copy of FilterState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? selectType = null,Object? isLoading = null,Object? type = null,Object? filter = freezed,Object? filterPrices = freezed,Object? extras = freezed,Object? categories = freezed,Object? brands = freezed,Object? prices = null,Object? shopFilter = freezed,Object? serviceType = freezed,Object? sort = freezed,Object? tagId = freezed,Object? genderIndex = null,Object? rangeValues = freezed,}) {
  return _then(_FilterState(
selectType: null == selectType ? _self.selectType : selectType // ignore: cast_nullable_to_non_nullable
as LayoutType,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,filter: freezed == filter ? _self.filter : filter // ignore: cast_nullable_to_non_nullable
as FilterResponse?,filterPrices: freezed == filterPrices ? _self.filterPrices : filterPrices // ignore: cast_nullable_to_non_nullable
as Price?,extras: freezed == extras ? _self._extras : extras // ignore: cast_nullable_to_non_nullable
as List<int>?,categories: freezed == categories ? _self._categories : categories // ignore: cast_nullable_to_non_nullable
as List<int>?,brands: freezed == brands ? _self._brands : brands // ignore: cast_nullable_to_non_nullable
as List<int>?,prices: null == prices ? _self._prices : prices // ignore: cast_nullable_to_non_nullable
as List<int>,shopFilter: freezed == shopFilter ? _self.shopFilter : shopFilter // ignore: cast_nullable_to_non_nullable
as ShopFilterModel?,serviceType: freezed == serviceType ? _self.serviceType : serviceType // ignore: cast_nullable_to_non_nullable
as String?,sort: freezed == sort ? _self.sort : sort // ignore: cast_nullable_to_non_nullable
as String?,tagId: freezed == tagId ? _self.tagId : tagId // ignore: cast_nullable_to_non_nullable
as int?,genderIndex: null == genderIndex ? _self.genderIndex : genderIndex // ignore: cast_nullable_to_non_nullable
as int,rangeValues: freezed == rangeValues ? _self.rangeValues : rangeValues // ignore: cast_nullable_to_non_nullable
as RangeValues?,
  ));
}


}

// dart format on
