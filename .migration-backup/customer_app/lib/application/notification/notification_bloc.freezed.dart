// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'notification_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$NotificationEvent {

 BuildContext get context;
/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NotificationEventCopyWith<NotificationEvent> get copyWith => _$NotificationEventCopyWithImpl<NotificationEvent>(this as NotificationEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NotificationEvent&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'NotificationEvent(context: $context)';
}


}

/// @nodoc
abstract mixin class $NotificationEventCopyWith<$Res>  {
  factory $NotificationEventCopyWith(NotificationEvent value, $Res Function(NotificationEvent) _then) = _$NotificationEventCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$NotificationEventCopyWithImpl<$Res>
    implements $NotificationEventCopyWith<$Res> {
  _$NotificationEventCopyWithImpl(this._self, this._then);

  final NotificationEvent _self;
  final $Res Function(NotificationEvent) _then;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? context = null,}) {
  return _then(_self.copyWith(
context: null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}

}


/// Adds pattern-matching-related methods to [NotificationEvent].
extension NotificationEventPatterns on NotificationEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchNotification value)?  fetchNotification,TResult Function( ReadAll value)?  readAll,TResult Function( ReadOne value)?  readOne,TResult Function( RemoveItem value)?  removeItem,TResult Function( FetchCount value)?  fetchCount,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchNotification() when fetchNotification != null:
return fetchNotification(_that);case ReadAll() when readAll != null:
return readAll(_that);case ReadOne() when readOne != null:
return readOne(_that);case RemoveItem() when removeItem != null:
return removeItem(_that);case FetchCount() when fetchCount != null:
return fetchCount(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchNotification value)  fetchNotification,required TResult Function( ReadAll value)  readAll,required TResult Function( ReadOne value)  readOne,required TResult Function( RemoveItem value)  removeItem,required TResult Function( FetchCount value)  fetchCount,}){
final _that = this;
switch (_that) {
case FetchNotification():
return fetchNotification(_that);case ReadAll():
return readAll(_that);case ReadOne():
return readOne(_that);case RemoveItem():
return removeItem(_that);case FetchCount():
return fetchCount(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchNotification value)?  fetchNotification,TResult? Function( ReadAll value)?  readAll,TResult? Function( ReadOne value)?  readOne,TResult? Function( RemoveItem value)?  removeItem,TResult? Function( FetchCount value)?  fetchCount,}){
final _that = this;
switch (_that) {
case FetchNotification() when fetchNotification != null:
return fetchNotification(_that);case ReadAll() when readAll != null:
return readAll(_that);case ReadOne() when readOne != null:
return readOne(_that);case RemoveItem() when removeItem != null:
return removeItem(_that);case FetchCount() when fetchCount != null:
return fetchCount(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchNotification,TResult Function( BuildContext context)?  readAll,TResult Function( BuildContext context,  int? id)?  readOne,TResult Function( BuildContext context,  int? id)?  removeItem,TResult Function( BuildContext context)?  fetchCount,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchNotification() when fetchNotification != null:
return fetchNotification(_that.context,_that.isRefresh,_that.controller);case ReadAll() when readAll != null:
return readAll(_that.context);case ReadOne() when readOne != null:
return readOne(_that.context,_that.id);case RemoveItem() when removeItem != null:
return removeItem(_that.context,_that.id);case FetchCount() when fetchCount != null:
return fetchCount(_that.context);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchNotification,required TResult Function( BuildContext context)  readAll,required TResult Function( BuildContext context,  int? id)  readOne,required TResult Function( BuildContext context,  int? id)  removeItem,required TResult Function( BuildContext context)  fetchCount,}) {final _that = this;
switch (_that) {
case FetchNotification():
return fetchNotification(_that.context,_that.isRefresh,_that.controller);case ReadAll():
return readAll(_that.context);case ReadOne():
return readOne(_that.context,_that.id);case RemoveItem():
return removeItem(_that.context,_that.id);case FetchCount():
return fetchCount(_that.context);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchNotification,TResult? Function( BuildContext context)?  readAll,TResult? Function( BuildContext context,  int? id)?  readOne,TResult? Function( BuildContext context,  int? id)?  removeItem,TResult? Function( BuildContext context)?  fetchCount,}) {final _that = this;
switch (_that) {
case FetchNotification() when fetchNotification != null:
return fetchNotification(_that.context,_that.isRefresh,_that.controller);case ReadAll() when readAll != null:
return readAll(_that.context);case ReadOne() when readOne != null:
return readOne(_that.context,_that.id);case RemoveItem() when removeItem != null:
return removeItem(_that.context,_that.id);case FetchCount() when fetchCount != null:
return fetchCount(_that.context);case _:
  return null;

}
}

}

/// @nodoc


class FetchNotification implements NotificationEvent {
  const FetchNotification(this.context, {this.isRefresh, this.controller});
  

@override final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchNotificationCopyWith<FetchNotification> get copyWith => _$FetchNotificationCopyWithImpl<FetchNotification>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchNotification&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'NotificationEvent.fetchNotification(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchNotificationCopyWith<$Res> implements $NotificationEventCopyWith<$Res> {
  factory $FetchNotificationCopyWith(FetchNotification value, $Res Function(FetchNotification) _then) = _$FetchNotificationCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchNotificationCopyWithImpl<$Res>
    implements $FetchNotificationCopyWith<$Res> {
  _$FetchNotificationCopyWithImpl(this._self, this._then);

  final FetchNotification _self;
  final $Res Function(FetchNotification) _then;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchNotification(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class ReadAll implements NotificationEvent {
  const ReadAll(this.context);
  

@override final  BuildContext context;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ReadAllCopyWith<ReadAll> get copyWith => _$ReadAllCopyWithImpl<ReadAll>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ReadAll&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'NotificationEvent.readAll(context: $context)';
}


}

/// @nodoc
abstract mixin class $ReadAllCopyWith<$Res> implements $NotificationEventCopyWith<$Res> {
  factory $ReadAllCopyWith(ReadAll value, $Res Function(ReadAll) _then) = _$ReadAllCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$ReadAllCopyWithImpl<$Res>
    implements $ReadAllCopyWith<$Res> {
  _$ReadAllCopyWithImpl(this._self, this._then);

  final ReadAll _self;
  final $Res Function(ReadAll) _then;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(ReadAll(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class ReadOne implements NotificationEvent {
  const ReadOne(this.context, {required this.id});
  

@override final  BuildContext context;
 final  int? id;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ReadOneCopyWith<ReadOne> get copyWith => _$ReadOneCopyWithImpl<ReadOne>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ReadOne&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,context,id);

@override
String toString() {
  return 'NotificationEvent.readOne(context: $context, id: $id)';
}


}

/// @nodoc
abstract mixin class $ReadOneCopyWith<$Res> implements $NotificationEventCopyWith<$Res> {
  factory $ReadOneCopyWith(ReadOne value, $Res Function(ReadOne) _then) = _$ReadOneCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int? id
});




}
/// @nodoc
class _$ReadOneCopyWithImpl<$Res>
    implements $ReadOneCopyWith<$Res> {
  _$ReadOneCopyWithImpl(this._self, this._then);

  final ReadOne _self;
  final $Res Function(ReadOne) _then;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = freezed,}) {
  return _then(ReadOne(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: freezed == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class RemoveItem implements NotificationEvent {
  const RemoveItem(this.context, {required this.id});
  

@override final  BuildContext context;
 final  int? id;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RemoveItemCopyWith<RemoveItem> get copyWith => _$RemoveItemCopyWithImpl<RemoveItem>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RemoveItem&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,context,id);

@override
String toString() {
  return 'NotificationEvent.removeItem(context: $context, id: $id)';
}


}

/// @nodoc
abstract mixin class $RemoveItemCopyWith<$Res> implements $NotificationEventCopyWith<$Res> {
  factory $RemoveItemCopyWith(RemoveItem value, $Res Function(RemoveItem) _then) = _$RemoveItemCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int? id
});




}
/// @nodoc
class _$RemoveItemCopyWithImpl<$Res>
    implements $RemoveItemCopyWith<$Res> {
  _$RemoveItemCopyWithImpl(this._self, this._then);

  final RemoveItem _self;
  final $Res Function(RemoveItem) _then;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = freezed,}) {
  return _then(RemoveItem(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: freezed == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int?,
  ));
}


}

/// @nodoc


class FetchCount implements NotificationEvent {
  const FetchCount(this.context);
  

@override final  BuildContext context;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchCountCopyWith<FetchCount> get copyWith => _$FetchCountCopyWithImpl<FetchCount>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchCount&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'NotificationEvent.fetchCount(context: $context)';
}


}

/// @nodoc
abstract mixin class $FetchCountCopyWith<$Res> implements $NotificationEventCopyWith<$Res> {
  factory $FetchCountCopyWith(FetchCount value, $Res Function(FetchCount) _then) = _$FetchCountCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$FetchCountCopyWithImpl<$Res>
    implements $FetchCountCopyWith<$Res> {
  _$FetchCountCopyWithImpl(this._self, this._then);

  final FetchCount _self;
  final $Res Function(FetchCount) _then;

/// Create a copy of NotificationEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(FetchCount(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc
mixin _$NotificationState {

 bool get isLoading; List<NotificationModel> get notifications; CountNotificationModel? get countOfNotifications; bool get isReadAllLoading;
/// Create a copy of NotificationState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NotificationStateCopyWith<NotificationState> get copyWith => _$NotificationStateCopyWithImpl<NotificationState>(this as NotificationState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NotificationState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&const DeepCollectionEquality().equals(other.notifications, notifications)&&(identical(other.countOfNotifications, countOfNotifications) || other.countOfNotifications == countOfNotifications)&&(identical(other.isReadAllLoading, isReadAllLoading) || other.isReadAllLoading == isReadAllLoading));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,const DeepCollectionEquality().hash(notifications),countOfNotifications,isReadAllLoading);

@override
String toString() {
  return 'NotificationState(isLoading: $isLoading, notifications: $notifications, countOfNotifications: $countOfNotifications, isReadAllLoading: $isReadAllLoading)';
}


}

/// @nodoc
abstract mixin class $NotificationStateCopyWith<$Res>  {
  factory $NotificationStateCopyWith(NotificationState value, $Res Function(NotificationState) _then) = _$NotificationStateCopyWithImpl;
@useResult
$Res call({
 bool isLoading, List<NotificationModel> notifications, CountNotificationModel? countOfNotifications, bool isReadAllLoading
});




}
/// @nodoc
class _$NotificationStateCopyWithImpl<$Res>
    implements $NotificationStateCopyWith<$Res> {
  _$NotificationStateCopyWithImpl(this._self, this._then);

  final NotificationState _self;
  final $Res Function(NotificationState) _then;

/// Create a copy of NotificationState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoading = null,Object? notifications = null,Object? countOfNotifications = freezed,Object? isReadAllLoading = null,}) {
  return _then(_self.copyWith(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,notifications: null == notifications ? _self.notifications : notifications // ignore: cast_nullable_to_non_nullable
as List<NotificationModel>,countOfNotifications: freezed == countOfNotifications ? _self.countOfNotifications : countOfNotifications // ignore: cast_nullable_to_non_nullable
as CountNotificationModel?,isReadAllLoading: null == isReadAllLoading ? _self.isReadAllLoading : isReadAllLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [NotificationState].
extension NotificationStatePatterns on NotificationState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _NotificationState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _NotificationState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _NotificationState value)  $default,){
final _that = this;
switch (_that) {
case _NotificationState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _NotificationState value)?  $default,){
final _that = this;
switch (_that) {
case _NotificationState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoading,  List<NotificationModel> notifications,  CountNotificationModel? countOfNotifications,  bool isReadAllLoading)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _NotificationState() when $default != null:
return $default(_that.isLoading,_that.notifications,_that.countOfNotifications,_that.isReadAllLoading);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoading,  List<NotificationModel> notifications,  CountNotificationModel? countOfNotifications,  bool isReadAllLoading)  $default,) {final _that = this;
switch (_that) {
case _NotificationState():
return $default(_that.isLoading,_that.notifications,_that.countOfNotifications,_that.isReadAllLoading);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoading,  List<NotificationModel> notifications,  CountNotificationModel? countOfNotifications,  bool isReadAllLoading)?  $default,) {final _that = this;
switch (_that) {
case _NotificationState() when $default != null:
return $default(_that.isLoading,_that.notifications,_that.countOfNotifications,_that.isReadAllLoading);case _:
  return null;

}
}

}

/// @nodoc


class _NotificationState implements NotificationState {
  const _NotificationState({this.isLoading = false, final  List<NotificationModel> notifications = const [], this.countOfNotifications = null, this.isReadAllLoading = false}): _notifications = notifications;
  

@override@JsonKey() final  bool isLoading;
 final  List<NotificationModel> _notifications;
@override@JsonKey() List<NotificationModel> get notifications {
  if (_notifications is EqualUnmodifiableListView) return _notifications;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_notifications);
}

@override@JsonKey() final  CountNotificationModel? countOfNotifications;
@override@JsonKey() final  bool isReadAllLoading;

/// Create a copy of NotificationState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$NotificationStateCopyWith<_NotificationState> get copyWith => __$NotificationStateCopyWithImpl<_NotificationState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _NotificationState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&const DeepCollectionEquality().equals(other._notifications, _notifications)&&(identical(other.countOfNotifications, countOfNotifications) || other.countOfNotifications == countOfNotifications)&&(identical(other.isReadAllLoading, isReadAllLoading) || other.isReadAllLoading == isReadAllLoading));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,const DeepCollectionEquality().hash(_notifications),countOfNotifications,isReadAllLoading);

@override
String toString() {
  return 'NotificationState(isLoading: $isLoading, notifications: $notifications, countOfNotifications: $countOfNotifications, isReadAllLoading: $isReadAllLoading)';
}


}

/// @nodoc
abstract mixin class _$NotificationStateCopyWith<$Res> implements $NotificationStateCopyWith<$Res> {
  factory _$NotificationStateCopyWith(_NotificationState value, $Res Function(_NotificationState) _then) = __$NotificationStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoading, List<NotificationModel> notifications, CountNotificationModel? countOfNotifications, bool isReadAllLoading
});




}
/// @nodoc
class __$NotificationStateCopyWithImpl<$Res>
    implements _$NotificationStateCopyWith<$Res> {
  __$NotificationStateCopyWithImpl(this._self, this._then);

  final _NotificationState _self;
  final $Res Function(_NotificationState) _then;

/// Create a copy of NotificationState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoading = null,Object? notifications = null,Object? countOfNotifications = freezed,Object? isReadAllLoading = null,}) {
  return _then(_NotificationState(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,notifications: null == notifications ? _self._notifications : notifications // ignore: cast_nullable_to_non_nullable
as List<NotificationModel>,countOfNotifications: freezed == countOfNotifications ? _self.countOfNotifications : countOfNotifications // ignore: cast_nullable_to_non_nullable
as CountNotificationModel?,isReadAllLoading: null == isReadAllLoading ? _self.isReadAllLoading : isReadAllLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
