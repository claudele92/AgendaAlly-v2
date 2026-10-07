part of 'chat_bloc.dart';

@freezed
abstract class ChatEvent with _$ChatEvent {
  const factory ChatEvent.checkChatId({required int sellerId}) = CheckChatId;

  const factory ChatEvent.sendImage(
    BuildContext context, {
    required String file,
    required String? chatId,
  }) = SendImage;

  const factory ChatEvent.sendMessage(
    BuildContext context, {
    required String message,
    required String? chatId,
  }) = SendMessage;

  const factory ChatEvent.editMessage(
    BuildContext context, {
    required String message,
    required String messageId,
    required String? chatId,
  }) = EditMessage;

  const factory ChatEvent.replyMessage(
    BuildContext context, {
    required String message,
    required String messageId,
    required String? chatId,
  }) = ReplyMessage;

  const factory ChatEvent.deleteMessage(
    BuildContext context, {
    required String messageId,
    required String? chatId,
  }) = DeleteMessage;

  const factory ChatEvent.createAndSendMessage(
    BuildContext context, {
    required String message,
    required int userId,
    required Function onSuccess,
  }) = CreateAndSendMessage;
}
