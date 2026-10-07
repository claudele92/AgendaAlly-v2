// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'become_seller_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$BecomeSellerEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BecomeSellerEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'BecomeSellerEvent()';
}


}

/// @nodoc
class $BecomeSellerEventCopyWith<$Res>  {
$BecomeSellerEventCopyWith(BecomeSellerEvent _, $Res Function(BecomeSellerEvent) __);
}


/// Adds pattern-matching-related methods to [BecomeSellerEvent].
extension BecomeSellerEventPatterns on BecomeSellerEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( UpdateLogoPath value)?  updateLogoPath,TResult Function( UpdateFilePath value)?  updateFilePath,TResult Function( RemoveFilePath value)?  removeFilePath,TResult Function( UpdateBgPath value)?  updateBgPath,TResult Function( SetDeliveryTimeType value)?  setDeliveryTimeType,TResult Function( SetDeliveryType value)?  setDeliveryType,TResult Function( CreateShop value)?  createShop,required TResult orElse(),}){
final _that = this;
switch (_that) {
case UpdateLogoPath() when updateLogoPath != null:
return updateLogoPath(_that);case UpdateFilePath() when updateFilePath != null:
return updateFilePath(_that);case RemoveFilePath() when removeFilePath != null:
return removeFilePath(_that);case UpdateBgPath() when updateBgPath != null:
return updateBgPath(_that);case SetDeliveryTimeType() when setDeliveryTimeType != null:
return setDeliveryTimeType(_that);case SetDeliveryType() when setDeliveryType != null:
return setDeliveryType(_that);case CreateShop() when createShop != null:
return createShop(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( UpdateLogoPath value)  updateLogoPath,required TResult Function( UpdateFilePath value)  updateFilePath,required TResult Function( RemoveFilePath value)  removeFilePath,required TResult Function( UpdateBgPath value)  updateBgPath,required TResult Function( SetDeliveryTimeType value)  setDeliveryTimeType,required TResult Function( SetDeliveryType value)  setDeliveryType,required TResult Function( CreateShop value)  createShop,}){
final _that = this;
switch (_that) {
case UpdateLogoPath():
return updateLogoPath(_that);case UpdateFilePath():
return updateFilePath(_that);case RemoveFilePath():
return removeFilePath(_that);case UpdateBgPath():
return updateBgPath(_that);case SetDeliveryTimeType():
return setDeliveryTimeType(_that);case SetDeliveryType():
return setDeliveryType(_that);case CreateShop():
return createShop(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( UpdateLogoPath value)?  updateLogoPath,TResult? Function( UpdateFilePath value)?  updateFilePath,TResult? Function( RemoveFilePath value)?  removeFilePath,TResult? Function( UpdateBgPath value)?  updateBgPath,TResult? Function( SetDeliveryTimeType value)?  setDeliveryTimeType,TResult? Function( SetDeliveryType value)?  setDeliveryType,TResult? Function( CreateShop value)?  createShop,}){
final _that = this;
switch (_that) {
case UpdateLogoPath() when updateLogoPath != null:
return updateLogoPath(_that);case UpdateFilePath() when updateFilePath != null:
return updateFilePath(_that);case RemoveFilePath() when removeFilePath != null:
return removeFilePath(_that);case UpdateBgPath() when updateBgPath != null:
return updateBgPath(_that);case SetDeliveryTimeType() when setDeliveryTimeType != null:
return setDeliveryTimeType(_that);case SetDeliveryType() when setDeliveryType != null:
return setDeliveryType(_that);case CreateShop() when createShop != null:
return createShop(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( String? imagePath)?  updateLogoPath,TResult Function( String? file)?  updateFilePath,TResult Function( String? file)?  removeFilePath,TResult Function( String? imagePath)?  updateBgPath,TResult Function( String? type)?  setDeliveryTimeType,TResult Function( String? type)?  setDeliveryType,TResult Function( BuildContext context,  CreateShopModel request)?  createShop,required TResult orElse(),}) {final _that = this;
switch (_that) {
case UpdateLogoPath() when updateLogoPath != null:
return updateLogoPath(_that.imagePath);case UpdateFilePath() when updateFilePath != null:
return updateFilePath(_that.file);case RemoveFilePath() when removeFilePath != null:
return removeFilePath(_that.file);case UpdateBgPath() when updateBgPath != null:
return updateBgPath(_that.imagePath);case SetDeliveryTimeType() when setDeliveryTimeType != null:
return setDeliveryTimeType(_that.type);case SetDeliveryType() when setDeliveryType != null:
return setDeliveryType(_that.type);case CreateShop() when createShop != null:
return createShop(_that.context,_that.request);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( String? imagePath)  updateLogoPath,required TResult Function( String? file)  updateFilePath,required TResult Function( String? file)  removeFilePath,required TResult Function( String? imagePath)  updateBgPath,required TResult Function( String? type)  setDeliveryTimeType,required TResult Function( String? type)  setDeliveryType,required TResult Function( BuildContext context,  CreateShopModel request)  createShop,}) {final _that = this;
switch (_that) {
case UpdateLogoPath():
return updateLogoPath(_that.imagePath);case UpdateFilePath():
return updateFilePath(_that.file);case RemoveFilePath():
return removeFilePath(_that.file);case UpdateBgPath():
return updateBgPath(_that.imagePath);case SetDeliveryTimeType():
return setDeliveryTimeType(_that.type);case SetDeliveryType():
return setDeliveryType(_that.type);case CreateShop():
return createShop(_that.context,_that.request);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( String? imagePath)?  updateLogoPath,TResult? Function( String? file)?  updateFilePath,TResult? Function( String? file)?  removeFilePath,TResult? Function( String? imagePath)?  updateBgPath,TResult? Function( String? type)?  setDeliveryTimeType,TResult? Function( String? type)?  setDeliveryType,TResult? Function( BuildContext context,  CreateShopModel request)?  createShop,}) {final _that = this;
switch (_that) {
case UpdateLogoPath() when updateLogoPath != null:
return updateLogoPath(_that.imagePath);case UpdateFilePath() when updateFilePath != null:
return updateFilePath(_that.file);case RemoveFilePath() when removeFilePath != null:
return removeFilePath(_that.file);case UpdateBgPath() when updateBgPath != null:
return updateBgPath(_that.imagePath);case SetDeliveryTimeType() when setDeliveryTimeType != null:
return setDeliveryTimeType(_that.type);case SetDeliveryType() when setDeliveryType != null:
return setDeliveryType(_that.type);case CreateShop() when createShop != null:
return createShop(_that.context,_that.request);case _:
  return null;

}
}

}

/// @nodoc


class UpdateLogoPath implements BecomeSellerEvent {
  const UpdateLogoPath({required this.imagePath});
  

 final  String? imagePath;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UpdateLogoPathCopyWith<UpdateLogoPath> get copyWith => _$UpdateLogoPathCopyWithImpl<UpdateLogoPath>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateLogoPath&&(identical(other.imagePath, imagePath) || other.imagePath == imagePath));
}


@override
int get hashCode => Object.hash(runtimeType,imagePath);

@override
String toString() {
  return 'BecomeSellerEvent.updateLogoPath(imagePath: $imagePath)';
}


}

/// @nodoc
abstract mixin class $UpdateLogoPathCopyWith<$Res> implements $BecomeSellerEventCopyWith<$Res> {
  factory $UpdateLogoPathCopyWith(UpdateLogoPath value, $Res Function(UpdateLogoPath) _then) = _$UpdateLogoPathCopyWithImpl;
@useResult
$Res call({
 String? imagePath
});




}
/// @nodoc
class _$UpdateLogoPathCopyWithImpl<$Res>
    implements $UpdateLogoPathCopyWith<$Res> {
  _$UpdateLogoPathCopyWithImpl(this._self, this._then);

  final UpdateLogoPath _self;
  final $Res Function(UpdateLogoPath) _then;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? imagePath = freezed,}) {
  return _then(UpdateLogoPath(
imagePath: freezed == imagePath ? _self.imagePath : imagePath // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class UpdateFilePath implements BecomeSellerEvent {
  const UpdateFilePath({required this.file});
  

 final  String? file;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UpdateFilePathCopyWith<UpdateFilePath> get copyWith => _$UpdateFilePathCopyWithImpl<UpdateFilePath>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateFilePath&&(identical(other.file, file) || other.file == file));
}


@override
int get hashCode => Object.hash(runtimeType,file);

@override
String toString() {
  return 'BecomeSellerEvent.updateFilePath(file: $file)';
}


}

/// @nodoc
abstract mixin class $UpdateFilePathCopyWith<$Res> implements $BecomeSellerEventCopyWith<$Res> {
  factory $UpdateFilePathCopyWith(UpdateFilePath value, $Res Function(UpdateFilePath) _then) = _$UpdateFilePathCopyWithImpl;
@useResult
$Res call({
 String? file
});




}
/// @nodoc
class _$UpdateFilePathCopyWithImpl<$Res>
    implements $UpdateFilePathCopyWith<$Res> {
  _$UpdateFilePathCopyWithImpl(this._self, this._then);

  final UpdateFilePath _self;
  final $Res Function(UpdateFilePath) _then;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? file = freezed,}) {
  return _then(UpdateFilePath(
file: freezed == file ? _self.file : file // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class RemoveFilePath implements BecomeSellerEvent {
  const RemoveFilePath({required this.file});
  

 final  String? file;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RemoveFilePathCopyWith<RemoveFilePath> get copyWith => _$RemoveFilePathCopyWithImpl<RemoveFilePath>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RemoveFilePath&&(identical(other.file, file) || other.file == file));
}


@override
int get hashCode => Object.hash(runtimeType,file);

@override
String toString() {
  return 'BecomeSellerEvent.removeFilePath(file: $file)';
}


}

/// @nodoc
abstract mixin class $RemoveFilePathCopyWith<$Res> implements $BecomeSellerEventCopyWith<$Res> {
  factory $RemoveFilePathCopyWith(RemoveFilePath value, $Res Function(RemoveFilePath) _then) = _$RemoveFilePathCopyWithImpl;
@useResult
$Res call({
 String? file
});




}
/// @nodoc
class _$RemoveFilePathCopyWithImpl<$Res>
    implements $RemoveFilePathCopyWith<$Res> {
  _$RemoveFilePathCopyWithImpl(this._self, this._then);

  final RemoveFilePath _self;
  final $Res Function(RemoveFilePath) _then;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? file = freezed,}) {
  return _then(RemoveFilePath(
file: freezed == file ? _self.file : file // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class UpdateBgPath implements BecomeSellerEvent {
  const UpdateBgPath({required this.imagePath});
  

 final  String? imagePath;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UpdateBgPathCopyWith<UpdateBgPath> get copyWith => _$UpdateBgPathCopyWithImpl<UpdateBgPath>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is UpdateBgPath&&(identical(other.imagePath, imagePath) || other.imagePath == imagePath));
}


@override
int get hashCode => Object.hash(runtimeType,imagePath);

@override
String toString() {
  return 'BecomeSellerEvent.updateBgPath(imagePath: $imagePath)';
}


}

/// @nodoc
abstract mixin class $UpdateBgPathCopyWith<$Res> implements $BecomeSellerEventCopyWith<$Res> {
  factory $UpdateBgPathCopyWith(UpdateBgPath value, $Res Function(UpdateBgPath) _then) = _$UpdateBgPathCopyWithImpl;
@useResult
$Res call({
 String? imagePath
});




}
/// @nodoc
class _$UpdateBgPathCopyWithImpl<$Res>
    implements $UpdateBgPathCopyWith<$Res> {
  _$UpdateBgPathCopyWithImpl(this._self, this._then);

  final UpdateBgPath _self;
  final $Res Function(UpdateBgPath) _then;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? imagePath = freezed,}) {
  return _then(UpdateBgPath(
imagePath: freezed == imagePath ? _self.imagePath : imagePath // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class SetDeliveryTimeType implements BecomeSellerEvent {
  const SetDeliveryTimeType({required this.type});
  

 final  String? type;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetDeliveryTimeTypeCopyWith<SetDeliveryTimeType> get copyWith => _$SetDeliveryTimeTypeCopyWithImpl<SetDeliveryTimeType>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetDeliveryTimeType&&(identical(other.type, type) || other.type == type));
}


@override
int get hashCode => Object.hash(runtimeType,type);

@override
String toString() {
  return 'BecomeSellerEvent.setDeliveryTimeType(type: $type)';
}


}

/// @nodoc
abstract mixin class $SetDeliveryTimeTypeCopyWith<$Res> implements $BecomeSellerEventCopyWith<$Res> {
  factory $SetDeliveryTimeTypeCopyWith(SetDeliveryTimeType value, $Res Function(SetDeliveryTimeType) _then) = _$SetDeliveryTimeTypeCopyWithImpl;
@useResult
$Res call({
 String? type
});




}
/// @nodoc
class _$SetDeliveryTimeTypeCopyWithImpl<$Res>
    implements $SetDeliveryTimeTypeCopyWith<$Res> {
  _$SetDeliveryTimeTypeCopyWithImpl(this._self, this._then);

  final SetDeliveryTimeType _self;
  final $Res Function(SetDeliveryTimeType) _then;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? type = freezed,}) {
  return _then(SetDeliveryTimeType(
type: freezed == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class SetDeliveryType implements BecomeSellerEvent {
  const SetDeliveryType({required this.type});
  

 final  String? type;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetDeliveryTypeCopyWith<SetDeliveryType> get copyWith => _$SetDeliveryTypeCopyWithImpl<SetDeliveryType>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetDeliveryType&&(identical(other.type, type) || other.type == type));
}


@override
int get hashCode => Object.hash(runtimeType,type);

@override
String toString() {
  return 'BecomeSellerEvent.setDeliveryType(type: $type)';
}


}

/// @nodoc
abstract mixin class $SetDeliveryTypeCopyWith<$Res> implements $BecomeSellerEventCopyWith<$Res> {
  factory $SetDeliveryTypeCopyWith(SetDeliveryType value, $Res Function(SetDeliveryType) _then) = _$SetDeliveryTypeCopyWithImpl;
@useResult
$Res call({
 String? type
});




}
/// @nodoc
class _$SetDeliveryTypeCopyWithImpl<$Res>
    implements $SetDeliveryTypeCopyWith<$Res> {
  _$SetDeliveryTypeCopyWithImpl(this._self, this._then);

  final SetDeliveryType _self;
  final $Res Function(SetDeliveryType) _then;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? type = freezed,}) {
  return _then(SetDeliveryType(
type: freezed == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class CreateShop implements BecomeSellerEvent {
  const CreateShop(this.context, {required this.request});
  

 final  BuildContext context;
 final  CreateShopModel request;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CreateShopCopyWith<CreateShop> get copyWith => _$CreateShopCopyWithImpl<CreateShop>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CreateShop&&(identical(other.context, context) || other.context == context)&&(identical(other.request, request) || other.request == request));
}


@override
int get hashCode => Object.hash(runtimeType,context,request);

@override
String toString() {
  return 'BecomeSellerEvent.createShop(context: $context, request: $request)';
}


}

/// @nodoc
abstract mixin class $CreateShopCopyWith<$Res> implements $BecomeSellerEventCopyWith<$Res> {
  factory $CreateShopCopyWith(CreateShop value, $Res Function(CreateShop) _then) = _$CreateShopCopyWithImpl;
@useResult
$Res call({
 BuildContext context, CreateShopModel request
});




}
/// @nodoc
class _$CreateShopCopyWithImpl<$Res>
    implements $CreateShopCopyWith<$Res> {
  _$CreateShopCopyWithImpl(this._self, this._then);

  final CreateShop _self;
  final $Res Function(CreateShop) _then;

/// Create a copy of BecomeSellerEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? request = null,}) {
  return _then(CreateShop(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,request: null == request ? _self.request : request // ignore: cast_nullable_to_non_nullable
as CreateShopModel,
  ));
}


}

/// @nodoc
mixin _$BecomeSellerState {

 String? get logoPath; String? get bgPath; List<String> get filepath; String get type; String get deliveryType; bool get isLoading; bool get isSuccess;
/// Create a copy of BecomeSellerState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BecomeSellerStateCopyWith<BecomeSellerState> get copyWith => _$BecomeSellerStateCopyWithImpl<BecomeSellerState>(this as BecomeSellerState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BecomeSellerState&&(identical(other.logoPath, logoPath) || other.logoPath == logoPath)&&(identical(other.bgPath, bgPath) || other.bgPath == bgPath)&&const DeepCollectionEquality().equals(other.filepath, filepath)&&(identical(other.type, type) || other.type == type)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isSuccess, isSuccess) || other.isSuccess == isSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,logoPath,bgPath,const DeepCollectionEquality().hash(filepath),type,deliveryType,isLoading,isSuccess);

@override
String toString() {
  return 'BecomeSellerState(logoPath: $logoPath, bgPath: $bgPath, filepath: $filepath, type: $type, deliveryType: $deliveryType, isLoading: $isLoading, isSuccess: $isSuccess)';
}


}

/// @nodoc
abstract mixin class $BecomeSellerStateCopyWith<$Res>  {
  factory $BecomeSellerStateCopyWith(BecomeSellerState value, $Res Function(BecomeSellerState) _then) = _$BecomeSellerStateCopyWithImpl;
@useResult
$Res call({
 String? logoPath, String? bgPath, List<String> filepath, String type, String deliveryType, bool isLoading, bool isSuccess
});




}
/// @nodoc
class _$BecomeSellerStateCopyWithImpl<$Res>
    implements $BecomeSellerStateCopyWith<$Res> {
  _$BecomeSellerStateCopyWithImpl(this._self, this._then);

  final BecomeSellerState _self;
  final $Res Function(BecomeSellerState) _then;

/// Create a copy of BecomeSellerState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? logoPath = freezed,Object? bgPath = freezed,Object? filepath = null,Object? type = null,Object? deliveryType = null,Object? isLoading = null,Object? isSuccess = null,}) {
  return _then(_self.copyWith(
logoPath: freezed == logoPath ? _self.logoPath : logoPath // ignore: cast_nullable_to_non_nullable
as String?,bgPath: freezed == bgPath ? _self.bgPath : bgPath // ignore: cast_nullable_to_non_nullable
as String?,filepath: null == filepath ? _self.filepath : filepath // ignore: cast_nullable_to_non_nullable
as List<String>,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isSuccess: null == isSuccess ? _self.isSuccess : isSuccess // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [BecomeSellerState].
extension BecomeSellerStatePatterns on BecomeSellerState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BecomeSellerState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BecomeSellerState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BecomeSellerState value)  $default,){
final _that = this;
switch (_that) {
case _BecomeSellerState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BecomeSellerState value)?  $default,){
final _that = this;
switch (_that) {
case _BecomeSellerState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? logoPath,  String? bgPath,  List<String> filepath,  String type,  String deliveryType,  bool isLoading,  bool isSuccess)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BecomeSellerState() when $default != null:
return $default(_that.logoPath,_that.bgPath,_that.filepath,_that.type,_that.deliveryType,_that.isLoading,_that.isSuccess);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? logoPath,  String? bgPath,  List<String> filepath,  String type,  String deliveryType,  bool isLoading,  bool isSuccess)  $default,) {final _that = this;
switch (_that) {
case _BecomeSellerState():
return $default(_that.logoPath,_that.bgPath,_that.filepath,_that.type,_that.deliveryType,_that.isLoading,_that.isSuccess);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? logoPath,  String? bgPath,  List<String> filepath,  String type,  String deliveryType,  bool isLoading,  bool isSuccess)?  $default,) {final _that = this;
switch (_that) {
case _BecomeSellerState() when $default != null:
return $default(_that.logoPath,_that.bgPath,_that.filepath,_that.type,_that.deliveryType,_that.isLoading,_that.isSuccess);case _:
  return null;

}
}

}

/// @nodoc


class _BecomeSellerState implements BecomeSellerState {
  const _BecomeSellerState({this.logoPath = null, this.bgPath = null, final  List<String> filepath = const [], this.type = TrKeys.month, this.deliveryType = TrKeys.wareHouse, this.isLoading = false, this.isSuccess = false}): _filepath = filepath;
  

@override@JsonKey() final  String? logoPath;
@override@JsonKey() final  String? bgPath;
 final  List<String> _filepath;
@override@JsonKey() List<String> get filepath {
  if (_filepath is EqualUnmodifiableListView) return _filepath;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_filepath);
}

@override@JsonKey() final  String type;
@override@JsonKey() final  String deliveryType;
@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isSuccess;

/// Create a copy of BecomeSellerState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BecomeSellerStateCopyWith<_BecomeSellerState> get copyWith => __$BecomeSellerStateCopyWithImpl<_BecomeSellerState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BecomeSellerState&&(identical(other.logoPath, logoPath) || other.logoPath == logoPath)&&(identical(other.bgPath, bgPath) || other.bgPath == bgPath)&&const DeepCollectionEquality().equals(other._filepath, _filepath)&&(identical(other.type, type) || other.type == type)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isSuccess, isSuccess) || other.isSuccess == isSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,logoPath,bgPath,const DeepCollectionEquality().hash(_filepath),type,deliveryType,isLoading,isSuccess);

@override
String toString() {
  return 'BecomeSellerState(logoPath: $logoPath, bgPath: $bgPath, filepath: $filepath, type: $type, deliveryType: $deliveryType, isLoading: $isLoading, isSuccess: $isSuccess)';
}


}

/// @nodoc
abstract mixin class _$BecomeSellerStateCopyWith<$Res> implements $BecomeSellerStateCopyWith<$Res> {
  factory _$BecomeSellerStateCopyWith(_BecomeSellerState value, $Res Function(_BecomeSellerState) _then) = __$BecomeSellerStateCopyWithImpl;
@override @useResult
$Res call({
 String? logoPath, String? bgPath, List<String> filepath, String type, String deliveryType, bool isLoading, bool isSuccess
});




}
/// @nodoc
class __$BecomeSellerStateCopyWithImpl<$Res>
    implements _$BecomeSellerStateCopyWith<$Res> {
  __$BecomeSellerStateCopyWithImpl(this._self, this._then);

  final _BecomeSellerState _self;
  final $Res Function(_BecomeSellerState) _then;

/// Create a copy of BecomeSellerState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? logoPath = freezed,Object? bgPath = freezed,Object? filepath = null,Object? type = null,Object? deliveryType = null,Object? isLoading = null,Object? isSuccess = null,}) {
  return _then(_BecomeSellerState(
logoPath: freezed == logoPath ? _self.logoPath : logoPath // ignore: cast_nullable_to_non_nullable
as String?,bgPath: freezed == bgPath ? _self.bgPath : bgPath // ignore: cast_nullable_to_non_nullable
as String?,filepath: null == filepath ? _self._filepath : filepath // ignore: cast_nullable_to_non_nullable
as List<String>,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isSuccess: null == isSuccess ? _self.isSuccess : isSuccess // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
