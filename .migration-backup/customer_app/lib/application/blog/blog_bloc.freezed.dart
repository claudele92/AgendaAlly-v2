// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'blog_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$BlogEvent {

 BuildContext get context;
/// Create a copy of BlogEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BlogEventCopyWith<BlogEvent> get copyWith => _$BlogEventCopyWithImpl<BlogEvent>(this as BlogEvent, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BlogEvent&&(identical(other.context, context) || other.context == context));
}


@override
int get hashCode => Object.hash(runtimeType,context);

@override
String toString() {
  return 'BlogEvent(context: $context)';
}


}

/// @nodoc
abstract mixin class $BlogEventCopyWith<$Res>  {
  factory $BlogEventCopyWith(BlogEvent value, $Res Function(BlogEvent) _then) = _$BlogEventCopyWithImpl;
@useResult
$Res call({
 BuildContext context
});




}
/// @nodoc
class _$BlogEventCopyWithImpl<$Res>
    implements $BlogEventCopyWith<$Res> {
  _$BlogEventCopyWithImpl(this._self, this._then);

  final BlogEvent _self;
  final $Res Function(BlogEvent) _then;

/// Create a copy of BlogEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? context = null,}) {
  return _then(_self.copyWith(
context: null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,
  ));
}

}


/// Adds pattern-matching-related methods to [BlogEvent].
extension BlogEventPatterns on BlogEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( FetchBlog value)?  fetchBlog,TResult Function( FetchBlogById value)?  fetchBlogById,required TResult orElse(),}){
final _that = this;
switch (_that) {
case FetchBlog() when fetchBlog != null:
return fetchBlog(_that);case FetchBlogById() when fetchBlogById != null:
return fetchBlogById(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( FetchBlog value)  fetchBlog,required TResult Function( FetchBlogById value)  fetchBlogById,}){
final _that = this;
switch (_that) {
case FetchBlog():
return fetchBlog(_that);case FetchBlogById():
return fetchBlogById(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( FetchBlog value)?  fetchBlog,TResult? Function( FetchBlogById value)?  fetchBlogById,}){
final _that = this;
switch (_that) {
case FetchBlog() when fetchBlog != null:
return fetchBlog(_that);case FetchBlogById() when fetchBlogById != null:
return fetchBlogById(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBlog,TResult Function( BuildContext context,  int id,  BlogData? blog)?  fetchBlogById,required TResult orElse(),}) {final _that = this;
switch (_that) {
case FetchBlog() when fetchBlog != null:
return fetchBlog(_that.context,_that.isRefresh,_that.controller);case FetchBlogById() when fetchBlogById != null:
return fetchBlogById(_that.context,_that.id,_that.blog);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)  fetchBlog,required TResult Function( BuildContext context,  int id,  BlogData? blog)  fetchBlogById,}) {final _that = this;
switch (_that) {
case FetchBlog():
return fetchBlog(_that.context,_that.isRefresh,_that.controller);case FetchBlogById():
return fetchBlogById(_that.context,_that.id,_that.blog);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  bool? isRefresh,  RefreshController? controller)?  fetchBlog,TResult? Function( BuildContext context,  int id,  BlogData? blog)?  fetchBlogById,}) {final _that = this;
switch (_that) {
case FetchBlog() when fetchBlog != null:
return fetchBlog(_that.context,_that.isRefresh,_that.controller);case FetchBlogById() when fetchBlogById != null:
return fetchBlogById(_that.context,_that.id,_that.blog);case _:
  return null;

}
}

}

/// @nodoc


class FetchBlog implements BlogEvent {
  const FetchBlog(this.context, {this.isRefresh, this.controller});
  

@override final  BuildContext context;
 final  bool? isRefresh;
 final  RefreshController? controller;

/// Create a copy of BlogEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchBlogCopyWith<FetchBlog> get copyWith => _$FetchBlogCopyWithImpl<FetchBlog>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchBlog&&(identical(other.context, context) || other.context == context)&&(identical(other.isRefresh, isRefresh) || other.isRefresh == isRefresh)&&(identical(other.controller, controller) || other.controller == controller));
}


@override
int get hashCode => Object.hash(runtimeType,context,isRefresh,controller);

@override
String toString() {
  return 'BlogEvent.fetchBlog(context: $context, isRefresh: $isRefresh, controller: $controller)';
}


}

/// @nodoc
abstract mixin class $FetchBlogCopyWith<$Res> implements $BlogEventCopyWith<$Res> {
  factory $FetchBlogCopyWith(FetchBlog value, $Res Function(FetchBlog) _then) = _$FetchBlogCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, bool? isRefresh, RefreshController? controller
});




}
/// @nodoc
class _$FetchBlogCopyWithImpl<$Res>
    implements $FetchBlogCopyWith<$Res> {
  _$FetchBlogCopyWithImpl(this._self, this._then);

  final FetchBlog _self;
  final $Res Function(FetchBlog) _then;

/// Create a copy of BlogEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? isRefresh = freezed,Object? controller = freezed,}) {
  return _then(FetchBlog(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,isRefresh: freezed == isRefresh ? _self.isRefresh : isRefresh // ignore: cast_nullable_to_non_nullable
as bool?,controller: freezed == controller ? _self.controller : controller // ignore: cast_nullable_to_non_nullable
as RefreshController?,
  ));
}


}

/// @nodoc


class FetchBlogById implements BlogEvent {
  const FetchBlogById(this.context, {required this.id, this.blog});
  

@override final  BuildContext context;
 final  int id;
 final  BlogData? blog;

/// Create a copy of BlogEvent
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$FetchBlogByIdCopyWith<FetchBlogById> get copyWith => _$FetchBlogByIdCopyWithImpl<FetchBlogById>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is FetchBlogById&&(identical(other.context, context) || other.context == context)&&(identical(other.id, id) || other.id == id)&&(identical(other.blog, blog) || other.blog == blog));
}


@override
int get hashCode => Object.hash(runtimeType,context,id,blog);

@override
String toString() {
  return 'BlogEvent.fetchBlogById(context: $context, id: $id, blog: $blog)';
}


}

/// @nodoc
abstract mixin class $FetchBlogByIdCopyWith<$Res> implements $BlogEventCopyWith<$Res> {
  factory $FetchBlogByIdCopyWith(FetchBlogById value, $Res Function(FetchBlogById) _then) = _$FetchBlogByIdCopyWithImpl;
@override @useResult
$Res call({
 BuildContext context, int id, BlogData? blog
});




}
/// @nodoc
class _$FetchBlogByIdCopyWithImpl<$Res>
    implements $FetchBlogByIdCopyWith<$Res> {
  _$FetchBlogByIdCopyWithImpl(this._self, this._then);

  final FetchBlogById _self;
  final $Res Function(FetchBlogById) _then;

/// Create a copy of BlogEvent
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? context = null,Object? id = null,Object? blog = freezed,}) {
  return _then(FetchBlogById(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,blog: freezed == blog ? _self.blog : blog // ignore: cast_nullable_to_non_nullable
as BlogData?,
  ));
}


}

/// @nodoc
mixin _$BlogState {

 List<BlogData> get blogs; BlogData? get blog; bool get isLoadingBlog; bool get isLoading;
/// Create a copy of BlogState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BlogStateCopyWith<BlogState> get copyWith => _$BlogStateCopyWithImpl<BlogState>(this as BlogState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BlogState&&const DeepCollectionEquality().equals(other.blogs, blogs)&&(identical(other.blog, blog) || other.blog == blog)&&(identical(other.isLoadingBlog, isLoadingBlog) || other.isLoadingBlog == isLoadingBlog)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(blogs),blog,isLoadingBlog,isLoading);

@override
String toString() {
  return 'BlogState(blogs: $blogs, blog: $blog, isLoadingBlog: $isLoadingBlog, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class $BlogStateCopyWith<$Res>  {
  factory $BlogStateCopyWith(BlogState value, $Res Function(BlogState) _then) = _$BlogStateCopyWithImpl;
@useResult
$Res call({
 List<BlogData> blogs, BlogData? blog, bool isLoadingBlog, bool isLoading
});




}
/// @nodoc
class _$BlogStateCopyWithImpl<$Res>
    implements $BlogStateCopyWith<$Res> {
  _$BlogStateCopyWithImpl(this._self, this._then);

  final BlogState _self;
  final $Res Function(BlogState) _then;

/// Create a copy of BlogState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? blogs = null,Object? blog = freezed,Object? isLoadingBlog = null,Object? isLoading = null,}) {
  return _then(_self.copyWith(
blogs: null == blogs ? _self.blogs : blogs // ignore: cast_nullable_to_non_nullable
as List<BlogData>,blog: freezed == blog ? _self.blog : blog // ignore: cast_nullable_to_non_nullable
as BlogData?,isLoadingBlog: null == isLoadingBlog ? _self.isLoadingBlog : isLoadingBlog // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [BlogState].
extension BlogStatePatterns on BlogState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BlogState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BlogState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BlogState value)  $default,){
final _that = this;
switch (_that) {
case _BlogState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BlogState value)?  $default,){
final _that = this;
switch (_that) {
case _BlogState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<BlogData> blogs,  BlogData? blog,  bool isLoadingBlog,  bool isLoading)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BlogState() when $default != null:
return $default(_that.blogs,_that.blog,_that.isLoadingBlog,_that.isLoading);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<BlogData> blogs,  BlogData? blog,  bool isLoadingBlog,  bool isLoading)  $default,) {final _that = this;
switch (_that) {
case _BlogState():
return $default(_that.blogs,_that.blog,_that.isLoadingBlog,_that.isLoading);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<BlogData> blogs,  BlogData? blog,  bool isLoadingBlog,  bool isLoading)?  $default,) {final _that = this;
switch (_that) {
case _BlogState() when $default != null:
return $default(_that.blogs,_that.blog,_that.isLoadingBlog,_that.isLoading);case _:
  return null;

}
}

}

/// @nodoc


class _BlogState implements BlogState {
  const _BlogState({final  List<BlogData> blogs = const [], this.blog = null, this.isLoadingBlog = true, this.isLoading = true}): _blogs = blogs;
  

 final  List<BlogData> _blogs;
@override@JsonKey() List<BlogData> get blogs {
  if (_blogs is EqualUnmodifiableListView) return _blogs;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_blogs);
}

@override@JsonKey() final  BlogData? blog;
@override@JsonKey() final  bool isLoadingBlog;
@override@JsonKey() final  bool isLoading;

/// Create a copy of BlogState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BlogStateCopyWith<_BlogState> get copyWith => __$BlogStateCopyWithImpl<_BlogState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _BlogState&&const DeepCollectionEquality().equals(other._blogs, _blogs)&&(identical(other.blog, blog) || other.blog == blog)&&(identical(other.isLoadingBlog, isLoadingBlog) || other.isLoadingBlog == isLoadingBlog)&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading));
}


@override
int get hashCode => Object.hash(runtimeType,const DeepCollectionEquality().hash(_blogs),blog,isLoadingBlog,isLoading);

@override
String toString() {
  return 'BlogState(blogs: $blogs, blog: $blog, isLoadingBlog: $isLoadingBlog, isLoading: $isLoading)';
}


}

/// @nodoc
abstract mixin class _$BlogStateCopyWith<$Res> implements $BlogStateCopyWith<$Res> {
  factory _$BlogStateCopyWith(_BlogState value, $Res Function(_BlogState) _then) = __$BlogStateCopyWithImpl;
@override @useResult
$Res call({
 List<BlogData> blogs, BlogData? blog, bool isLoadingBlog, bool isLoading
});




}
/// @nodoc
class __$BlogStateCopyWithImpl<$Res>
    implements _$BlogStateCopyWith<$Res> {
  __$BlogStateCopyWithImpl(this._self, this._then);

  final _BlogState _self;
  final $Res Function(_BlogState) _then;

/// Create a copy of BlogState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? blogs = null,Object? blog = freezed,Object? isLoadingBlog = null,Object? isLoading = null,}) {
  return _then(_BlogState(
blogs: null == blogs ? _self._blogs : blogs // ignore: cast_nullable_to_non_nullable
as List<BlogData>,blog: freezed == blog ? _self.blog : blog // ignore: cast_nullable_to_non_nullable
as BlogData?,isLoadingBlog: null == isLoadingBlog ? _self.isLoadingBlog : isLoadingBlog // ignore: cast_nullable_to_non_nullable
as bool,isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
