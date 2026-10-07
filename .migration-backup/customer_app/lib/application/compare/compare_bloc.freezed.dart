// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'compare_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$CompareEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CompareEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'CompareEvent()';
}


}

/// @nodoc
class $CompareEventCopyWith<$Res>  {
$CompareEventCopyWith(CompareEvent _, $Res Function(CompareEvent) __);
}


/// Adds pattern-matching-related methods to [CompareEvent].
extension CompareEventPatterns on CompareEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchCompare value)?  fetchCompare,TResult Function( SetExtraGroup value)?  setExtraGroup,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchCompare() when fetchCompare != null:
return fetchCompare(_that);case SetExtraGroup() when setExtraGroup != null:
return setExtraGroup(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchCompare value)  fetchCompare,required TResult Function( SetExtraGroup value)  setExtraGroup,}){
final _that = this;
switch (_that) {
case FetchCompare():
return fetchCompare(_that);case SetExtraGroup():
return setExtraGroup(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchCompare value)?  fetchCompare,TResult? Function( SetExtraGroup value)?  setExtraGroup,}){
final _that = this;
switch (_that) {
case FetchCompare() when fetchCompare != null:
return fetchCompare(_that);case SetExtraGroup() when setExtraGroup != null:
return setExtraGroup(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchCompare,TResult Function( List<ProductData> products)?  setExtraGroup,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchCompare() when fetchCompare != null:
return fetchCompare(_that.context,_that.isRefresh,_that.controller);case SetExtraGroup() when setExtraGroup != null:
return setExtraGroup(_that.products);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchCompare,required TResult Function( List<ProductData> products)  setExtraGroup,}) {final _that = this;
switch (_that) {
case FetchCompare():
return fetchCompare(_that.context,_that.isRefresh,_that.controller);case SetExtraGroup():
return setExtraGroup(_that.products);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchCompare,TResult? Function( List<ProductData> products)?  setExtraGroup,}) {final _that = this;
switch (_that) {
case FetchCompare() when fetchCompare != null:
return fetchCompare(_that.context,_that.isRefresh,_that.controller);case SetExtraGroup() when setExtraGroup != null:
return setExtraGroup(_that.products);case _:
  return null;

}
}

}

/// @nodoc


class FetchCompare implements CompareEvent {
  const FetchCompare(this.context, {this.isRefresh, this.controller});
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of CompareEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchCompareCopyWith<FetchCompare> get copyWith => _$FetchCompareCopyWithImpl<FetchCompare>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchCompare&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'CompareEvent.fetchCompare(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchCompareCopyWith<$Res> implements $CompareEventCopyWith<$Res> {
  factory $FetchCompareCopyWith(FetchCompare value, $Res Function(FetchCompare) _then) = _$FetchCompareCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchCompareCopyWithImpl<$Res>
    implements $FetchCompareCopyWith<$Res> {
  _$FetchCompareCopyWithImpl(this._self, this._then);

  final FetchCompare _self;
  final $Res Function(FetchCompare) _then;

/// Create a copy of CompareEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchCompare(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class SetExtraGroup implements CompareEvent {
  const SetExtraGroup({required final  List<ProductData> products}): _products = products;
  

 final  List<ProductData> _products;
 List<ProductData> get products {
  if (_products is EqualUnmodifiableListView) return _products;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_products);
}


/// Create a copy of CompareEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetExtraGroupCopyWith<SetExtraGroup> get copyWith => _$SetExtraGroupCopyWithImpl<SetExtraGroup>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetExtraGroup&&const DeepCollectionEquality().equals(other._products, _products));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_products));

@override
String toString() {
  return 'CompareEvent.setExtraGroup(products: $products)';
}


}

/// @nodoc
abstract mixin class $SetExtraGroupCopyWith<$Res> implements $CompareEventCopyWith<$Res> {
  factory $SetExtraGroupCopyWith(SetExtraGroup value, $Res Function(SetExtraGroup) _then) = _$SetExtraGroupCopyWithImpl;
@useResult
$Res call({
 List<ProductData> products
});




}
/// @nodoc
class _$SetExtraGroupCopyWithImpl<$Res>
    implements $SetExtraGroupCopyWith<$Res> {
  _$SetExtraGroupCopyWithImpl(this._self, this._then);

  final SetExtraGroup _self;
  final $Res Function(SetExtraGroup) _then;

/// Create a copy of CompareEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? products = null,}) {
  return _then(SetExtraGroup(
products: null == products ? _self._products : products // ignore: cast_nullable_to_non_nullable
as List<ProductData>,
  ));
}


}

/// @nodoc
mixin _$CompareState {

 bool get isLoading; List<List<ProductData>> get compare; List<GroupProperties> get extraGroup; List<GroupProperties> get propertyGroup;
/// Create a copy of CompareState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CompareStateCopyWith<CompareState> get copyWith => _$CompareStateCopyWithImpl<CompareState>(this as CompareState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CompareState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&const DeepCollectionEquality().equals(other.compare, compare)&&const DeepCollectionEquality().equals(other.extraGroup, extraGroup)&&const DeepCollectionEquality().equals(other.propertyGroup, propertyGroup));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,const DeepCollectionEquality().hash(compare),const DeepCollectionEquality().hash(extraGroup),const DeepCollectionEquality().hash(propertyGroup));

@override
String toString() {
  return 'CompareState(isLoading: $isLoading, compare: $compare, extraGroup: $extraGroup, propertyGroup: $propertyGroup)';
}


}

/// @nodoc
abstract mixin class $CompareStateCopyWith<$Res>  {
  factory $CompareStateCopyWith(CompareState value, $Res Function(CompareState) _then) = _$CompareStateCopyWithImpl;
@useResult
$Res call({
 bool isLoading, List<List<ProductData>> compare, List<GroupProperties> extraGroup, List<GroupProperties> propertyGroup
});




}
/// @nodoc
class _$CompareStateCopyWithImpl<$Res>
    implements $CompareStateCopyWith<$Res> {
  _$CompareStateCopyWithImpl(this._self, this._then);

  final CompareState _self;
  final $Res Function(CompareState) _then;

/// Create a copy of CompareState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoading = null,Object? compare = null,Object? extraGroup = null,Object? propertyGroup = null,}) {
  return _then(_self.copyWith(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,compare: null == compare ? _self.compare : compare // ignore: cast_nullable_to_non_nullable
as List<List<ProductData>>,extraGroup: null == extraGroup ? _self.extraGroup : extraGroup // ignore: cast_nullable_to_non_nullable
as List<GroupProperties>,propertyGroup: null == propertyGroup ? _self.propertyGroup : propertyGroup // ignore: cast_nullable_to_non_nullable
as List<GroupProperties>,
  ));
}

}


/// Adds pattern-matching-related methods to [CompareState].
extension CompareStatePatterns on CompareState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CompareState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CompareState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CompareState value)  $default,){
final _that = this;
switch (_that) {
case _CompareState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CompareState value)?  $default,){
final _that = this;
switch (_that) {
case _CompareState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoading,  List<List<ProductData>> compare,  List<GroupProperties> extraGroup,  List<GroupProperties> propertyGroup)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CompareState() when $default != null:
return $default(_that.isLoading,_that.compare,_that.extraGroup,_that.propertyGroup);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoading,  List<List<ProductData>> compare,  List<GroupProperties> extraGroup,  List<GroupProperties> propertyGroup)  $default,) {final _that = this;
switch (_that) {
case _CompareState():
return $default(_that.isLoading,_that.compare,_that.extraGroup,_that.propertyGroup);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoading,  List<List<ProductData>> compare,  List<GroupProperties> extraGroup,  List<GroupProperties> propertyGroup)?  $default,) {final _that = this;
switch (_that) {
case _CompareState() when $default != null:
return $default(_that.isLoading,_that.compare,_that.extraGroup,_that.propertyGroup);case _:
  return null;

}
}

}

/// @nodoc


class _CompareState implements CompareState {
  const _CompareState({this.isLoading = false, final  List<List<ProductData>> compare = const [], final  List<GroupProperties> extraGroup = const [], final  List<GroupProperties> propertyGroup = const []}): _compare = compare,_extraGroup = extraGroup,_propertyGroup = propertyGroup;
  

@override@JsonKey() final  bool isLoading;
 final  List<List<ProductData>> _compare;
@override@JsonKey() List<List<ProductData>> get compare {
  if (_compare is EqualUnmodifiableListView) return _compare;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_compare);
}

 final  List<GroupProperties> _extraGroup;
@override@JsonKey() List<GroupProperties> get extraGroup {
  if (_extraGroup is EqualUnmodifiableListView) return _extraGroup;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_extraGroup);
}

 final  List<GroupProperties> _propertyGroup;
@override@JsonKey() List<GroupProperties> get propertyGroup {
  if (_propertyGroup is EqualUnmodifiableListView) return _propertyGroup;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_propertyGroup);
}


/// Create a copy of CompareState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CompareStateCopyWith<_CompareState> get copyWith => __$CompareStateCopyWithImpl<_CompareState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _CompareState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&const DeepCollectionEquality().equals(other._compare, _compare)&&const DeepCollectionEquality().equals(other._extraGroup, _extraGroup)&&const DeepCollectionEquality().equals(other._propertyGroup, _propertyGroup));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,const DeepCollectionEquality().hash(_compare),const DeepCollectionEquality().hash(_extraGroup),const DeepCollectionEquality().hash(_propertyGroup));

@override
String toString() {
  return 'CompareState(isLoading: $isLoading, compare: $compare, extraGroup: $extraGroup, propertyGroup: $propertyGroup)';
}


}

/// @nodoc
abstract mixin class _$CompareStateCopyWith<$Res> implements $CompareStateCopyWith<$Res> {
  factory _$CompareStateCopyWith(_CompareState value, $Res Function(_CompareState) _then) = __$CompareStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoading, List<List<ProductData>> compare, List<GroupProperties> extraGroup, List<GroupProperties> propertyGroup
});




}
/// @nodoc
class __$CompareStateCopyWithImpl<$Res>
    implements _$CompareStateCopyWith<$Res> {
  __$CompareStateCopyWithImpl(this._self, this._then);

  final _CompareState _self;
  final $Res Function(_CompareState) _then;

/// Create a copy of CompareState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoading = null,Object? compare = null,Object? extraGroup = null,Object? propertyGroup = null,}) {
  return _then(_CompareState(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,compare: null == compare ? _self._compare : compare // ignore: cast_nullable_to_non_nullable
as List<List<ProductData>>,extraGroup: null == extraGroup ? _self._extraGroup : extraGroup // ignore: cast_nullable_to_non_nullable
as List<GroupProperties>,propertyGroup: null == propertyGroup ? _self._propertyGroup : propertyGroup // ignore: cast_nullable_to_non_nullable
as List<GroupProperties>,
  ));
}


}

// dart format on
