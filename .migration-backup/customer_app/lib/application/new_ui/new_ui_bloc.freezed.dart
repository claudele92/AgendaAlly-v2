// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'new_ui_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$NewUiEvent {

 double get top; double get left;
/// Create a copy of NewUiEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NewUiEventCopyWith<NewUiEvent> get copyWith => _$NewUiEventCopyWithImpl<NewUiEvent>(this as NewUiEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NewUiEvent&&(identical(other.top, top) || other.top == top)&&(identical(other.left, left) || other.left == left));
}


@override
int get hashCode => Object.hash(runtimeType,top,left);

@override
String toString() {
  return 'NewUiEvent(top: $top, left: $left)';
}


}

/// @nodoc
abstract mixin class $NewUiEventCopyWith<$Res>  {
  factory $NewUiEventCopyWith(NewUiEvent value, $Res Function(NewUiEvent) _then) = _$NewUiEventCopyWithImpl;
@useResult
$Res call({
 double top, double left
});




}
/// @nodoc
class _$NewUiEventCopyWithImpl<$Res>
    implements $NewUiEventCopyWith<$Res> {
  _$NewUiEventCopyWithImpl(this._self, this._then);

  final NewUiEvent _self;
  final $Res Function(NewUiEvent) _then;

/// Create a copy of NewUiEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? top = null,Object? left = null,}) {
  return _then(_self.copyWith(
top: null == top ? _self.top : top // ignore: cast_nullable_to_non_nullable
as double,left: null == left ? _self.left : left // ignore: cast_nullable_to_non_nullable
as double,
  ));
}

}


/// Adds pattern-matching-related methods to [NewUiEvent].
extension NewUiEventPatterns on NewUiEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( UpdateCart value)?  updateCart,required TResult orElse(),}){
final _that = this;
switch (_that) {
case UpdateCart() when updateCart != null:
return updateCart(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( UpdateCart value)  updateCart,}){
final _that = this;
switch (_that) {
case UpdateCart():
return updateCart(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( UpdateCart value)?  updateCart,}){
final _that = this;
switch (_that) {
case UpdateCart() when updateCart != null:
return updateCart(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( double top,  double left)?  updateCart,required TResult orElse(),}) {final _that = this;
switch (_that) {
case UpdateCart() when updateCart != null:
return updateCart(_that.top,_that.left);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( double top,  double left)  updateCart,}) {final _that = this;
switch (_that) {
case UpdateCart():
return updateCart(_that.top,_that.left);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( double top,  double left)?  updateCart,}) {final _that = this;
switch (_that) {
case UpdateCart() when updateCart != null:
return updateCart(_that.top,_that.left);case _:
  return null;

}
}

}

/// @nodoc


class UpdateCart implements NewUiEvent {
  const UpdateCart({required this.top, required this.left});
  

@override final  double top;
@override final  double left;

/// Create a copy of NewUiEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UpdateCartCopyWith<UpdateCart> get copyWith => _$UpdateCartCopyWithImpl<UpdateCart>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateCart&&(identical(other.top, top) || other.top == top)&&(identical(other.left, left) || other.left == left));
}


@override
int get hashCode => Object.hash(runtimeType,top,left);

@override
String toString() {
  return 'NewUiEvent.updateCart(top: $top, left: $left)';
}


}

/// @nodoc
abstract mixin class $UpdateCartCopyWith<$Res> implements $NewUiEventCopyWith<$Res> {
  factory $UpdateCartCopyWith(UpdateCart value, $Res Function(UpdateCart) _then) = _$UpdateCartCopyWithImpl;
@override @useResult
$Res call({
 double top, double left
});




}
/// @nodoc
class _$UpdateCartCopyWithImpl<$Res>
    implements $UpdateCartCopyWith<$Res> {
  _$UpdateCartCopyWithImpl(this._self, this._then);

  final UpdateCart _self;
  final $Res Function(UpdateCart) _then;

/// Create a copy of NewUiEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? top = null,Object? left = null,}) {
  return _then(UpdateCart(
top: null == top ? _self.top : top // ignore: cast_nullable_to_non_nullable
as double,left: null == left ? _self.left : left // ignore: cast_nullable_to_non_nullable
as double,
  ));
}


}

/// @nodoc
mixin _$NewUiState {

 double get top; double get left;
/// Create a copy of NewUiState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NewUiStateCopyWith<NewUiState> get copyWith => _$NewUiStateCopyWithImpl<NewUiState>(this as NewUiState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NewUiState&&(identical(other.top, top) || other.top == top)&&(identical(other.left, left) || other.left == left));
}


@override
int get hashCode => Object.hash(runtimeType,top,left);

@override
String toString() {
  return 'NewUiState(top: $top, left: $left)';
}


}

/// @nodoc
abstract mixin class $NewUiStateCopyWith<$Res>  {
  factory $NewUiStateCopyWith(NewUiState value, $Res Function(NewUiState) _then) = _$NewUiStateCopyWithImpl;
@useResult
$Res call({
 double top, double left
});




}
/// @nodoc
class _$NewUiStateCopyWithImpl<$Res>
    implements $NewUiStateCopyWith<$Res> {
  _$NewUiStateCopyWithImpl(this._self, this._then);

  final NewUiState _self;
  final $Res Function(NewUiState) _then;

/// Create a copy of NewUiState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? top = null,Object? left = null,}) {
  return _then(_self.copyWith(
top: null == top ? _self.top : top // ignore: cast_nullable_to_non_nullable
as double,left: null == left ? _self.left : left // ignore: cast_nullable_to_non_nullable
as double,
  ));
}

}


/// Adds pattern-matching-related methods to [NewUiState].
extension NewUiStatePatterns on NewUiState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _NewUiState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _NewUiState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _NewUiState value)  $default,){
final _that = this;
switch (_that) {
case _NewUiState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _NewUiState value)?  $default,){
final _that = this;
switch (_that) {
case _NewUiState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( double top,  double left)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _NewUiState() when $default != null:
return $default(_that.top,_that.left);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( double top,  double left)  $default,) {final _that = this;
switch (_that) {
case _NewUiState():
return $default(_that.top,_that.left);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( double top,  double left)?  $default,) {final _that = this;
switch (_that) {
case _NewUiState() when $default != null:
return $default(_that.top,_that.left);case _:
  return null;

}
}

}

/// @nodoc


class _NewUiState implements NewUiState {
  const _NewUiState({this.top = 50, this.left = -200});
  

@override@JsonKey() final  double top;
@override@JsonKey() final  double left;

/// Create a copy of NewUiState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$NewUiStateCopyWith<_NewUiState> get copyWith => __$NewUiStateCopyWithImpl<_NewUiState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _NewUiState&&(identical(other.top, top) || other.top == top)&&(identical(other.left, left) || other.left == left));
}


@override
int get hashCode => Object.hash(runtimeType,top,left);

@override
String toString() {
  return 'NewUiState(top: $top, left: $left)';
}


}

/// @nodoc
abstract mixin class _$NewUiStateCopyWith<$Res> implements $NewUiStateCopyWith<$Res> {
  factory _$NewUiStateCopyWith(_NewUiState value, $Res Function(_NewUiState) _then) = __$NewUiStateCopyWithImpl;
@override @useResult
$Res call({
 double top, double left
});




}
/// @nodoc
class __$NewUiStateCopyWithImpl<$Res>
    implements _$NewUiStateCopyWith<$Res> {
  __$NewUiStateCopyWithImpl(this._self, this._then);

  final _NewUiState _self;
  final $Res Function(_NewUiState) _then;

/// Create a copy of NewUiState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? top = null,Object? left = null,}) {
  return _then(_NewUiState(
top: null == top ? _self.top : top // ignore: cast_nullable_to_non_nullable
as double,left: null == left ? _self.left : left // ignore: cast_nullable_to_non_nullable
as double,
  ));
}


}

// dart format on
