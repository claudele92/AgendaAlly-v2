// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'form_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$FormEvent {

 BuildContext get context;
/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FormEventCopyWith<FormEvent> get copyWith => _$FormEventCopyWithImpl<FormEvent>(this as FormEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FormEvent&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'FormEvent(context: $context)';
}


}

/// @nodoc
abstract mixin class $FormEventCopyWith<$Res>  {
  factory $FormEventCopyWith(FormEvent value, $Res Function(FormEvent) _then) = _$FormEventCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$FormEventCopyWithImpl<$Res>
    implements $FormEventCopyWith<$Res> {
  _$FormEventCopyWithImpl(this._self, this._then);

  final FormEvent _self;
  final $Res Function(FormEvent) _then;

/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? context = null,}) {
  return _then(_self.copyWith(
context: null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}

}


/// Adds pattern-matching-related methods to [FormEvent].
extension FormEventPatterns on FormEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchForms value)?  fetchForms,TResult Function( FetchSingleForms value)?  fetchSingleForms,TResult Function( SetFormOption value)?  setFormOption,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchForms() when fetchForms != null:
return fetchForms(_that);case FetchSingleForms() when fetchSingleForms != null:
return fetchSingleForms(_that);case SetFormOption() when setFormOption != null:
return setFormOption(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchForms value)  fetchForms,required TResult Function( FetchSingleForms value)  fetchSingleForms,required TResult Function( SetFormOption value)  setFormOption,}){
final _that = this;
switch (_that) {
case FetchForms():
return fetchForms(_that);case FetchSingleForms():
return fetchSingleForms(_that);case SetFormOption():
return setFormOption(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchForms value)?  fetchForms,TResult? Function( FetchSingleForms value)?  fetchSingleForms,TResult? Function( SetFormOption value)?  setFormOption,}){
final _that = this;
switch (_that) {
case FetchForms() when fetchForms != null:
return fetchForms(_that);case FetchSingleForms() when fetchSingleForms != null:
return fetchSingleForms(_that);case SetFormOption() when setFormOption != null:
return setFormOption(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  List<int>? serviceMasterIds)?  fetchForms,TResult Function( BuildContext context,  int? id,  FormOptionsData? form)?  fetchSingleForms,TResult Function( BuildContext context,  FormOptionsData? formOptions)?  setFormOption,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchForms() when fetchForms != null:
return fetchForms(_that.context,_that.serviceMasterIds);case FetchSingleForms() when fetchSingleForms != null:
return fetchSingleForms(_that.context,_that.id,_that.form);case SetFormOption() when setFormOption != null:
return setFormOption(_that.context,_that.formOptions);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  List<int>? serviceMasterIds)  fetchForms,required TResult Function( BuildContext context,  int? id,  FormOptionsData? form)  fetchSingleForms,required TResult Function( BuildContext context,  FormOptionsData? formOptions)  setFormOption,}) {final _that = this;
switch (_that) {
case FetchForms():
return fetchForms(_that.context,_that.serviceMasterIds);case FetchSingleForms():
return fetchSingleForms(_that.context,_that.id,_that.form);case SetFormOption():
return setFormOption(_that.context,_that.formOptions);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  List<int>? serviceMasterIds)?  fetchForms,TResult? Function( BuildContext context,  int? id,  FormOptionsData? form)?  fetchSingleForms,TResult? Function( BuildContext context,  FormOptionsData? formOptions)?  setFormOption,}) {final _that = this;
switch (_that) {
case FetchForms() when fetchForms != null:
return fetchForms(_that.context,_that.serviceMasterIds);case FetchSingleForms() when fetchSingleForms != null:
return fetchSingleForms(_that.context,_that.id,_that.form);case SetFormOption() when setFormOption != null:
return setFormOption(_that.context,_that.formOptions);case _:
  return null;

}
}

}

/// @nodoc


class FetchForms implements FormEvent {
  const FetchForms(this.context, {required final  List<int>? serviceMasterIds}): _serviceMasterIds = serviceMasterIds;
  

@override final  BuildContext context;
 final  List<int>? _serviceMasterIds;
 List<int>? get serviceMasterIds {
  final value = _serviceMasterIds;
  if (value == null) return null;
  if (_serviceMasterIds is EqualUnmodifiableListView) return _serviceMasterIds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}


/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchFormsCopyWith<FetchForms> get copyWith => _$FetchFormsCopyWithImpl<FetchForms>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchForms&&(identical(other.context, context) || other.context == context)&&const DeepCollectionEquality().equals(other._serviceMasterIds, _serviceMasterIds));
}


@override
int get hashCode => Object.hash(runtimeType,context,const DeepCollectionEquality().hash(_serviceMasterIds));

@override
String toString() {
  return 'FormEvent.fetchForms(context: $context, serviceMasterIds: $serviceMasterIds)';
}


}

/// @nodoc
abstract mixin class $FetchFormsCopyWith<$Res> implements $FormEventCopyWith<$Res> {
  factory $FetchFormsCopyWith(FetchForms value, $Res Function(FetchForms) _then) = _$FetchFormsCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, List<int>? serviceMasterIds
});




}
/// @nodoc
class _$FetchFormsCopyWithImpl<$Res>
    implements $FetchFormsCopyWith<$Res> {
  _$FetchFormsCopyWithImpl(this._self, this._then);

  final FetchForms _self;
  final $Res Function(FetchForms) _then;

/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? serviceMasterIds = freezed,}) {
  return _then(FetchForms(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,serviceMasterIds: freezed == serviceMasterIds ? _self._serviceMasterIds : serviceMasterIds // ignore: cast_nullable_to_non_nullable
as List<int>?,
  ));
}


}

/// @nodoc


class FetchSingleForms implements FormEvent {
  const FetchSingleForms(this.context, {required this.id, this.form});
  

@override final  BuildContext context;
 final  int? id;
 final  FormOptionsData? form;

/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchSingleFormsCopyWith<FetchSingleForms> get copyWith => _$FetchSingleFormsCopyWithImpl<FetchSingleForms>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchSingleForms&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.form, form) || other.form == form));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,form);

@override
String toString() {
  return 'FormEvent.fetchSingleForms(context: $context, id: $id, form: $form)';
}


}

/// @nodoc
abstract mixin class $FetchSingleFormsCopyWith<$Res> implements $FormEventCopyWith<$Res> {
  factory $FetchSingleFormsCopyWith(FetchSingleForms value, $Res Function(FetchSingleForms) _then) = _$FetchSingleFormsCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int? id, FormOptionsData? form
});




}
/// @nodoc
class _$FetchSingleFormsCopyWithImpl<$Res>
    implements $FetchSingleFormsCopyWith<$Res> {
  _$FetchSingleFormsCopyWithImpl(this._self, this._then);

  final FetchSingleForms _self;
  final $Res Function(FetchSingleForms) _then;

/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = freezed,Object? form = freezed,}) {
  return _then(FetchSingleForms(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: freezed == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int?,form: freezed == form ? _self.form : form // ignore: cast_nullable_to_non_nullable
as FormOptionsData?,
  ));
}


}

/// @nodoc


class SetFormOption implements FormEvent {
  const SetFormOption(this.context, {required this.formOptions});
  

@override final  BuildContext context;
 final  FormOptionsData? formOptions;

/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetFormOptionCopyWith<SetFormOption> get copyWith => _$SetFormOptionCopyWithImpl<SetFormOption>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetFormOption&&(identical(other.context, context) || other.context == context)&&(identical(other.formOptions, formOptions) || other.formOptions == formOptions));
}


@override
int get hashCode => Object.hash(runtimeType,context,formOptions);

@override
String toString() {
  return 'FormEvent.setFormOption(context: $context, formOptions: $formOptions)';
}


}

/// @nodoc
abstract mixin class $SetFormOptionCopyWith<$Res> implements $FormEventCopyWith<$Res> {
  factory $SetFormOptionCopyWith(SetFormOption value, $Res Function(SetFormOption) _then) = _$SetFormOptionCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, FormOptionsData? formOptions
});




}
/// @nodoc
class _$SetFormOptionCopyWithImpl<$Res>
    implements $SetFormOptionCopyWith<$Res> {
  _$SetFormOptionCopyWithImpl(this._self, this._then);

  final SetFormOption _self;
  final $Res Function(SetFormOption) _then;

/// Create a copy of FormEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? formOptions = freezed,}) {
  return _then(SetFormOption(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,formOptions: freezed == formOptions ? _self.formOptions : formOptions // ignore: cast_nullable_to_non_nullable
as FormOptionsData?,
  ));
}


}

/// @nodoc
mixin _$FormOptionsState {

 List<FormOptionsData> get formOptionList; FormOptionsData? get formOptions; bool get isLoading;
/// Create a copy of FormOptionsState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FormOptionsStateCopyWith<FormOptionsState> get copyWith => _$FormOptionsStateCopyWithImpl<FormOptionsState>(this as FormOptionsState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FormOptionsState&&const DeepCollectionEquality().equals(other.formOptionList, formOptionList)&&(identical(other.formOptions, formOptions) || other.formOptions == formOptions)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(formOptionList),formOptions,isLoading);

@override
String toString() {
  return 'FormOptionsState(formOptionList: $formOptionList, formOptions: $formOptions, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class $FormOptionsStateCopyWith<$Res>  {
  factory $FormOptionsStateCopyWith(FormOptionsState value, $Res Function(FormOptionsState) _then) = _$FormOptionsStateCopyWithImpl;
@useResult
$Res call({
 List<FormOptionsData> formOptionList, FormOptionsData? formOptions, bool isLoading
});




}
/// @nodoc
class _$FormOptionsStateCopyWithImpl<$Res>
    implements $FormOptionsStateCopyWith<$Res> {
  _$FormOptionsStateCopyWithImpl(this._self, this._then);

  final FormOptionsState _self;
  final $Res Function(FormOptionsState) _then;

/// Create a copy of FormOptionsState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? formOptionList = null,Object? formOptions = freezed,Object? isLoading = null,}) {
  return _then(_self.copyWith(
formOptionList: null == formOptionList ? _self.formOptionList : formOptionList // ignore: cast_nullable_to_non_nullable
as List<FormOptionsData>,formOptions: freezed == formOptions ? _self.formOptions : formOptions // ignore: cast_nullable_to_non_nullable
as FormOptionsData?,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [FormOptionsState].
extension FormOptionsStatePatterns on FormOptionsState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _FormOptionsState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _FormOptionsState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _FormOptionsState value)  $default,){
final _that = this;
switch (_that) {
case _FormOptionsState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _FormOptionsState value)?  $default,){
final _that = this;
switch (_that) {
case _FormOptionsState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<FormOptionsData> formOptionList,  FormOptionsData? formOptions,  bool isLoading)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _FormOptionsState() when $default != null:
return $default(_that.formOptionList,_that.formOptions,_that.isLoading);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<FormOptionsData> formOptionList,  FormOptionsData? formOptions,  bool isLoading)  $default,) {final _that = this;
switch (_that) {
case _FormOptionsState():
return $default(_that.formOptionList,_that.formOptions,_that.isLoading);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<FormOptionsData> formOptionList,  FormOptionsData? formOptions,  bool isLoading)?  $default,) {final _that = this;
switch (_that) {
case _FormOptionsState() when $default != null:
return $default(_that.formOptionList,_that.formOptions,_that.isLoading);case _:
  return null;

}
}

}

/// @nodoc


class _FormOptionsState implements FormOptionsState {
  const _FormOptionsState({final  List<FormOptionsData> formOptionList = const [], this.formOptions = null, this.isLoading = true}): _formOptionList = formOptionList;
  

 final  List<FormOptionsData> _formOptionList;
@override@JsonKey() List<FormOptionsData> get formOptionList {
  if (_formOptionList is EqualUnmodifiableListView) return _formOptionList;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_formOptionList);
}

@override@JsonKey() final  FormOptionsData? formOptions;
@override@JsonKey() final  bool isLoading;

/// Create a copy of FormOptionsState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$FormOptionsStateCopyWith<_FormOptionsState> get copyWith => __$FormOptionsStateCopyWithImpl<_FormOptionsState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _FormOptionsState&&const DeepCollectionEquality().equals(other._formOptionList, _formOptionList)&&(identical(other.formOptions, formOptions) || other.formOptions == formOptions)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_formOptionList),formOptions,isLoading);

@override
String toString() {
  return 'FormOptionsState(formOptionList: $formOptionList, formOptions: $formOptions, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class _$FormOptionsStateCopyWith<$Res> implements $FormOptionsStateCopyWith<$Res> {
  factory _$FormOptionsStateCopyWith(_FormOptionsState value, $Res Function(_FormOptionsState) _then) = __$FormOptionsStateCopyWithImpl;
@override @useResult
$Res call({
 List<FormOptionsData> formOptionList, FormOptionsData? formOptions, bool isLoading
});




}
/// @nodoc
class __$FormOptionsStateCopyWithImpl<$Res>
    implements _$FormOptionsStateCopyWith<$Res> {
  __$FormOptionsStateCopyWithImpl(this._self, this._then);

  final _FormOptionsState _self;
  final $Res Function(_FormOptionsState) _then;

/// Create a copy of FormOptionsState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? formOptionList = null,Object? formOptions = freezed,Object? isLoading = null,}) {
  return _then(_FormOptionsState(
formOptionList: null == formOptionList ? _self._formOptionList : formOptionList // ignore: cast_nullable_to_non_nullable
as List<FormOptionsData>,formOptions: freezed == formOptions ? _self.formOptions : formOptions // ignore: cast_nullable_to_non_nullable
as FormOptionsData?,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
