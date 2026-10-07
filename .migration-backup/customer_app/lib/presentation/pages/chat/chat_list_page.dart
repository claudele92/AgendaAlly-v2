import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/domain/model/model/chat_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/firebase/firebase_service.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:lottie/lottie.dart';

import '../../app_assets.dart';
import 'widget/chat_item.dart';

class ChatListPage extends StatefulWidget {
  const ChatListPage({super.key});

  @override
  State<ChatListPage> createState() => _ChatListPageState();
}

class _ChatListPageState extends State<ChatListPage> {
  Future<List<ChatModel>> getList(
    QuerySnapshot<Map<String, dynamic>>? res,
  ) async {
    List<ChatModel> list = [];
    for (var element in res?.docs ?? []) {
      final user = await chatRepository.showChatUser(
        sellerId:
            ChatModel.fromJson(
              chat: element.data(),
              doc: element.id,
            ).senderId ??
            0,
      );
      user.fold(
        (l) {
          list.add(
            ChatModel.fromJson(chat: element.data(), doc: element.id, user: l),
          );
        },
        (r) {
          list.add(ChatModel.fromJson(chat: element.data(), doc: element.id));
        },
      );
    }

    return list..sort(
      (a, b) => b.lastTime?.compareTo(a.lastTime ?? Timestamp.now()) ?? 0,
    );
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        bottom: false,
        child: SizedBox(
          width: MediaQuery.sizeOf(context).width,
          child: Column(
            children: [
              Text(
                AppHelpers.getTranslation(TrKeys.chats),
                style: CustomStyle.interNoSemi(
                  color: colors.textBlack,
                  size: 18,
                ),
              ),
              24.verticalSpace,
              Expanded(
                child: StreamBuilder(
                  stream: FirebaseService.store
                      .collection("chat")
                      .where(
                        "ids",
                        arrayContainsAny: [LocalStorage.getUser().id],
                      )
                      .snapshots(),
                  builder: (context, snapshot) {
                    if (!snapshot.hasData) {
                      return const Center(child: Loading());
                    }
                    return FutureBuilder(
                      future: getList(snapshot.data),
                      builder: (context, data) {
                        if (!data.hasData) {
                          return const Center(child: Loading());
                        }
                        return data.data?.isNotEmpty ?? false
                            ? ListView.builder(
                                padding: EdgeInsets.symmetric(horizontal: 16.r),
                                shrinkWrap: true,
                                itemCount: data.data?.length,
                                itemBuilder: (context, index) {
                                  return ButtonEffectAnimation(
                                    onTap: () {
                                      AppRouteSetting.goChat(
                                        context,
                                        senderId:
                                            data.data?[index].senderId ?? 0,
                                        chatId: data.data?[index].docId ?? "",
                                      );
                                    },
                                    child: ChatItem(
                                      colors: colors,
                                      chat: data.data?[index] ?? ChatModel(),
                                    ),
                                  );
                                },
                              )
                            : _empty(context, colors);
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.startFloat,
      floatingButton: (colors) => PopButton(colors: colors),
    );
  }

  Widget _empty(BuildContext context, CustomColorSet colors) {
    return Column(
      children: [
        32.verticalSpace,
        Lottie.asset(
          Assets.lottieNotificationEmpty,
          width: MediaQuery.sizeOf(context).width / 1.5,
        ),
        32.verticalSpace,
        Text(
          AppHelpers.getTranslation(TrKeys.yourChatListIsEmpty),
          style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
        ),
      ],
    );
  }
}
