import 'package:demand/domain/di/dependency_manager.dart';
import 'package:flutter/cupertino.dart';
import 'package:freezed_annotation/freezed_annotation.dart';
import 'package:bloc/bloc.dart';
import 'package:demand/domain/model/model/currency_model.dart';
import 'package:demand/domain/model/model/referral_model.dart';
import 'package:demand/domain/model/model/translation_model.dart';
import 'package:demand/domain/model/model/user_model.dart';
import 'package:demand/domain/model/response/help_response.dart';
import 'package:demand/domain/model/response/languages_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/domain/model/response/digital_list_response.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

part 'profile_event.dart';

part 'profile_state.dart';

part 'profile_bloc.freezed.dart';

class ProfileBloc extends Bloc<ProfileEvent, ProfileState> {
  ProfileBloc() : super(const ProfileState()) {
    int all = 0;

    on<FetchDigitalList>((event, emit) async {
      if (event.isRefresh ?? false) {
        event.controller?.resetNoData();
        all = 0;
        emit(state.copyWith(digitalList: [], isDigitalLoading: true));
      }
      final res = await userRepository.getDigitalList(page: ++all);
      res.fold(
        (l) {
          List<DigitalProduct> list = List.from(state.digitalList);
          list.addAll(l.data ?? []);
          emit(state.copyWith(isDigitalLoading: false, digitalList: list));
          if (event.isRefresh ?? false) {
            event.controller?.refreshCompleted();
            return;
          } else if (l.data?.isEmpty ?? true) {
            event.controller?.loadNoData();
            return;
          }
          event.controller?.loadComplete();
          return;
        },
        (r) {
          emit(state.copyWith(isDigitalLoading: false));
          if (event.isRefresh ?? false) {
            event.controller?.refreshFailed();
          }
          event.controller?.loadFailed();

          AppHelpers.errorSnackBar(event.context, message: r, typeFixed: true);
        },
      );
    });

    on<UpdateImagePath>((event, emit) {
      emit(state.copyWith(imagePath: event.imagePath));
    });

    on<SetProgress>((event, emit) {
      List<DownloadModel> list = List.from(state.progressList);
      for (int i = 0; i < list.length; i++) {
        if (list[i].taskId == event.taskId) {
          list.removeAt(i);
          list.insert(i, list[i].copyWith(progress: event.progress));
          emit(state.copyWith(progressList: list));
          return;
        }
      }

      list.add(
        DownloadModel(progress: 0, taskId: event.taskId, index: event.index),
      );
      emit(state.copyWith(progressList: list));
    });

    on<ShowPassword>((event, emit) {
      emit(state.copyWith(showNewPassword: !state.showNewPassword));
    });

    on<ShowConfirmPassword>((event, emit) {
      emit(state.copyWith(showConfirmPassword: !state.showConfirmPassword));
    });

    on<FetchProfile>((event, emit) async {
      emit(state.copyWith(isLoading: true));
      await userRepository.getProfileDetails(event.context);
      emit(state.copyWith(isLoading: false));
    });

    on<UpdateUser>((event, emit) async {
      emit(state.copyWith(isLoading: true));
      String? imageUrl;
      if (state.imagePath != null) {
        final res = await galleryRepository.uploadImage(
          state.imagePath ?? "",
          UploadType.users,
        );
        res.fold(
          (l) => imageUrl = l.imageData?.title,
          (r) => AppHelpers.errorSnackBar(event.context, message: r.toString()),
        );
      }
      final res = await userRepository.updateProfile(
        firstName: event.firstName,
        lastName: event.lastName,
        imageUrl: imageUrl,
        phone: event.phone,
        email: event.email,
      );

      res.fold(
        (l) {
          LocalStorage.setUser(l.data);
          emit(state.copyWith(isLoading: false));
          event.onSuccess?.call();
        },
        (r) {
          AppHelpers.errorSnackBar(event.context, message: r.toString());
          emit(state.copyWith(isLoading: false));
        },
      );
    });

    on<UpdatePassword>((event, emit) async {
      emit(state.copyWith(isPasswordLoading: true));

      final res = await userRepository.updatePassword(
        password: event.newPassword,
        passwordConfirmation: event.confirmPassword,
      );

      res.fold(
        (l) {
          emit(state.copyWith(isPasswordLoading: false));
          event.onSuccess?.call();
        },
        (r) {
          emit(state.copyWith(isPasswordLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r.toString());
        },
      );
    });

    on<GetLanguage>((event, emit) async {
      emit(state.copyWith(isLanguage: state.languages.isEmpty));
      final res = await settingsRepository.getLanguages();
      res.fold(
        (l) {
          emit(state.copyWith(isLanguage: false, languages: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isLanguage: false));
          return AppHelpers.errorSnackBar(event.context, message: r.toString());
        },
      );
    });

    on<GetCurrency>((event, emit) async {
      emit(state.copyWith(isCurrency: state.currency.isEmpty));
      final res = await settingsRepository.getCurrencies();
      res.fold(
        (l) {
          emit(state.copyWith(isCurrency: false, currency: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isCurrency: false));
          return AppHelpers.errorSnackBar(event.context, message: r.toString());
        },
      );
    });

    on<FetchReferral>((event, emit) async {
      emit(state.copyWith(isReferralLoading: state.referralData == null));
      final res = await userRepository.getReferralDetails();
      res.fold(
        (l) {
          emit(state.copyWith(isReferralLoading: false, referralData: l));
        },
        (r) {
          emit(state.copyWith(isReferralLoading: false));
          return AppHelpers.errorSnackBar(event.context, message: r.toString());
        },
      );
    });

    on<GetHelps>((event, emit) async {
      emit(state.copyWith(isHelpLoading: state.helps.isEmpty));
      final res = await settingsRepository.getFaq();
      res.fold(
        (l) {
          emit(state.copyWith(isHelpLoading: false, helps: l.data ?? []));
        },
        (r) {
          emit(state.copyWith(isHelpLoading: false));
          return AppHelpers.errorSnackBar(event.context, message: r.toString());
        },
      );
    });

    on<UpdateLan>((event, emit) async {
      emit(state.copyWith(isLoading: true));
      emit(state.copyWith(isLoading: false));
    });

    on<GetPolicy>((event, emit) async {
      emit(state.copyWith(isHelpLoading: state.policy == null));
      final res = await settingsRepository.getPolicy();
      res.fold(
        (l) {
          emit(state.copyWith(isHelpLoading: false, policy: l));
        },
        (r) {
          emit(state.copyWith(isHelpLoading: false));
          return AppHelpers.errorSnackBar(event.context, message: r.toString());
        },
      );
    });

    on<GetTerm>((event, emit) async {
      emit(state.copyWith(isHelpLoading: state.term == null));
      final res = await settingsRepository.getTerm();
      res.fold(
        (l) {
          emit(state.copyWith(isHelpLoading: false, term: l));
        },
        (r) {
          emit(state.copyWith(isHelpLoading: false));
          return AppHelpers.errorSnackBar(event.context, message: r.toString());
        },
      );
    });
  }
}
