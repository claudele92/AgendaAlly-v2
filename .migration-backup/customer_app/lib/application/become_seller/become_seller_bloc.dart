// ignore_for_file: depend_on_referenced_packages

import 'package:demand/domain/model/request/create_shop_request.dart';
import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/infrastructure/service/services.dart';

part 'become_seller_event.dart';

part 'become_seller_state.dart';

part 'become_seller_bloc.freezed.dart';

class BecomeSellerBloc extends Bloc<BecomeSellerEvent, BecomeSellerState> {
  BecomeSellerBloc() : super(const BecomeSellerState()) {
    on<CreateShop>(createShop);

    on<SetDeliveryType>(setDeliveryType);

    on<SetDeliveryTimeType>(setDeliveryTimeType);

    on<UpdateLogoPath>(updateLogoPath);

    on<UpdateBgPath>(updateBgPath);

    on<UpdateFilePath>(updateFilePath);

    on<RemoveFilePath>(removeFilePath);
  }

  Future<void> createShop(CreateShop event, emit) async {
    String? logo;
    String? bgImage;
    List<String> filePath = [];
    emit(state.copyWith(isLoading: true, isSuccess: false));

    final logoRes = await galleryRepository.uploadImage(
      state.logoPath ?? "",
      UploadType.shopsLogo,
    );

    logoRes.fold((l) => logo = l.imageData?.title, (r) {
      emit(state.copyWith(isLoading: false));
      AppHelpers.errorSnackBar(event.context, message: r);
    });

    final bgRes = await galleryRepository.uploadImage(
      state.bgPath ?? "",
      UploadType.shopsBack,
    );

    bgRes.fold((l) => bgImage = l.imageData?.title, (r) {
      emit(state.copyWith(isLoading: false));
      AppHelpers.errorSnackBar(event.context, message: r);
    });

    final docRes = await galleryRepository.uploadMultipleImage(
      state.filepath,
      UploadType.shopsBack,
    );

    docRes.fold((l) => filePath = l.data?.title ?? [], (r) {
      emit(state.copyWith(isLoading: false));
      AppHelpers.errorSnackBar(event.context, message: r);
    });

    final res = await shopsRepository.createShop(
      shopRequest: event.request.copyWith(
        logo: logo,
        bgImage: bgImage,
        documents: filePath,
      ),
    );
    res.fold(
      (l) {
        emit(state.copyWith(isLoading: false, isSuccess: true));
        userRepository.getProfileDetails(event.context);
      },
      (r) {
        emit(state.copyWith(isLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  void setDeliveryType(SetDeliveryType event, emit) {
    emit(state.copyWith(deliveryType: event.type ?? ""));
  }

  void setDeliveryTimeType(SetDeliveryTimeType event, emit) {
    emit(state.copyWith(type: event.type ?? ""));
  }

  void updateLogoPath(UpdateLogoPath event, emit) {
    emit(state.copyWith(logoPath: event.imagePath));
  }

  void updateBgPath(UpdateBgPath event, emit) {
    emit(state.copyWith(bgPath: event.imagePath));
  }

  void updateFilePath(UpdateFilePath event, emit) {
    if (event.file == null) return;
    List<String> list = List.from(state.filepath);
    list.add(event.file!);
    emit(state.copyWith(filepath: list));
  }

  void removeFilePath(RemoveFilePath event, emit) {
    List<String> list = List.from(state.filepath);
    list.remove(event.file);
    emit(state.copyWith(filepath: list));
  }
}
