import 'package:demand/domain/di/dependency_manager.dart';
import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:demand/domain/model/model/chat_model.dart';
import 'package:demand/domain/model/model/message_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';

part 'chat_event.dart';

part 'chat_state.dart';

part 'chat_bloc.freezed.dart';

class ChatBloc extends Bloc<ChatEvent, ChatState> {
  ChatBloc() : super(const ChatState()) {
    on<CheckChatId>((event, emit) async {
      emit(state.copyWith(isMessageLoading: true));
      final res = await chatRepository.getChat(sellerId: event.sellerId);
      res.fold(
        (l) {
          emit(state.copyWith(chatModel: l, isMessageLoading: false));
        },
        (r) {
          emit(state.copyWith(isMessageLoading: false));
        },
      );
    });

    on<CreateAndSendMessage>((event, emit) async {
      final res = await chatRepository.createChat(id: event.userId);
      res.fold(
        (l) async {
          emit(state.copyWith(chatModel: l));
          event.onSuccess();
        },
        (r) {
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<SendMessage>((event, emit) {
      chatRepository.sendMessage(
        chatDocId: event.chatId ?? state.chatModel?.docId ?? "",
        message: MessageModel(
          message: event.message,
          senderId: LocalStorage.getUser().id ?? 0,
          doc: "",
        ),
      );
      chatRepository.sendNotification(
        userId: state.chatModel?.senderId ?? 0,
        message: event.message,
      );
    });

    on<SendImage>((event, emit) async {
      emit(state.copyWith(isButtonLoading: true));
      final res = await galleryRepository.uploadImage(
        event.file,
        UploadType.chats,
      );
      res.fold(
        (image) {
          chatRepository.sendMessage(
            chatDocId: event.chatId ?? state.chatModel?.docId ?? "",
            message: MessageModel(
              message: image.imageData?.title,
              senderId: LocalStorage.getUser().id ?? 0,
              type: "image",
              doc: "",
            ),
          );
          emit(state.copyWith(isButtonLoading: false));
        },
        (r) {
          emit(state.copyWith(isButtonLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    });

    on<EditMessage>((event, emit) {
      chatRepository.editMessage(
        chatDocId: event.chatId ?? state.chatModel?.docId ?? "",
        message: event.message,
        docId: event.messageId,
      );
    });

    on<ReplyMessage>((event, emit) {
      chatRepository.replyMessage(
        chatDocId: event.chatId ?? state.chatModel?.docId ?? "",
        message: MessageModel(
          message: event.message,
          senderId: LocalStorage.getUser().id ?? 0,
          doc: "",
          replyDocId: event.messageId,
        ),
      );
      chatRepository.sendNotification(
        userId: state.chatModel?.senderId ?? 0,
        message: event.message,
      );
    });

    on<DeleteMessage>((event, emit) {
      chatRepository.deleteMessage(
        chatDocId: event.chatId ?? state.chatModel?.docId ?? "",
        docId: event.messageId,
      );
    });
  }
}
