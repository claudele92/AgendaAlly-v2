// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'main_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$MainEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MainEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'MainEvent()';
}


}

/// @nodoc
class $MainEventCopyWith<$Res>  {
$MainEventCopyWith(MainEvent _, $Res Function(MainEvent) __);
}


/// Adds pattern-matching-related methods to [MainEvent].
extension MainEventPatterns on MainEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( ChangeIndex value)?  changeIndex,TResult Function( ChangeForYou value)?  changeForYou,TResult Function( ShowSearch value)?  showSearch,required TResult orElse(),}){
final _that = this;
switch (_that) {
case ChangeIndex() when changeIndex != null:
return changeIndex(_that);case ChangeForYou() when changeForYou != null:
return changeForYou(_that);case ShowSearch() when showSearch != null:
return showSearch(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( ChangeIndex value)  changeIndex,required TResult Function( ChangeForYou value)  changeForYou,required TResult Function( ShowSearch value)  showSearch,}){
final _that = this;
switch (_that) {
case ChangeIndex():
return changeIndex(_that);case ChangeForYou():
return changeForYou(_that);case ShowSearch():
return showSearch(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( ChangeIndex value)?  changeIndex,TResult? Function( ChangeForYou value)?  changeForYou,TResult? Function( ShowSearch value)?  showSearch,}){
final _that = this;
switch (_that) {
case ChangeIndex() when changeIndex != null:
return changeIndex(_that);case ChangeForYou() when changeForYou != null:
return changeForYou(_that);case ShowSearch() when showSearch != null:
return showSearch(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( int index)?  changeIndex,TResult Function( bool value)?  changeForYou,TResult Function( bool value)?  showSearch,required TResult orElse(),}) {final _that = this;
switch (_that) {
case ChangeIndex() when changeIndex != null:
return changeIndex(_that.index);case ChangeForYou() when changeForYou != null:
return changeForYou(_that.value);case ShowSearch() when showSearch != null:
return showSearch(_that.value);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( int index)  changeIndex,required TResult Function( bool value)  changeForYou,required TResult Function( bool value)  showSearch,}) {final _that = this;
switch (_that) {
case ChangeIndex():
return changeIndex(_that.index);case ChangeForYou():
return changeForYou(_that.value);case ShowSearch():
return showSearch(_that.value);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( int index)?  changeIndex,TResult? Function( bool value)?  changeForYou,TResult? Function( bool value)?  showSearch,}) {final _that = this;
switch (_that) {
case ChangeIndex() when changeIndex != null:
return changeIndex(_that.index);case ChangeForYou() when changeForYou != null:
return changeForYou(_that.value);case ShowSearch() when showSearch != null:
return showSearch(_that.value);case _:
  return null;

}
}

}

/// @nodoc


class ChangeIndex implements MainEvent {
  const ChangeIndex({required this.index});
  

 final  int index;

/// Create a copy of MainEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChangeIndexCopyWith<ChangeIndex> get copyWith => _$ChangeIndexCopyWithImpl<ChangeIndex>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChangeIndex&&(identical(other.index, index) || other.index == index));
}


@override
int get hashCode => Object.hash(runtimeType,index);

@override
String toString() {
  return 'MainEvent.changeIndex(index: $index)';
}


}

/// @nodoc
abstract mixin class $ChangeIndexCopyWith<$Res> implements $MainEventCopyWith<$Res> {
  factory $ChangeIndexCopyWith(ChangeIndex value, $Res Function(ChangeIndex) _then) = _$ChangeIndexCopyWithImpl;
@useResult
$Res call({
 int index
});




}
/// @nodoc
class _$ChangeIndexCopyWithImpl<$Res>
    implements $ChangeIndexCopyWith<$Res> {
  _$ChangeIndexCopyWithImpl(this._self, this._then);

  final ChangeIndex _self;
  final $Res Function(ChangeIndex) _then;

/// Create a copy of MainEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? index = null,}) {
  return _then(ChangeIndex(
index: null == index ? _self.index : index // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class ChangeForYou implements MainEvent {
  const ChangeForYou({required this.value});
  

 final  bool value;

/// Create a copy of MainEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChangeForYouCopyWith<ChangeForYou> get copyWith => _$ChangeForYouCopyWithImpl<ChangeForYou>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChangeForYou&&(identical(other.value, value) || other.value == value));
}


@override
int get hashCode => Object.hash(runtimeType,value);

@override
String toString() {
  return 'MainEvent.changeForYou(value: $value)';
}


}

/// @nodoc
abstract mixin class $ChangeForYouCopyWith<$Res> implements $MainEventCopyWith<$Res> {
  factory $ChangeForYouCopyWith(ChangeForYou value, $Res Function(ChangeForYou) _then) = _$ChangeForYouCopyWithImpl;
@useResult
$Res call({
 bool value
});




}
/// @nodoc
class _$ChangeForYouCopyWithImpl<$Res>
    implements $ChangeForYouCopyWith<$Res> {
  _$ChangeForYouCopyWithImpl(this._self, this._then);

  final ChangeForYou _self;
  final $Res Function(ChangeForYou) _then;

/// Create a copy of MainEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? value = null,}) {
  return _then(ChangeForYou(
value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc


class ShowSearch implements MainEvent {
  const ShowSearch({required this.value});
  

 final  bool value;

/// Create a copy of MainEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ShowSearchCopyWith<ShowSearch> get copyWith => _$ShowSearchCopyWithImpl<ShowSearch>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ShowSearch&&(identical(other.value, value) || other.value == value));
}


@override
int get hashCode => Object.hash(runtimeType,value);

@override
String toString() {
  return 'MainEvent.showSearch(value: $value)';
}


}

/// @nodoc
abstract mixin class $ShowSearchCopyWith<$Res> implements $MainEventCopyWith<$Res> {
  factory $ShowSearchCopyWith(ShowSearch value, $Res Function(ShowSearch) _then) = _$ShowSearchCopyWithImpl;
@useResult
$Res call({
 bool value
});




}
/// @nodoc
class _$ShowSearchCopyWithImpl<$Res>
    implements $ShowSearchCopyWith<$Res> {
  _$ShowSearchCopyWithImpl(this._self, this._then);

  final ShowSearch _self;
  final $Res Function(ShowSearch) _then;

/// Create a copy of MainEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? value = null,}) {
  return _then(ShowSearch(
value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$MainState {

 int get selectIndex; bool get selectForYou; bool get isShowSearch;
/// Create a copy of MainState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MainStateCopyWith<MainState> get copyWith => _$MainStateCopyWithImpl<MainState>(this as MainState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MainState&&(identical(other.selectIndex, selectIndex) || other.selectIndex == selectIndex)&&(identical(other.selectForYou, selectForYou) || other.selectForYou == selectForYou)&&(identical(other.isShowSearch, isShowSearch) || other.isShowSearch == isShowSearch));
}


@override
int get hashCode => Object.hash(runtimeType,selectIndex,selectForYou,isShowSearch);

@override
String toString() {
  return 'MainState(selectIndex: $selectIndex, selectForYou: $selectForYou, isShowSearch: $isShowSearch)';
}


}

/// @nodoc
abstract mixin class $MainStateCopyWith<$Res>  {
  factory $MainStateCopyWith(MainState value, $Res Function(MainState) _then) = _$MainStateCopyWithImpl;
@useResult
$Res call({
 int selectIndex, bool selectForYou, bool isShowSearch
});




}
/// @nodoc
class _$MainStateCopyWithImpl<$Res>
    implements $MainStateCopyWith<$Res> {
  _$MainStateCopyWithImpl(this._self, this._then);

  final MainState _self;
  final $Res Function(MainState) _then;

/// Create a copy of MainState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? selectIndex = null,Object? selectForYou = null,Object? isShowSearch = null,}) {
  return _then(_self.copyWith(
selectIndex: null == selectIndex ? _self.selectIndex : selectIndex // ignore: cast_nullable_to_non_nullable
as int,selectForYou: null == selectForYou ? _self.selectForYou : selectForYou // ignore: cast_nullable_to_non_nullable
as bool,isShowSearch: null == isShowSearch ? _self.isShowSearch : isShowSearch // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [MainState].
extension MainStatePatterns on MainState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MainState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MainState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MainState value)  $default,){
final _that = this;
switch (_that) {
case _MainState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MainState value)?  $default,){
final _that = this;
switch (_that) {
case _MainState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int selectIndex,  bool selectForYou,  bool isShowSearch)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MainState() when $default != null:
return $default(_that.selectIndex,_that.selectForYou,_that.isShowSearch);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int selectIndex,  bool selectForYou,  bool isShowSearch)  $default,) {final _that = this;
switch (_that) {
case _MainState():
return $default(_that.selectIndex,_that.selectForYou,_that.isShowSearch);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int selectIndex,  bool selectForYou,  bool isShowSearch)?  $default,) {final _that = this;
switch (_that) {
case _MainState() when $default != null:
return $default(_that.selectIndex,_that.selectForYou,_that.isShowSearch);case _:
  return null;

}
}

}

/// @nodoc


class _MainState implements MainState {
  const _MainState({this.selectIndex = 0, this.selectForYou = true, this.isShowSearch = true});
  

@override@JsonKey() final  int selectIndex;
@override@JsonKey() final  bool selectForYou;
@override@JsonKey() final  bool isShowSearch;

/// Create a copy of MainState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MainStateCopyWith<_MainState> get copyWith => __$MainStateCopyWithImpl<_MainState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _MainState&&(identical(other.selectIndex, selectIndex) || other.selectIndex == selectIndex)&&(identical(other.selectForYou, selectForYou) || other.selectForYou == selectForYou)&&(identical(other.isShowSearch, isShowSearch) || other.isShowSearch == isShowSearch));
}


@override
int get hashCode => Object.hash(runtimeType,selectIndex,selectForYou,isShowSearch);

@override
String toString() {
  return 'MainState(selectIndex: $selectIndex, selectForYou: $selectForYou, isShowSearch: $isShowSearch)';
}


}

/// @nodoc
abstract mixin class _$MainStateCopyWith<$Res> implements $MainStateCopyWith<$Res> {
  factory _$MainStateCopyWith(_MainState value, $Res Function(_MainState) _then) = __$MainStateCopyWithImpl;
@override @useResult
$Res call({
 int selectIndex, bool selectForYou, bool isShowSearch
});




}
/// @nodoc
class __$MainStateCopyWithImpl<$Res>
    implements _$MainStateCopyWith<$Res> {
  __$MainStateCopyWithImpl(this._self, this._then);

  final _MainState _self;
  final $Res Function(_MainState) _then;

/// Create a copy of MainState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? selectIndex = null,Object? selectForYou = null,Object? isShowSearch = null,}) {
  return _then(_MainState(
selectIndex: null == selectIndex ? _self.selectIndex : selectIndex // ignore: cast_nullable_to_non_nullable
as int,selectForYou: null == selectForYou ? _self.selectForYou : selectForYou // ignore: cast_nullable_to_non_nullable
as bool,isShowSearch: null == isShowSearch ? _self.isShowSearch : isShowSearch // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
