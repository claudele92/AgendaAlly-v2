// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'service_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$ServiceEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ServiceEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'ServiceEvent()';
}


}

/// @nodoc
class $ServiceEventCopyWith<$Res>  {
$ServiceEventCopyWith(ServiceEvent _, $Res Function(ServiceEvent) __);
}


/// Adds pattern-matching-related methods to [ServiceEvent].
extension ServiceEventPatterns on ServiceEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchServices value)?  fetchServices,TResult Function( FetchServiceCategories value)?  fetchServiceCategories,TResult Function( SelectServiceCategory value)?  selectServiceCategory,TResult Function( SelectServiceCategorChild value)?  selectServiceCategoryChild,TResult Function( SelectService value)?  selectService,TResult Function( SelectExtraIndex value)?  selectExtraIndex,TResult Function( SelectAllExtras value)?  selectAllExtras,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchServices() when fetchServices != null:
return fetchServices(_that);case FetchServiceCategories() when fetchServiceCategories != null:
return fetchServiceCategories(_that);case SelectServiceCategory() when selectServiceCategory != null:
return selectServiceCategory(_that);case SelectServiceCategorChild() when selectServiceCategoryChild != null:
return selectServiceCategoryChild(_that);case SelectService() when selectService != null:
return selectService(_that);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that);case SelectAllExtras() when selectAllExtras != null:
return selectAllExtras(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchServices value)  fetchServices,required TResult Function( FetchServiceCategories value)  fetchServiceCategories,required TResult Function( SelectServiceCategory value)  selectServiceCategory,required TResult Function( SelectServiceCategorChild value)  selectServiceCategoryChild,required TResult Function( SelectService value)  selectService,required TResult Function( SelectExtraIndex value)  selectExtraIndex,required TResult Function( SelectAllExtras value)  selectAllExtras,}){
final _that = this;
switch (_that) {
case FetchServices():
return fetchServices(_that);case FetchServiceCategories():
return fetchServiceCategories(_that);case SelectServiceCategory():
return selectServiceCategory(_that);case SelectServiceCategorChild():
return selectServiceCategoryChild(_that);case SelectService():
return selectService(_that);case SelectExtraIndex():
return selectExtraIndex(_that);case SelectAllExtras():
return selectAllExtras(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchServices value)?  fetchServices,TResult? Function( FetchServiceCategories value)?  fetchServiceCategories,TResult? Function( SelectServiceCategory value)?  selectServiceCategory,TResult? Function( SelectServiceCategorChild value)?  selectServiceCategoryChild,TResult? Function( SelectService value)?  selectService,TResult? Function( SelectExtraIndex value)?  selectExtraIndex,TResult? Function( SelectAllExtras value)?  selectAllExtras,}){
final _that = this;
switch (_that) {
case FetchServices() when fetchServices != null:
return fetchServices(_that);case FetchServiceCategories() when fetchServiceCategories != null:
return fetchServiceCategories(_that);case SelectServiceCategory() when selectServiceCategory != null:
return selectServiceCategory(_that);case SelectServiceCategorChild() when selectServiceCategoryChild != null:
return selectServiceCategoryChild(_that);case SelectService() when selectService != null:
return selectService(_that);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that);case SelectAllExtras() when selectAllExtras != null:
return selectAllExtras(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool isRefresh,  int? shopId,  int? categoryId,  int? masterId,  RefreshController? controller)?  fetchServices,TResult Function( BuildContext context,  bool isRefresh,  int? shopId,  int? masterId,  int? categoryId,  RefreshController? controller)?  fetchServiceCategories,TResult Function( CategoryData? category,  int? shopId,  int? masterId,  RefreshController? controller,  bool isRefresh)?  selectServiceCategory,TResult Function( CategoryData? category,  CategoryData? parent,  int? shopId,  int? masterId,  RefreshController? controller,  bool isRefresh)?  selectServiceCategoryChild,TResult Function( ServiceModel? service,  ServiceExtra? extra)?  selectService,TResult Function( ServiceExtra? extra)?  selectExtraIndex,TResult Function( List<ServiceExtra>? extra)?  selectAllExtras,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchServices() when fetchServices != null:
return fetchServices(_that.context,_that.isRefresh,_that.shopId,_that.categoryId,_that.masterId,_that.controller);case FetchServiceCategories() when fetchServiceCategories != null:
return fetchServiceCategories(_that.context,_that.isRefresh,_that.shopId,_that.masterId,_that.categoryId,_that.controller);case SelectServiceCategory() when selectServiceCategory != null:
return selectServiceCategory(_that.category,_that.shopId,_that.masterId,_that.controller,_that.isRefresh);case SelectServiceCategorChild() when selectServiceCategoryChild != null:
return selectServiceCategoryChild(_that.category,_that.parent,_that.shopId,_that.masterId,_that.controller,_that.isRefresh);case SelectService() when selectService != null:
return selectService(_that.service,_that.extra);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that.extra);case SelectAllExtras() when selectAllExtras != null:
return selectAllExtras(_that.extra);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool isRefresh,  int? shopId,  int? categoryId,  int? masterId,  RefreshController? controller)  fetchServices,required TResult Function( BuildContext context,  bool isRefresh,  int? shopId,  int? masterId,  int? categoryId,  RefreshController? controller)  fetchServiceCategories,required TResult Function( CategoryData? category,  int? shopId,  int? masterId,  RefreshController? controller,  bool isRefresh)  selectServiceCategory,required TResult Function( CategoryData? category,  CategoryData? parent,  int? shopId,  int? masterId,  RefreshController? controller,  bool isRefresh)  selectServiceCategoryChild,required TResult Function( ServiceModel? service,  ServiceExtra? extra)  selectService,required TResult Function( ServiceExtra? extra)  selectExtraIndex,required TResult Function( List<ServiceExtra>? extra)  selectAllExtras,}) {final _that = this;
switch (_that) {
case FetchServices():
return fetchServices(_that.context,_that.isRefresh,_that.shopId,_that.categoryId,_that.masterId,_that.controller);case FetchServiceCategories():
return fetchServiceCategories(_that.context,_that.isRefresh,_that.shopId,_that.masterId,_that.categoryId,_that.controller);case SelectServiceCategory():
return selectServiceCategory(_that.category,_that.shopId,_that.masterId,_that.controller,_that.isRefresh);case SelectServiceCategorChild():
return selectServiceCategoryChild(_that.category,_that.parent,_that.shopId,_that.masterId,_that.controller,_that.isRefresh);case SelectService():
return selectService(_that.service,_that.extra);case SelectExtraIndex():
return selectExtraIndex(_that.extra);case SelectAllExtras():
return selectAllExtras(_that.extra);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool isRefresh,  int? shopId,  int? categoryId,  int? masterId,  RefreshController? controller)?  fetchServices,TResult? Function( BuildContext context,  bool isRefresh,  int? shopId,  int? masterId,  int? categoryId,  RefreshController? controller)?  fetchServiceCategories,TResult? Function( CategoryData? category,  int? shopId,  int? masterId,  RefreshController? controller,  bool isRefresh)?  selectServiceCategory,TResult? Function( CategoryData? category,  CategoryData? parent,  int? shopId,  int? masterId,  RefreshController? controller,  bool isRefresh)?  selectServiceCategoryChild,TResult? Function( ServiceModel? service,  ServiceExtra? extra)?  selectService,TResult? Function( ServiceExtra? extra)?  selectExtraIndex,TResult? Function( List<ServiceExtra>? extra)?  selectAllExtras,}) {final _that = this;
switch (_that) {
case FetchServices() when fetchServices != null:
return fetchServices(_that.context,_that.isRefresh,_that.shopId,_that.categoryId,_that.masterId,_that.controller);case FetchServiceCategories() when fetchServiceCategories != null:
return fetchServiceCategories(_that.context,_that.isRefresh,_that.shopId,_that.masterId,_that.categoryId,_that.controller);case SelectServiceCategory() when selectServiceCategory != null:
return selectServiceCategory(_that.category,_that.shopId,_that.masterId,_that.controller,_that.isRefresh);case SelectServiceCategorChild() when selectServiceCategoryChild != null:
return selectServiceCategoryChild(_that.category,_that.parent,_that.shopId,_that.masterId,_that.controller,_that.isRefresh);case SelectService() when selectService != null:
return selectService(_that.service,_that.extra);case SelectExtraIndex() when selectExtraIndex != null:
return selectExtraIndex(_that.extra);case SelectAllExtras() when selectAllExtras != null:
return selectAllExtras(_that.extra);case _:
  return null;

}
}

}

/// @nodoc


class FetchServices implements ServiceEvent {
  const FetchServices(this.context, {this.isRefresh = false, this.shopId, this.categoryId, this.masterId, this.controller});
  

 final  BuildContext context;
@JsonKey() final  bool isRefresh;
 final  int? shopId;
 final  int? categoryId;
 final  int? masterId;
 final  RefreshController? controller;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchServicesCopyWith<FetchServices> get copyWith => _$FetchServicesCopyWithImpl<FetchServices>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchServices&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.categoryId, categoryId) || other.categoryId == categoryId)&&(identical(other.masterId, masterId) || other.masterId == masterId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,categoryId,masterId,controller);

@override
String toString() {
  return 'ServiceEvent.fetchServices(context: $context, isRefresh: $isRefresh, shopId: $shopId, categoryId: $categoryId, masterId: $masterId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchServicesCopyWith<$Res> implements $ServiceEventCopyWith<$Res> {
  factory $FetchServicesCopyWith(FetchServices value, $Res Function(FetchServices) _then) = _$FetchServicesCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool isRefresh, int? shopId, int? categoryId, int? masterId, RefreshController? controller
});




}
/// @nodoc
class _$FetchServicesCopyWithImpl<$Res>
    implements $FetchServicesCopyWith<$Res> {
  _$FetchServicesCopyWithImpl(this._self, this._then);

  final FetchServices _self;
  final $Res Function(FetchServices) _then;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = null,Object? shopId = freezed,Object? categoryId = freezed,Object? masterId = freezed,Object? controller = freezed,}) {
  return _then(FetchServices(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: null == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,categoryId: freezed == categoryId ? _self.categoryId : categoryId // ignore: cast_nullable_to_non_nullable
as int?,masterId: freezed == masterId ? _self.masterId : masterId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchServiceCategories implements ServiceEvent {
  const FetchServiceCategories(this.context, {this.isRefresh = false, this.shopId, this.masterId, this.categoryId, this.controller});
  

 final  BuildContext context;
@JsonKey() final  bool isRefresh;
 final  int? shopId;
 final  int? masterId;
 final  int? categoryId;
 final  RefreshController? controller;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchServiceCategoriesCopyWith<FetchServiceCategories> get copyWith => _$FetchServiceCategoriesCopyWithImpl<FetchServiceCategories>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchServiceCategories&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.masterId, masterId) || other.masterId == masterId)&&(identical(other.categoryId, categoryId) || other.categoryId == categoryId)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,masterId,categoryId,controller);

@override
String toString() {
  return 'ServiceEvent.fetchServiceCategories(context: $context, isRefresh: $isRefresh, shopId: $shopId, masterId: $masterId, categoryId: $categoryId, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchServiceCategoriesCopyWith<$Res> implements $ServiceEventCopyWith<$Res> {
  factory $FetchServiceCategoriesCopyWith(FetchServiceCategories value, $Res Function(FetchServiceCategories) _then) = _$FetchServiceCategoriesCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool isRefresh, int? shopId, int? masterId, int? categoryId, RefreshController? controller
});




}
/// @nodoc
class _$FetchServiceCategoriesCopyWithImpl<$Res>
    implements $FetchServiceCategoriesCopyWith<$Res> {
  _$FetchServiceCategoriesCopyWithImpl(this._self, this._then);

  final FetchServiceCategories _self;
  final $Res Function(FetchServiceCategories) _then;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = null,Object? shopId = freezed,Object? masterId = freezed,Object? categoryId = freezed,Object? controller = freezed,}) {
  return _then(FetchServiceCategories(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: null == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,masterId: freezed == masterId ? _self.masterId : masterId // ignore: cast_nullable_to_non_nullable
as int?,categoryId: freezed == categoryId ? _self.categoryId : categoryId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class SelectServiceCategory implements ServiceEvent {
  const SelectServiceCategory({this.category, this.shopId, this.masterId, this.controller, this.isRefresh = false});
  

 final  CategoryData? category;
 final  int? shopId;
 final  int? masterId;
 final  RefreshController? controller;
@JsonKey() final  bool isRefresh;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectServiceCategoryCopyWith<SelectServiceCategory> get copyWith => _$SelectServiceCategoryCopyWithImpl<SelectServiceCategory>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectServiceCategory&&(identical(other.category, category) || other.category == category)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.masterId, masterId) || other.masterId == masterId)&&(identical(other.controller, controller) || other.controller == controller)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh));
}


@override
int get hashCode => Object.hash(runtimeType,category,shopId,masterId,controller,isRefresh);

@override
String toString() {
  return 'ServiceEvent.selectServiceCategory(category: $category, shopId: $shopId, masterId: $masterId, controller: $controller, isRefresh: $isRefresh)';
}


}

/// @nodoc
abstract mixin class $SelectServiceCategoryCopyWith<$Res> implements $ServiceEventCopyWith<$Res> {
  factory $SelectServiceCategoryCopyWith(SelectServiceCategory value, $Res Function(SelectServiceCategory) _then) = _$SelectServiceCategoryCopyWithImpl;
@useResult
$Res call({
 CategoryData? category, int? shopId, int? masterId, RefreshController? controller, bool isRefresh
});




}
/// @nodoc
class _$SelectServiceCategoryCopyWithImpl<$Res>
    implements $SelectServiceCategoryCopyWith<$Res> {
  _$SelectServiceCategoryCopyWithImpl(this._self, this._then);

  final SelectServiceCategory _self;
  final $Res Function(SelectServiceCategory) _then;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? category = freezed,Object? shopId = freezed,Object? masterId = freezed,Object? controller = freezed,Object? isRefresh = null,}) {
  return _then(SelectServiceCategory(
category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as CategoryData?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,masterId: freezed == masterId ? _self.masterId : masterId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,isRefresh: null == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc


class SelectServiceCategorChild implements ServiceEvent {
  const SelectServiceCategorChild({this.category, this.parent, this.shopId, this.masterId, this.controller, this.isRefresh = false});
  

 final  CategoryData? category;
 final  CategoryData? parent;
 final  int? shopId;
 final  int? masterId;
 final  RefreshController? controller;
@JsonKey() final  bool isRefresh;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectServiceCategorChildCopyWith<SelectServiceCategorChild> get copyWith => _$SelectServiceCategorChildCopyWithImpl<SelectServiceCategorChild>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectServiceCategorChild&&(identical(other.category, category) || other.category == category)&&(identical(other.parent, parent) || other.parent == parent)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.masterId, masterId) || other.masterId == masterId)&&(identical(other.controller, controller) || other.controller == controller)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh));
}


@override
int get hashCode => Object.hash(runtimeType,category,parent,shopId,masterId,controller,isRefresh);

@override
String toString() {
  return 'ServiceEvent.selectServiceCategoryChild(category: $category, parent: $parent, shopId: $shopId, masterId: $masterId, controller: $controller, isRefresh: $isRefresh)';
}


}

/// @nodoc
abstract mixin class $SelectServiceCategorChildCopyWith<$Res> implements $ServiceEventCopyWith<$Res> {
  factory $SelectServiceCategorChildCopyWith(SelectServiceCategorChild value, $Res Function(SelectServiceCategorChild) _then) = _$SelectServiceCategorChildCopyWithImpl;
@useResult
$Res call({
 CategoryData? category, CategoryData? parent, int? shopId, int? masterId, RefreshController? controller, bool isRefresh
});




}
/// @nodoc
class _$SelectServiceCategorChildCopyWithImpl<$Res>
    implements $SelectServiceCategorChildCopyWith<$Res> {
  _$SelectServiceCategorChildCopyWithImpl(this._self, this._then);

  final SelectServiceCategorChild _self;
  final $Res Function(SelectServiceCategorChild) _then;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? category = freezed,Object? parent = freezed,Object? shopId = freezed,Object? masterId = freezed,Object? controller = freezed,Object? isRefresh = null,}) {
  return _then(SelectServiceCategorChild(
category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as CategoryData?,parent: freezed == parent ? _self.parent : parent // ignore: cast_nullable_to_non_nullable
as CategoryData?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,masterId: freezed == masterId ? _self.masterId : masterId // ignore: cast_nullable_to_non_nullable
as int?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,isRefresh: null == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc


class SelectService implements ServiceEvent {
  const SelectService({required this.service, this.extra});
  

 final  ServiceModel? service;
 final  ServiceExtra? extra;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectServiceCopyWith<SelectService> get copyWith => _$SelectServiceCopyWithImpl<SelectService>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectService&&(identical(other.service, service) || other.service == service)&&(identical(other.extra, extra) || other.extra == extra));
}


@override
int get hashCode => Object.hash(runtimeType,service,extra);

@override
String toString() {
  return 'ServiceEvent.selectService(service: $service, extra: $extra)';
}


}

/// @nodoc
abstract mixin class $SelectServiceCopyWith<$Res> implements $ServiceEventCopyWith<$Res> {
  factory $SelectServiceCopyWith(SelectService value, $Res Function(SelectService) _then) = _$SelectServiceCopyWithImpl;
@useResult
$Res call({
 ServiceModel? service, ServiceExtra? extra
});




}
/// @nodoc
class _$SelectServiceCopyWithImpl<$Res>
    implements $SelectServiceCopyWith<$Res> {
  _$SelectServiceCopyWithImpl(this._self, this._then);

  final SelectService _self;
  final $Res Function(SelectService) _then;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? service = freezed,Object? extra = freezed,}) {
  return _then(SelectService(
service: freezed == service ? _self.service : service // ignore: cast_nullable_to_non_nullable
as ServiceModel?,extra: freezed == extra ? _self.extra : extra // ignore: cast_nullable_to_non_nullable
as ServiceExtra?,
  ));
}


}

/// @nodoc


class SelectExtraIndex implements ServiceEvent {
  const SelectExtraIndex({required this.extra});
  

 final  ServiceExtra? extra;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectExtraIndexCopyWith<SelectExtraIndex> get copyWith => _$SelectExtraIndexCopyWithImpl<SelectExtraIndex>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectExtraIndex&&(identical(other.extra, extra) || other.extra == extra));
}


@override
int get hashCode => Object.hash(runtimeType,extra);

@override
String toString() {
  return 'ServiceEvent.selectExtraIndex(extra: $extra)';
}


}

/// @nodoc
abstract mixin class $SelectExtraIndexCopyWith<$Res> implements $ServiceEventCopyWith<$Res> {
  factory $SelectExtraIndexCopyWith(SelectExtraIndex value, $Res Function(SelectExtraIndex) _then) = _$SelectExtraIndexCopyWithImpl;
@useResult
$Res call({
 ServiceExtra? extra
});




}
/// @nodoc
class _$SelectExtraIndexCopyWithImpl<$Res>
    implements $SelectExtraIndexCopyWith<$Res> {
  _$SelectExtraIndexCopyWithImpl(this._self, this._then);

  final SelectExtraIndex _self;
  final $Res Function(SelectExtraIndex) _then;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? extra = freezed,}) {
  return _then(SelectExtraIndex(
extra: freezed == extra ? _self.extra : extra // ignore: cast_nullable_to_non_nullable
as ServiceExtra?,
  ));
}


}

/// @nodoc


class SelectAllExtras implements ServiceEvent {
  const SelectAllExtras({required final  List<ServiceExtra>? extra}): _extra = extra;
  

 final  List<ServiceExtra>? _extra;
 List<ServiceExtra>? get extra {
  final value = _extra;
  if (value == null) return null;
  if (_extra is EqualUnmodifiableListView) return _extra;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}


/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SelectAllExtrasCopyWith<SelectAllExtras> get copyWith => _$SelectAllExtrasCopyWithImpl<SelectAllExtras>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SelectAllExtras&&const DeepCollectionEquality().equals(other._extra, _extra));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_extra));

@override
String toString() {
  return 'ServiceEvent.selectAllExtras(extra: $extra)';
}


}

/// @nodoc
abstract mixin class $SelectAllExtrasCopyWith<$Res> implements $ServiceEventCopyWith<$Res> {
  factory $SelectAllExtrasCopyWith(SelectAllExtras value, $Res Function(SelectAllExtras) _then) = _$SelectAllExtrasCopyWithImpl;
@useResult
$Res call({
 List<ServiceExtra>? extra
});




}
/// @nodoc
class _$SelectAllExtrasCopyWithImpl<$Res>
    implements $SelectAllExtrasCopyWith<$Res> {
  _$SelectAllExtrasCopyWithImpl(this._self, this._then);

  final SelectAllExtras _self;
  final $Res Function(SelectAllExtras) _then;

/// Create a copy of ServiceEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? extra = freezed,}) {
  return _then(SelectAllExtras(
extra: freezed == extra ? _self._extra : extra // ignore: cast_nullable_to_non_nullable
as List<ServiceExtra>?,
  ));
}


}

/// @nodoc
mixin _$ServiceState {

 List<ServiceModel> get services; List<CategoryData> get serviceCategories; List<ServiceModel> get selectServices; List<ServiceExtra> get selectExtras; bool get isLoading; CategoryData? get selectedCategory; CategoryData? get selectedChildCategory;
/// Create a copy of ServiceState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ServiceStateCopyWith<ServiceState> get copyWith => _$ServiceStateCopyWithImpl<ServiceState>(this as ServiceState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ServiceState&&const DeepCollectionEquality().equals(other.services, services)&&const DeepCollectionEquality().equals(other.serviceCategories, serviceCategories)&&const DeepCollectionEquality().equals(other.selectServices, selectServices)&&const DeepCollectionEquality().equals(other.selectExtras, selectExtras)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.selectedCategory, selectedCategory) || other.selectedCategory == selectedCategory)&&(identical(other.selectedChildCategory, selectedChildCategory) || other.selectedChildCategory == selectedChildCategory));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(services),const DeepCollectionEquality().hash(serviceCategories),const DeepCollectionEquality().hash(selectServices),const DeepCollectionEquality().hash(selectExtras),isLoading,selectedCategory,selectedChildCategory);

@override
String toString() {
  return 'ServiceState(services: $services, serviceCategories: $serviceCategories, selectServices: $selectServices, selectExtras: $selectExtras, isLoading: $isLoading, selectedCategory: $selectedCategory, selectedChildCategory: $selectedChildCategory)';
}


}

/// @nodoc
abstract mixin class $ServiceStateCopyWith<$Res>  {
  factory $ServiceStateCopyWith(ServiceState value, $Res Function(ServiceState) _then) = _$ServiceStateCopyWithImpl;
@useResult
$Res call({
 List<ServiceModel> services, List<CategoryData> serviceCategories, List<ServiceModel> selectServices, List<ServiceExtra> selectExtras, bool isLoading, CategoryData? selectedCategory, CategoryData? selectedChildCategory
});




}
/// @nodoc
class _$ServiceStateCopyWithImpl<$Res>
    implements $ServiceStateCopyWith<$Res> {
  _$ServiceStateCopyWithImpl(this._self, this._then);

  final ServiceState _self;
  final $Res Function(ServiceState) _then;

/// Create a copy of ServiceState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? services = null,Object? serviceCategories = null,Object? selectServices = null,Object? selectExtras = null,Object? isLoading = null,Object? selectedCategory = freezed,Object? selectedChildCategory = freezed,}) {
  return _then(_self.copyWith(
services: null == services ? _self.services : services // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,serviceCategories: null == serviceCategories ? _self.serviceCategories : serviceCategories // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,selectServices: null == selectServices ? _self.selectServices : selectServices // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,selectExtras: null == selectExtras ? _self.selectExtras : selectExtras // ignore: cast_nullable_to_non_nullable
as List<ServiceExtra>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,selectedCategory: freezed == selectedCategory ? _self.selectedCategory : selectedCategory // ignore: cast_nullable_to_non_nullable
as CategoryData?,selectedChildCategory: freezed == selectedChildCategory ? _self.selectedChildCategory : selectedChildCategory // ignore: cast_nullable_to_non_nullable
as CategoryData?,
  ));
}

}


/// Adds pattern-matching-related methods to [ServiceState].
extension ServiceStatePatterns on ServiceState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ServiceState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ServiceState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ServiceState value)  $default,){
final _that = this;
switch (_that) {
case _ServiceState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ServiceState value)?  $default,){
final _that = this;
switch (_that) {
case _ServiceState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<ServiceModel> services,  List<CategoryData> serviceCategories,  List<ServiceModel> selectServices,  List<ServiceExtra> selectExtras,  bool isLoading,  CategoryData? selectedCategory,  CategoryData? selectedChildCategory)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ServiceState() when $default != null:
return $default(_that.services,_that.serviceCategories,_that.selectServices,_that.selectExtras,_that.isLoading,_that.selectedCategory,_that.selectedChildCategory);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<ServiceModel> services,  List<CategoryData> serviceCategories,  List<ServiceModel> selectServices,  List<ServiceExtra> selectExtras,  bool isLoading,  CategoryData? selectedCategory,  CategoryData? selectedChildCategory)  $default,) {final _that = this;
switch (_that) {
case _ServiceState():
return $default(_that.services,_that.serviceCategories,_that.selectServices,_that.selectExtras,_that.isLoading,_that.selectedCategory,_that.selectedChildCategory);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<ServiceModel> services,  List<CategoryData> serviceCategories,  List<ServiceModel> selectServices,  List<ServiceExtra> selectExtras,  bool isLoading,  CategoryData? selectedCategory,  CategoryData? selectedChildCategory)?  $default,) {final _that = this;
switch (_that) {
case _ServiceState() when $default != null:
return $default(_that.services,_that.serviceCategories,_that.selectServices,_that.selectExtras,_that.isLoading,_that.selectedCategory,_that.selectedChildCategory);case _:
  return null;

}
}

}

/// @nodoc


class _ServiceState implements ServiceState {
  const _ServiceState({final  List<ServiceModel> services = const [], final  List<CategoryData> serviceCategories = const [], final  List<ServiceModel> selectServices = const [], final  List<ServiceExtra> selectExtras = const [], this.isLoading = true, this.selectedCategory, this.selectedChildCategory}): _services = services,_serviceCategories = serviceCategories,_selectServices = selectServices,_selectExtras = selectExtras;
  

 final  List<ServiceModel> _services;
@override@JsonKey() List<ServiceModel> get services {
  if (_services is EqualUnmodifiableListView) return _services;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_services);
}

 final  List<CategoryData> _serviceCategories;
@override@JsonKey() List<CategoryData> get serviceCategories {
  if (_serviceCategories is EqualUnmodifiableListView) return _serviceCategories;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_serviceCategories);
}

 final  List<ServiceModel> _selectServices;
@override@JsonKey() List<ServiceModel> get selectServices {
  if (_selectServices is EqualUnmodifiableListView) return _selectServices;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_selectServices);
}

 final  List<ServiceExtra> _selectExtras;
@override@JsonKey() List<ServiceExtra> get selectExtras {
  if (_selectExtras is EqualUnmodifiableListView) return _selectExtras;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_selectExtras);
}

@override@JsonKey() final  bool isLoading;
@override final  CategoryData? selectedCategory;
@override final  CategoryData? selectedChildCategory;

/// Create a copy of ServiceState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ServiceStateCopyWith<_ServiceState> get copyWith => __$ServiceStateCopyWithImpl<_ServiceState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _ServiceState&&const DeepCollectionEquality().equals(other._services, _services)&&const DeepCollectionEquality().equals(other._serviceCategories, _serviceCategories)&&const DeepCollectionEquality().equals(other._selectServices, _selectServices)&&const DeepCollectionEquality().equals(other._selectExtras, _selectExtras)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.selectedCategory, selectedCategory) || other.selectedCategory == selectedCategory)&&(identical(other.selectedChildCategory, selectedChildCategory) || other.selectedChildCategory == selectedChildCategory));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_services),const DeepCollectionEquality().hash(_serviceCategories),const DeepCollectionEquality().hash(_selectServices),const DeepCollectionEquality().hash(_selectExtras),isLoading,selectedCategory,selectedChildCategory);

@override
String toString() {
  return 'ServiceState(services: $services, serviceCategories: $serviceCategories, selectServices: $selectServices, selectExtras: $selectExtras, isLoading: $isLoading, selectedCategory: $selectedCategory, selectedChildCategory: $selectedChildCategory)';
}


}

/// @nodoc
abstract mixin class _$ServiceStateCopyWith<$Res> implements $ServiceStateCopyWith<$Res> {
  factory _$ServiceStateCopyWith(_ServiceState value, $Res Function(_ServiceState) _then) = __$ServiceStateCopyWithImpl;
@override @useResult
$Res call({
 List<ServiceModel> services, List<CategoryData> serviceCategories, List<ServiceModel> selectServices, List<ServiceExtra> selectExtras, bool isLoading, CategoryData? selectedCategory, CategoryData? selectedChildCategory
});




}
/// @nodoc
class __$ServiceStateCopyWithImpl<$Res>
    implements _$ServiceStateCopyWith<$Res> {
  __$ServiceStateCopyWithImpl(this._self, this._then);

  final _ServiceState _self;
  final $Res Function(_ServiceState) _then;

/// Create a copy of ServiceState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? services = null,Object? serviceCategories = null,Object? selectServices = null,Object? selectExtras = null,Object? isLoading = null,Object? selectedCategory = freezed,Object? selectedChildCategory = freezed,}) {
  return _then(_ServiceState(
services: null == services ? _self._services : services // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,serviceCategories: null == serviceCategories ? _self._serviceCategories : serviceCategories // ignore: cast_nullable_to_non_nullable
as List<CategoryData>,selectServices: null == selectServices ? _self._selectServices : selectServices // ignore: cast_nullable_to_non_nullable
as List<ServiceModel>,selectExtras: null == selectExtras ? _self._selectExtras : selectExtras // ignore: cast_nullable_to_non_nullable
as List<ServiceExtra>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,selectedCategory: freezed == selectedCategory ? _self.selectedCategory : selectedCategory // ignore: cast_nullable_to_non_nullable
as CategoryData?,selectedChildCategory: freezed == selectedChildCategory ? _self.selectedChildCategory : selectedChildCategory // ignore: cast_nullable_to_non_nullable
as CategoryData?,
  ));
}


}

// dart format on
