// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'master_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$MasterEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MasterEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'MasterEvent()';
}


}

/// @nodoc
class $MasterEventCopyWith<$Res>  {
$MasterEventCopyWith(MasterEvent _, $Res Function(MasterEvent) __);
}


/// Adds pattern-matching-related methods to [MasterEvent].
extension MasterEventPatterns on MasterEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchMasters value)?  fetchMasters,TResult Function( FetchLikeMasters value)?  fetchLikeMasters,TResult Function( FetchMasterById value)?  fetchMasterById,TResult Function( FetchMasterImage value)?  fetchMasterImage,TResult Function( UpdateState value)?  updateState,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchMasters() when fetchMasters != null:
return fetchMasters(_that);case FetchLikeMasters() when fetchLikeMasters != null:
return fetchLikeMasters(_that);case FetchMasterById() when fetchMasterById != null:
return fetchMasterById(_that);case FetchMasterImage() when fetchMasterImage != null:
return fetchMasterImage(_that);case UpdateState() when updateState != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchMasters value)  fetchMasters,required TResult Function( FetchLikeMasters value)  fetchLikeMasters,required TResult Function( FetchMasterById value)  fetchMasterById,required TResult Function( FetchMasterImage value)  fetchMasterImage,required TResult Function( UpdateState value)  updateState,}){
final _that = this;
switch (_that) {
case FetchMasters():
return fetchMasters(_that);case FetchLikeMasters():
return fetchLikeMasters(_that);case FetchMasterById():
return fetchMasterById(_that);case FetchMasterImage():
return fetchMasterImage(_that);case UpdateState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchMasters value)?  fetchMasters,TResult? Function( FetchLikeMasters value)?  fetchLikeMasters,TResult? Function( FetchMasterById value)?  fetchMasterById,TResult? Function( FetchMasterImage value)?  fetchMasterImage,TResult? Function( UpdateState value)?  updateState,}){
final _that = this;
switch (_that) {
case FetchMasters() when fetchMasters != null:
return fetchMasters(_that);case FetchLikeMasters() when fetchLikeMasters != null:
return fetchLikeMasters(_that);case FetchMasterById() when fetchMasterById != null:
return fetchMasterById(_that);case FetchMasterImage() when fetchMasterImage != null:
return fetchMasterImage(_that);case UpdateState() when updateState != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  int? serviceId,  List<int>? serviceIds,  RefreshController? controller)?  fetchMasters,TResult Function( BuildContext context)?  fetchLikeMasters,TResult Function( BuildContext context,  MasterModel master)?  fetchMasterById,TResult Function( BuildContext context,  int id)?  fetchMasterImage,TResult Function()?  updateState,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchMasters() when fetchMasters != null:
return fetchMasters(_that.context,_that.isRefresh,_that.shopId,_that.serviceId,_that.serviceIds,_that.controller);case FetchLikeMasters() when fetchLikeMasters != null:
return fetchLikeMasters(_that.context);case FetchMasterById() when fetchMasterById != null:
return fetchMasterById(_that.context,_that.master);case FetchMasterImage() when fetchMasterImage != null:
return fetchMasterImage(_that.context,_that.id);case UpdateState() when updateState != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  int? shopId,  int? serviceId,  List<int>? serviceIds,  RefreshController? controller)  fetchMasters,required TResult Function( BuildContext context)  fetchLikeMasters,required TResult Function( BuildContext context,  MasterModel master)  fetchMasterById,required TResult Function( BuildContext context,  int id)  fetchMasterImage,required TResult Function()  updateState,}) {final _that = this;
switch (_that) {
case FetchMasters():
return fetchMasters(_that.context,_that.isRefresh,_that.shopId,_that.serviceId,_that.serviceIds,_that.controller);case FetchLikeMasters():
return fetchLikeMasters(_that.context);case FetchMasterById():
return fetchMasterById(_that.context,_that.master);case FetchMasterImage():
return fetchMasterImage(_that.context,_that.id);case UpdateState():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  int? shopId,  int? serviceId,  List<int>? serviceIds,  RefreshController? controller)?  fetchMasters,TResult? Function( BuildContext context)?  fetchLikeMasters,TResult? Function( BuildContext context,  MasterModel master)?  fetchMasterById,TResult? Function( BuildContext context,  int id)?  fetchMasterImage,TResult? Function()?  updateState,}) {final _that = this;
switch (_that) {
case FetchMasters() when fetchMasters != null:
return fetchMasters(_that.context,_that.isRefresh,_that.shopId,_that.serviceId,_that.serviceIds,_that.controller);case FetchLikeMasters() when fetchLikeMasters != null:
return fetchLikeMasters(_that.context);case FetchMasterById() when fetchMasterById != null:
return fetchMasterById(_that.context,_that.master);case FetchMasterImage() when fetchMasterImage != null:
return fetchMasterImage(_that.context,_that.id);case UpdateState() when updateState != null:
return updateState();case _:
  return null;

}
}

}

/// @nodoc


class FetchMasters implements MasterEvent {
  const FetchMasters(this.context, {this.isRefresh, this.shopId, this.serviceId, final  List<int>? serviceIds, this.controller}): _serviceIds = serviceIds;
  

 final  BuildContext context;
 final  bool? isRefresh;
 final  int? shopId;
 final  int? serviceId;
 final  List<int>? _serviceIds;
 List<int>? get serviceIds {
  final value = _serviceIds;
  if (value == null) return null;
  if (_serviceIds is EqualUnmodifiableListView) return _serviceIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}

 final  RefreshController? controller;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMastersCopyWith<FetchMasters> get copyWith => _$FetchMastersCopyWithImpl<FetchMasters>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMasters&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.shopId, shopId) || other.shopId == shopId)&&(identical(other.serviceId, serviceId) || other.serviceId == serviceId)&&const DeepCollectionEquality().equals(other._serviceIds, _serviceIds)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,shopId,serviceId,const DeepCollectionEquality().hash(_serviceIds),controller);

@override
String toString() {
  return 'MasterEvent.fetchMasters(context: $context, isRefresh: $isRefresh, shopId: $shopId, serviceId: $serviceId, serviceIds: $serviceIds, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchMastersCopyWith<$Res> implements $MasterEventCopyWith<$Res> {
  factory $FetchMastersCopyWith(FetchMasters value, $Res Function(FetchMasters) _then) = _$FetchMastersCopyWithImpl;
@useResult
$Res call({
 BuildContext context, bool? isRefresh, int? shopId, int? serviceId, List<int>? serviceIds, RefreshController? controller
});




}
/// @nodoc
class _$FetchMastersCopyWithImpl<$Res>
    implements $FetchMastersCopyWith<$Res> {
  _$FetchMastersCopyWithImpl(this._self, this._then);

  final FetchMasters _self;
  final $Res Function(FetchMasters) _then;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? shopId = freezed,Object? serviceId = freezed,Object? serviceIds = freezed,Object? controller = freezed,}) {
  return _then(FetchMasters(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,shopId: freezed == shopId ? _self.shopId : shopId // ignore: cast_nullable_to_non_nullable
as int?,serviceId: freezed == serviceId ? _self.serviceId : serviceId // ignore: cast_nullable_to_non_nullable
as int?,serviceIds: freezed == serviceIds ? _self._serviceIds : serviceIds // ignore: cast_nullable_to_non_nullable
as List<int>?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchLikeMasters implements MasterEvent {
  const FetchLikeMasters(this.context);
  

 final  BuildContext context;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchLikeMastersCopyWith<FetchLikeMasters> get copyWith => _$FetchLikeMastersCopyWithImpl<FetchLikeMasters>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchLikeMasters&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'MasterEvent.fetchLikeMasters(context: $context)';
}


}

/// @nodoc
abstract mixin class $FetchLikeMastersCopyWith<$Res> implements $MasterEventCopyWith<$Res> {
  factory $FetchLikeMastersCopyWith(FetchLikeMasters value, $Res Function(FetchLikeMasters) _then) = _$FetchLikeMastersCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$FetchLikeMastersCopyWithImpl<$Res>
    implements $FetchLikeMastersCopyWith<$Res> {
  _$FetchLikeMastersCopyWithImpl(this._self, this._then);

  final FetchLikeMasters _self;
  final $Res Function(FetchLikeMasters) _then;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,}) {
  return _then(FetchLikeMasters(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}


}

/// @nodoc


class FetchMasterById implements MasterEvent {
  const FetchMasterById(this.context, {required this.master});
  

 final  BuildContext context;
 final  MasterModel master;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMasterByIdCopyWith<FetchMasterById> get copyWith => _$FetchMasterByIdCopyWithImpl<FetchMasterById>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMasterById&&(identical(other.context, context) || other.context == context)&&(identical(other.master, master) || other.master == master));
}


@override
int get hashCode => Object.hash(runtimeType,context,master);

@override
String toString() {
  return 'MasterEvent.fetchMasterById(context: $context, master: $master)';
}


}

/// @nodoc
abstract mixin class $FetchMasterByIdCopyWith<$Res> implements $MasterEventCopyWith<$Res> {
  factory $FetchMasterByIdCopyWith(FetchMasterById value, $Res Function(FetchMasterById) _then) = _$FetchMasterByIdCopyWithImpl;
@useResult
$Res call({
 BuildContext context, MasterModel master
});




}
/// @nodoc
class _$FetchMasterByIdCopyWithImpl<$Res>
    implements $FetchMasterByIdCopyWith<$Res> {
  _$FetchMasterByIdCopyWithImpl(this._self, this._then);

  final FetchMasterById _self;
  final $Res Function(FetchMasterById) _then;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? master = null,}) {
  return _then(FetchMasterById(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,master: null == master ? _self.master : master // ignore: cast_nullable_to_non_nullable
as MasterModel,
  ));
}


}

/// @nodoc


class FetchMasterImage implements MasterEvent {
  const FetchMasterImage(this.context, {required this.id});
  

 final  BuildContext context;
 final  int id;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchMasterImageCopyWith<FetchMasterImage> get copyWith => _$FetchMasterImageCopyWithImpl<FetchMasterImage>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchMasterImage&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,context,id);

@override
String toString() {
  return 'MasterEvent.fetchMasterImage(context: $context, id: $id)';
}


}

/// @nodoc
abstract mixin class $FetchMasterImageCopyWith<$Res> implements $MasterEventCopyWith<$Res> {
  factory $FetchMasterImageCopyWith(FetchMasterImage value, $Res Function(FetchMasterImage) _then) = _$FetchMasterImageCopyWithImpl;
@useResult
$Res call({
 BuildContext context, int id
});




}
/// @nodoc
class _$FetchMasterImageCopyWithImpl<$Res>
    implements $FetchMasterImageCopyWith<$Res> {
  _$FetchMasterImageCopyWithImpl(this._self, this._then);

  final FetchMasterImage _self;
  final $Res Function(FetchMasterImage) _then;

/// Create a copy of MasterEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,}) {
  return _then(FetchMasterImage(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class UpdateState implements MasterEvent {
  const UpdateState();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateState);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'MasterEvent.updateState()';
}


}




/// @nodoc
mixin _$MasterState {

 List<MasterModel> get masters; MasterModel? get master; List<MasterModel> get likeMasters; List<Galleries> get masterImages; bool get isLoading;
/// Create a copy of MasterState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MasterStateCopyWith<MasterState> get copyWith => _$MasterStateCopyWithImpl<MasterState>(this as MasterState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MasterState&&const DeepCollectionEquality().equals(other.masters, masters)&&(identical(other.master, master) || other.master == master)&&const DeepCollectionEquality().equals(other.likeMasters, likeMasters)&&const DeepCollectionEquality().equals(other.masterImages, masterImages)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(masters),master,const DeepCollectionEquality().hash(likeMasters),const DeepCollectionEquality().hash(masterImages),isLoading);

@override
String toString() {
  return 'MasterState(masters: $masters, master: $master, likeMasters: $likeMasters, masterImages: $masterImages, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class $MasterStateCopyWith<$Res>  {
  factory $MasterStateCopyWith(MasterState value, $Res Function(MasterState) _then) = _$MasterStateCopyWithImpl;
@useResult
$Res call({
 List<MasterModel> masters, MasterModel? master, List<MasterModel> likeMasters, List<Galleries> masterImages, bool isLoading
});




}
/// @nodoc
class _$MasterStateCopyWithImpl<$Res>
    implements $MasterStateCopyWith<$Res> {
  _$MasterStateCopyWithImpl(this._self, this._then);

  final MasterState _self;
  final $Res Function(MasterState) _then;

/// Create a copy of MasterState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? masters = null,Object? master = freezed,Object? likeMasters = null,Object? masterImages = null,Object? isLoading = null,}) {
  return _then(_self.copyWith(
masters: null == masters ? _self.masters : masters // ignore: cast_nullable_to_non_nullable
as List<MasterModel>,master: freezed == master ? _self.master : master // ignore: cast_nullable_to_non_nullable
as MasterModel?,likeMasters: null == likeMasters ? _self.likeMasters : likeMasters // ignore: cast_nullable_to_non_nullable
as List<MasterModel>,masterImages: null == masterImages ? _self.masterImages : masterImages // ignore: cast_nullable_to_non_nullable
as List<Galleries>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [MasterState].
extension MasterStatePatterns on MasterState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MasterState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MasterState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MasterState value)  $default,){
final _that = this;
switch (_that) {
case _MasterState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MasterState value)?  $default,){
final _that = this;
switch (_that) {
case _MasterState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<MasterModel> masters,  MasterModel? master,  List<MasterModel> likeMasters,  List<Galleries> masterImages,  bool isLoading)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MasterState() when $default != null:
return $default(_that.masters,_that.master,_that.likeMasters,_that.masterImages,_that.isLoading);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<MasterModel> masters,  MasterModel? master,  List<MasterModel> likeMasters,  List<Galleries> masterImages,  bool isLoading)  $default,) {final _that = this;
switch (_that) {
case _MasterState():
return $default(_that.masters,_that.master,_that.likeMasters,_that.masterImages,_that.isLoading);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<MasterModel> masters,  MasterModel? master,  List<MasterModel> likeMasters,  List<Galleries> masterImages,  bool isLoading)?  $default,) {final _that = this;
switch (_that) {
case _MasterState() when $default != null:
return $default(_that.masters,_that.master,_that.likeMasters,_that.masterImages,_that.isLoading);case _:
  return null;

}
}

}

/// @nodoc


class _MasterState implements MasterState {
  const _MasterState({final  List<MasterModel> masters = const [], this.master = null, final  List<MasterModel> likeMasters = const [], final  List<Galleries> masterImages = const [], this.isLoading = true}): _masters = masters,_likeMasters = likeMasters,_masterImages = masterImages;
  

 final  List<MasterModel> _masters;
@override@JsonKey() List<MasterModel> get masters {
  if (_masters is EqualUnmodifiableListView) return _masters;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_masters);
}

@override@JsonKey() final  MasterModel? master;
 final  List<MasterModel> _likeMasters;
@override@JsonKey() List<MasterModel> get likeMasters {
  if (_likeMasters is EqualUnmodifiableListView) return _likeMasters;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_likeMasters);
}

 final  List<Galleries> _masterImages;
@override@JsonKey() List<Galleries> get masterImages {
  if (_masterImages is EqualUnmodifiableListView) return _masterImages;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_masterImages);
}

@override@JsonKey() final  bool isLoading;

/// Create a copy of MasterState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MasterStateCopyWith<_MasterState> get copyWith => __$MasterStateCopyWithImpl<_MasterState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _MasterState&&const DeepCollectionEquality().equals(other._masters, _masters)&&(identical(other.master, master) || other.master == master)&&const DeepCollectionEquality().equals(other._likeMasters, _likeMasters)&&const DeepCollectionEquality().equals(other._masterImages, _masterImages)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_masters),master,const DeepCollectionEquality().hash(_likeMasters),const DeepCollectionEquality().hash(_masterImages),isLoading);

@override
String toString() {
  return 'MasterState(masters: $masters, master: $master, likeMasters: $likeMasters, masterImages: $masterImages, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class _$MasterStateCopyWith<$Res> implements $MasterStateCopyWith<$Res> {
  factory _$MasterStateCopyWith(_MasterState value, $Res Function(_MasterState) _then) = __$MasterStateCopyWithImpl;
@override @useResult
$Res call({
 List<MasterModel> masters, MasterModel? master, List<MasterModel> likeMasters, List<Galleries> masterImages, bool isLoading
});




}
/// @nodoc
class __$MasterStateCopyWithImpl<$Res>
    implements _$MasterStateCopyWith<$Res> {
  __$MasterStateCopyWithImpl(this._self, this._then);

  final _MasterState _self;
  final $Res Function(_MasterState) _then;

/// Create a copy of MasterState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? masters = null,Object? master = freezed,Object? likeMasters = null,Object? masterImages = null,Object? isLoading = null,}) {
  return _then(_MasterState(
masters: null == masters ? _self._masters : masters // ignore: cast_nullable_to_non_nullable
as List<MasterModel>,master: freezed == master ? _self.master : master // ignore: cast_nullable_to_non_nullable
as MasterModel?,likeMasters: null == likeMasters ? _self._likeMasters : likeMasters // ignore: cast_nullable_to_non_nullable
as List<MasterModel>,masterImages: null == masterImages ? _self._masterImages : masterImages // ignore: cast_nullable_to_non_nullable
as List<Galleries>,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
