// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'category_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$CategoryEvent {

 BuildContext get context;
/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CategoryEventCopyWith<CategoryEvent> get copyWith => _$CategoryEventCopyWithImpl<CategoryEvent>(this as CategoryEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CategoryEvent&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'CategoryEvent(context: $context)';
}


}

/// @nodoc
abstract mixin class $CategoryEventCopyWith<$Res>  {
  factory $CategoryEventCopyWith(CategoryEvent value, $Res Function(CategoryEvent) _then) = _$CategoryEventCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$CategoryEventCopyWithImpl<$Res>
    implements $CategoryEventCopyWith<$Res> {
  _$CategoryEventCopyWithImpl(this._self, this._then);

  final CategoryEvent _self;
  final $Res Function(CategoryEvent) _then;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? context = null,}) {
  return _then(_self.copyWith(
context: null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}

}


/// Adds pattern-matching-related methods to [CategoryEvent].
extension CategoryEventPatterns on CategoryEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchCategory value)?  fetchCategory,TResult Function( FetchCategoryOfService value)?  fetchCategoryOfService,TResult Function( SelectCategory value)?  selectCategory,TResult Function( SelectCategoryTwo value)?  selectCategoryTwo,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchCategory() when fetchCategory != null:
return fetchCategory(_that);case FetchCategoryOfService() when fetchCategoryOfService != null:
return fetchCategoryOfService(_that);case SelectCategory() when selectCategory != null:
return selectCategory(_that);case SelectCategoryTwo() when selectCategoryTwo != null:
return selectCategoryTwo(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchCategory value)  fetchCategory,required TResult Function( FetchCategoryOfService value)  fetchCategoryOfService,required TResult Function( SelectCategory value)  selectCategory,required TResult Function( SelectCategoryTwo value)  selectCategoryTwo,}){
final _that = this;
switch (_that) {
case FetchCategory():
return fetchCategory(_that);case FetchCategoryOfService():
return fetchCategoryOfService(_that);case SelectCategory():
return selectCategory(_that);case SelectCategoryTwo():
return selectCategoryTwo(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchCategory value)?  fetchCategory,TResult? Function( FetchCategoryOfService value)?  fetchCategoryOfService,TResult? Function( SelectCategory value)?  selectCategory,TResult? Function( SelectCategoryTwo value)?  selectCategoryTwo,}){
final _that = this;
switch (_that) {
case FetchCategory() when fetchCategory != null:
return fetchCategory(_that);case FetchCategoryOfService() when fetchCategoryOfService != null:
return fetchCategoryOfService(_that);case SelectCategory() when selectCategory != null:
return selectCategory(_that);case SelectCategoryTwo() when selectCategoryTwo != null:
return selectCategoryTwo(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchCategory,TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchCategoryOfService,TResult Function( BuildContext context,  CategoryData? category)?  selectCategory,TResult Function( BuildContext context,  CategoryData? category)?  selectCategoryTwo,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchCategory() when fetchCategory != null:
return fetchCategory(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchCategoryOfService() when fetchCategoryOfService != null:
return fetchCategoryOfService(_that.context,_that.isRefresh,_that.shopId,_that.controller);case SelectCategory() when selectCategory != null:
return selectCategory(_that.context,_that.category);case SelectCategoryTwo() when selectCategoryTwo != null:
return selectCategoryTwo(_that.context,_that.category);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)  fetchCategory,required TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)  fetchCategoryOfService,required TResult Function( BuildContext context,  CategoryData? category)  selectCategory,required TResult Function( BuildContext context,  CategoryData? category)  selectCategoryTwo,}) {final _that = this;
switch (_that) {
case FetchCategory():
return fetchCategory(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchCategoryOfService():
return fetchCategoryOfService(_that.context,_that.isRefresh,_that.shopId,_that.controller);case SelectCategory():
return selectCategory(_that.context,_that.category);case SelectCategoryTwo():
return selectCategoryTwo(_that.context,_that.category);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchCategory,TResult? Function( BuildContext context,  bool? isRefresh,  int? shopId,  RefreshController? controller)?  fetchCategoryOfService,TResult? Function( BuildContext context,  CategoryData? category)?  selectCategory,TResult? Function( BuildContext context,  CategoryData? category)?  selectCategoryTwo,}) {final _that = this;
switch (_that) {
case FetchCategory() when fetchCategory != null:
return fetchCategory(_that.context,_that.isRefresh,_that.shopId,_that.controller);case FetchCategoryOfService() when fetchCategoryOfService != null:
return fetchCategoryOfService(_that.context,_that.isRefresh,_that.shopId,_that.controller);case SelectCategory() when selectCategory != null:
return selectCategory(_that.context,_that.category);case SelectCategoryTwo() when selectCategoryTwo != null:
return selectCategoryTwo(_that.context,_that.category);case _:
  return null;

}
}

}

/// @nodoc


class FetchCategory implements CategoryEvent {
  const FetchCategory(this.context, {this.isRefresh, this.shopId, this.controller});
  

@override final  BuildContext context;
 final  bool? isRefresh;
 final  int? shopId;
 final  RefreshController? controller;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchCategoryCopyWith<FetchCategory> get copyWith => _$FetchCategoryCopyWithImpl<FetchCategory>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchCategory&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,controller);

@override
String toString() {
  return 'CategoryEvent.fetchCategory(context: $context, isRefresh: $isRefresh, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchCategoryCopyWith<$Res> implements $CategoryEventCopyWith<$Res> {
  factory $FetchCategoryCopyWith(FetchCategory value, $Res Function(FetchCategory) _then) = _$FetchCategoryCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$FetchCategoryCopyWithImpl<$Res>
    implements $FetchCategoryCopyWith<$Res> {
  _$FetchCategoryCopyWithImpl(this._self, this._then);

  final FetchCategory _self;
  final $Res Function(FetchCategory) _then;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(FetchCategory(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchCategoryOfService implements CategoryEvent {
  const FetchCategoryOfService(this.context, {this.isRefresh, this.shopId, this.controller});
  

@override final  BuildContext context;
 final  bool? isRefresh;
 final  int? shopId;
 final  RefreshController? controller;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchCategoryOfServiceCopyWith<FetchCategoryOfService> get copyWith => _$FetchCategoryOfServiceCopyWithImpl<FetchCategoryOfService>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchCategoryOfService&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,controller);

@override
String toString() {
  return 'CategoryEvent.fetchCategoryOfService(context: $context, isRefresh: $isRefresh, shopId: $shopId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchCategoryOfServiceCopyWith<$Res> implements $CategoryEventCopyWith<$Res> {
  factory $FetchCategoryOfServiceCopyWith(FetchCategoryOfService value, $Res Function(FetchCategoryOfService) _then) = _$FetchCategoryOfServiceCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, RefreshController? controller
});




}
/// @nodoc
class _$FetchCategoryOfServiceCopyWithImpl<$Res>
    implements $FetchCategoryOfServiceCopyWith<$Res> {
  _$FetchCategoryOfServiceCopyWithImpl(this._self, this._then);

  final FetchCategoryOfService _self;
  final $Res Function(FetchCategoryOfService) _then;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? controller = freezed,}) {
  return _then(FetchCategoryOfService(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class SelectCategory implements CategoryEvent {
  const SelectCategory(this.context, {this.category});
  

@override final  BuildContext context;
 final  CategoryData? category;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectCategoryCopyWith<SelectCategory> get copyWith => _$SelectCategoryCopyWithImpl<SelectCategory>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectCategory&&(identical(other.context, context) || other.context == context)&&(identical(other.category, category) || other.category == category));
}


@override
int get hashCode => Object.hash(runtimeType,context,category);

@override
String toString() {
  return 'CategoryEvent.selectCategory(context: $context, category: $category)';
}


}

/// @nodoc
abstract mixin class $SelectCategoryCopyWith<$Res> implements $CategoryEventCopyWith<$Res> {
  factory $SelectCategoryCopyWith(SelectCategory value, $Res Function(SelectCategory) _then) = _$SelectCategoryCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, CategoryData? category
});




}
/// @nodoc
class _$SelectCategoryCopyWithImpl<$Res>
    implements $SelectCategoryCopyWith<$Res> {
  _$SelectCategoryCopyWithImpl(this._self, this._then);

  final SelectCategory _self;
  final $Res Function(SelectCategory) _then;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? category = freezed,}) {
  return _then(SelectCategory(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as CategoryData?,
  ));
}


}

/// @nodoc


class SelectCategoryTwo implements CategoryEvent {
  const SelectCategoryTwo(this.context, {this.category});
  

@override final  BuildContext context;
 final  CategoryData? category;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectCategoryTwoCopyWith<SelectCategoryTwo> get copyWith => _$SelectCategoryTwoCopyWithImpl<SelectCategoryTwo>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectCategoryTwo&&(identical(other.context, context) || other.context == context)&&(identical(other.category, category) || other.category == category));
}


@override
int get hashCode => Object.hash(runtimeType,context,category);

@override
String toString() {
  return 'CategoryEvent.selectCategoryTwo(context: $context, category: $category)';
}


}

/// @nodoc
abstract mixin class $SelectCategoryTwoCopyWith<$Res> implements $CategoryEventCopyWith<$Res> {
  factory $SelectCategoryTwoCopyWith(SelectCategoryTwo value, $Res Function(SelectCategoryTwo) _then) = _$SelectCategoryTwoCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, CategoryData? category
});




}
/// @nodoc
class _$SelectCategoryTwoCopyWithImpl<$Res>
    implements $SelectCategoryTwoCopyWith<$Res> {
  _$SelectCategoryTwoCopyWithImpl(this._self, this._then);

  final SelectCategoryTwo _self;
  final $Res Function(SelectCategoryTwo) _then;

/// Create a copy of CategoryEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? category = freezed,}) {
  return _then(SelectCategoryTwo(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as CategoryData?,
  ));
}


}

/// @nodoc
mixin _$CategoryState {

 List<CategoryData> get categories; List<CategoryData> get categoriesOfService; bool get isLoadingCategory; CategoryData? get selectCategory; CategoryData? get selectCategoryTwo;
/// Create a copy of CategoryState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CategoryStateCopyWith<CategoryState> get copyWith => _$CategoryStateCopyWithImpl<CategoryState>(this as CategoryState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CategoryState&&const DeepCollectionEquality().equals(other.categories, categories)&&const DeepCollectionEquality().equals(other.categoriesOfService, categoriesOfService)&&(identical(other.isLoadingCategory, isLoadingCategory) || other.isLoadingCategory == isLoadingCategory)&&(identical(other.selectCategory, selectCategory) || other.selectCategory == selectCategory)&&(identical(other.selectCategoryTwo, selectCategoryTwo) || other.selectCategoryTwo == selectCategoryTwo));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(categories),const DeepCollectionEquality().hash(categoriesOfService),isLoadingCategory,selectCategory,selectCategoryTwo);

@override
String toString() {
  return 'CategoryState(categories: $categories, categoriesOfService: $categoriesOfService, isLoadingCategory: $isLoadingCategory, selectCategory: $selectCategory, selectCategoryTwo: $selectCategoryTwo)';
}


}

/// @nodoc
abstract mixin class $CategoryStateCopyWith<$Res>  {
  factory $CategoryStateCopyWith(CategoryState value, $Res Function(CategoryState) _then) = _$CategoryStateCopyWithImpl;
@useResult
$Res call({
 List<CategoryData> categories, List<CategoryData> categoriesOfService, bool isLoadingCategory, CategoryData? selectCategory, CategoryData? selectCategoryTwo
});




}
/// @nodoc
class _$CategoryStateCopyWithImpl<$Res>
    implements $CategoryStateCopyWith<$Res> {
  _$CategoryStateCopyWithImpl(this._self, this._then);

  final CategoryState _self;
  final $Res Function(CategoryState) _then;

/// Create a copy of CategoryState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? categories = null,Object? categoriesOfService = null,Object? isLoadingCategory = null,Object? selectCategory = freezed,Object? selectCategoryTwo = freezed,}) {
  return _then(_self.copyWith(
categories: null == categories ? _self.categories : categories // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,categoriesOfService: null == categoriesOfService ? _self.categoriesOfService : categoriesOfService // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,isLoadingCategory: null == isLoadingCategory ? _self.isLoadingCategory : isLoadingCategory // ignore: cast_nullable_to_non_nullable
as bool,selectCategory: freezed == selectCategory ? _self.selectCategory : selectCategory // ignore: cast_nullable_to_non_nullable
as CategoryData?,selectCategoryTwo: freezed == selectCategoryTwo ? _self.selectCategoryTwo : selectCategoryTwo // ignore: cast_nullable_to_non_nullable
as CategoryData?,
  ));
}

}


/// Adds pattern-matching-related methods to [CategoryState].
extension CategoryStatePatterns on CategoryState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CategoryState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CategoryState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CategoryState value)  $default,){
final _that = this;
switch (_that) {
case _CategoryState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CategoryState value)?  $default,){
final _that = this;
switch (_that) {
case _CategoryState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<CategoryData> categories,  List<CategoryData> categoriesOfService,  bool isLoadingCategory,  CategoryData? selectCategory,  CategoryData? selectCategoryTwo)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CategoryState() when $default != null:
return $default(_that.categories,_that.categoriesOfService,_that.isLoadingCategory,_that.selectCategory,_that.selectCategoryTwo);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<CategoryData> categories,  List<CategoryData> categoriesOfService,  bool isLoadingCategory,  CategoryData? selectCategory,  CategoryData? selectCategoryTwo)  $default,) {final _that = this;
switch (_that) {
case _CategoryState():
return $default(_that.categories,_that.categoriesOfService,_that.isLoadingCategory,_that.selectCategory,_that.selectCategoryTwo);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<CategoryData> categories,  List<CategoryData> categoriesOfService,  bool isLoadingCategory,  CategoryData? selectCategory,  CategoryData? selectCategoryTwo)?  $default,) {final _that = this;
switch (_that) {
case _CategoryState() when $default != null:
return $default(_that.categories,_that.categoriesOfService,_that.isLoadingCategory,_that.selectCategory,_that.selectCategoryTwo);case _:
  return null;

}
}

}

/// @nodoc


class _CategoryState implements CategoryState {
  const _CategoryState({final  List<CategoryData> categories = const [], final  List<CategoryData> categoriesOfService = const [], this.isLoadingCategory = true, this.selectCategory = null, this.selectCategoryTwo = null}): _categories = categories,_categoriesOfService = categoriesOfService;
  

 final  List<CategoryData> _categories;
@override@JsonKey() List<CategoryData> get categories {
  if (_categories is EqualUnmodifiableListView) return _categories;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_categories);
}

 final  List<CategoryData> _categoriesOfService;
@override@JsonKey() List<CategoryData> get categoriesOfService {
  if (_categoriesOfService is EqualUnmodifiableListView) return _categoriesOfService;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_categoriesOfService);
}

@override@JsonKey() final  bool isLoadingCategory;
@override@JsonKey() final  CategoryData? selectCategory;
@override@JsonKey() final  CategoryData? selectCategoryTwo;

/// Create a copy of CategoryState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CategoryStateCopyWith<_CategoryState> get copyWith => __$CategoryStateCopyWithImpl<_CategoryState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CategoryState&&const DeepCollectionEquality().equals(other._categories, _categories)&&const DeepCollectionEquality().equals(other._categoriesOfService, _categoriesOfService)&&(identical(other.isLoadingCategory, isLoadingCategory) || other.isLoadingCategory == isLoadingCategory)&&(identical(other.selectCategory, selectCategory) || other.selectCategory == selectCategory)&&(identical(other.selectCategoryTwo, selectCategoryTwo) || other.selectCategoryTwo == selectCategoryTwo));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_categories),const DeepCollectionEquality().hash(_categoriesOfService),isLoadingCategory,selectCategory,selectCategoryTwo);

@override
String toString() {
  return 'CategoryState(categories: $categories, categoriesOfService: $categoriesOfService, isLoadingCategory: $isLoadingCategory, selectCategory: $selectCategory, selectCategoryTwo: $selectCategoryTwo)';
}


}

/// @nodoc
abstract mixin class _$CategoryStateCopyWith<$Res> implements $CategoryStateCopyWith<$Res> {
  factory _$CategoryStateCopyWith(_CategoryState value, $Res Function(_CategoryState) _then) = __$CategoryStateCopyWithImpl;
@override @useResult
$Res call({
 List<CategoryData> categories, List<CategoryData> categoriesOfService, bool isLoadingCategory, CategoryData? selectCategory, CategoryData? selectCategoryTwo
});




}
/// @nodoc
class __$CategoryStateCopyWithImpl<$Res>
    implements _$CategoryStateCopyWith<$Res> {
  __$CategoryStateCopyWithImpl(this._self, this._then);

  final _CategoryState _self;
  final $Res Function(_CategoryState) _then;

/// Create a copy of CategoryState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? categories = null,Object? categoriesOfService = null,Object? isLoadingCategory = null,Object? selectCategory = freezed,Object? selectCategoryTwo = freezed,}) {
  return _then(_CategoryState(
categories: null == categories ? _self._categories : categories // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,categoriesOfService: null == categoriesOfService ? _self._categoriesOfService : categoriesOfService // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,isLoadingCategory: null == isLoadingCategory ? _self.isLoadingCategory : isLoadingCategory // ignore: cast_nullable_to_non_nullable
as bool,selectCategory: freezed == selectCategory ? _self.selectCategory : selectCategory // ignore: cast_nullable_to_non_nullable
as CategoryData?,selectCategoryTwo: freezed == selectCategoryTwo ? _self.selectCategoryTwo : selectCategoryTwo // ignore: cast_nullable_to_non_nullable
as CategoryData?,
  ));
}


}

// dart format on
