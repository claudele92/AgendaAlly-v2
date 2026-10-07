// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'chat_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$ChatEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChatEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'ChatEvent()';
}


}

/// @nodoc
class $ChatEventCopyWith<$Res>  {
$ChatEventCopyWith(ChatEvent _, $Res Function(ChatEvent) __);
}


/// Adds pattern-matching-related methods to [ChatEvent].
extension ChatEventPatterns on ChatEvent {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( CheckChatId value)?  checkChatId,TResult Function( SendImage value)?  sendImage,TResult Function( SendMessage value)?  sendMessage,TResult Function( EditMessage value)?  editMessage,TResult Function( ReplyMessage value)?  replyMessage,TResult Function( DeleteMessage value)?  deleteMessage,TResult Function( CreateAndSendMessage value)?  createAndSendMessage,required TResult orElse(),}){
final _that = this;
switch (_that) {
case CheckChatId() when checkChatId != null:
return checkChatId(_that);case SendImage() when sendImage != null:
return sendImage(_that);case SendMessage() when sendMessage != null:
return sendMessage(_that);case EditMessage() when editMessage != null:
return editMessage(_that);case ReplyMessage() when replyMessage != null:
return replyMessage(_that);case DeleteMessage() when deleteMessage != null:
return deleteMessage(_that);case CreateAndSendMessage() when createAndSendMessage != null:
return createAndSendMessage(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( CheckChatId value)  checkChatId,required TResult Function( SendImage value)  sendImage,required TResult Function( SendMessage value)  sendMessage,required TResult Function( EditMessage value)  editMessage,required TResult Function( ReplyMessage value)  replyMessage,required TResult Function( DeleteMessage value)  deleteMessage,required TResult Function( CreateAndSendMessage value)  createAndSendMessage,}){
final _that = this;
switch (_that) {
case CheckChatId():
return checkChatId(_that);case SendImage():
return sendImage(_that);case SendMessage():
return sendMessage(_that);case EditMessage():
return editMessage(_that);case ReplyMessage():
return replyMessage(_that);case DeleteMessage():
return deleteMessage(_that);case CreateAndSendMessage():
return createAndSendMessage(_that);case _:
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( CheckChatId value)?  checkChatId,TResult? Function( SendImage value)?  sendImage,TResult? Function( SendMessage value)?  sendMessage,TResult? Function( EditMessage value)?  editMessage,TResult? Function( ReplyMessage value)?  replyMessage,TResult? Function( DeleteMessage value)?  deleteMessage,TResult? Function( CreateAndSendMessage value)?  createAndSendMessage,}){
final _that = this;
switch (_that) {
case CheckChatId() when checkChatId != null:
return checkChatId(_that);case SendImage() when sendImage != null:
return sendImage(_that);case SendMessage() when sendMessage != null:
return sendMessage(_that);case EditMessage() when editMessage != null:
return editMessage(_that);case ReplyMessage() when replyMessage != null:
return replyMessage(_that);case DeleteMessage() when deleteMessage != null:
return deleteMessage(_that);case CreateAndSendMessage() when createAndSendMessage != null:
return createAndSendMessage(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( int sellerId)?  checkChatId,TResult Function( BuildContext context,  String file,  String? chatId)?  sendImage,TResult Function( BuildContext context,  String message,  String? chatId)?  sendMessage,TResult Function( BuildContext context,  String message,  String messageId,  String? chatId)?  editMessage,TResult Function( BuildContext context,  String message,  String messageId,  String? chatId)?  replyMessage,TResult Function( BuildContext context,  String messageId,  String? chatId)?  deleteMessage,TResult Function( BuildContext context,  String message,  int userId,  Function onSuccess)?  createAndSendMessage,required TResult orElse(),}) {final _that = this;
switch (_that) {
case CheckChatId() when checkChatId != null:
return checkChatId(_that.sellerId);case SendImage() when sendImage != null:
return sendImage(_that.context,_that.file,_that.chatId);case SendMessage() when sendMessage != null:
return sendMessage(_that.context,_that.message,_that.chatId);case EditMessage() when editMessage != null:
return editMessage(_that.context,_that.message,_that.messageId,_that.chatId);case ReplyMessage() when replyMessage != null:
return replyMessage(_that.context,_that.message,_that.messageId,_that.chatId);case DeleteMessage() when deleteMessage != null:
return deleteMessage(_that.context,_that.messageId,_that.chatId);case CreateAndSendMessage() when createAndSendMessage != null:
return createAndSendMessage(_that.context,_that.message,_that.userId,_that.onSuccess);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( int sellerId)  checkChatId,required TResult Function( BuildContext context,  String file,  String? chatId)  sendImage,required TResult Function( BuildContext context,  String message,  String? chatId)  sendMessage,required TResult Function( BuildContext context,  String message,  String messageId,  String? chatId)  editMessage,required TResult Function( BuildContext context,  String message,  String messageId,  String? chatId)  replyMessage,required TResult Function( BuildContext context,  String messageId,  String? chatId)  deleteMessage,required TResult Function( BuildContext context,  String message,  int userId,  Function onSuccess)  createAndSendMessage,}) {final _that = this;
switch (_that) {
case CheckChatId():
return checkChatId(_that.sellerId);case SendImage():
return sendImage(_that.context,_that.file,_that.chatId);case SendMessage():
return sendMessage(_that.context,_that.message,_that.chatId);case EditMessage():
return editMessage(_that.context,_that.message,_that.messageId,_that.chatId);case ReplyMessage():
return replyMessage(_that.context,_that.message,_that.messageId,_that.chatId);case DeleteMessage():
return deleteMessage(_that.context,_that.messageId,_that.chatId);case CreateAndSendMessage():
return createAndSendMessage(_that.context,_that.message,_that.userId,_that.onSuccess);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( int sellerId)?  checkChatId,TResult? Function( BuildContext context,  String file,  String? chatId)?  sendImage,TResult? Function( BuildContext context,  String message,  String? chatId)?  sendMessage,TResult? Function( BuildContext context,  String message,  String messageId,  String? chatId)?  editMessage,TResult? Function( BuildContext context,  String message,  String messageId,  String? chatId)?  replyMessage,TResult? Function( BuildContext context,  String messageId,  String? chatId)?  deleteMessage,TResult? Function( BuildContext context,  String message,  int userId,  Function onSuccess)?  createAndSendMessage,}) {final _that = this;
switch (_that) {
case CheckChatId() when checkChatId != null:
return checkChatId(_that.sellerId);case SendImage() when sendImage != null:
return sendImage(_that.context,_that.file,_that.chatId);case SendMessage() when sendMessage != null:
return sendMessage(_that.context,_that.message,_that.chatId);case EditMessage() when editMessage != null:
return editMessage(_that.context,_that.message,_that.messageId,_that.chatId);case ReplyMessage() when replyMessage != null:
return replyMessage(_that.context,_that.message,_that.messageId,_that.chatId);case DeleteMessage() when deleteMessage != null:
return deleteMessage(_that.context,_that.messageId,_that.chatId);case CreateAndSendMessage() when createAndSendMessage != null:
return createAndSendMessage(_that.context,_that.message,_that.userId,_that.onSuccess);case _:
  return null;

}
}

}

/// @nodoc


class CheckChatId implements ChatEvent {
  const CheckChatId({required this.sellerId});
  

 final  int sellerId;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CheckChatIdCopyWith<CheckChatId> get copyWith => _$CheckChatIdCopyWithImpl<CheckChatId>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckChatId&&(identical(other.sellerId, sellerId) || other.sellerId == sellerId));
}


@override
int get hashCode => Object.hash(runtimeType,sellerId);

@override
String toString() {
  return 'ChatEvent.checkChatId(sellerId: $sellerId)';
}


}

/// @nodoc
abstract mixin class $CheckChatIdCopyWith<$Res> implements $ChatEventCopyWith<$Res> {
  factory $CheckChatIdCopyWith(CheckChatId value, $Res Function(CheckChatId) _then) = _$CheckChatIdCopyWithImpl;
@useResult
$Res call({
 int sellerId
});




}
/// @nodoc
class _$CheckChatIdCopyWithImpl<$Res>
    implements $CheckChatIdCopyWith<$Res> {
  _$CheckChatIdCopyWithImpl(this._self, this._then);

  final CheckChatId _self;
  final $Res Function(CheckChatId) _then;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? sellerId = null,}) {
  return _then(CheckChatId(
sellerId: null == sellerId ? _self.sellerId : sellerId // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc


class SendImage implements ChatEvent {
  const SendImage(this.context, {required this.file, required this.chatId});
  

 final  BuildContext context;
 final  String file;
 final  String? chatId;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SendImageCopyWith<SendImage> get copyWith => _$SendImageCopyWithImpl<SendImage>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SendImage&&(identical(other.context, context) || other.context == context)&&(identical(other.file, file) || other.file == file)&&(identical(other.chatId, chatId) || other.chatId == chatId));
}


@override
int get hashCode => Object.hash(runtimeType,context,file,chatId);

@override
String toString() {
  return 'ChatEvent.sendImage(context: $context, file: $file, chatId: $chatId)';
}


}

/// @nodoc
abstract mixin class $SendImageCopyWith<$Res> implements $ChatEventCopyWith<$Res> {
  factory $SendImageCopyWith(SendImage value, $Res Function(SendImage) _then) = _$SendImageCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String file, String? chatId
});




}
/// @nodoc
class _$SendImageCopyWithImpl<$Res>
    implements $SendImageCopyWith<$Res> {
  _$SendImageCopyWithImpl(this._self, this._then);

  final SendImage _self;
  final $Res Function(SendImage) _then;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? file = null,Object? chatId = freezed,}) {
  return _then(SendImage(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,file: null == file ? _self.file : file // ignore: cast_nullable_to_non_nullable
as String,chatId: freezed == chatId ? _self.chatId : chatId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class SendMessage implements ChatEvent {
  const SendMessage(this.context, {required this.message, required this.chatId});
  

 final  BuildContext context;
 final  String message;
 final  String? chatId;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SendMessageCopyWith<SendMessage> get copyWith => _$SendMessageCopyWithImpl<SendMessage>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SendMessage&&(identical(other.context, context) || other.context == context)&&(identical(other.message, message) || other.message == message)&&(identical(other.chatId, chatId) || other.chatId == chatId));
}


@override
int get hashCode => Object.hash(runtimeType,context,message,chatId);

@override
String toString() {
  return 'ChatEvent.sendMessage(context: $context, message: $message, chatId: $chatId)';
}


}

/// @nodoc
abstract mixin class $SendMessageCopyWith<$Res> implements $ChatEventCopyWith<$Res> {
  factory $SendMessageCopyWith(SendMessage value, $Res Function(SendMessage) _then) = _$SendMessageCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String message, String? chatId
});




}
/// @nodoc
class _$SendMessageCopyWithImpl<$Res>
    implements $SendMessageCopyWith<$Res> {
  _$SendMessageCopyWithImpl(this._self, this._then);

  final SendMessage _self;
  final $Res Function(SendMessage) _then;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? message = null,Object? chatId = freezed,}) {
  return _then(SendMessage(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,message: null == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String,chatId: freezed == chatId ? _self.chatId : chatId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class EditMessage implements ChatEvent {
  const EditMessage(this.context, {required this.message, required this.messageId, required this.chatId});
  

 final  BuildContext context;
 final  String message;
 final  String messageId;
 final  String? chatId;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$EditMessageCopyWith<EditMessage> get copyWith => _$EditMessageCopyWithImpl<EditMessage>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is EditMessage&&(identical(other.context, context) || other.context == context)&&(identical(other.message, message) || other.message == message)&&(identical(other.messageId, messageId) || other.messageId == messageId)&&(identical(other.chatId, chatId) || other.chatId == chatId));
}


@override
int get hashCode => Object.hash(runtimeType,context,message,messageId,chatId);

@override
String toString() {
  return 'ChatEvent.editMessage(context: $context, message: $message, messageId: $messageId, chatId: $chatId)';
}


}

/// @nodoc
abstract mixin class $EditMessageCopyWith<$Res> implements $ChatEventCopyWith<$Res> {
  factory $EditMessageCopyWith(EditMessage value, $Res Function(EditMessage) _then) = _$EditMessageCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String message, String messageId, String? chatId
});




}
/// @nodoc
class _$EditMessageCopyWithImpl<$Res>
    implements $EditMessageCopyWith<$Res> {
  _$EditMessageCopyWithImpl(this._self, this._then);

  final EditMessage _self;
  final $Res Function(EditMessage) _then;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? message = null,Object? messageId = null,Object? chatId = freezed,}) {
  return _then(EditMessage(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,message: null == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String,messageId: null == messageId ? _self.messageId : messageId // ignore: cast_nullable_to_non_nullable
as String,chatId: freezed == chatId ? _self.chatId : chatId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class ReplyMessage implements ChatEvent {
  const ReplyMessage(this.context, {required this.message, required this.messageId, required this.chatId});
  

 final  BuildContext context;
 final  String message;
 final  String messageId;
 final  String? chatId;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ReplyMessageCopyWith<ReplyMessage> get copyWith => _$ReplyMessageCopyWithImpl<ReplyMessage>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ReplyMessage&&(identical(other.context, context) || other.context == context)&&(identical(other.message, message) || other.message == message)&&(identical(other.messageId, messageId) || other.messageId == messageId)&&(identical(other.chatId, chatId) || other.chatId == chatId));
}


@override
int get hashCode => Object.hash(runtimeType,context,message,messageId,chatId);

@override
String toString() {
  return 'ChatEvent.replyMessage(context: $context, message: $message, messageId: $messageId, chatId: $chatId)';
}


}

/// @nodoc
abstract mixin class $ReplyMessageCopyWith<$Res> implements $ChatEventCopyWith<$Res> {
  factory $ReplyMessageCopyWith(ReplyMessage value, $Res Function(ReplyMessage) _then) = _$ReplyMessageCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String message, String messageId, String? chatId
});




}
/// @nodoc
class _$ReplyMessageCopyWithImpl<$Res>
    implements $ReplyMessageCopyWith<$Res> {
  _$ReplyMessageCopyWithImpl(this._self, this._then);

  final ReplyMessage _self;
  final $Res Function(ReplyMessage) _then;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? message = null,Object? messageId = null,Object? chatId = freezed,}) {
  return _then(ReplyMessage(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,message: null == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String,messageId: null == messageId ? _self.messageId : messageId // ignore: cast_nullable_to_non_nullable
as String,chatId: freezed == chatId ? _self.chatId : chatId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class DeleteMessage implements ChatEvent {
  const DeleteMessage(this.context, {required this.messageId, required this.chatId});
  

 final  BuildContext context;
 final  String messageId;
 final  String? chatId;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeleteMessageCopyWith<DeleteMessage> get copyWith => _$DeleteMessageCopyWithImpl<DeleteMessage>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeleteMessage&&(identical(other.context, context) || other.context == context)&&(identical(other.messageId, messageId) || other.messageId == messageId)&&(identical(other.chatId, chatId) || other.chatId == chatId));
}


@override
int get hashCode => Object.hash(runtimeType,context,messageId,chatId);

@override
String toString() {
  return 'ChatEvent.deleteMessage(context: $context, messageId: $messageId, chatId: $chatId)';
}


}

/// @nodoc
abstract mixin class $DeleteMessageCopyWith<$Res> implements $ChatEventCopyWith<$Res> {
  factory $DeleteMessageCopyWith(DeleteMessage value, $Res Function(DeleteMessage) _then) = _$DeleteMessageCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String messageId, String? chatId
});




}
/// @nodoc
class _$DeleteMessageCopyWithImpl<$Res>
    implements $DeleteMessageCopyWith<$Res> {
  _$DeleteMessageCopyWithImpl(this._self, this._then);

  final DeleteMessage _self;
  final $Res Function(DeleteMessage) _then;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? messageId = null,Object? chatId = freezed,}) {
  return _then(DeleteMessage(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,messageId: null == messageId ? _self.messageId : messageId // ignore: cast_nullable_to_non_nullable
as String,chatId: freezed == chatId ? _self.chatId : chatId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc


class CreateAndSendMessage implements ChatEvent {
  const CreateAndSendMessage(this.context, {required this.message, required this.userId, required this.onSuccess});
  

 final  BuildContext context;
 final  String message;
 final  int userId;
 final  Function onSuccess;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CreateAndSendMessageCopyWith<CreateAndSendMessage> get copyWith => _$CreateAndSendMessageCopyWithImpl<CreateAndSendMessage>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CreateAndSendMessage&&(identical(other.context, context) || other.context == context)&&(identical(other.message, message) || other.message == message)&&(identical(other.userId, userId) || other.userId == userId)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,message,userId,onSuccess);

@override
String toString() {
  return 'ChatEvent.createAndSendMessage(context: $context, message: $message, userId: $userId, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $CreateAndSendMessageCopyWith<$Res> implements $ChatEventCopyWith<$Res> {
  factory $CreateAndSendMessageCopyWith(CreateAndSendMessage value, $Res Function(CreateAndSendMessage) _then) = _$CreateAndSendMessageCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String message, int userId, Function onSuccess
});




}
/// @nodoc
class _$CreateAndSendMessageCopyWithImpl<$Res>
    implements $CreateAndSendMessageCopyWith<$Res> {
  _$CreateAndSendMessageCopyWithImpl(this._self, this._then);

  final CreateAndSendMessage _self;
  final $Res Function(CreateAndSendMessage) _then;

/// Create a copy of ChatEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? message = null,Object? userId = null,Object? onSuccess = null,}) {
  return _then(CreateAndSendMessage(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,message: null == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String,userId: null == userId ? _self.userId : userId // ignore: cast_nullable_to_non_nullable
as int,onSuccess: null == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as Function,
  ));
}


}

/// @nodoc
mixin _$ChatState {

 bool get isLoading; bool get isButtonLoading; bool get isMessageLoading; ChatModel? get chatModel;
/// Create a copy of ChatState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ChatStateCopyWith<ChatState> get copyWith => _$ChatStateCopyWithImpl<ChatState>(this as ChatState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ChatState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isMessageLoading, isMessageLoading) || other.isMessageLoading == isMessageLoading)&&(identical(other.chatModel, chatModel) || other.chatModel == chatModel));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,isButtonLoading,isMessageLoading,chatModel);

@override
String toString() {
  return 'ChatState(isLoading: $isLoading, isButtonLoading: $isButtonLoading, isMessageLoading: $isMessageLoading, chatModel: $chatModel)';
}


}

/// @nodoc
abstract mixin class $ChatStateCopyWith<$Res>  {
  factory $ChatStateCopyWith(ChatState value, $Res Function(ChatState) _then) = _$ChatStateCopyWithImpl;
@useResult
$Res call({
 bool isLoading, bool isButtonLoading, bool isMessageLoading, ChatModel? chatModel
});




}
/// @nodoc
class _$ChatStateCopyWithImpl<$Res>
    implements $ChatStateCopyWith<$Res> {
  _$ChatStateCopyWithImpl(this._self, this._then);

  final ChatState _self;
  final $Res Function(ChatState) _then;

/// Create a copy of ChatState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoading = null,Object? isButtonLoading = null,Object? isMessageLoading = null,Object? chatModel = freezed,}) {
  return _then(_self.copyWith(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isMessageLoading: null == isMessageLoading ? _self.isMessageLoading : isMessageLoading // ignore: cast_nullable_to_non_nullable
as bool,chatModel: freezed == chatModel ? _self.chatModel : chatModel // ignore: cast_nullable_to_non_nullable
as ChatModel?,
  ));
}

}


/// Adds pattern-matching-related methods to [ChatState].
extension ChatStatePatterns on ChatState {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ChatState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ChatState() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ChatState value)  $default,){
final _that = this;
switch (_that) {
case _ChatState():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ChatState value)?  $default,){
final _that = this;
switch (_that) {
case _ChatState() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoading,  bool isButtonLoading,  bool isMessageLoading,  ChatModel? chatModel)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ChatState() when $default != null:
return $default(_that.isLoading,_that.isButtonLoading,_that.isMessageLoading,_that.chatModel);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoading,  bool isButtonLoading,  bool isMessageLoading,  ChatModel? chatModel)  $default,) {final _that = this;
switch (_that) {
case _ChatState():
return $default(_that.isLoading,_that.isButtonLoading,_that.isMessageLoading,_that.chatModel);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoading,  bool isButtonLoading,  bool isMessageLoading,  ChatModel? chatModel)?  $default,) {final _that = this;
switch (_that) {
case _ChatState() when $default != null:
return $default(_that.isLoading,_that.isButtonLoading,_that.isMessageLoading,_that.chatModel);case _:
  return null;

}
}

}

/// @nodoc


class _ChatState implements ChatState {
  const _ChatState({this.isLoading = true, this.isButtonLoading = false, this.isMessageLoading = true, this.chatModel = null});
  

@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isButtonLoading;
@override@JsonKey() final  bool isMessageLoading;
@override@JsonKey() final  ChatModel? chatModel;

/// Create a copy of ChatState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ChatStateCopyWith<_ChatState> get copyWith => __$ChatStateCopyWithImpl<_ChatState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _ChatState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isButtonLoading, isButtonLoading) || other.isButtonLoading == isButtonLoading)&&(identical(other.isMessageLoading, isMessageLoading) || other.isMessageLoading == isMessageLoading)&&(identical(other.chatModel, chatModel) || other.chatModel == chatModel));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,isButtonLoading,isMessageLoading,chatModel);

@override
String toString() {
  return 'ChatState(isLoading: $isLoading, isButtonLoading: $isButtonLoading, isMessageLoading: $isMessageLoading, chatModel: $chatModel)';
}


}

/// @nodoc
abstract mixin class _$ChatStateCopyWith<$Res> implements $ChatStateCopyWith<$Res> {
  factory _$ChatStateCopyWith(_ChatState value, $Res Function(_ChatState) _then) = __$ChatStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoading, bool isButtonLoading, bool isMessageLoading, ChatModel? chatModel
});




}
/// @nodoc
class __$ChatStateCopyWithImpl<$Res>
    implements _$ChatStateCopyWith<$Res> {
  __$ChatStateCopyWithImpl(this._self, this._then);

  final _ChatState _self;
  final $Res Function(_ChatState) _then;

/// Create a copy of ChatState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoading = null,Object? isButtonLoading = null,Object? isMessageLoading = null,Object? chatModel = freezed,}) {
  return _then(_ChatState(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isButtonLoading: null == isButtonLoading ? _self.isButtonLoading : isButtonLoading // ignore: cast_nullable_to_non_nullable
as bool,isMessageLoading: null == isMessageLoading ? _self.isMessageLoading : isMessageLoading // ignore: cast_nullable_to_non_nullable
as bool,chatModel: freezed == chatModel ? _self.chatModel : chatModel // ignore: cast_nullable_to_non_nullable
as ChatModel?,
  ));
}


}

// dart format on
