import 'package:dartz/dartz.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

import '../../app_constants.dart';
import '../../domain/di/dependency_manager.dart';
import '../../domain/model/model/user_model.dart';
import '../../infrastructure/firebase/firebase_service.dart';
import '../../infrastructure/local_storage/local_storage.dart';
import '../../infrastructure/service/services.dart';
import '../../infrastructure/session/session_coordinator.dart';

part 'auth_event.dart';

part 'auth_state.dart';

part 'auth_bloc.freezed.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  bool isForgetPassword = false;
  String? refreshToken;
  String? _verificationEmail;
  final int _generation = SessionCoordinator.instance.generation;
  bool get _current =>
      !isClosed && SessionCoordinator.instance.current(_generation);

  Future<bool> _acceptToken(String value) async {
    if (!_current) return false;
    return await LocalStorage.setToken(
          value,
          expectedGeneration: _generation,
        ) &&
        _current;
  }

  AuthBloc() : super(const AuthState()) {
    on<Login>(login);

    on<AcceptTerm>(acceptTerm);

    on<SocialSignIn>(socialSignIn);

    on<SignUp>(signUp);

    on<SignUpWithData>(signUpWithData);

    on<SetVerificationId>(setVerificationId);

    on<ShowPassword>(showPassword);

    on<ShowConfirmPassword>(showConfirmPassword);

    on<SwitchScreen>(switchScreen);

    on<CheckVerify>(checkVerify);

    on<CheckVerifyEmail>(checkVerifyEmail);

    on<CheckPhone>(checkPhone);

    on<SignUpEmail>(signUpEmail);
    on<SignUpPhone>(signUpPhone);

    on<ForgotPassword>(forgotPassword);

    on<ForgotPasswordConfirm>(forgotPasswordConfirm);

    on<ForgotPasswordAfter>(forgotPasswordAfter);

    on<LoadingChange>(loadingChange);
  }

  Future<void> login(Login event, emit) async {
    emit(state.copyWith(isLoading: true));
    final res = await authRepository.login(
      email: event.email,
      phone: event.phone,
      password: event.password,
    );
    if (!_current) return;
    await res.fold(
      (l) async {
        if (!await _acceptToken(l.data?.accessToken ?? "")) return;
        await LocalStorage.setUser(l.data?.user);
        if (!_current) return;
        event.onSuccess?.call();
        authRepository.updateFirebaseToken();
        userRepository.setLikeProductList(
          ids: LocalStorage.getLikedProductsList(),
        );
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
    if (_current) emit(state.copyWith(isLoading: false));
  }

  Future<void> socialSignIn(SocialSignIn event, emit) async {
    emit(state.copyWith(isLoading: true));
    Either<UserCredential, dynamic>? user;
    switch (event.type) {
      case FlutterRemix.apple_fill:
        user = await SessionCoordinator.instance.runIdentity(
          FirebaseService.socialApple,
        );
        break;
      case FlutterRemix.google_fill:
        user = await SessionCoordinator.instance.runIdentity(
          FirebaseService.socialGoogle,
        );
        break;
      case FlutterRemix.facebook_fill:
        // user = await FirebaseService.socialFacebook();
        break;
    }

    if (!_current) return;
    await user?.fold(
      (l) async {
        String? token = await l.user?.getIdToken();
        if (!_current) return;
        final res = await authRepository.loginWithSocial(
          email: l.user?.email ?? "",
          displayName: l.user?.displayName ?? "",
          id: token ?? "",
          img: l.user?.photoURL,
        );

        if (!_current) return;
        await res.fold(
          (l) async {
            if (!await _acceptToken(l.data?.accessToken ?? "")) return;
            await LocalStorage.setUser(l.data?.user);
            if (!_current) return;
            add(const AuthEvent.loadingChange());
            event.onSuccess?.call();
            authRepository.updateFirebaseToken();
            userRepository.setLikeProductList(
              ids: LocalStorage.getLikedProductsList(),
            );
          },
          (r) {
            add(const AuthEvent.loadingChange());
            AppHelpers.errorSnackBar(event.context, message: r);
          },
        );
      },
      (r) {
        add(const AuthEvent.loadingChange());
      },
    );
  }

  Future<void> signUp(SignUp event, emit) async {
    emit(state.copyWith(isLoading: true));
    final res = await authRepository.sigUpWithPhone(
      user: UserModel(
        firstname: event.firstname,
        lastname: event.lastname,
        email: event.email,
        phone: event.phone,
        refreshToken: refreshToken,
        password: event.password,
        referral: event.referral,
      ),
    );
    if (!_current) return;
    await res.fold(
      (l) async {
        if (!await _acceptToken(l.token ?? "")) return;
        await LocalStorage.setUser(l.user);
        if (!_current) return;
        event.onSuccess?.call();
        authRepository.updateFirebaseToken();
        userRepository.setLikeProductList(
          ids: LocalStorage.getLikedProductsList(),
        );
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
    if (_current) emit(state.copyWith(isLoading: false));
  }

  Future<void> signUpWithData(SignUpWithData event, emit) async {
    emit(state.copyWith(isLoading: true));
    final res = await authRepository.sigUpWithData(
      user: UserModel(
        firstname: event.firstname,
        lastname: event.lastname,
        password: event.password,
        referral: event.referral,
        phone: event.phone,
        email: event.email,
      ),
    );
    if (!_current) return;
    await res.fold(
      (l) async {
        await LocalStorage.setUser(l.data);
        if (!_current) return;
        event.onSuccess?.call();
        authRepository.updateFirebaseToken();
        userRepository.setLikeProductList(
          ids: LocalStorage.getLikedProductsList(),
        );
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
    if (_current) emit(state.copyWith(isLoading: false));
  }

  Future<void> setVerificationId(SetVerificationId event, emit) async {
    emit(
      state.copyWith(
        screenType: AuthType.confirm,
        verificationId: event.id,
        isLoading: false,
      ),
    );
  }

  void showPassword(ShowPassword event, emit) {
    emit(state.copyWith(isShowPassword: !state.isShowPassword));
  }

  void showConfirmPassword(ShowConfirmPassword event, emit) {
    emit(state.copyWith(isShowConfirmPassword: !state.isShowConfirmPassword));
  }

  void switchScreen(SwitchScreen event, emit) {
    isForgetPassword = (event.type == AuthType.forgetPassword);
    emit(state.copyWith(screenType: event.type));
  }

  Future<void> checkVerify(CheckVerify event, Emitter<AuthState> emit) async {
    emit(state.copyWith(isLoading: true, isError: false));

    if (!AppConstants.isPhoneFirebase) {
      final res = await authRepository.verifyPhone(
        verifyId: event.verifyId,
        verifyCode: event.code,
        phone: event.phone,
      );

      if (!_current) return;
      await res.fold(
        (l) async {
          if (event.editPhone ?? false) {
            Navigator.pop(event.context, true);
            emit(state.copyWith(isLoading: false));
            return;
          }

          if (!await _acceptToken(l.token ?? "")) return;
          await LocalStorage.setUser(l.user);
          if (!_current) return;
          authRepository.updateFirebaseToken();
          userRepository.setLikeProductList(
            ids: LocalStorage.getLikedProductsList(),
          );

          emit(
            state.copyWith(
              isLoading: false,
              screenType: isForgetPassword
                  ? AuthType.updatePassword
                  : AuthType.signUpFull,
            ),
          );
        },
        (r) async {
          emit(state.copyWith(isLoading: false, isError: true));
        },
      );
    } else {
      refreshToken = null;
      final res = await SessionCoordinator.instance.runIdentity(
        () => FirebaseService.checkCode(
          verificationId: event.verifyId,
          code: event.code,
        ),
      );

      if (!_current) return;
      await res.fold(
        (l) async {
          if (event.editPhone ?? false) {
            emit(state.copyWith(isLoading: false));
            Navigator.pop(event.context, true);
            return;
          }

          refreshToken = await l.user?.getIdToken();
          if (!_current) return;

          emit(
            state.copyWith(
              isLoading: false,
              screenType: isForgetPassword
                  ? AuthType.updatePassword
                  : AuthType.signUpFull,
            ),
          );
        },
        (r) async {
          emit(state.copyWith(isLoading: false, isError: true));
        },
      );
    }
  }

  Future<void> checkVerifyEmail(CheckVerifyEmail event, emit) async {
    refreshToken = null;
    emit(state.copyWith(isLoading: true, isError: false));
    final email = _verificationEmail;
    if (email == null) {
      emit(state.copyWith(isLoading: false, isError: true));
      return;
    }
    final res = await authRepository.verifyEmail(
      verifyCode: event.code,
      email: email,
    );
    if (!_current) return;
    res.fold(
      (l) {
        emit(
          state.copyWith(
            isLoading: false,
            screenType: isForgetPassword
                ? AuthType.updatePassword
                : AuthType.signUpFull,
          ),
        );
      },
      (r) {
        emit(state.copyWith(isLoading: false, isError: true));
      },
    );
  }

  Future<void> checkPhone(CheckPhone event, emit) async {
    emit(state.copyWith(isLoading: true));
    final res = await authRepository.checkPhone(phone: event.phone);
    if (!_current) return;
    res.fold(
      (l) {
        if (isForgetPassword && l) {
          event.onSuccess?.call();
          return;
        }
        if (l) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(
            event.context,
            message: AppHelpers.getTranslation(TrKeys.thisPhoneAlreadyExist),
          );
          return;
        }
        if (!l && isForgetPassword) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(
            event.context,
            message: AppHelpers.getTranslation(TrKeys.thisPhoneAlreadyNoExist),
          );
          return;
        }
        event.onSuccess?.call();
      },
      (r) {
        emit(state.copyWith(isLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> signUpEmail(SignUpEmail event, emit) async {
    _verificationEmail = event.email.trim();
    emit(state.copyWith(isLoading: true));
    final res = await authRepository.sigUpWithEmail(email: event.email);
    if (!_current) return;
    res.fold(
      (l) {
        emit(state.copyWith(isLoading: false, screenType: AuthType.confirm));
      },
      (r) {
        emit(state.copyWith(isLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> signUpPhone(SignUpPhone event, emit) async {
    if (!AppConstants.isPhoneFirebase) {
      emit(state.copyWith(isLoading: true));
      final res = await authRepository.sendOtp(phone: event.phone);
      if (!_current) return;
      res.fold(
        (l) {
          add(AuthEvent.setVerificationId(id: l.data?.verifyId ?? ''));
        },
        (r) {
          emit(state.copyWith(isLoading: false));
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    } else {
      FirebaseService.sendCode(
        phone: event.phone,
        onSuccess: (id) {
          if (!_current) return;
          add(AuthEvent.setVerificationId(id: id));
          add(AuthEvent.loadingChange());
        },
        onError: (r) {
          if (!_current) return;
          add(AuthEvent.loadingChange());
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
    }
  }

  Future<void> forgotPassword(ForgotPassword event, emit) async {
    emit(state.copyWith(isLoading: true));
    if (!AppValidators.isEmail(event.phone)) {
      if (!AppConstants.isPhoneFirebase) {
        final res = await authRepository.forgotPassword(phone: event.phone);
        if (!_current) return;
        res.fold(
          (l) {
            emit(
              state.copyWith(
                screenType: AuthType.confirm,
                verificationId: l.data?.verifyId ?? '',
                isLoading: false,
                isReset: true,
              ),
            );
            event.onSuccess?.call();
          },
          (r) {
            emit(state.copyWith(isLoading: false));
            AppHelpers.errorSnackBar(event.context, message: r);
          },
        );
      } else {
        FirebaseService.sendCode(
          phone: event.phone,
          onSuccess: (id) {
            if (!_current) return;
            emit(
              state.copyWith(
                screenType: AuthType.confirm,
                verificationId: id,
                isLoading: false,
                isReset: true,
              ),
            );
            event.onSuccess?.call();
          },
          onError: (r) {
            if (!_current) return;
            add(AuthEvent.loadingChange());
            AppHelpers.errorSnackBar(event.context, message: r);
          },
        );
      }
    }
    final res = await authRepository.forgotPassword(phone: event.phone);
    if (!_current) return;
    res.fold(
      (l) {
        emit(
          state.copyWith(
            isLoading: false,
            isReset: true,
            screenType: AuthType.confirm,
          ),
        );
        event.onSuccess?.call();
      },
      (r) {
        emit(state.copyWith(isLoading: false));
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
  }

  Future<void> forgotPasswordConfirm(ForgotPasswordConfirm event, emit) async {
    emit(state.copyWith(isLoading: true));
    final res = await authRepository.forgotPasswordConfirm(
      verifyCode: event.code,
      email: event.email,
    );
    if (!_current) return;
    await res.fold(
      (l) async {
        if (!await _acceptToken(l.token ?? '')) return;
        await LocalStorage.setUser(l.user);
        if (!_current) return;
        emit(
          state.copyWith(isLoading: false, screenType: AuthType.updatePassword),
        );
        authRepository.updateFirebaseToken();
        userRepository.setLikeProductList(
          ids: LocalStorage.getLikedProductsList(),
        );
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
    if (_current) emit(state.copyWith(isLoading: false));
  }

  Future<void> forgotPasswordAfter(ForgotPasswordAfter event, emit) async {
    emit(state.copyWith(isLoading: true));
    if (!AppHelpers.checkPhone(event.phone)) {
      final res = await userRepository.updatePassword(
        password: event.password,
        passwordConfirmation: event.password,
      );
      if (!_current) return;
      res.fold(
        (l) {
          event.onSuccess?.call();
        },
        (r) {
          AppHelpers.errorSnackBar(event.context, message: r);
        },
      );
      emit(state.copyWith(isLoading: false));
      return;
    }
    final res = await authRepository.forgotPasswordAfter(
      phone: event.phone,
      password: event.password,
      verificationId: refreshToken ?? "",
    );
    if (!_current) return;
    await res.fold(
      (l) async {
        if (!await _acceptToken(l.data?.accessToken ?? "")) return;
        await LocalStorage.setUser(l.data?.user);
        if (!_current) return;
        event.onSuccess?.call();
        authRepository.updateFirebaseToken();
        userRepository.setLikeProductList(
          ids: LocalStorage.getLikedProductsList(),
        );
      },
      (r) {
        AppHelpers.errorSnackBar(event.context, message: r);
      },
    );
    emit(state.copyWith(isLoading: false));
  }

  void loadingChange(LoadingChange event, emit) {
    emit(state.copyWith(isLoading: false));
  }

  Future<void> acceptTerm(AcceptTerm event, emit) async {
    emit(state.copyWith(isAcceptTerm: !state.isAcceptTerm));
  }
}
